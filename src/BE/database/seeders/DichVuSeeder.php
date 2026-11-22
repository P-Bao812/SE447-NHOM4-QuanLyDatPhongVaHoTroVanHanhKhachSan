<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DichVuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 5 Dịch vụ theo đúng yêu cầu đề bài
        $dichVus = [
            [
                'id' => 1,
                'tenDichVu' => 'Ăn uống tại phòng',
                'loaiDichVu' => 'Ẩm thực',
                'moTa' => 'Phục vụ các món ăn Á – Âu hảo hạng, điểm tâm sáng và đồ ăn nhẹ nóng hổi trực tiếp tại không gian phòng nghỉ của Quý khách.',
                'donGia' => 150000,
                'donViTinh' => 'Suất',
                'hinhAnh' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
                'trangThai' => 'Đang cung cấp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'tenDichVu' => 'Giặt ủi',
                'loaiDichVu' => 'Giặt ủi',
                'moTa' => 'Dịch vụ giặt ướt sấy khô, giặt khô cao cấp và ủi phẳng quần áo chuyên nghiệp, cam kết giao trả trong ngày.',
                'donGia' => 50000,
                'donViTinh' => 'Kg',
                'hinhAnh' => 'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=800&q=80',
                'trangThai' => 'Đang cung cấp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'tenDichVu' => 'Dọn phòng theo yêu cầu',
                'loaiDichVu' => 'Buồng phòng',
                'moTa' => 'Hỗ trợ dọn dẹp phòng phát sinh ngoài giờ, thay mới toàn bộ chăn ga gối đệm, bổ sung khăn tắm cao cấp và đồ dùng cá nhân.',
                'donGia' => 60000,
                'donViTinh' => 'Lần',
                'hinhAnh' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
                'trangThai' => 'Đang cung cấp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'tenDichVu' => 'Minibar và đồ uống',
                'loaiDichVu' => 'Đồ uống',
                'moTa' => 'Bổ sung các loại đồ uống ướp lạnh, nước suối khoáng, nước ép trái cây tươi, trà thảo mộc, bia thủ công và các món snack ăn kèm.',
                'donGia' => 80000,
                'donViTinh' => 'Set',
                'hinhAnh' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
                'trangThai' => 'Đang cung cấp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'tenDichVu' => 'Spa và massage',
                'loaiDichVu' => 'Spa & Massage',
                'moTa' => 'Trải nghiệm liệu trình massage toàn thân bằng tinh dầu tự nhiên, đá nóng thư giãn chuyên sâu do kỹ thuật viên spa thực hiện tận phòng.',
                'donGia' => 350000,
                'donViTinh' => 'Gói 60 phút',
                'hinhAnh' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
                'trangThai' => 'Đang cung cấp',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($dichVus as $dv) {
            DB::table('Dich_Vu')->updateOrInsert(
                ['id' => $dv['id']],
                $dv
            );
        }
    }
}
