import { defineStore } from 'pinia';

export const useHotelStore = defineStore('hotel', {
  state: () => ({
    // 1. Guest Authentication State
    isGuestLoggedIn: false,
    guestName: 'Nguyễn Minh Anh',
    guestPhone: '0903 456 789',
    guestEmail: 'minhanh.nguyen@gmail.com',
    guestLoyaltyPoints: 320,

    // 2. Staff Authentication & Active Persona
    // 4 vai trò: 'an' (Chủ khách sạn), 'linh' (Lễ tân), 'huong' (Buồng phòng), 'ngoc' (Kế toán)
    activePersona: 'linh',
    isStaffLoggedIn: true,

    // 3. Active PMS View / Tab
    activePmsTab: 'overview',

    // 4. Danh sách các hạng phòng (Room Types)
    roomTypes: [
      {
        name: 'Deluxe',
        basePrice: 900000,
        area: '32 m²',
        capacity: '2 người lớn',
        count: 12,
        rating: 4.9,
        highlight: 'BÁN CHẠY NHẤT',
        description: 'Không gian mở thoáng đãng nhìn ra vườn nhiệt đới xanh mát, trang bị giường King cao cấp cùng ban công riêng lộng gió.',
        image: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80',
        amenities: ['Ban công hướng vườn', 'Bồn tắm ngâm đá tự nhiên', 'Smart TV 55 inch 4K', 'Máy pha cà phê Nespresso', 'Wifi tốc độ cao', 'Minibar miễn phí'],
        hasBalcony: true,
        hasBathtub: true,
      },
      {
        name: 'Superior',
        basePrice: 750000,
        area: '26 m²',
        capacity: '2 người lớn',
        count: 8,
        rating: 4.8,
        highlight: 'TIẾT KIỆM',
        description: 'Thiết kế hiện đại thanh lịch kết hợp chất liệu gỗ sồi ấm áp, cửa sổ lớn ngập tràn ánh sáng ban mai dịu nhẹ.',
        image: 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80',
        amenities: ['Cửa sổ kính lớn toàn cảnh', 'Vòi sen đứng áp lực lớn', 'Bàn làm việc tiện nghi', 'Két sắt điện tử bảo mật', 'Máy sấy tóc cao cấp'],
        hasBalcony: false,
        hasBathtub: false,
      },
      {
        name: 'Suite',
        basePrice: 1450000,
        area: '48 m²',
        capacity: '2 người lớn + 1 trẻ em',
        count: 6,
        rating: 5.0,
        highlight: 'VIEW SÔNG HOÀI',
        description: 'Hạng phòng cao cấp sở hữu phòng khách riêng biệt, bồn sục Jacuzzi ngoài trời cùng tầm nhìn panorama trực diện sông Hoài.',
        image: 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        amenities: ['Phòng khách tiếp khách riêng', 'Bồn sục Jacuzzi ngoài ban công', 'Hệ thống loa Marshall cao cấp', 'Trái cây & Rượu vang chào đón', 'Dịch vụ dọn phòng 2 lần/ngày'],
        hasBalcony: true,
        hasBathtub: true,
      },
      {
        name: 'Family',
        basePrice: 1800000,
        area: '60 m²',
        capacity: '4 người lớn + 2 trẻ em',
        count: 4,
        rating: 4.9,
        highlight: 'CHO GIA ĐÌNH',
        description: 'Giải pháp lý tưởng cho kỳ nghỉ đại gia đình với 2 phòng ngủ riêng tư kết nối liên thông, khu vực ăn uống và ban công đôi rộng rãi.',
        image: 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=800&q=80',
        amenities: ['2 phòng ngủ (1 King + 2 Single)', '2 phòng tắm riêng biệt', 'Khu vực bếp mini & bàn ăn', 'Khu vui chơi trẻ em mini', 'Bữa sáng buffet miễn phí'],
        hasBalcony: true,
        hasBathtub: true,
      },
    ],

    // 5. Danh sách Sơ đồ trạng thái phòng (Rooms)
    rooms: [
      { number: '101', type: 'Deluxe', status: 'available', guestName: '', stayNote: 'Sẵn sàng đón khách' },
      { number: '102', type: 'Superior', status: 'cleaning', guestName: 'Chờ dọn sau trả', stayNote: 'Trả 12:00' },
      { number: '201', type: 'Deluxe', status: 'occupied', guestName: 'Võ Minh Trí', stayNote: 'Trả 16/11 · 2 khách' },
      { number: '202', type: 'Suite', status: 'occupied', guestName: 'Đặng Thu Thảo', stayNote: 'Trả 17/11 · 2 khách' },
      { number: '203', type: 'Superior', status: 'maintenance', guestName: 'Bảo trì điều hòa', stayNote: 'Kỹ thuật viên Tuấn' },
      { number: '204', type: 'Superior', status: 'available', guestName: '', stayNote: 'Sẵn sàng đón khách' },
      { number: '301', type: 'Family', status: 'available', guestName: '', stayNote: 'Sẵn sàng đón khách' },
      { number: '302', type: 'Deluxe', status: 'available', guestName: '', stayNote: 'Sẵn sàng đón khách' },
      { number: '303', type: 'Deluxe', status: 'occupied', guestName: 'Nguyễn Minh Anh', stayNote: 'Trả 17/11 · 2 khách' },
      { number: '401', type: 'Suite', status: 'available', guestName: '', stayNote: 'Sẵn sàng đón khách' },
      { number: '402', type: 'Suite', status: 'occupied', guestName: 'Phạm Đức Long', stayNote: 'Trả 15/11 · 2 khách' },
      { number: '403', type: 'Family', status: 'cleaning', guestName: 'Dọn phòng định kỳ', stayNote: 'Trước 14:00' },
    ],

    // 6. Danh sách Đặt phòng (Bookings)
    bookings: [
      {
        id: 'AN-261108-01',
        guestName: 'Nguyễn Minh Anh',
        phone: '0903 456 789',
        email: 'minhanh.nguyen@gmail.com',
        roomNumber: '303',
        roomType: 'Deluxe',
        checkIn: '14/11',
        checkOut: '17/11',
        nights: 3,
        guestsCount: '2 người lớn',
        depositAmount: 1000000,
        totalAmount: 2700000,
        arrivalTime: '14:00',
        status: 'staying',
        paymentStatus: 'Đã cọc 1.000.000₫',
      },
      {
        id: 'AN-261108-02',
        guestName: 'Trần Quốc Huy',
        phone: '0978 123 912',
        email: 'huy.tran@example.com',
        roomNumber: '205',
        roomType: 'Superior',
        checkIn: '14/11',
        checkOut: '16/11',
        nights: 2,
        guestsCount: '2 người lớn',
        depositAmount: 0,
        totalAmount: 1500000,
        arrivalTime: '14:30',
        status: 'confirmed',
        paymentStatus: 'Chưa thanh toán',
      },
      {
        id: 'AN-261108-03',
        guestName: 'Lê Thu Hà',
        phone: '0945 884 302',
        email: 'ha.le@example.com',
        roomNumber: '401',
        roomType: 'Suite',
        checkIn: '14/11',
        checkOut: '15/11',
        nights: 1,
        guestsCount: '2 người lớn',
        depositAmount: 1450000,
        totalAmount: 1450000,
        arrivalTime: '15:00',
        status: 'confirmed',
        paymentStatus: 'Đã thanh toán 100%',
      },
    ],

    // 7. Yêu cầu dịch vụ phòng (Service Requests)
    serviceRequests: [
      {
        id: 'SR-001',
        roomNumber: '303',
        guestName: 'Nguyễn Minh Anh',
        serviceName: 'Thay bộ chăn ga gối lụa mới',
        quantity: '1 bộ',
        time: '10:15',
        status: 'doing',
        statusLabel: 'Đang xử lý',
        notes: 'Thay trước 12:00',
      },
      {
        id: 'SR-002',
        roomNumber: '202',
        guestName: 'Đặng Thu Thảo',
        serviceName: 'Set Trà chiều & Bánh ngọt Hội An',
        quantity: '2 phần',
        time: '14:30',
        status: 'pending',
        statusLabel: 'Chờ giao món',
        notes: 'Mang lên ban công phòng',
      },
    ],

    // 8. Nhiệm vụ buồng phòng (Housekeeping Tasks)
    housekeepingTasks: [
      {
        id: 'TSK-01',
        roomNumber: '102',
        taskType: 'Dọn phòng sau trả & thay ga gối',
        assignee: 'Nguyễn Hương (Trưởng nhóm)',
        priority: 'Cao',
        status: 'doing',
      },
      {
        id: 'TSK-02',
        roomNumber: '303',
        taskType: 'Dọn phòng định kỳ & bổ sung nước',
        assignee: 'Trần Hoa',
        priority: 'Bình thường',
        status: 'doing',
      },
      {
        id: 'TSK-03',
        roomNumber: '403',
        taskType: 'Khử khuẩn & kiểm tra minibar',
        assignee: 'Trần Hoa',
        priority: 'Cao',
        status: 'done',
      },
    ],

    // 9. Hóa đơn chứng từ (Invoices)
    invoices: [
      {
        id: 'INV-2611-001',
        guestName: 'Nguyễn Minh Anh',
        roomNumber: '303',
        date: '14/11/2026',
        roomFee: 2700000,
        serviceFee: 220000,
        total: 2920000,
        status: 'unpaid',
      },
      {
        id: 'INV-2611-002',
        guestName: 'Trần Quốc Huy',
        roomNumber: '205',
        date: '14/11/2026',
        roomFee: 1500000,
        serviceFee: 0,
        total: 1500000,
        status: 'paid',
      },
      {
        id: 'INV-2611-003',
        guestName: 'Lê Thu Hà',
        roomNumber: '401',
        date: '14/11/2026',
        roomFee: 1450000,
        serviceFee: 350000,
        total: 1800000,
        status: 'paid',
      },
    ],

    // 10. Danh bạ khách hàng lưu trú (Customers)
    customers: [
      {
        id: 'KH-001',
        name: 'Nguyễn Minh Anh',
        phone: '0903 456 789',
        email: 'minhanh.nguyen@gmail.com',
        cccd: '048•••••••321',
        nationality: 'Việt Nam',
        tier: 'Vàng (Gold)',
        points: 320,
        totalSpent: 8400000,
        stayCount: 3,
      },
      {
        id: 'KH-002',
        name: 'Trần Quốc Huy',
        phone: '0978 123 912',
        email: 'huy.tran@example.com',
        cccd: '079•••••••654',
        nationality: 'Việt Nam',
        tier: 'Bạc (Silver)',
        points: 150,
        totalSpent: 3000000,
        stayCount: 1,
      },
      {
        id: 'KH-003',
        name: 'Lê Thu Hà',
        phone: '0945 884 302',
        email: 'ha.le@example.com',
        cccd: '001•••••••819',
        nationality: 'Việt Nam',
        tier: 'Kim Cương (Diamond)',
        points: 620,
        totalSpent: 16500000,
        stayCount: 6,
      },
    ],

    // 11. Kiểm kê Minibar & Tài sản phòng (Minibar)
    minibarItems: [
      { id: 'MB-01', name: 'Nước khoáng thiên nhiên Alba 500ml', category: 'Nước uống', price: 0, minStock: 2, unit: 'Chai' },
      { id: 'MB-02', name: 'Bia thủ công Hội An Pale Ale', category: 'Đồ có cồn', price: 65000, minStock: 2, unit: 'Lon' },
      { id: 'MB-03', name: 'Nước ngọt Coca-Cola Zero', category: 'Nước ngọt', price: 35000, minStock: 2, unit: 'Lon' },
      { id: 'MB-04', name: 'Hạt điều rang củi Hội An 150g', category: 'Snack', price: 85000, minStock: 1, unit: 'Hũ' },
      { id: 'MB-05', name: 'Bộ ấm chén gốm Thanh Hà cao cấp', category: 'Tài sản', price: 450000, minStock: 1, unit: 'Bộ' },
      { id: 'MB-06', name: 'Máy sấy tóc ion âm Panasonic', category: 'Thiết bị', price: 850000, minStock: 1, unit: 'Cái' },
    ],

    // 12. Danh sách nhân sự khách sạn (Staff)
    staffList: [
      {
        id: 'NV-001',
        name: 'Mai Linh',
        email: 'linh@example.com',
        department: 'Lễ tân',
        roleTitle: 'Lễ tân · Ca trực chính',
        shiftToday: '07:00–15:00',
        status: 'Đang trong ca',
        accountRole: 'Lễ tân',
        personaKey: 'linh',
      },
      {
        id: 'NV-002',
        name: 'Trần An',
        email: 'an@example.com',
        department: 'Ban Giám Đốc',
        roleTitle: 'Chủ khách sạn · Quản trị',
        shiftToday: 'Toàn thời gian',
        status: 'Đang trong ca',
        accountRole: 'Quản trị viên (Admin)',
        personaKey: 'an',
      },
      {
        id: 'NV-003',
        name: 'Nguyễn Hương',
        email: 'huong@example.com',
        department: 'Buồng phòng',
        roleTitle: 'Tổ trưởng Buồng phòng',
        shiftToday: '07:00–15:00',
        status: 'Đang trong ca',
        accountRole: 'Buồng phòng',
        personaKey: 'huong',
      },
      {
        id: 'NV-004',
        name: 'Lê Ngọc',
        email: 'ngoc@example.com',
        department: 'Kế toán',
        roleTitle: 'Kế toán & Thu ngân',
        shiftToday: '08:00–17:00',
        status: 'Đang trong ca',
        accountRole: 'Kế toán',
        personaKey: 'ngoc',
      },
    ],

    // 13. Phân quyền vai trò (Role Permissions)
    rolePermissions: {
      an: {
        name: 'Trần An',
        roleTitle: 'Chủ khách sạn (Toàn quyền Admin)',
        badgeClass: 'bg-dark text-white',
        avatarText: 'TA',
        allowedTabs: ['overview', 'bookings', 'rooms', 'housekeeping', 'checkinout', 'service-requests', 'invoices', 'customers', 'services', 'promotions', 'reviews', 'reports', 'room-types', 'minibar', 'staff', 'accounts'],
        defaultTab: 'overview',
        description: 'Toàn quyền kiểm soát tất cả 16 chức năng của cả trang web.',
      },
      linh: {
        name: 'Mai Linh',
        roleTitle: 'Lễ tân · Ca trực chính',
        badgeClass: 'bg-success text-white',
        avatarText: 'ML',
        allowedTabs: ['overview', 'bookings', 'rooms', 'checkinout', 'housekeeping', 'service-requests', 'invoices', 'customers', 'minibar'],
        defaultTab: 'overview',
        description: 'Quản lý tiếp đón khách, đặt phòng tại quầy, nhận/trả phòng và dịch vụ phòng.',
      },
      huong: {
        name: 'Nguyễn Hương',
        roleTitle: 'Tổ trưởng Buồng phòng',
        badgeClass: 'bg-warning text-dark',
        avatarText: 'NH',
        allowedTabs: ['housekeeping', 'rooms', 'minibar'],
        defaultTab: 'housekeeping',
        description: 'Chuyên trách hoạt động buồng phòng, sơ đồ trạng thái phòng và kiểm kê minibar.',
      },
      ngoc: {
        name: 'Lê Ngọc',
        roleTitle: 'Kế toán & Thu ngân',
        badgeClass: 'bg-info text-dark',
        avatarText: 'LN',
        allowedTabs: ['invoices', 'reports', 'bookings', 'customers', 'minibar'],
        defaultTab: 'invoices',
        description: 'Quản lý thu chi, hóa đơn thanh toán, công nợ và báo cáo tài chính doanh thu.',
      },
    },

    // 14. Pending Booking State (Khi khách bấm đặt phòng chuẩn bị thanh toán)
    pendingBooking: null,
  }),

  getters: {
    currentPersonaInfo(state) {
      return state.rolePermissions[state.activePersona] || state.rolePermissions.linh;
    },
    myGuestBookings(state) {
      return state.bookings.filter(b => b.guestName.toLowerCase().includes(state.guestName.toLowerCase()) || b.phone === state.guestPhone);
    },
  },

  actions: {
    // -------------------------------------------------------------
    // GUEST ACTIONS
    // -------------------------------------------------------------
    loginGuest(name = 'Nguyễn Minh Anh', phone = '0903 456 789') {
      this.isGuestLoggedIn = true;
      this.guestName = name;
      this.guestPhone = phone;
    },
    logoutGuest() {
      this.isGuestLoggedIn = false;
    },
    createGuestBooking(bookingData) {
      const id = 'RB-' + Math.floor(100000 + Math.random() * 900000);
      const newBooking = {
        id,
        guestName: bookingData.guestName || this.guestName,
        phone: bookingData.phone || this.guestPhone,
        email: bookingData.email || this.guestEmail,
        roomNumber: bookingData.roomNumber || '101',
        roomType: bookingData.roomType || 'Deluxe',
        checkIn: bookingData.checkIn,
        checkOut: bookingData.checkOut,
        nights: bookingData.nights || 2,
        guestsCount: '2 người lớn',
        depositAmount: bookingData.totalAmount || 1800000,
        totalAmount: bookingData.totalAmount || 1800000,
        arrivalTime: '14:00',
        status: 'confirmed',
        paymentStatus: 'Đã thanh toán Online qua VietQR',
      };
      this.bookings.unshift(newBooking);

      // Cập nhật điểm thưởng hội viên (100k = 1 điểm)
      const earned = Math.floor((bookingData.totalAmount || 1800000) / 100000);
      this.guestLoyaltyPoints += earned;

      // Cập nhật trạng thái phòng nếu có xếp phòng
      const room = this.rooms.find(r => r.number === newBooking.roomNumber);
      if (room && room.status === 'available') {
        room.status = 'occupied';
        room.guestName = newBooking.guestName;
        room.stayNote = `Trả ${newBooking.checkOut}`;
      }

      return newBooking;
    },
    cancelGuestBooking(bookingId) {
      const b = this.bookings.find(item => item.id === bookingId);
      if (b) {
        b.status = 'cancelled';
        const room = this.rooms.find(r => r.number === b.roomNumber);
        if (room && room.guestName === b.guestName) {
          room.status = 'available';
          room.guestName = '';
          room.stayNote = 'Sẵn sàng đón khách';
        }
      }
    },
    redeemLoyaltyPoints(points) {
      if (this.guestLoyaltyPoints >= points) {
        this.guestLoyaltyPoints -= points;
        return true;
      }
      return false;
    },

    // -------------------------------------------------------------
    // PMS / STAFF ACTIONS
    // -------------------------------------------------------------
    switchStaffPersona(personaKey) {
      if (this.rolePermissions[personaKey]) {
        this.activePersona = personaKey;
        const defaultTab = this.rolePermissions[personaKey].defaultTab;
        if (!this.rolePermissions[personaKey].allowedTabs.includes(this.activePmsTab)) {
          this.activePmsTab = defaultTab;
        }
      }
    },
    setActivePmsTab(tabId) {
      this.activePmsTab = tabId;
    },
    doCheckIn(bookingId) {
      const b = this.bookings.find(item => item.id === bookingId);
      if (b) {
        b.status = 'staying';
        const room = this.rooms.find(r => r.number === b.roomNumber);
        if (room) {
          room.status = 'occupied';
          room.guestName = b.guestName;
          room.stayNote = `Trả ${b.checkOut}`;
        }
      }
    },
    doCheckOut(bookingId) {
      const b = this.bookings.find(item => item.id === bookingId);
      if (b) {
        b.status = 'checked_out';
        const room = this.rooms.find(r => r.number === b.roomNumber);
        if (room) {
          room.status = 'cleaning';
          room.guestName = 'Chờ dọn sau trả phòng';
          room.stayNote = 'Trước 14:00';
        }
      }
    },
    updateRoomStatus(roomNumber, newStatus) {
      const room = this.rooms.find(r => r.number === roomNumber);
      if (room) {
        room.status = newStatus;
        if (newStatus === 'available') {
          room.guestName = '';
          room.stayNote = 'Sẵn sàng đón khách';
        }
      }
    },
    toggleHousekeepingTask(taskId) {
      const t = this.housekeepingTasks.find(item => item.id === taskId);
      if (t) {
        t.status = t.status === 'done' ? 'doing' : 'done';
      }
    },
    settleInvoice(invoiceId) {
      const inv = this.invoices.find(item => item.id === invoiceId);
      if (inv) {
        inv.status = 'paid';
      }
    },
    addServiceRequest(newReq) {
      this.serviceRequests.unshift(newReq);
    },
  },
});
