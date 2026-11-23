<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Dat_Phong;
use App\Models\Hoa_Don;
use App\Models\Loai_Phong;
use App\Models\Luu_Tru;
use App\Models\Phong;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use ApiResponseTrait;

    /**
     * Báo cáo Dashboard tổng quan tích hợp: Doanh thu, Đặt phòng & Công suất (UC19 / UC26 / FR26).
     *
     * @param ReportFilterRequest $request
     * @return JsonResponse
     */
    public function dashboard(ReportFilterRequest $request): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->input('tuNgay'))->startOfDay();
            $endDate = Carbon::parse($request->input('denNgay'))->endOfDay();
            $nhomTheo = $request->input('nhomTheo', 'ngay');
            $maLoaiPhong = $request->filled('maLoaiPhong') ? (int) $request->input('maLoaiPhong') : null;

            $revenueData = $this->getRevenueData($startDate, $endDate, $nhomTheo);
            $bookingsData = $this->getBookingsData($startDate, $endDate, $nhomTheo);
            $occupancyData = $this->getOccupancyData($startDate, $endDate, $maLoaiPhong);

            $hasData = ($revenueData['tongDoanhThu'] > 0)
                || ($bookingsData['tongDonDat'] > 0)
                || ($occupancyData['tongPhongNgayCoKhach'] > 0);

            $message = $hasData
                ? 'Lấy dữ liệu báo cáo thống kê thành công.'
                : 'Không có dữ liệu phát sinh trong khoảng thời gian này.';

            $payload = [
                'kpi' => [
                    'tongDoanhThu'      => $revenueData['tongDoanhThu'],
                    'tongDonDat'        => $bookingsData['tongDonDat'],
                    'tongDonThanhCong'  => $bookingsData['donThanhCong'],
                    'tongDonHuy'        => $bookingsData['donHuy'],
                    'tyLeHuy'           => $bookingsData['tyLeHuy'],
                    'tongLuotKhach'     => $bookingsData['tongLuotKhach'],
                    'congSuatTrungBinh' => $occupancyData['toanKhachSan'],
                ],
                'doanhThu' => [
                    'tong'      => $revenueData['tongDoanhThu'],
                    'soHoaDon'  => $revenueData['soHoaDon'],
                    'nguonThu'  => $revenueData['nguonThu'],
                    'timeline'  => $revenueData['timeline'],
                ],
                'datPhong' => [
                    'tong'          => $bookingsData['tongDonDat'],
                    'soLuotLuuTru'  => $bookingsData['soLuotLuuTru'],
                    'coCauTrangThai' => [
                        'thanhCong' => $bookingsData['donThanhCong'],
                        'huy'       => $bookingsData['donHuy'],
                        'cho'       => $bookingsData['donCho'],
                    ],
                    'timeline'      => $bookingsData['timeline'],
                ],
                'congSuat' => [
                    'soNgayTrongKy'        => $occupancyData['soNgayTrongKy'],
                    'soPhongKhaDung'       => $occupancyData['soPhongKhaDung'],
                    'tongPhongNgayKhaDung' => $occupancyData['tongPhongNgayKhaDung'],
                    'tongPhongNgayCoKhach' => $occupancyData['tongPhongNgayCoKhach'],
                    'toanKhachSan'         => $occupancyData['toanKhachSan'],
                    'theoHangPhong'        => $occupancyData['theoHangPhong'],
                ],
            ];

            return $this->successResponse($payload, $message, 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tổng hợp báo cáo dashboard (UC19): ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi tổng hợp báo cáo.', 500);
        }
    }

    /**
     * Báo cáo Thống kê Doanh thu theo ngày/tháng/năm (UC19 - BR04.6).
     * Chỉ tính từ các hóa đơn có trạng thái "Đã thanh toán".
     *
     * @param ReportFilterRequest $request
     * @return JsonResponse
     */
    public function revenue(ReportFilterRequest $request): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->input('tuNgay'))->startOfDay();
            $endDate = Carbon::parse($request->input('denNgay'))->endOfDay();
            $nhomTheo = $request->input('nhomTheo', 'ngay');

            $data = $this->getRevenueData($startDate, $endDate, $nhomTheo);

            $message = $data['soHoaDon'] > 0
                ? 'Lấy báo cáo doanh thu thành công.'
                : 'Không có dữ liệu phát sinh trong khoảng thời gian này.';

            return $this->successResponse($data, $message, 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi lấy báo cáo doanh thu (UC19): ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi tổng hợp báo cáo.', 500);
        }
    }

    /**
     * Báo cáo Thống kê Tình hình Đặt phòng & Lưu trú (UC19).
     *
     * @param ReportFilterRequest $request
     * @return JsonResponse
     */
    public function bookings(ReportFilterRequest $request): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->input('tuNgay'))->startOfDay();
            $endDate = Carbon::parse($request->input('denNgay'))->endOfDay();
            $nhomTheo = $request->input('nhomTheo', 'ngay');

            $data = $this->getBookingsData($startDate, $endDate, $nhomTheo);

            $message = ($data['tongDonDat'] > 0 || $data['soLuotLuuTru'] > 0)
                ? 'Lấy báo cáo đặt phòng và lưu trú thành công.'
                : 'Không có dữ liệu phát sinh trong khoảng thời gian này.';

            return $this->successResponse($data, $message, 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi lấy báo cáo đặt phòng (UC19): ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi tổng hợp báo cáo.', 500);
        }
    }

    /**
     * Báo cáo Thống kê Công suất Sử dụng Phòng theo hạng phòng và toàn khách sạn (UC19).
     *
     * @param ReportFilterRequest $request
     * @return JsonResponse
     */
    public function occupancy(ReportFilterRequest $request): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->input('tuNgay'))->startOfDay();
            $endDate = Carbon::parse($request->input('denNgay'))->endOfDay();
            $maLoaiPhong = $request->filled('maLoaiPhong') ? (int) $request->input('maLoaiPhong') : null;

            $data = $this->getOccupancyData($startDate, $endDate, $maLoaiPhong);

            $message = $data['tongPhongNgayCoKhach'] > 0
                ? 'Lấy báo cáo công suất phòng thành công.'
                : 'Không có dữ liệu phát sinh trong khoảng thời gian này.';

            return $this->successResponse($data, $message, 200);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi lấy báo cáo công suất phòng (UC19): ' . $e->getMessage(), ['exception' => $e]);
            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi tổng hợp báo cáo.', 500);
        }
    }

    // ==========================================
    // CÁC HÀM XỬ LÝ AGGREGATE DỮ LIỆU ĐỘC LẬP
    // ==========================================

    /**
     * Tổng hợp dữ liệu Doanh thu (Chỉ lấy hóa đơn "Đã thanh toán").
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $nhomTheo
     * @return array<string, mixed>
     */
    protected function getRevenueData(Carbon $startDate, Carbon $endDate, string $nhomTheo): array
    {
        $invoices = Hoa_Don::with('chiTietHoaDons')
            ->where('trangThai', 'Đã thanh toán')
            ->whereBetween('ngayLap', [$startDate, $endDate])
            ->orderBy('ngayLap')
            ->get();

        $tongDoanhThu = (int) $invoices->sum('tongTien');
        $soHoaDon = $invoices->count();

        $tienPhong = 0;
        $dichVu = 0;
        $minibar = 0;
        $phuThu = 0;
        $khac = 0;

        foreach ($invoices as $inv) {
            foreach ($inv->chiTietHoaDons as $ct) {
                $noiDung = mb_strtolower($ct->noiDung ?? '');
                $thanhTien = (int) $ct->thanhTien;

                if (str_contains($noiDung, 'phụ thu') || str_contains($noiDung, 'sớm') || str_contains($noiDung, 'trễ')) {
                    $phuThu += $thanhTien;
                } elseif (str_contains($noiDung, 'dịch vụ')) {
                    $dichVu += $thanhTien;
                } elseif (str_contains($noiDung, 'minibar')) {
                    $minibar += $thanhTien;
                } elseif (str_contains($noiDung, 'tiền phòng') || str_contains($noiDung, 'giá phòng') || str_contains($noiDung, 'phòng')) {
                    $tienPhong += $thanhTien;
                } else {
                    $khac += $thanhTien;
                }
            }
        }

        $format = match ($nhomTheo) {
            'thang' => 'Y-m',
            'nam'   => 'Y',
            default => 'Y-m-d',
        };

        $grouped = $invoices->groupBy(function ($inv) use ($format) {
            return Carbon::parse($inv->ngayLap)->format($format);
        });

        $timeline = [];
        foreach ($grouped as $key => $items) {
            $timeline[] = [
                'thoiGian' => (string) $key,
                'doanhThu' => (int) $items->sum('tongTien'),
                'soHoaDon' => (int) $items->count(),
            ];
        }

        return [
            'tongDoanhThu' => $tongDoanhThu,
            'soHoaDon'     => $soHoaDon,
            'nguonThu'     => [
                'tienPhong' => $tienPhong,
                'dichVu'    => $dichVu,
                'minibar'   => $minibar,
                'phuThu'    => $phuThu,
                'khac'      => $khac,
            ],
            'timeline'     => $timeline,
        ];
    }

    /**
     * Tổng hợp dữ liệu Đặt phòng & Lượt lưu trú.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $nhomTheo
     * @return array<string, mixed>
     */
    protected function getBookingsData(Carbon $startDate, Carbon $endDate, string $nhomTheo): array
    {
        $bookings = Dat_Phong::with('chiTietDatPhong')
            ->whereBetween('ngayDat', [$startDate, $endDate])
            ->orderBy('ngayDat')
            ->get();

        $tongDonDat = $bookings->count();
        $donThanhCong = $bookings->whereIn('trangThai', ['Đã trả', 'Đang ở', 'Đã xác nhận', 'Đã cọc', 'Hoàn thành'])->count();
        $donHuy = $bookings->where('trangThai', 'Đã hủy')->count();
        $donCho = $bookings->whereIn('trangThai', ['Chờ xác nhận', 'Chờ thanh toán', 'Tạo mới'])->count();
        $tyLeHuy = $tongDonDat > 0 ? round(($donHuy / $tongDonDat) * 100, 2) : 0.0;

        // Đợt lưu trú và số lượt khách phát sinh/hoạt động trong kỳ
        $stays = Luu_Tru::with('chiTietDatPhong')
            ->where('thoiGianNhan', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->where('thoiGianTra', '>=', $startDate)
                  ->orWhere('trangThai', 'Đang ở');
            })->get();

        $soLuotLuuTru = $stays->count();
        $tongLuotKhach = (int) $stays->sum(fn($s) => (int) ($s->chiTietDatPhong?->soLuongKhach ?? 1));

        $format = match ($nhomTheo) {
            'thang' => 'Y-m',
            'nam'   => 'Y',
            default => 'Y-m-d',
        };

        $grouped = $bookings->groupBy(fn($b) => Carbon::parse($b->ngayDat)->format($format));
        $timeline = [];
        foreach ($grouped as $key => $items) {
            $timeline[] = [
                'thoiGian'       => (string) $key,
                'soDonDat'       => (int) $items->count(),
                'soDonThanhCong' => (int) $items->whereIn('trangThai', ['Đã trả', 'Đang ở', 'Đã xác nhận', 'Đã cọc', 'Hoàn thành'])->count(),
                'soDonHuy'       => (int) $items->where('trangThai', 'Đã hủy')->count(),
            ];
        }

        return [
            'tongDonDat'     => $tongDonDat,
            'donThanhCong'   => $donThanhCong,
            'donHuy'         => $donHuy,
            'donCho'         => $donCho,
            'tyLeHuy'        => $tyLeHuy,
            'soLuotLuuTru'   => $soLuotLuuTru,
            'tongLuotKhach'  => $tongLuotKhach,
            'timeline'       => $timeline,
        ];
    }

    /**
     * Tổng hợp dữ liệu Công suất sử dụng phòng (Tỷ lệ lấp đầy).
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $maLoaiPhong
     * @return array<string, mixed>
     */
    protected function getOccupancyData(Carbon $startDate, Carbon $endDate, ?int $maLoaiPhong = null): array
    {
        $soNgay = (int) Carbon::parse($startDate->toDateString())->diffInDays(Carbon::parse($endDate->toDateString())) + 1;

        $roomTypeQuery = Loai_Phong::with(['phong' => function ($q) {
            $q->where('trangThai', '!=', 'Ngưng khai thác');
        }]);

        if ($maLoaiPhong) {
            $roomTypeQuery->where('id', $maLoaiPhong);
        }

        $roomTypes = $roomTypeQuery->get();

        $allActiveRoomsQuery = Phong::where('trangThai', '!=', 'Ngưng khai thác');
        if ($maLoaiPhong) {
            $allActiveRoomsQuery->where('maLoaiPhong', $maLoaiPhong);
        }
        $allActiveRooms = $allActiveRoomsQuery->get();

        $soPhongKhaDung = $allActiveRooms->count();
        $tongPhongNgayKhaDung = $soPhongKhaDung * $soNgay;

        // Lấy tất cả đợt lưu trú giao thoa với kỳ báo cáo
        $stays = Luu_Tru::with('chiTietDatPhong')
            ->where('thoiGianNhan', '<=', $endDate)
            ->where(function ($q) use ($startDate) {
                $q->where('thoiGianTra', '>=', $startDate)
                  ->orWhere('trangThai', 'Đang ở');
            })->get();

        $tongPhongNgayCoKhach = 0;
        $roomOccupiedDaysMap = [];

        // Duyệt từng ngày trong khoảng thời gian để tính số ngày phòng có khách chuẩn xác
        $days = [];
        $currentDay = $startDate->copy()->startOfDay();
        $lastDay = $endDate->copy()->startOfDay();
        while ($currentDay->lessThanOrEqualTo($lastDay)) {
            $days[] = $currentDay->format('Y-m-d');
            $currentDay->addDay();
        }

        foreach ($allActiveRooms as $room) {
            $roomStays = $stays->filter(function ($s) use ($room) {
                return $s->chiTietDatPhong && $s->chiTietDatPhong->maPhong === $room->id;
            });

            $occupiedCount = 0;
            foreach ($days as $dayStr) {
                $dayStart = Carbon::parse($dayStr)->startOfDay();
                $dayEnd = Carbon::parse($dayStr)->endOfDay();

                $isOccupied = $roomStays->contains(function ($s) use ($dayStart, $dayEnd) {
                    $stayStart = Carbon::parse($s->thoiGianNhan);
                    $stayEnd = $s->thoiGianTra ? Carbon::parse($s->thoiGianTra) : Carbon::now()->addYear();
                    return $stayStart->lessThanOrEqualTo($dayEnd) && $stayEnd->greaterThanOrEqualTo($dayStart);
                });

                if ($isOccupied) {
                    $occupiedCount++;
                }
            }

            $roomOccupiedDaysMap[$room->id] = $occupiedCount;
            $tongPhongNgayCoKhach += $occupiedCount;
        }

        $tyLeToanKhachSan = $tongPhongNgayKhaDung > 0
            ? round(($tongPhongNgayCoKhach / $tongPhongNgayKhaDung) * 100, 2)
            : 0.0;

        $tyLeToanKhachSan = min(100.0, max(0.0, $tyLeToanKhachSan));

        $theoHangPhong = [];
        foreach ($roomTypes as $rt) {
            $roomsOfRt = $rt->phong;
            $soPhongOfRt = $roomsOfRt->count();
            $phongNgayKhaDungOfRt = $soPhongOfRt * $soNgay;

            $phongNgayCoKhachOfRt = 0;
            foreach ($roomsOfRt as $r) {
                $phongNgayCoKhachOfRt += $roomOccupiedDaysMap[$r->id] ?? 0;
            }

            $tyLeOfRt = $phongNgayKhaDungOfRt > 0
                ? round(($phongNgayCoKhachOfRt / $phongNgayKhaDungOfRt) * 100, 2)
                : 0.0;

            $theoHangPhong[] = [
                'maLoaiPhong'         => $rt->id,
                'tenLoaiPhong'        => $rt->tenLoai,
                'soPhong'             => $soPhongOfRt,
                'phongNgayKhaDung'    => $phongNgayKhaDungOfRt,
                'phongNgayCoKhach'    => $phongNgayCoKhachOfRt,
                'tyLeLapDay'          => min(100.0, max(0.0, $tyLeOfRt)),
            ];
        }

        return [
            'soNgayTrongKy'        => $soNgay,
            'soPhongKhaDung'       => $soPhongKhaDung,
            'tongPhongNgayKhaDung' => $tongPhongNgayKhaDung,
            'tongPhongNgayCoKhach' => $tongPhongNgayCoKhach,
            'toanKhachSan'         => $tyLeToanKhachSan,
            'theoHangPhong'        => $theoHangPhong,
        ];
    }
}
