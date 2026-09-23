<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'org_admin'])->default('org_admin')->after('id');
            $table->foreignId('organization_id')->nullable()->after('role')
                ->constrained()->nullOnDelete();
        });

        // PENTING: jadikan SEMUA akun yang sudah ada sekarang sebagai
        // super_admin, supaya akun admin lama tetap bisa akses semuanya
        // seperti biasa (tidak ada yang ke-lock out).
        DB::table('users')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn('role');
        });
    }
};
