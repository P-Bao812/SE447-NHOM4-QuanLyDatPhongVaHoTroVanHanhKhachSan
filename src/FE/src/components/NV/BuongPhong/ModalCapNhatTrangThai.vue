<template>
  <div v-if="show" class="custom-modal-backdrop" @click.self="$emit('close')">
    <div class="bg-white rounded-3 shadow-lg p-4 custom-modal-dialog">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">
          <i class="bi bi-arrow-repeat text-teal me-2"></i>Cập nhật trạng thái phòng
        </h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Phòng cần cập nhật:</label>
          <select class="form-select" v-model="form.maPhong" required>
            <option disabled value="">-- Chọn phòng --</option>
            <option v-for="r in allRooms" :key="r.id" :value="r.id">
              {{ r.tenPhong }} (Hiện tại: {{ formatRoomStatus(r.trangThai) }})
            </option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Trạng thái mới:</label>
          <select class="form-select" v-model="form.trangThai" required>
            <option value="Trong_Sach">Trống sạch (Sẵn sàng đón khách)</option>
            <option value="Can_Don">Cần vệ sinh (Khách đã check-out / Bẩn)</option>
            <option value="Dang_Don">Đang dọn dẹp</option>
            <option value="Cho_Kiem_Tra">Chờ kiểm tra nghiệm thu</option>
            <option value="Bao_Tri">Bảo trì (Tạm khóa bán phòng)</option>
            <option value="Dang_Su_Dung">Đang sử dụng (Có khách lưu trú)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Ghi chú trạng thái / Lý do:</label>
          <textarea
            class="form-control"
            rows="2"
            placeholder="Ví dụ: Khách vừa check-out lúc 11:30, cần dọn gấp đón khách mới..."
            v-model="form.ghiChu"
          ></textarea>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2">
          <button type="button" class="btn btn-light" @click="$emit('close')">Hủy</button>
          <button type="submit" class="btn btn-teal text-white" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalCapNhatTrangThai',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    allRooms: {
      type: Array,
      default: () => []
    },
    initialLocation: {
      type: String,
      default: ''
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit-status'],
  data() {
    return {
      form: {
        maPhong: '',
        trangThai: 'Can_Don',
        ghiChu: ''
      }
    };
  },
  watch: {
    show(val) {
      if (val) {
        let selectedId = '';
        if (this.initialLocation) {
          const clean = String(this.initialLocation).replace('Phòng ', '');
          const found = this.allRooms.find(r => r.tenPhong.includes(clean));
          if (found) selectedId = found.id;
        }
        this.form = {
          maPhong: selectedId,
          trangThai: 'Can_Don',
          ghiChu: ''
        };
      }
    }
  },
  methods: {
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
      this.$emit('submit-status', { ...this.form });
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
