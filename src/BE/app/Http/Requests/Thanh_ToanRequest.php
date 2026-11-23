<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Thanh_ToanRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Lễ tân và Quản lý mới được ghi nhận thanh toán (UC07).
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
     * Trả về phản hồi lỗi 403 Forbidden chuẩn khi không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Lễ tân hoặc Quản lý mới có quyền ghi nhận thanh toán.',
        ], 403));
    }

    /**
     * Quy tắc validation cho giao dịch thanh toán.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'maHoaDon'   => 'required|integer|exists:hoa_don,id',
            'soTien'     => 'required|integer|min:1',
            'phuongThuc' => 'required|string|in:tien mat,qr,the',
            'maGiaoDich' => 'required|string|max:255|unique:thanh_toan,maGiaoDich',
        ];
    }

    /**
     * Thông báo lỗi Tiếng Việt có dấu theo chuẩn dự án.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'maHoaDon.required'   => 'Mã hóa đơn không được để trống.',
            'maHoaDon.integer'    => 'Mã hóa đơn phải là số nguyên.',
            'maHoaDon.exists'     => 'Hóa đơn không tồn tại trong hệ thống.',

            'soTien.required'     => 'Số tiền thanh toán không được để trống.',
            'soTien.integer'      => 'Số tiền thanh toán phải là số nguyên.',
            'soTien.min'          => 'Số tiền thanh toán phải lớn hơn 0.',

            'phuongThuc.required' => 'Phương thức thanh toán không được để trống.',
            'phuongThuc.string'   => 'Phương thức thanh toán phải là chuỗi ký tự.',
            'phuongThuc.in'       => 'Phương thức thanh toán không hợp lệ. Chỉ chấp nhận: tien mat, qr, the.',

            'maGiaoDich.required' => 'Mã giao dịch không được để trống.',
            'maGiaoDich.string'   => 'Mã giao dịch phải là chuỗi ký tự.',
            'maGiaoDich.max'      => 'Mã giao dịch không được vượt quá 255 ký tự.',
            'maGiaoDich.unique'   => 'Mã giao dịch đã tồn tại trong hệ thống.',
        ];
    }

    /**
     * Xử lý khi xác thực dữ liệu thất bại, luôn trả về JSON chuẩn theo api-specification.
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
