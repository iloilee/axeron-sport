<?php
/**
 * API Lấy dự báo doanh thu từ Python AI Server (Prophet)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$db = db();

try {
    // 1. LẤY DỮ LIỆU LỊCH SỬ DOANH THU TỪ DATABASE
    // Lấy doanh thu theo từng ngày trong quá khứ
    $sql = "SELECT DATE(created_at) as ds, SUM(total_amount) as y 
            FROM orders 
            WHERE order_status NOT IN ('cancelled', 'returned') 
            AND payment_status = 'paid'
            GROUP BY DATE(created_at) 
            ORDER BY ds ASC";
            
    $raw_data = $db->select($sql);
    
    $historical_data = [];
    $labels_history = [];
    $values_history = [];

    foreach ($raw_data as $row) {
        $historical_data[] = [
            'ds' => $row['ds'],
            'y' => (float)$row['y']
        ];
        // Định dạng lại ngày để hiển thị đẹp hơn
        $labels_history[] = date('d/m/Y', strtotime($row['ds']));
        $values_history[] = (float)$row['y'];
    }

    if (count($historical_data) < 10) {
        echo json_encode(['error' => 'Chưa đủ 10 ngày dữ liệu để hệ thống AI (Prophet) có thể dự báo.']);
        exit;
    }

    // 2. GỌI API PYTHON ĐỂ LẤY DỰ BÁO 30 NGÀY TỚI
    $ch = curl_init('http://localhost:5000/forecast_revenue');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($historical_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Đặt timeout 30s vì model Prophet có thể mất vài giây để train
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); 

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception("Lỗi kết nối Python Server: " . curl_error($ch));
    }
    curl_close($ch);

    if ($httpCode !== 200) {
        $errData = json_decode($response, true);
        throw new Exception($errData['error'] ?? "Lỗi phản hồi từ Python Server (HTTP $httpCode)");
    }

    $forecast_data = json_decode($response, true);

    if (!$forecast_data || !is_array($forecast_data)) {
         throw new Exception("Dữ liệu trả về từ Python không hợp lệ.");
    }

    // 3. CHUẨN BỊ DỮ LIỆU CHO CHART.JS
    $labels_future = [];
    $values_future = [];

    // Để đường biểu đồ nối liền với nhau, điểm bắt đầu của Tương lai phải là điểm kết thúc của Quá khứ
    $last_history_value = end($values_history);
    $last_history_label = end($labels_history);
    
    $labels_future[] = $last_history_label;
    $values_future[] = $last_history_value;

    foreach ($forecast_data as $row) {
        $labels_future[] = date('d/m/Y', strtotime($row['ds']));
        // Không để dự báo số âm (doanh thu không thể âm)
        $values_future[] = $row['yhat'] > 0 ? $row['yhat'] : 0; 
    }

    // Trả về JSON tổng hợp cho Frontend
    echo json_encode([
        'status' => 'success',
        'labels_history' => $labels_history,
        'values_history' => $values_history,
        'labels_future' => $labels_future,
        'values_future' => $values_future
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}
