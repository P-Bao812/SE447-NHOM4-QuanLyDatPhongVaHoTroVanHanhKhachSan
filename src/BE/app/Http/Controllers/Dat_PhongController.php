<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Http\Requests\CheckOutRequest;
use App\Http\Requests\Dat_PhongRequest;
use App\Http\Requests\Duyet_Huy_Dat_PhongRequest;
use App\Http\Requests\UpdateDat_PhongRequest;
use App\Http\Requests\Yeu_Cau_Huy_Dat_PhongRequest;
use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Chi_Tiet_Hoa_Don;
use App\Models\Dat_Phong;
use App\Models\Hoa_Don;
use App\Models\Hoan_Tien;
use App\Models\Luu_Tru;
use App\Models\Phieu_Nhan_Tra_Phong;
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

        // Tìm kiếm theo từ khóa: mã đơn, tên khách hàng hoặc số điện thoại (UC05)
        if ($request->filled('keyword')) {
            $kw = trim($request->input('keyword'));
            $query->where(function ($q) use ($kw) {
                if (is_numeric($kw)) {
                    $q->orWhere('id', (int) $kw);
                }
                $q->orWhereHas('khachHang', function ($kq) use ($kw) {
                    $kq->where('hoTen', 'like', "%{$kw}%")
                       ->orWhere('soDienThoai', 'like', "%{$kw}%");
                });
            });
        }

        // Lọc theo khoảng thời gian nhận phòng (UC05)
        if ($request->filled('ngayNhanTu')) {
            $query->where('ngayNhanPhong', '>=', Carbon::parse($request->input('ngayNhanTu'))->startOfDay());
        }
        if ($request->filled('ngayNhanDen')) {
            $query->where('ngayNhanPhong', '<=', Carbon::parse($request->input('ngayNhanDen'))->endOfDay());
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
     * Cập nhật thông tin đơn đặt phòng (UC05 / BR03.3, BR03.4, BR02.1).
     * Chỉ dành cho Lễ tân và Quản lý.
     * Cho phép điều chỉnh: trạng thái, ngày lưu trú, danh sách phòng, số lượng khách, tiền cọc.
     *
     * @param UpdateDat_PhongRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function update(UpdateDat_PhongRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa xác thực người dùng.', 401);
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Lễ tân', 'Quản lý'], true)) {
            return $this->errorResponse('Chỉ Lễ tân hoặc Quản lý mới có quyền quản lý đơn đặt phòng.', 403);
        }

        $datPhong = Dat_Phong::with(['chiTietDatPhong.phong.loaiPhong', 'khachHang'])->find($id);
        if (!$datPhong) {
            return $this->errorResponse('Không tìm thấy đơn đặt phòng.', 404);
        }

        // 1. Kiểm tra trạng thái đơn đặt phòng hiện tại (BR05 / Terminal Status)
        // Từ chối mọi thay đổi đối với đơn đã ở trạng thái Đã hủy, Đã trả, Đã hoàn thành
        if (in_array($datPhong->trangThai, ['Đã hủy', 'Đã trả', 'Đã hoàn thành'], true)) {
            return $this->errorResponse('Không thể chỉnh sửa đơn đặt phòng đã hủy hoặc đã hoàn thành.', 409);
        }

        // 2. Kiểm tra tính hợp lệ của luồng chuyển đổi trạng thái nếu có thay đổi trạng thái
        if ($request->filled('trangThai') && $request->input('trangThai') !== $datPhong->trangThai) {
            $currentStatus = $datPhong->trangThai;
            $newStatus = $request->input('trangThai');

            $validTransitions = [
                'Chờ xác nhận'  => ['Đã xác nhận', 'Đã cọc', 'Đã hủy'],
                'Đã cọc'        => ['Đã xác nhận', 'Đã nhận phòng', 'Đang ở', 'Đã hủy'],
                'Đã xác nhận'   => ['Đã nhận phòng', 'Đang ở', 'Đã hủy'],
                'Đã nhận phòng' => ['Đã trả', 'Đã hoàn thành'],
                'Đang ở'        => ['Đã trả', 'Đã hoàn thành'],
            ];

            $allowed = $validTransitions[$currentStatus] ?? [];
            if (!in_array($newStatus, $allowed, true)) {
                return $this->errorResponse(
                    "Không thể chuyển trạng thái đơn đặt phòng từ '{$currentStatus}' sang '{$newStatus}'.",
                    422,
                    ['trangThai' => ["Chuyển đổi trạng thái từ {$currentStatus} sang {$newStatus} không hợp lệ."]]
                );
            }
        }

        // 3. Xác định ngày nhận và ngày trả phòng mới (hoặc giữ nguyên)
        $ngayNhanPhong = $request->filled('ngayNhanPhong')
            ? Carbon::parse($request->input('ngayNhanPhong'))->format('Y-m-d H:i:s')
            : Carbon::parse($datPhong->ngayNhanPhong)->format('Y-m-d H:i:s');

        $ngayTraPhong = $request->filled('ngayTraPhong')
            ? Carbon::parse($request->input('ngayTraPhong'))->format('Y-m-d H:i:s')
            : Carbon::parse($datPhong->ngayTraPhong)->format('Y-m-d H:i:s');

        if (Carbon::parse($ngayTraPhong)->lte(Carbon::parse($ngayNhanPhong))) {
            return $this->errorResponse(
                'Ngày trả phòng phải sau ngày nhận phòng.',
                422,
                ['ngayTraPhong' => ['Ngày trả phòng phải sau ngày nhận phòng.']]
            );
        }

        // 4. Xác định danh sách phòng mới (hoặc giữ nguyên)
        $hasNewRoomList = $request->filled('danhSachPhong');
        if ($hasNewRoomList) {
            $newRooms = $request->input('danhSachPhong');
        } else {
            $newRooms = $datPhong->chiTietDatPhong->map(fn($ct) => [
                'maPhong'        => (int) $ct->maPhong,
                'soLuongKhach'   => (int) $ct->soLuongKhach,
                'giaPhongApDung' => (int) $ct->giaPhongApDung,
            ])->toArray();
        }

        $targetRoomIds = array_map('intval', array_column($newRooms, 'maPhong'));
        $targetStatus = $request->input('trangThai', $datPhong->trangThai);

        // 5. Pessimistic Locking & Database Transaction
        DB::beginTransaction();
        try {
            // Khóa đơn đặt phòng để phòng ngừa Race Condition
            $lockedBooking = Dat_Phong::where('id', $datPhong->id)->lockForUpdate()->first();
            if (in_array($lockedBooking->trangThai, ['Đã hủy', 'Đã trả', 'Đã hoàn thành'], true)) {
                DB::rollBack();
                return $this->errorResponse('Không thể chỉnh sửa đơn đặt phòng đã hủy hoặc đã hoàn thành.', 409);
            }

            // Khóa các phòng liên quan
            Phong::whereIn('id', $targetRoomIds)->lockForUpdate()->get();

            // 6. Kiểm tra xung đột lịch đặt phòng (BR02.1) nếu trạng thái không phải là 'Đã hủy'
            if ($targetStatus !== 'Đã hủy') {
                $conflicts = DB::table('chi_tiet_dat_phong')
                    ->join('dat_phong', 'chi_tiet_dat_phong.maDatPhong', '=', 'dat_phong.id')
                    ->join('phong', 'chi_tiet_dat_phong.maPhong', '=', 'phong.id')
                    ->where('dat_phong.id', '!=', $lockedBooking->id)
                    ->whereIn('chi_tiet_dat_phong.maPhong', $targetRoomIds)
                    ->whereNotIn('dat_phong.trangThai', ['Đã hủy'])
                    ->where('dat_phong.ngayNhanPhong', '<', $ngayTraPhong)
                    ->where('dat_phong.ngayTraPhong', '>', $ngayNhanPhong)
                    ->select('phong.tenPhong')
                    ->get();

                if ($conflicts->isNotEmpty()) {
                    DB::rollBack();
                    $phongBiTrung = $conflicts->pluck('tenPhong')->unique()->values()->all();

                    return $this->errorResponse(
                        'Phòng đã được khách khác đặt trong khung giờ yêu cầu.',
                        409,
                        ['phongBiTrung' => $phongBiTrung]
                    );
                }
            }

            // 7. Ghi nhận mã nhân viên thực hiện cập nhật (BR03.4)
            $oldRoomIds = $lockedBooking->chiTietDatPhong()->pluck('maPhong')->toArray();
            $maNhanVien = $user->nhanVien ? (int) $user->nhanVien->id : $lockedBooking->maNhanVien;

            $lockedBooking->ngayNhanPhong = $ngayNhanPhong;
            $lockedBooking->ngayTraPhong  = $ngayTraPhong;
            $lockedBooking->trangThai     = $targetStatus;
            $lockedBooking->maNhanVien    = $maNhanVien;

            if ($request->filled('soLuongKhach')) {
                $lockedBooking->soLuongKhach = (int) $request->input('soLuongKhach');
            }
            if ($request->filled('tienCoc')) {
                $lockedBooking->tienCoc = (int) $request->input('tienCoc');
            }
            $lockedBooking->save();

            // 8. Cập nhật chi tiết đặt phòng (Không xóa cứng, bảo toàn giá phòng áp dụng cũ)
            if ($hasNewRoomList) {
                $existingDetails = Chi_Tiet_Dat_Phong::where('maDatPhong', $lockedBooking->id)->get();
                $existingByRoom = $existingDetails->keyBy('maPhong');
                $roomModels = Phong::with('loaiPhong')->whereIn('id', $targetRoomIds)->get()->keyBy('id');

                $unmatchedExisting = $existingDetails->filter(fn($ct) => !in_array((int) $ct->maPhong, $targetRoomIds, true))->values();
                $unmatchedNew = collect($newRooms)->filter(fn($item) => !$existingByRoom->has((int) $item['maPhong']))->values();

                // Các phòng giữ nguyên: bảo toàn giaPhongApDung cũ, chỉ cập nhật soLuongKhach
                foreach ($newRooms as $item) {
                    $mPhong = (int) $item['maPhong'];
                    if ($existingByRoom->has($mPhong)) {
                        $ct = $existingByRoom->get($mPhong);
                        $ct->soLuongKhach = (int) $item['soLuongKhach'];
                        $ct->save();
                    }
                }

                // Ghép nối các bản ghi cũ cần đổi sang phòng mới (tránh xóa cứng)
                $pairCount = min($unmatchedExisting->count(), $unmatchedNew->count());
                for ($i = 0; $i < $pairCount; $i++) {
                    $ct = $unmatchedExisting[$i];
                    $newItem = $unmatchedNew[$i];
                    $newRoom = $roomModels[(int) $newItem['maPhong']] ?? null;

                    $ct->maPhong = (int) $newItem['maPhong'];
                    $ct->giaPhongApDung = (int) ($newRoom->loaiPhong->giaTieuChuan ?? 0);
                    $ct->soLuongKhach = (int) $newItem['soLuongKhach'];
                    $ct->save();
                }

                // Nếu có thêm phòng mới vượt số lượng cũ, tạo mới bản ghi chi tiết
                if ($unmatchedNew->count() > $pairCount) {
                    for ($i = $pairCount; $i < $unmatchedNew->count(); $i++) {
                        $newItem = $unmatchedNew[$i];
                        $newRoom = $roomModels[(int) $newItem['maPhong']] ?? null;

                        Chi_Tiet_Dat_Phong::create([
                            'maDatPhong'     => $lockedBooking->id,
                            'maPhong'        => (int) $newItem['maPhong'],
                            'giaPhongApDung' => (int) ($newRoom->loaiPhong->giaTieuChuan ?? 0),
                            'soLuongKhach'   => (int) $newItem['soLuongKhach'],
                        ]);
                    }
                }
            }

            // 9. Đồng bộ trạng thái sơ đồ phòng theo quy tắc nghiệp vụ (BR03.3 / FR18)
            $allAffectedRoomIds = array_map('intval', array_merge($oldRoomIds, $targetRoomIds));
            $this->syncRoomStatuses($allAffectedRoomIds);

            DB::commit();

            // 10. Ghi Audit Log vào hệ thống (BR03.4)
            Log::info("Cập nhật đơn đặt phòng thành công (UC05)", [
                'maDatPhong'    => $lockedBooking->id,
                'maNhanVien'    => $maNhanVien,
                'trangThaiCu'   => $datPhong->trangThai,
                'trangThaiMoi'  => $lockedBooking->trangThai,
                'ngayNhanPhong' => $lockedBooking->ngayNhanPhong,
                'ngayTraPhong'  => $lockedBooking->ngayTraPhong,
                'phong'         => $targetRoomIds,
                'updated_at'    => $lockedBooking->updated_at,
            ]);

            // Trả về dữ liệu chi tiết đơn sau cập nhật
            $freshBooking = Dat_Phong::with(['khachHang', 'nhanVien', 'chiTietDatPhong.phong.loaiPhong'])->find($lockedBooking->id);
            $data = $this->formatBookingDetail($freshBooking);

            return $this->successResponse($data, 'Cập nhật đơn đặt phòng thành công.', 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật đơn đặt phòng (UC05): ' . $e->getMessage(), [
                'exception' => $e,
                'bookingId' => $id,
            ]);

            return $this->errorResponse(
                'Cập nhật đơn đặt phòng không thành công do lỗi hệ thống. Vui lòng thử lại sau.',
                500
            );
        }
    }

    /**
     * Cập nhật nhanh trạng thái đơn đặt phòng (UC05 / Alternative Flow 3a).
     *
     * @param UpdateDat_PhongRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateDat_PhongRequest $request, int|string $id): JsonResponse
    {
        if (!$request->filled('trangThai')) {
            return $this->errorResponse(
                'Trạng thái đơn đặt phòng không được để trống.',
                422,
                ['trangThai' => ['Trạng thái đơn đặt phòng không được để trống.']]
            );
        }

        return $this->update($request, $id);
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
            'maKhachHang'   => (int) $datPhong->maKhachHang,
            'maNhanVien'    => $datPhong->maNhanVien ? (int) $datPhong->maNhanVien : null,
            'ngayDat'       => Carbon::parse($datPhong->ngayDat)->format('Y-m-d H:i:s'),
            'ngayNhanPhong' => Carbon::parse($datPhong->ngayNhanPhong)->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::parse($datPhong->ngayTraPhong)->format('Y-m-d H:i:s'),
            'soDem'         => $soDem,
            'soLuongKhach'  => (int) $datPhong->soLuongKhach,
            'tienCoc'       => (int) $datPhong->tienCoc,
            'tongChiPhi'    => $tongChiPhi,
            'trangThai'     => $datPhong->trangThai,
            'created_at'    => $datPhong->created_at ? Carbon::parse($datPhong->created_at)->format('Y-m-d H:i:s') : null,
            'updated_at'    => $datPhong->updated_at ? Carbon::parse($datPhong->updated_at)->format('Y-m-d H:i:s') : null,
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
     * Đồng bộ trạng thái các phòng vật lý theo tình trạng đơn đặt (BR03.3 / FR18).
     *
     * @param array<int> $roomIds
     */
    private function syncRoomStatuses(array $roomIds): void
    {
        foreach (array_unique($roomIds) as $roomId) {
            $room = Phong::find($roomId);
            if (!$room || in_array($room->trangThai, ['Đang bảo trì', 'Ngừng kinh doanh', 'Ngưng khai thác'], true)) {
                continue;
            }

            // Kiểm tra các đơn đặt phòng đang hoạt động cho phòng này
            $activeBooking = DB::table('chi_tiet_dat_phong')
                ->join('dat_phong', 'chi_tiet_dat_phong.maDatPhong', '=', 'dat_phong.id')
                ->where('chi_tiet_dat_phong.maPhong', $roomId)
                ->whereNotIn('dat_phong.trangThai', ['Đã hủy', 'Đã trả', 'Đã hoàn thành'])
                ->orderByRaw("CASE 
                    WHEN dat_phong.trangThai IN ('Đang ở', 'Đã nhận phòng') THEN 1 
                    WHEN dat_phong.trangThai IN ('Đã xác nhận', 'Đã cọc') THEN 2 
                    ELSE 3 END")
                ->select('dat_phong.trangThai')
                ->first();

            if (!$activeBooking) {
                // Không có đơn đặt phòng nào đang hoạt động
                if ($room->trangThai === 'Đã đặt' || $room->trangThai === 'Đang ở') {
                    $room->trangThai = 'Sẵn sàng';
                    $room->save();
                }
            } else {
                if (in_array($activeBooking->trangThai, ['Đang ở', 'Đã nhận phòng'], true)) {
                    if ($room->trangThai !== 'Đang ở') {
                        $room->trangThai = 'Đang ở';
                        $room->save();
                    }
                } elseif (in_array($activeBooking->trangThai, ['Đã xác nhận', 'Đã cọc', 'Chờ xác nhận'], true)) {
                    if ($room->trangThai === 'Sẵn sàng') {
                        $room->trangThai = 'Đã đặt';
                        $room->save();
                    }
                }
            }
        }
    }

    /**
     * Tra cứu thông tin lưu trú, dịch vụ, hóa đơn và phiếu nhận/trả của đơn đặt phòng (UC09).
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function stayInfo($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa xác thực người dùng.', 401);
        }

        $datPhong = Dat_Phong::with([
            'khachHang',
            'nhanVien',
            'chiTietDatPhong.phong.loaiPhong',
        ])->find($id);

        if (!$datPhong) {
            return $this->errorResponse('Đơn đặt phòng không tồn tại trong hệ thống.', 404);
        }

        $vaiTro = $user->vaiTro;
        if ($vaiTro === 'Khách hàng') {
            if (!$user->khachHang || $user->khachHang->id !== $datPhong->maKhachHang) {
                return $this->errorResponse('Bạn không có quyền xem thông tin đợt lưu trú của khách hàng khác.', 403);
            }
        } elseif (!in_array($vaiTro, ['Quản lý', 'Lễ tân'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập thông tin đợt lưu trú này.', 403);
        }

        $chiTietPhongs = $datPhong->chiTietDatPhong->map(function (Chi_Tiet_Dat_Phong $ct) {
            $luuTru = Luu_Tru::with(['hoaDon.chiTietHoaDons', 'phieuNhanTraPhongs.nhanVien'])
                ->where('maChiTiet', $ct->id)
                ->latest('id')
                ->first();

            return [
                'maChiTiet'      => $ct->id,
                'maPhong'        => $ct->maPhong,
                'tenPhong'       => $ct->phong?->tenPhong,
                'tang'           => $ct->phong?->tang,
                'trangThaiPhong' => $ct->phong?->trangThai,
                'loaiPhong'      => [
                    'id'           => $ct->phong?->loaiPhong?->id,
                    'tenLoai'      => $ct->phong?->loaiPhong?->tenLoai,
                    'giaTieuChuan' => (int) ($ct->phong?->loaiPhong?->giaTieuChuan ?? 0),
                ],
                'giaPhongApDung' => (int) $ct->giaPhongApDung,
                'luuTru'         => $luuTru ? [
                    'maLuuTru'     => $luuTru->id,
                    'thoiGianNhan' => $luuTru->thoiGianNhan?->format('Y-m-d H:i:s'),
                    'thoiGianTra'  => $luuTru->thoiGianTra?->format('Y-m-d H:i:s'),
                    'trangThai'    => $luuTru->trangThai,
                    'phieu'        => $luuTru->phieuNhanTraPhongs->map(fn($p) => [
                        'id'         => $p->id,
                        'loaiPhieu'  => $p->loaiPhieu,
                        'thoiGian'   => $p->thoiGian?->format('Y-m-d H:i:s'),
                        'nhanVien'   => $p->nhanVien?->hoTen,
                        'ghiChu'     => $p->ghiChu,
                    ])->values(),
                    'hoaDon'       => $luuTru->hoaDon ? [
                        'maHoaDon'  => $luuTru->hoaDon->id,
                        'ngayLap'   => $luuTru->hoaDon->ngayLap?->format('Y-m-d H:i:s'),
                        'tongTien'  => (int) $luuTru->hoaDon->tongTien,
                        'trangThai' => $luuTru->hoaDon->trangThai,
                        'chiTiet'   => $luuTru->hoaDon->chiTietHoaDons->map(fn($item) => [
                            'noiDung'   => $item->noiDung,
                            'soLuong'   => (int) $item->soLuong,
                            'donGia'    => (int) $item->donGia,
                            'thanhTien' => (int) $item->thanhTien,
                        ])->values(),
                    ] : null,
                ] : null,
            ];
        });

        $data = [
            'maDatPhong'    => $datPhong->id,
            'ngayDat'       => $datPhong->ngayDat?->format('Y-m-d H:i:s'),
            'ngayNhanPhong' => $datPhong->ngayNhanPhong?->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => $datPhong->ngayTraPhong?->format('Y-m-d H:i:s'),
            'soLuongKhach'  => (int) $datPhong->soLuongKhach,
            'tienCoc'       => (int) $datPhong->tienCoc,
            'trangThai'     => $datPhong->trangThai,
            'khachHang'     => $datPhong->khachHang ? [
                'id'          => $datPhong->khachHang->id,
                'hoTen'       => $datPhong->khachHang->hoTen,
                'cccd'        => $datPhong->khachHang->cccd,
                'soDienThoai' => $datPhong->khachHang->soDienThoai,
                'email'       => $datPhong->khachHang->email,
            ] : null,
            'nhanVien'      => $datPhong->nhanVien ? [
                'id'    => $datPhong->nhanVien->id,
                'hoTen' => $datPhong->nhanVien->hoTen,
            ] : null,
            'danhSachPhong' => $chiTietPhongs,
        ];

        return $this->successResponse($data, 'Lấy thông tin lưu trú thành công.');
    }

    /**
     * Thực hiện thủ tục nhận phòng (Check-in) cho đơn đặt phòng (UC09 / BR03.5, BR06.1).
     *
     * @param int|string $id
     * @param CheckInRequest $request
     * @return JsonResponse
     */
    public function checkIn($id, CheckInRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var Dat_Phong|null $datPhong */
            $datPhong = Dat_Phong::with('chiTietDatPhong.phong')->where('id', $id)->lockForUpdate()->first();

            if (!$datPhong) {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng không tồn tại trong hệ thống.', 404);
            }

            // Kiểm tra trạng thái đơn đặt phòng
            if ($datPhong->trangThai === 'Đang ở' || $datPhong->trangThai === 'Đang lưu trú') {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng này đã hoàn tất thủ tục nhận phòng (Check-in) trước đó.', 409);
            }

            if (in_array($datPhong->trangThai, ['Đã hủy', 'Đã trả', 'Hoàn thành'], true)) {
                DB::rollBack();
                return $this->errorResponse("Không thể làm thủ tục Check-in cho đơn đặt phòng đang ở trạng thái '{$datPhong->trangThai}'.", 409);
            }

            // Cho phép đổi phòng trước check-in nếu có gửi danhSachPhong
            if ($request->filled('danhSachPhong')) {
                foreach ($request->input('danhSachPhong') as $change) {
                    $detail = $datPhong->chiTietDatPhong->firstWhere('id', (int) $change['maChiTiet']);
                    if ($detail && (int) $detail->maPhong !== (int) $change['maPhong']) {
                        $targetRoom = Phong::where('id', (int) $change['maPhong'])->lockForUpdate()->first();
                        if (!$targetRoom || $targetRoom->trangThai !== 'Sẵn sàng') {
                            DB::rollBack();
                            return $this->errorResponse("Phòng chuyển đổi '{$targetRoom?->tenPhong}' hiện không sẵn sàng.", 422);
                        }
                        $detail->update(['maPhong' => $targetRoom->id]);
                    }
                }
                $datPhong->load('chiTietDatPhong.phong');
            }

            // Kiểm tra trạng thái toàn bộ phòng vật lý được giao
            foreach ($datPhong->chiTietDatPhong as $ct) {
                $phong = Phong::where('id', $ct->maPhong)->lockForUpdate()->first();
                if (!$phong) {
                    DB::rollBack();
                    return $this->errorResponse("Phòng không tồn tại trong hệ thống.", 404);
                }

                // Không cho check-in nếu phòng đang bận, cần dọn dẹp hoặc bảo trì
                if ($phong->trangThai !== 'Sẵn sàng' && $phong->trangThai !== 'Đã đặt') {
                    DB::rollBack();
                    return $this->errorResponse("Phòng {$phong->tenPhong} hiện đang ở trạng thái '{$phong->trangThai}', không đủ điều kiện bàn giao cho khách nhận phòng.", 409);
                }
            }

            $thoiGianNhan = $request->filled('thoiGianNhan')
                ? Carbon::parse($request->input('thoiGianNhan'))
                : Carbon::now();
            $thoiGianTraDuKien = Carbon::parse($datPhong->ngayTraPhong);

            $staffId = $request->user()->nhanVien?->id ?? $datPhong->maNhanVien ?? 1;
            $ghiChu = $request->input('ghiChu') ?: 'Lễ tân hoàn tất thủ tục nhận phòng.';

            $createdStays = [];

            foreach ($datPhong->chiTietDatPhong as $ct) {
                $phong = Phong::where('id', $ct->maPhong)->lockForUpdate()->first();
                $phong->update(['trangThai' => 'Đang ở']);

                // Tạo bản ghi luu_tru
                $luuTru = Luu_Tru::create([
                    'maChiTiet'    => $ct->id,
                    'thoiGianNhan' => $thoiGianNhan,
                    'thoiGianTra'  => $thoiGianTraDuKien,
                    'trangThai'    => 'Đang lưu trú',
                ]);

                // Tạo phiếu nhận phòng
                Phieu_Nhan_Tra_Phong::create([
                    'maLuuTru'   => $luuTru->id,
                    'maNhanVien' => $staffId,
                    'loaiPhieu'  => 'Nhận phòng',
                    'thoiGian'   => $thoiGianNhan,
                    'ghiChu'     => $ghiChu,
                ]);

                // Tính tiền phòng và lập hóa đơn
                $ngayNhan = Carbon::parse($datPhong->ngayNhanPhong)->startOfDay();
                $ngayTra = Carbon::parse($datPhong->ngayTraPhong)->startOfDay();
                $soDem = max(1, $ngayNhan->diffInDays($ngayTra));
                $tienPhong = (int) $ct->giaPhongApDung * $soDem;

                $hoaDon = Hoa_Don::create([
                    'maLuuTru'  => $luuTru->id,
                    'ngayLap'   => Carbon::now(),
                    'tongTien'  => $tienPhong,
                    'trangThai' => 'Chờ thanh toán',
                ]);

                Chi_Tiet_Hoa_Don::create([
                    'maHoaDon'  => $hoaDon->id,
                    'noiDung'   => "Tiền phòng {$phong->tenPhong} ({$soDem} đêm)",
                    'soLuong'   => $soDem,
                    'donGia'    => (int) $ct->giaPhongApDung,
                    'thanhTien' => $tienPhong,
                ]);

                // Tự động tính phụ thu nhận phòng sớm (BR06.1) so với giờ nhận chuẩn 14:00
                $gioNhanChuan = Carbon::parse($datPhong->ngayNhanPhong)->setTime(14, 0, 0);
                if ($thoiGianNhan->lt($gioNhanChuan) && $thoiGianNhan->isSameDay($gioNhanChuan)) {
                    $hour = $thoiGianNhan->hour;
                    $phuThu = 0;
                    $lyDo = '';

                    if ($hour < 5) {
                        $phuThu = (int) ($ct->giaPhongApDung * 1.0);
                        $lyDo = 'Phụ thu nhận phòng sớm trước 05:00 (100% giá phòng)';
                    } elseif ($hour >= 5 && $hour < 9) {
                        $phuThu = (int) ($ct->giaPhongApDung * 0.5);
                        $lyDo = 'Phụ thu nhận phòng sớm từ 05:00 - 09:00 (50% giá phòng)';
                    } elseif ($hour >= 9 && $hour < 12) {
                        $phuThu = (int) ($ct->giaPhongApDung * 0.3);
                        $lyDo = 'Phụ thu nhận phòng sớm từ 09:00 - 12:00 (30% giá phòng)';
                    }

                    if ($phuThu > 0) {
                        Chi_Tiet_Hoa_Don::create([
                            'maHoaDon'  => $hoaDon->id,
                            'noiDung'   => $lyDo,
                            'soLuong'   => 1,
                            'donGia'    => $phuThu,
                            'thanhTien' => $phuThu,
                        ]);
                        $hoaDon->update(['tongTien' => (int) $hoaDon->chiTietHoaDons()->sum('thanhTien')]);
                    }
                }

                $createdStays[] = [
                    'maLuuTru'     => $luuTru->id,
                    'maPhong'      => $phong->id,
                    'tenPhong'     => $phong->tenPhong,
                    'thoiGianNhan' => $thoiGianNhan->format('Y-m-d H:i:s'),
                    'maHoaDon'     => $hoaDon->id,
                ];
            }

            // Chuyển trạng thái đơn đặt phòng sang Đang ở
            $datPhong->update([
                'trangThai'  => 'Đang ở',
                'maNhanVien' => $staffId,
            ]);

            DB::commit();

            return $this->successResponse([
                'maDatPhong' => $datPhong->id,
                'trangThai'  => 'Đang ở',
                'luuTru'     => $createdStays,
            ], 'Thủ tục nhận phòng (Check-in) thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thực hiện Check-in: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi làm thủ tục nhận phòng. Vui lòng thử lại.', 500);
        }
    }

    /**
     * Thực hiện thủ tục trả phòng (Check-out) cho đơn đặt phòng (UC09 / BR07.1, BR07.2).
     *
     * @param int|string $id
     * @param CheckOutRequest $request
     * @return JsonResponse
     */
    public function checkOut($id, CheckOutRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var Dat_Phong|null $datPhong */
            $datPhong = Dat_Phong::with('chiTietDatPhong.phong')->where('id', $id)->lockForUpdate()->first();

            if (!$datPhong) {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng không tồn tại trong hệ thống.', 404);
            }

            // Kiểm tra trạng thái đơn đặt phòng
            if (in_array($datPhong->trangThai, ['Đã trả', 'Hoàn thành'], true)) {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng này đã hoàn tất thủ tục trả phòng (Check-out) trước đó.', 409);
            }

            if ($datPhong->trangThai !== 'Đang ở' && $datPhong->trangThai !== 'Đang lưu trú') {
                DB::rollBack();
                return $this->errorResponse('Đơn đặt phòng chưa thực hiện Check-in, không thể làm thủ tục Check-out.', 409);
            }

            $detailIds = $datPhong->chiTietDatPhong->pluck('id')->toArray();
            $luuTrus = Luu_Tru::with('hoaDon')->whereIn('maChiTiet', $detailIds)->lockForUpdate()->get();

            if ($luuTrus->isEmpty()) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy đợt lưu trú nào cho đơn đặt phòng này.', 409);
            }

            $thoiGianTra = $request->filled('thoiGianTra')
                ? Carbon::parse($request->input('thoiGianTra'))
                : Carbon::now();

            // Kiểm tra thời gian trả phòng không được trước thời gian nhận phòng thực tế
            foreach ($luuTrus as $lt) {
                if ($thoiGianTra->lt($lt->thoiGianNhan)) {
                    DB::rollBack();
                    return $this->errorResponse(
                        "Thời gian trả phòng ({$thoiGianTra->format('Y-m-d H:i:s')}) không được trước thời gian nhận phòng thực tế ({$lt->thoiGianNhan->format('Y-m-d H:i:s')}).",
                        422
                    );
                }
            }

            $staffId = $request->user()->nhanVien?->id ?? $datPhong->maNhanVien ?? 1;
            $ghiChu = $request->input('ghiChu') ?: 'Lễ tân hoàn tất thủ tục trả phòng.';

            foreach ($luuTrus as $lt) {
                $lt->update([
                    'trangThai'   => 'Đã trả',
                    'thoiGianTra' => $thoiGianTra,
                ]);

                // BR07.2: Chuyển trạng thái phòng sang "Cần dọn dẹp" để bộ phận buồng phòng tiếp nhận
                $ct = $datPhong->chiTietDatPhong->firstWhere('id', $lt->maChiTiet);
                if ($ct && $ct->phong) {
                    $phong = Phong::where('id', $ct->maPhong)->lockForUpdate()->first();
                    $phong?->update(['trangThai' => 'Cần dọn dẹp']);
                }

                // Tạo phiếu trả phòng
                Phieu_Nhan_Tra_Phong::create([
                    'maLuuTru'   => $lt->id,
                    'maNhanVien' => $staffId,
                    'loaiPhieu'  => 'Trả phòng',
                    'thoiGian'   => $thoiGianTra,
                    'ghiChu'     => $ghiChu,
                ]);

                // Tự động tính phụ thu trả phòng trễ (BR07.1) so với giờ trả chuẩn 12:00
                $gioTraChuan = Carbon::parse($datPhong->ngayTraPhong)->setTime(12, 0, 0);
                if ($thoiGianTra->gt($gioTraChuan)) {
                    $hour = $thoiGianTra->hour;
                    $phuThu = 0;
                    $lyDo = '';
                    $giaPhong = (int) ($ct?->giaPhongApDung ?? 0);

                    if ($hour >= 18) {
                        $phuThu = (int) ($giaPhong * 1.0);
                        $lyDo = 'Phụ thu trả phòng trễ sau 18:00 (100% giá phòng)';
                    } elseif ($hour >= 15) {
                        $phuThu = (int) ($giaPhong * 0.5);
                        $lyDo = 'Phụ thu trả phòng trễ từ 15:00 - 18:00 (50% giá phòng)';
                    } elseif ($hour >= 12 && ($hour > 12 || $thoiGianTra->minute > 30)) {
                        $phuThu = (int) ($giaPhong * 0.3);
                        $lyDo = 'Phụ thu trả phòng trễ từ 12:30 - 15:00 (30% giá phòng)';
                    }

                    if ($phuThu > 0 && $lt->hoaDon) {
                        Chi_Tiet_Hoa_Don::create([
                            'maHoaDon'  => $lt->hoaDon->id,
                            'noiDung'   => $lyDo,
                            'soLuong'   => 1,
                            'donGia'    => $phuThu,
                            'thanhTien' => $phuThu,
                        ]);
                        $lt->hoaDon->update(['tongTien' => (int) $lt->hoaDon->chiTietHoaDons()->sum('thanhTien')]);
                    }
                }
            }

            // Chuyển trạng thái đơn đặt phòng sang Đã trả
            $datPhong->update([
                'trangThai' => 'Đã trả',
            ]);

            DB::commit();

            return $this->successResponse([
                'maDatPhong'  => $datPhong->id,
                'trangThai'   => 'Đã trả',
                'thoiGianTra' => $thoiGianTra->format('Y-m-d H:i:s'),
            ], 'Thủ tục trả phòng (Check-out) thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thực hiện Check-out: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi làm thủ tục trả phòng. Vui lòng thử lại.', 500);
        }
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
