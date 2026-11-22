<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clipboard2-check-fill fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Kiểm kê Minibar khi khách Check-out (Trả phòng)</h5>
              <div class="small opacity-75" v-if="phong">
                Phòng {{ phong.tenPhong }} &middot; Tầng {{ phong.tang }} &middot; {{ phong.loaiPhong }}
                <span v-if="stay" class="ms-2">| Khách: <strong>{{ stay.tenKhach }}</strong> ({{ stay.soDienThoai }})</span>
              </div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal" :disabled="loading"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4">
          <div v-if="fetching" class="text-center py-5">
            <div class="spinner-border text-teal" role="status"></div>
            <div class="text-muted small mt-2">Đang tải số liệu kiểm kê phòng...</div>
          </div>

          <div v-else>
            <!-- Nguyên tắc đối soát nghiệp vụ -->
            <div class="alert alert-warning py-2.5 px-3 small rounded-3 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-exclamation-triangle-fill text-warning flex-shrink-0 fs-5"></i>
              <div>
                <strong>Quy định kiểm kê:</strong> Số lượng dự kiến = Ban đầu + Bổ sung.
                <span class="text-danger fw-bold">Không tự ý quy mọi chênh lệch thành tiêu thụ.</span>
                Chỉ tính phí khi nhân viên xác nhận lý do là <em>"Khách đã sử dụng"</em> đối với sản phẩm tính phí. Sản phẩm miễn phí không tính tiền (0 đ).
              </div>
            </div>

            <!-- Bảng kiểm kê chi tiết -->
            <div class="table-responsive border rounded-3 mb-3">
              <table class="table table-hover align-middle mb-0 custom-modal-table">
                <thead class="table-light">
                  <tr class="small text-secondary">
                    <th class="ps-3 py-2">Sản phẩm</th>
                    <th class="text-center py-2">Loại</th>
                    <th class="text-center py-2">Ban đầu</th>
                    <th class="text-center py-2">Bổ sung</th>
                    <th class="text-center py-2 bg-light-subtle">Dự kiến</th>
                    <th class="text-center py-2" style="width: 120px;">Thực tế còn lại</th>
                    <th class="text-center py-2">Chênh lệch</th>
                    <th class="py-2" style="width: 200px;">Lý do chênh lệch</th>
                    <th class="text-center py-2" style="width: 110px;">Tính tiền KH</th>
                    <th class="text-end pe-3 py-2">Thành tiền</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in danhSachKiemKe" :key="item.maSanPham">
                    <!-- Tên SP & đơn giá -->
                    <td class="ps-3 py-2.5">
                      <div class="fw-semibold text-dark">{{ item.tenSanPham }}</div>
                      <div class="text-muted extra-small">
                        {{ item.maCode }} &middot; {{ item.donGiaDinhDang }} / {{ item.donViTinh }}
                      </div>
                    </td>

                    <!-- Loại SP -->
                    <td class="text-center">
                      <span class="badge" :class="item.loaiSanPham === 'Mien_Phi' ? 'bg-success' : 'bg-primary'">
                        {{ item.loaiSanPham === 'Mien_Phi' ? 'Miễn phí' : 'Tính phí' }}
                      </span>
                    </td>

                    <!-- Ban đầu -->
                    <td class="text-center text-muted small">{{ item.soLuongBanDau }}</td>

                    <!-- Bổ sung -->
                    <td class="text-center text-muted small">+{{ item.soLuongBoSung }}</td>

                    <!-- Dự kiến -->
                    <td class="text-center fw-bold text-dark bg-light-subtle">
                      {{ item.soLuongDuKien }}
                    </td>

                    <!-- Thực tế còn lại (input) -->
                    <td class="text-center">
                      <input
                        v-model.number="item.soLuongThucTe"
                        type="number"
                        min="0"
                        :max="item.soLuongDuKien"
                        class="form-control form-control-sm text-center mx-auto"
                        style="width: 75px;"
                        @input="capNhatChenhLech(item)"
                      />
                    </td>

                    <!-- Chênh lệch -->
                    <td class="text-center">
                      <span
                        v-if="item.soLuongChenhLech > 0"
                        class="badge bg-danger-subtle text-danger border border-danger-subtle px-2"
                      >
                        -{{ item.soLuongChenhLech }} {{ item.donViTinh }}
                      </span>
                      <span v-else class="text-success small">
                        <i class="bi bi-check2"></i> Đủ
                      </span>
                    </td>

                    <!-- Lý do chênh lệch -->
                    <td>
                      <select
                        v-if="item.soLuongChenhLech > 0"
                        v-model="item.lyDoChenhLech"
                        class="form-select form-select-sm"
                        @change="capNhatThanhTien(item)"
                      >
                        <option value="Khách đã sử dụng">Khách đã sử dụng (Tính tiền)</option>
                        <option value="Thiếu chưa xác định">Thiếu chưa xác định (0 đ)</option>
                        <option value="Sản phẩm hư hỏng">Sản phẩm hư hỏng (0 đ)</option>
                        <option value="Sai lệch kiểm kê">Sai lệch kiểm kê (0 đ)</option>
                        <option value="Nguyên nhân khác">Nguyên nhân khác (0 đ)</option>
                      </select>
                      <span v-else class="text-muted extra-small">Không có chênh lệch</span>
                    </td>

                    <!-- Số lượng tính tiền cho khách -->
                    <td class="text-center">
                      <input
                        v-if="item.soLuongChenhLech > 0 && item.lyDoChenhLech === 'Khách đã sử dụng' && item.loaiSanPham !== 'Mien_Phi'"
                        v-model.number="item.soLuongTieuThu"
                        type="number"
                        min="0"
                        :max="item.soLuongChenhLech"
                        class="form-control form-control-sm text-center mx-auto"
                        style="width: 65px;"
                        @input="tinhTongTien"
                      />
                      <span v-else class="text-muted small">0</span>
                    </td>

                    <!-- Thành tiền -->
                    <td class="text-end pe-3 fw-bold">
                      <span v-if="item.thanhTien > 0" class="text-teal">
                        {{ formatCurrency(item.thanhTien) }}
                      </span>
                      <span v-else class="text-muted small">0 đ</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Tổng kết tiền phát sinh & Ghi chú -->
            <div class="row g-3 align-items-center mb-3">
              <div class="col-md-7">
                <label class="form-label small fw-bold text-secondary mb-1">Ghi chú đối soát kiểm kê</label>
                <input
                  v-model="ghiChu"
                  type="text"
                  class="form-control rounded-3"
                  placeholder="Ghi nhận tình trạng minibar lúc khách bàn giao phòng..."
                />
              </div>

              <div class="col-md-5">
                <div class="p-3 bg-light rounded-3 border text-end">
                  <div class="small text-muted mb-1">Tổng tiền phát sinh minibar tính cho khách:</div>
                  <div class="h4 fw-bold text-teal mb-0">
                    {{ formatCurrency(tongTienPhatSinh) }}
                  </div>
                  <div class="extra-small text-muted mt-1">
                    Chỉ tính các sản phẩm trả phí được xác nhận khách đã dùng
                  </div>
                </div>
              </div>
            </div>

            <!-- Cảnh báo lỗi -->
            <div v-if="errorMsg" class="alert alert-danger py-2 small mb-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ errorMsg }}
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <div class="small text-muted">
                <i class="bi bi-person-badge me-1"></i> Nhân viên kiểm kê: <strong>Mai Linh (Lễ tân / Thu ngân)</strong>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-light rounded-3 px-3" @click="dongModal" :disabled="loading">
                  Hủy bỏ
                </button>
                <button
                  type="button"
                  class="btn btn-teal-primary text-white rounded-3 px-4 fw-semibold d-inline-flex align-items-center gap-2"
                  @click="xacNhanKiemKe"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-check-circle-fill"></i>
                  <span>Xác nhận kết quả kiểm kê Check-out</span>
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
  name: 'ModalKiemKeCheckOut',
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
    const danhSachKiemKe = ref([]);
    const ghiChu = ref('');

    const formatCurrency = (val) => {
      return Number(val || 0).toLocaleString('vi-VN') + ' đ';
    };

    const loadData = async () => {
      if (!props.phongId) return;
      fetching.value = true;
      errorMsg.value = '';

      try {
        const res = await minibarTaiSanService.getChiTietMinibarPhong(props.phongId);
        phong.value = res.phong;
        stay.value = res.stay;

        danhSachKiemKe.value = (res.sanPham || []).map(p => {
          const duKien = (p.soLuongBanDau || 0) + (p.soLuongBoSung || 0);
          const thucTe = p.soLuongThucTe !== undefined ? p.soLuongThucTe : duKien;
          const chenhLech = Math.max(0, duKien - thucTe);
          const lyDo = chenhLech > 0 ? (p.lyDoChenhLech || 'Khách đã sử dụng') : null;
          const isTieuThu = lyDo === 'Khách đã sử dụng' && p.loaiSanPham !== 'Mien_Phi';
          const soLuongTieuThu = isTieuThu ? chenhLech : 0;
          const thanhTien = isTieuThu ? soLuongTieuThu * p.donGia : 0;

          return {
            ...p,
            soLuongDuKien: duKien,
            soLuongThucTe: thucTe,
            soLuongChenhLech: chenhLech,
            lyDoChenhLech: lyDo,
            soLuongTieuThu: soLuongTieuThu,
            thanhTien: thanhTien
          };
        });

        ghiChu.value = `Kiểm kê minibar khi trả phòng ${phong.value.tenPhong}`;
      } catch (err) {
        errorMsg.value = 'Không thể tải dữ liệu phòng: ' + (err.message || 'Lỗi kết nối');
      } finally {
        fetching.value = false;
      }
    };

    watch(() => props.show, (newVal) => {
      if (newVal && props.phongId) {
        loadData();
      }
    });

    const capNhatChenhLech = (item) => {
      const thucTe = Math.max(0, Number(item.soLuongThucTe) || 0);
      item.soLuongThucTe = thucTe;
      item.soLuongChenhLech = Math.max(0, item.soLuongDuKien - thucTe);

      if (item.soLuongChenhLech > 0 && !item.lyDoChenhLech) {
        item.lyDoChenhLech = 'Khách đã sử dụng';
      } else if (item.soLuongChenhLech === 0) {
        item.lyDoChenhLech = null;
      }

      capNhatThanhTien(item);
    };

    const capNhatThanhTien = (item) => {
      if (item.soLuongChenhLech > 0 && item.lyDoChenhLech === 'Khách đã sử dụng') {
        item.soLuongTieuThu = item.soLuongChenhLech;
        item.thanhTien = item.loaiSanPham !== 'Mien_Phi' ? item.soLuongTieuThu * item.donGia : 0;
      } else {
        item.soLuongTieuThu = 0;
        item.thanhTien = 0;
      }
    };

    const tinhTongTien = () => {
      danhSachKiemKe.value.forEach(item => {
        if (item.soLuongChenhLech > 0 && item.lyDoChenhLech === 'Khách đã sử dụng' && item.loaiSanPham !== 'Mien_Phi') {
          item.thanhTien = (Number(item.soLuongTieuThu) || 0) * item.donGia;
        } else {
          item.thanhTien = 0;
        }
      });
    };

    const tongTienPhatSinh = computed(() => {
      return danhSachKiemKe.value.reduce((sum, item) => sum + (item.thanhTien || 0), 0);
    });

    const dongModal = () => {
      if (!loading.value) emit('close');
    };

    const xacNhanKiemKe = async () => {
      loading.value = true;
      errorMsg.value = '';

      try {
        const payload = {
          maPhong: Number(props.phongId),
          maLuuTru: stay.value ? stay.value.maLuuTru : 1,
          danhSach: danhSachKiemKe.value.map(item => ({
            maSanPham: item.maSanPham,
            soLuongThucTe: Number(item.soLuongThucTe) || 0,
            lyDoChenhLech: item.lyDoChenhLech,
            soLuongTieuThu: Number(item.soLuongTieuThu) || 0
          })),
          maNhanVien: 1,
          ghiChu: ghiChu.value
        };

        const res = await minibarTaiSanService.kiemKeCheckOut(payload);
        emit('success', res.message || 'Xác nhận kiểm kê trả phòng thành công!');
        emit('close');
      } catch (err) {
        errorMsg.value = err.response?.data?.message || err.message || 'Lỗi kiểm kê check-out.';
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
      danhSachKiemKe,
      ghiChu,
      tongTienPhatSinh,
      formatCurrency,
      capNhatChenhLech,
      capNhatThanhTien,
      tinhTongTien,
      dongModal,
      xacNhanKiemKe
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
