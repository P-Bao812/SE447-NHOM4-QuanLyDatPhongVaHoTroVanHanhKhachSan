<template>
  <div class="guest-profile-container py-4">
    <div class="container-fluid px-3 px-lg-5 max-w-4xl mx-auto">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h2 class="fw-bold text-main-dark mb-1">Hồ sơ cá nhân & Bảo mật</h2>
          <p class="text-secondary-custom small mb-0">Quản lý thông tin tài khoản thành viên RoomBook Rewards.</p>
        </div>
        <button @click="logout" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
          <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
        </button>
      </div>

      <div class="row g-4">
        <!-- Cột trái: Thẻ tóm tắt hội viên -->
        <div class="col-12 col-md-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white mb-3">
            <div class="profile-avatar mx-auto mb-3 rounded-circle bg-success-custom text-white d-flex align-items-center justify-content-center display-6 fw-bold">
              {{ form.name.charAt(0) }}
            </div>
            <h5 class="fw-bold text-main-dark mb-1">{{ form.name }}</h5>
            <div class="small text-muted mb-3">{{ form.email }}</div>
            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1.5 rounded-pill fw-bold">
              ★ Hội viên Vàng (Gold)
            </span>

            <div class="p-3 bg-light rounded-3 border mt-4 text-start small">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Điểm khả dụng:</span>
                <span class="fw-bold text-success-custom">{{ hotelStore.guestLoyaltyPoints }} pts</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Số lần lưu trú:</span>
                <span class="fw-bold text-dark">{{ hotelStore.myGuestBookings.length }} lần</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-muted">Mã hội viên:</span>
                <span class="font-monospace text-muted">RB-GOLD-2611</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Cột phải: Form cập nhật thông tin & đổi mật khẩu -->
        <div class="col-12 col-md-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h5 class="fw-bold text-main-dark mb-3 pb-2 border-bottom">Thông tin cá nhân</h5>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Họ và tên</label>
              <input v-model="form.name" type="text" class="form-control" />
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số điện thoại</label>
                <input v-model="form.phone" type="tel" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số CCCD / Hộ chiếu</label>
                <input v-model="form.cccd" type="text" class="form-control" />
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold text-muted">Địa chỉ email</label>
              <input v-model="form.email" type="email" class="form-control" />
            </div>

            <h5 class="fw-bold text-main-dark mb-3 pb-2 border-bottom">Đổi mật khẩu tài khoản</h5>
            <div class="row g-3 mb-4">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Mật khẩu mới</label>
                <input v-model="form.newPass" type="password" class="form-control" placeholder="Tối thiểu 6 ký tự" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Xác nhận mật khẩu</label>
                <input v-model="form.confirmPass" type="password" class="form-control" placeholder="Nhập lại mật khẩu" />
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button @click="saveProfile" class="btn btn-save-profile text-white px-4 py-2 rounded-3 fw-bold">
                Lưu thay đổi hồ sơ
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHotelStore } from '../../stores/hotelStore';

export default {
  name: 'HoSoCaNhan',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  data() {
    return {
      form: {
        name: 'Nguyễn Minh Anh',
        phone: '0903 456 789',
        email: 'minhanh.nguyen@gmail.com',
        cccd: '048098001321',
        newPass: '',
        confirmPass: '',
      },
    };
  },
  created() {
    this.form.name = this.hotelStore.guestName;
    this.form.phone = this.hotelStore.guestPhone;
    this.form.email = this.hotelStore.guestEmail;
  },
  methods: {
    saveProfile() {
      if (this.form.newPass && this.form.newPass !== this.form.confirmPass) {
        alert('Mật khẩu xác nhận không khớp!');
        return;
      }
      this.hotelStore.loginGuest(this.form.name, this.form.phone);
      alert('Cập nhật hồ sơ cá nhân thành công!');
    },
    logout() {
      this.hotelStore.logoutGuest();
      this.$router.push('/');
    },
  },
};
</script>

<style scoped>
.guest-profile-container {
  background-color: #F8F9FA;
  min-height: calc(100vh - 76px);
}

.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #5C6862;
}

.text-success-custom {
  color: #1E5E49;
}

.bg-success-custom {
  background-color: #1E5E49 !important;
}

.profile-avatar {
  width: 72px;
  height: 72px;
}

.btn-save-profile {
  background-color: #1E5E49;
  border: none;
}

.btn-save-profile:hover {
  background-color: #164636;
}
</style>
