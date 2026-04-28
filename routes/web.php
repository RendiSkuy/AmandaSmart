<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// ── Login ──
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.submit');

// ── 2FA Routes (wajib ada session 2fa_user_id) ──
Route::middleware(['2fa.session'])->group(function () {
    Route::get('/2fa/setup',   [AuthController::class, 'setup2fa'])->name('2fa.setup');
    Route::post('/2fa/setup',  [AuthController::class, 'confirmSetup2fa'])->name('2fa.setup.confirm');
    Route::get('/2fa/verify',  [AuthController::class, 'verify2fa'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'confirmVerify2fa'])->name('2fa.verify.confirm');
});

// ── Protected ──
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});