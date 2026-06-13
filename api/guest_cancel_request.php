<?php
/**
 * API Hủy đơn hàng từ phía khách chưa đăng nhập (Guest)
 * Thay vì hủy trực tiếp, sẽ ghi chú vào đơn hàng để Admin duyệt.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$token = trim($_POST['token'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if (!$orderId || empty($token) || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ. Vui lòng nhập lý do hủy.']);
    exit;
}

$db = db();

// Tìm đơn hàng bằng order_id và guest_token
$order = $db->selectOne("SELECT * FROM orders WHERE order_id = ? AND guest_token = ?", [$orderId, $token]);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc phiên theo dõi không hợp lệ.']);
    exit;
}

// Chỉ cho phép yêu cầu hủy khi pending hoặc confirmed
if (!in_array($order['order_status'], ['pending', 'confirmed'])) {
    echo json_encode(['success' => false, 'message' => 'Không thể yêu cầu hủy đơn hàng ở trạng thái hiện tại.']);
    exit;
}

// Kiểm tra xem đã gửi yêu cầu hủy chưa
if (strpos($order['note'] ?? '', '[Yêu cầu hủy từ khách]') !== false) {
    echo json_encode(['success' => false, 'message' => 'Bạn đã gửi yêu cầu hủy cho đơn hàng này rồi. Vui lòng chờ Admin xử lý.']);
    exit;
}

// Thêm yêu cầu hủy vào phần Ghi chú (Note) của đơn hàng
$newNote = trim(($order['note'] ?? '') . "\n\n[Yêu cầu hủy từ khách]: " . $reason);

try {
    $db->update("UPDATE orders SET note = ?, updated_at = NOW() WHERE order_id = ?", [$newNote, $orderId]);
    echo json_encode(['success' => true, 'message' => 'Đã gửi yêu cầu hủy đơn hàng. Admin sẽ kiểm duyệt và xử lý sớm nhất.']);
} catch (Exception $e) {
    error_log("Guest Cancel Request Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại sau.']);
}
