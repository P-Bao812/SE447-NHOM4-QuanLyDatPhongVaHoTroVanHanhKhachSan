<template>
  <header class="guest-header bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-lg-5 h-100 d-flex align-items-center justify-content-between">
      <!-- Logo & Brand -->
      <a href="#" class="d-flex align-items-center gap-3 text-decoration-none brand-wrap">
        <div class="brand-logo-box d-flex align-items-center justify-content-center">
          <i class="bi bi-buildings"></i>
        </div>
        <div>
          <div class="fw-bold text-main-dark lh-1" style="font-size: 1.15rem;">RoomBook</div>
          <div class="text-uppercase text-secondary-custom mt-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">
            HOTEL & RESORT · HỘI AN
          </div>
        </div>
      </a>

      <!-- Menu Navigation -->
      <nav class="d-none d-xl-flex align-items-center h-100 gap-4">
        <router-link to="/" class="nav-tab-item">
          <i class="bi bi-bed"></i>
          <span>Phòng nghỉ & Giá tốt</span>
        </router-link>

        <router-link to="/booking" class="nav-tab-item">
          <i class="bi bi-calendar-check"></i>
          <span>Đặt phòng</span>
        </router-link>

        <router-link to="/my-bookings" class="nav-tab-item">
          <i class="bi bi-journal-text"></i>
          <span>Lịch sử đặt phòng</span>
        </router-link>

        <router-link to="/tien-ich" class="nav-tab-item">
          <i class="bi bi-stars"></i>
          <span>Tiện ích & Trải nghiệm</span>
        </router-link>

        <router-link to="/services" class="nav-tab-item">
          <i class="bi bi-cup-hot"></i>
          <span>Dịch vụ phòng</span>
        </router-link>

        <router-link to="/promotions" class="nav-tab-item">
          <i class="bi bi-gift"></i>
          <span>Ưu đãi & Điểm thưởng</span>
        </router-link>

        <router-link to="/reviews" class="nav-tab-item">
          <i class="bi bi-star"></i>
          <span>Đánh giá</span>
        </router-link>
      </nav>

      <!-- Action Buttons (Đăng nhập, Đăng ký hoặc Hồ sơ khi đã đăng nhập) -->
      <div class="d-flex align-items-center gap-2">
        <template v-if="hasToken">
          <router-link to="/profile" class="text-decoration-none">
            <button class="btn btn-outline-teal d-flex align-items-center gap-2 rounded-pill px-3 py-1.5 small fw-semibold">
              <i class="bi bi-person-circle fs-6"></i>
              <span>{{ userName }}</span>
            </button>
          </router-link>
          <button class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1.5" @click="handleLogout" title="Đăng xuất">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </template>
        <template v-else>
          <router-link to="/dang-nhap" class="router-link nav-tab-item">
            <button class="btn btn-login d-flex align-items-center gap-2">
              <i class="bi bi-box-arrow-in-right"></i>
              <span>Đăng nhập</span>
            </button>
          </router-link>
          <router-link to="/dang-ky" class="router-link nav-tab-item">
            <button class="btn btn-register d-flex align-items-center gap-2">
              <i class="bi bi-person-plus"></i>
              <span>Đăng ký</span>
            </button>
          </router-link>
        </template>
      </div>
    </div>
  </header>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const hasToken = ref(!!localStorage.getItem('access_token'))
const userName = ref('Hồ sơ')

const syncUserState = () => {
  hasToken.value = !!localStorage.getItem('access_token')
  const userStr = localStorage.getItem('user')
  if (userStr) {
    try {
      const u = JSON.parse(userStr)
      userName.value = u.hoTen || u.tenDangNhap || 'Hồ sơ'
    } catch {
      userName.value = 'Hồ sơ'
    }
  } else {
    userName.value = 'Hồ sơ'
  }
}

const handleLogout = () => {
  localStorage.removeItem('access_token')
  localStorage.removeItem('user')
  localStorage.removeItem('vaiTro')
  syncUserState()
  router.push('/dang-nhap')
}

onMounted(() => {
  syncUserState()
})
</script>
<style>
.guest-header {
  height: 76px;
  border-color: #EAE8E1 !important;
  z-index: 1030;
}

.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #7A857F;
}

/* Logo Box */
.brand-logo-box {
  width: 42px;
  height: 42px;
  background-color: #EAF3EE;
  color: #1E5E49;
  border-radius: 12px;
  font-size: 1.25rem;
}

.router-link {
  text-decoration: none
}

/* Navigation Tabs (Áp dụng cho cả router-link) */
.nav-tab-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 100%;
  color: #5C6862;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  border-bottom: 2.5px solid transparent;
  padding: 0 4px;
  transition: all 0.2s ease;
}

.nav-tab-item i {
  font-size: 1.05rem;
}

.nav-tab-item:hover {
  color: #1E5E49;
}

/* Tự động nhận class active khi Vue Router chuyển đúng đường dẫn */
.nav-tab-item.router-link-active,
.nav-tab-item.router-link-exact-active {
  color: #1E5E49;
  font-weight: 700;
  border-bottom-color: #1E5E49;
}

/* Buttons */
.btn-login {
  background-color: #ffffff;
  color: #172620;
  border: 1px solid #E2E0D8;
  border-radius: 14px;
  padding: 9px 18px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-login:hover {
  background-color: #FAF9F5;
  border-color: #D2CFC5;
}

.btn-register {
  background-color: #14241E;
  color: #ffffff;
  border: 1px solid #14241E;
  border-radius: 14px;
  padding: 9px 20px;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-register:hover {
  background-color: #1E352D;
  color: #ffffff;
}

/* Các style cũ giữ nguyên, bổ sung và sửa lại phần nav-tab-item */
.nav-tab-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 100%;
  color: #5C6862;
  text-decoration: none !important;
  /* Buộc tắt gạch chân mặc định của thẻ a/router-link */
  font-size: 0.9rem;
  font-weight: 500;
  border-bottom: 2.5px solid transparent;
  padding: 0 4px;
  transition: all 0.2s ease;
}

.nav-tab-item i {
  font-size: 1.05rem;
}

.nav-tab-item:hover {
  color: #1E5E49;
  text-decoration: none !important;
}

/* Khi active hoặc chuyển trang */
.nav-tab-item.router-link-active,
.nav-tab-item.router-link-exact-active {
  color: #1E5E49 !important;
  font-weight: 700;
  border-bottom-color: #1E5E49;
  text-decoration: none !important;
}
</style>
