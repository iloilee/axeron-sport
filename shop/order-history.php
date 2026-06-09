<?php
/**
 * Order History Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Require login
requireLogin();

$userId = getUserId();
$db = db();

// Get user orders
$orders = $db->select("
    SELECT
        o.order_id,
        o.total_amount,
        o.order_status,
        o.payment_status,
        o.created_at,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
", [$userId]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lịch Sử Đơn Hàng - Axeron Sport</title>
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
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark">Lịch Sử Đơn Hàng</h1>

        <?php if (empty($orders)): ?>
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high p-8 md:p-12 text-center">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">receipt_long</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Chưa có đơn hàng nào</h2>
            <p class="text-on-surface-variant mb-8">Hãy bắt đầu mua sắm để xem lịch sử đơn hàng tại đây.</p>
            <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-flex items-center gap-2 bg-axeron-red text-white px-6 py-3 rounded-lg font-label-lg hover:bg-primary transition-colors">
                Mua Sắm Ngay
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <?php else: ?>
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-container">
                        <tr>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Mã Đơn Hàng</th>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Ngày Đặt</th>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Sản Phẩm</th>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Tổng Tiền</th>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Trạng Thái</th>
                            <th class="text-left px-6 py-4 font-label-lg text-label-lg">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 font-label-lg text-label-lg font-medium text-on-surface">
                                <a href="<?= BASE_URL ?>/shop/order-confirmation.php?id=<?= $order['order_id'] ?>" class="text-axeron-blue hover:underline font-bold">
                                    #<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 font-body-md text-on-surface-variant"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td class="px-6 py-4 font-body-md text-on-surface"><?= $order['item_count'] ?> sản phẩm</td>
                            <td class="px-6 py-4 font-label-lg text-label-lg font-bold text-axeron-red"><?= formatPrice($order['total_amount']) ?></td>
                            <td class="px-6 py-4">
                                <?php
                                $statusClass = match($order['order_status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'shipped' => 'bg-purple-100 text-purple-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    'returned' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $statusText = match($order['order_status']) {
                                    'pending' => 'Chờ xử lý',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipped' => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy',
                                    'returned' => 'Trả hàng',
                                    default => $order['order_status']
                                };
                                ?>
                                <span class="px-3 py-1 rounded-full text-sm <?= $statusClass ?>"><?= $statusText ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="<?= BASE_URL ?>/shop/order-confirmation.php?id=<?= $order['order_id'] ?>" 
                                   class="text-axeron-blue hover:underline font-semibold text-sm inline-flex items-center gap-1">
                                    Chi tiết <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
