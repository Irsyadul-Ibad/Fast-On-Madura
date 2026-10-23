<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman_konstruksi', function (Blueprint $table) {
            $table->json('berkas_names')->nullable();
            $table->json('laporan_names')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengiriman_konstruksi', function (Blueprint $table) {
            $table->dropColumn(['berkas_names', 'laporan_names']);
        });
    }
};
