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
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-20..200&display=swap" rel="stylesheet"/>
    <style>
        * { font-family: 'Noto Sans', sans-serif; }
        .font-display-lg, h1, h2, h3 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .fill-icon { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-[#fcf9f8] text-[#1b1c1c] antialiased min-h-screen flex flex-col" style="font-family: 'Noto Sans', sans-serif;">
    <!-- Header -->
    <header class="w-full py-4 px-4 md:px-6 border-b border-[#e5e2e1] bg-[#fcf9f8] flex justify-center items-center absolute top-0 z-10">
        <a class="text-2xl font-black text-[#BE1E2D] tracking-tight" href="<?= BASE_URL ?>/" style="font-family: 'Montserrat', sans-serif; font-weight: 800;">
            AXERON
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row w-full min-h-screen">
        <!-- Left Side: Branding -->
        <div class="hidden md:flex md:w-1/2 relative bg-[#F5F5F5] items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative z-10 p-12 text-center text-white">
                <h1 class="text-5xl font-black text-white mb-4 uppercase drop-shadow-md" style="font-family: 'Montserrat', sans-serif;">
                    Khôi Phục<br/>Mật Khẩu
                </h1>
                <p class="text-lg text-[#e5e2e1] max-w-md mx-auto">
                    Không lo lắng! Nhập email của bạn để nhận liên kết đặt lại mật khẩu.
                </p>
            </div>
        </div>

        <!-- Right Side: Forgot Password Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-12 lg:p-24 bg-[#fcf9f8] mt-16 md:mt-0">
            <div class="w-full max-w-md">
                <!-- Flash Message -->
                <?php if ($flash): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="mb-8 text-center md:text-left">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#ffdad6] text-[#93000a] mb-4">
                        <span class="material-symbols-outlined fill-icon text-3xl">lock_reset</span>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-semibold text-[#1b1c1c] mb-2" style="font-family: 'Montserrat', sans-serif;">Quên mật khẩu?</h2>
                    <p class="text-base text-[#5b403f]">
                        Đừng lo lắng! Nhập địa chỉ email liên kết với tài khoản của bạn và chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
                    </p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                    <input type="hidden" name="action" value="forgot_password">

                    <!-- Email Input -->
                    <div>
                        <label class="block text-sm font-medium text-[#1b1c1c] mb-2" for="email">Địa chỉ Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-[#8f6f6e]">mail</span>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 border border-[#e3bebb] rounded bg-white text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#2979FF] focus:border-transparent transition-shadow" id="email" name="email" placeholder="nhapemail@axeron.com" required type="email"/>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded shadow-sm text-sm font-medium uppercase text-white bg-[#BE1E2D] hover:bg-[#be1e2d] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                        Gửi Yêu Cầu
                        <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <a class="inline-flex items-center justify-center text-sm font-medium text-[#5b403f] hover:text-[#BE1E2D] transition-colors duration-200" href="<?= BASE_URL ?>/auth/login.php">
                        <span class="material-symbols-outlined mr-2 text-lg">arrow_back</span>
                        Quay lại trang Đăng nhập
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
