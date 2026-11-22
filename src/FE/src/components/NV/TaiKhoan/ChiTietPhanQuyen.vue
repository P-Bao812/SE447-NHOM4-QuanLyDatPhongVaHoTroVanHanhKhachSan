<template>
  <div class="custom-card permission-panel h-100">
    <div v-if="!account" class="text-center py-5 text-muted">
      <i class="bi bi-person-lines-fill fs-1 text-secondary mb-3 d-block"></i>
      <p class="mb-0">Vui lòng chọn một nhân viên từ danh sách bên trái để xem và thay đổi thông tin.</p>
    </div>

    <div v-else>
      <!-- Nút quay lại danh sách khi ở view chi tiết -->
      <div v-if="showBackButton" class="mb-3">
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-2"
          @click="$emit('back-to-list')"
        >
          <i class="bi bi-arrow-left"></i>
          <span>Quay lại danh sách</span>
        </button>
      </div>

      <!-- Tiêu đề và Badge trạng thái -->
      <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
          <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">
            {{ employeeCode }}
          </span>
          <h5 class="fw-bold mb-0 text-dark">
            Quyền truy cập · {{ formData.hoTen || account.nhanVien?.hoTen || account.tenDangNhap }}
          </h5>
        </div>
        <div>
          <span v-if="account.trangThai === 'Đang hoạt động'" class="badge-active-pill">
            • Đang hoạt động
          </span>
          <span v-else class="badge-locked-pill">
            • Đã khóa
          </span>
        </div>
      </div>

      <!-- Form chi tiết để thay đổi nhân viên -->
      <form @submit.prevent="handleSave">
        <!-- 1. Họ và tên nhân viên -->
        <div class="mb-3">
          <label class="form-label-custom">
            Họ và tên nhân viên <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
              <i class="bi bi-person"></i>
            </span>
            <input
              type="text"
              class="form-control form-control-custom border-start-0 ps-0"
              v-model="formData.hoTen"
              placeholder="Nhập họ tên nhân viên"
              required
            />
          </div>
        </div>

        <!-- 2. Email / Tên đăng nhập -->
        <div class="mb-3">
          <label class="form-label-custom">
            Email đăng nhập <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
              <i class="bi bi-envelope"></i>
            </span>
            <input
              type="email"
              class="form-control form-control-custom border-start-0 ps-0"
              v-model="formData.tenDangNhap"
              placeholder="email@example.com"
              required
            />
          </div>
        </div>

        <!-- 3. Số điện thoại liên hệ -->
        <div class="mb-3">
          <label class="form-label-custom">Số điện thoại liên hệ</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
              <i class="bi bi-telephone"></i>
            </span>
            <input
              type="tel"
              class="form-control form-control-custom border-start-0 ps-0"
              v-model="formData.soDienThoai"
              placeholder="VD: 0912 345 678"
            />
          </div>
        </div>

        <!-- 4. Vai trò dropdown -->
        <div class="mb-4">
          <label class="form-label-custom">
            Vai trò hệ thống <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
              <i class="bi bi-shield-check"></i>
            </span>
            <select
              v-model="formData.vaiTro"
              class="form-select form-control-custom border-start-0 ps-0"
              :disabled="loadingSave || loadingToggle"
              required
            >
              <option
                v-for="role in availableRoles"
                :key="role.id || role.tenVaiTro"
                :value="role.tenVaiTro"
              >
                {{ role.tenVaiTro }}
              </option>
            </select>
          </div>
        </div>

        <!-- Box cảnh báo / lưu ý thông tin -->
        <div class="info-alert-box mb-4">
          <i class="bi bi-info-circle-fill flex-shrink-0"></i>
          <div>
            Thay đổi vai trò áp dụng ở lần đồng bộ quyền tiếp theo. Chỉ Admin hoặc Quản lý được sửa phân quyền.
          </div>
        </div>

        <!-- Các nút hành động -->
        <div class="d-flex flex-column gap-2">
          <!-- Nút Lưu thay đổi -->
          <button
            type="submit"
            class="btn btn-save-role shadow-sm"
            :disabled="loadingSave"
          >
            <span v-if="loadingSave" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-check-circle-fill me-1"></i>
            <span>{{ loadingSave ? 'Đang lưu...' : 'Lưu thay đổi nhân viên' }}</span>
          </button>

          <!-- Nút Khóa / Mở khóa tài khoản -->
          <button
            type="button"
            class="btn btn-lock-account"
            :disabled="loadingToggle"
            @click="handleToggleStatus"
          >
            <span v-if="loadingToggle" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i :class="account.trangThai === 'Đang hoạt động' ? 'bi bi-lock-fill me-1 text-danger' : 'bi bi-unlock-fill me-1 text-success'"></i>
            <span>
              {{
                loadingToggle
                  ? 'Đang xử lý...'
                  : account.trangThai === 'Đang hoạt động'
                  ? 'Khóa tài khoản'
                  : 'Mở khóa tài khoản'
              }}
            </span>
          </button>
        </div>
      </form>

      <!-- Ghi chú chân panel -->
      <div class="panel-footer-note">
        Khóa tài khoản sẽ kết thúc phiên truy cập; không xóa lịch sử xử lý công việc.
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ChiTietPhanQuyen',
  props: {
    account: {
      type: Object,
      default: null,
    },
    roles: {
      type: Array,
      default: () => [],
    },
    loadingSave: {
      type: Boolean,
      default: false,
    },
    loadingToggle: {
      type: Boolean,
      default: false,
    },
    showBackButton: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['save-role', 'save-info', 'toggle-status', 'back-to-list'],
  data() {
    return {
      formData: {
        hoTen: '',
        tenDangNhap: '',
        soDienThoai: '',
        vaiTro: 'Lễ tân',
      },
    };
  },
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
    employeeCode() {
      if (!this.account) return '';
      return this.account.nhanVien?.maNV || `TK-${this.account.id}`;
    },
  },
  watch: {
    account: {
      immediate: true,
      deep: true,
      handler(newAcc) {
        if (newAcc) {
          this.formData = {
            hoTen: newAcc.nhanVien?.hoTen || '',
            tenDangNhap: newAcc.tenDangNhap || '',
            soDienThoai: newAcc.nhanVien?.soDienThoai || '',
            vaiTro: newAcc.vaiTro || (newAcc.nhanVien ? newAcc.nhanVien.chucVu : 'Lễ tân'),
          };
        }
      },
    },
  },
  methods: {
    handleSave() {
      if (!this.formData.hoTen || !this.formData.vaiTro) {
        return;
      }
      this.$emit('save-info', {
        id: this.account.id,
        hoTen: this.formData.hoTen,
        tenDangNhap: this.formData.tenDangNhap,
        soDienThoai: this.formData.soDienThoai,
        vaiTro: this.formData.vaiTro,
      });
    },
    handleToggleStatus() {
      this.$emit('toggle-status', this.account.id);
    },
  },
};
</script>
