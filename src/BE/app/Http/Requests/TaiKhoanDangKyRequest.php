<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TaiKhoanDangKyRequest extends FormRequest
{
    /**
     * Xác thực quyền gửi request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validation đăng ký tài khoản (UC01 - BR08).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Họ tên khách hàng
            'hoTen'                 => 'required|string|max:100',

            // Email và số điện thoại phải duy nhất theo BR08.2
            'email'                 => 'required|email|max:100|unique:khach_hang,email',
            'soDienThoai'           => 'required|string|max:20|unique:khach_hang,soDienThoai',

            // Tên đăng nhập (nếu cung cấp phải là duy nhất trong tai_khoan)
            'tenDangNhap'           => 'nullable|string|max:255|unique:tai_khoan,tenDangNhap',

            // Mật khẩu và xác nhận mật khẩu (hỗ trợ cả field password và mat_khau/matKhau)
            'password'              => 'required_without_all:mat_khau,matKhau|nullable|string|min:6|confirmed',
            'password_confirmation' => 'required_with:password|nullable|string|min:6',

            'mat_khau'              => 'required_without_all:password,matKhau|nullable|string|min:6|confirmed',
            'mat_khau_confirmation' => 'required_with:mat_khau|nullable|string|min:6',

            'matKhau'               => 'required_without_all:password,mat_khau|nullable|string|min:6|confirmed',
            'matKhau_confirmation'  => 'required_with:matKhau|nullable|string|min:6',

            // CCCD và Địa chỉ theo ràng buộc CSDL cdio4.sql
            'cccd'                  => 'nullable|string|max:20|unique:khach_hang,cccd',
            'diaChi'                => 'nullable|string|max:255',
        ];
    }

    /**
     * Thông báo lỗi Tiếng Việt thân thiện.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hoTen.required'                   => 'Họ tên không được để trống.',
            'hoTen.string'                     => 'Họ tên phải là chuỗi ký tự.',
            'hoTen.max'                        => 'Họ tên không được vượt quá 100 ký tự.',

            'email.required'                   => 'Email không được để trống.',
            'email.email'                      => 'Email không đúng định dạng.',
            'email.max'                        => 'Email không được vượt quá 100 ký tự.',
            'email.unique'                     => 'Email đã được đăng ký trong hệ thống.',

            'soDienThoai.required'             => 'Số điện thoại không được để trống.',
            'soDienThoai.string'               => 'Số điện thoại phải là chuỗi ký tự.',
            'soDienThoai.max'                  => 'Số điện thoại không được vượt quá 20 ký tự.',
            'soDienThoai.unique'               => 'Số điện thoại đã được đăng ký trong hệ thống.',

            'tenDangNhap.unique'               => 'Tên đăng nhập đã tồn tại trong hệ thống.',
            'tenDangNhap.string'               => 'Tên đăng nhập phải là chuỗi ký tự.',
            'tenDangNhap.max'                  => 'Tên đăng nhập không được vượt quá 255 ký tự.',

            'password.required_without_all'    => 'Mật khẩu không được để trống.',
            'password.min'                     => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed'               => 'Xác nhận mật khẩu không khớp.',
            'password_confirmation.required_with' => 'Xác nhận mật khẩu không được để trống.',

            'mat_khau.required_without_all'    => 'Mật khẩu không được để trống.',
            'mat_khau.min'                     => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'mat_khau.confirmed'               => 'Xác nhận mật khẩu không khớp.',
            'mat_khau_confirmation.required_with' => 'Xác nhận mật khẩu không được để trống.',

            'matKhau.required_without_all'     => 'Mật khẩu không được để trống.',
            'matKhau.min'                      => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'matKhau.confirmed'                => 'Xác nhận mật khẩu không khớp.',
            'matKhau_confirmation.required_with' => 'Xác nhận mật khẩu không được để trống.',

            'cccd.unique'                      => 'Số CCCD đã tồn tại trong hệ thống.',
            'cccd.max'                         => 'Số CCCD không được vượt quá 20 ký tự.',
            'diaChi.max'                       => 'Địa chỉ không được vượt quá 255 ký tự.',
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
