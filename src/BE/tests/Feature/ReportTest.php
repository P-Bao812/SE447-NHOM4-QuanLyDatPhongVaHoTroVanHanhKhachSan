<?php

namespace Tests\Feature;

use App\Models\Chi_Tiet_Dat_Phong;
use App\Models\Chi_Tiet_Hoa_Don;
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

class ReportTest extends TestCase
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
    protected Loai_Phong $roomTypeDeluxe;

    protected Phong $room101;
    protected Phong $room102;
    protected Phong $room201;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo các tài khoản theo vai trò
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_report',
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
            'tenDangNhap' => 'receptionist_report',
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
            'tenDangNhap' => 'housekeeper_report',
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
            'tenDangNhap' => 'customer_report',
            'password'    => Hash::make('123456'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Khách Hàng',
            'email'       => 'customer_report@example.com',
            'soDienThoai' => '0944555666',
            'cccd'        => '048098001122',
            'diaChi'      => '123 Đường ABC, Đà Nẵng',
        ]);

        // 2. Tạo Loại phòng & Phòng vật lý
        $this->roomTypeStandard = Loai_Phong::create([
            'tenLoai'      => 'Standard Room',
            'moTa'         => 'Phòng tiêu chuẩn ấm cúng',
            'giaTieuChuan' => 400000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Hoạt động',
        ]);

        $this->roomTypeDeluxe = Loai_Phong::create([
            'tenLoai'      => 'Deluxe Room',
            'moTa'         => 'Phòng cao cấp hướng biển',
            'giaTieuChuan' => 800000,
            'sucChuaToiDa' => 3,
            'trangThai'    => 'Hoạt động',
        ]);

        $this->room101 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P.101',
            'tang'        => 1,
            'trangThai'   => 'Trống',
        ]);

        $this->room102 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P.102',
            'tang'        => 1,
            'trangThai'   => 'Trống',
        ]);

        $this->room201 = Phong::create([
            'maLoaiPhong' => $this->roomTypeDeluxe->id,
            'tenPhong'    => 'P.201',
            'tang'        => 2,
            'trangThai'   => 'Trống',
        ]);
    }

    // ==========================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // ==========================================

    /**
     * Test 1: Quản lý truy cập báo cáo dashboard thành công với đầy đủ số liệu KPI.
     */
    public function test_manager_can_access_dashboard_report_successfully(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Chuẩn bị dữ liệu mẫu: 1 đơn đặt phòng, 1 lưu trú, 1 hóa đơn đã thanh toán
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => '2026-11-15 10:00:00',
            'ngayNhanPhong' => '2026-11-15 14:00:00',
            'ngayTraPhong'  => '2026-11-17 12:00:00',
            'soLuongKhach'  => 2,
            'tienCoc'       => 400000,
            'trangThai'     => 'Đã trả',
        ]);

        $detail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->room101->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        $stay = Luu_Tru::create([
            'maChiTiet'    => $detail->id,
            'thoiGianNhan' => '2026-11-15 14:00:00',
            'thoiGianTra'  => '2026-11-17 12:00:00',
            'trangThai'    => 'Đã trả',
        ]);

        $invoice = Hoa_Don::create([
            'maLuuTru'  => $stay->id,
            'ngayLap'   => '2026-11-17 12:00:00',
            'tongTien'  => 950000,
            'trangThai' => 'Đã thanh toán',
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Tiền phòng P.101 (2 đêm)',
            'soLuong'   => 2,
            'donGia'    => 400000,
            'thanhTien' => 800000,
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Dịch vụ giặt ủi',
            'soLuong'   => 1,
            'donGia'    => 150000,
            'thanhTien' => 150000,
        ]);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kpi.tongDoanhThu', 950000)
            ->assertJsonPath('data.kpi.tongDonDat', 1)
            ->assertJsonPath('data.kpi.tongDonThanhCong', 1)
            ->assertJsonPath('data.kpi.tongDonHuy', 0)
            ->assertJsonPath('data.kpi.tongLuotKhach', 2)
            ->assertJsonPath('data.doanhThu.nguonThu.tienPhong', 800000)
            ->assertJsonPath('data.doanhThu.nguonThu.dichVu', 150000);
    }

    /**
     * Test 2: Thống kê doanh thu theo ngày.
     */
    public function test_manager_can_access_revenue_report_grouped_by_day(): void
    {
        Sanctum::actingAs($this->managerUser);

        $invoice1 = Hoa_Don::create([
            'maLuuTru'  => 1,
            'ngayLap'   => '2026-11-10 14:00:00',
            'tongTien'  => 500000,
            'trangThai' => 'Đã thanh toán',
        ]);

        $invoice2 = Hoa_Don::create([
            'maLuuTru'  => 2,
            'ngayLap'   => '2026-11-10 18:30:00',
            'tongTien'  => 700000,
            'trangThai' => 'Đã thanh toán',
        ]);

        $invoice3 = Hoa_Don::create([
            'maLuuTru'  => 3,
            'ngayLap'   => '2026-11-12 09:00:00',
            'tongTien'  => 1200000,
            'trangThai' => 'Đã thanh toán',
        ]);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2026-11-01&denNgay=2026-11-30&nhomTheo=ngay');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tongDoanhThu', 2400000)
            ->assertJsonPath('data.soHoaDon', 3);

        $timeline = $response->json('data.timeline');
        $this->assertCount(2, $timeline);
        $this->assertEquals('2026-11-10', $timeline[0]['thoiGian']);
        $this->assertEquals(1200000, $timeline[0]['doanhThu']);
        $this->assertEquals(2, $timeline[0]['soHoaDon']);
        $this->assertEquals('2026-11-12', $timeline[1]['thoiGian']);
        $this->assertEquals(1200000, $timeline[1]['doanhThu']);
        $this->assertEquals(1, $timeline[1]['soHoaDon']);
    }

    /**
     * Test 3: Thống kê tình hình đặt phòng và lưu trú.
     */
    public function test_manager_can_access_bookings_and_stay_report(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Đơn 1: Hoàn thành
        $b1 = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => '2026-11-05 10:00:00',
            'ngayNhanPhong' => '2026-11-06 14:00:00',
            'ngayTraPhong'  => '2026-11-08 12:00:00',
            'soLuongKhach'  => 2,
            'tienCoc'       => 0,
            'trangThai'     => 'Đã trả',
        ]);

        // Đơn 2: Đã hủy
        $b2 = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => '2026-11-07 15:00:00',
            'ngayNhanPhong' => '2026-11-10 14:00:00',
            'ngayTraPhong'  => '2026-11-12 12:00:00',
            'soLuongKhach'  => 1,
            'tienCoc'       => 0,
            'trangThai'     => 'Đã hủy',
        ]);

        $response = $this->getJson('/api/v1/reports/bookings?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tongDonDat', 2)
            ->assertJsonPath('data.donThanhCong', 1)
            ->assertJsonPath('data.donHuy', 1);

        $this->assertEquals(50, $response->json('data.tyLeHuy'));
    }

    /**
     * Test 4: Thống kê công suất sử dụng phòng toàn khách sạn.
     */
    public function test_manager_can_access_occupancy_report_for_entire_hotel(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Khách sạn có 3 phòng (P.101, P.102, P.201)
        // Kỳ báo cáo: 2 ngày (2026-11-01 đến 2026-11-02) => Tổng phòng-ngày khả dụng = 3 * 2 = 6
        // Tạo 1 lượt lưu trú tại P.101 cả 2 ngày
        $booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'ngayDat'       => '2026-11-01 08:00:00',
            'ngayNhanPhong' => '2026-11-01 14:00:00',
            'ngayTraPhong'  => '2026-11-03 12:00:00',
            'soLuongKhach'  => 2,
            'tienCoc'       => 0,
            'trangThai'     => 'Đang ở',
        ]);

        $detail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $booking->id,
            'maPhong'        => $this->room101->id,
            'giaPhongApDung' => 400000,
            'soLuongKhach'   => 2,
        ]);

        Luu_Tru::create([
            'maChiTiet'    => $detail->id,
            'thoiGianNhan' => '2026-11-01 14:00:00',
            'thoiGianTra'  => '2026-11-03 12:00:00',
            'trangThai'    => 'Đang ở',
        ]);

        $response = $this->getJson('/api/v1/reports/occupancy?tuNgay=2026-11-01&denNgay=2026-11-02');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.soNgayTrongKy', 2)
            ->assertJsonPath('data.soPhongKhaDung', 3)
            ->assertJsonPath('data.tongPhongNgayKhaDung', 6)
            ->assertJsonPath('data.tongPhongNgayCoKhach', 2)
            ->assertJsonPath('data.toanKhachSan', 33.33);
    }

    // ==========================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // ==========================================

    /**
     * Test 5: Thống kê doanh thu theo tháng (nhomTheo=thang).
     */
    public function test_manager_can_access_revenue_report_grouped_by_month(): void
    {
        Sanctum::actingAs($this->managerUser);

        Hoa_Don::create([
            'maLuuTru'  => 1,
            'ngayLap'   => '2026-10-15 10:00:00',
            'tongTien'  => 1000000,
            'trangThai' => 'Đã thanh toán',
        ]);

        Hoa_Don::create([
            'maLuuTru'  => 2,
            'ngayLap'   => '2026-11-20 15:00:00',
            'tongTien'  => 2000000,
            'trangThai' => 'Đã thanh toán',
        ]);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2026-10-01&denNgay=2026-11-30&nhomTheo=thang');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tongDoanhThu', 3000000);

        $timeline = $response->json('data.timeline');
        $this->assertCount(2, $timeline);
        $this->assertEquals('2026-10', $timeline[0]['thoiGian']);
        $this->assertEquals(1000000, $timeline[0]['doanhThu']);
        $this->assertEquals('2026-11', $timeline[1]['thoiGian']);
        $this->assertEquals(2000000, $timeline[1]['doanhThu']);
    }

    /**
     * Test 6: Thống kê doanh thu theo năm (nhomTheo=nam).
     */
    public function test_manager_can_access_revenue_report_grouped_by_year(): void
    {
        Sanctum::actingAs($this->managerUser);

        Hoa_Don::create([
            'maLuuTru'  => 1,
            'ngayLap'   => '2025-12-31 23:00:00',
            'tongTien'  => 1500000,
            'trangThai' => 'Đã thanh toán',
        ]);

        Hoa_Don::create([
            'maLuuTru'  => 2,
            'ngayLap'   => '2026-01-01 01:00:00',
            'tongTien'  => 2500000,
            'trangThai' => 'Đã thanh toán',
        ]);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2025-01-01&denNgay=2026-12-31&nhomTheo=nam');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tongDoanhThu', 4000000);

        $timeline = $response->json('data.timeline');
        $this->assertCount(2, $timeline);
        $this->assertEquals('2025', $timeline[0]['thoiGian']);
        $this->assertEquals(1500000, $timeline[0]['doanhThu']);
        $this->assertEquals('2026', $timeline[1]['thoiGian']);
        $this->assertEquals(2500000, $timeline[1]['doanhThu']);
    }

    /**
     * Test 7: Lọc công suất theo từng hạng phòng cụ thể (maLoaiPhong).
     */
    public function test_manager_can_filter_occupancy_report_by_room_type(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/occupancy?tuNgay=2026-11-01&denNgay=2026-11-05&maLoaiPhong=' . $this->roomTypeStandard->id);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.soPhongKhaDung', 2) // Chỉ 2 phòng Standard
            ->assertJsonPath('data.soNgayTrongKy', 5)
            ->assertJsonPath('data.tongPhongNgayKhaDung', 10);
    }

    /**
     * Test 8: BR04.6 - Doanh thu chỉ tính hóa đơn "Đã thanh toán", không tính "Chờ thanh toán".
     */
    public function test_br04_6_revenue_only_includes_paid_invoices_and_excludes_pending_invoices(): void
    {
        Sanctum::actingAs($this->managerUser);

        // Hóa đơn đã thanh toán: 1,000,000đ
        Hoa_Don::create([
            'maLuuTru'  => 1,
            'ngayLap'   => '2026-11-10 10:00:00',
            'tongTien'  => 1000000,
            'trangThai' => 'Đã thanh toán',
        ]);

        // Hóa đơn đang chờ thanh toán: 5,000,000đ (KHÔNG ĐƯỢC CỘNG)
        Hoa_Don::create([
            'maLuuTru'  => 2,
            'ngayLap'   => '2026-11-10 11:00:00',
            'tongTien'  => 5000000,
            'trangThai' => 'Chờ thanh toán',
        ]);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.tongDoanhThu', 1000000)
            ->assertJsonPath('data.soHoaDon', 1);
    }

    /**
     * Test 9: Phân loại chi tiết nguồn thu chính xác (Tiền phòng, Dịch vụ, Minibar, Phụ thu).
     */
    public function test_revenue_properly_categorizes_details_without_double_counting(): void
    {
        Sanctum::actingAs($this->managerUser);

        $invoice = Hoa_Don::create([
            'maLuuTru'  => 1,
            'ngayLap'   => '2026-11-12 12:00:00',
            'tongTien'  => 1700000,
            'trangThai' => 'Đã thanh toán',
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Tiền phòng P.101',
            'soLuong'   => 1,
            'donGia'    => 1000000,
            'thanhTien' => 1000000,
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Dịch vụ ăn uống tại phòng',
            'soLuong'   => 2,
            'donGia'    => 150000,
            'thanhTien' => 300000,
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Tiêu dùng Minibar nước ngọt',
            'soLuong'   => 4,
            'donGia'    => 50000,
            'thanhTien' => 200000,
        ]);

        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $invoice->id,
            'noiDung'   => 'Phụ thu nhận phòng sớm 3 giờ',
            'soLuong'   => 1,
            'donGia'    => 200000,
            'thanhTien' => 200000,
        ]);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(200)
            ->assertJsonPath('data.tongDoanhThu', 1700000)
            ->assertJsonPath('data.nguonThu.tienPhong', 1000000)
            ->assertJsonPath('data.nguonThu.dichVu', 300000)
            ->assertJsonPath('data.nguonThu.minibar', 200000)
            ->assertJsonPath('data.nguonThu.phuThu', 200000);
    }

    /**
     * Test 10: Khoảng thời gian không có dữ liệu trả về 200 với các giá trị 0 và thông báo rõ ràng.
     */
    public function test_empty_date_range_returns_200_with_zero_figures_and_friendly_message(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2020-01-01&denNgay=2020-01-31');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Không có dữ liệu phát sinh trong khoảng thời gian này.')
            ->assertJsonPath('data.kpi.tongDoanhThu', 0)
            ->assertJsonPath('data.kpi.tongDonDat', 0)
            ->assertJsonPath('data.kpi.tongDonHuy', 0)
            ->assertJsonPath('data.kpi.tongLuotKhach', 0);
    }

    // ==========================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // ==========================================

    /**
     * Test 11: Bỏ trống tuNgay trả về lỗi validation 422.
     */
    public function test_missing_tu_ngay_returns_422_validation_error(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/dashboard?denNgay=2026-11-30');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['tuNgay']]);
    }

    /**
     * Test 12: Bỏ trống denNgay trả về lỗi validation 422.
     */
    public function test_missing_den_ngay_returns_422_validation_error(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['denNgay']]);
    }

    /**
     * Test 13: denNgay nhỏ hơn tuNgay trả về lỗi validation 422.
     */
    public function test_den_ngay_before_tu_ngay_returns_422_validation_error(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-30&denNgay=2026-11-01');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['denNgay']]);
    }

    /**
     * Test 14: nhomTheo không hợp lệ trả về lỗi validation 422.
     */
    public function test_invalid_nhom_theo_returns_422_validation_error(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/revenue?tuNgay=2026-11-01&denNgay=2026-11-30&nhomTheo=tuan');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['nhomTheo']]);
    }

    /**
     * Test 15: maLoaiPhong không tồn tại trả về lỗi validation 422.
     */
    public function test_non_existent_ma_loai_phong_returns_422_validation_error(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/reports/occupancy?tuNgay=2026-11-01&denNgay=2026-11-30&maLoaiPhong=999999');

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['maLoaiPhong']]);
    }

    /**
     * Test 16: Không có Bearer Token đăng nhập trả về 401 Unauthorized.
     */
    public function test_unauthenticated_user_cannot_access_reports_returns_401(): void
    {
        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(401);
    }

    /**
     * Test 17: Vai trò Lễ tân không được phép xem báo cáo quản trị, nhận 403 Forbidden.
     */
    public function test_receptionist_role_cannot_access_reports_returns_403(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test 18: Vai trò Buồng phòng không được phép xem báo cáo quản trị, nhận 403 Forbidden.
     */
    public function test_housekeeper_role_cannot_access_reports_returns_403(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test 19: Vai trò Khách hàng không được phép xem báo cáo quản trị, nhận 403 Forbidden.
     */
    public function test_customer_role_cannot_access_reports_returns_403(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    /**
     * Test 20: Việc gọi API báo cáo thống kê hoàn toàn không làm thay đổi hay xóa dữ liệu trong CSDL.
     */
    public function test_viewing_reports_does_not_mutate_database_records(): void
    {
        Sanctum::actingAs($this->managerUser);

        $invoiceCountBefore = Hoa_Don::count();
        $bookingCountBefore = Dat_Phong::count();
        $roomCountBefore = Phong::count();

        $this->getJson('/api/v1/reports/dashboard?tuNgay=2026-11-01&denNgay=2026-11-30')->assertStatus(200);
        $this->getJson('/api/v1/reports/revenue?tuNgay=2026-11-01&denNgay=2026-11-30')->assertStatus(200);
        $this->getJson('/api/v1/reports/bookings?tuNgay=2026-11-01&denNgay=2026-11-30')->assertStatus(200);
        $this->getJson('/api/v1/reports/occupancy?tuNgay=2026-11-01&denNgay=2026-11-30')->assertStatus(200);

        $this->assertEquals($invoiceCountBefore, Hoa_Don::count());
        $this->assertEquals($bookingCountBefore, Dat_Phong::count());
        $this->assertEquals($roomCountBefore, Phong::count());
    }
}
