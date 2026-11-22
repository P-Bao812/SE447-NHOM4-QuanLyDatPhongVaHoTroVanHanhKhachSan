<template>
  <div class="housekeeping-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
      <div>
        <h3 class="fw-bold text-navy mb-1">Phân hệ Buồng Phòng</h3>
        <p class="text-muted small mb-0">Quản lý dọn phòng, kiểm tra minibar và cơ sở vật chất</p>
      </div>
      <button class="btn btn-outline-danger btn-sm" @click="handleLogout">
        <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
      </button>
    </div>

    <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="avatar-lg bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fs-3">
          <i class="bi bi-house-gear-fill"></i>
        </div>
        <div>
          <h5 class="fw-bold text-navy mb-0">{{ currentUser.hoTen || currentUser.tenDangNhap || 'Nhân viên Buồng phòng' }}</h5>
          <span class="badge bg-warning text-dark">BUỒNG PHÒNG</span>
        </div>
      </div>
      <p class="text-muted mb-0">Danh sách phòng cần dọn dẹp và kiểm tra trạng thái buồng phòng.</p>
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

const handleLogout = () => {
  localStorage.removeItem('access_token')
  localStorage.removeItem('user')
  localStorage.removeItem('vaiTro')
  router.push('/login')
}
</script>

<style scoped>
.text-navy {
  color: #1e3a5f;
}
.avatar-lg {
  width: 52px;
  height: 52px;
}
</style>
