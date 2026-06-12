<?php
/**
 * Nhận callback từ Facebook và trao đổi code lấy thông tin user
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/oauth-config.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/social-auth-handler.php';

// Kiểm tra xem có mã lỗi trả về không (người dùng từ chối)
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'Bạn đã hủy quá trình đăng nhập qua Facebook.';
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Gửi request tới Facebook để đổi 'code' lấy 'access_token'
    $tokenUrl = "https://graph.facebook.com/v18.0/oauth/access_token";
    $postData = [
        'client_id' => FACEBOOK_CLIENT_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'client_secret' => FACEBOOK_CLIENT_SECRET,
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl . '?' . http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenInfo = json_decode($response, true);
    
    if (isset($tokenInfo['access_token'])) {
        $accessToken = $tokenInfo['access_token'];
        
        // Dùng access_token lấy thông tin hồ sơ người dùng
        $userInfoUrl = "https://graph.facebook.com/v18.0/me?fields=id,name,email,picture.type(large)&access_token=" . $accessToken;
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        $userResponse = curl_exec($ch2);
        curl_close($ch2);
        
        $userData = json_decode($userResponse, true);
        
        // Cấu trúc lại userData cho giống Google để xài chung handleSocialLogin
        if (isset($userData['id'])) {
            $formattedData = [
                'id' => $userData['id'],
                'name' => $userData['name'] ?? 'Facebook User',
                // Facebook có thể không trả về email nếu người dùng không xác thực email hoặc không cấp quyền
                'email' => $userData['email'] ?? ($userData['id'] . '@facebook.local'),
                'picture' => $userData['picture']['data']['url'] ?? null
            ];
            
            // Xử lý tạo/đăng nhập user
            handleSocialLogin('facebook', $formattedData);
            exit;
        } else {
            $_SESSION['error'] = 'Không thể lấy thông tin từ Facebook!';
        }
    } else {
        $_SESSION['error'] = 'Lỗi xác thực Token với Facebook!';
    }
} else {
    $_SESSION['error'] = 'Lỗi đăng nhập qua Facebook!';
}

// Nếu đến được đây nghĩa là có lỗi
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
