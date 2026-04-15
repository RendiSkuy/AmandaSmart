<?php

namespace Database\Seeders;

use App\Models\Retur;
use App\Models\ReturItem;
use App\Models\GoodsReceipt;
use Illuminate\Database\Seeder;

class ReturSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cari data LPB yang sudah kita buat sebelumnya (ID: 1)
        $lpb = GoodsReceipt::with('items')->first();

        if ($lpb) {
            // 2. Buat Header Retur (Induk)
            $retur = Retur::create([
                'retur_number' => 'RT-202604-' . str_pad($lpb->id, 4, '0', STR_PAD_LEFT),
                'goods_receipt_id' => $lpb->id,
                'supplier_id' => $lpb->supplier_id,
                'reason' => 'Barang bocor dan kemasan rusak saat diterima di gudang.',
                'status' => 'pending',
            ]);

            // 3. Buat Detail Retur (Anak)
            // Kita ambil produk dari item LPB tersebut
            foreach ($lpb->items as $item) {
                ReturItem::create([
                    'retur_id' => $retur->id,
                    'product_id' => $item->product_id,
                    'qty_retur' => 3, // Ceritanya kita kembalikan 3 pcs
                ]);
            }
        }
    }
}