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

        $query = Ttf::with(['goodsReceipt.purchaseOrder.product'])->latest();

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

        // Ambil LPB beserta data PO terkait
        $lpb = GoodsReceipt::with(['purchaseOrder.product', 'retur'])->findOrFail($request->goods_receipt_id);
        
        $supplierId = $lpb->purchaseOrder->selected_supplier_id;

        if (!$supplierId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier tidak ditemukan untuk PO ini.'
            ], 422);
        }

        // Ambil harga penawaran supplier yang diterima (accepted offer)
        $acceptedOffer = Offer::where('purchase_order_id', $lpb->purchase_order_id)
            ->where('supplier_id', $supplierId)
            ->where('status', 'accepted')
            ->first();

        if (!$acceptedOffer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Harga penawaran yang disetujui (accepted offer) tidak ditemukan untuk PO ini.'
            ], 422);
        }

        $pricePerPcs = $acceptedOffer->price_per_pcs;

        // Hitung total kotor: qty_received * harga penawaran
        $totalAmount = $lpb->qty_received * $pricePerPcs;

        // Hitung potongan denda/retur jika ada barang yang diretur
        $qtyRetur = $lpb->retur ? $lpb->retur->qty_retur : 0;
        $totalDeductions = $qtyRetur * $pricePerPcs;

        // Total Bayar = Nominal kotor - Potongan denda
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