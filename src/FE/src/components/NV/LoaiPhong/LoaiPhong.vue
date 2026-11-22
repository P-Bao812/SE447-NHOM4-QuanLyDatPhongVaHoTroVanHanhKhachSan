<template>
  <div class="loai-phong-container py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- TOAST THÔNG BÁO -->
      <div
        v-if="toast.show"
        class="alert alert-dismissible fade show position-fixed top-0 end-0 m-4 shadow-lg"
        :class="toast.type === 'success' ? 'alert-success border-success' : 'alert-danger border-danger'"
        style="z-index: 9999; max-width: 440px;"
        role="alert"
      >
        <div class="d-flex align-items-start gap-2">
          <i
            class="fs-5"
            :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-success' : 'bi bi-exclamation-triangle-fill text-danger'"
          ></i>
          <div>
            <strong>{{ toast.type === 'success' ? 'Thành công' : 'Thông báo máy chủ' }}</strong>
            <div class="small mt-1">{{ toast.message }}</div>
          </div>
        </div>
        <button type="button" class="btn-close" @click="toast.show = false"></button>
      </div>

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: BREADCRUMB & THÔNG TIN HỆ THỐNG -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-3">
          <button
            class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-xs"
            @click="quayLai"
            title="Quay lại"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
          </button>

          <div class="breadcrumb-custom">
            <span>Khách sạn</span>
            <span class="mx-2">&rsaquo;</span>
            <span class="text-dark fw-medium">Quản lý loại phòng</span>
          </div>
        </div>

        <!-- Góc phải: Thời gian, Thông báo, Người dùng -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1"></i>
            {{ ngayHienTai }}
          </span>

          <button class="btn btn-light btn-sm rounded-circle border p-2" title="Thông báo">
            <i class="bi bi-bell text-secondary"></i>
          </button>

          <!-- Avatar Admin -->
          <div class="d-flex align-items-center gap-2 py-1 px-2 border rounded-pill bg-white shadow-xs">
            <div
              class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
              style="width: 28px; height: 28px; background-color: #e6f4f1; color: #0d7065; font-size: 0.75rem;"
            >
              TA
            </div>
            <div class="text-start d-none d-sm-block me-1" style="line-height: 1.2;">
              <div class="fw-bold" style="font-size: 0.8rem;">Trần An</div>
              <div class="text-muted" style="font-size: 0.7rem;">Admin</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ TRANG & NÚT THÊM LOẠI PHÒNG -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 mt-2">
        <div>
          <h1 class="page-title mb-1">Quản lý loại phòng</h1>
          <p class="page-subtitle mb-0">
            Cấu hình hạng phòng, giá niêm yết và các điều kiện mở bán.
          </p>
        </div>

        <div>
          <button class="btn btn-primary-teal shadow-sm" @click="batCheDoThemMoi">
            <i class="bi bi-plus-lg"></i>
            <span>Thêm loại phòng</span>
          </button>
        </div>
      </div>

      <!-- BỐ CỤC 2 CỘT THEO ẢNH MẪU THAM KHẢO -->
      <div class="row g-4 align-items-start">
        <!-- Cột trái (col-lg-6): Danh sách loại phòng dưới dạng CARD kèm bộ lọc -->
        <div class="col-12 col-lg-6">
          <DanhSachLoaiPhong
            :items="roomTypes"
            :selectedId="selectedRoomTypeId"
            :filters="filters"
            :loading="loadingList"
            @select-room-type="onSelectRoomType"
            @update-filters="onUpdateFilters"
            @reset-filters="onResetFilters"
            @open-promo-modal="onOpenPromoModal"
          />
        </div>

        <!-- Cột phải (col-lg-6): Form chỉnh sửa / thêm mới loại phòng -->
        <div class="col-12 col-lg-6 sticky-lg-top" style="top: 90px; z-index: 10;">
          <FormLoaiPhong
            :roomType="selectedRoomType"
            :isCreateMode="isCreateMode"
            :allAmenities="amenities"
            :loadingSave="loadingSave"
            :loadingToggle="loadingToggle"
            @save="onSaveRoomType"
            @toggle-status="onToggleStatus"
            @cancel-create="isCreateMode = false"
          />
        </div>
      </div>

      <!-- MODAL THIẾT LẬP / CHỈNH SỬA KHUYẾN MÃI TỪNG CARD LOẠI PHÒNG -->
      <ModalKhuyenMaiLoaiPhong
        :show="showPromoModal"
        :room="promoRoom"
        :saving="savingPromo"
        @close="showPromoModal = false"
        @save="onSavePromo"
      />
    </div>
  </div>
</template>

<script>
import './loaiphong.css';
import loaiphongService from './loaiphongService.js';
import DanhSachLoaiPhong from './DanhSachLoaiPhong.vue';
import FormLoaiPhong from './FormLoaiPhong.vue';
import ModalKhuyenMaiLoaiPhong from './ModalKhuyenMaiLoaiPhong.vue';

export default {
  name: 'LoaiPhong',
  components: {
    DanhSachLoaiPhong,
    FormLoaiPhong,
    ModalKhuyenMaiLoaiPhong,
  },
  data() {
    return {
      roomTypes: [],
      amenities: [],
      selectedRoomTypeId: null,
      isCreateMode: false,
      loadingList: false,
      loadingSave: false,
      loadingToggle: false,

      // Trạng thái modal khuyến mãi
      showPromoModal: false,
      promoRoom: null,
      savingPromo: false,

      // Bộ lọc và tìm kiếm theo yêu cầu bổ sung
      filters: {
        q: '',
        trangThai: 'Tất cả',
        giaMin: '',
        giaMax: '',
        sucChua: '',
      },

      toast: {
        show: false,
        message: '',
        type: 'success',
      },
    };
  },
  computed: {
    selectedRoomType() {
      if (this.isCreateMode || !this.selectedRoomTypeId) return null;
      return this.roomTypes.find((r) => r.id === this.selectedRoomTypeId) || null;
    },
    ngayHienTai() {
      const d = new Date();
      const thu = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${thu[d.getDay()]}, ${day} tháng ${month}, ${year}`;
    },
  },
  async mounted() {
    await this.fetchAmenities();
    await this.fetchRoomTypes();
  },
  methods: {
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 5000);
    },

    quayLai() {
      if (window.history.length > 1) {
        this.$router.back();
      } else {
        this.$router.push('/');
      }
    },

    batCheDoThemMoi() {
      this.isCreateMode = true;
      this.selectedRoomTypeId = null;
    },

    onSelectRoomType(item) {
      this.isCreateMode = false;
      this.selectedRoomTypeId = item.id;
    },

    async fetchAmenities() {
      try {
        const res = await loaiphongService.getDanhSachTienNghi();
        if (res.status === 'success') {
          this.amenities = res.data;
        }
      } catch (err) {
        console.warn('Lỗi tải danh mục tiện nghi:', err);
      }
    },

    async fetchRoomTypes(preferId = null) {
      this.loadingList = true;
      try {
        const res = await loaiphongService.getDanhSachLoaiPhong(this.filters);
        if (res.status === 'success') {
          this.roomTypes = res.data;
          // Xác định item được chọn
          const targetId = preferId || this.selectedRoomTypeId;
          if (targetId && this.roomTypes.some((r) => r.id === targetId)) {
            this.selectedRoomTypeId = targetId;
          } else if (!this.isCreateMode && this.roomTypes.length > 0) {
            // Ưu tiên chọn Deluxe (hoặc Superior) như trong ảnh mẫu
            const defaultItem = this.roomTypes.find((r) => r.tenLoai === 'Deluxe') || this.roomTypes[0];
            this.selectedRoomTypeId = defaultItem.id;
          }
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.loadingList = false;
      }
    },

    onUpdateFilters(newFilters) {
      this.filters = { ...newFilters };
      clearTimeout(this._filterTimer);
      this._filterTimer = setTimeout(() => {
        this.fetchRoomTypes();
      }, 300);
    },

    onResetFilters() {
      this.filters = {
        q: '',
        trangThai: 'Tất cả',
        giaMin: '',
        giaMax: '',
        sucChua: '',
      };
      this.fetchRoomTypes();
    },

    onOpenPromoModal(room) {
      this.promoRoom = room;
      this.showPromoModal = true;
    },

    async onSavePromo(formData) {
      this.savingPromo = true;
      try {
        const res = await loaiphongService.thietLapKhuyenMaiLoaiPhong(formData);
        this.showToast(res.message || 'Thiết lập khuyến mãi cho loại phòng thành công!', 'success');
        this.showPromoModal = false;
        await this.fetchRoomTypes(this.selectedRoomTypeId);
      } catch (err) {
        this.showToast(err.message || 'Không thể lưu khuyến mãi.', 'danger');
      } finally {
        this.savingPromo = false;
      }
    },

    async onSaveRoomType(payload) {
      this.loadingSave = true;
      try {
        if (this.isCreateMode) {
          const res = await loaiphongService.themMoiLoaiPhong(payload);
          const newId = res.data?.id;

          // 1. Chuyển ngay khỏi chế độ tạo mới
          this.isCreateMode = false;

          // 2. Chọn ngay loại phòng mới tạo
          if (newId) {
            this.selectedRoomTypeId = newId;
          }

          // 3. Xóa từ khóa tìm kiếm nếu có để đảm bảo loại phòng mới hiển thị ngay trên danh sách
          if (this.filters.q) {
            this.filters.q = '';
          }

          // 4. Tải lại danh sách và chọn chính xác loại phòng mới
          await this.fetchRoomTypes(newId);
          this.showToast(res.message || 'Thêm loại phòng thành công và đã chuyển sang phòng hiện có!', 'success');

          // 5. Cuộn mượt tới card của loại phòng mới bên danh sách các phòng hiện có
          this.$nextTick(() => {
            if (newId) {
              const el = document.getElementById(`room-type-${newId}`);
              if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
              }
            }
          });
        } else {
          const res = await loaiphongService.capNhatLoaiPhong(payload.id, payload);
          this.showToast(res.message || 'Cập nhật loại phòng thành công!', 'success');
          await this.fetchRoomTypes(payload.id);
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.loadingSave = false;
      }
    },

    async onToggleStatus(id) {
      this.loadingToggle = true;
      try {
        const res = await loaiphongService.doiTrangThai(id);
        this.showToast(res.message || 'Đã cập nhật trạng thái thành công!', 'success');
        await this.fetchRoomTypes();
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.loadingToggle = false;
      }
    },
  },
};
</script>
