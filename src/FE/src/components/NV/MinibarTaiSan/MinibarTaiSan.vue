<template>
  <div class="minibar-page py-4">
    <div class="container-fluid px-3 px-lg-5">
      <!-- TOAST THÔNG BÁO TỔNG THỂ -->
      <div
        v-if="toast.show"
        class="alert alert-dismissible fade show position-fixed top-0 end-0 m-4 shadow-lg animate-fade-in"
        :class="toast.type === 'success' ? 'alert-success border-success' : 'alert-danger border-danger'"
        style="z-index: 9999; max-width: 440px;"
        role="alert"
      >
        <div class="d-flex align-items-start gap-2">
          <i
            class="fs-5 flex-shrink-0 mt-0.5"
            :class="toast.type === 'success' ? 'bi bi-check-circle-fill text-success' : 'bi bi-exclamation-triangle-fill text-danger'"
          ></i>
          <div>
            <strong>{{ toast.type === 'success' ? 'Thành công' : 'Thông báo hệ thống' }}</strong>
            <div class="small mt-0.5">{{ toast.message }}</div>
          </div>
        </div>
        <button type="button" class="btn-close" @click="toast.show = false"></button>
      </div>

      <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG: QUAY LẠI, BREADCRUMB, THỜI GIAN, USER PROFILE -->
      <div class="top-nav-bar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 p-3 bg-white rounded-4 border shadow-xs">
        <!-- Bên trái: Nút quay lại & Breadcrumb -->
        <div class="d-flex align-items-center gap-3">
          <button
            class="btn btn-outline-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1.5 shadow-xs"
            @click="quayLai"
            title="Quay lại"
          >
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
          </button>

          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
              <li class="breadcrumb-item text-muted">Khách sạn</li>
              <li class="breadcrumb-item active text-teal fw-bold" aria-current="page">Minibar & tài sản phòng</li>
            </ol>
          </nav>
        </div>

        <!-- Bên phải: Ngày tháng thực tế, Vai trò nhân viên & Profile -->
        <div class="d-flex align-items-center gap-3">
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-calendar3 me-1 text-teal"></i>
            {{ ngayHienTai }}
          </span>

          <!-- Chọn nhanh vai trò kiểm thử -->
          <div class="dropdown">
            <button
              class="btn btn-sm btn-light border rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2 dropdown-toggle"
              type="button"
              data-bs-toggle="dropdown"
            >
              <i class="bi bi-person-gear text-teal"></i>
              <span class="small fw-semibold">{{ currentRole.ten }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
              <li v-for="r in roles" :key="r.id">
                <a class="dropdown-item small py-2" href="#" @click.prevent="chonRole(r)">
                  <i class="bi me-2" :class="r.icon"></i> {{ r.ten }}
                </a>
              </li>
            </ul>
          </div>

          <!-- User Badge -->
          <div class="d-flex align-items-center gap-2 ps-2 border-start">
            <div class="user-avatar-circle">TA</div>
            <div class="d-none d-sm-block text-start lh-sm">
              <div class="fw-bold small text-dark">Trần An</div>
              <div class="text-muted" style="font-size: 0.72rem;">Admin</div>
            </div>
          </div>
        </div>
      </div>

      <!-- TIÊU ĐỀ TRANG & NÚT THAO TÁC CHÍNH -->
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          <h2 class="h3 fw-bold text-dark mb-1">Minibar & tài sản phòng</h2>
          <p class="text-muted mb-0 small">
            Kiểm kê minibar, thiết lập định mức tiêu chuẩn và theo dõi tài sản buồng phòng gắn liền với từng lượt lưu trú.
          </p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Nút Thêm tài sản phòng -->
          <button
            class="btn btn-outline-teal d-inline-flex align-items-center gap-2 rounded-3 px-3 py-2 fw-medium shadow-xs"
            @click="moModalThemTaiSan"
          >
            <i class="bi bi-shield-plus"></i>
            <span>Thêm tài sản</span>
          </button>

          <!-- Nút Nhập kho / Bổ sung màu Teal đặc trưng -->
          <button
            class="btn btn-teal-primary d-inline-flex align-items-center gap-2 rounded-3 px-3.5 py-2 fw-semibold text-white shadow-xs"
            @click="moModalNhapKho(null)"
          >
            <i class="bi bi-box-arrow-in-down"></i>
            <span>Nhập kho / Bổ sung</span>
          </button>
        </div>
      </div>

      <!-- TAB NAVIGATION: 3 TAB CHÍNH -->
      <div class="bg-white rounded-4 border p-2 mb-4 shadow-xs">
        <ul class="nav nav-pills custom-tab-pills gap-2">
          <li class="nav-item">
            <button
              class="nav-link px-4 py-2 rounded-3 fw-medium"
              :class="{ 'active': activeTab === 'minibar' }"
              @click="activeTab = 'minibar'"
            >
              <i class="bi bi-cup-straw me-2"></i>
              <span>Minibar & tồn kho</span>
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link px-4 py-2 rounded-3 fw-medium"
              :class="{ 'active': activeTab === 'tai-san' }"
              @click="activeTab = 'tai-san'"
            >
              <i class="bi bi-shield-shaded me-2"></i>
              <span>Tài sản theo phòng</span>
            </button>
          </li>

          <li class="nav-item">
            <button
              class="nav-link px-4 py-2 rounded-3 fw-medium"
              :class="{ 'active': activeTab === 'nhat-ky' }"
              @click="activeTab = 'nhat-ky'"
            >
              <i class="bi bi-journal-text me-2"></i>
              <span>Nhật ký kiểm kê</span>
            </button>
          </li>
        </ul>
      </div>

      <!-- =================================================================== -->
      <!-- TAB 1: MINIBAR & TỒN KHO                                             -->
      <!-- =================================================================== -->
      <div v-if="activeTab === 'minibar'" class="tab-content-area">
        <!-- 4 THẺ THỐNG KÊ TỔNG KHO -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Tổng mặt hàng Minibar</span>
                <div class="stat-icon-box bg-teal-subtle text-teal">
                  <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-dark mb-0">{{ thongKeMinibar.tongSanPham || 0 }}</div>
              <div class="extra-small text-muted mt-1">Được định danh trong khách sạn</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Tồn kho tổng hiện tại</span>
                <div class="stat-icon-box bg-primary-subtle text-primary">
                  <i class="bi bi-boxes"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-dark mb-0">{{ thongKeMinibar.tongTonKho || 0 }}</div>
              <div class="extra-small text-muted mt-1">Tổng sản phẩm trong kho trung tâm</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Cảnh báo sắp hết hàng</span>
                <div class="stat-icon-box bg-danger-subtle text-danger">
                  <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
              </div>
              <div class="h3 fw-bold mb-0" :class="thongKeMinibar.sapHetHang > 0 ? 'text-danger' : 'text-success'">
                {{ thongKeMinibar.sapHetHang || 0 }}
              </div>
              <div class="extra-small text-muted mt-1">Dưới ngưỡng tồn kho an toàn</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Giá trị tồn kho</span>
                <div class="stat-icon-box bg-success-subtle text-success">
                  <i class="bi bi-cash-coin"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-teal mb-0">{{ thongKeMinibar.tongGiaTriTonDinhDang || '0 đ' }}</div>
              <div class="extra-small text-muted mt-1">Quy đổi theo đơn giá niêm yết</div>
            </div>
          </div>
        </div>

        <!-- BẢNG 1: SẢN PHẨM & TỒN KHO TỔNG -->
        <div class="card border-0 shadow-xs rounded-4 bg-white mb-4 overflow-hidden">
          <div class="card-header bg-white border-bottom p-3 p-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-archive-fill text-teal"></i>
                <span>Danh mục sản phẩm Minibar & Tồn kho tổng</span>
              </h5>
              <div class="small text-muted mt-0.5">Kho trung tâm cung ứng đồ uống và đồ dùng phòng</div>
            </div>

            <!-- Tìm kiếm & Lọc -->
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="input-group input-group-sm" style="width: 240px;">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input
                  v-model="filterMinibar.q"
                  type="text"
                  class="form-control bg-light border-start-0"
                  placeholder="Tìm theo mã hoặc tên..."
                  @input="taiDanhSachMinibar"
                />
              </div>

              <select v-model="filterMinibar.loaiSanPham" class="form-select form-select-sm" style="width: 140px;" @change="taiDanhSachMinibar">
                <option value="Tất cả">Mọi loại SP</option>
                <option value="Tinh_Phi">Tính phí</option>
                <option value="Mien_Phi">Miễn phí</option>
              </select>

              <select v-model="filterMinibar.trangThai" class="form-select form-select-sm" style="width: 140px;" @change="taiDanhSachMinibar">
                <option value="Tất cả">Mọi tồn kho</option>
                <option value="Con_Hang">Còn hàng</option>
                <option value="Sap_Het">Sắp hết hàng</option>
                <option value="Het_Hang">Hết hàng</option>
              </select>
            </div>
          </div>

          <!-- Bảng danh sách sản phẩm kho -->
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-main-table">
              <thead class="table-light">
                <tr class="small text-secondary text-uppercase">
                  <th class="ps-4 py-3">Mã & Tên sản phẩm</th>
                  <th class="py-3">Phân loại</th>
                  <th class="py-3 text-end">Giá bán</th>
                  <th class="py-3 text-center">Tồn đầu</th>
                  <th class="py-3 text-center">Nhập</th>
                  <th class="py-3 text-center">Xuất</th>
                  <th class="py-3 text-center">Tồn hiện tại</th>
                  <th class="py-3 text-center">Định mức/phòng</th>
                  <th class="py-3 text-center">Trạng thái</th>
                  <th class="pe-4 py-3 text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingMinibar">
                  <td colspan="10" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải danh mục sản phẩm minibar...</div>
                  </td>
                </tr>

                <tr v-else-if="danhSachSanPham.length === 0">
                  <td colspan="10" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    Không có sản phẩm nào phù hợp với điều kiện tìm kiếm.
                  </td>
                </tr>

                <tr v-for="sp in danhSachSanPham" :key="sp.id">
                  <!-- Mã & Tên -->
                  <td class="ps-4 py-3">
                    <div class="fw-bold text-dark">{{ sp.tenSanPham }}</div>
                    <div class="small text-muted">{{ sp.maSanPham }} &middot; ĐVT: {{ sp.donViTinh }}</div>
                  </td>

                  <!-- Phân loại -->
                  <td>
                    <span class="badge" :class="sp.loaiSanPham === 'Mien_Phi' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle'">
                      {{ sp.loaiSanPhamText }}
                    </span>
                  </td>

                  <!-- Giá bán -->
                  <td class="text-end fw-semibold text-teal">
                    {{ sp.donGiaDinhDang }}
                  </td>

                  <!-- Tồn đầu, Nhập, Xuất -->
                  <td class="text-center text-muted small">{{ sp.tonDau }}</td>
                  <td class="text-center text-success small">+{{ sp.soLuongNhap }}</td>
                  <td class="text-center text-danger small">-{{ sp.soLuongXuat }}</td>

                  <!-- Tồn hiện tại -->
                  <td class="text-center">
                    <span class="fw-bold fs-6" :class="sp.canhBaoTonKho ? 'text-danger' : 'text-dark'">
                      {{ sp.tonHienTai }}
                    </span>
                    <span class="text-muted extra-small ms-1">{{ sp.donViTinh }}</span>
                    <div v-if="sp.canhBaoTonKho" class="extra-small text-danger fw-semibold">
                      <i class="bi bi-exclamation-triangle"></i> &le; {{ sp.nguongToiThieu }}
                    </div>
                  </td>

                  <!-- Định mức tiêu chuẩn -->
                  <td class="text-center small text-secondary">
                    <span class="badge bg-light text-dark border">{{ sp.dinhMucTieuChuan }} {{ sp.donViTinh }}</span>
                  </td>

                  <!-- Trạng thái -->
                  <td class="text-center">
                    <span
                      class="status-dot-pill"
                      :class="{
                        'pill-active': sp.trangThai === 'Con_Hang',
                        'pill-paused': sp.trangThai === 'Sap_Het',
                        'pill-expired': sp.trangThai === 'Het_Hang'
                      }"
                    >
                      <span class="dot"></span>
                      <span>{{ sp.trangThaiText }}</span>
                    </span>
                  </td>

                  <!-- Thao tác -->
                  <td class="pe-4 py-3 text-end">
                    <button
                      class="btn btn-sm btn-outline-teal d-inline-flex align-items-center gap-1 rounded-pill px-3"
                      @click="moModalNhapKho(sp.id)"
                    >
                      <i class="bi bi-plus-lg"></i>
                      <span>Nhập kho</span>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- BẢNG 2: KIỂM KÊ & BỔ SUNG THEO TỪNG PHÒNG THỰC TẾ -->
        <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom p-3 p-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-door-closed-fill text-teal"></i>
                <span>Minibar theo phòng lưu trú thực tế</span>
              </h5>
              <div class="small text-muted mt-0.5">Kiểm kê trước Check-in, bổ sung trong ca và đối soát khi Check-out</div>
            </div>

            <!-- Lọc phòng -->
            <div class="d-flex align-items-center gap-2">
              <select v-model="filterPhong.tang" class="form-select form-select-sm" style="width: 130px;" @change="taiDanhSachPhong">
                <option value="Tất cả">Mọi tầng</option>
                <option value="1">Tầng 1</option>
                <option value="2">Tầng 2</option>
                <option value="3">Tầng 3</option>
                <option value="4">Tầng 4</option>
              </select>

              <select v-model="filterPhong.trangThai" class="form-select form-select-sm" style="width: 160px;" @change="taiDanhSachPhong">
                <option value="Tất cả">Mọi trạng thái phòng</option>
                <option value="San_Sang">Sẵn sàng đón khách</option>
                <option value="Co_Khach">Đang có khách</option>
                <option value="Dang_Don">Đang dọn phòng</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-main-table">
              <thead class="table-light">
                <tr class="small text-secondary text-uppercase">
                  <th class="ps-4 py-3">Phòng / Loại phòng</th>
                  <th class="py-3">Trạng thái phòng</th>
                  <th class="py-3">Khách đang lưu trú</th>
                  <th class="py-3 text-center">Trạng thái Minibar</th>
                  <th class="py-3 text-center">Sản phẩm phòng</th>
                  <th class="pe-4 py-3 text-end">Nghiệp vụ buồng phòng</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingPhong">
                  <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải danh sách phòng...</div>
                  </td>
                </tr>

                <tr v-else-if="danhSachPhong.length === 0">
                  <td colspan="6" class="text-center py-5 text-muted">
                    Không tìm thấy phòng nào.
                  </td>
                </tr>

                <tr v-for="p in danhSachPhong" :key="p.id">
                  <!-- Phòng -->
                  <td class="ps-4 py-3">
                    <div class="fw-bold text-dark fs-6">Phòng {{ p.tenPhong }}</div>
                    <div class="small text-muted">Tầng {{ p.tang }} &middot; {{ p.tenLoaiPhong }}</div>
                  </td>

                  <!-- Trạng thái phòng -->
                  <td>
                    <span
                      class="badge"
                      :class="{
                        'bg-success-subtle text-success border border-success-subtle': p.trangThaiPhong === 'San_Sang',
                        'bg-primary-subtle text-primary border border-primary-subtle': p.trangThaiPhong === 'Co_Khach',
                        'bg-warning-subtle text-warning border border-warning-subtle': p.trangThaiPhong === 'Dang_Don'
                      }"
                    >
                      {{ p.trangThaiPhong === 'San_Sang' ? 'Sẵn sàng' : (p.trangThaiPhong === 'Co_Khach' ? 'Đang ở' : 'Đang dọn') }}
                    </span>
                  </td>

                  <!-- Khách đang lưu trú -->
                  <td>
                    <div v-if="p.activeStay">
                      <div class="fw-semibold text-dark">{{ p.activeStay.tenKhach }}</div>
                      <div class="extra-small text-muted">
                        <i class="bi bi-telephone me-1"></i>{{ p.activeStay.soDienThoai }}
                      </div>
                    </div>
                    <div v-else class="text-muted small fst-italic">Phòng trống</div>
                  </td>

                  <!-- Trạng thái minibar -->
                  <td class="text-center">
                    <span class="badge bg-light text-dark border px-2.5 py-1.5">
                      <i class="bi bi-check2-circle text-teal me-1"></i>
                      {{ p.minibarStatus }}
                    </span>
                  </td>

                  <!-- Số sản phẩm -->
                  <td class="text-center small">
                    <span class="fw-bold text-teal">{{ p.soLuongSanPhamMinibar || 5 }}</span> mặt hàng
                  </td>

                  <!-- Thao tác nghiệp vụ theo luồng chuẩn -->
                  <td class="pe-4 py-3 text-end">
                    <div class="btn-group btn-group-sm">
                      <!-- 1. Chuẩn bị trước Check-in -->
                      <button
                        class="btn btn-outline-teal"
                        title="Chuẩn bị & Kiểm tra Minibar trước khi khách đến"
                        @click="moModalChuanBi(p.id)"
                      >
                        <i class="bi bi-shield-check me-1"></i> Chuẩn bị đón khách
                      </button>

                      <!-- 2. Bổ sung trong ca -->
                      <button
                        class="btn btn-outline-primary"
                        title="Bổ sung Minibar vào phòng theo yêu cầu hoặc ca trực"
                        @click="moModalBoSung(p.id, p.tenPhong)"
                      >
                        <i class="bi bi-cart-plus me-1"></i> Bổ sung
                      </button>

                      <!-- 3. Kiểm kê trả phòng -->
                      <button
                        class="btn btn-outline-danger"
                        title="Kiểm kê Minibar khi khách Check-out"
                        @click="moModalCheckOut(p.id)"
                      >
                        <i class="bi bi-clipboard2-check me-1"></i> Kiểm kê Check-out
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- =================================================================== -->
      <!-- TAB 2: TÀI SẢN THEO PHÒNG                                            -->
      <!-- =================================================================== -->
      <div v-if="activeTab === 'tai-san'" class="tab-content-area">
        <!-- 4 THẺ THỐNG KÊ TÀI SẢN -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Tổng trang thiết bị</span>
                <div class="stat-icon-box bg-teal-subtle text-teal">
                  <i class="bi bi-shield-shaded"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-dark mb-0">{{ thongKeTaiSan.tongTaiSan || 0 }}</div>
              <div class="extra-small text-muted mt-1">Được trang bị trong các phòng</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Bình thường / Hoạt động tốt</span>
                <div class="stat-icon-box bg-success-subtle text-success">
                  <i class="bi bi-check-circle-fill"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-success mb-0">{{ thongKeTaiSan.binhThuong || 0 }}</div>
              <div class="extra-small text-muted mt-1">Sẵn sàng phục vụ khách</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Hư hỏng thiết bị</span>
                <div class="stat-icon-box bg-danger-subtle text-danger">
                  <i class="bi bi-wrench"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-danger mb-0">{{ thongKeTaiSan.huHong || 0 }}</div>
              <div class="extra-small text-muted mt-1">Cần sửa chữa hoặc bảo trì</div>
            </div>
          </div>

          <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-medium">Mất mát / Thất lạc</span>
                <div class="stat-icon-box bg-warning-subtle text-warning">
                  <i class="bi bi-question-diamond-fill"></i>
                </div>
              </div>
              <div class="h3 fw-bold text-warning mb-0">{{ thongKeTaiSan.matMat || 0 }}</div>
              <div class="extra-small text-muted mt-1">Đang xác minh hoặc thay thế</div>
            </div>
          </div>
        </div>

        <!-- BẢNG DANH SÁCH TÀI SẢN PHÒNG -->
        <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom p-3 p-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-tv text-teal"></i>
                <span>Trang thiết bị & Tài sản buồng phòng</span>
              </h5>
              <div class="small text-muted mt-0.5">Theo dõi chi tiết số lượng, tình trạng và lịch sử sự cố từng phòng</div>
            </div>

            <!-- Tìm kiếm & Lọc -->
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="input-group input-group-sm" style="width: 240px;">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input
                  v-model="filterTaiSan.q"
                  type="text"
                  class="form-control bg-light border-start-0"
                  placeholder="Tìm tài sản, phòng..."
                  @input="taiDanhSachTaiSan"
                />
              </div>

              <select v-model="filterTaiSan.maPhong" class="form-select form-select-sm" style="width: 140px;" @change="taiDanhSachTaiSan">
                <option value="Tất cả">Mọi phòng</option>
                <option v-for="p in danhSachPhong" :key="p.id" :value="p.id">Phòng {{ p.tenPhong }}</option>
              </select>

              <select v-model="filterTaiSan.tinhTrang" class="form-select form-select-sm" style="width: 150px;" @change="taiDanhSachTaiSan">
                <option value="Tất cả">Mọi tình trạng</option>
                <option value="Bình thường">Bình thường</option>
                <option value="Hư hỏng">Hư hỏng</option>
                <option value="Mất mát">Mất mát</option>
                <option value="Đang sửa chữa">Đang sửa chữa</option>
              </select>

              <!-- Nút Báo hỏng sự cố -->
              <button
                class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 rounded-3 px-3"
                @click="moModalBaoHong(null)"
              >
                <i class="bi bi-exclamation-triangle"></i>
                <span>Báo hỏng / Mất</span>
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-main-table">
              <thead class="table-light">
                <tr class="small text-secondary text-uppercase">
                  <th class="ps-4 py-3">Mã & Tên tài sản</th>
                  <th class="py-3">Phòng trang bị</th>
                  <th class="py-3 text-center">Số lượng</th>
                  <th class="py-3 text-end">Giá trị ước tính</th>
                  <th class="py-3 text-center">Tình trạng</th>
                  <th class="py-3">Ghi chú & Lịch sử</th>
                  <th class="pe-4 py-3 text-end">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingTaiSan">
                  <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải danh sách tài sản...</div>
                  </td>
                </tr>

                <tr v-else-if="danhSachTaiSan.length === 0">
                  <td colspan="7" class="text-center py-5 text-muted">
                    Không tìm thấy trang thiết bị nào phù hợp.
                  </td>
                </tr>

                <tr v-for="ts in danhSachTaiSan" :key="ts.id">
                  <!-- Mã & Tên -->
                  <td class="ps-4 py-3">
                    <div class="fw-bold text-dark">{{ ts.tenTaiSan }}</div>
                    <div class="small text-muted">{{ ts.maTaiSan }}</div>
                  </td>

                  <!-- Phòng -->
                  <td>
                    <span class="badge bg-light text-dark border px-2.5 py-1">
                      Phòng {{ ts.tenPhong }}
                    </span>
                  </td>

                  <!-- Số lượng -->
                  <td class="text-center fw-semibold text-secondary">{{ ts.soLuong }}</td>

                  <!-- Giá trị -->
                  <td class="text-end fw-semibold text-dark">{{ ts.giaTriDinhDang }}</td>

                  <!-- Tình trạng -->
                  <td class="text-center">
                    <span
                      class="badge"
                      :class="{
                        'bg-success-subtle text-success border border-success-subtle': ts.tinhTrang === 'Bình thường' || ts.tinhTrang === 'Tốt',
                        'bg-danger-subtle text-danger border border-danger-subtle': ts.tinhTrang === 'Hư hỏng' || ts.tinhTrang === 'Mất mát',
                        'bg-warning-subtle text-warning border border-warning-subtle': ts.tinhTrang === 'Đang sửa chữa'
                      }"
                    >
                      {{ ts.tinhTrang }}
                    </span>
                  </td>

                  <!-- Ghi chú -->
                  <td class="small text-muted" style="max-width: 250px;">
                    <div class="text-truncate">{{ ts.ghiChu || 'Hoạt động ổn định' }}</div>
                  </td>

                  <!-- Thao tác -->
                  <td class="pe-4 py-3 text-end">
                    <button
                      class="btn btn-sm btn-outline-danger rounded-pill px-3"
                      @click="moModalBaoHong(ts.id)"
                      title="Báo cáo hỏng hoặc mất thiết bị này"
                    >
                      <i class="bi bi-exclamation-octagon me-1"></i> Báo sự cố
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- =================================================================== -->
      <!-- TAB 3: NHẬT KÝ KIỂM KÊ                                               -->
      <!-- =================================================================== -->
      <div v-if="activeTab === 'nhat-ky'" class="tab-content-area">
        <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
          <div class="card-header bg-white border-bottom p-3 p-lg-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-teal"></i>
                <span>Nhật ký & Lịch sử kiểm kê buồng phòng</span>
              </h5>
              <div class="small text-muted mt-0.5">Lưu vết đầy đủ các đợt kiểm tra, nhập kho, bổ sung và đối soát trả phòng</div>
            </div>

            <!-- Tìm kiếm & Lọc -->
            <div class="d-flex flex-wrap align-items-center gap-2">
              <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text bg-light border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input
                  v-model="filterNhatKy.q"
                  type="text"
                  class="form-control bg-light border-start-0"
                  placeholder="Tìm phiếu, phòng, NV..."
                  @input="taiDanhSachNhatKy"
                />
              </div>

              <select v-model="filterNhatKy.loaiKiemKe" class="form-select form-select-sm" style="width: 170px;" @change="taiDanhSachNhatKy">
                <option value="Tất cả">Mọi loại nghiệp vụ</option>
                <option value="Chuan_Bi_CheckIn">Chuẩn bị Check-in</option>
                <option value="Bo_Sung_Minibar">Bổ sung Minibar</option>
                <option value="Kiem_Ke_CheckOut">Kiểm kê Check-out</option>
                <option value="Bao_Hong_Mat">Báo hỏng / mất tài sản</option>
                <option value="Nhap_Kho">Nhập kho tổng</option>
              </select>

              <select v-model="filterNhatKy.trangThai" class="form-select form-select-sm" style="width: 150px;" @change="taiDanhSachNhatKy">
                <option value="Tất cả">Mọi trạng thái</option>
                <option value="Da_Xac_Nhan">Đã xác nhận</option>
                <option value="Co_Chenh_Lech">Có chênh lệch</option>
                <option value="Cho_Xac_Minh">Chờ xác minh</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-main-table">
              <thead class="table-light">
                <tr class="small text-secondary text-uppercase">
                  <th class="ps-4 py-3">Mã phiếu</th>
                  <th class="py-3">Loại nghiệp vụ</th>
                  <th class="py-3">Phòng</th>
                  <th class="py-3">Thời gian</th>
                  <th class="py-3">Nhân viên thực hiện</th>
                  <th class="py-3 text-center">Trạng thái</th>
                  <th class="py-3 text-end">Tiền phát sinh</th>
                  <th class="pe-4 py-3 text-end">Chi tiết</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingNhatKy">
                  <td colspan="8" class="text-center py-5">
                    <div class="spinner-border text-teal" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải nhật ký kiểm kê...</div>
                  </td>
                </tr>

                <tr v-else-if="danhSachNhatKy.length === 0">
                  <td colspan="8" class="text-center py-5 text-muted">
                    Không tìm thấy phiếu kiểm kê nào.
                  </td>
                </tr>

                <tr v-for="pk in danhSachNhatKy" :key="pk.id">
                  <!-- Mã phiếu -->
                  <td class="ps-4 py-3">
                    <span class="fw-bold text-dark">{{ pk.maPhieu }}</span>
                  </td>

                  <!-- Loại nghiệp vụ -->
                  <td>
                    <span class="badge" :class="pk.badgeLoai">
                      {{ pk.loaiKiemKeText }}
                    </span>
                  </td>

                  <!-- Phòng -->
                  <td>
                    <span v-if="pk.tenPhong" class="fw-semibold text-secondary">
                      Phòng {{ pk.tenPhong }}
                    </span>
                    <span v-else class="text-muted small">Kho trung tâm</span>
                  </td>

                  <!-- Thời gian -->
                  <td class="small text-muted">
                    <i class="bi bi-clock me-1"></i>{{ pk.thoiGianFormatted }}
                  </td>

                  <!-- Nhân viên -->
                  <td class="small fw-semibold text-teal">
                    {{ pk.tenNhanVien }}
                  </td>

                  <!-- Trạng thái -->
                  <td class="text-center">
                    <span
                      class="badge"
                      :class="pk.trangThai === 'Co_Chenh_Lech' ? 'bg-warning text-dark' : 'bg-success-subtle text-success border border-success-subtle'"
                    >
                      {{ pk.trangThaiText }}
                    </span>
                  </td>

                  <!-- Tiền phát sinh -->
                  <td class="text-end fw-bold">
                    <span :class="pk.tongTienPhatSinh > 0 ? 'text-danger' : 'text-muted small'">
                      {{ pk.tongTienDinhDang }}
                    </span>
                  </td>

                  <!-- Chi tiết -->
                  <td class="pe-4 py-3 text-end">
                    <button
                      class="btn btn-sm btn-outline-teal rounded-pill px-3"
                      @click="xemChiTietPhieu(pk)"
                    >
                      <i class="bi bi-eye me-1"></i> Xem
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- DIALOG MODALS                                                       -->
    <!-- =================================================================== -->
    <!-- 1. Modal Nhập kho tổng -->
    <ModalNhapKho
      :show="modals.nhapKho"
      :danhSachSanPham="danhSachSanPham"
      :sanPhamMacDinhId="modalParams.sanPhamId"
      @close="modals.nhapKho = false"
      @success="onModalSuccess"
    />

    <!-- 2. Modal Chuẩn bị Check-in -->
    <ModalChuanBiCheckIn
      :show="modals.chuanBi"
      :phongId="modalParams.phongId"
      @close="modals.chuanBi = false"
      @success="onModalSuccess"
    />

    <!-- 3. Modal Bổ sung Minibar -->
    <ModalBoSungMinibar
      :show="modals.boSung"
      :phongId="modalParams.phongId"
      :phongTen="modalParams.phongTen"
      :danhSachSanPham="danhSachSanPham"
      :danhSachPhong="danhSachPhong"
      @close="modals.boSung = false"
      @success="onModalSuccess"
    />

    <!-- 4. Modal Kiểm kê Check-out -->
    <ModalKiemKeCheckOut
      :show="modals.checkOut"
      :phongId="modalParams.phongId"
      @close="modals.checkOut = false"
      @success="onModalSuccess"
    />

    <!-- 5. Modal Báo hỏng / mất tài sản -->
    <ModalBaoHongTaiSan
      :show="modals.baoHong"
      :taiSanId="modalParams.taiSanId"
      :danhSachTaiSan="danhSachTaiSan"
      @close="modals.baoHong = false"
      @success="onModalSuccess"
    />

    <!-- 6. Modal Thêm tài sản -->
    <ModalThemTaiSan
      :show="modals.themTaiSan"
      :danhSachPhong="danhSachPhong"
      :phongMacDinhId="modalParams.phongId"
      @close="modals.themTaiSan = false"
      @success="onModalSuccess"
    />

    <!-- 7. Modal Xem chi tiết phiếu kiểm kê -->
    <ModalChiTietPhieu
      :show="modals.chiTietPhieu"
      :phieu="modalParams.phieuDuocChon"
      @close="modals.chiTietPhieu = false"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { minibarTaiSanService } from './minibarTaiSanService';

import ModalNhapKho from './ModalNhapKho.vue';
import ModalChuanBiCheckIn from './ModalChuanBiCheckIn.vue';
import ModalBoSungMinibar from './ModalBoSungMinibar.vue';
import ModalKiemKeCheckOut from './ModalKiemKeCheckOut.vue';
import ModalBaoHongTaiSan from './ModalBaoHongTaiSan.vue';
import ModalThemTaiSan from './ModalThemTaiSan.vue';
import ModalChiTietPhieu from './ModalChiTietPhieu.vue';

export default {
  name: 'MinibarTaiSan',
  components: {
    ModalNhapKho,
    ModalChuanBiCheckIn,
    ModalBoSungMinibar,
    ModalKiemKeCheckOut,
    ModalBaoHongTaiSan,
    ModalThemTaiSan,
    ModalChiTietPhieu
  },
  setup() {
    const router = useRouter();

    // Tab đang chọn: 'minibar' | 'tai-san' | 'nhat-ky'
    const activeTab = ref('minibar');

    // Toast
    const toast = reactive({
      show: false,
      message: '',
      type: 'success'
    });

    const hienToast = (msg, type = 'success') => {
      toast.message = msg;
      toast.type = type;
      toast.show = true;
      setTimeout(() => {
        toast.show = false;
      }, 4000);
    };

    // Ngày hiện tại
    const ngayHienTai = computed(() => {
      const d = new Date();
      return `Chủ nhật, ${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`;
    });

    // Vai trò kiểm thử (Lễ tân, Buồng phòng, Admin)
    const roles = [
      { id: 'admin', ten: 'Admin / Quản lý', icon: 'bi-shield-lock-fill' },
      { id: 'letan', ten: 'Lễ tân (Mai Linh)', icon: 'bi-person-check-fill' },
      { id: 'buongphong', ten: 'Buồng phòng (Trần Hoa)', icon: 'bi-card-checklist' }
    ];
    const currentRole = ref(roles[0]);
    const chonRole = (r) => {
      currentRole.value = r;
      hienToast(`Đã chuyển sang vai trò: ${r.ten}`);
    };

    // ---------------- STATE TAB 1: MINIBAR & TỒN KHO ----------------
    const loadingMinibar = ref(false);
    const danhSachSanPham = ref([]);
    const thongKeMinibar = ref({});
    const filterMinibar = reactive({
      q: '',
      loaiSanPham: 'Tất cả',
      trangThai: 'Tất cả'
    });

    const loadingPhong = ref(false);
    const danhSachPhong = ref([]);
    const filterPhong = reactive({
      tang: 'Tất cả',
      trangThai: 'Tất cả'
    });

    const taiDanhSachMinibar = async () => {
      loadingMinibar.value = true;
      try {
        const res = await minibarTaiSanService.getDanhSachSanPham(filterMinibar);
        danhSachSanPham.value = res.data || [];
        thongKeMinibar.value = res.thongKe || {};
      } catch (err) {
        hienToast('Lỗi tải sản phẩm minibar: ' + (err.message || 'Lỗi mạng'), 'danger');
      } finally {
        loadingMinibar.value = false;
      }
    };

    const taiDanhSachPhong = async () => {
      loadingPhong.value = true;
      try {
        const res = await minibarTaiSanService.getDanhSachPhong(filterPhong);
        danhSachPhong.value = res.data || [];
      } catch (err) {
        hienToast('Lỗi tải danh sách phòng: ' + (err.message || 'Lỗi mạng'), 'danger');
      } finally {
        loadingPhong.value = false;
      }
    };

    // ---------------- STATE TAB 2: TÀI SẢN THEO PHÒNG ----------------
    const loadingTaiSan = ref(false);
    const danhSachTaiSan = ref([]);
    const thongKeTaiSan = ref({});
    const filterTaiSan = reactive({
      q: '',
      maPhong: 'Tất cả',
      tinhTrang: 'Tất cả'
    });

    const taiDanhSachTaiSan = async () => {
      loadingTaiSan.value = true;
      try {
        const res = await minibarTaiSanService.getDanhSachTaiSan(filterTaiSan);
        danhSachTaiSan.value = res.data || [];
        thongKeTaiSan.value = res.thongKe || {};
      } catch (err) {
        hienToast('Lỗi tải danh sách tài sản: ' + (err.message || 'Lỗi mạng'), 'danger');
      } finally {
        loadingTaiSan.value = false;
      }
    };

    // ---------------- STATE TAB 3: NHẬT KÝ KIỂM KÊ ----------------
    const loadingNhatKy = ref(false);
    const danhSachNhatKy = ref([]);
    const filterNhatKy = reactive({
      q: '',
      loaiKiemKe: 'Tất cả',
      trangThai: 'Tất cả'
    });

    const taiDanhSachNhatKy = async () => {
      loadingNhatKy.value = true;
      try {
        const res = await minibarTaiSanService.getDanhSachNhatKy(filterNhatKy);
        danhSachNhatKy.value = res.data || [];
      } catch (err) {
        hienToast('Lỗi tải nhật ký kiểm kê: ' + (err.message || 'Lỗi mạng'), 'danger');
      } finally {
        loadingNhatKy.value = false;
      }
    };

    // ---------------- MODALS CONTROL ----------------
    const modals = reactive({
      nhapKho: false,
      chuanBi: false,
      boSung: false,
      checkOut: false,
      baoHong: false,
      themTaiSan: false,
      chiTietPhieu: false
    });

    const modalParams = reactive({
      sanPhamId: null,
      phongId: null,
      phongTen: '',
      taiSanId: null,
      phieuDuocChon: null
    });

    const moModalNhapKho = (spId = null) => {
      modalParams.sanPhamId = spId;
      modals.nhapKho = true;
    };

    const moModalChuanBi = (pId) => {
      modalParams.phongId = pId;
      modals.chuanBi = true;
    };

    const moModalBoSung = (pId, pTen) => {
      modalParams.phongId = pId;
      modalParams.phongTen = pTen;
      modals.boSung = true;
    };

    const moModalCheckOut = (pId) => {
      modalParams.phongId = pId;
      modals.checkOut = true;
    };

    const moModalBaoHong = (tsId = null) => {
      modalParams.taiSanId = tsId;
      modals.baoHong = true;
    };

    const moModalThemTaiSan = (pId = null) => {
      modalParams.phongId = pId;
      modals.themTaiSan = true;
    };

    const xemChiTietPhieu = (phieu) => {
      modalParams.phieuDuocChon = phieu;
      modals.chiTietPhieu = true;
    };

    const onModalSuccess = (msg) => {
      hienToast(msg, 'success');
      // Tải lại các dữ liệu
      taiDanhSachMinibar();
      taiDanhSachPhong();
      taiDanhSachTaiSan();
      taiDanhSachNhatKy();
    };

    const quayLai = () => {
      router.back();
    };

    onMounted(() => {
      taiDanhSachMinibar();
      taiDanhSachPhong();
      taiDanhSachTaiSan();
      taiDanhSachNhatKy();
    });

    return {
      activeTab,
      toast,
      ngayHienTai,
      roles,
      currentRole,
      chonRole,
      // Tab 1
      loadingMinibar,
      danhSachSanPham,
      thongKeMinibar,
      filterMinibar,
      loadingPhong,
      danhSachPhong,
      filterPhong,
      taiDanhSachMinibar,
      taiDanhSachPhong,
      // Tab 2
      loadingTaiSan,
      danhSachTaiSan,
      thongKeTaiSan,
      filterTaiSan,
      taiDanhSachTaiSan,
      // Tab 3
      loadingNhatKy,
      danhSachNhatKy,
      filterNhatKy,
      taiDanhSachNhatKy,
      // Modals
      modals,
      modalParams,
      moModalNhapKho,
      moModalChuanBi,
      moModalBoSung,
      moModalCheckOut,
      moModalBaoHong,
      moModalThemTaiSan,
      xemChiTietPhieu,
      onModalSuccess,
      quayLai
    };
  }
};
</script>

<style scoped>
.minibar-page {
  background-color: #f8fafc;
  min-height: 100vh;
}

/* Màu Teal chủ đạo */
.text-teal {
  color: #1e5e49 !important;
}

.btn-teal-primary {
  background-color: #1e5e49;
  border-color: #1e5e49;
}
.btn-teal-primary:hover {
  background-color: #164636;
  border-color: #164636;
}

.btn-outline-teal {
  color: #1e5e49;
  border-color: #1e5e49;
}
.btn-outline-teal:hover {
  background-color: #1e5e49;
  color: #ffffff;
}

.bg-teal-subtle {
  background-color: #e6f4ea;
}

.stat-icon-box {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

/* User avatar */
.user-avatar-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #1e5e49;
  color: #fff;
  font-weight: 700;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Custom Tabs */
.custom-tab-pills .nav-link {
  color: #64748b;
  background-color: transparent;
  transition: all 0.2s ease;
}
.custom-tab-pills .nav-link:hover {
  color: #1e5e49;
  background-color: #f1f7f4;
}
.custom-tab-pills .nav-link.active {
  color: #ffffff;
  background-color: #1e5e49;
  box-shadow: 0 2px 4px rgba(30, 94, 73, 0.25);
}

/* Dot Pill trạng thái */
.status-dot-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 9999px;
  font-size: 0.78rem;
  font-weight: 600;
}

.status-dot-pill .dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
}

.pill-active {
  background-color: #ecfdf5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}
.pill-active .dot {
  background-color: #10b981;
}

.pill-paused {
  background-color: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
}
.pill-paused .dot {
  background-color: #f59e0b;
}

.pill-expired {
  background-color: #fef2f2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
.pill-expired .dot {
  background-color: #ef4444;
}

.shadow-xs {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.extra-small {
  font-size: 0.72rem;
}

.custom-main-table th {
  background-color: #fafbfc;
  color: #64748b;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
}
</style>
