<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\LPBController;
use App\Http\Controllers\Api\ServiceLevelController;
use App\Http\Controllers\Api\TtfController;
use App\Http\Controllers\Api\ReturController;
use App\Http\Controllers\Api\VRSController;

// 1. Public Routes (Bisa diakses tanpa login)
Route::post('/login', [AuthController::class, 'login']);

// 2. Protected Routes (Harus login pakai token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Purchase Order
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{id}', [PurchaseOrderController::class, 'show']);
    Route::post('/purchase-orders/{id}/read', [PurchaseOrderController::class, 'markAsRead']);

    // LPB
    Route::get('/lpb', [LPBController::class, 'index']);
    Route::get('/lpb/{id}', [LPBController::class, 'show']);

    // Service Level
    Route::get('/dashboard/service-level', [ServiceLevelController::class, 'index']);

    // TTF
    Route::get('/ttf', [TTFController::class, 'index']);
    Route::post('/ttf/generate', [TTFController::class, 'store']);

    // Retur
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/retur', [ReturController::class, 'index']);
    Route::get('/retur/{id}', [ReturController::class, 'show']);
    });

    // VRS
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/vrs/profile', [VRSController::class, 'index']);
    Route::put('/vrs/profile', [VRSController::class, 'update']);
    });
});
