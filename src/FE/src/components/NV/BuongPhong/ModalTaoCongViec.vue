<template>
  <div v-if="show" class="custom-modal-backdrop" @click.self="$emit('close')">
    <div class="bg-white rounded-3 shadow-lg p-4 custom-modal-dialog">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">Thêm công việc vận hành mới</h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Vị trí / Số phòng:</label>
          <input
            type="text"
            class="form-control"
            placeholder="Ví dụ: Phòng 502, Kho tầng 2, Sảnh..."
            v-model="form.location"
            required
          />
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Loại công việc:</label>
            <select class="form-select" v-model="form.category">
              <option value="Dọn phòng">Dọn phòng</option>
              <option value="Bảo trì">Bảo trì</option>
              <option value="Kiểm tra phòng">Kiểm tra phòng</option>
              <option value="Hàng hóa">Hàng hóa</option>
              <option value="Bàn giao ca">Bàn giao ca</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Mức ưu tiên:</label>
            <select class="form-select" v-model="form.priority">
              <option value="Cao">Cao</option>
              <option value="Vừa">Vừa</option>
              <option value="Thấp">Thấp</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Tiêu đề công việc:</label>
          <input
            type="text"
            class="form-control"
            placeholder="Ví dụ: Dọn phòng sau trả khách..."
            v-model="form.title"
            required
          />
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted">Mô tả / Ghi chú chi tiết:</label>
          <textarea
            class="form-control"
            rows="2"
            placeholder="Ví dụ: Thay ga giường, khử khuẩn, kiểm tra minibar..."
            v-model="form.note"
          ></textarea>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Người phụ trách:</label>
            <select class="form-select" v-model="form.assignee">
              <option v-for="staff in staffList" :key="staff.name" :value="staff.fullName || ('Chị ' + staff.name)">
                {{ staff.fullName || staff.name }}
              </option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold text-muted">Thời hạn / Dự kiến:</label>
            <input
              type="text"
              class="form-control"
              placeholder="Ví dụ: Trước 12:00"
              v-model="form.timeInfo"
            />
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-2">
          <button type="button" class="btn btn-light" @click="$emit('close')">Hủy</button>
          <button type="submit" class="btn btn-create-task" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            Tạo công việc
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalTaoCongViec',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    initialData: {
      type: Object,
      default: () => ({})
    },
    staffList: {
      type: Array,
      default: () => [
        { name: 'Hương', fullName: 'Chị Hương' },
        { name: 'Hoa', fullName: 'Chị Hoa' },
        { name: 'Tuấn', fullName: 'Anh Tuấn' },
        { name: 'Linh', fullName: 'Mai Linh' }
      ]
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'submit-create'],
  data() {
    return {
      form: {
        status: 'pending',
        location: '',
        category: 'Dọn phòng',
        priority: 'Cao',
        title: '',
        note: '',
        assignee: 'Chị Hương',
        timeInfo: 'Trước 14:00'
      }
    };
  },
  watch: {
    show(val) {
      if (val) {
        this.form = {
          status: this.initialData.status || 'pending',
          location: this.initialData.location || '',
          category: this.initialData.category || 'Dọn phòng',
          priority: this.initialData.priority || 'Cao',
          title: this.initialData.title || '',
          note: this.initialData.note || '',
          assignee: this.initialData.assignee || 'Chị Hương',
          timeInfo: this.initialData.timeInfo || 'Trước 14:00'
        };
      }
    }
  },
  methods: {
    handleSubmit() {
      this.$emit('submit-create', { ...this.form });
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
