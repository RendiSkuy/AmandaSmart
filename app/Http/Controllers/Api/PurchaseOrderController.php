<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * READ: Menampilkan daftar PO (Akses via DB Slave/Read Connection jika ada)
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $pos = PurchaseOrder::with(['dc', 'items.product']) 
            ->withCount('items')
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pos
        ]);
    }

    /**
     * CREATE: Generate PO Otomatis menggunakan Stored Procedure
     * Sesuai instruksi: Logic PB dipindah ke Database Layer (PostgreSQL)
     */
    public function generateAutoPO(Request $request)
    {
        $supplierId = $request->user()->supplier_id;
        $userId = $request->user()->id;

        try {
            // Membungkus dalam transaksi untuk menjaga konsistensi data (Atomic)
            DB::transaction(function () use ($supplierId, $userId) {
                // Memanggil Procedure 'generate_auto_po_proc' di PostgreSQL
                // Parameter: 1. ID Supplier, 2. ID User (untuk trigger notif di DB)
                DB::statement('CALL generate_auto_po_proc(?, ?)', [$supplierId, $userId]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'PO Berhasil dibuat secara otomatis oleh Stored Procedure (PB Logic di DB)'
            ]);

        } catch (\Exception $e) {
            /** * Menangkap error dari database (misal RAISE EXCEPTION 'Stok mencukupi')
             * Kita membersihkan pesan error agar lebih rapi dilihat di Postman
             */
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Semua stok mencukupi')) {
                $errorMessage = 'Semua stok masih mencukupi, tidak ada PO yang dibuat.';
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Pesan dari DB: ' . $errorMessage
            ], 422); // Gunakan code 422 untuk validation error dari DB
        }
    }

    /**
     * READ: Detail PO berdasarkan ID
     */
    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        $po = PurchaseOrder::with(['items.product', 'dc'])
            ->where('supplier_id', $supplierId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $po
        ]);
    }

    /**
     * UPDATE: Penyesuaian Qty oleh Supplier
     * Tetap menggunakan logic Laravel untuk validasi input cepat (Application Layer)
     */
    public function updateItem(Request $request, $id)
    {
        // Cari detail item PO beserta relasi produknya
        $item = PurchaseOrderItem::with(['product', 'purchaseOrder'])->findOrFail($id);
        
        $request->validate([
            'qty_ordered' => 'required|integer|min:0'
        ]);

        $newQty = $request->qty_ordered;
        $product = $item->product;

        // 1. VALIDASI LOGISTIK (MINOR): Memastikan efisiensi pengiriman
        if ($newQty % $product->minor !== 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Jumlah harus kelipatan dari Minor ({$product->minor} pcs)."
            ], 422);
        }

        // 2. VALIDASI KAPASITAS (MAX STOCK): Memastikan rak gudang muat
        if (($newQty + $product->on_hand) > $product->max_stock) {
            return response()->json([
                'status' => 'error',
                'message' => "Jumlah terlalu banyak! Sisa kapasitas rak hanya muat " . ($product->max_stock - $product->on_hand) . " pcs lagi."
            ], 422);
        }

        // Simpan perubahan ke database
        $item->update(['qty_ordered' => $newQty]);

        // Trigger Notifikasi ke Tabel Notification (Event-Driven)
        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Qty PO Diperbarui',
            'body' => 'Jumlah pesanan ' . $item->purchaseOrder->po_number . ' telah disesuaikan menjadi ' . $newQty,
            'type' => 'PO_UPDATE',
            'reference_id' => $item->purchase_order_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Qty PO berhasil disesuaikan oleh Supplier',
            'data' => $item
        ]);
    }
}