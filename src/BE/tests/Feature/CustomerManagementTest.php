<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Hoa_Don;
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

class CustomerManagementTest extends TestCase
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

    protected Tai_Khoan $otherCustomerUser;
    protected Khach_Hang $otherCustomerProfile;

    protected Khach_Hang $cleanCustomer;

    protected Loai_Phong $roomType;
    protected Phong $room;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo loại phòng & phòng
        $this->roomType = Loai_Phong::create([
            'tenLoai'      => 'Deluxe Ocean View',
            'moTa'         => 'Phòng view biển cao cấp',
            'giaTieuChuan' => 800000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->room = Phong::create([
            'maLoaiPhong' => $this->roomType->id,
            'tenPhong'    => 'P301',
            'tang'        => 3,
            'trangThai'   => 'Sẵn sàng',
        ]);

        // 2. Tạo tài khoản Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_uc08',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Trần Quản Lý',
            'ngaySinh'    => '1985-05-15',
            'cccd'        => '079085000081',
            'soDienThoai' => '0901000081',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 3. Tạo tài khoản Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'reception_uc08',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lê Lễ Tân',
            'ngaySinh'    => '1995-08-20',
            'cccd'        => '079095000082',
            'soDienThoai' => '0902000082',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 4. Tạo tài khoản Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_uc08',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'ngaySinh'    => '1992-03-10',
            'cccd'        => '079092000083',
            'soDienThoai' => '0903000083',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 5. Tạo Khách hàng chính (có phát sinh đơn đặt phòng)
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_uc08_main',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Minh',
            'cccd'        => '079099000084',
            'email'       => 'nguyenvanminh@example.com',
            'soDienThoai' => '0904000084',
            'diaChi'      => '123 Hai Bà Trưng, Q1, TP.HCM',
        ]);

        // 6. Tạo Khách hàng phụ (không phát sinh giao dịch)
        $this->otherCustomerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_uc08_other',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->otherCustomerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->otherCustomerUser->id,
            'hoTen'       => 'Trần Thị Thu Hà',
            'cccd'        => '079099000085',
            'email'       => 'thuha@example.com',
            'soDienThoai' => '0905000085',
            'diaChi'      => '456 Lê Lợi, Q3, TP.HCM',
        ]);

        // Khách hàng không có giao dịch (dùng cho test xóa)
        $cleanUser = Tai_Khoan::create([
            'tenDangNhap' => 'clean_guest_account',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->cleanCustomer = Khach_Hang::create([
            'maTaiKhoan'  => $cleanUser->id,
            'hoTen'       => 'Khách Hàng Vãng Lai Không Giao Dịch',
            'cccd'        => '079099000086',
            'email'       => 'clean_guest@example.com',
            'soDienThoai' => '0906000086',
            'diaChi'      => '789 Nguyễn Huệ, Q1, TP.HCM',
        ]);

        // 7. Tạo chuỗi giao dịch cho customerProfile (Đặt phòng -> Chi tiết -> Lưu trú -> Hóa đơn)
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => Carbon::now()->subDays(3),
            'ngayNhanPhong' => Carbon::now()->subDays(2),
            'ngayTraPhong'  => Carbon::now()->addDays(1),
            'soLuongKhach'  => 2,
            'tienCoc'       => 500000,
            'trangThai'     => 'Đang sử dụng',
        ]);

        $bookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->room->id,
            'giaPhongApDung' => 800000,
            'soLuongKhach'   => 2,
        ]);

        $stay = Luu_Tru::create([
            'maChiTiet'    => $bookingDetail->id,
            'thoiGianNhan' => Carbon::now()->subDays(2),
            'thoiGianTra'  => Carbon::now()->addDays(1),
            'trangThai'    => 'Đang lưu trú',
        ]);

        Hoa_Don::create([
            'maLuuTru'  => $stay->id,
            'ngayLap'   => Carbon::now(),
            'tongTien'  => 2400000,
            'trangThai' => 'Chờ thanh toán',
        ]);
    }

    // ==========================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // ==========================================

    /**
     * Test Lễ tân và Quản lý có thể lấy danh sách khách hàng.
     */
    public function test_receptionist_or_manager_can_fetch_customers_list(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy danh sách khách hàng thành công.',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'hoTen',
                        'cccd',
                        'email',
                        'soDienThoai',
                        'diaChi',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    /**
     * Test Lễ tân xem thông tin chi tiết một khách hàng.
     */
    public function test_receptionist_can_view_single_customer_detail(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson("/api/v1/customers/{$this->customerProfile->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy thông tin khách hàng thành công.',
                'data'    => [
                    'id'          => $this->customerProfile->id,
                    'hoTen'       => 'Nguyễn Văn Minh',
                    'cccd'        => '079099000084',
                    'email'       => 'nguyenvanminh@example.com',
                    'soDienThoai' => '0904000084',
                ],
            ]);
    }

    /**
     * Test thêm hồ sơ khách hàng mới thành công với dữ liệu hợp lệ.
     */
    public function test_receptionist_can_create_new_customer_with_valid_data(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'hoTen'       => 'Hoàng Gia Bảo',
            'cccd'        => '079099123456',
            'email'       => 'giabao@example.com',
            'soDienThoai' => '0912345678',
            'diaChi'      => '99 Đường Cách Mạng Tháng 8, Q10, TP.HCM',
        ];

        $response = $this->postJson('/api/v1/customers', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status'  => 201,
                'message' => 'Thêm hồ sơ khách hàng thành công.',
                'data'    => [
                    'hoTen'       => 'Hoàng Gia Bảo',
                    'cccd'        => '079099123456',
                    'email'       => 'giabao@example.com',
                    'soDienThoai' => '0912345678',
                ],
            ]);

        $this->assertDatabaseHas('khach_hang', [
            'cccd'        => '079099123456',
            'email'       => 'giabao@example.com',
            'soDienThoai' => '0912345678',
        ]);
    }

    /**
     * Test cập nhật hồ sơ khách hàng hiện có.
     */
    public function test_receptionist_can_update_existing_customer(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'hoTen'  => 'Nguyễn Văn Minh (Đã Cập Nhật)',
            'diaChi' => '789 Đường Mới, Q7, TP.HCM',
        ];

        $response = $this->putJson("/api/v1/customers/{$this->customerProfile->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Cập nhật thông tin khách hàng thành công.',
                'data'    => [
                    'id'     => $this->customerProfile->id,
                    'hoTen'  => 'Nguyễn Văn Minh (Đã Cập Nhật)',
                    'diaChi' => '789 Đường Mới, Q7, TP.HCM',
                ],
            ]);

        $this->assertDatabaseHas('khach_hang', [
            'id'     => $this->customerProfile->id,
            'hoTen'  => 'Nguyễn Văn Minh (Đã Cập Nhật)',
            'diaChi' => '789 Đường Mới, Q7, TP.HCM',
        ]);
    }

    /**
     * Test xem lịch sử đặt phòng & lưu trú của khách hàng.
     */
    public function test_receptionist_can_view_stay_history_of_customer(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson("/api/v1/customers/{$this->customerProfile->id}/stay-history");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy lịch sử lưu trú của khách hàng thành công.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'khachHang'      => ['id', 'hoTen', 'cccd', 'email', 'soDienThoai'],
                    'lichSuDatPhong' => [
                        '*' => [
                            'maDatPhong',
                            'ngayDat',
                            'ngayNhanPhong',
                            'ngayTraPhong',
                            'soLuongKhach',
                            'tienCoc',
                            'trangThai',
                            'danhSachPhong' => [
                                '*' => ['maChiTiet', 'tenPhong', 'tenLoaiPhong', 'giaPhongApDung', 'luuTru'],
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data.lichSuDatPhong'));
    }

    // ==========================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // ==========================================

    /**
     * Test tìm kiếm khách hàng theo họ tên.
     */
    public function test_search_customers_by_name(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers?keyword=Minh');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Nguyễn Văn Minh', $response->json('data.0.hoTen'));
    }

    /**
     * Test tìm kiếm khách hàng theo CCCD.
     */
    public function test_search_customers_by_cccd(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers?keyword=079099000085');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Trần Thị Thu Hà', $response->json('data.0.hoTen'));
    }

    /**
     * Test tìm kiếm khách hàng theo SĐT.
     */
    public function test_search_customers_by_phone(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers?keyword=0904000084');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Nguyễn Văn Minh', $response->json('data.0.hoTen'));
    }

    /**
     * Test tìm kiếm khách hàng theo email.
     */
    public function test_search_customers_by_email(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers?keyword=thuha@example.com');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Trần Thị Thu Hà', $response->json('data.0.hoTen'));
    }

    /**
     * Test phân trang khi có tham số page.
     */
    public function test_pagination_meta_included_when_page_parameter_present(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/customers?page=1&per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    /**
     * Test khách hàng xem thông tin và lịch sử của chính mình, nhưng bị từ chối 403 khi xem của khách khác.
     */
    public function test_customer_can_view_own_detail_and_history_but_forbidden_for_other(): void
    {
        // 1. Xem của chính mình -> OK 200
        Sanctum::actingAs($this->customerUser);
        $responseOwn = $this->getJson("/api/v1/customers/{$this->customerProfile->id}");
        $responseOwn->assertStatus(200);

        $responseOwnHistory = $this->getJson("/api/v1/customers/{$this->customerProfile->id}/stay-history");
        $responseOwnHistory->assertStatus(200);

        // 2. Khách hàng khác xem hồ sơ của người này -> 403 Forbidden
        Sanctum::actingAs($this->otherCustomerUser);
        $responseOther = $this->getJson("/api/v1/customers/{$this->customerProfile->id}");
        $responseOther->assertStatus(403);

        $responseOtherHistory = $this->getJson("/api/v1/customers/{$this->customerProfile->id}/stay-history");
        $responseOtherHistory->assertStatus(403);
    }

    /**
     * Test xóa khách hàng chưa từng phát sinh giao dịch thành công.
     */
    public function test_delete_customer_without_transactions_succeeds(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->deleteJson("/api/v1/customers/{$this->cleanCustomer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Xóa hồ sơ khách hàng thành công.',
            ]);

        $this->assertDatabaseMissing('khach_hang', [
            'id' => $this->cleanCustomer->id,
        ]);
    }

    // ==========================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // ==========================================

    /**
     * Test thiếu họ tên hoặc CCCD trả về lỗi validation 422.
     */
    public function test_create_customer_fails_when_required_fields_missing(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/customers', [
            'email'       => 'test@example.com',
            'soDienThoai' => '0900000000',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ])
            ->assertJsonValidationErrors(['hoTen', 'cccd']);
    }

    /**
     * Test email sai định dạng nhận lỗi 422.
     */
    public function test_create_customer_fails_with_invalid_email_format(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Người Dùng Test',
            'cccd'        => '079099999999',
            'email'       => 'email-sai-dinh-dang',
            'soDienThoai' => '0901234567',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test tạo khách hàng trùng CCCD nhận 422.
     */
    public function test_create_customer_fails_with_duplicate_cccd(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Trùng CCCD',
            'cccd'        => '079099000084', // Trùng của customerProfile
            'email'       => 'different@example.com',
            'soDienThoai' => '0909999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cccd']);
    }

    /**
     * Test tạo khách hàng trùng SĐT nhận 422.
     */
    public function test_create_customer_fails_with_duplicate_phone(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Trùng SĐT',
            'cccd'        => '079099111222',
            'email'       => 'unique_mail@example.com',
            'soDienThoai' => '0904000084', // Trùng của customerProfile
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['soDienThoai']);
    }

    /**
     * Test tạo khách hàng trùng Email nhận 422.
     */
    public function test_create_customer_fails_with_duplicate_email(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Trùng Email',
            'cccd'        => '079099333444',
            'email'       => 'nguyenvanminh@example.com', // Trùng của customerProfile
            'soDienThoai' => '0908888888',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test cập nhật khách hàng trùng CCCD của khách khác nhận 422.
     */
    public function test_update_customer_fails_with_duplicate_cccd_of_another_customer(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->putJson("/api/v1/customers/{$this->otherCustomerProfile->id}", [
            'cccd' => '079099000084', // CCCD của customerProfile
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cccd']);
    }

    /**
     * Test cập nhật khách hàng giữ nguyên CCCD/SĐT/email của chính mình thành công (Rule::ignore).
     */
    public function test_update_customer_succeeds_keeping_same_cccd_phone_email(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->putJson("/api/v1/customers/{$this->customerProfile->id}", [
            'hoTen'       => 'Nguyễn Văn Minh (Cập Nhật)',
            'cccd'        => '079099000084',
            'email'       => 'nguyenvanminh@example.com',
            'soDienThoai' => '0904000084',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
            ]);
    }

    /**
     * Test xem hoặc cập nhật khách hàng không tồn tại nhận 404.
     */
    public function test_view_or_update_non_existent_customer_returns_404(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $responseGet = $this->getJson('/api/v1/customers/999999');
        $responseGet->assertStatus(404);

        $responsePut = $this->putJson('/api/v1/customers/999999', ['hoTen' => 'Test']);
        $responsePut->assertStatus(404);
    }

    /**
     * Test không thể xóa khách hàng đã có lịch sử đặt phòng (BR02.6), nhận 409 Conflict.
     */
    public function test_delete_customer_with_booking_history_returns_409(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->deleteJson("/api/v1/customers/{$this->customerProfile->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('đã từng phát sinh giao dịch', $response->json('message'));
        $this->assertDatabaseHas('khach_hang', ['id' => $this->customerProfile->id]);
    }

    /**
     * Test không có token xác thực nhận 401 Unauthorized.
     */
    public function test_unauthenticated_cannot_access_customer_apis_returns_401(): void
    {
        $responseList = $this->getJson('/api/v1/customers');
        $responseList->assertStatus(401);

        $responseDetail = $this->getJson("/api/v1/customers/{$this->customerProfile->id}");
        $responseDetail->assertStatus(401);

        $responsePost = $this->postJson('/api/v1/customers', ['hoTen' => 'Test']);
        $responsePost->assertStatus(401);
    }

    /**
     * Test vai trò không hợp lệ (Buồng phòng) nhận 403 Forbidden.
     */
    public function test_unauthorized_role_cannot_manage_customers_returns_403(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        // Buồng phòng xem danh sách -> 403
        $responseList = $this->getJson('/api/v1/customers');
        $responseList->assertStatus(403);

        // Buồng phòng tạo khách hàng -> 403
        $responsePost = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Khách Mới',
            'cccd'        => '079099888777',
            'email'       => 'new@example.com',
            'soDienThoai' => '0988776655',
        ]);
        $responsePost->assertStatus(403);
    }

    /**
     * Test khi lỗi DB xảy ra trong lúc tạo khách hàng, transaction tự rollback và trả về 500.
     */
    public function test_database_error_triggers_rollback_and_returns_500(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        Khach_Hang::creating(function () {
            throw new \Exception('Simulated database error on creating customer.');
        });

        $response = $this->postJson('/api/v1/customers', [
            'hoTen'       => 'Lỗi DB Test',
            'cccd'        => '079099555666',
            'email'       => 'error_test@example.com',
            'soDienThoai' => '0977665544',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
                'message' => 'Có lỗi xảy ra khi tạo khách hàng. Vui lòng thử lại.',
            ]);

        $this->assertDatabaseMissing('khach_hang', [
            'cccd' => '079099555666',
        ]);
    }
}
