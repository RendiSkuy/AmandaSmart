<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * READ: Menampilkan daftar PO milik supplier yang login atau PO pending terbuka
     */
    public function index(Request $request)
    {
        $supplier = $request->user()->supplier;
        if (!$supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil supplier tidak ditemukan.'
            ], 403);
        }

        $supplierId = $supplier->id;
        $supplierCode = $supplier->supplier_code;

        // Ambil PO yang terikat ke supplier ini (dimenangkan oleh user sales ini) ATAU PO pending untuk barang milik supplier ini saja
        $pos = PurchaseOrder::with(['product']) 
            ->where(function ($q) use ($supplierId, $request) {
                $q->where('selected_supplier_id', $supplierId)
                  ->whereHas('offers', function ($qo) use ($request) {
                      $qo->where('user_id', $request->user()->id)
                         ->where('status', 'accepted');
                  });
            })
            ->orWhere(function ($q) use ($supplierCode) {
                $q->where('status', 'PENDING_BIDDING')
                  ->whereHas('product', function ($qp) use ($supplierCode) {
                      $qp->where('plu_code', 'like', $supplierCode . '%');
                  });
            })
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pos
        ]);
    }

    /**
     * CREATE: Generate PO Otomatis menggunakan Stored Procedure
     */
    public function generateAutoPO(Request $request)
    {
        $supplierId = $request->user()->supplier_id;
        $userId = $request->user()->id;

        try {
            DB::transaction(function () use ($supplierId, $userId) {
                DB::statement('CALL generate_auto_po_proc(?, ?)', [$supplierId, $userId]);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'PO Berhasil dibuat secara otomatis oleh Stored Procedure (PB Logic di DB)'
            ]);

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Semua stok mencukupi')) {
                $errorMessage = 'Semua stok masih mencukupi, tidak ada PO yang dibuat.';
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Pesan dari DB: ' . $errorMessage
            ], 422);
        }
    }

    /**
     * READ: Detail PO berdasarkan ID
     */
    public function show(Request $request, $id)
    {
        $supplier = $request->user()->supplier;
        if (!$supplier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil supplier tidak ditemukan.'
            ], 403);
        }

        $supplierId = $supplier->id;
        $supplierCode = $supplier->supplier_code;

        $po = PurchaseOrder::with(['product'])
            ->where(function($query) use ($supplierId, $supplierCode, $request) {
                $query->where(function ($q) use ($supplierId, $request) {
                    $q->where('selected_supplier_id', $supplierId)
                      ->whereHas('offers', function ($qo) use ($request) {
                          $qo->where('user_id', $request->user()->id)
                             ->where('status', 'accepted');
                      });
                })
                ->orWhere(function ($q) use ($supplierCode) {
                    $q->where('status', 'PENDING_BIDDING')
                      ->whereHas('product', function ($qp) use ($supplierCode) {
                          $qp->where('plu_code', 'like', $supplierCode . '%');
                      });
                });
            })
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $po
        ]);
    }

    /**
     * UPDATE: Pengajuan Penawaran Harga (Bidding) oleh Supplier
     */
    public function submitOffer(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;
        
        $request->validate([
            'price_per_pcs' => 'required|numeric|min:0'
        ]);

        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== 'PENDING_BIDDING') {
            return response()->json([
                'status' => 'error',
                'message' => 'PO ini sudah tidak menerima penawaran harga.'
            ], 422);
        }

        // Simpan penawaran harga dari supplier per user_id sales
        $offer = Offer::updateOrCreate(
            [
                'purchase_order_id' => $po->id,
                'user_id'           => $request->user()->id,
            ],
            [
                'supplier_id'       => $supplierId,
                'price_per_pcs'     => $request->price_per_pcs,
                'status'            => 'pending'
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Penawaran harga berhasil diajukan/diperbarui.',
            'data' => $offer
        ]);
    }

    /**
     * COMPARE: Menampilkan semua penawaran (offers) beserta kalkulasi harga kotor untuk PO
     */
    public function compareOffers($id)
    {
        $po = PurchaseOrder::with(['product'])->findOrFail($id);

        $offers = Offer::with(['supplier', 'user'])
            ->where('purchase_order_id', $po->id)
            ->get();

        $compareData = $offers->map(function ($offer) use ($po) {
            $totalGrossPrice = $po->qty_po * $offer->price_per_pcs;
            
            return [
                'id' => $offer->id,
                'supplier_name' => $offer->supplier ? $offer->supplier->name : 'N/A',
                'sales_username' => $offer->user ? $offer->user->username : 'N/A',
                'price_per_pcs' => (float) $offer->price_per_pcs,
                'total_gross_price' => (float) $totalGrossPrice,
                'status' => $offer->status,
                'created_at' => $offer->created_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'purchase_order' => $po,
                'offers' => $compareData
            ]
        ]);
    }

    /**
     * Fallback method untuk markAsRead PO
     */
    public function markAsRead(Request $request, $id)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'PO ditandai sebagai dibaca.'
        ]);
    }
}