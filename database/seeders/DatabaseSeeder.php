<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil EventSeeder yang sudah dibuat
        $this->call(EventSeeder::class);

        // Buat Akun Panitia
        User::create([
            'name' => 'Panitia Gate 1',
            'email' => 'admin@tiketin.com',
            'password' => Hash::make('password123'), // Password login
        ]);
    }
}
