<?php

namespace App\Http\Controllers;

use App\Mail\TicketLookupMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class TicketController extends Controller
{
    /**
     * Tampilkan form pencarian tiket. Tidak ada hasil apa pun di sini lagi.
     */
    public function check()
    {
        return view('frontend.check_ticket');
    }

    /**
     * Terima email dari form, kirim link verifikasi ke email tsb.
     *
     * PENTING: responsnya SENGAJA dibuat sama persis baik email itu
     * ditemukan di database maupun tidak. Ini untuk mencegah orang
     * memakai form ini buat "mengetes" email mana saja yang pernah
     * beli tiket (email enumeration).
     */
    public function search(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');

        $hasOrders = Order::where('customer_email', $email)->exists();

        if ($hasOrders) {
            $signedUrl = URL::temporarySignedRoute(
                'ticket.check.verify',
                now()->addMinutes(30),
                ['email' => $email]
            );

            Mail::to($email)->send(new TicketLookupMail($email, $signedUrl));
        }

        // Sama-sama redirect ke sini walau email tidak ditemukan.
        return back()->with('lookup_sent', true);
    }

    /**
     * Dibuka lewat link di email. Middleware "signed" di route otomatis
     * menolak (403) kalau link kedaluwarsa atau parameternya diotak-atik.
     */
    public function verify(Request $request)
    {
        $email = $request->query('email');

        $orders = Order::with('tickets.ticketCategory.event')
            ->where('customer_email', $email)
            ->latest()
            ->get();

        return view('frontend.check_ticket_result', [
            'email' => $email,
            'orders' => $orders,
        ]);
    }
}
