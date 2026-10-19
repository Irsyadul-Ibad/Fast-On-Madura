<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengirimanVendor extends Model
{
    protected $table = 'pengiriman_vendor';
    protected $guarded = [];
    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['dikirim_at' => 'datetime'];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function pelanggan()
    {
        return $this->belongsTo(PelangganPbpd::class, 'pelanggan_id');
    }
}
