<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Hoa_DonRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Lễ tân và Quản lý mới được chỉnh sửa / thêm phụ thu hóa đơn (UC07).
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
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Lễ tân hoặc Quản lý mới có quyền.',
        ], 403));
    }

    /**
     * Quy tắc validation cho khoản điều chỉnh / phụ thu hóa đơn.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'noiDung' => 'required|string|max:100',
            'soLuong' => 'required|integer|min:1',
            'donGia'  => 'required|integer|min:0',
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
            'noiDung.required' => 'Nội dung khoản thu không được để trống.',
            'noiDung.string'   => 'Nội dung khoản thu phải là chuỗi ký tự.',
            'noiDung.max'      => 'Nội dung khoản thu không được vượt quá 100 ký tự.',

            'soLuong.required' => 'Số lượng không được để trống.',
            'soLuong.integer'  => 'Số lượng phải là số nguyên.',
            'soLuong.min'      => 'Số lượng phải lớn hơn hoặc bằng 1.',

            'donGia.required'  => 'Đơn giá không được để trống.',
            'donGia.integer'   => 'Đơn giá phải là số nguyên.',
            'donGia.min'       => 'Đơn giá phải lớn hơn hoặc bằng 0.',
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
