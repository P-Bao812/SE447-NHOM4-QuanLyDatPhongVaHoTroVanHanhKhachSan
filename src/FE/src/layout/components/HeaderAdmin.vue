<template>
  <header class="app-header">
    <!-- Breadcrumb & Role Badge -->
    <div class="d-flex align-items-center gap-2 text-truncate">
      <span class="text-secondary-custom">Phân hệ PMS</span>
      <i class="bi bi-chevron-right text-secondary-custom fs-7"></i>
      <span class="fw-bold text-main-dark text-truncate">
        Tổng quan
      </span>>
      <div>
        <span
          class="d-none d-md-inline-flex px-2.5 py-1 rounded-pill text-uppercase fw-bold badge-role bg-light text-success border">
          LỄ TÂN - CA TRỰC CHÍNH
        </span>
      </div>
    </div>

    <div class="d-flex align-items-center gap-3">
      <!-- Switch to Guest Portal -->
      <router-link to="/" class="d-none d-sm-inline-flex align-items-center gap-2 px-3 py-2 rounded-xl btn-guest border-0 text-decoration-none">
        <i class="bi bi-globe"></i>
        <span>Cổng khách hàng</span>
      </router-link>

      <!-- Staff Profile Dropdown (Sử dụng cấu trúc click cơ bản hoặc tự binding tùy ý) -->
      <div class="dropdown">
        <button
          class="d-flex align-items-center gap-2 text-left bg-transparent border-0 p-1 rounded-xl user-toggle-btn dropdown-toggle"
          type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div
            class="avatar-box rounded-circle text-xs fw-bold d-flex align-items-center justify-content-center shadow-sm bg-success text-white">
            ML
          </div>
          <div class="d-none d-sm-block text-start">
            <div class="fw-bold text-main-dark lh-sm" style="font-size: 0.85rem;">
              Mai Linh
            </div>
            <div class="text-secondary-custom mt-0.5 lh-1" style="font-size: 0.7rem;">
              Lễ tân khách sạn
            </div>
          </div>
        </button>

        <!-- Dropdown Menu -->
        <ul class="dropdown-menu dropdown-menu-end shadow-lg p-2 rounded-4 border-0 mt-2" style="width: 260px;">
          <li>
            <h6 class="dropdown-header text-uppercase fw-bold text-secondary-custom px-3 py-2"
              style="font-size: 0.7rem;">Chuyển đổi nhân sự trực ca:</h6>
          </li>

          <li>
            <a class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1" href="#">
              <span class="fw-semibold text-main-dark">Trần An · Toàn quyền</span>
              <small class="text-secondary-custom" style="font-size: 0.75rem;">Chủ khách sạn (Admin)</small>
            </a>
          </li>
          <li>
            <a class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1 bg-success text-white" href="#">
              <span class="fw-semibold">Mai Linh · Ca chính</span>
              <small class="text-light" style="font-size: 0.75rem;">Lễ tân khách sạn</small>
            </a>
          </li>
          <li>
            <a class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1" href="#">
              <span class="fw-semibold text-main-dark">Nguyễn Hương · Buồng phòng</span>
              <small class="text-secondary-custom" style="font-size: 0.75rem;">Tổ trưởng Buồng phòng</small>
            </a>
          </li>
          <li>
            <a class="dropdown-item rounded-3 py-2 d-flex flex-column mb-1" href="#">
              <span class="fw-semibold text-main-dark">Lê Ngọc · Kế toán</span>
              <small class="text-secondary-custom" style="font-size: 0.75rem;">Kế toán & Thu ngân</small>
            </a>
          </li>

          <li>
            <hr class="dropdown-divider my-1">
          </li>
          <li>
            <a class="dropdown-item rounded-3 py-2 text-success fw-semibold d-flex align-items-center gap-2" href="#">
              <i class="bi bi-globe"></i>
              <span>Mở Cổng khách hàng →</span>
            </a>
          </li>
          <li>
            <hr class="dropdown-divider my-1">
          </li>
          <li>
            <a class="dropdown-item rounded-3 py-2 text-danger d-flex align-items-center gap-2" href="#">
              <i class="bi bi-box-arrow-right"></i>
              <span>Đăng xuất ca trực</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>
<script>
export default {

}
</script>
<style>
.app-header {
  height: 64px;
  background-color: #ffffff;
  border-bottom: 1px solid #EAE8E1;
  padding: 0 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 30;
}

@media (min-width: 640px) {
  .app-header {
    padding: 0 32px;
  }
}

.text-secondary-custom {
  color: #7A857F;
}

.text-main-dark {
  color: #172620;
}

.badge-role {
  background-color: #f1f3f2;
  color: #172620;
  font-size: 10px;
  letter-spacing: 0.05em;
}

.btn-guest {
  background-color: #EAF3EE;
  color: #1E5E49;
  font-size: 0.75rem;
  font-weight: 600;
  transition: background-color 0.2s;
  border-radius: 12px;
}

.btn-guest:hover {
  background-color: #DCECE4;
}

.user-toggle-btn:hover {
  background-color: #FAF9F5;
}

.avatar-box {
  width: 32px;
  height: 32px;
}
</style>
