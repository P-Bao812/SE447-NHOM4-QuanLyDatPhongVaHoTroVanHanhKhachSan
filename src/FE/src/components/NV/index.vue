<template>
  <div class="pms-dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
      <div>
        <h3 class="fw-bold text-navy mb-1">Bảng điều khiển Quản lý</h3>
        <p class="text-muted small mb-0">Hệ thống Quản lý Khách sạn RoomBook PMS</p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" @click="handleLogout">
          <i class="bi bi-box-arrow-right"></i>
          <span>Đăng xuất</span>
        </button>
      </div>
    </div>

    <!-- Thông tin nhân viên đăng nhập -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3 p-3 bg-white">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar-lg bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center fs-3">
              <i class="bi bi-person-badge"></i>
            </div>
            <div>
              <div class="text-muted small">Tài khoản hiện tại</div>
              <div class="fw-bold text-navy fs-5">{{ currentUser.hoTen || currentUser.tenDangNhap || 'Admin' }}</div>
              <span class="badge bg-success-subtle text-success border border-success-subtle">
                {{ currentUser.vaiTro || 'Quản lý' }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0 rounded-3 p-3 bg-white">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fs-3">
              <i class="bi bi-shield-check"></i>
            </div>
            <div>
              <div class="text-muted small">Phiên đăng nhập</div>
              <div class="fw-semibold text-truncate small" style="max-width: 200px;">
                {{ token ? 'Bearer Active' : 'Chưa có token' }}
              </div>
              <span class="badge bg-primary">Đã xác thực Sanctum</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Danh sách module PMS -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
      <h5 class="fw-bold text-navy mb-3">Các phân hệ nghiệp vụ PMS</h5>
      <div class="row g-3">
        <div class="col-6 col-md-4 col-lg-3">
          <router-link to="/admin/bookings" class="text-decoration-none">
            <div class="p-3 border rounded-3 text-center bg-light text-navy hover-card">
              <i class="bi bi-calendar-check fs-2 text-teal"></i>
              <div class="fw-semibold mt-2 small">Quản lý Đặt phòng</div>
            </div>
          </router-link>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <router-link to="/admin/reports" class="text-decoration-none">
            <div class="p-3 border rounded-3 text-center bg-light text-navy hover-card">
              <i class="bi bi-graph-up-arrow fs-2 text-danger"></i>
              <div class="fw-semibold mt-2 small">Báo cáo & Thống kê</div>
            </div>
          </router-link>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <router-link to="/admin/check-in-out" class="text-decoration-none">
            <div class="p-3 border rounded-3 text-center bg-light text-navy hover-card">
              <i class="bi bi-arrow-left-right fs-2 text-primary"></i>
              <div class="fw-semibold mt-2 small">Nhận/Trả phòng</div>
            </div>
          </router-link>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <router-link to="/admin/room-map" class="text-decoration-none">
            <div class="p-3 border rounded-3 text-center bg-light text-navy hover-card">
              <i class="bi bi-door-closed fs-2 text-warning"></i>
              <div class="fw-semibold mt-2 small">Sơ đồ phòng</div>
            </div>
          </router-link>
        </div>
        <div class="col-6 col-md-4 col-lg-3">
          <router-link to="/housekeeping/room-map" class="text-decoration-none">
            <div class="p-3 border rounded-3 text-center bg-light text-navy hover-card">
              <i class="bi bi-house-door fs-2 text-success"></i>
              <div class="fw-semibold mt-2 small">Buồng phòng</div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const currentUser = ref({})
const token = ref('')

onMounted(() => {
  token.value = localStorage.getItem('access_token') || ''
  try {
    currentUser.value = JSON.parse(localStorage.getItem('user') || '{}')
  } catch (e) {
    currentUser.value = {}
  }
})

const handleLogout = async () => {
  try {
    const apiBase = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000'
    if (token.value) {
      await axios.post(
        `${apiBase}/api/v1/auth/logout`,
        {},
        {
          headers: { Authorization: `Bearer ${token.value}` },
        }
      )
    }
  } catch (e) {
    console.error('Logout error:', e)
  } finally {
    localStorage.removeItem('access_token')
    localStorage.removeItem('token_type')
    localStorage.removeItem('user')
    localStorage.removeItem('vaiTro')
    router.push('/login')
  }
}
</script>

<style scoped>
.text-navy {
  color: #1e3a5f;
}
.text-teal {
  color: #0f766e;
}
.avatar-lg {
  width: 52px;
  height: 52px;
}
</style>
