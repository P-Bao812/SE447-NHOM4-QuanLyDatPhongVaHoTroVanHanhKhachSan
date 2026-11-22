<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Phong;
use App\Models\Phieu_Buong_Phong;

class BuongPhongApiTest extends TestCase
{
    public function test_api_ping()
    {
        $response = $this->getJson('/api/ping');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'pong']);
    }

    public function test_danh_sach_phong_can_ve_sinh()
    {
        $response = $this->getJson('/api/buong-phong/phong-can-ve-sinh');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'total',
            'data' => [
                '*' => ['id', 'tenPhong', 'tang', 'trangThai', 'tenLoai', 'ghiChuDonDep']
            ]
        ]);
    }

    public function test_cap_nhat_trang_thai_phong()
    {
        $phong = Phong::first();
        $response = $this->postJson('/api/buong-phong/cap-nhat-trang-thai-phong', [
            'maPhong' => $phong->id,
            'trangThai' => 'Can_Don',
            'ghiChu' => 'Khách vừa trả phòng lúc 11:00'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('Phong', [
            'id' => $phong->id,
            'trangThai' => 'Can_Don'
        ]);
    }

    public function test_kiem_tra_phong()
    {
        $phong = Phong::where('tenPhong', 'like', '%303%')->first() ?: Phong::first();
        $response = $this->postJson('/api/buong-phong/kiem-tra-phong', [
            'maPhong' => $phong->id,
            'ketQua' => 'Dat',
            'nguoiKiemTra' => 'Mai Linh',
            'ghiChu' => 'Phòng đạt chuẩn 5 sao, thơm tho sạch sẽ',
            'checklist' => [
                'giuong' => true,
                'phongTam' => true,
                'minibar' => true,
                'dienMay' => true
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'ketQua' => 'Dat'
        ]);
        $this->assertDatabaseHas('Phong', [
            'id' => $phong->id,
            'trangThai' => 'Trong_Sach'
        ]);
    }

    public function test_bao_cao_hoan_thanh()
    {
        $phieu = Phieu_Buong_Phong::where('trangThai', 'doing')->first() ?: Phieu_Buong_Phong::first();
        $response = $this->postJson('/api/buong-phong/bao-cao-hoan-thanh', [
            'maPhieu' => $phieu->id,
            'ghiChuBanGiao' => 'Đã thay drap mới, lau sàn và bổ sung xà phòng',
            'tinhTrangPhong' => 'Cho_Kiem_Tra',
            'minibar' => [
                ['maSanPham' => 1, 'soLuong' => 2, 'donGia' => 15000]
            ],
            'taiSan' => []
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function test_danh_sach_cong_viec_kanban()
    {
        $response = $this->getJson('/api/buong-phong/cong-viec');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'status', 'location', 'category', 'priority', 'title', 'assignee', 'timeInfo']
            ]
        ]);
    }

    public function test_tao_cong_viec_moi()
    {
        $response = $this->postJson('/api/buong-phong/tao-cong-viec', [
            'location' => 'Phòng 502',
            'category' => 'Dọn phòng',
            'priority' => 'Cao',
            'title' => 'Dọn phòng khách VIP',
            'note' => 'Chuẩn bị thêm giỏ hoa quả',
            'assignee' => 'Chị Hương',
            'timeInfo' => 'Trước 14:00'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('Phieu_Buong_Phong', [
            'viTri' => 'Phòng 502',
            'tieuDe' => 'Dọn phòng khách VIP'
        ]);
    }

    public function test_thong_ke_ca()
    {
        $response = $this->getJson('/api/buong-phong/thong-ke-ca');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'shiftName',
                'totalTasks',
                'completedTasks',
                'openTasks',
                'completionPercentage',
                'staff'
            ]
        ]);
    }
}
