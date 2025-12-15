<?php

use App\Http\Controllers\AdController;
use App\Http\Controllers\AdGenerationController;
use App\Http\Controllers\AdScheduleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\XenditController;
use App\Models\AdGeneration;

// Packages
Route::get("/packages", [PackageController::class, 'index']);


Route::middleware('auth:sanctum')->group(function () {
    // Order
    Route::post('/payment/invoice', [XenditController::class, 'createInvoice']);
    Route::get('/orders', [OrdersController::class, 'index']);

    // Ads
    // List semua ads milik user
    Route::get('/ads', [AdController::class, 'index']);
    // Buat ads baru
    Route::post('/ads', [AdController::class, 'store']);
    // Lihat detail 1 ads
    Route::get('/ads/{id}', [AdController::class, 'show']);
    // Update ads
    Route::put('/ads/{id}', [AdController::class, 'update']);
    // Hapus ads
    Route::delete('/ads/{id}', [AdController::class, 'destroy']);
});

// Xendit Webhook
Route::post('/payment/webhook', [XenditController::class,'webhook']);

// Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::middleware('auth:sanctum')->post('/logout', 'logout');
    Route::middleware('auth:sanctum')->get('/user', 'getUser');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/ads', [AdController::class, 'store']);
    Route::post('/ads/{ads}/generate', [AdController::class, 'generate']);
    Route::post('/ads/{ads}/schedule', [AdController::class, 'schedule']);

    Route::get('/ads-generations', [AdGenerationController::class, 'index']);
    Route::get('/ads-generations/{id}', [AdGenerationController::class, 'show']);
    Route::get('/ads/{ads}/generations', [AdGenerationController::class, 'byAd']);
    Route::get('/ads/{ads}/generations', [AdGenerationController::class, 'byAd']);
  // Ad Schedules ✨
    Route::get('/ad-schedules', [AdScheduleController::class, 'index']);
    Route::get('/ad-schedules/{id}', [AdScheduleController::class, 'show']);
    Route::get('/ads/{ads}/schedules', [AdScheduleController::class, 'byAd']);
});

// Route::get('/ads/schedules', [AdScheduleController::class, 'index']);


// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
