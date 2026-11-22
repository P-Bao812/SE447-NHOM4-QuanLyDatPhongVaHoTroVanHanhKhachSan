<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\TaiKhoanDangKyRequest;
use App\Http\Requests\TaiKhoanLoginRequest;
use App\Models\Tai_Khoan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Tai_KhoanController extends Controller
{
    use ApiResponseTrait;

    protected AuthController $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    /**
     * Hỗ trợ gọi register qua Tai_KhoanController (UC01).
     */
    public function register(TaiKhoanDangKyRequest $request): JsonResponse
    {
        return $this->authController->register($request);
    }

    /**
     * Hỗ trợ gọi login qua Tai_KhoanController (UC02).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->authController->login($request);
    }
}
