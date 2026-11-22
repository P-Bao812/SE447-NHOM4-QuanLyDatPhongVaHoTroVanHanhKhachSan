<template>
  <div v-if="show" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3" @click.self="$emit('close')">
    <div class="modal-content-custom bg-white rounded-4 shadow-xl overflow-hidden animate-fade-in" style="max-width: 580px; width: 100%;">
      <!-- MODAL HEADER -->
      <div class="modal-header-custom px-4 py-3 d-flex align-items-center justify-content-between border-bottom">
        <div class="d-flex align-items-center gap-2.5">
          <div class="header-icon-box d-flex align-items-center justify-content-center text-teal">
            <i class="bi bi-calendar-check fs-5"></i>
          </div>
          <div>
            <h5 class="fw-bold mb-0 text-main-dark fs-6">Đăng ký sử dụng dịch vụ phòng</h5>
            <span class="text-muted" style="font-size: 0.78rem;">Phục vụ trực tiếp tại phòng lưu trú</span>
          </div>
        </div>
        <button type="button" class="btn-close-custom" @click="$emit('close')" aria-label="Đóng">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <!-- MODAL BODY -->
      <div class="modal-body-custom px-4 py-3.5" style="max-height: 80vh; overflow-y: auto;">
        <!-- DỊCH VỤ ĐƯỢC CHỌN -->
        <div class="service-selected-card p-3 rounded-3 mb-3 d-flex align-items-center gap-3">
          <img
            :src="service?.hinhAnh || defaultImage"
            :alt="service?.tenDichVu"
            class="service-thumb rounded-3 object-fit-cover flex-shrink-0"
            style="width: 72px; height: 72px;"
          />
          <div class="flex-grow-1 overflow-hidden">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge badge-category-sm">{{ service?.loaiDichVu || 'Dịch vụ' }}</span>
              <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">
                <i class="bi bi-check-circle-fill me-1"></i>Đang phục vụ
              </span>
            </div>
            <h6 class="fw-bold text-main-dark mb-1 text-truncate" :title="service?.tenDichVu">
              {{ service?.tenDichVu }}
            </h6>
            <div class="text-teal fw-bold" style="font-size: 0.95rem;">
              {{ formatCurrency(service?.donGia) }}
              <span class="text-muted fw-normal small">/ {{ service?.donViTinh || 'Lượt' }}</span>
            </div>
          </div>
        </div>

        <form @submit.prevent="handleSubmit">
          <!-- 1. PHÒNG ĐANG LƯU TRÚ -->
          <div class="mb-3">
            <label class="form-label-custom fw-semibold d-flex justify-content-between align-items-center">
              <span>
                <i class="bi bi-door-open me-1 text-teal"></i>Phòng đang lưu trú <span class="text-danger">*</span>
              </span>
              <span v-if="selectedRoom" class="text-success small fw-normal">
                <i class="bi bi-shield-check me-1"></i>Đang lưu trú hợp lệ
              </span>
            </label>

            <select
              v-model="formData.maLuuTru"
              class="form-select form-select-custom"
              :class="{ 'is-invalid': errors.maLuuTru }"
              required
            >
              <option value="" disabled>-- Chọn phòng của quý khách --</option>
              <option
                v-for="room in activeRooms"
                :key="room.maLuuTru"
                :value="room.maLuuTru"
              >
                {{ room.displayText }} (Nhận: {{ room.thoiGianNhan }} - Trả: {{ room.thoiGianTra }})
              </option>
            </select>
            <div v-if="errors.maLuuTru" class="invalid-feedback d-block small mt-1">
              {{ errors.maLuuTru }}
            </div>
            <div v-if="activeRooms.length === 0" class="text-danger small mt-1">
              <i class="bi bi-exclamation-triangle me-1"></i>Hiện không tìm thấy phòng lưu trú hợp lệ. Vui lòng liên hệ lễ tân!
            </div>
          </div>

          <!-- THÔNG TIN KHÁCH HÀNG & PHÒNG XÁC NHẬN -->
          <div v-if="selectedRoom" class="room-stay-details p-2.5 rounded-3 mb-3 bg-light-soft border border-light-subtle">
            <div class="row g-2 small">
              <div class="col-6">
                <span class="text-muted">Khách lưu trú:</span>
                <strong class="d-block text-dark">{{ selectedRoom.tenKhachHang }}</strong>
              </div>
              <div class="col-6">
                <span class="text-muted">Số phòng & Tầng:</span>
                <strong class="d-block text-dark">Phòng {{ selectedRoom.tenPhong }} (Tầng {{ selectedRoom.tang }})</strong>
              </div>
              <div class="col-12 text-muted" style="font-size: 0.78rem;">
                <i class="bi bi-clock-history me-1"></i>Thời gian lưu trú: {{ selectedRoom.thoiGianNhan }} &rarr; {{ selectedRoom.thoiGianTra }}
              </div>
            </div>
          </div>

          <!-- 2. SỐ LƯỢNG SỬ DỤNG -->
          <div class="mb-3">
            <label class="form-label-custom fw-semibold">
              <i class="bi bi-123 me-1 text-teal"></i>Số lượng ({{ service?.donViTinh || 'Lượt' }}) <span class="text-danger">*</span>
            </label>
            <div class="d-flex align-items-center gap-3">
              <div class="quantity-counter-box d-flex align-items-center">
                <button
                  type="button"
                  class="btn-counter"
                  :disabled="formData.soLuong <= 1"
                  @click="formData.soLuong > 1 ? formData.soLuong-- : null"
                >
                  <i class="bi bi-dash"></i>
                </button>
                <input
                  v-model.number="formData.soLuong"
                  type="number"
                  min="1"
                  max="100"
                  class="input-counter text-center"
                  required
                />
                <button
                  type="button"
                  class="btn-counter"
                  :disabled="formData.soLuong >= 100"
                  @click="formData.soLuong < 100 ? formData.soLuong++ : null"
                >
                  <i class="bi bi-plus"></i>
                </button>
              </div>
              <div class="text-muted small">
                Tạm tính: <strong class="text-teal fs-6">{{ formatCurrency(totalEstimated) }}</strong>
              </div>
            </div>
            <div v-if="errors.soLuong" class="text-danger small mt-1">
              {{ errors.soLuong }}
            </div>
          </div>

          <!-- 3. THỜI GIAN MONG MUỐN PHỤC VỤ -->
          <div class="mb-3">
            <label class="form-label-custom fw-semibold">
              <i class="bi bi-clock me-1 text-teal"></i>Thời gian mong muốn phục vụ <span class="text-danger">*</span>
            </label>
            <input
              v-model="formData.thoiGianMongMuon"
              type="datetime-local"
              class="form-control form-control-custom"
              :class="{ 'is-invalid': errors.thoiGianMongMuon }"
              :min="minDateTime"
              required
            />
            <!-- Quick time buttons -->
            <div class="d-flex flex-wrap gap-1.5 mt-2">
              <button
                type="button"
                class="btn btn-sm btn-quick-time"
                @click="setQuickTime(15)"
              >
                +15 phút nữa
              </button>
              <button
                type="button"
                class="btn btn-sm btn-quick-time"
                @click="setQuickTime(30)"
              >
                +30 phút nữa
              </button>
              <button
                type="button"
                class="btn btn-sm btn-quick-time"
                @click="setQuickTime(60)"
              >
                +1 giờ nữa
              </button>
            </div>
            <div v-if="errors.thoiGianMongMuon" class="invalid-feedback d-block small mt-1">
              {{ errors.thoiGianMongMuon }}
            </div>
          </div>

          <!-- 4. GHI CHÚ YÊU CẦU -->
          <div class="mb-3">
            <label class="form-label-custom fw-semibold">
              <i class="bi bi-chat-left-text me-1 text-teal"></i>Ghi chú hoặc yêu cầu riêng
            </label>
            <textarea
              v-model="formData.ghiChu"
              rows="3"
              class="form-control form-control-custom"
              placeholder="Ví dụ: Đồ ăn ít cay, ủi phẳng áo sơ mi trắng, giao vào phòng trước 12:00..."
              maxlength="500"
            ></textarea>
            <div class="text-end text-muted small mt-1" style="font-size: 0.72rem;">
              {{ (formData.ghiChu || '').length }}/500 ký tự
            </div>
          </div>

          <!-- TỔNG KẾT CHI PHÍ -->
          <div class="order-summary-box p-3 rounded-3 mb-4 bg-teal-soft border border-teal-light">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Đơn giá dịch vụ:</span>
              <span class="text-dark fw-medium small">{{ formatCurrency(service?.donGia) }} / {{ service?.donViTinh }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="text-muted small">Số lượng đăng ký:</span>
              <span class="text-dark fw-medium small">{{ formData.soLuong }} {{ service?.donViTinh }}</span>
            </div>
            <hr class="my-2 border-teal-subtle" />
            <div class="d-flex justify-content-between align-items-center">
              <span class="fw-bold text-main-dark">Tổng tiền tạm tính:</span>
              <span class="fw-bold text-teal fs-5">{{ formatCurrency(totalEstimated) }}</span>
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
              <i class="bi bi-info-circle me-1"></i>Chi phí sẽ được đối soát và cộng vào hóa đơn thanh toán khi dịch vụ hoàn thành.
            </div>
          </div>

          <!-- MODAL ACTIONS -->
          <div class="d-flex align-items-center justify-content-end gap-2.5 pt-2 border-top">
            <button
              type="button"
              class="btn btn-outline-secondary btn-cancel rounded-pill px-4"
              :disabled="loading"
              @click="$emit('close')"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              class="btn btn-teal-primary rounded-pill px-4 d-inline-flex align-items-center gap-2 shadow-sm"
              :disabled="loading || activeRooms.length === 0"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <i v-else class="bi bi-send-fill"></i>
              <span>{{ loading ? 'Đang gửi...' : 'Gửi yêu cầu' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalDangKyDichVu',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    service: {
      type: Object,
      default: null
    },
    activeRooms: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit'],
  data() {
    return {
      defaultImage: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
      formData: {
        maLuuTru: '',
        soLuong: 1,
        thoiGianMongMuon: '',
        ghiChu: ''
      },
      errors: {}
    };
  },
  computed: {
    selectedRoom() {
      if (!this.formData.maLuuTru) return null;
      return this.activeRooms.find(r => r.maLuuTru === this.formData.maLuuTru) || null;
    },
    totalEstimated() {
      const price = this.service?.donGia || 0;
      const qty = this.formData.soLuong || 1;
      return price * qty;
    },
    minDateTime() {
      // Tối thiểu là thời điểm hiện tại
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      return now.toISOString().slice(0, 16);
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.resetForm();
      }
    },
    activeRooms: {
      immediate: true,
      handler(rooms) {
        // Tự động chọn phòng đầu tiên nếu có
        if (rooms && rooms.length > 0 && !this.formData.maLuuTru) {
          this.formData.maLuuTru = rooms[0].maLuuTru;
        }
      }
    }
  },
  methods: {
    resetForm() {
      const defaultTime = new Date();
      defaultTime.setMinutes(defaultTime.getMinutes() + 30); // Mặc định sau 30 phút
      defaultTime.setMinutes(defaultTime.getMinutes() - defaultTime.getTimezoneOffset());

      this.formData = {
        maLuuTru: this.activeRooms.length > 0 ? this.activeRooms[0].maLuuTru : '',
        soLuong: 1,
        thoiGianMongMuon: defaultTime.toISOString().slice(0, 16),
        ghiChu: ''
      };
      this.errors = {};
    },
    setQuickTime(minutes) {
      const targetTime = new Date();
      targetTime.setMinutes(targetTime.getMinutes() + minutes);
      targetTime.setMinutes(targetTime.getMinutes() - targetTime.getTimezoneOffset());
      this.formData.thoiGianMongMuon = targetTime.toISOString().slice(0, 16);
    },
    validate() {
      this.errors = {};
      if (!this.formData.maLuuTru) {
        this.errors.maLuuTru = 'Vui lòng chọn phòng đang lưu trú của quý khách.';
      }
      if (!this.formData.soLuong || this.formData.soLuong < 1) {
        this.errors.soLuong = 'Số lượng sử dụng tối thiểu là 1.';
      }
      if (!this.formData.thoiGianMongMuon) {
        this.errors.thoiGianMongMuon = 'Vui lòng chọn thời gian mong muốn phục vụ.';
      }
      return Object.keys(this.errors).length === 0;
    },
    handleSubmit() {
      if (!this.validate()) return;

      const payload = {
        maDichVu: this.service?.id,
        maLuuTru: this.formData.maLuuTru,
        maPhong: this.selectedRoom?.maPhong,
        soLuong: this.formData.soLuong,
        thoiGianMongMuon: this.formData.thoiGianMongMuon,
        ghiChu: this.formData.ghiChu
      };

      this.$emit('submit', payload);
    },
    formatCurrency(val) {
      if (!val && val !== 0) return '0 đ';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    }
  }
};
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(18, 32, 26, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1060;
}

.modal-content-custom {
  background-color: #ffffff;
  border: 1px solid #EAE8E1;
  animation: slideUp 0.25s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.header-icon-box {
  width: 38px;
  height: 38px;
  background-color: #EAF3EE;
  border-radius: 10px;
}

.text-teal {
  color: #1E5E49 !important;
}

.text-main-dark {
  color: #172620;
}

.btn-close-custom {
  background: transparent;
  border: none;
  font-size: 1.1rem;
  color: #7A857F;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close-custom:hover {
  color: #172620;
  background-color: #F3F4F6;
}

.service-selected-card {
  background-color: #FAF9F5;
  border: 1px solid #EAE8E1;
}

.badge-category-sm {
  background-color: #EAF3EE;
  color: #1E5E49;
  font-weight: 600;
  font-size: 0.72rem;
  padding: 3px 8px;
  border-radius: 6px;
}

.form-label-custom {
  font-size: 0.86rem;
  color: #2D3A34;
  margin-bottom: 6px;
}

.form-select-custom,
.form-control-custom {
  border: 1.5px solid #D8DFDB;
  border-radius: 10px;
  padding: 9px 12px;
  font-size: 0.9rem;
  transition: border-color 0.2s;
}

.form-select-custom:focus,
.form-control-custom:focus {
  border-color: #1E5E49;
  box-shadow: 0 0 0 3px rgba(30, 94, 73, 0.15);
}

.bg-light-soft {
  background-color: #F8FAF9;
}

.quantity-counter-box {
  border: 1.5px solid #D8DFDB;
  border-radius: 10px;
  overflow: hidden;
  width: 140px;
}

.btn-counter {
  width: 40px;
  height: 38px;
  border: none;
  background-color: #F8FAF9;
  color: #172620;
  font-size: 1.1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s;
}

.btn-counter:hover:not(:disabled) {
  background-color: #EAF3EE;
  color: #1E5E49;
}

.btn-counter:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.input-counter {
  width: 60px;
  height: 38px;
  border: none;
  border-left: 1px solid #EAE8E1;
  border-right: 1px solid #EAE8E1;
  font-weight: 600;
  font-size: 0.95rem;
  outline: none;
}

.btn-quick-time {
  background-color: #F0F4F2;
  color: #3B4B43;
  border: 1px solid #D9E2DE;
  border-radius: 14px;
  font-size: 0.74rem;
  font-weight: 500;
  padding: 3px 10px;
  transition: all 0.15s;
}

.btn-quick-time:hover {
  background-color: #1E5E49;
  color: #ffffff;
  border-color: #1E5E49;
}

.bg-teal-soft {
  background-color: #F4F8F6;
}

.border-teal-light {
  border-color: #D6E6DF !important;
}

.border-teal-subtle {
  border-color: #CBE0D7 !important;
}

.btn-teal-primary {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 9px 24px;
  transition: all 0.2s;
}

.btn-teal-primary:hover:not(:disabled) {
  background-color: #164637;
  border-color: #164637;
  color: #ffffff;
}

.btn-cancel {
  border-color: #D2D6D4;
  color: #4A5550;
  font-size: 0.9rem;
  padding: 9px 20px;
}

.btn-cancel:hover {
  background-color: #F3F4F6;
  color: #172620;
}
</style>
