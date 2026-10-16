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
            '5180NPS' => array_merge($umum, ['pbpd.view'], $perluasan, ['pbpd.upload','permintaan.create'] ),
            '5180MAN' => array_merge($umum, ['pbpd.view'], $perluasan, ['pengoperasian.view', 'laporan.view']),
            '5180REN' => array_merge($umum, $perluasan, ['pbpd.view','perencanaan.process', 'berkas.upload']),
            '5180KON' => array_merge($umum, $perluasan, ['pbpd.view', 'konstruksi.process', 'berkas.upload']),
            '5180TE' => array_merge($umum, $perluasan, ['pbpd.view', 'pengoperasian.upload']),
            '5180JAR' => array_merge($umum, ['perluasan.jtm.view', 'perluasan.jtr.view'], ['pbpd.view', 'jaringan.upload']),
        ];

        foreach (Role::whereIn('role_code', ['5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9'])->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', ['laporan.view', 'vendor.tiang.view', 'vendor.tiang.process', 'vendor.tiang.history'])->pluck('id'));
        }
        foreach (Role::whereIn('role_code', ['5180K1', '5180K2'])->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', ['laporan.view', 'vendor.konstruksi.view', 'vendor.konstruksi.process', 'vendor.konstruksi.history'])->pluck('id'));
        }

        $ulp = array_merge($umum, ['pbpd.view'], $perluasan, ['berkas.upload', 'permintaan.create'], ['laporan.view']);

        foreach (Role::where('type', 'ULP')
            ->whereNotIn('role_code', ['5180T1', '5180T2', '5180T3', '5180T4', '5180T5', '5180T6', '5180T7', '5180T8', '5180T9', '5180K1', '5180K2'])
            ->get() as $role) {
            $role->permissions()->sync(Permission::whereIn('name', $ulp)->pluck('id'));
        }

        foreach ($map as $code => $names) {
            Role::where('role_code', $code)->first()
                ?->permissions()->sync(Permission::whereIn('name', $names)->pluck('id'));
        }

        // Role 5180 menggunakan tampilan dan privilege yang sama dengan Manager UP3.
        $managerUp3Permissions = $map['5180MAN'] ?? [];
        Role::where('role_code', '5180')->first()
            ?->permissions()->sync(Permission::whereIn('name', $managerUp3Permissions)->pluck('id'));
    }
}
