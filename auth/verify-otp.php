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
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-20..200&display=block" rel="stylesheet"/>
    <style>
        * { font-family: 'Noto Sans', sans-serif; }
        .font-display-lg, h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .otp-input:focus { border-color: #BE1E2D; box-shadow: 0 0 0 3px rgba(190, 30, 45, 0.2); }
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
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-[#e5e2e1]">
            <!-- Flash Message -->
            <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#ffdad6] text-[#93000a] mb-5 shadow-sm border border-[#ffb3b0]">
                    <span class="material-symbols-outlined fill-icon text-3xl">pin</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-3" style="font-family: 'Montserrat', sans-serif;">Nhập mã xác thực</h2>
                <p class="text-sm text-[#5b403f] leading-relaxed">
                    Mã xác thực đã được gửi đến <strong class="text-[#1b1c1c]"><?= htmlspecialchars($resetEmail) ?></strong>
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="reset_token" value="<?= htmlspecialchars($resetToken) ?>">

                <!-- OTP Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="otp">Mã xác thực (6 số)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3.5 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all text-center text-2xl tracking-[1em] otp-input font-bold" id="otp" name="otp" placeholder="------" required type="text" maxlength="6" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]{6}"/>
                    </div>
                    <p class="mt-2 text-xs text-center text-[#8f6f6e]">Mã có hiệu lực trong <span id="otp-timer" class="font-bold text-[#BE1E2D]">05:00</span></p>
                </div>

                <!-- Submit Button -->
                <button class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                    Xác Thực
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-[#5b403f] text-sm">
                    Không nhận được mã?
                    <a href="<?= BASE_URL ?>/auth/forgot-password.php" class="text-[#BE1E2D] hover:text-[#98001b] transition-colors font-semibold ml-1">
                        Gửi lại
                    </a>
                </p>
            </div>

            <div class="mt-6 text-center pt-6 border-t border-[#f0eded]">
                <a class="inline-flex items-center justify-center text-sm font-semibold text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/login.php">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Quay lại trang Đăng nhập
                </a>
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
            const otpInput = document.getElementById('otp');
            otpInput.focus();

            // Countdown Timer
            let timeLeft = 300; // 5 minutes in seconds
            const timerElement = document.getElementById('otp-timer');
            
            const timerInterval = setInterval(() => {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerElement.textContent = "00:00";
                    timerElement.classList.replace("text-[#BE1E2D]", "text-[#8f6f6e]");
                    otpInput.disabled = true;
                    otpInput.placeholder = "Hết hạn";
                }
            }, 1000);
        });
    </script>
</body>
</html>
