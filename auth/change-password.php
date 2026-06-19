<?php

/**
 * Change Password Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Require login to access
requireLogin();

$db = db();
$userId = getUserId();
$error = '';
$success = '';

// Xử lý form đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yêu cầu không hợp lệ. Vui lòng tải lại trang và thử lại.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate
        if (empty($currentPassword)) {
        $error = 'Vui lòng nhập mật khẩu hiện tại';
    } elseif (empty($newPassword)) {
        $error = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Mật khẩu mới phải có ít nhất 8 ký tự';
    } elseif (!preg_match('/[A-Z]/', $newPassword)) {
        $error = 'Mật khẩu mới phải có ít nhất 1 chữ hoa';
    } elseif (!preg_match('/[0-9]/', $newPassword)) {
        $error = 'Mật khẩu mới phải có ít nhất 1 chữ số';
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?`~]/', $newPassword)) {
        $error = 'Mật khẩu mới phải có ít nhất 1 ký tự đặc biệt';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        // Kiểm tra mật khẩu hiện tại
        $user = $db->selectOne("SELECT password_hash FROM users WHERE user_id = ?", [$userId]);

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $error = 'Mật khẩu hiện tại không đúng';
        } else {
            // Cập nhật mật khẩu mới
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->update("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?", [$newHash, $userId]);

            $success = 'Đổi mật khẩu thành công!';
            // Clear form on success
            $_POST = [];
        }
    }
    }
}

// KHÔNG lấy flash message chung - chỉ hiện thông báo khi đổi mật khẩu thành công từ form submit
// Điều này tránh hiện thông báo đăng nhập cũ trên trang đổi mật khẩu
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Đổi Mật Khẩu - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-20..200&display=block" rel="stylesheet"/>
    <style>
        * { font-family: 'Noto Sans', sans-serif; }
        .font-display-lg, h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-[#fcf9f8] text-[#1b1c1c] font-body-md antialiased min-h-screen flex flex-col" style="font-family: 'Noto Sans', sans-serif;">
    <!-- Header -->
    <header class="w-full py-4 px-margin-mobile md:px-margin-desktop border-b border-[#e5e2e1] bg-[#fcf9f8] flex justify-center items-center absolute top-0 z-10">
        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
            <span class="font-display-lg text-[#BE1E2D] uppercase tracking-tight text-xl md:text-2xl" style="font-family: 'Montserrat', sans-serif;">Axeron Sport</span>
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center w-full min-h-screen p-4 pt-28 md:pt-32 pb-12">
        <!-- Form Container -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-[#e5e2e1]">
            <?php if ($success): ?>
            <!-- Success Message -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#e8f0fe] text-[#2979FF] mb-5 shadow-sm border border-[#b0c6ff]">
                    <span class="material-symbols-outlined fill-icon text-3xl">check_circle</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-3" style="font-family: 'Montserrat', sans-serif;">Đổi mật khẩu thành công!</h2>
                <p class="text-sm text-[#5b403f] leading-relaxed">Bạn có thể sử dụng mật khẩu mới từ bây giờ.</p>
            </div>
            <div class="pt-4">
                <a href="<?= BASE_URL ?>/auth/account.php" class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] transition-colors duration-200 group" style="font-family: 'Montserrat', sans-serif;">
                    Quay lại tài khoản
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </a>
            </div>
            <?php else: ?>
            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#ffdad6] text-[#93000a] mb-5 shadow-sm border border-[#ffb3b0]">
                    <span class="material-symbols-outlined fill-icon text-3xl">lock_reset</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-3" style="font-family: 'Montserrat', sans-serif;">Đổi mật khẩu</h2>
                <p class="text-sm text-[#5b403f] leading-relaxed">
                    Bảo vệ tài khoản của bạn bằng một mật khẩu mạnh.
                </p>
            </div>

            <?php if ($error): ?>
            <!-- Error Alert -->
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 text-red-700">
                <span class="material-symbols-outlined text-[20px] flex-shrink-0">error</span>
                <p class="text-sm"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>
            <form action="<?= BASE_URL ?>/auth/change-password.php" class="space-y-6" id="change-password-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                <!-- Current Password Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="current_password">Mật khẩu hiện tại</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại" required type="password"/>
                        <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility('current_password', 'icon_current')" type="button">
                            <span class="material-symbols-outlined" id="icon_current">visibility_off</span>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="new_password">Mật khẩu mới</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required type="password" minlength="8"/>
                        <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility('new_password', 'icon_new')" type="button">
                            <span class="material-symbols-outlined" id="icon_new">visibility_off</span>
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-[#8f6f6e] flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">info</span>
                        Tối thiểu 8 ký tự (chữ hoa, số, ký tự đặc biệt).
                    </p>
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="confirm_password">Xác nhận mật khẩu mới</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required type="password"/>
                        <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility('confirm_password', 'icon_confirm')" type="button">
                            <span class="material-symbols-outlined" id="icon_confirm">visibility_off</span>
                        </button>
                    </div>
                </div>

                <!-- Password Strength Indicator -->
                <div id="password-strength" class="hidden">
                    <div class="flex gap-2 mb-2">
                        <div id="strength-1" class="h-1.5 flex-1 rounded-full bg-[#e5e2e1]"></div>
                        <div id="strength-2" class="h-1.5 flex-1 rounded-full bg-[#e5e2e1]"></div>
                        <div id="strength-3" class="h-1.5 flex-1 rounded-full bg-[#e5e2e1]"></div>
                        <div id="strength-4" class="h-1.5 flex-1 rounded-full bg-[#e5e2e1]"></div>
                    </div>
                    <p id="strength-text" class="text-xs font-semibold text-[#5b403f]"></p>
                </div>

                <!-- Action Button -->
                <button class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                    Cập nhật mật khẩu
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-8 text-center pt-6 border-t border-[#f0eded]">
                <a class="inline-flex items-center justify-center text-sm font-semibold text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/account.php">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Quay lại trang Tài khoản
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === "password") {
            input.type = "text";
            icon.textContent = "visibility";
        } else {
            input.type = "password";
            icon.textContent = "visibility_off";
        }
    }

    // Password strength indicator
    const newPasswordInput = document.getElementById('new_password');
    const strengthDiv = document.getElementById('password-strength');
    const strengthText = document.getElementById('strength-text');
    const strengthBars = [
        document.getElementById('strength-1'),
        document.getElementById('strength-2'),
        document.getElementById('strength-3'),
        document.getElementById('strength-4')
    ];

    function updateStrength(password) {
        if (password.length === 0) {
            strengthDiv.classList.add('hidden');
            return;
        }

        strengthDiv.classList.remove('hidden');
        let strength = 0;
        let hints = [];

        if (password.length >= 8) strength++; else hints.push('ít nhất 8 ký tự');
        if (/[a-z]/.test(password)) strength++; else hints.push('chữ thường');
        if (/[A-Z]/.test(password)) strength++; else hints.push('chữ hoa');
        if (/[0-9]/.test(password)) strength++; else hints.push('số');
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        strength = Math.min(Math.floor(strength / 1.25), 4);
        if (strength === 0) strength = 1;

        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        const labels = ['Yếu', 'Trung bình', 'Khá', 'Mạnh'];

        for (let i = 0; i < 4; i++) {
            if (i < strength) {
                strengthBars[i].className = `h-1.5 flex-1 rounded-full transition-colors ${colors[strength - 1]}`;
            } else {
                strengthBars[i].className = 'h-1.5 flex-1 rounded-full bg-[#e5e2e1] transition-colors';
            }
        }

        if (strength > 0) {
            strengthText.textContent = `Độ mạnh: ${labels[strength - 1]}`;
            if (hints.length > 0 && strength < 4) {
                strengthText.textContent += ` - Thêm: ${hints.join(', ')}`;
            }
        }
    }

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            updateStrength(this.value);
        });
    }

    // Confirm password validation
    const confirmInput = document.getElementById('confirm_password');
    if (confirmInput) {
        confirmInput.addEventListener('input', function() {
            const newPass = document.getElementById('new_password').value;
            if (this.value && this.value !== newPass) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Form submit validation
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            const errors = [];
            let focusedField = null;

            if (newPass.length < 8) { errors.push('Mật khẩu phải có ít nhất 8 ký tự'); if (!focusedField) focusedField = document.getElementById('new_password'); }
            if (!/[A-Z]/.test(newPass)) { errors.push('Mật khẩu phải có ít nhất 1 chữ hoa'); if (!focusedField) focusedField = document.getElementById('new_password'); }
            if (!/[0-9]/.test(newPass)) { errors.push('Mật khẩu phải có ít nhất 1 chữ số'); if (!focusedField) focusedField = document.getElementById('new_password'); }
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(newPass)) { errors.push('Mật khẩu phải có ít nhất 1 ký tự đặc biệt'); if (!focusedField) focusedField = document.getElementById('new_password'); }
            if (newPass !== confirmPass) { errors.push('Mật khẩu xác nhận không khớp'); if (!focusedField) focusedField = document.getElementById('confirm_password'); }

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors[0]);
                if (focusedField) {
                    focusedField.focus();
                }
            }
        });
    }
    </script>
</body>
</html>
