<template>
  <div class="frontdesk-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
      <div>
        <h3 class="fw-bold text-navy mb-1">Bàn Trực Lễ Tân (Front Desk)</h3>
        <p class="text-muted small mb-0">Quản lý nhận/trả phòng, sơ đồ phòng và hóa đơn</p>
      </div>
    </div>

    <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div
          class="avatar-lg bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center fs-3">
          <i class="bi bi-bell-fill"></i>
        </div>
        <div>
          <h5 class="fw-bold text-navy mb-0">Ca trực của: {{ currentUser.hoTen || currentUser.tenDangNhap || 'Lễ tân' }}
          </h5>
          <span class="badge bg-success">LỄ TÂN - CA TRỰC CHÍNH</span>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top mt-3">
        <p class="text-muted mb-0">Chào mừng bạn đến với ca trực. Hệ thống sẵn sàng tiếp nhận đặt phòng và
          check-in/check-out.</p>
        <div class="d-flex gap-2">
          <router-link to="/receptionist/cancellations" class="btn btn-outline-navy rounded-3 px-3 py-2 fw-semibold">
            <i class="bi bi-calendar-x me-1"></i> Theo dõi & Xử lý hủy
          </router-link>
          <router-link to="/receptionist/booking" class="btn btn-teal text-white rounded-3 px-3 py-2 fw-semibold">
            <i class="bi bi-calendar-plus me-1"></i> Tạo đơn đặt phòng mới
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentUser = ref({})

onMounted(() => {
  try {
    currentUser.value = JSON.parse(localStorage.getItem('user') || '{}')
  } catch (e) {
    currentUser.value = {}
  }
})

</script>

<style scoped>
.text-navy {
  color: #1e3a5f;
}

.btn-outline-navy {
  color: #1e3a5f;
  border-color: #1e3a5f;
}

.btn-outline-navy:hover {
  background-color: #1e3a5f;
  color: #ffffff;
}

.text-teal {
  color: #0f766e;
}

.avatar-lg {
  width: 52px;
  height: 52px;
}

.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
}

.btn-teal:hover {
  background-color: #115e59;
}
</style>
