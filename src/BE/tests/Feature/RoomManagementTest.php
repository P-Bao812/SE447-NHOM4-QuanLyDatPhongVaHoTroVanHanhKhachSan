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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomManagementTest extends TestCase
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

    protected Loai_Phong $roomTypeStandard;
    protected Loai_Phong $roomTypeVip;

    protected Phong $roomP101;
    protected Phong $roomP102;
    protected Phong $roomP201;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo loại phòng
        $this->roomTypeStandard = Loai_Phong::create([
            'tenLoai'      => 'Tiêu Chuẩn',
            'moTa'         => 'Phòng đơn tiêu chuẩn 1 giường',
            'giaTieuChuan' => 400000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomTypeVip = Loai_Phong::create([
            'tenLoai'      => 'VIP Suite',
            'moTa'         => 'Phòng VIP sang trọng',
            'giaTieuChuan' => 1000000,
            'sucChuaToiDa' => 4,
            'trangThai'    => 'Kinh doanh',
        ]);

        // 2. Tạo phòng vật lý mẫu
        $this->roomP101 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomP102 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P102',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomP201 = Phong::create([
            'maLoaiPhong' => $this->roomTypeVip->id,
            'tenPhong'    => 'P201',
            'tang'        => 2,
            'trangThai'   => 'Sẵn sàng',
        ]);

        // 3. Tạo tài khoản Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_test',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Trần Quản Lý',
            'ngaySinh'    => '1985-05-15',
            'cccd'        => '079085000001',
            'soDienThoai' => '0901000001',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 4. Tạo tài khoản Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'reception_test',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lê Lễ Tân',
            'ngaySinh'    => '1995-08-20',
            'cccd'        => '079095000002',
            'soDienThoai' => '0902000002',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 5. Tạo tài khoản Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_test',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'ngaySinh'    => '1992-03-10',
            'cccd'        => '079092000003',
            'soDienThoai' => '0903000003',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 6. Tạo tài khoản Khách hàng
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_test',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Khách Hàng',
            'cccd'        => '079090000004',
            'email'       => 'customer@example.com',
            'soDienThoai' => '0904000004',
            'diaChi'      => '123 Đường ABC, Quận 1',
        ]);
    }

    // ==========================================
    // LUỒNG CHÍNH (MAIN FLOW)
    // ==========================================

    /**
     * 1. Quản lý xem danh sách phòng kèm quan hệ loại phòng.
     */
    public function test_manager_can_fetch_rooms_list(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy danh sách phòng thành công.',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'maLoaiPhong',
                        'tenPhong',
                        'tang',
                        'trangThai',
                        'loaiPhong' => [
                            'id',
                            'tenLoai',
                            'giaTieuChuan',
                            'sucChuaToiDa',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * 2. Quản lý xem chi tiết một phòng vật lý.
     */
    public function test_manager_can_view_single_room_detail(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson("/api/v1/rooms/{$this->roomP101->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy thông tin phòng thành công.',
                'data'    => [
                    'id'          => $this->roomP101->id,
                    'tenPhong'    => 'P101',
                    'tang'        => 1,
                    'trangThai'   => 'Sẵn sàng',
                    'maLoaiPhong' => $this->roomTypeStandard->id,
                ],
            ]);
    }

    /**
     * 3. Quản lý thêm phòng mới hợp lệ với tên phòng chưa tồn tại.
     */
    public function test_manager_can_create_room_with_valid_data(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P302',
            'tang'        => 3,
            'trangThai'   => 'Sẵn sàng',
        ];

        $response = $this->postJson('/api/v1/rooms', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status'  => 201,
                'message' => 'Thêm phòng mới thành công.',
                'data'    => [
                    'tenPhong'    => 'P302',
                    'tang'        => 3,
                    'trangThai'   => 'Sẵn sàng',
                    'maLoaiPhong' => $this->roomTypeStandard->id,
                ],
            ]);

        $this->assertDatabaseHas('phong', [
            'tenPhong'    => 'P302',
            'tang'        => 3,
            'trangThai'   => 'Sẵn sàng',
            'maLoaiPhong' => $this->roomTypeStandard->id,
        ]);
    }

    /**
     * 4. Quản lý sửa tên phòng, tầng và gán lại loại phòng hợp lệ.
     */
    public function test_manager_can_update_room_details_and_reassign_room_type(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'maLoaiPhong' => $this->roomTypeVip->id,
            'tenPhong'    => 'P101-VIP',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ];

        $response = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Cập nhật thông tin phòng thành công.',
                'data'    => [
                    'id'          => $this->roomP101->id,
                    'tenPhong'    => 'P101-VIP',
                    'maLoaiPhong' => $this->roomTypeVip->id,
                ],
            ]);

        $this->assertDatabaseHas('phong', [
            'id'          => $this->roomP101->id,
            'tenPhong'    => 'P101-VIP',
            'maLoaiPhong' => $this->roomTypeVip->id,
        ]);
    }

    /**
     * 5. Quản lý chuyển phòng trống sang "Đang bảo trì".
     */
    public function test_manager_can_change_vacant_room_to_maintenance(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'trangThai' => 'Đang bảo trì',
        ];

        $response = $this->putJson("/api/v1/rooms/{$this->roomP102->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'id'        => $this->roomP102->id,
                    'trangThai' => 'Đang bảo trì',
                ],
            ]);

        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP102->id,
            'trangThai' => 'Đang bảo trì',
        ]);
    }

    /**
     * 6. Quản lý chuyển phòng trống sang "Ngưng khai thác".
     */
    public function test_manager_can_change_vacant_room_to_retired(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP102->id}", [
            'trangThai' => 'Ngưng khai thác',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'id'        => $this->roomP102->id,
                    'trangThai' => 'Ngưng khai thác',
                ],
            ]);

        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP102->id,
            'trangThai' => 'Ngưng khai thác',
        ]);
    }

    /**
     * 7. Quản lý cập nhật trạng thái qua endpoint chuyên biệt PUT /rooms/{id}/status.
     */
    public function test_manager_can_update_status_via_dedicated_endpoint(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP102->id}/status", [
            'trangThai' => 'Đang bảo trì',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Cập nhật trạng thái phòng thành công.',
                'data'    => [
                    'id'        => $this->roomP102->id,
                    'trangThai' => 'Đang bảo trì',
                ],
            ]);

        $this->assertEquals('Đang bảo trì', $this->roomP102->fresh()->trangThai);
    }

    // ==========================================
    // LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // ==========================================

    /**
     * 8. Tìm kiếm phòng theo số/tên phòng (keyword).
     */
    public function test_filter_rooms_by_keyword_search(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms?keyword=P101');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P101', $data[0]['tenPhong']);
    }

    /**
     * 9. Lọc phòng theo tầng.
     */
    public function test_filter_rooms_by_floor(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms?tang=2');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P201', $data[0]['tenPhong']);
    }

    /**
     * 10. Lọc phòng theo loại phòng.
     */
    public function test_filter_rooms_by_room_type(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson("/api/v1/rooms?maLoaiPhong={$this->roomTypeVip->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P201', $data[0]['tenPhong']);
    }

    /**
     * 11. Lọc phòng theo trạng thái vận hành.
     */
    public function test_filter_rooms_by_status(): void
    {
        Sanctum::actingAs($this->managerUser);

        $this->roomP102->update(['trangThai' => 'Đang bảo trì']);

        $response = $this->getJson('/api/v1/rooms?trangThai=Đang bảo trì');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('P102', $data[0]['tenPhong']);
    }

    /**
     * 12. Phân trang danh sách phòng khi có query parameter 'page'.
     */
    public function test_pagination_meta_included_when_page_parameter_present(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/rooms?page=1&per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'pagination' => [
                        'total',
                        'per_page',
                        'current_page',
                        'last_page',
                    ],
                ],
            ]);

        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(3, $response->json('meta.pagination.total'));
    }

    // ==========================================
    // LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // ==========================================

    /**
     * 13. Thêm phòng thiếu trường bắt buộc nhận 422.
     */
    public function test_create_room_fails_when_required_fields_missing(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
                'message' => 'Dữ liệu gửi lên không hợp lệ.',
            ])
            ->assertJsonValidationErrors(['maLoaiPhong', 'tenPhong', 'tang', 'trangThai']);
    }

    /**
     * 14. Tên phòng dài hơn 20 ký tự nhận 422.
     */
    public function test_create_room_fails_when_room_name_exceeds_20_chars(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P' . str_repeat('0', 25),
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenPhong']);
    }

    /**
     * 15. Tầng nhỏ hơn 1 nhận 422.
     */
    public function test_create_room_fails_when_floor_less_than_1(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P001',
            'tang'        => 0,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tang']);
    }

    /**
     * 16. Chọn loại phòng không tồn tại nhận 422.
     */
    public function test_create_room_fails_when_room_type_not_exists(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => 99999,
            'tenPhong'    => 'P999',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['maLoaiPhong']);
    }

    /**
     * 17. Nhập tên phòng trùng nhận 422.
     */
    public function test_create_room_fails_when_room_name_duplicate(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P101', // Đã tồn tại
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenPhong']);
    }

    /**
     * 18. Trạng thái phòng không hợp lệ nhận 422.
     */
    public function test_create_room_fails_when_invalid_status(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P501',
            'tang'        => 5,
            'trangThai'   => 'KhongHopLe',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trangThai']);
    }

    /**
     * 19. Cập nhật trùng tên với một phòng khác nhận 422.
     */
    public function test_update_room_fails_when_duplicate_room_name_on_different_room(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'tenPhong' => 'P102', // Trùng với roomP102
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenPhong']);
    }

    /**
     * 20. Cập nhật giữ nguyên tên phòng hiện tại thành công (ignore self in unique rule).
     */
    public function test_update_room_succeeds_when_keeping_same_room_name(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'tenPhong' => 'P101',
            'tang'     => 1,
        ]);

        $response->assertStatus(200);
    }

    /**
     * 21. Không thể chuyển sang bảo trì hoặc ngưng khai thác khi phòng đang có khách lưu trú (nhận 409).
     */
    public function test_cannot_change_status_to_maintenance_when_room_has_active_stay_returns_409(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Tạo đơn đặt phòng
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => now(),
            'ngayNhanPhong' => Carbon::today(),
            'ngayTraPhong'  => Carbon::tomorrow(),
            'soLuongKhach'  => 2,
            'tienCoc'       => 400000,
            'trangThai'     => 'Đang ở',
        ]);

        $detail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->roomP101->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        // Tạo đợt lưu trú đang ở
        Luu_Tru::create([
            'maChiTiet'    => $detail->id,
            'thoiGianNhan' => now(),
            'thoiGianTra'  => Carbon::tomorrow(),
            'trangThai'    => 'Đang ở',
        ]);

        // Thử chuyển sang Đang bảo trì
        $resMaintenance = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'trangThai' => 'Đang bảo trì',
        ]);

        $resMaintenance->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        // Thử chuyển sang Ngưng khai thác qua endpoint status
        $resRetired = $this->putJson("/api/v1/rooms/{$this->roomP101->id}/status", [
            'trangThai' => 'Ngưng khai thác',
        ]);

        $resRetired->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        // Dữ liệu phòng không bị đổi
        $this->assertEquals('Sẵn sàng', $this->roomP101->fresh()->trangThai);
    }

    /**
     * 22. Không thể chuyển sang bảo trì/ngưng khai thác khi phòng có đơn đặt phòng hiệu lực chưa hoàn tất (nhận 409).
     */
    public function test_cannot_change_status_to_maintenance_when_room_has_active_booking_returns_409(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Tạo đơn đặt phòng đã xác nhận trong tương lai
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => now(),
            'ngayNhanPhong' => Carbon::now()->addDays(5),
            'ngayTraPhong'  => Carbon::now()->addDays(7),
            'soLuongKhach'  => 2,
            'tienCoc'       => 400000,
            'trangThai'     => 'Đã xác nhận',
        ]);

        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->roomP102->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP102->id}", [
            'trangThai' => 'Đang bảo trì',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertEquals('Sẵn sàng', $this->roomP102->fresh()->trangThai);
    }

    /**
     * 23. Có thể chuyển sang bảo trì nếu đơn đặt phòng cũ đã bị hủy.
     */
    public function test_can_change_status_to_maintenance_when_past_booking_is_cancelled(): void
    {
        Sanctum::actingAs($this->managerUser);

        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => now()->subDays(5),
            'ngayNhanPhong' => Carbon::now()->subDays(2),
            'ngayTraPhong'  => Carbon::tomorrow(),
            'soLuongKhach'  => 2,
            'tienCoc'       => 400000,
            'trangThai'     => 'Đã hủy', // Đã hủy
        ]);

        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->roomP102->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        $response = $this->putJson("/api/v1/rooms/{$this->roomP102->id}", [
            'trangThai' => 'Đang bảo trì',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Đang bảo trì', $this->roomP102->fresh()->trangThai);
    }

    /**
     * 24. Gọi API khi chưa đăng nhập nhận 401.
     */
    public function test_unauthenticated_user_cannot_access_room_apis_returns_401(): void
    {
        $resGet = $this->getJson('/api/v1/rooms');
        $resGet->assertStatus(401);

        $resPost = $this->postJson('/api/v1/rooms', [
            'tenPhong' => 'P999',
        ]);
        $resPost->assertStatus(401);

        $resPut = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'tenPhong' => 'P999',
        ]);
        $resPut->assertStatus(401);
    }

    /**
     * 25. Gọi API quản lý bằng vai trò không phải Quản lý nhận 403.
     */
    public function test_unauthorized_role_cannot_modify_rooms_returns_403(): void
    {
        // 1. Lễ tân không được thêm/sửa phòng
        Sanctum::actingAs($this->receptionistUser);

        $resPost = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P303',
            'tang'        => 3,
            'trangThai'   => 'Sẵn sàng',
        ]);
        $resPost->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
            ]);

        $resPut = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'tenPhong' => 'P101-REC',
        ]);
        $resPut->assertStatus(403);

        $resStatus = $this->putJson("/api/v1/rooms/{$this->roomP101->id}/status", [
            'trangThai' => 'Đang bảo trì',
        ]);
        $resStatus->assertStatus(403);

        $resDelete = $this->deleteJson("/api/v1/rooms/{$this->roomP101->id}");
        $resDelete->assertStatus(403);

        // 2. Khách hàng không được thêm/sửa phòng
        Sanctum::actingAs($this->customerUser);

        $resCustomerPost = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P304',
            'tang'        => 3,
            'trangThai'   => 'Sẵn sàng',
        ]);
        $resCustomerPost->assertStatus(403);

        // 3. Buồng phòng không được thêm/sửa phòng
        Sanctum::actingAs($this->housekeeperUser);

        $resHousekeeperPut = $this->putJson("/api/v1/rooms/{$this->roomP101->id}", [
            'tenPhong' => 'P101-HK',
        ]);
        $resHousekeeperPut->assertStatus(403);
    }

    /**
     * 26. Gọi API với mã phòng không tồn tại nhận 404.
     */
    public function test_view_or_update_non_existent_room_returns_404(): void
    {
        Sanctum::actingAs($this->managerUser);

        $resShow = $this->getJson('/api/v1/rooms/999999');
        $resShow->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status'  => 404,
                'message' => 'Không tìm thấy phòng.',
            ]);

        $resPut = $this->putJson('/api/v1/rooms/999999', [
            'tenPhong'    => 'P999',
            'tang'        => 9,
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'trangThai'   => 'Sẵn sàng',
        ]);
        $resPut->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status'  => 404,
                'message' => 'Không tìm thấy phòng.',
            ]);
    }

    /**
     * 27. Phòng từng phát sinh giao dịch không bị xóa cứng (nhận 409).
     */
    public function test_room_with_transaction_history_cannot_be_deleted_returns_409(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Tạo lịch sử giao dịch đặt phòng
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => now()->subMonths(1),
            'ngayNhanPhong' => Carbon::now()->subMonths(1),
            'ngayTraPhong'  => Carbon::now()->subMonths(1)->addDays(2),
            'soLuongKhach'  => 2,
            'tienCoc'       => 400000,
            'trangThai'     => 'Đã hoàn thành',
        ]);

        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->roomP101->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        $response = $this->deleteJson("/api/v1/rooms/{$this->roomP101->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        // Phòng vẫn còn nguyên vẹn trong CSDL
        $this->assertDatabaseHas('phong', [
            'id' => $this->roomP101->id,
        ]);
    }

    /**
     * 28. Phòng chưa từng phát sinh giao dịch có thể xóa thành công.
     */
    public function test_room_without_history_can_be_deleted_successfully(): void
    {
        Sanctum::actingAs($this->managerUser);

        $freshRoom = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P999-TEMP',
            'tang'        => 9,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response = $this->deleteJson("/api/v1/rooms/{$freshRoom->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Xóa phòng thành công.',
            ]);

        $this->assertDatabaseMissing('phong', [
            'id' => $freshRoom->id,
        ]);
    }

    /**
     * 29. Phòng bảo trì hoặc ngưng khai thác không thể chọn để đặt phòng mới (BR01.6).
     */
    public function test_room_under_maintenance_or_retired_cannot_be_booked(): void
    {
        Sanctum::actingAs($this->customerUser);

        // Chuyển P101 sang Ngưng khai thác
        $this->roomP101->update(['trangThai' => 'Ngưng khai thác']);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDays(2)->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 2,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 2],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['danhSachPhong.0.maPhong']);
    }

    /**
     * 30. Mô phỏng lỗi Database rollback transaction và trả 500.
     */
    public function test_database_error_triggers_rollback_and_returns_500(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Can thiệp model hook gây exception bên trong transaction
        Phong::saving(function () {
            throw new \RuntimeException('Simulated database error');
        });

        $response = $this->postJson('/api/v1/rooms', [
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P777',
            'tang'        => 7,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
            ]);

        $this->assertDatabaseMissing('phong', [
            'tenPhong' => 'P777',
        ]);
    }
}
