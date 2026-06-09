<?php
/**
 * Fix password_resets table - Cập nhật cấu trúc bảng cho OTP verification
 * Truy cập file này từ trình duyệt để chạy migration
 */
require_once __DIR__ . '/../config/database.php';

$db = db();
$conn = $db->getConnection();

echo "<h2>🔧 Fixing password_resets table...</h2>";

try {
    // Drop bảng cũ (cấu trúc không tương thích)
    $conn->query("DROP TABLE IF EXISTS `password_resets`");
    echo "<p>✅ Dropped old password_resets table</p>";

    // Tạo bảng mới với đầy đủ các cột cần thiết
    $sql = "CREATE TABLE `password_resets` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `user_id` INT(10) UNSIGNED NOT NULL,
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
        INDEX `idx_otp_code` (`otp_code`),
        CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->query($sql);
    echo "<p>✅ Created new password_resets table with correct schema</p>";

    echo "<h3 style='color: green;'>🎉 Migration completed successfully!</h3>";
    echo "<p>Bạn có thể xóa file này sau khi chạy xong.</p>";
    echo "<p><a href='/'>← Quay lại trang chủ</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
