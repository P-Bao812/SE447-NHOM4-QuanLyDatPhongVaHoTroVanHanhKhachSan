<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-check fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Chuẩn bị & Kiểm tra Minibar trước Check-in</h5>
              <div class="small opacity-75" v-if="phong">
                Phòng {{ phong.tenPhong }} &middot; Tầng {{ phong.tang }} &middot; {{ phong.loaiPhong }}
              </div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <div v-if="fetching" class="text-center py-5">
            <div class="spinner-border text-teal" role="status"></div>
            <div class="text-muted small mt-2">Đang tải dữ liệu minibar của phòng...</div>
          </div>

          <div v-else>
            <!-- Thẻ hướng dẫn nhanh -->
            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-info-circle-fill text-info flex-shrink-0 fs-6"></i>
              <div>
                Kiểm tra sản phẩm trong minibar phòng để đảm bảo đạt <strong>định mức tiêu chuẩn</strong> đón khách mới. Nếu thiếu, chọn số lượng xuất kho tổng để bổ sung.
              </div>
            </div>

            <!-- Bảng đối soát từng sản phẩm -->
            <div class="table-responsive border rounded-3 mb-3">
              <table class="table table-hover align-middle mb-0 custom-modal-table">
                <thead class="table-light">
                  <tr class="small text-secondary">
                    <th class="ps-3 py-2">Sản phẩm</th>
                    <th class="text-center py-2">Loại</th>
                    <th class="text-center py-2">Tiêu chuẩn</th>
                    <th class="text-center py-2" style="width: 140px;">Hiện có trong phòng</th>
                    <th class="text-center py-2" style="width: 130px;">Xuất kho bổ sung</th>
                    <th class="text-center py-2">Tồn kho tổng</th>
                    <th class="text-end pe-3 py-2">Tổng sau chuẩn bị</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in danhSachMinibar" :key="item.maSanPham">
                    <td class="ps-3 py-2.5">
                      <div class="fw-semibold text-dark">{{ item.tenSanPham }}</div>
                      <div class="text-muted extra-small">
                        {{ item.maCode }} &middot; {{ item.donGiaDinhDang }} / {{ item.donViTinh }}
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge" :class="item.loaiSanPham === 'Mien_Phi' ? 'bg-success' : 'bg-primary'">
                        {{ item.loaiSanPham === 'Mien_Phi' ? 'Miễn phí' : 'Tính phí' }}
                      </span>
                    </td>
                    <td class="text-center fw-bold text-secondary">
                      {{ item.soLuongTieuChuan }} {{ item.donViTinh }}
                    </td>
                    <td class="text-center">
                      <input
                        v-model.number="item.soLuongThucTe"
                        type="number"
                        min="0"
                        class="form-control form-control-sm text-center mx-auto"
                        style="width: 80px;"
                        @input="tinhGoiYBoSung(item)"
                      />
                    </td>
                    <td class="text-center">
                      <input
                        v-model.number="item.soLuongBoSung"
                        type="number"
                        min="0"
                        class="form-control form-control-sm text-center mx-auto"
                        :class="{ 'border-danger text-danger': item.soLuongBoSung > item.tonKhoTong }"
                        style="width: 80px;"
                      />
                    </td>
                    <td class="text-center small">
                      <span :class="item.tonKhoTong < 5 ? 'text-danger fw-bold' : 'text-muted'">
                        {{ item.tonKhoTong }} {{ item.donViTinh }}
                      </span>
                    </td>
                    <td class="text-end pe-3 fw-bold text-teal">
                      {{ (item.soLuongThucTe || 0) + (item.soLuongBoSung || 0) }} {{ item.donViTinh }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Ghi chú của nhân viên buồng phòng -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-secondary">Ghi chú chuẩn bị phòng</label>
              <textarea
                v-model="ghiChu"
                class="form-control rounded-3"
                rows="2"
                placeholder="Xác nhận đã lau chùi tủ lạnh minibar sạch sẽ, đồ uống ướp lạnh sẵn sàng đón khách..."
              ></textarea>
            </div>

            <!-- Cảnh báo lỗi -->
            <div v-if="errorMsg" class="alert alert-danger py-2 small mb-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMsg }}
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div class="small text-muted">
                <i class="bi bi-person-badge me-1"></i> Nhân viên: <strong>Trần Hoa (Buồng phòng)</strong>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-light rounded-3 px-3" @click="dongModal" :disabled="loading">
                  Hủy bỏ
                </button>
                <button
                  type="button"
                  class="btn btn-teal-primary text-white rounded-3 px-4 fw-semibold d-inline-flex align-items-center gap-2"
                  @click="xacNhanChuanBi"
                  :disabled="loading || coLoiTonKho"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-check-circle-fill"></i>
                  <span>Xác nhận sẵn sàng đón khách</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue';
import { minibarTaiSanService } from './minibarTaiSanService';

export default {
  name: 'ModalChuanBiCheckIn',
  props: {
    show: { type: Boolean, default: false },
    phongId: { type: [Number, String], default: null }
  },
  emits: ['close', 'success'],
  setup(props, { emit }) {
    const fetching = ref(false);
    const loading = ref(false);
    const errorMsg = ref('');
    const phong = ref(null);
    const stay = ref(null);
    const danhSachMinibar = ref([]);
    const ghiChu = ref('');

    const loadData = async () => {
      if (!props.phongId) return;
      fetching.value = true;
      errorMsg.value = '';

      try {
        const res = await minibarTaiSanService.getChiTietMinibarPhong(props.phongId);
        phong.value = res.phong;
        stay.value = res.stay;
        danhSachMinibar.value = (res.sanPham || []).map(p => {
          const soLuongThucTe = p.soLuongThucTe !== undefined ? p.soLuongThucTe : p.soLuongTieuChuan;
          const thieu = Math.max(0, p.soLuongTieuChuan - soLuongThucTe);
          return {
            ...p,
            soLuongThucTe: soLuongThucTe,
            soLuongBoSung: thieu
          };
        });
        ghiChu.value = `Minibar phòng ${phong.value.tenPhong} đã được làm sạch và chuẩn bị đầy đủ tiêu chuẩn đón khách.`;
      } catch (err) {
        errorMsg.value = 'Không thể tải thông tin minibar phòng: ' + (err.message || 'Lỗi kết nối');
      } finally {
        fetching.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal && props.phongId) {
        loadData();
      }
    });

    const tinhGoiYBoSung = (item) => {
      const thucTe = Number(item.soLuongThucTe) || 0;
      const thieu = Math.max(0, item.soLuongTieuChuan - thucTe);
      item.soLuongBoSung = thieu;
    };

    const coLoiTonKho = computed(() => {
      return danhSachMinibar.value.some(item => (item.soLuongBoSung || 0) > item.tonKhoTong);
    });

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanChuanBi = async () => {
      errorMsg.value = '';

      // Kiểm tra kho
      for (const item of danhSachMinibar.value) {
        if ((item.soLuongBoSung || 0) > item.tonKhoTong) {
          errorMsg.value = `Sản phẩm '${item.tenSanPham}' trong kho tổng chỉ còn ${item.tonKhoTong}, không đủ bổ sung ${item.soLuongBoSung}!`;
          return;
        }
      }

      loading.value = true;

      try {
        const payload = {
          maPhong: Number(props.phongId),
          maLuuTru: stay.value ? stay.value.maLuuTru : null,
          danhSach: danhSachMinibar.value.map(item => ({
            maSanPham: item.maSanPham,
            soLuongTieuChuan: Number(item.soLuongTieuChuan),
            soLuongThucTe: Number(item.soLuongThucTe) || 0,
            soLuongBoSung: Number(item.soLuongBoSung) || 0
          })),
          maNhanVien: 1,
          ghiChu: ghiChu.value
        };

        const res = await minibarTaiSanService.chuanBiCheckIn(payload);
        emit('success', res.message || 'Xác nhận minibar sẵn sàng đón khách!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Lỗi khi chuẩn bị check-in.';
      } finally {
        loading.value = false;
      }
    };

    return {
      fetching,
      loading,
      errorMsg,
      phong,
      stay,
      danhSachMinibar,
      ghiChu,
      coLoiTonKho,
      tinhGoiYBoSung,
      dongModal,
      xacNhanChuanBi
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
.extra-small {
  font-size: 0.75rem;
}
</style>
