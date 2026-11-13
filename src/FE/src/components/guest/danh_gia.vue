<template>
  <div class="guest-reviews-container py-4">
    <div class="container-fluid px-3 px-lg-5 max-w-5xl mx-auto">
      <!-- 1. Header -->
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="fw-bold text-main-dark mb-1">Đánh giá & Trải nghiệm khách hàng</h2>
          <p class="text-secondary-custom small mb-0">
            Lắng nghe những cảm nhận chân thực nhất từ du khách đã nghỉ dưỡng tại RoomBook Hotel & Resort Hội An.
          </p>
        </div>
        <div>
          <span class="badge bg-success-subtle text-success-custom px-3 py-2 rounded-pill d-flex align-items-center gap-1.5 font-monospace">
            <i class="bi bi-patch-check-fill text-success"></i>
            98% Du khách yêu thích điểm đến này
          </span>
        </div>
      </div>

      <!-- 2. Thống kê điểm số tổng quan (Rating Summary) -->
      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4 bg-white">
        <div class="row g-4 align-items-center">
          <div class="col-12 col-md-4 text-center border-end-md">
            <div class="display-3 fw-bold text-success-custom mb-1 font-monospace">4.9</div>
            <div class="text-warning fs-5 mb-2">
              <i class="bi bi-star-fill me-1"></i>
              <i class="bi bi-star-fill me-1"></i>
              <i class="bi bi-star-fill me-1"></i>
              <i class="bi bi-star-fill me-1"></i>
              <i class="bi bi-star-fill"></i>
            </div>
            <div class="fw-bold text-main-dark small">Tuyệt hảo · 128 đánh giá đã xác thực</div>
            <small class="text-muted" style="font-size: 0.75rem;">Dựa trên trải nghiệm thực tế của du khách lưu trú</small>
          </div>

          <div class="col-12 col-md-8">
            <div class="row g-3">
              <div class="col-6 col-sm-3 text-center">
                <div class="p-3 bg-light rounded-3 border">
                  <small class="text-muted d-block mb-1">Độ sạch sẽ</small>
                  <div class="fw-bold text-success-custom fs-5">4.9 / 5.0</div>
                  <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success-custom" style="width: 98%"></div>
                  </div>
                </div>
              </div>

              <div class="col-6 col-sm-3 text-center">
                <div class="p-3 bg-light rounded-3 border">
                  <small class="text-muted d-block mb-1">Phục vụ</small>
                  <div class="fw-bold text-success-custom fs-5">5.0 / 5.0</div>
                  <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success-custom" style="width: 100%"></div>
                  </div>
                </div>
              </div>

              <div class="col-6 col-sm-3 text-center">
                <div class="p-3 bg-light rounded-3 border">
                  <small class="text-muted d-block mb-1">Tiện nghi</small>
                  <div class="fw-bold text-success-custom fs-5">4.8 / 5.0</div>
                  <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success-custom" style="width: 96%"></div>
                  </div>
                </div>
              </div>

              <div class="col-6 col-sm-3 text-center">
                <div class="p-3 bg-light rounded-3 border">
                  <small class="text-muted d-block mb-1">Vị trí</small>
                  <div class="fw-bold text-success-custom fs-5">4.9 / 5.0</div>
                  <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success-custom" style="width: 98%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Form gửi đánh giá trải nghiệm (Submit Review) -->
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 bg-white">
        <h5 class="fw-bold text-main-dark mb-3 d-flex align-items-center gap-2">
          <span>✍️</span>
          <span>Gửi nhận xét về kỳ nghỉ của bạn</span>
        </h5>

        <div class="row g-3 mb-3">
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-muted">Họ và tên của bạn</label>
            <input v-model="formReview.author" type="text" class="form-control" placeholder="Ví dụ: Trần Quốc Huy" />
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-muted">Hạng phòng đã lưu trú</label>
            <select v-model="formReview.roomType" class="form-select">
              <option value="Phòng Deluxe hướng vườn">Phòng Deluxe hướng vườn</option>
              <option value="Phòng Superior ấm cúng">Phòng Superior ấm cúng</option>
              <option value="Phòng Suite view sông Hoài">Phòng Suite view sông Hoài</option>
              <option value="Phòng Family kết nối 2 phòng ngủ">Phòng Family kết nối 2 phòng ngủ</option>
            </select>
          </div>
        </div>

        <!-- Chấm điểm sao -->
        <div class="mb-3 d-flex align-items-center gap-3">
          <span class="small fw-bold text-muted">Mức độ hài lòng:</span>
          <div class="star-rating d-flex gap-1 text-warning fs-4 cursor-pointer">
            <i
              v-for="star in 5"
              :key="star"
              @click="formReview.rating = star"
              :class="star <= formReview.rating ? 'bi bi-star-fill' : 'bi bi-star'"
            ></i>
          </div>
          <span class="small fw-bold text-success-custom">{{ getRatingText(formReview.rating) }}</span>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-bold text-muted">Nội dung chia sẻ cảm nhận</label>
          <textarea
            v-model="formReview.comment"
            class="form-control"
            rows="3"
            placeholder="Chia sẻ về không gian phòng, món ăn tại nhà hàng, thái độ phục vụ của nhân viên..."
          ></textarea>
        </div>

        <div class="d-flex justify-content-end">
          <button @click="submitNewReview" class="btn btn-review text-white px-4 py-2 rounded-3 fw-bold">
            <i class="bi bi-send-fill me-1"></i> Gửi đánh giá ngay
          </button>
        </div>
      </div>

      <!-- 4. Danh sách đánh giá thực tế của khách (Reviews Feed) -->
      <div class="space-y-4">
        <h5 class="fw-bold text-main-dark mb-3">Đánh giá gần đây ({{ reviewsList.length }})</h5>

        <div
          v-for="rev in reviewsList"
          :key="rev.id"
          class="card border-0 shadow-sm rounded-4 p-4 mb-3 bg-white"
        >
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="author-avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-success-custom">
                {{ rev.author.charAt(0) }}
              </div>
              <div>
                <div class="fw-bold text-main-dark">{{ rev.author }}</div>
                <div class="text-muted small">Đã lưu trú tại: <strong>{{ rev.roomType }}</strong> · {{ rev.date }}</div>
              </div>
            </div>

            <!-- Stars -->
            <div class="text-warning small">
              <i v-for="s in rev.rating" :key="'fill-' + s" class="bi bi-star-fill me-0.5"></i>
              <i v-for="s in (5 - rev.rating)" :key="'empty-' + s" class="bi bi-star me-0.5 text-muted"></i>
            </div>
          </div>

          <p class="text-secondary-custom small mb-3 lh-base">{{ rev.comment }}</p>

          <!-- Phản hồi chính thức của Ban Quản trị Resort -->
          <div v-if="rev.response" class="p-3 rounded-3 bg-light border-start border-3 border-success-custom small mt-2">
            <div class="fw-bold text-success-custom mb-1 d-flex align-items-center gap-1.5">
              <i class="bi bi-reply-fill"></i>
              <span>Phản hồi từ Ban Quản lý RoomBook Hotel:</span>
            </div>
            <div class="text-muted">{{ rev.response }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DanhGia',
  data() {
    return {
      formReview: {
        author: '',
        roomType: 'Phòng Deluxe hướng vườn',
        rating: 5,
        comment: '',
      },

      reviewsList: [
        {
          id: 1,
          author: 'Lê Thu Hà',
          roomType: 'Phòng Suite view sông Hoài',
          date: 'Hôm qua',
          rating: 5,
          comment: 'Kỳ nghỉ 3 ngày 2 đêm trên cả tuyệt vời! Bồn Jacuzzi ngoài ban công ngắm hoàng hôn sông Hoài cực kỳ lãng mạn. Đội ngũ lễ tân nhiệt tình hỗ trợ mượn xe đạp đi dạo phố cổ. Chắc chắn sẽ quay lại!',
          response: 'Cảm ơn chị Thu Hà rất nhiều vì những lời khen ngọt ngào! RoomBook rất hạnh phúc được đồng hành cùng chị và mong sớm được đón tiếp chị trong kỳ nghỉ kế tiếp tại Hội An.',
        },
        {
          id: 2,
          author: 'Nguyễn Văn Hùng',
          roomType: 'Phòng Deluxe hướng vườn',
          date: '08/11/2026',
          rating: 5,
          comment: 'Phòng ốc sạch sẽ không một hạt bụi, bữa sáng buffet món Cao Lầu và phở bò rất ngon. Không gian tĩnh lặng, nhiều cây xanh mát mẻ, nhân viên buồng phòng chào hỏi rất lễ phép.',
          response: 'Dạ cảm ơn anh Hùng đã dành thời gian đánh giá. Niềm vui và sự hài lòng của gia đình là động lực to lớn cho toàn thể nhân viên khách sạn ạ!',
        },
        {
          id: 3,
          author: 'Trần Minh Quang',
          roomType: 'Phòng Superior ấm cúng',
          date: '05/11/2026',
          rating: 4,
          comment: 'Khách sạn đẹp, vị trí gần trung tâm đi bộ ra Chùa Cầu chỉ 10 phút. Điểm trừ nhỏ là hồ bơi hơi đông vào lúc 17h, còn lại mọi dịch vụ đều chuẩn 5 sao.',
          response: 'RoomBook chân thành cảm ơn góp ý của anh Quang. Khách sạn đã bổ sung thêm nhân sự điều phối và khăn tắm tại hồ bơi khung giờ cao điểm để phục vụ chu đáo hơn ạ.',
        },
      ],
    };
  },
  methods: {
    getRatingText(rating) {
      const map = {
        5: 'Tuyệt hảo (5/5)',
        4: 'Rất tốt (4/5)',
        3: 'Bình thường (3/5)',
        2: 'Chưa hài lòng (2/5)',
        1: 'Kém (1/5)',
      };
      return map[rating] || 'Rất tốt';
    },
    submitNewReview() {
      if (!this.formReview.author || !this.formReview.comment) {
        alert('Vui lòng nhập tên và nội dung nhận xét!');
        return;
      }
      this.reviewsList.unshift({
        id: Date.now(),
        author: this.formReview.author,
        roomType: this.formReview.roomType,
        date: 'Vừa xong',
        rating: this.formReview.rating,
        comment: this.formReview.comment,
        response: 'Cảm ơn bạn đã gửi đánh giá trải nghiệm! RoomBook rất trân trọng những đóng góp quý báu này.',
      });
      this.formReview.author = '';
      this.formReview.comment = '';
      alert('Gửi đánh giá thành công! Cảm ơn bạn rất nhiều.');
    },
  },
};
</script>

<style scoped>
.guest-reviews-container {
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

.border-success-custom {
  border-color: #1E5E49 !important;
}

.author-avatar {
  width: 44px;
  height: 44px;
  font-size: 1.1rem;
}

.cursor-pointer {
  cursor: pointer;
}

.btn-review {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-review:hover {
  background-color: #164636;
}

@media (min-width: 768px) {
  .border-end-md {
    border-right: 1px solid #EAE8E1;
  }
}
</style>
