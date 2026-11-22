<?php

namespace App\Http\Controllers;

use App\Models\Khach_Hang;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Khach_HangController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách khách hàng (cho Lễ tân / Quản lý).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Khach_Hang::query();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('hoTen', 'like', "%{$q}%")
                    ->orWhere('soDienThoai', 'like', "%{$q}%")
                    ->orWhere('cccd', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $customers = $query->orderBy('id', 'desc')->get();

        return $this->successResponse($customers, 'Lấy danh sách khách hàng thành công.');
    }
}
