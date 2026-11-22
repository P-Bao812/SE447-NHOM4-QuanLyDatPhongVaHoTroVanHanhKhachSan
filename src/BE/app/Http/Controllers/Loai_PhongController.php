<?php

namespace App\Http\Controllers;

use App\Models\Loai_Phong;
use App\Models\Phong;
use App\Models\Tien_Nghi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Loai_PhongController extends Controller
{
    /**
     * Danh sách loại phòng kèm bộ lọc tìm kiếm
     */
    public function index(Request $request)
    {
        $search = trim($request->query('q', $request->query('search', '')));
        $trangThai = $request->query('trangThai', $request->query('status', ''));
        $giaMin = $request->query('giaMin');
        $giaMax = $request->query('giaMax');
        $sucChua = $request->query('sucChua');

        $query = Loai_Phong::with(['phongs', 'tienNghis'])->orderBy('giaTieuChuan', 'asc');

        // 1. Tìm kiếm theo tên loại phòng
        if (!empty($search)) {
            $query->where('tenLoai', 'like', "%{$search}%");
        }

        // 2. Lọc theo trạng thái kinh doanh
        if (!empty($trangThai) && $trangThai !== 'Tất cả' && $trangThai !== 'Mọi trạng thái') {
            if ($trangThai === 'Đang mở bán') {
                $query->where(function ($q) {
                    $q->where('trangThai', 'Đang mở bán')
                      ->orWhere('trangThai', 'Hoat_Dong')
                      ->orWhere('trangThai', 'Kinh doanh');
                });
            } elseif ($trangThai === 'Ngừng kinh doanh') {
                $query->where(function ($q) {
                    $q->where('trangThai', 'Ngừng kinh doanh')
                      ->orWhere('trangThai', 'Ngung_Kinh_Doanh')
                      ->orWhere('trangThai', 'Tạm ngưng');
                });
            } else {
                $query->where('trangThai', $trangThai);
            }
        }

        // 3. Lọc theo khoảng giá mỗi đêm
        if ($giaMin !== null && is_numeric($giaMin)) {
            $query->where('giaTieuChuan', '>=', (float) $giaMin);
        }
        if ($giaMax !== null && is_numeric($giaMax) && (float) $giaMax > 0) {
            $query->where('giaTieuChuan', '<=', (float) $giaMax);
        }

        // 4. Lọc theo sức chứa
        if ($sucChua !== null && is_numeric($sucChua) && (int) $sucChua > 0) {
            $query->where('sucChuaToiDa', '>=', (int) $sucChua);
        }

        $items = $query->get();

        $data = $items->map(function ($lp) {
            $phongs = $lp->phongs;
            $soLuongPhong = $phongs->count();
            $minTang = $phongs->min('tang');
            $maxTang = $phongs->max('tang');
            $tangInfo = ($minTang && $maxTang)
                ? ($minTang == $maxTang ? "Tầng {$minTang}" : "Tầng {$minTang}–{$maxTang}")
                : 'Chưa phân tầng';

            $dsTenPhong = $phongs->pluck('tenPhong')->sort()->values();
            $phongText = $dsTenPhong->count() > 0
                ? 'Phòng: ' . $dsTenPhong->take(10)->implode(', ') . ($dsTenPhong->count() > 10 ? '...' : '')
                : 'Chưa có phòng liên kết';

            // Phân tích metadata trong moTa
            $meta = [];
            if (!empty($lp->moTa)) {
                $decoded = json_decode($lp->moTa, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            }

            $dienTich = $meta['dienTich'] ?? 28;
            $cauHinhGiuong = $meta['cauHinhGiuong'] ?? '1 giường đôi · 1,8 × 2 m';
            $tienIchTomTat = $meta['tienIchTomTat'] ?? 'Wi-Fi & máy lạnh đầy đủ';
            $dieuKien = $meta['dieuKien'] ?? '1 đêm · Không giới hạn tối đa';

            $tienNghiNames = $lp->tienNghis->pluck('tenTienNghi')->toArray();
            if (empty($tienNghiNames) && isset($meta['tienNghis'])) {
                $tienNghiNames = $meta['tienNghis'];
            }

            $statusFormatted = in_array($lp->trangThai, ['Đang mở bán', 'Hoat_Dong', 'Kinh doanh'])
                ? 'Đang mở bán'
                : 'Ngừng kinh doanh';

            $khuyenMai = \App\Http\Controllers\Khuyen_MaiController::getPromotionInfoForRoom((int)$lp->id, (float)$lp->giaTieuChuan);

            return [
                'id' => $lp->id,
                'tenLoai' => $lp->tenLoai,
                'giaTieuChuan' => (int) $lp->giaTieuChuan,
                'giaDinhDang' => number_format($lp->giaTieuChuan, 0, ',', '.') . ' đ',
                'sucChuaToiDa' => (int) $lp->sucChuaToiDa,
                'trangThai' => $statusFormatted,
                'dienTich' => $dienTich,
                'cauHinhGiuong' => $cauHinhGiuong,
                'tienIchTomTat' => $tienIchTomTat,
                'dieuKien' => $dieuKien,
                'soLuongPhong' => $soLuongPhong,
                'tangInfo' => $tangInfo,
                'danhSachPhong' => $dsTenPhong,
                'phongText' => $phongText,
                'tienNghis' => $tienNghiNames,
                'khuyenMai' => $khuyenMai,
            ];
        });

        return response()->json([
            'status' => 'success',
            'total' => $data->count(),
            'data' => $data,
        ]);
    }

    /**
     * Chi tiết 1 loại phòng
     */
    public function show($id)
    {
        $lp = Loai_Phong::with(['phongs', 'tienNghis'])->find($id);
        if (!$lp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy loại phòng'], 404);
        }

        $phongs = $lp->phongs;
        $meta = json_decode($lp->moTa, true) ?? [];

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $lp->id,
                'tenLoai' => $lp->tenLoai,
                'giaTieuChuan' => (int) $lp->giaTieuChuan,
                'sucChuaToiDa' => (int) $lp->sucChuaToiDa,
                'trangThai' => in_array($lp->trangThai, ['Đang mở bán', 'Hoat_Dong', 'Kinh doanh']) ? 'Đang mở bán' : 'Ngừng kinh doanh',
                'dienTich' => $meta['dienTich'] ?? 28,
                'cauHinhGiuong' => $meta['cauHinhGiuong'] ?? '1 giường đôi · 1,8 × 2 m',
                'tienIchTomTat' => $meta['tienIchTomTat'] ?? '',
                'dieuKien' => $meta['dieuKien'] ?? '1 đêm · Không giới hạn tối đa',
                'soLuongPhong' => $phongs->count(),
                'danhSachPhong' => $phongs->pluck('tenPhong')->values(),
                'tienNghis' => $lp->tienNghis->pluck('tenTienNghi')->toArray(),
                'khuyenMai' => \App\Http\Controllers\Khuyen_MaiController::getPromotionInfoForRoom((int)$lp->id, (float)$lp->giaTieuChuan),
            ]
        ]);
    }

    /**
     * Thêm mới loại phòng
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenLoai' => 'required|string|max:50|unique:Loai_Phong,tenLoai',
            'giaTieuChuan' => 'required|numeric|min:1',
            'sucChuaToiDa' => 'required|integer|min:1',
        ], [
            'tenLoai.required' => 'Vui lòng nhập tên loại phòng.',
            'tenLoai.unique' => 'Tên loại phòng này đã tồn tại trong hệ thống.',
            'giaTieuChuan.required' => 'Vui lòng nhập giá bán mỗi đêm.',
            'giaTieuChuan.min' => 'Giá bán mỗi đêm phải lớn hơn 0.',
            'sucChuaToiDa.required' => 'Vui lòng nhập sức chứa.',
            'sucChuaToiDa.min' => 'Sức chứa tối thiểu là 1 người.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $meta = [
            'dienTich' => $request->dienTich ?? 28,
            'cauHinhGiuong' => $request->cauHinhGiuong ?? '1 giường đôi · 1,8 × 2 m',
            'tienIchTomTat' => $request->tienIchTomTat ?? '',
            'dieuKien' => $request->dieuKien ?? '1 đêm · Không giới hạn tối đa',
            'tienNghis' => $request->tienNghis ?? [],
        ];

        $lp = Loai_Phong::create([
            'tenLoai' => trim($request->tenLoai),
            'moTa' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'giaTieuChuan' => (int) $request->giaTieuChuan,
            'sucChuaToiDa' => (int) $request->sucChuaToiDa,
            'trangThai' => $request->trangThai ?? 'Đang mở bán',
        ]);

        // Đồng bộ tiện nghi nếu có
        if ($request->has('tienNghis') && is_array($request->tienNghis)) {
            $this->dongBoTienNghi($lp->id, $request->tienNghis);
        }

        // Tự động tạo phòng khởi tạo liên kết với loại phòng mới để hiển thị ngay bên các phòng hiện có
        $soLuongPhongTao = (int) ($request->soLuongPhongTao ?? $request->soLuongPhong ?? 2);
        if ($soLuongPhongTao > 0) {
            $tang = (int) ($request->tang ?? 0);
            if ($tang <= 0) {
                $maxTang = (int) Phong::max('tang');
                $tang = $maxTang > 0 ? ($maxTang + 1) : 4;
            }

            $now = Carbon::now();
            $roomIndex = 1;
            for ($i = 1; $i <= $soLuongPhongTao; $i++) {
                do {
                    $tenPhong = sprintf('P%d%02d', $tang, $roomIndex);
                    $exists = Phong::where('tenPhong', $tenPhong)->exists();
                    $roomIndex++;
                } while ($exists);

                Phong::create([
                    'maLoaiPhong' => $lp->id,
                    'tenPhong' => $tenPhong,
                    'tang' => $tang,
                    'trangThai' => 'Sẵn sàng',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Đã tạo loại phòng '{$lp->tenLoai}' thành công và chuyển vào danh sách phòng hiện có!",
            'data' => $lp,
        ], 201);
    }

    /**
     * Cập nhật loại phòng
     * Giá mới chỉ áp dụng cho các đặt phòng mới, không ảnh hưởng đặt phòng cũ
     */
    public function update(Request $request, $id)
    {
        $lp = Loai_Phong::find($id);
        if (!$lp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy loại phòng cần sửa'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tenLoai' => "required|string|max:50|unique:Loai_Phong,tenLoai,{$id}",
            'giaTieuChuan' => 'required|numeric|min:1',
            'sucChuaToiDa' => 'required|integer|min:1',
        ], [
            'tenLoai.required' => 'Vui lòng nhập tên loại phòng.',
            'tenLoai.unique' => 'Tên loại phòng này đã tồn tại trong hệ thống.',
            'giaTieuChuan.required' => 'Vui lòng nhập giá bán mỗi đêm.',
            'giaTieuChuan.min' => 'Giá bán mỗi đêm phải lớn hơn 0.',
            'sucChuaToiDa.required' => 'Vui lòng nhập sức chứa.',
            'sucChuaToiDa.min' => 'Sức chứa tối thiểu là 1 người.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldMeta = json_decode($lp->moTa, true) ?? [];
        $meta = [
            'dienTich' => $request->dienTich ?? ($oldMeta['dienTich'] ?? 28),
            'cauHinhGiuong' => $request->cauHinhGiuong ?? ($oldMeta['cauHinhGiuong'] ?? '1 giường đôi · 1,8 × 2 m'),
            'tienIchTomTat' => $request->tienIchTomTat ?? ($oldMeta['tienIchTomTat'] ?? ''),
            'dieuKien' => $request->dieuKien ?? ($oldMeta['dieuKien'] ?? '1 đêm · Không giới hạn tối đa'),
            'tienNghis' => $request->tienNghis ?? ($oldMeta['tienNghis'] ?? []),
        ];

        $lp->update([
            'tenLoai' => trim($request->tenLoai),
            'moTa' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'giaTieuChuan' => (int) $request->giaTieuChuan,
            'sucChuaToiDa' => (int) $request->sucChuaToiDa,
            'trangThai' => $request->trangThai ?? $lp->trangThai,
        ]);

        // Cập nhật tiện nghi liên kết
        if ($request->has('tienNghis') && is_array($request->tienNghis)) {
            $this->dongBoTienNghi($lp->id, $request->tienNghis);
        }

        return response()->json([
            'status' => 'success',
            'message' => "Đã cập nhật loại phòng '{$lp->tenLoai}' thành công!",
            'data' => $lp,
        ]);
    }

    /**
     * Bật / Tắt trạng thái kinh doanh của loại phòng
     */
    public function doiTrangThai(Request $request, $id)
    {
        $lp = Loai_Phong::find($id);
        if (!$lp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy loại phòng'], 404);
        }

        $currentStatus = in_array($lp->trangThai, ['Đang mở bán', 'Hoat_Dong', 'Kinh doanh'])
            ? 'Đang mở bán'
            : 'Ngừng kinh doanh';

        $newStatus = ($currentStatus === 'Đang mở bán') ? 'Ngừng kinh doanh' : 'Đang mở bán';

        if ($request->has('trangThai')) {
            $newStatus = $request->trangThai;
        }

        $lp->trangThai = $newStatus;
        $lp->save();

        $msg = ($newStatus === 'Ngừng kinh doanh')
            ? "Đã ngừng kinh doanh loại phòng '{$lp->tenLoai}'."
            : "Đã mở bán lại loại phòng '{$lp->tenLoai}'.";

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => [
                'id' => $lp->id,
                'trangThai' => $newStatus,
            ]
        ]);
    }

    /**
     * Xóa loại phòng (bảo toàn lịch sử giao dịch)
     */
    public function destroy($id)
    {
        $lp = Loai_Phong::with('phongs')->find($id);
        if (!$lp) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy loại phòng'], 404);
        }

        // Kiểm tra xem đã có phòng hoặc giao dịch đặt phòng nào chưa
        $hasRooms = $lp->phongs()->count() > 0;
        $hasBookings = DB::table('chi_tiet_dat_phong')
            ->whereIn('maPhong', $lp->phongs()->pluck('id'))
            ->exists();

        if ($hasRooms || $hasBookings) {
            // Không xóa cứng, chuyển sang ngừng kinh doanh
            $lp->trangThai = 'Ngừng kinh doanh';
            $lp->save();

            return response()->json([
                'status' => 'success',
                'message' => "Loại phòng '{$lp->tenLoai}' đã có phòng liên kết hoặc lịch sử giao dịch nên đã được chuyển sang trạng thái 'Ngừng kinh doanh' để bảo toàn dữ liệu.",
                'data' => $lp,
            ]);
        }

        // Nếu là loại phòng mới chưa có dữ liệu liên kết nào thì có thể xóa
        DB::table('loai_phong_tien_nghi')->where('maLoaiPhong', $id)->delete();
        $lp->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Đã xóa loại phòng thành công!",
        ]);
    }

    /**
     * Danh sách tất cả tiện nghi có sẵn
     */
    public function danhSachTienNghi()
    {
        $tienNghis = Tien_Nghi::select('id', 'tenTienNghi', 'moTa')->get();
        return response()->json([
            'status' => 'success',
            'data' => $tienNghis,
        ]);
    }

    /**
     * Helper đồng bộ tiện nghi
     */
    private function dongBoTienNghi($maLoaiPhong, array $tenTienNghis)
    {
        DB::table('loai_phong_tien_nghi')->where('maLoaiPhong', $maLoaiPhong)->delete();
        $now = Carbon::now();

        foreach ($tenTienNghis as $ten) {
            $tn = Tien_Nghi::firstOrCreate(
                ['tenTienNghi' => trim($ten)],
                ['moTa' => 'Tiện nghi phòng', 'created_at' => $now, 'updated_at' => $now]
            );

            DB::table('loai_phong_tien_nghi')->insert([
                'maLoaiPhong' => $maLoaiPhong,
                'maTienNghi' => $tn->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
