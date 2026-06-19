<?php
/**
 * Chatbot API Endpoint - Axeron Sports Shop
 * Xử lý logic AI Chatbot với Gemini Function Calling
 */

session_start();
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/chatbot_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'chat';
$message = $input['message'] ?? '';
$session_id = $input['session_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$db = db();

if ($action === 'history') {
    if (!$session_id) {
        jsonResponse(true, 'No session', ['messages' => []]);
    }
    
    $sessionInfo = $db->selectOne("SELECT status FROM chat_sessions WHERE session_id = ?", [$session_id]);
    if (!$sessionInfo || $sessionInfo['status'] === 'closed') {
        jsonResponse(true, 'Session closed or invalid', ['messages' => []]);
    }
    
    $history = $db->select("
        SELECT sender_type, content 
        FROM chat_messages 
        WHERE session_id = ? 
        ORDER BY message_id ASC
        LIMIT 50
    ", [$session_id]);
    
    jsonResponse(true, 'Success', ['messages' => $history]);
}

if (empty(trim($message))) {
    jsonResponse(false, 'Tin nhắn không được để trống.');
}

// 1. Quản lý Session
if (!$session_id) {
    $stmt = $db->query("INSERT INTO chat_sessions (user_id, status) VALUES (?, 'open')", [$user_id]);
    $session_id = $db->lastInsertId();
} else {
    $sessionInfo = $db->selectOne("SELECT status FROM chat_sessions WHERE session_id = ?", [$session_id]);
    if (!$sessionInfo || $sessionInfo['status'] === 'closed') {
        $stmt = $db->query("INSERT INTO chat_sessions (user_id, status) VALUES (?, 'open')", [$user_id]);
        $session_id = $db->lastInsertId();
    }
}

// 2. Lưu tin nhắn của người dùng
$db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'user', ?)", [$session_id, $message]);

if (empty(GEMINI_API_KEY)) {
    $errorMsg = "Xin lỗi, hiện tại hệ thống AI đang được bảo trì (Thiếu API Key). Vui lòng thử lại sau.";
    $db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'bot', ?)", [$session_id, $errorMsg]);
    jsonResponse(true, 'Success', ['reply' => $errorMsg, 'session_id' => $session_id]);
}

// 3. Chuẩn bị Context từ Database (Sử dụng Cache Session)
$context = [];
if (!isset($_SESSION['chat_contact_info'])) {
    $settings = $db->select("SELECT setting_key, setting_value FROM site_settings WHERE group_name = 'contact'");
    $contactInfo = [];
    foreach ($settings as $s) {
        $contactInfo[$s['setting_key']] = $s['setting_value'];
    }
    $_SESSION['chat_contact_info'] = $contactInfo;
} else {
    $contactInfo = $_SESSION['chat_contact_info'];
}

$context[] = "Thông tin liên hệ cửa hàng:";
$context[] = "- Tên cửa hàng: " . ($contactInfo['site_name'] ?? 'Axeron Sport');
$context[] = "- Số điện thoại: " . ($contactInfo['contact_phone'] ?? '1800 0021');
$context[] = "- Địa chỉ: " . ($contactInfo['contact_address'] ?? '456 Nguyễn Thị Thập, Quận 7, TP.HCM');
$context[] = "- Giờ làm việc: " . ($contactInfo['contact_work_hours'] ?? '08:30 - 21:30');

// Lịch sử
$history = $db->select("
    SELECT sender_type, content 
    FROM chat_messages 
    WHERE session_id = ? 
    ORDER BY message_id DESC 
    LIMIT 6
", [$session_id]);
$history = array_reverse($history);

$geminiHistory = [];
foreach ($history as $msg) {
    if ($msg['content'] === $message && $msg['sender_type'] === 'user') continue;
    $geminiHistory[] = [
        "role" => $msg['sender_type'] === 'user' ? "user" : "model",
        "parts" => [["text" => $msg['content']]]
    ];
}

$contextStr = implode("\n", $context);
$fullPrompt = CHATBOT_SYSTEM_PROMPT . "
Bạn có thể sử dụng công cụ (tools) để tra cứu thông tin sản phẩm hoặc trạng thái đơn hàng. 
Nếu dùng công cụ search_products, khi có kết quả trả về, bạn hãy tóm tắt ngắn gọn và CHẮC CHẮN phải chèn thêm mã [PRODUCT_CARD:slug_san_pham] (thay slug_san_pham bằng slug thật) vào câu trả lời để hệ thống hiển thị ảnh cho khách hàng!
\n[CONTEXT START]\n" . $contextStr . "\n[CONTEXT END]";

// Thêm tin nhắn hiện tại
$geminiHistory[] = [
    "role" => "user",
    "parts" => [["text" => $message]]
];

// Zero-shot Tool Execution: Tra cứu DB trước khi gửi cho AI để giảm API Roundtrip
$msgLower = mb_strtolower($message, 'UTF-8');
$needSearch = preg_match('/(sản phẩm|áo|quần|giày|vợt|bóng|tìm|mua|có bán|giá|size|màu|axeron)/i', $msgLower);
$needOrder = preg_match('/(đơn hàng|đơn|order|tình trạng|kiểm tra|tra cứu|ax\d+|ordm-[a-z0-9]+|ord-[a-z0-9]+|\b[a-f0-9]{8}\b)/i', $msgLower);

if ($needSearch) {
    // Loại bỏ dấu câu để tách từ chính xác
    $cleanMsg = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $msgLower);
    
    // Lọc từ khóa thông minh bằng Stop Words mở rộng
    $stopWords = ['tìm', 'mua', 'xem', 'có', 'bán', 'cho', 'mình', 'tôi', 'bạn', 'ơi', 'nhé', 'không', 'hỏi', 'sản', 'phẩm', 'các', 'loại', 'những', 'một', 'chiếc', 'đôi', 'cái', 'thử', 'chào', 'shop', 'ở', 'đâu', 'giá', 'bao', 'nhiêu', 'vậy', 'ạ', 'bên', 'nào', 'luôn', 'rồi', 'chưa', 'làm', 'sao', 'tư', 'vấn', 'giúp'];
    $words = explode(' ', $cleanMsg);
    $keywords = [];
    foreach ($words as $w) {
        $w = trim($w);
        if (mb_strlen($w, 'UTF-8') >= 2 && !in_array($w, $stopWords)) {
            $keywords[] = $w;
        }
    }
    
    if (empty($keywords)) {
        $keywords[] = trim($cleanMsg);
    }
    
    // Tìm kiếm dựa trên thuật toán Tính điểm liên quan (Relevance Score)
    $relevanceParts = [];
    $params = [];
    foreach ($keywords as $kw) {
        $relevanceParts[] = "(IF(product_name LIKE ?, 3, 0) + IF(description LIKE ?, 1, 0))";
        $params[] = '%' . $kw . '%';
        $params[] = '%' . $kw . '%';
    }
    $relevanceSql = implode(' + ', $relevanceParts);
    
    $results = $db->select("
        SELECT product_name, slug, base_price, stock_quantity,
        ($relevanceSql) as relevance
        FROM products 
        WHERE is_visible = 1 
        HAVING relevance > 0
        ORDER BY relevance DESC
        LIMIT 5
    ", $params);
    
    if ($results) {
        $searchStr = implode(' ', $keywords);
        $fullPrompt .= "\n\n[HỆ THỐNG TỰ ĐỘNG TRA CỨU SẢN PHẨM: " . $searchStr . "]\nKết quả: " . json_encode($results, JSON_UNESCAPED_UNICODE) . "\nNếu có kết quả phù hợp, hãy format đẹp, kèm ảnh (dùng [PRODUCT_CARD:slug]).";
    } else {
        $fullPrompt .= "\n\n[HỆ THỐNG TỰ ĐỘNG TRA CỨU SẢN PHẨM]\nKết quả: Không tìm thấy sản phẩm.";
    }
}

if ($needOrder) {
    if (preg_match('/(ORDM-[A-Z0-9]+|ORD-[A-Z0-9]+|AX\d+|\b[A-F0-9]{8}\b)/i', $msgLower, $matches)) {
        $code = strtoupper($matches[1]);
        $order = $db->selectOne("SELECT order_status, total_amount, created_at FROM orders WHERE order_code = ?", [$code]);
        if ($order) {
            $fullPrompt .= "\n\n[HỆ THỐNG TỰ ĐỘNG TRA CỨU ĐƠN HÀNG: " . $code . "]\nKết quả: " . json_encode($order, JSON_UNESCAPED_UNICODE) . "\nHãy tóm tắt tình trạng đơn hàng cho khách.";
        } else {
            $fullPrompt .= "\n\n[HỆ THỐNG TỰ ĐỘNG TRA CỨU ĐƠN HÀNG: " . $code . "]\nKết quả: Không tìm thấy mã đơn hàng này.";
        }
    }
}

$payload = [
    "system_instruction" => [
        "parts" => [["text" => $fullPrompt]]
    ],
    "contents" => $geminiHistory
];

function callGeminiAPI($payload) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Tối ưu hóa kết nối cURL
    curl_setopt($ch, CURLOPT_ENCODING, ""); // Hỗ trợ gzip
    curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
    curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 120);
    curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [$httpCode, json_decode($response, true)];
}

// Vì đã tiêm kết quả DB vào Prompt, chỉ cần gọi 1 lần
list($httpCode, $data) = callGeminiAPI($payload);
$replyMsg = "Xin lỗi, tôi đang gặp lỗi kỹ thuật khi kết nối đến AI.";

if ($httpCode == 200 && isset($data['candidates'][0]['content']['parts'])) {
    $parts = $data['candidates'][0]['content']['parts'];
    $responseText = "";
    foreach ($parts as $part) {
        if (isset($part['text'])) {
            $responseText .= $part['text'];
        }
    }
    $replyMsg = $responseText;
} else {
    if ($httpCode == 429) {
        $replyMsg = "Hệ thống AI đang bị quá tải (Quota Exceeded). Vui lòng thử lại sau!";
    } else {
        error_log("Gemini API Error $httpCode: " . json_encode($data));
        $replyMsg = "Lỗi kết nối AI (Code: $httpCode). Chi tiết: " . ($data['error']['message'] ?? 'Unknown');
    }
}

// 5. Replace PRODUCT_CARD placeholders with HTML
$replyMsg = preg_replace_callback('/\[PRODUCT_CARD:([^\]]+)\]/', function($matches) use ($db) {
    $slug = trim($matches[1]);
    $product = $db->selectOne("
        SELECT p.product_name, p.base_price, p.slug, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE p.slug = ?
    ", [$slug]);
    
    if ($product) {
        $img = htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/100x100'));
        $url = BASE_URL . "/shop/product-detail.php?slug=" . urlencode($slug);
        $name = htmlspecialchars($product['product_name']);
        $price = number_format($product['base_price'], 0, ',', '.') . '₫';
        
        return "<a href='{$url}' class='block mt-2 bg-surface-container rounded-lg p-2 border border-outline-variant hover:border-axeron-red transition-colors no-underline' target='_blank'><div class='flex gap-3 items-center'><img src='{$img}' alt='img' class='w-12 h-12 object-cover rounded-md'><div><h4 class='font-bold text-xs text-on-surface line-clamp-1'>{$name}</h4><p class='text-axeron-red font-bold text-xs m-0'>{$price}</p></div></div></a>";
    }
    return '';
}, $replyMsg);

// 6. Lưu phản hồi vào DB
$db->query("INSERT INTO chat_messages (session_id, sender_type, content) VALUES (?, 'bot', ?)", [$session_id, $replyMsg]);

// 6. Trả về cho frontend
jsonResponse(true, 'Success', [
    'reply' => $replyMsg,
    'session_id' => $session_id
]);
