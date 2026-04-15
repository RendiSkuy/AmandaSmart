<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // Ambil ID Supplier dari user yang sedang login
        $supplierId = $request->user()->supplier_id;

        // Ambil PO milik supplier tersebut, sertakan data DC dan jumlah item
        $pos = PurchaseOrder::with(['dc'])
            ->withCount('items')
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pos
        ]);
    }

    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        // Cari PO berdasarkan ID dan pastikan milik supplier ini
        $po = PurchaseOrder::with(['items.product', 'dc'])
            ->where('supplier_id', $supplierId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $po
        ]);
    }
}