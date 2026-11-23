<template>
  <div class="service-management-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- 1. Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/admin/dashboard" class="text-decoration-none text-teal">
              Quản trị PMS
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quản lý danh mục dịch vụ (UC17)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3">
        <div class="col-lg-8">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-bell-fill fs-3 text-warning"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Quản Lý Danh Mục Dịch Vụ</h2>
              <p class="text-muted mb-0 small">
                Cấu hình danh mục dịch vụ bổ sung, tra cứu, điều chỉnh đơn giá niêm yết và trạng thái cung cấp (Phân hệ PMS - UC17).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-4 text-lg-end">
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
              {{ isManager ? 'Toàn quyền cấu hình, thêm mới và điều chỉnh đơn giá' : 'Chỉ Quản lý mới có quyền chỉnh sửa/thêm dịch vụ' }}
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
                <i class="bi bi-person-badge me-1"></i> Lễ tân (Chỉ xem)
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
                @click="fetchServices"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
              <button
                type="button"
                class="btn btn-sm btn-teal text-white rounded-3 px-3 fw-semibold shadow-sm"
                :disabled="!isManager"
                @click="openCreateModal"
              >
                <i class="bi bi-plus-circle me-1"></i> Thêm Dịch Vụ Mới
              </button>
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

      <!-- Cảnh báo nếu không có quyền Quản lý -->
      <div
        v-if="!isManager && currentUser.tenDangNhap"
        class="alert alert-warning border-warning shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3 p-3"
        role="alert"
      >
        <i class="bi bi-shield-exclamation fs-3 text-warning flex-shrink-0"></i>
        <div class="flex-grow-1">
          <h6 class="fw-bold mb-1 text-warning-emphasis">Chế độ xem giới hạn (Không có quyền Quản lý)</h6>
          <p class="mb-0 small text-muted">
            Tài khoản của bạn hiện tại là <strong>{{ currentUser.vaiTro || 'Khách' }}</strong>. Bạn chỉ có thể xem các dịch vụ đang khả dụng và không được phép thêm mới, sửa giá hoặc ngừng cung cấp dịch vụ.
          </p>
        </div>
      </div>

      <!-- 4. Thẻ KPI Thống Kê Nhanh -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Tổng số dịch vụ</span>
                <h3 class="fw-bold text-navy mb-0 mt-1">{{ kpi.total }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Danh mục khách sạn</small>
              </div>
              <div class="kpi-icon bg-navy-subtle text-navy rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-collection-fill fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Đang cung cấp</span>
                <h3 class="fw-bold text-success mb-0 mt-1">{{ kpi.active }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Sẵn sàng phục vụ khách</small>
              </div>
              <div class="kpi-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-check-circle-fill fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Ngừng cung cấp</span>
                <h3 class="fw-bold text-danger mb-0 mt-1">{{ kpi.retired }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Tạm dừng / Hết mùa vụ</small>
              </div>
              <div class="kpi-icon bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-pause-circle-fill fs-4"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white kpi-card">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
              <div>
                <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Đơn giá cao nhất</span>
                <h3 class="fw-bold text-teal mb-0 mt-1">{{ formatCurrency(kpi.maxPrice) }}</h3>
                <small class="text-muted" style="font-size: 0.7rem;">Dịch vụ cao cấp nhất</small>
              </div>
              <div class="kpi-icon bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-cash-coin fs-4"></i>
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
            <div class="col-lg-5 col-md-6">
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  v-model.trim="filterParams.keyword"
                  type="text"
                  class="form-control border-start-0 ps-0"
                  placeholder="Tìm kiếm theo tên dịch vụ hoặc mô tả..."
                  @keyup.enter="handleSearch"
                />
                <button
                  v-if="filterParams.keyword"
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="filterParams.keyword = ''; handleSearch()"
                >
                  <i class="bi bi-x"></i>
                </button>
              </div>
            </div>

            <!-- Lọc theo trạng thái hoạt động -->
            <div class="col-lg-3 col-md-6">
              <select
                v-model="filterParams.trangThai"
                class="form-select form-select-sm"
                @change="handleSearch"
              >
                <option value="">Tất cả trạng thái</option>
                <option value="Đang cung cấp">Đang cung cấp</option>
                <option value="Ngừng cung cấp">Ngừng cung cấp</option>
              </select>
            </div>

            <!-- Nút Lọc & Đặt lại -->
            <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end gap-2">
              <button
                type="button"
                class="btn btn-sm btn-navy text-white rounded-3 px-3 fw-semibold"
                :disabled="isLoading"
                @click="handleSearch"
              >
                <i class="bi bi-funnel-fill me-1"></i> Tìm kiếm
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

      <!-- 6. Bảng Danh sách Dịch vụ -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-list-stars text-teal fs-5"></i>
            <h5 class="card-title fw-bold text-navy mb-0">Danh Mục Dịch Vụ Khách Sạn</h5>
            <span class="badge bg-light text-dark border ms-1">{{ services.length }} dịch vụ</span>
          </div>

          <div class="d-flex align-items-center gap-2 text-muted small">
            <i class="bi bi-shield-check text-success"></i>
            <span>Quy tắc BR04.5: Đơn giá cập nhật không làm thay đổi giá của các yêu cầu cũ</span>
          </div>
        </div>

        <!-- Trạng thái Loading -->
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border text-teal" role="status">
            <span class="visually-hidden">Đang tải dữ liệu...</span>
          </div>
          <p class="text-muted small mt-2 mb-0">Đang tải danh mục dịch vụ...</p>
        </div>

        <!-- Trạng thái Rỗng -->
        <div v-else-if="services.length === 0" class="text-center py-5">
          <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
          <h6 class="text-secondary fw-bold">Không tìm thấy dịch vụ nào</h6>
          <p class="text-muted small mb-3">Vui lòng thử tìm kiếm với từ khóa khác hoặc thêm dịch vụ mới.</p>
          <button
            v-if="isManager"
            type="button"
            class="btn btn-sm btn-teal text-white rounded-3 px-3"
            @click="openCreateModal"
          >
            <i class="bi bi-plus-circle me-1"></i> Thêm Dịch Vụ Mới
          </button>
        </div>

        <!-- Bảng Dữ Liệu -->
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-navy small text-uppercase">
              <tr>
                <th scope="col" class="ps-4" style="width: 100px;">Mã DV</th>
                <th scope="col" style="min-width: 200px;">Tên Dịch Vụ</th>
                <th scope="col" style="min-width: 250px;">Mô Tả Chi Tiết</th>
                <th scope="col" class="text-end" style="width: 160px;">Đơn Giá Niêm Yết</th>
                <th scope="col" class="text-center" style="width: 160px;">Trạng Thái</th>
                <th scope="col" class="text-end pe-4" style="width: 200px;">Thao Tác</th>
              </tr>
            </thead>
            <tbody class="border-top-0">
              <tr v-for="s in services" :key="s.id">
                <!-- Cột 1: Mã dịch vụ -->
                <td class="ps-4">
                  <span class="badge bg-navy text-white font-monospace px-2 py-1">
                    #DV-{{ s.id }}
                  </span>
                </td>

                <!-- Cột 2: Tên dịch vụ -->
                <td>
                  <div class="fw-bold text-navy">
                    <i class="bi bi-tag-fill me-1 text-teal"></i>{{ s.tenDichVu }}
                  </div>
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Ngày tạo: {{ formatDateShort(s.created_at) }}
                  </small>
                </td>

                <!-- Cột 3: Mô tả -->
                <td class="small text-secondary">
                  {{ s.moTa || 'Không có mô tả chi tiết' }}
                </td>

                <!-- Cột 4: Đơn giá -->
                <td class="text-end">
                  <strong class="text-teal fs-6">{{ formatCurrency(s.donGia) }}</strong>
                  <small class="d-block text-muted" style="font-size: 0.7rem;">/ lượt sử dụng</small>
                </td>

                <!-- Cột 5: Trạng thái -->
                <td class="text-center">
                  <span
                    class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    :class="s.trangThai === 'Đang cung cấp' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'"
                  >
                    <i :class="s.trangThai === 'Đang cung cấp' ? 'bi bi-check-circle-fill me-1' : 'bi bi-dash-circle-fill me-1'"></i>
                    {{ s.trangThai }}
                  </span>
                </td>

                <!-- Cột 6: Thao tác -->
                <td class="text-end pe-4">
                  <div class="d-inline-flex align-items-center gap-1.5">
                    <!-- Nút Chỉnh sửa -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-navy rounded-3 px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1"
                      :disabled="!isManager || isSubmitting"
                      title="Chỉnh sửa thông tin & đơn giá dịch vụ"
                      @click="openEditModal(s)"
                    >
                      <i class="bi bi-pencil-square"></i>
                      <span>Sửa</span>
                    </button>

                    <!-- Nút Đổi trạng thái nhanh -->
                    <button
                      v-if="s.trangThai === 'Đang cung cấp'"
                      type="button"
                      class="btn btn-sm btn-outline-warning text-dark rounded-3 px-2 py-1"
                      :disabled="!isManager || isSubmitting"
                      title="Chuyển sang Ngừng cung cấp"
                      @click="toggleServiceStatus(s, 'Ngừng cung cấp')"
                    >
                      <i class="bi bi-pause-fill"></i>
                      <span>Ngừng</span>
                    </button>

                    <button
                      v-else
                      type="button"
                      class="btn btn-sm btn-outline-success rounded-3 px-2 py-1"
                      :disabled="!isManager || isSubmitting"
                      title="Kích hoạt lại dịch vụ"
                      @click="toggleServiceStatus(s, 'Đang cung cấp')"
                    >
                      <i class="bi bi-play-fill"></i>
                      <span>Kích hoạt</span>
                    </button>

                    <!-- Nút Xóa an toàn -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"
                      :disabled="!isManager || isSubmitting"
                      title="Xóa dịch vụ (nếu chưa từng phát sinh yêu cầu)"
                      @click="openDeleteModal(s)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL 1: THÊM MỚI / CHỈNH SỬA DỊCH VỤ                                     -->
      <!-- ========================================================================= -->
      <div
        v-if="showFormModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px);"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i :class="isEditMode ? 'bi bi-pencil-square text-warning' : 'bi bi-plus-circle text-warning'" class="fs-4"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">
                    {{ isEditMode ? 'Chỉnh Sửa Thông Tin Dịch Vụ' : 'Thêm Dịch Vụ Mới Vào Danh Mục' }}
                  </h5>
                  <small class="text-white-50">
                    {{ isEditMode ? `#DV-${targetService?.id} - ${targetService?.tenDichVu}` : 'Nhập thông tin dịch vụ phục vụ khách lưu trú' }}
                  </small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="closeFormModal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <form @submit.prevent="submitForm">
                <!-- Tên dịch vụ -->
                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy">
                    Tên dịch vụ <span class="text-danger">*</span>
                  </label>
                  <input
                    v-model.trim="formData.tenDichVu"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': formErrors.tenDichVu }"
                    placeholder="Ví dụ: Giặt ủi cao cấp, Thuê xe tự lái..."
                    maxlength="100"
                    required
                  />
                  <div v-if="formErrors.tenDichVu" class="invalid-feedback">
                    {{ formErrors.tenDichVu[0] }}
                  </div>
                  <small class="text-muted" style="font-size: 0.75rem;">Tối đa 100 ký tự và không được trùng với dịch vụ khác.</small>
                </div>

                <!-- Đơn giá niêm yết -->
                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy">
                    Đơn giá niêm yết (VNĐ) <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <input
                      v-model.number="formData.donGia"
                      type="number"
                      class="form-control"
                      :class="{ 'is-invalid': formErrors.donGia }"
                      placeholder="Nhập số tiền VNĐ..."
                      min="1"
                      step="1000"
                      required
                    />
                    <span class="input-group-text bg-light fw-bold text-teal">VNĐ</span>
                    <div v-if="formErrors.donGia" class="invalid-feedback">
                      {{ formErrors.donGia[0] }}
                    </div>
                  </div>
                  <small class="text-teal fw-semibold mt-1 d-block" style="font-size: 0.8rem;">
                    Bằng chữ / Quy đổi: {{ formatCurrency(formData.donGia) }}
                  </small>
                </div>

                <!-- Trạng thái dịch vụ -->
                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy">
                    Trạng thái hoạt động <span class="text-danger">*</span>
                  </label>
                  <select
                    v-model="formData.trangThai"
                    class="form-select"
                    :class="{ 'is-invalid': formErrors.trangThai }"
                    required
                  >
                    <option value="Đang cung cấp">Đang cung cấp (Hiển thị cho khách & lễ tân)</option>
                    <option value="Ngừng cung cấp">Ngừng cung cấp (Tạm dừng dịch vụ)</option>
                  </select>
                  <div v-if="formErrors.trangThai" class="invalid-feedback">
                    {{ formErrors.trangThai[0] }}
                  </div>
                </div>

                <!-- Mô tả chi tiết -->
                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy">
                    Mô tả chi tiết dịch vụ <span class="text-danger">*</span>
                  </label>
                  <textarea
                    v-model.trim="formData.moTa"
                    rows="3"
                    class="form-control"
                    :class="{ 'is-invalid': formErrors.moTa }"
                    placeholder="Mô tả quy trình, thời gian cung cấp, vật dụng đi kèm..."
                    maxlength="100"
                    required
                  ></textarea>
                  <div v-if="formErrors.moTa" class="invalid-feedback">
                    {{ formErrors.moTa[0] }}
                  </div>
                  <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.75rem;">
                    <span>Tối đa 100 ký tự theo quy định CSDL</span>
                    <span>{{ (formData.moTa || '').length }}/100</span>
                  </div>
                </div>

                <!-- Chú thích bảo toàn lịch sử BR04.5 -->
                <div v-if="isEditMode" class="alert alert-info small rounded-3 mb-0 d-flex align-items-center gap-2">
                  <i class="bi bi-info-circle-fill fs-5 text-teal"></i>
                  <div>
                    <strong>Quy chuẩn BR04.5:</strong> Khi bạn thay đổi đơn giá dịch vụ này, mức giá mới chỉ áp dụng cho các yêu cầu phát sinh sau này. Toàn bộ các yêu cầu dịch vụ đã gửi trong quá khứ vẫn giữ nguyên đơn giá áp dụng ban đầu!
                  </div>
                </div>
              </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="closeFormModal">
                Hủy Bỏ
              </button>
              <button
                type="button"
                class="btn btn-teal text-white rounded-3 px-4 fw-semibold shadow-sm"
                :disabled="isSubmitting"
                @click="submitForm"
              >
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-check2-circle me-1"></i>
                {{ isEditMode ? 'Cập Nhật Dịch Vụ' : 'Lưu Dịch Vụ Mới' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ========================================================================= -->
      <!-- MODAL 2: XÁC NHẬN XÓA / BẢO TOÀN DỮ LIỆU LỊCH SỬ                          -->
      <!-- ========================================================================= -->
      <div
        v-if="showDeleteModal"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(2px);"
      >
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-danger text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <h5 class="modal-title fw-bold mb-0">Xác Nhận Xóa Dịch Vụ</h5>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="closeDeleteModal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <p class="mb-3">
                Bạn có chắc chắn muốn xóa dịch vụ <strong>{{ targetService?.tenDichVu }}</strong> (#DV-{{ targetService?.id }}) khỏi danh mục hệ thống?
              </p>

              <div class="alert alert-warning small rounded-3 mb-0">
                <i class="bi bi-shield-exclamation me-1 fw-bold"></i>
                <strong>Quy chuẩn bảo toàn kế toán (BR04.5):</strong> Hệ thống sẽ từ chối xóa nếu dịch vụ này đã từng được khách hàng đăng ký sử dụng trong bất kỳ đợt lưu trú nào. Trong trường hợp đó, bạn có thể chuyển trạng thái sang <strong>"Ngừng cung cấp"</strong>.
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-between">
              <!-- Nút chuyển sang Ngừng cung cấp nếu không muốn xóa -->
              <button
                type="button"
                class="btn btn-outline-warning text-dark rounded-3 px-3"
                :disabled="isSubmitting"
                @click="retireInsteadOfDelete"
              >
                <i class="bi bi-pause-circle me-1"></i> Chuyển sang "Ngừng cung cấp"
              </button>

              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="closeDeleteModal">
                  Hủy Bỏ
                </button>
                <button
                  type="button"
                  class="btn btn-danger rounded-3 px-4 fw-semibold shadow-sm"
                  :disabled="isSubmitting"
                  @click="submitDelete"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                  <i v-else class="bi bi-trash me-1"></i> Xóa Vĩnh Viễn
                </button>
              </div>
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
// 1. Quản lý Tài khoản & Phân quyền
// =========================================================================
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

const isManager = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'quản lý' || role === 'admin'
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
        `Đã đăng nhập thành công với vai trò [${roleLabel} - ${username}].`,
        'alert-success'
      )
      await fetchServices()
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
const services = ref([])

const filterParams = reactive({
  keyword: '',
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
// 3. API Tra Cứu Danh Mục Dịch Vụ
// =========================================================================
const fetchServices = async () => {
  isLoading.value = true
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')
    const params = {}
    if (filterParams.keyword) params.keyword = filterParams.keyword
    if (filterParams.trangThai) params.trangThai = filterParams.trangThai

    const res = await axios.get('/api/v1/services', {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      params: params,
    })

    if (res.data?.success) {
      services.value = res.data.data || []
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || err.message

    if (status === 401) {
      showAlert('Chưa đăng nhập', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.', 'alert-danger')
    } else if (status === 403) {
      showAlert('Từ chối truy cập (403)', 'Tài khoản của bạn không có quyền xem danh mục dịch vụ.', 'alert-danger')
      services.value = []
    } else {
      showAlert('Lỗi nạp dữ liệu', msg || 'Không thể tải danh sách dịch vụ.', 'alert-danger')
    }
  } finally {
    isLoading.value = false
  }
}

const handleSearch = () => {
  fetchServices()
}

const resetFilter = () => {
  filterParams.keyword = ''
  filterParams.trangThai = ''
  fetchServices()
}

// KPI Thống kê
const kpi = computed(() => {
  let active = 0
  let retired = 0
  let maxPrice = 0

  services.value.forEach((s) => {
    if (s.trangThai === 'Đang cung cấp') {
      active++
    } else if (s.trangThai === 'Ngừng cung cấp') {
      retired++
    }
    if (Number(s.donGia) > maxPrice) {
      maxPrice = Number(s.donGia)
    }
  })

  return {
    total: services.value.length,
    active,
    retired,
    maxPrice,
  }
})

// =========================================================================
// 4. Modal 1: Thêm Mới / Chỉnh Sửa Dịch Vụ
// =========================================================================
const showFormModal = ref(false)
const isEditMode = ref(false)
const targetService = ref(null)

const formData = reactive({
  tenDichVu: '',
  moTa: '',
  donGia: 50000,
  trangThai: 'Đang cung cấp',
})

const formErrors = reactive({})

const openCreateModal = () => {
  isEditMode.value = false
  targetService.value = null
  formData.tenDichVu = ''
  formData.moTa = ''
  formData.donGia = 50000
  formData.trangThai = 'Đang cung cấp'
  Object.keys(formErrors).forEach((key) => delete formErrors[key])
  showFormModal.value = true
}

const openEditModal = (service) => {
  isEditMode.value = true
  targetService.value = service
  formData.tenDichVu = service.tenDichVu
  formData.moTa = service.moTa
  formData.donGia = service.donGia
  formData.trangThai = service.trangThai
  Object.keys(formErrors).forEach((key) => delete formErrors[key])
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  targetService.value = null
}

const submitForm = async () => {
  isSubmitting.value = true
  Object.keys(formErrors).forEach((key) => delete formErrors[key])
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')
    const payload = {
      tenDichVu: formData.tenDichVu,
      moTa: formData.moTa,
      donGia: Number(formData.donGia),
      trangThai: formData.trangThai,
    }

    let res
    if (isEditMode.value && targetService.value) {
      res = await axios.put(`/api/v1/services/${targetService.value.id}`, payload, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
    } else {
      res = await axios.post('/api/v1/services', payload, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
    }

    if (res.data?.success) {
      showAlert(
        isEditMode.value ? 'Cập nhật thành công' : 'Thêm mới thành công',
        res.data.message || 'Dữ liệu dịch vụ đã được cập nhật thành công vào hệ thống.',
        'alert-success'
      )
      closeFormModal()
      await fetchServices()
    }
  } catch (err) {
    const status = err.response?.status
    const data = err.response?.data
    if (status === 422) {
      if (data.errors) {
        Object.assign(formErrors, data.errors)
      }
      showAlert('Dữ liệu không hợp lệ (422)', data.message || 'Vui lòng kiểm tra lại các trường thông tin.', 'alert-danger')
    } else if (status === 403) {
      showAlert('Không có quyền (403)', 'Chỉ Quản lý mới có quyền cập nhật danh mục dịch vụ.', 'alert-danger')
    } else {
      showAlert('Lỗi xử lý', data?.message || err.message, 'alert-danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// =========================================================================
// 5. Cập Nhật Nhanh Trạng Thái (Đang cung cấp / Ngừng cung cấp)
// =========================================================================
const toggleServiceStatus = async (service, newStatus) => {
  isSubmitting.value = true
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.put(
      `/api/v1/services/${service.id}/status`,
      { trangThai: newStatus },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    )

    if (res.data?.success) {
      showAlert(
        'Đổi trạng thái thành công',
        `Dịch vụ [${service.tenDichVu}] đã chuyển sang trạng thái "${newStatus}".`,
        'alert-success'
      )
      await fetchServices()
    }
  } catch (err) {
    showAlert('Lỗi đổi trạng thái', err.response?.data?.message || err.message, 'alert-danger')
  } finally {
    isSubmitting.value = false
  }
}

// =========================================================================
// 6. Modal 2: Xóa Dịch Vụ & Bảo Toàn Lịch Sử BR04.5
// =========================================================================
const showDeleteModal = ref(false)

const openDeleteModal = (service) => {
  targetService.value = service
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  targetService.value = null
}

const retireInsteadOfDelete = async () => {
  if (!targetService.value) return
  await toggleServiceStatus(targetService.value, 'Ngừng cung cấp')
  closeDeleteModal()
}

const submitDelete = async () => {
  if (!targetService.value) return

  isSubmitting.value = true
  clearAlert()

  try {
    const token = localStorage.getItem('access_token')
    const res = await axios.delete(`/api/v1/services/${targetService.value.id}`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    })

    if (res.data?.success) {
      showAlert(
        'Xóa thành công',
        `Dịch vụ [${targetService.value.tenDichVu}] đã được xóa khỏi hệ thống.`,
        'alert-success'
      )
      closeDeleteModal()
      await fetchServices()
    }
  } catch (err) {
    const status = err.response?.status
    const msg = err.response?.data?.message || err.message

    if (status === 409) {
      showAlert(
        'Ràng buộc lịch sử (409 Conflict)',
        msg || 'Dịch vụ đã phát sinh giao dịch lịch sử, không thể xóa cứng. Bạn có thể chọn "Ngừng cung cấp".',
        'alert-warning'
      )
    } else if (status === 403) {
      showAlert('Không có quyền (403)', 'Chỉ Quản lý mới có quyền xóa dịch vụ.', 'alert-danger')
    } else {
      showAlert('Lỗi xóa dịch vụ', msg, 'alert-danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// =========================================================================
// 7. Helpers: Format Tiền tệ, Ngày tháng
// =========================================================================
const formatCurrency = (val) => {
  if (val === null || val === undefined || isNaN(val)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val)
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

// Lifecycle
onMounted(async () => {
  loadCurrentUser()
  await fetchServices()
})
</script>

<style scoped>
/* Design Tokens: Navy #1E3A5F, Teal #0F766E, Background #F8FAFC, Font Inter */
.service-management-view {
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
