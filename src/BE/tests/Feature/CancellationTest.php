<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Hoa_Don;
use App\Models\Hoan_Tien;
use App\Models\Khach_Hang;
use App\Models\Loai_Phong;
use App\Models\Luu_Tru;
use App\Models\Nhan_Vien;
use App\Models\Phong;
use App\Models\Tai_Khoan;
use App\Models\Thanh_Toan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CancellationTest extends TestCase
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
    protected Phong $roomP101;
    protected Phong $roomP201;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        // 1. Tạo loại phòng và phòng mẫu
        $this->roomTypeSingle = Loai_Phong::create([
            'tenLoai'      => 'Deluxe Single',
            'moTa'         => 'Phòng đơn tiêu chuẩn',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 1,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomTypeDouble = Loai_Phong::create([
            'tenLoai'      => 'Deluxe Double',
            'moTa'         => 'Phòng đôi tiêu chuẩn',
            'giaTieuChuan' => 800000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

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

        // 2. Tạo tài khoản Khách hàng 1
        $this->customerUser1 = Tai_Khoan::create([
            'tenDangNhap'   => 'khachhang1',
            'password'      => Hash::make('Password@123'),
            'hash_password' => Hash::make('Password@123'),
            'trangThai'     => 'Hoạt động',
        ]);
        $this->customerProfile1 = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser1->id,
            'hoTen'       => 'Nguyễn Văn Một',
            'cccd'        => '001099000001',
            'email'       => 'khach1@example.com',
            'soDienThoai' => '0901000001',
            'diaChi'      => '123 Đường A, Hà Nội',
        ]);

        // 3. Tạo tài khoản Khách hàng 2
        $this->customerUser2 = Tai_Khoan::create([
            'tenDangNhap'   => 'khachhang2',
            'password'      => Hash::make('Password@123'),
            'hash_password' => Hash::make('Password@123'),
            'trangThai'     => 'Hoạt động',
        ]);
        $this->customerProfile2 = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser2->id,
            'hoTen'       => 'Trần Thị Hai',
            'cccd'        => '001099000002',
            'email'       => 'khach2@example.com',
            'soDienThoai' => '0901000002',
            'diaChi'      => '456 Đường B, TP.HCM',
        ]);

        // 4. Tạo tài khoản Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap'   => 'letan01',
            'password'      => Hash::make('Password@123'),
            'hash_password' => Hash::make('Password@123'),
            'trangThai'     => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lễ Tân Tuyết Lan',
            'cccd'        => '001099000003',
            'ngaySinh'    => '1995-05-15',
            'email'       => 'letan@example.com',
            'soDienThoai' => '0901000003',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm',
        ]);

        // 5. Tạo tài khoản Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap'   => 'buongphong01',
            'password'      => Hash::make('Password@123'),
            'hash_password' => Hash::make('Password@123'),
            'trangThai'     => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Nhân Viên Dọn Dẹp',
            'cccd'        => '001099000004',
            'ngaySinh'    => '1996-08-20',
            'email'       => 'housekeeping@example.com',
            'soDienThoai' => '0901000004',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm',
        ]);
    }

    /**
     * Helper tạo đơn đặt phòng mẫu.
     */
    protected function createBooking(
        Khach_Hang $khachHang,
        array $rooms,
        string $checkIn,
        string $checkOut,
        int $tienCoc = 300000,
        string $trangThai = 'Đã cọc'
    ): Dat_Phong {
        $datPhong = Dat_Phong::create([
            'maKhachHang'   => $khachHang->id,
            'maNhanVien'    => null,
            'ngayDat'       => now(),
            'ngayNhanPhong' => $checkIn,
            'ngayTraPhong'  => $checkOut,
            'soLuongKhach'  => count($rooms),
            'tienCoc'       => $tienCoc,
            'trangThai'     => $trangThai,
        ]);

        foreach ($rooms as $r) {
            Chi_Tiet_Dat_Phong::create([
                'maDatPhong'     => $datPhong->id,
                'maPhong'        => $r['phong']->id,
                'giaPhongApDung' => $r['gia'] ?? 500000,
                'soLuongKhach'   => $r['soKhach'] ?? 1,
            ]);
        }

        return $datPhong;
    }

    // ==========================================
    // TEST CASE 1: TRA CỨU DANH SÁCH VÀ CHI TIẾT ĐƠN (UC15 / FR15)
    // ==========================================

    public function test_customer_can_view_only_own_bookings_list(): void
    {
        // Khách 1 có 2 đơn, Khách 2 có 1 đơn
        $b1 = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
        );

        $b2 = $this->createBooking(
            $this->customerProfile2,
            [['phong' => $this->roomP201, 'gia' => 800000, 'soKhach' => 2]],
            Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s')
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->getJson('/api/v1/bookings');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy danh sách đặt phòng thành công.',
            ]);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($b1->id, $data[0]['id']);
        $this->assertEquals('Đã cọc', $data[0]['trangThai']);
        $this->assertEquals(2, $data[0]['soDem']);
        $this->assertEquals(1000000, $data[0]['tongChiPhi']); // 500k * 2 đêm
        $this->assertEquals($this->customerProfile1->hoTen, $data[0]['khachHang']['hoTen']);
    }

    public function test_receptionist_can_view_all_bookings_and_filter(): void
    {
        $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
        );

        $this->createBooking(
            $this->customerProfile2,
            [['phong' => $this->roomP201, 'gia' => 800000, 'soKhach' => 2]],
            Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s')
        );

        Sanctum::actingAs($this->receptionistUser);

        // Xem tất cả
        $resAll = $this->getJson('/api/v1/bookings');
        $resAll->assertStatus(200);
        $this->assertCount(2, $resAll->json('data'));

        // Lọc theo maKhachHang
        $resFiltered = $this->getJson('/api/v1/bookings?maKhachHang=' . $this->customerProfile1->id);
        $resFiltered->assertStatus(200);
        $this->assertCount(1, $resFiltered->json('data'));
        $this->assertEquals($this->customerProfile1->id, $resFiltered->json('data.0.khachHang.id'));
    }

    public function test_customer_can_view_own_booking_detail_with_cancellation_policy(): void
    {
        $checkIn = Carbon::now()->addDays(4)->format('Y-m-d H:i:s');
        $checkOut = Carbon::now()->addDays(6)->format('Y-m-d H:i:s');

        $b = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            $checkIn,
            $checkOut,
            300000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->getJson("/api/v1/bookings/{$b->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'id'          => $b->id,
                    'maDatPhong'  => $b->id,
                    'trangThai'   => 'Đã cọc',
                    'soDem'       => 2,
                    'tongChiPhi'  => 1000000,
                    'tienCoc'     => 300000,
                    'chinhSachHuy' => [
                        'coTheHuy'            => true,
                        'trongThoiHanHoanCoc' => true,
                        'soTienHoanDuKien'    => 300000,
                    ],
                ],
            ]);
    }

    public function test_customer_cannot_view_other_customer_booking_detail_returns_403(): void
    {
        $otherBooking = $this->createBooking(
            $this->customerProfile2,
            [['phong' => $this->roomP201, 'gia' => 800000, 'soKhach' => 2]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->getJson("/api/v1/bookings/{$otherBooking->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
                'message' => 'Bạn không có quyền truy cập đơn đặt phòng này.',
            ]);
    }

    public function test_booking_detail_returns_404_for_non_existent_id(): void
    {
        Sanctum::actingAs($this->customerUser1);

        $response = $this->getJson('/api/v1/bookings/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'status'  => 404,
                'message' => 'Không tìm thấy đơn đặt phòng.',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_bookings(): void
    {
        $resList = $this->getJson('/api/v1/bookings');
        $resList->assertStatus(401);

        $resDetail = $this->getJson('/api/v1/bookings/1');
        $resDetail->assertStatus(401);
    }

    public function test_unauthorized_role_housekeeper_cannot_access_bookings(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        $resList = $this->getJson('/api/v1/bookings');
        $resList->assertStatus(403);

        $resDetail = $this->getJson('/api/v1/bookings/1');
        $resDetail->assertStatus(403);
    }

    // ==========================================
    // TEST CASE 2: GỬI YÊU CẦU HỦY TRONG THỜI HẠN HOÀN CỌC (UC15 / BR05.2, BR05.3, BR05.6)
    // ==========================================

    public function test_cancellation_request_within_refund_deadline_success(): void
    {
        // Ngày nhận phòng sau 5 ngày (vượt xa 24h quy định hoàn cọc)
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->customerUser1);

        $payload = [
            'lyDoHuy' => 'Có lịch công tác đột xuất',
        ];

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Gửi yêu cầu hủy đặt phòng thành công. Yêu cầu đang được chờ xử lý.',
                'data'    => [
                    'maDatPhong'          => $booking->id,
                    'trangThaiDatPhong'   => 'Đã cọc', // Không đổi sai trạng thái đơn khi chưa được duyệt
                    'trangThaiYeuCau'     => 'Chờ xử lý',
                    'lyDoHuy'             => 'Có lịch công tác đột xuất',
                    'trongThoiHanHoanCoc' => true,
                    'soTienHoanDuKien'    => 500000,
                ],
            ]);

        // Đảm bảo trạng thái trong database vẫn giữ nguyên 'Đã cọc'
        $this->assertDatabaseHas('dat_phong', [
            'id'        => $booking->id,
            'trangThai' => 'Đã cọc',
        ]);
    }

    // ==========================================
    // TEST CASE 3: GỬI YÊU CẦU HỦY NGOÀI THỜI HẠN HOÀN CỌC (UC15 / EXCEPTION FLOW 6a)
    // ==========================================

    public function test_cancellation_request_outside_deadline_fails_without_confirmation_returns_422(): void
    {
        // Ngày nhận phòng trong 10 tiếng tới (quá thời hạn 24h được hoàn cọc)
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addHours(10)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->customerUser1);

        // Khách gửi hủy nhưng không xác nhận không hoàn cọc
        $payload = [
            'lyDoHuy'             => 'Đổi lịch',
            'xacNhanKhongHoanCoc' => false,
        ];

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
                'message' => 'Dữ liệu gửi lên không hợp lệ.',
            ])
            ->assertJsonValidationErrors(['xacNhanKhongHoanCoc']);

        // Đảm bảo không có yêu cầu hủy nào được tạo trong Cache
        $this->assertNull(Cache::get("cancellation_request_{$booking->id}"));
    }

    public function test_cancellation_request_outside_deadline_succeeds_with_confirmation(): void
    {
        // Ngày nhận phòng trong 12 tiếng tới
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addHours(12)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->customerUser1);

        // Khách xác nhận chấp nhận không hoàn tiền cọc
        $payload = [
            'lyDoHuy'             => 'Việc gia đình khẩn cấp',
            'xacNhanKhongHoanCoc' => true,
        ];

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'maDatPhong'          => $booking->id,
                    'trangThaiYeuCau'     => 'Chờ xử lý',
                    'xacNhanKhongHoanCoc' => true,
                    'trongThoiHanHoanCoc' => false,
                    'soTienHoanDuKien'    => 0, // Không hoàn cọc
                ],
            ]);
    }

    // ==========================================
    // TEST CASE 4: XỬ LÝ LỖI TRẠNG THÁI KHÔNG HỢP LỆ VÀ TRÙNG LẶP (409 CONFLICT)
    // ==========================================

    public function test_cancellation_request_fails_if_request_already_pending_duplicate_returns_409(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(6)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->customerUser1);

        // Gửi lần 1: thành công
        $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", [
            'lyDoHuy' => 'Hủy lần 1',
        ])->assertStatus(200);

        // Gửi lần 2 khi lần 1 đang chờ xử lý: trả 409 Conflict
        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", [
            'lyDoHuy' => 'Hủy lần 2 trùng lặp',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
                'message' => 'Đơn đặt phòng đã gửi yêu cầu hủy và đang chờ xử lý.',
            ]);
    }

    public function test_cancellation_request_fails_if_booking_already_cancelled_returns_409(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(6)->format('Y-m-d H:i:s'),
            500000,
            'Đã hủy'
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", [
            'lyDoHuy' => 'Thử hủy đơn đã hủy',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
                'message' => 'Đơn đặt phòng đã được hủy trước đó.',
            ]);
    }

    public function test_cancellation_request_fails_if_booking_is_active_or_completed_returns_409(): void
    {
        $bookingActive = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->subDay()->format('Y-m-d H:i:s'),
            Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            500000,
            'Đang ở'
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->postJson("/api/v1/bookings/{$bookingActive->id}/cancellation-request", [
            'lyDoHuy' => 'Thử hủy đơn đang lưu trú',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
                'message' => 'Không thể hủy đơn đặt phòng đang lưu trú hoặc đã hoàn thành.',
            ]);
    }

    public function test_customer_cannot_request_cancellation_for_other_customer_returns_403(): void
    {
        $otherBooking = $this->createBooking(
            $this->customerProfile2,
            [['phong' => $this->roomP201, 'gia' => 800000, 'soKhach' => 2]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->postJson("/api/v1/bookings/{$otherBooking->id}/cancellation-request", [
            'lyDoHuy' => 'Phá hoại đơn người khác',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
            ]);
    }

    // ==========================================
    // TEST CASE 5: LỄ TÂN / QUẢN LÝ XỬ LÝ DUYỆT HỦY & HOÀN TIỀN (UC15 / BR05.4, BR05.5)
    // ==========================================

    public function test_receptionist_can_approve_cancellation_request_releases_rooms_and_creates_refund(): void
    {
        $checkIn = Carbon::now()->addDays(4)->format('Y-m-d H:i:s');
        $checkOut = Carbon::now()->addDays(6)->format('Y-m-d H:i:s');

        // 1. Tạo đơn đặt phòng có cọc 500k
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            $checkIn,
            $checkOut,
            500000,
            'Đã cọc'
        );

        // Tạo hóa đơn và thanh toán cọc liên kết
        $hoaDon = Hoa_Don::create([
            'maLuuTru'  => $booking->id,
            'ngayLap'   => now(),
            'tongTien'  => 1000000,
            'trangThai' => 'Chờ thanh toán',
        ]);

        $thanhToan = Thanh_Toan::create([
            'maHoaDon'   => $hoaDon->id,
            'soTien'     => 500000,
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'BANK_TRANS_DEP_001',
            'thoiGian'   => now(),
            'trangThai'  => 'Thành công',
        ]);

        // 2. Khách gửi yêu cầu hủy trong hạn
        Sanctum::actingAs($this->customerUser1);
        $this->postJson("/api/v1/bookings/{$booking->id}/cancellation-request", [
            'lyDoHuy' => 'Cần hoàn tiền gấp',
        ])->assertStatus(200);

        // 3. Lễ tân đăng nhập và duyệt yêu cầu
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancellation-status", [
            'trangThai' => 'Đã duyệt',
            'ghiChu'    => 'Đồng ý duyệt hủy và hoàn tiền cọc cho khách',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Duyệt yêu cầu hủy đặt phòng thành công. Phòng đã được giải phóng.',
                'data'    => [
                    'maDatPhong'        => $booking->id,
                    'trangThaiDatPhong' => 'Đã hủy',
                    'trangThaiYeuCau'   => 'Đã duyệt',
                    'ghiChuXuLy'        => 'Đồng ý duyệt hủy và hoàn tiền cọc cho khách',
                    'hoanTien'          => [
                        'maThanhToan' => $thanhToan->id,
                        'soTien'      => 500000,
                        'trangThai'   => 'Đã hoàn tiền',
                    ],
                ],
            ]);

        // 4. Kiểm tra Database trạng thái đơn và bản ghi hoàn tiền
        $this->assertDatabaseHas('dat_phong', [
            'id'        => $booking->id,
            'trangThai' => 'Đã hủy',
        ]);

        $this->assertDatabaseHas('hoan_tien', [
            'maThanhToan' => $thanhToan->id,
            'soTien'      => 500000,
            'trangThai'   => 'Đã hoàn tiền',
        ]);

        // 5. Kiểm tra phòng P101 đã được giải phóng: Khách 2 có thể đặt phòng P101 trong cùng khung giờ!
        Sanctum::actingAs($this->customerUser2);
        $newBookingPayload = [
            'ngayNhanPhong' => $checkIn,
            'ngayTraPhong'  => $checkOut,
            'soLuongKhach'  => 1,
            'danhSachPhong' => [
                ['maPhong' => $this->roomP101->id, 'soLuongKhach' => 1],
            ],
        ];

        $resRebook = $this->postJson('/api/v1/bookings', $newBookingPayload);
        $resRebook->assertStatus(201); // Thành công, không bị 409 Conflict
    }

    public function test_duplicate_refund_is_prevented_when_approving_cancellation(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(6)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        $hoaDon = Hoa_Don::create([
            'maLuuTru'  => $booking->id,
            'ngayLap'   => now(),
            'tongTien'  => 1000000,
            'trangThai' => 'Chờ thanh toán',
        ]);

        $thanhToan = Thanh_Toan::create([
            'maHoaDon'   => $hoaDon->id,
            'soTien'     => 500000,
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'BANK_TRANS_DEP_DUP',
            'thoiGian'   => now(),
            'trangThai'  => 'Thành công',
        ]);

        // Đã có sẵn 1 bản ghi hoàn tiền từ trước
        Hoan_Tien::create([
            'maThanhToan' => $thanhToan->id,
            'soTien'      => 500000,
            'lyDo'        => 'Đã hoàn trước đó',
            'thoiGian'    => now(),
            'trangThai'   => 'Đã hoàn tiền',
        ]);

        Sanctum::actingAs($this->receptionistUser);

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancellation-status", [
            'trangThai' => 'Đã duyệt',
            'ghiChu'    => 'Duyệt lần 2',
        ]);

        $response->assertStatus(200);

        // Đảm bảo chỉ có duy nhất 1 bản ghi hoan_tien cho maThanhToan này
        $this->assertEquals(1, Hoan_Tien::where('maThanhToan', $thanhToan->id)->count());
    }

    public function test_receptionist_can_reject_cancellation_request_preserves_booking_status(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
            300000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->receptionistUser);

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancellation-status", [
            'trangThai' => 'Từ chối',
            'ghiChu'    => 'Lý do hủy không chính đáng hoặc đã quá sát giờ',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Đã từ chối yêu cầu hủy đặt phòng. Đơn đặt phòng giữ nguyên trạng thái.',
                'data'    => [
                    'maDatPhong'        => $booking->id,
                    'trangThaiDatPhong' => 'Đã cọc', // Không đổi thành Đã hủy
                    'trangThaiYeuCau'   => 'Từ chối',
                ],
            ]);

        $this->assertDatabaseHas('dat_phong', [
            'id'        => $booking->id,
            'trangThai' => 'Đã cọc',
        ]);
    }

    public function test_customer_cannot_call_cancellation_status_endpoint_returns_403(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(5)->format('Y-m-d H:i:s')
        );

        Sanctum::actingAs($this->customerUser1);

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancellation-status", [
            'trangThai' => 'Đã duyệt',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
                'message' => 'Chỉ Lễ tân hoặc Quản lý mới có quyền xử lý yêu cầu hủy đặt phòng.',
            ]);
    }

    // ==========================================
    // TEST CASE 6: TRANSACTION ROLLBACK KHI GẶP LỖI HỆ THỐNG (EXCEPTION FLOW 8)
    // ==========================================

    public function test_transaction_rollback_when_approval_fails_database_not_corrupted(): void
    {
        $booking = $this->createBooking(
            $this->customerProfile1,
            [['phong' => $this->roomP101, 'gia' => 500000, 'soKhach' => 1]],
            Carbon::now()->addDays(4)->format('Y-m-d H:i:s'),
            Carbon::now()->addDays(6)->format('Y-m-d H:i:s'),
            500000,
            'Đã cọc'
        );

        Sanctum::actingAs($this->receptionistUser);

        // Giả lập lỗi DB phát sinh khi lưu Model Dat_Phong
        Dat_Phong::saving(function () {
            throw new \Exception('Lỗi database mô phỏng trong transaction');
        });

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/cancellation-status", [
            'trangThai' => 'Đã duyệt',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
                'message' => 'Xử lý yêu cầu hủy đặt phòng không thành công do lỗi hệ thống. Vui lòng thử lại sau.',
            ]);

        // Đảm bảo dữ liệu không bị thay đổi sai lệch (trạng thái vẫn là 'Đã cọc')
        $this->assertEquals('Đã cọc', $booking->fresh()->trangThai);
    }
}
