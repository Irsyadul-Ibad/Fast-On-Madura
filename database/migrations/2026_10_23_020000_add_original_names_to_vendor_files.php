<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->string('wo_tiang_name')->nullable();
        });

        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->json('berkas_names')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->dropColumn('berkas_names');
        });

        Schema::table('pengiriman_vendor', function (Blueprint $table) {
            $table->dropColumn('wo_tiang_name');
        });
    }
};
