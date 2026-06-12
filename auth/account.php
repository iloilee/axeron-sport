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

        <?php if ($flash && $flash['message'] === 'Chào mừng test register!'): ?>
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
                        $avatarImg = 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name'] ?? 'User') . '&background=random';
                        if (!empty($user['avatar_url'])) {
                            $avatarImg = (strpos($user['avatar_url'], 'http') === 0) ? $user['avatar_url'] : BASE_URL . $user['avatar_url'];
                        }
                        ?>
                        <img id="avatar-preview" src="<?= htmlspecialchars($avatarImg) ?>" alt="Avatar" class="w-full h-full object-cover" referrerpolicy="no-referrer">
                        
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

                    <!-- Email -->
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="email">Email <span class="text-xs lowercase text-outline">(Dùng để đăng nhập)</span></label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
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
                        <select id="district" name="district" class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full appearance-none disabled:opacity-50 disabled:bg-surface-variant" disabled>
                            <option value="">Chọn Quận/Huyện</option>
                        </select>
                        <input type="hidden" id="current_district" value="<?= htmlspecialchars($defaultAddress['district'] ?? '') ?>">
                    </div>
                    
                    <div class="flex flex-col gap-2 md:col-span-2">
                        <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="street_address">Địa chỉ</label>
                        <textarea id="street_address" name="street_address" rows="3" placeholder="Số nhà, Tên đường..."
                               class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full resize-none"><?= htmlspecialchars($defaultAddress['street_address'] ?? '') ?></textarea>
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

        <!-- Block Xóa Tài Khoản -->
        <div class="bg-red-50 rounded-xl border border-red-200 p-6 md:p-8 mt-8">
            <h2 class="font-headline-md text-headline-md font-semibold text-axeron-red mb-4">Xóa Tài Khoản</h2>
            <p class="text-on-surface-variant mb-6 text-sm">
                Việc xóa tài khoản sẽ đánh dấu tài khoản của bạn là đã xóa và xóa toàn bộ thông tin cá nhân.
                Hành động này <strong>không thể hoàn tác</strong>. Mọi dữ liệu liên quan đến lịch sử đơn hàng sẽ được giữ lại vô danh để phục vụ đối soát.
            </p>
            <button onclick="confirmDeleteAccount()" class="bg-white text-axeron-red border border-red-200 px-6 py-3 rounded-lg font-label-lg hover:bg-red-100 transition-colors uppercase shadow-sm">
                Xóa tài khoản
            </button>
            <form id="delete-account-form" action="<?= BASE_URL ?>/api/account-handler.php" method="POST" class="hidden">
                <input type="hidden" name="action" value="delete_account">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            </form>
        </div>

        <?php if (!empty($_SESSION['show_email_otp_modal'])): ?>
        <!-- OTP Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity">
            <div class="bg-surface rounded-xl shadow-2xl p-6 w-full max-w-md border border-outline-variant transform transition-transform scale-100">
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-error-container text-on-error-container mb-4">
                        <span class="material-symbols-outlined text-3xl">mark_email_read</span>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-2">Xác Thực Email Mới</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Mã xác thực (6 số) đã được gửi đến <strong><?= htmlspecialchars($_SESSION['email_change_new_email'] ?? '') ?></strong>. 
                        Vui lòng kiểm tra hộp thư của bạn.
                    </p>
                </div>
                
                <form action="<?= BASE_URL ?>/api/account-handler.php" method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="verify_email_otp">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                    
                    <div>
                        <label class="block font-label-lg text-label-lg text-on-surface mb-2" for="otp">Mã Xác Thực (OTP)</label>
                        <input type="text" id="otp" name="otp" required pattern="[0-9]{6}" placeholder="Nhập 6 số" maxlength="6" autofocus
                               class="text-center tracking-[0.5em] text-2xl px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors font-body-md w-full" />
                    </div>
                    
                    <div class="flex gap-4">
                        <a href="<?= BASE_URL ?>/api/account-handler.php?action=cancel_email_change" class="flex-1 text-center bg-surface-container-high text-on-surface px-4 py-3 rounded-lg font-label-lg hover:bg-surface-dim transition-colors uppercase py-3">
                            Hủy
                        </a>
                        <button type="submit" class="flex-1 bg-axeron-red text-white px-4 py-3 rounded-lg font-label-lg hover:bg-primary transition-colors uppercase shadow-sm">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <!-- Toast Container -->
    <div id="toast-container"></div>

    <script>
        // Hiển thị thông báo dạng cửa sổ nổi (Modal)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            container.innerHTML = '';

            const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
            const iconBg = type === 'success' ? 'bg-green-50 text-green-500 border-green-100' : type === 'error' ? 'bg-red-50 text-red-500 border-red-100' : 'bg-blue-50 text-blue-500 border-blue-100';

            const modalHtml = `
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-opacity duration-300" id="alert-modal-backdrop">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-transform duration-300 scale-95" id="alert-modal-content">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full ${iconBg} mb-5 border shadow-sm">
                            <span class="material-symbols-outlined text-[40px]">${icon}</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3" style="font-family: 'Montserrat', sans-serif;">Thông báo</h3>
                        <p class="text-gray-600 mb-8 text-base px-2">${message}</p>
                        <button onclick="document.getElementById('toast-container').innerHTML=''" class="px-8 py-3 bg-axeron-red text-white rounded-xl hover:bg-red-700 font-semibold transition-colors shadow-md w-full">
                            Đóng
                        </button>
                    </div>
                </div>
            `;
            
            container.innerHTML = modalHtml;

            requestAnimationFrame(() => {
                const wrapper = document.getElementById('alert-modal-content');
                if (wrapper) {
                    wrapper.classList.remove('scale-95');
                    wrapper.classList.add('scale-100');
                }
            });
        }

        <?php if ($flash && $flash['message'] !== 'Chào mừng test register!'): ?>
        document.addEventListener('DOMContentLoaded', () => {
            showToast(<?= json_encode($flash['message']) ?>, <?= json_encode($flash['type']) ?>);
        });
        <?php endif; ?>

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

        // Xử lý API Tỉnh/Thành - Quận/Huyện
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('district');
            const currentDistrict = document.getElementById('current_district').value;
            let provincesData = [];

            const normalizeString = (str) => {
                return str.toLowerCase().replace(/^(tỉnh|thành phố|tp\.|tp )\s*/, '').trim();
            };

            const loadDistricts = (selectedProvinceName) => {
                districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                districtSelect.disabled = true;

                if (!selectedProvinceName) return;

                const normalizedSelected = normalizeString(selectedProvinceName);
                const province = provincesData.find(p => {
                    const normAPI = normalizeString(p.name);
                    return normAPI === normalizedSelected || normAPI.includes(normalizedSelected) || normalizedSelected.includes(normAPI);
                });

                if (province && province.districts) {
                    province.districts.forEach(d => {
                        const option = document.createElement('option');
                        option.value = d.name;
                        option.textContent = d.name;
                        if (d.name === currentDistrict) {
                            option.selected = true;
                        }
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = false;
                } else {
                    districtSelect.disabled = false; // Fallback
                }
            };

            fetch('https://provinces.open-api.vn/api/?depth=2')
                .then(response => response.json())
                .then(data => {
                    provincesData = data;
                    loadDistricts(provinceSelect.value);
                })
                .catch(error => console.error('Error fetching provinces:', error));

            provinceSelect.addEventListener('change', function() {
                loadDistricts(this.value);
            });
        });

        // Validate form submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const fullNameInput = document.getElementById('full_name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
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

            const emailVal = emailInput.value.trim();
            if (emailVal.length === 0 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                errors.push('Email không hợp lệ');
                if (!focusedField) focusedField = emailInput;
            }

            const phoneVal = phoneInput.value.replace(/\s/g, '');
            if (phoneVal.length === 0 || !/^0[0-9]{9,10}$/.test(phoneVal)) {
                errors.push('Số điện thoại không hợp lệ');
                if (!focusedField) focusedField = phoneInput;
            }

            if (errors.length > 0) {
                e.preventDefault();
                showToast(errors[0], 'error');
                if (focusedField) {
                    focusedField.focus();
                }
            }
        });
        // Confirm Delete Account bằng Modal Nổi
        function confirmDeleteAccount() {
            const container = document.getElementById('toast-container');
            const modalHtml = `
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] flex items-center justify-center p-4 transition-opacity duration-300" id="confirm-modal-backdrop">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-transform duration-300 scale-95" id="confirm-modal-content">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-50 text-red-500 border-red-100 mb-5 border shadow-sm">
                            <span class="material-symbols-outlined text-[40px]">warning</span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3" style="font-family: 'Montserrat', sans-serif;">Xóa tài khoản</h3>
                        <p class="text-gray-600 mb-8 text-base px-2">Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.</p>
                        <div class="flex gap-4 w-full">
                            <button onclick="document.getElementById('toast-container').innerHTML=''" class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-semibold transition-colors shadow-sm">
                                Hủy
                            </button>
                            <button onclick="document.getElementById('delete-account-form').submit()" class="flex-1 px-4 py-3 bg-axeron-red text-white rounded-xl hover:bg-red-700 font-semibold transition-colors shadow-md">
                                Xác nhận xóa
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            container.innerHTML = modalHtml;

            requestAnimationFrame(() => {
                const wrapper = document.getElementById('confirm-modal-content');
                if (wrapper) {
                    wrapper.classList.remove('scale-95');
                    wrapper.classList.add('scale-100');
                }
            });
        }
    </script>
</body>
</html>
