<?php

use App\Http\Controllers\Nhan_VienController;
use App\Http\Controllers\Tai_KhoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('');
