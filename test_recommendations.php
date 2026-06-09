<?php
// test_recommendations.php - Kiểm thử hệ thống gợi ý cá nhân hóa
// Yêu cầu: PHP CLI, extension curl, server local đang chạy

$base_url = 'http://localhost/axeron-sport-website-master'; 
$cookie_file = tempnam(sys_get_temp_dir(), 'REC_TEST_COOKIE');

function curl_request($url, $post = null, $headers = []) {
    global $cookie_file;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
    // Follow redirect để nhận session cookie đúng
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body' => $response, 'code' => $http_code];
}

echo "Bắt đầu kiểm thử Hệ thống Gợi ý Cá nhân hóa...\n";
echo "Lưu ý: Bạn có thể thay đổi ID sản phẩm để phù hợp với database.\n\n";

// ---------- TEST 1: KHÁCH VÃNG LAI (GUEST) ----------
echo "===== TEST KHÁCH VÃNG LAI =====\n";

// Khởi tạo cookie mới
@unlink($cookie_file);
$cookie_file = tempnam(sys_get_temp_dir(), 'REC_TEST_GUEST');

// Khách truy cập trang chủ để tạo session ban đầu
curl_request("$base_url/");

// Mô phỏng hành vi xem sản phẩm (khách vãng lai xem giày thể thao)
// Sử dụng các ID hợp lệ trong DB (1, 2, 3...)
$guest_product_ids = [2, 3, 5]; 
echo "1. Khách đang xem các sản phẩm: " . implode(', ', $guest_product_ids) . "\n";
foreach ($guest_product_ids as $pid) {
    curl_request("$base_url/shop/product-detail.php?id=$pid");
    echo "   👁 Đã xem sản phẩm ID=$pid\n";
}

// Gọi API Recommendation
echo "2. Yêu cầu danh sách gợi ý cho khách...\n";
$guest_rec = curl_request("$base_url/api/recommendations.php", null, [
    'Accept: application/json'
]);

if ($guest_rec['code'] == 200) {
    $guest_data = json_decode($guest_rec['body'], true);
    if (isset($guest_data['success']) && $guest_data['success']) {
        echo "✅ Nhận được " . count($guest_data['data']) . " sản phẩm gợi ý.\n";
        echo "   (Nguồn dữ liệu: " . ($guest_data['source'] ?? 'unknown') . ")\n";
        foreach ($guest_data['data'] as $item) {
            echo "   - [{$item['product_id']}] {$item['product_name']} ({$item['formatted_price']})\n";
        }
    } else {
        echo "❌ API trả lỗi: " . ($guest_data['message'] ?? 'unknown') . "\n";
    }
} else {
    echo "❌ Lỗi HTTP " . $guest_rec['code'] . "\n";
}


// ---------- TEST 2: NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP ----------
echo "\n===== TEST USER ĐÃ ĐĂNG NHẬP =====\n";

// Khởi tạo cookie mới cho user đăng nhập
@unlink($cookie_file);
$cookie_file = tempnam(sys_get_temp_dir(), 'REC_TEST_USER');

// B1: Đăng nhập
echo "1. Đang đăng nhập với email: loiledelta0@gmail.com\n";
$login_res = curl_request("$base_url/api/auth-handler.php", [
    'action' => 'login',
    'email' => 'loiledelta0@gmail.com',   // Tài khoản thật
    'password' => 'Password@123'
]);

// Gọi trang chủ hoặc profile để kiểm tra session
$check_login = curl_request("$base_url/");
if (strpos($check_login['body'], 'Đăng xuất') !== false) {
    echo "✅ Đăng nhập thành công!\n";
} else {
    echo "❌ Đăng nhập thất bại. Vui lòng kiểm tra lại tài khoản test.\n";
    echo "Dừng test người dùng.\n";
    exit;
}

// B2: Mô phỏng hành vi – xem một số sản phẩm khác (VD: giày chạy bộ hoặc cầu lông)
$user_product_ids = [1, 2, 8]; 
echo "2. Người dùng xem các sản phẩm: " . implode(', ', $user_product_ids) . "\n";
foreach ($user_product_ids as $pid) {
    curl_request("$base_url/shop/product-detail.php?id=$pid");
    echo "   👁 Đã xem sản phẩm ID=$pid\n";
}

// B3: Gọi API Recommendation cho User
echo "3. Yêu cầu danh sách gợi ý cá nhân hóa...\n";
$user_rec = curl_request("$base_url/api/recommendations.php", null, [
    'Accept: application/json'
]);

if ($user_rec['code'] == 200) {
    $user_data = json_decode($user_rec['body'], true);
    if (isset($user_data['success']) && $user_data['success']) {
        echo "✅ Nhận được " . count($user_data['data']) . " sản phẩm gợi ý.\n";
        echo "   (Nguồn dữ liệu: " . ($user_data['source'] ?? 'unknown') . ")\n";
        echo "   (Trạng thái đăng nhập: " . ($user_data['is_logged_in'] ? 'Yes' : 'No') . ")\n";
        foreach ($user_data['data'] as $item) {
            echo "   - [{$item['product_id']}] {$item['product_name']} ({$item['formatted_price']})\n";
        }
    } else {
        echo "❌ API trả lỗi: " . ($user_data['message'] ?? 'unknown') . "\n";
    }
} else {
    echo "❌ Lỗi HTTP " . $user_rec['code'] . "\n";
}

// Dọn dẹp cookie file
@unlink($cookie_file);
echo "\n🏁 Test hoàn tất.\n";