<?php
/**
 * Register - Đăng ký
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
    <title>Đăng Ký - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
<body class="bg-[#fcf9f8] text-[#1b1c1c] antialiased min-h-screen flex flex-col items-center justify-center relative bg-cover bg-center py-12" style="font-family: 'Noto Sans', sans-serif; background-image: url('<?= BASE_URL ?>/assets/images/auth-banner.png');">
    <!-- Background Overlays -->
    <div class="absolute inset-0 bg-axeron-red/40 mix-blend-multiply"></div>
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Navigation to Homepage -->
    <a href="<?= BASE_URL ?>/" class="absolute top-6 left-6 z-20 flex items-center gap-2 text-white hover:text-[#ffb3b0] transition-colors bg-black/30 px-4 py-2 rounded-full backdrop-blur-sm border border-white/20">
        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
        <span class="font-medium text-sm">Quay lại trang chủ</span>
    </a>

    <!-- Main Content -->
    <main class="relative z-10 w-full max-w-lg px-4 py-8">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <a class="flex items-center gap-3 flex-shrink-0 bg-white/10 p-3 px-5 rounded-2xl backdrop-blur-md border border-white/20 hover:bg-white/20 transition-colors" href="<?= BASE_URL ?>/">
                <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-10 h-10 rounded-lg object-cover shadow-sm">
                <span class="font-display-lg text-white uppercase tracking-tight text-2xl font-black drop-shadow-sm" style="font-family: 'Montserrat', sans-serif;">Axeron</span>
            </a>
        </div>

        <!-- Form Container -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 w-full border border-[#e5e2e1]">
            <!-- Flash Message -->
            <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-[#1b1c1c] mb-2" style="font-family: 'Montserrat', sans-serif;">Tạo Tài Khoản</h2>
                <p class="text-sm text-[#5b403f]">Điền thông tin bên dưới để tạo tài khoản mới.</p>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-5">
                <input type="hidden" name="action" value="register">
                
                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="full_name">Họ và tên *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">badge</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="full_name" name="full_name" placeholder="Nhập họ và tên" required type="text" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="email">Email *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">mail</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="email" name="email" placeholder="Nhập email" required type="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-[#1b1c1c] mb-2" for="phone">Số điện thoại *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-[#8f6f6e] text-[20px]">phone</span>
                        </div>
                        <input class="w-full pl-11 pr-4 py-3 border border-[#e3bebb] rounded-xl bg-[#fcf9f8] text-[#1b1c1c] focus:outline-none focus:ring-2 focus:ring-[#BE1E2D] focus:border-transparent transition-all" id="phone" name="phone" placeholder="Nhập số điện thoại" required type="tel" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
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

            <div class="mt-8 text-center pt-6 border-t border-[#f0eded]">
                <p class="text-sm text-[#5b403f]">
                    Đã có tài khoản?
                    <a class="font-semibold text-[#BE1E2D] hover:text-[#98001b] transition-colors ml-1" href="<?= BASE_URL ?>/auth/login.php">Đăng nhập ngay</a>
                </p>
            </div>
        </div>
    </main>

    <script>
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
            const password = passwordInput.value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errors = [];

            if (password.length < 8) errors.push('Mật khẩu phải có ít nhất 8 ký tự');
            if (!/[A-Z]/.test(password)) errors.push('Mật khẩu phải có ít nhất 1 chữ hoa');
            if (!/[0-9]/.test(password)) errors.push('Mật khẩu phải có ít nhất 1 chữ số');
            if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?`~]/.test(password)) errors.push('Mật khẩu phải có ít nhất 1 ký tự đặc biệt');
            if (password !== confirmPassword) errors.push('Mật khẩu xác nhận không khớp');

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
                if (password !== confirmPassword && errors.length === 1) {
                    alert('Mật khẩu xác nhận không khớp!');
                }
            }
        });
    </script>
</body>
</html>
