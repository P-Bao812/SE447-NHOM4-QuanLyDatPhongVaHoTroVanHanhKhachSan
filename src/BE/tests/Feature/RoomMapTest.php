<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Khach_Hang;
use App\Models\Loai_Phong;
use App\Models\Luu_Tru;
use App\Models\Nhan_Vien;
use App\Models\Phong;
use App\Models\Tai_Khoan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomMapTest extends TestCase
{
    use RefreshDatabase;

    protected Tai_Khoan $managerUser;
    protected Nhan_Vien $managerProfile;

    protected Tai_Khoan $receptionistUser;
    protected Nhan_Vien $receptionistProfile;

    protected Tai_Khoan $housekeeperUser;
    protected Nhan_Vien $housekeeperProfile;

    protected Tai_Khoan $customerUser;
    protected Khach_Hang $customerProfile;

    protected Loai_Phong $typeStandard;
    protected Loai_Phong $typeDeluxe;
    protected Loai_Phong $typeVip;

    protected Phong $room101; // Tầng 1 - Trống
    protected Phong $room102; // Tầng 1 - Cần dọn dẹp
    protected Phong $room201; // Tầng 2 - Đang ở
    protected Phong $room202; // Tầng 2 - Đã đặt
    protected Phong $room301; // Tầng 3 - Đang bảo trì
    protected Phong $room302; // Tầng 3 - Ngưng khai thác

    protected Dat_Phong $activeBooking;
    protected Luu_Tru $activeStay;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo các vai trò tài khoản
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_map',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Trần Quản Lý',
            'ngaySinh'    => '1985-05-15',
            'cccd'        => '048085001234',
            'soDienThoai' => '0911222333',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm việc',
        ]);

        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'receptionist_map',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lê Lễ Tân',
            'ngaySinh'    => '1995-10-20',
            'cccd'        => '048095005678',
            'soDienThoai' => '0922333444',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm việc',
        ]);

        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_map',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'ngaySinh'    => '1992-03-10',
            'cccd'        => '048092009876',
            'soDienThoai' => '0933444555',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm việc',
        ]);

        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_map',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Khách VIP',
            'cccd'        => '048099009988',
            'email'       => 'vip_customer@example.com',
            'soDienThoai' => '0988777666',
            'diaChi'      => 'Sơn Trà, Đà Nẵng',
        ]);

        // 2. Tạo Loại phòng
        $this->typeStandard = Loai_Phong::create([
            'tenLoai'      => 'Tiêu Chuẩn (Standard)',
            'moTa'         => 'Phòng tiêu chuẩn ấm cúng',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->typeDeluxe = Loai_Phong::create([
            'tenLoai'      => 'Cao Cấp (Deluxe)',
            'moTa'         => 'Phòng Deluxe ban công ngắm biển',
            'giaTieuChuan' => 850000,
            'sucChuaToiDa' => 3,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->typeVip = Loai_Phong::create([
            'tenLoai'      => 'Tổng Thống (President VIP)',
            'moTa'         => 'Phòng Suite tổng thống sang trọng',
            'giaTieuChuan' => 2500000,
            'sucChuaToiDa' => 4,
            'trangThai'    => 'Kinh doanh',
        ]);

        // 3. Tạo phòng các tầng
        $this->room101 = Phong::create([
            'maLoaiPhong' => $this->typeStandard->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->room102 = Phong::create([
            'maLoaiPhong' => $this->typeStandard->id,
            'tenPhong'    => 'P102',
            'tang'        => 1,
            'trangThai'   => 'Cần dọn dẹp',
        ]);

        $this->room201 = Phong::create([
            'maLoaiPhong' => $this->typeDeluxe->id,
            'tenPhong'    => 'P201',
            'tang'        => 2,
            'trangThai'   => 'Đang ở',
        ]);

        $this->room202 = Phong::create([
            'maLoaiPhong' => $this->typeDeluxe->id,
            'tenPhong'    => 'P202',
            'tang'        => 2,
            'trangThai'   => 'Sẵn sàng', // Sẽ gắn đơn đặt phòng Đã đặt
        ]);

        $this->room301 = Phong::create([
            'maLoaiPhong' => $this->typeVip->id,
            'tenPhong'    => 'P301',
            'tang'        => 3,
            'trangThai'   => 'Đang bảo trì',
        ]);

        $this->room302 = Phong::create([
            'maLoaiPhong' => $this->typeVip->id,
            'tenPhong'    => 'P302',
            'tang'        => 3,
            'trangThai'   => 'Ngưng khai thác',
        ]);

        // 4. Tạo lưu trú Đang ở cho phòng P201
        $bookingStay = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => Carbon::now()->subDays(1),
            'ngayNhanPhong' => Carbon::now()->subDays(1),
            'ngayTraPhong'  => Carbon::now()->addDays(2),
            'soLuongKhach'  => 2,
            'tongTien'      => 1700000,
            'tienCoc'       => 500000,
            'trangThai'     => 'Đang ở',
        ]);
        $detailStay = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'      => $bookingStay->id,
            'maPhong'         => $this->room201->id,
            'soLuongKhach'    => 2,
            'giaPhongApDung'  => 850000,
        ]);
        $this->activeStay = Luu_Tru::create([
            'maChiTiet'    => $detailStay->id,
            'thoiGianNhan' => Carbon::now()->subDays(1)->setHour(14),
            'thoiGianTra'  => Carbon::now()->addDays(2)->setHour(12),
            'trangThai'    => 'Đang ở',
        ]);

        // 5. Tạo đơn đặt phòng hiệu lực (Đã đặt) cho phòng P202
        $this->activeBooking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => Carbon::now(),
            'ngayNhanPhong' => Carbon::now()->startOfDay(),
            'ngayTraPhong'  => Carbon::now()->addDays(3)->endOfDay(),
            'soLuongKhach'  => 2,
            'tongTien'      => 2550000,
            'tienCoc'       => 1000000,
            'trangThai'     => 'Đã xác nhận',
        ]);
        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'      => $this->activeBooking->id,
            'maPhong'         => $this->room202->id,
            'soLuongKhach'    => 2,
            'giaPhongApDung'  => 850000,
        ]);
    }

    // =========================================================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // =========================================================================

    public function test_manager_can_view_room_map_with_floors(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'tenPhong',
                        'tang',
                        'trangThai',
                        'trangThaiSoDo',
                        'mauTrangThai' => ['color', 'badgeClass', 'text'],
                        'loaiPhong'    => ['id', 'tenLoai', 'giaTieuChuan', 'sucChuaToiDa'],
                    ],
                ],
                'meta' => [
                    'total',
                    'soTang',
                    'thongKe' => [
                        'trong',
                        'daDat',
                        'dangO',
                        'canDonDep',
                        'dangBaoTri',
                        'ngungKhaiThac',
                    ],
                ],
            ]);

        $this->assertCount(6, $response->json('data'));
    }

    public function test_receptionist_can_view_room_map(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertCount(6, $response->json('data'));
    }

    public function test_housekeeper_can_view_room_map(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertCount(6, $response->json('data'));
    }

    public function test_filter_room_map_by_floor(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc tầng 1 (P101, P102)
        $response = $this->getJson('/api/v1/rooms/map?tang=1');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        foreach ($data as $room) {
            $this->assertEquals(1, $room['tang']);
        }
    }

    public function test_filter_room_map_by_room_type(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Lọc theo loại phòng VIP (P301, P302)
        $response = $this->getJson("/api/v1/rooms/map?maLoaiPhong={$this->typeVip->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        foreach ($data as $room) {
            $this->assertEquals($this->typeVip->id, $room['maLoaiPhong']);
        }
    }

    public function test_filter_room_map_by_status_vacant(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc phòng Trống (P101)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Trống');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P101', $data[0]['tenPhong']);
        $this->assertEquals('Trống', $data[0]['trangThaiSoDo']);
    }

    public function test_view_single_room_detail_with_active_stay_for_manager_or_receptionist(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Xem chi tiết phòng P201 (đang có khách lưu trú)
        $response = $this->getJson("/api/v1/rooms/{$this->room201->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->room201->id)
            ->assertJsonPath('data.tenPhong', 'P201')
            ->assertJsonPath('data.trangThaiSoDo', 'Đang ở')
            ->assertJsonPath('data.thongTinLuuTru.loai', 'Đang ở')
            ->assertJsonPath('data.thongTinLuuTru.khachHang.hoTen', 'Nguyễn Khách VIP')
            ->assertJsonPath('data.thongTinLuuTru.khachHang.soDienThoai', '0988777666')
            ->assertJsonPath('data.thongTinLuuTru.khachHang.cccd', '048099009988');
    }

    public function test_reset_filter_returns_all_rooms_on_map(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Không truyền tham số query lọc nào
        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(200);
        $this->assertCount(6, $response->json('data'));
    }

    // =========================================================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // =========================================================================

    public function test_filter_room_map_by_status_occupied(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc phòng Đang ở (P201)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Đang ở');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P201', $data[0]['tenPhong']);
    }

    public function test_filter_room_map_by_status_booked(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Lọc phòng Đã đặt (P202)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Đã đặt');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P202', $data[0]['tenPhong']);
    }

    public function test_filter_room_map_by_status_cleaning(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        // Lọc phòng Cần dọn dẹp (P102)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Cần dọn dẹp');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P102', $data[0]['tenPhong']);
    }

    public function test_filter_room_map_by_status_maintenance(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc phòng Đang bảo trì (P301)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Đang bảo trì');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P301', $data[0]['tenPhong']);
    }

    public function test_filter_room_map_by_status_retired(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc phòng Ngưng khai thác (P302)
        $response = $this->getJson('/api/v1/rooms/map?trangThai=Ngưng khai thác');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P302', $data[0]['tenPhong']);
    }

    public function test_search_room_map_by_keyword(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Tìm kiếm theo tên phòng 'P301'
        $response = $this->getJson('/api/v1/rooms/map?keyword=P301');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P301', $data[0]['tenPhong']);
    }

    public function test_housekeeper_viewing_occupied_room_hides_sensitive_customer_phone_and_cccd(): void
    {
        // Phân quyền bảo mật: Buồng phòng xem phòng P201
        Sanctum::actingAs($this->housekeeperUser);

        $response = $this->getJson("/api/v1/rooms/{$this->room201->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.tenPhong', 'P201')
            ->assertJsonPath('data.thongTinLuuTru.khachHang.hoTen', 'Nguyễn Khách VIP')
            // Số điện thoại và CCCD bắt buộc phải là null để bảo vệ dữ liệu cá nhân theo Requirement 102 & 167
            ->assertJsonPath('data.thongTinLuuTru.khachHang.soDienThoai', null)
            ->assertJsonPath('data.thongTinLuuTru.khachHang.cccd', null);
    }

    // =========================================================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // =========================================================================

    public function test_filter_returns_empty_message_when_no_rooms_match(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc tầng 99 không tồn tại
        $response = $this->getJson('/api/v1/rooms/map?tang=99');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', [])
            ->assertJsonPath('message', 'Không có phòng nào ở tình trạng này.');
    }

    public function test_unauthenticated_user_cannot_access_room_map_returns_401(): void
    {
        // Gọi không kèm Bearer Token
        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(401);
    }

    public function test_unauthorized_role_such_as_customer_cannot_access_room_map_returns_403(): void
    {
        // Khách hàng không được truy cập sơ đồ phòng khách sạn
        Sanctum::actingAs($this->customerUser);

        $response = $this->getJson('/api/v1/rooms/map');

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 403);
    }

    public function test_view_non_existent_room_returns_404(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms/999999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_read_room_map_does_not_mutate_any_database_records(): void
    {
        Sanctum::actingAs($this->managerUser);

        $roomsBefore = Phong::all()->toArray();

        // Gọi các thao tác lọc và xem sơ đồ phòng
        $this->getJson('/api/v1/rooms/map');
        $this->getJson('/api/v1/rooms/map?trangThai=Đang ở');
        $this->getJson("/api/v1/rooms/{$this->room201->id}");

        $roomsAfter = Phong::all()->toArray();

        // Đảm bảo không có bản ghi nào bị thay đổi dữ liệu
        $this->assertEquals($roomsBefore, $roomsAfter);
    }
}
