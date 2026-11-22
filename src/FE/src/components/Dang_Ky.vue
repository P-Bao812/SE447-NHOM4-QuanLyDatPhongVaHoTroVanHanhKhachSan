<template>
  <div class="register-page py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
          <!-- Card Đăng ký -->
          <div class="card register-card shadow-lg border-0 rounded-4 overflow-hidden">
            <!-- Header của Card -->
            <div class="card-header bg-white border-0 text-center pt-4 pb-2 px-4">
              <div class="brand-logo-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-person-plus-fill fs-3 text-teal"></i>
              </div>
              <h4 class="fw-bold text-navy mb-1">RoomBook PMS</h4>
              <p class="text-muted small mb-0">Hệ thống Quản lý Khách sạn & Resort</p>
            </div>

            <!-- Body của Card -->
            <div class="card-body p-4 pt-2">
              <h5 class="fw-semibold text-center mb-3 text-dark-emphasis">
                Đăng ký tài khoản khách hàng
              </h5>
              <p class="text-center text-muted small mb-4">
                Tạo tài khoản để trải nghiệm đặt phòng trực tuyến, theo dõi lịch sử lưu trú và nhận ưu đãi độc quyền
              </p>

              <!-- Thông báo lỗi chung (422, 500, lỗi mạng) -->
              <div
                v-if="errorMessage"
                class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-3 small"
                role="alert"
              >
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 fs-6"></i>
                <div class="flex-grow-1">{{ errorMessage }}</div>
                <button
                  type="button"
                  class="btn-close py-2"
                  aria-label="Close"
                  @click="errorMessage = ''"
                ></button>
              </div>

              <!-- Màn hình thông báo thành công (201 Created) -->
              <div
                v-if="isRegistered && registeredUser"
                class="success-container text-center py-4 px-3"
              >
                <div class="success-icon-box mx-auto mb-3 d-flex align-items-center justify-content-center">
                  <i class="bi bi-check2-circle fs-1 text-teal"></i>
                </div>
                <h5 class="fw-bold text-navy mb-2">Đăng ký tài khoản thành công!</h5>
                <p class="text-muted small mb-4">
                  Chào mừng <strong class="text-dark">{{ registeredUser.hoTen }}</strong> đến với RoomBook PMS.
                  Tài khoản của bạn đã được kích hoạt với vai trò <strong>{{ registeredUser.vaiTro }}</strong>.
                </p>

                <div class="card bg-light border-0 rounded-3 p-3 text-start mb-4 mx-auto" style="max-width: 420px;">
                  <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Họ và tên:</span>
                    <span class="fw-semibold text-dark">{{ registeredUser.hoTen }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Tên đăng nhập:</span>
                    <span class="fw-semibold text-dark">{{ registeredUser.tenDangNhap }}</span>
                  </div>
                  <div class="d-flex justify-content-between mb-1 small">
                    <span class="text-muted">Email:</span>
                    <span class="fw-semibold text-dark">{{ registeredUser.email }}</span>
                  </div>
                  <div class="d-flex justify-content-between small">
                    <span class="text-muted">Số điện thoại:</span>
                    <span class="fw-semibold text-dark">{{ registeredUser.soDienThoai }}</span>
                  </div>
                </div>

                <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                  <router-link
                    to="/login"
                    class="btn btn-primary-custom px-4 py-2 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm"
                  >
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span>Đến trang Đăng nhập ({{ countdown }}s)</span>
                  </router-link>
                  <button
                    type="button"
                    class="btn btn-outline-secondary px-3 py-2 rounded-3 small"
                    @click="resetForm"
                  >
                    Đăng ký tài khoản khác
                  </button>
                </div>
              </div>

              <!-- Form Đăng ký (Hiển thị khi chưa đăng ký xong) -->
              <form v-else @submit.prevent="handleRegister" novalidate>
                <!-- Nhóm 1: Thông tin cá nhân -->
                <div class="row g-3 mb-3">
                  <!-- Họ và tên -->
                  <div class="col-12 col-md-6">
                    <label for="hoTen" class="form-label fw-semibold small text-navy">
                      Họ và tên <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-person"></i>
                      </span>
                      <input
                        id="hoTen"
                        v-model.trim="form.hoTen"
                        type="text"
                        class="form-control border-start-0 ps-0"
                        :class="{ 'is-invalid': fieldErrors.hoTen }"
                        placeholder="Ví dụ: Nguyễn Văn An"
                        autocomplete="name"
                        :disabled="isLoading"
                        required
                        @input="clearFieldError('hoTen')"
                      />
                    </div>
                    <div v-if="fieldErrors.hoTen" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.hoTen }}
                    </div>
                  </div>

                  <!-- Số điện thoại -->
                  <div class="col-12 col-md-6">
                    <label for="soDienThoai" class="form-label fw-semibold small text-navy">
                      Số điện thoại <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-telephone"></i>
                      </span>
                      <input
                        id="soDienThoai"
                        v-model.trim="form.soDienThoai"
                        type="tel"
                        class="form-control border-start-0 ps-0"
                        :class="{ 'is-invalid': fieldErrors.soDienThoai }"
                        placeholder="Ví dụ: 0912345678"
                        autocomplete="tel"
                        :disabled="isLoading"
                        required
                        @input="clearFieldError('soDienThoai')"
                      />
                    </div>
                    <div v-if="fieldErrors.soDienThoai" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.soDienThoai }}
                    </div>
                  </div>
                </div>

                <!-- Nhóm 2: Thông tin định danh & tài khoản -->
                <div class="row g-3 mb-3">
                  <!-- Email -->
                  <div class="col-12 col-md-6">
                    <label for="email" class="form-label fw-semibold small text-navy">
                      Địa chỉ Email <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-envelope"></i>
                      </span>
                      <input
                        id="email"
                        v-model.trim="form.email"
                        type="email"
                        class="form-control border-start-0 ps-0"
                        :class="{ 'is-invalid': fieldErrors.email }"
                        placeholder="name@example.com"
                        autocomplete="email"
                        :disabled="isLoading"
                        required
                        @input="clearFieldError('email')"
                      />
                    </div>
                    <div v-if="fieldErrors.email" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.email }}
                    </div>
                  </div>

                  <!-- Tên đăng nhập (Tùy chọn) -->
                  <div class="col-12 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label for="tenDangNhap" class="form-label fw-semibold small text-navy mb-0">
                        Tên đăng nhập
                      </label>
                      <span class="text-muted" style="font-size: 0.75rem;">(Mặc định là Email)</span>
                    </div>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-at"></i>
                      </span>
                      <input
                        id="tenDangNhap"
                        v-model.trim="form.tenDangNhap"
                        type="text"
                        class="form-control border-start-0 ps-0"
                        :class="{ 'is-invalid': fieldErrors.tenDangNhap }"
                        placeholder="Để trống nếu dùng email"
                        autocomplete="username"
                        :disabled="isLoading"
                        @input="clearFieldError('tenDangNhap')"
                      />
                    </div>
                    <div v-if="fieldErrors.tenDangNhap" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.tenDangNhap }}
                    </div>
                  </div>
                </div>

                <!-- Nhóm 3: Mật khẩu và Xác nhận mật khẩu -->
                <div class="row g-3 mb-3">
                  <!-- Mật khẩu -->
                  <div class="col-12 col-md-6">
                    <label for="password" class="form-label fw-semibold small text-navy">
                      Mật khẩu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-lock"></i>
                      </span>
                      <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="form-control border-start-0 border-end-0 px-0"
                        :class="{ 'is-invalid': fieldErrors.password }"
                        placeholder="Ít nhất 6 ký tự"
                        autocomplete="new-password"
                        :disabled="isLoading"
                        required
                        @input="clearFieldError('password')"
                      />
                      <button
                        type="button"
                        class="btn btn-outline-light border border-start-0 text-muted bg-white px-3"
                        :disabled="isLoading"
                        tabindex="-1"
                        @click="showPassword = !showPassword"
                      >
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                      </button>
                    </div>
                    <div v-if="fieldErrors.password" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.password }}
                    </div>
                  </div>

                  <!-- Xác nhận mật khẩu -->
                  <div class="col-12 col-md-6">
                    <label for="password_confirmation" class="form-label fw-semibold small text-navy">
                      Xác nhận mật khẩu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-shield-check"></i>
                      </span>
                      <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        class="form-control border-start-0 border-end-0 px-0"
                        :class="{ 'is-invalid': fieldErrors.password_confirmation }"
                        placeholder="Nhập lại mật khẩu"
                        autocomplete="new-password"
                        :disabled="isLoading"
                        required
                        @input="clearFieldError('password_confirmation')"
                      />
                      <button
                        type="button"
                        class="btn btn-outline-light border border-start-0 text-muted bg-white px-3"
                        :disabled="isLoading"
                        tabindex="-1"
                        @click="showPasswordConfirm = !showPasswordConfirm"
                      >
                        <i :class="showPasswordConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                      </button>
                    </div>
                    <div v-if="fieldErrors.password_confirmation" class="invalid-feedback d-block small mt-1">
                      {{ fieldErrors.password_confirmation }}
                    </div>
                  </div>
                </div>

                <!-- Nhóm 4: Thông tin bổ sung (CCCD & Địa chỉ - Có thể cập nhật sau) -->
                <div class="accordion mb-3" id="additionalInfoAccordion">
                  <div class="accordion-item border rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingAdditional">
                      <button
                        class="accordion-button collapsed py-2 px-3 bg-light text-navy small fw-semibold"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapseAdditional"
                        aria-expanded="false"
                        aria-controls="collapseAdditional"
                      >
                        <i class="bi bi-card-heading me-2 text-teal"></i>
                        Thông tin bổ sung (CCCD / Địa chỉ - Tùy chọn)
                      </button>
                    </h2>
                    <div
                      id="collapseAdditional"
                      class="accordion-collapse collapse"
                      aria-labelledby="headingAdditional"
                    >
                      <div class="accordion-body p-3">
                        <div class="row g-3">
                          <div class="col-12 col-md-6">
                            <label for="cccd" class="form-label small text-muted">
                              Số CCCD / Hộ chiếu
                            </label>
                            <input
                              id="cccd"
                              v-model.trim="form.cccd"
                              type="text"
                              class="form-control form-control-sm"
                              :class="{ 'is-invalid': fieldErrors.cccd }"
                              placeholder="Ví dụ: 001203009999"
                              :disabled="isLoading"
                              @input="clearFieldError('cccd')"
                            />
                            <div v-if="fieldErrors.cccd" class="invalid-feedback d-block small mt-1">
                              {{ fieldErrors.cccd }}
                            </div>
                          </div>

                          <div class="col-12 col-md-6">
                            <label for="diaChi" class="form-label small text-muted">
                              Địa chỉ thường trú / liên hệ
                            </label>
                            <input
                              id="diaChi"
                              v-model.trim="form.diaChi"
                              type="text"
                              class="form-control form-control-sm"
                              :class="{ 'is-invalid': fieldErrors.diaChi }"
                              placeholder="Ví dụ: Hải Châu, Đà Nẵng"
                              :disabled="isLoading"
                              @input="clearFieldError('diaChi')"
                            />
                            <div v-if="fieldErrors.diaChi" class="invalid-feedback d-block small mt-1">
                              {{ fieldErrors.diaChi }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Điều khoản sử dụng -->
                <div class="mb-4">
                  <div class="form-check">
                    <input
                      id="dongYDieuKhoan"
                      v-model="form.dongYDieuKhoan"
                      type="checkbox"
                      class="form-check-input"
                      :class="{ 'is-invalid': fieldErrors.dongYDieuKhoan }"
                      :disabled="isLoading"
                      @change="clearFieldError('dongYDieuKhoan')"
                    />
                    <label for="dongYDieuKhoan" class="form-check-label small text-muted user-select-none">
                      Tôi đồng ý với
                      <a href="#" class="text-teal text-decoration-none hover-underline" @click.prevent>
                        Điều khoản dịch vụ
                      </a>
                      và
                      <a href="#" class="text-teal text-decoration-none hover-underline" @click.prevent>
                        Chính sách bảo mật
                      </a>
                      của RoomBook PMS. <span class="text-danger">*</span>
                    </label>
                  </div>
                  <div v-if="fieldErrors.dongYDieuKhoan" class="invalid-feedback d-block small mt-1">
                    {{ fieldErrors.dongYDieuKhoan }}
                  </div>
                </div>

                <!-- Nút Submit Đăng ký -->
                <button
                  type="submit"
                  class="btn btn-primary-custom w-100 py-2.5 rounded-3 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm"
                  :disabled="isLoading"
                >
                  <span
                    v-if="isLoading"
                    class="spinner-border spinner-border-sm"
                    role="status"
                    aria-hidden="true"
                  ></span>
                  <span>{{ isLoading ? 'Đang tạo tài khoản...' : 'Đăng ký tài khoản ngay' }}</span>
                  <i v-if="!isLoading" class="bi bi-arrow-right"></i>
                </button>
              </form>

              <!-- Phân cách & Nút hỗ trợ kiểm thử Dev/Test -->
              <div v-if="!isRegistered" class="position-relative my-4">
                <hr class="text-muted opacity-25" />
                <span
                  class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small user-select-none"
                >
                  Dữ liệu mẫu kiểm thử
                </span>
              </div>

              <!-- Quick Fill Buttons cho Tester / Dev -->
              <div v-if="!isRegistered" class="d-flex flex-wrap gap-2">
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary flex-fill text-truncate"
                  title="Điền dữ liệu đăng ký hợp lệ (Luồng chính)"
                  :disabled="isLoading"
                  @click="fillSampleCustomer"
                >
                  ✨ Điền mẫu hợp lệ
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-danger flex-fill text-truncate"
                  title="Kiểm thử trùng email/số điện thoại đã có (422)"
                  :disabled="isLoading"
                  @click="fillDuplicateCustomer"
                >
                  ⚠️ Kiểm tra trùng (422)
                </button>
                <button
                  type="button"
                  class="btn btn-sm btn-outline-light text-muted border flex-fill text-truncate"
                  title="Xóa trắng toàn bộ form"
                  :disabled="isLoading"
                  @click="clearAll"
                >
                  🧹 Xóa trắng
                </button>
              </div>
            </div>

            <!-- Footer của Card -->
            <div class="card-footer bg-light border-0 py-3 text-center text-muted small">
              Đã có tài khoản RoomBook PMS?
              <router-link to="/login" class="text-teal fw-semibold text-decoration-none">
                Đăng nhập ngay
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// Quản lý trạng thái form đăng ký bằng reactive()
const form = reactive({
  hoTen: '',
  email: '',
  soDienThoai: '',
  tenDangNhap: '',
  cccd: '',
  diaChi: '',
  password: '',
  password_confirmation: '',
  dongYDieuKhoan: true,
})

// Hiệu ứng và thông báo
const isLoading = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const errorMessage = ref('')
const isRegistered = ref(false)
const registeredUser = ref(null)
const countdown = ref(5)
let countdownTimer = null

// Quản lý lỗi validation theo từng trường
const fieldErrors = reactive({
  hoTen: '',
  email: '',
  soDienThoai: '',
  tenDangNhap: '',
  cccd: '',
  diaChi: '',
  password: '',
  password_confirmation: '',
  dongYDieuKhoan: '',
})

// Xóa lỗi của một trường cụ thể khi người dùng chỉnh sửa
const clearFieldError = (fieldName) => {
  if (fieldErrors[fieldName]) {
    fieldErrors[fieldName] = ''
  }
}

// Xóa toàn bộ lỗi các trường
const clearAllFieldErrors = () => {
  Object.keys(fieldErrors).forEach((key) => {
    fieldErrors[key] = ''
  })
}

// Xóa trắng toàn bộ form
const clearAll = () => {
  form.hoTen = ''
  form.email = ''
  form.soDienThoai = ''
  form.tenDangNhap = ''
  form.cccd = ''
  form.diaChi = ''
  form.password = ''
  form.password_confirmation = ''
  form.dongYDieuKhoan = true
  errorMessage.value = ''
  clearAllFieldErrors()
}

// Reset form về trạng thái ban đầu sau khi đăng ký thành công
const resetForm = () => {
  if (countdownTimer) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }
  isRegistered.value = false
  registeredUser.value = null
  clearAll()
}

// Hàm tiện ích điền dữ liệu mẫu hợp lệ cho kiểm thử luồng chính
const fillSampleCustomer = () => {
  const randomSuffix = Math.floor(1000 + Math.random() * 9000)
  form.hoTen = `Nguyễn Văn Khách ${randomSuffix}`
  form.email = `khachhang${randomSuffix}@example.com`
  form.soDienThoai = `09${Math.floor(10000000 + Math.random() * 90000000)}`
  form.tenDangNhap = `khachhang_${randomSuffix}`
  form.cccd = `00120300${randomSuffix}`
  form.diaChi = 'Hải Châu, Đà Nẵng'
  form.password = 'Password123@'
  form.password_confirmation = 'Password123@'
  form.dongYDieuKhoan = true
  errorMessage.value = ''
  clearAllFieldErrors()
}

// Hàm tiện ích điền dữ liệu trùng để kiểm tra lỗi 422
const fillDuplicateCustomer = () => {
  form.hoTen = 'Khách Hàng Cũ'
  form.email = 'oldcustomer@example.com'
  form.soDienThoai = '0901112222'
  form.tenDangNhap = 'existing_customer'
  form.cccd = '001200000001'
  form.diaChi = 'Hà Nội'
  form.password = 'Password123@'
  form.password_confirmation = 'Password123@'
  form.dongYDieuKhoan = true
  errorMessage.value = ''
  clearAllFieldErrors()
}

// Kiểm tra tính hợp lệ ở phía Client (Client-side Validation)
const validateForm = () => {
  clearAllFieldErrors()
  let isValid = true

  // 1. Kiểm tra Họ và tên
  if (!form.hoTen) {
    fieldErrors.hoTen = 'Vui lòng nhập họ và tên.'
    isValid = false
  } else if (form.hoTen.length > 100) {
    fieldErrors.hoTen = 'Họ và tên không được vượt quá 100 ký tự.'
    isValid = false
  }

  // 2. Kiểm tra Số điện thoại
  if (!form.soDienThoai) {
    fieldErrors.soDienThoai = 'Vui lòng nhập số điện thoại.'
    isValid = false
  } else if (!/^[0-9+]{9,15}$/.test(form.soDienThoai.replace(/\s+/g, ''))) {
    fieldErrors.soDienThoai = 'Số điện thoại không hợp lệ (từ 9 đến 15 chữ số).'
    isValid = false
  }

  // 3. Kiểm tra Email
  if (!form.email) {
    fieldErrors.email = 'Vui lòng nhập địa chỉ email.'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    fieldErrors.email = 'Địa chỉ email không đúng định dạng.'
    isValid = false
  }

  // 4. Kiểm tra Mật khẩu
  if (!form.password) {
    fieldErrors.password = 'Vui lòng nhập mật khẩu.'
    isValid = false
  } else if (form.password.length < 6) {
    fieldErrors.password = 'Mật khẩu phải có ít nhất 6 ký tự.'
    isValid = false
  }

  // 5. Kiểm tra Xác nhận mật khẩu
  if (!form.password_confirmation) {
    fieldErrors.password_confirmation = 'Vui lòng xác nhận lại mật khẩu.'
    isValid = false
  } else if (form.password !== form.password_confirmation) {
    fieldErrors.password_confirmation = 'Xác nhận mật khẩu không khớp.'
    isValid = false
  }

  // 6. Kiểm tra đồng ý điều khoản
  if (!form.dongYDieuKhoan) {
    fieldErrors.dongYDieuKhoan = 'Bạn cần đồng ý với Điều khoản dịch vụ để tiếp tục.'
    isValid = false
  }

  return isValid
}

// Bắt đầu đếm ngược chuyển về trang đăng nhập
const startRedirectCountdown = () => {
  countdown.value = 5
  if (countdownTimer) clearInterval(countdownTimer)

  countdownTimer = setInterval(() => {
    countdown.value -= 1
    if (countdown.value <= 0) {
      clearInterval(countdownTimer)
      countdownTimer = null
      router.push('/login')
    }
  }, 1000)
}

// Xử lý gửi Form Đăng ký qua Axios
const handleRegister = async () => {
  errorMessage.value = ''

  if (!validateForm()) {
    return
  }

  isLoading.value = true

  try {
    // Gọi API trực tiếp bằng axios (không dùng tầng service trung gian)
    const apiBase = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
    const registerEndpoint = `${apiBase}/api/v1/auth/register`

    // Chuẩn bị payload theo đúng API Specification và Database columns
    const payload = {
      hoTen: form.hoTen,
      email: form.email,
      soDienThoai: form.soDienThoai,
      password: form.password,
      password_confirmation: form.password_confirmation,
    }

    // Các trường tùy chọn: chỉ gửi nếu người dùng có nhập
    if (form.tenDangNhap) {
      payload.tenDangNhap = form.tenDangNhap
    }
    if (form.cccd) {
      payload.cccd = form.cccd
    }
    if (form.diaChi) {
      payload.diaChi = form.diaChi
    }

    const response = await axios.post(registerEndpoint, payload, {
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    })

    const result = response.data

    if (result.success && result.status === 201) {
      isRegistered.value = true
      registeredUser.value = result.data?.user || {
        hoTen: form.hoTen,
        email: form.email,
        soDienThoai: form.soDienThoai,
        tenDangNhap: form.tenDangNhap || form.email,
        vaiTro: 'Khách hàng',
      }
      startRedirectCountdown()
    } else {
      errorMessage.value = result.message || 'Đăng ký tài khoản không thành công. Vui lòng thử lại.'
    }
  } catch (error) {
    if (error.response) {
      const status = error.response.status
      const data = error.response.data

      if (status === 422) {
        // Lỗi Validation từ Backend (email/số điện thoại/CCCD/tên đăng nhập đã tồn tại hoặc dữ liệu sai)
        errorMessage.value = data.message || 'Dữ liệu gửi lên không hợp lệ. Vui lòng kiểm tra lại.'
        if (data.errors) {
          if (data.errors.hoTen) {
            fieldErrors.hoTen = Array.isArray(data.errors.hoTen)
              ? data.errors.hoTen[0]
              : data.errors.hoTen
          }
          if (data.errors.email) {
            fieldErrors.email = Array.isArray(data.errors.email)
              ? data.errors.email[0]
              : data.errors.email
          }
          if (data.errors.soDienThoai) {
            fieldErrors.soDienThoai = Array.isArray(data.errors.soDienThoai)
              ? data.errors.soDienThoai[0]
              : data.errors.soDienThoai
          }
          if (data.errors.tenDangNhap) {
            fieldErrors.tenDangNhap = Array.isArray(data.errors.tenDangNhap)
              ? data.errors.tenDangNhap[0]
              : data.errors.tenDangNhap
          }
          if (data.errors.password || data.errors.mat_khau || data.errors.matKhau) {
            const pwdErr =
              data.errors.password || data.errors.mat_khau || data.errors.matKhau
            fieldErrors.password = Array.isArray(pwdErr) ? pwdErr[0] : pwdErr
          }
          if (
            data.errors.password_confirmation ||
            data.errors.mat_khau_confirmation ||
            data.errors.matKhau_confirmation
          ) {
            const pwdConfErr =
              data.errors.password_confirmation ||
              data.errors.mat_khau_confirmation ||
              data.errors.matKhau_confirmation
            fieldErrors.password_confirmation = Array.isArray(pwdConfErr)
              ? pwdConfErr[0]
              : pwdConfErr
          }
          if (data.errors.cccd) {
            fieldErrors.cccd = Array.isArray(data.errors.cccd)
              ? data.errors.cccd[0]
              : data.errors.cccd
          }
          if (data.errors.diaChi) {
            fieldErrors.diaChi = Array.isArray(data.errors.diaChi)
              ? data.errors.diaChi[0]
              : data.errors.diaChi
          }
        }
      } else if (status === 500) {
        // Lỗi hệ thống Backend / Database Transaction rollback
        errorMessage.value =
          data.message || 'Đăng ký tài khoản không thành công do lỗi hệ thống. Vui lòng thử lại sau.'
      } else {
        errorMessage.value = data.message || `Đã xảy ra lỗi (${status}). Vui lòng thử lại sau.`
      }
    } else if (error.request) {
      errorMessage.value = 'Không thể kết nối đến máy chủ. Vui lòng kiểm tra kết nối mạng backend.'
    } else {
      errorMessage.value = error.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.'
    }
  } finally {
    isLoading.value = false
  }
}

// Dọn dẹp timer khi component bị unmount
onUnmounted(() => {
  if (countdownTimer) {
    clearInterval(countdownTimer)
    countdownTimer = null
  }
})
</script>

<style scoped>
/* Tuân thủ Design Tokens của dự án */
.register-page {
  background-color: #f8fafc;
  min-height: calc(100vh - 140px);
  display: flex;
  align-items: center;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.text-navy {
  color: #1e3a5f;
}

.text-teal {
  color: #0f766e;
}

.brand-logo-circle {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background-color: #eaf3ee;
  border: 1px solid rgba(15, 118, 110, 0.15);
}

.success-icon-box {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background-color: #eaf3ee;
  border: 2px solid rgba(15, 118, 110, 0.2);
}

.register-card {
  background: #ffffff;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.08), 0 8px 10px -6px rgba(30, 58, 95, 0.04) !important;
}

.form-control:focus,
.form-check-input:focus {
  border-color: #0f766e;
  box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.15);
}

.form-check-input:checked {
  background-color: #0f766e;
  border-color: #0f766e;
}

.btn-primary-custom {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
  color: #ffffff;
  transition: all 0.2s ease-in-out;
}

.btn-primary-custom:hover:not(:disabled) {
  background-color: #162c46;
  border-color: #162c46;
  color: #ffffff;
  transform: translateY(-1px);
}

.btn-primary-custom:disabled {
  background-color: #64748b;
  border-color: #64748b;
  opacity: 0.7;
}

.hover-underline:hover {
  text-decoration: underline !important;
}

.accordion-button:not(.collapsed) {
  color: #1e3a5f;
  background-color: #f1f5f9;
  box-shadow: none;
}
</style>
