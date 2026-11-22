<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dat_PhongRequest;
use App\Http\Requests\Duyet_Huy_Dat_PhongRequest;
use App\Http\Requests\Yeu_Cau_Huy_Dat_PhongRequest;
use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Hoan_Tien;
use App\Models\Phong;
use App\Models\Thanh_Toan;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dat_PhongController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách đơn đặt phòng (UC15 / BR05.1, BR05.4).
     * Khách hàng: Chỉ xem các đơn thuộc quyền sở hữu của mình.
     * Lễ tân / Quản lý: Xem toàn bộ đơn, có thể lọc theo khách hàng hoặc trạng thái.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa xác thực người dùng.', 401);
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Khách hàng', 'Lễ tân', 'Quản lý'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập danh sách đặt phòng.', 403);
        }

        $query = Dat_Phong::with(['khachHang', 'nhanVien', 'chiTietDatPhong.phong.loaiPhong']);

        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang) {
                return $this->errorResponse('Tài khoản chưa có thông tin hồ sơ khách hàng.', 403);
            }
            $query->where('maKhachHang', $user->khachHang->id);
        } else {
            // Lễ tân hoặc Quản lý có thể lọc theo maKhachHang hoặc trangThai
            if ($request->filled('maKhachHang')) {
                $query->where('maKhachHang', (int) $request->input('maKhachHang'));
            }
            if ($request->filled('trangThai')) {
                $query->where('trangThai', $request->input('trangThai'));
            }
        }

        $bookings = $query->orderBy('id', 'desc')->get();
        $data = $bookings->map(fn(Dat_Phong $item) => $this->formatBookingItem($item))->values();

        return $this->successResponse($data, 'Lấy danh sách đặt phòng thành công.', 200);
    }

    /**
     * Xem chi tiết một đơn đặt phòng (UC15 / BR05.1).
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa xác thực người dùng.', 401);
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Khách hàng', 'Lễ tân', 'Quản lý'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập đơn đặt phòng.', 403);
        }

        $datPhong = Dat_Phong::with(['khachHang', 'nhanVien', 'chiTietDatPhong.phong.loaiPhong'])->find($id);
        if (!$datPhong) {
            return $this->errorResponse('Không tìm thấy đơn đặt phòng.', 404);
        }

        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang || (int) $datPhong->maKhachHang !== (int) $user->khachHang->id) {
                return $this->errorResponse('Bạn không có quyền truy cập đơn đặt phòng này.', 403);
            }
        }

        $data = $this->formatBookingDetail($datPhong);

        return $this->successResponse($data, 'Lấy chi tiết đơn đặt phòng thành công.', 200);
    }

    /**
     * Khởi tạo đơn đặt phòng mới (UC04 / BR02).
     * Cho phép: Khách hàng tự đặt hoặc Lễ tân / Quản lý tạo thay mặt khách.
     *
     * @param Dat_PhongRequest $request
     * @return JsonResponse
     */
    public function store(Dat_PhongRequest $request): JsonResponse
    {
        $user = $request->user();
        $vaiTro = $user->vaiTro;

        // 1. Xác định khách hàng và nhân viên phụ trách
        if ($vaiTro === 'Khách hàng') {
            $maKhachHang = (int) $user->khachHang->id;
            $maNhanVien = null; // Khách tự đặt online, không gán nhân viên giả
        } else {
            // Lễ tân hoặc Quản lý
            $maKhachHang = (int) $request->input('maKhachHang');
            $maNhanVien = $user->nhanVien ? (int) $user->nhanVien->id : null;
        }

        $ngayNhanPhong = Carbon::parse($request->input('ngayNhanPhong'))->format('Y-m-d H:i:s');
        $ngayTraPhong = Carbon::parse($request->input('ngayTraPhong'))->format('Y-m-d H:i:s');
        $soLuongKhach = (int) $request->input('soLuongKhach');
        $danhSachPhong = $request->input('danhSachPhong');

        // 2. Tính số đêm lưu trú (tối thiểu 1 đêm)
        $start = Carbon::parse($ngayNhanPhong)->startOfDay();
        $end = Carbon::parse($ngayTraPhong)->startOfDay();
        $soDem = max(1, (int) $start->diffInDays($end));

        // 3. Lấy thông tin phòng và tính đơn giá có thẩm quyền từ bảng loai_phong (BR01.8)
        $maPhongList = array_column($danhSachPhong, 'maPhong');
        $phongs = Phong::with('loaiPhong')->whereIn('id', $maPhongList)->get()->keyBy('id');

        $tongTienPhong = 0;
        foreach ($danhSachPhong as $item) {
            $room = $phongs[$item['maPhong']];
            $giaTieuChuan = (int) ($room->loaiPhong->giaTieuChuan ?? 0);
            $tongTienPhong += $giaTieuChuan * $soDem;
        }

        // 4. Tính toán và kiểm chứng tiền cọc theo chính sách
        if ($request->filled('tienCoc')) {
            $tienCoc = (int) $request->input('tienCoc');
            if ($tienCoc > $tongTienPhong) {
                return $this->errorResponse(
                    'Tiền cọc không được vượt quá tổng tiền phòng (' . number_format($tongTienPhong, 0, ',', '.') . ' VNĐ).',
                    422,
                    ['tienCoc' => ['Tiền cọc không được vượt quá tổng tiền phòng.']]
                );
            }
        } else {
            // Chính sách mặc định: 30% tổng tiền phòng
            $tienCoc = (int) round($tongTienPhong * 0.3);
        }

        // 5. Database Transaction & Pessimistic Locking kiểm tra trùng lịch (BR02.1)
        DB::beginTransaction();
        try {
            // Khóa các phòng để chống Race Condition khi đặt đồng thời
            Phong::whereIn('id', $maPhongList)->lockForUpdate()->get();

            // Kiểm tra xung đột lịch đặt phòng trong khoảng thời gian [ngayNhanPhong, ngayTraPhong]
            $conflictingBookings = DB::table('chi_tiet_dat_phong')
                ->join('dat_phong', 'chi_tiet_dat_phong.maDatPhong', '=', 'dat_phong.id')
                ->join('phong', 'chi_tiet_dat_phong.maPhong', '=', 'phong.id')
                ->whereIn('chi_tiet_dat_phong.maPhong', $maPhongList)
                ->whereNotIn('dat_phong.trangThai', ['Đã hủy'])
                ->where('dat_phong.ngayNhanPhong', '<', $ngayTraPhong)
                ->where('dat_phong.ngayTraPhong', '>', $ngayNhanPhong)
                ->select('phong.tenPhong', 'chi_tiet_dat_phong.maPhong')
                ->get();

            if ($conflictingBookings->isNotEmpty()) {
                DB::rollBack();
                $phongBiTrung = $conflictingBookings->pluck('tenPhong')->unique()->values()->all();

                return $this->errorResponse(
                    'Phòng đã được khách khác đặt trong khung giờ yêu cầu.',
                    409,
                    ['phongBiTrung' => $phongBiTrung]
                );
            }

            // 6. Tạo đơn đặt phòng mới với trạng thái ban đầu là 'Chờ xác nhận' (BR03.1, BR03.2)
            $datPhong = Dat_Phong::create([
                'maKhachHang'   => $maKhachHang,
                'maNhanVien'    => $maNhanVien,
                'ngayDat'       => now(),
                'ngayNhanPhong' => $ngayNhanPhong,
                'ngayTraPhong'  => $ngayTraPhong,
                'soLuongKhach'  => $soLuongKhach,
                'tienCoc'       => $tienCoc,
                'trangThai'     => 'Chờ xác nhận',
            ]);

            // 7. Tạo chi tiết đặt phòng cho từng phòng, lưu đơn giá cố định tại thời điểm đặt (BR01.8)
            $danhSachChiTiet = [];
            foreach ($danhSachPhong as $item) {
                $room = $phongs[$item['maPhong']];
                $giaPhongApDung = (int) ($room->loaiPhong->giaTieuChuan ?? 0);

                $chiTiet = Chi_Tiet_Dat_Phong::create([
                    'maDatPhong'     => $datPhong->id,
                    'maPhong'        => $room->id,
                    'giaPhongApDung' => $giaPhongApDung,
                    'soLuongKhach'   => (int) $item['soLuongKhach'],
                ]);

                $danhSachChiTiet[] = [
                    'id'             => $chiTiet->id,
                    'maPhong'        => $room->id,
                    'tenPhong'       => $room->tenPhong,
                    'giaPhongApDung' => $giaPhongApDung,
                    'soLuongKhach'   => (int) $item['soLuongKhach'],
                ];
            }

            DB::commit();

            // 8. Trả về cấu trúc JSON chuẩn theo api-specification.md
            return $this->successResponse([
                'maDatPhong'    => $datPhong->id,
                'trangThai'     => $datPhong->trangThai,
                'tienCoc'       => (int) $datPhong->tienCoc,
                'ngayNhanPhong' => Carbon::parse($datPhong->ngayNhanPhong)->format('Y-m-d H:i:s'),
                'ngayTraPhong'  => Carbon::parse($datPhong->ngayTraPhong)->format('Y-m-d H:i:s'),
                'soLuongKhach'  => (int) $datPhong->soLuongKhach,
                'danhSachPhong' => $danhSachChiTiet,
            ], 'Tạo đơn đặt phòng thành công.', 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo đơn đặt phòng (UC04): ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->errorResponse(
                'Tạo đơn đặt phòng không thành công do lỗi hệ thống. Vui lòng thử lại sau.',
                500
            );
        }
    }

    /**
     * Gửi yêu cầu hủy đặt phòng (UC15 / BR05.2, BR05.3, BR05.6).
     * Khách hàng / Lễ tân gửi yêu cầu hủy.
     * Trạng thái đơn đặt phòng được GIỮ NGUYÊN cho đến khi được duyệt.
     *
     * @param Yeu_Cau_Huy_Dat_PhongRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function requestCancellation(Yeu_Cau_Huy_Dat_PhongRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $vaiTro = $user->vaiTro;

        $datPhong = Dat_Phong::with('chiTietDatPhong')->find($id);
        if (!$datPhong) {
            return $this->errorResponse('Không tìm thấy đơn đặt phòng.', 404);
        }

        // 1. Phân quyền sở hữu
        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang || (int) $datPhong->maKhachHang !== (int) $user->khachHang->id) {
                return $this->errorResponse('Bạn không có quyền gửi yêu cầu hủy cho đơn đặt phòng này.', 403);
            }
        } elseif (!in_array($vaiTro, ['Lễ tân', 'Quản lý'], true)) {
            return $this->errorResponse('Bạn không có quyền thực hiện thao tác này.', 403);
        }

        // 2. Kiểm tra trạng thái đơn đặt phòng
        if ($datPhong->trangThai === 'Đã hủy') {
            return $this->errorResponse('Đơn đặt phòng đã được hủy trước đó.', 409);
        }

        if (in_array($datPhong->trangThai, ['Đang ở', 'Đã trả', 'Đã hoàn thành'], true)) {
            return $this->errorResponse('Không thể hủy đơn đặt phòng đang lưu trú hoặc đã hoàn thành.', 409);
        }

        // 3. Kiểm tra yêu cầu hủy trùng lặp
        $existingRequest = $this->getCancellationRequest((int) $datPhong->id);
        if ($existingRequest && ($existingRequest['trangThaiYeuCau'] ?? '') === 'Chờ xử lý') {
            return $this->errorResponse('Đơn đặt phòng đã gửi yêu cầu hủy và đang chờ xử lý.', 409);
        }

        // 4. Kiểm tra thời hạn hoàn tiền cọc
        $isWithinDeadline = $this->isWithinRefundDeadline($datPhong);
        $xacNhanKhongHoanCoc = $request->boolean('xacNhanKhongHoanCoc');

        if (!$isWithinDeadline && !$xacNhanKhongHoanCoc) {
            return $this->errorResponse(
                'Đơn đặt phòng đã vượt quá thời hạn hủy được hoàn tiền cọc. Quý khách vui lòng xác nhận không hoàn cọc để gửi yêu cầu hủy.',
                422,
                ['xacNhanKhongHoanCoc' => ['Bạn cần xác nhận chấp nhận không hoàn lại tiền cọc khi hủy đơn sau thời hạn quy định.']]
            );
        }

        // 5. Ghi nhận yêu cầu hủy bằng transaction
        DB::beginTransaction();
        try {
            $lockedBooking = Dat_Phong::where('id', $datPhong->id)->lockForUpdate()->first();

            if ($lockedBooking->trangThai === 'Đã hủy') {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng đã được hủy trước đó.', 409);
            }

            $lyDoHuy = $request->input('lyDoHuy');
            $soTienHoanDuKien = ($isWithinDeadline && (int) $datPhong->tienCoc > 0) ? (int) $datPhong->tienCoc : 0;

            $cancellationData = [
                'maDatPhong'          => (int) $datPhong->id,
                'lyDoHuy'             => $lyDoHuy,
                'xacNhanKhongHoanCoc' => $xacNhanKhongHoanCoc,
                'trongThoiHanHoanCoc' => $isWithinDeadline,
                'soTienHoanDuKien'    => $soTienHoanDuKien,
                'thoiGianYeuCau'      => now()->format('Y-m-d H:i:s'),
                'trangThaiYeuCau'     => 'Chờ xử lý',
                'maNguoiYeuCau'       => (int) $user->id,
                'vaiTroNguoiYeuCau'   => $vaiTro,
                'maThanhToan'         => $request->input('maThanhToan'),
            ];

            $this->saveCancellationRequest((int) $datPhong->id, $cancellationData);

            DB::commit();

            return $this->successResponse([
                'maDatPhong'          => $datPhong->id,
                'trangThaiDatPhong'   => $datPhong->trangThai,
                'trangThaiYeuCau'     => 'Chờ xử lý',
                'lyDoHuy'             => $lyDoHuy,
                'xacNhanKhongHoanCoc' => $xacNhanKhongHoanCoc,
                'trongThoiHanHoanCoc' => $isWithinDeadline,
                'soTienHoanDuKien'    => $soTienHoanDuKien,
                'thoiGianYeuCau'      => $cancellationData['thoiGianYeuCau'],
            ], 'Gửi yêu cầu hủy đặt phòng thành công. Yêu cầu đang được chờ xử lý.', 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi ghi nhận yêu cầu hủy đặt phòng (UC15): ' . $e->getMessage(), [
                'exception'  => $e,
                'datPhongId' => $id,
            ]);

            return $this->errorResponse(
                'Gửi yêu cầu hủy đặt phòng không thành công do lỗi hệ thống. Vui lòng thử lại sau.',
                500
            );
        }
    }

    /**
     * Lễ tân / Quản lý xử lý trạng thái yêu cầu hủy đặt phòng (UC15 / BR05.4, BR05.5, BR05.6).
     * Khi duyệt: Đơn chuyển sang 'Đã hủy', giải phóng phòng và xử lý hoàn tiền nếu hợp lệ.
     * Khi từ chối: Đơn giữ nguyên trạng thái cũ.
     *
     * @param Duyet_Huy_Dat_PhongRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function updateCancellationStatus(Duyet_Huy_Dat_PhongRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        $vaiTro = $user->vaiTro;

        if (!in_array($vaiTro, ['Lễ tân', 'Quản lý'], true)) {
            return $this->errorResponse('Chỉ Lễ tân hoặc Quản lý mới có quyền xử lý yêu cầu hủy đặt phòng.', 403);
        }

        $datPhong = Dat_Phong::with('chiTietDatPhong')->find($id);
        if (!$datPhong) {
            return $this->errorResponse('Không tìm thấy đơn đặt phòng.', 404);
        }

        if ($datPhong->trangThai === 'Đã hủy') {
            return $this->errorResponse('Đơn đặt phòng đã được hủy trước đó.', 409);
        }

        if (in_array($datPhong->trangThai, ['Đang ở', 'Đã trả', 'Đã hoàn thành'], true)) {
            return $this->errorResponse('Không thể xử lý hủy cho đơn đặt phòng đang lưu trú hoặc đã hoàn thành.', 409);
        }

        $trangThaiXuLy = $request->input('trangThai'); // 'Đã duyệt' hoặc 'Từ chối'
        $ghiChu = $request->input('ghiChu');

        DB::beginTransaction();
        try {
            $lockedBooking = Dat_Phong::where('id', $datPhong->id)->lockForUpdate()->first();

            if ($lockedBooking->trangThai === 'Đã hủy') {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng đã được hủy trước đó.', 409);
            }

            $cancellationRequest = $this->getCancellationRequest((int) $datPhong->id) ?? [];

            $cancellationRequest['trangThaiYeuCau'] = $trangThaiXuLy;
            $cancellationRequest['maNhanVienXuLy']  = (int) $user->id;
            $cancellationRequest['thoiGianXuLy']   = now()->format('Y-m-d H:i:s');
            $cancellationRequest['ghiChuXuLy']     = $ghiChu;

            $hoanTienResult = null;

            if ($trangThaiXuLy === 'Đã duyệt') {
                // 1. Cập nhật trạng thái đơn thành 'Đã hủy' (giải phóng phòng)
                $lockedBooking->trangThai = 'Đã hủy';
                $lockedBooking->save();

                // 2. Xử lý hoàn tiền cọc nếu có chính sách hoàn tiền
                $isWithinDeadline = isset($cancellationRequest['trongThoiHanHoanCoc'])
                    ? (bool) $cancellationRequest['trongThoiHanHoanCoc']
                    : $this->isWithinRefundDeadline($lockedBooking);

                $xacNhanKhongHoanCoc = isset($cancellationRequest['xacNhanKhongHoanCoc'])
                    ? (bool) $cancellationRequest['xacNhanKhongHoanCoc']
                    : false;

                $soTienHoan = 0;
                if ($isWithinDeadline && (int) $lockedBooking->tienCoc > 0 && !$xacNhanKhongHoanCoc) {
                    $soTienHoan = (int) $lockedBooking->tienCoc;
                } elseif ($request->filled('soTienHoan')) {
                    $soTienHoan = (int) $request->input('soTienHoan');
                }

                // Tìm giao dịch thanh toán gốc
                $thanhToan = null;
                if ($request->filled('maThanhToan')) {
                    $thanhToan = Thanh_Toan::find($request->input('maThanhToan'));
                }
                if (!$thanhToan && !empty($cancellationRequest['maThanhToan'])) {
                    $thanhToan = Thanh_Toan::find($cancellationRequest['maThanhToan']);
                }
                if (!$thanhToan) {
                    $thanhToan = $this->findRelatedPayment($lockedBooking);
                }

                if ($thanhToan && $soTienHoan > 0) {
                    $soTienHoanHopLe = min($soTienHoan, (int) $thanhToan->soTien);

                    // Chống hoàn tiền trùng lặp: Một giao dịch thanh_toan không tạo trùng hoan_tien
                    $existingRefund = Hoan_Tien::where('maThanhToan', $thanhToan->id)->first();
                    if (!$existingRefund) {
                        $hoanTien = Hoan_Tien::create([
                            'maThanhToan' => $thanhToan->id,
                            'soTien'      => $soTienHoanHopLe,
                            'lyDo'        => $cancellationRequest['lyDoHuy'] ?? ($ghiChu ?: 'Hoàn tiền cọc khi duyệt hủy đặt phòng'),
                            'thoiGian'    => now(),
                            'trangThai'   => 'Đã hoàn tiền',
                        ]);

                        $hoanTienResult = [
                            'id'          => $hoanTien->id,
                            'maThanhToan' => $hoanTien->maThanhToan,
                            'soTien'      => (int) $hoanTien->soTien,
                            'trangThai'   => $hoanTien->trangThai,
                        ];
                    } else {
                        $hoanTienResult = [
                            'id'          => $existingRefund->id,
                            'maThanhToan' => $existingRefund->maThanhToan,
                            'soTien'      => (int) $existingRefund->soTien,
                            'trangThai'   => $existingRefund->trangThai,
                        ];
                    }
                }
            }

            // Lưu trạng thái yêu cầu hủy
            $this->saveCancellationRequest((int) $datPhong->id, $cancellationRequest);

            DB::commit();

            $message = $trangThaiXuLy === 'Đã duyệt'
                ? 'Duyệt yêu cầu hủy đặt phòng thành công. Phòng đã được giải phóng.'
                : 'Đã từ chối yêu cầu hủy đặt phòng. Đơn đặt phòng giữ nguyên trạng thái.';

            return $this->successResponse([
                'maDatPhong'        => $lockedBooking->id,
                'trangThaiDatPhong' => $lockedBooking->trangThai,
                'trangThaiYeuCau'   => $trangThaiXuLy,
                'ghiChuXuLy'        => $ghiChu,
                'thoiGianXuLy'      => $cancellationRequest['thoiGianXuLy'],
                'hoanTien'          => $hoanTienResult,
            ], $message, 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi xử lý trạng thái hủy đặt phòng (UC15): ' . $e->getMessage(), [
                'exception'  => $e,
                'datPhongId' => $id,
            ]);

            return $this->errorResponse(
                'Xử lý yêu cầu hủy đặt phòng không thành công do lỗi hệ thống. Vui lòng thử lại sau.',
                500
            );
        }
    }

    /**
     * Lấy dữ liệu yêu cầu hủy lưu trữ tạm/bền vững.
     */
    private function getCancellationRequest(int $datPhongId): ?array
    {
        return Cache::get("cancellation_request_{$datPhongId}");
    }

    /**
     * Lưu trữ dữ liệu yêu cầu hủy.
     */
    private function saveCancellationRequest(int $datPhongId, array $data): void
    {
        Cache::forever("cancellation_request_{$datPhongId}", $data);
    }

    /**
     * Tính toán thời hạn chót được hoàn cọc (trước ngày nhận phòng 24 giờ).
     */
    private function getRefundDeadline(Dat_Phong $datPhong): Carbon
    {
        return Carbon::parse($datPhong->ngayNhanPhong)->subHours(24);
    }

    /**
     * Kiểm tra thời điểm hiện tại có nằm trong thời hạn hoàn cọc hay không.
     */
    private function isWithinRefundDeadline(Dat_Phong $datPhong): bool
    {
        return now()->lessThanOrEqualTo($this->getRefundDeadline($datPhong));
    }

    /**
     * Tìm kiếm giao dịch thanh toán liên quan đến đơn đặt phòng.
     */
    private function findRelatedPayment(Dat_Phong $datPhong): ?Thanh_Toan
    {
        // 1. Kiểm tra chuỗi chi_tiet_dat_phong -> luu_tru -> hoa_don -> thanh_toan
        $chiTietIds = $datPhong->chiTietDatPhong()->pluck('id')->toArray();
        if (!empty($chiTietIds)) {
            $luuTruIds = DB::table('luu_tru')->whereIn('maChiTiet', $chiTietIds)->pluck('id')->toArray();
            if (!empty($luuTruIds)) {
                $hoaDonIds = DB::table('hoa_don')->whereIn('maLuuTru', $luuTruIds)->pluck('id')->toArray();
                if (!empty($hoaDonIds)) {
                    $thanhToan = Thanh_Toan::whereIn('maHoaDon', $hoaDonIds)->latest('id')->first();
                    if ($thanhToan) {
                        return $thanhToan;
                    }
                }
            }
        }

        // 2. Dự phòng: hoa_don.maLuuTru = dat_phong.id (trường hợp hóa đơn cọc tạo trực tiếp cho đơn)
        $hoaDon = DB::table('hoa_don')->where('maLuuTru', $datPhong->id)->latest('id')->first();
        if ($hoaDon) {
            $thanhToan = Thanh_Toan::where('maHoaDon', $hoaDon->id)->latest('id')->first();
            if ($thanhToan) {
                return $thanhToan;
            }
        }

        return null;
    }

    /**
     * Định dạng dữ liệu một đơn đặt phòng trong danh sách.
     */
    private function formatBookingItem(Dat_Phong $datPhong): array
    {
        $start = Carbon::parse($datPhong->ngayNhanPhong)->startOfDay();
        $end = Carbon::parse($datPhong->ngayTraPhong)->startOfDay();
        $soDem = max(1, (int) $start->diffInDays($end));

        $tongChiPhi = 0;
        $danhSachPhong = [];
        foreach ($datPhong->chiTietDatPhong as $ct) {
            $gia = (int) $ct->giaPhongApDung;
            $tongChiPhi += $gia * $soDem;
            $danhSachPhong[] = [
                'id'             => $ct->id,
                'maPhong'        => $ct->maPhong,
                'tenPhong'       => $ct->phong->tenPhong ?? null,
                'tenLoaiPhong'   => $ct->phong->loaiPhong->tenLoai ?? null,
                'giaPhongApDung' => $gia,
                'soLuongKhach'   => (int) $ct->soLuongKhach,
            ];
        }

        $cancellationRequest = $this->getCancellationRequest((int) $datPhong->id);

        return [
            'id'            => $datPhong->id,
            'maDatPhong'    => $datPhong->id,
            'ngayDat'       => Carbon::parse($datPhong->ngayDat)->format('Y-m-d H:i:s'),
            'ngayNhanPhong' => Carbon::parse($datPhong->ngayNhanPhong)->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::parse($datPhong->ngayTraPhong)->format('Y-m-d H:i:s'),
            'soDem'         => $soDem,
            'soLuongKhach'  => (int) $datPhong->soLuongKhach,
            'tienCoc'       => (int) $datPhong->tienCoc,
            'tongChiPhi'    => $tongChiPhi,
            'trangThai'     => $datPhong->trangThai,
            'khachHang'     => $datPhong->khachHang ? [
                'id'          => $datPhong->khachHang->id,
                'hoTen'       => $datPhong->khachHang->hoTen,
                'email'       => $datPhong->khachHang->email,
                'soDienThoai' => $datPhong->khachHang->soDienThoai,
            ] : null,
            'danhSachPhong' => $danhSachPhong,
            'yeuCauHuy'     => $cancellationRequest,
        ];
    }

    /**
     * Định dạng chi tiết đầy đủ của một đơn đặt phòng kèm chính sách hủy.
     */
    private function formatBookingDetail(Dat_Phong $datPhong): array
    {
        $item = $this->formatBookingItem($datPhong);

        if ($datPhong->khachHang) {
            $item['khachHang']['cccd'] = $datPhong->khachHang->cccd;
            $item['khachHang']['diaChi'] = $datPhong->khachHang->diaChi;
        }

        $item['nhanVien'] = $datPhong->nhanVien ? [
            'id'    => $datPhong->nhanVien->id,
            'hoTen' => $datPhong->nhanVien->hoTen,
        ] : null;

        $isWithinDeadline = $this->isWithinRefundDeadline($datPhong);
        $hanHoanCoc = $this->getRefundDeadline($datPhong);

        $cancellationRequest = $this->getCancellationRequest((int) $datPhong->id);
        $hasPendingRequest = $cancellationRequest && ($cancellationRequest['trangThaiYeuCau'] ?? '') === 'Chờ xử lý';

        $isCancellable = in_array($datPhong->trangThai, ['Chờ xác nhận', 'Đã cọc', 'Đã xác nhận'], true) && !$hasPendingRequest;

        $item['chinhSachHuy'] = [
            'coTheHuy'            => $isCancellable,
            'trongThoiHanHoanCoc' => $isWithinDeadline,
            'hanHoanCoc'          => $hanHoanCoc->format('Y-m-d H:i:s'),
            'soTienHoanDuKien'    => $isWithinDeadline ? (int) $datPhong->tienCoc : 0,
        ];

        // Gắn thêm thông tin hoàn tiền nếu có trong bảng hoan_tien
        $thanhToan = $this->findRelatedPayment($datPhong);
        $hoanTien = null;
        if ($thanhToan) {
            $ht = Hoan_Tien::where('maThanhToan', $thanhToan->id)->latest('id')->first();
            if ($ht) {
                $hoanTien = [
                    'id'          => $ht->id,
                    'maThanhToan' => $ht->maThanhToan,
                    'soTien'      => (int) $ht->soTien,
                    'lyDo'        => $ht->lyDo,
                    'thoiGian'    => Carbon::parse($ht->thoiGian)->format('Y-m-d H:i:s'),
                    'trangThai'   => $ht->trangThai,
                ];
            }
        }
        $item['hoanTien'] = $hoanTien;

        return $item;
    }
}
