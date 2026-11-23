<?php

namespace App\Http\Controllers;

use App\Http\Requests\Khach_HangRequest;
use App\Models\Khach_Hang;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Khach_HangController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách khách hàng kèm tìm kiếm, lọc và phân trang (UC08 / BR08.9).
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

        // BR08.9: Dữ liệu định danh chỉ hiển thị cho nhân viên có thẩm quyền (Lễ tân, Quản lý)
        if (!in_array($vaiTro, ['Quản lý', 'Lễ tân'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập danh sách khách hàng.', 403);
        }

        $query = Khach_Hang::query();

        // Tìm kiếm theo từ khóa (keyword hoặc q): họ tên, CCCD, SĐT, email
        $keyword = trim($request->input('keyword') ?? $request->input('q') ?? '');
        if ($keyword !== '') {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('hoTen', 'like', "%{$keyword}%")
                    ->orWhere('soDienThoai', 'like', "%{$keyword}%")
                    ->orWhere('cccd', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $query->orderByDesc('id');

        // Phân trang nếu có tham số 'page' hoặc 'per_page'
        if ($request->filled('page')) {
            $perPage = (int) $request->input('per_page', 15);
            $paginated = $query->paginate($perPage);

            return $this->successResponse($paginated->items(), 'Lấy danh sách khách hàng thành công.', 200, [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ]);
        }

        $customers = $query->get();

        return $this->successResponse($customers, 'Lấy danh sách khách hàng thành công.');
    }

    /**
     * Xem chi tiết thông tin một khách hàng (UC08).
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

        $vaiTro = $user->vaiTro;
        $customer = Khach_Hang::find($id);

        if (!$customer) {
            return $this->errorResponse('Khách hàng không tồn tại trong hệ thống.', 404);
        }

        // Nếu là Khách hàng, chỉ được xem thông tin của chính mình
        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang || $user->khachHang->id !== $customer->id) {
                return $this->errorResponse('Bạn không có quyền xem thông tin của khách hàng khác.', 403);
            }
        } elseif (!in_array($vaiTro, ['Quản lý', 'Lễ tân'], true)) {
            return $this->errorResponse('Bạn không có quyền xem thông tin khách hàng.', 403);
        }

        return $this->successResponse($customer, 'Lấy thông tin khách hàng thành công.');
    }

    /**
     * Thêm hồ sơ khách hàng mới (UC08).
     *
     * @param Khach_HangRequest $request
     * @return JsonResponse
     */
    public function store(Khach_HangRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $hoTen = trim($request->input('hoTen'));
            $cccd = trim($request->input('cccd'));
            $email = trim($request->input('email'));
            $soDienThoai = trim($request->input('soDienThoai'));
            $diaChi = $request->filled('diaChi') ? trim($request->input('diaChi')) : 'Chưa cập nhật';

            $maTaiKhoan = $request->input('maTaiKhoan');
            if (!$maTaiKhoan) {
                // Tự động sinh tài khoản tương ứng cho khách hàng mới
                $baseUsername = 'kh_' . preg_replace('/[^a-zA-Z0-9]/', '', $cccd ?: $soDienThoai);
                $tenDangNhap = $baseUsername;
                $counter = 1;
                while (\App\Models\Tai_Khoan::where('tenDangNhap', $tenDangNhap)->exists()) {
                    $tenDangNhap = $baseUsername . '_' . $counter++;
                }

                $taiKhoan = \App\Models\Tai_Khoan::create([
                    'tenDangNhap' => $tenDangNhap,
                    'password'    => \Illuminate\Support\Facades\Hash::make('KhachHang123@'),
                    'trangThai'   => 'Hoạt động',
                ]);
                $maTaiKhoan = $taiKhoan->id;
            }

            $customer = Khach_Hang::create([
                'hoTen'       => $hoTen,
                'cccd'        => $cccd,
                'email'       => $email,
                'soDienThoai' => $soDienThoai,
                'diaChi'      => $diaChi,
                'maTaiKhoan'  => $maTaiKhoan,
            ]);

            DB::commit();

            return $this->successResponse($customer, 'Thêm hồ sơ khách hàng thành công.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm hồ sơ khách hàng: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi tạo khách hàng. Vui lòng thử lại.', 500);
        }
    }

    /**
     * Cập nhật thông tin hồ sơ khách hàng (UC08).
     *
     * @param int|string $id
     * @param Khach_HangRequest $request
     * @return JsonResponse
     */
    public function update($id, Khach_HangRequest $request): JsonResponse
    {
        $customer = Khach_Hang::find($id);

        if (!$customer) {
            return $this->errorResponse('Khách hàng không tồn tại trong hệ thống.', 404);
        }

        DB::beginTransaction();
        try {
            $dataToUpdate = [];
            foreach (['hoTen', 'cccd', 'email', 'soDienThoai', 'diaChi', 'maTaiKhoan'] as $field) {
                if ($request->has($field)) {
                    $val = $request->input($field);
                    $dataToUpdate[$field] = is_string($val) ? trim($val) : $val;
                }
            }

            $customer->update($dataToUpdate);

            DB::commit();

            return $this->successResponse($customer->fresh(), 'Cập nhật thông tin khách hàng thành công.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật hồ sơ khách hàng: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi cập nhật khách hàng. Vui lòng thử lại.', 500);
        }
    }

    /**
     * Xem lịch sử đặt phòng & lưu trú của khách hàng (UC08 / BR02.6).
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function stayHistory($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $customer = Khach_Hang::find($id);

        if (!$customer) {
            return $this->errorResponse('Khách hàng không tồn tại trong hệ thống.', 404);
        }

        $vaiTro = $user->vaiTro;

        // Phân quyền: Khách hàng chỉ xem lịch sử của chính mình
        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang || $user->khachHang->id !== $customer->id) {
                return $this->errorResponse('Bạn không có quyền xem lịch sử của khách hàng khác.', 403);
            }
        } elseif (!in_array($vaiTro, ['Quản lý', 'Lễ tân'], true)) {
            return $this->errorResponse('Bạn không có quyền xem lịch sử lưu trú của khách hàng.', 403);
        }

        // Lấy danh sách các đơn đặt phòng kèm chi tiết phòng, lưu trú, hóa đơn
        $bookings = $customer->datPhongs()
            ->with([
                'chiTietDatPhong.phong.loaiPhong',
                'nhanVien',
            ])
            ->orderByDesc('id')
            ->get();

        $historyData = $bookings->map(function ($booking) {
            $rooms = $booking->chiTietDatPhong->map(function ($ct) {
                $stay = \App\Models\Luu_Tru::with('hoaDon')->where('maChiTiet', $ct->id)->first();

                return [
                    'maChiTiet'      => $ct->id,
                    'tenPhong'       => $ct->phong?->tenPhong,
                    'tenLoaiPhong'   => $ct->phong?->loaiPhong?->tenLoai,
                    'giaPhongApDung' => (int) $ct->giaPhongApDung,
                    'luuTru'         => $stay ? [
                        'maLuuTru'     => $stay->id,
                        'thoiGianNhan' => $stay->thoiGianNhan?->format('Y-m-d H:i:s'),
                        'thoiGianTra'  => $stay->thoiGianTra?->format('Y-m-d H:i:s'),
                        'trangThai'    => $stay->trangThai,
                        'hoaDon'       => $stay->hoaDon ? [
                            'maHoaDon'  => $stay->hoaDon->id,
                            'tongTien'  => (int) $stay->hoaDon->tongTien,
                            'trangThai' => $stay->hoaDon->trangThai,
                        ] : null,
                    ] : null,
                ];
            });

            return [
                'maDatPhong'    => $booking->id,
                'ngayDat'       => $booking->ngayDat?->format('Y-m-d H:i:s'),
                'ngayNhanPhong' => $booking->ngayNhanPhong?->format('Y-m-d H:i:s'),
                'ngayTraPhong'  => $booking->ngayTraPhong?->format('Y-m-d H:i:s'),
                'soLuongKhach'  => (int) $booking->soLuongKhach,
                'tienCoc'       => (int) $booking->tienCoc,
                'trangThai'     => $booking->trangThai,
                'nhanVien'      => $booking->nhanVien?->hoTen,
                'danhSachPhong' => $rooms,
            ];
        });

        return $this->successResponse([
            'khachHang' => [
                'id'          => $customer->id,
                'hoTen'       => $customer->hoTen,
                'cccd'        => $customer->cccd,
                'email'       => $customer->email,
                'soDienThoai' => $customer->soDienThoai,
                'diaChi'      => $customer->diaChi,
            ],
            'lichSuDatPhong' => $historyData,
        ], 'Lấy lịch sử lưu trú của khách hàng thành công.');
    }

    /**
     * Xóa hồ sơ khách hàng (UC08 / BR02.6: Không xóa cứng nếu đã phát sinh giao dịch).
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        if (!in_array($user->vaiTro, ['Quản lý', 'Lễ tân'], true)) {
            return $this->errorResponse('Bạn không có quyền thực hiện thao tác này.', 403);
        }

        $customer = Khach_Hang::find($id);
        if (!$customer) {
            return $this->errorResponse('Khách hàng không tồn tại trong hệ thống.', 404);
        }

        // BR02.6: Không xóa cứng hồ sơ khách hàng đã từng phát sinh đơn đặt phòng hoặc hóa đơn thanh toán
        if ($customer->datPhongs()->exists()) {
            return $this->errorResponse('Khách hàng đã từng phát sinh giao dịch đặt phòng hoặc lưu trú, không thể xóa khỏi hệ thống.', 409);
        }

        DB::beginTransaction();
        try {
            $customer->delete();
            DB::commit();

            return $this->successResponse(null, 'Xóa hồ sơ khách hàng thành công.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi xóa hồ sơ khách hàng: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi xóa khách hàng. Vui lòng thử lại.', 500);
        }
    }
}
