<template>
  <div class="profile-page min-vh-100 py-4">
    <div class="container-fluid px-3 px-lg-5">

      <!-- ================================================================= -->
      <!-- 1. QUICK-TEST TOOLBAR (DÀNH CHO TESTER, GIÁO VIÊN & ĐÁNH GIÁ NHANH) -->
      <!-- ================================================================= -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="bg-navy text-white px-3 py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2 small">
            <span class="badge bg-warning text-dark fw-bold px-2 py-1">Quick-Test Toolbar</span>
            <span class="text-white-50">Chuyển đổi tài khoản nhanh để kiểm thử phân quyền & hồ sơ (UC20 / UC04)</span>
          </div>
          <div class="small text-white-50 d-flex align-items-center gap-2">
            <span>Tài khoản hiện tại:</span>
            <span class="fw-bold text-white">{{ currentUser?.tenDangNhap || 'Chưa đăng nhập' }}</span>
            <span v-if="currentUser?.vaiTro" class="badge bg-teal text-white">{{ currentUser.vaiTro }}</span>
          </div>
        </div>
        <div class="card-body p-3 bg-light-subtle d-flex flex-wrap gap-2 align-items-center">
          <span class="small text-muted fw-semibold me-1">Chọn vai trò thử nghiệm:</span>

          <button
            class="btn btn-sm btn-outline-teal rounded-pill d-inline-flex align-items-center gap-1.5"
            :class="{ active: currentUser?.tenDangNhap === 'khachhang1' }"
            @click="switchAccount('khachhang1', 'Password123@')"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-person"></i>
            <span>Khách hàng 1 (khachhang1)</span>
          </button>

          <button
            class="btn btn-sm btn-outline-teal rounded-pill d-inline-flex align-items-center gap-1.5"
            :class="{ active: currentUser?.tenDangNhap === 'minhnguyen@gmail.com' }"
            @click="switchAccount('minhnguyen@gmail.com', '123456')"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-person-check"></i>
            <span>Khách hàng 2 (minhnguyen)</span>
          </button>

          <button
            class="btn btn-sm btn-outline-primary rounded-pill d-inline-flex align-items-center gap-1.5"
            :class="{ active: currentUser?.tenDangNhap === 'admin' }"
            @click="switchAccount('admin', 'Password123@')"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-shield-lock"></i>
            <span>Quản lý (admin)</span>
          </button>

          <button
            class="btn btn-sm btn-outline-info rounded-pill d-inline-flex align-items-center gap-1.5"
            :class="{ active: currentUser?.tenDangNhap === 'letan01' }"
            @click="switchAccount('letan01', '123456')"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-headset"></i>
            <span>Lễ tân (letan01)</span>
          </button>

          <button
            class="btn btn-sm btn-outline-warning rounded-pill d-inline-flex align-items-center gap-1.5"
            :class="{ active: currentUser?.tenDangNhap === 'buongphong01' }"
            @click="switchAccount('buongphong01', '123456')"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-stars"></i>
            <span>Buồng phòng (buongphong01)</span>
          </button>

          <button
            class="btn btn-sm btn-outline-danger rounded-pill d-inline-flex align-items-center gap-1.5 ms-auto"
            @click="logoutForTesting"
            :disabled="isSwitchingAccount"
          >
            <i class="bi bi-box-arrow-right"></i>
            <span>Xóa Token (Thử chặn 401)</span>
          </button>
        </div>
      </div>

      <!-- ================================================================= -->
      <!-- 2. TRƯỜNG HỢP CHƯA ĐĂNG NHẬP (401 UNAUTHENTICATED) -->
      <!-- ================================================================= -->
      <div v-if="!hasToken" class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-5">
        <div class="avatar-lg bg-danger-subtle text-danger mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center">
          <i class="bi bi-shield-x fs-1"></i>
        </div>
        <h4 class="fw-bold text-navy mb-2">Chưa xác thực danh tính (401 Unauthorized)</h4>
        <p class="text-muted mx-auto mb-4" style="max-width: 540px;">
          Bạn cần đăng nhập bằng tài khoản người dùng hoặc nhân viên để truy cập và quản lý hồ sơ cá nhân của mình.
        </p>
        <div class="d-flex justify-content-center gap-2">
          <router-link to="/dang-nhap" class="btn btn-navy px-4 rounded-pill">
            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập ngay
          </router-link>
          <button class="btn btn-outline-teal px-4 rounded-pill" @click="switchAccount('khachhang1', 'Password123@')">
            <i class="bi bi-lightning-charge me-1"></i> Đăng nhập nhanh khách hàng 1
          </button>
        </div>
      </div>

      <!-- ================================================================= -->
      <!-- 3. GIAO DIỆN CHÍNH KHI ĐÃ ĐĂNG NHẬP -->
      <!-- ================================================================= -->
      <div v-else>

        <!-- PAGE HEADER -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-navy px-3 py-1.5 rounded-pill text-uppercase fs-7 fw-semibold">
                {{ userProfile?.vaiTro || 'Tài khoản' }}
              </span>
              <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">
                <i class="bi bi-check-circle-fill me-1"></i> {{ userProfile?.trangThai || 'Hoạt động' }}
              </span>
              <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill small">
                Mã TK: #{{ userProfile?.id }}
              </span>
            </div>
            <h2 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
              <i class="bi bi-person-badge"></i>
              <span>Hồ Sơ Cá Nhân</span>
            </h2>
            <p class="text-muted mb-0 small">
              Xem và cập nhật thông tin cá nhân, liên hệ và đồng bộ trạng thái tài khoản.
            </p>
          </div>

          <!-- NÚT HÀNH ĐỘNG HEADER -->
          <div class="d-flex align-items-center gap-2">
            <template v-if="!isEditMode">
              <button
                class="btn btn-navy rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                @click="enableEditMode"
                :disabled="isLoading"
              >
                <i class="bi bi-pencil-square"></i>
                <span>Chỉnh sửa hồ sơ</span>
              </button>
              <button
                class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5"
                @click="fetchProfileData"
                :disabled="isLoading"
                title="Làm mới thông tin từ máy chủ"
              >
                <i class="bi bi-arrow-clockwise" :class="{ 'spin-anim': isLoading }"></i>
                <span class="d-none d-sm-inline">Làm mới</span>
              </button>
            </template>
            <template v-else>
              <button
                class="btn btn-outline-secondary rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2"
                @click="cancelEditMode"
                :disabled="isSaving"
              >
                <i class="bi bi-x-circle"></i>
                <span>Hủy bỏ</span>
              </button>
              <button
                class="btn btn-teal text-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                @click="handleSaveProfile"
                :disabled="isSaving"
              >
                <span v-if="isSaving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <i v-else class="bi bi-check-lg"></i>
                <span>{{ isSaving ? 'Đang lưu...' : 'Lưu thay đổi' }}</span>
              </button>
            </template>
          </div>
        </div>

        <!-- THÔNG BÁO ALERT (SUCCESS / ERROR) -->
        <div
          v-if="successMessage"
          class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2"
          role="alert"
        >
          <i class="bi bi-check-circle-fill fs-5 text-success"></i>
          <div>
            <strong>Thành công!</strong> {{ successMessage }}
          </div>
          <button type="button" class="btn-close" @click="successMessage = ''" aria-label="Close"></button>
        </div>

        <div
          v-if="errorMessage"
          class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2"
          role="alert"
        >
          <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
          <div>
            <strong>Lỗi thao tác:</strong> {{ errorMessage }}
          </div>
          <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
        </div>

        <!-- SPINNER LOADING KHI ĐANG NẠP DỮ LIỆU BAN ĐẦU -->
        <div v-if="isLoading && !userProfile" class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
          <div class="spinner-border text-teal mb-3" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Đang tải...</span>
          </div>
          <p class="text-muted fw-semibold mb-0">Đang đồng bộ dữ liệu hồ sơ cá nhân từ hệ thống...</p>
        </div>

        <!-- NỘI DUNG 2 CỘT RESPONSIVE -->
        <div v-else class="row g-4">

          <!-- ============================================================= -->
          <!-- CỘT TRÁI: THẺ TỔNG QUAN ĐỊNH DANH (USER IDENTITY CARD) -->
          <!-- ============================================================= -->
          <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4 sticky-top-card">
              <!-- Background banner -->
              <div class="profile-card-cover bg-navy p-4 text-center position-relative">
                <div class="avatar-wrapper mx-auto">
                  <div class="avatar-initials rounded-circle shadow-lg d-flex align-items-center justify-content-center text-white fw-bold">
                    {{ getInitials(formData.hoTen || userProfile?.hoTen || userProfile?.tenDangNhap) }}
                  </div>
                </div>
              </div>

              <div class="card-body pt-5 px-4 pb-4 text-center">
                <h4 class="fw-bold text-navy mb-1">{{ formData.hoTen || userProfile?.hoTen || 'Người dùng' }}</h4>
                <div class="text-muted small mb-2 d-flex align-items-center justify-content-center gap-1">
                  <i class="bi bi-at"></i>
                  <span>{{ userProfile?.tenDangNhap }}</span>
                </div>

                <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
                  <span class="badge rounded-pill px-3 py-1.5 fw-semibold" :class="getRoleBadgeClass(userProfile?.vaiTro)">
                    <i class="bi bi-person-fill me-1"></i> {{ userProfile?.vaiTro }}
                  </span>
                  <span class="badge bg-success text-white rounded-pill px-2.5 py-1.5 fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> {{ userProfile?.trangThai }}
                  </span>
                </div>

                <hr class="my-3 opacity-25">

                <!-- DANH SÁCH THÔNG SỐ ĐỊNH DANH (READ-ONLY) -->
                <div class="text-start">
                  <div class="text-uppercase small fw-bold text-muted mb-3" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                    Thông tin định danh hệ thống:
                  </div>

                  <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light-subtle">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-key text-navy"></i> Mã tài khoản
                    </span>
                    <span class="fw-semibold text-navy small">#{{ userProfile?.id }}</span>
                  </div>

                  <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light-subtle">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-person-vcard text-navy"></i> Số CCCD / Định danh
                    </span>
                    <span class="fw-semibold text-navy small">
                      {{ userProfile?.khachHang?.cccd || userProfile?.nhanVien?.cccd || 'Đã liên kết' }}
                    </span>
                  </div>

                  <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light-subtle">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-database text-navy"></i> Loại bản ghi CSDL
                    </span>
                    <span class="badge bg-light text-dark border small">
                      {{ isCustomerRole ? 'khach_hang' : 'nhan_vien' }}
                    </span>
                  </div>

                  <div v-if="!isCustomerRole && userProfile?.nhanVien" class="d-flex align-items-center justify-content-between py-2 border-bottom border-light-subtle">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-briefcase text-navy"></i> Chức vụ công việc
                    </span>
                    <span class="fw-semibold text-teal small">{{ userProfile.nhanVien.chucVu }}</span>
                  </div>

                  <div v-if="!isCustomerRole && userProfile?.nhanVien" class="d-flex align-items-center justify-content-between py-2 border-bottom border-light-subtle">
                    <span class="text-muted small d-flex align-items-center gap-2">
                      <i class="bi bi-activity text-navy"></i> Tình trạng làm việc
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                      {{ userProfile.nhanVien.trangThai }}
                    </span>
                  </div>
                </div>

                <!-- NÚT PHÍM TẮT ĐIỀU HƯỚNG TÙY VAI TRÒ -->
                <div class="mt-4 pt-2">
                  <div v-if="isCustomerRole" class="d-grid gap-2">
                    <router-link to="/my-bookings" class="btn btn-outline-teal btn-sm rounded-pill py-2 d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-journal-text"></i>
                      <span>Xem lịch sử đặt phòng</span>
                    </router-link>
                    <router-link to="/booking" class="btn btn-light btn-sm rounded-pill py-2 border text-navy d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-calendar-plus"></i>
                      <span>Đặt phòng nghỉ mới</span>
                    </router-link>
                  </div>

                  <div v-else class="d-grid gap-2">
                    <router-link to="/admin/dashboard" class="btn btn-outline-navy btn-sm rounded-pill py-2 d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-speedometer2"></i>
                      <span>Vào Bảng Điều Khiển PMS</span>
                    </router-link>
                    <router-link to="/receptionist/room-map" class="btn btn-light btn-sm rounded-pill py-2 border text-teal d-flex align-items-center justify-content-center gap-2">
                      <i class="bi bi-map"></i>
                      <span>Sơ đồ phòng trực tuyến</span>
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================================= -->
          <!-- CỘT PHẢI: CHI TIẾT HỒ SƠ & FORM CHỈNH SỬA TƯƠNG TÁC -->
          <!-- ============================================================= -->
          <div class="col-12 col-lg-8">

            <!-- THẺ FORM THÔNG TIN LIÊN HỆ -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
              <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                  <h5 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-card-text text-teal"></i>
                    <span>Thông Tin Liên Hệ & Cá Nhân</span>
                  </h5>
                  <p class="text-muted small mb-0">
                    {{ isEditMode ? 'Chỉnh sửa các trường thông tin bên dưới và nhấn "Lưu thay đổi".' : 'Chế độ xem chỉ đọc. Nhấn "Chỉnh sửa hồ sơ" để thay đổi thông tin.' }}
                  </p>
                </div>

                <span v-if="isEditMode" class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-semibold">
                  <i class="bi bi-pencil-fill me-1"></i> Đang chỉnh sửa
                </span>
                <span v-else class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill">
                  <i class="bi bi-eye-fill me-1"></i> Đang xem chỉ đọc
                </span>
              </div>

              <div class="card-body p-4">
                <form @submit.prevent="handleSaveProfile">

                  <!-- KHU VỰC CÁC TRƯỜNG CHO PHÉP CẬP NHẬT -->
                  <div class="row g-3">

                    <!-- HỌ VÀ TÊN -->
                    <div class="col-12 col-md-6">
                      <label class="form-label small fw-bold text-navy">
                        Họ và tên <span class="text-danger">*</span>
                      </label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                          <i class="bi bi-person"></i>
                        </span>
                        <input
                          v-if="isEditMode"
                          type="text"
                          class="form-control border-start-0 ps-0"
                          :class="{ 'is-invalid': fieldErrors.hoTen }"
                          v-model="formData.hoTen"
                          placeholder="Nhập họ và tên đầy đủ"
                          required
                          maxlength="100"
                        />
                        <div v-else class="form-control bg-light border-start-0 ps-0 text-navy fw-semibold">
                          {{ userProfile?.hoTen || 'Chưa cập nhật' }}
                        </div>
                      </div>
                      <div v-if="fieldErrors.hoTen" class="invalid-feedback d-block small mt-1">
                        {{ fieldErrors.hoTen[0] }}
                      </div>
                    </div>

                    <!-- SỐ ĐIỆN THOẠI -->
                    <div class="col-12 col-md-6">
                      <label class="form-label small fw-bold text-navy">
                        Số điện thoại liên hệ <span class="text-danger">*</span>
                      </label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                          <i class="bi bi-telephone"></i>
                        </span>
                        <input
                          v-if="isEditMode"
                          type="tel"
                          class="form-control border-start-0 ps-0"
                          :class="{ 'is-invalid': fieldErrors.soDienThoai }"
                          v-model="formData.soDienThoai"
                          placeholder="Ví dụ: 0912345678"
                          required
                          maxlength="20"
                        />
                        <div v-else class="form-control bg-light border-start-0 ps-0 text-navy fw-semibold">
                          {{ userProfile?.soDienThoai || 'Chưa cập nhật' }}
                        </div>
                      </div>
                      <div v-if="fieldErrors.soDienThoai" class="invalid-feedback d-block small mt-1">
                        {{ fieldErrors.soDienThoai[0] }}
                      </div>
                    </div>

                    <!-- EMAIL (BẮT BUỘC CHO KHÁCH HÀNG) -->
                    <div class="col-12 col-md-6">
                      <label class="form-label small fw-bold text-navy">
                        Địa chỉ Email
                        <span v-if="isCustomerRole" class="text-danger">*</span>
                        <span v-else class="text-muted fw-normal small">(Tùy chọn cho nhân viên)</span>
                      </label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                          <i class="bi bi-envelope"></i>
                        </span>
                        <input
                          v-if="isEditMode"
                          type="email"
                          class="form-control border-start-0 ps-0"
                          :class="{ 'is-invalid': fieldErrors.email }"
                          v-model="formData.email"
                          placeholder="Ví dụ: hoten@gmail.com"
                          :required="isCustomerRole"
                          maxlength="100"
                        />
                        <div v-else class="form-control bg-light border-start-0 ps-0 text-navy fw-semibold">
                          {{ userProfile?.email || 'Chưa cập nhật' }}
                        </div>
                      </div>
                      <div v-if="fieldErrors.email" class="invalid-feedback d-block small mt-1">
                        {{ fieldErrors.email[0] }}
                      </div>
                    </div>

                    <!-- NGÀY SINH (CHO HỒ SƠ NHÂN VIÊN) HOẶC ĐỊA CHỈ (CHO KHÁCH HÀNG) -->
                    <div v-if="!isCustomerRole" class="col-12 col-md-6">
                      <label class="form-label small fw-bold text-navy">
                        Ngày sinh nhân sự
                      </label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                          <i class="bi bi-calendar-event"></i>
                        </span>
                        <input
                          v-if="isEditMode"
                          type="date"
                          class="form-control border-start-0 ps-0"
                          :class="{ 'is-invalid': fieldErrors.ngaySinh }"
                          v-model="formData.ngaySinh"
                        />
                        <div v-else class="form-control bg-light border-start-0 ps-0 text-navy fw-semibold">
                          {{ userProfile?.nhanVien?.ngaySinh || 'Chưa cập nhật' }}
                        </div>
                      </div>
                      <div v-if="fieldErrors.ngaySinh" class="invalid-feedback d-block small mt-1">
                        {{ fieldErrors.ngaySinh[0] }}
                      </div>
                    </div>

                    <!-- ĐỊA CHỈ LIÊN LẠC -->
                    <div :class="isCustomerRole ? 'col-12' : 'col-12 col-md-6'">
                      <label class="form-label small fw-bold text-navy">
                        Địa chỉ thường trú / liên hệ
                      </label>
                      <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                          <i class="bi bi-geo-alt"></i>
                        </span>
                        <input
                          v-if="isEditMode"
                          type="text"
                          class="form-control border-start-0 ps-0"
                          :class="{ 'is-invalid': fieldErrors.diaChi }"
                          v-model="formData.diaChi"
                          placeholder="Ví dụ: 123 Đường Bạch Đằng, Đà Nẵng"
                          maxlength="255"
                        />
                        <div v-else class="form-control bg-light border-start-0 ps-0 text-navy fw-semibold">
                          {{ userProfile?.diaChi || 'Chưa cập nhật' }}
                        </div>
                      </div>
                      <div v-if="fieldErrors.diaChi" class="invalid-feedback d-block small mt-1">
                        {{ fieldErrors.diaChi[0] }}
                      </div>
                    </div>

                  </div>

                  <!-- ======================================================= -->
                  <!-- KHU VỰC THÔNG TIN BẢO MẬT & CỐ ĐỊNH (PROHIBITED RULES) -->
                  <!-- ======================================================= -->
                  <div class="mt-4 pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lock-fill text-muted"></i>
                        <span class="small fw-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">
                          Thông tin hệ thống được bảo vệ (Không cho phép người dùng tự sửa)
                        </span>
                      </div>
                      <span class="badge bg-light text-secondary border small">
                        <i class="bi bi-shield-lock me-1"></i> Prohibited Fields
                      </span>
                    </div>

                    <div class="row g-3">
                      <!-- TÊN ĐĂNG NHẬP -->
                      <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">Tên đăng nhập hệ thống</label>
                        <div class="input-group">
                          <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-lock"></i>
                          </span>
                          <input
                            type="text"
                            class="form-control border-start-0 ps-0 bg-light text-muted"
                            :value="userProfile?.tenDangNhap"
                            disabled
                            readonly
                          />
                        </div>
                      </div>

                      <!-- VAI TRÒ TÀI KHOẢN -->
                      <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">Vai trò & Phân quyền</label>
                        <div class="input-group">
                          <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-shield-shaded"></i>
                          </span>
                          <input
                            type="text"
                            class="form-control border-start-0 ps-0 bg-light text-muted"
                            :value="userProfile?.vaiTro"
                            disabled
                            readonly
                          />
                        </div>
                      </div>

                      <!-- SỐ CCCD ĐỊNH DANH -->
                      <div class="col-12 col-md-4">
                        <label class="form-label small text-muted">Số CCCD / CMND định danh</label>
                        <div class="input-group">
                          <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="bi bi-credit-card-2-front"></i>
                          </span>
                          <input
                            type="text"
                            class="form-control border-start-0 ps-0 bg-light text-muted"
                            :value="userProfile?.khachHang?.cccd || userProfile?.nhanVien?.cccd || 'Không khả dụng'"
                            disabled
                            readonly
                          />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ACTION BAR DƯỚI FORM KHI Ở EDIT MODE -->
                  <div v-if="isEditMode" class="mt-4 pt-3 border-top d-flex align-items-center justify-content-end gap-2">
                    <button
                      type="button"
                      class="btn btn-outline-secondary rounded-pill px-4 py-2"
                      @click="cancelEditMode"
                      :disabled="isSaving"
                    >
                      Hủy bỏ
                    </button>
                    <button
                      type="submit"
                      class="btn btn-teal text-white rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm"
                      :disabled="isSaving"
                    >
                      <span v-if="isSaving" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                      <i v-else class="bi bi-check2-circle"></i>
                      <span>{{ isSaving ? 'Đang lưu...' : 'Lưu hồ sơ' }}</span>
                    </button>
                  </div>

                </form>
              </div>
            </div>

            <!-- CARD HƯỚNG DẪN & CHÍNH SÁCH BẢO MẬT -->
            <div class="card border-0 shadow-sm rounded-4 bg-light-subtle p-3.5 border-start border-4 border-teal">
              <div class="d-flex gap-3">
                <i class="bi bi-info-circle-fill fs-4 text-teal mt-1"></i>
                <div class="small text-muted">
                  <div class="fw-bold text-navy mb-1">Chính sách bảo mật & tính toàn vẹn dữ liệu:</div>
                  <ul class="mb-0 ps-3">
                    <li>Họ tên, email và số điện thoại mới sẽ được cập nhật đồng bộ tức thì sang cơ sở dữ liệu hồ sơ.</li>
                    <li>Để thay đổi tên đăng nhập, số CCCD hoặc phân quyền vai trò công việc, vui lòng liên hệ trực tiếp Quản trị viên hệ thống.</li>
                    <li>Mọi giao dịch lịch sử và đơn đặt phòng đã hoàn tất được bảo toàn nguyên trạng, không bị xáo trộn khi đổi thông tin liên lạc.</li>
                  </ul>
                </div>
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
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// =========================================================================
// 1. STATE MANAGEMENT
// =========================================================================
const hasToken = computed(() => !!localStorage.getItem('access_token'))
const currentUser = ref(null)
const userProfile = ref(null)

const isLoading = ref(false)
const isSaving = ref(false)
const isEditMode = ref(false)
const isSwitchingAccount = ref(false)

const successMessage = ref('')
const errorMessage = ref('')
const fieldErrors = ref({})

// Form state
const formData = reactive({
  hoTen: '',
  email: '',
  soDienThoai: '',
  diaChi: '',
  ngaySinh: '',
})

// Kiểm tra vai trò
const isCustomerRole = computed(() => {
  const role = (userProfile.value?.vaiTro || '').toLowerCase().trim()
  return role === 'khách hàng' || role === 'customer' || !role
})

// Lấy chữ cái viết tắt đại diện cho avatar
const getInitials = (name) => {
  if (!name) return 'U'
  const words = name.trim().split(' ').filter(Boolean)
  if (words.length === 1) return words[0].substring(0, 2).toUpperCase()
  return (words[0][0] + words[words.length - 1][0]).toUpperCase()
}

// Badge color theo vai trò
const getRoleBadgeClass = (role) => {
  const r = (role || '').toLowerCase().trim()
  if (r === 'quản lý' || r === 'admin') return 'bg-danger text-white'
  if (r === 'lễ tân' || r === 'receptionist') return 'bg-primary text-white'
  if (r === 'buồng phòng' || r === 'housekeeping') return 'bg-warning text-dark'
  return 'bg-teal text-white'
}

// Helper lấy headers chứa Bearer Token
const getAuthHeaders = () => {
  const token = localStorage.getItem('access_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

// =========================================================================
// 2. API CALLS: NẠP VÀ CẬP NHẬT HỒ SƠ
// =========================================================================

/**
 * Nạp thông tin tài khoản hiện tại từ GET /api/v1/auth/me
 */
const fetchProfileData = async () => {
  const token = localStorage.getItem('access_token')
  if (!token) {
    currentUser.value = null
    userProfile.value = null
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    const res = await axios.get('/api/v1/auth/me', {
      headers: getAuthHeaders(),
    })

    if (res.data && res.data.success) {
      const u = res.data.data.user
      userProfile.value = u
      currentUser.value = u

      // Đồng bộ vào localStorage để header/sidebar hiển thị đúng tên
      localStorage.setItem('user', JSON.stringify(u))
      if (u.vaiTro) {
        localStorage.setItem('vaiTro', u.vaiTro)
      }

      // Khởi tạo dữ liệu form
      syncFormWithProfile(u)
    }
  } catch (err) {
    console.error('Lỗi khi nạp thông tin tài khoản:', err)
    if (err.response?.status === 401) {
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      localStorage.removeItem('vaiTro')
      currentUser.value = null
      userProfile.value = null
      errorMessage.value = 'Phiên làm việc đã hết hạn hoặc chưa xác thực (401). Vui lòng đăng nhập lại.'
    } else if (err.response?.status === 403) {
      errorMessage.value = 'Bạn không có quyền truy cập thông tin tài khoản này (403).'
    } else {
      errorMessage.value = err.response?.data?.message || 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.'
    }
  } finally {
    isLoading.value = false
  }
}

/**
 * Gán dữ liệu hồ sơ vào form reactive
 */
const syncFormWithProfile = (u) => {
  if (!u) return
  formData.hoTen = u.hoTen || ''
  formData.email = u.email || u.khachHang?.email || ''
  formData.soDienThoai = u.soDienThoai || u.khachHang?.soDienThoai || u.nhanVien?.soDienThoai || ''
  formData.diaChi = u.diaChi || u.khachHang?.diaChi || ''
  formData.ngaySinh = u.nhanVien?.ngaySinh || ''
}

/**
 * Bật chế độ chỉnh sửa
 */
const enableEditMode = () => {
  syncFormWithProfile(userProfile.value)
  fieldErrors.value = {}
  errorMessage.value = ''
  successMessage.value = ''
  isEditMode.value = true
}

/**
 * Hủy chỉnh sửa và khôi phục form
 */
const cancelEditMode = () => {
  syncFormWithProfile(userProfile.value)
  fieldErrors.value = {}
  errorMessage.value = ''
  isEditMode.value = false
}

/**
 * Lưu cập nhật hồ sơ cá nhân qua PUT /api/v1/auth/profile
 */
const handleSaveProfile = async () => {
  fieldErrors.value = {}
  errorMessage.value = ''
  successMessage.value = ''

  // Client-side quick check
  if (!formData.hoTen.trim()) {
    fieldErrors.value.hoTen = ['Họ và tên không được để trống.']
    return
  }
  if (!formData.soDienThoai.trim()) {
    fieldErrors.value.soDienThoai = ['Số điện thoại không được để trống.']
    return
  }
  if (isCustomerRole.value && !formData.email.trim()) {
    fieldErrors.value.email = ['Email không được để trống đối với khách hàng.']
    return
  }

  isSaving.value = true

  // Chuẩn bị payload chuẩn (không gửi các prohibited fields: vaiTro, cccd, id,...)
  const payload = {
    hoTen: formData.hoTen.trim(),
    soDienThoai: formData.soDienThoai.trim(),
  }

  if (formData.email && formData.email.trim()) {
    payload.email = formData.email.trim()
  }

  if (formData.diaChi && formData.diaChi.trim()) {
    payload.diaChi = formData.diaChi.trim()
  }

  if (!isCustomerRole.value && formData.ngaySinh) {
    payload.ngaySinh = formData.ngaySinh
  }

  try {
    const res = await axios.put('/api/v1/auth/profile', payload, {
      headers: getAuthHeaders(),
    })

    if (res.data && res.data.success) {
      successMessage.value = res.data.message || 'Cập nhật hồ sơ cá nhân thành công.'
      isEditMode.value = false

      // Nạp lại thông tin mới nhất
      await fetchProfileData()

      // Tự động tắt alert thông báo sau 5 giây
      setTimeout(() => {
        if (successMessage.value) successMessage.value = ''
      }, 5000)
    }
  } catch (err) {
    console.error('Lỗi khi lưu hồ sơ:', err)
    if (err.response?.status === 422) {
      const data = err.response.data
      fieldErrors.value = data.errors || {}
      errorMessage.value = data.message || 'Dữ liệu gửi lên không hợp lệ. Vui lòng kiểm tra lại từng ô nhập.'
    } else if (err.response?.status === 401) {
      errorMessage.value = 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.'
      localStorage.removeItem('access_token')
      localStorage.removeItem('user')
      currentUser.value = null
      userProfile.value = null
    } else if (err.response?.status === 403) {
      errorMessage.value = 'Không có quyền thực hiện hành động này.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Có lỗi hệ thống khi cập nhật hồ sơ. Vui lòng thử lại.'
    }
  } finally {
    isSaving.value = false
  }
}

// =========================================================================
// 3. QUICK-TEST TOOLBAR HANDLERS
// =========================================================================

/**
 * Đăng nhập nhanh một tài khoản mẫu để kiểm thử
 */
const switchAccount = async (username, password) => {
  isSwitchingAccount.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      password: password,
    })

    if (res.data && res.data.success) {
      const token = res.data.data.access_token
      const user = res.data.data.user

      localStorage.setItem('access_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      if (user.vaiTro) {
        localStorage.setItem('vaiTro', user.vaiTro)
      }

      currentUser.value = user
      isEditMode.value = false

      // Nạp chi tiết hồ sơ
      await fetchProfileData()
      successMessage.value = `Đã chuyển sang tài khoản ${user.hoTen} (${user.vaiTro}).`
    }
  } catch (err) {
    console.error('Lỗi khi chuyển tài khoản:', err)
    errorMessage.value = 'Không thể chuyển tài khoản mẫu. Vui lòng kiểm tra API backend.'
  } finally {
    isSwitchingAccount.value = false
  }
}

/**
 * Xóa token để thử nghiệm chặn 401
 */
const logoutForTesting = () => {
  localStorage.removeItem('access_token')
  localStorage.removeItem('user')
  localStorage.removeItem('vaiTro')
  currentUser.value = null
  userProfile.value = null
  isEditMode.value = false
  successMessage.value = ''
  errorMessage.value = 'Đã xóa Access Token. Thử nghiệm trạng thái chưa đăng nhập (401).'
}

// =========================================================================
// 4. LIFECYCLE HOOKS
// =========================================================================
onMounted(() => {
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      currentUser.value = JSON.parse(userStr)
    } catch {
      currentUser.value = null
    }
  }
  fetchProfileData()
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
  color: #ffffff;
  border-color: #1e3a5f;
  transition: all 0.2s ease;
}

.btn-navy:hover,
.btn-navy:focus {
  background-color: #152943;
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
  transition: all 0.2s ease;
}

.btn-teal:hover,
.btn-teal:focus {
  background-color: #0b5953;
  color: #ffffff;
}

.btn-outline-teal {
  color: #0f766e;
  border-color: #0f766e;
}

.btn-outline-teal:hover,
.btn-outline-teal.active {
  background-color: #0f766e;
  color: #ffffff;
}

.border-teal {
  border-color: #0f766e !important;
}

.profile-page {
  background-color: #f8fafc;
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Profile Card Styling */
.profile-card-cover {
  height: 90px;
}

.avatar-wrapper {
  position: absolute;
  bottom: -45px;
  left: 50%;
  transform: translateX(-50%);
}

.avatar-initials {
  width: 90px;
  height: 90px;
  background: linear-gradient(135deg, #0f766e 0%, #1e3a5f 100%);
  font-size: 2rem;
  border: 4px solid #ffffff;
}

.avatar-lg {
  width: 76px;
  height: 76px;
}

.sticky-top-card {
  position: sticky;
  top: 90px;
  z-index: 10;
}

/* Animations */
.spin-anim {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}
</style>
