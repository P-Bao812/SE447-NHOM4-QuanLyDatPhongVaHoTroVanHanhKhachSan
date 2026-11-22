<template>
  <div
    v-if="show"
    class="modal fade show d-block"
    tabindex="-1"
    style="background-color: rgba(15, 23, 42, 0.55); z-index: 1060;"
  >
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
        <!-- MODAL HEADER -->
        <div class="modal-header border-bottom p-3.5 bg-light d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2.5">
            <div class="header-icon-box d-flex align-items-center justify-content-center">
              <i class="bi bi-tag-fill"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark fs-6 mb-0">
                {{ isEditing ? 'Chỉnh sửa khuyến mãi loại phòng' : 'Thiết lập khuyến mãi loại phòng' }}
              </h5>
              <div class="text-muted extra-small">
                Áp dụng cho hạng phòng: <strong>{{ room?.tenLoai }}</strong> · Giá niêm yết: <strong>{{ room?.giaDinhDang }}/đêm</strong>
              </div>
            </div>
          </div>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>

        <!-- MODAL BODY -->
        <div class="modal-body p-4">
          <!-- BẢNG TÍNH GIÁ TRỰC TIẾP (LIVE PREVIEW) -->
          <div class="p-3.5 rounded-3 mb-4 border bg-teal-subtle-box">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-teal-light">
              <span class="fw-bold small text-dark d-flex align-items-center gap-1.5">
                <i class="bi bi-calculator text-teal"></i>
                Xem trước mức giá hiển thị trên Card
              </span>
              <span
                class="badge rounded-pill px-2.5 py-1 extra-small fw-semibold"
                :class="form.trangThai === 'Đang chạy' ? 'bg-success text-white' : 'bg-secondary text-white'"
              >
                ● {{ form.trangThai }}
              </span>
            </div>

            <div class="row g-2 align-items-center font-monospace">
              <div class="col-4">
                <div class="text-muted extra-small">Giá gốc niêm yết:</div>
                <div class="fw-bold text-dark fs-6" :class="{ 'text-decoration-line-through text-muted': form.trangThai === 'Đang chạy' && calcDiscount > 0 }">
                  {{ formatMoney(room?.giaTieuChuan) }} đ
                </div>
              </div>
              <div class="col-4 text-center">
                <div class="text-muted extra-small">Mức giảm trừ:</div>
                <div class="fw-bold text-danger fs-6">
                  <span v-if="form.trangThai === 'Đang chạy' && calcDiscount > 0">
                    -{{ form.loaiGiam === 'Phan_Tram' ? `${form.giaTriGiam}%` : `${formatMoney(form.giaTriGiam)} đ` }}
                    <span class="extra-small text-muted font-sans d-block">(-{{ formatMoney(calcDiscount) }} đ)</span>
                  </span>
                  <span v-else class="text-muted font-sans extra-small">Không áp dụng</span>
                </div>
              </div>
              <div class="col-4 text-end">
                <div class="text-muted extra-small">Giá bán thực tế/đêm:</div>
                <div class="fw-bold text-teal fs-5 font-sans">
                  {{ form.trangThai === 'Đang chạy' && calcDiscount > 0 ? `${formatMoney(calcFinalPrice)} đ` : `${formatMoney(room?.giaTieuChuan)} đ` }}
                </div>
              </div>
            </div>

            <div v-if="form.trangThai !== 'Đang chạy'" class="alert alert-warning py-1.5 px-2.5 mt-2.5 mb-0 extra-small">
              <i class="bi bi-exclamation-triangle me-1"></i>
              Chương trình đang ở trạng thái <strong>{{ form.trangThai }}</strong>. Card loại phòng sẽ hiển thị giá gốc và dòng <em>"Chưa áp dụng khuyến mãi"</em> cho đến khi kích hoạt.
            </div>
          </div>

          <!-- FORM NHẬP THÔNG TIN KHUYẾN MÃI -->
          <form @submit.prevent="onSave">
            <div class="row g-3">
              <!-- Hàng 1: Mã ưu đãi & Tên chương trình -->
              <div class="col-md-5">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Mã khuyến mãi *</label>
                <input
                  v-model="form.maKhuyenMai"
                  type="text"
                  class="form-control form-control-sm text-uppercase font-monospace fw-bold"
                  placeholder="VD: DELUXE10"
                  required
                />
              </div>

              <div class="col-md-7">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Tên chương trình *</label>
                <input
                  v-model="form.tenKhuyenMai"
                  type="text"
                  class="form-control form-control-sm"
                  placeholder="VD: Ưu đãi đặc quyền Deluxe"
                  required
                />
              </div>

              <!-- Hàng 2: Hình thức giảm & Mức giảm -->
              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Hình thức giảm giá *</label>
                <select v-model="form.loaiGiam" class="form-select form-select-sm" required>
                  <option value="Phan_Tram">Phần trăm theo giá phòng (%)</option>
                  <option value="So_Tien">Số tiền cố định mỗi đêm (đ)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">
                  {{ form.loaiGiam === 'Phan_Tram' ? 'Tỷ lệ giảm giá (%) *' : 'Số tiền giảm mỗi đêm (đ) *' }}
                </label>
                <div class="input-group input-group-sm">
                  <input
                    v-model.number="form.giaTriGiam"
                    type="number"
                    min="1"
                    :max="form.loaiGiam === 'Phan_Tram' ? 100 : (room?.giaTieuChuan || 10000000)"
                    class="form-control fw-bold"
                    placeholder="VD: 10"
                    required
                  />
                  <span class="input-group-text bg-light text-muted">
                    {{ form.loaiGiam === 'Phan_Tram' ? '%' : 'đ/đêm' }}
                  </span>
                </div>
              </div>

              <!-- Hàng 3: Giảm tối đa (nếu % ) & Ngày áp dụng -->
              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">
                  Giảm tối đa (đ)
                  <span class="text-muted fw-normal">(để trống nếu không giới hạn)</span>
                </label>
                <input
                  v-model.number="form.giamToiDa"
                  type="number"
                  min="0"
                  step="10000"
                  class="form-control form-control-sm"
                  placeholder="VD: 300000"
                />
              </div>

              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Trạng thái hoạt động *</label>
                <select v-model="form.trangThai" class="form-select form-select-sm" required>
                  <option value="Đang chạy">Áp Dụng</option>
                  <option value="Tạm dừng">Tạm Dừng</option>
                </select>
              </div>

              <!-- Hàng 4: Ngày bắt đầu & Ngày kết thúc -->
              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Thời gian bắt đầu *</label>
                <input
                  v-model="form.ngayBatDau"
                  type="datetime-local"
                  class="form-control form-control-sm"
                  required
                />
              </div>

              <div class="col-md-6">
                <label class="form-label extra-small text-muted fw-semibold mb-1">Thời gian kết thúc *</label>
                <input
                  v-model="form.ngayKetThuc"
                  type="datetime-local"
                  class="form-control form-control-sm"
                  required
                />
              </div>
            </div>

            <!-- NỘI DUNG LƯU Ý PHẠM VI ÁP DỤNG -->
            <div class="mt-3 p-2.5 rounded-3 bg-light border extra-small text-muted">
              <i class="bi bi-info-circle me-1 text-teal"></i>
              <strong>Đồng bộ với Quản lý khuyến mãi:</strong> Thiết lập này lưu trực tiếp vào chương trình khuyến mãi của hệ thống và đồng bộ tức thời với tính toán hóa đơn khi khách hàng đặt phòng.
            </div>

            <!-- MODAL ACTIONS -->
            <div class="d-flex align-items-center justify-content-between pt-3 mt-3 border-top">
              <button
                type="button"
                class="btn btn-outline-secondary btn-sm px-3.5"
                @click="$emit('close')"
              >
                Hủy bỏ
              </button>

              <button
                type="submit"
                class="btn btn-teal-primary btn-sm px-4 fw-semibold text-white shadow-xs"
                :disabled="saving"
              >
                <span v-if="saving" class="spinner-border spinner-border-sm me-1.5" role="status"></span>
                <i v-else class="bi bi-check2-circle me-1.5"></i>
                {{ isEditing ? 'Lưu thay đổi khuyến mãi' : 'Áp dụng khuyến mãi' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalKhuyenMaiLoaiPhong',
  props: {
    show: {
      type: Boolean,
      default: false,
    },
    room: {
      type: Object,
      default: () => null,
    },
    saving: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['close', 'save'],
  data() {
    return {
      isEditing: false,
      form: {
        promoId: null,
        maLoaiPhong: null,
        maKhuyenMai: '',
        tenKhuyenMai: '',
        loaiGiam: 'Phan_Tram',
        giaTriGiam: 10,
        giamToiDa: 0,
        giaTriDatToiThieu: 0,
        ngayBatDau: '',
        ngayKetThuc: '',
        trangThai: 'Đang chạy',
      },
    };
  },
  computed: {
    basePrice() {
      return Number(this.room?.giaTieuChuan || 0);
    },
    calcDiscount() {
      if (!this.basePrice) return 0;
      let discount = 0;
      if (this.form.loaiGiam === 'Phan_Tram') {
        const calculated = (this.basePrice * Number(this.form.giaTriGiam || 0)) / 100;
        const max = Number(this.form.giamToiDa || 0);
        discount = max > 0 ? Math.min(calculated, max) : calculated;
      } else {
        discount = Math.min(Number(this.form.giaTriGiam || 0), this.basePrice);
      }
      return discount;
    },
    calcFinalPrice() {
      return Math.max(0, this.basePrice - this.calcDiscount);
    },
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.khoiTaoDuLieu();
      }
    },
    room() {
      if (this.show) {
        this.khoiTaoDuLieu();
      }
    },
  },
  methods: {
    formatMoney(val) {
      if (val === null || val === undefined) return '0';
      return Number(val).toLocaleString('vi-VN');
    },

    formatDateInput(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return '';
      const y = d.getFullYear();
      const m = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      const h = String(d.getHours()).padStart(2, '0');
      const min = String(d.getMinutes()).padStart(2, '0');
      return `${y}-${m}-${day}T${h}:${min}`;
    },

    khoiTaoDuLieu() {
      if (!this.room) return;

      const km = this.room.khuyenMai;
      const targetPromo = km?.chuongTrinh || (km?.coKhuyenMai ? km : null);

      if (targetPromo) {
        this.isEditing = true;
        this.form = {
          promoId: targetPromo.id || null,
          maLoaiPhong: this.room.id,
          maKhuyenMai: targetPromo.maKhuyenMai || '',
          tenKhuyenMai: targetPromo.tenKhuyenMai || `Ưu đãi ${this.room.tenLoai}`,
          loaiGiam: targetPromo.loaiGiam || 'Phan_Tram',
          giaTriGiam: Number(targetPromo.giaTriGiam || 10),
          giamToiDa: Number(targetPromo.giamToiDa || 0),
          giaTriDatToiThieu: Number(targetPromo.giaTriDatToiThieu || 0),
          ngayBatDau: this.formatDateInput(targetPromo.ngayBatDau),
          ngayKetThuc: this.formatDateInput(targetPromo.ngayKetThuc),
          trangThai: targetPromo.trangThai === 'Tạm dừng' ? 'Tạm dừng' : 'Đang chạy',
        };
      } else {
        this.isEditing = false;
        const now = new Date();
        const nextMonth = new Date(now);
        nextMonth.setDate(now.getDate() + 30);

        const cleanName = this.room.tenLoai
          ? this.room.tenLoai.normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9]/g, '').toUpperCase()
          : 'ROOM';

        this.form = {
          promoId: null,
          maLoaiPhong: this.room.id,
          maKhuyenMai: `KM_${cleanName}`,
          tenKhuyenMai: `Ưu đãi ${this.room.tenLoai}`,
          loaiGiam: 'Phan_Tram',
          giaTriGiam: 10,
          giamToiDa: 0,
          giaTriDatToiThieu: 0,
          ngayBatDau: this.formatDateInput(now),
          ngayKetThuc: this.formatDateInput(nextMonth),
          trangThai: 'Đang chạy',
        };
      }
    },

    onSave() {
      this.$emit('save', { ...this.form });
    },
  },
};
</script>

<style scoped>
.header-icon-box {
  width: 36px;
  height: 36px;
  background-color: #e6f4f1;
  color: #0d7065;
  border-radius: 10px;
  font-size: 1.1rem;
}

.bg-teal-subtle-box {
  background-color: #f1f8f6;
  border-color: #cde7e1 !important;
}

.border-teal-light {
  border-color: #d8eee8 !important;
}

.text-teal {
  color: #0d7065 !important;
}

.btn-teal-primary {
  background-color: #0d7065;
  border-color: #0d7065;
  transition: all 0.2s ease;
}

.btn-teal-primary:hover,
.btn-teal-primary:focus {
  background-color: #095048;
  border-color: #095048;
  color: #ffffff;
}

.extra-small {
  font-size: 0.75rem;
}

.shadow-xs {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>
