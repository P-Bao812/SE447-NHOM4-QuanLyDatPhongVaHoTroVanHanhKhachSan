<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Loai_Phong;
use App\Models\Phong;
use App\Models\Nhan_Vien;
use App\Models\Tai_Khoan;
use App\Models\San_Pham_Minibar;
use App\Models\Tai_San;
use App\Models\Phieu_Buong_Phong;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuongPhongSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo loại phòng
        $lp1 = Loai_Phong::firstOrCreate(
            ['tenLoai' => 'Phòng Superior Giường Đôi'],
            ['moTa' => '25m2, giường King, cửa sổ thoáng mát', 'giaTieuChuan' => 850000, 'sucChuaToiDa' => 2, 'trangThai' => 'Hoat_Dong']
        );
        $lp2 = Loai_Phong::firstOrCreate(
            ['tenLoai' => 'Phòng Deluxe Hướng Phố'],
            ['moTa' => '32m2, ban công nhìn phố cổ', 'giaTieuChuan' => 1200000, 'sucChuaToiDa' => 2, 'trangThai' => 'Hoat_Dong']
        );
        $lp3 = Loai_Phong::firstOrCreate(
            ['tenLoai' => 'Suite An Nhiên Ban Công'],
            ['moTa' => '45m2, bồn tắm nằm, view vườn và sông', 'giaTieuChuan' => 1800000, 'sucChuaToiDa' => 3, 'trangThai' => 'Hoat_Dong']
        );

        // 2. Tạo nhân viên
        $nv1 = Nhan_Vien::firstOrCreate(
            ['cccd' => '048190001111'],
            ['maTaiKhoan' => 1, 'hoTen' => 'Chị Hương', 'ngaySinh' => '1988-04-12', 'soDienThoai' => '0905111222', 'chucVu' => 'Tổ trưởng buồng phòng', 'trangThai' => 'Dang_Lam']
        );
        $nv2 = Nhan_Vien::firstOrCreate(
            ['cccd' => '048190002222'],
            ['maTaiKhoan' => 2, 'hoTen' => 'Chị Hoa', 'ngaySinh' => '1992-08-20', 'soDienThoai' => '0905333444', 'chucVu' => 'Nhân viên buồng phòng', 'trangThai' => 'Dang_Lam']
        );
        $nv3 = Nhan_Vien::firstOrCreate(
            ['cccd' => '048190003333'],
            ['maTaiKhoan' => 3, 'hoTen' => 'Anh Tuấn', 'ngaySinh' => '1985-11-05', 'soDienThoai' => '0905555666', 'chucVu' => 'Nhân viên kỹ thuật', 'trangThai' => 'Dang_Lam']
        );
        $nv4 = Nhan_Vien::firstOrCreate(
            ['cccd' => '048190004444'],
            ['maTaiKhoan' => 4, 'hoTen' => 'Mai Linh', 'ngaySinh' => '1996-01-15', 'soDienThoai' => '0905777888', 'chucVu' => 'Lễ tân ca sáng', 'trangThai' => 'Dang_Lam']
        );

        // 3. Tạo phòng (20 phòng khách sạn An Nhiên Hội An)
        $roomsData = [
            // Tầng 3
            ['tenPhong' => 'Phòng 301', 'tang' => 3, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp1->id, 'ghiChuDonDep' => 'Đã sẵn sàng đón khách'],
            ['tenPhong' => 'Phòng 302', 'tang' => 3, 'trangThai' => 'Dang_Su_Dung', 'maLoaiPhong' => $lp1->id, 'ghiChuDonDep' => 'Khách ở đêm thứ 2'],
            ['tenPhong' => 'Phòng 303', 'tang' => 3, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Đã kiểm tra đạt chuẩn lúc 09:40'],
            ['tenPhong' => 'Phòng 304', 'tang' => 3, 'trangThai' => 'Can_Don', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Khách trả phòng lúc 10:00, cần dọn trước 12:00'],
            ['tenPhong' => 'Phòng 305', 'tang' => 3, 'trangThai' => 'Dang_Don', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Đang dọn dẹp, dự kiến xong 11:15'],
            ['tenPhong' => 'Phòng 306', 'tang' => 3, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],

            // Tầng 4
            ['tenPhong' => 'Phòng 401', 'tang' => 4, 'trangThai' => 'Dang_Su_Dung', 'maLoaiPhong' => $lp1->id, 'ghiChuDonDep' => 'Khách đoàn nước ngoài'],
            ['tenPhong' => 'Phòng 402', 'tang' => 4, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp1->id, 'ghiChuDonDep' => 'Đã bàn giao lễ tân lúc 09:20'],
            ['tenPhong' => 'Phòng 403', 'tang' => 4, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],
            ['tenPhong' => 'Phòng 404', 'tang' => 4, 'trangThai' => 'Can_Don', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Khách trả phòng lúc 10:15, ưu tiên vừa'],
            ['tenPhong' => 'Phòng 405', 'tang' => 4, 'trangThai' => 'Bao_Tri', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Bảo trì điều hòa không mát, khóa bán phòng'],
            ['tenPhong' => 'Phòng 406', 'tang' => 4, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],

            // Tầng 5
            ['tenPhong' => 'Phòng 501', 'tang' => 5, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp1->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],
            ['tenPhong' => 'Phòng 502', 'tang' => 5, 'trangThai' => 'Can_Don', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Check-out lúc 10:45, cần dọn trước 14:00'],
            ['tenPhong' => 'Phòng 503', 'tang' => 5, 'trangThai' => 'Dang_Su_Dung', 'maLoaiPhong' => $lp2->id, 'ghiChuDonDep' => 'Khách đang nghỉ ngơi'],
            ['tenPhong' => 'Phòng 504', 'tang' => 5, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],
            ['tenPhong' => 'Phòng 505', 'tang' => 5, 'trangThai' => 'Cho_Kiem_Tra', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Đã dọn xong, chờ lễ tân nghiệm thu'],
            ['tenPhong' => 'Phòng 506', 'tang' => 5, 'trangThai' => 'Trong_Sach', 'maLoaiPhong' => $lp3->id, 'ghiChuDonDep' => 'Sạch sẵn sàng'],
        ];

        $createdRooms = [];
        foreach ($roomsData as $rd) {
            $createdRooms[$rd['tenPhong']] = Phong::updateOrCreate(
                ['tenPhong' => $rd['tenPhong']],
                $rd
            );
        }

        // 4. Tạo sản phẩm Minibar
        $minibarItems = [
            ['tenSanPham' => 'Nước suối Lavie 500ml', 'donGia' => 15000, 'donViTinh' => 'Chai', 'trangThai' => 'Con_Hang'],
            ['tenSanPham' => 'Coca Cola lon 330ml', 'donGia' => 25000, 'donViTinh' => 'Lon', 'trangThai' => 'Con_Hang'],
            ['tenSanPham' => 'Bia Larue Hội An 330ml', 'donGia' => 30000, 'donViTinh' => 'Lon', 'trangThai' => 'Con_Hang'],
            ['tenSanPham' => 'Snack khoai tây Lays', 'donGia' => 25000, 'donViTinh' => 'Gói', 'trangThai' => 'Con_Hang'],
            ['tenSanPham' => 'Hạt điều rang muối 50g', 'donGia' => 45000, 'donViTinh' => 'Hũ', 'trangThai' => 'Con_Hang'],
        ];
        foreach ($minibarItems as $m) {
            San_Pham_Minibar::firstOrCreate(['tenSanPham' => $m['tenSanPham']], $m);
        }

        // 5. Tạo tài sản mẫu
        $taiSanItems = [
            ['tenTaiSan' => 'Khăn tắm lớn cao cấp', 'tinhTrang' => 'Tốt', 'giaTri' => 150000, 'maPhong' => $createdRooms['Phòng 304']->id ?? 1],
            ['tenTaiSan' => 'Máy sấy tóc Panasonic', 'tinhTrang' => 'Tốt', 'giaTri' => 450000, 'maPhong' => $createdRooms['Phòng 304']->id ?? 1],
            ['tenTaiSan' => 'Điều khiển điều hòa Daikin', 'tinhTrang' => 'Tốt', 'giaTri' => 200000, 'maPhong' => $createdRooms['Phòng 405']->id ?? 2],
            ['tenTaiSan' => 'Ấm đun nước siêu tốc', 'tinhTrang' => 'Tốt', 'giaTri' => 350000, 'maPhong' => $createdRooms['Phòng 305']->id ?? 3],
        ];
        foreach ($taiSanItems as $ts) {
            Tai_San::firstOrCreate(['tenTaiSan' => $ts['tenTaiSan'], 'maPhong' => $ts['maPhong']], $ts);
        }

        // 6. Tạo 8 công việc vận hành mẫu
        DB::table('Phieu_Buong_Phong')->truncate();

        $tasks = [
            // CHƯA LÀM (pending)
            [
                'maPhong' => $createdRooms['Phòng 304']->id ?? 4,
                'viTri' => 'Phòng 304',
                'maNhanVien' => $nv1->id,
                'loaiCongViec' => 'Dọn phòng',
                'mucDoUuTien' => 'Cao',
                'tieuDe' => 'Dọn phòng sau trả khách',
                'ghiChu' => 'Thay ga giường, bổ sung khăn và minibar.',
                'thoiHan' => 'Trước 12:00',
                'chiTietTienDo' => null,
                'trangThai' => 'pending',
                'ngayTao' => Carbon::now()->subHours(2)
            ],
            [
                'maPhong' => $createdRooms['Phòng 404']->id ?? 10,
                'viTri' => 'Phòng 404',
                'maNhanVien' => $nv1->id,
                'loaiCongViec' => 'Dọn phòng',
                'mucDoUuTien' => 'Vừa',
                'tieuDe' => 'Dọn phòng sau trả khách',
                'ghiChu' => 'Vệ sinh phòng tắm, hút bụi và thay ga.',
                'thoiHan' => 'Trước 13:00',
                'chiTietTienDo' => null,
                'trangThai' => 'pending',
                'ngayTao' => Carbon::now()->subHours(1)
            ],
            [
                'maPhong' => null,
                'viTri' => 'Kho tầng 2',
                'maNhanVien' => $nv2->id,
                'loaiCongViec' => 'Hàng hóa',
                'mucDoUuTien' => 'Thấp',
                'tieuDe' => 'Kiểm kê khăn tắm và dầu gội',
                'ghiChu' => 'Đếm số lượng còn lại và báo lại lễ tân.',
                'thoiHan' => 'Trước 15:00',
                'chiTietTienDo' => null,
                'trangThai' => 'pending',
                'ngayTao' => Carbon::now()->subMinutes(45)
            ],

            // ĐANG THỰC HIỆN (doing)
            [
                'maPhong' => $createdRooms['Phòng 305']->id ?? 5,
                'viTri' => 'Phòng 305',
                'maNhanVien' => $nv2->id,
                'loaiCongViec' => 'Dọn phòng',
                'mucDoUuTien' => 'Cao',
                'tieuDe' => 'Dọn phòng sau trả khách',
                'ghiChu' => 'Đã thay ga. Đang vệ sinh phòng tắm.',
                'chiTietTienDo' => 'Bắt đầu 10:10 · 20 phút',
                'thoiHan' => 'Xong lúc 11:15',
                'trangThai' => 'doing',
                'ngayTao' => Carbon::now()->subMinutes(30)
            ],
            [
                'maPhong' => $createdRooms['Phòng 405']->id ?? 11,
                'viTri' => 'Phòng 405',
                'maNhanVien' => $nv3->id,
                'loaiCongViec' => 'Bảo trì',
                'mucDoUuTien' => 'Cao',
                'tieuDe' => 'Kiểm tra điều hòa không mát',
                'ghiChu' => 'Vệ sinh bộ lọc, kiểm tra gas và chạy thử.',
                'chiTietTienDo' => 'Bắt đầu 09:30 · Khóa bán phòng',
                'thoiHan' => 'Xong lúc 15:00',
                'trangThai' => 'doing',
                'ngayTao' => Carbon::now()->subHours(2)
            ],

            // HOÀN THÀNH (done)
            [
                'maPhong' => $createdRooms['Phòng 301']->id ?? 1,
                'viTri' => 'Phòng 301',
                'maNhanVien' => $nv1->id,
                'loaiCongViec' => 'Dọn phòng',
                'mucDoUuTien' => 'Cao',
                'tieuDe' => 'Chuẩn bị phòng đón khách',
                'ghiChu' => 'Đã bàn giao lễ tân. Khách nhận lúc 09:15.',
                'thoiHan' => 'Hoàn thành 08:45',
                'chiTietTienDo' => null,
                'trangThai' => 'done',
                'ngayTao' => Carbon::now()->subHours(4),
                'thoiGianHoanThanh' => Carbon::now()->subHours(3)
            ],
            [
                'maPhong' => $createdRooms['Phòng 402']->id ?? 8,
                'viTri' => 'Phòng 402',
                'maNhanVien' => $nv2->id,
                'loaiCongViec' => 'Dọn phòng',
                'mucDoUuTien' => 'Vừa',
                'tieuDe' => 'Chuẩn bị phòng đón khách',
                'ghiChu' => 'Đã bàn giao lễ tân. Khách nhận lúc 10:00.',
                'thoiHan' => 'Hoàn thành 09:20',
                'chiTietTienDo' => null,
                'trangThai' => 'done',
                'ngayTao' => Carbon::now()->subHours(3),
                'thoiGianHoanThanh' => Carbon::now()->subHours(2)
            ],
            [
                'maPhong' => $createdRooms['Phòng 303']->id ?? 3,
                'viTri' => 'Phòng 303',
                'maNhanVien' => $nv4->id,
                'loaiCongViec' => 'Kiểm tra phòng',
                'mucDoUuTien' => 'Vừa',
                'tieuDe' => 'Kiểm tra phòng',
                'ghiChu' => 'Kiểm tra buồng phòng trước giờ check-in.',
                'thoiHan' => 'Hoàn thành 09:40',
                'chiTietTienDo' => null,
                'ketQuaKiemTra' => 'Dat',
                'nguoiKiemTra' => 'Mai Linh',
                'trangThai' => 'done',
                'ngayTao' => Carbon::now()->subHours(2),
                'thoiGianHoanThanh' => Carbon::now()->subHours(1)
            ]
        ];

        foreach ($tasks as $t) {
            Phieu_Buong_Phong::create($t);
        }
    }
}
