<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Judul acara (contoh: Pensi Skansa 2026)
            $table->text('description')->nullable(); // Deskripsi atau S&K acara
            $table->string('location'); // Lokasi acara
            $table->dateTime('event_date'); // Tanggal dan jam pelaksanaan
            $table->string('poster')->nullable(); // Nama file gambar poster
            $table->boolean('is_active')->default(true); // Status event buka/tutup
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
