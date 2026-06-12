<?php
/**
 * Session Configuration - Axeron Sports Shop
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
              (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
              
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Đảm bảo Database class luôn sẵn sàng (cần cho remember cookie)
require_once __DIR__ . '/database.php';

// Định nghĩa hằng số cho remember me
if (!defined('REMEMBER_COOKIE_NAME')) define('REMEMBER_COOKIE_NAME', 'axeron_remember');
if (!defined('REMEMBER_COOKIE_DAYS')) define('REMEMBER_COOKIE_DAYS', 30); // Ghi nhớ 30 ngày

// Define BASE_URL
$protocol = 'http://';
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    $protocol = 'https://';
}

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Tự động nhận diện thư mục nếu chạy ở localhost, hoặc gỡ bỏ nếu chạy trên domain chính
if ($host === 'localhost' || $host === '127.0.0.1') {
    $projectDir = '/' . basename(dirname(__DIR__));
} else {
    $projectDir = '';
    // Ép kiểu https cho domain thật để tránh lỗi Mixed Content (ảnh không tải được do http)
    $protocol = 'https://';
}

define('BASE_URL', rtrim($protocol . $host . $projectDir, '/'));
define('SITE_NAME', 'Axeron Sport');

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Auth helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserData() {
    if (!isLoggedIn()) return null;
    return [
        'user_id' => $_SESSION['user_id'],
        'full_name' => $_SESSION['full_name'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'role' => $_SESSION['role'] ?? 'customer',
        'avatar_url' => $_SESSION['avatar_url'] ?? ''
    ];
}

function loginUser($user) {
    session_regenerate_id(true); // Ngăn chặn Session Fixation (OWASP A07)
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role_name'] ?? 'customer';
    $_SESSION['avatar_url'] = $user['avatar_url'] ?? '';
    $_SESSION['login_time'] = time();
}

function logoutUser() {
    // Xoá remember token trong DB và cookie
    clearRememberCookie();
    unset($_SESSION['user_id']);
    unset($_SESSION['full_name']);
    unset($_SESSION['email']);
    unset($_SESSION['role']);
    unset($_SESSION['avatar_url']);
    session_destroy();
}

/**
 * Tạo cookie "Ghi nhớ đăng nhập"
 * Lưu token hash vào DB, gửi token gốc qua cookie
 */
function setRememberCookie($userId) {
    $token = bin2hex(random_bytes(32)); // Token ngẫu nhiên 64 ký tự
    $tokenHash = password_hash($token, PASSWORD_DEFAULT);

    // Lưu hash vào DB
    $db = Database::getInstance();
    $db->update("UPDATE users SET remember_token = ? WHERE user_id = ?", [$tokenHash, $userId]);

    // Gửi cookie chứa userId:token
    $cookieValue = $userId . ':' . $token;
    $expiry = time() + (REMEMBER_COOKIE_DAYS * 24 * 60 * 60);
    $path = '/'; // Cookie áp dụng cho toàn site
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    setcookie(REMEMBER_COOKIE_NAME, $cookieValue, [
        'expires' => $expiry,
        'path' => $path,
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Xoá cookie "Ghi nhớ đăng nhập" và token trong DB
 */
function clearRememberCookie() {
    if (isset($_COOKIE[REMEMBER_COOKIE_NAME])) {
        // Xoá token trong DB
        $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
        if (count($parts) === 2) {
            $userId = (int) $parts[0];
            if ($userId > 0) {
                try {
                    $db = Database::getInstance();
                    $db->update("UPDATE users SET remember_token = NULL WHERE user_id = ?", [$userId]);
                } catch (Exception $e) {
                    // Bỏ qua lỗi DB khi logout
                }
            }
        }

        // Xoá cookie
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(REMEMBER_COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[REMEMBER_COOKIE_NAME]);
    }
}

/**
 * Kiểm tra cookie "Ghi nhớ đăng nhập" và tự động đăng nhập
 * Gọi hàm này ở đầu mỗi trang sau session_start()
 */
function checkRememberCookie() {
    // Nếu đã đăng nhập rồi thì không cần kiểm tra
    if (isLoggedIn()) return;

    // Kiểm tra cookie có tồn tại không
    if (!isset($_COOKIE[REMEMBER_COOKIE_NAME])) return;

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
    if (count($parts) !== 2) {
        clearRememberCookie();
        return;
    }

    $userId = (int) $parts[0];
    $token = $parts[1];

    if ($userId <= 0 || empty($token)) {
        clearRememberCookie();
        return;
    }

    try {
        $db = Database::getInstance();
        $user = $db->selectOne("
            SELECT u.user_id, u.full_name, u.email, u.remember_token, u.role_id, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.user_id = ? AND u.is_active = 1 AND u.remember_token IS NOT NULL
        ", [$userId]);

        if (!$user || !password_verify($token, $user['remember_token'])) {
            clearRememberCookie();
            return;
        }

        // Token hợp lệ → tự động đăng nhập
        loginUser($user);

        // Tạo token mới (token rotation để tăng bảo mật)
        setRememberCookie($userId);

    } catch (Exception $e) {
        clearRememberCookie();
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Vui lòng đăng nhập');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

function isAdmin() {
    $role = $_SESSION['role'] ?? '';
    return in_array($role, ['admin', 'staff', 'staff_accounts', 'staff_products', 'staff_orders', 'staff_analytics', 'staff_cms']);
}

function hasPermission($action) {
    if (!isLoggedIn()) return false;
    $role = $_SESSION['role'] ?? 'customer';
    
    // admin has all permissions
    if ($role === 'admin') return true;
    
    $permissions = [
        'staff_accounts' => ['users', 'reviews'],
        'staff_products' => ['products', 'categories', 'brands', 'promotions'],
        'staff_orders' => ['orders', 'shipping_price'],
        'staff_analytics' => ['dashboard', 'analytics'],
        'staff_cms' => ['banners', 'articles', 'featured', 'settings']
    ];
    
    $allowedActions = $permissions[$role] ?? [];
    return in_array($action, $allowedActions);
}

// Cart helpers
function getCartCount() {
    return $_SESSION['cart_count'] ?? 0;
}

function setCartCount($count) {
    $_SESSION['cart_count'] = $count;
}

function updateCartCount() {
    if (isLoggedIn()) {
        require_once __DIR__ . '/database.php';
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $result = $db->selectOne("SELECT COALESCE(SUM(ci.quantity), 0) as total FROM cart_items ci JOIN carts c ON ci.cart_id = c.cart_id WHERE c.user_id = ?", [$userId]);
        setCartCount((int)$result['total']);
    } else {
        $cart = $_SESSION['cart'] ?? [];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['quantity'] ?? 0;
        }
        setCartCount($total);
    }
}

// Redirect helper
function axRedirect($url) {
    // Loại bỏ / thừa
    $url = rtrim($url, '/');
    header('Location: ' . $url);
    exit;
}

// --- Guest Product View Tracking (hỗ trợ Recommendation Engine) ---

/**
 * Lưu lịch sử xem sản phẩm cho khách vãng lai (chưa đăng nhập)
 * Giới hạn 20 sản phẩm gần nhất, không trùng lặp
 * @param int $productId
 */
function trackGuestProductView(int $productId): void
{
    if (!isset($_SESSION['guest_view_logs'])) {
        $_SESSION['guest_view_logs'] = [];
    }

    // Xóa nếu đã có (để đẩy lên đầu danh sách - gần nhất)
    $key = array_search($productId, $_SESSION['guest_view_logs']);
    if ($key !== false) {
        unset($_SESSION['guest_view_logs'][$key]);
    }

    // Thêm vào đầu danh sách
    array_unshift($_SESSION['guest_view_logs'], $productId);

    // Giới hạn 20 sản phẩm gần nhất
    $_SESSION['guest_view_logs'] = array_slice($_SESSION['guest_view_logs'], 0, 20);
}

/**
 * Lấy danh sách product_id mà khách vãng lai đã xem
 * @return array Mảng product_id
 */
function getGuestViewLogs(): array
{
    return $_SESSION['guest_view_logs'] ?? [];
}

// Tự động kiểm tra cookie "Ghi nhớ đăng nhập" khi load trang
checkRememberCookie();

/**
 * Kiểm tra trạng thái tài khoản đang đăng nhập
 * Nếu bị khóa (is_active = 0) hoặc bị xóa khỏi DB -> Đăng xuất ngay lập tức
 */
function checkAccountStatus() {
    if (!isLoggedIn()) return;
    
    try {
        $db = Database::getInstance();
        $userId = getUserId();
        
        $user = $db->selectOne("SELECT is_active FROM users WHERE user_id = ?", [$userId]);
        
        if (!$user || $user['is_active'] == 0) {
            logoutUser();
            setFlash('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
            
            // Bỏ qua redirect nếu đang ở trang auth hoặc request AJAX (để frontend tự handle nếu là API JSON)
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                      || isset($_POST['ajax_action']) || strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
                      
            if (!$isAjax && strpos($_SERVER['REQUEST_URI'], '/auth/') === false) {
                header('Location: ' . BASE_URL . '/auth/login.php');
                exit;
            }
        }
    } catch (Exception $e) {
        // Bỏ qua
    }
}

// Kiểm tra trạng thái tài khoản đang đăng nhập (hoạt động/khóa)
checkAccountStatus();

/**
 * CSRF Protection - Tạo token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF Protection - Xác thực token
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
