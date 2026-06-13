<?php
/**
 * Auth API (AJAX) - Axeron Sports Shop
 * Chỉ xử lý request dạng JSON
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$input = json_decode(file_get_contents('php://input'), true);

// Nếu không có JSON input, reject
if ($input === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $input['action'] ?? '';

switch ($action) {
    case 'login':
        ajaxLogin($input);
        break;
    case 'register':
        ajaxRegister($input);
        break;
    case 'logout':
        logoutUser();
        echo json_encode(['success' => true, 'message' => 'Đăng xuất thành công']);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Đăng nhập AJAX
 */
function ajaxLogin($input) {
    $email = sanitize($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email và mật khẩu']);
        return;
    }

    $db = db();

    $user = $db->selectOne("
        SELECT u.*, r.role_name,
               TIMESTAMPDIFF(SECOND, NOW(), u.locked_until) as lockout_seconds
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.email = ? AND u.is_active = 1
    ", [$email]);


    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng']);
        return;
    }

    if ($user['locked_until']) {
        if ($user['lockout_seconds'] > 0) {
            $remaining = (int)$user['lockout_seconds'];
            $mins = floor($remaining / 60);
            $secs = $remaining % 60;
            $timeStr = '';
            if ($mins > 0) $timeStr .= $mins . ' phút ';
            $timeStr .= $secs . ' giây';
            echo json_encode(['success' => false, 'message' => 'Tài khoản bị khóa. Vui lòng thử lại sau ' . trim($timeStr), 'lockout_seconds' => $remaining]);
            return;
        } else {
            // Đã hết thời gian khóa, reset login_attempts
            $db->update("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE user_id = ?", [$user['user_id']]);
            $user['login_attempts'] = 0;
            $user['locked_until'] = null;
        }
    }

    if (!password_verify($password, $user['password_hash'])) {
        $attempts = $user['login_attempts'] + 1;
        if ($attempts >= 5) {
            $db->update("UPDATE users SET login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE user_id = ?", [$attempts, $user['user_id']]);
            echo json_encode(['success' => false, 'message' => 'Bạn đã nhập sai 5 lần. Tài khoản bị khóa 15 phút.', 'lockout_seconds' => 900]);
            return;
        }
        $db->update("UPDATE users SET login_attempts = ? WHERE user_id = ?", [$attempts, $user['user_id']]);
        echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng']);
        return;
    }

    $db->update("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE user_id = ?", [$user['user_id']]);

    loginUser($user);

    if (!empty($_SESSION['cart'])) {
        mergeCart($user['user_id']);
    }
    updateCartCount();

    echo json_encode([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'data' => [
            'user' => [
                'user_id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_name']
            ],
            'cart_count' => getCartCount()
        ]
    ]);
}

/**
 * Đăng ký AJAX
 */
function ajaxRegister($input) {
    $fullName = sanitize($input['full_name'] ?? '');
    $email = sanitize($input['email'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $password = $input['password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';

    $errors = [];

    if (empty(trim($fullName))) {
        $errors[] = 'Vui lòng nhập họ tên';
    } elseif (mb_strlen(trim($fullName)) < 2 || mb_strlen(trim($fullName)) > 100) {
        $errors[] = 'Họ tên phải từ 2 đến 100 ký tự';
    } elseif (preg_match('/\d/', $fullName)) {
        $errors[] = 'Họ tên không được chứa số';
    } elseif (!preg_match("/^[\p{L}\s'-]+$/u", $fullName)) {
        $errors[] = 'Họ tên chứa ký tự không hợp lệ';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';

    $phoneClean = str_replace(' ', '', $phone);
    if (empty($phone) || !preg_match('/^0[0-9]{9,10}$/', $phoneClean)) $errors[] = 'Số điện thoại không hợp lệ';
    if (strlen($password) < 8) $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 chữ số';
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?`~]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
    if ($password !== $confirmPassword) $errors[] = 'Mật khẩu xác nhận không khớp';

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
        return;
    }

    $db = db();

    if ($db->selectOne("SELECT user_id FROM users WHERE email = ?", [$email])) {
        echo json_encode(['success' => false, 'message' => 'Email đã được sử dụng']);
        return;
    }

    if ($db->selectOne("SELECT user_id FROM users WHERE phone = ?", [$phone])) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại đã được sử dụng']);
        return;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $userId = $db->insert("
        INSERT INTO users (role_id, full_name, email, phone, password_hash, email_verified, created_at)
        VALUES (3, ?, ?, ?, ?, 1, NOW())
    ", [$fullName, $email, $phone, $passwordHash]);

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        return;
    }

    $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);

    if (!empty($_SESSION['cart'])) {
        mergeCart($userId);
    }

    $user = $db->selectOne("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?", [$userId]);
    loginUser($user);
    updateCartCount();

    echo json_encode([
        'success' => true,
        'message' => 'Đăng ký thành công',
        'data' => [
            'user' => [
                'user_id' => $user['user_id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_name']
            ],
            'cart_count' => getCartCount()
        ]
    ]);
}

/**
 * Merge giỏ hàng session vào database
 */
function mergeCart($userId) {
    $db = db();

    $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

    if (!$cart) {
        $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);
        $cartId = $db->lastInsertId();
    } else {
        $cartId = $cart['cart_id'];
    }

    foreach ($_SESSION['cart'] as $item) {
        $existing = $db->selectOne("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?", [$cartId, $item['variant_id']]);
        $variant = $db->selectOne("SELECT stock_quantity FROM product_variants WHERE variant_id = ? AND is_deleted = 0", [$item['variant_id']]);
        $maxQty = $variant ? $variant['stock_quantity'] : 0;

        if ($existing) {
            $newQty = min($existing['quantity'] + $item['quantity'], $maxQty);
            $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$newQty, $existing['cart_item_id']]);
        } else {
            $qty = min($item['quantity'], $maxQty);
            if ($qty > 0) {
                $db->insert("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)", [$cartId, $item['variant_id'], $qty]);
            }
        }
    }

    unset($_SESSION['cart']);
}
