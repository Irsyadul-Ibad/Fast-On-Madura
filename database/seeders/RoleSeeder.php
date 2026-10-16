<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Ulp;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $ulps = [
            ['kode' => '51801', 'nama' => 'ULP Bojonegoro'],
            ['kode' => '51802', 'nama' => 'ULP Tuban'],
            ['kode' => '51803', 'nama' => 'ULP Lamongan'],
            ['kode' => '51804', 'nama' => 'ULP Babat'],
            ['kode' => '51805', 'nama' => 'ULP Padangan'],
            ['kode' => '51806', 'nama' => 'ULP Brondon'],
            ['kode' => '51807', 'nama' => 'ULP Jatirogo'],
            ['kode' => '51808', 'nama' => 'ULP Sumberrejo'],
        ];

        foreach ($ulps as $ulp) {
            Ulp::updateOrCreate(['kode' => $ulp['kode']], $ulp);
        }

        $roles = [
            // ULP
            ['role_code' => '51801', 'name' => 'ULP Bojonegoro', 'type' => 'ULP'],
            ['role_code' => '51802', 'name' => 'ULP Tuban',      'type' => 'ULP'],
            ['role_code' => '51803', 'name' => 'ULP Lamongan',   'type' => 'ULP'],
            ['role_code' => '51804', 'name' => 'ULP Babat',      'type' => 'ULP'],
            ['role_code' => '51805', 'name' => 'ULP Padangan',   'type' => 'ULP'],
            ['role_code' => '51806', 'name' => 'ULP Brondon',    'type' => 'ULP'],
            ['role_code' => '51807', 'name' => 'ULP Jatirogo',   'type' => 'ULP'],
            ['role_code' => '51808', 'name' => 'ULP Sumberrejo', 'type' => 'ULP'],

            // VENDOR TIANG
            ['role_code' => '5180T1', 'name' => 'PT. JAYA BETON INDONESIA', 'type' => 'VENDOR'],
            ['role_code' => '5180T2', 'name' => 'PT. HUME SAKTI INDONESIA', 'type' => 'VENDOR'],
            ['role_code' => '5180T3', 'name' => 'PT. TONGGAK AMPUH', 'type' => 'VENDOR'],
            ['role_code' => '5180T4', 'name' => 'PT. TJAKRINDO MAS', 'type' => 'VENDOR'],
            ['role_code' => '5180T5', 'name' => 'PT. WIJAYA KARYA BETON', 'type' => 'VENDOR'],
            ['role_code' => '5180T6', 'name' => 'PT. SENTOSA SAKTI MAKMUR', 'type' => 'VENDOR'],
            ['role_code' => '5180T7', 'name' => 'PT. TJAKRINDO MAS', 'type' => 'VENDOR'],
            ['role_code' => '5180T8', 'name' => 'PT KENCANA TEKNIKATAMA SENTOSA', 'type' => 'VENDOR'],
            ['role_code' => '5180T9', 'name' => 'PT. MAKMUR JAYA', 'type' => 'VENDOR'],

            // VENDOR KONSTRUKSI
            ['role_code' => '5180K1', 'name' => 'PT. JAYA WIJAYA', 'type' => 'VENDOR'],
            ['role_code' => '5180K2', 'name' => 'PT. KONSTRUKSI JAYA', 'type' => 'VENDOR'],

            // UP3
            ['role_code' => '5180NPS',  'name' => 'NPS UP3', 'type' => 'UP3'],
            ['role_code' => '5180MAN', 'name' => 'Manager UP3',   'type' => 'UP3'],
            ['role_code' => '5180REN', 'name' => 'Perencanaan',   'type' => 'UP3'],
            ['role_code' => '5180KON', 'name' => 'Konstruksi',    'type' => 'UP3'],
            ['role_code' => '5180TE', 'name' => 'Transaksi',     'type' => 'UP3'],
            ['role_code' => '5180JAR', 'name' => 'Jaringan',      'type' => 'UP3'],
            ['role_code' => '5180',    'name' => 'Administrator', 'type' => 'UP3'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_code' => $role['role_code']],
                $role + ['ulp_id' => Ulp::where('kode', $role['role_code'])->value('id')]
            );
        }
    }
}
