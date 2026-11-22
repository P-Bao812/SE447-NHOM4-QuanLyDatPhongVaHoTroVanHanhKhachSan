<template>
  <div v-if="show" class="modal-backdrop-custom">
    <div class="modal-dialog-custom modal-lg animate-scale-up">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- HEADER MODAL -->
        <div class="modal-header bg-dark-teal text-white px-4 py-3 border-0 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="header-icon-box rounded-circle d-flex align-items-center justify-content-center bg-white-15">
              <i class="bi" :class="isEdit ? 'bi-pencil-square' : 'bi-person-plus-fill'"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold mb-0">
                {{ isEdit ? 'Chỉnh sửa hồ sơ nhân viên' : 'Thêm mới nhân viên khách sạn' }}
              </h5>
              <p class="mb-0 text-white-75 small">
                {{ isEdit ? 'Cập nhật thông tin phân bổ bộ phận, ca trực và liên hệ.' : 'Điền đầy đủ thông tin nhân sự và ca làm việc để thêm vào hệ thống.' }}
              </p>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="closeModal" :disabled="loading"></button>
        </div>

        <!-- THÔNG BÁO LỖI BACKEND NẾU CÓ -->
        <div v-if="errorMessage" class="alert alert-danger mx-4 mt-3 mb-0 rounded-3 d-flex align-items-center gap-2 py-2.5">
          <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
          <span class="small">{{ errorMessage }}</span>
        </div>

        <!-- FORM BODY -->
        <form @submit.prevent="handleSubmit" class="modal-body p-4">
          <div class="row g-3">
            <!-- 1. HỌ VÀ TÊN -->
            <div class="col-12 col-md-8">
              <label class="form-label fw-semibold text-dark small mb-1">
                Họ và tên nhân viên <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                <input
                  v-model.trim="form.hoTen"
                  type="text"
                  class="form-control border-start-0"
                  placeholder="Ví dụ: Nguyễn Văn An"
                  required
                />
              </div>
            </div>

            <!-- 2. MÃ NHÂN VIÊN -->
            <div class="col-12 col-md-4">
              <label class="form-label fw-semibold text-dark small mb-1">
                Mã nhân viên
              </label>
              <input
                v-model.trim="form.maNhanVien"
                type="text"
                class="form-control"
                :placeholder="isEdit ? 'NV-001' : 'Tự động sinh (NV-xxx)'"
                :disabled="isEdit"
              />
              <div class="form-text text-muted" style="font-size: 0.72rem;">
                {{ isEdit ? 'Mã cố định không đổi' : 'Để trống để hệ thống tự cấp' }}
              </div>
            </div>

            <!-- 3. EMAIL -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Email liên hệ <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                <input
                  v-model.trim="form.email"
                  type="email"
                  class="form-control border-start-0"
                  placeholder="nhanvien@hotel.com"
                  required
                />
              </div>
            </div>

            <!-- 4. SỐ ĐIỆN THOẠI -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Số điện thoại <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-telephone"></i></span>
                <input
                  v-model.trim="form.soDienThoai"
                  type="tel"
                  class="form-control border-start-0"
                  placeholder="0901234567"
                  required
                />
              </div>
            </div>

            <!-- 5. BỘ PHẬN LÀM VIỆC -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Bộ phận trực thuộc <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-building"></i></span>
                <select
                  v-model="form.boPhan"
                  class="form-select border-start-0"
                  required
                  @change="onDepartmentChange"
                >
                  <option v-for="dept in departmentList" :key="dept" :value="dept">
                    {{ dept }}
                  </option>
                </select>
              </div>
            </div>

            <!-- 6. CHỨC DANH / VỊ TRÍ -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Chức danh / Vị trí <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-award"></i></span>
                <input
                  v-model.trim="form.chucVu"
                  type="text"
                  list="positionSuggestions"
                  class="form-control border-start-0"
                  placeholder="Ví dụ: Nhân viên lễ tân"
                  required
                />
                <datalist id="positionSuggestions">
                  <option v-for="pos in suggestedPositions" :key="pos" :value="pos"></option>
                </datalist>
              </div>
            </div>

            <!-- 7. CA LÀM VIỆC -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Ca làm việc phân bổ <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-clock"></i></span>
                <select v-model="form.caLam" class="form-select border-start-0" required>
                  <option value="Ca sáng (06:00 - 14:00)">Ca sáng (06:00 – 14:00)</option>
                  <option value="Ca chiều (14:00 - 22:00)">Ca chiều (14:00 – 22:00)</option>
                  <option value="Ca đêm (22:00 - 06:00)">Ca đêm (22:00 – 06:00)</option>
                  <option value="Hành chính (08:00 - 17:00)">Hành chính (08:00 – 17:00)</option>
                </select>
              </div>
            </div>

            <!-- 8. TRẠNG THÁI LÀM VIỆC -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Trạng thái hoạt động
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-activity"></i></span>
                <select v-model="form.trangThai" class="form-select border-start-0">
                  <option value="Đang trong ca">Đang trong ca</option>
                  <option value="Nghỉ ca">Nghỉ ca</option>
                  <option value="Tạm nghỉ">Tạm nghỉ</option>
                  <option value="Đã nghỉ việc">Đã nghỉ việc</option>
                </select>
              </div>
            </div>

            <!-- 9. NGƯỜI QUẢN LÝ TRỰC TIẾP -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Người quản lý trực tiếp
              </label>
              <input
                v-model.trim="form.nguoiQuanLy"
                type="text"
                class="form-control"
                placeholder="Ví dụ: Trần An (Giám đốc)"
              />
            </div>

            <!-- 10. NGÀY GIA NHẬP -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Ngày gia nhập khách sạn
              </label>
              <input
                v-model="form.ngayGiaNhap"
                type="date"
                class="form-control"
              />
            </div>

            <!-- 11. CCCD / CMND -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Số CCCD / CMND
              </label>
              <input
                v-model.trim="form.cccd"
                type="text"
                class="form-control"
                placeholder="001xxxxxxxxxx"
              />
            </div>

            <!-- 12. NGÀY SINH -->
            <div class="col-12 col-md-6">
              <label class="form-label fw-semibold text-dark small mb-1">
                Ngày sinh
              </label>
              <input
                v-model="form.ngaySinh"
                type="date"
                class="form-control"
              />
            </div>

            <!-- 13. GHI CHÚ -->
            <div class="col-12">
              <label class="form-label fw-semibold text-dark small mb-1">
                Ghi chú / Kỹ năng bổ sung
              </label>
              <textarea
                v-model.trim="form.ghiChu"
                class="form-control"
                rows="2"
                placeholder="Ví dụ: Giao tiếp tiếng Anh lưu loát, chứng chỉ sơ cấp cứu..."
              ></textarea>
            </div>
          </div>

          <!-- FOOTER NÚT BẤM -->
          <div class="modal-footer px-0 pb-0 pt-4 mt-3 border-top d-flex justify-content-end gap-2">
            <button
              type="button"
              class="btn btn-outline-secondary px-4 rounded-pill"
              @click="closeModal"
              :disabled="loading"
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              class="btn btn-teal-primary px-4 rounded-pill d-inline-flex align-items-center gap-2"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <i v-else class="bi" :class="isEdit ? 'bi-check-lg' : 'bi-plus-lg'"></i>
              <span>{{ isEdit ? 'Lưu thay đổi' : 'Thêm nhân viên' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import nhanVienService from './nhanVienService.js';

export default {
  name: 'ModalThemSuaNhanVien',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    staffData: {
      type: Object,
      default: () => null
    },
    userRole: {
      type: String,
      default: 'Admin'
    }
  },
  emits: ['close', 'saved'],
  data() {
    return {
      loading: false,
      errorMessage: '',
      departmentList: [
        'Lễ tân',
        'Buồng phòng',
        'Ban Quản lý',
        'Kế toán',
        'Kỹ thuật - Bảo trì',
        'Nhà hàng & Bếp',
        'An ninh'
      ],
      departmentPositionsMap: {
        'Lễ tân': ['Nhân viên lễ tân', 'Tổ trưởng lễ tân', 'Nhân viên đón tiếp', 'Nhân viên tổng đài'],
        'Buồng phòng': ['Nhân viên buồng phòng', 'Giám sát buồng phòng', 'Tổ trưởng buồng phòng', 'Nhân viên giặt ủi'],
        'Ban Quản lý': ['Quản lý khách sạn', 'Phó giám đốc vận hành', 'Trưởng bộ phận lễ tân', 'Trưởng bộ phận buồng phòng'],
        'Kế toán': ['Kế toán viên', 'Kế toán trưởng', 'Kế toán thu mua', 'Thủ quỹ'],
        'Kỹ thuật - Bảo trì': ['Kỹ thuật viên điện nước', 'Kỹ sư bảo trì', 'Kỹ thuật viên lạnh'],
        'Nhà hàng & Bếp': ['Nhân viên phục vụ', 'Bếp trưởng', 'Bếp phó', 'Pha chế'],
        'An ninh': ['Nhân viên an ninh', 'Đội trưởng bảo vệ']
      },
      form: {
        id: null,
        maNhanVien: '',
        hoTen: '',
        email: '',
        soDienThoai: '',
        cccd: '',
        ngaySinh: '',
        boPhan: 'Lễ tân',
        chucVu: 'Nhân viên lễ tân',
        caLam: 'Ca sáng (06:00 - 14:00)',
        ngayGiaNhap: new Date().toISOString().split('T')[0],
        nguoiQuanLy: 'Trần An (Giám đốc)',
        trangThai: 'Đang trong ca',
        ghiChu: ''
      }
    };
  },
  computed: {
    suggestedPositions() {
      return this.departmentPositionsMap[this.form.boPhan] || [];
    }
  },
  watch: {
    show(newVal) {
      if (newVal) {
        this.errorMessage = '';
        if (this.isEdit && this.staffData) {
          this.initEditData(this.staffData);
        } else {
          this.resetForm();
        }
      }
    },
    staffData(newVal) {
      if (this.show && this.isEdit && newVal) {
        this.initEditData(newVal);
      }
    }
  },
  methods: {
    initEditData(data) {
      this.form = {
        id: data.id,
        maNhanVien: data.maNhanVien || '',
        hoTen: data.hoTen || '',
        email: data.email || '',
        soDienThoai: data.soDienThoai || '',
        cccd: data.cccd || '',
        ngaySinh: data.ngaySinh || '',
        boPhan: data.boPhan || 'Lễ tân',
        chucVu: data.chucVu || '',
        caLam: data.caLam || 'Ca sáng (06:00 - 14:00)',
        ngayGiaNhap: data.ngayGiaNhap || new Date().toISOString().split('T')[0],
        nguoiQuanLy: data.nguoiQuanLy || 'Trần An (Giám đốc)',
        trangThai: data.trangThai || 'Đang trong ca',
        ghiChu: data.ghiChu || ''
      };
    },
    resetForm() {
      this.form = {
        id: null,
        maNhanVien: '',
        hoTen: '',
        email: '',
        soDienThoai: '',
        cccd: '',
        ngaySinh: '',
        boPhan: 'Lễ tân',
        chucVu: 'Nhân viên lễ tân',
        caLam: 'Ca sáng (06:00 - 14:00)',
        ngayGiaNhap: new Date().toISOString().split('T')[0],
        nguoiQuanLy: 'Trần An (Giám đốc)',
        trangThai: 'Đang trong ca',
        ghiChu: ''
      };
    },
    onDepartmentChange() {
      const positions = this.departmentPositionsMap[this.form.boPhan];
      if (positions && positions.length > 0) {
        this.form.chucVu = positions[0];
      }
    },
    closeModal() {
      if (!this.loading) {
        this.$emit('close');
      }
    },
    async handleSubmit() {
      this.errorMessage = '';

      // Client-side quick checks
      if (!this.form.hoTen) {
        this.errorMessage = 'Vui lòng nhập họ và tên nhân viên.';
        return;
      }
      if (!this.form.email || !this.form.email.includes('@')) {
        this.errorMessage = 'Vui lòng nhập định dạng email hợp lệ.';
        return;
      }
      if (!this.form.soDienThoai) {
        this.errorMessage = 'Vui lòng cung cấp số điện thoại liên hệ.';
        return;
      }

      this.loading = true;
      try {
        let res;
        if (this.isEdit && this.form.id) {
          res = await nhanVienService.capNhatNhanVien(this.form.id, this.form, this.userRole);
        } else {
          res = await nhanVienService.themNhanVien(this.form, this.userRole);
        }

        if (res.status === 'success') {
          this.$emit('saved', {
            isEdit: this.isEdit,
            message: res.message || 'Thao tác nhân viên thành công!',
            data: res.data
          });
          this.closeModal();
        } else {
          this.errorMessage = res.message || 'Thao tác thất bại. Vui lòng kiểm tra lại.';
        }
      } catch (err) {
        console.error('Lỗi lưu nhân viên:', err);
        this.errorMessage = err.message || 'Lỗi kết nối máy chủ. Vui lòng thử lại sau.';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-dialog-custom {
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-content {
  max-height: 90vh;
  overflow-y: auto;
  background-color: #ffffff;
}

.bg-dark-teal {
  background: linear-gradient(135deg, #1E5E49 0%, #144636 100%);
}

.bg-white-15 {
  background-color: rgba(255, 255, 255, 0.15);
}

.header-icon-box {
  width: 42px;
  height: 42px;
  font-size: 1.25rem;
}

.btn-teal-primary {
  background-color: #1E5E49;
  color: #ffffff;
  border: 1px solid #1E5E49;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-teal-primary:hover {
  background-color: #164636;
  border-color: #164636;
  color: #ffffff;
}

.btn-teal-primary:disabled {
  background-color: #7daaa0;
  border-color: #7daaa0;
}

.form-control:focus,
.form-select:focus {
  border-color: #1E5E49;
  box-shadow: 0 0 0 0.2rem rgba(30, 94, 73, 0.15);
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

.animate-scale-up {
  animation: scaleUp 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
