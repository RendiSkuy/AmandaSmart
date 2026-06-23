<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LPB #{{ $lpb->id }} — Laporan Penerimaan Barang</title>
    
    <!-- Google Fonts & Tailwind Play CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Theme Checker -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <!-- Tailwind Configuration -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Fira Code"', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Custom CSS for Print and Theme Override -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                background-color: white !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-150 min-h-screen py-8 px-4 transition-colors duration-300">

    <div class="max-w-3xl mx-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-xl p-8 relative overflow-hidden print-card transition-colors">
        
        <!-- Watermark decoration -->
        <div class="absolute -right-16 -top-16 w-48 h-48 bg-blue-50/50 dark:bg-blue-950/10 rounded-full blur-2xl -z-10 no-print"></div>

        <!-- Top Header buttons -->
        <div class="flex justify-between items-center mb-8 no-print border-b border-slate-100 dark:border-slate-800 pb-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
            
            <div class="flex items-center space-x-3">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="p-2 rounded-xl bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-250 dark:border-slate-800 shadow-sm text-slate-700 dark:text-slate-300 transition-colors" aria-label="Toggle Theme">
                    <!-- Sun Icon -->
                    <svg id="sun-icon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    <!-- Moon Icon -->
                    <svg id="moon-icon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Print Button -->
                <button onclick="window.print()" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-md hover:shadow-lg hover:shadow-blue-500/10 transition duration-150">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / Download PDF
                </button>
            </div>
        </div>

        <!-- Header -->
        <div class="border-b border-slate-200 dark:border-slate-800 pb-6 mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div>
                    <img src="{{ asset('logo-amandamart.png') }}" alt="AmandaMart Logo" class="h-8 w-auto">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-semibold">Distribution Center (DC) Utama</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Jl. Mengger Hilir No.123, Sukapura, Kec. Dayeuhkolot, Kabupaten Bandung, Jawa Barat 40267</p>
                </div>
                <div class="sm:text-right">
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Laporan Penerimaan Barang</h2>
                    <p class="text-xs font-extrabold text-blue-600 dark:text-blue-500 mt-1.5">Doc ID: #LPB-{{ str_pad($lpb->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5 font-medium">Tanggal Unloading: {{ $lpb->received_at->format('d M Y H:i') }} WIB</p>
                </div>
            </div>
        </div>

        <!-- Document Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 dark:bg-slate-950/40 rounded-2xl p-5 mb-6 border border-slate-100 dark:border-slate-850 text-xs transition-colors">
            <div class="space-y-1">
                <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[9px]">Penerima (Gudang DC)</p>
                <p class="font-extrabold text-slate-900 dark:text-white mt-1.5">AmandaMart Warehouse Logistics</p>
                <p class="text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Petugas: Internal MD Team</p>
            </div>
            <div class="space-y-1">
                <p class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[9px]">Supplier Pengirim</p>
                <p class="font-extrabold text-slate-900 dark:text-white mt-1.5">{{ $lpb->purchaseOrder->supplier->name ?? '-' }}</p>
                <p class="text-slate-500 dark:text-slate-400 mt-0.5 font-medium">Kode Vendor: <span class="font-mono">{{ $lpb->purchaseOrder->supplier->supplier_code ?? '-' }}</span></p>
                <p class="text-slate-500 dark:text-slate-400 font-medium">WhatsApp: <span class="font-mono">{{ $lpb->purchaseOrder->supplier->whatsapp_number ?? '-' }}</span></p>
            </div>
        </div>

        <!-- Reference -->
        <div class="mb-6 text-xs text-slate-650 dark:text-slate-400 flex flex-col sm:flex-row justify-between sm:items-center gap-3">
            <div>
                <span class="font-bold text-slate-800 dark:text-slate-350">Nomor Referensi PO:</span> <span class="font-mono font-bold text-blue-600 dark:text-blue-450 bg-blue-50 dark:bg-blue-950/40 px-2 py-0.5 rounded">{{ $lpb->purchaseOrder->po_number }}</span>
            </div>
            @if($lpb->barcode)
            <div class="sm:text-right">
                <span class="font-bold text-slate-500 dark:text-slate-400">Barcode Barang:</span> <span class="font-mono bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-750 px-2.5 py-1 rounded-lg text-xs font-bold text-slate-800 dark:text-slate-200 tracking-widest">{{ $lpb->barcode }}</span>
            </div>
            @endif
        </div>

        <!-- Table -->
        <div class="border border-slate-200 dark:border-slate-850 rounded-2xl overflow-hidden mb-8 shadow-sm">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-950/40 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-850">
                        <th class="py-3.5 px-4">PLU Code</th>
                        <th class="py-3.5 px-4">Nama Produk</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Rencana (PO)</th>
                        <th class="py-3.5 px-4 text-center">Diterima Fisik (LPB)</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Retur</th>
                        <th class="py-3.5 px-4">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                    @foreach($lpb->details as $detail)
                        @php
                            $poDetail = $lpb->purchaseOrder->details->where('product_id', $detail->product_id)->first();
                            $qtyPo = $poDetail ? $poDetail->qty_po : 0;
                            $returItem = $lpb->returs->where('product_id', $detail->product_id)->first();
                            $qtyRetur = $returItem ? $returItem->qty_retur : 0;
                            $reason = $returItem ? $returItem->reason : '-';
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/20 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-605 dark:text-slate-400">{{ $detail->product->plu_code }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $detail->product->name }}</td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-600 dark:text-slate-400">{{ $qtyPo }} PCS</td>
                            <td class="py-3.5 px-4 text-center font-black text-slate-900 dark:text-white text-sm">{{ $detail->qty_received }} PCS</td>
                            <td class="py-3.5 px-4 text-center font-bold {{ $qtyRetur > 0 ? 'text-rose-600 dark:text-rose-500' : 'text-slate-600 dark:text-slate-400' }}">
                                {{ $qtyRetur }} PCS
                            </td>
                            <td class="py-3.5 px-4 text-slate-550 dark:text-slate-400 font-medium italic">{{ $reason }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-12 mt-12 text-center text-xs pt-8 border-t border-dashed border-slate-200 dark:border-slate-800">
            <div>
                <p class="text-slate-400 dark:text-slate-500 font-semibold">Petugas Bongkar Muat DC</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-slate-800 dark:text-slate-200 underline decoration-slate-300">Internal MD Team</p>
                <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">AmandaMart Logistics</p>
            </div>
            <div>
                <p class="text-slate-400 dark:text-slate-500 font-semibold">Armada Truk Driver / Supplier</p>
                <div class="h-16"></div>
                <p class="font-extrabold text-slate-800 dark:text-slate-200 underline decoration-slate-300">...................................</p>
                <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1 font-semibold uppercase tracking-wider">Representatif Vendor</p>
            </div>
        </div>

    </div>

    <!-- Script Theme Toggle -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        updateIcons();

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcons();
        });
    </script>
</body>
</html>
