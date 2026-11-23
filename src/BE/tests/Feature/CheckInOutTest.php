<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Dat_Phong;
use App\Models\Hoa_Don;
use App\Models\Khach_Hang;
use App\Models\Loai_Phong;
use App\Models\Luu_Tru;
use App\Models\Nhan_Vien;
use App\Models\Phieu_Nhan_Tra_Phong;
use App\Models\Phong;
use App\Models\Tai_Khoan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckInOutTest extends TestCase
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

    protected Loai_Phong $roomType;
    protected Phong $roomP101;
    protected Phong $roomP102;
    protected Phong $roomBusy;

    protected Dat_Phong $confirmedBooking;
    protected Chi_Tiet_Dat_Phong $confirmedBookingDetail;

    protected Dat_Phong $activeStayBooking;
    protected Chi_Tiet_Dat_Phong $activeStayBookingDetail;
    protected Luu_Tru $activeStay;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo loại phòng & các phòng mẫu
        $this->roomType = Loai_Phong::create([
            'tenLoai'      => 'Executive Suite',
            'moTa'         => 'Phòng hạng sang',
            'giaTieuChuan' => 1000000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomP101 = Phong::create([
            'maLoaiPhong' => $this->roomType->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomP102 = Phong::create([
            'maLoaiPhong' => $this->roomType->id,
            'tenPhong'    => 'P102',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        $this->roomBusy = Phong::create([
            'maLoaiPhong' => $this->roomType->id,
            'tenPhong'    => 'P103',
            'tang'        => 1,
            'trangThai'   => 'Cần dọn dẹp',
        ]);

        // 2. Tài khoản Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_uc09',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Trần Quản Lý',
            'ngaySinh'    => '1985-05-15',
            'cccd'        => '079085000091',
            'soDienThoai' => '0901000091',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 3. Tài khoản Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'reception_uc09',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lê Lễ Tân',
            'ngaySinh'    => '1995-08-20',
            'cccd'        => '079095000092',
            'soDienThoai' => '0902000092',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 4. Tài khoản Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_uc09',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'ngaySinh'    => '1992-03-10',
            'cccd'        => '079092000093',
            'soDienThoai' => '0903000093',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 5. Khách hàng chính
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_uc09_main',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Khách',
            'cccd'        => '079099000094',
            'email'       => 'khach_uc09@example.com',
            'soDienThoai' => '0904000094',
            'diaChi'      => '123 Đường Nhận Phòng, Q1, TP.HCM',
        ]);

        // 6. Khách hàng khác
        $this->otherCustomerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_uc09_other',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->otherCustomerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->otherCustomerUser->id,
            'hoTen'       => 'Trần Khách Khác',
            'cccd'        => '079099000095',
            'email'       => 'other_uc09@example.com',
            'soDienThoai' => '0905000095',
            'diaChi'      => '456 Đường Khác, Q3, TP.HCM',
        ]);

        // 7. Đơn đặt phòng 1: Đã xác nhận (chưa check-in)
        $this->confirmedBooking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => Carbon::now()->subDays(1),
            'ngayNhanPhong' => Carbon::today()->setTime(14, 0, 0),
            'ngayTraPhong'  => Carbon::today()->addDays(2)->setTime(12, 0, 0),
            'soLuongKhach'  => 2,
            'tienCoc'       => 500000,
            'trangThai'     => 'Đã xác nhận',
        ]);
        $this->confirmedBookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $this->confirmedBooking->id,
            'maPhong'        => $this->roomP101->id,
            'giaPhongApDung' => 1000000,
            'soLuongKhach'   => 2,
        ]);

        // 8. Đơn đặt phòng 2: Đang ở (đã check-in, chuẩn bị check-out)
        $this->activeStayBooking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => Carbon::now()->subDays(3),
            'ngayNhanPhong' => Carbon::now()->subDays(2)->setTime(14, 0, 0),
            'ngayTraPhong'  => Carbon::today()->setTime(12, 0, 0),
            'soLuongKhach'  => 2,
            'tienCoc'       => 500000,
            'trangThai'     => 'Đang ở',
        ]);
        $this->activeStayBookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $this->activeStayBooking->id,
            'maPhong'        => $this->roomP102->id,
            'giaPhongApDung' => 1000000,
            'soLuongKhach'   => 2,
        ]);
        $this->roomP102->update(['trangThai' => 'Đang ở']);

        $this->activeStay = Luu_Tru::create([
            'maChiTiet'    => $this->activeStayBookingDetail->id,
            'thoiGianNhan' => Carbon::now()->subDays(2)->setTime(14, 0, 0),
            'thoiGianTra'  => Carbon::today()->setTime(12, 0, 0),
            'trangThai'    => 'Đang lưu trú',
        ]);

        Phieu_Nhan_Tra_Phong::create([
            'maLuuTru'   => $this->activeStay->id,
            'maNhanVien' => $this->receptionistProfile->id,
            'loaiPhieu'  => 'Nhận phòng',
            'thoiGian'   => Carbon::now()->subDays(2)->setTime(14, 0, 0),
            'ghiChu'     => 'Khách nhận phòng đúng hẹn.',
        ]);

        Hoa_Don::create([
            'maLuuTru'  => $this->activeStay->id,
            'ngayLap'   => Carbon::now()->subDays(2),
            'tongTien'  => 2000000,
            'trangThai' => 'Chờ thanh toán',
        ]);
    }

    // ==========================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // ==========================================

    /**
     * Test Lễ tân tra cứu thông tin lưu trú của đơn đặt phòng.
     */
    public function test_receptionist_can_view_stay_info(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson("/api/v1/bookings/{$this->activeStayBooking->id}/stay-info");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy thông tin lưu trú thành công.',
                'data'    => [
                    'maDatPhong' => $this->activeStayBooking->id,
                    'trangThai'  => 'Đang ở',
                    'khachHang'  => [
                        'hoTen' => 'Nguyễn Văn Khách',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'danhSachPhong' => [
                        '*' => [
                            'maChiTiet',
                            'tenPhong',
                            'luuTru' => [
                                'maLuuTru',
                                'thoiGianNhan',
                                'trangThai',
                                'phieu',
                                'hoaDon',
                            ],
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test Check-in hợp lệ cho đơn đã xác nhận với phòng Sẵn sàng.
     */
    public function test_receptionist_can_checkin_valid_booking_with_ready_room(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'ghiChu'       => 'Khách xuất trình CCCD đầy đủ.',
            'thoiGianNhan' => Carbon::now()->setTime(14, 30, 0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Thủ tục nhận phòng (Check-in) thành công.',
                'data'    => [
                    'maDatPhong' => $this->confirmedBooking->id,
                    'trangThai'  => 'Đang ở',
                ],
            ]);

        // 1. Kiểm tra trạng thái đơn đặt phòng
        $this->assertDatabaseHas('dat_phong', [
            'id'        => $this->confirmedBooking->id,
            'trangThai' => 'Đang ở',
        ]);

        // 2. Kiểm tra trạng thái phòng vật lý chuyển sang Đang ở
        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP101->id,
            'trangThai' => 'Đang ở',
        ]);

        // 3. Kiểm tra tạo bản ghi luu_tru
        $this->assertDatabaseHas('luu_tru', [
            'maChiTiet' => $this->confirmedBookingDetail->id,
            'trangThai' => 'Đang lưu trú',
        ]);

        // 4. Kiểm tra tạo phiếu nhận phòng
        $this->assertDatabaseHas('phieu_nhan_tra_phong', [
            'loaiPhieu'  => 'Nhận phòng',
            'maNhanVien' => $this->receptionistProfile->id,
        ]);
    }

    /**
     * Test Check-in tự động khởi tạo hóa đơn và chi tiết tiền phòng.
     */
    public function test_checkin_creates_invoice_and_calculates_room_fee(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in", [
            'thoiGianNhan' => Carbon::now()->setTime(14, 0, 0)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(200);

        // Đơn 2 đêm x 1,000,000 = 2,000,000
        $this->assertDatabaseHas('hoa_don', [
            'tongTien'  => 2000000,
            'trangThai' => 'Chờ thanh toán',
        ]);

        $this->assertDatabaseHas('chi_tiet_hoa_don', [
            'noiDung'   => 'Tiền phòng P101 (2 đêm)',
            'soLuong'   => 2,
            'donGia'    => 1000000,
            'thanhTien' => 2000000,
        ]);
    }

    /**
     * Test Check-out đơn đang ở: phòng chuyển sang Cần dọn dẹp, đơn chuyển sang Đã trả, tạo phiếu trả phòng (BR07.2).
     */
    public function test_receptionist_can_checkout_active_booking(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'ghiChu'      => 'Khách trả phòng đúng giờ, không phát sinh chi phí.',
            'thoiGianTra' => Carbon::today()->setTime(11, 30, 0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-out", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Thủ tục trả phòng (Check-out) thành công.',
                'data'    => [
                    'maDatPhong' => $this->activeStayBooking->id,
                    'trangThai'  => 'Đã trả',
                ],
            ]);

        // 1. Kiểm tra đơn đặt phòng chuyển sang Đã trả
        $this->assertDatabaseHas('dat_phong', [
            'id'        => $this->activeStayBooking->id,
            'trangThai' => 'Đã trả',
        ]);

        // 2. BR07.2: Phòng chuyển sang Cần dọn dẹp để buồng phòng dọn dẹp
        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP102->id,
            'trangThai' => 'Cần dọn dẹp',
        ]);

        // 3. Đợt lưu trú chuyển sang Đã trả
        $this->assertDatabaseHas('luu_tru', [
            'id'        => $this->activeStay->id,
            'trangThai' => 'Đã trả',
        ]);

        // 4. Tạo phiếu trả phòng trong phieu_nhan_tra_phong
        $this->assertDatabaseHas('phieu_nhan_tra_phong', [
            'maLuuTru'  => $this->activeStay->id,
            'loaiPhieu' => 'Trả phòng',
        ]);
    }

    // ==========================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // ==========================================

    /**
     * Test đổi sang phòng trống cùng loại trước khi Check-in.
     */
    public function test_receptionist_can_swap_to_another_ready_room_during_checkin(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Đổi từ P101 sang P102 (đều là Executive Suite sẵn sàng)
        $this->roomP102->update(['trangThai' => 'Sẵn sàng']);

        $payload = [
            'danhSachPhong' => [
                [
                    'maChiTiet' => $this->confirmedBookingDetail->id,
                    'maPhong'   => $this->roomP102->id,
                ],
            ],
            'thoiGianNhan'  => Carbon::now()->setTime(14, 0, 0)->format('Y-m-d H:i:s'),
        ];

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in", $payload);

        $response->assertStatus(200);

        // Kiểm tra chi tiết đơn đã đổi maPhong sang P102
        $this->assertDatabaseHas('chi_tiet_dat_phong', [
            'id'      => $this->confirmedBookingDetail->id,
            'maPhong' => $this->roomP102->id,
        ]);

        // P102 thành Đang ở
        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP102->id,
            'trangThai' => 'Đang ở',
        ]);
    }

    /**
     * Test tự động tính phụ thu Check-in sớm nếu khách nhận phòng trước 14:00 (BR06.1).
     */
    public function test_checkin_calculates_early_checkin_surcharge(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Nhận phòng lúc 07:00 sáng (thuộc khung 05:00 - 09:00: phụ thu 50% = 500,000)
        $earlyTime = Carbon::parse($this->confirmedBooking->ngayNhanPhong)->setTime(7, 0, 0);

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in", [
            'thoiGianNhan' => $earlyTime->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(200);

        // Hóa đơn gồm tiền phòng (2,000,000) + phụ thu sớm (500,000) = 2,500,000
        $this->assertDatabaseHas('hoa_don', [
            'tongTien' => 2500000,
        ]);

        $this->assertDatabaseHas('chi_tiet_hoa_don', [
            'thanhTien' => 500000,
        ]);
    }

    /**
     * Test tự động tính phụ thu Check-out trễ nếu khách trả phòng sau 12:00 (BR07.1).
     */
    public function test_checkout_calculates_late_checkout_surcharge(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Trả phòng lúc 16:30 chiều (khung 15:00 - 18:00: phụ thu 50% = 500,000)
        $lateTime = Carbon::parse($this->activeStayBooking->ngayTraPhong)->setTime(16, 30, 0);

        $response = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-out", [
            'thoiGianTra' => $lateTime->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('chi_tiet_hoa_don', [
            'thanhTien' => 500000,
        ]);
    }

    /**
     * Test khách hàng xem thông tin lưu trú của đơn mình, nhưng bị cấm xem đơn của khách khác.
     */
    public function test_customer_can_view_own_stay_info_but_forbidden_for_other(): void
    {
        // 1. Khách hàng chính xem đơn của mình -> 200 OK
        Sanctum::actingAs($this->customerUser);
        $responseOwn = $this->getJson("/api/v1/bookings/{$this->activeStayBooking->id}/stay-info");
        $responseOwn->assertStatus(200);

        // 2. Khách hàng khác xem đơn của khách hàng chính -> 403 Forbidden
        Sanctum::actingAs($this->otherCustomerUser);
        $responseOther = $this->getJson("/api/v1/bookings/{$this->activeStayBooking->id}/stay-info");
        $responseOther->assertStatus(403);
    }

    // ==========================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // ==========================================

    /**
     * Test Check-in đơn đã ở trạng thái Đang ở bị từ chối 409 Conflict.
     */
    public function test_checkin_fails_when_booking_already_checked_in(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-in");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('đã hoàn tất thủ tục nhận phòng', $response->json('message'));
    }

    /**
     * Test Check-in đơn đã hủy hoặc đã trả bị từ chối 409 Conflict.
     */
    public function test_checkin_fails_when_booking_cancelled_or_completed(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $this->confirmedBooking->update(['trangThai' => 'Đã hủy']);

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);
    }

    /**
     * Test Check-in phòng không sẵn sàng (đang dọn dẹp, bảo trì, hoặc có khách) bị từ chối 409 Conflict.
     */
    public function test_checkin_fails_when_room_not_ready(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Đổi phòng sang Cần dọn dẹp
        $this->roomP101->update(['trangThai' => 'Cần dọn dẹp']);

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('không đủ điều kiện bàn giao', $response->json('message'));
    }

    /**
     * Test Check-out đơn chưa check-in bị từ chối 409 Conflict.
     */
    public function test_checkout_fails_when_booking_not_checked_in(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-out");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('chưa thực hiện Check-in', $response->json('message'));
    }

    /**
     * Test Check-out đơn đã trả phòng bị từ chối 409 Conflict.
     */
    public function test_checkout_fails_when_booking_already_checked_out(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $this->activeStayBooking->update(['trangThai' => 'Đã trả']);

        $response = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-out");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('đã hoàn tất thủ tục trả phòng', $response->json('message'));
    }

    /**
     * Test Check-out thời gian trước thời gian nhận phòng thực tế bị 422 Unprocessable Entity.
     */
    public function test_checkout_fails_when_checkout_time_before_checkin_time(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Thời gian check-in là 2 ngày trước, thời gian checkout truyền là 3 ngày trước
        $invalidTime = Carbon::now()->subDays(3)->format('Y-m-d H:i:s');

        $response = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-out", [
            'thoiGianTra' => $invalidTime,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);

        $this->assertStringContainsString('không được trước thời gian nhận phòng', $response->json('message'));
    }

    /**
     * Test check-in hoặc check-out đơn không tồn tại trả về 404.
     */
    public function test_checkin_or_checkout_non_existent_booking_returns_404(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $responseCheckIn = $this->postJson('/api/v1/bookings/999999/check-in');
        $responseCheckIn->assertStatus(404);

        $responseCheckOut = $this->postJson('/api/v1/bookings/999999/check-out');
        $responseCheckOut->assertStatus(404);
    }

    /**
     * Test không có Bearer token nhận 401 Unauthorized.
     */
    public function test_unauthenticated_cannot_access_checkin_checkout_apis(): void
    {
        $responseStay = $this->getJson("/api/v1/bookings/{$this->confirmedBooking->id}/stay-info");
        $responseStay->assertStatus(401);

        $responseIn = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in");
        $responseIn->assertStatus(401);

        $responseOut = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-out");
        $responseOut->assertStatus(401);
    }

    /**
     * Test vai trò không hợp lệ (Buồng phòng) nhận 403 Forbidden khi check-in hoặc check-out.
     */
    public function test_unauthorized_role_cannot_checkin_or_checkout(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        $responseIn = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in");
        $responseIn->assertStatus(403);

        $responseOut = $this->postJson("/api/v1/bookings/{$this->activeStayBooking->id}/check-out");
        $responseOut->assertStatus(403);
    }

    /**
     * Test khi lỗi DB xảy ra, transaction tự rollback và trả về 500.
     */
    public function test_database_error_triggers_rollback_and_returns_500(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        Luu_Tru::creating(function () {
            throw new \Exception('Simulated database error during checkin.');
        });

        $response = $this->postJson("/api/v1/bookings/{$this->confirmedBooking->id}/check-in");

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
                'message' => 'Có lỗi xảy ra khi làm thủ tục nhận phòng. Vui lòng thử lại.',
            ]);

        // Đảm bảo đơn và phòng không thay đổi
        $this->assertDatabaseHas('dat_phong', [
            'id'        => $this->confirmedBooking->id,
            'trangThai' => 'Đã xác nhận',
        ]);

        $this->assertDatabaseHas('phong', [
            'id'        => $this->roomP101->id,
            'trangThai' => 'Sẵn sàng',
        ]);
    }
}
