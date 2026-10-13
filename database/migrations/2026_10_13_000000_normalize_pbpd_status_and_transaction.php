<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->string('status', 30)->nullable()->change();
            $table->string('jenis_transaksi', 20)->nullable()->change();
        });

        // Data hasil import versi lama menyimpan status proses di transaksi.
        DB::statement("\n            UPDATE pelanggan_pbpd\n            SET status = UPPER(TRIM(jenis_transaksi)), jenis_transaksi = NULL\n            WHERE UPPER(TRIM(jenis_transaksi)) IN ('CETAK PK', 'PENGESAHAN PDL', 'PDL AWAL')\n        ");

    }

    public function down(): void
    {
        DB::statement("\n            UPDATE pelanggan_pbpd\n            SET jenis_transaksi = status\n            WHERE UPPER(TRIM(status)) IN ('CETAK PK', 'PENGESAHAN PDL', 'PDL AWAL')\n              AND (jenis_transaksi IS NULL OR TRIM(jenis_transaksi) = '')\n        ");

        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->string('status', 10)->nullable()->change();
            $table->string('jenis_transaksi', 15)->nullable()->change();
        });
    }
};
