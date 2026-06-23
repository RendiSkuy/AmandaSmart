<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem B2B AmandaMart — Selamat Datang</title>
    
    <!-- Google Fonts & Tailwind Play CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden transition-colors duration-300">
    
    <!-- Background Decorative Gradients -->
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-blue-400/20 dark:bg-blue-600/15 blur-3xl -z-10 animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-emerald-400/20 dark:bg-emerald-600/15 blur-3xl -z-10 animate-pulse pointer-events-none" style="animation-delay: 2s;"></div>

    <!-- Theme Toggle Floating Button -->
    <button id="theme-toggle" class="fixed top-6 right-6 p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg text-slate-700 dark:text-slate-300 hover:scale-105 hover:bg-slate-100 dark:hover:bg-slate-800 active:scale-95 transition-all duration-200 z-50" aria-label="Toggle Theme">
        <!-- Sun Icon -->
        <svg id="sun-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
        </svg>
        <!-- Moon Icon -->
        <svg id="moon-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>

    <!-- Main Container -->
    <div class="w-full max-w-3xl bg-white/70 dark:bg-slate-900/60 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-800/50 p-8 sm:p-12 relative transition-all duration-300">
        
        <!-- Header Section -->
        <div class="text-center mb-10">
            <span class="px-4 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-full uppercase tracking-widest shadow-sm">
                Portal Integrasi Rantai Pasok B2B
            </span>
            <div class="flex flex-col items-center justify-center mt-5">
                <img src="{{ asset('logo-amandamart.png') }}" alt="AmandaMart Logo" class="h-16 w-auto">
                <h1 class="text-2xl font-black text-slate-700 dark:text-slate-300 tracking-wider uppercase mt-3">B2B Portal</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 max-w-lg mx-auto leading-relaxed">
                Platform kolaborasi rantai pasok antara PT Amanda Smart Retail Tbk dengan para Supplier & Vendor rekanan untuk manajemen logistik dan pengadaan yang efisien.
            </p>
        </div>

        <!-- Portals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            
            <!-- Supplier Portal Link -->
            <a href="/login-supplier" class="group flex flex-col justify-between p-6 rounded-2xl border border-emerald-200/60 dark:border-emerald-950 bg-gradient-to-br from-emerald-50/40 to-white dark:from-emerald-950/20 dark:to-slate-900 hover:from-emerald-50/80 hover:shadow-lg hover:scale-[1.01] transition-all duration-300 relative overflow-hidden">
                <!-- Decorative Corner Light -->
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-300"></div>
                
                <div class="mb-8">
                    <!-- Icon Box -->
                    <div class="h-12 w-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4 group-hover:scale-110 transition-transform duration-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h2 class="font-extrabold text-emerald-800 dark:text-emerald-400 text-lg group-hover:translate-x-1 transition-transform duration-200">
                        Portal Supplier & Vendor &rarr;
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Akses khusus rekanan luar (b2b.amanda.id). Input penawaran harga modal, kelola reservasi antrean truk (VRS), dan pantau penerbitan Faktur Keuangan (TTF).
                    </p>
                </div>
                <div class="flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    Masuk sebagai Rekanan Vendor
                </div>
            </a>
            
            <!-- MD Portal Link -->
            <a href="/login-md" class="group flex flex-col justify-between p-6 rounded-2xl border border-blue-200/60 dark:border-blue-950 bg-gradient-to-br from-blue-50/40 to-white dark:from-blue-950/20 dark:to-slate-900 hover:from-blue-50/80 hover:shadow-lg hover:scale-[1.01] transition-all duration-300 relative overflow-hidden">
                <!-- Decorative Corner Light -->
                <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:scale-125 transition-transform duration-300"></div>

                <div class="mb-8">
                    <!-- Icon Box -->
                    <div class="h-12 w-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:scale-110 transition-transform duration-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h2 class="font-extrabold text-blue-800 dark:text-blue-400 text-lg group-hover:translate-x-1 transition-transform duration-200">
                        Portal Internal Merchandiser &rarr;
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                        Akses khusus tim internal AmandaMart (md.amanda.id). Kelola master produk kritis, lakukan persetujuan pemenang lelang (bidding), dan pantau bongkar muat gudang.
                    </p>
                </div>
                <div class="flex items-center text-xs font-bold text-blue-600 dark:text-blue-400">
                    Masuk sebagai Tim Internal MD
                </div>
            </a>
        </div>

        <!-- Footer Note -->
        <div class="text-center text-[11px] text-slate-400 dark:text-slate-500 border-t border-slate-200/50 dark:border-slate-800/50 pt-6">
            &copy; 2026 PT Amanda Smart Retail Tbk. Seluruh Hak Cipta Dilindungi Undang-Undang.<br>
            Sistem Manajemen Rantai Pasok Internal AmandaMart.
        </div>
    </div>

    <!-- Script for Theme Toggle -->
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

        // Initialize icons
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