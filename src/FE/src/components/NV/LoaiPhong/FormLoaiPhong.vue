<template>
  <div class="form-card h-100">
    <!-- TIÊU ĐỀ & TRẠNG THÁI FORM -->
    <div class="d-flex justify-content-between align-items-start mb-3 pb-2 border-bottom">
      <div>
        <div class="form-heading-title">
          {{ isCreateMode ? 'Thêm loại phòng mới' : `Chỉnh sửa loại phòng · ${formData.tenLoai || roomType?.tenLoai}` }}
        </div>
        <div class="form-heading-sub">
          Áp dụng cho các đặt phòng mới sau thời điểm lưu.
        </div>
      </div>

      <div v-if="!isCreateMode && roomType">
        <span v-if="roomType.trangThai === 'Đang mở bán'" class="badge-active-pill">
          • Đang mở bán
        </span>
        <span v-else class="badge-locked-pill">
          • Ngừng kinh doanh
        </span>
      </div>
    </div>

    <!-- FORM NHẬP LIỆU -->
    <form @submit.prevent="handleSubmit">
      <div class="row g-3">
        <!-- 1. Tên loại phòng -->
        <div class="col-12 col-md-6">
          <label class="form-label-custom">
            Tên loại phòng <span class="text-danger">*</span>
          </label>
          <input
            type="text"
            class="form-control form-control-custom"
            v-model="formData.tenLoai"
            placeholder="VD: Deluxe, Superior..."
            required
          />
        </div>

        <!-- 2. Số phòng liên kết (Tạo phòng ban đầu khi thêm mới, hoặc hiển thị readonly khi sửa) -->
        <div v-if="isCreateMode" class="col-12 col-md-6">
          <label class="form-label-custom">
            Số phòng tạo ban đầu <span class="text-teal small">(để chuyển vào phòng hiện có)</span>
          </label>
          <div class="row g-2">
            <div class="col-7">
              <div class="input-group">
                <input
                  type="number"
                  class="form-control form-control-custom"
                  v-model.number="formData.soLuongPhongTao"
                  min="1"
                  max="20"
                  placeholder="VD: 2"
                  title="Số lượng phòng thực tế khởi tạo"
                />
                <span class="input-group-text bg-light text-muted small">phòng</span>
              </div>
            </div>
            <div class="col-5">
              <div class="input-group">
                <span class="input-group-text bg-light text-muted small">Tầng</span>
                <input
                  type="number"
                  class="form-control form-control-custom"
                  v-model.number="formData.tang"
                  min="1"
                  max="20"
                  placeholder="4"
                  title="Tầng bố trí các phòng"
                />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="col-12 col-md-6">
          <label class="form-label-custom">Số phòng liên kết</label>
          <input
            type="text"
            class="form-control form-control-custom"
            readonly
            :value="roomCountText"
          />
        </div>

        <!-- 3. Sức chứa tiêu chuẩn -->
        <div class="col-12 col-md-6">
          <label class="form-label-custom">
            Sức chứa tiêu chuẩn <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input
              type="number"
              class="form-control form-control-custom"
              v-model.number="formData.sucChuaToiDa"
              min="1"
              max="20"
              required
            />
            <span class="input-group-text bg-light text-muted small">người lớn</span>
          </div>
        </div>

        <!-- 4. Diện tích -->
        <div class="col-12 col-md-6">
          <label class="form-label-custom">Diện tích phòng</label>
          <div class="input-group">
            <input
              type="number"
              class="form-control form-control-custom"
              v-model.number="formData.dienTich"
              min="10"
              placeholder="VD: 34"
            />
            <span class="input-group-text bg-light text-muted small">m²</span>
          </div>
        </div>

        <!-- 5. Cấu hình giường -->
        <div class="col-12">
          <label class="form-label-custom">Cấu hình giường</label>
          <input
            type="text"
            class="form-control form-control-custom"
            v-model="formData.cauHinhGiuong"
            placeholder="VD: 1 giường đôi · 1,8 × 2 m"
          />
        </div>

        <!-- 6. Giá bán / đêm -->
        <div class="col-12">
          <label class="form-label-custom">
            Giá bán / đêm · Đã gồm thuế, phí <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <input
              type="number"
              class="form-control form-control-custom fw-bold text-dark"
              v-model.number="formData.giaTieuChuan"
              min="1"
              step="10000"
              placeholder="VD: 900000"
              required
            />
            <span class="input-group-text bg-light fw-bold text-teal">VNĐ / đêm</span>
          </div>
          <div class="text-muted small mt-1" style="font-size: 0.78rem;">
            Giá định dạng: <strong class="text-teal">{{ formattedPricePreview }}</strong>
          </div>
        </div>

        <!-- 7. Tiện nghi được công bố -->
        <div class="col-12">
          <label class="form-label-custom">Tiện nghi được công bố</label>
          <div class="amenities-grid p-3 bg-light rounded-3 border">
            <label
              v-for="amenity in availableAmenities"
              :key="amenity"
              class="amenity-checkbox-item"
            >
              <input
                type="checkbox"
                :value="amenity"
                v-model="formData.tienNghis"
              />
              <span>{{ amenity }}</span>
            </label>
          </div>
        </div>

        <!-- 8. Điều kiện bán -->
        <div class="col-12">
          <div class="form-section-title">Điều kiện bán</div>
          <label class="form-label-custom">Thời gian lưu trú tối thiểu</label>
          <input
            type="text"
            class="form-control form-control-custom"
            v-model="formData.dieuKien"
            placeholder="VD: 1 đêm · Không giới hạn tối đa"
          />
        </div>

        <!-- 9. Tiện ích tóm tắt nổi bật (hiển thị trên thẻ) -->
        <div class="col-12">
          <label class="form-label-custom">Mô tả tóm tắt nổi bật</label>
          <input
            type="text"
            class="form-control form-control-custom"
            v-model="formData.tienIchTomTat"
            placeholder="VD: Ban công riêng · Góc thư giãn"
          />
        </div>
      </div>

      <!-- CÁC NÚT HÀNH ĐỘNG -->
      <div class="d-flex flex-wrap gap-2 align-items-center mt-4 pt-3 border-top">
        <!-- Nút Lưu -->
        <button
          type="submit"
          class="btn btn-save-room shadow-sm"
          :disabled="loadingSave"
        >
          <span v-if="loadingSave" class="spinner-border spinner-border-sm me-1" role="status"></span>
          <i v-else class="bi bi-check2-circle"></i>
          <span>{{ isCreateMode ? 'Tạo loại phòng' : 'Lưu thay đổi' }}</span>
        </button>

        <!-- Nút Ngừng kinh doanh / Mở bán lại (chỉ khi sửa) -->
        <button
          v-if="!isCreateMode && roomType"
          type="button"
          class="btn btn-toggle-status"
          :disabled="loadingToggle"
          @click="$emit('toggle-status', roomType.id)"
        >
          <span v-if="loadingToggle" class="spinner-border spinner-border-sm me-1" role="status"></span>
          <i :class="roomType.trangThai === 'Đang mở bán' ? 'bi bi-slash-circle text-danger' : 'bi bi-arrow-repeat text-success'"></i>
          <span>{{ roomType.trangThai === 'Đang mở bán' ? 'Ngừng kinh doanh' : 'Mở bán lại' }}</span>
        </button>

        <!-- Nút Hủy chế độ tạo mới -->
        <button
          v-if="isCreateMode"
          type="button"
          class="btn btn-light border px-3"
          @click="$emit('cancel-create')"
        >
          Hủy
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: 'FormLoaiPhong',
  props: {
    roomType: {
      type: Object,
      default: null,
    },
    isCreateMode: {
      type: Boolean,
      default: false,
    },
    allAmenities: {
      type: Array,
      default: () => [],
    },
    loadingSave: {
      type: Boolean,
      default: false,
    },
    loadingToggle: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['save', 'toggle-status', 'cancel-create'],
  data() {
    return {
      formData: {
        tenLoai: '',
        giaTieuChuan: 750000,
        sucChuaToiDa: 2,
        dienTich: 26,
        cauHinhGiuong: '1 giường đôi · 1,8 × 2 m',
        tienIchTomTat: '',
        dieuKien: '1 đêm · Không giới hạn tối đa',
        tienNghis: [],
        trangThai: 'Đang mở bán',
        soLuongPhongTao: 2,
        tang: 4,
      },
    };
  },
  computed: {
    availableAmenities() {
      if (this.allAmenities && this.allAmenities.length > 0) {
        return this.allAmenities.map((a) => (typeof a === 'string' ? a : a.tenTienNghi));
      }
      return [
        'Wi-Fi & điều hòa',
        'Bữa sáng 2 khách',
        'Ban công riêng',
        'Minibar tính phí',
        'Két an toàn',
        'Bồn tắm',
        'Cửa sổ thoáng mát',
        'Máy sấy tóc & Đồ dùng',
      ];
    },
    roomCountText() {
      if (this.isCreateMode || !this.roomType) return 'Chưa có phòng liên kết';
      const count = this.roomType.soLuongPhong || 0;
      return `${count} phòng · ${count > 0 ? 'Xem danh sách' : 'Chưa tạo phòng'}`;
    },
    formattedPricePreview() {
      if (!this.formData.giaTieuChuan) return '0 đ';
      return new Intl.NumberFormat('vi-VN').format(this.formData.giaTieuChuan) + ' đ / đêm';
    },
  },
  watch: {
    roomType: {
      immediate: true,
      handler(val) {
        if (!this.isCreateMode && val) {
          this.formData = {
            tenLoai: val.tenLoai || '',
            giaTieuChuan: val.giaTieuChuan || 0,
            sucChuaToiDa: val.sucChuaToiDa || 2,
            dienTich: val.dienTich || 28,
            cauHinhGiuong: val.cauHinhGiuong || '1 giường đôi · 1,8 × 2 m',
            tienIchTomTat: val.tienIchTomTat || '',
            dieuKien: val.dieuKien || '1 đêm · Không giới hạn tối đa',
            tienNghis: Array.isArray(val.tienNghis) ? [...val.tienNghis] : [],
            trangThai: val.trangThai || 'Đang mở bán',
          };
        }
      },
    },
    isCreateMode(val) {
      if (val) {
        this.formData = {
          tenLoai: '',
          giaTieuChuan: 800000,
          sucChuaToiDa: 2,
          dienTich: 28,
          cauHinhGiuong: '1 giường đôi · 1,8 × 2 m',
          tienIchTomTat: 'Cửa sổ · Tiện nghi đầy đủ',
          dieuKien: '1 đêm · Không giới hạn tối đa',
          tienNghis: ['Wi-Fi & điều hòa', 'Két an toàn', 'Minibar tính phí'],
          trangThai: 'Đang mở bán',
          soLuongPhongTao: 2,
          tang: 4,
        };
      } else if (this.roomType) {
        this.formData = {
          tenLoai: this.roomType.tenLoai || '',
          giaTieuChuan: this.roomType.giaTieuChuan || 0,
          sucChuaToiDa: this.roomType.sucChuaToiDa || 2,
          dienTich: this.roomType.dienTich || 28,
          cauHinhGiuong: this.roomType.cauHinhGiuong || '1 giường đôi · 1,8 × 2 m',
          tienIchTomTat: this.roomType.tienIchTomTat || '',
          dieuKien: this.roomType.dieuKien || '1 đêm · Không giới hạn tối đa',
          tienNghis: Array.isArray(this.roomType.tienNghis) ? [...this.roomType.tienNghis] : [],
          trangThai: this.roomType.trangThai || 'Đang mở bán',
        };
      }
    },
  },
  methods: {
    handleSubmit() {
      if (!this.formData.tenLoai || !this.formData.giaTieuChuan) {
        return;
      }
      this.$emit('save', {
        id: this.isCreateMode ? null : this.roomType?.id,
        ...this.formData,
      });
    },
  },
};
</script>
