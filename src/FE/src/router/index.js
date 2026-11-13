import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  // Giao diện cho nhân viên
  {
    path: '/customer',
    component: () => import('../components/NV/index.vue'),
    meta: { layout: 'nv' },
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
]
const router = createRouter({
  history: createWebHistory(),
  routes: routes,
})

export default router
