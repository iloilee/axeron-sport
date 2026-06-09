<?php
/**
 * Script để tạo lại bảng password_resets nếu thiếu cột
 */
require_once __DIR__ . '/../config/database.php';

$db = db();

echo "<h2>Kiểm tra và sửa bảng password_resets</h2>";

// Xóa bảng cũ nếu tồn tại (để tạo lại đúng cấu trúc)
echo "<p>Đang xóa bảng cũ...</p>";
$db->delete("DROP TABLE IF EXISTS password_resets");

// Tạo bảng mới (không có foreign key để tránh lỗi)
$sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `reset_token` VARCHAR(64) NOT NULL,
    `otp_code` VARCHAR(6) NULL,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `used_at` DATETIME NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_reset_token` (`reset_token`),
    INDEX `idx_otp_code` (`otp_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$db->query($sql);

echo "<p style='color: green;'>✅ Bảng password_resets đã được tạo thành công!</p>";
echo "<p>Các cột: id, user_id, email, reset_token, otp_code, expires_at, verified_at, used_at, ip_address, created_at</p>";
echo "<p><a href='../auth/forgot-password.php'> Quay lại trang Quên mật khẩu</a></p>";
