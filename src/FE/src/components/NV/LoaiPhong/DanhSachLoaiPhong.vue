<template>
  <div>
    <!-- THANH TÌM KIẾM VÀ BỘ LỌC (YÊU CẦU BỔ SUNG) -->
    <div class="filter-card mb-4">
      <div class="row g-2 align-items-center">
        <!-- 1. Ô tìm kiếm theo tên loại phòng -->
        <div class="col-12 col-md-4">
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input
              type="text"
              class="form-control"
              placeholder="Tìm theo tên loại phòng..."
              :value="filters.q"
              @input="onSearchInput($event.target.value)"
            />
          </div>
        </div>

        <!-- 2. Lọc theo trạng thái kinh doanh -->
        <div class="col-6 col-md-2">
          <select
            class="form-select filter-select w-100"
            :value="filters.trangThai"
            @change="onFilterChange('trangThai', $event.target.value)"
            title="Trạng thái kinh doanh"
          >
            <option value="Tất cả">Mọi trạng thái</option>
            <option value="Đang mở bán">Đang mở bán</option>
            <option value="Ngừng kinh doanh">Ngừng kinh doanh</option>
          </select>
        </div>

        <!-- 3. Lọc theo khoảng giá mỗi đêm -->
        <div class="col-6 col-md-3">
          <select
            class="form-select filter-select w-100"
            :value="priceRangeValue"
            @change="onPriceRangeChange($event.target.value)"
            title="Khoảng giá mỗi đêm"
          >
            <option value="all">Mọi mức giá</option>
            <option value="under_800">Dưới 800.000 đ</option>
            <option value="800_1500">800.000 đ – 1.500.000 đ</option>
            <option value="over_1500">Trên 1.500.000 đ</option>
          </select>
        </div>

        <!-- 4. Lọc theo sức chứa -->
        <div class="col-8 col-md-2">
          <select
            class="form-select filter-select w-100"
            :value="filters.sucChua"
            @change="onFilterChange('sucChua', $event.target.value)"
            title="Sức chứa người lớn"
          >
            <option value="">Mọi sức chứa</option>
            <option value="1">1 người</option>
            <option value="2">2 người</option>
            <option value="3">3 người trở lên</option>
            <option value="4">4 người trở lên</option>
          </select>
        </div>

        <!-- 5. Nút Xóa bộ lọc -->
        <div class="col-4 col-md-1 text-end">
          <button
            type="button"
            class="btn btn-outline-secondary btn-sm w-100 h-100 d-inline-flex align-items-center justify-content-center gap-1"
            style="min-height: 40px; border-radius: 8px;"
            @click="$emit('reset-filters')"
            title="Xóa toàn bộ điều kiện lọc để hiển thị lại tất cả"
          >
            <i class="bi bi-arrow-counterclockwise"></i>
            <span class="d-none d-xl-inline">Xóa lọc</span>
          </button>
        </div>
      </div>
    </div>

    <!-- DANH SÁCH CÁC CARD LOẠI PHÒNG (BÊN TRÁI) -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-teal spinner-border-sm me-2" role="status"></div>
      <span class="text-muted">Đang tải danh sách loại phòng...</span>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-5 filter-card">
      <i class="bi bi-door-closed fs-1 text-secondary d-block mb-2"></i>
      <div class="fw-bold text-dark">Không tìm thấy loại phòng nào</div>
      <p class="text-muted small mb-3">Thử thay đổi từ khóa tìm kiếm hoặc bấm Xóa bộ lọc bên trên.</p>
      <button class="btn btn-outline-secondary btn-sm px-3" @click="$emit('reset-filters')">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc
      </button>
    </div>

    <div v-else>
      <div
        v-for="item in items"
        :key="item.id"
        :id="'room-type-' + item.id"
        class="room-type-card"
        :class="{ 'card-selected': selectedId === item.id }"
        @click="$emit('select-room-type', item)"
        style="cursor: pointer;"
      >
        <!-- Dòng đầu: Tên loại phòng & Giá mỗi đêm -->
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <div class="card-room-title">{{ item.tenLoai }}</div>
            <div class="bed-icon-wrap mt-2 mb-2">
              <i class="bi bi-lamp"></i>
            </div>
            <div>
              <span v-if="item.trangThai === 'Đang mở bán'" class="badge-active-pill">
                • Đang mở bán
              </span>
              <span v-else class="badge-locked-pill">
                • Ngừng kinh doanh
              </span>
            </div>
          </div>

          <div class="text-end">
            <!-- Khi loại phòng có khuyến mãi hợp lệ -->
            <template v-if="item.khuyenMai && item.khuyenMai.coKhuyenMai">
              <div class="text-muted extra-small text-decoration-line-through mb-0.5" style="font-size: 0.78rem;">
                Giá gốc: {{ item.giaDinhDang }}/đêm
              </div>
              <div class="d-flex align-items-center justify-content-end gap-1.5">
                <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-1.5 py-0.5" style="font-size: 0.75rem;">
                  {{ item.khuyenMai.mucGiamHienThi }}
                </span>
                <span class="card-room-price text-teal fw-bold">
                  {{ item.khuyenMai.giaSauGiamDinhDang }}<span class="fw-normal text-muted" style="font-size: 0.75rem;">/đêm</span>
                </span>
              </div>
            </template>
            <!-- Khi loại phòng chưa có khuyến mãi -->
            <template v-else>
              <div class="card-room-price">
                {{ item.giaDinhDang }} <span>/ đêm</span>
              </div>
              <div class="text-muted extra-small mt-0.5" style="font-size: 0.72rem;">
                Chưa áp dụng khuyến mãi
              </div>
            </template>
          </div>
        </div>

        <!-- Chi tiết thông số loại phòng -->
        <div class="mt-3 pt-2 border-top">
          <div class="card-spec-row">
            <span class="spec-label">Số lượng</span>
            <span class="spec-value">{{ item.soLuongPhong }} phòng · {{ item.tangInfo }}</span>
          </div>

          <div class="card-spec-row">
            <span class="spec-label">Sức chứa / Giường</span>
            <span class="spec-value">{{ item.sucChuaToiDa }} khách · {{ item.cauHinhGiuong }}</span>
          </div>

          <div class="card-spec-row">
            <span class="spec-label">Diện tích</span>
            <span class="spec-value">{{ item.dienTich }} m²</span>
          </div>

          <div v-if="item.tienIchTomTat" class="card-spec-row">
            <span class="spec-label">Tiện ích nổi bật</span>
            <span class="spec-value">{{ item.tienIchTomTat }}</span>
          </div>

          <div class="card-spec-row">
            <span class="spec-label">Phòng liên kết</span>
            <span class="spec-value text-secondary small">{{ item.phongText }}</span>
          </div>
        </div>

        <!-- Thao tác Card: Chỉnh sửa loại phòng & Thiết lập / Chỉnh sửa khuyến mãi -->
        <div class="mt-3 d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
          <button
            type="button"
            class="btn btn-card-edit"
            @click.stop="$emit('select-room-type', item)"
          >
            Chỉnh sửa {{ item.tenLoai }}
          </button>

          <button
            type="button"
            class="btn btn-sm d-inline-flex align-items-center gap-1.5 rounded-3 px-2.5 py-1 fw-medium"
            :class="item.khuyenMai && item.khuyenMai.coKhuyenMai ? 'btn-promo-edit' : 'btn-promo-create'"
            @click.stop="$emit('open-promo-modal', item)"
            :title="item.khuyenMai && item.khuyenMai.coKhuyenMai ? 'Chỉnh sửa chương trình khuyến mãi' : 'Thiết lập chương trình khuyến mãi mới'"
          >
            <i class="bi bi-tag-fill"></i>
            <span>{{ item.khuyenMai && item.khuyenMai.coKhuyenMai ? 'Chỉnh sửa khuyến mãi' : 'Thiết lập khuyến mãi' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DanhSachLoaiPhong',
  props: {
    items: {
      type: Array,
      default: () => [],
    },
    selectedId: {
      type: [Number, String],
      default: null,
    },
    filters: {
      type: Object,
      default: () => ({
        q: '',
        trangThai: 'Tất cả',
        giaMin: '',
        giaMax: '',
        sucChua: '',
      }),
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['select-room-type', 'update-filters', 'reset-filters', 'open-promo-modal'],
  computed: {
    priceRangeValue() {
      const min = this.filters.giaMin;
      const max = this.filters.giaMax;
      if (max === 800000 && (!min || min === 0)) return 'under_800';
      if (min === 800000 && max === 1500000) return '800_1500';
      if (min === 1500000 && !max) return 'over_1500';
      return 'all';
    },
  },
  methods: {
    onSearchInput(val) {
      this.$emit('update-filters', { ...this.filters, q: val });
    },
    onFilterChange(field, val) {
      this.$emit('update-filters', { ...this.filters, [field]: val });
    },
    onPriceRangeChange(val) {
      let giaMin = '';
      let giaMax = '';
      if (val === 'under_800') {
        giaMin = 0;
        giaMax = 800000;
      } else if (val === '800_1500') {
        giaMin = 800000;
        giaMax = 1500000;
      } else if (val === 'over_1500') {
        giaMin = 1500000;
        giaMax = '';
      }
      this.$emit('update-filters', { ...this.filters, giaMin, giaMax });
    },
  },
};
</script>
