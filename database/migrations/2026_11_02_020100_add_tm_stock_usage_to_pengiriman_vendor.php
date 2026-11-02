<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->unsignedInteger('stok_tm_11_dipakai')->nullable()->after('stok_tm_dipakai');
            $table->unsignedInteger('stok_tm_13_dipakai')->nullable()->after('stok_tm_11_dipakai');
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->dropColumn(['stok_tm_11_dipakai', 'stok_tm_13_dipakai']);
        });
    }
};
