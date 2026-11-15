<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaiKhoanLoginRequest extends FormRequest
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
            'tenDangNhap' => "required",
            'password'    => "required|min:6|string"
        ];
    }
    public function messages(): array
    {
        return [
            "tenDangNhap.required" => "Tên đăng nhập không được để trống",
            "password.retquired" => "Mật khẩu không được để trống",
            "password.min:6" => "Mật khẩu không được dưới 6 ký tự",
        ];
    }
}
