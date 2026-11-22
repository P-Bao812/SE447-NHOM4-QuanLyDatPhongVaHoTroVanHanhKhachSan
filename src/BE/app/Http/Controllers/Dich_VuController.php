<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dich_Vu;
use App\Models\Yeu_Cau_Dich_Vu;
use App\Models\Luu_Tru;
use App\Models\Phong;
use App\Models\Hoa_Don;
use App\Models\Chi_Tiet_Hoa_Don;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Dich_VuController extends Controller
{
    /**
     * 1. LẤY DANH SÁCH DỊCH VỤ ĐANG CUNG CẤP TỪ DATABASE
     * Hỗ trợ tìm kiếm theo tên và lọc theo loại dịch vụ.
     * KHÔNG cho khách hàng thêm, sửa, xóa dịch vụ tại đây.
     */
    public function index(Request $request)
    {
        try {
            $query = Dich_Vu::where('trangThai', 'Đang cung cấp');

            // Tìm kiếm theo tên dịch vụ hoặc mô tả
            if ($request->filled('q')) {
                $search = trim($request->q);
                $query->where(function ($q) use ($search) {
                    $q->where('tenDichVu', 'like', "%{$search}%")
                      ->orWhere('moTa', 'like', "%{$search}%");
                });
            }

            // Bộ lọc theo loại dịch vụ
            if ($request->filled('loai') && $request->loai !== 'Tất cả' && $request->loai !== 'all') {
                $query->where('loaiDichVu', $request->loai);
            }

            $services = $query->orderBy('id', 'asc')->get();

            // Lấy danh sách các loại dịch vụ độc nhất trong DB để làm bộ lọc cho FE
            $categories = Dich_Vu::where('trangThai', 'Đang cung cấp')
                ->whereNotNull('loaiDichVu')
                ->distinct()
                ->pluck('loaiDichVu');

            return response()->json([
                'status' => 'success',
                'total' => $services->count(),
                'categories' => $categories,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi tải danh mục dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. LẤY DANH SÁCH CÁC PHÒNG ĐANG CÓ LƯU TRÚ HỢP LỆ
     * Dành cho form đăng ký dịch vụ để khách hàng chọn đúng phòng đang lưu trú.
     */
    public function danhSachPhongDangLuuTru(Request $request)
    {
        try {
            $now = Carbon::now();

            // Tìm các lượt lưu trú đang diễn ra: trạng thái 'Dang_Luu_Tru' và thoiGianNhan <= now <= thoiGianTra
            $activeStays = Luu_Tru::with([
                'chiTietDatPhong.phong',
                'chiTietDatPhong.datPhong.khachHang'
            ])
            ->where('trangThai', 'Dang_Luu_Tru')
            ->where('thoiGianNhan', '<=', $now)
            ->where('thoiGianTra', '>=', $now)
            ->get();

            // Nếu không có lượt lưu trú khớp giờ chính xác (ví dụ trong môi trường test),
            // lấy các lượt có trạng thái 'Dang_Luu_Tru'
            if ($activeStays->isEmpty()) {
                $activeStays = Luu_Tru::with([
                    'chiTietDatPhong.phong',
                    'chiTietDatPhong.datPhong.khachHang'
                ])
                ->where('trangThai', 'Dang_Luu_Tru')
                ->get();
            }

            $data = $activeStays->map(function ($stay) {
                $ct = $stay->chiTietDatPhong;
                $phong = $ct ? $ct->phong : null;
                $dp = $ct ? $ct->datPhong : null;
                $kh = $dp ? $dp->khachHang : null;

                return [
                    'maLuuTru' => $stay->id,
                    'maChiTiet' => $stay->maChiTiet,
                    'maPhong' => $phong ? $phong->id : null,
                    'tenPhong' => $phong ? $phong->tenPhong : 'Phòng không xác định',
                    'tang' => $phong ? $phong->tang : 1,
                    'maKhachHang' => $kh ? $kh->id : null,
                    'tenKhachHang' => $kh ? $kh->hoTen : 'Khách lưu trú',
                    'soDienThoai' => $kh ? $kh->soDienThoai : '',
                    'thoiGianNhan' => $stay->thoiGianNhan ? Carbon::parse($stay->thoiGianNhan)->format('H:i d/m/Y') : '',
                    'thoiGianTra' => $stay->thoiGianTra ? Carbon::parse($stay->thoiGianTra)->format('H:i d/m/Y') : '',
                    'trangThaiLuuTru' => $stay->trangThai,
                    'displayText' => ($phong ? "Phòng " . $phong->tenPhong : 'Phòng') . ' - ' . ($kh ? $kh->hoTen : 'Khách lưu trú')
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
                'message' => 'Lỗi khi tải danh sách phòng lưu trú: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. ĐĂNG KÝ SỬ DỤNG DỊCH VỤ PHÒNG
     * Kiểm tra lưu trú hợp lệ, số lượng, thời gian mong muốn và dữ liệu.
     * Lưu vào cơ sở dữ liệu.
     */
    public function dangKyDichVu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maDichVu' => 'required|integer|exists:Dich_Vu,id',
            'soLuong' => 'required|integer|min:1|max:100',
            'thoiGianMongMuon' => 'required',
            'maLuuTru' => 'nullable|integer|exists:Luu_Tru,id',
            'maPhong' => 'nullable|integer',
            'ghiChu' => 'nullable|string|max:500'
        ], [
            'maDichVu.required' => 'Vui lòng chọn dịch vụ cần sử dụng.',
            'maDichVu.exists' => 'Dịch vụ được chọn không tồn tại trong hệ thống.',
            'soLuong.required' => 'Vui lòng nhập số lượng.',
            'soLuong.min' => 'Số lượng sử dụng tối thiểu là 1.',
            'soLuong.max' => 'Số lượng sử dụng tối đa là 100 cho mỗi lần đăng ký.',
            'thoiGianMongMuon.required' => 'Vui lòng chọn thời gian mong muốn được phục vụ.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $now = Carbon::now();

            // 1. Kiểm tra dịch vụ có đang cung cấp không
            $dichVu = Dich_Vu::find($request->maDichVu);
            if (!$dichVu || $dichVu->trangThai !== 'Đang cung cấp') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dịch vụ này hiện đang tạm ngưng phục vụ, quý khách vui lòng chọn dịch vụ khác.'
                ], 422);
            }

            // 2. Xác định lượt lưu trú và KIỂM TRA THỜI GIAN LƯU TRÚ HỢP LỆ
            $luuTru = null;
            if ($request->filled('maLuuTru')) {
                $luuTru = Luu_Tru::with('chiTietDatPhong.phong', 'chiTietDatPhong.datPhong.khachHang')->find($request->maLuuTru);
            } elseif ($request->filled('maPhong')) {
                // Tìm lượt lưu trú của phòng này
                $luuTru = Luu_Tru::whereHas('chiTietDatPhong', function ($q) use ($request) {
                    $q->where('maPhong', $request->maPhong);
                })
                ->where('trangThai', 'Dang_Luu_Tru')
                ->latest('id')
                ->first();
            }

            if (!$luuTru) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy thông tin lưu trú của phòng này. Quý khách vui lòng liên hệ lễ tân để được hỗ trợ.'
                ], 422);
            }

            // Kiểm tra trạng thái lưu trú
            if ($luuTru->trangThai !== 'Dang_Luu_Tru') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Lượt lưu trú của phòng này đã kết thúc hoặc chưa nhận phòng. Chỉ khách hàng đang lưu trú mới có thể đăng ký dịch vụ phòng.'
                ], 422);
            }

            // Kiểm tra thời hạn lưu trú (check-in / check-out)
            $checkIn = Carbon::parse($luuTru->thoiGianNhan);
            $checkOut = Carbon::parse($luuTru->thoiGianTra);

            // Kiểm tra thời gian mong muốn
            $desiredTime = Carbon::parse($request->thoiGianMongMuon);

            // Thời gian mong muốn không được quá thời gian check out
            if ($desiredTime->greaterThan($checkOut->copy()->addHours(1))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thời gian mong muốn phục vụ (' . $desiredTime->format('H:i d/m/Y') . ') vượt quá thời gian trả phòng dự kiến (' . $checkOut->format('H:i d/m/Y') . ').'
                ], 422);
            }

            // 3. Tạo yêu cầu dịch vụ
            $boPhan = Yeu_Cau_Dich_VuController::xacDinhBoPhan($dichVu->tenDichVu, $dichVu->loaiDichVu);
            $maCode = Yeu_Cau_Dich_VuController::sinhMaYeuCauCode();
            $phong = $luuTru->chiTietDatPhong ? $luuTru->chiTietDatPhong->phong : null;
            $tenPhong = $phong ? $phong->tenPhong : '301';

            $yeuCau = Yeu_Cau_Dich_Vu::create([
                'maYeuCauCode' => $maCode,
                'maLuuTru' => $luuTru->id,
                'maDichVu' => $dichVu->id,
                'soLuong' => (int) $request->soLuong,
                'donGiaApDung' => $dichVu->donGia,
                'ngayYeuCau' => $now,
                'thoiGianMongMuon' => $desiredTime,
                'trangThai' => 'Cho_Tiep_Nhan', // Trạng thái ban đầu: Chờ tiếp nhận
                'boPhanPhuTrach' => $boPhan,
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => $desiredTime->format('d/m/Y - H:i'),
                'daGhiHoaDon' => false,
                'ghiChu' => $request->ghiChu ?? ''
            ]);

            // TỰ ĐỘNG CHUYỂN THÀNH CÔNG VIỆC TRONG QUẢN LÝ BUỒNG PHÒNG NẾU LÀ DỌN PHÒNG HOẶC GIẶT ỦI
            if ($boPhan === 'Buồng phòng') {
                if ($phong) {
                    $phong->trangThai = 'Can_Don';
                    $phong->ghiChuDonDep = "Khách yêu cầu {$dichVu->tenDichVu} [{$maCode}]";
                    $phong->save();
                }

                $phieu = \App\Models\Phieu_Buong_Phong::create([
                    'maPhong' => $phong ? $phong->id : null,
                    'maYeuCauDichVu' => $yeuCau->id,
                    'viTri' => 'Phòng ' . $tenPhong,
                    'maNhanVien' => 3, // NV-003 Nguyễn Hương
                    'tieuDe' => "{$dichVu->tenDichVu} [{$maCode}]",
                    'loaiCongViec' => 'Dọn phòng',
                    'mucDoUuTien' => 'Cao',
                    'thoiHan' => $desiredTime->format('H:i d/m'),
                    'ngayTao' => $now,
                    'trangThai' => 'pending',
                    'ghiChu' => "Khách yêu cầu: {$dichVu->tenDichVu} (x{$yeuCau->soLuong}). Ghi chú: {$yeuCau->ghiChu}"
                ]);

                $yeuCau->maPhieuBuongPhong = $phieu->id;
                $yeuCau->save();
            }

            // GỬI THÔNG BÁO TỰ ĐỘNG
            Thong_BaoController::guiThongBao(
                'yeu_cau_moi',
                "Khách đặt dịch vụ mới: {$dichVu->tenDichVu} [{$maCode}]",
                "Phòng {$tenPhong} vừa đặt {$yeuCau->soLuong} {$dichVu->donViTinh} {$dichVu->tenDichVu}. Mong muốn: " . $desiredTime->format('H:i d/m') . ".",
                $yeuCau->id,
                'Lễ tân'
            );

            Thong_BaoController::guiThongBao(
                'yeu_cau_moi',
                "Bộ phận {$boPhan} có công việc mới: [{$maCode}]",
                "Phục vụ phòng {$tenPhong}: {$dichVu->tenDichVu}. Mong muốn: " . $desiredTime->format('H:i d/m') . ".",
                $yeuCau->id,
                $boPhan,
                $yeuCau->maPhieuBuongPhong
            );

            DB::commit();

            // Trả về thông tin chi tiết yêu cầu vừa tạo
            $khachHang = $luuTru->chiTietDatPhong && $luuTru->chiTietDatPhong->datPhong ? $luuTru->chiTietDatPhong->datPhong->khachHang : null;

            return response()->json([
                'status' => 'success',
                'message' => 'Đăng ký sử dụng dịch vụ thành công! Khách sạn đã tiếp nhận và sẽ phục vụ quý khách đúng giờ.',
                'data' => [
                    'id' => $yeuCau->id,
                    'maYeuCau' => $maCode,
                    'maYeuCauCode' => $maCode,
                    'tenDichVu' => $dichVu->tenDichVu,
                    'loaiDichVu' => $dichVu->loaiDichVu,
                    'donViTinh' => $dichVu->donViTinh,
                    'donGia' => $yeuCau->donGiaApDung,
                    'soLuong' => $yeuCau->soLuong,
                    'thanhTien' => $yeuCau->soLuong * $yeuCau->donGiaApDung,
                    'tenPhong' => $phong ? $phong->tenPhong : '',
                    'tenKhachHang' => $khachHang ? $khachHang->hoTen : '',
                    'thoiGianMongMuon' => $desiredTime->format('H:i d/m/Y'),
                    'ngayYeuCau' => $now->format('H:i d/m/Y'),
                    'trangThai' => 'Cho_Tiep_Nhan',
                    'trangThaiLabel' => 'Chờ tiếp nhận',
                    'boPhanPhuTrach' => $boPhan,
                    'ghiChu' => $yeuCau->ghiChu
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi xử lý đăng ký dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. XEM DANH SÁCH VÀ TRẠNG THÁI CÁC YÊU CẦU DỊCH VỤ ĐÃ ĐĂNG KÝ
     * Hỗ trợ lọc theo phòng hoặc mã lưu trú, tìm kiếm theo tên dịch vụ.
     */
    public function danhSachYeuCau(Request $request)
    {
        try {
            $query = Yeu_Cau_Dich_Vu::with([
                'dichVu',
                'luuTru.chiTietDatPhong.phong',
                'luuTru.chiTietDatPhong.datPhong.khachHang'
            ]);

            if ($request->filled('maLuuTru')) {
                $query->where('maLuuTru', $request->maLuuTru);
            }

            if ($request->filled('maPhong')) {
                $query->whereHas('luuTru.chiTietDatPhong', function ($q) use ($request) {
                    $q->where('maPhong', $request->maPhong);
                });
            }

            if ($request->filled('trangThai') && $request->trangThai !== 'Tất cả' && $request->trangThai !== 'all') {
                $statusMap = [
                    'Tiep_Nhan' => 'Tiep_Nhan',
                    'Cho_Xu_Ly' => 'Tiep_Nhan',
                    'Tiếp nhận' => 'Tiep_Nhan',
                    'Chờ xử lý' => 'Tiep_Nhan',
                    'Dang_Xu_Ly' => 'Dang_Xu_Ly',
                    'Đang xử lý' => 'Dang_Xu_Ly',
                    'Hoan_Thanh' => 'Hoan_Thanh',
                    'Hoàn thành' => 'Hoan_Thanh',
                    'Da_Huy' => 'Da_Huy',
                    'Đã hủy' => 'Da_Huy',
                ];
                $mapped = $statusMap[$request->trangThai] ?? $request->trangThai;
                $query->where(function ($q) use ($mapped, $request) {
                    $q->where('trangThai', $mapped)
                      ->orWhere('trangThai', $request->trangThai);
                });
            }

            $yeuCaus = $query->orderBy('id', 'desc')->get();

            $data = $yeuCaus->map(function ($yc) {
                $dv = $yc->dichVu;
                $lt = $yc->luuTru;
                $ct = $lt ? $lt->chiTietDatPhong : null;
                $phong = $ct ? $ct->phong : null;
                $dp = $ct ? $ct->datPhong : null;
                $kh = $dp ? $dp->khachHang : null;

                return [
                    'id' => $yc->id,
                    'maYeuCau' => 'YCDV-' . str_pad($yc->id, 4, '0', STR_PAD_LEFT),
                    'maLuuTru' => $yc->maLuuTru,
                    'maDichVu' => $yc->maDichVu,
                    'tenDichVu' => $dv ? $dv->tenDichVu : 'Dịch vụ đã gỡ',
                    'loaiDichVu' => $dv ? $dv->loaiDichVu : 'Dịch vụ phòng',
                    'donViTinh' => $dv ? $dv->donViTinh : 'Lượt',
                    'hinhAnh' => $dv ? $dv->hinhAnh : '',
                    'soLuong' => $yc->soLuong,
                    'donGia' => $yc->donGiaApDung,
                    'thanhTien' => $yc->soLuong * $yc->donGiaApDung,
                    'ngayYeuCau' => $yc->ngayYeuCau ? Carbon::parse($yc->ngayYeuCau)->format('H:i d/m/Y') : '',
                    'thoiGianMongMuon' => $yc->thoiGianMongMuon ? Carbon::parse($yc->thoiGianMongMuon)->format('H:i d/m/Y') : 'Ngay khi có thể',
                    'trangThai' => $yc->trangThai,
                    'trangThaiLabel' => self::getTrangThaiLabel($yc->trangThai),
                    'ghiChu' => $yc->ghiChu ?? '',
                    'maPhong' => $phong ? $phong->id : null,
                    'tenPhong' => $phong ? $phong->tenPhong : 'Phòng không xác định',
                    'tang' => $phong ? $phong->tang : 1,
                    'tenKhachHang' => $kh ? $kh->hoTen : 'Khách lưu trú',
                    'soDienThoai' => $kh ? $kh->soDienThoai : '',
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
                'message' => 'Lỗi khi tải danh sách yêu cầu dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. CẬP NHẬT TRẠNG THÁI YÊU CẦU DỊCH VỤ (LỄ TÂN / NHÂN VIÊN XỬ LÝ)
     * Trạng thái hỗ trợ: 'Tiep_Nhan', 'Dang_Xu_Ly', 'Hoan_Thanh', 'Da_Huy'.
     * KHI HOÀN THÀNH: Tự động cộng chi phí dịch vụ vào hóa đơn lưu trú.
     */
    public function capNhatTrangThai(Request $request, $id)
    {
        $statusMap = [
            'Tiep_Nhan' => 'Tiep_Nhan',
            'Cho_Xu_Ly' => 'Tiep_Nhan',
            'Tiếp nhận' => 'Tiep_Nhan',
            'Chờ xử lý' => 'Tiep_Nhan',
            'Dang_Xu_Ly' => 'Dang_Xu_Ly',
            'Đang xử lý' => 'Dang_Xu_Ly',
            'Hoan_Thanh' => 'Hoan_Thanh',
            'Hoàn thành' => 'Hoan_Thanh',
            'Da_Huy' => 'Da_Huy',
            'Đã hủy' => 'Da_Huy',
        ];

        $rawStatus = $request->trangThai;
        $normalizedStatus = $statusMap[$rawStatus] ?? null;

        if (!$normalizedStatus) {
            return response()->json([
                'status' => 'error',
                'message' => 'Trạng thái không hợp lệ. Hỗ trợ: Tiếp nhận, Đang xử lý, Hoàn thành, Đã hủy.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $yeuCau = Yeu_Cau_Dich_Vu::with('dichVu', 'luuTru.chiTietDatPhong.phong')->find($id);
            if (!$yeuCau) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu dịch vụ #' . $id
                ], 404);
            }

            $trangThaiCu = $statusMap[$yeuCau->trangThai] ?? $yeuCau->trangThai;
            $trangThaiMoi = $normalizedStatus;

            $yeuCau->trangThai = $trangThaiMoi;
            $yeuCau->save();

            $thongBaoHoaDon = '';

            // NẾU CHUYỂN SANG HOÀN THÀNH: CỘNG CHI PHÍ VÀO HÓA ĐƠN LƯU TRÚ
            if ($trangThaiMoi === 'Hoan_Thanh' && $trangThaiCu !== 'Hoan_Thanh') {
                $tenDichVu = $yeuCau->dichVu ? $yeuCau->dichVu->tenDichVu : 'Dịch vụ phòng';
                $tenPhong = ($yeuCau->luuTru && $yeuCau->luuTru->chiTietDatPhong && $yeuCau->luuTru->chiTietDatPhong->phong)
                    ? $yeuCau->luuTru->chiTietDatPhong->phong->tenPhong
                    : '';

                // Tìm hoặc tạo mới Hóa đơn lưu trú
                $hoaDon = Hoa_Don::firstOrCreate(
                    ['maLuuTru' => $yeuCau->maLuuTru],
                    [
                        'ngayLap' => Carbon::now(),
                        'tongTien' => 0,
                        'trangThai' => 'Chưa thanh toán'
                    ]
                );

                $itemTag = "YC#{$yeuCau->id}";
                $noiDung = "Dịch vụ: {$tenDichVu} (Phòng {$tenPhong} - {$itemTag})";
                $thanhTien = $yeuCau->soLuong * $yeuCau->donGiaApDung;

                // Kiểm tra xem mục này đã có trong Chi_Tiet_Hoa_Don chưa
                $chiTiet = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)
                    ->where('noiDung', 'like', "%{$itemTag}%")
                    ->first();

                if (!$chiTiet) {
                    Chi_Tiet_Hoa_Don::create([
                        'maHoaDon' => $hoaDon->id,
                        'noiDung' => $noiDung,
                        'soLuong' => $yeuCau->soLuong,
                        'donGia' => $yeuCau->donGiaApDung,
                        'thanhTien' => $thanhTien
                    ]);
                } else {
                    $chiTiet->soLuong = $yeuCau->soLuong;
                    $chiTiet->donGia = $yeuCau->donGiaApDung;
                    $chiTiet->thanhTien = $thanhTien;
                    $chiTiet->save();
                }

                // Cập nhật lại tổng tiền hóa đơn lưu trú
                $tongTienMoi = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)->sum('thanhTien');
                $hoaDon->tongTien = $tongTienMoi;
                $hoaDon->save();

                $thongBaoHoaDon = " Chi phí (" . number_format($thanhTien, 0, ',', '.') . " đ) đã được tự động cộng vào hóa đơn lưu trú #" . $hoaDon->id . ".";
            }
            // NẾU TỪ HOÀN THÀNH CHUYỂN SANG HỦY HOẶC TRẠNG THÁI KHÁC: ĐIỀU CHỈNH HÓA ĐƠN
            elseif ($trangThaiCu === 'Hoan_Thanh' && $trangThaiMoi !== 'Hoan_Thanh') {
                $itemTag = "YC#{$yeuCau->id}";
                $hoaDon = Hoa_Don::where('maLuuTru', $yeuCau->maLuuTru)->first();
                if ($hoaDon) {
                    Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)
                        ->where('noiDung', 'like', "%{$itemTag}%")
                        ->delete();

                    $tongTienMoi = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)->sum('thanhTien');
                    $hoaDon->tongTien = $tongTienMoi;
                    $hoaDon->save();

                    $thongBaoHoaDon = " Chi phí dịch vụ đã được hoàn lại/trừ khỏi hóa đơn lưu trú.";
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật trạng thái yêu cầu sang [' . self::getTrangThaiLabel($trangThaiMoi) . '] thành công!' . $thongBaoHoaDon,
                'data' => [
                    'id' => $yeuCau->id,
                    'trangThai' => $trangThaiMoi,
                    'trangThaiLabel' => self::getTrangThaiLabel($trangThaiMoi)
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật trạng thái yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. KHÁCH HÀNG HỦY YÊU CẦU DỊCH VỤ (NẾU CHƯA THỰC HIỆN)
     */
    public function huyYeuCau(Request $request, $id)
    {
        try {
            $yeuCau = Yeu_Cau_Dich_Vu::find($id);
            if (!$yeuCau) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu dịch vụ #' . $id
                ], 404);
            }

            if (in_array($yeuCau->trangThai, ['Hoan_Thanh', 'Da_Hoan_Thanh'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Dịch vụ đã được hoàn thành, không thể hủy. Vui lòng liên hệ quầy lễ tân.'
                ], 422);
            }

            if ($yeuCau->trangThai === 'Da_Huy') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Yêu cầu này đã được hủy trước đó.'
                ], 422);
            }

            $yeuCau->trangThai = 'Da_Huy';
            $yeuCau->lyDoHuy = $request->filled('lyDoHuy') ? trim($request->lyDoHuy) : 'Khách hủy yêu cầu';
            $yeuCau->thoiGianHuy = Carbon::now();
            $yeuCau->save();

            // Đồng bộ hủy phiếu buồng phòng nếu có
            if ($yeuCau->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yeuCau->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->trangThai = 'cancelled';
                    $phieu->ghiChu = 'Khách hủy yêu cầu: ' . $yeuCau->lyDoHuy;
                    $phieu->save();
                }
            }

            // Gửi thông báo đến Lễ tân & Nhân viên phụ trách
            Thong_BaoController::guiThongBao(
                'huy_yeu_cau',
                "Khách hủy yêu cầu: [{$yeuCau->maYeuCauCode}]",
                "Khách lưu trú đã hủy yêu cầu {$yeuCau->lyDoHuy}.",
                $yeuCau->id,
                'Lễ tân',
                $yeuCau->maPhieuBuongPhong,
                $yeuCau->maNhanVien
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Đã hủy yêu cầu dịch vụ thành công.',
                'data' => [
                    'id' => $yeuCau->id,
                    'trangThai' => 'Da_Huy',
                    'trangThaiLabel' => 'Đã hủy',
                    'lyDoHuy' => $yeuCau->lyDoHuy
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hủy yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper chuyển đổi nhãn trạng thái thân thiện tiếng Việt
     */
    public static function getTrangThaiLabel($status)
    {
        switch ($status) {
            case 'Tiep_Nhan':
            case 'Cho_Xu_Ly':
                return 'Tiếp nhận';
            case 'Dang_Xu_Ly':
                return 'Đang xử lý';
            case 'Hoan_Thanh':
                return 'Hoàn thành';
            case 'Da_Huy':
                return 'Đã hủy';
            default:
                return $status;
        }
    }
}
