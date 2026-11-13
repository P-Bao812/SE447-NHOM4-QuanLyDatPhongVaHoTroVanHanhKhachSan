<template>
  <aside class="app-sidebar">
    <!-- Brand / Logo -->
    <div class="sidebar-brand">
      <router-link to="/" class="d-flex align-items-center gap-2 text-decoration-none">
        <div class="brand-icon">
          <i class="bi bi-buildings-fill text-white"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-white" style="font-size: 0.95rem;">RoomBook PMS</h5>
          <small class="text-muted-custom" style="font-size: 0.7rem;">Phân quyền theo vai trò</small>
        </div>
      </router-link>
    </div>

    <!-- Thông tin ca trực hiện tại (Dynamic theo Persona) -->
    <div class="shift-card">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-uppercase text-muted-custom" style="font-size: 0.65rem; letter-spacing: 0.5px;">Ca trực hiện tại:</span>
        <span class="badge text-uppercase" :class="hotelStore.currentPersonaInfo.badgeClass" style="font-size: 0.6rem; padding: 2px 6px;">
          {{ hotelStore.activePersona === 'an' ? 'ADMIN' : (hotelStore.activePersona === 'linh' ? 'LỄ TÂN' : (hotelStore.activePersona === 'huong' ? 'BUỒNG PHÒNG' : 'KẾ TOÁN')) }}
        </span>
      </div>
      <div class="fw-bold text-white d-flex align-items-center gap-1.5" style="font-size: 0.85rem;">
        <span>{{ hotelStore.activePersona === 'an' ? '👨‍💼' : (hotelStore.activePersona === 'linh' ? '👱‍♀️' : (hotelStore.activePersona === 'huong' ? '🧹' : '📊')) }}</span>
        <span>{{ hotelStore.currentPersonaInfo.name }}</span>
      </div>
      <small class="text-muted-custom d-block mt-1 lh-sm" style="font-size: 0.7rem;">
        {{ hotelStore.currentPersonaInfo.description }}
      </small>
    </div>

    <!-- Menu điều hướng (Tự động lọc theo quyền hạn Role Permissions) -->
    <div class="sidebar-menu">
      <!-- 1. Vận hành -->
      <div v-if="hasGroupItems('operations')" class="menu-category">Vận hành</div>
      <a
        v-if="canAccess('overview')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('overview')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'overview' }"
      >
        <i class="bi bi-grid-1x2"></i> Tổng quan ca trực
      </a>
      <a
        v-if="canAccess('bookings')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('bookings')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'bookings' }"
      >
        <i class="bi bi-calendar-check"></i> Đặt phòng
      </a>
      <a
        v-if="canAccess('rooms')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('rooms')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'rooms' }"
      >
        <i class="bi bi-map"></i> Sơ đồ phòng
      </a>
      <a
        v-if="canAccess('housekeeping')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('housekeeping')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'housekeeping' }"
      >
        <i class="bi bi-house-door"></i> Buồng phòng & Nhiệm vụ
      </a>
      <a
        v-if="canAccess('checkinout')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('checkinout')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'checkinout' }"
      >
        <i class="bi bi-arrow-left-right"></i> Nhận & trả phòng
      </a>
      <a
        v-if="canAccess('service-requests')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('service-requests')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'service-requests' }"
      >
        <i class="bi bi-bell"></i> Yêu cầu dịch vụ ({{ hotelStore.serviceRequests.length }})
      </a>

      <!-- 2. Kinh doanh & Tài chính -->
      <div v-if="hasGroupItems('business')" class="menu-category mt-3">Kinh doanh & Tài chính</div>
      <a
        v-if="canAccess('invoices')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('invoices')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'invoices' }"
      >
        <i class="bi bi-receipt"></i> Hóa đơn & Thu ngân
      </a>
      <a
        v-if="canAccess('customers')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('customers')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'customers' }"
      >
        <i class="bi bi-people"></i> Hồ sơ khách hàng
      </a>
      <a
        v-if="canAccess('services')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('services')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'services' }"
      >
        <i class="bi bi-cup-hot"></i> Danh mục dịch vụ
      </a>
      <a
        v-if="canAccess('promotions')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('promotions')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'promotions' }"
      >
        <i class="bi bi-tags"></i> Khuyến mãi & Voucher
      </a>
      <a
        v-if="canAccess('reviews')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('reviews')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'reviews' }"
      >
        <i class="bi bi-star"></i> Đánh giá khách hàng
      </a>
      <a
        v-if="canAccess('reports')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('reports')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'reports' }"
      >
        <i class="bi bi-graph-up-arrow"></i> Báo cáo thống kê
      </a>

      <!-- 3. Quản trị & Cấu hình -->
      <div v-if="hasGroupItems('admin')" class="menu-category mt-3">Quản trị & Cấu hình</div>
      <a
        v-if="canAccess('room-types')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('room-types')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'room-types' }"
      >
        <i class="bi bi-sliders"></i> Cấu hình hạng phòng
      </a>
      <a
        v-if="canAccess('minibar')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('minibar')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'minibar' }"
      >
        <i class="bi bi-box-seam"></i> Minibar & tài sản
      </a>
      <a
        v-if="canAccess('staff')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('staff')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'staff' }"
      >
        <i class="bi bi-person-badge"></i> Nhân sự khách sạn
      </a>
      <a
        v-if="canAccess('accounts')"
        href="#"
        @click.prevent="hotelStore.setActivePmsTab('accounts')"
        class="menu-item"
        :class="{ active: hotelStore.activePmsTab === 'accounts' }"
      >
        <i class="bi bi-shield-check"></i> Tài khoản & Phân quyền
      </a>
    </div>

    <!-- Footer Sidebar -->
    <div class="sidebar-footer">
      <router-link to="/" class="btn btn-sidebar-action w-100 mb-2 text-start d-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-globe"></i> Mở Cổng khách hàng
      </router-link>
      <button @click="$router.push('/')" class="btn btn-sidebar-danger w-100 text-start d-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-right"></i> Thoát ca trực
      </button>
    </div>
  </aside>
</template>

<script>
import { useHotelStore } from '../../stores/hotelStore';

export default {
  name: 'SidebarAdmin',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  methods: {
    canAccess(tabId) {
      const allowed = this.hotelStore.currentPersonaInfo?.allowedTabs || [];
      return allowed.includes(tabId);
    },
    hasGroupItems(group) {
      const ops = ['overview', 'bookings', 'rooms', 'housekeeping', 'checkinout', 'service-requests'];
      const biz = ['invoices', 'customers', 'services', 'promotions', 'reviews', 'reports'];
      const adm = ['room-types', 'minibar', 'staff', 'accounts'];

      const allowed = this.hotelStore.currentPersonaInfo?.allowedTabs || [];
      if (group === 'operations') return ops.some(t => allowed.includes(t));
      if (group === 'business') return biz.some(t => allowed.includes(t));
      if (group === 'admin') return adm.some(t => allowed.includes(t));
      return true;
    },
  },
};
</script>

<style scoped>
.app-sidebar {
  background-color: #172620;
  color: #fff;
  display: flex;
  flex-direction: column;
}

.sidebar-brand {
  padding: 1.25rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-icon {
  width: 34px;
  height: 34px;
  background-color: #1E5E49;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.shift-card {
  margin: 1rem;
  padding: 0.75rem;
  background-color: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-menu {
  flex: 1;
  padding: 0.5rem 0.75rem;
  overflow-y: auto;
}

.menu-category {
  font-size: 0.65rem;
  text-transform: uppercase;
  color: #7A857F;
  letter-spacing: 0.6px;
  font-weight: 700;
  padding: 0.5rem 0.75rem 0.25rem;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  color: #A3B0AA;
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  border-radius: 8px;
  margin-bottom: 2px;
  transition: all 0.2s ease;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.menu-item.active {
  background-color: #1E5E49;
  color: #ffffff;
  font-weight: 600;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-sidebar-action {
  background-color: rgba(255, 255, 255, 0.08);
  color: #A3B0AA;
  border: none;
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 7px 12px;
}

.btn-sidebar-action:hover {
  background-color: rgba(255, 255, 255, 0.15);
  color: #ffffff;
}

.btn-sidebar-danger {
  background-color: transparent;
  color: #e76f51;
  border: none;
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 7px 12px;
}

.btn-sidebar-danger:hover {
  background-color: rgba(231, 111, 81, 0.1);
}

.text-muted-custom {
  color: #7A857F;
}
</style>
