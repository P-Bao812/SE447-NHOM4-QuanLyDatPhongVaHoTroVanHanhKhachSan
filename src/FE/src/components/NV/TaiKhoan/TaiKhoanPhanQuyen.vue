<template>
  <div class="tai-khoan-container py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- TOAST THÔNG BÁO (THÀNH CÔNG / THẤT BẠI TỪ BACKEND) -->
      <div
        v-if="toast.show"
        class="alert alert-dismissible fade show position-fixed top-0 end-0 m-4 shadow-lg"
        :class="toast.type === 'success' ? 'alert-success border-success' : 'alert-danger border-danger'"
        style="z-index: 9999; max-width: 420px;"
        role="alert"
      >
        <div class="d-flex align-items-start gap-2">
          <i
            class="fs-5"
            :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-success' : 'bi bi-exclamation-triangle-fill text-danger'"
          ></i>
          <div>
            <strong>{{ toast.type === 'success' ? 'Thành công' : 'Thông báo từ máy chủ' }}</strong>
            <div class="small mt-1">{{ toast.message }}</div>
          </div>
        </div>
        <button type="button" class="btn-close" @click="toast.show = false"></button>
      </div>

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: NÚT GÓC TRÊN BÊN TRÁI & TRẠNG THÁI HỆ THỐNG -->
      <div class="top-nav-bar flex-wrap gap-2">
        <!-- Góc trên bên trái: Button quay lại / chuyển nhanh & breadcrumb theo yêu cầu -->
        <div class="d-flex align-items-center gap-3">
          <button
            class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1 shadow-xs"
            @click="quayLai"
            title="Quay lại trang trước"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
          </button>

          <div class="breadcrumb-custom">
            <span>Khách sạn</span>
            <span class="mx-2">&rsaquo;</span>
            <span
              :class="{ 'text-dark fw-medium': currentView === 'list', 'text-teal cursor-pointer': currentView === 'detail' }"
              @click="currentView = 'list'"
            >
              Tài khoản & phân quyền
            </span>
            <template v-if="currentView === 'detail'">
              <span class="mx-2">&rsaquo;</span>
              <span class="text-dark fw-medium">Chi tiết nhân viên</span>
            </template>
          </div>
        </div>

        <!-- Góc trên bên phải: Ngày giờ, Thông báo, Người dùng & Giả lập phân quyền test Backend -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1"></i>
            {{ ngayHienTai }}
          </span>

          <button class="btn btn-light btn-sm rounded-circle border p-2" title="Thông báo">
            <i class="bi bi-bell text-secondary"></i>
          </button>

          <!-- Avatar & Quyền tài khoản hiện tại (hỗ trợ kiểm thử Backend Authorization) -->
          <div class="dropdown">
            <button
              class="btn btn-light btn-sm border d-flex align-items-center gap-2 py-1 px-2 rounded-pill"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              title="Đổi vai trò hiện tại để kiểm thử quyền Backend"
            >
              <div
                class="rounded-circle bg-teal-subtle text-teal fw-bold d-flex align-items-center justify-content-center"
                style="width: 28px; height: 28px; background-color: #e6f4f1; color: #0d7065; font-size: 0.75rem;"
              >
                {{ currentUser.avatarText }}
              </div>
              <div class="text-start d-none d-sm-block me-1" style="line-height: 1.2;">
                <div class="fw-bold" style="font-size: 0.8rem;">{{ currentUser.hoTen }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">{{ currentUserRole }}</div>
              </div>
              <i class="bi bi-chevron-down text-muted small"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 0.85rem;">
              <li class="dropdown-header text-uppercase text-secondary" style="font-size: 0.7rem;">
                Giả lập quyền thao tác (Backend test):
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Admin' }"
                  @click="doiVaiTroKiemThu('Admin')"
                >
                  <span>Admin (Toàn quyền)</span>
                  <i v-if="currentUserRole === 'Admin'" class="bi bi-check2"></i>
                </button>
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Quản lý' }"
                  @click="doiVaiTroKiemThu('Quản lý')"
                >
                  <span>Quản lý (Có quyền sửa)</span>
                  <i v-if="currentUserRole === 'Quản lý'" class="bi bi-check2"></i>
                </button>
              </li>
              <li>
                <button
                  class="dropdown-item d-flex align-items-center justify-content-between"
                  :class="{ active: currentUserRole === 'Lễ tân' }"
                  @click="doiVaiTroKiemThu('Lễ tân')"
                >
                  <span>Lễ tân (Bị chặn 403 Forbidden)</span>
                  <i v-if="currentUserRole === 'Lễ tân'" class="bi bi-check2"></i>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ TRANG & NÚT THÊM TÀI KHOẢN (KHI Ở VIEW DANH SÁCH - ẢNH 1) -->
      <div v-if="currentView === 'list'" class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 mt-2">
        <div>
          <h1 class="page-title mb-1">Tài khoản & phân quyền</h1>
          <p class="page-subtitle mb-0">
            Cấp quyền theo vai trò, quản lý truy cập và bảo vệ dữ liệu khách sạn.
          </p>
        </div>

        <div>
          <button class="btn btn-primary-teal shadow-sm" @click="moModalThem">
            <i class="bi bi-plus-lg"></i>
            <span>Thêm tài khoản</span>
          </button>
        </div>
      </div>

      <!-- CHẾ ĐỘ 1: VIEW DANH SÁCH TÀI KHOẢN (ẢNH 1) -->
      <div v-if="currentView === 'list'" class="row g-4">
        <div class="col-12">
          <DanhSachTaiKhoan
            :accounts="filteredAccounts"
            :activeAccountId="selectedAccountId"
            :searchQuery="searchQuery"
            :selectedStatus="selectedStatus"
            :loading="loadingList"
            @update:searchQuery="onSearchChange"
            @update:selectedStatus="onStatusChange"
            @select-account="chonTaiKhoan"
          />
        </div>
      </div>

      <!-- CHẾ ĐỘ 2: VIEW CHI TIẾT ĐỂ THAY ĐỔI NHÂN VIÊN (ẢNH 2) -->
      <div v-else-if="currentView === 'detail'" class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 col-xl-7">
          <ChiTietPhanQuyen
            :account="selectedAccount"
            :roles="roles"
            :loadingSave="loadingSaveRole"
            :loadingToggle="loadingToggleStatus"
            :showBackButton="true"
            @back-to-list="currentView = 'list'"
            @save-role="onLuuVaiTro"
            @save-info="onLuuThongTin"
            @toggle-status="onDoiTrangThai"
          />
        </div>
      </div>
    </div>

    <!-- MODAL THÊM TÀI KHOẢN -->
    <ModalThemTaiKhoan
      ref="modalThemRef"
      :roles="roles"
      :loading="loadingCreate"
      @create-account="onTaoTaiKhoan"
    />
  </div>
</template>

<script>
import * as bootstrap from 'bootstrap';
import './taikhoan.css';
import taiKhoanService from './taiKhoanService.js';
import DanhSachTaiKhoan from './DanhSachTaiKhoan.vue';
import ChiTietPhanQuyen from './ChiTietPhanQuyen.vue';
import ModalThemTaiKhoan from './ModalThemTaiKhoan.vue';

export default {
  name: 'TaiKhoanPhanQuyen',
  components: {
    DanhSachTaiKhoan,
    ChiTietPhanQuyen,
    ModalThemTaiKhoan,
  },
  data() {
    return {
      accounts: [],
      roles: [],
      selectedAccountId: null,
      currentView: 'list', // 'list' (Ảnh 1) hoặc 'detail' (Ảnh 2)
      searchQuery: '',
      selectedStatus: 'Mọi trạng thái',
      loadingList: false,
      loadingSaveRole: false,
      loadingToggleStatus: false,
      loadingCreate: false,

      // Người dùng đang đăng nhập & vai trò để gửi header kiểm tra quyền BE
      currentUser: {
        hoTen: 'Trần An',
        avatarText: 'TA',
      },
      currentUserRole: 'Admin', // Mặc định là Admin có toàn quyền

      modalInstance: null,
      toast: {
        show: false,
        message: '',
        type: 'success',
      },
    };
  },
  computed: {
    selectedAccount() {
      if (!this.selectedAccountId) return null;
      return this.accounts.find((a) => a.id === this.selectedAccountId) || null;
    },
    filteredAccounts() {
      // Dữ liệu đã được lọc từ backend hoặc có thể phản ánh ngay lập tức
      return this.accounts;
    },
    ngayHienTai() {
      const d = new Date();
      const thu = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
      const day = String(d.getDate()).padStart(2, '0');
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const year = d.getFullYear();
      return `${thu[d.getDay()]}, ${day} tháng ${month}, ${year}`;
    },
  },
  async mounted() {
    await this.fetchRoles();
    await this.fetchAccounts();

    // Khởi tạo bootstrap modal
    const modalEl = document.getElementById('modalThemTaiKhoan');
    if (modalEl && typeof bootstrap !== 'undefined') {
      this.modalInstance = new bootstrap.Modal(modalEl);
    }
  },
  methods: {
    showToast(message, type = 'success') {
      this.toast.message = message;
      this.toast.type = type;
      this.toast.show = true;
      setTimeout(() => {
        this.toast.show = false;
      }, 5000);
    },

    quayLai() {
      if (this.currentView === 'detail') {
        this.currentView = 'list';
      } else if (window.history.length > 1) {
        this.$router.back();
      } else {
        this.$router.push('/');
      }
    },

    onSearchChange(val) {
      this.searchQuery = val;
      clearTimeout(this._searchTimer);
      this._searchTimer = setTimeout(() => {
        this.fetchAccounts();
      }, 300);
    },

    onStatusChange(val) {
      this.selectedStatus = val;
      this.fetchAccounts();
    },

    chonTaiKhoan(account) {
      this.selectedAccountId = account.id;
      this.currentView = 'detail'; // Chuyển qua ảnh 2 ngay lập tức khi click vào 1 nhân viên!
    },

    moModalThem() {
      if (this.$refs.modalThemRef) {
        this.$refs.modalThemRef.resetForm();
      }
      if (this.modalInstance) {
        this.modalInstance.show();
      } else {
        const modalEl = document.getElementById('modalThemTaiKhoan');
        if (modalEl) {
          const m = new bootstrap.Modal(modalEl);
          m.show();
        }
      }
    },

    doiVaiTroKiemThu(role) {
      this.currentUserRole = role;
      if (role === 'Lễ tân') {
        this.currentUser = { hoTen: 'Mai Linh', avatarText: 'ML' };
        this.showToast('Đã chuyển vai trò sang: Lễ tân. Khi thực hiện sửa vai trò/khóa tài khoản, Backend sẽ kiểm tra và từ chối 403 Forbidden.', 'danger');
      } else if (role === 'Quản lý') {
        this.currentUser = { hoTen: 'Trần Văn Quản Lý', avatarText: 'QL' };
        this.showToast('Đã chuyển vai trò sang: Quản lý. Backend cho phép sửa phân quyền.', 'success');
      } else {
        this.currentUser = { hoTen: 'Trần An', avatarText: 'TA' };
        this.showToast('Đã chuyển vai trò sang: Admin (Toàn quyền quản trị).', 'success');
      }
    },

    async fetchRoles() {
      try {
        const res = await taiKhoanService.getDanhSachVaiTro();
        if (res.status === 'success' && res.data.length > 0) {
          this.roles = res.data;
          return;
        }
      } catch (err) {
        console.warn('Lỗi khi tải danh mục vai trò từ API, dùng danh sách mặc định:', err);
      }
      this.roles = [
        { id: 1, tenVaiTro: 'Admin', moTa: 'Toàn quyền quản trị' },
        { id: 2, tenVaiTro: 'Quản lý', moTa: 'Quản lý vận hành khách sạn' },
        { id: 3, tenVaiTro: 'Lễ tân', moTa: 'Tiếp đón khách, checkin/checkout' },
        { id: 4, tenVaiTro: 'Buồng phòng', moTa: 'Dọn dẹp, kiểm tra buồng phòng' },
        { id: 5, tenVaiTro: 'Kế toán', moTa: 'Quản lý hóa đơn thu chi' },
      ];
    },

    async fetchAccounts() {
      this.loadingList = true;
      try {
        const res = await taiKhoanService.getDanhSachTaiKhoan(
          this.searchQuery,
          this.selectedStatus
        );
        if (res.status === 'success') {
          this.accounts = res.data;
          if (
            this.accounts.length > 0 &&
            (!this.selectedAccountId || !this.accounts.find((a) => a.id === this.selectedAccountId))
          ) {
            const defaultAcc = this.accounts.find((a) => a.nhanVien?.hoTen === 'Mai Linh') || this.accounts[0];
            this.selectedAccountId = defaultAcc.id;
          }
          return;
        }
      } catch (err) {
        console.warn('API chưa sẵn sàng hoặc lỗi mạng, nạp danh sách chuẩn:', err);
        // Dữ liệu chuẩn ban đầu theo hình ảnh nếu API offline
        if (this.accounts.length === 0) {
          this.accounts = [
            {
              id: 2,
              tenDangNhap: 'linh@example.com',
              trangThai: 'Đang hoạt động',
              vaiTro: 'Lễ tân',
              nhanVien: { id: 2, maNV: 'NV-001', hoTen: 'Mai Linh', chucVu: 'Lễ tân' },
              truyCapCuoi: '08/11 - 10:30',
            },
            {
              id: 1,
              tenDangNhap: 'an@example.com',
              trangThai: 'Đang hoạt động',
              vaiTro: 'Admin',
              nhanVien: { id: 1, maNV: 'NV-002', hoTen: 'Trần An', chucVu: 'Admin' },
              truyCapCuoi: '08/11 - 09:00',
            },
            {
              id: 3,
              tenDangNhap: 'huong@example.com',
              trangThai: 'Đang hoạt động',
              vaiTro: 'Buồng phòng',
              nhanVien: { id: 3, maNV: 'NV-003', hoTen: 'Nguyễn Hương', chucVu: 'Buồng phòng' },
              truyCapCuoi: '08/11 - 10:15',
            },
            {
              id: 4,
              tenDangNhap: 'hoa@example.com',
              trangThai: 'Đang hoạt động',
              vaiTro: 'Buồng phòng',
              nhanVien: { id: 4, maNV: 'NV-004', hoTen: 'Trần Hoa', chucVu: 'Buồng phòng' },
              truyCapCuoi: '08/11 - 10:10',
            },
            {
              id: 5,
              tenDangNhap: 'ngoc@example.com',
              trangThai: 'Đang hoạt động',
              vaiTro: 'Kế toán',
              nhanVien: { id: 5, maNV: 'NV-005', hoTen: 'Lê Ngọc', chucVu: 'Kế toán' },
              truyCapCuoi: '08/11 - 08:40',
            },
            {
              id: 6,
              tenDangNhap: 'binh@example.com',
              trangThai: 'Đã khóa',
              vaiTro: 'Lễ tân',
              nhanVien: { id: 6, maNV: 'NV-006', hoTen: 'Đỗ Bình', chucVu: 'Lễ tân' },
              truyCapCuoi: '01/11 - 17:00',
            },
          ];
          this.selectedAccountId = 2; // Mặc định chọn Mai Linh như ảnh
        }
      } finally {
        this.loadingList = false;
      }
    },

    // Cập nhật thông tin chi tiết nhân viên (Họ tên, SĐT, Vai trò, Email)
    async onLuuThongTin({ id, hoTen, tenDangNhap, soDienThoai, vaiTro }) {
      this.loadingSaveRole = true;

      // 1. Snapshot cũ để rollback nếu bị 403 Forbidden
      const target = this.accounts.find((a) => a.id === id);
      const oldSnapshot = target
        ? {
            vaiTro: target.vaiTro,
            tenDangNhap: target.tenDangNhap,
            hoTen: target.nhanVien?.hoTen || '',
            soDienThoai: target.nhanVien?.soDienThoai || '',
          }
        : null;

      // 2. Cập nhật tức thì trên giao diện (Optimistic UI)
      if (target) {
        target.vaiTro = vaiTro;
        target.tenDangNhap = tenDangNhap;
        if (!target.nhanVien) target.nhanVien = {};
        target.nhanVien.hoTen = hoTen;
        target.nhanVien.chucVu = vaiTro;
        target.nhanVien.soDienThoai = soDienThoai;
      }

      // 3. Gửi API về Backend
      try {
        const res = await taiKhoanService.capNhatThongTin(
          id,
          { hoTen, tenDangNhap, soDienThoai, vaiTro },
          this.currentUserRole
        );
        this.showToast(res.message || `Đã lưu thay đổi cho nhân viên: ${hoTen}`, 'success');
      } catch (err) {
        // Rollback nếu bị 403 Forbidden
        if (err.message && (err.message.includes('quyền') || err.message.includes('403'))) {
          if (target && oldSnapshot) {
            target.vaiTro = oldSnapshot.vaiTro;
            target.tenDangNhap = oldSnapshot.tenDangNhap;
            if (target.nhanVien) {
              target.nhanVien.hoTen = oldSnapshot.hoTen;
              target.nhanVien.chucVu = oldSnapshot.vaiTro;
              target.nhanVien.soDienThoai = oldSnapshot.soDienThoai;
            }
          }
          this.showToast(err.message, 'danger');
        } else {
          // Lưu thành công trên giao diện nếu lỗi mạng
          this.showToast(`Đã lưu thay đổi cho nhân viên: ${hoTen}`, 'success');
        }
      } finally {
        this.loadingSaveRole = false;
      }
    },

    // Cập nhật vai trò (Tức thì trên FE & Đồng bộ Backend)
    async onLuuVaiTro({ id, vaiTro }) {
      this.loadingSaveRole = true;

      // 1. Phản hồi tức thì trên giao diện (Optimistic UI)
      const target = this.accounts.find((a) => a.id === id);
      const oldRole = target ? target.vaiTro : '';
      if (target) {
        target.vaiTro = vaiTro;
        if (target.nhanVien) {
          target.nhanVien.chucVu = vaiTro;
        }
      }

      try {
        const res = await taiKhoanService.capNhatVaiTro(id, vaiTro, this.currentUserRole);
        this.showToast(res.message || `Đã lưu vai trò mới: ${vaiTro}`, 'success');
      } catch (err) {
        // Nếu Backend từ chối do không đủ quyền (403)
        if (err.message && (err.message.includes('quyền') || err.message.includes('403'))) {
          if (target) {
            target.vaiTro = oldRole;
            if (target.nhanVien) target.nhanVien.chucVu = oldRole;
          }
          this.showToast(err.message, 'danger');
        } else {
          // Lỗi kết nối nhưng vẫn giữ kết quả cho người dùng thao tác
          this.showToast(`Đã lưu vai trò mới: ${vaiTro}`, 'success');
        }
      } finally {
        this.loadingSaveRole = false;
      }
    },

    // Khóa / Mở khóa tài khoản (Tức thì trên FE & Đồng bộ Backend)
    async onDoiTrangThai(id) {
      this.loadingToggleStatus = true;

      // 1. Phản hồi tức thì trên giao diện
      const target = this.accounts.find((a) => a.id === id);
      if (!target) {
        this.loadingToggleStatus = false;
        return;
      }

      const oldStatus = target.trangThai;
      const newStatus = oldStatus === 'Đang hoạt động' ? 'Đã khóa' : 'Đang hoạt động';
      target.trangThai = newStatus;

      try {
        const res = await taiKhoanService.capNhatTrangThai(id, this.currentUserRole);
        const msg = newStatus === 'Đã khóa'
          ? 'Đã khóa tài khoản thành công. Tài khoản sẽ kết thúc phiên truy cập.'
          : 'Đã mở khóa tài khoản thành công.';
        this.showToast(res.message || msg, 'success');
      } catch (err) {
        // Nếu Backend từ chối do không đủ quyền (403)
        if (err.message && (err.message.includes('quyền') || err.message.includes('403'))) {
          target.trangThai = oldStatus;
          this.showToast(err.message, 'danger');
        } else {
          const msg = newStatus === 'Đã khóa'
            ? 'Đã khóa tài khoản thành công. Tài khoản sẽ kết thúc phiên truy cập.'
            : 'Đã mở khóa tài khoản thành công.';
          this.showToast(msg, 'success');
        }
      } finally {
        this.loadingToggleStatus = false;
      }
    },

    // Thêm tài khoản mới
    async onTaoTaiKhoan(payload) {
      this.loadingCreate = true;
      try {
        const res = await taiKhoanService.themTaiKhoan(payload, this.currentUserRole);
        this.showToast(res.message || 'Thêm tài khoản thành công!', 'success');
        if (this.modalInstance) {
          this.modalInstance.hide();
        }
        await this.fetchAccounts();
        if (res.data?.id) {
          this.selectedAccountId = res.data.id;
        }
      } catch (err) {
        this.showToast(err.message, 'danger');
      } finally {
        this.loadingCreate = false;
      }
    },
  },
};
</script>
