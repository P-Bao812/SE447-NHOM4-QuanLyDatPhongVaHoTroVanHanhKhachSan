import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/loai-phong',
    component: () => import('../components/NV/LoaiPhong/LoaiPhong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-loai-phong',
    component: () => import('../components/NV/LoaiPhong/LoaiPhong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/tai-khoan',
    component: () => import('../components/NV/TaiKhoan/TaiKhoanPhanQuyen.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-tai-khoan',
    component: () => import('../components/NV/TaiKhoan/TaiKhoanPhanQuyen.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/nhan-vien',
    component: () => import('../components/NV/NhanVien/NhanVien.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-nhan-vien',
    component: () => import('../components/NV/NhanVien/NhanVien.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/yeu-cau-dich-vu',
    component: () => import('../components/NV/YeuCauDichVu/YeuCauDichVu.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-yeu-cau-dich-vu',
    component: () => import('../components/NV/YeuCauDichVu/YeuCauDichVu.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/khuyen-mai',
    component: () => import('../components/NV/KhuyenMai/KhuyenMai.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-khuyen-mai',
    component: () => import('../components/NV/KhuyenMai/KhuyenMai.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/promotions',
    component: () => import('../components/NV/KhuyenMai/KhuyenMai.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/minibar-tai-san',
    component: () => import('../components/NV/MinibarTaiSan/MinibarTaiSan.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/quan-ly-minibar',
    component: () => import('../components/NV/MinibarTaiSan/MinibarTaiSan.vue'),
    meta: { layout: 'guest' },
  },

  {
    path: '/buong-phong',
    component: () => import('../components/NV/BuongPhong/BuongPhong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/customer',
    component: () => import('../components/NV/BuongPhong/BuongPhong.vue'),
    meta: { layout: 'guest' },
  },
  // Giao diện khách hàng
  {
    path: '/',
    component: () => import('../components/guest/index_home.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/tien-ich-phong',
    component: () => import('../components/guest/DichVu/index_dich_vu_phong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/services',
    component: () => import('../components/guest/DichVu/index_dich_vu_phong.vue'),
    meta: { layout: 'guest' },
  },
  {
    path: '/dich-vu-phong',
    component: () => import('../components/guest/DichVu/index_dich_vu_phong.vue'),
    meta: { layout: 'guest' },
  },
]
const router = createRouter({
  history: createWebHistory(),
  routes: routes,
})

export default router
