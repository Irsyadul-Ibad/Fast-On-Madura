<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE permintaan_material
            SET jenis_konduktor_tm = jenis_konduktor,
                jml_konduktor_tm = jml_konduktor
            WHERE jenis_konduktor_tm IS NULL
              AND jml_konduktor_tm IS NULL
              AND (jenis_konduktor IS NOT NULL OR jml_konduktor IS NOT NULL)
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            UPDATE permintaan_material
            SET jenis_konduktor_tm = NULL,
                jml_konduktor_tm = NULL
            WHERE jenis_konduktor IS NOT NULL
              AND jenis_konduktor_tm = jenis_konduktor
              AND jml_konduktor_tm = jml_konduktor
        SQL);
    }
};
