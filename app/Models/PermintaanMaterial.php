<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanMaterial extends Model
{
    protected $table = 'permintaan_material';
    protected $guarded = [];
    public $timestamps = false;

    protected function casts(): array
    {
        return ['dikirim_at' => 'datetime'];
    }

    public function getJenisKonduktorAttribute($value): ?string
    {
        $tm = $this->jenis_konduktor_tm;
        $tr = $this->jenis_konduktor_tr;
        if ($tm && $tr) return "TM: {$tm} | TR: {$tr}";
        return $tm ?: $tr ?: $value;
    }

    public function getJmlKonduktorAttribute($value): ?string
    {
        $tm = $this->jml_konduktor_tm;
        $tr = $this->jml_konduktor_tr;
        if ($tm !== null && $tr !== null) return "TM: {$tm} | TR: {$tr}";
        return $tm !== null ? (string) $tm : ($tr !== null ? (string) $tr : ($value !== null ? (string) $value : null));
    }

    public function pelanggan()
    {
        return $this->belongsTo(PelangganPbpd::class, 'pelanggan_id');
    }
}
