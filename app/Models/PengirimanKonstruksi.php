<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengirimanKonstruksi extends Model
{
    protected $table = 'pengiriman_konstruksi';
    protected $guarded = [];
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'berkas_paths' => 'array',
            'berkas_names' => 'array',
            'laporan_paths' => 'array',
            'laporan_names' => 'array',
            'hasil_paths' => 'array',
            'hasil_konstruksi_paths' => 'array',
            'dikirim_at' => 'datetime',
            'laporan_at' => 'datetime',
            'hasil_perencanaan_at' => 'datetime',
            'hasil_konstruksi_at' => 'datetime',
            'pekerjaan_lengkap' => 'boolean',
            'pekerjaan_sesuai_wo' => 'boolean',
            'foto_terlampir' => 'boolean',
            'siap_dilanjutkan' => 'boolean',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function dikirimOleh()
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }
}
