<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_material', function (Blueprint $table) {
            $table->string('jenis_tiang_tm', 30)->nullable()->after('jenis_tiang');
            $table->unsignedInteger('jml_tiang_tm')->nullable()->after('jenis_tiang_tm');
            $table->string('jenis_tiang_tr', 30)->nullable()->after('jml_tiang_tm');
            $table->unsignedInteger('jml_tiang_tr')->nullable()->after('jenis_tiang_tr');
        });

        DB::table('permintaan_material')->whereNull('jenis_tiang_tm')->update([
            'jenis_tiang_tm' => DB::raw('jenis_tiang'),
            'jml_tiang_tm' => DB::raw('jml_tiang'),
        ]);
    }

    public function down(): void
    {
        Schema::table('permintaan_material', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_tiang_tm',
                'jml_tiang_tm',
                'jenis_tiang_tr',
                'jml_tiang_tr',
            ]);
        });
    }
};
