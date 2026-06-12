<?php
/**
 * Chuyển hướng người dùng sang trang xác thực Facebook
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/oauth-config.php';
require_once __DIR__ . '/../config/session.php';

// Tạo URL xác thực Facebook
$facebookOauthUrl = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
    'client_id' => FACEBOOK_CLIENT_ID,
    'redirect_uri' => FACEBOOK_REDIRECT_URI,
    'state' => bin2hex(random_bytes(16)), // CSRF Protection
    'scope' => 'email,public_profile'
]);

// Chuyển hướng
header("Location: $facebookOauthUrl");
exit;
