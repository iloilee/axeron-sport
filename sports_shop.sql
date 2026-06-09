-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 05, 2026 lúc 10:46 PM
-- Thời gian đã tạo: Th6 09, 2026 lúc 05:25 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `sports_shop`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `articles`
--

CREATE TABLE `articles` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề bài viết',
  `slug` varchar(255) NOT NULL COMMENT 'Slug URL thân thiện',
  `excerpt` text DEFAULT NULL COMMENT 'Tóm tắt ngắn (hiển thị ở danh sách)',
  `content` longtext DEFAULT NULL COMMENT 'Nội dung bài viết (HTML/Markdown)',
  `featured_image` varchar(500) DEFAULT NULL COMMENT 'Ảnh đại diện bài viết',
  `category` enum('news','blog','promotion','announcement','guide') DEFAULT 'blog' COMMENT 'Loại bài viết',
  `tags` varchar(500) DEFAULT NULL COMMENT 'Tags, phân cách bằng dấu phẩy',
  `author_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Tác giả (admin user_id)',
  `author_name` varchar(100) DEFAULT NULL COMMENT 'Tên tác giả hiển thị',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT '1=bài nổi bật hiển thị trang chủ',
  `is_published` tinyint(1) DEFAULT 0 COMMENT '1=đã xuất bản, 0=nháp',
  `published_at` datetime DEFAULT NULL COMMENT 'Thời gian xuất bản',
  `view_count` int(11) DEFAULT 0 COMMENT 'Số lượt xem',
  `meta_title` varchar(255) DEFAULT NULL COMMENT 'SEO title',
  `meta_description` text DEFAULT NULL COMMENT 'SEO description',
  `meta_keywords` varchar(255) DEFAULT NULL COMMENT 'SEO keywords',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý bài viết/tin tức';

--
-- Đang đổ dữ liệu cho bảng `articles`
--

REPLACE INTO `articles` (`article_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `tags`, `author_id`, `author_name`, `is_featured`, `is_published`, `published_at`, `view_count`, `meta_title`, `meta_description`, `meta_keywords`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Top 10 Giày Chạy Bộ Tốt Nhất 2024', 'top-10-giay-chay-bo-2024', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024 với công nghệ tiên tiến nhất.', '<h2>Giới thiệu</h2><p>Năm 2024 hứa hẹn là năm của những đôi giày chạy bộ công nghệ cao với nhiều cải tiến vượt bậc. Dưới đây là top 10 sản phẩm đáng mua nhất.</p><h2>1. Nike Alphafly 3</h2><p>Đôi giày định hình lại ngành giày chạy bộ với công nghệ ZoomX foam và Air Zoom pods.</p><h2>2. Adidas Ultraboost Light</h2><p>Phiên bản nhẹ hơn, êm hơn với Boost foam thế hệ mới.</p>', 'https://placehold.co/800x400/1d3557/ffffff?text=Top+Running+Shoes', 'blog', NULL, NULL, 'Axeron Team', 1, 1, '2026-06-05 12:54:12', 0, 'Top 10 Giày Chạy Bộ Tốt Nhất 2024 | Axeron Sport', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024.', NULL, 0, '2026-06-05 12:54:12', '2026-06-05 12:54:12'),
(2, 'Hướng Dẫn Chọn Size Giày Thể Thao', 'huong-dan-chon-size-giay', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ, mẹo đo chân tại nhà để chọn được đôi giày vừa vặn.', '<h2>Tại sao chọn đúng size quan trọng?</h2><p>Giày vừa vặn giúp bạn thoải mái khi vận động, tránh chấn thương và tối ưu hiệu suất tập luyện.</p><h2>Cách đo chân</h2><p>1. Đứng lên tờ giấy và vẽ viền chân<br>2. Đo khoảng cách từ gót đến ngón dài nhất<br>3. Cộng thêm 0.5-1cm để có không gian thoải mái</p>', 'https://placehold.co/800x400/457b9d/ffffff?text=Size+Guide', 'guide', NULL, NULL, 'Axeron Team', 1, 1, '2026-06-05 12:54:12', 0, 'Hướng Dẫn Chọn Size Giày Thể Thao | Axeron Sport', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ.', NULL, 0, '2026-06-05 12:54:12', '2026-06-05 12:54:12'),
(3, 'Axeron Khai Trương Cửa Hàng Mới', 'axeron-khai-truong-cua-hang-moi', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi hấp dẫn dành cho khách hàng.', '<h2>Grand Opening Event</h2><p>Axeron Sport hân hạnh thông báo khai trương cửa hàng mới tại 456 Nguyễn Thị Thập, Quận 7, TP.HCM.</p><h2>Ưu đãi khai trương</h2><ul><li>Giảm 20% toàn bộ sản phẩm</li><li>Miễn phí vận chuyển cho đơn từ 500K</li><li>Tặng voucher 200K cho 100 khách hàng đầu tiên</li></ul>', 'https://placehold.co/800x400/e63946/ffffff?text=Grand+Opening', 'announcement', NULL, NULL, 'Axeron Team', 0, 1, '2026-06-05 12:54:12', 0, 'Axeron Khai Trương Cửa Hàng Mới | Axeron Sport', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi.', NULL, 0, '2026-06-05 12:54:12', '2026-06-05 12:54:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `banner_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề banner',
  `subtitle` varchar(255) DEFAULT NULL COMMENT 'Phụ đề/description ngắn',
  `image_url` varchar(500) NOT NULL COMMENT 'Đường dẫn ảnh banner',
  `image_url_mobile` varchar(500) DEFAULT NULL COMMENT 'Ảnh banner cho mobile',
  `link_url` varchar(500) DEFAULT NULL COMMENT 'URL khi click vào banner',
  `link_type` enum('none','product','category','page','url') DEFAULT 'none' COMMENT 'Loại link: none, product, category, page, url',
  `target_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID sản phẩm/danh mục/page nếu cần',
  `button_text` varchar(100) DEFAULT NULL COMMENT 'Text nút bấm (VD: "Mua Ngay")',
  `position` int(11) DEFAULT 0 COMMENT 'Vị trí hiển thị (thứ tự)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=hiển thị, 0=ẩn',
  `start_date` datetime DEFAULT NULL COMMENT 'Bắt đầu hiển thị từ ngày',
  `end_date` datetime DEFAULT NULL COMMENT 'Kết thúc hiển thị',
  `click_count` int(11) DEFAULT 0 COMMENT 'Số lượt click',
  `created_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin tạo',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý banner/slider';

--
-- Đang đổ dữ liệu cho bảng `banners`
--

REPLACE INTO `banners` (`banner_id`, `title`, `subtitle`, `image_url`, `image_url_mobile`, `link_url`, `link_type`, `target_id`, `button_text`, `position`, `is_active`, `start_date`, `end_date`, `click_count`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Siêu Sale Mùa Hè', 'Giảm đến 50% cho các sản phẩm thể thao', 'https://placehold.co/1920x800/e63946/ffffff?text=Summer+SALE+50%25', NULL, NULL, 'url', NULL, 'Mua Ngay', 1, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-05 12:54:12'),
(2, 'Colección Adidas Mới', 'Công nghệ Boost đỉnh cao - Thiết kế hiện đại', 'https://placehold.co/1920x800/1d3557/ffffff?text=Adidas+Collection', NULL, NULL, 'category', NULL, 'Khám Phá Ngay', 2, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-05 12:54:12'),
(3, 'Giày Chạy Bộ Pro', 'Hỗ trợ tối đa - Cực kỳ nhẹ', 'https://placehold.co/1920x800/457b9d/ffffff?text=Running+Pro', NULL, NULL, 'url', NULL, 'Xem Chi Tiết', 3, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-05 12:54:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(10) UNSIGNED NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thuong hieu san pham';

--
-- Đang đổ dữ liệu cho bảng `brands`
--

REPLACE INTO `brands` (`brand_id`, `brand_name`, `logo_url`, `description`, `is_active`) VALUES
(1, 'Động Lực', '', '', 1),
(2, 'Nike', NULL, NULL, 1),
(3, 'Adidas', NULL, NULL, 1),
(4, 'Yonex', NULL, NULL, 1),
(5, 'Lining', NULL, NULL, 1),
(6, 'Puma', NULL, NULL, 1),
(7, 'Under Armour', NULL, NULL, 1),
(8, 'Mizuno', NULL, NULL, 1),
(9, 'Asics', NULL, NULL, 1),
(10, 'Head', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Moi user chi co 1 gio hang dang mo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gio hang cua nguoi dung';

--
-- Đang đổ dữ liệu cho bảng `carts`
--

REPLACE INTO `carts` (`cart_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(2, 4, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 5, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(13, 1, '2026-06-05 15:51:29', '2026-06-05 15:51:29');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'So luong san pham',
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cart_items`
--

REPLACE INTO `cart_items` (`cart_item_id`, `cart_id`, `variant_id`, `quantity`, `added_at`) VALUES
(1, 1, 147, 1, '2026-05-29 19:17:55'),
(2, 1, 3, 1, '2026-05-29 19:17:55'),
(3, 1, 172, 2, '2026-05-29 19:17:55'),
(4, 2, 204, 1, '2026-05-29 19:17:55'),
(5, 2, 136, 1, '2026-05-29 19:17:55'),
(6, 3, 14, 1, '2026-05-29 19:17:55'),
(7, 3, 95, 2, '2026-05-29 19:17:55'),
(20, 13, 173, 1, '2026-06-08 19:22:41'),
(21, 13, 147, 1, '2026-06-08 19:22:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = danh muc goc; co gia tri = danh muc con',
  `category_name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL COMMENT 'URL-friendly, vd: giay-chay-bo',
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'An/hien danh muc',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh muc san pham (de quy cha-con)';

--
-- Đang đổ dữ liệu cho bảng `categories`
--

REPLACE INTO `categories` (`category_id`, `parent_id`, `category_name`, `slug`, `description`, `image_url`, `sort_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Nam', 'nam', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(2, NULL, 'Nữ', 'nu', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, NULL, 'Thể Thao', 'the-thao', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(10, 1, 'Giày Nam', 'giay-nam', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(11, 1, 'Quần Áo Nam', 'quan-ao-nam', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(12, 1, 'Bộ Thể Thao Nam', 'bo-the-thao-nam', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(13, 1, 'Phụ Kiện Nam', 'phu-kien-nam', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(20, 2, 'Giày Nữ', 'giay-nu', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(21, 2, 'Quần Áo Nữ', 'quan-ao-nu', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(22, 2, 'Bộ Thể Thao Nữ', 'bo-the-thao-nu', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(23, 2, 'Phụ Kiện Nữ', 'phu-kien-nu', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(30, 3, 'Bóng Đá', 'bong-da', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(31, 3, 'Cầu Lông', 'cau-long', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(32, 3, 'Pickleball', 'pickleball', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(33, 3, 'Bóng Rổ', 'bong-ro', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(34, 3, 'Bóng Chuyền', 'bong-chuyen', NULL, NULL, 5, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(35, 3, 'Thiết Bị Tập', 'thiet-bi-tap', NULL, NULL, 6, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(100, 10, 'Giày Đá Bóng', 'giay-da-bong', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(101, 10, 'Giày Chạy Bộ Nam', 'giay-chay-bo-nam', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(102, 10, 'Giày Cầu Lông Nam', 'giay-cau-long-nam', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(103, 10, 'Giày Bóng Rổ', 'giay-bong-ro', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(104, 10, 'Giày Thể Thao Nam', 'giay-the-thao-nam', NULL, NULL, 5, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(105, 10, 'Dép Nam', 'dep-nam', NULL, NULL, 6, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(110, 11, 'Áo Polo Nam', 'ao-polo-nam', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(111, 11, 'Áo T-shirt Nam', 'ao-tshirt-nam', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(112, 11, 'Áo Khoác Nam', 'ao-khoac-nam', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(113, 11, 'Quần Short Nam', 'quan-short-nam', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(114, 11, 'Quần Dài Nam', 'quan-dai-nam', NULL, NULL, 5, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(200, 20, 'Giày Chạy Bộ Nữ', 'giay-chay-bo-nu', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(201, 20, 'Giày Cầu Lông Nữ', 'giay-cau-long-nu', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(202, 20, 'Giày Thể Thao Nữ', 'giay-the-thao-nu', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(203, 20, 'Dép Nữ', 'dep-nu', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(210, 21, 'Áo Phông Nữ', 'ao-phong-nu', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(211, 21, 'Áo Sport Bra', 'ao-sport-bra', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(212, 21, 'Quần Short Nữ', 'quan-short-nu', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(213, 21, 'Quần Legging', 'quan-legging', NULL, NULL, 4, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(214, 21, 'Chân Váy', 'chan-vay', NULL, NULL, 5, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(300, 30, 'Bóng Đá Sân Cỏ', 'bong-da-san-co', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(301, 30, 'Bóng Futsal', 'bong-futsal', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(302, 31, 'Vợt Cầu Lông', 'vot-cau-long', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(303, 31, 'Cầu Lông Lưới', 'cau-long-qua', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(304, 32, 'Vợt Pickleball', 'vot-pickleball', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(305, 32, 'Bóng Pickleball', 'bong-pickleball', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(306, 35, 'Máy Chạy Bộ', 'may-chay-bo', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(307, 35, 'Tạ Tay Và Ghế', 'ta-tay-va-giai', NULL, NULL, 2, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(308, 35, 'Giàn Tập', 'gian-tap', NULL, NULL, 3, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(312, NULL, 'bóng đá', 'bong-a', '', NULL, 4, 1, '2026-06-06 01:44:00', '2026-06-06 01:44:00'),
(313, 312, 'bóng đá đơn', 'bong-a-n', '', NULL, 1, 1, '2026-06-06 01:44:26', '2026-06-06 01:44:26'),
(314, 313, 'bóng đá đơn đôi', 'bong-a-n-i', '', NULL, 1, 1, '2026-06-06 01:44:39', '2026-06-06 01:44:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `sender_type` enum('user','bot','staff') NOT NULL,
  `content` text NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tin nhan trong chatbox';

--
-- Đang đổ dữ liệu cho bảng `chat_messages`
--

REPLACE INTO `chat_messages` (`message_id`, `session_id`, `sender_type`, `content`, `sent_at`) VALUES
(1, 1, 'user', 'Cho tôi hỏi vợt Yonex Astrox 99 Pro có hàng thật không ạ?', '2026-05-29 19:17:55'),
(2, 1, 'bot', 'Dạ, Yonex Astrox 99 Pro tại cửa hàng là hàng chính hãng 100%, có tem chống hàng giả và chứng nhận phân phối chính thức. Bạn cần hỗ trợ thêm gì không?', '2026-05-29 19:17:55'),
(3, 1, 'user', 'Ok cảm ơn, vậy tôi muốn đặt hàng', '2026-05-29 19:17:55'),
(4, 1, 'staff', 'Chào bạn! Bạn có thể thực hiện đặt hàng trực tiếp trên website hoặc liên hệ hotline 1900xxxx để được hỗ trợ nhanh hơn nhé!', '2026-05-29 19:17:55'),
(5, 2, 'user', 'Giày size 41 có còn hàng không?', '2026-05-29 19:17:55'),
(6, 2, 'bot', 'Bên mình còn hàng size 41 của nhiều dòng sản phẩm. Bạn đang quan tâm sản phẩm nào? Giày chạy bộ, đá bóng hay cầu lông ạ?', '2026-05-29 19:17:55'),
(7, 3, 'user', 'Ship Hà Nội mất bao lâu vậy shop?', '2026-05-29 19:17:55'),
(8, 3, 'bot', 'Giao hàng tiêu chuẩn Hà Nội khoảng 3-5 ngày làm việc. Giao hàng nhanh 1-2 ngày. Bạn muốn chọn hình thức nào?', '2026-05-29 19:17:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `session_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL neu khach vang lai',
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phien hoi thoai chatbox';

--
-- Đang đổ dữ liệu cho bảng `chat_sessions`
--

REPLACE INTO `chat_sessions` (`session_id`, `user_id`, `status`, `started_at`, `ended_at`) VALUES
(1, 3, 'closed', '2026-05-29 19:17:55', NULL),
(2, NULL, 'closed', '2026-05-29 19:17:55', NULL),
(3, 5, 'open', '2026-05-29 19:17:55', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `shipping_id` int(10) UNSIGNED DEFAULT NULL,
  `promo_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL COMMENT 'Snapshot ten nguoi nhan',
  `recipient_phone` varchar(15) NOT NULL COMMENT 'Snapshot so dien thoai',
  `shipping_address` varchar(500) NOT NULL COMMENT 'Snapshot dia chi giao hang',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Tong tien hang truoc giam gia',
  `discount_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'So tien duoc giam',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Phi van chuyen',
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Tong thanh toan cuoi cung',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'pending' COMMENT 'Trang thai don hang',
  `payment_method` enum('cod','bank_transfer','momo','vnpay','zalopay') NOT NULL DEFAULT 'cod' COMMENT 'Hinh thuc thanh toan',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' COMMENT 'Trang thai thanh toan',
  `note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

REPLACE INTO `orders` (`order_id`, `user_id`, `shipping_id`, `promo_id`, `recipient_name`, `recipient_phone`, `shipping_address`, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `order_status`, `payment_method`, `payment_status`, `note`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 1, 'Nguyễn Văn An', '0912345678', '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP HCM', 890000.00, 50000.00, 0.00, 840000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(2, 4, 2, NULL, 'Trần Thị Bích', '0923456789', '45 Xô Viết Nghệ Tĩnh, Phường 25, Quận Bình Thạnh, TP HCM', 4500000.00, 0.00, 50000.00, 4550000.00, 'shipped', 'vnpay', 'paid', 'Giao hàng giờ hành chính', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 5, 1, 2, 'Lê Minh Cường', '0934567890', '88 Trần Thái Tông, Phường Dịch Vọng, Cầu Giấy, Hà Nội', 2900000.00, 100000.00, 0.00, 2800000.00, 'confirmed', 'bank_transfer', 'paid', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 6, 3, NULL, 'Phạm Thị Dung', '0945678901', '23 Trần Phú, Phường Hải Châu 1, Hải Châu, Đà Nẵng', 1200000.00, 0.00, 80000.00, 1280000.00, 'delivered', 'cod', 'unpaid', NULL, '2026-05-29 19:17:55', '2026-06-05 14:55:07'),
(5, 7, 2, 3, 'Hoàng Văn Em', '0956789012', '5 Nguyễn Văn Linh, Phường Tân Phong, Quận 7, TP HCM', 680000.00, 50000.00, 0.00, 630000.00, 'delivered', 'momo', 'paid', 'Giao trước 18h', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(6, 3, 1, NULL, 'Nguyễn Văn An', '0912345678', '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP HCM', 3200000.00, 0.00, 0.00, 3200000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(7, 8, 2, NULL, 'Nguyễn Thị Phương', '0967890123', '100 Võ Văn Ngân, Phường Linh Trung, Thủ Đức, TP HCM', 1570000.00, 0.00, 50000.00, 1620000.00, 'processing', 'vnpay', 'paid', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(8, 4, 1, 1, 'Trần Thị Bích', '0923456789', '45 Xô Viết Nghệ Tĩnh, Phường 25, Quận Bình Thạnh, TP HCM', 750000.00, 50000.00, 0.00, 700000.00, 'cancelled', 'cod', 'unpaid', 'Khách hủy vì sai size', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(16, 1, 2, 6, 'Quản Trị Viên', '0123456789', '77/5a phường 4 tpvl, , , Vĩnh Long', 4500000.00, 225000.00, 0.00, 4275000.00, 'cancelled', 'cod', 'refunded', '', '2026-06-05 16:08:26', '2026-06-05 16:08:54'),
(17, 1, 14, 6, 'Quản Trị Viên', '0123456789', '77/5a phường 4 tpvl, , , Bình Dương', 4500000.00, 225000.00, 25000.00, 4300000.00, 'delivered', 'bank_transfer', 'paid', '', '2026-06-05 16:33:17', '2026-06-05 16:33:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED NOT NULL,
  `product_name` varchar(200) NOT NULL COMMENT 'Snapshot ten san pham',
  `variant_info` varchar(100) DEFAULT NULL COMMENT 'Snapshot mau/size',
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Gia tai thoi diem mua',
  `quantity` int(11) NOT NULL DEFAULT 1 COMMENT 'So luong',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Thanh tien dong nay'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

REPLACE INTO `order_items` (`order_item_id`, `order_id`, `variant_id`, `product_name`, `variant_info`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen - Size 41', 890000.00, 1, 890000.00),
(2, 2, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Đỏ - 4U/G5', 4500000.00, 1, 4500000.00),
(3, 3, 151, 'Vợt cầu lông Yonex Nanoflare 700', 'Xanh lá - 4U/G5', 3200000.00, 1, 3200000.00),
(4, 4, 213, 'Bóng đá sân cỏ Adidas Al Rihla', 'Trắng/Đen - Size 5', 1200000.00, 1, 1200000.00),
(5, 5, 33, 'Giày đá bóng DL Striker FG 2024', 'Vàng - Size 40', 650000.00, 1, 650000.00),
(6, 6, 175, 'Cầu lông Yonex AS-30 (hộp 12 quả)', 'Trắng - Lớp 77', 280000.00, 2, 560000.00),
(7, 6, 70, 'Giày cầu lông DL Wing Pro 2024', 'Đỏ - Size 39', 680000.00, 1, 680000.00),
(8, 7, 204, 'Quần legging Under Armour HeatGear', 'Đen - M', 750000.00, 1, 750000.00),
(9, 7, 136, 'Áo sport bra Adidas Powerreact', 'Đen - M', 680000.00, 1, 680000.00),
(10, 8, 15, 'Giày Nike Revolution 7', 'Đen - Size 41', 1950000.00, 1, 1950000.00),
(16, 16, 148, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Do (3U) - Size 3U/G4', 4500000.00, 1, 4500000.00),
(17, 17, 148, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Do (3U) - Size 3U/G4', 4500000.00, 1, 4500000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_status_logs`
--

CREATE TABLE `order_status_logs` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'user_id cua admin/nhan vien',
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lich su trang thai don hang';

--
-- Đang đổ dữ liệu cho bảng `order_status_logs`
--

REPLACE INTO `order_status_logs` (`log_id`, `order_id`, `changed_by`, `old_status`, `new_status`, `note`, `changed_at`) VALUES
(1, 1, 2, 'pending', 'confirmed', 'Xác nhận đơn hàng', '2026-05-29 19:17:55'),
(2, 1, 2, 'confirmed', 'processing', 'Đang đóng gói', '2026-05-29 19:17:55'),
(3, 1, 2, 'processing', 'shipped', 'Đã giao cho GHTK', '2026-05-29 19:17:55'),
(4, 1, 2, 'shipped', 'delivered', 'Khách đã nhận hàng', '2026-05-29 19:17:55'),
(5, 2, 2, 'pending', 'confirmed', 'Xác nhận đơn hàng', '2026-05-29 19:17:55'),
(6, 2, 2, 'confirmed', 'processing', 'Đang đóng gói', '2026-05-29 19:17:55'),
(7, 2, 2, 'processing', 'shipped', 'Đã giao cho GHN', '2026-05-29 19:17:55'),
(8, 3, 2, 'pending', 'confirmed', 'Đã xác nhận chuyển khoản', '2026-05-29 19:17:55'),
(9, 4, 2, NULL, 'pending', 'Đơn hàng mới', '2026-05-29 19:17:55'),
(10, 5, 2, 'pending', 'confirmed', 'Xác nhận', '2026-05-29 19:17:55'),
(11, 5, 2, 'confirmed', 'processing', 'Đóng gói', '2026-05-29 19:17:55'),
(12, 5, 2, 'processing', 'shipped', 'Đã giao vận chuyển', '2026-05-29 19:17:55'),
(13, 5, 2, 'shipped', 'delivered', 'Khách đã nhận', '2026-05-29 19:17:55'),
(14, 6, 2, 'pending', 'confirmed', 'Xác nhận', '2026-05-29 19:17:55'),
(15, 6, 2, 'confirmed', 'processing', 'Đóng gói', '2026-05-29 19:17:55'),
(16, 6, 2, 'processing', 'shipped', 'Đã gửi GHTK', '2026-05-29 19:17:55'),
(17, 6, 2, 'shipped', 'delivered', 'Giao thành công', '2026-05-29 19:17:55'),
(18, 7, 2, 'pending', 'confirmed', 'Xác nhận', '2026-05-29 19:17:55'),
(19, 7, 2, 'confirmed', 'processing', 'Đang xử lý', '2026-05-29 19:17:55'),
(20, 8, 2, 'pending', 'confirmed', 'Xác nhận', '2026-05-29 19:17:55'),
(21, 8, 1, 'confirmed', 'cancelled', 'Khách hủy đơn hàng vì sai size', '2026-05-29 19:17:55'),
(26, 4, 1, 'pending', 'delivered', NULL, '2026-06-05 14:55:07'),
(27, 16, NULL, NULL, 'pending', NULL, '2026-06-05 16:08:26'),
(28, 16, 1, 'pending', 'cancelled', NULL, '2026-06-05 16:08:52'),
(29, 16, 1, 'unpaid', 'refunded', 'Cập nhật thanh toán bởi admin', '2026-06-05 16:08:54'),
(30, 17, NULL, NULL, 'pending', NULL, '2026-06-05 16:33:17'),
(31, 17, 1, 'unpaid', 'paid', 'Cập nhật thanh toán bởi admin', '2026-06-05 16:33:46'),
(32, 17, 1, 'pending', 'delivered', NULL, '2026-06-05 16:33:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `txn_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `gateway` enum('momo','vnpay','zalopay','bank_transfer') NOT NULL COMMENT 'Cong thanh toan',
  `gateway_txn_id` varchar(150) DEFAULT NULL COMMENT 'Ma giao dich cua cong',
  `amount` decimal(14,2) NOT NULL COMMENT 'So tien giao dich',
  `status` enum('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  `raw_response` text DEFAULT NULL COMMENT 'JSON response tu cong thanh toan',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Giao dich thanh toan online';

--
-- Đang đổ dữ liệu cho bảng `payment_transactions`
--

REPLACE INTO `payment_transactions` (`txn_id`, `order_id`, `gateway`, `gateway_txn_id`, `amount`, `status`, `raw_response`, `created_at`, `updated_at`) VALUES
(1, 2, 'vnpay', 'VNPAY20250615001', 4550000.00, 'success', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(2, 3, 'bank_transfer', 'BIDV20250618001', 2800000.00, 'success', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 5, 'momo', 'MOMO20250620001', 630000.00, 'success', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 7, 'vnpay', 'VNPAY20250622001', 1620000.00, 'success', NULL, '2026-05-29 19:17:55', '2026-05-29 19:17:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `brand_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Gia co so',
  `stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Ton kho tong hop',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'An/hien san pham',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Noi bat tren trang chu',
  `featured_sort_order` int(11) DEFAULT 999,
  `avg_rating` decimal(3,2) DEFAULT NULL COMMENT 'Diem danh gia trung binh (1-5)',
  `total_reviews` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

REPLACE INTO `products` (`product_id`, `category_id`, `brand_id`, `product_name`, `slug`, `description`, `base_price`, `stock_quantity`, `is_visible`, `is_featured`, `featured_sort_order`, `avg_rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 101, 1, 'Giày chạy bộ DL Speed Pro X1', 'giay-chay-bo-dl-speed-pro-x1', 'Giày chạy bộ chuyên nghiệp với đế giày EVA siêu nhẹ.', 890000.00, 50, 1, 1, 4, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(2, 101, 1, 'Giày chạy bộ DL Runner Air 2024', 'giay-chay-bo-dl-runner-air-2024', 'Thiết kế khí động học giúp tăng tốc độ.', 750000.00, 60, 1, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(3, 101, 2, 'Giày Nike Revolution 7', 'giay-nike-revolution-7', 'Giày chạy bộ phổ biến của Nike với đế giữa foam đen.', 1950000.00, 30, 1, 1, 3, 4.50, 15, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(4, 101, 3, 'Giày Adidas Runfalcon 3.0', 'giay-adidas-runfalcon-3', 'Dòng giày chạy bộ giá tốt của Adidas.', 1650000.00, 40, 1, 0, 999, 4.30, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(5, 101, 9, 'Giày ASICS Gel-Nimbus 26', 'giay-asics-gel-nimbus-26', 'Dòng giày cao cấp của ASICS, công nghệ đệm Gel.', 3200000.00, 20, 1, 1, 5, 4.80, 18, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(6, 100, 1, 'Giày đá bóng DL Striker FG 2024', 'giay-da-bong-dl-striker-fg-2024', 'Giày đá bóng sân cỏ, đế cao su tự nhiên.', 650000.00, 80, 1, 1, 6, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(7, 100, 1, 'Giày đá bóng DL Futsal Pro F10', 'giay-da-bong-dl-futsal-pro-f10', 'Thiết kế dành riêng cho futsal, đế bám sân nhà tốt.', 550000.00, 70, 1, 0, 999, 4.30, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(8, 100, 3, 'Giày đá bóng Adidas Predator 24 FG', 'giay-da-bong-adidas-predator-24', 'Vật liệu Zone Skin giúp kiểm soát bóng chính xác hơn.', 2800000.00, 25, 1, 1, 7, 4.70, 11, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(9, 100, 2, 'Giày đá bóng Nike Phantom GX2', 'giay-da-bong-nike-phantom-gx2', 'Công nghệ Aerotrak giúp bóng bay chính xác.', 3100000.00, 20, 1, 0, 999, 4.60, 13, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(10, 100, 1, 'Giày đá bóng DL Speed FG Jr', 'giay-da-bong-dl-speed-fg-jr', 'Dành cho cầu thủ trẻ em và thiếu niên.', 420000.00, 100, 1, 0, 999, 4.20, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(11, 102, 4, 'Giày cầu lông Yonex Power Cushion 65Z3', 'giay-cl-yonex-65z3', 'Đế giày Power Cushion hấp thụ chấn động.', 2900000.00, 30, 1, 1, 9, 4.80, 16, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(12, 102, 1, 'Giày cầu lông DL Wing Pro 2024', 'giay-cl-dl-wing-pro', 'Giày cầu lông thương hiệu Đồng Lực, đế EVA nhẹ.', 680000.00, 60, 1, 0, 999, 4.40, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(13, 102, 5, 'Giày cầu lông Lining Ranger TD', 'giay-cl-lining-ranger', 'Thương hiệu Lining nổi tiếng, đế giữa Light Foam.', 1200000.00, 40, 1, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(14, 201, 4, 'Giày cầu lông nữ Yonex SHB-01MXLX', 'giay-cl-nu-yonex-01mx', 'Thiết kế dành cho nữ, trọng lượng siêu nhẹ 70g.', 3100000.00, 20, 1, 1, 10, 4.90, 14, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(15, 201, 1, 'Giày cầu lông nữ DL Wing Lite', 'giay-cl-nu-dl-wing-lite', 'Phiên bản nhẹ hơn cho nữ, màu sắc nữ tính.', 620000.00, 50, 1, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(16, 110, 1, 'Áo polo thể thao DL Classic 2024', 'ao-polo-dl-classic-2024', 'Vải Polyester 100%, thoáng khí, thoáng mát.', 280000.00, 120, 1, 1, 8, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(17, 110, 1, 'Áo polo DL Pro Team 2024', 'ao-polo-dl-pro-team-2024', 'Phiên bản chuyên nghiệp cho các đội thể thao.', 320000.00, 100, 1, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(18, 111, 1, 'Áo T-shirt DL Training Basic', 'ao-tshirt-dl-training-basic', 'Áo tập thể thao co tròn, vải Polyester thoáng mát.', 220000.00, 150, 1, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(19, 111, 7, 'Áo Under Armour HeatGear Nam', 'ao-ua-heatgear-nam', 'Công nghệ HeatGear hút ẩm và làm mát nhanh.', 650000.00, 60, 1, 1, 11, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(20, 111, 2, 'Áo Nike Dri-FIT Training', 'ao-nike-dri-fit-training', 'Công nghệ Dri-FIT thoát mồ hôi siêu nhanh.', 580000.00, 70, 1, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(21, 210, 1, 'Áo phông nữ DL Sport Lite', 'ao-phong-nu-dl-sport-lite', 'Áo phông thể thao nữ, chất liệu co giãn 4 chiều.', 230000.00, 100, 1, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(22, 210, 7, 'Áo Under Armour HeatGear Nữ', 'ao-ua-heatgear-nu', 'Phiên bản nữ của HeatGear, chất liệu siêu nhẹ.', 620000.00, 50, 1, 1, 12, 4.60, 10, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(23, 211, 3, 'Áo sport bra Adidas Powerreact', 'ao-sport-bra-adidas', 'Áo lót thể thao nữ Adidas, dây vai lưới thông gió.', 680000.00, 45, 1, 1, 14, 4.50, 11, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(24, 211, 2, 'Áo sport bra Nike Indy', 'ao-sport-bra-nike-indy', 'Nike Indy Medium-Support, chất liệu Dri-FIT.', 750000.00, 40, 1, 0, 999, 4.70, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(25, 302, 4, 'Vợt cầu lông Yonex Astrox 99 Pro', 'vot-cl-yonex-astrox-99-pro', 'Vợt tấn công hàng đầu Yonex, khung carbon cao cấp.', 4500000.00, 9, 1, 1, 1, 4.90, 22, '2026-05-29 19:17:55', '2026-06-07 19:54:05'),
(26, 302, 4, 'Vợt cầu lông Yonex Nanoflare 700', 'vot-cl-yonex-nanoflare-700', 'Vợt phản công nhanh, khung Tungsten Mesh.', 3200000.00, 25, 1, 1, 15, 4.80, 18, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(27, 302, 5, 'Vợt cầu lông Lining Windstorm 72', 'vot-cl-lining-windstorm-72', 'Vợt phản công siêu nhẹ 72g, khung carbon tổ hợp.', 1800000.00, 35, 1, 0, 999, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(28, 302, 1, 'Vợt cầu lông DL Pro 500', 'vot-cl-dl-pro-500', 'Vợt cầu lông thương hiệu Đồng Lực dành cho người chơi phổ thông.', 480000.00, 60, 1, 0, 999, 4.20, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(29, 302, 1, 'Vợt cầu lông DL Fighter 200', 'vot-cl-dl-fighter-200', 'Vợt dành cho người mới bắt đầu, dễ cầm tay.', 280000.00, 80, 1, 0, 999, 4.10, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(30, 302, 10, 'Vợt cầu lông Head Zephyr Pro', 'vot-cl-head-zephyr-pro', 'Head Zephyr Pro trọng lượng 78g, phù hợp trung-cao cấp.', 2100000.00, 28, 1, 0, 999, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(31, 303, 4, 'Cầu lông Yonex AS-05 (hop 12 qua)', 'cau-long-yonex-as05-12', 'Cầu lông lưới ngắn Yonex AS-05.', 160000.00, 200, 1, 1, 16, 4.70, 20, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(32, 303, 4, 'Cầu lông Yonex AS-30 (hop 12 qua)', 'cau-long-yonex-as30-12', 'Cầu lông lưới vit cấp cao Yonex AS-30.', 280000.00, 150, 1, 0, 999, 4.80, 15, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(33, 303, 1, 'Cầu lông DL Training (hop 12 qua)', 'cau-long-dl-training-12', 'Cầu lông Đồng Lực dùng tập luyện hàng ngày.', 85000.00, 300, 1, 0, 999, 4.20, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(34, 303, 5, 'Cầu lông Lining A+90D (hop 12 qua)', 'cau-long-lining-a90d', 'Cầu lông lưới vit Lining A+90D, lớp 76 - 78.', 210000.00, 180, 1, 0, 999, 4.60, 11, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(35, 113, 1, 'Quần short thể thao DL Training 2024', 'quan-short-dl-training-2024', 'Quần short thể thao nam chất liệu Polyester khô nhanh.', 220000.00, 100, 1, 0, 999, 4.40, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(36, 113, 2, 'Quần short Nike Dri-FIT 5 inch', 'quan-short-nike-dri-fit-5', 'Quần short chạy bộ Nike 5 inch, Dri-FIT thoát mồ hôi.', 580000.00, 60, 1, 1, 17, 4.60, 9, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(37, 212, 1, 'Quần short nữ DL Active', 'quan-short-nu-dl-active', 'Quần short nữ với dây lưng co giãn.', 200000.00, 90, 1, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(38, 213, 7, 'Quần legging Under Armour HeatGear', 'quan-legging-ua-heatgear', 'Quần legging nữ 7/8, chất liệu HeatGear co giãn 4 chiều.', 750000.00, 45, 1, 1, 18, 4.70, 14, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(39, 213, 3, 'Quần legging Adidas Optime 7/8', 'quan-legging-adidas-optime', 'Quần legging nữ Adidas, AEROREADY thoát ẩm.', 680000.00, 50, 1, 0, 999, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(40, 300, 3, 'Bóng đá sân cỏ Adidas Al Rihla', 'bong-da-adidas-al-rihla', 'Bóng đá chính thức FIFA Quality Pro.', 1200000.00, 30, 1, 1, 13, 4.70, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(41, 300, 1, 'Bóng đá sân cỏ DL Classic 5', 'bong-da-dl-classic-5', 'Bóng đá số 5 chất liệu PVC cao cấp.', 180000.00, 80, 1, 0, 999, 4.20, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(42, 301, 1, 'Bóng Futsal DL Super', 'bong-futsal-dl-super', 'Bóng futsal số 4, chất liệu PU 4 lớp.', 250000.00, 60, 1, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(43, 305, 1, 'Bóng Pickleball DL Outdoor', 'bong-pickleball-dl-outdoor', 'Bóng pickleball 40 lỗ, chất liệu HDPE.', 120000.00, 100, 1, 0, 999, 4.30, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(44, 307, 1, 'Tạ tay DL Vinyl 1kg - 10kg', 'ta-tay-dl-vinyl', 'Tạ tay phủ cao su vinyl kháng trọt.', 85000.00, 200, 1, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(45, 13, 1, 'Mũ thể thao DL DryCool', 'mu-the-thao-dl-drycool', 'Mũ thể thao kiểu snapback, chất liệu thoáng khí.', 165000.00, 80, 1, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(46, 13, 1, 'Balo thể thao DL Sport 20L', 'balo-dl-sport-20l', 'Balo thể thao dung tích 20L.', 420000.00, 40, 1, 0, 999, 4.50, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(47, 13, 4, 'Tất cầu lông Yonex 75th (3 doi)', 'tat-cau-long-yonex-75th', '3 đôi tất cầu lông Yonex, chất liệu bông cao cấp.', 180000.00, 100, 1, 0, 999, 4.60, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(48, 13, 1, 'Tất thể thao DL Sport (5 doi)', 'tat-the-thao-dl-sport-5doi', '5 đôi tất thể thao DL, chất liệu cotton pha.', 95000.00, 150, 1, 0, 999, 4.20, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(49, 23, 1, 'Mũ thể thao nữ DL Lily', 'mu-the-thao-nu-dl-lily', 'Mũ nữ kiểu bucket hat thời trang.', 155000.00, 60, 1, 0, 999, 4.30, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(50, 23, 3, 'Balo nữ Adidas Classic BOS', 'balo-nu-adidas-classic', 'Balo nữ Adidas Classic BOS dung tích 22L.', 650000.00, 30, 1, 0, 999, 4.60, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(56, 314, 4, 'sản phẩm test ẩn hiện', 'san-phm-test-n-hin', '', 1000000.00, 10, 1, 1, 2, NULL, 0, '2026-06-06 02:20:00', '2026-06-06 02:53:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Anh dai dien chinh',
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hinh anh san pham';

--
-- Đang đổ dữ liệu cho bảng `product_images`
--

REPLACE INTO `product_images` (`image_id`, `product_id`, `image_url`, `alt_text`, `sort_order`, `is_primary`, `color`) VALUES
(1, 1, 'https://placehold.co/600x600/111827/ffffff?text=DL+Speed+Pro+X1', 'Giày chạy bộ DL Speed Pro X1', 0, 1, NULL),
(2, 2, 'https://placehold.co/600x600/1e3a8a/ffffff?text=DL+Runner+Air', 'Giày chạy bộ DL Runner Air', 0, 1, NULL),
(3, 3, 'https://placehold.co/600x600/000000/ffffff?text=Nike+Rev+7', 'Nike Revolution 7', 0, 1, NULL),
(4, 4, 'https://placehold.co/600x600/1f2937/ffffff?text=Adidas+RF3', 'Adidas Runfalcon 3', 0, 1, NULL),
(5, 5, 'https://placehold.co/600x600/0369a1/ffffff?text=ASICS+GN26', 'ASICS Gel-Nimbus 26', 0, 1, NULL),
(6, 6, 'https://placehold.co/600x600/ca8a04/ffffff?text=DL+Striker+FG', 'Giày đá bóng DL Striker FG', 0, 1, NULL),
(7, 7, 'https://placehold.co/600x600/111827/ffffff?text=DL+Futsal+Pro', 'Giày đá bóng Futsal Pro', 0, 1, NULL),
(8, 8, 'https://placehold.co/600x600/1c1917/ffffff?text=Adidas+Predator', 'Adidas Predator 24', 0, 1, NULL),
(9, 9, 'https://placehold.co/600x600/dc2626/ffffff?text=Nike+Phantom', 'Nike Phantom GX2', 0, 1, NULL),
(10, 10, 'https://placehold.co/600x600/ca8a04/ffffff?text=DL+Speed+Jr', 'DL Speed FG Jr', 0, 1, NULL),
(11, 11, 'https://placehold.co/600x600/f59e0b/ffffff?text=Yonex+65Z3', 'Yonex Power Cushion 65Z3', 0, 1, NULL),
(12, 12, 'https://placehold.co/600x600/dc2626/ffffff?text=DL+Wing+Pro', 'DL Wing Pro 2024', 0, 1, NULL),
(13, 13, 'https://placehold.co/600x600/1e40af/ffffff?text=Lining+Ranger', 'Lining Ranger TD', 0, 1, NULL),
(14, 14, 'https://placehold.co/600x600/be185d/ffffff?text=Yonex+01MX', 'Yonex SHB-01MXLX Nữ', 0, 1, NULL),
(15, 15, 'https://placehold.co/600x600/f472b6/ffffff?text=DL+Wing+Lite', 'DL Wing Lite Nữ', 0, 1, NULL),
(16, 16, 'https://placehold.co/600x600/f0fdf4/166534?text=Polo+Classic', 'DL Polo Classic', 0, 1, NULL),
(17, 17, 'https://placehold.co/600x600/111827/ffffff?text=Polo+Pro+Team', 'DL Polo Pro Team', 0, 1, NULL),
(18, 18, 'https://placehold.co/600x600/1e40af/ffffff?text=DL+T-shirt', 'DL T-shirt Basic', 0, 1, NULL),
(19, 19, 'https://placehold.co/600x600/111827/ffffff?text=UA+HeatGear', 'UA HeatGear Nam', 0, 1, NULL),
(20, 20, 'https://placehold.co/600x600/dc2626/ffffff?text=Nike+DriFIT', 'Nike Dri-FIT Training', 0, 1, NULL),
(21, 21, 'https://placehold.co/600x600/fce7f3/9d174d?text=DL+Nu+Sport', 'DL Sport Lite Nữ', 0, 1, NULL),
(22, 22, 'https://placehold.co/600x600/111827/ffffff?text=UA+Nu', 'UA HeatGear Nữ', 0, 1, NULL),
(23, 23, 'https://placehold.co/600x600/7f1d1d/fecdd3?text=Adidas+Bra', 'Adidas Sport Bra', 0, 1, NULL),
(24, 24, 'https://placehold.co/600x600/dc2626/ffffff?text=Nike+Indy', 'Nike Indy Bra', 0, 1, NULL),
(26, 26, 'https://placehold.co/600x600/16a34a/ffffff?text=Yonex+NF700', 'Yonex Nanoflare 700', 0, 1, NULL),
(27, 27, 'https://placehold.co/600x600/1d4ed8/ffffff?text=Lining+WS72', 'Lining Windstorm 72', 0, 1, NULL),
(28, 28, 'https://placehold.co/600x600/111827/ffffff?text=DL+Pro+500', 'DL Pro 500', 0, 1, NULL),
(29, 29, 'https://placehold.co/600x600/374151/ffffff?text=DL+Fighter+200', 'DL Fighter 200', 0, 1, NULL),
(30, 30, 'https://placehold.co/600x600/f59e0b/111827?text=Head+Zephyr', 'Head Zephyr Pro', 0, 1, NULL),
(31, 31, 'https://placehold.co/600x600/fef9c3/713f12?text=Yonex+AS05', 'Yonex AS-05', 0, 1, NULL),
(32, 32, 'https://placehold.co/600x600/fef9c3/713f12?text=Yonex+AS30', 'Yonex AS-30', 0, 1, NULL),
(33, 33, 'https://placehold.co/600x600/ca8a04/ffffff?text=DL+Training', 'DL Training Cầu', 0, 1, NULL),
(34, 34, 'https://placehold.co/600x600/dc2626/ffffff?text=Lining+A90D', 'Lining A+90D', 0, 1, NULL),
(35, 35, 'https://placehold.co/600x600/111827/ffffff?text=DL+Short+Nam', 'DL Short Training Nam', 0, 1, NULL),
(36, 36, 'https://placehold.co/600x600/dc2626/ffffff?text=Nike+Short', 'Nike Short Dri-FIT', 0, 1, NULL),
(37, 37, 'https://placehold.co/600x600/fce7f3/9d174d?text=DL+Short+Nu', 'DL Active Short Nữ', 0, 1, NULL),
(38, 38, 'https://placehold.co/600x600/111827/ffffff?text=UA+Legging', 'UA Legging HeatGear', 0, 1, NULL),
(39, 39, 'https://placehold.co/600x600/1c1917/ffffff?text=Adidas+Legging', 'Adidas Legging Optime', 0, 1, NULL),
(40, 40, 'https://placehold.co/600x600/1c1917/ffffff?text=Adidas+Bong', 'Bóng đá Adidas Al Rihla', 0, 1, NULL),
(41, 41, 'https://placehold.co/600x600/ca8a04/ffffff?text=DL+Bong+DA', 'DL Bóng đá Classic 5', 0, 1, NULL),
(42, 42, 'https://placehold.co/600x600/1d4ed8/ffffff?text=DL+Futsal', 'DL Bóng Futsal', 0, 1, NULL),
(43, 43, 'https://placehold.co/600x600/ca8a04/111827?text=Pickleball', 'Bóng Pickleball DL', 0, 1, NULL),
(44, 44, 'https://placehold.co/600x600/1e40af/ffffff?text=Ta+Tay+DL', 'Tạ tay DL Vinyl', 0, 1, NULL),
(45, 45, 'https://placehold.co/600x600/111827/ffffff?text=Mu+DL', 'Mũ thể thao DL', 0, 1, NULL),
(46, 46, 'https://placehold.co/600x600/1e3a8a/ffffff?text=Balo+DL', 'Balo DL Sport', 0, 1, NULL),
(47, 47, 'https://placehold.co/600x600/f0fdf4/166534?text=Tat+Yonex', 'Tất Yonex', 0, 1, NULL),
(48, 48, 'https://placehold.co/600x600/f0fdf4/166534?text=Tat+DL', 'Tất DL Sport', 0, 1, NULL),
(49, 49, 'https://placehold.co/600x600/fce7f3/9d174d?text=Mu+Nu+DL', 'Mũ nữ DL Lily', 0, 1, NULL),
(50, 50, 'https://placehold.co/600x600/1c1917/ffffff?text=Balo+Adidas', 'Balo nữ Adidas', 0, 1, NULL),
(55, 56, 'https://placehold.co/600x600/111827/ffffff?text=sa%CC%89n+ph%C3%A2%CC%89m+test+%C3', 'sản phẩm test ẩn hiện', 0, 1, NULL),
(58, 25, '/uploads/products/vot-cl-yonex-astrox-99-pro/img_6a25663abb956_1780835898.webp', 'vot-cau-long-yonex-astrox-99-pro-do-khong-bao-hanh-ma-sp_1727208715.webp', 1, 0, 'Do (4U)'),
(59, 25, '/uploads/products/vot-cl-yonex-astrox-99-pro/img_6a2566416b5e4_1780835905.webp', 'vot-cau-long-yonex-astrox-99-pro-trang-ma-jp-3_1695687409.webp', 2, 1, 'Trang (4U)');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(100) NOT NULL COMMENT 'Ma SKU rieng cua tung bien the',
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(30) DEFAULT NULL COMMENT 'S, M, L, XL hoac 38, 39, 40...',
  `extra_price` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Gia tang them so voi base_price',
  `stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Ton kho bien the',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `product_variants`
--

REPLACE INTO `product_variants` (`variant_id`, `product_id`, `sku`, `color`, `size`, `extra_price`, `stock_quantity`, `is_active`, `is_deleted`) VALUES
(1, 1, 'DL-SPX1-DEN-39', 'Đen', '39', 0.00, 8, 1, 0),
(2, 1, 'DL-SPX1-DEN-40', 'Đen', '40', 0.00, 10, 1, 0),
(3, 1, 'DL-SPX1-DEN-41', 'Đen', '41', 0.00, 10, 1, 0),
(4, 1, 'DL-SPX1-DEN-42', 'Đen', '42', 0.00, 8, 1, 0),
(5, 1, 'DL-SPX1-DEN-43', 'Đen', '43', 0.00, 7, 1, 0),
(6, 1, 'DL-SPX1-TRANG-40', 'Trắng', '40', 0.00, 7, 1, 0),
(7, 2, 'DL-RAR-XANH-39', 'Xanh navy', '39', 0.00, 8, 1, 0),
(8, 2, 'DL-RAR-XANH-40', 'Xanh navy', '40', 0.00, 10, 1, 0),
(9, 2, 'DL-RAR-XANH-41', 'Xanh navy', '41', 0.00, 10, 1, 0),
(10, 2, 'DL-RAR-XANH-42', 'Xanh navy', '42', 0.00, 8, 1, 0),
(11, 2, 'DL-RAR-DO-40', 'Đỏ', '40', 0.00, 7, 1, 0),
(12, 2, 'DL-RAR-DO-41', 'Đỏ', '41', 0.00, 7, 1, 0),
(13, 3, 'NK-REV7-DEN-39', 'Đen', '39', 0.00, 5, 1, 0),
(14, 3, 'NK-REV7-DEN-40', 'Đen', '40', 0.00, 6, 1, 0),
(15, 3, 'NK-REV7-DEN-41', 'Đen', '41', 0.00, 6, 1, 0),
(16, 3, 'NK-REV7-DEN-42', 'Đen', '42', 0.00, 5, 1, 0),
(17, 3, 'NK-REV7-XANH-40', 'Xanh đường', '40', 50000.00, 4, 1, 0),
(18, 3, 'NK-REV7-XANH-41', 'Xanh đường', '41', 50000.00, 4, 1, 0),
(19, 4, 'AD-RF3-DEN-39', 'Đen', '39', 0.00, 7, 1, 0),
(20, 4, 'AD-RF3-DEN-40', 'Đen', '40', 0.00, 8, 1, 0),
(21, 4, 'AD-RF3-DEN-41', 'Đen', '41', 0.00, 8, 1, 0),
(22, 4, 'AD-RF3-DEN-42', 'Đen', '42', 0.00, 7, 1, 0),
(23, 4, 'AD-RF3-TRANG-40', 'Trắng', '40', 0.00, 5, 1, 0),
(24, 4, 'AD-RF3-TRANG-41', 'Trắng', '41', 0.00, 5, 1, 0),
(25, 5, 'AS-GN26-XANH-39', 'Xanh', '39', 100000.00, 3, 1, 0),
(26, 5, 'AS-GN26-XANH-40', 'Xanh', '40', 100000.00, 4, 1, 0),
(27, 5, 'AS-GN26-XANH-41', 'Xanh', '41', 100000.00, 4, 1, 0),
(28, 5, 'AS-GN26-XANH-42', 'Xanh', '42', 100000.00, 3, 1, 0),
(29, 5, 'AS-GN26-TRANG-40', 'Trắng', '40', 100000.00, 3, 1, 0),
(30, 5, 'AS-GN26-TRANG-41', 'Trắng', '41', 100000.00, 3, 1, 0),
(31, 6, 'DL-STR-VANG-38', 'Vàng', '38', 0.00, 12, 1, 0),
(32, 6, 'DL-STR-VANG-39', 'Vàng', '39', 0.00, 15, 1, 0),
(33, 6, 'DL-STR-VANG-40', 'Vàng', '40', 0.00, 15, 1, 0),
(34, 6, 'DL-STR-VANG-41', 'Vàng', '41', 0.00, 12, 1, 0),
(35, 6, 'DL-STR-VANG-42', 'Vàng', '42', 0.00, 10, 1, 0),
(36, 6, 'DL-STR-XANH-40', 'Xanh lá', '40', 0.00, 8, 1, 0),
(37, 7, 'DL-FP10-DEN-38', 'Đen', '38', 0.00, 10, 1, 0),
(38, 7, 'DL-FP10-DEN-39', 'Đen', '39', 0.00, 12, 1, 0),
(39, 7, 'DL-FP10-DEN-40', 'Đen', '40', 0.00, 12, 1, 0),
(40, 7, 'DL-FP10-DEN-41', 'Đen', '41', 0.00, 10, 1, 0),
(41, 7, 'DL-FP10-DO-39', 'Đỏ', '39', 0.00, 8, 1, 0),
(42, 7, 'DL-FP10-DO-40', 'Đỏ', '40', 0.00, 8, 1, 0),
(43, 8, 'AD-PR24-DEN-38', 'Đen', '38', 0.00, 4, 1, 0),
(44, 8, 'AD-PR24-DEN-39', 'Đen', '39', 0.00, 5, 1, 0),
(45, 8, 'AD-PR24-DEN-40', 'Đen', '40', 0.00, 5, 1, 0),
(46, 8, 'AD-PR24-DEN-41', 'Đen', '41', 0.00, 4, 1, 0),
(47, 8, 'AD-PR24-DEN-42', 'Đen', '42', 0.00, 3, 1, 0),
(48, 8, 'AD-PR24-TRANG-40', 'Trắng', '40', 100000.00, 4, 1, 0),
(49, 9, 'NK-PGX2-DEN-38', 'Đen', '38', 0.00, 3, 1, 0),
(50, 9, 'NK-PGX2-DEN-39', 'Đen', '39', 0.00, 4, 1, 0),
(51, 9, 'NK-PGX2-DEN-40', 'Đen', '40', 0.00, 4, 1, 0),
(52, 9, 'NK-PGX2-DEN-41', 'Đen', '41', 0.00, 3, 1, 0),
(53, 9, 'NK-PGX2-DO-39', 'Đỏ', '39', 50000.00, 3, 1, 0),
(54, 9, 'NK-PGX2-DO-40', 'Đỏ', '40', 50000.00, 3, 1, 0),
(55, 10, 'DL-JR-VANG-32', 'Vàng', '32', 0.00, 15, 1, 0),
(56, 10, 'DL-JR-VANG-33', 'Vàng', '33', 0.00, 20, 1, 0),
(57, 10, 'DL-JR-VANG-34', 'Vàng', '34', 0.00, 20, 1, 0),
(58, 10, 'DL-JR-VANG-35', 'Vàng', '35', 0.00, 20, 1, 0),
(59, 10, 'DL-JR-VANG-36', 'Vàng', '36', 0.00, 15, 1, 0),
(60, 10, 'DL-JR-VANG-37', 'Vàng', '37', 0.00, 10, 1, 0),
(61, 11, 'YNX-65Z3-TRANG-36', 'Trắng', '36', 0.00, 5, 1, 0),
(62, 11, 'YNX-65Z3-TRANG-37', 'Trắng', '37', 0.00, 6, 1, 0),
(63, 11, 'YNX-65Z3-TRANG-38', 'Trắng', '38', 0.00, 6, 1, 0),
(64, 11, 'YNX-65Z3-TRANG-39', 'Trắng', '39', 0.00, 5, 1, 0),
(65, 11, 'YNX-65Z3-TRANG-40', 'Trắng', '40', 0.00, 4, 1, 0),
(66, 11, 'YNX-65Z3-TRANG-41', 'Trắng', '41', 0.00, 4, 1, 0),
(67, 12, 'DL-WP-DO-36', 'Đỏ', '36', 0.00, 8, 1, 0),
(68, 12, 'DL-WP-DO-37', 'Đỏ', '37', 0.00, 10, 1, 0),
(69, 12, 'DL-WP-DO-38', 'Đỏ', '38', 0.00, 10, 1, 0),
(70, 12, 'DL-WP-DO-39', 'Đỏ', '39', 0.00, 10, 1, 0),
(71, 12, 'DL-WP-DO-40', 'Đỏ', '40', 0.00, 8, 1, 0),
(72, 12, 'DL-WP-XANH-38', 'Xanh', '38', 0.00, 7, 1, 0),
(73, 12, 'DL-WP-XANH-39', 'Xanh', '39', 0.00, 7, 1, 0),
(74, 13, 'LI-RG-XANH-36', 'Xanh navy', '36', 0.00, 6, 1, 0),
(75, 13, 'LI-RG-XANH-37', 'Xanh navy', '37', 0.00, 7, 1, 0),
(76, 13, 'LI-RG-XANH-38', 'Xanh navy', '38', 0.00, 7, 1, 0),
(77, 13, 'LI-RG-XANH-39', 'Xanh navy', '39', 0.00, 7, 1, 0),
(78, 13, 'LI-RG-XANH-40', 'Xanh navy', '40', 0.00, 6, 1, 0),
(79, 13, 'LI-RG-DEN-38', 'Đen', '38', 0.00, 7, 1, 0),
(80, 14, 'YNX-01MX-DO-35', 'Đỏ hồng', '35', 0.00, 3, 1, 0),
(81, 14, 'YNX-01MX-DO-36', 'Đỏ hồng', '36', 0.00, 4, 1, 0),
(82, 14, 'YNX-01MX-DO-37', 'Đỏ hồng', '37', 0.00, 4, 1, 0),
(83, 14, 'YNX-01MX-DO-38', 'Đỏ hồng', '38', 0.00, 4, 1, 0),
(84, 14, 'YNX-01MX-TRANG-36', 'Trắng', '36', 0.00, 3, 1, 0),
(85, 14, 'YNX-01MX-TRANG-37', 'Trắng', '37', 0.00, 2, 1, 0),
(86, 15, 'DL-WL-DO-35', 'Hồng', '35', 0.00, 7, 1, 0),
(87, 15, 'DL-WL-DO-36', 'Hồng', '36', 0.00, 8, 1, 0),
(88, 15, 'DL-WL-DO-37', 'Hồng', '37', 0.00, 8, 1, 0),
(89, 15, 'DL-WL-DO-38', 'Hồng', '38', 0.00, 8, 1, 0),
(90, 15, 'DL-WL-TRANG-36', 'Trắng', '36', 0.00, 6, 1, 0),
(91, 15, 'DL-WL-TRANG-37', 'Trắng', '37', 0.00, 6, 1, 0),
(92, 15, 'DL-WL-TRANG-38', 'Trắng', '38', 0.00, 7, 1, 0),
(93, 16, 'DL-POLO-TRANG-S', 'Trắng', 'S', 0.00, 20, 1, 0),
(94, 16, 'DL-POLO-TRANG-M', 'Trắng', 'M', 0.00, 25, 1, 0),
(95, 16, 'DL-POLO-TRANG-L', 'Trắng', 'L', 0.00, 25, 1, 0),
(96, 16, 'DL-POLO-TRANG-XL', 'Trắng', 'XL', 0.00, 20, 1, 0),
(97, 16, 'DL-POLO-XANH-M', 'Xanh navy', 'M', 0.00, 15, 1, 0),
(98, 16, 'DL-POLO-XANH-L', 'Xanh navy', 'L', 0.00, 15, 1, 0),
(99, 17, 'DL-PROT-DEN-S', 'Đen', 'S', 0.00, 15, 1, 0),
(100, 17, 'DL-PROT-DEN-M', 'Đen', 'M', 0.00, 20, 1, 0),
(101, 17, 'DL-PROT-DEN-L', 'Đen', 'L', 0.00, 20, 1, 0),
(102, 17, 'DL-PROT-DEN-XL', 'Đen', 'XL', 0.00, 15, 1, 0),
(103, 17, 'DL-PROT-DO-M', 'Đỏ', 'M', 0.00, 15, 1, 0),
(104, 17, 'DL-PROT-DO-L', 'Đỏ', 'L', 0.00, 15, 1, 0),
(105, 18, 'DL-TB-XANH-S', 'Xanh đường', 'S', 0.00, 25, 1, 0),
(106, 18, 'DL-TB-XANH-M', 'Xanh đường', 'M', 0.00, 30, 1, 0),
(107, 18, 'DL-TB-XANH-L', 'Xanh đường', 'L', 0.00, 30, 1, 0),
(108, 18, 'DL-TB-XANH-XL', 'Xanh đường', 'XL', 0.00, 25, 1, 0),
(109, 18, 'DL-TB-DEN-M', 'Đen', 'M', 0.00, 20, 1, 0),
(110, 18, 'DL-TB-DEN-L', 'Đen', 'L', 0.00, 20, 1, 0),
(111, 19, 'UA-HG-DEN-S', 'Đen', 'S', 0.00, 10, 1, 0),
(112, 19, 'UA-HG-DEN-M', 'Đen', 'M', 0.00, 12, 1, 0),
(113, 19, 'UA-HG-DEN-L', 'Đen', 'L', 0.00, 12, 1, 0),
(114, 19, 'UA-HG-DEN-XL', 'Đen', 'XL', 0.00, 10, 1, 0),
(115, 19, 'UA-HG-XANH-M', 'Xanh', 'M', 0.00, 8, 1, 0),
(116, 19, 'UA-HG-XANH-L', 'Xanh', 'L', 0.00, 8, 1, 0),
(117, 20, 'NK-DF-DEN-S', 'Đen', 'S', 0.00, 12, 1, 0),
(118, 20, 'NK-DF-DEN-M', 'Đen', 'M', 0.00, 15, 1, 0),
(119, 20, 'NK-DF-DEN-L', 'Đen', 'L', 0.00, 15, 1, 0),
(120, 20, 'NK-DF-DEN-XL', 'Đen', 'XL', 0.00, 12, 1, 0),
(121, 20, 'NK-DF-XANH-M', 'Xanh', 'M', 0.00, 8, 1, 0),
(122, 20, 'NK-DF-XANH-L', 'Xanh', 'L', 0.00, 8, 1, 0),
(123, 21, 'DL-PNL-HONG-S', 'Hồng', 'S', 0.00, 18, 1, 0),
(124, 21, 'DL-PNL-HONG-M', 'Hồng', 'M', 0.00, 22, 1, 0),
(125, 21, 'DL-PNL-HONG-L', 'Hồng', 'L', 0.00, 22, 1, 0),
(126, 21, 'DL-PNL-TRANG-S', 'Trắng', 'S', 0.00, 15, 1, 0),
(127, 21, 'DL-PNL-TRANG-M', 'Trắng', 'M', 0.00, 15, 1, 0),
(128, 21, 'DL-PNL-TIM-M', 'Tím', 'M', 0.00, 8, 1, 0),
(129, 22, 'UA-HGN-DEN-XS', 'Đen', 'XS', 0.00, 8, 1, 0),
(130, 22, 'UA-HGN-DEN-S', 'Đen', 'S', 0.00, 10, 1, 0),
(131, 22, 'UA-HGN-DEN-M', 'Đen', 'M', 0.00, 12, 1, 0),
(132, 22, 'UA-HGN-DEN-L', 'Đen', 'L', 0.00, 10, 1, 0),
(133, 22, 'UA-HGN-HONG-S', 'Hồng', 'S', 0.00, 5, 1, 0),
(134, 22, 'UA-HGN-HONG-M', 'Hồng', 'M', 0.00, 5, 1, 0),
(135, 23, 'AD-SB-DEN-S', 'Đen', 'S', 0.00, 8, 1, 0),
(136, 23, 'AD-SB-DEN-M', 'Đen', 'M', 0.00, 10, 1, 0),
(137, 23, 'AD-SB-DEN-L', 'Đen', 'L', 0.00, 10, 1, 0),
(138, 23, 'AD-SB-DO-S', 'Đỏ', 'S', 0.00, 7, 1, 0),
(139, 23, 'AD-SB-DO-M', 'Đỏ', 'M', 0.00, 5, 1, 0),
(140, 23, 'AD-SB-XANH-M', 'Xanh', 'M', 0.00, 5, 1, 0),
(141, 24, 'NK-IND-TRANG-XS', 'Trắng', 'XS', 0.00, 7, 1, 0),
(142, 24, 'NK-IND-TRANG-S', 'Trắng', 'S', 0.00, 8, 1, 0),
(143, 24, 'NK-IND-TRANG-M', 'Trắng', 'M', 0.00, 8, 1, 0),
(144, 24, 'NK-IND-HONG-S', 'Hồng', 'S', 0.00, 7, 1, 0),
(145, 24, 'NK-IND-HONG-M', 'Hồng', 'M', 0.00, 5, 1, 0),
(146, 24, 'NK-IND-TIM-M', 'Tím', 'M', 0.00, 5, 1, 0),
(147, 25, 'YNX-A99P-DO-4U', 'Do (4U)', '4U/G5', 0.00, 6, 1, 0),
(148, 25, 'YNX-A99P-DO-3U', 'Do (3U)', '3U/G4', 0.00, 5, 0, 1),
(149, 25, 'YNX-A99P-TRANG-4U', 'Trang (4U)', '4U/G5', 100000.00, 3, 1, 0),
(151, 26, 'YNX-NF700-XANH-4U', 'Xanh lá', '4U/G5', 0.00, 8, 1, 0),
(152, 26, 'YNX-NF700-XANH-3U', 'Xanh lá', '3U/G4', 0.00, 8, 1, 0),
(153, 26, 'YNX-NF700-DO-4U', 'Đỏ', '4U/G5', 50000.00, 5, 1, 0),
(154, 26, 'YNX-NF700-DO-3U', 'Đỏ', '3U/G4', 50000.00, 4, 1, 0),
(155, 27, 'LI-WS72-XANH-4U', 'Xanh đường', '4U', 0.00, 10, 1, 0),
(156, 27, 'LI-WS72-XANH-3U', 'Xanh đường', '3U', 0.00, 10, 1, 0),
(157, 27, 'LI-WS72-DO-4U', 'Đỏ', '4U', 0.00, 8, 1, 0),
(158, 27, 'LI-WS72-DO-3U', 'Đỏ', '3U', 0.00, 7, 1, 0),
(159, 28, 'DL-P500-DEN-3U', 'Đen', '3U', 0.00, 20, 1, 0),
(160, 28, 'DL-P500-DEN-2U', 'Đen', '2U', 0.00, 20, 1, 0),
(161, 28, 'DL-P500-XANH-3U', 'Xanh', '3U', 0.00, 10, 1, 0),
(162, 28, 'DL-P500-XANH-2U', 'Xanh', '2U', 0.00, 10, 1, 0),
(163, 29, 'DL-F200-DEN-3U', 'Đen', '3U', 0.00, 25, 1, 0),
(164, 29, 'DL-F200-DEN-2U', 'Đen', '2U', 0.00, 25, 1, 0),
(165, 29, 'DL-F200-DO-3U', 'Đỏ', '3U', 0.00, 15, 1, 0),
(166, 29, 'DL-F200-DO-2U', 'Đỏ', '2U', 0.00, 15, 1, 0),
(167, 30, 'HE-ZP-TRANG-4U', 'Trắng', '4U', 0.00, 8, 1, 0),
(168, 30, 'HE-ZP-TRANG-3U', 'Trắng', '3U', 0.00, 8, 1, 0),
(169, 30, 'HE-ZP-DEN-4U', 'Đen', '4U', 0.00, 6, 1, 0),
(170, 30, 'HE-ZP-DEN-3U', 'Đen', '3U', 0.00, 6, 1, 0),
(171, 31, 'YNX-AS05-L76', 'Vàng', 'Lop 76', 0.00, 60, 1, 0),
(172, 31, 'YNX-AS05-L77', 'Vàng', 'Lop 77', 0.00, 80, 1, 0),
(173, 31, 'YNX-AS05-L78', 'Vàng', 'Lop 78', 0.00, 60, 1, 0),
(174, 32, 'YNX-AS30-L76', 'Trắng', 'Lop 76', 0.00, 45, 1, 0),
(175, 32, 'YNX-AS30-L77', 'Trắng', 'Lop 77', 0.00, 60, 1, 0),
(176, 32, 'YNX-AS30-L78', 'Trắng', 'Lop 78', 0.00, 45, 1, 0),
(177, 33, 'DL-TR-L76', 'Vàng', 'Lop 76', 0.00, 80, 1, 0),
(178, 33, 'DL-TR-L77', 'Vàng', 'Lop 77', 0.00, 100, 1, 0),
(179, 33, 'DL-TR-L78', 'Vàng', 'Lop 78', 0.00, 80, 1, 0),
(180, 33, 'DL-TR-L79', 'Vàng', 'Lop 79', 0.00, 40, 1, 0),
(181, 34, 'LI-A90-L76', 'Trắng', 'Lop 76', 0.00, 50, 1, 0),
(182, 34, 'LI-A90-L77', 'Trắng', 'Lop 77', 0.00, 70, 1, 0),
(183, 34, 'LI-A90-L78', 'Trắng', 'Lop 78', 0.00, 60, 1, 0),
(184, 35, 'DL-QST-DEN-S', 'Đen', 'S', 0.00, 18, 1, 0),
(185, 35, 'DL-QST-DEN-M', 'Đen', 'M', 0.00, 22, 1, 0),
(186, 35, 'DL-QST-DEN-L', 'Đen', 'L', 0.00, 22, 1, 0),
(187, 35, 'DL-QST-DEN-XL', 'Đen', 'XL', 0.00, 18, 1, 0),
(188, 35, 'DL-QST-XANH-M', 'Xanh', 'M', 0.00, 10, 1, 0),
(189, 35, 'DL-QST-XANH-L', 'Xanh', 'L', 0.00, 10, 1, 0),
(190, 36, 'NK-QS5-DEN-S', 'Đen', 'S', 0.00, 10, 1, 0),
(191, 36, 'NK-QS5-DEN-M', 'Đen', 'M', 0.00, 12, 1, 0),
(192, 36, 'NK-QS5-DEN-L', 'Đen', 'L', 0.00, 12, 1, 0),
(193, 36, 'NK-QS5-DEN-XL', 'Đen', 'XL', 0.00, 10, 1, 0),
(194, 36, 'NK-QS5-XANH-M', 'Xanh', 'M', 0.00, 8, 1, 0),
(195, 36, 'NK-QS5-XANH-L', 'Xanh', 'L', 0.00, 8, 1, 0),
(196, 37, 'DL-QSN-HONG-S', 'Hồng', 'S', 0.00, 15, 1, 0),
(197, 37, 'DL-QSN-HONG-M', 'Hồng', 'M', 0.00, 20, 1, 0),
(198, 37, 'DL-QSN-HONG-L', 'Hồng', 'L', 0.00, 20, 1, 0),
(199, 37, 'DL-QSN-DEN-M', 'Đen', 'M', 0.00, 15, 1, 0),
(200, 37, 'DL-QSN-DEN-L', 'Đen', 'L', 0.00, 15, 1, 0),
(201, 37, 'DL-QSN-TRANG-M', 'Trắng', 'M', 0.00, 5, 1, 0),
(202, 38, 'UA-LG-DEN-XS', 'Đen', 'XS', 0.00, 8, 1, 0),
(203, 38, 'UA-LG-DEN-S', 'Đen', 'S', 0.00, 10, 1, 0),
(204, 38, 'UA-LG-DEN-M', 'Đen', 'M', 0.00, 12, 1, 0),
(205, 38, 'UA-LG-DEN-L', 'Đen', 'L', 0.00, 10, 1, 0),
(206, 38, 'UA-LG-XANH-S', 'Xanh', 'S', 0.00, 5, 1, 0),
(207, 39, 'AD-LG-DEN-XS', 'Đen', 'XS', 0.00, 8, 1, 0),
(208, 39, 'AD-LG-DEN-S', 'Đen', 'S', 0.00, 10, 1, 0),
(209, 39, 'AD-LG-DEN-M', 'Đen', 'M', 0.00, 12, 1, 0),
(210, 39, 'AD-LG-DEN-L', 'Đen', 'L', 0.00, 10, 1, 0),
(211, 39, 'AD-LG-XANH-S', 'Xanh', 'S', 0.00, 5, 1, 0),
(212, 39, 'AD-LG-XANH-M', 'Xanh', 'M', 0.00, 5, 1, 0),
(213, 40, 'AD-AR-SO5', 'Trắng/Đen', 'Size 5', 0.00, 30, 1, 0),
(214, 41, 'DL-CL5-TRANG', 'Trắng', 'Size 5', 0.00, 80, 1, 0),
(215, 42, 'DL-FS-XANH', 'Xanh', 'Size 4', 0.00, 60, 1, 0),
(216, 43, 'DL-PB-VANG', 'Vàng neon', '40 lo', 0.00, 100, 1, 0),
(217, 44, 'DL-TA-1KG', 'Xanh', '1 kg', 0.00, 40, 1, 0),
(218, 44, 'DL-TA-2KG', 'Xanh', '2 kg', 25000.00, 40, 1, 0),
(219, 44, 'DL-TA-3KG', 'Xanh', '3 kg', 50000.00, 35, 1, 0),
(220, 44, 'DL-TA-5KG', 'Xanh', '5 kg', 100000.00, 30, 1, 0),
(221, 44, 'DL-TA-8KG', 'Xanh', '8 kg', 200000.00, 25, 1, 0),
(222, 44, 'DL-TA-10KG', 'Xanh', '10 kg', 300000.00, 20, 1, 0),
(223, 45, 'DL-MU-DEN-FS', 'Đen', 'Free size', 0.00, 25, 1, 0),
(224, 45, 'DL-MU-XANH-FS', 'Xanh navy', 'Free size', 0.00, 25, 1, 0),
(225, 45, 'DL-MU-DO-FS', 'Đỏ', 'Free size', 0.00, 15, 1, 0),
(226, 45, 'DL-MU-TRANG-FS', 'Trắng', 'Free size', 0.00, 15, 1, 0),
(227, 46, 'DL-BALO-DEN', 'Đen', '20L', 0.00, 15, 1, 0),
(228, 46, 'DL-BALO-XANH', 'Xanh navy', '20L', 0.00, 15, 1, 0),
(229, 46, 'DL-BALO-TRANG', 'Trắng', '20L', 0.00, 10, 1, 0),
(230, 47, 'YNX-TAT-TRANG-SS', 'Trắng', 'S/M', 0.00, 40, 1, 0),
(231, 47, 'YNX-TAT-TRANG-ML', 'Trắng', 'M/L', 0.00, 40, 1, 0),
(232, 47, 'YNX-TAT-DEN-SS', 'Đen', 'S/M', 0.00, 10, 1, 0),
(233, 47, 'YNX-TAT-DEN-ML', 'Đen', 'M/L', 0.00, 10, 1, 0),
(234, 48, 'DL-TAT-TRANG', 'Trắng', 'Free size', 0.00, 70, 1, 0),
(235, 48, 'DL-TAT-DEN', 'Đen', 'Free size', 0.00, 50, 1, 0),
(236, 48, 'DL-TAT-XANH', 'Xanh', 'Free size', 0.00, 30, 1, 0),
(237, 49, 'DL-MUN-TRANG-FS', 'Trắng', 'Free size', 0.00, 20, 1, 0),
(238, 49, 'DL-MUN-HONG-FS', 'Hồng', 'Free size', 0.00, 25, 1, 0),
(239, 49, 'DL-MUN-DEN-FS', 'Đen', 'Free size', 0.00, 15, 1, 0),
(240, 50, 'AD-BN-DEN', 'Đen', '22L', 0.00, 10, 1, 0),
(241, 50, 'AD-BN-TRANG', 'Trắng', '22L', 0.00, 10, 1, 0),
(242, 50, 'AD-BN-HONG', 'Hồng', '22L', 0.00, 10, 1, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_view_logs`
--

CREATE TABLE `product_view_logs` (
  `view_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lich su xem san pham (ho tro AI goi y)';

--
-- Đang đổ dữ liệu cho bảng `product_view_logs`
--

REPLACE INTO `product_view_logs` (`view_id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 3, 1, '2026-05-29 19:17:55'),
(2, 3, 3, '2026-05-29 19:17:55'),
(3, 3, 5, '2026-05-29 19:17:55'),
(4, 3, 11, '2026-05-29 19:17:55'),
(5, 3, 25, '2026-05-29 19:17:55'),
(6, 3, 26, '2026-05-29 19:17:55'),
(7, 3, 31, '2026-05-29 19:17:55'),
(8, 3, 32, '2026-05-29 19:17:55'),
(9, 4, 25, '2026-05-29 19:17:55'),
(10, 4, 26, '2026-05-29 19:17:55'),
(11, 4, 14, '2026-05-29 19:17:55'),
(12, 4, 38, '2026-05-29 19:17:55'),
(13, 4, 23, '2026-05-29 19:17:55'),
(14, 4, 19, '2026-05-29 19:17:55'),
(15, 4, 16, '2026-05-29 19:17:55'),
(16, 5, 3, '2026-05-29 19:17:55'),
(17, 5, 4, '2026-05-29 19:17:55'),
(18, 5, 5, '2026-05-29 19:17:55'),
(19, 5, 25, '2026-05-29 19:17:55'),
(20, 5, 27, '2026-05-29 19:17:55'),
(21, 5, 31, '2026-05-29 19:17:55'),
(22, 5, 32, '2026-05-29 19:17:55'),
(23, 5, 33, '2026-05-29 19:17:55'),
(24, 6, 40, '2026-05-29 19:17:55'),
(25, 6, 41, '2026-05-29 19:17:55'),
(26, 6, 6, '2026-05-29 19:17:55'),
(27, 6, 8, '2026-05-29 19:17:55'),
(28, 6, 9, '2026-05-29 19:17:55'),
(29, 6, 45, '2026-05-29 19:17:55'),
(30, 6, 46, '2026-05-29 19:17:55'),
(31, 7, 6, '2026-05-29 19:17:55'),
(32, 7, 7, '2026-05-29 19:17:55'),
(33, 7, 10, '2026-05-29 19:17:55'),
(34, 7, 40, '2026-05-29 19:17:55'),
(35, 7, 44, '2026-05-29 19:17:55'),
(36, 7, 35, '2026-05-29 19:17:55'),
(37, 7, 36, '2026-05-29 19:17:55'),
(38, 8, 38, '2026-05-29 19:17:55'),
(39, 8, 39, '2026-05-29 19:17:55'),
(40, 8, 21, '2026-05-29 19:17:55'),
(41, 8, 22, '2026-05-29 19:17:55'),
(42, 8, 23, '2026-05-29 19:17:55'),
(43, 8, 24, '2026-05-29 19:17:55'),
(44, 8, 37, '2026-05-29 19:17:55'),
(45, 8, 50, '2026-05-29 19:17:55'),
(51, 1, 44, '2026-06-05 13:20:13'),
(52, 1, 1, '2026-06-05 13:23:33'),
(53, 1, 2, '2026-06-05 13:23:44'),
(56, 1, 5, '2026-06-05 13:27:18'),
(57, 1, 2, '2026-06-05 15:49:11'),
(60, 1, 25, '2026-06-05 16:03:46'),
(61, 1, 25, '2026-06-05 16:32:15'),
(62, 1, 25, '2026-06-05 16:34:00'),
(63, 1, 31, '2026-06-05 16:34:04'),
(64, 1, 25, '2026-06-05 16:34:07'),
(65, 1, 25, '2026-06-05 16:34:58'),
(66, 1, 25, '2026-06-05 16:39:08'),
(67, 1, 5, '2026-06-05 16:41:47'),
(68, 1, 25, '2026-06-06 01:26:27'),
(71, 1, 25, '2026-06-07 19:29:26'),
(72, 1, 25, '2026-06-07 19:38:59'),
(73, 1, 25, '2026-06-07 19:43:53'),
(74, 1, 25, '2026-06-07 19:44:52'),
(75, 1, 25, '2026-06-07 19:49:38'),
(76, 1, 25, '2026-06-07 19:53:58'),
(77, 1, 31, '2026-06-08 19:22:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `promo_id` int(10) UNSIGNED NOT NULL,
  `promo_code` varchar(50) NOT NULL,
  `promo_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent' COMMENT 'percent=%, fixed=so tien co dinh',
  `discount_value` decimal(12,2) NOT NULL COMMENT 'Gia tri giam gia',
  `min_order_value` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Gia tri don toi thieu de ap dung',
  `max_discount` decimal(12,2) DEFAULT NULL COMMENT 'Giam toi da (dung khi type=percent)',
  `usage_limit` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = khong gioi han',
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `promotions`
--

REPLACE INTO `promotions` (`promo_id`, `promo_code`, `promo_name`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Chào mừng thành viên mới', NULL, 'fixed', 100000.00, 200000.00, 50000.00, 1000, 0, '2026-01-01 00:00:00', '2026-12-31 00:00:00', 1, '2026-05-29 19:17:55', '2026-06-05 13:11:49'),
(2, 'SALE20', 'Khuyến mãi 20%', NULL, 'percent', 20.00, 500000.00, 100000.00, 500, 0, '2025-06-01 00:00:00', '2025-12-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 'FREESHIP', 'Miễn phí vận chuyển', NULL, 'fixed', 50000.00, 300000.00, NULL, 300, 0, '2025-07-01 00:00:00', '2025-09-30 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 'SUMMER100K', 'Hè nóng giảm 100K', NULL, 'fixed', 100000.00, 800000.00, NULL, 200, 0, '2025-06-01 00:00:00', '2025-08-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(5, 'VIP30', 'Ưu đãi khách hàng VIP', NULL, 'percent', 30.00, 1000000.00, 200000.00, 50, 0, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(6, 'GIAM5', 'giảm 5%', NULL, 'percent', 5.00, 0.00, NULL, 10, 2, '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, '2026-06-05 16:03:27', '2026-06-05 16:33:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(4) NOT NULL COMMENT 'Diem danh gia 1-5',
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','hidden') NOT NULL DEFAULT 'pending' COMMENT 'Admin duyet binh luan',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

REPLACE INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 5, 'Sản phẩm rất xịn, đế giày êm ái, đi chạy 10km không thấy mỏi chân. Hàng thật chất lượng tốt lắm!', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(2, 1, 4, 4, 'Giày đẹp, đủ size, giao hàng nhanh. Chỉ trừ là hộp quá chút.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 1, 5, 5, 'Đã mua lần 2 vì quá xài lần đầu thích lắm. Điều kiện giao hàng tốt.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 3, 6, 5, 'Giày Nike nhẹ, đẹp, ôm chân rất vừa. Xứng đáng với giá tiền.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(5, 3, 7, 4, 'Chất lượng tốt, thiết kế hiện đại. Gì giao hàng hơi lâu một chút.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(6, 3, 3, 5, 'Đã từng dùng nhiều dòng giày khác nhau, Revolution 7 là dòng kinh tế nhất mà chất lượng thực sự ok.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(7, 5, 4, 5, 'Mua cho chồng tập marathon, anh ấy rất thích, nói là đếm tốt hơn tất cả giày cũ. Giao hàng nhanh.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(8, 5, 8, 5, 'Giày chạy bộ đỉnh nhất tôi từng dùng. Gel phần sau giảm sốc rất tốt. Rất đáng tiền.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(9, 25, 3, 5, 'Vợt cầu lông đỉnh của đỉnh, cần đà tốt, góp chơi của tôi rất nhiều. Hàng chính hãng 100%.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(10, 25, 5, 5, 'Mua tặng anh trai sinh nhật, anh ấy mê lắm. Đóng gói đẹp, có chứng nhận hàng chính hãng.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(11, 25, 7, 4, 'Vợt rất tốt nhưng giá hơi cao. Nếu dùng cho chuyên nghiệp thì xứng đáng đầu tư.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(12, 26, 4, 5, 'Vợt phản công siêu nhanh, day chac, cam tay vua. Rat hai long.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(13, 26, 6, 4, 'Vợt tốt, nhẹ, phù hợp người chơi phản công. Ship nhanh.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(14, 28, 8, 4, 'Vợt giá tốt mà chơi được, phù hợp người mới bắt đầu như mình. Nên mua.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(15, 28, 7, 3, 'Vợt ok cho giá tiền, nhưng khung hơi bị rung, có lẽ do mình chơi mạnh.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(16, 31, 3, 5, 'Cầu bay đẹp, độ bền khá ổn, mua nhiều lần rồi. Giá tốt.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(17, 31, 5, 4, 'Dùng được khoảng 30-40 hiệu, không tệ. Nảy rất ổn cho cầu giao lưu.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(18, 16, 4, 5, 'Vải đẹp, thoáng mát, màu không bị bám sau nhiều lần giặt. Rất hài lòng.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(19, 16, 8, 4, 'Áo vừa vặn, chất lượng ok với mức giá. Khuyên bạn nên mua.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(20, 38, 4, 5, 'Quần chất, ôm sát nhưng không bị bó. Dùng cho yoga hàng ngày. Ship nhanh!', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(21, 38, 6, 5, 'Chất lượng rất tốt, giá hợp lý. Mua lần thứ 3 rồi vẫn rất thích.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(22, 23, 4, 4, 'Áo ôm đẹp, hỗ trợ tốt, chất liệu thoáng. Giao hàng nhanh.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(23, 23, 8, 5, 'Mua tập yoga, chất lượng rất tốt, không bại sau nhiều buổi tập. Hài lòng.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(24, 44, 5, 4, 'Tạ chắc, bọc cao su không bị tuột, đem được lâu. Giá rẻ.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(25, 44, 7, 4, 'Mua loại 3kg, chất lượng ok. Cảm ơn shop.', 'approved', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(26, 1, 8, 2, 'Hàng bị lỗi, mũi giày bị tách. Cần kiểm tra lại chất lượng trước khi gửi hàng!', 'pending', '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(27, 25, 8, 1, 'Vợt bị bong dây khi mới mua, phải đợi 1 tuần mới xong. Rất phiền.', 'pending', '2026-05-29 19:17:55', '2026-05-29 19:17:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `roles`
--

CREATE TABLE `roles` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL COMMENT 'Ten vai tro: admin | staff | customer',
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai tro he thong';

--
-- Đang đổ dữ liệu cho bảng `roles`
--

REPLACE INTO `roles` (`role_id`, `role_name`, `description`) VALUES
(1, 'admin', 'Quản trị viên toàn quyền'),
(2, 'staff', 'Nhân viên xử lý đơn hàng'),
(3, 'customer', 'Khách hàng thông thường'),
(4, 'staff_accounts', 'Nhân viên QL tài khoản'),
(5, 'staff_products', 'Nhân viên QL sản phẩm'),
(6, 'staff_orders', 'Nhân viên QL đơn hàng'),
(7, 'staff_analytics', 'Nhân viên QL thống kê'),
(8, 'staff_cms', 'Nhân viên QL trang chủ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `search_logs`
--

CREATE TABLE `search_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL neu chua dang nhap',
  `keyword` varchar(255) NOT NULL,
  `result_count` int(11) DEFAULT NULL,
  `searched_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lich su tim kiem cua nguoi dung';

--
-- Đang đổ dữ liệu cho bảng `search_logs`
--

REPLACE INTO `search_logs` (`log_id`, `user_id`, `keyword`, `result_count`, `searched_at`) VALUES
(1, 3, 'giay chay bo', 5, '2026-05-29 19:17:55'),
(2, 3, 'yonex', 8, '2026-05-29 19:17:55'),
(3, 3, 'cau long', 12, '2026-05-29 19:17:55'),
(4, 4, 'vot cau long', 6, '2026-05-29 19:17:55'),
(5, 4, 'yonex astrox', 3, '2026-05-29 19:17:55'),
(6, 4, 'legging nu', 4, '2026-05-29 19:17:55'),
(7, 5, 'nike', 10, '2026-05-29 19:17:55'),
(8, 5, 'asics', 3, '2026-05-29 19:17:55'),
(9, 6, 'bong da adidas', 2, '2026-05-29 19:17:55'),
(10, 7, 'giay da bong', 5, '2026-05-29 19:17:55'),
(11, 8, 'sport bra', 4, '2026-05-29 19:17:55'),
(12, NULL, 'giay chay bo', 5, '2026-05-29 19:17:55'),
(13, NULL, 'vot cau long', 6, '2026-05-29 19:17:55'),
(14, NULL, 'ao the thao', 9, '2026-05-29 19:17:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `shipping_id` int(10) UNSIGNED NOT NULL,
  `method_name` varchar(100) NOT NULL COMMENT 'VD: Giao hang tieu chuan, Giao nhanh',
  `description` varchar(255) DEFAULT NULL,
  `base_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Phi co ban',
  `free_threshold` decimal(12,2) DEFAULT NULL COMMENT 'Mien phi ship khi don >= gia tri nay',
  `estimated_days` varchar(50) DEFAULT NULL COMMENT 'VD: 2-3 ngay lam viec',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shipping_methods`
--

REPLACE INTO `shipping_methods` (`shipping_id`, `method_name`, `description`, `base_fee`, `free_threshold`, `estimated_days`, `is_active`) VALUES
(1, 'Giao hàng tiêu chuẩn', 'Giao trong 3-5 ngày làm việc', 30000.00, 500000.00, '3-5 ngày làm việc', 1),
(2, 'Giao hàng nhanh', 'Giao trong 1-2 ngày làm việc', 50000.00, 800000.00, '1-2 ngày làm việc', 1),
(3, 'Giao hàng hoả tốc', 'Giao trong ngày (nội thành)', 80000.00, NULL, 'Trong ngày', 1),
(4, 'Nhận tại cửa hàng', 'Nhận trực tiếp tại cửa hàng', 0.00, NULL, 'Ngay lập tức', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_prices`
--

CREATE TABLE `shipping_prices` (
  `shipping_id` int(10) UNSIGNED NOT NULL,
  `province_city` varchar(150) NOT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 30000.00,
  `estimated_days` int(11) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shipping_prices`
--

REPLACE INTO `shipping_prices` (`shipping_id`, `province_city`, `base_price`, `estimated_days`, `created_at`, `updated_at`) VALUES
(1, 'TP. Hồ Chí Minh', 20000.00, 2, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(2, 'Hà Nội', 20000.00, 2, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(3, 'Đà Nẵng', 20000.00, 2, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(4, 'Hải Phòng', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(5, 'Cần Thơ', 25000.00, 3, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(7, 'Bà Rịa - Vũng Tàu', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(8, 'Bắc Giang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(9, 'Bắc Kạn', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(10, 'Bạc Liêu', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(11, 'Bắc Ninh', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(12, 'Bến Tre', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(13, 'Bình Định', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(14, 'Bình Dương', 25000.00, 3, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(15, 'Bình Phước', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(16, 'Bình Thuận', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(17, 'Cà Mau', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(18, 'Cao Bằng', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(19, 'Đắk Lắk', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(20, 'Đắk Nông', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(21, 'Điện Biên', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(22, 'Đồng Nai', 25000.00, 3, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(23, 'Đồng Tháp', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(24, 'Gia Lai', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(25, 'Hà Giang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(26, 'Hà Nam', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(27, 'Hà Tĩnh', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(28, 'Hải Dương', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(29, 'Hậu Giang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(30, 'Hòa Bình', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(31, 'Hưng Yên', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(32, 'Khánh Hòa', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(33, 'Kiên Giang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(34, 'Kon Tum', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(35, 'Lai Châu', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(36, 'Lâm Đồng', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(37, 'Lạng Sơn', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(38, 'Lào Cai', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(39, 'Long An', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(40, 'Nam Định', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(41, 'Nghệ An', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(42, 'Ninh Bình', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(43, 'Ninh Thuận', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(44, 'Phú Thọ', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(45, 'Phú Yên', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(46, 'Quảng Bình', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(47, 'Quảng Nam', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(48, 'Quảng Ngãi', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(49, 'Quảng Ninh', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(50, 'Quảng Trị', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(51, 'Sóc Trăng', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(52, 'Sơn La', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(53, 'Tây Ninh', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(54, 'Thái Bình', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(55, 'Thái Nguyên', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(56, 'Thanh Hóa', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(57, 'Thừa Thiên Huế', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(58, 'Tiền Giang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(59, 'Trà Vinh', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(60, 'Tuyên Quang', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(61, 'Vĩnh Long', 25000.00, 3, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(62, 'Vĩnh Phúc', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(63, 'Yên Bái', 35000.00, 4, '2026-06-05 16:13:22', '2026-06-05 16:13:22'),
(65, 'An Giang', 30000.00, 3, '2026-06-05 16:22:13', '2026-06-05 16:22:13');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Key cài đặt (unique)',
  `setting_value` text DEFAULT NULL COMMENT 'Giá trị cài đặt',
  `setting_type` enum('text','textarea','image','number','boolean','json') DEFAULT 'text' COMMENT 'Loại giá trị',
  `group_name` varchar(50) DEFAULT 'general' COMMENT 'Nhóm cài đặt: general, contact, social, footer',
  `display_name` varchar(255) DEFAULT NULL COMMENT 'Tên hiển thị trong admin',
  `description` varchar(500) DEFAULT NULL COMMENT 'Mô tả/giải thích cài đặt',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị',
  `is_public` tinyint(1) DEFAULT 1 COMMENT '1=hiển thị công khai, 0=chỉ admin thấy',
  `updated_by` int(10) UNSIGNED DEFAULT NULL COMMENT 'Admin cập nhật cuối',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng cài đặt website';

--
-- Đang đổ dữ liệu cho bảng `site_settings`
--

REPLACE INTO `site_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `group_name`, `display_name`, `description`, `sort_order`, `is_public`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'Axeron Sport', 'text', 'general', 'Tên Website', 'Tên website hiển thị trên trình duyệt và logo', 1, 1, NULL, '2026-06-05 12:54:12'),
(2, 'site_tagline', 'Thể Thao Đỉnh Cao - Phong Cách Thượng Lưu', 'text', 'general', 'Tagline', 'Khẩu hiệu ngắn của website', 2, 1, NULL, '2026-06-05 12:54:12'),
(3, 'site_logo', '', 'image', 'general', 'Logo Website', 'Logo chính của website (khuyến nghị: 200x60px)', 3, 1, NULL, '2026-06-05 12:54:12'),
(4, 'site_favicon', '', 'image', 'general', 'Favicon', 'Icon nhỏ hiển thị trên tab trình duyệt (32x32px)', 4, 1, NULL, '2026-06-05 12:54:12'),
(5, 'site_logo_alt', '', 'image', 'general', 'Logo Alternates', 'Logo cho chế độ nền tối (nếu cần)', 5, 1, NULL, '2026-06-05 12:54:12'),
(6, 'contact_email', 'contact@axeron.vn', 'text', 'contact', 'Email Liên Hệ', 'Email chính để khách hàng liên hệ', 10, 1, NULL, '2026-06-05 12:54:12'),
(7, 'contact_phone', '1900 1234', 'text', 'contact', 'Số Điện Thoại', 'Số hotline hoặc hotline hỗ trợ', 11, 1, NULL, '2026-06-05 12:54:12'),
(8, 'contact_phone_2', '', 'text', 'contact', 'SĐT Hỗ Trợ 2', 'Số điện thoại hỗ trợ thứ 2', 12, 1, NULL, '2026-06-05 12:54:12'),
(9, 'contact_address', '123 Nguyễn Trãi, Quận 1, TP.HCM', 'textarea', 'contact', 'Địa Chỉ', 'Địa chỉ trụ sở/cửa hàng chính', 13, 1, NULL, '2026-06-05 12:54:12'),
(10, 'contact_map_embed', '', 'textarea', 'contact', 'Google Maps Embed', 'Mã nhúng bản đồ Google Maps', 14, 1, NULL, '2026-06-05 12:54:12'),
(11, 'contact_work_hours', 'Thứ 2 - Thứ 7: 8:00 - 20:00', 'text', 'contact', 'Giờ Làm Việc', 'Giờ làm việc của cửa hàng', 15, 1, NULL, '2026-06-05 12:54:12'),
(12, 'social_facebook', '', 'text', 'social', 'Facebook', 'Link fanpage Facebook', 20, 1, NULL, '2026-06-05 12:54:12'),
(13, 'social_instagram', '', 'text', 'social', 'Instagram', 'Link Instagram', 21, 1, NULL, '2026-06-05 12:54:12'),
(14, 'social_youtube', '', 'text', 'social', 'YouTube', 'Link YouTube channel', 22, 1, NULL, '2026-06-05 12:54:12'),
(15, 'social_tiktok', '', 'text', 'social', 'TikTok', 'Link TikTok', 23, 1, NULL, '2026-06-05 12:54:12'),
(16, 'social_zalo', '', 'text', 'social', 'Zalo', 'Link Zalo Official Account', 24, 1, NULL, '2026-06-05 12:54:12'),
(17, 'footer_about', 'Chuyên cung cấp các sản phẩm thể thao chính hãng từ các thương hiệu nổi tiếng thế giới. Cam kết 100% authentic, bảo hành chính hãng.', 'textarea', 'footer', 'Giới Thiệu Footer', 'Đoạn text giới thiệu ngắn hiển thị ở footer', 30, 1, NULL, '2026-06-05 12:54:12'),
(18, 'footer_copyright', '© 2024 Axeron Sport. Tất cả quyền được bảo lưu.', 'text', 'footer', 'Copyright Text', 'Text copyright ở footer', 31, 1, NULL, '2026-06-05 12:54:12'),
(19, 'footer_policy_links', '', 'textarea', 'footer', 'Footer Policy Links', 'JSON format: {\"privacy\": \"/policies/privacy-policy.php\", \"terms\": \"/policies/purchase-policy.php\"}', 32, 1, NULL, '2026-06-05 12:54:12');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT '3=customer, 2=staff, 1=admin',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Luu mat khau da bam (bcrypt/argon2)',
  `avatar_url` varchar(500) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=hoat dong, 0=bi khoa',
  `locked_until` datetime DEFAULT NULL COMMENT 'Khoa tam thoi sau nhieu lan dang nhap sai',
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'So lan dang nhap sai lien tiep',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Token ghi nhớ đăng nhập',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tai khoan nguoi dung';

--
-- Đang đổ dữ liệu cho bảng `users`
--

REPLACE INTO `users` (`user_id`, `role_id`, `full_name`, `email`, `phone`, `password_hash`, `avatar_url`, `gender`, `date_of_birth`, `is_active`, `locked_until`, `login_attempts`, `remember_token`, `email_verified`, `created_at`, `updated_at`) VALUES
(1, 1, 'Quản Trị Viên', 'admin@sportsshop.vn', '0901000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-06 01:21:33'),
(3, 3, 'Nguyễn Văn An', 'user@sportsshop.vn', '0912345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 3, 'Trần Thị Bích', 'bich.tran@gmail.com', '0923456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(5, 3, 'Lê Minh Cường', 'cuong.le@gmail.com', '0934567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(6, 3, 'Phạm Thị Dung', 'dung.pham@gmail.com', '0945678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(7, 3, 'Hoàng Văn Em', 'em.hoang@gmail.com', '0956789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(8, 3, 'Nguyễn Thị Phương', 'phuong.nt@gmail.com', '0967890123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(21, 4, 'Staff Tài Khoản', 'nvacc@sportsshop.vn', '0901000004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-06 03:41:36'),
(22, 5, 'Staff Sản Phẩm', 'nvsp@sportsshop.vn', '0901000005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-06 03:44:17'),
(23, 6, 'Staff Đơn Hàng', 'nvorder@sportsshop.vn', '0901000006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-06 03:42:11'),
(24, 7, 'Staff Thống Kê', 'nvtk@sportsshop.vn', '0901000007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-06 03:42:24'),
(25, 8, 'Staff CMS', 'nvcms@sportsshop.vn', '0901000008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, 1, NULL, 0, NULL, 1, '2026-06-06 03:16:46', '2026-06-06 03:41:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `province` varchar(100) NOT NULL COMMENT 'Tinh/Thanh pho',
  `district` varchar(100) NOT NULL COMMENT 'Quan/Huyen',
  `ward` varchar(100) NOT NULL COMMENT 'Phuong/Xa',
  `street_address` varchar(255) NOT NULL COMMENT 'So nha, ten duong',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dia chi giao hang';

--
-- Đang đổ dữ liệu cho bảng `user_addresses`
--

REPLACE INTO `user_addresses` (`address_id`, `user_id`, `recipient_name`, `phone`, `province`, `district`, `ward`, `street_address`, `is_default`, `created_at`) VALUES
(1, 3, 'Nguyễn Văn An', '0912345678', 'TP Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', '12 Nguyễn Huệ', 1, '2026-05-29 19:17:55'),
(2, 4, 'Trần Thị Bích', '0923456789', 'TP Hồ Chí Minh', 'Quận Bình Thạnh', 'Phường 25', '45 Xô Viết Nghệ Tĩnh', 1, '2026-05-29 19:17:55'),
(3, 5, 'Lê Minh Cường', '0934567890', 'Hà Nội', 'Cầu Giấy', 'Phường Dịch Vọng', '88 Trần Thái Tông', 1, '2026-05-29 19:17:55'),
(4, 6, 'Phạm Thị Dung', '0945678901', 'Đà Nẵng', 'Hải Châu', 'Phường Hải Châu 1', '23 Trần Phú', 1, '2026-05-29 19:17:55'),
(5, 7, 'Hoàng Văn Em', '0956789012', 'TP Hồ Chí Minh', 'Quận 7', 'Phường Tân Phong', '5 Nguyễn Văn Linh', 1, '2026-05-29 19:17:55'),
(6, 8, 'Nguyễn Thị Phương', '0967890123', 'TP Hồ Chí Minh', 'Thủ Đức', 'Phường Linh Trung', '100 Võ Văn Ngân', 1, '2026-05-29 19:17:55');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_customer_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_customer_stats` (
`user_id` int(10) unsigned
,`full_name` varchar(100)
,`email` varchar(150)
,`total_orders` bigint(21)
,`total_spent` decimal(36,2)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_revenue_monthly`
-- (See below for the actual view)
--
CREATE TABLE `v_revenue_monthly` (
`year` int(4)
,`month` int(2)
,`total_orders` bigint(21)
,`revenue` decimal(36,2)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_top_products`
-- (See below for the actual view)
--
CREATE TABLE `v_top_products` (
`product_id` int(10) unsigned
,`product_name` varchar(200)
,`total_sold` decimal(32,0)
,`total_revenue` decimal(36,2)
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_customer_stats`
--
DROP TABLE IF EXISTS `v_customer_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_customer_stats`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, count(`o`.`order_id`) AS `total_orders`, sum(`o`.`total_amount`) AS `total_spent` FROM (`users` `u` left join `orders` `o` on(`u`.`user_id` = `o`.`user_id` and `o`.`order_status` not in ('cancelled','returned'))) WHERE `u`.`role_id` = 3 GROUP BY `u`.`user_id`, `u`.`full_name`, `u`.`email` ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_revenue_monthly`
--
DROP TABLE IF EXISTS `v_revenue_monthly`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_revenue_monthly`  AS SELECT year(`o`.`created_at`) AS `year`, month(`o`.`created_at`) AS `month`, count(`o`.`order_id`) AS `total_orders`, sum(`o`.`total_amount`) AS `revenue` FROM `orders` AS `o` WHERE `o`.`order_status` not in ('cancelled','returned') AND `o`.`payment_status` = 'paid' GROUP BY year(`o`.`created_at`), month(`o`.`created_at`) ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_top_products`
--
DROP TABLE IF EXISTS `v_top_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_top_products`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_name` AS `product_name`, sum(`oi`.`quantity`) AS `total_sold`, sum(`oi`.`subtotal`) AS `total_revenue` FROM (((`order_items` `oi` join `product_variants` `pv` on(`oi`.`variant_id` = `pv`.`variant_id`)) join `products` `p` on(`pv`.`product_id` = `p`.`product_id`)) join `orders` `o` on(`oi`.`order_id` = `o`.`order_id`)) WHERE `o`.`order_status` not in ('cancelled','returned') GROUP BY `p`.`product_id`, `p`.`product_name` ORDER BY sum(`oi`.`quantity`) DESC ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`),
  ADD UNIQUE KEY `uk_slug` (`slug`),
  ADD KEY `idx_published` (`is_published`,`published_at`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_author` (`author_id`);
ALTER TABLE `articles` ADD FULLTEXT KEY `ft_search` (`title`,`excerpt`,`content`);

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`banner_id`),
  ADD KEY `idx_active_position` (`is_active`,`position`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`),
  ADD UNIQUE KEY `brand_name` (`brand_name`);

--
-- Chỉ mục cho bảng `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD UNIQUE KEY `uq_cart_variant` (`cart_id`,`variant_id`),
  ADD KEY `fk_cartitem_variant` (`variant_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_slug` (`slug`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_msg_session` (`session_id`);

--
-- Chỉ mục cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `fk_chat_user` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_user` (`user_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_order_date` (`created_at`),
  ADD KEY `fk_order_shipping` (`shipping_id`),
  ADD KEY `fk_order_promo` (`promo_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_oitem_order` (`order_id`),
  ADD KEY `idx_oitem_variant` (`variant_id`);

--
-- Chỉ mục cho bảng `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_log_order` (`order_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_reset_token` (`reset_token`),
  ADD KEY `idx_otp_code` (`otp_code`);

--
-- Chỉ mục cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`txn_id`),
  ADD KEY `idx_txn_order` (`order_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_brand` (`brand_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_visible` (`is_visible`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Chỉ mục cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_variant_product` (`product_id`);

--
-- Chỉ mục cho bảng `product_view_logs`
--
ALTER TABLE `product_view_logs`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `idx_view_user` (`user_id`),
  ADD KEY `idx_view_product` (`product_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `promo_code` (`promo_code`),
  ADD KEY `idx_promo_code` (`promo_code`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_review_product` (`product_id`),
  ADD KEY `idx_review_user` (`user_id`),
  ADD KEY `idx_review_status` (`status`);

--
-- Chỉ mục cho bảng `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Chỉ mục cho bảng `search_logs`
--
ALTER TABLE `search_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_search_user` (`user_id`);

--
-- Chỉ mục cho bảng `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`shipping_id`);

--
-- Chỉ mục cho bảng `shipping_prices`
--
ALTER TABLE `shipping_prices`
  ADD PRIMARY KEY (`shipping_id`),
  ADD UNIQUE KEY `province_city` (`province_city`);

--
-- Chỉ mục cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `uk_setting_key` (`setting_key`),
  ADD KEY `idx_group` (`group_name`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role_id`);

--
-- Chỉ mục cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;


--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=315;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;


--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;


--
-- AUTO_INCREMENT cho bảng `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `txn_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;


--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;


--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;


--
-- AUTO_INCREMENT cho bảng `product_view_logs`
--
ALTER TABLE `product_view_logs`
  MODIFY `view_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;


--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `search_logs`
--
ALTER TABLE `search_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `shipping_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `shipping_prices`
--
ALTER TABLE `shipping_prices`
  MODIFY `shipping_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT cho bảng `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `setting_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cartitem_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cartitem_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_msg_session` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_promo` FOREIGN KEY (`promo_id`) REFERENCES `promotions` (`promo_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_shipping` FOREIGN KEY (`shipping_id`) REFERENCES `shipping_prices` (`shipping_id`),
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oitem_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_oitem_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `order_status_logs`
--
ALTER TABLE `order_status_logs`
  ADD CONSTRAINT `fk_log_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `fk_txn_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_img_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `product_view_logs`
--
ALTER TABLE `product_view_logs`
  ADD CONSTRAINT `fk_view_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_view_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `search_logs`
--
ALTER TABLE `search_logs`
  ADD CONSTRAINT `fk_search_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
