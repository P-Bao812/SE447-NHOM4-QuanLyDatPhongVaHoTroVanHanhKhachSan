<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hoa_DonRequest;
use App\Models\Chi_Tiet_Hoa_Don;
use App\Models\Hoa_Don;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Hoa_DonController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách hóa đơn thanh toán kèm tìm kiếm, lọc và phân trang (UC07).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $vaiTro = $user->vaiTro;

        // Phân quyền truy cập
        if (!in_array($vaiTro, ['Quản lý', 'Lễ tân', 'Khách hàng'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập danh sách hóa đơn.', 403);
        }

        $query = Hoa_Don::with([
            'luuTru.chiTietDatPhong.phong',
            'luuTru.chiTietDatPhong.datPhong.khachHang',
            'thanhToans',
            'chiTietHoaDons',
        ]);

        // Nếu là Khách hàng, chỉ xem hóa đơn thuộc các đợt lưu trú của chính mình
        if ($vaiTro === 'Khách hàng') {
            $khachHang = $user->khachHang;
            if (!$khachHang) {
                return $this->successResponse([], 'Lấy danh sách hóa đơn thành công.');
            }
            $query->whereHas('luuTru.chiTietDatPhong.datPhong', function ($q) use ($khachHang) {
                $q->where('maKhachHang', $khachHang->id);
            });
        }

        // Tìm kiếm theo từ khóa: mã hóa đơn, tên/số phòng, họ tên khách hàng, số điện thoại
        if ($request->filled('keyword')) {
            $kw = trim($request->input('keyword'));
            $query->where(function ($q) use ($kw) {
                if (is_numeric($kw)) {
                    $q->orWhere('id', (int) $kw);
                }
                $q->orWhereHas('luuTru.chiTietDatPhong.phong', function ($sub) use ($kw) {
                    $sub->where('tenPhong', 'like', "%{$kw}%");
                })->orWhereHas('luuTru.chiTietDatPhong.datPhong.khachHang', function ($sub) use ($kw) {
                    $sub->where('hoTen', 'like', "%{$kw}%")
                        ->orWhere('soDienThoai', 'like', "%{$kw}%");
                });
            });
        }

        // Lọc theo trạng thái: Chờ thanh toán, Đã thanh toán
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->input('trangThai'));
        }

        // Lọc theo mã khách hàng
        if ($request->filled('maKhachHang')) {
            $maKH = (int) $request->input('maKhachHang');
            $query->whereHas('luuTru.chiTietDatPhong.datPhong', function ($q) use ($maKH) {
                $q->where('maKhachHang', $maKH);
            });
        }

        // Lọc theo khoảng ngày lập hóa đơn
        if ($request->filled('ngayLapTu')) {
            $query->whereDate('ngayLap', '>=', $request->input('ngayLapTu'));
        }
        if ($request->filled('ngayLapDen')) {
            $query->whereDate('ngayLap', '<=', $request->input('ngayLapDen'));
        }

        $query->orderByDesc('id');

        // Phân trang nếu có tham số 'page' hoặc 'per_page'
        if ($request->filled('page')) {
            $perPage = (int) $request->input('per_page', 15);
            $paginated = $query->paginate($perPage);

            $data = collect($paginated->items())->map(fn(Hoa_Don $hd) => $this->formatInvoiceListItem($hd))->values();

            return $this->successResponse($data, 'Lấy danh sách hóa đơn thành công.', 200, [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ]);
        }

        $invoices = $query->get()->map(fn(Hoa_Don $hd) => $this->formatInvoiceListItem($hd))->values();

        return $this->successResponse($invoices, 'Lấy danh sách hóa đơn thành công.');
    }

    /**
     * Xem chi tiết hóa đơn thanh toán (UC07).
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $hoaDon = Hoa_Don::with([
            'luuTru.chiTietDatPhong.phong.loaiPhong',
            'luuTru.chiTietDatPhong.datPhong.khachHang',
            'chiTietHoaDons',
            'thanhToans',
        ])->find($id);

        if (!$hoaDon) {
            return $this->errorResponse('Hóa đơn không tồn tại trong hệ thống.', 404);
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Quản lý', 'Lễ tân', 'Khách hàng'], true)) {
            return $this->errorResponse('Bạn không có quyền xem thông tin hóa đơn này.', 403);
        }

        // Kiểm tra quyền sở hữu nếu là Khách hàng
        if ($vaiTro === 'Khách hàng') {
            $maKhachHangCuaDon = $hoaDon->luuTru?->chiTietDatPhong?->datPhong?->maKhachHang;
            if (!$user->khachHang || $maKhachHangCuaDon !== $user->khachHang->id) {
                return $this->errorResponse('Bạn không có quyền xem hóa đơn của khách hàng khác.', 403);
            }
        }

        $daThanhToan = (int) $hoaDon->thanhToans->where('trangThai', 'Thành công')->sum('soTien');
        $conLai = max(0, (int) $hoaDon->tongTien - $daThanhToan);
        $chiTietDatPhong = $hoaDon->luuTru?->chiTietDatPhong;
        $datPhong = $chiTietDatPhong?->datPhong;
        $khachHang = $datPhong?->khachHang;
        $phong = $chiTietDatPhong?->phong;

        $data = [
            'maHoaDon'     => $hoaDon->id,
            'maLuuTru'     => $hoaDon->maLuuTru,
            'ngayLap'      => $hoaDon->ngayLap?->format('Y-m-d H:i:s'),
            'tongTien'     => (int) $hoaDon->tongTien,
            'daThanhToan'  => $daThanhToan,
            'conLai'       => $conLai,
            'trangThai'    => $hoaDon->trangThai,
            'tienCoc'      => (int) ($datPhong?->tienCoc ?? 0),
            'khachHang'    => $khachHang ? [
                'id'          => $khachHang->id,
                'hoTen'       => $khachHang->hoTen,
                'soDienThoai' => $khachHang->soDienThoai,
                'email'       => $khachHang->email,
                'cccd'        => $khachHang->cccd,
            ] : null,
            'phong'        => $phong ? [
                'id'        => $phong->id,
                'tenPhong'  => $phong->tenPhong,
                'tang'      => $phong->tang,
                'tenLoai'   => $phong->loaiPhong?->tenLoai,
            ] : null,
            'chiTiet'      => $hoaDon->chiTietHoaDons->map(function (Chi_Tiet_Hoa_Don $ct) {
                return [
                    'id'        => $ct->id,
                    'noiDung'   => $ct->noiDung,
                    'soLuong'   => (int) $ct->soLuong,
                    'donGia'    => (int) $ct->donGia,
                    'thanhTien' => (int) $ct->thanhTien,
                ];
            })->values(),
            'thanhToans'   => $hoaDon->thanhToans->map(function ($tt) {
                return [
                    'id'         => $tt->id,
                    'soTien'     => (int) $tt->soTien,
                    'phuongThuc' => $tt->phuongThuc,
                    'maGiaoDich' => $tt->maGiaoDich,
                    'thoiGian'   => $tt->thoiGian?->format('Y-m-d H:i:s'),
                    'trangThai'  => $tt->trangThai,
                ];
            })->values(),
        ];

        return $this->successResponse($data, 'Truy vấn hóa đơn thành công.');
    }

    /**
     * Thêm phụ thu / điều chỉnh khoản mục vào hóa đơn trước khi thanh toán (UC07 / BR04.2).
     *
     * @param int|string $id
     * @param Hoa_DonRequest $request
     * @return JsonResponse
     */
    public function addAdjustment($id, Hoa_DonRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var Hoa_Don|null $hoaDon */
            $hoaDon = Hoa_Don::where('id', $id)->lockForUpdate()->first();

            if (!$hoaDon) {
                DB::rollBack();
                return $this->errorResponse('Hóa đơn không tồn tại trong hệ thống.', 404);
            }

            // BR04.2: Hóa đơn sau khi đã chuyển sang trạng thái "Đã thanh toán" sẽ bị khóa chỉnh sửa
            if ($hoaDon->trangThai === 'Đã thanh toán') {
                DB::rollBack();
                return $this->errorResponse('Hóa đơn đã thanh toán hoàn tất, không thể chỉnh sửa hoặc thêm khoản điều chỉnh.', 409);
            }

            $soLuong = (int) $request->input('soLuong');
            $donGia = (int) $request->input('donGia');
            $thanhTien = $soLuong * $donGia;

            $chiTiet = Chi_Tiet_Hoa_Don::create([
                'maHoaDon'  => $hoaDon->id,
                'noiDung'   => trim($request->input('noiDung')),
                'soLuong'   => $soLuong,
                'donGia'    => $donGia,
                'thanhTien' => $thanhTien,
            ]);

            // Tính lại tổng tiền hóa đơn từ toàn bộ chi tiết hóa đơn
            $tongTienMoi = (int) $hoaDon->chiTietHoaDons()->sum('thanhTien');
            $hoaDon->update([
                'tongTien' => max(0, $tongTienMoi),
            ]);

            DB::commit();

            return $this->successResponse([
                'chiTiet'  => [
                    'id'        => $chiTiet->id,
                    'noiDung'   => $chiTiet->noiDung,
                    'soLuong'   => (int) $chiTiet->soLuong,
                    'donGia'    => (int) $chiTiet->donGia,
                    'thanhTien' => (int) $chiTiet->thanhTien,
                ],
                'tongTien' => (int) $hoaDon->tongTien,
            ], 'Thêm khoản điều chỉnh vào hóa đơn thành công.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm khoản điều chỉnh hóa đơn: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi thêm điều chỉnh hóa đơn. Vui lòng thử lại.', 500);
        }
    }

    /**
     * Chuẩn hóa dữ liệu hiển thị item hóa đơn trong danh sách.
     *
     * @param Hoa_Don $hoaDon
     * @return array<string, mixed>
     */
    private function formatInvoiceListItem(Hoa_Don $hoaDon): array
    {
        $daThanhToan = (int) $hoaDon->thanhToans->where('trangThai', 'Thành công')->sum('soTien');
        $conLai = max(0, (int) $hoaDon->tongTien - $daThanhToan);
        $chiTietDatPhong = $hoaDon->luuTru?->chiTietDatPhong;
        $datPhong = $chiTietDatPhong?->datPhong;
        $khachHang = $datPhong?->khachHang;
        $phong = $chiTietDatPhong?->phong;

        return [
            'maHoaDon'     => $hoaDon->id,
            'maLuuTru'     => $hoaDon->maLuuTru,
            'ngayLap'      => $hoaDon->ngayLap?->format('Y-m-d H:i:s'),
            'tongTien'     => (int) $hoaDon->tongTien,
            'daThanhToan'  => $daThanhToan,
            'conLai'       => $conLai,
            'trangThai'    => $hoaDon->trangThai,
            'maDatPhong'   => $datPhong?->id,
            'tenKhachHang' => $khachHang?->hoTen,
            'soDienThoai'  => $khachHang?->soDienThoai,
            'tenPhong'     => $phong?->tenPhong,
        ];
    }
}
