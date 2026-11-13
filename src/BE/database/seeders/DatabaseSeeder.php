<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // === Nhóm 1: Dữ liệu nền tảng & Độc lập (Không phụ thuộc bảng nào) ===
        $this->call([
            VaiTroSeeder::class,          // Vai trò người dùng
            QuyenSeeder::class,           // Quyền hạn chức năng
            LoaiPhongSeeder::class,       // Loại phòng (Single, Double, VIP)
            TienNghiSeeder::class,        // Tiện nghi (Tivi, Điều hòa, Wifi...)
            DichVuSeeder::class,          // Dịch vụ khách sạn (Giặt ủi, Spa...)
            SanPhamMinibarSeeder::class,  // Sản phẩm minibar (Nước suối, Snack...)
            KhuyenMaiSeeder::class,       // Chương trình khuyến mãi
        ]);

        // === Nhóm 2: Dữ liệu trung gian phụ thuộc Nhóm 1 ===
        $this->call([
            VaiTroQuyenSeeder::class,     // Phân quyền cho vai trò (Phụ thuộc VaiTro, Quyen)
            LoaiPhongTienNghiSeeder::class, // Tiện nghi của loại phòng (Phụ thuộc LoaiPhong, TienNghi)
        ]);

        // === Nhóm 3: Tài khoản hệ thống (Cần VaiTro) ===
        $this->call([
            TaiKhoanSeeder::class,        // Tài khoản đăng nhập (Phụ thuộc VaiTro)
        ]);

        // === Nhóm 4: Người dùng (Khách hàng & Nhân viên - Phụ thuộc TaiKhoan) ===
        $this->call([
            KhachHangSeeder::class,       // Khách hàng (Phụ thuộc TaiKhoan)
            NhanVienSeeder::class,        // Nhân viên (Phụ thuộc TaiKhoan)
        ]);

        // === Nhóm 5: Phòng thực tế & Tài sản (Phụ thuộc LoaiPhong, Phong) ===
        $this->call([
            PhongSeeder::class,           // Danh sách phòng thực tế (Phụ thuộc LoaiPhong)
            TaiSanSeeder::class,          // Tài sản trong phòng (Phụ thuộc Phong)
        ]);

        // === Nhóm 6: Giao dịch Đặt phòng & Lưu trú (Phụ thuộc KhachHang, NhanVien, Phong, KhuyenMai) ===
        $this->call([
            DatPhongSeeder::class,        // Đơn đặt phòng
            ChiTietDatPhongSeeder::class, // Chi tiết phòng trong đơn đặt
            LuuTruSeeder::class,          // Đợt lưu trú thực tế (Check-in)
            PhieuNhanTraPhongSeeder::class, // Phiếu thủ tục nhận/trả phòng
        ]);

        // === Nhóm 7: Nghiệp vụ phát sinh trong quá trình lưu trú ===
        $this->call([
            YeuCauDichVuSeeder::class,    // Khách gọi dịch vụ
            PhatSinhMinibarSeeder::class, // Khách dùng đồ minibar
            PhieuBuongPhongSeeder::class, // Phân công dọn phòng cho nhân viên
            ChiTietBuongPhongSeeder::class, // Chi tiết công việc buồng phòng
            GhiNhanTaiSanSeeder::class,   // Ghi nhận sự cố hư hỏng/mất tài sản
        ]);

        // === Nhóm 8: Tài chính, Hóa đơn & Đánh giá (Phụ thuộc LuuTru) ===
        $this->call([
            HoaDonSeeder::class,          // Hóa đơn thanh toán
            ChiTietHoaDonSeeder::class,   // Chi tiết khoản mục hóa đơn
            ThanhToanSeeder::class,       // Giao dịch thanh toán tiền
            HoanTienSeeder::class,        // Giao dịch hoàn tiền (nếu có)
            DanhGiaSeeder::class,         // Khách đánh giá sao
            PhanHoiSeeder::class,         // Khách sạn phản hồi đánh giá
        ]);
    }
}
