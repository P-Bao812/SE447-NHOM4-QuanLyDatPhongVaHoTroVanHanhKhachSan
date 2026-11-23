<?php

namespace App\Http\Controllers;

use App\Models\Loai_Phong;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Loai_PhongController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách loại phòng.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $types = Loai_Phong::orderBy('id')->get();

        return $this->successResponse($types, 'Lấy danh sách loại phòng thành công.', 200);
    }
}
