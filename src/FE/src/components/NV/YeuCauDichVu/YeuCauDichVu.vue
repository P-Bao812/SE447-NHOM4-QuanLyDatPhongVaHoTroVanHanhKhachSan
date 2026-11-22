<template>
  <div class="yeu-cau-dich-vu-page py-4">
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

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: QUAY LẠI, BREADCRUMB, THỜI GIAN, CHUÔNG THÔNG BÁO & GIẢ LẬP VAI TRÒ -->
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
              <li class="breadcrumb-item active text-teal fw-bold" aria-current="page">Quản lý yêu cầu dịch vụ</li>
            </ol>
          </nav>
        </div>

        <!-- Bên phải: Ngày giờ thực tế, Chuông thông báo (kèm badge & dropdown) & User profile -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1 text-teal"></i>
            {{ ngayHienTai }}
          </span>

          <!-- DROPDOWN CHUÔNG THÔNG BÁO NỘI BỘ (HỖ TRỢ THÔNG BÁO REAL-TIME) -->
          <div class="dropdown">
            <button
              class="btn btn-light btn-sm rounded-circle border position-relative p-2"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              title="Thông báo hệ thống"
              @click="fetchNotifications"
            >
              <i class="bi bi-bell text-secondary"></i>
              <span
                v-if="unreadNotifyCount > 0"
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                style="font-size: 0.65rem;"
              >
                {{ unreadNotifyCount }}
              </span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-0 notify-dropdown" style="width: 340px; max-height: 400px; overflow-y: auto;">
              <li class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                <span class="fw-bold small text-dark">Thông báo nội bộ</span>
                <button
                  type="button"
                  class="btn btn-link btn-sm text-teal text-decoration-none p-0 small"
                  @click="markAllNotificationsRead"
                >
                  Đọc tất cả
                </button>
              </li>

              <li v-if="notifications.length === 0" class="p-4 text-center text-muted small">
                Chưa có thông báo mới nào.
              </li>

              <li
                v-for="nb in notifications"
                :key="nb.id"
                class="p-2.5 border-bottom notify-item cursor-pointer"
                :class="{ 'bg-teal-subtle-soft': nb.trangThai === 'Chua_Xem' }"
              >
                <div class="d-flex align-items-start gap-2">
                  <div class="notify-icon-circle rounded-circle bg-teal text-white flex-shrink-0 d-flex align-items-center justify-content-center">
                    <i class="bi" :class="getNotifyIcon(nb.loai)"></i>
                  </div>
                  <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;">{{ nb.tieuDe }}</div>
                    <div class="text-muted small line-clamp-2" style="font-size: 0.75rem;">{{ nb.noiDung }}</div>
                    <div class="text-secondary mt-1" style="font-size: 0.68rem;">{{ nb.thoiGian }}</div>
                  </div>
                </div>
              </li>
            </ul>
          </div>

          <!-- Avatar & Quyền Admin / Quản lý duy nhất của hệ thống -->
          <div class="d-flex align-items-center gap-2 py-1.5 px-3 bg-white border rounded-pill shadow-xs">
            <div
              class="rounded-circle fw-bold d-flex align-items-center justify-content-center text-white"
              style="width: 32px; height: 32px; background-color: #1E5E49; font-size: 0.8rem;"
            >
              AD
            </div>
            <div class="text-start me-1" style="line-height: 1.25;">
              <div class="fw-bold text-dark" style="font-size: 0.82rem;">Trần An</div>
              <div class="text-muted" style="font-size: 0.72rem;">Admin</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ, MÔ TẢ & NÚT TẠO YÊU CẦU -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
          <h2 class="fw-bold text-main-dark mb-1">
            Quản lý yêu cầu dịch vụ
          </h2>
          <p class="text-muted mb-0 small">
            Tiếp nhận, phân công và theo dõi từng yêu cầu gắn với khách lưu trú.
          </p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <button
            type="button"
            class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm d-inline-flex align-items-center gap-1.5"
            @click="refreshData"
            :disabled="loading"
            title="Làm mới dữ liệu từ Database"
          >
            <i class="bi bi-arrow-clockwise" :class="{ 'spin-infinite': loading }"></i>
            <span class="d-none d-sm-inline">Làm mới</span>
          </button>

          <button
            type="button"
            class="btn btn-teal-primary rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-xs"
            @click="showCreateModal = true"
          >
            <i class="bi bi-plus-lg"></i>
            <span class="fw-medium">Tạo yêu cầu</span>
          </button>
        </div>
      </div>

      <!-- TABS CHUYỂN ĐỔI: TẤT CẢ, CHỜ TIẾP NHẬN, ĐANG XỬ LÝ, ĐÃ HOÀN TẤT, ĐÃ TỪ CHỐI -->
      <div class="d-flex align-items-center gap-2 border-bottom mb-4 overflow-x-auto">
        <button
          type="button"
          class="btn-tab-nav"
          :class="{ active: currentTab === 'all' }"
          @click="changeTab('all')"
        >
          <span>Tất cả</span>
          <span class="tab-count-badge ms-1">({{ tabCounts.all }})</span>
        </button>

        <button
          type="button"
          class="btn-tab-nav"
          :class="{ active: currentTab === 'cho_tiep_nhan' }"
          @click="changeTab('cho_tiep_nhan')"
        >
          <span>Chờ tiếp nhận</span>
          <span class="tab-count-badge ms-1">({{ tabCounts.choTiepNhan }})</span>
        </button>

        <button
          type="button"
          class="btn-tab-nav"
          :class="{ active: currentTab === 'dang_xu_ly' }"
          @click="changeTab('dang_xu_ly')"
        >
          <span>Đang xử lý</span>
          <span class="tab-count-badge ms-1">({{ tabCounts.dangXuLy }})</span>
        </button>

        <button
          type="button"
          class="btn-tab-nav"
          :class="{ active: currentTab === 'hoan_tat' }"
          @click="changeTab('hoan_tat')"
        >
          <span>Đã hoàn tất</span>
          <span class="tab-count-badge ms-1">({{ tabCounts.hoanTat }})</span>
        </button>

        <button
          type="button"
          class="btn-tab-nav"
          :class="{ active: currentTab === 'tu_choi' }"
          @click="changeTab('tu_choi')"
        >
          <span>Đã từ chối</span>
          <span class="tab-count-badge ms-1">({{ tabCounts.tuChoi }})</span>
        </button>
      </div>

      <!-- THANH TÌM KIẾM VÀ BỘ LỌC ĐA NĂNG -->
      <div class="filter-card p-3 rounded-4 mb-4 bg-white border shadow-xs">
        <div class="row g-2 align-items-center">
          <!-- Ô tìm kiếm -->
          <div class="col-12 col-md-5">
            <div class="position-relative">
              <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
              <input
                v-model="filters.q"
                type="text"
                class="form-control form-control-sm ps-5 rounded-3 border"
                placeholder="Mã yêu cầu, khách hàng hoặc số phòng"
                @input="handleSearchInput"
              />
              <button
                v-if="filters.q"
                class="btn btn-sm btn-clear-search position-absolute top-50 end-0 translate-middle-y me-2"
                @click="filters.q = ''; fetchRequests()"
              >
                <i class="bi bi-x-circle-fill text-muted"></i>
              </button>
            </div>
          </div>

          <!-- Lọc ngày -->
          <div class="col-6 col-md-2">
            <input
              v-model="filters.ngay"
              type="date"
              class="form-control form-control-sm rounded-3 border"
              @change="fetchRequests"
            />
          </div>

          <!-- Lọc mức độ ưu tiên -->
          <div class="col-6 col-md-2">
            <select
              v-model="filters.mucDoUuTien"
              class="form-select form-select-sm rounded-3 border"
              @change="fetchRequests"
            >
              <option value="Tất cả">Mọi ưu tiên</option>
              <option value="Bình thường">Bình thường</option>
              <option value="Cao">Cao</option>
              <option value="Khẩn cấp">Khẩn cấp</option>
            </select>
          </div>

          <!-- Lọc người phụ trách -->
          <div class="col-12 col-md-3">
            <select
              v-model="filters.maNhanVien"
              class="form-select form-select-sm rounded-3 border"
              @change="fetchRequests"
            >
              <option value="Tất cả">Mọi người phụ trách</option>
              <option value="chua_phan_cong">Chưa phân công</option>
              <option v-for="nv in staffOptions" :key="nv.id" :value="nv.id">
                {{ nv.hoTen }} ({{ nv.boPhan }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- BỐ CỤC 2 CỘT CHÍNH -->
      <div class="row g-4">
        <!-- ==================== CỘT TRÁI: DANH SÁCH YÊU CẦU ("YÊU CẦU HÔM NAY") ==================== -->
        <div class="col-12 col-xl-7 col-xxl-8">
          <div class="card border-0 rounded-4 shadow-xs overflow-hidden">
            <div class="card-header bg-white border-0 pt-3 px-3 pb-2">
              <h5 class="fw-bold text-main-dark mb-0">Yêu cầu hôm nay</h5>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 custom-request-table">
                <thead class="text-muted small text-uppercase" style="background-color: #F8FAF9; font-size: 0.75rem;">
                  <tr>
                    <th scope="col" class="ps-3 py-2.5">YÊU CẦU / KHÁCH</th>
                    <th scope="col" class="py-2.5">DỊCH VỤ / GIÁ</th>
                    <th scope="col" class="py-2.5">PHỤ TRÁCH</th>
                    <th scope="col" class="pe-3 py-2.5">TIẾN ĐỘ</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- LOADING -->
                  <tr v-if="loading">
                    <td colspan="4" class="text-center py-5">
                      <div class="spinner-border text-teal spinner-border-sm" role="status"></div>
                      <p class="text-muted small mt-2 mb-0">Đang tải danh sách yêu cầu...</p>
                    </td>
                  </tr>

                  <!-- EMPTY STATE -->
                  <tr v-else-if="requests.length === 0">
                    <td colspan="4" class="text-center py-5">
                      <div class="empty-state text-muted py-3">
                        <i class="bi bi-inbox display-6 text-muted mb-2"></i>
                        <p class="mb-1 fw-medium">Không có yêu cầu dịch vụ nào phù hợp.</p>
                        <p class="small text-muted mb-0">Thử thay đổi bộ lọc hoặc tạo yêu cầu mới.</p>
                      </div>
                    </td>
                  </tr>

                  <!-- DANH SÁCH CÁC HÀNG -->
                  <tr
                    v-for="item in requests"
                    :key="item.id"
                    class="request-table-row cursor-pointer transition-all"
                    :class="{ 'row-active': selectedRequest && selectedRequest.id === item.id }"
                    @click="chonYeuCau(item)"
                  >
                    <!-- CỘT 1: YÊU CẦU / KHÁCH -->
                    <td class="ps-3 py-3">
                      <div class="d-flex flex-column">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                          <span class="code-room-badge fw-bold" :class="{ 'highlight-badge': selectedRequest && selectedRequest.id === item.id }">
                            {{ item.maYeuCauCode }} · {{ item.soPhongDisplay }}
                          </span>
                        </div>
                        <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">
                          {{ item.tenKhachHang }}
                        </div>
                      </div>
                    </td>

                    <!-- CỘT 2: DỊCH VỤ / GIÁ -->
                    <td class="py-3">
                      <div class="d-flex flex-column">
                        <div class="text-dark fw-medium" style="font-size: 0.88rem;">
                          {{ item.tenDichVu }} · {{ item.soLuong }} {{ item.donViTinh }}
                        </div>
                        <div class="text-muted small mt-0.5">
                          {{ formatCurrency(item.thanhTien) }}
                        </div>
                      </div>
                    </td>

                    <!-- CỘT 3: PHỤ TRÁCH -->
                    <td class="py-3">
                      <div class="d-flex flex-column">
                        <div class="text-dark fw-medium" style="font-size: 0.88rem;">
                          {{ item.tenNhanVien }}
                        </div>
                        <div class="small mt-0.5" :class="getPriorityClass(item.mucDoUuTien)">
                          {{ item.mucDoUuTien }}
                        </div>
                      </div>
                    </td>

                    <!-- CỘT 4: TIẾN ĐỘ -->
                    <td class="pe-3 py-3">
                      <div class="d-flex flex-column">
                        <div class="d-flex align-items-center gap-1.5">
                          <span class="status-indicator-dot" :class="getStatusDotClass(item.trangThai)"></span>
                          <span class="fw-semibold text-dark" style="font-size: 0.85rem;">
                            {{ item.trangThaiLabel }}
                          </span>
                        </div>
                        <div class="text-muted small mt-0.5" style="font-size: 0.78rem;">
                          {{ item.tienDoText }}
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- FOOTER DƯỚI BẢNG -->
            <div class="card-footer bg-white border-top p-3 small text-muted">
              <span>Chỉ yêu cầu đã hoàn tất được ghi phí vào hóa đơn. Đơn giá đã gồm thuế, phí.</span>
            </div>
          </div>
        </div>

        <!-- ==================== CỘT PHẢI: CHI TIẾT YÊU CẦU ==================== -->
        <div class="col-12 col-xl-5 col-xxl-4">
          <div class="detail-panel-card card border-0 rounded-4 shadow-xs position-sticky" style="top: 1.5rem;">
            <!-- TRƯỜNG HỢP CÓ YÊU CẦU ĐƯỢC CHỌN -->
            <div v-if="selectedRequest" class="card-body p-4 animate-fade-in">
              <!-- TIÊU ĐỀ YÊU CẦU -->
              <h5 class="fw-bold text-main-dark mb-2">
                {{ selectedRequest.maYeuCauCode }} · {{ selectedRequest.tenDichVu }}
              </h5>

              <!-- BADGE TRẠNG THÁI -->
              <div class="mb-3">
                <span class="badge-status-tag" :class="getStatusTagClass(selectedRequest.trangThai)">
                  <span class="status-dot"></span>
                  <span>{{ getFullStatusTagLabel(selectedRequest) }}</span>
                </span>
              </div>

              <!-- LƯỚI THÔNG TIN CHI TIẾT -->
              <div class="detail-info-list d-flex flex-column gap-2.5 pb-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="text-muted small">Khách / Phòng</span>
                  <strong class="text-dark small">{{ selectedRequest.tenKhachHang }} · {{ selectedRequest.tenPhong }}</strong>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                  <span class="text-muted small">Kỳ lưu trú</span>
                  <span class="text-dark small">{{ selectedRequest.kyLuuTru }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                  <span class="text-muted small">Số lượng × đơn giá</span>
                  <span class="text-dark small">{{ selectedRequest.soLuong }} {{ selectedRequest.donViTinh }} × {{ formatCurrency(selectedRequest.donGia) }}</span>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                  <span class="text-muted small">Tổng dự kiến</span>
                  <strong class="text-teal fs-6">{{ formatCurrency(selectedRequest.thanhTien) }}</strong>
                </div>

                <!-- ĐỔI NGƯỜI PHỤ TRÁCH -->
                <div class="mt-2">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label text-muted small fw-semibold mb-0">Người phụ trách</label>
                    <span v-if="selectedRequest.tenNhanVien && selectedRequest.tenNhanVien !== 'Chưa phân công'" class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.72rem;">
                      <i class="bi bi-check-circle-fill me-1"></i>Đã phân công
                    </span>
                    <span v-else class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">
                      Chưa phân công
                    </span>
                  </div>
                  <div class="d-flex align-items-center gap-1.5">
                    <select
                      v-model="editForm.maNhanVien"
                      class="form-select form-select-sm rounded-3 flex-grow-1"
                      @change="handleUpdateAssignee"
                    >
                      <option value="">Chưa phân công</option>
                      <optgroup :label="'★ Đúng chuyên môn (' + (selectedRequest.boPhanPhuTrach || 'Phù hợp') + ')'">
                        <option v-for="nv in matchingStaff" :key="nv.id" :value="nv.id">
                          {{ nv.hoTen }} · {{ nv.chucVu }} ({{ nv.caLam || 'Ca sáng' }})
                        </option>
                      </optgroup>
                      <optgroup v-if="otherStaff.length > 0" label="Nhân viên bộ phận khác (Cảnh báo sai bộ phận)">
                        <option v-for="nv in otherStaff" :key="nv.id" :value="nv.id">
                          {{ nv.hoTen }} · {{ nv.boPhan }}
                        </option>
                      </optgroup>
                    </select>
                    <button
                      type="button"
                      class="btn btn-sm btn-teal-primary rounded-3 px-2.5 py-1 d-inline-flex align-items-center gap-1 flex-shrink-0"
                      @click="handleUpdateAssignee"
                      :disabled="actionLoading"
                      title="Lưu người phụ trách"
                    >
                      <i class="bi bi-check-lg"></i>
                      <span>Lưu</span>
                    </button>
                  </div>
                </div>

                <!-- ĐỔI ƯU TIÊN -->
                <div class="mt-1">
                  <label class="form-label text-muted small fw-semibold mb-1">Ưu tiên</label>
                  <select
                    v-model="editForm.mucDoUuTien"
                    class="form-select form-select-sm rounded-3"
                    @change="handleUpdatePriority"
                  >
                    <option value="Bình thường">Bình thường</option>
                    <option value="Cao">Cao</option>
                    <option value="Khẩn cấp">Khẩn cấp</option>
                  </select>
                </div>

                <!-- HẸN TRẢ ĐỒ / HẸN GIỜ -->
                <div class="mt-1">
                  <label class="form-label text-muted small fw-semibold mb-1">Hẹn trả đồ / Hẹn giờ</label>
                  <div class="input-group input-group-sm">
                    <input
                      v-model="editForm.thoiGianHen"
                      type="text"
                      class="form-control rounded-start-3"
                      placeholder="VD: 09/11/2026 - 10:00"
                    />
                    <button class="btn btn-outline-secondary" type="button" @click="handleUpdateDeadline" title="Lưu giờ hẹn">
                      <i class="bi bi-check-lg"></i>
                    </button>
                  </div>
                </div>

                <!-- GHI CHÚ TỪ KHÁCH -->
                <div class="mt-2">
                  <label class="form-label text-muted small fw-semibold mb-1">Ghi chú từ khách</label>
                  <div class="p-2.5 rounded-3 bg-light-soft text-dark small fst-italic">
                    {{ selectedRequest.ghiChu || 'Khách không để lại ghi chú thêm.' }}
                  </div>
                </div>

                <!-- SỰ CỐ PHÁT SINH NẾU CÓ -->
                <div v-if="selectedRequest.baoSuCo" class="mt-2 p-2.5 rounded-3 bg-danger-subtle border border-danger-subtle text-danger small">
                  <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Sự cố ghi nhận:</strong>
                  <div>{{ selectedRequest.baoSuCo }}</div>
                </div>
              </div>

              <!-- HÀNH ĐỘNG DÀNH CHO ADMIN / LỄ TÂN & NHÂN VIÊN -->
              <div class="action-buttons-zone pt-3 d-flex flex-column gap-2">
                <!-- NẾU CHỜ TIẾP NHẬN: TIẾP NHẬN HOẶC TỪ CHỐI HOẶC HỦY -->
                <template v-if="isPendingState(selectedRequest.trangThai)">
                  <button
                    type="button"
                    class="btn btn-teal-primary w-100 rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2"
                    @click="handleTiepNhan"
                    :disabled="actionLoading"
                  >
                    <span v-if="actionLoading" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-check2-circle"></i>
                    <span>Tiếp nhận yêu cầu</span>
                  </button>

                  <div class="d-flex align-items-center gap-2">
                    <button
                      type="button"
                      class="btn btn-outline-danger flex-fill rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1.5"
                      @click="openRejectModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-x-circle"></i>
                      <span>Từ chối</span>
                    </button>

                    <button
                      type="button"
                      class="btn btn-outline-secondary flex-fill rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-1.5"
                      @click="openCancelModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-trash"></i>
                      <span>Hủy yêu cầu</span>
                    </button>
                  </div>
                </template>

                <!-- NẾU ĐÃ TIẾP NHẬN: NHÂN VIÊN NHẬN VIỆC HOẶC BẮT ĐẦU -->
                <template v-else-if="selectedRequest.trangThai === 'Da_Tiep_Nhan'">
                  <button
                    type="button"
                    class="btn btn-teal-primary w-100 rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2"
                    @click="handleNhanViec"
                    :disabled="actionLoading"
                  >
                    <span v-if="actionLoading" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-hand-index-thumb-fill"></i>
                    <span>Nhận việc ngay</span>
                  </button>

                  <button
                    type="button"
                    class="btn btn-outline-teal w-100 rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2"
                    @click="handleBatDauThucHien"
                    :disabled="actionLoading"
                  >
                    <i class="bi bi-play-circle-fill"></i>
                    <span>Bắt đầu thực hiện</span>
                  </button>

                  <div class="d-flex align-items-center gap-2">
                    <button
                      type="button"
                      class="btn btn-outline-danger flex-fill rounded-pill py-1.5 btn-sm"
                      @click="openRejectModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-x-circle me-1"></i>Từ chối
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline-secondary flex-fill rounded-pill py-1.5 btn-sm"
                      @click="openCancelModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-trash me-1"></i>Hủy đơn
                    </button>
                  </div>
                </template>

                <!-- NẾU ĐÃ NHẬN VIỆC HOẶC ĐANG THỰC HIỆN -->
                <template v-else-if="selectedRequest.trangThai === 'Da_Nhan_Viec' || selectedRequest.trangThai === 'Dang_Thuc_Hien' || selectedRequest.trangThai === 'Dang_Xu_Ly'">
                  <button
                    v-if="selectedRequest.trangThai !== 'Dang_Thuc_Hien'"
                    type="button"
                    class="btn btn-outline-teal w-100 rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2 mb-1"
                    @click="handleBatDauThucHien"
                    :disabled="actionLoading"
                  >
                    <i class="bi bi-play-circle-fill"></i>
                    <span>Bắt đầu thực hiện</span>
                  </button>

                  <button
                    type="button"
                    class="btn btn-success w-100 rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2"
                    @click="handleHoanThanh"
                    :disabled="actionLoading"
                  >
                    <span v-if="actionLoading" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="bi bi-check-all"></i>
                    <span>Xác nhận hoàn tất</span>
                  </button>

                  <div class="d-flex align-items-center gap-2 mt-1">
                    <button
                      type="button"
                      class="btn btn-outline-secondary flex-fill rounded-pill py-1.5 btn-sm"
                      @click="openCancelModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-trash me-1"></i>Hủy yêu cầu
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline-warning text-dark flex-fill rounded-pill py-1.5 btn-sm"
                      @click="openIncidentModal"
                      :disabled="actionLoading"
                    >
                      <i class="bi bi-exclamation-triangle me-1 text-warning"></i>Báo sự cố
                    </button>
                  </div>
                </template>

                <!-- NẾU ĐÃ HOÀN TẤT -->
                <template v-else-if="selectedRequest.trangThai === 'Da_Hoan_Thanh' || selectedRequest.trangThai === 'Hoan_Thanh'">
                  <div class="alert alert-success py-2.5 text-center small rounded-3 mb-0">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    <span>Đã hoàn tất và được ghi phí vào hóa đơn lưu trú.</span>
                  </div>
                </template>

                <!-- NẾU ĐÃ TỪ CHỐI -->
                <template v-else-if="selectedRequest.trangThai === 'Da_Tu_Choi'">
                  <div class="alert alert-danger-subtle border border-danger-subtle py-2.5 small rounded-3 mb-0 text-danger-emphasis">
                    <div class="d-flex align-items-center gap-1.5 fw-bold">
                      <i class="bi bi-slash-circle-fill text-danger"></i>
                      <span>Yêu cầu đã bị từ chối</span>
                    </div>
                    <div v-if="selectedRequest.lyDoTuChoi" class="mt-1 small text-dark">
                      Lý do: <em>{{ selectedRequest.lyDoTuChoi }}</em>
                    </div>
                  </div>
                </template>

                <!-- NẾU ĐÃ HỦY -->
                <template v-else-if="selectedRequest.trangThai === 'Da_Huy'">
                  <div class="alert alert-secondary py-2.5 small rounded-3 mb-0">
                    <div class="d-flex align-items-center gap-1.5 fw-bold text-dark">
                      <i class="bi bi-x-circle-fill text-secondary"></i>
                      <span>Yêu cầu đã bị hủy</span>
                    </div>
                    <div v-if="selectedRequest.lyDoHuy" class="mt-1 small text-muted">
                      Lý do: <strong class="text-dark">{{ selectedRequest.lyDoHuy }}</strong>
                    </div>
                    <div v-if="selectedRequest.thoiGianHuy" class="small text-secondary mt-0.5" style="font-size: 0.72rem;">
                      Thời gian hủy: {{ selectedRequest.thoiGianHuy }}
                    </div>
                  </div>
                </template>
              </div>
            </div>

            <!-- NẾU CHƯA CHỌN YÊU CẦU NÀO -->
            <div v-else class="card-body p-5 text-center text-muted">
              <i class="bi bi-cursor-fill display-6 text-muted mb-3 opacity-50"></i>
              <h6 class="fw-bold text-dark">Chưa chọn yêu cầu</h6>
              <p class="small text-muted mb-0">
                Nhấp vào một dòng trong danh sách bên trái để xem đầy đủ chi tiết và thực hiện xử lý.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS: TẠO YÊU CẦU, TỪ CHỐI, HỦY & BÁO SỰ CỐ -->
      <modal-tao-yeu-cau
        :show="showCreateModal"
        :user-role="currentUserRole"
        @close="showCreateModal = false"
        @created="handleRequestCreated"
      ></modal-tao-yeu-cau>

      <modal-tu-choi
        :show="showRejectModal"
        :request-data="selectedRequest"
        :user-role="currentUserRole"
        @close="showRejectModal = false"
        @submitted="handleRequestRejected"
      ></modal-tu-choi>

      <modal-huy-yeu-cau
        :show="showCancelModal"
        :request-data="selectedRequest"
        :user-role="currentUserRole"
        @close="showCancelModal = false"
        @submitted="handleRequestCancelled"
      ></modal-huy-yeu-cau>

      <modal-bao-su-co
        :show="showIncidentModal"
        :request-data="selectedRequest"
        :user-role="currentUserRole"
        @close="showIncidentModal = false"
        @submitted="handleIncidentReported"
      ></modal-bao-su-co>
    </div>
  </div>
</template>

<script>
import yeuCauDichVuService from './yeuCauDichVuService.js';
import ModalTaoYeuCau from './ModalTaoYeuCau.vue';
import ModalTuChoi from './ModalTuChoi.vue';
import ModalHuyYeuCau from './ModalHuyYeuCau.vue';
import ModalBaoSuCo from './ModalBaoSuCo.vue';

export default {
  name: 'YeuCauDichVu',
  components: {
    ModalTaoYeuCau,
    ModalTuChoi,
    ModalHuyYeuCau,
    ModalBaoSuCo
  },
  data() {
    return {
      requests: [],
      selectedRequest: null,
      loading: false,
      actionLoading: false,

      currentTab: 'all', // all, cho_tiep_nhan, dang_xu_ly, hoan_tat, tu_choi
      tabCounts: {
        all: 0,
        choTiepNhan: 0,
        dangXuLy: 0,
        hoanTat: 0,
        tuChoi: 0
      },

      filters: {
        q: '',
        ngay: '',
        mucDoUuTien: 'Tất cả',
        boPhan: 'Tất cả',
        maNhanVien: 'Tất cả'
      },
      searchDebounceTimer: null,

      staffOptions: [],

      // Form sửa nhanh trên panel chi tiết
      editForm: {
        maNhanVien: '',
        mucDoUuTien: 'Bình thường',
        thoiGianHen: ''
      },

      // Modals
      showCreateModal: false,
      showRejectModal: false,
      showCancelModal: false,
      showIncidentModal: false,

      // Thông báo nội bộ
      notifications: [],
      unreadNotifyCount: 0,

      // Vai trò: Chỉ duy nhất Admin / Quản lý điều hành toàn bộ
      currentUserRole: 'Admin',
      currentUserId: 1,
      currentUser: {
        hoTen: 'Trần An',
        avatarText: 'AD'
      },

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
      const days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
      const dayName = days[d.getDay()];
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${dayName}, ${day} tháng ${month}, ${year}`;
    },
    matchingStaff() {
      if (!this.selectedRequest || !this.selectedRequest.boPhanPhuTrach) {
        return this.staffOptions;
      }
      return this.staffOptions.filter(nv => nv.boPhan === this.selectedRequest.boPhanPhuTrach);
    },
    otherStaff() {
      if (!this.selectedRequest || !this.selectedRequest.boPhanPhuTrach) {
        return [];
      }
      return this.staffOptions.filter(nv => nv.boPhan !== this.selectedRequest.boPhanPhuTrach);
    }
  },
  mounted() {
    this.fetchRequests();
    this.fetchStaffOptions();
    this.fetchNotifications();
  },
  methods: {
    /**
     * 1. LẤY DANH SÁCH YÊU CẦU THEO TAB VÀ BỘ LỌC
     */
    async fetchRequests(tab = null) {
      if (tab) this.currentTab = tab;
      this.loading = true;

      try {
        const res = await yeuCauDichVuService.getDanhSachYeuCau({
          tab: this.currentTab,
          ...this.filters
        }, this.currentUserRole, this.currentUserId);

        if (res.status === 'success') {
          this.requests = res.data || [];
          if (res.counts) {
            this.tabCounts = res.counts;
          }

          // Cập nhật selected request
          if (this.requests.length > 0) {
            if (!this.selectedRequest) {
              this.chonYeuCau(this.requests[0]);
            } else {
              const found = this.requests.find(r => r.id === this.selectedRequest.id);
              this.chonYeuCau(found || this.requests[0]);
            }
          } else {
            this.selectedRequest = null;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách yêu cầu:', err);
        this.showToast(err.message || 'Không thể tải danh sách yêu cầu dịch vụ.', 'danger');
      } finally {
        this.loading = false;
      }
    },

    /**
     * LẤY DANH SÁCH NHÂN VIÊN ĐỂ PHÂN CÔNG
     */
    async fetchStaffOptions() {
      try {
        const res = await yeuCauDichVuService.getNhanVienPhanCong();
        if (res.status === 'success') {
          this.staffOptions = res.data || [];
        }
      } catch (err) {
        console.error('Lỗi tải nhân viên:', err);
      }
    },

    /**
     * LẤY DANH SÁCH THÔNG BÁO NỘI BỘ
     */
    async fetchNotifications() {
      try {
        const res = await yeuCauDichVuService.getThongBao(this.currentUserRole, this.currentUserId);
        if (res.status === 'success') {
          this.notifications = res.data || [];
          this.unreadNotifyCount = res.unreadCount || 0;
        }
      } catch (err) {
        console.error('Lỗi tải thông báo:', err);
      }
    },

    /**
     * ĐỌC TẤT CẢ THÔNG BÁO
     */
    async markAllNotificationsRead() {
      try {
        await yeuCauDichVuService.markAllThongBaoAsRead(this.currentUserRole);
        this.unreadNotifyCount = 0;
        this.notifications.forEach(n => n.trangThai = 'Da_Xem');
      } catch (err) {
        console.error('Lỗi đánh dấu thông báo:', err);
      }
    },

    /**
     * CHỌN YÊU CẦU ĐỂ XEM CHI TIẾT
     */
    chonYeuCau(item) {
      this.selectedRequest = item;
      this.editForm = {
        maNhanVien: item.maNhanVien ? Number(item.maNhanVien) : '',
        mucDoUuTien: item.mucDoUuTien || 'Bình thường',
        thoiGianHen: item.thoiGianHen || ''
      };
    },

    /**
     * CHUYỂN TAB
     */
    changeTab(tab) {
      this.currentTab = tab;
      this.fetchRequests();
    },

    /**
     * TÌM KIẾM CÓ DEBOUNCE
     */
    handleSearchInput() {
      clearTimeout(this.searchDebounceTimer);
      this.searchDebounceTimer = setTimeout(() => {
        this.fetchRequests();
      }, 300);
    },

    /**
     * LÀM MỚI DỮ LIỆU
     */
    refreshData() {
      this.fetchRequests();
      this.fetchNotifications();
      this.showToast('Đã làm mới dữ liệu từ máy chủ.', 'success');
    },

    /**
     * TIẾP NHẬN YÊU CẦU
     */
    async handleTiepNhan() {
      if (!this.selectedRequest) return;
      this.actionLoading = true;
      try {
        const res = await yeuCauDichVuService.tiepNhan(this.selectedRequest.id, {
          mucDoUuTien: this.editForm.mucDoUuTien,
          thoiGianHen: this.editForm.thoiGianHen,
          maNhanVien: this.editForm.maNhanVien || null
        }, this.currentUserRole);

        if (res.status === 'success') {
          this.showToast(res.message, 'success');
          await this.fetchRequests();
          await this.fetchNotifications();
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.actionLoading = false;
      }
    },

    /**
     * NHÂN VIÊN NHẬN VIỆC TRỰC TIẾP
     */
    async handleNhanViec() {
      if (!this.selectedRequest) return;
      this.actionLoading = true;
      try {
        const res = await yeuCauDichVuService.nhanViec(this.selectedRequest.id, this.currentUserId, this.currentUserRole);
        if (res.status === 'success') {
          this.showToast(res.message, 'success');
          await this.fetchRequests();
          await this.fetchNotifications();
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.actionLoading = false;
      }
    },

    /**
     * BẮT ĐẦU THỰC HIỆN
     */
    async handleBatDauThucHien() {
      if (!this.selectedRequest) return;
      this.actionLoading = true;
      try {
        const res = await yeuCauDichVuService.batDauThucHien(this.selectedRequest.id, this.currentUserRole, this.currentUserId);
        if (res.status === 'success') {
          this.showToast(res.message, 'success');
          await this.fetchRequests();
          await this.fetchNotifications();
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.actionLoading = false;
      }
    },

    /**
     * HOÀN THÀNH YÊU CẦU
     */
    async handleHoanThanh() {
      if (!this.selectedRequest) return;
      this.actionLoading = true;
      try {
        const res = await yeuCauDichVuService.hoanThanh(this.selectedRequest.id, this.currentUserRole, this.currentUserId);
        if (res.status === 'success') {
          this.showToast(res.message, 'success');
          await this.fetchRequests();
          await this.fetchNotifications();
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.actionLoading = false;
      }
    },

    /**
     * CẬP NHẬT NGƯỜI PHỤ TRÁCH NHANH TRÊN PANEL CHI TIẾT
     */
    async handleUpdateAssignee() {
      if (!this.selectedRequest) return;
      this.actionLoading = true;
      try {
        const payload = {
          maNhanVien: this.editForm.maNhanVien ? Number(this.editForm.maNhanVien) : null,
          mucDoUuTien: this.editForm.mucDoUuTien,
          thoiGianHen: this.editForm.thoiGianHen
        };

        const res = await yeuCauDichVuService.phanCong(this.selectedRequest.id, payload, this.currentUserRole, this.currentUserId);

        if (res.status === 'success') {
          this.showToast(res.message, 'success');

          if (res.data) {
            this.selectedRequest.maNhanVien = res.data.maNhanVien;
            this.selectedRequest.tenNhanVien = res.data.tenNhanVien;
            this.selectedRequest.chucVuNhanVien = res.data.chucVuNhanVien;
            this.selectedRequest.trangThai = res.data.trangThai;
            this.selectedRequest.trangThaiLabel = res.data.trangThaiLabel;
            this.selectedRequest.mucDoUuTien = res.data.mucDoUuTien;
            this.selectedRequest.thoiGianHen = res.data.thoiGianHen;

            this.editForm.maNhanVien = res.data.maNhanVien ? Number(res.data.maNhanVien) : '';
            this.editForm.mucDoUuTien = res.data.mucDoUuTien || 'Bình thường';
            this.editForm.thoiGianHen = res.data.thoiGianHen || '';
          }

          // Cập nhật mảng requests tại chỗ
          const idx = this.requests.findIndex(r => r.id === this.selectedRequest.id);
          if (idx !== -1 && res.data) {
            this.requests[idx].maNhanVien = res.data.maNhanVien;
            this.requests[idx].tenNhanVien = res.data.tenNhanVien;
            this.requests[idx].mucDoUuTien = res.data.mucDoUuTien;
            this.requests[idx].thoiGianHen = res.data.thoiGianHen;
            this.requests[idx].trangThai = res.data.trangThai;
            this.requests[idx].trangThaiLabel = res.data.trangThaiLabel;
          }

          // Làm mới số đếm tab ngầm
          this.fetchTabCountsSilently();
        }
      } catch (err) {
        this.showToast(err.message || 'Lỗi phân công nhân viên.', 'danger');
      } finally {
        this.actionLoading = false;
      }
    },

    /**
     * TẢI SỐ LƯỢNG TAB NGẦM MÀ KHÔNG LÀM NHẢY SELECTION
     */
    async fetchTabCountsSilently() {
      try {
        const res = await yeuCauDichVuService.getDanhSachYeuCau({ tab: 'all' });
        if (res.status === 'success' && res.counts) {
          this.tabCounts = res.counts;
        }
      } catch (e) {
        // Bỏ qua lỗi silent
      }
    },

    /**
     * CẬP NHẬT ƯU TIÊN
     */
    async handleUpdatePriority() {
      this.handleUpdateAssignee();
    },

    /**
     * CẬP NHẬT GIỜ HẸN
     */
    async handleUpdateDeadline() {
      this.handleUpdateAssignee();
    },

    /**
     * MODAL TỪ CHỐI
     */
    openRejectModal() {
      this.showRejectModal = true;
    },

    handleRequestRejected(res) {
      this.showToast(res.message, 'success');
      this.fetchRequests();
      this.fetchNotifications();
    },

    /**
     * MODAL HỦY YÊU CẦU
     */
    openCancelModal() {
      this.showCancelModal = true;
    },

    handleRequestCancelled(res) {
      this.showToast(res.message, 'success');
      this.fetchRequests();
      this.fetchNotifications();
    },

    /**
     * MODAL BÁO SỰ CỐ
     */
    openIncidentModal() {
      this.showIncidentModal = true;
    },

    handleIncidentReported(res) {
      this.showToast(res.message, 'success');
      this.fetchRequests();
      this.fetchNotifications();
    },

    /**
     * MODAL TẠO YÊU CẦU MỚI
     */
    handleRequestCreated(res) {
      this.showToast(res.message, 'success');
      this.fetchRequests();
      this.fetchNotifications();
    },

    quayLai() {
      if (window.history.length > 1) {
        this.$router.back();
      } else {
        this.$router.push('/buong-phong');
      }
    },

    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 4000);
    },

    formatCurrency(val) {
      if (!val) return '0 đ';
      return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
    },

    isPendingState(status) {
      return status === 'Cho_Tiep_Nhan' || status === 'Tiep_Nhan';
    },

    getPriorityClass(p) {
      if (p === 'Cao') return 'text-danger fw-semibold';
      if (p === 'Khẩn cấp') return 'text-danger fw-bold';
      return 'text-muted';
    },

    getStatusDotClass(st) {
      if (st === 'Da_Hoan_Thanh' || st === 'Hoan_Thanh') return 'dot-green';
      if (st === 'Da_Tiep_Nhan' || st === 'Da_Nhan_Viec' || st === 'Dang_Thuc_Hien' || st === 'Dang_Xu_Ly') return 'dot-blue';
      if (st === 'Da_Tu_Choi' || st === 'Da_Huy') return 'dot-gray';
      return 'dot-amber';
    },

    getStatusTagClass(st) {
      if (st === 'Da_Hoan_Thanh' || st === 'Hoan_Thanh') return 'tag-done';
      if (st === 'Da_Tiep_Nhan') return 'tag-accepted';
      if (st === 'Da_Nhan_Viec' || st === 'Dang_Thuc_Hien' || st === 'Dang_Xu_Ly') return 'tag-doing';
      if (st === 'Da_Tu_Choi' || st === 'Da_Huy') return 'tag-rejected';
      return 'tag-pending';
    },

    getFullStatusTagLabel(req) {
      if (req.trangThai === 'Da_Hoan_Thanh' || req.trangThai === 'Hoan_Thanh') {
        return '• Đã hoàn tất';
      }
      if (req.trangThai === 'Da_Tiep_Nhan') {
        return '• Đã tiếp nhận · Chưa hoàn tất';
      }
      if (req.trangThai === 'Da_Nhan_Viec') {
        return `• Đã nhận việc (${req.tenNhanVien || 'Nhân viên'})`;
      }
      if (req.trangThai === 'Dang_Thuc_Hien' || req.trangThai === 'Dang_Xu_Ly') {
        return '• Đang thực hiện';
      }
      if (req.trangThai === 'Da_Tu_Choi') {
        return '• Đã từ chối';
      }
      return '• Chờ tiếp nhận';
    },

    getNotifyIcon(type) {
      if (type === 'hoan_thanh') return 'bi-check-circle-fill';
      if (type === 'su_co') return 'bi-exclamation-triangle-fill';
      if (type === 'nhan_viec') return 'bi-person-check-fill';
      if (type === 'tiep_nhan') return 'bi-check2';
      return 'bi-bell-fill';
    }
  }
};
</script>

<style scoped>
.yeu-cau-dich-vu-page {
  background-color: #F8F9FA;
  min-height: calc(100vh - 120px);
}

.text-teal {
  color: #1E5E49 !important;
}

.bg-teal-subtle {
  background-color: #E6F3EE !important;
}

.bg-teal-subtle-soft {
  background-color: #F0F9F5 !important;
}

.text-main-dark {
  color: #172620;
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

/* TABS */
.btn-tab-nav {
  background: none;
  border: none;
  padding: 0.65rem 1rem;
  font-weight: 500;
  font-size: 0.92rem;
  color: #55625D;
  position: relative;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-tab-nav:hover {
  color: #1E5E49;
}

.btn-tab-nav.active {
  color: #1E5E49;
  font-weight: 600;
}

.btn-tab-nav.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2.5px;
  background-color: #1E5E49;
  border-radius: 3px 3px 0 0;
}

.tab-count-badge {
  font-size: 0.85rem;
  color: inherit;
}

/* TABLE */
.custom-request-table th {
  border-bottom: 1px solid #EAE8E1;
  font-weight: 600;
  letter-spacing: 0.3px;
}

.custom-request-table td {
  border-bottom: 1px solid #F1F3F5;
}

.request-table-row {
  transition: background-color 0.15s ease;
}

.request-table-row:hover {
  background-color: #F4F8F6;
}

.request-table-row.row-active {
  background-color: #EAF4F0 !important;
  border-left: 3px solid #1E5E49;
}

.code-room-badge {
  font-size: 0.88rem;
  color: #1E5E49;
}

.highlight-badge {
  color: #0E4837;
  font-weight: 700;
}

/* STATUS DOTS */
.status-indicator-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  display: inline-block;
}

.dot-green { background-color: #10B981; }
.dot-blue { background-color: #3B82F6; }
.dot-amber { background-color: #F59E0B; }
.dot-gray { background-color: #9CA3AF; }

/* BADGE TAG CHI TIẾT */
.badge-status-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.8rem;
  border-radius: 50rem;
  font-size: 0.78rem;
  font-weight: 600;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
}

.tag-accepted {
  background-color: #EEF2FF;
  color: #4F46E5;
  border: 1px solid #C7D2FE;
}
.tag-accepted .status-dot { background-color: #4F46E5; }

.tag-done {
  background-color: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}
.tag-done .status-dot { background-color: #10B981; }

.tag-doing {
  background-color: #F0FDF4;
  color: #15803D;
  border: 1px solid #BBF7D0;
}
.tag-doing .status-dot { background-color: #22C55E; }

.tag-pending {
  background-color: #FFFBEB;
  color: #B45309;
  border: 1px solid #FDE68A;
}
.tag-pending .status-dot { background-color: #F59E0B; }

.tag-rejected {
  background-color: #FEF2F2;
  color: #B91C1C;
  border: 1px solid #FECACA;
}
.tag-rejected .status-dot { background-color: #EF4444; }

/* NOTIFICATION DROPDOWN */
.notify-icon-circle {
  width: 26px;
  height: 26px;
  font-size: 0.75rem;
}

.notify-item:hover {
  background-color: #F8FAF9;
}

/* UTILITY */
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
