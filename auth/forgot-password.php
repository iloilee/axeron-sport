<?php
/**
 * Forgot Password - Quên mật khẩu
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/');
}

// Check for flash messages
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Quên Mật Khẩu - Axeron</title>
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
                    <span class="material-symbols-outlined fill-icon text-3xl">lock_reset</span>
                </div>
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-3" style="font-family: 'Montserrat', sans-serif;">Quên Mật Khẩu?</h2>
                <p class="text-sm text-[#5b403f] leading-relaxed">
                    Nhập địa chỉ email liên kết với tài khoản của bạn và chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
                </p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                <input type="hidden" name="action" value="forgot_password">

                <!-- Email Input -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="email">Địa chỉ Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">mail</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3.5 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="email" name="email" placeholder="email@example.com" required type="email"/>
                    </div>
                </div>

                <!-- Submit Button -->
                <button class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                    Gửi Yêu Cầu
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-8 text-center pt-6 border-t border-[#f0eded]">
                <a class="inline-flex items-center justify-center text-sm font-semibold text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/login.php">
                    <span class="material-symbols-outlined mr-2 text-[18px]">arrow_back</span>
                    Quay lại trang Đăng nhập
                </a>
            </div>
        </div>
    </main>
</body>
</html>
