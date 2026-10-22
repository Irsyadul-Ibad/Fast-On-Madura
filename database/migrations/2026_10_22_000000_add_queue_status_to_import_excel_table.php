<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_excel', function (Blueprint $table) {
            $table->string('status', 20)->default('completed')->index();
            $table->unsignedInteger('baru')->default(0);
            $table->unsignedInteger('diperbarui')->default(0);
            $table->unsignedInteger('jml_gagal')->default(0);
            $table->json('failed_rows')->nullable();
            $table->text('error_message')->nullable();
            $table->string('file_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('import_excel', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'baru',
                'diperbarui',
                'jml_gagal',
                'failed_rows',
                'error_message',
                'file_path',
            ]);
        });
    }
};
