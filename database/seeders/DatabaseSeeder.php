<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
   public function run(): void
{
    // Komentari atau hapus baris ini:
    // User::factory()->create([...]);

    // Tambahkan ini agar menggunakan seeder yang kita buat sendiri:
    $this->call([
        SupplierSeeder::class,
        ProductPoSeeder::class,
        GoodsReceiptSeeder::class,
    ]);
}
}
