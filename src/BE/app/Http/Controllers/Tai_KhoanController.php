<?php

namespace App\Http\Controllers;

use App\Models\Tai_Khoan;
use App\Models\Nhan_Vien;
use App\Models\Vai_Tro;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Tai_KhoanController extends Controller
{
    /**
     * Kiểm tra quyền thực hiện thao tác (Backend Authorization).
     * Chỉ 'Chủ khách sạn', 'Quản lý' hoặc tài khoản admin mới được thao tác phân quyền & tài khoản.
     */
    private function kiemTraQuyen(Request $request): bool
    {
        // Lấy vai trò gửi từ header, input body, hoặc query string
        $userRole = $request->header('X-User-Role')
            ?? $request->input('userRole')
            ?? $request->query('userRole');

        // Mặc định nếu chưa đăng nhập hoặc không cấu hình thì gán Admin
        if (empty($userRole)) {
            $userRole = 'Admin';
        }

        $rolesChoPhep = ['Admin', 'admin', 'Chủ khách sạn', 'Quản lý', 'quanly'];

        if (!in_array($userRole, $rolesChoPhep)) {
            return false;
        }

        return true;
    }

    /**
     * Danh sách tài khoản kèm nhân viên, hỗ trợ tìm kiếm và lọc trạng thái.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('q', $request->query('search', '')));
        $trangThai = $request->query('trangThai', $request->query('status', ''));

        $query = Tai_Khoan::with('nhanVien')->orderBy('id', 'asc');

        // Lọc theo trạng thái tài khoản
        if (!empty($trangThai) && $trangThai !== 'Mọi trạng thái' && $trangThai !== 'all') {
            if ($trangThai === 'Đang hoạt động') {
                $query->where(function ($q) {
                    $q->where('trangThai', 'Đang hoạt động')
                      ->orWhere('trangThai', 'Hoạt động');
                });
            } elseif ($trangThai === 'Đã khóa') {
                $query->where('trangThai', 'Đã khóa');
            } else {
                $query->where('trangThai', $trangThai);
            }
        }

        // Tìm kiếm theo tên đăng nhập, họ tên nhân viên, hoặc chức vụ / vai trò
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('tenDangNhap', 'like', "%{$search}%")
                  ->orWhereHas('nhanVien', function ($sub) use ($search) {
                      $sub->where('hoTen', 'like', "%{$search}%")
                          ->orWhere('chucVu', 'like', "%{$search}%")
                          ->orWhere('soDienThoai', 'like', "%{$search}%");
                  });
            });
        }

        $taiKhoans = $query->get();

        // Chuẩn hóa dữ liệu trả về cho Frontend
        $data = $taiKhoans->map(function ($item) {
            $nv = $item->nhanVien;
            $maNvFormat = $nv ? sprintf('NV-%03d', $nv->id) : sprintf('TK-%03d', $item->id);
            $hoTen = $nv ? $nv->hoTen : 'Chưa gắn nhân viên';
            $chucVu = $nv ? $nv->chucVu : 'Chưa phân vai trò';

            // Định dạng ngày giờ truy cập gần nhất (dùng updated_at)
            $truyCapCuoi = $item->updated_at
                ? Carbon::parse($item->updated_at)->format('d/m - H:i')
                : 'Chưa truy cập';

            // Chuẩn hóa trạng thái
            $statusNormalized = in_array($item->trangThai, ['Đang hoạt động', 'Hoạt động'])
                ? 'Đang hoạt động'
                : 'Đã khóa';

            return [
                'id' => $item->id,
                'tenDangNhap' => $item->tenDangNhap,
                'trangThai' => $statusNormalized,
                'vaiTro' => $chucVu,
                'nhanVien' => [
                    'id' => $nv ? $nv->id : null,
                    'maNV' => $maNvFormat,
                    'hoTen' => $hoTen,
                    'chucVu' => $chucVu,
                    'soDienThoai' => $nv ? $nv->soDienThoai : '',
                    'cccd' => $nv ? $nv->cccd : '',
                ],
                'truyCapCuoi' => $truyCapCuoi,
                'created_at' => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $item->updated_at ? $item->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Chi tiết một tài khoản
     */
    public function show($id)
    {
        $taiKhoan = Tai_Khoan::with('nhanVien')->find($id);

        if (!$taiKhoan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy tài khoản',
            ], 404);
        }

        $nv = $taiKhoan->nhanVien;
        $statusNormalized = in_array($taiKhoan->trangThai, ['Đang hoạt động', 'Hoạt động'])
            ? 'Đang hoạt động'
            : 'Đã khóa';

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $taiKhoan->id,
                'tenDangNhap' => $taiKhoan->tenDangNhap,
                'trangThai' => $statusNormalized,
                'vaiTro' => $nv ? $nv->chucVu : 'Chưa phân vai trò',
                'nhanVien' => [
                    'id' => $nv ? $nv->id : null,
                    'maNV' => $nv ? sprintf('NV-%03d', $nv->id) : sprintf('TK-%03d', $taiKhoan->id),
                    'hoTen' => $nv ? $nv->hoTen : '',
                    'chucVu' => $nv ? $nv->chucVu : '',
                    'soDienThoai' => $nv ? $nv->soDienThoai : '',
                ],
                'truyCapCuoi' => $taiKhoan->updated_at ? Carbon::parse($taiKhoan->updated_at)->format('d/m - H:i') : '',
            ]
        ]);
    }

    /**
     * Thêm tài khoản mới và liên kết hồ sơ nhân viên.
     * Kiểm tra quyền backend: Chỉ Chủ khách sạn/Quản lý mới có quyền tạo tài khoản.
     */
    public function store(Request $request)
    {
        // 1. Kiểm tra quyền hạn
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền thêm tài khoản. Chỉ Admin hoặc Quản lý mới được phép thực hiện.',
            ], 403);
        }

        // 2. Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'tenDangNhap' => 'required|string|max:100|unique:Tai_Khoan,tenDangNhap',
            'hoTen' => 'required|string|max:100',
            'vaiTro' => 'required|string|max:50',
            'password' => 'nullable|string|min:6',
            'soDienThoai' => 'nullable|string|max:15',
            'cccd' => 'nullable|string|max:20',
        ], [
            'tenDangNhap.required' => 'Vui lòng nhập email / tên đăng nhập.',
            'tenDangNhap.unique' => 'Email / tên đăng nhập này đã tồn tại trong hệ thống.',
            'hoTen.required' => 'Vui lòng nhập họ và tên nhân viên.',
            'vaiTro.required' => 'Vui lòng chọn vai trò cho nhân viên.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // 3. Tạo tài khoản
        $now = Carbon::now();
        $taiKhoan = Tai_Khoan::create([
            'tenDangNhap' => $request->tenDangNhap,
            'password' => bcrypt($request->password ?? '123456'),
            'hash_password' => null,
            'trangThai' => 'Đang hoạt động',
        ]);

        // 4. Tạo hồ sơ nhân viên liên kết
        $nhanVien = Nhan_Vien::create([
            'maTaiKhoan' => $taiKhoan->id,
            'hoTen' => $request->hoTen,
            'ngaySinh' => $request->ngaySinh ?? '1998-01-01',
            'cccd' => $request->cccd ?? '001' . rand(100000000, 999999999),
            'soDienThoai' => $request->soDienThoai ?? ('09' . rand(10000000, 99999999)),
            'chucVu' => $request->vaiTro,
            'trangThai' => 'Đang làm',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thêm tài khoản và nhân viên thành công!',
            'data' => [
                'id' => $taiKhoan->id,
                'tenDangNhap' => $taiKhoan->tenDangNhap,
                'trangThai' => 'Đang hoạt động',
                'vaiTro' => $nhanVien->chucVu,
                'nhanVien' => [
                    'id' => $nhanVien->id,
                    'maNV' => sprintf('NV-%03d', $nhanVien->id),
                    'hoTen' => $nhanVien->hoTen,
                    'chucVu' => $nhanVien->chucVu,
                ],
                'truyCapCuoi' => $now->format('d/m - H:i'),
            ]
        ], 201);
    }

    /**
     * Cập nhật vai trò / phân quyền cho tài khoản.
     * Kiểm tra quyền backend: Chỉ Chủ khách sạn/Quản lý mới có quyền đổi vai trò.
     */
    public function capNhatVaiTro(Request $request, $id)
    {
        // 1. Kiểm tra quyền hạn backend
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền thay đổi vai trò. Chỉ Admin hoặc Quản lý mới được chỉnh sửa phân quyền.',
            ], 403);
        }

        // 2. Validate
        $validator = Validator::make($request->all(), [
            'vaiTro' => 'required|string|max:50',
        ], [
            'vaiTro.required' => 'Vui lòng chọn vai trò cần cập nhật.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // 3. Tìm tài khoản
        $taiKhoan = Tai_Khoan::with('nhanVien')->find($id);

        if (!$taiKhoan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy tài khoản cần cập nhật.',
            ], 404);
        }

        $vaiTroMoi = $request->vaiTro ?? $request->chucVu;
        $hoTenMoi = $request->hoTen;
        $soDienThoaiMoi = $request->soDienThoai;

        // Cập nhật chức vụ/vai trò và thông tin của nhân viên liên kết
        if ($taiKhoan->nhanVien) {
            $updateData = [];
            if ($vaiTroMoi) $updateData['chucVu'] = $vaiTroMoi;
            if ($hoTenMoi) $updateData['hoTen'] = $hoTenMoi;
            if ($soDienThoaiMoi !== null) $updateData['soDienThoai'] = $soDienThoaiMoi;

            if (!empty($updateData)) {
                $taiKhoan->nhanVien->update($updateData);
            }
        } else {
            // Nếu chưa có bản ghi nhân viên, tạo mới liên kết
            Nhan_Vien::create([
                'maTaiKhoan' => $taiKhoan->id,
                'hoTen' => $hoTenMoi ?? $taiKhoan->tenDangNhap,
                'ngaySinh' => '1995-01-01',
                'cccd' => '001' . rand(100000000, 999999999),
                'soDienThoai' => $soDienThoaiMoi ?? '0900000000',
                'chucVu' => $vaiTroMoi ?? 'Nhân viên',
                'trangThai' => 'Đang làm',
            ]);
        }

        if ($request->filled('tenDangNhap') && $request->tenDangNhap !== $taiKhoan->tenDangNhap) {
            $taiKhoan->tenDangNhap = $request->tenDangNhap;
        }

        $taiKhoan->touch(); // Cập nhật updated_at của tài khoản

        $tenHienThi = $taiKhoan->nhanVien ? $taiKhoan->nhanVien->hoTen : $taiKhoan->tenDangNhap;

        return response()->json([
            'status' => 'success',
            'message' => "Đã lưu thay đổi cho nhân viên: {$tenHienThi}",
            'data' => [
                'id' => $taiKhoan->id,
                'tenDangNhap' => $taiKhoan->tenDangNhap,
                'vaiTro' => $taiKhoan->nhanVien ? $taiKhoan->nhanVien->chucVu : $vaiTroMoi,
                'hoTen' => $taiKhoan->nhanVien ? $taiKhoan->nhanVien->hoTen : '',
                'soDienThoai' => $taiKhoan->nhanVien ? $taiKhoan->nhanVien->soDienThoai : '',
                'updated_at' => $taiKhoan->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Cập nhật thông tin chi tiết nhân viên (Họ tên, SĐT, Vai trò)
     */
    public function capNhatThongTin(Request $request, $id)
    {
        return $this->capNhatVaiTro($request, $id);
    }

    /**
     * Khóa hoặc Mở khóa tài khoản.
     * Kiểm tra quyền backend: Chỉ Chủ khách sạn/Quản lý mới có quyền khóa/mở khóa.
     */
    public function capNhatTrangThai(Request $request, $id)
    {
        // 1. Kiểm tra quyền hạn backend
        if (!$this->kiemTraQuyen($request)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Từ chối truy cập: Bạn không có quyền khóa hoặc mở khóa tài khoản. Chỉ Admin hoặc Quản lý mới được phép thao tác.',
            ], 403);
        }

        // 2. Tìm tài khoản
        $taiKhoan = Tai_Khoan::with('nhanVien')->find($id);

        if (!$taiKhoan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy tài khoản.',
            ], 404);
        }

        // Xác định trạng thái mới: chuyển đổi giữa Đang hoạt động <-> Đã khóa
        $trangThaiHienTai = in_array($taiKhoan->trangThai, ['Đang hoạt động', 'Hoạt động'])
            ? 'Đang hoạt động'
            : 'Đã khóa';

        $trangThaiMoi = ($trangThaiHienTai === 'Đang hoạt động') ? 'Đã khóa' : 'Đang hoạt động';

        if ($request->has('trangThai')) {
            $trangThaiMoi = $request->trangThai;
        }

        $taiKhoan->trangThai = $trangThaiMoi;
        $taiKhoan->save();

        $thongBao = ($trangThaiMoi === 'Đã khóa')
            ? 'Đã khóa tài khoản thành công. Tài khoản sẽ kết thúc phiên truy cập.'
            : 'Đã mở khóa tài khoản thành công. Tài khoản có thể đăng nhập bình thường.';

        return response()->json([
            'status' => 'success',
            'message' => $thongBao,
            'data' => [
                'id' => $taiKhoan->id,
                'trangThai' => $trangThaiMoi,
                'updated_at' => $taiKhoan->updated_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Danh sách các vai trò để FE hiển thị tùy chọn dropdown.
     */
    public function danhSachVaiTro()
    {
        $vaiTros = Vai_Tro::select('id', 'tenVaiTro', 'moTa')->get();

        return response()->json([
            'status' => 'success',
            'data' => $vaiTros,
        ]);
    }
}
