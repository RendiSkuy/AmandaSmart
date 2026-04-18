<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoodsReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil data PO yang sudah dibuat oleh ProductPoSeeder sebelumnya
        $po = PurchaseOrder::first();

        // Jika PO tidak ditemukan, seeder berhenti agar tidak error
        if (!$po) {
            $this->command->info('Data PO tidak ditemukan, silakan jalankan ProductPoSeeder dulu.');
            return;
        }

        // 2. Buat data "Kepala" LPB (Goods Receipt Header)
        // Kolom disesuaikan dengan migration kamu: lpb_number, received_at, dll.
        $lpb = GoodsReceipt::create([
            'lpb_number'        => 'LPB-' . date('Ymd') . '-001',
            'purchase_order_id' => $po->id,
            'supplier_id'       => $po->supplier_id,
            'dc_id'             => $po->dc_id,
            'status'            => 'received',
            'is_read'           => false,
            'received_at'       => now(),
        ]);

        // 3. Ambil item barang dari PO tersebut untuk dimasukkan ke Detail LPB
        $purchaseOrderItems = PurchaseOrderItem::where('purchase_order_id', $po->id)->get();

        foreach ($purchaseOrderItems as $item) {
            // Kita masukkan ke tabel detail: goods_receipt_items
            // Kita gunakan DB::table karena biasanya tabel detail belum dibuatkan Model-nya
            DB::table('goods_receipt_items')->insert([
                'goods_receipt_id' => $lpb->id,
                'product_id'       => $item->product_id,
                'qty_ordered'      => $item->qty_ordered,
                // Simulasi: Barang diterima kurang 2 (misal pesen 120 datang 118)
                'qty_received'     => $item->qty_ordered - 2, 
                'unit_price'       => $item->unit_price,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}