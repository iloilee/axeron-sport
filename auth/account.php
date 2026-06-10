<?php
/**
 * User Account Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Require login to access account page
requireLogin();

$db = db();
$userId = getUserId();

// Lấy thông tin user mới nhất
$user = $db->selectOne("
    SELECT u.*, r.role_name 
    FROM users u 
    JOIN roles r ON u.role_id = r.role_id 
    WHERE u.user_id = ?
", [$userId]);

// Cập nhật session user data
loginUser([
    'user_id' => $user['user_id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'role_id' => $user['role_id'],
    'role_name' => $user['role_name'],
    'avatar_url' => $user['avatar_url']
]);

// Lấy địa chỉ giao hàng mặc định
$defaultAddress = $db->selectOne("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1", [$userId]);

// Lấy danh sách Tỉnh/Thành cho dropdown
$shippingPrices = $db->select("SELECT province_city FROM shipping_prices ORDER BY province_city ASC");

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tài Khoản - Axeron Sport</title>
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
        .avatar-upload-label { cursor: pointer; }
        .avatar-upload-label:hover .avatar-overlay { opacity: 1; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased flex flex-col min-h-screen">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark">Tài Khoản Của Tôi</h1>

        <?php if ($flash): ?>
        <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700' ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high p-6 md:p-8">
            <h2 class="font-headline-md text-headline-md font-semibold text-text-dark mb-6 border-b border-surface-variant pb-4">Thông Tin Tài Khoản</h2>

            <form action="<?= BASE_URL ?>/api/account-handler.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                <!-- Phần hiển thị và Upload Avatar -->
                <div class="flex items-center gap-6 border-b border-surface-variant pb-6">
                    <div class="relative w-24 h-24 rounded-full overflow-hidden border-2 border-outline-variant flex-shrink-0 bg-surface-variant">
                        <?php 
                        $avatarImg = !empty($user['avatar_url']) ? BASE_URL . $user['avatar_url'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? 'User') . '&background=random';
                        ?>
                        <img id="avatar-preview" src="<?= htmlspecialchars($avatarImg) ?>" alt="Avatar" class="w-full h-full object-cover">
                        
                        <label for="avatar-upload" class="avatar-upload-label absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 transition-opacity duration-200">
                            <span class="material-symbols-outlined text-white">photo_camera</span>
                        </label>
                        <input type="file" id="avatar-upload" name="avatar" accept="image/jpeg, image/png, image/webp" class="hidden" onchange="previewImage(event)">
                    </div>
                    <div>
                        <h3 class="font-headline-md text-headline-md text-on-surface"><?= htmlspecialchars($user['full_name'] ?? 'N/A') ?></h3>
                        <p class="text-on-surface-variant capitalize"><?= htmlspecialchars($user['role_name'] ?? 'customer') ?></p>
                        <label for="avatar-upload" class="mt-2 inline-block text-sm text-axeron-blue cursor-pointer hover:underline">Thay đổi ảnh đại diện</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Họ và tên -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="full_name">Họ và Tên</label>
                        <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required minlength="2" maxlength="100" title="Vui lòng nhập họ tên hợp lệ từ 2 đến 100 ký tự"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>

                    <!-- Email (Readonly) -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="email">Email <span class="text-xs lowercase text-outline">(Dùng để đăng nhập)</span></label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled readonly
                               class="px-4 py-3 bg-surface-variant text-on-surface-variant border border-outline-variant rounded cursor-not-allowed font-body-md w-full" />
                    </div>

                    <!-- Số điện thoại -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" pattern="[0-9]{10,11}" title="Vui lòng nhập 10 hoặc 11 chữ số"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>

                    <!-- Ngày sinh -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="date_of_birth">Ngày sinh</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>" max="<?= date('Y-m-d') ?>"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>

                    <!-- Giới tính -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="gender">Giới tính</label>
                        <select id="gender" name="gender" class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full appearance-none">
                            <option value="">Chọn giới tính</option>
                            <option value="male" <?= ($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                </div>

                <!-- Địa chỉ giao hàng mặc định -->
                <h3 class="font-headline-md text-headline-md font-semibold text-text-dark mt-8 border-b border-surface-variant pb-4">Địa chỉ giao hàng mặc định</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="province">Tỉnh/Thành phố</label>
                        <select id="province" name="province" class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full appearance-none">
                            <option value="">Chọn Tỉnh/Thành</option>
                            <?php foreach ($shippingPrices as $sp): ?>
                                <option value="<?= htmlspecialchars($sp['province_city']) ?>" <?= (($defaultAddress['province'] ?? '') === $sp['province_city']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sp['province_city']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="district">Quận/Huyện</label>
                        <input type="text" id="district" name="district" value="<?= htmlspecialchars($defaultAddress['district'] ?? '') ?>" placeholder="Ví dụ: Quận 1" minlength="2" maxlength="100"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="ward">Phường/Xã</label>
                        <input type="text" id="ward" name="ward" value="<?= htmlspecialchars($defaultAddress['ward'] ?? '') ?>" placeholder="Ví dụ: Phường Bến Nghé" minlength="2" maxlength="100"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="street_address">Đường/Số nhà</label>
                        <input type="text" id="street_address" name="street_address" value="<?= htmlspecialchars($defaultAddress['street_address'] ?? '') ?>" placeholder="Ví dụ: 123 Lê Lợi" minlength="2" maxlength="255"
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-outline-variant flex items-center gap-4">
                    <button type="submit" class="bg-axeron-red text-white px-6 py-3 rounded-lg font-label-lg hover:bg-primary transition-colors uppercase">
                        Cập nhật thông tin
                    </button>
                    <a href="<?= BASE_URL ?>/auth/change-password.php" class="inline-block bg-surface-container-high text-on-surface px-6 py-3 rounded-lg font-label-lg hover:bg-surface-dim transition-colors">
                        Đổi Mật Khẩu
                    </a>
                </div>
            </form>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script>
        // Xử lý xem trước ảnh khi chọn file
        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
