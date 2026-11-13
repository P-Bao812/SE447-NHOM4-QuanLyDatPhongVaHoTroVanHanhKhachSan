<template>
  <div class="guest-bookings-container py-4">
    <div class="container-fluid px-3 px-lg-5 max-w-5xl mx-auto">
      <!-- 1. Header -->
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="fw-bold text-main-dark mb-1">Lịch sử đặt phòng của tôi</h2>
          <p class="text-secondary-custom small mb-0">
            Theo dõi chi tiết tình trạng phòng, mã vé lưu trú điện tử và chính sách hủy phòng tại RoomBook.
          </p>
        </div>
        <router-link to="/" class="btn btn-outline-success rounded-pill px-3 py-2 small fw-bold d-flex align-items-center gap-1.5">
          <i class="bi bi-plus-lg"></i>
          <span>Đặt thêm phòng mới</span>
        </router-link>
      </div>

      <!-- 2. Danh sách đơn đặt phòng -->
      <div v-if="hotelStore.myGuestBookings.length > 0" class="space-y-4">
        <div
          v-for="b in hotelStore.myGuestBookings"
          :key="b.id"
          class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white"
        >
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 border-bottom pb-3 mb-3">
            <div>
              <div class="d-flex align-items-center gap-2">
                <span class="fs-5 fw-bold text-main-dark font-monospace">{{ b.id }}</span>
                <span class="badge px-3 py-1 rounded-pill" :class="getStatusBadgeClass(b.status)">
                  ● {{ getStatusLabel(b.status) }}
                </span>
              </div>
              <small class="text-muted">Họ và tên khách: <strong>{{ b.guestName }}</strong> · SĐT: {{ b.phone }}</small>
            </div>

            <div class="text-md-end">
              <span class="text-muted small">Tổng thanh toán:</span>
              <div class="fs-4 fw-bold text-success-custom">{{ formatVND(b.totalAmount) }}</div>
            </div>
          </div>

          <!-- Thông tin lưu trú & QR Code check-in -->
          <div class="row g-4 align-items-center">
            <div class="col-12 col-md-8">
              <div class="row g-3">
                <div class="col-6 col-sm-4">
                  <div class="p-3 bg-light rounded-3">
                    <small class="text-muted d-block">Hạng phòng</small>
                    <div class="fw-bold text-main-dark mt-1">{{ b.roomType }} Room</div>
                    <small class="text-muted">Phòng: <strong>{{ b.roomNumber }}</strong></small>
                  </div>
                </div>

                <div class="col-6 col-sm-4">
                  <div class="p-3 bg-light rounded-3">
                    <small class="text-muted d-block">Thời gian lưu trú</small>
                    <div class="fw-bold text-main-dark mt-1">{{ b.checkIn }} - {{ b.checkOut }}</div>
                    <small class="text-muted">Thời lượng: {{ b.nights }} đêm</small>
                  </div>
                </div>

                <div class="col-12 col-sm-4">
                  <div class="p-3 bg-light rounded-3">
                    <small class="text-muted d-block">Tình trạng thanh toán</small>
                    <div class="fw-bold text-success-custom mt-1 small">{{ b.paymentStatus || 'Đã thanh toán' }}</div>
                    <small class="text-muted">Check-in: 14:00</small>
                  </div>
                </div>
              </div>

              <!-- Thao tác phụ -->
              <div class="mt-3 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="small text-muted">
                  <i class="bi bi-info-circle text-primary me-1"></i>
                  Xuất trình mã này tại quầy lễ tân để nhận chìa khóa phòng nhanh.
                </span>

                <button
                  v-if="b.status === 'confirmed'"
                  @click="handleCancel(b.id)"
                  class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold"
                >
                  <i class="bi bi-x-circle me-1"></i> Hủy đặt phòng này
                </button>
              </div>
            </div>

            <!-- Mã QR Vé Lưu Trú Điện Tử -->
            <div class="col-12 col-md-4 text-center border-start-md">
              <div class="p-3 bg-light rounded-3 border d-inline-block">
                <img
                  :src="`https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=${b.id}`"
                  alt="QR Check-in"
                  class="rounded-2 shadow-xs mb-2"
                  style="width: 100px; height: 100px;"
                />
                <div class="small fw-bold text-main-dark font-monospace">{{ b.id }}</div>
                <div class="small text-muted" style="font-size: 0.7rem;">QR Nhận phòng nhanh</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Khi chưa có đặt phòng nào -->
      <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
        <div class="fs-1 text-muted mb-2">📭</div>
        <h4 class="fw-bold text-main-dark">Bạn chưa có đơn đặt phòng nào</h4>
        <p class="text-muted small mb-4">Hãy khám phá các hạng phòng nghỉ dưỡng tuyệt đẹp bên dòng sông Hoài tại RoomBook ngay hôm nay!</p>
        <div>
          <router-link to="/" class="btn btn-success-custom text-white px-4 py-2 rounded-pill fw-bold">
            Xem phòng nghỉ & Đặt ngay
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHotelStore } from '../../stores/hotelStore';

export default {
  name: 'LichSuDatPhong',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  methods: {
    formatVND(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    getStatusBadgeClass(status) {
      const map = {
        confirmed: 'bg-primary-subtle text-primary',
        staying: 'bg-success-subtle text-success-custom',
        checked_out: 'bg-secondary-subtle text-secondary',
        cancelled: 'bg-danger-subtle text-danger',
      };
      return map[status] || 'bg-light text-dark';
    },
    getStatusLabel(status) {
      const map = {
        confirmed: 'Đã xác nhận đặt chỗ',
        staying: 'Đang lưu trú tại khách sạn',
        checked_out: 'Đã hoàn tất trả phòng',
        cancelled: 'Đã hủy',
      };
      return map[status] || status;
    },
    handleCancel(bookingId) {
      if (confirm(`Quý khách có chắc chắn muốn hủy đặt phòng ${bookingId}?`)) {
        this.hotelStore.cancelGuestBooking(bookingId);
        alert(`Đã hủy thành công đơn đặt phòng ${bookingId}.`);
      }
    },
  },
};
</script>

<style scoped>
.guest-bookings-container {
  background-color: #F8F9FA;
  min-height: calc(100vh - 76px);
}

.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #5C6862;
}

.text-success-custom {
  color: #1E5E49;
}

.btn-success-custom {
  background-color: #1E5E49;
  border-color: #1E5E49;
}

@media (min-width: 768px) {
  .border-start-md {
    border-left: 1px solid #EAE8E1;
  }
}
</style>
