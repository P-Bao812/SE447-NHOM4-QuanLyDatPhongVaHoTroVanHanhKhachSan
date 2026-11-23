<?php

namespace Tests\Feature;

use App\Models\Khach_Hang;
use App\Models\Nhan_Vien;
use App\Models\Tai_Khoan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Tai_Khoan $customerUser;
    protected Khach_Hang $customerProfile;

    protected Tai_Khoan $otherCustomerUser;
    protected Khach_Hang $otherCustomerProfile;

    protected Tai_Khoan $managerUser;
    protected Nhan_Vien $managerProfile;

    protected Tai_Khoan $otherEmployeeUser;
    protected Nhan_Vien $otherEmployeeProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Khách hàng chính
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'khachhang1',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Khách',
            'cccd'        => '048200000001',
            'email'       => 'khachhang1@gmail.com',
            'soDienThoai' => '0912345678',
            'diaChi'      => '123 Đường Bạch Đằng, Đà Nẵng',
        ]);

        // 2. Khách hàng khác (để test trùng email/SĐT)
        $this->otherCustomerUser = Tai_Khoan::create([
            'tenDangNhap' => 'khachhang2',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->otherCustomerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->otherCustomerUser->id,
            'hoTen'       => 'Trần Thị Khách 2',
            'cccd'        => '048200000002',
            'email'       => 'khachhang2@gmail.com',
            'soDienThoai' => '0987654321',
            'diaChi'      => '456 Đường Lê Lợi, Đà Nẵng',
        ]);

        // 3. Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'admin',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Lê Văn Quản Lý',
            'ngaySinh'    => '1988-06-20',
            'cccd'        => '048188000001',
            'soDienThoai' => '0905111222',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm',
        ]);

        // 4. Nhân viên khác (để test trùng SĐT nhân viên)
        $this->otherEmployeeUser = Tai_Khoan::create([
            'tenDangNhap' => 'letan1',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->otherEmployeeProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->otherEmployeeUser->id,
            'hoTen'       => 'Phạm Văn Lễ Tân',
            'ngaySinh'    => '1995-10-12',
            'cccd'        => '048195000002',
            'soDienThoai' => '0905333444',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm',
        ]);
    }

    // =========================================================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // =========================================================================

    /**
     * Test Case 1: Khách hàng xem thông tin hồ sơ của chính mình qua GET /api/v1/auth/me.
     */
    public function test_customer_can_view_own_profile(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy thông tin tài khoản thành công.',
                'data'    => [
                    'user' => [
                        'id'          => $this->customerUser->id,
                        'tenDangNhap' => 'khachhang1',
                        'vaiTro'      => 'Khách hàng',
                        'hoTen'       => 'Nguyễn Văn Khách',
                        'email'       => 'khachhang1@gmail.com',
                        'soDienThoai' => '0912345678',
                        'diaChi'      => '123 Đường Bạch Đằng, Đà Nẵng',
                    ],
                ],
            ]);
    }

    /**
     * Test Case 2: Khách hàng cập nhật họ tên, email, SĐT, địa chỉ qua PUT /api/v1/auth/profile.
     */
    public function test_customer_can_update_profile_successfully(): void
    {
        Sanctum::actingAs($this->customerUser);

        $payload = [
            'hoTen'       => 'Nguyễn Văn Khách Đã Đổi',
            'email'       => 'khach_moi@gmail.com',
            'soDienThoai' => '0933444555',
            'diaChi'      => '789 Đường Hùng Vương, Đà Nẵng',
        ];

        $response = $this->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Cập nhật hồ sơ cá nhân thành công.',
                'data'    => [
                    'user' => [
                        'id'          => $this->customerUser->id,
                        'hoTen'       => 'Nguyễn Văn Khách Đã Đổi',
                        'email'       => 'khach_moi@gmail.com',
                        'soDienThoai' => '0933444555',
                        'diaChi'      => '789 Đường Hùng Vương, Đà Nẵng',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('khach_hang', [
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Khách Đã Đổi',
            'email'       => 'khach_moi@gmail.com',
            'soDienThoai' => '0933444555',
            'diaChi'      => '789 Đường Hùng Vương, Đà Nẵng',
        ]);
    }

    /**
     * Test Case 3: Quản lý xem thông tin hồ sơ của chính mình qua GET /api/v1/auth/me.
     */
    public function test_manager_can_view_own_profile(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'user' => [
                        'id'          => $this->managerUser->id,
                        'tenDangNhap' => 'admin',
                        'vaiTro'      => 'Quản lý',
                        'hoTen'       => 'Lê Văn Quản Lý',
                        'soDienThoai' => '0905111222',
                    ],
                ],
            ]);
    }

    /**
     * Test Case 4: Quản lý / Nhân viên cập nhật họ tên, SĐT, ngày sinh qua PUT /api/v1/auth/profile.
     */
    public function test_manager_can_update_profile_successfully(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'hoTen'       => 'Lê Văn Tổng Quản Lý',
            'soDienThoai' => '0905999888',
            'ngaySinh'    => '1987-12-25',
        ];

        $response = $this->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Cập nhật hồ sơ cá nhân thành công.',
                'data'    => [
                    'user' => [
                        'id'          => $this->managerUser->id,
                        'hoTen'       => 'Lê Văn Tổng Quản Lý',
                        'soDienThoai' => '0905999888',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('nhan_vien', [
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Lê Văn Tổng Quản Lý',
            'soDienThoai' => '0905999888',
            'ngaySinh'    => '1987-12-25',
        ]);
    }

    /**
     * Test Case 5: Sau khi cập nhật, nạp lại GET /api/v1/auth/me nhận được thông tin mới nhất.
     */
    public function test_profile_refresh_me_returns_updated_data(): void
    {
        Sanctum::actingAs($this->customerUser);

        $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Tên Mới Hoàn Toàn',
            'email'       => 'tenmoi@gmail.com',
            'soDienThoai' => '0911223344',
            'diaChi'      => 'Quận Cẩm Lệ, Đà Nẵng',
        ])->assertStatus(200);

        $meResponse = $this->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => [
                        'hoTen'       => 'Tên Mới Hoàn Toàn',
                        'email'       => 'tenmoi@gmail.com',
                        'soDienThoai' => '0911223344',
                        'diaChi'      => 'Quận Cẩm Lệ, Đà Nẵng',
                    ],
                ],
            ]);
    }

    // =========================================================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // =========================================================================

    /**
     * Test Case 6: Cập nhật bằng phương thức PATCH /api/v1/auth/profile thành công.
     */
    public function test_customer_can_update_profile_via_patch_method(): void
    {
        Sanctum::actingAs($this->customerUser);

        $payload = [
            'hoTen'       => 'Nguyễn Văn Khách PATCH',
            'email'       => 'khach_patch@gmail.com',
            'soDienThoai' => '0933111222',
        ];

        $response = $this->patchJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'user' => [
                        'hoTen' => 'Nguyễn Văn Khách PATCH',
                        'email' => 'khach_patch@gmail.com',
                    ],
                ],
            ]);
    }

    /**
     * Test Case 7: Khách hàng cập nhật giữ nguyên email và SĐT của chính mình (unique ignore).
     */
    public function test_customer_can_keep_own_email_and_phone(): void
    {
        Sanctum::actingAs($this->customerUser);

        $payload = [
            'hoTen'       => 'Nguyễn Văn Khách Đổi Tên Thôi',
            'email'       => 'khachhang1@gmail.com', // email hiện tại
            'soDienThoai' => '0912345678',          // SĐT hiện tại
            'diaChi'      => '123 Đường Bạch Đằng, Đà Nẵng',
        ];

        $response = $this->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.hoTen', 'Nguyễn Văn Khách Đổi Tên Thôi');
    }

    /**
     * Test Case 8: Nhân viên cập nhật giữ nguyên SĐT của chính mình (unique ignore).
     */
    public function test_employee_can_keep_own_phone(): void
    {
        Sanctum::actingAs($this->managerUser);

        $payload = [
            'hoTen'       => 'Lê Văn Quản Lý Tên Mới',
            'soDienThoai' => '0905111222', // SĐT hiện tại của quản lý
        ];

        $response = $this->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.hoTen', 'Lê Văn Quản Lý Tên Mới');
    }

    /**
     * Test Case 9: Người dùng chỉ xem hồ sơ mà không cập nhật thì CSDL không đổi.
     */
    public function test_viewing_profile_does_not_mutate_database(): void
    {
        Sanctum::actingAs($this->customerUser);

        $this->getJson('/api/v1/auth/me')->assertStatus(200);

        $this->assertDatabaseHas('khach_hang', [
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Khách',
            'email'       => 'khachhang1@gmail.com',
            'soDienThoai' => '0912345678',
        ]);
    }

    /**
     * Test Case 10: Tài khoản chưa có bản ghi khach_hang khi gọi updateProfile sẽ tự tạo bản ghi.
     */
    public function test_account_without_customer_record_creates_one_on_update(): void
    {
        $newCustomer = Tai_Khoan::create([
            'tenDangNhap' => 'brandnewcustomer',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);

        Sanctum::actingAs($newCustomer);

        $payload = [
            'hoTen'       => 'Khách Mới Tạo',
            'email'       => 'newcreated@example.com',
            'soDienThoai' => '0944555666',
            'diaChi'      => 'Đà Nẵng',
        ];

        $response = $this->putJson('/api/v1/auth/profile', $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('khach_hang', [
            'maTaiKhoan'  => $newCustomer->id,
            'hoTen'       => 'Khách Mới Tạo',
            'email'       => 'newcreated@example.com',
            'soDienThoai' => '0944555666',
        ]);
    }

    // =========================================================================
    // 3. LUỒNG NGOẠI LỆ & BẢO MẬT (EXCEPTION & SECURITY FLOW)
    // =========================================================================

    /**
     * Test Case 11: Chưa xác thực danh tính gọi GET /api/v1/auth/me trả về 401.
     */
    public function test_unauthenticated_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Test Case 12: Chưa xác thực danh tính gọi PUT /api/v1/auth/profile trả về 401.
     */
    public function test_unauthenticated_cannot_update_profile(): void
    {
        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Hacker',
            'soDienThoai' => '0900000000',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test Case 13: Thiếu họ tên (hoTen) trả về 422 Unprocessable Entity.
     */
    public function test_update_profile_validation_requires_hoten(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '0912345678',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
                'message' => 'Dữ liệu cập nhật hồ sơ không hợp lệ.',
            ])
            ->assertJsonValidationErrors(['hoTen']);
    }

    /**
     * Test Case 14: Thiếu số điện thoại (soDienThoai) trả về 422.
     */
    public function test_update_profile_validation_requires_sodienthoai(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen' => 'Nguyễn Văn A',
            'email' => 'khach@gmail.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['soDienThoai']);
    }

    /**
     * Test Case 15: Số điện thoại không đúng định dạng (chứa chữ hoặc không đủ 9-15 số) trả về 422.
     */
    public function test_update_profile_validation_invalid_phone_format(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Nguyễn Văn A',
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '090-abc-xyz',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['soDienThoai']);
    }

    /**
     * Test Case 16: Khách hàng thiếu email trả về 422.
     */
    public function test_customer_update_profile_requires_email(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Nguyễn Văn A',
            'soDienThoai' => '0912345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test Case 17: Khách hàng nhập email sai định dạng trả về 422.
     */
    public function test_customer_update_profile_invalid_email_format(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Nguyễn Văn A',
            'email'       => 'not-an-email',
            'soDienThoai' => '0912345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test Case 18: Khách hàng cập nhật email trùng với khách hàng khác trả về 422.
     */
    public function test_customer_cannot_use_email_of_another_customer(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Nguyễn Văn A',
            'email'       => 'khachhang2@gmail.com', // Trùng với otherCustomerProfile
            'soDienThoai' => '0912345678',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test Case 19: Khách hàng cập nhật số điện thoại trùng với khách hàng khác trả về 422.
     */
    public function test_customer_cannot_use_phone_of_another_customer(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Nguyễn Văn A',
            'email'       => 'khachhang1@gmail.com',
            'soDienThoai' => '0987654321', // Trùng với otherCustomerProfile
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['soDienThoai']);
    }

    /**
     * Test Case 20: Nhân viên cập nhật số điện thoại trùng với nhân viên khác trả về 422.
     */
    public function test_employee_cannot_use_phone_of_another_employee(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Lê Văn Quản Lý',
            'soDienThoai' => '0905333444', // Trùng với otherEmployeeProfile
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['soDienThoai']);
    }

    /**
     * Test Case 21: Cố tình gửi vaiTro trong payload bị cấm (prohibited) trả về 422.
     */
    public function test_update_profile_prohibits_vaitro(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Khách Hàng',
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '0912345678',
            'vaiTro'      => 'Quản lý', // Cố tình đổi vai trò
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vaiTro']);
    }

    /**
     * Test Case 22: Cố tình gửi tenDangNhap trong payload bị cấm trả về 422.
     */
    public function test_update_profile_prohibits_tendangnhap(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Khách Hàng',
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '0912345678',
            'tenDangNhap' => 'new_admin_username',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tenDangNhap']);
    }

    /**
     * Test Case 23: Cố tình gửi cccd trong payload bị cấm trả về 422.
     */
    public function test_update_profile_prohibits_cccd(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Khách Hàng',
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '0912345678',
            'cccd'        => '099999999999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cccd']);
    }

    /**
     * Test Case 24: Cố tình gửi chucVu hoặc trangThai trong payload bị cấm trả về 422.
     */
    public function test_update_profile_prohibits_chucvu_and_trangthai(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Quản Lý',
            'soDienThoai' => '0905111222',
            'chucVu'      => 'Tổng Giám Đốc',
            'trangThai'   => 'Nghỉ việc',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['chucVu', 'trangThai']);
    }

    /**
     * Test Case 25: Mật khẩu người dùng không bị thay đổi hoặc lộ qua endpoint profile.
     */
    public function test_update_profile_does_not_mutate_password(): void
    {
        Sanctum::actingAs($this->customerUser);

        $oldPasswordHash = $this->customerUser->password;

        $response = $this->putJson('/api/v1/auth/profile', [
            'hoTen'       => 'Khách Hàng',
            'email'       => 'khach@gmail.com',
            'soDienThoai' => '0912345678',
            'password'    => 'NewHackerPassword123@',
        ]);

        $response->assertStatus(200);

        $this->customerUser->refresh();
        $this->assertEquals($oldPasswordHash, $this->customerUser->password);
        $this->assertTrue(Hash::check('Password123@', $this->customerUser->password));
    }
}
