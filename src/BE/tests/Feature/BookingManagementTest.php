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

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tai_Khoan $managerUser;
    protected Nhan_Vien $managerProfile;

    protected Tai_Khoan $receptionistUser;
    protected Nhan_Vien $receptionistProfile;

    protected Tai_Khoan $customerUser;
    protected Khach_Hang $customerProfile;

    protected Tai_Khoan $housekeeperUser;
    protected Nhan_Vien $housekeeperProfile;

    protected Loai_Phong $roomTypeStandard;
    protected Loai_Phong $roomTypeVip;
    protected Loai_Phong $roomTypeInactive;

    protected Phong $roomP101;
    protected Phong $roomP102;
    protected Phong $roomP201;
    protected Phong $roomMaintenance;
    protected Phong $roomInactiveType;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo các loại phòng
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
            'giaTieuChuan' => 900000,
            'sucChuaToiDa' => 4,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomTypeInactive = Loai_Phong::create([
            'tenLoai'      => 'Ngừng hoạt động',
            'moTa'         => 'Loại phòng đã ngừng phục vụ',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Ngừng kinh doanh',
        ]);

        // 2. Tạo các phòng vật lý
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

        $this->roomMaintenance = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P301',
            'tang'        => 3,
            'trangThai'   => 'Đang bảo trì',
        ]);

        $this->roomInactiveType = Phong::create([
            'maLoaiPhong' => $this->roomTypeInactive->id,
            'tenPhong'    => 'P401',
            'tang'        => 4,
            'trangThai'   => 'Sẵn sàng',
        ]);

        // 3. Tạo Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'quanly',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Nguyễn Quản Lý',
            'cccd'        => '001090000001',
            'ngaySinh'    => '1985-01-01',
            'soDienThoai' => '0905111222',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm',
        ]);

        // 4. Tạo Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'letan',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Trần Lễ Tân',
            'cccd'        => '001090000002',
            'ngaySinh'    => '1995-02-02',
            'soDienThoai' => '0905333444',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm',
        ]);

        // 5. Tạo Khách hàng
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'khachhang',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Lê Khách Hàng',
            'cccd'        => '001200000003',
            'email'       => 'khach@example.com',
            'soDienThoai' => '0905555666',
            'diaChi'      => 'Đà Nẵng',
        ]);

        // 6. Tạo Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'buongphong',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'cccd'        => '001090000004',
            'ngaySinh'    => '1992-05-05',
            'soDienThoai' => '0905777888',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm',
        ]);
    }

    /**
     * Tạo helper đơn đặt phòng mẫu.
     */
    protected function createSampleBooking(
        string $trangThai = 'Chờ xác nhận',
        string $checkin = null,
        string $checkout = null,
        array $rooms = null
    ): Dat_Phong {
        $checkin = $checkin ?: Carbon::now()->addDays(2)->format('Y-m-d H:i:s');
        $checkout = $checkout ?: Carbon::now()->addDays(4)->format('Y-m-d H:i:s');

        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => now(),
            'ngayNhanPhong' => $checkin,
            'ngayTraPhong'  => $checkout,
            'soLuongKhach'  => 2,
            'tienCoc'       => 200000,
            'trangThai'     => $trangThai,
        ]);

        $rooms = $rooms ?: [$this->roomP101];
        foreach ($rooms as $r) {
            Chi_Tiet_Dat_Phong::create([
                'maDatPhong'     => $booking->id,
                'maPhong'        => $r->id,
                'giaPhongApDung' => $r->loaiPhong->giaTieuChuan,
                'soLuongKhach'   => 2,
            ]);
        }

        return $booking;
    }

    /**
     * 1. Lễ tân / Quản lý truy cập được danh sách đơn đặt phòng kèm phân trang / lọc.
     */
    public function test_receptionist_can_fetch_and_filter_bookings(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $this->createSampleBooking('Chờ xác nhận');
        $this->createSampleBooking('Đã xác nhận');

        // Tìm kiếm theo từ khóa tên khách
        $response = $this->getJson('/api/v1/bookings?keyword=Lê Khách Hàng');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
            ])
            ->assertJsonCount(2, 'data');

        // Lọc theo trạng thái
        $responseStatus = $this->getJson('/api/v1/bookings?trangThai=Đã xác nhận');
        $responseStatus->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * 2. Vai trò không hợp lệ (Khách hàng, Buồng phòng) bị từ chối truy cập update đơn (403).
     */
    public function test_unauthorized_roles_cannot_update_booking(): void
    {
        $booking = $this->createSampleBooking('Chờ xác nhận');

        // Khách hàng gọi API update
        Sanctum::actingAs($this->customerUser);
        $resCustomer = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);
        $resCustomer->assertStatus(403);

        // Buồng phòng gọi API update
        Sanctum::actingAs($this->housekeeperUser);
        $resHousekeeper = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);
        $resHousekeeper->assertStatus(403);
    }

    /**
     * 3. Lễ tân cập nhật trạng thái hợp lệ (Chờ xác nhận -> Đã xác nhận).
     */
    public function test_receptionist_can_update_status_to_confirmed(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'maDatPhong' => $booking->id,
                    'trangThai'  => 'Đã xác nhận',
                ],
            ]);

        $this->assertDatabaseHas('dat_phong', [
            'id'        => $booking->id,
            'trangThai' => 'Đã xác nhận',
        ]);

        // Kiểm tra phòng được đồng bộ sang 'Đã đặt'
        $this->assertEquals('Đã đặt', $this->roomP101->fresh()->trangThai);
    }

    /**
     * 4. Cập nhật nhanh bằng route /status.
     */
    public function test_receptionist_can_update_via_status_endpoint(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}/status", [
            'trangThai' => 'Đã xác nhận',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'trangThai' => 'Đã xác nhận',
                ],
            ]);
    }

    /**
     * 5. Từ chối chuyển đổi trạng thái không hợp lệ (Chờ xác nhận -> Đã trả) nhận 422.
     */
    public function test_invalid_status_transition_returns_422(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã trả',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);

        $this->assertEquals('Chờ xác nhận', $booking->fresh()->trangThai);
    }

    /**
     * 6. Cập nhật ngày lưu trú thành công khi không xung đột.
     */
    public function test_update_stay_dates_successfully(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $newCheckin = Carbon::now()->addDays(5)->format('Y-m-d H:i:s');
        $newCheckout = Carbon::now()->addDays(8)->format('Y-m-d H:i:s');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'ngayNhanPhong' => $newCheckin,
            'ngayTraPhong'  => $newCheckout,
        ]);

        $response->assertStatus(200);

        $fresh = $booking->fresh();
        $this->assertEquals(Carbon::parse($newCheckin)->format('Y-m-d H:i:s'), Carbon::parse($fresh->ngayNhanPhong)->format('Y-m-d H:i:s'));
        $this->assertEquals(Carbon::parse($newCheckout)->format('Y-m-d H:i:s'), Carbon::parse($fresh->ngayTraPhong)->format('Y-m-d H:i:s'));
    }

    /**
     * 7. Ngày trả trước hoặc bằng ngày nhận nhận 422; không lưu thay đổi.
     */
    public function test_checkout_before_or_equal_to_checkin_returns_422(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'ngayNhanPhong' => Carbon::now()->addDays(5)->format('Y-m-d H:i:s'),
            'ngayTraPhong'  => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(422);
    }

    /**
     * 8. Đổi phòng thành công khi phòng mới trống; bảo toàn không xóa cứng chi tiết cũ.
     */
    public function test_swap_room_successfully_preserves_detail_and_updates_price(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận', null, null, [$this->roomP101]);

        // Đổi từ P101 sang P201 (VIP Suite giá 900.000)
        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'danhSachPhong' => [
                [
                    'maPhong'      => $this->roomP201->id,
                    'soLuongKhach' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        // Chi tiết đặt phòng được cập nhật maPhong sang P201
        $detail = Chi_Tiet_Dat_Phong::where('maDatPhong', $booking->id)->first();
        $this->assertEquals($this->roomP201->id, $detail->maPhong);
        $this->assertEquals(900000, $detail->giaPhongApDung);
    }

    /**
     * 9. Đổi sang phòng đã có đơn khác trùng lịch nhận 409 Conflict.
     */
    public function test_swap_room_with_conflict_returns_409(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $checkin = Carbon::now()->addDays(3)->format('Y-m-d H:i:s');
        $checkout = Carbon::now()->addDays(6)->format('Y-m-d H:i:s');

        // Đơn 1 đã đặt P201 trong khoảng này
        $this->createSampleBooking('Đã xác nhận', $checkin, $checkout, [$this->roomP201]);

        // Đơn 2 đặt P101, cố gắng đổi sang P201
        $booking2 = $this->createSampleBooking('Chờ xác nhận', $checkin, $checkout, [$this->roomP101]);

        $response = $this->putJson("/api/v1/bookings/{$booking2->id}", [
            'danhSachPhong' => [
                [
                    'maPhong'      => $this->roomP201->id,
                    'soLuongKhach' => 2,
                ],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        // Đơn 2 vẫn giữ nguyên P101
        $this->assertEquals($this->roomP101->id, $booking2->fresh()->chiTietDatPhong->first()->maPhong);
    }

    /**
     * 10. Đơn đã ở trạng thái Đã hủy hoặc Đã trả / Đã hoàn thành không thể chỉnh sửa, nhận 409.
     */
    public function test_terminal_status_booking_cannot_be_updated_returns_409(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $cancelledBooking = $this->createSampleBooking('Đã hủy');
        $completedBooking = $this->createSampleBooking('Đã hoàn thành');

        // Thử cập nhật đơn đã hủy
        $resCancelled = $this->putJson("/api/v1/bookings/{$cancelledBooking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);
        $resCancelled->assertStatus(409);

        // Thử cập nhật đơn đã hoàn thành
        $resCompleted = $this->putJson("/api/v1/bookings/{$completedBooking->id}", [
            'ngayTraPhong' => Carbon::now()->addDays(10)->format('Y-m-d H:i:s'),
        ]);
        $resCompleted->assertStatus(409);
    }

    /**
     * 11. Đổi sang phòng đang bảo trì hoặc loại phòng ngừng kinh doanh nhận 422.
     */
    public function test_swap_to_maintenance_or_inactive_room_returns_422(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        // Phòng bảo trì
        $resMaint = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'danhSachPhong' => [
                ['maPhong' => $this->roomMaintenance->id, 'soLuongKhach' => 1],
            ],
        ]);
        $resMaint->assertStatus(422);

        // Loại phòng ngừng kinh doanh
        $resInactive = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'danhSachPhong' => [
                ['maPhong' => $this->roomInactiveType->id, 'soLuongKhach' => 1],
            ],
        ]);
        $resInactive->assertStatus(422);
    }

    /**
     * 12. Ghi nhận mã nhân viên thực hiện (maNhanVien) và updated_at khi cập nhật.
     */
    public function test_update_records_staff_id_and_updated_at(): void
    {
        Sanctum::actingAs($this->managerUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);

        $response->assertStatus(200);

        $fresh = $booking->fresh();
        $this->assertEquals($this->managerProfile->id, $fresh->maNhanVien);
        $this->assertNotNull($fresh->updated_at);
    }

    /**
     * 13. Hủy đơn qua update trạng thái ('Đã hủy') giải phóng trạng thái phòng về 'Sẵn sàng'.
     */
    public function test_updating_status_to_cancelled_releases_room(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Đã xác nhận', null, null, [$this->roomP101]);

        // Giả sử phòng đang ở trạng thái 'Đã đặt'
        $this->roomP101->update(['trangThai' => 'Đã đặt']);

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã hủy',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Đã hủy', $booking->fresh()->trangThai);
        $this->assertEquals('Sẵn sàng', $this->roomP101->fresh()->trangThai);
    }

    /**
     * 14. Transaction rollback khi phát sinh lỗi hệ thống; dữ liệu không bị cập nhật dở dang.
     */
    public function test_transaction_rollbacks_on_database_error(): void
    {
        Sanctum::actingAs($this->receptionistUser);
        $booking = $this->createSampleBooking('Chờ xác nhận');

        // Mô phỏng ngoại lệ DB khi model Dat_Phong lưu
        Dat_Phong::saving(function ($model) use ($booking) {
            if ($model->id === $booking->id && $model->trangThai === 'Đã xác nhận') {
                throw new \Exception('Lỗi database mô phỏng trong transaction cập nhật');
            }
        });

        $response = $this->putJson("/api/v1/bookings/{$booking->id}", [
            'trangThai' => 'Đã xác nhận',
        ]);

        $response->assertStatus(500);

        // Đảm bảo dữ liệu không bị thay đổi dở dang
        $this->assertEquals('Chờ xác nhận', $booking->fresh()->trangThai);
    }
}
