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
      <nav class="d-none d-lg-flex align-items-center h-100 gap-4">
        <router-link to="/" class="nav-tab-item">
          <i class="bi bi-bed"></i>
          <span>Phòng nghỉ & Giá tốt</span>
        </router-link>

        <router-link to="/buong-phong" class="nav-tab-item">
          <i class="bi bi-card-checklist"></i>
          <span>Buồng phòng</span>
        </router-link>

        <router-link to="/loai-phong" class="nav-tab-item">
          <i class="bi bi-door-open"></i>
          <span>Loại phòng</span>
        </router-link>

        <router-link to="/nhan-vien" class="nav-tab-item">
          <i class="bi bi-people"></i>
          <span>Nhân viên</span>
        </router-link>

        <router-link to="/services" class="nav-tab-item">
          <i class="bi bi-cup-hot"></i>
          <span>Dịch vụ phòng</span>
        </router-link>

        <router-link to="/yeu-cau-dich-vu" class="nav-tab-item">
          <i class="bi bi-bell"></i>
          <span>Yêu cầu dịch vụ</span>
        </router-link>

        <router-link to="/khuyen-mai" class="nav-tab-item">
          <i class="bi bi-gift"></i>
          <span>Khuyến mãi</span>
        </router-link>

        <router-link to="/minibar-tai-san" class="nav-tab-item">
          <i class="bi bi-cup-straw"></i>
          <span>Minibar & Tài sản</span>
        </router-link>

      </nav>

      <!-- Action Buttons & Avatar Profile (Hình 2: Avatar T màu tím) -->
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-login d-flex align-items-center gap-2">
          <i class="bi bi-box-arrow-in-right"></i>
          <span>Đăng nhập</span>
        </button>
        <button class="btn btn-register d-flex align-items-center gap-2">
          <i class="bi bi-person-plus"></i>
          <span>Đăng ký</span>
        </button>

        <!-- Avatar T màu tím dẫn tới Tài khoản & Phân quyền như hình 2 -->
        <router-link
          to="/tai-khoan"
          class="avatar-purple-btn text-decoration-none"
          title="Tài khoản & phân quyền (Admin)"
        >
          <div class="avatar-purple-circle">
            <span>T</span>
          </div>
        </router-link>
      </div>
    </div>
  </header>
</template>
<script>
export default {

}
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

/* Avatar T màu tím như hình 2 */
.avatar-purple-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.avatar-purple-btn:hover {
  transform: scale(1.08);
}

.avatar-purple-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: radial-gradient(circle, #6a0dad 0%, #4a0072 100%);
  border: 2px solid #8e24aa;
  box-shadow: 0 2px 8px rgba(106, 13, 173, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 1.25rem;
  font-weight: 700;
  user-select: none;
  font-family: system-ui, -apple-system, sans-serif;
}
</style>
