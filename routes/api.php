<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\LPBController;
use App\Http\Controllers\Api\ServiceLevelController;
use App\Http\Controllers\Api\TTFController;
use App\Http\Controllers\Api\ReturController;
use App\Http\Controllers\Api\VRSController;
use App\Http\Controllers\Api\NotificationController;

// 1. Public Routes
Route::post('/login', [AuthController::class, 'login']);

// 2. Protected Routes (Wajib Login Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // AUTH & PROFILE GLOBAL
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user-detail', [AuthController::class, 'userDetail']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ── DATA AKSES PORTAL SUPPLIER (b2b.amanda.id) ──
    // Fitur-fitur yang boleh diakses oleh vendor luar
    Route::middleware('role:supplier')->group(function () {
        Route::get('/supplier/purchase-orders', [PurchaseOrderController::class, 'index']); 
        Route::post('/supplier/purchase-orders/{id}/offers', [PurchaseOrderController::class, 'submitOffer']); // Kirim Harga per PCS
        Route::get('/supplier/vrs/booking', [VRSController::class, 'index']);
        Route::post('/supplier/vrs/booking', [VRSController::class, 'createBooking']);
    });

    // ── DATA AKSES INTERNAL MERCHANDISER (md.amanda.id) ──
    // Fitur-fitur rahasia yang HANYA boleh diakses internal MD & Gudang
    Route::middleware('role:md')->group(function () {
        Route::post('/md/purchase-orders/generate-auto', [PurchaseOrderController::class, 'generateAutoPO']); // Pemicu manual PB
        Route::get('/md/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
        Route::get('/md/purchase-orders/{id}/compare', [PurchaseOrderController::class, 'compareOffers']);
        Route::post('/md/lpb', [LPBController::class, 'store']); // Input LPB & Retur dari Gudang
        Route::get('/md/lpb', [LPBController::class, 'index']);
        Route::get('/md/retur', [ReturController::class, 'index']);
        Route::get('/md/ttf', [TTFController::class, 'index']);
        Route::post('/md/ttf/generate', [TTFController::class, 'store']);
        Route::get('/md/dashboard/service-level', [ServiceLevelController::class, 'index']); // Nilai Rapor Vendor
    });
});