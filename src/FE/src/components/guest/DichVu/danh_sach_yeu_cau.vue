<template>
  <div class="danh-sach-yeu-cau-wrap">
    <!-- TOOLBAR: BỘ LỌC TRẠNG THÁI & TÌM KIẾM -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
      <!-- Tabs lọc trạng thái: Tất cả, Tiếp nhận, Đang xử lý, Hoàn thành, Đã hủy -->
      <div class="d-flex flex-wrap align-items-center gap-1.5 p-1 bg-white border rounded-pill shadow-xs">
        <button
          v-for="st in statusOptions"
          :key="st.value"
          type="button"
          class="btn btn-sm btn-filter-pill rounded-pill"
          :class="{ active: currentStatus === st.value }"
          @click="currentStatus = st.value"
        >
          <span>{{ st.label }}</span>
          <span class="count-badge ms-1.5" :class="{ 'count-active': currentStatus === st.value }">
            {{ getCountByStatus(st.value) }}
          </span>
        </button>
      </div>

      <!-- Ô tìm kiếm trong lịch sử yêu cầu -->
      <div class="search-box position-relative" style="min-width: 280px;">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input
          v-model="searchKeyword"
          type="text"
          class="form-control form-control-sm ps-5 rounded-pill border shadow-xs"
          placeholder="Tìm theo số phòng, mã đơn, tên..."
        />
        <button
          v-if="searchKeyword"
          class="btn btn-sm btn-clear-search position-absolute top-50 end-0 translate-middle-y me-2"
          @click="searchKeyword = ''"
        >
          <i class="bi bi-x-circle-fill text-muted"></i>
        </button>
      </div>
    </div>

    <!-- DANH SÁCH YÊU CẦU DẠNG CARD CHUẨN GRID BOOTSTRAP -->
    <div v-if="filteredRequests.length > 0" class="row g-3">
      <div
        v-for="item in filteredRequests"
        :key="item.id"
        class="col-12"
      >
        <div class="request-card p-3 p-md-4 bg-white rounded-4 border shadow-xs hover-shadow transition-all">
          <div class="row g-3 align-items-center">
            <!-- CỘT 1: HÌNH THUMBNAIL & THÔNG TIN DỊCH VỤ / PHÒNG -->
            <div class="col-12 col-md-7 col-lg-8">
              <div class="d-flex align-items-start gap-3">
                <!-- Thumbnail dịch vụ -->
                <img
                  :src="item.hinhAnh || defaultImages[item.maDichVu] || defaultFallbackImage"
                  :alt="item.tenDichVu"
                  class="request-thumb rounded-3 object-fit-cover flex-shrink-0"
                  style="width: 80px; height: 80px;"
                  @error="onImgError($event, item.maDichVu)"
                />

                <div class="flex-grow-1 overflow-hidden">
                  <!-- Tag mã đơn, phòng và khách hàng -->
                  <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                    <span class="badge-code">{{ item.maYeuCau || ('#YC-' + item.id) }}</span>
                    <span class="badge-room">
                      <i class="bi bi-door-open-fill me-1"></i>Phòng {{ item.tenPhong }}
                    </span>
                    <span class="text-secondary small">
                      <i class="bi bi-person me-0.5"></i>Khách: <strong class="text-dark">{{ item.tenKhachHang || 'Khách lưu trú' }}</strong>
                    </span>
                  </div>

                  <!-- Tên dịch vụ & Số lượng -->
                  <h6 class="fw-bold text-main-dark mb-1 fs-6">
                    {{ item.tenDichVu }}
                    <span class="text-teal fw-semibold small">
                      (x{{ item.soLuong }} {{ item.donViTinh || 'Lượt' }})
                    </span>
                  </h6>

                  <!-- Thời gian yêu cầu & Mong muốn -->
                  <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-1" style="font-size: 0.8rem;">
                    <span>
                      <i class="bi bi-clock-history me-1 text-teal"></i>Đăng ký: {{ item.ngayYeuCau }}
                    </span>
                    <span>
                      <i class="bi bi-alarm-fill me-1 text-teal"></i>Mong muốn: <strong class="text-dark">{{ item.thoiGianMongMuon || 'Ngay khi có thể' }}</strong>
                    </span>
                  </div>

                  <!-- Ghi chú từ khách nếu có -->
                  <div v-if="item.ghiChu" class="request-note mt-2 p-2 rounded-2 bg-light-soft text-muted small">
                    <i class="bi bi-chat-left-quote-fill me-1.5 text-teal"></i>
                    <span>Ghi chú: <strong class="text-dark">{{ item.ghiChu }}</strong></span>
                  </div>

                  <!-- Lý do từ chối nếu có -->
                  <div v-if="item.lyDoTuChoi" class="request-note mt-2 p-2 rounded-2 bg-danger-subtle text-danger small">
                    <i class="bi bi-exclamation-circle-fill me-1.5"></i>
                    <span>Lý do từ chối: <strong class="text-danger-emphasis">{{ item.lyDoTuChoi }}</strong></span>
                  </div>

                  <!-- Lý do hủy nếu có -->
                  <div v-if="item.lyDoHuy" class="request-note mt-2 p-2 rounded-2 bg-light border text-muted small">
                    <i class="bi bi-x-circle-fill me-1.5 text-secondary"></i>
                    <span>Lý do hủy: <strong class="text-dark">{{ item.lyDoHuy }}</strong></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- CỘT 2: GIÁ TIỀN, TRẠNG THÁI & HÀNH ĐỘNG -->
            <div class="col-12 col-md-5 col-lg-4">
              <div class="d-flex flex-column align-items-start align-items-md-end justify-content-between gap-2.5 h-100">
                <!-- Tổng chi phí -->
                <div class="text-start text-md-end">
                  <div class="text-muted" style="font-size: 0.72rem; letter-spacing: 0.3px;">TỔNG CHI PHÍ DỊCH VỤ</div>
                  <div class="fw-bold text-teal fs-5 lh-1 mt-0.5">{{ formatCurrency(item.thanhTien) }}</div>
                </div>

                <!-- Badge Trạng thái hiển thị chuẩn 4 trạng thái -->
                <div class="d-flex flex-column align-items-start align-items-md-end gap-1">
                  <span class="status-badge" :class="getStatusClass(item.trangThai)">
                    <i :class="getStatusIcon(item.trangThai)"></i>
                    <span>{{ getStatusLabel(item.trangThai) }}</span>
                  </span>
                  <span v-if="isHoanThanh(item.trangThai)" class="invoice-badge-note">
                    <i class="bi bi-check2-circle me-1"></i>Đã cộng vào hóa đơn lưu trú
                  </span>
                </div>

                <!-- Hành động: Hủy yêu cầu (khách chỉ hủy khi trạng thái là Tiếp nhận / Chờ xử lý) -->
                <div class="d-flex align-items-center gap-2 flex-wrap justify-content-start justify-content-md-end pt-1">
                  <button
                    v-if="isTiepNhan(item.trangThai)"
                    type="button"
                    class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 d-inline-flex align-items-center gap-1 shadow-xs"
                    @click="promptCancel(item)"
                    title="Hủy yêu cầu này"
                  >
                    <i class="bi bi-x-circle"></i>
                    <span>Hủy yêu cầu</span>
                  </button>

                  <!-- Control cập nhật trạng thái nếu là nhân viên -->
                  <div v-if="isStaff" class="d-inline-flex align-items-center gap-1">
                    <label class="text-muted small d-none d-lg-inline" style="font-size: 0.75rem;">Tiến độ:</label>
                    <select
                      class="form-select form-select-sm status-select-control rounded-pill shadow-xs"
                      :value="normalizeStatus(item.trangThai)"
                      @change="handleStatusChange(item.id, $event.target.value)"
                      title="Cập nhật tiến độ xử lý"
                    >
                      <option value="Tiep_Nhan">⏳ Tiếp nhận</option>
                      <option value="Dang_Xu_Ly">⚙️ Đang xử lý</option>
                      <option value="Hoan_Thanh">✅ Hoàn thành (+Hóa đơn)</option>
                      <option value="Da_Huy">❌ Đã hủy</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-else class="text-center py-5 bg-white rounded-4 border p-4 shadow-xs">
      <div class="empty-icon-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
        <i class="bi bi-inbox text-muted fs-2"></i>
      </div>
      <h6 class="fw-bold text-main-dark mb-1">
        {{ currentStatus !== 'Tất cả' ? `Không có yêu cầu nào ở trạng thái "${getStatusLabel(currentStatus)}"` : 'Chưa có yêu cầu dịch vụ nào' }}
      </h6>
      <p class="text-muted small mb-3">
        Quý khách có thể lựa chọn dịch vụ từ danh mục để đăng ký sử dụng ngay tại phòng nghỉ.
      </p>
      <button
        type="button"
        class="btn btn-teal-primary btn-sm rounded-pill px-3.5 py-2 shadow-xs"
        @click="$emit('switchToServices')"
      >
        <i class="bi bi-grid-fill me-1.5"></i>Xem danh mục dịch vụ
      </button>
    </div>

    <!-- MODAL XÁC NHẬN HỦY DÀNH CHO KHÁCH HÀNG -->
    <div v-if="cancelModal.show" class="modal-backdrop-custom">
      <div class="modal-dialog-custom modal-sm-custom animate-scale-up">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="modal-header bg-danger-subtle border-0 px-4 py-3 d-flex align-items-center justify-content-between">
            <h6 class="modal-title fw-bold mb-0 text-danger-emphasis">
              <i class="bi bi-exclamation-triangle-fill me-1.5 text-danger"></i>Xác nhận hủy yêu cầu
            </h6>
            <button type="button" class="btn-close" @click="cancelModal.show = false"></button>
          </div>
          <div class="modal-body p-4 bg-white">
            <p class="small text-muted mb-3">
              Quý khách có chắc chắn muốn hủy yêu cầu <strong class="text-dark">{{ cancelModal.item?.maYeuCau || ('#YC-' + cancelModal.item?.id) }}</strong>
              (dịch vụ <strong class="text-teal">{{ cancelModal.item?.tenDichVu }}</strong> tại Phòng {{ cancelModal.item?.tenPhong }}) không?
            </p>
            <label class="form-label small fw-semibold text-dark mb-1">Lý do hủy (tùy chọn):</label>
            <input
              v-model="cancelModal.lyDo"
              type="text"
              class="form-control form-control-sm rounded-3 mb-3"
              placeholder="Ví dụ: Đổi ý / Không có mặt tại phòng..."
            />
            <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
              <button
                type="button"
                class="btn btn-outline-secondary btn-sm px-3 rounded-pill"
                @click="cancelModal.show = false"
              >
                Không hủy
              </button>
              <button
                type="button"
                class="btn btn-danger btn-sm px-3.5 rounded-pill shadow-xs"
                @click="executeCancel"
              >
                <i class="bi bi-trash-fill me-1"></i>Xác nhận hủy
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
  name: 'DanhSachYeuCauDichVu',
  props: {
    requests: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    isStaff: {
      type: Boolean,
      default: false
    }
  },
  emits: ['cancel', 'updateStatus', 'switchToServices'],
  data() {
    return {
      currentStatus: 'Tất cả',
      searchKeyword: '',
      statusOptions: [
        { label: 'Tất cả', value: 'Tất cả' },
        { label: 'Tiếp nhận', value: 'Tiep_Nhan' },
        { label: 'Đang xử lý', value: 'Dang_Xu_Ly' },
        { label: 'Hoàn thành', value: 'Hoan_Thanh' },
        { label: 'Đã hủy', value: 'Da_Huy' }
      ],
      cancelModal: {
        show: false,
        item: null,
        lyDo: ''
      },
      defaultImages: {
        1: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=80',
        2: 'https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=800&q=80',
        3: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        4: 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=800&q=80',
        5: 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80'
      },
      defaultFallbackImage: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80'
    };
  },
  computed: {
    filteredRequests() {
      let list = this.requests;

      // 1. Lọc theo trạng thái
      if (this.currentStatus !== 'Tất cả') {
        list = list.filter(r => this.normalizeStatus(r.trangThai) === this.currentStatus);
      }

      // 2. Lọc theo từ khóa tìm kiếm
      if (this.searchKeyword.trim()) {
        const kw = this.searchKeyword.trim().toLowerCase();
        list = list.filter(r =>
          (r.maYeuCau && r.maYeuCau.toLowerCase().includes(kw)) ||
          (r.tenPhong && r.tenPhong.toLowerCase().includes(kw)) ||
          (r.tenKhachHang && r.tenKhachHang.toLowerCase().includes(kw)) ||
          (r.tenDichVu && r.tenDichVu.toLowerCase().includes(kw))
        );
      }

      return list;
    }
  },
  methods: {
    /**
     * Chuẩn hóa trạng thái đầy đủ 4 nhóm chuẩn
     */
    normalizeStatus(status) {
      if (!status) return 'Tiep_Nhan';
      const s = String(status).trim().toLowerCase();
      // 1. Nhóm Tiếp nhận
      if (
        s === 'tiep_nhan' ||
        s === 'cho_tiep_nhan' ||
        s === 'cho_xu_ly' ||
        s === 'tiếp nhận' ||
        s === 'chờ tiếp nhận' ||
        s === 'chờ xử lý' ||
        s === 'da_tiep_nhan' ||
        s === 'đã tiếp nhận' ||
        s === 'pending'
      ) {
        return 'Tiep_Nhan';
      }
      // 2. Nhóm Đang xử lý
      if (
        s === 'dang_xu_ly' ||
        s === 'dang_thuc_hien' ||
        s === 'da_nhan_viec' ||
        s === 'đang xử lý' ||
        s === 'đang thực hiện' ||
        s === 'đã nhận việc' ||
        s === 'doing' ||
        s === 'processing' ||
        s === 'in_progress'
      ) {
        return 'Dang_Xu_Ly';
      }
      // 3. Nhóm Hoàn thành
      if (
        s === 'hoan_thanh' ||
        s === 'da_hoan_thanh' ||
        s === 'hoan_tat' ||
        s === 'đã hoàn tất' ||
        s === 'hoàn thành' ||
        s === 'đã hoàn thành' ||
        s === 'done' ||
        s === 'completed'
      ) {
        return 'Hoan_Thanh';
      }
      // 4. Nhóm Đã hủy / Từ chối
      if (
        s === 'da_huy' ||
        s === 'huy' ||
        s === 'đã hủy' ||
        s === 'da_tu_choi' ||
        s === 'tu_choi' ||
        s === 'từ chối' ||
        s === 'đã từ chối' ||
        s === 'cancelled' ||
        s === 'cancel' ||
        s === 'rejected'
      ) {
        return 'Da_Huy';
      }
      return status;
    },

    getCountByStatus(status) {
      if (status === 'Tất cả') return this.requests.length;
      return this.requests.filter(r => this.normalizeStatus(r.trangThai) === status).length;
    },

    isTiepNhan(status) {
      return this.normalizeStatus(status) === 'Tiep_Nhan';
    },

    isHoanThanh(status) {
      return this.normalizeStatus(status) === 'Hoan_Thanh';
    },

    getStatusLabel(status) {
      const s = String(status || '').trim().toLowerCase();
      if (s === 'da_tu_choi' || s === 'tu_choi' || s === 'đã từ chối' || s === 'từ chối') {
        return 'Đã từ chối';
      }
      if (s === 'da_huy' || s === 'đã hủy' || s === 'cancelled') {
        return 'Đã hủy';
      }
      const norm = this.normalizeStatus(status);
      switch (norm) {
        case 'Tiep_Nhan':
          return 'Tiếp nhận';
        case 'Dang_Xu_Ly':
          return 'Đang xử lý';
        case 'Hoan_Thanh':
          return 'Hoàn thành';
        case 'Da_Huy':
          return 'Đã hủy';
        default:
          return status || 'Tiếp nhận';
      }
    },

    getStatusClass(status) {
      const s = String(status || '').trim().toLowerCase();
      if (s === 'da_tu_choi' || s === 'tu_choi' || s === 'đã từ chối' || s === 'từ chối') {
        return 'status-badge-da-tu-choi';
      }
      const norm = this.normalizeStatus(status);
      switch (norm) {
        case 'Tiep_Nhan':
          return 'status-badge-tiep-nhan';
        case 'Dang_Xu_Ly':
          return 'status-badge-dang-xu-ly';
        case 'Hoan_Thanh':
          return 'status-badge-hoan-thanh';
        case 'Da_Huy':
          return 'status-badge-da-huy';
        default:
          return 'status-badge-tiep-nhan';
      }
    },

    getStatusIcon(status) {
      const s = String(status || '').trim().toLowerCase();
      if (s === 'da_tu_choi' || s === 'tu_choi') {
        return 'bi bi-slash-circle-fill';
      }
      const norm = this.normalizeStatus(status);
      switch (norm) {
        case 'Tiep_Nhan':
          return 'bi bi-clock-fill';
        case 'Dang_Xu_Ly':
          return 'bi bi-arrow-repeat spin-slow';
        case 'Hoan_Thanh':
          return 'bi bi-check-circle-fill';
        case 'Da_Huy':
          return 'bi bi-x-circle-fill';
        default:
          return 'bi bi-info-circle-fill';
      }
    },

    promptCancel(item) {
      this.cancelModal.item = item;
      this.cancelModal.lyDo = '';
      this.cancelModal.show = true;
    },

    executeCancel() {
      if (this.cancelModal.item) {
        this.$emit('cancel', {
          id: this.cancelModal.item.id,
          lyDoHuy: this.cancelModal.lyDo || 'Khách hủy yêu cầu'
        });
      }
      this.cancelModal.show = false;
    },

    handleStatusChange(id, newStatus) {
      this.$emit('updateStatus', { id, trangThai: newStatus });
    },

    onImgError(event, serviceId) {
      if (this.defaultImages[serviceId]) {
        event.target.src = this.defaultImages[serviceId];
      } else {
        event.target.src = this.defaultFallbackImage;
      }
    },

    formatCurrency(val) {
      if (!val && val !== 0) return '0 đ';
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    }
  }
};
</script>

<style scoped>
.text-teal {
  color: #1E5E49 !important;
}

.text-main-dark {
  color: #172620 !important;
}

.request-card {
  border-color: #EAE8E1 !important;
  transition: all 0.22s ease;
}

.request-card:hover {
  border-color: #B5D4C7 !important;
  box-shadow: 0 6px 18px rgba(30, 94, 73, 0.08) !important;
}

.badge-code {
  background-color: #F0F4F2;
  color: #2D3A34;
  font-size: 0.74rem;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #DCE4E0;
}

.badge-room {
  background-color: #EAF3EE;
  color: #1E5E49;
  font-size: 0.74rem;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 6px;
  border: 1px solid #C7DFD4;
}

.bg-light-soft {
  background-color: #F8FAF9;
  border: 1px solid #EBF0ED;
}

/* 4 Trạng thái thiết kế sang trọng, độ tương phản cao */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 14px;
  border-radius: 20px;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.2px;
}

/* 1. Tiếp nhận: Xanh dương chuẩn khách sạn */
.status-badge-tiep-nhan {
  background-color: #E0F2FE;
  color: #0369A1;
  border: 1px solid #BAE6FD;
}

/* 2. Đang xử lý: Vàng cam ấm áp */
.status-badge-dang-xu-ly {
  background-color: #FEF3C7;
  color: #B45309;
  border: 1px solid #FDE68A;
}

/* 3. Hoàn thành: Xanh lá đậm chuẩn thương hiệu */
.status-badge-hoan-thanh {
  background-color: #DCFCE7;
  color: #15803D;
  border: 1px solid #86EFAC;
}

/* 4. Đã hủy: Xám dịu */
.status-badge-da-huy {
  background-color: #F1F5F9;
  color: #475569;
  border: 1px solid #CBD5E1;
}

/* 5. Đã từ chối: Đỏ hồng */
.status-badge-da-tu-choi {
  background-color: #FFE4E6;
  color: #BE123C;
  border: 1px solid #FECDD3;
}

.invoice-badge-note {
  font-size: 0.74rem;
  color: #15803D;
  font-weight: 500;
}

.status-select-control {
  border-color: #D8DFDB;
  font-size: 0.78rem;
  font-weight: 500;
  padding: 4px 10px;
  background-color: #FAF9F5;
  color: #374151;
  cursor: pointer;
}

.status-select-control:focus {
  border-color: #1E5E49;
  box-shadow: 0 0 0 2px rgba(30, 94, 73, 0.15);
}

/* Filter pills */
.btn-filter-pill {
  border: none;
  background: transparent;
  color: #5C6862;
  font-size: 0.82rem;
  font-weight: 500;
  padding: 5px 14px;
  transition: all 0.15s;
}

.btn-filter-pill:hover {
  color: #1E5E49;
  background-color: #F4F8F6;
}

.btn-filter-pill.active {
  background-color: #1E5E49;
  color: #ffffff;
}

.count-badge {
  background-color: #EAE8E1;
  color: #4A5650;
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 10px;
}

.count-active {
  background-color: rgba(255, 255, 255, 0.28);
  color: #ffffff;
}

.btn-clear-search {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.empty-icon-circle {
  width: 64px;
  height: 64px;
  background-color: #F4F8F6;
  border-radius: 50%;
}

.btn-teal-primary {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 600;
}

.btn-teal-primary:hover {
  background-color: #164637;
  color: #ffffff;
}

.spin-slow {
  display: inline-block;
  animation: spin 3s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1060;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 450px;
}

.animate-scale-up {
  animation: scaleUp 0.18s ease-out;
}

@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.96);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
