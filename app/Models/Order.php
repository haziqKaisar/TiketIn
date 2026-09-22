<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'merchant_ref',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'payment_method',
        'status',
        'checkout_url',
        'eticket_sent_at', // Menambahkan kolom ini agar siap diisi via mass assignment
    ];

    /**
     * Casting tipe data otomatis untuk atribut tertentu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'eticket_sent_at' => 'datetime',
    ];

    /**
     * Relasi: 1 Order bisa menghasilkan banyak Tiket.
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
