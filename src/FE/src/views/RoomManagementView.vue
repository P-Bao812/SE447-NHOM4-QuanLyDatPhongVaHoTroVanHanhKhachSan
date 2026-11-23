<template>
  <div class="room-management-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/admin/dashboard" class="text-decoration-none text-teal">
              Quản trị PMS
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quản lý phòng vật lý (UC06)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-7">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-door-open fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Quản Lý Phòng Vật Lý</h2>
              <p class="text-muted mb-0 small">
                Tra cứu, lọc, thêm mới phòng, cấu hình loại phòng và chuyển đổi tình trạng vận hành (Phân hệ PMS - UC06).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-5 text-lg-end">
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
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Người dùng' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isManager ? 'Có toàn quyền quản lý, thêm, sửa và đổi trạng thái phòng' : 'Chỉ Quản lý mới có quyền chỉnh sửa/thêm phòng' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Test & Switch Account Toolbar -->
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
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'admin' }"
                :disabled="isLoading"
                @click="quickSwitchUser('admin', '123456', 'Quản lý')"
              >
                <i class="bi bi-shield-lock me-1"></i> Quản lý (admin)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'letan01' }"
                :disabled="isLoading"
                @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
              >
                <i class="bi bi-person-badge me-1"></i> Lễ tân (Thử 403)
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
                @click="fetchRooms"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
              <button
                type="button"
                class="btn btn-sm btn-teal text-white rounded-3 px-3 fw-semibold shadow-sm"
                :disabled="!isManager"
                @click="openAddModal"
              >
                <i class="bi bi-plus-circle me-1"></i>
                Thêm phòng mới
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Thống kê tổng quan nhanh -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div
            class="card border-0 shadow-sm rounded-4 h-100 stat-card cursor-pointer"
            :class="{ 'border-active': filters.trangThai === '' }"
            @click="setFilterStatus('')"
          >
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase d-block mb-1" style="font-size: 0.72rem;">
                  Tổng số phòng
                </span>
                <h3 class="fw-bold text-navy mb-0">{{ stats.total }}</h3>
              </div>
              <div class="stat-icon bg-primary-subtle text-primary rounded-3">
                <i class="bi bi-buildings"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div
            class="card border-0 shadow-sm rounded-4 h-100 stat-card cursor-pointer"
            :class="{ 'border-active': filters.trangThai === 'Sẵn sàng' }"
            @click="setFilterStatus('Sẵn sàng')"
          >
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase d-block mb-1" style="font-size: 0.72rem;">
                  Sẵn sàng
                </span>
                <h3 class="fw-bold text-success mb-0">{{ stats.ready }}</h3>
              </div>
              <div class="stat-icon bg-success-subtle text-success rounded-3">
                <i class="bi bi-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div
            class="card border-0 shadow-sm rounded-4 h-100 stat-card cursor-pointer"
            :class="{ 'border-active': filters.trangThai === 'Đang bảo trì' }"
            @click="setFilterStatus('Đang bảo trì')"
          >
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase d-block mb-1" style="font-size: 0.72rem;">
                  Đang bảo trì
                </span>
                <h3 class="fw-bold text-warning mb-0">{{ stats.maintenance }}</h3>
              </div>
              <div class="stat-icon bg-warning-subtle text-warning rounded-3">
                <i class="bi bi-tools"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-3">
          <div
            class="card border-0 shadow-sm rounded-4 h-100 stat-card cursor-pointer"
            :class="{ 'border-active': filters.trangThai === 'Ngưng khai thác' }"
            @click="setFilterStatus('Ngưng khai thác')"
          >
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small fw-medium text-uppercase d-block mb-1" style="font-size: 0.72rem;">
                  Ngưng khai thác
                </span>
                <h3 class="fw-bold text-secondary mb-0">{{ stats.retired }}</h3>
              </div>
              <div class="stat-icon bg-secondary-subtle text-secondary rounded-3">
                <i class="bi bi-slash-circle"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Khung Tìm kiếm & Bộ lọc -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3 p-lg-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm tên/số phòng -->
            <div class="col-md-4 col-lg-3">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-search me-1"></i> Tên / Số phòng
              </label>
              <div class="input-group input-group-sm">
                <input
                  type="text"
                  class="form-control rounded-3"
                  placeholder="Ví dụ: P101, P205..."
                  v-model.trim="filters.keyword"
                  @keyup.enter="handleSearch"
                />
                <button
                  type="button"
                  class="btn btn-outline-secondary"
                  v-if="filters.keyword"
                  @click="filters.keyword = ''; handleSearch()"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
            </div>

            <!-- Lọc theo tầng -->
            <div class="col-6 col-md-2 col-lg-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-layers me-1"></i> Tầng
              </label>
              <select
                class="form-select form-select-sm rounded-3"
                v-model="filters.tang"
                @change="handleSearch"
              >
                <option value="">Tất cả tầng</option>
                <option v-for="fl in availableFloors" :key="fl" :value="fl">
                  Tầng {{ fl }}
                </option>
              </select>
            </div>

            <!-- Lọc theo loại phòng -->
            <div class="col-6 col-md-3 col-lg-3">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-tag me-1"></i> Loại phòng
              </label>
              <select
                class="form-select form-select-sm rounded-3"
                v-model="filters.maLoaiPhong"
                @change="handleSearch"
              >
                <option value="">Tất cả loại phòng</option>
                <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                  {{ type.tenLoai }} ({{ formatCurrency(type.giaTieuChuan) }})
                </option>
              </select>
            </div>

            <!-- Lọc theo trạng thái vận hành -->
            <div class="col-6 col-md-3 col-lg-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-activity me-1"></i> Trạng thái
              </label>
              <select
                class="form-select form-select-sm rounded-3"
                v-model="filters.trangThai"
                @change="handleSearch"
              >
                <option value="">Tất cả</option>
                <option value="Sẵn sàng">Sẵn sàng</option>
                <option value="Đang bảo trì">Đang bảo trì</option>
                <option value="Ngưng khai thác">Ngưng khai thác</option>
              </select>
            </div>

            <!-- Nút thao tác lọc -->
            <div class="col-6 col-md-12 col-lg-2 d-flex gap-2">
              <button
                type="button"
                class="btn btn-sm btn-navy text-white rounded-3 w-100 fw-semibold"
                :disabled="isLoading"
                @click="handleSearch"
              >
                <i class="bi bi-funnel me-1"></i> Lọc
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                title="Đặt lại bộ lọc"
                :disabled="isLoading"
                @click="resetFilters"
              >
                <i class="bi bi-arrow-counterclockwise"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Thông báo lỗi khi vai trò không được cấp quyền (403) -->
      <div v-if="!isManager" class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3">
        <i class="bi bi-shield-exclamation fs-3 text-warning"></i>
        <div>
          <h6 class="fw-bold mb-1">Chế độ Xem Hạn Chế</h6>
          <p class="mb-0 small text-muted">
            Tài khoản hiện tại thuộc vai trò <strong>{{ currentUser.vaiTro || 'Chưa xác thực' }}</strong>.
            Chỉ <strong>Quản lý</strong> mới có quyền thêm mới, chỉnh sửa thông tin phòng, đổi tình trạng bảo trì/ngưng khai thác hoặc xóa phòng.
            Bạn có thể dùng thanh <strong>Quick-Test</strong> phía trên để chuyển sang tài khoản <code>admin</code>.
          </p>
        </div>
      </div>

      <!-- Bảng danh sách phòng -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-navy mb-0">Danh Sách Phòng</h5>
            <span class="badge bg-light text-navy border small">
              {{ pagination.total }} phòng
            </span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">Hiển thị:</span>
            <select
              class="form-select form-select-sm rounded-3 py-1 px-2"
              style="width: 80px;"
              v-model.number="pagination.per_page"
              @change="filters.page = 1; fetchRooms()"
            >
              <option :value="10">10</option>
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light border-bottom">
              <tr>
                <th scope="col" class="ps-4 text-secondary small fw-semibold" style="width: 70px;">ID</th>
                <th scope="col" class="text-secondary small fw-semibold">Tên / Số phòng</th>
                <th scope="col" class="text-secondary small fw-semibold">Tầng</th>
                <th scope="col" class="text-secondary small fw-semibold">Loại phòng</th>
                <th scope="col" class="text-secondary small fw-semibold">Sức chứa tối đa</th>
                <th scope="col" class="text-secondary small fw-semibold">Giá tiêu chuẩn</th>
                <th scope="col" class="text-secondary small fw-semibold">Tình trạng vận hành</th>
                <th scope="col" class="pe-4 text-end text-secondary small fw-semibold" style="width: 170px;">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <!-- Trạng thái Loading -->
              <tr v-if="isLoading">
                <td colspan="8" class="text-center py-5">
                  <div class="spinner-border text-teal" role="status">
                    <span class="visually-hidden">Đang tải dữ liệu...</span>
                  </div>
                  <div class="mt-2 text-muted small">Đang nạp danh sách phòng từ hệ thống...</div>
                </td>
              </tr>

              <!-- Trạng thái Không có dữ liệu -->
              <tr v-else-if="rooms.length === 0">
                <td colspan="8" class="text-center py-5">
                  <div class="text-muted mb-2">
                    <i class="bi bi-inbox fs-1 d-block text-secondary opacity-50"></i>
                    Không tìm thấy phòng nào phù hợp với điều kiện tìm kiếm.
                  </div>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-teal rounded-3 mt-2"
                    @click="resetFilters"
                  >
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc
                  </button>
                </td>
              </tr>

              <!-- Danh sách phòng dữ liệu thật -->
              <tr v-for="room in rooms" :key="room.id" class="room-row">
                <!-- ID -->
                <td class="ps-4 fw-semibold text-secondary small">
                  #{{ room.id }}
                </td>

                <!-- Tên / Số phòng -->
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="room-badge fw-bold text-navy">
                      {{ room.tenPhong }}
                    </span>
                  </div>
                </td>

                <!-- Tầng -->
                <td>
                  <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-medium">
                    Tầng {{ room.tang }}
                  </span>
                </td>

                <!-- Loại phòng -->
                <td>
                  <div class="fw-semibold text-navy">
                    {{ room.loaiPhong?.tenLoai || 'Chưa gán loại' }}
                  </div>
                  <small class="text-muted d-block text-truncate" style="max-width: 220px;" :title="room.loaiPhong?.moTa || ''">
                    {{ room.loaiPhong?.moTa || '—' }}
                  </small>
                </td>

                <!-- Sức chứa -->
                <td>
                  <span class="small fw-semibold text-secondary">
                    <i class="bi bi-person me-1 text-muted"></i>
                    {{ room.loaiPhong?.sucChuaToiDa || 2 }} người
                  </span>
                </td>

                <!-- Giá tiêu chuẩn -->
                <td>
                  <span class="fw-bold text-navy small">
                    {{ formatCurrency(room.loaiPhong?.giaTieuChuan || 0) }}
                  </span>
                  <span class="text-muted small"> /đêm</span>
                </td>

                <!-- Tình trạng vận hành -->
                <td>
                  <span
                    class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    :class="getStatusBadgeClass(room.trangThai)"
                  >
                    <i :class="getStatusIcon(room.trangThai)" class="me-1"></i>
                    {{ room.trangThai }}
                  </span>
                </td>

                <!-- Nút thao tác -->
                <td class="pe-4 text-end">
                  <div class="btn-group btn-group-sm">
                    <!-- Xem chi tiết -->
                    <button
                      type="button"
                      class="btn btn-outline-secondary rounded-2 me-1"
                      title="Xem chi tiết phòng"
                      @click="openDetailModal(room)"
                    >
                      <i class="bi bi-eye"></i>
                    </button>

                    <!-- Chỉnh sửa thông tin phòng -->
                    <button
                      type="button"
                      class="btn btn-outline-primary rounded-2 me-1"
                      title="Chỉnh sửa thông tin phòng"
                      :disabled="!isManager"
                      @click="openEditModal(room)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>

                    <!-- Chuyển trạng thái nhanh -->
                    <button
                      type="button"
                      class="btn btn-outline-warning rounded-2 me-1 text-dark"
                      title="Thay đổi tình trạng vận hành"
                      :disabled="!isManager"
                      @click="openStatusModal(room)"
                    >
                      <i class="bi bi-sliders"></i>
                    </button>

                    <!-- Xóa phòng -->
                    <button
                      type="button"
                      class="btn btn-outline-danger rounded-2"
                      title="Xóa phòng"
                      :disabled="!isManager"
                      @click="openDeleteModal(room)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Phân trang (Pagination) -->
        <div
          v-if="pagination.last_page > 1"
          class="card-footer bg-white py-3 px-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-2"
        >
          <div class="small text-muted">
            Hiển thị trang <strong>{{ pagination.current_page }}</strong> / <strong>{{ pagination.last_page }}</strong>
            (Tổng cộng {{ pagination.total }} phòng)
          </div>
          <nav aria-label="Phân trang">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: pagination.current_page <= 1 }">
                <button
                  class="page-link"
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page <= 1"
                >
                  <i class="bi bi-chevron-left"></i>
                </button>
              </li>

              <li
                v-for="p in visiblePages"
                :key="p"
                class="page-item"
                :class="{ active: p === pagination.current_page }"
              >
                <button class="page-link" @click="goToPage(p)">
                  {{ p }}
                </button>
              </li>

              <li class="page-item" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                <button
                  class="page-link"
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page >= pagination.last_page"
                >
                  <i class="bi bi-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: THÊM PHÒNG MỚI (POST /api/v1/rooms)                   -->
    <!-- ============================================================== -->
    <div
      v-if="showAddModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-plus-circle-fill fs-4 text-teal"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">Thêm Phòng Vật Lý Mới</h5>
                <small class="text-white-50">Tạo mới phòng vào sơ đồ vận hành</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              :disabled="isSubmitting"
              @click="closeAddModal"
            ></button>
          </div>

          <form @submit.prevent="submitAddRoom">
            <div class="modal-body p-4">
              <!-- Cảnh báo lỗi API nếu có -->
              <div v-if="apiError" class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ apiError }}
              </div>

              <!-- Tên / Số phòng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Tên / Số phòng <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control rounded-3"
                  :class="{ 'is-invalid': fieldErrors.tenPhong }"
                  placeholder="Ví dụ: P101, P205, VIP301"
                  v-model.trim="addForm.tenPhong"
                  maxlength="20"
                  required
                />
                <div v-if="fieldErrors.tenPhong" class="invalid-feedback small">
                  {{ fieldErrors.tenPhong }}
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">
                  Tối đa 20 ký tự, không được trùng với phòng đã có trong hệ thống.
                </small>
              </div>

              <!-- Loại phòng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Loại phòng <span class="text-danger">*</span>
                </label>
                <select
                  class="form-select rounded-3"
                  :class="{ 'is-invalid': fieldErrors.maLoaiPhong }"
                  v-model="addForm.maLoaiPhong"
                  required
                >
                  <option value="" disabled>-- Chọn loại phòng --</option>
                  <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                    {{ type.tenLoai }} ({{ formatCurrency(type.giaTieuChuan) }} / đêm, tối đa {{ type.sucChuaToiDa }} khách)
                  </option>
                </select>
                <div v-if="fieldErrors.maLoaiPhong" class="invalid-feedback small">
                  {{ fieldErrors.maLoaiPhong }}
                </div>
              </div>

              <!-- Tầng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Tầng <span class="text-danger">*</span>
                </label>
                <input
                  type="number"
                  class="form-control rounded-3"
                  :class="{ 'is-invalid': fieldErrors.tang }"
                  placeholder="Ví dụ: 1, 2, 3..."
                  v-model.number="addForm.tang"
                  min="1"
                  required
                />
                <div v-if="fieldErrors.tang" class="invalid-feedback small">
                  {{ fieldErrors.tang }}
                </div>
              </div>

              <!-- Trạng thái vận hành ban đầu -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Trạng thái vận hành ban đầu <span class="text-danger">*</span>
                </label>
                <select
                  class="form-select rounded-3"
                  :class="{ 'is-invalid': fieldErrors.trangThai }"
                  v-model="addForm.trangThai"
                  required
                >
                  <option value="Sẵn sàng">Sẵn sàng (Đưa vào kinh doanh ngay)</option>
                  <option value="Đang bảo trì">Đang bảo trì (Chưa tiếp nhận khách)</option>
                  <option value="Ngưng khai thác">Ngưng khai thác</option>
                </select>
                <div v-if="fieldErrors.trangThai" class="invalid-feedback small">
                  {{ fieldErrors.trangThai }}
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3 border-0">
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3 px-3"
                :disabled="isSubmitting"
                @click="closeAddModal"
              >
                Hủy bỏ
              </button>
              <button
                type="submit"
                class="btn btn-teal text-white rounded-3 px-4 fw-semibold"
                :disabled="isSubmitting"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-check-circle me-1"></i>
                Lưu phòng mới
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: CHỈNH SỬA THÔNG TIN PHÒNG (PUT /api/v1/rooms/{id})    -->
    <!-- ============================================================== -->
    <div
      v-if="showEditModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-pencil-square fs-4 text-warning"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">Chỉnh Sửa Phòng #{{ editForm.id }}</h5>
                <small class="text-white-50">Cập nhật tên, tầng, loại phòng hoặc trạng thái</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              :disabled="isSubmitting"
              @click="closeEditModal"
            ></button>
          </div>

          <form @submit.prevent="submitEditRoom">
            <div class="modal-body p-4">
              <!-- Cảnh báo lỗi 409 hoặc API nếu có -->
              <div v-if="apiError" class="alert alert-danger py-2 px-3 small rounded-3 mb-3 d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 mt-0.5"></i>
                <div>
                  <strong>Không thể cập nhật:</strong>
                  <div>{{ apiError }}</div>
                </div>
              </div>

              <!-- Tên / Số phòng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Tên / Số phòng <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control rounded-3"
                  :class="{ 'is-invalid': fieldErrors.tenPhong }"
                  placeholder="Ví dụ: P101, P205, VIP301"
                  v-model.trim="editForm.tenPhong"
                  maxlength="20"
                  required
                />
                <div v-if="fieldErrors.tenPhong" class="invalid-feedback small">
                  {{ fieldErrors.tenPhong }}
                </div>
              </div>

              <!-- Gán lại loại phòng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Gán Loại phòng <span class="text-danger">*</span>
                </label>
                <select
                  class="form-select rounded-3"
                  :class="{ 'is-invalid': fieldErrors.maLoaiPhong }"
                  v-model="editForm.maLoaiPhong"
                  required
                >
                  <option v-for="type in roomTypes" :key="type.id" :value="type.id">
                    {{ type.tenLoai }} ({{ formatCurrency(type.giaTieuChuan) }} / đêm, tối đa {{ type.sucChuaToiDa }} khách)
                  </option>
                </select>
                <div v-if="fieldErrors.maLoaiPhong" class="invalid-feedback small">
                  {{ fieldErrors.maLoaiPhong }}
                </div>
              </div>

              <!-- Tầng -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Tầng <span class="text-danger">*</span>
                </label>
                <input
                  type="number"
                  class="form-control rounded-3"
                  :class="{ 'is-invalid': fieldErrors.tang }"
                  placeholder="Ví dụ: 1, 2, 3..."
                  v-model.number="editForm.tang"
                  min="1"
                  required
                />
                <div v-if="fieldErrors.tang" class="invalid-feedback small">
                  {{ fieldErrors.tang }}
                </div>
              </div>

              <!-- Tình trạng vận hành -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-1">
                  Tình trạng vận hành <span class="text-danger">*</span>
                </label>
                <select
                  class="form-select rounded-3"
                  :class="{ 'is-invalid': fieldErrors.trangThai }"
                  v-model="editForm.trangThai"
                  required
                >
                  <option value="Sẵn sàng">Sẵn sàng</option>
                  <option value="Đang bảo trì">Đang bảo trì</option>
                  <option value="Ngưng khai thác">Ngưng khai thác</option>
                </select>
                <div v-if="fieldErrors.trangThai" class="invalid-feedback small">
                  {{ fieldErrors.trangThai }}
                </div>
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                  <i class="bi bi-info-circle me-1"></i>
                  Lưu ý: Không thể chuyển sang <strong>Đang bảo trì</strong> hoặc <strong>Ngưng khai thác</strong> nếu phòng đang có khách lưu trú hoặc có đơn đặt phòng hiệu lực chưa hoàn tất (Quy tắc BR01.5, BR01.6).
                </small>
              </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3 border-0">
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3 px-3"
                :disabled="isSubmitting"
                @click="closeEditModal"
              >
                Hủy bỏ
              </button>
              <button
                type="submit"
                class="btn btn-navy text-white rounded-3 px-4 fw-semibold"
                :disabled="isSubmitting"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-save me-1"></i>
                Lưu thay đổi
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: ĐỔI TRẠNG THÁI NHANH (PUT /api/v1/rooms/{id}/status)  -->
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
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-sliders fs-4 text-warning"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">Cập Nhật Tình Trạng Vận Hành</h5>
                <small class="text-white-50">Phòng {{ statusForm.room?.tenPhong }} (Tầng {{ statusForm.room?.tang }})</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              :disabled="isSubmitting"
              @click="closeStatusModal"
            ></button>
          </div>

          <form @submit.prevent="submitStatusChange">
            <div class="modal-body p-4">
              <!-- Cảnh báo lỗi 409 từ API -->
              <div v-if="apiError" class="alert alert-danger py-2.5 px-3 small rounded-3 mb-3 d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 mt-0.5 text-danger"></i>
                <div>
                  <strong class="d-block mb-1">Xung đột trạng thái (409 Conflict):</strong>
                  <div>{{ apiError }}</div>
                </div>
              </div>

              <!-- Thông tin tóm tắt phòng -->
              <div class="bg-light p-3 rounded-3 mb-3 border">
                <div class="row g-2 small">
                  <div class="col-6">
                    <span class="text-muted">Phòng:</span>
                    <strong class="text-navy ms-1">{{ statusForm.room?.tenPhong }}</strong>
                  </div>
                  <div class="col-6">
                    <span class="text-muted">Tầng:</span>
                    <strong class="text-navy ms-1">{{ statusForm.room?.tang }}</strong>
                  </div>
                  <div class="col-12">
                    <span class="text-muted">Loại phòng:</span>
                    <strong class="text-navy ms-1">{{ statusForm.room?.loaiPhong?.tenLoai || '—' }}</strong>
                  </div>
                  <div class="col-12">
                    <span class="text-muted">Trạng thái hiện tại:</span>
                    <span
                      class="badge rounded-pill ms-2"
                      :class="getStatusBadgeClass(statusForm.room?.trangThai)"
                    >
                      {{ statusForm.room?.trangThai }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Chọn trạng thái mới -->
              <div class="mb-3">
                <label class="form-label small fw-semibold text-navy mb-2">
                  Chọn tình trạng vận hành mới <span class="text-danger">*</span>
                </label>
                <div class="d-flex flex-column gap-2">
                  <label
                    class="form-check p-3 rounded-3 border cursor-pointer transition-all"
                    :class="{ 'border-success bg-success-subtle': statusForm.trangThai === 'Sẵn sàng' }"
                  >
                    <input
                      class="form-check-input ms-0 me-2"
                      type="radio"
                      name="radioStatus"
                      value="Sẵn sàng"
                      v-model="statusForm.trangThai"
                    />
                    <span class="fw-semibold text-success">Sẵn sàng</span>
                    <small class="d-block text-muted mt-1 ps-4">
                      Phòng sạch sẽ, đủ điều kiện đón tiếp và sẵn sàng cho khách đặt hoặc nhận phòng.
                    </small>
                  </label>

                  <label
                    class="form-check p-3 rounded-3 border cursor-pointer transition-all"
                    :class="{ 'border-warning bg-warning-subtle': statusForm.trangThai === 'Đang bảo trì' }"
                  >
                    <input
                      class="form-check-input ms-0 me-2"
                      type="radio"
                      name="radioStatus"
                      value="Đang bảo trì"
                      v-model="statusForm.trangThai"
                    />
                    <span class="fw-semibold text-warning">Đang bảo trì</span>
                    <small class="d-block text-muted mt-1 ps-4">
                      Phòng hỏng hóc hoặc cần sửa chữa, tạm thời loại khỏi danh sách chọn đặt phòng mới.
                    </small>
                  </label>

                  <label
                    class="form-check p-3 rounded-3 border cursor-pointer transition-all"
                    :class="{ 'border-secondary bg-secondary-subtle': statusForm.trangThai === 'Ngưng khai thác' }"
                  >
                    <input
                      class="form-check-input ms-0 me-2"
                      type="radio"
                      name="radioStatus"
                      value="Ngưng khai thác"
                      v-model="statusForm.trangThai"
                    />
                    <span class="fw-semibold text-secondary">Ngưng khai thác</span>
                    <small class="d-block text-muted mt-1 ps-4">
                      Dừng sử dụng vĩnh viễn hoặc dài hạn. Thay thế cho việc xóa cứng phòng đã từng có lịch sử lưu trú.
                    </small>
                  </label>
                </div>
              </div>

              <div class="alert alert-info py-2 px-3 small rounded-3 mb-0">
                <i class="bi bi-shield-lock me-1"></i>
                Hệ thống tự động áp dụng khóa bi quan (Pessimistic Locking) và kiểm tra các đơn đặt/lưu trú trước khi xác nhận.
              </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3 border-0">
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3 px-3"
                :disabled="isSubmitting"
                @click="closeStatusModal"
              >
                Hủy bỏ
              </button>
              <button
                type="submit"
                class="btn btn-warning text-dark rounded-3 px-4 fw-semibold"
                :disabled="isSubmitting || statusForm.trangThai === statusForm.room?.trangThai"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                Cập nhật trạng thái
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: XEM CHI TIẾT PHÒNG (GET /api/v1/rooms/{id})           -->
    <!-- ============================================================== -->
    <div
      v-if="showDetailModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-navy text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-info-circle-fill fs-4 text-teal"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">Chi Tiết Phòng {{ selectedRoom?.tenPhong }}</h5>
                <small class="text-white-50">Mã phòng: #{{ selectedRoom?.id }}</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              @click="closeDetailModal"
            ></button>
          </div>

          <div class="modal-body p-4">
            <div class="row g-4">
              <!-- Cột 1: Thông số phòng vật lý -->
              <div class="col-md-6">
                <div class="card border rounded-3 p-3 h-100 bg-light">
                  <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-door-closed text-teal"></i> Thông Tin Phòng Vật Lý
                  </h6>
                  <ul class="list-unstyled mb-0 small d-flex flex-column gap-2.5">
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Mã số (ID):</span>
                      <strong class="text-navy">#{{ selectedRoom?.id }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Tên / Số phòng:</span>
                      <strong class="text-navy fs-6">{{ selectedRoom?.tenPhong }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Vị trí tầng:</span>
                      <strong class="text-navy">Tầng {{ selectedRoom?.tang }}</strong>
                    </li>
                    <li class="d-flex justify-content-between align-items-center">
                      <span class="text-muted">Tình trạng vận hành:</span>
                      <span
                        class="badge rounded-pill px-3 py-1 fw-semibold"
                        :class="getStatusBadgeClass(selectedRoom?.trangThai)"
                      >
                        {{ selectedRoom?.trangThai }}
                      </span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Ngày tạo bản ghi:</span>
                      <span class="text-secondary">{{ selectedRoom?.created_at || '—' }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Cập nhật lần cuối:</span>
                      <span class="text-secondary">{{ selectedRoom?.updated_at || '—' }}</span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Cột 2: Cấu hình loại phòng -->
              <div class="col-md-6">
                <div class="card border rounded-3 p-3 h-100 bg-light">
                  <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-tag text-teal"></i> Loại Phòng Áp Dụng
                  </h6>
                  <ul class="list-unstyled mb-0 small d-flex flex-column gap-2.5">
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Mã loại phòng:</span>
                      <strong class="text-navy">#{{ selectedRoom?.loaiPhong?.id || selectedRoom?.maLoaiPhong }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Tên loại phòng:</span>
                      <strong class="text-navy fs-6">{{ selectedRoom?.loaiPhong?.tenLoai || '—' }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Giá tiêu chuẩn niêm yết:</span>
                      <strong class="text-teal fs-6">
                        {{ formatCurrency(selectedRoom?.loaiPhong?.giaTieuChuan || 0) }} /đêm
                      </strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Sức chứa tối đa:</span>
                      <strong class="text-navy">{{ selectedRoom?.loaiPhong?.sucChuaToiDa || 2 }} khách</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                      <span class="text-muted">Trạng thái loại:</span>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        {{ selectedRoom?.loaiPhong?.trangThai || 'Kinh doanh' }}
                      </span>
                    </li>
                    <li>
                      <span class="text-muted d-block mb-1">Mô tả loại phòng:</span>
                      <div class="p-2 bg-white rounded border small text-muted">
                        {{ selectedRoom?.loaiPhong?.moTa || 'Chưa có mô tả chi tiết cho loại phòng này.' }}
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
            <div>
              <button
                v-if="isManager"
                type="button"
                class="btn btn-sm btn-outline-warning text-dark rounded-3 me-2"
                @click="closeDetailModal(); openStatusModal(selectedRoom)"
              >
                <i class="bi bi-sliders me-1"></i> Đổi trạng thái
              </button>
              <button
                v-if="isManager"
                type="button"
                class="btn btn-sm btn-outline-primary rounded-3"
                @click="closeDetailModal(); openEditModal(selectedRoom)"
              >
                <i class="bi bi-pencil me-1"></i> Chỉnh sửa
              </button>
            </div>
            <button
              type="button"
              class="btn btn-secondary rounded-3 px-4"
              @click="closeDetailModal"
            >
              Đóng
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 5: XÁC NHẬN XÓA PHÒNG (DELETE /api/v1/rooms/{id})        -->
    <!-- ============================================================== -->
    <div
      v-if="showDeleteModal"
      class="modal fade show d-block"
      tabindex="-1"
      style="background: rgba(0, 0, 0, 0.6); z-index: 1055;"
      role="dialog"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-danger text-white py-3 px-4 border-0">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-trash-fill fs-4 text-white"></i>
              <div>
                <h5 class="modal-title fw-bold mb-0">Xác Nhận Xóa Phòng</h5>
                <small class="text-white-50">Thao tác này sẽ xóa bản ghi khỏi hệ thống</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close btn-close-white"
              :disabled="isSubmitting"
              @click="closeDeleteModal"
            ></button>
          </div>

          <div class="modal-body p-4">
            <!-- Hiển thị lỗi 409 nếu phòng có lịch sử giao dịch -->
            <div v-if="deleteConflictError" class="alert alert-warning border-0 shadow-sm rounded-3 mb-3 p-3">
              <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning mt-0.5"></i>
                <div>
                  <strong class="d-block mb-1 text-dark">Không thể xóa phòng này (409 Conflict):</strong>
                  <p class="small mb-2 text-dark">{{ deleteConflictError }}</p>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-dark rounded-3 fw-semibold"
                    :disabled="isSubmitting"
                    @click="handleSwitchToRetiredFromDelete"
                  >
                    <i class="bi bi-slash-circle me-1"></i> Chuyển sang "Ngưng khai thác" ngay
                  </button>
                </div>
              </div>
            </div>

            <div v-else>
              <p class="mb-3">
                Bạn có chắc chắn muốn xóa phòng <strong>{{ deleteTargetRoom?.tenPhong }}</strong> (Tầng {{ deleteTargetRoom?.tang }}) không?
              </p>
              <div class="alert alert-info py-2 px-3 small rounded-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Quy tắc nghiệp vụ: Hệ thống chỉ cho phép xóa phòng chưa từng phát sinh giao dịch đặt phòng hoặc lưu trú. Nếu đã phát sinh giao dịch, phòng chỉ có thể chuyển sang <strong>Ngưng khai thác</strong>.
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light px-4 py-3 border-0">
            <button
              type="button"
              class="btn btn-outline-secondary rounded-3 px-3"
              :disabled="isSubmitting"
              @click="closeDeleteModal"
            >
              Hủy bỏ
            </button>
            <button
              v-if="!deleteConflictError"
              type="button"
              class="btn btn-danger rounded-3 px-4 fw-semibold"
              :disabled="isSubmitting"
              @click="confirmDeleteRoom"
            >
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1" role="status"></span>
              <i v-else class="bi bi-trash me-1"></i>
              Xác nhận xóa
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================== -->
    <!-- TOAST NOTIFICATIONS                                            -->
    <!-- ============================================================== -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast show align-items-center border-0 shadow-lg mb-2 rounded-3 overflow-hidden"
        :class="`bg-${toast.type} text-white`"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-2">
            <i :class="toast.icon" class="fs-5"></i>
            <div>
              <div class="fw-bold">{{ toast.title }}</div>
              <div class="small">{{ toast.message }}</div>
            </div>
          </div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            @click="removeToast(toast.id)"
          ></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

// 1. Trạng thái Loading & Phân trang & Dữ liệu
const isLoading = ref(false)
const isSubmitting = ref(false)
const rooms = ref([])
const roomTypes = ref([])

const pagination = reactive({
  total: 0,
  per_page: 15,
  current_page: 1,
  last_page: 1,
})

// 2. Bộ lọc tìm kiếm
const filters = reactive({
  keyword: '',
  tang: '',
  maLoaiPhong: '',
  trangThai: '',
  page: 1,
})

// 3. Quản lý Người dùng & Quyền hạn
const currentUser = reactive({
  tenDangNhap: '',
  vaiTro: '',
  hoTen: '',
})

const isManager = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'quản lý' || role === 'admin'
})

const loadCurrentUser = () => {
  try {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const user = JSON.parse(userStr)
      currentUser.tenDangNhap = user.tenDangNhap || ''
      currentUser.vaiTro = user.vaiTro || ''
      currentUser.hoTen = user.nhan_vien?.hoTen || user.khach_hang?.hoTen || user.hoTen || user.tenDangNhap || ''
    }
  } catch (e) {
    console.error('Không thể đọc thông tin người dùng từ localStorage:', e)
  }
}

// 4. Quick-Test: Chuyển đổi tài khoản nhanh
const quickSwitchUser = async (username, password, expectedRole) => {
  isLoading.value = true
  try {
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      matKhau: password,
    })

    if (res.data?.success && res.data?.data?.token) {
      localStorage.setItem('access_token', res.data.data.token)
      localStorage.setItem('user', JSON.stringify(res.data.data.user || {}))
      loadCurrentUser()
      showToast('success', 'Đổi tài khoản thành công', `Đang thao tác với vai trò: ${expectedRole} (${username})`)
      await fetchRooms()
      await fetchRoomTypes()
    }
  } catch (err) {
    showToast('danger', 'Đổi tài khoản thất bại', err.response?.data?.message || 'Vui lòng kiểm tra lại tài khoản.')
  } finally {
    isLoading.value = false
  }
}

// 5. Thống kê nhanh
const stats = computed(() => {
  const total = pagination.total || rooms.value.length
  let ready = 0
  let maintenance = 0
  let retired = 0

  rooms.value.forEach((r) => {
    if (r.trangThai === 'Sẵn sàng') ready++
    else if (r.trangThai === 'Đang bảo trì') maintenance++
    else if (r.trangThai === 'Ngưng khai thác') retired++
  })

  return { total, ready, maintenance, retired }
})

// Danh sách các tầng xuất hiện trong dữ liệu hoặc mặc định 1..5
const availableFloors = computed(() => {
  const set = new Set([1, 2, 3, 4, 5])
  rooms.value.forEach((r) => {
    if (r.tang) set.add(Number(r.tang))
  })
  return Array.from(set).sort((a, b) => a - b)
})

// Các trang nhìn thấy trong phân trang
const visiblePages = computed(() => {
  const current = pagination.current_page
  const last = pagination.last_page
  const delta = 2
  const pages = []

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    pages.push(i)
  }

  if (current - delta > 2) {
    pages.unshift('...')
  }
  if (current + delta < last - 1) {
    pages.push('...')
  }

  pages.unshift(1)
  if (last > 1) {
    pages.push(last)
  }

  return pages.filter((p) => p !== '...')
})

// 6. Tải danh sách phòng từ Backend API (GET /api/v1/rooms)
const fetchRooms = async () => {
  isLoading.value = true
  const token = localStorage.getItem('access_token')

  try {
    const params = {
      page: filters.page,
      per_page: pagination.per_page,
    }

    if (filters.keyword) params.keyword = filters.keyword
    if (filters.tang) params.tang = filters.tang
    if (filters.maLoaiPhong) params.maLoaiPhong = filters.maLoaiPhong
    if (filters.trangThai) params.trangThai = filters.trangThai

    const res = await axios.get('/api/v1/rooms', {
      headers: { Authorization: `Bearer ${token}` },
      params,
    })

    if (res.data?.success) {
      rooms.value = res.data.data || []

      if (res.data.meta?.pagination) {
        pagination.total = res.data.meta.pagination.total
        pagination.per_page = res.data.meta.pagination.per_page
        pagination.current_page = res.data.meta.pagination.current_page
        pagination.last_page = res.data.meta.pagination.last_page
      } else {
        pagination.total = rooms.value.length
        pagination.current_page = 1
        pagination.last_page = 1
      }
    }
  } catch (err) {
    console.error('Lỗi khi tải danh sách phòng:', err)
    if (err.response?.status === 401) {
      router.push({ path: '/dang-nhap', query: { redirect: route.fullPath } })
    } else if (err.response?.status === 403) {
      showToast('danger', 'Truy cập bị từ chối', 'Bạn không có quyền xem danh sách phòng.')
    } else {
      showToast('danger', 'Lỗi kết nối', 'Không thể nạp danh sách phòng từ máy chủ.')
    }
  } finally {
    isLoading.value = false
  }
}

// 7. Tải danh mục loại phòng (GET /api/v1/room-types hoặc trích xuất từ rooms)
const fetchRoomTypes = async () => {
  const token = localStorage.getItem('access_token')
  try {
    const res = await axios.get('/api/v1/room-types', {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success && Array.isArray(res.data.data)) {
      roomTypes.value = res.data.data
      return
    }
  } catch {
    // Fallback: nếu endpoint chưa sẵn sàng, trích xuất loại phòng từ danh sách phòng
    const map = new Map()
    rooms.value.forEach((r) => {
      if (r.loaiPhong && !map.has(r.loaiPhong.id)) {
        map.set(r.loaiPhong.id, r.loaiPhong)
      }
    })
    if (map.size > 0) {
      roomTypes.value = Array.from(map.values())
    }
  }
}

// 8. Tìm kiếm & Lọc
const handleSearch = () => {
  filters.page = 1
  fetchRooms()
}

const resetFilters = () => {
  filters.keyword = ''
  filters.tang = ''
  filters.maLoaiPhong = ''
  filters.trangThai = ''
  filters.page = 1
  fetchRooms()
}

const setFilterStatus = (st) => {
  filters.trangThai = st
  filters.page = 1
  fetchRooms()
}

const goToPage = (p) => {
  if (p < 1 || p > pagination.last_page || p === pagination.current_page) return
  filters.page = p
  fetchRooms()
}

// 9. Quản lý Modals & Forms
const showAddModal = ref(false)
const showEditModal = ref(false)
const showStatusModal = ref(false)
const showDetailModal = ref(false)
const showDeleteModal = ref(false)

const apiError = ref('')
const fieldErrors = reactive({})

const addForm = reactive({
  tenPhong: '',
  maLoaiPhong: '',
  tang: 1,
  trangThai: 'Sẵn sàng',
})

const editForm = reactive({
  id: null,
  tenPhong: '',
  maLoaiPhong: '',
  tang: 1,
  trangThai: 'Sẵn sàng',
})

const statusForm = reactive({
  room: null,
  trangThai: 'Sẵn sàng',
})

const selectedRoom = ref(null)
const deleteTargetRoom = ref(null)
const deleteConflictError = ref('')

const clearErrors = () => {
  apiError.value = ''
  Object.keys(fieldErrors).forEach((k) => delete fieldErrors[k])
}

// Mở Modal Thêm phòng
const openAddModal = () => {
  clearErrors()
  addForm.tenPhong = ''
  addForm.maLoaiPhong = roomTypes.value.length > 0 ? roomTypes.value[0].id : ''
  addForm.tang = 1
  addForm.trangThai = 'Sẵn sàng'
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  clearErrors()
}

// Mở Modal Sửa phòng
const openEditModal = (room) => {
  clearErrors()
  editForm.id = room.id
  editForm.tenPhong = room.tenPhong
  editForm.maLoaiPhong = room.maLoaiPhong || room.loaiPhong?.id || ''
  editForm.tang = room.tang
  editForm.trangThai = room.trangThai
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  clearErrors()
}

// Mở Modal Đổi trạng thái nhanh
const openStatusModal = (room) => {
  clearErrors()
  statusForm.room = room
  statusForm.trangThai = room.trangThai
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  clearErrors()
}

// Mở Modal Chi tiết
const openDetailModal = async (room) => {
  selectedRoom.value = room
  showDetailModal.value = true

  // Nạp chi tiết mới nhất từ API (GET /api/v1/rooms/{id})
  const token = localStorage.getItem('access_token')
  try {
    const res = await axios.get(`/api/v1/rooms/${room.id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (res.data?.success && res.data?.data) {
      selectedRoom.value = res.data.data
    }
  } catch (err) {
    console.error('Không thể nạp chi tiết phòng:', err)
  }
}

const closeDetailModal = () => {
  showDetailModal.value = false
  selectedRoom.value = null
}

// Mở Modal Xóa phòng
const openDeleteModal = (room) => {
  deleteTargetRoom.value = room
  deleteConflictError.value = ''
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deleteTargetRoom.value = null
  deleteConflictError.value = ''
}

// 10. Xử lý Thao tác API (Add, Edit, Change Status, Delete)

// Submit thêm phòng mới (POST /api/v1/rooms)
const submitAddRoom = async () => {
  clearErrors()

  // Validate phía Client
  if (!addForm.tenPhong) {
    fieldErrors.tenPhong = 'Vui lòng nhập tên / số phòng.'
    return
  }
  if (addForm.tenPhong.length > 20) {
    fieldErrors.tenPhong = 'Tên phòng không được vượt quá 20 ký tự.'
    return
  }
  if (!addForm.maLoaiPhong) {
    fieldErrors.maLoaiPhong = 'Vui lòng chọn loại phòng.'
    return
  }
  if (!addForm.tang || addForm.tang < 1) {
    fieldErrors.tang = 'Tầng phải là số nguyên tối thiểu là 1.'
    return
  }

  isSubmitting.value = true
  const token = localStorage.getItem('access_token')

  try {
    const payload = {
      tenPhong: addForm.tenPhong,
      maLoaiPhong: Number(addForm.maLoaiPhong),
      tang: Number(addForm.tang),
      trangThai: addForm.trangThai,
    }

    const res = await axios.post('/api/v1/rooms', payload, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success) {
      showToast('success', 'Thêm phòng thành công', `Phòng ${addForm.tenPhong} đã được đưa vào hệ thống.`)
      closeAddModal()
      await fetchRooms()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      const errors = err.response.data?.errors || err.response.data?.data || {}
      Object.keys(errors).forEach((key) => {
        fieldErrors[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
      })
      apiError.value = err.response.data?.message || 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.'
    } else if (err.response?.status === 403) {
      apiError.value = 'Bạn không có quyền thực hiện thao tác này. Chỉ Quản lý mới có quyền thêm phòng.'
    } else {
      apiError.value = err.response?.data?.message || 'Có lỗi xảy ra khi thêm phòng mới.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Submit cập nhật thông tin phòng (PUT /api/v1/rooms/{id})
const submitEditRoom = async () => {
  clearErrors()

  if (!editForm.tenPhong) {
    fieldErrors.tenPhong = 'Vui lòng nhập tên / số phòng.'
    return
  }
  if (editForm.tenPhong.length > 20) {
    fieldErrors.tenPhong = 'Tên phòng không được vượt quá 20 ký tự.'
    return
  }
  if (!editForm.maLoaiPhong) {
    fieldErrors.maLoaiPhong = 'Vui lòng chọn loại phòng.'
    return
  }
  if (!editForm.tang || editForm.tang < 1) {
    fieldErrors.tang = 'Tầng phải là số nguyên tối thiểu là 1.'
    return
  }

  isSubmitting.value = true
  const token = localStorage.getItem('access_token')

  try {
    const payload = {
      tenPhong: editForm.tenPhong,
      maLoaiPhong: Number(editForm.maLoaiPhong),
      tang: Number(editForm.tang),
      trangThai: editForm.trangThai,
    }

    const res = await axios.put(`/api/v1/rooms/${editForm.id}`, payload, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success) {
      showToast('success', 'Cập nhật thành công', `Thông tin phòng ${editForm.tenPhong} đã được lưu.`)
      closeEditModal()
      await fetchRooms()
    }
  } catch (err) {
    if (err.response?.status === 409) {
      apiError.value = err.response.data?.message || 'Không thể cập nhật trạng thái do xung đột lưu trú hoặc đơn đặt phòng.'
    } else if (err.response?.status === 422) {
      const errors = err.response.data?.errors || err.response.data?.data || {}
      Object.keys(errors).forEach((key) => {
        fieldErrors[key] = Array.isArray(errors[key]) ? errors[key][0] : errors[key]
      })
      apiError.value = err.response.data?.message || 'Dữ liệu không hợp lệ.'
    } else if (err.response?.status === 403) {
      apiError.value = 'Bạn không có quyền chỉnh sửa phòng.'
    } else {
      apiError.value = err.response?.data?.message || 'Có lỗi xảy ra khi cập nhật phòng.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Submit cập nhật riêng trạng thái vận hành (PUT /api/v1/rooms/{id}/status)
const submitStatusChange = async () => {
  clearErrors()

  if (!statusForm.room) return

  isSubmitting.value = true
  const token = localStorage.getItem('access_token')

  try {
    const payload = {
      trangThai: statusForm.trangThai,
    }

    const res = await axios.put(`/api/v1/rooms/${statusForm.room.id}/status`, payload, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success) {
      showToast(
        'success',
        'Đổi trạng thái thành công',
        `Phòng ${statusForm.room.tenPhong} đã được chuyển sang "${statusForm.trangThai}".`
      )
      closeStatusModal()
      await fetchRooms()
    }
  } catch (err) {
    if (err.response?.status === 409) {
      apiError.value = err.response.data?.message || 'Không thể chuyển trạng thái do phòng đang có khách hoặc đơn đặt phòng hiệu lực.'
    } else if (err.response?.status === 403) {
      apiError.value = 'Chỉ Quản lý mới có quyền thay đổi trạng thái vận hành phòng.'
    } else {
      apiError.value = err.response?.data?.message || 'Cập nhật trạng thái thất bại.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// Submit xóa phòng (DELETE /api/v1/rooms/{id})
const confirmDeleteRoom = async () => {
  if (!deleteTargetRoom.value) return

  isSubmitting.value = true
  deleteConflictError.value = ''
  const token = localStorage.getItem('access_token')

  try {
    const res = await axios.delete(`/api/v1/rooms/${deleteTargetRoom.value.id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success) {
      showToast('success', 'Xóa thành công', `Phòng ${deleteTargetRoom.value.tenPhong} đã được xóa khỏi hệ thống.`)
      closeDeleteModal()
      await fetchRooms()
    }
  } catch (err) {
    if (err.response?.status === 409) {
      deleteConflictError.value = err.response.data?.message || 'Phòng đã từng phát sinh giao dịch, không thể xóa.'
    } else if (err.response?.status === 403) {
      showToast('danger', 'Từ chối', 'Chỉ Quản lý mới có quyền xóa phòng.')
      closeDeleteModal()
    } else {
      showToast('danger', 'Lỗi', err.response?.data?.message || 'Không thể xóa phòng do lỗi hệ thống.')
      closeDeleteModal()
    }
  } finally {
    isSubmitting.value = false
  }
}

// Chuyển sang "Ngưng khai thác" ngay từ modal xóa khi nhận 409
const handleSwitchToRetiredFromDelete = async () => {
  if (!deleteTargetRoom.value) return

  isSubmitting.value = true
  const token = localStorage.getItem('access_token')

  try {
    const res = await axios.put(`/api/v1/rooms/${deleteTargetRoom.value.id}/status`, {
      trangThai: 'Ngưng khai thác',
    }, {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data?.success) {
      showToast('success', 'Chuyển trạng thái thành công', `Phòng ${deleteTargetRoom.value.tenPhong} đã được chuyển sang "Ngưng khai thác".`)
      closeDeleteModal()
      await fetchRooms()
    }
  } catch (err) {
    showToast('danger', 'Không thể chuyển trạng thái', err.response?.data?.message || 'Có lỗi xảy ra.')
  } finally {
    isSubmitting.value = false
  }
}

// 11. Toast Notifications Manager
const toasts = ref([])

const showToast = (type, title, message) => {
  const id = Date.now() + Math.random()
  const icon =
    type === 'success'
      ? 'bi bi-check-circle-fill'
      : type === 'danger'
      ? 'bi bi-x-circle-fill'
      : 'bi bi-info-circle-fill'

  toasts.value.push({ id, type, title, message, icon })

  setTimeout(() => {
    removeToast(id)
  }, 4500)
}

const removeToast = (id) => {
  toasts.value = toasts.value.filter((t) => t.id !== id)
}

// 12. Formatters & Badges
const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0)
}

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Sẵn sàng':
      return 'bg-success text-white'
    case 'Đang bảo trì':
      return 'bg-warning text-dark'
    case 'Ngưng khai thác':
      return 'bg-secondary text-white'
    case 'Đang ở':
    case 'Đang có khách':
      return 'bg-info text-white'
    default:
      return 'bg-light text-dark border'
  }
}

const getStatusIcon = (status) => {
  switch (status) {
    case 'Sẵn sàng':
      return 'bi bi-check-circle-fill'
    case 'Đang bảo trì':
      return 'bi bi-tools'
    case 'Ngưng khai thác':
      return 'bi bi-slash-circle-fill'
    case 'Đang ở':
    case 'Đang có khách':
      return 'bi bi-person-fill'
    default:
      return 'bi bi-circle'
  }
}

// 13. Khởi tạo
onMounted(async () => {
  loadCurrentUser()
  await fetchRooms()
  await fetchRoomTypes()
})
</script>

<style scoped>
.room-management-view {
  background-color: #f8fafc;
  min-height: calc(100vh - 120px);
  font-family: 'Inter', sans-serif;
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
  color: #ffffff;
}

.btn-navy:hover {
  background-color: #152943;
  border-color: #152943;
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

.room-badge {
  font-size: 1rem;
  letter-spacing: 0.5px;
}

.stat-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}

.stat-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
}

.cursor-pointer {
  cursor: pointer;
}

.border-active {
  border: 2px solid #0f766e !important;
}

.room-row:hover {
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
