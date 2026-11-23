<template>
  <div class="booking-management-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/receptionist/front-desk" class="text-decoration-none text-teal">
              Bàn trực Lễ tân
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quản lý đơn đặt phòng (UC05)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-journal-text fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Quản Lý Đơn Đặt Phòng</h2>
              <p class="text-muted mb-0 small">
                Tra cứu, lọc, kiểm tra chi tiết, cập nhật trạng thái và điều chỉnh lịch lưu trú / phòng đặt (Phân hệ PMS - UC05).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isStaff ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isStaff ? 'bi bi-person-badge-fill me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Nhân viên' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isStaff ? 'Được cấp quyền quản lý đơn đặt phòng' : 'Không đủ quyền hạn truy cập' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Test & Switch Account Toolbar (Hỗ trợ kiểm thử nhanh) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi tài khoản kiểm thử:</span>
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
                to="/receptionist/check-in-out"
                class="btn btn-sm btn-warning text-dark rounded-3 px-3 fw-semibold shadow-sm"
              >
                <i class="bi bi-arrow-left-right me-1"></i> Nhận & Trả phòng (UC09)
              </router-link>
              <router-link
                to="/receptionist/booking"
                class="btn btn-sm btn-teal text-white rounded-3 px-3 fw-semibold"
              >
                <i class="bi bi-calendar-plus me-1"></i> Tiếp nhận đặt phòng mới
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Cảnh báo nếu vai trò không hợp lệ (403 Forbidden) -->
      <div
        v-if="!isStaff && token"
        class="alert alert-danger border-danger shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3 p-3"
        role="alert"
      >
        <i class="bi bi-shield-slash-fill fs-3 text-danger flex-shrink-0"></i>
        <div class="flex-grow-1">
          <h6 class="fw-bold mb-1 text-danger">Không có quyền truy cập chức năng này (Mã lỗi: 403 Forbidden)</h6>
          <p class="mb-0 small text-danger-emphasis">
            Chức năng Quản lý Đơn đặt phòng chỉ dành riêng cho <strong>Lễ tân</strong> và <strong>Quản lý</strong>.
            Vui lòng chuyển sang tài khoản Lễ tân hoặc Quản lý ở thanh Quick-Test phía trên để tiếp tục.
          </p>
        </div>
      </div>

      <!-- Thống kê KPI / Trạng thái đơn -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === '' }"
            @click="setQuickFilterStatus('')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Tổng số đơn</span>
              <div class="stat-icon bg-primary-subtle text-primary rounded-3">
                <i class="bi bi-collection-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold text-navy mb-0">{{ stats.total }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Toàn bộ dữ liệu</small>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === 'Chờ xác nhận' }"
            @click="setQuickFilterStatus('Chờ xác nhận')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Chờ xác nhận</span>
              <div class="stat-icon bg-warning-subtle text-warning rounded-3">
                <i class="bi bi-hourglass-split"></i>
              </div>
            </div>
            <h4 class="fw-bold text-warning mb-0">{{ stats.pending }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Chờ Lễ tân duyệt</small>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === 'Đã xác nhận' }"
            @click="setQuickFilterStatus('Đã xác nhận')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Đã xác nhận</span>
              <div class="stat-icon bg-teal-subtle text-teal rounded-3">
                <i class="bi bi-check2-circle"></i>
              </div>
            </div>
            <h4 class="fw-bold text-teal mb-0">{{ stats.confirmed }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Sẵn sàng check-in</small>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === 'Đã cọc' }"
            @click="setQuickFilterStatus('Đã cọc')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Đã nhận cọc</span>
              <div class="stat-icon bg-info-subtle text-info rounded-3">
                <i class="bi bi-cash-coin"></i>
              </div>
            </div>
            <h4 class="fw-bold text-info mb-0">{{ stats.deposited }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Đã thanh toán cọc</small>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === 'Đang ở' }"
            @click="setQuickFilterStatus('Đang ở')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Đang lưu trú</span>
              <div class="stat-icon bg-success-subtle text-success rounded-3">
                <i class="bi bi-door-open-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold text-success mb-0">{{ stats.inHouse }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Đã check-in / Đang ở</small>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
          <div
            class="card stat-card border-0 shadow-sm rounded-4 p-3 bg-white h-100 cursor-pointer"
            :class="{ 'border-active': activeFilterStatus === 'Đã hủy' }"
            @click="setQuickFilterStatus('Đã hủy')"
          >
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="text-muted small">Đã hủy</span>
              <div class="stat-icon bg-danger-subtle text-danger rounded-3">
                <i class="bi bi-x-circle-fill"></i>
              </div>
            </div>
            <h4 class="fw-bold text-danger mb-0">{{ stats.cancelled }}</h4>
            <small class="text-muted" style="font-size: 0.72rem;">Đơn không hiệu lực</small>
          </div>
        </div>
      </div>

      <!-- Thanh công cụ Tìm kiếm & Bộ lọc nâng cao (Search & Filter Toolbar) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm từ khóa -->
            <div class="col-12 col-md-4">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-search me-1 text-teal"></i> Tìm kiếm đơn
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  type="text"
                  class="form-control bg-light border-start-0"
                  placeholder="Mã đơn (ví dụ: 1), tên khách, SĐT..."
                  v-model.trim="searchKeyword"
                  @keyup.enter="handleFilterSubmit"
                />
                <button
                  v-if="searchKeyword"
                  type="button"
                  class="btn btn-light border"
                  title="Xóa từ khóa"
                  @click="searchKeyword = ''; handleFilterSubmit()"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
            </div>

            <!-- Lọc theo trạng thái -->
            <div class="col-12 col-sm-6 col-md-3">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-funnel me-1 text-teal"></i> Trạng thái đơn
              </label>
              <select
                class="form-select bg-light"
                v-model="activeFilterStatus"
                @change="handleFilterSubmit"
              >
                <option value="">Tất cả trạng thái</option>
                <option value="Chờ xác nhận">Chờ xác nhận</option>
                <option value="Đã cọc">Đã cọc</option>
                <option value="Đã xác nhận">Đã xác nhận</option>
                <option value="Đã nhận phòng">Đã nhận phòng</option>
                <option value="Đang ở">Đang ở</option>
                <option value="Đã trả">Đã trả</option>
                <option value="Đã hoàn thành">Đã hoàn thành</option>
                <option value="Đã hủy">Đã hủy</option>
              </select>
            </div>

            <!-- Lọc ngày nhận phòng từ -->
            <div class="col-6 col-sm-3 col-md-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-calendar-event me-1 text-teal"></i> Nhận từ ngày
              </label>
              <input
                type="date"
                class="form-control bg-light"
                v-model="filterDateFrom"
                @change="handleFilterSubmit"
              />
            </div>

            <!-- Lọc ngày nhận phòng đến -->
            <div class="col-6 col-sm-3 col-md-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-calendar-check me-1 text-teal"></i> Đến ngày
              </label>
              <input
                type="date"
                class="form-control bg-light"
                v-model="filterDateTo"
                @change="handleFilterSubmit"
              />
            </div>

            <!-- Nút hành động lọc & Đặt lại -->
            <div class="col-12 col-md-1 d-flex gap-2">
              <button
                type="button"
                class="btn btn-outline-secondary w-100 rounded-3"
                title="Đặt lại bộ lọc"
                @click="resetFilter"
                :disabled="isLoading"
              >
                <i class="bi bi-arrow-counterclockwise"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo lỗi API chung (nếu có) -->
      <div
        v-if="fetchError"
        class="alert alert-danger border-danger shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3 p-3"
        role="alert"
      >
        <i class="bi bi-exclamation-octagon-fill fs-4 text-danger flex-shrink-0"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold">{{ fetchError }}</div>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="fetchError = ''"></button>
      </div>

      <!-- Danh sách bảng đơn đặt phòng -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h5 class="fw-bold text-navy mb-0">
              <i class="bi bi-table text-teal me-2"></i>Danh Sách Đơn Đặt Phòng
            </h5>
            <small class="text-muted">
              Hiển thị {{ paginatedBookings.length }} trên tổng số {{ filteredBookings.length }} đơn phù hợp
            </small>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Số dòng / trang:</span>
            <select class="form-select form-select-sm w-auto" v-model.number="pageSize">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>

        <!-- Trạng thái Loading spinner -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-teal" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Đang tải dữ liệu...</span>
          </div>
          <p class="text-muted mt-3 small">Đang tải danh sách đơn đặt phòng từ máy chủ...</p>
        </div>

        <!-- Bảng dữ liệu chính -->
        <div v-else-if="filteredBookings.length > 0" class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-navy text-uppercase small" style="font-size: 0.75rem; letter-spacing: 0.5px;">
              <tr>
                <th scope="col" class="ps-4" style="width: 100px;">Mã đơn</th>
                <th scope="col" style="min-width: 200px;">Khách hàng</th>
                <th scope="col" style="min-width: 220px;">Phòng & Loại phòng</th>
                <th scope="col" style="min-width: 200px;">Thời gian lưu trú</th>
                <th scope="col" style="min-width: 160px;">Tài chính & Cọc</th>
                <th scope="col" class="text-center" style="min-width: 140px;">Trạng thái</th>
                <th scope="col" style="min-width: 140px;">Cập nhật</th>
                <th scope="col" class="text-end pe-4" style="min-width: 180px;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in paginatedBookings" :key="item.id" class="booking-row">
                <!-- Mã đơn -->
                <td class="ps-4">
                  <span class="badge bg-light text-navy border fw-bold px-2.5 py-1.5 fs-7 font-monospace">
                    #DP-{{ item.id }}
                  </span>
                </td>

                <!-- Khách hàng -->
                <td>
                  <div v-if="item.khachHang">
                    <div class="fw-bold text-navy d-flex align-items-center gap-1.5">
                      <span>{{ item.khachHang.hoTen }}</span>
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-2 mt-0.5" style="font-size: 0.75rem;">
                      <span v-if="item.khachHang.soDienThoai">
                        <i class="bi bi-telephone-fill me-1 text-teal"></i>{{ item.khachHang.soDienThoai }}
                      </span>
                      <span v-if="item.khachHang.email" class="text-truncate" style="max-width: 160px;" :title="item.khachHang.email">
                        • {{ item.khachHang.email }}
                      </span>
                    </div>
                  </div>
                  <div v-else class="text-muted small fst-italic">
                    Chưa gán thông tin khách
                  </div>
                </td>

                <!-- Danh sách phòng -->
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    <span
                      v-for="room in item.danhSachPhong"
                      :key="room.id"
                      class="badge bg-light text-dark border text-truncate d-inline-flex align-items-center gap-1 px-2 py-1"
                      style="font-size: 0.75rem; max-width: 200px;"
                    >
                      <i class="bi bi-door-closed-fill text-teal"></i>
                      <strong>{{ room.tenPhong }}</strong>
                      <span class="text-muted" v-if="room.tenLoaiPhong">({{ room.tenLoaiPhong }})</span>
                    </span>
                  </div>
                  <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                    <i class="bi bi-people-fill me-1 text-teal"></i>
                    Tổng khách: <strong>{{ item.soLuongKhach }}</strong> người
                  </div>
                </td>

                <!-- Thời gian lưu trú -->
                <td>
                  <div class="small">
                    <div class="text-navy fw-semibold">
                      <i class="bi bi-box-arrow-in-right text-teal me-1"></i>
                      {{ formatDateTime(item.ngayNhanPhong) }}
                    </div>
                    <div class="text-navy fw-semibold mt-0.5">
                      <i class="bi bi-box-arrow-right text-danger me-1"></i>
                      {{ formatDateTime(item.ngayTraPhong) }}
                    </div>
                    <span class="badge bg-light text-muted border mt-1" style="font-size: 0.7rem;">
                      <i class="bi bi-moon-stars-fill me-1 text-warning"></i>
                      {{ item.soDem }} đêm
                    </span>
                  </div>
                </td>

                <!-- Tài chính & Cọc -->
                <td>
                  <div class="small">
                    <div class="text-muted" style="font-size: 0.72rem;">Tổng tiền:</div>
                    <div class="fw-bold text-navy fs-7">
                      {{ formatCurrency(item.tongChiPhi) }}
                    </div>
                    <div class="text-muted mt-0.5" style="font-size: 0.72rem;">
                      Cọc: <strong class="text-teal">{{ formatCurrency(item.tienCoc) }}</strong>
                    </div>
                  </div>
                </td>

                <!-- Trạng thái -->
                <td class="text-center">
                  <span
                    class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    :class="getStatusBadgeClass(item.trangThai)"
                  >
                    <i :class="getStatusIconClass(item.trangThai)" class="me-1"></i>
                    {{ item.trangThai }}
                  </span>
                  <div v-if="item.yeuCauHuy" class="mt-1">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle small px-2 py-0.5">
                      <i class="bi bi-exclamation-circle me-1"></i> Yêu cầu hủy
                    </span>
                  </div>
                </td>

                <!-- Cập nhật & Audit -->
                <td>
                  <div class="small text-muted" style="font-size: 0.75rem;">
                    <div>{{ formatDateTime(item.updated_at || item.created_at) }}</div>
                    <div v-if="item.maNhanVien" class="text-teal mt-0.5">
                      <i class="bi bi-person-check me-1"></i> NV #{{ item.maNhanVien }}
                    </div>
                  </div>
                </td>

                <!-- Thao tác hành động -->
                <td class="text-end pe-4">
                  <div class="btn-group btn-group-sm" role="group">
                    <!-- Nút xem chi tiết -->
                    <button
                      type="button"
                      class="btn btn-outline-navy"
                      title="Xem chi tiết đơn"
                      @click="openDetailModal(item.id)"
                    >
                      <i class="bi bi-eye"></i>
                    </button>

                    <!-- Nút đổi trạng thái nhanh -->
                    <button
                      type="button"
                      class="btn btn-outline-teal"
                      title="Chuyển trạng thái nhanh"
                      :disabled="isTerminalStatus(item.trangThai)"
                      @click="openQuickStatusModal(item)"
                    >
                      <i class="bi bi-arrow-left-right"></i>
                    </button>

                    <!-- Nút điều chỉnh đơn (đổi phòng / ngày) -->
                    <button
                      type="button"
                      class="btn btn-teal text-white"
                      title="Điều chỉnh phòng hoặc ngày lưu trú"
                      :disabled="isTerminalStatus(item.trangThai)"
                      @click="openEditModal(item)"
                    >
                      <i class="bi bi-pencil-square"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Trạng thái rỗng không có kết quả -->
        <div v-else class="text-center py-5">
          <div class="empty-state-icon bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
            <i class="bi bi-inbox text-muted fs-2"></i>
          </div>
          <h6 class="fw-bold text-navy">Không tìm thấy đơn đặt phòng nào</h6>
          <p class="text-muted small mb-3">
            Thử thay đổi từ khóa tìm kiếm hoặc bỏ bớt các bộ lọc trạng thái và khoảng thời gian.
          </p>
          <button type="button" class="btn btn-sm btn-outline-navy rounded-3 px-3" @click="resetFilter">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại bộ lọc
          </button>
        </div>

        <!-- Phân trang (Pagination) -->
        <div
          v-if="filteredBookings.length > pageSize"
          class="card-footer bg-white border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2"
        >
          <div class="small text-muted">
            Trang {{ currentPage }} / {{ totalPages }} (Tổng {{ filteredBookings.length }} đơn)
          </div>
          <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="currentPage--" aria-label="Trang trước">
                  <i class="bi bi-chevron-left"></i>
                </button>
              </li>
              <li
                v-for="page in totalPages"
                :key="page"
                class="page-item"
                :class="{ active: currentPage === page }"
              >
                <button class="page-link" @click="currentPage = page">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="currentPage++" aria-label="Trang sau">
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: XEM CHI TIẾT ĐƠN ĐẶT PHÒNG (GET /api/v1/bookings/{id})-->
    <!-- ============================================================== -->
    <div
      v-if="showDetailModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-file-text-fill fs-4 text-teal"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">
                  Chi Tiết Đơn Đặt Phòng #DP-{{ selectedBookingDetail?.id }}
                </h5>
                <small class="text-white-50" style="font-size: 0.75rem;">
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
            <div v-if="isLoadingDetail" class="text-center py-5">
              <div class="spinner-border text-teal" role="status">
                <span class="visually-hidden">Đang tải chi tiết...</span>
              </div>
              <p class="text-muted mt-2 small">Đang nạp chi tiết đơn đặt phòng...</p>
            </div>

            <div v-else-if="detailError" class="alert alert-danger border-danger rounded-3 p-3">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ detailError }}
            </div>

            <div v-else-if="selectedBookingDetail">
              <!-- Header tóm tắt trạng thái -->
              <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light mb-4">
                <div>
                  <span class="text-muted small">Trạng thái hiện tại:</span>
                  <span
                    class="badge rounded-pill px-3 py-1.5 ms-2 fw-semibold"
                    :class="getStatusBadgeClass(selectedBookingDetail.trangThai)"
                  >
                    <i :class="getStatusIconClass(selectedBookingDetail.trangThai)" class="me-1"></i>
                    {{ selectedBookingDetail.trangThai }}
                  </span>
                </div>
                <div class="text-end small text-muted">
                  <span>Nhân viên phụ trách: </span>
                  <strong class="text-navy">
                    {{ selectedBookingDetail.maNhanVien ? `Mã NV #${selectedBookingDetail.maNhanVien}` : 'Tự đặt trực tuyến' }}
                  </strong>
                </div>
              </div>

              <!-- Thông tin khách hàng & Thời gian lưu trú -->
              <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                  <div class="card border rounded-3 p-3 h-100 bg-white">
                    <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                      <i class="bi bi-person-circle text-teal"></i> Thông Tin Khách Hàng
                    </h6>
                    <div v-if="selectedBookingDetail.khachHang" class="small">
                      <div class="mb-2">
                        <span class="text-muted">Họ và tên:</span>
                        <div class="fw-bold text-navy">{{ selectedBookingDetail.khachHang.hoTen }}</div>
                      </div>
                      <div class="mb-2">
                        <span class="text-muted">Số điện thoại:</span>
                        <div class="fw-semibold">{{ selectedBookingDetail.khachHang.soDienThoai || 'N/A' }}</div>
                      </div>
                      <div class="mb-2">
                        <span class="text-muted">Email:</span>
                        <div class="fw-semibold">{{ selectedBookingDetail.khachHang.email || 'N/A' }}</div>
                      </div>
                      <div class="mb-2" v-if="selectedBookingDetail.khachHang.cccd">
                        <span class="text-muted">CCCD/CMND:</span>
                        <div class="fw-semibold">{{ selectedBookingDetail.khachHang.cccd }}</div>
                      </div>
                      <div v-if="selectedBookingDetail.khachHang.diaChi">
                        <span class="text-muted">Địa chỉ:</span>
                        <div class="fw-semibold">{{ selectedBookingDetail.khachHang.diaChi }}</div>
                      </div>
                    </div>
                    <div v-else class="text-muted small fst-italic">
                      Không có thông tin khách hàng chi tiết.
                    </div>
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <div class="card border rounded-3 p-3 h-100 bg-white">
                    <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                      <i class="bi bi-calendar3 text-teal"></i> Lịch Lưu Trú & Tài Chính
                    </h6>
                    <div class="small">
                      <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Ngày nhận phòng:</span>
                        <strong class="text-navy">{{ formatDateTime(selectedBookingDetail.ngayNhanPhong) }}</strong>
                      </div>
                      <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Ngày trả phòng:</span>
                        <strong class="text-navy">{{ formatDateTime(selectedBookingDetail.ngayTraPhong) }}</strong>
                      </div>
                      <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Số đêm lưu trú:</span>
                        <span class="badge bg-light text-navy border">{{ selectedBookingDetail.soDem }} đêm</span>
                      </div>
                      <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Tổng số lượng khách:</span>
                        <strong class="text-teal">{{ selectedBookingDetail.soLuongKhach }} người</strong>
                      </div>
                      <hr class="my-2" />
                      <div class="mb-1 d-flex justify-content-between">
                        <span class="text-muted">Tổng tiền phòng:</span>
                        <strong class="text-navy fs-6">{{ formatCurrency(selectedBookingDetail.tongChiPhi) }}</strong>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span class="text-muted">Tiền cọc quy định:</span>
                        <strong class="text-teal fs-6">{{ formatCurrency(selectedBookingDetail.tienCoc) }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Danh sách phòng chi tiết trong đơn -->
              <div class="card border rounded-3 overflow-hidden mb-3">
                <div class="card-header bg-light py-2 px-3 border-bottom">
                  <h6 class="fw-bold text-navy mb-0 small text-uppercase">
                    <i class="bi bi-door-open-fill text-teal me-1"></i> Danh Sách Phòng Trong Đơn ({{ selectedBookingDetail.danhSachPhong?.length || 0 }} phòng)
                  </h6>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light small text-muted">
                      <tr>
                        <th class="ps-3">Số phòng</th>
                        <th>Loại phòng</th>
                        <th class="text-center">Số khách</th>
                        <th class="text-end">Đơn giá / đêm</th>
                        <th class="text-end pe-3">Thành tiền</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="room in selectedBookingDetail.danhSachPhong" :key="room.id">
                        <td class="ps-3 fw-bold text-navy">
                          <i class="bi bi-door-closed text-teal me-1"></i>
                          {{ room.tenPhong }}
                        </td>
                        <td>{{ room.tenLoaiPhong || 'Chưa phân loại' }}</td>
                        <td class="text-center">{{ room.soLuongKhach }} người</td>
                        <td class="text-end">{{ formatCurrency(room.giaPhongApDung) }}</td>
                        <td class="text-end pe-3 fw-bold text-navy">
                          {{ formatCurrency(room.giaPhongApDung * selectedBookingDetail.soDem) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Thông tin Yêu cầu hủy nếu có -->
              <div
                v-if="selectedBookingDetail.yeuCauHuy"
                class="alert alert-warning border-warning rounded-3 p-3 small mb-0"
              >
                <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                  <i class="bi bi-exclamation-octagon-fill text-warning fs-5"></i>
                  Đơn có yêu cầu hủy đặt phòng (Trạng thái: {{ selectedBookingDetail.yeuCauHuy.trangThaiYeuCau }})
                </div>
                <div><strong>Lý do hủy:</strong> {{ selectedBookingDetail.yeuCauHuy.lyDoHuy || 'Không có' }}</div>
                <div><strong>Thời gian gửi:</strong> {{ selectedBookingDetail.yeuCauHuy.thoiGianYeuCau }}</div>
                <div><strong>Số tiền hoàn dự kiến:</strong> {{ formatCurrency(selectedBookingDetail.yeuCauHuy.soTienHoanDuKien) }}</div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light py-2 px-4 border-0 d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="closeDetailModal">
              Đóng
            </button>
            <div class="d-flex gap-2" v-if="selectedBookingDetail && !isTerminalStatus(selectedBookingDetail.trangThai)">
              <button
                type="button"
                class="btn btn-outline-teal rounded-3 px-3"
                @click="openQuickStatusModal(selectedBookingDetail)"
              >
                <i class="bi bi-arrow-left-right me-1"></i> Đổi trạng thái
              </button>
              <button
                type="button"
                class="btn btn-teal text-white rounded-3 px-3"
                @click="openEditModal(selectedBookingDetail)"
              >
                <i class="bi bi-pencil-square me-1"></i> Điều chỉnh đơn
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: ĐIỀU CHỈNH ĐƠN (ĐỔI PHÒNG, ĐỔI NGÀY LƯU TRÚ - UC05)     -->
    <!-- ============================================================== -->
    <div
      v-if="showEditModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-pencil-square fs-4 text-warning"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">
                  Điều Chỉnh Đơn Đặt Phòng #DP-{{ editForm.id }}
                </h5>
                <small class="text-white-50" style="font-size: 0.75rem;">
                  Khách hàng: {{ editForm.tenKhachHang }}
                </small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              :disabled="isUpdating"
              @click="closeEditModal"
            ></button>
          </div>

          <!-- Modal Body Form -->
          <div class="modal-body p-4">
            <!-- Cảnh báo lỗi 409 Conflict (Phòng trùng lịch) -->
            <div
              v-if="conflictError"
              class="alert alert-warning border-warning shadow-sm rounded-3 p-3 mb-3"
              role="alert"
            >
              <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-warning flex-shrink-0 mt-0.5"></i>
                <div class="flex-grow-1 small">
                  <strong class="d-block mb-1 text-dark">Xung đột lịch đặt phòng (Mã lỗi: 409 Conflict)</strong>
                  <span>{{ conflictError.message }}</span>
                  <div v-if="conflictError.rooms && conflictError.rooms.length > 0" class="mt-2">
                    <span class="fw-semibold">Các phòng bị trùng lịch:</span>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                      <span
                        v-for="rName in conflictError.rooms"
                        :key="rName"
                        class="badge bg-danger text-white px-2 py-1"
                      >
                        {{ rName }}
                      </span>
                    </div>
                  </div>
                  <small class="text-muted d-block mt-2">
                    Gợi ý: Vui lòng chọn khoảng thời gian lưu trú khác hoặc đổi sang phòng khác còn trống.
                  </small>
                </div>
              </div>
            </div>

            <!-- Cảnh báo lỗi 422 Validation -->
            <div
              v-if="validationError"
              class="alert alert-danger border-danger shadow-sm rounded-3 p-3 mb-3 small"
              role="alert"
            >
              <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0 mt-0.5"></i>
                <div class="flex-grow-1">
                  <strong class="d-block mb-1">Dữ liệu điều chỉnh chưa hợp lệ:</strong>
                  <span>{{ validationError }}</span>
                  <ul v-if="fieldErrors && Object.keys(fieldErrors).length > 0" class="mb-0 mt-1 ps-3">
                    <li v-for="(errs, f) in fieldErrors" :key="f">
                      {{ Array.isArray(errs) ? errs.join(', ') : errs }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <form @submit.prevent="submitEditBooking">
              <!-- Nhóm 1: Trạng thái & Tài chính -->
              <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold text-navy mb-1">
                    Trạng thái đơn đặt phòng <span class="text-danger">*</span>
                  </label>
                  <select
                    class="form-select bg-light"
                    v-model="editForm.trangThai"
                    :disabled="isUpdating"
                  >
                    <option
                      v-for="st in availableStatusOptions"
                      :key="st"
                      :value="st"
                    >
                      {{ st }}
                    </option>
                  </select>
                  <div class="form-text" style="font-size: 0.72rem;">
                    Luồng trạng thái hợp lệ từ: <strong>{{ editForm.originalStatus }}</strong>
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label small fw-semibold text-navy mb-1">
                    Tiền cọc ghi nhận (VNĐ)
                  </label>
                  <div class="input-group">
                    <input
                      type="number"
                      class="form-control bg-light"
                      min="0"
                      step="10000"
                      v-model.number="editForm.tienCoc"
                      :disabled="isUpdating"
                    />
                    <span class="input-group-text bg-light text-muted small">₫</span>
                  </div>
                  <div class="form-text" style="font-size: 0.72rem;">
                    Quy định: tối thiểu 30% tổng chi phí đơn.
                  </div>
                </div>
              </div>

              <!-- Nhóm 2: Khoảng thời gian lưu trú -->
              <div class="card border rounded-3 p-3 bg-light mb-4">
                <h6 class="fw-bold text-navy mb-2 small text-uppercase">
                  <i class="bi bi-clock-history text-teal me-1"></i> Điều Chỉnh Khoảng Thời Gian Lưu Trú
                </h6>
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-navy mb-1">
                      Ngày nhận phòng (Check-in) <span class="text-danger">*</span>
                    </label>
                    <input
                      type="datetime-local"
                      class="form-control bg-white"
                      v-model="editForm.ngayNhanPhong"
                      :disabled="isUpdating"
                      required
                    />
                  </div>

                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-navy mb-1">
                      Ngày trả phòng (Check-out) <span class="text-danger">*</span>
                    </label>
                    <input
                      type="datetime-local"
                      class="form-control bg-white"
                      v-model="editForm.ngayTraPhong"
                      :disabled="isUpdating"
                      required
                    />
                  </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top small text-muted">
                  <span>
                    Số đêm lưu trú tính toán:
                    <strong class="text-navy">{{ calculateNights(editForm.ngayNhanPhong, editForm.ngayTraPhong) }} đêm</strong>
                  </span>
                  <span v-if="calculateNights(editForm.ngayNhanPhong, editForm.ngayTraPhong) <= 0" class="text-danger fw-semibold">
                    <i class="bi bi-exclamation-triangle me-1"></i> Ngày trả phòng phải sau ngày nhận phòng!
                  </span>
                </div>
              </div>

              <!-- Nhóm 3: Tổng số lượng khách & Danh sách phòng -->
              <div class="card border rounded-3 p-3 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="fw-bold text-navy mb-0 small text-uppercase">
                    <i class="bi bi-door-closed text-teal me-1"></i> Danh Sách Phòng Trong Đơn
                  </h6>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-teal rounded-3"
                    :disabled="isUpdating"
                    @click="addRoomToEditForm"
                  >
                    <i class="bi bi-plus-circle me-1"></i> Thêm phòng
                  </button>
                </div>

                <!-- Danh sách từng phòng trong đơn -->
                <div
                  v-for="(roomItem, index) in editForm.danhSachPhong"
                  :key="index"
                  class="p-3 rounded-3 border bg-light mb-2"
                >
                  <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                      <label class="form-label small fw-semibold text-navy mb-1">
                        Chọn phòng vật lý #{{ index + 1 }}
                      </label>
                      <select
                        class="form-select form-select-sm bg-white"
                        v-model.number="roomItem.maPhong"
                        :disabled="isUpdating"
                        @change="handleRoomSelectChange(roomItem)"
                        required
                      >
                        <option :value="null" disabled>-- Chọn phòng --</option>
                        <option
                          v-for="r in allRooms"
                          :key="r.id"
                          :value="r.id"
                          :disabled="r.trangThai === 'Đang bảo trì' || r.trangThai === 'Ngừng kinh doanh'"
                        >
                          {{ r.tenPhong }} — {{ r.loaiPhong?.tenLoai }} (Tối đa {{ r.loaiPhong?.sucChuaToiDa }} khách - {{ formatCurrency(r.loaiPhong?.giaTieuChuan) }}/đêm)
                          {{ r.trangThai === 'Đang bảo trì' ? ' [BẢO TRÌ]' : '' }}
                        </option>
                      </select>
                    </div>

                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold text-navy mb-1">
                        Số khách phòng này
                      </label>
                      <input
                        type="number"
                        class="form-control form-control-sm bg-white"
                        min="1"
                        v-model.number="roomItem.soLuongKhach"
                        :disabled="isUpdating"
                        required
                      />
                    </div>

                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold text-navy mb-1">
                        Đơn giá áp dụng
                      </label>
                      <div class="small fw-bold text-teal mt-1">
                        {{ formatCurrency(roomItem.giaPhongApDung) }} / đêm
                      </div>
                    </div>

                    <div class="col-12 col-md-1 text-md-end">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-danger border-0 mt-3"
                        title="Xóa phòng này"
                        :disabled="isUpdating || editForm.danhSachPhong.length <= 1"
                        @click="removeRoomFromEditForm(index)"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Tổng khách cả đơn -->
                <div class="row g-2 align-items-center mt-3 pt-3 border-top">
                  <div class="col-12 col-md-6">
                    <label class="form-label small fw-semibold text-navy mb-1">
                      Tổng số lượng khách toàn đơn:
                    </label>
                    <input
                      type="number"
                      class="form-control bg-light"
                      min="1"
                      v-model.number="editForm.soLuongKhach"
                      :disabled="isUpdating"
                      required
                    />
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="small text-muted mt-3">
                      Tổng sức chứa các phòng đã chọn:
                      <strong class="text-navy">{{ calculateTotalCapacity() }} người</strong>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light py-2 px-4 border-0 d-flex justify-content-between">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              :disabled="isUpdating"
              @click="closeEditModal"
            >
              Hủy bỏ
            </button>
            <button
              type="button"
              class="btn btn-teal text-white rounded-3 px-4 fw-semibold"
              :disabled="isUpdating"
              @click="submitEditBooking"
            >
              <span v-if="isUpdating" class="spinner-border spinner-border-sm me-1" role="status"></span>
              <i v-else class="bi bi-check2-circle me-1"></i>
              Lưu thay đổi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: CẬP NHẬT NHANH TRẠNG THÁI (Alternative Flow 3a)         -->
    <!-- ============================================================== -->
    <div
      v-if="showStatusModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <!-- Modal Header -->
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-arrow-left-right fs-4 text-teal"></i>
              <h5 class="modal-title fw-bold mb-0">
                Chuyển Trạng Thái Đơn #DP-{{ statusModalItem?.id }}
              </h5>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              aria-label="Close"
              :disabled="isUpdatingStatus"
              @click="closeStatusModal"
            ></button>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4">
            <div v-if="statusError" class="alert alert-danger border-danger rounded-3 p-3 small mb-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ statusError }}
            </div>

            <div class="mb-3 small">
              <span class="text-muted">Khách hàng: </span>
              <strong class="text-navy">{{ statusModalItem?.khachHang?.hoTen }}</strong>
            </div>

            <div class="mb-3 small">
              <span class="text-muted">Trạng thái hiện tại: </span>
              <span
                class="badge rounded-pill px-2.5 py-1 ms-1 fw-semibold"
                :class="getStatusBadgeClass(statusModalItem?.trangThai)"
              >
                {{ statusModalItem?.trangThai }}
              </span>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-semibold text-navy mb-1">
                Chọn trạng thái mới <span class="text-danger">*</span>
              </label>
              <select
                class="form-select bg-light"
                v-model="selectedNewStatus"
                :disabled="isUpdatingStatus"
              >
                <option
                  v-for="st in getNextValidStatuses(statusModalItem?.trangThai)"
                  :key="st"
                  :value="st"
                >
                  {{ st }}
                </option>
              </select>
            </div>

            <div class="p-3 rounded-3 bg-light border small text-muted">
              <i class="bi bi-info-circle text-teal me-1"></i>
              <strong>Quy tắc nghiệp vụ:</strong> Khi chuyển sang <em>Đã hủy</em>, hệ thống sẽ tự động giải phóng phòng và cập nhật trạng thái phòng vật lý về <em>Sẵn sàng</em> (FR18).
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light py-2 px-4 border-0 d-flex justify-content-between">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              :disabled="isUpdatingStatus"
              @click="closeStatusModal"
            >
              Đóng
            </button>
            <button
              type="button"
              class="btn btn-teal text-white rounded-3 px-4 fw-semibold"
              :disabled="isUpdatingStatus || !selectedNewStatus"
              @click="submitQuickStatus"
            >
              <span v-if="isUpdatingStatus" class="spinner-border spinner-border-sm me-1" role="status"></span>
              <i v-else class="bi bi-check2-circle me-1"></i>
              Xác nhận chuyển
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
import axios from 'axios'

// 1. Trạng thái người dùng & Token
const token = ref('')
const currentUser = ref({})

// Kiểm tra quyền Lễ tân hoặc Quản lý
const isStaff = computed(() => {
  const role = (currentUser.value?.vaiTro || '').toLowerCase().trim()
  return ['lễ tân', 'quản lý', 'admin', 'receptionist', 'manager'].includes(role)
})

// 2. Dữ liệu danh sách đơn & Loading state
const bookings = ref([])
const isLoading = ref(false)
const fetchError = ref('')

// 3. Danh sách phòng vật lý (dùng để chọn khi đổi phòng)
const allRooms = ref([])

// 4. Bộ lọc & Tìm kiếm
const searchKeyword = ref('')
const activeFilterStatus = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

// 5. Phân trang
const currentPage = ref(1)
const pageSize = ref(10)

// 6. Modal 1: Chi tiết đơn
const showDetailModal = ref(false)
const selectedBookingDetail = ref(null)
const isLoadingDetail = ref(false)
const detailError = ref('')

// 7. Modal 2: Điều chỉnh đơn
const showEditModal = ref(false)
const isUpdating = ref(false)
const conflictError = ref(null)
const validationError = ref('')
const fieldErrors = ref({})

const editForm = reactive({
  id: null,
  tenKhachHang: '',
  trangThai: '',
  originalStatus: '',
  ngayNhanPhong: '',
  ngayTraPhong: '',
  soLuongKhach: 1,
  tienCoc: 0,
  danhSachPhong: [],
})

// 8. Modal 3: Đổi trạng thái nhanh
const showStatusModal = ref(false)
const statusModalItem = ref(null)
const selectedNewStatus = ref('')
const isUpdatingStatus = ref(false)
const statusError = ref('')

// 9. Toast Notification
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

// 10. Định dạng tiền tệ & thời gian
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

// Chuyển chuỗi date sang format input datetime-local: YYYY-MM-DDTHH:mm
const formatInputDateTime = (val) => {
  if (!val) return ''
  const d = new Date(val)
  if (isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

// Tính số đêm lưu trú
const calculateNights = (startStr, endStr) => {
  if (!startStr || !endStr) return 0
  const start = new Date(startStr).getTime()
  const end = new Date(endStr).getTime()
  if (isNaN(start) || isNaN(end) || end <= start) return 0
  const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
  return Math.max(1, diffDays)
}

// Badge màu cho từng trạng thái
const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Chờ xác nhận':
      return 'bg-warning-subtle text-warning border border-warning-subtle'
    case 'Đã cọc':
      return 'bg-info-subtle text-info border border-info-subtle'
    case 'Đã xác nhận':
      return 'bg-primary-subtle text-primary border border-primary-subtle'
    case 'Đã nhận phòng':
    case 'Đang ở':
      return 'bg-success-subtle text-success border border-success-subtle'
    case 'Đã trả':
    case 'Đã hoàn thành':
      return 'bg-secondary-subtle text-secondary border border-secondary-subtle'
    case 'Đã hủy':
      return 'bg-danger-subtle text-danger border border-danger-subtle'
    default:
      return 'bg-light text-dark border'
  }
}

const getStatusIconClass = (status) => {
  switch (status) {
    case 'Chờ xác nhận':
      return 'bi bi-hourglass-split'
    case 'Đã cọc':
      return 'bi bi-cash-coin'
    case 'Đã xác nhận':
      return 'bi bi-check2-circle'
    case 'Đã nhận phòng':
    case 'Đang ở':
      return 'bi bi-door-open-fill'
    case 'Đã trả':
    case 'Đã hoàn thành':
      return 'bi bi-check-all'
    case 'Đã hủy':
      return 'bi bi-x-circle-fill'
    default:
      return 'bi bi-circle'
  }
}

// Đơn ở trạng thái kết thúc (Terminal Status) thì không được chỉnh sửa
const isTerminalStatus = (status) => {
  return ['Đã hủy', 'Đã trả', 'Đã hoàn thành'].includes(status)
}

// Thống kê nhanh
const stats = computed(() => {
  const all = bookings.value
  return {
    total: all.length,
    pending: all.filter((b) => b.trangThai === 'Chờ xác nhận').length,
    confirmed: all.filter((b) => b.trangThai === 'Đã xác nhận').length,
    deposited: all.filter((b) => b.trangThai === 'Đã cọc').length,
    inHouse: all.filter((b) => ['Đang ở', 'Đã nhận phòng'].includes(b.trangThai)).length,
    cancelled: all.filter((b) => b.trangThai === 'Đã hủy').length,
  }
})

// Danh sách sau khi lọc tại client
const filteredBookings = computed(() => {
  return bookings.value.filter((b) => {
    // 1. Trạng thái
    if (activeFilterStatus.value && b.trangThai !== activeFilterStatus.value) {
      return false
    }

    // 2. Từ khóa (mã đơn, tên khách, SĐT)
    if (searchKeyword.value) {
      const kw = searchKeyword.value.toLowerCase().trim()
      const matchId = String(b.id).includes(kw)
      const matchName = b.khachHang?.hoTen?.toLowerCase().includes(kw)
      const matchPhone = b.khachHang?.soDienThoai?.includes(kw)
      if (!matchId && !matchName && !matchPhone) {
        return false
      }
    }

    // 3. Khoảng ngày nhận phòng
    if (filterDateFrom.value) {
      const from = new Date(filterDateFrom.value).getTime()
      const checkIn = new Date(b.ngayNhanPhong).getTime()
      if (checkIn < from) return false
    }
    if (filterDateTo.value) {
      const to = new Date(filterDateTo.value + 'T23:59:59').getTime()
      const checkIn = new Date(b.ngayNhanPhong).getTime()
      if (checkIn > to) return false
    }

    return true
  })
})

// Dữ liệu phân trang
const totalPages = computed(() => {
  return Math.ceil(filteredBookings.value.length / pageSize.value) || 1
})

const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredBookings.value.slice(start, start + pageSize.value)
})

// 11. Các API Calls trực tiếp bằng Axios
const getHeaders = () => ({
  Authorization: `Bearer ${token.value}`,
  Accept: 'application/json',
})

// Lấy danh sách đơn từ API
const fetchBookings = async () => {
  if (!token.value) {
    fetchError.value = 'Bạn chưa đăng nhập. Vui lòng đăng nhập để tiếp tục.'
    return
  }

  isLoading.value = true
  fetchError.value = ''

  try {
    const params = {}
    if (searchKeyword.value) params.keyword = searchKeyword.value
    if (activeFilterStatus.value) params.trangThai = activeFilterStatus.value
    if (filterDateFrom.value) params.ngayNhanTu = filterDateFrom.value
    if (filterDateTo.value) params.ngayNhanDen = filterDateTo.value

    const res = await axios.get('/api/v1/bookings', {
      headers: getHeaders(),
      params,
    })

    if (res.data?.success) {
      bookings.value = res.data.data || []
      currentPage.value = 1
    } else {
      fetchError.value = res.data?.message || 'Không thể tải danh sách đơn đặt phòng.'
    }
  } catch (err) {
    if (err.response?.status === 403) {
      fetchError.value = 'Bạn không có quyền truy cập danh sách đặt phòng.'
    } else if (err.response?.status === 401) {
      fetchError.value = 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'
    } else {
      fetchError.value = err.response?.data?.message || 'Lỗi kết nối khi tải danh sách đơn.'
    }
  } finally {
    isLoading.value = false
  }
}

// Lấy danh mục phòng vật lý để phục vụ chọn phòng
const fetchAllRooms = async () => {
  try {
    const res = await axios.get('/api/v1/rooms', {
      headers: getHeaders(),
    })
    if (res.data?.success) {
      allRooms.value = res.data.data || []
    }
  } catch (err) {
    console.error('Không thể tải danh mục phòng:', err)
  }
}

// 12. Xử lý bộ lọc
const handleFilterSubmit = () => {
  currentPage.value = 1
  fetchBookings()
}

const setQuickFilterStatus = (status) => {
  activeFilterStatus.value = status
  handleFilterSubmit()
}

const resetFilter = () => {
  searchKeyword.value = ''
  activeFilterStatus.value = ''
  filterDateFrom.value = ''
  filterDateTo.value = ''
  currentPage.value = 1
  fetchBookings()
}

// 13. Xử lý Modal 1: Chi tiết đơn
const openDetailModal = async (id) => {
  showDetailModal.value = true
  isLoadingDetail.value = true
  detailError.value = ''
  selectedBookingDetail.value = null

  try {
    const res = await axios.get(`/api/v1/bookings/${id}`, {
      headers: getHeaders(),
    })
    if (res.data?.success) {
      selectedBookingDetail.value = res.data.data
    } else {
      detailError.value = res.data?.message || 'Không tìm thấy chi tiết đơn đặt phòng.'
    }
  } catch (err) {
    detailError.value = err.response?.data?.message || 'Lỗi khi tải chi tiết đơn đặt phòng.'
  } finally {
    isLoadingDetail.value = false
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedBookingDetail.value = null
  detailError.value = ''
}

// 14. Xử lý Modal 2: Điều chỉnh đơn đặt phòng (UC05)
const openEditModal = (item) => {
  closeDetailModal()
  closeStatusModal()

  conflictError.value = null
  validationError.value = ''
  fieldErrors.value = {}

  editForm.id = item.id
  editForm.tenKhachHang = item.khachHang?.hoTen || 'Khách vãng lai'
  editForm.trangThai = item.trangThai
  editForm.originalStatus = item.trangThai
  editForm.ngayNhanPhong = formatInputDateTime(item.ngayNhanPhong)
  editForm.ngayTraPhong = formatInputDateTime(item.ngayTraPhong)
  editForm.soLuongKhach = item.soLuongKhach || 1
  editForm.tienCoc = item.tienCoc || 0

  // Sao chép danh sách phòng
  editForm.danhSachPhong = (item.danhSachPhong || []).map((p) => ({
    maPhong: p.maPhong,
    soLuongKhach: p.soLuongKhach || 1,
    giaPhongApDung: p.giaPhongApDung || 0,
    tenPhong: p.tenPhong,
  }))

  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  conflictError.value = null
  validationError.value = ''
  fieldErrors.value = {}
}

// Danh sách trạng thái được phép chuyển sang từ trạng thái hiện tại
const validStatusTransitions = {
  'Chờ xác nhận': ['Chờ xác nhận', 'Đã xác nhận', 'Đã cọc', 'Đã hủy'],
  'Đã cọc': ['Đã cọc', 'Đã xác nhận', 'Đã nhận phòng', 'Đang ở', 'Đã hủy'],
  'Đã xác nhận': ['Đã xác nhận', 'Đã nhận phòng', 'Đang ở', 'Đã hủy'],
  'Đã nhận phòng': ['Đã nhận phòng', 'Đã trả', 'Đã hoàn thành'],
  'Đang ở': ['Đang ở', 'Đã trả', 'Đã hoàn thành'],
}

const availableStatusOptions = computed(() => {
  const current = editForm.originalStatus
  return validStatusTransitions[current] || [current]
})

const getNextValidStatuses = (currentStatus) => {
  const list = validStatusTransitions[currentStatus] || []
  return list.filter((s) => s !== currentStatus)
}

// Khi đổi phòng trong dropdown
const handleRoomSelectChange = (roomItem) => {
  const selected = allRooms.value.find((r) => r.id === roomItem.maPhong)
  if (selected) {
    roomItem.giaPhongApDung = selected.loaiPhong?.giaTieuChuan || 0
    roomItem.tenPhong = selected.tenPhong
  }
}

// Thêm phòng vào form điều chỉnh
const addRoomToEditForm = () => {
  const availableRoom = allRooms.value.find(
    (r) => !editForm.danhSachPhong.some((p) => p.maPhong === r.id) &&
           r.trangThai !== 'Đang bảo trì' &&
           r.trangThai !== 'Ngừng kinh doanh'
  )

  editForm.danhSachPhong.push({
    maPhong: availableRoom ? availableRoom.id : null,
    soLuongKhach: 1,
    giaPhongApDung: availableRoom?.loaiPhong?.giaTieuChuan || 0,
    tenPhong: availableRoom?.tenPhong || '',
  })
}

// Xóa phòng khỏi form điều chỉnh
const removeRoomFromEditForm = (index) => {
  if (editForm.danhSachPhong.length > 1) {
    editForm.danhSachPhong.splice(index, 1)
  }
}

// Tính tổng sức chứa
const calculateTotalCapacity = () => {
  let total = 0
  for (const item of editForm.danhSachPhong) {
    const room = allRooms.value.find((r) => r.id === item.maPhong)
    if (room && room.loaiPhong) {
      total += Number(room.loaiPhong.sucChuaToiDa || 0)
    }
  }
  return total
}

// Gửi cập nhật điều chỉnh đơn (PUT /api/v1/bookings/{id})
const submitEditBooking = async () => {
  conflictError.value = null
  validationError.value = ''
  fieldErrors.value = {}

  // Kiểm tra client-side cơ bản
  const nights = calculateNights(editForm.ngayNhanPhong, editForm.ngayTraPhong)
  if (nights <= 0) {
    validationError.value = 'Ngày trả phòng phải sau ngày nhận phòng.'
    return
  }

  isUpdating.value = true

  const payload = {
    trangThai: editForm.trangThai,
    ngayNhanPhong: editForm.ngayNhanPhong.replace('T', ' ') + ':00',
    ngayTraPhong: editForm.ngayTraPhong.replace('T', ' ') + ':00',
    soLuongKhach: editForm.soLuongKhach,
    tienCoc: editForm.tienCoc,
    danhSachPhong: editForm.danhSachPhong.map((p) => ({
      maPhong: p.maPhong,
      soLuongKhach: p.soLuongKhach,
      giaPhongApDung: p.giaPhongApDung,
    })),
  }

  try {
    const res = await axios.put(`/api/v1/bookings/${editForm.id}`, payload, {
      headers: getHeaders(),
    })

    if (res.data?.success) {
      showToast('Cập nhật thông tin đơn đặt phòng thành công!', 'success')
      closeEditModal()
      await fetchBookings()
      await fetchAllRooms()
    } else {
      validationError.value = res.data?.message || 'Cập nhật thất bại.'
    }
  } catch (err) {
    const status = err.response?.status
    const data = err.response?.data

    if (status === 409) {
      // Xung đột phòng trùng lịch
      conflictError.value = {
        message: data?.message || 'Phòng đã có đơn đặt trong khoảng thời gian này.',
        rooms: data?.errors?.phongBiTrung || [],
      }
    } else if (status === 422) {
      // Lỗi validation
      validationError.value = data?.message || 'Thông tin nhập không hợp lệ.'
      fieldErrors.value = data?.errors || {}
    } else if (status === 403) {
      validationError.value = 'Bạn không có quyền thực hiện thao tác này.'
    } else {
      validationError.value = data?.message || 'Lỗi hệ thống khi cập nhật đơn.'
    }
  } finally {
    isUpdating.value = false
  }
}

// 15. Xử lý Modal 3: Chuyển trạng thái nhanh (Alternative Flow 3a)
const openQuickStatusModal = (item) => {
  closeDetailModal()
  statusModalItem.value = item
  statusError.value = ''
  const nextList = getNextValidStatuses(item.trangThai)
  selectedNewStatus.value = nextList.length > 0 ? nextList[0] : ''
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  statusModalItem.value = null
  selectedNewStatus.value = ''
  statusError.value = ''
}

const submitQuickStatus = async () => {
  if (!statusModalItem.value || !selectedNewStatus.value) return

  isUpdatingStatus.value = true
  statusError.value = ''

  try {
    const res = await axios.put(
      `/api/v1/bookings/${statusModalItem.value.id}/status`,
      { trangThai: selectedNewStatus.value },
      { headers: getHeaders() }
    )

    if (res.data?.success) {
      showToast(`Chuyển trạng thái sang "${selectedNewStatus.value}" thành công!`, 'success')
      closeStatusModal()
      await fetchBookings()
      await fetchAllRooms()
    } else {
      statusError.value = res.data?.message || 'Không thể cập nhật trạng thái.'
    }
  } catch (err) {
    const data = err.response?.data
    statusError.value = data?.message || 'Lỗi khi cập nhật trạng thái đơn.'
  } finally {
    isUpdatingStatus.value = false
  }
}

// 16. Chuyển đổi tài khoản nhanh phục vụ kiểm thử (Quick-Test switcher)
const quickSwitchUser = async (username, password, roleLabel) => {
  isLoading.value = true
  fetchError.value = ''

  try {
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      matKhau: password,
    })

    if (res.data?.success) {
      const { access_token, user } = res.data.data
      token.value = access_token
      currentUser.value = user
      localStorage.setItem('access_token', access_token)
      localStorage.setItem('user', JSON.stringify(user))

      showToast(`Đã chuyển sang tài khoản ${roleLabel} (${username})!`, 'success')
      await fetchBookings()
      await fetchAllRooms()
    } else {
      showToast('Đăng nhập tài khoản kiểm thử thất bại.', 'danger')
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Lỗi đăng nhập nhanh.', 'danger')
  } finally {
    isLoading.value = false
  }
}

// 17. Khởi tạo khi nạp component
onMounted(() => {
  token.value = localStorage.getItem('access_token') || ''
  try {
    const uStr = localStorage.getItem('user')
    currentUser.value = uStr ? JSON.parse(uStr) : {}
  } catch {
    currentUser.value = {}
  }

  fetchBookings()
  fetchAllRooms()
})
</script>

<style scoped>
/* Design Tokens */
.text-navy {
  color: #1e3a5f !important;
}

.bg-navy {
  background-color: #1e3a5f !important;
}

.btn-navy {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
  color: #ffffff;
}

.btn-navy:hover {
  background-color: #152a45;
  border-color: #152a45;
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

.header-icon-box {
  width: 54px;
  height: 54px;
}

.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.cursor-pointer {
  cursor: pointer;
}

.border-active {
  border: 2px solid #0f766e !important;
}

.booking-row:hover {
  background-color: #f8fafc;
}

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
</style>
