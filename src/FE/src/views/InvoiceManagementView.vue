<template>
  <div class="invoice-management-view py-4">
    <div class="container-fluid px-lg-4">
      <!-- Breadcrumb & Tiêu đề trang -->
      <nav aria-label="breadcrumb" class="mb-3 d-print-none">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item">
            <router-link to="/receptionist/front-desk" class="text-decoration-none text-teal">
              Lễ tân & Vận hành
            </router-link>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quản lý hóa đơn & Thanh toán (UC07)</li>
        </ol>
      </nav>

      <div class="row align-items-center mb-4 g-3 d-print-none">
        <div class="col-lg-7">
          <div class="d-flex align-items-center gap-3">
            <div class="header-icon-box bg-navy text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm">
              <i class="bi bi-receipt-cutoff fs-3"></i>
            </div>
            <div>
              <h2 class="fw-bold text-navy mb-1">Quản Lý Hóa Đơn & Thanh Toán</h2>
              <p class="text-muted mb-0 small">
                Tổng hợp chi phí phòng, dịch vụ, minibar, điều chỉnh phụ thu và ghi nhận thanh toán tiền mặt / QR / thẻ (UC07).
              </p>
            </div>
          </div>
        </div>

        <div class="col-lg-5 text-lg-end">
          <div class="d-inline-flex flex-column align-items-lg-end gap-1">
            <div class="d-flex align-items-center gap-2">
              <span
                class="badge rounded-pill px-3 py-2 fw-semibold"
                :class="isAuthorized ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle'"
              >
                <i :class="isAuthorized ? 'bi bi-shield-check me-1' : 'bi bi-exclamation-triangle-fill me-1'"></i>
                Vai trò: {{ currentUser.vaiTro || 'Chưa xác thực' }}
              </span>
              <span class="small fw-semibold text-navy">
                {{ currentUser.hoTen || currentUser.tenDangNhap || 'Người dùng' }}
              </span>
            </div>
            <span class="text-muted small" style="font-size: 0.75rem;">
              {{ isAuthorized ? 'Có quyền lập, xem, thêm phụ thu và ghi nhận thanh toán hóa đơn' : 'Cần đăng nhập tài khoản Lễ tân hoặc Quản lý để thao tác' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Quick Test & Switch Account Toolbar -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
        <div class="card-body p-3">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <span class="badge bg-navy text-white px-2 py-1">
                <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Quick-Test
              </span>
              <span class="small text-muted">Chuyển đổi tài khoản kiểm thử:</span>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'letan01' }"
                :disabled="isLoading"
                @click="quickSwitchUser('letan01', '123456', 'Lễ tân')"
              >
                <i class="bi bi-person-badge me-1"></i> Lễ tân (letan01)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary rounded-3"
                :class="{ 'btn-secondary text-white': currentUser.tenDangNhap === 'admin' }"
                :disabled="isLoading"
                @click="quickSwitchUser('admin', '123456', 'Quản lý')"
              >
                <i class="bi bi-shield-lock me-1"></i> Quản lý (admin)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-danger rounded-3"
                :class="{ 'btn-danger text-white': currentUser.tenDangNhap === 'buongphong01' }"
                :disabled="isLoading"
                @click="quickSwitchUser('buongphong01', '123456', 'Buồng phòng')"
              >
                <i class="bi bi-slash-circle me-1"></i> Buồng phòng (Thử 403)
              </button>
              <button
                type="button"
                class="btn btn-sm btn-outline-warning rounded-3"
                :class="{ 'btn-warning text-dark': currentUser.tenDangNhap === 'nguyenvanminh' }"
                :disabled="isLoading"
                @click="quickSwitchUser('nguyenvanminh', '123456', 'Khách hàng')"
              >
                <i class="bi bi-person me-1"></i> Khách hàng (Xem đơn riêng)
              </button>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button
                type="button"
                class="btn btn-sm btn-outline-navy rounded-3 px-3"
                :disabled="isLoading"
                @click="fetchInvoices"
              >
                <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin-animation': isLoading }"></i>
                Làm mới
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Banner Thông báo Kết quả / Lỗi -->
      <div v-if="alertMessage" class="alert alert-dismissible fade show rounded-3 mb-4 shadow-sm border-0 d-print-none" :class="alertClass" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i :class="alertIcon" class="fs-5"></i>
          <div>{{ alertMessage }}</div>
        </div>
        <button type="button" class="btn-close" aria-label="Close" @click="alertMessage = ''"></button>
      </div>

      <!-- Thống kê nhanh KPI Cards -->
      <div class="row g-3 mb-4 d-print-none">
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-navy-subtle text-navy rounded-3 p-3">
                <i class="bi bi-receipt fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Tổng hóa đơn</small>
                <h4 class="fw-bold mb-0 text-navy">{{ statistics.total }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-warning-subtle text-warning rounded-3 p-3">
                <i class="bi bi-hourglass-split fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Chờ thanh toán</small>
                <h4 class="fw-bold mb-0 text-warning">{{ statistics.pending }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-success-subtle text-success rounded-3 p-3">
                <i class="bi bi-check-circle-fill fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Đã thanh toán</small>
                <h4 class="fw-bold mb-0 text-success">{{ statistics.paid }}</h4>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-3 d-flex align-items-center gap-3">
              <div class="kpi-icon-box bg-teal-subtle text-teal rounded-3 p-3">
                <i class="bi bi-cash-stack fs-4"></i>
              </div>
              <div>
                <small class="text-muted d-block fw-medium">Đã thu thực tế</small>
                <h5 class="fw-bold mb-0 text-teal text-truncate">{{ formatCurrency(statistics.totalCollected) }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Khối Lọc & Tìm kiếm -->
      <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
        <div class="card-body p-4">
          <div class="row g-3 align-items-end">
            <!-- Tìm kiếm từ khóa -->
            <div class="col-12 col-md-4">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-search me-1"></i> Tìm kiếm hóa đơn
              </label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-search"></i>
                </span>
                <input
                  v-model.trim="filters.keyword"
                  type="text"
                  class="form-control border-start-0 ps-0"
                  placeholder="Mã HĐ, số phòng, tên khách, SĐT..."
                  @keyup.enter="handleSearch"
                />
              </div>
            </div>

            <!-- Lọc trạng thái -->
            <div class="col-6 col-md-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-funnel me-1"></i> Trạng thái
              </label>
              <select v-model="filters.trangThai" class="form-select" @change="handleSearch">
                <option value="">Tất cả trạng thái</option>
                <option value="Chờ thanh toán">Chờ thanh toán</option>
                <option value="Đã thanh toán">Đã thanh toán</option>
              </select>
            </div>

            <!-- Ngày lập từ -->
            <div class="col-6 col-md-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-calendar3 me-1"></i> Ngày lập từ
              </label>
              <input
                v-model="filters.ngayLapTu"
                type="date"
                class="form-control"
                @change="handleSearch"
              />
            </div>

            <!-- Ngày lập đến -->
            <div class="col-6 col-md-2">
              <label class="form-label small fw-semibold text-navy mb-1">
                <i class="bi bi-calendar3 me-1"></i> Đến ngày
              </label>
              <input
                v-model="filters.ngayLapDen"
                type="date"
                class="form-control"
                @change="handleSearch"
              />
            </div>

            <!-- Nút tìm kiếm & Xóa lọc -->
            <div class="col-6 col-md-2 d-flex gap-2">
              <button
                type="button"
                class="btn btn-navy w-100 fw-semibold rounded-3"
                :disabled="isLoading"
                @click="handleSearch"
              >
                <i class="bi bi-search me-1"></i> Tìm
              </button>
              <button
                type="button"
                class="btn btn-outline-secondary rounded-3"
                title="Xóa bộ lọc"
                :disabled="isLoading"
                @click="resetFilters"
              >
                <i class="bi bi-arrow-counterclockwise"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Bảng Danh sách Hóa đơn -->
      <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4 d-print-none">
        <div class="card-header bg-white py-3 px-4 border-0 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <h5 class="fw-bold text-navy mb-0">Danh Sách Hóa Đơn</h5>
            <span class="badge bg-light text-navy border px-2 py-1 rounded-pill">
              {{ pagination.total }} hóa đơn
            </span>
          </div>

          <div class="d-flex align-items-center gap-2">
            <label class="small text-muted me-1 d-none d-sm-inline">Hiển thị:</label>
            <select v-model.number="pagination.perPage" class="form-select form-select-sm" style="width: auto;" @change="handleSearch">
              <option :value="10">10 / trang</option>
              <option :value="15">15 / trang</option>
              <option :value="25">25 / trang</option>
              <option :value="50">50 / trang</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 custom-invoice-table">
            <thead class="bg-light">
              <tr class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                <th class="ps-4 py-3">Mã HĐ</th>
                <th class="py-3">Khách hàng</th>
                <th class="py-3">Phòng</th>
                <th class="py-3">Ngày lập</th>
                <th class="text-end py-3">Tổng tiền</th>
                <th class="text-end py-3">Đã trả</th>
                <th class="text-end py-3">Còn lại</th>
                <th class="text-center py-3">Trạng thái</th>
                <th class="text-end pe-4 py-3">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <!-- Spinner khi tải dữ liệu -->
              <tr v-if="isLoading">
                <td colspan="9" class="text-center py-5">
                  <div class="spinner-border text-navy mb-2" role="status">
                    <span class="visually-hidden">Đang tải dữ liệu...</span>
                  </div>
                  <div class="text-muted small">Đang nạp danh sách hóa đơn từ hệ thống...</div>
                </td>
              </tr>

              <!-- Khi không có dữ liệu -->
              <tr v-else-if="invoices.length === 0">
                <td colspan="9" class="text-center py-5">
                  <div class="empty-state text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    <p class="fw-semibold mb-1">Không tìm thấy hóa đơn nào phù hợp</p>
                    <small>Vui lòng thử thay đổi từ khóa tìm kiếm hoặc điều kiện lọc ngày/trạng thái.</small>
                  </div>
                </td>
              </tr>

              <!-- Dữ liệu hóa đơn -->
              <tr v-for="inv in invoices" :key="inv.maHoaDon" class="transition-row">
                <td class="ps-4 fw-bold text-navy">
                  #HD-{{ String(inv.maHoaDon).padStart(4, '0') }}
                </td>
                <td>
                  <div class="fw-semibold text-navy">{{ inv.tenKhachHang || 'Khách vãng lai' }}</div>
                  <small class="text-muted">
                    <i class="bi bi-telephone me-1"></i>{{ inv.soDienThoai || 'Chưa cập nhật' }}
                  </small>
                </td>
                <td>
                  <span class="badge bg-navy-subtle text-navy border border-navy-subtle px-2.5 py-1.5 fw-bold rounded-3">
                    <i class="bi bi-door-closed me-1"></i>{{ inv.tenPhong || 'Chưa gán' }}
                  </span>
                </td>
                <td class="small text-muted">
                  {{ formatDate(inv.ngayLap) }}
                </td>
                <td class="text-end fw-bold text-navy">
                  {{ formatCurrency(inv.tongTien) }}
                </td>
                <td class="text-end text-success fw-semibold">
                  {{ formatCurrency(inv.daThanhToan) }}
                </td>
                <td class="text-end fw-bold" :class="inv.conLai > 0 ? 'text-danger' : 'text-muted'">
                  {{ formatCurrency(inv.conLai) }}
                </td>
                <td class="text-center">
                  <span
                    class="badge rounded-pill px-3 py-1.5 fw-semibold"
                    :class="inv.trangThai === 'Đã thanh toán' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle'"
                  >
                    <i :class="inv.trangThai === 'Đã thanh toán' ? 'bi bi-check2-circle me-1' : 'bi bi-hourglass-split me-1'"></i>
                    {{ inv.trangThai }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <div class="d-inline-flex gap-1.5">
                    <!-- Nút Xem chi tiết -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-navy rounded-3"
                      title="Xem chi tiết hóa đơn"
                      @click="openDetailModal(inv.maHoaDon)"
                    >
                      <i class="bi bi-eye"></i>
                    </button>

                    <!-- Nút Thêm phụ thu (Chỉ khi chưa thanh toán) -->
                    <button
                      v-if="inv.trangThai !== 'Đã thanh toán'"
                      type="button"
                      class="btn btn-sm btn-outline-secondary rounded-3"
                      title="Thêm khoản phụ thu / đền bù"
                      :disabled="!isAuthorized"
                      @click="openAdjustmentModal(inv)"
                    >
                      <i class="bi bi-plus-circle"></i>
                    </button>

                    <!-- Nút Ghi nhận thanh toán (Chỉ khi còn nợ) -->
                    <button
                      v-if="inv.trangThai !== 'Đã thanh toán' && inv.conLai > 0"
                      type="button"
                      class="btn btn-sm btn-teal text-white rounded-3 fw-semibold"
                      title="Ghi nhận thanh toán"
                      :disabled="!isAuthorized"
                      @click="openPaymentModal(inv)"
                    >
                      <i class="bi bi-credit-card me-1"></i> Trả
                    </button>

                    <!-- Nút In hóa đơn -->
                    <button
                      type="button"
                      class="btn btn-sm btn-outline-secondary rounded-3"
                      title="In phiếu hóa đơn"
                      @click="printInvoiceDirectly(inv.maHoaDon)"
                    >
                      <i class="bi bi-printer"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Phân trang Pagination -->
        <div v-if="pagination.lastPage > 1" class="card-footer bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
          <div class="small text-muted">
            Trang {{ pagination.currentPage }} / {{ pagination.lastPage }} (Tổng {{ pagination.total }} hóa đơn)
          </div>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: pagination.currentPage <= 1 }">
              <button class="page-link" @click="changePage(pagination.currentPage - 1)">
                <i class="bi bi-chevron-left"></i>
              </button>
            </li>
            <li
              v-for="page in displayedPages"
              :key="page"
              class="page-item"
              :class="{ active: pagination.currentPage === page }"
            >
              <button class="page-link" @click="changePage(page)">{{ page }}</button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.currentPage >= pagination.lastPage }">
              <button class="page-link" @click="changePage(pagination.currentPage + 1)">
                <i class="bi bi-chevron-right"></i>
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 1: CHI TIẾT HÓA ĐƠN & XEM TRƯỚC BẢN IN            -->
      <!-- ======================================================= -->
      <div v-if="showDetailModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showDetailModal = false">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-receipt fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">
                    Chi Tiết Hóa Đơn #HD-{{ String(selectedInvoice?.maHoaDon || '').padStart(4, '0') }}
                  </h5>
                  <small class="text-white-50">Ngày lập: {{ formatDate(selectedInvoice?.ngayLap) }}</small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showDetailModal = false"></button>
            </div>

            <div class="modal-body p-4" id="printable-invoice-area">
              <!-- Loading khi nạp chi tiết -->
              <div v-if="isDetailLoading" class="text-center py-5">
                <div class="spinner-border text-navy mb-2" role="status"></div>
                <div class="text-muted small">Đang tải thông tin chi tiết hóa đơn...</div>
              </div>

              <div v-else-if="selectedInvoice">
                <!-- Thông tin khách hàng & phòng -->
                <div class="row g-3 p-3 bg-light rounded-3 mb-4">
                  <div class="col-sm-6">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Khách hàng</small>
                    <div class="fw-bold text-navy fs-6">{{ selectedInvoice.khachHang?.hoTen || 'Khách vãng lai' }}</div>
                    <div class="small text-muted">
                      <i class="bi bi-telephone me-1"></i>{{ selectedInvoice.khachHang?.soDienThoai || 'N/A' }}
                      <span v-if="selectedInvoice.khachHang?.email"> | {{ selectedInvoice.khachHang?.email }}</span>
                    </div>
                    <div v-if="selectedInvoice.khachHang?.cccd" class="small text-muted">
                      CCCD/Hộ chiếu: {{ selectedInvoice.khachHang.cccd }}
                    </div>
                  </div>

                  <div class="col-sm-6 text-sm-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Phòng lưu trú</small>
                    <div class="fw-bold text-teal fs-6">
                      Phòng {{ selectedInvoice.phong?.tenPhong || 'N/A' }} (Tầng {{ selectedInvoice.phong?.tang || 1 }})
                    </div>
                    <div class="small text-muted">Loại phòng: {{ selectedInvoice.phong?.tenLoai || 'Tiêu chuẩn' }}</div>
                    <div class="mt-1">
                      <span
                        class="badge rounded-pill px-3 py-1"
                        :class="selectedInvoice.trangThai === 'Đã thanh toán' ? 'bg-success text-white' : 'bg-warning text-dark'"
                      >
                        {{ selectedInvoice.trangThai }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Bảng các mục chi tiết hóa đơn -->
                <h6 class="fw-bold text-navy mb-2 d-flex align-items-center gap-2">
                  <i class="bi bi-list-check"></i> Các Khoản Thu & Chi Tiết Dịch Vụ
                </h6>
                <div class="table-responsive mb-3">
                  <table class="table table-bordered align-middle">
                    <thead class="table-light small">
                      <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Nội dung mục thu</th>
                        <th class="text-center" style="width: 100px;">Số lượng</th>
                        <th class="text-end" style="width: 130px;">Đơn giá</th>
                        <th class="text-end" style="width: 140px;">Thành tiền</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="!selectedInvoice.chiTiet || selectedInvoice.chiTiet.length === 0">
                        <td colspan="5" class="text-center text-muted py-3">Chưa có chi tiết khoản mục nào</td>
                      </tr>
                      <tr v-for="(item, idx) in selectedInvoice.chiTiet" :key="item.id || idx">
                        <td class="text-center text-muted small">{{ idx + 1 }}</td>
                        <td class="fw-medium text-navy">{{ item.noiDung }}</td>
                        <td class="text-center">{{ item.soLuong }}</td>
                        <td class="text-end">{{ formatCurrency(item.donGia) }}</td>
                        <td class="text-end fw-semibold text-navy">{{ formatCurrency(item.thanhTien) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Tóm tắt thanh toán & Tiền cọc -->
                <div class="row justify-content-end mb-4">
                  <div class="col-md-6">
                    <ul class="list-group list-group-flush rounded-3 border">
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">Tiền cọc đặt trước:</span>
                        <span class="fw-semibold text-secondary">-{{ formatCurrency(selectedInvoice.tienCoc || 0) }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2 bg-light">
                        <span class="fw-bold text-navy">Tổng thanh toán:</span>
                        <span class="fw-bold fs-6 text-navy">{{ formatCurrency(selectedInvoice.tongTien) }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="text-success small fw-medium">Đã thanh toán:</span>
                        <span class="fw-bold text-success">{{ formatCurrency(selectedInvoice.daThanhToan) }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between align-items-center py-2 bg-warning-subtle">
                        <span class="fw-bold text-danger">Còn lại phải thu:</span>
                        <span class="fw-bold fs-5 text-danger">{{ formatCurrency(selectedInvoice.conLai) }}</span>
                      </li>
                    </ul>
                  </div>
                </div>

                <!-- Lịch sử giao dịch thanh toán -->
                <h6 class="fw-bold text-navy mb-2 d-flex align-items-center gap-2">
                  <i class="bi bi-clock-history"></i> Lịch Sử Giao Dịch Thanh Toán
                </h6>
                <div class="table-responsive">
                  <table class="table table-sm table-striped border align-middle mb-0">
                    <thead class="table-light small">
                      <tr>
                        <th>Thời gian</th>
                        <th>Phương thức</th>
                        <th>Mã giao dịch</th>
                        <th class="text-end">Số tiền</th>
                        <th class="text-center">Trạng thái</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-if="!selectedInvoice.thanhToans || selectedInvoice.thanhToans.length === 0">
                        <td colspan="5" class="text-center text-muted py-2 small">Chưa phát sinh giao dịch thanh toán nào.</td>
                      </tr>
                      <tr v-for="tt in selectedInvoice.thanhToans" :key="tt.id" class="small">
                        <td>{{ formatDate(tt.thoiGian) }}</td>
                        <td>
                          <span class="badge bg-light text-dark border text-capitalize">
                            {{ tt.phuongThuc }}
                          </span>
                        </td>
                        <td class="font-monospace text-navy">{{ tt.maGiaoDich }}</td>
                        <td class="text-end fw-bold text-success">{{ formatCurrency(tt.soTien) }}</td>
                        <td class="text-center">
                          <span class="badge bg-success-subtle text-success border border-success-subtle">
                            {{ tt.trangThai }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-between">
              <div class="d-flex gap-2">
                <!-- Nút in hóa đơn -->
                <button type="button" class="btn btn-outline-navy rounded-3 px-3" @click="printCurrentInvoice">
                  <i class="bi bi-printer me-1"></i> In Hóa Đơn
                </button>
                <!-- Nút thêm phụ thu -->
                <button
                  v-if="selectedInvoice?.trangThai !== 'Đã thanh toán'"
                  type="button"
                  class="btn btn-outline-secondary rounded-3"
                  :disabled="!isAuthorized"
                  @click="openAdjustmentFromDetail"
                >
                  <i class="bi bi-plus-circle me-1"></i> Thêm Phụ Thu
                </button>
              </div>

              <div class="d-flex gap-2">
                <button
                  v-if="selectedInvoice?.trangThai !== 'Đã thanh toán' && selectedInvoice?.conLai > 0"
                  type="button"
                  class="btn btn-teal text-white rounded-3 fw-semibold px-4"
                  :disabled="!isAuthorized"
                  @click="openPaymentFromDetail"
                >
                  <i class="bi bi-credit-card me-1"></i> Thanh Toán Ngay
                </button>
                <button type="button" class="btn btn-secondary rounded-3 px-4" @click="showDetailModal = false">
                  Đóng
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 2: THÊM KHOẢN PHỤ THU / ĐIỀU CHỈNH                -->
      <!-- ======================================================= -->
      <div v-if="showAdjustmentModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showAdjustmentModal = false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-navy text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle fs-5 text-warning"></i>
                <h5 class="modal-title fw-bold mb-0">Thêm Khoản Phụ Thu / Điều Chỉnh</h5>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showAdjustmentModal = false"></button>
            </div>

            <form @submit.prevent="submitAdjustment">
              <div class="modal-body p-4">
                <!-- Cảnh báo nếu hóa đơn đã thanh toán -->
                <div v-if="targetInvoice?.trangThai === 'Đã thanh toán'" class="alert alert-danger rounded-3 mb-3">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  Hóa đơn này đã được thanh toán hoàn tất. Không thể thêm khoản phụ thu!
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy">Hóa đơn mục tiêu</label>
                  <input
                    type="text"
                    class="form-control bg-light"
                    readonly
                    :value="`#HD-${String(targetInvoice?.maHoaDon || targetInvoice?.id || '').padStart(4, '0')} - Phòng: ${targetInvoice?.tenPhong || 'P101'} (${targetInvoice?.tenKhachHang || 'Khách'})`"
                  />
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-semibold text-navy required-label">Nội dung khoản thu</label>
                  <input
                    v-model.trim="adjustmentForm.noiDung"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': adjustmentErrors.noiDung }"
                    placeholder="Ví dụ: Phụ thu check-out trễ 2h, Đền bù ly vỡ..."
                    required
                  />
                  <div v-if="adjustmentErrors.noiDung" class="invalid-feedback">
                    {{ adjustmentErrors.noiDung[0] }}
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-6">
                    <label class="form-label small fw-semibold text-navy required-label">Số lượng</label>
                    <input
                      v-model.number="adjustmentForm.soLuong"
                      type="number"
                      min="1"
                      step="1"
                      class="form-control"
                      :class="{ 'is-invalid': adjustmentErrors.soLuong }"
                      required
                    />
                    <div v-if="adjustmentErrors.soLuong" class="invalid-feedback">
                      {{ adjustmentErrors.soLuong[0] }}
                    </div>
                  </div>

                  <div class="col-6">
                    <label class="form-label small fw-semibold text-navy required-label">Đơn giá (VNĐ)</label>
                    <input
                      v-model.number="adjustmentForm.donGia"
                      type="number"
                      min="0"
                      step="1000"
                      class="form-control"
                      :class="{ 'is-invalid': adjustmentErrors.donGia }"
                      required
                    />
                    <div v-if="adjustmentErrors.donGia" class="invalid-feedback">
                      {{ adjustmentErrors.donGia[0] }}
                    </div>
                  </div>
                </div>

                <!-- Tính tạm thành tiền -->
                <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                  <span class="small text-muted fw-medium">Thành tiền tạm tính:</span>
                  <span class="fs-6 fw-bold text-navy">
                    {{ formatCurrency((adjustmentForm.soLuong || 0) * (adjustmentForm.donGia || 0)) }}
                  </span>
                </div>
              </div>

              <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="showAdjustmentModal = false">
                  Hủy
                </button>
                <button
                  type="submit"
                  class="btn btn-navy rounded-3 px-4 fw-semibold"
                  :disabled="isSubmitting || targetInvoice?.trangThai === 'Đã thanh toán'"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                  Lưu Khoản Thu
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- MODAL 3: GHI NHẬN THANH TOÁN (TIỀN MẶT / QR / THẺ)      -->
      <!-- ======================================================= -->
      <div v-if="showPaymentModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55);" @click.self="showPaymentModal = false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-teal text-white py-3 px-4 border-0">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 fs-4 text-warning"></i>
                <div>
                  <h5 class="modal-title fw-bold mb-0">Xử Lý Thanh Toán Hóa Đơn</h5>
                  <small class="text-white-50">
                    Mã HĐ: #HD-{{ String(targetInvoice?.maHoaDon || targetInvoice?.id || '').padStart(4, '0') }}
                  </small>
                </div>
              </div>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" @click="showPaymentModal = false"></button>
            </div>

            <form @submit.prevent="submitPayment">
              <div class="modal-body p-4">
                <!-- Tóm tắt số tiền -->
                <div class="row g-3 mb-4">
                  <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 text-center">
                      <small class="text-muted d-block">Tổng hóa đơn</small>
                      <span class="fw-bold text-navy fs-6">{{ formatCurrency(targetInvoice?.tongTien || 0) }}</span>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="p-3 bg-light rounded-3 text-center">
                      <small class="text-muted d-block">Đã thanh toán</small>
                      <span class="fw-bold text-success fs-6">{{ formatCurrency(targetInvoice?.daThanhToan || 0) }}</span>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="p-3 bg-danger-subtle rounded-3 text-center border border-danger-subtle">
                      <small class="text-danger d-block fw-semibold">Còn lại cần thu</small>
                      <span class="fw-bold text-danger fs-5">{{ formatCurrency(targetInvoice?.conLai || 0) }}</span>
                    </div>
                  </div>
                </div>

                <!-- Chọn phương thức thanh toán -->
                <div class="mb-4">
                  <label class="form-label small fw-semibold text-navy d-block mb-2">
                    Chọn Phương Thức Thanh Toán
                  </label>
                  <div class="row g-2">
                    <div class="col-4">
                      <input
                        type="radio"
                        class="btn-check"
                        name="paymentMethod"
                        id="methodCash"
                        value="tien mat"
                        v-model="paymentForm.phuongThuc"
                      />
                      <label class="btn btn-outline-secondary w-100 py-2.5 rounded-3 d-flex flex-column align-items-center gap-1" for="methodCash">
                        <i class="bi bi-cash-stack fs-4 text-success"></i>
                        <span class="small fw-semibold">Tiền Mặt</span>
                      </label>
                    </div>
                    <div class="col-4">
                      <input
                        type="radio"
                        class="btn-check"
                        name="paymentMethod"
                        id="methodQR"
                        value="qr"
                        v-model="paymentForm.phuongThuc"
                      />
                      <label class="btn btn-outline-secondary w-100 py-2.5 rounded-3 d-flex flex-column align-items-center gap-1" for="methodQR">
                        <i class="bi bi-qr-code-scan fs-4 text-primary"></i>
                        <span class="small fw-semibold">Mã QR Động</span>
                      </label>
                    </div>
                    <div class="col-4">
                      <input
                        type="radio"
                        class="btn-check"
                        name="paymentMethod"
                        id="methodCard"
                        value="the"
                        v-model="paymentForm.phuongThuc"
                      />
                      <label class="btn btn-outline-secondary w-100 py-2.5 rounded-3 d-flex flex-column align-items-center gap-1" for="methodCard">
                        <i class="bi bi-credit-card-2-front fs-4 text-warning"></i>
                        <span class="small fw-semibold">Thẻ Ngân Hàng</span>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Khu vực Mã QR nếu chọn phương thức QR (BR04.3) -->
                <div v-if="paymentForm.phuongThuc === 'qr'" class="card border border-primary-subtle bg-light rounded-3 p-3 mb-4">
                  <div class="row align-items-center g-3">
                    <div class="col-sm-4 text-center">
                      <!-- Mô phỏng mã QR động tự điền số tiền và mã hóa đơn -->
                      <div class="bg-white p-2 rounded-3 border d-inline-block shadow-sm">
                        <img
                          :src="`https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=HOTEL_PAYMENT_HD${targetInvoice?.maHoaDon || targetInvoice?.id}_${paymentForm.soTien}`"
                          alt="Dynamic QR Code"
                          class="img-fluid"
                          style="width: 120px; height: 120px;"
                        />
                      </div>
                      <small class="d-block text-muted mt-1" style="font-size: 0.7rem;">Quét bằng app ngân hàng</small>
                    </div>
                    <div class="col-sm-8">
                      <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white">QR Động (BR04.3)</span>
                        <small class="text-muted">Tự động điền số tiền & nội dung</small>
                      </div>
                      <div class="small mb-1">
                        <span class="text-muted">Ngân hàng:</span> <strong>MB BANK (Ngân hàng Quân Đội)</strong>
                      </div>
                      <div class="small mb-1">
                        <span class="text-muted">Số tài khoản:</span> <strong>090123456789</strong>
                      </div>
                      <div class="small mb-1">
                        <span class="text-muted">Số tiền chuyển:</span> <strong class="text-danger">{{ formatCurrency(paymentForm.soTien) }}</strong>
                      </div>
                      <div class="small">
                        <span class="text-muted">Nội dung CK:</span> <strong class="text-navy">THANHTOAN HD{{ targetInvoice?.maHoaDon || targetInvoice?.id }}</strong>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Nhập Số tiền thanh toán -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-navy mb-0 required-label">
                      Số tiền thực nhận (VNĐ)
                    </label>
                    <button
                      type="button"
                      class="btn btn-link btn-sm p-0 text-decoration-none small text-teal"
                      @click="fillFullRemaining"
                    >
                      <i class="bi bi-arrow-right-circle me-1"></i>Thanh toán toàn bộ
                    </button>
                  </div>
                  <div class="input-group">
                    <span class="input-group-text bg-light text-muted">VNĐ</span>
                    <input
                      v-model.number="paymentForm.soTien"
                      type="number"
                      min="1"
                      :max="targetInvoice?.conLai"
                      class="form-control fw-bold fs-5 text-navy"
                      :class="{ 'is-invalid': paymentErrors.soTien }"
                      required
                    />
                    <div v-if="paymentErrors.soTien" class="invalid-feedback">
                      {{ paymentErrors.soTien[0] }}
                    </div>
                  </div>
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Tối đa có thể thu: {{ formatCurrency(targetInvoice?.conLai || 0) }}
                  </small>
                </div>

                <!-- Nhập Mã giao dịch ngân hàng / chứng từ (BR04.4 Idempotency) -->
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-navy mb-0 required-label">
                      Mã giao dịch / Mã tham chiếu
                    </label>
                    <button
                      type="button"
                      class="btn btn-link btn-sm p-0 text-decoration-none small text-navy"
                      @click="generateAutoTransactionCode"
                    >
                      <i class="bi bi-magic me-1"></i>Tự tạo mã
                    </button>
                  </div>
                  <input
                    v-model.trim="paymentForm.maGiaoDich"
                    type="text"
                    class="form-control font-monospace"
                    :class="{ 'is-invalid': paymentErrors.maGiaoDich }"
                    placeholder="Ví dụ: MB_20261122_9981 hoặc TXN_CASH_01"
                    required
                  />
                  <div v-if="paymentErrors.maGiaoDich" class="invalid-feedback">
                    {{ paymentErrors.maGiaoDich[0] }}
                  </div>
                  <small class="text-muted" style="font-size: 0.75rem;">
                    Mã giao dịch phải là duy nhất để đảm bảo tính toàn vẹn kế toán (Idempotency).
                  </small>
                </div>
              </div>

              <div class="modal-footer bg-light py-3 px-4 border-0 d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-3 px-3" @click="showPaymentModal = false">
                  Hủy Bỏ
                </button>
                <button
                  type="submit"
                  class="btn btn-teal text-white rounded-3 px-4 fw-semibold"
                  :disabled="isSubmitting || paymentForm.soTien <= 0 || paymentForm.soTien > (targetInvoice?.conLai || 0)"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
                  Xác Nhận Thu Tiền
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'

// 1. Quản lý trạng thái xác thực người dùng
const currentUser = reactive({
  id: null,
  tenDangNhap: '',
  hoTen: '',
  vaiTro: '',
})

// Kiểm tra quyền hạn (Lễ tân hoặc Quản lý)
const isAuthorized = computed(() => {
  const role = (currentUser.vaiTro || '').toLowerCase().trim()
  return role === 'lễ tân' || role === 'quản lý' || role === 'admin'
})

// Nạp thông tin người dùng từ localStorage
const loadCurrentUser = () => {
  try {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      const u = JSON.parse(userStr)
      currentUser.id = u.id || null
      currentUser.tenDangNhap = u.tenDangNhap || ''
      currentUser.hoTen = u.hoTen || ''
      currentUser.vaiTro = u.vaiTro || ''
    }
  } catch {
    // ignore
  }
}

// Chuyển đổi nhanh tài khoản phục vụ kiểm thử
const quickSwitchUser = async (username, password, roleLabel) => {
  try {
    isLoading.value = true
    const res = await axios.post('/api/v1/auth/login', {
      tenDangNhap: username,
      password: password,
    })

    if (res.data?.success && res.data?.data?.access_token) {
      localStorage.setItem('access_token', res.data.data.access_token)
      localStorage.setItem('user', JSON.stringify(res.data.data.user || {}))
      loadCurrentUser()
      showAlert(`Đã chuyển sang tài khoản ${roleLabel} (${username}) thành công!`, 'success')
      fetchInvoices()
    }
  } catch (err) {
    showAlert(`Không thể chuyển sang tài khoản ${username}: ${err.response?.data?.message || err.message}`, 'danger')
  } finally {
    isLoading.value = false
  }
}

// 2. State danh sách hóa đơn & bộ lọc
const invoices = ref([])
const isLoading = ref(false)

const filters = reactive({
  keyword: '',
  trangThai: '',
  ngayLapTu: '',
  ngayLapDen: '',
})

const pagination = reactive({
  currentPage: 1,
  lastPage: 1,
  total: 0,
  perPage: 15,
})

// Thống kê nhanh
const statistics = reactive({
  total: 0,
  pending: 0,
  paid: 0,
  totalCollected: 0,
})

// Thông báo Banner Alert
const alertMessage = ref('')
const alertType = ref('success')

const alertClass = computed(() => {
  switch (alertType.value) {
    case 'success':
      return 'alert-success bg-success-subtle text-success'
    case 'danger':
      return 'alert-danger bg-danger-subtle text-danger'
    case 'warning':
      return 'alert-warning bg-warning-subtle text-warning'
    default:
      return 'alert-info bg-info-subtle text-info'
  }
})

const alertIcon = computed(() => {
  switch (alertType.value) {
    case 'success':
      return 'bi bi-check-circle-fill'
    case 'danger':
      return 'bi bi-exclamation-triangle-fill'
    case 'warning':
      return 'bi bi-exclamation-circle-fill'
    default:
      return 'bi bi-info-circle-fill'
  }
})

const showAlert = (message, type = 'success') => {
  alertMessage.value = message
  alertType.value = type
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Helper lấy Auth Header
const getAuthHeaders = () => {
  const token = localStorage.getItem('access_token')
  return {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  }
}

// 3. API Fetch danh sách hóa đơn
const fetchInvoices = async () => {
  isLoading.value = true
  try {
    const params = {
      page: pagination.currentPage,
      per_page: pagination.perPage,
    }

    if (filters.keyword) params.keyword = filters.keyword
    if (filters.trangThai) params.trangThai = filters.trangThai
    if (filters.ngayLapTu) params.ngayLapTu = filters.ngayLapTu
    if (filters.ngayLapDen) params.ngayLapDen = filters.ngayLapDen

    const response = await axios.get('/api/v1/invoices', {
      ...getAuthHeaders(),
      params,
    })

    if (response.data?.success) {
      invoices.value = response.data.data || []

      // Cập nhật phân trang
      if (response.data.meta) {
        pagination.currentPage = response.data.meta.current_page || 1
        pagination.lastPage = response.data.meta.last_page || 1
        pagination.total = response.data.meta.total || invoices.value.length
      } else {
        pagination.total = invoices.value.length
      }

      calculateStatistics()
    }
  } catch (error) {
    console.error('Lỗi khi tải danh sách hóa đơn:', error)
    if (error.response?.status === 401) {
      showAlert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.', 'danger')
    } else if (error.response?.status === 403) {
      showAlert('Bạn không có quyền truy cập danh sách hóa đơn.', 'danger')
    } else {
      showAlert('Có lỗi xảy ra khi tải danh sách hóa đơn: ' + (error.response?.data?.message || error.message), 'danger')
    }
  } finally {
    isLoading.value = false
  }
}

// Tính toán thống kê nhanh
const calculateStatistics = () => {
  statistics.total = pagination.total || invoices.value.length
  statistics.pending = invoices.value.filter(i => i.trangThai === 'Chờ thanh toán').length
  statistics.paid = invoices.value.filter(i => i.trangThai === 'Đã thanh toán').length
  statistics.totalCollected = invoices.value.reduce((acc, curr) => acc + (Number(curr.daThanhToan) || 0), 0)
}

const handleSearch = () => {
  pagination.currentPage = 1
  fetchInvoices()
}

const resetFilters = () => {
  filters.keyword = ''
  filters.trangThai = ''
  filters.ngayLapTu = ''
  filters.ngayLapDen = ''
  pagination.currentPage = 1
  fetchInvoices()
}

const changePage = (page) => {
  if (page < 1 || page > pagination.lastPage) return
  pagination.currentPage = page
  fetchInvoices()
}

const displayedPages = computed(() => {
  const current = pagination.currentPage
  const last = pagination.lastPage
  const delta = 2
  const left = Math.max(1, current - delta)
  const right = Math.min(last, current + delta)
  const pages = []
  for (let i = left; i <= right; i++) {
    pages.push(i)
  }
  return pages
})

// 4. MODAL CHI TIẾT HÓA ĐƠN
const showDetailModal = ref(false)
const isDetailLoading = ref(false)
const selectedInvoice = ref(null)

const openDetailModal = async (invoiceId) => {
  showDetailModal.value = true
  isDetailLoading.value = true
  selectedInvoice.value = null

  try {
    const res = await axios.get(`/api/v1/invoices/${invoiceId}`, getAuthHeaders())
    if (res.data?.success) {
      selectedInvoice.value = res.data.data
    }
  } catch (err) {
    showAlert('Không thể tải chi tiết hóa đơn: ' + (err.response?.data?.message || err.message), 'danger')
    showDetailModal.value = false
  } finally {
    isDetailLoading.value = false
  }
}

const printCurrentInvoice = () => {
  window.print()
}

const printInvoiceDirectly = async (invoiceId) => {
  await openDetailModal(invoiceId)
  setTimeout(() => {
    window.print()
  }, 400)
}

// 5. MODAL THÊM PHỤ THU / ĐIỀU CHỈNH
const showAdjustmentModal = ref(false)
const targetInvoice = ref(null)
const isSubmitting = ref(false)
const adjustmentErrors = ref({})

const adjustmentForm = reactive({
  noiDung: '',
  soLuong: 1,
  donGia: 0,
})

const openAdjustmentModal = (invoice) => {
  targetInvoice.value = invoice
  adjustmentForm.noiDung = ''
  adjustmentForm.soLuong = 1
  adjustmentForm.donGia = 0
  adjustmentErrors.value = {}
  showAdjustmentModal.value = true
}

const openAdjustmentFromDetail = () => {
  if (selectedInvoice.value) {
    showDetailModal.value = false
    openAdjustmentModal(selectedInvoice.value)
  }
}

const submitAdjustment = async () => {
  if (!targetInvoice.value) return

  isSubmitting.value = true
  adjustmentErrors.value = {}

  try {
    const id = targetInvoice.value.maHoaDon || targetInvoice.value.id
    const payload = {
      noiDung: adjustmentForm.noiDung,
      soLuong: adjustmentForm.soLuong,
      donGia: adjustmentForm.donGia,
    }

    const res = await axios.post(`/api/v1/invoices/${id}/adjustments`, payload, getAuthHeaders())

    if (res.data?.success) {
      showAlert('Thêm khoản điều chỉnh / phụ thu vào hóa đơn thành công!', 'success')
      showAdjustmentModal.value = false
      fetchInvoices()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      adjustmentErrors.value = err.response.data?.errors || {}
      showAlert('Dữ liệu nhập chưa hợp lệ, vui lòng kiểm tra lại.', 'danger')
    } else if (err.response?.status === 409) {
      showAlert('Hóa đơn đã được thanh toán hoàn tất, không thể chỉnh sửa.', 'danger')
      showAdjustmentModal.value = false
    } else {
      showAlert('Có lỗi khi thêm khoản phụ thu: ' + (err.response?.data?.message || err.message), 'danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// 6. MODAL GHI NHẬN THANH TOÁN
const showPaymentModal = ref(false)
const paymentErrors = ref({})

const paymentForm = reactive({
  phuongThuc: 'tien mat',
  soTien: 0,
  maGiaoDich: '',
})

const openPaymentModal = (invoice) => {
  targetInvoice.value = invoice
  paymentForm.phuongThuc = 'tien mat'
  paymentForm.soTien = invoice.conLai || 0
  paymentErrors.value = {}
  generateAutoTransactionCode()
  showPaymentModal.value = true
}

const openPaymentFromDetail = () => {
  if (selectedInvoice.value) {
    showDetailModal.value = false
    openPaymentModal(selectedInvoice.value)
  }
}

const fillFullRemaining = () => {
  if (targetInvoice.value) {
    paymentForm.soTien = targetInvoice.value.conLai || 0
  }
}

const generateAutoTransactionCode = () => {
  const now = new Date()
  const yyyy = now.getFullYear()
  const mm = String(now.getMonth() + 1).padStart(2, '0')
  const dd = String(now.getDate()).padStart(2, '0')
  const hh = String(now.getHours()).padStart(2, '0')
  const min = String(now.getMinutes()).padStart(2, '0')
  const sec = String(now.getSeconds()).padStart(2, '0')
  const randomSuffix = Math.floor(1000 + Math.random() * 9000)

  let prefix = 'CASH'
  if (paymentForm.phuongThuc === 'qr') prefix = 'QR'
  if (paymentForm.phuongThuc === 'the') prefix = 'CARD'

  paymentForm.maGiaoDich = `TXN_${prefix}_${yyyy}${mm}${dd}_${hh}${min}${sec}_${randomSuffix}`
}

const submitPayment = async () => {
  if (!targetInvoice.value) return

  isSubmitting.value = true
  paymentErrors.value = {}

  try {
    const id = targetInvoice.value.maHoaDon || targetInvoice.value.id
    const payload = {
      maHoaDon: id,
      soTien: paymentForm.soTien,
      phuongThuc: paymentForm.phuongThuc,
      maGiaoDich: paymentForm.maGiaoDich,
    }

    const res = await axios.post('/api/v1/payments', payload, getAuthHeaders())

    if (res.data?.success) {
      showAlert(`Ghi nhận thanh toán ${formatCurrency(paymentForm.soTien)} cho hóa đơn #HD-${String(id).padStart(4, '0')} thành công!`, 'success')
      showPaymentModal.value = false
      fetchInvoices()
    }
  } catch (err) {
    if (err.response?.status === 422) {
      paymentErrors.value = err.response.data?.errors || {}
      showAlert(err.response.data?.message || 'Số tiền thanh toán hoặc dữ liệu không hợp lệ.', 'danger')
    } else if (err.response?.status === 409) {
      showAlert('Xung đột: ' + (err.response.data?.message || 'Hóa đơn đã thanh toán hoặc mã giao dịch bị trùng.'), 'danger')
    } else {
      showAlert('Lỗi khi ghi nhận thanh toán: ' + (err.response?.data?.message || err.message), 'danger')
    }
  } finally {
    isSubmitting.value = false
  }
}

// 7. Formatters
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) return '0 ₫'
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount)
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    if (isNaN(d.getTime())) return dateStr
    return d.toLocaleString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit',
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

onMounted(() => {
  loadCurrentUser()
  fetchInvoices()
})
</script>

<style scoped>
/* Design Tokens: Navy #1E3A5F, Teal #0F766E, Background #F8FAFC, Font Inter */
.invoice-management-view {
  background-color: #f8fafc;
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
}

.text-navy {
  color: #1e3a5f !important;
}

.bg-navy {
  background-color: #1e3a5f !important;
}

.bg-navy-subtle {
  background-color: rgba(30, 58, 95, 0.1) !important;
}

.btn-navy {
  background-color: #1e3a5f;
  color: #ffffff;
  border: 1px solid #1e3a5f;
}

.btn-navy:hover {
  background-color: #162c47;
  color: #ffffff;
}

.btn-outline-navy {
  color: #1e3a5f;
  border-color: #1e3a5f;
  background-color: transparent;
}

.btn-outline-navy:hover {
  background-color: #1e3a5f;
  color: #ffffff;
}

.text-teal {
  color: #0f766e !important;
}

.bg-teal {
  background-color: #0f766e !important;
}

.bg-teal-subtle {
  background-color: rgba(15, 118, 110, 0.1) !important;
}

.btn-teal {
  background-color: #0f766e;
  border-color: #0f766e;
  color: #ffffff;
}

.btn-teal:hover {
  background-color: #0d5f58;
  color: #ffffff;
}

.header-icon-box {
  width: 52px;
  height: 52px;
}

.kpi-icon-box {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.custom-invoice-table thead th {
  border-top: none;
  font-size: 0.78rem;
}

.custom-invoice-table tbody tr {
  transition: background-color 0.15s ease-in-out;
}

.custom-invoice-table tbody tr:hover {
  background-color: #f1f5f9;
}

.required-label::after {
  content: ' *';
  color: #dc3545;
}

.spin-animation {
  animation: spin 1s infinite linear;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Print CSS */
@media print {
  body * {
    visibility: hidden;
  }
  #printable-invoice-area,
  #printable-invoice-area * {
    visibility: visible;
  }
  #printable-invoice-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    padding: 20px;
    background: #ffffff !important;
  }
  .d-print-none {
    display: none !important;
  }
}
</style>
