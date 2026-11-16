<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->string('status_kelayakan', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->dropColumn('status_kelayakan');
        });
    }
};
