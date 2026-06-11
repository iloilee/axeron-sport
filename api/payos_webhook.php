<?php
/**
 * API Webhook nhận thông báo thanh toán từ PayOS
 */
require_once __DIR__ . '/../config/database.php';

// Đảm bảo trả về JSON
header('Content-Type: application/json');

// Lấy raw body
$requestBody = file_get_contents('php://input');
$webhookData = json_decode($requestBody, true);

if (!$webhookData || !isset($webhookData['data']) || !isset($webhookData['signature'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$payosChecksumKey = getenv('PAYOS_CHECKSUM_KEY') ?: $_ENV['PAYOS_CHECKSUM_KEY'] ?? '';

if (empty($payosChecksumKey)) {
    echo json_encode(['success' => false, 'message' => 'Checksum Key is not configured']);
    exit;
}

$data = $webhookData['data'];
$signature = $webhookData['signature'];

// Tạo chuỗi ký (Signature verification)
$signData = [];
// Chỉ lấy các trường có giá trị, các trường null/empty có thể gây lỗi signature tùy phiên bản API, 
// nhưng chuẩn nhất của PayOS là lấy tất cả dữ liệu bên trong biến "data"
foreach ($data as $k => $v) {
    if (is_array($v)) continue; // Bỏ qua mảng lồng nhau nếu có
    $signData[$k] = $v;
}
ksort($signData);

$queryArr = [];
foreach ($signData as $k => $v) {
    $queryArr[] = $k . '=' . $v;
}
$queryString = implode('&', $queryArr);

$calculatedSignature = hash_hmac('sha256', $queryString, $payosChecksumKey);

// Nếu signature khớp, nghĩa là request an toàn và chuẩn xác từ PayOS
if ($calculatedSignature === $signature) {
    // Lấy thông tin orderCode và trạng thái
    $orderCode = $data['orderCode'];
    $code = $data['code']; // "00" nghĩa là thanh toán thành công
    
    if ($code == '00') {
        $db = db();
        
        // Cập nhật trạng thái payment của đơn hàng trong DB
        // Do lúc gửi API lên PayOS, ta truyền orderCode = order_id
        $orderId = (int)$orderCode;
        
        $db->update("UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE order_id = ?", [$orderId]);
        
        echo json_encode(['success' => true, 'message' => 'Order updated successfully']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Payment not successful, ignoring']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid signature']);
}
