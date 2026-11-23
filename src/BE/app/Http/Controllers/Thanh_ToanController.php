<?php

namespace App\Http\Controllers;

use App\Http\Requests\Thanh_ToanRequest;
use App\Models\Hoa_Don;
use App\Models\Thanh_Toan;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Thanh_ToanController extends Controller
{
    use ApiResponseTrait;

    /**
     * Ghi nhận thanh toán tiền mặt, QR hoặc thẻ cho hóa đơn lưu trú (UC07 / BR04.1, BR04.2, BR04.4).
     *
     * @param Thanh_ToanRequest $request
     * @return JsonResponse
     */
    public function store(Thanh_ToanRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // BR04.4: Đảm bảo Idempotency - Không cho ghi hai lần cùng một mã giao dịch
            $existingPayment = Thanh_Toan::where('maGiaoDich', $request->input('maGiaoDich'))->first();
            if ($existingPayment) {
                DB::rollBack();
                return $this->errorResponse('Mã giao dịch này đã được ghi nhận trước đó trong hệ thống.', 409);
            }

            /** @var Hoa_Don|null $hoaDon */
            $hoaDon = Hoa_Don::where('id', $request->input('maHoaDon'))->lockForUpdate()->first();

            if (!$hoaDon) {
                DB::rollBack();
                return $this->errorResponse('Hóa đơn không tồn tại trong hệ thống.', 404);
            }

            // BR04.2: Hóa đơn đã thanh toán hoàn tất không thể thanh toán tiếp
            if ($hoaDon->trangThai === 'Đã thanh toán') {
                DB::rollBack();
                return $this->errorResponse('Hóa đơn này đã được thanh toán hoàn tất trước đó.', 409);
            }

            // Tính số tiền đã thanh toán và số tiền còn lại phải trả
            $daThanhToan = (int) $hoaDon->thanhToans()->where('trangThai', 'Thành công')->sum('soTien');
            $conLai = max(0, (int) $hoaDon->tongTien - $daThanhToan);
            $soTienThanhToan = (int) $request->input('soTien');

            // Kiểm tra số tiền thanh toán không được vượt quá số còn lại
            if ($soTienThanhToan > $conLai) {
                DB::rollBack();
                return $this->errorResponse("Số tiền thanh toán ({$soTienThanhToan} VNĐ) vượt quá số tiền còn lại phải thanh toán ({$conLai} VNĐ).", 422);
            }

            // Ghi nhận bản ghi thanh toán
            $thanhToan = Thanh_Toan::create([
                'maHoaDon'   => $hoaDon->id,
                'soTien'     => $soTienThanhToan,
                'phuongThuc' => $request->input('phuongThuc'),
                'maGiaoDich' => $request->input('maGiaoDich'),
                'thoiGian'   => now(),
                'trangThai'  => 'Thành công',
            ]);

            // Cập nhật trạng thái hóa đơn khi đã thanh toán đủ
            $tongDaThanhToanMoi = $daThanhToan + $soTienThanhToan;
            if ($tongDaThanhToanMoi >= $hoaDon->tongTien) {
                $hoaDon->update([
                    'trangThai' => 'Đã thanh toán',
                ]);
            }

            DB::commit();

            return $this->successResponse([
                'thanhToan' => [
                    'id'         => $thanhToan->id,
                    'maHoaDon'   => $thanhToan->maHoaDon,
                    'soTien'     => (int) $thanhToan->soTien,
                    'phuongThuc' => $thanhToan->phuongThuc,
                    'maGiaoDich' => $thanhToan->maGiaoDich,
                    'thoiGian'   => $thanhToan->thoiGian?->format('Y-m-d H:i:s'),
                    'trangThai'  => $thanhToan->trangThai,
                ],
                'hoaDon'    => [
                    'maHoaDon'    => $hoaDon->id,
                    'tongTien'    => (int) $hoaDon->tongTien,
                    'daThanhToan' => $tongDaThanhToanMoi,
                    'conLai'      => max(0, (int) $hoaDon->tongTien - $tongDaThanhToanMoi),
                    'trangThai'   => $hoaDon->trangThai,
                ],
            ], 'Xác nhận thanh toán thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi ghi nhận thanh toán: ' . $e->getMessage());
            return $this->errorResponse('Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.', 500);
        }
    }
}
