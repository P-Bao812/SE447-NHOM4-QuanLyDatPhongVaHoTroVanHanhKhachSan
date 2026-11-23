<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckInRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Lễ tân và Quản lý mới được thực hiện Check-in nhận phòng (UC09 / BR08.14).
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
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Lễ tân hoặc Quản lý mới có quyền làm thủ tục nhận phòng.',
        ], 403));
    }

    /**
     * Quy tắc validation cho yêu cầu Check-in.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ghiChu'                      => 'nullable|string|max:100',
            'thoiGianNhan'                => 'nullable|date',
            'danhSachPhong'               => 'nullable|array',
            'danhSachPhong.*.maChiTiet'   => 'required_with:danhSachPhong|integer|exists:chi_tiet_dat_phong,id',
            'danhSachPhong.*.maPhong'      => 'required_with:danhSachPhong|integer|exists:phong,id',
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
            'ghiChu.string'                    => 'Ghi chú phải là chuỗi ký tự.',
            'ghiChu.max'                       => 'Ghi chú không được vượt quá 100 ký tự.',
            'thoiGianNhan.date'                => 'Thời gian nhận phòng không đúng định dạng ngày giờ.',
            'danhSachPhong.array'              => 'Danh sách phòng phải là một mảng.',
            'danhSachPhong.*.maChiTiet.exists' => 'Chi tiết đặt phòng không tồn tại.',
            'danhSachPhong.*.maPhong.exists'    => 'Phòng được chỉ định không tồn tại trong hệ thống.',
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
