<?php
/**
 * Register - Đăng ký
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Check for flash messages
$flash = getFlash();
$showSuccessAndRedirect = false;

// Redirect if already logged in
if (isLoggedIn()) {
    if ($flash && $flash['type'] === 'success') {
        $showSuccessAndRedirect = true;
    } else {
        redirect(BASE_URL . '/');
    }
}

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

// Lấy url redirect nếu có
$targetRedirect = BASE_URL . '/';
if (!empty($_SESSION['register_redirect'])) {
    $targetRedirect = $_SESSION['register_redirect'];
    unset($_SESSION['register_redirect']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Đăng Ký - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-surface-variant": "#5b403f", "tertiary": "#005066", "on-primary": "#ffffff",
                        "error-container": "#ffdad6", "surface-container-lowest": "#ffffff", "outline": "#8f6f6e",
                        "on-tertiary-fixed": "#001f29", "on-secondary-fixed": "#001945", "tertiary-fixed-dim": "#85d1ef",
                        primary: "#98001b", "on-error-container": "#93000a", "surface-dim": "#dcd9d9", white: "#FFFFFF",
                        "on-primary-fixed-variant": "#930019", "on-surface": "#1b1c1c", "axeron-red": "#BE1E2D",
                        error: "#ba1a1a", "surface-container-low": "#f6f3f2", "surface-variant": "#e5e2e1",
                        "inverse-primary": "#ffb3b0", "surface-gray": "#F5F5F5", "axeron-blue": "#2979FF",
                        "secondary-container": "#0f6df3", "surface-container-high": "#eae7e7", "on-background": "#1b1c1c",
                        "on-secondary-fixed-variant": "#00429b", "tertiary-fixed": "#baeaff", "on-tertiary": "#ffffff",
                        "secondary-fixed": "#d9e2ff", "surface-tint": "#b91a2a", "on-secondary-container": "#fefcff",
                        "primary-fixed-dim": "#ffb3b0", secondary: "#0056c5", "text-dark": "#212121",
                        "surface-container": "#f0eded", "secondary-fixed-dim": "#b0c6ff", "on-primary-container": "#ffd3d1",
                        "surface-container-highest": "#e5e2e1", "on-secondary": "#ffffff",
                        "tertiary-container": "#006a85", "primary-container": "#be1e2d", "inverse-on-surface": "#f3f0ef",
                        background: "#fcf9f8", "on-primary-fixed": "#410006", "on-error": "#ffffff",
                        surface: "#fcf9f8", "primary-fixed": "#ffdad8", "on-tertiary-container": "#abe6ff",
                        "outline-variant": "#e3bebb", "inverse-surface": "#303030", "surface-bright": "#fcf9f8",
                        "on-tertiary-fixed-variant": "#004d62"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { "container-max": "1200px", gutter: "16px", base: "8px", "margin-mobile": "16px", "margin-desktop": "24px" },
                    fontFamily: { "headline-lg": ["Montserrat", "sans-serif"], "body-lg": ["Noto Sans", "sans-serif"], "display-lg": ["Montserrat", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg-mobile": ["Montserrat", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"] },
                    fontSize: { "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }], "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }], "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }] }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-[#fcf9f8] text-[#1b1c1c] font-body-md antialiased min-h-screen flex flex-col" style="font-family: 'Noto Sans', sans-serif;">
    <header class="w-full py-4 px-margin-mobile md:px-margin-desktop border-b border-[#e5e2e1] bg-[#fcf9f8] flex justify-center items-center absolute top-0 z-10">
        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
            <span class="font-display-lg text-[#BE1E2D] uppercase tracking-tight text-xl md:text-2xl" style="font-family: 'Montserrat', sans-serif;">Axeron Sport</span>
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row w-full min-h-screen">
        <!-- Left Side: Branding -->
        <div class="hidden md:flex md:w-1/2 relative bg-[#F5F5F5] items-center justify-center overflow-hidden bg-cover bg-center" style="background-image: url('<?= BASE_URL ?>/assets/images/auth-banner.png');">
            <div class="absolute inset-0 bg-[#BE1E2D]/40 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
            <div class="relative z-10 p-12 text-center text-white">
                <h1 class="font-display-lg text-[48px] font-black leading-tight text-white mb-4 uppercase drop-shadow-md" style="font-family: 'Montserrat', sans-serif;">Khởi Đầu<br/>Hành Trình</h1>
                <p class="text-[18px] text-[#e5e2e1] max-w-md mx-auto">
                    Trở thành thành viên của Axeron ngay hôm nay để mở khóa những đặc quyền hấp dẫn và cháy hết mình với đam mê thể thao.
                </p>
            </div>
        </div>

        <!-- Right Side: Register Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-4 md:p-12 lg:p-24 bg-[#fcf9f8] mt-16 md:mt-0">
            <div class="w-full max-w-md">
            <?php if ($showSuccessAndRedirect): ?>
                <div class="text-center py-10 bg-white rounded-2xl shadow-xl border border-[#e5e2e1] p-8">
                    <span class="material-symbols-outlined text-6xl text-green-500 mb-4 block mx-auto">check_circle</span>
                    <h2 class="text-2xl font-bold text-[#1b1c1c] mb-2" style="font-family: 'Montserrat', sans-serif;">Đăng ký thành công!</h2>
                    <p class="text-sm text-[#5b403f]">Hệ thống đang tự động chuyển hướng...</p>
                    <div class="mt-8 flex justify-center">
                        <div class="w-8 h-8 border-4 border-[#BE1E2D] border-t-transparent rounded-full animate-spin"></div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        setTimeout(() => {
                            window.location.href = <?= json_encode($targetRedirect) ?>;
                        }, 3000);
                    });
                </script>
            <?php else: ?>
                <div class="mb-8 text-center">
                    <h2 class="text-2xl md:text-[32px] font-bold text-[#1b1c1c] mb-2" style="font-family: 'Montserrat', sans-serif;">Tạo Tài Khoản</h2>
                    <p class="text-base text-[#5b403f]">Điền thông tin bên dưới để tạo tài khoản mới.</p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-5">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                
                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="full_name">Họ và tên *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">badge</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="full_name" name="full_name" placeholder="Nhập họ và tên" required type="text" value="<?= htmlspecialchars($old['full_name'] ?? $_POST['full_name'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="email">Email *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">mail</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="email" name="email" placeholder="Nhập email" required type="email" value="<?= htmlspecialchars($old['email'] ?? $_POST['email'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="phone">Số điện thoại *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">phone</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="phone" name="phone" placeholder="Nhập số điện thoại" required type="tel" value="<?= htmlspecialchars($old['phone'] ?? $_POST['phone'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="password">Mật khẩu *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="password" name="password" placeholder="Ít nhất 8 ký tự" required type="password" minlength="8"/>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="togglePasswordVisibility()">
                            <span class="material-symbols-outlined" id="visibility-icon">visibility_off</span>
                        </div>
                    </div>
                    <!-- Password Requirements -->
                    <div id="password-requirements" class="mt-2 p-3 bg-[#fcf9f8] rounded-xl border border-[#e3bebb] text-sm" style="display:none;">
                        <p class="font-semibold text-sm text-[#5b403f] mb-1.5">Mật khẩu phải có:</p>
                        <ul class="space-y-1">
                            <li id="req-length" class="flex items-center gap-1.5 text-[#5b403f]">
                                <span class="material-symbols-outlined text-base" id="icon-length">circle</span>
                                <span>Ít nhất 8 ký tự</span>
                            </li>
                            <li id="req-uppercase" class="flex items-center gap-1.5 text-[#5b403f]">
                                <span class="material-symbols-outlined text-base" id="icon-uppercase">circle</span>
                                <span>Ít nhất 1 chữ hoa (A-Z)</span>
                            </li>
                            <li id="req-number" class="flex items-center gap-1.5 text-[#5b403f]">
                                <span class="material-symbols-outlined text-base" id="icon-number">circle</span>
                                <span>Ít nhất 1 chữ số (0-9)</span>
                            </li>
                            <li id="req-special" class="flex items-center gap-1.5 text-[#5b403f]">
                                <span class="material-symbols-outlined text-base" id="icon-special">circle</span>
                                <span>Ít nhất 1 ký tự đặc biệt (!@#$%...)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="confirm_password">Xác nhận mật khẩu *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">lock</span>
                        </div>
                        <input class="w-full pl-11 pr-10 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu" required type="password"/>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-[#8f6f6e] hover:text-[#1b1c1c] transition-colors" onclick="toggleConfirmPasswordVisibility()">
                            <span class="material-symbols-outlined" id="confirm-visibility-icon">visibility_off</span>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start gap-3">
                    <input class="h-5 w-5 mt-0.5 text-[#BE1E2D] focus:ring-[#BE1E2D] border-[#e3bebb] rounded cursor-pointer" id="agree-terms" name="agree_terms" required type="checkbox"/>
                    <label class="text-sm text-[#5b403f]" for="agree-terms">
                        Tôi đồng ý với <a class="text-[#2979FF] hover:underline font-medium" href="<?= BASE_URL ?>/policies/privacy-policy.php">Chính sách bảo mật</a> và <a class="text-[#2979FF] hover:underline font-medium" href="<?= BASE_URL ?>/policies/purchase-policy.php">Điều khoản dịch vụ</a> của Axeron.
                    </label>
                </div>

                <!-- Submit Button -->
                <button class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold uppercase tracking-wide text-white bg-[#BE1E2D] hover:bg-[#98001b] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#BE1E2D] transition-colors duration-200 group" type="submit" style="font-family: 'Montserrat', sans-serif;">
                    Đăng ký
                    <span class="material-symbols-outlined ml-2 group-hover:translate-x-1 transition-transform text-[20px]">arrow_forward</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="mt-8 relative">
                <div aria-hidden="true" class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-[#e3bebb]"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-2 bg-white text-sm text-[#5b403f]">Hoặc đăng ký nhanh bằng</span>
                </div>
            </div>

            <!-- Social Login -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <a class="w-full inline-flex justify-center items-center py-3 px-4 border border-[#e3bebb] rounded-xl bg-white font-semibold text-sm text-[#1b1c1c] hover:bg-[#fcf9f8] transition-colors shadow-sm" href="<?= BASE_URL ?>/auth/redirect.php">
                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    </svg>
                    Google
                </a>
                <a class="w-full inline-flex justify-center items-center py-3 px-4 border border-[#e3bebb] rounded-xl bg-white font-semibold text-sm text-[#1b1c1c] hover:bg-[#fcf9f8] transition-colors shadow-sm" href="<?= BASE_URL ?>/auth/facebook-login.php">
                    <svg aria-hidden="true" class="h-5 w-5 mr-2 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                        <path clip-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" fill-rule="evenodd"></path>
                    </svg>
                    Facebook
                </a>
            </div>

            <div class="mt-8 text-center pt-6 border-t border-[#f0eded]">
                <p class="text-sm text-[#5b403f]">
                    Đã có tài khoản?
                    <a class="font-semibold text-[#BE1E2D] hover:text-[#98001b] transition-colors ml-1" href="<?= BASE_URL ?>/auth/login.php">Đăng nhập ngay</a>
                </p>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed inset-0 pointer-events-none z-[9999] flex flex-col items-center justify-center gap-4"></div>

    <script>
        // Toast notification (Centered Modal Style)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            
            // Xóa các thông báo cũ để không bị lặp hiển thị nhiều lần
            container.innerHTML = '';

            const toast = document.createElement('div');

            const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
            const iconColor = type === 'success' ? 'text-green-500' : type === 'error' ? 'text-red-500' : 'text-blue-500';
            const bgColor = 'bg-white';

            toast.className = `${bgColor} border border-gray-100 pointer-events-auto px-8 py-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col items-center gap-3 transform transition-all duration-300 scale-95 opacity-0 min-w-[320px] text-center`;
            toast.innerHTML = `
                <span class="material-symbols-outlined text-[48px] ${iconColor}">${icon}</span>
                <span class="text-gray-800 font-semibold text-lg leading-tight">${message}</span>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('scale-95', 'opacity-0');
                toast.classList.add('scale-100', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('scale-100', 'opacity-100');
                toast.classList.add('scale-95', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        <?php if ($flash && !$showSuccessAndRedirect): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showToast(<?= json_encode($flash['message']) ?>, <?= json_encode($flash['type']) ?>);
        });
        <?php endif; ?>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('visibility-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        function toggleConfirmPasswordVisibility() {
            const passwordInput = document.getElementById('confirm_password');
            const icon = document.getElementById('confirm-visibility-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Real-time password requirements check
        const passwordInput = document.getElementById('password');
        const reqBox = document.getElementById('password-requirements');

        const requirements = [
            { id: 'length',    test: v => v.length >= 8 },
            { id: 'uppercase', test: v => /[A-Z]/.test(v) },
            { id: 'number',    test: v => /[0-9]/.test(v) },
            { id: 'special',   test: v => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(v) }
        ];

        passwordInput.addEventListener('focus', () => reqBox.style.display = 'block');
        passwordInput.addEventListener('blur', () => {
            if (passwordInput.value === '') reqBox.style.display = 'none';
        });

        passwordInput.addEventListener('input', function() {
            const val = this.value;
            requirements.forEach(req => {
                const li   = document.getElementById('req-' + req.id);
                const icon = document.getElementById('icon-' + req.id);
                if (req.test(val)) {
                    li.classList.remove('text-on-surface-variant', 'text-error');
                    li.classList.add('text-green-600');
                    icon.textContent = 'check_circle';
                    icon.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                } else {
                    li.classList.remove('text-green-600');
                    li.classList.add('text-on-surface-variant');
                    icon.textContent = 'circle';
                    icon.style.fontVariationSettings = "'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                }
            });
        });

        // Form submit validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fullNameInput = document.getElementById('full_name');
            const phoneInput = document.getElementById('phone');
            const password = passwordInput.value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errors = [];
            let focusedField = null;

            const fullNameVal = fullNameInput.value.trim();
            if (fullNameVal.length === 0) {
                errors.push('Vui lòng nhập họ tên');
                if (!focusedField) focusedField = fullNameInput;
            } else if (fullNameVal.length < 2 || fullNameVal.length > 100) {
                errors.push('Họ tên phải từ 2 đến 100 ký tự');
                if (!focusedField) focusedField = fullNameInput;
            } else if (/\d/.test(fullNameVal)) {
                errors.push('Họ tên không được chứa số');
                if (!focusedField) focusedField = fullNameInput;
            } else if (!/^[\p{L}\s'-]+$/u.test(fullNameVal)) {
                errors.push('Họ tên chứa ký tự không hợp lệ');
                if (!focusedField) focusedField = fullNameInput;
            }

            const phoneVal = phoneInput.value.replace(/\s/g, '');
            if (phoneVal.length === 0 || !/^0[0-9]{9,10}$/.test(phoneVal)) {
                errors.push('Số điện thoại không hợp lệ');
                if (!focusedField) focusedField = phoneInput;
            }

            if (password.length < 8) { errors.push('Mật khẩu phải có ít nhất 8 ký tự'); if (!focusedField) focusedField = passwordInput; }
            if (!/[A-Z]/.test(password)) { errors.push('Mật khẩu phải có ít nhất 1 chữ hoa'); if (!focusedField) focusedField = passwordInput; }
            if (!/[0-9]/.test(password)) { errors.push('Mật khẩu phải có ít nhất 1 chữ số'); if (!focusedField) focusedField = passwordInput; }
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(password)) { errors.push('Mật khẩu phải có ít nhất 1 ký tự đặc biệt'); if (!focusedField) focusedField = passwordInput; }
            if (password !== confirmPassword) { errors.push('Mật khẩu xác nhận không khớp'); if (!focusedField) focusedField = document.getElementById('confirm_password'); }

            if (errors.length > 0) {
                e.preventDefault();
                // Highlight unmet requirements in red
                requirements.forEach(req => {
                    if (!req.test(password)) {
                        const li   = document.getElementById('req-' + req.id);
                        const icon = document.getElementById('icon-' + req.id);
                        li.classList.remove('text-on-surface-variant', 'text-green-600');
                        li.classList.add('text-error');
                        icon.textContent = 'cancel';
                        icon.style.fontVariationSettings = "'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24";
                    }
                });
                reqBox.style.display = 'block';
                
                showToast(errors[0], 'error'); // Show the first error message as a centered toast
                if (focusedField) {
                    focusedField.focus(); // Focus on the first invalid field
                }
            }
        });
    </script>
</body>
</html>
