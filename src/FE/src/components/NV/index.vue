<template>
  <div class="p-3 p-lg-4">
    <!-- 1. Header Toolbar & Tab Switcher bên trong phân hệ nhân viên -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
      <div>
        <h3 class="fw-bold text-main-dark mb-1">Hệ thống Vận hành Quản trị Khách sạn (PMS)</h3>
        <p class="text-secondary-custom small mb-0">
          Ca trực: <strong>Mai Linh</strong> (Lễ tân · Ca sáng) · Ngày làm việc: <strong>14/11/2026</strong>
        </p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button
          @click="openNewBookingModal = true"
          class="btn btn-create-booking px-3 py-2 text-white rounded-3 fw-bold small d-flex align-items-center gap-2 shadow-sm"
        >
          <i class="bi bi-plus-circle-fill"></i>
          <span>Tạo đặt phòng tại quầy</span>
        </button>
      </div>
    </div>

    <!-- 2. Nav Pills Tabs chuyển đổi nghiệp vụ -->
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
      <ul class="nav nav-pills gap-2">
        <li class="nav-item">
          <button
            @click="currentTab = 'overview'"
            class="nav-link rounded-3 px-3 py-2 small fw-bold d-flex align-items-center gap-2"
            :class="currentTab === 'overview' ? 'active-pms-tab' : 'text-secondary-custom'"
          >
            <i class="bi bi-grid-1x2"></i> Tổng quan ca trực
          </button>
        </li>
        <li class="nav-item">
          <button
            @click="currentTab = 'bookings'"
            class="nav-link rounded-3 px-3 py-2 small fw-bold d-flex align-items-center gap-2"
            :class="currentTab === 'bookings' ? 'active-pms-tab' : 'text-secondary-custom'"
          >
            <i class="bi bi-calendar-check"></i> Quản lý Đặt phòng ({{ bookings.length }})
          </button>
        </li>
        <li class="nav-item">
          <button
            @click="currentTab = 'rooms'"
            class="nav-link rounded-3 px-3 py-2 small fw-bold d-flex align-items-center gap-2"
            :class="currentTab === 'rooms' ? 'active-pms-tab' : 'text-secondary-custom'"
          >
            <i class="bi bi-map"></i> Sơ đồ phòng ({{ rooms.length }})
          </button>
        </li>
        <li class="nav-item">
          <button
            @click="currentTab = 'housekeeping'"
            class="nav-link rounded-3 px-3 py-2 small fw-bold d-flex align-items-center gap-2"
            :class="currentTab === 'housekeeping' ? 'active-pms-tab' : 'text-secondary-custom'"
          >
            <i class="bi bi-house-door"></i> Buồng phòng & Nhiệm vụ
          </button>
        </li>
        <li class="nav-item">
          <button
            @click="currentTab = 'invoices'"
            class="nav-link rounded-3 px-3 py-2 small fw-bold d-flex align-items-center gap-2"
            :class="currentTab === 'invoices' ? 'active-pms-tab' : 'text-secondary-custom'"
          >
            <i class="bi bi-receipt"></i> Hóa đơn & Thu ngân
          </button>
        </li>
      </ul>
    </div>

    <!-- ======================================================================
     * TAB 1: TỔNG QUAN CA TRỰC (OVERVIEW)
     * ==================================================================== -->
    <div v-if="currentTab === 'overview'" class="space-y-4">
      <!-- 4 Thẻ KPI chính -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card kpi-card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary-custom small fw-semibold">Công suất phòng</span>
              <div class="kpi-icon-box rounded-3 bg-success-subtle text-success-custom d-flex align-items-center justify-content-center">
                <i class="bi bi-pie-chart-fill fs-5"></i>
              </div>
            </div>
            <div class="fs-2 fw-bold text-main-dark mb-1 font-monospace">78.5%</div>
            <div class="small text-success-custom d-flex align-items-center gap-1">
              <i class="bi bi-arrow-up-right"></i>
              <span>+4.2% so với hôm qua</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card kpi-card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary-custom small fw-semibold">Doanh thu hôm nay</span>
              <div class="kpi-icon-box rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                <i class="bi bi-cash-stack fs-5"></i>
              </div>
            </div>
            <div class="fs-3 fw-bold text-main-dark mb-1 font-monospace">{{ formatVND(18500000) }}</div>
            <div class="small text-primary d-flex align-items-center gap-1">
              <i class="bi bi-receipt"></i>
              <span>12 giao dịch thanh toán</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card kpi-card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary-custom small fw-semibold">Khách đang lưu trú</span>
              <div class="kpi-icon-box rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center">
                <i class="bi bi-people-fill fs-5"></i>
              </div>
            </div>
            <div class="fs-2 fw-bold text-main-dark mb-1 font-monospace">34 khách</div>
            <div class="small text-muted">
              <span>Đang ở 17 phòng</span>
            </div>
          </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
          <div class="card kpi-card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-secondary-custom small fw-semibold">Phòng cần dọn</span>
              <div class="kpi-icon-box rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center">
                <i class="bi bi-brush-fill fs-5"></i>
              </div>
            </div>
            <div class="fs-2 fw-bold text-warning mb-1 font-monospace">4 phòng</div>
            <div class="small text-warning-emphasis">
              <span>2 phòng khách vừa trả</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bảng đón tiếp hôm nay & Nhắc việc -->
      <div class="row g-4">
        <!-- Cột trái: Lịch nhận phòng trong ngày -->
        <div class="col-12 col-lg-8">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
              <h5 class="fw-bold text-main-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-box-arrow-in-right text-success-custom"></i>
                <span>Khách nhận phòng hôm nay (Check-in)</span>
              </h5>
              <span class="badge bg-light text-dark border">{{ todaysCheckIns.length }} lượt</span>
            </div>

            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light">
                  <tr>
                    <th>MÃ ĐẶT</th>
                    <th>KHÁCH HÀNG</th>
                    <th>PHÒNG</th>
                    <th>DỰ KIẾN ĐẾN</th>
                    <th>TIỀN CỌC</th>
                    <th class="text-end">THAO TÁC</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="b in todaysCheckIns" :key="b.id">
                    <td class="fw-bold text-main-dark">{{ b.id }}</td>
                    <td>
                      <div class="fw-semibold">{{ b.guestName }}</div>
                      <small class="text-muted">{{ b.phone }}</small>
                    </td>
                    <td><span class="badge bg-light text-dark border">Phòng {{ b.roomNumber }} ({{ b.roomType }})</span></td>
                    <td>{{ b.arrivalTime }}</td>
                    <td class="fw-bold text-success-custom">{{ formatVND(b.deposit) }}</td>
                    <td class="text-end">
                      <button
                        @click="doCheckIn(b)"
                        :disabled="b.status === 'staying'"
                        class="btn btn-sm px-3 rounded-pill fw-bold"
                        :class="b.status === 'staying' ? 'btn-secondary' : 'btn-success-custom text-white'"
                      >
                        {{ b.status === 'staying' ? 'Đã nhận phòng' : 'Nhận phòng' }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Cột phải: Nhiệm vụ bàn giao ca -->
        <div class="col-12 col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h5 class="fw-bold text-main-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-clipboard-check text-success-custom"></i>
              <span>Ghi chú ca trực lễ tân</span>
            </h5>
            <div class="space-y-3">
              <div class="p-3 bg-light rounded-3 border mb-2">
                <div class="fw-semibold text-main-dark small">Phòng 303 yêu cầu trả trễ:</div>
                <div class="text-muted small mt-0.5">Khách gia hạn trả phòng lúc 14:00 (Hội viên Vàng miễn phí phí trễ).</div>
              </div>
              <div class="p-3 bg-light rounded-3 border mb-2">
                <div class="fw-semibold text-main-dark small">Đoàn khách Pháp check-in:</div>
                <div class="text-muted small mt-0.5">Chuyến bay VN-128 hạ cánh lúc 15:30, xe riêng khách sạn đón tại Sân bay Đà Nẵng.</div>
              </div>
              <div class="p-3 bg-light rounded-3 border">
                <div class="fw-semibold text-main-dark small">Bảo trì điều hòa phòng 204:</div>
                <div class="text-muted small mt-0.5">Kỹ thuật viên Tuấn đã xử lý xong lúc 10:00, phòng đã sẵn sàng đón khách.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
     * TAB 2: QUẢN LÝ ĐẶT PHÒNG (BOOKINGS)
     * ==================================================================== -->
    <div v-else-if="currentTab === 'bookings'" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
      <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
          <h4 class="fw-bold text-main-dark mb-1">Danh sách hồ sơ đặt phòng</h4>
          <p class="text-secondary-custom small mb-0">Theo dõi toàn bộ đơn đặt phòng từ Website, OTA và Quầy trực tiếp.</p>
        </div>
        <div class="d-flex gap-2">
          <input
            v-model="bookingSearchQuery"
            type="text"
            class="form-control form-control-sm rounded-pill"
            placeholder="Tìm theo tên hoặc mã phòng..."
            style="width: 240px;"
          />
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light">
            <tr>
              <th>MÃ ĐẶT</th>
              <th>KHÁCH HÀNG</th>
              <th>HẠNG PHÒNG</th>
              <th>LƯU TRÚ</th>
              <th>TỔNG TIỀN</th>
              <th>TRẠNG THÁI</th>
              <th class="text-end">THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in filteredBookings" :key="b.id">
              <td class="fw-bold text-main-dark">{{ b.id }}</td>
              <td>
                <div class="fw-semibold">{{ b.guestName }}</div>
                <small class="text-muted">{{ b.phone }}</small>
              </td>
              <td>
                <span class="badge bg-light text-dark border">P.{{ b.roomNumber }} ({{ b.roomType }})</span>
              </td>
              <td>
                <div>{{ b.checkIn }} - {{ b.checkOut }}</div>
                <small class="text-muted">{{ b.nights }} đêm</small>
              </td>
              <td class="fw-bold text-success-custom">{{ formatVND(b.totalAmount) }}</td>
              <td>
                <span class="badge px-2.5 py-1 rounded-pill" :class="getStatusBadgeClass(b.status)">
                  ● {{ getStatusText(b.status) }}
                </span>
              </td>
              <td class="text-end">
                <button
                  v-if="b.status === 'confirmed'"
                  @click="doCheckIn(b)"
                  class="btn btn-sm btn-outline-success rounded-pill px-3 me-1 fw-semibold"
                >
                  Nhận phòng
                </button>
                <button
                  v-else-if="b.status === 'staying'"
                  @click="doCheckOut(b)"
                  class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1 fw-semibold"
                >
                  Trả phòng
                </button>
                <button
                  v-if="b.status !== 'cancelled' && b.status !== 'checked_out'"
                  @click="cancelBooking(b)"
                  class="btn btn-sm btn-outline-danger rounded-pill px-2.5"
                  title="Hủy đặt phòng"
                >
                  <i class="bi bi-x-lg"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
     * TAB 3: SƠ ĐỒ PHÒNG (ROOMS GRID)
     * ==================================================================== -->
    <div v-else-if="currentTab === 'rooms'" class="space-y-4">
      <!-- Bộ lọc sơ đồ phòng -->
      <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white d-flex flex-wrap flex-row align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 small">
          <span class="fw-bold text-main-dark">Mã màu trạng thái:</span>
          <span class="badge bg-success-subtle text-success-custom border">● Phòng trống sẵn sàng</span>
          <span class="badge bg-danger-subtle text-danger border">● Khách đang ở</span>
          <span class="badge bg-warning-subtle text-warning-emphasis border">● Cần dọn phòng</span>
          <span class="badge bg-secondary-subtle text-secondary border">● Bảo trì</span>
        </div>
      </div>

      <div class="row g-3">
        <div
          v-for="r in rooms"
          :key="r.number"
          class="col-6 col-sm-4 col-md-3 col-xl-2"
        >
          <div
            class="card room-grid-box h-100 border rounded-4 p-3 text-center transition"
            :class="getRoomStatusBoxClass(r.status)"
          >
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-white text-dark shadow-xs border" style="font-size: 0.65rem;">{{ r.type }}</span>
              <span class="small fw-bold" :class="getRoomStatusTextClass(r.status)">● {{ getRoomStatusLabel(r.status) }}</span>
            </div>
            <div class="display-6 fw-bold my-1 font-monospace">{{ r.number }}</div>
            <div class="small text-truncate mt-1" style="font-size: 0.75rem;">
              {{ r.guestName || 'Phòng trống' }}
            </div>
            <div class="mt-3 pt-2 border-top">
              <select
                :value="r.status"
                @change="updateRoomStatus(r, $event.target.value)"
                class="form-select form-select-sm rounded-pill text-center border-0 bg-white shadow-xs fw-semibold"
                style="font-size: 0.7rem;"
              >
                <option value="available">Sẵn sàng đón khách</option>
                <option value="occupied">Đang ở</option>
                <option value="cleaning">Cần dọn phòng</option>
                <option value="maintenance">Bảo trì</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ======================================================================
     * TAB 4: BUỒNG PHÒNG & NHIỆM VỤ (HOUSEKEEPING)
     * ==================================================================== -->
    <div v-else-if="currentTab === 'housekeeping'" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
      <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
          <h4 class="fw-bold text-main-dark mb-1">Nhiệm vụ buồng phòng & dọn dẹp</h4>
          <p class="text-secondary-custom small mb-0">Điều phối nhân sự dọn phòng, thay đồ vải và kiểm kê minibar.</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light">
            <tr>
              <th>MÃ VIỆC</th>
              <th>PHÒNG</th>
              <th>LOẠI CÔNG VIỆC</th>
              <th>NHÂN SỰ PHỤ TRÁCH</th>
              <th>ƯU TIÊN</th>
              <th>TRẠNG THÁI</th>
              <th class="text-end">HOÀN TẤT</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in housekeepingTasks" :key="t.id">
              <td class="fw-bold text-main-dark">{{ t.id }}</td>
              <td><span class="badge bg-light text-dark border fs-6">P.{{ t.roomNumber }}</span></td>
              <td>{{ t.taskType }}</td>
              <td>
                <span class="fw-semibold">{{ t.assignee }}</span>
              </td>
              <td>
                <span class="badge" :class="t.priority === 'Cao' ? 'bg-danger-subtle text-danger' : 'bg-light text-dark border'">
                  {{ t.priority }}
                </span>
              </td>
              <td>
                <span class="badge px-2.5 py-1 rounded-pill" :class="t.status === 'done' ? 'bg-success-subtle text-success-custom' : 'bg-warning-subtle text-warning-emphasis'">
                  {{ t.status === 'done' ? '✓ Đã xong' : '⏳ Đang làm' }}
                </span>
              </td>
              <td class="text-end">
                <button
                  @click="toggleTaskDone(t)"
                  class="btn btn-sm rounded-pill px-3 fw-bold"
                  :class="t.status === 'done' ? 'btn-outline-secondary' : 'btn-success-custom text-white'"
                >
                  {{ t.status === 'done' ? 'Làm lại' : 'Xác nhận sạch' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ======================================================================
     * TAB 5: HÓA ĐƠN & THU NGÂN (INVOICES)
     * ==================================================================== -->
    <div v-else-if="currentTab === 'invoices'" class="card border-0 shadow-sm rounded-4 p-4 bg-white">
      <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div>
          <h4 class="fw-bold text-main-dark mb-1">Hóa đơn & Chứng từ thanh toán</h4>
          <p class="text-secondary-custom small mb-0">Quản lý thu ngân, tiền phòng, dịch vụ phát sinh và đối soát check-out.</p>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
          <thead class="table-light">
            <tr>
              <th>SỐ HÓA ĐƠN</th>
              <th>KHÁCH HÀNG</th>
              <th>PHÒNG</th>
              <th>TIỀN PHÒNG</th>
              <th>DỊCH VỤ / MINIBAR</th>
              <th>TỔNG THANH TOÁN</th>
              <th>TRẠNG THÁI</th>
              <th class="text-end">THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id">
              <td class="fw-bold text-main-dark font-monospace">{{ inv.id }}</td>
              <td>
                <div class="fw-semibold">{{ inv.guestName }}</div>
                <small class="text-muted">{{ inv.date }}</small>
              </td>
              <td><span class="badge bg-light text-dark border">P.{{ inv.roomNumber }}</span></td>
              <td>{{ formatVND(inv.roomFee) }}</td>
              <td>{{ formatVND(inv.serviceFee) }}</td>
              <td class="fw-bold text-success-custom fs-6">{{ formatVND(inv.total) }}</td>
              <td>
                <span class="badge px-2.5 py-1 rounded-pill" :class="inv.status === 'paid' ? 'bg-success-subtle text-success-custom' : 'bg-warning-subtle text-warning-emphasis'">
                  ● {{ inv.status === 'paid' ? 'Đã tất toán' : 'Chưa thanh toán' }}
                </span>
              </td>
              <td class="text-end">
                <button
                  v-if="inv.status !== 'paid'"
                  @click="settleInvoice(inv)"
                  class="btn btn-sm btn-success-custom text-white rounded-pill px-3 fw-bold"
                >
                  Tất toán
                </button>
                <button v-else class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                  Đã in HĐ
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tạo đặt phòng mới tại quầy -->
    <div v-if="openNewBookingModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center p-3">
      <div class="modal-dialog-custom bg-white rounded-4 shadow-2xl overflow-hidden w-100 max-w-lg">
        <div class="modal-header border-bottom p-4 d-flex align-items-center justify-content-between bg-light">
          <h5 class="modal-title fw-bold text-main-dark d-flex align-items-center gap-2">
            <i class="bi bi-calendar-plus text-success-custom"></i>
            Tạo đặt phòng mới tại quầy lễ tân
          </h5>
          <button @click="openNewBookingModal = false" class="btn-close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Họ và tên khách</label>
            <input v-model="newBookingForm.guestName" type="text" class="form-control" placeholder="Ví dụ: Hoàng Văn Nam" />
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">Số điện thoại</label>
              <input v-model="newBookingForm.phone" type="tel" class="form-control" placeholder="091•••••••" />
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">Số phòng xếp</label>
              <select v-model="newBookingForm.roomNumber" class="form-select">
                <option value="101">101 - Deluxe</option>
                <option value="102">102 - Superior</option>
                <option value="201">201 - Deluxe</option>
                <option value="202">202 - Suite</option>
                <option value="301">301 - Family</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">Ngày nhận</label>
              <input v-model="newBookingForm.checkIn" type="date" class="form-control" />
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold text-muted">Ngày trả</label>
              <input v-model="newBookingForm.checkOut" type="date" class="form-control" />
            </div>
          </div>
        </div>
        <div class="modal-footer border-top p-3 d-flex justify-content-end gap-2 bg-light">
          <button @click="openNewBookingModal = false" class="btn btn-outline-secondary px-4 rounded-3">Đóng</button>
          <button @click="saveNewBooking" class="btn btn-create-booking text-white px-4 rounded-3 fw-bold">Lưu hồ sơ đặt phòng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NhanVienDashboard',
  data() {
    return {
      currentTab: 'overview',
      bookingSearchQuery: '',
      openNewBookingModal: false,

      newBookingForm: {
        guestName: '',
        phone: '',
        roomNumber: '101',
        checkIn: '2026-11-14',
        checkOut: '2026-11-16',
      },

      bookings: [
        {
          id: 'AN-261108-01',
          guestName: 'Nguyễn Minh Anh',
          phone: '0903 456 789',
          roomNumber: '303',
          roomType: 'Deluxe',
          checkIn: '14/11',
          checkOut: '17/11',
          nights: 3,
          deposit: 1000000,
          totalAmount: 2700000,
          arrivalTime: '14:00',
          status: 'staying',
        },
        {
          id: 'AN-261108-02',
          guestName: 'Trần Quốc Huy',
          phone: '0978 123 912',
          roomNumber: '205',
          roomType: 'Superior',
          checkIn: '14/11',
          checkOut: '16/11',
          nights: 2,
          deposit: 0,
          totalAmount: 1500000,
          arrivalTime: '14:30',
          status: 'confirmed',
        },
        {
          id: 'AN-261108-03',
          guestName: 'Lê Thu Hà',
          phone: '0945 884 302',
          roomNumber: '401',
          roomType: 'Suite',
          checkIn: '14/11',
          checkOut: '15/11',
          nights: 1,
          deposit: 1450000,
          totalAmount: 1450000,
          arrivalTime: '15:00',
          status: 'confirmed',
        },
      ],

      rooms: [
        { number: '101', type: 'Deluxe', status: 'available', guestName: '' },
        { number: '102', type: 'Superior', status: 'cleaning', guestName: 'Chờ dọn sau trả' },
        { number: '201', type: 'Deluxe', status: 'occupied', guestName: 'Võ Minh Trí' },
        { number: '202', type: 'Suite', status: 'occupied', guestName: 'Đặng Thu Thảo' },
        { number: '203', type: 'Superior', status: 'maintenance', guestName: 'Bảo trì điều hòa' },
        { number: '204', type: 'Superior', status: 'available', guestName: '' },
        { number: '301', type: 'Family', status: 'available', guestName: '' },
        { number: '302', type: 'Deluxe', status: 'available', guestName: '' },
        { number: '303', type: 'Deluxe', status: 'occupied', guestName: 'Nguyễn Minh Anh' },
        { number: '401', type: 'Suite', status: 'available', guestName: '' },
        { number: '402', type: 'Suite', status: 'occupied', guestName: 'Phạm Đức Long' },
        { number: '403', type: 'Family', status: 'cleaning', guestName: 'Dọn phòng định kỳ' },
      ],

      housekeepingTasks: [
        { id: 'TSK-01', roomNumber: '102', taskType: 'Dọn phòng sau trả & thay ga gối', assignee: 'Nguyễn Hương (Trưởng nhóm)', priority: 'Cao', status: 'doing' },
        { id: 'TSK-02', roomNumber: '303', taskType: 'Dọn phòng định kỳ & bổ sung nước', assignee: 'Trần Hoa', priority: 'Bình thường', status: 'doing' },
        { id: 'TSK-03', roomNumber: '403', taskType: 'Khử khuẩn & kiểm tra minibar', assignee: 'Trần Hoa', priority: 'Cao', status: 'done' },
      ],

      invoices: [
        { id: 'INV-2611-001', guestName: 'Nguyễn Minh Anh', roomNumber: '303', date: '14/11/2026', roomFee: 2700000, serviceFee: 220000, total: 2920000, status: 'unpaid' },
        { id: 'INV-2611-002', guestName: 'Trần Quốc Huy', roomNumber: '205', date: '14/11/2026', roomFee: 1500000, serviceFee: 0, total: 1500000, status: 'paid' },
        { id: 'INV-2611-003', guestName: 'Lê Thu Hà', roomNumber: '401', date: '14/11/2026', roomFee: 1450000, serviceFee: 350000, total: 1800000, status: 'paid' },
      ],
    };
  },
  computed: {
    todaysCheckIns() {
      return this.bookings.filter((b) => b.checkIn === '14/11');
    },
    filteredBookings() {
      if (!this.bookingSearchQuery) return this.bookings;
      const q = this.bookingSearchQuery.toLowerCase();
      return this.bookings.filter((b) => b.guestName.toLowerCase().includes(q) || b.roomNumber.includes(q) || b.id.toLowerCase().includes(q));
    },
  },
  methods: {
    formatVND(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    doCheckIn(booking) {
      booking.status = 'staying';
      const r = this.rooms.find((room) => room.number === booking.roomNumber);
      if (r) {
        r.status = 'occupied';
        r.guestName = booking.guestName;
      }
      alert(`Đã nhận phòng thành công cho khách ${booking.guestName} vào phòng ${booking.roomNumber}!`);
    },
    doCheckOut(booking) {
      booking.status = 'checked_out';
      const r = this.rooms.find((room) => room.number === booking.roomNumber);
      if (r) {
        r.status = 'cleaning';
        r.guestName = 'Chờ dọn sau trả phòng';
      }
      alert(`Đã hoàn tất thủ tục trả phòng ${booking.roomNumber}! Phòng được chuyển sang trạng thái Cần dọn dẹp.`);
    },
    cancelBooking(booking) {
      if (confirm(`Bạn có chắc chắn muốn hủy đặt phòng ${booking.id}?`)) {
        booking.status = 'cancelled';
      }
    },
    updateRoomStatus(room, newStatus) {
      room.status = newStatus;
      if (newStatus === 'available') room.guestName = '';
    },
    toggleTaskDone(task) {
      task.status = task.status === 'done' ? 'doing' : 'done';
    },
    settleInvoice(inv) {
      inv.status = 'paid';
      alert(`Hóa đơn ${inv.id} đã được tất toán thành công!`);
    },
    saveNewBooking() {
      if (!this.newBookingForm.guestName || !this.newBookingForm.phone) {
        alert('Vui lòng điền đủ thông tin khách hàng!');
        return;
      }
      const newBk = {
        id: 'AN-261108-0' + (this.bookings.length + 1),
        guestName: this.newBookingForm.guestName,
        phone: this.newBookingForm.phone,
        roomNumber: this.newBookingForm.roomNumber,
        roomType: 'Deluxe',
        checkIn: '14/11',
        checkOut: '16/11',
        nights: 2,
        deposit: 500000,
        totalAmount: 1800000,
        arrivalTime: '14:00',
        status: 'confirmed',
      };
      this.bookings.unshift(newBk);
      this.openNewBookingModal = false;
      this.newBookingForm.guestName = '';
      this.newBookingForm.phone = '';
      alert('Tạo hồ sơ đặt phòng thành công!');
    },
    getStatusBadgeClass(status) {
      const map = {
        confirmed: 'bg-primary-subtle text-primary',
        staying: 'bg-success-subtle text-success-custom',
        checked_out: 'bg-secondary-subtle text-secondary',
        cancelled: 'bg-danger-subtle text-danger',
      };
      return map[status] || 'bg-light text-dark';
    },
    getStatusText(status) {
      const map = {
        confirmed: 'Đã xác nhận',
        staying: 'Đang lưu trú',
        checked_out: 'Đã trả phòng',
        cancelled: 'Đã hủy',
      };
      return map[status] || status;
    },
    getRoomStatusBoxClass(status) {
      const map = {
        available: 'border-success-subtle bg-white',
        occupied: 'border-danger-subtle bg-danger-subtle bg-opacity-25',
        cleaning: 'border-warning-subtle bg-warning-subtle bg-opacity-25',
        maintenance: 'border-secondary-subtle bg-secondary-subtle bg-opacity-25',
      };
      return map[status] || 'bg-white';
    },
    getRoomStatusTextClass(status) {
      const map = {
        available: 'text-success-custom',
        occupied: 'text-danger',
        cleaning: 'text-warning-emphasis',
        maintenance: 'text-secondary',
      };
      return map[status] || 'text-dark';
    },
    getRoomStatusLabel(status) {
      const map = {
        available: 'Trống',
        occupied: 'Đang ở',
        cleaning: 'Cần dọn',
        maintenance: 'Bảo trì',
      };
      return map[status] || status;
    },
  },
};
</script>

<style scoped>
.text-main-dark {
  color: #172620;
}

.text-secondary-custom {
  color: #5C6862;
}

.text-success-custom {
  color: #1E5E49;
}

.bg-success-custom {
  background-color: #1E5E49 !important;
}

.btn-success-custom {
  background-color: #1E5E49;
  border-color: #1E5E49;
}

.btn-success-custom:hover {
  background-color: #164636;
}

.btn-create-booking {
  background-color: #1E5E49;
  border: none;
}

.btn-create-booking:hover {
  background-color: #164636;
}

.active-pms-tab {
  background-color: #1E5E49 !important;
  color: #ffffff !important;
}

.kpi-card {
  border: 1px solid #EAE8E1 !important;
}

.kpi-icon-box {
  width: 44px;
  height: 44px;
}

.room-grid-box {
  border: 1.5px solid #EAE8E1;
}

/* Modal */
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-dialog-custom {
  max-height: 90vh;
}
</style>
