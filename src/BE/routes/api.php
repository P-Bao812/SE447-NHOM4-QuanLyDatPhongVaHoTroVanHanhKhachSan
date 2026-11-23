<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Dat_PhongController;
use App\Http\Controllers\Dich_VuController;
use App\Http\Controllers\Hoa_DonController;
use App\Http\Controllers\Khach_HangController;
use App\Http\Controllers\Loai_PhongController;
use App\Http\Controllers\Nhan_VienController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Tai_KhoanController;
use App\Http\Controllers\Thanh_ToanController;
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
        Route::match(['put', 'patch'], '/auth/profile', [AuthController::class, 'updateProfile']);

        // 6.2. Phân hệ Loại phòng & Phòng
        Route::get('/rooms', [PhongController::class, 'index']);
        Route::get('/rooms/map', [PhongController::class, 'map']);
        Route::get('/rooms/search', [PhongController::class, 'index']);
        Route::post('/rooms', [PhongController::class, 'store']);
        Route::get('/rooms/{id}', [PhongController::class, 'show'])->whereNumber('id');
        Route::put('/rooms/{id}', [PhongController::class, 'update'])->whereNumber('id');
        Route::put('/rooms/{id}/status', [PhongController::class, 'updateStatus'])->whereNumber('id');
        Route::delete('/rooms/{id}', [PhongController::class, 'destroy'])->whereNumber('id');
        Route::get('/room-types', [Loai_PhongController::class, 'index']);

        // 6.1. Phân hệ Khách hàng & Hồ sơ (Customers - UC08)
        Route::get('/customers', [Khach_HangController::class, 'index']);
        Route::post('/customers', [Khach_HangController::class, 'store']);
        Route::get('/customers/{id}', [Khach_HangController::class, 'show'])->whereNumber('id');
        Route::put('/customers/{id}', [Khach_HangController::class, 'update'])->whereNumber('id');
        Route::delete('/customers/{id}', [Khach_HangController::class, 'destroy'])->whereNumber('id');
        Route::get('/customers/{id}/stay-history', [Khach_HangController::class, 'stayHistory'])->whereNumber('id');

        // 6.3. Phân hệ Đặt phòng & Check-in/Check-out (Bookings & Front Desk)
        Route::get('/bookings', [Dat_PhongController::class, 'index']);
        Route::post('/bookings', [Dat_PhongController::class, 'store']);
        Route::get('/bookings/{id}', [Dat_PhongController::class, 'show']);
        Route::put('/bookings/{id}', [Dat_PhongController::class, 'update']);
        Route::put('/bookings/{id}/status', [Dat_PhongController::class, 'updateStatus']);
        Route::post('/bookings/{id}/cancellation-request', [Dat_PhongController::class, 'requestCancellation']);
        Route::put('/bookings/{id}/cancellation-status', [Dat_PhongController::class, 'updateCancellationStatus']);
        Route::get('/bookings/{id}/stay-info', [Dat_PhongController::class, 'stayInfo'])->whereNumber('id');
        Route::post('/bookings/{id}/check-in', [Dat_PhongController::class, 'checkIn'])->whereNumber('id');
        Route::post('/bookings/{id}/check-out', [Dat_PhongController::class, 'checkOut'])->whereNumber('id');

        // 6.6. Phân hệ Hóa đơn & Thanh toán (Invoices & Payments - UC07)
        Route::get('/invoices', [Hoa_DonController::class, 'index']);
        Route::get('/invoices/{id}', [Hoa_DonController::class, 'show'])->whereNumber('id');
        Route::post('/invoices/{id}/adjustments', [Hoa_DonController::class, 'addAdjustment'])->whereNumber('id');
        Route::post('/payments', [Thanh_ToanController::class, 'store']);

        // 6.4. Phân hệ Dịch vụ & Minibar (Services - UC17)
        Route::get('/services', [Dich_VuController::class, 'index']);
        Route::post('/services', [Dich_VuController::class, 'store']);
        Route::get('/services/{id}', [Dich_VuController::class, 'show'])->whereNumber('id');
        Route::put('/services/{id}', [Dich_VuController::class, 'update'])->whereNumber('id');
        Route::put('/services/{id}/status', [Dich_VuController::class, 'updateStatus'])->whereNumber('id');
        Route::delete('/services/{id}', [Dich_VuController::class, 'destroy'])->whereNumber('id');

        // 6.7. Phân hệ Báo cáo & Thống kê (Reports & Dashboard - UC19 / UC26 / FR26)
        Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
        Route::get('/reports/revenue', [ReportController::class, 'revenue']);
        Route::get('/reports/bookings', [ReportController::class, 'bookings']);
        Route::get('/reports/occupancy', [ReportController::class, 'occupancy']);
    });
});

// Legacy / Framework fallback
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
