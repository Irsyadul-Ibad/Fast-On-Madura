<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->json('berkas_hasil_names')->nullable()->after('berkas_hasil_paths');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->dropColumn('berkas_hasil_names');
        });
    }
};
