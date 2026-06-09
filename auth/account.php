<?php
/**
 * User Account Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Require login to access account page
requireLogin();

$user = getUserData();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tài Khoản - Axeron Sport</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-background": "#1b1c1c",
                        "inverse-surface": "#303030",
                        "text-dark": "#212121",
                        "tertiary-container": "#006a85",
                        "background": "#fcf9f8",
                        "on-primary-fixed-variant": "#930019",
                        "inverse-primary": "#ffb3b0",
                        "secondary-fixed-dim": "#b0c6ff",
                        "error-container": "#ffdad6",
                        "outline-variant": "#e3bebb",
                        "tertiary-fixed": "#baeaff",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#d9e2ff",
                        "on-primary-fixed": "#410006",
                        "surface-container": "#f0eded",
                        error: "#ba1a1a",
                        "axeron-red": "#BE1E2D",
                        "on-tertiary": "#ffffff",
                        "surface-dim": "#dcd9d9",
                        "on-primary-container": "#ffd3d1",
                        "secondary-container": "#0f6df3",
                        "surface-tint": "#b91a2a",
                        primary: "#98001b",
                        "surface-gray": "#F5F5F5",
                        "surface-bright": "#fcf9f8",
                        "surface-container-highest": "#e5e2e1",
                        "on-surface": "#1b1c1c",
                        white: "#FFFFFF",
                        tertiary: "#005066",
                        "surface-container-high": "#eae7e7",
                        "on-error-container": "#93000a",
                        "primary-container": "#be1e2d",
                        "primary-fixed": "#ffdad8",
                        "surface-container-lowest": "#ffffff",
                        "inverse-on-surface": "#f3f0ef",
                        "on-tertiary-container": "#abe6ff",
                        "surface-variant": "#e5e2e1",
                        "on-secondary-container": "#fefcff",
                        secondary: "#0056c5",
                        outline: "#8f6f6e",
                        "axeron-blue": "#2979FF",
                        "tertiary-fixed-dim": "#85d1ef",
                        surface: "#fcf9f8",
                        "on-secondary-fixed-variant": "#00429b",
                        "on-tertiary-fixed": "#001f29",
                        "on-tertiary-fixed-variant": "#004d62",
                        "surface-container-low": "#f6f3f2",
                        "on-secondary-fixed": "#001945",
                        "primary-fixed-dim": "#ffb3b0",
                        "on-surface-variant": "#5b403f",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { "margin-desktop": "24px", gutter: "16px", "container-max": "1200px", base: "8px", "margin-mobile": "16px" },
                    fontFamily: { "body-lg": ["Noto Sans", "sans-serif"], "headline-lg-mobile": ["Montserrat", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "display-lg": ["Montserrat", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg": ["Montserrat", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"] },
                    fontSize: { "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }], "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }], "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }] }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased flex flex-col min-h-screen">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark">Tài Khoản Của Tôi</h1>

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high p-6 md:p-8">
            <h2 class="font-headline-md text-headline-md font-semibold text-text-dark mb-6 border-b border-surface-variant pb-4">Thông Tin Tài Khoản</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-1">
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide">Họ và Tên</label>
                    <p class="text-on-surface font-body-md"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></p>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide">Email</label>
                    <p class="text-on-surface font-body-md"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide">Vai trò</label>
                    <p class="text-on-surface font-body-md capitalize"><?= htmlspecialchars($user['role'] ?? 'customer') ?></p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-outline-variant">
                <a href="<?= BASE_URL ?>/auth/change-password.php" class="inline-block bg-axeron-blue text-white px-6 py-3 rounded-lg font-label-lg hover:bg-secondary transition-colors">
                    Đổi Mật Khẩu
                </a>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
