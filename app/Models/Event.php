<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'location', 'event_date', 'poster', 'is_active'];

    // Relasi: 1 Event punya banyak Kategori Tiket
    public function ticketCategories()
    {
        return $this->hasMany(TicketCategory::class);
    }
}
