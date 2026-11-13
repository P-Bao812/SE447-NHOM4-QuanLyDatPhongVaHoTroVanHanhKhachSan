<template>
  <div class="admin-layout">
    <!-- Sidebar nằm bên trái, cố định chiều cao -->
    <SidebarAdmin class="app-sidebar" />

    <!-- Header nằm ở cột phải, hàng trên cùng -->
    <HeaderAmind class="app-header" />

    <!-- Nội dung thay đổi theo đường dẫn -->
    <main class="app-content">
      <router-view />
    </main>
  </div>
</template>
<script>
import HeaderAmind from '../components/HeaderAdmin.vue';
import SidebarAdmin from '../components/SidebarAdmin.vue';
export default {
  components: {
    HeaderAmind,
    SidebarAdmin
  }
}
</script>
<style>
.admin-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  /* Cố định sidebar 260px, phần còn lại cho nội dung */
  grid-template-rows: auto 1fr auto;
  /* Hàng 1: Header, Hàng 2: Nội dung, Hàng 3: Footer */
  min-height: 100vh;
  background-color: #f8f9fa;
}

/* 1. Sidebar: Chiếm toàn bộ cột 1, kéo dài từ hàng đầu đến cuối và dính màn hình */
.app-sidebar {
  grid-column: 1 / 2;
  grid-row: 1 / 4;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 1040;
}

/* 2. Header: Nằm ở cột 2, hàng 1 (bên phải sidebar) */
.app-header {
  grid-column: 2 / 3;
  grid-row: 1 / 2;
}

/* 3. Nội dung (router-view): Nằm ở cột 2, hàng 2 (dưới header) */
.app-content {
  grid-column: 2 / 3;
  grid-row: 2 / 3;
  padding: 24px;
}

/* 4. Footer: Nằm ở cột 2, hàng 3 */
.app-footer {
  grid-column: 2 / 3;
  grid-row: 3 / 4;
}
</style>
