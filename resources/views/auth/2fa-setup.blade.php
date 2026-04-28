<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA — AmandaMart B2B</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
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
            --success-bg: #F0FBF4;
            --warning: #D35400;
            --warning-bg: #FEF9F0;
            --radius: 6px;
            --transition: 200ms ease;
        }

        html, body { height: 100%; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 24px;
        }

        .wrapper {
            width: 100%;
            max-width: 480px;
            animation: fadeUp 0.4s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-icon svg {
            width: 18px; height: 18px;
            fill: none; stroke: #fff;
            stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .brand-name { font-size: 15px; font-weight: 600; letter-spacing: -0.3px; }
        .brand-sub  { font-size: 11px; color: var(--text-muted); letter-spacing: 0.6px; text-transform: uppercase; }

        /* Progress bar */
        .progress {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 28px;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .progress-step.active { color: var(--text-primary); }
        .progress-step.done   { color: var(--success); }

        .step-dot {
            width: 24px; height: 24px;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600;
            color: var(--text-muted);
            background: var(--surface);
            flex-shrink: 0;
        }

        .progress-step.active .step-dot {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .progress-step.done .step-dot {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .progress-line {
            flex: 1;
            height: 1px;
            background: var(--border);
            margin: 0 8px;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 32px;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 18px; font-weight: 600;
            letter-spacing: -0.4px;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 13px; color: var(--text-secondary);
            line-height: 1.6; margin-bottom: 24px;
        }

        /* Alert */
        .alert {
            padding: 12px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-warning {
            background: var(--warning-bg);
            border: 1px solid #F5CBA7;
            color: var(--warning);
        }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid #F5C6C2;
            color: var(--error);
        }

        .alert svg {
            width: 15px; height: 15px;
            flex-shrink: 0; margin-top: 1px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        /* Tabs */
        .tabs {
            display: flex;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13px;
            font-weight: 500;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .tab-btn svg {
            width: 14px; height: 14px;
            stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .tab-btn:not(:last-child) {
            border-right: 1px solid var(--border);
        }

        .tab-btn.active {
            background: var(--accent);
            color: #fff;
        }

        /* Tab content */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* QR Code */
        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 24px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .qr-image {
            width: 180px; height: 180px;
            border: 4px solid #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .qr-caption {
            font-size: 12px;
            color: var(--text-secondary);
            text-align: center;
            line-height: 1.5;
        }

        /* Manual key */
        .manual-container {
            padding: 20px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 20px;
        }

        .manual-steps {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-bottom: 20px;
        }

        .manual-steps li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .step-num {
            width: 22px; height: 22px;
            background: var(--accent);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .key-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .key-value {
            font-family: 'DM Mono', monospace;
            font-size: 17px;
            letter-spacing: 4px;
            color: var(--text-primary);
            font-weight: 500;
            word-break: break-all;
        }

        .copy-btn {
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 6px 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all var(--transition);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .copy-btn:hover { border-color: var(--accent); color: var(--accent); }
        .copy-btn.copied { border-color: var(--success); color: var(--success); }

        .copy-btn svg {
            width: 12px; height: 12px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        .key-note {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .key-note svg {
            width: 11px; height: 11px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0;
        }

        /* App download */
        .app-links {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .app-link {
            flex: 1;
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all var(--transition);
        }

        .app-link:hover { border-color: var(--accent); color: var(--accent); }

        .app-link svg {
            width: 13px; height: 13px;
            stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        /* OTP Confirm section */
        .confirm-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .confirm-sub {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .form-group { margin-bottom: 0; }

        .form-label {
            display: block;
            font-size: 11px; font-weight: 500;
            color: var(--text-secondary);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .otp-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: #FAFAF8;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Mono', monospace;
            font-size: 22px;
            letter-spacing: 10px;
            text-align: center;
            color: var(--text-primary);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
            -webkit-appearance: none;
        }

        .otp-input:focus {
            border-color: var(--border-focus);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(26,26,26,0.06);
        }

        .otp-input.is-error { border-color: var(--error); background: var(--error-bg); }

        .field-error { font-size: 12px; color: var(--error); margin-top: 5px; }

        /* Submit */
        .btn-submit {
            width: 100%;
            height: 44px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: var(--radius);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px; font-weight: 500;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px;
            margin-top: 14px;
            transition: background var(--transition), box-shadow var(--transition);
        }

        .btn-submit:hover { background: var(--accent-hover); box-shadow: 0 4px 12px rgba(26,26,26,0.15); }
        .btn-submit:active { transform: scale(0.99); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-submit svg {
            width: 15px; height: 15px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        .spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-submit.loading .spinner { display: block; }
        .btn-submit.loading .btn-text,
        .btn-submit.loading .btn-icon { display: none; }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 20px;
        }

        .footer a {
            color: var(--text-secondary);
            text-decoration: none;
        }

        .footer a:hover { color: var(--text-primary); }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- Brand --}}
    <div class="brand">
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

    {{-- Progress --}}
    <div class="progress">
        <div class="progress-step done">
            <div class="step-dot">
                <svg viewBox="0 0 24 24" width="12" height="12"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span>Login</span>
        </div>
        <div class="progress-line"></div>
        <div class="progress-step active">
            <div class="step-dot">2</div>
            <span>Setup 2FA</span>
        </div>
        <div class="progress-line"></div>
        <div class="progress-step">
            <div class="step-dot">3</div>
            <span>Selesai</span>
        </div>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-title">Setup Google Authenticator</div>
        <div class="card-subtitle">
            Halo, <strong>{{ $username }}</strong>! Ini adalah pertama kalinya kamu login.
            Sambungkan akun ke Google Authenticator untuk mengamankan akses kamu.
        </div>

        {{-- Alert error --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Belum punya Google Authenticator? --}}
        <div class="alert alert-warning">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <strong>Belum punya Google Authenticator?</strong><br>
                Download dulu aplikasinya di HP kamu:
                <div class="app-links">
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="app-link">
                        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/><path d="M8 12l4-7 4 7"/><path d="M8 12h8"/></svg>
                        Android
                    </a>
                    <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="app-link">
                        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/><path d="M9 12l2 2 4-4"/></svg>
                        iPhone
                    </a>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('qr', this)">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/></svg>
                Scan QR Code
            </button>
            <button class="tab-btn" onclick="switchTab('manual', this)">
                <svg viewBox="0 0 24 24"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                Kode Manual
            </button>
        </div>

        {{-- Tab QR Code --}}
        <div class="tab-content active" id="tab-qr">
            <div class="qr-container">
                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}"
                    alt="QR Code 2FA"
                    class="qr-image"
                >
                <div class="qr-caption">
                    Buka Google Authenticator → Tap <strong>"+"</strong> → <strong>"Scan QR Code"</strong><br>
                    Arahkan kamera ke gambar di atas
                </div>
            </div>
        </div>

        {{-- Tab Manual Key --}}
        <div class="tab-content" id="tab-manual">
            <div class="manual-container">
                <ul class="manual-steps">
                    <li>
                        <div class="step-num">1</div>
                        <span>Buka aplikasi <strong>Google Authenticator</strong> di HP kamu</span>
                    </li>
                    <li>
                        <div class="step-num">2</div>
                        <span>Tap tombol <strong>"+"</strong> di pojok kanan bawah</span>
                    </li>
                    <li>
                        <div class="step-num">3</div>
                        <span>Pilih <strong>"Enter a setup key"</strong></span>
                    </li>
                    <li>
                        <div class="step-num">4</div>
                        <span>Isi <strong>Account name</strong> dengan nama kamu, lalu masukkan kode di bawah ini ke kolom <strong>Key</strong></span>
                    </li>
                </ul>

                <div class="key-box">
                    <div class="key-value" id="secretKeyDisplay">
                        {{ implode(' ', str_split($secretKey, 4)) }}
                    </div>
                    <button class="copy-btn" id="copyBtn" onclick="copyKey()">
                        <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                        Salin
                    </button>
                </div>

                <div class="key-note">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Jangan bagikan kode ini ke siapapun termasuk admin
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Konfirmasi OTP --}}
        <div class="confirm-title">Konfirmasi Koneksi</div>
        <div class="confirm-sub">
            Setelah scan QR Code atau masukkan kode manual, masukkan 6 digit kode yang muncul di Google Authenticator untuk memastikan setup berhasil.
        </div>

        <form id="setupForm" method="POST" action="{{ route('2fa.setup.confirm') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="otp">Kode dari Google Authenticator</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    class="otp-input @error('otp') is-error @enderror"
                    placeholder="000000"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    autocomplete="one-time-code"
                    autofocus
                    required
                >
                @error('otp')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <div class="spinner"></div>
                <svg class="btn-icon" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span class="btn-text">Konfirmasi & Aktifkan 2FA</span>
            </button>
        </form>
    </div>

    <div class="footer">
        Butuh bantuan? Hubungi admin &mdash;
        <a href="{{ route('login') }}">Kembali ke Login</a>
    </div>

</div>

<script>
    // Switch tab
    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        btn.classList.add('active');
    }

    // Copy key
    function copyKey() {
        const key = '{{ $secretKey }}';
        navigator.clipboard.writeText(key).then(() => {
            const btn = document.getElementById('copyBtn');
            btn.classList.add('copied');
            btn.innerHTML = `
                <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Tersalin!
            `;
            setTimeout(() => {
                btn.classList.remove('copied');
                btn.innerHTML = `
                    <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Salin
                `;
            }, 2000);
        });
    }

    // OTP: only numbers
    document.getElementById('otp').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
    });

    // Loading state
    document.getElementById('setupForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
</script>

</body>
</html>