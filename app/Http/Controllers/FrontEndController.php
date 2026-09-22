<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    // Menampilkan daftar event di halaman utama
    public function index()
    {
        $events = Event::where('is_active', true)
            ->orderBy('event_date', 'asc')
            ->get();

        return view('frontend.index', compact('events'));
    }

    // Menampilkan detail event dan pilihan tiket
    public function show(Event $event)
    {
        // Ambil data event beserta kategori tiketnya
        $event->load('ticketCategories');

        return view('frontend.show', compact('event'));
    }

    // Menampilkan halaman e-ticket jika sudah lunas
    public function ticket($merchant_ref)
    {
        // Cari transaksi beserta data tiket dan event-nya
        $order = Order::where('merchant_ref', $merchant_ref)
            ->with([
                'tickets.ticketCategory.event'
            ])
            ->firstOrFail();

        // Cegah akses jika belum dibayar
        if ($order->status !== 'PAID') {
            abort(403, 'Maaf, pesanan ini belum lunas atau sudah kadaluarsa.');
        }

        return view('frontend.ticket', compact('order'));
    }

    // Menampilkan halaman scanner
    public function scanIndex()
    {
        return view('frontend.scan');
    }

    // Memproses validasi QR Code dari kamera (via AJAX)
    public function scanValidate(Request $request)
    {
        $ticketCode = $request->ticket_code;

        $ticket = Ticket::where('ticket_code', $ticketCode)
            ->with([
                'order',
                'ticketCategory.event'
            ])
            ->first();

        // Tiket tidak ditemukan
        if (!$ticket) {
            return response()->json([
                'status' => 'error',
                'message' => '❌ TIKET TIDAK DITEMUKAN!'
            ], 404);
        }

        // Tiket sudah digunakan
        if ($ticket->status === 'SCANNED') {
            return response()->json([
                'status' => 'warning',
                'message' => '⚠️ TIKET SUDAH DIGUNAKAN!',
                'detail' => $ticket->order->customer_name
                    . ' (' . $ticket->ticketCategory->name . ')'
            ]);
        }

        // Tiket belum dibayar
        if ($ticket->status === 'PENDING') {
            return response()->json([
                'status' => 'error',
                'message' => '❌ TIKET BELUM DIBAYAR!'
            ]);
        }

        // Jika status AVAILABLE, ubah menjadi SCANNED
        $ticket->update([
            'status' => 'SCANNED'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => '✅ TIKET VALID! SILAKAN MASUK',
            'detail' => $ticket->order->customer_name
                . ' - ' . $ticket->ticketCategory->name
        ]);
    }

    // Menampilkan halaman pencarian tiket
    public function checkTicketInput()
    {
        return view('frontend.check_ticket');
    }

    // Memproses pencarian tiket berdasarkan email
    public function checkTicketSearch(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $orders = Order::where('customer_email', $email)
            ->with([
                'tickets.ticketCategory.event'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.check_ticket', compact('orders', 'email'));
    }
}
