<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceLevelController extends Controller
{
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        // Query untuk menghitung total ordered vs received
        $stats = DB::table('goods_receipt_items')
            ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
            ->where('goods_receipts.supplier_id', $supplierId)
            ->select(
                DB::raw('SUM(qty_ordered) as total_ordered'),
                DB::raw('SUM(qty_received) as total_received')
            )
            ->first();

        $totalOrdered = (int) $stats->total_ordered;
        $totalReceived = (int) $stats->total_received;

        // Hitung persentase SL
        $serviceLevel = $totalOrdered > 0 
            ? round(($totalReceived / $totalOrdered) * 100, 2) 
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'supplier_id' => $supplierId,
                'total_ordered' => $totalOrdered,
                'total_received' => $totalReceived,
                'service_level_percentage' => $serviceLevel,
                'status_performa' => $serviceLevel >= 95 ? 'Sangat Baik' : ($serviceLevel >= 85 ? 'Cukup' : 'Perlu Evaluasi')
            ]
        ]);
    }
}