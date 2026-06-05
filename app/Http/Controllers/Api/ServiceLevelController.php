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

        if ($request->user()->role === 'md') {
            $supplierId = $request->query('supplier_id');
        }

        // Query untuk menghitung total ordered vs received vs retur
        $query = DB::table('purchase_orders')
            ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
            ->leftJoin('returs', 'returs.goods_receipt_id', '=', 'goods_receipts.id');

        if ($supplierId) {
            $query->where('purchase_orders.selected_supplier_id', $supplierId);
        }

        $stats = $query->select(
                DB::raw('COALESCE(SUM(purchase_orders.qty_po), 0) as total_ordered'),
                DB::raw('COALESCE(SUM(goods_receipts.qty_received), 0) as total_received'),
                DB::raw('COALESCE(SUM(returs.qty_retur), 0) as total_retur')
            )
            ->first();

        $totalOrdered = (int) $stats->total_ordered;
        $totalReceived = (int) $stats->total_received;
        $totalRetur = (int) $stats->total_retur;

        // Kuantitas Bersih Diterima = qty_received - qty_retur
        $qtyReceivedClean = max(0, $totalReceived - $totalRetur);

        // Hitung persentase Service Level (SL)
        $serviceLevel = $totalOrdered > 0 
            ? round(($qtyReceivedClean / $totalOrdered) * 100, 2) 
            : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'supplier_id'              => $supplierId,
                'total_ordered'            => $totalOrdered,
                'total_received'           => $totalReceived,
                'total_retur'              => $totalRetur,
                'qty_received_clean'       => $qtyReceivedClean,
                'service_level_percentage' => $serviceLevel,
                'status_performa'          => $serviceLevel >= 95 ? 'Sangat Baik' : ($serviceLevel >= 85 ? 'Cukup' : 'Perlu Evaluasi')
            ]
        ]);
    }
}