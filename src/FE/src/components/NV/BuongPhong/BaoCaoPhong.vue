<template>
  <div v-if="show" class="custom-modal-backdrop" @click.self="$emit('close')">
    <div class="bg-white rounded-3 shadow-lg p-4 custom-modal-dialog" style="width: 580px;">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">
          <i class="bi bi-clipboard2-check-fill text-teal me-2"></i>Báo cáo hoàn thành dọn phòng & bàn giao
        </h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>

      <form @submit.prevent="handleSubmit">
        <!-- Thông tin cơ bản -->
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Vị trí / Phòng:</label>
            <input type="text" class="form-control fw-bold" v-model="formData.location" readonly />
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Người thực hiện:</label>
            <input type="text" class="form-control" v-model="formData.assignee" required />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Ghi chú bàn giao cho lễ tân:</label>
          <textarea
            class="form-control"
            rows="2"
            placeholder="Ví dụ: Đã thay bộ ga mới, bổ sung 2 chai nước suối, sàn nhà sạch bóng..."
            v-model="formData.ghiChuBanGiao"
            required
          ></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Trạng thái phòng sau khi dọn:</label>
          <select class="form-select" v-model="formData.tinhTrangPhong">
            <option value="Cho_Kiem_Tra">Chờ kiểm tra nghiệm thu (Lễ tân duyệt trước khi đón khách)</option>
            <option value="Trong_Sach">Trống sạch (Sẵn sàng mở đón khách ngay)</option>
          </select>
        </div>

        <!-- KÊ KHAI PHÁT SINH MINIBAR NẾU KHÁCH DÙNG -->
        <div class="border rounded-3 p-3 bg-light mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-dark">
              <i class="bi bi-cup-straw text-primary me-1"></i> Phát sinh Minibar (Nếu khách đã dùng):
            </span>
            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" @click="addMinibarItem">
              <i class="bi bi-plus-circle me-1"></i> Thêm món
            </button>
          </div>

          <div v-for="(item, idx) in formData.minibarList" :key="idx" class="row g-2 mb-2 align-items-center">
            <div class="col-7">
              <select class="form-select form-select-sm" v-model="item.maSanPham" @change="onSelectMinibar(item)">
                <option value="">-- Chọn sản phẩm minibar --</option>
                <option v-for="sp in minibarProducts" :key="sp.id" :value="sp.id">
                  {{ sp.tenSanPham }} ({{ Number(sp.donGia).toLocaleString('vi-VN') }}đ)
                </option>
              </select>
            </div>
            <div class="col-3">
              <input
                type="number"
                min="1"
                class="form-control form-control-sm"
                placeholder="SL"
                v-model.number="item.soLuong"
              />
            </div>
            <div class="col-2 text-end">
              <button
                type="button"
                class="btn btn-sm btn-outline-danger py-0 px-1.5"
                @click="removeMinibarItem(idx)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>

          <div v-if="formData.minibarList.length === 0" class="text-muted small fst-italic">
            Không có phát sinh đồ minibar (Khách không dùng hoặc đã bổ sung đủ).
          </div>
        </div>

        <!-- KÊ KHAI TÀI SẢN HƯ HỎNG NẾU CÓ -->
        <div class="border rounded-3 p-3 bg-light mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-dark">
              <i class="bi bi-exclamation-triangle text-danger me-1"></i> Báo cáo hư hỏng tài sản (Nếu có):
            </span>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" @click="addTaiSanItem">
              <i class="bi bi-plus-circle me-1"></i> Thêm tài sản hư
            </button>
          </div>

          <div v-for="(item, idx) in formData.taiSanList" :key="idx" class="row g-2 mb-2">
            <div class="col-5">
              <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Tên tài sản (vd: Khăn rách, Ly vỡ...)"
                v-model="item.tenTaiSan"
              />
            </div>
            <div class="col-4">
              <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Mô tả sự cố"
                v-model="item.ghiChu"
              />
            </div>
            <div class="col-2">
              <input
                type="number"
                class="form-control form-control-sm"
                placeholder="Bồi thường"
                v-model.number="item.soTienBoiThuong"
              />
            </div>
            <div class="col-1 text-end">
              <button
                type="button"
                class="btn btn-sm btn-outline-danger py-0 px-1"
                @click="removeTaiSanItem(idx)"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>

          <div v-if="formData.taiSanList.length === 0" class="text-muted small fst-italic">
            Tất cả tài sản và trang thiết bị trong phòng đều bình thường, không hư hại.
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2">
          <button type="button" class="btn btn-light" @click="$emit('close')">Hủy</button>
          <button type="submit" class="btn btn-teal text-white" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            Gửi báo cáo & Hoàn tất
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaoCaoPhong',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    taskOrRoom: {
      type: Object,
      default: () => ({})
    },
    minibarProducts: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit-report'],
  data() {
    return {
      formData: {
        taskId: null,
        maPhong: null,
        location: '',
        assignee: 'Chị Hương',
        tinhTrangPhong: 'Cho_Kiem_Tra',
        ghiChuBanGiao: 'Đã dọn phòng sạch sẽ, thay ga gối, khử khuẩn và lau khô nhà tắm.',
        minibarList: [],
        taiSanList: []
      }
    };
  },
  watch: {
    taskOrRoom: {
      immediate: true,
      handler(val) {
        if (val) {
          this.formData = {
            taskId: val.id || null,
            maPhong: val.maPhong || null,
            location: val.location || val.tenPhong || 'Phòng',
            assignee: val.assignee || 'Chị Hương',
            tinhTrangPhong: 'Cho_Kiem_Tra',
            ghiChuBanGiao: 'Đã dọn dẹp sạch sẽ, khử khuẩn và bàn giao lễ tân.',
            minibarList: [],
            taiSanList: []
          };
        }
      }
    }
  },
  methods: {
    addMinibarItem() {
      const defaultSp = this.minibarProducts[0] || { id: 1, donGia: 15000 };
      this.formData.minibarList.push({
        maSanPham: defaultSp.id,
        soLuong: 1,
        donGia: defaultSp.donGia
      });
    },
    removeMinibarItem(index) {
      this.formData.minibarList.splice(index, 1);
    },
    onSelectMinibar(item) {
      const found = this.minibarProducts.find(p => p.id === item.maSanPham);
      if (found) {
        item.donGia = found.donGia;
      }
    },
    addTaiSanItem() {
      this.formData.taiSanList.push({
        tenTaiSan: 'Khăn tắm lớn',
        ghiChu: 'Dính vết bẩn khó giặt',
        soTienBoiThuong: 50000
      });
    },
    removeTaiSanItem(index) {
      this.formData.taiSanList.splice(index, 1);
    },
    handleSubmit() {
      this.$emit('submit-report', { ...this.formData });
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
