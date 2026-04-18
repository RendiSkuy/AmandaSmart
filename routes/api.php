<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\LPBController;
use App\Http\Controllers\Api\ServiceLevelController;
use App\Http\Controllers\Api\TtfController; // Pastikan huruf besar kecilnya sama dengan nama file
use App\Http\Controllers\Api\ReturController;
use App\Http\Controllers\Api\VRSController;
use App\Http\Controllers\Api\NotificationController;

// 1. Public Routes
Route::post('/login', [AuthController::class, 'login']);

// 2. Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // AUTH & PROFILE
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // PURCHASE ORDER (PO)
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
    Route::post('/purchase-orders/generate-auto', [PurchaseOrderController::class, 'generateAutoPO']);
    Route::put('/purchase-order-items/{id}', [PurchaseOrderController::class, 'updateItem']); // Penamaan seragam
    Route::post('/purchase-orders/{id}/read', [PurchaseOrderController::class, 'markAsRead']);

    // LOGISTIK & PENERIMAAN (LPB & RETUR)
    Route::get('/lpb', [LPBController::class, 'index']);
    Route::get('/lpb/{id}', [LPBController::class, 'show']);
    Route::get('/retur', [ReturController::class, 'index']);
    Route::get('/retur/{id}', [ReturController::class, 'show']);

    // VEHICLE RESERVATION SYSTEM (VRS)
    Route::get('/vrs/booking', [VRSController::class, 'index']);
    Route::post('/vrs/booking', [VRSController::class, 'createBooking']);
    Route::get('/vrs/profile', [VRSController::class, 'showProfile']); // Contoh pembeda dengan index booking
    Route::put('/vrs/profile', [VRSController::class, 'updateProfile']);

    // FINANCE (TTF)
    Route::get('/ttf', [TtfController::class, 'index']);
    Route::post('/ttf/generate', [TtfController::class, 'store']);

    // DASHBOARD & NOTIFICATIONS
    Route::get('/dashboard/service-level', [ServiceLevelController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});