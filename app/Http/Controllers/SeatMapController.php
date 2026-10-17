<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\SeatMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SeatMapController extends Controller
{
    private const MAX_SEATS_PER_CATEGORY = 1000;

    /**
     * Super admin boleh semua. Admin organisasi cuma boleh event miliknya
     * sendiri (mencegah org lain utak-atik denah lewat ubah ID di URL).
     */
    private function authorizeEvent(Event $event): void
    {
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $event->organization_id !== $user->organization_id) {
            abort(403, 'Kamu tidak punya akses ke event ini.');
        }
    }

    // =========================================================
    // Halaman editor denah
    // =========================================================
    public function edit(Event $event)
    {
        $this->authorizeEvent($event);

        $seatedCategories = $event->ticketCategories()
            ->where('seating_mode', 'seated')
            ->get(['id', 'name']);

        $seats = app(SeatMapService::class)->forEvent($event);

        return view('admin.events.seats', compact('event', 'seatedCategories', 'seats'));
    }

    // =========================================================
    // Upload / ganti gambar denah
    // =========================================================
    public function uploadImage(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'seat_map' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = $request->file('seat_map')->store('seat-maps', 'public');

        if ($event->seat_map_image) {
            Storage::disk('public')->delete($event->seat_map_image);
        }

        $event->forceFill(['seat_map_image' => $path])->save();

        return back()->with('success', 'Denah berhasil diupload. Sekarang klik di atas gambar untuk menandai kursi.');
    }

    // =========================================================
    // Tambah satu titik kursi (dipanggil lewat fetch/JSON)
    // =========================================================
    public function storeSeat(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEvent($event);

        $data = $request->validate([
            'ticket_category_id' => 'required|integer',
            'label'              => 'required|string|max:30',
            'x'                  => 'required|numeric|between:0,100',
            'y'                  => 'required|numeric|between:0,100',
        ]);

        $label = trim($data['label']);

        return DB::transaction(function () use ($event, $data, $label) {
            // Kunci kategori supaya jumlah kursi & kuota tetap sinkron
            // walau ada beberapa klik/permintaan hampir bersamaan.
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->where('event_id', $event->id)
                ->lockForUpdate()
                ->first();

            if (! $category || $category->seating_mode !== 'seated') {
                return response()->json(['message' => 'Kategori tidak valid atau bukan mode pilih kursi.'], 422);
            }

            if (Seat::where('ticket_category_id', $category->id)->count() >= self::MAX_SEATS_PER_CATEGORY) {
                return response()->json(['message' => 'Jumlah kursi untuk kategori ini sudah mencapai batas maksimum.'], 422);
            }

            if (Seat::where('ticket_category_id', $category->id)->where('label', $label)->exists()) {
                return response()->json(['message' => "Label \"{$label}\" sudah dipakai di kategori ini."], 422);
            }

            $seat = Seat::create([
                'ticket_category_id' => $category->id,
                'label'              => $label,
                'x'                  => round((float) $data['x'], 2),
                'y'                  => round((float) $data['y'], 2),
            ]);

            // Kuota kategori "seated" selalu = jumlah kursi yang masih kosong,
            // jadi semua logika kuota yang sudah ada tetap berlaku.
            $category->increment('quota');

            return response()->json([
                'id'          => $seat->id,
                'category_id' => $seat->ticket_category_id,
                'label'       => $seat->label,
                'x'           => $seat->x,
                'y'           => $seat->y,
                'taken'       => false,
            ], 201);
        });
    }

    // =========================================================
    // Hapus satu titik kursi
    // =========================================================
    public function destroySeat(Seat $seat): JsonResponse
    {
        $category = TicketCategory::findOrFail($seat->ticket_category_id);
        $event    = Event::findOrFail($category->event_id);

        $this->authorizeEvent($event);

        return DB::transaction(function () use ($seat, $category) {
            $lockedCategory = TicketCategory::where('id', $category->id)->lockForUpdate()->first();

            if (Ticket::where('seat_id', $seat->id)->exists()) {
                return response()->json([
                    'message' => 'Kursi ini sudah dipesan (atau sedang menunggu pembayaran), tidak bisa dihapus.',
                ], 422);
            }

            $seat->delete();

            if ($lockedCategory && $lockedCategory->quota > 0) {
                $lockedCategory->decrement('quota');
            }

            return response()->json(['deleted' => true]);
        });
    }
}
