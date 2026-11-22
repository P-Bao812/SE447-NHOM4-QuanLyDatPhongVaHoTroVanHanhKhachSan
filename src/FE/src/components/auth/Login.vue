<template>
  <div class="login-page py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
          <!-- Card Đăng nhập -->
          <div class="card login-card shadow-lg border-0 rounded-4 overflow-hidden">
            <!-- Header của Card -->
            <div class="card-header bg-white border-0 text-center pt-4 pb-2 px-4">
              <div class="brand-logo-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-buildings-fill fs-3 text-teal"></i>
              </div>
              <h4 class="fw-bold text-navy mb-1">RoomBook PMS</h4>
              <p class="text-muted small mb-0">Hệ thống Quản lý Khách sạn & Resort</p>
            </div>

            <!-- Body của Card chứa Form -->
            <div class="card-body p-4 pt-2">
              <h5 class="fw-semibold text-center mb-4 text-dark-emphasis">Đăng nhập tài khoản</h5>

              <!-- Thông báo lỗi (401, 403, 500) -->
              <div
                v-if="errorMessage"
                class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-3 small"
                role="alert"
              >
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <div class="flex-grow-1">{{ errorMessage }}</div>
                <button
                  type="button"
                  class="btn-close py-2"
                  aria-label="Close"
                  @click="errorMessage = ''"
                ></button>
              </div>

              <!-- Thông báo thành công (200) -->
              <div
                v-if="successMessage"
                class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 rounded-3 mb-3 small"
                role="alert"
              >
                <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                <div>{{ successMessage }}</div>
              </div>

              <!-- Form Đăng nhập -->
              <form @submit.prevent="handleLogin" novalidate>
                <!-- Tên đăng nhập -->
                <div class="mb-3">
                  <label for="tenDangNhap" class="form-label fw-semibold small text-navy">
                    Tên đăng nhập <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-person"></i>
                    </span>
                    <input
                      id="tenDangNhap"
                      v-model.trim="form.tenDangNhap"
                      type="text"
                      class="form-control border-start-0 ps-0"
                      :class="{ 'is-invalid': fieldErrors.tenDangNhap }"
                      placeholder="Ví dụ: admin hoặc mã nhân viên"
                      autocomplete="username"
                      :disabled="isLoading"
                      required
                    />
                  </div>
                  <div v-if="fieldErrors.tenDangNhap" class="invalid-feedback d-block small mt-1">
                    {{ fieldErrors.tenDangNhap }}
                  </div>
                </div>

                <!-- Mật khẩu -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="matKhau" class="form-label fw-semibold small text-navy mb-0">
                      Mật khẩu <span class="text-danger">*</span>
                    </label>
                    <a href="#" class="text-decoration-none small text-teal hover-underline">
                      Quên mật khẩu?
                    </a>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                      <i class="bi bi-lock"></i>
                    </span>
                    <input
                      id="matKhau"
                      v-model="form.matKhau"
                      :type="showPassword ? 'text' : 'password'"
                      class="form-control border-start-0 border-end-0 px-0"
                      :class="{ 'is-invalid': fieldErrors.matKhau }"
                      placeholder="Nhập mật khẩu của bạn"
                      autocomplete="current-password"
                      :disabled="isLoading"
                      required
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
                  <div v-if="fieldErrors.matKhau" class="invalid-feedback d-block small mt-1">
                    {{ fieldErrors.matKhau }}
                  </div>
                </div>

                <!-- Ghi nhớ đăng nhập -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div class="form-check">
                    <input
                      id="rememberMe"
                      v-model="form.remember"
                      type="checkbox"
                      class="form-check-input"
                      :disabled="isLoading"
                    />
                    <label for="rememberMe" class="form-check-label small text-muted user-select-none">
                      Ghi nhớ phiên đăng nhập
                    </label>
                  </div>
                </div>

                <!-- Nút Submit -->
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
                  <span>{{ isLoading ? 'Đang xác thực...' : 'Đăng nhập ngay' }}</span>
                  <i v-if="!isLoading" class="bi bi-arrow-right"></i>
                </button>
              </form>

              <!-- Phân cách -->
              <div class="position-relative my-4">
                <hr class="text-muted opacity-25" />
                <span
                  class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small user-select-none"
                >
                  Tài khoản mẫu kiểm thử
                </span>
              </div>

              <!-- Quick Login Buttons cho Dev & Test -->
              <div class="d-grid gap-2">
                <div class="d-flex gap-2">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary flex-fill text-truncate"
                    title="Đăng nhập tài khoản Quản lý"
                    :disabled="isLoading"
                    @click="fillCredentials('admin', 'Password123@')"
                  >
                    👑 Quản lý
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary flex-fill text-truncate"
                    title="Đăng nhập tài khoản Lễ tân"
                    :disabled="isLoading"
                    @click="fillCredentials('letan', '123456')"
                  >
                    🛎️ Lễ tân
                  </button>
                </div>
                <div class="d-flex gap-2">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary flex-fill text-truncate"
                    title="Đăng nhập tài khoản Buồng phòng"
                    :disabled="isLoading"
                    @click="fillCredentials('buongphong', '123456')"
                  >
                    🧹 Buồng phòng
                  </button>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-danger flex-fill text-truncate"
                    title="Kiểm tra Test Case tài khoản bị khóa (403)"
                    :disabled="isLoading"
                    @click="fillCredentials('user_locked', 'Password123@')"
                  >
                    🔒 Khóa (403)
                  </button>
                </div>
              </div>
            </div>

            <!-- Footer của Card -->
            <div class="card-footer bg-light border-0 py-3 text-center text-muted small">
              Chưa có tài khoản?
              <router-link to="/dang-ky" class="text-teal fw-semibold text-decoration-none">
                Đăng ký ngay
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

// Quản lý trạng thái form bằng reactive() theo chuẩn Coding Conventions
const form = reactive({
  tenDangNhap: '',
  matKhau: '',
  remember: false,
})

// Hiệu ứng và thông báo
const isLoading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const fieldErrors = reactive({
  tenDangNhap: '',
  matKhau: '',
})

// Tự động nạp tên đăng nhập đã lưu nếu có
onMounted(() => {
  const savedUsername = localStorage.getItem('remembered_username')
  if (savedUsername) {
    form.tenDangNhap = savedUsername
    form.remember = true
  }
})

// Hàm tiện ích điền nhanh thông tin tài khoản mẫu
const fillCredentials = (username, password) => {
  form.tenDangNhap = username
  form.matKhau = password
  errorMessage.value = ''
  clearFieldErrors()
}

// Xóa lỗi validation trường
const clearFieldErrors = () => {
  fieldErrors.tenDangNhap = ''
  fieldErrors.matKhau = ''
}

// Hàm xác thực Form phía Client
const validateForm = () => {
  clearFieldErrors()
  let isValid = true

  if (!form.tenDangNhap) {
    fieldErrors.tenDangNhap = 'Vui lòng nhập tên đăng nhập.'
    isValid = false
  }

  if (!form.matKhau) {
    fieldErrors.matKhau = 'Vui lòng nhập mật khẩu.'
    isValid = false
  }

  return isValid
}

// Hàm điều hướng theo vai trò (Role-based Routing)
const redirectByRole = (user) => {
  // Nếu có tham số redirect trong query, ưu tiên chuyển về trang đó
  const redirectUrl = route.query.redirect
  if (redirectUrl && !redirectUrl.includes('/login') && !redirectUrl.includes('/dang-nhap')) {
    router.push(redirectUrl)
    return
  }

  const vaiTro = user?.vaiTro || ''
  const vaiTroNormalized = vaiTro.toLowerCase().trim()

  if (vaiTroNormalized === 'quản lý' || vaiTroNormalized === 'admin') {
    router.push('/admin/dashboard')
  } else if (vaiTroNormalized === 'lễ tân' || vaiTroNormalized === 'receptionist') {
    router.push('/receptionist/front-desk')
  } else if (vaiTroNormalized === 'buồng phòng' || vaiTroNormalized === 'housekeeping') {
    router.push('/housekeeping')
  } else {
    // Khách hàng hoặc vai trò mặc định
    router.push('/')
  }
}

// Xử lý gửi Form Đăng nhập qua Axios
const handleLogin = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!validateForm()) {
    return
  }

  isLoading.value = true

  try {
    // Gọi API trực tiếp bằng axios (không thông qua lớp Service trung gian)
    // Tương thích với cả backend proxy hoặc URL trực tiếp
    const apiBase = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
    const loginEndpoint = `${apiBase}/api/v1/auth/login`

    const response = await axios.post(
      loginEndpoint,
      {
        tenDangNhap: form.tenDangNhap,
        matKhau: form.matKhau,
        password: form.matKhau, // Đính kèm cả 2 trường để tương thích tối đa với LoginRequest
      },
      {
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
      }
    )

    const result = response.data

    if (result.success && result.data?.access_token) {
      // 1. Lưu Bearer Access Token vào localStorage
      localStorage.setItem('access_token', result.data.access_token)
      localStorage.setItem('token_type', result.data.token_type || 'Bearer')

      // 2. Lưu thông tin tài khoản và vai trò vào localStorage
      if (result.data.user) {
        localStorage.setItem('user', JSON.stringify(result.data.user))
        localStorage.setItem('vaiTro', result.data.user.vaiTro || '')
      }

      // 3. Xử lý ghi nhớ tên đăng nhập
      if (form.remember) {
        localStorage.setItem('remembered_username', form.tenDangNhap)
      } else {
        localStorage.removeItem('remembered_username')
      }

      successMessage.value = result.message || 'Đăng nhập thành công! Đang chuyển hướng...'

      // 4. Chuyển hướng theo đúng vai trò hệ thống
      setTimeout(() => {
        redirectByRole(result.data.user)
      }, 500)
    } else {
      errorMessage.value = result.message || 'Đăng nhập không thành công. Vui lòng thử lại.'
    }
  } catch (error) {
    if (error.response) {
      const status = error.response.status
      const data = error.response.data

      if (status === 401) {
        // Sai tên đăng nhập hoặc mật khẩu
        errorMessage.value = data.message || 'Tài khoản hoặc mật khẩu không chính xác.'
      } else if (status === 403) {
        // Tài khoản bị khóa (trangThai = 'Khóa')
        errorMessage.value =
          data.message || 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'
      } else if (status === 422) {
        // Lỗi Validation từ Backend
        errorMessage.value = data.message || 'Dữ liệu gửi lên không hợp lệ.'
        if (data.errors) {
          if (data.errors.tenDangNhap) {
            fieldErrors.tenDangNhap = Array.isArray(data.errors.tenDangNhap)
              ? data.errors.tenDangNhap[0]
              : data.errors.tenDangNhap
          }
          if (data.errors.matKhau || data.errors.password) {
            const pwdErr = data.errors.matKhau || data.errors.password
            fieldErrors.matKhau = Array.isArray(pwdErr) ? pwdErr[0] : pwdErr
          }
        }
      } else {
        errorMessage.value = data.message || `Lỗi hệ thống (${status}). Vui lòng thử lại.`
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
</script>

<style scoped>
/* Tuân thủ Design Tokens của dự án */
.login-page {
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

.login-card {
  background: #ffffff;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 10px 25px -5px rgba(30, 58, 95, 0.08), 0 8px 10px -6px rgba(30, 58, 95, 0.04) !important;
}

.form-control:focus {
  border-color: #0f766e;
  box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.15);
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
</style>
