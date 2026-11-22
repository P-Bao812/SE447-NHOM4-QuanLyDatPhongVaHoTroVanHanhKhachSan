<template>
  <div
    class="modal fade"
    id="modalThemTaiKhoan"
    tabindex="-1"
    aria-labelledby="modalThemTaiKhoanLabel"
    aria-hidden="true"
    ref="modalRef"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <!-- Header -->
        <div class="modal-header border-bottom px-4 py-3">
          <h5 class="modal-title fw-bold text-dark" id="modalThemTaiKhoanLabel">
            <i class="bi bi-person-plus text-teal me-2"></i>
            Thêm tài khoản nhân viên
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <!-- Body Form -->
        <form @submit.prevent="handleSubmit">
          <div class="modal-body px-4 py-3">
            <div class="mb-3">
              <label class="form-label-custom">
                Họ và tên nhân viên <span class="text-danger">*</span>
              </label>
              <input
                type="text"
                class="form-control form-control-custom"
                v-model="form.hoTen"
                placeholder="VD: Trần Thị Phương"
                required
              />
            </div>

            <div class="mb-3">
              <label class="form-label-custom">
                Email / Tên đăng nhập <span class="text-danger">*</span>
              </label>
              <input
                type="email"
                class="form-control form-control-custom"
                v-model="form.tenDangNhap"
                placeholder="VD: phuong@example.com"
                required
              />
            </div>

            <div class="mb-3">
              <label class="form-label-custom">
                Vai trò hệ thống <span class="text-danger">*</span>
              </label>
              <select class="form-select form-control-custom" v-model="form.vaiTro" required>
                <option value="" disabled>-- Chọn vai trò --</option>
                <option
                  v-for="role in availableRoles"
                  :key="role.id || role.tenVaiTro"
                  :value="role.tenVaiTro"
                >
                  {{ role.tenVaiTro }}
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Mật khẩu khởi tạo</label>
              <input
                type="password"
                class="form-control form-control-custom"
                v-model="form.password"
                placeholder="Mặc định: 123456"
              />
              <div class="form-text text-muted small" style="font-size: 0.78rem;">
                Nhân viên có thể đổi mật khẩu sau lần đăng nhập đầu tiên.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label-custom">Số điện thoại liên hệ</label>
              <input
                type="tel"
                class="form-control form-control-custom"
                v-model="form.soDienThoai"
                placeholder="VD: 0912 345 678"
              />
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer border-top px-4 py-3">
            <button
              type="button"
              class="btn btn-light border px-3"
              data-bs-dismiss="modal"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="btn btn-primary-teal px-4"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
              <span>{{ loading ? 'Đang thêm...' : 'Tạo tài khoản' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalThemTaiKhoan',
  props: {
    roles: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['create-account'],
  computed: {
    availableRoles() {
      if (this.roles && this.roles.length > 0) {
        return this.roles;
      }
      return [
        { id: 1, tenVaiTro: 'Admin' },
        { id: 2, tenVaiTro: 'Quản lý' },
        { id: 3, tenVaiTro: 'Lễ tân' },
        { id: 4, tenVaiTro: 'Buồng phòng' },
        { id: 5, tenVaiTro: 'Kế toán' },
      ];
    },
  },
  data() {
    return {
      form: {
        hoTen: '',
        tenDangNhap: '',
        vaiTro: 'Lễ tân',
        password: '',
        soDienThoai: '',
      },
    };
  },
  methods: {
    handleSubmit() {
      if (!this.form.hoTen || !this.form.tenDangNhap || !this.form.vaiTro) {
        return;
      }
      this.$emit('create-account', { ...this.form });
    },
    resetForm() {
      this.form = {
        hoTen: '',
        tenDangNhap: '',
        vaiTro: this.roles.length > 0 ? this.roles[0].tenVaiTro : 'Lễ tân',
        password: '',
        soDienThoai: '',
      };
    },
  },
};
</script>
