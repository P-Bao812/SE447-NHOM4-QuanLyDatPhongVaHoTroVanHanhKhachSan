<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-danger text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Báo hỏng / Mất mát tài sản phòng</h5>
              <div class="small opacity-75">Ghi nhận sự cố trang thiết bị phòng để bảo trì & xử lý</div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <form @submit.prevent="xacNhanBaoHong">
            <!-- Chọn tài sản -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Tài sản / Thiết bị gặp sự cố <span class="text-danger">*</span>
              </label>
              <select
                v-model="form.maTaiSan"
                class="form-select rounded-3 py-2"
                required
                :disabled="loading || Boolean(taiSanId)"
              >
                <option value="" disabled>-- Chọn thiết bị --</option>
                <option v-for="ts in danhSachTaiSan" :key="ts.id" :value="ts.id">
                  [{{ ts.maTaiSan }}] {{ ts.tenTaiSan }} - Phòng {{ ts.tenPhong }}
                </option>
              </select>
            </div>

            <!-- Loại sự cố & Số lượng -->
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary">
                  Loại sự cố <span class="text-danger">*</span>
                </label>
                <select v-model="form.loaiPhatSinh" class="form-select rounded-3 py-2" required :disabled="loading">
                  <option value="Hư hỏng">Hư hỏng thiết bị</option>
                  <option value="Mất mát">Mất mát / Thất lạc</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-bold text-secondary">
                  Số lượng ảnh hưởng <span class="text-danger">*</span>
                </label>
                <input
                  v-model.number="form.soLuong"
                  type="number"
                  min="1"
                  class="form-control rounded-3 py-2"
                  required
                  :disabled="loading"
                />
              </div>
            </div>

            <!-- Chi phí đền bù đề xuất -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Số tiền đền bù đề xuất (VNĐ)
              </label>
              <div class="input-group">
                <input
                  v-model.number="form.soTienBoiThuong"
                  type="number"
                  min="0"
                  step="10000"
                  class="form-control rounded-start-3 py-2"
                  placeholder="Nhập 0 nếu do hao mòn tự nhiên..."
                  :disabled="loading"
                />
                <span class="input-group-text bg-white text-muted">VNĐ</span>
              </div>
              <div class="form-text small text-muted">
                Có thể nhập <strong>0 đ</strong> nếu do hao mòn kỹ thuật, không tự ý gán lỗi phạt khách khi chưa có thẩm định.
              </div>
            </div>

            <!-- Mô tả nguyên nhân -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Mô tả chi tiết tình trạng sự cố <span class="text-danger">*</span>
              </label>
              <textarea
                v-model="form.moTa"
                class="form-control rounded-3"
                rows="3"
                required
                placeholder="Ví dụ: Điều khiển tivi bị rơi vỡ màn hình LCD, hoặc vòi sen tắm bị rò rỉ nước do lão hóa zoăng..."
                :disabled="loading"
              ></textarea>
            </div>

            <!-- Thông báo lỗi -->
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
                class="btn btn-danger text-white rounded-3 px-4 fw-semibold d-inline-flex align-items-center gap-2"
                :disabled="loading || !form.maTaiSan || !form.moTa"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-check2"></i>
                <span>Xác nhận ghi nhận sự cố</span>
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
  name: 'ModalBaoHongTaiSan',
  props: {
    show: { type: Boolean, default: false },
    taiSanId: { type: [Number, String], default: null },
    danhSachTaiSan: { type: Array, default: () => [] }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errorMsg = ref('');
    const form = ref({
      maTaiSan: '',
      loaiPhatSinh: 'Hư hỏng',
      soLuong: 1,
      soTienBoiThuong: 0,
      moTa: '',
      maNhanVien: 1
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        errorMsg.value = '';
        form.value.maTaiSan = props.taiSanId || (props.danhSachTaiSan[0]?.id || '');
        form.value.loaiPhatSinh = 'Hư hỏng';
        form.value.soLuong = 1;
        form.value.soTienBoiThuong = 0;
        form.value.moTa = '';
      }
    });

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanBaoHong = async () => {
      if (!form.value.maTaiSan) {
        errorMsg.value = 'Vui lòng chọn tài sản.';
        return;
      }
      if (!form.value.moTa.trim()) {
        errorMsg.value = 'Vui lòng nhập mô tả chi tiết sự cố.';
        return;
      }

      loading.value = true;
      errorMsg.value = '';

      try {
        const payload = {
          maTaiSan: Number(form.value.maTaiSan),
          loaiPhatSinh: form.value.loaiPhatSinh,
          soLuong: Number(form.value.soLuong) || 1,
          soTienBoiThuong: Number(form.value.soTienBoiThuong) || 0,
          moTa: form.value.moTa.trim(),
          maNhanVien: 1
        };

        const res = await minibarTaiSanService.baoHongTaiSan(payload);
        emit('success', res.message || 'Ghi nhận sự cố tài sản thành công!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Lỗi khi ghi nhận sự cố.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      errorMsg,
      dongModal,
      xacNhanBaoHong
    };
  }
};
</script>
