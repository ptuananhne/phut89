-- Tạo và sử dụng cơ sở dữ liệu
CREATE DATABASE IF NOT EXISTS `phut89` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `phut89`;

-- 1. Bảng danh mục sản phẩm
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(110) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=4;

-- 2. Bảng thương hiệu
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(110) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6;

-- 3. Bảng sản phẩm (Đã cập nhật với view_count)
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category_id` int DEFAULT NULL,
  `brand_id` int DEFAULT NULL,
  `view_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `brand_id` (`brand_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2;

-- 4. Bảng hình ảnh sản phẩm
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2;

-- 5. Bảng banners
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `position` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- 6. Bảng admin
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB;

-- 7. Bảng thuộc tính (Thông số kỹ thuật)
CREATE TABLE IF NOT EXISTS `attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5;

-- 8. Bảng nối giá trị thuộc tính cho sản phẩm
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `product_id` int NOT NULL,
  `attribute_id` int NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`product_id`,`attribute_id`),
  KEY `pa_attribute_fk` (`attribute_id`),
  CONSTRAINT `pa_attribute_fk` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pa_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- --- CHÈN DỮ LIỆU MẪU ---

-- Dữ liệu mẫu cho categories
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Điện thoại', 'dien-thoai'),
(2, 'Laptop', 'laptop'),
(3, 'Xe máy', 'xe-may');

-- Dữ liệu mẫu cho brands
INSERT IGNORE INTO `brands` (`id`, `name`, `slug`) VALUES
(1, 'Apple', 'apple'),
(2, 'Samsung', 'samsung'),
(3, 'Honda', 'honda'),
(4, 'Dell', 'dell'),
(5, 'Yamaha', 'yamaha');

-- Dữ liệu mẫu cho 1 sản phẩm
INSERT IGNORE INTO `products` (`id`, `name`, `slug`, `price`, `description`, `category_id`, `brand_id`, `view_count`) VALUES
(1, 'iPhone 15 Pro Max 256GB', 'iphone-15-pro-max-256gb', 29500000, 'Camera siêu đỉnh, chip A17 Pro mạnh mẽ. Khung viền Titan sang trọng, bền bỉ. Màn hình ProMotion 120Hz mượt mà.', 1, 1, 150);

-- Dữ liệu mẫu cho hình ảnh của sản phẩm đó
INSERT IGNORE INTO `product_images` (`id`, `product_id`, `image_url`, `is_default`) VALUES
(1, 1, 'https://cdn.tgdd.vn/Products/Images/42/305658/iphone-15-pro-max-blue-thumbnew-600x600.jpg', 1);

-- Dữ liệu mẫu cho admin
INSERT IGNORE INTO `admins` (`username`, `password`) VALUES
('admin', '$2b$10$VbR.YxMTzAeAe6wJ2utk5ulZDFbItcJRoFVDeCDBqUy7S1aX/t8x6');

-- Dữ liệu mẫu cho attributes
INSERT IGNORE INTO `attributes` (`id`, `name`) VALUES
(1, 'CPU'),
(2, 'RAM'),
(3, 'Ổ cứng'),
(4, 'Màn hình');

-- Dữ liệu mẫu cho product_attributes
INSERT IGNORE INTO `product_attributes` (`product_id`, `attribute_id`, `value`) VALUES
(1, 1, 'Apple A17 Pro'),
(1, 2, '8GB'),
(1, 3, '256GB'),
(1, 4, '6.7 inch Super Retina XDR');

