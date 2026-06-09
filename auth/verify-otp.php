<?php
/**
 * Verify OTP - Xác thực mã OTP
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/');
}

// Kiểm tra đã gửi OTP chưa
if (empty($_SESSION['reset_token']) || empty($_SESSION['reset_email'])) {
    setFlash('error', 'Vui lòng nhập email để nhận mã xác thực');
    redirect(BASE_URL . '/auth/forgot-password.php');
}

$resetToken = $_SESSION['reset_token'];
$resetEmail = $_SESSION['reset_email'];

// Check for flash messages
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Xác Thực OTP - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-20..200&display=swap" rel="stylesheet"/>
    <style>
        * { font-family: 'Noto Sans', sans-serif; }
        .font-display-lg, h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .otp-input:focus { border-color: #2979FF; box-shadow: 0 0 0 3px rgba(41, 121, 255, 0.2); }
    </style>
</head>
<body class="bg-[#fcf9f8] text-[#1b1c1c] antialiased min-h-screen flex flex-col" style="font-family: 'Noto Sans', sans-serif;">
    <!-- Header -->
    <header class="w-full py-4 px-4 md:px-6 border-b border-[#e5e2e1] bg-[#fcf9f8] flex justify-center items-center absolute top-0 z-10">
                        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
            <span class="font-display-lg text-[#BE1E2D] uppercase tracking-tight text-xl md:text-2xl" style="font-family: 'Montserrat', sans-serif;">Axeron</span>
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row w-full min-h-screen">
        <!-- Left Side: Branding -->
        <div class="hidden md:flex md:w-1/2 relative bg-[#F5F5F5] items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative z-10 p-12 text-center text-white">
                <h1 class="text-5xl font-black text-white mb-4 uppercase drop-shadow-md" style="font-family: 'Montserrat', sans-serif;">
                    Xác Thực<br/>Mã OTP
                </h1>
                <p class="text-lg text-[#e5e2e1] max-w-md mx-auto">
                    Nhập mã xác thực đã được gửi đến email của bạn.
                </p>
            </div>
        </div>

        <!-- Right Side: OTP Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-12 lg:p-24 bg-[#fcf9f8] mt-16 md:mt-0">
            <div class="w-full max-w-md">
                <!-- Flash Message -->
                <?php if ($flash): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="mb-8 text-center md:text-left">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#0f6df3] text-white mb-4">
                        <span class="material-symbols-outlined fill-icon text-3xl">pin</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#1b1c1c] mb-2" style="font-family: 'Montserrat', sans-serif;">Nhập mã xác thực</h2>
                    <p class="text-base text-[#5b403f]">
                        Mã xác thực đã được gửi đến <strong><?= htmlspecialchars($resetEmail) ?></strong>
                    </p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                    <input type="hidden" name="action" value="verify_otp">
                    <input type="hidden" name="reset_token" value="<?= htmlspecialchars($resetToken) ?>">

                    <!-- OTP Input -->
                    <div>
                        <label class="block text-sm font-medium text-[#1b1c1c] mb-2" for="otp">Mã xác thực (6 số)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-[#8f6f6e]">lock</span>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 border border-[#e3bebb] rounded bg-white text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#2979FF] focus:border-transparent transition-shadow text-center text-2xl tracking-[1em] otp-input" id="otp" name="otp" placeholder="------" required type="text" maxlength="6" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}"/>
                        </div>
                        <p class="mt-2 text-sm text-[#5b403f]">Mã có hiệu lực trong 5 phút</p>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded shadow-sm text-sm font-medium uppercase text-white bg-[#BE1E2D] hover:bg-[#be1e2d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200" type="submit" style="font-family: 'Montserrat', sans-serif;">
                        Xác Thực
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-[#5b403f] text-sm">
                        Không nhận được mã?
                        <a href="<?= BASE_URL ?>/auth/forgot-password.php" class="text-[#2979FF] hover:text-[#0056c5] transition-colors font-medium">
                            Gửi lại
                        </a>
                    </p>
                </div>

                <div class="mt-8 text-center">
                    <a class="inline-flex items-center justify-center text-sm font-medium text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/login.php">
                        <span class="material-symbols-outlined mr-2 text-lg">arrow_back</span>
                        Quay lại trang Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-format OTP input - chỉ cho phép số
        document.getElementById('otp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);

            // Auto submit when 6 digits entered
            if (this.value.length === 6) {
                this.closest('form').submit();
            }
        });

        // Focus on load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('otp').focus();
        });
    </script>
</body>
</html>
