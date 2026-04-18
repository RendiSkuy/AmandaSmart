<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductPoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
{
    // 1. Buat Produk untuk Unilever (ID: 1)
    $product = \App\Models\Product::create([
        'supplier_id' => 1,
        'plu_code' => 'PLU-001',
        'name' => 'Indomie Goreng 85gr',
        'minor' => 40,      // 1 Karton isi 40
        'max_stock' => 200, // Kapasitas rak
        'on_hand' => 50,    // Stok sisa sedikit
        'unit_price' => 3000
    ]);

    // 2. Buat Kepala PO
    $po = \App\Models\PurchaseOrder::create([
        'po_number' => 'PO-2026-0001',
        'supplier_id' => 1,
        'dc_id' => 1, // Pastikan DC ID 1 sudah ada (DC Subang)
        'type' => 'bkl',
        'order_date' => now(),
        'expire_date' => now()->addDays(7),
        'status' => 'active'
    ]);

    // 3. Buat Detail PO (Gunakan Rumus PB dari Model)
    \App\Models\PurchaseOrderItem::create([
        'purchase_order_id' => $po->id,
        'product_id' => $product->id,
        'qty_pb' => $product->qty_pb, // Otomatis terhitung (200-50)/40 * 40 = 120
        'qty_ordered' => $product->qty_pb,
        'unit_price' => $product->unit_price
    ]);
}
}
