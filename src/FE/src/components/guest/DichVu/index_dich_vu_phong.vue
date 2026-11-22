<template>
  <div class="dich-vu-phong-page py-4 py-lg-5">
    <div class="container-fluid px-3 px-lg-5">
      <!-- TOAST THÔNG BÁO KẾT QUẢ -->
      <div
        v-if="toast.show"
        class="toast-alert-custom position-fixed top-0 end-0 m-3 m-md-4 shadow-lg p-3 rounded-3 d-flex align-items-start gap-2.5"
        :class="toast.type === 'success' ? 'bg-toast-success' : 'bg-toast-danger'"
        style="z-index: 9999; max-width: 440px;"
        role="alert"
      >
        <i
          class="fs-4 flex-shrink-0"
          :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-success' : 'bi bi-exclamation-triangle-fill text-danger'"
        ></i>
        <div class="flex-grow-1">
          <strong class="d-block" :class="toast.type === 'success' ? 'text-success-emphasis' : 'text-danger-emphasis'">
            {{ toast.type === 'success' ? 'Thành công' : 'Thông báo từ khách sạn' }}
          </strong>
          <div class="small mt-0.5 text-secondary-dark">{{ toast.message }}</div>
        </div>
        <button type="button" class="btn-close-toast" @click="toast.show = false">
          <i class="bi bi-x"></i>
        </button>
      </div>

      <!-- BANNER TIÊU ĐỀ TRANG -->
      <div class="page-header-card p-4 p-md-4.5 rounded-4 mb-4 border bg-white shadow-xs">
        <div class="row align-items-center g-3">
          <div class="col-12 col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-2.5 py-1 rounded-pill bg-teal-subtle text-teal small fw-semibold mb-2">
              <i class="bi bi-stars"></i>
              <span>Dịch vụ phòng khách sạn cao cấp</span>
            </div>
            <!-- Tiêu đề: Dịch vụ khách sạn, kèm mô tả ngắn theo đúng yêu cầu đề bài -->
            <h1 class="page-title fw-bold mb-2">Dịch vụ khách sạn</h1>
            <p class="page-description text-muted mb-0">
              Trải nghiệm các tiện ích ẩm thực, buồng phòng, giặt ủi và thư giãn chuẩn 5 sao được phục vụ chu đáo, tận tâm ngay tại phòng nghỉ của Quý khách.
            </p>
          </div>

          <!-- Thông tin hỗ trợ & Trạng thái phòng lưu trú -->
          <div class="col-12 col-lg-4 text-lg-end">
            <div class="stay-info-chip d-inline-flex flex-column align-items-lg-end p-3 rounded-3 bg-light-card border">
              <div class="d-flex align-items-center gap-2 text-teal fw-semibold small mb-1">
                <i class="bi bi-shield-check fs-5"></i>
                <span>Hệ thống đăng ký trực tuyến</span>
              </div>
              <div class="small text-muted" style="font-size: 0.8rem;">
                Phục vụ 24/7 · Hotline quầy lễ tân: <strong class="text-dark">0905 888 999</strong>
              </div>
              <div v-if="activeRooms.length > 0" class="badge bg-success-subtle text-success border border-success-subtle mt-1.5" style="font-size: 0.75rem;">
                <i class="bi bi-person-check-fill me-1"></i>Đang ghi nhận {{ activeRooms.length }} lượt lưu trú hợp lệ
              </div>
            </div>
          </div>
        </div>

        <!-- TABS CHUYỂN ĐỔI CHÍNH: DANH MỤC DỊCH VỤ / YÊU CẦU ĐÃ ĐĂNG KÝ -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
          <ul class="nav nav-pills custom-main-tabs gap-2">
            <li class="nav-item">
              <button
                class="nav-link d-inline-flex align-items-center tab-nav-custom"
                :class="{ active: currentTab === 'services' }"
                @click="currentTab = 'services'"
              >
                <i class="bi bi-grid-fill me-2"></i>
                <span>Danh mục dịch vụ</span>
                <span class="tab-count-pill ms-2" :class="{ 'active-pill': currentTab === 'services' }">
                  {{ services.length }}
                </span>
              </button>
            </li>
            <li class="nav-item">
              <button
                class="nav-link d-inline-flex align-items-center gap-1.5 tab-nav-custom"
                :class="{ active: currentTab === 'requests' }"
                @click="currentTab = 'requests'"
              >
                <i class="bi bi-clock-history me-1"></i>
                <span>Yêu cầu đã đăng ký</span>
                <span class="tab-count-pill ms-1" :class="{ 'active-pill': currentTab === 'requests' }">
                  {{ requests.length }}
                </span>
                <span
                  v-if="pendingRequestsCount > 0"
                  class="badge-pending-pill ms-1.5"
                  :class="{ 'badge-pending-active': currentTab === 'requests' }"
                  title="Có yêu cầu đang chờ tiếp nhận hoặc đang xử lý"
                >
                  <span class="pulse-dot"></span>
                  <span>{{ pendingRequestsCount }} đang chờ</span>
                </span>
              </button>
            </li>
          </ul>

          <div class="text-muted small d-none d-md-block">
            <i class="bi bi-info-circle me-1 text-teal"></i>
            <span>Chỉ khách đang lưu trú hợp lệ mới có thể đăng ký dịch vụ cho phòng của mình.</span>
          </div>
        </div>
      </div>

      <!-- ==================== TAB 1: DANH MỤC 5 DỊCH VỤ ==================== -->
      <div v-if="currentTab === 'services'" class="services-tab-content animate-fade-in">
        <!-- THANH CÔNG CỤ: Ô TÌM KIẾM THEO TÊN & BỘ LỌC THEO LOẠI DỊCH VỤ -->
        <div class="filter-toolbar-card p-3 rounded-4 mb-4 bg-white border shadow-xs">
          <div class="row g-3 align-items-center">
            <!-- Ô TÌM KIẾM THEO TÊN DỊCH VỤ -->
            <div class="col-12 col-md-5 col-lg-4">
              <div class="search-input-wrap position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input
                  v-model="filters.q"
                  type="text"
                  class="form-control ps-5 rounded-pill border form-search-service"
                  placeholder="Tìm kiếm theo tên dịch vụ..."
                  @input="handleSearchInput"
                />
                <button
                  v-if="filters.q"
                  type="button"
                  class="btn-clear position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted"
                  @click="filters.q = ''; fetchServices();"
                  title="Xóa tìm kiếm"
                >
                  <i class="bi bi-x-circle-fill"></i>
                </button>
              </div>
            </div>

            <!-- BỘ LỌC THEO LOẠI DỊCH VỤ -->
            <div class="col-12 col-md-7 col-lg-8">
              <div class="d-flex align-items-center gap-1.5 flex-wrap overflow-x-auto py-1">
                <span class="text-muted small me-1 d-none d-sm-inline">
                  <i class="bi bi-funnel me-1"></i>Loại:
                </span>
                <button
                  type="button"
                  class="btn btn-sm btn-category-pill rounded-pill"
                  :class="{ active: filters.loai === 'Tất cả' }"
                  @click="setCategoryFilter('Tất cả')"
                >
                  Tất cả
                </button>
                <button
                  v-for="cat in availableCategories"
                  :key="cat"
                  type="button"
                  class="btn btn-sm btn-category-pill rounded-pill"
                  :class="{ active: filters.loai === cat }"
                  @click="setCategoryFilter(cat)"
                >
                  <i :class="getCategoryIcon(cat)" class="me-1"></i>
                  <span>{{ cat }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- LOADING SPINNER -->
        <div v-if="loadingServices" class="text-center py-5">
          <div class="spinner-border text-teal" role="status">
            <span class="visually-hidden">Đang tải danh sách dịch vụ...</span>
          </div>
          <p class="text-muted small mt-2">Đang tải danh sách dịch vụ từ khách sạn...</p>
        </div>

        <!-- HIỂN THỊ DANH SÁCH 5 DỊCH VỤ DƯỚI DẠNG CARD -->
        <div v-else-if="services.length > 0" class="row g-4">
          <div
            v-for="item in services"
            :key="item.id"
            class="col-12 col-md-6 col-lg-4"
          >
            <div class="service-card h-100 bg-white rounded-4 border overflow-hidden shadow-xs d-flex flex-column transition-all hover-translate">
              <!-- KHỐI HÌNH ẢNH DỊCH VỤ -->
              <div class="service-image-box position-relative overflow-hidden">
                <img
                  :src="item.hinhAnh || defaultImages[item.id] || defaultFallbackImage"
                  :alt="item.tenDichVu"
                  class="service-img w-100 object-fit-cover"
                  loading="lazy"
                  @error="onImageError($event, item.id)"
                />
                <!-- Badge Loại dịch vụ -->
                <div class="position-absolute top-0 start-0 m-3">
                  <span class="badge badge-category shadow-xs d-inline-flex align-items-center gap-1">
                    <i :class="getCategoryIcon(item.loaiDichVu)"></i>
                    <span>{{ item.loaiDichVu || 'Dịch vụ' }}</span>
                  </span>
                </div>
                <!-- Badge Đang cung cấp -->
                <div class="position-absolute top-0 end-0 m-3">
                  <span class="badge badge-status-pill shadow-xs">
                    <span class="status-dot"></span>
                    <span>Phục vụ tại phòng</span>
                  </span>
                </div>
              </div>

              <!-- NỘI DUNG CARD: TÊN, MÔ TẢ, GIÁ, ĐƠN VỊ TÍNH -->
              <div class="p-3.5 p-md-4 d-flex flex-column flex-grow-1">
                <!-- Tên dịch vụ -->
                <h5 class="service-title fw-bold mb-2 text-main-dark">
                  {{ item.tenDichVu }}
                </h5>

                <!-- Mô tả dịch vụ -->
                <p class="service-description text-muted small flex-grow-1 mb-3">
                  {{ item.moTa }}
                </p>

                <!-- KHU VỰC GIÁ VÀ BUTTON ĐĂNG KÝ SỬ DỤNG -->
                <div class="pt-3 border-top d-flex align-items-center justify-content-between gap-2 mt-auto">
                  <div>
                    <div class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.3px;">GIÁ DỊCH VỤ</div>
                    <div class="service-price fw-bold text-teal">
                      {{ formatCurrency(item.donGia) }}
                      <span class="price-unit text-muted fw-normal">/ {{ item.donViTinh || 'Lượt' }}</span>
                    </div>
                  </div>

                  <!-- BUTTON ĐĂNG KÝ SỬ DỤNG: Màu xanh lá đậm, chữ trắng, KHÔNG CÓ button Thêm dịch vụ -->
                  <button
                    type="button"
                    class="btn btn-register-service rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-xs"
                    @click="openBookingModal(item)"
                  >
                    <i class="bi bi-clipboard-plus"></i>
                    <span>Đăng ký sử dụng</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- EMPTY STATE KHI KHÔNG TÌM THẤY DỊCH VỤ -->
        <div v-else class="text-center py-5 bg-white rounded-4 border p-4">
          <i class="bi bi-search text-muted fs-1 mb-2 d-block"></i>
          <h6 class="fw-bold text-main-dark">Không tìm thấy dịch vụ phù hợp</h6>
          <p class="text-muted small mb-3">
            Không có kết quả nào cho từ khóa "{{ filters.q }}". Vui lòng thử tìm với từ khóa khác hoặc xóa bộ lọc.
          </p>
          <button
            type="button"
            class="btn btn-outline-secondary btn-sm rounded-pill px-3"
            @click="filters.q = ''; filters.loai = 'Tất cả'; fetchServices();"
          >
            Đặt lại bộ lọc
          </button>
        </div>
      </div>

      <!-- ==================== TAB 2: YÊU CẦU ĐÃ ĐĂNG KÝ ==================== -->
      <div v-else-if="currentTab === 'requests'" class="requests-tab-content animate-fade-in">
        <danh-sach-yeu-cau
          :requests="requests"
          :loading="loadingRequests"
          @cancel="handleCancelRequest"
          @update-status="handleUpdateStatus"
          @switch-to-services="currentTab = 'services'"
        ></danh-sach-yeu-cau>
      </div>

      <!-- MODAL ĐĂNG KÝ SỬ DỤNG DỊCH VỤ -->
      <modal-dang-ky-dich-vu
        :show="showBookingModal"
        :service="selectedService"
        :active-rooms="activeRooms"
        :loading="submittingBooking"
        @close="showBookingModal = false"
        @submit="handleConfirmBooking"
      ></modal-dang-ky-dich-vu>
    </div>
  </div>
</template>

<script>
import dichVuService from './dichVuService.js';
import ModalDangKyDichVu from './modal_dang_ky_dich_vu.vue';
import DanhSachYeuCau from './danh_sach_yeu_cau.vue';

export default {
  name: 'IndexDichVuPhong',
  components: {
    ModalDangKyDichVu,
    DanhSachYeuCau
  },
  data() {
    return {
      currentTab: 'services', // 'services' hoặc 'requests'
      services: [],
      requests: [],
      activeRooms: [],
      availableCategories: ['Ẩm thực', 'Giặt ủi', 'Buồng phòng', 'Đồ uống', 'Spa & Massage'],
      loadingServices: false,
      loadingRequests: false,
      submittingBooking: false,

      // Bộ lọc tìm kiếm
      filters: {
        q: '',
        loai: 'Tất cả'
      },
      searchDebounceTimer: null,

      // Modal đăng ký
      showBookingModal: false,
      selectedService: null,

      // Toast thông báo
      toast: {
        show: false,
        message: '',
        type: 'success'
      },

      // Fallback hình ảnh chất lượng cao tương ứng với 5 dịch vụ
      defaultImages: {
        1: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        2: 'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=800&q=80',
        3: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        4: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
        5: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80'
      },
      defaultFallbackImage: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80'
    };
  },
  computed: {
    pendingRequestsCount() {
      return this.requests.filter(r => r.trangThai === 'Tiep_Nhan' || r.trangThai === 'Dang_Xu_Ly').length;
    }
  },
  mounted() {
    this.fetchServices();
    this.fetchActiveRooms();
    this.fetchRequests();
  },
  methods: {
    /**
     * 1. LẤY DANH SÁCH 5 DỊCH VỤ TỪ DATABASE QUA LARAVEL API
     */
    async fetchServices() {
      this.loadingServices = true;
      try {
        const res = await dichVuService.getDanhSachDichVu(this.filters);
        if (res.status === 'success') {
          this.services = res.data || [];
          if (res.categories && res.categories.length > 0) {
            this.availableCategories = res.categories;
          }
        }
      } catch (err) {
        console.error('Lỗi tải dịch vụ:', err);
        this.showToastNotification(err.message || 'Không thể tải danh sách dịch vụ.', 'danger');
      } finally {
        this.loadingServices = false;
      }
    },

    /**
     * 2. LẤY DANH SÁCH CÁC PHÒNG ĐANG CÓ LƯU TRÚ HỢP LỆ
     */
    async fetchActiveRooms() {
      try {
        const res = await dichVuService.getPhongDangLuuTru();
        if (res.status === 'success') {
          this.activeRooms = res.data || [];
        }
      } catch (err) {
        console.error('Lỗi tải phòng lưu trú:', err);
      }
    },

    /**
     * 3. LẤY DANH SÁCH CÁC YÊU CẦU DỊCH VỤ ĐÃ ĐĂNG KÝ
     */
    async fetchRequests() {
      this.loadingRequests = true;
      try {
        const res = await dichVuService.getDanhSachYeuCau();
        if (res.status === 'success') {
          this.requests = res.data || [];
        }
      } catch (err) {
        console.error('Lỗi tải danh sách yêu cầu:', err);
      } finally {
        this.loadingRequests = false;
      }
    },

    /**
     * TÌM KIẾM THEO TÊN DỊCH VỤ VỚI DEBOUNCE
     */
    handleSearchInput() {
      clearTimeout(this.searchDebounceTimer);
      this.searchDebounceTimer = setTimeout(() => {
        this.fetchServices();
      }, 350);
    },

    /**
     * BỘ LỌC LOẠI DỊCH VỤ
     */
    setCategoryFilter(cat) {
      this.filters.loai = cat;
      this.fetchServices();
    },

    /**
     * MỞ FORM ĐĂNG KÝ DỊCH VỤ
     */
    openBookingModal(service) {
      this.selectedService = service;
      this.showBookingModal = true;
    },

    /**
     * GỬI YÊU CẦU SỬ DỤNG DỊCH VỤ LÊN BACKEND LARAVEL
     * Chỉ cập nhật giao diện khi API trả về thành công!
     */
    async handleConfirmBooking(payload) {
      this.submittingBooking = true;
      try {
        const res = await dichVuService.dangKyDichVu(payload);
        if (res.status === 'success') {
          // 1. Thông báo kết quả thành công
          this.showToastNotification(
            res.message || 'Đăng ký sử dụng dịch vụ thành công! Khách sạn đã tiếp nhận.',
            'success'
          );
          // 2. Đóng modal đăng ký
          this.showBookingModal = false;
          // 3. Tải lại danh sách yêu cầu để khách theo dõi trạng thái mới nhất
          await this.fetchRequests();
          // 4. Chuyển sang tab theo dõi yêu cầu
          this.currentTab = 'requests';
        } else {
          this.showToastNotification(res.message || 'Không thể đăng ký dịch vụ.', 'danger');
        }
      } catch (err) {
        console.error('Lỗi đăng ký dịch vụ:', err);
        this.showToastNotification(err.message || 'Lỗi xử lý đăng ký dịch vụ.', 'danger');
      } finally {
        this.submittingBooking = false;
      }
    },

    /**
     * KHÁCH HÀNG HỦY YÊU CẦU DỊCH VỤ
     */
    async handleCancelRequest(payload) {
      const id = typeof payload === 'object' && payload !== null ? payload.id : payload;
      const lyDoHuy = typeof payload === 'object' && payload !== null ? payload.lyDoHuy : 'Khách hủy yêu cầu';

      try {
        const res = await dichVuService.huyYeuCau(id, lyDoHuy);
        if (res.status === 'success') {
          this.showToastNotification(res.message || 'Đã hủy yêu cầu thành công.', 'success');
          await this.fetchRequests();
        }
      } catch (err) {
        this.showToastNotification(err.message || 'Không thể hủy yêu cầu.', 'danger');
      }
    },

    /**
     * NHÂN VIÊN / LỄ TÂN CẬP NHẬT TRẠNG THÁI YÊU CẦU
     * Khi hoàn thành: tự động cộng vào hóa đơn lưu trú
     */
    async handleUpdateStatus({ id, trangThai }) {
      try {
        const res = await dichVuService.capNhatTrangThaiYeuCau(id, trangThai);
        if (res.status === 'success') {
          this.showToastNotification(res.message || 'Đã cập nhật trạng thái!', 'success');
          await this.fetchRequests();
        }
      } catch (err) {
        this.showToastNotification(err.message || 'Lỗi cập nhật trạng thái.', 'danger');
      }
    },

    /**
     * HIỂN THỊ TOAST THÔNG BÁO
     */
    showToastNotification(message, type = 'success') {
      this.toast = {
        show: true,
        message,
        type
      };
      setTimeout(() => {
        if (this.toast.message === message) {
          this.toast.show = false;
        }
      }, 5000);
    },

    /**
     * HELPER ICON THEO LOẠI DỊCH VỤ
     */
    getCategoryIcon(category) {
      if (!category) return 'bi-tag-fill';
      const c = category.toLowerCase();
      if (c.includes('ẩm thực') || c.includes('ăn uống')) return 'bi-egg-fried';
      if (c.includes('giặt ủi')) return 'bi-water';
      if (c.includes('buồng phòng') || c.includes('dọn phòng') || c.includes('vệ sinh')) return 'bi-stars';
      if (c.includes('đồ uống') || c.includes('minibar')) return 'bi-cup-straw';
      if (c.includes('spa') || c.includes('massage')) return 'bi-flower1';
      return 'bi-bookmark-star-fill';
    },

    /**
     * FALLBACK KHI HÌNH ẢNH LỖI MẠNG HOẶC OFFLINE
     */
    onImageError(event, id) {
      if (this.defaultImages[id]) {
        event.target.src = this.defaultImages[id];
      } else {
        event.target.src = this.defaultFallbackImage;
      }
    },

    /**
     * ĐỊNH DẠNG TIỀN TỆ VND
     */
    formatCurrency(val) {
      if (!val && val !== 0) return '0 đ';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    }
  }
};
</script>

<style scoped>
/* TOÀN BỘ GIAO DIỆN SỬ DỤNG MÀU CHỦ ĐẠO: XANH LÁ ĐẬM #1E5E49 */
.dich-vu-phong-page {
  background-color: #F8F9F8;
  min-height: calc(100vh - 76px - 85px);
  font-family: inherit;
}

.text-teal {
  color: #1E5E49 !important;
}

.text-main-dark {
  color: #172620 !important;
}

.bg-teal-subtle {
  background-color: #EAF3EE;
}

/* Page Header Card */
.page-header-card {
  border-color: #EAE8E1 !important;
}

.page-title {
  color: #1E5E49;
  font-size: 1.85rem;
  letter-spacing: -0.5px;
}

.page-description {
  font-size: 0.95rem;
  line-height: 1.55;
  color: #637069 !important;
}

.bg-light-card {
  background-color: #FAF9F5;
  border-color: #EAE8E1 !important;
}

/* Tabs chính */
.custom-main-tabs .nav-link {
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.92rem;
  color: #4A5650;
  padding: 8px 18px;
  border: 1px solid #EAE8E1;
  background-color: #FAF9F5;
  transition: all 0.2s ease;
}

.custom-main-tabs .nav-link:hover {
  color: #1E5E49;
  border-color: #B2D4C6;
  background-color: #EAF3EE;
}

.custom-main-tabs .nav-link.active {
  background-color: #1E5E49;
  border-color: #1E5E49;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(30, 94, 73, 0.2);
}

.tab-count-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 7px;
  border-radius: 11px;
  font-size: 0.76rem;
  font-weight: 700;
  background-color: #E2E8E4;
  color: #374151;
  transition: all 0.2s;
}

.tab-count-pill.active-pill {
  background-color: rgba(255, 255, 255, 0.22);
  color: #ffffff;
}

/* Badge số yêu cầu đang chờ: Thiết kế sang trọng, độ tương phản cao, dễ nhìn */
.badge-pending-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background-color: #FEF3C7;
  color: #92400E;
  border: 1px solid #FDE68A;
  border-radius: 999px;
  padding: 2px 8px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1px;
  line-height: 1.3;
  transition: all 0.2s ease;
}

.badge-pending-active {
  background-color: #FFFBEB;
  color: #92400E;
  border-color: #FCD34D;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: #D97706;
  display: inline-block;
  animation: pulseAmber 2s infinite ease-in-out;
}

@keyframes pulseAmber {
  0%, 100% {
    transform: scale(0.9);
    opacity: 0.8;
  }
  50% {
    transform: scale(1.3);
    opacity: 1;
  }
}

/* Filter toolbar */
.filter-toolbar-card {
  border-color: #EAE8E1 !important;
}

.form-search-service {
  border-color: #D8DFDB;
  font-size: 0.88rem;
  padding-top: 8px;
  padding-bottom: 8px;
}

.form-search-service:focus {
  border-color: #1E5E49;
  box-shadow: 0 0 0 3px rgba(30, 94, 73, 0.15);
}

.btn-clear {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

/* Category pills */
.btn-category-pill {
  border: 1px solid #D8DFDB;
  background-color: #ffffff;
  color: #4A5650;
  font-size: 0.82rem;
  font-weight: 500;
  padding: 6px 14px;
  transition: all 0.18s ease;
  white-space: nowrap;
}

.btn-category-pill:hover {
  border-color: #1E5E49;
  color: #1E5E49;
  background-color: #F4F8F6;
}

.btn-category-pill.active {
  background-color: #1E5E49;
  border-color: #1E5E49;
  color: #ffffff;
}

/* 5 Services Cards */
.service-card {
  border-color: #EAE8E1 !important;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.hover-translate:hover {
  transform: translateY(-4px);
  border-color: #B5D4C7 !important;
  box-shadow: 0 12px 24px -6px rgba(30, 94, 73, 0.12) !important;
}

.service-image-box {
  height: 200px;
  background-color: #EAF3EE;
}

.service-img {
  height: 100%;
  transition: transform 0.35s ease;
}

.service-card:hover .service-img {
  transform: scale(1.04);
}

.badge-category {
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  color: #1E5E49;
  font-weight: 600;
  font-size: 0.75rem;
  padding: 5px 10px;
  border-radius: 8px;
  border: 1px solid rgba(234, 243, 238, 0.9);
}

.badge-status-pill {
  background-color: rgba(20, 36, 30, 0.85);
  backdrop-filter: blur(4px);
  color: #ffffff;
  font-size: 0.72rem;
  font-weight: 500;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status-dot {
  width: 6px;
  height: 6px;
  background-color: #10B981;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
}

.service-title {
  font-size: 1.12rem;
  letter-spacing: -0.2px;
}

.service-description {
  line-height: 1.5;
  color: #637069 !important;
  min-height: 48px;
}

.service-price {
  font-size: 1.25rem;
  line-height: 1.2;
}

.price-unit {
  font-size: 0.82rem;
}

/* BUTTON ĐĂNG KÝ SỬ DỤNG DỊCH VỤ */
.btn-register-service {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 600;
  font-size: 0.86rem;
  transition: all 0.2s ease;
}

.btn-register-service:hover {
  background-color: #164637;
  border-color: #164637;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(30, 94, 73, 0.25);
}

/* TOAST ALERT */
.toast-alert-custom {
  border-radius: 14px;
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.bg-toast-success {
  background-color: #F0FDF4;
  border: 1.5px solid #86EFAC;
}

.bg-toast-danger {
  background-color: #FEF2F2;
  border: 1.5px solid #FCA5A5;
}

.btn-close-toast {
  background: transparent;
  border: none;
  font-size: 1.2rem;
  color: #6B7280;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.btn-close-toast:hover {
  color: #111827;
}

.shadow-xs {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.animate-fade-in {
  animation: fadeIn 0.2s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .page-title {
    font-size: 1.5rem;
  }
  .service-image-box {
    height: 180px;
  }
}
</style>
