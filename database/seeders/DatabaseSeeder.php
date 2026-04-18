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
            SupplierSeeder::class,      // Buat Toko & User
            ProductPoSeeder::class,     // Buat Master Barang & PO Pertama
            PurchaseOrderSeeder::class, // Buat Variasi PO BKL/DC
            GoodsReceiptSeeder::class,  // Buat Penerimaan Barang (LPB)
            ReturSeeder::class,         // Buat Pengembalian Barang (Retur)
            VrsScheduleSeeder::class,   // Buat Jadwal Booking VRS
        ]);
}
}
