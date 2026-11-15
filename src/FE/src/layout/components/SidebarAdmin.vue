<template>
  <aside class="app-sidebar">
    <!-- Brand / Logo -->
    <div class="sidebar-brand">
      <div class="d-flex align-items-center gap-2">
        <div class="brand-icon">
          <i class="bi bi-buildings-fill text-white"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-bold text-white" style="font-size: 0.95rem;">RoomBook PMS</h5>
          <small class="text-muted-custom" style="font-size: 0.7rem;">Phân quyền theo vai trò</small>
        </div>
      </div>
    </div>

    <!-- Thông tin ca trực hiện tại -->
    <div class="shift-card">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="text-uppercase text-muted-custom" style="font-size: 0.65rem; letter-spacing: 0.5px;">Ca trực hiện
          tại:</span>
        <span class="badge bg-success-custom text-white" style="font-size: 0.6rem; padding: 2px 6px;">LỄ TÂN</span>
      </div>
      <div class="fw-bold text-white d-flex align-items-center gap-1.5" style="font-size: 0.85rem;">
        <span>👩‍💼</span> Mai Linh
      </div>
      <small class="text-muted-custom d-block mt-1 lh-sm" style="font-size: 0.7rem;">
        Quản lý đón tiếp, đặt phòng, nhận/trả phòng & hóa đơn quầy.
      </small>
    </div>

    <!-- Menu điều hướng -->
    <div class="sidebar-menu">
      <div class="menu-category">Vận hành</div>
      <a href="#" class="menu-item active">
        <i class="bi bi-grid-1x2"></i> Tổng quan
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-calendar-check"></i> Đặt phòng
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-map"></i> Sơ đồ phòng
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-house-door"></i> Buồng phòng
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-arrow-left-right"></i> Nhận & trả phòng
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-bell"></i> Yêu cầu dịch vụ
      </a>

      <div class="menu-category mt-3">Kinh doanh</div>
      <a href="#" class="menu-item">
        <i class="bi bi-receipt"></i> Hóa đơn & thanh toán
      </a>
      <a href="#" class="menu-item">
        <i class="bi bi-people"></i> Khách hàng
      </a>

      <div class="menu-category mt-3">Quản trị</div>
      <a href="#" class="menu-item">
        <i class="bi bi-box-seam"></i> Minibar & tài sản
      </a>
    </div>

    <!-- Footer Sidebar: Nút mở cổng khách hàng & đăng xuất ca -->
    <div class="sidebar-footer">
      <button class="btn btn-sidebar-action w-100 mb-2 text-start d-flex align-items-center gap-2">
        <i class="bi bi-globe"></i> Mở Cổng khách hàng
      </button>
      <button class="btn btn-sidebar-danger w-100 text-start d-flex align-items-center gap-2">
        <i class="bi bi-box-arrow-right"></i> Đăng xuất ca trực
      </button>
    </div>
  </aside>
</template>
<script>
export default {

}
</script>
<style>
.app-sidebar {
  width: 260px;
  background-color: #0d231e;
  /* Màu xanh tối đặc trưng của khách sạn */
  color: #fff;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: sticky;
  top: 0;
  overflow-y: auto;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-brand {
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-icon {
  width: 32px;
  height: 32px;
  background-color: #163830;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.shift-card {
  margin: 12px;
  padding: 10px 12px;
  background-color: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 10px;
}

.text-muted-custom {
  color: #8da298;
}

.bg-success-custom {
  background-color: #1e5e49;
}

.sidebar-menu {
  flex: 1;
  padding: 8px 12px;
}

.menu-category {
  font-size: 0.65rem;
  text-transform: uppercase;
  color: #728a7e;
  margin-bottom: 6px;
  margin-top: 12px;
  padding-left: 8px;
  letter-spacing: 0.5px;
  font-weight: 700;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 9px 12px;
  color: #b0c2b9;
  text-decoration: none;
  border-radius: 8px;
  margin-bottom: 2px;
  font-size: 0.85rem;
  transition: all 0.15s ease;
}

.menu-item i {
  margin-right: 10px;
  font-size: 1rem;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.menu-item.active {
  background-color: #163830;
  color: #ffffff;
  font-weight: 600;
}

.sidebar-footer {
  padding: 12px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background-color: rgba(0, 0, 0, 0.1);
}

.btn-sidebar-action {
  background-color: rgba(255, 255, 255, 0.06);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 8px 12px;
  transition: background-color 0.15s;
}

.btn-sidebar-action:hover {
  background-color: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.btn-sidebar-danger {
  background-color: transparent;
  color: #ff8b8b;
  border: 1px solid transparent;
  font-size: 0.8rem;
  border-radius: 8px;
  padding: 8px 12px;
  transition: all 0.15s;
}

.btn-sidebar-danger:hover {
  background-color: rgba(255, 75, 75, 0.1);
  color: #ff6b6b;
}
</style>
