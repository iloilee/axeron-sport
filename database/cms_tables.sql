-- =====================================================
-- CMS Database Tables for Axeron Sports Shop
-- Creates: banners, articles, site_settings tables
-- =====================================================

SET NAMES 'utf8mb4';
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Table: banners
-- Quản lý Banner/Slider trên trang chủ
-- =====================================================
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
    `banner_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL COMMENT 'Tiêu đề banner',
    `subtitle` VARCHAR(255) DEFAULT NULL COMMENT 'Phụ đề/description ngắn',
    `image_url` VARCHAR(500) NOT NULL COMMENT 'Đường dẫn ảnh banner',
    `image_url_mobile` VARCHAR(500) DEFAULT NULL COMMENT 'Ảnh banner cho mobile',
    `link_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL khi click vào banner',
    `link_type` ENUM('none', 'product', 'category', 'page', 'url') DEFAULT 'none' COMMENT 'Loại link: none, product, category, page, url',
    `target_id` INT UNSIGNED DEFAULT NULL COMMENT 'ID sản phẩm/danh mục/page nếu cần',
    `button_text` VARCHAR(100) DEFAULT NULL COMMENT 'Text nút bấm (VD: "Mua Ngay")',
    `position` INT DEFAULT 0 COMMENT 'Vị trí hiển thị (thứ tự)',
    `is_active` TINYINT(1) DEFAULT 1 COMMENT '1=hiển thị, 0=ẩn',
    `start_date` DATETIME DEFAULT NULL COMMENT 'Bắt đầu hiển thị từ ngày',
    `end_date` DATETIME DEFAULT NULL COMMENT 'Kết thúc hiển thị',
    `click_count` INT DEFAULT 0 COMMENT 'Số lượt click',
    `created_by` INT UNSIGNED DEFAULT NULL COMMENT 'Admin tạo',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`banner_id`),
    INDEX `idx_active_position` (`is_active`, `position`),
    INDEX `idx_dates` (`start_date`, `end_date`),
    INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý banner/slider';

-- =====================================================
-- Table: articles
-- Quản lý Bài viết/Tin tức/Blog
-- =====================================================
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
    `article_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL COMMENT 'Tiêu đề bài viết',
    `slug` VARCHAR(255) NOT NULL COMMENT 'Slug URL thân thiện',
    `excerpt` TEXT DEFAULT NULL COMMENT 'Tóm tắt ngắn (hiển thị ở danh sách)',
    `content` LONGTEXT DEFAULT NULL COMMENT 'Nội dung bài viết (HTML/Markdown)',
    `featured_image` VARCHAR(500) DEFAULT NULL COMMENT 'Ảnh đại diện bài viết',
    `category` ENUM('news', 'blog', 'promotion', 'announcement', 'guide') DEFAULT 'blog' COMMENT 'Loại bài viết',
    `tags` VARCHAR(500) DEFAULT NULL COMMENT 'Tags, phân cách bằng dấu phẩy',
    `author_id` INT UNSIGNED DEFAULT NULL COMMENT 'Tác giả (admin user_id)',
    `author_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên tác giả hiển thị',
    `is_featured` TINYINT(1) DEFAULT 0 COMMENT '1=bài nổi bật hiển thị trang chủ',
    `is_published` TINYINT(1) DEFAULT 0 COMMENT '1=đã xuất bản, 0=nháp',
    `published_at` DATETIME DEFAULT NULL COMMENT 'Thời gian xuất bản',
    `view_count` INT DEFAULT 0 COMMENT 'Số lượt xem',
    `meta_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO title',
    `meta_description` TEXT DEFAULT NULL COMMENT 'SEO description',
    `meta_keywords` VARCHAR(255) DEFAULT NULL COMMENT 'SEO keywords',
    `sort_order` INT DEFAULT 0 COMMENT 'Thứ tự sắp xếp',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`article_id`),
    UNIQUE KEY `uk_slug` (`slug`),
    INDEX `idx_published` (`is_published`, `published_at`),
    INDEX `idx_category` (`category`),
    INDEX `idx_featured` (`is_featured`),
    INDEX `idx_author` (`author_id`),
    FULLTEXT KEY `ft_search` (`title`, `excerpt`, `content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý bài viết/tin tức';

-- =====================================================
-- Table: site_settings
-- Cài đặt cơ bản của website
-- =====================================================
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
    `setting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL COMMENT 'Key cài đặt (unique)',
    `setting_value` TEXT DEFAULT NULL COMMENT 'Giá trị cài đặt',
    `setting_type` ENUM('text', 'textarea', 'image', 'number', 'boolean', 'json') DEFAULT 'text' COMMENT 'Loại giá trị',
    `group_name` VARCHAR(50) DEFAULT 'general' COMMENT 'Nhóm cài đặt: general, contact, social, footer',
    `display_name` VARCHAR(255) DEFAULT NULL COMMENT 'Tên hiển thị trong admin',
    `description` VARCHAR(500) DEFAULT NULL COMMENT 'Mô tả/giải thích cài đặt',
    `sort_order` INT DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    `is_public` TINYINT(1) DEFAULT 1 COMMENT '1=hiển thị công khai, 0=chỉ admin thấy',
    `updated_by` INT UNSIGNED DEFAULT NULL COMMENT 'Admin cập nhật cuối',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`setting_id`),
    UNIQUE KEY `uk_setting_key` (`setting_key`),
    INDEX `idx_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng cài đặt website';

-- =====================================================
-- Insert default site settings
-- =====================================================
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `display_name`, `description`, `sort_order`, `is_public`) VALUES
-- General Settings
('site_name', 'Axeron Sport', 'text', 'general', 'Tên Website', 'Tên website hiển thị trên trình duyệt và logo', 1, 1),
('site_tagline', 'Thể Thao Đỉnh Cao - Phong Cách Thượng Lưu', 'text', 'general', 'Tagline', 'Khẩu hiệu ngắn của website', 2, 1),
('site_logo', '', 'image', 'general', 'Logo Website', 'Logo chính của website (khuyến nghị: 200x60px)', 3, 1),
('site_favicon', '', 'image', 'general', 'Favicon', 'Icon nhỏ hiển thị trên tab trình duyệt (32x32px)', 4, 1),
('site_logo_alt', '', 'image', 'general', 'Logo Alternates', 'Logo cho chế độ nền tối (nếu cần)', 5, 1),

-- Contact Information
('contact_email', 'contact@axeron.vn', 'text', 'contact', 'Email Liên Hệ', 'Email chính để khách hàng liên hệ', 10, 1),
('contact_phone', '1900 1234', 'text', 'contact', 'Số Điện Thoại', 'Số hotline hoặc hotline hỗ trợ', 11, 1),
('contact_phone_2', '', 'text', 'contact', 'SĐT Hỗ Trợ 2', 'Số điện thoại hỗ trợ thứ 2', 12, 1),
('contact_address', '123 Nguyễn Trãi, Quận 1, TP.HCM', 'textarea', 'contact', 'Địa Chỉ', 'Địa chỉ trụ sở/cửa hàng chính', 13, 1),
('contact_map_embed', '', 'textarea', 'contact', 'Google Maps Embed', 'Mã nhúng bản đồ Google Maps', 14, 1),
('contact_work_hours', 'Thứ 2 - Thứ 7: 8:00 - 20:00', 'text', 'contact', 'Giờ Làm Việc', 'Giờ làm việc của cửa hàng', 15, 1),

-- Social Media
('social_facebook', '', 'text', 'social', 'Facebook', 'Link fanpage Facebook', 20, 1),
('social_instagram', '', 'text', 'social', 'Instagram', 'Link Instagram', 21, 1),
('social_youtube', '', 'text', 'social', 'YouTube', 'Link YouTube channel', 22, 1),
('social_tiktok', '', 'text', 'social', 'TikTok', 'Link TikTok', 23, 1),
('social_zalo', '', 'text', 'social', 'Zalo', 'Link Zalo Official Account', 24, 1),

-- Footer Content
('footer_about', 'Chuyên cung cấp các sản phẩm thể thao chính hãng từ các thương hiệu nổi tiếng thế giới. Cam kết 100% authentic, bảo hành chính hãng.', 'textarea', 'footer', 'Giới Thiệu Footer', 'Đoạn text giới thiệu ngắn hiển thị ở footer', 30, 1),
('footer_copyright', '© 2024 Axeron Sport. Tất cả quyền được bảo lưu.', 'text', 'footer', 'Copyright Text', 'Text copyright ở footer', 31, 1),
('footer_policy_links', '', 'textarea', 'footer', 'Footer Policy Links', 'JSON format: {"privacy": "/policies/privacy-policy.php", "terms": "/policies/purchase-policy.php"}', 32, 1);

-- =====================================================
-- Insert sample banners
-- =====================================================
INSERT INTO `banners` (`title`, `subtitle`, `image_url`, `link_type`, `button_text`, `position`, `is_active`, `start_date`, `end_date`) VALUES
('Siêu Sale Mùa Hè', 'Giảm đến 50% cho các sản phẩm thể thao', 'https://placehold.co/1920x800/e63946/ffffff?text=Summer+SALE+50%25', 'url', 'Mua Ngay', 1, 1, NULL, NULL),
('Colección Adidas Mới', 'Công nghệ Boost đỉnh cao - Thiết kế hiện đại', 'https://placehold.co/1920x800/1d3557/ffffff?text=Adidas+Collection', 'category', 'Khám Phá Ngay', 2, 1, NULL, NULL),
('Giày Chạy Bộ Pro', 'Hỗ trợ tối đa - Cực kỳ nhẹ', 'https://placehold.co/1920x800/457b9d/ffffff?text=Running+Pro', 'url', 'Xem Chi Tiết', 3, 1, NULL, NULL);

-- =====================================================
-- Insert sample articles
-- =====================================================
INSERT INTO `articles` (`title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `author_name`, `is_featured`, `is_published`, `published_at`, `meta_title`, `meta_description`) VALUES
('Top 10 Giày Chạy Bộ Tốt Nhất 2024', 'top-10-giay-chay-bo-2024', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024 với công nghệ tiên tiến nhất.', '<h2>Giới thiệu</h2><p>Năm 2024 hứa hẹn là năm của những đôi giày chạy bộ công nghệ cao với nhiều cải tiến vượt bậc. Dưới đây là top 10 sản phẩm đáng mua nhất.</p><h2>1. Nike Alphafly 3</h2><p>Đôi giày định hình lại ngành giày chạy bộ với công nghệ ZoomX foam và Air Zoom pods.</p><h2>2. Adidas Ultraboost Light</h2><p>Phiên bản nhẹ hơn, êm hơn với Boost foam thế hệ mới.</p>', 'https://placehold.co/800x400/1d3557/ffffff?text=Top+Running+Shoes', 'blog', 'Axeron Team', 1, 1, NOW(), 'Top 10 Giày Chạy Bộ Tốt Nhất 2024 | Axeron Sport', 'Cập nhật danh sách những đôi giày chạy bộ được đánh giá cao nhất năm 2024.'),
('Hướng Dẫn Chọn Size Giày Thể Thao', 'huong-dan-chon-size-giay', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ, mẹo đo chân tại nhà để chọn được đôi giày vừa vặn.', '<h2>Tại sao chọn đúng size quan trọng?</h2><p>Giày vừa vặn giúp bạn thoải mái khi vận động, tránh chấn thương và tối ưu hiệu suất tập luyện.</p><h2>Cách đo chân</h2><p>1. Đứng lên tờ giấy và vẽ viền chân<br>2. Đo khoảng cách từ gót đến ngón dài nhất<br>3. Cộng thêm 0.5-1cm để có không gian thoải mái</p>', 'https://placehold.co/800x400/457b9d/ffffff?text=Size+Guide', 'guide', 'Axeron Team', 1, 1, NOW(), 'Hướng Dẫn Chọn Size Giày Thể Thao | Axeron Sport', 'Bảng hướng dẫn chọn size giày chuẩn cho nam và nữ.'),
('Axeron Khai Trương Cửa Hàng Mới', 'axeron-khai-truong-cua-hang-moi', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi hấp dẫn dành cho khách hàng.', '<h2>Grand Opening Event</h2><p>Axeron Sport hân hạnh thông báo khai trương cửa hàng mới tại 456 Nguyễn Thị Thập, Quận 7, TP.HCM.</p><h2>Ưu đãi khai trương</h2><ul><li>Giảm 20% toàn bộ sản phẩm</li><li>Miễn phí vận chuyển cho đơn từ 500K</li><li>Tặng voucher 200K cho 100 khách hàng đầu tiên</li></ul>', 'https://placehold.co/800x400/e63946/ffffff?text=Grand+Opening', 'announcement', 'Axeron Team', 0, 1, NOW(), 'Axeron Khai Trương Cửa Hàng Mới | Axeron Sport', 'Chính thức khai trương chi nhánh mới tại Quận 7 với nhiều ưu đãi.');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Notes for implementation:
-- 1. Run this SQL in phpMyAdmin or MySQL CLI
-- 2. After running, update the upload config to support banners folder
-- 3. Create API endpoints for CRUD operations
-- 4. Update admin panel navigation
-- 5. Update homepage to fetch dynamic content
-- =====================================================
