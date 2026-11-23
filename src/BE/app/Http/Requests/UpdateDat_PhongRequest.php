<?php

namespace App\Http\Requests;

use App\Models\Dat_Phong;
use App\Models\Phong;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDat_PhongRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng gửi request (UC05).
     * Chỉ Lễ tân và Quản lý mới được quyền quản lý và cập nhật đơn đặt phòng.
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
     * Phản hồi lỗi 403 Forbidden chuẩn khi không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => 'Chỉ Lễ tân hoặc Quản lý mới có quyền quản lý đơn đặt phòng.',
        ], 403));
    }

    /**
     * Quy tắc validation cập nhật đơn đặt phòng (UC05).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trangThai'                      => 'sometimes|nullable|string|in:Chờ xác nhận,Đã cọc,Đã xác nhận,Đã nhận phòng,Đang ở,Đã trả,Đã hoàn thành,Đã hủy',
            'ngayNhanPhong'                  => 'sometimes|nullable|date',
            'ngayTraPhong'                   => 'sometimes|nullable|date',
            'soLuongKhach'                   => 'sometimes|nullable|integer|min:1',
            'tienCoc'                        => 'sometimes|nullable|integer|min:0',
            'danhSachPhong'                  => 'sometimes|nullable|array|min:1',
            'danhSachPhong.*.maPhong'        => 'required_with:danhSachPhong|integer|distinct|exists:phong,id',
            'danhSachPhong.*.soLuongKhach'   => 'required_with:danhSachPhong|integer|min:1',
            'danhSachPhong.*.giaPhongApDung' => 'nullable|numeric|min:0',
            'ghiChu'                         => 'nullable|string|max:255',
        ];
    }

    /**
     * Thông báo lỗi Tiếng Việt có dấu theo quy chuẩn dự án.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'trangThai.string'                       => 'Trạng thái đơn đặt phòng phải là chuỗi ký tự.',
            'trangThai.in'                           => 'Trạng thái đơn đặt phòng không hợp lệ trong hệ thống.',

            'ngayNhanPhong.date'                     => 'Ngày nhận phòng không đúng định dạng ngày giờ.',

            'ngayTraPhong.date'                      => 'Ngày trả phòng không đúng định dạng ngày giờ.',

            'soLuongKhach.integer'                   => 'Số lượng khách phải là số nguyên.',
            'soLuongKhach.min'                       => 'Số lượng khách phải lớn hơn hoặc bằng 1.',

            'tienCoc.integer'                        => 'Tiền cọc phải là số nguyên.',
            'tienCoc.min'                            => 'Tiền cọc không được là số âm.',

            'danhSachPhong.array'                    => 'Danh sách phòng phải là một mảng.',
            'danhSachPhong.min'                      => 'Danh sách phòng phải có ít nhất một phòng.',

            'danhSachPhong.*.maPhong.required_with'  => 'Mã phòng không được để trống.',
            'danhSachPhong.*.maPhong.integer'        => 'Mã phòng phải là số nguyên.',
            'danhSachPhong.*.maPhong.distinct'       => 'Danh sách phòng không được chứa phòng trùng lặp.',
            'danhSachPhong.*.maPhong.exists'         => 'Phòng được chọn không tồn tại trong hệ thống.',

            'danhSachPhong.*.soLuongKhach.required_with' => 'Số lượng khách cho từng phòng không được để trống.',
            'danhSachPhong.*.soLuongKhach.integer'   => 'Số lượng khách cho từng phòng phải là số nguyên.',
            'danhSachPhong.*.soLuongKhach.min'       => 'Số lượng khách cho từng phòng phải lớn hơn hoặc bằng 1.',

            'danhSachPhong.*.giaPhongApDung.numeric' => 'Giá phòng áp dụng phải là số.',
            'danhSachPhong.*.giaPhongApDung.min'     => 'Giá phòng áp dụng không được là số âm.',
        ];
    }

    /**
     * Kiểm tra nghiệp vụ chuyên sâu: ngày lưu trú, phòng bảo trì, sức chứa.
     *
     * @param Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // 1. Kiểm tra phải có ít nhất một thông tin cần cập nhật
            $hasAnyField = $this->hasAny([
                'trangThai',
                'ngayNhanPhong',
                'ngayTraPhong',
                'soLuongKhach',
                'tienCoc',
                'danhSachPhong',
            ]);

            if (!$hasAnyField) {
                $validator->errors()->add(
                    'payload',
                    'Vui lòng cung cấp ít nhất một thông tin cần cập nhật (trạng thái, ngày lưu trú hoặc phòng).'
                );
                return;
            }

            // 2. Kiểm tra khoảng thời gian lưu trú (ngayTraPhong > ngayNhanPhong)
            $ngayNhan = $this->input('ngayNhanPhong');
            $ngayTra = $this->input('ngayTraPhong');

            if (($ngayNhan || $ngayTra) && !($ngayNhan && $ngayTra)) {
                $bookingId = $this->route('id');
                $booking = $bookingId ? Dat_Phong::find($bookingId) : null;
                if ($booking) {
                    $ngayNhan = $ngayNhan ?: $booking->ngayNhanPhong;
                    $ngayTra = $ngayTra ?: $booking->ngayTraPhong;
                }
            }

            if ($ngayNhan && $ngayTra) {
                try {
                    $start = Carbon::parse($ngayNhan);
                    $end = Carbon::parse($ngayTra);
                    if ($end->lte($start)) {
                        $validator->errors()->add('ngayTraPhong', 'Ngày trả phòng phải sau ngày nhận phòng.');
                    }
                } catch (\Throwable $e) {
                    // Bỏ qua vì đã bắt bởi rule 'date'
                }
            }

            // 3. Kiểm tra tính hợp lệ của danh sách phòng nếu có cập nhật phòng
            $danhSachPhong = $this->input('danhSachPhong');
            if (is_array($danhSachPhong) && !empty($danhSachPhong)) {
                $maPhongList = array_filter(array_column($danhSachPhong, 'maPhong'));
                if (!empty($maPhongList)) {
                    $phongs = Phong::with('loaiPhong')->whereIn('id', $maPhongList)->get()->keyBy('id');
                    $tongSucChua = 0;

                    foreach ($danhSachPhong as $index => $item) {
                        $maPhong = $item['maPhong'] ?? null;
                        $soKhach = isset($item['soLuongKhach']) ? (int) $item['soLuongKhach'] : 0;

                        if (!$maPhong || !isset($phongs[$maPhong])) {
                            continue;
                        }

                        $phong = $phongs[$maPhong];

                        // Phòng bảo trì hoặc ngừng hoạt động
                        if (in_array($phong->trangThai, ['Đang bảo trì', 'Ngừng kinh doanh', 'Ngưng khai thác'], true)) {
                            $validator->errors()->add(
                                "danhSachPhong.{$index}.maPhong",
                                "Phòng {$phong->tenPhong} hiện đang bảo trì hoặc ngừng hoạt động."
                            );
                        }

                        // Loại phòng ngừng kinh doanh
                        if ($phong->loaiPhong && $phong->loaiPhong->trangThai === 'Ngừng kinh doanh') {
                            $validator->errors()->add(
                                "danhSachPhong.{$index}.maPhong",
                                "Loại phòng của phòng {$phong->tenPhong} đã ngừng kinh doanh."
                            );
                        }

                        // Sức chứa từng phòng
                        $sucChuaToiDa = $phong->loaiPhong ? (int) $phong->loaiPhong->sucChuaToiDa : 0;
                        if ($sucChuaToiDa > 0 && $soKhach > $sucChuaToiDa) {
                            $validator->errors()->add(
                                "danhSachPhong.{$index}.soLuongKhach",
                                "Số lượng khách tại phòng {$phong->tenPhong} vượt quá sức chứa tối đa ({$sucChuaToiDa} người)."
                            );
                        }

                        $tongSucChua += $sucChuaToiDa;
                    }

                    // Tổng số lượng khách so với tổng sức chứa các phòng
                    $soLuongKhach = (int) $this->input('soLuongKhach', 0);
                    if ($soLuongKhach > 0 && $tongSucChua > 0 && $soLuongKhach > $tongSucChua) {
                        $validator->errors()->add(
                            'soLuongKhach',
                            "Tổng số lượng khách ({$soLuongKhach}) vượt quá tổng sức chứa của các phòng đã chọn ({$tongSucChua} người)."
                        );
                    }
                }
            }
        });
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
