<?php
/**
 * Simple Rate Limiting System
 * Giới hạn số lượng request theo IP và Endpoint để chống Spam / DDoS
 */

function checkRateLimit($endpoint, $maxRequests = 60, $timeWindow = 60) {
    // Determine client IP
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    $ip = trim($ip);

    $cacheDir = __DIR__ . '/../logs/rate_limit/';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    
    $key = md5($ip . '_' . $endpoint);
    $file = $cacheDir . $key . '.json';
    
    $now = time();
    $data = [];
    
    // Đọc lịch sử request
    if (file_exists($file)) {
        $content = @file_get_contents($file);
        if ($content) {
            $data = json_decode($content, true) ?: [];
        }
    }
    
    // Xóa các request đã quá hạn (ngoài timeWindow)
    $data = array_filter($data, function($timestamp) use ($now, $timeWindow) {
        return ($now - $timestamp) < $timeWindow;
    });
    
    // Kiểm tra giới hạn
    if (count($data) >= $maxRequests) {
        header('HTTP/1.1 429 Too Many Requests');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau ít phút!',
            'error_code' => 'RATE_LIMIT_EXCEEDED'
        ]);
        exit;
    }
    
    // Thêm request hiện tại và lưu lại
    $data[] = $now;
    @file_put_contents($file, json_encode(array_values($data)));
}
