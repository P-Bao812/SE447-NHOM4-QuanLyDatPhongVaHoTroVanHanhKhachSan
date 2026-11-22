<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-cart-plus fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Bổ sung Minibar vào phòng</h5>
              <div class="small opacity-75" v-if="phongTen">
                Phòng {{ phongTen }}
              </div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <form @submit.prevent="xacNhanBoSung">
            <!-- Chọn phòng nếu chưa chọn sẵn -->
            <div class="mb-3" v-if="!phongId">
              <label class="form-label small fw-bold text-secondary">
                Chọn phòng <span class="text-danger">*</span>
              </label>
              <select v-model="form.maPhong" class="form-select rounded-3 py-2" required :disabled="loading">
                <option value="" disabled>-- Chọn phòng cần bổ sung --</option>
                <option v-for="p in danhSachPhong" :key="p.id" :value="p.id">
                  Phòng {{ p.tenPhong }} (Tầng {{ p.tang }} - {{ p.tenLoaiPhong }})
                </option>
              </select>
            </div>

            <!-- Chọn sản phẩm minibar -->
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
                <option value="" disabled>-- Chọn sản phẩm cần bổ sung --</option>
                <option v-for="sp in danhSachSanPham" :key="sp.id" :value="sp.id">
                  [{{ sp.maSanPham }}] {{ sp.tenSanPham }} (Kho còn: {{ sp.tonHienTai }} {{ sp.donViTinh }})
                </option>
              </select>
            </div>

            <!-- Xem nhanh thông tin sản phẩm -->
            <div v-if="selectedProduct" class="p-3 mb-3 bg-light rounded-3 border">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold text-dark">{{ selectedProduct.tenSanPham }}</span>
                <span class="badge" :class="selectedProduct.loaiSanPham === 'Mien_Phi' ? 'bg-success' : 'bg-primary'">
                  {{ selectedProduct.loaiSanPhamText }}
                </span>
              </div>
              <div class="row g-2 small text-muted">
                <div class="col-6">
                  Đơn giá: <strong class="text-teal">{{ selectedProduct.donGiaDinhDang }}</strong>
                </div>
                <div class="col-6">
                  Kho tổng khả dụng:
                  <strong :class="selectedProduct.tonHienTai < 5 ? 'text-danger' : 'text-dark'">
                    {{ selectedProduct.tonHienTai }} {{ selectedProduct.donViTinh }}
                  </strong>
                </div>
              </div>
            </div>

            <!-- Số lượng bổ sung -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Số lượng bổ sung vào phòng <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <input
                  v-model.number="form.soLuongBoSung"
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
              <div
                v-if="selectedProduct && form.soLuongBoSung > selectedProduct.tonHienTai"
                class="form-text text-danger small"
              >
                <i class="bi bi-exclamation-circle me-1"></i> Số lượng bổ sung vượt quá tồn kho tổng khả dụng ({{ selectedProduct.tonHienTai }} {{ selectedProduct.donViTinh }}).
              </div>
            </div>

            <!-- Liên kết Yêu cầu dịch vụ (nếu có) -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">
                Mã yêu cầu dịch vụ liên kết (Tùy chọn)
              </label>
              <input
                v-model="form.maYeuCauDichVu"
                type="text"
                class="form-control rounded-3 py-2"
                placeholder="Ví dụ: YC-0811-06 hoặc để trống..."
                :disabled="loading"
              />
              <div class="form-text text-muted small">
                Nếu khách yêu cầu qua App/Lễ tân, nhập mã để hệ thống tự động hoàn thành yêu cầu dịch vụ.
              </div>
            </div>

            <!-- Ghi chú -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Ghi chú bổ sung</label>
              <textarea
                v-model="form.ghiChu"
                class="form-control rounded-3"
                rows="2"
                placeholder="Ví dụ: Bổ sung theo ca trực chiều hoặc theo yêu cầu thêm của khách..."
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
                :disabled="loading || !targetPhongId || !form.maSanPham || form.soLuongBoSung <= 0 || (selectedProduct && form.soLuongBoSung > selectedProduct.tonHienTai)"
              >
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-check2"></i>
                <span>Xác nhận bổ sung</span>
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
  name: 'ModalBoSungMinibar',
  props: {
    show: { type: Boolean, default: false },
    phongId: { type: [Number, String], default: null },
    phongTen: { type: String, default: '' },
    danhSachSanPham: { type: Array, default: () => [] },
    danhSachPhong: { type: Array, default: () => [] }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const loading = ref(false);
    const errorMsg = ref('');
    const form = ref({
      maPhong: '',
      maSanPham: '',
      soLuongBoSung: 2,
      maYeuCauDichVu: '',
      ghiChu: '',
      maNhanVien: 1
    });

    const targetPhongId = computed(() => {
      return props.phongId || form.value.maPhong;
    });

    const selectedProduct = computed(() => {
      if (!form.value.maSanPham) return null;
      return props.danhSachSanPham.find(p => p.id === Number(form.value.maSanPham));
    });

    watch(() => props.show, (newVal) => {
      if (newVal) {
        errorMsg.value = '';
        form.value.maPhong = props.phongId || (props.danhSachPhong[0]?.id || '');
        form.value.maSanPham = props.danhSachSanPham[0]?.id || '';
        form.value.soLuongBoSung = 2;
        form.value.maYeuCauDichVu = '';
        form.value.ghiChu = '';
      }
    });

    const onSelectProduct = () => {
      errorMsg.value = '';
    };

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanBoSung = async () => {
      if (!targetPhongId.value) {
        errorMsg.value = 'Vui lòng chọn phòng cần bổ sung.';
        return;
      }
      if (!form.value.maSanPham) {
        errorMsg.value = 'Vui lòng chọn sản phẩm.';
        return;
      }
      if (form.value.soLuongBoSung <= 0) {
        errorMsg.value = 'Số lượng bổ sung phải lớn hơn 0.';
        return;
      }
      if (selectedProduct.value && form.value.soLuongBoSung > selectedProduct.value.tonHienTai) {
        errorMsg.value = 'Số lượng bổ sung vượt quá tồn kho khả dụng.';
        return;
      }

      loading.value = true;
      errorMsg.value = '';

      try {
        const payload = {
          maPhong: Number(targetPhongId.value),
          maSanPham: Number(form.value.maSanPham),
          soLuongBoSung: Number(form.value.soLuongBoSung),
          maYeuCauDichVu: form.value.maYeuCauDichVu ? Number(form.value.maYeuCauDichVu.replace(/\D/g, '')) : null,
          ghiChu: form.value.ghiChu,
          maNhanVien: 1
        };

        const res = await minibarTaiSanService.boSungMinibar(payload);
        emit('success', res.message || 'Bổ sung minibar vào phòng thành công!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Lỗi khi bổ sung minibar.';
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      loading,
      errorMsg,
      targetPhongId,
      selectedProduct,
      dongModal,
      onSelectProduct,
      xacNhanBoSung
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
