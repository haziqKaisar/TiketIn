<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            // Menghubungkan kategori tiket dengan event (Foreign Key)
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // Kategori (contoh: VIP, Festival, Tribun)
            $table->integer('price'); // Harga tiket
            $table->integer('quota'); // Jumlah maksimal tiket yang bisa dibeli
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
