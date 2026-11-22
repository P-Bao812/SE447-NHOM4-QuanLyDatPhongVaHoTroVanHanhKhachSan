<template>
  <div class="kiem-tra-phong">
    <!-- TAB NỘI DUNG: DANH SÁCH PHÒNG CHỜ KIỂM TRA -->
    <div class="bg-white rounded-3 border p-4 shadow-sm mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-bold mb-1 text-dark">
            <i class="bi bi-shield-check text-success me-2"></i>Quy trình kiểm tra chất lượng buồng phòng (Inspection)
          </h5>
          <p class="text-muted small mb-0">
            Nghiệm thu tiêu chuẩn buồng phòng sau khi dọn dẹp để bàn giao cho lễ tân sẵn sàng đón khách check-in.
          </p>
        </div>
        <button class="btn btn-teal text-white shadow-sm" @click="openModal(null)">
          <i class="bi bi-plus-lg me-1"></i> Tạo phiếu kiểm tra
        </button>
      </div>

      <div class="row g-3">
        <!-- Danh sách phòng chờ kiểm tra -->
        <div class="col-12 col-md-6" v-for="room in inspectionRooms" :key="room.id">
          <div class="border rounded-3 p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-bold fs-6 text-dark">
                <i class="bi bi-door-open-fill text-teal me-1.5"></i>{{ room.tenPhong }}
              </div>
              <span class="badge bg-info-subtle text-info-emphasis border">
                {{ formatRoomStatus(room.trangThai) }}
              </span>
            </div>
            <p class="text-muted small mb-2">{{ room.ghiChuDonDep || 'Đã dọn dẹp xong, chờ lễ tân nghiệm thu' }}</p>
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
              <small class="text-muted">Loại: {{ room.tenLoai }}</small>
              <button class="btn btn-sm btn-success" @click="openModal(room.tenPhong)">
                <i class="bi bi-clipboard-check me-1"></i> Nghiệm thu ngay
              </button>
            </div>
          </div>
        </div>

        <div v-if="inspectionRooms.length === 0" class="col-12 text-center py-5 text-muted">
          <i class="bi bi-check-all fs-2 text-success d-block mb-2"></i>
          Hiện tại không có phòng nào đang chờ kiểm tra nghiệm thu.
        </div>
      </div>
    </div>

    <!-- MODAL KIỂM TRA PHÒNG -->
    <div v-if="showModal" class="custom-modal-backdrop" @click.self="closeModal">
      <div class="bg-white rounded-3 shadow-lg p-4 custom-modal-dialog" style="width: 550px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clipboard-check text-success me-2"></i>Nghiệm thu & Kiểm tra phòng
          </h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>

        <form @submit.prevent="handleSubmit">
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label small fw-semibold text-muted">Phòng kiểm tra:</label>
              <select class="form-select" v-model="form.maPhong" required>
                <option disabled value="">-- Chọn phòng --</option>
                <option v-for="r in allRooms" :key="r.id" :value="r.id">
                  {{ r.tenPhong }} ({{ formatRoomStatus(r.trangThai) }})
                </option>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label small fw-semibold text-muted">Người kiểm tra:</label>
              <input type="text" class="form-control" v-model="form.nguoiKiemTra" required />
            </div>
          </div>

          <!-- CHECKLIST TIÊU CHUẨN KHÁCH SẠN -->
          <div class="border rounded-3 p-3 bg-light mb-3">
            <div class="small fw-bold text-dark mb-2">Checklist tiêu chuẩn nghiệm thu:</div>
            <div class="form-check mb-1.5">
              <input
                class="form-check-input"
                type="checkbox"
                id="checkGiuong"
                v-model="form.checklist.giuong"
              />
              <label class="form-check-label small" for="checkGiuong">
                Giường ngủ: Drap, chăn, vỏ gối phẳng phiu, sạch sẽ, không nhàu.
              </label>
            </div>
            <div class="form-check mb-1.5">
              <input
                class="form-check-input"
                type="checkbox"
                id="checkPhongTam"
                v-model="form.checklist.phongTam"
              />
              <label class="form-check-label small" for="checkPhongTam">
                Phòng tắm: Sạch khô ráo, gương trong, đủ bộ khăn tắm trắng sạch.
              </label>
            </div>
            <div class="form-check mb-1.5">
              <input
                class="form-check-input"
                type="checkbox"
                id="checkMinibar"
                v-model="form.checklist.minibar"
              />
              <label class="form-check-label small" for="checkMinibar">
                Đồ dùng & Minibar: Bổ sung đủ nước suối, trà, cà phê, dầu gội, bàn chải.
              </label>
            </div>
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="checkDien"
                v-model="form.checklist.thietBiDien"
              />
              <label class="form-check-label small" for="checkDien">
                Thiết bị: Điều hòa mát tốt, tivi, bình nóng lạnh, đèn hoạt động hoàn hảo.
              </label>
            </div>
          </div>

          <!-- KẾT QUẢ NGHIỆM THU -->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted d-block">Kết quả đánh giá:</label>
            <div class="btn-group w-100" role="group">
              <input
                type="radio"
                class="btn-check"
                name="inspectionResult"
                id="resDat"
                value="Dat"
                v-model="form.ketQua"
              />
              <label class="btn btn-outline-success" for="resDat">
                <i class="bi bi-check2-circle me-1"></i> ĐẠT TIÊU CHUẨN (Mở phòng đón khách)
              </label>

              <input
                type="radio"
                class="btn-check"
                name="inspectionResult"
                id="resChuaDat"
                value="Chua_Dat"
                v-model="form.ketQua"
              />
              <label class="btn btn-outline-danger" for="resChuaDat">
                <i class="bi bi-x-circle me-1"></i> CHƯA ĐẠT (Yêu cầu dọn lại)
              </label>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Ghi chú kiểm tra:</label>
            <textarea
              class="form-control"
              rows="2"
              placeholder="Nhận xét cụ thể về phòng..."
              v-model="form.ghiChu"
            ></textarea>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-2">
            <button type="button" class="btn btn-light" @click="closeModal">Hủy</button>
            <button type="submit" class="btn btn-success text-white" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              Xác nhận nghiệm thu
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'KiemTraPhong',
  props: {
    allRooms: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit-inspection'],
  data() {
    return {
      showModal: false,
      form: {
        maPhong: '',
        nguoiKiemTra: 'Mai Linh',
        ketQua: 'Dat',
        ghiChu: 'Phòng đạt tiêu chuẩn sạch sẽ, thơm mát, sẵn sàng đón khách.',
        checklist: {
          giuong: true,
          phongTam: true,
          minibar: true,
          thietBiDien: true
        }
      }
    };
  },
  computed: {
    inspectionRooms() {
      return this.allRooms.filter(r => r.trangThai === 'Cho_Kiem_Tra' || r.trangThai === 'Dang_Don');
    }
  },
  methods: {
    openModal(location) {
      let selectedId = '';
      if (location) {
        const found = this.allRooms.find(r => r.tenPhong.includes(String(location).replace('Phòng ', '')));
        if (found) selectedId = found.id;
      }
      this.form = {
        maPhong: selectedId,
        nguoiKiemTra: 'Mai Linh',
        ketQua: 'Dat',
        ghiChu: 'Phòng đạt tiêu chuẩn sạch sẽ, thơm mát, sẵn sàng đón khách.',
        checklist: {
          giuong: true,
          phongTam: true,
          minibar: true,
          thietBiDien: true
        }
      };
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    formatRoomStatus(status) {
      const map = {
        Trong_Sach: 'Trống sạch',
        Can_Don: 'Cần dọn dẹp',
        Dang_Don: 'Đang dọn dẹp',
        Cho_Kiem_Tra: 'Chờ kiểm tra',
        Bao_Tri: 'Đang bảo trì',
        Dang_Su_Dung: 'Đang có khách'
      };
      return map[status] || status;
    },
    handleSubmit() {
      this.$emit('submit-inspection', { ...this.form });
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
