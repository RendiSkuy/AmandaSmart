<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VrsSchedule;
use App\Models\PurchaseOrder;

class VrsScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil PO yang statusnya active untuk dijadikan contoh booking
        $po1 = PurchaseOrder::where('po_number', 'PO-2026-0001')->first();
        $po2 = PurchaseOrder::where('po_number', 'PO-NONBKL-001')->first();

        // Skenario 1: Booking untuk Besok Pagi
        if ($po1) {
            VrsSchedule::create([
                'supplier_id' => $po1->supplier_id,
                'purchase_order_id' => $po1->id,
                'dc_id' => $po1->dc_id ?? 1,
                'scheduled_date' => now()->addDay()->format('Y-m-d'),
                'time_slot' => '08:00 - 09:00',
                'gate_number' => 'GATE-01',
                'status' => 'booked'
            ]);
        }

        // Skenario 2: Booking yang sudah Selesai (Completed)
        if ($po2) {
            VrsSchedule::create([
                'supplier_id' => $po2->supplier_id,
                'purchase_order_id' => $po2->id,
                'dc_id' => $po2->dc_id ?? 1,
                'scheduled_date' => now()->subDay()->format('Y-m-d'),
                'time_slot' => '13:00 - 14:00',
                'gate_number' => 'GATE-03',
                'status' => 'completed'
            ]);
        }
    }
}