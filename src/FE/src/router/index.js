import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // Giao diện cho nhân viên / Quản lý PMS
  {
    path: '/customer',
    component: () => import('../components/NV/index.vue'),
    meta: { layout: 'nv' },
  },
  {
    path: '/admin',
    redirect: '/customer',
  },
  // Giao diện khách hàng
  {
    path: '/',
    component: () => import('../components/guest/index_home.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/tien-ich',
    component: () => import('../components/guest/index_tien_ich_trai_nghiem.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/services',
    component: () => import('../components/guest/index_dich_vu_phong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/promotions',
    component: () => import('../components/guest/uu_dai_va_diem_thuong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/reviews',
    component: () => import('../components/guest/danh_gia.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/my-bookings',
    component: () => import('../components/guest/lich_su_dat_phong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/payment',
    component: () => import('../components/guest/thanh_toan_online.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/profile',
    component: () => import('../components/guest/ho_so_ca_nhan.vue'),
    meta: { layout: 'guest' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes: routes,
})

export default router
