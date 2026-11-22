<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-in-down fs-5"></i>
            <h5 class="modal-title fw-bold mb-0">Nhập kho tổng sản phẩm Minibar</h5>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <form @submit.prevent="xacNhanNhapKho">
            <!-- Chọn sản phẩm -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Sản phẩm minibar <span class="text-danger">*</span>
              </label>
              <select
                v-model="form.maSanPham"
                class="form-select rounded-3 py-2"
                required
                :disabled="loading"
                @change="onSelectProduct"
              >
                <option value="" disabled>-- Chọn sản phẩm cần nhập --</option>
                <option v-for="sp in danhSachSanPham" :key="sp.id" :value="sp.id">
                  [{{ sp.maSanPham }}] {{ sp.tenSanPham }} ({{ sp.donViTinh }}) - Hiện có: {{ sp.tonHienTai }}
                </option>
              </select>
            </div>

            <!-- Thông tin tồn kho sản phẩm đã chọn -->
            <div v-if="selectedProduct" class="p-3 mb-3 bg-light rounded-3 border">
              <div class="row g-2 small">
                <div class="col-6">
                  <span class="text-muted">Đơn giá bán:</span>
                  <span class="fw-semibold text-teal ms-1">{{ selectedProduct.donGiaDinhDang }}</span>
                </div>
                <div class="col-6">
                  <span class="text-muted">Phân loại:</span>
                  <span class="badge ms-1" :class="selectedProduct.loaiSanPham === 'Mien_Phi' ? 'bg-success' : 'bg-primary'">
                    {{ selectedProduct.loaiSanPhamText }}
                  </span>
                </div>
                <div class="col-6">
                  <span class="text-muted">Tồn kho hiện tại:</span>
                  <span class="fw-bold text-dark ms-1">{{ selectedProduct.tonHienTai }} {{ selectedProduct.donViTinh }}</span>
                </div>
                <div class="col-6">
                  <span class="text-muted">Ngưỡng tối thiểu:</span>
                  <span class="text-muted ms-1">{{ selectedProduct.nguongToiThieu }} {{ selectedProduct.donViTinh }}</span>
                </div>
              </div>
            </div>

            <!-- Số lượng nhập -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Số lượng nhập kho <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input
                  v-model.number="form.soLuongNhap"
                  type="number"
                  min="1"
                  step="1"
                  class="form-control rounded-start-3 py-2"
                  placeholder="Nhập số lượng..."
                  required
                  :disabled="loading"
                />
                <span class="input-group-text bg-white text-muted">
                  {{ selectedProduct ? selectedProduct.donViTinh : 'Đơn vị' }}
                </span>
              </div>
              <div v-if="selectedProduct && form.soLuongNhap > 0" class="form-text text-success small">
                Tồn kho sau khi nhập: <strong>{{ selectedProduct.tonHienTai + form.soLuongNhap }}</strong> {{ selectedProduct.donViTinh }}
              </div>
            </div>

            <!-- Ghi chú nhập kho -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Ghi chú / Nguồn nhập</label>
              <textarea
                v-model="form.ghiChu"
                class="form-control rounded-3"
                rows="2"
                placeholder="Ví dụ: Nhập bổ sung đợt đầu tháng từ Nhà cung cấp đồ uống..."
                :disabled="loading"
              ></textarea>
            </div>

            <!-- Thông báo lỗi nếu có -->
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
                :disabled="loading || !form.maSanPham || form.soLuongNhap <= 0"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-check2"></i>
                <span>Xác nhận nhập kho</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { minibarTaiSanService } from './minibarTaiSanService';

export default {
  name: 'ModalNhapKho',
  props: {
    show: { type: Boolean, default: false },
    danhSachSanPham: { type: Array, default: () => [] },
    sanPhamMacDinhId: { type: [Number, String], default: null }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errorMsg = ref('');
    const form = ref({
      maSanPham: '',
      soLuongNhap: 20,
      ghiChu: '',
      maNhanVien: 1
    });

    const selectedProduct = computed(() => {
      if (!form.value.maSanPham) return null;
      return props.danhSachSanPham.find(p => p.id === Number(form.value.maSanPham));
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        errorMsg.value = '';
        if (props.sanPhamMacDinhId) {
          form.value.maSanPham = Number(props.sanPhamMacDinhId);
        } else if (props.danhSachSanPham.length > 0) {
          form.value.maSanPham = props.danhSachSanPham[0].id;
        }
        form.value.soLuongNhap = 20;
        form.value.ghiChu = '';
      }
    });

    const onSelectProduct = () => {
      errorMsg.value = '';
    };

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanNhapKho = async () => {
      if (!form.value.maSanPham) {
        errorMsg.value = 'Vui lòng chọn sản phẩm.';
        return;
      }
      if (form.value.soLuongNhap <= 0) {
        errorMsg.value = 'Số lượng nhập phải lớn hơn 0.';
        return;
      }

      loading.value = true;
      errorMsg.value = '';

      try {
        const res = await minibarTaiSanService.nhapKhoTong({
          maSanPham: Number(form.value.maSanPham),
          soLuongNhap: Number(form.value.soLuongNhap),
          ghiChu: form.value.ghiChu,
          maNhanVien: 1
        });

        emit('success', res.message || 'Nhập kho thành công!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Có lỗi xảy ra khi nhập kho.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      errorMsg,
      selectedProduct,
      dongModal,
      onSelectProduct,
      xacNhanNhapKho
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
.text-teal {
  color: #1e5e49 !important;
}
</style>
