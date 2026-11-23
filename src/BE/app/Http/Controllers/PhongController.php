<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhongRequest;
use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Luu_Tru;
use App\Models\Phong;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PhongController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách phòng vật lý kèm loại phòng, hỗ trợ tìm kiếm, lọc và phân trang (UC06 / UC08 / UC18).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userRole = $user?->vaiTro;

        $query = Phong::with('loaiPhong');

        // Tìm kiếm theo số / tên phòng
        if ($request->filled('keyword')) {
            $kw = trim($request->input('keyword'));
            $query->where('tenPhong', 'like', "%{$kw}%");
        }

        // Lọc theo tầng
        if ($request->filled('tang')) {
            $query->where('tang', (int) $request->input('tang'));
        }

        // Lọc theo trạng thái vận hành trong CSDL
        if ($request->filled('trangThai')) {
            $st = $request->input('trangThai');
            if ($st === 'Trống') {
                $query->whereIn('trangThai', ['Trống', 'Sẵn sàng']);
            } else {
                $query->where('trangThai', $st);
            }
        }

        // Lọc theo mã loại phòng
        if ($request->filled('maLoaiPhong')) {
            $query->where('maLoaiPhong', (int) $request->input('maLoaiPhong'));
        }

        $query->orderBy('tang')->orderBy('tenPhong');

        // Hỗ trợ phân trang nếu có tham số query 'page'
        if ($request->filled('page')) {
            $perPage = (int) $request->input('per_page', 15);
            $paginated = $query->paginate($perPage);

            $data = collect($paginated->items())->map(fn(Phong $p) => $this->formatRoomItem($p, $userRole, true))->values();

            $meta = [
                'pagination' => [
                    'total'        => $paginated->total(),
                    'per_page'     => $paginated->perPage(),
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                ],
            ];

            return $this->successResponse($data, 'Lấy danh sách phòng thành công.', 200, $meta);
        }

        $rooms = $query->get();

        if ($rooms->isEmpty()) {
            return $this->successResponse([], 'Không có phòng nào ở tình trạng này.', 200);
        }

        $formatted = $rooms->map(fn(Phong $p) => $this->formatRoomItem($p, $userRole, true))->values();

        return $this->successResponse($formatted, 'Lấy danh sách phòng thành công.', 200);
    }

    /**
     * API chuyên biệt dành riêng cho Sơ Đồ Phòng (Room Map - UC18).
     * Chỉ cho phép Lễ tân, Quản lý và Buồng phòng truy cập (RBAC).
     * Trả về danh sách phòng được chuẩn hóa trạng thái sơ đồ, mã màu và khách đang ở.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function map(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $userRole = $user->vaiTro;

        // Chỉ Quản lý, Lễ tân và Buồng phòng được phép xem sơ đồ phòng khách sạn
        if (!in_array($userRole, ['Quản lý', 'Admin', 'Lễ tân', 'Buồng phòng'], true)) {
            return $this->errorResponse(
                'Bạn không có quyền truy cập sơ đồ phòng. Chức năng chỉ dành cho Lễ tân, Quản lý và Buồng phòng.',
                403
            );
        }

        try {
            $query = Phong::with('loaiPhong');

            // 1. Tìm kiếm theo tên / số phòng
            if ($request->filled('keyword')) {
                $kw = trim($request->input('keyword'));
                $query->where('tenPhong', 'like', "%{$kw}%");
            }

            // 2. Lọc theo tầng
            if ($request->filled('tang')) {
                $query->where('tang', (int) $request->input('tang'));
            }

            // 3. Lọc theo loại phòng
            if ($request->filled('maLoaiPhong')) {
                $query->where('maLoaiPhong', (int) $request->input('maLoaiPhong'));
            }

            $query->orderBy('tang')->orderBy('tenPhong');
            $rooms = $query->get();

            // Chuẩn hóa read model sơ đồ phòng kèm thông tin lưu trú
            $formattedRooms = $rooms->map(fn(Phong $p) => $this->formatRoomItem($p, $userRole, true));

            // 4. Lọc theo trạng thái sơ đồ (Trống, Đã đặt, Đang ở, Cần dọn dẹp, Đang bảo trì, Ngưng khai thác)
            if ($request->filled('trangThai')) {
                $filterStatus = trim($request->input('trangThai'));
                $formattedRooms = $formattedRooms->filter(function ($room) use ($filterStatus) {
                    if ($filterStatus === 'Trống' || $filterStatus === 'Sẵn sàng') {
                        return in_array($room['trangThaiSoDo'], ['Trống', 'Sẵn sàng'], true);
                    }
                    return $room['trangThaiSoDo'] === $filterStatus || $room['trangThai'] === $filterStatus;
                })->values();
            }

            if ($formattedRooms->isEmpty()) {
                return $this->successResponse([], 'Không có phòng nào ở tình trạng này.', 200);
            }

            // Nhóm theo tầng để hỗ trợ FE vẽ sơ đồ tầng trực quan
            $byFloor = $formattedRooms->groupBy('tang');

            $meta = [
                'total'     => $formattedRooms->count(),
                'soTang'    => $byFloor->keys()->values(),
                'thongKe'   => [
                    'trong'         => $formattedRooms->whereIn('trangThaiSoDo', ['Trống', 'Sẵn sàng'])->count(),
                    'daDat'         => $formattedRooms->where('trangThaiSoDo', 'Đã đặt')->count(),
                    'dangO'         => $formattedRooms->where('trangThaiSoDo', 'Đang ở')->count(),
                    'canDonDep'     => $formattedRooms->where('trangThaiSoDo', 'Cần dọn dẹp')->count(),
                    'dangBaoTri'    => $formattedRooms->where('trangThaiSoDo', 'Đang bảo trì')->count(),
                    'ngungKhaiThac' => $formattedRooms->where('trangThaiSoDo', 'Ngưng khai thác')->count(),
                ],
            ];

            return $this->successResponse($formattedRooms->values(), 'Lấy dữ liệu sơ đồ phòng thành công.', 200, $meta);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi lấy dữ liệu sơ đồ phòng (UC18): ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi tải sơ đồ phòng.', 500);
        }
    }

    /**
     * Lấy thông tin chi tiết một phòng vật lý (UC06 / UC18).
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $room = Phong::with('loaiPhong')->find($id);

        if (!$room) {
            return $this->errorResponse('Không tìm thấy phòng.', 404);
        }

        $user = $request->user();
        $userRole = $user?->vaiTro;

        return $this->successResponse(
            $this->formatRoomItem($room, $userRole, true),
            'Lấy thông tin phòng thành công.',
            200
        );
    }

    /**
     * Thêm mới một phòng vật lý vào hệ thống (UC06 / BR01.5, BR01.6).
     * Chỉ Quản lý mới có quyền thực hiện.
     *
     * @param PhongRequest $request
     * @return JsonResponse
     */
    public function store(PhongRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $room = Phong::create([
                'maLoaiPhong' => (int) $request->input('maLoaiPhong'),
                'tenPhong'    => trim($request->input('tenPhong')),
                'tang'        => (int) $request->input('tang'),
                'trangThai'   => $request->input('trangThai', 'Sẵn sàng'),
            ]);

            $room->load('loaiPhong');

            DB::commit();

            return $this->successResponse(
                $this->formatRoomItem($room),
                'Thêm phòng mới thành công.',
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm phòng mới (UC06): ' . $e->getMessage(), ['exception' => $e]);

            return $this->errorResponse('Thêm phòng mới thất bại do lỗi hệ thống.', 500);
        }
    }

    /**
     * Cập nhật thông tin phòng vật lý, gán loại phòng hoặc thay đổi trạng thái (UC06).
     * Áp dụng Pessimistic Locking và kiểm tra xung đột lưu trú/đơn đặt trước khi chuyển sang bảo trì/ngưng khai thác.
     *
     * @param PhongRequest $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function update(PhongRequest $request, int|string $id): JsonResponse
    {
        $existing = Phong::find($id);
        if (!$existing) {
            return $this->errorResponse('Không tìm thấy phòng.', 404);
        }

        DB::beginTransaction();
        try {
            /** @var Phong|null $lockedRoom */
            $lockedRoom = Phong::lockForUpdate()->find($id);
            if (!$lockedRoom) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy phòng.', 404);
            }

            $targetStatus = $request->input('trangThai', $lockedRoom->trangThai);

            // Kiểm tra điều kiện nghiệp vụ khi chuyển sang bảo trì hoặc ngưng khai thác (BR01.5, BR01.6)
            if (in_array($targetStatus, ['Đang bảo trì', 'Ngưng khai thác'], true) && $lockedRoom->trangThai !== $targetStatus) {
                // 1. Kiểm tra phòng đang có khách lưu trú
                $hasActiveStay = Luu_Tru::whereHas('chiTietDatPhong', function ($q) use ($lockedRoom) {
                    $q->where('maPhong', $lockedRoom->id);
                })->where('trangThai', 'Đang ở')->exists();

                if ($hasActiveStay || in_array($lockedRoom->trangThai, ['Đang ở', 'Đang có khách'], true)) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Không thể chuyển phòng sang bảo trì hoặc ngưng khai thác khi phòng đang có khách lưu trú.',
                        409
                    );
                }

                // 2. Kiểm tra đơn đặt phòng còn hiệu lực chưa hoàn tất hoặc hủy
                $hasActiveBooking = Dat_Phong::whereHas('chiTietDatPhong', function ($q) use ($lockedRoom) {
                    $q->where('maPhong', $lockedRoom->id);
                })->whereNotIn('trangThai', ['Đã hủy', 'Đã trả', 'Đã hoàn thành'])->exists();

                if ($hasActiveBooking) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Không thể chuyển phòng sang bảo trì hoặc ngưng khai thác khi phòng đang có đơn đặt phòng hiệu lực chưa hoàn tất.',
                        409
                    );
                }
            }

            if ($request->has('maLoaiPhong')) {
                $lockedRoom->maLoaiPhong = (int) $request->input('maLoaiPhong');
            }
            if ($request->has('tenPhong')) {
                $lockedRoom->tenPhong = trim($request->input('tenPhong'));
            }
            if ($request->has('tang')) {
                $lockedRoom->tang = (int) $request->input('tang');
            }
            if ($request->has('trangThai')) {
                $lockedRoom->trangThai = $targetStatus;
            }

            $lockedRoom->save();
            $lockedRoom->load('loaiPhong');

            DB::commit();

            return $this->successResponse(
                $this->formatRoomItem($lockedRoom),
                'Cập nhật thông tin phòng thành công.',
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật phòng (UC06): ' . $e->getMessage(), ['exception' => $e]);

            return $this->errorResponse('Cập nhật thông tin phòng thất bại do lỗi hệ thống.', 500);
        }
    }

    /**
     * Cập nhật riêng trạng thái vận hành của phòng (UC06 / PUT /rooms/{id}/status).
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->vaiTro !== 'Quản lý') {
            return $this->errorResponse('Bạn không có quyền thực hiện thao tác này. Chỉ Quản lý mới có quyền cập nhật trạng thái phòng.', 403);
        }

        $validator = Validator::make($request->all(), [
            'trangThai' => ['required', 'string', 'in:Sẵn sàng,Trống,Đang bảo trì,Ngưng khai thác,Cần dọn dẹp'],
        ], [
            'trangThai.required' => 'Trạng thái phòng không được để trống.',
            'trangThai.string'   => 'Trạng thái phòng phải là chuỗi ký tự.',
            'trangThai.in'       => 'Trạng thái phòng không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Dữ liệu gửi lên không hợp lệ.', 422, $validator->errors());
        }

        $existing = Phong::find($id);
        if (!$existing) {
            return $this->errorResponse('Không tìm thấy phòng.', 404);
        }

        $targetStatus = $request->input('trangThai');

        DB::beginTransaction();
        try {
            /** @var Phong|null $lockedRoom */
            $lockedRoom = Phong::lockForUpdate()->find($id);
            if (!$lockedRoom) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy phòng.', 404);
            }

            // Kiểm tra điều kiện nghiệp vụ khi chuyển sang bảo trì hoặc ngưng khai thác
            if (in_array($targetStatus, ['Đang bảo trì', 'Ngưng khai thác'], true) && $lockedRoom->trangThai !== $targetStatus) {
                // 1. Kiểm tra phòng đang có khách lưu trú
                $hasActiveStay = Luu_Tru::whereHas('chiTietDatPhong', function ($q) use ($lockedRoom) {
                    $q->where('maPhong', $lockedRoom->id);
                })->where('trangThai', 'Đang ở')->exists();

                if ($hasActiveStay || in_array($lockedRoom->trangThai, ['Đang ở', 'Đang có khách'], true)) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Không thể chuyển phòng sang bảo trì hoặc ngưng khai thác khi phòng đang có khách lưu trú.',
                        409
                    );
                }

                // 2. Kiểm tra đơn đặt phòng hiệu lực chưa hoàn tất
                $hasActiveBooking = Dat_Phong::whereHas('chiTietDatPhong', function ($q) use ($lockedRoom) {
                    $q->where('maPhong', $lockedRoom->id);
                })->whereNotIn('trangThai', ['Đã hủy', 'Đã trả', 'Đã hoàn thành'])->exists();

                if ($hasActiveBooking) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'Không thể chuyển phòng sang bảo trì hoặc ngưng khai thác khi phòng đang có đơn đặt phòng hiệu lực chưa hoàn tất.',
                        409
                    );
                }
            }

            $lockedRoom->trangThai = $targetStatus;
            $lockedRoom->save();
            $lockedRoom->load('loaiPhong');

            DB::commit();

            return $this->successResponse(
                $this->formatRoomItem($lockedRoom),
                'Cập nhật trạng thái phòng thành công.',
                200
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật trạng thái phòng (UC06): ' . $e->getMessage(), ['exception' => $e]);

            return $this->errorResponse('Cập nhật trạng thái phòng thất bại do lỗi hệ thống.', 500);
        }
    }

    /**
     * Xóa phòng vật lý (UC06 / BR01.5).
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->vaiTro !== 'Quản lý') {
            return $this->errorResponse('Bạn không có quyền thực hiện thao tác này. Chỉ Quản lý mới có quyền xóa phòng.', 403);
        }

        $room = Phong::find($id);
        if (!$room) {
            return $this->errorResponse('Không tìm thấy phòng.', 404);
        }

        // Kiểm tra xem phòng đã từng phát sinh giao dịch đặt phòng hoặc lưu trú
        $hasHistory = Chi_Tiet_Dat_Phong::where('maPhong', $room->id)->exists();
        if ($hasHistory) {
            return $this->errorResponse(
                'Phòng đã từng phát sinh giao dịch đặt phòng hoặc lưu trú, không thể xóa khỏi hệ thống. Vui lòng chuyển trạng thái sang "Ngưng khai thác".',
                409
            );
        }

        $room->delete();

        return $this->successResponse(null, 'Xóa phòng thành công.', 200);
    }

    /**
     * Xác định trạng thái sơ đồ phòng chuẩn hóa (Room Map Status Read Model).
     *
     * @param Phong $room
     * @param array|null $occupancyInfo
     * @return string
     */
    protected function determineRoomMapStatus(Phong $room, ?array $occupancyInfo = null): string
    {
        // 1. Trạng thái vận hành đặc thù
        if ($room->trangThai === 'Đang bảo trì') {
            return 'Đang bảo trì';
        }
        if ($room->trangThai === 'Ngưng khai thác') {
            return 'Ngưng khai thác';
        }
        if ($room->trangThai === 'Cần dọn dẹp') {
            return 'Cần dọn dẹp';
        }

        // 2. Nếu phòng có trạng thái Đang ở hoặc có đợt lưu trú Đang ở
        if ($room->trangThai === 'Đang ở' || ($occupancyInfo && $occupancyInfo['loai'] === 'Đang ở')) {
            return 'Đang ở';
        }

        // 3. Nếu phòng có đơn đặt phòng hiệu lực hoặc trạng thái Đã đặt
        if ($room->trangThai === 'Đã đặt' || ($occupancyInfo && $occupancyInfo['loai'] === 'Đã đặt')) {
            return 'Đã đặt';
        }

        // 4. Mặc định là Trống
        return in_array($room->trangThai, ['Trống', 'Sẵn sàng'], true) ? 'Trống' : $room->trangThai;
    }

    /**
     * Lấy thông tin đợt lưu trú hoặc đơn đặt phòng hiệu lực gắn với phòng.
     * Áp dụng bảo vệ dữ liệu cá nhân (Data Privacy) theo vai trò truy cập.
     *
     * @param Phong $room
     * @param string|null $userRole
     * @return array|null
     */
    protected function getActiveStayInfo(Phong $room, ?string $userRole = null): ?array
    {
        // 1. Kiểm tra đợt lưu trú đang ở (Luu_Tru)
        $activeStay = Luu_Tru::whereHas('chiTietDatPhong', function ($q) use ($room) {
            $q->where('maPhong', $room->id);
        })->where('trangThai', 'Đang ở')
          ->with(['chiTietDatPhong.datPhong.khachHang'])
          ->latest('id')
          ->first();

        if ($activeStay) {
            $datPhong = $activeStay->chiTietDatPhong?->datPhong;
            $khachHang = $datPhong?->khachHang;

            $info = [
                'loai'          => 'Đang ở',
                'maLuuTru'      => $activeStay->id,
                'maDatPhong'    => $datPhong?->id,
                'thoiGianNhan'  => $activeStay->thoiGianNhan ? $activeStay->thoiGianNhan->format('Y-m-d H:i:s') : null,
                'thoiGianTra'   => $activeStay->thoiGianTra ? $activeStay->thoiGianTra->format('Y-m-d H:i:s') : null,
                'ngayNhanPhong' => $datPhong?->ngayNhanPhong ? $datPhong->ngayNhanPhong->format('Y-m-d H:i:s') : null,
                'ngayTraPhong'  => $datPhong?->ngayTraPhong ? $datPhong->ngayTraPhong->format('Y-m-d H:i:s') : null,
            ];

            // Phân quyền dữ liệu cá nhân: Quản lý & Lễ tân được xem; Buồng phòng bị ẩn thông tin nhạy cảm
            if (in_array($userRole, ['Quản lý', 'Admin', 'Lễ tân'], true)) {
                $info['khachHang'] = [
                    'hoTen'       => $khachHang?->hoTen,
                    'soDienThoai' => $khachHang?->soDienThoai,
                    'cccd'        => $khachHang?->cccd,
                ];
            } else {
                // Buồng phòng: Chỉ thấy họ tên, không thấy SĐT & CCCD
                $info['khachHang'] = [
                    'hoTen'       => $khachHang?->hoTen,
                    'soDienThoai' => null,
                    'cccd'        => null,
                ];
            }

            return $info;
        }

        // 2. Kiểm tra đơn đặt phòng hiệu lực (Đã xác nhận hoặc Chờ thanh toán)
        $activeBooking = Dat_Phong::whereHas('chiTietDatPhong', function ($q) use ($room) {
            $q->where('maPhong', $room->id);
        })->whereIn('trangThai', ['Đã xác nhận', 'Chờ thanh toán'])
          ->where('ngayNhanPhong', '<=', now()->endOfDay())
          ->where('ngayTraPhong', '>=', now()->startOfDay())
          ->with('khachHang')
          ->latest('id')
          ->first();

        if ($activeBooking) {
            $khachHang = $activeBooking->khachHang;
            $info = [
                'loai'          => 'Đã đặt',
                'maDatPhong'    => $activeBooking->id,
                'ngayNhanPhong' => $activeBooking->ngayNhanPhong ? $activeBooking->ngayNhanPhong->format('Y-m-d H:i:s') : null,
                'ngayTraPhong'  => $activeBooking->ngayTraPhong ? $activeBooking->ngayTraPhong->format('Y-m-d H:i:s') : null,
                'trangThaiDon'  => $activeBooking->trangThai,
            ];

            if (in_array($userRole, ['Quản lý', 'Admin', 'Lễ tân'], true)) {
                $info['khachHang'] = [
                    'hoTen'       => $khachHang?->hoTen,
                    'soDienThoai' => $khachHang?->soDienThoai,
                    'cccd'        => $khachHang?->cccd,
                ];
            } else {
                $info['khachHang'] = [
                    'hoTen'       => $khachHang?->hoTen,
                    'soDienThoai' => null,
                    'cccd'        => null,
                ];
            }

            return $info;
        }

        return null;
    }

    /**
     * Lấy cấu hình mã màu hiển thị trực quan cho sơ đồ phòng.
     *
     * @param string $status
     * @return array<string, string>
     */
    protected function getStatusColor(string $status): array
    {
        return match ($status) {
            'Trống', 'Sẵn sàng' => [
                'color'      => '#0F766E',
                'badgeClass' => 'bg-teal text-white',
                'text'       => 'Trống',
            ],
            'Đã đặt' => [
                'color'      => '#F59E0B',
                'badgeClass' => 'bg-warning text-dark',
                'text'       => 'Đã đặt',
            ],
            'Đang ở' => [
                'color'      => '#1E3A5F',
                'badgeClass' => 'bg-primary text-white',
                'text'       => 'Đang ở',
            ],
            'Cần dọn dẹp' => [
                'color'      => '#8B5CF6',
                'badgeClass' => 'bg-purple text-white',
                'text'       => 'Cần dọn dẹp',
            ],
            'Đang bảo trì' => [
                'color'      => '#64748B',
                'badgeClass' => 'bg-secondary text-white',
                'text'       => 'Đang bảo trì',
            ],
            'Ngưng khai thác' => [
                'color'      => '#1E293B',
                'badgeClass' => 'bg-dark text-white',
                'text'       => 'Ngưng khai thác',
            ],
            default => [
                'color'      => '#64748B',
                'badgeClass' => 'bg-secondary text-white',
                'text'       => $status,
            ],
        };
    }

    /**
     * Chuẩn hóa cấu trúc dữ liệu bản ghi phòng trả về cho Client.
     *
     * @param Phong $p
     * @param string|null $userRole
     * @param bool $withOccupancy
     * @return array<string, mixed>
     */
    protected function formatRoomItem(Phong $p, ?string $userRole = null, bool $withOccupancy = false): array
    {
        $occupancy = null;
        if ($withOccupancy) {
            $occupancy = $this->getActiveStayInfo($p, $userRole);
        }

        $trangThaiSoDo = $this->determineRoomMapStatus($p, $occupancy);
        $colorInfo = $this->getStatusColor($trangThaiSoDo);

        $res = [
            'id'            => $p->id,
            'maLoaiPhong'   => $p->maLoaiPhong,
            'tenPhong'      => $p->tenPhong,
            'tang'          => (int) $p->tang,
            'trangThai'     => $p->trangThai,
            'trangThaiSoDo' => $trangThaiSoDo,
            'mauTrangThai'  => $colorInfo,
            'loaiPhong'     => $p->loaiPhong ? [
                'id'           => $p->loaiPhong->id,
                'tenLoai'      => $p->loaiPhong->tenLoai,
                'moTa'         => $p->loaiPhong->moTa,
                'giaTieuChuan' => (int) $p->loaiPhong->giaTieuChuan,
                'sucChuaToiDa' => (int) $p->loaiPhong->sucChuaToiDa,
                'trangThai'    => $p->loaiPhong->trangThai,
            ] : null,
            'created_at'    => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
            'updated_at'    => $p->updated_at ? $p->updated_at->format('Y-m-d H:i:s') : null,
        ];

        if ($withOccupancy) {
            $res['thongTinLuuTru'] = $occupancy;
        }

        return $res;
    }
}
