<?php

namespace App\Http\Requests;

use App\Models\Dat_Phong;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Duyet_Huy_Dat_PhongRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn: Chỉ Lễ tân hoặc Quản lý mới được duyệt/từ chối hủy (UC15 / BR05.4).
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return in_array($user->vaiTro, ['Lễ tân', 'Quản lý'], true);
    }

    /**
     * Phản hồi lỗi 403 Forbidden chuẩn khi không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Chỉ Lễ tân hoặc Quản lý mới có quyền xử lý yêu cầu hủy đặt phòng.',
        ], 403));
    }

    /**
     * Quy tắc validation xử lý yêu cầu hủy đặt phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trangThai'   => 'required|string|in:Đã duyệt,Từ chối',
            'ghiChu'      => 'nullable|string|max:255',
            'soTienHoan'  => 'nullable|integer|min:0',
            'maThanhToan' => 'nullable|integer|exists:thanh_toan,id',
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
            'trangThai.required' => 'Trạng thái xử lý không được để trống.',
            'trangThai.in'       => 'Trạng thái xử lý phải là "Đã duyệt" hoặc "Từ chối".',
            'ghiChu.string'      => 'Ghi chú xử lý phải là chuỗi ký tự.',
            'ghiChu.max'         => 'Ghi chú không được vượt quá 255 ký tự.',
            'soTienHoan.integer' => 'Số tiền hoàn phải là số nguyên.',
            'soTienHoan.min'     => 'Số tiền hoàn không được âm.',
            'maThanhToan.exists' => 'Giao dịch thanh toán không tồn tại.',
        ];
    }

    /**
     * Kiểm tra trạng thái nghiệp vụ đơn đặt phòng trước khi xử lý.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $bookingId = $this->route('id');
            $datPhong = Dat_Phong::find($bookingId);

            if (!$datPhong) {
                // Đơn không tồn tại sẽ do Controller xử lý 404
                return;
            }

            if ($datPhong->trangThai === 'Đã hủy') {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'status'  => 409,
                    'message' => 'Đơn đặt phòng đã được hủy trước đó.',
                ], 409));
            }

            if (in_array($datPhong->trangThai, ['Đang ở', 'Đã trả', 'Đã hoàn thành'], true)) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'status'  => 409,
                    'message' => 'Không thể xử lý hủy cho đơn đặt phòng đang lưu trú hoặc đã hoàn thành.',
                ], 409));
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
