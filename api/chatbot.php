<?php
/**
 * Chatbot API Endpoint - Axeron Sports Shop
 * Xử lý logic AI Chatbot, lấy context từ DB và gọi Google Gemini API
 */

session_start();
require_once '../config/database.php';
require_once '../config/chatbot_config.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';
$session_id = $input['session_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (empty(trim($message))) {
    jsonResponse(false, 'Tin nhắn không được để trống.');
}

$db = db();

// 1. Quản lý Session
if (!$session_id) {
    // Tạo session mới
    $stmt = $db->query("INSERT INTO chat_sessions (user_id, status) VALUES (?, 'open')", [$user_id]);
    $session_id = $db->lastInsertId();
} else {
    // Kiểm tra session có tồn tại và đang mở không
    $sessionInfo = $db->selectOne("SELECT status FROM chat_sessions WHERE session_id = ?", [$session_id]);
    if (!$sessionInfo || $sessionInfo['status'] === 'closed') {
        // Tạo session mới nếu không hợp lệ
        $stmt = $db->query("INSERT INTO chat_sessions (user_id, status) VALUES (?, 'open')", [$user_id]);
        $session_id = $db->lastInsertId();
    }
}

// 2. Lưu tin nhắn của người dùng
$db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'user', ?)", [$session_id, $message]);

// Nếu chưa cấu hình API Key, trả về thông báo lỗi thân thiện
if (empty(GEMINI_API_KEY)) {
    $errorMsg = "Xin lỗi, hiện tại hệ thống AI đang được bảo trì (Thiếu API Key). Vui lòng thử lại sau hoặc liên hệ qua số hotline.";
    $db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'bot', ?)", [$session_id, $errorMsg]);
    jsonResponse(true, 'Success', ['reply' => $errorMsg, 'session_id' => $session_id]);
}

// 3. Chuẩn bị Context từ Database
$context = [];

// 3.1. Thông tin liên hệ
$settings = $db->select("SELECT setting_key, setting_value FROM site_settings WHERE group_name = 'contact'");
$contactInfo = [];
foreach ($settings as $s) {
    $contactInfo[$s['setting_key']] = $s['setting_value'];
}
$context[] = "Thông tin liên hệ cửa hàng:";
$context[] = "- Tên cửa hàng: " . ($contactInfo['site_name'] ?? 'Axeron Sport');
$context[] = "- Số điện thoại: " . ($contactInfo['contact_phone'] ?? '1800 0021');
$context[] = "- Địa chỉ: " . ($contactInfo['contact_address'] ?? '456 Nguyễn Thị Thập, Quận 7, TP.HCM');
$context[] = "- Giờ làm việc: " . ($contactInfo['contact_work_hours'] ?? '08:30 - 21:30');

// 3.2. Sản phẩm nổi bật (Lấy tối đa 10 sản phẩm để không vượt quá token limit)
$products = $db->select("
    SELECT p.product_name, p.base_price, p.stock_quantity, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE p.is_visible = 1 AND p.is_featured = 1 AND p.stock_quantity > 0
    ORDER BY p.featured_sort_order ASC
    LIMIT 10
");
if ($products) {
    $context[] = "\nDanh sách các sản phẩm nổi bật đang bán tại cửa hàng:";
    foreach ($products as $p) {
        $priceStr = number_format($p['base_price'], 0, ',', '.') . ' VNĐ';
        $context[] = "- Sản phẩm: {$p['product_name']} | Danh mục: {$p['category_name']} | Giá: {$priceStr} | Còn hàng";
    }
} else {
    $context[] = "\nHiện tại chưa có sản phẩm nổi bật nào.";
}

// 3.3. Lịch sử trò chuyện gần nhất (Lấy 5 tin nhắn cuối để hiểu ngữ cảnh)
$history = $db->select("
    SELECT sender_type, content 
    FROM chat_messages 
    WHERE session_id = ? 
    ORDER BY message_id DESC 
    LIMIT 6
", [$session_id]);
$history = array_reverse($history); // Đảo ngược để xếp theo thứ tự thời gian

$geminiHistory = [];
foreach ($history as $msg) {
    if ($msg['content'] === $message && $msg['sender_type'] === 'user') continue; // Bỏ qua tin nhắn vừa gửi vì sẽ thêm riêng vào cuối
    $geminiHistory[] = [
        "role" => $msg['sender_type'] === 'user' ? "user" : "model",
        "parts" => [["text" => $msg['content']]]
    ];
}

$contextStr = implode("\n", $context);
$fullPrompt = CHATBOT_SYSTEM_PROMPT . "\n\n[CONTEXT START]\n" . $contextStr . "\n[CONTEXT END]";

// 4. Gọi Google Gemini API
$url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

// Cấu trúc payload theo Gemini API v1beta
$payload = [
    "system_instruction" => [
        "parts" => [
            ["text" => $fullPrompt]
        ]
    ],
    "contents" => $geminiHistory
];

// Thêm tin nhắn hiện tại
$payload["contents"][] = [
    "role" => "user",
    "parts" => [["text" => $message]]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix lỗi SSL trên localhost XAMPP

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$replyMsg = "Xin lỗi, tôi đang gặp lỗi kỹ thuật khi kết nối đến AI. Vui lòng thử lại sau.";

if ($httpCode == 200 && $response) {
    $data = json_decode($response, true);
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $replyMsg = $data['candidates'][0]['content']['parts'][0]['text'];
    }
} else {
    // Phân tích lỗi cụ thể để báo lại
    if ($httpCode == 429) {
        $replyMsg = "Xin lỗi, hiện tại hệ thống AI đang bị quá tải yêu cầu hoặc đã hết hạn mức sử dụng (Quota Exceeded). Vui lòng cung cấp API Key mới hoặc quay lại sau!";
    } elseif ($httpCode == 503) {
        $replyMsg = "Máy chủ AI của Google hiện đang bị quá tải (Service Unavailable). Vui lòng thử lại sau vài phút.";
    } elseif ($httpCode == 404) {
        $replyMsg = "Phiên bản AI cấu hình không tồn tại (Model Not Found). Vui lòng kiểm tra lại cấu hình GEMINI_MODEL.";
    }
    
    // Log error
    error_log("Gemini API Error. HTTP Code: " . $httpCode . ". cURL Error: " . $curlError . ". Response: " . $response);
}

// 5. Lưu phản hồi vào DB
$db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'bot', ?)", [$session_id, $replyMsg]);

// 6. Trả về cho frontend
jsonResponse(true, 'Success', [
    'reply' => $replyMsg,
    'session_id' => $session_id
]);
