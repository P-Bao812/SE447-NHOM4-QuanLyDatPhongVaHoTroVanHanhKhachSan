<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuongPhongController;
use App\Http\Controllers\Tai_KhoanController;
use App\Http\Controllers\Loai_PhongController;
use App\Http\Controllers\Dich_VuController;
use App\Http\Controllers\Nhan_VienController;
use App\Http\Controllers\Yeu_Cau_Dich_VuController;
use App\Http\Controllers\Thong_BaoController;
use App\Http\Controllers\Khuyen_MaiController;
use App\Http\Controllers\MinibarTaiSanController;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong', 'time' => now()]);
});

// Nhóm API Quản lý Loại phòng & Cấu hình giá
Route::prefix('loai-phong')->group(function () {
    Route::get('/', [Loai_PhongController::class, 'index']);
    Route::get('/tien-nghi', [Loai_PhongController::class, 'danhSachTienNghi']);
    Route::get('/{id}', [Loai_PhongController::class, 'show']);
    Route::post('/', [Loai_PhongController::class, 'store']);
    Route::match(['put', 'post'], '/{id}', [Loai_PhongController::class, 'update']);
    Route::match(['put', 'post'], '/{id}/trang-thai', [Loai_PhongController::class, 'doiTrangThai']);
    Route::delete('/{id}', [Loai_PhongController::class, 'destroy']);
});

// Nhóm API Quản lý Tài khoản & Phân quyền
Route::prefix('tai-khoan')->group(function () {
    Route::get('/', [Tai_KhoanController::class, 'index']);
    Route::get('/vai-tro', [Tai_KhoanController::class, 'danhSachVaiTro']);
    Route::get('/{id}', [Tai_KhoanController::class, 'show']);
    Route::post('/', [Tai_KhoanController::class, 'store']);
    Route::match(['put', 'post'], '/{id}/vai-tro', [Tai_KhoanController::class, 'capNhatVaiTro']);
    Route::match(['put', 'post'], '/{id}/thong-tin', [Tai_KhoanController::class, 'capNhatThongTin']);
    Route::match(['put', 'post'], '/{id}/trang-thai', [Tai_KhoanController::class, 'capNhatTrangThai']);
});

// Nhóm API Nghiệp vụ Quản lý buồng phòng & Vận hành hằng ngày
Route::prefix('buong-phong')->group(function () {
    // 4 Chức năng chính theo yêu cầu đề bài
    Route::get('/phong-can-ve-sinh', [BuongPhongController::class, 'danhSachPhongCanVeSinh']);
    Route::post('/cap-nhat-trang-thai-phong', [BuongPhongController::class, 'capNhatTrangThaiPhong']);
    Route::post('/kiem-tra-phong', [BuongPhongController::class, 'kiemTraPhong']);
    Route::post('/bao-cao-hoan-thanh', [BuongPhongController::class, 'baoCaoHoanThanh']);

    // Các chức năng hỗ trợ Kanban và quản lý ca làm việc
    Route::get('/cong-viec', [BuongPhongController::class, 'danhSachCongViec']);
    Route::post('/tao-cong-viec', [BuongPhongController::class, 'taoCongViec']);
    Route::post('/chuyen-trang-thai', [BuongPhongController::class, 'chuyenTrangThai']);
    Route::get('/thong-ke-ca', [BuongPhongController::class, 'thongKeCa']);
    Route::get('/phong', [BuongPhongController::class, 'danhSachPhong']);
    Route::get('/nhan-vien', [BuongPhongController::class, 'danhSachNhanVien']);
    Route::get('/danh-muc-dich-vu', [BuongPhongController::class, 'danhMucDichVu']);
});

// Nhóm API Dịch vụ khách sạn & Đăng ký sử dụng dịch vụ phòng (Dành cho Khách hàng & Vận hành)
Route::prefix('dich-vu')->group(function () {
    Route::get('/', [Dich_VuController::class, 'index']); // Lấy danh sách dịch vụ đang cung cấp + tìm kiếm + lọc loại
    Route::get('/loai-dich-vu', [Dich_VuController::class, 'danhSachLoaiDichVu']);
    Route::get('/phong-dang-luu-tru', [Dich_VuController::class, 'danhSachPhongDangLuuTru']); // Kiểm tra phòng lưu trú hợp lệ
    Route::post('/dang-ky', [Dich_VuController::class, 'dangKyDichVu']); // Đăng ký sử dụng dịch vụ
    Route::get('/yeu-cau', [Dich_VuController::class, 'danhSachYeuCau']); // Xem danh sách & trạng thái yêu cầu
    Route::post('/yeu-cau/{id}/trang-thai', [Dich_VuController::class, 'capNhatTrangThai']); // Cập nhật trạng thái & cộng vào hóa đơn khi Hoàn thành
    Route::match(['post', 'delete'], '/yeu-cau/{id}/huy', [Dich_VuController::class, 'huyYeuCau']); // Khách hủy yêu cầu
});

Route::prefix('yeu-cau-dich-vu')->group(function () {
    Route::get('/', [Yeu_Cau_Dich_VuController::class, 'index']);
    Route::post('/', [Yeu_Cau_Dich_VuController::class, 'store']);
    Route::get('/nhan-vien-phan-cong', [Yeu_Cau_Dich_VuController::class, 'danhSachNhanVienPhanCong']);
    Route::get('/{id}', [Yeu_Cau_Dich_VuController::class, 'show']);
    Route::post('/{id}/tiep-nhan', [Yeu_Cau_Dich_VuController::class, 'tiepNhan']);
    Route::post('/{id}/tu-choi', [Yeu_Cau_Dich_VuController::class, 'tuChoi']);
    Route::post('/{id}/huy', [Yeu_Cau_Dich_VuController::class, 'huyYeuCau']);
    Route::post('/{id}/phan-cong', [Yeu_Cau_Dich_VuController::class, 'phanCong']);
    Route::post('/{id}/nhan-viec', [Yeu_Cau_Dich_VuController::class, 'nhanViec']);
    Route::post('/{id}/bat-dau', [Yeu_Cau_Dich_VuController::class, 'batDauThucHien']);
    Route::post('/{id}/hoan-thanh', [Yeu_Cau_Dich_VuController::class, 'hoanThanh']);
    Route::post('/{id}/bao-su-co', [Yeu_Cau_Dich_VuController::class, 'baoSuCo']);
    Route::post('/{id}/trang-thai', [Dich_VuController::class, 'capNhatTrangThai']);
    Route::match(['post', 'delete'], '/{id}/huy', [Dich_VuController::class, 'huyYeuCau']);
});

// Nhóm API Quản lý Yêu cầu dịch vụ (Admin & Lễ tân)
Route::prefix('quan-ly-yeu-cau-dich-vu')->group(function () {
    Route::get('/', [Yeu_Cau_Dich_VuController::class, 'index']);
    Route::post('/', [Yeu_Cau_Dich_VuController::class, 'store']);
    Route::get('/nhan-vien-phan-cong', [Yeu_Cau_Dich_VuController::class, 'danhSachNhanVienPhanCong']);
    Route::get('/{id}', [Yeu_Cau_Dich_VuController::class, 'show']);
    Route::post('/{id}/tiep-nhan', [Yeu_Cau_Dich_VuController::class, 'tiepNhan']);
    Route::post('/{id}/tu-choi', [Yeu_Cau_Dich_VuController::class, 'tuChoi']);
    Route::post('/{id}/huy', [Yeu_Cau_Dich_VuController::class, 'huyYeuCau']);
    Route::post('/{id}/phan-cong', [Yeu_Cau_Dich_VuController::class, 'phanCong']);
    Route::post('/{id}/nhan-viec', [Yeu_Cau_Dich_VuController::class, 'nhanViec']);
    Route::post('/{id}/bat-dau', [Yeu_Cau_Dich_VuController::class, 'batDauThucHien']);
    Route::post('/{id}/hoan-thanh', [Yeu_Cau_Dich_VuController::class, 'hoanThanh']);
    Route::post('/{id}/bao-su-co', [Yeu_Cau_Dich_VuController::class, 'baoSuCo']);
});

// Nhóm API Hệ thống thông báo nội bộ
Route::prefix('thong-bao')->group(function () {
    Route::get('/', [Thong_BaoController::class, 'index']);
    Route::post('/doc-tat-ca', [Thong_BaoController::class, 'markAllAsRead']);
    Route::post('/{id}/da-xem', [Thong_BaoController::class, 'markAsRead']);
});

// Nhóm API Quản lý Nhân viên
Route::prefix('nhan-vien')->group(function () {
    Route::get('/', [Nhan_VienController::class, 'index']); // Lấy danh sách, tìm kiếm & bộ lọc
    Route::get('/thong-ke', [Nhan_VienController::class, 'thongKe']); // 3 Thẻ thống kê: Tổng NV, Đang trong ca, Ca hiện tại
    Route::get('/{id}', [Nhan_VienController::class, 'show']); // Chi tiết nhân viên
    Route::post('/', [Nhan_VienController::class, 'store']); // Thêm mới nhân viên
    Route::match(['put', 'post'], '/{id}', [Nhan_VienController::class, 'update']); // Cập nhật thông tin
    Route::match(['put', 'post'], '/{id}/trang-thai', [Nhan_VienController::class, 'capNhatTrangThai']); // Đổi trạng thái làm việc
    Route::delete('/{id}', [Nhan_VienController::class, 'destroy']); // Xóa / Vô hiệu hóa
});

// Nhóm API Quản lý Khuyến mãi (Bộ nhớ tạm/Cache - Không lưu DB danh sách)
Route::prefix('khuyen-mai')->group(function () {
    Route::get('/', [Khuyen_MaiController::class, 'index']); // Danh sách + Lọc + Thống kê
    Route::post('/', [Khuyen_MaiController::class, 'store']); // Tạo mới chương trình
    Route::post('/kiem-tra', [Khuyen_MaiController::class, 'kiemTra']); // Khách kiểm tra mã khuyến mãi
    Route::post('/xem-truoc', [Khuyen_MaiController::class, 'xemTruoc']); // Widget xem trước cách tính
    Route::post('/thanh-toan-hoa-don', [Khuyen_MaiController::class, 'thanhToanHoaDon']); // Thanh toán hóa đơn thực tế
    Route::get('/loai-phong/{id}', [Khuyen_MaiController::class, 'getKhuyenMaiTheoLoaiPhong']); // Khuyến mãi cho từng loại phòng
    Route::post('/thiet-lap-loai-phong', [Khuyen_MaiController::class, 'thietLapKhuyenMaiLoaiPhong']); // Thiết lập khuyến mãi trực tiếp từ Card
    Route::get('/{id}', [Khuyen_MaiController::class, 'show']); // Chi tiết
    Route::match(['put', 'post'], '/{id}', [Khuyen_MaiController::class, 'update']); // Sửa chương trình
    Route::post('/{id}/toggle', [Khuyen_MaiController::class, 'toggle']); // Bật / Tắt khuyến mãi
    Route::delete('/{id}', [Khuyen_MaiController::class, 'destroy']); // Xóa chương trình
});

Route::prefix('quan-ly-khuyen-mai')->group(function () {
    Route::get('/', [Khuyen_MaiController::class, 'index']);
    Route::post('/', [Khuyen_MaiController::class, 'store']);
    Route::post('/kiem-tra', [Khuyen_MaiController::class, 'kiemTra']);
    Route::post('/xem-truoc', [Khuyen_MaiController::class, 'xemTruoc']);
    Route::post('/thanh-toan-hoa-don', [Khuyen_MaiController::class, 'thanhToanHoaDon']);
    Route::get('/loai-phong/{id}', [Khuyen_MaiController::class, 'getKhuyenMaiTheoLoaiPhong']);
    Route::post('/thiet-lap-loai-phong', [Khuyen_MaiController::class, 'thietLapKhuyenMaiLoaiPhong']);
    Route::get('/{id}', [Khuyen_MaiController::class, 'show']);
    Route::match(['put', 'post'], '/{id}', [Khuyen_MaiController::class, 'update']);
    Route::post('/{id}/toggle', [Khuyen_MaiController::class, 'toggle']);
    Route::delete('/{id}', [Khuyen_MaiController::class, 'destroy']);
});

// Nhóm API Quản lý Minibar & Tài sản phòng (FR24)
Route::prefix('minibar-tai-san')->group(function () {
    // Tab 1: Minibar & Tồn kho
    Route::get('/san-pham', [MinibarTaiSanController::class, 'danhSachSanPham']);
    Route::post('/san-pham', [MinibarTaiSanController::class, 'themSanPham']);
    Route::match(['put', 'post'], '/san-pham/{id}', [MinibarTaiSanController::class, 'capNhatSanPham']);
    Route::post('/nhap-kho', [MinibarTaiSanController::class, 'nhapKhoTong']);
    Route::get('/phong', [MinibarTaiSanController::class, 'danhSachPhong']);
    Route::get('/phong/{maPhong}/minibar', [MinibarTaiSanController::class, 'chiTietMinibarPhong']);
    Route::post('/chuan-bi-checkin', [MinibarTaiSanController::class, 'chuanBiCheckIn']);
    Route::post('/bo-sung-phong', [MinibarTaiSanController::class, 'boSungMinibar']);
    Route::post('/kiem-ke-checkout', [MinibarTaiSanController::class, 'kiemKeCheckOut']);

    // Tab 2: Tài sản phòng
    Route::get('/tai-san', [MinibarTaiSanController::class, 'danhSachTaiSan']);
    Route::post('/tai-san', [MinibarTaiSanController::class, 'themTaiSan']);
    Route::post('/bao-hong-tai-san', [MinibarTaiSanController::class, 'baoHongTaiSan']);

    // Tab 3: Nhật ký kiểm kê
    Route::get('/nhat-ky', [MinibarTaiSanController::class, 'danhSachNhatKy']);
    Route::get('/nhat-ky/{id}', [MinibarTaiSanController::class, 'chiTietNhatKy']);
});
