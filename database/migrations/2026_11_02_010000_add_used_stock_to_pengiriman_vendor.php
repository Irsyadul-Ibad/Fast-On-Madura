<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->unsignedInteger('stok_tr_dipakai')->nullable()->after('vendor_id');
            $table->unsignedInteger('stok_tm_dipakai')->nullable()->after('stok_tr_dipakai');
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->dropColumn(['stok_tr_dipakai', 'stok_tm_dipakai']);
        });
    }
};
