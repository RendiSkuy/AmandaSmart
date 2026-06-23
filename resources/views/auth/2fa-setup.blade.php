<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA — AmandaMart B2B</title>
    
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
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex flex-col items-center justify-start py-12 px-6 relative transition-colors duration-300">
    
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
    <div class="w-full max-w-sm transition-all duration-300">
        
        <!-- Brand -->
        <div class="flex flex-col items-center justify-center mb-5">
            <img src="{{ asset('logo-amandamart.png') }}" alt="AmandaMart Logo" class="h-8.5 w-auto">
            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Portal B2B Supplier & Vendor</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex items-center justify-between mb-6 px-3">
            <div class="flex items-center gap-1.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                <div class="h-5 w-5 rounded-full bg-emerald-100 dark:bg-emerald-955/50 border border-emerald-300 dark:border-emerald-800 flex items-center justify-center text-[9px]">
                    ✓
                </div>
                <span>Login</span>
            </div>
            <div class="flex-1 h-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>
            <div class="flex items-center gap-1.5 text-[10px] font-bold text-blue-600 dark:text-blue-400">
                <div class="h-5 w-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] shadow-sm">
                    2
                </div>
                <span>Setup 2FA</span>
            </div>
            <div class="flex-1 h-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>
            <div class="flex items-center gap-1.5 text-[10px] font-semibold text-slate-400 dark:text-slate-600">
                <div class="h-5 w-5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-[9px]">
                    3
                </div>
                <span>Selesai</span>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white/80 dark:bg-slate-900/60 backdrop-blur-xl rounded-2xl shadow-xl border border-slate-200/50 dark:border-slate-800/50 p-5 sm:p-6 mb-4 transition-all duration-300">
            
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight">
                Setup Google Authenticator
            </h2>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                Halo, <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ $username }}</strong>! Ini adalah pertama kalinya Anda masuk ke sistem. Silakan sambungkan akun Anda ke Google Authenticator untuk verifikasi keamanan dua faktor (2FA).
            </p>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="p-3 my-3 text-[11px] text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-955/30 border border-rose-200 dark:border-rose-900/50 rounded-xl flex items-start space-x-2" role="alert">
                    <svg class="h-4 w-4 text-rose-600 dark:text-rose-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Warning Info Box -->
            <div class="p-4 my-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl flex items-start gap-3 text-xs text-amber-800 dark:text-amber-400 leading-relaxed">
                <svg class="h-5 w-5 text-amber-600 dark:text-amber-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <strong class="font-bold">Belum memiliki Google Authenticator?</strong><br>
                    Unduh aplikasi terlebih dahulu pada ponsel cerdas Anda:
                    <div class="flex gap-2 mt-2">
                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="flex-1 py-1.5 px-3 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-slate-400 text-slate-600 dark:text-slate-300 text-center font-bold text-[10px] bg-white dark:bg-slate-950 transition-colors">
                            Android Google Play
                        </a>
                        <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="flex-1 py-1.5 px-3 border border-slate-200 dark:border-slate-800 rounded-lg hover:border-slate-400 text-slate-600 dark:text-slate-300 text-center font-bold text-[10px] bg-white dark:bg-slate-950 transition-colors">
                            iOS App Store
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tab Switching Buttons -->
            <div class="flex border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden mb-4 bg-slate-50 dark:bg-slate-955/40 p-0.5">
                <button class="flex-1 py-1.5 text-[11px] font-bold rounded-lg transition-all flex items-center justify-center gap-1 bg-white dark:bg-slate-900 shadow-sm text-slate-900 dark:text-white" id="tab-btn-qr" onclick="switchTab('qr')">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Pindai QR Code
                </button>
                <button class="flex-1 py-1.5 text-[11px] font-bold rounded-lg transition-all flex items-center justify-center gap-1 text-slate-400 dark:text-slate-600 hover:text-slate-800 dark:hover:text-slate-300" id="tab-btn-manual" onclick="switchTab('manual')">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Kunci Manual
                </button>
            </div>

            <!-- Tab Content: QR Code -->
            <div id="tab-content-qr" class="block">
                <div class="flex flex-col items-center gap-3 p-4 bg-slate-50 dark:bg-slate-955/40 border border-slate-200 dark:border-slate-800 rounded-xl mb-4">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrCodeUrl) }}" alt="QR Code 2FA" class="w-36 h-36 border-4 border-white dark:border-slate-900 rounded-lg shadow-md">
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 text-center leading-relaxed">
                        Pindai kode QR di atas dengan membuka aplikasi <strong class="text-slate-800 dark:text-slate-200">Google Authenticator</strong>, tekan tombol <strong class="text-slate-800 dark:text-slate-200">"+"</strong>, lalu pilih <strong class="text-slate-800 dark:text-slate-200">"Scan QR Code"</strong>.
                    </p>
                </div>
            </div>

            <!-- Tab Content: Manual Key -->
            <div id="tab-content-manual" class="hidden">
                <div class="p-5 bg-slate-50 dark:bg-slate-950/40 border border-slate-200 dark:border-slate-800 rounded-2xl mb-5 space-y-4 text-xs">
                    <ol class="list-decimal pl-4 space-y-2 text-slate-600 dark:text-slate-400 leading-relaxed">
                        <li>Buka aplikasi <strong>Google Authenticator</strong> di ponsel Anda.</li>
                        <li>Tekan ikon tambah <strong>"+"</strong> di bagian kanan bawah.</li>
                        <li>Pilih opsi <strong>"Enter a setup key"</strong>.</li>
                        <li>Masukkan nama akun (misal: AmandaMart) dan isi kolom <strong>Key</strong> dengan kode di bawah.</li>
                    </ol>

                    <div class="flex items-center justify-between p-3.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl gap-3">
                        <span class="font-mono text-base font-semibold tracking-widest text-slate-900 dark:text-white" id="secretKeyDisplay">
                            {{ implode(' ', str_split($secretKey, 4)) }}
                        </span>
                        <button type="button" class="py-1.5 px-3 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-600 text-[10px] font-bold rounded-lg text-slate-600 dark:text-slate-300 transition-colors flex items-center gap-1" id="copyBtn" onclick="copyKey()">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            Salin
                        </button>
                    </div>

                    <div class="text-[10px] text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Rahasiakan kunci manual ini demi keamanan akun Anda.
                    </div>
                </div>
            </div>

            <hr class="border-slate-200 dark:border-slate-800 my-4">

            <!-- OTP Confirmation Form -->
            <form id="setupForm" method="POST" action="{{ route('2fa.setup.confirm') }}" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1.5" for="otp">
                        Kode Konfirmasi OTP
                    </label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        class="w-full py-2 text-center text-xl font-mono font-bold tracking-[0.75em] bg-slate-50 dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition duration-200 text-slate-900 dark:text-white"
                        placeholder="000000"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autocomplete="one-time-code"
                        required
                    >
                </div>

                <button type="submit" class="w-full py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md hover:shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all duration-200 text-xs flex items-center justify-center gap-1.5" id="submitBtn">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Konfirmasi & Aktifkan 2FA
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-[10px] text-slate-400 dark:text-slate-550">
            Butuh bantuan? Silakan hubungi tim IT Administrator &mdash; 
            <a href="{{ route('login') }}" class="underline hover:text-slate-600 dark:hover:text-slate-300">Kembali ke Login</a>
        </div>
    </div>

    <!-- Scripting for Tabs, Copy and Theme Switcher -->
    <script>
        // Tab Switcher
        function switchTab(mode) {
            const qrBtn = document.getElementById('tab-btn-qr');
            const manualBtn = document.getElementById('tab-btn-manual');
            const qrContent = document.getElementById('tab-content-qr');
            const manualContent = document.getElementById('tab-content-manual');

            const activeClass = ['bg-white', 'dark:bg-slate-900', 'shadow-sm', 'text-slate-900', 'dark:text-white'];
            const inactiveClass = ['text-slate-400', 'dark:text-slate-600', 'hover:text-slate-800', 'dark:hover:text-slate-300'];

            if (mode === 'qr') {
                qrBtn.classList.add(...activeClass);
                qrBtn.classList.remove(...inactiveClass);
                manualBtn.classList.remove(...activeClass);
                manualBtn.classList.add(...inactiveClass);
                qrContent.classList.replace('hidden', 'block');
                manualContent.classList.replace('block', 'hidden');
            } else {
                manualBtn.classList.add(...activeClass);
                manualBtn.classList.remove(...inactiveClass);
                qrBtn.classList.remove(...activeClass);
                qrBtn.classList.add(...inactiveClass);
                manualContent.classList.replace('hidden', 'block');
                qrContent.classList.replace('block', 'hidden');
            }
        }

        // Initialize Tab Visuals
        switchTab('qr');

        // Copy Key Functionality
        function copyKey() {
            const secret = '{{ $secretKey }}';
            navigator.clipboard.writeText(secret).then(() => {
                const btn = document.getElementById('copyBtn');
                btn.innerHTML = `
                    <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Tersalin!
                `;
                btn.classList.add('border-emerald-300', 'bg-emerald-50', 'text-emerald-700', 'dark:bg-emerald-950/20', 'dark:border-emerald-900/50');
                setTimeout(() => {
                    btn.innerHTML = `
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        Salin
                    `;
                    btn.classList.remove('border-emerald-300', 'bg-emerald-50', 'text-emerald-700', 'dark:bg-emerald-950/20', 'dark:border-emerald-900/50');
                }, 2000);
            });
        }

        // Only Numbers in OTP Input
        document.getElementById('otp').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });

        // Loading Spinner on Submit
        document.getElementById('setupForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `
                <div class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                Memproses Setup...
            `;
        });

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