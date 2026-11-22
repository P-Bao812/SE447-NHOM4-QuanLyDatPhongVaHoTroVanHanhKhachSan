<template>
  <div v-if="show" class="modal-backdrop-custom">
    <div class="modal-dialog-custom modal-lg animate-scale-up">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER -->
        <div class="modal-header bg-dark-teal text-white px-4 py-3 border-0 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon-box rounded-circle d-flex align-items-center justify-content-center bg-white-15">
              <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0">Tạo yêu cầu dịch vụ phòng</h5>
              <p class="mb-0 text-white-75 small">
                Tiếp nhận trực tiếp yêu cầu từ khách lưu trú và phân công bộ phận thực hiện.
              </p>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="closeModal" :disabled="loading"></button>
        </div>

        <!-- FORM BODY -->
        <form @submit.prevent="handleSubmit" class="modal-body p-4">
          <div v-if="errorMessage" class="alert alert-danger py-2 small rounded-3 mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ errorMessage }}</span>
          </div>

          <div class="row g-3">
            <!-- 1. CHỌN PHÒNG LƯU TRÚ -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Phòng lưu trú & Khách hàng <span class="text-danger">*</span>
              </label>
              <select v-model="form.maLuuTru" class="form-select" required>
                <option value="" disabled>-- Chọn phòng đang có khách --</option>
                <option v-for="room in activeRooms" :key="room.maLuuTru" :value="room.maLuuTru">
                  Phòng {{ room.tenPhong }} · {{ room.tenKhachHang }}
                </option>
              </select>
              <div class="form-text text-muted" style="font-size: 0.72rem;">
                Chỉ hiển thị các phòng đang có lượt lưu trú hợp lệ tại khách sạn.
              </div>
            </div>

            <!-- 2. CHỌN DỊCH VỤ -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Dịch vụ khách sạn <span class="text-danger">*</span>
              </label>
              <select v-model="form.maDichVu" class="form-select" required @change="onServiceChange">
                <option value="" disabled>-- Chọn dịch vụ --</option>
                <option v-for="s in services" :key="s.id" :value="s.id">
                  {{ s.tenDichVu }} ({{ formatCurrency(s.donGia) }} / {{ s.donViTinh }})
                </option>
              </select>
            </div>

            <!-- 3. SỐ LƯỢNG -->
            <div class="col-6 col-md-4">
              <label class="form-label fw-semibold text-dark small mb-1">
                Số lượng ({{ selectedService?.donViTinh || 'Lượt' }}) <span class="text-danger">*</span>
              </label>
              <input
                v-model.number="form.soLuong"
                type="number"
                min="1"
                max="100"
                class="form-control"
                required
              />
            </div>

            <!-- 4. ĐƠN GIÁ & TỔNG TIỀN (DỰ KIẾN) -->
            <div class="col-6 col-md-4">
              <label class="form-label fw-semibold text-dark small mb-1">
                Tổng dự kiến
              </label>
              <div class="form-control bg-light text-teal fw-bold">
                {{ formatCurrency(estimatedTotal) }}
              </div>
            </div>

            <!-- 5. MỨC ĐỘ ƯU TIÊN -->
            <div class="col-12 col-md-4">
              <label class="form-label fw-semibold text-dark small mb-1">
                Mức độ ưu tiên
              </label>
              <select v-model="form.mucDoUuTien" class="form-select">
                <option value="Bình thường">Bình thường</option>
                <option value="Cao">Cao</option>
                <option value="Khẩn cấp">Khẩn cấp</option>
              </select>
            </div>

            <!-- 6. HẸN THỜI GIAN GIAO / HOÀN TẤT -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Thời gian hẹn phục vụ / Hẹn trả
              </label>
              <input
                v-model="form.thoiGianHen"
                type="text"
                class="form-control"
                placeholder="Ví dụ: 10:30 hôm nay hoặc 09/11/2026 - 10:00"
              />
            </div>

            <!-- 7. PHÂN CÔNG NGƯỜI PHỤ TRÁCH -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Phân công trực tiếp (tùy chọn)
              </label>
              <select v-model="form.maNhanVien" class="form-select">
                <option value="">Chưa phân công (để nhân viên tự nhận)</option>
                <option v-for="nv in staffList" :key="nv.id" :value="nv.id">
                  {{ nv.hoTen }} · {{ nv.boPhan }} ({{ nv.caLam }})
                </option>
              </select>
            </div>

            <!-- 8. GHI CHÚ TỪ KHÁCH HÀNG -->
            <div class="col-12">
              <label class="form-label fw-semibold text-dark small mb-1">
                Ghi chú / Yêu cầu cụ thể của khách
              </label>
              <textarea
                v-model.trim="form.ghiChu"
                class="form-control"
                rows="2"
                placeholder="Ví dụ: Khách dặn ít đá, gọi cửa trước khi vào..."
              ></textarea>
            </div>
          </div>

          <!-- FOOTER -->
          <div class="d-flex align-items-center justify-content-end gap-2 pt-4 mt-3 border-top">
            <button
              type="button"
              class="btn btn-outline-secondary px-4 rounded-pill"
              @click="closeModal"
              :disabled="loading"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="btn btn-teal-primary px-4 rounded-pill d-inline-flex align-items-center gap-2"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status"></span>
              <i v-else class="bi bi-check-circle-fill"></i>
              <span>Tạo yêu cầu ngay</span>
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
  name: 'ModalTaoYeuCau',
  props: {
    show: { type: Boolean, default: false },
    userRole: { type: String, default: 'Lễ tân' }
  },
  emits: ['close', 'created'],
  data() {
    return {
      loading: false,
      errorMessage: '',
      activeRooms: [],
      services: [],
      staffList: [],
      form: {
        maLuuTru: '',
        maDichVu: '',
        soLuong: 1,
        mucDoUuTien: 'Bình thường',
        thoiGianHen: '',
        maNhanVien: '',
        ghiChu: ''
      }
    };
  },
  computed: {
    selectedService() {
      return this.services.find(s => s.id === this.form.maDichVu) || null;
    },
    estimatedTotal() {
      if (!this.selectedService) return 0;
      return (this.form.soLuong || 1) * this.selectedService.donGia;
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.resetForm();
        this.loadPrerequisites();
      }
    }
  },
  methods: {
    resetForm() {
      this.form = {
        maLuuTru: '',
        maDichVu: '',
        soLuong: 1,
        mucDoUuTien: 'Bình thường',
        thoiGianHen: 'Trong vòng 30 phút',
        maNhanVien: '',
        ghiChu: ''
      };
      this.errorMessage = '';
    },
    async loadPrerequisites() {
      try {
        const [resRooms, resServices, resStaff] = await Promise.all([
          yeuCauDichVuService.getPhongDangLuuTru(),
          yeuCauDichVuService.getDanhSachDichVu(),
          yeuCauDichVuService.getNhanVienPhanCong()
        ]);

        if (resRooms.status === 'success') {
          this.activeRooms = resRooms.data || [];
          if (this.activeRooms.length > 0 && !this.form.maLuuTru) {
            this.form.maLuuTru = this.activeRooms[0].maLuuTru;
          }
        }
        if (resServices.status === 'success') {
          this.services = resServices.data || [];
          if (this.services.length > 0 && !this.form.maDichVu) {
            this.form.maDichVu = this.services[0].id;
          }
        }
        if (resStaff.status === 'success') {
          this.staffList = resStaff.data || [];
        }
      } catch (err) {
        console.error('Lỗi tải dữ liệu khởi tạo:', err);
      }
    },
    onServiceChange() {
      // Auto-set priority or default time if needed
    },
    closeModal() {
      if (!this.loading) {
        this.$emit('close');
      }
    },
    async handleSubmit() {
      if (!this.form.maLuuTru || !this.form.maDichVu) {
        this.errorMessage = 'Vui lòng chọn đầy đủ phòng lưu trú và dịch vụ.';
        return;
      }

      this.loading = true;
      this.errorMessage = '';

      try {
        const res = await yeuCauDichVuService.taoYeuCau(this.form, this.userRole);
        if (res.status === 'success') {
          this.$emit('created', {
            message: res.message || 'Tạo yêu cầu dịch vụ thành công!',
            data: res.data
          });
          this.closeModal();
        } else {
          this.errorMessage = res.message || 'Lỗi khi tạo yêu cầu dịch vụ.';
        }
      } catch (err) {
        this.errorMessage = err.message || 'Lỗi kết nối máy chủ.';
      } finally {
        this.loading = false;
      }
    },
    formatCurrency(val) {
      if (!val) return '0 đ';
      return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
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
  max-width: 760px;
  max-height: 90vh;
}

.modal-content {
  max-height: 90vh;
  overflow-y: auto;
  background-color: #ffffff;
}

.bg-dark-teal {
  background: linear-gradient(135deg, #1E5E49 0%, #144636 100%);
}

.bg-white-15 {
  background-color: rgba(255, 255, 255, 0.15);
}

.header-icon-box {
  width: 38px;
  height: 38px;
  font-size: 1.2rem;
}

.btn-teal-primary {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-teal-primary:hover {
  background-color: #154536;
  border-color: #154536;
  color: #ffffff;
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}

.animate-scale-up {
  animation: scaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
