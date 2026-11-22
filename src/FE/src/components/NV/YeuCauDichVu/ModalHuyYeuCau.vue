<template>
  <div v-if="show" class="modal-backdrop-custom">
    <div class="modal-dialog-custom modal-sm-custom animate-scale-up">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="modal-header bg-danger-subtle border-0 px-4 py-3 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-x-octagon-fill text-danger fs-5"></i>
            <h6 class="modal-title fw-bold mb-0 text-danger-emphasis">Hủy yêu cầu dịch vụ</h6>
          </div>
          <button type="button" class="btn-close" @click="closeModal" :disabled="loading"></button>
        </div>

        <!-- FORM BODY -->
        <form @submit.prevent="handleSubmit" class="modal-body p-4 bg-white">
          <div class="mb-3">
            <div class="p-2.5 rounded-3 bg-light border mb-3 small">
              <div>Mã yêu cầu: <strong class="text-dark">{{ requestData?.maYeuCauCode || requestData?.maYeuCau }}</strong></div>
              <div>Dịch vụ: <strong class="text-teal">{{ requestData?.tenDichVu }}</strong> · Phòng {{ requestData?.tenPhong }}</div>
              <div>Khách hàng: <strong>{{ requestData?.tenKhachHang }}</strong></div>
            </div>

            <label class="form-label fw-semibold text-dark small mb-1">
              Lý do hủy yêu cầu <span class="text-danger">*</span>
            </label>
            <textarea
              v-model.trim="lyDoHuy"
              class="form-control rounded-3"
              rows="3"
              placeholder="Ví dụ: Khách hàng đổi kế hoạch đi ra ngoài / Phòng trả sớm / Yêu cầu trùng lặp..."
              required
            ></textarea>
            <div class="form-text text-muted" style="font-size: 0.75rem;">
              Lý do hủy sẽ được lưu vào cơ sở dữ liệu và tự động gửi thông báo đến khách hàng cùng nhân viên phụ trách.
            </div>
          </div>

          <div v-if="errorMessage" class="alert alert-danger py-2 small rounded-3 mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ errorMessage }}
          </div>

          <!-- FOOTER BUTTONS -->
          <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
            <button
              type="button"
              class="btn btn-outline-secondary btn-sm px-3 rounded-pill"
              @click="closeModal"
              :disabled="loading"
            >
              Đóng
            </button>
            <button
              type="submit"
              class="btn btn-danger btn-sm px-3.5 rounded-pill d-inline-flex align-items-center gap-1.5 shadow-xs"
              :disabled="loading || !lyDoHuy"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
              <i v-else class="bi bi-trash-fill"></i>
              <span>Xác nhận hủy yêu cầu</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import yeuCauDichVuService from './yeuCauDichVuService.js';

export default {
  name: 'ModalHuyYeuCau',
  props: {
    show: { type: Boolean, default: false },
    requestData: { type: Object, default: () => null },
    userRole: { type: String, default: 'Lễ tân' }
  },
  emits: ['close', 'submitted'],
  data() {
    return {
      lyDoHuy: '',
      loading: false,
      errorMessage: ''
    };
  },
  watch: {
    show(val) {
      if (val) {
        this.lyDoHuy = '';
        this.errorMessage = '';
      }
    }
  },
  methods: {
    closeModal() {
      if (!this.loading) {
        this.$emit('close');
      }
    },
    async handleSubmit() {
      if (!this.lyDoHuy) {
        this.errorMessage = 'Vui lòng nhập lý do hủy.';
        return;
      }

      this.loading = true;
      this.errorMessage = '';
      try {
        const res = await yeuCauDichVuService.huyYeuCau(
          this.requestData.id,
          this.lyDoHuy,
          this.userRole
        );
        this.$emit('submitted', {
          id: this.requestData.id,
          lyDoHuy: this.lyDoHuy,
          data: res.data
        });
        this.closeModal();
      } catch (err) {
        this.errorMessage = err.message || 'Lỗi khi hủy yêu cầu.';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1060;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 480px;
}

.animate-scale-up {
  animation: scaleUp 0.18s ease-out;
}

@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.96);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.text-teal {
  color: #1E5E49 !important;
}
</style>
