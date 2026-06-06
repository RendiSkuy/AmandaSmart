<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA — AmandaMart B2B</title>
    
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
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden transition-colors duration-300">
    
    <!-- Background Decorative Gradients -->
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-blue-400/20 dark:bg-blue-600/10 blur-3xl -z-10 animate-pulse pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] rounded-full bg-slate-300/20 dark:bg-slate-800/10 blur-3xl -z-10 animate-pulse pointer-events-none" style="animation-delay: 2s;"></div>

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

    <!-- Main Wrapper -->
    <div class="w-full max-w-md transition-all duration-300">
        
        <!-- Brand -->
        <div class="flex items-center gap-3 mb-8 justify-center">
            <div class="h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-extrabold shadow-md">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-extrabold text-slate-800 dark:text-white leading-none">AMANDA<span class="text-blue-600 dark:text-blue-500">mart</span></h1>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Portal B2B Supplier & Vendor</p>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-between mb-8 px-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <div class="h-6 w-6 rounded-full bg-emerald-100 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 flex items-center justify-center text-[10px]">
                    ✓
                </div>
                <span>Login</span>
            </div>
            <div class="flex-1 h-[1px] bg-slate-200 dark:bg-slate-800 mx-3"></div>
            <div class="flex items-center gap-2 text-xs font-bold text-blue-600 dark:text-blue-400">
                <div class="h-6 w-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shadow-sm">
                    2
                </div>
                <span>Verifikasi 2FA</span>
            </div>
            <div class="flex-1 h-[1px] bg-slate-200 dark:bg-slate-800 mx-3"></div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 dark:text-slate-600">
                <div class="h-6 w-6 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-[10px]">
                    3
                </div>
                <span>Selesai</span>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-200/50 dark:border-slate-800/50 p-6 sm:p-8 mb-6 transition-all duration-300 text-center">
            
            <!-- Icon Shield -->
            <div class="flex justify-center mb-6">
                <div class="h-14 w-14 bg-slate-100 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-850 rounded-2xl flex items-center justify-center text-slate-700 dark:text-slate-300 shadow-inner">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>

            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Verifikasi Identitas Anda
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Silakan masukkan 6-digit kode verifikasi dari aplikasi <strong class="text-slate-800 dark:text-slate-200">Google Authenticator</strong> di ponsel cerdas Anda untuk melanjutkan.
            </p>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="p-4 my-4 text-xs text-left text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 rounded-2xl flex items-start space-x-2" role="alert">
                    <svg class="h-4 w-4 text-rose-600 dark:text-rose-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Countdown Timer -->
            <div class="flex items-center justify-center gap-3 my-5 py-3.5 bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-2xl">
                <!-- SVG Timer Ring -->
                <div class="relative w-9 h-9 flex-shrink-0">
                    <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                        <circle class="stroke-slate-200 dark:stroke-slate-800 fill-none" stroke-width="3.5" cx="18" cy="18" r="14"/>
                        <circle class="stroke-blue-600 dark:stroke-blue-500 fill-none transition-all duration-1000" stroke-width="3.5" stroke-dasharray="88" stroke-dashoffset="0" stroke-linecap="round" id="timerRing" cx="18" cy="18" r="14"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center font-mono text-xs font-bold text-slate-800 dark:text-white" id="timerNum">
                        30
                    </div>
                </div>
                <div class="text-left text-xs text-slate-500 dark:text-slate-400 leading-normal">
                    Kode OTP berubah secara dinamis setiap <strong class="text-slate-700 dark:text-slate-300">30 detik</strong>.
                </div>
            </div>

            <!-- OTP Form -->
            <form id="verifyForm" method="POST" action="{{ route('2fa.verify.confirm') }}" class="space-y-4">
                @csrf

                <div class="text-left">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2" for="otp">
                        Kode Authenticator
                    </label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        class="w-full py-3.5 text-center text-3xl font-mono font-bold tracking-[0.75em] bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition duration-200 text-slate-900 dark:text-white"
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autocomplete="one-time-code"
                        autofocus
                        required
                    >
                    
                    @error('otp')
                        <div class="text-[11px] text-rose-600 dark:text-rose-400 mt-2 font-semibold">{{ $message }}</div>
                    @else
                        <div class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1.5 mt-2">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Temukan akun <strong>AmandaMart</strong> di aplikasi Google Authenticator Anda.
                        </div>
                    @enderror
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all duration-200 text-sm flex items-center justify-center gap-2" id="submitBtn">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="btn-text">Masuk ke Dashboard</span>
                </button>
            </form>

            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 mt-6 transition-colors">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Login
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 dark:text-slate-500">
            &copy; 2026 AmandaMart &mdash; Sistem Manajemen Rantai Pasok B2B
        </div>
    </div>

    <!-- Scripting for Timer, Validation & Theme Switcher -->
    <script>
        // Only numbers in input
        document.getElementById('otp').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });

        // Loading state
        document.getElementById('verifyForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `
                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                Memverifikasi...
            `;
        });

        // Dynamic Countdown Timer (TOTP 30s)
        function startTimer() {
            const ring = document.getElementById('timerRing');
            const num = document.getElementById('timerNum');
            const circumference = 2 * Math.PI * 14; // r=14

            ring.style.strokeDasharray = circumference;

            function tick() {
                const now = Math.floor(Date.now() / 1000);
                const seconds = 30 - (now % 30);
                const offset = circumference * (1 - seconds / 30);

                ring.style.strokeDashoffset = offset;
                num.textContent = seconds;

                // Turn red in the last 5 seconds
                if (seconds <= 5) {
                    ring.classList.replace('stroke-blue-600', 'stroke-rose-600');
                    ring.classList.replace('stroke-blue-500', 'stroke-rose-600');
                    num.classList.add('text-rose-600');
                } else {
                    ring.classList.remove('stroke-rose-600');
                    ring.classList.add('stroke-blue-600', 'dark:stroke-blue-500');
                    num.classList.remove('text-rose-600');
                }
            }

            tick();
            setInterval(tick, 1000);
        }

        startTimer();

        // Theme Switcher Code
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