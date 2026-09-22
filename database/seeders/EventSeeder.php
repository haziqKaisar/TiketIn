<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Membuat Event
        $event = Event::create([
            'name' => 'Pensi Puncak Harmoni 2026',
            'description' => 'Acara puncak pentas seni tahunan yang dimeriahkan oleh band lokal dan artis ibukota. Syarat masuk: Wajib membawa identitas asli.',
            'location' => 'Stadion Utama, Lapangan Terbuka',
            'event_date' => Carbon::now()->addDays(30), // Diatur 30 hari dari sekarang
            'poster' => 'default-poster.jpg',
            'is_active' => true,
        ]);

        // 2. Membuat Kategori Tiket untuk Event tersebut
        TicketCategory::create([
            'event_id' => $event->id,
            'name' => 'Festival (Berdiri)',
            'price' => 75000,
            'quota' => 200,
        ]);

        TicketCategory::create([
            'event_id' => $event->id,
            'name' => 'VIP (Duduk)',
            'price' => 150000,
            'quota' => 50,
        ]);
    }
}
