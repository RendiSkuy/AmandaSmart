<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    /**
     * Menampilkan daftar retur milik supplier yang sedang login
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $returs = Retur::with(['goodsReceipt', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $returs
        ]);
    }

    /**
     * Menampilkan detail satu dokumen retur beserta list barangnya
     */
    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        $retur = Retur::with(['goodsReceipt', 'items.product'])
            ->where('supplier_id', $supplierId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $retur
        ]);
    }
}