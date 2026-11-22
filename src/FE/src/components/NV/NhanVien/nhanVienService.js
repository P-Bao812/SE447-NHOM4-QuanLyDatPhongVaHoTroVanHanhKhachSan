/**
 * API Service cho Quản lý Nhân viên
 * Tương tác trực tiếp với Laravel Backend tại /api/nhan-vien
 */

const API_BASE = 'http://127.0.0.1:8000/api/nhan-vien';

export default {
  /**
   * 1. Lấy danh sách nhân viên (kèm tìm kiếm & bộ lọc)
   * @param {Object} filters { q, boPhan, caLam, trangThai }
   */
  async getDanhSachNhanVien(filters = {}) {
    const params = new URLSearchParams();
    if (filters.q) params.append('q', filters.q);
    if (filters.boPhan && filters.boPhan !== 'Tất cả') params.append('boPhan', filters.boPhan);
    if (filters.caLam && filters.caLam !== 'Tất cả') params.append('caLam', filters.caLam);
    if (filters.trangThai && filters.trangThai !== 'Tất cả') params.append('trangThai', filters.trangThai);

    const queryString = params.toString() ? `?${params.toString()}` : '';
    const res = await fetch(`${API_BASE}${queryString}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải danh sách nhân viên (${res.status})`);
    }

    return await res.json();
  },

  /**
   * 2. Lấy 3 thẻ thống kê: Tổng nhân viên, Đang trong ca, Ca làm việc hiện tại
   */
  async getThongKe() {
    const res = await fetch(`${API_BASE}/thong-ke`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải thống kê nhân viên (${res.status})`);
    }

    return await res.json();
  },

  /**
   * 3. Lấy thông tin chi tiết một nhân viên
   * @param {number|string} id
   */
  async getChiTietNhanVien(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải chi tiết nhân viên #${id}`);
    }

    return await res.json();
  },

  /**
   * 4. Thêm mới nhân viên
   * @param {Object} data
   * @param {string} userRole Role của người thực hiện để Backend kiểm tra quyền
   */
  async themNhanVien(data, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ ...data, userRole }),
    });

    const resData = await res.json().catch(() => ({}));

    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi thêm mới nhân viên.');
    }

    return resData;
  },

  /**
   * 5. Chỉnh sửa thông tin nhân viên
   * @param {number|string} id
   * @param {Object} data
   * @param {string} userRole
   */
  async capNhatNhanVien(id, data, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'POST', // hoặc PUT
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ ...data, userRole }),
    });

    const resData = await res.json().catch(() => ({}));

    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi cập nhật thông tin nhân viên.');
    }

    return resData;
  },

  /**
   * 6. Cập nhật nhanh trạng thái làm việc (Đang trong ca, Nghỉ ca, Tạm nghỉ, Đã nghỉ việc)
   * @param {number|string} id
   * @param {string} trangThai
   * @param {string} userRole
   */
  async capNhatTrangThai(id, trangThai, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}/trang-thai`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
      body: JSON.stringify({ trangThai, userRole }),
    });

    const resData = await res.json().catch(() => ({}));

    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi cập nhật trạng thái làm việc.');
    }

    return resData;
  },

  /**
   * 7. Xóa hoặc vô hiệu hóa nhân viên
   * @param {number|string} id
   * @param {string} userRole
   */
  async xoaNhanVien(id, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-User-Role': userRole,
      },
    });

    const resData = await res.json().catch(() => ({}));

    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi xóa nhân viên.');
    }

    return resData;
  },
};
