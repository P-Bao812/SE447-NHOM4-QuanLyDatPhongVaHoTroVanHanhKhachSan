<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Yeu_Cau_Dich_Vu;
use App\Models\Dich_Vu;
use App\Models\Luu_Tru;
use App\Models\Phong;
use App\Models\Nhan_Vien;
use App\Models\Phieu_Buong_Phong;
use App\Models\Hoa_Don;
use App\Models\Chi_Tiet_Hoa_Don;
use App\Models\Thong_Bao;
use App\Http\Controllers\Thong_BaoController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Yeu_Cau_Dich_VuController extends Controller
{
    /**
     * Xác định bộ phận phụ trách tự động dựa trên dịch vụ
     */
    public static function xacDinhBoPhan($tenDichVu, $loaiDichVu = '')
    {
        $ten = mb_strtolower($tenDichVu);
        $loai = mb_strtolower($loaiDichVu);

        if (str_contains($ten, 'dọn phòng') || str_contains($loai, 'buồng phòng')) {
            return 'Buồng phòng';
        }
        if (str_contains($ten, 'giặt') || str_contains($loai, 'giặt ủi')) {
            return 'Buồng phòng';
        }
        if (str_contains($ten, 'ăn') || str_contains($ten, 'bữa sáng') || str_contains($loai, 'ẩm thực')) {
            return 'Nhà hàng & Bếp';
        }
        if (str_contains($ten, 'minibar') || str_contains($ten, 'đồ uống') || str_contains($loai, 'đồ uống')) {
            return 'Nhà hàng & Bếp';
        }
        if (str_contains($ten, 'spa') || str_contains($ten, 'massage') || str_contains($loai, 'spa')) {
            return 'Spa & Massage';
        }

        return 'Lễ tân';
    }

    /**
     * Sinh mã yêu cầu dạng YC-MMDD-XX (VD: YC-0811-04)
     */
    public static function sinhMaYeuCauCode($ngay = null)
    {
        $date = $ngay ? Carbon::parse($ngay) : Carbon::now();
        $prefix = 'YC-' . $date->format('md') . '-';

        $last = Yeu_Cau_Dich_Vu::where('maYeuCauCode', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $seq = 1;
        if ($last && $last->maYeuCauCode) {
            $parts = explode('-', $last->maYeuCauCode);
            if (count($parts) >= 3) {
                $seq = intval(end($parts)) + 1;
            }
        } else {
            $countToday = Yeu_Cau_Dich_Vu::whereDate('ngayYeuCau', $date->toDateString())->count();
            $seq = $countToday + 1;
        }

        return sprintf('%s%02d', $prefix, $seq);
    }

    /**
     * Helper chuẩn hóa nhãn trạng thái hiển thị
     */
    public static function getTrangThaiLabel($status)
    {
        switch ($status) {
            case 'Cho_Tiep_Nhan':
            case 'Tiep_Nhan':
                return 'Chờ tiếp nhận';
            case 'Da_Tiep_Nhan':
                return 'Đã tiếp nhận';
            case 'Da_Nhan_Viec':
                return 'Đã nhận việc';
            case 'Dang_Thuc_Hien':
            case 'Dang_Xu_Ly':
                return 'Đang xử lý';
            case 'Da_Hoan_Thanh':
            case 'Hoan_Thanh':
                return 'Hoàn tất';
            case 'Da_Tu_Choi':
                return 'Đã từ chối';
            case 'Da_Huy':
                return 'Đã hủy';
            default:
                return $status;
        }
    }

    /**
     * 1. DANH SÁCH YÊU CẦU DỊCH VỤ DÀNH CHO ADMIN / LỄ TÂN & NHÂN VIÊN
     */
    public function index(Request $request)
    {
        try {
            $query = Yeu_Cau_Dich_Vu::with([
                'dichVu',
                'luuTru.chiTietDatPhong.phong.loaiPhong',
                'luuTru.chiTietDatPhong.datPhong.khachHang',
                'nhanVien',
                'phieuBuongPhong'
            ])->orderBy('id', 'desc');

            // 1. Lọc theo Tab:
            $tab = $request->query('tab', 'all');
            if ($tab === 'cho_tiep_nhan') {
                $query->whereIn('trangThai', ['Cho_Tiep_Nhan', 'Tiep_Nhan']);
            } elseif ($tab === 'dang_xu_ly') {
                $query->whereIn('trangThai', ['Da_Tiep_Nhan', 'Da_Nhan_Viec', 'Dang_Thuc_Hien', 'Dang_Xu_Ly']);
            } elseif ($tab === 'hoan_tat') {
                $query->whereIn('trangThai', ['Da_Hoan_Thanh', 'Hoan_Thanh']);
            } elseif ($tab === 'tu_choi') {
                $query->whereIn('trangThai', ['Da_Tu_Choi', 'Da_Huy']);
            }

            // 2. Tìm kiếm theo mã yêu cầu, khách hàng, số phòng, tên dịch vụ
            if ($request->filled('q')) {
                $search = trim($request->q);
                $query->where(function ($q) use ($search) {
                    $q->where('maYeuCauCode', 'like', "%{$search}%")
                      ->orWhere('id', 'like', "%{$search}%")
                      ->orWhereHas('dichVu', function ($sub) use ($search) {
                          $sub->where('tenDichVu', 'like', "%{$search}%");
                      })
                      ->orWhereHas('luuTru.chiTietDatPhong.phong', function ($sub) use ($search) {
                          $sub->where('tenPhong', 'like', "%{$search}%");
                      })
                      ->orWhereHas('luuTru.chiTietDatPhong.datPhong.khachHang', function ($sub) use ($search) {
                          $sub->where('hoTen', 'like', "%{$search}%")
                              ->orWhere('soDienThoai', 'like', "%{$search}%");
                      });
                });
            }

            // 3. Lọc theo ngày yêu cầu
            if ($request->filled('ngay') && $request->ngay !== 'Tất cả') {
                $dateFormatted = Carbon::parse($request->ngay)->toDateString();
                $query->whereDate('ngayYeuCau', $dateFormatted);
            }

            // 4. Lọc theo mức độ ưu tiên
            if ($request->filled('mucDoUuTien') && $request->mucDoUuTien !== 'Tất cả' && $request->mucDoUuTien !== 'Mọi ưu tiên') {
                $query->where('mucDoUuTien', $request->mucDoUuTien);
            }

            // 5. Lọc theo người phụ trách / nhân viên
            if ($request->filled('maNhanVien') && $request->maNhanVien !== 'Tất cả' && $request->maNhanVien !== 'Mọi người phụ trách') {
                if ($request->maNhanVien === 'chua_phan_cong') {
                    $query->whereNull('maNhanVien');
                } else {
                    $query->where('maNhanVien', $request->maNhanVien);
                }
            }

            // 6. Lọc theo bộ phận phụ trách
            if ($request->filled('boPhan') && $request->boPhan !== 'Tất cả' && $request->boPhan !== 'Mọi bộ phận') {
                $query->where('boPhanPhuTrach', $request->boPhan);
            }

            // 7. Lọc chỉ xem việc của tôi
            if ($request->has('chiXemViecCuaToi') && $request->chiXemViecCuaToi == 1) {
                $myId = $request->maNhanVien ?? $request->header('X-User-Id');
                if ($myId) {
                    $query->where('maNhanVien', $myId);
                }
            }

            $list = $query->get();

            // Tính số lượng bản ghi cho từng tab
            $countAll = Yeu_Cau_Dich_Vu::count();
            $countChoTiepNhan = Yeu_Cau_Dich_Vu::whereIn('trangThai', ['Cho_Tiep_Nhan', 'Tiep_Nhan'])->count();
            $countDangXuLy = Yeu_Cau_Dich_Vu::whereIn('trangThai', ['Da_Tiep_Nhan', 'Da_Nhan_Viec', 'Dang_Thuc_Hien', 'Dang_Xu_Ly'])->count();
            $countHoanTat = Yeu_Cau_Dich_Vu::whereIn('trangThai', ['Da_Hoan_Thanh', 'Hoan_Thanh'])->count();
            $countTuChoi = Yeu_Cau_Dich_Vu::whereIn('trangThai', ['Da_Tu_Choi', 'Da_Huy'])->count();

            $data = $list->map(function ($yc) {
                $dv = $yc->dichVu;
                $lt = $yc->luuTru;
                $ct = $lt ? $lt->chiTietDatPhong : null;
                $phong = $ct ? $ct->phong : null;
                $dp = $ct ? $ct->datPhong : null;
                $kh = $dp ? $dp->khachHang : null;
                $nv = $yc->nhanVien;

                // Mã hiển thị (chuẩn YC-MMDD-XX)
                $code = $yc->maYeuCauCode ?: ('YC-' . Carbon::parse($yc->ngayYeuCau ?? $yc->created_at)->format('md') . '-' . sprintf('%02d', $yc->id));
                $tenPhong = $phong ? $phong->tenPhong : '301';
                $tenKhach = $kh ? $kh->hoTen : 'Khách lưu trú';

                // Kỳ lưu trú
                $kyLuuTru = 'Hôm nay';
                if ($lt && $lt->thoiGianNhan && $lt->thoiGianTra) {
                    $kyLuuTru = Carbon::parse($lt->thoiGianNhan)->format('d') . '-' . Carbon::parse($lt->thoiGianTra)->format('d/m/Y');
                }

                // Tiến độ hiển thị text
                $timeFrom = $yc->ngayYeuCau ? Carbon::parse($yc->ngayYeuCau)->format('H:i') : '09:00';
                $timeTo = $yc->thoiGianHen ?: ($yc->thoiGianMongMuon ? Carbon::parse($yc->thoiGianMongMuon)->format('d/m H:i') : 'Trong ngày');

                $tienDoText = "{$timeFrom} → {$timeTo}";
                if (in_array($yc->trangThai, ['Da_Hoan_Thanh', 'Hoan_Thanh'])) {
                    $tienDoText = 'Hoàn tất ' . ($yc->thoiGianHoanThanh ? Carbon::parse($yc->thoiGianHoanThanh)->format('H:i') : $timeFrom);
                } elseif ($yc->trangThai === 'Da_Huy') {
                    $tienDoText = 'Đã hủy ' . ($yc->thoiGianHuy ? Carbon::parse($yc->thoiGianHuy)->format('H:i') : '');
                } elseif (in_array($yc->trangThai, ['Da_Tu_Choi', 'Tu_Choi'])) {
                    $tienDoText = 'Đã từ chối';
                }

                return [
                    'id' => $yc->id,
                    'maYeuCau' => $code,
                    'maYeuCauCode' => $code,
                    'maLuuTru' => $yc->maLuuTru,
                    'maPhong' => $phong ? $phong->id : null,
                    'tenPhong' => $tenPhong,
                    'soPhongDisplay' => 'P.' . $tenPhong,
                    'tang' => $phong ? $phong->tang : 1,
                    'maKhachHang' => $kh ? $kh->id : null,
                    'tenKhachHang' => $tenKhach,
                    'soDienThoaiKhach' => $kh ? $kh->soDienThoai : '',
                    'kyLuuTru' => $kyLuuTru,
                    'maDichVu' => $yc->maDichVu,
                    'tenDichVu' => $dv ? $dv->tenDichVu : 'Dịch vụ phòng',
                    'loaiDichVu' => $dv ? $dv->loaiDichVu : 'Khác',
                    'donViTinh' => $dv ? $dv->donViTinh : 'Lượt',
                    'hinhAnh' => $dv ? $dv->hinhAnh : '',
                    'soLuong' => $yc->soLuong,
                    'donGia' => $yc->donGiaApDung,
                    'thanhTien' => $yc->soLuong * $yc->donGiaApDung,
                    'ngayYeuCau' => $yc->ngayYeuCau ? Carbon::parse($yc->ngayYeuCau)->format('H:i d/m/Y') : '',
                    'thoiGianMongMuon' => $yc->thoiGianMongMuon ? Carbon::parse($yc->thoiGianMongMuon)->format('H:i d/m/Y') : '',
                    'thoiGianHen' => $yc->thoiGianHen ?: $timeTo,
                    'trangThai' => $yc->trangThai,
                    'trangThaiLabel' => self::getTrangThaiLabel($yc->trangThai),
                    'boPhanPhuTrach' => $yc->boPhanPhuTrach ?: self::xacDinhBoPhan($dv ? $dv->tenDichVu : '', $dv ? $dv->loaiDichVu : ''),
                    'maNhanVien' => $yc->maNhanVien,
                    'tenNhanVien' => $nv ? $nv->hoTen : ($yc->maNhanVien ? "NV #{$yc->maNhanVien}" : 'Chưa phân công'),
                    'chucVuNhanVien' => $nv ? $nv->chucVu : '',
                    'mucDoUuTien' => $yc->mucDoUuTien ?: 'Bình thường',
                    'lyDoTuChoi' => $yc->lyDoTuChoi ?: '',
                    'lyDoHuy' => $yc->lyDoHuy ?: '',
                    'thoiGianHuy' => $yc->thoiGianHuy ? Carbon::parse($yc->thoiGianHuy)->format('H:i d/m/Y') : '',
                    'baoSuCo' => $yc->baoSuCo ?: '',
                    'ghiChu' => $yc->ghiChu ?: '',
                    'maPhieuBuongPhong' => $yc->maPhieuBuongPhong,
                    'daGhiHoaDon' => (bool) $yc->daGhiHoaDon,
                    'tienDoText' => $tienDoText,
                    'thoiGianTiepNhan' => $yc->thoiGianTiepNhan ? Carbon::parse($yc->thoiGianTiepNhan)->format('H:i d/m/Y') : '',
                    'thoiGianNhanViec' => $yc->thoiGianNhanViec ? Carbon::parse($yc->thoiGianNhanViec)->format('H:i d/m/Y') : '',
                    'thoiGianBatDau' => $yc->thoiGianBatDau ? Carbon::parse($yc->thoiGianBatDau)->format('H:i d/m/Y') : '',
                    'thoiGianHoanThanh' => $yc->thoiGianHoanThanh ? Carbon::parse($yc->thoiGianHoanThanh)->format('H:i d/m/Y') : '',
                ];
            });

            return response()->json([
                'status' => 'success',
                'tab' => $tab,
                'counts' => [
                    'all' => $countAll,
                    'choTiepNhan' => $countChoTiepNhan,
                    'dangXuLy' => $countDangXuLy,
                    'hoanTat' => $countHoanTat,
                    'tuChoi' => $countTuChoi
                ],
                'total' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải danh sách yêu cầu dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. CHI TIẾT MỘT YÊU CẦU DỊCH VỤ
     */
    public function show($id)
    {
        try {
            $yc = Yeu_Cau_Dich_Vu::with([
                'dichVu',
                'luuTru.chiTietDatPhong.phong',
                'luuTru.chiTietDatPhong.datPhong.khachHang',
                'nhanVien',
                'phieuBuongPhong'
            ])->find($id);

            if (!$yc) {
                // Thử tìm theo mã code YC-0811-04
                $yc = Yeu_Cau_Dich_Vu::with([
                    'dichVu',
                    'luuTru.chiTietDatPhong.phong',
                    'luuTru.chiTietDatPhong.datPhong.khachHang',
                    'nhanVien',
                    'phieuBuongPhong'
                ])->where('maYeuCauCode', $id)->first();
            }

            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu dịch vụ #' . $id
                ], 404);
            }

            $dv = $yc->dichVu;
            $lt = $yc->luuTru;
            $ct = $lt ? $lt->chiTietDatPhong : null;
            $phong = $ct ? $ct->phong : null;
            $dp = $ct ? $ct->datPhong : null;
            $kh = $dp ? $dp->khachHang : null;
            $nv = $yc->nhanVien;

            $code = $yc->maYeuCauCode ?: ('YC-' . Carbon::parse($yc->ngayYeuCau ?? $yc->created_at)->format('md') . '-' . sprintf('%02d', $yc->id));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $yc->id,
                    'maYeuCau' => $code,
                    'maYeuCauCode' => $code,
                    'maLuuTru' => $yc->maLuuTru,
                    'maPhong' => $phong ? $phong->id : null,
                    'tenPhong' => $phong ? $phong->tenPhong : '301',
                    'maKhachHang' => $kh ? $kh->id : null,
                    'tenKhachHang' => $kh ? $kh->hoTen : 'Khách lưu trú',
                    'soDienThoai' => $kh ? $kh->soDienThoai : '',
                    'kyLuuTru' => ($lt && $lt->thoiGianNhan && $lt->thoiGianTra)
                        ? Carbon::parse($lt->thoiGianNhan)->format('d') . '-' . Carbon::parse($lt->thoiGianTra)->format('d/m/Y')
                        : 'Hôm nay',
                    'maDichVu' => $yc->maDichVu,
                    'tenDichVu' => $dv ? $dv->tenDichVu : 'Dịch vụ phòng',
                    'loaiDichVu' => $dv ? $dv->loaiDichVu : 'Khác',
                    'donViTinh' => $dv ? $dv->donViTinh : 'Lượt',
                    'donGia' => $yc->donGiaApDung,
                    'soLuong' => $yc->soLuong,
                    'thanhTien' => $yc->soLuong * $yc->donGiaApDung,
                    'ngayYeuCau' => $yc->ngayYeuCau ? Carbon::parse($yc->ngayYeuCau)->format('H:i d/m/Y') : '',
                    'thoiGianMongMuon' => $yc->thoiGianMongMuon ? Carbon::parse($yc->thoiGianMongMuon)->format('H:i d/m/Y') : '',
                    'thoiGianHen' => $yc->thoiGianHen ?: '',
                    'trangThai' => $yc->trangThai,
                    'trangThaiLabel' => self::getTrangThaiLabel($yc->trangThai),
                    'boPhanPhuTrach' => $yc->boPhanPhuTrach ?: self::xacDinhBoPhan($dv ? $dv->tenDichVu : '', $dv ? $dv->loaiDichVu : ''),
                    'maNhanVien' => $yc->maNhanVien,
                    'tenNhanVien' => $nv ? $nv->hoTen : 'Chưa phân công',
                    'chucVuNhanVien' => $nv ? $nv->chucVu : '',
                    'mucDoUuTien' => $yc->mucDoUuTien ?: 'Bình thường',
                    'lyDoTuChoi' => $yc->lyDoTuChoi ?: '',
                    'baoSuCo' => $yc->baoSuCo ?: '',
                    'ghiChu' => $yc->ghiChu ?: '',
                    'maPhieuBuongPhong' => $yc->maPhieuBuongPhong,
                    'daGhiHoaDon' => (bool) $yc->daGhiHoaDon,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải chi tiết yêu cầu dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. TẠO YÊU CẦU DỊCH VỤ MỚI (DÀNH CHO ADMIN / LỄ TÂN TẠO NHANH CHO KHÁCH)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maDichVu' => 'required|integer|exists:Dich_Vu,id',
            'soLuong' => 'required|integer|min:1|max:100',
            'maLuuTru' => 'nullable|integer',
            'maPhong' => 'nullable|integer',
            'mucDoUuTien' => 'nullable|string',
            'thoiGianHen' => 'nullable|string',
            'ghiChu' => 'nullable|string|max:500',
            'maNhanVien' => 'nullable|integer'
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
            $dichVu = Dich_Vu::find($request->maDichVu);

            // Tìm lưu trú
            $luuTru = null;
            if ($request->filled('maLuuTru')) {
                $luuTru = Luu_Tru::with('chiTietDatPhong.phong', 'chiTietDatPhong.datPhong.khachHang')->find($request->maLuuTru);
            } elseif ($request->filled('maPhong')) {
                $luuTru = Luu_Tru::whereHas('chiTietDatPhong', function ($q) use ($request) {
                    $q->where('maPhong', $request->maPhong);
                })
                ->where('trangThai', 'Dang_Luu_Tru')
                ->latest('id')
                ->first();
            }

            if (!$luuTru) {
                // Thử lấy lưu trú active bất kỳ nếu đang ở môi trường test
                $luuTru = Luu_Tru::with('chiTietDatPhong.phong', 'chiTietDatPhong.datPhong.khachHang')->first();
            }

            if (!$luuTru) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy hồ sơ lưu trú tương ứng để gán yêu cầu dịch vụ.'
                ], 422);
            }

            $boPhan = self::xacDinhBoPhan($dichVu->tenDichVu, $dichVu->loaiDichVu);
            $maCode = self::sinhMaYeuCauCode();
            $phong = $luuTru->chiTietDatPhong ? $luuTru->chiTietDatPhong->phong : null;
            $tenPhong = $phong ? $phong->tenPhong : '301';

            $desiredTime = $request->filled('thoiGianMongMuon')
                ? Carbon::parse($request->thoiGianMongMuon)
                : $now->copy()->addMinutes(30);

            // Tạo yêu cầu
            $yeuCau = Yeu_Cau_Dich_Vu::create([
                'maYeuCauCode' => $maCode,
                'maLuuTru' => $luuTru->id,
                'maDichVu' => $dichVu->id,
                'soLuong' => (int) $request->soLuong,
                'donGiaApDung' => $dichVu->donGia,
                'ngayYeuCau' => $now,
                'thoiGianMongMuon' => $desiredTime,
                'trangThai' => $request->filled('maNhanVien') ? 'Da_Nhan_Viec' : 'Cho_Tiep_Nhan',
                'boPhanPhuTrach' => $boPhan,
                'maNhanVien' => $request->maNhanVien ?: null,
                'mucDoUuTien' => $request->input('mucDoUuTien', 'Bình thường'),
                'thoiGianHen' => $request->input('thoiGianHen', $now->copy()->addHours(2)->format('d/m/Y - H:i')),
                'ghiChu' => $request->input('ghiChu', ''),
                'daGhiHoaDon' => false
            ]);

            // LIÊN KẾT BUỒNG PHÒNG NẾU LÀ DỊCH VỤ DỌN PHÒNG HOẶC GIẶT ỦI
            if ($boPhan === 'Buồng phòng') {
                $phieu = Phieu_Buong_Phong::create([
                    'maPhong' => $phong ? $phong->id : null,
                    'maYeuCauDichVu' => $yeuCau->id,
                    'viTri' => 'Phòng ' . $tenPhong,
                    'maNhanVien' => $request->maNhanVien ?: 3, // NV-003 Nguyễn Hương
                    'tieuDe' => "{$dichVu->tenDichVu} [{$maCode}]",
                    'loaiCongViec' => 'Dọn phòng',
                    'mucDoUuTien' => $request->input('mucDoUuTien', 'Bình thường') === 'Khẩn cấp' ? 'Khẩn cấp' : 'Cao',
                    'thoiHan' => $request->input('thoiGianHen', 'Trước ' . $now->copy()->addHours(2)->format('H:i')),
                    'ngayTao' => $now,
                    'trangThai' => 'pending',
                    'ghiChu' => "Khách yêu cầu: {$dichVu->tenDichVu} (x{$yeuCau->soLuong}). Ghi chú: {$yeuCau->ghiChu}"
                ]);

                $yeuCau->maPhieuBuongPhong = $phieu->id;
                $yeuCau->save();
            }

            // GỬI THÔNG BÁO TỰ ĐỘNG
            // 1. Đến Admin & Lễ tân
            Thong_BaoController::guiThongBao(
                'yeu_cau_moi',
                "Yêu cầu mới: {$dichVu->tenDichVu} [{$maCode}]",
                "Phòng {$tenPhong} vừa đăng ký {$yeuCau->soLuong} {$dichVu->donViTinh} {$dichVu->tenDichVu}.",
                $yeuCau->id,
                'Lễ tân'
            );

            // 2. Đến Bộ phận phụ trách
            Thong_BaoController::guiThongBao(
                'yeu_cau_moi',
                "Công việc mới bộ phận {$boPhan}: [{$maCode}]",
                "Phòng {$tenPhong} cần phục vụ: {$dichVu->tenDichVu}. Mong muốn: " . $desiredTime->format('H:i d/m') . ".",
                $yeuCau->id,
                $boPhan
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Tạo yêu cầu dịch vụ [{$maCode}] thành công!",
                'data' => $yeuCau
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tạo yêu cầu dịch vụ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. TIẾP NHẬN YÊU CẦU (ADMIN / LỄ TÂN)
     * Chuyển trạng thái sang 'Da_Tiep_Nhan'
     */
    public function tiepNhan(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu dịch vụ #' . $id
                ], 404);
            }

            $yc->trangThai = 'Da_Tiep_Nhan';
            $yc->thoiGianTiepNhan = Carbon::now();

            if ($request->filled('mucDoUuTien')) {
                $yc->mucDoUuTien = $request->mucDoUuTien;
            }
            if ($request->filled('thoiGianHen')) {
                $yc->thoiGianHen = $request->thoiGianHen;
            }
            if ($request->filled('maNhanVien')) {
                $yc->maNhanVien = $request->maNhanVien;
                $yc->trangThai = 'Da_Nhan_Viec';
                $yc->thoiGianNhanViec = Carbon::now();
            }

            $yc->save();

            // Đồng bộ sang Buồng phòng nếu có
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    if ($yc->maNhanVien) {
                        $phieu->maNhanVien = $yc->maNhanVien;
                    }
                    $phieu->save();
                }
            }

            // Gửi thông báo đến bộ phận
            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                : '';
            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';

            Thong_BaoController::guiThongBao(
                'tiep_nhan',
                "Đã tiếp nhận yêu cầu [{$yc->maYeuCauCode}]",
                "Lễ tân đã tiếp nhận yêu cầu {$tenDichVu} (Phòng {$tenPhong}). Sẵn sàng thực hiện.",
                $yc->id,
                $yc->boPhanPhuTrach
            );

            // Gửi thông báo cho khách hàng
            Thong_BaoController::guiThongBao(
                'tiep_nhan',
                "Yêu cầu {$tenDichVu} đã được tiếp nhận",
                "Khách sạn đã tiếp nhận yêu cầu của quý khách và đang chuẩn bị phục vụ.",
                $yc->id,
                'Khách hàng'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã tiếp nhận yêu cầu [{$yc->maYeuCauCode}] thành công!",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tiếp nhận yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. TỪ CHỐI YÊU CẦU (KÈM LÝ DO)
     */
    public function tuChoi(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lyDoTuChoi' => 'required|string|max:500'
        ], [
            'lyDoTuChoi.required' => 'Vui lòng cung cấp lý do từ chối yêu cầu.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            $yc->trangThai = 'Da_Tu_Choi';
            $yc->lyDoTuChoi = trim($request->lyDoTuChoi);
            $yc->save();

            // Nếu có phiếu buồng phòng, cập nhật ghi chú hủy
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->ghiChu = 'Yêu cầu bị từ chối: ' . $yc->lyDoTuChoi;
                    $phieu->save();
                }
            }

            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';
            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                : '';

            // Thông báo cho khách hàng
            Thong_BaoController::guiThongBao(
                'tu_choi',
                "Yêu cầu {$tenDichVu} không thể thực hiện",
                "Rất tiếc yêu cầu cho phòng {$tenPhong} tạm thời không thể phục vụ. Lý do: {$yc->lyDoTuChoi}.",
                $yc->id,
                'Khách hàng'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã từ chối yêu cầu [{$yc->maYeuCauCode}] thành công.",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi từ chối yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HỦY YÊU CẦU DỊCH VỤ (ADMIN / LỄ TÂN) KÈM LÝ DO HỦY
     */
    public function huyYeuCau(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'lyDoHuy' => 'required|string|max:500'
        ], [
            'lyDoHuy.required' => 'Vui lòng cung cấp lý do hủy yêu cầu.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            if (in_array($yc->trangThai, ['Da_Hoan_Thanh', 'Hoan_Thanh'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không thể hủy yêu cầu đã hoàn tất và đã tính vào hóa đơn.'
                ], 422);
            }

            $yc->trangThai = 'Da_Huy';
            $yc->lyDoHuy = trim($request->lyDoHuy);
            $yc->thoiGianHuy = Carbon::now();
            $yc->save();

            // Nếu có phiếu buồng phòng, cập nhật trạng thái hủy
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->trangThai = 'cancelled';
                    $phieu->ghiChu = 'Yêu cầu bị hủy: ' . $yc->lyDoHuy;
                    $phieu->save();
                }
            }

            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';
            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                : '';

            // Thông báo cho khách hàng
            Thong_BaoController::guiThongBao(
                'huy_yeu_cau',
                "Yêu cầu {$tenDichVu} đã được hủy",
                "Yêu cầu [{$yc->maYeuCauCode}] cho phòng {$tenPhong} đã được hủy. Lý do: {$yc->lyDoHuy}.",
                $yc->id,
                'Khách hàng'
            );

            // Nếu đã phân công nhân viên, gửi thông báo trực tiếp cho nhân viên đó
            if ($yc->maNhanVien) {
                Thong_BaoController::guiThongBao(
                    'huy_yeu_cau',
                    "Yêu cầu [{$yc->maYeuCauCode}] đã bị hủy",
                    "Công việc {$tenDichVu} (Phòng {$tenPhong}) đã bị hủy. Lý do: {$yc->lyDoHuy}.",
                    $yc->id,
                    $yc->boPhanPhuTrach,
                    $yc->maPhieuBuongPhong,
                    $yc->maNhanVien
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã hủy yêu cầu [{$yc->maYeuCauCode}] thành công.",
                'data' => [
                    'id' => $yc->id,
                    'maYeuCauCode' => $yc->maYeuCauCode,
                    'trangThai' => $yc->trangThai,
                    'trangThaiLabel' => self::getTrangThaiLabel($yc->trangThai),
                    'lyDoHuy' => $yc->lyDoHuy
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hủy yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper tìm yêu cầu theo ID số hoặc mã code (YC-MMDD-XX)
     */
    private function timYeuCau($id)
    {
        $yc = Yeu_Cau_Dich_Vu::with([
            'dichVu',
            'luuTru.chiTietDatPhong.phong.loaiPhong',
            'luuTru.chiTietDatPhong.datPhong.khachHang',
            'nhanVien',
            'phieuBuongPhong'
        ])->find($id);

        if (!$yc) {
            $yc = Yeu_Cau_Dich_Vu::with([
                'dichVu',
                'luuTru.chiTietDatPhong.phong.loaiPhong',
                'luuTru.chiTietDatPhong.datPhong.khachHang',
                'nhanVien',
                'phieuBuongPhong'
            ])->where('maYeuCauCode', $id)->first();
        }

        return $yc;
    }

    /**
     * 6. PHÂN CÔNG NHÂN VIÊN PHỤ TRÁCH (ADMIN / LỄ TÂN)
     */
    public function phanCong(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'maNhanVien' => 'nullable',
            'mucDoUuTien' => 'nullable|string',
            'thoiGianHen' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            $nv = null;
            if ($request->filled('maNhanVien')) {
                $nv = Nhan_Vien::find($request->maNhanVien);
                if (!$nv) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Nhân viên được chọn không tồn tại trong hệ thống.'
                    ], 422);
                }

                // Kiểm tra sự phù hợp bộ phận theo dịch vụ
                $boPhanYeuCau = $yc->boPhanPhuTrach ?: self::xacDinhBoPhan($yc->dichVu ? $yc->dichVu->tenDichVu : '');
                $boPhanNV = $nv->boPhan;
                $hopLe = false;

                if ($boPhanYeuCau === 'Buồng phòng') {
                    $hopLe = in_array($boPhanNV, ['Buồng phòng', 'Giặt ủi']) || str_contains(mb_strtolower($boPhanNV), 'buồng');
                } elseif ($boPhanYeuCau === 'Nhà hàng & Bếp') {
                    $hopLe = in_array($boPhanNV, ['Nhà hàng & Bếp', 'Phục vụ', 'Minibar', 'Bếp']) || str_contains(mb_strtolower($boPhanNV), 'nhà hàng') || str_contains(mb_strtolower($boPhanNV), 'bếp');
                } elseif ($boPhanYeuCau === 'Spa & Massage') {
                    $hopLe = in_array($boPhanNV, ['Spa & Massage', 'Spa']) || str_contains(mb_strtolower($boPhanNV), 'spa');
                } else {
                    $hopLe = true;
                }

                if (!$hopLe) {
                    $tenDV = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ';
                    return response()->json([
                        'status' => 'error',
                        'message' => "Nhân viên [{$nv->hoTen}] thuộc bộ phận [{$nv->boPhan}], không thể phân công cho dịch vụ [{$tenDV}] thuộc bộ phận [{$boPhanYeuCau}]."
                    ], 422);
                }

                $yc->maNhanVien = $nv->id;
                $yc->trangThai = in_array($yc->trangThai, ['Cho_Tiep_Nhan', 'Tiep_Nhan', 'Da_Tiep_Nhan'])
                    ? 'Da_Nhan_Viec'
                    : $yc->trangThai;
                $yc->thoiGianNhanViec = Carbon::now();
            } else {
                // Hủy phân công
                $yc->maNhanVien = null;
            }

            if ($request->filled('mucDoUuTien')) {
                $yc->mucDoUuTien = $request->mucDoUuTien;
            }
            if ($request->filled('thoiGianHen')) {
                $yc->thoiGianHen = $request->thoiGianHen;
            }

            // Đồng bộ hoặc tạo mới Phiếu buồng phòng nếu bộ phận là Buồng phòng
            $phong = $yc->luuTru && $yc->luuTru->chiTietDatPhong ? $yc->luuTru->chiTietDatPhong->phong : null;
            $tenPhong = $phong ? $phong->tenPhong : '301';
            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';

            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->maNhanVien = $yc->maNhanVien;
                    if ($request->filled('mucDoUuTien')) {
                        $phieu->mucDoUuTien = $request->mucDoUuTien === 'Khẩn cấp' ? 'Khẩn cấp' : 'Cao';
                    }
                    if ($request->filled('thoiGianHen')) {
                        $phieu->thoiHan = $request->thoiGianHen;
                    }
                    $phieu->save();
                }
            } elseif ($yc->boPhanPhuTrach === 'Buồng phòng') {
                $phieu = Phieu_Buong_Phong::create([
                    'maPhong' => $phong ? $phong->id : null,
                    'maYeuCauDichVu' => $yc->id,
                    'viTri' => 'Phòng ' . $tenPhong,
                    'maNhanVien' => $yc->maNhanVien,
                    'tieuDe' => "{$tenDichVu} [{$yc->maYeuCauCode}]",
                    'loaiCongViec' => 'Dọn phòng',
                    'mucDoUuTien' => $yc->mucDoUuTien === 'Khẩn cấp' ? 'Khẩn cấp' : 'Cao',
                    'thoiHan' => $yc->thoiGianHen ?: 'Trong ngày',
                    'ngayTao' => Carbon::now(),
                    'trangThai' => 'pending',
                    'ghiChu' => "Khách yêu cầu: {$tenDichVu} (x{$yc->soLuong}). Ghi chú: {$yc->ghiChu}"
                ]);
                $yc->maPhieuBuongPhong = $phieu->id;
            }

            $yc->save();

            // Gửi thông báo đến nhân viên hoặc bộ phận
            if ($nv) {
                Thong_BaoController::guiThongBao(
                    'nhan_viec',
                    "Phân công mới: [{$yc->maYeuCauCode}]",
                    "Phân công cho {$nv->hoTen} ({$nv->boPhan}) phụ trách {$tenDichVu} (Phòng {$tenPhong}).",
                    $yc->id,
                    $yc->boPhanPhuTrach,
                    $yc->maPhieuBuongPhong,
                    $nv->id
                );
            }

            DB::commit();

            $yc->load(['nhanVien', 'dichVu', 'luuTru.chiTietDatPhong.phong']);

            $msg = $nv
                ? "Đã phân công nhân viên [{$nv->hoTen}] phụ trách yêu cầu [{$yc->maYeuCauCode}]!"
                : "Đã cập nhật yêu cầu [{$yc->maYeuCauCode}] (Chưa phân công nhân viên).";

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'data' => [
                    'id' => $yc->id,
                    'maYeuCauCode' => $yc->maYeuCauCode,
                    'maNhanVien' => $yc->maNhanVien,
                    'tenNhanVien' => $yc->nhanVien ? $yc->nhanVien->hoTen : 'Chưa phân công',
                    'chucVuNhanVien' => $yc->nhanVien ? $yc->nhanVien->chucVu : '',
                    'trangThai' => $yc->trangThai,
                    'trangThaiLabel' => self::getTrangThaiLabel($yc->trangThai),
                    'mucDoUuTien' => $yc->mucDoUuTien,
                    'thoiGianHen' => $yc->thoiGianHen,
                    'maPhieuBuongPhong' => $yc->maPhieuBuongPhong
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi phân công nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. NHÂN VIÊN NHẤN "NHẬN VIỆC" (TỰ NHẬN TRỰC TIẾP)
     * Tránh nhiều nhân viên cùng xử lý một yêu cầu.
     */
    public function nhanViec(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            // Lấy ID nhân viên hiện tại thực hiện (hoặc gửi qua request)
            $maNhanVien = $request->input('maNhanVien');
            if (!$maNhanVien) {
                // Thử tìm theo tên hoặc gán nhân viên tương ứng bộ phận
                $nv = Nhan_Vien::where('boPhan', $yc->boPhanPhuTrach)->first();
                $maNhanVien = $nv ? $nv->id : 2;
            }

            // Kiểm tra xem yêu cầu đã có ai nhận chưa
            if ($yc->maNhanVien && $yc->maNhanVien != $maNhanVien && in_array($yc->trangThai, ['Da_Nhan_Viec', 'Dang_Thuc_Hien', 'Dang_Xu_Ly'])) {
                $daNhan = Nhan_Vien::find($yc->maNhanVien);
                $tenNhan = $daNhan ? $daNhan->hoTen : "NV #{$yc->maNhanVien}";
                return response()->json([
                    'status' => 'error',
                    'message' => "Yêu cầu này đã được nhân viên [{$tenNhan}] tiếp nhận xử lý trước đó."
                ], 422);
            }

            $nhanVien = Nhan_Vien::find($maNhanVien);
            $tenNhanVien = $nhanVien ? $nhanVien->hoTen : 'Nhân viên';

            $yc->maNhanVien = $maNhanVien;
            $yc->trangThai = 'Da_Nhan_Viec';
            $yc->thoiGianNhanViec = Carbon::now();
            $yc->save();

            // Đồng bộ Buồng phòng nếu có
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->maNhanVien = $maNhanVien;
                    $phieu->save();
                }
            }

            // Thông báo đến Admin & Lễ tân
            Thong_BaoController::guiThongBao(
                'nhan_viec',
                "Nhân viên đã nhận việc: [{$yc->maYeuCauCode}]",
                "[{$tenNhanVien}] thuộc bộ phận {$yc->boPhanPhuTrach} đã nhận xử lý yêu cầu.",
                $yc->id,
                'Lễ tân'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Bạn đã nhận xử lý yêu cầu [{$yc->maYeuCauCode}] thành công!",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi nhận việc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. BẮT ĐẦU THỰC HIỆN / CẬP NHẬT TIẾN ĐỘ SANG 'Dang_Thuc_Hien'
     */
    public function batDauThucHien(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            // Kiểm tra phân quyền cập nhật tiến độ
            $userRole = $request->header('X-User-Role') ?? $request->input('userRole') ?? '';
            $userId = $request->header('X-User-Id') ?? $request->input('userId') ?? null;
            if ($userRole && !in_array($userRole, ['Admin', 'admin', 'Lễ tân', 'Quản lý']) && $userId) {
                if ($yc->maNhanVien && (int)$yc->maNhanVien !== (int)$userId) {
                    $nvPhuTrach = $yc->nhanVien ? $yc->nhanVien->hoTen : "NV #{$yc->maNhanVien}";
                    return response()->json([
                        'status' => 'error',
                        'message' => "Chỉ nhân viên được phân công phụ trách [{$nvPhuTrach}] mới được cập nhật tiến độ cho yêu cầu này."
                    ], 403);
                }
            }

            $yc->trangThai = 'Dang_Thuc_Hien';
            $yc->thoiGianBatDau = Carbon::now();
            $yc->save();

            // Đồng bộ Buồng phòng nếu có
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->trangThai = 'doing';
                    $phieu->chiTietTienDo = 'Đang thực hiện lúc ' . Carbon::now()->format('H:i');
                    $phieu->save();
                }
            }

            // Thông báo đến Admin/Lễ tân
            Thong_BaoController::guiThongBao(
                'thuc_hien',
                "Đang thực hiện: [{$yc->maYeuCauCode}]",
                "Nhân viên đang tiến hành phục vụ tại phòng " . ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong ? $yc->luuTru->chiTietDatPhong->phong->tenPhong : '') . ".",
                $yc->id,
                'Lễ tân'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã chuyển yêu cầu [{$yc->maYeuCauCode}] sang trạng thái Đang thực hiện.",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật tiến độ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 9. HOÀN THÀNH YÊU CẦU DỊCH VỤ
     * - Chuyển trạng thái sang 'Da_Hoan_Thanh'
     * - Tự động cộng chi phí vào hóa đơn lưu trú (tránh trùng lặp qua daGhiHoaDon)
     * - Đồng bộ phiếu buồng phòng (nếu có)
     * - Gửi thông báo đến Khách hàng và Lễ tân
     */
    public function hoanThanh(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            // Kiểm tra phân quyền cập nhật tiến độ
            $userRole = $request->header('X-User-Role') ?? $request->input('userRole') ?? '';
            $userId = $request->header('X-User-Id') ?? $request->input('userId') ?? null;
            if ($userRole && !in_array($userRole, ['Admin', 'admin', 'Lễ tân', 'Quản lý']) && $userId) {
                if ($yc->maNhanVien && (int)$yc->maNhanVien !== (int)$userId) {
                    $nvPhuTrach = $yc->nhanVien ? $yc->nhanVien->hoTen : "NV #{$yc->maNhanVien}";
                    return response()->json([
                        'status' => 'error',
                        'message' => "Chỉ nhân viên được phân công phụ trách [{$nvPhuTrach}] mới được xác nhận hoàn tất yêu cầu này."
                    ], 403);
                }
            }

            $yc->trangThai = 'Da_Hoan_Thanh';
            $yc->thoiGianHoanThanh = Carbon::now();

            $thongBaoHoaDon = '';

            // CỘNG CHI PHÍ VÀO HÓA ĐƠN LƯU TRÚ (DUY NHẤT 1 LẦN)
            if (!$yc->daGhiHoaDon && $yc->maLuuTru) {
                $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';
                $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                    ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                    : 'Phòng khách';

                // Tìm hoặc tạo mới Hóa đơn lưu trú
                $hoaDon = Hoa_Don::firstOrCreate(
                    ['maLuuTru' => $yc->maLuuTru],
                    [
                        'ngayLap' => Carbon::now(),
                        'tongTien' => 0,
                        'trangThai' => 'Chưa thanh toán'
                    ]
                );

                $itemTag = "YC#{$yc->id}";
                $noiDung = "Dịch vụ: {$tenDichVu} (Phòng {$tenPhong} - {$itemTag})";
                $thanhTien = $yc->soLuong * $yc->donGiaApDung;

                $chiTiet = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)
                    ->where('noiDung', 'like', "%{$itemTag}%")
                    ->first();

                if (!$chiTiet) {
                    Chi_Tiet_Hoa_Don::create([
                        'maHoaDon' => $hoaDon->id,
                        'noiDung' => $noiDung,
                        'soLuong' => $yc->soLuong,
                        'donGia' => $yc->donGiaApDung,
                        'thanhTien' => $thanhTien
                    ]);
                }

                // Cập nhật lại tổng tiền hóa đơn lưu trú
                $tongTienMoi = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)->sum('thanhTien');
                $hoaDon->tongTien = $tongTienMoi;
                $hoaDon->save();

                $yc->daGhiHoaDon = true;
                $thongBaoHoaDon = " Chi phí (" . number_format($thanhTien, 0, ',', '.') . " đ) đã được ghi nhận vào hóa đơn lưu trú.";
            }

            $yc->save();

            // Đồng bộ Buồng phòng nếu có
            if ($yc->maPhieuBuongPhong) {
                $phieu = Phieu_Buong_Phong::find($yc->maPhieuBuongPhong);
                if ($phieu) {
                    $phieu->trangThai = 'done';
                    $phieu->thoiGianHoanThanh = Carbon::now();
                    $phieu->thoiHan = 'Hoàn thành lúc ' . Carbon::now()->format('H:i');
                    $phieu->save();
                }
            }

            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dịch vụ phòng';
            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                : '';

            // Thông báo đến Khách hàng
            Thong_BaoController::guiThongBao(
                'hoan_thanh',
                "Dịch vụ {$tenDichVu} đã hoàn tất!",
                "Yêu cầu phục vụ tại phòng {$tenPhong} đã được nhân viên hoàn thành tốt đẹp. Chúc quý khách kỳ nghỉ tuyệt vời!",
                $yc->id,
                'Khách hàng'
            );

            // Thông báo đến Admin/Lễ tân
            Thong_BaoController::guiThongBao(
                'hoan_thanh',
                "Yêu cầu [{$yc->maYeuCauCode}] đã hoàn tất",
                "Yêu cầu {$tenDichVu} (Phòng {$tenPhong}) đã được hoàn thành. Đã đối soát hóa đơn.",
                $yc->id,
                'Lễ tân'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã xác nhận hoàn thành yêu cầu [{$yc->maYeuCauCode}]!{$thongBaoHoaDon}",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi hoàn thành yêu cầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 10. BÁO SỰ CỐ PHÁT SINH
     */
    public function baoSuCo(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'baoSuCo' => 'required|string|max:1000'
        ], [
            'baoSuCo.required' => 'Vui lòng nhập mô tả sự cố phát sinh.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $yc = $this->timYeuCau($id);
            if (!$yc) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy yêu cầu #' . $id
                ], 404);
            }

            // Kiểm tra phân quyền cập nhật tiến độ
            $userRole = $request->header('X-User-Role') ?? $request->input('userRole') ?? '';
            $userId = $request->header('X-User-Id') ?? $request->input('userId') ?? null;
            if ($userRole && !in_array($userRole, ['Admin', 'admin', 'Lễ tân', 'Quản lý']) && $userId) {
                if ($yc->maNhanVien && (int)$yc->maNhanVien !== (int)$userId) {
                    $nvPhuTrach = $yc->nhanVien ? $yc->nhanVien->hoTen : "NV #{$yc->maNhanVien}";
                    return response()->json([
                        'status' => 'error',
                        'message' => "Chỉ nhân viên được phân công phụ trách [{$nvPhuTrach}] mới được báo cáo sự cố cho yêu cầu này."
                    ], 403);
                }
            }

            $yc->baoSuCo = trim($request->baoSuCo);
            $yc->save();

            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                : '';

            // Thông báo khẩn đến Admin & Lễ tân
            Thong_BaoController::guiThongBao(
                'su_co',
                "Cảnh báo sự cố: [{$yc->maYeuCauCode}]",
                "Phòng {$tenPhong}: {$yc->baoSuCo}. Cần hỗ trợ xử lý ngay.",
                $yc->id,
                'Lễ tân'
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã gửi báo cáo sự cố yêu cầu [{$yc->maYeuCauCode}] đến Ban quản lý & Lễ tân.",
                'data' => $yc
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi báo sự cố: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 11. DANH SÁCH NHÂN VIÊN THEO BỘ PHẬN ĐỂ PHÂN CÔNG
     */
    public function danhSachNhanVienPhanCong(Request $request)
    {
        try {
            $boPhan = $request->query('boPhan');
            $query = Nhan_Vien::where('chucVu', '!=', 'Khách hàng')
                ->where('trangThai', '!=', 'Đã nghỉ việc');

            if ($boPhan && $boPhan !== 'Tất cả' && $boPhan !== 'all') {
                $query->where('boPhan', $boPhan);
            }

            $list = $query->orderBy('hoTen', 'asc')->get();

            $data = $list->map(function ($nv) {
                return [
                    'id' => $nv->id,
                    'maNhanVien' => $nv->maNhanVien,
                    'hoTen' => $nv->hoTen,
                    'boPhan' => $nv->boPhan,
                    'chucVu' => $nv->chucVu,
                    'caLam' => $nv->caLam,
                    'trangThai' => $nv->trangThai,
                    'displayText' => "{$nv->hoTen} · {$nv->boPhan} ({$nv->chucVu})"
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
                'message' => 'Lỗi tải danh sách nhân viên: ' . $e->getMessage()
            ], 500);
        }
    }
}
