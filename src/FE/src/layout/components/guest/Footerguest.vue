<template>
  <footer class="guest-footer bg-white border-top py-4">
    <div class="container-fluid px-3 px-lg-5">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <!-- Cột trái: Thông tin thương hiệu & bản quyền -->
        <div>
          <div class="fw-bold text-main-dark" style="font-size: 0.9rem;">
            RoomBook Hotel & Resort
          </div>
          <div class="text-secondary-custom mt-1" style="font-size: 0.8rem;">
            Hệ thống đặt phòng trực tuyến dành riêng cho khách hàng · Bản quyền © 2026 RoomBook.
          </div>
        </div>

        <!-- Cột phải: Hotline & Link chuyển sang cổng quản trị -->
        <div class="d-flex flex-wrap align-items-center gap-3" style="font-size: 0.85rem;">
          <span class="text-secondary-custom">
            Hotline: <strong class="text-main-dark fw-semibold">0905 888 999</strong>
          </span>
          <span class="text-secondary-custom d-none d-sm-inline">•</span>
          <router-link to="/customer"
            class="staff-portal-link d-inline-flex align-items-center gap-1.5 text-decoration-none fw-semibold">
            <i class="bi bi-lock"></i>
            <span>Cổng nhân viên & Quản lý PMS</span>
          </router-link>
        </div>
      </div>
    </div>
  </footer>
</template>
<script>
export default {

}
</script>
<style>
.guest-footer {
  border-color: #EAE8E1 !important;
}

.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #7A857F;
}

.staff-portal-link {
  color: #1E5E49;
  transition: opacity 0.2s;
}

.staff-portal-link:hover {
  opacity: 0.8;
  text-decoration: underline !important;
}
</style>
