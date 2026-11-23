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
use App\Models\Thanh_Toan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
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

    protected Loai_Phong $roomTypeStandard;
    protected Phong $roomP101;
    protected Phong $roomP102;

    protected Dat_Phong $booking;
    protected Chi_Tiet_Dat_Phong $bookingDetail;
    protected Luu_Tru $stay;
    protected Hoa_Don $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo loại phòng & phòng
        $this->roomTypeStandard = Loai_Phong::create([
            'tenLoai'      => 'Tiêu Chuẩn',
            'moTa'         => 'Phòng đơn tiêu chuẩn 1 giường',
            'giaTieuChuan' => 500000,
            'sucChuaToiDa' => 2,
            'trangThai'    => 'Kinh doanh',
        ]);

        $this->roomP101 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P101',
            'tang'        => 1,
            'trangThai'   => 'Đang sử dụng',
        ]);

        $this->roomP102 = Phong::create([
            'maLoaiPhong' => $this->roomTypeStandard->id,
            'tenPhong'    => 'P102',
            'tang'        => 1,
            'trangThai'   => 'Sẵn sàng',
        ]);

        // 2. Tạo tài khoản Quản lý
        $this->managerUser = Tai_Khoan::create([
            'tenDangNhap' => 'manager_uc07',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->managerProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->managerUser->id,
            'hoTen'       => 'Trần Quản Lý',
            'ngaySinh'    => '1985-05-15',
            'cccd'        => '079085000071',
            'soDienThoai' => '0901000071',
            'chucVu'      => 'Quản lý',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 3. Tạo tài khoản Lễ tân
        $this->receptionistUser = Tai_Khoan::create([
            'tenDangNhap' => 'reception_uc07',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->receptionistProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->receptionistUser->id,
            'hoTen'       => 'Lê Lễ Tân',
            'ngaySinh'    => '1995-08-20',
            'cccd'        => '079095000072',
            'soDienThoai' => '0902000072',
            'chucVu'      => 'Lễ tân',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 4. Tạo tài khoản Buồng phòng
        $this->housekeeperUser = Tai_Khoan::create([
            'tenDangNhap' => 'housekeeper_uc07',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->housekeeperProfile = Nhan_Vien::create([
            'maTaiKhoan'  => $this->housekeeperUser->id,
            'hoTen'       => 'Phạm Buồng Phòng',
            'ngaySinh'    => '1992-03-10',
            'cccd'        => '079092000073',
            'soDienThoai' => '0903000073',
            'chucVu'      => 'Buồng phòng',
            'trangThai'   => 'Đang làm việc',
        ]);

        // 5. Tạo tài khoản Khách hàng chính
        $this->customerUser = Tai_Khoan::create([
            'tenDangNhap' => 'customer_uc07',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->customerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->customerUser->id,
            'hoTen'       => 'Nguyễn Văn Khách',
            'cccd'        => '079099000074',
            'email'       => 'khach_uc07@example.com',
            'soDienThoai' => '0904000074',
            'diaChi'      => '123 Đường Test, Q1, TP.HCM',
        ]);

        // 6. Tạo tài khoản Khách hàng khác
        $this->otherCustomerUser = Tai_Khoan::create([
            'tenDangNhap' => 'other_customer_uc07',
            'password'    => Hash::make('Password123@'),
            'trangThai'   => 'Hoạt động',
        ]);
        $this->otherCustomerProfile = Khach_Hang::create([
            'maTaiKhoan'  => $this->otherCustomerUser->id,
            'hoTen'       => 'Trần Thị Khách Khác',
            'cccd'        => '079099000075',
            'email'       => 'other_khach@example.com',
            'soDienThoai' => '0905000075',
            'diaChi'      => '456 Đường Khác, Q3, TP.HCM',
        ]);

        // 7. Tạo chuỗi dữ liệu Đặt phòng -> Chi tiết đặt phòng -> Lưu trú -> Hóa đơn
        $this->booking = Dat_Phong::create([
            'maKhachHang'   => $this->customerProfile->id,
            'maNhanVien'    => $this->receptionistProfile->id,
            'ngayDat'       => Carbon::now()->subDays(2),
            'ngayNhanPhong' => Carbon::now()->subDays(1),
            'ngayTraPhong'  => Carbon::now()->addDays(1),
            'soLuongKhach'  => 2,
            'tienCoc'       => 200000,
            'trangThai'     => 'Đang sử dụng',
        ]);

        $this->bookingDetail = Chi_Tiet_Dat_Phong::create([
            'maDatPhong'     => $this->booking->id,
            'maPhong'        => $this->roomP101->id,
            'giaPhongApDung' => 500000,
            'soLuongKhach'   => 2,
        ]);

        $this->stay = Luu_Tru::create([
            'maChiTiet'    => $this->bookingDetail->id,
            'thoiGianNhan' => Carbon::now()->subDays(1),
            'thoiGianTra'  => Carbon::now()->addDays(1),
            'trangThai'    => 'Đang lưu trú',
        ]);

        // Hóa đơn khởi tạo có tổng tiền 1,000,000 VNĐ
        $this->invoice = Hoa_Don::create([
            'maLuuTru'  => $this->stay->id,
            'ngayLap'   => Carbon::now(),
            'tongTien'  => 1000000,
            'trangThai' => 'Chờ thanh toán',
        ]);

        // Thêm chi tiết hóa đơn ban đầu: Tiền phòng 2 đêm x 500,000
        Chi_Tiet_Hoa_Don::create([
            'maHoaDon'  => $this->invoice->id,
            'noiDung'   => 'Tiền phòng P101 (2 đêm)',
            'soLuong'   => 2,
            'donGia'    => 500000,
            'thanhTien' => 1000000,
        ]);
    }

    // ==========================================
    // 1. LUỒNG CHÍNH (MAIN FLOW)
    // ==========================================

    /**
     * Test Lễ tân và Quản lý có thể lấy danh sách hóa đơn thành công.
     */
    public function test_receptionist_or_manager_can_fetch_invoices_list(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson('/api/v1/invoices');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Lấy danh sách hóa đơn thành công.',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'maHoaDon',
                        'maLuuTru',
                        'ngayLap',
                        'tongTien',
                        'daThanhToan',
                        'conLai',
                        'trangThai',
                        'tenKhachHang',
                        'tenPhong',
                    ],
                ],
            ]);

        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test xem chi tiết một hóa đơn Chờ thanh toán với đầy đủ thông tin phòng, khách hàng, chi tiết mục thu.
     */
    public function test_receptionist_can_view_invoice_detail(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->getJson("/api/v1/invoices/{$this->invoice->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Truy vấn hóa đơn thành công.',
                'data'    => [
                    'maHoaDon'    => $this->invoice->id,
                    'maLuuTru'    => $this->stay->id,
                    'tongTien'    => 1000000,
                    'daThanhToan' => 0,
                    'conLai'      => 1000000,
                    'trangThai'   => 'Chờ thanh toán',
                    'tienCoc'     => 200000,
                    'khachHang'   => [
                        'hoTen' => 'Nguyễn Văn Khách',
                    ],
                    'phong'       => [
                        'tenPhong' => 'P101',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'chiTiet' => [
                        '*' => ['id', 'noiDung', 'soLuong', 'donGia', 'thanhTien'],
                    ],
                ],
            ]);
    }

    /**
     * Test thanh toán đủ toàn bộ số tiền còn lại, hóa đơn chuyển sang Đã thanh toán.
     */
    public function test_payment_full_amount_updates_invoice_to_paid(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 1000000,
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'BANK_TRANS_UC07_0001',
        ];

        $response = $this->postJson('/api/v1/payments', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'message' => 'Xác nhận thanh toán thành công.',
                'data'    => [
                    'thanhToan' => [
                        'maHoaDon'   => $this->invoice->id,
                        'soTien'     => 1000000,
                        'phuongThuc' => 'qr',
                        'maGiaoDich' => 'BANK_TRANS_UC07_0001',
                        'trangThai'  => 'Thành công',
                    ],
                    'hoaDon'    => [
                        'maHoaDon'    => $this->invoice->id,
                        'tongTien'    => 1000000,
                        'daThanhToan' => 1000000,
                        'conLai'      => 0,
                        'trangThai'   => 'Đã thanh toán',
                    ],
                ],
            ]);

        // Kiểm tra cơ sở dữ liệu
        $this->assertDatabaseHas('thanh_toan', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 1000000,
            'maGiaoDich' => 'BANK_TRANS_UC07_0001',
            'trangThai'  => 'Thành công',
        ]);

        $this->assertDatabaseHas('hoa_don', [
            'id'        => $this->invoice->id,
            'trangThai' => 'Đã thanh toán',
        ]);
    }

    /**
     * Test thanh toán một phần tiền, trạng thái hóa đơn vẫn là Chờ thanh toán và còn lại được tính đúng.
     */
    public function test_payment_partial_amount_keeps_invoice_pending(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 400000,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'CASH_TRANS_UC07_0001',
        ];

        $response = $this->postJson('/api/v1/payments', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 200,
                'data'    => [
                    'hoaDon' => [
                        'tongTien'    => 1000000,
                        'daThanhToan' => 400000,
                        'conLai'      => 600000,
                        'trangThai'   => 'Chờ thanh toán',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('hoa_don', [
            'id'        => $this->invoice->id,
            'trangThai' => 'Chờ thanh toán',
        ]);
    }

    /**
     * Test hỗ trợ các phương thức thanh toán tien mat, qr, the.
     */
    public function test_payment_supports_cash_qr_and_card_methods(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $methods = ['tien mat', 'qr', 'the'];
        $stepAmount = 300000;

        foreach ($methods as $index => $method) {
            $transId = "TRANS_TEST_METHOD_{$index}";
            $response = $this->postJson('/api/v1/payments', [
                'maHoaDon'   => $this->invoice->id,
                'soTien'     => $stepAmount,
                'phuongThuc' => $method,
                'maGiaoDich' => $transId,
            ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('thanh_toan', [
                'maHoaDon'   => $this->invoice->id,
                'phuongThuc' => $method,
                'maGiaoDich' => $transId,
            ]);
        }
    }

    // ==========================================
    // 2. LUỒNG THAY THẾ (ALTERNATIVE FLOW)
    // ==========================================

    /**
     * Test Lễ tân thêm khoản phụ thu/điều chỉnh thành công và cập nhật lại tổng tiền.
     */
    public function test_receptionist_can_add_adjustment_surcharge(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $payload = [
            'noiDung' => 'Phụ thu trả phòng trễ 2 giờ',
            'soLuong' => 1,
            'donGia'  => 150000,
        ];

        $response = $this->postJson("/api/v1/invoices/{$this->invoice->id}/adjustments", $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status'  => 201,
                'message' => 'Thêm khoản điều chỉnh vào hóa đơn thành công.',
                'data'    => [
                    'chiTiet'  => [
                        'noiDung'   => 'Phụ thu trả phòng trễ 2 giờ',
                        'soLuong'   => 1,
                        'donGia'    => 150000,
                        'thanhTien' => 150000,
                    ],
                    'tongTien' => 1150000, // 1,000,000 + 150,000
                ],
            ]);

        $this->assertDatabaseHas('chi_tiet_hoa_don', [
            'maHoaDon'  => $this->invoice->id,
            'noiDung'   => 'Phụ thu trả phòng trễ 2 giờ',
            'thanhTien' => 150000,
        ]);

        $this->assertDatabaseHas('hoa_don', [
            'id'       => $this->invoice->id,
            'tongTien' => 1150000,
        ]);
    }

    /**
     * Test lọc danh sách hóa đơn theo trạng thái (Chờ thanh toán, Đã thanh toán).
     */
    public function test_filter_invoices_by_status(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Tạo thêm 1 hóa đơn đã thanh toán
        $paidInvoice = Hoa_Don::create([
            'maLuuTru'  => $this->stay->id,
            'ngayLap'   => Carbon::now()->subDays(5),
            'tongTien'  => 500000,
            'trangThai' => 'Đã thanh toán',
        ]);

        // Lọc Chờ thanh toán
        $responsePending = $this->getJson('/api/v1/invoices?trangThai=Chờ thanh toán');
        $responsePending->assertStatus(200);
        $this->assertCount(1, $responsePending->json('data'));
        $this->assertEquals($this->invoice->id, $responsePending->json('data.0.maHoaDon'));

        // Lọc Đã thanh toán
        $responsePaid = $this->getJson('/api/v1/invoices?trangThai=Đã thanh toán');
        $responsePaid->assertStatus(200);
        $this->assertCount(1, $responsePaid->json('data'));
        $this->assertEquals($paidInvoice->id, $responsePaid->json('data.0.maHoaDon'));
    }

    /**
     * Test tìm kiếm hóa đơn theo keyword (tên khách hàng, số phòng, mã hóa đơn).
     */
    public function test_search_invoices_by_keyword(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Tìm theo tên phòng 'P101'
        $responseRoom = $this->getJson('/api/v1/invoices?keyword=P101');
        $responseRoom->assertStatus(200);
        $this->assertCount(1, $responseRoom->json('data'));

        // Tìm theo tên khách hàng
        $responseCustomer = $this->getJson('/api/v1/invoices?keyword=Nguyễn Văn Khách');
        $responseCustomer->assertStatus(200);
        $this->assertCount(1, $responseCustomer->json('data'));

        // Tìm theo từ khóa không khớp
        $responseNone = $this->getJson('/api/v1/invoices?keyword=KhongTonTai123');
        $responseNone->assertStatus(200);
        $this->assertCount(0, $responseNone->json('data'));
    }

    /**
     * Test khách hàng xem được hóa đơn của mình, nhưng không xem được hóa đơn của khách khác.
     */
    public function test_customer_can_view_own_invoice_but_forbidden_for_other(): void
    {
        // 1. Khách hàng chính xem hóa đơn của mình -> 200 OK
        Sanctum::actingAs($this->customerUser);
        $responseOwn = $this->getJson("/api/v1/invoices/{$this->invoice->id}");
        $responseOwn->assertStatus(200);

        // 2. Khách hàng khác xem hóa đơn của khách hàng chính -> 403 Forbidden
        Sanctum::actingAs($this->otherCustomerUser);
        $responseOther = $this->getJson("/api/v1/invoices/{$this->invoice->id}");
        $responseOther->assertStatus(403)
            ->assertJson([
                'success' => false,
                'status'  => 403,
                'message' => 'Bạn không có quyền xem hóa đơn của khách hàng khác.',
            ]);
    }

    /**
     * Test phân trang khi có tham số page.
     */
    public function test_pagination_meta_included_when_page_parameter_present(): void
    {
        Sanctum::actingAs($this->managerUser);

        $response = $this->getJson('/api/v1/invoices?page=1&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    // ==========================================
    // 3. LUỒNG NGOẠI LỆ (EXCEPTION FLOW)
    // ==========================================

    /**
     * Test thanh toán số tiền âm hoặc bằng 0 bị từ chối 422.
     */
    public function test_payment_fails_when_amount_is_zero_or_negative(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $responseZero = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 0,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'ZERO_TRANS',
        ]);
        $responseZero->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);

        $responseNegative = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => -50000,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'NEG_TRANS',
        ]);
        $responseNegative->assertStatus(422);
    }

    /**
     * Test thanh toán vượt quá số tiền còn lại phải trả trả về 422.
     */
    public function test_payment_fails_when_amount_exceeds_remaining_balance(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 1500000, // Vượt quá 1,000,000
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'EXCEED_TRANS',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);

        $this->assertStringContainsString('vượt quá số tiền còn lại', $response->json('message'));
    }

    /**
     * Test thanh toán hóa đơn không tồn tại trả về 422 từ FormRequest hoặc 404.
     */
    public function test_payment_fails_when_invoice_not_found(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $response = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => 999999,
            'soTien'     => 500000,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'NOT_FOUND_TRANS',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);
    }

    /**
     * Test thanh toán hóa đơn đã ở trạng thái Đã thanh toán bị chặn với 409 Conflict (BR04.2).
     */
    public function test_payment_fails_when_invoice_already_paid(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Chuyển hóa đơn sang Đã thanh toán
        $this->invoice->update(['trangThai' => 'Đã thanh toán']);

        $response = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 100000,
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'ALREADY_PAID_TRANS',
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('đã được thanh toán', $response->json('message'));
    }

    /**
     * Test thêm khoản phụ thu vào hóa đơn đã Đã thanh toán bị từ chối 409 Conflict (BR04.2).
     */
    public function test_adjustment_fails_when_invoice_already_paid(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        $this->invoice->update(['trangThai' => 'Đã thanh toán']);

        $response = $this->postJson("/api/v1/invoices/{$this->invoice->id}/adjustments", [
            'noiDung' => 'Phụ thu thêm sau khi thanh toán',
            'soLuong' => 1,
            'donGia'  => 100000,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'status'  => 409,
            ]);

        $this->assertStringContainsString('không thể chỉnh sửa', $response->json('message'));
    }

    /**
     * Test gửi trùng mã giao dịch (maGiaoDich) bị từ chối để đảm bảo idempotency (BR04.4).
     */
    public function test_payment_fails_with_duplicate_transaction_id(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Giao dịch 1
        $response1 = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 200000,
            'phuongThuc' => 'the',
            'maGiaoDich' => 'DUPLICATE_TRANS_001',
        ]);
        $response1->assertStatus(200);

        // Giao dịch 2 gửi lại cùng maGiaoDich
        $response2 = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 200000,
            'phuongThuc' => 'the',
            'maGiaoDich' => 'DUPLICATE_TRANS_001',
        ]);

        // FormRequest bắt unique rule trả 422
        $response2->assertStatus(422)
            ->assertJson([
                'success' => false,
                'status'  => 422,
            ]);

        $this->assertStringContainsString('đã tồn tại', $response2->json('errors.maGiaoDich.0'));
    }

    /**
     * Test không có Bearer Token nhận lỗi 401 Unauthorized.
     */
    public function test_unauthenticated_cannot_access_invoice_or_payment_apis(): void
    {
        $responseList = $this->getJson('/api/v1/invoices');
        $responseList->assertStatus(401);

        $responseDetail = $this->getJson("/api/v1/invoices/{$this->invoice->id}");
        $responseDetail->assertStatus(401);

        $responseAdjustment = $this->postJson("/api/v1/invoices/{$this->invoice->id}/adjustments", [
            'noiDung' => 'Test',
            'soLuong' => 1,
            'donGia'  => 50000,
        ]);
        $responseAdjustment->assertStatus(401);

        $responsePayment = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 50000,
            'phuongThuc' => 'qr',
            'maGiaoDich' => 'NO_AUTH',
        ]);
        $responsePayment->assertStatus(401);
    }

    /**
     * Test vai trò không hợp lệ (Buồng phòng) nhận lỗi 403 khi thêm điều chỉnh hoặc thanh toán.
     */
    public function test_unauthorized_role_cannot_add_adjustment_or_pay(): void
    {
        Sanctum::actingAs($this->housekeeperUser);

        // 1. Buồng phòng thêm điều chỉnh hóa đơn -> 403
        $responseAdjustment = $this->postJson("/api/v1/invoices/{$this->invoice->id}/adjustments", [
            'noiDung' => 'Khách làm hỏng gối',
            'soLuong' => 1,
            'donGia'  => 100000,
        ]);
        $responseAdjustment->assertStatus(403);

        // 2. Buồng phòng ghi nhận thanh toán -> 403
        $responsePayment = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 100000,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'HK_PAY_TRANS',
        ]);
        $responsePayment->assertStatus(403);
    }

    /**
     * Test khi xảy ra lỗi database trong lúc ghi thanh toán, transaction tự rollback và trả về 500.
     */
    public function test_transaction_rollback_when_database_error_occurs(): void
    {
        Sanctum::actingAs($this->receptionistUser);

        // Giả lập lỗi DB bằng cách lắng nghe sự kiện tạo thanh_toan và throw Exception
        Thanh_Toan::creating(function () {
            throw new \Exception('Simulated database failure during payment.');
        });

        $response = $this->postJson('/api/v1/payments', [
            'maHoaDon'   => $this->invoice->id,
            'soTien'     => 500000,
            'phuongThuc' => 'tien mat',
            'maGiaoDich' => 'SIMULATED_ERR_TRANS',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'status'  => 500,
                'message' => 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.',
            ]);

        // Đảm bảo không có bản ghi thanh toán rác nào được lưu
        $this->assertDatabaseMissing('thanh_toan', [
            'maGiaoDich' => 'SIMULATED_ERR_TRANS',
        ]);

        // Trạng thái hóa đơn không đổi
        $this->assertDatabaseHas('hoa_don', [
            'id'        => $this->invoice->id,
            'trangThai' => 'Chờ thanh toán',
        ]);
    }
}
