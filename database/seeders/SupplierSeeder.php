<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
    $supplier = \App\Models\Supplier::create([
        'supplier_code' => 'SUP-001',
        'name' => 'PT Unilever Indonesia',
        'status' => 'active'
    ]);

    \App\Models\User::create([
        'name' => 'Rendi Admin',
        'username' => 'rendi_b2b',
        'email' => 'rendi@unilever.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'supplier_id' => $supplier->id,
        'role' => 'admin'
    ]);
    // Buat DC dulu agar ID-nya ada
$dc = \App\Models\DistributionCenter::create([
    'code' => 'DC01',
    'name' => 'DC SUBANG',
    'type' => 'dc'
]);
}
}
