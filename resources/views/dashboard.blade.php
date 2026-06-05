<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Portal B2B — AmandaMart</title>
    
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
    
    <style>
        .animate-slideIn {
            animation: slideIn 0.2s ease-out forwards;
        }
        @keyframes slideIn {
            from { transform: translateY(5px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-955 bg-slate-50 dark:bg-[#0b1329] text-slate-800 dark:text-slate-200 min-h-screen transition-colors duration-200">
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>

    <!-- Active Tab Configuration -->
    @php
        $role = auth()->user()->role;
        if ($role === 'md') {
            $activeTab = request()->query('tab', 'products');
            if (!in_array($activeTab, ['products', 'bidding', 'vrs', 'lpb', 'ttf', 'service_level'])) {
                $activeTab = 'products';
            }
            // Count badges
            $criticalCount = $products->filter(fn($p) => $p->on_hand < ($p->max_stock / 2))->count();
            $pendingBiddingCount = $purchaseOrders->where('status', 'PENDING_BIDDING')->count();
            $pendingVrsCount = $vrsSchedules->where('status', 'pending')->count();
            $pendingLpbCount = $purchaseOrders->where('status', 'APPROVED')->filter(fn($po) => !$po->goodsReceipt)->count();
            $pendingTtfCount = $goodsReceipts->filter(fn($gr) => !$gr->ttf)->count();
        } else {
            $activeTab = request()->query('tab', 'dashboard');
            if (!in_array($activeTab, ['dashboard', 'bidding', 'vrs', 'lpb', 'ttf', 'profile'])) {
                $activeTab = 'dashboard';
            }
            // Count badges
            $pendingBidsCount = $purchaseOrders->where('status', 'PENDING_BIDDING')->filter(fn($po) => !isset($myOffers[$po->id]))->count();
            $pendingVrsCount = $purchaseOrders->where('status', 'APPROVED')->filter(fn($po) => !$vrsSchedules->contains('purchase_order_id', $po->id))->count();
            $pendingTtfCount = $goodsReceipts->filter(fn($gr) => !$gr->ttf)->count();
        }
    @endphp

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Mobile Header Bar -->
        <header class="md:hidden bg-white dark:bg-[#111c44] border-b border-slate-200 dark:border-slate-800 px-4 py-2.5 flex justify-between items-center z-40 sticky top-0">
            <div class="flex items-center gap-2">
                <span class="text-lg font-black tracking-tight {{ $role === 'md' ? 'text-blue-600 dark:text-blue-500' : 'text-emerald-600 dark:text-emerald-500' }}">
                    AMANDA<span class="text-slate-800 dark:text-white font-medium">mart</span>
                </span>
            </div>
            <button id="mobile-sidebar-toggle" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300" aria-label="Menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </header>

        <!-- Left Sidebar Navigation -->
        <aside id="sidebar-nav" class="fixed inset-y-0 left-0 z-40 w-56 bg-white dark:bg-[#111c44] border-r border-slate-200 dark:border-slate-800 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out no-print md:sticky md:top-0 md:h-screen">
            <!-- Sidebar Header / Brand -->
            <div class="py-4 px-5 border-b border-slate-100 dark:border-slate-800/60 flex justify-between items-center">
                <div>
                    <span class="text-lg font-black tracking-tight {{ $role === 'md' ? 'text-blue-600' : 'text-emerald-600' }}">
                        AMANDA<span class="text-slate-800 dark:text-white font-medium">mart</span>
                    </span>
                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        {{ $role === 'md' ? 'Merchandiser Portal' : 'Supplier B2B Portal' }}
                    </span>
                </div>
                <button id="mobile-sidebar-close" class="md:hidden p-1 text-slate-450 hover:text-slate-600" aria-label="Close">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Profile Info Bubble Area -->
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800/60 bg-slate-50/50 dark:bg-slate-950/10">
                <div class="flex items-center gap-3">
                    <!-- Initial Bubble -->
                    <div class="h-9 w-9 rounded-lg flex-shrink-0 flex items-center justify-center font-extrabold text-[11px] text-white uppercase shadow-sm transition-transform hover:scale-105 duration-200
                        {{ $role === 'md' ? 'bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-blue-500/10 border border-blue-400' : 'bg-gradient-to-tr from-emerald-600 to-teal-500 shadow-emerald-500/10 border border-emerald-400' }}">
                        {{ substr(auth()->user()->username, 0, 2) }}
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->username }}</h4>
                        @if($role === 'supplier' && isset($supplier))
                            <p class="text-[9px] text-slate-450 dark:text-slate-400 truncate font-semibold mt-0.5">{{ $supplier->name }}</p>
                        @else
                            <p class="text-[9px] text-slate-450 dark:text-slate-400 truncate font-semibold mt-0.5">Admin Merchandiser</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scrollable Navigation Items -->
            <div class="flex-grow py-4 px-3 space-y-5 overflow-y-auto">
                
                @if($role === 'md')
                    <!-- ================= MD SIDEBAR MENU ================= -->
                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">UTAMA</span>
                        <a href="?tab=products" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'products' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">📦 Master Produk</span>
                            @if($criticalCount > 0)
                                <span class="bg-rose-500 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full animate-pulse">{{ $criticalCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">TRANSAKSI</span>
                        <a href="?tab=bidding" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'bidding' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🤝 Bidding & Approval</span>
                            @if($pendingBiddingCount > 0)
                                <span class="bg-blue-600 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingBiddingCount }}</span>
                            @endif
                        </a>
                        <a href="?tab=vrs" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'vrs' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🚛 Logistik VRS</span>
                            @if($pendingVrsCount > 0)
                                <span class="bg-amber-500 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingVrsCount }}</span>
                            @endif
                        </a>
                        <a href="?tab=lpb" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'lpb' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">📥 LPB Penerimaan</span>
                            @if($pendingLpbCount > 0)
                                <span class="bg-indigo-600 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingLpbCount }}</span>
                            @endif
                        </a>
                        <a href="?tab=ttf" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'ttf' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🧾 TTF / Faktur</span>
                            @if($pendingTtfCount > 0)
                                <span class="bg-emerald-600 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingTtfCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">ANALITIK</span>
                        <a href="?tab=service_level" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'service_level' ? 'bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 border-l-4 border-blue-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🏆 Rapor Service Level</span>
                        </a>
                    </div>

                @else
                    <!-- ================= SUPPLIER SIDEBAR MENU ================= -->
                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">UTAMA</span>
                        <a href="?tab=dashboard" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'dashboard' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">📊 Ringkasan Dasbor</span>
                        </a>
                    </div>

                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">TRANSAKSI</span>
                        <a href="?tab=bidding" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'bidding' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🤝 Bidding Penawaran</span>
                            @if($pendingBidsCount > 0)
                                <span class="bg-rose-500 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingBidsCount }}</span>
                            @endif
                        </a>
                        <a href="?tab=vrs" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'vrs' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🚛 VRS Logistik</span>
                            @if($pendingVrsCount > 0)
                                <span class="bg-amber-500 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingVrsCount }}</span>
                            @endif
                        </a>
                        <a href="?tab=lpb" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'lpb' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">📥 LPB Penerimaan</span>
                        </a>
                        <a href="?tab=ttf" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'ttf' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">🧾 TTF / Faktur</span>
                            @if($pendingTtfCount > 0)
                                <span class="bg-indigo-600 text-white text-[8px] font-extrabold px-1.5 py-0.5 rounded-full">{{ $pendingTtfCount }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="space-y-1.5">
                        <span class="px-2.5 text-[9px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block">AKUN</span>
                        <a href="?tab=profile" class="group flex items-center justify-between px-2.5 py-2 rounded-lg text-[11px] font-bold transition-all duration-150
                            {{ $activeTab === 'profile' ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border-l-4 border-emerald-600' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/40 hover:text-slate-900 dark:hover:text-white border-l-4 border-transparent' }}">
                            <span class="flex items-center gap-2">👤 Profil & Akun</span>
                        </a>
                    </div>
                @endif

            </div>

            <!-- Theme Toggle and Logout in Sidebar Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center gap-2">
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="p-2 rounded-lg bg-slate-50 dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 transition-colors" aria-label="Toggle Theme">
                    <!-- Sun Icon -->
                    <svg id="sun-icon" class="h-3.5 w-3.5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                    </svg>
                    <!-- Moon Icon -->
                    <svg id="moon-icon" class="h-3.5 w-3.5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="inline flex-grow">
                    @csrf
                    <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-bold py-2 px-3 rounded-lg text-[10px] flex items-center justify-center gap-1 transition-colors border border-rose-100 dark:border-rose-900/30">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Right Content Pane Container -->
        <main class="flex-grow min-w-0 flex flex-col p-4 sm:p-5 lg:p-6">
            <!-- Flash Session Alerts -->
            @if (session('success'))
                <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-950/25 border border-emerald-250 dark:border-emerald-900/40 rounded-xl text-emerald-800 dark:text-emerald-400 shadow-sm flex items-center space-x-2.5 transition-colors animate-slideIn">
                    <svg class="h-4 w-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-bold text-[11px] sm:text-xs">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-rose-50 dark:bg-rose-955/25 border border-rose-250 dark:border-rose-900/40 rounded-xl text-rose-800 dark:text-rose-400 shadow-sm flex items-center space-x-2.5 transition-colors animate-slideIn">
                    <svg class="h-4 w-4 text-rose-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-bold text-[11px] sm:text-xs">{{ session('error') }}</span>
                </div>
            @endif

            @if($role === 'md')
                <!-- ======================================================== -->
                <!-- ================== TIM MERCHANDISER VIEW ================== -->
                <!-- ======================================================== -->

                <!-- Welcome Banner -->
                <div class="bg-gradient-to-r from-blue-50/40 to-white dark:from-slate-900/30 dark:to-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm mb-6 transition-all">
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                        Selamat datang kembali, <span class="text-blue-600 dark:text-blue-450 font-black">{{ auth()->user()->username }}</span>!
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-1.5 text-xs max-w-3xl leading-relaxed">
                        Kelola efisiensi rantai pasok logistik AmandaMart: deteksi ketersediaan stok produk kritis, proses bidding penawaran lelang supplier, pantau reservasi antrean bongkar muat VRS, serta verifikasi faktur keuangan TTF.
                    </p>
                </div>

                <!-- Tab 1: Products -->
                @if($activeTab === 'products')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-300 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 1: Pemeriksaan Stok & Restock Otomatis (PB)</span>
                            <p class="mt-0.5 leading-relaxed">Merchandiser (MD) memantau ketersediaan barang di Distribution Center (DC). Apabila kuantitas berada di bawah level safety stock/minor, MD dapat memicu pembuatan draf PO baru secara otomatis melalui tombol pembuat draf PO.</p>
                        </div>
                    </div>
                    
                    <!-- Grid Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Product Stock List -->
                        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">📦 Master Produk & Stok Gudang DC</h2>
                                <span class="text-[9px] bg-slate-100 dark:bg-slate-950 px-2 py-0.5 rounded text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Single DC Gudang</span>
                            </div>

                            @php
                                $groupedProducts = $products->groupBy(function($prod) use ($suppliers) {
                                    $parts = explode('-', $prod->plu_code);
                                    if (count($parts) >= 2) {
                                        $prefix = $parts[0] . '-' . $parts[1]; // SUP-001
                                        $sup = $suppliers->firstWhere('supplier_code', $prefix);
                                        return $sup ? $sup->id : 0;
                                    }
                                    return 0;
                                });
                            @endphp

                            <div class="space-y-3">
                                @foreach($groupedProducts as $supplierId => $prods)
                                    @php
                                        $supplier = $suppliers->firstWhere('id', $supplierId);
                                        $supplierName = $supplier ? $supplier->name : 'Produk Umum / Tidak Diketahui';
                                        $supplierCode = $supplier ? $supplier->supplier_code : '-';
                                        $criticalCountInGroup = $prods->filter(fn($p) => $p->on_hand < ($p->max_stock / 2))->count();
                                    @endphp
                                    <div class="border border-slate-200 dark:border-slate-800/80 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                                        <!-- Accordion Header Kategori Supplier -->
                                        <button type="button" 
                                                onclick="toggleProductSupplier('prod-sup-{{ $supplierId }}')"
                                                class="w-full text-left px-4 py-3 bg-slate-50 dark:bg-slate-950/60 hover:bg-slate-100 dark:hover:bg-slate-950 transition flex justify-between items-center border-b border-slate-200 dark:border-slate-850">
                                            <div class="flex items-center space-x-2">
                                                <div class="h-2 w-2 rounded-full {{ $criticalCountInGroup > 0 ? 'bg-rose-500 animate-pulse' : 'bg-slate-400' }}"></div>
                                                <span class="text-xs font-bold text-slate-850 dark:text-slate-200">{{ $supplierName }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">({{ $supplierCode }})</span>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($criticalCountInGroup > 0)
                                                    <span class="text-[8px] bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-450 px-2 py-0.5 rounded font-bold uppercase tracking-wide">
                                                        {{ $criticalCountInGroup }} Kritis / Butuh PO
                                                    </span>
                                                @else
                                                    <span class="text-[8px] bg-slate-105 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded font-bold uppercase tracking-wide">
                                                        Aman
                                                    </span>
                                                @endif
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- Accordion Content -->
                                        <div id="prod-sup-{{ $supplierId }}" class="p-3 overflow-x-auto">
                                            <table class="w-full text-left border-collapse text-[11px]">
                                                <thead>
                                                    <tr class="bg-slate-50 dark:bg-slate-950/40 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                                                        <th class="py-2.5 px-3">PLU Code</th>
                                                        <th class="py-2.5 px-3">Nama Produk</th>
                                                        <th class="py-2.5 px-3 text-center">Safety / Min Stock</th>
                                                        <th class="py-2.5 px-3 text-center">Max Stock</th>
                                                        <th class="py-2.5 px-3 text-center">Stok On Hand</th>
                                                        <th class="py-2.5 px-3 text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-105 dark:divide-slate-800">
                                                    @foreach($prods as $prod)
                                                    @php
                                                        $isCritical = $prod->on_hand < ($prod->max_stock / 2);
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition product-row" data-critical="{{ $isCritical ? 'true' : 'false' }}">
                                                        <td class="py-2 px-3 font-mono font-semibold text-slate-550 dark:text-slate-400">{{ $prod->plu_code }}</td>
                                                        <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-200">{{ $prod->name }}</td>
                                                        <td class="py-2 px-3 text-center font-medium">{{ $prod->minor }} PCS</td>
                                                        <td class="py-2 px-3 text-center font-medium">{{ $prod->max_stock }} PCS</td>
                                                        <td class="py-2 px-3 text-center font-bold {{ $isCritical ? 'text-rose-600 dark:text-rose-500' : 'text-slate-700 dark:text-slate-350' }}">{{ $prod->on_hand }} PCS</td>
                                                        <td class="py-2 px-3 text-center status-cell">
                                                            @if($isCritical)
                                                                <span class="px-2 py-0.5 text-[9px] font-bold bg-rose-50 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/40 rounded-full uppercase">Kritis / Butuh PO</span>
                                                            @else
                                                                <span class="px-2 py-0.5 text-[9px] font-semibold bg-slate-50 dark:bg-slate-800 text-slate-650 dark:text-slate-400 border border-slate-100 dark:border-slate-700 rounded-full uppercase">Aman</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Stage 1 Restock Trigger Panel -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between h-fit gap-5">
                            <div class="space-y-3">
                                <h2 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">⚡</span>
                                    Stage 1: Pemicu Restock
                                </h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Sistem akan secara cerdas memindai data produk yang memiliki kuantitas kritis (di bawah safety stock) untuk kemudian menghasilkan draf Purchase Order (PO) baru berstatus <strong class="text-amber-600 dark:text-amber-500 font-semibold">PENDING_BIDDING</strong> melalui Stored Procedure database.
                                </p>
                                <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 rounded-xl text-[11px] text-blue-800 dark:text-blue-400 leading-relaxed">
                                    <strong class="font-bold text-blue-900 dark:text-blue-300 block mb-0.5">Kebutuhan Otomatis Gudang:</strong>
                                    Kuantitas pemesanan dikunci mutlak berdasarkan kebutuhan stok gudang DC. Vendor rekanan hanya diperkenankan mengajukan penawaran harga modal terbaik.
                                </div>
                            </div>
                            <form action="{{ route('dashboard.po.generate') }}" method="POST" id="generate-po-form">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm hover:shadow-md active:scale-[0.98] transition-all duration-150 flex items-center justify-center space-x-2 text-[11px]">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>Generate PO Otomatis</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Tab 2: Bidding Approval -->
                @if($activeTab === 'bidding')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 2 & 3: Proses Bidding & Pemilihan Pemenang</span>
                            <p class="mt-0.5 leading-relaxed">MD meninjau serta membandingkan penawaran harga modal masuk dari berbagai akun sales supplier rekanan. Tekan tombol 'Setujui' pada baris sales terpilih untuk menyetujui pemenang PO secara dinamis menggunakan teknologi AJAX tanpa reload halaman.</p>
                        </div>
                    </div>

                    <!-- Stage 2 & 3 Body -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">🤝</span>
                            Stage 2 & 3: Proses Bidding & Pemilihan Pemenang (MD Approval)
                        </h2>
                        
                        @php
                            $pendingPos = $purchaseOrders->where('status', 'PENDING_BIDDING');
                            $pendingPoIds = $pendingPos->pluck('id');
                        @endphp

                        @if($pendingPos->isEmpty())
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 italic text-center py-8 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada draf Purchase Order (PO) berstatus PENDING_BIDDING saat ini.</p>
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
                                                    class="w-full text-left px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/40 hover:bg-slate-100 dark:hover:bg-slate-955 transition flex justify-between items-center border-b border-slate-100 dark:border-slate-800">
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
                                                        <span class="text-[9px] bg-slate-105 dark:bg-slate-800 text-slate-500 dark:text-slate-450 px-1 py-0.5 rounded font-mono">{{ $salesOfferCount }} Barang</span>
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
                                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-3 font-mono">Detail Penawaran:</h4>
                                    
                                    <!-- Placeholder -->
                                    <div class="bg-slate-50 dark:bg-slate-950/30 border border-slate-200 dark:border-slate-850 border-dashed rounded-2xl p-6 min-h-[220px] flex flex-col justify-center items-center text-center shadow-inner"
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
                                        @endphp
                                        <div class="bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-900/60 rounded-2xl p-4 shadow-sm hidden space-y-4"
                                             id="offer-detail-sales-{{ $salesUser->id }}">
                                            
                                            <!-- Header Informasi Sales -->
                                            <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-3">
                                                <div>
                                                    <h5 class="text-[8px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Perusahaan Rekanan</h5>
                                                    <p class="text-xs font-extrabold text-slate-850 dark:text-slate-200">{{ $sup->name }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <span class="text-[10px] font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/40 px-2 py-1 rounded-lg border border-blue-100/50 dark:border-blue-900/30">Akun Sales: {{ $salesUser->username }}</span>
                                                </div>
                                            </div>

                                            <!-- List Semua Barang yang Ditawar -->
                                            <div class="space-y-3">
                                                <h4 class="text-[10px] font-bold text-slate-550 dark:text-slate-400 uppercase tracking-widest font-mono">Daftar Penawaran Harga Modal:</h4>
                                                
                                                @foreach($salesOffers as $off)
                                                @php
                                                    $poItem = $pendingPos->where('id', $off->purchase_order_id)->first();
                                                @endphp
                                                @if($poItem)
                                                <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-950/20 hover:bg-white dark:hover:bg-slate-900 hover:shadow-sm transition-all duration-150 space-y-3 po-card" id="po-card-{{ $poItem->id }}">
                                                    <div class="flex justify-between items-start flex-wrap gap-2">
                                                        <div>
                                                            <span class="px-1.5 py-0.5 text-[8px] font-bold bg-amber-100 dark:bg-amber-955 text-amber-800 dark:text-amber-400 border border-amber-200 rounded">PO: {{ $poItem->po_number }}</span>
                                                            <h5 class="text-xs font-bold text-slate-900 dark:text-white mt-1.5">{{ $poItem->product->name }}</h5>
                                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Kuantitas PO Ritel: <strong>{{ $poItem->qty_po }} PCS</strong></p>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-[9px] text-slate-400 dark:text-slate-500 font-bold block uppercase tracking-wider">Harga Modal</span>
                                                            <span class="text-sm font-black text-blue-600 dark:text-blue-450 mt-0.5 block">Rp {{ number_format($off->price_per_pcs, 0, ',', '.') }} <span class="text-[10px] font-normal text-slate-450">/ PCS</span></span>
                                                        </div>
                                                    </div>

                                                    <div class="flex flex-wrap justify-between items-center gap-2.5 pt-2.5 border-t border-slate-200/50 dark:border-slate-805">
                                                        <div>
                                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium font-mono">Total Harga Kotor: <strong class="text-slate-800 dark:text-slate-200">Rp {{ number_format($off->price_per_pcs * $poItem->qty_po, 0, ',', '.') }}</strong></span>
                                                        </div>
                                                        
                                                        <!-- Form Approve -->
                                                        <form action="{{ route('dashboard.offers.approve') }}" method="POST" class="ajax-approve-form flex flex-wrap items-center gap-1.5" data-po-id="{{ $poItem->id }}" data-sales-id="{{ $salesUser->id }}">
                                                            @csrf
                                                            <input type="hidden" name="purchase_order_id" value="{{ $poItem->id }}">
                                                            <input type="hidden" name="offer_id" value="{{ $off->id }}">
                                                            
                                                            <div class="flex items-center space-x-1">
                                                                <label class="text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase whitespace-nowrap">Deadline:</label>
                                                                <input type="date" name="delivery_deadline" required value="{{ now()->addDays(7)->format('Y-m-d') }}"
                                                                       class="text-[11px] border border-slate-350 dark:border-slate-800 rounded-md px-2 py-0.5 focus:ring-1 focus:ring-blue-500 focus:outline-none bg-white dark:bg-slate-950 text-slate-850 dark:text-white font-semibold">
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
                @endif

                <!-- Tab 3: VRS monitor -->
                @if($activeTab === 'vrs')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 4: Pemantauan Logistik & Antrean Truk (VRS)</span>
                            <p class="mt-0.5 leading-relaxed">Tim Merchandiser (MD) mengawasi status slot dan jadwal bongkar muat armada pengiriman logistik supplier rekanan guna menjamin kelancaran unloading di Distribution Center (DC) utama.</p>
                        </div>
                    </div>

                    <!-- VRS Monitor Section -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">🚛</span>
                            Stage 4: Logistik & Reservasi Kedatangan Truk (VRS Monitor)
                        </h2>
                        
                        @if($vrsSchedules->isEmpty())
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 italic text-center py-8 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Belum ada booking jadwal truk logistik yang terdaftar saat ini.</p>
                        @else
                            @php
                                $groupedVrsSchedules = $vrsSchedules->groupBy(function($sched) {
                                    return $sched->purchaseOrder->selected_supplier_id ?? 0;
                                });
                            @endphp
                            <div class="space-y-3">
                                @foreach($groupedVrsSchedules as $supplierId => $schedules)
                                    @php
                                        $firstPo = $schedules->first()->purchaseOrder;
                                        $supplier = $firstPo ? $firstPo->supplier : null;
                                        $supplierName = $supplier ? $supplier->name : 'Supplier Tidak Diketahui';
                                        $supplierCode = $supplier ? $supplier->supplier_code : '-';
                                        
                                        $groupSalesUsernames = $schedules->map(function($sched) {
                                            $acceptedOffer = $sched->purchaseOrder ? $sched->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                            return $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : null;
                                        })->filter()->unique()->implode(', ');
                                    @endphp
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                                        <!-- Accordion Header Kategori Supplier -->
                                        <button type="button" 
                                                onclick="toggleVrsSupplier('vrs-sup-{{ $supplierId }}')"
                                                class="w-full text-left px-4 py-3 bg-slate-50 dark:bg-slate-950/60 hover:bg-slate-100 dark:hover:bg-slate-950 transition flex justify-between items-center border-b border-slate-200 dark:border-slate-850">
                                            <div class="flex items-center space-x-2 flex-wrap gap-1.5">
                                                <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $supplierName }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">({{ $supplierCode }})</span>
                                                @if($groupSalesUsernames)
                                                    <span class="text-[9px] text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-955/40 px-1.5 py-0.5 rounded font-mono border border-blue-100 dark:border-blue-900/30">Sales: {{ $groupSalesUsernames }}</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-[8px] bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-405 px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                                                    {{ $schedules->count() }} Jadwal Truk
                                                </span>
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- Accordion Content -->
                                        <div id="vrs-sup-{{ $supplierId }}" class="p-3 overflow-x-auto">
                                            <table class="w-full text-left border-collapse text-[11px]">
                                                <thead>
                                                    <tr class="bg-slate-50 dark:bg-slate-950/40 text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                                                        <th class="py-2 px-3">PO Number</th>
                                                        <th class="py-2 px-3">Nama Akun Sales</th>
                                                        <th class="py-2 px-3">Tanggal Booking</th>
                                                        <th class="py-2 px-3">Slot Waktu</th>
                                                        <th class="py-2 px-3">Waktu Tiba Riil</th>
                                                        <th class="py-2 px-3">Status Antrean</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-105 dark:divide-slate-800">
                                                    @foreach($schedules as $sched)
                                                    @php
                                                        $acceptedOffer = $sched->purchaseOrder ? $sched->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                                        $salesUsername = $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : '-';
                                                    @endphp
                                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/30 transition">
                                                        <td class="py-2 px-3 font-mono font-bold text-blue-650 dark:text-blue-400">PO: {{ $sched->purchaseOrder->po_number }}</td>
                                                        <td class="py-2 px-3 font-semibold text-slate-700 dark:text-slate-350">👤 Sales: {{ $salesUsername }}</td>
                                                        <td class="py-2 px-3 font-medium">{{ $sched->scheduled_date }}</td>
                                                        <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-200">Jadwal Truk: {{ $sched->time_slot }}</td>
                                                        <td class="py-2 px-3 font-mono font-medium">
                                                            {{ $sched->actual_arrival_at ? $sched->actual_arrival_at->format('Y-m-d H:i:s') : '-' }}
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            @if($sched->status === 'completed')
                                                                <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40 rounded-full uppercase">Selesai Bongkar</span>
                                                            @else
                                                                <span class="px-2 py-0.5 text-[9px] font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/45 rounded-full uppercase">Menunggu Kedatangan</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tab 4: LPB Receiving -->
                @if($activeTab === 'lpb')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 5: Pencatatan LPB Gudang</span>
                            <p class="mt-0.5 leading-relaxed">Catat kuantitas fisik produk tiba beserta jumlah retur saat truk melakukan bongkar muat di Distribution Center. Informasi penerimaan bersih ini secara otomatis memperbarui persediaan DC utama dan menyusun statistik kepatuhan pengiriman.</p>
                        </div>
                    </div>

                    <!-- LPB Form Panel -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">📥</span>
                            Stage 5: Input Fisik Barang Tiba & Retur Gudang (LPB)
                        </h2>
                        
                        @php
                            $approvedPos = $purchaseOrders->where('status', 'APPROVED')->filter(fn($po) => !$po->goodsReceipt);
                        @endphp

                        @if($approvedPos->isEmpty())
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 italic text-center py-8 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada Purchase Order berstatus APPROVED yang siap dibongkar di Gudang saat ini.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($approvedPos->groupBy('selected_supplier_id') as $supplierId => $pos)
                                    @php
                                        $supplier = $pos->first()->supplier;
                                        $supplierName = $supplier ? $supplier->name : 'Supplier Tidak Diketahui';
                                        $supplierCode = $supplier ? $supplier->supplier_code : '-';
                                        
                                        $groupSalesUsernames = $pos->map(function($po) {
                                            $acceptedOffer = $po->offers->firstWhere('status', 'accepted');
                                            return $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : null;
                                        })->filter()->unique()->implode(', ');
                                    @endphp
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                                        <!-- Accordion Header Kategori Supplier -->
                                        <button type="button" 
                                                onclick="toggleLpbSupplier('lpb-sup-{{ $supplierId }}')"
                                                class="w-full text-left px-4 py-3 bg-slate-50 dark:bg-slate-950/60 hover:bg-slate-105 dark:hover:bg-slate-955 transition flex justify-between items-center border-b border-slate-200 dark:border-slate-850">
                                            <div class="flex items-center space-x-2 flex-wrap gap-1.5">
                                                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $supplierName }}</span>
                                                <span class="text-[10px] text-slate-400 font-mono">({{ $supplierCode }})</span>
                                                @if($groupSalesUsernames)
                                                    <span class="text-[9px] text-emerald-700 dark:text-emerald-450 bg-emerald-50 dark:bg-emerald-950/30 px-2 py-0.5 rounded font-mono border border-emerald-100 dark:border-emerald-900/30">Supplier: {{ $groupSalesUsernames }}</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-[8px] bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-400 px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                                                    {{ $pos->count() }} PO Siap Bongkar
                                                </span>
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- Accordion Content -->
                                        <div id="lpb-sup-{{ $supplierId }}" class="p-3 space-y-3">
                                            @foreach($pos as $po)
                                            <div class="p-3 border border-slate-200 dark:border-slate-850 rounded-xl bg-slate-50/50 dark:bg-slate-950/20 lpb-card" id="lpb-card-{{ $po->id }}">
                                                <div class="mb-3">
                                                    <div class="flex justify-between items-start flex-wrap gap-2">
                                                        <div>
                                                            <h3 class="font-bold text-xs text-slate-900 dark:text-white">Faktur PO: <span class="font-mono text-blue-600 dark:text-blue-450">{{ $po->po_number }}</span></h3>
                                                            @php
                                                                $acceptedOffer = $po->offers->firstWhere('status', 'accepted');
                                                                $salesUsername = $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : '-';
                                                            @endphp
                                                            <p class="text-[10px] text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                                                Nama Supplier: <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ $supplierName }}</strong> | 
                                                                Kode Supplier: <strong class="text-slate-800 dark:text-slate-200 font-mono font-semibold">{{ $supplierCode }}</strong> |
                                                                Akun Supplier: <strong class="text-blue-700 dark:text-blue-400 font-bold">Akun Supplier: {{ $salesUsername }}</strong>
                                                            </p>
                                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Produk: <span class="font-semibold text-slate-700 dark:text-slate-350">{{ $po->product->name }}</span> | Diminta Ritel: <strong>{{ $po->qty_po }} PCS</strong></p>
                                                        </div>
                                                        <span class="px-2 py-0.5 text-[8px] font-bold bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/35 rounded uppercase">PO Siap Bongkar</span>
                                                    </div>
                                                </div>
                                                <form action="{{ route('dashboard.lpb.store') }}" method="POST" class="ajax-lpb-form space-y-3 bg-white dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm" data-po-id="{{ $po->id }}" data-supplier-id="{{ $supplierId }}">
                                                    @csrf
                                                    <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                                                    
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                        <div>
                                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wide mb-1">Barcode Barang (Otomatis)</label>
                                                            <input type="text" name="barcode" readonly value="{{ strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $po->product->name)) }}" class="w-full border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-lg py-1.5 px-2.5 text-[10px] font-mono font-bold text-slate-700 dark:text-slate-300 tracking-widest outline-none">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wide mb-1">Waktu Tiba (Diisi Admin)</label>
                                                            <input type="datetime-local" name="received_at" required value="{{ now()->format('Y-m-d\TH:i') }}" class="w-full border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg py-1 px-2 text-[10px] text-slate-700 dark:text-slate-300 font-bold focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wide mb-1">Jumlah Fisik Tiba (LPB)</label>
                                                            <input type="number" name="qty_received" required min="0" placeholder="Contoh: 1000" class="w-full border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                        <div>
                                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wide mb-1">Jumlah Rusak (Retur)</label>
                                                            <input type="number" name="qty_retur" min="0" placeholder="Kosongkan jika nihil" class="w-full border border-slate-350 dark:border-slate-850 bg-white dark:bg-slate-955 rounded-lg py-1 px-2 text-[10px] font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-450 uppercase tracking-wide mb-1">Alasan Retur (Wajib jika ada rusak)</label>
                                                            <div class="flex space-x-2">
                                                                <input type="text" name="reason" placeholder="Contoh: Dus pembungkus pecah / sobek" class="flex-grow border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg py-1 px-2.5 text-[10px] text-slate-850 dark:text-slate-200 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3.5 rounded-lg text-[10px] shadow-sm transition duration-150 flex-shrink-0">
                                                                    Simpan LPB
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tab 5: TTF Invoice -->
                @if($activeTab === 'ttf')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">💡 Petunjuk Tahap 5: Tanda Terima Faktur & Nota Keuangan Pajak (TTF)</span>
                            <p class="mt-0.5 leading-relaxed">Terbitkan dokumen resmi Tanda Terima Faktur (TTF) atas tagihan keuangan supplier berdasarkan kuantitas LPB bersih (setelah dikurangi barang retur/rusak) guna memicu proses pembayaran transfer.</p>
                        </div>
                    </div>

                    <!-- Stage 5 Invoice Section -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-7 w-7 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">🧾</span>
                            Stage 5: Tanda Terima Faktur & Nota Keuangan Pajak (TTF)
                        </h2>
                        
                        @php
                            $lpbsWithoutTtf = $goodsReceipts->filter(fn($gr) => !$gr->ttf);
                            $ttfs = \App\Models\Ttf::with(['goodsReceipt.purchaseOrder.product', 'goodsReceipt.purchaseOrder.supplier'])->latest()->get();
                        @endphp

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- LPB Awaiting TTF -->
                            <div class="space-y-3">
                                <h3 class="text-[10px] font-bold text-slate-405 dark:text-slate-500 uppercase tracking-widest font-mono">LPB Menunggu Invoice (TTF)</h3>
                                @if($lpbsWithoutTtf->isEmpty())
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic py-6 bg-slate-50 dark:bg-slate-955/40 border border-slate-200/60 dark:border-slate-800/60 rounded-xl text-center">Semua penerimaan barang (LPB) telah diterbitkan dokumen TTF-nya.</p>
                                @else
                                    @php
                                        $groupedLpbsWithoutTtf = $lpbsWithoutTtf->groupBy(function($gr) {
                                            return $gr->purchaseOrder->selected_supplier_id ?? 0;
                                        });
                                    @endphp
                                    <div class="space-y-2.5">
                                        @foreach($groupedLpbsWithoutTtf as $supplierId => $lpbs)
                                            @php
                                                $firstPo = $lpbs->first()->purchaseOrder;
                                                $supplier = $firstPo ? $firstPo->supplier : null;
                                                $supplierName = $supplier ? $supplier->name : 'Supplier Tidak Diketahui';
                                                $supplierCode = $supplier ? $supplier->supplier_code : '-';
                                                
                                                $groupSalesUsernames = $lpbs->map(function($gr) {
                                                    $acceptedOffer = $gr->purchaseOrder ? $gr->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                                    return $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : null;
                                                })->filter()->unique()->implode(', ');
                                            @endphp
                                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                                                <!-- Accordion Header -->
                                                <button type="button" 
                                                        onclick="toggleTtfLpbSupplier('ttf-lpb-sup-{{ $supplierId }}')"
                                                        class="w-full text-left px-3.5 py-2.5 bg-slate-50 dark:bg-slate-955/40 hover:bg-slate-100 dark:hover:bg-slate-950 transition flex justify-between items-center border-b border-slate-100 dark:border-slate-850">
                                                    <div class="flex flex-col">
                                                        <span class="text-xs font-bold text-slate-805 dark:text-slate-200">{{ $supplierName }} ({{ $supplierCode }})</span>
                                                        @if($groupSalesUsernames)
                                                            <span class="text-[9px] text-indigo-650 dark:text-indigo-405 font-mono mt-0.5">Sales: {{ $groupSalesUsernames }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[9px] bg-slate-150 dark:bg-slate-800 text-slate-705 dark:text-slate-400 px-2 py-0.5 rounded font-bold">
                                                        {{ $lpbs->count() }} LPB
                                                    </span>
                                                </button>

                                                <!-- Accordion Content -->
                                                <div id="ttf-lpb-sup-{{ $supplierId }}" class="p-2.5 space-y-2.5">
                                                    @foreach($lpbs as $lpb)
                                                    @php
                                                        $acceptedOffer = $lpb->purchaseOrder ? $lpb->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                                        $salesUsername = $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : '-';
                                                    @endphp
                                                    <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900/40 shadow-sm flex justify-between items-center ttf-lpb-row" id="ttf-lpb-row-{{ $lpb->id }}">
                                                        <div class="space-y-0.5">
                                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">PO: {{ $lpb->purchaseOrder->po_number }}</p>
                                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Tiba Fisik: {{ $lpb->qty_received }} PCS | Retur Rusak: {{ $lpb->retur ? $lpb->retur->qty_retur : 0 }} PCS</p>
                                                            <p class="text-[10px] text-slate-650 dark:text-slate-400 font-semibold">Akun Sales: <strong class="text-indigo-600 dark:text-indigo-400">👤 {{ $salesUsername }}</strong></p>
                                                            <a href="{{ route('dashboard.lpb.print', $lpb->id) }}" target="_blank" class="inline-block text-[9px] font-bold text-blue-600 dark:text-blue-450 hover:underline mt-0.5">
                                                                Detail & Cetak LPB
                                                            </a>
                                                        </div>
                                                        <form action="{{ route('dashboard.ttf.generate') }}" method="POST" class="ajax-ttf-form" data-lpb-id="{{ $lpb->id }}" data-supplier-id="{{ $supplierId }}">
                                                            @csrf
                                                            <input type="hidden" name="goods_receipt_id" value="{{ $lpb->id }}">
                                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded-lg text-[10px] shadow-sm transition duration-150">
                                                                Generate TTF
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Existing TTFs -->
                            <div class="space-y-3">
                                <h3 class="text-[10px] font-bold text-slate-405 dark:text-slate-500 uppercase tracking-widest font-mono">Daftar TTF Terbit</h3>
                                @if($ttfs->isEmpty())
                                    <p class="text-xs text-slate-500 dark:text-slate-400 italic py-6 bg-slate-50 dark:bg-slate-955/40 border border-slate-200/60 dark:border-slate-800/60 rounded-xl text-center">Belum ada dokumen Tanda Terima Faktur (TTF) terbit.</p>
                                @else
                                    @php
                                        $groupedTtfs = $ttfs->groupBy(function($t) {
                                            return $t->goodsReceipt->purchaseOrder->selected_supplier_id ?? 0;
                                        });
                                    @endphp
                                    <div class="space-y-2.5">
                                        @foreach($groupedTtfs as $supplierId => $ttfItems)
                                            @php
                                                $firstPo = $ttfItems->first()->goodsReceipt->purchaseOrder;
                                                $supplier = $firstPo ? $firstPo->supplier : null;
                                                $supplierName = $supplier ? $supplier->name : 'Supplier Tidak Diketahui';
                                                $supplierCode = $supplier ? $supplier->supplier_code : '-';
                                                
                                                $groupSalesUsernames = $ttfItems->map(function($t) {
                                                    $acceptedOffer = $t->goodsReceipt && $t->goodsReceipt->purchaseOrder ? $t->goodsReceipt->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                                    return $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : null;
                                                })->filter()->unique()->implode(', ');
                                            @endphp
                                            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm" id="ttf-sup-{{ $supplierId }}">
                                                <!-- Accordion Header -->
                                                <button type="button" 
                                                        onclick="toggleTtfSupplier('ttf-sup-{{ $supplierId }}-content')"
                                                        class="w-full text-left px-3.5 py-2.5 bg-slate-50 dark:bg-slate-950/40 hover:bg-slate-100 dark:hover:bg-slate-955 transition flex justify-between items-center border-b border-slate-100 dark:border-slate-850">
                                                    <div class="flex flex-col">
                                                        <span class="text-xs font-bold text-slate-850 dark:text-slate-200">{{ $supplierName }} ({{ $supplierCode }})</span>
                                                        @if($groupSalesUsernames)
                                                            <span class="text-[9px] text-indigo-650 dark:text-indigo-405 font-mono mt-0.5 font-semibold">Sales: {{ $groupSalesUsernames }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-[9px] bg-slate-150 dark:bg-slate-800 text-slate-705 dark:text-slate-400 px-2 py-0.5 rounded font-bold">
                                                        {{ $ttfItems->count() }} TTF
                                                    </span>
                                                </button>

                                                <!-- Accordion Content -->
                                                <div id="ttf-sup-{{ $supplierId }}-content" class="p-2.5 space-y-2.5">
                                                    @foreach($ttfItems as $t)
                                                    @php
                                                        $acceptedOffer = $t->goodsReceipt && $t->goodsReceipt->purchaseOrder ? $t->goodsReceipt->purchaseOrder->offers->firstWhere('status', 'accepted') : null;
                                                        $salesUsername = $acceptedOffer && $acceptedOffer->user ? $acceptedOffer->user->username : '-';
                                                    @endphp
                                                    <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-955/35 flex justify-between items-center">
                                                        <div class="space-y-0.5">
                                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Invoice TTF #{{ $t->id }}</p>
                                                            <p class="text-[9px] text-slate-450 dark:text-slate-500 font-semibold">Supplier: {{ $t->goodsReceipt->purchaseOrder->supplier->name ?? '-' }}</p>
                                                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Denda Cacat: <strong class="text-rose-600 dark:text-rose-500">Rp {{ number_format($t->total_deductions, 0, ',', '.') }}</strong></p>
                                                            <p class="text-[10px] text-slate-650 dark:text-slate-400 font-medium">Akun Sales: <strong class="text-indigo-600 dark:text-indigo-400 font-semibold">Akun Sales: 👤 {{ $salesUsername }}</strong></p>
                                                        </div>
                                                        <div class="text-right flex flex-col items-end">
                                                            <p class="text-[8px] text-slate-400 dark:text-slate-505 font-bold uppercase tracking-wider">Total Bersih</p>
                                                            <p class="text-xs font-black text-indigo-755 dark:text-indigo-400">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</p>
                                                            <span class="inline-block mt-0.5 px-1.5 py-0.5 text-[8px] font-bold bg-amber-50 dark:bg-amber-955/40 text-amber-700 dark:text-amber-400 border border-amber-105 dark:border-amber-900/35 rounded uppercase">{{ $t->status_payment }}</span>
                                                            <a href="{{ route('dashboard.ttf.print', $t->id) }}" target="_blank" class="inline-block mt-1 text-[9px] font-bold text-indigo-650 dark:text-indigo-455 hover:underline">
                                                                Detail & Cetak PDF
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tab 6: Service Level Report (MD view) -->
                @if($activeTab === 'service_level')
                    <!-- Alert Petunjuk Tahap -->
                    <div class="mb-4 p-3.5 bg-blue-50 dark:bg-blue-950/20 border-l-4 border-blue-600 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-blue-900 dark:text-blue-400 block">🏆 Evaluasi Rapor Performa Service Level Mitra B2B</span>
                            <p class="mt-0.5 leading-relaxed">Halaman ini menyajikan status kepatuhan operasional pengiriman barang seluruh mitra supplier ke Distribution Center (DC) AmandaMart berdasarkan tingkat akurasi pemenuhan stok logistik.</p>
                        </div>
                    </div>

                    <!-- Service Level Section -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm mb-6">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-450 rounded-lg flex items-center justify-center text-xs">🏆</span>
                            Rapor Performa Kepatuhan Supplier
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @foreach($suppliers as $supp)
                            @php
                                $report = $serviceLevels[$supp->id] ?? null;
                                $score = $report ? $report['score'] : 0;
                                $totalOrd = $report ? $report['total_ordered'] : 0;
                                $cleanRec = $report ? $report['clean_received'] : 0;
                                $totRet = $report ? $report['total_retur'] : 0;
                            @endphp
                            <div class="p-4 border border-slate-205 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 rounded-xl flex flex-col justify-between space-y-3">
                                <div class="flex justify-between items-start flex-wrap gap-2">
                                    <div>
                                        <h3 class="text-xs font-black text-slate-900 dark:text-white">{{ $supp->name }}</h3>
                                        <p class="text-[9px] text-slate-400 font-mono mt-0.5">ID Vendor: {{ $supp->supplier_code }}</p>
                                    </div>
                                    <div>
                                        @if($totalOrd == 0)
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-bold bg-slate-105 dark:bg-slate-800 text-slate-700 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-full uppercase tracking-wider">
                                                Belum Ada Transaksi
                                            </span>
                                        @elseif($score >= 95)
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-405 border border-emerald-100 dark:border-emerald-900/35 rounded-full uppercase tracking-wider">
                                                Sangat Bagus & Patuh
                                            </span>
                                        @elseif($score >= 85)
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-100 dark:border-blue-900/35 rounded-full uppercase tracking-wider">
                                                Bagus & Sesuai
                                            </span>
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-[8px] font-bold bg-rose-50 dark:bg-rose-955/40 text-rose-700 dark:text-rose-455 border border-rose-100 dark:border-rose-900/35 rounded-full uppercase tracking-wider">
                                                Perlu Perbaikan
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Progress Graphic (No Percent Text) -->
                                @if($totalOrd > 0)
                                <div class="space-y-1">
                                    <div class="flex justify-between text-[9px] font-bold text-slate-400">
                                        <span>Skala Kepatuhan Pengiriman:</span>
                                        <span class="{{ $score >= 95 ? 'text-emerald-600' : ($score >= 85 ? 'text-blue-500' : 'text-rose-500') }}">
                                            {{ $score >= 95 ? 'OPTIMAL' : ($score >= 85 ? 'STANDAR' : 'DI BAWAH RATA-RATA') }}
                                        </span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500
                                            {{ $score >= 95 ? 'bg-emerald-500' : ($score >= 85 ? 'bg-blue-500' : 'bg-rose-500') }}"
                                            style="width: {{ min(100, max(5, $score)) }}%">
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="text-[10.5px] text-slate-650 dark:text-slate-400 bg-white dark:bg-slate-900 border border-slate-150 dark:border-slate-800 p-3 rounded-xl space-y-1.5 font-medium">
                                    @if($totalOrd == 0)
                                        <p class="italic text-slate-400 text-center py-1 font-semibold">Belum memiliki riwayat transaksi pengiriman.</p>
                                    @else
                                        <p>Volume Dipesan Ritel: <strong class="text-slate-800 dark:text-white font-mono">{{ $totalOrd }} PCS</strong></p>
                                        <p>Volume Diterima Bersih: <strong class="text-slate-850 dark:text-slate-205 font-mono">{{ $cleanRec }} PCS</strong></p>
                                        <p>Barang Cacat (Retur): <strong class="{{ $totRet > 0 ? 'text-rose-650 dark:text-rose-400 font-extrabold' : 'text-slate-800 dark:text-slate-202' }} font-mono">{{ $totRet }} PCS</strong></p>
                                        <p class="text-[9.5px] leading-relaxed text-slate-550 dark:text-slate-450 mt-1.5 border-t border-slate-100 dark:border-slate-800 pt-1.5 font-semibold">
                                            @if($score >= 95)
                                                Operasional logistik memuaskan, pengiriman sesuai dengan kuantitas pesanan. Kemitraan dapat terus dikembangkan.
                                            @elseif($score >= 85)
                                                Kinerja logistik baik, tingkat pemenuhan logistik melampaui batas toleransi DC. Harap tingkatkan kualitas kemasan.
                                            @else
                                                Disarankan melakukan koordinasi perbaikan untuk mengurangi tingkat kerusakan barang dan denda.
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                <!-- ======================================================== -->
                <!-- ================= PORTAL SUPPLIER VIEW ================= -->
                <!-- ======================================================== -->

                <!-- Tab 1: Dashboard Overview -->
                @if($activeTab === 'dashboard')
                    <!-- Welcome Banner -->
                    <div class="bg-gradient-to-r from-emerald-50/40 to-white dark:from-slate-900/30 dark:to-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-sm mb-6 transition-all">
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                            Mitra Supplier: <span class="text-emerald-600 dark:text-emerald-450 font-black">{{ auth()->user()->username }}</span>
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 mt-1.5 text-xs max-w-3xl leading-relaxed">
                            Kirimkan penawaran harga terbaik untuk pengadaan barang ritel, daftarkan antrean bongkar muat armada logistik (VRS), serta monitor riwayat pembayaran tagihan keuangan (TTF) Anda.
                        </p>
                        
                        @if(isset($supplier))
                            <div class="mt-4 p-3 bg-emerald-55/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 rounded-xl flex flex-wrap gap-4 items-center text-[11px] font-semibold">
                                <div>
                                    <p class="text-[9px] text-emerald-600 dark:text-emerald-550 uppercase font-bold tracking-wider">Perusahaan Rekanan</p>
                                    <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300 mt-0.5">{{ $supplier->name }}</p>
                                </div>
                                <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>
                                <div>
                                    <p class="text-[9px] text-emerald-600 dark:text-emerald-550 uppercase font-bold tracking-wider">Kode Vendor B2B</p>
                                    <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300 mt-0.5 font-mono">{{ $supplier->supplier_code }}</p>
                                </div>
                                <div class="h-6 w-[1px] bg-slate-200 dark:bg-slate-800 hidden sm:block"></div>
                                <div>
                                    <p class="text-[9px] text-emerald-600 dark:text-emerald-550 uppercase font-bold tracking-wider">Nomor Notifikasi WhatsApp</p>
                                    <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300 mt-0.5 font-mono">{{ $supplier->whatsapp_number ?? '-' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Alert Petunjuk -->
                    <div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-emerald-900 dark:text-emerald-450 block">📊 Ringkasan Logistik Mitra</span>
                            <p class="mt-0.5 leading-relaxed">Berikut ini adalah rangkuman jadwal reservasi armada truk aktif (VRS) serta riwayat berkas tanda terima penerimaan LPB di gudang DC AmandaMart.</p>
                        </div>
                    </div>

                    <!-- Grid Layout: 2 Columns for Actions -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- VRS Active List -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="h-6 w-6 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-450 rounded-lg flex items-center justify-center text-xs">📅</span>
                                Antrean Truk Aktif Anda
                            </h3>
                            @if($vrsSchedules->isEmpty())
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 italic text-center py-6 bg-slate-50 dark:bg-slate-950/20 rounded-xl">Belum ada antrean truk logistik terdaftar.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($vrsSchedules as $sched)
                                    <div class="p-3 border border-slate-200 dark:border-slate-850 rounded-xl bg-slate-50 dark:bg-slate-950/40 flex justify-between items-center text-xs transition-all hover:bg-white dark:hover:bg-slate-900">
                                        <div class="space-y-0.5">
                                            <p class="font-bold text-slate-800 dark:text-slate-200">PO: {{ $sched->purchaseOrder->po_number }}</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold font-mono">Tanggal: {{ $sched->scheduled_date }} (Jadwal Truk: {{ $sched->time_slot }})</p>
                                        </div>
                                        <div>
                                            @if($sched->status === 'completed')
                                                <span class="px-2 py-0.5 text-[8px] font-bold bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-905 rounded-full uppercase">Selesai</span>
                                            @else
                                                <span class="px-2 py-0.5 text-[8px] font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-450 border border-amber-100 dark:border-amber-905 rounded-full uppercase text-amber-600">Booking</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- LPBs and Invoices -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="h-7 w-7 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center text-xs">🧾</span>
                                Laporan Penerimaan (LPB) & Invoice
                            </h3>
                            @if($goodsReceipts->isEmpty())
                                <p class="text-xs text-slate-450 dark:text-slate-550 italic text-center py-6 bg-slate-50 dark:bg-slate-950/20 rounded-xl">Belum ada dokumen penerimaan barang (LPB) yang terbit.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($goodsReceipts as $lpb)
                                    <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-white dark:bg-slate-900 shadow-sm text-xs space-y-1.5">
                                        <div class="flex justify-between items-center border-b border-slate-100 dark:border-slate-800 pb-1.5">
                                            <span class="font-bold text-slate-850 dark:text-slate-200">PO: {{ $lpb->purchaseOrder->po_number }}</span>
                                            <span class="text-[10px] text-slate-400 font-semibold font-mono">{{ $lpb->received_at->format('Y-m-d') }}</span>
                                        </div>
                                        <p class="text-slate-550 dark:text-slate-400">Tiba Fisik: <strong class="text-slate-750 dark:text-slate-300 font-semibold font-mono">{{ $lpb->qty_received }} PCS</strong></p>
                                        <p class="text-slate-555 dark:text-slate-400">Retur Rusak: <strong class="{{ $lpb->retur && $lpb->retur->qty_retur > 0 ? 'text-rose-600 dark:text-rose-455 font-bold' : 'text-slate-750 dark:text-slate-300 font-semibold' }} font-mono">{{ $lpb->retur ? $lpb->retur->qty_retur : 0 }} PCS</strong></p>
                                        
                                        <div class="mt-1.5 pt-1.5 border-t border-slate-105 dark:border-slate-800 flex justify-between items-center text-[9px] font-bold">
                                            <a href="{{ route('dashboard.lpb.print', $lpb->id) }}" target="_blank" class="text-blue-600 dark:text-blue-450 hover:underline">
                                                Detail & Cetak LPB
                                            </a>
                                            @if($lpb->ttf)
                                                <a href="{{ route('dashboard.ttf.print', $lpb->ttf->id) }}" target="_blank" class="text-indigo-600 dark:text-indigo-455 hover:underline">
                                                    Detail & Cetak TTF
                                                </a>
                                            @else
                                                <span class="text-amber-600 dark:text-amber-500 italic font-semibold">Menunggu TTF</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Tab 2: Bidding Submission -->
                @if($activeTab === 'bidding')
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
                            <p class="text-xs text-slate-400 dark:text-slate-550 italic text-center py-8 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada draf Purchase Order (PO) terbuka untuk bidding penawaran saat ini.</p>
                        @else
                            <form action="{{ route('dashboard.offers.submit') }}" method="POST" class="space-y-5">
                                @csrf
                                <div class="space-y-3">
                                    @foreach($biddingPos as $po)
                                    @php
                                        $existingOffer = $myOffersDetails->where('user_id', auth()->user()->id)->where('purchase_order_id', $po->id)->first();
                                    @endphp
                                    <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-955/20 shadow-inner">
                                        <div class="flex flex-wrap justify-between items-center gap-3">
                                            <div>
                                                <span class="px-2 py-0.5 text-[8px] font-bold bg-amber-100 dark:bg-amber-955 text-amber-800 dark:text-amber-450 border border-amber-200 rounded uppercase">Bidding Open</span>
                                                <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-1.5">{{ $po->po_number }}</h3>
                                                <p class="text-[10px] text-slate-505 dark:text-slate-400 mt-0.5">Produk: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $po->product->name }}</strong></p>
                                            </div>
                                            <div class="flex items-center space-x-2 w-full sm:w-auto">
                                                <div class="relative">
                                                    <span class="absolute left-2.5 top-1.5 text-[11px] font-bold text-slate-400">Rp</span>
                                                    <input type="number" 
                                                           name="prices[{{ $po->id }}]" 
                                                           min="0" 
                                                           value="{{ $existingOffer ? $existingOffer->price_per_pcs : '' }}" 
                                                           placeholder="Harga per PCS" 
                                                           class="border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-950 rounded-lg pl-7 pr-2 py-1 text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none w-40">
                                                </div>
                                                @if($existingOffer)
                                                    <span class="text-[8.5px] text-emerald-700 dark:text-emerald-400 font-bold bg-emerald-55/40 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-100/50 dark:border-emerald-900/30 font-mono">Terisi: Rp {{ number_format($existingOffer->price_per_pcs, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Box Kuantitas (Read-Only) -->
                                        <div class="mt-3 p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg flex items-center space-x-2 text-[10.5px] shadow-sm max-w-sm">
                                            <div class="h-6 w-6 bg-slate-100 dark:bg-slate-900/30 rounded flex items-center justify-center text-slate-400 dark:text-slate-400 flex-shrink-0">
                                                <svg width="16" height="16" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </div>
                                            <div class="flex-grow">
                                                <p class="font-semibold text-slate-650 dark:text-slate-350">Kuantitas PO Ritel (Locked):</p>
                                            </div>
                                            <div class="text-right flex-shrink-0 bg-blue-50 dark:bg-blue-955/40 text-blue-700 dark:text-blue-400 px-2.5 py-1 rounded border border-blue-100 dark:border-blue-900/30 font-extrabold font-mono">
                                                {{ $po->qty_po }} PCS
                                            </div>
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
                @endif

                <!-- Tab 3: VRS Scheduling -->
                @if($activeTab === 'vrs')
                    <!-- Alert Petunjuk -->
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-2xl text-slate-700 dark:text-slate-300 shadow-sm flex items-start space-x-3 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-emerald-900 dark:text-emerald-450 block">💡 Petunjuk Registrasi Antrean Truk (VRS)</span>
                            <p class="mt-0.5 leading-relaxed">Daftarkan reservasi bongkar muat untuk Purchase Order yang penawarannya telah disetujui (APPROVED). Kapasitas maksimal slot adalah 5 truk per slot waktu.</p>
                        </div>
                    </div>

                    <!-- VRS Scheduling Panel -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-450 rounded-lg flex items-center justify-center text-xs">🚛</span>
                            Stage 4: Booking Jadwal Armada Pengiriman Truk (VRS)
                        </h2>
                        
                        @php
                            $wonPos = $purchaseOrders->where('status', 'APPROVED')->filter(function($po) use ($vrsSchedules) {
                                return !$vrsSchedules->contains('purchase_order_id', $po->id);
                            });
                        @endphp

                        @if($wonPos->isEmpty())
                            <p class="text-xs text-slate-450 dark:text-slate-550 italic text-center py-8 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-xl">Tidak ada Purchase Order disetujui yang membutuhkan reservasi jadwal kirim saat ini.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($wonPos as $po)
                                <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50 dark:bg-slate-955/20 flex flex-wrap justify-between items-center gap-3">
                                    <div>
                                        <span class="px-2 py-0.5 text-[8px] font-bold bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-450 border border-blue-200 dark:border-blue-900/35 rounded-full uppercase">Awaiting Delivery</span>
                                        <h3 class="font-bold text-xs text-slate-900 dark:text-white mt-1.5">{{ $po->po_number }}</h3>
                                        <p class="text-[10px] text-rose-600 dark:text-rose-500 font-bold mt-0.5">Batas Waktu: {{ $po->delivery_deadline ? $po->delivery_deadline->format('Y-m-d') : '-' }}</p>
                                    </div>
                                    <form action="{{ route('dashboard.vrs.booking') }}" method="POST" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                                        @csrf
                                        <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                                        
                                        <input type="date" name="scheduled_date" required class="border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg p-1.5 text-xs font-semibold text-slate-850 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                        
                                        <select name="time_slot" required class="border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg p-1.5 text-xs font-semibold text-slate-850 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                            <option value="">Pilih Slot Waktu</option>
                                            <option value="09:00 - 11:00">09:00 - 11:00</option>
                                            <option value="11:00 - 13:00">11:00 - 13:00</option>
                                            <option value="13:00 - 15:00">13:00 - 15:00</option>
                                        </select>

                                        <button type="submit" class="bg-emerald-605 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1.5 px-3 rounded-lg text-[10px] shadow-sm transition duration-150">
                                            Daftarkan Jadwal
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tab 4: LPB Records -->
                @if($activeTab === 'lpb')
                    <!-- Alert Petunjuk -->
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-2xl text-slate-700 dark:text-slate-350 shadow-sm flex items-start space-x-3 no-print">
                        <svg class="h-4 w-4 text-emerald-650 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-emerald-900 dark:text-emerald-450 block">📥 Laporan Penerimaan Barang (LPB)</span>
                            <p class="mt-0.5 leading-relaxed">Gunakan menu ini untuk melacak dan mencetak Laporan Penerimaan Barang (LPB) resmi yang diterbitkan oleh Distribution Center AmandaMart.</p>
                        </div>
                    </div>

                    <!-- LPB List Panel -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-450 rounded-lg flex items-center justify-center text-xs">📥</span>
                            Riwayat Laporan Penerimaan Gudang Anda
                        </h3>
                        
                        @if($goodsReceipts->isEmpty())
                            <p class="text-xs text-slate-450 dark:text-slate-550 italic text-center py-6 bg-slate-50 dark:bg-slate-950/20 rounded-xl">Belum ada dokumen LPB yang terbit.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($goodsReceipts as $lpb)
                                <div class="p-3 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-950/20 text-xs space-y-2">
                                    <div class="flex justify-between items-center border-b border-slate-200/50 dark:border-slate-800 pb-2">
                                        <span class="font-bold text-slate-850 dark:text-slate-250">PO: {{ $lpb->purchaseOrder->po_number }}</span>
                                        <span class="text-[9px] text-slate-400 font-semibold font-mono">{{ $lpb->received_at->format('Y-m-d') }}</span>
                                    </div>
                                    <p class="text-slate-555 dark:text-slate-400">Tiba Fisik: <strong class="text-slate-750 dark:text-slate-300 font-semibold font-mono">{{ $lpb->qty_received }} PCS</strong></p>
                                    <p class="text-slate-555 dark:text-slate-400">Retur Rusak: <strong class="{{ $lpb->retur && $lpb->retur->qty_retur > 0 ? 'text-rose-600 dark:text-rose-455 font-bold' : 'text-slate-750 dark:text-slate-300 font-semibold' }} font-mono">{{ $lpb->retur ? $lpb->retur->qty_retur : 0 }} PCS</strong></p>
                                    
                                    <div class="mt-2 pt-2 border-t border-slate-200/50 dark:border-slate-800 flex justify-between items-center text-[9px] font-bold">
                                        <a href="{{ route('dashboard.lpb.print', $lpb->id) }}" target="_blank" class="text-blue-650 dark:text-blue-450 hover:underline">
                                            Detail & Cetak LPB
                                        </a>
                                        @if($lpb->ttf)
                                            <a href="{{ route('dashboard.ttf.print', $lpb->ttf->id) }}" target="_blank" class="text-indigo-650 dark:text-indigo-455 hover:underline">
                                                Detail & Cetak TTF
                                            </a>
                                        @else
                                            <span class="text-amber-605 dark:text-amber-500 italic font-semibold">Menunggu TTF</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tab 5: TTF Invoice List -->
                @if($activeTab === 'ttf')
                    <!-- Alert Petunjuk -->
                    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-2xl text-slate-700 dark:text-slate-350 shadow-sm flex items-start space-x-3 no-print">
                        <svg class="h-4 w-4 text-emerald-650 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-xs text-emerald-900 dark:text-emerald-450 block">🧾 Tanda Terima Faktur & Nota Pajak (TTF)</span>
                            <p class="mt-0.5 leading-relaxed">Pantau status pembayaran tagihan Anda di sini. Unduh nota tanda terima faktur untuk mempermudah proses pencairan logistik keuangan.</p>
                        </div>
                    </div>

                    <!-- TTF Invoice List -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-450 rounded-lg flex items-center justify-center text-xs">🧾</span>
                            Daftar Faktur Pembayaran Tagihan Anda
                        </h3>
                        
                        @if($ttfs->isEmpty())
                            <p class="text-xs text-slate-450 dark:text-slate-550 italic text-center py-6 bg-slate-50 dark:bg-slate-950/20 rounded-xl">Belum ada faktur TTF yang terdaftar.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($ttfs as $t)
                                <div class="p-3 border border-slate-200 dark:border-slate-850 rounded-xl bg-slate-55 dark:bg-slate-950/40 flex justify-between items-center text-xs">
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-slate-800 dark:text-slate-200">Invoice TTF #{{ $t->id }}</p>
                                        <p class="text-[9px] text-slate-500 dark:text-slate-455 font-mono">PO: {{ $t->goodsReceipt->purchaseOrder->po_number }}</p>
                                        <p class="text-[9px] text-slate-500 dark:text-slate-400">Denda Cacat: <strong class="text-rose-600 dark:text-rose-500">Rp {{ number_format($t->total_deductions, 0, ',', '.') }}</strong></p>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <p class="text-[8px] text-slate-400 dark:text-slate-505 font-bold uppercase tracking-wider">Total Bersih</p>
                                        <p class="text-xs font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</p>
                                        <span class="inline-block mt-0.5 px-1.5 py-0.5 text-[8px] font-bold bg-amber-50 dark:bg-amber-955/40 text-amber-705 dark:text-amber-400 border border-amber-105 dark:border-amber-900/35 rounded uppercase">{{ $t->status_payment }}</span>
                                        <a href="{{ route('dashboard.ttf.print', $t->id) }}" target="_blank" class="inline-block text-[9px] font-bold text-emerald-605 dark:text-emerald-450 hover:underline mt-1">
                                            Cetak PDF
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Tab 6: Profile & Security settings -->
                @if($activeTab === 'profile')
                    <!-- Alert Petunjuk -->
                    <div class="mb-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 rounded-r-xl text-slate-700 dark:text-slate-400 shadow-sm flex items-start space-x-2.5 no-print">
                        <svg width="16" height="16" class="h-4 w-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="text-[11px]">
                            <span class="font-bold text-sm text-emerald-900 dark:text-emerald-455 block">👤 Pengaturan Profil & Keamanan Akun B2B</span>
                            <p class="mt-0.5 leading-relaxed">Perbarui nomor kontak darurat untuk notifikasi dispatch via WhatsApp, serta ganti kata sandi keamanan masuk akun sales Anda.</p>
                        </div>
                    </div>

                    <!-- Profile Settings Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        
                        <!-- Profile Form Column -->
                        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm h-fit">
                            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                                <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-455 rounded-lg flex items-center justify-center text-xs">👤</span>
                                Ubah Profil & Keamanan Akun
                            </h2>

                            <form action="{{ route('dashboard.profile.update') }}" method="POST" class="space-y-4">
                                @csrf
                                
                                <div class="space-y-3">
                                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest font-mono">KONTAK INFORMASI NOTIFIKASI</h4>
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-550 dark:text-slate-450 uppercase tracking-wide mb-1">Nomor WhatsApp Notifikasi</label>
                                        <input type="text" name="whatsapp_number" 
                                               value="{{ isset($supplier) ? $supplier->whatsapp_number : '' }}"
                                               placeholder="Contoh: 081234567890" 
                                               class="w-full border border-slate-350 dark:border-slate-800 bg-white dark:bg-slate-950 rounded-lg py-2 px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                        <span class="text-[9px] text-slate-400 mt-0.5 block">Nomor ini digunakan untuk mengirim pesan rekap PO dan status antrean logistik secara berkala.</span>
                                    </div>
                                </div>

                                <hr class="border-slate-100 dark:border-slate-850">

                                <div class="space-y-3">
                                    <h4 class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest font-mono">KEAMANAN & SANDI AKUN</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-550 dark:text-slate-450 uppercase tracking-wide mb-1">Kata Sandi Baru</label>
                                            <input type="password" name="password" placeholder="Minimal 6 karakter"
                                                   class="w-full border border-slate-355 dark:border-slate-800 bg-white dark:bg-slate-950 rounded-lg py-2 px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-550 dark:text-slate-450 uppercase tracking-wide mb-1">Konfirmasi Sandi Baru</label>
                                            <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru"
                                                   class="w-full border border-slate-355 dark:border-slate-800 bg-white dark:bg-slate-955 rounded-lg py-2 px-3 text-xs font-bold text-slate-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                        </div>
                                    </div>
                                    <span class="text-[9px] text-slate-400 block">Biarkan kosong jika Anda tidak ingin memperbarui kata sandi saat ini.</span>
                                </div>

                                <div class="pt-3 border-t border-slate-105 dark:border-slate-800 flex justify-end">
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-[11px] shadow-sm hover:shadow-md active:scale-[0.98] transition-all duration-200 flex items-center space-x-1.5">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Perbarui Profil & Sandi</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Read Only Company Info Column -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm h-fit">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                                <span class="h-6 w-6 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-455 rounded-lg flex items-center justify-center text-xs">🏢</span>
                                Detail Perusahaan Rekanan
                            </h3>
                            @if(isset($supplier))
                                <div class="space-y-3">
                                    <div class="p-2.5 bg-slate-50 dark:bg-slate-955/35 rounded-xl border border-slate-150 dark:border-slate-850">
                                        <p class="text-[9px] text-slate-450 uppercase font-bold tracking-wider">Nama PT Supplier:</p>
                                        <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">{{ $supplier->name }}</p>
                                    </div>
                                    <div class="p-2.5 bg-slate-50 dark:bg-slate-955/35 rounded-xl border border-slate-150 dark:border-slate-850">
                                        <p class="text-[9px] text-slate-450 uppercase font-bold tracking-wider">Kode Vendor B2B Resmi:</p>
                                        <p class="text-[11px] font-bold text-slate-800 dark:text-slate-200 mt-0.5 font-mono">{{ $supplier->supplier_code }}</p>
                                    </div>
                                    <div class="p-2.5 bg-slate-50 dark:bg-slate-955/35 rounded-xl border border-slate-150 dark:border-slate-850">
                                        <p class="text-[9px] text-slate-450 uppercase font-bold tracking-wider">Username Pengguna:</p>
                                        <p class="text-[11px] font-bold text-slate-850 dark:text-slate-202 mt-0.5 font-mono">{{ auth()->user()->username }}</p>
                                    </div>
                                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/30 text-[10px] leading-relaxed text-emerald-805 dark:text-emerald-400">
                                        <strong>ℹ️ Catatan Sistem:</strong> Informasi Nama PT dan Kode Vendor dikunci mutlak demi integritas data dan keamanan transaksi. Hubungi Tim MD AmandaMart untuk pemutakhiran data korporat.
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            @endif
        </main>
    </div>

    <!-- JavaScript Handlers -->
    <script>
        // Sidebar Toggling logic for mobile
        const sidebarToggle = document.getElementById('mobile-sidebar-toggle');
        const sidebarClose = document.getElementById('mobile-sidebar-close');
        const sidebarNav = document.getElementById('sidebar-nav');

        if (sidebarToggle && sidebarNav) {
            sidebarToggle.addEventListener('click', () => {
                sidebarNav.classList.remove('-translate-x-full');
            });
        }
        if (sidebarClose && sidebarNav) {
            sidebarClose.addEventListener('click', () => {
                sidebarNav.classList.add('-translate-x-full');
            });
        }

        // Collapse/Expand accordion handlers
        function toggleSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function toggleLpbSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function toggleVrsSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function toggleProductSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function toggleTtfLpbSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function toggleTtfSupplier(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.toggle('hidden');
            }
        }

        function selectSalesOffer(salesUserId) {
            // Hide placeholder
            const placeholder = document.getElementById('offer-detail-placeholder');
            if (placeholder) {
                placeholder.classList.add('hidden');
            }

            // Hide all details
            const offerDivs = document.querySelectorAll('[id^="offer-detail-sales-"]');
            offerDivs.forEach(div => {
                div.classList.add('hidden');
            });

            // Show selected detail
            const selectedDiv = document.getElementById('offer-detail-sales-' + salesUserId);
            if (selectedDiv) {
                selectedDiv.classList.remove('hidden');
            }

            // Highlight active button
            const buttons = document.querySelectorAll('.sales-btn');
            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-100', 'dark:bg-blue-950/40', 'text-blue-700', 'dark:text-blue-400', 'font-bold');
                btn.classList.add('text-slate-650', 'dark:text-slate-450', 'font-medium');
            });
            const activeBtn = document.getElementById('btn-sales-' + salesUserId);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-650', 'dark:text-slate-450', 'font-medium');
                activeBtn.classList.add('bg-blue-100', 'dark:bg-blue-950/40', 'text-blue-700', 'dark:text-blue-400', 'font-bold');
            }
        }

        // Toast Notification Helper
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            
            const toast = document.createElement('div');
            const bgClass = type === 'success' 
                ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-500 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400' 
                : 'bg-rose-50 dark:bg-rose-900/30 border-rose-500 dark:border-rose-800 text-rose-800 dark:text-rose-400';
            const iconColor = type === 'success' ? 'text-emerald-500' : 'text-rose-500';
            const iconSvg = type === 'success' 
                ? `<svg width="16" height="16" class="h-4 w-4 ${iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
                : `<svg width="16" height="16" class="h-4 w-4 ${iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`;
            
            toast.className = `p-3.5 border-l-4 rounded-xl shadow-md flex items-center space-x-2.5 transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto ${bgClass}`;
            toast.innerHTML = `
                ${iconSvg}
                <span class="font-bold text-[11px] sm:text-xs">${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);
            
            // Animate out and remove
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 4000);
        }

        // AJAX Interceptors for MD operations
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Generate PO Form
            const genPoForm = document.getElementById('generate-po-form');
            if (genPoForm) {
                genPoForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full mr-1.5"></span> Memproses...`;
                    
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            btn.innerHTML = `<span>⚡ PO Berhasil Dibuat</span>`;
                            btn.className = "w-full bg-emerald-600 text-white font-bold py-2.5 px-4 rounded-xl shadow-sm transition-all text-[11px] text-center";
                            
                            // Dynamically update UI state of critical items in products table without reload
                            const criticalRows = document.querySelectorAll('.product-row[data-critical="true"]');
                            criticalRows.forEach(row => {
                                row.setAttribute('data-critical', 'false');
                                const statusCell = row.querySelector('.status-cell');
                                if (statusCell) {
                                    statusCell.innerHTML = `<span class="px-2 py-0.5 text-[9px] font-semibold bg-slate-50 dark:bg-slate-800 text-slate-650 dark:text-slate-400 border border-slate-100 dark:border-slate-700 rounded-full uppercase">Aman</span>`;
                                }
                                const stockCell = row.querySelector('.font-bold.text-rose-600');
                                if (stockCell) {
                                    stockCell.classList.remove('text-rose-600', 'dark:text-rose-500');
                                    stockCell.classList.add('text-slate-750', 'dark:text-slate-350');
                                }
                            });
                        } else {
                            showToast(data.message || 'Terjadi kesalahan.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Terjadi kesalahan koneksi jaringan.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                });
            }

            // 2. Approve Offer Form (AJAX submission - no page reload)
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('ajax-approve-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const poId = form.getAttribute('data-po-id');
                    const salesId = form.getAttribute('data-sales-id');
                    const btn = form.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    
                    btn.disabled = true;
                    btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border border-current border-t-transparent rounded-full mr-1"></span>`;
                    
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            
                            const card = document.getElementById('po-card-' + poId);
                            if (card) {
                                card.style.transition = 'all 0.2s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(-5px)';
                                setTimeout(() => {
                                    card.remove();
                                    
                                    const salesPanel = document.getElementById('offer-detail-sales-' + salesId);
                                    if (salesPanel) {
                                        const remainingCards = salesPanel.querySelectorAll('.po-card');
                                        if (remainingCards.length === 0) {
                                            salesPanel.classList.add('hidden');
                                            
                                            const salesBtn = document.getElementById('btn-sales-' + salesId);
                                            if (salesBtn) {
                                                salesBtn.remove();
                                            }
                                            
                                            const placeholder = document.getElementById('offer-detail-placeholder');
                                            if (placeholder) {
                                                placeholder.classList.remove('hidden');
                                            }
                                        } else {
                                            const salesBtn = document.getElementById('btn-sales-' + salesId);
                                            if (salesBtn) {
                                                const countBadge = salesBtn.querySelector('span:last-child');
                                                if (countBadge) {
                                                    countBadge.textContent = `${remainingCards.length} Barang`;
                                                }
                                            }
                                        }
                                    }
                                }, 200);
                            }
                        } else {
                            showToast(data.message || 'Terjadi kesalahan.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Terjadi kesalahan koneksi jaringan.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                }
            });

            // 3. Save LPB Form (AJAX submission - no page reload)
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('ajax-lpb-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const poId = form.getAttribute('data-po-id');
                    const supplierId = form.getAttribute('data-supplier-id');
                    const btn = form.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    
                    btn.disabled = true;
                    btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border border-current border-t-transparent rounded-full mr-1"></span>`;
                    
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            
                            const card = document.getElementById('lpb-card-' + poId);
                            if (card) {
                                card.style.transition = 'all 0.2s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'translateY(-5px)';
                                setTimeout(() => {
                                    card.remove();
                                    
                                    const supplierPanel = document.getElementById('lpb-sup-' + supplierId);
                                    if (supplierPanel) {
                                        const remainingCards = supplierPanel.querySelectorAll('.lpb-card');
                                        if (remainingCards.length === 0) {
                                            const accordionDiv = supplierPanel.closest('.border.border-slate-200');
                                            if (accordionDiv) {
                                                accordionDiv.remove();
                                            }
                                        }
                                    }
                                }, 200);
                            }
                        } else {
                            showToast(data.message || 'Terjadi kesalahan.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Terjadi kesalahan koneksi jaringan.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                }
            });

            // 4. Generate TTF Form (AJAX submission - no page reload)
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.classList.contains('ajax-ttf-form')) {
                    e.preventDefault();
                    const form = e.target;
                    const lpbId = form.getAttribute('data-lpb-id');
                    const supplierId = form.getAttribute('data-supplier-id');
                    const btn = form.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    
                    btn.disabled = true;
                    btn.innerHTML = `<span class="animate-spin inline-block w-3.5 h-3.5 border border-current border-t-transparent rounded-full mr-1"></span>`;
                    
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            
                            const row = document.getElementById('ttf-lpb-row-' + lpbId);
                            if (row) {
                                row.style.transition = 'all 0.2s ease';
                                row.style.opacity = '0';
                                row.style.transform = 'translateY(-5px)';
                                setTimeout(() => {
                                    row.remove();
                                    
                                    const accordionContent = document.getElementById('ttf-lpb-sup-' + supplierId);
                                    if (accordionContent) {
                                        const remainingRows = accordionContent.querySelectorAll('.ttf-lpb-row');
                                        if (remainingRows.length === 0) {
                                            const accordionDiv = accordionContent.closest('.border.border-slate-200');
                                            if (accordionDiv) {
                                                accordionDiv.remove();
                                            }
                                        }
                                    }
                                }, 200);
                            }
                        } else {
                            showToast(data.message || 'Terjadi kesalahan.', 'error');
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        showToast(err.message || 'Terjadi kesalahan koneksi jaringan.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
                }
            });
        });

        // Theme Switcher Code
        const themeToggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        function updateThemeIcons() {
            if (document.documentElement.classList.contains('dark')) {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            } else {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            }
        }

        updateThemeIcons();

        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        });
    </script>
</body>
</html>
