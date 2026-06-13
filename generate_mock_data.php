<?php
/**
 * Axeron Sport - Sinh dữ liệu mẫu (Mock Data Generator)
 * Chạy script này từ terminal hoặc browser để tạo thêm dữ liệu
 */

require_once __DIR__ . '/config/database.php';
$db = Database::getInstance();

// Cấu hình thời gian
$startDate = strtotime('2025-01-01 00:00:00');
$endDate = strtotime('2026-06-13 23:59:59');

function randomDate($start, $end) {
    return date('Y-m-d H:i:s', mt_rand($start, $end));
}

echo "Bắt đầu tạo dữ liệu...\n";

// 1. Tạo Khách Hàng (Users)
$numUsers = 10;
$userRole = 3; // Customer
$newUsers = [];

for ($i = 1; $i <= $numUsers; $i++) {
    $email = "khachhang_{$i}_" . time() . "@example.com";
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    $name = "Khách hàng số {$i}";
    $phone = "09" . mt_rand(10000000, 99999999);
    $created_at = randomDate($startDate, $endDate);
    
    $userId = $db->insert("INSERT INTO users (email, password_hash, full_name, phone, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)", 
        [$email, $pass, $name, $phone, $userRole, $created_at]);
        
    $newUsers[] = $userId;
}

echo "✅ Đã tạo $numUsers khách hàng mới.\n";

// Lấy danh sách sản phẩm để tạo đơn hàng
$variants = $db->select("SELECT v.variant_id, (p.base_price + v.extra_price) as price, p.product_id, p.product_name, v.color, v.size FROM product_variants v JOIN products p ON v.product_id = p.product_id");
if (empty($variants)) {
    die("Lỗi: Không tìm thấy sản phẩm nào trong database để tạo đơn hàng. Vui lòng thêm sản phẩm trước.\n");
}

// 2. Tạo Đơn Hàng (Orders)
$numOrders = 40; // 280 đơn hàng trải dài 12 tháng
// Trọng số trạng thái đơn hàng (nhiều đơn delivered hơn)
$statuses = ['delivered', 'delivered', 'delivered', 'delivered', 'delivered', 'pending', 'processing', 'shipped', 'cancelled', 'cancelled'];
$paymentStatuses = ['paid', 'pending', 'failed'];

for ($i = 1; $i <= $numOrders; $i++) {
    // Chọn ngẫu nhiên 1 user từ danh sách user vừa tạo, hoặc cả user cũ
    $userId = $newUsers[array_rand($newUsers)];
    $orderCode = strtoupper(substr(md5(uniqid()), 0, 8));
    
    $status = $statuses[array_rand($statuses)];
    // Logic payment status: giao thành công thì chắc chắn paid, hủy thì failed
    $paymentStatus = ($status === 'delivered') ? 'paid' : $paymentStatuses[array_rand($paymentStatuses)];
    if ($status === 'cancelled') $paymentStatus = 'failed';
    
    $createdAt = randomDate($startDate, $endDate);
    
    // Tạo record đơn hàng với tổng tiền = 0
    $orderId = $db->insert("INSERT INTO orders (order_code, user_id, total_amount, order_status, payment_status, shipping_address, created_at) VALUES (?, ?, 0, ?, ?, '123 Đường Axeron, TP.HCM', ?)",
        [$orderCode, $userId, $status, $paymentStatus, $createdAt]);
        
    // Tạo ngẫu nhiên 1 - 3 sản phẩm cho đơn hàng
    $numItems = mt_rand(1, 3);
    $totalAmount = 0;
    
    for ($j = 0; $j < $numItems; $j++) {
        // Cố tình tạo thiên vị cho vài sản phẩm đầu tiên để chúng trở thành "Sản phẩm bán chạy"
        $isTopSelling = (mt_rand(1, 100) <= 40); // 40% cơ hội rơi vào 5 sản phẩm đầu
        if ($isTopSelling && count($variants) > 5) {
            $variantIndex = mt_rand(0, 4);
        } else {
            $variantIndex = array_rand($variants);
        }
        
        $variant = $variants[$variantIndex];
        $quantity = mt_rand(1, 3);
        $price = $variant['price'];
        $subtotal = $quantity * $price;
        $totalAmount += $subtotal;
        
        $variantInfo = trim(($variant['color'] ?? '') . ' ' . ($variant['size'] ?? ''));
        if (empty($variantInfo)) $variantInfo = 'Mặc định';
        
        $db->insert("INSERT INTO order_items (order_id, variant_id, product_name, variant_info, unit_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$orderId, $variant['variant_id'], $variant['product_name'], $variantInfo, $price, $quantity, $subtotal]);
    }
    
    // Cập nhật lại tổng tiền thực tế
    $db->update("UPDATE orders SET total_amount = ? WHERE order_id = ?", [$totalAmount, $orderId]);
}

echo "✅ Đã tạo $numOrders đơn hàng.\n";

// 3. Tạo Dữ liệu hành vi (Search Logs & Product Views)
$keywords = ['giày bóng đá', 'áo cầu lông', 'vợt yonex', 'tạ tay', 'găng tay thủ môn', 'bóng rổ', 'đệm gối', 'quần đùi thể thao', 'giày chạy bộ', 'dây nhảy'];
$numSearches = 80;

for ($i = 0; $i < $numSearches; $i++) {
    $userId = $newUsers[array_rand($newUsers)];
    $kw = $keywords[array_rand($keywords)];
    $searchedAt = randomDate($startDate, $endDate);
    $db->insert("INSERT INTO search_logs (user_id, keyword, result_count, searched_at) VALUES (?, ?, ?, ?)",
        [$userId, $kw, mt_rand(0, 50), $searchedAt]);
}

$numViews = 180;
for ($i = 0; $i < $numViews; $i++) {
    $userId = $newUsers[array_rand($newUsers)];
    $variant = $variants[array_rand($variants)];
    $viewedAt = randomDate($startDate, $endDate);
    $db->insert("INSERT INTO product_view_logs (user_id, product_id, viewed_at) VALUES (?, ?, ?)",
        [$userId, $variant['product_id'], $viewedAt]);
}

echo "✅ Đã tạo dữ liệu $numSearches lịch sử tìm kiếm và $numViews lượt xem.\n";
echo "🎉 Hoàn tất! Hãy quay lại trang thống kê để xem kết quả.\n";
