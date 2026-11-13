<template>
  <header class="app-header d-flex align-items-center justify-content-between px-3 px-lg-4 border-bottom bg-white">
    <!-- Breadcrumb & Role Badge -->
    <div class="d-flex align-items-center gap-2 text-truncate">
      <span class="text-secondary-custom small">Phân hệ PMS</span>
      <i class="bi bi-chevron-right text-secondary-custom fs-7"></i>
      <span class="fw-bold text-main-dark text-truncate small">
        {{ getTabBreadcrumb(hotelStore.activePmsTab) }}
      </span>
      <div>
        <span
          class="d-none d-md-inline-flex px-2.5 py-1 rounded-pill text-uppercase fw-bold badge-role border"
          :class="hotelStore.currentPersonaInfo.badgeClass"
          style="font-size: 0.65rem;"
        >
          {{ hotelStore.currentPersonaInfo.roleTitle }}
        </span>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <!-- Switch to Guest Portal -->
      <router-link to="/" class="d-none d-sm-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill btn btn-outline-success border small fw-bold text-decoration-none">
        <i class="bi bi-globe"></i>
        <span>Cổng khách hàng</span>
      </router-link>

      <!-- Staff Profile Dropdown -->
      <div class="dropdown">
        <button
          class="d-flex align-items-center gap-2 text-left bg-transparent border-0 p-1 rounded-xl user-toggle-btn dropdown-toggle"
          type="button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <div
            class="avatar-box rounded-circle text-xs fw-bold d-flex align-items-center justify-content-center shadow-sm text-white"
            :class="hotelStore.activePersona === 'an' ? 'bg-dark' : (hotelStore.activePersona === 'linh' ? 'bg-success' : (hotelStore.activePersona === 'huong' ? 'bg-warning text-dark' : 'bg-info text-dark'))"
          >
            {{ hotelStore.currentPersonaInfo.avatarText }}
          </div>
          <div class="d-none d-sm-block text-start">
            <div class="fw-bold text-main-dark lh-sm" style="font-size: 0.85rem;">
              {{ hotelStore.currentPersonaInfo.name }}
            </div>
            <div class="text-secondary-custom mt-0.5 lh-1" style="font-size: 0.7rem;">
              {{ hotelStore.currentPersonaInfo.roleTitle }}
            </div>
          </div>
        </button>

        <!-- Dropdown Menu: Chuyển đổi nhân sự theo Ảnh 1 -->
        <ul class="dropdown-menu dropdown-menu-end shadow-lg p-2 rounded-4 border-0 mt-2" style="width: 270px;">
          <li>
            <h6 class="dropdown-header text-uppercase fw-bold text-secondary-custom px-3 py-2" style="font-size: 0.7rem;">
              Chuyển đổi ca trực nhân sự (Ảnh 1):
            </h6>
          </li>

          <!-- 1. Trần An -->
          <li>
            <a
              @click.prevent="hotelStore.switchStaffPersona('an')"
              class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1 cursor-pointer"
              :class="hotelStore.activePersona === 'an' ? 'bg-dark text-white font-bold' : ''"
              href="#"
            >
              <span class="fw-semibold">👨‍💼 Trần An · Toàn quyền</span>
              <small :class="hotelStore.activePersona === 'an' ? 'text-light' : 'text-secondary-custom'" style="font-size: 0.75rem;">
                Chủ khách sạn (Admin 16 tabs)
              </small>
            </a>
          </li>

          <!-- 2. Mai Linh -->
          <li>
            <a
              @click.prevent="hotelStore.switchStaffPersona('linh')"
              class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1 cursor-pointer"
              :class="hotelStore.activePersona === 'linh' ? 'bg-success text-white font-bold' : ''"
              href="#"
            >
              <span class="fw-semibold">👱‍♀️ Mai Linh · Ca trực chính</span>
              <small :class="hotelStore.activePersona === 'linh' ? 'text-light' : 'text-secondary-custom'" style="font-size: 0.75rem;">
                Lễ tân đón tiếp & hóa đơn
              </small>
            </a>
          </li>

          <!-- 3. Nguyễn Hương -->
          <li>
            <a
              @click.prevent="hotelStore.switchStaffPersona('huong')"
              class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1 cursor-pointer"
              :class="hotelStore.activePersona === 'huong' ? 'bg-warning text-dark font-bold' : ''"
              href="#"
            >
              <span class="fw-semibold">🧹 Nguyễn Hương · Buồng phòng</span>
              <small class="text-muted" style="font-size: 0.75rem;">
                Tổ trưởng Buồng phòng & Minibar
              </small>
            </a>
          </li>

          <!-- 4. Lê Ngọc -->
          <li>
            <a
              @click.prevent="hotelStore.switchStaffPersona('ngoc')"
              class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1 cursor-pointer"
              :class="hotelStore.activePersona === 'ngoc' ? 'bg-info text-dark font-bold' : ''"
              href="#"
            >
              <span class="fw-semibold">📊 Lê Ngọc · Kế toán</span>
              <small class="text-muted" style="font-size: 0.75rem;">
                Kế toán, Hóa đơn & Thu ngân
              </small>
            </a>
          </li>

          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <router-link to="/" class="dropdown-item rounded-3 py-2 text-success fw-semibold d-flex align-items-center gap-2">
              <i class="bi bi-globe"></i>
              <span>Mở Cổng khách hàng →</span>
            </router-link>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <router-link to="/" class="dropdown-item rounded-3 py-2 text-danger d-flex align-items-center gap-2">
              <i class="bi bi-box-arrow-right"></i>
              <span>Thoát ca trực</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script>
import { useHotelStore } from '../../stores/hotelStore';

export default {
  name: 'HeaderAdmin',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  methods: {
    getTabBreadcrumb(tabId) {
      const map = {
        overview: 'Tổng quan ca trực',
        bookings: 'Quản lý đặt phòng',
        rooms: 'Sơ đồ trạng thái phòng',
        housekeeping: 'Buồng phòng & Nhiệm vụ',
        checkinout: 'Nhận & Trả phòng tại quầy',
        'service-requests': 'Yêu cầu dịch vụ phòng',
        invoices: 'Hóa đơn & Thu ngân',
        customers: 'Hồ sơ khách hàng',
        services: 'Danh mục dịch vụ',
        promotions: 'Chương trình khuyến mãi',
        reviews: 'Đánh giá & Phản hồi',
        reports: 'Báo cáo thống kê',
        'room-types': 'Cấu hình hạng phòng',
        minibar: 'Minibar & Tài sản phòng',
        staff: 'Nhân sự khách sạn',
        accounts: 'Tài khoản & Phân quyền',
      };
      return map[tabId] || 'Vận hành PMS';
    },
  },
};
</script>

<style scoped>
.app-header {
  height: 64px;
  border-color: #EAE8E1 !important;
  position: sticky;
  top: 0;
  z-index: 1020;
}

.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #7A857F;
}

.avatar-box {
  width: 34px;
  height: 34px;
  font-size: 0.85rem;
}

.cursor-pointer {
  cursor: pointer;
}
</style>
