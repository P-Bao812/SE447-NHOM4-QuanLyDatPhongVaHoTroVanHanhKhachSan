/**
 * API Service cho Quản lý Tài khoản & Phân quyền
 * Gọi trực tiếp tới backend Laravel, kèm header X-User-Role để kiểm tra quyền ở Backend
 */

const API_BASE = 'http://127.0.0.1:8000/api/tai-khoan';

export default {
  /**
   * Lấy danh sách tài khoản kèm nhân viên (có tìm kiếm & lọc trạng thái)
   */
  async getDanhSachTaiKhoan(search = '', trangThai = '') {
    const params = new URLSearchParams();
    if (search) params.append('q', search);
    if (trangThai && trangThai !== 'Mọi trạng thái') params.append('trangThai', trangThai);

    const url = `${API_BASE}${params.toString() ? '?' + params.toString() : ''}`;
    const res = await fetch(url);
    if (!res.ok) {
      throw new Error(`Lỗi tải danh sách: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Lấy chi tiết một tài khoản
   */
  async getChiTietTaiKhoan(id) {
    const res = await fetch(`${API_BASE}/${id}`);
    if (!res.ok) {
      throw new Error(`Lỗi tải chi tiết: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Lấy danh sách các vai trò có trong hệ thống (DB)
   */
  async getDanhSachVaiTro() {
    const res = await fetch(`${API_BASE}/vai-tro`);
    if (!res.ok) {
      throw new Error(`Lỗi tải danh mục vai trò: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Cập nhật vai trò / thông tin nhân viên
   * Backend kiểm tra quyền qua header X-User-Role hoặc body userRole
   */
  async capNhatVaiTro(id, vaiTro, userRole = 'Admin', extraData = {}) {
    const res = await fetch(`${API_BASE}/${id}/vai-tro`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ vaiTro, userRole, ...extraData }),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể cập nhật vai trò');
    }
    return data;
  },

  /**
   * Cập nhật thông tin chi tiết nhân viên (Họ tên, SĐT, Vai trò, Email)
   */
  async capNhatThongTin(id, payload, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}/thong-tin`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ ...payload, userRole }),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể cập nhật thông tin nhân viên');
    }
    return data;
  },

  /**
   * Khóa hoặc mở khóa tài khoản
   * Backend kiểm tra quyền qua header X-User-Role hoặc body userRole
   */
  async capNhatTrangThai(id, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}/trang-thai`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ userRole }),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể đổi trạng thái tài khoản');
    }
    return data;
  },

  /**
   * Thêm tài khoản mới
   * Backend kiểm tra quyền qua header X-User-Role hoặc body userRole
   */
  async themTaiKhoan(payload, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ ...payload, userRole }),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể tạo tài khoản');
    }
    return data;
  },
};
