<?php

namespace Tests\Feature;

use App\Models\Khach_Hang;
use App\Models\Nhan_Vien;
use App\Models\Tai_Khoan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Khởi tạo tài khoản quản lý mẫu
        $admin = Tai_Khoan::create([
            'tenDangNhap' => 'admin',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);

        Nhan_Vien::create([
            'maTaiKhoan'  => $admin->id,
            'hoTen'       => 'Trần Văn Quản Lý',
            'cccd'        => '001190005555',
            'ngaySinh'    => '1990-05-15',
            'soDienThoai' => '0988777666',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm',
        ]);

        // Khởi tạo tài khoản khách hàng có sẵn mẫu để kiểm tra trùng dữ liệu
        $existingUser = Tai_Khoan::create([
            'tenDangNhap' => 'existing_customer',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);

        Khach_Hang::create([
            'maTaiKhoan'  => $existingUser->id,
            'hoTen'       => 'Khách Hàng Cũ',
            'cccd'        => '001200000001',
            'email'       => 'oldcustomer@example.com',
            'soDienThoai' => '0901112222',
            'diaChi'      => 'Hà Nội',
        ]);

        // Khởi tạo tài khoản bị khóa mẫu
        Tai_Khoan::create([
            'tenDangNhap' => 'user_locked',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Khóa',
        ]);
    }

    // ==========================================
    // UC01: CÁC TEST CASES ĐĂNG KÝ TÀI KHOẢN
    // ==========================================

    /**
     * Test Case 1 - Luồng chính: Đăng ký thành công trả về HTTP 201, cấu trúc JSON chuẩn.
     */
    public function test_register_success_with_valid_data(): void
    {
        $payload = [
            'hoTen'                 => 'Nguyễn Văn Khách',
            'email'                 => 'nguyenkhach@example.com',
            'soDienThoai'           => '0912345678',
            'cccd'                  => '001203009999',
            'diaChi'                => 'Đà Nẵng',
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status'  => 201,
                'message' => 'Đăng ký tài khoản thành công.',
                'data'    => [
                    'user' => [
                        'vaiTro'      => 'Khách hàng',
                        'hoTen'       => 'Nguyễn Văn Khách',
                        'email'       => 'nguyenkhach@example.com',
                        'soDienThoai' => '0912345678',
                    ],
                ],
            ])
            ->assertJsonMissing(['password', 'access_token']);

        // Kiểm tra cơ sở dữ liệu đã lưu đúng và mật khẩu đã được băm
        $createdTaiKhoan = Tai_Khoan::where('tenDangNhap', 'nguyenkhach@example.com')->first();
        $this->assertNotNull($createdTaiKhoan);
        $this->assertTrue(Hash::check('Password123@', $createdTaiKhoan->password));
        $this->assertEquals('Hoạt động', $createdTaiKhoan->trangThai);

        $createdKhachHang = Khach_Hang::where('maTaiKhoan', $createdTaiKhoan->id)->first();
        $this->assertNotNull($createdKhachHang);
        $this->assertEquals('001203009999', $createdKhachHang->cccd);
        $this->assertEquals('Khách hàng', $createdTaiKhoan->vaiTro);
    }

    /**
     * Test Case 2 - Luồng thay thế: Thiếu trường bắt buộc hoặc mật khẩu không khớp -> 422.
     */
    public function test_register_fails_when_required_fields_missing_or_password_mismatch(): void
    {
        // 1. Trống toàn bộ
        $response1 = $this->postJson('/api/v1/auth/register', []);
        $response1->assertStatus(422)
            ->assertJsonStructure(['errors' => ['hoTen', 'email', 'soDienThoai']]);

        // 2. Email sai định dạng, mật khẩu xác nhận không khớp
        $response2 = $this->postJson('/api/v1/auth/register', [
            'hoTen'                 => 'Trần B',
            'email'                 => 'invalid-email',
            'soDienThoai'           => '0933333333',
            'password'              => 'Password123@',
            'password_confirmation' => 'MismatchPass!',
        ]);
        $response2->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email', 'password']]);
    }

    /**
     * Test Case 3 - Trùng dữ liệu: Trùng email, số điện thoại, CCCD hoặc tên đăng nhập -> 422.
     */
    public function test_register_fails_with_duplicate_email_phone_or_cccd(): void
    {
        // 1. Trùng Email
        $res1 = $this->postJson('/api/v1/auth/register', [
            'hoTen'                 => 'Khách Mới',
            'email'                 => 'oldcustomer@example.com', // Đã tồn tại
            'soDienThoai'           => '0987654321',
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ]);
        $res1->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email']]);

        // 2. Trùng Số điện thoại
        $res2 = $this->postJson('/api/v1/auth/register', [
            'hoTen'                 => 'Khách Mới 2',
            'email'                 => 'newcustomer2@example.com',
            'soDienThoai'           => '0901112222', // Đã tồn tại
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ]);
        $res2->assertStatus(422)
            ->assertJsonStructure(['errors' => ['soDienThoai']]);

        // 3. Trùng CCCD
        $res3 = $this->postJson('/api/v1/auth/register', [
            'hoTen'                 => 'Khách Mới 3',
            'email'                 => 'newcustomer3@example.com',
            'soDienThoai'           => '0987111222',
            'cccd'                  => '001200000001', // Đã tồn tại
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ]);
        $res3->assertStatus(422)
            ->assertJsonStructure(['errors' => ['cccd']]);
    }

    /**
     * Test Case 4 - Luồng ngoại lệ: Mô phỏng lỗi khi tạo hồ sơ khach_hang -> Rollback.
     */
    public function test_register_rollbacks_transaction_on_failure(): void
    {
        $initialTaiKhoanCount = Tai_Khoan::count();

        // Gây lỗi giả định bằng cách lắng nghe sự kiện tạo Khach_Hang và ném ngoại lệ
        Khach_Hang::saving(function () {
            throw new \Exception('Lỗi mô phỏng CSDL khi tạo khách hàng.');
        });

        $response = $this->postJson('/api/v1/auth/register', [
            'hoTen'                 => 'Khách Rollback',
            'email'                 => 'rollback@example.com',
            'soDienThoai'           => '0999888777',
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
            ]);

        // Kiểm tra không có bản ghi tài khoản mồ côi nào tồn tại
        $this->assertEquals($initialTaiKhoanCount, Tai_Khoan::count());
        $this->assertNull(Tai_Khoan::where('tenDangNhap', 'rollback@example.com')->first());
    }

    /**
     * Test Case 6 - Hồi quy: Dùng tài khoản khách hàng vừa đăng ký đăng nhập qua POST /api/v1/auth/login.
     */
    public function test_newly_registered_customer_can_login_successfully(): void
    {
        // 1. Đăng ký tài khoản
        $registerRes = $this->postJson('/api/v1/auth/register', [
            'tenDangNhap'           => 'khach_login_test',
            'hoTen'                 => 'Khách Đăng Nhập',
            'email'                 => 'khachlogin@example.com',
            'soDienThoai'           => '0944555666',
            'password'              => 'Password123@',
            'password_confirmation' => 'Password123@',
        ]);
        $registerRes->assertStatus(201);

        // 2. Đăng nhập với tài khoản vừa tạo
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'tenDangNhap' => 'khach_login_test',
            'password'    => 'Password123@',
        ]);

        $loginRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'token_type' => 'Bearer',
                    'user'       => [
                        'tenDangNhap' => 'khach_login_test',
                        'vaiTro'      => 'Khách hàng',
                        'hoTen'       => 'Khách Đăng Nhập',
                    ],
                ],
            ]);
    }

    // ==========================================
    // UC02: CÁC TEST CASES ĐĂNG NHẬP HỆ THỐNG
    // ==========================================

    /**
     * Kiểm tra Validation khi để trống dữ liệu đăng nhập.
     */
    public function test_login_validation_fails_when_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
                'message' => 'Dữ liệu gửi lên không hợp lệ.',
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'errors' => ['tenDangNhap'],
            ]);
    }

    /**
     * Kiểm tra đăng nhập thất bại do sai mật khẩu.
     */
    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'tenDangNhap' => 'admin',
            'password'    => 'SaiMatKhau123@',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'status'  => 401,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác.',
            ]);
    }

    /**
     * Kiểm tra đăng nhập thất bại với tài khoản bị khóa.
     */
    public function test_login_fails_when_account_is_locked(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'tenDangNhap' => 'user_locked',
            'password'    => 'Password123@',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
    }

    /**
     * Kiểm tra đăng nhập thành công trả về Bearer Token và thông tin user chuẩn.
     */
    public function test_login_success_returns_token_and_user_info(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'tenDangNhap' => 'admin',
            'password'    => 'Password123@',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Đăng nhập thành công.',
                'data'    => [
                    'token_type' => 'Bearer',
                    'user'       => [
                        'tenDangNhap' => 'admin',
                        'vaiTro'      => 'Quản lý',
                        'hoTen'       => 'Trần Văn Quản Lý',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'tenDangNhap',
                        'vaiTro',
                        'hoTen',
                    ],
                ],
            ]);
    }
}
