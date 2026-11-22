<template>
  <div v-if="show" class="modal-backdrop-custom">
    <div class="modal-dialog-custom modal-sm-custom animate-scale-up">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="modal-header bg-warning text-dark px-4 py-3 border-0 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-dark"></i>
            <h6 class="modal-title fw-bold mb-0">Báo cáo sự cố phát sinh</h6>
          </div>
          <button type="button" class="btn-close" @click="closeModal" :disabled="loading"></button>
        </div>

        <!-- FORM BODY -->
        <form @submit.prevent="handleSubmit" class="modal-body p-4">
          <div class="mb-3">
            <p class="small text-muted mb-2">
              Báo cáo sự cố liên quan đến yêu cầu <strong class="text-dark">{{ requestData?.maYeuCauCode || requestData?.maYeuCau }}</strong>
              (Phòng {{ requestData?.tenPhong }} - {{ requestData?.tenDichVu }}).
            </p>
            <label class="form-label fw-semibold text-dark small mb-1">
              Mô tả chi tiết sự cố <span class="text-danger">*</span>
            </label>
            <textarea
              v-model.trim="baoSuCo"
              class="form-control rounded-3"
              rows="3"
              placeholder="Ví dụ: Khách không có trong phòng khi đến phục vụ / Hỏng hóc ổ cắm điện / Đồ ủi phát sinh vết ố cũ..."
              required
            ></textarea>
            <div class="form-text text-muted" style="font-size: 0.75rem;">
              Thông báo khẩn sẽ được chuyển ngay đến Admin và Lễ tân trực ca để hỗ trợ kịp thời.
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
              Hủy
            </button>
            <button
              type="submit"
              class="btn btn-warning btn-sm px-3 rounded-pill d-inline-flex align-items-center gap-1.5 fw-semibold"
              :disabled="loading || !baoSuCo"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
              <i v-else class="bi bi-send-fill"></i>
              <span>Gửi báo cáo</span>
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
  name: 'ModalBaoSuCo',
  props: {
    show: { type: Boolean, default: false },
    requestData: { type: Object, default: () => null },
    userRole: { type: String, default: 'Nhân viên' }
  },
  emits: ['close', 'submitted'],
  data() {
    return {
      baoSuCo: '',
      loading: false,
      errorMessage: ''
    };
  },
  watch: {
    show(val) {
      if (val) {
        this.baoSuCo = '';
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
      if (!this.baoSuCo) {
        this.errorMessage = 'Vui lòng nhập mô tả sự cố.';
        return;
      }
      this.loading = true;
      this.errorMessage = '';

      try {
        const res = await yeuCauDichVuService.baoSuCo(this.requestData.id, this.baoSuCo, this.userRole);
        if (res.status === 'success') {
          this.$emit('submitted', {
            id: this.requestData.id,
            message: res.message || 'Đã gửi báo cáo sự cố thành công.'
          });
          this.closeModal();
        } else {
          this.errorMessage = res.message || 'Lỗi gửi báo cáo sự cố.';
        }
      } catch (err) {
        this.errorMessage = err.message || 'Lỗi kết nối máy chủ.';
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
  background-color: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  z-index: 1060;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 480px;
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.animate-scale-up {
  animation: scaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
