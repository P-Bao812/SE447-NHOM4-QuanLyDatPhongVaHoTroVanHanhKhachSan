import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // 1. Phân hệ Xác thực & Tài khoản (Public & Guest Only)
  {
    path: '/login',
    name: 'Login',
    component: () => import('../components/auth/Login.vue'),
    meta: { layout: 'guest', guestOnly: true },
  },
  {
    path: '/dang-nhap',
    name: 'DangNhap',
    component: () => import('../components/auth/Login.vue'),
    meta: { layout: 'guest', guestOnly: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../components/Dang_Ky.vue'),
    meta: { layout: 'guest', guestOnly: true },
  },
  {
    path: '/dang-ky',
    name: 'DangKy',
    component: () => import('../components/Dang_Ky.vue'),
    meta: { layout: 'guest', guestOnly: true },
  },

  // 2. Phân hệ Quản trị / Quản lý PMS
  {
    path: '/admin',
    redirect: '/admin/dashboard',
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('../components/NV/index.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Quản lý' },
  },
  {
    path: '/admin/rooms',
    name: 'AdminRooms',
    component: () => import('../views/RoomManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Quản lý' },
  },
  {
    path: '/admin/room-map',
    name: 'AdminRoomMap',
    component: () => import('../views/RoomMapView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Admin'] },
  },
  {
    path: '/admin/services',
    name: 'AdminServices',
    component: () => import('../views/ServiceManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Quản lý' },
  },
  {
    path: '/admin/bookings',
    name: 'AdminBookings',
    component: () => import('../views/BookingManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân'] },
  },
  {
    path: '/admin/invoices',
    name: 'AdminInvoices',
    component: () => import('../views/InvoiceManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân'] },
  },
  {
    path: '/admin/customers',
    name: 'AdminCustomers',
    component: () => import('../views/CustomerManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân'] },
  },
  {
    path: '/admin/check-in-out',
    name: 'AdminCheckInOut',
    component: () => import('../views/CheckInOutView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân'] },
  },
  {
    path: '/admin/reports',
    name: 'AdminReports',
    component: () => import('../views/DashboardView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Admin'] },
  },
  {
    path: '/admin/profile',
    name: 'AdminProfile',
    component: () => import('../components/guest/ho_so_ca_nhan.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Admin'] },
  },
  {
    path: '/reports',
    name: 'Reports',
    component: () => import('../views/DashboardView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Admin'] },
  },
  {
    path: '/customer',
    name: 'CustomerManagement',
    component: () => import('../views/CustomerManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân'] },
  },

  // 3. Phân hệ Lễ tân (Front Desk)
  {
    path: '/receptionist/front-desk',
    name: 'ReceptionistFrontDesk',
    component: () => import('../components/NV/FrontDesk.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Lễ tân' },
  },
  {
    path: '/receptionist/check-in-out',
    name: 'ReceptionistCheckInOut',
    component: () => import('../views/CheckInOutView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },
  {
    path: '/receptionist/room-map',
    name: 'ReceptionistRoomMap',
    component: () => import('../views/RoomMapView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },
  {
    path: '/receptionist/bookings',
    name: 'ReceptionistBookings',
    component: () => import('../views/BookingManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },
  {
    path: '/receptionist/booking',
    name: 'ReceptionistBooking',
    component: () => import('../views/BookingView.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Lễ tân' },
  },
  {
    path: '/receptionist/cancellations',
    name: 'ReceptionistCancellations',
    component: () => import('../components/guest/lich_su_dat_phong.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Lễ tân' },
  },
  {
    path: '/receptionist/invoices',
    name: 'ReceptionistInvoices',
    component: () => import('../views/InvoiceManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },
  {
    path: '/receptionist/customers',
    name: 'ReceptionistCustomers',
    component: () => import('../views/CustomerManagementView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },
  {
    path: '/receptionist/profile',
    name: 'ReceptionistProfile',
    component: () => import('../components/guest/ho_so_ca_nhan.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Lễ tân', 'Quản lý'] },
  },

  // 4. Phân hệ Buồng phòng (Housekeeping)
  {
    path: '/housekeeping',
    name: 'HousekeepingTasks',
    component: () => import('../components/NV/Housekeeping.vue'),
    meta: { layout: 'nv', requiresAuth: true, role: 'Buồng phòng' },
  },
  {
    path: '/housekeeping/room-map',
    name: 'HousekeepingRoomMap',
    component: () => import('../views/RoomMapView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Buồng phòng', 'Quản lý'] },
  },
  {
    path: '/housekeeping/profile',
    name: 'HousekeepingProfile',
    component: () => import('../components/guest/ho_so_ca_nhan.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Buồng phòng', 'Quản lý'] },
  },
  {
    path: '/room-map',
    name: 'RoomMap',
    component: () => import('../views/RoomMapView.vue'),
    meta: { layout: 'nv', requiresAuth: true, roles: ['Quản lý', 'Lễ tân', 'Buồng phòng'] },
  },

  // 5. Giao diện Cổng Khách hàng (Public)
  {
    path: '/',
    name: 'Home',
    component: () => import('../components/guest/index_home.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/tien-ich',
    name: 'Amenities',
    component: () => import('../components/guest/index_tien_ich_trai_nghiem.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/services',
    name: 'Services',
    component: () => import('../components/guest/index_dich_vu_phong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/promotions',
    name: 'Promotions',
    component: () => import('../components/guest/uu_dai_va_diem_thuong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/reviews',
    name: 'Reviews',
    component: () => import('../components/guest/danh_gia.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/my-bookings',
    name: 'MyBookings',
    component: () => import('../components/guest/lich_su_dat_phong.vue'),
    meta: { layout: 'guest', requiresAuth: true },
  },

  {
    path: '/booking',
    name: 'Booking',
    component: () => import('../views/BookingView.vue'),
    meta: { layout: 'guest', requiresAuth: true },
  },

  {
    path: '/payment',
    name: 'Payment',
    component: () => import('../components/guest/thanh_toan_online.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../components/guest/ho_so_ca_nhan.vue'),
    meta: { layout: 'guest', requiresAuth: true },
  },

  // Bắt tất cả đường dẫn không tồn tại và chuyển về trang chủ hoặc login
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation Guard kiểm tra Authentication và Role
router.beforeEach((to) => {
  const token = localStorage.getItem('access_token')
  let user = null

  try {
    const userStr = localStorage.getItem('user')
    if (userStr) {
      user = JSON.parse(userStr)
    }
  } catch {
    // user = null
  }

  // 1. Kiểm tra route yêu cầu đăng nhập
  if (to.meta.requiresAuth && !token) {
    return {
      path: '/dang-nhap',
      query: { redirect: to.fullPath },
    }
  }

  // 2. Nếu đã đăng nhập mà truy cập trang guestOnly (/dang-nhap, /dang-ky)
  if (to.meta.guestOnly && token) {
    const vaiTro = (user?.vaiTro || '').toLowerCase().trim()
    if (vaiTro === 'quản lý' || vaiTro === 'admin') {
      return '/admin/dashboard'
    } else if (vaiTro === 'lễ tân' || vaiTro === 'receptionist') {
      return '/receptionist/front-desk'
    } else if (vaiTro === 'buồng phòng' || vaiTro === 'housekeeping') {
      return '/housekeeping'
    } else {
      return '/'
    }
  }

  // 3. Kiểm tra phân quyền vai trò (Role-based Authorization)
  if ((to.meta.role || to.meta.roles) && user) {
    const userRole = (user.vaiTro || '').toLowerCase().trim()

    // Quản lý/Admin có quyền truy cập tất cả phân hệ quản trị
    if (userRole === 'quản lý' || userRole === 'admin') {
      return true
    }

    const allowedRoles = []
    if (to.meta.role) {
      allowedRoles.push((to.meta.role || '').toLowerCase().trim())
    }
    if (Array.isArray(to.meta.roles)) {
      to.meta.roles.forEach((r) => allowedRoles.push((r || '').toLowerCase().trim()))
    }

    if (!allowedRoles.includes(userRole)) {
      // Chuyển hướng về trang nghiệp vụ đúng vai trò của họ
      if (userRole === 'lễ tân' || userRole === 'receptionist') {
        return '/receptionist/front-desk'
      } else if (userRole === 'buồng phòng' || userRole === 'housekeeping') {
        return '/housekeeping'
      } else {
        return '/'
      }
    }
  }

  return true
})

export default router
