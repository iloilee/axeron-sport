<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ']);
    exit;
}

// Kiểm tra CSRF token
$headers = getallheaders();
$csrfToken = $_POST['csrf_token'] ?? $headers['X-CSRF-Token'] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ (CSRF)']);
    exit;
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng tính năng này', 'redirect' => true]);
    exit;
}

$db = db();
$userId = getUserId();
$action = $_POST['action'] ?? '';

if ($action === 'toggle') {
    $productId = (int)($_POST['product_id'] ?? 0);
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID sản phẩm không hợp lệ']);
        exit;
    }

    // Kiểm tra sản phẩm có tồn tại không
    $product = $db->selectOne("SELECT product_id FROM products WHERE product_id = ?", [$productId]);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // Kiểm tra xem đã yêu thích chưa
    $isFavorited = $db->selectOne("SELECT 1 FROM user_wishlists WHERE user_id = ? AND product_id = ?", [$userId, $productId]);

    if ($isFavorited) {
        // Hủy yêu thích
        $db->delete("DELETE FROM user_wishlists WHERE user_id = ? AND product_id = ?", [$userId, $productId]);
        echo json_encode(['success' => true, 'status' => 'removed']);
    } else {
        // Thêm yêu thích
        $db->insert("INSERT INTO user_wishlists (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
        echo json_encode(['success' => true, 'status' => 'added']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
exit;
