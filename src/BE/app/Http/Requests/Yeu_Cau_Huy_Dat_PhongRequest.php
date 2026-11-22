<?php

namespace App\Http\Requests;

use App\Models\Dat_Phong;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Cache;

class Yeu_Cau_Huy_Dat_PhongRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng gửi yêu cầu hủy (UC15 / BR05.1, BR05.4).
     * Cho phép: Khách hàng (chỉ đơn của chính mình), Lễ tân, Quản lý.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Khách hàng', 'Lễ tân', 'Quản lý'], true)) {
            return false;
        }

        if ($vaiTro === 'Khách hàng') {
            $khachHang = $user->khachHang;
            if (!$khachHang) {
                return false;
            }

            $bookingId = $this->route('id');
            $datPhong = Dat_Phong::find($bookingId);
            if ($datPhong && (int) $datPhong->maKhachHang !== (int) $khachHang->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Phản hồi lỗi 403 Forbidden chuẩn khi không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        $user = $this->user();
        $message = 'Bạn không có quyền thực hiện thao tác này.';

        if ($user && $user->vaiTro === 'Khách hàng') {
            $bookingId = $this->route('id');
            $datPhong = Dat_Phong::find($bookingId);
            if ($datPhong && $user->khachHang && (int) $datPhong->maKhachHang !== (int) $user->khachHang->id) {
                $message = 'Bạn không có quyền gửi yêu cầu hủy cho đơn đặt phòng của khách hàng khác.';
            } elseif (!$user->khachHang) {
                $message = 'Tài khoản chưa có hồ sơ khách hàng.';
            }
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => $message,
        ], 403));
    }

    /**
     * Quy tắc validation cho yêu cầu hủy đặt phòng (UC15).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lyDoHuy'             => 'nullable|string|max:255',
            'xacNhanKhongHoanCoc' => 'nullable|boolean',
            'maThanhToan'         => 'nullable|integer|exists:thanh_toan,id',
        ];
    }

    /**
     * Thông báo lỗi Tiếng Việt có dấu theo quy chuẩn dự án.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lyDoHuy.string'         => 'Lý do hủy phải là chuỗi ký tự.',
            'lyDoHuy.max'            => 'Lý do hủy không được vượt quá 255 ký tự.',
            'xacNhanKhongHoanCoc.boolean' => 'Giá trị xác nhận không hoàn cọc phải là kiểu boolean (true/false).',
            'maThanhToan.exists'     => 'Giao dịch thanh toán không tồn tại.',
        ];
    }

    /**
     * Kiểm tra trạng thái đơn và điều kiện chính sách hủy sau khi xác thực cú pháp cơ bản.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $bookingId = $this->route('id');
            $datPhong = Dat_Phong::find($bookingId);

            if (!$datPhong) {
                // Đơn không tồn tại sẽ được Controller xử lý trả về 404 Not Found
                return;
            }

            // 1. Kiểm tra nếu đơn đã hủy từ trước
            if ($datPhong->trangThai === 'Đã hủy') {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'status'  => 409,
                    'message' => 'Đơn đặt phòng đã được hủy trước đó.',
                ], 409));
            }

            // 2. Kiểm tra nếu đơn đang lưu trú hoặc đã hoàn thành
            if (in_array($datPhong->trangThai, ['Đang ở', 'Đã trả', 'Đã hoàn thành'], true)) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'status'  => 409,
                    'message' => 'Không thể hủy đơn đặt phòng đang lưu trú hoặc đã hoàn thành.',
                ], 409));
            }

            // 3. Kiểm tra yêu cầu hủy trùng lặp đang chờ xử lý
            $existing = Cache::get("cancellation_request_{$datPhong->id}");
            if ($existing && ($existing['trangThaiYeuCau'] ?? '') === 'Chờ xử lý') {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'status'  => 409,
                    'message' => 'Đơn đặt phòng đã gửi yêu cầu hủy và đang chờ xử lý.',
                ], 409));
            }

            // 4. Kiểm tra thời hạn hoàn cọc (trước ngày nhận phòng 24h)
            $hanHoanCoc = Carbon::parse($datPhong->ngayNhanPhong)->subHours(24);
            $isWithinDeadline = now()->lessThanOrEqualTo($hanHoanCoc);

            if (!$isWithinDeadline && !$this->boolean('xacNhanKhongHoanCoc')) {
                $validator->errors()->add(
                    'xacNhanKhongHoanCoc',
                    'Đơn đặt phòng đã vượt quá thời hạn hủy được hoàn tiền cọc. Quý khách vui lòng xác nhận không hoàn cọc để gửi yêu cầu hủy.'
                );
            }
        });
    }

    /**
     * Xử lý khi xác thực dữ liệu thất bại, luôn trả về JSON chuẩn 422.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 422,
            'message' => 'Dữ liệu gửi lên không hợp lệ.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
