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
    
    // Chủ động gọi API của PayOS để kiểm tra lại trạng thái thực tế nhằm cập nhật ngay lập tức
    // Tránh việc Webhook bị miss hoặc chưa được cấu hình.
    $payosClientId = getenv('PAYOS_CLIENT_ID') ?: $_ENV['PAYOS_CLIENT_ID'] ?? '';
    $payosApiKey = getenv('PAYOS_API_KEY') ?: $_ENV['PAYOS_API_KEY'] ?? '';
    
    if ($payosClientId && $payosApiKey && $orderId) {
        $ch = curl_init("https://api-merchant.payos.vn/v2/payment-requests/{$orderId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "x-client-id: $payosClientId",
            "x-api-key: $payosApiKey"
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        
        if ($result) {
            $response = json_decode($result, true);
            if (isset($response['code']) && $response['code'] == '00') {
                $payosStatus = $response['data']['status'] ?? '';
                if ($payosStatus === 'PAID') {
                    $db = db();
                    $order = $db->selectOne("SELECT total_amount FROM orders WHERE order_id = ?", [$orderId]);
                    if ($order) {
                        $amountPaid = $response['data']['amountPaid'] ?? $response['data']['amount'] ?? 0;
                        if ((int)$amountPaid >= (int)$order['total_amount']) {
                            // Cập nhật cả trạng thái thanh toán và trạng thái đơn hàng (đã xác nhận)
                            $db->update("UPDATE orders SET payment_status = 'paid', order_status = 'confirmed', updated_at = NOW() WHERE order_id = ?", [$orderId]);
                        }
                    }
                }
            }
        }
    }
} else {
    setFlash('error', 'Giao dịch thanh toán chưa hoàn tất hoặc có lỗi xảy ra.');
}

redirect(BASE_URL . "/shop/order-confirmation.php?id=$orderId&token=$guestToken");
