<?php
/**
 * API Hủy đơn hàng từ phía người dùng
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện chức năng này.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');

if (!$orderId || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ. Vui lòng nhập lý do hủy.']);
    exit;
}

$db = db();
$userId = getUserId();

// Lấy thông tin đơn hàng và kiểm tra quyền sở hữu
$order = $db->selectOne("SELECT * FROM orders WHERE order_id = ? AND user_id = ?", [$orderId, $userId]);

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc bạn không có quyền hủy đơn này.']);
    exit;
}

// Chỉ cho phép hủy khi pending hoặc confirmed
if (!in_array($order['order_status'], ['pending', 'confirmed'])) {
    echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.']);
    exit;
}

$db->beginTransaction();

try {
    $noteAppend = "";
    // Nếu thanh toán online và đã trả tiền, thêm ghi chú cần hoàn tiền
    if ($order['payment_method'] !== 'cod' && $order['payment_status'] === 'paid') {
        $noteAppend = "\n[Hệ thống]: Khách hàng đã tự hủy - Cần hoàn tiền.";
    }
    
    $newNote = trim($order['note'] . $noteAppend);
    
    // Cập nhật trạng thái đơn hàng thành cancelled
    $db->update("UPDATE orders SET order_status = 'cancelled', note = ?, updated_at = NOW() WHERE order_id = ?", [$newNote, $orderId]);
    
    // Hoàn lại tồn kho
    $orderItems = $db->select("SELECT variant_id, quantity FROM order_items WHERE order_id = ?", [$orderId]);
    foreach ($orderItems as $item) {
        $variantId = $item['variant_id'];
        $qty = $item['quantity'];
        
        // Hoàn tồn kho biến thể
        $db->update("UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE variant_id = ?", [$qty, $variantId]);
        
        // Tìm product_id của biến thể để cập nhật tổng kho (nếu cần thiết)
        // Lấy lại tổng tồn kho của toàn bộ các biến thể đang active của product đó
        $pv = $db->selectOne("SELECT product_id FROM product_variants WHERE variant_id = ?", [$variantId]);
        if ($pv) {
            $productId = $pv['product_id'];
            $totalStock = $db->selectOne("SELECT SUM(stock_quantity) as total FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0", [$productId]);
            $stockSum = (int)($totalStock['total'] ?? 0);
            
            $db->update("UPDATE products SET stock_quantity = ? WHERE product_id = ?", [$stockSum, $productId]);
        }
    }
    
    // Ghi log trạng thái
    $logNote = "Lý do: " . $reason;
    $db->insert("INSERT INTO order_status_logs (order_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)", 
        [$orderId, $userId, $order['order_status'], 'cancelled', $logNote]);
        
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Hủy đơn hàng thành công.']);
} catch (Exception $e) {
    $db->rollBack();
    error_log("Cancel Order Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi hủy đơn hàng. Vui lòng thử lại sau.']);
}
