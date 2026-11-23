<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckOutRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Lễ tân và Quản lý mới được thực hiện Check-out trả phòng (UC09 / BR08.14).
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
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Lễ tân hoặc Quản lý mới có quyền làm thủ tục trả phòng.',
        ], 403));
    }

    /**
     * Quy tắc validation cho yêu cầu Check-out.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ghiChu'      => 'nullable|string|max:100',
            'thoiGianTra' => 'nullable|date',
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
            'ghiChu.string'     => 'Ghi chú phải là chuỗi ký tự.',
            'ghiChu.max'        => 'Ghi chú không được vượt quá 100 ký tự.',
            'thoiGianTra.date'  => 'Thời gian trả phòng không đúng định dạng ngày giờ.',
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
