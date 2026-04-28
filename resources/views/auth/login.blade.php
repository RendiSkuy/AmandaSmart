<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — AmandaMart B2B</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* Force light mode — cegah dark mode browser override warna */
        :root {
            color-scheme: light;
            --bg: #F7F6F3;
            --surface: #FFFFFF;
            --border: #E4E2DC;
            --border-focus: #1A1A1A;
            --text-primary: #1A1A1A;
            --text-secondary: #7A7870;
            --text-muted: #B0AEA8;
            --accent: #1A1A1A;
            --accent-hover: #333333;
            --error: #C0392B;
            --error-bg: #FDF2F2;
            --success: #27AE60;
            --radius: 6px;
            --transition: 200ms ease;
        }

        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Brand ── */
        .brand { margin-bottom: 40px; }

        .brand-mark {
            display: flex; align-items: center; gap: 10px; margin-bottom: 8px;
        }

        .brand-icon {
            width: 32px; height: 32px;
            background: var(--accent); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .brand-icon svg {
            width: 18px; height: 18px; fill: none; stroke: #fff;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .brand-name { font-size: 16px; font-weight: 600; letter-spacing: -0.3px; color: var(--text-primary); }
        .brand-sub  { font-size: 11px; color: var(--text-muted); letter-spacing: 0.8px; text-transform: uppercase; }
        .brand-tagline { font-size: 13px; color: var(--text-secondary); }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 32px;
        }

        .card-title {
            font-size: 20px; font-weight: 600;
            letter-spacing: -0.5px; margin-bottom: 4px;
            color: var(--text-primary);
        }

        .card-subtitle {
            font-size: 13px; color: var(--text-secondary);
            margin-bottom: 28px; line-height: 1.5;
        }

        /* ── OTP Section ── */
        .otp-section {
            border-top: 1px solid var(--border);
            padding-top: 16px;
            margin-top: 4px;
        }

        .otp-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .otp-badge {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 20px;
            background: #F0F0EE;
            color: var(--text-muted);
        }

        .otp-input-mono {
            width: 100%; height: 44px;
            padding: 0 12px;
            background: #FAFAF8;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Mono', monospace;
            font-size: 20px;
            letter-spacing: 8px;
            text-align: center;
            color: var(--text-primary);
            outline: none;
            transition: border-color var(--transition), background var(--transition), box-shadow var(--transition);
            -webkit-appearance: none;
        }

        .otp-input-mono::placeholder {
            font-size: 14px;
            letter-spacing: 0;
            color: var(--text-muted);
            font-family: 'DM Sans', sans-serif;
        }

        .otp-input-mono:focus {
            border-color: var(--border-focus);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.06);
        }

        .otp-input-mono.is-error { border-color: var(--error); background: var(--error-bg); }

        .otp-hint-row {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin-top: 8px;
            font-size: 11.5px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .otp-hint-row svg {
            width: 12px; height: 12px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0; margin-top: 1px;
        }

        /* ── Alert ── */
        .alert {
            padding: 12px 14px; border-radius: var(--radius);
            font-size: 13px; margin-bottom: 20px; line-height: 1.5;
            display: flex; align-items: flex-start; gap: 10px;
        }

        .alert-error   { background: var(--error-bg); border: 1px solid #F5C6C2; color: var(--error); }
        .alert-success { background: #F0FBF4; border: 1px solid #A8E6C0; color: var(--success); }

        .alert svg {
            width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        /* ── Form ── */
        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block; font-size: 12px; font-weight: 500;
            color: var(--text-secondary); letter-spacing: 0.4px;
            text-transform: uppercase; margin-bottom: 6px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); pointer-events: none;
            display: flex; align-items: center;
        }

        .input-icon svg {
            width: 15px; height: 15px; stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .form-input {
            width: 100%; height: 44px; padding: 0 12px 0 38px;
            background: #FAFAF8; border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif; font-size: 14px;
            color: var(--text-primary);
            outline: none;
            transition: border-color var(--transition), background var(--transition), box-shadow var(--transition);
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input::placeholder { color: var(--text-muted); }

        .form-input:focus {
            border-color: var(--border-focus);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(26, 26, 26, 0.06);
        }

        .form-input.is-error { border-color: var(--error); background: var(--error-bg); }
        .form-input.has-toggle { padding-right: 42px; }

        .field-error { font-size: 12px; color: var(--error); margin-top: 5px; }

        /* Toggle password */
        .toggle-password {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: 4px;
            display: flex; align-items: center;
            transition: color var(--transition);
        }

        .toggle-password:hover { color: var(--text-secondary); }

        .toggle-password svg {
            width: 15px; height: 15px; stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        /* ── Divider ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0;
            font-size: 11px; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }

        /* ── Buttons ── */
        .btn-group { display: flex; flex-direction: column; gap: 10px; }

        .btn {
            width: 100%; height: 46px;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; font-weight: 500;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background var(--transition), box-shadow var(--transition), border-color var(--transition), opacity var(--transition);
            position: relative;
            overflow: hidden;
            /* Pastikan tombol selalu visible */
            min-height: 46px;
        }

        .btn:active { transform: scale(0.99); }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Primary — Login */
        .btn-primary {
            background: var(--accent);
            color: #FFFFFF !important;
            border: none;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--accent-hover);
            box-shadow: 0 4px 12px rgba(26, 26, 26, 0.2);
        }

        /* Secondary — Setup 2FA */
        .btn-secondary {
            background: #FFFFFF;
            color: var(--text-primary) !important;
            border: 1.5px solid var(--border);
        }

        .btn-secondary:hover:not(:disabled) {
            border-color: var(--accent);
            background: #F7F6F3;
        }

        /* Icons & text inside btn */
        .btn-icon {
            width: 15px; height: 15px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0;
        }

        .btn-text { white-space: nowrap; }

        /* Spinner */
        .spinner {
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2px solid transparent;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }

        /* Spinner warna berdasarkan jenis tombol */
        .btn-primary .spinner {
            border-color: rgba(255, 255, 255, 0.3);
            border-top-color: #FFFFFF;
        }

        .btn-secondary .spinner {
            border-color: rgba(26, 26, 26, 0.15);
            border-top-color: var(--accent);
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Loading state */
        .btn.loading .spinner  { display: block; }
        .btn.loading .btn-text { display: none; }
        .btn.loading .btn-icon { display: none; }

        /* ── Helper text ── */
        .helper {
            font-size: 12px; color: var(--text-muted);
            text-align: center; margin-top: 12px;
            line-height: 1.6;
        }

        .helper strong { color: var(--text-secondary); }

        /* ── Footer ── */
        .footer {
            text-align: center; margin-top: 24px;
            font-size: 12px; color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- Brand --}}
    <div class="brand">
        <div class="brand-mark">
            <div class="brand-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <div>
                <div class="brand-name">AmandaMart</div>
                <div class="brand-sub">B2B Supplier Portal</div>
            </div>
        </div>
        <div class="brand-tagline">Selamat datang kembali.</div>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-title">Masuk ke Akun</div>
        <div class="card-subtitle">Masukkan username, password, dan kode Google Authenticator.</div>

        {{-- Alerts --}}
        @if (session('error'))
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Form --}}
        <form id="loginForm" method="POST" action="{{ route('login.submit') }}" autocomplete="off">
            @csrf
            <input type="hidden" name="device_name" id="device_name">

            {{-- Username --}}
            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input {{ $errors->has('username') ? 'is-error' : '' }}"
                        placeholder="Masukkan username"
                        value="{{ old('username') }}"
                        autocomplete="off"
                        autofocus
                        required
                    >
                </div>
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input has-toggle"
                        placeholder="Masukkan password"
                        autocomplete="off"
                        required
                    >
                    <button type="button" class="toggle-password" id="toggleBtn" title="Tampilkan password">
                        <svg id="eyeIcon" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- OTP Field --}}
            <div class="otp-section">
                <div class="otp-label-row">
                    <label class="form-label" for="otp" style="margin-bottom:0">Kode Authenticator</label>
                    <span class="otp-badge">Google Authenticator</span>
                </div>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    class="otp-input-mono {{ $errors->has('otp') ? 'is-error' : '' }}"
                    placeholder="Kosongkan jika pertama kali setup"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code"
                    value="{{ old('otp') }}"
                >
                <div class="otp-hint-row">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>
                        Sudah punya Google Authenticator? Isi kode 6 digit dari aplikasi.<br>
                        <strong>Pertama kali masuk?</strong> Biarkan kosong — kamu akan diarahkan ke halaman setup.
                    </span>
                </div>
            </div>

            {{-- Tombol Masuk --}}
            <div class="btn-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary" id="btnLogin">
                    <div class="spinner"></div>
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span class="btn-text">Masuk</span>
                </button>
            </div>

        </form>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} AmandaMart &mdash; Sistem B2B Internal
    </div>

</div>

<script>
    // ── Set device name otomatis ──
    document.getElementById('device_name').value =
        (navigator.userAgent || 'unknown').substring(0, 50) +
        '_' + screen.width + 'x' + screen.height;

    // ── Reset tombol saat halaman dimuat / back button ──
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.btn').forEach(function (btn) {
            btn.classList.remove('loading');
            btn.disabled = false;
        });
    });

    // ── OTP: hanya angka, maks 6 digit ──
    document.getElementById('otp').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
    });

    // ── Submit form ──
    document.getElementById('loginForm').addEventListener('submit', function () {
        var btn = document.getElementById('btnLogin');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    // ── Toggle show/hide password ──
    document.getElementById('toggleBtn').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            `;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            `;
        }
    });
</script>

</body>
</html>