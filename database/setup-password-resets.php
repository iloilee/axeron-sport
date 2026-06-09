<?php
/**
 * Database Setup Script - Tạo bảng password_resets
 * Chạy script này một lần để tạo bảng cần thiết
 */

require_once __DIR__ . '/../config/database.php';

$db = db();

// SQL tạo bảng password_resets
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

try {
    $db->query($sql);
    echo "✅ Bảng password_resets đã được tạo thành công!\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
