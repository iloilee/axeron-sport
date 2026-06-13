-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th6 13, 2026 lúc 09:17 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

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

INSERT INTO `articles` (`article_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `tags`, `author_id`, `author_name`, `is_featured`, `is_published`, `published_at`, `view_count`, `meta_title`, `meta_description`, `meta_keywords`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Top 10 Giày Chạy Bộ Tốt Nhất 2024', 'top-10-giay-chay-bo-2024', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024 với công nghệ tiên tiến nhất.', '<h2>Giới thiệu</h2><p>Năm 2024 hứa hẹn là năm của những đôi giày chạy bộ công nghệ cao với nhiều cải tiến vượt bậc. Dưới đây là top 10 sản phẩm đáng mua nhất.</p><h2>1. Nike Alphafly 3</h2><p>Đôi giày định hình lại ngành giày chạy bộ với công nghệ ZoomX foam và Air Zoom pods.</p><h2>2. Adidas Ultraboost Light</h2><p>Phiên bản nhẹ hơn, êm hơn với Boost foam thế hệ mới.</p>', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', 'blog', NULL, NULL, 'Axeron Team', 1, 1, '2026-06-05 12:54:12', 2, 'Top 10 Giày Chạy Bộ Tốt Nhất 2024 | Axeron Sport', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024.', NULL, 0, '2026-06-05 12:54:12', '2026-06-10 00:30:45'),
(2, 'Hướng Dẫn Chọn Size Giày Thể Thao', 'huong-dan-chon-size-giay', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ, mẹo đo chân tại nhà để chọn được đôi giày vừa vặn.', '<h2>Tại sao chọn đúng size quan trọng?</h2><p>Giày vừa vặn giúp bạn thoải mái khi vận động, tránh chấn thương và tối ưu hiệu suất tập luyện.</p><h2>Cách đo chân</h2><p>1. Đứng lên tờ giấy và vẽ viền chân<br>2. Đo khoảng cách từ gót đến ngón dài nhất<br>3. Cộng thêm 0.5-1cm để có không gian thoải mái</p>', 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', 'guide', NULL, NULL, 'Axeron Team', 1, 1, '2026-06-05 12:54:12', 3, 'Hướng Dẫn Chọn Size Giày Thể Thao | Axeron Sport', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ.', NULL, 0, '2026-06-05 12:54:12', '2026-06-10 00:30:45'),
(3, 'Axeron Khai Trương Cửa Hàng Mới', 'axeron-khai-truong-cua-hang-moi', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi hấp dẫn dành cho khách hàng.', '<h2>Grand Opening Event</h2><p>Axeron Sport hân hạnh thông báo khai trương cửa hàng mới tại 456 Nguyễn Thị Thập, Quận 7, TP.HCM.</p><h2>Ưu đãi khai trương</h2><ul><li>Giảm 20% toàn bộ sản phẩm</li><li>Miễn phí vận chuyển cho đơn từ 500K</li><li>Tặng voucher 200K cho 100 khách hàng đầu tiên</li></ul>', 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?q=80&w=800&auto=format&fit=crop', 'announcement', NULL, NULL, 'Axeron Team', 0, 1, '2026-06-05 12:54:12', 0, 'Axeron Khai Trương Cửa Hàng Mới | Axeron Sport', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi.', NULL, 0, '2026-06-05 12:54:12', '2026-06-10 00:30:45'),
(4, 'Sân vận động', 'stadiums', 'Khám phá các dự án sân vận động tiêu chuẩn quốc tế mà Axeron đã tham gia thi công và cung cấp trang thiết bị.', '<p>Nội dung chi tiết đang được cập nhật...</p>', 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?q=80&w=1200&auto=format&fit=crop', '', NULL, NULL, NULL, 0, 1, NULL, 0, NULL, NULL, NULL, 0, '2026-06-10 00:25:12', '2026-06-10 00:25:12'),
(5, 'Nhà thi đấu', 'arenas', 'Các dự án nhà thi đấu đa năng được trang bị hệ thống ánh sáng, âm thanh và sàn gỗ thể thao cao cấp từ Axeron.', '<p>Nội dung chi tiết đang được cập nhật...</p>', 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?q=80&w=1200&auto=format&fit=crop', '', NULL, NULL, NULL, 0, 1, NULL, 0, NULL, NULL, NULL, 0, '2026-06-10 00:25:12', '2026-06-10 00:25:12'),
(6, 'Đồng phục học sinh - sinh viên', 'school-uniforms', 'Thiết kế và sản xuất đồng phục thể dục, đồng phục lớp cho các trường đại học và phổ thông trên toàn quốc.', '<p>Nội dung chi tiết đang được cập nhật...</p>', 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?q=80&w=1200&auto=format&fit=crop', '', NULL, NULL, NULL, 0, 1, NULL, 0, NULL, NULL, NULL, 0, '2026-06-10 00:25:12', '2026-06-10 00:25:12'),
(7, 'Thiết bị phòng tập', 'gym-equipment', 'Cung cấp và lắp đặt thiết bị gym chuyên nghiệp cho các trung tâm thể hình với công nghệ hiện đại nhất.', '<p>Nội dung chi tiết đang được cập nhật...</p>', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200&auto=format&fit=crop', '', NULL, NULL, NULL, 0, 1, NULL, 1, NULL, NULL, NULL, 0, '2026-06-10 00:25:12', '2026-06-11 10:18:18');

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

INSERT INTO `banners` (`banner_id`, `title`, `subtitle`, `image_url`, `image_url_mobile`, `link_url`, `link_type`, `target_id`, `button_text`, `position`, `is_active`, `start_date`, `end_date`, `click_count`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Siêu Sale Mùa Hè', 'Giảm đến 50% cho các sản phẩm thể thao', 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?q=80&w=1920&auto=format&fit=crop', NULL, 'url', NULL, 'Mua Ngay', 1, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-10 00:33:58'),
(2, 'Colección Adidas Mới', 'Công nghệ Boost đỉnh cao - Thiết kế hiện đại', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=1920&auto=format&fit=crop', NULL, 'category', NULL, 'Khám Phá Ngay', 2, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-10 00:33:58'),
(3, 'Giày Chạy Bộ Pro', 'Hỗ trợ tối đa - Cực kỳ nhẹ', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=1920&auto=format&fit=crop', NULL, 'url', NULL, 'Xem Chi Tiết', 3, 1, NULL, NULL, 0, NULL, '2026-06-05 12:54:12', '2026-06-10 00:33:58'),
(4, 'Bùng Nổ Đam Mê Bóng Đá', 'Trang bị trọn bộ thiết bị và giày bóng đá chuyên nghiệp', 'https://images.unsplash.com/photo-1511886929837-354d827aae26?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1511886929837-354d827aae26?q=80&w=1920&auto=format&fit=crop', '/shop/product-catalog.php?category=bong-da', '', NULL, 'Mua ngay', 4, 1, NULL, NULL, 0, NULL, '2026-06-10 00:33:58', '2026-06-10 00:33:58'),
(5, 'Cầu Lông Đỉnh Cao', 'Khám phá bộ sưu tập vợt và giày cầu lông mới nhất', 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?q=80&w=1920&auto=format&fit=crop', '/shop/product-catalog.php?category=cau-long', '', NULL, 'Khám phá', 5, 1, NULL, NULL, 0, NULL, '2026-06-10 00:33:58', '2026-06-10 00:36:28'),
(6, 'Gym & Fitness', 'Nâng tầm sức mạnh với thiết bị tập gym đẳng cấp', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1920&auto=format&fit=crop', 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1920&auto=format&fit=crop', '/shop/product-catalog.php?category=gym-fitness', '', NULL, 'Xem chi tiết', 6, 1, NULL, NULL, 0, NULL, '2026-06-10 00:33:58', '2026-06-10 00:33:58');

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

INSERT INTO `brands` (`brand_id`, `brand_name`, `logo_url`, `description`, `is_active`) VALUES
(1, 'Động Lực', '', '', 1),
(2, 'Nike', NULL, NULL, 1),
(3, 'Adidas', NULL, NULL, 1),
(4, 'Yonex', NULL, NULL, 1),
(5, 'Lining', NULL, NULL, 1),
(6, 'Puma', NULL, NULL, 1),
(7, 'Under Armour', NULL, NULL, 1),
(8, 'Mizuno', NULL, NULL, 1),
(9, 'Asics', NULL, NULL, 1),
(10, 'Head', NULL, NULL, 1),
(11, 'Axeron', NULL, NULL, 1);

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

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 12, '2026-05-29 19:17:55', '2026-06-11 01:04:10'),
(2, 8, '2026-05-29 19:17:55', '2026-06-11 01:04:10'),
(3, 9, '2026-05-29 19:17:55', '2026-06-11 01:04:10'),
(13, 1, '2026-06-05 15:51:29', '2026-06-11 01:04:10'),
(16, 2, '2026-06-11 01:07:17', '2026-06-11 01:07:17'),
(17, 3, '2026-06-11 01:10:11', '2026-06-11 01:10:11'),
(19, 34, '2026-06-13 14:40:33', '2026-06-13 14:40:33');

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

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `variant_id`, `quantity`, `added_at`) VALUES
(4, 2, 204, 1, '2026-05-29 19:17:55'),
(5, 2, 136, 1, '2026-05-29 19:17:55'),
(6, 3, 14, 1, '2026-05-29 19:17:55'),
(7, 3, 95, 2, '2026-05-29 19:17:55'),
(20, 13, 173, 1, '2026-06-08 19:22:41'),
(21, 13, 147, 1, '2026-06-08 19:22:46'),
(26, 1, 249, 2, '2026-06-11 00:44:13'),
(27, 17, 6, 1, '2026-06-11 01:10:12'),
(33, 13, 250, 1, '2026-06-11 03:03:11');

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

INSERT INTO `categories` (`category_id`, `parent_id`, `category_name`, `slug`, `description`, `image_url`, `sort_order`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Nam', 'nam', '', NULL, 10, 1, '2026-05-29 19:17:55', '2026-06-11 02:16:54'),
(2, NULL, 'Nữ', 'nu', NULL, NULL, 20, 1, '2026-05-29 19:17:55', '2026-06-10 00:18:05'),
(3, NULL, 'Thể Thao', 'the-thao', NULL, NULL, 30, 1, '2026-05-29 19:17:55', '2026-06-10 00:18:05'),
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
(100, 318, 'Giày Đá Bóng', 'giay-da-bong', NULL, NULL, 1, 1, '2026-05-29 19:17:55', '2026-06-10 00:15:37'),
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
(315, 318, 'Giày Pickleball', 'giay-pickleball', NULL, NULL, 0, 1, '2026-06-10 00:10:10', '2026-06-10 00:15:37'),
(316, 318, 'Giày cầu lông', 'giay-cau-long', NULL, NULL, 0, 1, '2026-06-10 00:10:10', '2026-06-10 00:15:37'),
(317, 318, 'Giày chạy bộ', 'giay-chay-bo', NULL, NULL, 0, 1, '2026-06-10 00:10:10', '2026-06-10 00:15:37'),
(318, NULL, 'Giày Thể Thao', 'giay-the-thao', NULL, NULL, 45, 1, '2026-06-10 00:15:37', '2026-06-10 00:18:48'),
(319, 12, 'Bộ Suvec Nam', 'bo-suvec-nam', NULL, NULL, 1, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(320, 12, 'Bộ Bóng Đá Nam', 'bo-bong-da-nam', NULL, NULL, 2, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(321, 12, 'Bộ Cầu Lông Nam', 'bo-cau-long-nam', NULL, NULL, 3, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(322, 12, 'Bộ Bóng Chuyền Nam', 'bo-bong-chuyen-nam', NULL, NULL, 4, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(323, 12, 'Bộ Pickleball Nam', 'bo-pickleball-nam', NULL, NULL, 5, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(324, 13, 'Mũ Nam', 'mu-nam', NULL, NULL, 6, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(325, 13, 'Balo Nam', 'balo-nam', NULL, NULL, 7, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(326, 13, 'Tất Nam', 'tat-nam', NULL, NULL, 8, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(327, 22, 'Bộ Suvec Nữ', 'bo-suvec-nu', NULL, NULL, 9, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(328, 22, 'Bộ Cầu Lông Nữ', 'bo-cau-long-nu', NULL, NULL, 10, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(329, 22, 'Bộ Bóng Chuyền Nữ', 'bo-bong-chuyen-nu', NULL, NULL, 11, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(330, 22, 'Bộ Pickleball Nữ', 'bo-pickleball-nu', NULL, NULL, 12, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(331, 23, 'Mũ Nữ', 'mu-nu', NULL, NULL, 13, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(332, 23, 'Balo Nữ', 'balo-nu', NULL, NULL, 14, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(333, 23, 'Tất Nữ', 'tat-nu', NULL, NULL, 15, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08');

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

INSERT INTO `chat_messages` (`message_id`, `session_id`, `sender_type`, `content`, `sent_at`) VALUES
(1, 1, 'user', 'Cho tôi hỏi vợt Yonex Astrox 99 Pro có hàng thật không ạ?', '2026-05-29 19:17:55'),
(2, 1, 'bot', 'Dạ, Yonex Astrox 99 Pro tại cửa hàng là hàng chính hãng 100%, có tem chống hàng giả và chứng nhận phân phối chính thức. Bạn cần hỗ trợ thêm gì không?', '2026-05-29 19:17:55'),
(3, 1, 'user', 'Ok cảm ơn, vậy tôi muốn đặt hàng', '2026-05-29 19:17:55'),
(4, 1, 'staff', 'Chào bạn! Bạn có thể thực hiện đặt hàng trực tiếp trên website hoặc liên hệ hotline 1900xxxx để được hỗ trợ nhanh hơn nhé!', '2026-05-29 19:17:55'),
(5, 2, 'user', 'Giày size 41 có còn hàng không?', '2026-05-29 19:17:55'),
(6, 2, 'bot', 'Bên mình còn hàng size 41 của nhiều dòng sản phẩm. Bạn đang quan tâm sản phẩm nào? Giày chạy bộ, đá bóng hay cầu lông ạ?', '2026-05-29 19:17:55'),
(7, 3, 'user', 'Ship Hà Nội mất bao lâu vậy shop?', '2026-05-29 19:17:55'),
(8, 3, 'bot', 'Giao hàng tiêu chuẩn Hà Nội khoảng 3-5 ngày làm việc. Giao hàng nhanh 1-2 ngày. Bạn muốn chọn hình thức nào?', '2026-05-29 19:17:55'),
(9, 4, 'user', 'Xin chào', '2026-06-09 11:56:08'),
(10, 4, 'bot', 'Xin lỗi, hiện tại hệ thống AI đang bị quá tải yêu cầu hoặc đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cung cấp API Key mới hoặc quay lại sau!', '2026-06-09 11:56:08'),
(11, 5, 'user', 'Giày chạy bộ nào đang bán chạy nhất bên mình?', '2026-06-09 11:57:01'),
(12, 5, 'bot', 'Xin lỗi, hiện tại hệ thống AI đang bị quá tải yêu cầu hoặc đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cung cấp API Key mới hoặc quay lại sau!', '2026-06-09 11:57:02'),
(13, 6, 'user', 'Giá của đôi giày bóng đá rẻ nhất hiện tại là bao nhiêu?', '2026-06-09 11:59:34'),
(14, 6, 'bot', 'Xin lỗi, hiện tại hệ thống AI đang bị quá tải yêu cầu hoặc đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cung cấp API Key mới hoặc quay lại sau!', '2026-06-09 11:59:34'),
(15, 6, 'user', 'Tôi muốn mua giày chạy bộ.', '2026-06-09 12:42:51'),
(16, 6, 'bot', 'Xin lỗi, hiện tại hệ thống AI đang bị quá tải yêu cầu hoặc đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cung cấp API Key mới hoặc quay lại sau!', '2026-06-09 12:42:52'),
(17, 6, 'user', 'tôi muốn mua vợt', '2026-06-09 18:23:21'),
(18, 6, 'bot', 'Chào bạn, hiện tại Axeron Sport đang có sẵn sản phẩm vợt cầu lông sau:\n\n*   **Vợt cầu lông Yonex Astrox 99 Pro**\n    *   **Giá:** 4.500.000 VNĐ\n    *   **Tình trạng:** Còn hàng\n\nĐể đặt mua sản phẩm, bạn có thể ghé trực tiếp cửa hàng tại địa chỉ **123 Nguyễn Trãi, Quận 1, TP.HCM** hoặc liên hệ hotline **1900 1234** (8:00 - 20:00) để được hỗ trợ nhanh nhất nhé!', '2026-06-09 18:23:27'),
(19, 6, 'user', 'vợt cho nữ có không?', '2026-06-09 18:23:39'),
(20, 6, 'bot', 'Máy chủ AI của Google hiện đang bị quá tải (Service Unavailable). Vui lòng thử lại sau vài phút.', '2026-06-09 18:23:40'),
(21, 6, 'user', 'có bán giày chạy bộ không?', '2026-06-09 18:23:54'),
(22, 6, 'bot', 'Máy chủ AI của Google hiện đang bị quá tải (Service Unavailable). Vui lòng thử lại sau vài phút.', '2026-06-09 18:24:08'),
(23, 6, 'user', 'tôi muốn mua giày', '2026-06-10 00:42:29'),
(24, 6, 'bot', 'Dạ hiện tại Axeron Sport đang sẵn hàng rất nhiều mẫu giày thể thao chất lượng. Bạn có thể tham khảo các mẫu dưới đây:\n\n**Giày Chạy Bộ:**\n*   **Giày chạy bộ DL Speed Pro X1:** 890.000 VNĐ\n*   **Giày Nike Revolution 7:** 1.950.000 VNĐ\n*   **Giày ASICS Gel-Nimbus 26:** 3.200.000 VNĐ\n\n**Giày Đá Bóng:**\n*   **Giày đá bóng DL Striker FG 2024:** 650.000 VNĐ\n*   **Giày đá bóng Adidas Predator 24 FG:** 2.800.000 VNĐ\n\n**Giày Cầu Lông:**\n*   **Giày cầu lông Yonex Power Cushion 65Z3 (Nam):** 2.900.000 VNĐ\n*   **Giày cầu lông nữ Yonex SHB-01MXLX (Nữ):** 3.100.000 VNĐ\n\nTất cả các sản phẩm trên đều đang **còn hàng**. Bạn đang quan tâm đến dòng giày nào để em tư vấn chi tiết hơn ạ? Bạn cũng có thể liên hệ hotline **1900 1234** hoặc ghé cửa hàng tại **123 Nguyễn Trãi, Quận 1, TP.HCM** để thử giày trực tiếp nhé!', '2026-06-10 00:43:53'),
(25, 6, 'user', 'tôi muốn mua giày dưới 1 triệu', '2026-06-10 01:12:34'),
(26, 6, 'bot', 'Máy chủ AI của Google hiện đang bị quá tải (Service Unavailable). Vui lòng thử lại sau vài phút.', '2026-06-10 01:12:41'),
(27, 7, 'user', 'tôi muốn mua giày', '2026-06-11 14:54:03'),
(28, 7, 'bot', 'Chào anh/chị, Axeron Sport hiện đang có sẵn các mẫu giày thể thao chính hãng dưới đây để anh/chị lựa chọn:\n\n**Giày Chạy Bộ Nam:**\n*   **Giày chạy bộ DL Speed Pro X1**: 890.000 VNĐ\n*   **Giày Nike Revolution 7**: 1.950.000 VNĐ\n*   **Giày ASICS Gel-Nimbus 26**: 3.200.000 VNĐ\n\n**Giày Đá Bóng:**\n*   **Giày đá bóng DL Striker FG 2024**: 650.000 VNĐ\n*   **Giày đá bóng Adidas Predator 24 FG**: 2.800.000 VNĐ\n\n**Giày Cầu Lông:**\n*   **Giày cầu lông Yonex Power Cushion 65Z3** (Nam): 2.900.000 VNĐ\n*   **Giày cầu lông nữ Yonex SHB-01MXLX**: 3.100.000 VNĐ\n\nAnh/chị đang tìm kiếm giày cho bộ môn thể thao nào để em hỗ trợ tư vấn size và đặt hàng nhanh nhất ạ?', '2026-06-11 14:54:50'),
(29, 8, 'user', 'tôi muốn mua giày chạy bộ', '2026-06-11 14:57:46'),
(30, 8, 'bot', 'Chào bạn, Axeron Sport hiện đang có sẵn các mẫu giày chạy bộ nam chất lượng để bạn lựa chọn:\n\n1. **Giày chạy bộ DL Speed Pro X1** - Giá: **890.000 VNĐ** (Còn hàng)\n2. **Giày Nike Revolution 7** - Giá: **1.950.000 VNĐ** (Còn hàng)\n3. **Giày ASICS Gel-Nimbus 26** - Giá: **3.200.000 VNĐ** (Còn hàng)\n\nBạn quan tâm đến mẫu giày nào hoặc cần tư vấn thêm về size, hãy cho Axeron Sport biết nhé! Bạn cũng có thể liên hệ hotline **1900 1234** hoặc ghé trực tiếp cửa hàng tại **123 Nguyễn Trãi, Quận 1, TP.HCM** để thử giày trực tiếp.', '2026-06-11 14:57:55'),
(31, 8, 'user', 'giày nike ổn không', '2026-06-11 14:58:15'),
(32, 8, 'bot', 'Mẫu **Giày Nike Revolution 7** (giá **1.950.000 VNĐ**) là một dòng giày chạy bộ nam rất chất lượng và được ưa chuộng từ thương hiệu Nike nhờ thiết kế thể thao, năng động. \n\nĐể có cảm nhận thực tế nhất về độ êm và vừa vặn của giày, Axeron Sport khuyến khích bạn ghé trực tiếp cửa hàng tại **123 Nguyễn Trãi, Quận 1, TP.HCM** để thử giày. \n\nNếu cần tư vấn thêm về size, bạn cũng có thể liên hệ hotline **1900 1234** để nhân viên hỗ trợ nhanh nhất nhé!', '2026-06-11 14:58:28');

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

INSERT INTO `chat_sessions` (`session_id`, `user_id`, `status`, `started_at`, `ended_at`) VALUES
(1, 12, 'closed', '2026-05-29 19:17:55', NULL),
(2, NULL, 'closed', '2026-05-29 19:17:55', NULL),
(3, 9, 'open', '2026-05-29 19:17:55', NULL),
(4, NULL, 'open', '2026-06-09 11:56:08', NULL),
(5, NULL, 'open', '2026-06-09 11:57:01', NULL),
(6, NULL, 'open', '2026-06-09 11:59:34', NULL),
(7, 2, 'open', '2026-06-11 14:54:03', NULL),
(8, NULL, 'open', '2026-06-11 14:57:46', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `order_code` varchar(50) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `shipping_id` int(10) UNSIGNED DEFAULT NULL,
  `shipping_method_id` int(11) DEFAULT 1,
  `promo_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_name` varchar(100) NOT NULL COMMENT 'Snapshot ten nguoi nhan',
  `recipient_phone` varchar(15) NOT NULL COMMENT 'Snapshot so dien thoai',
  `recipient_email` varchar(100) DEFAULT NULL,
  `guest_token` varchar(64) DEFAULT NULL,
  `shipping_address` varchar(500) NOT NULL COMMENT 'Snapshot dia chi giao hang',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Tong tien hang truoc giam gia',
  `discount_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'So tien duoc giam',
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Phi van chuyen',
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Tong thanh toan cuoi cung',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled','returned') NOT NULL DEFAULT 'pending' COMMENT 'Trang thai don hang',
  `payment_method` enum('cod','bank_transfer','momo','vnpay','zalopay','payos') NOT NULL DEFAULT 'cod',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' COMMENT 'Trang thai thanh toan',
  `note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `order_code`, `user_id`, `shipping_id`, `shipping_method_id`, `promo_id`, `recipient_name`, `recipient_phone`, `recipient_email`, `guest_token`, `shipping_address`, `subtotal`, `discount_amount`, `shipping_fee`, `total_amount`, `order_status`, `payment_method`, `payment_status`, `note`, `created_at`, `updated_at`) VALUES
(1, 'ORD-000001', 12, 1, 1, 1, 'Nguyễn Văn An', '0912345678', NULL, NULL, '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP HCM', 890000.00, 50000.00, 0.00, 840000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(2, 'ORD-000002', 8, 2, 1, NULL, 'Trần Thị Bích', '0923456789', NULL, NULL, '45 Xô Viết Nghệ Tĩnh, Phường 25, Quận Bình Thạnh, TP HCM', 4500000.00, 0.00, 50000.00, 4550000.00, 'shipped', 'vnpay', 'paid', 'Giao hàng giờ hành chính', '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(3, 'ORD-000003', 9, 1, 1, 2, 'Lê Minh Cường', '0934567890', NULL, NULL, '88 Trần Thái Tông, Phường Dịch Vọng, Cầu Giấy, Hà Nội', 2900000.00, 100000.00, 0.00, 2800000.00, 'confirmed', 'bank_transfer', 'paid', NULL, '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(4, 'ORD-000004', 10, 3, 1, NULL, 'Phạm Thị Dung', '0945678901', NULL, NULL, '23 Trần Phú, Phường Hải Châu 1, Hải Châu, Đà Nẵng', 1200000.00, 0.00, 80000.00, 1280000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-29 19:17:55', '2026-06-12 22:12:00'),
(5, 'ORD-000005', 11, 2, 1, 3, 'Hoàng Văn Em', '0956789012', NULL, NULL, '5 Nguyễn Văn Linh, Phường Tân Phong, Quận 7, TP HCM', 680000.00, 50000.00, 0.00, 630000.00, 'delivered', 'momo', 'paid', 'Giao trước 18h', '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(6, 'ORD-000006', 12, 1, 1, NULL, 'Nguyễn Văn An', '0912345678', NULL, NULL, '12 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP HCM', 3200000.00, 0.00, 0.00, 3200000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(7, 'ORD-000007', 13, 2, 1, NULL, 'Nguyễn Thị Phương', '0967890123', NULL, NULL, '100 Võ Văn Ngân, Phường Linh Trung, Thủ Đức, TP HCM', 1570000.00, 0.00, 50000.00, 1620000.00, 'processing', 'vnpay', 'paid', NULL, '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(8, 'ORD-000008', 8, 1, 1, 1, 'Trần Thị Bích', '0923456789', NULL, NULL, '45 Xô Viết Nghệ Tĩnh, Phường 25, Quận Bình Thạnh, TP HCM', 750000.00, 50000.00, 0.00, 700000.00, 'cancelled', 'cod', 'unpaid', 'Khách hủy vì sai size', '2026-05-29 19:17:55', '2026-06-11 03:14:41'),
(16, 'ORD-000016', 1, 2, 1, 6, 'Quản Trị Viên', '0123456789', NULL, NULL, '77/5a phường 4 tpvl, , , Vĩnh Long', 4500000.00, 225000.00, 0.00, 4275000.00, 'cancelled', 'cod', 'refunded', '', '2026-06-05 16:08:26', '2026-06-11 03:14:41'),
(17, 'ORD-000017', 1, 14, 1, 6, 'Quản Trị Viên', '0123456789', NULL, NULL, '77/5a phường 4 tpvl, , , Bình Dương', 4500000.00, 225000.00, 25000.00, 4300000.00, 'delivered', 'bank_transfer', 'paid', '', '2026-06-05 16:33:17', '2026-06-11 03:14:41'),
(20, 'ORD-000020', 2, 1, 3, 1, 'Tester User', '0987654321', NULL, NULL, '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 1680000.00, 100000.00, 0.00, 1580000.00, 'pending', 'cod', 'unpaid', '', '2026-06-11 01:56:28', '2026-06-11 03:14:41'),
(21, 'ORD-000021', 2, 1, 1, NULL, 'Tester User', '0987654321', NULL, NULL, '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 3300000.00, 0.00, 0.00, 3300000.00, 'delivered', 'bank_transfer', 'unpaid', '', '2026-06-11 02:02:11', '2026-06-11 23:03:31'),
(22, 'ORD-000022', 2, 1, 2, NULL, 'Tester User', '0987654321', NULL, NULL, '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 1170000.00, 0.00, 35000.00, 1205000.00, 'pending', 'cod', 'unpaid', '', '2026-06-11 02:10:46', '2026-06-11 03:14:41'),
(23, 'ORD-2B5F71', NULL, 7, 1, NULL, 'Guest Tester', '0901234567', 'guest@example.com', '03b5c61831f9b657e798be62a1eca990', '123 Guest Street, , , Bà Rịa - Vũng Tàu', 160000.00, 0.00, 35000.00, 195000.00, 'cancelled', 'cod', 'unpaid', '', '2026-06-11 03:20:09', '2026-06-11 22:57:24'),
(24, 'ORD-E81C83', NULL, 11, 1, NULL, 'khach vang lai', '0987912342', 'guest@gmail.com', 'a88b452293120863fce434c3cd11789e', 'Tam Bình, , , Bắc Ninh', 2000000.00, 0.00, 0.00, 2000000.00, 'confirmed', 'cod', 'unpaid', '', '2026-06-11 22:01:58', '2026-06-11 22:57:15'),
(25, 'ORD-478360', NULL, 61, 2, NULL, 'khach van lai 2', '0987654333', 'vanglai2@gmail.com', '50aae7b27431865f8ce289ef3abefa7f', '6/71 Nguyễn Huệ, , Huyện Trà Ôn, Vĩnh Long', 890000.00, 0.00, 40000.00, 930000.00, 'processing', 'cod', 'refunded', '', '2026-06-11 22:53:18', '2026-06-11 22:59:46'),
(26, 'ORD-F6D4D0', 2, 1, 1, NULL, 'Bug', '0987654321', '', '74c772bce67e10207be806715d4da1ad', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 2003000.00, 0.00, 0.00, 2003000.00, 'pending', 'payos', 'unpaid', '', '2026-06-11 23:40:05', '2026-06-12 10:56:14'),
(27, 'ORD-7A70FA', 2, 1, 2, 7, 'Bug', '0987654321', '', '56bdf4aeb8c3778bebb3791a8ea092ea', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 3000.00, 35000.00, 35000.00, 3000.00, 'pending', 'cod', 'unpaid', '', '2026-06-11 23:46:05', '2026-06-11 23:46:05'),
(28, 'ORD-0BD226', 2, 1, 2, 7, 'Bug', '0987654321', '', 'afbcacfef26b531db7314cae38ba0e7d', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 3000.00, 35000.00, 35000.00, 3000.00, 'cancelled', 'payos', 'unpaid', '', '2026-06-11 23:47:45', '2026-06-12 10:56:14'),
(29, 'ORD-037718', 2, 1, 2, 7, 'Bug', '0987654321', '', '4420568facd9fa393f9b1db5d8e54492', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 3000.00, 35000.00, 35000.00, 3000.00, 'cancelled', 'payos', 'unpaid', '', '2026-06-11 23:53:00', '2026-06-12 10:56:14'),
(30, 'ORD-39C77C', 2, 1, 1, 7, 'Bug', '0987654321', '', '5f9c1b2cdc890502e20fdfb1e0e55d5e', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 15000.00, 35000.00, 20000.00, 0.00, 'cancelled', 'cod', 'unpaid', '', '2026-06-12 10:29:03', '2026-06-12 10:29:37'),
(31, 'ORD-C04CC7', 2, 51, 1, 7, 'Bug', '0987654321', '', '5cad99fa3a25bcaa22726a6ef1e55a23', '6/21 Huỳnh Tấn Phát, , Huyện Long Phú, Sóc Trăng', 3000.00, 35000.00, 35000.00, 3000.00, 'pending', 'payos', 'paid', '', '2026-06-12 21:45:05', '2026-06-12 21:50:00'),
(32, 'ORD-CA62D5', 2, 1, 2, 7, 'Bug', '0987654321', '', '616dbcd74003072cc1edaa313b6eeb20', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 10000.00, 35000.00, 35000.00, 10000.00, 'pending', 'payos', 'unpaid', '', '2026-06-12 21:52:59', '2026-06-12 21:52:59'),
(33, 'ORD-C1808E', 2, 1, 2, 7, 'Bug', '0987654321', '', '1a50e4122371386e1a4f5bd91822eeee', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 10000.00, 35000.00, 35000.00, 10000.00, 'pending', 'payos', 'unpaid', '', '2026-06-12 21:53:45', '2026-06-12 21:53:45'),
(50000, 'ORD-4C1599', 2, 1, 2, 7, 'Bug', '0987654321', '', '0ce0a5f3da2b4039dfb4e869c44dd3c3', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 10000.00, 35000.00, 35000.00, 10000.00, 'cancelled', 'payos', 'unpaid', '', '2026-06-12 22:10:57', '2026-06-12 22:22:29'),
(50001, 'ORD-13523F', 2, 1, 2, 7, 'Bug', '0987654321', '', '20f94ea939bfbf98d3857d27ecd0ac04', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 20000.00, 35000.00, 35000.00, 20000.00, 'pending', 'payos', 'paid', '', '2026-06-12 22:24:01', '2026-06-12 22:24:33'),
(50002, 'ORD-860E2B', 2, 1, 2, 7, 'Bug', '0987654321', '', '84fdd338d5e2b2828baa82fb3b239d68', '6/21 Huỳnh Tấn Phát, , Huyện Nhà Bè, TP. Hồ Chí Minh', 3000.00, 35000.00, 35000.00, 3000.00, 'pending', 'payos', 'paid', 'day la ghi chu', '2026-06-12 22:34:12', '2026-06-12 22:34:44'),
(50003, 'ORD-27A8F5', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1500000.00, 0.00, 30000.00, 1530000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-03 18:11:20', '2026-01-03 18:11:20'),
(50004, 'ORD-3AF370', 13, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 3900000.00, 0.00, 30000.00, 3930000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-14 18:50:57', '2026-01-14 18:50:57'),
(50005, 'ORD-4E5F2E', 10, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 890000.00, 0.00, 30000.00, 920000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-05 18:00:57', '2026-01-05 18:00:57'),
(50006, 'ORD-D3520A', 9, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1500000.00, 0.00, 30000.00, 1530000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-26 10:38:52', '2026-01-26 10:38:52'),
(50007, 'ORD-B1A7CF', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 9000000.00, 0.00, 30000.00, 9030000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-06 15:08:03', '2026-02-06 15:08:03'),
(50008, 'ORD-4857FC', 8, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 4500000.00, 0.00, 30000.00, 4530000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-02 19:28:55', '2026-02-02 19:28:55'),
(50009, 'ORD-919ED7', 10, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 890000.00, 0.00, 30000.00, 920000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-03 16:48:45', '2026-02-03 16:48:45'),
(50010, 'ORD-CE9AED', 9, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1950000.00, 0.00, 30000.00, 1980000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-05 09:47:41', '2026-02-05 09:47:41'),
(50011, 'ORD-D3D364', 8, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 3900000.00, 0.00, 30000.00, 3930000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-07 17:39:57', '2026-02-07 17:39:57'),
(50012, 'ORD-1EA6FC', 8, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 3900000.00, 0.00, 30000.00, 3930000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-01 15:59:29', '2026-02-01 15:59:29'),
(50013, 'ORD-F6053B', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 4500000.00, 0.00, 30000.00, 4530000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-06 16:03:19', '2026-02-06 16:03:19'),
(50014, 'ORD-9FC68C', 13, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1500000.00, 0.00, 30000.00, 1530000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-03 08:51:38', '2026-02-03 08:51:38'),
(50015, 'ORD-E7D9DB', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 890000.00, 0.00, 30000.00, 920000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-09 10:26:09', '2026-03-09 10:26:09'),
(50016, 'ORD-5194AC', 10, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 4500000.00, 0.00, 30000.00, 4530000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-17 20:01:15', '2026-03-17 20:01:15'),
(50017, 'ORD-1BC0A6', 13, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 750000.00, 0.00, 30000.00, 780000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-10 08:50:26', '2026-03-10 08:50:26'),
(50018, 'ORD-6AE7DE', 12, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 3900000.00, 0.00, 30000.00, 3930000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-25 15:01:22', '2026-03-25 15:01:22'),
(50019, 'ORD-D70AA7', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 3900000.00, 0.00, 30000.00, 3930000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-15 11:53:45', '2026-03-15 11:53:45'),
(50020, 'ORD-B66D2D', 12, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 750000.00, 0.00, 30000.00, 780000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-26 12:27:28', '2026-03-26 12:27:28'),
(50021, 'ORD-72F0CE', 8, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1780000.00, 0.00, 30000.00, 1810000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-21 08:39:14', '2026-03-21 08:39:14'),
(50022, 'ORD-EE376E', 11, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 4500000.00, 0.00, 30000.00, 4530000.00, 'delivered', 'cod', 'paid', NULL, '2026-04-22 19:44:05', '2026-04-22 19:44:05'),
(50023, 'ORD-3F8DF2', 9, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 750000.00, 0.00, 30000.00, 780000.00, 'delivered', 'cod', 'paid', NULL, '2026-04-22 08:46:42', '2026-04-22 08:46:42'),
(50024, 'ORD-3B1217', 8, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1950000.00, 0.00, 30000.00, 1980000.00, 'delivered', 'cod', 'paid', NULL, '2026-04-12 09:45:52', '2026-04-12 09:45:52'),
(50025, 'ORD-404458', 13, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1950000.00, 0.00, 30000.00, 1980000.00, 'delivered', 'cod', 'paid', NULL, '2026-04-19 08:49:14', '2026-04-19 08:49:14'),
(50026, 'ORD-F9682A', 9, NULL, 1, NULL, 'Test User', '0987654321', NULL, NULL, 'Dia chi test', 1500000.00, 0.00, 30000.00, 1530000.00, 'delivered', 'cod', 'paid', NULL, '2026-04-20 12:45:00', '2026-04-20 12:45:00'),
(50027, 'C9734A08', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 750000.00, 'cancelled', 'cod', '', NULL, '2025-07-20 23:51:09', '2026-06-13 06:58:03'),
(50028, 'EEFA9FC1', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1300000.00, 'delivered', 'cod', 'paid', NULL, '2025-08-01 11:24:04', '2026-06-13 06:58:03'),
(50029, '9E31DBE0', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5550000.00, 'pending', 'cod', '', NULL, '2026-01-20 14:18:03', '2026-06-13 06:58:03'),
(50030, '2333C271', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1960000.00, 'shipped', 'cod', '', NULL, '2025-01-13 05:23:25', '2026-06-13 06:58:03'),
(50031, '34E70D82', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3300000.00, 'delivered', 'cod', 'paid', NULL, '2025-03-21 11:29:21', '2026-06-13 06:58:03'),
(50032, 'E37B7DB6', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5000000.00, 'shipped', 'cod', 'paid', NULL, '2026-03-24 15:16:59', '2026-06-13 06:58:03'),
(50033, '3889903F', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'delivered', 'cod', 'paid', NULL, '2025-05-15 05:53:07', '2026-06-13 06:58:03'),
(50034, '53B372AE', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2360000.00, 'cancelled', 'cod', '', NULL, '2026-01-04 15:09:19', '2026-06-13 06:58:03'),
(50035, '5AB6251B', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2235000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-17 10:19:18', '2026-06-13 06:58:03'),
(50036, '99C0544F', 18, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 200000.00, 'delivered', 'cod', 'paid', NULL, '2025-09-09 00:31:01', '2026-06-13 06:58:03'),
(50037, '6AD07AA3', 19, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 890000.00, 'delivered', 'cod', 'paid', NULL, '2025-04-15 01:15:13', '2026-06-13 06:58:03'),
(50038, '5F40C98D', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'delivered', 'cod', 'paid', NULL, '2025-05-12 04:42:08', '2026-06-13 06:58:03'),
(50039, '0E6D2E96', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5950000.00, 'pending', 'cod', '', NULL, '2025-12-05 02:35:15', '2026-06-13 06:58:03'),
(50040, '0D9491B1', 16, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3845000.00, 'shipped', 'cod', '', NULL, '2026-01-31 09:05:20', '2026-06-13 06:58:03'),
(50041, '2ED72338', 18, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 8470000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-18 03:27:21', '2026-06-13 06:58:03'),
(50042, '79242EC8', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 890000.00, 'cancelled', 'cod', '', NULL, '2026-02-05 14:20:57', '2026-06-13 06:58:03'),
(50043, 'E46550E2', 15, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 7630000.00, 'delivered', 'cod', 'paid', NULL, '2025-06-01 22:15:20', '2026-06-13 06:58:03'),
(50044, 'C4C76A29', 18, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1780000.00, 'processing', 'cod', '', NULL, '2026-02-26 10:42:44', '2026-06-13 06:58:03'),
(50045, '58938C61', 16, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4290000.00, 'delivered', 'cod', 'paid', NULL, '2025-12-14 05:38:57', '2026-06-13 06:58:03'),
(50046, 'A19844DD', 17, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'delivered', 'cod', 'paid', NULL, '2025-09-30 15:38:09', '2026-06-13 06:58:03'),
(50047, 'C0764F76', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 8910000.00, 'processing', 'cod', 'paid', NULL, '2026-04-11 16:59:31', '2026-06-13 06:58:03'),
(50048, '5359CA63', 16, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2190000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-01 02:49:16', '2026-06-13 06:58:03'),
(50049, '94228441', 15, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 6360000.00, 'shipped', 'cod', '', NULL, '2025-12-12 03:17:42', '2026-06-13 06:58:03'),
(50050, '5E92C385', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 9620000.00, 'cancelled', 'cod', '', NULL, '2025-06-28 06:16:10', '2026-06-13 06:58:03'),
(50051, '4302D20D', 18, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 15570000.00, 'delivered', 'cod', 'paid', NULL, '2025-05-16 01:35:10', '2026-06-13 06:58:03'),
(50052, '438A2E51', 23, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 8650000.00, 'processing', 'cod', '', NULL, '2026-02-18 19:57:13', '2026-06-13 06:58:03'),
(50053, '3C4FCB27', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3040000.00, 'delivered', 'cod', 'paid', NULL, '2026-06-13 05:40:33', '2026-06-13 06:58:03'),
(50054, '1E802108', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1360000.00, 'cancelled', 'cod', '', NULL, '2026-02-16 03:01:47', '2026-06-13 06:58:03'),
(50055, 'A46E2262', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3510000.00, 'delivered', 'cod', 'paid', NULL, '2025-09-17 04:00:15', '2026-06-13 06:58:03'),
(50056, '3CB92C5C', 22, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4085000.00, 'delivered', 'cod', 'paid', NULL, '2025-02-16 08:07:27', '2026-06-13 06:58:03'),
(50057, 'D3C7DE1A', 17, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1130000.00, 'cancelled', 'cod', '', NULL, '2026-05-10 19:03:23', '2026-06-13 06:58:03'),
(50058, 'CD6032F3', 19, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 8010000.00, 'pending', 'cod', '', NULL, '2025-11-23 18:03:22', '2026-06-13 06:58:03'),
(50059, 'BED07629', 14, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 7120000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-15 22:04:30', '2026-06-13 06:58:03'),
(50060, '381ED46C', 16, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4630000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-17 20:19:19', '2026-06-13 06:58:03'),
(50061, '0A252145', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3600000.00, 'shipped', 'cod', '', NULL, '2026-04-12 12:52:09', '2026-06-13 06:58:03'),
(50062, 'AE51D262', 18, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4160000.00, 'delivered', 'cod', 'paid', NULL, '2025-08-28 20:46:07', '2026-06-13 06:58:03'),
(50063, '11072D3F', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3200000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-28 13:06:23', '2026-06-13 06:58:03'),
(50064, '550FC7CB', 19, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1730000.00, 'delivered', 'cod', 'paid', NULL, '2025-02-03 03:39:01', '2026-06-13 06:58:03'),
(50065, 'DE8EA248', 20, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3900000.00, 'delivered', 'cod', 'paid', NULL, '2025-12-31 19:24:21', '2026-06-13 06:58:03'),
(50066, '21136BFE', 16, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 200000.00, 'pending', 'cod', '', NULL, '2026-01-22 14:40:48', '2026-06-13 06:58:03'),
(50067, 'D4266031', 30, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 360000.00, 'delivered', 'cod', 'paid', NULL, '2025-01-23 05:44:09', '2026-06-13 07:22:14'),
(50068, '4BB598D2', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 170000.00, 'delivered', 'cod', 'paid', NULL, '2025-05-22 20:14:04', '2026-06-13 07:22:14'),
(50069, '0C49EEAD', 25, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5340000.00, 'delivered', 'cod', 'paid', NULL, '2025-12-25 19:37:01', '2026-06-13 07:22:14'),
(50070, 'ED0BE009', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'processing', 'cod', '', NULL, '2025-12-20 07:05:56', '2026-06-13 07:22:14'),
(50071, '668D3559', 29, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3760000.00, 'delivered', 'cod', 'paid', NULL, '2025-08-05 12:36:08', '2026-06-13 07:22:14'),
(50072, 'E8E4B1E2', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3590000.00, 'delivered', 'cod', 'paid', NULL, '2025-11-07 04:09:25', '2026-06-13 07:22:14'),
(50073, '99B333CC', 30, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1160000.00, 'delivered', 'cod', 'paid', NULL, '2025-05-28 11:47:37', '2026-06-13 07:22:14'),
(50074, '024A51A3', 28, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2790000.00, 'delivered', 'cod', 'paid', NULL, '2026-02-03 12:07:40', '2026-06-13 07:22:14'),
(50075, '7A587DBE', 29, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 6540000.00, 'delivered', 'cod', 'paid', NULL, '2025-04-17 15:12:13', '2026-06-13 07:22:14'),
(50076, '50E639C5', 25, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 6360000.00, 'cancelled', 'cod', '', NULL, '2026-01-06 11:45:57', '2026-06-13 07:22:14'),
(50077, 'FFFC60D4', 29, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5080000.00, 'cancelled', 'cod', '', NULL, '2026-04-03 02:30:33', '2026-06-13 07:22:14'),
(50078, 'B146A406', 27, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 15140000.00, 'delivered', 'cod', 'paid', NULL, '2025-04-09 05:33:41', '2026-06-13 07:22:14'),
(50079, '6EB82DA2', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1160000.00, 'delivered', 'cod', 'paid', NULL, '2025-02-28 04:04:38', '2026-06-13 07:22:14'),
(50080, '17CAACC5', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 5950000.00, 'processing', 'cod', '', NULL, '2025-07-07 08:39:05', '2026-06-13 07:22:14'),
(50081, '0FB6440D', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 8970000.00, 'processing', 'cod', 'paid', NULL, '2025-05-19 06:36:08', '2026-06-13 07:22:15'),
(50082, '1C4719FF', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1360000.00, 'pending', 'cod', '', NULL, '2026-01-29 09:37:08', '2026-06-13 07:22:15'),
(50083, '3863DD4A', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 945000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-05 03:05:57', '2026-06-13 07:22:15'),
(50084, '9D47FC84', 24, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 12180000.00, 'cancelled', 'cod', '', NULL, '2025-02-17 14:51:59', '2026-06-13 07:22:15'),
(50085, 'BC8F27C7', 27, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4450000.00, 'processing', 'cod', '', NULL, '2025-01-22 12:49:22', '2026-06-13 07:22:15'),
(50086, '594BDE6D', 30, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2200000.00, 'pending', 'cod', '', NULL, '2025-12-15 15:36:24', '2026-06-13 07:22:15'),
(50087, 'A69617DC', 25, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1210000.00, 'pending', 'cod', '', NULL, '2025-07-10 02:33:29', '2026-06-13 07:22:15'),
(50088, '6A088280', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 400000.00, 'delivered', 'cod', 'paid', NULL, '2026-03-01 20:14:09', '2026-06-13 07:22:15'),
(50089, '7D4C9C29', 28, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1260000.00, 'cancelled', 'cod', '', NULL, '2025-03-30 00:35:50', '2026-06-13 07:22:15'),
(50090, 'CF646E16', 33, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3510000.00, 'shipped', 'cod', '', NULL, '2025-03-28 05:53:57', '2026-06-13 07:22:15'),
(50091, '1E3B5947', 27, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1450000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-21 22:22:11', '2026-06-13 07:22:15'),
(50092, '2ADFADD9', 30, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 9270000.00, 'delivered', 'cod', 'paid', NULL, '2026-05-07 19:06:58', '2026-06-13 07:22:15'),
(50093, 'A7CD0CD1', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2530000.00, 'shipped', 'cod', 'paid', NULL, '2025-03-20 10:05:26', '2026-06-13 07:22:15'),
(50094, '33C98C40', 28, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 6100000.00, 'delivered', 'cod', 'paid', NULL, '2025-12-07 10:36:55', '2026-06-13 07:22:15'),
(50095, '60C2F83D', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 19600000.00, 'cancelled', 'cod', '', NULL, '2026-06-09 20:55:24', '2026-06-13 07:22:15'),
(50096, 'DF038CD2', 33, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 4490000.00, 'delivered', 'cod', 'paid', NULL, '2026-01-10 23:05:04', '2026-06-13 07:22:15'),
(50097, '386AA085', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 650000.00, 'delivered', 'cod', 'paid', NULL, '2025-03-04 20:54:40', '2026-06-13 07:22:15'),
(50098, '279053A2', 26, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'shipped', 'cod', 'paid', NULL, '2025-11-15 13:13:41', '2026-06-13 07:22:15'),
(50099, '4C19A20F', 30, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2670000.00, 'delivered', 'cod', 'paid', NULL, '2025-12-05 13:54:13', '2026-06-13 07:22:15'),
(50100, '4CEA8E8B', 32, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 6990000.00, 'delivered', 'cod', 'paid', NULL, '2025-08-12 21:10:38', '2026-06-13 07:22:15'),
(50101, '419E61A1', 29, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1780000.00, 'cancelled', 'cod', '', NULL, '2026-04-04 10:58:41', '2026-06-13 07:22:15'),
(50102, '653E1AF9', 27, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 2070000.00, 'pending', 'cod', '', NULL, '2025-03-05 05:07:52', '2026-06-13 07:22:15'),
(50103, '354BF131', 28, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 3095000.00, 'delivered', 'cod', 'paid', NULL, '2025-08-29 16:59:32', '2026-06-13 07:22:15'),
(50104, '6D0510F0', 24, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 1570000.00, 'delivered', 'cod', 'paid', NULL, '2025-07-26 07:38:07', '2026-06-13 07:22:15'),
(50105, '8A134395', 25, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 890000.00, 'shipped', 'cod', 'paid', NULL, '2025-07-12 11:01:36', '2026-06-13 07:22:15'),
(50106, '0D899FC4', 28, NULL, 1, NULL, '', '', NULL, NULL, '123 Đường Axeron, TP.HCM', 0.00, 0.00, 0.00, 11530000.00, 'delivered', 'cod', 'paid', NULL, '2025-10-18 13:51:27', '2026-06-13 07:22:15'),
(50107, 'ORD-B89F98', NULL, 9, 1, NULL, 'khach vang lai', '0987655555', 'vanlai@gmail.com', '4c609cb2dd34363262e6a5dce9c3ba2c', '7/21, , Huyện Ba Bể, Bắc Kạn', 3100000.00, 0.00, 0.00, 3100000.00, 'pending', 'cod', 'unpaid', '[Yêu cầu hủy từ khách]: Tìm được sản phẩm khác', '2026-06-14 01:25:20', '2026-06-14 01:30:47');

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

INSERT INTO `order_items` (`order_item_id`, `order_id`, `variant_id`, `product_name`, `variant_info`, `unit_price`, `quantity`, `subtotal`) VALUES
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
(17, 17, 148, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Do (3U) - Size 3U/G4', 4500000.00, 1, 4500000.00),
(20, 20, 95, 'Áo polo thể thao DL Classic 2024', 'Trắng - Size L', 280000.00, 6, 1680000.00),
(21, 21, 29, 'Giày ASICS Gel-Nimbus 26', 'Trắng - Size 40', 3300000.00, 1, 3300000.00),
(22, 22, 6, 'Giày chạy bộ DL Speed Pro X1', 'Trắng - Size 40', 890000.00, 1, 890000.00),
(23, 22, 95, 'Áo polo thể thao DL Classic 2024', 'Trắng - Size L', 280000.00, 1, 280000.00),
(24, 23, 171, 'Cầu lông Yonex AS-05 (hop 12 qua)', 'Vàng - Size Lop 76', 160000.00, 1, 160000.00),
(25, 24, 17, 'Giày Nike Revolution 7', 'Xanh đường - Size 40', 2000000.00, 1, 2000000.00),
(26, 25, 6, 'Giày chạy bộ DL Speed Pro X1', 'Trắng - Size 40', 890000.00, 1, 890000.00),
(27, 26, 17, 'Giày Nike Revolution 7', 'Xanh đường - Size 40', 2000000.00, 1, 2000000.00),
(28, 26, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(29, 27, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(30, 28, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(31, 29, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(32, 30, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 5, 15000.00),
(33, 31, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(34, 32, 256, 'BANKING Test 10K', 'Mặc định - Size Free Size', 10000.00, 1, 10000.00),
(35, 33, 256, 'BANKING Test 10K', 'Mặc định - Size Free Size', 10000.00, 1, 10000.00),
(38, 50000, 256, 'BANKING Test 10K', 'Mặc định - Size Free Size', 10000.00, 1, 10000.00),
(39, 50001, 256, 'BANKING Test 10K', 'Mặc định - Size Free Size', 10000.00, 2, 20000.00),
(40, 50002, 254, 'BANKING Test 3K', 'Mặc định - Size Free Size', 3000.00, 1, 3000.00),
(41, 50003, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 2, 1500000.00),
(42, 50004, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 2, 3900000.00),
(43, 50005, 3, 'Giày chạy bộ DL Speed Pro X1', NULL, 890000.00, 1, 890000.00),
(44, 50006, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 2, 1500000.00),
(45, 50007, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', NULL, 4500000.00, 2, 9000000.00),
(46, 50008, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', NULL, 4500000.00, 1, 4500000.00),
(47, 50009, 3, 'Giày chạy bộ DL Speed Pro X1', NULL, 890000.00, 1, 890000.00),
(48, 50010, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 1, 1950000.00),
(49, 50011, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 2, 3900000.00),
(50, 50012, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 2, 3900000.00),
(51, 50013, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', NULL, 4500000.00, 1, 4500000.00),
(52, 50014, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 2, 1500000.00),
(53, 50015, 3, 'Giày chạy bộ DL Speed Pro X1', NULL, 890000.00, 1, 890000.00),
(54, 50016, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', NULL, 4500000.00, 1, 4500000.00),
(55, 50017, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 1, 750000.00),
(56, 50018, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 2, 3900000.00),
(57, 50019, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 2, 3900000.00),
(58, 50020, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 1, 750000.00),
(59, 50021, 3, 'Giày chạy bộ DL Speed Pro X1', NULL, 890000.00, 2, 1780000.00),
(60, 50022, 147, 'Vợt cầu lông Yonex Astrox 99 Pro', NULL, 4500000.00, 1, 4500000.00),
(61, 50023, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 1, 750000.00),
(62, 50024, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 1, 1950000.00),
(63, 50025, 15, 'Giày Nike Revolution 7', NULL, 1950000.00, 1, 1950000.00),
(64, 50026, 204, 'Quần legging Under Armour HeatGear', NULL, 750000.00, 2, 1500000.00),
(65, 50027, 215, 'Bóng Futsal DL Super', 'Xanh Size 4', 250000.00, 3, 750000.00),
(66, 50028, 36, 'Giày đá bóng DL Striker FG 2024', 'Xanh lá 40', 650000.00, 2, 1300000.00),
(67, 50029, 32, 'Giày đá bóng DL Striker FG 2024', 'Vàng 39', 650000.00, 3, 1950000.00),
(68, 50029, 157, 'Vợt cầu lông Lining Windstorm 72', 'Đỏ 4U', 1800000.00, 2, 3600000.00),
(69, 50030, 100, 'Áo polo DL Pro Team 2024', 'Đen M', 320000.00, 1, 320000.00),
(70, 50030, 10, 'Giày chạy bộ DL Runner Air 2024', 'Xanh navy 42', 750000.00, 1, 750000.00),
(71, 50030, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 1, 890000.00),
(72, 50031, 25, 'Giày ASICS Gel-Nimbus 26', 'Xanh 39', 3300000.00, 1, 3300000.00),
(73, 50032, 69, 'Giày cầu lông DL Wing Pro 2024', 'Đỏ 38', 680000.00, 2, 1360000.00),
(74, 50032, 88, 'Giày cầu lông nữ DL Wing Lite', 'Hồng 37', 620000.00, 3, 1860000.00),
(75, 50032, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 2, 1780000.00),
(76, 50033, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(77, 50034, 118, 'Áo Nike Dri-FIT Training', 'Đen M', 580000.00, 1, 580000.00),
(78, 50034, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 2, 1780000.00),
(79, 50035, 35, 'Giày đá bóng DL Striker FG 2024', 'Vàng 42', 650000.00, 3, 1950000.00),
(80, 50035, 236, 'Tất thể thao DL Sport (5 doi)', 'Xanh Free size', 95000.00, 3, 285000.00),
(81, 50036, 197, 'Quần short nữ DL Active', 'Hồng M', 200000.00, 1, 200000.00),
(82, 50037, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 1, 890000.00),
(83, 50038, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(84, 50039, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 3, 2670000.00),
(85, 50039, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(86, 50039, 11, 'Giày chạy bộ DL Runner Air 2024', 'Đỏ 40', 750000.00, 2, 1500000.00),
(87, 50040, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 3, 2670000.00),
(88, 50040, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 1, 890000.00),
(89, 50040, 235, 'Tất thể thao DL Sport (5 doi)', 'Đen Free size', 95000.00, 3, 285000.00),
(90, 50041, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(91, 50041, 62, 'Giày cầu lông Yonex Power Cushion 65Z3', 'Trắng 37', 2900000.00, 2, 5800000.00),
(92, 50042, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 1, 890000.00),
(93, 50043, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 2, 1780000.00),
(94, 50043, 143, 'Áo sport bra Nike Indy', 'Trắng M', 750000.00, 3, 2250000.00),
(95, 50043, 75, 'Giày cầu lông Lining Ranger TD', 'Xanh navy 37', 1200000.00, 3, 3600000.00),
(96, 50044, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 2, 1780000.00),
(97, 50045, 128, 'Áo phông nữ DL Sport Lite', 'Tím M', 230000.00, 3, 690000.00),
(98, 50045, 76, 'Giày cầu lông Lining Ranger TD', 'Xanh navy 38', 1200000.00, 3, 3600000.00),
(99, 50046, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(100, 50047, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 3, 2670000.00),
(101, 50047, 58, 'Giày đá bóng DL Speed FG Jr', 'Vàng 35', 420000.00, 2, 840000.00),
(102, 50047, 158, 'Vợt cầu lông Lining Windstorm 72', 'Đỏ 3U', 1800000.00, 3, 5400000.00),
(103, 50048, 143, 'Áo sport bra Nike Indy', 'Trắng M', 750000.00, 1, 750000.00),
(104, 50048, 162, 'Vợt cầu lông DL Pro 500', 'Xanh 2U', 480000.00, 3, 1440000.00),
(105, 50049, 46, 'Giày đá bóng Adidas Predator 24 FG', 'Đen 41', 2800000.00, 1, 2800000.00),
(106, 50049, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(107, 50049, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 2, 1780000.00),
(108, 50050, 109, 'Áo T-shirt DL Training Basic', 'Đen M', 220000.00, 1, 220000.00),
(109, 50050, 76, 'Giày cầu lông Lining Ranger TD', 'Xanh navy 38', 1200000.00, 3, 3600000.00),
(110, 50050, 65, 'Giày cầu lông Yonex Power Cushion 65Z3', 'Trắng 40', 2900000.00, 2, 5800000.00),
(111, 50051, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(112, 50051, 82, 'Giày cầu lông nữ Yonex SHB-01MXLX', 'Đỏ hồng 37', 3100000.00, 3, 9300000.00),
(113, 50051, 79, 'Giày cầu lông Lining Ranger TD', 'Đen 38', 1200000.00, 3, 3600000.00),
(114, 50052, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(115, 50052, 170, 'Vợt cầu lông Head Zephyr Pro', 'Đen 3U', 2100000.00, 2, 4200000.00),
(116, 50052, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(117, 50053, 58, 'Giày đá bóng DL Speed FG Jr', 'Vàng 35', 420000.00, 3, 1260000.00),
(118, 50053, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 2, 1780000.00),
(119, 50054, 211, 'Quần legging Adidas Optime 7/8', 'Xanh S', 680000.00, 2, 1360000.00),
(120, 50055, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 2, 1780000.00),
(121, 50055, 58, 'Giày đá bóng DL Speed FG Jr', 'Vàng 35', 420000.00, 2, 840000.00),
(122, 50055, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 1, 890000.00),
(123, 50056, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(124, 50056, 229, 'Balo thể thao DL Sport 20L', 'Trắng 20L', 420000.00, 3, 1260000.00),
(125, 50056, 237, 'Mũ thể thao nữ DL Lily', 'Trắng Free size', 155000.00, 1, 155000.00),
(126, 50057, 33, 'Giày đá bóng DL Striker FG 2024', 'Vàng 40', 650000.00, 1, 650000.00),
(127, 50057, 171, 'Cầu lông Yonex AS-05 (hop 12 qua)', 'Vàng Lop 76', 160000.00, 3, 480000.00),
(128, 50058, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(129, 50058, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(130, 50058, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(131, 50059, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(132, 50059, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 2, 1780000.00),
(133, 50059, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(134, 50060, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(135, 50060, 184, 'Quần short thể thao DL Training 2024', 'Đen S', 220000.00, 3, 660000.00),
(136, 50060, 32, 'Giày đá bóng DL Striker FG 2024', 'Vàng 39', 650000.00, 2, 1300000.00),
(137, 50061, 156, 'Vợt cầu lông Lining Windstorm 72', 'Xanh đường 3U', 1800000.00, 2, 3600000.00),
(138, 50062, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(139, 50062, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 1, 890000.00),
(140, 50062, 197, 'Quần short nữ DL Active', 'Hồng M', 200000.00, 3, 600000.00),
(141, 50063, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 1, 890000.00),
(142, 50063, 188, 'Quần short thể thao DL Training 2024', 'Xanh M', 220000.00, 3, 660000.00),
(143, 50063, 42, 'Giày đá bóng DL Futsal Pro F10', 'Đỏ 40', 550000.00, 3, 1650000.00),
(144, 50064, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 1, 890000.00),
(145, 50064, 94, 'Áo polo thể thao DL Classic 2024', 'Trắng M', 280000.00, 3, 840000.00),
(146, 50065, 111, 'Áo Under Armour HeatGear Nam', 'Đen S', 650000.00, 3, 1950000.00),
(147, 50065, 113, 'Áo Under Armour HeatGear Nam', 'Đen L', 650000.00, 3, 1950000.00),
(148, 50066, 197, 'Quần short nữ DL Active', 'Hồng M', 200000.00, 1, 200000.00),
(149, 50067, 216, 'Bóng Pickleball DL Outdoor', 'Vàng neon 40 lo', 120000.00, 3, 360000.00),
(150, 50068, 179, 'Cầu lông DL Training (hop 12 qua)', 'Vàng Lop 78', 85000.00, 2, 170000.00),
(151, 50069, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 3, 2670000.00),
(152, 50069, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(153, 50070, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(154, 50071, 107, 'Áo T-shirt DL Training Basic', 'Xanh đường L', 220000.00, 2, 440000.00),
(155, 50071, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(156, 50071, 36, 'Giày đá bóng DL Striker FG 2024', 'Xanh lá 40', 650000.00, 1, 650000.00),
(157, 50072, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 1, 890000.00),
(158, 50072, 204, 'Quần legging Under Armour HeatGear', 'Đen M', 750000.00, 1, 750000.00),
(159, 50072, 111, 'Áo Under Armour HeatGear Nam', 'Đen S', 650000.00, 3, 1950000.00),
(160, 50073, 195, 'Quần short Nike Dri-FIT 5 inch', 'Xanh L', 580000.00, 2, 1160000.00),
(161, 50074, 217, 'Tạ tay DL Vinyl 1kg - 10kg', 'Xanh 1 kg', 85000.00, 2, 170000.00),
(162, 50074, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(163, 50074, 164, 'Vợt cầu lông DL Fighter 200', 'Đen 2U', 280000.00, 3, 840000.00),
(164, 50075, 216, 'Bóng Pickleball DL Outdoor', 'Vàng neon 40 lo', 120000.00, 2, 240000.00),
(165, 50075, 168, 'Vợt cầu lông Head Zephyr Pro', 'Trắng 3U', 2100000.00, 3, 6300000.00),
(166, 50076, 162, 'Vợt cầu lông DL Pro 500', 'Xanh 2U', 480000.00, 3, 1440000.00),
(167, 50076, 10, 'Giày chạy bộ DL Runner Air 2024', 'Xanh navy 42', 750000.00, 3, 2250000.00),
(168, 50076, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 3, 2670000.00),
(169, 50077, 20, 'Giày Adidas Runfalcon 3.0', 'Đen 40', 1650000.00, 2, 3300000.00),
(170, 50077, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 2, 1780000.00),
(171, 50078, 128, 'Áo phông nữ DL Sport Lite', 'Tím M', 230000.00, 3, 690000.00),
(172, 50078, 34, 'Giày đá bóng DL Striker FG 2024', 'Vàng 41', 650000.00, 1, 650000.00),
(173, 50078, 149, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Trang (4U) 4U/G5', 4600000.00, 3, 13800000.00),
(174, 50079, 118, 'Áo Nike Dri-FIT Training', 'Đen M', 580000.00, 2, 1160000.00),
(175, 50080, 44, 'Giày đá bóng Adidas Predator 24 FG', 'Đen 39', 2800000.00, 1, 2800000.00),
(176, 50080, 53, 'Giày đá bóng Nike Phantom GX2', 'Đỏ 39', 3150000.00, 1, 3150000.00),
(177, 50081, 167, 'Vợt cầu lông Head Zephyr Pro', 'Trắng 4U', 2100000.00, 3, 6300000.00),
(178, 50081, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(179, 50082, 67, 'Giày cầu lông DL Wing Pro 2024', 'Đỏ 36', 680000.00, 2, 1360000.00),
(180, 50083, 233, 'Tất cầu lông Yonex 75th (3 doi)', 'Đen M/L', 180000.00, 3, 540000.00),
(181, 50083, 177, 'Cầu lông DL Training (hop 12 qua)', 'Vàng Lop 76', 85000.00, 1, 85000.00),
(182, 50083, 104, 'Áo polo DL Pro Team 2024', 'Đỏ L', 320000.00, 1, 320000.00),
(183, 50084, 154, 'Vợt cầu lông Yonex Nanoflare 700', 'Đỏ 3U/G4', 3250000.00, 3, 9750000.00),
(184, 50084, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 2, 1780000.00),
(185, 50084, 241, 'Balo nữ Adidas Classic BOS', 'Trắng 22L', 650000.00, 1, 650000.00),
(186, 50085, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 1, 890000.00),
(187, 50085, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 1, 890000.00),
(188, 50085, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 3, 2670000.00),
(189, 50086, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 2, 1780000.00),
(190, 50086, 56, 'Giày đá bóng DL Speed FG Jr', 'Vàng 33', 420000.00, 1, 420000.00),
(191, 50087, 32, 'Giày đá bóng DL Striker FG 2024', 'Vàng 39', 650000.00, 1, 650000.00),
(192, 50087, 96, 'Áo polo thể thao DL Classic 2024', 'Trắng XL', 280000.00, 2, 560000.00),
(193, 50088, 196, 'Quần short nữ DL Active', 'Hồng S', 200000.00, 2, 400000.00),
(194, 50089, 56, 'Giày đá bóng DL Speed FG Jr', 'Vàng 33', 420000.00, 3, 1260000.00),
(195, 50090, 98, 'Áo polo thể thao DL Classic 2024', 'Xanh navy L', 280000.00, 3, 840000.00),
(196, 50090, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 1, 890000.00),
(197, 50090, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(198, 50091, 93, 'Áo polo thể thao DL Classic 2024', 'Trắng S', 280000.00, 2, 560000.00),
(199, 50091, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 1, 890000.00),
(200, 50092, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(201, 50092, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 1, 890000.00),
(202, 50092, 28, 'Giày ASICS Gel-Nimbus 26', 'Xanh 42', 3300000.00, 2, 6600000.00),
(203, 50093, 250, 'San pham test QA live', 'Xanh XL', 250000.00, 3, 750000.00),
(204, 50093, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 2, 1780000.00),
(205, 50094, 21, 'Giày Adidas Runfalcon 3.0', 'Đen 41', 1650000.00, 1, 1650000.00),
(206, 50094, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 3, 2670000.00),
(207, 50094, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 2, 1780000.00),
(208, 50095, 149, 'Vợt cầu lông Yonex Astrox 99 Pro', 'Trang (4U) 4U/G5', 4600000.00, 3, 13800000.00),
(209, 50095, 64, 'Giày cầu lông Yonex Power Cushion 65Z3', 'Trắng 39', 2900000.00, 2, 5800000.00),
(210, 50096, 156, 'Vợt cầu lông Lining Windstorm 72', 'Xanh đường 3U', 1800000.00, 2, 3600000.00),
(211, 50096, 4, 'Giày chạy bộ DL Speed Pro X1', 'Đen 42', 890000.00, 1, 890000.00),
(212, 50097, 31, 'Giày đá bóng DL Striker FG 2024', 'Vàng 38', 650000.00, 1, 650000.00),
(213, 50098, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 3, 2670000.00),
(214, 50099, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 1, 890000.00),
(215, 50099, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 2, 1780000.00),
(216, 50100, 1, 'Giày chạy bộ DL Speed Pro X1', 'Đen 39', 890000.00, 1, 890000.00),
(217, 50100, 63, 'Giày cầu lông Yonex Power Cushion 65Z3', 'Trắng 38', 2900000.00, 1, 2900000.00),
(218, 50100, 152, 'Vợt cầu lông Yonex Nanoflare 700', 'Xanh lá 3U/G4', 3200000.00, 1, 3200000.00),
(219, 50101, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 2, 1780000.00),
(220, 50102, 24, 'Giày Adidas Runfalcon 3.0', 'Trắng 41', 1650000.00, 1, 1650000.00),
(221, 50102, 60, 'Giày đá bóng DL Speed FG Jr', 'Vàng 37', 420000.00, 1, 420000.00),
(222, 50103, 3, 'Giày chạy bộ DL Speed Pro X1', 'Đen 41', 890000.00, 1, 890000.00),
(223, 50103, 135, 'Áo sport bra Adidas Powerreact', 'Đen S', 680000.00, 3, 2040000.00),
(224, 50103, 226, 'Mũ thể thao DL DryCool', 'Trắng Free size', 165000.00, 1, 165000.00),
(225, 50104, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 1, 890000.00),
(226, 50104, 135, 'Áo sport bra Adidas Powerreact', 'Đen S', 680000.00, 1, 680000.00),
(227, 50105, 2, 'Giày chạy bộ DL Speed Pro X1', 'Đen 40', 890000.00, 1, 890000.00),
(228, 50106, 153, 'Vợt cầu lông Yonex Nanoflare 700', 'Đỏ 4U/G5', 3250000.00, 3, 9750000.00),
(229, 50106, 5, 'Giày chạy bộ DL Speed Pro X1', 'Đen 43', 890000.00, 2, 1780000.00),
(230, 50107, 84, 'Giày cầu lông nữ Yonex SHB-01MXLX', 'Trắng - Size 36', 3100000.00, 1, 3100000.00);

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

INSERT INTO `order_status_logs` (`log_id`, `order_id`, `changed_by`, `old_status`, `new_status`, `note`, `changed_at`) VALUES
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
(32, 17, 1, 'pending', 'delivered', NULL, '2026-06-05 16:33:49'),
(35, 20, NULL, NULL, 'pending', NULL, '2026-06-11 01:56:28'),
(36, 21, NULL, NULL, 'pending', NULL, '2026-06-11 02:02:11'),
(37, 22, NULL, NULL, 'pending', NULL, '2026-06-11 02:10:46'),
(38, 23, NULL, NULL, 'pending', NULL, '2026-06-11 03:20:09'),
(39, 24, NULL, NULL, 'pending', NULL, '2026-06-11 22:01:58'),
(40, 25, NULL, NULL, 'pending', NULL, '2026-06-11 22:53:18'),
(41, 25, 1, 'pending', 'confirmed', NULL, '2026-06-11 22:56:49'),
(42, 25, 1, 'unpaid', 'paid', 'Cập nhật thanh toán bởi admin', '2026-06-11 22:57:01'),
(43, 25, 1, 'paid', 'refunded', 'Cập nhật thanh toán bởi admin', '2026-06-11 22:57:06'),
(44, 24, 1, 'pending', 'confirmed', NULL, '2026-06-11 22:57:15'),
(45, 23, 1, 'pending', 'cancelled', NULL, '2026-06-11 22:57:24'),
(46, 25, 1, 'confirmed', 'processing', NULL, '2026-06-11 22:59:46'),
(47, 21, 1, 'pending', 'confirmed', NULL, '2026-06-11 23:03:04'),
(48, 21, 1, 'confirmed', 'processing', NULL, '2026-06-11 23:03:10'),
(49, 21, 1, 'processing', 'shipped', NULL, '2026-06-11 23:03:12'),
(50, 21, 1, 'shipped', 'delivered', NULL, '2026-06-11 23:03:15'),
(51, 21, 1, 'delivered', 'returned', NULL, '2026-06-11 23:03:25'),
(52, 21, 1, 'returned', 'pending', NULL, '2026-06-11 23:03:29'),
(53, 21, 1, 'pending', 'delivered', NULL, '2026-06-11 23:03:31'),
(54, 26, NULL, NULL, 'pending', NULL, '2026-06-11 23:40:05'),
(55, 27, NULL, NULL, 'pending', NULL, '2026-06-11 23:46:05'),
(56, 28, NULL, NULL, 'pending', NULL, '2026-06-11 23:47:45'),
(57, 29, NULL, NULL, 'pending', NULL, '2026-06-11 23:53:00'),
(58, 29, 3, 'pending', 'cancelled', 'Lý do: Tìm được sản phẩm khác', '2026-06-12 10:25:24'),
(59, 30, NULL, NULL, 'pending', NULL, '2026-06-12 10:29:03'),
(60, 30, 3, 'pending', 'cancelled', 'Lý do: Tìm được sản phẩm khác', '2026-06-12 10:29:37'),
(61, 28, 3, 'pending', 'cancelled', 'Lý do: Tìm được sản phẩm khác', '2026-06-12 10:38:27'),
(62, 31, NULL, NULL, 'pending', NULL, '2026-06-12 21:45:05'),
(63, 32, NULL, NULL, 'pending', NULL, '2026-06-12 21:52:59'),
(64, 33, NULL, NULL, 'pending', NULL, '2026-06-12 21:53:45'),
(65, 50000, NULL, NULL, 'pending', NULL, '2026-06-12 22:10:58'),
(66, 50000, 3, 'pending', 'cancelled', 'Lý do: Tìm được sản phẩm khác', '2026-06-12 22:22:29'),
(67, 50001, NULL, NULL, 'pending', NULL, '2026-06-12 22:24:02'),
(68, 50002, NULL, NULL, 'pending', NULL, '2026-06-12 22:34:13'),
(71, 50107, NULL, NULL, 'pending', NULL, '2026-06-14 01:25:20');

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

INSERT INTO `payment_transactions` (`txn_id`, `order_id`, `gateway`, `gateway_txn_id`, `amount`, `status`, `raw_response`, `created_at`, `updated_at`) VALUES
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
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
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

INSERT INTO `products` (`product_id`, `category_id`, `brand_id`, `product_name`, `slug`, `description`, `base_price`, `stock_quantity`, `is_visible`, `is_deleted`, `is_featured`, `featured_sort_order`, `avg_rating`, `total_reviews`, `created_at`, `updated_at`) VALUES
(1, 101, 1, 'Giày chạy bộ DL Speed Pro X1', 'giay-chay-bo-dl-speed-pro-x1', 'Giày chạy bộ chuyên nghiệp với đế giày EVA siêu nhẹ.', 890000.00, 50, 1, 0, 1, 4, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(2, 101, 1, 'Giày chạy bộ DL Runner Air 2024', 'giay-chay-bo-dl-runner-air-2024', 'Thiết kế khí động học giúp tăng tốc độ.', 750000.00, 60, 1, 0, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(3, 101, 2, 'Giày Nike Revolution 7', 'giay-nike-revolution-7', 'Giày chạy bộ phổ biến của Nike với đế giữa foam đen.', 1950000.00, 30, 1, 0, 1, 3, 4.50, 15, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(4, 101, 3, 'Giày Adidas Runfalcon 3.0', 'giay-adidas-runfalcon-3', 'Dòng giày chạy bộ giá tốt của Adidas.', 1650000.00, 40, 1, 0, 0, 999, 4.30, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(5, 101, 9, 'Giày ASICS Gel-Nimbus 26', 'giay-asics-gel-nimbus-26', 'Dòng giày cao cấp của ASICS, công nghệ đệm Gel.', 3200000.00, 20, 1, 0, 1, 5, 4.80, 18, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(6, 100, 1, 'Giày đá bóng DL Striker FG 2024', 'giay-da-bong-dl-striker-fg-2024', 'Giày đá bóng sân cỏ, đế cao su tự nhiên.', 650000.00, 80, 1, 0, 1, 6, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(7, 100, 1, 'Giày đá bóng DL Futsal Pro F10', 'giay-da-bong-dl-futsal-pro-f10', 'Thiết kế dành riêng cho futsal, đế bám sân nhà tốt.', 550000.00, 70, 1, 0, 0, 999, 4.30, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(8, 100, 3, 'Giày đá bóng Adidas Predator 24 FG', 'giay-da-bong-adidas-predator-24', 'Vật liệu Zone Skin giúp kiểm soát bóng chính xác hơn.', 2800000.00, 25, 1, 0, 1, 7, 4.70, 11, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(9, 100, 2, 'Giày đá bóng Nike Phantom GX2', 'giay-da-bong-nike-phantom-gx2', 'Công nghệ Aerotrak giúp bóng bay chính xác.', 3100000.00, 20, 1, 0, 0, 999, 4.60, 13, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(10, 100, 1, 'Giày đá bóng DL Speed FG Jr', 'giay-da-bong-dl-speed-fg-jr', 'Dành cho cầu thủ trẻ em và thiếu niên.', 420000.00, 100, 1, 0, 0, 999, 4.20, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(11, 102, 4, 'Giày cầu lông Yonex Power Cushion 65Z3', 'giay-cl-yonex-65z3', 'Đế giày Power Cushion hấp thụ chấn động.', 2900000.00, 30, 1, 0, 1, 9, 4.80, 16, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(12, 102, 1, 'Giày cầu lông DL Wing Pro 2024', 'giay-cl-dl-wing-pro', 'Giày cầu lông thương hiệu Đồng Lực, đế EVA nhẹ.', 680000.00, 60, 1, 0, 0, 999, 4.40, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(13, 102, 5, 'Giày cầu lông Lining Ranger TD', 'giay-cl-lining-ranger', 'Thương hiệu Lining nổi tiếng, đế giữa Light Foam.', 1200000.00, 40, 1, 0, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(14, 201, 4, 'Giày cầu lông nữ Yonex SHB-01MXLX', 'giay-cl-nu-yonex-01mx', 'Thiết kế dành cho nữ, trọng lượng siêu nhẹ 70g.', 3100000.00, 20, 1, 0, 1, 10, 4.90, 14, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(15, 201, 1, 'Giày cầu lông nữ DL Wing Lite', 'giay-cl-nu-dl-wing-lite', 'Phiên bản nhẹ hơn cho nữ, màu sắc nữ tính.', 620000.00, 50, 1, 0, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(16, 110, 1, 'Áo polo thể thao DL Classic 2024', 'ao-polo-dl-classic-2024', 'Vải Polyester 100%, thoáng khí, thoáng mát.', 280000.00, 120, 1, 0, 1, 8, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(17, 110, 1, 'Áo polo DL Pro Team 2024', 'ao-polo-dl-pro-team-2024', 'Phiên bản chuyên nghiệp cho các đội thể thao.', 320000.00, 100, 1, 0, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(18, 111, 1, 'Áo T-shirt DL Training Basic', 'ao-tshirt-dl-training-basic', 'Áo tập thể thao co tròn, vải Polyester thoáng mát.', 220000.00, 150, 1, 0, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(19, 111, 7, 'Áo Under Armour HeatGear Nam', 'ao-ua-heatgear-nam', 'Công nghệ HeatGear hút ẩm và làm mát nhanh.', 650000.00, 60, 1, 0, 1, 11, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(20, 111, 2, 'Áo Nike Dri-FIT Training', 'ao-nike-dri-fit-training', 'Công nghệ Dri-FIT thoát mồ hôi siêu nhanh.', 580000.00, 70, 1, 0, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(21, 210, 1, 'Áo phông nữ DL Sport Lite', 'ao-phong-nu-dl-sport-lite', 'Áo phông thể thao nữ, chất liệu co giãn 4 chiều.', 230000.00, 100, 1, 0, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(22, 210, 7, 'Áo Under Armour HeatGear Nữ', 'ao-ua-heatgear-nu', 'Phiên bản nữ của HeatGear, chất liệu siêu nhẹ.', 620000.00, 50, 1, 0, 1, 12, 4.60, 10, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(23, 211, 3, 'Áo sport bra Adidas Powerreact', 'ao-sport-bra-adidas', 'Áo lót thể thao nữ Adidas, dây vai lưới thông gió.', 680000.00, 45, 1, 0, 1, 14, 4.50, 11, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(24, 211, 2, 'Áo sport bra Nike Indy', 'ao-sport-bra-nike-indy', 'Nike Indy Medium-Support, chất liệu Dri-FIT.', 750000.00, 40, 1, 0, 0, 999, 4.70, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(25, 302, 4, 'Vợt cầu lông Yonex Astrox 99 Pro', 'vot-cl-yonex-astrox-99-pro', 'Vợt tấn công hàng đầu Yonex, khung carbon cao cấp.', 4500000.00, 9, 1, 0, 1, 1, 4.90, 22, '2026-05-29 19:17:55', '2026-06-07 19:54:05'),
(26, 302, 4, 'Vợt cầu lông Yonex Nanoflare 700', 'vot-cl-yonex-nanoflare-700', 'Vợt phản công nhanh, khung Tungsten Mesh.', 3200000.00, 25, 1, 0, 1, 15, 4.80, 18, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(27, 302, 5, 'Vợt cầu lông Lining Windstorm 72', 'vot-cl-lining-windstorm-72', 'Vợt phản công siêu nhẹ 72g, khung carbon tổ hợp.', 1800000.00, 35, 1, 0, 0, 999, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(28, 302, 1, 'Vợt cầu lông DL Pro 500', 'vot-cl-dl-pro-500', 'Vợt cầu lông thương hiệu Đồng Lực dành cho người chơi phổ thông.', 480000.00, 60, 1, 0, 0, 999, 4.20, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(29, 302, 1, 'Vợt cầu lông DL Fighter 200', 'vot-cl-dl-fighter-200', 'Vợt dành cho người mới bắt đầu, dễ cầm tay.', 280000.00, 80, 1, 0, 0, 999, 4.10, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(30, 302, 10, 'Vợt cầu lông Head Zephyr Pro', 'vot-cl-head-zephyr-pro', 'Head Zephyr Pro trọng lượng 78g, phù hợp trung-cao cấp.', 2100000.00, 28, 1, 0, 0, 999, 4.60, 12, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(31, 303, 4, 'Cầu lông Yonex AS-05 (hop 12 qua)', 'cau-long-yonex-as05-12', 'Cầu lông lưới ngắn Yonex AS-05.', 160000.00, 200, 1, 0, 1, 16, 4.70, 20, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(32, 303, 4, 'Cầu lông Yonex AS-30 (hop 12 qua)', 'cau-long-yonex-as30-12', 'Cầu lông lưới vit cấp cao Yonex AS-30.', 280000.00, 150, 1, 0, 0, 999, 4.80, 15, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(33, 303, 1, 'Cầu lông DL Training (hop 12 qua)', 'cau-long-dl-training-12', 'Cầu lông Đồng Lực dùng tập luyện hàng ngày.', 85000.00, 300, 1, 0, 0, 999, 4.20, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(34, 303, 5, 'Cầu lông Lining A+90D (hop 12 qua)', 'cau-long-lining-a90d', 'Cầu lông lưới vit Lining A+90D, lớp 76 - 78.', 210000.00, 180, 1, 0, 0, 999, 4.60, 11, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(35, 113, 1, 'Quần short thể thao DL Training 2024', 'quan-short-dl-training-2024', 'Quần short thể thao nam chất liệu Polyester khô nhanh.', 220000.00, 100, 1, 0, 0, 999, 4.40, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(36, 113, 2, 'Quần short Nike Dri-FIT 5 inch', 'quan-short-nike-dri-fit-5', 'Quần short chạy bộ Nike 5 inch, Dri-FIT thoát mồ hôi.', 580000.00, 60, 1, 0, 1, 17, 4.60, 9, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(37, 212, 1, 'Quần short nữ DL Active', 'quan-short-nu-dl-active', 'Quần short nữ với dây lưng co giãn.', 200000.00, 90, 1, 0, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(38, 213, 7, 'Quần legging Under Armour HeatGear', 'quan-legging-ua-heatgear', 'Quần legging nữ 7/8, chất liệu HeatGear co giãn 4 chiều.', 750000.00, 45, 1, 0, 1, 18, 4.70, 14, '2026-05-29 19:17:55', '2026-06-06 02:51:23'),
(39, 213, 3, 'Quần legging Adidas Optime 7/8', 'quan-legging-adidas-optime', 'Quần legging nữ Adidas, AEROREADY thoát ẩm.', 680000.00, 50, 1, 0, 0, 999, 4.50, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(40, 300, 3, 'Bóng đá sân cỏ Adidas Al Rihla', 'bong-da-adidas-al-rihla', 'Bóng đá chính thức FIFA Quality Pro.', 1200000.00, 30, 1, 0, 1, 13, 4.70, 12, '2026-05-29 19:17:55', '2026-06-06 02:53:39'),
(41, 300, 1, 'Bóng đá sân cỏ DL Classic 5', 'bong-da-dl-classic-5', 'Bóng đá số 5 chất liệu PVC cao cấp.', 180000.00, 80, 1, 0, 0, 999, 4.20, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(42, 301, 1, 'Bóng Futsal DL Super', 'bong-futsal-dl-super', 'Bóng futsal số 4, chất liệu PU 4 lớp.', 250000.00, 60, 1, 0, 0, 999, 4.40, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(43, 305, 1, 'Bóng Pickleball DL Outdoor', 'bong-pickleball-dl-outdoor', 'Bóng pickleball 40 lỗ, chất liệu HDPE.', 120000.00, 100, 1, 0, 0, 999, 4.30, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(44, 307, 1, 'Tạ tay DL Vinyl 1kg - 10kg', 'ta-tay-dl-vinyl', 'Tạ tay phủ cao su vinyl kháng trọt.', 85000.00, 200, 1, 0, 0, 999, 4.50, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(45, 13, 1, 'Mũ thể thao DL DryCool', 'mu-the-thao-dl-drycool', 'Mũ thể thao kiểu snapback, chất liệu thoáng khí.', 165000.00, 80, 1, 0, 0, 999, 4.30, 6, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(46, 13, 1, 'Balo thể thao DL Sport 20L', 'balo-dl-sport-20l', 'Balo thể thao dung tích 20L.', 420000.00, 40, 1, 0, 0, 999, 4.50, 8, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(47, 13, 4, 'Tất cầu lông Yonex 75th (3 doi)', 'tat-cau-long-yonex-75th', '3 đôi tất cầu lông Yonex, chất liệu bông cao cấp.', 180000.00, 100, 1, 0, 0, 999, 4.60, 10, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(48, 13, 1, 'Tất thể thao DL Sport (5 doi)', 'tat-the-thao-dl-sport-5doi', '5 đôi tất thể thao DL, chất liệu cotton pha.', 95000.00, 150, 1, 0, 0, 999, 4.20, 7, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(49, 23, 1, 'Mũ thể thao nữ DL Lily', 'mu-the-thao-nu-dl-lily', 'Mũ nữ kiểu bucket hat thời trang.', 155000.00, 60, 1, 0, 0, 999, 4.30, 5, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(50, 23, 3, 'Balo nữ Adidas Classic BOS', 'balo-nu-adidas-classic', 'Balo nữ Adidas Classic BOS dung tích 22L.', 650000.00, 30, 1, 0, 0, 999, 4.60, 9, '2026-05-29 19:17:55', '2026-06-06 02:34:43'),
(56, 34, 4, 'sản phẩm test ẩn hiện', 'san-phm-test-n-hin', '', 1000000.00, 12, 0, 0, 1, 2, NULL, 0, '2026-06-06 02:20:00', '2026-06-12 11:50:34'),
(58, 315, 11, 'Giày Pickleball Axeron Pro V1-C439', 'giay-pickleball-v1-c439', '<p>Sản phẩm Giày Pickleball Axeron Pro V1-C439 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 835000.00, 42, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(59, 315, 11, 'Giày Pickleball Axeron Pro V2-D06C', 'giay-pickleball-v2-d06c', '<p>Sản phẩm Giày Pickleball Axeron Pro V2-D06C với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1459000.00, 75, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(60, 315, 11, 'Giày Pickleball Axeron Pro V3-0BD7', 'giay-pickleball-v3-0bd7', '<p>Sản phẩm Giày Pickleball Axeron Pro V3-0BD7 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 926000.00, 79, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(61, 315, 11, 'Giày Pickleball Axeron Pro V4-0A99', 'giay-pickleball-v4-0a99', '<p>Sản phẩm Giày Pickleball Axeron Pro V4-0A99 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1095000.00, 22, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(62, 315, 11, 'Giày Pickleball Axeron Pro V5-F316', 'giay-pickleball-v5-f316', '<p>Sản phẩm Giày Pickleball Axeron Pro V5-F316 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 854000.00, 63, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(63, 315, 11, 'Giày Pickleball Axeron Pro V6-CD61', 'giay-pickleball-v6-cd61', '<p>Sản phẩm Giày Pickleball Axeron Pro V6-CD61 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1289000.00, 67, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(64, 315, 11, 'Giày Pickleball Axeron Pro V7-076E', 'giay-pickleball-v7-076e', '<p>Sản phẩm Giày Pickleball Axeron Pro V7-076E với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 936000.00, 26, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(65, 315, 11, 'Giày Pickleball Axeron Pro V8-972E', 'giay-pickleball-v8-972e', '<p>Sản phẩm Giày Pickleball Axeron Pro V8-972E với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1251000.00, 58, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(66, 315, 11, 'Giày Pickleball Axeron Pro V9-CBAB', 'giay-pickleball-v9-cbab', '<p>Sản phẩm Giày Pickleball Axeron Pro V9-CBAB với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1343000.00, 55, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(67, 315, 11, 'Giày Pickleball Axeron Pro V10-77AD', 'giay-pickleball-v10-77ad', '<p>Sản phẩm Giày Pickleball Axeron Pro V10-77AD với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 938000.00, 40, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(68, 100, 11, 'Giày Bóng Đá Axeron V1-FF6F', 'giay-da-bong-v1-ff6f', '<p>Sản phẩm Giày Bóng Đá Axeron V1-FF6F với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1677000.00, 19, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(69, 100, 11, 'Giày Bóng Đá Axeron V2-0429', 'giay-da-bong-v2-0429', '<p>Sản phẩm Giày Bóng Đá Axeron V2-0429 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1284000.00, 20, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(70, 100, 11, 'Giày Bóng Đá Axeron V3-8DBF', 'giay-da-bong-v3-8dbf', '<p>Sản phẩm Giày Bóng Đá Axeron V3-8DBF với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1760000.00, 12, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(71, 100, 11, 'Giày Bóng Đá Axeron V4-7620', 'giay-da-bong-v4-7620', '<p>Sản phẩm Giày Bóng Đá Axeron V4-7620 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1204000.00, 25, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(72, 100, 11, 'Giày Bóng Đá Axeron V5-C9CD', 'giay-da-bong-v5-c9cd', '<p>Sản phẩm Giày Bóng Đá Axeron V5-C9CD với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1591000.00, 82, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(73, 316, 11, 'Giày Cầu Lông Axeron Smash V1-5FD8', 'giay-cau-long-v1-5fd8', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V1-5FD8 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1040000.00, 66, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(74, 316, 11, 'Giày Cầu Lông Axeron Smash V2-28A4', 'giay-cau-long-v2-28a4', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V2-28A4 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 921000.00, 79, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(75, 316, 11, 'Giày Cầu Lông Axeron Smash V3-231E', 'giay-cau-long-v3-231e', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V3-231E với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 896000.00, 35, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(76, 316, 11, 'Giày Cầu Lông Axeron Smash V4-215C', 'giay-cau-long-v4-215c', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V4-215C với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1029000.00, 10, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(77, 316, 11, 'Giày Cầu Lông Axeron Smash V5-3B23', 'giay-cau-long-v5-3b23', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V5-3B23 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1073000.00, 16, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(78, 316, 11, 'Giày Cầu Lông Axeron Smash V6-2124', 'giay-cau-long-v6-2124', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V6-2124 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 803000.00, 93, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(79, 316, 11, 'Giày Cầu Lông Axeron Smash V7-C6A6', 'giay-cau-long-v7-c6a6', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V7-C6A6 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 846000.00, 98, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(80, 316, 11, 'Giày Cầu Lông Axeron Smash V8-AC76', 'giay-cau-long-v8-ac76', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V8-AC76 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1072000.00, 52, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(81, 316, 11, 'Giày Cầu Lông Axeron Smash V9-C744', 'giay-cau-long-v9-c744', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V9-C744 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 687000.00, 45, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(82, 316, 11, 'Giày Cầu Lông Axeron Smash V10-79F1', 'giay-cau-long-v10-79f1', '<p>Sản phẩm Giày Cầu Lông Axeron Smash V10-79F1 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 675000.00, 31, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(83, 317, 11, 'Giày Chạy Bộ Axeron Run V1-DA7E', 'giay-chay-bo-v1-da7e', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V1-DA7E với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 570000.00, 12, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(84, 317, 11, 'Giày Chạy Bộ Axeron Run V2-9BCA', 'giay-chay-bo-v2-9bca', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V2-9BCA với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1447000.00, 34, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(85, 317, 11, 'Giày Chạy Bộ Axeron Run V3-F9B5', 'giay-chay-bo-v3-f9b5', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V3-F9B5 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 950000.00, 86, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(86, 317, 11, 'Giày Chạy Bộ Axeron Run V4-455D', 'giay-chay-bo-v4-455d', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V4-455D với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 980000.00, 50, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(87, 317, 11, 'Giày Chạy Bộ Axeron Run V5-B9B5', 'giay-chay-bo-v5-b9b5', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V5-B9B5 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 568000.00, 64, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(88, 317, 11, 'Giày Chạy Bộ Axeron Run V6-D657', 'giay-chay-bo-v6-d657', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V6-D657 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 843000.00, 84, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(89, 317, 11, 'Giày Chạy Bộ Axeron Run V7-EBB9', 'giay-chay-bo-v7-ebb9', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V7-EBB9 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1488000.00, 83, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(90, 317, 11, 'Giày Chạy Bộ Axeron Run V8-2DEA', 'giay-chay-bo-v8-2dea', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V8-2DEA với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1261000.00, 98, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(91, 317, 11, 'Giày Chạy Bộ Axeron Run V9-2FED', 'giay-chay-bo-v9-2fed', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V9-2FED với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 1152000.00, 77, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(92, 317, 11, 'Giày Chạy Bộ Axeron Run V10-82E4', 'giay-chay-bo-v10-82e4', '<p>Sản phẩm Giày Chạy Bộ Axeron Run V10-82E4 với thiết kế hiện đại, chất liệu bền bỉ mang đến trải nghiệm tập luyện thể thao tuyệt vời.</p>', 617000.00, 19, 1, 0, 0, 999, NULL, 0, '2026-06-10 00:10:43', '2026-06-10 00:10:43'),
(93, 319, 10, 'Bộ Suvec Nam Thể Thao 4046', 'bo-suvec-nam-the-thao-4046-1781112128-1', 'Đây là sản phẩm Bộ Suvec Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 900000.00, 90, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(94, 319, 4, 'Bộ Suvec Nam Thể Thao E1F4', 'bo-suvec-nam-the-thao-e1f4-1781112128-2', 'Đây là sản phẩm Bộ Suvec Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 600000.00, 22, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(95, 320, 5, 'Bộ Bóng Đá Nam Thể Thao C853', 'bo-bong-da-nam-the-thao-c853-1781112128-1', 'Đây là sản phẩm Bộ Bóng Đá Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 1000000.00, 88, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(96, 320, 6, 'Bộ Bóng Đá Nam Thể Thao 790B', 'bo-bong-da-nam-the-thao-790b-1781112128-2', 'Đây là sản phẩm Bộ Bóng Đá Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 500000.00, 61, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(97, 321, 9, 'Bộ Cầu Lông Nam Thể Thao D4D2', 'bo-cau-long-nam-the-thao-d4d2-1781112128-1', 'Đây là sản phẩm Bộ Cầu Lông Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 850000.00, 68, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(98, 321, 10, 'Bộ Cầu Lông Nam Thể Thao C815', 'bo-cau-long-nam-the-thao-c815-1781112128-2', 'Đây là sản phẩm Bộ Cầu Lông Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 550000.00, 73, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(99, 322, 6, 'Bộ Bóng Chuyền Nam Thể Thao F2AD', 'bo-bong-chuyen-nam-the-thao-f2ad-1781112128-1', 'Đây là sản phẩm Bộ Bóng Chuyền Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 250000.00, 54, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(100, 322, 1, 'Bộ Bóng Chuyền Nam Thể Thao 3CB9', 'bo-bong-chuyen-nam-the-thao-3cb9-1781112128-2', 'Đây là sản phẩm Bộ Bóng Chuyền Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 250000.00, 88, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(101, 323, 9, 'Bộ Pickleball Nam Thể Thao 8D6C', 'bo-pickleball-nam-the-thao-8d6c-1781112128-1', 'Đây là sản phẩm Bộ Pickleball Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 100000.00, 49, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(102, 323, 8, 'Bộ Pickleball Nam Thể Thao 5BB5', 'bo-pickleball-nam-the-thao-5bb5-1781112128-2', 'Đây là sản phẩm Bộ Pickleball Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 550000.00, 69, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(103, 324, 4, 'Mũ Nam Thể Thao D313', 'mu-nam-the-thao-d313-1781112128-1', 'Đây là sản phẩm Mũ Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 200000.00, 56, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(104, 324, 9, 'Mũ Nam Thể Thao D59B', 'mu-nam-the-thao-d59b-1781112128-2', 'Đây là sản phẩm Mũ Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 250000.00, 66, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(105, 325, 10, 'Balo Nam Thể Thao 9033', 'balo-nam-the-thao-9033-1781112128-1', 'Đây là sản phẩm Balo Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 800000.00, 13, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(106, 325, 4, 'Balo Nam Thể Thao 0FBC', 'balo-nam-the-thao-0fbc-1781112128-2', 'Đây là sản phẩm Balo Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 600000.00, 60, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(107, 326, 6, 'Tất Nam Thể Thao 1655', 'tat-nam-the-thao-1655-1781112128-1', 'Đây là sản phẩm Tất Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 500000.00, 48, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(108, 326, 2, 'Tất Nam Thể Thao 5B0E', 'tat-nam-the-thao-5b0e-1781112128-2', 'Đây là sản phẩm Tất Nam cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 350000.00, 64, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(109, 327, 4, 'Bộ Suvec Nữ Thể Thao 09BF', 'bo-suvec-nu-the-thao-09bf-1781112128-1', 'Đây là sản phẩm Bộ Suvec Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 100000.00, 63, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(110, 327, 3, 'Bộ Suvec Nữ Thể Thao 5F29', 'bo-suvec-nu-the-thao-5f29-1781112128-2', 'Đây là sản phẩm Bộ Suvec Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 950000.00, 89, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(111, 328, 4, 'Bộ Cầu Lông Nữ Thể Thao 2C19', 'bo-cau-long-nu-the-thao-2c19-1781112128-1', 'Đây là sản phẩm Bộ Cầu Lông Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 350000.00, 45, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(112, 328, 2, 'Bộ Cầu Lông Nữ Thể Thao 0143', 'bo-cau-long-nu-the-thao-0143-1781112128-2', 'Đây là sản phẩm Bộ Cầu Lông Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 800000.00, 27, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(113, 329, 1, 'Bộ Bóng Chuyền Nữ Thể Thao A42C', 'bo-bong-chuyen-nu-the-thao-a42c-1781112128-1', 'Đây là sản phẩm Bộ Bóng Chuyền Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 500000.00, 90, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(114, 329, 9, 'Bộ Bóng Chuyền Nữ Thể Thao 8123', 'bo-bong-chuyen-nu-the-thao-8123-1781112128-2', 'Đây là sản phẩm Bộ Bóng Chuyền Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 850000.00, 39, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(115, 330, 11, 'Bộ Pickleball Nữ Thể Thao 073F', 'bo-pickleball-nu-the-thao-073f-1781112128-1', 'Đây là sản phẩm Bộ Pickleball Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 100000.00, 37, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(116, 330, 11, 'Bộ Pickleball Nữ Thể Thao 49AC', 'bo-pickleball-nu-the-thao-49ac-1781112128-2', 'Đây là sản phẩm Bộ Pickleball Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 500000.00, 35, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(117, 331, 9, 'Mũ Nữ Thể Thao 0D73', 'mu-nu-the-thao-0d73-1781112128-1', 'Đây là sản phẩm Mũ Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 100000.00, 88, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(118, 331, 2, 'Mũ Nữ Thể Thao D20A', 'mu-nu-the-thao-d20a-1781112128-2', 'Đây là sản phẩm Mũ Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 150000.00, 88, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(119, 332, 6, 'Balo Nữ Thể Thao 972D', 'balo-nu-the-thao-972d-1781112128-1', 'Đây là sản phẩm Balo Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 950000.00, 49, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(120, 332, 5, 'Balo Nữ Thể Thao 113B', 'balo-nu-the-thao-113b-1781112128-2', 'Đây là sản phẩm Balo Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 200000.00, 61, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(121, 333, 11, 'Tất Nữ Thể Thao 7C88', 'tat-nu-the-thao-7c88-1781112128-1', 'Đây là sản phẩm Tất Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 600000.00, 49, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(122, 333, 5, 'Tất Nữ Thể Thao 28A6', 'tat-nu-the-thao-28a6-1781112128-2', 'Đây là sản phẩm Tất Nữ cực kỳ chất lượng, mang lại cảm giác thoải mái và hiệu năng ấn tượng.', 600000.00, 100, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:22:08', '2026-06-11 00:22:08'),
(123, 103, 1, 'Giày Bóng Rổ Signature 4201', 'giay-bong-ro-signature-4201-1781112536-1', 'Đây là sản phẩm Giày Bóng Rổ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1300000.00, 66, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(124, 103, 8, 'Giày Bóng Rổ Signature 5636', 'giay-bong-ro-signature-5636-1781112536-2', 'Đây là sản phẩm Giày Bóng Rổ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1450000.00, 21, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(125, 104, 2, 'Giày Thể Thao Nam Signature D354', 'giay-the-thao-nam-signature-d354-1781112536-1', 'Đây là sản phẩm Giày Thể Thao Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1150000.00, 75, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(126, 104, 9, 'Giày Thể Thao Nam Signature ED8C', 'giay-the-thao-nam-signature-ed8c-1781112536-2', 'Đây là sản phẩm Giày Thể Thao Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 250000.00, 85, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(127, 105, 10, 'Dép Nam Signature 8580', 'dep-nam-signature-8580-1781112536-1', 'Đây là sản phẩm Dép Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 550000.00, 14, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(128, 105, 3, 'Dép Nam Signature C888', 'dep-nam-signature-c888-1781112536-2', 'Đây là sản phẩm Dép Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 500000.00, 75, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(129, 112, 9, 'Áo Khoác Nam Signature 7846', 'ao-khoac-nam-signature-7846-1781112536-1', 'Đây là sản phẩm Áo Khoác Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 250000.00, 41, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(130, 112, 8, 'Áo Khoác Nam Signature 05C4', 'ao-khoac-nam-signature-05c4-1781112536-2', 'Đây là sản phẩm Áo Khoác Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 800000.00, 35, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(131, 114, 9, 'Quần Dài Nam Signature 8E6D', 'quan-dai-nam-signature-8e6d-1781112536-1', 'Đây là sản phẩm Quần Dài Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 350000.00, 12, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(132, 114, 5, 'Quần Dài Nam Signature DAF8', 'quan-dai-nam-signature-daf8-1781112536-2', 'Đây là sản phẩm Quần Dài Nam có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 850000.00, 57, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(133, 200, 4, 'Giày Chạy Bộ Nữ Signature 6DE3', 'giay-chay-bo-nu-signature-6de3-1781112536-1', 'Đây là sản phẩm Giày Chạy Bộ Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1100000.00, 88, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(134, 200, 6, 'Giày Chạy Bộ Nữ Signature 5F21', 'giay-chay-bo-nu-signature-5f21-1781112536-2', 'Đây là sản phẩm Giày Chạy Bộ Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1500000.00, 100, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(135, 202, 1, 'Giày Thể Thao Nữ Signature 97E1', 'giay-the-thao-nu-signature-97e1-1781112536-1', 'Đây là sản phẩm Giày Thể Thao Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 350000.00, 48, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(136, 202, 5, 'Giày Thể Thao Nữ Signature 5703', 'giay-the-thao-nu-signature-5703-1781112536-2', 'Đây là sản phẩm Giày Thể Thao Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 650000.00, 31, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(137, 203, 10, 'Dép Nữ Signature 7247', 'dep-nu-signature-7247-1781112536-1', 'Đây là sản phẩm Dép Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 950000.00, 43, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(138, 203, 1, 'Dép Nữ Signature 3EBA', 'dep-nu-signature-3eba-1781112536-2', 'Đây là sản phẩm Dép Nữ có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 500000.00, 56, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(139, 214, 10, 'Chân Váy Signature 58EC', 'chan-vay-signature-58ec-1781112536-1', 'Đây là sản phẩm Chân Váy có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1450000.00, 25, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(140, 214, 1, 'Chân Váy Signature 8256', 'chan-vay-signature-8256-1781112536-2', 'Đây là sản phẩm Chân Váy có thiết kế nổi bật, phù hợp cho nhiều hoạt động thể thao.', 1400000.00, 75, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:28:56', '2026-06-11 00:28:56'),
(141, 33, 11, 'Bóng Rổ 6FC2', 'bong-ro-6fc2-1781112741-1', 'Đây là sản phẩm Bóng Rổ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1350000.00, 91, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(142, 33, 1, 'Bóng Rổ 237A', 'bong-ro-237a-1781112741-2', 'Đây là sản phẩm Bóng Rổ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2500000.00, 37, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(143, 33, 11, 'Bóng Rổ 4F74', 'bong-ro-4f74-1781112741-3', 'Đây là sản phẩm Bóng Rổ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1750000.00, 92, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(144, 33, 10, 'Bóng Rổ 003E', 'bong-ro-003e-1781112741-4', 'Đây là sản phẩm Bóng Rổ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2050000.00, 21, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(145, 33, 10, 'Bóng Rổ 74E2', 'bong-ro-74e2-1781112741-5', 'Đây là sản phẩm Bóng Rổ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1050000.00, 35, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-14 01:53:32'),
(146, 34, 10, 'Bóng Chuyền 12DA', 'bong-chuyen-12da-1781112741-1', 'Đây là sản phẩm Bóng Chuyền chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2000000.00, 15, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(147, 34, 9, 'Bóng Chuyền 83FA', 'bong-chuyen-83fa-1781112741-2', 'Đây là sản phẩm Bóng Chuyền chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1000000.00, 33, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(148, 34, 10, 'Bóng Chuyền 6607', 'bong-chuyen-6607-1781112741-3', 'Đây là sản phẩm Bóng Chuyền chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2250000.00, 58, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(149, 34, 4, 'Bóng Chuyền D1F0', 'bong-chuyen-d1f0-1781112741-4', 'Đây là sản phẩm Bóng Chuyền chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1900000.00, 57, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(150, 304, 10, 'Vợt Pickleball 784A', 'vot-pickleball-784a-1781112741-1', 'Đây là sản phẩm Vợt Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 950000.00, 92, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(151, 304, 11, 'Vợt Pickleball 0CC4', 'vot-pickleball-0cc4-1781112741-2', 'Đây là sản phẩm Vợt Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2350000.00, 51, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(152, 304, 1, 'Vợt Pickleball 345C', 'vot-pickleball-345c-1781112741-3', 'Đây là sản phẩm Vợt Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1850000.00, 50, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(153, 304, 5, 'Vợt Pickleball 38FF', 'vot-pickleball-38ff-1781112741-4', 'Đây là sản phẩm Vợt Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 700000.00, 73, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(154, 304, 2, 'Vợt Pickleball 13D4', 'vot-pickleball-13d4-1781112741-5', 'Đây là sản phẩm Vợt Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 550000.00, 57, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(155, 305, 6, 'Bóng Pickleball 21CE', 'bong-pickleball-21ce-1781112741-1', 'Đây là sản phẩm Bóng Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1300000.00, 93, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(156, 305, 1, 'Bóng Pickleball 3A12', 'bong-pickleball-3a12-1781112741-2', 'Đây là sản phẩm Bóng Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2350000.00, 95, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(157, 305, 10, 'Bóng Pickleball 11C4', 'bong-pickleball-11c4-1781112741-3', 'Đây là sản phẩm Bóng Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2300000.00, 22, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(158, 305, 6, 'Bóng Pickleball BEAE', 'bong-pickleball-beae-1781112741-4', 'Đây là sản phẩm Bóng Pickleball chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 850000.00, 82, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(159, 306, 6, 'Máy Chạy Bộ 1719', 'may-chay-bo-1719-1781112741-1', 'Đây là sản phẩm Máy Chạy Bộ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1900000.00, 35, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(160, 306, 11, 'Máy Chạy Bộ D499', 'may-chay-bo-d499-1781112741-2', 'Đây là sản phẩm Máy Chạy Bộ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1000000.00, 41, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(161, 306, 6, 'Máy Chạy Bộ F865', 'may-chay-bo-f865-1781112741-3', 'Đây là sản phẩm Máy Chạy Bộ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 650000.00, 49, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(162, 306, 10, 'Máy Chạy Bộ 2C9F', 'may-chay-bo-2c9f-1781112741-4', 'Đây là sản phẩm Máy Chạy Bộ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1550000.00, 78, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(163, 306, 3, 'Máy Chạy Bộ 74F4', 'may-chay-bo-74f4-1781112741-5', 'Đây là sản phẩm Máy Chạy Bộ chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2200000.00, 86, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(164, 308, 6, 'Giàn Tập 6E3A', 'gian-tap-6e3a-1781112741-1', 'Đây là sản phẩm Giàn Tập chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1450000.00, 84, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(165, 308, 10, 'Giàn Tập AF69', 'gian-tap-af69-1781112741-2', 'Đây là sản phẩm Giàn Tập chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1750000.00, 10, 1, 0, 1, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 02:26:02'),
(166, 308, 9, 'Giàn Tập 2798', 'gian-tap-2798-1781112741-3', 'Đây là sản phẩm Giàn Tập chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 600000.00, 17, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(167, 308, 9, 'Giàn Tập BBDF', 'gian-tap-bbdf-1781112741-4', 'Đây là sản phẩm Giàn Tập chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 2000000.00, 47, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(168, 308, 1, 'Giàn Tập 2AD4', 'gian-tap-2ad4-1781112741-5', 'Đây là sản phẩm Giàn Tập chính hãng, độ bền cao, hỗ trợ tốt nhất cho bạn.', 1400000.00, 87, 1, 0, 0, 999, 5.00, 1, '2026-06-11 00:32:21', '2026-06-11 00:32:21'),
(169, 112, 11, 'San pham test QA live', 'san-pham-test-qa-live', '', 250000.00, 10, 1, 1, 0, 999, NULL, 0, '2026-06-11 03:00:42', '2026-06-11 03:04:42'),
(174, 1, 3, 'BANKING Test 3K', 'san-pham-test-3k', '<p>Đây là sản phẩm giả lập dùng để test hệ thống thanh toán qua cổng PayOS.</p>', 3000.00, 997, 1, 0, 0, 999, NULL, 0, '2026-06-11 23:39:07', '2026-06-12 10:38:27'),
(175, 1, 3, 'BANKING Test 5K', 'san-pham-test-5k', '<p>Đây là sản phẩm giả lập dùng để test hệ thống thanh toán qua cổng PayOS.</p>', 5000.00, 999, 1, 0, 0, 999, NULL, 0, '2026-06-11 23:39:07', '2026-06-11 23:39:07'),
(176, 1, 3, 'BANKING Test 10K', 'san-pham-test-10k', '<p>Đây là sản phẩm giả lập dùng để test hệ thống thanh toán qua cổng PayOS.</p>', 10000.00, 997, 1, 0, 0, 999, NULL, 0, '2026-06-11 23:39:07', '2026-06-12 22:22:29');

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

INSERT INTO `product_images` (`image_id`, `product_id`, `image_url`, `alt_text`, `sort_order`, `is_primary`, `color`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', 'Giày chạy bộ DL Speed Pro X1', 0, 1, NULL),
(2, 2, 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=800&auto=format&fit=crop', 'Giày chạy bộ DL Runner Air', 0, 1, NULL),
(3, 3, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=800&auto=format&fit=crop', 'Nike Revolution 7', 0, 1, NULL),
(4, 4, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=800&auto=format&fit=crop', 'Adidas Runfalcon 3', 0, 1, NULL),
(5, 5, 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?q=80&w=800&auto=format&fit=crop', 'ASICS Gel-Nimbus 26', 0, 1, NULL),
(6, 6, 'https://assets.adidas.com/images/w_766,h_766,f_auto,q_auto,fl_lossy,c_fill,g_auto/ef9f060621764bd29e015b2d40dc2b13_9366/Giay_DJa_Bong_Trong_Nha_Predator_League_Mau_xanh_da_troi_IF6393_01_standard_hover.jpg', 'Giày đá bóng DL Striker FG', 0, 1, NULL),
(7, 7, 'https://cdn.hstatic.net/products/200000278317/giay-da-bong-nike-tiempo-legend-10-pro-tf-dv4336-400-xanh-trang-1r_8ead71187a914a40a0a1c9563644e78d_master.jpg', 'Giày đá bóng Futsal Pro', 0, 1, NULL),
(8, 8, 'https://cdn.hstatic.net/products/1000061481/anh_sp_add_web_1-01-02-02-_2-2-2-2_3e4939d7306744d78811f350981dea04_large.jpg', 'Adidas Predator 24', 0, 1, NULL),
(9, 9, 'https://bizweb.dktcdn.net/thumb/large/100/108/842/products/8-7.jpg?v=1780477421603', 'Nike Phantom GX2', 0, 1, NULL),
(10, 10, 'https://sumstore.vn/wp-content/uploads/2024/10/Lefus-Z001-FG-den.jpg', 'DL Speed FG Jr', 0, 1, NULL),
(11, 11, 'https://cdn.shopvnb.com/img/300x300/uploads/san_pham/giay-cau-long-yonex-strider-flow_1733432842.webp', 'Yonex Power Cushion 65Z3', 0, 1, NULL),
(12, 12, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRfqUUw5cPK4aeN2MbEjS0GGfUwuhCNbcWwKQ&s', 'DL Wing Pro 2024', 0, 1, NULL),
(13, 13, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXPAbT9161PajBNiTQnJ_alQ5A67BKcP8baw&s', 'Lining Ranger TD', 0, 1, NULL),
(14, 14, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', 'Yonex SHB-01MXLX Nữ', 0, 1, NULL),
(15, 15, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', 'DL Wing Lite Nữ', 0, 1, NULL),
(16, 16, 'https://deltasport.vn/wp-content/uploads/2026/03/PO121M1-ao-polo-nam-479K-2.png', 'DL Polo Classic', 0, 1, NULL),
(17, 17, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=800&auto=format&fit=crop', 'DL Polo Pro Team', 0, 1, NULL),
(18, 18, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?q=80&w=800&auto=format&fit=crop', 'DL T-shirt Basic', 0, 1, NULL),
(19, 19, 'https://underarmour.com.vn/cdn/shop/files/1361518-001-1.jpg', 'UA HeatGear Nam', 0, 1, NULL),
(20, 20, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=800&auto=format&fit=crop', 'Nike Dri-FIT Training', 0, 1, NULL),
(21, 21, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=800&auto=format&fit=crop', 'DL Sport Lite Nữ', 0, 1, NULL),
(22, 22, 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?q=80&w=800&auto=format&fit=crop', 'UA HeatGear Nữ', 0, 1, NULL),
(23, 23, 'https://encrypted-tbn1.gstatic.com/shopping?q=tbn:ANd9GcSWs14SungTS0EfUErNDyoOW2PQq-GzZylKjPKze0vGCwvNuBu81Q72lmzZEBIp6ZYAUEdSNfdDj-6mTRu6kPv1LtPGcMwY4Tf7F8vuvCPSVGkb_R6ql9UXOA', 'Adidas Sport Bra', 0, 1, NULL),
(24, 24, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=800&auto=format&fit=crop', 'Nike Indy Bra', 0, 1, NULL),
(26, 26, 'https://cdn.shopvnb.com/uploads/san_pham/vot-cau-long-yonex-nanoflare-700-xanh-2.webp', 'Yonex Nanoflare 700', 0, 1, NULL),
(27, 27, 'https://cdn.shopvnb.com/uploads/san_pham/vot-cau-long-lining-windstorm-72-blk-1.webp', 'Lining Windstorm 72', 0, 1, NULL),
(28, 28, 'https://vn-live-01.slatic.net/p/a1c2a1f58b8f3fa51d06b44f3a63a986.jpg', 'DL Pro 500', 0, 1, NULL),
(29, 29, 'https://bissport.com/uploads/webps/product/2026/03/votcaulonglininghalbertec7000timchinhhangnoibattocdolinhhoatchongrungbenbitoan.webp', 'DL Fighter 200', 0, 1, NULL),
(30, 30, 'https://cdn.hstatic.net/products/1000362402/z7860280356451_be1588c0dc1bb59103dabf454e32729d_32fda64cb2944735aa726df6e6e3a4b2_master.jpg', 'Head Zephyr Pro', 0, 1, NULL),
(31, 31, 'https://product.hstatic.net/200000427585/product/cau_long_yonex_as_05__2__77680a4cba344fdf8fb4f8ba321b9b65_master.jpg', 'Yonex AS-05', 0, 1, NULL),
(32, 32, 'https://cdn.shopvnb.com/uploads/san_pham/ong-cau-long-yonex-as30-1.webp', 'Yonex AS-30', 0, 1, NULL),
(33, 33, 'https://media.tripmap.vn/marketplace/2026/02/cau-tien-bo-chuan-dep-ben-dung-cho-hoc-sinh-sinh-vien-choi-phong-trao-the-duc-the-thao-1-ong-12-qua-1772045754-xl4ia5-380x380.webp', 'DL Training Cầu', 0, 1, NULL),
(34, 34, 'https://m.media-amazon.com/images/I/51Il5euwB7S._SX679_.jpg', 'Lining A+90D', 0, 1, NULL),
(35, 35, 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?q=80&w=800&auto=format&fit=crop', 'DL Short Training Nam', 0, 1, NULL),
(36, 36, 'https://encrypted-tbn3.gstatic.com/shopping?q=tbn:ANd9GcStDJU86RhcPVxD9u9zvM24_k9CXWw4CIVL6F2ufYQ_-gKPUJQvjjvpsW_3a2PPE_svZDgejAwGo371ZgVH36F_CK6m4_y7LuPvgGVtQlFaYujv6m9wXv376TY', 'Nike Short Dri-FIT', 0, 1, NULL),
(37, 37, 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?q=80&w=800&auto=format&fit=crop', 'DL Active Short Nữ', 0, 1, NULL),
(38, 38, 'https://underarmour.com.vn/cdn/shop/files/1361586-090-1.jpg', 'UA Legging HeatGear', 0, 1, NULL),
(39, 39, 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=800&auto=format&fit=crop', 'Adidas Legging Optime', 0, 1, NULL),
(40, 40, 'https://images.unsplash.com/photo-1614632537190-23e4146777db?q=80&w=800&auto=format&fit=crop', 'Bóng đá Adidas Al Rihla', 0, 1, NULL),
(41, 41, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCRGtd15BlvXHC_DYDzvyGohFm2nB5mrEjQg&s', 'DL Bóng đá Classic 5', 0, 1, NULL),
(42, 42, 'https://htsport.vn/wp-content/uploads/2019/11/qua-bong-da-dong-luc-hoa-so-5-1.jpg', 'DL Bóng Futsal', 0, 1, NULL),
(43, 43, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0jgqCxi3Mzk580W9z-p5dilvAq_D6ZT1Ngw&s', 'Bóng Pickleball DL', 0, 1, NULL),
(44, 44, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFSy4c7qyOtB-K-NGHcII0qKrsFd4wyosggg&s', 'Tạ tay DL Vinyl', 0, 1, NULL),
(45, 45, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?q=80&w=800&auto=format&fit=crop', 'Mũ thể thao DL', 0, 1, NULL),
(46, 46, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', 'Balo DL Sport', 0, 1, NULL),
(47, 47, 'https://cdn.shopvnb.com/uploads/san_pham/vo-cau-long-yonex-75th-trang-1.webp', 'Tất Yonex', 0, 1, NULL),
(48, 48, 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?q=80&w=800&auto=format&fit=crop', 'Tất DL Sport', 0, 1, NULL),
(49, 49, 'https://images.unsplash.com/photo-1521369909029-2afed882baee?q=80&w=800&auto=format&fit=crop', 'Mũ nữ DL Lily', 0, 1, NULL),
(50, 50, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR5taz02Lz4bDFjnYj2hPSs-w61NLcnw0rP8A&s', 'Balo nữ Adidas', 0, 1, NULL),
(55, 56, 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=800&auto=format&fit=crop', 'sản phẩm test ẩn hiện', 0, 1, NULL),
(58, 25, 'https://cdn.shopvnb.com/uploads/gallery/vot-cau-long-yonex-astrox-99-pro-trang-ma-jp_1695687382.webp', 'vot-cau-long-yonex-astrox-99-pro-do-khong-bao-hanh-ma-sp_1727208715.webp', 1, 0, 'Do (4U)'),
(59, 25, 'https://cdn.shopvnb.com/uploads/gallery/vot-cau-long-yonex-astrox-99-pro-trang-ma-jp_1695687382.webp', 'vot-cau-long-yonex-astrox-99-pro-trang-ma-jp-3_1695687409.webp', 2, 1, 'Trang (4U)'),
(60, 58, 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(61, 59, 'https://cdn.vuahanghieu.com/unsafe/0x500/left/top/smart/filters:quality(90)/https://s3.vuahanghieu.com/upload/1779348649183/cloud-image-1779348649183-giay-pickleball-tennis-nu-wilson-women-s-intrigue-pro-omni-tennis-shoes-wrs340120u-mau-trang-xanh--12-.jpg', NULL, 0, 1, NULL),
(62, 60, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(63, 61, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(64, 62, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(65, 63, 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(66, 64, 'https://cdn.vuahanghieu.com/unsafe/0x500/left/top/smart/filters:quality(90)/https://s3.vuahanghieu.com/upload/1779347451365/cloud-image-1779347451365-giay-pickleball-tennis-wilson-rush-pro-ace-dv-wrs335360-mau-xanh-blue--2-.jpg', NULL, 0, 1, NULL),
(67, 65, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(68, 66, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(69, 67, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(70, 68, 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(71, 69, 'https://bizweb.dktcdn.net/100/419/791/files/giay-bong-da-jogarbola-colorlux-9019-2.jpg?v=1621043495375', NULL, 0, 1, NULL),
(72, 70, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(73, 71, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(74, 72, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(75, 73, 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(76, 74, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNc5sAQlauyhN24JsN3OIzYr814Lg6PkPrDw&s', NULL, 0, 1, NULL),
(77, 75, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(78, 76, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(79, 77, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(80, 78, 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(81, 79, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZTGpyP2WnsnK5rq1Ul4JCL1wiMv9k66G1RQ&s', NULL, 0, 1, NULL),
(82, 80, 'https://images.unsplash.com/photo-1595341888016-a392ef81b7de?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(83, 81, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(84, 82, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(85, 83, 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(86, 84, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(87, 85, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(88, 86, 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(89, 87, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(90, 88, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(91, 89, 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(92, 90, 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(93, 91, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(94, 92, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=800&auto=format&fit=crop', NULL, 0, 1, NULL),
(95, 93, 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(96, 94, 'https://images.unsplash.com/photo-1560769629-975ec94e6a86?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(97, 95, 'https://images.unsplash.com/photo-1521369909029-2afed882baee?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(98, 96, 'https://product.hstatic.net/200000427585/product/cau_long_yonex_as_05__2__77680a4cba344fdf8fb4f8ba321b9b65_master.jpg', NULL, 1, 1, NULL),
(99, 97, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(100, 98, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(101, 99, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(102, 100, 'https://cdn.hstatic.net/products/1000061481/anh_sp_add_web_1-01-02-02-_2-2-2-2_3e4939d7306744d78811f350981dea04_large.jpg', NULL, 1, 1, NULL),
(103, 101, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR5taz02Lz4bDFjnYj2hPSs-w61NLcnw0rP8A&s', NULL, 1, 1, NULL),
(104, 102, 'https://vn-live-01.slatic.net/p/a1c2a1f58b8f3fa51d06b44f3a63a986.jpg', NULL, 1, 1, NULL),
(105, 103, 'https://cdn.hstatic.net/products/1000362402/z7860280356451_be1588c0dc1bb59103dabf454e32729d_32fda64cb2944735aa726df6e6e3a4b2_master.jpg', NULL, 1, 1, NULL),
(106, 104, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(107, 105, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(108, 106, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(109, 107, 'https://cdn.shopvnb.com/uploads/san_pham/vo-cau-long-yonex-75th-trang-1.webp', NULL, 1, 1, NULL),
(110, 108, 'https://cdn.shopvnb.com/uploads/san_pham/vo-cau-long-yonex-75th-trang-1.webp', NULL, 1, 1, NULL),
(111, 109, 'https://sumstore.vn/wp-content/uploads/2024/10/Lefus-Z001-FG-den.jpg', NULL, 1, 1, NULL),
(112, 110, 'https://cdn.shopvnb.com/uploads/san_pham/ong-cau-long-yonex-as30-1.webp', NULL, 1, 1, NULL),
(113, 111, 'https://cdn.shopvnb.com/uploads/san_pham/vot-cau-long-lining-windstorm-72-blk-1.webp', NULL, 1, 1, NULL),
(114, 112, 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(115, 113, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(116, 114, 'https://images.unsplash.com/photo-1521369909029-2afed882baee?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(117, 115, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR5taz02Lz4bDFjnYj2hPSs-w61NLcnw0rP8A&s', NULL, 1, 1, NULL),
(118, 116, 'https://bissport.com/uploads/webps/product/2026/03/votcaulonglininghalbertec7000timchinhhangnoibattocdolinhhoatchongrungbenbitoan.webp', NULL, 1, 1, NULL),
(119, 117, 'https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(120, 118, 'https://cdn.shopvnb.com/img/300x300/uploads/san_pham/giay-cau-long-yonex-strider-flow_1733432842.webp', NULL, 1, 1, NULL),
(121, 119, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(122, 120, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(123, 121, 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?q=80&w=800&auto=format&fit=crop', NULL, 1, 1, NULL),
(124, 122, 'https://cdn.shopvnb.com/uploads/san_pham/vo-cau-long-yonex-75th-trang-1.webp', NULL, 1, 1, NULL),
(125, 123, 'https://placehold.co/400x400/800000/ffffff?text=Gi%C3%A0y+B%C3%B3ng+R%E1%BB%95+Signatu...', NULL, 1, 1, NULL),
(126, 124, 'https://placehold.co/400x400/483d8b/ffffff?text=Gi%C3%A0y+B%C3%B3ng+R%E1%BB%95+Signatu...', NULL, 1, 1, NULL),
(127, 125, 'https://placehold.co/400x400/2e8b57/ffffff?text=Gi%C3%A0y+Th%E1%BB%83+Thao+Nam+Si...', NULL, 1, 1, NULL),
(128, 126, 'https://placehold.co/400x400/8b4513/ffffff?text=Gi%C3%A0y+Th%E1%BB%83+Thao+Nam+Si...', NULL, 1, 1, NULL),
(129, 127, 'https://placehold.co/400x400/4b0082/ffffff?text=D%C3%A9p+Nam+Signature+85...', NULL, 1, 1, NULL),
(130, 128, 'https://placehold.co/400x400/2f4f4f/ffffff?text=D%C3%A9p+Nam+Signature+C8...', NULL, 1, 1, NULL),
(131, 129, 'https://placehold.co/400x400/1a1a1a/ffffff?text=%C3%81o+Kho%C3%A1c+Nam+Signatu...', NULL, 1, 1, NULL),
(132, 130, 'https://placehold.co/400x400/4b0082/ffffff?text=%C3%81o+Kho%C3%A1c+Nam+Signatu...', NULL, 1, 1, NULL),
(133, 131, 'https://placehold.co/400x400/8b4513/ffffff?text=Qu%E1%BA%A7n+D%C3%A0i+Nam+Signatu...', NULL, 1, 1, NULL),
(134, 132, 'https://placehold.co/400x400/2e8b57/ffffff?text=Qu%E1%BA%A7n+D%C3%A0i+Nam+Signatu...', NULL, 1, 1, NULL),
(135, 133, 'https://placehold.co/400x400/2f4f4f/ffffff?text=Gi%C3%A0y+Ch%E1%BA%A1y+B%E1%BB%99+N%E1%BB%AF+Sign...', NULL, 1, 1, NULL),
(136, 134, 'https://placehold.co/400x400/2e8b57/ffffff?text=Gi%C3%A0y+Ch%E1%BA%A1y+B%E1%BB%99+N%E1%BB%AF+Sign...', NULL, 1, 1, NULL),
(137, 135, 'https://placehold.co/400x400/003366/ffffff?text=Gi%C3%A0y+Th%E1%BB%83+Thao+N%E1%BB%AF+Sig...', NULL, 1, 1, NULL),
(138, 136, 'https://placehold.co/400x400/800000/ffffff?text=Gi%C3%A0y+Th%E1%BB%83+Thao+N%E1%BB%AF+Sig...', NULL, 1, 1, NULL),
(139, 137, 'https://placehold.co/400x400/1a1a1a/ffffff?text=D%C3%A9p+N%E1%BB%AF+Signature+724...', NULL, 1, 1, NULL),
(140, 138, 'https://placehold.co/400x400/2e8b57/ffffff?text=D%C3%A9p+N%E1%BB%AF+Signature+3EB...', NULL, 1, 1, NULL),
(141, 139, 'https://placehold.co/400x400/4b0082/ffffff?text=Ch%C3%A2n+V%C3%A1y+Signature+5...', NULL, 1, 1, NULL),
(142, 140, 'https://placehold.co/400x400/8b4513/ffffff?text=Ch%C3%A2n+V%C3%A1y+Signature+8...', NULL, 1, 1, NULL),
(143, 141, 'https://placehold.co/400x400/2f4f4f/ffffff?text=B%C3%B3ng+R%E1%BB%95+6FC2', NULL, 1, 1, NULL),
(144, 142, 'https://placehold.co/400x400/4b0082/ffffff?text=B%C3%B3ng+R%E1%BB%95+237A', NULL, 1, 1, NULL),
(145, 143, 'https://placehold.co/400x400/006400/ffffff?text=B%C3%B3ng+R%E1%BB%95+4F74', NULL, 1, 1, NULL),
(146, 144, 'https://placehold.co/400x400/006400/ffffff?text=B%C3%B3ng+R%E1%BB%95+003E', NULL, 1, 1, NULL),
(147, 145, 'https://placehold.co/400x400/2e8b57/ffffff?text=B%C3%B3ng+R%E1%BB%95+74E2', NULL, 1, 1, NULL),
(148, 146, 'https://placehold.co/400x400/4682b4/ffffff?text=B%C3%B3ng+Chuy%E1%BB%81n+12DA', NULL, 1, 1, NULL),
(149, 147, 'https://placehold.co/400x400/006400/ffffff?text=B%C3%B3ng+Chuy%E1%BB%81n+83FA', NULL, 1, 1, NULL),
(150, 148, 'https://placehold.co/400x400/2e8b57/ffffff?text=B%C3%B3ng+Chuy%E1%BB%81n+6607', NULL, 1, 1, NULL),
(151, 149, 'https://placehold.co/400x400/483d8b/ffffff?text=B%C3%B3ng+Chuy%E1%BB%81n+D1F0', NULL, 1, 1, NULL),
(152, 150, 'https://placehold.co/400x400/800000/ffffff?text=V%E1%BB%A3t+Pickleball+784A', NULL, 1, 1, NULL),
(153, 151, 'https://placehold.co/400x400/4682b4/ffffff?text=V%E1%BB%A3t+Pickleball+0CC4', NULL, 1, 1, NULL),
(154, 152, 'https://placehold.co/400x400/2f4f4f/ffffff?text=V%E1%BB%A3t+Pickleball+345C', NULL, 1, 1, NULL),
(155, 153, 'https://placehold.co/400x400/8b4513/ffffff?text=V%E1%BB%A3t+Pickleball+38FF', NULL, 1, 1, NULL),
(156, 154, 'https://placehold.co/400x400/8b4513/ffffff?text=V%E1%BB%A3t+Pickleball+13D4', NULL, 1, 1, NULL),
(157, 155, 'https://placehold.co/400x400/8b4513/ffffff?text=B%C3%B3ng+Pickleball+21CE', NULL, 1, 1, NULL),
(158, 156, 'https://placehold.co/400x400/4b0082/ffffff?text=B%C3%B3ng+Pickleball+3A12', NULL, 1, 1, NULL),
(159, 157, 'https://placehold.co/400x400/4b0082/ffffff?text=B%C3%B3ng+Pickleball+11C4', NULL, 1, 1, NULL),
(160, 158, 'https://placehold.co/400x400/003366/ffffff?text=B%C3%B3ng+Pickleball+BEAE', NULL, 1, 1, NULL),
(161, 159, 'https://placehold.co/400x400/003366/ffffff?text=M%C3%A1y+Ch%E1%BA%A1y+B%E1%BB%99+1719', NULL, 1, 1, NULL),
(162, 160, 'https://placehold.co/400x400/1a1a1a/ffffff?text=M%C3%A1y+Ch%E1%BA%A1y+B%E1%BB%99+D499', NULL, 1, 1, NULL),
(163, 161, 'https://placehold.co/400x400/2e8b57/ffffff?text=M%C3%A1y+Ch%E1%BA%A1y+B%E1%BB%99+F865', NULL, 1, 1, NULL),
(164, 162, 'https://placehold.co/400x400/2e8b57/ffffff?text=M%C3%A1y+Ch%E1%BA%A1y+B%E1%BB%99+2C9F', NULL, 1, 1, NULL),
(165, 163, 'https://placehold.co/400x400/2f4f4f/ffffff?text=M%C3%A1y+Ch%E1%BA%A1y+B%E1%BB%99+74F4', NULL, 1, 1, NULL),
(166, 164, 'https://placehold.co/400x400/d2691e/ffffff?text=Gi%C3%A0n+T%E1%BA%ADp+6E3A', NULL, 1, 1, NULL),
(167, 165, 'https://placehold.co/400x400/800000/ffffff?text=Gi%C3%A0n+T%E1%BA%ADp+AF69', NULL, 1, 1, NULL),
(168, 166, 'https://placehold.co/400x400/2e8b57/ffffff?text=Gi%C3%A0n+T%E1%BA%ADp+2798', NULL, 1, 1, NULL),
(169, 167, 'https://placehold.co/400x400/8b4513/ffffff?text=Gi%C3%A0n+T%E1%BA%ADp+BBDF', NULL, 1, 1, NULL),
(170, 168, 'https://placehold.co/400x400/2e8b57/ffffff?text=Gi%C3%A0n+T%E1%BA%ADp+2AD4', NULL, 1, 1, NULL),
(171, 169, 'https://placehold.co/600x600/111827/ffffff?text=San+pham+test+QA+liv', 'San pham test QA live', 0, 1, NULL),
(175, 174, 'https://placehold.co/400x400/E63946/FFFFFF?text=BANKING+Test+3K%0A3%2C000+VND', NULL, 0, 1, NULL),
(176, 175, 'https://placehold.co/400x400/2A9D8F/FFFFFF?text=BANKING+Test+5K%0A5%2C000+VND', NULL, 0, 1, NULL),
(177, 176, 'https://placehold.co/400x400/E9C46A/000000?text=BANKING+Test+10K%0A10%2C000+VND', NULL, 0, 1, NULL);

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

INSERT INTO `product_variants` (`variant_id`, `product_id`, `sku`, `color`, `size`, `extra_price`, `stock_quantity`, `is_active`, `is_deleted`) VALUES
(1, 1, 'DL-SPX1-DEN-39', 'Đen', '39', 0.00, 8, 1, 0),
(2, 1, 'DL-SPX1-DEN-40', 'Đen', '40', 0.00, 10, 1, 0),
(3, 1, 'DL-SPX1-DEN-41', 'Đen', '41', 0.00, 10, 1, 0),
(4, 1, 'DL-SPX1-DEN-42', 'Đen', '42', 0.00, 8, 1, 0),
(5, 1, 'DL-SPX1-DEN-43', 'Đen', '43', 0.00, 7, 1, 0),
(6, 1, 'DL-SPX1-TRANG-40', 'Trắng', '40', 0.00, 5, 1, 0),
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
(17, 3, 'NK-REV7-XANH-40', 'Xanh đường', '40', 50000.00, 2, 1, 0),
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
(29, 5, 'AS-GN26-TRANG-40', 'Trắng', '40', 100000.00, 2, 1, 0),
(30, 5, 'AS-GN26-TRANG-41', 'Trắng', '41', 100000.00, 2, 1, 0),
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
(84, 14, 'YNX-01MX-TRANG-36', 'Trắng', '36', 0.00, 2, 1, 0),
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
(95, 16, 'DL-POLO-TRANG-L', 'Trắng', 'L', 0.00, 18, 1, 0),
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
(229, 46, 'DL-BALO-TRANG', 'Trắng', '20L', 0.00, 9, 1, 0),
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
(242, 50, 'AD-BN-HONG', 'Hồng', '22L', 0.00, 10, 1, 0),
(246, 56, '21', 'Xanh lá', '21', 0.00, 12, 1, 0),
(247, 141, 'DEFAULT-141-1781113277', 'default', 'default', 0.00, 91, 1, 0),
(248, 144, 'DEFAULT-144-1781113313', 'default', 'default', 0.00, 21, 1, 0),
(249, 165, 'DEFAULT-165-1781113453', 'default', 'default', 0.00, 10, 1, 0),
(250, 169, 'SP-TEST-01', 'Xanh', 'XL', 0.00, 10, 1, 0),
(254, 174, 'TEST-84B78D', 'Mặc định', 'Free Size', 0.00, 995, 1, 0),
(255, 175, 'TEST-F66E00', 'Mặc định', 'Free Size', 0.00, 999, 1, 0),
(256, 176, 'TEST-B404DD', 'Mặc định', 'Free Size', 0.00, 995, 1, 0),
(257, 63, 'DEFAULT-63-1781233659', 'default', 'default', 0.00, 67, 1, 0);

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

INSERT INTO `product_view_logs` (`view_id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 12, 1, '2026-05-29 19:17:55'),
(2, 12, 3, '2026-05-29 19:17:55'),
(3, 12, 5, '2026-05-29 19:17:55'),
(4, 12, 11, '2026-05-29 19:17:55'),
(5, 12, 25, '2026-05-29 19:17:55'),
(6, 12, 26, '2026-05-29 19:17:55'),
(7, 12, 31, '2026-05-29 19:17:55'),
(8, 12, 32, '2026-05-29 19:17:55'),
(9, 8, 25, '2026-05-29 19:17:55'),
(10, 8, 26, '2026-05-29 19:17:55'),
(11, 8, 14, '2026-05-29 19:17:55'),
(12, 8, 38, '2026-05-29 19:17:55'),
(13, 8, 23, '2026-05-29 19:17:55'),
(14, 8, 19, '2026-05-29 19:17:55'),
(15, 8, 16, '2026-05-29 19:17:55'),
(16, 9, 3, '2026-05-29 19:17:55'),
(17, 9, 4, '2026-05-29 19:17:55'),
(18, 9, 5, '2026-05-29 19:17:55'),
(19, 9, 25, '2026-05-29 19:17:55'),
(20, 9, 27, '2026-05-29 19:17:55'),
(21, 9, 31, '2026-05-29 19:17:55'),
(22, 9, 32, '2026-05-29 19:17:55'),
(23, 9, 33, '2026-05-29 19:17:55'),
(24, 10, 40, '2026-05-29 19:17:55'),
(25, 10, 41, '2026-05-29 19:17:55'),
(26, 10, 6, '2026-05-29 19:17:55'),
(27, 10, 8, '2026-05-29 19:17:55'),
(28, 10, 9, '2026-05-29 19:17:55'),
(29, 10, 45, '2026-05-29 19:17:55'),
(30, 10, 46, '2026-05-29 19:17:55'),
(31, 11, 6, '2026-05-29 19:17:55'),
(32, 11, 7, '2026-05-29 19:17:55'),
(33, 11, 10, '2026-05-29 19:17:55'),
(34, 11, 40, '2026-05-29 19:17:55'),
(35, 11, 44, '2026-05-29 19:17:55'),
(36, 11, 35, '2026-05-29 19:17:55'),
(37, 11, 36, '2026-05-29 19:17:55'),
(38, 13, 38, '2026-05-29 19:17:55'),
(39, 13, 39, '2026-05-29 19:17:55'),
(40, 13, 21, '2026-05-29 19:17:55'),
(41, 13, 22, '2026-05-29 19:17:55'),
(42, 13, 23, '2026-05-29 19:17:55'),
(43, 13, 24, '2026-05-29 19:17:55'),
(44, 13, 37, '2026-05-29 19:17:55'),
(45, 13, 50, '2026-05-29 19:17:55'),
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
(77, 1, 31, '2026-06-08 19:22:39'),
(195, 12, 56, '2026-06-11 00:17:48'),
(196, 12, 56, '2026-06-11 00:19:08'),
(197, 12, 141, '2026-06-11 00:41:15'),
(198, 12, 144, '2026-06-11 00:41:46'),
(199, 12, 144, '2026-06-11 00:42:29'),
(200, 12, 165, '2026-06-11 00:44:09'),
(201, 12, 165, '2026-06-11 00:47:09'),
(202, 3, 1, '2026-06-11 01:10:09'),
(203, 2, 1, '2026-06-11 01:24:21'),
(204, 2, 5, '2026-06-11 01:47:39'),
(205, 2, 16, '2026-06-11 01:47:43'),
(206, 2, 5, '2026-06-11 01:56:51'),
(207, 2, 1, '2026-06-11 02:05:28'),
(208, 2, 16, '2026-06-11 02:10:42'),
(209, 2, 165, '2026-06-11 02:20:01'),
(210, 1, 165, '2026-06-11 02:20:04'),
(211, 2, 165, '2026-06-11 02:21:18'),
(212, 2, 1, '2026-06-11 02:24:39'),
(213, 1, 165, '2026-06-11 02:25:53'),
(214, 1, 169, '2026-06-11 03:02:04'),
(215, 2, 3, '2026-06-11 22:03:24'),
(218, 2, 174, '2026-06-11 23:39:56'),
(219, 2, 174, '2026-06-11 23:40:27'),
(220, 2, 174, '2026-06-11 23:40:28'),
(221, 2, 174, '2026-06-11 23:40:34'),
(222, 2, 174, '2026-06-11 23:43:09'),
(223, 2, 174, '2026-06-11 23:44:48'),
(224, 2, 174, '2026-06-11 23:44:51'),
(225, 2, 174, '2026-06-11 23:47:10'),
(226, 2, 174, '2026-06-11 23:47:20'),
(227, 2, 174, '2026-06-11 23:47:25'),
(228, 2, 174, '2026-06-11 23:52:42'),
(229, 2, 1, '2026-06-12 00:03:30'),
(230, 2, 1, '2026-06-12 00:05:12'),
(231, 2, 1, '2026-06-12 00:05:26'),
(232, 2, 1, '2026-06-12 00:05:33'),
(233, 2, 1, '2026-06-12 00:05:41'),
(234, 2, 11, '2026-06-12 00:05:46'),
(235, 2, 3, '2026-06-12 00:05:46'),
(236, 2, 11, '2026-06-12 00:06:32'),
(237, 2, 3, '2026-06-12 00:07:01'),
(238, 2, 11, '2026-06-12 00:07:04'),
(239, 2, 8, '2026-06-12 00:07:16'),
(240, 2, 6, '2026-06-12 00:07:17'),
(241, 2, 6, '2026-06-12 00:08:17'),
(242, 2, 25, '2026-06-12 00:09:28'),
(243, 2, 27, '2026-06-12 00:09:31'),
(244, 2, 28, '2026-06-12 00:09:33'),
(245, 2, 27, '2026-06-12 00:09:34'),
(246, 2, 29, '2026-06-12 00:09:51'),
(247, 2, 30, '2026-06-12 00:09:55'),
(248, 2, 16, '2026-06-12 10:07:22'),
(249, 2, 11, '2026-06-12 10:07:29'),
(250, 2, 16, '2026-06-12 10:07:31'),
(251, 2, 63, '2026-06-12 10:07:37'),
(252, 2, 25, '2026-06-12 10:28:34'),
(253, 2, 174, '2026-06-12 10:28:44'),
(254, 2, 174, '2026-06-12 10:31:09'),
(255, 2, 6, '2026-06-12 10:39:02'),
(256, 2, 6, '2026-06-12 10:39:52'),
(257, 2, 6, '2026-06-12 10:43:43'),
(258, 2, 6, '2026-06-12 10:44:18'),
(259, 2, 174, '2026-06-12 11:06:43'),
(260, 2, 56, '2026-06-12 11:47:28'),
(261, 2, 56, '2026-06-12 11:50:25'),
(262, 2, 174, '2026-06-12 21:43:10'),
(263, 2, 176, '2026-06-12 21:51:39'),
(264, 2, 176, '2026-06-12 21:53:04'),
(265, 2, 176, '2026-06-12 21:53:28'),
(266, 2, 176, '2026-06-12 21:58:08'),
(267, 2, 176, '2026-06-12 22:10:41'),
(268, 2, 176, '2026-06-12 22:23:09'),
(269, 2, 174, '2026-06-12 22:33:46'),
(271, 11, 34, '2026-01-03 14:25:33'),
(272, 11, 23, '2026-01-02 18:31:14'),
(273, 11, 33, '2026-01-02 18:01:00'),
(274, NULL, 5, '2026-01-14 12:16:01'),
(275, 13, 25, '2026-01-10 10:25:17'),
(276, NULL, 47, '2026-01-09 20:25:54'),
(277, NULL, 2, '2026-01-12 20:03:13'),
(278, NULL, 18, '2026-01-11 08:40:42'),
(279, 10, 31, '2026-01-05 20:55:18'),
(280, 10, 16, '2026-01-02 22:26:59'),
(281, NULL, 45, '2026-01-01 20:48:37'),
(282, NULL, 33, '2026-01-02 10:23:43'),
(283, 10, 8, '2026-01-03 08:20:25'),
(284, 10, 17, '2026-01-05 13:16:18'),
(285, NULL, 33, '2026-01-03 10:35:18'),
(286, 10, 15, '2026-01-04 19:56:14'),
(287, NULL, 42, '2026-01-04 11:07:13'),
(288, NULL, 22, '2026-01-04 13:21:17'),
(289, 9, 46, '2026-01-10 17:50:14'),
(290, NULL, 32, '2026-01-14 20:20:06'),
(291, 9, 36, '2026-01-07 10:07:39'),
(292, NULL, 42, '2026-01-25 23:17:08'),
(293, 9, 26, '2026-01-05 19:25:42'),
(294, NULL, 17, '2026-01-19 18:32:55'),
(295, 11, 47, '2026-02-06 11:44:00'),
(296, 11, 16, '2026-02-05 09:27:49'),
(297, 11, 10, '2026-02-06 09:34:26'),
(298, NULL, 18, '2026-02-02 23:52:30'),
(299, NULL, 45, '2026-02-02 21:58:19'),
(300, 8, 38, '2026-02-02 14:02:47'),
(301, 8, 41, '2026-02-01 10:38:09'),
(302, NULL, 23, '2026-02-01 16:48:37'),
(303, 10, 22, '2026-02-03 13:14:01'),
(304, 10, 2, '2026-02-03 10:31:59'),
(305, NULL, 15, '2026-02-01 17:09:39'),
(306, 10, 32, '2026-02-03 08:43:23'),
(307, NULL, 4, '2026-02-02 11:55:04'),
(308, 10, 14, '2026-02-03 12:46:38'),
(309, NULL, 1, '2026-02-03 10:00:16'),
(310, NULL, 11, '2026-02-02 10:31:41'),
(311, NULL, 15, '2026-02-03 20:19:50'),
(312, NULL, 16, '2026-02-03 15:28:19'),
(313, 9, 43, '2026-02-05 20:12:53'),
(314, NULL, 22, '2026-02-04 14:00:09'),
(315, NULL, 13, '2026-02-05 13:13:59'),
(316, 9, 13, '2026-02-04 23:23:10'),
(317, NULL, 48, '2026-02-02 23:26:13'),
(318, 9, 30, '2026-02-05 19:21:17'),
(319, 9, 8, '2026-02-03 18:55:45'),
(320, NULL, 25, '2026-02-01 21:05:23'),
(321, NULL, 37, '2026-02-01 20:34:21'),
(322, NULL, 22, '2026-02-05 10:02:54'),
(323, 8, 33, '2026-02-02 21:31:15'),
(324, NULL, 32, '2026-02-04 16:06:53'),
(325, NULL, 5, '2026-02-06 15:33:10'),
(326, 8, 3, '2026-02-01 16:39:10'),
(327, 8, 47, '2026-02-01 22:30:25'),
(328, 8, 39, '2026-02-01 11:40:36'),
(329, NULL, 47, '2026-02-01 18:38:46'),
(330, 8, 48, '2026-02-01 16:12:15'),
(331, 8, 46, '2026-02-01 21:21:36'),
(332, 8, 30, '2026-02-01 16:02:40'),
(333, NULL, 47, '2026-02-01 17:01:27'),
(334, NULL, 13, '2026-02-01 08:51:32'),
(335, 11, 48, '2026-02-06 22:40:45'),
(336, 11, 12, '2026-02-04 11:32:43'),
(337, NULL, 8, '2026-02-04 19:37:10'),
(338, NULL, 21, '2026-02-03 18:15:21'),
(339, NULL, 15, '2026-02-02 10:10:11'),
(340, 11, 39, '2026-02-05 17:33:32'),
(341, NULL, 2, '2026-02-01 18:17:48'),
(342, 11, 36, '2026-02-04 19:19:27'),
(343, 13, 5, '2026-02-02 12:27:04'),
(344, NULL, 37, '2026-02-03 11:49:40'),
(345, 13, 48, '2026-02-03 21:16:12'),
(346, 13, 49, '2026-02-01 15:35:25'),
(347, NULL, 44, '2026-02-01 10:09:23'),
(348, NULL, 3, '2026-02-02 19:20:26'),
(349, 13, 29, '2026-02-01 12:25:52'),
(350, 13, 45, '2026-02-01 17:17:07'),
(351, 13, 24, '2026-02-03 15:44:54'),
(352, 11, 22, '2026-03-02 20:06:01'),
(353, 11, 11, '2026-03-01 08:48:51'),
(354, NULL, 25, '2026-03-02 17:02:20'),
(355, NULL, 31, '2026-03-06 15:20:45'),
(356, NULL, 46, '2026-03-08 21:52:37'),
(357, 11, 39, '2026-03-06 17:38:01'),
(358, 10, 48, '2026-03-15 08:56:39'),
(359, 10, 47, '2026-03-13 14:28:04'),
(360, NULL, 44, '2026-03-11 15:42:46'),
(361, NULL, 23, '2026-03-08 21:01:21'),
(362, NULL, 47, '2026-03-04 21:31:02'),
(363, NULL, 12, '2026-03-05 19:07:34'),
(364, 13, 31, '2026-03-07 17:45:15'),
(365, NULL, 30, '2026-03-02 13:54:09'),
(366, NULL, 41, '2026-03-17 21:22:17'),
(367, NULL, 5, '2026-03-16 12:28:11'),
(368, 12, 18, '2026-03-16 20:12:54'),
(369, 12, 41, '2026-03-18 10:10:16'),
(370, 12, 44, '2026-03-13 20:04:31'),
(371, NULL, 3, '2026-03-01 19:05:40'),
(372, NULL, 17, '2026-03-07 22:53:09'),
(373, NULL, 1, '2026-03-09 20:18:37'),
(374, NULL, 34, '2026-03-02 10:47:50'),
(375, NULL, 45, '2026-03-12 23:51:50'),
(376, 12, 43, '2026-03-14 14:44:57'),
(377, NULL, 22, '2026-03-12 21:23:48'),
(378, 8, 44, '2026-03-15 11:58:13'),
(379, 8, 9, '2026-03-08 18:44:31'),
(380, 8, 12, '2026-03-08 18:56:00'),
(381, 8, 35, '2026-03-18 23:06:59'),
(382, 8, 20, '2026-03-03 08:35:48'),
(383, 8, 27, '2026-03-18 20:44:54'),
(384, NULL, 48, '2026-03-19 14:04:33'),
(385, NULL, 15, '2026-04-16 08:10:53'),
(386, 11, 32, '2026-04-19 11:57:28'),
(387, NULL, 32, '2026-04-14 08:05:03'),
(388, NULL, 31, '2026-04-02 09:33:41'),
(389, 9, 42, '2026-04-05 12:48:57'),
(390, NULL, 34, '2026-04-14 22:46:14'),
(391, 9, 31, '2026-04-07 16:18:39'),
(392, 9, 26, '2026-04-08 08:55:19'),
(393, 9, 28, '2026-04-02 20:34:48'),
(394, NULL, 3, '2026-04-06 09:54:13'),
(395, NULL, 37, '2026-04-07 22:49:54'),
(396, NULL, 2, '2026-04-11 21:48:07'),
(397, 9, 5, '2026-04-05 23:43:01'),
(398, 8, 16, '2026-04-05 17:50:45'),
(399, NULL, 26, '2026-04-05 15:21:31'),
(400, NULL, 41, '2026-04-04 10:49:51'),
(401, NULL, 30, '2026-04-07 14:10:17'),
(402, 13, 32, '2026-04-19 17:32:56'),
(403, 13, 13, '2026-04-18 16:39:58'),
(404, 13, 45, '2026-04-03 21:22:36'),
(405, NULL, 27, '2026-04-02 21:49:41'),
(406, NULL, 27, '2026-04-18 21:29:27'),
(407, NULL, 42, '2026-04-05 17:38:36'),
(408, 9, 34, '2026-04-06 12:31:02'),
(409, NULL, 21, '2026-04-05 13:50:06'),
(410, 9, 11, '2026-04-05 09:13:52'),
(411, 9, 23, '2026-04-05 12:24:35'),
(412, 9, 11, '2026-04-20 17:00:28'),
(413, 1, 25, '2026-06-13 06:12:24'),
(414, 19, 19, '2025-01-18 14:52:45'),
(415, 20, 38, '2025-09-14 10:07:28'),
(416, 21, 2, '2025-08-29 17:25:16'),
(417, 23, 31, '2026-06-10 06:27:17'),
(418, 21, 34, '2026-04-26 08:27:33'),
(419, 15, 49, '2025-07-21 20:03:33'),
(420, 23, 26, '2025-01-06 10:53:42'),
(421, 19, 23, '2025-09-04 08:32:41'),
(422, 19, 7, '2025-07-23 14:43:13'),
(423, 19, 9, '2025-04-01 04:08:15'),
(424, 18, 12, '2025-09-15 13:59:22'),
(425, 18, 176, '2025-07-25 08:12:38'),
(426, 20, 11, '2025-06-26 22:37:36'),
(427, 14, 13, '2025-02-27 10:24:51'),
(428, 19, 27, '2026-04-27 23:42:05'),
(429, 23, 13, '2025-08-21 09:03:52'),
(430, 14, 34, '2025-03-21 19:35:02'),
(431, 15, 20, '2025-06-29 16:36:23'),
(432, 18, 30, '2025-05-08 17:34:55'),
(433, 16, 174, '2025-11-13 07:26:47'),
(434, 22, 12, '2025-06-17 07:14:05'),
(435, 23, 15, '2025-03-01 23:24:38'),
(436, 15, 2, '2026-05-29 22:29:44'),
(437, 22, 16, '2026-05-22 07:41:24'),
(438, 17, 35, '2025-09-16 23:47:42'),
(439, 17, 22, '2025-03-12 16:23:07'),
(440, 17, 18, '2025-08-17 14:03:30'),
(441, 16, 12, '2026-05-30 09:24:51'),
(442, 22, 12, '2025-10-01 14:00:00'),
(443, 14, 12, '2026-01-14 07:24:52'),
(444, 17, 15, '2025-09-05 02:26:40'),
(445, 20, 18, '2026-03-18 22:04:54'),
(446, 22, 22, '2026-03-04 05:34:19'),
(447, 14, 11, '2025-05-18 20:46:42'),
(448, 23, 33, '2025-10-26 19:37:53'),
(449, 17, 169, '2026-05-25 05:31:37'),
(450, 14, 35, '2025-07-25 01:02:26'),
(451, 18, 19, '2025-08-01 21:52:25'),
(452, 16, 2, '2025-04-01 23:05:47'),
(453, 14, 10, '2026-05-20 19:37:35'),
(454, 19, 40, '2026-03-23 05:23:48'),
(455, 14, 6, '2025-08-01 20:46:33'),
(456, 19, 45, '2025-03-02 10:18:03'),
(457, 16, 33, '2025-01-31 02:56:02'),
(458, 18, 36, '2026-03-23 03:43:47'),
(459, 22, 2, '2025-03-15 23:20:32'),
(460, 17, 35, '2025-05-02 16:50:25'),
(461, 20, 22, '2025-07-29 09:58:55'),
(462, 23, 2, '2026-05-23 01:46:33'),
(463, 22, 28, '2026-03-20 19:00:21'),
(464, 14, 6, '2025-05-12 08:36:35'),
(465, 23, 17, '2026-03-26 18:36:42'),
(466, 23, 45, '2025-07-11 05:31:08'),
(467, 18, 35, '2026-04-25 09:46:39'),
(468, 19, 2, '2026-02-12 08:12:45'),
(469, 15, 29, '2026-05-21 00:02:06'),
(470, 17, 38, '2025-06-28 05:16:52'),
(471, 20, 17, '2026-04-19 22:22:19'),
(472, 17, 36, '2025-12-20 23:57:42'),
(473, 23, 9, '2025-02-23 02:18:17'),
(474, 21, 39, '2026-04-11 04:47:49'),
(475, 14, 44, '2025-08-22 14:57:52'),
(476, 23, 4, '2025-10-06 17:03:50'),
(477, 18, 14, '2026-02-17 21:00:29'),
(478, 19, 21, '2025-11-10 09:35:54'),
(479, 22, 50, '2025-09-13 21:10:14'),
(480, 15, 8, '2025-02-21 10:23:58'),
(481, 18, 10, '2025-01-13 08:03:14'),
(482, 19, 24, '2025-08-12 12:03:43'),
(483, 17, 40, '2025-07-02 11:19:41'),
(484, 19, 35, '2026-01-15 17:02:52'),
(485, 14, 16, '2026-05-07 09:44:06'),
(486, 20, 36, '2025-11-24 06:06:00'),
(487, 22, 15, '2026-05-06 00:43:39'),
(488, 16, 45, '2026-05-24 20:11:33'),
(489, 22, 24, '2025-11-17 19:53:25'),
(490, 15, 14, '2026-03-24 14:23:56'),
(491, 19, 6, '2025-12-30 18:45:27'),
(492, 21, 47, '2026-03-06 16:24:43'),
(493, 17, 23, '2026-06-03 06:24:07'),
(494, 14, 29, '2025-09-19 17:25:55'),
(495, 15, 35, '2025-01-08 12:50:38'),
(496, 15, 2, '2026-03-21 17:07:35'),
(497, 23, 3, '2026-06-12 12:52:14'),
(498, 16, 11, '2025-10-07 17:34:17'),
(499, 20, 49, '2025-12-15 21:32:57'),
(500, 17, 10, '2025-12-13 06:48:10'),
(501, 17, 21, '2025-05-25 19:05:44'),
(502, 17, 12, '2025-03-24 01:14:58'),
(503, 21, 20, '2025-08-14 10:06:14'),
(504, 23, 25, '2025-05-11 04:23:12'),
(505, 18, 37, '2025-04-25 19:51:01'),
(506, 23, 14, '2025-03-07 23:58:11'),
(507, 17, 31, '2025-05-18 17:34:17'),
(508, 17, 36, '2026-01-28 04:55:30'),
(509, 22, 15, '2025-01-08 12:04:06'),
(510, 14, 46, '2025-03-10 03:58:41'),
(511, 18, 20, '2025-08-05 13:21:56'),
(512, 22, 38, '2025-09-19 22:06:07'),
(513, 20, 21, '2025-07-20 23:50:53'),
(514, 15, 23, '2026-05-19 21:59:43'),
(515, 16, 20, '2025-07-15 10:59:21'),
(516, 19, 1, '2025-04-03 19:24:45'),
(517, 21, 8, '2025-11-28 11:29:31'),
(518, 22, 32, '2025-08-25 04:49:11'),
(519, 23, 174, '2026-03-28 00:10:13'),
(520, 14, 2, '2025-11-21 23:49:00'),
(521, 14, 3, '2025-02-17 07:27:21'),
(522, 23, 13, '2026-03-17 08:27:30'),
(523, 18, 15, '2025-07-31 18:38:00'),
(524, 20, 7, '2025-02-14 04:51:33'),
(525, 21, 39, '2025-03-30 15:32:02'),
(526, 17, 34, '2025-02-01 02:37:53'),
(527, 16, 21, '2025-06-07 22:24:23'),
(528, 21, 43, '2025-09-16 06:18:26'),
(529, 22, 27, '2025-05-11 14:20:03'),
(530, 14, 50, '2026-01-22 16:17:59'),
(531, 20, 45, '2025-09-16 18:22:49'),
(532, 20, 37, '2025-08-30 15:27:39'),
(533, 16, 9, '2025-05-12 17:31:16'),
(534, 17, 6, '2025-03-28 21:55:06'),
(535, 20, 34, '2026-06-02 09:01:05'),
(536, 17, 12, '2026-02-17 18:27:55'),
(537, 18, 20, '2025-10-16 03:08:08'),
(538, 18, 8, '2025-04-19 10:32:38'),
(539, 17, 37, '2025-02-17 08:03:22'),
(540, 22, 5, '2026-01-01 08:41:44'),
(541, 21, 28, '2025-09-08 19:45:15'),
(542, 15, 50, '2025-01-11 08:01:29'),
(543, 17, 11, '2026-03-25 22:00:37'),
(544, 17, 21, '2025-02-07 22:53:51'),
(545, 15, 2, '2025-09-17 16:10:52'),
(546, 19, 27, '2026-01-28 13:49:03'),
(547, 20, 38, '2026-04-19 21:43:15'),
(548, 18, 175, '2025-04-23 07:27:41'),
(549, 17, 11, '2026-03-07 15:25:08'),
(550, 19, 48, '2025-02-07 19:46:37'),
(551, 23, 16, '2025-02-27 22:46:54'),
(552, 21, 21, '2025-03-10 15:42:19'),
(553, 14, 16, '2025-07-17 16:00:13'),
(554, 23, 30, '2025-04-04 19:40:59'),
(555, 21, 21, '2025-06-05 04:36:51'),
(556, 14, 2, '2025-01-03 04:49:43'),
(557, 20, 6, '2025-10-26 15:30:17'),
(558, 22, 174, '2025-05-31 02:41:03'),
(559, 19, 44, '2025-06-29 21:43:52'),
(560, 14, 7, '2025-02-27 18:20:45'),
(561, 18, 63, '2025-05-29 18:45:59'),
(562, 15, 26, '2025-11-06 15:29:11'),
(563, 20, 50, '2025-09-13 02:09:03'),
(564, 18, 15, '2026-01-30 21:43:54'),
(565, 19, 11, '2025-08-24 14:47:09'),
(566, 17, 4, '2025-08-16 17:26:47'),
(567, 22, 22, '2026-02-09 02:48:41'),
(568, 22, 22, '2025-04-04 18:56:47'),
(569, 18, 15, '2026-01-27 18:59:04'),
(570, 21, 39, '2025-06-01 00:27:04'),
(571, 14, 38, '2026-06-04 16:05:08'),
(572, 21, 2, '2025-06-26 15:20:11'),
(573, 18, 7, '2025-06-10 23:38:06'),
(574, 17, 22, '2025-11-20 22:58:19'),
(575, 19, 46, '2025-11-21 19:07:17'),
(576, 15, 4, '2025-09-04 11:00:54'),
(577, 20, 144, '2025-11-08 12:51:19'),
(578, 17, 13, '2026-04-30 21:29:06'),
(579, 18, 18, '2025-08-02 17:54:19'),
(580, 17, 10, '2026-06-12 01:44:08'),
(581, 22, 16, '2025-08-28 00:26:20'),
(582, 14, 30, '2025-07-12 21:21:37'),
(583, 14, 21, '2025-08-10 10:09:52'),
(584, 17, 44, '2025-03-21 20:02:29'),
(585, 20, 43, '2025-12-14 01:23:32'),
(586, 19, 33, '2025-02-02 12:29:54'),
(587, 19, 13, '2025-01-04 15:19:31'),
(588, 21, 7, '2026-05-05 09:17:16'),
(589, 19, 32, '2026-02-22 11:40:20'),
(590, 23, 6, '2025-12-27 03:34:50'),
(591, 21, 144, '2025-09-06 19:00:14'),
(592, 19, 16, '2026-03-11 18:41:17'),
(593, 20, 11, '2025-10-13 09:35:11'),
(594, 31, 33, '2026-02-15 19:49:43'),
(595, 27, 24, '2025-08-06 23:39:08'),
(596, 27, 21, '2026-06-04 19:33:49'),
(597, 32, 11, '2025-05-08 22:12:59'),
(598, 24, 16, '2025-05-31 03:35:47'),
(599, 26, 174, '2025-01-16 10:23:25'),
(600, 24, 37, '2025-05-18 16:23:58'),
(601, 28, 6, '2025-01-13 14:15:28'),
(602, 25, 2, '2025-10-06 12:05:35'),
(603, 33, 35, '2026-06-02 02:09:50'),
(604, 25, 33, '2025-10-11 01:40:51'),
(605, 32, 35, '2025-06-16 09:27:01'),
(606, 32, 44, '2025-04-28 12:59:31'),
(607, 33, 24, '2025-10-20 08:08:17'),
(608, 25, 25, '2026-06-03 06:05:47'),
(609, 25, 20, '2026-04-14 23:22:43'),
(610, 24, 9, '2026-01-02 07:03:21'),
(611, 33, 35, '2025-09-27 11:22:47'),
(612, 25, 15, '2025-01-27 21:54:54'),
(613, 32, 31, '2025-08-02 01:06:19'),
(614, 24, 1, '2026-06-03 01:26:05'),
(615, 32, 144, '2025-05-29 23:30:02'),
(616, 33, 7, '2026-06-06 13:48:27'),
(617, 33, 23, '2026-04-07 13:55:52'),
(618, 27, 37, '2025-01-30 01:44:08'),
(619, 32, 11, '2025-10-24 00:57:24'),
(620, 27, 32, '2026-05-30 13:07:07'),
(621, 29, 26, '2025-10-03 06:05:20'),
(622, 28, 39, '2025-02-13 00:14:27'),
(623, 29, 1, '2025-02-22 08:52:40'),
(624, 30, 28, '2025-04-14 15:15:31'),
(625, 29, 19, '2026-05-29 06:31:08'),
(626, 30, 38, '2025-10-30 04:49:41'),
(627, 24, 11, '2025-09-13 16:37:39'),
(628, 32, 5, '2025-07-02 02:16:59'),
(629, 27, 7, '2025-05-19 15:43:41'),
(630, 31, 46, '2025-09-28 00:50:27'),
(631, 25, 22, '2025-06-18 01:31:11'),
(632, 29, 3, '2025-10-10 10:06:10'),
(633, 32, 17, '2025-04-28 21:22:55'),
(634, 24, 31, '2025-10-11 06:41:55'),
(635, 30, 44, '2025-12-29 18:12:33'),
(636, 27, 38, '2026-01-18 23:11:46'),
(637, 26, 3, '2025-07-11 07:50:34'),
(638, 25, 28, '2026-05-28 09:55:20'),
(639, 28, 44, '2025-05-25 11:26:03'),
(640, 29, 35, '2025-10-10 06:36:07'),
(641, 28, 21, '2026-04-15 03:15:17'),
(642, 32, 165, '2025-01-28 11:40:26'),
(643, 29, 43, '2026-01-20 12:29:36'),
(644, 24, 25, '2025-08-21 11:47:39'),
(645, 32, 6, '2026-01-19 15:41:17'),
(646, 29, 39, '2025-12-21 17:03:42'),
(647, 28, 22, '2025-04-23 05:26:07'),
(648, 29, 8, '2025-08-29 23:51:58'),
(649, 27, 28, '2026-04-27 14:12:13'),
(650, 28, 4, '2025-11-06 14:55:45'),
(651, 30, 44, '2025-06-21 06:06:26'),
(652, 24, 6, '2026-01-21 21:48:57'),
(653, 24, 42, '2025-01-31 17:59:34'),
(654, 28, 30, '2025-10-22 18:07:57'),
(655, 27, 14, '2025-07-08 21:03:39'),
(656, 26, 2, '2025-03-01 04:40:43'),
(657, 33, 15, '2025-12-30 05:37:23'),
(658, 31, 17, '2026-06-02 00:57:02'),
(659, 32, 8, '2026-02-10 04:05:27'),
(660, 31, 31, '2026-06-06 13:06:27'),
(661, 32, 9, '2025-09-21 01:42:14'),
(662, 29, 45, '2025-03-08 19:12:25'),
(663, 31, 15, '2025-04-16 14:16:41'),
(664, 25, 12, '2025-02-27 11:42:40'),
(665, 26, 8, '2026-01-29 20:31:00'),
(666, 28, 7, '2025-07-18 11:34:56'),
(667, 28, 17, '2025-07-15 16:32:26'),
(668, 24, 29, '2025-06-17 12:54:33'),
(669, 32, 16, '2025-09-13 04:17:40'),
(670, 25, 5, '2026-04-30 00:16:38'),
(671, 29, 38, '2025-10-24 21:50:23'),
(672, 26, 19, '2025-05-28 02:10:34'),
(673, 28, 12, '2026-01-29 07:45:04'),
(674, 33, 1, '2025-05-20 12:27:18'),
(675, 29, 23, '2025-11-18 11:29:27'),
(676, 24, 40, '2026-01-11 05:56:36'),
(677, 25, 175, '2026-02-14 17:13:47'),
(678, 27, 44, '2025-05-18 12:22:03'),
(679, 31, 4, '2026-04-15 11:13:44'),
(680, 26, 6, '2025-01-04 00:03:22'),
(681, 26, 11, '2025-07-30 07:54:23'),
(682, 27, 6, '2025-02-03 16:50:35'),
(683, 27, 46, '2026-03-31 20:22:01'),
(684, 24, 39, '2026-05-15 18:15:33'),
(685, 28, 29, '2025-10-07 03:14:02'),
(686, 25, 13, '2025-04-20 00:38:10'),
(687, 32, 2, '2026-05-16 14:53:05'),
(688, 29, 47, '2025-08-17 07:10:28'),
(689, 26, 3, '2025-10-07 19:43:45'),
(690, 26, 22, '2025-03-14 07:20:32'),
(691, 26, 14, '2025-11-14 12:46:17'),
(692, 25, 11, '2025-04-22 22:29:04'),
(693, 33, 37, '2026-03-07 23:39:47'),
(694, 27, 20, '2025-08-27 08:35:02'),
(695, 29, 13, '2026-01-19 00:35:08'),
(696, 27, 11, '2025-02-14 13:42:18'),
(697, 30, 6, '2026-03-23 14:26:56'),
(698, 25, 45, '2025-08-25 03:48:26'),
(699, 25, 14, '2025-12-10 08:26:08'),
(700, 27, 20, '2025-11-07 14:31:56'),
(701, 25, 28, '2026-02-26 21:35:12'),
(702, 25, 26, '2026-01-20 02:26:11'),
(703, 25, 9, '2026-03-22 11:30:28'),
(704, 28, 18, '2025-07-22 07:42:40'),
(705, 25, 15, '2026-01-01 07:49:21'),
(706, 28, 22, '2026-06-07 02:38:54'),
(707, 28, 22, '2025-12-27 19:44:51'),
(708, 24, 18, '2026-01-25 01:02:28'),
(709, 30, 21, '2026-04-29 05:55:15'),
(710, 31, 13, '2026-01-18 14:34:51'),
(711, 26, 49, '2026-02-18 17:43:02'),
(712, 24, 32, '2025-03-03 17:06:01'),
(713, 27, 38, '2026-06-07 10:43:37'),
(714, 30, 169, '2025-08-23 02:24:48'),
(715, 30, 23, '2025-08-31 09:25:02'),
(716, 26, 28, '2026-06-12 04:54:24'),
(717, 26, 4, '2025-02-15 04:16:20'),
(718, 32, 2, '2026-03-03 03:25:27'),
(719, 27, 18, '2026-01-17 09:29:50'),
(720, 27, 3, '2026-05-02 21:29:21'),
(721, 28, 47, '2026-03-23 03:04:54'),
(722, 29, 12, '2025-07-14 20:16:25'),
(723, 28, 44, '2025-11-17 10:12:24'),
(724, 29, 24, '2026-05-09 10:14:10'),
(725, 24, 13, '2025-09-21 19:36:03'),
(726, 28, 14, '2026-02-25 20:04:31'),
(727, 29, 28, '2026-03-01 04:14:25'),
(728, 33, 4, '2026-06-11 23:30:29'),
(729, 28, 9, '2026-01-03 19:43:52'),
(730, 28, 24, '2025-10-26 22:28:38'),
(731, 24, 36, '2025-01-07 18:40:45'),
(732, 30, 17, '2025-06-11 01:47:59'),
(733, 24, 32, '2026-05-27 19:25:36'),
(734, 32, 11, '2025-05-12 10:57:56'),
(735, 31, 21, '2025-08-20 16:13:38'),
(736, 28, 1, '2025-12-26 21:42:08'),
(737, 31, 24, '2026-05-24 06:58:50'),
(738, 26, 26, '2026-02-14 21:18:03'),
(739, 24, 39, '2025-04-14 09:48:29'),
(740, 29, 2, '2025-09-08 03:09:20'),
(741, 31, 46, '2026-06-08 06:12:30'),
(742, 33, 3, '2025-01-11 05:31:06'),
(743, 27, 35, '2025-05-25 10:07:29'),
(744, 26, 12, '2025-10-14 14:31:29'),
(745, 30, 25, '2025-11-22 14:09:47'),
(746, 31, 9, '2025-09-27 18:48:05'),
(747, 32, 15, '2025-12-10 01:11:35'),
(748, 29, 13, '2025-01-02 08:34:52'),
(749, 31, 34, '2026-01-26 05:20:52'),
(750, 31, 26, '2025-01-15 02:11:21'),
(751, 31, 37, '2025-12-07 14:02:02'),
(752, 30, 14, '2025-08-10 23:04:03'),
(753, 24, 32, '2026-02-15 22:17:20'),
(754, 30, 4, '2025-04-16 12:39:03'),
(755, 26, 36, '2026-03-11 15:03:24'),
(756, 29, 16, '2026-04-29 21:10:55'),
(757, 24, 10, '2026-02-03 14:59:35'),
(758, 24, 5, '2025-10-26 19:57:45'),
(759, 24, 10, '2025-04-04 21:56:53'),
(760, 33, 33, '2026-04-08 19:47:22'),
(761, 28, 45, '2025-06-26 01:42:38'),
(762, 31, 21, '2025-02-10 05:09:20'),
(763, 28, 17, '2025-03-16 02:54:33'),
(764, 33, 24, '2025-07-18 04:28:04'),
(765, 24, 9, '2025-05-30 08:29:11'),
(766, 31, 28, '2025-06-01 04:05:45'),
(767, 29, 31, '2025-11-12 13:07:15'),
(768, 31, 26, '2025-11-04 11:48:26'),
(769, 27, 20, '2025-01-19 22:44:37'),
(770, 25, 13, '2026-05-03 04:56:59'),
(771, 28, 2, '2026-06-08 21:35:19'),
(772, 24, 15, '2025-08-26 02:23:09'),
(773, 29, 3, '2025-09-15 17:01:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `promo_id` int(10) UNSIGNED NOT NULL,
  `promo_code` varchar(50) NOT NULL,
  `promo_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('voucher','product','category','flashsale') NOT NULL DEFAULT 'voucher',
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

INSERT INTO `promotions` (`promo_id`, `promo_code`, `promo_name`, `description`, `type`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Chào mừng thành viên mới', NULL, 'voucher', 'fixed', 100000.00, 200000.00, 50000.00, 1000, 1, '2026-01-01 00:00:00', '2026-12-31 00:00:00', 1, '2026-05-29 19:17:55', '2026-06-11 01:56:28'),
(2, 'SALE20', 'Khuyến mãi 20%', NULL, 'voucher', 'percent', 20.00, 500000.00, 100000.00, 500, 0, '2025-06-01 00:00:00', '2025-12-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(3, 'FREESHIP', 'Miễn phí vận chuyển', NULL, 'voucher', 'fixed', 50000.00, 300000.00, NULL, 300, 0, '2025-07-01 00:00:00', '2025-09-30 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(4, 'SUMMER100K', 'Hè nóng giảm 100K', NULL, 'voucher', 'fixed', 100000.00, 800000.00, NULL, 200, 0, '2025-06-01 00:00:00', '2025-08-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(5, 'VIP30', 'Ưu đãi khách hàng VIP', NULL, 'voucher', 'percent', 30.00, 1000000.00, 200000.00, 50, 0, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, '2026-05-29 19:17:55', '2026-05-29 19:17:55'),
(6, 'GIAM5', 'giảm 5%', NULL, 'voucher', 'percent', 5.00, 0.00, NULL, 10, 2, '2026-06-05 00:00:00', '2026-12-31 00:00:00', 1, '2026-06-05 16:03:27', '2026-06-05 16:33:17'),
(7, 'TEST-BANK', 'test thanh toán online', NULL, 'voucher', 'fixed', 35000.00, 0.00, NULL, NULL, 10, '2026-06-11 00:00:00', '2026-12-30 00:00:00', 1, '2026-06-11 23:42:42', '2026-06-12 22:34:13');

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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `order_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `user_id`, `rating`, `comment`, `status`, `created_at`, `updated_at`, `is_deleted`, `order_id`) VALUES
(1, 1, 12, 5, 'Sản phẩm rất xịn, đế giày êm ái, đi chạy 10km không thấy mỏi chân. Hàng thật chất lượng tốt lắm!', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(2, 1, 8, 4, 'Giày đẹp, đủ size, giao hàng nhanh. Chỉ trừ là hộp quá chút.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(3, 1, 9, 5, 'Đã mua lần 2 vì quá xài lần đầu thích lắm. Điều kiện giao hàng tốt.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(4, 3, 10, 5, 'Giày Nike nhẹ, đẹp, ôm chân rất vừa. Xứng đáng với giá tiền.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(5, 3, 11, 4, 'Chất lượng tốt, thiết kế hiện đại. Gì giao hàng hơi lâu một chút.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(6, 3, 12, 5, 'Đã từng dùng nhiều dòng giày khác nhau, Revolution 7 là dòng kinh tế nhất mà chất lượng thực sự ok.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(7, 5, 8, 5, 'Mua cho chồng tập marathon, anh ấy rất thích, nói là đếm tốt hơn tất cả giày cũ. Giao hàng nhanh.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(8, 5, 13, 5, 'Giày chạy bộ đỉnh nhất tôi từng dùng. Gel phần sau giảm sốc rất tốt. Rất đáng tiền.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(9, 25, 12, 5, 'Vợt cầu lông đỉnh của đỉnh, cần đà tốt, góp chơi của tôi rất nhiều. Hàng chính hãng 100%.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(10, 25, 9, 5, 'Mua tặng anh trai sinh nhật, anh ấy mê lắm. Đóng gói đẹp, có chứng nhận hàng chính hãng.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(11, 25, 11, 4, 'Vợt rất tốt nhưng giá hơi cao. Nếu dùng cho chuyên nghiệp thì xứng đáng đầu tư.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(12, 26, 8, 5, 'Vợt phản công siêu nhanh, day chac, cam tay vua. Rat hai long.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(13, 26, 10, 4, 'Vợt tốt, nhẹ, phù hợp người chơi phản công. Ship nhanh.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(14, 28, 13, 4, 'Vợt giá tốt mà chơi được, phù hợp người mới bắt đầu như mình. Nên mua.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(15, 28, 11, 3, 'Vợt ok cho giá tiền, nhưng khung hơi bị rung, có lẽ do mình chơi mạnh.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(16, 31, 12, 5, 'Cầu bay đẹp, độ bền khá ổn, mua nhiều lần rồi. Giá tốt.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(17, 31, 9, 4, 'Dùng được khoảng 30-40 hiệu, không tệ. Nảy rất ổn cho cầu giao lưu.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(18, 16, 8, 5, 'Vải đẹp, thoáng mát, màu không bị bám sau nhiều lần giặt. Rất hài lòng.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(19, 16, 13, 4, 'Áo vừa vặn, chất lượng ok với mức giá. Khuyên bạn nên mua.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(20, 38, 8, 5, 'Quần chất, ôm sát nhưng không bị bó. Dùng cho yoga hàng ngày. Ship nhanh!', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(21, 38, 10, 5, 'Chất lượng rất tốt, giá hợp lý. Mua lần thứ 3 rồi vẫn rất thích.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(22, 23, 8, 4, 'Áo ôm đẹp, hỗ trợ tốt, chất liệu thoáng. Giao hàng nhanh.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(23, 23, 13, 5, 'Mua tập yoga, chất lượng rất tốt, không bại sau nhiều buổi tập. Hài lòng.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(24, 44, 9, 4, 'Tạ chắc, bọc cao su không bị tuột, đem được lâu. Giá rẻ.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(25, 44, 11, 4, 'Mua loại 3kg, chất lượng ok. Cảm ơn shop.', 'approved', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(26, 1, 13, 2, 'Hàng bị lỗi, mũi giày bị tách. Cần kiểm tra lại chất lượng trước khi gửi hàng!', 'pending', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL),
(27, 25, 13, 1, 'Vợt bị bong dây khi mới mua, phải đợi 1 tuần mới xong. Rất phiền.', 'pending', '2026-05-29 19:17:55', '2026-06-11 01:04:10', 0, NULL);

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

INSERT INTO `roles` (`role_id`, `role_name`, `description`) VALUES
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

INSERT INTO `search_logs` (`log_id`, `user_id`, `keyword`, `result_count`, `searched_at`) VALUES
(1, 12, 'giay chay bo', 5, '2026-05-29 19:17:55'),
(2, 12, 'yonex', 8, '2026-05-29 19:17:55'),
(3, 12, 'cau long', 12, '2026-05-29 19:17:55'),
(4, 8, 'vot cau long', 6, '2026-05-29 19:17:55'),
(5, 8, 'yonex astrox', 3, '2026-05-29 19:17:55'),
(6, 8, 'legging nu', 4, '2026-05-29 19:17:55'),
(7, 9, 'nike', 10, '2026-05-29 19:17:55'),
(8, 9, 'asics', 3, '2026-05-29 19:17:55'),
(9, 10, 'bong da adidas', 2, '2026-05-29 19:17:55'),
(10, 11, 'giay da bong', 5, '2026-05-29 19:17:55'),
(11, 13, 'sport bra', 4, '2026-05-29 19:17:55'),
(12, NULL, 'giay chay bo', 5, '2026-05-29 19:17:55'),
(13, NULL, 'vot cau long', 6, '2026-05-29 19:17:55'),
(14, NULL, 'ao the thao', 9, '2026-05-29 19:17:55'),
(15, 12, 'Giàn Tập AF69', 1, '2026-06-11 00:44:08'),
(16, 1, 'San pham test QA live', 1, '2026-06-11 03:01:44'),
(17, 1, 'San pham test QA live', 1, '2026-06-11 03:01:44'),
(18, 2, 'Sản phẩm', 114, '2026-06-11 23:36:55'),
(19, 2, '3k', 1, '2026-06-11 23:37:00'),
(20, 2, '5k', 1, '2026-06-11 23:37:05'),
(21, 2, '10k', 2, '2026-06-11 23:37:08'),
(22, 2, '5k', 1, '2026-06-11 23:39:26'),
(23, 2, '10k', 2, '2026-06-11 23:39:32'),
(24, 2, '3k', 1, '2026-06-11 23:39:36'),
(25, 2, 'vợt', 11, '2026-06-12 00:09:48'),
(26, 2, 'ẩn hiện', 0, '2026-06-12 11:47:18'),
(27, 20, 'vợt yonex', 23, '2025-05-12 01:51:15'),
(28, 16, 'giày bóng đá', 14, '2026-02-09 06:35:12'),
(29, 16, 'bóng rổ', 30, '2025-03-20 10:34:55'),
(30, 14, 'vợt yonex', 49, '2025-03-20 07:11:04'),
(31, 15, 'áo cầu lông', 47, '2025-03-11 22:46:57'),
(32, 14, 'bóng rổ', 44, '2025-03-23 22:23:55'),
(33, 17, 'quần đùi thể thao', 43, '2025-08-20 22:53:52'),
(34, 19, 'giày bóng đá', 19, '2025-06-07 16:46:34'),
(35, 21, 'giày chạy bộ', 23, '2025-02-14 03:48:37'),
(36, 23, 'đệm gối', 48, '2025-07-07 21:10:52'),
(37, 22, 'dây nhảy', 7, '2026-01-29 06:14:32'),
(38, 16, 'đệm gối', 39, '2025-06-05 03:40:59'),
(39, 15, 'găng tay thủ môn', 49, '2026-02-25 07:48:44'),
(40, 23, 'giày chạy bộ', 14, '2025-03-01 22:17:18'),
(41, 23, 'quần đùi thể thao', 48, '2025-12-07 14:09:22'),
(42, 17, 'vợt yonex', 4, '2025-09-21 21:00:33'),
(43, 18, 'dây nhảy', 7, '2026-05-06 11:48:52'),
(44, 19, 'quần đùi thể thao', 41, '2026-02-28 10:56:01'),
(45, 21, 'găng tay thủ môn', 2, '2025-11-01 10:01:41'),
(46, 22, 'găng tay thủ môn', 4, '2026-01-01 01:12:27'),
(47, 14, 'dây nhảy', 6, '2025-03-16 21:04:53'),
(48, 20, 'giày chạy bộ', 10, '2025-10-13 08:30:46'),
(49, 23, 'áo cầu lông', 8, '2025-10-23 14:15:28'),
(50, 14, 'dây nhảy', 3, '2025-11-21 11:57:33'),
(51, 14, 'giày chạy bộ', 9, '2026-01-21 23:26:34'),
(52, 17, 'quần đùi thể thao', 6, '2025-04-05 16:37:50'),
(53, 19, 'vợt yonex', 39, '2025-11-02 08:25:35'),
(54, 19, 'tạ tay', 35, '2025-07-29 19:12:57'),
(55, 21, 'tạ tay', 19, '2026-01-17 19:19:19'),
(56, 19, 'quần đùi thể thao', 27, '2025-10-04 09:52:53'),
(57, 22, 'dây nhảy', 9, '2025-02-13 00:00:08'),
(58, 23, 'giày bóng đá', 45, '2025-02-28 10:15:00'),
(59, 15, 'áo cầu lông', 43, '2025-03-26 12:59:13'),
(60, 14, 'vợt yonex', 13, '2026-04-07 06:09:12'),
(61, 17, 'dây nhảy', 49, '2026-04-06 10:17:40'),
(62, 17, 'giày chạy bộ', 43, '2026-05-04 18:42:02'),
(63, 22, 'đệm gối', 28, '2026-06-01 00:05:11'),
(64, 14, 'quần đùi thể thao', 28, '2025-03-22 06:28:49'),
(65, 21, 'áo cầu lông', 24, '2026-04-10 19:33:43'),
(66, 18, 'đệm gối', 4, '2025-06-03 21:02:57'),
(67, 16, 'vợt yonex', 44, '2026-02-15 11:03:05'),
(68, 18, 'dây nhảy', 43, '2026-02-06 11:18:13'),
(69, 21, 'quần đùi thể thao', 25, '2026-01-18 14:01:32'),
(70, 14, 'găng tay thủ môn', 48, '2025-08-12 07:57:51'),
(71, 20, 'tạ tay', 33, '2026-05-23 09:27:55'),
(72, 22, 'tạ tay', 14, '2026-02-08 16:15:42'),
(73, 18, 'giày bóng đá', 48, '2026-04-19 16:52:59'),
(74, 20, 'áo cầu lông', 33, '2025-01-18 03:17:29'),
(75, 16, 'tạ tay', 28, '2025-01-17 08:35:03'),
(76, 23, 'giày bóng đá', 3, '2025-01-20 01:43:53'),
(77, 21, 'vợt yonex', 15, '2025-02-04 20:18:28'),
(78, 22, 'găng tay thủ môn', 40, '2025-02-14 13:17:09'),
(79, 17, 'dây nhảy', 13, '2025-08-02 13:38:20'),
(80, 16, 'giày bóng đá', 7, '2025-12-28 21:08:55'),
(81, 19, 'bóng rổ', 37, '2025-10-23 13:40:47'),
(82, 14, 'giày bóng đá', 7, '2025-07-29 23:52:42'),
(83, 23, 'quần đùi thể thao', 11, '2025-10-11 15:32:44'),
(84, 16, 'bóng rổ', 34, '2025-10-25 06:08:44'),
(85, 19, 'đệm gối', 42, '2025-07-12 07:43:26'),
(86, 21, 'áo cầu lông', 32, '2025-06-10 21:57:32'),
(87, 22, 'đệm gối', 13, '2026-02-21 20:59:29'),
(88, 21, 'giày bóng đá', 35, '2026-05-24 01:36:13'),
(89, 16, 'giày bóng đá', 2, '2025-03-03 05:38:32'),
(90, 20, 'găng tay thủ môn', 44, '2026-05-16 01:00:25'),
(91, 16, 'áo cầu lông', 15, '2025-05-04 13:39:20'),
(92, 19, 'vợt yonex', 32, '2026-03-30 15:38:20'),
(93, 17, 'giày chạy bộ', 10, '2026-01-03 07:28:06'),
(94, 14, 'giày bóng đá', 26, '2025-09-21 03:16:07'),
(95, 20, 'tạ tay', 1, '2026-02-21 15:22:43'),
(96, 16, 'áo cầu lông', 25, '2026-03-07 06:01:07'),
(97, 16, 'vợt yonex', 38, '2025-04-29 00:48:07'),
(98, 20, 'dây nhảy', 7, '2026-01-30 23:18:08'),
(99, 21, 'vợt yonex', 4, '2026-01-20 20:40:25'),
(100, 18, 'vợt yonex', 46, '2025-06-08 00:07:39'),
(101, 22, 'đệm gối', 14, '2026-05-03 22:27:58'),
(102, 21, 'áo cầu lông', 47, '2025-12-28 04:35:19'),
(103, 16, 'dây nhảy', 33, '2025-10-27 03:09:12'),
(104, 17, 'giày bóng đá', 1, '2025-12-19 11:57:24'),
(105, 23, 'vợt yonex', 44, '2025-09-03 17:41:13'),
(106, 18, 'áo cầu lông', 33, '2025-09-10 20:59:31'),
(107, 33, 'bóng rổ', 27, '2026-01-15 20:15:03'),
(108, 28, 'tạ tay', 16, '2026-03-22 07:25:20'),
(109, 26, 'vợt yonex', 48, '2025-12-28 19:41:49'),
(110, 26, 'đệm gối', 32, '2026-05-28 05:17:01'),
(111, 33, 'dây nhảy', 7, '2025-10-22 04:18:57'),
(112, 24, 'bóng rổ', 34, '2025-01-19 13:58:38'),
(113, 33, 'quần đùi thể thao', 49, '2025-01-21 16:44:25'),
(114, 27, 'quần đùi thể thao', 44, '2025-03-30 18:07:20'),
(115, 32, 'găng tay thủ môn', 42, '2026-01-07 14:32:56'),
(116, 31, 'găng tay thủ môn', 11, '2026-04-25 07:13:33'),
(117, 27, 'áo cầu lông', 12, '2026-02-06 04:06:26'),
(118, 32, 'tạ tay', 47, '2025-09-30 03:00:25'),
(119, 30, 'vợt yonex', 10, '2025-03-24 04:08:15'),
(120, 26, 'găng tay thủ môn', 34, '2025-04-18 10:00:56'),
(121, 30, 'găng tay thủ môn', 12, '2026-05-13 05:01:57'),
(122, 26, 'giày chạy bộ', 34, '2025-10-25 16:06:26'),
(123, 28, 'áo cầu lông', 41, '2025-05-19 12:22:42'),
(124, 24, 'quần đùi thể thao', 27, '2025-09-30 22:30:45'),
(125, 30, 'găng tay thủ môn', 44, '2025-11-12 14:49:01'),
(126, 32, 'quần đùi thể thao', 43, '2025-09-04 16:16:51'),
(127, 24, 'quần đùi thể thao', 44, '2025-04-19 05:22:33'),
(128, 31, 'áo cầu lông', 17, '2026-02-17 12:34:12'),
(129, 31, 'giày bóng đá', 36, '2026-01-04 20:40:30'),
(130, 26, 'giày bóng đá', 48, '2026-04-04 11:21:56'),
(131, 26, 'áo cầu lông', 20, '2026-01-24 22:59:21'),
(132, 27, 'găng tay thủ môn', 32, '2026-06-10 18:50:28'),
(133, 31, 'găng tay thủ môn', 8, '2025-04-03 02:25:12'),
(134, 32, 'vợt yonex', 26, '2025-01-29 16:15:57'),
(135, 31, 'quần đùi thể thao', 17, '2025-09-02 20:08:52'),
(136, 31, 'áo cầu lông', 11, '2026-01-30 02:48:53'),
(137, 30, 'quần đùi thể thao', 48, '2026-01-22 12:42:43'),
(138, 27, 'tạ tay', 14, '2026-01-25 04:09:47'),
(139, 33, 'bóng rổ', 5, '2025-08-08 12:14:16'),
(140, 27, 'giày bóng đá', 21, '2025-09-27 01:46:27'),
(141, 33, 'vợt yonex', 36, '2025-06-03 09:44:34'),
(142, 28, 'áo cầu lông', 50, '2025-08-07 21:02:08'),
(143, 24, 'găng tay thủ môn', 35, '2025-11-20 03:16:25'),
(144, 28, 'giày chạy bộ', 9, '2025-11-02 08:23:21'),
(145, 26, 'giày chạy bộ', 41, '2025-06-29 00:58:16'),
(146, 33, 'quần đùi thể thao', 33, '2025-06-09 19:30:27'),
(147, 31, 'giày bóng đá', 29, '2026-01-20 01:05:28'),
(148, 33, 'vợt yonex', 50, '2025-12-21 22:26:59'),
(149, 30, 'áo cầu lông', 20, '2025-02-07 08:35:05'),
(150, 26, 'giày chạy bộ', 13, '2025-01-02 06:46:46'),
(151, 33, 'đệm gối', 7, '2026-02-14 00:24:58'),
(152, 33, 'dây nhảy', 39, '2025-03-29 12:12:20'),
(153, 30, 'vợt yonex', 22, '2025-02-21 10:31:00'),
(154, 33, 'vợt yonex', 3, '2025-12-31 07:29:57'),
(155, 26, 'bóng rổ', 49, '2025-02-24 13:21:36'),
(156, 32, 'găng tay thủ môn', 28, '2026-04-11 03:37:24'),
(157, 27, 'bóng rổ', 48, '2026-02-12 07:25:37'),
(158, 25, 'đệm gối', 0, '2025-05-04 16:11:16'),
(159, 25, 'quần đùi thể thao', 27, '2025-04-16 16:41:26'),
(160, 29, 'giày bóng đá', 4, '2025-08-22 02:32:54'),
(161, 31, 'đệm gối', 20, '2025-11-25 11:00:33'),
(162, 31, 'đệm gối', 24, '2025-04-30 06:28:51'),
(163, 27, 'tạ tay', 26, '2025-10-22 04:50:32'),
(164, 30, 'giày bóng đá', 30, '2026-05-10 17:57:00'),
(165, 30, 'áo cầu lông', 0, '2025-03-27 01:55:38'),
(166, 30, 'áo cầu lông', 48, '2025-10-18 21:02:57'),
(167, 29, 'quần đùi thể thao', 27, '2025-12-14 23:45:01'),
(168, 25, 'găng tay thủ môn', 49, '2025-03-26 14:22:19'),
(169, 27, 'đệm gối', 28, '2025-11-15 08:51:54'),
(170, 25, 'dây nhảy', 26, '2026-03-11 00:46:15'),
(171, 31, 'găng tay thủ môn', 28, '2025-01-02 01:11:33'),
(172, 28, 'áo cầu lông', 45, '2025-12-10 12:58:07'),
(173, 24, 'quần đùi thể thao', 10, '2025-03-17 04:44:42'),
(174, 29, 'tạ tay', 25, '2026-01-04 20:03:52'),
(175, 33, 'bóng rổ', 44, '2025-09-22 17:42:15'),
(176, 25, 'giày chạy bộ', 10, '2025-01-30 03:51:53'),
(177, 25, 'tạ tay', 39, '2025-08-09 06:07:15'),
(178, 31, 'vợt yonex', 47, '2026-02-10 23:09:06'),
(179, 30, 'vợt yonex', 36, '2025-11-17 15:09:52'),
(180, 30, 'áo cầu lông', 40, '2025-11-01 12:34:55'),
(181, 25, 'tạ tay', 48, '2026-05-08 16:16:13'),
(182, 27, 'đệm gối', 20, '2025-01-25 05:20:42'),
(183, 26, 'quần đùi thể thao', 28, '2026-04-04 07:40:29'),
(184, 28, 'bóng rổ', 22, '2025-01-04 05:56:24'),
(185, 26, 'tạ tay', 29, '2025-11-23 05:12:02'),
(186, 31, 'giày bóng đá', 37, '2025-10-24 01:04:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `additional_fee` decimal(10,2) DEFAULT 0.00,
  `fee_type` varchar(50) DEFAULT 'standard',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `shipping_methods`
--

INSERT INTO `shipping_methods` (`method_id`, `method_name`, `additional_fee`, `fee_type`, `is_active`) VALUES
(1, 'Giao hàng tiêu chuẩn', 0.00, 'standard', 1),
(2, 'Giao nhanh (Express)', 15000.00, 'express', 1),
(3, 'Nhận tại cửa hàng', 0.00, 'store_pickup', 1);

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

INSERT INTO `shipping_prices` (`shipping_id`, `province_city`, `base_price`, `estimated_days`, `created_at`, `updated_at`) VALUES
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

INSERT INTO `site_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_type`, `group_name`, `display_name`, `description`, `sort_order`, `is_public`, `updated_by`, `updated_at`) VALUES
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
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=hoat dong, 0=bi khoa',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL COMMENT 'Khoa tam thoi sau nhieu lan dang nhap sai',
  `login_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'So lan dang nhap sai lien tiep',
  `remember_token` varchar(255) DEFAULT NULL COMMENT 'Token ghi nhớ đăng nhập',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `review_banned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tai khoan nguoi dung';

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `role_id`, `full_name`, `email`, `google_id`, `facebook_id`, `phone`, `password_hash`, `avatar_url`, `gender`, `date_of_birth`, `is_active`, `is_deleted`, `locked_until`, `login_attempts`, `remember_token`, `email_verified`, `created_at`, `updated_at`, `review_banned`) VALUES
(1, 1, 'Quản Trị Viên', 'admin@gmail.com', NULL, NULL, '0901000001', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/avatar_1_1781017883.png', NULL, NULL, 1, 0, NULL, 0, '$2y$10$OIvMG2avM/kjXIVXSnzouu0RBM3nkpc7oAEDHm1eDEcMbxvIvK2qu', 1, '2026-05-29 19:17:55', '2026-06-14 01:14:27', 0),
(2, 3, 'Bug', 'vancedloile@gmail.com', NULL, NULL, '0987654321', '$2y$10$vuvFSwtxYA8.tss5/Wg99eS65JeT5x6MyQDjtDrzZdKEnU309/FLG', '/assets/uploads/avatars/avatar_14_1781114853.png', NULL, '2004-10-21', 1, 0, NULL, 0, '$2y$10$ZrOiQ8oJOlocn/2R/HH0Ye4HzkPF8JiDtAqwLJkfuLNsgfLcX7Cze', 1, '2026-06-11 01:07:16', '2026-06-14 01:34:42', 0),
(3, 4, 'Staff Tài Khoản', 'nvacc@gmail.com', NULL, NULL, '0901000004', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/staff.jpg', NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-13 06:39:03', 0),
(4, 5, 'Staff Sản Phẩm', 'nvsp@gmail.com', NULL, NULL, '0901000005', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/staff.jpg', NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-13 06:39:03', 0),
(5, 6, 'Staff Đơn Hàng', 'nvorder@gmail.com', NULL, NULL, '0901000006', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/staff.jpg', NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-13 06:39:03', 0),
(6, 7, 'Staff Thống Kê', 'nvtk@gmail.com', NULL, NULL, '0901000007', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/staff.jpg', NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-06-06 03:16:45', '2026-06-13 06:39:03', 0),
(7, 8, 'Staff CMS', 'nvcms@gmail.com', NULL, NULL, '0901000008', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', '/assets/uploads/avatars/staff.jpg', NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-06-06 03:16:46', '2026-06-13 06:39:03', 0),
(8, 3, 'Trần Thị Bích', 'bich.tran@gmail.com', NULL, NULL, '0923456789', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, '2026-06-14 01:34:13', 5, NULL, 1, '2026-05-29 19:17:55', '2026-06-14 01:19:13', 0),
(9, 3, 'Lê Minh Cường', 'cuong.le@gmail.com', NULL, NULL, '0934567890', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-13 06:39:03', 0),
(10, 3, 'Phạm Thị Dung', 'dung.pham@gmail.com', NULL, NULL, '0945678901', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-13 06:39:03', 0),
(11, 3, 'Hoàng Văn Em', 'em.hoang@gmail.com', NULL, NULL, '0956789012', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-13 06:39:03', 0),
(12, 3, 'Nguyễn Văn An', 'user@gmail.com', NULL, NULL, '0912345678', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-13 06:39:03', 0),
(13, 3, 'Nguyễn Thị Phương', 'phuong.nt@gmail.com', NULL, NULL, '0967890123', '$2y$10$w9aoVDTN7R7vivnQVT.dr.9jcaUTbPYOQSa9ZN8v.VgGGY4brzuP.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 1, '2026-05-29 19:17:55', '2026-06-13 06:39:03', 0),
(14, 3, 'Khách hàng số 1', 'khachhang_1_1781308682@example.com', NULL, NULL, '0971386588', '$2y$10$fofWvsib3n2TOahrwdpe/.gK/k4Nv444hiiNi2htgVV8WX3gl3uYC', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-12-31 12:05:13', '2026-06-13 06:58:02', 0),
(15, 3, 'Khách hàng số 2', 'khachhang_2_1781308682@example.com', NULL, NULL, '0969934170', '$2y$10$bh0I9D.AwAZMCebKwl0eH.8t7PEh3NWyQ.iOWvmRutQ5bDU0V.e46', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-11-01 17:05:43', '2026-06-13 06:58:03', 0),
(16, 3, 'Khách hàng số 3', 'khachhang_3_1781308683@example.com', NULL, NULL, '0975404289', '$2y$10$t2YNkt1Mjoz10rf9h1TgAuksSSw1im8OHScUhJwYJ89CGHPDLHMuK', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-02-20 13:26:06', '2026-06-13 06:58:03', 0),
(17, 3, 'Khách hàng số 4', 'khachhang_4_1781308683@example.com', NULL, NULL, '0992794886', '$2y$10$zTGs8spnG7avypV.wvm13eCnjSxPxa4fH3EimCIvZtHpuXwh7hEES', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-05-08 06:46:19', '2026-06-13 06:58:03', 0),
(18, 3, 'Khách hàng số 5', 'khachhang_5_1781308683@example.com', NULL, NULL, '0957416337', '$2y$10$MilXjhISB2VO7kuySBm4eONBi8eePg5YBSba46zHfFXztVyjikrKy', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-06-07 07:17:31', '2026-06-13 06:58:03', 0),
(19, 3, 'Khách hàng số 6', 'khachhang_6_1781308683@example.com', NULL, NULL, '0930442750', '$2y$10$31hgdpG76AaXSyVIPXqHwuMlrNPcI75MVUSCVwPad.Hvi5EvIucbK', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-08-29 17:57:02', '2026-06-13 06:58:03', 0),
(20, 3, 'Khách hàng số 7', 'khachhang_7_1781308683@example.com', NULL, NULL, '0982824664', '$2y$10$ZN1v00yERj/26ucdPJzmS.2uLiHshXdub3doFUTmsnl.KEMiYsvd6', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-05-19 17:27:57', '2026-06-13 06:58:03', 0),
(21, 3, 'Khách hàng số 8', 'khachhang_8_1781308683@example.com', NULL, NULL, '0953812485', '$2y$10$ifDhgzXAmZhdUaLpRMa2rOHLrprKPHvqWQauLC4Vnm68lcjiTI9s.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-07-02 01:33:40', '2026-06-13 06:58:03', 0),
(22, 3, 'Khách hàng số 9', 'khachhang_9_1781308683@example.com', NULL, NULL, '0974237206', '$2y$10$mhGrMSOjujd.QX5QqQBJc.2xxWTX59nv3KVzE.fAeoHyCuuGo0s1i', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-03-25 21:19:26', '2026-06-13 06:58:03', 0),
(23, 3, 'Khách hàng số 10', 'khachhang_10_1781308683@example.com', NULL, NULL, '0955243733', '$2y$10$0.cR5EYRTlS2XHHU25r4lO7VTCKTZXtFb18WK0fT6aHmS4A2KPO3q', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-10-08 01:31:50', '2026-06-13 06:58:03', 0),
(24, 3, 'Khách hàng số 11', 'khachhang_11_1781310134@example.com', NULL, NULL, '0989684959', '$2y$10$CeURItHfgegY//KofqQjIO8w93UBSMJwwfytfPmD1wfMOT8GZ2KBO', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-11-05 06:04:04', '2026-06-13 07:25:46', 0),
(25, 3, 'Khách hàng số 12', 'khachhang_12_1781310134@example.com', NULL, NULL, '0953350393', '$2y$10$Cte8Tbnev0HjDtC0AeYSbORhmi1w2S8i5o9tYvMfVd4x8zp4jXm62', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-06-28 02:30:09', '2026-06-13 07:25:43', 0),
(26, 3, 'Khách hàng số 13', 'khachhang_13_1781310134@example.com', NULL, NULL, '0990860538', '$2y$10$u96Ui0Lpv0NFPYFZ/P80EOQ3bpf22HgpebeGoS3zfiII7iUYZ/Ci.', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-01-25 07:50:17', '2026-06-13 07:25:36', 0),
(27, 3, 'Khách hàng số 14', 'khachhang_14_1781310134@example.com', NULL, NULL, '0974967710', '$2y$10$hdjwS3hDp1kReKDrUklv9..tzrHk/3phAl8R1BznP6Bnigfp6tNbu', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-02-21 04:25:29', '2026-06-13 07:25:28', 0),
(28, 3, 'Khách hàng số 15', 'khachhang_15_1781310134@example.com', NULL, NULL, '0957758089', '$2y$10$/EIpa3uocOOIf86XPO41seG8os9mQwm634Y8OSshgvXdtfIyI4bFO', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2026-04-20 14:03:14', '2026-06-13 07:25:26', 0),
(29, 3, 'Khách hàng số 16', 'khachhang_16_1781310134@example.com', NULL, NULL, '0984507495', '$2y$10$kPdOUw.I93DiwvlNgC1IH.D8RDzkd8KV.Jyub5AURrePCSjXkZlSi', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-10-28 07:20:06', '2026-06-13 07:25:24', 0),
(30, 3, 'Khách hàng số 17', 'khachhang_17_1781310134@example.com', NULL, NULL, '0939597074', '$2y$10$UHGp30paALwhJSRedXx7nuKo1zjNrPhJsWnfXs54Kg.nKcDrQ.9hO', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-10-14 07:06:08', '2026-06-13 07:25:21', 0),
(31, 3, 'Khách hàng số 18', 'khachhang_18_1781310134@example.com', NULL, NULL, '0982969979', '$2y$10$Aohum6AHusyBjF1/Mz4auuFhrjBMcobDLq1jDrl79tWS.47i434nS', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-09-03 10:00:21', '2026-06-13 07:25:18', 0),
(32, 3, 'Khách hàng số 19', 'khachhang_19_1781310134@example.com', NULL, NULL, '0933978533', '$2y$10$ZZ3o4ujAY7o/BazizubnfOJ6zaeMds9ViG9AalMSyh1/sQJwHVd1u', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-10-19 20:33:27', '2026-06-13 07:25:15', 0),
(33, 3, 'Khách hàng số 20', 'khachhang_20_1781310134@example.com', NULL, NULL, '0987924889', '$2y$10$KOHfR9fkw60.uCQQDwHdxO3ecPLvGNjRwsMlk8LkPtM93WHrVlp0u', NULL, NULL, NULL, 1, 0, NULL, 0, NULL, 0, '2025-04-15 14:47:10', '2026-06-13 07:25:12', 0),
(34, 3, 'Test Lock', 'lockme_123@axeron.com', NULL, NULL, '0988000123', '$2y$10$wLRHg9O.4u7P0/bZf8L0reMMtUshFJbEROFcciZljUvYqKn2FBZfq', NULL, NULL, NULL, 0, 0, NULL, 1, NULL, 1, '2026-06-13 14:40:33', '2026-06-13 21:54:35', 0);

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

INSERT INTO `user_addresses` (`address_id`, `user_id`, `recipient_name`, `phone`, `province`, `district`, `ward`, `street_address`, `is_default`, `created_at`) VALUES
(1, 12, 'Nguyễn Văn An', '0912345678', 'TP Hồ Chí Minh', 'Quận 1', 'Phường Bến Nghé', '12 Nguyễn Huệ', 1, '2026-05-29 19:17:55'),
(2, 8, 'Trần Thị Bích', '0923456789', 'TP Hồ Chí Minh', 'Quận Bình Thạnh', 'Phường 25', '45 Xô Viết Nghệ Tĩnh', 1, '2026-05-29 19:17:55'),
(3, 9, 'Lê Minh Cường', '0934567890', 'Hà Nội', 'Cầu Giấy', 'Phường Dịch Vọng', '88 Trần Thái Tông', 1, '2026-05-29 19:17:55'),
(4, 10, 'Phạm Thị Dung', '0945678901', 'Đà Nẵng', 'Hải Châu', 'Phường Hải Châu 1', '23 Trần Phú', 1, '2026-05-29 19:17:55'),
(5, 11, 'Hoàng Văn Em', '0956789012', 'TP Hồ Chí Minh', 'Quận 7', 'Phường Tân Phong', '5 Nguyễn Văn Linh', 1, '2026-05-29 19:17:55'),
(6, 13, 'Nguyễn Thị Phương', '0967890123', 'TP Hồ Chí Minh', 'Thủ Đức', 'Phường Linh Trung', '100 Võ Văn Ngân', 1, '2026-05-29 19:17:55'),
(13, 2, 'Tester User', '0987654321', 'TP. Hồ Chí Minh', 'Huyện Nhà Bè', '', '6/21 Huỳnh Tấn Phát', 1, '2026-06-11 01:23:24');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_wishlists`
--

CREATE TABLE `user_wishlists` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_wishlists`
--

INSERT INTO `user_wishlists` (`wishlist_id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 3, 170, '2026-06-11 16:37:04'),
(2, 3, 171, '2026-06-11 16:37:07'),
(3, 3, 172, '2026-06-11 16:37:14'),
(4, 3, 175, '2026-06-11 16:39:28'),
(5, 3, 176, '2026-06-11 16:39:34'),
(6, 3, 174, '2026-06-11 16:39:38'),
(8, 3, 1, '2026-06-11 17:05:38'),
(9, 3, 56, '2026-06-12 04:47:53');

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
-- Chỉ mục cho bảng `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `idx_order_user` (`user_id`),
  ADD KEY `idx_order_status` (`order_status`),
  ADD KEY `idx_order_date` (`created_at`),
  ADD KEY `fk_order_shipping` (`shipping_id`),
  ADD KEY `fk_order_promo` (`promo_id`),
  ADD KEY `fk_order_shipping_method` (`shipping_method_id`);

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
  ADD PRIMARY KEY (`method_id`);

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
-- Chỉ mục cho bảng `user_wishlists`
--
ALTER TABLE `user_wishlists`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `articles`
--
ALTER TABLE `articles`
  MODIFY `article_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=334;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `session_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50108;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=231;

--
-- AUTO_INCREMENT cho bảng `order_status_logs`
--
ALTER TABLE `order_status_logs`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `txn_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT cho bảng `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT cho bảng `product_view_logs`
--
ALTER TABLE `product_view_logs`
  MODIFY `view_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=774;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promo_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT cho bảng `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `user_wishlists`
--
ALTER TABLE `user_wishlists`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  ADD CONSTRAINT `fk_order_shipping_method` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`method_id`) ON DELETE SET NULL,
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
