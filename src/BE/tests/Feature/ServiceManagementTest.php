<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Dich_Vu;
use App\Models\Khach_Hang;
use App\Models\Loai_Phong;
use App\Models\Luu_Tru;
use App\Models\Nhan_Vien;
use App\Models\Phong;
use App\Models\Tai_Khoan;
use App\Models\Yeu_Cau_Dich_Vu;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
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

    protected Dich_Vu $activeService1;
    protected Dich_Vu $activeService2;
    protected Dich_Vu $retiredService;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_test',
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

        // 2. Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'receptionist_test',
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

        // 3. Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_test',
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

        // 4. Khách hàng
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_test',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Khách Hàng',
            'cccd'        => '048099001122',
            'email'       => 'customer@example.com',
            'soDienThoai' => '0944555666',
            'diaChi'      => 'Hải Châu, Đà Nẵng',
        ]);

        // 5. Dữ liệu dịch vụ mẫu
        $this->activeService1 = Dich_Vu::create([
            'tenDichVu' => 'Giặt ủi quần áo cao cấp',
            'moTa'      => 'Giặt sấy ủi phẳng nhanh trong vòng 4 tiếng',
            'donGia'    => 50000,
            'trangThai' => 'Đang cung cấp',
        ]);

        $this->activeService2 = Dich_Vu::create([
            'tenDichVu' => 'Dịch vụ Spa & Massage trị liệu',
            'moTa'      => 'Xông hơi thảo dược và massage đá nóng 60 phút',
            'donGia'    => 350000,
            'trangThai' => 'Đang cung cấp',
        ]);

        $this->retiredService = Dich_Vu::create([
            'tenDichVu' => 'Xe đưa đón sân bay VIP',
            'moTa'      => 'Đưa đón sân bay Đà Nẵng bằng xe limousine 7 chỗ',
            'donGia'    => 250000,
            'trangThai' => 'Ngừng cung cấp',
        ]);
    }

    // =========================================================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // =========================================================================

    public function test_manager_can_view_services_list(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    '*' => ['id', 'tenDichVu', 'moTa', 'donGia', 'trangThai'],
                ],
            ]);

        // Quản lý xem được cả dịch vụ đang cung cấp lẫn ngừng cung cấp
        $this->assertCount(3, $response->json('data'));
    }

    public function test_manager_can_create_service_with_valid_data(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'tenDichVu' => 'Ăn sáng Buffet tại nhà hàng',
            'moTa'      => 'Buffet sáng quốc tế hơn 50 món ăn Á - Âu',
            'donGia'    => 180000,
            'trangThai' => 'Đang cung cấp',
        ];

        $response = $this->postJson('/api/v1/services', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201)
            ->assertJsonPath('data.tenDichVu', 'Ăn sáng Buffet tại nhà hàng')
            ->assertJsonPath('data.donGia', 180000)
            ->assertJsonPath('data.trangThai', 'Đang cung cấp');

        $this->assertDatabaseHas('dich_vu', [
            'tenDichVu' => 'Ăn sáng Buffet tại nhà hàng',
            'donGia'    => 180000,
            'trangThai' => 'Đang cung cấp',
        ]);
    }

    public function test_manager_can_view_single_service_detail(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson("/api/v1/services/{$this->activeService1->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $this->activeService1->id)
            ->assertJsonPath('data.tenDichVu', $this->activeService1->tenDichVu)
            ->assertJsonPath('data.donGia', 50000);
    }

    public function test_manager_can_update_service_details(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'tenDichVu' => 'Giặt ủi quần áo lấy ngay (Cập nhật)',
            'moTa'      => 'Giặt hấp sấy khử khuẩn hương hoa',
            'donGia'    => 65000,
            'trangThai' => 'Đang cung cấp',
        ];

        $response = $this->putJson("/api/v1/services/{$this->activeService1->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tenDichVu', 'Giặt ủi quần áo lấy ngay (Cập nhật)')
            ->assertJsonPath('data.donGia', 65000);

        $this->assertDatabaseHas('dich_vu', [
            'id'        => $this->activeService1->id,
            'tenDichVu' => 'Giặt ủi quần áo lấy ngay (Cập nhật)',
            'donGia'    => 65000,
        ]);
    }

    public function test_manager_can_update_service_status(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Chuyển sang Ngừng cung cấp qua status endpoint
        $response = $this->putJson("/api/v1/services/{$this->activeService1->id}/status", [
            'trangThai' => 'Ngừng cung cấp',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.trangThai', 'Ngừng cung cấp');

        $this->assertEquals('Ngừng cung cấp', $this->activeService1->fresh()->trangThai);
    }

    // =========================================================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // =========================================================================

    public function test_filter_services_by_keyword(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/services?keyword=Massage');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->activeService2->id, $data[0]['id']);
    }

    public function test_filter_services_by_status(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Lọc dịch vụ Ngừng cung cấp
        $response = $this->getJson('/api/v1/services?trangThai=Ngừng cung cấp');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->retiredService->id, $data[0]['id']);
    }

    public function test_pagination_meta_included_when_page_parameter_present(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/services?page=1&per_page=2');

        $response->assertStatus(200)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_update_service_succeeds_keeping_same_name(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Giữ nguyên tên cũ của activeService1 nhưng đổi giá
        $payload = [
            'tenDichVu' => $this->activeService1->tenDichVu,
            'moTa'      => 'Mô tả mới được điều chỉnh',
            'donGia'    => 75000,
            'trangThai' => 'Đang cung cấp',
        ];

        $response = $this->putJson("/api/v1/services/{$this->activeService1->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.donGia', 75000);
    }

    public function test_customer_or_receptionist_only_sees_active_services_not_retired(): void
    {
        // 1. Khách hàng
        Sanctum::actingAs($this->customerUser);
        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200);
        $data = $response->json('data');
        // Chỉ có 2 dịch vụ 'Đang cung cấp', không thấy 'Ngừng cung cấp'
        $this->assertCount(2, $data);
        $statusList = collect($data)->pluck('trangThai')->all();
        $this->assertNotContains('Ngừng cung cấp', $statusList);

        // 2. Lễ tân
        Sanctum::actingAs($this->receptionistUser);
        $resRec = $this->getJson('/api/v1/services');
        $this->assertCount(2, $resRec->json('data'));
    }

    public function test_br04_5_service_price_update_does_not_affect_existing_service_requests(): void
    {
        // Setup phòng & đơn lưu trú để tạo yêu cầu dịch vụ cũ
        $roomType = Loai_Phong::create([
            'tenLoai'      => 'Standard Room',
            'moTa'         => 'Phòng tiêu chuẩn',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);
        $room = Phong::create([
            'maLoaiPhong' => $roomType->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Đang ở',
        ]);
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => Carbon::now()->subDays(2),
            'ngayNhanPhong' => Carbon::now()->subDays(1),
            'ngayTraPhong'  => Carbon::now()->addDays(2),
            'soLuongKhach'  => 2,
            'tongTien'      => 1500000,
            'tienCoc'       => 500000,
            'trangThai'     => 'Đang ở',
        ]);
        $bookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'      => $booking->id,
            'maPhong'         => $room->id,
            'soLuongKhach'    => 2,
            'giaPhongApDung'  => 500000,
        ]);
        $stay = Luu_Tru::create([
            'maChiTiet'     => $bookingDetail->id,
            'thoiGianNhan'  => Carbon::now()->subDays(1),
            'thoiGianTra'   => Carbon::now()->addDays(2),
            'trangThai'     => 'Đang ở',
        ]);

        // Yêu cầu dịch vụ cũ được tạo với đơn giá áp dụng 50,000 VNĐ
        $oldServiceRequest = Yeu_Cau_Dich_Vu::create([
            'maLuuTru'     => $stay->id,
            'maDichVu'     => $this->activeService1->id,
            'soLuong'      => 2,
            'donGiaApDung' => 50000,
            'ngayYeuCau'   => Carbon::now()->subHours(6),
            'trangThai'    => 'Hoàn thành',
            'ghiChu'       => 'Giặt áo sơ mi',
        ]);

        // Quản lý tăng đơn giá dịch vụ lên 90,000 VNĐ
        Sanctum::actingAs($this->managerUser);
        $payload = [
            'tenDichVu' => $this->activeService1->tenDichVu,
            'moTa'      => $this->activeService1->moTa,
            'donGia'    => 90000,
            'trangThai' => 'Đang cung cấp',
        ];
        $response = $this->putJson("/api/v1/services/{$this->activeService1->id}", $payload);
        $response->assertStatus(200);

        // BR04.5: Đơn giá áp dụng trong yêu cầu dịch vụ đã phát sinh phải giữ nguyên 50,000 VNĐ
        $this->assertEquals(50000, $oldServiceRequest->fresh()->donGiaApDung);
        $this->assertEquals(90000, $this->activeService1->fresh()->donGia);
    }

    public function test_manager_can_delete_service_without_usage_history(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Tạo dịch vụ mới chưa có yêu cầu nào
        $newService = Dich_Vu::create([
            'tenDichVu' => 'Dịch vụ thuê xe đạp dạo biển',
            'moTa'      => 'Xe đạp thể thao kèm nón bảo hiểm',
            'donGia'    => 40000,
            'trangThai' => 'Đang cung cấp',
        ]);

        $response = $this->deleteJson("/api/v1/services/{$newService->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('dich_vu', ['id' => $newService->id]);
    }

    public function test_service_with_usage_history_cannot_be_deleted_returns_409(): void
    {
        // Gắn một yêu cầu dịch vụ vào activeService1
        $roomType = Loai_Phong::create([
            'tenLoai'      => 'Superior Room',
            'moTa'         => 'Phòng Superior',
            'giaTieuChuan' => 600000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);
        $room = Phong::create([
            'maLoaiPhong' => $roomType->id,
            'tenPhong'    => 'P202',
            'tang'        => 2,
            'trangThai'   => 'Đang ở',
        ]);
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => Carbon::now()->subDays(1),
            'ngayNhanPhong' => Carbon::now(),
            'ngayTraPhong'  => Carbon::now()->addDays(1),
            'soLuongKhach'  => 2,
            'tongTien'      => 600000,
            'tienCoc'       => 200000,
            'trangThai'     => 'Đang ở',
        ]);
        $bookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'      => $booking->id,
            'maPhong'         => $room->id,
            'soLuongKhach'    => 2,
            'giaPhongApDung'  => 600000,
        ]);
        $stay = Luu_Tru::create([
            'maChiTiet'     => $bookingDetail->id,
            'thoiGianNhan'  => Carbon::now(),
            'thoiGianTra'   => Carbon::now()->addDays(1),
            'trangThai'     => 'Đang ở',
        ]);
        Yeu_Cau_Dich_Vu::create([
            'maLuuTru'     => $stay->id,
            'maDichVu'     => $this->activeService1->id,
            'soLuong'      => 1,
            'donGiaApDung' => 50000,
            'ngayYeuCau'   => Carbon::now(),
            'trangThai'    => 'Hoàn thành',
            'ghiChu'       => 'Test bảo toàn lịch sử',
        ]);

        Sanctum::actingAs($this->managerUser);

        // Cố tình xóa dịch vụ đã có lịch sử yêu cầu
        $response = $this->deleteJson("/api/v1/services/{$this->activeService1->id}");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 409);

        // Bản ghi vẫn còn nguyên vẹn trong CSDL
        $this->assertDatabaseHas('dich_vu', ['id' => $this->activeService1->id]);
    }

    // =========================================================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // =========================================================================

    public function test_create_service_fails_when_required_fields_missing_returns_422(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/services', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['tenDichVu', 'moTa', 'donGia', 'trangThai']);
    }

    public function test_create_service_fails_when_price_is_zero_or_negative_returns_422(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Đơn giá = 0
        $responseZero = $this->postJson('/api/v1/services', [
            'tenDichVu' => 'Dịch vụ miễn phí lỗi',
            'moTa'      => 'Mô tả hợp lệ',
            'donGia'    => 0,
            'trangThai' => 'Đang cung cấp',
        ]);
        $responseZero->assertStatus(422)
            ->assertJsonValidationErrors(['donGia']);

        // Đơn giá âm
        $responseNegative = $this->postJson('/api/v1/services', [
            'tenDichVu' => 'Dịch vụ giá âm',
            'moTa'      => 'Mô tả hợp lệ',
            'donGia'    => -50000,
            'trangThai' => 'Đang cung cấp',
        ]);
        $responseNegative->assertStatus(422)
            ->assertJsonValidationErrors(['donGia']);
    }

    public function test_create_service_fails_when_name_is_duplicate_returns_422(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'tenDichVu' => $this->activeService1->tenDichVu, // Tên đã có
            'moTa'      => 'Mô tả mới',
            'donGia'    => 100000,
            'trangThai' => 'Đang cung cấp',
        ];

        $response = $this->postJson('/api/v1/services', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenDichVu']);
    }

    public function test_update_service_fails_when_name_duplicates_another_service_returns_422(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Cố gắng đổi tên của activeService2 thành tên của activeService1
        $payload = [
            'tenDichVu' => $this->activeService1->tenDichVu,
            'moTa'      => 'Mô tả cập nhật',
            'donGia'    => 200000,
            'trangThai' => 'Đang cung cấp',
        ];

        $response = $this->putJson("/api/v1/services/{$this->activeService2->id}", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenDichVu']);
    }

    public function test_view_or_update_non_existent_service_returns_404(): void
    {
        Sanctum::actingAs($this->managerUser);

        // View 404
        $resView = $this->getJson('/api/v1/services/999999');
        $resView->assertStatus(404)
            ->assertJsonPath('success', false);

        // Update 404
        $resUpdate = $this->putJson('/api/v1/services/999999', [
            'tenDichVu' => 'Dịch vụ ma',
            'moTa'      => 'Không tồn tại',
            'donGia'    => 100000,
            'trangThai' => 'Đang cung cấp',
        ]);
        $resUpdate->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_customer_cannot_view_retired_service_detail_returns_404(): void
    {
        Sanctum::actingAs($this->customerUser);

        // Khách hàng cố tình xem chi tiết dịch vụ đã ngừng cung cấp
        $response = $this->getJson("/api/v1/services/{$this->retiredService->id}");

        $response->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_unauthenticated_user_cannot_access_services_apis_returns_401(): void
    {
        $this->getJson('/api/v1/services')->assertStatus(401);
        $this->postJson('/api/v1/services', [])->assertStatus(401);
        $this->putJson('/api/v1/services/1', [])->assertStatus(401);
        $this->deleteJson('/api/v1/services/1')->assertStatus(401);
    }

    public function test_unauthorized_role_cannot_manage_services_returns_403(): void
    {
        // 1. Lễ tân không được tạo dịch vụ
        Sanctum::actingAs($this->receptionistUser);
        $this->postJson('/api/v1/services', [
            'tenDichVu' => 'Dịch vụ trái phép',
            'moTa'      => 'Không có quyền tạo',
            'donGia'    => 100000,
            'trangThai' => 'Đang cung cấp',
        ])->assertStatus(403);

        // 2. Khách hàng không được cập nhật dịch vụ
        Sanctum::actingAs($this->customerUser);
        $this->putJson("/api/v1/services/{$this->activeService1->id}", [
            'tenDichVu' => 'Khách hàng tự sửa giá',
            'moTa'      => 'Trái phép',
            'donGia'    => 1000,
            'trangThai' => 'Đang cung cấp',
        ])->assertStatus(403);

        // 3. Khách hàng không được xóa dịch vụ
        $this->deleteJson("/api/v1/services/{$this->activeService1->id}")
            ->assertStatus(403);
    }

    public function test_unauthorized_role_cannot_view_services_list_returns_403(): void
    {
        // Buồng phòng không có thẩm quyền truy cập danh mục dịch vụ
        Sanctum::actingAs($this->housekeeperUser);
        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_database_error_triggers_rollback_and_returns_500(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Kích hoạt lỗi mô phỏng khi tạo model
        Dich_Vu::creating(function () {
            throw new \Exception('Simulated database error on creating service.');
        });

        // Gửi payload hợp lệ nhưng DB gặp lỗi
        $response = $this->postJson('/api/v1/services', [
            'tenDichVu' => 'Dịch vụ test lỗi 500',
            'moTa'      => 'Mô tả kiểm thử',
            'donGia'    => 150000,
            'trangThai' => 'Đang cung cấp',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 500);
    }
}
