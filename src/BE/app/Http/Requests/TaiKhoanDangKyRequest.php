<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaiKhoanDangKyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'tenDangNhap' => "required|unique:tai_khoan,tenDangNhap",
            'mat_khau' => "required|min:6|string",
            'mat_khau_confirmation' => 'required|same:mat_khau'
        ];
    }
    public function messages(): array
    {
        return [
            //
            'tenDangNhap.unique' => "Tên đăng nhập không được trùng",
            'mat_khau.required' => 'Mật khẩu mới không được để trống',
            'mat_khau.min'      => 'Mật khẩu mới không được dưới 6 ký tự',
            'mat_khau.string'   => 'Mật khẩu mới phải là chuỗi',
            'mat_khau_confirmation.required' => 'Xác nhận mật khẩu không được để trống',
            'mat_khau_confirmation.same' => 'Xác nhận mật khẩu không khớp',
        ];
    }
}
