<template>
  <div class="booking-page py-4">
    <div class="container">
      <!-- Breadcrumb & Page Header -->
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/" class="text-decoration-none text-teal">Trang chủ</router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Đặt phòng lưu trú</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <h2 class="fw-bold text-navy mb-1">
            <i class="bi bi-calendar2-check-fill text-teal me-2"></i>
            {{ isReceptionist ? 'Tiếp Nhận Đặt Phòng (Lễ Tân)' : 'Đặt Phòng Trực Tuyến' }}
          </h2>
          <p class="text-muted mb-0">
            {{ isReceptionist
              ? 'Tạo đơn đặt phòng trực tiếp cho khách hàng tại quầy lễ tân hoặc qua điện thoại.'
              : 'Trải nghiệm kỳ nghỉ đẳng cấp tại RoomBook Hotel & Resort. Đảm bảo giá tốt nhất và xác nhận nhanh chóng.'
            }}
          </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <span class="badge rounded-pill px-3 py-2" :class="isReceptionist ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle'">
            <i :class="isReceptionist ? 'bi bi-person-badge-fill me-1' : 'bi bi-person-check-fill me-1'"></i>
            Vai trò: {{ currentUser.vaiTro || 'Khách hàng' }}
          </span>
          <div class="small text-muted mt-1" v-if="currentUser.hoTen">
            Tài khoản: <strong>{{ currentUser.hoTen }}</strong>
          </div>
        </div>
      </div>

      <!-- Quick Test & Demo Helpers (Hỗ trợ kiểm thử nhanh) -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Dữ liệu mẫu kiểm thử nhanh:</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                @click="fillDemoSingleRoom"
                :disabled="isSubmitting"
              >
                <i class="bi bi-door-closed me-1"></i> Mẫu 1 phòng (Hợp lệ)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                @click="fillDemoMultiRoom"
                :disabled="isSubmitting"
              >
                <i class="bi bi-houses me-1"></i> Mẫu 2 phòng (Gia đình)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-warning rounded-3"
                @click="fillDemoConflict"
                :disabled="isSubmitting"
              >
                <i class="bi bi-exclamation-triangle me-1"></i> Thử trùng lịch (409)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger rounded-3"
                @click="fillDemoOverCapacity"
                :disabled="isSubmitting"
              >
                <i class="bi bi-people me-1"></i> Thử quá tải (422)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-dark rounded-3"
                @click="resetForm"
                :disabled="isSubmitting"
              >
                <i class="bi bi-arrow-counterclockwise me-1"></i> Làm mới form
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Global Alerts (409 Conflict, 422 Validation, 500 Server Error) -->
      <div
        v-if="conflictError"
        class="alert alert-warning border-warning shadow-sm rounded-4 mb-4 d-flex align-items-start gap-3 p-3"
        role="alert"
      >
        <i class="bi bi-exclamation-octagon-fill fs-4 text-warning flex-shrink-0 mt-1"></i>
        <div class="flex-grow-1">
          <h5 class="fw-bold mb-1 text-dark">Phòng đã bị trùng lịch đặt (Mã lỗi: 409 Conflict)</h5>
          <p class="mb-2 text-dark-emphasis">{{ conflictError.message }}</p>
          <div v-if="conflictError.rooms && conflictError.rooms.length > 0" class="mb-2">
            <span class="small fw-semibold text-muted">Các phòng không khả dụng trong khung giờ này:</span>
            <div class="d-flex flex-wrap gap-1 mt-1">
              <span
                v-for="roomName in conflictError.rooms"
                :key="roomName"
                class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"
              >
                <i class="bi bi-x-circle me-1"></i> {{ roomName }}
              </span>
            </div>
          </div>
          <small class="text-muted d-block">
            Gợi ý: Quý khách vui lòng chọn khoảng thời gian khác hoặc đổi sang phòng còn trống bên dưới.
          </small>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="conflictError = null"></button>
      </div>

      <div
        v-if="errorMessage"
        class="alert alert-danger border-danger shadow-sm rounded-4 mb-4 d-flex align-items-center gap-3 p-3"
        role="alert"
      >
        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger flex-shrink-0"></i>
        <div class="flex-grow-1">
          <div class="fw-semibold">{{ errorMessage }}</div>
          <ul v-if="Object.keys(fieldErrors).length > 0" class="mb-0 mt-1 ps-3 small">
            <li v-for="(errs, field) in fieldErrors" :key="field">
              {{ Array.isArray(errs) ? errs.join(', ') : errs }}
            </li>
          </ul>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="errorMessage = ''"></button>
      </div>

      <!-- Main Booking Form Layout -->
      <div class="row g-4">
        <!-- Cột Trái: Thông tin khách + Ngày nhận/trả + Danh sách chọn phòng -->
        <div class="col-lg-8">
          <!-- 1. Thông tin Khách hàng (Phân quyền theo UC04 & BR02) -->
          <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
              <div class="d-flex align-items-center gap-2">
                <div class="step-badge">1</div>
                <h5 class="fw-bold text-navy mb-0">Thông tin Khách hàng</h5>
              </div>
            </div>
            <div class="card-body p-4">
              <!-- Nếu là Khách hàng: Hiển thị thông tin tự động gắn từ tài khoản -->
              <div v-if="!isReceptionist" class="bg-light rounded-3 p-3 border">
                <div class="row g-3 align-items-center">
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Họ tên khách hàng:</span>
                    <strong class="text-navy fs-6">{{ currentUser.hoTen || 'Khách hàng' }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Tài khoản đăng nhập:</span>
                    <strong class="text-navy fs-6">{{ currentUser.tenDangNhap }}</strong>
                  </div>
                </div>
                <div class="alert alert-info py-2 px-3 rounded-3 small mb-0 mt-3 d-flex align-items-center gap-2">
                  <i class="bi bi-shield-check text-info flex-shrink-0"></i>
                  <span>Đơn đặt phòng sẽ được tự động liên kết với mã khách hàng của tài khoản bạn đang đăng nhập.</span>
                </div>
              </div>

              <!-- Nếu là Lễ tân hoặc Quản lý: Có quyền chọn khách hàng hoặc tìm kiếm theo SĐT/CCCD -->
              <div v-else>
                <div class="row g-3">
                  <div class="col-md-7">
                    <label class="form-label fw-semibold small text-navy">
                      Chọn khách hàng từ hệ thống <span class="text-danger">*</span>
                    </label>
                    <select
                      v-model="form.maKhachHang"
                      class="form-select"
                      :class="{ 'is-invalid': fieldErrors.maKhachHang }"
                      :disabled="isSubmitting"
                    >
                      <option :value="null">-- Chọn khách hàng lưu trú --</option>
                      <option v-for="c in customerList" :key="c.id" :value="c.id">
                        #{{ c.id }} - {{ c.hoTen }} ({{ c.soDienThoai || 'Chưa có SĐT' }} - CCCD: {{ c.cccd || 'N/A' }})
                      </option>
                    </select>
                    <div v-if="fieldErrors.maKhachHang" class="invalid-feedback">
                      {{ Array.isArray(fieldErrors.maKhachHang) ? fieldErrors.maKhachHang[0] : fieldErrors.maKhachHang }}
                    </div>
                  </div>
                  <div class="col-md-5">
                    <label class="form-label fw-semibold small text-navy">
                      Nhập nhanh Mã khách hàng (ID)
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light text-muted">ID</span>
                      <input
                        type="number"
                        v-model.number="form.maKhachHang"
                        class="form-control"
                        placeholder="Ví dụ: 1"
                        min="1"
                        :disabled="isSubmitting"
                      />
                    </div>
                  </div>
                </div>

                <!-- Thông tin tóm tắt khách hàng được chọn -->
                <div v-if="selectedCustomerInfo" class="mt-3 p-3 bg-light rounded-3 border">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-person-check-fill text-teal"></i>
                    <strong class="text-navy">Khách được chọn: {{ selectedCustomerInfo.hoTen }}</strong>
                  </div>
                  <div class="row g-2 small text-muted">
                    <div class="col-6 col-sm-4">
                      <i class="bi bi-telephone me-1"></i> {{ selectedCustomerInfo.soDienThoai || 'N/A' }}
                    </div>
                    <div class="col-6 col-sm-4">
                      <i class="bi bi-card-heading me-1"></i> CCCD: {{ selectedCustomerInfo.cccd || 'N/A' }}
                    </div>
                    <div class="col-12 col-sm-4">
                      <i class="bi bi-envelope me-1"></i> {{ selectedCustomerInfo.email || 'N/A' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Thời gian Lưu trú & Số lượng khách (BR01.4, BR02.1) -->
          <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <div class="step-badge">2</div>
                  <h5 class="fw-bold text-navy mb-0">Thời gian Lưu trú & Số lượng khách</h5>
                </div>
                <span class="badge bg-teal-subtle text-teal fw-semibold px-3 py-2">
                  <i class="bi bi-moon-stars-fill me-1"></i> {{ calculatedNights }} đêm
                </span>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="row g-3">
                <!-- Ngày nhận phòng -->
                <div class="col-md-6">
                  <label for="ngayNhanPhong" class="form-label fw-semibold small text-navy">
                    <i class="bi bi-box-arrow-in-right text-teal me-1"></i> Ngày nhận phòng <span class="text-danger">*</span>
                  </label>
                  <input
                    id="ngayNhanPhong"
                    v-model="form.ngayNhanPhong"
                    type="datetime-local"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.ngayNhanPhong }"
                    :disabled="isSubmitting"
                    required
                  />
                  <div v-if="fieldErrors.ngayNhanPhong" class="invalid-feedback">
                    {{ Array.isArray(fieldErrors.ngayNhanPhong) ? fieldErrors.ngayNhanPhong[0] : fieldErrors.ngayNhanPhong }}
                  </div>
                  <div class="form-text small">Giờ nhận phòng tiêu chuẩn: 14:00</div>
                </div>

                <!-- Ngày trả phòng -->
                <div class="col-md-6">
                  <label for="ngayTraPhong" class="form-label fw-semibold small text-navy">
                    <i class="bi bi-box-arrow-right text-danger me-1"></i> Ngày trả phòng <span class="text-danger">*</span>
                  </label>
                  <input
                    id="ngayTraPhong"
                    v-model="form.ngayTraPhong"
                    type="datetime-local"
                    class="form-control"
                    :class="{ 'is-invalid': fieldErrors.ngayTraPhong }"
                    :disabled="isSubmitting"
                    required
                  />
                  <div v-if="fieldErrors.ngayTraPhong" class="invalid-feedback">
                    {{ Array.isArray(fieldErrors.ngayTraPhong) ? fieldErrors.ngayTraPhong[0] : fieldErrors.ngayTraPhong }}
                  </div>
                  <div class="form-text small">Giờ trả phòng tiêu chuẩn: 12:00</div>
                </div>

                <!-- Tổng số lượng khách -->
                <div class="col-md-6">
                  <label for="soLuongKhach" class="form-label fw-semibold small text-navy">
                    <i class="bi bi-people-fill text-teal me-1"></i> Tổng số lượng khách lưu trú <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <button
                      type="button"
                      class="btn btn-outline-secondary"
                      @click="form.soLuongKhach = Math.max(1, form.soLuongKhach - 1)"
                      :disabled="isSubmitting || form.soLuongKhach <= 1"
                    >
                      <i class="bi bi-dash"></i>
                    </button>
                    <input
                      id="soLuongKhach"
                      v-model.number="form.soLuongKhach"
                      type="number"
                      class="form-control text-center fw-bold"
                      :class="{ 'is-invalid': fieldErrors.soLuongKhach }"
                      min="1"
                      max="50"
                      :disabled="isSubmitting"
                      required
                    />
                    <button
                      type="button"
                      class="btn btn-outline-secondary"
                      @click="form.soLuongKhach = form.soLuongKhach + 1"
                      :disabled="isSubmitting"
                    >
                      <i class="bi bi-plus"></i>
                    </button>
                  </div>
                  <div v-if="fieldErrors.soLuongKhach" class="invalid-feedback d-block">
                    {{ Array.isArray(fieldErrors.soLuongKhach) ? fieldErrors.soLuongKhach[0] : fieldErrors.soLuongKhach }}
                  </div>
                  <div class="form-text small">Bao gồm cả người lớn và trẻ em</div>
                </div>

                <!-- Phím tắt chọn nhanh khoảng thời gian -->
                <div class="col-md-6">
                  <label class="form-label fw-semibold small text-navy">Chọn nhanh khoảng lưu trú:</label>
                  <div class="d-flex flex-wrap gap-2">
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-teal rounded-pill"
                      @click="setQuickDates(1)"
                      :disabled="isSubmitting"
                    >
                      1 đêm
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-teal rounded-pill"
                      @click="setQuickDates(2)"
                      :disabled="isSubmitting"
                    >
                      2 đêm (Cuối tuần)
                    </button>
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-teal rounded-pill"
                      @click="setQuickDates(3)"
                      :disabled="isSubmitting"
                    >
                      3 đêm (Nghỉ dưỡng)
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Lựa chọn Phòng (`danhSachPhong`) (BR01.8, BR02.1) -->
          <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                  <div class="step-badge">3</div>
                  <h5 class="fw-bold text-navy mb-0">Chọn Phòng Lưu Trú</h5>
                  <span class="badge bg-primary rounded-pill">
                    Đã chọn: {{ selectedRoomCount }} phòng
                  </span>
                </div>
                <!-- Bộ lọc theo tầng và loại phòng -->
                <div class="d-flex gap-2">
                  <select v-model="filterFloor" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Tất cả tầng</option>
                    <option :value="1">Tầng 1</option>
                    <option :value="2">Tầng 2</option>
                    <option :value="3">Tầng 3</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="card-body p-4">
              <!-- Loading Spinner cho danh sách phòng -->
              <div v-if="isLoadingRooms" class="text-center py-5">
                <div class="spinner-border text-teal mb-3" role="status">
                  <span class="visually-hidden">Đang tải phòng...</span>
                </div>
                <div class="text-muted small">Đang nạp danh sách phòng từ hệ thống...</div>
              </div>

              <!-- Lỗi khi không có phòng hoặc rỗng -->
              <div v-else-if="filteredRooms.length === 0" class="text-center py-4 text-muted">
                <i class="bi bi-door-closed fs-1 text-secondary mb-2 d-block"></i>
                <p class="mb-0">Không tìm thấy phòng phù hợp với bộ lọc hiện tại.</p>
              </div>

              <!-- Danh sách lưới các phòng -->
              <div v-else class="row g-3">
                <div v-for="room in filteredRooms" :key="room.id" class="col-md-6">
                  <div
                    class="card room-card h-100 rounded-3 border transition-all"
                    :class="{
                      'border-teal bg-teal-subtle-light shadow-sm selected': isRoomSelected(room.id),
                      'opacity-50 cursor-not-allowed bg-light': room.trangThai !== 'Sẵn sàng' || ['Đang bảo trì', 'Bảo trì', 'Ngưng khai thác'].includes(room.trangThai)
                    }"
                  >
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                          <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold text-navy mb-0">{{ room.tenPhong }}</h5>
                            <span class="badge bg-light text-dark border small">Tầng {{ room.tang }}</span>
                          </div>
                          <div class="text-teal fw-semibold small mt-1">
                            {{ room.loaiPhong?.tenLoai || 'Phòng tiêu chuẩn' }}
                          </div>
                        </div>
                        <span
                          class="badge rounded-pill"
                          :class="room.trangThai === 'Sẵn sàng' ? 'bg-success' : (['Đang bảo trì', 'Bảo trì'].includes(room.trangThai) ? 'bg-danger' : (room.trangThai === 'Ngưng khai thác' ? 'bg-secondary' : 'bg-warning text-dark'))"
                        >
                          {{ room.trangThai }}
                        </span>
                      </div>

                      <div class="row g-2 small text-muted my-2">
                        <div class="col-6">
                          <i class="bi bi-people me-1"></i> Tối đa: <strong>{{ room.loaiPhong?.sucChuaToiDa || 2 }}</strong> khách
                        </div>
                        <div class="col-6 text-end">
                          <strong class="text-navy fs-6">{{ formatCurrency(room.loaiPhong?.giaTieuChuan || 0) }}</strong>
                          <span class="small">/đêm</span>
                        </div>
                      </div>

                      <!-- Khu vực Chọn phòng & Phân bổ số khách -->
                      <div class="mt-3 pt-2 border-top">
                        <div v-if="!isRoomSelected(room.id)">
                          <button
                            type="button"
                            class="btn btn-outline-teal btn-sm w-100 rounded-3"
                            :disabled="room.trangThai !== 'Sẵn sàng' || isSubmitting"
                            @click="toggleSelectRoom(room)"
                          >
                            <i class="bi bi-check-circle me-1"></i> Chọn phòng này
                          </button>
                        </div>
                        <div v-else class="d-flex align-items-center justify-content-between gap-2">
                          <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <label class="small fw-semibold text-navy mb-0 text-nowrap">Số khách:</label>
                            <input
                              type="number"
                              v-model.number="getRoomSelection(room.id).soLuongKhach"
                              class="form-control form-control-sm text-center fw-bold"
                              style="width: 70px;"
                              min="1"
                              :max="room.loaiPhong?.sucChuaToiDa || 10"
                              :disabled="isSubmitting"
                            />
                            <span class="small text-muted">/ {{ room.loaiPhong?.sucChuaToiDa }}</span>
                          </div>
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-danger rounded-3"
                            @click="toggleSelectRoom(room)"
                            :disabled="isSubmitting"
                            title="Bỏ chọn phòng"
                          >
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Lỗi danh sách phòng nếu chưa chọn phòng nào -->
              <div v-if="fieldErrors.danhSachPhong" class="alert alert-danger py-2 px-3 small rounded-3 mt-3 mb-0">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ Array.isArray(fieldErrors.danhSachPhong) ? fieldErrors.danhSachPhong[0] : fieldErrors.danhSachPhong }}
              </div>
            </div>
          </div>
        </div>

        <!-- Cột Phải: Bảng Tóm Tắt Chi Phí, Tiền Cọc & Nút Xác Nhận -->
        <div class="col-lg-4">
          <div class="sticky-top" style="top: 90px; z-index: 10;">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
              <div class="card-header bg-navy text-white p-4">
                <h5 class="fw-bold mb-1">
                  <i class="bi bi-receipt me-2"></i> Tóm Tắt Đơn Đặt Phòng
                </h5>
                <p class="small text-white-50 mb-0">Kiểm tra thông tin chi tiết trước khi xác nhận</p>
              </div>

              <div class="card-body p-4">
                <!-- Thông tin tóm tắt lưu trú -->
                <div class="mb-3 pb-3 border-bottom">
                  <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Nhận phòng:</span>
                    <strong class="text-navy">{{ formatDateTime(form.ngayNhanPhong) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Trả phòng:</span>
                    <strong class="text-navy">{{ formatDateTime(form.ngayTraPhong) }}</strong>
                  </div>
                  <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Thời gian lưu trú:</span>
                    <strong class="text-teal">{{ calculatedNights }} đêm</strong>
                  </div>
                  <div class="d-flex justify-content-between small text-muted">
                    <span>Tổng số khách:</span>
                    <strong class="text-navy">{{ form.soLuongKhach }} khách</strong>
                  </div>
                </div>

                <!-- Danh sách phòng đã chọn -->
                <div class="mb-3 pb-3 border-bottom">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small fw-bold text-navy">Phòng đã chọn:</span>
                    <span class="badge bg-light text-dark">{{ selectedRoomCount }} phòng</span>
                  </div>
                  <div v-if="selectedRoomsList.length === 0" class="text-muted small fst-italic">
                    Chưa có phòng nào được chọn. Vui lòng bấm "Chọn phòng này" ở danh sách bên trái.
                  </div>
                  <div v-else class="space-y-2">
                    <div
                      v-for="item in selectedRoomsList"
                      :key="item.maPhong"
                      class="d-flex justify-content-between align-items-center small py-1"
                    >
                      <div>
                        <strong>{{ item.tenPhong }}</strong> ({{ item.tenLoai }})
                        <div class="text-muted" style="font-size: 0.75rem;">
                          {{ item.soLuongKhach }} khách · {{ formatCurrency(item.giaTieuChuan) }}/đêm
                        </div>
                      </div>
                      <div class="text-end fw-semibold text-navy">
                        {{ formatCurrency(item.giaTieuChuan * calculatedNights) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tính toán Tổng tiền & Tiền cọc (BR01.8, BR02.4, BR03.1) -->
                <div class="mb-4">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Tổng tiền phòng:</span>
                    <strong class="fs-5 text-navy">{{ formatCurrency(totalRoomAmount) }}</strong>
                  </div>

                  <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div>
                        <span class="fw-bold text-teal small d-block">Tiền cọc quy định (30%):</span>
                        <span class="text-muted" style="font-size: 0.75rem;">Cần thanh toán để giữ phòng</span>
                      </div>
                      <strong class="text-teal fs-6">{{ formatCurrency(defaultDeposit) }}</strong>
                    </div>

                    <!-- Cho phép tùy chỉnh tiền cọc nếu cần -->
                    <div class="mt-2">
                      <label for="customTienCoc" class="form-label small fw-semibold text-navy mb-1">
                        Số tiền cọc thực tế nộp:
                      </label>
                      <div class="input-group input-group-sm">
                        <input
                          id="customTienCoc"
                          type="number"
                          v-model.number="form.tienCoc"
                          class="form-control"
                          :class="{ 'is-invalid': fieldErrors.tienCoc }"
                          min="0"
                          :max="totalRoomAmount"
                          :disabled="isSubmitting"
                        />
                        <span class="input-group-text">VNĐ</span>
                      </div>
                      <div v-if="fieldErrors.tienCoc" class="invalid-feedback d-block small">
                        {{ Array.isArray(fieldErrors.tienCoc) ? fieldErrors.tienCoc[0] : fieldErrors.tienCoc }}
                      </div>
                      <div class="form-text" style="font-size: 0.75rem;">
                        Không âm và không vượt quá tổng tiền phòng.
                      </div>
                    </div>
                  </div>

                  <div class="alert alert-secondary py-2 px-3 rounded-3 small mb-0 d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-secondary flex-shrink-0 mt-1"></i>
                    <div style="font-size: 0.8rem;">
                      <strong>Chính sách hệ thống:</strong> Đơn đặt phòng sẽ được khởi tạo ở trạng thái
                      <span class="badge bg-warning text-dark">Chờ xác nhận</span>.
                      Chỉ sau khi thanh toán tiền cọc thành công, đơn mới được chuyển sang trạng thái chính thức (BR03.2).
                    </div>
                  </div>
                </div>

                <!-- Nút Xác nhận tạo đơn đặt phòng -->
                <button
                  type="button"
                  class="btn btn-navy w-100 py-3 rounded-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow"
                  :disabled="isSubmitting || selectedRoomCount === 0"
                  @click="submitBooking"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  <i v-else class="bi bi-check2-circle fs-5"></i>
                  <span>{{ isSubmitting ? 'Đang xử lý đơn đặt phòng...' : 'Xác Nhận & Đặt Phòng Ngay' }}</span>
                </button>

                <div class="text-center mt-3">
                  <small class="text-muted">
                    <i class="bi bi-lock-fill me-1"></i> Bảo mật giao dịch & tuân thủ quy chuẩn CDIO4
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal / Success Dialog khi Đặt phòng thành công (201 Created) -->
      <div
        v-if="bookingSuccessResult"
        class="modal fade show d-block"
        tabindex="-1"
        style="background: rgba(0, 0, 0, 0.65); z-index: 1050;"
        role="dialog"
      >
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-teal fs-4"></i>
                <h5 class="modal-title fw-bold mb-0">Đặt Phòng Khởi Tạo Thành Công!</h5>
              </div>
              <button
                type="button"
                class="btn-close btn-close-white"
                aria-label="Close"
                @click="closeSuccessModal"
              ></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
              <!-- Banner chúc mừng & trạng thái -->
              <div class="text-center mb-4">
                <div class="success-icon-wrap mx-auto mb-3 d-flex align-items-center justify-content-center">
                  <i class="bi bi-bookmark-check-fill fs-1 text-teal"></i>
                </div>
                <h4 class="fw-bold text-navy mb-1">Mã Đặt Phòng: #{{ bookingSuccessResult.maDatPhong }}</h4>
                <div class="mt-2">
                  <span class="badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-clock-history me-1"></i> Trạng thái: {{ bookingSuccessResult.trangThai }}
                  </span>
                </div>
                <p class="text-muted small mt-2 mb-0">
                  Đơn đặt phòng của quý khách đã được lưu trên hệ thống và đang chờ thanh toán tiền cọc để hoàn tất xác nhận.
                </p>
              </div>

              <!-- Chi tiết đơn đặt phòng -->
              <div class="bg-light p-3 rounded-4 border mb-4">
                <h6 class="fw-bold text-navy mb-3 border-bottom pb-2">
                  <i class="bi bi-info-circle me-1 text-teal"></i> Thông tin chi tiết đơn lưu trú:
                </h6>
                <div class="row g-3">
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Ngày nhận phòng:</span>
                    <strong class="text-navy">{{ formatDateTime(bookingSuccessResult.ngayNhanPhong) }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Ngày trả phòng:</span>
                    <strong class="text-navy">{{ formatDateTime(bookingSuccessResult.ngayTraPhong) }}</strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Số lượng khách:</span>
                    <strong class="text-navy">{{ bookingSuccessResult.soLuongKhach }} người</strong>
                  </div>
                  <div class="col-sm-6">
                    <span class="small text-muted d-block">Số tiền cọc cần thanh toán:</span>
                    <strong class="text-danger fs-5">{{ formatCurrency(bookingSuccessResult.tienCoc) }}</strong>
                  </div>
                </div>

                <!-- Danh sách các phòng đã đặt -->
                <div v-if="bookingSuccessResult.danhSachPhong && bookingSuccessResult.danhSachPhong.length > 0" class="mt-3 pt-3 border-top">
                  <span class="small text-muted fw-bold d-block mb-2">Các phòng đã được giữ chỗ:</span>
                  <div class="row g-2">
                    <div
                      v-for="room in bookingSuccessResult.danhSachPhong"
                      :key="room.id"
                      class="col-sm-6"
                    >
                      <div class="p-2 bg-white rounded-3 border d-flex justify-content-between align-items-center small">
                        <div>
                          <strong class="text-navy">{{ room.tenPhong || ('Phòng #' + room.maPhong) }}</strong>
                          <span class="text-muted ms-1">({{ room.soLuongKhach }} khách)</span>
                        </div>
                        <span class="badge bg-teal-subtle text-teal">
                          {{ formatCurrency(room.giaPhongApDung) }}/đêm
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Lưu ý quan trọng về xác nhận đơn -->
              <div class="alert alert-warning border-warning py-2 px-3 rounded-3 small d-flex align-items-start gap-2 mb-0">
                <i class="bi bi-shield-exclamation text-warning flex-shrink-0 mt-1"></i>
                <div>
                  <strong>Lưu ý xác nhận đơn:</strong> Theo quy định khách sạn, đơn đặt phòng chỉ được chuyển sang trạng thái
                  <strong>Đã xác nhận</strong> sau khi quý khách hoàn tất thanh toán số tiền cọc
                  <strong>{{ formatCurrency(bookingSuccessResult.tienCoc) }}</strong>.
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light px-4 py-3 border-0 d-flex justify-content-between">
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3 px-3"
                @click="closeSuccessModal"
              >
                <i class="bi bi-plus-circle me-1"></i> Tạo đơn khác
              </button>
              <div class="d-flex gap-2">
                <router-link
                  to="/my-bookings"
                  class="btn btn-outline-navy rounded-3 px-3"
                  @click="closeSuccessModal"
                >
                  <i class="bi bi-journal-text me-1"></i> Lịch sử đặt phòng
                </router-link>
                <router-link
                  :to="{ path: '/payment', query: { maDatPhong: bookingSuccessResult.maDatPhong, tienCoc: bookingSuccessResult.tienCoc } }"
                  class="btn btn-teal text-white rounded-3 px-4 fw-semibold"
                  @click="closeSuccessModal"
                >
                  <i class="bi bi-credit-card-2-front-fill me-1"></i> Tiếp tục thanh toán cọc
                </router-link>
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
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

// 1. Trạng thái người dùng & phân quyền
const currentUser = ref({})
const isReceptionist = computed(() => {
  const role = (currentUser.value.vaiTro || '').toLowerCase().trim()
  return role === 'lễ tân' || role === 'quản lý' || role === 'admin'
})

// 2. Trạng thái nạp dữ liệu & biểu mẫu
const isLoadingRooms = ref(false)
const isSubmitting = ref(false)
const roomList = ref([])
const customerList = ref([])
const filterFloor = ref('')

// Trạng thái thông báo lỗi & xung đột (409, 422, 500)
const errorMessage = ref('')
const fieldErrors = ref({})
const conflictError = ref(null)
const bookingSuccessResult = ref(null)

// 3. Khởi tạo đối tượng Form theo đúng tên cột CSDL cdio4.sql & API Specification
const form = reactive({
  maKhachHang: null,
  ngayNhanPhong: '',
  ngayTraPhong: '',
  soLuongKhach: 1,
  tienCoc: 0,
  danhSachPhongMap: {}, // Key: roomId, Value: { soLuongKhach: 1, roomData: {} }
})

// Thiết lập ngày mặc định (Ngày mai 14:00 -> Ngày kia 12:00)
const initDefaultDates = () => {
  const now = new Date()
  const tomorrow = new Date(now)
  tomorrow.setDate(now.getDate() + 1)
  tomorrow.setHours(14, 0, 0, 0)

  const dayAfter = new Date(now)
  dayAfter.setDate(now.getDate() + 2)
  dayAfter.setHours(12, 0, 0, 0)

  form.ngayNhanPhong = formatInputDateTime(tomorrow)
  form.ngayTraPhong = formatInputDateTime(dayAfter)
}

const formatInputDateTime = (dateObj) => {
  const pad = (n) => String(n).padStart(2, '0')
  const yyyy = dateObj.getFullYear()
  const mm = pad(dateObj.getMonth() + 1)
  const dd = pad(dateObj.getDate())
  const hh = pad(dateObj.getHours())
  const mi = pad(dateObj.getMinutes())
  return `${yyyy}-${mm}-${dd}T${hh}:${mi}`
}

// 4. Các Computed Properties tính toán chi phí & thời gian
const calculatedNights = computed(() => {
  if (!form.ngayNhanPhong || !form.ngayTraPhong) return 1
  const start = new Date(form.ngayNhanPhong)
  const end = new Date(form.ngayTraPhong)
  const diffTime = end.getTime() - start.getTime()
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return Math.max(1, diffDays)
})

const selectedRoomIds = computed(() => Object.keys(form.danhSachPhongMap).map(Number))
const selectedRoomCount = computed(() => selectedRoomIds.value.length)

const selectedRoomsList = computed(() => {
  return selectedRoomIds.value.map((id) => {
    const item = form.danhSachPhongMap[id]
    const r = item.roomData || {}
    return {
      maPhong: id,
      tenPhong: r.tenPhong || `Phòng ${id}`,
      tenLoai: r.loaiPhong?.tenLoai || 'Tiêu chuẩn',
      giaTieuChuan: r.loaiPhong?.giaTieuChuan || 0,
      soLuongKhach: item.soLuongKhach || 1,
    }
  })
})

const totalRoomAmount = computed(() => {
  const nights = calculatedNights.value
  return selectedRoomsList.value.reduce((total, r) => {
    return total + r.giaTieuChuan * nights
  }, 0)
})

const defaultDeposit = computed(() => {
  return Math.round(totalRoomAmount.value * 0.3)
})

// Tự động cập nhật tiền cọc mặc định khi chọn phòng hoặc thay đổi ngày
const updateAutoDeposit = () => {
  form.tienCoc = defaultDeposit.value
}

// Lọc danh sách phòng theo tầng
const filteredRooms = computed(() => {
  let list = roomList.value
  if (filterFloor.value !== '') {
    list = list.filter((r) => r.tang === Number(filterFloor.value))
  }
  return list
})

// Thông tin khách hàng được chọn (cho Lễ tân)
const selectedCustomerInfo = computed(() => {
  if (!form.maKhachHang || !customerList.value.length) return null
  return customerList.value.find((c) => c.id === form.maKhachHang) || null
})

// 5. Thao tác chọn / bỏ chọn phòng
const isRoomSelected = (roomId) => {
  return Boolean(form.danhSachPhongMap[roomId])
}

const getRoomSelection = (roomId) => {
  return form.danhSachPhongMap[roomId] || { soLuongKhach: 1 }
}

const toggleSelectRoom = (room) => {
  if (room.trangThai !== 'Sẵn sàng' && !isRoomSelected(room.id)) {
    return
  }
  if (isRoomSelected(room.id)) {
    delete form.danhSachPhongMap[room.id]
  } else {
    form.danhSachPhongMap[room.id] = {
      soLuongKhach: Math.min(2, room.loaiPhong?.sucChuaToiDa || 2),
      roomData: room,
    }
  }
  updateAutoDeposit()
}

// 6. Chọn nhanh khoảng thời gian
const setQuickDates = (nights) => {
  const start = form.ngayNhanPhong ? new Date(form.ngayNhanPhong) : new Date()
  if (isNaN(start.getTime())) {
    initDefaultDates()
    return
  }
  start.setHours(14, 0, 0, 0)
  const end = new Date(start)
  end.setDate(start.getDate() + nights)
  end.setHours(12, 0, 0, 0)

  form.ngayNhanPhong = formatInputDateTime(start)
  form.ngayTraPhong = formatInputDateTime(end)
  updateAutoDeposit()
}

// 7. Gọi API trực tiếp bằng Axios (Theo chuẩn quy định Coding Conventions)
const fetchRooms = async () => {
  isLoadingRooms.value = true
  const token = localStorage.getItem('access_token')

  try {
    const res = await axios.get('/api/v1/rooms', {
      headers: { Authorization: `Bearer ${token}` },
    })

    if (res.data && res.data.success) {
      roomList.value = res.data.data || []
    }
  } catch (err) {
    console.error('Không thể tải danh sách phòng:', err)
    if (err.response?.status === 401) {
      router.push({ path: '/login', query: { redirect: route.fullPath } })
    }
  } finally {
    isLoadingRooms.value = false
  }
}

const fetchCustomers = async () => {
  if (!isReceptionist.value) return
  const token = localStorage.getItem('access_token')

  try {
    const res = await axios.get('/api/v1/customers', {
      headers: { Authorization: `Bearer ${token}` },
    })
    if (res.data && res.data.success) {
      customerList.value = res.data.data || []
      if (customerList.value.length > 0 && !form.maKhachHang) {
        form.maKhachHang = customerList.value[0].id
      }
    }
  } catch (err) {
    console.error('Không thể nạp danh sách khách hàng:', err)
  }
}

// 8. Submit đơn đặt phòng qua API POST /api/v1/bookings
const submitBooking = async () => {
  errorMessage.value = ''
  fieldErrors.value = {}
  conflictError.value = null

  // Client-side Validation cơ bản
  if (selectedRoomIds.value.length === 0) {
    fieldErrors.value.danhSachPhong = 'Vui lòng chọn ít nhất một phòng lưu trú.'
    return
  }

  if (isReceptionist.value && !form.maKhachHang) {
    fieldErrors.value.maKhachHang = 'Vui lòng chọn hoặc nhập mã khách hàng.'
    return
  }

  const token = localStorage.getItem('access_token')
  if (!token) {
    router.push({ path: '/login', query: { redirect: route.fullPath } })
    return
  }

  // Chuẩn bị payload khớp với hợp đồng API POST /api/v1/bookings
  const payload = {
    ngayNhanPhong: form.ngayNhanPhong.replace('T', ' ') + ':00',
    ngayTraPhong: form.ngayTraPhong.replace('T', ' ') + ':00',
    soLuongKhach: Number(form.soLuongKhach),
    tienCoc: Number(form.tienCoc),
    danhSachPhong: selectedRoomIds.value.map((id) => {
      const item = form.danhSachPhongMap[id]
      return {
        maPhong: Number(id),
        giaPhongApDung: item.roomData?.loaiPhong?.giaTieuChuan || 0,
        soLuongKhach: Number(item.soLuongKhach || 1),
      }
    }),
  }

  if (isReceptionist.value && form.maKhachHang) {
    payload.maKhachHang = Number(form.maKhachHang)
  }

  isSubmitting.value = true

  try {
    const response = await axios.post('/api/v1/bookings', payload, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    })

    if (response.data && response.data.success) {
      bookingSuccessResult.value = response.data.data
    }
  } catch (error) {
    const status = error.response?.status
    const data = error.response?.data

    if (status === 409) {
      // 409 Conflict: Phòng bị trùng lịch
      conflictError.value = {
        message: data?.message || 'Phòng đã được khách khác đặt trong khung giờ yêu cầu.',
        rooms: data?.errors?.phongBiTrung || [],
      }
    } else if (status === 422) {
      // 422 Unprocessable Entity: Lỗi dữ liệu validation
      errorMessage.value = data?.message || 'Dữ liệu đặt phòng không hợp lệ. Vui lòng kiểm tra lại.'
      fieldErrors.value = data?.errors || {}
    } else if (status === 401) {
      // 401 Unauthorized: Hết hạn phiên làm việc
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      router.push({ path: '/login', query: { redirect: route.fullPath } })
    } else if (status === 403) {
      // 403 Forbidden: Sai phân quyền
      errorMessage.value = data?.message || 'Bạn không có quyền thực hiện thao tác đặt phòng này.'
    } else {
      // 500 hoặc lỗi mạng
      errorMessage.value = data?.message || 'Lỗi hệ thống khi tạo đơn đặt phòng. Vui lòng thử lại sau.'
    }
  } finally {
    isSubmitting.value = false
  }
}

// 9. Quick-Fill Demo Helpers phục vụ kiểm thử nhanh
const fillDemoSingleRoom = () => {
  conflictError.value = null
  errorMessage.value = ''
  fieldErrors.value = {}

  initDefaultDates()
  form.soLuongKhach = 1
  form.danhSachPhongMap = {}

  const p101 = roomList.value.find((r) => r.tenPhong === 'P101') || roomList.value[0]
  if (p101) {
    form.danhSachPhongMap[p101.id] = { soLuongKhach: 1, roomData: p101 }
  }
  updateAutoDeposit()
}

const fillDemoMultiRoom = () => {
  conflictError.value = null
  errorMessage.value = ''
  fieldErrors.value = {}

  initDefaultDates()
  form.soLuongKhach = 3
  form.danhSachPhongMap = {}

  const p101 = roomList.value.find((r) => r.tenPhong === 'P101')
  const p201 = roomList.value.find((r) => r.tenPhong === 'P201')

  if (p101) form.danhSachPhongMap[p101.id] = { soLuongKhach: 1, roomData: p101 }
  if (p201) form.danhSachPhongMap[p201.id] = { soLuongKhach: 2, roomData: p201 }
  updateAutoDeposit()
}

const fillDemoConflict = () => {
  // Đặt lịch trùng với đơn mẫu sẵn trong DB (2026-11-14 đến 2026-11-17, phòng P101)
  conflictError.value = null
  errorMessage.value = ''
  fieldErrors.value = {}

  form.ngayNhanPhong = '2026-11-15T14:00'
  form.ngayTraPhong = '2026-11-16T12:00'
  form.soLuongKhach = 1
  form.danhSachPhongMap = {}

  const p101 = roomList.value.find((r) => r.tenPhong === 'P101') || roomList.value[0]
  if (p101) {
    form.danhSachPhongMap[p101.id] = { soLuongKhach: 1, roomData: p101 }
  }
  updateAutoDeposit()
}

const fillDemoOverCapacity = () => {
  conflictError.value = null
  errorMessage.value = ''
  fieldErrors.value = {}

  initDefaultDates()
  form.soLuongKhach = 10 // Vượt sức chứa
  form.danhSachPhongMap = {}

  const p101 = roomList.value.find((r) => r.tenPhong === 'P101') || roomList.value[0]
  if (p101) {
    // P101 Single Room chỉ chứa 1 người, gán 10 người để kích hoạt 422
    form.danhSachPhongMap[p101.id] = { soLuongKhach: 10, roomData: p101 }
  }
  updateAutoDeposit()
}

const resetForm = () => {
  conflictError.value = null
  errorMessage.value = ''
  fieldErrors.value = {}
  bookingSuccessResult.value = null
  form.danhSachPhongMap = {}
  initDefaultDates()
  form.soLuongKhach = 1
  form.tienCoc = 0
}

const closeSuccessModal = () => {
  bookingSuccessResult.value = null
  resetForm()
}

// 10. Formatters tiền tệ & ngày tháng
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

// 11. Lifecycle Hooks
onMounted(async () => {
  try {
    const userStr = localStorage.getItem('user')
    currentUser.value = userStr ? JSON.parse(userStr) : {}
  } catch (e) {
    currentUser.value = {}
  }

  initDefaultDates()
  await fetchRooms()

  if (isReceptionist.value) {
    await fetchCustomers()
  }

  // Nếu người dùng chọn phòng từ màn hình trước thông qua query parameter
  if (route.query.maPhong && roomList.value.length > 0) {
    const preRoom = roomList.value.find((r) => r.id === Number(route.query.maPhong))
    if (preRoom) {
      toggleSelectRoom(preRoom)
    }
  }
})
</script>

<style scoped>
.booking-page {
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

.text-teal {
  color: #0f766e !important;
}

.bg-teal {
  background-color: #0f766e !important;
}

.bg-teal-subtle-light {
  background-color: #f0fdfa !important;
}

.bg-teal-subtle {
  background-color: #ccfbf1 !important;
}

.border-teal {
  border-color: #0f766e !important;
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

.btn-navy {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
  color: #ffffff;
}

.btn-navy:hover {
  background-color: #162a45;
  border-color: #162a45;
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

.step-badge {
  width: 28px;
  height: 28px;
  background-color: #1e3a5f;
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}

.room-card {
  transition: all 0.2s ease-in-out;
}

.room-card:hover {
  border-color: #0f766e;
  transform: translateY(-2px);
}

.room-card.selected {
  border-width: 2px !important;
}

.success-icon-wrap {
  width: 72px;
  height: 72px;
  background-color: #ccfbf1;
  border-radius: 50%;
}

.cursor-not-allowed {
  cursor: not-allowed;
}
</style>
