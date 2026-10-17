<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'ticket_category_id',
        'label',
        'x',
        'y',
    ];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
    ];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'ticket_category_id');
    }

    /**
     * Tiket yang menempati kursi ini (kalau ada). Selama baris Ticket-nya
     * ada (PENDING / AVAILABLE / SCANNED), kursi dianggap TERISI. Begitu
     * order kadaluarsa dan tiketnya dihapus (OrderService), kursi bebas lagi.
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }
}
