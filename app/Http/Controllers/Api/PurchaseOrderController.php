<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str; // Import ini sudah benar
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        // Ambil PO milik supplier, sertakan data DC dan item produk
        $pos = PurchaseOrder::with(['dc', 'items.product']) 
            ->withCount('items')
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pos
        ]);
    }

    public function generateAutoPO(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        // Cari produk milik supplier yang stoknya butuh diisi
        $productsToOrder = Product::where('supplier_id', $supplierId)
            ->whereRaw('on_hand < max_stock')
            ->get();

        if ($productsToOrder->isEmpty()) {
            return response()->json([
                'status' => 'info',
                'message' => 'Semua stok masih mencukupi, tidak ada PO yang dibuat.'
            ]);
        }

        return DB::transaction(function () use ($productsToOrder, $supplierId, $request) {
            // PERBAIKAN: Menggunakan Str::random(4) bukan str_random(4)
            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'supplier_id' => $supplierId,
                'dc_id' => 1, 
                'type' => 'dc',
                'order_date' => now(),
                'expire_date' => now()->addDays(7),
                'status' => 'active'
            ]);

            foreach ($productsToOrder as $product) {
                $selisih = $product->max_stock - $product->on_hand;
                $jumlahDus = floor($selisih / $product->minor);
                $qtyPb = $jumlahDus * $product->minor;

                if ($qtyPb > 0) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $product->id,
                        'qty_pb' => $qtyPb,
                        'qty_ordered' => $qtyPb,
                        'unit_price' => $product->unit_price
                    ]);
                }
            }

            // Trigger Notifikasi: PO Baru Berhasil Dibuat
            Notification::create([
                'user_id' => $request->user()->id,
                'title' => 'Pesanan Baru (PO)',
                'body' => 'Sistem AmandaMart telah membuat PO baru otomatis: ' . $po->po_number,
                'type' => 'PO_NEW',
                'reference_id' => $po->id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'PO Otomatis berhasil dibuat berdasarkan Rumus PB',
                'data' => $po->load('items')
            ]);
        });
    }

    public function show(Request $request, $id)
    {
        $supplierId = $request->user()->supplier_id;

        $po = PurchaseOrder::with(['items.product', 'dc'])
            ->where('supplier_id', $supplierId)
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $po
        ]);
    }

    public function updateItem(Request $request, $id)
    {
        $item = PurchaseOrderItem::with(['product', 'purchaseOrder'])->findOrFail($id);
        
        $request->validate([
            'qty_ordered' => 'required|integer|min:0'
        ]);

        $newQty = $request->qty_ordered;
        $product = $item->product;

        // Validasi Minor
        if ($newQty % $product->minor !== 0) {
            return response()->json([
                'status' => 'error',
                'message' => "Jumlah harus kelipatan dari Minor ({$product->minor} pcs)."
            ], 422);
        }

        // Validasi Max Stock
        if (($newQty + $product->on_hand) > $product->max_stock) {
            return response()->json([
                'status' => 'error',
                'message' => "Jumlah terlalu banyak! Rak hanya muat " . ($product->max_stock - $product->on_hand) . " pcs lagi."
            ], 422);
        }

        $item->update(['qty_ordered' => $newQty]);

        // Trigger Notifikasi: Supplier update Qty
        Notification::create([
            'user_id' => $request->user()->id,
            'title' => 'Qty PO Diperbarui',
            'body' => 'Anda telah menyesuaikan jumlah pesanan pada ' . $item->purchaseOrder->po_number,
            'type' => 'PO_UPDATE',
            'reference_id' => $item->purchase_order_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Qty PO berhasil disesuaikan oleh Supplier',
            'data' => $item
        ]);
    }
}