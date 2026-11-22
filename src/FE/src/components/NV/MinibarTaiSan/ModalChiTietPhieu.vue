<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.55); z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <!-- Header -->
        <div class="modal-header bg-teal-header text-white px-4 py-3">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-text-fill fs-5"></i>
            <div>
              <h5 class="modal-title fw-bold mb-0">Chi tiết Phiếu kiểm kê / Nhật ký</h5>
              <div class="small opacity-75" v-if="phieu">
                Mã phiếu: <strong>{{ phieu.maPhieu }}</strong> &middot; {{ phieu.loaiKiemKeText }}
              </div>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" @click="dongModal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body p-4" v-if="phieu">
          <!-- Thông tin tổng quan phiếu -->
          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="row g-3 small">
              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Loại nghiệp vụ:</span>
                <span class="badge" :class="phieu.badgeLoai">
                  {{ phieu.loaiKiemKeText }}
                </span>
              </div>

              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Phòng liên quan:</span>
                <span class="fw-bold text-dark">{{ phieu.tenPhong }}</span>
              </div>

              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Thời gian thực hiện:</span>
                <span class="fw-medium text-dark">{{ phieu.thoiGianFormatted }}</span>
              </div>

              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Nhân viên phụ trách:</span>
                <span class="fw-semibold text-teal">{{ phieu.tenNhanVien }}</span>
              </div>

              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Trạng thái phiếu:</span>
                <span class="badge" :class="phieu.trangThai === 'Co_Chenh_Lech' ? 'bg-warning text-dark' : 'bg-success'">
                  {{ phieu.trangThaiText }}
                </span>
              </div>

              <div class="col-sm-6 col-md-3">
                <span class="text-muted d-block">Tổng tiền phát sinh:</span>
                <span class="fw-bold text-danger">{{ phieu.tongTienDinhDang }}</span>
              </div>

              <div class="col-12">
                <span class="text-muted d-block">Ghi chú đối soát:</span>
                <span class="text-dark">{{ phieu.ghiChu || 'Không có ghi chú thêm.' }}</span>
              </div>
            </div>
          </div>

          <!-- Chi tiết nội dung phiếu -->
          <div class="mb-3">
            <h6 class="fw-bold text-secondary mb-2 small text-uppercase">
              <i class="bi bi-list-check me-1"></i> Bảng kê chi tiết nghiệp vụ
            </h6>

            <!-- Trường hợp có danh sách sản phẩm (Kiểm kê Check-out hoặc Chuẩn bị Check-in) -->
            <div v-if="phieu.chiTiet && phieu.chiTiet.sanPham && Array.isArray(phieu.chiTiet.sanPham)" class="table-responsive border rounded-3">
              <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light extra-small text-secondary">
                  <tr>
                    <th class="ps-3 py-2">Sản phẩm</th>
                    <th class="text-center py-2">Loại</th>
                    <th class="text-center py-2" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">Dự kiến</th>
                    <th class="text-center py-2" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">Thực tế</th>
                    <th class="text-center py-2" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">Chênh lệch</th>
                    <th class="text-center py-2" v-if="phieu.loaiKiemKe === 'Chuan_Bi_CheckIn'">Ban đầu</th>
                    <th class="text-center py-2" v-if="phieu.loaiKiemKe === 'Chuan_Bi_CheckIn'">Bổ sung từ kho</th>
                    <th class="py-2" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">Lý do chênh lệch</th>
                    <th class="text-end pe-3 py-2">Thành tiền</th>
                  </tr>
                </thead>
                <tbody class="small">
                  <tr v-for="(sp, idx) in phieu.chiTiet.sanPham" :key="idx">
                    <td class="ps-3 py-2">
                      <div class="fw-semibold text-dark">{{ sp.tenSanPham }}</div>
                      <div class="text-muted extra-small" v-if="sp.donGia">
                        {{ formatCurrency(sp.donGia) }} / {{ sp.donViTinh || 'đơn vị' }}
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge" :class="sp.loaiSanPham === 'Mien_Phi' ? 'bg-success' : 'bg-primary'">
                        {{ sp.loaiSanPham === 'Mien_Phi' ? 'Miễn phí' : 'Tính phí' }}
                      </span>
                    </td>
                    <td class="text-center" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">{{ sp.soLuongDuKien }}</td>
                    <td class="text-center" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">{{ sp.soLuongThucTe }}</td>
                    <td class="text-center" v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">
                      <span :class="sp.soLuongChenhLech > 0 ? 'text-danger fw-bold' : 'text-success'">
                        {{ sp.soLuongChenhLech > 0 ? '-' + sp.soLuongChenhLech : '0' }}
                      </span>
                    </td>
                    <td class="text-center" v-if="phieu.loaiKiemKe === 'Chuan_Bi_CheckIn'">{{ sp.soLuongBanDau }}</td>
                    <td class="text-center text-teal fw-bold" v-if="phieu.loaiKiemKe === 'Chuan_Bi_CheckIn'">
                      +{{ sp.boSungTuKho || 0 }}
                    </td>
                    <td v-if="phieu.loaiKiemKe === 'Kiem_Ke_CheckOut'">
                      <span class="badge bg-light text-dark border">{{ sp.lyDoChenhLech || 'Bình thường' }}</span>
                    </td>
                    <td class="text-end pe-3 fw-bold">
                      <span :class="(sp.thanhTien || 0) > 0 ? 'text-teal' : 'text-muted'">
                        {{ formatCurrency(sp.thanhTien || 0) }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Trường hợp Nhập kho hoặc Bổ sung đơn lẻ -->
            <div v-else-if="phieu.chiTiet && phieu.chiTiet.sanPham" class="p-3 bg-light rounded-3 border small">
              <div class="row g-2">
                <div class="col-sm-6">
                  <span class="text-muted">Mặt hàng:</span>
                  <strong class="text-dark ms-1">{{ phieu.chiTiet.sanPham.tenSanPham }}</strong>
                </div>
                <div class="col-sm-6" v-if="phieu.chiTiet.sanPham.soLuongNhap">
                  <span class="text-muted">Số lượng nhập:</span>
                  <strong class="text-success ms-1">+{{ phieu.chiTiet.sanPham.soLuongNhap }}</strong>
                </div>
                <div class="col-sm-6" v-if="phieu.chiTiet.sanPham.soLuongBoSung">
                  <span class="text-muted">Số lượng bổ sung phòng:</span>
                  <strong class="text-teal ms-1">+{{ phieu.chiTiet.sanPham.soLuongBoSung }}</strong>
                </div>
                <div class="col-sm-6" v-if="phieu.chiTiet.sanPham.tonMoi">
                  <span class="text-muted">Tồn kho tổng mới:</span>
                  <strong class="text-dark ms-1">{{ phieu.chiTiet.sanPham.tonMoi }}</strong>
                </div>
              </div>
            </div>

            <!-- Trường hợp Báo hỏng tài sản -->
            <div v-else-if="phieu.chiTiet && phieu.chiTiet.taiSan" class="p-3 bg-light rounded-3 border small">
              <div class="row g-2">
                <div class="col-sm-6">
                  <span class="text-muted">Tài sản sự cố:</span>
                  <strong class="text-danger ms-1">{{ phieu.chiTiet.taiSan.tenTaiSan }}</strong>
                </div>
                <div class="col-sm-6">
                  <span class="text-muted">Loại sự cố:</span>
                  <span class="badge bg-danger ms-1">{{ phieu.chiTiet.taiSan.loaiPhatSinh }}</span>
                </div>
                <div class="col-sm-6">
                  <span class="text-muted">Số lượng:</span>
                  <strong class="ms-1">{{ phieu.chiTiet.taiSan.soLuong }}</strong>
                </div>
                <div class="col-sm-6">
                  <span class="text-muted">Đền bù đề xuất:</span>
                  <strong class="text-danger ms-1">{{ formatCurrency(phieu.chiTiet.taiSan.soTienBoiThuong) }}</strong>
                </div>
                <div class="col-12 mt-1">
                  <span class="text-muted">Mô tả chi tiết:</span>
                  <div class="mt-0.5 fst-italic text-secondary">{{ phieu.chiTiet.taiSan.moTa }}</div>
                </div>
              </div>
            </div>

            <div v-else class="text-muted small p-3 bg-light rounded-3 border">
              Không có dữ liệu chi tiết kèm theo phiếu này.
            </div>
          </div>

          <!-- Footer Buttons -->
          <div class="d-flex justify-content-end pt-2 border-top">
            <button type="button" class="btn btn-secondary rounded-3 px-4" @click="dongModal">
              Đóng
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ModalChiTietPhieu',
  props: {
    show: { type: Boolean, default: false },
    phieu: { type: Object, default: null }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const formatCurrency = (val) => {
      return Number(val || 0).toLocaleString('vi-VN') + ' đ';
    };

    const dongModal = () => {
      emit('close');
    };

    return {
      formatCurrency,
      dongModal
    };
  }
};
</script>

<style scoped>
.bg-teal-header {
  background-color: #1e5e49;
}
.text-teal {
  color: #1e5e49 !important;
}
.extra-small {
  font-size: 0.75rem;
}
</style>
