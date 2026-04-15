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

        // Ambil data LPB, sertakan info PO dan DC (Gudang tujuan)
        $lpbs = GoodsReceipt::with(['purchaseOrder', 'distributionCenter'])
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

        // Cari LPB berdasarkan ID dan pastikan milik supplier ini
        // Kita asumsikan model GoodsReceipt punya relasi 'items'
        $lpb = GoodsReceipt::with(['purchaseOrder', 'distributionCenter', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $lpb
        ]);
    }
}