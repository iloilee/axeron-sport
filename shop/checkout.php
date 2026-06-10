<?php
/**
 * Checkout - Thanh toán
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();

    // Kiểm tra user có tồn tại không
    $userId = getUserId();
    $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
    if (!$userCheck) {
        // User không tồn tại - đăng xuất và chuyển về login
        logoutUser();
        setFlash('error', 'Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }

    $recipientName = sanitize($_POST['fullname'] ?? '');
    $recipientPhone = sanitize($_POST['phone'] ?? '');
    $province = sanitize($_POST['province'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $ward = sanitize($_POST['ward'] ?? '');
    $streetAddress = sanitize($_POST['address'] ?? '');
    $shippingId = (int)($_POST['shipping'] ?? 1);
    $paymentMethod = sanitize($_POST['payment'] ?? 'cod');
    $note = sanitize($_POST['note'] ?? '');

    // Validate
    if (empty($recipientName) || empty($recipientPhone) || empty($streetAddress) || empty($province)) {
        setFlash('error', 'Vui lòng điền đầy đủ thông tin giao hàng');
        redirect(BASE_URL . '/shop/checkout.php');
    }

    // Get cart items
    $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);
    if (!$cart) {
        setFlash('error', 'Giỏ hàng trống');
        redirect(BASE_URL . '/shop/cart.php');
    }

    $cartItems = $db->select("
        SELECT ci.*, pv.variant_id, pv.stock_quantity, p.product_name, p.base_price, pv.extra_price, pv.color, pv.size
        FROM cart_items ci
        JOIN product_variants pv ON ci.variant_id = pv.variant_id
        JOIN products p ON pv.product_id = p.product_id
        WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0
    ", [$cart['cart_id']]);

    if (empty($cartItems)) {
        setFlash('error', 'Giỏ hàng trống');
        redirect(BASE_URL . '/shop/cart.php');
    }

    // Calculate totals
    $subtotal = 0;
    $shippingFee = 0;
    $discountAmount = 0;

    foreach ($cartItems as $item) {
        $subtotal += $item['quantity'] * ($item['base_price'] + $item['extra_price']);
    }

    // Get shipping rate
    $shipping = $db->selectOne("SELECT * FROM shipping_prices WHERE shipping_id = ?", [$shippingId]);
    if ($shipping) {
        $shippingFee = (float)$shipping['base_price'];
    } else {
        $shippingFee = 30000;
    }

    // Apply promo if exists
    $promoId = null;
    if (isset($_SESSION['checkout_promo'])) {
        $promoId = $_SESSION['checkout_promo']['promo_id'];
        $promo = $_SESSION['checkout_promo'];

        if ($subtotal >= $promo['min_order_value']) {
            if ($promo['discount_type'] === 'percent') {
                $discountAmount = $subtotal * ($promo['discount_value'] / 100);
                if ($promo['max_discount']) {
                    $discountAmount = min($discountAmount, $promo['max_discount']);
                }
            } else {
                $discountAmount = $promo['discount_value'];
            }
        }
    }

    $totalAmount = $subtotal + $shippingFee - $discountAmount;

    // Create order
    $shippingAddress = "$streetAddress, $ward, $district, $province";

    // Save address if requested
    $saveAddress = isset($_POST['save_address']) && $_POST['save_address'] == '1';
    if ($saveAddress && $userId) {
        $existingAddress = $db->selectOne("SELECT address_id FROM user_addresses WHERE user_id = ? AND is_default = 1", [$userId]);
        if (!$existingAddress) {
            $db->update("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
            $db->insert(
                "INSERT INTO user_addresses (user_id, recipient_name, phone, province, district, ward, street_address, is_default, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())",
                [$userId, $recipientName, $recipientPhone, $province, $district, $ward, $streetAddress]
            );
        }
    }

    try {
        $db->beginTransaction();

        // Kiểm tra tồn kho lần cuối ngay trước khi tạo đơn hàng
        foreach ($cartItems as $item) {
            $currentStock = $db->selectOne("SELECT stock_quantity FROM product_variants WHERE variant_id = ? FOR UPDATE", [$item['variant_id']]);
            if (!$currentStock || $currentStock['stock_quantity'] < $item['quantity']) {
                throw new Exception("Sản phẩm hiện không đủ số lượng trong kho.");
            }
        }

        // Create order
        $orderId = $db->insert("
            INSERT INTO orders (user_id, shipping_id, promo_id, recipient_name, recipient_phone, shipping_address,
                subtotal, discount_amount, shipping_fee, total_amount, order_status, payment_method, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
        ", [$userId, $shippingId, $promoId, $recipientName, $recipientPhone, $shippingAddress,
            $subtotal, $discountAmount, $shippingFee, $totalAmount, $paymentMethod, $note]);

        // Create order items
        foreach ($cartItems as $item) {
            $productSnapshot = $db->selectOne("SELECT product_name FROM products WHERE product_id = (
                SELECT product_id FROM product_variants WHERE variant_id = ?
            )", [$item['variant_id']]);

            $db->insert("
                INSERT INTO order_items (order_id, variant_id, product_name, variant_info, unit_price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [
                $orderId,
                $item['variant_id'],
                $productSnapshot['product_name'],
                ($item['color'] ?? '') . ' - Size ' . ($item['size'] ?? ''),
                $item['base_price'] + $item['extra_price'],
                $item['quantity'],
                $item['quantity'] * ($item['base_price'] + $item['extra_price'])
            ]);

            // Update stock
            $db->update("
                UPDATE product_variants SET stock_quantity = stock_quantity - ?
                WHERE variant_id = ?
            ", [$item['quantity'], $item['variant_id']]);
        }

        // Clear cart
        $db->delete("DELETE FROM cart_items WHERE cart_id = ?", [$cart['cart_id']]);

        // Update promo usage
        if ($promoId) {
            $db->update("UPDATE promotions SET used_count = used_count + 1 WHERE promo_id = ?", [$promoId]);
            unset($_SESSION['checkout_promo']);
        }

        // Log status
        $db->insert("
            INSERT INTO order_status_logs (order_id, new_status, changed_at)
            VALUES (?, 'pending', NOW())
        ", [$orderId]);

        $db->commit();

        // Update cart count
        updateCartCount();

        // Redirect to confirmation
        redirect(BASE_URL . '/shop/order-confirmation.php?id=' . $orderId);

    } catch (Exception $e) {
        $db->rollback();
        error_log('Checkout Error: ' . $e->getMessage());
        
        if ($e->getMessage() === "Sản phẩm hiện không đủ số lượng trong kho.") {
            setFlash('error', $e->getMessage());
            redirect(BASE_URL . '/shop/cart.php');
        } else {
            setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
}

// Get cart items
$cartItems = [];
$subtotal = 0;

// Nếu chưa đăng nhập và có session cart, yêu cầu đăng nhập trước
if (!isLoggedIn() && !empty($_SESSION['cart'])) {
    setFlash('info', 'Vui lòng đăng nhập để tiến hành thanh toán');
    header('Location: ' . BASE_URL . '/auth/login.php?redirect=' . urlencode(BASE_URL . '/shop/checkout.php'));
    exit;
}

// Kiểm tra user có tồn tại không (tránh lỗi foreign key khi re-import database)
$userId = getUserId();
$userValid = false;
if (isLoggedIn() && $userId) {
    $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
    $userValid = !empty($userCheck);
}

if (isLoggedIn() && $userValid) {
    // User hợp lệ - merge session cart vào database cart trước khi lấy items
    if (!empty($_SESSION['cart'])) {
        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

        if (!$cart) {
            // Tạo cart mới cho user
            $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);
            $cartId = $db->lastInsertId();
        } else {
            $cartId = $cart['cart_id'];
        }

        // Merge từng item vào cart
        foreach ($_SESSION['cart'] as $sessionItem) {
            $variant = $db->selectOne("SELECT stock_quantity FROM product_variants WHERE variant_id = ? AND is_active = 1 AND is_deleted = 0", [$sessionItem['variant_id']]);
            $maxQty = $variant ? $variant['stock_quantity'] : 0;

            if ($maxQty <= 0) continue;

            $existing = $db->selectOne("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?", [$cartId, $sessionItem['variant_id']]);

            if ($existing) {
                // Cộng dồn số lượng
                $newQty = min($existing['quantity'] + $sessionItem['quantity'], $maxQty);
                $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$newQty, $existing['cart_item_id']]);
            } else {
                // Thêm mới
                $qty = min($sessionItem['quantity'], $maxQty);
                if ($qty > 0) {
                    $db->insert("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)", [$cartId, $sessionItem['variant_id'], $qty]);
                }
            }
        }

        // Xóa session cart sau khi merge
        unset($_SESSION['cart']);
        updateCartCount();
    }

    $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

    if ($cart) {
        $cartItems = $db->select("
            SELECT ci.*, pv.variant_id, pv.color, pv.size, pv.extra_price,
                p.product_id, p.product_name, p.slug,
                p.base_price, pv.stock_quantity,
                pi.image_url,
                (p.base_price + pv.extra_price) as unit_price,
                (ci.quantity * (p.base_price + pv.extra_price)) as item_total
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0
        ", [$cart['cart_id']]);

        foreach ($cartItems as $item) {
            $subtotal += $item['item_total'];
        }
    }
} else if (!empty($_SESSION['cart'])) {
    // User không hợp lệ (do re-import database) nhưng có session cart - hiển thị từ session
    foreach ($_SESSION['cart'] as $sessionItem) {
        $item = $db->selectOne("
            SELECT pv.variant_id, pv.color, pv.size, pv.extra_price, pv.stock_quantity,
                p.product_id, p.product_name, p.slug, p.base_price,
                pi.image_url,
                (p.base_price + pv.extra_price) as unit_price,
                (? * (p.base_price + pv.extra_price)) as item_total
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE pv.variant_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0
        ", [$sessionItem['quantity'], $sessionItem['variant_id']]);

        if ($item) {
            $item['quantity'] = min($sessionItem['quantity'], $item['stock_quantity']);
            $item['cart_item_id'] = $sessionItem['variant_id']; // Dùng variant_id làm key tạm
            $cartItems[] = $item;
            $subtotal += $item['item_total'];
        }
    }
}

// Get all shipping rates
$shippingPrices = $db->select("SELECT * FROM shipping_prices ORDER BY province_city ASC");

// Get user data if logged in
$userData = getUserData();

// Get user's default shipping address if logged in
$defaultAddress = null;
if (isLoggedIn() && $userValid) {
    $defaultAddress = $db->selectOne("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1", [$userId]);
}
$defaultProvince = $defaultAddress ? $defaultAddress['province'] : '';

// Apply promo if exists in session
$discountAmount = 0;
$promoCode = '';

if (isset($_SESSION['checkout_promo'])) {
    $promo = $_SESSION['checkout_promo'];
    if ($subtotal >= $promo['min_order_value']) {
        $promoCode = $promo['promo_code'];
        if ($promo['discount_type'] === 'percent') {
            $discountAmount = $subtotal * ($promo['discount_value'] / 100);
            if ($promo['max_discount']) {
                $discountAmount = min($discountAmount, $promo['max_discount']);
            }
        } else {
            $discountAmount = $promo['discount_value'];
        }
    }
}

// Calculate totals based on default province or default fallback (shipping_id = 1, TP. Hồ Chí Minh)
$shippingFee = 30000;
$selectedShippingId = 1;

if (!empty($defaultProvince)) {
    $sp = $db->selectOne("SELECT * FROM shipping_prices WHERE province_city = ?", [$defaultProvince]);
    if ($sp) {
        $shippingFee = (float)$sp['base_price'];
        $selectedShippingId = $sp['shipping_id'];
    }
} else {
    $sp = $db->selectOne("SELECT * FROM shipping_prices WHERE shipping_id = 1");
    if ($sp) {
        $shippingFee = (float)$sp['base_price'];
        $selectedShippingId = $sp['shipping_id'];
    }
}
$totalAmount = $subtotal + $shippingFee - $discountAmount;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Thanh Toán - Axeron</title>
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
                        "on-background": "#1b1c1c", "inverse-surface": "#303030", "text-dark": "#212121",
                        "tertiary-container": "#006a85", background: "#fcf9f8", "on-primary-fixed-variant": "#930019",
                        "inverse-primary": "#ffb3b0", "secondary-fixed-dim": "#b0c6ff", "error-container": "#ffdad6",
                        "outline-variant": "#e3bebb", "tertiary-fixed": "#baeaff", "on-secondary": "#ffffff",
                        "secondary-fixed": "#d9e2ff", "on-primary-fixed": "#410006", "surface-container": "#f0eded",
                        error: "#ba1a1a", "axeron-red": "#BE1E2D", "on-tertiary": "#ffffff",
                        "surface-dim": "#dcd9d9", "on-primary-container": "#ffd3d1", "secondary-container": "#0f6df3",
                        "surface-tint": "#b91a2a", primary: "#98001b", "surface-gray": "#F5F5F5",
                        "surface-bright": "#fcf9f8", "surface-container-highest": "#e5e2e1", "on-surface": "#1b1c1c",
                        white: "#FFFFFF", tertiary: "#005066", "surface-container-high": "#eae7e7",
                        "on-error-container": "#93000a", "primary-container": "#be1e2d", "primary-fixed": "#ffdad8",
                        "surface-container-lowest": "#ffffff", "inverse-on-surface": "#f3f0ef",
                        "on-tertiary-container": "#abe6ff", "surface-variant": "#e5e2e1",
                        "on-secondary-container": "#fefcff", secondary: "#0056c5", outline: "#8f6f6e",
                        "axeron-blue": "#2979FF", "tertiary-fixed-dim": "#85d1ef", surface: "#fcf9f8",
                        "on-secondary-fixed-variant": "#00429b", "on-tertiary-fixed": "#001f29",
                        "on-tertiary-fixed-variant": "#004d62", "surface-container-low": "#f6f3f2",
                        "on-secondary-fixed": "#001945", "primary-fixed-dim": "#ffb3b0",
                        "on-surface-variant": "#5b403f", "on-primary": "#ffffff", "on-error": "#ffffff"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { "margin-desktop": "24px", gutter: "16px", "container-max": "1200px", base: "8px", "margin-mobile": "16px" },
                    fontFamily: { "body-lg": ["Noto Sans", "sans-serif"], "headline-lg-mobile": ["Montserrat", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "display-lg": ["Montserrat", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg": ["Montserrat", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"] },
                    fontSize: { "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }], "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }], "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }], "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }], "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }], "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }], "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }], "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }] }
                }
            }
        };
    </script>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 md:py-12">
        <h1 class="font-headline-lg text-headline-lg mb-8 uppercase text-center md:text-left">Thanh Toán</h1>

        <?php if (empty($cartItems)): ?>
            <div class="text-center py-16">
                <span class="material-symbols-outlined text-8xl text-on-surface-variant mb-6">shopping_cart</span>
                <h2 class="font-headline-lg text-2xl text-on-surface mb-4">Giỏ hàng trống</h2>
                <p class="text-on-surface-variant mb-8">Bạn cần thêm sản phẩm vào giỏ hàng trước khi thanh toán</p>
                <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-flex items-center gap-2 bg-axeron-red text-white px-8 py-4 rounded-lg font-bold hover:bg-primary transition-colors">
                    Tiếp tục mua sắm
                </a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="flex flex-col lg:flex-row gap-gutter lg:gap-8 items-start">
                    <!-- Left Column: Forms -->
                    <div class="w-full lg:w-2/3 flex flex-col gap-8">
                        <!-- Thông tin giao hàng -->
                        <section class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container shadow-sm">
                            <h2 class="font-headline-md text-headline-md mb-6 flex items-center gap-2 border-b border-surface-variant pb-4">
                                <span class="material-symbols-outlined text-axeron-red">local_shipping</span>
                                Thông tin giao hàng
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="fullname">Họ và tên *</label>
                                    <input class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md" id="fullname" name="fullname" placeholder="Nhập họ và tên" type="text" value="<?= htmlspecialchars($userData['full_name'] ?? '') ?>" required/>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="phone">Số điện thoại *</label>
                                    <input class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md" id="phone" name="phone" placeholder="Nhập số điện thoại" type="tel" value="<?= htmlspecialchars($defaultAddress['phone'] ?? '') ?>" required/>
                                </div>
                                <div class="flex flex-col gap-1 md:col-span-2">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="address">Địa chỉ cụ thể *</label>
                                    <input class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md" id="address" name="address" placeholder="Số nhà, tên đường, phường/xã" type="text" value="<?= htmlspecialchars($defaultAddress['street_address'] ?? '') ?>" required/>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="province">Tỉnh/Thành phố *</label>
                                    <select class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md appearance-none" id="province" name="province" required>
                                        <option value="">Chọn Tỉnh/Thành</option>
                                        <?php foreach ($shippingPrices as $sp): ?>
                                            <option value="<?= htmlspecialchars($sp['province_city']) ?>" <?= $defaultProvince === $sp['province_city'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($sp['province_city']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="district">Quận/Huyện</label>
                                    <input class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md" id="district" name="district" placeholder="Quận/Huyện" type="text" value="<?= htmlspecialchars($defaultAddress['district'] ?? '') ?>"/>
                                </div>
                                <div class="flex flex-col gap-1 md:col-span-2">
                                    <label class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide" for="note">Ghi chú đơn hàng</label>
                                    <textarea class="px-4 py-3 bg-surface border border-outline-variant rounded focus:outline-none focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue transition-colors w-full font-body-md" id="note" name="note" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..." rows="2"></textarea>
                                </div>
                                <?php if (isLoggedIn() && $userValid && !$defaultAddress): ?>
                                <div class="flex items-center gap-2 mt-2 md:col-span-2">
                                    <input type="checkbox" id="save_address" name="save_address" value="1" class="w-4 h-4 text-axeron-red focus:ring-axeron-blue border-outline rounded cursor-pointer">
                                    <label for="save_address" class="font-body-md text-body-md text-on-surface-variant cursor-pointer">Lưu thông tin giao hàng cho lần sau</label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </section>

                        <!-- Shipping & Payment -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter lg:gap-8">
                            <!-- Vận chuyển -->
                            <section class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container shadow-sm flex flex-col h-full">
                                <h2 class="font-headline-md text-headline-md mb-6 flex items-center gap-2 border-b border-surface-variant pb-4">
                                    <span class="material-symbols-outlined text-axeron-red">speed</span>
                                    Vận chuyển
                                </h2>
                                <div class="flex flex-col gap-3 flex-grow justify-center" id="shipping-method-info">
                                    <div class="p-4 border border-axeron-blue bg-secondary-fixed/20 rounded-lg flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="font-label-lg text-label-lg" id="shipping-display-name">Phí vận chuyển</span>
                                            <span class="font-body-md text-body-md text-on-surface-variant text-sm" id="shipping-display-days">Đang tính toán...</span>
                                        </div>
                                        <span class="font-label-lg text-label-lg text-axeron-red" id="shipping-display-fee">0₫</span>
                                    </div>
                                    <input type="hidden" name="shipping" id="selected_shipping_id" value="<?= $selectedShippingId ?>">
                                </div>
                            </section>

                            <!-- Thanh toán -->
                            <section class="bg-surface-container-lowest p-6 rounded-xl border border-surface-container shadow-sm flex flex-col h-full">
                                <h2 class="font-headline-md text-headline-md mb-6 flex items-center gap-2 border-b border-surface-variant pb-4">
                                    <span class="material-symbols-outlined text-axeron-red">payments</span>
                                    Thanh toán
                                </h2>
                                <div class="flex flex-col gap-3 flex-grow">
                                    <label class="flex items-center gap-3 p-4 border border-axeron-blue bg-secondary-fixed/20 rounded cursor-pointer transition-colors relative overflow-hidden group">
                                        <input class="text-axeron-blue focus:ring-axeron-blue w-5 h-5" name="payment" type="radio" value="cod" checked/>
                                        <div class="flex items-center gap-3 relative z-10">
                                            <span class="material-symbols-outlined text-on-surface-variant">wallet</span>
                                            <span class="font-label-lg text-label-lg">Thanh toán khi nhận hàng (COD)</span>
                                        </div>
                                        <div class="absolute inset-0 bg-axeron-blue opacity-0 group-hover:opacity-5 transition-opacity"></div>
                                    </label>
                                    <label class="flex items-center gap-3 p-4 border border-outline-variant rounded cursor-pointer hover:border-axeron-blue transition-colors relative overflow-hidden group">
                                        <input class="text-axeron-blue focus:ring-axeron-blue w-5 h-5" name="payment" type="radio" value="bank_transfer"/>
                                        <div class="flex items-center gap-3 relative z-10">
                                            <span class="material-symbols-outlined text-on-surface-variant">account_balance</span>
                                            <span class="font-label-lg text-label-lg">Chuyển khoản ngân hàng</span>
                                        </div>
                                        <div class="absolute inset-0 bg-axeron-blue opacity-0 group-hover:opacity-5 transition-opacity"></div>
                                    </label>
                                </div>
                            </section>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="w-full lg:w-1/3 sticky top-24">
                        <aside class="bg-surface-container-lowest/80 backdrop-blur-md p-6 rounded-xl border border-surface-container shadow-lg">
                            <h2 class="font-headline-md text-headline-md mb-6 uppercase tracking-wider">Đơn hàng của bạn</h2>

                            <!-- Products -->
                            <div class="flex flex-col gap-4 mb-6 border-b border-surface-variant pb-6">
                                <?php foreach ($cartItems as $item): ?>
                                <div class="flex gap-4 items-center">
                                    <div class="w-16 h-16 bg-surface-variant rounded flex-shrink-0 relative overflow-hidden">
                                        <img alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-full h-full object-cover"
                                            src="<?= htmlspecialchars(getImageUrl($item['image_url'], 'https://placehold.co/64x64/f0eded/5b403f?text=Product')) ?>"/>
                                        <span class="absolute top-0 right-0 bg-on-background text-white text-xs font-bold px-1.5 py-0.5 rounded-bl"><?= $item['quantity'] ?></span>
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="font-label-lg text-label-lg leading-tight line-clamp-2"><?= htmlspecialchars($item['product_name']) ?></h3>
                                        <p class="text-sm text-on-surface-variant"><?= htmlspecialchars($item['color'] ?? '') ?> | Size: <?= htmlspecialchars($item['size'] ?? '') ?></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-label-lg text-label-lg text-axeron-red"><?= formatPrice($item['item_total']) ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Totals -->
                            <div class="flex flex-col gap-2 mb-8">
                                <div class="flex justify-between font-body-md text-body-md text-on-surface-variant">
                                    <span>Tạm tính</span>
                                    <span id="checkout-subtotal"><?= formatPrice($subtotal) ?></span>
                                </div>
                                <?php if ($discountAmount > 0): ?>
                                <div class="flex justify-between font-body-md text-body-md text-green-600 font-semibold">
                                    <span>Khuyến mãi (<?= htmlspecialchars($promoCode) ?>)</span>
                                    <span id="checkout-discount">-<?= formatPrice($discountAmount) ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="flex justify-between font-body-md text-body-md text-on-surface-variant">
                                    <span>Phí vận chuyển</span>
                                    <span id="checkout-shipping"><?= $shippingFee > 0 ? formatPrice($shippingFee) : 'Miễn phí' ?></span>
                                </div>
                                <div class="flex justify-between items-end mt-4 pt-4 border-t border-outline-variant">
                                    <span class="font-headline-md text-headline-md uppercase">Tổng cộng</span>
                                    <div class="flex flex-col items-end">
                                        <span class="font-headline-lg text-headline-lg text-axeron-red font-bold" id="checkout-total"><?= formatPrice($totalAmount) ?></span>
                                        <span class="text-xs text-on-surface-variant">Đã bao gồm VAT</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button type="submit" class="w-full bg-axeron-red text-white py-4 rounded-lg font-headline-md text-headline-md uppercase tracking-wider hover:bg-primary transition-colors duration-200 flex items-center justify-center gap-2 group shadow-md hover:shadow-lg">
                                ĐẶT HÀNG
                                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </button>

                            <p class="text-xs text-center mt-4 text-on-surface-variant">
                                Bằng việc đặt hàng, bạn đồng ý với <a class="text-axeron-blue hover:underline" href="<?= BASE_URL ?>/policies/purchase-policy.php">Điều khoản & Dịch vụ</a> của chúng tôi.
                            </p>
                        </aside>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        const shippingPrices = <?= json_encode($shippingPrices) ?>;
        const provinceSelect = document.getElementById('province');
        const shippingIdInput = document.getElementById('selected_shipping_id');

        function updateShippingFee() {
            const province = provinceSelect.value;
            const subtotal = <?= $subtotal ?>;
            const discount = <?= $discountAmount ?>;
            
            // Tìm cấu hình phí vận chuyển cho tỉnh/thành tương ứng, nếu không tìm thấy thì dùng dòng đầu tiên hoặc mặc định (ID 1)
            const rate = shippingPrices.find(sp => sp.province_city === province) || shippingPrices.find(sp => sp.shipping_id == 1);
            
            if (rate) {
                const shippingFee = parseFloat(rate.base_price);
                shippingIdInput.value = rate.shipping_id;
                
                // Cập nhật thông tin block Vận chuyển
                document.getElementById('shipping-display-name').textContent = 'Giao hàng đến ' + rate.province_city;
                document.getElementById('shipping-display-days').textContent = 'Dự kiến: ' + rate.estimated_days + ' ngày làm việc';
                document.getElementById('shipping-display-fee').textContent = shippingFee > 0 ? new Intl.NumberFormat('vi-VN').format(shippingFee) + '₫' : 'Miễn phí';
                
                // Cập nhật ở sidebar tóm tắt đơn hàng
                document.getElementById('checkout-shipping').textContent = shippingFee > 0 ? new Intl.NumberFormat('vi-VN').format(shippingFee) + '₫' : 'Miễn phí';
                
                const total = subtotal + shippingFee - discount;
                document.getElementById('checkout-total').textContent = new Intl.NumberFormat('vi-VN').format(total) + '₫';
            }
        }

        if (provinceSelect) {
            provinceSelect.addEventListener('change', updateShippingFee);
            // Chạy ban đầu khi load trang
            updateShippingFee();
        }
    </script>
</body>
</html>
