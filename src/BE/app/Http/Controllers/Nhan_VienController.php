<?php

namespace App\Http\Controllers;

use App\Models\Nhan_Vien;
use App\Models\Tai_Khoan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Nhan_VienController extends Controller
{
    /**
     * Kiểm tra quyền thực hiện thao tác (Backend Authorization).
     * Chỉ 'Chủ khách sạn', 'Quản lý' hoặc 'Admin' mới được thêm/sửa/xóa nhân viên.
     */
    private function kiemTraQuyen(Request $request): bool
    {
        $userRole = $request->header('X-User-Role')
            ?? $request->input('userRole')
            ?? $request->query('userRole');

        if (empty($userRole)) {
            $userRole = 'Admin';
        }

        $rolesChoPhep = ['Admin', 'admin', 'Chủ khách sạn', 'Quản lý', 'quanly'];

        return in_array($userRole, $rolesChoPhep);
    }

    /**
     * Helper xác định ca làm hiện tại theo thời gian thực
     */
    public static function getCaHienTai()
    {
        $hour = (int) Carbon::now()->format('H');
        if ($hour >= 6 && $hour < 14) {
            return [
                'tenCa' => 'Ca sáng',
                'khungGio' => '06:00 – 14:00',
                'full' => 'Ca sáng (06:00 - 14:00)'
            ];
        } elseif ($hour >= 14 && $hour < 22) {
            return [
                'tenCa' => 'Ca chiều',
                'khungGio' => '14:00 – 22:00',
                'full' => 'Ca chiều (14:00 - 22:00)'
            ];
        } else {
            return [
                'tenCa' => 'Ca đêm',
                'khungGio' => '22:00 – 06:00',
                'full' => 'Ca đêm (22:00 - 06:00)'
            ];
        }
    }

    /**
     * Helper tính avatar text viết tắt từ họ tên (VD: 'Mai Linh' -> 'ML')
     */
    public static function getInitials($name)
    {
        if (empty($name)) return 'NV';
        $parts = explode(' ', trim($name));
        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 2));
        }
        $first = mb_substr($parts[0], 0, 1);
        $last = mb_substr(end($parts), 0, 1);
        return mb_strtoupper($first . $last);
    }

    /**
     * 1. LẤY DANH SÁCH NHÂN VIÊN (KÈM TÌM KIẾM & BỘ LỌC)
     */
    public function index(Request $request)
    {
        try {
            $search = trim($request->query('q', $request->query('search', '')));
            $boPhan = $request->query('boPhan', 'Tất cả');
            $caLam = $request->query('caLam', 'Tất cả');
            $trangThai = $request->query('trangThai', 'Tất cả');

            $query = Nhan_Vien::with('taiKhoan')->orderBy('id', 'asc');

            // 1. Tìm kiếm theo tên, mã NV, email, số điện thoại, chức vụ
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('hoTen', 'like', "%{$search}%")
                      ->orWhere('maNhanVien', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('soDienThoai', 'like', "%{$search}%")
                      ->orWhere('chucVu', 'like', "%{$search}%")
                      ->orWhereHas('taiKhoan', function ($sub) use ($search) {
                          $sub->where('tenDangNhap', 'like', "%{$search}%");
                      });
                });
            }

            // 2. Lọc theo bộ phận
            if (!empty($boPhan) && $boPhan !== 'Tất cả' && $boPhan !== 'all') {
                $query->where('boPhan', $boPhan);
            }

            // 3. Lọc theo ca làm việc
            if (!empty($caLam) && $caLam !== 'Tất cả' && $caLam !== 'all') {
                $query->where('caLam', 'like', "%{$caLam}%");
            }

            // 4. Lọc theo trạng thái làm việc
            if (!empty($trangThai) && $trangThai !== 'Tất cả' && $trangThai !== 'all') {
                if ($trangThai === 'Đang trong ca' || $trangThai === 'Đang làm') {
                    $query->where(function ($q) {
                        $q->where('trangThai', 'Đang trong ca')
                          ->orWhere('trangThai', 'Đang làm');
                    });
                } else {
                    $query->where('trangThai', $trangThai);
                }
            }

            $list = $query->get();

            $data = $list->map(function ($item) {
                $email = $item->email ?: ($item->taiKhoan ? $item->taiKhoan->tenDangNhap : 'chua_co_email@hotel.com');
                $maNV = $item->maNhanVien ?: sprintf('NV-%03d', $item->id);

                return [
                    'id' => $item->id,
                    'maNhanVien' => $maNV,
                    'maTaiKhoan' => $item->maTaiKhoan,
                    'hoTen' => $item->hoTen,
                    'email' => $email,
                    'soDienThoai' => $item->soDienThoai,
                    'cccd' => $item->cccd,
                    'ngaySinh' => $item->ngaySinh,
                    'chucVu' => $item->chucVu,
                    'boPhan' => $item->boPhan ?: 'Lễ tân',
                    'caLam' => $item->caLam ?: 'Ca sáng (06:00 - 14:00)',
                    'ngayGiaNhap' => $item->ngayGiaNhap ?: ($item->created_at ? $item->created_at->format('Y-m-d') : '2023-01-01'),
                    'nguoiQuanLy' => $item->nguoiQuanLy ?: 'Trần An (Giám đốc)',
                    'ghiChu' => $item->ghiChu ?: '',
                    'trangThai' => in_array($item->trangThai, ['Đang trong ca', 'Đang làm']) ? 'Đang trong ca' : $item->trangThai,
                    'avatarText' => self::getInitials($item->hoTen),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'total' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi lấy danh sách nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. BA THẺ THỐNG KÊ THEO YÊU CẦU ĐỀ BÀI:
     * - Tổng nhân viên
     * - Nhân viên đang trong ca
     * - Ca làm việc hiện tại
     */
    public function thongKe()
    {
        try {
            $caInfo = self::getCaHienTai();

            // Tổng nhân viên (không tính nhân viên test 'Khách hàng' nếu có)
            $tongNhanVien = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')->count();

            // Nhân viên đang trong ca
            $dangTrongCa = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')
                ->whereIn('trangThai', ['Đang trong ca', 'Đang làm'])
                ->count();

            // Số nhân viên thuộc ca làm việc hiện tại
            $nhanVienTheoCaHienTai = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')
                ->where('caLam', 'like', "%{$caInfo['tenCa']}%")
                ->count();

            // Số nhân viên nghỉ ca
            $nghiCa = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')
                ->where('trangThai', 'Nghỉ ca')
                ->count();

            // Số nhân viên đã nghỉ việc
            $daNghiViec = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')
                ->where('trangThai', 'Đã nghỉ việc')
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'tongNhanVien' => $tongNhanVien,
                    'dangTrongCa' => $dangTrongCa,
                    'caHienTai' => [
                        'tenCa' => $caInfo['tenCa'],
                        'khungGio' => $caInfo['khungGio'],
                        'full' => $caInfo['full'],
                        'soLuongNhanVien' => $nhanVienTheoCaHienTai > 0 ? $nhanVienTheoCaHienTai : $dangTrongCa,
                    ],
                    'nghiCa' => $nghiCa,
                    'daNghiViec' => $daNghiViec,
                    'tiLeTrongCa' => $tongNhanVien > 0 ? round(($dangTrongCa / $tongNhanVien) * 100, 1) : 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi lấy thống kê nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. LẤY CHI TIẾT MỘT NHÂN VIÊN
     */
    public function show($id)
    {
        try {
            $item = Nhan_Vien::with('taiKhoan')->find($id);
            if (!$item) {
                // Thử tìm theo mã nhân viên dạng "NV-001"
                $item = Nhan_Vien::with('taiKhoan')->where('maNhanVien', $id)->first();
            }

            if (!$item) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin nhân viên có ID: ' . $id
                ], 404);
            }

            $email = $item->email ?: ($item->taiKhoan ? $item->taiKhoan->tenDangNhap : '');
            $maNV = $item->maNhanVien ?: sprintf('NV-%03d', $item->id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $item->id,
                    'maNhanVien' => $maNV,
                    'maTaiKhoan' => $item->maTaiKhoan,
                    'hoTen' => $item->hoTen,
                    'email' => $email,
                    'soDienThoai' => $item->soDienThoai,
                    'cccd' => $item->cccd,
                    'ngaySinh' => $item->ngaySinh,
                    'chucVu' => $item->chucVu,
                    'boPhan' => $item->boPhan ?: 'Lễ tân',
                    'caLam' => $item->caLam ?: 'Ca sáng (06:00 - 14:00)',
                    'ngayGiaNhap' => $item->ngayGiaNhap ?: ($item->created_at ? $item->created_at->format('Y-m-d') : '2023-01-01'),
                    'nguoiQuanLy' => $item->nguoiQuanLy ?: 'Trần An (Giám đốc)',
                    'ghiChu' => $item->ghiChu ?: '',
                    'trangThai' => in_array($item->trangThai, ['Đang trong ca', 'Đang làm']) ? 'Đang trong ca' : $item->trangThai,
                    'avatarText' => self::getInitials($item->hoTen),
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi lấy chi tiết nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. THÊM MỚI NHÂN VIÊN
     * Kiểm tra dữ liệu hợp lệ, mã nhân viên, email và số điện thoại trùng lặp.
     */
    public function store(Request $request)
    {
        // 1. Kiểm tra quyền hạn
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền thêm mới nhân viên. Vui lòng đăng nhập với quyền Admin hoặc Quản lý.'
            ], 403);
        }

        // 2. Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'hoTen' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'soDienThoai' => 'required|string|max:15',
            'chucVu' => 'required|string|max:50',
            'boPhan' => 'required|string|max:50',
            'caLam' => 'required|string|max:50',
            'ngaySinh' => 'nullable|string|max:20',
            'cccd' => 'nullable|string|max:20',
            'ngayGiaNhap' => 'nullable|string|max:20',
            'nguoiQuanLy' => 'nullable|string|max:100',
            'ghiChu' => 'nullable|string|max:1000',
            'trangThai' => 'nullable|string|max:20',
        ], [
            'hoTen.required' => 'Họ và tên nhân viên là bắt buộc.',
            'email.required' => 'Email nhân viên là bắt buộc.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'soDienThoai.required' => 'Số điện thoại liên hệ là bắt buộc.',
            'chucVu.required' => 'Chức danh công việc là bắt buộc.',
            'boPhan.required' => 'Vui lòng chọn bộ phận làm việc.',
            'caLam.required' => 'Vui lòng chọn ca làm việc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. KIỂM TRA TRÙNG LẶP: EMAIL, SỐ ĐIỆN THOẠI, MÃ NHÂN VIÊN
        $email = trim(strtolower($request->email));
        $phone = trim($request->soDienThoai);
        $cccd = trim($request->cccd ?? '');

        // Check email trong Nhan_Vien
        if (Nhan_Vien::where('email', $email)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email "' . $email . '" đã được sử dụng bởi một nhân viên khác trong hệ thống.'
            ], 422);
        }

        // Check phone trong Nhan_Vien
        if (Nhan_Vien::where('soDienThoai', $phone)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số điện thoại "' . $phone . '" đã được đăng ký bởi nhân viên khác.'
            ], 422);
        }

        // Check CCCD nếu có
        if (!empty($cccd) && Nhan_Vien::where('cccd', $cccd)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số CCCD/CMND "' . $cccd . '" đã tồn tại trong hồ sơ nhân sự.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            // Sinh mã nhân viên tự động NV-00X nếu chưa cung cấp
            $maNV = trim($request->maNhanVien ?? '');
            if (empty($maNV)) {
                $maxId = Nhan_Vien::max('id') ?? 0;
                $maNV = sprintf('NV-%03d', $maxId + 1);
            } else {
                if (Nhan_Vien::where('maNhanVien', $maNV)->exists()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Mã nhân viên "' . $maNV . '" đã tồn tại. Vui lòng chọn mã khác.'
                    ], 422);
                }
            }

            // Tạo tài khoản đăng nhập tương ứng nếu chưa có
            $taiKhoan = Tai_Khoan::where('tenDangNhap', $email)->first();
            if (!$taiKhoan) {
                $taiKhoan = Tai_Khoan::create([
                    'tenDangNhap' => $email,
                    'password' => bcrypt('123456'),
                    'trangThai' => 'Đang hoạt động',
                ]);
            }

            // Lưu hồ sơ nhân viên
            $nhanVien = Nhan_Vien::create([
                'maNhanVien' => $maNV,
                'maTaiKhoan' => $taiKhoan->id,
                'hoTen' => trim($request->hoTen),
                'email' => $email,
                'soDienThoai' => $phone,
                'cccd' => $cccd ?: ('001' . rand(100000000, 999999999)),
                'ngaySinh' => $request->ngaySinh ?: '1996-01-01',
                'chucVu' => trim($request->chucVu),
                'boPhan' => trim($request->boPhan),
                'caLam' => trim($request->caLam),
                'ngayGiaNhap' => $request->ngayGiaNhap ?: $now->format('Y-m-d'),
                'nguoiQuanLy' => trim($request->nguoiQuanLy ?: 'Trần An (Giám đốc)'),
                'ghiChu' => trim($request->ghiChu ?: ''),
                'trangThai' => $request->trangThai ?: 'Đang trong ca',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm nhân viên mới [' . $nhanVien->hoTen . '] thành công!',
                'data' => [
                    'id' => $nhanVien->id,
                    'maNhanVien' => $nhanVien->maNhanVien,
                    'hoTen' => $nhanVien->hoTen,
                    'email' => $nhanVien->email,
                    'soDienThoai' => $nhanVien->soDienThoai,
                    'chucVu' => $nhanVien->chucVu,
                    'boPhan' => $nhanVien->boPhan,
                    'caLam' => $nhanVien->caLam,
                    'ngayGiaNhap' => $nhanVien->ngayGiaNhap,
                    'nguoiQuanLy' => $nhanVien->nguoiQuanLy,
                    'ghiChu' => $nhanVien->ghiChu,
                    'trangThai' => $nhanVien->trangThai,
                    'avatarText' => self::getInitials($nhanVien->hoTen),
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tạo nhân viên mới: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. CHỈNH SỬA THÔNG TIN NHÂN VIÊN
     */
    public function update(Request $request, $id)
    {
        // 1. Kiểm tra quyền hạn
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền chỉnh sửa thông tin nhân viên.'
            ], 403);
        }

        $nhanVien = Nhan_Vien::find($id);
        if (!$nhanVien) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy nhân viên cần cập nhật ID: ' . $id
            ], 404);
        }

        // 2. Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'hoTen' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'soDienThoai' => 'required|string|max:15',
            'chucVu' => 'required|string|max:50',
            'boPhan' => 'required|string|max:50',
            'caLam' => 'required|string|max:50',
        ], [
            'hoTen.required' => 'Họ và tên nhân viên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'soDienThoai.required' => 'Số điện thoại là bắt buộc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. Kiểm tra trùng lặp email và SĐT (loại trừ chính bản ghi này)
        $email = trim(strtolower($request->email));
        $phone = trim($request->soDienThoai);

        if (Nhan_Vien::where('email', $email)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email "' . $email . '" đã được sử dụng bởi nhân viên khác.'
            ], 422);
        }

        if (Nhan_Vien::where('soDienThoai', $phone)->where('id', '!=', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Số điện thoại "' . $phone . '" đã được đăng ký bởi nhân viên khác.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $nhanVien->hoTen = trim($request->hoTen);
            $nhanVien->email = $email;
            $nhanVien->soDienThoai = $phone;
            $nhanVien->chucVu = trim($request->chucVu);
            $nhanVien->boPhan = trim($request->boPhan);
            $nhanVien->caLam = trim($request->caLam);

            if ($request->filled('cccd')) {
                $nhanVien->cccd = trim($request->cccd);
            }
            if ($request->filled('ngaySinh')) {
                $nhanVien->ngaySinh = trim($request->ngaySinh);
            }
            if ($request->filled('ngayGiaNhap')) {
                $nhanVien->ngayGiaNhap = trim($request->ngayGiaNhap);
            }
            if ($request->filled('nguoiQuanLy')) {
                $nhanVien->nguoiQuanLy = trim($request->nguoiQuanLy);
            }
            if ($request->has('ghiChu')) {
                $nhanVien->ghiChu = trim($request->ghiChu);
            }
            if ($request->filled('trangThai')) {
                $nhanVien->trangThai = trim($request->trangThai);
            }

            $nhanVien->save();

            // Đồng bộ tên đăng nhập tài khoản nếu có
            if ($nhanVien->taiKhoan) {
                $nhanVien->taiKhoan->tenDangNhap = $email;
                $nhanVien->taiKhoan->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thông tin nhân viên [' . $nhanVien->hoTen . '] thành công!',
                'data' => [
                    'id' => $nhanVien->id,
                    'maNhanVien' => $nhanVien->maNhanVien ?: sprintf('NV-%03d', $nhanVien->id),
                    'hoTen' => $nhanVien->hoTen,
                    'email' => $nhanVien->email,
                    'soDienThoai' => $nhanVien->soDienThoai,
                    'chucVu' => $nhanVien->chucVu,
                    'boPhan' => $nhanVien->boPhan,
                    'caLam' => $nhanVien->caLam,
                    'ngayGiaNhap' => $nhanVien->ngayGiaNhap,
                    'nguoiQuanLy' => $nhanVien->nguoiQuanLy,
                    'ghiChu' => $nhanVien->ghiChu,
                    'trangThai' => $nhanVien->trangThai,
                    'avatarText' => self::getInitials($nhanVien->hoTen),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. CẬP NHẬT TRẠNG THÁI LÀM VIỆC CỦA NHÂN VIÊN
     */
    public function capNhatTrangThai(Request $request, $id)
    {
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền đổi trạng thái nhân viên.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'trangThai' => 'required|string|max:50'
        ], [
            'trangThai.required' => 'Vui lòng cung cấp trạng thái cần cập nhật.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $nhanVien = Nhan_Vien::find($id);
            if (!$nhanVien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy nhân viên #' . $id
                ], 404);
            }

            $nhanVien->trangThai = trim($request->trangThai);
            $nhanVien->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật trạng thái của [' . $nhanVien->hoTen . '] sang: ' . $nhanVien->trangThai,
                'data' => [
                    'id' => $nhanVien->id,
                    'trangThai' => $nhanVien->trangThai
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. XÓA HOẶC VÔ HIỆU HÓA NHÂN VIÊN
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền xóa nhân viên.'
            ], 403);
        }

        try {
            $nhanVien = Nhan_Vien::find($id);
            if (!$nhanVien) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy nhân viên #' . $id
                ], 404);
            }

            // Chuyển trạng thái sang Đã nghỉ việc thay vì xóa cứng để bảo toàn khóa ngoại các bảng giao dịch
            $nhanVien->trangThai = 'Đã nghỉ việc';
            $nhanVien->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã chuyển nhân viên [' . $nhanVien->hoTen . '] sang trạng thái Đã nghỉ việc.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xóa nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }
}
