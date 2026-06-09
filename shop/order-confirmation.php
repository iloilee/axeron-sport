<?php
/**
 * Order Confirmation - Xác nhận đơn hàng
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$orderId = (int)($_GET['id'] ?? 0);

if (!$orderId) {
    redirect(BASE_URL . '/');
}

// Get order
$order = $db->selectOne("
    SELECT o.*, s.province_city, s.base_price, s.estimated_days
    FROM orders o
    LEFT JOIN shipping_prices s ON o.shipping_id = s.shipping_id
    WHERE o.order_id = ? AND o.user_id = ?
", [$orderId, getUserId()]);

if (!$order) {
    redirect(BASE_URL . '/');
}

// Fetch already reviewed products by the user for completed orders
$reviewedProductIds = [];
$isDelivered = ($order['order_status'] === 'delivered');
if ($isDelivered && isLoggedIn()) {
    $reviewedList = $db->select("SELECT product_id FROM reviews WHERE user_id = ?", [getUserId()]);
    $reviewedProductIds = array_column($reviewedList, 'product_id');
}

// Get order items
$orderItems = $db->select("
    SELECT oi.*, pv.product_id, p.slug, pi.image_url
    FROM order_items oi
    LEFT JOIN product_variants pv ON oi.variant_id = pv.variant_id
    LEFT JOIN products p ON pv.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE oi.order_id = ?
    GROUP BY oi.order_item_id
", [$orderId]);

// Status labels
$statusLabels = [
    'pending' => ['text' => 'Chờ xác nhận', 'color' => 'bg-yellow-100 text-yellow-800'],
    'confirmed' => ['text' => 'Đã xác nhận', 'color' => 'bg-blue-100 text-blue-800'],
    'processing' => ['text' => 'Đang xử lý', 'color' => 'bg-blue-100 text-blue-800'],
    'shipped' => ['text' => 'Đang giao hàng', 'color' => 'bg-purple-100 text-purple-800'],
    'delivered' => ['text' => 'Đã giao hàng', 'color' => 'bg-green-100 text-green-800'],
    'cancelled' => ['text' => 'Đã hủy', 'color' => 'bg-red-100 text-red-800'],
    'returned' => ['text' => 'Trả hàng', 'color' => 'bg-gray-100 text-gray-800'],
];

$paymentLabels = [
    'cod' => 'Thanh toán khi nhận hàng (COD)',
    'bank_transfer' => 'Chuyển khoản ngân hàng',
    'momo' => 'Ví MoMo',
    'vnpay' => 'VNPay',
    'zalopay' => 'ZaloPay'
];

$currentStatus = $statusLabels[$order['order_status']] ?? ['text' => $order['order_status'], 'color' => 'bg-gray-100 text-gray-800'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Đặt hàng thành công - Axeron</title>
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
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="max-w-4xl mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <!-- Success Message -->
        <div class="text-center mb-12">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined text-6xl text-green-600">check_circle</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-4">Đặt hàng thành công!</h1>
            <p class="text-on-surface-variant text-lg">Cảm ơn bạn đã đặt hàng tại Axeron. Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.</p>
        </div>

        <!-- Order Info -->
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-6 border-b border-outline-variant">
                <div>
                    <p class="text-on-surface-variant text-sm">Mã đơn hàng</p>
                    <p class="font-headline-md text-headline-md text-on-surface font-bold">#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="px-4 py-2 rounded-full text-sm font-label-lg <?= $currentStatus['color'] ?>">
                        <?= $currentStatus['text'] ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Ngày đặt</p>
                    <p class="font-body-md text-on-surface"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-on-surface-variant text-sm mb-2">Phương thức thanh toán</p>
                    <p class="font-body-md text-on-surface"><?= $paymentLabels[$order['payment_method']] ?? $order['payment_method'] ?></p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-on-surface-variant text-sm mb-2">Địa chỉ giao hàng</p>
                    <p class="font-body-md text-on-surface"><?= htmlspecialchars($order['recipient_name']) ?> - <?= htmlspecialchars($order['recipient_phone']) ?><br><?= htmlspecialchars($order['shipping_address']) ?></p>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-6 mb-8">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6">Sản phẩm đã đặt</h2>

            <div class="space-y-4">
                <?php foreach ($orderItems as $item): ?>
                <div class="flex gap-4 items-center py-4 border-b border-outline-variant last:border-0">
                    <div class="w-20 h-20 bg-surface-variant rounded-lg flex-shrink-0 overflow-hidden">
                        <img alt="" class="w-full h-full object-cover" src="<?= htmlspecialchars(getImageUrl($item['image_url'], 'https://placehold.co/80x80/f0eded/5b403f?text=Product')) ?>"/>
                    </div>
                    <div class="flex-grow">
                        <h3 class="font-label-lg text-label-lg text-on-surface"><?= htmlspecialchars($item['product_name']) ?></h3>
                        <p class="text-on-surface-variant text-sm"><?= htmlspecialchars($item['variant_info'] ?? '') ?></p>
                        <p class="text-sm text-on-surface-variant">SL: <?= $item['quantity'] ?></p>
                        <?php if ($isDelivered && !empty($item['product_id'])): ?>
                            <div class="mt-2">
                                <?php if (in_array($item['product_id'], $reviewedProductIds)): ?>
                                    <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>#reviews-section" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-lg text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span>
                                        Xem đánh giá của bạn
                                    </a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>#reviews-section" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-axeron-red hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                        Đánh giá ngay
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <p class="font-headline-md text-headline-md text-axeron-red font-bold"><?= formatPrice($item['subtotal']) ?></p>
                        <p class="text-sm text-on-surface-variant"><?= formatPrice($item['unit_price']) ?> / sản phẩm</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container p-6 mb-8">
            <div class="space-y-3">
                <div class="flex justify-between text-on-surface-variant">
                    <span>Tạm tính</span>
                    <span><?= formatPrice($order['subtotal']) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div class="flex justify-between text-green-600">
                    <span>Giảm giá</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-on-surface-variant">
                    <span>Phí vận chuyển</span>
                    <span><?= $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'Miễn phí' ?></span>
                </div>
                <div class="flex justify-between items-center pt-4 border-t border-outline-variant">
                    <span class="font-headline-md text-headline-md text-on-surface font-bold">Tổng cộng</span>
                    <span class="font-headline-lg text-headline-lg text-axeron-red font-bold"><?= formatPrice($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="flex-1 sm:flex-none bg-axeron-red text-white px-8 py-4 rounded-lg font-bold text-center hover:bg-primary transition-colors">
                Tiếp tục mua sắm
            </a>
            <a href="<?= BASE_URL ?>/shop/order-history.php" class="flex-1 sm:flex-none border-2 border-axeron-red text-axeron-red px-8 py-4 rounded-lg font-bold text-center hover:bg-axeron-red hover:text-white transition-colors">
                Xem lịch sử đơn hàng
            </a>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
