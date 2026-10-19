<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulp', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // 51700 dst
            $table->string('nama', 50);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('ulp_id')->nullable()->constrained('ulp')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ulp_id');
        });

        Schema::dropIfExists('ulp');
    }
};