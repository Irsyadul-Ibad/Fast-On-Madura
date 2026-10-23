<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanVendor extends Model
{
    protected $table = 'laporan_vendor';
    protected $guarded = [];
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'dikirim_at' => 'datetime',
            'berkas_paths' => 'array',
            'berkas_names' => 'array',
            'berkas_hasil_paths' => 'array',
            'berkas_hasil_names' => 'array',
            'berkas_hasil_at' => 'datetime',
            'vendor_report_at' => 'datetime',
        ];
    }
}
