<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Dat_PhongController;
use App\Http\Controllers\Khach_HangController;
use App\Http\Controllers\Nhan_VienController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\Tai_KhoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - CDIO4 Hotel Management System
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // 6.1. Phân hệ Xác thực & Tài khoản (Public Routes)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Phân hệ Xác thực & Tài khoản (Protected Routes - Bearer Token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // 6.2. Phân hệ Loại phòng & Phòng
        Route::get('/rooms', [PhongController::class, 'index']);
        Route::get('/customers', [Khach_HangController::class, 'index']);

        // 6.3. Phân hệ Đặt phòng & Check-in/Check-out (Bookings & Front Desk)
        Route::get('/bookings', [Dat_PhongController::class, 'index']);
        Route::post('/bookings', [Dat_PhongController::class, 'store']);
        Route::get('/bookings/{id}', [Dat_PhongController::class, 'show']);
        Route::post('/bookings/{id}/cancellation-request', [Dat_PhongController::class, 'requestCancellation']);
        Route::put('/bookings/{id}/cancellation-status', [Dat_PhongController::class, 'updateCancellationStatus']);
    });
});

// Legacy / Framework fallback
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
