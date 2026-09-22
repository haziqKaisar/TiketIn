<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_ref')->unique(); // Nomor Invoice unik (misal: INV-123456)
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->integer('total_amount'); // Total harga yang harus dibayar
            $table->string('payment_method')->nullable(); // Metode bayar (QRIS, BRIVA, dll)
            $table->string('status')->default('UNPAID'); // UNPAID, PAID, EXPIRED, FAILED
            $table->string('checkout_url')->nullable(); // Link ke halaman Tripay
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
