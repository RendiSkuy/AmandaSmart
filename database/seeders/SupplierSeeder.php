<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Supplier;
use App\Models\User;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // Data 5 Perusahaan Supplier Ritel AmandaMart
        $companies = [
            [
                'prefix'        => 'unilever',
                'supplier_code' => 'SUP-001',
                'name'          => 'PT Unilever Indonesia',
                'whatsapp'      => '628123456789'
            ],
            [
                'prefix'        => 'indofood',
                'supplier_code' => 'SUP-002',
                'name'          => 'PT Indofood CBP Sukses Makmur',
                'whatsapp'      => '628987654321'
            ],
            [
                'prefix'        => 'wings',
                'supplier_code' => 'SUP-003',
                'name'          => 'PT Wings Surya',
                'whatsapp'      => '628555555555'
            ],
            [
                'prefix'        => 'mayora',
                'supplier_code' => 'SUP-004',
                'name'          => 'PT Mayora Indah Tbk',
                'whatsapp'      => '628777777777'
            ],
            [
                'prefix'        => 'nestle',
                'supplier_code' => 'SUP-005',
                'name'          => 'PT Nestle Indonesia',
                'whatsapp'      => '628111111111'
            ]
        ];

        $this->command->info("====================================");
        
        foreach ($companies as $comp) {
            // 1. Buat Profil Perusahaan Supplier
            $supplier = Supplier::firstOrCreate(
                ['supplier_code' => $comp['supplier_code']],
                [
                    'name'            => $comp['name'],
                    'whatsapp_number' => $comp['whatsapp']
                ]
            );

            // 2. Buat 5 Akun Sales untuk masing-masing perusahaan
            for ($i = 1; $i <= 5; $i++) {
                $username = $comp['prefix'] . '_sales' . $i;
                User::firstOrCreate(
                    ['username' => $username],
                    [
                        'password'          => Hash::make('password123'),
                        'role'              => 'supplier',
                        'google_2fa_secret' => null,
                        'supplier_id'       => $supplier->id,
                    ]
                );
            }

            $this->command->info("Supplier '{$comp['name']}' dengan 5 akun sales berhasil dibuat!");
        }

        // 3. Buat User Merchandiser (MD) untuk testing
        $userMd = User::firstOrCreate(
            ['username' => 'rendi_md'],
            [
                'password'          => Hash::make('password123'),
                'role'              => 'md',
                'google_2fa_secret' => null,
                'supplier_id'       => null,
            ]
        );

        $this->command->info("User Merchandiser '{$userMd->username}' berhasil dibuat!");
        $this->command->warn("Semua Password Akun: password123");
        $this->command->info("====================================");
    }
}