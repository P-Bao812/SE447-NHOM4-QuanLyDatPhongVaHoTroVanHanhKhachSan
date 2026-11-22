<template>
  <div class="nhan-vien-page py-4">
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

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: QUAY LẠI, BREADCRUMB & BỘ GIẢ LẬP QUYỀN TEST BACKEND -->
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
              <li class="breadcrumb-item text-muted">Vận hành & Quản trị</li>
              <li class="breadcrumb-item active text-teal fw-bold" aria-current="page">Quản lý nhân viên</li>
            </ol>
          </nav>
        </div>

        <!-- Bên phải: Thời gian thực tế, Chuông thông báo & Dropdown đổi vai trò test Backend -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1 text-teal"></i>
            {{ ngayHienTai }}
          </span>

          <button class="btn btn-light btn-sm rounded-circle border p-2" title="Thông báo nội bộ">
            <i class="bi bi-bell text-secondary"></i>
          </button>

          <!-- Dropdown Giả lập quyền để test Backend Authorization -->
          <div class="dropdown">
            <button
              class="btn btn-light btn-sm border d-flex align-items-center gap-2 py-1 px-2.5 rounded-pill"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              title="Đổi quyền kiểm thử Backend"
            >
              <div
                class="rounded-circle fw-bold d-flex align-items-center justify-content-center text-white"
                style="width: 28px; height: 28px; background-color: #1E5E49; font-size: 0.75rem;"
              >
                {{ currentUser.avatarText }}
              </div>
              <div class="text-start d-none d-sm-block me-1" style="line-height: 1.2;">
                <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ currentUser.hoTen }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">{{ currentUserRole }}</div>
              </div>
              <i class="bi bi-chevron-down text-muted small"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-1" style="font-size: 0.85rem;">
              <li class="dropdown-header text-uppercase text-secondary" style="font-size: 0.7rem;">
                Kiểm thử phân quyền Backend:
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Admin' }"
                  @click="doiVaiTroKiemThu('Admin')"
                >
                  <span>Admin (Toàn quyền quản trị)</span>
                  <i v-if="currentUserRole === 'Admin'" class="bi bi-check2"></i>
                </button>
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Quản lý' }"
                  @click="doiVaiTroKiemThu('Quản lý')"
                >
                  <span>Quản lý (Quyền thêm/sửa)</span>
                  <i v-if="currentUserRole === 'Quản lý'" class="bi bi-check2"></i>
                </button>
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Lễ tân' }"
                  @click="doiVaiTroKiemThu('Lễ tân')"
                >
                  <span>Lễ tân (Chỉ xem, bị chặn 403)</span>
                  <i v-if="currentUserRole === 'Lễ tân'" class="bi bi-check2"></i>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ, MÔ TẢ & NÚT THÊM NHÂN VIÊN -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="fw-bold text-main-dark mb-1 d-flex align-items-center gap-2">
            <span class="title-bar-accent"></span>
            Quản lý nhân viên
          </h2>
          <p class="text-muted mb-0 small">
            Theo dõi hồ sơ nhân sự, phân bổ ca trực và cập nhật trạng thái làm việc tại các bộ phận khách sạn.
          </p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <button
            type="button"
            class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm d-inline-flex align-items-center gap-1.5"
            @click="refreshData"
            :disabled="loadingList || loadingStats"
            title="Làm mới dữ liệu từ Database"
          >
            <i class="bi bi-arrow-clockwise" :class="{ 'spin-infinite': loadingList }"></i>
            <span class="d-none d-sm-inline">Làm mới</span>
          </button>

          <button
            type="button"
            class="btn btn-teal-primary rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-xs"
            @click="moModalThem"
          >
            <i class="bi bi-person-plus-fill"></i>
            <span class="fw-medium">Thêm nhân viên</span>
          </button>
        </div>
      </div>

      <!-- 3 THẺ THỐNG KÊ THEO YÊU CẦU: TỔNG NHÂN VIÊN - ĐANG TRONG CA - CA LÀM HIỆN TẠI -->
      <div class="row g-3 mb-4">
        <!-- THẺ 1: TỔNG NHÂN VIÊN -->
        <div class="col-12 col-md-4">
          <div class="stat-card p-3 p-xl-3.5 bg-white rounded-4 border shadow-xs h-100 d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                Tổng nhân viên
              </span>
              <div class="fs-2 fw-bold text-main-dark mt-1 lh-1">
                {{ stats.tongNhanVien || 0 }}
              </div>
              <div class="small text-muted mt-2 d-flex align-items-center gap-1">
                <i class="bi bi-check-all text-teal"></i>
                <span>Toàn bộ hồ sơ nhân sự</span>
              </div>
            </div>
            <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center bg-teal-subtle text-teal">
              <i class="bi bi-people-fill fs-4"></i>
            </div>
          </div>
        </div>

        <!-- THẺ 2: NHÂN VIÊN ĐANG TRONG CA -->
        <div class="col-12 col-md-4">
          <div class="stat-card p-3 p-xl-3.5 bg-white rounded-4 border shadow-xs h-100 d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                Nhân viên đang trong ca
              </span>
              <div class="fs-2 fw-bold text-success mt-1 lh-1">
                {{ stats.dangTrongCa || 0 }}
              </div>
              <div class="small text-muted mt-2 d-flex align-items-center gap-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-0.5 px-2">
                  <i class="bi bi-activity me-1"></i>{{ stats.tiLeTrongCa || 0 }}% tổng số
                </span>
                <span class="d-none d-lg-inline">đang trực</span>
              </div>
            </div>
            <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success">
              <i class="bi bi-person-check-fill fs-4"></i>
            </div>
          </div>
        </div>

        <!-- THẺ 3: CA LÀM VIỆC HIỆN TẠI (THEO THỜI GIAN THỰC) -->
        <div class="col-12 col-md-4">
          <div class="stat-card p-3 p-xl-3.5 bg-white rounded-4 border shadow-xs h-100 d-flex align-items-center justify-content-between">
            <div>
              <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                Ca làm việc hiện tại
              </span>
              <div class="fs-4 fw-bold text-teal mt-1 lh-1 text-truncate" style="max-width: 220px;">
                {{ stats.caHienTai?.tenCa || 'Ca sáng' }}
              </div>
              <div class="small text-muted mt-2 d-flex align-items-center gap-1.5 flex-wrap">
                <span class="badge bg-light text-dark border rounded-pill py-0.5 px-2">
                  <i class="bi bi-clock me-1 text-teal"></i>{{ stats.caHienTai?.khungGio || '06:00 – 14:00' }}
                </span>
                <span class="text-muted small">
                  ({{ stats.caHienTai?.soLuongNhanVien || 0 }} nhân sự)
                </span>
              </div>
            </div>
            <div class="stat-icon-wrapper rounded-circle d-flex align-items-center justify-content-center bg-teal-subtle text-teal">
              <i class="bi bi-clock-history fs-4"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- BỐ CỤC 2 CỘT CHÍNH -->
      <div class="row g-4">
        <!-- ==================== CỘT TRÁI: BỘ LỌC, TÌM KIẾM & BẢNG DANH SÁCH ==================== -->
        <div class="col-12 col-xl-7 col-xxl-8">
          <div class="card border-0 rounded-4 shadow-xs overflow-hidden">
            <!-- THANH TÌM KIẾM & BỘ LỌC TRÊN ĐẦU BẢNG -->
            <div class="card-header bg-white border-bottom p-3">
              <div class="row g-2 align-items-center">
                <!-- Ô TÌM KIẾM -->
                <div class="col-12 col-md-5">
                  <div class="position-relative">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                    <input
                      v-model="filters.q"
                      type="text"
                      class="form-control form-control-sm ps-5 rounded-pill border"
                      placeholder="Tìm theo tên, mã NV, email, SĐT..."
                      @input="handleSearchInput"
                    />
                    <button
                      v-if="filters.q"
                      class="btn btn-sm btn-clear-search position-absolute top-50 end-0 translate-middle-y me-2"
                      @click="filters.q = ''; fetchStaffList()"
                    >
                      <i class="bi bi-x-circle-fill text-muted"></i>
                    </button>
                  </div>
                </div>

                <!-- BỘ LỌC BỘ PHẬN -->
                <div class="col-6 col-md-3">
                  <select
                    v-model="filters.boPhan"
                    class="form-select form-select-sm rounded-pill border"
                    @change="fetchStaffList"
                  >
                    <option value="Tất cả">Tất cả bộ phận</option>
                    <option value="Lễ tân">Lễ tân</option>
                    <option value="Buồng phòng">Buồng phòng</option>
                    <option value="Ban Quản lý">Ban Quản lý</option>
                    <option value="Kế toán">Kế toán</option>
                    <option value="Kỹ thuật - Bảo trì">Kỹ thuật - Bảo trì</option>
                    <option value="Nhà hàng & Bếp">Nhà hàng & Bếp</option>
                    <option value="An ninh">An ninh</option>
                  </select>
                </div>

                <!-- BỘ LỌC CA LÀM -->
                <div class="col-6 col-md-2">
                  <select
                    v-model="filters.caLam"
                    class="form-select form-select-sm rounded-pill border"
                    @change="fetchStaffList"
                  >
                    <option value="Tất cả">Tất cả ca</option>
                    <option value="Ca sáng">Ca sáng</option>
                    <option value="Ca chiều">Ca chiều</option>
                    <option value="Ca đêm">Ca đêm</option>
                    <option value="Hành chính">Hành chính</option>
                  </select>
                </div>

                <!-- BỘ LỌC TRẠNG THÁI -->
                <div class="col-12 col-md-2">
                  <select
                    v-model="filters.trangThai"
                    class="form-select form-select-sm rounded-pill border"
                    @change="fetchStaffList"
                  >
                    <option value="Tất cả">Trạng thái</option>
                    <option value="Đang trong ca">Đang trong ca</option>
                    <option value="Nghỉ ca">Nghỉ ca</option>
                    <option value="Tạm nghỉ">Tạm nghỉ</option>
                    <option value="Đã nghỉ việc">Đã nghỉ việc</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- BẢNG DANH SÁCH NHÂN VIÊN DẠNG BẢNG -->
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 custom-staff-table">
                <thead class="bg-light-soft text-muted small text-uppercase">
                  <tr>
                    <th scope="col" class="ps-3 py-3" style="width: 100px;">Mã NV</th>
                    <th scope="col" class="py-3">Họ và tên</th>
                    <th scope="col" class="py-3 d-none d-md-table-cell">Email</th>
                    <th scope="col" class="py-3">Bộ phận</th>
                    <th scope="col" class="py-3 d-none d-lg-table-cell">Ca làm</th>
                    <th scope="col" class="pe-3 py-3 text-center" style="width: 140px;">Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- SPINNER KHI ĐANG TẢI -->
                  <tr v-if="loadingList">
                    <td colspan="6" class="text-center py-5">
                      <div class="spinner-border text-teal spinner-border-sm" role="status">
                        <span class="visually-hidden">Đang tải danh sách...</span>
                      </div>
                      <p class="text-muted small mt-2 mb-0">Đang tải dữ liệu nhân viên từ database...</p>
                    </td>
                  </tr>

                  <!-- EMPTY STATE KHI KHÔNG CÓ DỮ LIỆU -->
                  <tr v-else-if="staffList.length === 0">
                    <td colspan="6" class="text-center py-5">
                      <div class="empty-state-box text-muted">
                        <i class="bi bi-people display-6 text-muted mb-2"></i>
                        <p class="mb-1 fw-medium">Không tìm thấy nhân viên nào phù hợp.</p>
                        <p class="small text-muted mb-0">Thử thay đổi từ khóa tìm kiếm hoặc các tiêu chí bộ lọc.</p>
                      </div>
                    </td>
                  </tr>

                  <!-- DANH SÁCH HÀNG NHÂN VIÊN -->
                  <tr
                    v-for="item in staffList"
                    :key="item.id"
                    class="staff-row cursor-pointer transition-all"
                    :class="{ 'row-active': selectedStaff && selectedStaff.id === item.id }"
                    @click="chonNhanVien(item)"
                  >
                    <!-- MÃ NHÂN VIÊN -->
                    <td class="ps-3 fw-bold text-teal" style="font-size: 0.85rem;">
                      {{ item.maNhanVien }}
                    </td>

                    <!-- HỌ VÀ TÊN + AVATAR INITIAL -->
                    <td>
                      <div class="d-flex align-items-center gap-2.5">
                        <div
                          class="staff-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                          :class="getAvatarColorClass(item.boPhan)"
                        >
                          {{ item.avatarText }}
                        </div>
                        <div class="overflow-hidden">
                          <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">
                            {{ item.hoTen }}
                          </div>
                          <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                            {{ item.chucVu }}
                          </div>
                        </div>
                      </div>
                    </td>

                    <!-- EMAIL -->
                    <td class="d-none d-md-table-cell text-muted small text-truncate" style="max-width: 160px;">
                      {{ item.email }}
                    </td>

                    <!-- BỘ PHẬN -->
                    <td>
                      <span class="badge-department" :class="getDepartmentBadgeClass(item.boPhan)">
                        {{ item.boPhan }}
                      </span>
                    </td>

                    <!-- CA LÀM -->
                    <td class="d-none d-lg-table-cell text-muted small">
                      <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-clock text-teal" style="font-size: 0.75rem;"></i>
                        <span>{{ rutGonCaLam(item.caLam) }}</span>
                      </div>
                    </td>

                    <!-- TRẠNG THÁI -->
                    <td class="pe-3 text-center">
                      <span class="badge-status-pill" :class="getStatusBadgeClass(item.trangThai)">
                        <span class="status-dot"></span>
                        <span>{{ item.trangThai }}</span>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- FOOTER BẢNG: ĐẾM TỔNG SỐ BẢN GHI -->
            <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between small text-muted">
              <span>Hiển thị <strong>{{ staffList.length }}</strong> nhân viên</span>
              <span v-if="selectedStaff" class="text-teal">
                <i class="bi bi-cursor-fill me-1"></i>Đang xem: <strong>{{ selectedStaff.hoTen }}</strong>
              </span>
            </div>
          </div>
        </div>

        <!-- ==================== CỘT PHẢI: HIỂN THỊ THÔNG TIN CHI TIẾT NHÂN VIÊN ĐƯỢC CHỌN ==================== -->
        <div class="col-12 col-xl-5 col-xxl-4">
          <div class="detail-sidebar-card card border-0 rounded-4 shadow-xs position-sticky" style="top: 1.5rem;">
            <!-- HEADER CHI TIẾT -->
            <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between">
              <h5 class="fw-bold mb-0 text-main-dark d-flex align-items-center gap-2">
                <i class="bi bi-person-lines-fill text-teal"></i>
                Hồ sơ chi tiết
              </h5>

              <!-- NÚT CHỈNH SỬA & DROPDOWN CẬP NHẬT TRẠNG THÁI -->
              <div v-if="selectedStaff" class="d-flex align-items-center gap-2">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-teal rounded-pill px-2.5 d-inline-flex align-items-center gap-1"
                  @click="moModalSua(selectedStaff)"
                  title="Chỉnh sửa thông tin"
                >
                  <i class="bi bi-pencil-square"></i>
                  <span>Sửa</span>
                </button>

                <!-- DROPDOWN ĐỔI TRẠNG THÁI NHANH -->
                <div class="dropdown">
                  <button
                    class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 d-inline-flex align-items-center gap-1"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Cập nhật nhanh trạng thái"
                  >
                    <i class="bi bi-arrow-repeat"></i>
                    <span>Trạng thái</span>
                    <i class="bi bi-chevron-down small"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 0.85rem;">
                    <li class="dropdown-header text-uppercase text-secondary" style="font-size: 0.7rem;">
                      Chuyển trạng thái làm việc:
                    </li>
                    <li>
                      <button class="dropdown-item" @click="doiTrangThaiNhanh('Đang trong ca')">
                        <span class="status-dot dot-active me-2"></span>Đang trong ca
                      </button>
                    </li>
                    <li>
                      <button class="dropdown-item" @click="doiTrangThaiNhanh('Nghỉ ca')">
                        <span class="status-dot dot-shift-off me-2"></span>Nghỉ ca
                      </button>
                    </li>
                    <li>
                      <button class="dropdown-item" @click="doiTrangThaiNhanh('Tạm nghỉ')">
                        <span class="status-dot dot-leave me-2"></span>Tạm nghỉ
                      </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <button class="dropdown-item text-danger" @click="doiTrangThaiNhanh('Đã nghỉ việc')">
                        <span class="status-dot dot-resigned me-2"></span>Đã nghỉ việc
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- NỘI DUNG CHI TIẾT -->
            <div class="card-body p-4">
              <!-- TRƯỜNG HỢP CÓ NHÂN VIÊN ĐƯỢC CHỌN -->
              <div v-if="selectedStaff" class="staff-detail-content animate-fade-in">
                <!-- AVATAR, HỌ TÊN, MÃ NV & BADGE TRẠNG THÁI -->
                <div class="text-center pb-3 mb-3 border-bottom">
                  <div
                    class="large-avatar rounded-circle mx-auto d-flex align-items-center justify-content-center text-white fw-bold mb-2 shadow-xs"
                    :class="getAvatarColorClass(selectedStaff.boPhan)"
                  >
                    {{ selectedStaff.avatarText }}
                  </div>
                  <h5 class="fw-bold text-dark mb-0.5">{{ selectedStaff.hoTen }}</h5>
                  <div class="text-muted small mb-2">{{ selectedStaff.chucVu }}</div>

                  <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                    <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.8rem;">
                      <i class="bi bi-upc-scan me-1 text-teal"></i>{{ selectedStaff.maNhanVien }}
                    </span>
                    <span class="badge-status-pill" :class="getStatusBadgeClass(selectedStaff.trangThai)">
                      <span class="status-dot"></span>
                      <span>{{ selectedStaff.trangThai }}</span>
                    </span>
                  </div>
                </div>

                <!-- LƯỚI THÔNG TIN CHI TIẾT THEO YÊU CẦU:
                     Bộ phận, Chức danh, Ngày gia nhập, SĐT, Email, Ca làm, Người quản lý, Ghi chú -->
                <div class="info-list d-flex flex-column gap-2.5">
                  <!-- 1. BỘ PHẬN -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-building text-teal"></i>
                      <span>Bộ phận:</span>
                    </span>
                    <strong class="text-dark small">{{ selectedStaff.boPhan }}</strong>
                  </div>

                  <!-- 2. CHỨC DANH / VỊ TRÍ -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-award text-teal"></i>
                      <span>Chức danh:</span>
                    </span>
                    <strong class="text-dark small">{{ selectedStaff.chucVu }}</strong>
                  </div>

                  <!-- 3. CA LÀM VIỆC -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-clock text-teal"></i>
                      <span>Ca làm việc:</span>
                    </span>
                    <strong class="text-teal small text-end">{{ selectedStaff.caLam }}</strong>
                  </div>

                  <!-- 4. NGÀY GIA NHẬP -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-calendar-check text-teal"></i>
                      <span>Ngày gia nhập:</span>
                    </span>
                    <strong class="text-dark small">{{ formatNgay(selectedStaff.ngayGiaNhap) }}</strong>
                  </div>

                  <!-- 5. SỐ ĐIỆN THOẠI -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-telephone text-teal"></i>
                      <span>Số điện thoại:</span>
                    </span>
                    <a :href="'tel:' + selectedStaff.soDienThoai" class="text-decoration-none text-teal fw-bold small">
                      {{ selectedStaff.soDienThoai }}
                    </a>
                  </div>

                  <!-- 6. EMAIL LIÊN HỆ -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-envelope text-teal"></i>
                      <span>Email:</span>
                    </span>
                    <a :href="'mailto:' + selectedStaff.email" class="text-decoration-none text-dark small text-truncate" style="max-width: 190px;" :title="selectedStaff.email">
                      {{ selectedStaff.email }}
                    </a>
                  </div>

                  <!-- 7. NGƯỜI QUẢN LÝ -->
                  <div class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-person-badge text-teal"></i>
                      <span>Người quản lý:</span>
                    </span>
                    <strong class="text-dark small">{{ selectedStaff.nguoiQuanLy || 'Ban Giám đốc' }}</strong>
                  </div>

                  <!-- 8. CCCD / CMND NẾU CÓ -->
                  <div v-if="selectedStaff.cccd" class="info-item d-flex align-items-center justify-content-between p-2 rounded-3 bg-light-soft">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-card-heading text-teal"></i>
                      <span>Số CCCD:</span>
                    </span>
                    <strong class="text-dark small">{{ selectedStaff.cccd }}</strong>
                  </div>

                  <!-- 9. GHI CHÚ / KỸ NĂNG -->
                  <div class="p-2.5 rounded-3 bg-light-soft mt-1">
                    <div class="text-muted small d-flex align-items-center gap-2 mb-1">
                      <i class="bi bi-chat-left-text text-teal"></i>
                      <span>Ghi chú hồ sơ:</span>
                    </div>
                    <div class="small text-dark fst-italic">
                      {{ selectedStaff.ghiChu || 'Chưa có ghi chú đặc biệt cho nhân sự này.' }}
                    </div>
                  </div>
                </div>

                <!-- CHÂN TRANG CHI TIẾT: THỜI GIAN ĐỒNG BỘ -->
                <div class="mt-3 pt-3 border-top text-center text-muted" style="font-size: 0.72rem;">
                  <i class="bi bi-shield-check text-teal me-1"></i>
                  <span>Dữ liệu xác thực và liên kết cơ sở dữ liệu khách sạn.</span>
                </div>
              </div>

              <!-- CHƯA CHỌN NHÂN VIÊN NÀO -->
              <div v-else class="text-center py-5 text-muted">
                <i class="bi bi-person-bounding-box display-5 text-muted opacity-50 mb-3"></i>
                <h6 class="fw-bold text-dark">Chưa chọn nhân viên</h6>
                <p class="small text-muted mb-0">
                  Nhấp vào một nhân viên trong bảng bên trái để xem đầy đủ hồ sơ chi tiết.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL THÊM / SỬA NHÂN VIÊN -->
      <modal-them-sua-nhan-vien
        :show="showModal"
        :is-edit="isEditMode"
        :staff-data="editingStaff"
        :user-role="currentUserRole"
        @close="showModal = false"
        @saved="handleStaffSaved"
      ></modal-them-sua-nhan-vien>
    </div>
  </div>
</template>

<script>
import nhanVienService from './nhanVienService.js';
import ModalThemSuaNhanVien from './ModalThemSuaNhanVien.vue';

export default {
  name: 'NhanVien',
  components: {
    ModalThemSuaNhanVien
  },
  data() {
    return {
      // Dữ liệu chính
      staffList: [],
      selectedStaff: null,
      stats: {
        tongNhanVien: 0,
        dangTrongCa: 0,
        caHienTai: {
          tenCa: 'Ca sáng',
          khungGio: '06:00 – 14:00',
          full: 'Ca sáng (06:00 - 14:00)',
          soLuongNhanVien: 0
        },
        tiLeTrongCa: 0
      },

      // Trạng thái tải
      loadingList: false,
      loadingStats: false,

      // Bộ lọc tìm kiếm
      filters: {
        q: '',
        boPhan: 'Tất cả',
        caLam: 'Tất cả',
        trangThai: 'Tất cả'
      },
      searchDebounceTimer: null,

      // Modal thêm/sửa
      showModal: false,
      isEditMode: false,
      editingStaff: null,

      // Phân quyền giả lập để kiểm thử Backend
      currentUserRole: 'Admin',
      currentUser: {
        hoTen: 'Trần Văn Quản Trị',
        avatarText: 'AD'
      },

      // Toast thông báo
      toast: {
        show: false,
        message: '',
        type: 'success'
      }
    };
  },
  computed: {
    ngayHienTai() {
      const d = new Date();
      const thu = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'][d.getDay()];
      const ngay = String(d.getDate()).padStart(2, '0');
      const thang = String(d.getMonth() + 1).padStart(2, '0');
      const nam = d.getFullYear();
      return `${thu}, ${ngay}/${thang}/${nam}`;
    }
  },
  mounted() {
    this.fetchStats();
    this.fetchStaffList();
  },
  methods: {
    /**
     * 1. LẤY DỮ LIỆU THỐNG KÊ (3 THẺ THỐNG KÊ TỪ DATABASE QUA LARAVEL)
     */
    async fetchStats() {
      this.loadingStats = true;
      try {
        const res = await nhanVienService.getThongKe();
        if (res.status === 'success') {
          this.stats = res.data;
        }
      } catch (err) {
        console.error('Lỗi tải thống kê nhân viên:', err);
      } finally {
        this.loadingStats = false;
      }
    },

    /**
     * 2. LẤY DANH SÁCH NHÂN VIÊN TỪ LARAVEL API
     */
    async fetchStaffList() {
      this.loadingList = true;
      try {
        const res = await nhanVienService.getDanhSachNhanVien(this.filters);
        if (res.status === 'success') {
          this.staffList = res.data || [];

          // Tự động chọn nhân viên đầu tiên nếu danh sách có và chưa chọn ai
          if (this.staffList.length > 0) {
            if (!this.selectedStaff) {
              this.selectedStaff = this.staffList[0];
            } else {
              // Cập nhật lại bản ghi của nhân viên đang chọn nếu nó vẫn tồn tại trong danh sách
              const found = this.staffList.find(s => s.id === this.selectedStaff.id);
              this.selectedStaff = found || this.staffList[0];
            }
          } else {
            this.selectedStaff = null;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách nhân viên:', err);
        this.showToast(err.message || 'Không thể tải danh sách nhân viên.', 'danger');
      } finally {
        this.loadingList = false;
      }
    },

    /**
     * TÌM KIẾM CÓ DEBOUNCE
     */
    handleSearchInput() {
      clearTimeout(this.searchDebounceTimer);
      this.searchDebounceTimer = setTimeout(() => {
        this.fetchStaffList();
      }, 300);
    },

    /**
     * CHỌN MỘT NHÂN VIÊN TRONG BẢNG
     */
    chonNhanVien(staff) {
      this.selectedStaff = staff;
    },

    /**
     * LÀM MỚI DỮ LIỆU
     */
    refreshData() {
      this.fetchStats();
      this.fetchStaffList();
      this.showToast('Đã làm mới dữ liệu từ cơ sở dữ liệu.', 'success');
    },

    /**
     * MỞ MODAL THÊM MỚI NHÂN VIÊN
     */
    moModalThem() {
      this.isEditMode = false;
      this.editingStaff = null;
      this.showModal = true;
    },

    /**
     * MỞ MODAL CHỈNH SỬA
     */
    moModalSua(staff) {
      this.isEditMode = true;
      this.editingStaff = { ...staff };
      this.showModal = true;
    },

    /**
     * XỬ LÝ KHI LƯU THÀNH CÔNG TỪ MODAL
     */
    async handleStaffSaved(result) {
      this.showToast(result.message, 'success');
      await this.fetchStats();
      await this.fetchStaffList();

      if (result.data && result.data.id) {
        const found = this.staffList.find(s => s.id === result.data.id);
        if (found) {
          this.selectedStaff = found;
        }
      }
    },

    /**
     * ĐỔI NHANH TRẠNG THÁI LÀM VIỆC CỦA NHÂN VIÊN ĐANG CHỌN
     */
    async doiTrangThaiNhanh(trangThaiMoi) {
      if (!this.selectedStaff) return;
      if (this.selectedStaff.trangThai === trangThaiMoi) return;

      try {
        const res = await nhanVienService.capNhatTrangThai(
          this.selectedStaff.id,
          trangThaiMoi,
          this.currentUserRole
        );

        if (res.status === 'success') {
          this.showToast(res.message, 'success');
          // Cập nhật local
          this.selectedStaff.trangThai = trangThaiMoi;
          const found = this.staffList.find(s => s.id === this.selectedStaff.id);
          if (found) {
            found.trangThai = trangThaiMoi;
          }
          // Cập nhật lại thống kê số lượng trong ca
          this.fetchStats();
        }
      } catch (err) {
        console.error('Lỗi cập nhật trạng thái:', err);
        this.showToast(err.message || 'Không thể đổi trạng thái.', 'danger');
      }
    },

    /**
     * GIẢ LẬP ĐỔI VAI TRÒ TEST BACKEND AUTHORIZATION
     */
    doiVaiTroKiemThu(role) {
      this.currentUserRole = role;
      if (role === 'Admin') {
        this.currentUser = { hoTen: 'Trần Văn Quản Trị', avatarText: 'AD' };
      } else if (role === 'Quản lý') {
        this.currentUser = { hoTen: 'Nguyễn Thị Quản Lý', avatarText: 'QL' };
      } else {
        this.currentUser = { hoTen: 'Lê Văn Lễ Tân', avatarText: 'LT' };
      }
      this.showToast(`Đã chuyển vai trò sang: [${role}]. Hãy thử thêm/sửa để kiểm tra Backend.`, 'success');
    },

    /**
     * QUAY LẠI TRANG TRƯỚC
     */
    quayLai() {
      if (window.history.length > 1) {
        this.$router.back();
      } else {
        this.$router.push('/loai-phong');
      }
    },

    /**
     * HIỂN THỊ TOAST THÔNG BÁO
     */
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 4000);
    },

    /**
     * FORMAT NGÀY THÁNG
     */
    formatNgay(dateStr) {
      if (!dateStr) return '01/01/2023';
      try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
      } catch {
        return dateStr;
      }
    },

    /**
     * RÚT GỌN TÊN CA ĐỂ HIỂN THỊ BẢNG GỌN GÀNG
     */
    rutGonCaLam(caLam) {
      if (!caLam) return 'Ca sáng';
      if (caLam.includes('Ca sáng')) return 'Ca sáng (6h-14h)';
      if (caLam.includes('Ca chiều')) return 'Ca chiều (14h-22h)';
      if (caLam.includes('Ca đêm')) return 'Ca đêm (22h-6h)';
      if (caLam.includes('Hành chính')) return 'Hành chính (8h-17h)';
      return caLam;
    },

    /**
     * MÀU BADGE BỘ PHẬN
     */
    getDepartmentBadgeClass(dept) {
      switch (dept) {
        case 'Lễ tân':
          return 'dept-le-tan';
        case 'Buồng phòng':
          return 'dept-buong-phong';
        case 'Ban Quản lý':
          return 'dept-quan-ly';
        case 'Kế toán':
          return 'dept-ke-toan';
        case 'Kỹ thuật - Bảo trì':
          return 'dept-ky-thuat';
        case 'Nhà hàng & Bếp':
          return 'dept-nha-hang';
        case 'An ninh':
          return 'dept-an-ninh';
        default:
          return 'dept-default';
      }
    },

    /**
     * MÀU AVATAR DỰA THEO BỘ PHẬN
     */
    getAvatarColorClass(dept) {
      switch (dept) {
        case 'Lễ tân':
          return 'av-teal';
        case 'Buồng phòng':
          return 'av-blue';
        case 'Ban Quản lý':
          return 'av-dark';
        case 'Kế toán':
          return 'av-amber';
        case 'Kỹ thuật - Bảo trì':
          return 'av-purple';
        case 'Nhà hàng & Bếp':
          return 'av-rose';
        case 'An ninh':
          return 'av-slate';
        default:
          return 'av-teal';
      }
    },

    /**
     * MÀU BADGE TRẠNG THÁI
     */
    getStatusBadgeClass(status) {
      switch (status) {
        case 'Đang trong ca':
        case 'Đang làm':
          return 'badge-st-active';
        case 'Nghỉ ca':
          return 'badge-st-shift-off';
        case 'Tạm nghỉ':
          return 'badge-st-leave';
        case 'Đã nghỉ việc':
          return 'badge-st-resigned';
        default:
          return 'badge-st-active';
      }
    }
  }
};
</script>

<style scoped>
.nhan-vien-page {
  background-color: #F8F9FA;
  min-height: calc(100vh - 120px);
}

.text-teal {
  color: #1E5E49 !important;
}

.bg-teal-subtle {
  background-color: #E6F3EE !important;
}

.text-main-dark {
  color: #172620;
}

.title-bar-accent {
  display: inline-block;
  width: 5px;
  height: 24px;
  background-color: #1E5E49;
  border-radius: 3px;
}

.btn-teal-primary {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-teal-primary:hover {
  background-color: #154536;
  border-color: #154536;
  color: #ffffff;
}

.btn-outline-teal {
  color: #1E5E49;
  border-color: #1E5E49;
  background-color: transparent;
}

.btn-outline-teal:hover {
  color: #ffffff;
  background-color: #1E5E49;
}

/* THẺ THỐNG KÊ */
.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06) !important;
}

.stat-icon-wrapper {
  width: 52px;
  height: 52px;
  flex-shrink: 0;
}

/* BẢNG NHÂN VIÊN */
.custom-staff-table th {
  border-bottom: 1px solid #EAE8E1;
  font-weight: 600;
  letter-spacing: 0.3px;
  background-color: #F8FAF9;
}

.custom-staff-table td {
  border-bottom: 1px solid #F1F3F5;
  padding-top: 0.85rem;
  padding-bottom: 0.85rem;
}

.staff-row {
  transition: background-color 0.15s ease;
}

.staff-row:hover {
  background-color: #F4F8F6;
}

.staff-row.row-active {
  background-color: #EAF4F0 !important;
  border-left: 3px solid #1E5E49;
}

.staff-avatar {
  width: 36px;
  height: 36px;
  font-size: 0.8rem;
  letter-spacing: -0.5px;
}

.large-avatar {
  width: 64px;
  height: 64px;
  font-size: 1.4rem;
}

/* BỘ MÀU AVATAR */
.av-teal { background: linear-gradient(135deg, #1E5E49 0%, #154536 100%); }
.av-blue { background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); }
.av-dark { background: linear-gradient(135deg, #334155 0%, #1E293B 100%); }
.av-amber { background: linear-gradient(135deg, #D97706 0%, #B45309 100%); }
.av-purple { background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 100%); }
.av-rose { background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%); }
.av-slate { background: linear-gradient(135deg, #475569 0%, #334155 100%); }

/* BADGE BỘ PHẬN */
.badge-department {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 50rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.dept-le-tan { background-color: #E6F3EE; color: #1E5E49; border: 1px solid #C5E5D8; }
.dept-buong-phong { background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
.dept-quan-ly { background-color: #F1F5F9; color: #0F172A; border: 1px solid #CBD5E1; }
.dept-ke-toan { background-color: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
.dept-ky-thuat { background-color: #F5F3FF; color: #5B21B6; border: 1px solid #DDD6FE; }
.dept-nha-hang { background-color: #FFF1F2; color: #9F1239; border: 1px solid #FECDD3; }
.dept-an-ninh { background-color: #F8FAFC; color: #334155; border: 1px solid #E2E8F0; }
.dept-default { background-color: #F3F4F6; color: #374151; }

/* BADGE TRẠNG THÁI */
.badge-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.25rem 0.65rem;
  border-radius: 50rem;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.badge-st-active {
  background-color: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}
.badge-st-active .status-dot {
  background-color: #10B981;
}

.badge-st-shift-off {
  background-color: #F3F4F6;
  color: #4B5563;
  border: 1px solid #E5E7EB;
}
.badge-st-shift-off .status-dot {
  background-color: #9CA3AF;
}

.badge-st-leave {
  background-color: #FFFBEB;
  color: #92400E;
  border: 1px solid #FDE68A;
}
.badge-st-leave .status-dot {
  background-color: #F59E0B;
}

.badge-st-resigned {
  background-color: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
}
.badge-st-resigned .status-dot {
  background-color: #EF4444;
}

.dot-active { background-color: #10B981; }
.dot-shift-off { background-color: #9CA3AF; }
.dot-leave { background-color: #F59E0B; }
.dot-resigned { background-color: #EF4444; }

/* HOVER & TRANSITION */
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease; }
.shadow-xs { box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05); }

.spin-infinite {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-fade-in {
  animation: fadeIn 0.25s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
