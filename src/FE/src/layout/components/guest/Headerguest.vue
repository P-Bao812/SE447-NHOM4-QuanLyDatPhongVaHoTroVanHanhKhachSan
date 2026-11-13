<template>
  <header class="guest-header bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-lg-5 h-100 d-flex align-items-center justify-content-between">
      <!-- Logo & Brand -->
      <router-link to="/" class="d-flex align-items-center gap-3 text-decoration-none brand-wrap">
        <div class="brand-logo-box d-flex align-items-center justify-content-center">
          <i class="bi bi-buildings"></i>
        </div>
        <div>
          <div class="fw-bold text-main-dark lh-1" style="font-size: 1.15rem;">RoomBook</div>
          <div class="text-uppercase text-secondary-custom mt-1" style="font-size: 0.65rem; letter-spacing: 0.8px;">
            HOTEL & RESORT · HỘI AN
          </div>
        </div>
      </router-link>

      <!-- Menu Navigation -->
      <nav class="d-none d-xl-flex align-items-center h-100 gap-4">
        <router-link to="/" class="nav-tab-item">
          <i class="bi bi-bed"></i>
          <span>Phòng nghỉ & Giá tốt</span>
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

      <!-- Action Buttons hoặc Dropdown người dùng -->
      <div class="d-flex align-items-center gap-2">
        <!-- Khi Khách chưa đăng nhập -->
        <template v-if="!hotelStore.isGuestLoggedIn">
          <button @click="openAuth('login')" class="btn btn-login d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Đăng nhập</span>
          </button>
          <button @click="openAuth('register')" class="btn btn-register d-flex align-items-center gap-2">
            <i class="bi bi-person-plus"></i>
            <span>Đăng ký</span>
          </button>
        </template>

        <!-- Khi Khách đã đăng nhập -->
        <template v-else>
          <router-link to="/promotions" class="badge bg-warning-subtle text-warning-emphasis border text-decoration-none px-3 py-2 rounded-pill font-monospace small d-none d-sm-inline-flex align-items-center gap-1">
            <span>⭐</span>
            <strong>{{ hotelStore.guestLoyaltyPoints }} pts</strong>
          </router-link>

          <div class="dropdown">
            <button
              class="btn btn-light rounded-pill px-3 py-1.5 border d-flex align-items-center gap-2 dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <div class="guest-avatar-small rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold">
                {{ hotelStore.guestName.charAt(0) }}
              </div>
              <span class="small fw-bold text-main-dark d-none d-md-inline">{{ hotelStore.guestName }}</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-2 border-0 mt-2" style="width: 230px;">
              <li class="px-3 py-2 border-bottom mb-1">
                <div class="fw-bold text-main-dark small">{{ hotelStore.guestName }}</div>
                <div class="text-muted" style="font-size: 0.75rem;">Hội viên Vàng RoomBook</div>
              </li>
              <li>
                <router-link to="/my-bookings" class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2">
                  <i class="bi bi-journal-bookmark text-success"></i>
                  <span>Lịch sử đặt phòng</span>
                </router-link>
              </li>
              <li>
                <router-link to="/profile" class="dropdown-item rounded-3 py-2 small d-flex align-items-center gap-2">
                  <i class="bi bi-person-gear text-primary"></i>
                  <span>Hồ sơ cá nhân</span>
                </router-link>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <button @click="hotelStore.logoutGuest()" class="dropdown-item rounded-3 py-2 small text-danger d-flex align-items-center gap-2">
                  <i class="bi bi-box-arrow-right"></i>
                  <span>Đăng xuất</span>
                </button>
              </li>
            </ul>
          </div>
        </template>
      </div>
    </div>

    <!-- Modal Xác thực Hợp nhất (Unified Auth Modal) -->
    <div v-if="authModalOpen" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
      <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-lg">
        <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between bg-light">
          <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
            <i class="bi bi-shield-lock text-success-custom"></i>
            {{ authTab === 'guest' ? (authMode === 'login' ? 'Đăng nhập Khách hàng' : 'Đăng ký Hội viên mới') : 'Đăng nhập Ca trực Nhân sự PMS' }}
          </h5>
          <button @click="authModalOpen = false" class="btn-close"></button>
        </div>

        <!-- Tab chuyển đổi giữa Khách hàng & Nhân sự -->
        <div class="px-4 pt-3 border-bottom bg-light">
          <ul class="nav nav-tabs border-0 gap-2">
            <li class="nav-item">
              <button
                @click="authTab = 'guest'"
                class="nav-link border-0 fw-bold small rounded-top-3 px-3 py-2"
                :class="authTab === 'guest' ? 'active bg-white text-success-custom' : 'text-muted'"
              >
                👤 Khách hàng
              </button>
            </li>
            <li class="nav-item">
              <button
                @click="authTab = 'staff'"
                class="nav-link border-0 fw-bold small rounded-top-3 px-3 py-2"
                :class="authTab === 'staff' ? 'active bg-white text-success-custom' : 'text-muted'"
              >
                🔐 Nhân viên PMS
              </button>
            </li>
          </ul>
        </div>

        <div class="modal-body p-4">
          <!-- Phân hệ 1: Khách hàng -->
          <div v-if="authTab === 'guest'">
            <div class="mb-3" v-if="authMode === 'register'">
              <label class="form-label small fw-bold text-muted">Họ và tên</label>
              <input v-model="formAuth.name" type="text" class="form-control" placeholder="Nguyễn Minh Anh" />
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Email hoặc Số điện thoại</label>
              <input v-model="formAuth.account" type="text" class="form-control" placeholder="minhanh.nguyen@gmail.com" />
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Mật khẩu</label>
              <input v-model="formAuth.password" type="password" class="form-control" placeholder="••••••••" />
            </div>

            <button @click="submitGuestAuth" class="btn btn-login-submit w-100 py-2.5 text-white fw-bold rounded-3 mb-3">
              {{ authMode === 'login' ? 'Đăng nhập ngay' : 'Đăng ký tài khoản mới' }}
            </button>

            <div class="text-center small text-muted">
              <span v-if="authMode === 'login'">
                Chưa có tài khoản?
                <a href="#" @click.prevent="authMode = 'register'" class="text-success-custom fw-bold text-decoration-none">Đăng ký ngay</a>
              </span>
              <span v-else>
                Đã có tài khoản?
                <a href="#" @click.prevent="authMode = 'login'" class="text-success-custom fw-bold text-decoration-none">Đăng nhập</a>
              </span>
            </div>
          </div>

          <!-- Phân hệ 2: 1-Click Đăng nhập nhanh 4 ca trực nhân sự (Ảnh 1) -->
          <div v-else>
            <div class="small text-muted mb-3">
              Chọn nhanh ca trực nhân viên theo đúng phân quyền hệ thống:
            </div>

            <div class="d-flex flex-column gap-2 mb-3">
              <!-- Mai Linh -->
              <button
                @click="quickLoginStaff('linh')"
                class="btn btn-outline-success p-3 rounded-3 text-start d-flex align-items-center justify-content-between transition"
              >
                <div>
                  <div class="fw-bold text-dark">👱‍♀️ Mai Linh · Ca trực chính</div>
                  <small class="text-muted">Bộ phận Lễ tân · Đón tiếp & Hóa đơn quầy</small>
                </div>
                <i class="bi bi-chevron-right text-success"></i>
              </button>

              <!-- Trần An -->
              <button
                @click="quickLoginStaff('an')"
                class="btn btn-outline-dark p-3 rounded-3 text-start d-flex align-items-center justify-content-between transition"
              >
                <div>
                  <div class="fw-bold text-dark">👨‍💼 Trần An · Chủ khách sạn</div>
                  <small class="text-muted">Quản trị tối cao · Toàn quyền 16 module PMS</small>
                </div>
                <i class="bi bi-chevron-right text-dark"></i>
              </button>

              <!-- Nguyễn Hương -->
              <button
                @click="quickLoginStaff('huong')"
                class="btn btn-outline-warning p-3 rounded-3 text-start d-flex align-items-center justify-content-between transition text-dark"
              >
                <div>
                  <div class="fw-bold text-dark">🧹 Nguyễn Hương · Tổ trưởng Buồng phòng</div>
                  <small class="text-muted">Buồng phòng, Sơ đồ phòng & Kiểm minibar</small>
                </div>
                <i class="bi bi-chevron-right text-warning"></i>
              </button>

              <!-- Lê Ngọc -->
              <button
                @click="quickLoginStaff('ngoc')"
                class="btn btn-outline-info p-3 rounded-3 text-start d-flex align-items-center justify-content-between transition text-dark"
              >
                <div>
                  <div class="fw-bold text-dark">📊 Lê Ngọc · Kế toán & Thu ngân</div>
                  <small class="text-muted">Hóa đơn, Báo cáo doanh thu & Đối soát</small>
                </div>
                <i class="bi bi-chevron-right text-info"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { useHotelStore } from '../../../stores/hotelStore';

export default {
  name: 'HeaderGuest',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  data() {
    return {
      authModalOpen: false,
      authTab: 'guest',
      authMode: 'login',

      formAuth: {
        name: 'Nguyễn Minh Anh',
        account: 'minhanh.nguyen@gmail.com',
        password: 'password123',
      },
    };
  },
  methods: {
    openAuth(mode = 'login') {
      this.authMode = mode;
      this.authTab = 'guest';
      this.authModalOpen = true;
    },
    submitGuestAuth() {
      this.hotelStore.loginGuest(this.formAuth.name || 'Nguyễn Minh Anh');
      this.authModalOpen = false;
      alert(`Đăng nhập thành công! Chào mừng ${this.hotelStore.guestName}.`);
    },
    quickLoginStaff(personaKey) {
      this.hotelStore.switchStaffPersona(personaKey);
      this.authModalOpen = false;
      this.$router.push('/customer');
    },
  },
};
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

.text-success-custom {
  color: #1E5E49;
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

/* Navigation Tabs */
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

.nav-tab-item:hover,
.nav-tab-item.router-link-exact-active {
  color: #1E5E49;
  border-bottom-color: #1E5E49;
}

.btn-login {
  background-color: transparent;
  color: #172620;
  border: 1px solid #EAE8E1;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: 12px;
  padding: 8px 16px;
}

.btn-login:hover {
  background-color: #FAF9F5;
}

.btn-register {
  background-color: #1E5E49;
  color: #ffffff;
  border: none;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: 12px;
  padding: 8px 16px;
}

.btn-register:hover {
  background-color: #164636;
  color: #ffffff;
}

.btn-login-submit {
  background-color: #1E5E49;
  border: none;
}

.btn-login-submit:hover {
  background-color: #164636;
}

.guest-avatar-small {
  width: 28px;
  height: 28px;
  font-size: 0.8rem;
}

/* Modal */
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-dialog-custom {
  max-height: 90vh;
  animation: modalFadeIn 0.25s ease-out;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
