/**
 * Service API cho chức năng Quản lý Minibar & Tài sản phòng (FR24)
 * Kết nối với Laravel Backend tại /api/minibar-tai-san
 */

const API_BASE = 'http://127.0.0.1:8000/api/minibar-tai-san';

async function request(url, options = {}) {
  const defaultHeaders = {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  };

  const response = await fetch(url, {
    ...options,
    headers: {
      ...defaultHeaders,
      ...(options.headers || {})
    }
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || `Lỗi máy chủ (${response.status})`);
    error.response = { data, status: response.status };
    throw error;
  }

  return data;
}

export const minibarTaiSanService = {
  // ---------------- TAB 1: MINIBAR & TỒN KHO ----------------
  async getDanhSachSanPham(params = {}) {
    const query = new URLSearchParams();
    if (params.q) query.append('q', params.q);
    if (params.loaiSanPham && params.loaiSanPham !== 'Tất cả') {
      query.append('loaiSanPham', params.loaiSanPham);
    }
    if (params.trangThai && params.trangThai !== 'Tất cả') {
      query.append('trangThai', params.trangThai);
    }
    const url = `${API_BASE}/san-pham${query.toString() ? '?' + query.toString() : ''}`;
    return await request(url, { method: 'GET' });
  },

  async themSanPham(payload) {
    return await request(`${API_BASE}/san-pham`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  async capNhatSanPham(id, payload) {
    return await request(`${API_BASE}/san-pham/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload)
    });
  },

  async nhapKhoTong(payload) {
    return await request(`${API_BASE}/nhap-kho`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  async getDanhSachPhong(params = {}) {
    const query = new URLSearchParams();
    if (params.tang && params.tang !== 'Tất cả') query.append('tang', params.tang);
    if (params.trangThai && params.trangThai !== 'Tất cả') query.append('trangThai', params.trangThai);
    const url = `${API_BASE}/phong${query.toString() ? '?' + query.toString() : ''}`;
    return await request(url, { method: 'GET' });
  },

  async getChiTietMinibarPhong(maPhong) {
    return await request(`${API_BASE}/phong/${maPhong}/minibar`, { method: 'GET' });
  },

  async chuanBiCheckIn(payload) {
    return await request(`${API_BASE}/chuan-bi-checkin`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  async boSungMinibar(payload) {
    return await request(`${API_BASE}/bo-sung-phong`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  async kiemKeCheckOut(payload) {
    return await request(`${API_BASE}/kiem-ke-checkout`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // ---------------- TAB 2: TÀI SẢN THEO PHÒNG ----------------
  async getDanhSachTaiSan(params = {}) {
    const query = new URLSearchParams();
    if (params.q) query.append('q', params.q);
    if (params.maPhong && params.maPhong !== 'Tất cả') query.append('maPhong', params.maPhong);
    if (params.tinhTrang && params.tinhTrang !== 'Tất cả') query.append('tinhTrang', params.tinhTrang);
    const url = `${API_BASE}/tai-san${query.toString() ? '?' + query.toString() : ''}`;
    return await request(url, { method: 'GET' });
  },

  async themTaiSan(payload) {
    return await request(`${API_BASE}/tai-san`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  async baoHongTaiSan(payload) {
    return await request(`${API_BASE}/bao-hong-tai-san`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // ---------------- TAB 3: NHẬT KÝ KIỂM KÊ ----------------
  async getDanhSachNhatKy(params = {}) {
    const query = new URLSearchParams();
    if (params.q) query.append('q', params.q);
    if (params.loaiKiemKe && params.loaiKiemKe !== 'Tất cả') query.append('loaiKiemKe', params.loaiKiemKe);
    if (params.trangThai && params.trangThai !== 'Tất cả') query.append('trangThai', params.trangThai);
    if (params.ngay) query.append('ngay', params.ngay);
    const url = `${API_BASE}/nhat-ky${query.toString() ? '?' + query.toString() : ''}`;
    return await request(url, { method: 'GET' });
  },

  async getChiTietNhatKy(id) {
    return await request(`${API_BASE}/nhat-ky/${id}`, { method: 'GET' });
  }
};
