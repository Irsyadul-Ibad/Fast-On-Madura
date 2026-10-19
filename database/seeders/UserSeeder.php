<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Role::orderBy('id')->get() as $role) {
            $user = User::updateOrCreate(
                ['user_id' => $role->role_code],
                [
                    'password' => Hash::make('password'),
                    'role_id'  => $role->id,
                    'aktif'    => true,
                ]
            );

            if (in_array($role->role_code, ['5170T1', '5170T2', '5170T3', '5170T4', '5170T5', '5170T6', '5170T7', '5170T8', '5170T9'], true)) {
                Vendor::updateOrCreate(
                    ['user_id' => $user->id],
                    ['nama' => $role->name, 'jenis' => 'TIANG']
                );
            }
            if (in_array($role->role_code, ['5170K1', '5170K2', '5170K3', '5170K4', '5170K5', '5170K6', '5170K7'], true)) {
                Vendor::updateOrCreate(
                    ['user_id' => $user->id],
                    ['nama' => $role->name, 'jenis' => 'KONSTRUKSI']
                );
            }
        }

        $this->command->info('Akun dibuat/diperbarui. Password semua akun: password');
    }
}
