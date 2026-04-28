<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class AuthController extends Controller
{
    // =========================================================
    // API LOGIN (Postman / Mobile — Sanctum Token)
    // =========================================================
    public function login(Request $request)
    {
        $request->validate([
            'username'    => 'required',
            'password'    => 'required',
            'otp'         => 'required|digits:6',
            'device_name' => 'required',
        ]);

        $user = User::with('supplier')
                    ->where('username', $request->username)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        if ($user->supplier && $user->supplier->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun Supplier tidak aktif.'
            ], 403);
        }

        if (!$user->google2fa_secret) {
            return response()->json([
                'status'  => '2fa_not_setup',
                'message' => 'Silakan setup 2FA melalui website terlebih dahulu.'
            ], 403);
        }

        if (!Google2FA::verifyKey($user->google2fa_secret, $request->otp)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa.'
            ], 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'data'    => [
                'token'    => $token,
                'user'     => $user,
                'supplier' => $user->supplier
            ]
        ]);
    }

    // =========================================================
    // WEB LOGIN — Handle intent: 'login' atau 'setup'
    // =========================================================
    public function loginWeb(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Ambil data user fresh dari DB
        $user = User::with('supplier')
                    ->where('username', $request->username)
                    ->first();

        // Cek kredensial
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->withInput();
        }

        // Cek status supplier
        if ($user->supplier && $user->supplier->status !== 'active') {
            return back()
                ->with('error', 'Akun Supplier Anda sedang tidak aktif.')
                ->withInput();
        }

        // Baca secret dari DB (fresh)
        $secret = $user->fresh()->google2fa_secret;
        $otp    = $request->otp; // bisa kosong (user baru) atau diisi (user lama)

        // ── User BARU: belum punya secret, OTP kosong → arahkan ke Setup ──
        if (!$secret && !$otp) {
            // Simpan session sementara untuk proses setup
            session()->forget('2fa_secret_temp');
            session([
                '2fa_user_id'     => $user->id,
                '2fa_device_name' => $request->device_name ?? 'web',
            ]);
            return redirect()->route('2fa.setup');
        }

        // ── User sudah punya secret TAPI tidak mengisi OTP ──
        if ($secret && !$otp) {
            return back()
                ->withErrors(['otp' => 'Masukkan kode dari Google Authenticator di HP kamu.'])
                ->withInput();
        }

        // ── User belum punya secret TAPI mengisi OTP (tidak masuk akal) ──
        if (!$secret && $otp) {
            return back()
                ->withErrors(['otp' => 'Akun belum terdaftar di Google Authenticator. Kosongkan kolom kode untuk melanjutkan setup.'])
                ->withInput();
        }

        // ── LOGIN dengan OTP: verifikasi langsung ──
        if (!Google2FA::verifyKey($secret, $otp)) {
            return back()
                ->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa. Coba kode terbaru dari Google Authenticator.'])
                ->withInput();
        }

        // OTP valid → login penuh langsung
        auth()->login($user);

        return redirect()->intended('/dashboard');
    }

    // =========================================================
    // WEB 2FA SETUP — Tampilkan QR Code & Manual Key
    // =========================================================
    public function setup2fa(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        $user = User::find($userId);

        // Double check: kalau sudah punya secret di DB, tolak akses setup
        if ($user->google2fa_secret) {
            session()->forget(['2fa_user_id', '2fa_device_name', '2fa_secret_temp']);
            return redirect()->route('login')
                ->with('error', 'Akun ini sudah setup 2FA. Silakan login dengan tombol Login.');
        }

        // Reuse secret yang sudah ada di session agar OTP tidak berubah saat page refresh/back
        $secretKey = session('2fa_secret_temp');
        if (!$secretKey) {
            $secretKey = Google2FA::generateSecretKey();
            session(['2fa_secret_temp' => $secretKey]);
        }

        $qrCodeUrl = Google2FA::getQRCodeUrl(
            config('app.name', 'AmandaMart'),
            $user->username,
            $secretKey
        );

        return view('auth.2fa-setup', [
            'qrCodeUrl' => $qrCodeUrl,
            'secretKey' => $secretKey,
            'username'  => $user->username,
        ]);
    }

    // =========================================================
    // WEB 2FA SETUP CONFIRM — Verifikasi OTP lalu simpan secret
    // =========================================================
    public function confirmSetup2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId    = session('2fa_user_id');
        $secretKey = session('2fa_secret_temp');

        if (!$userId || !$secretKey) {
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Verifikasi OTP dengan secret sementara
        if (!Google2FA::verifyKey($secretKey, $request->otp)) {
            return back()->withErrors([
                'otp' => 'Kode OTP salah. Pastikan sudah scan QR Code atau masukkan kode manual dengan benar.'
            ]);
        }

        // OTP valid → simpan secret ke DB
        $user = User::find($userId);
        $user->update(['google2fa_secret' => $secretKey]);

        // Bersihkan semua session 2FA sebelum login
        session()->forget(['2fa_secret_temp', '2fa_user_id', '2fa_device_name']);

        // Login penuh
        auth()->login($user);

        return redirect('/dashboard')
            ->with('success', 'Setup 2FA berhasil! Akun Anda sekarang lebih aman.');
    }

    // =========================================================
    // WEB 2FA VERIFY — Tampilkan halaman input OTP
    // =========================================================
    public function verify2fa(Request $request)
    {
        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        $user = User::find($userId);

        // Kalau belum setup, arahkan ke setup
        if (!$user->google2fa_secret) {
            return redirect()->route('login')
                ->with('error', 'Kamu belum setup Google Authenticator. Gunakan tombol Setup 2FA.');
        }

        return view('auth.2fa-verify');
    }

    // =========================================================
    // WEB 2FA VERIFY CONFIRM — Proses OTP & login penuh
    // =========================================================
    public function confirmVerify2fa(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Ambil user fresh dari DB
        $user = User::find($userId);

        if (!$user || !$user->google2fa_secret) {
            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan. Silakan login ulang.');
        }

        // Verifikasi OTP
        if (!Google2FA::verifyKey($user->google2fa_secret, $request->otp)) {
            return back()->withErrors([
                'otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa. Coba kode terbaru dari Google Authenticator.'
            ]);
        }

        // Bersihkan session 2FA
        session()->forget(['2fa_user_id', '2fa_device_name']);

        // Login penuh
        auth()->login($user);

        return redirect()->intended('/dashboard');
    }

    // =========================================================
    // ME — Profil user (API)
    // =========================================================
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data'   => $request->user()->load('supplier')
        ]);
    }

    // =========================================================
    // LOGOUT API
    // =========================================================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    // =========================================================
    // LOGOUT WEB
    // =========================================================
    public function logoutWeb(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}