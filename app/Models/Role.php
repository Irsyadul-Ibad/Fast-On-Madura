<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    public const DEFAULT_ULP_PERMISSIONS = [
        'dashboard.view',
        'pencarian.view',
        'notifikasi.view',
        'laporan.view',
        'pbpd.view',
        'perluasan.jtm.view',
        'perluasan.jtr.view',
        'tanpa.perluasan.view',
        'berkas.upload',
        'permintaan.create',
    ];

    public function permissions()
    
    {
    return $this->belongsToMany(Permission::class);
    }

    protected $fillable = [
        'name',
        'role_code',
        'type',
        'ulp_id',
    ];

    public function ulp()
    {
        return $this->belongsTo(Ulp::class);
    }

    public function effectivePermissions(): array
    {
        $this->loadMissing('permissions');
        $permissions = $this->permissions->pluck('name')->all();

        if ($this->type === 'ULP' && $this->ulp_id) {
            return array_values(array_unique(array_merge(self::DEFAULT_ULP_PERMISSIONS, $permissions)));
        }

        return $permissions;
    }
}
