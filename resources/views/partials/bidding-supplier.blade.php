<!-- Alert Petunjuk -->
<div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
    <svg width="16" height="16" class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="text-[11px]">
        <span class="font-bold text-xs text-emerald-900 dark:text-emerald-450 block">💡 Petunjuk Pengisian Penawaran (Bidding)</span>
        <p class="mt-0.5 leading-relaxed">Tinjau kuantitas PO ritel yang dibutuhkan. Harap input penawaran harga modal Anda per PCS untuk masing-masing barang, kemudian tekan tombol 'Kirim Semua Penawaran Final'.</p>
    </div>
</div>

<!-- Bidding Form Box -->
<div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
        <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-450 rounded-lg flex items-center justify-center text-xs">🤝</span>
        Stage 2: Mengajukan Penawaran Harga (Bidding)
    </h2>
    
    @php
        $biddingPos = $purchaseOrders->where('status', 'PENDING_BIDDING');
    @endphp

    @if($biddingPos->isEmpty())
        <p class="text-xs text-slate-400 dark:text-slate-550 italic text-center py-8 bg-slate-50 dark:bg-slate-955/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada draf Purchase Order (PO) terbuka untuk bidding penawaran saat ini.</p>
    @else
        <form action="{{ route('dashboard.offers.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div class="space-y-3">
                @foreach($biddingPos as $po)
                <div class="p-4 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-955/20 shadow-inner space-y-3">
                    <div>
                        <span class="px-2 py-0.5 text-[8px] font-bold bg-amber-100 dark:bg-amber-955 text-amber-800 dark:text-amber-450 border border-amber-200 rounded uppercase">Bidding Open</span>
                        <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-1.5">Faktur PO: <span class="font-mono text-blue-600 dark:text-blue-450">{{ $po->po_number }}</span></h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Silakan ajukan penawaran harga modal untuk setiap produk di bawah ini:</p>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-lg bg-white dark:bg-slate-900/40">
                        <table class="w-full text-left text-[11px] border-collapse">
                            <thead>
                                <tr class="bg-slate-100/80 dark:bg-slate-955/60 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800 font-bold">
                                    <th class="px-3 py-2">Nama Barang</th>
                                    <th class="px-3 py-2 text-center w-24">Jumlah PO</th>
                                    <th class="px-3 py-2 w-48">Penawaran Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900/10">
                                @foreach($po->details as $detail)
                                @php
                                    $existingOffer = $myOffersDetails->where('user_id', auth()->user()->id)
                                                                     ->where('purchase_order_id', $po->id)
                                                                     ->where('product_id', $detail->product_id)
                                                                     ->first();
                                @endphp
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $detail->product->name }}</span>
                                        <span class="text-[9px] text-slate-400 font-mono">{{ $detail->product->plu_code }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-center font-extrabold font-mono text-blue-600 dark:text-blue-400 bg-blue-50/20 dark:bg-blue-955/10">
                                        {{ $detail->qty_po }} PCS
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex items-center space-x-1.5">
                                            <div class="relative flex-grow">
                                                <span class="absolute left-2 top-1.5 text-[10px] font-bold text-slate-400">Rp</span>
                                                <input type="number" 
                                                       name="prices[{{ $po->id }}][{{ $detail->product_id }}]" 
                                                       min="0" 
                                                       value="{{ $existingOffer ? $existingOffer->price_per_pcs : '' }}" 
                                                       placeholder="Harga per PCS" 
                                                       class="border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-950 rounded-lg pl-6 pr-1.5 py-1 text-xs font-bold text-slate-905 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none w-full">
                                            </div>
                                            @if($existingOffer)
                                                <span class="text-[8.5px] text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-55/40 dark:bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-100/50 dark:border-emerald-900/30 font-mono whitespace-nowrap">Terisi: Rp {{ number_format($existingOffer->price_per_pcs, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-105 dark:border-slate-800 flex justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-[11px] shadow-sm hover:shadow-md active:scale-[0.98] transition-all duration-200 flex items-center space-x-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                    <span>Kirim Semua Penawaran Final</span>
                </button>
            </div>
        </form>
    @endif
</div>
