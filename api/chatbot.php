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

// 3. Chuẩn bị Context từ Database
$context = [];
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

$payload = [
    "system_instruction" => [
        "parts" => [["text" => $fullPrompt]]
    ],
    "tools" => [
        [
            "function_declarations" => [
                [
                    "name" => "search_products",
                    "description" => "Tìm kiếm sản phẩm trong database theo tên, loại hoặc thương hiệu.",
                    "parameters" => [
                        "type" => "OBJECT",
                        "properties" => [
                            "keyword" => [
                                "type" => "STRING",
                                "description" => "Từ khóa tìm kiếm (ví dụ: 'giày nike', 'áo thun')"
                            ]
                        ],
                        "required" => ["keyword"]
                    ]
                ],
                [
                    "name" => "check_order",
                    "description" => "Kiểm tra tình trạng đơn hàng bằng mã đơn hàng.",
                    "parameters" => [
                        "type" => "OBJECT",
                        "properties" => [
                            "order_code" => [
                                "type" => "STRING",
                                "description" => "Mã đơn hàng (ví dụ: AX12345)"
                            ]
                        ],
                        "required" => ["order_code"]
                    ]
                ]
            ]
        ]
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
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [$httpCode, json_decode($response, true)];
}

// Lặp tối đa 3 lần để xử lý function calling
$max_turns = 3;
$replyMsg = "Xin lỗi, tôi đang gặp lỗi kỹ thuật khi kết nối đến AI.";

for ($i = 0; $i < $max_turns; $i++) {
    list($httpCode, $data) = callGeminiAPI($payload);
    
    if ($httpCode == 200 && isset($data['candidates'][0]['content']['parts'])) {
        $parts = $data['candidates'][0]['content']['parts'];
        $functionCall = null;
        $responseText = "";
        
        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                $functionCall = $part['functionCall'];
            }
            if (isset($part['text'])) {
                $responseText .= $part['text'];
            }
        }
        
        if ($functionCall) {
            $funcName = $functionCall['name'];
            $args = $functionCall['args'] ?? [];
            $funcResponse = [];
            
            // Xử lý logic nội bộ
            if ($funcName === 'search_products') {
                $kw = '%' . ($args['keyword'] ?? '') . '%';
                $results = $db->select("
                    SELECT product_name, slug, base_price, stock_quantity 
                    FROM products 
                    WHERE is_visible = 1 AND (product_name LIKE ? OR description LIKE ?)
                    LIMIT 3
                ", [$kw, $kw]);
                
                if ($results) {
                    $funcResponse = ["status" => "success", "products" => $results];
                } else {
                    $funcResponse = ["status" => "not_found", "message" => "Không tìm thấy sản phẩm nào phù hợp."];
                }
            } elseif ($funcName === 'check_order') {
                $code = $args['order_code'] ?? '';
                $order = $db->selectOne("SELECT order_status, total_amount, created_at FROM orders WHERE order_code = ?", [$code]);
                if ($order) {
                    $funcResponse = ["status" => "success", "order" => $order];
                } else {
                    $funcResponse = ["status" => "not_found", "message" => "Không tìm thấy mã đơn hàng này."];
                }
            }
            
            // Thêm phản hồi của function vào lịch sử
            $payload['contents'][] = [
                "role" => "model",
                "parts" => $parts
            ];
            $payload['contents'][] = [
                "role" => "function",
                "parts" => [
                    [
                        "functionResponse" => [
                            "name" => $funcName,
                            "response" => ["name" => $funcName, "content" => $funcResponse]
                        ]
                    ]
                ]
            ];
            
            continue; // Gọi lại Gemini với kết quả
        } else {
            // Không có function call, trả về text
            $replyMsg = $responseText;
            break;
        }
    } else {
        if ($httpCode == 429) {
            $replyMsg = "Hệ thống AI đang bị quá tải (Quota Exceeded). Vui lòng thử lại sau!";
        } else {
            error_log("Gemini API Error $httpCode: " . json_encode($data));
            $replyMsg = "Lỗi kết nối AI (Code: $httpCode). Chi tiết: " . ($data['error']['message'] ?? 'Unknown');
        }
        break;
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
