<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class Dich_VuRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng: Chỉ Quản lý mới được phép thêm/sửa/cập nhật dịch vụ.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $vaiTro = $user->vaiTro;
        return in_array($vaiTro, ['Quản lý', 'Admin'], true);
    }

    /**
     * Quy tắc xác thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Nếu là endpoint cập nhật nhanh trạng thái
        if ($this->routeIs('*status*') || $this->is('*/status')) {
            return [
                'trangThai' => ['required', 'string', 'in:Đang cung cấp,Ngừng cung cấp'],
            ];
        }

        $serviceId = $this->route('id') ?? $this->route('service');

        $rules = [
            'tenDichVu' => [
                'required',
                'string',
                'max:100',
                $serviceId
                    ? Rule::unique('dich_vu', 'tenDichVu')->ignore($serviceId)
                    : Rule::unique('dich_vu', 'tenDichVu'),
            ],
            'moTa'      => ['required', 'string', 'max:100'],
            'donGia'    => ['required', 'integer', 'min:1'],
            'trangThai' => ['required', 'string', 'in:Đang cung cấp,Ngừng cung cấp'],
        ];

        return $rules;
    }

    /**
     * Thông báo lỗi Validation bằng Tiếng Việt thân thiện.
     */
    public function messages(): array
    {
        return [
            'tenDichVu.required' => 'Tên dịch vụ không được để trống.',
            'tenDichVu.string'   => 'Tên dịch vụ phải là chuỗi ký tự hợp lệ.',
            'tenDichVu.max'      => 'Tên dịch vụ không được vượt quá 100 ký tự.',
            'tenDichVu.unique'   => 'Tên dịch vụ đã tồn tại trong hệ thống.',
            'moTa.required'      => 'Mô tả dịch vụ không được để trống.',
            'moTa.string'        => 'Mô tả dịch vụ phải là chuỗi ký tự hợp lệ.',
            'moTa.max'           => 'Mô tả dịch vụ không được vượt quá 100 ký tự.',
            'donGia.required'    => 'Đơn giá dịch vụ không được để trống.',
            'donGia.integer'     => 'Đơn giá dịch vụ phải là số nguyên hợp lệ.',
            'donGia.min'         => 'Đơn giá dịch vụ phải lớn hơn 0 (tối thiểu là 1 VNĐ).',
            'trangThai.required' => 'Trạng thái dịch vụ không được để trống.',
            'trangThai.string'   => 'Trạng thái dịch vụ không hợp lệ.',
            'trangThai.in'       => 'Trạng thái dịch vụ phải là "Đang cung cấp" hoặc "Ngừng cung cấp".',
        ];
    }

    /**
     * Xử lý khi xác thực dữ liệu thất bại (Trả về 422 JSON chuẩn).
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 422,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422));
    }

    /**
     * Xử lý khi người dùng không có quyền truy cập (Trả về 403 Forbidden chuẩn).
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chức năng chỉ dành cho Quản lý khách sạn.',
        ], 403));
    }
}
