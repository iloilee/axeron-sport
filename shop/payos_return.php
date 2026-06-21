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
    // Bỏ cập nhật CSDL ở đây vì đã xử lý qua webhook (api/payos_webhook.php) để đảm bảo bảo mật.
    // Việc cập nhật trạng thái đơn hàng chỉ được thực hiện khi nhận webhook có signature hợp lệ từ PayOS.
    // setFlash('info', 'Thanh toán đang được xử lý. Trạng thái đơn hàng sẽ được cập nhật trong giây lát.');
    
} else {
    setFlash('error', 'Giao dịch thanh toán chưa hoàn tất hoặc có lỗi xảy ra.');
}

redirect(BASE_URL . "/shop/order-confirmation.php?id=$orderId&token=$guestToken");
