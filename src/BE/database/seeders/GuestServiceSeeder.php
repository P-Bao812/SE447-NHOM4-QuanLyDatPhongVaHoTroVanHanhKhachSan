<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GuestServiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Khách hàng
        $khachHangs = [
            [
                'id' => 1,
                'maTaiKhoan' => 1,
                'hoTen' => 'Nguyễn Văn Minh',
                'cccd' => '079201001234',
                'email' => 'minh.nguyen@example.com',
                'soDienThoai' => '0905123456',
                'diaChi' => '123 Lê Lợi, Phường Bến Nghé, Quận 1, TP.HCM',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'maTaiKhoan' => 2,
                'hoTen' => 'Trần Thị Mai Linh',
                'cccd' => '079201005678',
                'email' => 'linh.tran@example.com',
                'soDienThoai' => '0938765432',
                'diaChi' => '456 Trần Phú, Quận Hải Châu, TP.Đà Nẵng',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'maTaiKhoan' => 3,
                'hoTen' => 'Lê Hoàng Nam',
                'cccd' => '079201009876',
                'email' => 'nam.le@example.com',
                'soDienThoai' => '0918999888',
                'diaChi' => '78 Phan Bội Châu, TP.Hội An, Quảng Nam',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'maTaiKhoan' => 4,
                'hoTen' => 'Phạm Quốc Hùng (Đã trả phòng)',
                'cccd' => '079201004321',
                'email' => 'hung.pham@example.com',
                'soDienThoai' => '0977112233',
                'diaChi' => '12 Hai Bà Trưng, Quận Hoàn Kiếm, Hà Nội',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(5),
            ],
        ];

        foreach ($khachHangs as $kh) {
            DB::table('Khach_Hang')->updateOrInsert(['id' => $kh['id']], $kh);
        }

        // Lấy ID phòng mẫu: 101, 102, 201
        $p101 = DB::table('Phong')->where('tenPhong', '101')->first() ?? DB::table('Phong')->first();
        $p102 = DB::table('Phong')->where('tenPhong', '102')->first() ?? DB::table('Phong')->skip(1)->first();
        $p201 = DB::table('Phong')->where('tenPhong', '201')->first() ?? DB::table('Phong')->skip(2)->first();
        $p202 = DB::table('Phong')->where('tenPhong', '202')->first() ?? DB::table('Phong')->skip(3)->first();

        $id101 = $p101 ? $p101->id : 19;
        $id102 = $p102 ? $p102->id : 20;
        $id201 = $p201 ? $p201->id : 24;
        $id202 = $p202 ? $p202->id : 25;

        // 2. Đặt phòng
        $datPhongs = [
            [
                'id' => 1,
                'maKhachHang' => 1,
                'maNhanVien' => 1,
                'ngayDat' => $now->copy()->subDays(2),
                'ngayNhanPhong' => $now->copy()->subDays(1),
                'ngayTraPhong' => $now->copy()->addDays(2),
                'soLuongKhach' => 2,
                'tienCoc' => 1000000,
                'trangThai' => 'Đang lưu trú',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'maKhachHang' => 2,
                'maNhanVien' => 1,
                'ngayDat' => $now->copy()->subDays(1),
                'ngayNhanPhong' => $now->copy()->subHours(6),
                'ngayTraPhong' => $now->copy()->addDays(3),
                'soLuongKhach' => 1,
                'tienCoc' => 800000,
                'trangThai' => 'Đang lưu trú',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'maKhachHang' => 3,
                'maNhanVien' => 1,
                'ngayDat' => $now->copy()->subDays(1),
                'ngayNhanPhong' => $now->copy()->subHours(2),
                'ngayTraPhong' => $now->copy()->addDays(1),
                'soLuongKhach' => 2,
                'tienCoc' => 500000,
                'trangThai' => 'Đang lưu trú',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'maKhachHang' => 4,
                'maNhanVien' => 1,
                'ngayDat' => $now->copy()->subDays(10),
                'ngayNhanPhong' => $now->copy()->subDays(8),
                'ngayTraPhong' => $now->copy()->subDays(4),
                'soLuongKhach' => 2,
                'tienCoc' => 1200000,
                'trangThai' => 'Đã trả phòng',
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(4),
            ],
        ];

        foreach ($datPhongs as $dp) {
            DB::table('Dat_Phong')->updateOrInsert(['id' => $dp['id']], $dp);
        }

        // 3. Chi tiết đặt phòng
        $chiTietDatPhongs = [
            [
                'id' => 1,
                'maDatPhong' => 1,
                'maPhong' => $id101,
                'giaPhongApDung' => 1200000,
                'soLuongKhach' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'maDatPhong' => 2,
                'maPhong' => $id102,
                'giaPhongApDung' => 1500000,
                'soLuongKhach' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'maDatPhong' => 3,
                'maPhong' => $id201,
                'giaPhongApDung' => 2000000,
                'soLuongKhach' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'maDatPhong' => 4,
                'maPhong' => $id202,
                'giaPhongApDung' => 1200000,
                'soLuongKhach' => 2,
                'created_at' => $now->copy()->subDays(10),
                'updated_at' => $now->copy()->subDays(4),
            ],
        ];

        foreach ($chiTietDatPhongs as $ctdp) {
            DB::table('Chi_Tiet_Dat_Phong')->updateOrInsert(['id' => $ctdp['id']], $ctdp);
        }

        // 4. Lưu trú
        $luuTrus = [
            [
                'id' => 1,
                'maChiTiet' => 1, // Phòng 101 - Nguyễn Văn Minh
                'thoiGianNhan' => $now->copy()->subDays(1)->setHour(14)->setMinute(0),
                'thoiGianTra' => $now->copy()->addDays(2)->setHour(12)->setMinute(0),
                'trangThai' => 'Dang_Luu_Tru',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'maChiTiet' => 2, // Phòng 102 - Trần Thị Mai Linh
                'thoiGianNhan' => $now->copy()->subHours(6),
                'thoiGianTra' => $now->copy()->addDays(3)->setHour(12)->setMinute(0),
                'trangThai' => 'Dang_Luu_Tru',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'maChiTiet' => 3, // Phòng 201 - Lê Hoàng Nam
                'thoiGianNhan' => $now->copy()->subHours(2),
                'thoiGianTra' => $now->copy()->addDays(1)->setHour(12)->setMinute(0),
                'trangThai' => 'Dang_Luu_Tru',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'maChiTiet' => 4, // Đã hết hạn lưu trú
                'thoiGianNhan' => $now->copy()->subDays(8),
                'thoiGianTra' => $now->copy()->subDays(4),
                'trangThai' => 'Da_Tra_Phong',
                'created_at' => $now->copy()->subDays(8),
                'updated_at' => $now->copy()->subDays(4),
            ],
        ];

        foreach ($luuTrus as $lt) {
            DB::table('Luu_Tru')->updateOrInsert(['id' => $lt['id']], $lt);
        }

        // 5. Khởi tạo một số yêu cầu mẫu thực tế để khách hàng thấy danh sách lịch sử ban đầu
        $yeuCaus = [
            [
                'id' => 1,
                'maLuuTru' => 1,
                'maDichVu' => 1, // Ăn uống tại phòng
                'soLuong' => 2,
                'donGiaApDung' => 150000,
                'ngayYeuCau' => $now->copy()->subHours(3),
                'thoiGianMongMuon' => $now->copy()->subHours(2),
                'trangThai' => 'Hoan_Thanh',
                'ghiChu' => '2 suất bít tết chín vừa, nhiều tiêu đen',
                'created_at' => $now->copy()->subHours(3),
                'updated_at' => $now->copy()->subHours(2),
            ],
            [
                'id' => 2,
                'maLuuTru' => 1,
                'maDichVu' => 2, // Giặt ủi
                'soLuong' => 3,
                'donGiaApDung' => 50000,
                'ngayYeuCau' => $now->copy()->subMinutes(45),
                'thoiGianMongMuon' => $now->copy()->addHours(2),
                'trangThai' => 'Dang_Xu_Ly',
                'ghiChu' => 'Ủi phẳng 2 áo sơ mi trắng đi tiệc',
                'created_at' => $now->copy()->subMinutes(45),
                'updated_at' => $now->copy()->subMinutes(30),
            ],
            [
                'id' => 3,
                'maLuuTru' => 2,
                'maDichVu' => 5, // Spa và massage
                'soLuong' => 1,
                'donGiaApDung' => 350000,
                'ngayYeuCau' => $now->copy()->subMinutes(15),
                'thoiGianMongMuon' => $now->copy()->addHours(1),
                'trangThai' => 'Tiep_Nhan',
                'ghiChu' => 'Massage body đá nóng thư giãn',
                'created_at' => $now->copy()->subMinutes(15),
                'updated_at' => $now->copy()->subMinutes(15),
            ],
        ];

        foreach ($yeuCaus as $yc) {
            DB::table('Yeu_Cau_Dich_Vu')->updateOrInsert(['id' => $yc['id']], $yc);
        }

        // 6. Hóa đơn lưu trú cho Luu_Tru 1 (đã có dịch vụ 1 Hoàn thành)
        $hoaDon = [
            'id' => 1,
            'maLuuTru' => 1,
            'ngayLap' => $now,
            'tongTien' => 300000, // 2 * 150.000 = 300.000 đ
            'trangThai' => 'Chua_Thanh_Toan',
            'created_at' => $now,
            'updated_at' => $now,
        ];
        DB::table('Hoa_Don')->updateOrInsert(['id' => 1], $hoaDon);

        $chiTietHoaDon = [
            'id' => 1,
            'maHoaDon' => 1,
            'noiDung' => 'Dịch vụ: Ăn uống tại phòng (Phòng 101)',
            'soLuong' => 2,
            'donGia' => 150000,
            'thanhTien' => 300000,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        DB::table('Chi_Tiet_Hoa_Don')->updateOrInsert(['id' => 1], $chiTietHoaDon);
    }
}
