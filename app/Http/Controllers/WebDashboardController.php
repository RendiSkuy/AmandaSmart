<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Offer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\Retur;
use App\Models\Ttf;
use App\Models\VrsSchedule;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'md') {
            $products = Product::orderBy('name')->get();
            $purchaseOrders = PurchaseOrder::with(['details.product', 'offers.user', 'offers.supplier', 'supplier', 'goodsReceipt.details.product', 'goodsReceipt.returs', 'goodsReceipt.ttf', 'vrsSchedule'])
                ->latest()
                ->get();
            $offers = Offer::with(['purchaseOrder.details.product', 'supplier', 'product'])
                ->latest()
                ->get();
            $suppliers = Supplier::orderBy('name')->get();
            $goodsReceipts = GoodsReceipt::with(['purchaseOrder.details.product', 'details.product', 'returs'])->latest()->get();
            $vrsSchedules = VrsSchedule::with('purchaseOrder.details.product')->latest()->get();

            // Calculate service levels untuk setiap supplier
            $serviceLevels = [];
            foreach ($suppliers as $s) {
                $stats = DB::table('purchase_orders')
                    ->where('purchase_orders.selected_supplier_id', $s->id)
                    ->leftJoin('purchase_order_details', 'purchase_order_details.purchase_order_id', '=', 'purchase_orders.id')
                    ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
                    ->leftJoin('goods_receipt_details', function ($join) {
                        $join->on('goods_receipt_details.goods_receipt_id', '=', 'goods_receipts.id')
                             ->on('goods_receipt_details.product_id', '=', 'purchase_order_details.product_id');
                    })
                    ->leftJoin('returs', function ($join) {
                        $join->on('returs.goods_receipt_id', '=', 'goods_receipts.id')
                             ->on('returs.product_id', '=', 'purchase_order_details.product_id');
                    })
                    ->select(
                        DB::raw('COALESCE(SUM(purchase_order_details.qty_po), 0) as total_ordered'),
                        DB::raw('COALESCE(SUM(goods_receipt_details.qty_received), 0) as total_received'),
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
            $purchaseOrders = PurchaseOrder::with(['details.product', 'goodsReceipt.details.product', 'goodsReceipt.returs', 'goodsReceipt.ttf'])
                ->where(function ($q) use ($supplier, $user) {
                    $q->where('selected_supplier_id', $supplier->id)
                      ->whereHas('offers', function ($qo) use ($user) {
                          $qo->where('user_id', $user->id)
                             ->where('status', 'accepted');
                      });
                })
                ->orWhere(function ($q) use ($supplierCode) {
                    $q->where('status', 'PENDING_BIDDING')
                      ->whereHas('details.product', function ($qp) use ($supplierCode) {
                          $qp->where('plu_code', 'like', $supplierCode . '%');
                      });
                })
                ->latest()
                ->get();

            $myOffers = Offer::where('supplier_id', $supplier->id)
                ->pluck('status', 'purchase_order_id')
                ->toArray();

            $myOffersDetails = Offer::where('supplier_id', $supplier->id)
                ->with(['purchaseOrder.details.product', 'product'])
                ->latest()
                ->get();

            $goodsReceipts = GoodsReceipt::with(['purchaseOrder.details.product', 'details.product', 'returs', 'ttf'])
                ->whereHas('purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            $vrsSchedules = VrsSchedule::with('purchaseOrder.details.product')
                ->whereHas('purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            $ttfs = Ttf::with(['goodsReceipt.purchaseOrder.details.product'])
                ->whereHas('goodsReceipt.purchaseOrder.offers', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'accepted');
                })
                ->latest()
                ->get();

            // Hitung service level
            $stats = DB::table('purchase_orders')
                ->join('offers', function ($join) use ($user) {
                    $join->on('offers.purchase_order_id', '=', 'purchase_orders.id')
                         ->where('offers.user_id', '=', $user->id)
                         ->where('offers.status', '=', 'accepted');
                })
                ->leftJoin('purchase_order_details', 'purchase_order_details.purchase_order_id', '=', 'purchase_orders.id')
                ->leftJoin('goods_receipts', 'goods_receipts.purchase_order_id', '=', 'purchase_orders.id')
                ->leftJoin('goods_receipt_details', function ($join) {
                    $join->on('goods_receipt_details.goods_receipt_id', '=', 'goods_receipts.id')
                         ->on('goods_receipt_details.product_id', '=', 'purchase_order_details.product_id');
                })
                ->leftJoin('returs', function ($join) {
                    $join->on('returs.goods_receipt_id', '=', 'goods_receipts.id')
                         ->on('returs.product_id', '=', 'purchase_order_details.product_id');
                })
                ->select(
                    DB::raw('COALESCE(SUM(purchase_order_details.qty_po), 0) as total_ordered'),
                    DB::raw('COALESCE(SUM(goods_receipt_details.qty_received), 0) as total_received'),
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
        ]);
 
        $supplier = $request->user()->supplier;
        if (!$supplier) {
            return back()->with('error', 'Profil supplier tidak ditemukan.');
        }
 
        $submittedCount = 0;
        foreach ($request->prices as $poId => $productPrices) {
            if (!is_array($productPrices)) {
                continue;
            }
 
            $po = PurchaseOrder::find($poId);
            if (!$po || $po->status !== 'PENDING_BIDDING') {
                continue;
            }
 
            foreach ($productPrices as $productId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }
 
                Offer::updateOrCreate(
                    [
                        'purchase_order_id' => $po->id,
                        'product_id'        => $productId,
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
        $winningUserId = $selectedOffer->user_id;

        DB::transaction(function () use ($po, $selectedOffer, $winningUserId, $request) {
            $po->update([
                'status'               => 'APPROVED',
                'selected_supplier_id' => $selectedOffer->supplier_id,
                'delivery_deadline'    => $request->delivery_deadline
            ]);

            // Setujui semua penawaran dari user sales pemenang untuk PO ini
            Offer::where('purchase_order_id', $po->id)
                ->where('user_id', $winningUserId)
                ->update(['status' => 'accepted']);

            // Tolak semua penawaran dari user sales lain untuk PO ini
            Offer::where('purchase_order_id', $po->id)
                ->where('user_id', '!=', $winningUserId)
                ->update(['status' => 'rejected']);

            // Salin harga penawaran ke purchase_order_details
            $acceptedOffers = Offer::where('purchase_order_id', $po->id)
                ->where('user_id', $winningUserId)
                ->get();
                
            foreach ($acceptedOffers as $off) {
                PurchaseOrderDetail::where('purchase_order_id', $po->id)
                    ->where('product_id', $off->product_id)
                    ->update(['price_per_pcs' => $off->price_per_pcs]);
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pemenang lelang telah disetujui. Status PO diperbarui menjadi APPROVED.']);
        }
        return back()->with('success', 'Pemenang lelang telah disetujui. Status PO diperbarui menjadi APPROVED.');
    }

    public function approveOfferQuick(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_id'       => 'required|exists:suppliers,id',
            'price_per_pcs'     => 'required|numeric|min:0',
            'delivery_deadline' => 'required|date'
        ]);

        $po = PurchaseOrder::with('details')->findOrFail($request->purchase_order_id);

        DB::transaction(function () use ($po, $request) {
            $salesUser = User::where('supplier_id', $request->supplier_id)->first();
            $userId = $salesUser ? $salesUser->id : $request->user()->id;

            // Buat penawaran baru untuk setiap item produk di detail PO
            foreach ($po->details as $detail) {
                Offer::create([
                    'purchase_order_id' => $po->id,
                    'supplier_id'       => $request->supplier_id,
                    'user_id'           => $userId,
                    'product_id'        => $detail->product_id,
                    'price_per_pcs'     => $request->price_per_pcs,
                    'status'            => 'accepted'
                ]);

                $detail->update(['price_per_pcs' => $request->price_per_pcs]);
            }

            // Setujui PO
            $po->update([
                'status'               => 'APPROVED',
                'selected_supplier_id' => $request->supplier_id,
                'delivery_deadline'    => $request->delivery_deadline
            ]);

            // Tolak penawaran lain jika ada
            Offer::where('purchase_order_id', $po->id)
                ->where('user_id', '!=', $userId)
                ->update(['status' => 'rejected']);
        });

        return back()->with('success', 'Penawaran dibuat dan vendor berhasil disetujui.');
    }

    public function createVrsBooking(Request $request)
    {
        if ($request->has('purchase_order_id')) {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'scheduled_date'    => 'required|date',
                'time_slot'         => 'required|string'
            ]);
            $poId = $request->purchase_order_id;
            $scheduledDates = [$poId => $request->scheduled_date];
            $timeSlots = [$poId => $request->time_slot];
        } else {
            $request->validate([
                'scheduled_dates' => 'required|array',
                'time_slots'      => 'required|array',
            ]);
            $scheduledDates = $request->scheduled_dates;
            $timeSlots = $request->time_slots;
        }

        $submittedCount = 0;
        $errors = [];

        foreach ($scheduledDates as $poId => $date) {
            $slot = $timeSlots[$poId] ?? null;

            if (!$date || !$slot) {
                continue;
            }

            $po = PurchaseOrder::find($poId);
            if (!$po || $po->status !== 'APPROVED') {
                continue;
            }

            $exists = VrsSchedule::where('purchase_order_id', $poId)->exists();
            if ($exists) {
                $errors[] = "Antrean logistik untuk PO {$po->po_number} sudah terdaftar.";
                continue;
            }

            // Batasi kuota antrean: Maksimal 5 truk per slot waktu di tanggal yang sama (Karena DC hanya memiliki 5 tempat)
            $slotCount = VrsSchedule::where('scheduled_date', $date)
                ->where('time_slot', $slot)
                ->where('status', '!=', 'cancelled')
                ->count();
            if ($slotCount >= 5) {
                $errors[] = "Slot waktu {$slot} pada tanggal {$date} untuk PO {$po->po_number} sudah penuh (Maksimal 5 truk).";
                continue;
            }

            VrsSchedule::create([
                'purchase_order_id' => $poId,
                'scheduled_date'    => $date,
                'time_slot'         => $slot,
                'status'            => 'pending',
            ]);

            $submittedCount++;
        }

        if ($submittedCount === 0) {
            $msg = count($errors) > 0 ? implode(' ', $errors) : 'Tidak ada jadwal valid yang diisi.';
            return back()->with('error', $msg);
        }

        $msg = "{$submittedCount} booking antrean logistik berhasil dibuat.";
        if (count($errors) > 0) {
            $msg .= ' Beberapa PO gagal: ' . implode(' ', $errors);
        }
        return back()->with('success', $msg);
    }

    public function storeLpb(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'barcode'           => 'required|string',
            'received_at'       => 'required|date',
            'qty_received'      => 'required|array',
            'qty_received.*'    => 'required|integer|min:0',
            'qty_retur'         => 'nullable|array',
            'qty_retur.*'       => 'nullable|integer|min:0',
            'reason'            => 'nullable|array',
            'reason.*'          => 'nullable|string',
        ]);

        $po = PurchaseOrder::with('details.product')->findOrFail($request->purchase_order_id);

        // Validasi Kritis (Revisi 2): Qty received tidak boleh melebihi qty yang dipesan di PO
        foreach ($po->details as $detail) {
            $received = (int) ($request->qty_received[$detail->product_id] ?? 0);
            if ($received < 0 || $received > $detail->qty_po) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Gagal! Jumlah barang fisik diterima untuk produk {$detail->product->name} ({$received} PCS) tidak boleh kurang dari 0 atau melebihi jumlah permintaan di PO ({$detail->qty_po} PCS)."
                    ], 422);
                }
                return back()->with('error', "Gagal! Jumlah barang fisik diterima untuk produk {$detail->product->name} ({$received} PCS) tidak boleh kurang dari 0 atau melebihi jumlah permintaan di PO ({$detail->qty_po} PCS).");
            }
        }

        DB::transaction(function () use ($request, $po) {
            // 1. Catat Goods Receipt Header
            $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $po->id,
                'received_at'       => $request->received_at,
                'barcode'           => $request->barcode,
            ]);

            // 2. Simpan Detail Penerimaan dan Retur per Produk
            foreach ($po->details as $detail) {
                $pId = $detail->product_id;
                $received = (int) ($request->qty_received[$pId] ?? 0);
                $returQty = isset($request->qty_retur[$pId]) && $request->qty_retur[$pId] !== '' 
                    ? (int) $request->qty_retur[$pId] 
                    : max(0, $detail->qty_po - $received);
                $reasonText = $request->reason[$pId] ?? '';

                // Detail LPB
                GoodsReceiptDetail::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id'       => $pId,
                    'qty_received'      => $received,
                ]);

                // Retur
                if ($returQty > 0) {
                    Retur::create([
                        'goods_receipt_id' => $goodsReceipt->id,
                        'product_id'       => $pId,
                        'qty_retur'        => $returQty,
                        'reason'           => $reasonText ?: 'Barang rusak/selisih',
                    ]);
                }

                // Tambah stok fisik bersih ke produk
                $product = $detail->product;
                $qtyReceivedClean = max(0, $received - $returQty);
                $product->increment('on_hand', $qtyReceivedClean);
            }

            // Update VRS status jika ada
            VrsSchedule::where('purchase_order_id', $po->id)->update([
                'status' => 'completed',
                'actual_arrival_at' => now()
            ]);
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
        $lpb = GoodsReceipt::with(['purchaseOrder.details.product', 'purchaseOrder.supplier', 'details.product', 'returs'])->findOrFail($id);

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
        $ttf = Ttf::with(['goodsReceipt.purchaseOrder.details.product', 'goodsReceipt.purchaseOrder.supplier', 'goodsReceipt.details.product', 'goodsReceipt.returs'])->findOrFail($id);

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
