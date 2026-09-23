<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('id')
                ->constrained()->cascadeOnDelete();
        });

        // Buat organisasi default untuk menampung SEMUA event yang sudah
        // ada sebelum fitur multi-tenant ini, supaya datanya tidak hilang.
        $adminEmail = DB::table('users')->where('role', 'super_admin')->value('email');

        $defaultOrgId = DB::table('organizations')->insertGetId([
            'name'       => 'Organisasi Utama',
            'slug'       => 'organisasi-utama',
            'email'      => $adminEmail ?? 'admin@tiketin.test',
            'status'     => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('events')->whereNull('organization_id')->update([
            'organization_id' => $defaultOrgId,
        ]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
        });

        DB::table('organizations')->where('slug', 'organisasi-utama')->delete();
    }
};
