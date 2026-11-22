/**
 * Service API cho chức năng Quản lý khuyến mãi
 * Kết nối trực tiếp với Laravel Backend tại /api/khuyen-mai và /api/loai-phong
 */

const API_BASE = 'http://127.0.0.1:8000/api/khuyen-mai';
const API_LOAI_PHONG = 'http://127.0.0.1:8000/api/loai-phong';

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

export const khuyenMaiService = {
  // 1. Lấy danh sách khuyến mãi kèm tìm kiếm & lọc trạng thái
  async getDanhSach(params = {}) {
    const query = new URLSearchParams();
    if (params.q) query.append('q', params.q);
    if (params.trangThai && params.trangThai !== 'Mọi trạng thái') {
      query.append('trangThai', params.trangThai);
    }
    const url = `${API_BASE}${query.toString() ? '?' + query.toString() : ''}`;
    return await request(url, { method: 'GET' });
  },

  // 2. Chi tiết khuyến mãi
  async getChiTiet(id) {
    return await request(`${API_BASE}/${id}`, { method: 'GET' });
  },

  // 3. Tạo mới khuyến mãi
  async taoKhuyenMai(payload) {
    return await request(API_BASE, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // 4. Cập nhật khuyến mãi
  async capNhatKhuyenMai(id, payload) {
    return await request(`${API_BASE}/${id}`, {
      method: 'PUT',
      body: JSON.stringify(payload)
    });
  },

  // 5. Bật / Tắt trạng thái
  async toggleTrangThai(id) {
    return await request(`${API_BASE}/${id}/toggle`, {
      method: 'POST'
    });
  },

  // 6. Xóa khuyến mãi
  async xoaKhuyenMai(id) {
    return await request(`${API_BASE}/${id}`, {
      method: 'DELETE'
    });
  },

  // 7. Kiểm tra mã khuyến mãi dành cho Khách hàng / Đặt phòng
  async kiemTraMa(payload) {
    return await request(`${API_BASE}/kiem-tra`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // 8. Xem trước cách tính số tiền được giảm
  async xemTruoc(payload) {
    return await request(`${API_BASE}/xem-truoc`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // 9. Thanh toán hóa đơn thực tế có áp dụng khuyến mãi
  async thanhToanHoaDon(payload) {
    return await request(`${API_BASE}/thanh-toan-hoa-don`, {
      method: 'POST',
      body: JSON.stringify(payload)
    });
  },

  // 10. Lấy danh sách loại phòng thực tế từ Quản lý loại phòng
  async getDanhSachLoaiPhong() {
    return await request(API_LOAI_PHONG, { method: 'GET' });
  }
};

export default khuyenMaiService;
