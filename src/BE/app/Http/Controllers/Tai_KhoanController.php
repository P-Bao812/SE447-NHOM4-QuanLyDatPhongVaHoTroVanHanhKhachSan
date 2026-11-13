<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaiKhoanDangKyRequest;
use App\Http\Requests\TaiKhoanLoginRequest;
use App\Models\Tai_Khoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;

class Tai_KhoanController extends Controller
{
    //
    public function login(TaiKhoanLoginRequest $request)
    {
        $user = Auth::guard('tai_khoan')->attempt([
            'tenDangNhap' => $request->tenDangNhap,
            'password' => $request->password,
        ]);
        if ($user) {
            $user = Auth::guard('tai_khoan')->user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => 1,
                'message' => "Đăng nhập thành công",
                'token' => $token,
                'data' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => 0,
                'message' => "Đăng nhập thất bại. Vui lòng nhập tại email hoặc mật khẩu"
            ]);
        }
    }
    public function checkToken()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            return response()->json([
                'status' => 1,
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'status' => 0,
                "message" => 'Token hết hạng'
            ], 200);
        }
    }
    public function logout()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json([
                'status' => 1,
                'message' => "Đăng xuất thành công"
            ], 200);
        } else {
            return response()->json([
                'status' => 0,
                'message' => "Đăng xuất thất bại"
            ], 401);
        }
    }
    public function logoutAll()
    {
        $user = Auth::guard('sanctum')->user();
        if ($user) {
            $user->tokens()->delete();
            return response()->json([
                'status' => 1,
                'message' => "Đăng xuất thành công"
            ], 200);
        } else {
            return response()->json([
                'status' => 0,
                'message' => "Đăng xuất thất bại"
            ], 401);
        }
    }
    public function dangKy(TaiKhoanDangKyRequest $request)
    {
        $taikhoan = Tai_Khoan::create([
            "tenDangNhap" => $request->tenDangNhap,
            "password" => bcrypt($request->mat_khau),
            "trangThai" => "Hoạt động"
        ]);
        return response()->json([
            'status' => 1,
            'message' => "Đăng ký tài khoản thành công",
            'data' => $taikhoan
        ], 200);
    }
}
