<template>
  <div class="guest-home py-5">
    <div class="container text-center py-5">
      <h1 class="display-5 fw-bold text-navy mb-3">Chào mừng đến với RoomBook Hotel & Resort</h1>
      <p class="lead text-muted mb-4">Trải nghiệm kỳ nghỉ đẳng cấp quốc tế tại Hội An với dịch vụ hoàn hảo</p>
      <div class="d-flex justify-content-center gap-3">
        <router-link to="/booking" class="btn btn-teal px-4 py-2 rounded-3 text-white fw-semibold">
          <i class="bi bi-calendar2-check-fill me-1"></i> Đặt phòng ngay
        </router-link>
        <router-link to="/login" class="btn btn-navy px-4 py-2 rounded-3 text-white">
          <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập hệ thống
        </router-link>
        <router-link to="/services" class="btn btn-outline-secondary px-4 py-2 rounded-3">
          Khám phá dịch vụ
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.text-navy {
  color: #1e3a5f;
}
.btn-navy {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
}
.btn-navy:hover {
  background-color: #152943;
}
.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
}
.btn-teal:hover {
  background-color: #115e59;
}
</style>
