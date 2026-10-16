<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_material', function (Blueprint $table) {
            $table->string('jenis_konduktor_tm', 30)->nullable()->after('jenis_konduktor');
            $table->unsignedInteger('jml_konduktor_tm')->nullable()->after('jenis_konduktor_tm');
            $table->string('jenis_konduktor_tr', 30)->nullable()->after('jml_konduktor_tm');
            $table->unsignedInteger('jml_konduktor_tr')->nullable()->after('jenis_konduktor_tr');
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_material', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_konduktor_tm',
                'jml_konduktor_tm',
                'jenis_konduktor_tr',
                'jml_konduktor_tr',
            ]);
        });
    }
};
