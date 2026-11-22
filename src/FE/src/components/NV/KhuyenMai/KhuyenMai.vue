<template>
  <div class="khuyen-mai-page py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- TOAST THÔNG BÁO TỔNG THỂ -->
      <div
        v-if="toast.show"
        class="alert alert-dismissible fade show position-fixed top-0 end-0 m-4 shadow-lg animate-fade-in"
        :class="toast.type === 'success' ? 'alert-success border-success' : 'alert-danger border-danger'"
        style="z-index: 9999; max-width: 440px;"
        role="alert"
      >
        <div class="d-flex align-items-start gap-2">
          <i
            class="fs-5 flex-shrink-0 mt-0.5"
            :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-success' : 'bi bi-exclamation-triangle-fill text-danger'"
          ></i>
          <div>
            <strong>{{ toast.type === 'success' ? 'Thành công' : 'Thông báo hệ thống' }}</strong>
            <div class="small mt-0.5">{{ toast.message }}</div>
          </div>
        </div>
        <button type="button" class="btn-close" @click="toast.show = false"></button>
      </div>

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: QUAY LẠI, BREADCRUMB, THỜI GIAN, USER PROFILE -->
      <div class="top-nav-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-white rounded-4 border shadow-xs">
        <!-- Bên trái: Nút quay lại & Breadcrumb -->
        <div class="d-flex align-items-center gap-3">
          <button
            class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-xs"
            @click="quayLai"
            title="Quay lại"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
          </button>

          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
              <li class="breadcrumb-item text-muted">Khách sạn</li>
              <li class="breadcrumb-item active text-teal fw-bold" aria-current="page">Quản lý khuyến mãi</li>
            </ol>
          </nav>
        </div>

        <!-- Bên phải: Ngày tháng thực tế, Chuông thông báo & Profile Trần An -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1 text-teal"></i>
            {{ ngayHienTai }}
          </span>

          <button class="btn btn-light btn-sm rounded-circle border p-2 position-relative" title="Thông báo">
            <i class="bi bi-bell text-secondary"></i>
          </button>

          <!-- User Badge: TA - Trần An (Chủ khách sạn / Admin) -->
          <div class="d-flex align-items-center gap-2 ps-2 border-start">
            <div class="user-avatar-circle">TA</div>
            <div class="d-none d-sm-block text-start lh-sm">
              <div class="fw-bold small text-dark">Trần An</div>
              <div class="text-muted" style="font-size: 0.72rem;">Admin</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ TRANG & NÚT TẠO KHUYẾN MÃI -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="h3 fw-bold text-dark mb-1">Quản lý khuyến mãi</h2>
          <p class="text-muted mb-0 small">
            Thiết lập ưu đãi có điều kiện rõ ràng, kiểm soát lượt dùng và thời hạn.
          </p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Nút mở Drawer Thử nghiệm Đặt phòng & Hóa đơn khách hàng -->
          <button
            class="btn btn-outline-success d-inline-flex align-items-center gap-2 rounded-3 px-3 py-2 fw-medium shadow-xs"
            @click="moModalThuNghiem"
          >
            <i class="bi bi-patch-check"></i>
            <span>Thử nghiệm mã & Hóa đơn</span>
          </button>

          <!-- Nút Tạo khuyến mãi màu Teal đặc trưng -->
          <button
            class="btn btn-teal-primary d-inline-flex align-items-center gap-2 rounded-3 px-3.5 py-2 fw-semibold text-white shadow-xs"
            @click="chuyenSangTaoMoi"
          >
            <i class="bi bi-plus-lg"></i>
            <span>Tạo khuyến mãi</span>
          </button>
        </div>
      </div>

      <!-- KHU VỰC BỐ CỤC 2 CỘT CHÍNH -->
      <div class="row g-4">
        <!-- ==================== CỘT TRÁI (DANH SÁCH & XEM TRƯỚC CÁCH TÍNH) ==================== -->
        <div class="col-lg-7 d-flex flex-column gap-4">
          <!-- CARD 1: DANH SÁCH CHƯƠNG TRÌNH -->
          <div class="card border rounded-4 shadow-xs bg-white overflow-hidden">
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0 fs-6">Danh sách chương trình</h5>
                <span class="badge bg-light text-muted border fw-normal px-2.5 py-1">
                  {{ danhSachFiltered.length }} chương trình
                </span>
              </div>

              <!-- Thanh tìm kiếm & lọc trạng thái -->
              <div class="row g-2 mb-3">
                <div class="col-sm-8">
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-search"></i>
                    </span>
                    <input
                      v-model="searchQuery"
                      type="text"
                      class="form-control bg-light border-start-0 ps-0"
                      placeholder="Tên hoặc mã khuyến mãi"
                      @input="onSearchChange"
                    />
                  </div>
                </div>
                <div class="col-sm-4">
                  <select v-model="statusFilter" class="form-select bg-light" @change="onStatusFilterChange">
                    <option value="Mọi trạng thái">Mọi trạng thái</option>
                    <option value="Đang chạy">Đang chạy</option>
                    <option value="Sắp chạy">Sắp chạy</option>
                    <option value="Hết hạn">Hết hạn</option>
                    <option value="Tạm dừng">Tạm dừng</option>
                  </select>
                </div>
              </div>

              <!-- Tabs trạng thái -->
              <div class="status-tabs-nav d-flex border-bottom gap-4 mb-3">
                <button
                  class="tab-link-btn"
                  :class="{ active: currentTab === 'tatCa' }"
                  @click="doiTab('tatCa')"
                >
                  Tất cả ({{ thongKe.tatCa || 0 }})
                </button>
                <button
                  class="tab-link-btn"
                  :class="{ active: currentTab === 'dangChay' }"
                  @click="doiTab('dangChay')"
                >
                  Đang chạy ({{ thongKe.dangChay || 0 }})
                </button>
                <button
                  class="tab-link-btn"
                  :class="{ active: currentTab === 'sapChay' }"
                  @click="doiTab('sapChay')"
                >
                  Sắp chạy ({{ thongKe.sapChay || 0 }})
                </button>
                <button
                  class="tab-link-btn"
                  :class="{ active: currentTab === 'hetHan' }"
                  @click="doiTab('hetHan')"
                >
                  Hết hạn ({{ thongKe.hetHan || 0 }})
                </button>
              </div>

              <!-- Bảng danh sách chương trình -->
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-promo-table">
                  <thead>
                    <tr class="text-muted small text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                      <th class="ps-3 border-0 py-2.5">MÃ / CHƯƠNG TRÌNH</th>
                      <th class="border-0 py-2.5">MỨC GIẢM</th>
                      <th class="border-0 py-2.5">THỜI HẠN</th>
                      <th class="text-end pe-3 border-0 py-2.5">LƯỢT / TRẠNG THÁI</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="loadingList">
                      <td colspan="4" class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-teal me-2" role="status"></div>
                        Đang tải danh sách khuyến mãi...
                      </td>
                    </tr>
                    <tr v-else-if="danhSachFiltered.length === 0">
                      <td colspan="4" class="text-center py-4 text-muted small">
                        Không có chương trình khuyến mãi nào phù hợp với bộ lọc.
                      </td>
                    </tr>
                    <tr
                      v-for="item in danhSachFiltered"
                      :key="item.id"
                      class="promo-row cursor-pointer"
                      :class="{ 'row-selected': selectedPromo && selectedPromo.id === item.id }"
                      @click="chonKhuyenMai(item)"
                    >
                      <td class="ps-3 py-3">
                        <div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">
                          {{ item.maKhuyenMai }}
                        </div>
                        <div class="text-muted small">{{ item.tenKhuyenMai }}</div>
                      </td>
                      <td class="py-3">
                        <div class="fw-semibold text-dark small">{{ item.mucGiamHienThi }}</div>
                        <div class="text-muted extra-small">
                          {{ item.loaiPhongNames && item.loaiPhongNames.length > 0 ? item.loaiPhongNames.join(', ') : 'Tất cả loại phòng' }}
                        </div>
                      </td>
                      <td class="py-3">
                        <div class="small text-dark">{{ item.thoiHanHienThi }}</div>
                      </td>
                      <td class="text-end pe-3 py-3">
                        <div class="small text-muted mb-0.5">{{ item.luotDungHienThi }}</div>
                        <span
                          class="badge rounded-pill fw-medium px-2 py-0.5"
                          :class="getBadgeClass(item.trangThai)"
                        >
                          {{ item.trangThai }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- CARD 2: XEM TRƯỚC CÁCH TÍNH (DƯỚI DANH SÁCH) -->
          <div class="card border rounded-4 shadow-xs bg-white">
            <div class="card-body p-4">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <h5 class="fw-bold text-dark mb-0 fs-6">
                  Xem trước cách tính ·
                  <span class="text-teal font-monospace">{{ selectedPromo ? selectedPromo.maKhuyenMai : 'CHƯA CHỌN' }}</span>
                </h5>
                <span class="badge bg-light text-muted border fw-normal extra-small">
                  Mô phỏng tức thời
                </span>
              </div>

              <p class="text-muted small mb-3">
                Ví dụ đủ điều kiện:
                <strong>{{ previewRoomName }}</strong> ·
                {{ previewRangeText }} ·
                <strong>{{ previewNights }} đêm</strong> ·
                Đặt trực tiếp.
              </p>

              <!-- Bộ chọn loại phòng & số đêm mô phỏng nhanh -->
              <div class="p-3 rounded-3 bg-light border mb-3">
                <div class="row g-2 align-items-center">
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted mb-1 fw-medium">Chọn loại phòng thử nghiệm:</label>
                    <select v-model="previewRoomId" class="form-select form-select-sm" @change="capNhatXemTruoc">
                      <option v-for="lp in danhSachLoaiPhong" :key="lp.id" :value="lp.id">
                        {{ lp.tenLoai }} ({{ formatMoney(lp.giaTieuChuan) }} đ/đêm)
                      </option>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <label class="form-label extra-small text-muted mb-1 fw-medium">Số đêm:</label>
                    <select v-model="previewNights" class="form-select form-select-sm" @change="capNhatXemTruoc">
                      <option :value="1">1 đêm</option>
                      <option :value="2">2 đêm</option>
                      <option :value="3">3 đêm</option>
                      <option :value="4">4 đêm</option>
                      <option :value="5">5 đêm</option>
                    </select>
                  </div>
                  <div class="col-sm-3 text-sm-end pt-sm-3">
                    <span v-if="isRoomEligible" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                      <i class="bi bi-check2-circle me-1"></i>Hợp lệ
                    </span>
                    <span v-else class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                      <i class="bi bi-x-circle me-1"></i>Không áp dụng
                    </span>
                  </div>
                </div>

                <!-- Cảnh báo nếu phòng không đủ điều kiện -->
                <div v-if="!isRoomEligible" class="alert alert-warning py-1.5 px-2 mt-2 mb-0 extra-small">
                  <i class="bi bi-exclamation-circle me-1"></i>
                  Mã <strong>{{ selectedPromo?.maKhuyenMai }}</strong> chỉ áp dụng cho:
                  <strong>{{ selectedPromo?.loaiPhongNames?.join(', ') || 'Tất cả loại phòng' }}</strong>.
                </div>
              </div>

              <!-- Chi tiết phép tính toán học -->
              <div class="calculation-lines font-monospace">
                <div class="d-flex justify-content-between py-1.5 text-muted small">
                  <span>Giá phòng gốc · {{ previewNights }} × {{ formatMoney(previewRoomPrice) }} đ</span>
                  <span class="text-dark fw-semibold">{{ formatMoney(previewTotalOrigin) }} đ</span>
                </div>
                <div class="d-flex justify-content-between py-1.5 text-muted small">
                  <span>
                    {{ selectedPromo?.loaiGiam === 'Phan_Tram' ? `${selectedPromo.giaTriGiam}% giá trị tiền phòng` : `Giảm cố định mỗi đơn` }}
                  </span>
                  <span class="text-danger fw-semibold">- {{ formatMoney(previewDiscount) }} đ</span>
                </div>
                <div class="border-top my-2"></div>
                <div class="d-flex justify-content-between py-2 align-items-center">
                  <span class="fw-bold text-dark fs-6 font-sans">Khách thanh toán dự kiến</span>
                  <span class="fw-bold text-teal fs-5">{{ formatMoney(previewFinalTotal) }} đ</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ==================== CỘT PHẢI (CHI TIẾT CHƯƠNG TRÌNH & FORM) ==================== -->
        <div class="col-lg-5">
          <div class="card border rounded-4 shadow-xs bg-white sticky-top" style="top: 96px; z-index: 10;">
            <div class="card-body p-4">
              <!-- Header Chi tiết chương trình kèm Status Badge -->
              <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <h5 class="fw-bold text-dark mb-0 fs-6">
                    {{ isCreatingNew ? 'Tạo chương trình khuyến mãi' : 'Chi tiết chương trình' }}
                  </h5>
                </div>

                <!-- Pill trạng thái có chấm tròn -->
                <div class="d-flex align-items-center gap-2">
                  <span
                    class="status-dot-pill"
                    :class="getDotPillClass(form.trangThai)"
                  >
                    <span class="dot"></span>
                    <span>{{ form.trangThai }}</span>
                  </span>

                  <!-- Nút toggle Bật/Tắt trạng thái nhanh -->
                  <button
                    v-if="!isCreatingNew"
                    class="btn btn-sm btn-outline-secondary py-0.5 px-2 extra-small rounded-pill"
                    :title="form.trangThai === 'Tạm dừng' ? 'Kích hoạt lại' : 'Tạm dừng áp dụng'"
                    @click="toggleActiveStatus"
                  >
                    {{ form.trangThai === 'Tạm dừng' ? 'Kích hoạt' : 'Tạm dừng' }}
                  </button>
                </div>
              </div>

              <!-- Form nhập liệu -->
              <form @submit.prevent="luuChuongTrinh">
                <!-- Hàng 1: Mã ưu đãi & Tên chương trình -->
                <div class="row g-2 mb-3">
                  <div class="col-sm-5">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Mã ưu đãi *</label>
                    <input
                      v-model="form.maKhuyenMai"
                      type="text"
                      class="form-control form-control-sm text-uppercase font-monospace fw-bold"
                      placeholder="VD: ANNHIEN10"
                      required
                    />
                  </div>
                  <div class="col-sm-7">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Tên chương trình *</label>
                    <input
                      v-model="form.tenKhuyenMai"
                      type="text"
                      class="form-control form-control-sm"
                      placeholder="VD: Kỳ nghỉ an nhiên"
                      required
                    />
                  </div>
                </div>

                <!-- Hàng 2: Loại giảm & Giá trị -->
                <div class="row g-2 mb-3">
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Loại giảm *</label>
                    <select v-model="form.loaiGiam" class="form-select form-select-sm" required>
                      <option value="Phan_Tram">Phần trăm (%)</option>
                      <option value="So_Tien">Số tiền cố định (đ)</option>
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">
                      {{ form.loaiGiam === 'Phan_Tram' ? 'Tỷ lệ giảm (%) *' : 'Số tiền giảm (đ) *' }}
                    </label>
                    <input
                      v-model.number="form.giaTriGiam"
                      type="number"
                      min="1"
                      class="form-control form-control-sm fw-semibold"
                      :placeholder="form.loaiGiam === 'Phan_Tram' ? 'VD: 10' : 'VD: 100000'"
                      required
                    />
                  </div>
                </div>

                <!-- Hàng 3: Giảm tối đa & Giá trị đặt tối thiểu -->
                <div class="row g-2 mb-3">
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">
                      Giảm tối đa (đ)
                      <span class="text-muted fw-normal">(để trống nếu không giới hạn)</span>
                    </label>
                    <input
                      v-model.number="form.giamToiDa"
                      type="number"
                      min="0"
                      step="10000"
                      class="form-control form-control-sm"
                      placeholder="VD: 300000"
                    />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">
                      Giá trị đặt tối thiểu (đ)
                    </label>
                    <input
                      v-model.number="form.giaTriDatToiThieu"
                      type="number"
                      min="0"
                      step="10000"
                      class="form-control form-control-sm"
                      placeholder="VD: 1500000"
                    />
                  </div>
                </div>

                <!-- Hàng 4: Ngày bắt đầu & Kết thúc -->
                <div class="row g-2 mb-3">
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Bắt đầu *</label>
                    <input
                      v-model="form.ngayBatDau"
                      type="datetime-local"
                      class="form-control form-control-sm"
                      required
                    />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Kết thúc *</label>
                    <input
                      v-model="form.ngayKetThuc"
                      type="datetime-local"
                      class="form-control form-control-sm"
                      required
                    />
                  </div>
                </div>

                <!-- Hàng 5: Ngày lưu trú được áp dụng & Số đêm tối thiểu -->
                <div class="row g-2 mb-3">
                  <div class="col-sm-8">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">
                      Ngày lưu trú được áp dụng
                    </label>
                    <input
                      v-model="form.ngayLuuTruApDung"
                      type="text"
                      class="form-control form-control-sm"
                      placeholder="VD: 10/11–15/12/2026 · Tối thiểu 2 đêm"
                    />
                  </div>
                  <div class="col-sm-4">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Tối thiểu số đêm</label>
                    <input
                      v-model.number="form.soDemToiThieu"
                      type="number"
                      min="1"
                      class="form-control form-control-sm"
                      placeholder="1"
                    />
                  </div>
                </div>

                <!-- Hàng 6: Loại phòng áp dụng (Lấy trực tiếp từ Quản lý loại phòng) -->
                <div class="mb-3">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label extra-small text-muted fw-semibold mb-0">
                      Loại phòng được áp dụng *
                    </label>
                    <button
                      type="button"
                      class="btn btn-link p-0 text-teal extra-small text-decoration-none"
                      @click="toggleChonTatCaPhong"
                    >
                      {{ form.loaiPhongApDung.length === danhSachLoaiPhong.length ? 'Bỏ chọn tất cả' : 'Chọn tất cả phòng' }}
                    </button>
                  </div>

                  <!-- Danh sách Checkboxes loại phòng thực tế -->
                  <div class="border rounded-3 p-2.5 bg-light" style="max-height: 140px; overflow-y: auto;">
                    <div class="row g-1">
                      <div
                        v-for="lp in danhSachLoaiPhong"
                        :key="lp.id"
                        class="col-12"
                      >
                        <div class="form-check form-check-inline py-0.5">
                          <input
                            :id="'lp-check-' + lp.id"
                            v-model="form.loaiPhongApDung"
                            class="form-check-input"
                            type="checkbox"
                            :value="lp.id"
                          />
                          <label :for="'lp-check-' + lp.id" class="form-check-label small text-dark cursor-pointer">
                            <strong>{{ lp.tenLoai }}</strong>
                            <span class="text-muted extra-small ms-1">({{ formatMoney(lp.giaTieuChuan) }} đ)</span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-text extra-small text-muted mt-1">
                    * Được liên kết từ danh mục loại phòng thực tế. Nếu không chọn phòng nào, hệ thống mặc định áp dụng cho tất cả loại phòng.
                  </div>
                </div>

                <!-- Hàng 7: Giới hạn toàn chương trình & Mỗi khách hàng -->
                <div class="row g-2 mb-4">
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Giới hạn toàn CT (lượt)</label>
                    <input
                      v-model.number="form.gioiHanToanChuongTrinh"
                      type="number"
                      min="1"
                      class="form-control form-control-sm"
                      placeholder="100"
                    />
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label extra-small text-muted fw-semibold mb-1">Mỗi khách hàng (lượt)</label>
                    <input
                      v-model.number="form.moiKhachHang"
                      type="number"
                      min="1"
                      class="form-control form-control-sm"
                      placeholder="1"
                    />
                  </div>
                </div>

                <!-- Nút thao tác Form -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                  <div class="d-flex align-items-center gap-2">
                    <button
                      v-if="!isCreatingNew"
                      type="button"
                      class="btn btn-outline-danger btn-sm px-3"
                      @click="xoaChuongTrinhHienTai"
                    >
                      <i class="bi bi-trash3 me-1"></i>Xóa
                    </button>
                    <button
                      type="button"
                      class="btn btn-light btn-sm text-secondary px-3"
                      @click="datLaiForm"
                    >
                      Hủy bỏ
                    </button>
                  </div>

                  <button
                    type="submit"
                    class="btn btn-teal-primary btn-sm px-4 fw-semibold text-white shadow-xs"
                    :disabled="savingForm"
                  >
                    <span v-if="savingForm" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="bi bi-check2-circle me-1"></i>
                    {{ isCreatingNew ? 'Tạo khuyến mãi' : 'Lưu thay đổi' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- ==================== MODAL / DRAWER THỬ NGHIỆM ĐẶT PHÒNG & HÓA ĐƠN KHÁCH HÀNG ==================== -->
      <div
        v-if="showTestModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background-color: rgba(0, 0, 0, 0.5); z-index: 1050;"
      >
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom p-3.5">
              <div class="d-flex align-items-center gap-2">
                <div class="badge-icon-teal p-2 rounded-3 text-teal bg-teal-subtle">
                  <i class="bi bi-patch-check-fill fs-5"></i>
                </div>
                <div>
                  <h5 class="modal-title fw-bold text-dark fs-6 mb-0">
                    Thử nghiệm Khách hàng áp dụng mã & Xuất hóa đơn
                  </h5>
                  <div class="text-muted extra-small">
                    Kiểm tra xác thực điều kiện & tính tiền hóa đơn thực tế qua Backend Laravel
                  </div>
                </div>
              </div>
              <button type="button" class="btn-close" @click="showTestModal = false"></button>
            </div>

            <div class="modal-body p-4">
              <!-- Hướng dẫn 5 trường hợp kiểm thử -->
              <div class="alert alert-info py-2 px-3 mb-3 small">
                <strong>Gợi ý kiểm thử nhanh các trường hợp theo yêu cầu:</strong>
                <ul class="mb-0 ps-3 mt-1 extra-small">
                  <li><strong>ANNHIEN10 + Deluxe:</strong> Hợp lệ, giảm 10% (tiền phòng gốc 2.700.000 đ giảm 270.000 đ còn 2.430.000 đ).</li>
                  <li><strong>ANNHIEN10 + Family Suite:</strong> Bị từ chối vì không nằm trong danh sách phòng áp dụng.</li>
                  <li><strong>THANG10:</strong> Bị từ chối vì chương trình đã hết hạn vào 31/10/2026.</li>
                  <li><strong>Mã không tồn tại (VD: SAI999):</strong> Báo lỗi mã không tồn tại trên hệ thống.</li>
                </ul>
              </div>

              <!-- Form nhập thử nghiệm -->
              <div class="row g-3 mb-3">
                <div class="col-md-5">
                  <label class="form-label extra-small text-muted fw-semibold">Mã khuyến mãi khách nhập:</label>
                  <div class="input-group input-group-sm">
                    <input
                      v-model="testCustomer.maKhuyenMai"
                      type="text"
                      class="form-control font-monospace text-uppercase fw-bold"
                      placeholder="VD: ANNHIEN10"
                    />
                    <button class="btn btn-outline-secondary" type="button" @click="chonNhanhMa('ANNHIEN10')">
                      ANNHIEN10
                    </button>
                    <button class="btn btn-outline-secondary" type="button" @click="chonNhanhMa('THANG10')">
                      THANG10
                    </button>
                  </div>
                </div>

                <div class="col-md-4">
                  <label class="form-label extra-small text-muted fw-semibold">Loại phòng khách chọn:</label>
                  <select v-model="testCustomer.maLoaiPhong" class="form-select form-select-sm">
                    <option v-for="lp in danhSachLoaiPhong" :key="lp.id" :value="lp.id">
                      {{ lp.tenLoai }} ({{ formatMoney(lp.giaTieuChuan) }} đ)
                    </option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label extra-small text-muted fw-semibold">Số đêm lưu trú:</label>
                  <input
                    v-model.number="testCustomer.soDem"
                    type="number"
                    min="1"
                    class="form-control form-control-sm"
                  />
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mb-4">
                <button
                  type="button"
                  class="btn btn-teal-primary btn-sm px-4 fw-semibold text-white shadow-xs"
                  :disabled="checkingCoupon"
                  @click="kiemTraMaKhachHang"
                >
                  <span v-if="checkingCoupon" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-shield-check me-1"></i>
                  Kiểm tra mã & Tính toán
                </button>
              </div>

              <!-- Kết quả xác thực từ Backend -->
              <div v-if="testResult" class="p-3 rounded-4 border" :class="testResult.hopLe ? 'bg-success-subtle border-success' : 'bg-danger-subtle border-danger'">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <i :class="testResult.hopLe ? 'bi bi-check-circle-fill text-success fs-5' : 'bi bi-x-circle-fill text-danger fs-5'"></i>
                  <span class="fw-bold" :class="testResult.hopLe ? 'text-success' : 'text-danger'">
                    {{ testResult.hopLe ? 'MÃ HỢP LỆ - ÁP DỤNG THÀNH CÔNG' : 'MÃ KHÔNG HỢP LỆ' }}
                  </span>
                </div>
                <div class="small text-dark mb-3">
                  {{ testResult.message }}
                </div>

                <!-- Bảng chi tiết hóa đơn nếu hợp lệ -->
                <div v-if="testResult.hopLe && testResult.chiTietTinh" class="bg-white p-3 rounded-3 border">
                  <div class="fw-bold small text-dark mb-2 pb-1 border-bottom">
                    Chi tiết hóa đơn dự kiến:
                  </div>
                  <div class="d-flex justify-content-between small text-muted py-1">
                    <span>Phòng: {{ testResult.chiTietTinh.tenLoaiPhong }} ({{ testResult.chiTietTinh.soDem }} đêm × {{ testResult.chiTietTinh.giaMotDemDinhDang }})</span>
                    <span class="fw-semibold text-dark">{{ testResult.chiTietTinh.tongTienGocDinhDang }}</span>
                  </div>
                  <div class="d-flex justify-content-between small text-danger py-1">
                    <span>Chiết khấu mã [{{ testResult.khuyenMai.maKhuyenMai }}]</span>
                    <span class="fw-semibold">- {{ testResult.chiTietTinh.tienGiamDinhDang }}</span>
                  </div>
                  <div class="border-top my-2"></div>
                  <div class="d-flex justify-content-between align-items-center py-1">
                    <span class="fw-bold text-dark">Tổng khách phải trả:</span>
                    <span class="fw-bold fs-5 text-teal">{{ testResult.chiTietTinh.tongSauGiamDinhDang }}</span>
                  </div>

                  <!-- Nút tạo hóa đơn thực tế -->
                  <div class="mt-3 text-end">
                    <button
                      type="button"
                      class="btn btn-success btn-sm px-3 fw-medium"
                      :disabled="payingInvoice"
                      @click="thanhToanHoaDonMau"
                    >
                      <span v-if="payingInvoice" class="spinner-border spinner-border-sm me-1"></span>
                      <i v-else class="bi bi-receipt me-1"></i>
                      Thanh toán thực tế & Xuất hóa đơn
                    </button>
                  </div>
                </div>
              </div>

              <!-- Thông báo hóa đơn đã tạo thành công -->
              <div v-if="invoiceResult" class="alert alert-success mt-3 py-2.5 px-3 small">
                <i class="bi bi-patch-check-fill text-success me-1"></i>
                {{ invoiceResult.message }}
              </div>
            </div>

            <div class="modal-footer bg-light p-3 border-top">
              <button type="button" class="btn btn-secondary btn-sm" @click="showTestModal = false">
                Đóng
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import khuyenMaiService from './khuyenMaiService'

export default {
  name: 'QuanLyKhuyenMai',
  data() {
    return {
      // Danh sách & Bộ lọc
      danhSach: [],
      danhSachLoaiPhong: [],
      searchQuery: '',
      statusFilter: 'Mọi trạng thái',
      currentTab: 'tatCa',
      loadingList: false,
      savingForm: false,

      // Thống kê đếm số lượng
      thongKe: {
        tatCa: 0,
        dangChay: 0,
        sapChay: 0,
        hetHan: 0,
        tamDung: 0,
      },

      // Chương trình đang chọn
      selectedPromo: null,
      isCreatingNew: false,

      // Dữ liệu Form
      form: {
        id: null,
        maKhuyenMai: '',
        tenKhuyenMai: '',
        loaiGiam: 'Phan_Tram',
        giaTriGiam: 10,
        giamToiDa: 300000,
        giaTriDatToiThieu: 1500000,
        ngayBatDau: '',
        ngayKetThuc: '',
        ngayLuuTruApDung: '',
        soDemToiThieu: 1,
        loaiPhongApDung: [],
        gioiHanToanChuongTrinh: 100,
        moiKhachHang: 1,
        trangThai: 'Đang chạy',
        moTa: '',
      },

      // Dữ liệu Xem trước cách tính
      previewRoomId: 2, // Mặc định Deluxe (900.000 đ)
      previewNights: 3,
      previewRangeText: '20–23/11/2026',

      // Test Modal cho Khách hàng
      showTestModal: false,
      checkingCoupon: false,
      payingInvoice: false,
      testCustomer: {
        maKhuyenMai: 'ANNHIEN10',
        maLoaiPhong: 2,
        soDem: 3,
      },
      testResult: null,
      invoiceResult: null,

      // Toast thông báo
      toast: {
        show: false,
        type: 'success',
        message: '',
      },
    }
  },
  computed: {
    // Ngày hiện tại định dạng Việt Nam
    ngayHienTai() {
      const now = new Date()
      const days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
      const dayName = days[now.getDay()]
      const d = String(now.getDate()).padStart(2, '0')
      const m = String(now.getMonth() + 1).padStart(2, '0')
      const y = now.getFullYear()
      return `${dayName}, ${d} tháng ${m}, ${y}`
    },

    // Lọc danh sách theo Tab, Search và Dropdown
    danhSachFiltered() {
      let list = [...this.danhSach]

      // 1. Lọc theo tab
      if (this.currentTab === 'dangChay') {
        list = list.filter((p) => p.trangThai === 'Đang chạy')
      } else if (this.currentTab === 'sapChay') {
        list = list.filter((p) => p.trangThai === 'Sắp chạy')
      } else if (this.currentTab === 'hetHan') {
        list = list.filter((p) => p.trangThai === 'Hết hạn')
      }

      // 2. Lọc theo dropdown trạng thái
      if (this.statusFilter && this.statusFilter !== 'Mọi trạng thái') {
        list = list.filter((p) => p.trangThai === this.statusFilter)
      }

      // 3. Lọc theo ô tìm kiếm
      if (this.searchQuery && this.searchQuery.trim() !== '') {
        const q = this.searchQuery.trim().toLowerCase()
        list = list.filter(
          (p) =>
            p.maKhuyenMai.toLowerCase().includes(q) ||
            p.tenKhuyenMai.toLowerCase().includes(q)
        )
      }

      return list
    },

    // Thông tin phòng xem trước
    selectedPreviewRoom() {
      return (
        this.danhSachLoaiPhong.find((lp) => lp.id === this.previewRoomId) ||
        this.danhSachLoaiPhong[0] || { id: 2, tenLoai: 'Deluxe', giaTieuChuan: 900000 }
      )
    },
    previewRoomName() {
      return this.selectedPreviewRoom.tenLoai
    },
    previewRoomPrice() {
      return Number(this.selectedPreviewRoom.giaTieuChuan || 900000)
    },
    previewTotalOrigin() {
      return this.previewRoomPrice * this.previewNights
    },
    isRoomEligible() {
      if (!this.selectedPromo) return true
      const rooms = this.selectedPromo.loaiPhongApDung
      if (!rooms || rooms.length === 0) return true
      return rooms.includes(Number(this.previewRoomId))
    },
    previewDiscount() {
      if (!this.selectedPromo || !this.isRoomEligible) return 0
      const promo = this.selectedPromo
      if (promo.trangThai !== 'Đang chạy') return 0

      let discount = 0
      if (promo.loaiGiam === 'Phan_Tram') {
        const calculated = (this.previewTotalOrigin * Number(promo.giaTriGiam)) / 100
        const max = Number(promo.giamToiDa || 0)
        discount = max > 0 ? Math.min(calculated, max) : calculated
      } else {
        discount = Math.min(Number(promo.giaTriGiam || 0), this.previewTotalOrigin)
      }
      return discount
    },
    previewFinalTotal() {
      return Math.max(0, this.previewTotalOrigin - this.previewDiscount)
    },
  },
  async mounted() {
    await this.taiDanhSachLoaiPhong()
    await this.taiDanhSachKhuyenMai()
  },
  methods: {
    formatMoney(val) {
      if (val === null || val === undefined) return '0'
      return Number(val).toLocaleString('vi-VN')
    },

    quayLai() {
      if (window.history.length > 1) {
        this.$router.back()
      } else {
        this.$router.push('/')
      }
    },

    // 1. Tải danh mục loại phòng từ database
    async taiDanhSachLoaiPhong() {
      try {
        const res = await khuyenMaiService.getDanhSachLoaiPhong()
        if (res && res.data) {
          this.danhSachLoaiPhong = res.data
          if (this.danhSachLoaiPhong.length > 0) {
            // Mặc định chọn phòng Deluxe (ID 2) nếu có
            const deluxe = this.danhSachLoaiPhong.find((p) => p.id === 2 || p.tenLoai.includes('Deluxe'))
            if (deluxe) {
              this.previewRoomId = deluxe.id
            } else {
              this.previewRoomId = this.danhSachLoaiPhong[0].id
            }
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách loại phòng:', err)
      }
    },

    // 2. Tải danh sách khuyến mãi từ Backend
    async taiDanhSachKhuyenMai() {
      this.loadingList = true
      try {
        const res = await khuyenMaiService.getDanhSach()
        if (res && res.status === 'success') {
          this.danhSach = res.data || []
          this.thongKe = res.thongKe || {
            tatCa: this.danhSach.length,
            dangChay: 0,
            sapChay: 0,
            hetHan: 0,
            tamDung: 0,
          }

          // Mặc định chọn dòng đầu tiên nếu chưa chọn
          if (this.danhSach.length > 0 && !this.selectedPromo) {
            this.chonKhuyenMai(this.danhSach[0])
          }
        }
      } catch (err) {
        this.hienToast('error', 'Không thể kết nối đến máy chủ lấy danh sách khuyến mãi.')
      } finally {
        this.loadingList = false
      }
    },

    // 3. Chọn khuyến mãi để xem chi tiết
    chonKhuyenMai(promo) {
      this.selectedPromo = promo
      this.isCreatingNew = false

      // Điền dữ liệu vào form
      this.form = {
        id: promo.id,
        maKhuyenMai: promo.maKhuyenMai,
        tenKhuyenMai: promo.tenKhuyenMai,
        loaiGiam: promo.loaiGiam || 'Phan_Tram',
        giaTriGiam: promo.giaTriGiam,
        giamToiDa: promo.giamToiDa || 0,
        giaTriDatToiThieu: promo.giaTriDatToiThieu || 0,
        ngayBatDau: this.formatDateInput(promo.ngayBatDau),
        ngayKetThuc: this.formatDateInput(promo.ngayKetThuc),
        ngayLuuTruApDung: promo.ngayLuuTruApDung || '',
        soDemToiThieu: promo.soDemToiThieu || 1,
        loaiPhongApDung: Array.isArray(promo.loaiPhongApDung) ? [...promo.loaiPhongApDung] : [],
        gioiHanToanChuongTrinh: promo.gioiHanToanChuongTrinh || 100,
        moiKhachHang: promo.moiKhachHang || 1,
        trangThai: promo.trangThai || 'Đang chạy',
        moTa: promo.moTa || '',
      }

      // Cập nhật lại xem trước
      this.capNhatXemTruoc()
    },

    formatDateInput(dateStr) {
      if (!dateStr) return ''
      const d = new Date(dateStr)
      if (isNaN(d.getTime())) return ''
      const y = d.getFullYear()
      const m = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      const h = String(d.getHours()).padStart(2, '0')
      const min = String(d.getMinutes()).padStart(2, '0')
      return `${y}-${m}-${day}T${h}:${min}`
    },

    // 4. Chuyển sang form tạo mới
    chuyenSangTaoMoi() {
      this.isCreatingNew = true
      this.selectedPromo = null

      const now = new Date()
      const nextMonth = new Date(now)
      nextMonth.setDate(now.getDate() + 30)

      this.form = {
        id: null,
        maKhuyenMai: '',
        tenKhuyenMai: '',
        loaiGiam: 'Phan_Tram',
        giaTriGiam: 10,
        giamToiDa: 300000,
        giaTriDatToiThieu: 1000000,
        ngayBatDau: this.formatDateInput(now),
        ngayKetThuc: this.formatDateInput(nextMonth),
        ngayLuuTruApDung: 'Tối thiểu 1 đêm',
        soDemToiThieu: 1,
        loaiPhongApDung: this.danhSachLoaiPhong.map((p) => p.id),
        gioiHanToanChuongTrinh: 100,
        moiKhachHang: 1,
        trangThai: 'Đang chạy',
        moTa: '',
      }
    },

    // 5. Chọn / Bỏ chọn tất cả phòng
    toggleChonTatCaPhong() {
      if (this.form.loaiPhongApDung.length === this.danhSachLoaiPhong.length) {
        this.form.loaiPhongApDung = []
      } else {
        this.form.loaiPhongApDung = this.danhSachLoaiPhong.map((lp) => lp.id)
      }
    },

    // 6. Lưu chương trình (Tạo mới hoặc Cập nhật)
    async luuChuongTrinh() {
      if (!this.form.maKhuyenMai || !this.form.tenKhuyenMai) {
        this.hienToast('error', 'Vui lòng điền đầy đủ mã và tên chương trình.')
        return
      }

      this.savingForm = true
      try {
        let res
        if (this.isCreatingNew) {
          res = await khuyenMaiService.taoKhuyenMai(this.form)
        } else {
          res = await khuyenMaiService.capNhatKhuyenMai(this.form.id, this.form)
        }

        if (res && res.status === 'success') {
          this.hienToast('success', res.message)
          await this.taiDanhSachKhuyenMai()

          // Chọn lại item vừa lưu
          if (res.data) {
            const updated = this.danhSach.find((p) => p.id === res.data.id)
            if (updated) {
              this.chonKhuyenMai(updated)
            }
          }
        }
      } catch (err) {
        const msg = err.response?.data?.message || 'Có lỗi xảy ra khi lưu khuyến mãi.'
        this.hienToast('error', msg)
      } finally {
        this.savingForm = false
      }
    },

    // 7. Bật / Tắt trạng thái khuyến mãi (Tạm dừng <-> Kích hoạt)
    async toggleActiveStatus() {
      if (!this.selectedPromo) return
      try {
        const res = await khuyenMaiService.toggleTrangThai(this.selectedPromo.id)
        if (res && res.status === 'success') {
          this.hienToast('success', res.message)
          await this.taiDanhSachKhuyenMai()
          const updated = this.danhSach.find((p) => p.id === this.selectedPromo.id)
          if (updated) this.chonKhuyenMai(updated)
        }
      } catch (err) {
        this.hienToast('error', 'Không thể đổi trạng thái khuyến mãi.')
      }
    },

    // 8. Xóa chương trình
    async xoaChuongTrinhHienTai() {
      if (!this.selectedPromo) return
      if (!confirm(`Bạn có chắc chắn muốn xóa khuyến mãi '${this.selectedPromo.maKhuyenMai}'?`)) {
        return
      }

      try {
        const res = await khuyenMaiService.xoaKhuyenMai(this.selectedPromo.id)
        if (res && res.status === 'success') {
          this.hienToast('success', res.message)
          this.selectedPromo = null
          await this.taiDanhSachKhuyenMai()
        }
      } catch (err) {
        this.hienToast('error', 'Lỗi khi xóa khuyến mãi.')
      }
    },

    datLaiForm() {
      if (this.selectedPromo) {
        this.chonKhuyenMai(this.selectedPromo)
      } else if (this.danhSach.length > 0) {
        this.chonKhuyenMai(this.danhSach[0])
      }
    },

    doiTab(tabKey) {
      this.currentTab = tabKey
    },

    onSearchChange() {},
    onStatusFilterChange() {},

    capNhatXemTruoc() {
      // Tự động tính toán lại nhờ computed properties
    },

    // 9. Thử nghiệm khách hàng áp dụng mã
    moModalThuNghiem() {
      this.showTestModal = true
      this.testResult = null
      this.invoiceResult = null
      if (this.selectedPromo) {
        this.testCustomer.maKhuyenMai = this.selectedPromo.maKhuyenMai
      }
    },

    chonNhanhMa(code) {
      this.testCustomer.maKhuyenMai = code
    },

    async kiemTraMaKhachHang() {
      this.checkingCoupon = true
      this.testResult = null
      this.invoiceResult = null
      try {
        const res = await khuyenMaiService.kiemTraMa({
          maKhuyenMai: this.testCustomer.maKhuyenMai,
          maLoaiPhong: this.testCustomer.maLoaiPhong,
          soDem: this.testCustomer.soDem,
        })
        this.testResult = res
      } catch (err) {
        const resData = err.response?.data
        if (resData) {
          this.testResult = {
            hopLe: false,
            message: resData.message || 'Mã không hợp lệ.',
          }
        } else {
          this.testResult = {
            hopLe: false,
            message: 'Không thể kết nối đến máy chủ để xác thực.',
          }
        }
      } finally {
        this.checkingCoupon = false
      }
    },

    // 10. Thanh toán hóa đơn mẫu trong modal thử nghiệm
    async thanhToanHoaDonMau() {
      this.payingInvoice = true
      this.invoiceResult = null
      try {
        const res = await khuyenMaiService.thanhToanHoaDon({
          maKhuyenMai: this.testCustomer.maKhuyenMai,
          maLoaiPhong: this.testCustomer.maLoaiPhong,
          soDem: this.testCustomer.soDem,
          maLuuTru: 1,
        })
        if (res && res.status === 'success') {
          this.invoiceResult = res
          this.hienToast('success', res.message)
          // Cập nhật lại số lượt dùng trên danh sách
          await this.taiDanhSachKhuyenMai()
        }
      } catch (err) {
        this.hienToast('error', err.response?.data?.message || 'Lỗi khi thanh toán hóa đơn.')
      } finally {
        this.payingInvoice = false
      }
    },

    // 11. Helper Style Badges
    getBadgeClass(status) {
      switch (status) {
        case 'Đang chạy':
          return 'bg-success-subtle text-success border border-success-subtle'
        case 'Sắp chạy':
          return 'bg-info-subtle text-info border border-info-subtle'
        case 'Hết hạn':
          return 'bg-secondary-subtle text-secondary border border-secondary-subtle'
        case 'Tạm dừng':
          return 'bg-warning-subtle text-warning border border-warning-subtle'
        default:
          return 'bg-light text-dark border'
      }
    },

    getDotPillClass(status) {
      switch (status) {
        case 'Đang chạy':
          return 'pill-active'
        case 'Sắp chạy':
          return 'pill-upcoming'
        case 'Hết hạn':
          return 'pill-expired'
        case 'Tạm dừng':
          return 'pill-paused'
        default:
          return 'pill-default'
      }
    },

    hienToast(type, message) {
      this.toast = {
        show: true,
        type,
        message,
      }
      setTimeout(() => {
        if (this.toast.show) {
          this.toast.show = false
        }
      }, 4500)
    },
  },
}
</script>

<style scoped>
/* ==========================================================================
   STYLE QUẢN LÝ KHUYẾN MÃI - GIAO DIỆN CHUẨN XANH TEAL ĐẬM
   ========================================================================== */

.khuyen-mai-page {
  background-color: #f8faf9;
  min-height: calc(100vh - 76px);
  color: #172620;
}

.text-teal {
  color: #1e5e49 !important;
}

.bg-teal-primary {
  background-color: #1e5e49 !important;
}

.btn-teal-primary {
  background-color: #1e5e49;
  border-color: #1e5e49;
  transition: all 0.2s ease;
}

.btn-teal-primary:hover,
.btn-teal-primary:focus {
  background-color: #164636;
  border-color: #164636;
  color: #ffffff;
}

.user-avatar-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-color: #e2e8f0;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.88rem;
  letter-spacing: 0.5px;
}

/* Tabs trạng thái */
.status-tabs-nav {
  overflow-x: auto;
  white-space: nowrap;
}

.tab-link-btn {
  background: none;
  border: none;
  padding: 8px 0;
  font-size: 0.9rem;
  font-weight: 500;
  color: #64748b;
  position: relative;
  cursor: pointer;
  transition: color 0.2s ease;
}

.tab-link-btn:hover {
  color: #1e5e49;
}

.tab-link-btn.active {
  color: #1e5e49;
  font-weight: 700;
}

.tab-link-btn.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2.5px;
  background-color: #1e5e49;
  border-radius: 2px 2px 0 0;
}

/* Bảng Khuyến mãi */
.custom-promo-table th {
  background-color: #fafbfc;
  color: #64748b;
  font-size: 0.75rem;
}

.promo-row {
  transition: all 0.15s ease;
  border-left: 3px solid transparent;
}

.promo-row:hover {
  background-color: #f1f7f4;
}

.promo-row.row-selected {
  background-color: #ebf5f0 !important;
  border-left-color: #1e5e49;
}

/* Dot Pill trạng thái */
.status-dot-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 9999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-dot-pill .dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
}

.pill-active {
  background-color: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}
.pill-active .dot {
  background-color: #10b981;
}

.pill-upcoming {
  background-color: #eff6ff;
  color: #1e40af;
  border: 1px solid #bfdbfe;
}
.pill-upcoming .dot {
  background-color: #3b82f6;
}

.pill-expired {
  background-color: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
}
.pill-expired .dot {
  background-color: #94a3b8;
}

.pill-paused {
  background-color: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
}
.pill-paused .dot {
  background-color: #f59e0b;
}

.extra-small {
  font-size: 0.75rem;
}

.shadow-xs {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.cursor-pointer {
  cursor: pointer;
}
</style>
