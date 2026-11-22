<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NhanVienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $nhanViens = [
            [
                'id' => 1,
                'maNhanVien' => 'NV-001',
                'maTaiKhoan' => 1,
                'hoTen' => 'Trần An',
                'email' => 'an@example.com',
                'ngaySinh' => '1985-04-12',
                'cccd' => '001185001111',
                'soDienThoai' => '0905123456',
                'chucVu' => 'Giám đốc điều hành',
                'boPhan' => 'Ban Quản lý',
                'caLam' => 'Hành chính (08:00 - 17:00)',
                'ngayGiaNhap' => '2023-01-10',
                'nguoiQuanLy' => 'Hội đồng quản trị',
                'ghiChu' => 'Phụ trách chung toàn bộ vận hành và chiến lược kinh doanh của khách sạn.',
                'trangThai' => 'Đang trong ca',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'maNhanVien' => 'NV-002',
                'maTaiKhoan' => 2,
                'hoTen' => 'Mai Linh',
                'email' => 'linh@example.com',
                'ngaySinh' => '1998-08-20',
                'cccd' => '001198002222',
                'soDienThoai' => '0912345678',
                'chucVu' => 'Trưởng ca Lễ tân',
                'boPhan' => 'Lễ tân',
                'caLam' => 'Ca sáng (06:00 - 14:00)',
                'ngayGiaNhap' => '2023-06-15',
                'nguoiQuanLy' => 'Trần An (Giám đốc)',
                'ghiChu' => 'Điều phối check-in, check-out, hỗ trợ đón tiếp khách VIP và xử lý sự vụ tại quầy.',
                'trangThai' => 'Đang trong ca',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'maNhanVien' => 'NV-003',
                'maTaiKhoan' => 3,
                'hoTen' => 'Nguyễn Hương',
                'email' => 'huong@example.com',
                'ngaySinh' => '1995-11-05',
                'cccd' => '001195003333',
                'soDienThoai' => '0935111222',
                'chucVu' => 'Tổ trưởng Buồng phòng',
                'boPhan' => 'Buồng phòng',
                'caLam' => 'Ca sáng (06:00 - 14:00)',
                'ngayGiaNhap' => '2023-09-01',
                'nguoiQuanLy' => 'Trần An (Giám đốc)',
                'ghiChu' => 'Phân công nhân sự dọn phòng, kiểm tra chất lượng phòng sạch trước khi bàn giao.',
                'trangThai' => 'Đang trong ca',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'maNhanVien' => 'NV-004',
                'maTaiKhoan' => 4,
                'hoTen' => 'Trần Hoa',
                'email' => 'hoa@example.com',
                'ngaySinh' => '1996-03-15',
                'cccd' => '001196004444',
                'soDienThoai' => '0945222333',
                'chucVu' => 'Nhân viên Buồng phòng',
                'boPhan' => 'Buồng phòng',
                'caLam' => 'Ca chiều (14:00 - 22:00)',
                'ngayGiaNhap' => '2024-02-15',
                'nguoiQuanLy' => 'Nguyễn Hương (Tổ trưởng)',
                'ghiChu' => 'Phụ trách vệ sinh tầng 2 và tầng 3, kiểm tra minibar và tài sản phòng.',
                'trangThai' => 'Nghỉ ca',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'maNhanVien' => 'NV-005',
                'maTaiKhoan' => 5,
                'hoTen' => 'Lê Ngọc',
                'email' => 'ngoc@example.com',
                'ngaySinh' => '1992-09-10',
                'cccd' => '001192005555',
                'soDienThoai' => '0978333444',
                'chucVu' => 'Kế toán trưởng',
                'boPhan' => 'Kế toán & Thu ngân',
                'caLam' => 'Hành chính (08:00 - 17:00)',
                'ngayGiaNhap' => '2023-04-20',
                'nguoiQuanLy' => 'Trần An (Giám đốc)',
                'ghiChu' => 'Quản lý thu chi, đối soát hóa đơn buồng phòng và dịch vụ, báo cáo tài chính tháng.',
                'trangThai' => 'Đang trong ca',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'maNhanVien' => 'NV-006',
                'maTaiKhoan' => 6,
                'hoTen' => 'Đỗ Bình',
                'email' => 'binh@example.com',
                'ngaySinh' => '1997-06-25',
                'cccd' => '001197006666',
                'soDienThoai' => '0989444555',
                'chucVu' => 'Nhân viên Lễ tân',
                'boPhan' => 'Lễ tân',
                'caLam' => 'Ca đêm (22:00 - 06:00)',
                'ngayGiaNhap' => '2024-05-10',
                'nguoiQuanLy' => 'Mai Linh (Trưởng ca)',
                'ghiChu' => 'Đã hoàn tất bàn giao công việc trước khi nghỉ.',
                'trangThai' => 'Đã nghỉ việc',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($nhanViens as $nv) {
            DB::table('Nhan_Vien')->updateOrInsert(
                ['id' => $nv['id']],
                $nv
            );
        }
    }
}
