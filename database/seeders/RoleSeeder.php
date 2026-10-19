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
            ['kode' => '51700', 'nama' => 'PAMEKASAN'],
            ['kode' => '51710', 'nama' => 'SUMENEP'],
            ['kode' => '51715', 'nama' => 'KEPULAUAN KANGEAN'],
            ['kode' => '51720', 'nama' => 'SAMPANG'],
            ['kode' => '51730', 'nama' => 'BANGKALAN'],
            ['kode' => '51740', 'nama' => 'KAMAL'],
            ['kode' => '51750', 'nama' => 'KETAPANG'],
            ['kode' => '51760', 'nama' => 'BLEGA'],
            ['kode' => '51780', 'nama' => 'WARU'],
            ['kode' => '51790', 'nama' => 'AMBUNTEN'],
        ];

        foreach ($ulps as $ulp) {
            Ulp::updateOrCreate(['kode' => $ulp['kode']], $ulp);
        }

        $roles = [
            // ULP
            ['role_code' => '51700', 'name' => 'ULP Pamekasan', 'type' => 'ULP'],
            ['role_code' => '51710', 'name' => 'ULP Sumenep',      'type' => 'ULP'],
            ['role_code' => '51715', 'name' => 'ULP Kepualauan kangean',   'type' => 'ULP'],
            ['role_code' => '51720', 'name' => 'ULP Sampang',      'type' => 'ULP'],
            ['role_code' => '51730', 'name' => 'ULP bangkalan',   'type' => 'ULP'],
            ['role_code' => '51740', 'name' => 'ULP Kamal',    'type' => 'ULP'],
            ['role_code' => '51750', 'name' => 'ULP Ketapang',   'type' => 'ULP'],
            ['role_code' => '51760', 'name' => 'ULP Blega', 'type' => 'ULP'],
            ['role_code' => '51780', 'name' => 'ULP Waru', 'type' => 'ULP'],
            ['role_code' => '51790', 'name' => 'ULP Ambunten', 'type' => 'ULP'],

            // VENDOR TIANG
            ['role_code' => '5170T1', 'name' => 'PT. JAYA BETON INDONESIA', 'type' => 'VENDOR'],
            ['role_code' => '5170T2', 'name' => 'PT. HUME SAKTI INDONESIA', 'type' => 'VENDOR'],
            ['role_code' => '5170T3', 'name' => 'PT. TONGGAK AMPUH', 'type' => 'VENDOR'],
            ['role_code' => '5170T4', 'name' => 'PT. TJAKRINDO MAS', 'type' => 'VENDOR'],
            ['role_code' => '5170T5', 'name' => 'PT. WIJAYA KARYA BETON', 'type' => 'VENDOR'],
            ['role_code' => '5170T6', 'name' => 'PT. SENTOSA SAKTI MAKMUR', 'type' => 'VENDOR'],
            ['role_code' => '5170T7', 'name' => 'PT. TJAKRINDO MAS', 'type' => 'VENDOR'],
            ['role_code' => '5170T8', 'name' => 'PT KENCANA TEKNIKATAMA SENTOSA', 'type' => 'VENDOR'],
            ['role_code' => '5170T9', 'name' => 'PT. MAKMUR JAYA', 'type' => 'VENDOR'],

            // VENDOR KONSTRUKSI
            ['role_code' => '5170K1', 'name' => 'PT. ARTHA NUSANTARA ELEKTRON', 'type' => 'VENDOR'],
            ['role_code' => '5170K2', 'name' => 'PT. SUMBER ANUGERAH AGUNG', 'type' => 'VENDOR'],
            ['role_code' => '5170K3', 'name' => 'PT. SURYA CAHAYA SEJAHTERA', 'type' => 'VENDOR'],
            ['role_code' => '5170K4', 'name' => 'PT. CAHAYA WANDA ELEKTRIKAL', 'type' => 'VENDOR'],
            ['role_code' => '5170K5', 'name' => 'PT. IFAMA NUANSA ASRI', 'type' => 'VENDOR'],
            ['role_code' => '5170K6', 'name' => 'PT. TERANG TIGA SAUDARA', 'type' => 'VENDOR'],
            ['role_code' => '5170K7', 'name' => 'PT. CAHAYA MARTA PERKASA', 'type' => 'VENDOR'],

            // UP3
            ['role_code' => '5170NPS',  'name' => 'NPS UP3', 'type' => 'UP3'],
            ['role_code' => '5170MAN', 'name' => 'Manager UP3',   'type' => 'UP3'],
            ['role_code' => '5170REN', 'name' => 'Perencanaan',   'type' => 'UP3'],
            ['role_code' => '5170KON', 'name' => 'Konstruksi',    'type' => 'UP3'],
            ['role_code' => '5170TE ', 'name' => 'Transaksi',     'type' => 'UP3'],
            ['role_code' => '5170JAR', 'name' => 'Jaringan',      'type' => 'UP3'],
            ['role_code' => '5170   ',    'name' => 'Administrator', 'type' => 'UP3'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_code' => $role['role_code']],
                $role + ['ulp_id' => Ulp::where('kode', $role['role_code'])->value('id')]
            );
        }
    }
}
