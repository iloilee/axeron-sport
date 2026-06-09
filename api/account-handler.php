<?php
/**
 * Account Handler - Xử lý cập nhật thông tin người dùng và upload Avatar
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Yêu cầu đăng nhập
requireLogin();

$db = db();
$userId = getUserId();
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    
    // Address info
    $province = sanitize($_POST['province'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $ward = sanitize($_POST['ward'] ?? '');
    $streetAddress = sanitize($_POST['street_address'] ?? '');

    // Validate thông tin cơ bản
    if (empty($fullName)) {
        setFlash('error', 'Vui lòng nhập họ và tên');
        axRedirect(BASE_URL . '/auth/account.php');
    }

    if (!empty($dateOfBirth)) {
        // Kiểm tra định dạng YYYY-MM-DD (trình duyệt gửi lên từ input type date)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOfBirth)) {
            $dateOfBirth = null;
        }
    } else {
        $dateOfBirth = null;
    }

    if (!in_array($gender, ['male', 'female', 'other'])) {
        $gender = null;
    }

    // Xử lý Upload Avatar
    $avatarUrl = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileType = $_FILES['avatar']['type'];
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileExt, $allowedExts)) {
            if ($fileSize < 2 * 1024 * 1024) { // Tối đa 2MB
                $uploadDir = __DIR__ . '/../assets/uploads/avatars/';
                // Tạo thư mục nếu chưa có
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Lưu đường dẫn tương đối để dễ dùng qua CDN/Cloudflare
                    $avatarUrl = '/assets/uploads/avatars/' . $newFileName;
                } else {
                    setFlash('error', 'Đã xảy ra lỗi khi lưu ảnh đại diện.');
                    axRedirect(BASE_URL . '/auth/account.php');
                }
            } else {
                setFlash('error', 'Ảnh đại diện không được vượt quá 2MB.');
                axRedirect(BASE_URL . '/auth/account.php');
            }
        } else {
            setFlash('error', 'Chỉ hỗ trợ file ảnh định dạng JPG, JPEG, PNG, WEBP.');
            axRedirect(BASE_URL . '/auth/account.php');
        }
    }

    // Cập nhật users table
    try {
        $db->beginTransaction();
        
        // Cập nhật thông tin cơ bản
        $updateQuery = "UPDATE users SET full_name = ?, phone = ?, updated_at = NOW()";
        $updateParams = [$fullName, $phone];
        
        if ($dateOfBirth !== null) {
            $updateQuery .= ", date_of_birth = ?";
            $updateParams[] = $dateOfBirth;
        }
        
        if ($gender !== null) {
            $updateQuery .= ", gender = ?";
            $updateParams[] = $gender;
        }
        
        if ($avatarUrl !== null) {
            $updateQuery .= ", avatar_url = ?";
            $updateParams[] = $avatarUrl;
        }
        
        $updateQuery .= " WHERE user_id = ?";
        $updateParams[] = $userId;
        
        $db->update($updateQuery, $updateParams);

        // Xử lý Địa chỉ giao hàng mặc định (nếu có nhập)
        if (!empty($province) && !empty($streetAddress)) {
            // Kiểm tra xem user đã có địa chỉ mặc định chưa
            $existingAddress = $db->selectOne("SELECT address_id FROM user_addresses WHERE user_id = ? AND is_default = 1", [$userId]);
            
            if ($existingAddress) {
                // Cập nhật địa chỉ mặc định
                $db->update(
                    "UPDATE user_addresses SET recipient_name = ?, phone = ?, province = ?, district = ?, ward = ?, street_address = ? WHERE address_id = ?",
                    [$fullName, $phone, $province, $district, $ward, $streetAddress, $existingAddress['address_id']]
                );
            } else {
                // Thêm địa chỉ mới và set làm mặc định
                // Xóa cờ mặc định của các địa chỉ khác (đề phòng)
                $db->update("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
                
                $db->insert(
                    "INSERT INTO user_addresses (user_id, recipient_name, phone, province, district, ward, street_address, is_default, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())",
                    [$userId, $fullName, $phone, $province, $district, $ward, $streetAddress]
                );
            }
        }
        
        $db->commit();
        
        // Cập nhật lại session user data
        $user = $db->selectOne(
            "SELECT u.user_id, u.full_name, u.email, u.avatar_url, u.role_id, r.role_name
             FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
            [$userId]
        );
        loginUser($user); // Re-login to refresh session data
        
        setFlash('success', 'Cập nhật thông tin thành công!');
    } catch (Exception $e) {
        $db->rollback();
        error_log('Update Profile Error: ' . $e->getMessage());
        setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại sau.');
    }
    
    axRedirect(BASE_URL . '/auth/account.php');
}

// Fallback
axRedirect(BASE_URL . '/auth/account.php');
