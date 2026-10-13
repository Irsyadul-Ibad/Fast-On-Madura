<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->dropUnique('pelanggan_pbpd_no_agenda_ulp_id_unique');
            $table->index(['ulp_id', 'no_agenda']);
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->dropIndex(['ulp_id', 'no_agenda']);
            $table->unique(['no_agenda', 'ulp_id']);
        });
    }
};
