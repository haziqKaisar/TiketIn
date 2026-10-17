<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Denah venue (gambar) per event, diupload organisasi.
        Schema::table('events', function (Blueprint $table) {
            $table->string('seat_map_image')->nullable();
        });

        // 2. Tiap kategori tiket boleh "kuota biasa" (default, seperti sekarang)
        //    atau "seated" (pembeli memilih kursi spesifik di denah).
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->enum('seating_mode', ['quota', 'seated'])->default('quota')->after('quota');
        });

        // 3. Titik-titik kursi yang ditandai organisasi di atas gambar denah.
        //    x & y disimpan dalam PERSEN (0-100) dari lebar/tinggi gambar,
        //    supaya posisinya tetap pas di layar ukuran apa pun.
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30);
            $table->decimal('x', 5, 2);
            $table->decimal('y', 5, 2);
            $table->timestamps();

            $table->unique(['ticket_category_id', 'label']);
        });

        // 4. Tiket yang dibeli di kategori "seated" menunjuk ke kursi tertentu.
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('seat_id')->nullable()->after('ticket_category_id')
                ->constrained('seats')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seat_id');
        });

        Schema::dropIfExists('seats');

        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn('seating_mode');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('seat_map_image');
        });
    }
};
