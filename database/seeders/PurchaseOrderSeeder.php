<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATA NON-BKL (Pesanan ke Gudang Pusat/DC)
        $poNonBkl = PurchaseOrder::create([
            'po_number' => 'PO-NONBKL-001',
            'supplier_id' => 1,
            'dc_id' => 1, // ID DC Subang
            'type' => 'dc',
            'order_date' => now(),
            'expire_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $poNonBkl->id,
            'product_id' => 1,
            'qty_pb' => 120,
            'qty_ordered' => 120,
            'unit_price' => 3000
        ]);

        // 2. DATA BKL (Kirim Langsung ke Toko Amanda Antapani)
        $poBkl = PurchaseOrder::create([
            'po_number' => 'PO-BKL-2026-001',
            'supplier_id' => 1,
            'dc_id' => null, // NULL karena BKL langsung ke Toko
            'type' => 'bkl',
            'order_date' => now(),
            'expire_date' => now()->addDays(7),
            'status' => 'active',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $poBkl->id,
            'product_id' => 1,
            'qty_pb' => 40,
            'qty_ordered' => 40,
            'unit_price' => 3000
        ]);
    }
}