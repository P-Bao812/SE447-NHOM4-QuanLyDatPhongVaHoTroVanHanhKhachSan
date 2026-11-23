<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class PhongRequest extends FormRequest
{
    /**
     * Xác định quyền hạn: Chỉ Quản lý mới được thêm, sửa hoặc đổi tình trạng phòng vật lý (UC06 / BR01.5, BR01.6).
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        return $user->vaiTro === 'Quản lý';
    }

    /**
     * Trả về phản hồi lỗi 403 Forbidden chuẩn khi không có quyền Quản lý.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Bạn không có quyền thực hiện thao tác này. Chỉ Quản lý mới có quyền quản lý phòng.',
        ], 403));
    }

    /**
     * Quy tắc validation cho tạo và cập nhật phòng vật lý.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roomId = $this->route('id') ?? $this->route('room');

        if ($this->isMethod('post')) {
            return [
                'maLoaiPhong' => 'required|integer|exists:loai_phong,id',
                'tenPhong'    => 'required|string|max:20|unique:phong,tenPhong',
                'tang'        => 'required|integer|min:1',
                'trangThai'   => 'required|string|in:Sẵn sàng,Đang bảo trì,Ngưng khai thác',
            ];
        }

        // Với PUT / PATCH
        return [
            'maLoaiPhong' => 'sometimes|required|integer|exists:loai_phong,id',
            'tenPhong'    => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('phong', 'tenPhong')->ignore($roomId),
            ],
            'tang'        => 'sometimes|required|integer|min:1',
            'trangThai'   => 'sometimes|required|string|in:Sẵn sàng,Đang bảo trì,Ngưng khai thác',
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
            'maLoaiPhong.required' => 'Mã loại phòng không được để trống.',
            'maLoaiPhong.integer'  => 'Mã loại phòng phải là số nguyên.',
            'maLoaiPhong.exists'   => 'Loại phòng được chọn không tồn tại trong hệ thống.',

            'tenPhong.required'    => 'Tên phòng không được để trống.',
            'tenPhong.string'      => 'Tên phòng phải là chuỗi ký tự.',
            'tenPhong.max'         => 'Tên phòng không được vượt quá 20 ký tự.',
            'tenPhong.unique'      => 'Tên phòng đã tồn tại trong hệ thống.',

            'tang.required'        => 'Tầng không được để trống.',
            'tang.integer'         => 'Tầng phải là số nguyên.',
            'tang.min'             => 'Tầng phải lớn hơn hoặc bằng 1.',

            'trangThai.required'   => 'Trạng thái phòng không được để trống.',
            'trangThai.string'     => 'Trạng thái phòng phải là chuỗi ký tự.',
            'trangThai.in'         => 'Trạng thái phòng không hợp lệ. Chỉ chấp nhận: Sẵn sàng, Đang bảo trì, Ngưng khai thác.',
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
