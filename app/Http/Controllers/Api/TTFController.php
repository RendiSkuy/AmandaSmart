<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ttf;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TTFController extends Controller
{
    /**
     * Menampilkan daftar tagihan (TTF) milik supplier
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $ttfs = Ttf::with(['goodsReceipt.purchaseOrder'])
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $ttfs
        ]);
    }

    /**
     * Simulasi Generate TTF dari LPB (Untuk Testing)
     * Biasanya ini dipicu saat supplier klik "Ajukan Tagihan"
     */
    public function store(Request $request)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id'
        ]);

        $lpb = GoodsReceipt::with('items')->findOrFail($request->goods_receipt_id);
        
        // Pastikan LPB milik supplier yang login
        if ($lpb->supplier_id !== $request->user()->supplier_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Hitung total_amount dari items LPB (Qty Received * Harga)
        $totalAmount = $lpb->items->sum(function($item) {
            return $item->qty_received * $item->unit_price;
        });

        // Simulasi: Jatuh tempo 30 hari dari sekarang (TOP 30)
        $dueDate = now()->addDays(30);

        $ttf = Ttf::create([
            'ttf_number' => 'TTF-' . date('Ymd') . '-' . str_pad($lpb->id, 4, '0', STR_PAD_LEFT),
            'goods_receipt_id' => $lpb->id,
            'supplier_id' => $lpb->supplier_id,
            'due_date' => $dueDate,
            'total_amount' => $totalAmount,
            'total_deductions' => 0, // Bisa diisi nanti jika ada pinalti
            'net_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tagihan (TTF) berhasil dibuat',
            'data' => $ttf
        ]);
    }
}