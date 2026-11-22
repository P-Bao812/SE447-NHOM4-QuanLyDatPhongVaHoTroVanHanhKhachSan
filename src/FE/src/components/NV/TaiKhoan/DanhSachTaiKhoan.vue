<template>
  <div class="custom-card h-100">
    <div class="p-3 p-lg-4 border-bottom">
      <div class="card-heading mb-3">Tài khoản nhân viên</div>

      <!-- Thanh tìm kiếm và bộ lọc trạng thái -->
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <div class="search-input-wrap">
          <i class="bi bi-search"></i>
          <input
            type="text"
            class="form-control"
            placeholder="Tên, email hoặc vai trò"
            :value="searchQuery"
            @input="$emit('update:searchQuery', $event.target.value)"
          />
        </div>

        <select
          class="form-select status-filter-select"
          :value="selectedStatus"
          @change="$emit('update:selectedStatus', $event.target.value)"
        >
          <option value="Mọi trạng thái">Mọi trạng thái</option>
          <option value="Đang hoạt động">Đang hoạt động</option>
          <option value="Đã khóa">Đã khóa</option>
        </select>
      </div>
    </div>

    <!-- Bảng danh sách tài khoản -->
    <div class="table-responsive">
      <table class="table account-table align-middle">
        <thead>
          <tr>
            <th style="width: 36%;">NHÂN VIÊN / EMAIL MẪU</th>
            <th style="width: 24%;">VAI TRÒ</th>
            <th style="width: 22%;">TRẠNG THÁI</th>
            <th style="width: 18%;">TRUY CẬP CUỐI</th>
          </tr>
        </thead>
        <tbody>
          <!-- Trạng thái đang tải dữ liệu -->
          <tr v-if="loading">
            <td colspan="4" class="text-center py-5 text-muted">
              <div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>
              <span>Đang tải danh sách tài khoản từ hệ thống...</span>
            </td>
          </tr>

          <!-- Trạng thái không có dữ liệu khớp -->
          <tr v-else-if="accounts.length === 0">
            <td colspan="4" class="text-center py-5 text-muted">
              <i class="bi bi-person-x fs-2 d-block mb-2 text-secondary"></i>
              <span>Không tìm thấy tài khoản nào phù hợp với điều kiện tìm kiếm.</span>
            </td>
          </tr>

          <!-- Danh sách các tài khoản -->
          <tr
            v-for="item in accounts"
            :key="item.id"
            :class="{ 'row-selected': activeAccountId === item.id }"
            @click="$emit('select-account', item)"
            style="cursor: pointer;"
            title="Nhấn để xem và thay đổi thông tin nhân viên này"
          >
            <td>
              <div class="d-flex align-items-center gap-2">
                <i
                  v-if="activeAccountId === item.id"
                  class="bi bi-chevron-right text-teal fw-bold"
                  style="font-size: 0.85rem;"
                ></i>
                <div>
                  <div class="employee-name">{{ item.nhanVien?.hoTen || 'Chưa cập nhật' }}</div>
                  <div class="employee-email">{{ item.tenDangNhap }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="text-dark fw-medium">{{ item.vaiTro }}</span>
            </td>
            <td>
              <span v-if="item.trangThai === 'Đang hoạt động'" class="badge-active">
                Đang hoạt động
              </span>
              <span v-else class="badge-locked">
                Đã khóa
              </span>
            </td>
            <td>
              <span class="text-muted small">{{ item.truyCapCuoi || '--' }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Ghi chú chân bảng -->
    <div class="table-footer-note">
      <i class="bi bi-shield-lock me-1"></i>
      Không hiển thị hoặc lưu mật khẩu trong danh sách quản trị.
    </div>
  </div>
</template>

<script>
export default {
  name: 'DanhSachTaiKhoan',
  props: {
    accounts: {
      type: Array,
      default: () => [],
    },
    activeAccountId: {
      type: [Number, String],
      default: null,
    },
    searchQuery: {
      type: String,
      default: '',
    },
    selectedStatus: {
      type: String,
      default: 'Mọi trạng thái',
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:searchQuery', 'update:selectedStatus', 'select-account'],
};
</script>
