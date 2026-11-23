<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReportFilterRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Quản lý khách sạn (hoặc Admin) mới được truy cập báo cáo thống kê (UC19).
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return in_array($user->vaiTro, ['Quản lý', 'Admin'], true);
    }

    /**
     * Trả về phản hồi lỗi 403 Forbidden chuẩn khi người dùng không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Bạn không có quyền truy cập báo cáo thống kê. Chức năng chỉ dành cho Quản lý khách sạn.',
        ], 403));
    }

    /**
     * Quy tắc validation cho khoảng thời gian và tiêu chí lọc báo cáo.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tuNgay'      => 'required|date',
            'denNgay'     => 'required|date|after_or_equal:tuNgay',
            'nhomTheo'    => 'nullable|string|in:ngay,thang,nam',
            'maLoaiPhong' => 'nullable|integer|exists:loai_phong,id',
        ];
    }

    /**
     * Thông báo lỗi Tiếng Việt có dấu theo quy chuẩn hệ thống.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tuNgay.required'        => 'Ngày bắt đầu không được để trống.',
            'tuNgay.date'            => 'Ngày bắt đầu không đúng định dạng ngày hợp lệ.',
            'denNgay.required'       => 'Ngày kết thúc không được để trống.',
            'denNgay.date'           => 'Ngày kết thúc không đúng định dạng ngày hợp lệ.',
            'denNgay.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'nhomTheo.string'        => 'Nhóm theo phải là chuỗi ký tự.',
            'nhomTheo.in'            => 'Nhóm theo chỉ chấp nhận các giá trị: ngay, thang, nam.',
            'maLoaiPhong.integer'    => 'Mã loại phòng phải là số nguyên.',
            'maLoaiPhong.exists'     => 'Loại phòng được chọn không tồn tại.',
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
            'message' => 'Khoảng thời gian hoặc dữ liệu lọc báo cáo không hợp lệ.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
