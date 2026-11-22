/**
 * API Service cho Dịch vụ phòng & Đăng ký sử dụng dịch vụ khách sạn
 */

const API_BASE = 'http://127.0.0.1:8000/api/dich-vu';

export default {
  /**
   * 1. Lấy danh sách dịch vụ đang cung cấp từ cơ sở dữ liệu
   * Hỗ trợ tìm kiếm theo tên và lọc theo loại dịch vụ
   */
  async getDanhSachDichVu(filters = {}) {
    const params = new URLSearchParams();
    if (filters.q) params.append('q', filters.q);
    if (filters.loai && filters.loai !== 'Tất cả') params.append('loai', filters.loai);

    const url = `${API_BASE}${params.toString() ? '?' + params.toString() : ''}`;
    const res = await fetch(url);
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải danh sách dịch vụ: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * 2. Lấy danh sách danh mục loại dịch vụ
   */
  async getLoaiDichVu() {
    const res = await fetch(`${API_BASE}/loai-dich-vu`);
    if (!res.ok) {
      throw new Error(`Lỗi tải loại dịch vụ: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * 3. Lấy danh sách các phòng đang có lưu trú hợp lệ
   */
  async getPhongDangLuuTru() {
    const res = await fetch(`${API_BASE}/phong-dang-luu-tru`);
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải danh sách phòng lưu trú: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * 4. Gửi đăng ký sử dụng dịch vụ phòng
   */
  async dangKyDichVu(payload) {
    const res = await fetch(`${API_BASE}/dang-ky`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(data.message || 'Lỗi khi gửi yêu cầu sử dụng dịch vụ.');
    }
    return data;
  },

  /**
   * 5. Lấy danh sách yêu cầu dịch vụ đã đăng ký
   */
  async getDanhSachYeuCau(filters = {}) {
    const params = new URLSearchParams();
    if (filters.maLuuTru) params.append('maLuuTru', filters.maLuuTru);
    if (filters.maPhong) params.append('maPhong', filters.maPhong);
    if (filters.trangThai && filters.trangThai !== 'Tất cả') params.append('trangThai', filters.trangThai);

    const url = `${API_BASE}/yeu-cau${params.toString() ? '?' + params.toString() : ''}`;
    const res = await fetch(url);
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải danh sách yêu cầu: ${res.statusText}`);
    }
    return await res.json();
  },

  /**
   * 6. Cập nhật trạng thái yêu cầu (Lễ tân/Nhân viên xử lý)
   */
  async capNhatTrangThaiYeuCau(id, trangThai) {
    const res = await fetch(`${API_BASE}/yeu-cau/${id}/trang-thai`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ trangThai })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(data.message || 'Lỗi khi cập nhật trạng thái yêu cầu.');
    }
    return data;
  },

  /**
   * 7. Khách hàng hủy yêu cầu dịch vụ
   */
  async huyYeuCau(id, lyDoHuy = 'Khách hủy yêu cầu') {
    const res = await fetch(`${API_BASE}/yeu-cau/${id}/huy`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ lyDoHuy })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(data.message || 'Lỗi khi hủy yêu cầu dịch vụ.');
    }
    return data;
  }
};
