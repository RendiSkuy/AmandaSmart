<!-- Alert Petunjuk Tahap -->
<div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-955/20 border-l-4 border-blue-600 rounded-r-xl text-slate-705 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
    <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="text-[11px]">
        <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 2 & 3: Proses Bidding & Pemilihan Pemenang (MD Approval)</span>
        <p class="mt-0.5 leading-relaxed">MD meninjau serta membandingkan penawaran harga modal masuk dari berbagai akun sales supplier rekanan. Tentukan batas waktu pengiriman (Deadline Kirim), lalu tekan tombol 'Setujui' pada baris sales terpilih untuk menyetujui pemenang PO secara dinamis menggunakan teknologi AJAX tanpa reload halaman.</p>
    </div>
</div>

<!-- Stage 2 & 3 Body -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-455 rounded-lg flex items-center justify-center text-xs">🤝</span>
        Stage 2 & 3: Proses Bidding & Pemilihan Pemenang (MD Approval)
    </h2>
    
    @php
        $pendingPos = $purchaseOrders->where('status', 'PENDING_BIDDING');
        $pendingPoIds = $pendingPos->pluck('id');
    @endphp

    @if($pendingPos->isEmpty())
        <p class="text-[11px] text-slate-500 dark:text-slate-400 italic text-center py-8 bg-slate-50 dark:bg-slate-955/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada draf Purchase Order (PO) berstatus PENDING_BIDDING saat ini.</p>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kolom Kiri: Daftar PT & Sales -->
            <div class="lg:col-span-1 space-y-3">
                <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest font-mono">Pilih Supplier & Akun Sales:</h4>
                
                <div class="space-y-2">
                    @foreach($suppliers as $sup)
                    @php
                        $poOffersForSup = $offers->where('supplier_id', $sup->id)->filter(fn($o) => $pendingPoIds->contains($o->purchase_order_id));
                        $salesWithOffers = $poOffersForSup->pluck('user')->unique('id')->filter();
                    @endphp
                    <div class="border border-slate-200 dark:border-slate-850 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                        <!-- Header PT (Clickable) -->
                        <button type="button" 
                                onclick="toggleSupplier('sup-{{ $sup->id }}')"
                                class="w-full text-left px-3.5 py-2.5 bg-slate-50 dark:bg-slate-955/40 hover:bg-slate-100 dark:hover:bg-slate-955 transition flex justify-between items-center border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center space-x-2">
                                <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                <span class="text-xs font-bold text-slate-805 dark:text-slate-200">{{ $sup->name }}</span>
                            </div>
                            <span class="text-[8px] bg-blue-50 dark:bg-blue-950/30 text-blue-800 dark:text-blue-400 px-1.5 py-0.5 rounded font-bold uppercase">
                                {{ $salesWithOffers->count() }} Sales
                            </span>
                        </button>

                        <!-- List Sales (Collapsible) -->
                        <div id="sup-{{ $sup->id }}" class="hidden p-1.5 space-y-1 bg-white dark:bg-slate-900 border-t border-slate-50 dark:border-slate-850">
                            @if($salesWithOffers->isEmpty())
                                <p class="text-[9px] text-slate-400 italic p-2 text-center">Belum ada penawaran bidding.</p>
                            @else
                                @foreach($salesWithOffers as $salesUser)
                                @php
                                    $salesOfferCount = $poOffersForSup->where('user_id', $salesUser->id)->count();
                                @endphp
                                <button type="button"
                                        onclick="selectSalesOffer('{{ $salesUser->id }}')"
                                        class="w-full text-left px-2.5 py-1.5 text-[11px] rounded-lg transition hover:bg-blue-50 dark:hover:bg-blue-955/20 text-slate-650 dark:text-slate-400 hover:text-blue-700 dark:hover:text-blue-400 font-semibold flex justify-between items-center sales-btn"
                                        id="btn-sales-{{ $salesUser->id }}">
                                    <span>👤 {{ $salesUser->username }}</span>
                                    <span class="text-[9px] bg-slate-105 dark:bg-slate-800 text-slate-500 dark:text-slate-455 px-1 py-0.5 rounded font-mono">{{ $salesOfferCount }} Barang</span>
                                </button>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Kolom Kanan: Detail Penawaran Sales -->
            <div class="lg:col-span-2">
                <h4 class="text-[10px] font-bold text-slate-455 dark:text-slate-550 uppercase tracking-widest mb-3 font-mono">Detail Penawaran:</h4>
                
                <!-- Placeholder -->
                <div class="bg-slate-50 dark:bg-slate-955/30 border border-slate-200 dark:border-slate-850 border-dashed rounded-2xl p-6 min-h-[220px] flex flex-col justify-center items-center text-center shadow-inner"
                     id="offer-detail-placeholder">
                    <div class="text-slate-350 dark:text-slate-700 text-4xl mb-2">🤝</div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs leading-relaxed">Pilih salah satu Nama Perusahaan di sebelah kiri, lalu pilih akun sales rekanan untuk melihat rincian penawaran harga modal barang.</p>
                </div>

                <!-- Render penawaran tersembunyi per Sales User -->
                @foreach($suppliers as $sup)
                @php
                    $poOffersForSup = $offers->where('supplier_id', $sup->id)->filter(fn($o) => $pendingPoIds->contains($o->purchase_order_id));
                    $salesWithOffers = $poOffersForSup->pluck('user')->unique('id')->filter();
                @endphp
                    @foreach($salesWithOffers as $salesUser)
                    @php
                        $salesOffers = $poOffersForSup->where('user_id', $salesUser->id);
                        $salesOffersGrouped = $salesOffers->groupBy('purchase_order_id');
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/60 rounded-2xl p-4 shadow-sm hidden space-y-4"
                         id="offer-detail-sales-{{ $salesUser->id }}">
                        
                        <!-- Header Informasi Sales -->
                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                            <div>
                                <h5 class="text-[8px] font-extrabold text-slate-400 dark:text-slate-555 uppercase tracking-wider">Perusahaan Rekanan</h5>
                                <p class="text-xs font-extrabold text-slate-850 dark:text-slate-200">{{ $sup->name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-955/40 px-2 py-1 rounded-lg border border-blue-100/50 dark:border-blue-900/30 font-semibold">Akun Sales: {{ $salesUser->username }}</span>
                            </div>
                        </div>

                        <!-- List Semua Barang yang Ditawar -->
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-bold text-slate-455 dark:text-slate-500 uppercase tracking-widest font-mono">Daftar Penawaran Harga Modal:</h4>
                            
                            @foreach($salesOffersGrouped as $poId => $poOffers)
                            @php
                                $poItem = $pendingPos->where('id', $poId)->first();
                            @endphp
                            @if($poItem)
                            <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-955/20 hover:bg-white dark:hover:bg-slate-900 hover:shadow-sm transition-all duration-150 space-y-3 po-card" id="po-card-{{ $poItem->id }}">
                                <div class="flex justify-between items-start flex-wrap gap-2">
                                    <div>
                                        <span class="px-1.5 py-0.5 text-[8px] font-bold bg-amber-100 dark:bg-amber-955 text-amber-800 dark:text-amber-450 border border-amber-200 rounded">PO: {{ $poItem->po_number }}</span>
                                        <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 font-semibold">Total Item: {{ $poItem->details->count() }} Jenis</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[8px] bg-blue-50 dark:bg-blue-955 text-blue-700 dark:text-blue-400 px-1.5 py-0.5 rounded font-mono font-bold uppercase">Bidding Open</span>
                                    </div>
                                </div>

                                <!-- Table of items inside the PO card -->
                                <div class="overflow-hidden border border-slate-200/50 dark:border-slate-805 rounded-lg bg-white dark:bg-slate-955/20">
                                    <table class="w-full text-left text-[10px] border-collapse">
                                        <thead>
                                            <tr class="bg-slate-50 dark:bg-slate-955/60 text-slate-550 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800">
                                                <th class="py-1 px-2">Nama Barang</th>
                                                <th class="py-1 px-2 text-center w-16">Qty PO</th>
                                                <th class="py-1 px-2 text-right">Harga Modal</th>
                                                <th class="py-1 px-2 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900/10">
                                            @php
                                                $grandTotal = 0;
                                            @endphp
                                            @foreach($poOffers as $off)
                                                @php
                                                    $detail = $poItem->details->firstWhere('product_id', $off->product_id);
                                                    $qty = $detail ? $detail->qty_po : 0;
                                                    $itemTotal = $off->price_per_pcs * $qty;
                                                    $grandTotal += $itemTotal;
                                                @endphp
                                                <tr>
                                                    <td class="py-1.5 px-2 font-medium text-slate-750 dark:text-slate-350">
                                                        {{ $off->product->name ?? 'Produk' }}
                                                    </td>
                                                    <td class="py-1.5 px-2 text-center font-semibold font-mono text-slate-600 dark:text-slate-400">
                                                        {{ $qty }} PCS
                                                    </td>
                                                    <td class="py-1.5 px-2 text-right text-blue-600 dark:text-blue-455 font-bold font-mono">
                                                        Rp {{ number_format($off->price_per_pcs, 0, ',', '.') }}
                                                    </td>
                                                    <td class="py-1.5 px-2 text-right font-mono text-slate-605 dark:text-slate-400">
                                                        Rp {{ number_format($itemTotal, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex flex-wrap justify-between items-center gap-2.5 pt-2.5 border-t border-slate-200/50 dark:border-slate-800">
                                    <div>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium font-mono">Total Harga Kotor: <strong class="text-slate-800 dark:text-slate-200 font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></span>
                                    </div>
                                    
                                    <!-- Form Approve -->
                                    <form action="{{ route('dashboard.offers.approve') }}" method="POST" class="ajax-approve-form flex flex-wrap items-center gap-1.5" data-po-id="{{ $poItem->id }}" data-sales-id="{{ $salesUser->id }}">
                                        @csrf
                                        <input type="hidden" name="purchase_order_id" value="{{ $poItem->id }}">
                                        <input type="hidden" name="offer_id" value="{{ $poOffers->first()->id }}">
                                        
                                        <div class="flex items-center space-x-1">
                                            <label class="text-[9px] font-bold text-slate-550 dark:text-slate-450 uppercase whitespace-nowrap">Deadline:</label>
                                            <input type="date" name="delivery_deadline" required 
                                                   class="text-[11px] border border-slate-350 dark:border-slate-800 rounded-md px-2 py-0.5 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-slate-950 text-slate-855 dark:text-white font-semibold">
                                        </div>

                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-2.5 rounded-md text-[10px] shadow-sm transition duration-150 flex items-center space-x-1">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>Setujui</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif
</div>
