<template>
  <div class="booking-history-page py-4">
    <div class="container-xl">
      <!-- Breadcrumb -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/" class="text-decoration-none text-teal">Trang chủ</router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            {{ isStaff ? 'Quản lý & Theo dõi Hủy Đặt Phòng' : 'Lịch sử đặt phòng' }}
          </li>
        </ol>
      </nav>

      <!-- Page Header -->
      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-teal-subtle text-teal rounded-3 d-flex align-items-center justify-content-center">
              <i class="bi bi-journal-bookmark-fill fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">
                {{ isStaff ? 'Theo Dõi & Xử Lý Hủy Đặt Phòng' : 'Lịch Sử & Theo Dõi Đặt Phòng' }}
              </h2>
              <p class="text-muted mb-0 small">
                {{
                  isStaff
                    ? 'Bảng điều phối Lễ tân / Quản lý: Tra cứu toàn bộ đơn, kiểm tra chính sách và phê duyệt/từ chối yêu cầu hủy phòng.'
                    : 'Theo dõi chi tiết các đơn đặt phòng của bạn, kiểm tra chính sách hủy và gửi yêu cầu hủy phòng theo quy định.'
                }}
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isStaff ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle'"
              >
                <i :class="isStaff ? 'bi bi-person-badge-fill me-1' : 'bi bi-person-check-fill me-1'"></i>
                {{ currentUser.vaiTro || 'Khách hàng' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Khách vãng lai' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isStaff ? 'Chế độ quản trị nhân viên' : 'Chế độ cổng khách hàng' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Test & Role Switcher Toolbar -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi tài khoản kiểm thử nhanh:</span>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'nguyenvanminh' }"
                :disabled="isLoading"
                @click="quickSwitchUser('nguyenvanminh', '123456', 'Khách hàng')"
              >
                <i class="bi bi-person me-1"></i> Khách hàng (Minh)
              </button>
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
                to="/booking"
                class="btn btn-sm btn-teal text-white rounded-3 px-3 fw-semibold"
              >
                <i class="bi bi-calendar-plus me-1"></i> Đặt phòng mới
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Stats Summary Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small">Tổng đơn đặt</span>
              <div class="stat-icon bg-primary-subtle text-primary rounded-3">
                <i class="bi bi-collection-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold text-navy mb-0">{{ bookings.length }}</h4>
            <small class="text-muted" style="font-size: 0.75rem;">Đơn trong hệ thống</small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small">Đang hiệu lực</span>
              <div class="stat-icon bg-success-subtle text-success rounded-3">
                <i class="bi bi-check-circle-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold text-success mb-0">{{ activeBookingsCount }}</h4>
            <small class="text-muted" style="font-size: 0.75rem;">Chờ xác nhận / Đã cọc</small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 h-100"
            :class="pendingCancelCount > 0 ? 'bg-danger-subtle border-danger border' : 'bg-white'"
          >
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span :class="pendingCancelCount > 0 ? 'text-danger fw-semibold small' : 'text-muted small'">
                Yêu cầu hủy chờ duyệt
              </span>
              <div
                class="stat-icon rounded-3"
                :class="pendingCancelCount > 0 ? 'bg-danger text-white' : 'bg-warning-subtle text-warning'"
              >
                <i class="bi bi-exclamation-octagon-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold mb-0" :class="pendingCancelCount > 0 ? 'text-danger' : 'text-navy'">
              {{ pendingCancelCount }}
            </h4>
            <small :class="pendingCancelCount > 0 ? 'text-danger fw-medium' : 'text-muted'" style="font-size: 0.75rem;">
              {{ pendingCancelCount > 0 ? 'Cần xử lý ngay' : 'Không có yêu cầu tồn đọng' }}
            </small>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-muted small">Tiền cọc quản lý</span>
              <div class="stat-icon bg-info-subtle text-info rounded-3">
                <i class="bi bi-cash-stack"></i>
              </div>
            </div>
            <h4 class="fw-bold text-teal mb-0">{{ formatCurrency(totalDepositAmount) }}</h4>
            <small class="text-muted" style="font-size: 0.75rem;">Tổng tiền cọc các đơn</small>
          </div>
        </div>
      </div>

      <!-- Filter Tabs & Search Bar -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="row g-2 align-items-center">
            <!-- Search Input -->
            <div class="col-12 col-md-4 col-lg-4">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  v-model.trim="searchQuery"
                  type="text"
                  class="form-control border-start-0 ps-0 bg-light"
                  placeholder="Tìm theo mã #DP, phòng, khách..."
                />
                <button
                  v-if="searchQuery"
                  class="btn btn-light border-start-0 text-muted"
                  type="button"
                  @click="searchQuery = ''"
                >
                  <i class="bi bi-x-circle"></i>
                </button>
              </div>
            </div>

            <!-- Filter Status Badges/Tabs -->
            <div class="col-12 col-md-8 col-lg-8">
              <div class="d-flex flex-wrap gap-1 justify-content-md-end">
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'all' ? 'btn-navy text-white fw-semibold' : 'btn-light text-muted'"
                  @click="activeFilter = 'all'"
                >
                  Tất cả ({{ bookings.length }})
                </button>
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'Chờ xác nhận' ? 'btn-warning text-dark fw-semibold' : 'btn-light text-muted'"
                  @click="activeFilter = 'Chờ xác nhận'"
                >
                  Chờ xác nhận
                </button>
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'Đã cọc' ? 'btn-info text-dark fw-semibold' : 'btn-light text-muted'"
                  @click="activeFilter = 'Đã cọc'"
                >
                  Đã cọc
                </button>
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'Đã xác nhận' ? 'btn-success text-white fw-semibold' : 'btn-light text-muted'"
                  @click="activeFilter = 'Đã xác nhận'"
                >
                  Đã xác nhận
                </button>
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'cancellation_pending' ? 'btn-danger text-white fw-bold' : 'btn-light text-danger'"
                  @click="activeFilter = 'cancellation_pending'"
                >
                  <i class="bi bi-bell-fill me-1"></i>
                  Chờ duyệt hủy ({{ pendingCancelCount }})
                </button>
                <button
                  type="button"
                  class="btn btn-sm rounded-pill px-3 py-1.5"
                  :class="activeFilter === 'Đã hủy' ? 'btn-secondary text-white fw-semibold' : 'btn-light text-muted'"
                  @click="activeFilter = 'Đã hủy'"
                >
                  Đã hủy
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Global Alert / Error State -->
      <div
        v-if="fetchError"
        class="alert alert-danger border-danger shadow-sm rounded-4 mb-4 d-flex align-items-center justify-content-between p-3"
        role="alert"
      >
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
          <div>
            <strong>Lỗi nạp dữ liệu:</strong> {{ fetchError }}
          </div>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-sm btn-outline-danger rounded-3" @click="fetchBookings">
            <i class="bi bi-arrow-clockwise me-1"></i> Thử lại
          </button>
          <router-link to="/dang-nhap" class="btn btn-sm btn-danger rounded-3">
            Đăng nhập lại
          </router-link>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="text-center py-5 my-4">
        <div class="spinner-border text-teal mb-3" style="width: 3rem; height: 3rem;" role="status">
          <span class="visually-hidden">Đang tải...</span>
        </div>
        <h5 class="fw-semibold text-navy">Đang nạp danh sách đặt phòng...</h5>
        <p class="text-muted small">Vui lòng chờ trong giây lát</p>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="filteredBookings.length === 0"
        class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4"
      >
        <div class="empty-icon-circle mx-auto mb-3 bg-light text-muted d-flex align-items-center justify-content-center">
          <i class="bi bi-calendar-x fs-1"></i>
        </div>
        <h4 class="fw-bold text-navy mb-2">Không tìm thấy đơn đặt phòng nào</h4>
        <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
          {{
            searchQuery || activeFilter !== 'all'
              ? 'Không có đơn đặt phòng nào phù hợp với điều kiện tìm kiếm hoặc bộ lọc hiện tại. Thử xóa tìm kiếm hoặc chuyển tab lọc khác.'
              : isStaff
                ? 'Hiện tại hệ thống chưa ghi nhận đơn đặt phòng nào từ khách hàng.'
                : 'Bạn chưa có đơn đặt phòng nào tại RoomBook Hotel & Resort. Hãy bắt đầu chọn phòng và tận hưởng kỳ nghỉ ngay hôm nay!'
          }}
        </p>
        <div class="d-flex justify-content-center gap-2">
          <button
            v-if="searchQuery || activeFilter !== 'all'"
            class="btn btn-outline-navy rounded-3 px-3 py-2"
            @click="resetFilters"
          >
            <i class="bi bi-funnel me-1"></i> Đặt lại bộ lọc
          </button>
          <router-link
            to="/booking"
            class="btn btn-teal text-white rounded-3 px-4 py-2 fw-semibold"
          >
            <i class="bi bi-calendar-plus me-1"></i> Đặt phòng ngay
          </router-link>
        </div>
      </div>

      <!-- Booking Card List -->
      <div v-else class="row g-3">
        <div
          v-for="item in filteredBookings"
          :key="item.id"
          class="col-12"
        >
          <div
            class="card booking-card border-0 shadow-sm rounded-4 overflow-hidden bg-white"
            :class="{
              'border-start-cancellation': item.yeuCauHuy && item.yeuCauHuy.trangThaiYeuCau === 'Chờ xử lý',
              'border-start-cancelled': item.trangThai === 'Đã hủy',
              'border-start-active': ['Chờ xác nhận', 'Đã cọc', 'Đã xác nhận'].includes(item.trangThai)
            }"
          >
            <!-- Card Header -->
            <div class="card-header bg-light bg-opacity-50 py-3 px-4 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="booking-code fw-bold text-navy fs-6">
                  <i class="bi bi-ticket-perforated-fill text-teal me-1"></i>
                  Đơn #DP-{{ item.id }}
                </span>
                <span class="text-muted small">
                  <i class="bi bi-clock me-1"></i> Ngày đặt: {{ formatDateTime(item.ngayDat) }}
                </span>
                <span
                  class="badge rounded-pill px-3 py-1.5 fw-semibold"
                  :class="getBookingStatusBadge(item.trangThai)"
                >
                  <i :class="getBookingStatusIcon(item.trangThai)" class="me-1"></i>
                  {{ item.trangThai }}
                </span>
              </div>

              <!-- Cancellation Tag Badge -->
              <div v-if="item.yeuCauHuy" class="d-flex align-items-center gap-2">
                <span
                  class="badge px-3 py-1.5 rounded-pill fw-bold"
                  :class="getCancellationBadgeClass(item.yeuCauHuy.trangThaiYeuCau)"
                >
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  Yêu cầu hủy: {{ item.yeuCauHuy.trangThaiYeuCau }}
                </span>
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4">
              <div class="row g-3 align-items-center">
                <!-- Thông tin lưu trú & phòng -->
                <div class="col-12 col-lg-5">
                  <div class="stay-info-box p-3 rounded-3 bg-light mb-2">
                    <div class="d-flex align-items-center justify-content-between">
                      <div>
                        <div class="text-muted small" style="font-size: 0.72rem;">NGÀY NHẬN PHÒNG</div>
                        <div class="fw-bold text-navy fs-6">{{ formatDateTime(item.ngayNhanPhong) }}</div>
                      </div>
                      <div class="text-center px-2">
                        <span class="badge bg-teal text-white rounded-pill px-2 py-1 small">
                          {{ item.soDem }} đêm
                        </span>
                        <div class="text-muted" style="font-size: 0.9rem;">➔</div>
                      </div>
                      <div class="text-end">
                        <div class="text-muted small" style="font-size: 0.72rem;">NGÀY TRẢ PHÒNG</div>
                        <div class="fw-bold text-navy fs-6">{{ formatDateTime(item.ngayTraPhong) }}</div>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex align-items-center gap-3 text-muted small mt-2">
                    <span>
                      <i class="bi bi-people-fill me-1 text-teal"></i>
                      <strong>{{ item.soLuongKhach }}</strong> khách
                    </span>
                    <span>•</span>
                    <span>
                      <i class="bi bi-door-closed-fill me-1 text-teal"></i>
                      <strong>{{ item.danhSachPhong?.length || 0 }}</strong> phòng
                    </span>
                  </div>
                </div>

                <!-- Danh sách phòng tóm tắt -->
                <div class="col-12 col-md-6 col-lg-4">
                  <div class="text-muted small mb-1 fw-semibold">PHÒNG ĐÃ CHỌN:</div>
                  <div class="d-flex flex-wrap gap-1.5">
                    <div
                      v-for="room in item.danhSachPhong"
                      :key="room.id"
                      class="room-chip px-2.5 py-1 rounded-2 bg-light border text-truncate small"
                      style="max-width: 100%;"
                    >
                      <i class="bi bi-door-closed text-teal me-1"></i>
                      <strong>{{ room.tenPhong }}</strong>
                      <span v-if="room.tenLoaiPhong" class="text-muted ms-1">({{ room.tenLoaiPhong }})</span>
                      <span class="text-navy fw-semibold ms-1">· {{ formatCurrency(room.giaPhongApDung) }}/đêm</span>
                    </div>
                  </div>

                  <!-- Thông tin khách hàng (nếu là Lễ tân/Quản lý) -->
                  <div v-if="isStaff && item.khachHang" class="mt-2 pt-2 border-top small">
                    <div class="text-muted small" style="font-size: 0.7rem;">KHÁCH HÀNG:</div>
                    <div class="fw-bold text-navy">{{ item.khachHang.hoTen }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                      {{ item.khachHang.soDienThoai }} · {{ item.khachHang.email }}
                    </div>
                  </div>
                </div>

                <!-- Thông tin tài chính & cọc -->
                <div class="col-12 col-md-6 col-lg-3 text-md-end">
                  <div class="text-muted small" style="font-size: 0.72rem;">TỔNG TIỀN PHÒNG</div>
                  <div class="fs-5 fw-bold text-navy mb-1">
                    {{ formatCurrency(item.tongChiPhi) }}
                  </div>

                  <div class="text-muted small" style="font-size: 0.72rem;">TIỀN CỌC QUY ĐỊNH</div>
                  <div class="fw-bold text-teal fs-6 mb-2">
                    {{ formatCurrency(item.tienCoc) }}
                  </div>

                  <div v-if="item.trangThai === 'Chờ xác nhận'" class="small text-warning mb-2">
                    <i class="bi bi-info-circle me-1"></i> Chưa thanh toán cọc
                  </div>
                </div>
              </div>

              <!-- Khung cảnh báo thông tin yêu cầu hủy nếu có -->
              <div
                v-if="item.yeuCauHuy"
                class="mt-3 p-3 rounded-3 border cancellation-info-box"
                :class="getCancellationBoxClass(item.yeuCauHuy.trangThaiYeuCau)"
              >
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-info-circle-fill fs-5 mt-0.5 flex-shrink-0"></i>
                  <div class="flex-grow-1 small">
                    <div class="fw-bold mb-1">
                      Yêu Cầu Hủy Phòng — Trạng Thái: {{ item.yeuCauHuy.trangThaiYeuCau }}
                    </div>
                    <div class="row g-2">
                      <div class="col-12 col-md-6">
                        <strong>Lý do hủy:</strong> {{ item.yeuCauHuy.lyDoHuy || '(Không có lý do cụ thể)' }}
                      </div>
                      <div class="col-12 col-md-6">
                        <strong>Thời gian gửi:</strong> {{ item.yeuCauHuy.thoiGianYeuCau }}
                      </div>
                      <div class="col-12 col-md-6">
                        <strong>Thời hạn cọc:</strong>
                        <span v-if="item.yeuCauHuy.trongThoiHanHoanCoc" class="badge bg-success-subtle text-success ms-1">
                          Trong hạn (Hoàn cọc)
                        </span>
                        <span v-else class="badge bg-danger-subtle text-danger ms-1">
                          Quá hạn (Không hoàn cọc)
                        </span>
                      </div>
                      <div class="col-12 col-md-6">
                        <strong>Số tiền dự kiến hoàn:</strong>
                        <span class="fw-bold text-teal ms-1">
                          {{ formatCurrency(item.yeuCauHuy.soTienHoanDuKien) }}
                        </span>
                      </div>
                      <div v-if="item.yeuCauHuy.ghiChuXuLy" class="col-12 border-top pt-1 mt-1">
                        <strong>Ghi chú xử lý:</strong> {{ item.yeuCauHuy.ghiChuXuLy }}
                        <span v-if="item.yeuCauHuy.thoiGianXuLy" class="text-muted ms-1">
                          ({{ item.yeuCauHuy.thoiGianXuLy }})
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Footer: Actions -->
            <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div class="d-flex align-items-center gap-2">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-navy rounded-3 px-3 py-1.5"
                  @click="openDetailModal(item.id)"
                >
                  <i class="bi bi-eye me-1"></i> Xem chi tiết
                </button>

                <!-- Nút Thanh toán cọc (nếu đơn chưa cọc và là khách hàng) -->
                <router-link
                  v-if="!isStaff && item.trangThai === 'Chờ xác nhận'"
                  :to="{ path: '/payment', query: { maDatPhong: item.id, tienCoc: item.tienCoc } }"
                  class="btn btn-sm btn-outline-teal rounded-3 px-3 py-1.5 fw-semibold"
                >
                  <i class="bi bi-credit-card-2-front me-1"></i> Thanh toán cọc
                </router-link>
              </div>

              <div class="d-flex align-items-center gap-2">
                <!-- Nút Xử lý duyệt hủy dành cho Lễ tân / Quản lý -->
                <button
                  v-if="isStaff && item.yeuCauHuy && item.yeuCauHuy.trangThaiYeuCau === 'Chờ xử lý'"
                  type="button"
                  class="btn btn-sm btn-danger rounded-3 px-3 py-1.5 fw-bold"
                  @click="openProcessModal(item)"
                >
                  <i class="bi bi-shield-check me-1"></i> Xử lý duyệt yêu cầu hủy
                </button>

                <!-- Nút Gửi yêu cầu hủy (cho Khách hàng hoặc Lễ tân khi đơn chưa hủy và chưa có yêu cầu chờ) -->
                <button
                  v-if="canRequestCancellation(item)"
                  type="button"
                  class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1.5 fw-semibold"
                  @click="openCancelModal(item)"
                >
                  <i class="bi bi-calendar-x me-1"></i> Yêu cầu hủy phòng
                </button>

                <!-- Trạng thái đã duyệt hủy -->
                <span
                  v-else-if="item.trangThai === 'Đã hủy'"
                  class="text-muted small"
                >
                  <i class="bi bi-slash-circle me-1"></i> Đơn đã hoàn tất hủy
                </span>
                <span
                  v-else-if="item.yeuCauHuy && item.yeuCauHuy.trangThaiYeuCau === 'Chờ xử lý'"
                  class="text-warning small fw-semibold"
                >
                  <i class="bi bi-hourglass-split me-1"></i> Đang chờ Lễ tân duyệt hủy
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: XEM CHI TIẾT ĐƠN ĐẶT PHÒNG                           -->
    <!-- ============================================================== -->
    <div
      v-if="showDetailModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.65); z-index: 1050;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-navy text-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-receipt-cutoff text-teal fs-4"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">
                  Chi Tiết Đơn Đặt Phòng #DP-{{ selectedBookingDetail?.id }}
                </h5>
                <small class="opacity-75" style="font-size: 0.75rem;">
                  Ngày đặt: {{ formatDateTime(selectedBookingDetail?.ngayDat) }}
                </small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              @click="closeDetailModal"
            ></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4">
            <div v-if="isLoadingDetail" class="text-center py-4">
              <div class="spinner-border text-teal mb-2" role="status"></div>
              <div class="text-muted small">Đang tải chi tiết đơn...</div>
            </div>

            <div v-else-if="detailError" class="alert alert-danger mb-0">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ detailError }}
            </div>

            <div v-else-if="selectedBookingDetail">
              <!-- Trạng thái & Khách hàng -->
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light h-100">
                    <div class="text-muted small fw-semibold mb-1">TRẠNG THÁI HIỆN TẠI</div>
                    <div class="d-flex align-items-center gap-2">
                      <span
                        class="badge rounded-pill px-3 py-1.5 fs-6 fw-semibold"
                        :class="getBookingStatusBadge(selectedBookingDetail.trangThai)"
                      >
                        {{ selectedBookingDetail.trangThai }}
                      </span>
                      <span
                        v-if="selectedBookingDetail.yeuCauHuy"
                        class="badge bg-danger rounded-pill px-2.5 py-1 small"
                      >
                        Hủy: {{ selectedBookingDetail.yeuCauHuy.trangThaiYeuCau }}
                      </span>
                    </div>
                    <div class="small text-muted mt-2">
                      Số lượng khách: <strong>{{ selectedBookingDetail.soLuongKhach }} người</strong> · Lưu trú: <strong>{{ selectedBookingDetail.soDem }} đêm</strong>
                    </div>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light h-100">
                    <div class="text-muted small fw-semibold mb-1">THÔNG TIN KHÁCH HÀNG</div>
                    <div class="fw-bold text-navy fs-6">
                      {{ selectedBookingDetail.khachHang?.hoTen || 'Khách hàng' }}
                    </div>
                    <div class="text-muted small">
                      <i class="bi bi-telephone me-1"></i> {{ selectedBookingDetail.khachHang?.soDienThoai || 'N/A' }}
                    </div>
                    <div class="text-muted small">
                      <i class="bi bi-envelope me-1"></i> {{ selectedBookingDetail.khachHang?.email || 'N/A' }}
                    </div>
                    <div v-if="selectedBookingDetail.khachHang?.cccd" class="text-muted small">
                      <i class="bi bi-person-vcard me-1"></i> CCCD: {{ selectedBookingDetail.khachHang.cccd }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Lịch trình lưu trú -->
              <div class="p-3 rounded-3 bg-light-subtle border mb-3">
                <div class="row text-center g-2 align-items-center">
                  <div class="col-5">
                    <div class="text-muted small" style="font-size: 0.72rem;">CHECK-IN (NHẬN PHÒNG)</div>
                    <div class="fw-bold text-navy fs-6">{{ formatDateTime(selectedBookingDetail.ngayNhanPhong) }}</div>
                  </div>
                  <div class="col-2 text-muted">
                    <span class="badge bg-teal text-white">{{ selectedBookingDetail.soDem }} đêm</span>
                    <div>➔</div>
                  </div>
                  <div class="col-5">
                    <div class="text-muted small" style="font-size: 0.72rem;">CHECK-OUT (TRẢ PHÒNG)</div>
                    <div class="fw-bold text-navy fs-6">{{ formatDateTime(selectedBookingDetail.ngayTraPhong) }}</div>
                  </div>
                </div>
              </div>

              <!-- Bảng danh sách phòng chi tiết -->
              <h6 class="fw-bold text-navy mb-2">Danh Sách Phòng Đã Đặt</h6>
              <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm align-middle mb-0">
                  <thead class="table-light small text-muted">
                    <tr>
                      <th class="ps-3">Tên Phòng</th>
                      <th>Loại Phòng</th>
                      <th class="text-center">Số Khách</th>
                      <th class="text-end">Đơn Giá Áp Dụng</th>
                      <th class="text-end pe-3">Thành Tiền ({{ selectedBookingDetail.soDem }} đêm)</th>
                    </tr>
                  </thead>
                  <tbody class="small">
                    <tr v-for="r in selectedBookingDetail.danhSachPhong" :key="r.id">
                      <td class="ps-3 fw-bold text-navy">
                        <i class="bi bi-door-closed text-teal me-1"></i> {{ r.tenPhong }}
                      </td>
                      <td>{{ r.tenLoaiPhong || 'Tiêu chuẩn' }}</td>
                      <td class="text-center">{{ r.soLuongKhach }} khách</td>
                      <td class="text-end">{{ formatCurrency(r.giaPhongApDung) }}</td>
                      <td class="text-end pe-3 fw-semibold text-navy">
                        {{ formatCurrency(r.giaPhongApDung * selectedBookingDetail.soDem) }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <td colspan="4" class="text-end fw-bold ps-3">Tổng chi phí phòng:</td>
                      <td class="text-end pe-3 fw-bold text-navy fs-6">
                        {{ formatCurrency(selectedBookingDetail.tongChiPhi) }}
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="text-end fw-bold text-teal">Tiền cọc quy định:</td>
                      <td class="text-end pe-3 fw-bold text-teal fs-6">
                        {{ formatCurrency(selectedBookingDetail.tienCoc) }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <!-- Chính sách hủy & Hoàn cọc cụ thể -->
              <div v-if="selectedBookingDetail.chinhSachHuy" class="card border-0 rounded-3 bg-light p-3 mb-3">
                <h6 class="fw-bold text-navy mb-2 d-flex align-items-center gap-1.5">
                  <i class="bi bi-shield-exclamation text-teal"></i>
                  Chính Sách Hủy Đặt Phòng Của Đơn Này
                </h6>
                <div class="small">
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Hạn chót hủy được hoàn tiền cọc (Trước 24h nhận phòng):</span>
                    <strong class="text-navy">{{ formatDateTime(selectedBookingDetail.chinhSachHuy.hanHoanCoc) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Đánh giá thời điểm hiện tại:</span>
                    <span
                      v-if="selectedBookingDetail.chinhSachHuy.trongThoiHanHoanCoc"
                      class="badge bg-success-subtle text-success fw-bold"
                    >
                      <i class="bi bi-check-circle me-1"></i> Còn trong thời hạn được hoàn cọc
                    </span>
                    <span
                      v-else
                      class="badge bg-danger-subtle text-danger fw-bold"
                    >
                      <i class="bi bi-x-circle me-1"></i> Đã quá thời hạn hoàn cọc (Mất cọc)
                    </span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="text-muted">Số tiền cọc dự kiến hoàn nếu hủy:</span>
                    <strong class="text-teal fs-6">
                      {{ formatCurrency(selectedBookingDetail.chinhSachHuy.soTienHoanDuKien) }}
                    </strong>
                  </div>
                </div>
              </div>

              <!-- Lịch sử hoàn tiền (nếu có bản ghi hoan_tien) -->
              <div v-if="selectedBookingDetail.hoanTien" class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 small">
                <i class="bi bi-cash-coin fs-5"></i>
                <div>
                  <strong>Đã hoàn tiền cọc:</strong> {{ formatCurrency(selectedBookingDetail.hoanTien.soTien) }}
                  <span class="ms-1 text-muted">({{ selectedBookingDetail.hoanTien.thoiGian }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              @click="closeDetailModal"
            >
              Đóng
            </button>
            <div class="d-flex gap-2">
              <button
                v-if="canRequestCancellation(selectedBookingDetail)"
                type="button"
                class="btn btn-outline-danger rounded-3 px-3 fw-semibold"
                @click="openCancelModalFromDetail"
              >
                <i class="bi bi-calendar-x me-1"></i> Yêu cầu hủy phòng
              </button>
              <button
                v-if="isStaff && selectedBookingDetail?.yeuCauHuy?.trangThaiYeuCau === 'Chờ xử lý'"
                type="button"
                class="btn btn-danger rounded-3 px-3 fw-bold"
                @click="openProcessModalFromDetail"
              >
                <i class="bi bi-shield-check me-1"></i> Xử lý duyệt yêu cầu
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: GỬI YÊU CẦU HỦY ĐẶT PHÒNG (KHÁCH HÀNG / LỄ TÂN)        -->
    <!-- ============================================================== -->
    <div
      v-if="showCancelModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.65); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-danger text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill fs-4"></i>
              <h5 class="modal-title fw-bold mb-0">
                Yêu Cầu Hủy Đặt Phòng #DP-{{ bookingToCancel?.id }}
              </h5>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              :disabled="isSubmittingCancel"
              @click="closeCancelModal"
            ></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4">
            <div v-if="bookingToCancel">
              <!-- Tóm tắt đơn -->
              <div class="p-3 rounded-3 bg-light mb-3 small">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Thời gian lưu trú:</span>
                  <span class="fw-semibold text-navy">
                    {{ formatDateTime(bookingToCancel.ngayNhanPhong) }} ➔ {{ formatDateTime(bookingToCancel.ngayTraPhong) }}
                  </span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Tổng tiền phòng:</span>
                  <span class="fw-semibold text-navy">{{ formatCurrency(bookingToCancel.tongChiPhi) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Tiền cọc đã ghi nhận:</span>
                  <span class="fw-bold text-teal">{{ formatCurrency(bookingToCancel.tienCoc) }}</span>
                </div>
              </div>

              <!-- Phân tích chính sách cọc & Cảnh báo -->
              <div
                v-if="isWithinCancelRefundDeadline"
                class="alert alert-success border-success rounded-3 mb-3 p-3 small"
              >
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-check-circle-fill text-success fs-5 mt-0.5 flex-shrink-0"></i>
                  <div>
                    <strong class="text-success d-block mb-1">
                      Đơn đủ điều kiện hoàn lại tiền cọc!
                    </strong>
                    <span>
                      Đơn hủy trước giờ nhận phòng 24 giờ. Số tiền cọc dự kiến được hoàn lại:
                      <strong class="text-teal fs-6">{{ formatCurrency(bookingToCancel.tienCoc) }}</strong>.
                      Yêu cầu sẽ được gửi tới Lễ tân để tiếp nhận và duyệt hoàn tiền.
                    </span>
                  </div>
                </div>
              </div>

              <!-- Trường hợp QUÁ HẠN: Bắt buộc checkbox xác nhận không hoàn cọc -->
              <div
                v-else
                class="alert alert-warning border-warning rounded-3 mb-3 p-3 small"
              >
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-exclamation-octagon-fill text-warning fs-5 mt-0.5 flex-shrink-0"></i>
                  <div>
                    <strong class="text-danger d-block mb-1">
                      ĐÃ QUÁ THỜI HẠN HOÀN TIỀN CỌC QUY ĐỊNH!
                    </strong>
                    <p class="mb-2">
                      Theo quy định của khách sạn, đơn đặt phòng hủy cách giờ nhận phòng dưới 24 giờ sẽ
                      <strong class="text-danger">KHÔNG ĐƯỢC HOÀN LẠI TIỀN CỌC</strong>
                      ({{ formatCurrency(bookingToCancel.tienCoc) }}).
                    </p>
                    <div class="form-check mt-2 pt-2 border-top border-warning-subtle">
                      <input
                        id="confirmNoRefundCheck"
                        v-model="cancelForm.xacNhanKhongHoanCoc"
                        class="form-check-input"
                        type="checkbox"
                        :disabled="isSubmittingCancel"
                        required
                      />
                      <label
                        for="confirmNoRefundCheck"
                        class="form-check-label fw-bold text-navy"
                        style="cursor: pointer;"
                      >
                        Tôi đã hiểu rõ và đồng ý hủy phòng không hoàn lại tiền cọc. <span class="text-danger">*</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Lỗi validation nếu có -->
              <div v-if="cancelError" class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ cancelError }}
              </div>

              <!-- Ô nhập lý do hủy -->
              <div class="mb-3">
                <label for="cancelReasonInput" class="form-label fw-semibold small text-navy">
                  Lý do hủy đặt phòng <span class="text-muted">(tùy chọn)</span>
                </label>
                <textarea
                  id="cancelReasonInput"
                  v-model="cancelForm.lyDoHuy"
                  class="form-control"
                  rows="3"
                  placeholder="Ví dụ: Thay đổi kế hoạch công tác, bận việc đột xuất, đổi khách sạn..."
                  :disabled="isSubmittingCancel"
                ></textarea>

                <!-- Gợi ý nhanh lý do -->
                <div class="d-flex flex-wrap gap-1 mt-2">
                  <span class="text-muted small me-1">Gợi ý nhanh:</span>
                  <button
                    type="button"
                    class="badge bg-light text-muted border text-decoration-none btn btn-sm p-1"
                    @click="cancelForm.lyDoHuy = 'Thay đổi kế hoạch công tác'"
                  >
                    Công tác đột xuất
                  </button>
                  <button
                    type="button"
                    class="badge bg-light text-muted border text-decoration-none btn btn-sm p-1"
                    @click="cancelForm.lyDoHuy = 'Có việc bận gia đình đột xuất'"
                  >
                    Việc gia đình
                  </button>
                  <button
                    type="button"
                    class="badge bg-light text-muted border text-decoration-none btn btn-sm p-1"
                    @click="cancelForm.lyDoHuy = 'Lý do sức khỏe / thời tiết'"
                  >
                    Sức khỏe / Thời tiết
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              :disabled="isSubmittingCancel"
              @click="closeCancelModal"
            >
              Hủy bỏ
            </button>
            <button
              type="button"
              class="btn btn-danger rounded-3 px-4 fw-semibold"
              :disabled="isCancelSubmitDisabled"
              @click="submitCancellationRequest"
            >
              <span v-if="isSubmittingCancel" class="spinner-border spinner-border-sm me-1" role="status"></span>
              <i v-else class="bi bi-send-check me-1"></i>
              Xác nhận gửi yêu cầu hủy
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: LỄ TÂN / QUẢN LÝ XỬ LÝ DUYỆT HỦY                      -->
    <!-- ============================================================== -->
    <div
      v-if="showProcessModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.65); z-index: 1060;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-shield-check text-teal fs-4"></i>
              <h5 class="modal-title fw-bold mb-0">
                Xử Lý Yêu Cầu Hủy Đặt Phòng #DP-{{ bookingToProcess?.id }}
              </h5>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              :disabled="isSubmittingProcess"
              @click="closeProcessModal"
            ></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4">
            <div v-if="bookingToProcess">
              <!-- Tóm tắt yêu cầu từ khách -->
              <div class="p-3 rounded-3 bg-light mb-3 small">
                <div class="fw-bold text-navy mb-2">THÔNG TIN YÊU CẦU TỪ KHÁCH HÀNG:</div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Khách hàng:</span>
                  <strong class="text-navy">{{ bookingToProcess.khachHang?.hoTen || 'Khách hàng' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Số điện thoại:</span>
                  <span class="text-navy">{{ bookingToProcess.khachHang?.soDienThoai || 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Thời gian gửi:</span>
                  <span class="text-navy">{{ bookingToProcess.yeuCauHuy?.thoiGianYeuCau }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Lý do hủy của khách:</span>
                  <span class="text-navy fw-medium">{{ bookingToProcess.yeuCauHuy?.lyDoHuy || '(Trống)' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Thời hạn hoàn cọc:</span>
                  <span v-if="bookingToProcess.yeuCauHuy?.trongThoiHanHoanCoc" class="badge bg-success-subtle text-success">
                    Hợp lệ hoàn cọc
                  </span>
                  <span v-else class="badge bg-danger-subtle text-danger">
                    Quá hạn (Khách đã cam kết mất cọc)
                  </span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">Tiền cọc ban đầu:</span>
                  <strong class="text-teal fs-6">{{ formatCurrency(bookingToProcess.tienCoc) }}</strong>
                </div>
              </div>

              <!-- Lựa chọn hành động duyệt / từ chối -->
              <div class="mb-3">
                <label class="form-label fw-bold text-navy small">Hành động xử lý: <span class="text-danger">*</span></label>
                <div class="d-flex gap-2">
                  <div class="form-check card p-2.5 flex-fill cursor-pointer" :class="{ 'border-success bg-success-subtle': processForm.trangThai === 'Đã duyệt' }">
                    <input
                      id="actionApprove"
                      v-model="processForm.trangThai"
                      class="form-check-input"
                      type="radio"
                      value="Đã duyệt"
                      name="processAction"
                      :disabled="isSubmittingProcess"
                    />
                    <label class="form-check-label fw-semibold text-navy small" for="actionApprove">
                      <i class="bi bi-check-circle-fill text-success me-1"></i>
                      Duyệt Hủy Đơn
                      <span class="d-block text-muted fw-normal" style="font-size: 0.7rem;">
                        Giải phóng phòng & Hoàn tiền cọc
                      </span>
                    </label>
                  </div>

                  <div class="form-check card p-2.5 flex-fill cursor-pointer" :class="{ 'border-danger bg-danger-subtle': processForm.trangThai === 'Từ chối' }">
                    <input
                      id="actionReject"
                      v-model="processForm.trangThai"
                      class="form-check-input"
                      type="radio"
                      value="Từ chối"
                      name="processAction"
                      :disabled="isSubmittingProcess"
                    />
                    <label class="form-check-label fw-semibold text-navy small" for="actionReject">
                      <i class="bi bi-x-circle-fill text-danger me-1"></i>
                      Từ Chối Yêu Cầu
                      <span class="d-block text-muted fw-normal" style="font-size: 0.7rem;">
                        Giữ nguyên trạng thái đơn
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Nếu Duyệt: Nhập số tiền hoàn -->
              <div v-if="processForm.trangThai === 'Đã duyệt'" class="mb-3">
                <label for="refundAmountInput" class="form-label fw-semibold text-navy small">
                  Số tiền hoàn cọc (VNĐ):
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-light text-teal">
                    <i class="bi bi-cash"></i>
                  </span>
                  <input
                    id="refundAmountInput"
                    v-model.number="processForm.soTienHoan"
                    type="number"
                    min="0"
                    :max="bookingToProcess.tienCoc"
                    class="form-control"
                    :disabled="isSubmittingProcess"
                  />
                  <span class="input-group-text bg-light">VNĐ</span>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">
                  {{
                    bookingToProcess.yeuCauHuy?.trongThoiHanHoanCoc
                      ? 'Đơn trong thời hạn: Tự động điền 100% tiền cọc hợp lệ.'
                      : 'Đơn quá thời hạn: Mặc định là 0 VNĐ, có thể tùy chỉnh nếu áp dụng chính sách ưu đãi riêng.'
                  }}
                </small>
              </div>

              <!-- Ghi chú xử lý -->
              <div class="mb-3">
                <label for="processNoteInput" class="form-label fw-semibold text-navy small">
                  {{ processForm.trangThai === 'Đã duyệt' ? 'Ghi chú xử lý / Biên nhận hoàn tiền:' : 'Lý do từ chối yêu cầu hủy:' }}
                </label>
                <textarea
                  id="processNoteInput"
                  v-model="processForm.ghiChu"
                  class="form-control"
                  rows="2"
                  :placeholder="processForm.trangThai === 'Đã duyệt' ? 'Ví dụ: Đã kiểm tra đủ điều kiện, chuyển khoản hoàn cọc cho khách...' : 'Ví dụ: Đã quá sát giờ nhận phòng, khách sạn không thể hỗ trợ hủy...'"
                  :disabled="isSubmittingProcess"
                ></textarea>
              </div>

              <!-- Lỗi xử lý nếu có -->
              <div v-if="processError" class="alert alert-danger py-2 px-3 small rounded-3 mb-0">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ processError }}
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              :disabled="isSubmittingProcess"
              @click="closeProcessModal"
            >
              Hủy bỏ
            </button>
            <button
              type="button"
              class="btn rounded-3 px-4 fw-semibold"
              :class="processForm.trangThai === 'Đã duyệt' ? 'btn-success text-white' : 'btn-danger text-white'"
              :disabled="isSubmittingProcess"
              @click="submitProcessCancellation"
            >
              <span v-if="isSubmittingProcess" class="spinner-border spinner-border-sm me-1" role="status"></span>
              <i v-else :class="processForm.trangThai === 'Đã duyệt' ? 'bi bi-check2-circle me-1' : 'bi bi-x-circle me-1'"></i>
              Xác nhận xử lý
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification (Floating bottom-right) -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
      <div
        class="toast align-items-center border-0 show shadow-lg rounded-3"
        :class="toast.type === 'success' ? 'bg-navy text-white' : 'bg-danger text-white'"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        v-if="toast.show"
      >
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-2">
            <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-teal fs-5' : 'bi bi-exclamation-octagon-fill fs-5'"></i>
            <span>{{ toast.message }}</span>
          </div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            aria-label="Close"
            @click="toast.show = false"
          ></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// 1. Trạng thái người dùng & Token
const token = ref('')
const currentUser = ref({})

// Phân biệt vai trò
const isStaff = computed(() => {
  const role = (currentUser.value.vaiTro || '').toLowerCase().trim()
  return ['lễ tân', 'quản lý', 'admin', 'receptionist', 'manager'].includes(role)
})

// 2. Danh sách đơn đặt phòng & Loading
const bookings = ref([])
const isLoading = ref(false)
const fetchError = ref('')

// 3. Bộ lọc & Tìm kiếm
const searchQuery = ref('')
const activeFilter = ref('all')

// 4. Modal Chi Tiết
const showDetailModal = ref(false)
const selectedBookingDetail = ref(null)
const isLoadingDetail = ref(false)
const detailError = ref('')

// 5. Modal Yêu Cầu Hủy (Khách hàng & Lễ tân)
const showCancelModal = ref(false)
const bookingToCancel = ref(null)
const cancelForm = reactive({
  lyDoHuy: '',
  xacNhanKhongHoanCoc: false,
})
const isSubmittingCancel = ref(false)
const cancelError = ref('')

// 6. Modal Xử Lý Yêu Cầu Hủy (Lễ tân / Quản lý)
const showProcessModal = ref(false)
const bookingToProcess = ref(null)
const processForm = reactive({
  trangThai: 'Đã duyệt',
  ghiChu: '',
  soTienHoan: 0,
})
const isSubmittingProcess = ref(false)
const processError = ref('')

// 7. Toast Notification
const toast = reactive({
  show: false,
  message: '',
  type: 'success',
})

const showToast = (message, type = 'success') => {
  toast.message = message
  toast.type = type
  toast.show = true
  setTimeout(() => {
    toast.show = false
  }, 4000)
}

// 8. Định dạng Tiền tệ & Thời gian
const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0)
}

const formatDateTime = (val) => {
  if (!val) return 'N/A'
  const d = new Date(val)
  if (isNaN(d.getTime())) return val
  return d.toLocaleString('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

// 9. API URL Base & Headers
const getHeaders = () => {
  return {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json',
  }
}

// 10. Load Danh Sách Đặt Phòng từ API
const fetchBookings = async () => {
  if (!token.value) {
    fetchError.value = 'Bạn chưa đăng nhập. Vui lòng đăng nhập để xem lịch sử đặt phòng.'
    return
  }

  isLoading.value = true
  fetchError.value = ''

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await axios.get(`${apiBase}/api/v1/bookings`, {
      headers: getHeaders(),
    })

    if (res.data && res.data.success) {
      bookings.value = res.data.data || []
    } else {
      bookings.value = []
    }
  } catch (err) {
    console.error('Fetch bookings error:', err)
    if (err.response?.status === 401) {
      fetchError.value = 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.'
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
    } else if (err.response?.status === 403) {
      fetchError.value = 'Bạn không có quyền truy cập danh sách đặt phòng này.'
    } else {
      fetchError.value = err.response?.data?.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.'
    }
  } finally {
    isLoading.value = false
  }
}

// 11. Computed: Danh sách sau khi lọc & tìm kiếm
const filteredBookings = computed(() => {
  return bookings.value.filter((item) => {
    // 1. Lọc theo trạng thái tab
    if (activeFilter.value === 'cancellation_pending') {
      if (!item.yeuCauHuy || item.yeuCauHuy.trangThaiYeuCau !== 'Chờ xử lý') {
        return false
      }
    } else if (activeFilter.value !== 'all') {
      if (item.trangThai !== activeFilter.value) {
        return false
      }
    }

    // 2. Lọc theo ô tìm kiếm
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const matchId = String(item.id).includes(q) || `dp-${item.id}`.includes(q)
      const matchCustomer = item.khachHang && (
        (item.khachHang.hoTen || '').toLowerCase().includes(q) ||
        (item.khachHang.soDienThoai || '').includes(q) ||
        (item.khachHang.email || '').toLowerCase().includes(q)
      )
      const matchRoom = (item.danhSachPhong || []).some((r) =>
        (r.tenPhong || '').toLowerCase().includes(q) ||
        (r.tenLoaiPhong || '').toLowerCase().includes(q)
      )
      return matchId || matchCustomer || matchRoom
    }

    return true
  })
})

// Thống kê nhanh
const activeBookingsCount = computed(() => {
  return bookings.value.filter((b) => ['Chờ xác nhận', 'Đã cọc', 'Đã xác nhận'].includes(b.trangThai)).length
})

const pendingCancelCount = computed(() => {
  return bookings.value.filter((b) => b.yeuCauHuy && b.yeuCauHuy.trangThaiYeuCau === 'Chờ xử lý').length
})

const totalDepositAmount = computed(() => {
  return bookings.value.reduce((acc, curr) => acc + (Number(curr.tienCoc) || 0), 0)
})

const resetFilters = () => {
  searchQuery.value = ''
  activeFilter.value = 'all'
}

// 12. Điều kiện có thể gửi yêu cầu hủy
const canRequestCancellation = (item) => {
  if (!item) return false
  const validStatus = ['Chờ xác nhận', 'Đã cọc', 'Đã xác nhận'].includes(item.trangThai)
  const hasPending = item.yeuCauHuy && item.yeuCauHuy.trangThaiYeuCau === 'Chờ xử lý'
  return validStatus && !hasPending
}

// 13. Kiểm tra thời hạn hoàn cọc (trước 24h)
const isWithinCancelRefundDeadline = computed(() => {
  if (!bookingToCancel.value) return false
  if (bookingToCancel.value.chinhSachHuy?.trongThoiHanHoanCoc !== undefined) {
    return Boolean(bookingToCancel.value.chinhSachHuy.trongThoiHanHoanCoc)
  }
  const checkIn = new Date(bookingToCancel.value.ngayNhanPhong).getTime()
  const deadline = checkIn - 24 * 60 * 60 * 1000
  return Date.now() <= deadline
})

const isCancelSubmitDisabled = computed(() => {
  if (isSubmittingCancel.value) return true
  // Nếu ngoài thời hạn hoàn cọc thì bắt buộc phải tích chọn xác nhận không hoàn cọc
  if (!isWithinCancelRefundDeadline.value && !cancelForm.xacNhanKhongHoanCoc) {
    return true
  }
  return false
})

// 14. Thao tác Modal Chi Tiết
const openDetailModal = async (id) => {
  showDetailModal.value = true
  isLoadingDetail.value = true
  detailError.value = ''
  selectedBookingDetail.value = null

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await axios.get(`${apiBase}/api/v1/bookings/${id}`, {
      headers: getHeaders(),
    })

    if (res.data && res.data.success) {
      selectedBookingDetail.value = res.data.data
    } else {
      detailError.value = 'Không thể lấy thông tin chi tiết.'
    }
  } catch (err) {
    console.error('Fetch booking detail error:', err)
    detailError.value = err.response?.data?.message || 'Lỗi khi tải chi tiết đơn đặt phòng.'
  } finally {
    isLoadingDetail.value = false
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedBookingDetail.value = null
}

const openCancelModalFromDetail = () => {
  if (!selectedBookingDetail.value) return
  bookingToCancel.value = selectedBookingDetail.value
  closeDetailModal()
  openCancelModal(bookingToCancel.value)
}

const openProcessModalFromDetail = () => {
  if (!selectedBookingDetail.value) return
  bookingToProcess.value = selectedBookingDetail.value
  closeDetailModal()
  openProcessModal(bookingToProcess.value)
}

// 15. Thao tác Modal Yêu Cầu Hủy (Cancellation Request)
const openCancelModal = (item) => {
  bookingToCancel.value = item
  cancelForm.lyDoHuy = ''
  cancelForm.xacNhanKhongHoanCoc = false
  cancelError.value = ''
  showCancelModal.value = true
}

const closeCancelModal = () => {
  if (isSubmittingCancel.value) return
  showCancelModal.value = false
  bookingToCancel.value = null
}

const submitCancellationRequest = async () => {
  if (!bookingToCancel.value) return

  // Kiểm tra điều kiện phía client: nếu quá hạn và chưa tick checkbox
  if (!isWithinCancelRefundDeadline.value && !cancelForm.xacNhanKhongHoanCoc) {
    cancelError.value = 'Quý khách vui lòng đánh dấu xác nhận chấp nhận không hoàn lại tiền cọc.'
    return
  }

  isSubmittingCancel.value = true
  cancelError.value = ''

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const id = bookingToCancel.value.id
    const payload = {
      lyDoHuy: cancelForm.lyDoHuy || null,
      xacNhanKhongHoanCoc: cancelForm.xacNhanKhongHoanCoc,
    }

    const res = await axios.post(`${apiBase}/api/v1/bookings/${id}/cancellation-request`, payload, {
      headers: getHeaders(),
    })

    if (res.data && res.data.success) {
      showToast('Gửi yêu cầu hủy đặt phòng thành công! Yêu cầu đang được chờ xử lý.', 'success')
      closeCancelModal()
      await fetchBookings()
    }
  } catch (err) {
    console.error('Cancellation request error:', err)
    if (err.response?.status === 409) {
      cancelError.value = err.response.data.message || 'Đơn đặt phòng đã gửi yêu cầu hủy hoặc không thể hủy vào thời điểm này.'
    } else if (err.response?.status === 422) {
      const errors = err.response.data.errors
      if (errors?.xacNhanKhongHoanCoc) {
        cancelError.value = errors.xacNhanKhongHoanCoc[0]
      } else {
        cancelError.value = err.response.data.message || 'Dữ liệu yêu cầu hủy không hợp lệ.'
      }
    } else if (err.response?.status === 403) {
      cancelError.value = 'Bạn không có quyền gửi yêu cầu hủy cho đơn đặt phòng này.'
    } else {
      cancelError.value = err.response?.data?.message || 'Có lỗi xảy ra khi gửi yêu cầu hủy. Vui lòng thử lại.'
    }
  } finally {
    isSubmittingCancel.value = false
  }
}

// 16. Thao tác Modal Xử Lý Yêu Cầu Hủy (Lễ tân / Quản lý)
const openProcessModal = (item) => {
  bookingToProcess.value = item
  processForm.trangThai = 'Đã duyệt'
  processForm.ghiChu = ''

  // Điền trước số tiền hoàn: nếu đơn trong hạn, điền tiền cọc; nếu ngoài hạn, điền 0
  const isWithin = item.yeuCauHuy?.trongThoiHanHoanCoc ?? false
  processForm.soTienHoan = isWithin ? Number(item.tienCoc) || 0 : 0

  processError.value = ''
  showProcessModal.value = true
}

const closeProcessModal = () => {
  if (isSubmittingProcess.value) return
  showProcessModal.value = false
  bookingToProcess.value = null
}

const submitProcessCancellation = async () => {
  if (!bookingToProcess.value) return

  isSubmittingProcess.value = true
  processError.value = ''

  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const id = bookingToProcess.value.id
    const payload = {
      trangThai: processForm.trangThai,
      ghiChu: processForm.ghiChu || null,
      soTienHoan: processForm.trangThai === 'Đã duyệt' ? Number(processForm.soTienHoan) : 0,
    }

    const res = await axios.put(`${apiBase}/api/v1/bookings/${id}/cancellation-status`, payload, {
      headers: getHeaders(),
    })

    if (res.data && res.data.success) {
      const msg = processForm.trangThai === 'Đã duyệt'
        ? 'Duyệt yêu cầu hủy thành công. Đơn phòng đã chuyển sang Đã hủy và phòng đã được giải phóng!'
        : 'Đã từ chối yêu cầu hủy đặt phòng. Đơn phòng giữ nguyên trạng thái.'
      showToast(msg, 'success')
      closeProcessModal()
      await fetchBookings()
    }
  } catch (err) {
    console.error('Process cancellation error:', err)
    if (err.response?.status === 409) {
      processError.value = err.response.data.message || 'Trạng thái đơn đặt phòng đã thay đổi hoặc đã xử lý trước đó.'
    } else if (err.response?.status === 403) {
      processError.value = 'Chỉ Lễ tân hoặc Quản lý mới có quyền phê duyệt yêu cầu hủy.'
    } else {
      processError.value = err.response?.data?.message || 'Có lỗi xảy ra khi xử lý yêu cầu. Vui lòng thử lại.'
    }
  } finally {
    isSubmittingProcess.value = false
  }
}

// 17. Helpers hiển thị Badge trạng thái
const getBookingStatusBadge = (status) => {
  switch (status) {
    case 'Chờ xác nhận':
      return 'bg-warning-subtle text-warning border border-warning-subtle'
    case 'Đã cọc':
      return 'bg-info-subtle text-info border border-info-subtle'
    case 'Đã xác nhận':
      return 'bg-success-subtle text-success border border-success-subtle'
    case 'Đang ở':
      return 'bg-primary-subtle text-primary border border-primary-subtle'
    case 'Đã trả':
    case 'Đã hoàn thành':
      return 'bg-secondary-subtle text-secondary border border-secondary-subtle'
    case 'Đã hủy':
      return 'bg-danger-subtle text-danger border border-danger-subtle'
    default:
      return 'bg-light text-muted border'
  }
}

const getBookingStatusIcon = (status) => {
  switch (status) {
    case 'Chờ xác nhận':
      return 'bi bi-hourglass-split'
    case 'Đã cọc':
      return 'bi bi-credit-card-2-front'
    case 'Đã xác nhận':
      return 'bi bi-check2-circle'
    case 'Đang ở':
      return 'bi bi-building-check'
    case 'Đã trả':
    case 'Đã hoàn thành':
      return 'bi bi-door-closed'
    case 'Đã hủy':
      return 'bi bi-x-circle'
    default:
      return 'bi bi-circle'
  }
}

const getCancellationBadgeClass = (status) => {
  switch (status) {
    case 'Chờ xử lý':
      return 'bg-danger text-white pulse-badge'
    case 'Đã duyệt':
      return 'bg-danger-subtle text-danger border border-danger-subtle'
    case 'Từ chối':
      return 'bg-secondary-subtle text-secondary border border-secondary-subtle'
    default:
      return 'bg-light text-muted'
  }
}

const getCancellationBoxClass = (status) => {
  switch (status) {
    case 'Chờ xử lý':
      return 'bg-warning-subtle border-warning text-dark'
    case 'Đã duyệt':
      return 'bg-danger-subtle border-danger text-danger'
    case 'Từ chối':
      return 'bg-light border-secondary text-muted'
    default:
      return 'bg-light'
  }
}

// 18. Quick Switch User (Kiểm thử nhanh giữa Khách hàng và Lễ tân)
const quickSwitchUser = async (username, password, roleName) => {
  isLoading.value = true
  try {
    const apiBase = import.meta.env.VITE_API_URL || ''
    const res = await axios.post(`${apiBase}/api/v1/auth/login`, {
      tenDangNhap: username,
      matKhau: password,
    })

    if (res.data && res.data.success) {
      const data = res.data.data
      localStorage.setItem('access_token', data.token)
      localStorage.setItem('token_type', data.token_type || 'Bearer')
      localStorage.setItem('user', JSON.stringify(data.user))
      localStorage.setItem('vaiTro', data.user.vaiTro)

      token.value = data.token
      currentUser.value = data.user

      showToast(`Đã chuyển sang tài khoản ${roleName} (${data.user.hoTen || username})`, 'success')
      await fetchBookings()
    }
  } catch (err) {
    console.error('Quick switch user error:', err)
    showToast(`Không thể đăng nhập tài khoản ${roleName}. Vui lòng thử lại.`, 'danger')
  } finally {
    isLoading.value = false
  }
}

// 19. Khởi chạy khi mounted
onMounted(async () => {
  token.value = localStorage.getItem('access_token') || ''
  try {
    currentUser.value = JSON.parse(localStorage.getItem('user') || '{}')
  } catch {
    currentUser.value = {}
  }

  // Tự động nạp danh sách đặt phòng nếu đã có token
  if (token.value) {
    await fetchBookings()
  } else {
    // Nếu chưa đăng nhập, tự động kích hoạt tài khoản khách hàng demo để tiện kiểm thử ngay
    await quickSwitchUser('nguyenvanminh', '123456', 'Khách hàng')
  }
})
</script>

<style scoped>
/* Design Tokens & Colors */
.text-navy {
  color: #1e3a5f !important;
}

.bg-navy {
  background-color: #1e3a5f !important;
}

.btn-navy {
  background-color: #1e3a5f;
  color: #ffffff;
  border-color: #1e3a5f;
}

.btn-navy:hover {
  background-color: #152942;
  color: #ffffff;
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

.bg-teal-subtle {
  background-color: #ccfbf1 !important;
}

.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
  color: #ffffff;
}

.btn-teal:hover {
  background-color: #115e59;
  border-color: #115e59;
  color: #ffffff;
}

.btn-outline-teal {
  color: #0f766e;
  border-color: #0f766e;
}

.btn-outline-teal:hover {
  background-color: #0f766e;
  color: #ffffff;
}

/* Header & Stat Cards */
.header-icon-box {
  width: 54px;
  height: 54px;
}

.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

/* Booking Card Highlights */
.booking-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border-left: 4px solid #cbd5e1;
}

.booking-card:hover {
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.08) !important;
}

.border-start-active {
  border-left-color: #0f766e !important;
}

.border-start-cancellation {
  border-left-color: #dc3545 !important;
}

.border-start-cancelled {
  border-left-color: #64748b !important;
  opacity: 0.92;
}

.booking-code {
  letter-spacing: 0.5px;
}

.stay-info-box {
  border: 1px dashed #cbd5e1;
}

.empty-icon-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
}

.room-chip {
  background-color: #f1f5f9;
  border-color: #e2e8f0 !important;
}

/* Animation & Pulse */
.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}

.pulse-badge {
  animation: pulse-danger 1.8s infinite;
}

@keyframes pulse-danger {
  0% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.6);
  }
  70% {
    box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
  }
}

.cursor-pointer {
  cursor: pointer;
}
</style>
