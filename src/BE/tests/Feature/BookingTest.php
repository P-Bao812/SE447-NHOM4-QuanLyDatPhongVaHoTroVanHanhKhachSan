<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Khach_Hang;
use App\Models\Loai_Phong;
use App\Models\Nhan_Vien;
use App\Models\Phong;
use App\Models\Tai_Khoan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected Tai_Khoan $customerUser1;
    protected Khach_Hang $customerProfile1;
    protected Tai_Khoan $customerUser2;
    protected Khach_Hang $customerProfile2;
    protected Tai_Khoan $receptionistUser;
    protected Nhan_Vien $receptionistProfile;
    protected Tai_Khoan $housekeeperUser;
    protected Nhan_Vien $housekeeperProfile;

    protected Loai_Phong $roomTypeSingle;
    protected Loai_Phong $roomTypeDouble;
    protected Loai_Phong $roomTypeRetired;

    protected Phong $roomP101;
    protected Phong $roomP201;
    protected Phong $roomP202;
    protected Phong $roomMaintenance;
    protected Phong $roomRetiredType;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Khởi tạo Loại phòng mẫu
        $this->roomTypeSingle = Loai_Phong::create([
            'tenLoai'      => 'Single Room',
            'moTa'         => 'Phòng đơn cho 1 người',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 1,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomTypeDouble = Loai_Phong::create([
            'tenLoai'      => 'Double Room',
            'moTa'         => 'Phòng đôi cho 2 người',
            'giaTieuChuan' => 800000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomTypeRetired = Loai_Phong::create([
            'tenLoai'      => 'Old Room',
            'moTa'         => 'Loại phòng đã ngừng',
            'giaTieuChuan' => 600000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Ngừng kinh doanh',
        ]);

        // 2. Khởi tạo Phòng vật lý mẫu
        $this->roomP101 = Phong::create([
            'maLoaiPhong' => $this->roomTypeSingle->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomP201 = Phong::create([
            'maLoaiPhong' => $this->roomTypeDouble->id,
            'tenPhong'    => 'P201',
            'tang'        => 2,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomP202 = Phong::create([
            'maLoaiPhong' => $this->roomTypeDouble->id,
            'tenPhong'    => 'P202',
            'tang'        => 2,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomMaintenance = Phong::create([
            'maLoaiPhong' => $this->roomTypeSingle->id,
            'tenPhong'    => 'P301',
            'tang'        => 3,
            'trangThai'   => 'Đang bảo trì',
        ]);

        $this->roomRetiredType = Phong::create([
            'maLoaiPhong' => $this->roomTypeRetired->id,
            'tenPhong'    => 'P401',
            'tang'        => 4,
            'trangThai'   => 'Sẵn sàng',
        ]);

        // 3. Khách hàng 1
        $this->customerUser1 = Tai_Khoan::create([
            'tenDangNhap' => 'khach1',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile1 = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser1->id,
            'hoTen'       => 'Nguyễn Văn Khách 1',
            'cccd'        => '001200000001',
            'email'       => 'khach1@example.com',
            'soDienThoai' => '0901111111',
            'diaChi'      => 'Đà Nẵng',
        ]);

        // 4. Khách hàng 2
        $this->customerUser2 = Tai_Khoan::create([
            'tenDangNhap' => 'khach2',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile2 = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser2->id,
            'hoTen'       => 'Lê Thị Khách 2',
            'cccd'        => '001200000002',
            'email'       => 'khach2@example.com',
            'soDienThoai' => '0902222222',
            'diaChi'      => 'Hà Nội',
        ]);

        // 5. Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'letan',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Trần Lễ Tân',
            'cccd'        => '001190001111',
            'ngaySinh'    => '1995-01-01',
            'soDienThoai' => '0988111222',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm',
        ]);

        // 6. Buồng phòng (vai trò không có quyền đặt phòng)
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'buongphong',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Võ Buồng Phòng',
            'cccd'        => '001190002222',
            'ngaySinh'    => '1996-02-02',
            'soDienThoai' => '0988333444',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm',
        ]);
    }

    // ==========================================
    // TEST CASE 1: LUỒNG CHÍNH KHÁCH HÀNG TỰ ĐẶT PHÒNG
    // ==========================================

    public function test_customer_can_create_booking_successfully(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $checkIn = Carbon::tomorrow()->setTime(14, 0, 0)->format('Y-m-d H:i:s');
        $checkOut = Carbon::tomorrow()->addDays(2)->setTime(12, 0, 0)->format('Y-m-d H:i:s');

        $payload = [
            'ngayNhanPhong' => $checkIn,
            'ngayTraPhong'  => $checkOut,
            'soLuongKhach'  => 2,
            'tienCoc'       => 500000,
            'danhSachPhong' => [
                [
                    'maPhong'         => $this->roomP201->id,
                    'giaPhongApDung'  => 9999999, // Giá client gửi phải bị ghi đè bởi giá Backend tính
                    'soLuongKhach'    => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status'  => 201,
                'message' => 'Tạo đơn đặt phòng thành công.',
                'data'    => [
                    'trangThai'     => 'Chờ xác nhận',
                    'tienCoc'       => 500000,
                    'ngayNhanPhong' => $checkIn,
                    'ngayTraPhong'  => $checkOut,
                    'soLuongKhach'  => 2,
                ],
            ]);

        $bookingId = $response->json('data.maDatPhong');
        $this->assertNotNull($bookingId);

        // Kiểm tra database lưu đúng maKhachHang của user đang đăng nhập và maNhanVien là null
        $this->assertDatabaseHas('dat_phong', [
            'id'          => $bookingId,
            'maKhachHang' => $this->customerProfile1->id,
            'maNhanVien'  => null,
            'trangThai'   => 'Chờ xác nhận',
            'tienCoc'     => 500000,
            'soLuongKhach'=> 2,
        ]);

        // Kiểm tra chi tiết đơn đặt phòng lưu đúng giá chuẩn từ bảng loai_phong (800.000, không lấy 9.999.999)
        $this->assertDatabaseHas('chi_tiet_dat_phong', [
            'maDatPhong'     => $bookingId,
            'maPhong'        => $this->roomP201->id,
            'giaPhongApDung' => 800000,
            'soLuongKhach'   => 2,
        ]);
    }

    // ==========================================
    // TEST CASE 2: LUỒNG LỄ TÂN ĐẶT PHÒNG THAY KHÁCH
    // ==========================================

    public function test_receptionist_can_create_booking_for_customer(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $checkIn = Carbon::tomorrow()->setTime(14, 0, 0)->format('Y-m-d H:i:s');
        $checkOut = Carbon::tomorrow()->addDay()->setTime(12, 0, 0)->format('Y-m-d H:i:s');

        $payload = [
            'maKhachHang'   => $this->customerProfile2->id,
            'ngayNhanPhong' => $checkIn,
            'ngayTraPhong'  => $checkOut,
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                [
                    'maPhong'      => $this->roomP101->id,
                    'soLuongKhach' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(201);
        $bookingId = $response->json('data.maDatPhong');

        // Xác minh gắn đúng khách hàng 2 và nhân viên lễ tân
        $this->assertDatabaseHas('dat_phong', [
            'id'          => $bookingId,
            'maKhachHang' => $this->customerProfile2->id,
            'maNhanVien'  => $this->receptionistProfile->id,
            'trangThai'   => 'Chờ xác nhận',
        ]);
    }

    // ==========================================
    // TEST CASE 3: LUỒNG THAY THẾ - ĐẶT NHIỀU PHÒNG
    // ==========================================

    public function test_booking_multiple_rooms_calculates_total_and_saves_all_details(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $checkIn = Carbon::tomorrow()->setTime(14, 0, 0)->format('Y-m-d H:i:s');
        $checkOut = Carbon::tomorrow()->addDays(2)->setTime(12, 0, 0)->format('Y-m-d H:i:s');

        $payload = [
            'ngayNhanPhong' => $checkIn,
            'ngayTraPhong'  => $checkOut,
            'soLuongKhach'  => 3,
            'danhSachPhong' => [
                [
                    'maPhong'      => $this->roomP101->id, // Single: 500k, 1 khách
                    'soLuongKhach' => 1,
                ],
                [
                    'maPhong'      => $this->roomP201->id, // Double: 800k, 2 khách
                    'soLuongKhach' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(201);
        $bookingId = $response->json('data.maDatPhong');

        // Kiểm tra có đủ 2 bản ghi chi tiết đặt phòng
        $this->assertDatabaseCount('chi_tiet_dat_phong', 2);
        $this->assertDatabaseHas('chi_tiet_dat_phong', [
            'maDatPhong'     => $bookingId,
            'maPhong'        => $this->roomP101->id,
            'giaPhongApDung' => 500000,
        ]);
        $this->assertDatabaseHas('chi_tiet_dat_phong', [
            'maDatPhong'     => $bookingId,
            'maPhong'        => $this->roomP201->id,
            'giaPhongApDung' => 800000,
        ]);
    }

    // ==========================================
    // TEST CASE 4: KIỂM TRA DỮ LIỆU BIÊN VÀ VALIDATION (422)
    // ==========================================

    public function test_booking_fails_when_checkin_in_the_past(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'ngayNhanPhong' => Carbon::yesterday()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ngayNhanPhong']);
    }

    public function test_booking_fails_when_checkout_before_or_equal_to_checkin(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $time = Carbon::tomorrow()->setTime(14, 0, 0)->format('Y-m-d H:i:s');

        $payload = [
            'ngayNhanPhong' => $time,
            'ngayTraPhong'  => $time,
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ngayTraPhong']);
    }

    public function test_booking_fails_when_room_capacity_exceeded(): void
    {
        Sanctum::actingAs($this->customerUser1);

        // Room P101 là Single Room (sức chứa tối đa 1 người), gửi 2 người
        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 2,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 2],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['danhSachPhong.0.soLuongKhach']);
    }

    public function test_booking_fails_when_deposit_is_negative(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'tienCoc'       => -50000,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tienCoc']);
    }

    public function test_booking_fails_when_room_list_is_empty(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['danhSachPhong']);
    }

    public function test_booking_fails_when_room_is_under_maintenance(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomMaintenance->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['danhSachPhong.0.maPhong']);
    }

    public function test_booking_fails_when_room_type_is_retired(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomRetiredType->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['danhSachPhong.0.maPhong']);
    }

    // ==========================================
    // TEST CASE 5: XUNG ĐỘT LỊCH ĐẶT PHÒNG (409 CONFLICT)
    // ==========================================

    public function test_booking_returns_409_conflict_when_room_already_booked(): void
    {
        // 1. Tạo đơn đặt trước cho phòng P201 từ ngày 10 đến ngày 15
        $existingBooking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile1->id,
            'maNhanVien'    => null,
            'ngayDat'       => now(),
            'ngayNhanPhong' => Carbon::parse('2026-12-10 14:00:00'),
            'ngayTraPhong'  => Carbon::parse('2026-12-15 12:00:00'),
            'soLuongKhach'  => 2,
            'tienCoc'       => 500000,
            'trangThai'     => 'Chờ xác nhận',
        ]);

        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $existingBooking->id,
            'maPhong'        => $this->roomP201->id,
            'giaPhongApDung' => 800000,
            'soLuongKhach'   => 2,
        ]);

        // 2. Khách khác cố gắng đặt phòng P201 trong khoảng thời gian giao nhau (12 đến 17)
        Sanctum::actingAs($this->customerUser2);

        $payload = [
            'ngayNhanPhong' => '2026-12-12 14:00:00',
            'ngayTraPhong'  => '2026-12-17 12:00:00',
            'soLuongKhach'  => 2,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP201->id, 'soLuongKhach' => 2],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
                'message' => 'Phòng đã được khách khác đặt trong khung giờ yêu cầu.',
                'errors'  => [
                    'phongBiTrung' => ['P201'],
                ],
            ]);

        // Đảm bảo không tạo thêm đơn đặt phòng mới trong DB
        $this->assertDatabaseCount('dat_phong', 1);
    }

    public function test_booking_succeeds_when_overlapping_booking_was_cancelled(): void
    {
        // Đơn cũ đã bị Hủy thì không xung đột
        $cancelledBooking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile1->id,
            'maNhanVien'    => null,
            'ngayDat'       => now(),
            'ngayNhanPhong' => Carbon::parse('2026-12-10 14:00:00'),
            'ngayTraPhong'  => Carbon::parse('2026-12-15 12:00:00'),
            'soLuongKhach'  => 2,
            'tienCoc'       => 0,
            'trangThai'     => 'Đã hủy',
        ]);

        Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $cancelledBooking->id,
            'maPhong'        => $this->roomP201->id,
            'giaPhongApDung' => 800000,
            'soLuongKhach'   => 2,
        ]);

        Sanctum::actingAs($this->customerUser2);

        $payload = [
            'ngayNhanPhong' => '2026-12-12 14:00:00',
            'ngayTraPhong'  => '2026-12-14 12:00:00',
            'soLuongKhach'  => 2,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP201->id, 'soLuongKhach' => 2],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(201);
    }

    // ==========================================
    // TEST CASE 6: PHÂN QUYỀN VÀ BẢO MẬT (401, 403)
    // ==========================================

    public function test_unauthenticated_user_cannot_create_booking(): void
    {
        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(401);
    }

    public function test_unauthorized_role_cannot_create_booking(): void
    {
        // Tài khoản Buồng phòng không có quyền đặt phòng
        Sanctum::actingAs($this->housekeeperUser);

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
            ]);
    }

    public function test_customer_cannot_book_for_another_customer(): void
    {
        // Khách hàng 1 cố gắng đặt phòng với maKhachHang của Khách hàng 2
        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'maKhachHang'   => $this->customerProfile2->id,
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
                'message' => 'Khách hàng không được phép đặt phòng thay cho người khác.',
            ]);
    }

    // ==========================================
    // TEST CASE 7: TRANSACTION ROLLBACK
    // ==========================================

    public function test_transaction_rollbacks_when_detail_insert_fails(): void
    {
        Sanctum::actingAs($this->customerUser1);

        // Can thiệp DB hook hoặc trigger lỗi trong transaction bằng cách tạm drop bảng chi_tiet_dat_phong
        DB::statement('DROP TABLE chi_tiet_dat_phong');

        $payload = [
            'ngayNhanPhong' => Carbon::tomorrow()->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::tomorrow()->addDay()->format('Y-m-d H:i:s'),
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $response = $this->postJson('/api/v1/bookings', $payload);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
            ]);

        // Đảm bảo dat_phong không còn lưu bản ghi nào bị dở dang (Rollback hoàn toàn)
        $this->assertDatabaseCount('dat_phong', 0);
    }

    // ==========================================
    // TEST CASE 8: ROOMS & CUSTOMERS LIST FOR BOOKING
    // ==========================================

    public function test_authenticated_user_can_fetch_rooms_list(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $response = $this->getJson('/api/v1/rooms');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
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
    }

    public function test_receptionist_can_fetch_customers_list(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'hoTen',
                        'cccd',
                        'soDienThoai',
                        'email',
                    ],
                ],
            ]);
    }
}
