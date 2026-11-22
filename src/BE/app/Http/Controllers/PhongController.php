<?php

namespace App\Http\Controllers;

use App\Models\Phong;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhongController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách phòng vật lý kèm loại phòng (UC06 / UC08).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Phong::with('loaiPhong');

        if ($request->filled('tang')) {
            $query->where('tang', (int) $request->input('tang'));
        }

        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->input('trangThai'));
        }

        if ($request->filled('maLoaiPhong')) {
            $query->where('maLoaiPhong', (int) $request->input('maLoaiPhong'));
        }

        $phongs = $query->orderBy('tang')->orderBy('tenPhong')->get()->map(function ($p) {
            return [
                'id'        => $p->id,
                'tenPhong'  => $p->tenPhong,
                'tang'      => $p->tang,
                'trangThai' => $p->trangThai,
                'loaiPhong' => $p->loaiPhong ? [
                    'id'           => $p->loaiPhong->id,
                    'tenLoai'      => $p->loaiPhong->tenLoai,
                    'giaTieuChuan' => $p->loaiPhong->giaTieuChuan,
                    'sucChuaToiDa' => $p->loaiPhong->sucChuaToiDa,
                ] : null,
            ];
        });

        return $this->successResponse($phongs, 'Lấy danh sách phòng thành công.');
    }
}
