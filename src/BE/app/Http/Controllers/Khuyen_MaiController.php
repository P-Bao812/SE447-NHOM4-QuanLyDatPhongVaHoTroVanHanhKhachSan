<?php

namespace App\Http\Controllers;

use App\Models\Loai_Phong;
use App\Models\Hoa_Don;
use App\Models\Chi_Tiet_Hoa_Don;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Khuyen_MaiController extends Controller
{
    private const CACHE_KEY = 'hotel_promotions_list';

    /**
     * Khởi tạo dữ liệu mẫu 4 chương trình khuyến mãi theo đúng giao diện tham khảo
     */
    private function getInitialPromotions(): array
    {
        return [
            [
                'id' => 1,
                'maKhuyenMai' => 'ANNHIEN10',
                'tenKhuyenMai' => 'Kỳ nghỉ an nhiên',
                'loaiGiam' => 'Phan_Tram',
                'giaTriGiam' => 10,
                'giamToiDa' => 300000,
                'giaTriDatToiThieu' => 1500000,
                'ngayBatDau' => '2026-11-01 00:00:00',
                'ngayKetThuc' => '2026-11-30 23:59:59',
                'ngayLuuTruApDung' => '10/11–15/12/2026 · Tối thiểu 2 đêm',
                'soDemToiThieu' => 2,
                'loaiPhongApDung' => [1, 2],
                'loaiPhongNames' => ['Superior', 'Deluxe'],
                'nguonDat' => 'Trực tiếp',
                'gioiHanToanChuongTrinh' => 100,
                'luotDaDung' => 18,
                'moiKhachHang' => 1,
                'trangThai' => 'Đang chạy',
                'moTa' => 'Giảm 10% tối đa 300.000 đ cho phòng Superior và Deluxe khi đặt từ 2 đêm.',
                'created_at' => '2026-11-01 08:00:00',
                'updated_at' => '2026-11-08 10:00:00'
            ],
            [
                'id' => 2,
                'maKhuyenMai' => 'O2DEM',
                'tenKhuyenMai' => 'Ở lâu, nghỉ chậm',
                'loaiGiam' => 'So_Tien',
                'giaTriGiam' => 100000,
                'giamToiDa' => 100000,
                'giaTriDatToiThieu' => 800000,
                'ngayBatDau' => '2026-11-01 00:00:00',
                'ngayKetThuc' => '2026-11-30 23:59:59',
                'ngayLuuTruApDung' => '01/11–30/11/2026 · Tối thiểu 2 đêm',
                'soDemToiThieu' => 2,
                'loaiPhongApDung' => [2, 3],
                'loaiPhongNames' => ['Deluxe', 'Suite An Nhiên'],
                'nguonDat' => 'Tất cả',
                'gioiHanToanChuongTrinh' => 50,
                'luotDaDung' => 9,
                'moiKhachHang' => 1,
                'trangThai' => 'Đang chạy',
                'moTa' => 'Giảm trực tiếp 100.000 đ mỗi đặt phòng khi lưu trú từ 2 đêm trở lên.',
                'created_at' => '2026-11-01 09:00:00',
                'updated_at' => '2026-11-08 10:00:00'
            ],
            [
                'id' => 3,
                'maKhuyenMai' => 'DONGAM',
                'tenKhuyenMai' => 'Chào mùa đông',
                'loaiGiam' => 'Phan_Tram',
                'giaTriGiam' => 15,
                'giamToiDa' => 400000,
                'giaTriDatToiThieu' => 1000000,
                'ngayBatDau' => '2026-12-01 00:00:00',
                'ngayKetThuc' => '2026-12-20 23:59:59',
                'ngayLuuTruApDung' => '01/12–20/12/2026',
                'soDemToiThieu' => 1,
                'loaiPhongApDung' => [],
                'loaiPhongNames' => ['Tất cả loại phòng'],
                'nguonDat' => 'Trực tiếp',
                'gioiHanToanChuongTrinh' => 80,
                'luotDaDung' => 0,
                'moiKhachHang' => 1,
                'trangThai' => 'Sắp chạy',
                'moTa' => 'Chào đón mùa đông ấm áp với mức giảm 15% tối đa 400.000 đ.',
                'created_at' => '2026-11-05 14:00:00',
                'updated_at' => '2026-11-08 10:00:00'
            ],
            [
                'id' => 4,
                'maKhuyenMai' => 'THANG10',
                'tenKhuyenMai' => 'Ưu đãi tháng mười',
                'loaiGiam' => 'Phan_Tram',
                'giaTriGiam' => 10,
                'giamToiDa' => 250000,
                'giaTriDatToiThieu' => 500000,
                'ngayBatDau' => '2026-10-01 00:00:00',
                'ngayKetThuc' => '2026-10-31 23:59:59',
                'ngayLuuTruApDung' => '01/10–31/10/2026',
                'soDemToiThieu' => 1,
                'loaiPhongApDung' => [1], // 1: Superior
                'loaiPhongNames' => ['Superior'],
                'nguonDat' => 'Trực tiếp',
                'gioiHanToanChuongTrinh' => 50,
                'luotDaDung' => 42,
                'moiKhachHang' => 1,
                'trangThai' => 'Hết hạn',
                'moTa' => 'Chương trình tri ân khách hàng tháng 10 đã kết thúc.',
                'created_at' => '2026-09-25 10:00:00',
                'updated_at' => '2026-11-01 00:00:00'
            ]
        ];
    }

    /**
     * Lấy toàn bộ danh sách khuyến mãi từ Cache
     */
    public function getAllPromotions(): array
    {
        $promos = Cache::get(self::CACHE_KEY);
        if (!$promos || !is_array($promos)) {
            $promos = $this->getInitialPromotions();
            Cache::forever(self::CACHE_KEY, $promos);
        }
        return $promos;
    }

    /**
     * Lưu danh sách khuyến mãi vào Cache
     */
    public function saveAllPromotions(array $promotions): void
    {
        Cache::forever(self::CACHE_KEY, array_values($promotions));
    }

    /**
     * Lấy thông tin khuyến mãi đang áp dụng cho một loại phòng cụ thể (dành cho Card loại phòng & Đặt phòng)
     */
    public static function getPromotionInfoForRoom(int $roomTypeId, float $roomPrice): ?array
    {
        $instance = new self();
        $promos = $instance->getAllPromotions();
        $now = Carbon::now();

        $matchedPromo = null;
        $isCurrentlyActive = false;

        // Ưu tiên 1: Tìm chương trình "Đang chạy" và còn trong hạn ngày
        foreach ($promos as $p) {
            $instance->formatPromoDisplay($p);
            $start = Carbon::parse($p['ngayBatDau']);
            $end = Carbon::parse($p['ngayKetThuc']);

            $rooms = $p['loaiPhongApDung'] ?? [];
            $appliesToRoom = empty($rooms) || in_array($roomTypeId, array_map('intval', $rooms));
            if (!$appliesToRoom) {
                continue;
            }

            if ($p['trangThai'] === 'Đang chạy' && $now->gte($start) && $now->lte($end)) {
                $matchedPromo = $p;
                $isCurrentlyActive = true;
                break;
            }
        }

        // Ưu tiên 2: Nếu không có chương trình đang chạy, tìm chương trình mới nhất của phòng này
        // (để Admin có thể xem lại và kích hoạt lại trực tiếp từ Card)
        if (!$matchedPromo) {
            foreach ($promos as $p) {
                $instance->formatPromoDisplay($p);
                $rooms = $p['loaiPhongApDung'] ?? [];
                $appliesToRoom = empty($rooms) || in_array($roomTypeId, array_map('intval', $rooms));
                if ($appliesToRoom) {
                    $matchedPromo = $p;
                    $isCurrentlyActive = false;
                    break;
                }
            }
        }

        if (!$matchedPromo) {
            return null;
        }

        // Tính toán mức giảm và giá sau giảm
        $tienGiamMotDem = 0;
        $mucGiamHienThi = '';

        if ($matchedPromo['loaiGiam'] === 'Phan_Tram') {
            $calculated = ($roomPrice * (float)$matchedPromo['giaTriGiam']) / 100;
            $giamToiDa = (float)($matchedPromo['giamToiDa'] ?? 0);
            if ($giamToiDa > 0 && $calculated > $giamToiDa) {
                $tienGiamMotDem = $giamToiDa;
            } else {
                $tienGiamMotDem = $calculated;
            }
            $mucGiamHienThi = '-' . $matchedPromo['giaTriGiam'] . '%';
        } else {
            $tienGiamMotDem = min((float)$matchedPromo['giaTriGiam'], $roomPrice);
            $mucGiamHienThi = '-' . number_format($matchedPromo['giaTriGiam'], 0, ',', '.') . ' đ';
        }

        $giaSauGiam = max(0, $roomPrice - $tienGiamMotDem);

        return [
            'coKhuyenMai' => $isCurrentlyActive, // Chỉ true khi đang chạy và còn hiệu lực
            'id' => $matchedPromo['id'],
            'maKhuyenMai' => $matchedPromo['maKhuyenMai'],
            'tenKhuyenMai' => $matchedPromo['tenKhuyenMai'],
            'loaiGiam' => $matchedPromo['loaiGiam'],
            'giaTriGiam' => (float)$matchedPromo['giaTriGiam'],
            'giamToiDa' => (float)($matchedPromo['giamToiDa'] ?? 0),
            'giaTriDatToiThieu' => (float)($matchedPromo['giaTriDatToiThieu'] ?? 0),
            'ngayBatDau' => $matchedPromo['ngayBatDau'],
            'ngayKetThuc' => $matchedPromo['ngayKetThuc'],
            'ngayLuuTruApDung' => $matchedPromo['ngayLuuTruApDung'] ?? '',
            'soDemToiThieu' => (int)($matchedPromo['soDemToiThieu'] ?? 1),
            'loaiPhongApDung' => $matchedPromo['loaiPhongApDung'] ?? [],
            'loaiPhongNames' => $matchedPromo['loaiPhongNames'] ?? [],
            'trangThai' => $matchedPromo['trangThai'],
            'mucGiamHienThi' => $mucGiamHienThi,
            'tienGiamMotDem' => (int)$tienGiamMotDem,
            'tienGiamMotDemDinhDang' => number_format($tienGiamMotDem, 0, ',', '.') . ' đ',
            'giaSauGiam' => (int)$giaSauGiam,
            'giaSauGiamDinhDang' => number_format($giaSauGiam, 0, ',', '.') . ' đ',
            'chuongTrinh' => $matchedPromo,
        ];
    }

    /**
     * Định dạng nhãn hiển thị mức giảm & thời hạn
     */
    private function formatPromoDisplay(array &$promo): void
    {
        // 1. Mức giảm hiển thị
        if ($promo['loaiGiam'] === 'Phan_Tram') {
            $maxText = !empty($promo['giamToiDa']) ? ' · Tối đa ' . number_format($promo['giamToiDa'], 0, ',', '.') . ' đ' : '';
            $promo['mucGiamHienThi'] = $promo['giaTriGiam'] . '%' . $maxText;
        } else {
            $promo['mucGiamHienThi'] = number_format($promo['giaTriGiam'], 0, ',', '.') . ' đ / đặt phòng';
        }

        // 2. Thời hạn hiển thị
        $start = Carbon::parse($promo['ngayBatDau']);
        $end = Carbon::parse($promo['ngayKetThuc']);
        $promo['thoiHanHienThi'] = $start->format('d') . '-' . $end->format('d/m/Y');

        // 3. Tự động tính trạng thái nếu không bị cưỡng chế "Tạm dừng"
        if ($promo['trangThai'] !== 'Tạm dừng') {
            $now = Carbon::now();
            if ($now->gt($end)) {
                $promo['trangThai'] = 'Hết hạn';
            } elseif ($now->lt($start)) {
                $promo['trangThai'] = 'Sắp chạy';
            } else {
                $promo['trangThai'] = 'Đang chạy';
            }
        }

        // 4. Lượt dùng hiển thị
        $total = $promo['gioiHanToanChuongTrinh'] ?? 100;
        $used = $promo['luotDaDung'] ?? 0;
        $promo['luotDungHienThi'] = "{$used} / {$total}";
    }

    /**
     * Danh sách khuyến mãi (kèm tìm kiếm, lọc trạng thái và thống kê)
     * GET /api/khuyen-mai
     */
    public function index(Request $request)
    {
        $promos = $this->getAllPromotions();

        // Cập nhật hiển thị và trạng thái
        foreach ($promos as &$p) {
            $this->formatPromoDisplay($p);
        }
        unset($p);

        // Thống kê số lượng theo từng tab
        $thongKe = [
            'tatCa' => count($promos),
            'dangChay' => count(array_filter($promos, fn($p) => $p['trangThai'] === 'Đang chạy')),
            'sapChay' => count(array_filter($promos, fn($p) => $p['trangThai'] === 'Sắp chạy')),
            'hetHan' => count(array_filter($promos, fn($p) => $p['trangThai'] === 'Hết hạn')),
            'tamDung' => count(array_filter($promos, fn($p) => $p['trangThai'] === 'Tạm dừng')),
        ];

        // Lọc theo tìm kiếm từ khóa
        $q = trim($request->query('q', ''));
        if (!empty($q)) {
            $qLower = mb_strtolower($q, 'UTF-8');
            $promos = array_filter($promos, function ($p) use ($qLower) {
                return str_contains(mb_strtolower($p['maKhuyenMai'], 'UTF-8'), $qLower)
                    || str_contains(mb_strtolower($p['tenKhuyenMai'], 'UTF-8'), $qLower);
            });
        }

        // Lọc theo trạng thái
        $trangThai = trim($request->query('trangThai', ''));
        if (!empty($trangThai) && !in_array($trangThai, ['Tất cả', 'Mọi trạng thái'])) {
            $promos = array_filter($promos, fn($p) => $p['trangThai'] === $trangThai);
        }

        return response()->json([
            'status' => 'success',
            'thongKe' => $thongKe,
            'total' => count($promos),
            'data' => array_values($promos)
        ]);
    }

    /**
     * Chi tiết một khuyến mãi
     * GET /api/khuyen-mai/{id}
     */
    public function show($id)
    {
        $promos = $this->getAllPromotions();
        $promo = collect($promos)->first(function ($p) use ($id) {
            return (string)$p['id'] === (string)$id || strtoupper($p['maKhuyenMai']) === strtoupper($id);
        });

        if (!$promo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chương trình khuyến mãi.'
            ], 404);
        }

        $this->formatPromoDisplay($promo);

        return response()->json([
            'status' => 'success',
            'data' => $promo
        ]);
    }

    /**
     * Tạo mới chương trình khuyến mãi (lưu cache/bộ nhớ)
     * POST /api/khuyen-mai
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maKhuyenMai' => 'required|string|max:50',
            'tenKhuyenMai' => 'required|string|max:255',
            'loaiGiam' => 'required|in:Phan_Tram,So_Tien',
            'giaTriGiam' => 'required|numeric|min:1',
            'ngayBatDau' => 'required',
            'ngayKetThuc' => 'required',
        ], [
            'maKhuyenMai.required' => 'Vui lòng nhập mã khuyến mãi.',
            'tenKhuyenMai.required' => 'Vui lòng nhập tên chương trình.',
            'loaiGiam.required' => 'Vui lòng chọn loại giảm giá.',
            'giaTriGiam.required' => 'Vui lòng nhập mức giảm giá hợp lệ.',
            'ngayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'ngayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $promos = $this->getAllPromotions();
        $code = strtoupper(trim($request->input('maKhuyenMai')));

        // Kiểm tra trùng mã
        $exists = collect($promos)->contains(fn($p) => strtoupper($p['maKhuyenMai']) === $code);
        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => "Mã khuyến mãi '{$code}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác."
            ], 422);
        }

        $maxId = count($promos) > 0 ? max(array_column($promos, 'id')) : 0;
        $newId = $maxId + 1;

        // Xử lý loại phòng áp dụng từ database
        $loaiPhongApDung = $request->input('loaiPhongApDung', []);
        if (!is_array($loaiPhongApDung)) {
            $loaiPhongApDung = [];
        }

        $loaiPhongNames = [];
        if (empty($loaiPhongApDung)) {
            $loaiPhongNames = ['Tất cả loại phòng'];
        } else {
            $dbRooms = Loai_Phong::whereIn('id', $loaiPhongApDung)->get();
            $loaiPhongNames = $dbRooms->pluck('tenLoai')->toArray();
        }

        $startStr = Carbon::parse($request->input('ngayBatDau'))->format('Y-m-d H:i:s');
        $endStr = Carbon::parse($request->input('ngayKetThuc'))->format('Y-m-d H:i:s');

        $trangThai = $request->input('trangThai', 'Đang chạy');

        $newPromo = [
            'id' => $newId,
            'maKhuyenMai' => $code,
            'tenKhuyenMai' => trim($request->input('tenKhuyenMai')),
            'loaiGiam' => $request->input('loaiGiam'),
            'giaTriGiam' => (float)$request->input('giaTriGiam'),
            'giamToiDa' => (float)$request->input('giamToiDa', 0),
            'giaTriDatToiThieu' => (float)$request->input('giaTriDatToiThieu', 0),
            'ngayBatDau' => $startStr,
            'ngayKetThuc' => $endStr,
            'ngayLuuTruApDung' => $request->input('ngayLuuTruApDung', Carbon::parse($startStr)->format('d/m') . '–' . Carbon::parse($endStr)->format('d/m/Y')),
            'soDemToiThieu' => (int)$request->input('soDemToiThieu', 1),
            'loaiPhongApDung' => array_map('intval', $loaiPhongApDung),
            'loaiPhongNames' => $loaiPhongNames,
            'nguonDat' => $request->input('nguonDat', 'Trực tiếp'),
            'gioiHanToanChuongTrinh' => (int)$request->input('gioiHanToanChuongTrinh', 100),
            'luotDaDung' => 0,
            'moiKhachHang' => (int)$request->input('moiKhachHang', 1),
            'trangThai' => $trangThai,
            'moTa' => $request->input('moTa', ''),
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];

        $this->formatPromoDisplay($newPromo);
        $promos[] = $newPromo;
        $this->saveAllPromotions($promos);

        return response()->json([
            'status' => 'success',
            'message' => "Tạo chương trình khuyến mãi '{$code}' thành công!",
            'data' => $newPromo
        ], 201);
    }

    /**
     * Cập nhật thông tin chương trình khuyến mãi
     * PUT/POST /api/khuyen-mai/{id}
     */
    public function update(Request $request, $id)
    {
        $promos = $this->getAllPromotions();
        $index = -1;
        foreach ($promos as $k => $p) {
            if ((string)$p['id'] === (string)$id) {
                $index = $k;
                break;
            }
        }

        if ($index === -1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chương trình khuyến mãi cần cập nhật.'
            ], 404);
        }

        $code = strtoupper(trim($request->input('maKhuyenMai', $promos[$index]['maKhuyenMai'])));

        // Kiểm tra nếu đổi mã sang mã khác đã tồn tại
        if ($code !== strtoupper($promos[$index]['maKhuyenMai'])) {
            $exists = collect($promos)->contains(fn($p) => strtoupper($p['maKhuyenMai']) === $code && $p['id'] != $id);
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Mã khuyến mãi '{$code}' đã được sử dụng ở chương trình khác."
                ], 422);
            }
        }

        // Cập nhật loại phòng áp dụng
        if ($request->has('loaiPhongApDung')) {
            $loaiPhongApDung = $request->input('loaiPhongApDung');
            if (!is_array($loaiPhongApDung)) {
                $loaiPhongApDung = [];
            }
            $promos[$index]['loaiPhongApDung'] = array_map('intval', $loaiPhongApDung);
            if (empty($loaiPhongApDung)) {
                $promos[$index]['loaiPhongNames'] = ['Tất cả loại phòng'];
            } else {
                $dbRooms = Loai_Phong::whereIn('id', $loaiPhongApDung)->get();
                $promos[$index]['loaiPhongNames'] = $dbRooms->pluck('tenLoai')->toArray();
            }
        }

        if ($request->has('tenKhuyenMai')) {
            $promos[$index]['tenKhuyenMai'] = trim($request->input('tenKhuyenMai'));
        }
        if ($request->has('loaiGiam')) {
            $promos[$index]['loaiGiam'] = $request->input('loaiGiam');
        }
        if ($request->has('giaTriGiam')) {
            $promos[$index]['giaTriGiam'] = (float)$request->input('giaTriGiam');
        }
        if ($request->has('giamToiDa')) {
            $promos[$index]['giamToiDa'] = (float)$request->input('giamToiDa');
        }
        if ($request->has('giaTriDatToiThieu')) {
            $promos[$index]['giaTriDatToiThieu'] = (float)$request->input('giaTriDatToiThieu');
        }
        if ($request->has('ngayBatDau')) {
            $promos[$index]['ngayBatDau'] = Carbon::parse($request->input('ngayBatDau'))->format('Y-m-d H:i:s');
        }
        if ($request->has('ngayKetThuc')) {
            $promos[$index]['ngayKetThuc'] = Carbon::parse($request->input('ngayKetThuc'))->format('Y-m-d H:i:s');
        }
        if ($request->has('ngayLuuTruApDung')) {
            $promos[$index]['ngayLuuTruApDung'] = $request->input('ngayLuuTruApDung');
        }
        if ($request->has('soDemToiThieu')) {
            $promos[$index]['soDemToiThieu'] = (int)$request->input('soDemToiThieu');
        }
        if ($request->has('gioiHanToanChuongTrinh')) {
            $promos[$index]['gioiHanToanChuongTrinh'] = (int)$request->input('gioiHanToanChuongTrinh');
        }
        if ($request->has('moiKhachHang')) {
            $promos[$index]['moiKhachHang'] = (int)$request->input('moiKhachHang');
        }
        if ($request->has('trangThai')) {
            $promos[$index]['trangThai'] = $request->input('trangThai');
        }
        if ($request->has('moTa')) {
            $promos[$index]['moTa'] = $request->input('moTa');
        }

        $promos[$index]['maKhuyenMai'] = $code;
        $promos[$index]['updated_at'] = Carbon::now()->toDateTimeString();

        $this->formatPromoDisplay($promos[$index]);
        $this->saveAllPromotions($promos);

        return response()->json([
            'status' => 'success',
            'message' => "Cập nhật khuyến mãi '{$code}' thành công!",
            'data' => $promos[$index]
        ]);
    }

    /**
     * Bật / Tắt trạng thái khuyến mãi (Đang chạy <-> Tạm dừng)
     * POST /api/khuyen-mai/{id}/toggle
     */
    public function toggle($id)
    {
        $promos = $this->getAllPromotions();
        $index = -1;
        foreach ($promos as $k => $p) {
            if ((string)$p['id'] === (string)$id) {
                $index = $k;
                break;
            }
        }

        if ($index === -1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chương trình khuyến mãi.'
            ], 404);
        }

        $currentStatus = $promos[$index]['trangThai'];
        if ($currentStatus === 'Tạm dừng') {
            $promos[$index]['trangThai'] = 'Đang chạy';
            $msg = "Đã kích hoạt lại chương trình khuyến mãi '{$promos[$index]['maKhuyenMai']}'.";
        } else {
            $promos[$index]['trangThai'] = 'Tạm dừng';
            $msg = "Đã tạm dừng chương trình khuyến mãi '{$promos[$index]['maKhuyenMai']}'. Khách hàng sẽ không thể áp dụng mã này.";
        }

        $promos[$index]['updated_at'] = Carbon::now()->toDateTimeString();
        $this->formatPromoDisplay($promos[$index]);
        $this->saveAllPromotions($promos);

        return response()->json([
            'status' => 'success',
            'message' => $msg,
            'data' => $promos[$index]
        ]);
    }

    /**
     * Lấy thông tin khuyến mãi đang áp dụng cho 1 loại phòng
     * GET /api/khuyen-mai/loai-phong/{id}
     */
    public function getKhuyenMaiTheoLoaiPhong($id)
    {
        $lp = Loai_Phong::find((int)$id);
        if (!$lp) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy loại phòng.'
            ], 404);
        }

        $info = self::getPromotionInfoForRoom((int)$lp->id, (float)$lp->giaTieuChuan);

        return response()->json([
            'status' => 'success',
            'data' => $info,
            'loaiPhong' => [
                'id' => $lp->id,
                'tenLoai' => $lp->tenLoai,
                'giaTieuChuan' => (int)$lp->giaTieuChuan,
                'giaDinhDang' => number_format($lp->giaTieuChuan, 0, ',', '.') . ' đ',
            ]
        ]);
    }

    /**
     * Thiết lập hoặc cập nhật nhanh khuyến mãi cho loại phòng từ Card
     * POST /api/khuyen-mai/thiet-lap-loai-phong
     */
    public function thietLapKhuyenMaiLoaiPhong(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maLoaiPhong' => 'required|integer',
            'maKhuyenMai' => 'required|string|max:50',
            'tenKhuyenMai' => 'required|string|max:255',
            'loaiGiam' => 'required|in:Phan_Tram,So_Tien',
            'giaTriGiam' => 'required|numeric|min:1',
            'ngayBatDau' => 'required',
            'ngayKetThuc' => 'required',
        ], [
            'maKhuyenMai.required' => 'Vui lòng nhập mã khuyến mãi.',
            'tenKhuyenMai.required' => 'Vui lòng nhập tên chương trình.',
            'loaiGiam.required' => 'Vui lòng chọn loại giảm giá.',
            'giaTriGiam.required' => 'Vui lòng nhập mức giảm hợp lệ.',
            'ngayBatDau.required' => 'Vui lòng chọn ngày bắt đầu.',
            'ngayKetThuc.required' => 'Vui lòng chọn ngày kết thúc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422);
        }

        $maLoaiPhong = (int)$request->input('maLoaiPhong');
        $loaiPhong = Loai_Phong::find($maLoaiPhong);
        if (!$loaiPhong) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy loại phòng.'
            ], 404);
        }

        $promos = $this->getAllPromotions();
        $code = strtoupper(trim($request->input('maKhuyenMai')));
        $promoId = $request->input('promoId');

        $targetIndex = -1;

        // 1. Tìm theo promoId nếu có truyền
        if (!empty($promoId)) {
            foreach ($promos as $k => $p) {
                if ((string)$p['id'] === (string)$promoId) {
                    $targetIndex = $k;
                    break;
                }
            }
        }

        // 2. Nếu chưa tìm thấy, tìm theo mã code
        if ($targetIndex === -1) {
            foreach ($promos as $k => $p) {
                if (strtoupper($p['maKhuyenMai']) === $code) {
                    $targetIndex = $k;
                    break;
                }
            }
        }

        // TRƯỜNG HỢP A: CẬP NHẬT CHƯƠNG TRÌNH ĐANG CÓ
        if ($targetIndex !== -1) {
            // Đảm bảo không trùng mã với chương trình khác
            if ($code !== strtoupper($promos[$targetIndex]['maKhuyenMai'])) {
                $existsOther = collect($promos)->contains(fn($p) => strtoupper($p['maKhuyenMai']) === $code && $p['id'] != $promos[$targetIndex]['id']);
                if ($existsOther) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Mã '{$code}' đã được sử dụng ở chương trình khác."
                    ], 422);
                }
            }

            // Giữ lại các phòng khác nếu chương trình này áp dụng cho nhiều phòng
            $existingRooms = $promos[$targetIndex]['loaiPhongApDung'] ?? [];
            if (!in_array($maLoaiPhong, $existingRooms)) {
                $existingRooms[] = $maLoaiPhong;
            }
            $promos[$targetIndex]['loaiPhongApDung'] = array_values(array_unique(array_map('intval', $existingRooms)));

            $dbRooms = Loai_Phong::whereIn('id', $promos[$targetIndex]['loaiPhongApDung'])->get();
            $promos[$targetIndex]['loaiPhongNames'] = $dbRooms->pluck('tenLoai')->toArray();

            $promos[$targetIndex]['maKhuyenMai'] = $code;
            $promos[$targetIndex]['tenKhuyenMai'] = trim($request->input('tenKhuyenMai'));
            $promos[$targetIndex]['loaiGiam'] = $request->input('loaiGiam');
            $promos[$targetIndex]['giaTriGiam'] = (float)$request->input('giaTriGiam');
            $promos[$targetIndex]['giamToiDa'] = (float)$request->input('giamToiDa', 0);
            $promos[$targetIndex]['giaTriDatToiThieu'] = (float)$request->input('giaTriDatToiThieu', 0);
            $promos[$targetIndex]['ngayBatDau'] = Carbon::parse($request->input('ngayBatDau'))->format('Y-m-d H:i:s');
            $promos[$targetIndex]['ngayKetThuc'] = Carbon::parse($request->input('ngayKetThuc'))->format('Y-m-d H:i:s');
            $promos[$targetIndex]['trangThai'] = $request->input('trangThai', 'Đang chạy');
            $promos[$targetIndex]['updated_at'] = Carbon::now()->toDateTimeString();

            $this->formatPromoDisplay($promos[$targetIndex]);
            $this->saveAllPromotions($promos);

            $promoInfo = self::getPromotionInfoForRoom($maLoaiPhong, (float)$loaiPhong->giaTieuChuan);

            return response()->json([
                'status' => 'success',
                'message' => "Cập nhật khuyến mãi '{$code}' cho loại phòng {$loaiPhong->tenLoai} thành công!",
                'data' => $promos[$targetIndex],
                'khuyenMai' => $promoInfo
            ]);
        }

        // TRƯỜNG HỢP B: TẠO MỚI CHƯƠNG TRÌNH CHO LOẠI PHÒNG NÀY
        $existsCode = collect($promos)->contains(fn($p) => strtoupper($p['maKhuyenMai']) === $code);
        if ($existsCode) {
            return response()->json([
                'status' => 'error',
                'message' => "Mã khuyến mãi '{$code}' đã được sử dụng. Vui lòng chọn mã khác."
            ], 422);
        }

        $maxId = count($promos) > 0 ? max(array_column($promos, 'id')) : 0;
        $newId = $maxId + 1;

        $startStr = Carbon::parse($request->input('ngayBatDau'))->format('Y-m-d H:i:s');
        $endStr = Carbon::parse($request->input('ngayKetThuc'))->format('Y-m-d H:i:s');

        $newPromo = [
            'id' => $newId,
            'maKhuyenMai' => $code,
            'tenKhuyenMai' => trim($request->input('tenKhuyenMai')),
            'loaiGiam' => $request->input('loaiGiam'),
            'giaTriGiam' => (float)$request->input('giaTriGiam'),
            'giamToiDa' => (float)$request->input('giamToiDa', 0),
            'giaTriDatToiThieu' => (float)$request->input('giaTriDatToiThieu', 0),
            'ngayBatDau' => $startStr,
            'ngayKetThuc' => $endStr,
            'ngayLuuTruApDung' => $request->input('ngayLuuTruApDung', Carbon::parse($startStr)->format('d/m') . '–' . Carbon::parse($endStr)->format('d/m/Y')),
            'soDemToiThieu' => (int)$request->input('soDemToiThieu', 1),
            'loaiPhongApDung' => [$maLoaiPhong],
            'loaiPhongNames' => [$loaiPhong->tenLoai],
            'nguonDat' => 'Trực tiếp',
            'gioiHanToanChuongTrinh' => (int)$request->input('gioiHanToanChuongTrinh', 100),
            'luotDaDung' => 0,
            'moiKhachHang' => 1,
            'trangThai' => $request->input('trangThai', 'Đang chạy'),
            'moTa' => "Chương trình ưu đãi cho phòng {$loaiPhong->tenLoai}",
            'created_at' => Carbon::now()->toDateTimeString(),
            'updated_at' => Carbon::now()->toDateTimeString()
        ];

        $this->formatPromoDisplay($newPromo);
        $promos[] = $newPromo;
        $this->saveAllPromotions($promos);

        $promoInfo = self::getPromotionInfoForRoom($maLoaiPhong, (float)$loaiPhong->giaTieuChuan);

        return response()->json([
            'status' => 'success',
            'message' => "Thiết lập chương trình khuyến mãi '{$code}' cho loại phòng {$loaiPhong->tenLoai} thành công!",
            'data' => $newPromo,
            'khuyenMai' => $promoInfo
        ], 201);
    }

    /**
     * Xóa khuyến mãi (khỏi bộ nhớ cache)
     * DELETE /api/khuyen-mai/{id}
     */
    public function destroy($id)
    {
        $promos = $this->getAllPromotions();
        $promoToDelete = null;

        $newPromos = [];
        foreach ($promos as $p) {
            if ((string)$p['id'] === (string)$id) {
                $promoToDelete = $p;
            } else {
                $newPromos[] = $p;
            }
        }

        if (!$promoToDelete) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy chương trình khuyến mãi để xóa.'
            ], 404);
        }

        $this->saveAllPromotions($newPromos);

        return response()->json([
            'status' => 'success',
            'message' => "Đã xóa chương trình khuyến mãi '{$promoToDelete['maKhuyenMai']}' thành công!"
        ]);
    }

    /**
     * KIỂM TRA MÃ KHUYẾN MÃI DÀNH CHO KHÁCH HÀNG / CHECKOUT / TÍNH TIỀN
     * POST /api/khuyen-mai/kiem-tra
     *
     * Request body:
     * - maKhuyenMai: string (bắt buộc)
     * - maLoaiPhong: int | string (ID loại phòng hoặc tên loại phòng khách đặt)
     * - soDem: int (số đêm lưu trú, mặc định 1)
     * - giaPhongMotDem: float (giá phòng 1 đêm, nếu không truyền sẽ lấy từ DB Loai_Phong)
     * - ngayNhanPhong: string (Y-m-d)
     * - ngayTraPhong: string (Y-m-d)
     */
    public function kiemTra(Request $request)
    {
        $rawCode = trim($request->input('maKhuyenMai', ''));
        if (empty($rawCode)) {
            return response()->json([
                'hopLe' => false,
                'message' => 'Vui lòng nhập mã khuyến mãi.'
            ], 400);
        }

        $code = strtoupper($rawCode);
        $promos = $this->getAllPromotions();

        $promo = collect($promos)->first(fn($p) => strtoupper($p['maKhuyenMai']) === $code);

        // 1. Kiểm tra tồn tại
        if (!$promo) {
            return response()->json([
                'hopLe' => false,
                'message' => "Mã khuyến mãi '{$code}' không tồn tại hoặc đã bị xóa."
            ], 404);
        }

        $this->formatPromoDisplay($promo);

        // 2. Kiểm tra nếu đang bị Tạm dừng
        if ($promo['trangThai'] === 'Tạm dừng') {
            return response()->json([
                'hopLe' => false,
                'message' => "Chương trình khuyến mãi '{$code}' hiện đang tạm dừng kích hoạt."
            ], 400);
        }

        // 3. Kiểm tra ngày hiệu lực
        $now = Carbon::now();
        $start = Carbon::parse($promo['ngayBatDau']);
        $end = Carbon::parse($promo['ngayKetThuc']);

        if ($now->gt($end) || $promo['trangThai'] === 'Hết hạn') {
            return response()->json([
                'hopLe' => false,
                'message' => "Mã khuyến mãi '{$code}' đã hết hạn sử dụng vào ngày " . $end->format('d/m/Y') . "."
            ], 400);
        }

        if ($now->lt($start) || $promo['trangThai'] === 'Sắp chạy') {
            return response()->json([
                'hopLe' => false,
                'message' => "Mã khuyến mãi '{$code}' chưa đến thời gian áp dụng (Bắt đầu từ ngày " . $start->format('d/m/Y') . ")."
            ], 400);
        }

        // 4. Lấy thông tin loại phòng khách chọn
        $maLoaiPhong = $request->input('maLoaiPhong');
        $loaiPhong = null;

        if (!empty($maLoaiPhong)) {
            if (is_numeric($maLoaiPhong)) {
                $loaiPhong = Loai_Phong::find((int)$maLoaiPhong);
            } else {
                $loaiPhong = Loai_Phong::where('tenLoai', 'like', "%{$maLoaiPhong}%")->first();
            }
        }

        // 5. Kiểm tra loại phòng có được áp dụng khuyến mãi không
        $applicableRooms = $promo['loaiPhongApDung'] ?? [];
        if (!empty($applicableRooms) && is_array($applicableRooms) && count($applicableRooms) > 0) {
            if (!$loaiPhong) {
                return response()->json([
                    'hopLe' => false,
                    'message' => 'Vui lòng chọn loại phòng để kiểm tra điều kiện áp dụng khuyến mãi.'
                ], 400);
            }

            if (!in_array((int)$loaiPhong->id, array_map('intval', $applicableRooms))) {
                $names = !empty($promo['loaiPhongNames']) ? implode(', ', $promo['loaiPhongNames']) : 'một số phòng nhất định';
                return response()->json([
                    'hopLe' => false,
                    'message' => "Mã khuyến mãi '{$code}' chỉ áp dụng cho: {$names}. Loại phòng bạn chọn ({$loaiPhong->tenLoai}) không thuộc chương trình này."
                ], 400);
            }
        }

        // 6. Tính toán giá tiền
        $soDem = max(1, (int)$request->input('soDem', 1));
        $giaMotDem = 0;

        if ($request->has('giaPhongMotDem') && (float)$request->input('giaPhongMotDem') > 0) {
            $giaMotDem = (float)$request->input('giaPhongMotDem');
        } elseif ($loaiPhong) {
            $giaMotDem = (float)$loaiPhong->giaTieuChuan;
        } else {
            $giaMotDem = 900000; // Mặc định Deluxe mẫu nếu không xác định
        }

        // Kiểm tra số đêm tối thiểu
        $soDemToiThieu = (int)($promo['soDemToiThieu'] ?? 1);
        if ($soDem < $soDemToiThieu) {
            return response()->json([
                'hopLe' => false,
                'message' => "Chương trình '{$code}' yêu cầu đặt tối thiểu {$soDemToiThieu} đêm. Bạn đang đặt {$soDem} đêm."
            ], 400);
        }

        $tongTienGoc = $giaMotDem * $soDem;

        // Kiểm tra giá trị đặt tối thiểu
        $minTotal = (float)($promo['giaTriDatToiThieu'] ?? 0);
        if ($minTotal > 0 && $tongTienGoc < $minTotal) {
            return response()->json([
                'hopLe' => false,
                'message' => "Tổng tiền phòng (" . number_format($tongTienGoc, 0, ',', '.') . " đ) chưa đạt giá trị đặt tối thiểu (" . number_format($minTotal, 0, ',', '.') . " đ) của chương trình này."
            ], 400);
        }

        // 7. Tính số tiền giảm giá
        $tienGiam = 0;
        $dienGiai = '';

        if ($promo['loaiGiam'] === 'Phan_Tram') {
            $percent = (float)$promo['giaTriGiam'];
            $calculatedDiscount = ($tongTienGoc * $percent) / 100;
            $giamToiDa = (float)($promo['giamToiDa'] ?? 0);

            if ($giamToiDa > 0 && $calculatedDiscount > $giamToiDa) {
                $tienGiam = $giamToiDa;
                $dienGiai = "Giảm {$percent}% (tính ra " . number_format($calculatedDiscount, 0, ',', '.') . " đ, áp dụng mức giảm tối đa: " . number_format($giamToiDa, 0, ',', '.') . " đ)";
            } else {
                $tienGiam = $calculatedDiscount;
                $dienGiai = "Giảm {$percent}% giá trị tiền phòng (" . number_format($tienGiam, 0, ',', '.') . " đ)";
            }
        } else {
            $tienGiam = min((float)$promo['giaTriGiam'], $tongTienGoc);
            $dienGiai = "Giảm cố định " . number_format($tienGiam, 0, ',', '.') . " đ trên đơn đặt phòng";
        }

        $tongSauGiam = max(0, $tongTienGoc - $tienGiam);

        return response()->json([
            'hopLe' => true,
            'message' => "Áp dụng thành công mã '{$code}': {$dienGiai}.",
            'khuyenMai' => [
                'id' => $promo['id'],
                'maKhuyenMai' => $promo['maKhuyenMai'],
                'tenKhuyenMai' => $promo['tenKhuyenMai'],
                'loaiGiam' => $promo['loaiGiam'],
                'giaTriGiam' => $promo['giaTriGiam'],
                'giamToiDa' => $promo['giamToiDa'],
                'mucGiamHienThi' => $promo['mucGiamHienThi']
            ],
            'chiTietTinh' => [
                'tenLoaiPhong' => $loaiPhong ? $loaiPhong->tenLoai : 'Phòng tiêu chuẩn',
                'soDem' => $soDem,
                'giaMotDem' => $giaMotDem,
                'giaMotDemDinhDang' => number_format($giaMotDem, 0, ',', '.') . ' đ',
                'tongTienGoc' => $tongTienGoc,
                'tongTienGocDinhDang' => number_format($tongTienGoc, 0, ',', '.') . ' đ',
                'tienGiam' => $tienGiam,
                'tienGiamDinhDang' => number_format($tienGiam, 0, ',', '.') . ' đ',
                'tongSauGiam' => $tongSauGiam,
                'tongSauGiamDinhDang' => number_format($tongSauGiam, 0, ',', '.') . ' đ',
                'dienGiai' => $dienGiai,
            ]
        ]);
    }

    /**
     * Xem trước cách tính linh hoạt cho Widget xem trước bên trái
     * POST /api/khuyen-mai/xem-truoc
     */
    public function xemTruoc(Request $request)
    {
        return $this->kiemTra($request);
    }

    /**
     * Thanh toán hóa đơn thực tế có áp dụng khuyến mãi
     * POST /api/khuyen-mai/thanh-toan-hoa-don
     */
    public function thanhToanHoaDon(Request $request)
    {
        $kiemTraRes = $this->kiemTra($request);
        $data = $kiemTraRes->getData(true);

        if (!isset($data['hopLe']) || !$data['hopLe']) {
            return $kiemTraRes;
        }

        $calc = $data['chiTietTinh'];
        $promo = $data['khuyenMai'];

        // Lưu vào Hoa_Don và Chi_Tiet_Hoa_Don trong SQLite
        DB::beginTransaction();
        try {
            $maLuuTru = (int)$request->input('maLuuTru', 1);
            $ngayLap = Carbon::now()->format('Y-m-d');

            $hoaDon = Hoa_Don::create([
                'maLuuTru' => $maLuuTru,
                'ngayLap' => $ngayLap,
                'tongTien' => $calc['tongSauGiam'],
                'trangThai' => 'Da_Thanh_Toan',
            ]);

            // Dòng 1: Tiền phòng gốc
            Chi_Tiet_Hoa_Don::create([
                'maHoaDon' => $hoaDon->id,
                'noiDung' => "Tiền phòng: {$calc['tenLoaiPhong']} ({$calc['soDem']} đêm)",
                'soLuong' => $calc['soDem'],
                'donGia' => $calc['giaMotDem'],
                'thanhTien' => $calc['tongTienGoc'],
            ]);

            // Dòng 2: Chiết khấu khuyến mãi (nếu có giảm)
            if ($calc['tienGiam'] > 0) {
                Chi_Tiet_Hoa_Don::create([
                    'maHoaDon' => $hoaDon->id,
                    'noiDung' => "Ưu đãi khuyến mãi [{$promo['maKhuyenMai']}]: {$calc['dienGiai']}",
                    'soLuong' => 1,
                    'donGia' => -$calc['tienGiam'],
                    'thanhTien' => -$calc['tienGiam'],
                ]);
            }

            // Tăng lượt dùng của khuyến mãi
            $promos = $this->getAllPromotions();
            foreach ($promos as &$p) {
                if (strtoupper($p['maKhuyenMai']) === strtoupper($promo['maKhuyenMai'])) {
                    $p['luotDaDung'] = ($p['luotDaDung'] ?? 0) + 1;
                    break;
                }
            }
            $this->saveAllPromotions($promos);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Thanh toán hóa đơn #HD-{$hoaDon->id} thành công với tổng số tiền " . number_format($calc['tongSauGiam'], 0, ',', '.') . " đ.",
                'hoaDon' => $hoaDon,
                'chiTiet' => [
                    'giaGoc' => $calc['tongTienGoc'],
                    'tienGiam' => $calc['tienGiam'],
                    'tongThanhToan' => $calc['tongSauGiam'],
                    'maKhuyenMai' => $promo['maKhuyenMai']
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi tạo hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }
}
