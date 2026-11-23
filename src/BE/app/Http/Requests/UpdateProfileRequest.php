<?php

namespace App\Http\Requests;

use App\Models\Khach_Hang;
use App\Models\Nhan_Vien;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Mọi người dùng đã xác thực (Sanctum) đều có quyền cập nhật hồ sơ cá nhân của mình.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Phản hồi lỗi 401 khi chưa xác thực danh tính.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 401,
            'message' => 'Chưa xác thực danh tính.',
        ], 401));
    }

    /**
     * Quy tắc validation cho việc cập nhật hồ sơ cá nhân (UC20 / UC04).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isCustomer = $user && $user->vaiTro === 'Khách hàng';
        $khachHang = $user?->khachHang;
        $nhanVien = $user?->nhanVien;

        $rules = [
            'hoTen'       => ['required', 'string', 'max:100'],
            'soDienThoai' => ['required', 'string', 'max:20', 'regex:/^[0-9]{9,15}$/'],
            'diaChi'      => ['nullable', 'string', 'max:255'],
            'ngaySinh'    => ['nullable', 'date'],
            'vaiTro'      => ['prohibited'],
            'chucVu'      => ['prohibited'],
            'trangThai'   => ['prohibited'],
            'tenDangNhap' => ['prohibited'],
            'cccd'        => ['prohibited'],
            'maTaiKhoan'  => ['prohibited'],
            'id'          => ['prohibited'],
        ];

        // Với khách hàng: email là bắt buộc và duy nhất trong bảng khach_hang
        if ($isCustomer || $khachHang) {
            $rules['email'] = [
                'required',
                'email',
                'max:100',
                Rule::unique('khach_hang', 'email')->ignore($khachHang?->id),
            ];
            $rules['soDienThoai'][] = Rule::unique('khach_hang', 'soDienThoai')->ignore($khachHang?->id);
        } else {
            // Với nhân viên: kiểm tra duy nhất số điện thoại trong bảng nhan_vien
            $rules['email'] = ['nullable', 'email', 'max:100'];
            $rules['soDienThoai'][] = Rule::unique('nhan_vien', 'soDienThoai')->ignore($nhanVien?->id);
        }

        return $rules;
    }

    /**
     * Thông báo lỗi Tiếng Việt có dấu theo quy chuẩn hệ thống.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hoTen.required'         => 'Họ và tên không được để trống.',
            'hoTen.string'           => 'Họ và tên phải là chuỗi ký tự.',
            'hoTen.max'              => 'Họ và tên không được vượt quá 100 ký tự.',

            'email.required'         => 'Email không được để trống.',
            'email.email'            => 'Email không đúng định dạng hợp lệ.',
            'email.max'              => 'Email không được vượt quá 100 ký tự.',
            'email.unique'           => 'Email này đã được sử dụng bởi tài khoản khác.',

            'soDienThoai.required'   => 'Số điện thoại không được để trống.',
            'soDienThoai.string'     => 'Số điện thoại phải là chuỗi ký tự.',
            'soDienThoai.max'        => 'Số điện thoại không được vượt quá 20 ký tự.',
            'soDienThoai.regex'      => 'Số điện thoại phải từ 9 đến 15 chữ số.',
            'soDienThoai.unique'     => 'Số điện thoại này đã được sử dụng bởi tài khoản khác.',

            'diaChi.string'          => 'Địa chỉ phải là chuỗi ký tự.',
            'diaChi.max'             => 'Địa chỉ không được vượt quá 255 ký tự.',

            'ngaySinh.date'          => 'Ngày sinh không đúng định dạng ngày hợp lệ.',

            'vaiTro.prohibited'      => 'Không được phép tự ý thay đổi vai trò tài khoản.',
            'chucVu.prohibited'      => 'Không được phép tự ý thay đổi chức vụ.',
            'trangThai.prohibited'   => 'Không được phép tự ý thay đổi trạng thái tài khoản hoặc làm việc.',
            'tenDangNhap.prohibited' => 'Không được phép thay đổi tên đăng nhập.',
            'cccd.prohibited'        => 'Không được phép thay đổi số CCCD định danh.',
            'maTaiKhoan.prohibited'  => 'Không được phép thay đổi mã tài khoản liên kết.',
            'id.prohibited'          => 'Không được phép thay đổi định danh hồ sơ.',
        ];
    }

    /**
     * Xử lý khi xác thực dữ liệu thất bại, luôn trả về JSON chuẩn HTTP 422.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 422,
            'message' => 'Dữ liệu cập nhật hồ sơ không hợp lệ.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
