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
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Yêu cầu không hợp lệ. Vui lòng thử lại.');
        axRedirect(BASE_URL . '/auth/account.php');
    }

    $fullName = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    
    // Address info
    $province = sanitize($_POST['province'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $ward = ''; // Đã gộp vào trường địa chỉ
    $streetAddress = sanitize($_POST['street_address'] ?? '');

    // Validate thông tin cơ bản
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

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($phone) || !preg_match('/^0[0-9]{9,10}$/', str_replace(' ', '', $phone))) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }

    // Unique checks
    if ($db->selectOne("SELECT user_id FROM users WHERE email = ? AND user_id != ?", [$email, $userId])) {
        $errors[] = 'Email đã được sử dụng bởi tài khoản khác';
    }

    if ($db->selectOne("SELECT user_id FROM users WHERE phone = ? AND user_id != ?", [$phone, $userId])) {
        $errors[] = 'Số điện thoại đã được sử dụng bởi tài khoản khác';
    }

    if ($errors) {
        setFlash('error', implode('. ', $errors));
        axRedirect(BASE_URL . '/auth/account.php');
    }

    $currentUser = $db->selectOne("SELECT email FROM users WHERE user_id = ?", [$userId]);
    $currentEmail = $currentUser['email'];
    $isEmailChanged = ($email !== $currentEmail);

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
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);
        
        if (in_array($fileExt, $allowedExts) && in_array($mime, $allowedMimes)) {
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
        if ($isEmailChanged) {
            $updateQuery = "UPDATE users SET full_name = ?, phone = ?, updated_at = NOW()";
            $updateParams = [$fullName, $phone];
        } else {
            $updateQuery = "UPDATE users SET full_name = ?, email = ?, phone = ?, updated_at = NOW()";
            $updateParams = [$fullName, $email, $phone];
        }
        
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
        
        if ($isEmailChanged) {
            require_once __DIR__ . '/../config/smtp_config.php';
            $otpCode = sprintf("%06d", mt_rand(1, 999999));
            
            $_SESSION['email_change_otp'] = $otpCode;
            $_SESSION['email_change_new_email'] = $email;
            $_SESSION['show_email_otp_modal'] = true;

            $subject = 'Mã xác thực đổi email - Axeron Sports';
            $body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="' . BASE_URL . '/assets/images/logo-axeron.jpg" alt="Axeron Sports" style="max-height: 40px; margin: 0 auto; display: block;">
                </div>
                <div style="background: #f9f9f9; border-radius: 10px; padding: 30px; text-align: center;">
                    <h2 style="color: #333; font-size: 20px; margin-bottom: 20px;">Xác thực thay đổi Email</h2>
                    <p style="color: #666; font-size: 14px; margin-bottom: 20px;">Bạn đang yêu cầu đổi email tài khoản. Mã xác thực của bạn là:</p>
                    <div style="background: #BE1E2D; color: white; font-size: 32px; font-weight: bold; padding: 20px 40px; border-radius: 8px; letter-spacing: 8px; display: inline-block;">
                        ' . $otpCode . '
                    </div>
                    <p style="color: #999; font-size: 12px; margin-top: 30px;">Vui lòng không chia sẻ mã này với bất kỳ ai.</p>
                </div>
            </div>';

            sendEmail($email, $subject, $body);

            // Gửi email cảnh báo bảo mật tới email cũ
            $oldEmailSubject = 'Cảnh báo bảo mật: Yêu cầu thay đổi Email - Axeron Sports';
            $oldEmailBody = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <img src="' . BASE_URL . '/assets/images/logo-axeron.jpg" alt="Axeron Sports" style="max-height: 40px; margin: 0 auto; display: block;">
                </div>
                <div style="background: #fff3f3; border: 1px solid #ffcdd2; border-radius: 10px; padding: 30px; text-align: center;">
                    <h2 style="color: #d32f2f; font-size: 20px; margin-bottom: 20px;">Cảnh Báo Bảo Mật</h2>
                    <p style="color: #333; font-size: 14px; margin-bottom: 20px;">Tài khoản của bạn đang yêu cầu đổi email sang <strong>' . htmlspecialchars($email) . '</strong>.</p>
                    <p style="color: #666; font-size: 14px; margin-bottom: 10px;">Nếu bạn là người thực hiện, vui lòng kiểm tra hộp thư của email mới để lấy mã xác thực.</p>
                    <p style="color: #d32f2f; font-size: 14px; font-weight: bold;">Nếu không phải bạn thực hiện, vui lòng liên hệ bộ phận hỗ trợ ngay lập tức để bảo vệ tài khoản.</p>
                </div>
            </div>';
            sendEmail($currentEmail, $oldEmailSubject, $oldEmailBody);

        } else {
            setFlash('success', 'Cập nhật thông tin thành công!');
        }
    } catch (Exception $e) {
        $db->rollback();
        error_log('Update Profile Error: ' . $e->getMessage());
        setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại sau.');
    }
    
    axRedirect(BASE_URL . '/auth/account.php');
}


if ($action === 'verify_email_otp') {
    $otp = $_POST['otp'] ?? '';
    if (isset($_SESSION['email_change_otp']) && $_SESSION['email_change_otp'] === $otp) {
        $newEmail = $_SESSION['email_change_new_email'];
        
        $db->update("UPDATE users SET email = ?, updated_at = NOW() WHERE user_id = ?", [$newEmail, $userId]);
        
        // Refresh session
        $user = $db->selectOne(
            "SELECT u.user_id, u.full_name, u.email, u.avatar_url, u.role_id, r.role_name
             FROM users u JOIN roles r ON u.role_id = r.role_id WHERE u.user_id = ?",
            [$userId]
        );
        loginUser($user);
        
        unset($_SESSION['email_change_otp'], $_SESSION['email_change_new_email'], $_SESSION['show_email_otp_modal']);
        setFlash('success', 'Đổi email thành công!');
        axRedirect(BASE_URL . '/auth/account.php');
    } else {
        $_SESSION['show_email_otp_modal'] = true; // Keep modal open
        setFlash('error', 'Mã xác thực không chính xác.');
        axRedirect(BASE_URL . '/auth/account.php');
    }
}

if ($action === 'cancel_email_change') {
    unset($_SESSION['email_change_otp'], $_SESSION['email_change_new_email'], $_SESSION['show_email_otp_modal']);
    axRedirect(BASE_URL . '/auth/account.php');
}

if ($action === 'delete_account') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Yêu cầu không hợp lệ. Vui lòng thử lại.');
        axRedirect(BASE_URL . '/auth/account.php');
    }

    try {
        $db->beginTransaction();

        $deletedEmail = 'deleted_' . $userId . '_' . time() . '@deleted.local';
        
        $db->update("
            UPDATE users 
            SET is_deleted = 1, 
                is_active = 0, 
                full_name = 'Deleted User', 
                email = ?, 
                phone = NULL,
                password_hash = NULL,
                google_id = NULL,
                avatar_url = NULL,
                updated_at = NOW() 
            WHERE user_id = ?
        ", [$deletedEmail, $userId]);

        $db->commit();

        logoutUser();
        setFlash('success', 'Tài khoản của bạn đã được xóa thành công. Rất tiếc vì sự rời đi của bạn.');
        axRedirect(BASE_URL . '/');
    } catch (Exception $e) {
        $db->rollback();
        error_log('Delete Account Error: ' . $e->getMessage());
        setFlash('error', 'Có lỗi xảy ra khi xóa tài khoản. Vui lòng thử lại sau.');
        axRedirect(BASE_URL . '/auth/account.php');
    }
}

// Fallback
axRedirect(BASE_URL . '/auth/account.php');
