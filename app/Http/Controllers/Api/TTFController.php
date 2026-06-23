<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ttf;
use App\Models\GoodsReceipt;
use App\Models\Offer;
use Illuminate\Http\Request;

class TTFController extends Controller
{
    /**
     * Menampilkan daftar tagihan (TTF) milik supplier
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $query = Ttf::with(['goodsReceipt.purchaseOrder.details.product'])->latest();

        if ($request->user()->role === 'md') {
            if ($request->has('supplier_id')) {
                $query->whereHas('goodsReceipt.purchaseOrder', function ($q) use ($request) {
                    $q->where('selected_supplier_id', $request->supplier_id);
                });
            }
        } else {
            $query->whereHas('goodsReceipt.purchaseOrder.offers', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->where('status', 'accepted');
            });
        }

        $ttfs = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $ttfs
        ]);
    }

    /**
     * Pembuatan TTF dari LPB (Mengajukan Tagihan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id'
        ]);

        $lpb = GoodsReceipt::with(['purchaseOrder.details.product', 'details', 'returs'])->findOrFail($request->goods_receipt_id);
        
        $totalAmount = 0;
        $totalDeductions = 0;

        foreach ($lpb->details as $lpbDetail) {
            $pId = $lpbDetail->product_id;
            
            // Ambil harga beli yang disepakati dari PO detail
            $poDetail = $lpb->purchaseOrder->details->where('product_id', $pId)->first();
            $pricePerPcs = $poDetail ? (float) $poDetail->price_per_pcs : 0.0;

            // Qty diterima
            $qtyReceived = $lpbDetail->qty_received;

            // Qty retur
            $returItem = $lpb->returs->where('product_id', $pId)->first();
            $qtyRetur = $returItem ? $returItem->qty_retur : 0;

            $totalAmount += $qtyReceived * $pricePerPcs;
            $totalDeductions += $qtyRetur * $pricePerPcs;
        }

        $finalPayment = $totalAmount - $totalDeductions;

        $ttf = Ttf::create([
            'goods_receipt_id' => $lpb->id,
            'total_amount'     => $finalPayment,
            'total_deductions' => $totalDeductions,
            'status_payment'   => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tagihan (TTF) berhasil diajukan.',
            'data' => $ttf
        ]);
    }
}