<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-plus fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Thêm trang bị tài sản cho phòng</h5>
              <div class="small opacity-75">Trang bị thiết bị hoặc đồ dùng cố định mới vào phòng</div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <form @submit.prevent="xacNhanThem">
            <!-- Chọn phòng -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Phòng trang bị <span class="text-danger">*</span>
              </label>
              <select v-model="form.maPhong" class="form-select rounded-3 py-2" required :disabled="loading">
                <option value="" disabled>-- Chọn phòng --</option>
                <option v-for="p in danhSachPhong" :key="p.id" :value="p.id">
                  Phòng {{ p.tenPhong }} (Tầng {{ p.tang }})
                </option>
              </select>
            </div>

            <!-- Tên tài sản -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Tên tài sản / Thiết bị <span class="text-danger">*</span>
              </label>
              <input
                v-model="form.tenTaiSan"
                type="text"
                class="form-control rounded-3 py-2"
                placeholder="Ví dụ: Tivi Samsung Smart 55 inch, Máy sấy tóc Panasonic..."
                required
                :disabled="loading"
              />
            </div>

            <div class="row g-3 mb-3">
              <!-- Số lượng -->
              <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary">Số lượng</label>
                <input
                  v-model.number="form.soLuong"
                  type="number"
                  min="1"
                  class="form-control rounded-3 py-2"
                  required
                  :disabled="loading"
                />
              </div>

              <!-- Giá trị ước tính -->
              <div class="col-md-8">
                <label class="form-label small fw-bold text-secondary">
                  Giá trị ước tính (VNĐ) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <input
                    v-model.number="form.giaTri"
                    type="number"
                    min="0"
                    step="10000"
                    class="form-control rounded-start-3 py-2"
                    required
                    :disabled="loading"
                  />
                  <span class="input-group-text bg-white text-muted">VNĐ</span>
                </div>
              </div>
            </div>

            <!-- Tình trạng ban đầu -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Tình trạng ban đầu</label>
              <select v-model="form.tinhTrang" class="form-select rounded-3 py-2" :disabled="loading">
                <option value="Bình thường">Bình thường / Hoạt động tốt</option>
                <option value="Mới trang bị">Mới trang bị 100%</option>
              </select>
            </div>

            <!-- Ghi chú -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Ghi chú</label>
              <input
                v-model="form.ghiChu"
                type="text"
                class="form-control rounded-3 py-2"
                placeholder="Ví dụ: Trang bị thay thế ngày..."
                :disabled="loading"
              />
            </div>

            <!-- Lỗi nếu có -->
            <div v-if="errorMsg" class="alert alert-danger py-2 small mb-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMsg }}
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
              <button type="button" class="btn btn-light rounded-3 px-3" @click="dongModal" :disabled="loading">
                Hủy bỏ
              </button>
              <button
                type="submit"
                class="btn btn-teal-primary text-white rounded-3 px-4 fw-semibold d-inline-flex align-items-center gap-2"
                :disabled="loading || !form.maPhong || !form.tenTaiSan"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-check2"></i>
                <span>Thêm trang bị</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue';
import { minibarTaiSanService } from './minibarTaiSanService';

export default {
  name: 'ModalThemTaiSan',
  props: {
    show: { type: Boolean, default: false },
    danhSachPhong: { type: Array, default: () => [] },
    phongMacDinhId: { type: [Number, String], default: null }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errorMsg = ref('');
    const form = ref({
      maPhong: '',
      tenTaiSan: '',
      soLuong: 1,
      giaTri: 500000,
      tinhTrang: 'Bình thường',
      ghiChu: ''
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        errorMsg.value = '';
        form.value.maPhong = props.phongMacDinhId || (props.danhSachPhong[0]?.id || '');
        form.value.tenTaiSan = '';
        form.value.soLuong = 1;
        form.value.giaTri = 500000;
        form.value.tinhTrang = 'Bình thường';
        form.value.ghiChu = '';
      }
    });

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanThem = async () => {
      if (!form.value.maPhong) {
        errorMsg.value = 'Vui lòng chọn phòng.';
        return;
      }
      if (!form.value.tenTaiSan.trim()) {
        errorMsg.value = 'Vui lòng nhập tên tài sản.';
        return;
      }

      loading.value = true;
      errorMsg.value = '';

      try {
        const payload = {
          maPhong: Number(form.value.maPhong),
          tenTaiSan: form.value.tenTaiSan.trim(),
          soLuong: Number(form.value.soLuong) || 1,
          giaTri: Number(form.value.giaTri) || 0,
          tinhTrang: form.value.tinhTrang,
          ghiChu: form.value.ghiChu
        };

        const res = await minibarTaiSanService.themTaiSan(payload);
        emit('success', res.message || 'Thêm tài sản cho phòng thành công!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Lỗi khi thêm tài sản.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      errorMsg,
      dongModal,
      xacNhanThem
    };
  }
};
</script>

<style scoped>
.bg-teal-header {
  background-color: #1e5e49;
}
.btn-teal-primary {
  background-color: #1e5e49;
  border-color: #1e5e49;
}
.btn-teal-primary:hover {
  background-color: #164636;
  border-color: #164636;
}
</style>
