<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class Khach_HangRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Lễ tân và Quản lý mới được tạo hoặc cập nhật hồ sơ khách hàng (UC08 / BR08.9).
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
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Lễ tân hoặc Quản lý mới có quyền quản lý hồ sơ khách hàng.',
        ], 403));
    }

    /**
     * Quy tắc validation cho tạo và cập nhật hồ sơ khách hàng.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('id') ?? $this->route('customer');

        if ($this->isMethod('post')) {
            return [
                'hoTen'       => 'required|string|max:100',
                'cccd'        => 'required|string|max:20|unique:khach_hang,cccd',
                'email'       => 'required|email|max:100|unique:khach_hang,email',
                'soDienThoai' => 'required|string|max:20|unique:khach_hang,soDienThoai',
                'diaChi'      => 'nullable|string|max:255',
                'maTaiKhoan'  => 'nullable|integer|exists:tai_khoan,id',
            ];
        }

        // Với PUT / PATCH
        return [
            'hoTen'       => 'sometimes|required|string|max:100',
            'cccd'        => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('khach_hang', 'cccd')->ignore($customerId),
            ],
            'email'       => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('khach_hang', 'email')->ignore($customerId),
            ],
            'soDienThoai' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('khach_hang', 'soDienThoai')->ignore($customerId),
            ],
            'diaChi'      => 'nullable|string|max:255',
            'maTaiKhoan'  => 'nullable|integer|exists:tai_khoan,id',
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
            'hoTen.required'       => 'Họ tên khách hàng không được để trống.',
            'hoTen.string'         => 'Họ tên khách hàng phải là chuỗi ký tự.',
            'hoTen.max'            => 'Họ tên khách hàng không được vượt quá 100 ký tự.',

            'cccd.required'        => 'Số CCCD/Hộ chiếu không được để trống.',
            'cccd.string'          => 'Số CCCD/Hộ chiếu phải là chuỗi ký tự.',
            'cccd.max'             => 'Số CCCD/Hộ chiếu không được vượt quá 20 ký tự.',
            'cccd.unique'          => 'Số CCCD/Hộ chiếu này đã tồn tại trong hệ thống.',

            'email.required'       => 'Email khách hàng không được để trống.',
            'email.email'          => 'Email không đúng định dạng.',
            'email.max'            => 'Email không được vượt quá 100 ký tự.',
            'email.unique'         => 'Email này đã tồn tại trong hệ thống.',

            'soDienThoai.required' => 'Số điện thoại không được để trống.',
            'soDienThoai.string'   => 'Số điện thoại phải là chuỗi ký tự.',
            'soDienThoai.max'      => 'Số điện thoại không được vượt quá 20 ký tự.',
            'soDienThoai.unique'   => 'Số điện thoại này đã tồn tại trong hệ thống.',

            'diaChi.string'        => 'Địa chỉ phải là chuỗi ký tự.',
            'diaChi.max'           => 'Địa chỉ không được vượt quá 255 ký tự.',

            'maTaiKhoan.integer'   => 'Mã tài khoản phải là số nguyên.',
            'maTaiKhoan.exists'    => 'Tài khoản liên kết không tồn tại trong hệ thống.',
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
