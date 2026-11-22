<?php

namespace App\Http\Controllers;

use App\Models\San_Pham_Minibar;
use App\Models\Phat_Sinh_Minibar;
use App\Models\Tai_San;
use App\Models\Ghi_Nhan_Tai_San;
use App\Models\Minibar_Phong;
use App\Models\Phieu_Kiem_Ke;
use App\Models\Phong;
use App\Models\Luu_Tru;
use App\Models\Nhan_Vien;
use App\Models\Yeu_Cau_Dich_Vu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MinibarTaiSanController extends Controller
{
    /**
     * =========================================================================
     * TAB 1: QUẢN LÝ MINIBAR & TỒN KHO TỔNG
     * =========================================================================
     */

    /**
     * Lấy danh sách sản phẩm minibar & tồn kho tổng
     * GET /api/minibar-tai-san/san-pham
     */
    public function danhSachSanPham(Request $request)
    {
        $q = trim($request->query('q', ''));
        $loai = $request->query('loaiSanPham', 'Tất cả');
        $trangThai = $request->query('trangThai', 'Tất cả');

        $query = San_Pham_Minibar::orderBy('id', 'asc');

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('tenSanPham', 'like', "%{$q}%")
                    ->orWhere('maSanPham', 'like', "%{$q}%");
            });
        }

        if ($loai !== 'Tất cả' && !empty($loai)) {
            $query->where('loaiSanPham', $loai);
        }

        if ($trangThai !== 'Tất cả' && !empty($trangThai)) {
            if ($trangThai === 'Sap_Het') {
                $query->whereRaw('tonHienTai <= nguongToiThieu AND tonHienTai > 0');
            } elseif ($trangThai === 'Het_Hang') {
                $query->where('tonHienTai', '<=', 0);
            } elseif ($trangThai === 'Con_Hang') {
                $query->whereRaw('tonHienTai > nguongToiThieu');
            }
        }

        $items = $query->get()->map(function ($p) {
            $canhBao = $p->tonHienTai <= $p->nguongToiThieu;
            $statusText = 'Còn hàng';
            $statusBadge = 'Con_Hang';

            if ($p->tonHienTai <= 0) {
                $statusText = 'Hết hàng';
                $statusBadge = 'Het_Hang';
            } elseif ($canhBao) {
                $statusText = 'Sắp hết hàng';
                $statusBadge = 'Sap_Het';
            }

            return [
                'id' => $p->id,
                'maSanPham' => $p->maSanPham ?: 'MB-' . str_pad($p->id, 2, '0', STR_PAD_LEFT),
                'tenSanPham' => $p->tenSanPham,
                'donGia' => (int)$p->donGia,
                'donGiaDinhDang' => number_format($p->donGia, 0, ',', '.') . ' đ',
                'donViTinh' => $p->donViTinh,
                'loaiSanPham' => $p->loaiSanPham ?: 'Tinh_Phi',
                'loaiSanPhamText' => ($p->loaiSanPham === 'Mien_Phi') ? 'Miễn phí' : 'Tính phí',
                'tonDau' => (int)$p->tonDau,
                'soLuongNhap' => (int)$p->soLuongNhap,
                'soLuongXuat' => (int)$p->soLuongXuat,
                'tonHienTai' => (int)$p->tonHienTai,
                'nguongToiThieu' => (int)$p->nguongToiThieu,
                'dinhMucTieuChuan' => (int)($p->dinhMucTieuChuan ?: 2),
                'canhBaoTonKho' => $canhBao,
                'trangThai' => $statusBadge,
                'trangThaiText' => $statusText,
            ];
        });

        // Thống kê tổng quan thẻ đầu trang
        $allProducts = San_Pham_Minibar::all();
        $thongKe = [
            'tongSanPham' => $allProducts->count(),
            'tongTonKho' => (int)$allProducts->sum('tonHienTai'),
            'sapHetHang' => $allProducts->filter(fn($p) => $p->tonHienTai <= $p->nguongToiThieu)->count(),
            'tongGiaTriTon' => (int)$allProducts->sum(fn($p) => $p->tonHienTai * $p->donGia),
            'tongGiaTriTonDinhDang' => number_format((int)$allProducts->sum(fn($p) => $p->tonHienTai * $p->donGia), 0, ',', '.') . ' đ',
        ];

        return response()->json([
            'status' => 'success',
            'thongKe' => $thongKe,
            'total' => $items->count(),
            'data' => $items
        ]);
    }

    /**
     * Thêm sản phẩm minibar mới
     * POST /api/minibar-tai-san/san-pham
     */
    public function themSanPham(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenSanPham' => 'required|string|max:100',
            'donGia' => 'required|numeric|min:0',
            'donViTinh' => 'required|string|max:20',
            'loaiSanPham' => 'required|in:Tinh_Phi,Mien_Phi',
            'tonHienTai' => 'nullable|integer|min:0',
            'nguongToiThieu' => 'nullable|integer|min:0',
            'dinhMucTieuChuan' => 'nullable|integer|min:0',
        ], [
            'tenSanPham.required' => 'Vui lòng nhập tên sản phẩm.',
            'donGia.required' => 'Vui lòng nhập đơn giá.',
            'donViTinh.required' => 'Vui lòng nhập đơn vị tính.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $ton = (int)$request->input('tonHienTai', 50);
        $ma = trim($request->input('maSanPham', ''));
        if (empty($ma)) {
            $nextId = (San_Pham_Minibar::max('id') ?: 0) + 1;
            $ma = 'MB-' . str_pad($nextId, 2, '0', STR_PAD_LEFT);
        }

        $sp = San_Pham_Minibar::create([
            'maSanPham' => strtoupper($ma),
            'tenSanPham' => trim($request->input('tenSanPham')),
            'donGia' => (int)$request->input('donGia'),
            'donViTinh' => trim($request->input('donViTinh')),
            'loaiSanPham' => $request->input('loaiSanPham', 'Tinh_Phi'),
            'nguongToiThieu' => (int)$request->input('nguongToiThieu', 10),
            'tonDau' => $ton,
            'soLuongNhap' => $ton,
            'soLuongXuat' => 0,
            'tonHienTai' => $ton,
            'dinhMucTieuChuan' => (int)$request->input('dinhMucTieuChuan', 2),
            'trangThai' => $ton > 0 ? 'Con_Hang' : 'Het_Hang'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Thêm sản phẩm minibar '{$sp->tenSanPham}' thành công!",
            'data' => $sp
        ], 201);
    }

    /**
     * Cập nhật thông tin sản phẩm minibar
     * PUT /api/minibar-tai-san/san-pham/{id}
     */
    public function capNhatSanPham(Request $request, $id)
    {
        $sp = San_Pham_Minibar::find($id);
        if (!$sp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        if ($request->has('tenSanPham')) $sp->tenSanPham = trim($request->input('tenSanPham'));
        if ($request->has('donGia')) $sp->donGia = (int)$request->input('donGia');
        if ($request->has('donViTinh')) $sp->donViTinh = trim($request->input('donViTinh'));
        if ($request->has('loaiSanPham')) $sp->loaiSanPham = $request->input('loaiSanPham');
        if ($request->has('nguongToiThieu')) $sp->nguongToiThieu = (int)$request->input('nguongToiThieu');
        if ($request->has('dinhMucTieuChuan')) $sp->dinhMucTieuChuan = (int)$request->input('dinhMucTieuChuan');

        $sp->trangThai = $sp->tonHienTai > 0 ? 'Con_Hang' : 'Het_Hang';
        $sp->save();

        return response()->json([
            'status' => 'success',
            'message' => "Cập nhật sản phẩm '{$sp->tenSanPham}' thành công!",
            'data' => $sp
        ]);
    }

    /**
     * Nghiệp vụ 2.1: Nhập kho tổng sản phẩm minibar
     * POST /api/minibar-tai-san/nhap-kho
     */
    public function nhapKhoTong(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maSanPham' => 'required|integer',
            'soLuongNhap' => 'required|integer|min:1',
            'maNhanVien' => 'nullable|integer',
            'ghiChu' => 'nullable|string',
        ], [
            'maSanPham.required' => 'Vui lòng chọn sản phẩm cần nhập kho.',
            'soLuongNhap.required' => 'Vui lòng nhập số lượng nhập.',
            'soLuongNhap.min' => 'Số lượng nhập phải lớn hơn 0.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $sp = San_Pham_Minibar::find($request->input('maSanPham'));
        if (!$sp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $qty = (int)$request->input('soLuongNhap');
        $maNV = $request->input('maNhanVien') ?: 1;
        $nv = Nhan_Vien::find($maNV);
        $tenNV = $nv ? $nv->hoTen : 'Trần An (Admin)';

        DB::beginTransaction();
        try {
            // Cập nhật tồn kho tổng
            $sp->soLuongNhap = (int)$sp->soLuongNhap + $qty;
            $sp->tonHienTai = (int)$sp->tonHienTai + $qty;
            $sp->trangThai = $sp->tonHienTai > 0 ? 'Con_Hang' : 'Het_Hang';
            $sp->save();

            // Tạo mã phiếu nhập kho
            $maPhieu = 'PNK-' . Carbon::now()->format('Ymd') . '-' . str_pad(Phieu_Kiem_Ke::count() + 1, 3, '0', STR_PAD_LEFT);

            $chiTiet = [
                'sanPham' => [
                    'id' => $sp->id,
                    'maSanPham' => $sp->maSanPham,
                    'tenSanPham' => $sp->tenSanPham,
                    'donViTinh' => $sp->donViTinh,
                    'donGia' => $sp->donGia,
                    'soLuongNhap' => $qty,
                    'tonMoi' => $sp->tonHienTai,
                ]
            ];

            $phieu = Phieu_Kiem_Ke::create([
                'maPhieu' => $maPhieu,
                'maPhong' => null,
                'maLuuTru' => null,
                'maNhanVien' => $maNV,
                'tenNhanVien' => $tenNV,
                'loaiKiemKe' => 'Nhap_Kho',
                'thoiGian' => Carbon::now(),
                'trangThai' => 'Da_Xac_Nhan',
                'tongTienPhatSinh' => 0,
                'ghiChu' => $request->input('ghiChu', "Nhập kho {$qty} {$sp->donViTinh} {$sp->tenSanPham}"),
                'chiTiet' => $chiTiet,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Nhập kho thành công {$qty} {$sp->donViTinh} '{$sp->tenSanPham}'. Tồn kho hiện tại: {$sp->tonHienTai}.",
                'sanPham' => $sp,
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi nhập kho: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * THIẾT LẬP ĐỊNH MỨC & PHÒNG THỰC TẾ
     * =========================================================================
     */

    /**
     * Lấy danh sách phòng thực tế kèm thông tin minibar & lượt lưu trú hiện tại
     * GET /api/minibar-tai-san/phong
     */
    public function danhSachPhong(Request $request)
    {
        $tang = $request->query('tang');
        $trangThai = $request->query('trangThai');

        $query = Phong::with('loaiPhong')->orderBy('tenPhong', 'asc');

        if (!empty($tang) && $tang !== 'Tất cả') {
            $query->where('tang', (int)$tang);
        }
        if (!empty($trangThai) && $trangThai !== 'Tất cả') {
            $query->where('trangThai', $trangThai);
        }

        $phongs = $query->get()->map(function ($p) {
            // Tìm lượt lưu trú đang hoạt động của phòng này
            $activeStay = DB::table('Luu_Tru')
                ->join('Chi_Tiet_Dat_Phong', 'Luu_Tru.maChiTiet', '=', 'Chi_Tiet_Dat_Phong.id')
                ->join('Dat_Phong', 'Chi_Tiet_Dat_Phong.maDatPhong', '=', 'Dat_Phong.id')
                ->join('Khach_Hang', 'Dat_Phong.maKhachHang', '=', 'Khach_Hang.id')
                ->where('Chi_Tiet_Dat_Phong.maPhong', $p->id)
                ->where('Luu_Tru.trangThai', 'Dang_Luu_Tru')
                ->select(
                    'Luu_Tru.id as maLuuTru',
                    'Khach_Hang.hoTen as tenKhach',
                    'Khach_Hang.soDienThoai',
                    'Luu_Tru.thoiGianNhan',
                    'Luu_Tru.thoiGianTra'
                )
                ->first();

            // Đếm số lượng minibar và tài sản của phòng
            $minibarItems = Minibar_Phong::where('maPhong', $p->id)->count();
            $taiSanCount = Tai_San::where('maPhong', $p->id)->count();
            $taiSanHong = Tai_San::where('maPhong', $p->id)->whereIn('tinhTrang', ['Hư hỏng', 'Mất mát'])->count();

            // Trạng thái minibar của phòng
            $minibarStatus = 'Chưa kiểm kê';
            $latestInspection = Phieu_Kiem_Ke::where('maPhong', $p->id)->orderBy('id', 'desc')->first();
            if ($latestInspection) {
                if ($latestInspection->loaiKiemKe === 'Chuan_Bi_CheckIn') {
                    $minibarStatus = 'Đã chuẩn bị đón khách';
                } elseif ($latestInspection->loaiKiemKe === 'Bo_Sung_Minibar') {
                    $minibarStatus = 'Đã bổ sung trong ca';
                } elseif ($latestInspection->loaiKiemKe === 'Kiem_Ke_CheckOut') {
                    $minibarStatus = 'Đã kiểm kê trả phòng';
                }
            }

            return [
                'id' => $p->id,
                'tenPhong' => $p->tenPhong,
                'tang' => $p->tang,
                'trangThaiPhong' => $p->trangThai,
                'tenLoaiPhong' => $p->loaiPhong ? $p->loaiPhong->tenLoai : 'Phòng tiêu chuẩn',
                'activeStay' => $activeStay,
                'minibarStatus' => $minibarStatus,
                'soLuongSanPhamMinibar' => $minibarItems,
                'tongTaiSan' => $taiSanCount,
                'taiSanHong' => $taiSanHong,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total' => $phongs->count(),
            'data' => $phongs
        ]);
    }

    /**
     * Lấy chi tiết minibar của 1 phòng (cho màn hình chuẩn bị hoặc kiểm kê)
     * GET /api/minibar-tai-san/phong/{maPhong}/minibar
     */
    public function chiTietMinibarPhong($maPhong)
    {
        $phong = Phong::with('loaiPhong')->find($maPhong);
        if (!$phong) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phòng.'], 404);
        }

        // Lấy lượt lưu trú hiện tại
        $stay = DB::table('Luu_Tru')
            ->join('Chi_Tiet_Dat_Phong', 'Luu_Tru.maChiTiet', '=', 'Chi_Tiet_Dat_Phong.id')
            ->join('Dat_Phong', 'Chi_Tiet_Dat_Phong.maDatPhong', '=', 'Dat_Phong.id')
            ->join('Khach_Hang', 'Dat_Phong.maKhachHang', '=', 'Khach_Hang.id')
            ->where('Chi_Tiet_Dat_Phong.maPhong', $phong->id)
            ->where('Luu_Tru.trangThai', 'Dang_Luu_Tru')
            ->select(
                'Luu_Tru.id as maLuuTru',
                'Khach_Hang.hoTen as tenKhach',
                'Khach_Hang.soDienThoai',
                'Luu_Tru.thoiGianNhan',
                'Luu_Tru.thoiGianTra'
            )
            ->first();

        $allProducts = San_Pham_Minibar::all();

        // Lấy dữ liệu minibar hiện tại của phòng
        $existingItems = Minibar_Phong::where('maPhong', $phong->id)->get()->keyBy('maSanPham');

        $productList = $allProducts->map(function ($p) use ($existingItems) {
            $item = $existingItems->get($p->id);
            $tieuChuan = $item ? $item->soLuongTieuChuan : ($p->dinhMucTieuChuan ?: 2);
            $banDau = $item ? $item->soLuongBanDau : $tieuChuan;
            $boSung = $item ? $item->soLuongBoSung : 0;
            $thucTe = $item ? $item->soLuongThucTe : $banDau;
            $duKien = $banDau + $boSung;
            $chenhLech = max(0, $duKien - $thucTe);

            return [
                'maSanPham' => $p->id,
                'maCode' => $p->maSanPham ?: 'MB-' . str_pad($p->id, 2, '0', STR_PAD_LEFT),
                'tenSanPham' => $p->tenSanPham,
                'donViTinh' => $p->donViTinh,
                'donGia' => (int)$p->donGia,
                'donGiaDinhDang' => number_format($p->donGia, 0, ',', '.') . ' đ',
                'loaiSanPham' => $p->loaiSanPham ?: 'Tinh_Phi',
                'tonKhoTong' => (int)$p->tonHienTai,
                'soLuongTieuChuan' => $tieuChuan,
                'soLuongBanDau' => $banDau,
                'soLuongBoSung' => $boSung,
                'soLuongDuKien' => $duKien,
                'soLuongThucTe' => $thucTe,
                'soLuongChenhLech' => $chenhLech,
                'lyDoChenhLech' => $item ? $item->lyDoChenhLech : null,
                'soLuongTieuThu' => $item ? $item->soLuongTieuThu : 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'phong' => [
                'id' => $phong->id,
                'tenPhong' => $phong->tenPhong,
                'tang' => $phong->tang,
                'loaiPhong' => $phong->loaiPhong ? $phong->loaiPhong->tenLoai : 'Tiêu chuẩn',
            ],
            'stay' => $stay,
            'sanPham' => $productList
        ]);
    }

    /**
     * =========================================================================
     * NGHIỆP VỤ 2.2: THIẾT LẬP & CHUẨN BỊ MINIBAR TRƯỚC CHECK-IN
     * =========================================================================
     */
    public function chuanBiCheckIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maPhong' => 'required|integer',
            'danhSach' => 'required|array|min:1',
            'danhSach.*.maSanPham' => 'required|integer',
            'danhSach.*.soLuongThucTe' => 'required|integer|min:0',
            'danhSach.*.soLuongBoSung' => 'nullable|integer|min:0',
            'maNhanVien' => 'nullable|integer',
            'ghiChu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $maPhong = (int)$request->input('maPhong');
        $phong = Phong::find($maPhong);
        if (!$phong) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phòng.'], 404);
        }

        $maLuuTru = $request->input('maLuuTru');
        $maNV = $request->input('maNhanVien') ?: 1;
        $nv = Nhan_Vien::find($maNV);
        $tenNV = $nv ? $nv->hoTen : 'Trần Hoa (Buồng phòng)';

        DB::beginTransaction();
        try {
            $itemsSummary = [];

            foreach ($request->input('danhSach') as $item) {
                $sp = San_Pham_Minibar::find($item['maSanPham']);
                if (!$sp) continue;

                $soLuongBoSung = (int)($item['soLuongBoSung'] ?? 0);
                $soLuongThucTe = (int)$item['soLuongThucTe'];

                // Nếu có bổ sung từ kho tổng để đạt tiêu chuẩn
                if ($soLuongBoSung > 0) {
                    if ($sp->tonHienTai < $soLuongBoSung) {
                        throw new \Exception("Sản phẩm '{$sp->tenSanPham}' trong kho tổng chỉ còn {$sp->tonHienTai}, không đủ {$soLuongBoSung} để bổ sung vào phòng.");
                    }
                    $sp->tonHienTai -= $soLuongBoSung;
                    $sp->soLuongXuat += $soLuongBoSung;
                    $sp->trangThai = $sp->tonHienTai > 0 ? 'Con_Hang' : 'Het_Hang';
                    $sp->save();
                }

                $banDau = $soLuongThucTe + $soLuongBoSung;

                // Cập nhật hoặc tạo mới bản ghi Minibar_Phong
                Minibar_Phong::updateOrCreate(
                    ['maPhong' => $maPhong, 'maSanPham' => $sp->id],
                    [
                        'maLuuTru' => $maLuuTru,
                        'soLuongTieuChuan' => (int)($item['soLuongTieuChuan'] ?? $sp->dinhMucTieuChuan ?: 2),
                        'soLuongBanDau' => $banDau,
                        'soLuongBoSung' => 0,
                        'soLuongThucTe' => $banDau,
                        'soLuongTieuThu' => 0,
                        'soLuongChenhLech' => 0,
                        'lyDoChenhLech' => null,
                        'donGiaApDung' => $sp->donGia,
                        'thanhTien' => 0,
                        'trangThai' => 'Da_Chuan_Bi',
                        'ghiChu' => $request->input('ghiChu')
                    ]
                );

                $itemsSummary[] = [
                    'tenSanPham' => $sp->tenSanPham,
                    'soLuongBanDau' => $banDau,
                    'boSungTuKho' => $soLuongBoSung,
                    'donGia' => $sp->donGia,
                    'loai' => $sp->loaiSanPham
                ];
            }

            // Tạo phiếu kiểm kê chuẩn bị check-in
            $maPhieu = 'PCK-' . Carbon::now()->format('Ymd') . '-P' . $phong->tenPhong . '-' . rand(100, 999);
            $phieu = Phieu_Kiem_Ke::create([
                'maPhieu' => $maPhieu,
                'maPhong' => $maPhong,
                'maLuuTru' => $maLuuTru,
                'maNhanVien' => $maNV,
                'tenNhanVien' => $tenNV,
                'loaiKiemKe' => 'Chuan_Bi_CheckIn',
                'thoiGian' => Carbon::now(),
                'trangThai' => 'Da_Xac_Nhan',
                'tongTienPhatSinh' => 0,
                'ghiChu' => "Xác nhận minibar phòng {$phong->tenPhong} đã đầy đủ tiêu chuẩn sẵn sàng đón khách.",
                'chiTiet' => ['sanPham' => $itemsSummary]
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Xác nhận minibar phòng {$phong->tenPhong} đã sẵn sàng đón khách!",
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * =========================================================================
     * NGHIỆP VỤ 2.3: BỔ SUNG MINIBAR TRONG THỜI GIAN LƯU TRÚ
     * =========================================================================
     */
    public function boSungMinibar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maPhong' => 'required|integer',
            'maSanPham' => 'required|integer',
            'soLuongBoSung' => 'required|integer|min:1',
            'maNhanVien' => 'nullable|integer',
            'maYeuCauDichVu' => 'nullable|integer',
            'ghiChu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $maPhong = (int)$request->input('maPhong');
        $phong = Phong::find($maPhong);
        if (!$phong) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phòng.'], 404);
        }

        $sp = San_Pham_Minibar::find($request->input('maSanPham'));
        if (!$sp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy sản phẩm.'], 404);
        }

        $soLuong = (int)$request->input('soLuongBoSung');

        // Kiểm tra tồn kho tổng
        if ($sp->tonHienTai < $soLuong) {
            return response()->json([
                'status' => 'error',
                'message' => "Tồn kho tổng của '{$sp->tenSanPham}' hiện chỉ còn {$sp->tonHienTai} {$sp->donViTinh}, không đủ để bổ sung {$soLuong} {$sp->donViTinh}."
            ], 422);
        }

        $maNV = $request->input('maNhanVien') ?: 1;
        $nv = Nhan_Vien::find($maNV);
        $tenNV = $nv ? $nv->hoTen : 'Trần Hoa (Buồng phòng)';

        DB::beginTransaction();
        try {
            // 1. Trừ tồn kho tổng
            $sp->tonHienTai -= $soLuong;
            $sp->soLuongXuat += $soLuong;
            $sp->trangThai = $sp->tonHienTai > 0 ? 'Con_Hang' : 'Het_Hang';
            $sp->save();

            // 2. Lấy hoặc tạo bản ghi minibar trong phòng
            $minibarItem = Minibar_Phong::firstOrNew(['maPhong' => $maPhong, 'maSanPham' => $sp->id]);
            $minibarItem->soLuongBoSung = (int)$minibarItem->soLuongBoSung + $soLuong;
            $minibarItem->soLuongThucTe = (int)$minibarItem->soLuongThucTe + $soLuong;
            $minibarItem->donGiaApDung = $sp->donGia;
            $minibarItem->trangThai = 'Da_Chuan_Bi';
            $minibarItem->save();

            // 3. Nếu xuất phát từ Yêu cầu dịch vụ, cập nhật trạng thái yêu cầu
            $maYeuCau = $request->input('maYeuCauDichVu');
            if ($maYeuCau) {
                $yeuCau = Yeu_Cau_Dich_Vu::find($maYeuCau);
                if ($yeuCau && $yeuCau->trangThai !== 'Da_Hoan_Thanh') {
                    $yeuCau->trangThai = 'Da_Hoan_Thanh';
                    $yeuCau->ghiChu = ($yeuCau->ghiChu ? $yeuCau->ghiChu . ' | ' : '') . "Đã bổ sung {$soLuong} {$sp->donViTinh} {$sp->tenSanPham} vào phòng {$phong->tenPhong}.";
                    $yeuCau->save();
                }
            }

            // 4. Tạo phiếu kiểm kê bổ sung
            $maPhieu = 'PBS-' . Carbon::now()->format('Ymd') . '-P' . $phong->tenPhong . '-' . rand(100, 999);
            $phieu = Phieu_Kiem_Ke::create([
                'maPhieu' => $maPhieu,
                'maPhong' => $maPhong,
                'maLuuTru' => $request->input('maLuuTru'),
                'maNhanVien' => $maNV,
                'tenNhanVien' => $tenNV,
                'loaiKiemKe' => 'Bo_Sung_Minibar',
                'thoiGian' => Carbon::now(),
                'trangThai' => 'Da_Xac_Nhan',
                'tongTienPhatSinh' => 0,
                'ghiChu' => $request->input('ghiChu', "Bổ sung {$soLuong} {$sp->donViTinh} {$sp->tenSanPham} vào phòng {$phong->tenPhong}"),
                'chiTiet' => [
                    'sanPham' => [
                        'maSanPham' => $sp->id,
                        'tenSanPham' => $sp->tenSanPham,
                        'soLuongBoSung' => $soLuong,
                        'soLuongMoiTrongPhong' => $minibarItem->soLuongThucTe,
                        'tonKhoTongConLai' => $sp->tonHienTai
                    ]
                ]
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Bổ sung thành công {$soLuong} {$sp->donViTinh} '{$sp->tenSanPham}' cho phòng {$phong->tenPhong}. Tồn kho tổng còn: {$sp->tonHienTai}.",
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi bổ sung: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * NGHIỆP VỤ 2.4: KIỂM KÊ MINIBAR KHI KHÁCH CHECK-OUT
     * =========================================================================
     */
    public function kiemKeCheckOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maPhong' => 'required|integer',
            'maLuuTru' => 'required|integer',
            'danhSach' => 'required|array|min:1',
            'danhSach.*.maSanPham' => 'required|integer',
            'danhSach.*.soLuongThucTe' => 'required|integer|min:0',
            'danhSach.*.lyDoChenhLech' => 'nullable|string',
            'danhSach.*.soLuongTieuThu' => 'nullable|integer|min:0',
            'maNhanVien' => 'nullable|integer',
            'ghiChu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $maPhong = (int)$request->input('maPhong');
        $maLuuTru = (int)$request->input('maLuuTru');
        $phong = Phong::find($maPhong);
        $luuTru = Luu_Tru::find($maLuuTru);

        if (!$phong || !$luuTru) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy thông tin phòng hoặc lượt lưu trú.'], 404);
        }

        $maNV = $request->input('maNhanVien') ?: 1;
        $nv = Nhan_Vien::find($maNV);
        $tenNV = $nv ? $nv->hoTen : 'Mai Linh (Lễ tân)';

        DB::beginTransaction();
        try {
            $tongTienTieuThu = 0;
            $itemsInspection = [];
            $coChenhLech = false;

            foreach ($request->input('danhSach') as $item) {
                $sp = San_Pham_Minibar::find($item['maSanPham']);
                if (!$sp) continue;

                $minibarItem = Minibar_Phong::where('maPhong', $maPhong)
                    ->where('maSanPham', $sp->id)
                    ->first();

                $banDau = $minibarItem ? $minibarItem->soLuongBanDau : ($sp->dinhMucTieuChuan ?: 2);
                $boSung = $minibarItem ? $minibarItem->soLuongBoSung : 0;
                $duKien = $banDau + $boSung;
                $thucTe = (int)$item['soLuongThucTe'];
                $chenhLech = max(0, $duKien - $thucTe);

                $lyDo = $item['lyDoChenhLech'] ?? ($chenhLech > 0 ? 'Khách đã sử dụng' : null);

                // YÊU CẦU BẮT BUỘC: Chỉ khi nhân viên xác nhận là 'Khách đã sử dụng' thì mới ghi nhận tiêu thụ tính tiền
                $soLuongTieuThu = 0;
                if ($chenhLech > 0) {
                    $coChenhLech = true;
                    if ($lyDo === 'Khách đã sử dụng') {
                        $soLuongTieuThu = (int)($item['soLuongTieuThu'] ?? $chenhLech);
                    }
                }

                $thanhTien = 0;
                // Nếu sản phẩm miễn phí -> không tính tiền (0 đ)
                if ($sp->loaiSanPham !== 'Mien_Phi' && $soLuongTieuThu > 0) {
                    $thanhTien = $soLuongTieuThu * $sp->donGia;
                    $tongTienTieuThu += $thanhTien;

                    // Ghi nhận vào bảng Phat_Sinh_Minibar
                    Phat_Sinh_Minibar::create([
                        'maLuuTru' => $maLuuTru,
                        'maSanPham' => $sp->id,
                        'soLuong' => $soLuongTieuThu,
                        'donGiaApDung' => $sp->donGia,
                        'thoiGian' => Carbon::now()
                    ]);
                }

                // Cập nhật trạng thái Minibar_Phong
                if ($minibarItem) {
                    $minibarItem->soLuongThucTe = $thucTe;
                    $minibarItem->soLuongChenhLech = $chenhLech;
                    $minibarItem->soLuongTieuThu = $soLuongTieuThu;
                    $minibarItem->lyDoChenhLech = $lyDo;
                    $minibarItem->donGiaApDung = $sp->donGia;
                    $minibarItem->thanhTien = $thanhTien;
                    $minibarItem->trangThai = $chenhLech > 0 && $lyDo !== 'Khách đã sử dụng' ? 'Co_Chenh_Lech' : 'Da_Xac_Nhan';
                    $minibarItem->save();
                }

                $itemsInspection[] = [
                    'maSanPham' => $sp->id,
                    'maCode' => $sp->maSanPham,
                    'tenSanPham' => $sp->tenSanPham,
                    'donViTinh' => $sp->donViTinh,
                    'loaiSanPham' => $sp->loaiSanPham,
                    'donGia' => $sp->donGia,
                    'soLuongBanDau' => $banDau,
                    'soLuongBoSung' => $boSung,
                    'soLuongDuKien' => $duKien,
                    'soLuongThucTe' => $thucTe,
                    'soLuongChenhLech' => $chenhLech,
                    'soLuongTieuThu' => $soLuongTieuThu,
                    'lyDoChenhLech' => $lyDo,
                    'thanhTien' => $thanhTien,
                    'thanhTienDinhDang' => number_format($thanhTien, 0, ',', '.') . ' đ',
                ];
            }

            // Tạo phiếu kiểm kê check-out
            $maPhieu = 'PKK-OUT-' . Carbon::now()->format('Ymd') . '-P' . $phong->tenPhong . '-' . rand(100, 999);
            $phieu = Phieu_Kiem_Ke::create([
                'maPhieu' => $maPhieu,
                'maPhong' => $maPhong,
                'maLuuTru' => $maLuuTru,
                'maNhanVien' => $maNV,
                'tenNhanVien' => $tenNV,
                'loaiKiemKe' => 'Kiem_Ke_CheckOut',
                'thoiGian' => Carbon::now(),
                'trangThai' => $coChenhLech ? 'Co_Chenh_Lech' : 'Da_Xac_Nhan',
                'tongTienPhatSinh' => $tongTienTieuThu,
                'ghiChu' => $request->input('ghiChu', "Kiểm kê trả phòng P.{$phong->tenPhong}. Tổng tiền tiêu thụ: " . number_format($tongTienTieuThu, 0, ',', '.') . " đ"),
                'chiTiet' => [
                    'phong' => $phong->tenPhong,
                    'maLuuTru' => $maLuuTru,
                    'tongTienTieuThu' => $tongTienTieuThu,
                    'sanPham' => $itemsInspection
                ]
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Xác nhận kiểm kê minibar phòng {$phong->tenPhong} thành công! Tiền phát sinh tiêu thụ: " . number_format($tongTienTieuThu, 0, ',', '.') . " đ.",
                'tongTienTieuThu' => $tongTienTieuThu,
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi kiểm kê: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * TAB 2: TÀI SẢN THEO PHÒNG
     * =========================================================================
     */

    /**
     * Lấy danh sách tài sản phòng
     * GET /api/minibar-tai-san/tai-san
     */
    public function danhSachTaiSan(Request $request)
    {
        $maPhong = $request->query('maPhong');
        $tinhTrang = $request->query('tinhTrang', 'Tất cả');
        $q = trim($request->query('q', ''));

        $query = Tai_San::with('phong')->orderBy('maPhong', 'asc')->orderBy('id', 'asc');

        if (!empty($maPhong) && $maPhong !== 'Tất cả') {
            $query->where('maPhong', (int)$maPhong);
        }

        if (!empty($tinhTrang) && $tinhTrang !== 'Tất cả') {
            $query->where('tinhTrang', $tinhTrang);
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('tenTaiSan', 'like', "%{$q}%")
                    ->orWhere('maTaiSan', 'like', "%{$q}%")
                    ->orWhereHas('phong', fn($p) => $p->where('tenPhong', 'like', "%{$q}%"));
            });
        }

        $items = $query->get()->map(function ($ts) {
            return [
                'id' => $ts->id,
                'maTaiSan' => $ts->maTaiSan ?: 'TS-' . str_pad($ts->id, 3, '0', STR_PAD_LEFT),
                'maPhong' => $ts->maPhong,
                'tenPhong' => $ts->phong ? $ts->phong->tenPhong : 'Chưa phân phòng',
                'tang' => $ts->phong ? $ts->phong->tang : null,
                'tenTaiSan' => $ts->tenTaiSan,
                'soLuong' => (int)($ts->soLuong ?: 1),
                'tinhTrang' => $ts->tinhTrang,
                'giaTri' => (int)$ts->giaTri,
                'giaTriDinhDang' => number_format($ts->giaTri, 0, ',', '.') . ' đ',
                'ghiChu' => $ts->ghiChu,
            ];
        });

        $allTS = Tai_San::all();
        $thongKe = [
            'tongTaiSan' => $allTS->count(),
            'binhThuong' => $allTS->whereIn('tinhTrang', ['Bình thường', 'Tốt'])->count(),
            'huHong' => $allTS->where('tinhTrang', 'Hư hỏng')->count(),
            'matMat' => $allTS->where('tinhTrang', 'Mất mát')->count(),
            'suaChua' => $allTS->where('tinhTrang', 'Đang sửa chữa')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'thongKe' => $thongKe,
            'total' => $items->count(),
            'data' => $items
        ]);
    }

    /**
     * Thêm tài sản trang bị cho phòng
     * POST /api/minibar-tai-san/tai-san
     */
    public function themTaiSan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maPhong' => 'required|integer',
            'tenTaiSan' => 'required|string|max:100',
            'soLuong' => 'nullable|integer|min:1',
            'giaTri' => 'required|numeric|min:0',
            'tinhTrang' => 'nullable|string',
            'ghiChu' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $phong = Phong::find($request->input('maPhong'));
        if (!$phong) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phòng.'], 404);
        }

        $nextId = (Tai_San::max('id') ?: 0) + 1;
        $maTS = $request->input('maTaiSan') ?: 'TS-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $ts = Tai_San::create([
            'maTaiSan' => strtoupper($maTS),
            'maPhong' => $phong->id,
            'tenTaiSan' => trim($request->input('tenTaiSan')),
            'soLuong' => (int)$request->input('soLuong', 1),
            'tinhTrang' => $request->input('tinhTrang', 'Bình thường'),
            'giaTri' => (int)$request->input('giaTri', 0),
            'ghiChu' => $request->input('ghiChu')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Thêm tài sản '{$ts->tenTaiSan}' cho phòng {$phong->tenPhong} thành công!",
            'data' => $ts
        ], 201);
    }

    /**
     * Nghiệp vụ 3.4: Báo hỏng hoặc mất tài sản phòng
     * POST /api/minibar-tai-san/bao-hong-tai-san
     */
    public function baoHongTaiSan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maTaiSan' => 'required|integer',
            'loaiPhatSinh' => 'required|in:Hư hỏng,Mất mát',
            'moTa' => 'required|string',
            'soLuong' => 'nullable|integer|min:1',
            'soTienBoiThuong' => 'nullable|numeric|min:0',
            'maNhanVien' => 'nullable|integer',
        ], [
            'moTa.required' => 'Vui lòng nhập mô tả chi tiết sự cố.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $ts = Tai_San::with('phong')->find($request->input('maTaiSan'));
        if (!$ts) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy tài sản.'], 404);
        }

        $maPhong = $ts->maPhong;
        $loaiPhatSinh = $request->input('loaiPhatSinh'); // 'Hư hỏng' hoặc 'Mất mát'
        $tienBoiThuong = (float)$request->input('soTienBoiThuong', 0); // Cho phép 0 đ
        $moTa = trim($request->input('moTa'));
        $soLuongHong = (int)$request->input('soLuong', 1);

        // Lấy lượt lưu trú hiện tại nếu có
        $stay = DB::table('Luu_Tru')
            ->join('Chi_Tiet_Dat_Phong', 'Luu_Tru.maChiTiet', '=', 'Chi_Tiet_Dat_Phong.id')
            ->where('Chi_Tiet_Dat_Phong.maPhong', $maPhong)
            ->where('Luu_Tru.trangThai', 'Dang_Luu_Tru')
            ->select('Luu_Tru.id')
            ->first();

        $maLuuTru = $request->input('maLuuTru') ?: ($stay ? $stay->id : 1);
        $maNV = $request->input('maNhanVien') ?: 1;
        $nv = Nhan_Vien::find($maNV);
        $tenNV = $nv ? $nv->hoTen : 'Trần An (Admin)';

        DB::beginTransaction();
        try {
            // 1. Cập nhật tình trạng tài sản
            $ts->tinhTrang = $loaiPhatSinh;
            $ts->ghiChu = ($ts->ghiChu ? $ts->ghiChu . ' | ' : '') . Carbon::now()->format('d/m/Y') . ": {$loaiPhatSinh} ({$moTa})";
            $ts->save();

            // 2. Ghi nhận sự cố vào bảng Ghi_Nhan_Tai_San
            $ghiNhan = Ghi_Nhan_Tai_San::create([
                'maPhong' => $maPhong,
                'maLuuTru' => $maLuuTru,
                'maTaiSan' => $ts->id,
                'soLuong' => $soLuongHong,
                'maNhanVien' => $maNV,
                'loaiPhatSinh' => $loaiPhatSinh,
                'thoiGian' => Carbon::now(),
                'soTienBoiThuong' => $tienBoiThuong,
                'ghiChu' => $moTa,
                'trangThaiXuLy' => $tienBoiThuong > 0 ? 'Cho_Xac_Minh' : 'Mien_Tru'
            ]);

            // 3. Tạo phiếu trong Phieu_Kiem_Ke
            $maPhieu = 'PSC-' . Carbon::now()->format('Ymd') . '-TS' . $ts->id . '-' . rand(100, 999);
            $phieu = Phieu_Kiem_Ke::create([
                'maPhieu' => $maPhieu,
                'maPhong' => $maPhong,
                'maLuuTru' => $maLuuTru,
                'maNhanVien' => $maNV,
                'tenNhanVien' => $tenNV,
                'loaiKiemKe' => 'Bao_Hong_Mat',
                'thoiGian' => Carbon::now(),
                'trangThai' => 'Da_Xac_Nhan',
                'tongTienPhatSinh' => $tienBoiThuong,
                'ghiChu' => "Báo {$loaiPhatSinh} tài sản '{$ts->tenTaiSan}' tại phòng " . ($ts->phong ? $ts->phong->tenPhong : '') . ". Chi phí: " . number_format($tienBoiThuong, 0, ',', '.') . " đ. Lý do: {$moTa}",
                'chiTiet' => [
                    'taiSan' => [
                        'id' => $ts->id,
                        'maTaiSan' => $ts->maTaiSan,
                        'tenTaiSan' => $ts->tenTaiSan,
                        'soLuong' => $soLuongHong,
                        'loaiPhatSinh' => $loaiPhatSinh,
                        'soTienBoiThuong' => $tienBoiThuong,
                        'moTa' => $moTa,
                    ]
                ]
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Ghi nhận sự cố {$loaiPhatSinh} tài sản '{$ts->tenTaiSan}' thành công!",
                'ghiNhan' => $ghiNhan,
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi ghi nhận sự cố: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================================================================
     * TAB 3: NHẬT KÝ KIỂM KÊ
     * =========================================================================
     */

    /**
     * Lấy danh sách nhật ký kiểm kê
     * GET /api/minibar-tai-san/nhat-ky
     */
    public function danhSachNhatKy(Request $request)
    {
        $q = trim($request->query('q', ''));
        $loai = $request->query('loaiKiemKe', 'Tất cả');
        $trangThai = $request->query('trangThai', 'Tất cả');
        $ngay = $request->query('ngay');

        $query = Phieu_Kiem_Ke::with(['phong', 'nhanVien'])->orderBy('thoiGian', 'desc')->orderBy('id', 'desc');

        if (!empty($loai) && $loai !== 'Tất cả') {
            $query->where('loaiKiemKe', $loai);
        }

        if (!empty($trangThai) && $trangThai !== 'Tất cả') {
            $query->where('trangThai', $trangThai);
        }

        if (!empty($ngay)) {
            $query->whereDate('thoiGian', $ngay);
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('maPhieu', 'like', "%{$q}%")
                    ->orWhere('ghiChu', 'like', "%{$q}%")
                    ->orWhere('tenNhanVien', 'like', "%{$q}%")
                    ->orWhereHas('phong', fn($p) => $p->where('tenPhong', 'like', "%{$q}%"));
            });
        }

        $items = $query->get()->map(function ($p) {
            $loaiText = 'Kiểm kê';
            switch ($p->loaiKiemKe) {
                case 'Nhap_Kho': $loaiText = 'Nhập kho tổng'; break;
                case 'Chuan_Bi_CheckIn': $loaiText = 'Chuẩn bị trước check-in'; break;
                case 'Bo_Sung_Minibar': $loaiText = 'Bổ sung minibar'; break;
                case 'Kiem_Ke_CheckOut': $loaiText = 'Kiểm kê khi check-out'; break;
                case 'Kiem_Tra_Tai_San': $loaiText = 'Kiểm tra tài sản phòng'; break;
                case 'Bao_Hong_Mat': $loaiText = 'Báo hỏng / mất tài sản'; break;
            }

            return [
                'id' => $p->id,
                'maPhieu' => $p->maPhieu,
                'maPhong' => $p->maPhong,
                'tenPhong' => $p->phong ? $p->phong->tenPhong : ($p->maPhong ? 'P.' . $p->maPhong : 'Kho tổng'),
                'maLuuTru' => $p->maLuuTru ? '#LT-' . $p->maLuuTru : '---',
                'loaiKiemKe' => $p->loaiKiemKe,
                'loaiKiemKeText' => $loaiText,
                'thoiGian' => $p->thoiGian ? Carbon::parse($p->thoiGian)->format('d/m/Y H:i') : '',
                'maNhanVien' => $p->maNhanVien,
                'tenNhanVien' => $p->tenNhanVien ?: ($p->nhanVien ? $p->nhanVien->hoTen : 'Nhân viên'),
                'trangThai' => $p->trangThai,
                'trangThaiText' => $p->trangThai === 'Da_Xac_Nhan' ? 'Đã xác nhận' : ($p->trangThai === 'Co_Chenh_Lech' ? 'Có chênh lệch' : 'Chờ xác nhận'),
                'tongTienPhatSinh' => (int)$p->tongTienPhatSinh,
                'tongTienPhatSinhDinhDang' => number_format($p->tongTienPhatSinh, 0, ',', '.') . ' đ',
                'ghiChu' => $p->ghiChu,
                'chiTiet' => $p->chiTiet,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total' => $items->count(),
            'data' => $items
        ]);
    }

    /**
     * Xem chi tiết 1 phiếu kiểm kê
     * GET /api/minibar-tai-san/nhat-ky/{id}
     */
    public function chiTietNhatKy($id)
    {
        $p = Phieu_Kiem_Ke::with(['phong', 'nhanVien'])->find($id);
        if (!$p) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu kiểm kê.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $p->id,
                'maPhieu' => $p->maPhieu,
                'maPhong' => $p->maPhong,
                'tenPhong' => $p->phong ? $p->phong->tenPhong : ($p->maPhong ? 'P.' . $p->maPhong : 'Kho tổng'),
                'maLuuTru' => $p->maLuuTru ? '#LT-' . $p->maLuuTru : '---',
                'loaiKiemKe' => $p->loaiKiemKe,
                'thoiGian' => $p->thoiGian ? Carbon::parse($p->thoiGian)->format('d/m/Y H:i') : '',
                'tenNhanVien' => $p->tenNhanVien ?: ($p->nhanVien ? $p->nhanVien->hoTen : 'Nhân viên'),
                'trangThai' => $p->trangThai,
                'tongTienPhatSinh' => (int)$p->tongTienPhatSinh,
                'tongTienPhatSinhDinhDang' => number_format($p->tongTienPhatSinh, 0, ',', '.') . ' đ',
                'ghiChu' => $p->ghiChu,
                'chiTiet' => $p->chiTiet,
            ]
        ]);
    }
}
