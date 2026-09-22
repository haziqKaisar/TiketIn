<?php

namespace App\Http\Controllers;

use App\Jobs\SendETicketJob;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // =========================================================
    // 1. DASHBOARD STATISTIK
    // =========================================================
    public function dashboard()
    {
        $revenue = Order::where('status', 'PAID')
            ->sum('total_amount');

        $ticketsSold = Ticket::whereIn('status', [
            'AVAILABLE',
            'SCANNED',
        ])->count();

        $checkedIn = Ticket::where('status', 'SCANNED')
            ->count();

        return view('admin.dashboard', compact(
            'revenue',
            'ticketsSold',
            'checkedIn'
        ));
    }

    // =========================================================
    // 2. TAMPILKAN DAFTAR EVENT (READ)
    // =========================================================
    public function eventIndex()
    {
        $events = Event::orderBy('event_date', 'desc')
            ->get();

        return view('admin.events.index', compact('events'));
    }

    // =========================================================
    // 3. FORM TAMBAH EVENT (CREATE)
    // =========================================================
    public function eventCreate()
    {
        return view('admin.events.form');
    }

    // =========================================================
    // 4. SIMPAN EVENT BARU (STORE)
    // =========================================================
    public function eventStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'event_date'  => 'required|date',
            'poster'      => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        // Upload poster
        if ($request->hasFile('poster')) {
            $data['poster'] = $request->file('poster')
                ->store('posters', 'public');
        }

        Event::create($data);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil ditambahkan!');
    }

    // =========================================================
    // 5. FORM EDIT EVENT (EDIT)
    // =========================================================
    public function eventEdit(Event $event)
    {
        return view('admin.events.form', compact('event'));
    }

    // =========================================================
    // 6. SIMPAN PERUBAHAN EVENT (UPDATE)
    // =========================================================
    public function eventUpdate(Request $request, Event $event)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'location'    => 'required|string|max:255',
            'event_date'  => 'required|date',
            'poster'      => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'is_active'   => 'required|boolean',
        ]);

        // Upload poster baru
        if ($request->hasFile('poster')) {
            // Hapus poster lama
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }

            // Simpan poster baru
            $data['poster'] = $request->file('poster')
                ->store('posters', 'public');
        }

        $event->update($data);

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    // =========================================================
    // 7. HAPUS EVENT (DELETE)
    // =========================================================
    public function eventDestroy(Event $event)
    {
        // Hapus poster
        if ($event->poster) {
            Storage::disk('public')->delete($event->poster);
        }

        // Hapus event
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    // =========================================================
    // 8. TAMPILKAN SEMUA PESANAN
    // =========================================================
    public function orderIndex()
    {
        $orders = Order::with([
            'tickets.ticketCategory.event',
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    // =========================================================
    // 9. KONFIRMASI BAYAR MANUAL
    // =========================================================
    public function orderMarkPaid(Order $order)
    {
        if ($order->status === 'UNPAID') {
            // Ubah status order menjadi PAID
            $order->update(['status' => 'PAID']);

            // Ubah status semua tiket menjadi AVAILABLE
            Ticket::where('order_id', $order->id)->update(['status' => 'AVAILABLE']);

            // Kirim E-Ticket via Queue Job
            SendETicketJob::dispatch($order->id);

            return back()->with(
                'success',
                'Status pesanan berhasil diubah menjadi PAID! E-Ticket sedang dikirim.'
            );
        }

        return back()->with(
            'error',
            'Pesanan sudah diproses sebelumnya.'
        );
    }
}
