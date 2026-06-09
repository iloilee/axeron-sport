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
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate
    if (empty($currentPassword)) {
        $error = 'Vui lòng nhập mật khẩu hiện tại';
    } elseif (empty($newPassword)) {
        $error = 'Vui lòng nhập mật khẩu mới';
    } elseif (strlen($newPassword) < 8) {
        $error = 'Mật khẩu mới phải dài ít nhất 8 ký tự';
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

// KHÔNG lấy flash message chung - chỉ hiện thông báo khi đổi mật khẩu thành công từ form submit
// Điều này tránh hiện thông báo đăng nhập cũ trên trang đổi mật khẩu
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Đổi mật khẩu - Axeron</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "on-surface-variant": "#5b403f",
                tertiary: "#005066",
                "on-primary": "#ffffff",
                "error-container": "#ffdad6",
                "surface-container-lowest": "#ffffff",
                outline: "#8f6f6e",
                "on-tertiary-fixed": "#001f29",
                "on-secondary-fixed": "#001945",
                "tertiary-fixed-dim": "#85d1ef",
                primary: "#98001b",
                "on-error-container": "#93000a",
                "surface-dim": "#dcd9d9",
                white: "#FFFFFF",
                "on-primary-fixed-variant": "#930019",
                "on-surface": "#1b1c1c",
                "axeron-red": "#BE1E2D",
                error: "#ba1a1a",
                "surface-container-low": "#f6f3f2",
                "surface-variant": "#e5e2e1",
                "inverse-primary": "#ffb3b0",
                "surface-gray": "#F5F5F5",
                "axeron-blue": "#2979FF",
                "secondary-container": "#0f6df3",
                "surface-container-high": "#eae7e7",
                "on-background": "#1b1c1c",
                "on-secondary-fixed-variant": "#00429b",
                "tertiary-fixed": "#baeaff",
                "on-tertiary": "#ffffff",
                "secondary-fixed": "#d9e2ff",
                "surface-tint": "#b91a2a",
                "on-secondary-container": "#fefcff",
                "primary-fixed-dim": "#ffb3b0",
                secondary: "#0056c5",
                "text-dark": "#212121",
                "surface-container": "#f0eded",
                "secondary-fixed-dim": "#b0c6ff",
                "on-primary-container": "#ffd3d1",
                "surface-container-highest": "#e5e2e1",
                "on-secondary": "#ffffff",
                "tertiary-container": "#006a85",
                "primary-container": "#be1e2d",
                "inverse-on-surface": "#f3f0ef",
                background: "#fcf9f8",
                "on-primary-fixed": "#410006",
                "on-error": "#ffffff",
                surface: "#fcf9f8",
                "primary-fixed": "#ffdad8",
                "on-tertiary-container": "#abe6ff",
                "outline-variant": "#e3bebb",
                "inverse-surface": "#303030",
                "surface-bright": "#fcf9f8",
                "on-tertiary-fixed-variant": "#004d62"
            },
            borderRadius: {
                DEFAULT: "0.125rem",
                lg: "0.25rem",
                xl: "0.5rem",
                full: "0.75rem"
            },
            spacing: {
                "container-max": "1200px",
                gutter: "16px",
                base: "8px",
                "margin-mobile": "16px",
                "margin-desktop": "24px"
            },
            fontFamily: {
                "headline-lg": ["Montserrat", "sans-serif"],
                "body-lg": ["Noto Sans", "sans-serif"],
                "display-lg": ["Montserrat", "sans-serif"],
                "headline-md": ["Montserrat", "sans-serif"],
                "headline-lg-mobile": ["Montserrat", "sans-serif"],
                "label-sm": ["Noto Sans", "sans-serif"],
                "label-lg": ["Noto Sans", "sans-serif"],
                "body-md": ["Noto Sans", "sans-serif"]
            },
            fontSize: {
                "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }],
                "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }],
                "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }],
                "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }]
            }
        }
    }
};
</script>
<style>
body { font-family: 'Noto Sans', sans-serif; }
h1, h2, h3, h4, h5, h6, .font-headline-lg, .font-display-lg, .font-headline-md, .font-headline-lg-mobile { font-family: 'Montserrat', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
</style>
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">
<!-- Header -->
<header class="bg-surface border-b border-outline-variant w-full sticky top-0 z-40">
    <div class="flex justify-between items-center w-full px-margin-mobile md:px-margin-desktop py-4 max-w-container-max mx-auto">
        <a class="font-display-lg text-headline-lg font-black text-axeron-red tracking-tight" href="<?= BASE_URL ?>">Axeron</a>
        <a class="text-on-surface hover:text-axeron-red transition-colors flex items-center gap-2" href="<?= BASE_URL ?>/auth/account.php">
            <span class="material-symbols-outlined">arrow_back</span>
            <span class="hidden md:inline font-label-lg text-label-lg">Quay lại</span>
        </a>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow flex items-center justify-center py-12 px-margin-mobile md:px-margin-desktop">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-surface-variant p-8 relative overflow-hidden">
        <!-- Subtle accent top border -->
        <div class="absolute top-0 left-0 w-full h-1 bg-axeron-red"></div>

        <?php if ($success): ?>
        <!-- Success Message -->
        <div class="text-center mb-6">
            <span class="material-symbols-outlined text-5xl text-green-500 mb-4">check_circle</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-2"><?= htmlspecialchars($success) ?></h2>
            <p class="text-on-surface-variant font-body-md">Bạn có thể đăng nhập bằng mật khẩu mới.</p>
        </div>
        <div class="pt-4">
            <a href="<?= BASE_URL ?>/auth/account.php" class="w-full inline-flex items-center justify-center gap-2 bg-axeron-red text-white font-label-lg text-label-lg uppercase tracking-wider py-4 rounded-DEFAULT hover:bg-primary-fixed-variant transition-colors shadow-sm">
                Quay lại tài khoản
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <?php else: ?>
        <div class="text-center mb-8">
            <span class="material-symbols-outlined text-4xl text-axeron-red mb-4">lock_reset</span>
            <h1 class="font-headline-md text-headline-md text-on-surface mb-2">Đổi mật khẩu</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">Bảo vệ tài khoản của bạn bằng một mật khẩu mạnh.</p>
        </div>

        <?php if ($error): ?>
        <!-- Error Alert -->
        <div class="mb-6 p-4 bg-error-container border border-red-300 rounded-lg flex items-start gap-3">
            <span class="material-symbols-outlined text-error flex-shrink-0">error</span>
            <p class="text-on-error-container font-body-md"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-6">
            <!-- Current Password -->
            <div>
                <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="current_password">Mật khẩu hiện tại</label>
                <div class="relative">
                    <input class="w-full px-4 py-3 rounded-DEFAULT border border-surface-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue outline-none transition-colors font-body-md text-body-md bg-surface-gray text-on-surface pr-12" id="current_password" name="current_password" placeholder="Nhập mật khẩu hiện tại" required="" type="password"/>
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-axeron-red transition-colors" onclick="togglePasswordVisibility('current_password', 'icon_current')" type="button">
                        <span class="material-symbols-outlined" id="icon_current">visibility</span>
                    </button>
                </div>
            </div>

            <!-- New Password -->
            <div>
                <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="new_password">Mật khẩu mới</label>
                <div class="relative">
                    <input class="w-full px-4 py-3 rounded-DEFAULT border border-surface-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue outline-none transition-colors font-body-md text-body-md bg-surface-gray text-on-surface pr-12" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required="" type="password" minlength="8"/>
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-axeron-red transition-colors" onclick="togglePasswordVisibility('new_password', 'icon_new')" type="button">
                        <span class="material-symbols-outlined" id="icon_new">visibility</span>
                    </button>
                </div>
                <p class="mt-2 font-label-sm text-label-sm text-on-surface-variant flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">info</span>
                    Mật khẩu phải dài ít nhất 8 ký tự.
                </p>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="confirm_password">Xác nhận mật khẩu mới</label>
                <div class="relative">
                    <input class="w-full px-4 py-3 rounded-DEFAULT border border-surface-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue outline-none transition-colors font-body-md text-body-md bg-surface-gray text-on-surface pr-12" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required="" type="password"/>
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-axeron-red transition-colors" onclick="togglePasswordVisibility('confirm_password', 'icon_confirm')" type="button">
                        <span class="material-symbols-outlined" id="icon_confirm">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Password Strength Indicator -->
            <div id="password-strength" class="hidden">
                <div class="flex gap-1 mb-2">
                    <div id="strength-1" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                    <div id="strength-2" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                    <div id="strength-3" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                    <div id="strength-4" class="h-1 flex-1 rounded-full bg-gray-200 transition-colors"></div>
                </div>
                <p id="strength-text" class="font-label-sm text-label-sm text-on-surface-variant"></p>
            </div>

            <!-- Action Button -->
            <div class="pt-4">
                <button class="w-full bg-axeron-red text-white font-label-lg text-label-lg uppercase tracking-wider py-4 rounded-DEFAULT hover:bg-primary transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-axeron-red flex items-center justify-center gap-2" type="submit">
                    <span>Cập nhật mật khẩu</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</main>

<!-- Footer -->
<footer class="bg-inverse-surface w-full py-12 px-margin-mobile md:px-margin-desktop text-white mt-auto">
    <div class="max-w-container-max mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="text-headline-md font-headline-lg text-white">Axeron</div>
        <div class="font-body-md text-body-md text-surface-variant">
            Copyright © <?= date('Y') ?> Axeron Sport. All rights reserved.
        </div>
    </div>
</footer>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "visibility_off";
    } else {
        input.type = "password";
        icon.textContent = "visibility";
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

    // Length check
    if (password.length >= 8) strength++;
    else hints.push('ít nhất 8 ký tự');

    // Lowercase check
    if (/[a-z]/.test(password)) strength++;
    else hints.push('chữ thường');

    // Uppercase check
    if (/[A-Z]/.test(password)) strength++;
    else hints.push('chữ hoa');

    // Number check
    if (/[0-9]/.test(password)) strength++;
    else hints.push('số');

    // Special character check
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    // Cap strength at 4
    strength = Math.min(strength, 4);

    // Update bars
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
    const labels = ['Yếu', 'Trung bình', 'Khá', 'Mạnh'];

    for (let i = 0; i < 4; i++) {
        if (i < strength) {
            strengthBars[i].className = `h-1 flex-1 rounded-full transition-colors ${colors[strength - 1]}`;
        } else {
            strengthBars[i].className = 'h-1 flex-1 rounded-full bg-gray-200 transition-colors';
        }
    }

    if (strength > 0) {
        strengthText.textContent = `Độ mạnh: ${labels[strength - 1]}`;
        if (hints.length > 0 && strength < 4) {
            strengthText.textContent += ` - Thêm: ${hints.join(', ')}`;
        }
    }
}

newPasswordInput.addEventListener('input', function() {
    updateStrength(this.value);
});

// Confirm password validation
const confirmInput = document.getElementById('confirm_password');
confirmInput.addEventListener('input', function() {
    const newPass = document.getElementById('new_password').value;
    if (this.value && this.value !== newPass) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        this.setCustomValidity('');
    }
});
</script>
</body>
</html>
