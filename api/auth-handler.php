<?php
/**
 * Auth Handler - Xử lý đăng nhập/đăng ký từ form POST
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$input = $_POST;
$email = sanitize($input['email'] ?? '');
$password = $input['password'] ?? '';
$action = $input['action'] ?? '';

// Xử lý LOGIN
if ($action === 'login') {
    if (empty($email) || empty($password)) {
        setFlash('error', 'Vui lòng nhập email và mật khẩu');
        axRedirect(BASE_URL . '/auth/login.php');
    }

    // Tìm user
    $user = $db->selectOne("
        SELECT u.user_id, u.full_name, u.email, u.password_hash, u.role_id, r.role_name, u.avatar_url, u.login_attempts, u.locked_until, u.is_active
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        WHERE u.email = ? OR u.phone = ?
    ", [$email, $email]);

    if (!$user) {
        setFlash('error', 'Email hoặc mật khẩu không đúng');
        axRedirect(BASE_URL . '/auth/login.php');
    }

    if ($user['is_active'] == 0) {
        setFlash('error', 'Tài khoản của bạn đã bị khóa, vui lòng liên hệ Quản Trị Viên');
        axRedirect(BASE_URL . '/auth/login.php');
    }

    // Kiểm tra khóa
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        setFlash('error', 'Tài khoản bị khóa tạm 15 phút');
        axRedirect(BASE_URL . '/auth/login.php');
    }

    // Kiểm tra mật khẩu
    if (!password_verify($password, $user['password_hash'])) {
        $attempts = $user['login_attempts'] + 1;
        if ($attempts >= 5) {
            $db->update(
                "UPDATE users SET login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE user_id = ?",
                [$attempts, $user['user_id']]
            );
            setFlash('error', 'Đăng nhập sai 5 lần. Tài khoản bị khóa 15 phút');
        } else {
            $db->update("UPDATE users SET login_attempts = ? WHERE user_id = ?", [$attempts, $user['user_id']]);
            setFlash('error', 'Email hoặc mật khẩu không đúng (' . $attempts . '/5)');
        }
        axRedirect(BASE_URL . '/auth/login.php');
    }

    // Thành công
    $db->update("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE user_id = ?", [$user['user_id']]);
    loginUser($user);

    // Xử lý "Ghi nhớ đăng nhập"
    if (!empty($input['remember_me'])) {
        setRememberCookie($user['user_id']);
    }

    // Nếu là admin hoặc staff thì chuyển đến trang admin
    if (in_array($user['role_id'], [1, 2, 4, 5, 6, 7, 8])) {
        setFlash('success', 'Chào mừng ' . $user['full_name'] . '!');
        axRedirect(BASE_URL . '/admin/admin.php');
    }

    // Merge cart
    if (!empty($_SESSION['cart'])) {
        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$user['user_id']]);
        if (!$cart) {
            $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$user['user_id']]);
            $cartId = $db->lastInsertId();
        } else {
            $cartId = $cart['cart_id'];
        }
        foreach ($_SESSION['cart'] as $item) {
            $existing = $db->selectOne(
                "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?",
                [$cartId, $item['variant_id']]
            );
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

    updateCartCount();
    setFlash('success', 'Chào mừng ' . $user['full_name'] . '!');

    // Xử lý redirect sau khi đăng nhập
    $redirect = $input['redirect'] ?? '';
    if (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) {
        axRedirect($redirect);
    }
    axRedirect(rtrim(BASE_URL, '/') . '/');
}

// Xử lý REGISTER
if ($action === 'register') {
    $fullName = sanitize($input['full_name'] ?? '');
    $phone = sanitize($input['phone'] ?? '');
    $confirmPassword = $input['confirm_password'] ?? '';

    $errors = [];
    if (empty($fullName)) $errors[] = 'Nhập họ tên';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ';
    if (empty($phone) || !preg_match('/^0[0-9]{9,10}$/', str_replace(' ', '', $phone))) $errors[] = 'Số điện thoại không hợp lệ';
    if (strlen($password) < 8) $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 chữ số';
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?`~]/', $password)) $errors[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt';
    if ($password !== $confirmPassword) $errors[] = 'Mật khẩu xác nhận không khớp';

    if ($errors) {
        setFlash('error', implode('. ', $errors));
        axRedirect(BASE_URL . '/auth/register.php');
    }

    if ($db->selectOne("SELECT user_id FROM users WHERE email = ?", [$email])) {
        setFlash('error', 'Email đã được sử dụng');
        axRedirect(BASE_URL . '/auth/register.php');
    }

    if ($db->selectOne("SELECT user_id FROM users WHERE phone = ?", [$phone])) {
        setFlash('error', 'Số điện thoại đã được sử dụng');
        axRedirect(BASE_URL . '/auth/register.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $userId = $db->insert(
        "INSERT INTO users (role_id, full_name, email, phone, password_hash, email_verified) VALUES (3, ?, ?, ?, ?, 1)",
        [$fullName, $email, $phone, $hash]
    );

    if (!$userId) {
        setFlash('error', 'Có lỗi xảy ra');
        axRedirect(BASE_URL . '/auth/register.php');
    }

    $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);

    $user = $db->selectOne(
        "SELECT u.user_id, u.full_name, u.email, u.role_id, r.role_name
         FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
        [$userId]
    );
    loginUser($user);

    // Merge cart nếu có session cart (từ trang checkout yêu cầu login)
    if (!empty($_SESSION['cart'])) {
        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);
        if ($cart) {
            foreach ($_SESSION['cart'] as $item) {
                $existing = $db->selectOne(
                    "SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?",
                    [$cart['cart_id'], $item['variant_id']]
                );
                $variant = $db->selectOne("SELECT stock_quantity FROM product_variants WHERE variant_id = ? AND is_deleted = 0", [$item['variant_id']]);
                $maxQty = $variant ? $variant['stock_quantity'] : 0;
                if ($existing) {
                    $newQty = min($existing['quantity'] + $item['quantity'], $maxQty);
                    $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$newQty, $existing['cart_item_id']]);
                } else {
                    $qty = min($item['quantity'], $maxQty);
                    if ($qty > 0) {
                        $db->insert("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)", [$cart['cart_id'], $item['variant_id'], $qty]);
                    }
                }
            }
        }
        unset($_SESSION['cart']);
    }

    updateCartCount();
    setFlash('success', 'Đăng ký thành công!');

    // Xử lý redirect sau khi đăng ký
    $redirect = $input['redirect'] ?? '';
    if (!empty($redirect) && filter_var($redirect, FILTER_VALIDATE_URL)) {
        axRedirect($redirect);
    }
    axRedirect(rtrim(BASE_URL, '/') . '/');
}

// Xử lý FORGOT PASSWORD
if ($action === 'forgot_password') {
    require_once __DIR__ . '/../config/smtp_config.php';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'Email không hợp lệ');
        axRedirect(BASE_URL . '/auth/forgot-password.php');
    }

    // Kiểm tra email có tồn tại không
    $user = $db->selectOne("SELECT user_id, full_name FROM users WHERE email = ?", [$email]);

    if (!$user) {
        // Vẫn hiển thị thành công để tránh leak thông tin email tồn tại
        setFlash('success', 'Nếu email tồn tại trong hệ thống, bạn sẽ nhận được mã xác thực qua email.');
        axRedirect(BASE_URL . '/auth/forgot-password.php');
    }

    // Xóa các yêu cầu reset cũ của user này
    $db->delete("DELETE FROM password_resets WHERE user_id = ?", [$user['user_id']]);

    // Tạo OTP và token
    $otpCode = generateOTP(OTP_LENGTH);
    $resetToken = generateResetToken();
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    // Lưu vào database
    $db->insert(
        "INSERT INTO password_resets (user_id, email, reset_token, otp_code, expires_at, ip_address) VALUES (?, ?, ?, ?, ?, ?)",
        [$user['user_id'], $email, $resetToken, $otpCode, $expiresAt, $ipAddress]
    );

    // Lưu token vào session để verify
    $_SESSION['reset_token'] = $resetToken;
    $_SESSION['reset_email'] = $email;

    // Gửi email
    $subject = 'Mã xác thực đặt lại mật khẩu - Axeron Sports';
    $body = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #BE1E2D; font-size: 24px; margin: 0;">AXERON SPORTS</h1>
        </div>
        <div style="background: #f9f9f9; border-radius: 10px; padding: 30px; text-align: center;">
            <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Yêu cầu đặt lại mật khẩu</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Xin chào ' . htmlspecialchars($user['full_name']) . ',</p>
            <p style="color: #666; font-size: 14px; margin-bottom: 30px;">Mã xác thực của bạn là:</p>
            <div style="background: #BE1E2D; color: white; font-size: 32px; font-weight: bold; padding: 20px 40px; border-radius: 8px; letter-spacing: 8px; display: inline-block;">
                ' . $otpCode . '
            </div>
            <p style="color: #999; font-size: 12px; margin-top: 30px;">Mã này sẽ hết hạn sau ' . OTP_EXPIRY_MINUTES . ' phút.<br>Vui lòng không chia sẻ mã này với bất kỳ ai.</p>
        </div>
        <div style="text-align: center; margin-top: 30px; color: #999; font-size: 12px;">
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
        </div>
    </div>';

    $emailSent = sendEmail($email, $subject, $body);

    if ($emailSent) {
        setFlash('success', 'Mã xác thực đã được gửi đến email của bạn.');
    } else {
        setFlash('error', 'Không thể gửi email. Vui lòng thử lại sau.');
    }

    axRedirect(BASE_URL . '/auth/verify-otp.php');
}

// Xử lý VERIFY OTP
if ($action === 'verify_otp') {
    $otp = sanitize($input['otp'] ?? '');
    $resetToken = sanitize($input['reset_token'] ?? '');

    if (empty($otp) || empty($resetToken)) {
        setFlash('error', 'Vui lòng nhập mã xác thực');
        axRedirect(BASE_URL . '/auth/verify-otp.php');
    }

    // Kiểm tra token và OTP
    $resetRequest = $db->selectOne(
        "SELECT * FROM password_resets WHERE reset_token = ? AND otp_code = ? AND verified_at IS NULL AND used_at IS NULL",
        [$resetToken, $otp]
    );

    if (!$resetRequest) {
        setFlash('error', 'Mã xác thực không đúng hoặc đã hết hạn');
        axRedirect(BASE_URL . '/auth/verify-otp.php');
    }

    // Kiểm tra hết hạn
    if (strtotime($resetRequest['expires_at']) < time()) {
        setFlash('error', 'Mã xác thực đã hết hạn. Vui lòng yêu cầu mã mới.');
        axRedirect(BASE_URL . '/auth/forgot-password.php');
    }

    // Đánh dấu đã xác thực
    $db->update("UPDATE password_resets SET verified_at = NOW() WHERE id = ?", [$resetRequest['id']]);

    // Lưu vào session để reset password
    $_SESSION['reset_user_id'] = $resetRequest['user_id'];
    $_SESSION['reset_verified'] = true;

    axRedirect(BASE_URL . '/auth/reset-password.php');
}

// Xử lý RESET PASSWORD
if ($action === 'reset_password') {
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? '';
    $resetToken = sanitize($input['reset_token'] ?? '');

    // Kiểm tra đã xác thực chưa
    if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_user_id'])) {
        setFlash('error', 'Phiên xác thực đã hết hạn. Vui lòng thực hiện lại.');
        axRedirect(BASE_URL . '/auth/forgot-password.php');
    }

    // Validate
    if (strlen($newPassword) < 6) {
        setFlash('error', 'Mật khẩu mới phải có ít nhất 6 ký tự');
        axRedirect(BASE_URL . '/auth/reset-password.php');
    }

    if ($newPassword !== $confirmPassword) {
        setFlash('error', 'Mật khẩu xác nhận không khớp');
        axRedirect(BASE_URL . '/auth/reset-password.php');
    }

    // Cập nhật mật khẩu
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $userId = $_SESSION['reset_user_id'];

    $db->update("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?", [$hash, $userId]);

    // Đánh dấu đã sử dụng
    $db->update("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND reset_token = ?", [$userId, $resetToken]);

    // Xóa session
    unset($_SESSION['reset_token']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_verified']);

    setFlash('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập với mật khẩu mới.');
    axRedirect(BASE_URL . '/auth/login.php');
}

// Fallback
axRedirect(BASE_URL . '/auth/login.php');
