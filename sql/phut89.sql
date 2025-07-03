-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 25, 2025 lúc 02:11 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `phut89`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$ti9GQxqtd3jYXB2V/RU5rup55S6Uo0lDi4yaX4KZDkCknCOx0QU1i');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(255) DEFAULT NULL,
  `hinh_anh` varchar(255) NOT NULL,
  `lien_ket` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(1) DEFAULT 1,
  `vi_tri` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`id`, `tieu_de`, `hinh_anh`, `lien_ket`, `trang_thai`, `vi_tri`) VALUES
(10, '', '6853e4e561a1d-z6686047555190_0855fc5d2b160050f2259b9808c0f11b.jpg', '', 1, 0),
(11, '', '6853e5bc56517-z6686047565387_cf79c9b4549925744985366983615610.jpg', '', 1, 0),
(12, 'Phút 89', '6853e5cebdbb3-685037f7e73bf-banner.jpg', '', 1, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc_thuoc_tinh`
--

CREATE TABLE `danhmuc_thuoc_tinh` (
  `danh_muc_id` int(11) NOT NULL,
  `thuoc_tinh_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc_thuoc_tinh`
--

INSERT INTO `danhmuc_thuoc_tinh` (`danh_muc_id`, `thuoc_tinh_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 5),
(1, 8),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 1),
(3, 6),
(3, 7),
(4, 1),
(4, 6),
(4, 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc_thuonghieu`
--

CREATE TABLE `danhmuc_thuonghieu` (
  `danh_muc_id` int(11) NOT NULL,
  `thuong_hieu_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc_thuonghieu`
--

INSERT INTO `danhmuc_thuonghieu` (`danh_muc_id`, `thuong_hieu_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3),
(2, 4),
(3, 5),
(4, 6),
(8, 1),
(8, 2),
(9, 1),
(9, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `vi_tri` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `slug`, `is_active`, `vi_tri`) VALUES
(1, 'Điện thoại', 'dien-thoai', 1, 0),
(2, 'Laptop', 'laptop', 1, 1),
(3, 'Xe máy', 'xe-may', 1, 3),
(4, 'Ô tô', 'o-to', 1, 2),
(8, 'Phụ Kiện', 'phu-kien', 1, 4),
(9, 'Máy tính bảng', 'may-tinh-bang', 1, 5),
(10, 'máy bay', 'may-bay', 1, 6);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gia_tri_thuoc_tinh`
--

CREATE TABLE `gia_tri_thuoc_tinh` (
  `san_pham_id` int(11) NOT NULL,
  `thuoc_tinh_id` int(11) NOT NULL,
  `gia_tri` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gia_tri_thuoc_tinh_bien_the`
--

CREATE TABLE `gia_tri_thuoc_tinh_bien_the` (
  `id` int(11) NOT NULL,
  `thuoc_tinh_id` int(11) NOT NULL,
  `gia_tri` varchar(100) NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL COMMENT 'Dùng cho swatch màu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `gia_tri_thuoc_tinh_bien_the`
--

INSERT INTO `gia_tri_thuoc_tinh_bien_the` (`id`, `thuoc_tinh_id`, `gia_tri`, `hinh_anh`) VALUES
(1, 1, '128GB', NULL),
(2, 1, '256GB', NULL),
(3, 1, '512GB', NULL),
(4, 1, '1TB', NULL),
(5, 2, 'Titan Tự Nhiên', 'swatch_titan_natural.jpg'),
(6, 2, 'Titan Xanh', 'swatch_titan_blue.jpg'),
(7, 2, 'Titan Trắng', 'swatch_titan_white.jpg'),
(8, 2, 'Titan Đen', 'swatch_titan_black.jpg'),
(9, 2, 'Space Gray', 'swatch_mac_gray.jpg'),
(10, 2, 'Silver', 'swatch_mac_silver.jpg'),
(11, 2, 'Starlight', 'swatch_mac_starlight.jpg'),
(12, 3, '8GB', NULL),
(13, 3, '16GB', NULL),
(16, 5, 'GPS', NULL),
(17, 5, 'GPS + Cellular', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hinh_anh_san_pham`
--

CREATE TABLE `hinh_anh_san_pham` (
  `id` int(11) NOT NULL,
  `san_pham_id` int(11) DEFAULT NULL,
  `url_hinh_anh` varchar(255) DEFAULT NULL,
  `la_anh_dai_dien` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hinh_anh_san_pham`
--

INSERT INTO `hinh_anh_san_pham` (`id`, `san_pham_id`, `url_hinh_anh`, `la_anh_dai_dien`) VALUES
(101, 1, 'iphone-15-pro-natural.jpg', 1),
(102, 1, 'iphone-15-pro-blue.jpg', 0),
(103, 1, 'iphone-15-pro-white.jpg', 0),
(104, 1, 'iphone-15-pro-black.jpg', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kho_hang`
--

CREATE TABLE `kho_hang` (
  `san_pham_id` int(11) NOT NULL,
  `so_luong_ton` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `ma_bien_the` varchar(100) DEFAULT NULL COMMENT 'SKU - Mã định danh duy nhất',
  `gia` bigint(20) NOT NULL DEFAULT 0,
  `gia_khuyen_mai` bigint(20) DEFAULT 0,
  `so_luong_ton` int(11) NOT NULL DEFAULT 0,
  `hinh_anh_id` int(11) DEFAULT NULL COMMENT 'FK đến hinh_anh_san_pham.id',
  `la_mac_dinh` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id` int(11) NOT NULL,
  `ten_san_pham` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `mo_ta_chi_tiet` text DEFAULT NULL,
  `gia_goc` bigint(20) DEFAULT NULL,
  `gia_khuyen_mai` bigint(20) DEFAULT 0,
  `so_luong_ton` int(11) NOT NULL DEFAULT 0,
  `danh_muc_id` int(11) DEFAULT NULL,
  `thuong_hieu_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `loai_san_pham` enum('simple','variable') NOT NULL DEFAULT 'simple',
  `ma_dinh_danh_duy_nhat` varchar(255) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id`, `ten_san_pham`, `slug`, `mo_ta_ngan`, `mo_ta_chi_tiet`, `gia_goc`, `gia_khuyen_mai`, `so_luong_ton`, `danh_muc_id`, `thuong_hieu_id`, `is_active`, `loai_san_pham`, `ma_dinh_danh_duy_nhat`, `luot_xem`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'Khung titan, Chip A17 Pro, camera đỉnh cao.', 'Đây là mô tả chi tiết cho iPhone 15 Pro Max...', NULL, 0, 0, 1, 1, 1, 'variable', '', 21, '2025-06-24 22:12:05', '2025-06-25 00:11:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuoc_tinh`
--

CREATE TABLE `thuoc_tinh` (
  `id` int(11) NOT NULL,
  `ten_thuoc_tinh` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuoc_tinh`
--

INSERT INTO `thuoc_tinh` (`id`, `ten_thuoc_tinh`) VALUES
(8, 'Bảo Hành'),
(4, 'CPU'),
(2, 'Dung lượng'),
(5, 'Màn hình'),
(1, 'Màu sắc'),
(7, 'Năm sản xuất'),
(3, 'RAM'),
(6, 'Động cơ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuoc_tinh_bien_the`
--

CREATE TABLE `thuoc_tinh_bien_the` (
  `id` int(11) NOT NULL,
  `ten_thuoc_tinh` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuoc_tinh_bien_the`
--

INSERT INTO `thuoc_tinh_bien_the` (`id`, `ten_thuoc_tinh`) VALUES
(1, 'Dung lượng'),
(5, 'Kết nối'),
(2, 'Màu sắc'),
(3, 'RAM');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuong_hieu`
--

CREATE TABLE `thuong_hieu` (
  `id` int(11) NOT NULL,
  `ten_thuong_hieu` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuong_hieu`
--

INSERT INTO `thuong_hieu` (`id`, `ten_thuong_hieu`, `slug`) VALUES
(1, 'Apple', 'apple'),
(2, 'Samsung', 'samsung'),
(3, 'Dell', 'dell'),
(4, 'Asus', 'asus'),
(5, 'Honda', 'honda'),
(6, 'Toyota', 'toyota'),
(10, 'Anker', 'anker');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `variant_option_values`
--

CREATE TABLE `variant_option_values` (
  `variant_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `danhmuc_thuoc_tinh`
--
ALTER TABLE `danhmuc_thuoc_tinh`
  ADD PRIMARY KEY (`danh_muc_id`,`thuoc_tinh_id`),
  ADD KEY `thuoc_tinh_id` (`thuoc_tinh_id`);

--
-- Chỉ mục cho bảng `danhmuc_thuonghieu`
--
ALTER TABLE `danhmuc_thuonghieu`
  ADD PRIMARY KEY (`danh_muc_id`,`thuong_hieu_id`),
  ADD KEY `thuong_hieu_id` (`thuong_hieu_id`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `gia_tri_thuoc_tinh`
--
ALTER TABLE `gia_tri_thuoc_tinh`
  ADD PRIMARY KEY (`san_pham_id`,`thuoc_tinh_id`),
  ADD KEY `thuoc_tinh_id` (`thuoc_tinh_id`);

--
-- Chỉ mục cho bảng `gia_tri_thuoc_tinh_bien_the`
--
ALTER TABLE `gia_tri_thuoc_tinh_bien_the`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thuoc_tinh_id` (`thuoc_tinh_id`);

--
-- Chỉ mục cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_san_pham_id_dai_dien` (`san_pham_id`,`la_anh_dai_dien`);

--
-- Chỉ mục cho bảng `kho_hang`
--
ALTER TABLE `kho_hang`
  ADD PRIMARY KEY (`san_pham_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_bien_the` (`ma_bien_the`),
  ADD KEY `san_pham_id` (`san_pham_id`),
  ADD KEY `hinh_anh_id` (`hinh_anh_id`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `ma_dinh_danh_duy_nhat` (`ma_dinh_danh_duy_nhat`),
  ADD KEY `idx_danh_muc_id` (`danh_muc_id`),
  ADD KEY `idx_thuong_hieu_id` (`thuong_hieu_id`);

--
-- Chỉ mục cho bảng `thuoc_tinh`
--
ALTER TABLE `thuoc_tinh`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_thuoc_tinh` (`ten_thuoc_tinh`);

--
-- Chỉ mục cho bảng `thuoc_tinh_bien_the`
--
ALTER TABLE `thuoc_tinh_bien_the`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_thuoc_tinh` (`ten_thuoc_tinh`);

--
-- Chỉ mục cho bảng `thuong_hieu`
--
ALTER TABLE `thuong_hieu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `variant_option_values`
--
ALTER TABLE `variant_option_values`
  ADD PRIMARY KEY (`variant_id`,`value_id`),
  ADD KEY `value_id` (`value_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `gia_tri_thuoc_tinh_bien_the`
--
ALTER TABLE `gia_tri_thuoc_tinh_bien_the`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `thuoc_tinh`
--
ALTER TABLE `thuoc_tinh`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `thuoc_tinh_bien_the`
--
ALTER TABLE `thuoc_tinh_bien_the`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `thuong_hieu`
--
ALTER TABLE `thuong_hieu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `danhmuc_thuoc_tinh`
--
ALTER TABLE `danhmuc_thuoc_tinh`
  ADD CONSTRAINT `danhmuc_thuoc_tinh_ibfk_1` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `danhmuc_thuoc_tinh_ibfk_2` FOREIGN KEY (`thuoc_tinh_id`) REFERENCES `thuoc_tinh` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `danhmuc_thuonghieu`
--
ALTER TABLE `danhmuc_thuonghieu`
  ADD CONSTRAINT `danhmuc_thuonghieu_ibfk_1` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `danhmuc_thuonghieu_ibfk_2` FOREIGN KEY (`thuong_hieu_id`) REFERENCES `thuong_hieu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `gia_tri_thuoc_tinh`
--
ALTER TABLE `gia_tri_thuoc_tinh`
  ADD CONSTRAINT `gia_tri_thuoc_tinh_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `gia_tri_thuoc_tinh_ibfk_2` FOREIGN KEY (`thuoc_tinh_id`) REFERENCES `thuoc_tinh` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `gia_tri_thuoc_tinh_bien_the`
--
ALTER TABLE `gia_tri_thuoc_tinh_bien_the`
  ADD CONSTRAINT `fk_value_to_option` FOREIGN KEY (`thuoc_tinh_id`) REFERENCES `thuoc_tinh_bien_the` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `hinh_anh_san_pham`
--
ALTER TABLE `hinh_anh_san_pham`
  ADD CONSTRAINT `hinh_anh_san_pham_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `kho_hang`
--
ALTER TABLE `kho_hang`
  ADD CONSTRAINT `kho_hang_ibfk_1` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_to_product` FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`danh_muc_id`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `san_pham_ibfk_2` FOREIGN KEY (`thuong_hieu_id`) REFERENCES `thuong_hieu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `variant_option_values`
--
ALTER TABLE `variant_option_values`
  ADD CONSTRAINT `fk_map_to_value` FOREIGN KEY (`value_id`) REFERENCES `gia_tri_thuoc_tinh_bien_the` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_map_to_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
