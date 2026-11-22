<template>
  <div class="buong-phong-container container-fluid px-3 px-lg-5 py-4">
    <!-- TIÊU ĐỀ TRANG & THANH HÀNH ĐỘNG -->
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
      <div>
        <h2 class="fw-bold mb-1 text-dark page-title">Vận hành hằng ngày</h2>
        <p class="text-muted mb-0" style="font-size: 0.92rem;">
          Phối hợp dọn phòng, bảo trì, kiểm tra chất lượng và bàn giao buồng phòng trong ca làm việc.
        </p>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Nút kiểm tra phòng nhanh -->
        <button class="btn btn-outline-success shadow-sm btn-action-sm" @click="openInspectionModal(null)">
          <i class="bi bi-clipboard-check"></i>
          <span>Kiểm tra phòng</span>
        </button>

        <!-- Nút cập nhật trạng thái phòng -->
        <button class="btn btn-outline-primary shadow-sm btn-action-sm" @click="openRoomStatusModal(null)">
          <i class="bi bi-arrow-repeat"></i>
          <span>Đổi trạng thái phòng</span>
        </button>

        <!-- Nút Thông báo Buồng phòng -->
        <div class="position-relative">
          <button
            type="button"
            class="btn btn-outline-success shadow-sm btn-action-sm position-relative d-inline-flex align-items-center gap-1.5"
            @click="toggleNotifyDropdown"
            title="Thông báo bộ phận buồng phòng"
          >
            <i class="bi bi-bell-fill"></i>
            <span>Thông báo</span>
            <span v-if="unreadNotifyCount > 0" class="badge bg-danger rounded-pill px-1.5 py-0.5" style="font-size: 0.72rem;">
              {{ unreadNotifyCount }}
            </span>
          </button>

          <!-- Dropdown thông báo -->
          <div
            v-if="showNotifyDropdown"
            class="notify-dropdown-menu shadow-lg rounded-4 p-3 bg-white border position-absolute end-0 mt-2 z-3"
            style="width: 360px; max-width: 90vw;"
          >
            <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
              <strong class="text-dark small d-flex align-items-center gap-1.5">
                <i class="bi bi-bell-fill text-success"></i>
                <span>Thông báo buồng phòng ({{ unreadNotifyCount }})</span>
              </strong>
              <button
                v-if="unreadNotifyCount > 0"
                class="btn btn-link btn-sm text-success p-0 text-decoration-none"
                style="font-size: 0.75rem;"
                @click="markAllNotificationsAsRead"
              >
                Đã đọc tất cả
              </button>
            </div>

            <div v-if="notifications.length === 0" class="text-center py-4 text-muted small">
              <i class="bi bi-bell-slash d-block fs-3 mb-1 text-secondary"></i>
              <span>Chưa có thông báo mới nào</span>
            </div>

            <div v-else class="notify-list-scroll d-flex flex-column gap-2" style="max-height: 320px; overflow-y: auto;">
              <div
                v-for="item in notifications"
                :key="item.id"
                class="p-2.5 rounded-3 border transition-all cursor-pointer"
                :class="{ 'bg-light border-success': item.trangThai === 'Chua_Xem', 'bg-white': item.trangThai !== 'Chua_Xem' }"
                @click="handleNotificationClick(item)"
              >
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div class="fw-semibold text-dark small lh-sm">{{ item.tieuDe }}</div>
                  <small class="text-muted flex-shrink-0" style="font-size: 0.7rem;">{{ item.thoiGian }}</small>
                </div>
                <div class="text-secondary mt-1 small" style="font-size: 0.78rem;">
                  {{ item.noiDung }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Nút Thêm công việc -->
        <button class="btn btn-create-task shadow-sm" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i>
          <span>Thêm công việc</span>
        </button>
      </div>
    </div>

    <!-- BANNER CẢNH BÁO / YÊU CẦU DỊCH VỤ MỚI TỪ KHÁCH HÀNG -->
    <div v-if="guestServiceAlerts.length > 0" class="mb-4">
      <div
        v-for="alert in guestServiceAlerts"
        :key="alert.id"
        class="guest-service-alert-box p-3 rounded-4 bg-white border border-warning shadow-xs mb-2 d-flex flex-wrap align-items-center justify-content-between gap-3"
      >
        <div class="d-flex align-items-center gap-3">
          <div class="alert-icon-circle rounded-circle d-flex align-items-center justify-content-center bg-warning text-dark flex-shrink-0 shadow-xs" style="width: 44px; height: 44px;">
            <i class="bi bi-bell-fill fs-5"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-danger rounded-pill px-2.5 py-1 text-uppercase fw-bold" style="font-size: 0.68rem;">
                Yêu cầu mới từ khách
              </span>
              <strong class="text-dark">{{ alert.tieuDe }}</strong>
              <small class="text-muted">({{ alert.thoiGian }})</small>
            </div>
            <p class="text-muted small mb-0 mt-0.5">
              {{ alert.noiDung }}
            </p>
          </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-shrink-0">
          <button
            type="button"
            class="btn btn-sm btn-success rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs"
            @click="handleAcknowledgeAlert(alert)"
          >
            <i class="bi bi-check2-circle"></i>
            <span>Xem & Nhận việc</span>
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1"
            @click="dismissAlert(alert.id)"
            title="Đã xem"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- METRIC CARDS: TIẾN ĐỘ & CA TRỰC -->
    <div class="row g-3 mb-4">
      <!-- Card 1: Tiến độ hoàn thành công việc -->
      <div class="col-12 col-lg-7 col-xl-6">
        <div class="metric-card d-flex align-items-center gap-3">
          <div class="icon-square-light flex-shrink-0">
            <i class="bi bi-check2-all"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                {{ completedTasksCount }} / {{ tasks.length }} công việc hoàn thành
              </div>
              <div class="fw-bold text-teal" style="font-size: 0.92rem;">
                {{ completionPercentage }}%
              </div>
            </div>
            <!-- Progress bar -->
            <div class="progress-custom mb-2">
              <div class="progress-bar-custom h-100" :style="{ width: completionPercentage + '%' }"></div>
            </div>
            <div class="text-muted small d-flex justify-content-between" style="font-size: 0.8rem;">
              <span>{{ openTasksCount }} việc đang mở · {{ highPriorityCount }} việc ưu tiên cao</span>
              <span class="text-success fw-medium">{{ cleaningRooms.length }} phòng cần vệ sinh</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Ca trực & Nhân sự -->
      <div class="col-12 col-lg-5 col-xl-6">
        <div class="metric-card d-flex align-items-center justify-content-between h-100">
          <div>
            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
              {{ shiftInfo.shiftName || 'Ca sáng' }} · {{ shiftInfo.shiftHours || '07:00 – 15:00' }}
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.8rem;">
              {{ activeStaff.length }} nhân sự đang làm việc · Khách sạn An Nhiên
            </div>
          </div>
          <!-- Staff avatars -->
          <div class="d-flex align-items-center gap-2">
            <div
              v-for="staff in activeStaff"
              :key="staff.name"
              class="text-center"
              :title="staff.fullName || staff.name"
            >
              <div class="avatar-circle avatar-sm mx-auto mb-1">
                {{ staff.initial || getInitials(staff.name) }}
              </div>
              <div class="text-muted" style="font-size: 0.68rem;">{{ staff.name }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABS CHUYỂN ĐỔI CHẾ ĐỘ XEM -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 border-bottom pb-2">
      <ul class="nav nav-pills custom-nav-pills gap-2">
        <li class="nav-item">
          <button
            class="nav-link"
            :class="{ active: currentTab === 'kanban' }"
            @click="currentTab = 'kanban'"
          >
            <i class="bi bi-kanban me-1.5"></i>
            <span>Bảng công việc (Kanban)</span>
          </button>
        </li>
        <li class="nav-item position-relative">
          <button
            class="nav-link"
            :class="{ active: currentTab === 'cleaning' }"
            @click="currentTab = 'cleaning'"
          >
            <i class="bi bi-bucket me-1.5"></i>
            <span>Danh sách phòng cần vệ sinh</span>
            <span class="badge bg-danger ms-2 rounded-pill" v-if="cleaningRooms.length > 0">
              {{ cleaningRooms.length }}
            </span>
          </button>
        </li>
        <li class="nav-item">
          <button
            class="nav-link"
            :class="{ active: currentTab === 'inspection' }"
            @click="currentTab = 'inspection'"
          >
            <i class="bi bi-clipboard2-check me-1.5"></i>
            <span>Kiểm tra & Nghiệm thu phòng</span>
          </button>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-2 text-secondary small fw-medium">
        <i class="bi bi-calendar-event"></i>
        <span>Hôm nay, 08/11/2026</span>
      </div>
    </div>

    <!-- NỘI DUNG TỪNG TAB (TÁCH THÀNH COMPONENT CON) -->
    <!-- Tab 1: Công việc vệ sinh (Kanban) -->
    <CongViecVeSinh
      v-show="currentTab === 'kanban'"
      :tasks="tasks"
      @move-task="handleMoveTask"
      @open-report="openReportModal"
      @open-inspection="openInspectionModal"
      @open-status="openRoomStatusModal"
      @open-create="openCreateModalWithStatus"
    />

    <!-- Tab 2: Danh sách phòng cần vệ sinh -->
    <DanhSachPhong
      v-show="currentTab === 'cleaning'"
      :rooms="cleaningRooms"
      @assign-task="handleAssignCleaningTask"
      @open-report="openReportModal"
      @open-status="openRoomStatusModal"
      @refresh="fetchCleaningRooms"
    />

    <!-- Tab 3: Kiểm tra & Nghiệm thu phòng -->
    <KiemTraPhong
      ref="kiemTraRef"
      v-show="currentTab === 'inspection'"
      :all-rooms="allRooms"
      :loading="loading"
      @submit-inspection="handleSubmitInspection"
    />

    <!-- MODAL COMPONENT CON -->
    <!-- Modal 1: Tạo công việc mới -->
    <ModalTaoCongViec
      :show="showCreateModal"
      :initial-data="newTaskInitial"
      :staff-list="activeStaff"
      :loading="loading"
      @close="showCreateModal = false"
      @submit-create="handleSubmitCreateTask"
    />

    <!-- Modal 2: Cập nhật trạng thái phòng -->
    <ModalCapNhatTrangThai
      :show="showRoomStatusModal"
      :all-rooms="allRooms"
      :initial-location="selectedRoomLocation"
      :loading="loading"
      @close="showRoomStatusModal = false"
      @submit-status="handleSubmitUpdateRoomStatus"
    />

    <!-- Modal 3: Báo cáo phòng hoàn thành -->
    <BaoCaoPhong
      :show="showReportModal"
      :task-or-room="reportTarget"
      :minibar-products="minibarProducts"
      :loading="loading"
      @close="showReportModal = false"
      @submit-report="handleSubmitReport"
    />

    <!-- TOAST NOTIFICATION -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000;" v-if="toastMsg">
      <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-3">
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <span>{{ toastMsg }}</span>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="toastMsg = ''"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CongViecVeSinh from './CongViecVeSinh.vue';
import DanhSachPhong from './DanhSachPhong.vue';
import KiemTraPhong from './KiemTraPhong.vue';
import BaoCaoPhong from './BaoCaoPhong.vue';
import ModalCapNhatTrangThai from './ModalCapNhatTrangThai.vue';
import ModalTaoCongViec from './ModalTaoCongViec.vue';

const API_BASE = 'http://127.0.0.1:8000/api/buong-phong';

export default {
  name: 'BuongPhong',
  components: {
    CongViecVeSinh,
    DanhSachPhong,
    KiemTraPhong,
    BaoCaoPhong,
    ModalCapNhatTrangThai,
    ModalTaoCongViec
  },
  data() {
    return {
      currentTab: 'kanban',
      loading: false,
      toastMsg: '',

      // Dữ liệu ban đầu hiển thị giao diện (FE chỉ hiển thị & nhập liệu)
      tasks: [
        {
          id: 1,
          status: 'pending',
          location: 'Phòng 304',
          maPhong: 4,
          category: 'Dọn phòng',
          priority: 'Cao',
          title: 'Dọn phòng sau trả khách',
          note: 'Thay ga giường, bổ sung khăn và minibar.',
          assignee: 'Chị Hương',
          assigneeInitial: 'HƯ',
          timeInfo: 'Trước 12:00'
        },
        {
          id: 4,
          status: 'doing',
          location: 'Phòng 305',
          maPhong: 5,
          category: 'Dọn phòng',
          priority: 'Cao',
          title: 'Dọn phòng sau trả khách',
          note: 'Đã thay ga. Đang vệ sinh phòng tắm.',
          ongoingDetail: 'Bắt đầu 10:10 · 20 phút',
          assignee: 'Chị Hoa',
          assigneeInitial: 'HO',
          timeInfo: 'Xong lúc 11:15'
        },
        {
          id: 6,
          status: 'done',
          location: 'Phòng 301',
          maPhong: 1,
          category: 'Dọn phòng',
          priority: 'Cao',
          title: 'Chuẩn bị phòng đón khách',
          note: 'Đã bàn giao lễ tân. Khách nhận lúc 09:15.',
          assignee: 'Chị Hương',
          assigneeInitial: 'HƯ',
          timeInfo: 'Hoàn thành 08:45'
        }
      ],
      cleaningRooms: [
        {
          id: 4,
          tenPhong: 'Phòng 304',
          tang: 3,
          trangThai: 'Can_Don',
          tenLoai: 'Phòng Deluxe Hướng Phố',
          ghiChuDonDep: 'Khách trả phòng lúc 10:00, cần dọn trước 12:00',
          thoiGianCheckOut: '10:00 08/11/2026',
          hasActiveTask: true,
          taskStatus: 'pending',
          assignee: 'Chị Hương'
        },
        {
          id: 10,
          tenPhong: 'Phòng 404',
          tang: 4,
          trangThai: 'Can_Don',
          tenLoai: 'Phòng Deluxe Hướng Phố',
          ghiChuDonDep: 'Khách trả phòng lúc 10:15, ưu tiên vừa',
          thoiGianCheckOut: '10:15 08/11/2026',
          hasActiveTask: false,
          taskStatus: 'Chua_Phan_Cong',
          assignee: 'Chưa phân công'
        }
      ],
      allRooms: [
        { id: 1, tenPhong: 'Phòng 301', tang: 3, trangThai: 'Trong_Sach', tenLoai: 'Superior Giường Đôi' },
        { id: 2, tenPhong: 'Phòng 302', tang: 3, trangThai: 'Dang_Su_Dung', tenLoai: 'Superior Giường Đôi' },
        { id: 3, tenPhong: 'Phòng 303', tang: 3, trangThai: 'Trong_Sach', tenLoai: 'Deluxe Hướng Phố' },
        { id: 4, tenPhong: 'Phòng 304', tang: 3, trangThai: 'Can_Don', tenLoai: 'Deluxe Hướng Phố' },
        { id: 5, tenPhong: 'Phòng 305', tang: 3, trangThai: 'Dang_Don', tenLoai: 'Suite An Nhiên' },
        { id: 10, tenPhong: 'Phòng 404', tang: 4, trangThai: 'Can_Don', tenLoai: 'Deluxe Hướng Phố' },
        { id: 11, tenPhong: 'Phòng 405', tang: 4, trangThai: 'Bao_Tri', tenLoai: 'Suite An Nhiên' }
      ],
      minibarProducts: [
        { id: 1, tenSanPham: 'Nước suối Lavie 500ml', donGia: 15000 },
        { id: 2, tenSanPham: 'Coca Cola lon 330ml', donGia: 25000 },
        { id: 3, tenSanPham: 'Bia Larue Hội An 330ml', donGia: 30000 },
        { id: 4, tenSanPham: 'Snack khoai tây Lays', donGia: 25000 }
      ],
      shiftInfo: {
        shiftName: 'Ca sáng',
        shiftHours: '07:00 – 15:00'
      },
      activeStaff: [
        { name: 'Hương', fullName: 'Chị Hương', initial: 'HƯ' },
        { name: 'Hoa', fullName: 'Chị Hoa', initial: 'HO' },
        { name: 'Tuấn', fullName: 'Anh Tuấn', initial: 'TU' },
        { name: 'Linh', fullName: 'Mai Linh', initial: 'ML' }
      ],

      // Quản lý Modal
      showCreateModal: false,
      showRoomStatusModal: false,
      showReportModal: false,

      // Quản lý thông báo Buồng phòng
      notifications: [],
      unreadNotifyCount: 0,
      showNotifyDropdown: false,
      dismissedAlertIds: [],
      pollTimer: null,

      newTaskInitial: {},
      selectedRoomLocation: '',
      reportTarget: {}
    };
  },

  computed: {
    guestServiceAlerts() {
      return this.notifications.filter(n =>
        n.trangThai === 'Chua_Xem' &&
        !this.dismissedAlertIds.includes(n.id)
      );
    },
    completedTasksCount() {
      return this.tasks.filter(t => t.status === 'done').length;
    },
    openTasksCount() {
      return this.tasks.filter(t => t.status !== 'done').length;
    },
    highPriorityCount() {
      return this.tasks.filter(t => t.priority === 'Cao' && t.status !== 'done').length;
    },
    completionPercentage() {
      if (this.tasks.length === 0) return 0;
      return Math.round((this.completedTasksCount / this.tasks.length) * 100);
    }
  },

  mounted() {
    this.loadAllData();
    this.fetchNotifications();
    // Tự động kiểm tra và cập nhật công việc, thông báo mỗi 10 giây
    this.pollTimer = setInterval(() => {
      this.fetchNotifications(true);
      this.fetchTasks();
      this.fetchCleaningRooms();
    }, 10000);
  },

  beforeUnmount() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer);
    }
  },

  methods: {
    async loadAllData() {
      this.loading = true;
      try {
        await Promise.all([
          this.fetchTasks(),
          this.fetchCleaningRooms(),
          this.fetchAllRooms(),
          this.fetchThongKeCa(),
          this.fetchDichVu()
        ]);
      } finally {
        this.loading = false;
      }
    },

    // 1. LẤY DANH SÁCH CÔNG VIỆC TỪ BACKEND
    async fetchTasks() {
      try {
        const res = await fetch(`${API_BASE}/cong-viec`);
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success' && Array.isArray(resData.data)) {
            this.tasks = resData.data;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách công việc:', err);
      }
    },

    // 2. LẤY DANH SÁCH PHÒNG CẦN VỆ SINH TỪ BACKEND
    async fetchCleaningRooms() {
      try {
        const res = await fetch(`${API_BASE}/phong-can-ve-sinh`);
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success' && Array.isArray(resData.data)) {
            this.cleaningRooms = resData.data;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách phòng cần vệ sinh:', err);
      }
    },

    // 3. LẤY TOÀN BỘ DANH SÁCH PHÒNG TỪ BACKEND
    async fetchAllRooms() {
      try {
        const res = await fetch(`${API_BASE}/phong`);
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success' && Array.isArray(resData.data)) {
            this.allRooms = resData.data;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh sách phòng:', err);
      }
    },

    // 4. LẤY THỐNG KÊ CA & NHÂN SỰ TỪ BACKEND
    async fetchThongKeCa() {
      try {
        const res = await fetch(`${API_BASE}/thong-ke-ca`);
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success' && resData.data) {
            this.shiftInfo = {
              shiftName: resData.data.shiftName,
              shiftHours: resData.data.shiftHours
            };
            if (Array.isArray(resData.data.staff)) {
              this.activeStaff = resData.data.staff;
            }
          }
        }
      } catch (err) {
        console.error('Lỗi tải thống kê ca:', err);
      }
    },

    // 5. LẤY DANH MỤC MINIBAR TỪ BACKEND
    async fetchDichVu() {
      try {
        const res = await fetch(`${API_BASE}/danh-muc-dich-vu`);
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success' && Array.isArray(resData.minibar)) {
            this.minibarProducts = resData.minibar;
          }
        }
      } catch (err) {
        console.error('Lỗi tải danh mục minibar:', err);
      }
    },

    // ================= XỬ LÝ HÀNH ĐỘNG GỌI API BACKEND =================
    // A. CHUYỂN TRẠNG THÁI CÔNG VIỆC (KANBAN)
    async handleMoveTask(task, newStatus) {
      try {
        const res = await fetch(`${API_BASE}/chuyen-trang-thai`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({ id: task.id, status: newStatus })
        });
        if (res.ok) {
          const data = await res.json();
          this.showToast(data.message || 'Đã chuyển trạng thái thành công!');
          await Promise.all([this.fetchTasks(), this.fetchCleaningRooms(), this.fetchAllRooms()]);
        }
      } catch (err) {
        console.error('Lỗi chuyển trạng thái công việc:', err);
        this.showToast('Lỗi kết nối đến máy chủ!');
      }
    },

    // B. TẠO CÔNG VIỆC MỚI
    openCreateModal() {
      this.newTaskInitial = {
        status: 'pending',
        location: '',
        category: 'Dọn phòng',
        priority: 'Cao',
        title: '',
        note: '',
        assignee: 'Chị Hương',
        timeInfo: 'Trước 14:00'
      };
      this.showCreateModal = true;
    },

    openCreateModalWithStatus(status) {
      this.openCreateModal();
      this.newTaskInitial.status = status;
    },

    handleAssignCleaningTask(room) {
      this.newTaskInitial = {
        status: 'pending',
        location: room.tenPhong,
        category: 'Dọn phòng',
        priority: 'Cao',
        title: 'Dọn phòng sau trả khách',
        note: room.ghiChuDonDep || 'Thay ga giường, bổ sung khăn tắm và minibar',
        assignee: 'Chị Hương',
        timeInfo: 'Trước 13:00'
      };
      this.showCreateModal = true;
    },

    async handleSubmitCreateTask(payload) {
      this.loading = true;
      try {
        const res = await fetch(`${API_BASE}/tao-cong-viec`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        if (res.ok) {
          const resData = await res.json();
          this.showToast(resData.message || 'Đã tạo công việc thành công!');
          this.showCreateModal = false;
          await Promise.all([this.fetchTasks(), this.fetchCleaningRooms(), this.fetchAllRooms()]);
        }
      } catch (err) {
        console.error('Lỗi tạo công việc:', err);
        this.showToast('Không thể tạo công việc. Vui lòng thử lại!');
      } finally {
        this.loading = false;
      }
    },

    // C. CẬP NHẬT TRẠNG THÁI PHÒNG
    openRoomStatusModal(location) {
      this.selectedRoomLocation = location || '';
      this.showRoomStatusModal = true;
    },

    async handleSubmitUpdateRoomStatus(payload) {
      this.loading = true;
      try {
        const res = await fetch(`${API_BASE}/cap-nhat-trang-thai-phong`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        if (res.ok) {
          const data = await res.json();
          this.showToast(data.message || 'Đã cập nhật trạng thái phòng thành công!');
          this.showRoomStatusModal = false;
          await Promise.all([this.fetchCleaningRooms(), this.fetchAllRooms(), this.fetchTasks()]);
        }
      } catch (err) {
        console.error('Lỗi cập nhật trạng thái phòng:', err);
        this.showToast('Lỗi cập nhật trạng thái phòng!');
      } finally {
        this.loading = false;
      }
    },

    // D. KIỂM TRA & NGHIỆM THU PHÒNG
    openInspectionModal(location) {
      this.currentTab = 'inspection';
      this.$nextTick(() => {
        if (this.$refs.kiemTraRef) {
          this.$refs.kiemTraRef.openModal(location);
        }
      });
    },

    async handleSubmitInspection(payload) {
      this.loading = true;
      try {
        const res = await fetch(`${API_BASE}/kiem-tra-phong`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        if (res.ok) {
          const data = await res.json();
          this.showToast(data.message || 'Đã ghi nhận kết quả kiểm tra phòng!');
          if (this.$refs.kiemTraRef) {
            this.$refs.kiemTraRef.closeModal();
          }
          await Promise.all([this.fetchCleaningRooms(), this.fetchAllRooms(), this.fetchTasks()]);
        }
      } catch (err) {
        console.error('Lỗi kiểm tra phòng:', err);
        this.showToast('Lỗi kiểm tra phòng!');
      } finally {
        this.loading = false;
      }
    },

    // E. BÁO CÁO PHÒNG HOÀN THÀNH
    openReportModal(taskOrRoom) {
      this.reportTarget = { ...taskOrRoom };
      this.showReportModal = true;
    },

    async handleSubmitReport(payload) {
      this.loading = true;
      try {
        const res = await fetch(`${API_BASE}/bao-cao-hoan-thanh`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify({
            maPhieu: payload.taskId,
            maPhong: payload.maPhong,
            ghiChuBanGiao: payload.ghiChuBanGiao,
            tinhTrangPhong: payload.tinhTrangPhong,
            minibar: payload.minibarList,
            taiSan: payload.taiSanList
          })
        });
        if (res.ok) {
          const data = await res.json();
          this.showToast(data.message || 'Đã lưu báo cáo hoàn thành dọn phòng!');
          this.showReportModal = false;
          await Promise.all([this.fetchTasks(), this.fetchCleaningRooms(), this.fetchAllRooms()]);
        }
      } catch (err) {
        console.error('Lỗi báo cáo hoàn thành:', err);
        this.showToast('Lỗi gửi báo cáo!');
      } finally {
        this.loading = false;
      }
    },

    // HELPERS
    getInitials(name) {
      if (!name) return 'NV';
      if (name.includes('Hương')) return 'HƯ';
      if (name.includes('Hoa')) return 'HO';
      if (name.includes('Tuấn')) return 'TU';
      if (name.includes('Linh')) return 'ML';
      return name.substring(0, 2).toUpperCase();
    },

    showToast(msg) {
      this.toastMsg = msg;
      setTimeout(() => {
        if (this.toastMsg === msg) this.toastMsg = '';
      }, 3500);
    },

    // 6. THÔNG BÁO CHO BUỒNG PHÒNG
    async fetchNotifications(silent = false) {
      try {
        const res = await fetch('http://127.0.0.1:8000/api/thong-bao?userRole=Buồng phòng', {
          headers: { 'Accept': 'application/json', 'X-User-Role': 'Buồng phòng' }
        });
        if (res.ok) {
          const resData = await res.json();
          if (resData.status === 'success') {
            const previousUnread = this.unreadNotifyCount;
            this.notifications = resData.data || [];
            this.unreadNotifyCount = resData.unreadCount || 0;

            // Nếu có thông báo mới đến trong lúc đang trực ca
            if (silent && this.unreadNotifyCount > previousUnread) {
              const latest = this.notifications[0];
              if (latest) {
                this.toastMsg = `🔔 ${latest.tieuDe}`;
              }
            }
          }
        }
      } catch (err) {
        if (!silent) console.error('Lỗi tải thông báo buồng phòng:', err);
      }
    },

    toggleNotifyDropdown() {
      this.showNotifyDropdown = !this.showNotifyDropdown;
    },

    async markAllNotificationsAsRead() {
      try {
        const res = await fetch('http://127.0.0.1:8000/api/thong-bao/doc-tat-ca', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-User-Role': 'Buồng phòng'
          },
          body: JSON.stringify({ userRole: 'Buồng phòng' })
        });
        if (res.ok) {
          this.unreadNotifyCount = 0;
          this.notifications.forEach(n => n.trangThai = 'Da_Xem');
          this.showToast('Đã đánh dấu tất cả thông báo là đã xem.');
        }
      } catch (err) {
        console.error('Lỗi đánh dấu thông báo:', err);
      }
    },

    async dismissAlert(id) {
      this.dismissedAlertIds.push(id);
      try {
        await fetch(`http://127.0.0.1:8000/api/thong-bao/${id}/da-xem`, {
          method: 'POST',
          headers: { 'Accept': 'application/json' }
        });
        const found = this.notifications.find(n => n.id === id);
        if (found) found.trangThai = 'Da_Xem';
        if (this.unreadNotifyCount > 0) this.unreadNotifyCount--;
      } catch (err) {
        console.error('Lỗi tắt thông báo:', err);
      }
    },

    async handleAcknowledgeAlert(alert) {
      await this.dismissAlert(alert.id);
      this.currentTab = 'kanban';
      await Promise.all([this.fetchTasks(), this.fetchCleaningRooms()]);
      this.showToast(`Đã chuyển đến công việc: ${alert.tieuDe}`);
    },

    async handleNotificationClick(item) {
      if (item.trangThai === 'Chua_Xem') {
        await this.dismissAlert(item.id);
      }
      this.showNotifyDropdown = false;
      this.currentTab = 'kanban';
      await Promise.all([this.fetchTasks(), this.fetchCleaningRooms()]);
    }
  }
};
</script>

<style scoped src="./buongphong.css"></style>
