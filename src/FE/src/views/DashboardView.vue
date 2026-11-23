<template>
  <div class="dashboard-report-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- 1. Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3 d-print-none">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/admin/dashboard" class="text-decoration-none text-teal">
              Quản trị PMS
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Báo cáo & Thống kê doanh thu, công suất (UC19)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-graph-up-arrow fs-3 text-warning"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Báo Cáo & Thống Kê Quản Trị</h2>
              <p class="text-muted mb-0 small">
                Tổng hợp doanh thu dòng tiền thực tế, tình hình đặt phòng & công suất sử dụng phòng khách sạn (Phân hệ PMS - UC19 / UC26 / FR26).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end d-print-none">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isManager ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isManager ? 'bi bi-shield-check me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Quản lý' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isManager ? 'Toàn quyền truy cập báo cáo tài chính & vận hành' : 'Chỉ Quản lý mới có quyền truy cập báo cáo' }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Quick Test Toolbar (Chuyển đổi vai trò kiểm thử trực tiếp) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi tài khoản:</span>
              <button
                type="button"
                class="btn btn-sm rounded-3"
                :class="currentUser.tenDangNhap === 'admin' ? 'btn-secondary text-white' : 'btn-outline-secondary'"
                :disabled="isLoading"
                @click="quickSwitchUser('admin', '123456', 'Quản lý')"
              >
                <i class="bi bi-shield-lock me-1"></i> Quản lý (admin)
              </button>
              <button
                type="button"
                class="btn btn-sm rounded-3"
                :class="currentUser.tenDangNhap === 'letan01' ? 'btn-danger text-white' : 'btn-outline-danger'"
                :disabled="isLoading"
                @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
              >
                <i class="bi bi-slash-circle me-1"></i> Lễ tân (Thử 403)
              </button>
              <button
                type="button"
                class="btn btn-sm rounded-3"
                :class="currentUser.tenDangNhap === 'nguyenvanminh' ? 'btn-danger text-white' : 'btn-outline-danger'"
                :disabled="isLoading"
                @click="quickSwitchUser('nguyenvanminh', '123456', 'Khách hàng')"
              >
                <i class="bi bi-slash-circle me-1"></i> Khách hàng (Thử 403)
              </button>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3 px-3"
                :disabled="isLoading"
                @click="printReport"
              >
                <i class="bi bi-printer me-1"></i> In báo cáo
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-navy rounded-3 px-3"
                :disabled="isLoading"
                @click="fetchReportData"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Màn hình 403 Forbidden khi người dùng không phải Quản lý -->
      <div v-if="accessDenied" class="card border-0 shadow-sm rounded-4 mb-4 bg-white text-center py-5">
        <div class="card-body">
          <div class="avatar-denied mx-auto bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-shield-x fs-1"></i>
          </div>
          <h4 class="fw-bold text-navy mb-2">Truy Cập Bị Từ Chối (403 Forbidden)</h4>
          <p class="text-muted mx-auto mb-4" style="max-width: 550px;">
            {{ accessDeniedMessage || 'Bạn không có quyền truy cập báo cáo thống kê. Chức năng chỉ dành riêng cho Quản lý khách sạn.' }}
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button
              type="button"
              class="btn btn-teal text-white rounded-3 px-4"
              @click="quickSwitchUser('admin', '123456', 'Quản lý')"
            >
              <i class="bi bi-shield-lock me-1"></i> Đăng nhập tài khoản Quản lý (admin)
            </button>
          </div>
        </div>
      </div>

      <!-- Nội dung Báo cáo khi có quyền Quản lý -->
      <div v-else>
        <!-- 3. Bộ lọc thời gian & Cấu hình báo cáo -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
          <div class="card-body p-4">
            <!-- Tabs phân loại báo cáo -->
            <ul class="nav nav-pills custom-pills mb-3 border-bottom pb-3">
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link fw-semibold rounded-3"
                  :class="{ active: currentTab === 'dashboard' }"
                  @click="switchTab('dashboard')"
                >
                  <i class="bi bi-grid-1x2-fill me-1.5"></i> Tổng quan Dashboard
                </button>
              </li>
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link fw-semibold rounded-3"
                  :class="{ active: currentTab === 'revenue' }"
                  @click="switchTab('revenue')"
                >
                  <i class="bi bi-cash-stack me-1.5"></i> Doanh thu thực thu
                </button>
              </li>
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link fw-semibold rounded-3"
                  :class="{ active: currentTab === 'bookings' }"
                  @click="switchTab('bookings')"
                >
                  <i class="bi bi-calendar2-check-fill me-1.5"></i> Đặt phòng & Lưu trú
                </button>
              </li>
              <li class="nav-item">
                <button
                  type="button"
                  class="nav-link fw-semibold rounded-3"
                  :class="{ active: currentTab === 'occupancy' }"
                  @click="switchTab('occupancy')"
                >
                  <i class="bi bi-pie-chart-fill me-1.5"></i> Công suất phòng
                </button>
              </li>
            </ul>

            <!-- Form khoảng thời gian & bộ lọc -->
            <div class="row g-3 align-items-end">
              <!-- Ngày bắt đầu -->
              <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Từ ngày (Bắt đầu) <span class="text-danger">*</span>
                </label>
                <input
                  type="date"
                  class="form-control bg-light"
                  v-model="filters.tuNgay"
                  :class="{ 'is-invalid': validationErrors.tuNgay }"
                />
                <div v-if="validationErrors.tuNgay" class="invalid-feedback small">
                  {{ validationErrors.tuNgay[0] }}
                </div>
              </div>

              <!-- Ngày kết thúc -->
              <div class="col-lg-3 col-md-6">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Đến ngày (Kết thúc) <span class="text-danger">*</span>
                </label>
                <input
                  type="date"
                  class="form-control bg-light"
                  v-model="filters.denNgay"
                  :class="{ 'is-invalid': validationErrors.denNgay }"
                />
                <div v-if="validationErrors.denNgay" class="invalid-feedback small">
                  {{ validationErrors.denNgay[0] }}
                </div>
              </div>

              <!-- Đơn vị nhóm thời gian -->
              <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold text-navy mb-1">Nhóm theo</label>
                <select class="form-select bg-light" v-model="filters.nhomTheo">
                  <option value="ngay">Theo Ngày</option>
                  <option value="thang">Theo Tháng</option>
                  <option value="nam">Theo Năm</option>
                </select>
              </div>

              <!-- Lọc theo loại phòng (chỉ hiện khi tab occupancy hoặc dashboard) -->
              <div class="col-lg-2 col-md-4">
                <label class="form-label small fw-semibold text-navy mb-1">Hạng phòng</label>
                <select class="form-select bg-light" v-model="filters.maLoaiPhong">
                  <option value="">Tất cả hạng phòng</option>
                  <option v-for="rt in roomTypeList" :key="rt.id" :value="rt.id">
                    {{ rt.tenLoai }}
                  </option>
                </select>
              </div>

              <!-- Nút Thao tác -->
              <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button
                  type="button"
                  class="btn btn-navy text-white rounded-3 flex-grow-1"
                  :disabled="isLoading"
                  @click="handleSearch"
                >
                  <i class="bi bi-funnel-fill me-1"></i> Xem báo cáo
                </button>
                <button
                  type="button"
                  class="btn btn-outline-secondary rounded-3"
                  :disabled="isLoading"
                  title="Đặt lại khoảng thời gian"
                  @click="resetFilters"
                >
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>
              </div>
            </div>

            <!-- Phím chọn nhanh khoảng thời gian (Quick Date Presets) -->
            <div class="d-flex flex-wrap align-items-center gap-2 pt-3 mt-3 border-top small text-muted">
              <span class="fw-semibold text-navy">
                <i class="bi bi-clock-history me-1"></i> Chọn nhanh:
              </span>
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-navy"
                @click="applyPreset('today')"
              >
                Hôm nay
              </button>
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-navy"
                @click="applyPreset('last7days')"
              >
                7 ngày qua
              </button>
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-navy"
                @click="applyPreset('thisMonth')"
              >
                Tháng này
              </button>
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-navy"
                @click="applyPreset('lastMonth')"
              >
                Tháng trước
              </button>
              <button
                type="button"
                class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 text-navy"
                @click="applyPreset('thisYear')"
              >
                Năm nay
              </button>
            </div>
          </div>
        </div>

        <!-- 4. Loading Spinner khi tải dữ liệu -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-navy mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Đang tổng hợp báo cáo...</span>
          </div>
          <p class="text-muted fw-semibold">Đang tổng hợp dữ liệu doanh thu và công suất phòng...</p>
        </div>

        <!-- 5. Thông báo khi không có dữ liệu phát sinh (Empty State) -->
        <div v-else-if="!hasReportData" class="card border-0 shadow-sm rounded-4 mb-4 bg-white text-center py-5">
          <div class="card-body">
            <div class="empty-icon-box mx-auto bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mb-3">
              <i class="bi bi-clipboard2-x fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-navy mb-2">{{ emptyMessage || 'Không có dữ liệu phát sinh trong khoảng thời gian này.' }}</h5>
            <p class="text-muted mx-auto mb-4" style="max-width: 520px;">
              Hệ thống không tìm thấy giao dịch hóa đơn hoặc đơn đặt phòng nào trong khoảng thời gian từ
              <strong>{{ formatDate(filters.tuNgay) }}</strong> đến <strong>{{ formatDate(filters.denNgay) }}</strong>.
              Bạn có thể mở rộng khoảng thời gian hoặc nhấn chọn nhanh các mốc thời gian khác.
            </p>
            <button type="button" class="btn btn-teal text-white rounded-3 px-4" @click="applyPreset('thisMonth')">
              <i class="bi bi-calendar3 me-1"></i> Xem tháng hiện tại
            </button>
          </div>
        </div>

        <!-- 6. Vùng Hiển thị Dữ liệu Báo Cáo Thống Kê -->
        <div v-else>
          <!-- 6.1. Thẻ KPI Trọng Yếu (KPI Cards) -->
          <div class="row g-3 mb-4">
            <!-- Tổng Doanh Thu (BR04.6: Hóa đơn đã thanh toán) -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 kpi-card bg-white h-100">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">TỔNG DOANH THU THỰC THU</span>
                    <div class="kpi-icon-box bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                  </div>
                  <h3 class="fw-bold text-success mb-1">
                    {{ formatCurrency(reportData.kpi?.tongDoanhThu || 0) }}
                  </h3>
                  <div class="small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-receipt"></i>
                    <span>{{ reportData.doanhThu?.soHoaDon || 0 }} hóa đơn đã thanh toán</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tình hình Đặt phòng -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 kpi-card bg-white h-100">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">ĐƠN ĐẶT PHÒNG PHÁT SINH</span>
                    <div class="kpi-icon-box bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-calendar-check fs-5"></i>
                    </div>
                  </div>
                  <h3 class="fw-bold text-navy mb-1">
                    {{ reportData.kpi?.tongDonDat || 0 }} <span class="fs-6 fw-normal text-muted">đơn</span>
                  </h3>
                  <div class="small text-muted d-flex justify-content-between">
                    <span>Thành công: <strong class="text-success">{{ reportData.kpi?.tongDonThanhCong || 0 }}</strong></span>
                    <span>Đã hủy: <strong class="text-danger">{{ reportData.kpi?.tongDonHuy || 0 }}</strong></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tỷ Lệ Hủy Đơn -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 kpi-card bg-white h-100">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">TỶ LỆ HỦY ĐẶT PHÒNG</span>
                    <div class="kpi-icon-box bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-x-octagon fs-5"></i>
                    </div>
                  </div>
                  <h3 class="fw-bold text-danger mb-1">
                    {{ reportData.kpi?.tyLeHuy || 0 }}%
                  </h3>
                  <div class="small text-muted">
                    <span>Tổng lượt khách: <strong>{{ reportData.kpi?.tongLuotKhach || 0 }}</strong> lượt</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Công Suất Phòng Trung Bình -->
            <div class="col-12 col-sm-6 col-xl-3">
              <div class="card border-0 shadow-sm rounded-4 kpi-card bg-white h-100">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">CÔNG SUẤT LẤP ĐẦY PHÒNG</span>
                    <div class="kpi-icon-box bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-pie-chart fs-5"></i>
                    </div>
                  </div>
                  <h3 class="fw-bold text-teal mb-1">
                    {{ reportData.kpi?.congSuatTrungBinh || 0 }}%
                  </h3>
                  <div class="small text-muted">
                    <span>{{ reportData.congSuat?.tongPhongNgayCoKhach || 0 }} / {{ reportData.congSuat?.tongPhongNgayKhaDung || 0 }} phòng-ngày</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 6.2. Biểu Đồ & Phân Tích Doanh Thu -->
          <div v-if="currentTab === 'dashboard' || currentTab === 'revenue'" class="row g-4 mb-4">
            <!-- Biểu đồ cột Doanh thu theo thời gian -->
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h5 class="fw-bold text-navy mb-0">Biểu Đồ Doanh Thu Theo Thời Gian</h5>
                    <small class="text-muted">Nhóm theo: {{ filters.nhomTheo === 'thang' ? 'Tháng' : (filters.nhomTheo === 'nam' ? 'Năm' : 'Ngày') }}</small>
                  </div>
                  <span class="badge bg-success text-white px-3 py-1.5 fw-semibold">
                    {{ formatCurrency(reportData.doanhThu?.tong || 0) }}
                  </span>
                </div>

                <!-- Custom SVG / CSS Bar Chart -->
                <div class="chart-container py-2">
                  <div v-if="revenueTimeline.length > 0" class="d-flex align-items-end gap-2 h-100 px-2 pt-4 pb-2 border-bottom">
                    <div
                      v-for="(item, idx) in revenueTimeline"
                      :key="idx"
                      class="flex-grow-1 d-flex flex-column align-items-center timeline-bar-wrapper"
                      :title="`${item.thoiGian}: ${formatCurrency(item.doanhThu)} (${item.soHoaDon} HĐ)`"
                    >
                      <small class="bar-value text-muted mb-1">{{ formatShortCurrency(item.doanhThu) }}</small>
                      <div
                        class="timeline-bar bg-teal rounded-top w-100"
                        :style="{ height: getBarHeight(item.doanhThu, maxRevenueInTimeline) }"
                      ></div>
                      <span class="bar-label small text-muted text-truncate mt-2" style="font-size: 0.7rem; max-width: 60px;">
                        {{ formatTimelineLabel(item.thoiGian) }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="text-center py-4 text-muted small">
                    Chưa có điểm dữ liệu chuỗi thời gian nào.
                  </div>
                </div>
              </div>
            </div>

            <!-- Phân tích Cơ cấu nguồn thu (Tiền phòng, Dịch vụ, Minibar, Phụ thu) -->
            <div class="col-lg-4">
              <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4">
                <h5 class="fw-bold text-navy mb-1">Cơ Cấu Nguồn Doanh Thu</h5>
                <small class="text-muted d-block mb-3">Tỷ trọng chi tiết các khoản thu</small>

                <div class="revenue-sources-list">
                  <!-- Tiền phòng -->
                  <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                      <span><i class="bi bi-door-closed-fill text-teal me-1"></i> Tiền phòng</span>
                      <span class="text-navy">{{ formatCurrency(revenueSources.tienPhong) }} ({{ getSourcePercentage(revenueSources.tienPhong) }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-teal" :style="{ width: `${getSourcePercentage(revenueSources.tienPhong)}%` }"></div>
                    </div>
                  </div>

                  <!-- Dịch vụ -->
                  <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                      <span><i class="bi bi-bell-fill text-primary me-1"></i> Dịch vụ phát sinh</span>
                      <span class="text-navy">{{ formatCurrency(revenueSources.dichVu) }} ({{ getSourcePercentage(revenueSources.dichVu) }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-primary" :style="{ width: `${getSourcePercentage(revenueSources.dichVu)}%` }"></div>
                    </div>
                  </div>

                  <!-- Minibar -->
                  <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                      <span><i class="bi bi-cup-straw text-warning me-1"></i> Minibar</span>
                      <span class="text-navy">{{ formatCurrency(revenueSources.minibar) }} ({{ getSourcePercentage(revenueSources.minibar) }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-warning" :style="{ width: `${getSourcePercentage(revenueSources.minibar)}%` }"></div>
                    </div>
                  </div>

                  <!-- Phụ thu nhận sớm / trả trễ -->
                  <div class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                      <span><i class="bi bi-clock-fill text-purple me-1"></i> Phụ thu nhận/trả</span>
                      <span class="text-navy">{{ formatCurrency(revenueSources.phuThu) }} ({{ getSourcePercentage(revenueSources.phuThu) }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-purple" :style="{ width: `${getSourcePercentage(revenueSources.phuThu)}%` }"></div>
                    </div>
                  </div>

                  <!-- Khác / Đền bù -->
                  <div v-if="revenueSources.khac > 0" class="mb-3">
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                      <span><i class="bi bi-shield-fill-exclamation text-secondary me-1"></i> Bồi thường / Khác</span>
                      <span class="text-navy">{{ formatCurrency(revenueSources.khac) }} ({{ getSourcePercentage(revenueSources.khac) }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-secondary" :style="{ width: `${getSourcePercentage(revenueSources.khac)}%` }"></div>
                    </div>
                  </div>
                </div>

                <div class="alert alert-light border rounded-3 p-3 mt-auto mb-0 small text-muted">
                  <i class="bi bi-info-circle-fill text-teal me-1"></i>
                  Số liệu doanh thu tính toán trên các hóa đơn đã hoàn tất thanh toán (BR04.6).
                </div>
              </div>
            </div>
          </div>

          <!-- 6.3. Bảng Chi Tiết Doanh Thu Theo Mốc Thời Gian (Table) -->
          <div v-if="currentTab === 'revenue' || currentTab === 'dashboard'" class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
              <h5 class="fw-bold text-navy mb-0">Bảng Kê Chi Tiết Doanh Thu Theo Mốc Thời Gian</h5>
              <span class="badge bg-navy text-white px-3 py-1">{{ revenueTimeline.length }} mốc phát sinh</span>
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                  <tr>
                    <th class="ps-4">Mốc Thời Gian</th>
                    <th class="text-center">Số Hóa Đơn</th>
                    <th class="text-end">Doanh Thu Phát Sinh</th>
                    <th class="text-end pe-4">Tỷ Trọng (%)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, idx) in revenueTimeline" :key="idx">
                    <td class="ps-4 fw-bold text-navy">{{ row.thoiGian }}</td>
                    <td class="text-center">
                      <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1">
                        {{ row.soHoaDon }} HĐ
                      </span>
                    </td>
                    <td class="text-end fw-bold text-success">
                      {{ formatCurrency(row.doanhThu) }}
                    </td>
                    <td class="text-end pe-4 text-muted fw-semibold">
                      {{ reportData.doanhThu?.tong > 0 ? ((row.doanhThu / reportData.doanhThu.tong) * 100).toFixed(1) : 0 }}%
                    </td>
                  </tr>
                </tbody>
                <tfoot class="table-light fw-bold">
                  <tr>
                    <td class="ps-4 text-navy">TỔNG CỘNG</td>
                    <td class="text-center text-navy">{{ reportData.doanhThu?.soHoaDon || 0 }} HĐ</td>
                    <td class="text-end text-success fs-6">{{ formatCurrency(reportData.doanhThu?.tong || 0) }}</td>
                    <td class="text-end pe-4 text-navy">100%</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- 6.4. Báo Cáo Công Suất Sử Dụng Phòng (Occupancy Report) -->
          <div v-if="currentTab === 'dashboard' || currentTab === 'occupancy'" class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h5 class="fw-bold text-navy mb-0">Công Suất Sử Dụng Phòng Theo Từng Hạng Phòng</h5>
                <small class="text-muted">
                  Tỷ lệ lấp đầy: (Tổng phòng-ngày có khách / Tổng phòng-ngày khả dụng) * 100
                </small>
              </div>
              <span class="badge bg-teal text-white px-3 py-1.5 fs-6 fw-semibold">
                Toàn khách sạn: {{ reportData.congSuat?.toanKhachSan || 0 }}%
              </span>
            </div>

            <!-- Bảng công suất theo hạng phòng -->
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                  <tr>
                    <th>Hạng Phòng</th>
                    <th class="text-center">Số Phòng Khả Dụng</th>
                    <th class="text-center">Phòng-Ngày Khả Dụng</th>
                    <th class="text-center">Phòng-Ngày Có Khách</th>
                    <th style="width: 250px;">Tỷ Lệ Lấp Đầy (%)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="hang in roomTypeOccupancyList" :key="hang.maLoaiPhong">
                    <td class="fw-bold text-navy">{{ hang.tenLoaiPhong }}</td>
                    <td class="text-center">{{ hang.soPhong }} phòng</td>
                    <td class="text-center">{{ hang.phongNgayKhaDung }} ngày</td>
                    <td class="text-center fw-semibold text-primary">{{ hang.phongNgayCoKhach }} ngày</td>
                    <td>
                      <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height: 10px;">
                          <div
                            class="progress-bar rounded"
                            :class="getOccupancyBarColor(hang.tyLeLapDay)"
                            :style="{ width: `${hang.tyLeLapDay}%` }"
                          ></div>
                        </div>
                        <span class="fw-bold small" :class="getOccupancyTextColor(hang.tyLeLapDay)">
                          {{ hang.tyLeLapDay }}%
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 6.5. Báo Cáo Tình Hình Đặt Phòng & Lưu Trú (Bookings Report) -->
          <div v-if="currentTab === 'dashboard' || currentTab === 'bookings'" class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-4">
            <h5 class="fw-bold text-navy mb-1">Tình Hình Đặt Phòng & Lưu Trú</h5>
            <small class="text-muted d-block mb-3">Tổng hợp số lượng đơn, tỷ lệ hủy và lượt khách trong kỳ</small>

            <div class="row g-4 align-items-center">
              <div class="col-md-6">
                <div class="p-3 bg-light rounded-4">
                  <h6 class="fw-bold text-navy mb-3">Cơ cấu trạng thái đơn đặt</h6>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-check-circle-fill text-success me-1"></i> Thành công / Đã hoàn thành</span>
                    <span class="text-success">{{ reportData.datPhong?.coCauTrangThai?.thanhCong || 0 }} đơn</span>
                  </div>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-x-circle-fill text-danger me-1"></i> Đã hủy</span>
                    <span class="text-danger">{{ reportData.datPhong?.coCauTrangThai?.huy || 0 }} đơn</span>
                  </div>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-hourglass-split text-warning me-1"></i> Đang chờ / Chờ thanh toán</span>
                    <span class="text-warning">{{ reportData.datPhong?.coCauTrangThai?.cho || 0 }} đơn</span>
                  </div>
                  <hr class="my-2" />
                  <div class="d-flex justify-content-between fw-bold text-navy">
                    <span>TỔNG SỐ ĐƠN:</span>
                    <span>{{ reportData.datPhong?.tong || 0 }} đơn</span>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="p-3 bg-light rounded-4">
                  <h6 class="fw-bold text-navy mb-3">Thống kê lượt khách & lưu trú</h6>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-people-fill text-teal me-1"></i> Tổng số lượt khách lưu trú:</span>
                    <span class="text-teal fs-6 fw-bold">{{ reportData.kpi?.tongLuotKhach || 0 }} khách</span>
                  </div>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-building-check text-primary me-1"></i> Số đợt lưu trú thực tế:</span>
                    <span class="text-primary fs-6 fw-bold">{{ reportData.datPhong?.soLuotLuuTru || 0 }} đợt</span>
                  </div>
                  <div class="d-flex justify-content-between small fw-semibold mb-2">
                    <span><i class="bi bi-percent text-danger me-1"></i> Tỷ lệ hủy đơn đặt:</span>
                    <span class="text-danger fs-6 fw-bold">{{ reportData.kpi?.tyLeHuy || 0 }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. Toast thông báo -->
      <div class="position-fixed bottom-0 end-0 p-3 d-print-none" style="z-index: 1090">
        <div
          v-if="toast.show"
          class="toast show align-items-center text-white border-0 shadow-lg rounded-3"
          :class="toast.type === 'success' ? 'bg-teal' : 'bg-danger'"
          role="alert"
          aria-live="assertive"
          aria-atomic="true"
        >
          <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
              <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill fs-5' : 'bi bi-exclamation-octagon-fill fs-5'"></i>
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// ==========================
// 1. STATE & REACTIVE DATA
// ==========================
const isLoading = ref(false)
const accessDenied = ref(false)
const accessDeniedMessage = ref('')
const emptyMessage = ref('')
const currentTab = ref('dashboard') // 'dashboard', 'revenue', 'bookings', 'occupancy'

const roomTypeList = ref([])
const validationErrors = reactive({})

// Thông tin người dùng đăng nhập hiện tại
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

// Bộ lọc thời gian (mặc định từ đầu tháng hiện tại đến hôm nay)
const now = new Date()
const defaultYear = now.getFullYear()
const defaultMonth = String(now.getMonth() + 1).padStart(2, '0')
const firstDayOfMonth = `${defaultYear}-${defaultMonth}-01`
const today = now.toISOString().slice(0, 10)

const filters = reactive({
  tuNgay: firstDayOfMonth,
  denNgay: today,
  nhomTheo: 'ngay',
  maLoaiPhong: '',
})

// Dữ liệu báo cáo trả về từ API
const reportData = reactive({
  kpi: {
    tongDoanhThu: 0,
    tongDonDat: 0,
    tongDonThanhCong: 0,
    tongDonHuy: 0,
    tyLeHuy: 0,
    tongLuotKhach: 0,
    congSuatTrungBinh: 0,
  },
  doanhThu: {
    tong: 0,
    soHoaDon: 0,
    nguonThu: {
      tienPhong: 0,
      dichVu: 0,
      minibar: 0,
      phuThu: 0,
      khac: 0,
    },
    timeline: [],
  },
  datPhong: {
    tong: 0,
    soLuotLuuTru: 0,
    coCauTrangThai: {
      thanhCong: 0,
      huy: 0,
      cho: 0,
    },
    timeline: [],
  },
  congSuat: {
    soNgayTrongKy: 0,
    soPhongKhaDung: 0,
    tongPhongNgayKhaDung: 0,
    tongPhongNgayCoKhach: 0,
    toanKhachSan: 0,
    theoHangPhong: [],
  },
})

// Toast
const toast = reactive({
  show: false,
  message: '',
  type: 'success',
})

const showToast = (msg, type = 'success') => {
  toast.message = msg
  toast.type = type
  toast.show = true
  setTimeout(() => {
    toast.show = false
  }, 4000)
}

// ==========================
// 2. COMPUTED PROPERTIES
// ==========================
const isManager = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'quản lý' || role === 'admin'
})

const hasReportData = computed(() => {
  return (
    (reportData.kpi?.tongDoanhThu || 0) > 0 ||
    (reportData.kpi?.tongDonDat || 0) > 0 ||
    (reportData.congSuat?.tongPhongNgayCoKhach || 0) > 0
  )
})

const revenueSources = computed(() => {
  return reportData.doanhThu?.nguonThu || {
    tienPhong: 0,
    dichVu: 0,
    minibar: 0,
    phuThu: 0,
    khac: 0,
  }
})

const revenueTimeline = computed(() => {
  return reportData.doanhThu?.timeline || []
})

const maxRevenueInTimeline = computed(() => {
  const list = revenueTimeline.value
  if (!list || list.length === 0) return 1
  return Math.max(...list.map((i) => i.doanhThu || 0), 1)
})

const roomTypeOccupancyList = computed(() => {
  return reportData.congSuat?.theoHangPhong || []
})

// ==========================
// 3. API CALLS & ACTIONS
// ==========================
const getAuthHeaders = () => {
  const token = localStorage.getItem('access_token')
  return {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
  }
}

/**
 * Tải danh mục loại phòng phục vụ bộ lọc
 */
const fetchRoomTypes = async () => {
  try {
    const res = await axios.get('/api/v1/room-types', {
      headers: getAuthHeaders(),
    })
    if (res.data?.success && Array.isArray(res.data.data)) {
      roomTypeList.value = res.data.data
    }
  } catch {
    // Không gián đoạn nếu lỗi room-types
  }
}

/**
 * Lấy dữ liệu báo cáo từ Backend API
 */
const fetchReportData = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    router.push('/login')
    return
  }

  // Clear previous errors
  Object.keys(validationErrors).forEach((key) => delete validationErrors[key])

  isLoading.value = true
  try {
    const params = {
      tuNgay: filters.tuNgay,
      denNgay: filters.denNgay,
      nhomTheo: filters.nhomTheo,
    }
    if (filters.maLoaiPhong) {
      params.maLoaiPhong = filters.maLoaiPhong
    }

    // Chọn endpoint phù hợp theo tab hiện tại (hoặc dashboard tổng quan)
    let endpoint = '/api/v1/reports/dashboard'
    if (currentTab.value === 'revenue') {
      endpoint = '/api/v1/reports/revenue'
    } else if (currentTab.value === 'bookings') {
      endpoint = '/api/v1/reports/bookings'
    } else if (currentTab.value === 'occupancy') {
      endpoint = '/api/v1/reports/occupancy'
    }

    const res = await axios.get(endpoint, {
      headers: getAuthHeaders(),
      params,
    })

    if (res.data?.success) {
      accessDenied.value = false
      emptyMessage.value = res.data.message || ''

      const data = res.data.data || {}
      if (currentTab.value === 'dashboard') {
        Object.assign(reportData, data)
      } else if (currentTab.value === 'revenue') {
        reportData.doanhThu = data
        reportData.kpi.tongDoanhThu = data.tongDoanhThu || 0
      } else if (currentTab.value === 'bookings') {
        reportData.datPhong = data
        reportData.kpi.tongDonDat = data.tongDonDat || 0
        reportData.kpi.tongDonThanhCong = data.donThanhCong || 0
        reportData.kpi.tongDonHuy = data.donHuy || 0
        reportData.kpi.tyLeHuy = data.tyLeHuy || 0
        reportData.kpi.tongLuotKhach = data.tongLuotKhach || 0
      } else if (currentTab.value === 'occupancy') {
        reportData.congSuat = data
        reportData.kpi.congSuatTrungBinh = data.toanKhachSan || 0
      }
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || 'Không thể tổng hợp báo cáo.'

    if (status === 401) {
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      router.push('/login')
    } else if (status === 403) {
      accessDenied.value = true
      accessDeniedMessage.value = msg
    } else if (status === 422 && err.response?.data?.errors) {
      Object.assign(validationErrors, err.response.data.errors)
      showToast(msg, 'danger')
    } else {
      showToast(msg, 'danger')
    }
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  fetchReportData()
}

const switchTab = (tab) => {
  currentTab.value = tab
  fetchReportData()
}

/**
 * Đặt lại bộ lọc về mặc định
 */
const resetFilters = () => {
  filters.tuNgay = firstDayOfMonth
  filters.denNgay = today
  filters.nhomTheo = 'ngay'
  filters.maLoaiPhong = ''
  fetchReportData()
}

/**
 * Chọn nhanh các mốc thời gian (Presets)
 */
const applyPreset = (preset) => {
  const d = new Date()
  const toIso = (date) => date.toISOString().slice(0, 10)

  if (preset === 'today') {
    filters.tuNgay = toIso(d)
    filters.denNgay = toIso(d)
    filters.nhomTheo = 'ngay'
  } else if (preset === 'last7days') {
    const past7 = new Date()
    past7.setDate(d.getDate() - 6)
    filters.tuNgay = toIso(past7)
    filters.denNgay = toIso(d)
    filters.nhomTheo = 'ngay'
  } else if (preset === 'thisMonth') {
    filters.tuNgay = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-01`
    filters.denNgay = toIso(d)
    filters.nhomTheo = 'ngay'
  } else if (preset === 'lastMonth') {
    const lastMonthDate = new Date(d.getFullYear(), d.getMonth() - 1, 1)
    const lastMonthEnd = new Date(d.getFullYear(), d.getMonth(), 0)
    filters.tuNgay = toIso(lastMonthDate)
    filters.denNgay = toIso(lastMonthEnd)
    filters.nhomTheo = 'ngay'
  } else if (preset === 'thisYear') {
    filters.tuNgay = `${d.getFullYear()}-01-01`
    filters.denNgay = toIso(d)
    filters.nhomTheo = 'thang'
  }

  fetchReportData()
}

/**
 * In báo cáo
 */
const printReport = () => {
  window.print()
}

// ==========================
// 4. QUICK TEST USER SWITCH
// ==========================
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
      currentUser.vaiTro = user.vaiTro || roleLabel

      accessDenied.value = false
      showToast(`Đã chuyển sang tài khoản ${roleLabel} (${username})`, 'success')
      fetchReportData()
    } else {
      showToast(res.data?.message || 'Đăng nhập thất bại.', 'danger')
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Không thể chuyển đổi tài khoản.', 'danger')
  } finally {
    isLoading.value = false
  }
}

// ==========================
// 5. HELPER FORMATTERS
// ==========================
const formatCurrency = (val) => {
  if (!val || isNaN(val)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

const formatShortCurrency = (val) => {
  if (!val || isNaN(val)) return '0'
  if (val >= 1000000) {
    return (val / 1000000).toFixed(1) + 'M'
  }
  if (val >= 1000) {
    return (val / 1000).toFixed(0) + 'k'
  }
  return val.toString()
}

const formatDate = (val) => {
  if (!val) return '---'
  try {
    const d = new Date(val)
    if (isNaN(d.getTime())) return val
    return d.toLocaleDateString('vi-VN')
  } catch {
    return val
  }
}

const formatTimelineLabel = (val) => {
  if (!val) return ''
  // If Y-m-d, show dd/mm
  if (val.length === 10) {
    return val.slice(8, 10) + '/' + val.slice(5, 7)
  }
  return val
}

const getBarHeight = (val, max) => {
  if (!val || !max) return '6px'
  const percent = Math.min(100, Math.max(8, Math.round((val / max) * 100)))
  return `${percent}%`
}

const getSourcePercentage = (val) => {
  const total = reportData.doanhThu?.tong || 0
  if (!val || total <= 0) return 0
  return Math.min(100, Math.round((val / total) * 100))
}

const getOccupancyBarColor = (val) => {
  if (val >= 80) return 'bg-success'
  if (val >= 50) return 'bg-teal'
  if (val >= 25) return 'bg-warning'
  return 'bg-secondary'
}

const getOccupancyTextColor = (val) => {
  if (val >= 80) return 'text-success'
  if (val >= 50) return 'text-teal'
  if (val >= 25) return 'text-warning'
  return 'text-secondary'
}

// ==========================
// 6. LIFECYCLE HOOKS
// ==========================
onMounted(() => {
  try {
    const storedUser = JSON.parse(localStorage.getItem('user') || '{}')
    currentUser.id = storedUser.id || null
    currentUser.tenDangNhap = storedUser.tenDangNhap || ''
    currentUser.hoTen = storedUser.hoTen || ''
    currentUser.vaiTro = storedUser.vaiTro || ''
  } catch {
    // ignore
  }

  fetchRoomTypes()
  fetchReportData()
})
</script>

<style scoped>
/* ==========================
   DESIGN TOKENS & STYLING
   ========================== */
.dashboard-report-view {
  background-color: #f8fafc;
  min-height: 90vh;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
  background-color: #152a45;
  border-color: #152a45;
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
  background-color: #0b5852;
  border-color: #0b5852;
}

.bg-purple {
  background-color: #8b5cf6 !important;
}

.text-purple {
  color: #8b5cf6 !important;
}

.header-icon-box {
  width: 52px;
  height: 52px;
}

.kpi-icon-box {
  width: 44px;
  height: 44px;
}

.kpi-card {
  transition: all 0.2s ease-in-out;
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1rem rgba(30, 58, 95, 0.08) !important;
}

/* Custom Pills */
.custom-pills .nav-link {
  color: #64748b;
  padding: 0.5rem 1.25rem;
  transition: all 0.2s ease;
}

.custom-pills .nav-link.active {
  background-color: #1e3a5f;
  color: #ffffff;
}

/* Chart Container & Timeline Bar */
.chart-container {
  height: 240px;
  display: flex;
  flex-direction: column;
}

.timeline-bar-wrapper {
  height: 100%;
  cursor: pointer;
}

.timeline-bar {
  transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 4px;
}

.timeline-bar-wrapper:hover .timeline-bar {
  background-color: #0d9488 !important;
}

.timeline-bar-wrapper:hover .bar-value {
  color: #1e3a5f !important;
  font-weight: bold;
}

/* Empty State & Access Denied */
.empty-icon-box,
.avatar-denied {
  width: 72px;
  height: 72px;
}

/* Spin animation */
.spin-animation {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Print Styles */
@media print {
  .dashboard-report-view {
    background-color: #ffffff !important;
  }
  .card {
    box-shadow: none !important;
    border: 1px solid #e2e8f0 !important;
  }
}
</style>
