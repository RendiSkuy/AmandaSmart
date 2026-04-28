<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Supplier;
use App\Models\User;
use App\Models\DistributionCenter;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // Buat atau ambil Supplier yang sudah ada
        $supplier = Supplier::firstOrCreate(
            ['supplier_code' => 'SUP-001'],
            [
                'name'   => 'PT Unilever Indonesia',
                'status' => 'active'
            ]
        );

        // Buat atau ambil User — google2fa_secret dibiarkan NULL
        // Supplier akan setup 2FA sendiri saat pertama login
        $user = User::firstOrCreate(
            ['username' => 'rendi_b2b'],
            [
                'name'             => 'Rendi Admin',
                'email'            => 'rendi@unilever.com',
                'password'         => Hash::make('password123'),
                'supplier_id'      => $supplier->id,
                'role'             => 'admin',
                'google2fa_secret' => null, // ← NULL, supplier setup sendiri
            ]
        );

        // Buat atau ambil Distribution Center
        DistributionCenter::firstOrCreate(
            ['code' => 'DC01'],
            [
                'name' => 'DC SUBANG',
                'type' => 'dc'
            ]
        );

        $this->command->info("====================================");
        $this->command->info("User '{$user->username}' berhasil dibuat!");
        $this->command->warn("Password : password123");
        $this->command->warn("2FA      : Belum disetup (supplier setup mandiri saat login)");
        $this->command->info("====================================");
    }
}