<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'password'    => 'required_without:matKhau|string|nullable',
            'matKhau'     => 'required_without:password|string|nullable',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tenDangNhap.required'        => 'Tên đăng nhập không được để trống.',
            'tenDangNhap.string'          => 'Tên đăng nhập phải là chuỗi ký tự.',
            'password.required_without'   => 'Mật khẩu không được để trống.',
            'password.string'             => 'Mật khẩu phải là chuỗi ký tự.',
            'matKhau.required_without'    => 'Mật khẩu không được để trống.',
            'matKhau.string'              => 'Mật khẩu phải là chuỗi ký tự.',
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
