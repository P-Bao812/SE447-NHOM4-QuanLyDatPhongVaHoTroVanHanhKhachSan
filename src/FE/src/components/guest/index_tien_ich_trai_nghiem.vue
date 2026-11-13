<template>
  <div class="guest-facilities-container py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- 1. Header Banner -->
      <div class="facilities-hero rounded-4 text-white p-4 p-md-5 mb-5 shadow-lg position-relative overflow-hidden">
        <div class="max-w-2xl position-relative z-1">
          <div class="badge-resort d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3">
            <i class="bi bi-stars text-warning"></i>
            <span class="text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">KHU TIỆN ÍCH & TRẢI NGHIỆM NGHỈ DƯỠNG</span>
          </div>
          <h1 class="display-6 fw-bold mb-3">
            Hệ sinh thái tiện nghi 5 sao tại RoomBook
          </h1>
          <p class="lead mb-4 text-light opacity-90" style="font-size: 1rem;">
            Tận hưởng kỳ nghỉ trọn vẹn với hồ bơi vô cực ngắm sông Hoài, ẩm thực tinh hoa tại Sen Lounge, không gian An Spa thư giãn và các tour văn hóa bản địa độc bản.
          </p>
        </div>

        <!-- Quick Stats -->
        <div class="row g-3 text-center border-top border-white border-opacity-10 pt-4 mt-3 position-relative z-1">
          <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold text-white">06:00 - 22:00</div>
            <div class="small text-light opacity-75 mt-1">Thời gian phục vụ</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold text-white">100% Free</div>
            <div class="small text-light opacity-75 mt-1">Xe đạp & Hồ bơi</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold text-white">5 Khu tiện ích</div>
            <div class="small text-light opacity-75 mt-1">Tiêu chuẩn quốc tế</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="fs-4 fw-bold text-white">4.9 / 5.0 ★</div>
            <div class="small text-light opacity-75 mt-1">Đánh giá hài lòng</div>
          </div>
        </div>
      </div>

      <!-- 2. Danh mục phân loại tiện ích (Tabs) -->
      <div class="d-flex align-items-center gap-2 overflow-x-auto pb-3 mb-4">
        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="activeCategory = cat.id"
          class="btn rounded-pill px-3 py-2 text-nowrap d-flex align-items-center gap-2 fw-semibold transition"
          :class="activeCategory === cat.id ? 'btn-category-active shadow-sm' : 'btn-category-inactive bg-white border'"
          style="font-size: 0.85rem;"
        >
          <span>{{ cat.icon }}</span>
          <span>{{ cat.name }}</span>
        </button>
      </div>

      <!-- 3. Grid Danh sách các Tiện ích (Facility Cards) -->
      <div class="row g-4 mb-5">
        <div
          v-for="f in filteredFacilities"
          :key="f.id"
          class="col-12 col-md-6 col-xl-4"
        >
          <div class="card facility-card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column justify-content-between">
            <!-- Header Hình ảnh / Emoji Visual -->
            <div class="facility-visual position-relative p-4 d-flex align-items-center justify-content-center">
              <div class="visual-emoji display-3">{{ f.emoji }}</div>
              <span class="badge bg-white text-dark shadow-sm position-absolute top-0 start-0 m-3 px-3 py-1.5 rounded-pill" style="font-size: 0.75rem;">
                📍 {{ f.location }}
              </span>
              <span class="badge bg-success-custom text-white position-absolute top-0 end-0 m-3 px-3 py-1.5 rounded-pill" style="font-size: 0.75rem;">
                ⏰ {{ f.hours }}
              </span>
            </div>

            <!-- Body Nội dung -->
            <div class="card-body p-4 d-flex flex-column justify-content-between flex-grow-1">
              <div>
                <h5 class="card-title fw-bold text-main-dark mb-1">{{ f.title }}</h5>
                <div class="text-success-custom fw-semibold small mb-3">{{ f.subtitle }}</div>
                <p class="text-secondary-custom small mb-4 lh-base">{{ f.description }}</p>

                <!-- Đặc điểm nổi bật -->
                <div class="row g-2 mb-4">
                  <div
                    v-for="(hl, idx) in f.highlights"
                    :key="idx"
                    class="col-12 col-sm-6"
                  >
                    <div class="d-flex align-items-center gap-2 small text-dark">
                      <i class="bi bi-check2-circle text-success-custom"></i>
                      <span class="text-truncate">{{ hl }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer Giá & Nút Đặt chỗ -->
              <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                <div>
                  <span class="text-muted small">Chi phí:</span>
                  <div class="fw-bold text-success-custom fs-5">{{ f.priceTag }}</div>
                </div>
                <button
                  @click="openBookingModal(f)"
                  class="btn btn-book-facility px-3 py-2 rounded-3 text-white fw-bold d-flex align-items-center gap-1.5"
                  style="font-size: 0.85rem;"
                >
                  <i class="bi bi-calendar2-plus"></i> Đặt chỗ ngay
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Modal Đặt Chỗ Tiện Ích (Facility Booking Modal) -->
      <div v-if="targetFacility" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
        <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-lg">
          <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between bg-light">
            <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
              <i class="bi bi-bookmark-check text-success-custom"></i>
              Đặt chỗ: {{ targetFacility.title }}
            </h5>
            <button @click="targetFacility = null" class="btn-close"></button>
          </div>

          <div class="modal-body p-4" v-if="!modalBooked">
            <div class="p-3 bg-light rounded-3 border mb-3">
              <div class="d-flex align-items-center gap-3">
                <div class="fs-1">{{ targetFacility.emoji }}</div>
                <div>
                  <div class="fw-bold text-main-dark">{{ targetFacility.title }}</div>
                  <small class="text-muted">{{ targetFacility.location }} · {{ targetFacility.hours }}</small>
                </div>
              </div>
            </div>

            <!-- Form Đặt -->
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Ngày sử dụng</label>
                <input v-model="formFacility.date" type="date" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Khung giờ đón</label>
                <select v-model="formFacility.time" class="form-select">
                  <option value="08:00 - 10:00">08:00 - 10:00 (Sáng)</option>
                  <option value="14:00 - 16:00">14:00 - 16:00 (Chiều)</option>
                  <option value="17:00 - 19:00">17:00 - 19:00 (Hoàng hôn)</option>
                  <option value="19:30 - 21:00">19:30 - 21:00 (Tối)</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số khách tham gia</label>
                <input v-model.number="formFacility.guests" type="number" min="1" max="10" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số phòng (Nếu đã nhận)</label>
                <input v-model="formFacility.roomNumber" type="text" class="form-control" placeholder="Ví dụ: 303" />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Yêu cầu đặc biệt</label>
              <textarea v-model="formFacility.note" class="form-control" rows="2" placeholder="Ví dụ: Chuẩn bị khăn tắm, ghế cho em bé..."></textarea>
            </div>
          </div>

          <div class="modal-body p-5 text-center" v-else>
            <div class="avatar-success-box mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success-custom">
              <i class="bi bi-check-circle-fill fs-1"></i>
            </div>
            <h4 class="fw-bold text-main-dark mb-2">Đã ghi nhận đặt chỗ!</h4>
            <p class="text-muted small mb-4">
              Nhân viên bộ phận <strong>{{ targetFacility.title }}</strong> sẽ liên hệ hỗ trợ quý khách vào ngày {{ formFacility.date }}.
            </p>
            <button @click="targetFacility = null; modalBooked = false" class="btn btn-book-facility text-white px-4 py-2 rounded-3 fw-bold">
              Đóng thông báo
            </button>
          </div>

          <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2 bg-light" v-if="!modalBooked">
            <button @click="targetFacility = null" class="btn btn-outline-secondary px-4 rounded-3">Hủy</button>
            <button @click="submitFacilityBooking" class="btn btn-book-facility text-white px-4 rounded-3 fw-bold">
              Xác nhận đặt chỗ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IndexTienIchTraiNghiem',
  data() {
    return {
      activeCategory: 'all',
      targetFacility: null,
      modalBooked: false,

      formFacility: {
        date: '2026-11-15',
        time: '17:00 - 19:00',
        guests: 2,
        roomNumber: '303',
        note: '',
      },

      categories: [
        { id: 'all', name: 'Tất cả tiện ích', icon: '✨' },
        { id: 'pool', name: 'Hồ bơi & Thư giãn', icon: '🏊‍♂️' },
        { id: 'dining', name: 'Ẩm thực & Lounge', icon: '🍽️' },
        { id: 'spa', name: 'An Spa & Trị liệu', icon: '💆‍♀️' },
        { id: 'experience', name: 'Tour & Khám phá', icon: '🚲' },
        { id: 'transport', name: 'Đưa đón sân bay', icon: '🚗' },
      ],

      facilities: [
        {
          id: 'pool-infinity',
          category: 'pool',
          emoji: '🏊‍♂️',
          title: 'Hồ bơi vô cực ngắm sông Hoài',
          subtitle: 'Tầng 5 · Tầm nhìn sông nước thơ mộng',
          location: 'Tầng thượng (Rooftop)',
          hours: '06:00 - 21:00',
          priceTag: 'Miễn phí cho khách',
          description: 'Hồ bơi nước ấm vô cực nhìn thẳng ra bờ sông Hoài huyền ảo. Nơi lý tưởng để đắm mình trong làn nước trong vắt và ngắm hoàng hôn buông xuống phố Hội.',
          highlights: ['Nước ấm điều hòa 4 mùa', 'Khăn tắm & Ghế nằm miễn phí', 'Cocktail bar bên hồ', 'Khu vực bơi riêng cho trẻ em'],
        },
        {
          id: 'dining-sen',
          category: 'dining',
          emoji: '🍽️',
          title: 'Sen Lounge & Riverside Bar',
          subtitle: 'Ẩm thực Fusion Á - Âu & Đặc sản Hội An',
          location: 'Tầng trệt & Sân vườn ven sông',
          hours: '06:30 - 22:30',
          priceTag: 'Theo thực đơn',
          description: 'Không gian ẩm thực mộc mạc bên rặng tre xanh. Thưởng thức buffet sáng chuẩn quốc tế, món Cao Lầu trứ danh và trà chiều ngắm đèn lồng lung linh.',
          highlights: ['Bữa sáng buffet phong phú', 'Thực đơn món Việt thuần túy', 'Nhạc acoustic cuối tuần', 'Bàn tiệc lãng mạn ven sông'],
        },
        {
          id: 'spa-wellness',
          category: 'spa',
          emoji: '💆‍♀️',
          title: 'An Spa & Thảo dược cổ truyền',
          subtitle: 'Liệu pháp phục hồi năng lượng từ thiên nhiên',
          location: 'Khu biệt lập tĩnh lặng',
          hours: '09:00 - 21:30',
          priceTag: 'Từ 350.000₫',
          description: 'Đánh thức giác quan với các liệu trình massage bấm huyệt cổ truyền, ngâm chân thảo dược lá rừng và xông hơi đá muối Himalaya thư thái.',
          highlights: ['Tinh dầu sả chanh nguyên chất', 'Kỹ thuật viên chứng chỉ quốc tế', 'Phòng trị liệu riêng cặp đôi', 'Trà gừng mật ong chào đón'],
        },
        {
          id: 'tour-bike',
          category: 'experience',
          emoji: '🚲',
          title: 'Xe đạp & Tour khám phá Phố cổ',
          subtitle: 'Trải nghiệm cuộc sống người bản địa',
          location: 'Sảnh chính Lễ tân',
          hours: '06:00 - 18:00',
          priceTag: 'Miễn phí mượn xe',
          description: 'Khách sạn cung cấp miễn phí xe đạp vintage dạo quanh phố cổ Hội An, ngắm làng rau Trà Quế và khám phá những con hẻm cổ kính rực rỡ hoa giấy.',
          highlights: ['Miễn phí xe đạp cả ngày', 'Bản đồ du lịch độc quyền', 'Mũ cói & Nước suối miễn phí', 'Hướng dẫn viên tour buổi sáng'],
        },
        {
          id: 'transport-shuttle',
          category: 'transport',
          emoji: '🚗',
          title: 'Dịch vụ Đưa đón Sân bay Đà Nẵng',
          subtitle: 'Xe riêng cao cấp 4 - 7 chỗ an toàn',
          location: 'Cổng đón trả khách sạn',
          hours: '24/7 Theo yêu cầu',
          priceTag: 'Từ 280.000₫ / lượt',
          description: 'Chuyến đi êm ái, đúng giờ từ Sân bay Quốc tế Đà Nẵng hoặc Ga tàu đến tận sảnh khách sạn với đội ngũ tài xế tận tâm, lịch thiệp.',
          highlights: ['Đón tận sảnh với bảng tên', 'Xe đời mới máy lạnh êm ái', 'Miễn phí chờ chuyến bay trễ', 'Nước khoáng & Khăn lạnh trên xe'],
        },
      ],
    };
  },
  computed: {
    filteredFacilities() {
      if (this.activeCategory === 'all') return this.facilities;
      return this.facilities.filter((f) => f.category === this.activeCategory);
    },
  },
  methods: {
    openBookingModal(facility) {
      this.targetFacility = facility;
      this.modalBooked = false;
    },
    submitFacilityBooking() {
      this.modalBooked = true;
    },
  },
};
</script>

<style scoped>
.guest-facilities-container {
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

.bg-success-custom {
  background-color: #1E5E49 !important;
}

/* Hero Banner */
.facilities-hero {
  background: linear-gradient(135deg, #17382B 0%, #1E5E49 60%, #29745B 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.badge-resort {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Category Filter Tabs */
.btn-category-active {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
}

.btn-category-inactive {
  color: #5C6862;
  border-color: #EAE8E1;
}

.btn-category-inactive:hover {
  background-color: #FAF9F5;
  color: #172620;
}

/* Facility Card */
.facility-card {
  border: 1px solid #EAE8E1 !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.facility-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}

.facility-visual {
  height: 180px;
  background: linear-gradient(135deg, #EAF3EE 0%, #D4ECE2 100%);
}

.visual-emoji {
  transition: transform 0.3s ease;
}

.facility-card:hover .visual-emoji {
  transform: scale(1.15);
}

.btn-book-facility {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-book-facility:hover {
  background-color: #164636;
}

/* Modal */
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-dialog-custom {
  max-height: 90vh;
  animation: modalFadeIn 0.25s ease-out;
}

.avatar-success-box {
  width: 72px;
  height: 72px;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
