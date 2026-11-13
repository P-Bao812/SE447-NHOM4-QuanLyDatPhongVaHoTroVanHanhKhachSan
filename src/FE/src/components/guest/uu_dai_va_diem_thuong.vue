<template>
  <div class="guest-loyalty-container py-4">
    <div class="container-fluid px-3 px-lg-5 max-w-5xl mx-auto">
      <!-- 1. Header & Virtual Member Card -->
      <div class="loyalty-hero rounded-4 text-white p-4 p-md-5 mb-5 shadow-lg position-relative overflow-hidden">
        <div class="row g-4 align-items-center position-relative z-1">
          <!-- Cột trái: Giới thiệu -->
          <div class="col-12 col-lg-7">
            <div class="badge-resort d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3">
              <i class="bi bi-award-fill text-warning"></i>
              <span class="text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">ROOMBOOK REWARDS CLUB</span>
            </div>
            <h2 class="display-6 fw-bold mb-3">Chương trình Khách hàng thân thiết</h2>
            <p class="lead mb-0 text-light opacity-90" style="font-size: 0.95rem;">
              Tích lũy điểm thưởng tự động từ mỗi kỳ nghỉ (100.000₫ = 1 điểm). Sử dụng điểm để quy đổi voucher giảm giá, ẩm thực và dịch vụ spa 5 sao miễn phí.
            </p>
          </div>

          <!-- Cột phải: Thẻ hội viên ảo -->
          <div class="col-12 col-lg-5">
            <div class="virtual-card p-4 rounded-4 shadow-lg position-relative overflow-hidden text-white border border-white border-opacity-25">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold tracking-wider small text-uppercase text-warning">★ HỘI VIÊN VÀNG (GOLD)</span>
                <i class="bi bi-wifi fs-4 opacity-75"></i>
              </div>

              <div class="mb-4">
                <div class="small opacity-75 text-uppercase" style="font-size: 0.7rem;">Chủ sở hữu thẻ</div>
                <div class="fs-5 fw-bold">{{ guestName }}</div>
              </div>

              <div class="d-flex justify-content-between align-items-end pt-3 border-top border-white border-opacity-20">
                <div>
                  <div class="small opacity-75 text-uppercase" style="font-size: 0.7rem;">Điểm khả dụng</div>
                  <div class="fs-2 fw-bold text-warning font-monospace">{{ userPoints }} <small class="fs-6 text-white">pts</small></div>
                </div>
                <div class="small opacity-75 font-monospace">ID: RB-GOLD-2611</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Các hạng thẻ hội viên -->
        <div class="row g-3 text-center border-top border-white border-opacity-10 pt-4 mt-4 position-relative z-1">
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 text-start">
              <div class="small opacity-75">Hạng Bạc (Silver)</div>
              <div class="fw-bold mt-1">0 – 199 pts · Giảm 5% đặt phòng</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white bg-opacity-25 border border-warning text-start shadow-sm">
              <div class="small text-warning fw-bold">Hạng Vàng (Gold) ★ Hiện tại</div>
              <div class="fw-bold mt-1">200 – 499 pts · Giảm 10% & Trả phòng trễ</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 text-start">
              <div class="small opacity-75">Hạng Kim Cương (Diamond)</div>
              <div class="fw-bold mt-1">500+ pts · Giảm 15% & Đưa đón sân bay Free</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Danh mục Voucher đổi điểm (Redeem Catalog) -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
          <div>
            <h4 class="fw-bold text-main-dark mb-1">Danh mục Quà tặng & Voucher đổi điểm</h4>
            <p class="text-secondary-custom small mb-0">Sử dụng điểm tích lũy của bạn để quy đổi trực tiếp ra mã ưu đãi</p>
          </div>
          <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">
            Điểm của bạn: <strong class="text-success-custom">{{ userPoints }} pts</strong>
          </span>
        </div>

        <div class="row g-4">
          <div
            v-for="reward in rewardsCatalog"
            :key="reward.id"
            class="col-12 col-md-6"
          >
            <div class="card reward-item-card h-100 border rounded-4 p-4 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="reward-icon-box rounded-3 d-flex align-items-center justify-content-center fs-2 bg-light border">
                    {{ reward.emoji }}
                  </div>
                  <span class="badge bg-warning-subtle text-warning-emphasis fs-6 px-3 py-1.5 rounded-pill fw-bold font-monospace">
                    {{ reward.costPoints }} pts
                  </span>
                </div>

                <h5 class="fw-bold text-main-dark mb-1">{{ reward.title }}</h5>
                <p class="text-secondary-custom small mb-3">{{ reward.desc }}</p>

                <div class="small text-muted mb-3">
                  <i class="bi bi-clock me-1"></i>
                  <span>Hạn sử dụng: {{ reward.expiry }}</span>
                </div>
              </div>

              <div class="pt-3 border-top mt-auto d-flex align-items-center justify-content-between">
                <span class="small" :class="userPoints >= reward.costPoints ? 'text-success-custom fw-semibold' : 'text-danger'">
                  <i :class="userPoints >= reward.costPoints ? 'bi bi-check-circle-fill' : 'bi bi-x-circle'"></i>
                  {{ userPoints >= reward.costPoints ? 'Đủ điểm đổi thưởng' : 'Chưa đủ điểm' }}
                </span>
                <button
                  @click="redeemReward(reward)"
                  :disabled="userPoints < reward.costPoints"
                  class="btn btn-redeem px-3 py-1.5 rounded-3 text-white fw-bold small"
                >
                  Đổi ưu đãi ngay
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Danh sách Voucher đã đổi thành công -->
      <div v-if="redeemedVouchers.length > 0" class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
        <h5 class="fw-bold text-main-dark mb-3 d-flex align-items-center gap-2">
          <span>🎟️</span>
          <span>Ví Voucher của bạn</span>
        </h5>
        <div class="row g-3">
          <div
            v-for="vc in redeemedVouchers"
            :key="vc.code"
            class="col-12 col-md-6"
          >
            <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold text-main-dark">{{ vc.title }}</div>
                <small class="text-muted">Mã áp dụng khi đặt phòng hoặc gọi món:</small>
                <div class="fw-bold text-success-custom fs-5 font-monospace mt-1">{{ vc.code }}</div>
              </div>
              <button @click="copyCode(vc.code)" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                Sao chép
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UuDaiVaDiemThuong',
  data() {
    return {
      guestName: 'Nguyễn Minh Anh',
      userPoints: 320,

      redeemedVouchers: [
        {
          code: 'GOLD-WELCOME-100K',
          title: 'Voucher giảm 100.000₫ đặt phòng',
        },
      ],

      rewardsCatalog: [
        {
          id: 'rw-1',
          emoji: '🎟️',
          costPoints: 100,
          title: 'Voucher giảm 100.000₫ đặt phòng',
          desc: 'Áp dụng trừ trực tiếp vào hóa đơn cho mọi hạng phòng từ Superior trở lên tại khách sạn.',
          expiry: '30 ngày kể từ ngày đổi',
        },
        {
          id: 'rw-2',
          emoji: '🥐',
          costPoints: 150,
          title: 'Miễn phí Buffet sáng cho 2 khách',
          desc: 'Thưởng thức bữa sáng quốc tế tại Sen Lounge với hơn 40 món ăn Á - Âu và đặc sản Hội An.',
          expiry: 'Sử dụng trong kỳ lưu trú',
        },
        {
          id: 'rw-3',
          emoji: '💆‍♀️',
          costPoints: 200,
          title: 'Massage phục hồi cổ vai gáy 45 phút',
          desc: 'Gói trị liệu thư giãn tại An Spa giúp giải tỏa căng thẳng sau chuyến hành trình dài.',
          expiry: 'Áp dụng từ 09:00 - 18:00',
        },
        {
          id: 'rw-4',
          emoji: '🚗',
          costPoints: 200,
          title: 'Miễn phí Đưa đón Sân bay Đà Nẵng',
          desc: '1 chuyến xe riêng 4-7 chỗ đưa đón tận nơi an toàn, êm ái giữa khách sạn và sân bay.',
          expiry: 'Đặt trước 12 giờ',
        },
        {
          id: 'rw-5',
          emoji: '⭐',
          costPoints: 350,
          title: 'Nâng cấp hạng phòng Suite View Sông',
          desc: 'Miễn phí nâng cấp lên phòng Suite cao cấp có bồn Jacuzzi (tùy thuộc tình trạng phòng trống).',
          expiry: 'Xác nhận tại quầy lễ tân',
        },
      ],
    };
  },
  methods: {
    redeemReward(reward) {
      if (this.userPoints < reward.costPoints) {
        alert('Rất tiếc, bạn không đủ điểm để đổi phần quà này!');
        return;
      }
      this.userPoints -= reward.costPoints;
      const code = 'VCH-' + Math.floor(100000 + Math.random() * 900000);
      this.redeemedVouchers.unshift({
        code,
        title: reward.title,
      });
      alert(`Đổi thành công "${reward.title}"!\nMã ưu đãi của bạn là: ${code}`);
    },
    copyCode(code) {
      navigator.clipboard?.writeText(code);
      alert(`Đã sao chép mã ${code} vào bộ nhớ tạm!`);
    },
  },
};
</script>

<style scoped>
.guest-loyalty-container {
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

/* Loyalty Hero */
.loyalty-hero {
  background: linear-gradient(135deg, #17382B 0%, #1E5E49 60%, #29745B 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.badge-resort {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Virtual Card */
.virtual-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.05) 100%);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
  border-radius: 16px;
}

.reward-item-card {
  border: 1px solid #EAE8E1 !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.reward-item-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06) !important;
}

.reward-icon-box {
  width: 52px;
  height: 52px;
}

.btn-redeem {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-redeem:hover:not(:disabled) {
  background-color: #164636;
}

.btn-redeem:disabled {
  background-color: #A3B8B0;
  cursor: not-allowed;
}
</style>
