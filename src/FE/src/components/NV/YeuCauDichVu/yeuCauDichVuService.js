/**
 * API Service cho Quản lý Yêu cầu dịch vụ
 * Kết nối với Laravel Backend tại /api/quan-ly-yeu-cau-dich-vu và /api/thong-bao
 */

const API_BASE = 'http://127.0.0.1:8000/api/quan-ly-yeu-cau-dich-vu';
const API_NOTIFY = 'http://127.0.0.1:8000/api/thong-bao';
const API_DICH_VU = 'http://127.0.0.1:8000/api/dich-vu';

export default {
  /**
   * 1. Lấy danh sách yêu cầu dịch vụ kèm bộ lọc và phân trang/tabs
   * @param {Object} params { tab, q, ngay, mucDoUuTien, boPhan, maNhanVien }
   */
  async getDanhSachYeuCau(params = {}, userRole = '', userId = null) {
    const query = new URLSearchParams();
    if (params.tab && params.tab !== 'all') query.append('tab', params.tab);
    if (params.q) query.append('q', params.q);
    if (params.ngay && params.ngay !== 'Tất cả') query.append('ngay', params.ngay);
    if (params.mucDoUuTien && params.mucDoUuTien !== 'Tất cả' && params.mucDoUuTien !== 'Mọi ưu tiên') {
      query.append('mucDoUuTien', params.mucDoUuTien);
    }
    if (params.boPhan && params.boPhan !== 'Tất cả' && params.boPhan !== 'Mọi bộ phận') {
      query.append('boPhan', params.boPhan);
    }
    if (params.maNhanVien && params.maNhanVien !== 'Tất cả' && params.maNhanVien !== 'Mọi người phụ trách') {
      query.append('maNhanVien', params.maNhanVien);
    }
    if (params.chiXemViecCuaToi) {
      query.append('chiXemViecCuaToi', '1');
    }

    const headers = { 'Accept': 'application/json' };
    if (userRole) headers['X-User-Role'] = userRole;
    if (userId) headers['X-User-Id'] = String(userId);

    const url = `${API_BASE}${query.toString() ? '?' + query.toString() : ''}`;
    const res = await fetch(url, {
      method: 'GET',
      headers
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải danh sách yêu cầu (${res.status})`);
    }

    return await res.json();
  },

  /**
   * 2. Lấy chi tiết một yêu cầu dịch vụ
   */
  async getChiTietYeuCau(id) {
    const res = await fetch(`${API_BASE}/${id}`, {
      method: 'GET',
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err.message || `Lỗi tải chi tiết yêu cầu #${id}`);
    }

    return await res.json();
  },

  /**
   * 3. Tạo yêu cầu dịch vụ mới (Admin / Lễ tân tạo nhanh cho khách)
   */
  async taoYeuCau(data, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ ...data, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi tạo yêu cầu dịch vụ.');
    }

    return resData;
  },

  /**
   * 4. Tiếp nhận yêu cầu
   */
  async tiepNhan(id, data = {}, userRole = 'Lễ tân') {
    const res = await fetch(`${API_BASE}/${id}/tiep-nhan`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ ...data, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi tiếp nhận yêu cầu.');
    }

    return resData;
  },

  /**
   * 5. Từ chối yêu cầu (kèm lý do)
   */
  async tuChoi(id, lyDoTuChoi, userRole = 'Lễ tân') {
    const res = await fetch(`${API_BASE}/${id}/tu-choi`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ lyDoTuChoi, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi từ chối yêu cầu.');
    }

    return resData;
  },

  /**
   * 5b. Hủy yêu cầu (Admin / Lễ tân hủy kèm lý do)
   */
  async huyYeuCau(id, lyDoHuy, userRole = 'Lễ tân') {
    const res = await fetch(`${API_BASE}/${id}/huy`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ lyDoHuy, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi hủy yêu cầu.');
    }

    return resData;
  },

  /**
   * 6. Phân công nhân viên phụ trách
   */
  async phanCong(id, data, userRole = 'Admin') {
    const res = await fetch(`${API_BASE}/${id}/phan-cong`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ ...data, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi phân công nhân viên.');
    }

    return resData;
  },

  /**
   * 7. Nhân viên nhận việc trực tiếp
   */
  async nhanViec(id, maNhanVien, userRole = 'Nhân viên') {
    const res = await fetch(`${API_BASE}/${id}/nhan-viec`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-User-Role': userRole
      },
      body: JSON.stringify({ maNhanVien, userRole })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi nhận việc.');
    }

    return resData;
  },

  /**
   * 8. Bắt đầu thực hiện
   */
  async batDauThucHien(id, userRole = 'Nhân viên', userId = null) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-User-Role': userRole
    };
    if (userId) headers['X-User-Id'] = String(userId);

    const res = await fetch(`${API_BASE}/${id}/bat-dau`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ userRole, userId })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi cập nhật tiến độ.');
    }

    return resData;
  },

  /**
   * 9. Hoàn thành yêu cầu
   */
  async hoanThanh(id, userRole = 'Nhân viên', userId = null) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-User-Role': userRole
    };
    if (userId) headers['X-User-Id'] = String(userId);

    const res = await fetch(`${API_BASE}/${id}/hoan-thanh`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ userRole, userId })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi hoàn tất yêu cầu.');
    }

    return resData;
  },

  /**
   * 10. Báo sự cố phát sinh
   */
  async baoSuCo(id, baoSuCo, userRole = 'Nhân viên', userId = null) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-User-Role': userRole
    };
    if (userId) headers['X-User-Id'] = String(userId);

    const res = await fetch(`${API_BASE}/${id}/bao-su-co`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ baoSuCo, userRole, userId })
    });

    const resData = await res.json().catch(() => ({}));
    if (!res.ok) {
      throw new Error(resData.message || 'Lỗi gửi báo cáo sự cố.');
    }

    return resData;
  },

  /**
   * 11. Danh sách nhân viên để phân công
   */
  async getNhanVienPhanCong(boPhan = '') {
    const query = boPhan ? `?boPhan=${encodeURIComponent(boPhan)}` : '';
    const res = await fetch(`${API_BASE}/nhan-vien-phan-cong${query}`, {
      headers: { 'Accept': 'application/json' }
    });

    if (!res.ok) {
      return { status: 'success', data: [] };
    }

    return await res.json();
  },

  /**
   * 12. Danh mục dịch vụ và phòng đang lưu trú để dùng trong form tạo yêu cầu
   */
  async getDanhSachDichVu() {
    const res = await fetch(`${API_DICH_VU}`, {
      headers: { 'Accept': 'application/json' }
    });
    return await res.json();
  },

  async getPhongDangLuuTru() {
    const res = await fetch(`${API_DICH_VU}/phong-dang-luu-tru`, {
      headers: { 'Accept': 'application/json' }
    });
    return await res.json();
  },

  /**
   * 13. Hệ thống thông báo
   */
  async getThongBao(userRole = 'Admin', userId = null) {
    const headers = {
      'Accept': 'application/json',
      'X-User-Role': userRole
    };
    if (userId) headers['X-User-Id'] = String(userId);

    const res = await fetch(`${API_NOTIFY}`, { headers });
    return await res.json();
  },

  async markAllThongBaoAsRead(userRole = 'Admin', userId = null) {
    const headers = {
      'Accept': 'application/json',
      'X-User-Role': userRole
    };
    if (userId) headers['X-User-Id'] = String(userId);

    const res = await fetch(`${API_NOTIFY}/doc-tat-ca`, {
      method: 'POST',
      headers
    });
    return await res.json();
  }
};
