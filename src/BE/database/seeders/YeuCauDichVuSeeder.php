<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Yeu_Cau_Dich_Vu;
use App\Models\Dich_Vu;
use App\Models\Luu_Tru;
use App\Models\Phong;
use App\Models\Khach_Hang;
use App\Models\Phieu_Buong_Phong;
use App\Models\Thong_Bao;
use Carbon\Carbon;

class YeuCauDichVuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $dateStr = $now->format('Y-m-d');

        // Tìm hoặc đảm bảo phòng 301, 402, 102, 201, 103 tồn tại
        $phongMap = [];
        foreach (['301', '402', '102', '201', '103'] as $ten) {
            $p = Phong::where('tenPhong', $ten)->first();
            if (!$p) {
                $p = Phong::create([
                    'tenPhong' => $ten,
                    'maLoaiPhong' => 1,
                    'tang' => (int) substr($ten, 0, 1),
                    'trangThai' => 'Dang_Su_Dung'
                ]);
            }
            $phongMap[$ten] = $p;
        }

        // Tìm các lượt lưu trú mẫu
        $lt = Luu_Tru::first();
        $maLuuTruDefault = $lt ? $lt->id : 1;

        // Dọn dẹp dữ liệu yêu cầu cũ để seed bộ chuẩn theo giao diện ảnh mẫu
        Yeu_Cau_Dich_Vu::truncate();
        Thong_Bao::truncate();

        // 6 Yêu cầu mẫu theo đúng ảnh tham khảo
        $samples = [
            [
                'maYeuCauCode' => 'YC-0811-04',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 2, // Giặt ủi
                'soLuong' => 2,
                'donGiaApDung' => 40000,
                'ngayYeuCau' => Carbon::now()->setTime(9, 50),
                'thoiGianMongMuon' => Carbon::now()->addDay()->setTime(10, 0),
                'trangThai' => 'Da_Tiep_Nhan',
                'boPhanPhuTrach' => 'Buồng phòng',
                'maNhanVien' => 4, // Trần Hoa
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => Carbon::now()->addDay()->format('d/m/Y - 10:00'),
                'ghiChu' => 'Khách cần là ủi thẳng 2 áo sơ mi trắng, giao trước 10:00 sáng mai.',
                'daGhiHoaDon' => false,
                'thoiGianTiepNhan' => Carbon::now()->setTime(9, 52),
            ],
            [
                'maYeuCauCode' => 'YC-0811-06',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 3, // Dọn phòng theo yêu cầu
                'soLuong' => 1,
                'donGiaApDung' => 60000,
                'ngayYeuCau' => Carbon::now()->setTime(10, 25),
                'thoiGianMongMuon' => Carbon::now()->setTime(11, 0),
                'trangThai' => 'Cho_Tiep_Nhan',
                'boPhanPhuTrach' => 'Buồng phòng',
                'maNhanVien' => null, // Chưa phân công
                'mucDoUuTien' => 'Cao',
                'thoiGianHen' => 'Hôm nay - 11:00',
                'ghiChu' => 'Khách vừa đi dạo về, phòng cần thay thêm 2 khăn tắm lớn và quét dọn ban công.',
                'daGhiHoaDon' => false,
            ],
            [
                'maYeuCauCode' => 'YC-0811-05',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 2, // Giặt ủi
                'soLuong' => 1,
                'donGiaApDung' => 40000,
                'ngayYeuCau' => Carbon::now()->setTime(10, 0),
                'thoiGianMongMuon' => Carbon::now()->addDay()->setTime(10, 0),
                'trangThai' => 'Dang_Thuc_Hien',
                'boPhanPhuTrach' => 'Buồng phòng',
                'maNhanVien' => 3, // Nguyễn Hương
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => Carbon::now()->addDay()->format('d/m/Y - 10:00'),
                'ghiChu' => 'Giặt hấp đầm dạ hội, không dùng chất tẩy mạnh.',
                'daGhiHoaDon' => false,
                'thoiGianTiepNhan' => Carbon::now()->setTime(10, 2),
                'thoiGianBatDau' => Carbon::now()->setTime(10, 10),
            ],
            [
                'maYeuCauCode' => 'YC-0811-03',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 1, // Ăn uống tại phòng
                'soLuong' => 2,
                'donGiaApDung' => 80000,
                'ngayYeuCau' => Carbon::now()->setTime(8, 30),
                'thoiGianMongMuon' => Carbon::now()->setTime(9, 0),
                'trangThai' => 'Da_Hoan_Thanh',
                'boPhanPhuTrach' => 'Nhà hàng & Bếp',
                'maNhanVien' => 2, // Mai Linh
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => '09:00',
                'ghiChu' => '2 phần điểm tâm kiểu Mỹ, kèm 1 cà phê đen đá và 1 nước cam.',
                'daGhiHoaDon' => true,
                'thoiGianTiepNhan' => Carbon::now()->setTime(8, 32),
                'thoiGianHoanThanh' => Carbon::now()->setTime(9, 10),
            ],
            [
                'maYeuCauCode' => 'YC-0811-02',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 4, // Minibar & đồ uống
                'soLuong' => 2,
                'donGiaApDung' => 25000,
                'ngayYeuCau' => Carbon::now()->setTime(8, 0),
                'thoiGianMongMuon' => Carbon::now()->setTime(8, 30),
                'trangThai' => 'Da_Hoan_Thanh',
                'boPhanPhuTrach' => 'Nhà hàng & Bếp',
                'maNhanVien' => 2, // Mai Linh
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => '08:30',
                'ghiChu' => 'Bổ sung thêm 2 lon nước ngọt ướp lạnh lên phòng.',
                'daGhiHoaDon' => true,
                'thoiGianTiepNhan' => Carbon::now()->setTime(8, 2),
                'thoiGianHoanThanh' => Carbon::now()->setTime(8, 30),
            ],
            [
                'maYeuCauCode' => 'YC-0811-01',
                'maLuuTru' => $maLuuTruDefault,
                'maDichVu' => 1, // Ăn uống tại phòng
                'soLuong' => 2,
                'donGiaApDung' => 80000,
                'ngayYeuCau' => Carbon::now()->setTime(7, 15),
                'thoiGianMongMuon' => Carbon::now()->setTime(7, 45),
                'trangThai' => 'Da_Hoan_Thanh',
                'boPhanPhuTrach' => 'Nhà hàng & Bếp',
                'maNhanVien' => 2, // Mai Linh
                'mucDoUuTien' => 'Bình thường',
                'thoiGianHen' => '07:45',
                'ghiChu' => 'Phở bò tái nạm 2 tô nóng, ít hành.',
                'daGhiHoaDon' => true,
                'thoiGianTiepNhan' => Carbon::now()->setTime(7, 18),
                'thoiGianHoanThanh' => Carbon::now()->setTime(7, 45),
            ]
        ];

        foreach ($samples as $s) {
            $yc = Yeu_Cau_Dich_Vu::create($s);

            // Nếu là dọn phòng hoặc giặt ủi, tạo phiếu buồng phòng liên kết
            if ($s['boPhanPhuTrach'] === 'Buồng phòng') {
                $phieu = Phieu_Buong_Phong::create([
                    'maPhong' => $phongMap['301']->id,
                    'maYeuCauDichVu' => $yc->id,
                    'viTri' => 'Phòng 301',
                    'maNhanVien' => $s['maNhanVien'] ?: 3,
                    'tieuDe' => "Dịch vụ phòng [{$yc->maYeuCauCode}]",
                    'loaiCongViec' => 'Dọn phòng',
                    'mucDoUuTien' => $s['mucDoUuTien'] === 'Cao' ? 'Cao' : 'Vừa',
                    'thoiHan' => $s['thoiGianHen'],
                    'ngayTao' => $s['ngayYeuCau'],
                    'trangThai' => $s['trangThai'] === 'Da_Hoan_Thanh' ? 'done' : ($s['trangThai'] === 'Dang_Thuc_Hien' ? 'doing' : 'pending'),
                    'ghiChu' => $s['ghiChu']
                ]);

                $yc->maPhieuBuongPhong = $phieu->id;
                $yc->save();
            }

            // Tạo thông báo mẫu
            Thong_Bao::create([
                'loai' => 'yeu_cau_moi',
                'tieuDe' => "Yêu cầu: [{$yc->maYeuCauCode}]",
                'noiDung' => "Phòng 301 vừa đăng ký dịch vụ: {$yc->ghiChu}",
                'maYeuCauDichVu' => $yc->id,
                'vaiTroNhan' => 'Lễ tân',
                'trangThai' => $yc->trangThai === 'Da_Hoan_Thanh' ? 'Da_Xem' : 'Chua_Xem',
                'created_at' => $s['ngayYeuCau'],
                'updated_at' => $s['ngayYeuCau'],
            ]);
        }
    }
}
