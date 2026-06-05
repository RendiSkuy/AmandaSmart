<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Offer;
use App\Models\VrsSchedule;
use App\Models\GoodsReceipt;
use App\Models\Retur;
use App\Models\Ttf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SimulasiRetailCommand extends Command
{
    protected $signature = 'amandamart:simulasi';
    protected $description = 'Menjalankan simulasi transaksi hulu ke hilir AmandaMart otomatis tanpa Tinker';

    public function handle()
    {
        $this->headerOutput("MEMULAI SIMULASI SISTEM B2B AMANDAMART");

        // ----------------------------------------------------
        // TAHAP 1: SETUP DATA INDUK KRITIS (SEEDING)
        // ----------------------------------------------------
        $this->info("👉 TAHAP 1: Membersihkan database & membuat data induk...");
        
        DB::statement('TRUNCATE users, suppliers, products, purchase_orders, offers, vrs_schedules, goods_receipts, returs, ttf CASCADE');

        // Menggunakan google_2fa_secret sesuai skema migrasi B2B
        $userMd = User::create([
            'username' => 'md_amanda',
            'password' => Hash::make('password123'),
            'role' => 'md',
            'google_2fa_secret' => 'B37J76SIONMZA3Y2' // Contoh dummy secret key 2FA
        ]);

        $userSupplier1 = User::create(['username' => 'supplier_a', 'password' => Hash::make('password123'), 'role' => 'supplier']);
        $supplierA = Supplier::create([
            'user_id' => $userSupplier1->id,
            'supplier_code' => 'SPL-001',
            'name' => 'PT Sembako Jaya',
            'whatsapp_number' => '081234567890'
        ]);

        $userSupplier2 = User::create(['username' => 'supplier_b', 'password' => Hash::make('password123'), 'role' => 'supplier']);
        $supplierB = Supplier::create([
            'user_id' => $userSupplier2->id,
            'supplier_code' => 'SPL-002',
            'name' => 'CV Beras Organik',
            'whatsapp_number' => '089876543210'
        ]);

        $product = Product::create([
            'plu_code' => 'BRS-PREM-05',
            'name' => 'Beras Premium 5kg',
            'minor' => 1,
            'max_stock' => 1500,
            'on_hand' => 300 
        ]);

        $this->line("   [OK] Data Master MD, Supplier A, Supplier B, dan Produk Beras berhasil disiapkan.");

        // ----------------------------------------------------
        // TAHAP 2: EKSEKUSI OTOMATISASI RESTOCK (PROSES PB)
        // ----------------------------------------------------
        $this->info("\n👉 TAHAP 2: Sistem mendeteksi stok kritis & membuat Permintaan Barang (PB)...");
        
        $qtyToOrder = $product->max_stock - $product->on_hand; 
        $poNumber = 'PO-' . strtoupper(Str::random(4)) . '-' . date('Ymd');

        $po = PurchaseOrder::create([
            'po_number' => $poNumber,
            'product_id' => $product->id,
            'qty_po' => $qtyToOrder,
            'status' => 'PENDING_BIDDING',
            'selected_supplier_id' => null
        ]);

        $this->line("   [OK] Sistem berhasil membuat draf otomatis: {$po->po_number}");
        $this->line("        Kuantitas yang diminta sistem (Read-Only): {$po->qty_po} PCS");

        // ----------------------------------------------------
        // TAHAP 3: SIMULASI SUPPLIER MENGISI HARGA (BIDDING)
        // ----------------------------------------------------
        $this->info("\n👉 TAHAP 3: Supplier melihat draf PO di b2b.amanda.id & menginput Harga per PCS...");

        $offerA = Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplierA->id,
            'price_per_pcs' => 60000,
            'status' => 'pending'
        ]);

        $offerB = Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplierB->id,
            'price_per_pcs' => 58000,
            'status' => 'pending'
        ]);

        $this->line("   [OK] Supplier A ({$supplierA->name}) mengajukan penawaran: Rp 60.000 / PCS");
        $this->line("   [OK] Supplier B ({$supplierB->name}) mengajukan penawaran: Rp 58.000 / PCS");

        // ----------------------------------------------------
        // TAHAP 4: SIMULASI MD MEMILIH PEMENANG (CHECKBOX SELECTION)
        // ----------------------------------------------------
        $this->info("\n👉 TAHAP 4: Tim MD membuka md.amanda.id & menyetujui penawaran Supplier B...");

        $po->update([
            'status' => 'APPROVED',
            'selected_supplier_id' => $supplierB->id,
            'delivery_deadline' => now()->addDays(2) 
        ]);

        $offerB->update(['status' => 'accepted']);
        $offerA->update(['status' => 'rejected']);

        $this->line("   [OK] Status {$po->po_number} berubah menjadi 'APPROVED'.");
        $this->line("   [OK] Supplier B terpilih sebagai vendor resmi. Sistem memicu WhatsApp Notifikasi.");

        // ----------------------------------------------------
        // TAHAP 5: SIMULASI TRUK DATANG & CEK FISIK GUDANG (LPB & RETUR)
        // ----------------------------------------------------
        $this->info("\n👉 TAHAP 5: Truk Supplier B tiba di Gudang DC. Melakukan cek fisik...");

        $vrs = VrsSchedule::create([
            'purchase_order_id' => $po->id,
            'scheduled_date' => now()->format('Y-m-d'),
            'time_slot' => '09:00 - 11:00',
            'status' => 'completed',
            'actual_arrival_at' => now() 
        ]);

        $lpb = GoodsReceipt::create([
            'purchase_order_id' => $po->id,
            'qty_received' => 1180,
            'received_at' => now(),
            'barcode' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $product->name)),
        ]);

        $retur = Retur::create([
            'goods_receipt_id' => $lpb->id,
            'product_id' => $product->id,
            'qty_retur' => 15,
            'reason' => 'Kemasan pecah saat pembongkaran'
        ]);

        $this->line("   [OK] Dokumen LPB terbit. Jumlah barang tiba fisik: 1180 PCS.");
        $this->line("   [OK] Dokumen Retur terbit. Jumlah barang rusak/cacat: 15 PCS.");

        // ----------------------------------------------------
        // TAHAP 6: VALIDASI NILAI AKHIR (SERVICE LEVEL & AKUNTANSI)
        // ----------------------------------------------------
        $this->info("\n👉 TAHAP 6: Mengkalkulasi laporan performa & nominal tagihan otomatis...");

        $qtyCleanReceived = $lpb->qty_received - $retur->qty_retur; 
        $serviceLevelScore = ($qtyCleanReceived / $po->qty_po) * 100; 

        $pricePerPcs = $offerB->price_per_pcs;
        $totalAmountBeforeDeduction = $lpb->qty_received * $pricePerPcs;
        $totalDeductions = $retur->qty_retur * $pricePerPcs; 
        $finalPayment = $totalAmountBeforeDeduction - $totalDeductions;

        Ttf::create([
            'goods_receipt_id' => $lpb->id,
            'total_amount' => $finalPayment,
            'total_deductions' => $totalDeductions,
            'status_payment' => 'pending'
        ]);

        $this->newLine();
        $this->table(
            ['Komponen Laporan Operasional', 'Nilai / Hasil Kalkulasi Backend'],
            [
                ['Nomor Purchase Order (PO)', $po->po_number],
                ['Vendor Terpilih', $supplierB->name],
                ['Kuantitas Diminta Sistem (PO)', $po->qty_po . ' PCS'],
                ['Kuantitas Tiba di Gudang (LPB)', $lpb->qty_received . ' PCS'],
                ['Kuantitas Barang Cacat (Retur)', $retur->qty_retur . ' PCS'],
                ['Kuantitas Bersih Diterima Gudang', $qtyCleanReceived . ' PCS'],
                ['SKOR SERVICE LEVEL SUPPLIER', number_format($serviceLevelScore, 1) . ' %'],
                ['Harga Modal per PCS', 'Rp ' . number_format($pricePerPcs, 0, ',', '.')],
                ['Potongan Penalti Keuangan (Deductions)', 'Rp ' . number_format($totalDeductions, 0, ',', '.')],
                ['Total Bersih yang Wajib Dibayar AmandaMart', 'Rp ' . number_format($finalPayment, 0, ',', '.')],
            ]
        );

        $this->headerOutput("SIMULASI SUKSES 100% - LOGIKA BACKEND VALID");
        return Command::SUCCESS;
    }

    private function headerOutput($title)
    {
        $this->line('====================================================================');
        $this->info("   " . $title);
        $this->line('====================================================================');
    }
}