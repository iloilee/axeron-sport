<?php
/**
 * Migration: Thêm cột remember_token vào bảng users
 * Chạy file này 1 lần để thêm cột cần thiết cho chức năng "Ghi nhớ đăng nhập"
 */
require_once __DIR__ . '/../config/database.php';

$db = db();

try {
    // Kiểm tra cột đã tồn tại chưa
    $result = $db->selectOne("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
        AND TABLE_NAME = 'users' 
        AND COLUMN_NAME = 'remember_token'
    ");

    if ($result) {
        echo "Cột 'remember_token' đã tồn tại trong bảng 'users'. Không cần thêm.\n";
    } else {
        $db->query("ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) DEFAULT NULL COMMENT 'Token ghi nhớ đăng nhập' AFTER `login_attempts`");
        echo "✅ Đã thêm cột 'remember_token' vào bảng 'users' thành công!\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
