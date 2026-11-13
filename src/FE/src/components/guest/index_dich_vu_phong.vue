<template>
  <div class="guest-services-container py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- 1. Header -->
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="fw-bold text-main-dark mb-1">Dịch vụ phòng & Phục vụ tận nơi</h2>
          <p class="text-secondary-custom small mb-0">
            Gửi yêu cầu ẩm thực, tiện nghi buồng phòng và di chuyển trực tiếp đến đội ngũ phục vụ RoomBook 24/7.
          </p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-success-subtle text-success-custom px-3 py-2 rounded-pill d-flex align-items-center gap-2 font-monospace">
            <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
            Đội ngũ trực tuyến 24/7
          </span>
        </div>
      </div>

      <!-- 2. Danh mục dịch vụ (Category Tabs) -->
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

      <!-- 3. Grid Danh sách các món / dịch vụ (Cards) -->
      <div class="row g-4 mb-5">
        <div
          v-for="service in filteredServices"
          :key="service.name"
          class="col-12 col-md-6 col-xl-4"
        >
          <div class="card service-card h-100 border-0 shadow-sm rounded-4 p-4 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                <div class="service-emoji-box rounded-3 d-flex align-items-center justify-content-center fs-2 bg-light border">
                  {{ service.emoji }}
                </div>
                <span
                  class="badge px-2.5 py-1 rounded-pill"
                  :class="service.price === 'Miễn phí' ? 'bg-success-subtle text-success-custom' : 'bg-light text-dark border'"
                  style="font-size: 0.75rem;"
                >
                  {{ service.price }}
                </span>
              </div>

              <h5 class="fw-bold text-main-dark mb-1">{{ service.name }}</h5>
              <p class="text-secondary-custom small mb-4 lh-base">{{ service.desc }}</p>
            </div>

            <div class="pt-3 border-top d-flex align-items-center justify-content-between">
              <span class="text-muted small">⏱️ {{ service.timeEstimate }}</span>
              <button
                @click="openRequestModal(service)"
                class="btn btn-request px-3 py-1.5 rounded-3 text-white fw-bold small d-flex align-items-center gap-1.5"
              >
                <span>Yêu cầu ngay</span>
                <i class="bi bi-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Lịch sử yêu cầu đã gửi (Live Requests Log) -->
      <div v-if="submittedRequests.length > 0" class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
          <h5 class="fw-bold text-main-dark mb-0 d-flex align-items-center gap-2">
            <span>📋</span>
            <span>Lịch sử yêu cầu dịch vụ hôm nay</span>
          </h5>
          <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 rounded-pill">
            {{ submittedRequests.length }} yêu cầu
          </span>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light">
              <tr>
                <th>MÃ YÊU CẦU</th>
                <th>DỊCH VỤ</th>
                <th>PHÒNG</th>
                <th>SỐ LƯỢNG</th>
                <th>THỜI GIAN</th>
                <th>TRẠNG THÁI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="req in submittedRequests" :key="req.id">
                <td class="fw-bold text-main-dark">{{ req.id }}</td>
                <td>
                  <span class="me-1">{{ req.emoji }}</span>
                  <span class="fw-semibold">{{ req.serviceName }}</span>
                </td>
                <td><span class="badge bg-light text-dark border">Phòng {{ req.roomNumber }}</span></td>
                <td>{{ req.quantity }}</td>
                <td class="text-muted">{{ req.time }}</td>
                <td>
                  <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 rounded-pill">
                    ● {{ req.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Modal Gửi Yêu Cầu Dịch Vụ (Request Modal) -->
      <div v-if="targetService" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
        <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-lg">
          <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between bg-light">
            <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
              <i class="bi bi-bell-fill text-success-custom"></i>
              Gửi yêu cầu: {{ targetService.name }}
            </h5>
            <button @click="targetService = null" class="btn-close"></button>
          </div>

          <div class="modal-body p-4" v-if="!serviceRequestedSuccess">
            <div class="p-3 bg-light rounded-3 border mb-3 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-3">
                <span class="fs-2">{{ targetService.emoji }}</span>
                <div>
                  <div class="fw-bold text-main-dark">{{ targetService.name }}</div>
                  <small class="text-muted">Ước tính phục vụ: {{ targetService.timeEstimate }}</small>
                </div>
              </div>
              <span class="badge bg-success-custom text-white fs-6">{{ targetService.price }}</span>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số phòng của bạn</label>
                <input v-model="formService.roomNumber" type="text" class="form-control fw-bold" placeholder="303" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số lượng</label>
                <input v-model.number="formService.quantity" type="number" min="1" max="10" class="form-control" />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Thời điểm mong muốn</label>
              <select v-model="formService.timeRange" class="form-select">
                <option value="Càng sớm càng tốt (15-20 phút)">Càng sớm càng tốt (15-20 phút)</option>
                <option value="Trong vòng 1 giờ">Trong vòng 1 giờ</option>
                <option value="Vào buổi trưa (12:00)">Vào buổi trưa (12:00)</option>
                <option value="Vào buổi tối (19:00)">Vào buổi tối (19:00)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Ghi chú thêm cho nhân viên</label>
              <textarea v-model="formService.notes" class="form-control" rows="2" placeholder="Ví dụ: Ít cay, giao tại cửa, không gõ cửa to..."></textarea>
            </div>
          </div>

          <div class="modal-body p-5 text-center" v-else>
            <div class="avatar-success-box mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success-custom">
              <i class="bi bi-check-lg fs-1"></i>
            </div>
            <h4 class="fw-bold text-main-dark mb-2">Đã tiếp nhận yêu cầu!</h4>
            <p class="text-muted small mb-4">
              Yêu cầu <strong>{{ targetService.name }}</strong> đã được chuyển trực tiếp đến bộ phận phục vụ phòng {{ formService.roomNumber }}.
            </p>
            <button @click="targetService = null; serviceRequestedSuccess = false" class="btn btn-request text-white px-4 py-2 rounded-3 fw-bold">
              Đóng thông báo
            </button>
          </div>

          <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2 bg-light" v-if="!serviceRequestedSuccess">
            <button @click="targetService = null" class="btn btn-outline-secondary px-4 rounded-3">Hủy</button>
            <button @click="submitServiceRequest" class="btn btn-request text-white px-4 rounded-3 fw-bold">
              Gửi yêu cầu phục vụ
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IndexDichVuPhong',
  data() {
    return {
      activeCategory: 'all',
      targetService: null,
      serviceRequestedSuccess: false,

      formService: {
        roomNumber: '303',
        quantity: 1,
        timeRange: 'Càng sớm càng tốt (15-20 phút)',
        notes: '',
      },

      submittedRequests: [
        {
          id: 'SR-001',
          emoji: '🧺',
          serviceName: 'Thay bộ chăn ga gối mới',
          roomNumber: '303',
          quantity: '1 bộ',
          time: 'Vừa xong',
          status: 'Đang xử lý',
        },
      ],

      categories: [
        { id: 'all', name: 'Tất cả dịch vụ', icon: '✨' },
        { id: 'dining', name: 'Ẩm thực & Thức uống', icon: '🍽️' },
        { id: 'housekeeping', name: 'Tiện nghi buồng phòng', icon: '🧹' },
        { id: 'spa', name: 'Thư giãn & Spa tại phòng', icon: '💆' },
        { id: 'laundry', name: 'Giặt ủi & Là hơi', icon: '👔' },
      ],

      services: [
        {
          name: 'Khay bánh & Cà phê sáng tại phòng',
          category: 'dining',
          emoji: '☕',
          price: 'Miễn phí',
          desc: 'Phục vụ bữa sáng nhẹ gồm bánh sừng bò nướng giòn và cà phê phin đậm đà ngay tại ban công phòng.',
          timeEstimate: '15 - 20 phút',
        },
        {
          name: 'Set Trà chiều & Bánh ngọt Hội An',
          category: 'dining',
          emoji: '🫖',
          price: '220.000 ₫',
          desc: 'Thưởng trà hoa sen cùng các loại mứt truyền thống, bánh ít lá gai và bánh su kem tươi mát lạnh.',
          timeEstimate: '20 - 30 phút',
        },
        {
          name: 'Rượu vang đỏ & Khay phô mai thượng hạng',
          category: 'dining',
          emoji: '🍷',
          price: '650.000 ₫',
          desc: '1 chai vang Chile Cabernet Sauvignon đi kèm đĩa phô mai ngoại nhập, thịt nguội và hạt óc chó.',
          timeEstimate: '15 phút',
        },
        {
          name: 'Thay bộ chăn ga gối lụa mới',
          category: 'housekeeping',
          emoji: '🛏️',
          price: 'Miễn phí',
          desc: 'Nhân viên buồng phòng sẽ thay mới toàn bộ drap giường, vỏ gối cotton lụa 400 sợi cao cấp.',
          timeEstimate: '15 - 20 phút',
        },
        {
          name: 'Dọn phòng & Khử khuẩn tăng cường',
          category: 'housekeeping',
          emoji: '🧹',
          price: 'Miễn phí',
          desc: 'Hút bụi, lau dọn bề mặt và xông tinh dầu quế thiên nhiên giúp không khí phòng trong lành tinh tươm.',
          timeEstimate: '30 - 45 phút',
        },
        {
          name: 'Bổ sung Minibar & Nước khoáng',
          category: 'housekeeping',
          emoji: '🧊',
          price: 'Miễn phí',
          desc: 'Bổ sung thêm 2 chai nước suối thiên nhiên, đá viên và đồ ăn vặt trang bị trong minibar phòng.',
          timeEstimate: '10 phút',
        },
        {
          name: 'Massage cổ vai gáy thảo mộc tại phòng',
          category: 'spa',
          emoji: '💆‍♂️',
          price: '450.000 ₫',
          desc: 'Kỹ thuật viên An Spa mang theo đệm trị liệu và túi chườm ngải cứu phục vụ trực tiếp tại phòng (45 phút).',
          timeEstimate: 'Đặt trước 30 phút',
        },
        {
          name: 'Dịch vụ Giặt sấy lấy nhanh trong 4 giờ',
          category: 'laundry',
          emoji: '👔',
          price: '120.000 ₫ / kg',
          desc: 'Nhận quần áo tận cửa phòng, giặt sấy thơm tho và là ủi phẳng phiu trao tận tay trong ngày.',
          timeEstimate: 'Trong vòng 4 giờ',
        },
      ],
    };
  },
  computed: {
    filteredServices() {
      if (this.activeCategory === 'all') return this.services;
      return this.services.filter((s) => s.category === this.activeCategory);
    },
  },
  methods: {
    openRequestModal(service) {
      this.targetService = service;
      this.serviceRequestedSuccess = false;
    },
    submitServiceRequest() {
      const newReq = {
        id: 'SR-00' + (this.submittedRequests.length + 1),
        emoji: this.targetService.emoji,
        serviceName: this.targetService.name,
        roomNumber: this.formService.roomNumber,
        quantity: this.formService.quantity + ' phần',
        time: 'Vừa xong',
        status: 'Chờ xử lý',
      };
      this.submittedRequests.unshift(newReq);
      this.serviceRequestedSuccess = true;
    },
  },
};
</script>

<style scoped>
.guest-services-container {
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

/* Service Card */
.service-card {
  border: 1px solid #EAE8E1 !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.service-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}

.service-emoji-box {
  width: 52px;
  height: 52px;
}

.btn-request {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-request:hover {
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
