<?php
/**
 * Chuyển hướng người dùng sang trang Đăng nhập Google
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/oauth-config.php';
require_once __DIR__ . '/../config/session.php';

// Endpoint OAuth2 của Google
$authUrl = "https://accounts.google.com/o/oauth2/v2/auth";

// Xây dựng tham số request
$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account' // Bắt buộc người dùng chọn tài khoản mỗi lần bấm
];

// Tạo URL và chuyển hướng
$url = $authUrl . '?' . http_build_query($params);
header("Location: $url");
exit;
