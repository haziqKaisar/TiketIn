<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel Order (Siapa yang beli)
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Relasi ke tabel Kategori Tiket (Tiket jenis apa)
            $table->foreignId('ticket_category_id')->constrained()->cascadeOnDelete();

            $table->string('ticket_code')->unique(); // Kode unik untuk di-scan (misal: TKT-X7B9K)
            $table->string('status')->default('AVAILABLE'); // AVAILABLE (bisa dipakai) atau SCANNED (sudah masuk)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
