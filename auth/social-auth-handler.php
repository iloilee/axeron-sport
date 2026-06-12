<?php
/**
 * Xử lý thông tin người dùng từ mạng xã hội (Google)
 * Kiểm tra xem tài khoản đã tồn tại chưa, nếu chưa thì tạo mới
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

function handleSocialLogin($provider, $userData) {
    $db = db();
    
    $email = $userData['email'];
    $googleId = $userData['id'];
    $fullName = $userData['name'] ?? 'Google User';
    $avatarUrl = $userData['picture'] ?? null;
    
    // Kiểm tra xem email đã tồn tại trong hệ thống chưa
    $existingUser = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if ($existingUser) {
        // Tài khoản bị khóa?
        if ($existingUser['is_active'] == 0) {
            $_SESSION['error'] = 'Tài khoản của bạn đã bị khóa!';
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }

        // Nếu email đã tồn tại nhưng chưa có google_id thì cập nhật để liên kết
        if (empty($existingUser['google_id'])) {
            $db->execute("UPDATE users SET google_id = ?, avatar_url = COALESCE(avatar_url, ?) WHERE user_id = ?", [$googleId, $avatarUrl, $existingUser['user_id']]);
        }
        
        // Thiết lập Session đăng nhập
        $_SESSION['user_id'] = $existingUser['user_id'];
        $_SESSION['role_id'] = $existingUser['role_id'];
        $_SESSION['full_name'] = $existingUser['full_name'];
        $_SESSION['email'] = $existingUser['email'];
        $_SESSION['avatar_url'] = $existingUser['avatar_url'] ?: $avatarUrl;
        
        $_SESSION['success'] = 'Đăng nhập thành công!';
        
        // Phân quyền chuyển hướng
        if ($existingUser['role_id'] == 1 || $existingUser['role_id'] == 2) {
            header('Location: ' . BASE_URL . '/admin/');
        } else {
            header('Location: ' . BASE_URL . '/');
        }
        exit;
        
    } else {
        // Tạo tài khoản mới
        $roleId = 3; // Role mặc định là Khách hàng
        
        $userId = $db->insert("INSERT INTO users (role_id, full_name, email, google_id, avatar_url, is_active, email_verified, password_hash) VALUES (?, ?, ?, ?, ?, 1, 1, NULL)", [
            $roleId, $fullName, $email, $googleId, $avatarUrl
        ]);
        
        if ($userId) {
            // Đăng nhập luôn bằng tài khoản mới
            $_SESSION['user_id'] = $userId;
            $_SESSION['role_id'] = $roleId;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;
            $_SESSION['avatar_url'] = $avatarUrl;
            
            $_SESSION['success'] = 'Tạo tài khoản và đăng nhập thành công!';
            header('Location: ' . BASE_URL . '/');
            exit;
        } else {
            $_SESSION['error'] = 'Không thể khởi tạo tài khoản mới!';
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
    }
}
