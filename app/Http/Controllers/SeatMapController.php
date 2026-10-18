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

        return back()->with('success', 'Denah berhasil diupload. Gunakan generator massal, deteksi berbantuan, atau penempatan manual untuk membuat kursi.');
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
    // Buat kursi massal dari grid atau hasil deteksi yang dikonfirmasi
    // POST /admin/events/{event}/seats/bulk
    // =========================================================
    public function storeBulk(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEvent($event);

        $data = $request->validate([
            'mode' => 'required|in:generate,detected',
            'ticket_category_id' => 'required|integer',
            'rows' => 'required_if:mode,generate|integer|min:1|max:50',
            'seats_per_row' => 'required_if:mode,generate|integer|min:1|max:50',
            'row_start' => ['required_if:mode,generate', 'string', 'regex:/^[A-Za-z]{1,2}$/'],
            'row_prefix' => 'nullable|string|max:10',
            'seat_start' => 'required_if:mode,generate|integer|min:1|max:9999',
            'x_start' => 'required_if:mode,generate|numeric|between:0,100',
            'x_end' => 'required_if:mode,generate|numeric|between:0,100',
            'y_start' => 'required_if:mode,generate|numeric|between:0,100',
            'y_end' => 'required_if:mode,generate|numeric|between:0,100',
            'seats' => 'required_if:mode,detected|array|min:1|max:1000',
            'seats.*.label' => 'required_if:mode,detected|string|max:30',
            'seats.*.x' => 'required_if:mode,detected|numeric|between:0,100',
            'seats.*.y' => 'required_if:mode,detected|numeric|between:0,100',
        ]);

        if ($data['mode'] === 'generate') {
            if ((float) $data['x_start'] >= (float) $data['x_end'] || (float) $data['y_start'] >= (float) $data['y_end']) {
                return response()->json(['message' => 'Nilai akhir X dan Y harus lebih besar daripada nilai awal.'], 422);
            }

            $total = (int) $data['rows'] * (int) $data['seats_per_row'];
            if ($total > 1000) {
                return response()->json(['message' => 'Maksimal 1.000 kursi dalam satu proses pembuatan. Bagi menjadi beberapa blok.'], 422);
            }

            $startRowNumber = $this->alphaToNumber(strtoupper($data['row_start']));
            $prefix = trim((string) ($data['row_prefix'] ?? ''));
            $prepared = [];

            for ($r = 0; $r < (int) $data['rows']; $r++) {
                $rowLabel = $prefix . $this->numberToAlpha($startRowNumber + $r);
                for ($c = 0; $c < (int) $data['seats_per_row']; $c++) {
                    $x = (int) $data['seats_per_row'] === 1
                        ? (((float) $data['x_start'] + (float) $data['x_end']) / 2)
                        : ((float) $data['x_start'] + (((float) $data['x_end'] - (float) $data['x_start']) * $c / ((int) $data['seats_per_row'] - 1)));
                    $y = (int) $data['rows'] === 1
                        ? (((float) $data['y_start'] + (float) $data['y_end']) / 2)
                        : ((float) $data['y_start'] + (((float) $data['y_end'] - (float) $data['y_start']) * $r / ((int) $data['rows'] - 1)));

                    $prepared[] = [
                        'label' => $rowLabel . ((int) $data['seat_start'] + $c),
                        'x' => round($x, 2),
                        'y' => round($y, 2),
                    ];
                }
            }
        } else {
            $prepared = array_map(function (array $seat) {
                return [
                    'label' => trim($seat['label']),
                    'x' => round((float) $seat['x'], 2),
                    'y' => round((float) $seat['y'], 2),
                ];
            }, $data['seats']);
        }

        $labels = array_column($prepared, 'label');
        if (count($labels) !== count(array_unique($labels))) {
            return response()->json(['message' => 'Ada label kursi duplikat di hasil ini. Periksa label sebelum menyimpan.'], 422);
        }

        return DB::transaction(function () use ($event, $data, $prepared, $labels) {
            $category = TicketCategory::where('id', $data['ticket_category_id'])
                ->where('event_id', $event->id)
                ->lockForUpdate()
                ->first();

            if (! $category || $category->seating_mode !== 'seated') {
                return response()->json(['message' => 'Kategori tidak valid atau belum menggunakan mode pilih kursi.'], 422);
            }

            $existingCount = Seat::where('ticket_category_id', $category->id)->count();
            if ($existingCount + count($prepared) > self::MAX_SEATS_PER_CATEGORY) {
                return response()->json(['message' => 'Kategori ini maksimal memiliki 1.000 kursi. Pilih kategori lain atau buat lebih sedikit kursi.'], 422);
            }

            $duplicate = Seat::where('ticket_category_id', $category->id)
                ->whereIn('label', $labels)
                ->value('label');
            if ($duplicate !== null) {
                return response()->json(['message' => "Label kursi {$duplicate} sudah ada di kategori ini. Ganti awalan baris atau nomor awal."], 422);
            }

            $created = [];
            foreach ($prepared as $item) {
                $seat = Seat::create([
                    'ticket_category_id' => $category->id,
                    'label' => $item['label'],
                    'x' => $item['x'],
                    'y' => $item['y'],
                ]);
                $created[] = [
                    'id' => $seat->id,
                    'category_id' => $seat->ticket_category_id,
                    'label' => $seat->label,
                    'x' => (float) $seat->x,
                    'y' => (float) $seat->y,
                    'taken' => false,
                ];
            }

            $category->increment('quota', count($created));

            return response()->json([
                'message' => count($created) . ' kursi berhasil dibuat.',
                'seats' => $created,
            ], 201);
        });
    }

    // =========================================================
    // Pindahkan banyak kursi ke kategori lain sekaligus.
    // Kursi yang sudah terikat ke tiket sengaja dikunci dari pemindahan.
    // PATCH /admin/events/{event}/seats/bulk-category
    // =========================================================
    public function bulkAssignCategory(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEvent($event);

        $data = $request->validate([
            'ticket_category_id' => 'required|integer',
            'seat_ids' => 'required|array|min:1|max:1000',
            'seat_ids.*' => 'required|integer|distinct',
        ]);

        return DB::transaction(function () use ($event, $data) {
            $ids = array_map('intval', $data['seat_ids']);
            $seats = Seat::whereIn('id', $ids)
                ->whereHas('category', fn ($query) => $query->where('event_id', $event->id))
                ->lockForUpdate()
                ->get();

            if ($seats->count() !== count($ids)) {
                return response()->json(['message' => 'Sebagian kursi tidak ditemukan pada event ini. Muat ulang halaman lalu coba lagi.'], 422);
            }

            if (Ticket::whereIn('seat_id', $ids)->exists()) {
                return response()->json(['message' => 'Pilihan berisi kursi yang sudah dipesan atau sedang menunggu pembayaran. Kursi tersebut tidak bisa dipindahkan kategorinya.'], 422);
            }

            $categoryIds = $seats->pluck('ticket_category_id')->push((int) $data['ticket_category_id'])->unique()->values();
            $categories = TicketCategory::whereIn('id', $categoryIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $target = $categories->get((int) $data['ticket_category_id']);
            if (! $target || (int) $target->event_id !== (int) $event->id || $target->seating_mode !== 'seated') {
                return response()->json(['message' => 'Kategori tujuan tidak valid untuk event ini.'], 422);
            }

            foreach ($seats as $seat) {
                $source = $categories->get((int) $seat->ticket_category_id);
                if (! $source || (int) $source->event_id !== (int) $event->id || $source->seating_mode !== 'seated') {
                    return response()->json(['message' => 'Kursi harus berada pada kategori pilih-kursi yang valid.'], 422);
                }
            }

            $moving = $seats->filter(fn ($seat) => (int) $seat->ticket_category_id !== (int) $target->id);
            if (Seat::where('ticket_category_id', $target->id)->count() + $moving->count() > self::MAX_SEATS_PER_CATEGORY) {
                return response()->json(['message' => 'Kategori tujuan akan melebihi batas 1.000 kursi. Kurangi jumlah yang dipilih atau pilih kategori lain.'], 422);
            }
            if ($moving->isEmpty()) {
                return response()->json(['message' => 'Semua kursi yang dipilih sudah berada pada kategori tersebut.'], 200);
            }

            $movingLabels = $moving->pluck('label')->all();
            if (count($movingLabels) !== count(array_unique($movingLabels))) {
                return response()->json(['message' => 'Kursi terpilih memiliki label yang sama dari kategori berbeda. Pisahkan pemindahan atau ubah label terlebih dahulu.'], 422);
            }
            $labels = $moving->pluck('label')->unique()->values();
            $movingIds = $moving->pluck('id')->map(fn ($id) => (int) $id)->all();
            $conflict = Seat::where('ticket_category_id', $target->id)
                ->whereIn('label', $labels)
                ->whereNotIn('id', $movingIds)
                ->value('label');
            if ($conflict !== null) {
                return response()->json(['message' => "Kategori tujuan sudah memiliki kursi bernama {$conflict}. Gunakan label unik sebelum memindahkan kursi."], 422);
            }

            $movingCounts = $moving->countBy('ticket_category_id');
            foreach ($movingCounts as $sourceId => $count) {
                $source = $categories->get((int) $sourceId);
                if ((int) $source->quota < $count) {
                    return response()->json(['message' => 'Kuota kategori asal tidak sinkron dengan kursi yang dipilih. Periksa kuota terlebih dahulu.'], 422);
                }
            }

            foreach ($movingCounts as $sourceId => $count) {
                $categories->get((int) $sourceId)->decrement('quota', $count);
            }

            foreach ($moving as $seat) {
                $seat->ticket_category_id = $target->id;
                $seat->save();
            }

            $target->increment('quota', $moving->count());

            return response()->json([
                'message' => $moving->count() . ' kursi dipindahkan ke kategori ' . $target->name . '.',
                'updated_ids' => $moving->pluck('id')->values(),
                'category_id' => $target->id,
            ]);
        });
    }

    private function alphaToNumber(string $letters): int
    {
        $number = 0;
        foreach (str_split(strtoupper($letters)) as $letter) {
            $number = ($number * 26) + (ord($letter) - 64);
        }
        return max(1, $number);
    }

    private function numberToAlpha(int $number): string
    {
        $label = '';
        while ($number > 0) {
            $number--;
            $label = chr(65 + ($number % 26)) . $label;
            $number = intdiv($number, 26);
        }
        return $label;
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
