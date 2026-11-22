/**
 * API Service cho Quản lý Loại phòng & Tiện nghi
 */

const API_BASE = 'http://127.0.0.1:8000/api/loai-phong';

export default {
  /**
   * Lấy danh sách loại phòng với bộ lọc và tìm kiếm
   */
  async getDanhSachLoaiPhong(filters = {}) {
    const params = new URLSearchParams();
    if (filters.q) params.append('q', filters.q);
    if (filters.trangThai && filters.trangThai !== 'Tất cả') params.append('trangThai', filters.trangThai);
    if (filters.giaMin !== undefined && filters.giaMin !== null && filters.giaMin !== '') params.append('giaMin', filters.giaMin);
    if (filters.giaMax !== undefined && filters.giaMax !== null && filters.giaMax !== '') params.append('giaMax', filters.giaMax);
    if (filters.sucChua !== undefined && filters.sucChua !== null && filters.sucChua !== '') params.append('sucChua', filters.sucChua);

    const url = `${API_BASE}${params.toString() ? '?' + params.toString() : ''}`;
    const res = await fetch(url);
    if (!res.ok) {
      throw new Error(`Lỗi tải danh sách loại phòng: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Xem chi tiết 1 loại phòng
   */
  async getChiTietLoaiPhong(id) {
    const res = await fetch(`${API_BASE}/${id}`);
    if (!res.ok) {
      throw new Error(`Lỗi tải chi tiết: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Lấy danh mục tiện nghi
   */
  async getDanhSachTienNghi() {
    const res = await fetch(`${API_BASE}/tien-nghi`);
    if (!res.ok) {
      throw new Error(`Lỗi tải tiện nghi: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * Thêm mới loại phòng
   */
  async themLoaiPhong(payload) {
    const res = await fetch(`${API_BASE}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể tạo loại phòng');
    }
    return data;
  },

  /**
   * Alias themMoiLoaiPhong
   */
  async themMoiLoaiPhong(payload) {
    return await this.themLoaiPhong(payload);
  },

  /**
   * Cập nhật loại phòng
   */
  async capNhatLoaiPhong(id, payload) {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể cập nhật loại phòng');
    }
    return data;
  },

  /**
   * Đổi trạng thái kinh doanh (Đang mở bán <-> Ngừng kinh doanh)
   */
  async doiTrangThai(id, trangThai = null) {
    const res = await fetch(`${API_BASE}/${id}/trang-thai`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ trangThai }),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể đổi trạng thái loại phòng');
    }
    return data;
  },

  /**
   * Xóa hoặc ngừng kinh doanh loại phòng
   */
  async xoaLoaiPhong(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
      },
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể xóa loại phòng');
    }
    return data;
  },

  /**
   * Thiết lập hoặc chỉnh sửa khuyến mãi cho loại phòng
   */
  async thietLapKhuyenMaiLoaiPhong(payload) {
    const res = await fetch('http://127.0.0.1:8000/api/khuyen-mai/thiet-lap-loai-phong', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể thiết lập khuyến mãi');
    }
    return data;
  },

  /**
   * Bật / Tắt khuyến mãi
   */
  async toggleKhuyenMai(promoId) {
    const res = await fetch(`http://127.0.0.1:8000/api/khuyen-mai/${promoId}/toggle`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
    });

    const data = await res.json();
    if (!res.ok) {
      throw new Error(data.message || 'Không thể đổi trạng thái khuyến mãi');
    }
    return data;
  },
};
