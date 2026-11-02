<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokTiang extends Model
{
    protected $table = 'stok_tiang';

    protected $fillable = [
        'vendor_id',
        'stok_tr',
        'stok_tm',
    ];

    protected function casts(): array
    {
        return [
            'stok_tr' => 'integer',
            'stok_tm' => 'integer',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
