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
    $socialId = $userData['id'];
    $fullName = $userData['name'] ?? ucfirst($provider) . ' User';
    $avatarUrl = $userData['picture'] ?? null;
    
    // Determine the ID column based on provider
    $idColumn = ($provider === 'facebook') ? 'facebook_id' : 'google_id';

    // Kiểm tra xem email đã tồn tại trong hệ thống chưa
    $existingUser = $db->selectOne("SELECT * FROM users WHERE email = ?", [$email]);
    
    if ($existingUser) {
        // Tài khoản bị khóa?
        if ($existingUser['is_active'] == 0) {
            $_SESSION['error'] = 'Tài khoản của bạn đã bị khóa!';
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }

        // Nếu email đã tồn tại nhưng chưa liên kết với mạng xã hội này thì cập nhật để liên kết
        if (empty($existingUser[$idColumn])) {
            $db->update("UPDATE users SET {$idColumn} = ?, avatar_url = COALESCE(avatar_url, ?) WHERE user_id = ?", [$socialId, $avatarUrl, $existingUser['user_id']]);
        }
        
        // Cập nhật lại thông tin session
        $user = $db->selectOne(
            "SELECT u.user_id, u.full_name, u.email, u.avatar_url, u.role_id, r.role_name
             FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
            [$existingUser['user_id']]
        );
        loginUser($user);
        
        setFlash('success', 'Đăng nhập thành công!');
        
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
        
        $userId = $db->insert("INSERT INTO users (role_id, full_name, email, {$idColumn}, avatar_url, is_active, email_verified, password_hash) VALUES (?, ?, ?, ?, ?, 1, 1, NULL)", [
            $roleId, $fullName, $email, $socialId, $avatarUrl
        ]);
        
        if ($userId) {
            // Đăng nhập luôn bằng tài khoản mới
            $user = $db->selectOne(
                "SELECT u.user_id, u.full_name, u.email, u.avatar_url, u.role_id, r.role_name
                 FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
                [$userId]
            );
            loginUser($user);
            
            setFlash('success', 'Tạo tài khoản và đăng nhập thành công!');
            header('Location: ' . BASE_URL . '/');
            exit;
        } else {
            setFlash('error', 'Không thể khởi tạo tài khoản mới!');
            header('Location: ' . BASE_URL . '/auth/login.php');
            exit;
        }
    }
}
