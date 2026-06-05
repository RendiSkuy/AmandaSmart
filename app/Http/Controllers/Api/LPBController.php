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

        $lpbs = GoodsReceipt::with(['purchaseOrder.product'])
            ->whereHas('purchaseOrder.offers', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->where('status', 'accepted');
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $lpbs
        ]);
    }

    /**
     * Menampilkan detail satu LPB
     */
    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        $lpb = GoodsReceipt::with(['purchaseOrder.product'])
            ->whereHas('purchaseOrder.offers', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->where('status', 'accepted');
            })
            ->find($id);

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

    /**
     * Menyimpan penerimaan barang (LPB) beserta retur jika ada
     */
    public function store(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'qty_received'      => 'required|integer|min:0',
            'qty_retur'         => 'nullable|integer|min:0',
            'reason'            => 'required_if:qty_retur,>0|string|nullable',
            'barcode'           => 'nullable|string',
            'received_at'       => 'nullable|date',
        ]);

        $po = \App\Models\PurchaseOrder::findOrFail($request->purchase_order_id);
        $barcode = $request->barcode ?? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po->product->name));
        $receivedAt = $request->received_at ?? now();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $po, $barcode, $receivedAt) {
            // 1. Catat Goods Receipt
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'qty_received'      => $request->qty_received,
                'received_at'       => $receivedAt,
                'barcode'           => $barcode,
            ]);

            // 2. Catat Retur jika ada qty_retur > 0
            $retur = null;
            if ($request->filled('qty_retur') && $request->qty_retur > 0) {
                $retur = \App\Models\Retur::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id'       => $po->product_id,
                    'qty_retur'        => $request->qty_retur,
                    'reason'           => $request->reason,
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'LPB dan Retur berhasil dicatat.',
                'data'    => [
                    'goods_receipt' => $goodsReceipt,
                    'retur'         => $retur,
                ]
            ], 201);
        });
    }
}