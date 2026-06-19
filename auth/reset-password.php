<?php
/**
 * Reset Password - Đặt lại mật khẩu mới
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/');
}

// Kiểm tra đã xác thực OTP chưa
if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_user_id'])) {
    setFlash('error', 'Vui lòng xác thực mã OTP trước');
    redirect(BASE_URL . '/auth/forgot-password.php');
}

// Check for flash messages
$flash = getFlash();

// Lấy thông tin user để hiển thị
$db = db();
$user = $db->selectOne("SELECT full_name, email FROM users WHERE user_id = ?", [$_SESSION['reset_user_id']]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Đặt Lại Mật Khẩu - Axeron</title>
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
    <?php include __DIR__ . '/../includes/dark-mode.php'; ?>
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
    <main class="flex-1 flex items-center justify-center w-full min-h-screen p-4 pt-24 md:pt-0">
        <!-- Form Container -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-6 md:p-8 border border-[#e5e2e1]">
            <!-- Flash Message -->
            <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#e8f0fe] text-[#2979FF] mb-5 shadow-sm border border-[#b0c6ff]">
                    <span class="material-symbols-outlined fill-icon text-3xl">check_circle</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-3" style="font-family: 'Montserrat', sans-serif;">Đặt mật khẩu mới</h2>
                <p class="text-sm text-[#5b403f] leading-relaxed">
                    <?php if ($user): ?>
                    Xin chào <strong class="text-[#1b1c1c]"><?= htmlspecialchars($user['full_name']) ?></strong>, vui lòng nhập mật khẩu mới cho tài khoản của bạn.
                    <?php else: ?>
                    Vui lòng nhập mật khẩu mới cho tài khoản của bạn.
                    <?php endif; ?>
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="reset_token" value="<?= htmlspecialchars($_SESSION['reset_token'] ?? '') ?>">

                <!-- New Password Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="new_password">Mật khẩu mới</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required type="password" minlength="6"/>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility('new_password', 'toggle-new-password-icon')">
                            <span class="material-symbols-outlined" id="toggle-new-password-icon">visibility_off</span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-[#8f6f6e]">Ít nhất 6 ký tự</p>
                </div>

                <!-- Confirm Password Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="confirm_password">Xác nhận mật khẩu mới</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required type="password" minlength="6"/>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility('confirm_password', 'toggle-confirm-password-icon')">
                            <span class="material-symbols-outlined" id="toggle-confirm-password-icon">visibility_off</span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-[#8f6f6e]">Nhập lại mật khẩu để xác nhận</p>
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

                <!-- Submit Button -->
                <button class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                    Cập Nhật Mật Khẩu
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 text-center pt-6 border-t border-[#f0eded]">
                <a class="inline-flex items-center justify-center text-sm font-semibold text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/login.php">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Quay lại trang Đăng nhập
                </a>
            </div>
        </div>
    </main>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Password strength indicator
        const newPasswordInput = document.getElementById('new_password');
        const strengthDiv = document.getElementById('password-strength');
        const strengthText = document.getElementById('strength-text');

        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            if (password.length === 0) {
                strengthDiv.classList.add('hidden');
                return;
            }
            strengthDiv.classList.remove('hidden');

            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const labels = ['Yếu', 'Trung bình', 'Khá mạnh', 'Mạnh'];
            const level = Math.min(Math.floor(strength / 1.5), 4);

            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById('strength-' + i);
                if (i <= level) {
                    bar.className = 'h-1.5 flex-1 rounded-full ' + colors[level - 1];
                } else {
                    bar.className = 'h-1.5 flex-1 rounded-full bg-[#e5e2e1]';
                }
            }

            if (level > 0) {
                strengthText.textContent = 'Độ mạnh: ' + labels[level - 1];
            } else {
                strengthText.textContent = '';
            }
        });
    </script>
</body>
</html>
