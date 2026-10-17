<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Seat;
use App\Models\Ticket;

class SeatMapService
{
    /**
     * Semua kursi dari kategori bermode "seated" milik event ini, lengkap
     * dengan penanda 'taken' (sudah dipesan / sedang ditahan pembayaran).
     * Dipakai oleh halaman beli tiket (publik) dan editor denah (admin).
     */
    public function forEvent(Event $event): array
    {
        $seatedCategoryIds = $event->ticketCategories
            ->where('seating_mode', 'seated')
            ->pluck('id');

        if ($seatedCategoryIds->isEmpty()) {
            return [];
        }

        $seats = Seat::whereIn('ticket_category_id', $seatedCategoryIds)
            ->get(['id', 'ticket_category_id', 'label', 'x', 'y'])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $takenSeatIds = array_flip(
            Ticket::whereIn('seat_id', $seats->pluck('id'))->pluck('seat_id')->all()
        );

        return $seats->map(fn (Seat $seat) => [
            'id'          => $seat->id,
            'category_id' => $seat->ticket_category_id,
            'label'       => $seat->label,
            'x'           => $seat->x,
            'y'           => $seat->y,
            'taken'       => isset($takenSeatIds[$seat->id]),
        ])->all();
    }
}
