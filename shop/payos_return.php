<?php
/**
 * Trang nhận kết quả redirect từ PayOS sau khi thanh toán
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$guestToken = $_GET['token'] ?? null;
$code = $_GET['code'] ?? null;
$cancel = $_GET['cancel'] ?? 'false';
$status = $_GET['status'] ?? ''; // Ví dụ: PAID, CANCELLED

// Lấy orderId từ orderCode (vì PayOS trả về 'id' là mã link của họ, sẽ ghi đè 'id' của ta)
$orderId = $_GET['orderCode'] ?? $_GET['id'] ?? null;

if (!$orderId) {
    redirect(BASE_URL . '/');
}

if ($cancel === 'true' || $status === 'CANCELLED') {
    setFlash('error', 'Bạn đã hủy thanh toán. Đơn hàng vẫn được ghi nhận (Chờ xử lý) nhưng chưa được thanh toán.');
} elseif ($code === '00' || $status === 'PAID') {
    setFlash('success', 'Thanh toán thành công! Cảm ơn bạn đã mua sắm.');
    
    // Cập nhật trạng thái thanh toán thành công vào database
    $db = db();
    $db->update("UPDATE orders SET payment_status = 'paid' WHERE order_id = ?", [$orderId]);
    
} else {
    setFlash('error', 'Giao dịch thanh toán chưa hoàn tất hoặc có lỗi xảy ra.');
}

redirect(BASE_URL . "/shop/order-confirmation.php?id=$orderId&token=$guestToken");
