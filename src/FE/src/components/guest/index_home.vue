<template>
  <div class="guest-home-container py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- 1. Hero Banner sang trọng phong cách Hội An Resort -->
      <div class="hero-banner rounded-4 text-white p-4 p-md-5 mb-5 shadow-lg position-relative overflow-hidden">
        <div class="hero-content position-relative z-1 max-w-2xl">
          <div class="badge-resort d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill mb-3">
            <i class="bi bi-stars text-warning"></i>
            <span class="text-uppercase fw-bold" style="letter-spacing: 1px; font-size: 0.75rem;">ROOMBOOK BOUTIQUE HOTEL & RESORT</span>
          </div>
          <h1 class="display-5 fw-bold mb-3 lh-sm">
            Gác lại bộn bề,<br />trở về chốn bình yên.
          </h1>
          <p class="lead mb-4 text-light opacity-90" style="font-size: 1.05rem;">
            Khu nghỉ dưỡng phong cách di sản giữa lòng phố cổ Hội An. Trải nghiệm không gian lưu trú mộc mạc kết hợp tiện nghi đương đại 5 sao, đón bình minh bên sông Hoài thơ mộng.
          </p>
        </div>

        <!-- 2. Search Bar Widget đặt phòng trực tiếp -->
        <div class="search-widget bg-white text-dark rounded-4 p-3 p-md-4 shadow-lg position-relative z-1 mt-4">
          <div class="row g-3 align-items-end">
            <!-- Ngày nhận phòng -->
            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label text-muted fw-bold text-uppercase mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                <i class="bi bi-calendar-event text-success-custom"></i>
                Ngày nhận phòng
              </label>
              <input
                v-model="searchCheckIn"
                type="date"
                class="form-control form-control-custom fw-bold"
              />
            </div>

            <!-- Ngày trả phòng -->
            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label text-muted fw-bold text-uppercase mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                <i class="bi bi-calendar-check text-success-custom"></i>
                Ngày trả phòng
              </label>
              <input
                v-model="searchCheckOut"
                type="date"
                class="form-control form-control-custom fw-bold"
              />
            </div>

            <!-- Số lượng khách -->
            <div class="col-12 col-sm-6 col-lg-3">
              <label class="form-label text-muted fw-bold text-uppercase mb-1 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                <i class="bi bi-people text-success-custom"></i>
                Số lượng khách
              </label>
              <select v-model="searchCapacity" class="form-select form-control-custom fw-bold">
                <option value="all">Tất cả sức chứa</option>
                <option value="couple">2 người lớn (Cặp đôi)</option>
                <option value="single">1 người lớn (Đơn thân)</option>
                <option value="family">Gia đình (4+ khách)</option>
              </select>
            </div>

            <!-- Nút tìm kiếm -->
            <div class="col-12 col-sm-6 col-lg-3">
              <button
                @click="applyFilters"
                class="btn btn-search-rooms w-100 py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2 fw-bold text-white shadow-sm"
              >
                <i class="bi bi-search"></i>
                <span>Tìm phòng trống ({{ filteredRooms.length }})</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Toolbar bộ lọc nhanh & sắp xếp -->
      <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
          <!-- Bộ lọc tags -->
          <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="text-muted fw-bold d-flex align-items-center gap-1.5 me-1" style="font-size: 0.85rem;">
              <i class="bi bi-funnel text-success-custom"></i>
              Bộ lọc nhanh:
            </span>

            <button
              @click="filterBalcony = !filterBalcony"
              class="btn btn-sm rounded-pill px-3 py-1.5 transition"
              :class="filterBalcony ? 'btn-filter-active' : 'btn-filter-inactive'"
            >
              🌿 Có ban công riêng
            </button>

            <button
              @click="filterBathtub = !filterBathtub"
              class="btn btn-sm rounded-pill px-3 py-1.5 transition"
              :class="filterBathtub ? 'btn-filter-active' : 'btn-filter-inactive'"
            >
              🛁 Có bồn tắm ngâm
            </button>

            <button
              @click="filterPriceMax = filterPriceMax === 1500000 ? 0 : 1500000"
              class="btn btn-sm rounded-pill px-3 py-1.5 transition"
              :class="filterPriceMax === 1500000 ? 'btn-filter-active' : 'btn-filter-inactive'"
            >
              🏷️ Dưới 1.500.000₫
            </button>

            <button
              v-if="filterBalcony || filterBathtub || filterPriceMax > 0 || searchCapacity !== 'all'"
              @click="resetFilters"
              class="btn btn-link btn-sm text-success-custom text-decoration-none fw-bold"
            >
              Xóa bộ lọc
            </button>
          </div>

          <!-- Sắp xếp giá -->
          <div class="d-flex align-items-center gap-2 ms-auto">
            <span class="text-muted" style="font-size: 0.85rem;">Sắp xếp:</span>
            <select v-model="sortBy" class="form-select form-select-sm rounded-pill border-light-custom" style="width: 170px;">
              <option value="featured">Nổi bật nhất</option>
              <option value="price-asc">Giá thấp đến cao</option>
              <option value="price-desc">Giá cao đến thấp</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 4. Danh sách các hạng phòng (Room Cards Grid) -->
      <div class="row g-4 mb-5">
        <div
          v-for="room in sortedRooms"
          :key="room.name"
          class="col-12 col-md-6 col-xl-3"
        >
          <div class="card room-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
            <!-- Room Image Container -->
            <div class="room-image-wrap position-relative">
              <img :src="room.image" :alt="room.name" class="room-img w-100" />
              <div class="room-badge position-absolute top-0 end-0 m-3 px-2.5 py-1 rounded-pill bg-dark text-white bg-opacity-75" style="font-size: 0.75rem;">
                {{ room.area }} · {{ room.capacity }}
              </div>
              <div v-if="room.highlight" class="position-absolute top-0 start-0 m-3 px-2.5 py-1 rounded-pill bg-success-custom text-white fw-bold" style="font-size: 0.7rem;">
                {{ room.highlight }}
              </div>
            </div>

            <!-- Card Body -->
            <div class="card-body p-4 d-flex flex-column justify-content-between">
              <div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <h4 class="card-title fw-bold text-main-dark mb-0" style="font-size: 1.25rem;">
                    {{ room.name }} Room
                  </h4>
                  <div class="d-flex align-items-center text-warning" style="font-size: 0.85rem;">
                    <i class="bi bi-star-fill me-1"></i>
                    <span class="fw-bold text-dark">{{ room.rating }}</span>
                  </div>
                </div>

                <p class="text-secondary-custom mb-3 lh-sm" style="font-size: 0.85rem;">
                  {{ room.description }}
                </p>

                <!-- Amenities Tags -->
                <div class="d-flex flex-wrap gap-1.5 mb-3">
                  <span
                    v-for="amenity in room.amenities.slice(0, 3)"
                    :key="amenity"
                    class="badge bg-light text-secondary-custom border px-2 py-1"
                    style="font-size: 0.7rem; font-weight: 500;"
                  >
                    ✓ {{ amenity }}
                  </span>
                  <span v-if="room.amenities.length > 3" class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.7rem;">
                    +{{ room.amenities.length - 3 }} tiện ích
                  </span>
                </div>
              </div>

              <!-- Price & CTA Buttons -->
              <div class="pt-3 border-top mt-auto">
                <div class="d-flex align-items-baseline justify-content-between mb-3">
                  <div>
                    <span class="text-muted" style="font-size: 0.75rem;">Giá niêm yết:</span>
                    <div class="fw-bold text-success-custom" style="font-size: 1.25rem;">
                      {{ formatVND(room.basePrice) }}
                      <small class="text-muted fw-normal" style="font-size: 0.75rem;">/ đêm</small>
                    </div>
                  </div>
                  <span class="badge bg-success-subtle text-success-custom px-2 py-1" style="font-size: 0.75rem;">
                    Còn {{ room.count }} phòng trống
                  </span>
                </div>

                <div class="d-flex gap-2">
                  <button
                    @click="openRoomDetail(room)"
                    class="btn btn-outline-secondary w-50 py-2 rounded-3 text-secondary-custom fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                    style="font-size: 0.85rem;"
                  >
                    <i class="bi bi-eye"></i> Chi tiết
                  </button>
                  <button
                    @click="startBooking(room)"
                    class="btn btn-book-now w-50 py-2 rounded-3 text-white fw-semibold d-flex align-items-center justify-content-center gap-1.5"
                    style="font-size: 0.85rem;"
                  >
                    <i class="bi bi-calendar-check"></i> Đặt ngay
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Modal Xem Chi Tiết Phòng (Room Detail Modal) -->
      <div v-if="selectedRoomDetail" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
        <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-3xl">
          <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between">
            <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
              <i class="bi bi-info-circle text-success-custom"></i>
              Chi tiết hạng phòng: {{ selectedRoomDetail.name }}
            </h5>
            <button @click="selectedRoomDetail = null" class="btn-close" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
            <div class="position-relative rounded-3 overflow-hidden mb-4" style="height: 280px;">
              <img :src="selectedRoomDetail.image" class="w-100 h-100 object-fit-cover" />
              <div class="position-absolute bottom-0 start-0 m-3 px-3 py-1.5 rounded-pill bg-dark bg-opacity-75 text-white fw-bold">
                {{ formatVND(selectedRoomDetail.basePrice) }} / đêm
              </div>
            </div>

            <h6 class="fw-bold text-main-dark mb-2">Mô tả tổng quan</h6>
            <p class="text-secondary-custom mb-4">{{ selectedRoomDetail.description }}</p>

            <div class="row g-3 mb-4">
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 text-center">
                  <div class="text-muted small">Diện tích</div>
                  <div class="fw-bold text-dark mt-1">{{ selectedRoomDetail.area }}</div>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 text-center">
                  <div class="text-muted small">Sức chứa</div>
                  <div class="fw-bold text-dark mt-1">{{ selectedRoomDetail.capacity }}</div>
                </div>
              </div>
              <div class="col-4">
                <div class="p-3 bg-light rounded-3 text-center">
                  <div class="text-muted small">Số phòng trống</div>
                  <div class="fw-bold text-dark mt-1">{{ selectedRoomDetail.count }} phòng</div>
                </div>
              </div>
            </div>

            <h6 class="fw-bold text-main-dark mb-2">Trang thiết bị & Tiện nghi phòng</h6>
            <div class="row g-2 mb-4">
              <div
                v-for="am in selectedRoomDetail.amenities"
                :key="am"
                class="col-6 col-sm-4"
              >
                <div class="p-2 border rounded-3 small d-flex align-items-center gap-2">
                  <i class="bi bi-check-circle-fill text-success-custom"></i>
                  <span>{{ am }}</span>
                </div>
              </div>
            </div>

            <div class="alert alert-light border small text-muted">
              ℹ️ <strong>Chính sách nhận & trả phòng:</strong> Nhận phòng từ 14:00, trả phòng trước 12:00. Miễn phí hủy trước 48 giờ.
            </div>
          </div>
          <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2 bg-light">
            <button @click="selectedRoomDetail = null" class="btn btn-outline-secondary px-4 rounded-3">
              Đóng
            </button>
            <button @click="startBooking(selectedRoomDetail)" class="btn btn-book-now text-white px-4 rounded-3 fw-bold">
              Đặt phòng này ngay
            </button>
          </div>
        </div>
      </div>

      <!-- 6. Modal Đặt Phòng Nhanh (Booking Modal) -->
      <div v-if="bookingTargetRoom" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
        <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-xl">
          <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between bg-light">
            <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
              <i class="bi bi-calendar2-check text-success-custom"></i>
              Xác nhận Đặt phòng {{ bookingTargetRoom.name }}
            </h5>
            <button @click="bookingTargetRoom = null" class="btn-close"></button>
          </div>

          <div class="modal-body p-4" v-if="!bookingSuccess">
            <div class="p-3 rounded-3 bg-light border mb-4 d-flex align-items-center justify-content-between">
              <div>
                <div class="fw-bold text-main-dark">{{ bookingTargetRoom.name }} Room</div>
                <small class="text-muted">{{ bookingTargetRoom.area }} · {{ bookingTargetRoom.capacity }}</small>
              </div>
              <div class="fw-bold text-success-custom fs-5">
                {{ formatVND(bookingTargetRoom.basePrice) }}<small class="text-muted fs-6">/đêm</small>
              </div>
            </div>

            <!-- Ngày CheckIn / CheckOut -->
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Ngày nhận phòng</label>
                <input v-model="formBooking.checkIn" type="date" class="form-control" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Ngày trả phòng</label>
                <input v-model="formBooking.checkOut" type="date" class="form-control" />
              </div>
            </div>

            <!-- Thông tin khách -->
            <div class="mb-3">
              <label class="form-label small fw-bold text-muted">Họ và tên khách lưu trú</label>
              <input v-model="formBooking.guestName" type="text" class="form-control" placeholder="Ví dụ: Nguyễn Văn An" />
            </div>

            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Số điện thoại</label>
                <input v-model="formBooking.phone" type="tel" class="form-control" placeholder="090•••••••" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-bold text-muted">Email nhận xác nhận</label>
                <input v-model="formBooking.email" type="email" class="form-control" placeholder="email@example.com" />
              </div>
            </div>

            <!-- Bảng tính tiền dự kiến -->
            <div class="p-3 rounded-3 border bg-light mt-3">
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Số đêm lưu trú:</span>
                <span class="fw-bold text-dark">{{ calculateNights }} đêm</span>
              </div>
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Giá phòng x {{ calculateNights }} đêm:</span>
                <span>{{ formatVND(bookingTargetRoom.basePrice * calculateNights) }}</span>
              </div>
              <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Thuế & Phí phục vụ (10%):</span>
                <span>{{ formatVND(bookingTargetRoom.basePrice * calculateNights * 0.1) }}</span>
              </div>
              <div class="d-flex justify-content-between fw-bold text-main-dark pt-2 border-top">
                <span>Tổng chi phí dự kiến:</span>
                <span class="text-success-custom fs-5">{{ formatVND(calculateTotal) }}</span>
              </div>
            </div>
          </div>

          <!-- Thông báo đặt phòng thành công -->
          <div class="modal-body p-5 text-center" v-else>
            <div class="avatar-success-box mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success-custom">
              <i class="bi bi-check-lg fs-1"></i>
            </div>
            <h4 class="fw-bold text-main-dark mb-2">Đặt phòng thành công!</h4>
            <p class="text-muted small mb-4">
              Cảm ơn quý khách <strong>{{ formBooking.guestName }}</strong> đã chọn RoomBook Hotel & Resort Hội An.<br />
              Mã đặt phòng của bạn là: <strong class="text-success-custom">{{ lastBookingId }}</strong>.
            </p>
            <button @click="bookingTargetRoom = null; bookingSuccess = false" class="btn btn-book-now text-white px-4 py-2 rounded-3 fw-bold">
              Hoàn tất & Về trang chủ
            </button>
          </div>

          <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2 bg-light" v-if="!bookingSuccess">
            <button @click="bookingTargetRoom = null" class="btn btn-outline-secondary px-4 rounded-3">
              Hủy bỏ
            </button>
            <button @click="submitBooking" class="btn btn-book-now text-white px-4 rounded-3 fw-bold">
              Xác nhận đặt phòng
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'IndexHome',
  data() {
    return {
      searchCheckIn: '2026-11-15',
      searchCheckOut: '2026-11-18',
      searchCapacity: 'all',
      filterBalcony: false,
      filterBathtub: false,
      filterPriceMax: 0,
      sortBy: 'featured',

      selectedRoomDetail: null,
      bookingTargetRoom: null,
      bookingSuccess: false,
      lastBookingId: '',

      formBooking: {
        guestName: 'Nguyễn Minh Anh',
        phone: '0903 456 789',
        email: 'minhanh.nguyen@gmail.com',
        checkIn: '2026-11-15',
        checkOut: '2026-11-18',
      },

      // Dữ liệu mẫu 4 hạng phòng cao cấp chuẩn Resort
      rooms: [
        {
          name: 'Deluxe',
          basePrice: 900000,
          area: '32 m²',
          capacity: '2 người lớn',
          count: 12,
          rating: 4.9,
          highlight: 'BÁN CHẠY NHẤT',
          description: 'Không gian mở thoáng đãng nhìn ra vườn nhiệt đới xanh mát, trang bị giường King cao cấp cùng ban công riêng lộng gió.',
          image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80',
          amenities: ['Ban công hướng vườn', 'Bồn tắm ngâm đá tự nhiên', 'Smart TV 55 inch 4K', 'Máy pha cà phê Nespresso', 'Wifi tốc độ cao', 'Minibar miễn phí đồ uống'],
          hasBalcony: true,
          hasBathtub: true,
        },
        {
          name: 'Superior',
          basePrice: 750000,
          area: '26 m²',
          capacity: '2 người lớn',
          count: 8,
          rating: 4.8,
          highlight: 'TIẾT KIỆM',
          description: 'Thiết kế hiện đại thanh lịch kết hợp chất liệu gỗ sồi ấm áp, cửa sổ lớn ngập tràn ánh sáng ban mai dịu nhẹ.',
          image: 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80',
          amenities: ['Cửa sổ kính lớn toàn cảnh', 'Vòi sen đứng áp lực lớn', 'Bàn làm việc tiện nghi', 'Két sắt điện tử bảo mật', 'Máy sấy tóc cao cấp'],
          hasBalcony: false,
          hasBathtub: false,
        },
        {
          name: 'Suite',
          basePrice: 1450000,
          area: '48 m²',
          capacity: '2 người lớn + 1 trẻ em',
          count: 6,
          rating: 5.0,
          highlight: 'VIEW SÔNG HOÀI',
          description: 'Hạng phòng cao cấp sở hữu phòng khách riêng biệt, bồn sục Jacuzzi ngoài trời cùng tầm nhìn panorama trực diện sông Hoài.',
          image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
          amenities: ['Phòng khách tiếp khách riêng', 'Bồn sục Jacuzzi ngoài ban công', 'Hệ thống loa Marshall cao cấp', 'Trái cây & Rượu vang chào đón', 'Dịch vụ dọn phòng 2 lần/ngày'],
          hasBalcony: true,
          hasBathtub: true,
        },
        {
          name: 'Family',
          basePrice: 1800000,
          area: '60 m²',
          capacity: '4 người lớn + 2 trẻ em',
          count: 4,
          rating: 4.9,
          highlight: 'CHO GIA ĐÌNH',
          description: 'Giải pháp lý tưởng cho kỳ nghỉ đại gia đình với 2 phòng ngủ riêng tư kết nối liên thông, khu vực ăn uống và ban công đôi rộng rãi.',
          image: 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=800&q=80',
          amenities: ['2 phòng ngủ (1 King + 2 Single)', '2 phòng tắm riêng biệt', 'Khu vực bếp mini & bàn ăn', 'Khu vui chơi trẻ em mini', 'Bữa sáng buffet miễn phí'],
          hasBalcony: true,
          hasBathtub: true,
        },
      ],
    };
  },
  computed: {
    filteredRooms() {
      return this.rooms.filter((room) => {
        if (this.filterBalcony && !room.hasBalcony) return false;
        if (this.filterBathtub && !room.hasBathtub) return false;
        if (this.filterPriceMax > 0 && room.basePrice > this.filterPriceMax) return false;
        if (this.searchCapacity === 'couple' && room.name === 'Family') return false;
        if (this.searchCapacity === 'family' && room.name !== 'Family') return false;
        return true;
      });
    },
    sortedRooms() {
      const list = [...this.filteredRooms];
      if (this.sortBy === 'price-asc') {
        list.sort((a, b) => a.basePrice - b.basePrice);
      } else if (this.sortBy === 'price-desc') {
        list.sort((a, b) => b.basePrice - a.basePrice);
      }
      return list;
    },
    calculateNights() {
      if (!this.formBooking.checkIn || !this.formBooking.checkOut) return 1;
      const start = new Date(this.formBooking.checkIn);
      const end = new Date(this.formBooking.checkOut);
      const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
      return diff > 0 ? diff : 1;
    },
    calculateTotal() {
      if (!this.bookingTargetRoom) return 0;
      const roomTotal = this.bookingTargetRoom.basePrice * this.calculateNights;
      return roomTotal + roomTotal * 0.1;
    },
  },
  methods: {
    formatVND(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    applyFilters() {
      // Logic kích hoạt tìm kiếm
    },
    resetFilters() {
      this.filterBalcony = false;
      this.filterBathtub = false;
      this.filterPriceMax = 0;
      this.searchCapacity = 'all';
    },
    openRoomDetail(room) {
      this.selectedRoomDetail = room;
    },
    startBooking(room) {
      this.bookingTargetRoom = room;
      this.selectedRoomDetail = null;
      this.bookingSuccess = false;
      this.formBooking.checkIn = this.searchCheckIn;
      this.formBooking.checkOut = this.searchCheckOut;
    },
    submitBooking() {
      if (!this.formBooking.guestName || !this.formBooking.phone) {
        alert('Vui lòng nhập đầy đủ họ tên và số điện thoại liên hệ!');
        return;
      }
      this.lastBookingId = 'RB-' + Math.floor(100000 + Math.random() * 900000);
      this.bookingSuccess = true;
    },
  },
};
</script>

<style scoped>
.guest-home-container {
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

.border-light-custom {
  border-color: #EAE8E1;
}

/* 1. Hero Banner */
.hero-banner {
  background: linear-gradient(135deg, #17382B 0%, #1E5E49 60%, #29745B 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.badge-resort {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* 2. Search Widget */
.search-widget {
  border: 1px solid #EAE8E1;
}

.form-control-custom {
  border: none;
  border-bottom: 2px solid #EAE8E1;
  border-radius: 0;
  padding-left: 0;
  color: #172620;
}

.form-control-custom:focus {
  border-color: #1E5E49;
  box-shadow: none;
}

.btn-search-rooms {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-search-rooms:hover {
  background-color: #164636;
  transform: translateY(-1px);
}

/* 3. Filter Buttons */
.btn-filter-active {
  background-color: #EAF3EE;
  color: #1E5E49;
  border: 1.5px solid #1E5E49;
  font-weight: 600;
}

.btn-filter-inactive {
  background-color: #FAF9F5;
  color: #526059;
  border: 1px solid #E2E0D8;
}

.btn-filter-inactive:hover {
  background-color: #F0EFEA;
}

/* 4. Room Card */
.room-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  border: 1px solid #EAE8E1 !important;
}

.room-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
}

.room-image-wrap {
  height: 200px;
  overflow: hidden;
}

.room-img {
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.room-card:hover .room-img {
  transform: scale(1.05);
}

.btn-book-now {
  background-color: #1E5E49;
  border: none;
  transition: all 0.2s ease;
}

.btn-book-now:hover {
  background-color: #164636;
}

/* 5. Custom Modal */
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
