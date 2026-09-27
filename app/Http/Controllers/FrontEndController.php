<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    // =========================================================
    // 1. HALAMAN UTAMA
    // Menampilkan maksimal 3 event terdekat
    // =========================================================
    public function index()
    {
        $events = Event::with('organization')
            ->where('is_active', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->take(3)
            ->get();

        return view('frontend.index', compact('events'));
    }

    // =========================================================
    // 2. DETAIL EVENT
    // =========================================================
    public function show(Event $event)
    {
        $event->load('ticketCategories');

        return view('frontend.show', compact('event'));
    }

    // =========================================================
    // 3. HALAMAN E-TICKET
    // =========================================================
    public function ticket($merchant_ref)
    {
        $order = Order::where('merchant_ref', $merchant_ref)
            ->with([
                'tickets.ticketCategory.event',
            ])
            ->firstOrFail();

        // Cegah akses jika belum dibayar
        if ($order->status !== 'PAID') {
            abort(
                403,
                'Maaf, pesanan ini belum lunas atau sudah kadaluarsa.'
            );
        }

        return view('frontend.ticket', compact('order'));
    }

    // =========================================================
    // 4. HALAMAN SCANNER
    // =========================================================
    public function scanIndex()
    {
        return view('frontend.scan');
    }

    // =========================================================
    // 5. VALIDASI QR CODE
    // =========================================================
    public function scanValidate(Request $request)
    {
        $ticketCode = $request->ticket_code;

        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->with([
                'order',
                'ticketCategory.event',
            ])
            ->first();

        // Tiket tidak ditemukan
        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ TIKET TIDAK DITEMUKAN!',
            ], 404);
        }

        // Tiket sudah digunakan
        if ($ticket->status === 'SCANNED') {
            return response()->json([
                'status' => 'warning',
                'message' => '⚠️ TIKET SUDAH DIGUNAKAN!',
                'detail' => $ticket->order->customer_name
                    . ' (' . $ticket->ticketCategory->name . ')',
            ]);
        }

        // Tiket belum dibayar
        if ($ticket->status === 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => '❌ TIKET BELUM DIBAYAR!',
            ]);
        }

        // Tiket tersedia → tandai sudah digunakan
        $ticket->update([
            'status' => 'SCANNED',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ TIKET VALID! SILAKAN MASUK',
            'detail' => $ticket->order->customer_name
                . ' - ' . $ticket->ticketCategory->name,
        ]);
    }

    // =========================================================
    // 6. MARKETPLACE / JELAJAHI
    // Pencarian + pagination
    // =========================================================
    public function marketplace(Request $request)
    {
        $q = $request->query('q');

        $events = Event::with('organization')
            ->where('is_active', true)
            ->where('event_date', '>=', now())
            ->when($q, function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%")
                        ->orWhere('location', 'like', "%{$q}%");
                });
            })
            ->orderBy('event_date', 'asc')
            ->paginate(10)
            ->appends([
                'q' => $q,
            ]);

        return view('frontend.marketplace', [
            'events' => $events,
            'q' => $q,
        ]);
    }

    // =========================================================
    // 7. CEK TIKET LAMA
    // =========================================================
    // Method ini bisa dibiarkan jika masih ada bagian project
    // yang menggunakannya. Saat ini route cek tiket memakai
    // TicketController.

    public function checkTicketInput()
    {
        return view('frontend.check_ticket');
    }

    public function checkTicketSearch(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $orders = Order::where('customer_email', $email)
            ->with([
                'tickets.ticketCategory.event',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view(
            'frontend.check_ticket',
            compact('orders', 'email')
        );
    }
}
