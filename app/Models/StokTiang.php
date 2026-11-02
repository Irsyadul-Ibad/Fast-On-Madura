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
        'stok_tm_11',
        'stok_tm_13',
    ];

    protected function casts(): array
    {
        return [
            'stok_tr' => 'integer',
            'stok_tm' => 'integer',
            'stok_tm_11' => 'integer',
            'stok_tm_13' => 'integer',
        ];
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
