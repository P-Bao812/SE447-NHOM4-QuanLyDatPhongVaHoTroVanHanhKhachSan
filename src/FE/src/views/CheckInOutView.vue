<template>
  <div class="check-in-out-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- 1. Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/receptionist/front-desk" class="text-decoration-none text-teal">
              Bàn trực Lễ tân
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Hỗ trợ nhận & trả phòng (UC09)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-arrow-left-right fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Hỗ Trợ Nhận Phòng & Trả Phòng (Check-in / Check-out)</h2>
              <p class="text-muted mb-0 small">
                Tra cứu đơn đặt, đối chiếu khách & phòng, tạo đợt lưu trú, tính phụ thu sớm/trễ và xuất phiếu nhận/trả phòng (UC09).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isAuthorized ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isAuthorized ? 'bi bi-person-badge-fill me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Nhân viên' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isAuthorized ? 'Được cấp quyền thực hiện Check-in / Check-out' : 'Không có quyền thực hiện nghiệp vụ này' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Quick Test & Switch Account Toolbar (Hỗ trợ kiểm thử nhanh) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi tài khoản:</span>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'letan01' }"
                :disabled="isLoading"
                @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
              >
                <i class="bi bi-person-badge me-1"></i> Lễ tân (letan01)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'admin' }"
                :disabled="isLoading"
                @click="quickSwitchUser('admin', '123456', 'Quản lý')"
              >
                <i class="bi bi-shield-lock me-1"></i> Quản lý (admin)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger rounded-3"
                :class="{ 'btn-danger text-white': currentUser.tenDangNhap === 'buongphong01' }"
                :disabled="isLoading"
                @click="quickSwitchUser('buongphong01', '123456', 'Buồng phòng')"
              >
                <i class="bi bi-slash-circle me-1"></i> Buồng phòng (Thử 403)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-warning rounded-3"
                :class="{ 'btn-warning text-dark': currentUser.tenDangNhap === 'nguyenvanminh' }"
                :disabled="isLoading"
                @click="quickSwitchUser('nguyenvanminh', '123456', 'Khách hàng')"
              >
                <i class="bi bi-person me-1"></i> Khách hàng (Thử 403)
              </button>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button
                type="button"
                class="btn btn-sm btn-outline-navy rounded-3 px-3"
                :disabled="isLoading"
                @click="fetchBookings"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
              <router-link
                to="/receptionist/invoices"
                class="btn btn-sm btn-outline-teal rounded-3 px-3 fw-semibold"
              >
                <i class="bi bi-receipt me-1"></i> Đến Trang Hóa Đơn (UC07)
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Alert Messages (Thông báo kết quả thao tác) -->
      <div v-if="alertMessage.text" class="alert alert-dismissible fade show rounded-3 shadow-sm mb-4" :class="alertMessage.typeClass" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i :class="alertMessage.iconClass" class="fs-5"></i>
          <div>
            <strong>{{ alertMessage.title }}:</strong> {{ alertMessage.text }}
          </div>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="clearAlert"></button>
      </div>

      <!-- 4. Thẻ KPI Thống Kê Nhanh Vận Hành -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Chờ nhận phòng</span>
                <h3 class="fw-bold text-teal mb-0 mt-1">{{ kpi.readyForCheckin }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Đã xác nhận / Chờ thanh toán</small>
              </div>
              <div class="kpi-icon bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-in-right fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Đang lưu trú</span>
                <h3 class="fw-bold text-primary mb-0 mt-1">{{ kpi.activeStay }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Khách đang ở phòng</small>
              </div>
              <div class="kpi-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-house-door-fill fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Đã trả phòng</span>
                <h3 class="fw-bold text-success mb-0 mt-1">{{ kpi.checkedOut }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Hoàn tất trả phòng</small>
              </div>
              <div class="kpi-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-right fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Tổng số đơn nạp</span>
                <h3 class="fw-bold text-navy mb-0 mt-1">{{ bookings.length }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Hiển thị theo bộ lọc</small>
              </div>
              <div class="kpi-icon bg-navy-subtle text-navy rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-journal-bookmark-fill fs-4"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Bộ lọc & Tìm kiếm nhanh -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="row g-2 align-items-center">
            <!-- Ô tìm kiếm từ khóa -->
            <div class="col-lg-4 col-md-6">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  v-model.trim="filterParams.tuKhoa"
                  type="text"
                  class="form-control border-start-0 ps-0"
                  placeholder="Mã đơn (#ĐP-..), Tên khách, CCCD, SĐT..."
                  @keyup.enter="handleSearch"
                />
                <button
                  v-if="filterParams.tuKhoa"
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="filterParams.tuKhoa = ''; handleSearch()"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
            </div>

            <!-- Lọc theo trạng thái -->
            <div class="col-lg-3 col-md-6">
              <select
                v-model="filterParams.trangThai"
                class="form-select form-select-sm"
                @change="handleSearch"
              >
                <option value="">Tất cả trạng thái đơn</option>
                <option value="ready_checkin">Sẵn sàng nhận phòng (Đã xác nhận / Chờ cọc)</option>
                <option value="Đang ở">Đang ở (Cần trả phòng)</option>
                <option value="Đã xác nhận">Đã xác nhận</option>
                <option value="Chờ thanh toán">Chờ thanh toán</option>
                <option value="Đã trả">Đã trả</option>
                <option value="Đã hoàn thành">Đã hoàn thành</option>
                <option value="Đã hủy">Đã hủy</option>
              </select>
            </div>

            <!-- Nút tìm kiếm & Đặt lại -->
            <div class="col-lg-5 col-md-12 d-flex justify-content-lg-end gap-2">
              <button
                type="button"
                class="btn btn-sm btn-navy text-white rounded-3 px-3 fw-semibold"
                :disabled="isLoading"
                @click="handleSearch"
              >
                <i class="bi bi-funnel-fill me-1"></i> Lọc dữ liệu
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3 px-3"
                :disabled="isLoading"
                @click="resetFilter"
              >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Danh sách Đơn đặt phòng & Chi tiết Phòng -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-check text-teal fs-5"></i>
            <h5 class="card-title fw-bold text-navy mb-0">Danh Sách Đơn Đặt Phòng Tiếp Nhận</h5>
            <span class="badge bg-light text-dark border ms-1">{{ filteredBookings.length }} đơn</span>
          </div>

          <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="d-inline-block rounded-circle bg-success me-1" style="width: 8px; height: 8px;"></span> Phòng Trống
            <span class="d-inline-block rounded-circle bg-danger ms-2 me-1" style="width: 8px; height: 8px;"></span> Đang ở
            <span class="d-inline-block rounded-circle bg-warning ms-2 me-1" style="width: 8px; height: 8px;"></span> Cần dọn dẹp
          </div>
        </div>

        <!-- Trạng thái Loading -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-teal" role="status">
            <span class="visually-hidden">Đang tải dữ liệu...</span>
          </div>
          <p class="text-muted small mt-2 mb-0">Đang tải danh sách đặt phòng & kiểm tra trạng thái phòng...</p>
        </div>

        <!-- Trạng thái Rỗng -->
        <div v-else-if="filteredBookings.length === 0" class="text-center py-5">
          <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
          <h6 class="text-secondary fw-bold">Không tìm thấy đơn đặt phòng nào</h6>
          <p class="text-muted small mb-3">Vui lòng thử tìm kiếm với từ khóa khác hoặc điều chỉnh lại bộ lọc.</p>
          <button type="button" class="btn btn-sm btn-outline-navy rounded-3 px-3" @click="resetFilter">
            Xóa bộ lọc tìm kiếm
          </button>
        </div>

        <!-- Bảng Dữ Liệu -->
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-navy small text-uppercase">
              <tr>
                <th scope="col" class="ps-4">Mã Đơn / Khách Hàng</th>
                <th scope="col">Phòng & Trạng Thái</th>
                <th scope="col">Lịch Nhận / Trả</th>
                <th scope="col" class="text-end">Tiền Cọc / Tổng</th>
                <th scope="col" class="text-center">Trạng Thái Đơn</th>
                <th scope="col" class="text-end pe-4">Thao Tác Nghiệp Vụ</th>
              </tr>
            </thead>
            <tbody class="border-top-0">
              <tr v-for="b in filteredBookings" :key="b.id">
                <!-- Cột 1: Mã đơn & Khách hàng -->
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-navy text-white font-monospace px-2 py-1">
                      #ĐP-{{ b.id }}
                    </span>
                    <span class="small text-muted" style="font-size: 0.75rem;">
                      {{ formatDateShort(b.created_at || b.ngayDat) }}
                    </span>
                  </div>
                  <div class="fw-bold text-navy mt-1">
                    <i class="bi bi-person me-1 text-teal"></i>{{ getCustomerName(b) }}
                  </div>
                  <div class="small text-muted d-flex align-items-center gap-2 mt-0.5" style="font-size: 0.8rem;">
                    <span><i class="bi bi-telephone me-1"></i>{{ getCustomerPhone(b) }}</span>
                    <span>•</span>
                    <span><i class="bi bi-card-heading me-1"></i>{{ getCustomerCCCD(b) }}</span>
                  </div>
                </td>

                <!-- Cột 2: Phòng & Trạng thái phòng vật lý -->
                <td>
                  <div v-if="b.chi_tiet_dat_phongs && b.chi_tiet_dat_phongs.length > 0" class="d-flex flex-column gap-1">
                    <div
                      v-for="detail in b.chi_tiet_dat_phongs"
                      :key="detail.id"
                      class="d-flex align-items-center gap-2"
                    >
                      <span class="fw-bold text-navy font-monospace">
                        <i class="bi bi-door-closed me-1 text-secondary"></i>
                        {{ detail.phong?.tenPhong || 'Phòng #' + detail.maPhong }}
                      </span>
                      <small class="text-muted">({{ detail.phong?.loai_phong?.tenLoai || 'Tiêu chuẩn' }})</small>

                      <!-- Badge trạng thái phòng vật lý -->
                      <span
                        class="badge rounded-pill px-2 py-0.5"
                        :class="getRoomStatusBadge(detail.phong?.trangThai)"
                        style="font-size: 0.7rem;"
                      >
                        {{ detail.phong?.trangThai || 'Trống' }}
                      </span>

                      <!-- Cảnh báo nếu phòng chưa sẵn sàng lúc sắp nhận phòng -->
                      <span
                        v-if="canCheckIn(b) && detail.phong?.trangThai !== 'Trống'"
                        class="text-danger small"
                        title="Phòng chưa sẵn sàng (Không ở trạng thái Trống)!"
                      >
                        <i class="bi bi-exclamation-triangle-fill"></i> Chưa sẵn sàng
                      </span>
                    </div>
                  </div>
                  <div v-else class="text-muted small fst-italic">
                    Chưa có thông tin phòng
                  </div>
                </td>

                <!-- Cột 3: Lịch nhận / trả -->
                <td class="small">
                  <div>
                    <span class="text-muted">Nhận:</span>
                    <strong class="text-navy ms-1">{{ formatDate(b.ngayNhanPhong) }}</strong>
                  </div>
                  <div>
                    <span class="text-muted">Trả:</span>
                    <strong class="text-navy ms-1">{{ formatDate(b.ngayTraPhong) }}</strong>
                  </div>
                  <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">
                    {{ calculateNights(b.ngayNhanPhong, b.ngayTraPhong) }} đêm lưu trú
                  </small>
                </td>

                <!-- Cột 4: Tiền cọc / Tổng tiền -->
                <td class="text-end small">
                  <div>
                    <span class="text-muted">Đã cọc:</span>
                    <strong class="text-success ms-1">{{ formatCurrency(b.tienCoc) }}</strong>
                  </div>
                  <div>
                    <span class="text-muted">Tổng dự kiến:</span>
                    <span class="fw-bold text-navy ms-1">{{ formatCurrency(b.tongTien) }}</span>
                  </div>
                </td>

                <!-- Cột 5: Trạng thái đơn -->
                <td class="text-center">
                  <span
                    class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    :class="getBookingStatusBadge(b.trangThai)"
                  >
                    {{ b.trangThai }}
                  </span>
                  <div v-if="b.trangThai === 'Đang ở'" class="mt-1">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">
                      <i class="bi bi-clock-history me-1"></i>Đang lưu trú
                    </span>
                  </div>
                </td>

                <!-- Cột 6: Thao tác Nghiệp Vụ -->
                <td class="text-end pe-4">
                  <div class="d-inline-flex align-items-center gap-1.5">
                    <!-- Nút Check-in -->
                    <button
                      v-if="canCheckIn(b)"
                      type="button"
                      class="btn btn-sm btn-teal text-white rounded-3 px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                      :disabled="!isAuthorized || isSubmitting"
                      title="Thực hiện nhận phòng cho khách"
                      @click="openCheckInModal(b)"
                    >
                      <i class="bi bi-box-arrow-in-right"></i>
                      <span>Nhận phòng</span>
                    </button>

                    <!-- Nút Check-out -->
                    <button
                      v-if="canCheckOut(b)"
                      type="button"
                      class="btn btn-sm btn-warning text-dark rounded-3 px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                      :disabled="!isAuthorized || isSubmitting"
                      title="Thực hiện trả phòng cho khách"
                      @click="openCheckOutModal(b)"
                    >
                      <i class="bi bi-box-arrow-right"></i>
                      <span>Trả phòng</span>
                    </button>

                    <!-- Nút Xem thông tin lưu trú -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-navy rounded-3 px-2 py-1"
                      title="Xem chi tiết đợt lưu trú & phiếu nhận/trả"
                      @click="openStayInfoModal(b.id)"
                    >
                      <i class="bi bi-info-circle"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL 1: THỦ TỤC NHẬN PHÒNG (CHECK-IN)                                     -->
      <!-- ========================================================================= -->
      <div
        v-if="showCheckInModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px);"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-in-right fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">Thủ Tục Nhận Phòng (Check-in)</h5>
                  <small class="text-white-50">Đơn đặt phòng #ĐP-{{ currentBooking?.id }}</small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="closeCheckInModal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <!-- Đối chiếu khách hàng -->
              <div class="card border rounded-3 bg-light p-3 mb-3">
                <div class="row g-2 small">
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Khách hàng nhận phòng:</span>
                    <strong class="text-navy fs-6">{{ getCustomerName(currentBooking) }}</strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Số CCCD / Hộ chiếu:</span>
                    <strong class="text-navy font-monospace">{{ getCustomerCCCD(currentBooking) }}</strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Số điện thoại liên hệ:</span>
                    <strong class="text-navy">{{ getCustomerPhone(currentBooking) }}</strong>
                  </div>
                </div>
              </div>

              <!-- Danh sách phòng được giao & Tùy chọn đổi phòng -->
              <div class="mb-3">
                <label class="form-label fw-bold text-navy small text-uppercase mb-2">
                  <i class="bi bi-door-open-fill text-teal me-1"></i> Danh sách phòng bàn giao & Trạng thái:
                </label>

                <div class="table-responsive">
                  <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light small">
                      <tr>
                        <th>Phòng ban đầu</th>
                        <th>Loại phòng</th>
                        <th>Trạng thái hiện tại</th>
                        <th>Chuyển sang phòng khác (Nếu phòng chưa sẵn sàng)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="roomItem in checkInRooms" :key="roomItem.maChiTiet">
                        <td class="fw-bold text-navy font-monospace">
                          {{ roomItem.tenPhong || 'Phòng #' + roomItem.maPhong }}
                        </td>
                        <td class="small">{{ roomItem.tenLoaiPhong }}</td>
                        <td>
                          <span
                            class="badge rounded-pill px-2 py-0.5"
                            :class="getRoomStatusBadge(roomItem.trangThaiPhong)"
                          >
                            {{ roomItem.trangThaiPhong }}
                          </span>
                        </td>
                        <td>
                          <!-- Select hoán đổi phòng -->
                          <select
                            v-model="roomItem.maPhongMoi"
                            class="form-select form-select-sm"
                            :class="{ 'border-warning': roomItem.maPhongMoi !== roomItem.maPhong }"
                          >
                            <option :value="roomItem.maPhong">
                              Giữ nguyên ({{ roomItem.tenPhong }})
                            </option>
                            <option
                              v-for="avail in getAvailableRoomsForType(roomItem.maLoaiPhong, roomItem.maPhong)"
                              :key="avail.id"
                              :value="avail.id"
                            >
                              Đổi sang {{ avail.tenPhong }} (Tầng {{ avail.soTang }} - Trống)
                            </option>
                          </select>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Cảnh báo nếu có phòng không sẵn sàng mà chưa đổi -->
                <div v-if="hasUnreadyRoomWithoutSwap" class="alert alert-warning small rounded-3 mt-2 mb-0 d-flex align-items-center gap-2">
                  <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                  <div>
                    <strong>Cảnh báo:</strong> Có phòng được giao hiện không ở trạng thái <code>Trống</code> (ví dụ: đang cần dọn dẹp hoặc sửa chữa). Vui lòng chọn đổi sang phòng trống cùng loại trước khi xác nhận nhận phòng!
                  </div>
                </div>
              </div>

              <!-- Thời gian nhận thực tế & Ước tính phụ thu nhận sớm (BR06.1) -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-navy">
                    Thời gian nhận phòng thực tế <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="checkInForm.thoiGianNhan"
                    type="datetime-local"
                    class="form-control"
                    required
                  />
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Giờ check-in tiêu chuẩn của khách sạn là <strong>14:00</strong>.
                  </small>
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-navy">
                    Ước tính phụ thu nhận sớm (BR06.1)
                  </label>
                  <div class="p-2.5 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                    <div>
                      <span class="d-block small text-muted">{{ earlySurchargeNote }}</span>
                      <strong class="text-teal fs-6">{{ formatCurrency(estimatedEarlySurcharge) }}</strong>
                    </div>
                    <span
                      class="badge"
                      :class="estimatedEarlySurcharge > 0 ? 'bg-warning text-dark' : 'bg-success text-white'"
                    >
                      {{ estimatedEarlySurcharge > 0 ? 'Có phụ thu' : 'Miễn phí' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Ghi chú khi nhận phòng -->
              <div class="mb-2">
                <label class="form-label small fw-semibold text-navy">Ghi chú nhận phòng / Bàn giao chìa khóa</label>
                <textarea
                  v-model.trim="checkInForm.ghiChu"
                  rows="2"
                  class="form-control"
                  placeholder="Ghi chú về tình trạng phòng, số chìa khóa/thẻ giao cho khách, yêu cầu đặc biệt..."
                ></textarea>
              </div>

              <div class="p-3 bg-light-subtle rounded-3 border small text-muted">
                <i class="bi bi-info-circle text-teal me-1"></i>
                Hệ thống sẽ cập nhật trạng thái đơn sang <strong>Đang ở</strong>, phòng vật lý sang <strong>Đang ở</strong>, tự động tạo <strong>Phiếu nhận phòng</strong> và khởi tạo hóa đơn lưu trú.
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="closeCheckInModal">
                Hủy Bỏ
              </button>
              <button
                type="button"
                class="btn btn-teal text-white rounded-3 px-4 fw-semibold shadow-sm"
                :disabled="isSubmitting || hasUnreadyRoomWithoutSwap"
                @click="submitCheckIn"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                Xác Nhận Nhận Phòng
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL 2: THỦ TỤC TRẢ PHÒNG (CHECK-OUT)                                     -->
      <!-- ========================================================================= -->
      <div
        v-if="showCheckOutModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px);"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-right fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">Thủ Tục Trả Phòng (Check-out)</h5>
                  <small class="text-white-50">Đơn đặt phòng #ĐP-{{ currentBooking?.id }}</small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="closeCheckOutModal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <!-- Đối chiếu thông tin khách & phòng đang ở -->
              <div class="card border rounded-3 bg-light p-3 mb-3">
                <div class="row g-2 small">
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Khách hàng:</span>
                    <strong class="text-navy fs-6">{{ getCustomerName(currentBooking) }}</strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Phòng đang ở:</span>
                    <strong class="text-navy font-monospace">
                      {{ getRoomsString(currentBooking) }}
                    </strong>
                  </div>
                  <div class="col-sm-4">
                    <span class="text-muted d-block">Tiền cọc ban đầu:</span>
                    <strong class="text-success">{{ formatCurrency(currentBooking?.tienCoc) }}</strong>
                  </div>
                </div>
              </div>

              <!-- Thời gian trả phòng thực tế & Phụ thu trả trễ (BR07.1) -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-navy">
                    Thời gian trả phòng thực tế <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model="checkOutForm.thoiGianTra"
                    type="datetime-local"
                    class="form-control"
                    required
                  />
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Giờ check-out tiêu chuẩn của khách sạn là <strong>12:00</strong>.
                  </small>
                </div>

                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-navy">
                    Ước tính phụ thu trả trễ (BR07.1)
                  </label>
                  <div class="p-2.5 rounded-3 border bg-light d-flex align-items-center justify-content-between">
                    <div>
                      <span class="d-block small text-muted">{{ lateSurchargeNote }}</span>
                      <strong class="text-danger fs-6">{{ formatCurrency(estimatedLateSurcharge) }}</strong>
                    </div>
                    <span
                      class="badge"
                      :class="estimatedLateSurcharge > 0 ? 'bg-warning text-dark' : 'bg-success text-white'"
                    >
                      {{ estimatedLateSurcharge > 0 ? 'Có phụ thu trễ' : 'Đúng giờ' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Checklist kiểm tra trước trả phòng (BR07.3) -->
              <div class="card border rounded-3 p-3 mb-3 bg-white">
                <h6 class="small fw-bold text-navy text-uppercase mb-2">
                  <i class="bi bi-clipboard-check text-teal me-1"></i> Danh Mục Kiểm Tra Thu Hồi Phòng:
                </h6>
                <div class="d-flex flex-column gap-2 small">
                  <div class="form-check">
                    <input id="chkKey" v-model="checkOutChecklist.keyCollected" class="form-check-input" type="checkbox" />
                    <label class="form-check-label text-navy fw-semibold" for="chkKey">
                      Đã thu hồi đầy đủ chìa khóa / thẻ từ mở phòng
                    </label>
                  </div>
                  <div class="form-check">
                    <input id="chkMinibar" v-model="checkOutChecklist.minibarChecked" class="form-check-input" type="checkbox" />
                    <label class="form-check-label text-navy" for="chkMinibar">
                      Đã đối chiếu tiêu dùng Minibar & Nước giải khát (ghi nhận vào hóa đơn)
                    </label>
                  </div>
                  <div class="form-check">
                    <input id="chkDamage" v-model="checkOutChecklist.damageChecked" class="form-check-input" type="checkbox" />
                    <label class="form-check-label text-navy" for="chkDamage">
                      Đã kiểm tra cơ sở vật chất phòng (không phát hiện tài sản hư hỏng / mất mát)
                    </label>
                  </div>
                </div>
              </div>

              <!-- Ghi chú khi trả phòng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy">Ghi chú trả phòng / Tình trạng thu hồi</label>
                <textarea
                  v-model.trim="checkOutForm.ghiChu"
                  rows="2"
                  class="form-control"
                  placeholder="Ghi chú về tình trạng phòng lúc thu hồi chìa khóa, đánh giá hoặc biên bản liên quan..."
                ></textarea>
              </div>

              <!-- Cảnh báo trạng thái buồng phòng BR07.2 -->
              <div class="alert alert-info small rounded-3 mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-info-circle-fill fs-5 text-teal"></i>
                <div>
                  <strong>Quy chuẩn BR07.2:</strong> Sau khi xác nhận trả phòng, phòng vật lý sẽ tự động chuyển sang trạng thái <code>Cần dọn dẹp</code> để bộ phận buồng phòng tiến hành vệ sinh, khử khuẩn và chuẩn bị đón lượt khách tiếp theo.
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="closeCheckOutModal">
                Hủy Bỏ
              </button>
              <button
                type="button"
                class="btn btn-warning text-dark rounded-3 px-4 fw-semibold shadow-sm"
                :disabled="isSubmitting"
                @click="submitCheckOut"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-box-arrow-right me-1"></i>
                Xác Nhận Trả Phòng
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL 3: THÔNG TIN LƯU TRÚ & LỊCH SỬ PHIẾU NHẬN/TRẢ (STAY INFO MODAL)     -->
      <!-- ========================================================================= -->
      <div
        v-if="showStayInfoModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px);"
      >
        <div class="modal-dialog modal-xl modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-journal-text fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">Hồ Sơ Lưu Trú & Audit Trail Nhận / Trả</h5>
                  <small class="text-white-50">Đơn đặt phòng #ĐP-{{ stayInfoData?.datPhong?.id }}</small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showStayInfoModal = false"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <!-- Loading -->
              <div v-if="isLoadingStayInfo" class="text-center py-5">
                <div class="spinner-border text-teal" role="status">
                  <span class="visually-hidden">Đang tải hồ sơ...</span>
                </div>
                <p class="text-muted small mt-2">Đang tải thông tin lưu trú & lịch sử phiếu nhận/trả...</p>
              </div>

              <!-- Nội dung -->
              <div v-else-if="stayInfoData">
                <!-- Tóm tắt Đơn & Khách -->
                <div class="row g-3 mb-4">
                  <div class="col-md-6">
                    <div class="card border rounded-3 p-3 bg-light h-100">
                      <h6 class="fw-bold text-navy small text-uppercase mb-2">
                        <i class="bi bi-person-fill text-teal me-1"></i> Khách Hàng Đại Diện:
                      </h6>
                      <div class="row g-2 small">
                        <div class="col-6">
                          <span class="text-muted">Họ và tên:</span>
                          <strong class="d-block text-navy">{{ stayInfoData.datPhong?.khach_hang?.hoTen }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Số CCCD / Hộ chiếu:</span>
                          <strong class="d-block text-navy font-monospace">{{ stayInfoData.datPhong?.khach_hang?.cccd }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Số điện thoại:</span>
                          <span class="d-block text-navy">{{ stayInfoData.datPhong?.khach_hang?.soDienThoai }}</span>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Email:</span>
                          <span class="d-block text-navy">{{ stayInfoData.datPhong?.khach_hang?.email }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="card border rounded-3 p-3 bg-light h-100">
                      <h6 class="fw-bold text-navy small text-uppercase mb-2">
                        <i class="bi bi-calendar-event-fill text-teal me-1"></i> Thông Tin Lịch & Trạng Thái:
                      </h6>
                      <div class="row g-2 small">
                        <div class="col-6">
                          <span class="text-muted">Ngày nhận dự kiến:</span>
                          <strong class="d-block text-navy">{{ formatDate(stayInfoData.datPhong?.ngayNhanPhong) }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Ngày trả dự kiến:</span>
                          <strong class="d-block text-navy">{{ formatDate(stayInfoData.datPhong?.ngayTraPhong) }}</strong>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Trạng thái đơn:</span>
                          <span class="badge rounded-pill px-2.5 py-1" :class="getBookingStatusBadge(stayInfoData.datPhong?.trangThai)">
                            {{ stayInfoData.datPhong?.trangThai }}
                          </span>
                        </div>
                        <div class="col-6">
                          <span class="text-muted">Tiền cọc:</span>
                          <strong class="d-block text-success">{{ formatCurrency(stayInfoData.datPhong?.tienCoc) }}</strong>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Danh sách Đợt Lưu Trú Thực Tế (luu_tru) -->
                <div class="card border rounded-3 mb-4 overflow-hidden">
                  <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy small text-uppercase mb-0">
                      <i class="bi bi-door-closed-fill text-teal me-1"></i> Danh Sách Phòng & Đợt Lưu Trú ({{ stayInfoData.danhSachLuuTru?.length || 0 }} đợt)
                    </h6>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light small">
                        <tr>
                          <th>Mã Lưu Trú</th>
                          <th>Phòng</th>
                          <th>Loại Phòng</th>
                          <th>Thời Gian Nhận Thực Tế</th>
                          <th>Thời Gian Trả Thực Tế</th>
                          <th>Trạng Thái Lưu Trú</th>
                        </tr>
                      </thead>
                      <tbody class="small">
                        <tr v-if="!stayInfoData.danhSachLuuTru || stayInfoData.danhSachLuuTru.length === 0">
                          <td colspan="6" class="text-center py-3 text-muted fst-italic">
                            Chưa có đợt lưu trú nào được kích hoạt (Đơn chưa check-in).
                          </td>
                        </tr>
                        <tr v-for="lt in stayInfoData.danhSachLuuTru" :key="lt.id">
                          <td class="font-monospace fw-bold text-navy">#LT-{{ lt.id }}</td>
                          <td class="fw-bold text-navy">
                            {{ lt.chi_tiet_dat_phong?.phong?.tenPhong || 'Phòng #' + lt.chi_tiet_dat_phong?.maPhong }}
                          </td>
                          <td>{{ lt.chi_tiet_dat_phong?.phong?.loai_phong?.tenLoai || 'Tiêu chuẩn' }}</td>
                          <td>{{ formatDate(lt.thoiGianNhan) }}</td>
                          <td>{{ formatDate(lt.thoiGianTra) }}</td>
                          <td>
                            <span class="badge rounded-pill px-2 py-0.5" :class="lt.trangThai === 'Đang ở' ? 'bg-primary text-white' : 'bg-secondary text-white'">
                              {{ lt.trangThai }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Danh sách Phiếu Nhận / Trả Phòng (phieu_nhan_tra_phong) -->
                <div class="card border rounded-3 mb-4 overflow-hidden">
                  <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy small text-uppercase mb-0">
                      <i class="bi bi-file-earmark-check-fill text-teal me-1"></i> Nhật Ký Phiếu Nhận / Trả Phòng (Audit Trail)
                    </h6>
                  </div>
                  <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                      <thead class="table-light small">
                        <tr>
                          <th>Mã Phiếu</th>
                          <th>Loại Phiếu</th>
                          <th>Thời Gian Lập</th>
                          <th>Nhân Viên Lập Phiếu</th>
                          <th>Ghi Chú Nghiệp Vụ</th>
                        </tr>
                      </thead>
                      <tbody class="small">
                        <tr v-if="!stayInfoData.danhSachPhieu || stayInfoData.danhSachPhieu.length === 0">
                          <td colspan="5" class="text-center py-3 text-muted fst-italic">
                            Chưa phát sinh phiếu nhận hoặc trả phòng.
                          </td>
                        </tr>
                        <tr v-for="phieu in stayInfoData.danhSachPhieu" :key="phieu.id">
                          <td class="font-monospace fw-bold text-navy">#PNTP-{{ phieu.id }}</td>
                          <td>
                            <span
                              class="badge rounded-pill px-2.5 py-1"
                              :class="phieu.loaiPhieu === 'Nhận phòng' ? 'bg-teal text-white' : 'bg-warning text-dark'"
                            >
                              <i :class="phieu.loaiPhieu === 'Nhận phòng' ? 'bi bi-box-arrow-in-right' : 'bi bi-box-arrow-right'" class="me-1"></i>
                              {{ phieu.loaiPhieu }}
                            </span>
                          </td>
                          <td>{{ formatDate(phieu.created_at) }}</td>
                          <td class="fw-semibold text-navy">
                            {{ phieu.nhan_vien?.hoTen || 'Nhân viên #' + (phieu.maNhanVien || 'N/A') }}
                          </td>
                          <td class="text-muted">{{ phieu.ghiChu || 'Không có ghi chú' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Thông tin Hóa Đơn Thanh Toán (hoa_don) -->
                <div v-if="stayInfoData.hoaDon" class="card border rounded-3 p-3 bg-light">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-navy small text-uppercase mb-0">
                      <i class="bi bi-receipt text-teal me-1"></i> Hóa Đơn Liên Kết:
                    </h6>
                    <router-link
                      to="/receptionist/invoices"
                      class="btn btn-sm btn-outline-teal rounded-3 px-2 py-0.5"
                    >
                      Mở quản lý hóa đơn (UC07)
                    </router-link>
                  </div>
                  <div class="row g-2 small">
                    <div class="col-sm-3">
                      <span class="text-muted d-block">Mã hóa đơn:</span>
                      <strong class="text-navy font-monospace">#HD-{{ stayInfoData.hoaDon.id }}</strong>
                    </div>
                    <div class="col-sm-3">
                      <span class="text-muted d-block">Tổng tiền:</span>
                      <strong class="text-navy">{{ formatCurrency(stayInfoData.hoaDon.tongTien) }}</strong>
                    </div>
                    <div class="col-sm-3">
                      <span class="text-muted d-block">Tiền cọc trừ đi:</span>
                      <strong class="text-success">{{ formatCurrency(stayInfoData.hoaDon.tienCoc) }}</strong>
                    </div>
                    <div class="col-sm-3">
                      <span class="text-muted d-block">Còn lại cần thanh toán:</span>
                      <strong class="text-danger">{{ formatCurrency(stayInfoData.hoaDon.tienConLai) }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end">
              <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="showStayInfoModal = false">
                Đóng
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

// =========================================================================
// 1. Quản lý Tài khoản & Phân quyền (RBAC)
// =========================================================================
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

const loadCurrentUser = () => {
  try {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const u = JSON.parse(userStr)
      currentUser.id = u.id || null
      currentUser.tenDangNhap = u.tenDangNhap || ''
      currentUser.hoTen = u.hoTen || ''
      currentUser.vaiTro = u.vaiTro || ''
    }
  } catch {
    // ignore
  }
}

const isAuthorized = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'lễ tân' || role === 'quản lý' || role === 'admin'
})

// Chuyển đổi tài khoản nhanh (Quick-Test)
const quickSwitchUser = async (username, password, roleLabel) => {
  try {
    isLoading.value = true
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      password: password,
    })

    if (res.data?.success && res.data?.data?.access_token) {
      const token = res.data.data.access_token
      const user = res.data.data.user
      localStorage.setItem('access_token', token)
      localStorage.setItem('user', JSON.stringify(user))

      currentUser.id = user.id
      currentUser.tenDangNhap = user.tenDangNhap
      currentUser.hoTen = user.hoTen
      currentUser.vaiTro = user.vaiTro

      showAlert(
        'Đã chuyển tài khoản',
        `Đã đăng nhập thành công vai trò [${roleLabel} - ${username}].`,
        'alert-success'
      )
      await fetchBookings()
      await fetchRooms()
    }
  } catch (err) {
    showAlert('Lỗi chuyển tài khoản', err.response?.data?.message || err.message, 'alert-danger')
  } finally {
    isLoading.value = false
  }
}

// =========================================================================
// 2. Trạng Thái Dữ Liệu & Bộ Lọc
// =========================================================================
const isLoading = ref(false)
const isSubmitting = ref(false)
const bookings = ref([])
const availableRooms = ref([]) // Danh sách tất cả các phòng vật lý

const filterParams = reactive({
  tuKhoa: '',
  trangThai: '',
})

const alertMessage = reactive({
  title: '',
  text: '',
  typeClass: 'alert-success',
  iconClass: 'bi-check-circle-fill',
})

const showAlert = (title, text, typeClass = 'alert-success') => {
  alertMessage.title = title
  alertMessage.text = text
  alertMessage.typeClass = typeClass
  if (typeClass.includes('success')) {
    alertMessage.iconClass = 'bi-check-circle-fill text-success'
  } else if (typeClass.includes('danger')) {
    alertMessage.iconClass = 'bi-x-circle-fill text-danger'
  } else if (typeClass.includes('warning')) {
    alertMessage.iconClass = 'bi-exclamation-triangle-fill text-warning'
  } else {
    alertMessage.iconClass = 'bi-info-circle-fill text-teal'
  }
}

const clearAlert = () => {
  alertMessage.title = ''
  alertMessage.text = ''
}

// =========================================================================
// 3. API Tra Cứu Dữ Liệu
// =========================================================================
const fetchBookings = async () => {
  isLoading.value = true
  clearAlert()
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get('/api/v1/bookings', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      params: {
        per_page: 100,
      },
    })

    if (res.data?.success) {
      bookings.value = res.data.data?.data || res.data.data || []
    }
  } catch (err) {
    const status = err.response?.status
    if (status === 401) {
      showAlert('Chưa đăng nhập', 'Phiên làm việc hết hạn, vui lòng đăng nhập lại.', 'alert-danger')
    } else if (status === 403) {
      showAlert('Từ chối truy cập (403)', 'Tài khoản không đủ quyền hạn truy cập danh sách đặt phòng.', 'alert-danger')
    } else {
      showAlert('Lỗi nạp dữ liệu', err.response?.data?.message || 'Không thể tải danh sách đặt phòng.', 'alert-danger')
    }
  } finally {
    isLoading.value = false
  }
}

const fetchRooms = async () => {
  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get('/api/v1/rooms', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      params: {
        per_page: 200,
      },
    })
    if (res.data?.success) {
      availableRooms.value = res.data.data?.data || res.data.data || []
    }
  } catch {
    // ignore
  }
}

// Danh sách phòng được lọc trên UI
const filteredBookings = computed(() => {
  return bookings.value.filter((b) => {
    // 1. Lọc theo trạng thái
    if (filterParams.trangThai) {
      if (filterParams.trangThai === 'ready_checkin') {
        if (!['Đã xác nhận', 'Chờ thanh toán'].includes(b.trangThai)) return false
      } else if (b.trangThai !== filterParams.trangThai) {
        return false
      }
    }

    // 2. Lọc theo từ khóa
    if (filterParams.tuKhoa) {
      const q = filterParams.tuKhoa.toLowerCase().trim()
      const bookingId = String(b.id || '')
      const customerName = (b.khach_hang?.hoTen || '').toLowerCase()
      const cccd = (b.khach_hang?.cccd || '').toLowerCase()
      const phone = (b.khach_hang?.soDienThoai || '').toLowerCase()

      const matchId = bookingId.includes(q) || `#đp-${bookingId}`.includes(q)
      const matchName = customerName.includes(q)
      const matchCccd = cccd.includes(q)
      const matchPhone = phone.includes(q)

      if (!matchId && !matchName && !matchCccd && !matchPhone) {
        return false
      }
    }

    return true
  })
})

const handleSearch = () => {
  // Lọc trực tiếp qua computed
}

const resetFilter = () => {
  filterParams.tuKhoa = ''
  filterParams.trangThai = ''
}

// KPI Thống kê
const kpi = computed(() => {
  let readyForCheckin = 0
  let activeStay = 0
  let checkedOut = 0

  bookings.value.forEach((b) => {
    if (['Đã xác nhận', 'Chờ thanh toán'].includes(b.trangThai)) {
      readyForCheckin++
    } else if (b.trangThai === 'Đang ở') {
      activeStay++
    } else if (b.trangThai === 'Đã trả' || b.trangThai === 'Đã hoàn thành') {
      checkedOut++
    }
  })

  return { readyForCheckin, activeStay, checkedOut }
})

// Kiểm tra quyền hành động
const canCheckIn = (b) => {
  return ['Đã xác nhận', 'Chờ thanh toán'].includes(b.trangThai)
}

const canCheckOut = (b) => {
  return b.trangThai === 'Đang ở'
}

// =========================================================================
// 4. Modal 1: Check-in & Tính phụ thu nhận sớm (BR06.1)
// =========================================================================
const showCheckInModal = ref(false)
const currentBooking = ref(null)
const checkInRooms = ref([])

const checkInForm = reactive({
  thoiGianNhan: '',
  ghiChu: '',
})

// Format thời gian ISO về dạng YYYY-MM-DDTHH:mm
const getCurrentLocalDatetime = () => {
  const now = new Date()
  const year = now.getFullYear()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const date = String(now.getDate()).padStart(2, '0')
  const hours = String(now.getHours()).padStart(2, '0')
  const minutes = String(now.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${date}T${hours}:${minutes}`
}

const openCheckInModal = async (booking) => {
  currentBooking.value = booking
  checkInForm.thoiGianNhan = getCurrentLocalDatetime()
  checkInForm.ghiChu = ''

  // Load lại danh sách phòng vật lý mới nhất
  await fetchRooms()

  // Tạo danh sách phòng cần giao
  checkInRooms.value = (booking.chi_tiet_dat_phongs || []).map((detail) => {
    // Tìm phòng trong availableRooms để lấy trạng thái mới nhất
    const liveRoom = availableRooms.value.find((r) => r.id === detail.maPhong) || detail.phong || {}
    return {
      maChiTiet: detail.id,
      maPhong: detail.maPhong,
      maPhongMoi: detail.maPhong,
      tenPhong: liveRoom.tenPhong || detail.phong?.tenPhong || `Phòng #${detail.maPhong}`,
      maLoaiPhong: liveRoom.maLoaiPhong || detail.phong?.maLoaiPhong,
      tenLoaiPhong: liveRoom.loai_phong?.tenLoai || detail.phong?.loai_phong?.tenLoai || 'Tiêu chuẩn',
      trangThaiPhong: liveRoom.trangThai || 'Trống',
      giaPhongApDung: detail.giaPhongApDung || 0,
    }
  })

  showCheckInModal.value = true
}

const closeCheckInModal = () => {
  showCheckInModal.value = false
  currentBooking.value = null
  checkInRooms.value = []
}

// Lấy danh sách các phòng trống cùng loại để đổi
const getAvailableRoomsForType = (maLoaiPhong, currentRoomId) => {
  return availableRooms.value.filter(
    (r) => r.maLoaiPhong === maLoaiPhong && r.id !== currentRoomId && r.trangThai === 'Trống'
  )
}

// Kiểm tra xem có phòng nào chưa sẵn sàng mà chưa được đổi sang phòng trống không
const hasUnreadyRoomWithoutSwap = computed(() => {
  return checkInRooms.value.some((r) => {
    // Nếu giữ nguyên phòng cũ mà phòng cũ không ở trạng thái Trống
    if (r.maPhongMoi === r.maPhong && r.trangThaiPhong !== 'Trống') {
      return true
    }
    return false
  })
})

// Tính phụ thu nhận phòng sớm (BR06.1)
const earlySurchargeNote = ref('Nhận phòng theo giờ tiêu chuẩn (14:00)')
const estimatedEarlySurcharge = computed(() => {
  if (!checkInForm.thoiGianNhan || !currentBooking.value) return 0

  const checkinTime = new Date(checkInForm.thoiGianNhan)
  const scheduledCheckin = new Date(currentBooking.value.ngayNhanPhong)

  // So sánh ngày
  const checkinDateStr = checkinTime.toISOString().slice(0, 10)
  const scheduledDateStr = scheduledCheckin.toISOString().slice(0, 10)

  // Tổng đơn giá ngày các phòng
  const totalDailyRate = checkInRooms.value.reduce((acc, r) => acc + Number(r.giaPhongApDung || 0), 0)

  // Nếu đến cùng ngày với ngày dự kiến
  if (checkinDateStr === scheduledDateStr) {
    const hours = checkinTime.getHours()
    if (hours < 6) {
      earlySurchargeNote.value = 'Nhận phòng trước 06:00 (Phụ thu 100% đơn giá ngày)'
      return totalDailyRate * 1.0
    } else if (hours < 9) {
      earlySurchargeNote.value = 'Nhận phòng từ 06:00 - 09:00 (Phụ thu 50% đơn giá ngày)'
      return totalDailyRate * 0.5
    } else if (hours < 12) {
      earlySurchargeNote.value = 'Nhận phòng từ 09:00 - 12:00 (Phụ thu 30% đơn giá ngày)'
      return totalDailyRate * 0.3
    } else {
      earlySurchargeNote.value = 'Nhận phòng từ 12:00 - 14:00 (Miễn phí nhận sớm)'
      return 0
    }
  } else if (checkinTime < scheduledCheckin) {
    earlySurchargeNote.value = 'Nhận phòng sớm trước ngày dự kiến (Phụ thu 100% đơn giá ngày)'
    return totalDailyRate * 1.0
  }

  earlySurchargeNote.value = 'Nhận phòng theo khung giờ quy chuẩn'
  return 0
})

const submitCheckIn = async () => {
  if (!currentBooking.value) return

  isSubmitting.value = true
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')

    // Chuẩn bị danh sách phòng thực tế
    const danhSachPhongPayload = checkInRooms.value.map((r) => ({
      maChiTiet: r.maChiTiet,
      maPhongCu: r.maPhong,
      maPhongMoi: r.maPhongMoi,
    }))

    const payload = {
      ghiChu: checkInForm.ghiChu || 'Check-in tại quầy lễ tân',
      thoiGianNhan: checkInForm.thoiGianNhan ? checkInForm.thoiGianNhan.replace('T', ' ') + ':00' : null,
      danhSachPhong: danhSachPhongPayload,
    }

    const res = await axios.post(`/api/v1/bookings/${currentBooking.value.id}/check-in`, payload, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.data?.success) {
      showAlert(
        'Nhận phòng thành công',
        res.data.message || 'Thủ tục Check-in hoàn tất. Trạng thái phòng và đơn đã được cập nhật sang Đang ở.',
        'alert-success'
      )
      closeCheckInModal()
      await fetchBookings()
      await fetchRooms()
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || err.message
    if (status === 409) {
      showAlert('Xung đột nghiệp vụ (409)', msg || 'Đơn đã nhận phòng hoặc phòng không ở trạng thái sẵn sàng.', 'alert-danger')
    } else if (status === 422) {
      showAlert('Dữ liệu không hợp lệ (422)', msg, 'alert-danger')
    } else if (status === 403) {
      showAlert('Không có quyền (403)', 'Chỉ Lễ tân hoặc Quản lý mới được thực hiện check-in.', 'alert-danger')
    } else {
      showAlert('Lỗi xử lý check-in', msg, 'alert-danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// =========================================================================
// 5. Modal 2: Check-out & Tính phụ thu trả trễ (BR07.1)
// =========================================================================
const showCheckOutModal = ref(false)
const checkOutForm = reactive({
  thoiGianTra: '',
  ghiChu: '',
})

const checkOutChecklist = reactive({
  keyCollected: true,
  minibarChecked: true,
  damageChecked: true,
})

const openCheckOutModal = (booking) => {
  currentBooking.value = booking
  checkOutForm.thoiGianTra = getCurrentLocalDatetime()
  checkOutForm.ghiChu = ''
  checkOutChecklist.keyCollected = true
  checkOutChecklist.minibarChecked = true
  checkOutChecklist.damageChecked = true
  showCheckOutModal.value = true
}

const closeCheckOutModal = () => {
  showCheckOutModal.value = false
  currentBooking.value = null
}

// Tính phụ thu trả phòng trễ (BR07.1)
const lateSurchargeNote = ref('Trả phòng theo khung giờ tiêu chuẩn (12:00)')
const estimatedLateSurcharge = computed(() => {
  if (!checkOutForm.thoiGianTra || !currentBooking.value) return 0

  const checkoutTime = new Date(checkOutForm.thoiGianTra)
  const scheduledCheckout = new Date(currentBooking.value.ngayTraPhong)

  // Tổng đơn giá ngày
  const totalDailyRate = (currentBooking.value.chi_tiet_dat_phongs || []).reduce(
    (acc, d) => acc + Number(d.giaPhongApDung || 0),
    0
  )

  const checkoutDateStr = checkoutTime.toISOString().slice(0, 10)
  const scheduledDateStr = scheduledCheckout.toISOString().slice(0, 10)

  if (checkoutDateStr === scheduledDateStr) {
    const hours = checkoutTime.getHours()
    if (hours >= 12 && hours < 15) {
      lateSurchargeNote.value = 'Trả phòng từ 12:00 - 15:00 (Phụ thu 30% đơn giá ngày)'
      return totalDailyRate * 0.3
    } else if (hours >= 15 && hours < 18) {
      lateSurchargeNote.value = 'Trả phòng từ 15:00 - 18:00 (Phụ thu 50% đơn giá ngày)'
      return totalDailyRate * 0.5
    } else if (hours >= 18) {
      lateSurchargeNote.value = 'Trả phòng sau 18:00 (Phụ thu 100% đơn giá ngày)'
      return totalDailyRate * 1.0
    }
  } else if (checkoutTime > scheduledCheckout) {
    lateSurchargeNote.value = 'Trả phòng trễ hơn ngày đăng ký (Phụ thu 100% đơn giá ngày)'
    return totalDailyRate * 1.0
  }

  lateSurchargeNote.value = 'Trả phòng đúng giờ hoặc trước 12:00 (Không phụ thu)'
  return 0
})

const submitCheckOut = async () => {
  if (!currentBooking.value) return

  isSubmitting.value = true
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')

    const payload = {
      ghiChu: checkOutForm.ghiChu || 'Check-out tại quầy lễ tân',
      thoiGianTra: checkOutForm.thoiGianTra ? checkOutForm.thoiGianTra.replace('T', ' ') + ':00' : null,
    }

    const res = await axios.post(`/api/v1/bookings/${currentBooking.value.id}/check-out`, payload, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.data?.success) {
      showAlert(
        'Trả phòng thành công',
        `${res.data.message || 'Thủ tục Check-out hoàn tất.'} Phòng vật lý đã được chuyển sang trạng thái "Cần dọn dẹp" (BR07.2).`,
        'alert-success'
      )
      closeCheckOutModal()
      await fetchBookings()
      await fetchRooms()
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || err.message
    if (status === 409) {
      showAlert('Xung đột nghiệp vụ (409)', msg || 'Đơn chưa nhận phòng hoặc đã trả phòng trước đó.', 'alert-danger')
    } else if (status === 422) {
      showAlert('Dữ liệu không hợp lệ (422)', msg, 'alert-danger')
    } else if (status === 403) {
      showAlert('Không có quyền (403)', 'Chỉ Lễ tân hoặc Quản lý mới được thực hiện check-out.', 'alert-danger')
    } else {
      showAlert('Lỗi xử lý check-out', msg, 'alert-danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// =========================================================================
// 6. Modal 3: Chi Tiết Thông Tin Lưu Trú (Stay Info Modal)
// =========================================================================
const showStayInfoModal = ref(false)
const isLoadingStayInfo = ref(false)
const stayInfoData = ref(null)

const openStayInfoModal = async (bookingId) => {
  showStayInfoModal.value = true
  isLoadingStayInfo.value = true
  stayInfoData.value = null

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.get(`/api/v1/bookings/${bookingId}/stay-info`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })
    if (res.data?.success) {
      stayInfoData.value = res.data.data
    }
  } catch (err) {
    showAlert('Lỗi nạp thông tin lưu trú', err.response?.data?.message || err.message, 'alert-danger')
  } finally {
    isLoadingStayInfo.value = false
  }
}

// =========================================================================
// 7. Helpers: Format Tiền tệ, Ngày tháng & Badges
// =========================================================================
const formatCurrency = (val) => {
  if (val === null || val === undefined || isNaN(val)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

const formatDateShort = (dateStr) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('vi-VN', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

const calculateNights = (checkin, checkout) => {
  if (!checkin || !checkout) return 1
  try {
    const d1 = new Date(checkin)
    const d2 = new Date(checkout)
    const diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24))
    return diff > 0 ? diff : 1
  } catch {
    return 1
  }
}

const getCustomerName = (b) => {
  return b?.khach_hang?.hoTen || 'Khách vãng lai'
}

const getCustomerPhone = (b) => {
  return b?.khach_hang?.soDienThoai || 'Chưa cập nhật'
}

const getCustomerCCCD = (b) => {
  return b?.khach_hang?.cccd || 'Chưa cập nhật'
}

const getRoomsString = (b) => {
  if (!b?.chi_tiet_dat_phongs || b.chi_tiet_dat_phongs.length === 0) return 'Chưa phân phòng'
  return b.chi_tiet_dat_phongs
    .map((d) => d.phong?.tenPhong || `Phòng #${d.maPhong}`)
    .join(', ')
}

const getRoomStatusBadge = (status) => {
  switch (status) {
    case 'Trống':
      return 'bg-success text-white'
    case 'Đang ở':
      return 'bg-danger text-white'
    case 'Cần dọn dẹp':
      return 'bg-warning text-dark'
    case 'Đang bảo trì':
      return 'bg-secondary text-white'
    case 'Ngưng khai thác':
      return 'bg-dark text-white'
    default:
      return 'bg-light text-dark border'
  }
}

const getBookingStatusBadge = (status) => {
  switch (status) {
    case 'Chờ xác nhận':
    case 'Chờ thanh toán':
      return 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
    case 'Đã xác nhận':
      return 'bg-info-subtle text-info-emphasis border border-info-subtle'
    case 'Đang ở':
      return 'bg-primary-subtle text-primary border border-primary-subtle'
    case 'Đã trả':
    case 'Đã hoàn thành':
      return 'bg-success-subtle text-success border border-success-subtle'
    case 'Đã hủy':
      return 'bg-danger-subtle text-danger border border-danger-subtle'
    default:
      return 'bg-light text-secondary border'
  }
}

// Lifecycle
onMounted(async () => {
  loadCurrentUser()
  await fetchBookings()
  await fetchRooms()
})
</script>

<style scoped>
/* Design Tokens: Navy #1E3A5F, Teal #0F766E, Background #F8FAFC, Font Inter */
.check-in-out-view {
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.text-navy {
  color: #1e3a5f !important;
}

.bg-navy {
  background-color: #1e3a5f !important;
}

.btn-navy {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
}

.btn-navy:hover {
  background-color: #162c47;
  border-color: #162c47;
}

.btn-outline-navy {
  color: #1e3a5f;
  border-color: #1e3a5f;
}

.btn-outline-navy:hover {
  background-color: #1e3a5f;
  color: #ffffff;
}

.text-teal {
  color: #0f766e !important;
}

.bg-teal {
  background-color: #0f766e !important;
}

.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
}

.btn-teal:hover {
  background-color: #0d635d;
  border-color: #0d635d;
}

.btn-outline-teal {
  color: #0f766e;
  border-color: #0f766e;
}

.btn-outline-teal:hover {
  background-color: #0f766e;
  color: #ffffff;
}

.header-icon-box {
  width: 52px;
  height: 52px;
}

.kpi-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.kpi-icon {
  width: 48px;
  height: 48px;
}

.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}
</style>
