<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Batalkan order UNPAID: kembalikan kuota ke kategori tiketnya,
     * hapus baris Ticket yang masih PENDING, ubah status order.
     *
     * Dipakai di DUA tempat: (1) webhook callback Tripay saat status
     * EXPIRED/FAILED, dan (2) command orders:expire-stale yang
     * membersihkan order yang ditinggal begitu saja tanpa pernah
     * dapat webhook sama sekali.
     */
    public function releaseUnpaidOrder(Order $order, string $status = 'EXPIRED'): void
    {
        DB::transaction(function () use ($order, $status) {
            // Kunci baris order ini supaya tidak diproses dobel kalau
            // command & webhook kebetulan jalan bersamaan.
            $locked = Order::where('id', $order->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'UNPAID') {
                return; // sudah diproses lebih dulu, jangan diapa-apain lagi
            }

            $tickets = Ticket::where('order_id', $locked->id)->lockForUpdate()->get();

            if ($tickets->isNotEmpty()) {
                $categoryId = $tickets->first()->ticket_category_id;

                TicketCategory::where('id', $categoryId)
                    ->lockForUpdate()
                    ->increment('quota', $tickets->count());

                Ticket::where('order_id', $locked->id)->delete();
            }

            $locked->update(['status' => $status]);
        });
    }
}
