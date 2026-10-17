<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketCategoryController extends Controller
{
    /**
     * Super admin boleh semua. Admin organisasi hanya boleh event miliknya.
     */
    private function authorizeEvent(Event $event): void
    {
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $event->organization_id !== $user->organization_id) {
            abort(403, 'Kamu tidak punya akses ke event ini.');
        }
    }

    // Parameter route sengaja TIDAK di-type-hint model, supaya tidak
    // bergantung pada nama parameter di routes/web.php ({event} / {category} / dst).
    // Laravel mengisi parameter polos ini sesuai urutan di URL.

    public function index($eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorizeEvent($event);

        $categories = $event->ticketCategories()->get();

        // Jumlah titik kursi per kategori (untuk kategori mode "seated").
        $seatCounts = Seat::whereIn('ticket_category_id', $categories->pluck('id'))
            ->selectRaw('ticket_category_id, COUNT(*) as total')
            ->groupBy('ticket_category_id')
            ->pluck('total', 'ticket_category_id');

        return view('admin.ticket_categories.index', compact('event', 'categories', 'seatCounts'));
    }

    public function store(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorizeEvent($event);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'required|string|max:1000',
            'price'        => 'required|numeric|min:0',
            'seating_mode' => 'required|in:quota,seated',
            'quota'        => 'required_if:seating_mode,quota|nullable|integer|min:1',
        ]);

        $isSeated = $data['seating_mode'] === 'seated';

        // forceFill: tidak tergantung isi $fillable di model TicketCategory.
        $category = new TicketCategory();
        $category->forceFill([
            'event_id'     => $event->id,
            'name'         => $data['name'],
            'description'  => $data['description'],
            'price'        => $data['price'],
            // Kategori "seated" mulai dari 0 kuota; bertambah otomatis 1 per kursi
            // yang ditandai di editor denah.
            'quota'        => $isSeated ? 0 : (int) $data['quota'],
            'seating_mode' => $data['seating_mode'],
        ])->save();

        $message = $isSeated
            ? 'Kategori ditambahkan. Lanjut ke "Atur Denah Kursi" untuk menandai posisi kursinya.'
            : 'Kategori tiket berhasil ditambahkan.';

        return back()->with('success', $message);
    }

    public function destroy($categoryId)
    {
        $category = TicketCategory::findOrFail($categoryId);
        $event    = Event::findOrFail($category->event_id);

        $this->authorizeEvent($event);

        // Jangan hapus kategori yang sudah punya tiket (terjual, dipakai,
        // atau masih menunggu pembayaran) -- datanya milik pembeli sungguhan.
        if (Ticket::where('ticket_category_id', $category->id)->exists()) {
            return back()->with(
                'error',
                'Kategori ini tidak bisa dihapus karena sudah ada tiket yang terjual atau sedang menunggu pembayaran.'
            );
        }

        // Titik kursi ikut terhapus otomatis (foreign key cascade).
        $category->delete();

        return back()->with('success', 'Kategori tiket berhasil dihapus.');
    }
}
