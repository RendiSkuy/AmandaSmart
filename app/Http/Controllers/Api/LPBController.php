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

        $lpbs = GoodsReceipt::with(['purchaseOrder.details.product', 'details.product', 'returs'])
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

        $lpb = GoodsReceipt::with(['purchaseOrder.details.product', 'details.product', 'returs'])
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
        $po = \App\Models\PurchaseOrder::with('details.product')->findOrFail($request->purchase_order_id);

        // Normalisasi input jika berupa scalar (legacy single product PO)
        if (!is_array($request->qty_received)) {
            $firstDetail = $po->details->first();
            $productId = $firstDetail ? $firstDetail->product_id : null;
            if ($productId) {
                $qtyReceived = [$productId => $request->qty_received];
                $qtyRetur = [$productId => $request->qty_retur ?? 0];
                $reason = [$productId => $request->reason ?? ''];
                $request->merge([
                    'qty_received' => $qtyReceived,
                    'qty_retur' => $qtyRetur,
                    'reason' => $reason,
                ]);
            }
        }

        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'qty_received'      => 'required|array',
            'qty_received.*'    => 'required|integer|min:0',
            'qty_retur'         => 'nullable|array',
            'qty_retur.*'       => 'nullable|integer|min:0',
            'reason'            => 'nullable|array',
            'reason.*'          => 'nullable|string',
            'barcode'           => 'nullable|string',
            'received_at'       => 'nullable|date',
        ]);

        // Validasi Qty received tidak boleh melebihi qty_po
        foreach ($po->details as $detail) {
            $received = (int) ($request->qty_received[$detail->product_id] ?? 0);
            if ($received < 0 || $received > $detail->qty_po) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Gagal! Jumlah barang fisik diterima untuk produk {$detail->product->name} ({$received} PCS) tidak boleh kurang dari 0 atau melebihi jumlah permintaan di PO ({$detail->qty_po} PCS)."
                ], 422);
            }
        }

        $firstDetail = $po->details->first();
        $barcode = $request->barcode ?? ($firstDetail ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $firstDetail->product->name)) : 'BARCODE');
        $receivedAt = $request->received_at ?? now();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $po, $barcode, $receivedAt) {
            // 1. Catat Goods Receipt Header
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'received_at'       => $receivedAt,
                'barcode'           => $barcode,
            ]);

            // 2. Simpan Detail Penerimaan dan Retur per Produk
            $retursList = [];
            foreach ($po->details as $detail) {
                $pId = $detail->product_id;
                $received = (int) ($request->qty_received[$pId] ?? 0);
                $returQty = (int) ($request->qty_retur[$pId] ?? 0);
                $reasonText = $request->reason[$pId] ?? '';

                \App\Models\GoodsReceiptDetail::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id'       => $pId,
                    'qty_received'      => $received,
                ]);

                if ($returQty > 0) {
                    $retursList[] = \App\Models\Retur::create([
                        'goods_receipt_id' => $goodsReceipt->id,
                        'product_id'       => $pId,
                        'qty_retur'        => $returQty,
                        'reason'           => $reasonText ?: 'Barang rusak/selisih',
                    ]);
                }

                $product = $detail->product;
                $qtyReceivedClean = max(0, $received - $returQty);
                $product->increment('on_hand', $qtyReceivedClean);
            }

            // Update VRS status jika ada
            \App\Models\VrsSchedule::where('purchase_order_id', $po->id)->update([
                'status' => 'completed',
                'actual_arrival_at' => now()
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'LPB dan Retur berhasil dicatat.',
                'data'    => [
                    'goods_receipt' => $goodsReceipt,
                    'returs'        => $retursList,
                ]
            ], 201);
        });
    }
}