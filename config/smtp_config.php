<?php
/**
 * SMTP Configuration - Cấu hình email SMTP
 *
 * Hướng dẫn cấu hình:
 * 1. Gmail: Bật 2-Factor Authentication và tạo App Password tại https://myaccount.google.com/security
 * 2. SMTP Server: smtp.gmail.com
 * 3. Port: 587 (TLS) hoặc 465 (SSL)
 * 4. Username: Email Gmail của bạn
 * 5. Password: App Password (không phải mật khẩu thường)
 */

// Load PHPMailer
require_once __DIR__ . '/../vendor/PHPMailer/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/SMTP/SMTP.php';
require_once __DIR__ . '/../vendor/PHPMailer/Exception/Exception.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Cấu hình SMTP - THAY ĐỔI THEO THÔNG TIN CỦA BẠN
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'hoduykhang060@gmail.com');        // Thay bằng email của bạn
define('SMTP_PASSWORD', 'drjuxekykkjocpgv');            // Thay bằng App Password (16 ký tự)
define('SMTP_FROM_NAME', 'Axeron Sports');
define('SMTP_FROM_EMAIL', 'noreply@axeron.com');
define('SMTP_ENCRYPTION', 'tls');                        // 'tls' hoặc 'ssl'

// Cấu hình OTP
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_LENGTH', 6);

/**
 * Gửi email sử dụng PHPMailer
 *
 * @param string $to      Email người nhận
 * @param string $subject Tiêu đề email
 * @param string $body    Nội dung email (HTML)
 * @param string $altBody Nội dung thay thế (plain text)
 * @return bool
 */
function sendEmail($to, $subject, $body, $altBody = '') {

    $mail = new PHPMailer(true);

    try {
        // Cấu hình debug (0 = tắt, 1 = client, 2 = client và server)
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer: $str");
        };

        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Người gửi
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // Người nhận
        $mail->addAddress($to);

        // Nội dung
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $altBody ?: strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "<h3 style='color:red;'>LỖI THẬT LÀ: " . $mail->ErrorInfo . "</h3>";
        die(); // Dừng chạy code luôn để xem lỗi trên màn hình
    }
}

/**
 * Tạo mã OTP ngẫu nhiên
 *
 * @param int $length Độ dài mã
 * @return string
 */
function generateOTP($length = 6) {
    return str_pad((string)random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Tạo token reset mật khẩu ngẫu nhiên
 *
 * @return string
 */
function generateResetToken() {
    return bin2hex(random_bytes(32));
}
