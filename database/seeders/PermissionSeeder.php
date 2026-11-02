<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Sesuaikan dengan role untuk menampilkan menu di sidebar
            'dashboard.view'        => 'Melihat dashboard',
            'pbpd.upload'           => 'Upload Excel data PB/PD',
            'pbpd.view'             => 'Melihat data PB/PD',
            'perluasan.jtm.view'    => 'Melihat Perluasan JTM',
            'perluasan.jtr.view'    => 'Melihat Perluasan JTR',
            'tanpa.perluasan.view'  => 'Melihat Tanpa Perluasan',
            'pengoperasian.view'    => 'Melihat Pengoperasian',
            'pencarian.view'        => 'Melihat Pencarian',
            'notifikasi.view'       => 'Melihat Notifikasi',
            'laporan.view'          => 'Melihat Laporan',

            // Aksi di dalam halaman
            'berkas.upload'         => 'Upload berkas/foto',
            'permintaan.create'     => 'Mengisi & mengirim form permintaan ke perencanaan',
            'perencanaan.process'   => 'Memproses perencanaan & kirim ke vendor tiang',
            'konstruksi.process'    => 'Memproses konstruksi & kirim ke vendor konstruksi',
            'pengoperasian.upload'  => 'Upload berkas/foto pengoperasian',
            'jaringan.upload'       => 'Upload berkas/foto jaringan',
            'vendor.tiang.view'      => 'Melihat agenda vendor tiang',
            'vendor.tiang.process'   => 'Mengirim laporan vendor tiang',
            'vendor.tiang.history'   => 'Melihat riwayat pengiriman vendor tiang',
            'stok.tiang.view'         => 'Melihat stok tiang',
            'stok.tiang.manage'       => 'Menginput stok tiang per vendor',
            'vendor.konstruksi.view' => 'Melihat agenda vendor konstruksi',
            'vendor.konstruksi.process' => 'Mengirim laporan vendor konstruksi',
            'vendor.konstruksi.history' => 'Melihat riwayat vendor konstruksi',
        ];

        foreach ($permissions as $name => $desc) {
            Permission::updateOrCreate(['name' => $name], ['description' => $desc]);
        }

        // Privilege setiap ULP (sama untuk 8 ULP, datanya dibatasi per ULP nanti)
        //$ulp = ['dashboard.view', 'pbpd.view', 'berkas.upload',
        //        'permintaan.create', 'permintaan.view'];

        $umum = ['dashboard.view', 'pencarian.view', 'notifikasi.view', 'laporan.view'];
        $perluasan = ['perluasan.jtm.view', 'perluasan.jtr.view', 'tanpa.perluasan.view'];

        $map = [
            '5170NPS' => array_merge($umum, ['pbpd.view'], $perluasan, ['pbpd.upload', 'permintaan.create', 'berkas.upload'] ),
            '5170MAN' => array_merge($umum, ['pbpd.view'], $perluasan, ['pengoperasian.view', 'laporan.view']),
            '5170REN' => array_merge($umum, $perluasan, ['pbpd.view','perencanaan.process', 'berkas.upload', 'stok.tiang.view', 'stok.tiang.manage']),
            '5170KON' => array_merge($umum, $perluasan, ['pbpd.view', 'konstruksi.process', 'berkas.upload']),
            '5170TE' => array_merge($umum, $perluasan, ['pbpd.view', 'pengoperasian.upload']),
            '5170JAR' => array_merge($umum, ['perluasan.jtm.view', 'perluasan.jtr.view'], ['pbpd.view', 'jaringan.upload']),
        ];

        foreach (Role::whereIn('role_code', ['5170T1', '5170T2', '5170T3', '5170T4', '5170T5', '5170T6', '5170T7', '5170T8', '5170T9'])->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', ['laporan.view', 'vendor.tiang.view', 'vendor.tiang.process', 'vendor.tiang.history'])->pluck('id'));
        }
        foreach (Role::whereIn('role_code', ['5170K1', '5170K2', '5170K3', '5170K4', '5170K5', '5170K6', '5170K7'])->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', ['laporan.view', 'vendor.konstruksi.view', 'vendor.konstruksi.process', 'vendor.konstruksi.history'])->pluck('id'));
        }

        $ulp = array_merge($umum, ['pbpd.view'], $perluasan, ['berkas.upload', 'permintaan.create'], ['laporan.view']);

        foreach (Role::where('type', 'ULP')
            ->whereNotIn('role_code', ['5170T1', '5170T2', '5170T3', '5170T4', '5170T5', '5170T6', '5170T7', '5170T8', '5170T9', '5170K1', '5170K2'])
            ->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', $ulp)->pluck('id'));
        }

        foreach ($map as $code => $names) {
            Role::where('role_code', $code)->first()
                ?->permissions()->sync(Permission::whereIn('name', $names)->pluck('id'));
        }

        // Role 5170 menggunakan tampilan dan privilege yang sama dengan Manager UP3.
        $managerUp3Permissions = $map['5170MAN'] ?? [];
        Role::where('role_code', '5170')->first()
            ?->permissions()->sync(Permission::whereIn('name', $managerUp3Permissions)->pluck('id'));
    }
}
