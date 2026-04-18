<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;

class LPBController extends Controller
{
    /**
     * Menampilkan daftar LPB milik supplier yang login
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        // TAMBAHKAN 'items.product' di sini agar qty_ordered & qty_received muncul di daftar
        $lpbs = GoodsReceipt::with(['purchaseOrder', 'distributionCenter', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lpbs
        ]);
    }

    /**
     * Menampilkan detail item di dalam satu LPB
     */
    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        // Cari LPB dan pastikan milik supplier ini
        $lpb = GoodsReceipt::with(['purchaseOrder', 'distributionCenter', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->find($id); // Pakai find agar bisa kita handle jika null

        if (!$lpb) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data LPB tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lpb
        ]);
    }
}