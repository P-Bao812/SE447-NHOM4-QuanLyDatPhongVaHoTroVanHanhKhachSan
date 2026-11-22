<template>
  <div class="payment-page py-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <div class="avatar-lg bg-teal-subtle text-teal rounded-circle d-flex align-items-center justify-content-center fs-3">
                <i class="bi bi-credit-card-2-front-fill"></i>
              </div>
              <div>
                <h3 class="fw-bold text-navy mb-0">Thanh toán tiền cọc trực tuyến</h3>
                <p class="text-muted small mb-0">Cổng thanh toán an toàn qua mã QR và ngân hàng điện tử (UC04 / UC16 / UC21)</p>
              </div>
            </div>

            <!-- Nếu có thông tin đơn đặt phòng từ query -->
            <div v-if="maDatPhong" class="alert alert-info rounded-3 p-3 mb-4">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <h6 class="fw-bold text-navy mb-1">
                    <i class="bi bi-bookmark-check me-1"></i> Đơn đặt phòng: #{{ maDatPhong }}
                  </h6>
                  <span class="badge bg-warning text-dark">Trạng thái: Chờ thanh toán cọc</span>
                </div>
                <div class="text-end">
                  <div class="small text-muted">Số tiền cọc cần thanh toán:</div>
                  <strong class="fs-4 text-danger">{{ formatCurrency(tienCoc) }}</strong>
                </div>
              </div>
            </div>

            <div class="row g-4">
              <!-- Cột trái: QR Code demo -->
              <div class="col-md-6 text-center border-end">
                <div class="p-3 bg-light rounded-4 border d-inline-block mb-3">
                  <div class="qr-placeholder d-flex flex-column align-items-center justify-content-center bg-white p-3 rounded-3 shadow-sm" style="width: 200px; height: 200px; margin: 0 auto;">
                    <i class="bi bi-qr-code text-navy" style="font-size: 8rem; line-height: 1;"></i>
                  </div>
                </div>
                <h6 class="fw-bold text-navy mb-1">Quét mã VietQR để thanh toán</h6>
                <p class="text-muted small mb-0">Hỗ trợ tất cả ứng dụng Ngân hàng & Ví điện tử</p>
              </div>

              <!-- Cột phải: Thông tin chuyển khoản -->
              <div class="col-md-6">
                <h6 class="fw-bold text-navy mb-3">Thông tin tài khoản thụ hưởng:</h6>
                <ul class="list-group list-group-flush small mb-3">
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">Ngân hàng:</span>
                    <strong class="text-navy">Vietcombank (Hội An)</strong>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">Số tài khoản:</span>
                    <strong class="text-navy font-monospace">1028889999</strong>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">Chủ tài khoản:</span>
                    <strong class="text-navy">ROOMBOOK HOTEL & RESORT</strong>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">Số tiền:</span>
                    <strong class="text-danger">{{ formatCurrency(tienCoc || 500000) }}</strong>
                  </li>
                  <li class="list-group-item px-0 d-flex justify-content-between">
                    <span class="text-muted">Nội dung CK:</span>
                    <strong class="text-teal font-monospace">COC DP {{ maDatPhong || '101' }}</strong>
                  </li>
                </ul>

                <div class="alert alert-warning py-2 px-3 rounded-3 small">
                  <i class="bi bi-info-circle me-1"></i>
                  Hệ thống sẽ tự động cập nhật trạng thái đơn thành <strong>Đã xác nhận</strong> ngay khi nhận được biến động số dư.
                </div>
              </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
              <router-link to="/booking" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Quay lại đặt phòng
              </router-link>
              <router-link to="/my-bookings" class="btn btn-navy text-white rounded-3 px-4">
                <i class="bi bi-journal-text me-1"></i> Xem đơn của tôi
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const maDatPhong = computed(() => route.query.maDatPhong || null)
const tienCoc = computed(() => Number(route.query.tienCoc) || 0)

const formatCurrency = (val) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0)
}
</script>

<style scoped>
.text-navy {
  color: #1e3a5f;
}
.text-teal {
  color: #0f766e;
}
.bg-teal-subtle {
  background-color: #ccfbf1;
}
.btn-navy {
  background-color: #1e3a5f;
  border-color: #1e3a5f;
}
.btn-navy:hover {
  background-color: #162a45;
}
.avatar-lg {
  width: 52px;
  height: 52px;
}
</style>
