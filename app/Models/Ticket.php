<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'ticket_category_id', 'ticket_code', 'status'];

    // Relasi: Tiket ini milik transaksi yang mana?
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi: Tiket ini kategorinya apa?
    public function ticketCategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }
}

