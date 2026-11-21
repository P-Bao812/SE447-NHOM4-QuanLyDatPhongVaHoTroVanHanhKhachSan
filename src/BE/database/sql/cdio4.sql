/*
 Navicat Premium Data Transfer

 Source Server         : ProjectCDIO
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : cdio4

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 21/11/2026 19:41:52
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_expiration_index`(`expiration` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache
-- ----------------------------

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE,
  INDEX `cache_locks_expiration_index`(`expiration` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for chi_tiet_buong_phong
-- ----------------------------
DROP TABLE IF EXISTS `chi_tiet_buong_phong`;
CREATE TABLE `chi_tiet_buong_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maPhieu` bigint UNSIGNED NOT NULL,
  `noiDungCongViec` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghiChu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of chi_tiet_buong_phong
-- ----------------------------

-- ----------------------------
-- Table structure for chi_tiet_dat_phong
-- ----------------------------
DROP TABLE IF EXISTS `chi_tiet_dat_phong`;
CREATE TABLE `chi_tiet_dat_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maDatPhong` bigint UNSIGNED NOT NULL,
  `maPhong` bigint UNSIGNED NOT NULL,
  `giaPhongApDung` bigint NOT NULL,
  `soLuongKhach` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of chi_tiet_dat_phong
-- ----------------------------

-- ----------------------------
-- Table structure for chi_tiet_hoa_don
-- ----------------------------
DROP TABLE IF EXISTS `chi_tiet_hoa_don`;
CREATE TABLE `chi_tiet_hoa_don`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maHoaDon` bigint UNSIGNED NOT NULL,
  `noiDung` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `soLuong` int NOT NULL,
  `donGia` bigint NOT NULL,
  `thanhTien` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of chi_tiet_hoa_don
-- ----------------------------

-- ----------------------------
-- Table structure for danh_gia
-- ----------------------------
DROP TABLE IF EXISTS `danh_gia`;
CREATE TABLE `danh_gia`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maKhachHang` bigint NOT NULL,
  `maLuuTru` bigint NOT NULL,
  `diemDanhGia` int NOT NULL,
  `noiDung` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngayDanhGia` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of danh_gia
-- ----------------------------

-- ----------------------------
-- Table structure for dat_phong
-- ----------------------------
DROP TABLE IF EXISTS `dat_phong`;
CREATE TABLE `dat_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maKhachHang` bigint UNSIGNED NOT NULL,
  `maNhanVien` bigint UNSIGNED NOT NULL,
  `ngayDat` datetime NOT NULL,
  `ngayNhanPhong` datetime NOT NULL,
  `ngayTraPhong` datetime NOT NULL,
  `soLuongKhach` int NOT NULL,
  `tienCoc` bigint NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dat_phong
-- ----------------------------
INSERT INTO `dat_phong` VALUES (1, 1, 1, '2026-11-13 18:16:03', '2026-11-14 00:00:00', '2026-11-17 00:00:00', 2, 500000, 'Đã cọc', '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for dich_vu
-- ----------------------------
DROP TABLE IF EXISTS `dich_vu`;
CREATE TABLE `dich_vu`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenDichVu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moTa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `donGia` bigint NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dich_vu
-- ----------------------------

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for ghi_nhan_tai_san
-- ----------------------------
DROP TABLE IF EXISTS `ghi_nhan_tai_san`;
CREATE TABLE `ghi_nhan_tai_san`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLuuTru` bigint NOT NULL,
  `maTaiSan` bigint NOT NULL,
  `loaiPhatSinh` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoiGian` datetime NOT NULL,
  `soTienBoiThuong` bigint NOT NULL,
  `ghiChu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ghi_nhan_tai_san
-- ----------------------------

-- ----------------------------
-- Table structure for hoa_don
-- ----------------------------
DROP TABLE IF EXISTS `hoa_don`;
CREATE TABLE `hoa_don`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLuuTru` bigint UNSIGNED NOT NULL,
  `ngayLap` datetime NOT NULL,
  `tongTien` bigint NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hoa_don
-- ----------------------------
INSERT INTO `hoa_don` VALUES (1, 1, '2026-11-13 18:16:03', 2400000, 'Chờ thanh toán', '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for hoan_tien
-- ----------------------------
DROP TABLE IF EXISTS `hoan_tien`;
CREATE TABLE `hoan_tien`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maThanhToan` bigint UNSIGNED NOT NULL,
  `soTien` bigint NOT NULL,
  `lyDo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoiGian` datetime NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hoan_tien
-- ----------------------------

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for khach_hang
-- ----------------------------
DROP TABLE IF EXISTS `khach_hang`;
CREATE TABLE `khach_hang`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maTaiKhoan` bigint UNSIGNED NOT NULL,
  `hoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cccd` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `soDienThoai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `diaChi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `khach_hang_cccd_unique`(`cccd` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of khach_hang
-- ----------------------------
INSERT INTO `khach_hang` VALUES (1, 3, 'Nguyễn Văn Minh', '001203001234', 'minhnguyen@gmail.com', '0912345678', '123 Lê Lợi, Quận 1, TP.HCM', '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for khuyen_mai
-- ----------------------------
DROP TABLE IF EXISTS `khuyen_mai`;
CREATE TABLE `khuyen_mai`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenKhuyenMai` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `loaiKhuyenMai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `giaTriGiam` int NOT NULL,
  `ngayBatDau` datetime NOT NULL,
  `ngayKetThuc` datetime NOT NULL,
  `dieuKienApDung` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of khuyen_mai
-- ----------------------------

-- ----------------------------
-- Table structure for loai_phong
-- ----------------------------
DROP TABLE IF EXISTS `loai_phong`;
CREATE TABLE `loai_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenLoai` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moTa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `giaTieuChuan` bigint NOT NULL,
  `sucChuaToiDa` int NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of loai_phong
-- ----------------------------
INSERT INTO `loai_phong` VALUES (1, 'Single Room', 'Phòng đơn tiêu chuẩn cho 1 người', 500000, 1, 'Kinh doanh', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `loai_phong` VALUES (2, 'Double Room', 'Phòng đôi cho 2 người', 800000, 2, 'Kinh doanh', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `loai_phong` VALUES (3, 'VIP Suite', 'Phòng cao cấp đầy đủ tiện nghi sang trọng', 2000000, 4, 'Kinh doanh', '2026-11-13 18:16:02', '2026-11-13 18:16:02');

-- ----------------------------
-- Table structure for loai_phong_tien_nghi
-- ----------------------------
DROP TABLE IF EXISTS `loai_phong_tien_nghi`;
CREATE TABLE `loai_phong_tien_nghi`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLoaiPhong` bigint UNSIGNED NOT NULL,
  `maTienNghi` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of loai_phong_tien_nghi
-- ----------------------------

-- ----------------------------
-- Table structure for luu_tru
-- ----------------------------
DROP TABLE IF EXISTS `luu_tru`;
CREATE TABLE `luu_tru`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maChiTiet` bigint UNSIGNED NOT NULL,
  `thoiGianNhan` datetime NOT NULL,
  `thoiGianTra` datetime NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of luu_tru
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 99 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (66, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (67, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` VALUES (68, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` VALUES (69, '2026_11_13_094629_vai__tro', 1);
INSERT INTO `migrations` VALUES (70, '2026_11_13_095358_quyen', 1);
INSERT INTO `migrations` VALUES (71, '2026_11_13_095359_vai__tro__quyen', 1);
INSERT INTO `migrations` VALUES (72, '2026_11_13_100115_tai__khoan', 1);
INSERT INTO `migrations` VALUES (73, '2026_11_13_100117_khach__hang', 1);
INSERT INTO `migrations` VALUES (74, '2026_11_13_100118_nhan__vien', 1);
INSERT INTO `migrations` VALUES (75, '2026_11_13_102421_loai__phong', 1);
INSERT INTO `migrations` VALUES (76, '2026_11_13_102423_phong', 1);
INSERT INTO `migrations` VALUES (77, '2026_11_13_102424_tien__nghi', 1);
INSERT INTO `migrations` VALUES (78, '2026_11_13_102426_loai__phong__tien__nghi', 1);
INSERT INTO `migrations` VALUES (79, '2026_11_13_103847_khuyen__mai', 1);
INSERT INTO `migrations` VALUES (80, '2026_11_13_103849_dat__phong', 1);
INSERT INTO `migrations` VALUES (81, '2026_11_13_103850_chi__tiet__dat__phong', 1);
INSERT INTO `migrations` VALUES (82, '2026_11_13_105156_luu__tru', 1);
INSERT INTO `migrations` VALUES (83, '2026_11_13_105158_phieu__nhan__tra__phong', 1);
INSERT INTO `migrations` VALUES (84, '2026_11_13_105159_phieu__buong__phong', 1);
INSERT INTO `migrations` VALUES (85, '2026_11_13_105200_chi__tiet__buong__phong', 1);
INSERT INTO `migrations` VALUES (86, '2026_11_13_110242_hoa__don', 1);
INSERT INTO `migrations` VALUES (87, '2026_11_13_110243_chi__tiet__hoa__don', 1);
INSERT INTO `migrations` VALUES (88, '2026_11_13_110245_thanh__toan', 1);
INSERT INTO `migrations` VALUES (89, '2026_11_13_110246_hoan__tien', 1);
INSERT INTO `migrations` VALUES (90, '2026_11_13_111542_dich__vu', 1);
INSERT INTO `migrations` VALUES (91, '2026_11_13_111543_yeu__cau__dich__vu', 1);
INSERT INTO `migrations` VALUES (92, '2026_11_13_111544_san__pham__minibar', 1);
INSERT INTO `migrations` VALUES (93, '2026_11_13_111546_phat__sinh__minibar', 1);
INSERT INTO `migrations` VALUES (94, '2026_11_13_112742_tai__san', 1);
INSERT INTO `migrations` VALUES (95, '2026_11_13_112743_ghi__nhan__tai__san', 1);
INSERT INTO `migrations` VALUES (96, '2026_11_13_112744_danh__gia', 1);
INSERT INTO `migrations` VALUES (97, '2026_11_13_112746_phan__hoi', 1);
INSERT INTO `migrations` VALUES (98, '2026_11_13_155655_create_personal_access_tokens_table', 1);

-- ----------------------------
-- Table structure for nhan_vien
-- ----------------------------
DROP TABLE IF EXISTS `nhan_vien`;
CREATE TABLE `nhan_vien`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maTaiKhoan` bigint UNSIGNED NOT NULL,
  `hoTen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngaySinh` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cccd` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `soDienThoai` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chucVu` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `nhan_vien_cccd_unique`(`cccd` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of nhan_vien
-- ----------------------------
INSERT INTO `nhan_vien` VALUES (1, 1, 'Trần Văn Quản Lý', '1990-05-15', '001190005555', '0988777666', 'Quản lý', 'Đang làm', '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token` ASC) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type` ASC, `tokenable_id` ASC) USING BTREE,
  INDEX `personal_access_tokens_expires_at_index`(`expires_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for phan_hoi
-- ----------------------------
DROP TABLE IF EXISTS `phan_hoi`;
CREATE TABLE `phan_hoi`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maDanhGia` bigint NOT NULL,
  `maNhanVien` bigint NOT NULL,
  `noiDung` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngayPhanHoi` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of phan_hoi
-- ----------------------------

-- ----------------------------
-- Table structure for phat_sinh_minibar
-- ----------------------------
DROP TABLE IF EXISTS `phat_sinh_minibar`;
CREATE TABLE `phat_sinh_minibar`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLuuTru` bigint NOT NULL,
  `maSanPham` bigint NOT NULL,
  `soLuong` int NOT NULL,
  `donGiaApDung` bigint NOT NULL,
  `thoiGian` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of phat_sinh_minibar
-- ----------------------------

-- ----------------------------
-- Table structure for phieu_buong_phong
-- ----------------------------
DROP TABLE IF EXISTS `phieu_buong_phong`;
CREATE TABLE `phieu_buong_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maPhong` bigint UNSIGNED NOT NULL,
  `maNhanVien` bigint UNSIGNED NOT NULL,
  `ngayTao` datetime NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghiChu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of phieu_buong_phong
-- ----------------------------

-- ----------------------------
-- Table structure for phieu_nhan_tra_phong
-- ----------------------------
DROP TABLE IF EXISTS `phieu_nhan_tra_phong`;
CREATE TABLE `phieu_nhan_tra_phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLuuTru` bigint UNSIGNED NOT NULL,
  `maNhanVien` bigint UNSIGNED NOT NULL,
  `loaiPhieu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoiGian` datetime NOT NULL,
  `ghiChu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of phieu_nhan_tra_phong
-- ----------------------------

-- ----------------------------
-- Table structure for phong
-- ----------------------------
DROP TABLE IF EXISTS `phong`;
CREATE TABLE `phong`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLoaiPhong` bigint UNSIGNED NOT NULL,
  `tenPhong` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tang` int NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `phong_tenphong_unique`(`tenPhong` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of phong
-- ----------------------------
INSERT INTO `phong` VALUES (1, 1, 'P101', 1, 'Sẵn sàng', '2026-11-13 18:16:03', '2026-11-13 18:16:03');
INSERT INTO `phong` VALUES (2, 2, 'P201', 2, 'Sẵn sàng', '2026-11-13 18:16:03', '2026-11-13 18:16:03');
INSERT INTO `phong` VALUES (3, 3, 'P301', 3, 'Sẵn sàng', '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for quyen
-- ----------------------------
DROP TABLE IF EXISTS `quyen`;
CREATE TABLE `quyen`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenQuyen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moTa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of quyen
-- ----------------------------

-- ----------------------------
-- Table structure for san_pham_minibar
-- ----------------------------
DROP TABLE IF EXISTS `san_pham_minibar`;
CREATE TABLE `san_pham_minibar`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenSanPham` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `donGia` bigint NOT NULL,
  `donViTinh` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of san_pham_minibar
-- ----------------------------

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sessions_user_id_index`(`user_id` ASC) USING BTREE,
  INDEX `sessions_last_activity_index`(`last_activity` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sessions
-- ----------------------------

-- ----------------------------
-- Table structure for tai_khoan
-- ----------------------------
DROP TABLE IF EXISTS `tai_khoan`;
CREATE TABLE `tai_khoan`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenDangNhap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash_password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `trangThai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `tai_khoan_tendangnhap_unique`(`tenDangNhap` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tai_khoan
-- ----------------------------
INSERT INTO `tai_khoan` VALUES (1, 'admin', '$2y$12$cWN9jGB./vdWqAgQmMuVSuUssvfWR09V.B.OjG1H6/BIW0mqidd0K', NULL, 'Hoạt động', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `tai_khoan` VALUES (2, 'letan01', '$2y$12$tgcMf9hI5azJ2rluFXbcduMBO/hzrPWQ5FzZgSBxQn9pfEngh4hs2', NULL, 'Hoạt động', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `tai_khoan` VALUES (3, 'nguyenvanminh', '$2y$12$hNKyvCVFAibPh7pBUSlBL.kbEjwrCEb0.v7sB0jiQ0YV5BwQopkAK', NULL, 'Hoạt động', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `tai_khoan` VALUES (4, 'khachHang1', '$2y$12$1nTDc3XQ86U./npIlQd7SO6kyH3fpLgFt92Ubd59PjmTHGdqx8YJO', NULL, 'Hoạt động', '2026-11-13 18:18:28', '2026-11-13 18:18:28');

-- ----------------------------
-- Table structure for tai_san
-- ----------------------------
DROP TABLE IF EXISTS `tai_san`;
CREATE TABLE `tai_san`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maPhong` bigint NOT NULL,
  `tenTaiSan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tinhTrang` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `giaTri` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tai_san
-- ----------------------------
INSERT INTO `tai_san` VALUES (1, 1, 'Tivi Samsung 43 inch', 'Tốt', 7000000, '2026-11-13 18:16:03', '2026-11-13 18:16:03');
INSERT INTO `tai_san` VALUES (2, 1, 'Điều hòa Daikin', 'Tốt', 9000000, '2026-11-13 18:16:03', '2026-11-13 18:16:03');

-- ----------------------------
-- Table structure for thanh_toan
-- ----------------------------
DROP TABLE IF EXISTS `thanh_toan`;
CREATE TABLE `thanh_toan`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maHoaDon` bigint UNSIGNED NOT NULL,
  `soTien` bigint NOT NULL,
  `phuongThuc` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'tien mat, qr, the',
  `maGiaoDich` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `thoiGian` datetime NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `thanh_toan_magiaodich_unique`(`maGiaoDich` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of thanh_toan
-- ----------------------------

-- ----------------------------
-- Table structure for tien_nghi
-- ----------------------------
DROP TABLE IF EXISTS `tien_nghi`;
CREATE TABLE `tien_nghi`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenTienNghi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moTa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tien_nghi
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------

-- ----------------------------
-- Table structure for vai_tro
-- ----------------------------
DROP TABLE IF EXISTS `vai_tro`;
CREATE TABLE `vai_tro`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenVaiTro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moTa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vai_tro
-- ----------------------------
INSERT INTO `vai_tro` VALUES (1, 'Quản lý', 'Quản lý toàn bộ hệ thống khách sạn', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `vai_tro` VALUES (2, 'Lễ tân', 'Tiếp đón khách, làm thủ tục check-in/check-out', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `vai_tro` VALUES (3, 'Buồng phòng', 'Kiểm tra, dọn dẹp và quản lý tài sản phòng', '2026-11-13 18:16:02', '2026-11-13 18:16:02');
INSERT INTO `vai_tro` VALUES (4, 'Khách hàng', 'Khách hàng sử dụng dịch vụ đặt phòng', '2026-11-13 18:16:02', '2026-11-13 18:16:02');

-- ----------------------------
-- Table structure for vai_tro_quyen
-- ----------------------------
DROP TABLE IF EXISTS `vai_tro_quyen`;
CREATE TABLE `vai_tro_quyen`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maVaiTro` int NOT NULL,
  `maQuyen` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of vai_tro_quyen
-- ----------------------------

-- ----------------------------
-- Table structure for yeu_cau_dich_vu
-- ----------------------------
DROP TABLE IF EXISTS `yeu_cau_dich_vu`;
CREATE TABLE `yeu_cau_dich_vu`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maLuuTru` bigint UNSIGNED NOT NULL,
  `maDichVu` bigint UNSIGNED NOT NULL,
  `soLuong` int NOT NULL,
  `donGiaApDung` bigint NOT NULL,
  `ngayYeuCau` datetime NOT NULL,
  `trangThai` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ghiChu` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of yeu_cau_dich_vu
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
