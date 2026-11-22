<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenDangNhap' => 'required|string',
            'password'    => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'tenDangNhap.required' => 'Tên đăng nhập không được để trống.',
            'tenDangNhap.string'   => 'Tên đăng nhập phải là chuỗi ký tự.',
            'password.required'    => 'Mật khẩu không được để trống.',
            'password.string'      => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min'         => 'Mật khẩu không được dưới 6 ký tự.',
        ];
    }

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
