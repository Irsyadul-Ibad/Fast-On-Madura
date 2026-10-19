<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->json('nps_wo_paths')->nullable();
            $table->timestamp('nps_wo_at')->nullable();
            $table->json('nps_checklist_paths')->nullable();
            $table->timestamp('nps_checklist_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan_pbpd', function (Blueprint $table) {
            $table->dropColumn([
                'nps_wo_paths',
                'nps_wo_at',
                'nps_checklist_paths',
                'nps_checklist_at',
            ]);
        });
    }
};
