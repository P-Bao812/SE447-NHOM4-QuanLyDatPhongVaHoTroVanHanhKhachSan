<template>
  <div class="guest-payment-container py-4">
    <div class="container-fluid px-3 px-lg-5 max-w-4xl mx-auto">
      <div v-if="!paidSuccess">
        <!-- Header -->
        <div class="text-center mb-4">
          <h2 class="fw-bold text-main-dark mb-1">Cổng thanh toán trực tuyến</h2>
          <p class="text-secondary-custom small">Xác nhận thanh toán tiền cọc / giữ phòng qua cổng thanh toán bảo mật 256-bit SSL.</p>
        </div>

        <div class="row g-4">
          <!-- Cột trái: Tùy chọn phương thức thanh toán -->
          <div class="col-12 col-md-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
              <h5 class="fw-bold text-main-dark mb-3">1. Chọn phương thức thanh toán</h5>

              <div class="d-flex flex-column gap-2 mb-4">
                <!-- VietQR -->
                <div
                  @click="paymentMethod = 'qr'"
                  class="p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer transition"
                  :class="paymentMethod === 'qr' ? 'border-success-custom bg-success-subtle bg-opacity-25' : 'bg-light'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="payment-icon-box bg-white rounded-2 d-flex align-items-center justify-content-center border shadow-xs fs-4">
                      📱
                    </div>
                    <div>
                      <div class="fw-bold text-main-dark">Quét mã VietQR (Khuyên dùng)</div>
                      <small class="text-muted">Hỗ trợ tất cả app ngân hàng & ví điện tử MoMo, ZaloPay</small>
                    </div>
                  </div>
                  <i class="bi fs-5" :class="paymentMethod === 'qr' ? 'bi-check-circle-fill text-success-custom' : 'bi-circle text-muted'"></i>
                </div>

                <!-- Thẻ tín dụng Visa/Master -->
                <div
                  @click="paymentMethod = 'card'"
                  class="p-3 border rounded-3 d-flex align-items-center justify-content-between cursor-pointer transition"
                  :class="paymentMethod === 'card' ? 'border-success-custom bg-success-subtle bg-opacity-25' : 'bg-light'"
                >
                  <div class="d-flex align-items-center gap-3">
                    <div class="payment-icon-box bg-white rounded-2 d-flex align-items-center justify-content-center border shadow-xs fs-4">
                      💳
                    </div>
                    <div>
                      <div class="fw-bold text-main-dark">Thẻ Quốc Tế (Visa, Master, JCB)</div>
                      <small class="text-muted">Miễn phí phí giao dịch thẻ quốc tế</small>
                    </div>
                  </div>
                  <i class="bi fs-5" :class="paymentMethod === 'card' ? 'bi-check-circle-fill text-success-custom' : 'bi-circle text-muted'"></i>
                </div>
              </div>

              <!-- Chi tiết phương thức VietQR -->
              <div v-if="paymentMethod === 'qr'" class="text-center p-3 bg-light rounded-3 border">
                <div class="small fw-bold text-muted text-uppercase mb-2">Quét mã bằng App Ngân Hàng của bạn</div>
                <img
                  :src="`https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=ROOMBOOK_${billData.totalAmount}`"
                  alt="VietQR Code"
                  class="rounded-3 shadow-sm border p-2 bg-white mb-2"
                  style="width: 170px; height: 170px;"
                />
                <div class="small fw-bold text-main-dark">Ngân hàng TMCP Ngoại thương (Vietcombank)</div>
                <div class="small text-muted font-monospace">STK: 0071 0009 99888 · Chủ TK: ROOMBOOK HOTEL</div>
                <div class="small text-danger mt-1 fw-semibold">Nội dung CK: RB {{ billData.phone }}</div>
              </div>

              <!-- Chi tiết phương thức Thẻ tín dụng -->
              <div v-else class="p-3 bg-light rounded-3 border">
                <div class="mb-3">
                  <label class="form-label small fw-bold text-muted">Số thẻ ngân hàng</label>
                  <input type="text" class="form-control font-monospace" placeholder="4111 2222 3333 4444" />
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted">Hết hạn (MM/YY)</label>
                    <input type="text" class="form-control text-center font-monospace" placeholder="12/28" />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-muted">Mã bảo mật CVV</label>
                    <input type="password" maxlength="3" class="form-control text-center font-monospace" placeholder="•••" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cột phải: Tóm tắt đơn hàng & nút xác nhận -->
          <div class="col-12 col-md-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-3">
              <h5 class="fw-bold text-main-dark mb-3 pb-2 border-bottom">Tóm tắt đơn đặt phòng</h5>

              <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Hạng phòng:</span>
                <span class="fw-bold text-dark">{{ billData.roomType }} Room</span>
              </div>
              <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Thời gian:</span>
                <span class="fw-semibold text-dark">{{ billData.checkIn }} - {{ billData.checkOut }} ({{ billData.nights }} đêm)</span>
              </div>
              <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Khách lưu trú:</span>
                <span class="fw-semibold text-dark">{{ billData.guestName }}</span>
              </div>
              <div class="d-flex justify-content-between small text-muted mb-3">
                <span>Điểm thưởng tích lũy:</span>
                <span class="fw-bold text-success-custom">+{{ Math.floor(billData.totalAmount / 100000) }} pts</span>
              </div>

              <div class="d-flex justify-content-between align-items-baseline pt-3 border-top mb-4">
                <span class="fw-bold text-main-dark">Tổng thanh toán:</span>
                <span class="fs-4 fw-bold text-success-custom">{{ formatVND(billData.totalAmount) }}</span>
              </div>

              <button
                @click="confirmPayment"
                class="btn btn-pay w-100 py-2.5 text-white fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2"
              >
                <i class="bi bi-shield-check fs-5"></i>
                <span>Xác nhận đã thanh toán</span>
              </button>

              <div class="text-center mt-3">
                <small class="text-muted" style="font-size: 0.75rem;">
                  🔒 Dữ liệu thanh toán được mã hóa an toàn theo tiêu chuẩn PCI-DSS.
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Màn hình Thanh toán thành công & Vé lưu trú -->
      <div v-else class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
        <div class="avatar-success-box mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success-custom">
          <i class="bi bi-check-lg fs-1"></i>
        </div>
        <h3 class="fw-bold text-main-dark mb-1">Thanh toán hoàn tất!</h3>
        <p class="text-muted small mb-4">
          Cảm ơn quý khách <strong>{{ billData.guestName }}</strong>. Mã đặt phòng của bạn là:
          <strong class="text-success-custom fs-5 font-monospace">{{ createdBookingId }}</strong>.
        </p>

        <div class="p-3 bg-light rounded-3 border max-w-md mx-auto mb-4 text-start">
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Hạng phòng:</span>
            <span class="fw-bold text-dark">{{ billData.roomType }} Room</span>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Nhận phòng:</span>
            <span>14:00 ngày {{ billData.checkIn }}</span>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span>Số tiền đã thanh toán:</span>
            <span class="fw-bold text-success-custom">{{ formatVND(billData.totalAmount) }}</span>
          </div>
          <div class="d-flex justify-content-between small text-muted">
            <span>Điểm tích lũy mới:</span>
            <span class="fw-bold text-warning font-monospace">+{{ Math.floor(billData.totalAmount / 100000) }} pts</span>
          </div>
        </div>

        <div class="d-flex justify-content-center gap-3">
          <router-link to="/my-bookings" class="btn btn-outline-success px-4 py-2 rounded-pill fw-bold">
            Xem lịch sử đặt phòng
          </router-link>
          <router-link to="/" class="btn btn-pay text-white px-4 py-2 rounded-pill fw-bold">
            Về trang chủ
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useHotelStore } from '../../stores/hotelStore';

export default {
  name: 'ThanhToanOnline',
  setup() {
    const hotelStore = useHotelStore();
    return { hotelStore };
  },
  data() {
    return {
      paymentMethod: 'qr',
      paidSuccess: false,
      createdBookingId: '',

      billData: {
        guestName: 'Nguyễn Minh Anh',
        phone: '0903 456 789',
        roomType: 'Deluxe',
        checkIn: '15/11',
        checkOut: '18/11',
        nights: 3,
        totalAmount: 2700000,
      },
    };
  },
  created() {
    if (this.hotelStore.pendingBooking) {
      this.billData = { ...this.hotelStore.pendingBooking };
    }
  },
  methods: {
    formatVND(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    confirmPayment() {
      const newBk = this.hotelStore.createGuestBooking(this.billData);
      this.createdBookingId = newBk.id;
      this.paidSuccess = true;
    },
  },
};
</script>

<style scoped>
.guest-payment-container {
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

.border-success-custom {
  border-color: #1E5E49 !important;
}

.cursor-pointer {
  cursor: pointer;
}

.payment-icon-box {
  width: 44px;
  height: 44px;
}

.btn-pay {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-pay:hover {
  background-color: #164636;
}

.avatar-success-box {
  width: 72px;
  height: 72px;
}
</style>
