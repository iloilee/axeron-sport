<?php
/**
 * Cấu hình Google OAuth
 */

// Đọc Client ID & Secret từ biến môi trường (nếu có trong .env), nếu không thì điền trực tiếp
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? 'YOUR_GOOGLE_CLIENT_SECRET');

// Tự động nhận diện domain hiện tại (có www hoặc không www, http hoặc https)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = "https";
}

// FIX: Ép buộc luôn dùng https nếu chạy thực tế vì Google Cloud Console đa số chỉ cho phép https
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false) {
    $protocol = "https";
}

$domain = $_SERVER['HTTP_HOST'] ?? 'www.axeronsport.xyz';
define('GOOGLE_REDIRECT_URI', $protocol . '://' . $domain . '/auth/google-callback.php');
