<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Offer;
use App\Models\GoodsReceipt;
use App\Models\Retur;
use App\Models\Ttf;
use App\Models\VrsSchedule;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'md') {
            $products = Product::orderBy('name')->get();
            $purchaseOrders = PurchaseOrder::with(['product', 'supplier', 'goodsReceipt.retur', 'goodsReceipt.ttf'])
                ->latest()
                ->get();
            $offers = Offer::with(['purchaseOrder.product', 'supplier'])
                ->latest()
                ->get();
            $suppliers = Supplier::orderBy('name')->get();
            $goodsReceipts = GoodsReceipt::with(['purchaseOrder.product', 'retur'])->latest()->get();
            $vrsSchedules = VrsSchedule::with('purchaseOrder.product')->latest()->get();

            // Calculate service levels untuk setiap supplier
            $serviceLevels = [];
            foreach ($suppliers as $s) {
                $stats = DB::table('purchase_orders')
                    ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
                    ->leftJoin('returs', 'returs.goods_receipt_id', '=', 'goods_receipts.id')
                    ->where('purchase_orders.selected_supplier_id', $s->id)
                    ->select(
                        DB::raw('COALESCE(SUM(purchase_orders.qty_po), 0) as total_ordered'),
                        DB::raw('COALESCE(SUM(goods_receipts.qty_received), 0) as total_received'),
                        DB::raw('COALESCE(SUM(returs.qty_retur), 0) as total_retur')
                    )
                    ->first();
                $totalOrdered = (int) $stats->total_ordered;
                $totalReceived = (int) $stats->total_received;
                $totalRetur = (int) $stats->total_retur;
                $qtyReceivedClean = max(0, $totalReceived - $totalRetur);
                $serviceLevel = $totalOrdered > 0 ? round(($qtyReceivedClean / $totalOrdered) * 100, 2) : 0;
                $serviceLevels[$s->id] = [
                    'score' => $serviceLevel,
                    'total_ordered' => $totalOrdered,
                    'total_received' => $totalReceived,
                    'total_retur' => $totalRetur,
                    'clean_received' => $qtyReceivedClean
                ];
            }

            return view('dashboard', compact('products', 'purchaseOrders', 'offers', 'suppliers', 'goodsReceipts', 'vrsSchedules', 'serviceLevels'));
        } else {
            $supplier = $user->supplier;
            if (!$supplier) {
                abort(403, 'Profil supplier tidak ditemukan.');
            }

            // PO yang tersedia untuk supplier ini: yang dimenangkan oleh user sales ini ATAU status PENDING_BIDDING untuk barang milik supplier ini saja
            $supplierCode = $supplier->supplier_code;
            $purchaseOrders = PurchaseOrder::with(['product', 'goodsReceipt.retur', 'goodsReceipt.ttf'])
                ->where(function ($q) use ($supplier, $user) {
                    $q->where('selected_supplier_id', $supplier->id)
                      ->whereHas('offers', function ($qo) use ($user) {
                          $qo->where('user_id', $user->id)
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

            $myOffers = Offer::where('supplier_id', $supplier->id)
                ->pluck('status', 'purchase_order_id')
                ->toArray();

            $myOffersDetails = Offer::where('supplier_id', $supplier->id)
                ->with('purchaseOrder.product')
                ->latest()
                ->get();

            $goodsReceipts = GoodsReceipt::with(['purchaseOrder.product', 'retur', 'ttf'])
                ->whereHas('purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            $vrsSchedules = VrsSchedule::with('purchaseOrder.product')
                ->whereHas('purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            $ttfs = Ttf::with(['goodsReceipt.purchaseOrder.product'])
                ->whereHas('goodsReceipt.purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            // Hitung service level
            $stats = DB::table('purchase_orders')
                ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
                ->leftJoin('returs', 'returs.goods_receipt_id', '=', 'goods_receipts.id')
                ->join('offers', function ($join) use ($user) {
                    $join->on('offers.purchase_order_id', '=', 'purchase_orders.id')
                         ->where('offers.user_id', '=', $user->id)
                         ->where('offers.status', '=', 'accepted');
                })
                ->select(
                    DB::raw('COALESCE(SUM(purchase_orders.qty_po), 0) as total_ordered'),
                    DB::raw('COALESCE(SUM(goods_receipts.qty_received), 0) as total_received'),
                    DB::raw('COALESCE(SUM(returs.qty_retur), 0) as total_retur')
                )
                ->first();
            $totalOrdered = (int) $stats->total_ordered;
            $totalReceived = (int) $stats->total_received;
            $totalRetur = (int) $stats->total_retur;
            $qtyReceivedClean = max(0, $totalReceived - $totalRetur);
            $serviceLevel = $totalOrdered > 0 ? round(($qtyReceivedClean / $totalOrdered) * 100, 2) : 100.0;
            $performanceReport = [
                'score' => $serviceLevel,
                'total_ordered' => $totalOrdered,
                'total_received' => $totalReceived,
                'total_retur' => $totalRetur,
                'clean_received' => $qtyReceivedClean
            ];

            return view('dashboard', compact('supplier', 'purchaseOrders', 'myOffers', 'myOffersDetails', 'goodsReceipts', 'vrsSchedules', 'ttfs', 'serviceLevel', 'performanceReport'));
        }
    }

    public function generateAutoPO(Request $request)
    {
        $userId = $request->user()->id;

        try {
            DB::transaction(function () use ($userId) {
                DB::statement('CALL generate_auto_po_proc(?, ?)', [null, $userId]);
            });
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'PO Berhasil dibuat secara otomatis oleh Stored Procedure (PB Logic di DB)']);
            }
            return back()->with('success', 'PO Berhasil dibuat secara otomatis oleh Stored Procedure (PB Logic di DB)');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'Semua stok mencukupi')) {
                $errorMessage = 'Semua stok masih mencukupi, tidak ada PO yang dibuat.';
            }
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Pesan dari DB: ' . $errorMessage], 400);
            }
            return back()->with('error', 'Pesan dari DB: ' . $errorMessage);
        }
    }

    public function submitOffer(Request $request)
    {
        $request->validate([
            'prices' => 'required|array',
            'prices.*' => 'nullable|numeric|min:0'
        ]);
 
        $supplier = $request->user()->supplier;
        if (!$supplier) {
            return back()->with('error', 'Profil supplier tidak ditemukan.');
        }
 
        $submittedCount = 0;
        foreach ($request->prices as $poId => $price) {
            if ($price === null || $price === '') {
                continue;
            }
 
            $po = PurchaseOrder::find($poId);
            if ($po && $po->status === 'PENDING_BIDDING') {
                Offer::updateOrCreate(
                    [
                        'purchase_order_id' => $po->id,
                        'user_id'           => $request->user()->id,
                    ],
                    [
                        'supplier_id'       => $supplier->id,
                        'price_per_pcs'     => $price,
                        'status'            => 'pending'
                    ]
                );
                $submittedCount++;
            }
        }
 
        if ($submittedCount === 0) {
            return back()->with('error', 'Tidak ada penawaran harga valid yang diisi.');
        }
 
        return back()->with('success', $submittedCount . ' penawaran harga berhasil diajukan sekaligus!');
    }

    public function approveOffer(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'offer_id'          => 'required|exists:offers,id',
            'delivery_deadline' => 'required|date'
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);
        $selectedOffer = Offer::findOrFail($request->offer_id);
        
        $ttfData = null;

        DB::transaction(function () use ($po, $selectedOffer, $request, &$ttfData) {
            $po->update([
                'status'               => 'APPROVED',
                'selected_supplier_id' => $selectedOffer->supplier_id,
                'delivery_deadline'    => $request->delivery_deadline
            ]);

            Offer::where('purchase_order_id', $po->id)
                ->where('id', $selectedOffer->id)
                ->update(['status' => 'accepted']);

            Offer::where('purchase_order_id', $po->id)
                ->where('id', '!=', $selectedOffer->id)
                ->update(['status' => 'rejected']);

            // 1. Create clean Goods Receipt (LPB)
            $barcode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po->product->name));
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'qty_received'      => $po->qty_po,
                'received_at'       => now(),
                'barcode'           => $barcode,
            ]);

            // 2. Create clean TTF
            $totalAmount = $po->qty_po * $selectedOffer->price_per_pcs;
            $ttf = Ttf::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'total_amount'     => $totalAmount,
                'total_deductions' => 0,
                'status_payment'   => 'pending'
            ]);

            // 3. Complete VRS schedule if exists
            VrsSchedule::where('purchase_order_id', $po->id)->update([
                'status' => 'completed',
                'actual_arrival_at' => now()
            ]);

            // 4. Update Product Stock
            $product = $po->product;
            if ($product) {
                $product->increment('on_hand', $po->qty_po);
            }

            $ttfData = [
                'ttf_id' => $ttf->id,
                'total_amount' => $totalAmount,
                'price_per_pcs' => $selectedOffer->price_per_pcs
            ];
        });

        // 5. Trigger Webhook Gateway for WhatsApp Notification (Outside Transaction)
        if ($ttfData) {
            $supplier = Supplier::find($selectedOffer->supplier_id);
            if ($supplier && $supplier->whatsapp_number) {
                try {
                    \Illuminate\Support\Facades\Http::timeout(3)->post(env('WHATSAPP_WEBHOOK_URL', 'https://api.amandamart.com/webhook/whatsapp'), [
                        'to'        => $supplier->whatsapp_number,
                        'message'   => "Halo {$supplier->name}, penawaran Anda untuk PO {$po->po_number} telah disetujui dengan harga modal Rp " . number_format($ttfData['price_per_pcs'], 0, ',', '.') . "/PCS. Nota TTF #{$ttfData['ttf_id']} dengan total Rp " . number_format($ttfData['total_amount'], 0, ',', '.') . " telah diterbitkan secara bersih. Terima kasih.",
                        'po_number' => $po->po_number,
                        'ttf_id'    => $ttfData['ttf_id'],
                        'amount'    => $ttfData['total_amount']
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal mengirim webhook WhatsApp: " . $e->getMessage());
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pemenang lelang telah disetujui. Status PO APPROVED, LPB terbit, dan TTF #' . $ttfData['ttf_id'] . ' berhasil diterbitkan otomatis.']);
        }
        return back()->with('success', 'Pemenang lelang telah disetujui. Status PO APPROVED, LPB terbit, dan TTF #' . $ttfData['ttf_id'] . ' berhasil diterbitkan otomatis.');
    }

    public function approveOfferQuick(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'price_per_pcs'     => 'required|numeric|min:0',
            'delivery_deadline' => 'required|date'
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);
        
        $selectedOffer = null;
        $ttfData = null;

        DB::transaction(function () use ($po, $request, &$selectedOffer, &$ttfData) {
            // 1. Buat penawaran baru
            $selectedOffer = Offer::create([
                'purchase_order_id' => $po->id,
                'supplier_id'       => $request->supplier_id,
                'user_id'           => $request->user()->id,
                'price_per_pcs'     => $request->price_per_pcs,
                'status'            => 'accepted'
            ]);

            // 2. Setujui PO
            $po->update([
                'status'               => 'APPROVED',
                'selected_supplier_id' => $request->supplier_id,
                'delivery_deadline'    => $request->delivery_deadline
            ]);

            // 3. Tolak penawaran lain jika ada
            Offer::where('purchase_order_id', $po->id)
                ->where('id', '!=', $selectedOffer->id)
                ->update(['status' => 'rejected']);

            // 4. Create clean Goods Receipt (LPB)
            $barcode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po->product->name));
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'qty_received'      => $po->qty_po,
                'received_at'       => now(),
                'barcode'           => $barcode,
            ]);

            // 5. Create clean TTF
            $totalAmount = $po->qty_po * $request->price_per_pcs;
            $ttf = Ttf::create([
                'goods_receipt_id' => $goodsReceipt->id,
                'total_amount'     => $totalAmount,
                'total_deductions' => 0,
                'status_payment'   => 'pending'
            ]);

            // 6. Complete VRS schedule if exists
            VrsSchedule::where('purchase_order_id', $po->id)->update([
                'status' => 'completed',
                'actual_arrival_at' => now()
            ]);

            // 7. Update Product Stock
            $product = $po->product;
            if ($product) {
                $product->increment('on_hand', $po->qty_po);
            }

            $ttfData = [
                'ttf_id' => $ttf->id,
                'total_amount' => $totalAmount,
                'price_per_pcs' => $request->price_per_pcs
            ];
        });

        // 8. Trigger Webhook Gateway for WhatsApp Notification (Outside Transaction)
        if ($ttfData && $selectedOffer) {
            $supplier = Supplier::find($request->supplier_id);
            if ($supplier && $supplier->whatsapp_number) {
                try {
                    \Illuminate\Support\Facades\Http::timeout(3)->post(env('WHATSAPP_WEBHOOK_URL', 'https://api.amandamart.com/webhook/whatsapp'), [
                        'to'        => $supplier->whatsapp_number,
                        'message'   => "Halo {$supplier->name}, penawaran Anda untuk PO {$po->po_number} telah disetujui dengan harga modal Rp " . number_format($ttfData['price_per_pcs'], 0, ',', '.') . "/PCS. Nota TTF #{$ttfData['ttf_id']} dengan total Rp " . number_format($ttfData['total_amount'], 0, ',', '.') . " telah diterbitkan secara bersih. Terima kasih.",
                        'po_number' => $po->po_number,
                        'ttf_id'    => $ttfData['ttf_id'],
                        'amount'    => $ttfData['total_amount']
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal mengirim webhook WhatsApp: " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Penawaran dibuat, vendor disetujui, dan Faktur TTF #' . $ttfData['ttf_id'] . ' berhasil diterbitkan otomatis.');
    }

    public function createVrsBooking(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'scheduled_date'    => 'required|date',
            'time_slot'         => 'required|string'
        ]);

        $exists = VrsSchedule::where('purchase_order_id', $request->purchase_order_id)->exists();
        if ($exists) {
            return back()->with('error', 'Antrean logistik untuk PO ini sudah terdaftar.');
        }

        // Batasi kuota antrean: Maksimal 5 truk per slot waktu di tanggal yang sama (Karena DC hanya memiliki 5 tempat)
        $slotCount = VrsSchedule::where('scheduled_date', $request->scheduled_date)
            ->where('time_slot', $request->time_slot)
            ->where('status', '!=', 'cancelled')
            ->count();
        if ($slotCount >= 5) {
            return back()->with('error', 'Slot waktu ' . $request->time_slot . ' pada tanggal ' . $request->scheduled_date . ' sudah penuh (Maksimal 5 truk). Silakan pilih tanggal atau slot waktu lain.');
        }

        VrsSchedule::create([
            'purchase_order_id' => $request->purchase_order_id,
            'scheduled_date'    => $request->scheduled_date,
            'time_slot'         => $request->time_slot,
            'status'            => 'pending',
        ]);

        return back()->with('success', 'Booking antrean logistik berhasil dibuat.');
    }

    public function storeLpb(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'qty_received'      => 'required|integer|min:0',
            'qty_retur'         => 'nullable|integer|min:0',
            'reason'            => 'required_if:qty_retur,>0|string|nullable',
            'barcode'           => 'required|string',
            'received_at'       => 'required|date',
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);

        DB::transaction(function () use ($request, $po) {
            // 1. Catat Goods Receipt
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'qty_received'      => $request->qty_received,
                'received_at'       => $request->received_at,
                'barcode'           => $request->barcode,
            ]);

            // 2. Catat Retur jika ada
            if ($request->filled('qty_retur') && $request->qty_retur > 0) {
                Retur::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id'       => $po->product_id,
                    'qty_retur'        => $request->qty_retur,
                    'reason'           => $request->reason,
                ]);
            }

            // Update VRS status jika ada
            VrsSchedule::where('purchase_order_id', $po->id)->update([
                'status' => 'completed',
                'actual_arrival_at' => now()
            ]);
            
            // Update stok
            $product = $po->product;
            $qtyReceivedClean = max(0, $request->qty_received - ($request->qty_retur ?? 0));
            $product->increment('on_hand', $qtyReceivedClean);
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'LPB (Penerimaan Barang) dan Retur berhasil dicatat.']);
        }
        return back()->with('success', 'LPB (Penerimaan Barang) dan Retur berhasil dicatat.');
    }

    public function generateTtf(Request $request)
    {
        $request->validate([
            'goods_receipt_id' => 'required|exists:goods_receipts,id'
        ]);

        $lpb = GoodsReceipt::with(['purchaseOrder.product', 'retur'])->findOrFail($request->goods_receipt_id);
        $supplierId = $lpb->purchaseOrder->selected_supplier_id;

        if (!$supplierId) {
            return back()->with('error', 'Supplier tidak ditemukan untuk PO ini.');
        }

        $acceptedOffer = Offer::where('purchase_order_id', $lpb->purchase_order_id)
            ->where('supplier_id', $supplierId)
            ->where('status', 'accepted')
            ->first();

        if (!$acceptedOffer) {
            return back()->with('error', 'Harga penawaran yang disetujui (accepted offer) tidak ditemukan untuk PO ini.');
        }

        $pricePerPcs = $acceptedOffer->price_per_pcs;
        $totalAmount = $lpb->qty_received * $pricePerPcs;
        $qtyRetur = $lpb->retur ? $lpb->retur->qty_retur : 0;
        $totalDeductions = $qtyRetur * $pricePerPcs;
        $finalPayment = $totalAmount - $totalDeductions;

        Ttf::create([
            'goods_receipt_id' => $lpb->id,
            'total_amount'     => $finalPayment,
            'total_deductions' => $totalDeductions,
            'status_payment'   => 'pending'
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tagihan (TTF) berhasil dibuat.']);
        }
        return back()->with('success', 'Tagihan (TTF) berhasil dibuat.');
    }

    /**
     * Menampilkan detail LPB untuk cetak/download
     */
    public function showLpb(Request $request, $id)
    {
        $lpb = GoodsReceipt::with(['purchaseOrder.product', 'purchaseOrder.supplier', 'retur'])->findOrFail($id);

        if ($request->user()->role === 'supplier') {
            $hasAcceptedOffer = Offer::where('purchase_order_id', $lpb->purchase_order_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'accepted')
                ->exists();
            if (!$hasAcceptedOffer) {
                abort(403, 'Anda tidak memiliki akses ke LPB ini.');
            }
        }

        return view('lpb-detail', compact('lpb'));
    }

    /**
     * Menampilkan detail TTF untuk cetak/download
     */
    public function showTtf(Request $request, $id)
    {
        $ttf = Ttf::with(['goodsReceipt.purchaseOrder.product', 'goodsReceipt.purchaseOrder.supplier', 'goodsReceipt.retur'])->findOrFail($id);

        if ($request->user()->role === 'supplier') {
            $hasAcceptedOffer = Offer::where('purchase_order_id', $ttf->goodsReceipt->purchase_order_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'accepted')
                ->exists();
            if (!$hasAcceptedOffer) {
                abort(403, 'Anda tidak memiliki akses ke TTF ini.');
            }
        }

        return view('ttf-detail', compact('ttf'));
    }

    /**
     * Memperbarui profil supplier (WA) dan password user
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'whatsapp_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed'
        ]);

        if ($user->role === 'supplier') {
            $supplier = $user->supplier;
            if ($supplier) {
                $supplier->update([
                    'whatsapp_number' => $request->whatsapp_number
                ]);
            }
        }

        if ($request->filled('password')) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password)
            ]);
        }

        return back()->with('success', 'Profil dan pengaturan keamanan berhasil diperbarui.');
    }
}
