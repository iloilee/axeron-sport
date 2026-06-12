<?php
/**
 * Nhận callback từ Google và trao đổi code lấy thông tin user
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/oauth-config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/social-auth-handler.php';

// Kiểm tra xem có mã lỗi trả về không
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Bạn đã hủy quá trình đăng nhập.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Gửi request tới Google để đổi 'code' lấy 'access_token'
    $tokenUrl = "https://oauth2.googleapis.com/token";
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Tránh lỗi SSL nội bộ nếu có
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $tokenInfo = json_decode($response, true);
    
    if (isset($tokenInfo['access_token'])) {
        $accessToken = $tokenInfo['access_token'];
        
        // Dùng access_token lấy thông tin hồ sơ người dùng
        $userInfoUrl = "https://www.googleapis.com/oauth2/v2/userinfo";
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $userResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $userData = json_decode($userResponse, true);
        
        // Kiểm tra xem có lấy được ID và Email không
        if (isset($userData['id']) && isset($userData['email'])) {
            // Xử lý tạo/đăng nhập user
            handleSocialLogin('google', $userData);
            exit;
        } else {
            $_SESSION['error'] = 'Không thể lấy thông tin từ Google!';
        }
    } else {
        $_SESSION['error'] = 'Lỗi xác thực Token với Google!';
    }
} else {
    $_SESSION['error'] = 'Lỗi đăng nhập qua Google!';
}

// Nếu đến được đây nghĩa là có lỗi
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
