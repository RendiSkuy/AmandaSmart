<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA — AmandaMart B2B</title>
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
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .wrapper {
            width: 100%;
            max-width: 400px;
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
            width: 32px; height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }

        .brand-icon svg {
            width: 18px; height: 18px;
            fill: none; stroke: #fff;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .brand-name { font-size: 15px; font-weight: 600; letter-spacing: -0.3px; }
        .brand-sub  { font-size: 11px; color: var(--text-muted); letter-spacing: 0.6px; text-transform: uppercase; }

        /* Progress */
        .progress {
            display: flex;
            align-items: center;
            margin-bottom: 28px;
        }

        .progress-step {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; color: var(--text-muted);
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
            background: var(--accent); border-color: var(--accent); color: #fff;
        }

        .progress-step.done .step-dot {
            background: var(--success); border-color: var(--success); color: #fff;
        }

        .progress-line { flex: 1; height: 1px; background: var(--border); margin: 0 8px; }

        /* Icon shield */
        .shield-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .shield-icon {
            width: 56px; height: 56px;
            background: #F0F0EE;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
        }

        .shield-icon svg {
            width: 28px; height: 28px;
            stroke: var(--text-primary); fill: none;
            stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round;
        }

        /* Card */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 32px;
            margin-bottom: 16px;
            text-align: center;
        }

        .card-title {
            font-size: 18px; font-weight: 600;
            letter-spacing: -0.4px; margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 13px; color: var(--text-secondary);
            line-height: 1.6; margin-bottom: 28px;
        }

        /* Alert */
        .alert {
            padding: 12px 14px;
            border-radius: var(--radius);
            font-size: 13px; margin-bottom: 20px;
            line-height: 1.5;
            display: flex; align-items: flex-start; gap: 10px;
            text-align: left;
        }

        .alert-error {
            background: var(--error-bg);
            border: 1px solid #F5C6C2;
            color: var(--error);
        }

        .alert svg {
            width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        /* Timer */
        .timer-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .timer-ring {
            position: relative;
            width: 36px; height: 36px;
        }

        .timer-ring svg {
            transform: rotate(-90deg);
        }

        .timer-ring circle {
            fill: none;
            stroke-width: 3;
        }

        .timer-ring .bg { stroke: var(--border); }
        .timer-ring .progress-ring {
            stroke: var(--accent);
            stroke-dasharray: 88;
            stroke-dashoffset: 0;
            stroke-linecap: round;
            transition: stroke-dashoffset 1s linear;
        }

        .timer-num {
            position: absolute;
            inset: 0;
            display: flex; align-items: center; justify-content: center;
            font-family: 'DM Mono', monospace;
            font-size: 11px; font-weight: 500;
            color: var(--text-primary);
        }

        .timer-label {
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* OTP Input */
        .otp-wrap { margin-bottom: 8px; }

        .form-label {
            display: block;
            font-size: 11px; font-weight: 500;
            color: var(--text-secondary);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin-bottom: 8px;
            text-align: left;
        }

        .otp-input {
            width: 100%;
            height: 56px;
            background: #FAFAF8;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-family: 'DM Mono', monospace;
            font-size: 26px;
            letter-spacing: 12px;
            text-align: center;
            color: var(--text-primary);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
            padding: 0 16px;
            -webkit-appearance: none;
        }

        .otp-input:focus {
            border-color: var(--border-focus);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(26,26,26,0.06);
        }

        .otp-input.is-error { border-color: var(--error); background: var(--error-bg); }

        .field-error { font-size: 12px; color: var(--error); margin-top: 6px; text-align: left; }

        .otp-hint {
            font-size: 12px; color: var(--text-muted);
            margin-top: 8px; text-align: left;
            display: flex; align-items: center; gap: 5px;
        }

        .otp-hint svg {
            width: 12px; height: 12px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            flex-shrink: 0;
        }

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
            margin-top: 16px;
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

        /* Back link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
            margin-top: 16px;
            transition: color var(--transition);
        }

        .back-link:hover { color: var(--text-primary); }

        .back-link svg {
            width: 14px; height: 14px;
            stroke: currentColor; fill: none;
            stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 8px;
        }
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
            <span>Verifikasi 2FA</span>
        </div>
        <div class="progress-line"></div>
        <div class="progress-step">
            <div class="step-dot">3</div>
            <span>Selesai</span>
        </div>
    </div>

    {{-- Card --}}
    <div class="card">

        {{-- Shield icon --}}
        <div class="shield-wrap">
            <div class="shield-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            </div>
        </div>

        <div class="card-title">Verifikasi Identitas</div>
        <div class="card-subtitle">
            Masukkan 6 digit kode dari <strong>Google Authenticator</strong> di HP kamu untuk melanjutkan.
        </div>

        {{-- Error --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Timer --}}
        <div class="timer-wrap">
            <div class="timer-ring">
                <svg viewBox="0 0 36 36" width="36" height="36">
                    <circle class="bg" cx="18" cy="18" r="14"/>
                    <circle class="progress-ring" id="timerRing" cx="18" cy="18" r="14"/>
                </svg>
                <div class="timer-num" id="timerNum">30</div>
            </div>
            <div class="timer-label">Kode berganti setiap <strong>30 detik</strong></div>
        </div>

        {{-- Form --}}
        <form id="verifyForm" method="POST" action="{{ route('2fa.verify.confirm') }}">
            @csrf

            <div class="otp-wrap">
                <label class="form-label" for="otp">Kode Authenticator</label>
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
                @else
                    <div class="otp-hint">
                        <svg viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                        Buka Google Authenticator → cari akun <strong>AmandaMart</strong>
                    </div>
                @enderror
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <div class="spinner"></div>
                <svg class="btn-icon" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                <span class="btn-text">Masuk ke Dashboard</span>
            </button>
        </form>

        <a href="{{ route('login') }}" class="back-link">
            <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali ke Login
        </a>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} AmandaMart &mdash; Sistem B2B Internal
    </div>

</div>

<script>
    // OTP: only numbers
    document.getElementById('otp').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
    });

    // Loading state
    document.getElementById('verifyForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });

    // Countdown timer sinkron dengan TOTP 30 detik
    function startTimer() {
        const ring  = document.getElementById('timerRing');
        const num   = document.getElementById('timerNum');
        const circumference = 2 * Math.PI * 14; // r=14

        ring.style.strokeDasharray  = circumference;

        function tick() {
            const now     = Math.floor(Date.now() / 1000);
            const seconds = 30 - (now % 30);
            const offset  = circumference * (1 - seconds / 30);

            ring.style.strokeDashoffset = offset;
            num.textContent = seconds;

            // Warna merah kalau < 5 detik
            ring.style.stroke = seconds <= 5 ? '#C0392B' : 'var(--accent)';
            num.style.color   = seconds <= 5 ? '#C0392B' : 'var(--text-primary)';
        }

        tick();
        setInterval(tick, 1000);
    }

    startTimer();
</script>

</body>
</html>