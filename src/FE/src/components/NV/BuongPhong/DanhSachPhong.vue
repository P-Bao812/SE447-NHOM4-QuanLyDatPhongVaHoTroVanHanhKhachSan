<template>
  <div class="danh-sach-phong bg-white rounded-3 border p-4 shadow-sm mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
      <div>
        <h5 class="fw-bold mb-1 text-dark">
          <i class="bi bi-bucket-fill text-teal me-2"></i>Danh sách phòng cần vệ sinh & dọn dẹp
        </h5>
        <p class="text-muted small mb-0">
          Theo dõi danh sách các phòng vừa trả phòng (check-out) hoặc phòng bẩn cần phân công nhân sự vệ sinh.
        </p>
      </div>
      <button class="btn btn-sm btn-outline-secondary" @click="$emit('refresh')">
        <i class="bi bi-arrow-clockwise me-1"></i> Làm mới danh sách
      </button>
    </div>

    <!-- Danh sách phòng dạng Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light small text-uppercase fw-semibold text-muted">
          <tr>
            <th scope="col">Số phòng</th>
            <th scope="col">Loại phòng & Tầng</th>
            <th scope="col">Trạng thái phòng</th>
            <th scope="col">Ghi chú trả phòng / Yêu cầu</th>
            <th scope="col">Tình trạng công việc</th>
            <th scope="col">Phụ trách</th>
            <th scope="col" class="text-end">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="room in rooms" :key="room.id">
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="icon-square-sm bg-light text-teal rounded-2 p-2">
                  <i class="bi bi-door-closed-fill"></i>
                </div>
                <div>
                  <div class="fw-bold text-dark">{{ room.tenPhong }}</div>
                  <small class="text-muted">Tầng {{ room.tang }}</small>
                </div>
              </div>
            </td>
            <td>
              <div class="fw-medium text-dark">{{ room.tenLoai }}</div>
              <small class="text-muted">An Nhiên Boutique</small>
            </td>
            <td>
              <span :class="getRoomStatusBadgeClass(room.trangThai)">
                {{ formatRoomStatus(room.trangThai) }}
              </span>
            </td>
            <td>
              <div class="small text-dark">{{ room.ghiChuDonDep || 'Cần vệ sinh sau trả phòng' }}</div>
              <small class="text-muted">Check-out: {{ room.thoiGianCheckOut || 'Hôm nay' }}</small>
            </td>
            <td>
              <span
                class="badge"
                :class="room.hasActiveTask ? 'bg-primary-subtle text-primary border' : 'bg-warning-subtle text-warning-emphasis border'"
              >
                {{ room.hasActiveTask ? (room.taskStatus === 'doing' ? 'Đang dọn dẹp' : 'Đã giao việc') : 'Chưa phân công' }}
              </span>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1.5" v-if="room.assignee && room.assignee !== 'Chưa phân công'">
                <div class="avatar-circle avatar-sm" style="width: 22px; height: 22px; font-size: 0.6rem;">
                  {{ getInitials(room.assignee) }}
                </div>
                <span class="small">{{ room.assignee }}</span>
              </div>
              <span v-else class="text-muted small fst-italic">Chưa có</span>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <!-- Nếu chưa có việc thì tạo việc dọn ngay -->
                <button
                  v-if="!room.hasActiveTask"
                  class="btn btn-outline-teal"
                  @click="$emit('assign-task', room)"
                  title="Giao việc dọn phòng"
                >
                  <i class="bi bi-person-plus-fill me-1"></i> Giao việc
                </button>

                <!-- Báo cáo hoàn thành nếu đang dọn -->
                <button
                  v-else
                  class="btn btn-outline-success"
                  @click="$emit('open-report', { location: room.tenPhong, maPhong: room.id, assignee: room.assignee })"
                  title="Báo cáo hoàn thành"
                >
                  <i class="bi bi-check-lg me-1"></i> Báo cáo
                </button>

                <!-- Đổi trạng thái nhanh -->
                <button
                  class="btn btn-outline-secondary"
                  @click="$emit('open-status', room.tenPhong)"
                  title="Đổi trạng thái"
                >
                  <i class="bi bi-arrow-repeat"></i>
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="rooms.length === 0">
            <td colspan="7" class="text-center py-4 text-muted">
              <i class="bi bi-emoji-smile fs-3 d-block text-success mb-1"></i>
              Tuyệt vời! Hiện tại không có phòng nào đang chờ dọn dẹp.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DanhSachPhong',
  props: {
    rooms: {
      type: Array,
      default: () => []
    }
  },
  emits: ['assign-task', 'open-report', 'open-status', 'refresh'],
  methods: {
    getRoomStatusBadgeClass(status) {
      switch (status) {
        case 'Trong_Sach': return 'badge bg-success-subtle text-success border';
        case 'Can_Don': return 'badge bg-danger-subtle text-danger border';
        case 'Dang_Don': return 'badge bg-warning-subtle text-warning-emphasis border';
        case 'Cho_Kiem_Tra': return 'badge bg-info-subtle text-info border';
        case 'Bao_Tri': return 'badge bg-secondary-subtle text-secondary border';
        case 'Dang_Su_Dung': return 'badge bg-primary-subtle text-primary border';
        default: return 'badge bg-light text-dark border';
      }
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
    getInitials(name) {
      if (!name) return 'NV';
      if (name.includes('Hương')) return 'HƯ';
      if (name.includes('Hoa')) return 'HO';
      if (name.includes('Tuấn')) return 'TU';
      if (name.includes('Linh')) return 'ML';
      return name.substring(0, 2).toUpperCase();
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
