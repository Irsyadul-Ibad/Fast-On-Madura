<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stok_tiang', function (Blueprint $table) {
            $table->unsignedInteger('stok_tm_11')->default(0)->after('stok_tm');
            $table->unsignedInteger('stok_tm_13')->default(0)->after('stok_tm_11');
        });

        // Stok TM lama belum membedakan jenis tiang, sehingga dipertahankan
        // sebagai stok jenis 11 agar tidak hilang saat struktur dipisah.
        DB::table('stok_tiang')->update(['stok_tm_11' => DB::raw('stok_tm')]);
    }

    public function down(): void
    {
        Schema::table('stok_tiang', function (Blueprint $table) {
            $table->dropColumn(['stok_tm_11', 'stok_tm_13']);
        });
    }
};
