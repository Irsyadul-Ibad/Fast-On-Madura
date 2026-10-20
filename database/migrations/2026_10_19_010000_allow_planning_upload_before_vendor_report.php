<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->timestamp('vendor_report_at')->nullable()->after('dikirim_at');
        });

        // Data lama sudah merupakan laporan vendor; tandai sebagai laporan aktual.
        DB::table('laporan_vendor')
            ->whereNull('vendor_report_at')
            ->update(['vendor_report_at' => DB::raw('dikirim_at')]);
    }

    public function down(): void
    {
        Schema::table('laporan_vendor', function (Blueprint $table) {
            $table->dropColumn('vendor_report_at');
        });
    }
};
