<?php

namespace App\Http\Requests;

use App\Models\Phong;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Dat_PhongRequest extends FormRequest
{
    /**
     * Xác thực quyền hạn người dùng gửi request (UC04 / BR02.4).
     * Cho phép: Khách hàng, Lễ tân, Quản lý.
     * Khách hàng chỉ được phép đặt cho chính mình (không sửa maKhachHang thành của người khác).
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Khách hàng', 'Lễ tân', 'Quản lý'], true)) {
            return false;
        }

        // Khách hàng không được đặt thay cho khách khác
        if ($vaiTro === 'Khách hàng') {
            $khachHang = $user->khachHang;
            if (!$khachHang) {
                return false;
            }
            if ($this->filled('maKhachHang') && (int) $this->input('maKhachHang') !== (int) $khachHang->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Phản hồi lỗi 403 Forbidden chuẩn khi không có quyền.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization()
    {
        $user = $this->user();
        $message = 'Bạn không có quyền thực hiện thao tác này.';

        if ($user && $user->vaiTro === 'Khách hàng') {
            if ($this->filled('maKhachHang') && $user->khachHang && (int) $this->input('maKhachHang') !== (int) $user->khachHang->id) {
                $message = 'Khách hàng không được phép đặt phòng thay cho người khác.';
            } elseif (!$user->khachHang) {
                $message = 'Tài khoản chưa có hồ sơ khách hàng.';
            }
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'status'  => 403,
            'message' => $message,
        ], 403));
    }

    /**
     * Quy tắc validation tạo đơn đặt phòng (UC04 - api-specification.md & business-rules.md).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isStaff = $user && in_array($user->vaiTro, ['Lễ tân', 'Quản lý'], true);

        return [
            // maKhachHang bắt buộc đối với Lễ tân / Quản lý; tùy chọn đối với Khách hàng (tự động lấy từ tài khoản)
            'maKhachHang'                  => $isStaff ? 'required|integer|exists:khach_hang,id' : 'nullable|integer|exists:khach_hang,id',
            'ngayNhanPhong'                => 'required|date|after_or_equal:today',
            'ngayTraPhong'                 => 'required|date|after:ngayNhanPhong',
            'soLuongKhach'                 => 'required|integer|min:1',
            'tienCoc'                      => 'nullable|integer|min:0',
            'danhSachPhong'                => 'required|array|min:1',
            'danhSachPhong.*.maPhong'       => 'required|integer|distinct|exists:phong,id',
            'danhSachPhong.*.soLuongKhach' => 'required|integer|min:1',
            'danhSachPhong.*.giaPhongApDung' => 'nullable|numeric|min:0',
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
            'maKhachHang.required'                  => 'Mã khách hàng không được để trống.',
            'maKhachHang.integer'                   => 'Mã khách hàng phải là số nguyên.',
            'maKhachHang.exists'                    => 'Khách hàng không tồn tại trong hệ thống.',

            'ngayNhanPhong.required'                => 'Ngày nhận phòng không được để trống.',
            'ngayNhanPhong.date'                    => 'Ngày nhận phòng không đúng định dạng ngày giờ.',
            'ngayNhanPhong.after_or_equal'          => 'Ngày nhận phòng không được ở trong quá khứ.',

            'ngayTraPhong.required'                 => 'Ngày trả phòng không được để trống.',
            'ngayTraPhong.date'                     => 'Ngày trả phòng không đúng định dạng ngày giờ.',
            'ngayTraPhong.after'                    => 'Ngày trả phòng phải sau ngày nhận phòng.',

            'soLuongKhach.required'                 => 'Số lượng khách không được để trống.',
            'soLuongKhach.integer'                  => 'Số lượng khách phải là số nguyên.',
            'soLuongKhach.min'                      => 'Số lượng khách phải lớn hơn hoặc bằng 1.',

            'tienCoc.integer'                       => 'Tiền cọc phải là số nguyên.',
            'tienCoc.min'                           => 'Tiền cọc không được là số âm.',

            'danhSachPhong.required'                => 'Danh sách phòng không được để trống.',
            'danhSachPhong.array'                   => 'Danh sách phòng phải là một mảng.',
            'danhSachPhong.min'                     => 'Phải chọn ít nhất một phòng để đặt.',

            'danhSachPhong.*.maPhong.required'       => 'Mã phòng không được để trống.',
            'danhSachPhong.*.maPhong.integer'        => 'Mã phòng phải là số nguyên.',
            'danhSachPhong.*.maPhong.distinct'       => 'Danh sách phòng không được chứa phòng trùng lặp.',
            'danhSachPhong.*.maPhong.exists'         => 'Phòng được chọn không tồn tại trong hệ thống.',

            'danhSachPhong.*.soLuongKhach.required' => 'Số lượng khách cho từng phòng không được để trống.',
            'danhSachPhong.*.soLuongKhach.integer'  => 'Số lượng khách cho từng phòng phải là số nguyên.',
            'danhSachPhong.*.soLuongKhach.min'      => 'Số lượng khách cho từng phòng phải lớn hơn hoặc bằng 1.',

            'danhSachPhong.*.giaPhongApDung.numeric' => 'Giá phòng áp dụng phải là số.',
            'danhSachPhong.*.giaPhongApDung.min'     => 'Giá phòng áp dụng không được là số âm.',
        ];
    }

    /**
     * Bổ sung kiểm tra logic nghiệp vụ chuyên sâu: sức chứa, trạng thái phòng và loại phòng.
     *
     * @param Validator $validator
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $danhSachPhong = $this->input('danhSachPhong');
            if (!is_array($danhSachPhong) || empty($danhSachPhong)) {
                return;
            }

            $maPhongList = array_filter(array_column($danhSachPhong, 'maPhong'));
            if (empty($maPhongList)) {
                return;
            }

            $phongs = Phong::with('loaiPhong')->whereIn('id', $maPhongList)->get()->keyBy('id');

            $tongSucChua = 0;
            $tongKhachCacPhong = 0;

            foreach ($danhSachPhong as $index => $item) {
                $maPhong = $item['maPhong'] ?? null;
                $soKhach = isset($item['soLuongKhach']) ? (int) $item['soLuongKhach'] : 0;

                if (!$maPhong || !isset($phongs[$maPhong])) {
                    continue;
                }

                $phong = $phongs[$maPhong];

                // 1. Kiểm tra trạng thái phòng
                if (in_array($phong->trangThai, ['Đang bảo trì', 'Ngừng kinh doanh'], true)) {
                    $validator->errors()->add(
                        "danhSachPhong.{$index}.maPhong",
                        "Phòng {$phong->tenPhong} hiện đang bảo trì hoặc ngừng hoạt động."
                    );
                }

                // 2. Kiểm tra trạng thái loại phòng
                if ($phong->loaiPhong && $phong->loaiPhong->trangThai === 'Ngừng kinh doanh') {
                    $validator->errors()->add(
                        "danhSachPhong.{$index}.maPhong",
                        "Loại phòng của phòng {$phong->tenPhong} đã ngừng kinh doanh."
                    );
                }

                // 3. Kiểm tra sức chứa từng phòng
                $sucChuaToiDa = $phong->loaiPhong ? (int) $phong->loaiPhong->sucChuaToiDa : 0;
                if ($sucChuaToiDa > 0 && $soKhach > $sucChuaToiDa) {
                    $validator->errors()->add(
                        "danhSachPhong.{$index}.soLuongKhach",
                        "Số lượng khách tại phòng {$phong->tenPhong} vượt quá sức chứa tối đa ({$sucChuaToiDa} người)."
                    );
                }

                $tongSucChua += $sucChuaToiDa;
                $tongKhachCacPhong += $soKhach;
            }

            // 4. Kiểm tra tổng số khách so với tổng sức chứa của các phòng đã chọn
            $soLuongKhach = (int) $this->input('soLuongKhach', 0);
            if ($soLuongKhach > 0 && $tongSucChua > 0 && $soLuongKhach > $tongSucChua) {
                $validator->errors()->add(
                    'soLuongKhach',
                    "Tổng số lượng khách ({$soLuongKhach}) vượt quá tổng sức chứa của các phòng đã chọn ({$tongSucChua} người)."
                );
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
