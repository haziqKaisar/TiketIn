<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'name', 'price', 'quota'];

    // Relasi: Kategori Tiket ini milik 1 Event
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
