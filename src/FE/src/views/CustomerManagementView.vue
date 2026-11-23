<template>
  <div class="customer-management-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/receptionist/front-desk" class="text-decoration-none text-teal">
              Lễ tân & Vận hành
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quản lý khách hàng (UC08)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-7">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-people-fill fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Quản Lý Hồ Sơ Khách Hàng</h2>
              <p class="text-muted mb-0 small">
                Tra cứu, thêm mới, cập nhật hồ sơ định danh và theo dõi lịch sử đặt phòng / lưu trú (Phân hệ PMS - UC08).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-5 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isAuthorized ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isAuthorized ? 'bi bi-shield-check me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Người dùng' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isAuthorized ? 'Có quyền quản lý, tạo, sửa và tra cứu lịch sử hồ sơ khách hàng' : 'Chỉ Lễ tân hoặc Quản lý mới có quyền truy cập dữ liệu khách hàng' }}
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
                @click="fetchCustomers"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
              <button
                type="button"
                class="btn btn-sm btn-teal text-white rounded-3 px-3 fw-semibold"
                :disabled="!isAuthorized"
                @click="openCreateModal"
              >
                <i class="bi bi-person-plus-fill me-1"></i> Thêm Khách Hàng
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Banner Thông báo Kết quả / Lỗi -->
      <div v-if="alertMessage" class="alert alert-dismissible fade show rounded-3 mb-4 shadow-sm border-0" :class="alertClass" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i :class="alertIcon" class="fs-5"></i>
          <div>{{ alertMessage }}</div>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="alertMessage = ''"></button>
      </div>

      <!-- Thống kê nhanh KPI Cards -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-navy-subtle text-navy rounded-3 p-3">
                <i class="bi bi-people fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Tổng khách hàng</small>
                <h4 class="fw-bold mb-0 text-navy">{{ statistics.total }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-teal-subtle text-teal rounded-3 p-3">
                <i class="bi bi-person-check-fill fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Đã có tài khoản</small>
                <h4 class="fw-bold mb-0 text-teal">{{ statistics.hasAccount }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-success-subtle text-success rounded-3 p-3">
                <i class="bi bi-shield-lock-fill fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Định danh CCCD</small>
                <h4 class="fw-bold mb-0 text-success">{{ statistics.hasCccd }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-primary-subtle text-primary rounded-3 p-3">
                <i class="bi bi-geo-alt-fill fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Có địa chỉ liên hệ</small>
                <h4 class="fw-bold mb-0 text-primary">{{ statistics.hasAddress }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Khối Lọc & Tìm kiếm -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm từ khóa -->
            <div class="col-12 col-md-8">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-search me-1"></i> Tra cứu thông tin khách hàng
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  v-model.trim="searchKeyword"
                  type="text"
                  class="form-control border-start-0 ps-0"
                  placeholder="Nhập họ tên, số CCCD/CMND, số điện thoại hoặc email..."
                  @keyup.enter="handleSearch"
                />
              </div>
            </div>

            <!-- Nút tìm kiếm & Xóa lọc -->
            <div class="col-12 col-md-4 d-flex gap-2">
              <button
                type="button"
                class="btn btn-navy flex-grow-1 fw-semibold rounded-3"
                :disabled="isLoading"
                @click="handleSearch"
              >
                <i class="bi bi-search me-1"></i> Tìm Kiếm
              </button>
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3"
                title="Xóa tìm kiếm"
                :disabled="isLoading"
                @click="resetSearch"
              >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Bảng Danh sách Khách hàng -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-navy mb-0">Danh Sách Khách Hàng</h5>
            <span class="badge bg-light text-navy border px-2.5 py-1 rounded-pill">
              {{ pagination.total }} hồ sơ
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <label class="small text-muted me-1 d-none d-sm-inline">Hiển thị:</label>
            <select v-model.number="pagination.perPage" class="form-select form-select-sm" style="width: auto;" @change="handleSearch">
              <option :value="10">10 / trang</option>
              <option :value="15">15 / trang</option>
              <option :value="25">25 / trang</option>
              <option :value="50">50 / trang</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 custom-customer-table">
            <thead class="bg-light">
              <tr class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                <th class="ps-4 py-3">Mã KH</th>
                <th class="py-3">Họ và tên</th>
                <th class="py-3">CCCD / Hộ chiếu</th>
                <th class="py-3">Số điện thoại</th>
                <th class="py-3">Email liên hệ</th>
                <th class="py-3">Địa chỉ thường trú</th>
                <th class="text-center py-3">Tài khoản</th>
                <th class="text-end pe-4 py-3">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <!-- Spinner khi tải dữ liệu -->
              <tr v-if="isLoading">
                <td colspan="8" class="text-center py-5">
                  <div class="spinner-border text-navy mb-2" role="status">
                    <span class="visually-hidden">Đang tải dữ liệu...</span>
                  </div>
                  <div class="text-muted small">Đang nạp danh sách khách hàng từ hệ thống...</div>
                </td>
              </tr>

              <!-- Khi không có dữ liệu -->
              <tr v-else-if="customers.length === 0">
                <td colspan="8" class="text-center py-5">
                  <div class="empty-state text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                    <p class="fw-semibold mb-1">Không tìm thấy khách hàng nào</p>
                    <small>Vui lòng thử tìm với từ khóa khác hoặc nhấn "Thêm Khách Hàng" mới.</small>
                  </div>
                </td>
              </tr>

              <!-- Dữ liệu khách hàng -->
              <tr v-for="c in customers" :key="c.id" class="transition-row">
                <td class="ps-4 fw-bold text-navy">
                  #KH-{{ String(c.id).padStart(4, '0') }}
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar-circle bg-navy-subtle text-navy fw-bold">
                      {{ getInitials(c.hoTen) }}
                    </div>
                    <div>
                      <div class="fw-bold text-navy">{{ c.hoTen }}</div>
                      <small class="text-muted" style="font-size: 0.75rem;">
                        Tham gia: {{ formatDate(c.created_at) }}
                      </small>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="font-monospace fw-semibold text-navy bg-light px-2 py-1 rounded border">
                    {{ c.cccd }}
                  </span>
                </td>
                <td>
                  <div class="fw-semibold text-teal">
                    <i class="bi bi-telephone me-1"></i>{{ c.soDienThoai }}
                  </div>
                </td>
                <td>
                  <div class="small text-muted">
                    <i class="bi bi-envelope me-1"></i>{{ c.email }}
                  </div>
                </td>
                <td>
                  <div class="small text-muted text-truncate" style="max-width: 220px;" :title="c.diaChi || 'Chưa cập nhật'">
                    {{ c.diaChi || 'Chưa cập nhật' }}
                  </div>
                </td>
                <td class="text-center">
                  <span
                    v-if="c.maTaiKhoan"
                    class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill"
                    title="Đã liên kết tài khoản"
                  >
                    <i class="bi bi-check-circle me-1"></i>Đã cấp
                  </span>
                  <span
                    v-else
                    class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill"
                    title="Khách vãng lai"
                  >
                    Vãng lai
                  </span>
                </td>
                <td class="text-end pe-4">
                  <div class="d-inline-flex gap-1.5">
                    <!-- Nút Xem Lịch sử lưu trú -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-teal rounded-3"
                      title="Xem lịch sử đặt phòng & lưu trú"
                      @click="openStayHistoryModal(c.id)"
                    >
                      <i class="bi bi-clock-history me-1"></i> Lịch sử
                    </button>

                    <!-- Nút Chỉnh sửa -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-navy rounded-3"
                      title="Chỉnh sửa thông tin khách hàng"
                      :disabled="!isAuthorized"
                      @click="openEditModal(c)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>

                    <!-- Nút Xóa -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger rounded-3"
                      title="Xóa hồ sơ khách hàng"
                      :disabled="!isAuthorized"
                      @click="openDeleteConfirmModal(c)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Phân trang Pagination -->
        <div v-if="pagination.lastPage > 1" class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
          <div class="small text-muted">
            Trang {{ pagination.currentPage }} / {{ pagination.lastPage }} (Tổng {{ pagination.total }} khách hàng)
          </div>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 }">
              <button class="page-link" @click="changePage(pagination.currentPage - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
            </li>
            <li
              v-for="page in displayedPages"
              :key="page"
              class="page-item"
              :class="{ active: pagination.currentPage === page }"
            >
              <button class="page-link" @click="changePage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage }">
              <button class="page-link" @click="changePage(pagination.currentPage + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 1: THÊM MỚI / CHỈNH SỬA HỒ SƠ KHÁCH HÀNG          -->
      <!-- ======================================================= -->
      <div v-if="showFormModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showFormModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i :class="isEditMode ? 'bi bi-pencil-square text-warning' : 'bi bi-person-plus-fill text-warning'" class="fs-4"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">
                    {{ isEditMode ? 'Chỉnh Sửa Hồ Sơ Khách Hàng' : 'Thêm Hồ Sơ Khách Hàng Mới' }}
                  </h5>
                  <small class="text-white-50">
                    {{ isEditMode ? `Cập nhật thông tin mã hồ sơ #KH-${String(currentEditingId).padStart(4, '0')}` : 'Tạo hồ sơ định danh và lưu trữ thông tin liên hệ' }}
                  </small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showFormModal = false"></button>
            </div>

            <form @submit.prevent="submitCustomerForm">
              <div class="modal-body p-4">
                <div class="row g-3">
                  <!-- Họ và tên -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-navy required-label">Họ và tên khách hàng</label>
                    <input
                      v-model.trim="formData.hoTen"
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': formErrors.hoTen }"
                      placeholder="Ví dụ: Nguyễn Văn Minh"
                      required
                    />
                    <div v-if="formErrors.hoTen" class="invalid-feedback">
                      {{ formErrors.hoTen[0] }}
                    </div>
                  </div>

                  <!-- CCCD / Hộ chiếu -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-navy required-label">Số CCCD / Hộ chiếu</label>
                    <input
                      v-model.trim="formData.cccd"
                      type="text"
                      class="form-control font-monospace"
                      :class="{ 'is-invalid': formErrors.cccd }"
                      placeholder="12 chữ số CCCD hoặc số hộ chiếu"
                      required
                    />
                    <div v-if="formErrors.cccd" class="invalid-feedback">
                      {{ formErrors.cccd[0] }}
                    </div>
                  </div>

                  <!-- Số điện thoại -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-navy required-label">Số điện thoại liên hệ</label>
                    <input
                      v-model.trim="formData.soDienThoai"
                      type="text"
                      class="form-control"
                      :class="{ 'is-invalid': formErrors.soDienThoai }"
                      placeholder="Ví dụ: 0901234567"
                      required
                    />
                    <div v-if="formErrors.soDienThoai" class="invalid-feedback">
                      {{ formErrors.soDienThoai[0] }}
                    </div>
                  </div>

                  <!-- Email -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-navy required-label">Địa chỉ Email</label>
                    <input
                      v-model.trim="formData.email"
                      type="email"
                      class="form-control"
                      :class="{ 'is-invalid': formErrors.email }"
                      placeholder="Ví dụ: khachhang@example.com"
                      required
                    />
                    <div v-if="formErrors.email" class="invalid-feedback">
                      {{ formErrors.email[0] }}
                    </div>
                  </div>

                  <!-- Địa chỉ thường trú -->
                  <div class="col-12">
                    <label class="form-label small fw-semibold text-navy">Địa chỉ thường trú / Nơi cư trú</label>
                    <textarea
                      v-model.trim="formData.diaChi"
                      rows="2"
                      class="form-control"
                      :class="{ 'is-invalid': formErrors.diaChi }"
                      placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                    ></textarea>
                    <div v-if="formErrors.diaChi" class="invalid-feedback">
                      {{ formErrors.diaChi[0] }}
                    </div>
                  </div>
                </div>

                <!-- Ghi chú tự động tạo tài khoản -->
                <div v-if="!isEditMode" class="mt-3 p-3 bg-light rounded-3 d-flex align-items-center gap-2 text-muted small">
                  <i class="bi bi-info-circle text-teal fs-5"></i>
                  <div>
                    Hệ thống sẽ <strong>tự động tạo tài khoản đăng nhập</strong> cho khách hàng này dựa trên số CCCD hoặc SĐT nếu chưa có tài khoản liên kết.
                  </div>
                </div>
              </div>

              <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="showFormModal = false">
                  Hủy Bỏ
                </button>
                <button
                  type="submit"
                  class="btn btn-navy rounded-3 px-4 fw-semibold"
                  :disabled="isSubmitting"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                  {{ isEditMode ? 'Cập Nhật Hồ Sơ' : 'Lưu Khách Hàng' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 2: XEM LỊCH SỬ ĐẶT PHÒNG & LƯU TRÚ (STAY HISTORY) -->
      <!-- ======================================================= -->
      <div v-if="showStayHistoryModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showStayHistoryModal = false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-teal text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">
                    Lịch Sử Đặt Phòng & Lưu Trú - {{ historyCustomerInfo?.hoTen || 'Khách hàng' }}
                  </h5>
                  <small class="text-white-50">
                    CCCD: {{ historyCustomerInfo?.cccd || 'N/A' }} | SĐT: {{ historyCustomerInfo?.soDienThoai || 'N/A' }}
                  </small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showStayHistoryModal = false"></button>
            </div>

            <div class="modal-body p-4">
              <!-- Loading -->
              <div v-if="isHistoryLoading" class="text-center py-5">
                <div class="spinner-border text-teal mb-2" role="status"></div>
                <div class="text-muted small">Đang tải lịch sử lưu trú của khách hàng...</div>
              </div>

              <!-- Nội dung lịch sử -->
              <div v-else>
                <!-- Thẻ tóm tắt thông tin hồ sơ -->
                <div class="card border-0 bg-light rounded-3 mb-4">
                  <div class="card-body p-3">
                    <div class="row g-3">
                      <div class="col-sm-3">
                        <small class="text-muted d-block">Họ và tên:</small>
                        <strong class="text-navy">{{ historyCustomerInfo?.hoTen }}</strong>
                      </div>
                      <div class="col-sm-3">
                        <small class="text-muted d-block">CCCD/Hộ chiếu:</small>
                        <strong class="text-navy font-monospace">{{ historyCustomerInfo?.cccd }}</strong>
                      </div>
                      <div class="col-sm-3">
                        <small class="text-muted d-block">Số điện thoại:</small>
                        <strong class="text-teal">{{ historyCustomerInfo?.soDienThoai }}</strong>
                      </div>
                      <div class="col-sm-3">
                        <small class="text-muted d-block">Địa chỉ:</small>
                        <span class="text-navy">{{ historyCustomerInfo?.diaChi || 'Chưa cập nhật' }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Danh sách các đơn đặt phòng -->
                <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                  <i class="bi bi-journal-bookmark-fill text-teal"></i>
                  Danh Sách Đơn Đặt Phòng Đã Phát Sinh ({{ stayHistoryList.length }} đơn)
                </h6>

                <div v-if="stayHistoryList.length === 0" class="text-center py-5 bg-light rounded-3 text-muted">
                  <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                  <p class="fw-semibold mb-1">Khách hàng chưa phát sinh đơn đặt phòng nào</p>
                  <small>Lịch sử lưu trú và hóa đơn sẽ xuất hiện tại đây khi khách hàng đặt phòng hoặc check-in.</small>
                </div>

                <div v-else class="d-flex flex-column gap-3">
                  <div
                    v-for="booking in stayHistoryList"
                    :key="booking.maDatPhong"
                    class="card border rounded-3 overflow-hidden shadow-sm"
                  >
                    <div class="card-header bg-white py-3 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-navy text-white px-2 py-1">#ĐP-{{ booking.maDatPhong }}</span>
                        <span class="small text-muted">Ngày đặt: {{ formatDate(booking.ngayDat) }}</span>
                        <span v-if="booking.nhanVien" class="small text-muted">
                          (Lễ tân tiếp nhận: <strong>{{ booking.nhanVien }}</strong>)
                        </span>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <span
                          class="badge rounded-pill px-3 py-1"
                          :class="getBookingStatusBadge(booking.trangThai)"
                        >
                          {{ booking.trangThai }}
                        </span>
                      </div>
                    </div>

                    <div class="card-body p-3">
                      <div class="row g-3 mb-3">
                        <div class="col-sm-3">
                          <small class="text-muted d-block">Nhận phòng dự kiến:</small>
                          <strong class="text-navy">{{ formatDate(booking.ngayNhanPhong) }}</strong>
                        </div>
                        <div class="col-sm-3">
                          <small class="text-muted d-block">Trả phòng dự kiến:</small>
                          <strong class="text-navy">{{ formatDate(booking.ngayTraPhong) }}</strong>
                        </div>
                        <div class="col-sm-3">
                          <small class="text-muted d-block">Số lượng khách:</small>
                          <strong class="text-navy">{{ booking.soLuongKhach }} người</strong>
                        </div>
                        <div class="col-sm-3">
                          <small class="text-muted d-block">Tiền cọc trước:</small>
                          <strong class="text-success">{{ formatCurrency(booking.tienCoc) }}</strong>
                        </div>
                      </div>

                      <!-- Chi tiết các phòng trong đơn -->
                      <h6 class="small fw-bold text-secondary text-uppercase mb-2">Phòng & Đợt Lưu Trú:</h6>
                      <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                          <thead class="table-light small">
                            <tr>
                              <th>Phòng</th>
                              <th>Loại phòng</th>
                              <th class="text-end">Giá áp dụng</th>
                              <th>Đợt lưu trú thực tế</th>
                              <th class="text-end">Hóa đơn đính kèm</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="rm in booking.danhSachPhong" :key="rm.maChiTiet" class="small">
                              <td class="fw-bold text-navy">
                                <i class="bi bi-door-closed me-1"></i>{{ rm.tenPhong || 'Chưa gán' }}
                              </td>
                              <td>{{ rm.tenLoaiPhong || 'Tiêu chuẩn' }}</td>
                              <td class="text-end fw-semibold">{{ formatCurrency(rm.giaPhongApDung) }}</td>
                              <td>
                                <div v-if="rm.luuTru">
                                  <span class="badge bg-light text-dark border me-1">{{ rm.luuTru.trangThai }}</span>
                                  <span class="text-muted">
                                    {{ formatDate(rm.luuTru.thoiGianNhan) }} - {{ formatDate(rm.luuTru.thoiGianTra) }}
                                  </span>
                                </div>
                                <span v-else class="text-muted italic">Chưa check-in</span>
                              </td>
                              <td class="text-end">
                                <div v-if="rm.luuTru?.hoaDon">
                                  <span class="fw-bold text-navy">#HD-{{ rm.luuTru.hoaDon.maHoaDon }}</span>:
                                  <span class="text-danger fw-semibold">{{ formatCurrency(rm.luuTru.hoaDon.tongTien) }}</span>
                                  <span
                                    class="badge ms-1"
                                    :class="rm.luuTru.hoaDon.trangThai === 'Đã thanh toán' ? 'bg-success' : 'bg-warning text-dark'"
                                  >
                                    {{ rm.luuTru.hoaDon.trangThai }}
                                  </span>
                                </div>
                                <span v-else class="text-muted">Chưa lập</span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-3 px-4 border-0">
              <button type="button" class="btn btn-secondary rounded-3 px-4" @click="showStayHistoryModal = false">
                Đóng
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 3: XÁC NHẬN XÓA HỒ SƠ KHÁCH HÀNG                  -->
      <!-- ======================================================= -->
      <div v-if="showDeleteModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showDeleteModal = false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <h5 class="modal-title fw-bold mb-0">Xác Nhận Xóa Hồ Sơ Khách Hàng</h5>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showDeleteModal = false"></button>
            </div>

            <div class="modal-body p-4">
              <p class="mb-3">
                Bạn có chắc chắn muốn xóa hồ sơ khách hàng <strong>{{ targetDeleteCustomer?.hoTen }}</strong>
                (CCCD: <span class="font-monospace">{{ targetDeleteCustomer?.cccd }}</span>) khỏi hệ thống?
              </p>

              <div class="alert alert-warning small rounded-3 mb-0">
                <i class="bi bi-shield-exclamation me-1 fw-bold"></i>
                <strong>Lưu ý BR02.6:</strong> Hệ thống sẽ từ chối xóa nếu khách hàng này đã từng phát sinh đơn đặt phòng, đợt lưu trú hoặc hóa đơn thanh toán nhằm bảo toàn lịch sử dữ liệu kế toán và kiểm toán.
              </div>
            </div>

            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="showDeleteModal = false">
                Hủy Bỏ
              </button>
              <button
                type="button"
                class="btn btn-danger rounded-3 px-4 fw-semibold"
                :disabled="isSubmitting"
                @click="confirmDeleteCustomer"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                Xóa Hồ Sơ
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

// 1. Quản lý trạng thái người dùng hiện tại
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

const isAuthorized = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'lễ tân' || role === 'quản lý' || role === 'admin'
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

// Chuyển đổi nhanh tài khoản kiểm thử
const quickSwitchUser = async (username, password, roleLabel) => {
  try {
    isLoading.value = true
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      password: password,
    })

    if (res.data?.success && res.data?.data?.access_token) {
      localStorage.setItem('access_token', res.data.data.access_token)
      localStorage.setItem('user', JSON.stringify(res.data.data.user || {}))
      loadCurrentUser()
      showAlert(`Đã chuyển sang tài khoản ${roleLabel} (${username}) thành công!`, 'success')
      fetchCustomers()
    }
  } catch (err) {
    showAlert(`Không thể chuyển sang tài khoản ${username}: ${err.response?.data?.message || err.message}`, 'danger')
  } finally {
    isLoading.value = false
  }
}

// 2. State danh sách khách hàng & tìm kiếm
const customers = ref([])
const isLoading = ref(false)
const searchKeyword = ref('')

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 15,
})

const statistics = reactive({
  total: 0,
  hasAccount: 0,
  hasCccd: 0,
  hasAddress: 0,
})

// Thông báo Banner Alert
const alertMessage = ref('')
const alertType = ref('success')

const alertClass = computed(() => {
  switch (alertType.value) {
    case 'success':
      return 'alert-success bg-success-subtle text-success'
    case 'danger':
      return 'alert-danger bg-danger-subtle text-danger'
    case 'warning':
      return 'alert-warning bg-warning-subtle text-warning'
    default:
      return 'alert-info bg-info-subtle text-info'
  }
})

const alertIcon = computed(() => {
  switch (alertType.value) {
    case 'success':
      return 'bi bi-check-circle-fill'
    case 'danger':
      return 'bi bi-exclamation-triangle-fill'
    case 'warning':
      return 'bi bi-exclamation-circle-fill'
    default:
      return 'bi bi-info-circle-fill'
  }
})

const showAlert = (message, type = 'success') => {
  alertMessage.value = message
  alertType.value = type
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const getAuthHeaders = () => {
  const token = localStorage.getItem('access_token')
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  }
}

// 3. API Fetch danh sách khách hàng
const fetchCustomers = async () => {
  isLoading.value = true
  try {
    const params = {
      page: pagination.currentPage,
      per_page: pagination.perPage,
    }

    if (searchKeyword.value) {
      params.keyword = searchKeyword.value
    }

    const response = await axios.get('/api/v1/customers', {
      ...getAuthHeaders(),
      params,
    })

    if (response.data?.success) {
      customers.value = response.data.data || []

      if (response.data.meta) {
        pagination.currentPage = response.data.meta.current_page || 1
        pagination.lastPage = response.data.meta.last_page || 1
        pagination.total = response.data.meta.total || customers.value.length
      } else {
        pagination.total = customers.value.length
      }

      calculateStatistics()
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách khách hàng:', error)
    if (error.response?.status === 401) {
      showAlert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'danger')
    } else if (error.response?.status === 403) {
      showAlert('Bạn không có quyền truy cập danh sách khách hàng. Chỉ Lễ tân hoặc Quản lý mới có quyền.', 'danger')
    } else {
      showAlert('Lỗi khi tải danh sách khách hàng: ' + (error.response?.data?.message || error.message), 'danger')
    }
  } finally {
    isLoading.value = false
  }
}

const calculateStatistics = () => {
  statistics.total = pagination.total || customers.value.length
  statistics.hasAccount = customers.value.filter(c => !!c.maTaiKhoan).length
  statistics.hasCccd = customers.value.filter(c => !!c.cccd && c.cccd.trim() !== '').length
  statistics.hasAddress = customers.value.filter(c => !!c.diaChi && c.diaChi !== 'Chưa cập nhật').length
}

const handleSearch = () => {
  pagination.currentPage = 1
  fetchCustomers()
}

const resetSearch = () => {
  searchKeyword.value = ''
  pagination.currentPage = 1
  fetchCustomers()
}

const changePage = (page) => {
  if (page < 1 || page > pagination.lastPage) return
  pagination.currentPage = page
  fetchCustomers()
}

const displayedPages = computed(() => {
  const current = pagination.currentPage
  const last = pagination.lastPage
  const delta = 2
  const left = Math.max(1, current - delta)
  const right = Math.min(last, current + delta)
  const pages = []
  for (let i = left; i <= right; i++) {
    pages.push(i)
  }
  return pages
})

// 4. MODAL THÊM / CHỈNH SỬA HỒ SƠ KHÁCH HÀNG
const showFormModal = ref(false)
const isEditMode = ref(false)
const currentEditingId = ref(null)
const isSubmitting = ref(false)
const formErrors = ref({})

const formData = reactive({
  hoTen: '',
  cccd: '',
  soDienThoai: '',
  email: '',
  diaChi: '',
})

const openCreateModal = () => {
  isEditMode.value = false
  currentEditingId.value = null
  formData.hoTen = ''
  formData.cccd = ''
  formData.soDienThoai = ''
  formData.email = ''
  formData.diaChi = ''
  formErrors.value = {}
  showFormModal.value = true
}

const openEditModal = (customer) => {
  isEditMode.value = true
  currentEditingId.value = customer.id
  formData.hoTen = customer.hoTen || ''
  formData.cccd = customer.cccd || ''
  formData.soDienThoai = customer.soDienThoai || ''
  formData.email = customer.email || ''
  formData.diaChi = customer.diaChi || ''
  formErrors.value = {}
  showFormModal.value = true
}

const submitCustomerForm = async () => {
  isSubmitting.value = true
  formErrors.value = {}

  try {
    const payload = {
      hoTen: formData.hoTen,
      cccd: formData.cccd,
      soDienThoai: formData.soDienThoai,
      email: formData.email,
      diaChi: formData.diaChi,
    }

    let res
    if (isEditMode.value) {
      res = await axios.put(`/api/v1/customers/${currentEditingId.value}`, payload, getAuthHeaders())
    } else {
      res = await axios.post('/api/v1/customers', payload, getAuthHeaders())
    }

    if (res.data?.success) {
      showAlert(
        isEditMode.value
          ? `Cập nhật hồ sơ khách hàng "${formData.hoTen}" thành công!`
          : `Thêm khách hàng mới "${formData.hoTen}" thành công!`,
        'success'
      )
      showFormModal.value = false
      fetchCustomers()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      formErrors.value = err.response.data?.errors || {}
      showAlert('Dữ liệu nhập chưa hợp lệ hoặc bị trùng lặp, vui lòng kiểm tra lại.', 'danger')
    } else if (err.response?.status === 403) {
      showAlert('Bạn không có quyền thực hiện thao tác này.', 'danger')
    } else {
      showAlert('Đã có lỗi xảy ra: ' + (err.response?.data?.message || err.message), 'danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// 5. MODAL XEM LỊCH SỬ ĐẶT PHÒNG & LƯU TRÚ (STAY HISTORY)
const showStayHistoryModal = ref(false)
const isHistoryLoading = ref(false)
const historyCustomerInfo = ref(null)
const stayHistoryList = ref([])

const openStayHistoryModal = async (customerId) => {
  showStayHistoryModal.value = true
  isHistoryLoading.value = true
  historyCustomerInfo.value = null
  stayHistoryList.value = []

  try {
    const res = await axios.get(`/api/v1/customers/${customerId}/stay-history`, getAuthHeaders())
    if (res.data?.success) {
      historyCustomerInfo.value = res.data.data?.khachHang || null
      stayHistoryList.value = res.data.data?.lichSuDatPhong || []
    }
  } catch (err) {
    showAlert('Không thể tải lịch sử lưu trú của khách hàng: ' + (err.response?.data?.message || err.message), 'danger')
    showStayHistoryModal.value = false
  } finally {
    isHistoryLoading.value = false
  }
}

// 6. MODAL XÁC NHẬN XÓA HỒ SƠ
const showDeleteModal = ref(false)
const targetDeleteCustomer = ref(null)

const openDeleteConfirmModal = (customer) => {
  targetDeleteCustomer.value = customer
  showDeleteModal.value = true
}

const confirmDeleteCustomer = async () => {
  if (!targetDeleteCustomer.value) return

  isSubmitting.value = true
  try {
    const res = await axios.delete(`/api/v1/customers/${targetDeleteCustomer.value.id}`, getAuthHeaders())
    if (res.data?.success) {
      showAlert(`Đã xóa hồ sơ khách hàng "${targetDeleteCustomer.value.hoTen}" thành công!`, 'success')
      showDeleteModal.value = false
      fetchCustomers()
    }
  } catch (err) {
    if (err.response?.status === 409) {
      showAlert(err.response.data?.message || 'Khách hàng đã từng phát sinh giao dịch lưu trú, không thể xóa.', 'danger')
    } else {
      showAlert('Lỗi khi xóa hồ sơ khách hàng: ' + (err.response?.data?.message || err.message), 'danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// 7. Formatters & UI Helpers
const getInitials = (name) => {
  if (!name) return 'KH'
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr
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

const getBookingStatusBadge = (status) => {
  switch (status) {
    case 'Đã xác nhận':
      return 'bg-success text-white'
    case 'Đang sử dụng':
      return 'bg-primary text-white'
    case 'Hoàn thành':
      return 'bg-teal text-white'
    case 'Đã hủy':
      return 'bg-danger text-white'
    default:
      return 'bg-warning text-dark'
  }
}

onMounted(() => {
  loadCurrentUser()
  fetchCustomers()
})
</script>

<style scoped>
/* Design Tokens: Navy #1E3A5F, Teal #0F766E, Background #F8FAFC, Font Inter */
.customer-management-view {
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
}

.text-navy {
  color: #1e3a5f !important;
}

.bg-navy {
  background-color: #1e3a5f !important;
}

.bg-navy-subtle {
  background-color: rgba(30, 58, 95, 0.1) !important;
}

.btn-navy {
  background-color: #1e3a5f;
  color: #ffffff;
  border: 1px solid #1e3a5f;
}

.btn-navy:hover {
  background-color: #162c47;
  color: #ffffff;
}

.btn-outline-navy {
  color: #1e3a5f;
  border-color: #1e3a5f;
  background-color: transparent;
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
  background-color: rgba(15, 118, 110, 0.1) !important;
}

.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
  color: #ffffff;
}

.btn-teal:hover {
  background-color: #0d5f58;
  color: #ffffff;
}

.btn-outline-teal {
  color: #0f766e;
  border-color: #0f766e;
  background-color: transparent;
}

.btn-outline-teal:hover {
  background-color: #0f766e;
  color: #ffffff;
}

.header-icon-box {
  width: 52px;
  height: 52px;
}

.kpi-icon-box {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  letter-spacing: 0.5px;
}

.custom-customer-table thead th {
  border-top: none;
  font-size: 0.78rem;
}

.custom-customer-table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

.custom-customer-table tbody tr:hover {
  background-color: #f1f5f9;
}

.required-label::after {
  content: ' *';
  color: #dc3545;
}

.spin-animation {
  animation: spin 1s infinite linear;
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
