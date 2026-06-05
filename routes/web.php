<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// ── 1. JALUR LOGIN PORTAL SUPPLIER (b2b.amanda.id) ──
Route::get('/login-supplier', fn() => view('auth.login-supplier'))->name('login.supplier');
Route::post('/login-supplier', [AuthController::class, 'loginWeb'])->name('login.supplier.submit');

// ── 2. JALUR LOGIN INTERNAL MD + GOOGLE 2FA (md.amanda.id) ──
Route::get('/login-md', fn() => view('auth.login-md'))->name('login.md');
Route::post('/login-md', [AuthController::class, 'loginWeb'])->name('login.md.submit');

// ── 2FA Verifikasi (Hanya Tim MD yang Melewati Ini) ──
Route::middleware(['2fa.session'])->group(function () {
    Route::get('/2fa/setup',   [AuthController::class, 'setup2fa'])->name('2fa.setup');
    Route::post('/2fa/setup',  [AuthController::class, 'confirmSetup2fa'])->name('2fa.setup.confirm');
    Route::get('/2fa/verify',  [AuthController::class, 'verify2fa'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'confirmVerify2fa'])->name('2fa.verify.confirm');
});

// ── Fallback Login Route (to avoid Route [login] not defined exception) ──
Route::get('/login', fn() => redirect('/'))->name('login');

use App\Http\Controllers\WebDashboardController;

// ── Protected Web Dashboard ──
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

    // MD Operations
    Route::post('/dashboard/po/generate', [WebDashboardController::class, 'generateAutoPO'])->name('dashboard.po.generate');
    Route::post('/dashboard/offers/approve', [WebDashboardController::class, 'approveOffer'])->name('dashboard.offers.approve');
    Route::post('/dashboard/offers/approve-quick', [WebDashboardController::class, 'approveOfferQuick'])->name('dashboard.offers.approve_quick');
    Route::post('/dashboard/lpb/store', [WebDashboardController::class, 'storeLpb'])->name('dashboard.lpb.store');
    Route::post('/dashboard/ttf/generate', [WebDashboardController::class, 'generateTtf'])->name('dashboard.ttf.generate');

    // Detail & Print Views
    Route::get('/dashboard/lpb/{id}/print', [WebDashboardController::class, 'showLpb'])->name('dashboard.lpb.print');
    Route::get('/dashboard/ttf/{id}/print', [WebDashboardController::class, 'showTtf'])->name('dashboard.ttf.print');

    // Supplier Operations
    Route::post('/dashboard/offers/submit', [WebDashboardController::class, 'submitOffer'])->name('dashboard.offers.submit');
    Route::post('/dashboard/vrs/booking', [WebDashboardController::class, 'createVrsBooking'])->name('dashboard.vrs.booking');
    Route::post('/dashboard/profile/update', [WebDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});