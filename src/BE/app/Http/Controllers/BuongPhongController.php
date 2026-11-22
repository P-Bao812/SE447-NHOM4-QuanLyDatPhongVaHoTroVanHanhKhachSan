<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Phong;
use App\Models\Phieu_Buong_Phong;
use App\Models\Chi_Tiet_Buong_Phong;
use App\Models\Nhan_Vien;
use App\Models\San_Pham_Minibar;
use App\Models\Phat_Sinh_Minibar;
use App\Models\Tai_San;
use App\Models\Ghi_Nhan_Tai_San;
use App\Models\Yeu_Cau_Dich_Vu;
use App\Models\Hoa_Don;
use App\Models\Chi_Tiet_Hoa_Don;
use App\Http\Controllers\Thong_BaoController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuongPhongController extends Controller
{
    /**
     * 1. XEM DANH SÁCH PHÒNG CẦN VỆ SINH
     * Trả về danh sách các phòng có trạng thái 'Can_Don', 'Dang_Don', 'Cho_Kiem_Tra' hoặc vừa trả phòng.
     */
    public function danhSachPhongCanVeSinh(Request $request)
    {
        try {
            $query = Phong::with(['loaiPhong', 'phieuBuongPhongs' => function ($q) {
                $q->whereIn('trangThai', ['pending', 'doing'])
                  ->orderBy('id', 'desc');
            }]);

            // Lọc các phòng cần dọn hoặc đang dọn
            $query->whereIn('trangThai', ['Can_Don', 'Dang_Don', 'Cho_Kiem_Tra', 'Ban']);

            if ($request->has('tang') && $request->tang != 'all') {
                $query->where('tang', $request->tang);
            }

            $rooms = $query->orderBy('tang', 'asc')->orderBy('tenPhong', 'asc')->get();

            $data = $rooms->map(function ($room) {
                $activeTask = $room->phieuBuongPhongs->first();
                return [
                    'id' => $room->id,
                    'tenPhong' => $room->tenPhong,
                    'tang' => $room->tang,
                    'trangThai' => $room->trangThai,
                    'tenLoai' => $room->loaiPhong ? $room->loaiPhong->tenLoai : 'Phòng Tiêu Chuẩn',
                    'ghiChuDonDep' => $room->ghiChuDonDep ?? 'Cần vệ sinh sau trả phòng',
                    'thoiGianCheckOut' => $room->thoiGianCheckOut ? Carbon::parse($room->thoiGianCheckOut)->format('H:i d/m/Y') : 'Hôm nay',
                    'hasActiveTask' => $activeTask ? true : false,
                    'activeTaskId' => $activeTask ? $activeTask->id : null,
                    'assignee' => $activeTask && $activeTask->nhanVien ? $activeTask->nhanVien->hoTen : ($activeTask ? $activeTask->ghiChu : 'Chưa phân công'),
                    'taskStatus' => $activeTask ? $activeTask->trangThai : 'Chua_Phan_Cong',
                    'priority' => $activeTask ? $activeTask->mucDoUuTien : 'Cao'
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
                'message' => 'Lỗi lấy danh sách phòng cần vệ sinh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. CẬP NHẬT TRẠNG THÁI PHÒNG
     * Tham số: maPhong, trangThai ('Trong_Sach', 'Can_Don', 'Dang_Don', 'Cho_Kiem_Tra', 'Bao_Tri', 'Dang_Su_Dung')
     */
    public function capNhatTrangThaiPhong(Request $request)
    {
        $request->validate([
            'maPhong' => 'required',
            'trangThai' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            $phong = Phong::find($request->maPhong);
            if (!$phong) {
                // Tìm theo tên phòng ví dụ "Phòng 304" hoặc "304"
                $cleanName = str_replace('Phòng ', '', $request->maPhong);
                $phong = Phong::where('tenPhong', 'like', "%{$cleanName}%")->first();
            }

            if (!$phong) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy phòng: ' . $request->maPhong
                ], 404);
            }

            $trangThaiCu = $phong->trangThai;
            $phong->trangThai = $request->trangThai;

            if ($request->has('ghiChu')) {
                $phong->ghiChuDonDep = $request->ghiChu;
            }

            if ($request->trangThai === 'Trong_Sach') {
                $phong->thoiGianDonGanNhat = Carbon::now();
            }

            $phong->save();

            // Nếu trạng thái đổi sang 'Dang_Don', đồng bộ phiếu buồng phòng liên quan sang 'doing'
            if ($request->trangThai === 'Dang_Don') {
                Phieu_Buong_Phong::where('maPhong', $phong->id)
                    ->where('trangThai', 'pending')
                    ->update([
                        'trangThai' => 'doing',
                        'chiTietTienDo' => 'Bắt đầu lúc ' . Carbon::now()->format('H:i') . ' · Đang dọn dẹp'
                    ]);
            } elseif ($request->trangThai === 'Trong_Sach') {
                // Nếu phòng đã sạch, cập nhật các phiếu đang làm sang 'done'
                Phieu_Buong_Phong::where('maPhong', $phong->id)
                    ->whereIn('trangThai', ['pending', 'doing'])
                    ->update([
                        'trangThai' => 'done',
                        'thoiGianHoanThanh' => Carbon::now(),
                        'thoiHan' => 'Hoàn thành lúc ' . Carbon::now()->format('H:i')
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã cập nhật trạng thái {$phong->tenPhong} từ '{$trangThaiCu}' sang '{$phong->trangThai}'",
                'phong' => $phong
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật trạng thái phòng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. KIỂM TRA PHÒNG (Room Inspection)
     * Đánh giá chất lượng sau khi dọn: Đạt hoặc Chưa đạt
     */
    public function kiemTraPhong(Request $request)
    {
        $request->validate([
            'maPhong' => 'required',
            'ketQua' => 'required|in:Dat,Chua_Dat'
        ]);

        try {
            DB::beginTransaction();

            $phong = Phong::find($request->maPhong);
            if (!$phong) {
                $cleanName = str_replace('Phòng ', '', $request->maPhong);
                $phong = Phong::where('tenPhong', 'like', "%{$cleanName}%")->first();
            }

            if (!$phong) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy phòng: ' . $request->maPhong
                ], 404);
            }

            $nguoiKiemTra = $request->input('nguoiKiemTra', 'Mai Linh');
            $ghiChuChecklist = $request->input('ghiChu', '');
            $checklist = $request->input('checklist', []);

            $checklistSummary = '';
            if (is_array($checklist) && !empty($checklist)) {
                $items = [];
                foreach ($checklist as $k => $v) {
                    $items[] = "$k: " . ($v ? 'Đạt' : 'Chưa đạt');
                }
                $checklistSummary = ' [' . implode(', ', $items) . ']';
            }

            if ($request->ketQua === 'Dat') {
                // Phòng đạt chuẩn -> Chuyển sang Sạch sẵn sàng đón khách
                $phong->trangThai = 'Trong_Sach';
                $phong->ghiChuDonDep = 'Đã kiểm tra đạt chuẩn. Sẵn sàng đón khách.';
                $phong->thoiGianDonGanNhat = Carbon::now();
                $phong->save();

                // Tạo phiếu ghi nhận kiểm tra phòng hoàn thành
                $phieu = Phieu_Buong_Phong::create([
                    'maPhong' => $phong->id,
                    'viTri' => $phong->tenPhong,
                    'maNhanVien' => $request->input('maNhanVien', 4), // 4: Mai Linh (Lễ tân)
                    'tieuDe' => 'Kiểm tra phòng đạt chuẩn',
                    'loaiCongViec' => 'Kiểm tra phòng',
                    'mucDoUuTien' => 'Vừa',
                    'thoiHan' => 'Hoàn thành lúc ' . Carbon::now()->format('H:i'),
                    'ketQuaKiemTra' => 'Dat',
                    'ghiChuKiemTra' => $ghiChuChecklist . $checklistSummary,
                    'thoiGianHoanThanh' => Carbon::now(),
                    'nguoiKiemTra' => $nguoiKiemTra,
                    'ngayTao' => Carbon::now(),
                    'trangThai' => 'done',
                    'ghiChu' => "Kiểm tra phòng đạt chuẩn bởi {$nguoiKiemTra}. Sẵn sàng check-in."
                ]);
            } else {
                // Phòng chưa đạt -> Cần dọn dẹp bổ sung
                $phong->trangThai = 'Can_Don';
                $phong->ghiChuDonDep = 'Kiểm tra chưa đạt: ' . ($ghiChuChecklist ?: 'Cần dọn lại phòng tắm & ga giường');
                $phong->save();

                // Tạo công việc mới dọn dẹp lại với ưu tiên cao
                $phieu = Phieu_Buong_Phong::create([
                    'maPhong' => $phong->id,
                    'viTri' => $phong->tenPhong,
                    'maNhanVien' => $request->input('maNhanVien', 1), // 1: Chị Hương
                    'tieuDe' => 'Vệ sinh bổ sung sau kiểm tra',
                    'loaiCongViec' => 'Dọn phòng',
                    'mucDoUuTien' => 'Cao',
                    'thoiHan' => 'Trước ' . Carbon::now()->addHour()->format('H:i'),
                    'ketQuaKiemTra' => 'Chua_Dat',
                    'ghiChuKiemTra' => $ghiChuChecklist . $checklistSummary,
                    'nguoiKiemTra' => $nguoiKiemTra,
                    'ngayTao' => Carbon::now(),
                    'trangThai' => 'pending',
                    'ghiChu' => "Cần dọn lại: {$ghiChuChecklist} (Kiểm tra bởi {$nguoiKiemTra})"
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $request->ketQua === 'Dat'
                    ? "Phòng {$phong->tenPhong} đã kiểm tra ĐẠT và sẵn sàng đón khách!"
                    : "Phòng {$phong->tenPhong} CHƯA ĐẠT và đã được chuyển lại danh sách cần dọn dẹp.",
                'ketQua' => $request->ketQua,
                'phong' => $phong,
                'phieu' => $phieu
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi kiểm tra phòng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. BÁO CÁO TÌNH TRẠNG PHÒNG SAU KHI HOÀN THÀNH
     * Ghi nhận tình trạng sau dọn dẹp: bàn giao lễ tân, minibar phát sinh, tài sản hư hỏng.
     */
    public function baoCaoHoanThanh(Request $request)
    {
        $request->validate([
            'maPhieu' => 'nullable',
            'maPhong' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $phieu = null;
            if ($request->has('maPhieu') && $request->maPhieu) {
                $phieu = Phieu_Buong_Phong::find($request->maPhieu);
            }

            $phong = null;
            if ($phieu && $phieu->maPhong) {
                $phong = Phong::find($phieu->maPhong);
            } elseif ($request->has('maPhong') && $request->maPhong) {
                $phong = Phong::find($request->maPhong);
                if (!$phong) {
                    $cleanName = str_replace('Phòng ', '', $request->maPhong);
                    $phong = Phong::where('tenPhong', 'like', "%{$cleanName}%")->first();
                }
            }

            $ghiChuBanGiao = $request->input('ghiChuBanGiao', 'Đã dọn dẹp sạch sẽ, bàn giao lễ tân.');
            $tinhTrangPhong = $request->input('tinhTrangPhong', 'Cho_Kiem_Tra'); // Cho_Kiem_Tra hoặc Trong_Sach
            $nhanVienThucHien = $request->input('nhanVienThucHien', $phieu && $phieu->nhanVien ? $phieu->nhanVien->hoTen : 'Chị Hương');

            // 1. Cập nhật phiếu buồng phòng
            if ($phieu) {
                $phieu->trangThai = 'done';
                $phieu->thoiGianHoanThanh = Carbon::now();
                $phieu->thoiHan = 'Hoàn thành lúc ' . Carbon::now()->format('H:i');
                $phieu->ghiChu = $ghiChuBanGiao;
                $phieu->save();

                // ĐỒNG BỘ YÊU CẦU DỊCH VỤ NẾU CÓ
                if ($phieu->maYeuCauDichVu) {
                    $yc = Yeu_Cau_Dich_Vu::with('dichVu', 'luuTru.chiTietDatPhong.phong')->find($phieu->maYeuCauDichVu);
                    if ($yc) {
                        $yc->trangThai = 'Da_Hoan_Thanh';
                        if (!$yc->thoiGianHoanThanh) $yc->thoiGianHoanThanh = Carbon::now();

                        // Cộng chi phí vào hóa đơn lưu trú
                        if (!$yc->daGhiHoaDon && $yc->maLuuTru) {
                            $hoaDon = Hoa_Don::firstOrCreate(
                                ['maLuuTru' => $yc->maLuuTru],
                                ['ngayLap' => Carbon::now(), 'tongTien' => 0, 'trangThai' => 'Chưa thanh toán']
                            );
                            $itemTag = "YC#{$yc->id}";
                            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dọn phòng theo yêu cầu';
                            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                                : ($phong ? $phong->tenPhong : 'Phòng khách');
                            $thanhTien = $yc->soLuong * $yc->donGiaApDung;

                            $chiTiet = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)
                                ->where('noiDung', 'like', "%{$itemTag}%")
                                ->first();

                            if (!$chiTiet) {
                                Chi_Tiet_Hoa_Don::create([
                                    'maHoaDon' => $hoaDon->id,
                                    'noiDung' => "Dịch vụ: {$tenDichVu} (Phòng {$tenPhong} - {$itemTag})",
                                    'soLuong' => $yc->soLuong,
                                    'donGia' => $yc->donGiaApDung,
                                    'thanhTien' => $thanhTien
                                ]);
                            }

                            $hoaDon->tongTien = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)->sum('thanhTien');
                            $hoaDon->save();
                            $yc->daGhiHoaDon = true;
                        }

                        $yc->save();

                        Thong_BaoController::guiThongBao(
                            'hoan_thanh',
                            "Dọn phòng hoàn tất [{$yc->maYeuCauCode}]",
                            "Yêu cầu dọn phòng của quý khách đã hoàn thành. Đã bàn giao sạch sẽ.",
                            $yc->id,
                            'Khách hàng',
                            $phieu->id
                        );
                    }
                }
            }

            // 2. Cập nhật phòng
            if ($phong) {
                $phong->trangThai = $tinhTrangPhong;
                $phong->ghiChuDonDep = $ghiChuBanGiao;
                $phong->thoiGianDonGanNhat = Carbon::now();
                $phong->save();
            }

            // 3. Ghi nhận phát sinh Minibar (nếu có khách sử dụng)
            $minibarList = $request->input('minibar', []);
            $minibarAdded = [];
            if (!empty($minibarList) && is_array($minibarList)) {
                foreach ($minibarList as $item) {
                    if (!empty($item['maSanPham']) && !empty($item['soLuong']) && $item['soLuong'] > 0) {
                        $sp = San_Pham_Minibar::find($item['maSanPham']);
                        $donGia = $sp ? $sp->donGia : ($item['donGia'] ?? 25000);

                        $record = Phat_Sinh_Minibar::create([
                            'maLuuTru' => $request->input('maLuuTru', 1),
                            'maSanPham' => $item['maSanPham'],
                            'soLuong' => $item['soLuong'],
                            'donGiaApDung' => $donGia,
                            'thoiGian' => Carbon::now()
                        ]);
                        $minibarAdded[] = $record;
                    }
                }
            }

            // 4. Ghi nhận tài sản hư hỏng / mất mát (nếu có)
            $taiSanList = $request->input('taiSan', []);
            $taiSanAdded = [];
            if (!empty($taiSanList) && is_array($taiSanList)) {
                foreach ($taiSanList as $item) {
                    if (!empty($item['tenTaiSan'])) {
                        $record = Ghi_Nhan_Tai_San::create([
                            'maLuuTru' => $request->input('maLuuTru', 1),
                            'maTaiSan' => $item['maTaiSan'] ?? 1,
                            'loaiPhatSinh' => $item['loaiPhatSinh'] ?? 'Hư hỏng nhẹ',
                            'thoiGian' => Carbon::now(),
                            'soTienBoiThuong' => $item['soTienBoiThuong'] ?? 0,
                            'ghiChu' => $item['ghiChu'] ?? 'Ghi nhận sau khi dọn phòng'
                        ]);
                        $taiSanAdded[] = $record;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã lưu báo cáo hoàn thành dọn phòng và bàn giao thành công!',
                'phieu' => $phieu,
                'phong' => $phong,
                'minibarCount' => count($minibarAdded),
                'taiSanCount' => count($taiSanAdded)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi ghi nhận báo cáo hoàn thành: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. DANH SÁCH TẤT CẢ CÔNG VIỆC VẬN HÀNH (Dành cho Kanban Board)
     */
    public function danhSachCongViec(Request $request)
    {
        try {
            $query = Phieu_Buong_Phong::with(['phong', 'nhanVien'])->orderBy('id', 'desc');

            if ($request->has('category') && $request->category != 'all') {
                $query->where('loaiCongViec', $request->category);
            }

            if ($request->has('assignee') && $request->assignee != 'all') {
                $query->whereHas('nhanVien', function ($q) use ($request) {
                    $q->where('hoTen', 'like', "%{$request->assignee}%");
                });
            }

            if ($request->has('priority') && $request->priority != 'all') {
                $query->where('mucDoUuTien', $request->priority);
            }

            $tasks = $query->get();

            $data = $tasks->map(function ($task) {
                $assigneeName = $task->nhanVien ? $task->nhanVien->hoTen : 'Chưa phân công';
                return [
                    'id' => $task->id,
                    'maYeuCauDichVu' => $task->maYeuCauDichVu,
                    'status' => $task->trangThai, // 'pending', 'doing', 'done'
                    'location' => $task->viTri ?: ($task->phong ? $task->phong->tenPhong : 'Phòng khách sạn'),
                    'maPhong' => $task->maPhong,
                    'category' => $task->loaiCongViec ?: 'Dọn phòng',
                    'priority' => $task->mucDoUuTien ?: 'Vừa',
                    'title' => $task->tieuDe ?: 'Dọn phòng sau trả khách',
                    'note' => $task->ghiChu ?: 'Không có ghi chú thêm',
                    'assignee' => $assigneeName,
                    'assigneeInitial' => $this->getInitial($assigneeName),
                    'timeInfo' => $task->thoiHan ?: 'Trước 14:00',
                    'ongoingDetail' => $task->chiTietTienDo,
                    'ketQuaKiemTra' => $task->ketQuaKiemTra,
                    'ngayTao' => Carbon::parse($task->ngayTao)->format('H:i d/m/Y')
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải danh sách công việc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. TẠO CÔNG VIỆC VẬN HÀNH MỚI
     */
    public function taoCongViec(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
            'title' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // Tìm phòng tương ứng nếu location là số phòng
            $maPhong = null;
            $cleanName = str_replace('Phòng ', '', $request->location);
            $phong = Phong::where('tenPhong', 'like', "%{$cleanName}%")->first();
            if ($phong) {
                $maPhong = $phong->id;
                // Nếu tạo việc dọn phòng thì chuyển trạng thái phòng thành 'Can_Don'
                if ($request->input('category') === 'Dọn phòng' && $phong->trangThai !== 'Dang_Don') {
                    $phong->trangThai = 'Can_Don';
                    $phong->save();
                } elseif ($request->input('category') === 'Bảo trì') {
                    $phong->trangThai = 'Bao_Tri';
                    $phong->save();
                }
            }

            // Tìm nhân viên
            $maNhanVien = 1;
            if ($request->has('assignee')) {
                $nv = Nhan_Vien::where('hoTen', 'like', "%{$request->assignee}%")->first();
                if ($nv) {
                    $maNhanVien = $nv->id;
                }
            }

            $status = $request->input('status', 'pending');
            $ongoingDetail = null;
            if ($status === 'doing') {
                $ongoingDetail = 'Bắt đầu lúc ' . Carbon::now()->format('H:i');
            }

            $task = Phieu_Buong_Phong::create([
                'maPhong' => $maPhong,
                'viTri' => $request->location,
                'maNhanVien' => $maNhanVien,
                'tieuDe' => $request->title,
                'loaiCongViec' => $request->input('category', 'Dọn phòng'),
                'mucDoUuTien' => $request->input('priority', 'Cao'),
                'thoiHan' => $request->input('timeInfo', 'Trước 14:00'),
                'chiTietTienDo' => $ongoingDetail,
                'ngayTao' => Carbon::now(),
                'trangThai' => $status,
                'ghiChu' => $request->input('note', 'Thay ga giường, bổ sung đồ tiêu hao.')
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã tạo công việc mới thành công!',
                'data' => $task
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tạo công việc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. CHUYỂN TRẠNG THÁI CÔNG VIỆC (pending -> doing -> done)
     */
    public function chuyenTrangThai(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required|in:pending,doing,done'
        ]);

        try {
            DB::beginTransaction();

            $task = Phieu_Buong_Phong::find($request->id);
            if (!$task) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy công việc #' . $request->id
                ], 404);
            }

            $task->trangThai = $request->status;

            if ($request->status === 'doing') {
                $task->chiTietTienDo = 'Bắt đầu lúc ' . Carbon::now()->format('H:i') . ' · 20 phút';
                if ($task->maPhong) {
                    $phong = Phong::find($task->maPhong);
                    if ($phong && $phong->trangThai !== 'Dang_Su_Dung') {
                        $phong->trangThai = ($task->loaiCongViec === 'Bảo trì') ? 'Bao_Tri' : 'Dang_Don';
                        $phong->save();
                    }
                }

                // ĐỒNG BỘ SANG YÊU CẦU DỊCH VỤ: 'Dang_Thuc_Hien'
                if ($task->maYeuCauDichVu) {
                    $yc = Yeu_Cau_Dich_Vu::find($task->maYeuCauDichVu);
                    if ($yc) {
                        $yc->trangThai = 'Dang_Thuc_Hien';
                        if (!$yc->thoiGianBatDau) $yc->thoiGianBatDau = Carbon::now();
                        if ($task->maNhanVien && !$yc->maNhanVien) $yc->maNhanVien = $task->maNhanVien;
                        $yc->save();

                        Thong_BaoController::guiThongBao(
                            'thuc_hien',
                            "Đang thực hiện dọn phòng [{$yc->maYeuCauCode}]",
                            "Nhân viên buồng phòng đang thực hiện dọn phòng tại {$task->viTri}.",
                            $yc->id,
                            'Lễ tân',
                            $task->id
                        );
                    }
                }
            } elseif ($request->status === 'done') {
                $task->thoiGianHoanThanh = Carbon::now();
                $task->thoiHan = 'Hoàn thành lúc ' . Carbon::now()->format('H:i');
                if ($task->maPhong) {
                    $phong = Phong::find($task->maPhong);
                    if ($phong) {
                        $phong->trangThai = ($task->loaiCongViec === 'Kiểm tra phòng') ? 'Trong_Sach' : 'Cho_Kiem_Tra';
                        $phong->thoiGianDonGanNhat = Carbon::now();
                        $phong->save();
                    }
                }

                // ĐỒNG BỘ SANG YÊU CẦU DỊCH VỤ: 'Da_Hoan_Thanh' & CỘNG HÓA ĐƠN NẾU CHƯA CỘNG
                if ($task->maYeuCauDichVu) {
                    $yc = Yeu_Cau_Dich_Vu::with('dichVu', 'luuTru.chiTietDatPhong.phong')->find($task->maYeuCauDichVu);
                    if ($yc) {
                        $yc->trangThai = 'Da_Hoan_Thanh';
                        if (!$yc->thoiGianHoanThanh) $yc->thoiGianHoanThanh = Carbon::now();

                        // Cộng chi phí vào hóa đơn lưu trú nếu chưa cộng
                        if (!$yc->daGhiHoaDon && $yc->maLuuTru) {
                            $hoaDon = Hoa_Don::firstOrCreate(
                                ['maLuuTru' => $yc->maLuuTru],
                                ['ngayLap' => Carbon::now(), 'tongTien' => 0, 'trangThai' => 'Chưa thanh toán']
                            );
                            $itemTag = "YC#{$yc->id}";
                            $tenDichVu = $yc->dichVu ? $yc->dichVu->tenDichVu : 'Dọn phòng theo yêu cầu';
                            $tenPhong = ($yc->luuTru && $yc->luuTru->chiTietDatPhong && $yc->luuTru->chiTietDatPhong->phong)
                                ? $yc->luuTru->chiTietDatPhong->phong->tenPhong
                                : $task->viTri;
                            $thanhTien = $yc->soLuong * $yc->donGiaApDung;

                            $chiTiet = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)
                                ->where('noiDung', 'like', "%{$itemTag}%")
                                ->first();

                            if (!$chiTiet) {
                                Chi_Tiet_Hoa_Don::create([
                                    'maHoaDon' => $hoaDon->id,
                                    'noiDung' => "Dịch vụ: {$tenDichVu} ({$tenPhong} - {$itemTag})",
                                    'soLuong' => $yc->soLuong,
                                    'donGia' => $yc->donGiaApDung,
                                    'thanhTien' => $thanhTien
                                ]);
                            }

                            $hoaDon->tongTien = Chi_Tiet_Hoa_Don::where('maHoaDon', $hoaDon->id)->sum('thanhTien');
                            $hoaDon->save();
                            $yc->daGhiHoaDon = true;
                        }

                        $yc->save();

                        // Thông báo đến Khách hàng và Lễ tân
                        Thong_BaoController::guiThongBao(
                            'hoan_thanh',
                            "Dọn phòng hoàn tất [{$yc->maYeuCauCode}]",
                            "Yêu cầu dọn phòng của quý khách tại {$task->viTri} đã hoàn thành sạch sẽ.",
                            $yc->id,
                            'Khách hàng',
                            $task->id
                        );
                        Thong_BaoController::guiThongBao(
                            'hoan_thanh',
                            "Hoàn tất yêu cầu buồng phòng [{$yc->maYeuCauCode}]",
                            "Nhân viên đã hoàn thành dọn phòng tại {$task->viTri}. Đã cập nhật vào hóa đơn lưu trú.",
                            $yc->id,
                            'Lễ tân',
                            $task->id
                        );
                    }
                }
            }

            $task->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Đã chuyển công việc sang '{$request->status}'",
                'data' => $task
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi chuyển trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 8. THỐNG KÊ TIẾN ĐỘ CA LÀM VIỆC
     */
    public function thongKeCa()
    {
        try {
            $total = Phieu_Buong_Phong::count();
            $completed = Phieu_Buong_Phong::where('trangThai', 'done')->count();
            $open = Phieu_Buong_Phong::whereIn('trangThai', ['pending', 'doing'])->count();
            $highPriority = Phieu_Buong_Phong::whereIn('trangThai', ['pending', 'doing'])
                ->where('mucDoUuTien', 'Cao')
                ->count();
            $percent = $total > 0 ? round(($completed / $total) * 100) : 0;

            // Nhân viên trong ca sáng
            $staff = [
                ['name' => 'Hương', 'fullName' => 'Chị Hương', 'initial' => 'HƯ', 'role' => 'Tổ trưởng buồng phòng'],
                ['name' => 'Hoa', 'fullName' => 'Chị Hoa', 'initial' => 'HO', 'role' => 'Nhân viên buồng phòng'],
                ['name' => 'Tuấn', 'fullName' => 'Anh Tuấn', 'initial' => 'TU', 'role' => 'Nhân viên bảo trì'],
                ['name' => 'Linh', 'fullName' => 'Mai Linh', 'initial' => 'ML', 'role' => 'Lễ tân ca sáng']
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'shiftName' => 'Ca sáng',
                    'shiftHours' => '07:00 – 15:00',
                    'activeStaffCount' => count($staff),
                    'staff' => $staff,
                    'totalTasks' => $total,
                    'completedTasks' => $completed,
                    'openTasks' => $open,
                    'highPriorityTasks' => $highPriority,
                    'completionPercentage' => $percent,
                    'roomsNeedingCleaning' => Phong::whereIn('trangThai', ['Can_Don', 'Ban'])->count(),
                    'roomsUnderMaintenance' => Phong::where('trangThai', 'Bao_Tri')->count(),
                    'roomsReady' => Phong::where('trangThai', 'Trong_Sach')->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 9. LẤY TOÀN BỘ PHÒNG
     */
    public function danhSachPhong()
    {
        $rooms = Phong::with('loaiPhong')->orderBy('tenPhong', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $rooms
        ]);
    }

    /**
     * 10. DANH SÁCH NHÂN VIÊN
     */
    public function danhSachNhanVien()
    {
        $staff = Nhan_Vien::all();
        return response()->json([
            'status' => 'success',
            'data' => $staff
        ]);
    }

    /**
     * 11. DANH MỤC MINIBAR & TÀI SẢN
     */
    public function danhMucDichVu(Request $request)
    {
        $minibar = San_Pham_Minibar::all();
        $taiSan = Tai_San::all();

        return response()->json([
            'status' => 'success',
            'minibar' => $minibar,
            'taiSan' => $taiSan
        ]);
    }

    private function getInitial($name)
    {
        if (str_contains($name, 'Hương')) return 'HƯ';
        if (str_contains($name, 'Hoa')) return 'HO';
        if (str_contains($name, 'Tuấn')) return 'TU';
        if (str_contains($name, 'Linh')) return 'ML';
        return mb_substr($name, 0, 2);
    }
}
