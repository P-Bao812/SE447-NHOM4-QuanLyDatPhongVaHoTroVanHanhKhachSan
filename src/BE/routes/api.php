<?php

use App\Http\Controllers\Nhan_VienController;
use App\Http\Controllers\Tai_KhoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('');
Route::post('login', [Tai_KhoanController::class, 'login']);
Route::get('check-token', [Tai_KhoanController::class, 'checkToken']);
Route::get('logout', [Tai_KhoanController::class, 'logout']);
Route::get('logout-all', [Tai_KhoanController::class, 'logoutAll']);
Route::post('dang-ky',[Tai_KhoanController::class,'dangKy']);
