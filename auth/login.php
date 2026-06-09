<?php
/**
 * Login - ÄÄƒng nháº­p
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
    <title>ÄÄƒng Nháº­p - Axeron</title>
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
        .fill-icon { font-variation-settings: "FILL" 1; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col">
    <!-- Header -->
    <header class="w-full py-4 px-margin-mobile md:px-margin-desktop border-b border-surface-variant bg-surface flex justify-center items-center absolute top-0 z-10">
        <a class="font-display-lg text-headline-lg font-black text-axeron-red tracking-tight" href="<?= BASE_URL ?>/">
            AXERON
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row w-full min-h-screen">
        <!-- Left Side: Branding -->
        <div class="hidden md:flex md:w-1/2 relative bg-surface-gray items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative z-10 p-12 text-center text-white">
                <h1 class="font-display-lg text-display-lg text-white mb-4 uppercase drop-shadow-md">VÆ°á»£t Má»i<br/>Giá»›i Háº¡n</h1>
                <p class="font-body-lg text-body-lg text-surface-container-highest max-w-md mx-auto">
                    Tham gia cá»™ng Ä‘á»“ng Axeron Ä‘á»ƒ khÃ¡m phÃ¡ nhá»¯ng bá»™ sÆ°u táº­p thá»ƒ thao Ä‘á»‰nh cao vÃ  Æ°u Ä‘Ã£i Ä‘á»™c quyá»n.
                </p>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-margin-mobile md:p-12 lg:p-24 bg-surface mt-16 md:mt-0">
            <div class="w-full max-w-md">
                <!-- Flash Message -->
                <?php if ($flash): ?>
                <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
                <?php endif; ?>

                <div class="mb-8 text-center md:text-left">
                    <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface mb-2">ÄÄƒng Nháº­p</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">ChÃ o má»«ng báº¡n trá»Ÿ láº¡i! Vui lÃ²ng nháº­p thÃ´ng tin.</p>
                </div>

                <form method="POST" action="<?= BASE_URL ?>/api/auth-handler.php" class="space-y-6">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? BASE_URL . '/') ?>">

                    <!-- Email / Phone Input -->
                    <div>
                        <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="email">Email hoáº·c Sá»‘ Ä‘iá»‡n thoáº¡i</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline">person</span>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 border border-outline-variant rounded bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-shadow" id="email" name="email" placeholder="Nháº­p email hoáº·c sá»‘ Ä‘iá»‡n thoáº¡i" required type="text" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="password">Máº­t kháº©u</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline">lock</span>
                            </div>
                            <input class="w-full pl-10 pr-10 py-3 border border-outline-variant rounded bg-surface-container-lowest text-on-surface focus:outline-none focus:ring-2 focus:ring-axeron-blue focus:border-transparent transition-shadow" id="password" name="password" placeholder="Nháº­p máº­t kháº©u" required type="password"/>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-outline hover:text-on-surface transition-colors" onclick="togglePasswordVisibility()">
                                <span class="material-symbols-outlined" id="visibility-icon">visibility_off</span>
                            </div>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input class="h-4 w-4 text-axeron-red focus:ring-axeron-blue border-outline rounded cursor-pointer" id="remember-me" name="remember_me" type="checkbox" value="1"/>
                            <label class="ml-2 block font-body-md text-body-md text-on-surface cursor-pointer" for="remember-me">
                                Ghi nhá»› Ä‘Äƒng nháº­p
                            </label>
                        </div>
                        <div class="text-sm">
                            <a class="font-label-lg text-label-lg text-axeron-blue hover:text-secondary transition-colors" href="<?= BASE_URL ?>/auth/forgot-password.php">QuÃªn máº­t kháº©u?</a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button class="w-full flex justify-center py-3 px-4 border border-transparent rounded shadow-sm font-label-lg text-label-lg uppercase text-white bg-axeron-red hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-axeron-red transition-colors duration-200" type="submit">
                            ÄÄƒng nháº­p
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="mt-8 relative">
                    <div aria-hidden="true" class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-outline-variant"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="px-2 bg-surface font-body-md text-body-md text-on-surface-variant">Hoáº·c tiáº¿p tá»¥c vá»›i</span>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="mt-8 grid grid-cols-2 gap-4">
                    <a class="w-full inline-flex justify-center items-center py-2 px-4 border border-outline-variant rounded bg-surface-container-lowest font-label-lg text-label-lg text-on-surface hover:bg-surface-container-low transition-colors shadow-sm" href="#">
                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                        </svg>
                        Google
                    </a>
                    <a class="w-full inline-flex justify-center items-center py-2 px-4 border border-outline-variant rounded bg-surface-container-lowest font-label-lg text-label-lg text-on-surface hover:bg-surface-container-low transition-colors shadow-sm" href="#">
                        <svg aria-hidden="true" class="h-5 w-5 mr-2 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                            <path clip-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" fill-rule="evenodd"></path>
                        </svg>
                        Facebook
                    </a>
                </div>

                <!-- Sign up prompt -->
                <p class="mt-8 text-center font-body-md text-body-md text-on-surface-variant">
                    ChÆ°a cÃ³ tÃ i khoáº£n?
                    <a class="font-label-lg text-label-lg text-axeron-red hover:text-primary transition-colors" href="<?= BASE_URL ?>/auth/register.php">ÄÄƒng kÃ½ ngay</a>
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

        // === Ghi nhá»› Ä‘Äƒng nháº­p (localStorage) ===
        const STORAGE_KEY = 'axeron_saved_credentials';
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const rememberCheckbox = document.getElementById('remember-me');

        // KhÃ´i phá»¥c thÃ´ng tin Ä‘Ã£ lÆ°u khi load trang
        (function restoreSavedCredentials() {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (!saved) return;
            try {
                const data = JSON.parse(atob(saved));
                if (data.e) emailInput.value = data.e;
                if (data.p) passwordInput.value = data.p;
                rememberCheckbox.checked = true;
            } catch (err) {
                localStorage.removeItem(STORAGE_KEY);
            }
        })();

        // LÆ°u hoáº·c xoÃ¡ thÃ´ng tin khi submit form
        document.querySelector('form').addEventListener('submit', function() {
            if (rememberCheckbox.checked) {
                const data = btoa(JSON.stringify({
                    e: emailInput.value,
                    p: passwordInput.value
                }));
                localStorage.setItem(STORAGE_KEY, data);
            } else {
                localStorage.removeItem(STORAGE_KEY);
            }
        });

        // XoÃ¡ thÃ´ng tin lÆ°u khi bá» tick checkbox
        rememberCheckbox.addEventListener('change', function() {
            if (!this.checked) {
                localStorage.removeItem(STORAGE_KEY);
            }
        });
    </script>
</body>
</html>

