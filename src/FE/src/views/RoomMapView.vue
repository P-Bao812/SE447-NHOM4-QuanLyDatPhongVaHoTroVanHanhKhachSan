<template>
  <div class="room-map-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- 1. Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/admin/dashboard" class="text-decoration-none text-teal">
              Vận hành PMS
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Sơ đồ phòng khách sạn (UC18)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-map-fill fs-3 text-warning"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Sơ Đồ Phòng Khách Sạn</h2>
              <p class="text-muted mb-0 small">
                Theo dõi trực quan trạng thái buồng phòng theo tầng, tình trạng lưu trú, dọn phòng và bảo trì (Phân hệ PMS - UC18).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isAuthorizedRole ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isAuthorizedRole ? 'bi bi-shield-check me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Người dùng' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ roleDescriptionText }}
            </span>
          </div>
        </div>
      </div>

      <!-- 2. Quick Test & Switch Account Toolbar (Hỗ trợ kiểm thử trực tiếp) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi vai trò:</span>
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
                :class="currentUser.tenDangNhap === 'letan01' ? 'btn-secondary text-white' : 'btn-outline-secondary'"
                :disabled="isLoading"
                @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
              >
                <i class="bi bi-person-badge me-1"></i> Lễ tân (letan01)
              </button>
              <button
                type="button"
                class="btn btn-sm rounded-3"
                :class="currentUser.vaiTro === 'Buồng phòng' ? 'btn-teal text-white' : 'btn-outline-teal'"
                :disabled="isLoading"
                @click="quickSetHousekeeperRole"
              >
                <i class="bi bi-house-door me-1"></i> Buồng phòng (Bảo mật SĐT/CCCD)
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

            <!-- Nút Làm mới & Tự động cập nhật -->
            <div class="d-flex align-items-center gap-3">
              <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                <input
                  class="form-check-input custom-switch cursor-pointer"
                  type="checkbox"
                  role="switch"
                  id="autoRefreshSwitch"
                  v-model="autoRefresh"
                />
                <label class="form-check-label small text-muted cursor-pointer" for="autoRefreshSwitch">
                  Tự động làm mới (30s)
                </label>
              </div>

              <button
                type="button"
                class="btn btn-sm btn-outline-navy rounded-3 px-3"
                :disabled="isLoading"
                @click="fetchRoomMap(false)"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Màn hình Cảnh báo khi bị 403 Forbidden (Khách hàng truy cập) -->
      <div v-if="accessDenied" class="card border-0 shadow-sm rounded-4 mb-4 bg-white text-center py-5">
        <div class="card-body">
          <div class="avatar-denied mx-auto bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mb-3">
            <i class="bi bi-shield-x fs-1"></i>
          </div>
          <h4 class="fw-bold text-navy mb-2">Truy Cập Bị Từ Chối (403 Forbidden)</h4>
          <p class="text-muted mx-auto mb-4" style="max-width: 550px;">
            {{ accessDeniedMessage || 'Bạn không có quyền truy cập sơ đồ phòng. Chức năng chỉ dành riêng cho Lễ tân, Quản lý khách sạn và Buồng phòng.' }}
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button
              type="button"
              class="btn btn-navy text-white rounded-3 px-4"
              @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
            >
              <i class="bi bi-person-badge me-1"></i> Đăng nhập tài khoản Lễ tân
            </button>
            <button
              type="button"
              class="btn btn-teal text-white rounded-3 px-4"
              @click="quickSwitchUser('admin', '123456', 'Quản lý')"
            >
              <i class="bi bi-shield-lock me-1"></i> Đăng nhập tài khoản Quản lý
            </button>
          </div>
        </div>
      </div>

      <!-- Nội dung chính: Khi có quyền truy cập -->
      <div v-else>
        <!-- 3. KPI Thống kê trạng thái sơ đồ phòng -->
        <div class="row g-3 mb-4">
          <!-- Tổng số phòng -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === '' }"
              @click="quickFilterStatus('')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small fw-semibold">Tất cả phòng</span>
                  <i class="bi bi-buildings-fill text-navy fs-5"></i>
                </div>
                <h3 class="fw-bold text-navy mb-0">{{ kpiSummary.total }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Toàn khách sạn</small>
              </div>
            </div>
          </div>

          <!-- Phòng Trống -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === 'Trống' }"
              @click="quickFilterStatus('Trống')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-success small fw-semibold">Trống</span>
                  <i class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ kpiSummary.trong }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Sẵn sàng đón khách</small>
              </div>
            </div>
          </div>

          <!-- Phòng Đang ở -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === 'Đang ở' }"
              @click="quickFilterStatus('Đang ở')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-primary small fw-semibold">Đang ở</span>
                  <i class="bi bi-person-fill text-primary fs-5"></i>
                </div>
                <h3 class="fw-bold text-primary mb-0">{{ kpiSummary.dangO }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Khách đang lưu trú</small>
              </div>
            </div>
          </div>

          <!-- Phòng Đã đặt -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === 'Đã đặt' }"
              @click="quickFilterStatus('Đã đặt')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-warning-custom small fw-semibold">Đã đặt</span>
                  <i class="bi bi-calendar-check-fill text-warning-custom fs-5"></i>
                </div>
                <h3 class="fw-bold text-warning-custom mb-0">{{ kpiSummary.daDat }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Chờ khách nhận</small>
              </div>
            </div>
          </div>

          <!-- Phòng Cần dọn dẹp -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === 'Cần dọn dẹp' }"
              @click="quickFilterStatus('Cần dọn dẹp')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-purple small fw-semibold">Cần dọn dẹp</span>
                  <i class="bi bi-stars text-purple fs-5"></i>
                </div>
                <h3 class="fw-bold text-purple mb-0">{{ kpiSummary.canDonDep }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Chờ buồng phòng</small>
              </div>
            </div>
          </div>

          <!-- Đang bảo trì / Ngưng khai thác -->
          <div class="col-6 col-md-4 col-xl-2">
            <div
              class="card border-0 shadow-sm rounded-4 kpi-card h-100 cursor-pointer"
              :class="{ 'kpi-card-active': filters.trangThai === 'Đang bảo trì' }"
              @click="quickFilterStatus('Đang bảo trì')"
            >
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-secondary small fw-semibold">Bảo trì / Ngưng</span>
                  <i class="bi bi-wrench-adjustable-circle-fill text-secondary fs-5"></i>
                </div>
                <h3 class="fw-bold text-secondary mb-0">{{ kpiSummary.dangBaoTri + kpiSummary.ngungKhaiThac }}</h3>
                <small class="text-muted" style="font-size: 0.72rem;">Tạm dừng sử dụng</small>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Thanh Bộ Lọc & Tìm Kiếm Đa Năng -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
          <div class="card-body p-3">
            <div class="row g-3 align-items-center">
              <!-- Ô tìm kiếm keyword -->
              <div class="col-lg-3 col-md-6">
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-search"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control bg-light border-start-0"
                    placeholder="Tìm số phòng, loại phòng..."
                    v-model="filters.keyword"
                    @keyup.enter="handleSearch"
                  />
                </div>
              </div>

              <!-- Lọc theo Tầng -->
              <div class="col-lg-2 col-md-6">
                <select class="form-select bg-light" v-model="filters.tang" @change="handleSearch">
                  <option value="">Tất cả các tầng</option>
                  <option v-for="t in floorList" :key="t" :value="t">
                    Tầng {{ t }}
                  </option>
                </select>
              </div>

              <!-- Lọc theo Loại Phòng -->
              <div class="col-lg-3 col-md-6">
                <select class="form-select bg-light" v-model="filters.maLoaiPhong" @change="handleSearch">
                  <option value="">Tất cả loại phòng</option>
                  <option v-for="lp in roomTypeList" :key="lp.id" :value="lp.id">
                    {{ lp.tenLoai }} ({{ formatCurrency(lp.giaTieuChuan) }})
                  </option>
                </select>
              </div>

              <!-- Lọc theo Trạng Thái Sơ Đồ -->
              <div class="col-lg-2 col-md-6">
                <select class="form-select bg-light" v-model="filters.trangThai" @change="handleSearch">
                  <option value="">Tất cả trạng thái</option>
                  <option value="Trống">🟢 Trống / Sẵn sàng</option>
                  <option value="Đang ở">🔵 Đang ở</option>
                  <option value="Đã đặt">🟡 Đã đặt</option>
                  <option value="Cần dọn dẹp">🟣 Cần dọn dẹp</option>
                  <option value="Đang bảo trì">⚪ Đang bảo trì</option>
                  <option value="Ngưng khai thác">⚫ Ngưng khai thác</option>
                </select>
              </div>

              <!-- Nút Lọc & Nút Xóa Lọc -->
              <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button
                  type="button"
                  class="btn btn-navy text-white rounded-3 flex-grow-1"
                  :disabled="isLoading"
                  @click="handleSearch"
                >
                  <i class="bi bi-funnel-fill me-1"></i> Lọc
                </button>
                <button
                  type="button"
                  class="btn btn-outline-secondary rounded-3 px-3"
                  :disabled="isLoading"
                  title="Xóa toàn bộ bộ lọc"
                  @click="resetFilters"
                >
                  <i class="bi bi-x-circle me-1"></i> Xóa lọc
                </button>
              </div>
            </div>

            <!-- Chú thích mã màu sơ đồ -->
            <div class="d-flex flex-wrap align-items-center gap-3 pt-3 mt-3 border-top small text-muted">
              <span class="fw-bold text-navy">
                <i class="bi bi-palette me-1"></i> Chú thích màu:
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-success"></span>
                <span>Trống / Sẵn sàng</span>
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-primary"></span>
                <span>Đang ở (Có khách)</span>
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-warning-custom"></span>
                <span>Đã đặt (Chờ nhận)</span>
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-purple"></span>
                <span>Cần dọn dẹp (Buồng phòng)</span>
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-secondary"></span>
                <span>Đang bảo trì</span>
              </span>
              <span class="d-flex align-items-center gap-1.5">
                <span class="color-dot bg-dark"></span>
                <span>Ngưng khai thác</span>
              </span>
            </div>
          </div>
        </div>

        <!-- 5. Vùng Loading Spinner -->
        <div v-if="isLoading && rooms.length === 0" class="text-center py-5">
          <div class="spinner-border text-navy mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Đang tải...</span>
          </div>
          <p class="text-muted fw-semibold">Đang nạp sơ đồ phòng khách sạn...</p>
        </div>

        <!-- 6. Vùng Hiển thị Khi Không có Kết Quả Phù Hợp (Empty State) -->
        <div v-else-if="rooms.length === 0" class="card border-0 shadow-sm rounded-4 mb-4 bg-white text-center py-5">
          <div class="card-body">
            <div class="empty-icon-box mx-auto bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mb-3">
              <i class="bi bi-door-closed fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold text-navy mb-2">{{ emptyMessage || 'Không có phòng nào ở tình trạng này.' }}</h5>
            <p class="text-muted mx-auto mb-4" style="max-width: 500px;">
              Không tìm thấy phòng nào phù hợp với điều kiện tìm kiếm hoặc bộ lọc hiện tại. Vui lòng thử lại với các tiêu chí khác hoặc xóa bộ lọc để xem toàn bộ sơ đồ.
            </p>
            <button type="button" class="btn btn-navy text-white rounded-3 px-4" @click="resetFilters">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc và tải lại
            </button>
          </div>
        </div>

        <!-- 7. Hiển Thị Sơ Đồ Phòng Theo Tầng -->
        <div v-else class="floor-plan-container">
          <div
            v-for="(floorRooms, floorNumber) in groupedByFloor"
            :key="floorNumber"
            class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden"
          >
            <!-- Header Tầng -->
            <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2">
                <div class="floor-badge bg-navy text-white fw-bold rounded-3 px-3 py-1">
                  TẦNG {{ floorNumber }}
                </div>
                <span class="fw-semibold text-navy">
                  Khu vực phòng lầu {{ floorNumber }}
                </span>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                  {{ floorRooms.length }} phòng
                </span>
              </div>

              <!-- Thống kê nhanh theo tầng -->
              <div class="d-flex align-items-center gap-3 small text-muted flex-wrap">
                <span class="d-flex align-items-center gap-1">
                  <span class="status-indicator bg-success"></span>
                  {{ countByStatus(floorRooms, 'Trống') }} trống
                </span>
                <span class="d-flex align-items-center gap-1">
                  <span class="status-indicator bg-primary"></span>
                  {{ countByStatus(floorRooms, 'Đang ở') }} đang ở
                </span>
                <span class="d-flex align-items-center gap-1">
                  <span class="status-indicator bg-warning-custom"></span>
                  {{ countByStatus(floorRooms, 'Đã đặt') }} đã đặt
                </span>
                <span class="d-flex align-items-center gap-1">
                  <span class="status-indicator bg-purple"></span>
                  {{ countByStatus(floorRooms, 'Cần dọn dẹp') }} cần dọn
                </span>
              </div>
            </div>

            <!-- Lưới các phòng trong tầng -->
            <div class="card-body p-3 p-md-4">
              <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
                <div v-for="room in floorRooms" :key="room.id" class="col">
                  <div
                    class="room-card h-100 rounded-4 p-3 d-flex flex-column justify-content-between cursor-pointer position-relative shadow-sm"
                    :class="getRoomCardBorderClass(room.trangThaiSoDo)"
                    @click="openRoomDetail(room)"
                  >
                    <!-- Header Card: Số phòng & Icon trạng thái -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div>
                        <h5 class="fw-bold text-navy mb-0 room-title">
                          {{ room.tenPhong }}
                        </h5>
                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem; max-width: 110px;">
                          {{ room.loaiPhong?.tenLoai || 'Chưa phân loại' }}
                        </small>
                      </div>

                      <span class="status-icon" :title="room.trangThaiSoDo">
                        <i v-if="room.trangThaiSoDo === 'Trống'" class="bi bi-check-circle-fill text-success fs-5"></i>
                        <i v-else-if="room.trangThaiSoDo === 'Đang ở'" class="bi bi-person-fill text-primary fs-5"></i>
                        <i v-else-if="room.trangThaiSoDo === 'Đã đặt'" class="bi bi-calendar-check-fill text-warning-custom fs-5"></i>
                        <i v-else-if="room.trangThaiSoDo === 'Cần dọn dẹp'" class="bi bi-stars text-purple fs-5"></i>
                        <i v-else-if="room.trangThaiSoDo === 'Đang bảo trì'" class="bi bi-wrench-adjustable text-secondary fs-5"></i>
                        <i v-else class="bi bi-slash-circle text-dark fs-5"></i>
                      </span>
                    </div>

                    <!-- Giữa Card: Thông tin giá & Khách đang ở (nếu có) -->
                    <div class="mb-2">
                      <div class="small fw-semibold text-teal">
                        {{ formatCurrency(room.loaiPhong?.giaTieuChuan || 0) }}
                      </div>

                      <!-- Thông tin khách nếu phòng đang ở hoặc đã đặt -->
                      <div
                        v-if="room.thongTinLuuTru?.khachHang?.hoTen"
                        class="guest-preview-badge mt-1 rounded-2 px-1.5 py-0.5 small text-truncate"
                        :class="room.trangThaiSoDo === 'Đang ở' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning-dark'"
                      >
                        <i class="bi bi-person me-1"></i>
                        <span>{{ room.thongTinLuuTru.khachHang.hoTen }}</span>
                      </div>
                      <div v-else class="text-muted small" style="font-size: 0.7rem;">
                        Tối đa: {{ room.loaiPhong?.sucChuaToiDa || 2 }} khách
                      </div>
                    </div>

                    <!-- Footer Card: Badge trạng thái với chữ rõ ràng -->
                    <div class="pt-2 border-top">
                      <span
                        class="badge w-100 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1"
                        :class="room.mauTrangThai?.badgeClass || 'bg-secondary text-white'"
                      >
                        <span>{{ room.trangThaiSoDo }}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 8. Modal Chi Tiết Phòng (Room Detail Modal) -->
      <div
        v-if="isDetailModalOpen && selectedRoom"
        class="modal fade show d-block custom-modal-backdrop"
        tabindex="-1"
        role="dialog"
      >
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white px-4 py-3">
              <div class="d-flex align-items-center gap-3">
                <div class="modal-icon-box bg-white text-navy rounded-3 d-flex align-items-center justify-content-center">
                  <i class="bi bi-door-open-fill fs-4"></i>
                </div>
                <div>
                  <h5 class="modal-title fw-bold mb-0">
                    Chi Tiết Phòng: {{ selectedRoom.tenPhong }}
                  </h5>
                  <small class="text-white-50">
                    Tầng {{ selectedRoom.tang }} • {{ selectedRoom.loaiPhong?.tenLoai || 'Loại phòng tiêu chuẩn' }}
                  </small>
                </div>
              </div>

              <button
                type="button"
                class="btn-close btn-close-white"
                aria-label="Close"
                @click="closeRoomDetail"
              ></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
              <!-- Banner trạng thái hiện tại -->
              <div
                class="alert border-0 rounded-3 mb-4 d-flex align-items-center justify-content-between p-3"
                :class="getStatusAlertClass(selectedRoom.trangThaiSoDo)"
              >
                <div class="d-flex align-items-center gap-2">
                  <span class="fs-4">
                    <i v-if="selectedRoom.trangThaiSoDo === 'Trống'" class="bi bi-check-circle-fill text-success"></i>
                    <i v-else-if="selectedRoom.trangThaiSoDo === 'Đang ở'" class="bi bi-person-fill text-primary"></i>
                    <i v-else-if="selectedRoom.trangThaiSoDo === 'Đã đặt'" class="bi bi-calendar-check-fill text-warning-custom"></i>
                    <i v-else-if="selectedRoom.trangThaiSoDo === 'Cần dọn dẹp'" class="bi bi-stars text-purple"></i>
                    <i v-else-if="selectedRoom.trangThaiSoDo === 'Đang bảo trì'" class="bi bi-wrench-adjustable text-secondary"></i>
                    <i v-else class="bi bi-slash-circle text-dark"></i>
                  </span>
                  <div>
                    <h6 class="fw-bold mb-0">Trạng thái sơ đồ: {{ selectedRoom.trangThaiSoDo }}</h6>
                    <small class="text-muted">
                      Trạng thái vận hành cơ sở dữ liệu: <strong>{{ selectedRoom.trangThai }}</strong>
                    </small>
                  </div>
                </div>

                <span
                  class="badge px-3 py-2 fw-semibold fs-6"
                  :class="selectedRoom.mauTrangThai?.badgeClass || 'bg-secondary text-white'"
                >
                  {{ selectedRoom.trangThaiSoDo }}
                </span>
              </div>

              <div class="row g-4">
                <!-- Cột 1: Thông tin phòng & Tiện nghi -->
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                      <h6 class="fw-bold text-navy border-bottom pb-2 mb-3">
                        <i class="bi bi-info-circle-fill text-teal me-1"></i> Thông Tin Phòng
                      </h6>

                      <table class="table table-sm table-borderless mb-0">
                        <tbody>
                          <tr>
                            <td class="text-muted small ps-0" style="width: 140px;">Số / Tên phòng:</td>
                            <td class="fw-bold text-navy">{{ selectedRoom.tenPhong }}</td>
                          </tr>
                          <tr>
                            <td class="text-muted small ps-0">Tầng:</td>
                            <td class="fw-semibold">Tầng {{ selectedRoom.tang }}</td>
                          </tr>
                          <tr>
                            <td class="text-muted small ps-0">Loại phòng:</td>
                            <td class="fw-semibold text-teal">{{ selectedRoom.loaiPhong?.tenLoai || 'Tiêu chuẩn' }}</td>
                          </tr>
                          <tr>
                            <td class="text-muted small ps-0">Đơn giá tiêu chuẩn:</td>
                            <td class="fw-bold text-danger">
                              {{ formatCurrency(selectedRoom.loaiPhong?.giaTieuChuan || 0) }} / đêm
                            </td>
                          </tr>
                          <tr>
                            <td class="text-muted small ps-0">Sức chứa tối đa:</td>
                            <td>
                              <span class="badge bg-light text-dark border">
                                <i class="bi bi-people-fill me-1 text-teal"></i>
                                {{ selectedRoom.loaiPhong?.sucChuaToiDa || 2 }} khách
                              </span>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-muted small ps-0">Mô tả loại phòng:</td>
                            <td class="small text-muted">
                              {{ selectedRoom.loaiPhong?.moTa || 'Đầy đủ tiện nghi máy lạnh, wifi, minibar, TV truyền hình cáp.' }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <!-- Cột 2: Thông tin Lưu trú / Đặt phòng (nếu có) -->
                <div class="col-md-6">
                  <div class="card border-0 shadow-sm rounded-3 h-100 bg-white">
                    <div class="card-body p-3">
                      <h6 class="fw-bold text-navy border-bottom pb-2 mb-3">
                        <i class="bi bi-journal-bookmark-fill text-primary me-1"></i>
                        Thông Tin Đặt Phòng & Lưu Trú
                      </h6>

                      <!-- Có thông tin khách đang ở hoặc đã đặt -->
                      <div v-if="selectedRoom.thongTinLuuTru">
                        <table class="table table-sm table-borderless mb-0">
                          <tbody>
                            <tr>
                              <td class="text-muted small ps-0" style="width: 130px;">Hình thức:</td>
                              <td>
                                <span
                                  class="badge"
                                  :class="selectedRoom.thongTinLuuTru.loai === 'Lưu trú' ? 'bg-primary text-white' : 'bg-warning text-dark'"
                                >
                                  {{ selectedRoom.thongTinLuuTru.loai }}
                                </span>
                              </td>
                            </tr>
                            <tr>
                              <td class="text-muted small ps-0">Mã đơn đặt:</td>
                              <td class="fw-bold text-navy">
                                #BK{{ selectedRoom.thongTinLuuTru.maDatPhong }}
                              </td>
                            </tr>
                            <tr>
                              <td class="text-muted small ps-0">Họ tên khách:</td>
                              <td class="fw-bold text-navy">
                                {{ selectedRoom.thongTinLuuTru.khachHang?.hoTen || 'Khách vãng lai' }}
                              </td>
                            </tr>

                            <!-- Số điện thoại (Bảo mật cho Buồng phòng) -->
                            <tr>
                              <td class="text-muted small ps-0">Số điện thoại:</td>
                              <td>
                                <span v-if="selectedRoom.thongTinLuuTru.khachHang?.soDienThoai" class="fw-semibold">
                                  {{ selectedRoom.thongTinLuuTru.khachHang.soDienThoai }}
                                </span>
                                <span v-else class="badge bg-secondary-subtle text-secondary small">
                                  <i class="bi bi-lock-fill me-1"></i> Bảo mật vai trò
                                </span>
                              </td>
                            </tr>

                            <!-- CCCD / Định danh (Bảo mật cho Buồng phòng) -->
                            <tr>
                              <td class="text-muted small ps-0">CCCD / Hộ chiếu:</td>
                              <td>
                                <span v-if="selectedRoom.thongTinLuuTru.khachHang?.cccd" class="fw-semibold">
                                  {{ selectedRoom.thongTinLuuTru.khachHang.cccd }}
                                </span>
                                <span v-else class="badge bg-secondary-subtle text-secondary small">
                                  <i class="bi bi-lock-fill me-1"></i> Bảo mật vai trò
                                </span>
                              </td>
                            </tr>

                            <tr>
                              <td class="text-muted small ps-0">Thời gian nhận:</td>
                              <td class="small fw-semibold text-success">
                                {{ formatDateTime(selectedRoom.thongTinLuuTru.thoiGianNhan) }}
                              </td>
                            </tr>
                            <tr>
                              <td class="text-muted small ps-0">Thời gian trả:</td>
                              <td class="small fw-semibold text-danger">
                                {{ formatDateTime(selectedRoom.thongTinLuuTru.thoiGianTra) }}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <!-- Không có khách lưu trú -->
                      <div v-else class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 text-secondary d-block mb-2"></i>
                        <p class="small mb-0">Hiện tại không có lượt lưu trú hay đơn đặt phòng trực tiếp tại phòng này.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Thao tác thay đổi trạng thái nhanh (Cho Quản lý hoặc Buồng phòng) -->
              <div v-if="canManageRoomStatus" class="card border-0 shadow-sm rounded-3 bg-white mt-4">
                <div class="card-body p-3">
                  <h6 class="fw-bold text-navy mb-2">
                    <i class="bi bi-gear-wide-connected text-teal me-1"></i> Cập Nhật Trạng Thái Buồng Phòng Nhanh
                  </h6>
                  <p class="text-muted small mb-3">
                    Chuyển nhanh trạng thái phòng sau khi hoàn thành vệ sinh dọn dẹp hoặc kích hoạt chế độ bảo trì:
                  </p>

                  <div class="d-flex flex-wrap gap-2">
                    <!-- Nút Hoàn thành dọn phòng -->
                    <button
                      v-if="selectedRoom.trangThaiSoDo === 'Cần dọn dẹp' || selectedRoom.trangThai === 'Cần dọn dẹp'"
                      type="button"
                      class="btn btn-success text-white rounded-3 btn-sm px-3"
                      :disabled="isUpdatingStatus"
                      @click="updateRoomStatus(selectedRoom.id, 'Sẵn sàng')"
                    >
                      <i class="bi bi-check2-circle me-1"></i> Hoàn thành dọn phòng (Về Sẵn sàng)
                    </button>

                    <!-- Nút Chuyển sang Cần dọn dẹp -->
                    <button
                      v-if="selectedRoom.trangThaiSoDo === 'Trống'"
                      type="button"
                      class="btn btn-outline-purple rounded-3 btn-sm px-3"
                      :disabled="isUpdatingStatus"
                      @click="updateRoomStatus(selectedRoom.id, 'Cần dọn dẹp')"
                    >
                      <i class="bi bi-stars me-1"></i> Báo cần dọn dẹp
                    </button>

                    <!-- Nút Bật bảo trì (chỉ khi không có khách) -->
                    <button
                      v-if="selectedRoom.trangThaiSoDo === 'Trống' || selectedRoom.trangThaiSoDo === 'Cần dọn dẹp'"
                      type="button"
                      class="btn btn-outline-secondary rounded-3 btn-sm px-3"
                      :disabled="isUpdatingStatus"
                      @click="updateRoomStatus(selectedRoom.id, 'Đang bảo trì')"
                    >
                      <i class="bi bi-wrench-adjustable me-1"></i> Chuyển sang Đang bảo trì
                    </button>

                    <!-- Nút Hoàn thành bảo trì -->
                    <button
                      v-if="selectedRoom.trangThaiSoDo === 'Đang bảo trì' || selectedRoom.trangThai === 'Đang bảo trì'"
                      type="button"
                      class="btn btn-outline-success rounded-3 btn-sm px-3"
                      :disabled="isUpdatingStatus"
                      @click="updateRoomStatus(selectedRoom.id, 'Sẵn sàng')"
                    >
                      <i class="bi bi-check-lg me-1"></i> Hoàn thành bảo trì (Về Sẵn sàng)
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-white px-4 py-3 border-top d-flex justify-content-between">
              <button type="button" class="btn btn-secondary rounded-3" @click="closeRoomDetail">
                <i class="bi bi-x-lg me-1"></i> Đóng
              </button>

              <div class="d-flex gap-2">
                <!-- Nút Đặt phòng nếu phòng trống -->
                <router-link
                  v-if="selectedRoom.trangThaiSoDo === 'Trống'"
                  :to="`/receptionist/booking?maPhong=${selectedRoom.id}&maLoaiPhong=${selectedRoom.maLoaiPhong}`"
                  class="btn btn-teal text-white rounded-3 px-3"
                >
                  <i class="bi bi-calendar-plus me-1"></i> Đặt phòng này
                </router-link>

                <!-- Nút Thủ tục Trả phòng nếu phòng Đang ở -->
                <router-link
                  v-else-if="selectedRoom.trangThaiSoDo === 'Đang ở' && selectedRoom.thongTinLuuTru?.maDatPhong"
                  :to="`/receptionist/check-in-out?maDatPhong=${selectedRoom.thongTinLuuTru.maDatPhong}&action=checkout`"
                  class="btn btn-danger text-white rounded-3 px-3"
                >
                  <i class="bi bi-box-arrow-right me-1"></i> Thủ tục Trả phòng (Check-out)
                </router-link>

                <!-- Nút Thủ tục Nhận phòng nếu phòng Đã đặt -->
                <router-link
                  v-else-if="selectedRoom.trangThaiSoDo === 'Đã đặt' && selectedRoom.thongTinLuuTru?.maDatPhong"
                  :to="`/receptionist/check-in-out?maDatPhong=${selectedRoom.thongTinLuuTru.maDatPhong}&action=checkin`"
                  class="btn btn-warning text-dark rounded-3 px-3"
                >
                  <i class="bi bi-box-arrow-in-left me-1"></i> Thủ tục Nhận phòng (Check-in)
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 9. Toast thông báo -->
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1090">
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
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// ==========================
// 1. STATE & REACTIVE DATA
// ==========================
const isLoading = ref(false)
const isUpdatingStatus = ref(false)
const autoRefresh = ref(true)
let refreshIntervalTimer = null

const accessDenied = ref(false)
const accessDeniedMessage = ref('')
const emptyMessage = ref('')

const rooms = ref([])
const roomTypeList = ref([])
const meta = reactive({
  total: 0,
  soTang: [],
  thongKe: {
    trong: 0,
    daDat: 0,
    dangO: 0,
    canDonDep: 0,
    dangBaoTri: 0,
    ngungKhaiThac: 0,
  },
})

// Bộ lọc
const filters = reactive({
  keyword: '',
  tang: '',
  maLoaiPhong: '',
  trangThai: '',
})

// Thông tin người dùng đăng nhập hiện tại
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

// Modal chi tiết phòng
const isDetailModalOpen = ref(false)
const selectedRoom = ref(null)

// Toast thông báo
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
const isAuthorizedRole = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return ['quản lý', 'admin', 'lễ tân', 'receptionist', 'buồng phòng', 'housekeeping'].includes(role)
})

const canManageRoomStatus = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return ['quản lý', 'admin', 'lễ tân', 'buồng phòng'].includes(role)
})

const roleDescriptionText = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  if (role === 'quản lý' || role === 'admin') {
    return 'Quản lý toàn quyền: Theo dõi, lọc, xem chi tiết và cấu hình phòng'
  } else if (role === 'lễ tân' || role === 'receptionist') {
    return 'Lễ tân: Xem sơ đồ, đặt phòng, hỗ trợ nhận/trả phòng và liên hệ khách'
  } else if (role === 'buồng phòng' || role === 'housekeeping') {
    return 'Buồng phòng: Xem sơ đồ, nhận diện phòng cần dọn dẹp (Bảo mật SĐT/CCCD)'
  }
  return 'Tài khoản chưa được cấp quyền xem sơ đồ phòng'
})

// Danh sách tầng suy diễn từ dữ liệu hoặc meta
const floorList = computed(() => {
  if (meta.soTang && meta.soTang.length > 0) {
    return meta.soTang
  }
  const floors = new Set()
  rooms.value.forEach((r) => {
    if (r.tang) floors.add(Number(r.tang))
  })
  return Array.from(floors).sort((a, b) => a - b)
})

// Thống kê KPI
const kpiSummary = computed(() => {
  return {
    total: meta.total || rooms.value.length,
    trong: meta.thongKe?.trong ?? countByStatus(rooms.value, 'Trống'),
    daDat: meta.thongKe?.daDat ?? countByStatus(rooms.value, 'Đã đặt'),
    dangO: meta.thongKe?.dangO ?? countByStatus(rooms.value, 'Đang ở'),
    canDonDep: meta.thongKe?.canDonDep ?? countByStatus(rooms.value, 'Cần dọn dẹp'),
    dangBaoTri: meta.thongKe?.dangBaoTri ?? countByStatus(rooms.value, 'Đang bảo trì'),
    ngungKhaiThac: meta.thongKe?.ngungKhaiThac ?? countByStatus(rooms.value, 'Ngưng khai thác'),
  }
})

// Nhóm phòng theo tầng
const groupedByFloor = computed(() => {
  const map = {}
  rooms.value.forEach((r) => {
    const fl = r.tang || 1
    if (!map[fl]) {
      map[fl] = []
    }
    map[fl].push(r)
  })

  // Sắp xếp các phòng trong mỗi tầng theo tên phòng
  Object.keys(map).forEach((fl) => {
    map[fl].sort((a, b) => a.tenPhong.localeCompare(b.tenPhong, 'vi', { numeric: true }))
  })

  return map
})

const countByStatus = (roomList, status) => {
  if (!roomList) return 0
  if (status === 'Trống') {
    return roomList.filter((r) => r.trangThaiSoDo === 'Trống' || r.trangThaiSoDo === 'Sẵn sàng').length
  }
  return roomList.filter((r) => r.trangThaiSoDo === status).length
}

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
 * Tải dữ liệu sơ đồ phòng từ API GET /api/v1/rooms/map (UC18)
 */
const fetchRoomMap = async (showLoadingSpinner = true) => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    router.push('/login')
    return
  }

  if (showLoadingSpinner) {
    isLoading.value = true
  }

  try {
    const params = {}
    if (filters.keyword.trim()) params.keyword = filters.keyword.trim()
    if (filters.tang) params.tang = filters.tang
    if (filters.maLoaiPhong) params.maLoaiPhong = filters.maLoaiPhong
    if (filters.trangThai) params.trangThai = filters.trangThai

    const res = await axios.get('/api/v1/rooms/map', {
      headers: getAuthHeaders(),
      params,
    })

    if (res.data?.success) {
      accessDenied.value = false
      rooms.value = res.data.data || []
      emptyMessage.value = res.data.message || ''

      if (res.data.meta) {
        meta.total = res.data.meta.total || rooms.value.length
        meta.soTang = res.data.meta.soTang || []
        if (res.data.meta.thongKe) {
          meta.thongKe = res.data.meta.thongKe
        }
      }
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || 'Không thể nạp dữ liệu sơ đồ phòng.'

    if (status === 401) {
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      router.push('/login')
    } else if (status === 403) {
      accessDenied.value = true
      accessDeniedMessage.value = msg
      rooms.value = []
    } else {
      showToast(msg, 'danger')
    }
  } finally {
    if (showLoadingSpinner) {
      isLoading.value = false
    }
  }
}

/**
 * Lấy danh mục loại phòng phục vụ bộ lọc
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
    // Không làm gián đoạn sơ đồ phòng nếu lấy danh mục loại phòng gặp lỗi
  }
}

/**
 * Tìm kiếm và áp dụng bộ lọc
 */
const handleSearch = () => {
  fetchRoomMap(true)
}

/**
 * Lọc nhanh trạng thái bằng cách nhấp thẻ KPI
 */
const quickFilterStatus = (status) => {
  if (filters.trangThai === status) {
    filters.trangThai = ''
  } else {
    filters.trangThai = status
  }
  fetchRoomMap(true)
}

/**
 * Xóa toàn bộ bộ lọc
 */
const resetFilters = () => {
  filters.keyword = ''
  filters.tang = ''
  filters.maLoaiPhong = ''
  filters.trangThai = ''
  fetchRoomMap(true)
}

/**
 * Mở modal chi tiết phòng
 */
const openRoomDetail = async (room) => {
  selectedRoom.value = room
  isDetailModalOpen.value = true

  // Gọi API GET /api/v1/rooms/{id} để lấy dữ liệu mới nhất (bao gồm cả phân quyền bảo mật)
  try {
    const res = await axios.get(`/api/v1/rooms/${room.id}`, {
      headers: getAuthHeaders(),
    })
    if (res.data?.success && res.data.data) {
      selectedRoom.value = res.data.data
    }
  } catch {
    // Giữ nguyên object room hiện tại nếu fetch chi tiết gặp lỗi
  }
}

const closeRoomDetail = () => {
  isDetailModalOpen.value = false
  selectedRoom.value = null
}

/**
 * Cập nhật trạng thái phòng nhanh (Hoàn thành dọn phòng, Bảo trì)
 */
const updateRoomStatus = async (roomId, targetStatus) => {
  isUpdatingStatus.value = true
  try {
    const res = await axios.put(
      `/api/v1/rooms/${roomId}/status`,
      { trangThai: targetStatus },
      { headers: getAuthHeaders() }
    )

    if (res.data?.success) {
      showToast(`Đã cập nhật trạng thái phòng sang "${targetStatus}" thành công!`, 'success')
      closeRoomDetail()
      fetchRoomMap(false)
    } else {
      showToast(res.data?.message || 'Cập nhật thất bại.', 'danger')
    }
  } catch (err) {
    const msg = err.response?.data?.message || 'Lỗi khi cập nhật trạng thái phòng.'
    showToast(msg, 'danger')
  } finally {
    isUpdatingStatus.value = false
  }
}

// ==========================
// 4. QUICK TEST SWITCH USER
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
      fetchRoomMap(true)
    } else {
      showToast(res.data?.message || 'Đăng nhập chuyển đổi thất bại.', 'danger')
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Không thể chuyển đổi tài khoản.', 'danger')
  } finally {
    isLoading.value = false
  }
}

/**
 * Giả lập vai trò Buồng phòng để kiểm thử tính năng ẩn SĐT và CCCD
 */
const quickSetHousekeeperRole = () => {
  currentUser.vaiTro = 'Buồng phòng'
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  user.vaiTro = 'Buồng phòng'
  localStorage.setItem('user', JSON.stringify(user))

  showToast('Đã chuyển vai trò sang Buồng phòng (Chế độ bảo mật thông tin khách hàng).', 'success')
  fetchRoomMap(true)
}

// ==========================
// 5. HELPER FORMATTERS
// ==========================
const formatCurrency = (val) => {
  if (!val || isNaN(val)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
}

const formatDateTime = (val) => {
  if (!val) return '---'
  try {
    const d = new Date(val)
    if (isNaN(d.getTime())) return val
    return d.toLocaleString('vi-VN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  } catch {
    return val
  }
}

const getRoomCardBorderClass = (status) => {
  switch (status) {
    case 'Trống':
    case 'Sẵn sàng':
      return 'room-card-vacant'
    case 'Đang ở':
      return 'room-card-occupied'
    case 'Đã đặt':
      return 'room-card-booked'
    case 'Cần dọn dẹp':
      return 'room-card-cleaning'
    case 'Đang bảo trì':
      return 'room-card-maintenance'
    case 'Ngưng khai thác':
      return 'room-card-retired'
    default:
      return 'room-card-default'
  }
}

const getStatusAlertClass = (status) => {
  switch (status) {
    case 'Trống':
    case 'Sẵn sàng':
      return 'alert-success'
    case 'Đang ở':
      return 'alert-primary'
    case 'Đã đặt':
      return 'alert-warning'
    case 'Cần dọn dẹp':
      return 'alert-purple'
    case 'Đang bảo trì':
      return 'alert-secondary'
    case 'Ngưng khai thác':
      return 'alert-dark'
    default:
      return 'alert-info'
  }
}

// ==========================
// 6. LIFECYCLE HOOKS & TIMERS
// ==========================
const setupAutoRefresh = () => {
  if (refreshIntervalTimer) {
    clearInterval(refreshIntervalTimer)
    refreshIntervalTimer = null
  }
  if (autoRefresh.value) {
    refreshIntervalTimer = setInterval(() => {
      if (!isDetailModalOpen.value && !accessDenied.value) {
        fetchRoomMap(false)
      }
    }, 30000) // 30 giây tự động nạp lại dữ liệu
  }
}

watch(autoRefresh, () => {
  setupAutoRefresh()
})

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
  fetchRoomMap(true)
  setupAutoRefresh()
})

onUnmounted(() => {
  if (refreshIntervalTimer) {
    clearInterval(refreshIntervalTimer)
  }
})
</script>

<style scoped>
/* ==========================
   DESIGN TOKENS & BASE
   ========================== */
.room-map-view {
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

.btn-outline-teal {
  color: #0f766e;
  border-color: #0f766e;
}

.btn-outline-teal:hover {
  background-color: #0f766e;
  color: #ffffff;
}

/* Purple cho Cần dọn dẹp */
.text-purple {
  color: #8b5cf6 !important;
}

.bg-purple {
  background-color: #8b5cf6 !important;
}

.btn-outline-purple {
  color: #8b5cf6;
  border-color: #8b5cf6;
}

.btn-outline-purple:hover {
  background-color: #8b5cf6;
  color: #ffffff;
}

.alert-purple {
  background-color: #ede9fe;
  color: #6d28d9;
}

/* Warning & Colors */
.text-warning-custom {
  color: #d97706 !important;
}

.bg-warning-custom {
  background-color: #d97706 !important;
}

.text-warning-dark {
  color: #b45309 !important;
}

.cursor-pointer {
  cursor: pointer;
}

/* Header Box */
.header-icon-box {
  width: 52px;
  height: 52px;
}

.modal-icon-box {
  width: 44px;
  height: 44px;
}

/* KPI Cards */
.kpi-card {
  transition: all 0.2s ease-in-out;
  border: 2px solid transparent !important;
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.kpi-card-active {
  border-color: #0f766e !important;
  background-color: #f0fdf4 !important;
}

/* Switch & Controls */
.custom-switch:checked {
  background-color: #0f766e;
  border-color: #0f766e;
}

/* Chú thích màu */
.color-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

/* Room Card */
.room-card {
  background-color: #ffffff;
  border: 2px solid #e2e8f0;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  min-height: 140px;
}

.room-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1.25rem rgba(30, 58, 95, 0.15) !important;
}

.room-title {
  font-size: 1.05rem;
}

/* Border styles per room status */
.room-card-vacant {
  border-top: 4px solid #10b981;
}

.room-card-occupied {
  border-top: 4px solid #1e3a5f;
  background-color: #f8fafc;
}

.room-card-booked {
  border-top: 4px solid #d97706;
}

.room-card-cleaning {
  border-top: 4px solid #8b5cf6;
}

.room-card-maintenance {
  border-top: 4px solid #64748b;
  background-color: #f1f5f9;
}

.room-card-retired {
  border-top: 4px solid #1e293b;
  opacity: 0.8;
}

.guest-preview-badge {
  font-size: 0.72rem;
  max-width: 100%;
}

/* Empty State & Denied */
.empty-icon-box,
.avatar-denied {
  width: 72px;
  height: 72px;
}

/* Floor Badge */
.floor-badge {
  letter-spacing: 0.5px;
  font-size: 0.85rem;
}

/* Modal Custom */
.custom-modal-backdrop {
  background-color: rgba(15, 23, 42, 0.65);
}

/* Animations */
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
