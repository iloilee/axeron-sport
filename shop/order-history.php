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

// Handle Search & Filter
$statusFilter = $_GET['status'] ?? '';
$searchCode = $_GET['code'] ?? '';

$whereParams = [$userId];
$whereClause = "WHERE o.user_id = ?";

if ($statusFilter) {
    $whereClause .= " AND o.order_status = ?";
    $whereParams[] = $statusFilter;
}

if ($searchCode) {
    $whereClause .= " AND o.order_code LIKE ?";
    $whereParams[] = '%' . $searchCode . '%';
}

// Get user orders
$orders = $db->select("
    SELECT
        o.order_id,
        o.order_code,
        o.total_amount,
        o.order_status,
        o.payment_method,
        o.payment_status,
        o.created_at,
        sm.method_name as shipping_method_name,
        COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN shipping_methods sm ON o.shipping_method_id = sm.method_id
    $whereClause
    GROUP BY o.order_id
    ORDER BY o.created_at DESC
", $whereParams);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lịch Sử Đơn Hàng - Axeron Sport</title>
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
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased flex flex-col min-h-screen">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-[1400px] mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold uppercase text-text-dark m-0">Lịch Sử Đơn Hàng</h1>
            
            <form action="" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <input type="text" name="code" value="<?= htmlspecialchars($searchCode) ?>" placeholder="Mã đơn hàng..." class="border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-axeron-red outline-none min-w-[200px]">
                <select name="status" class="border border-outline-variant rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-axeron-red outline-none min-w-[165px]">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                    <option value="confirmed" <?= $statusFilter == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                    <option value="processing" <?= $statusFilter == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                    <option value="shipped" <?= $statusFilter == 'shipped' ? 'selected' : '' ?>>Đang giao</option>
                    <option value="delivered" <?= $statusFilter == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                    <option value="cancelled" <?= $statusFilter == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    <option value="returned" <?= $statusFilter == 'returned' ? 'selected' : '' ?>>Trả hàng</option>
                </select>
                <button type="submit" class="bg-axeron-red text-white px-4 py-2 rounded-lg font-medium hover:bg-primary transition-colors whitespace-nowrap">
                    Tìm kiếm
                </button>
            </form>
        </div>

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
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Mã Đơn Hàng</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Ngày Đặt</th>
                            <th class="text-center px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Sản Phẩm</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Tổng Tiền</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Phương thức vận chuyển</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Thanh toán</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Trạng Thái</th>
                            <th class="text-left px-4 py-4 font-label-lg text-label-lg whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-4 py-4 font-label-lg text-label-lg font-medium text-on-surface whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/shop/order-confirmation.php?id=<?= $order['order_id'] ?>" class="text-axeron-blue hover:underline font-bold">
                                    <?= htmlspecialchars($order['order_code']) ?>
                                </a>
                            </td>
                            <td class="px-4 py-4 font-body-md text-on-surface-variant whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td class="px-4 py-4 font-body-md text-on-surface whitespace-nowrap text-center"><?= $order['item_count'] ?></td>
                            <td class="px-4 py-4 font-label-lg text-label-lg font-bold text-axeron-red whitespace-nowrap"><?= formatPrice($order['total_amount']) ?></td>
                            <td class="px-4 py-4 font-body-md whitespace-nowrap font-semibold">
                                <?php
                                $shippingName = htmlspecialchars($order['shipping_method_name'] ?? 'Giao hàng tiêu chuẩn');
                                $shippingColor = match($shippingName) {
                                    'Giao hàng tiêu chuẩn' => 'text-[#6B7280]',
                                    'Giao nhanh (Express)' => 'text-[#8B5CF6]',
                                    'Nhận tại cửa hàng' => 'text-[#22C55E]',
                                    default => 'text-on-surface'
                                };
                                ?>
                                <span class="<?= $shippingColor ?>"><?= $shippingName ?></span>
                            </td>
                            <td class="px-4 py-4 font-body-md font-semibold whitespace-nowrap">
                                <?php
                                $paymentText = match($order['payment_method']) {
                                    'cod' => 'Thanh toán khi nhận hàng (COD)',
                                    'payos', 'bank_transfer' => 'Thanh toán QR/Chuyển khoản (PayOS)',
                                    'momo' => 'Ví MoMo',
                                    'vnpay' => 'VNPay',
                                    'zalopay' => 'ZaloPay',
                                    default => strtoupper($order['payment_method'])
                                };
                                
                                $paymentColor = match($order['payment_method']) {
                                    'cod' => 'text-[#F59E0B]',
                                    'payos', 'bank_transfer' => 'text-[#2563EB]',
                                    default => 'text-on-surface'
                                };
                                
                                $paymentStatusText = match($order['payment_status']) {
                                    'paid' => 'Đã thanh toán',
                                    'unpaid' => 'Chưa thanh toán',
                                    'refunded' => 'Đã hoàn tiền',
                                    default => 'Chưa thanh toán'
                                };
                                
                                $paymentStatusClass = match($order['payment_status']) {
                                    'paid' => 'bg-green-100 text-green-800',
                                    'unpaid' => 'bg-yellow-100 text-yellow-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                                ?>
                                <span class="<?= $paymentColor ?> block mb-1"><?= $paymentText ?></span>
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $paymentStatusClass ?>"><?= $paymentStatusText ?></span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
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
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="<?= BASE_URL ?>/shop/order-confirmation.php?id=<?= $order['order_id'] ?>" 
                                       class="text-axeron-blue hover:bg-blue-50 p-2 rounded-lg transition-colors inline-flex items-center justify-center" title="Chi tiết">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </a>
                                    <?php if (in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                    <button onclick="showCancelModal(<?= $order['order_id'] ?>)" 
                                            class="text-axeron-red hover:bg-red-50 p-2 rounded-lg transition-colors inline-flex items-center justify-center" title="Hủy đơn hàng">
                                        <span class="material-symbols-outlined text-[20px]">close</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Cancel Order Modal -->
        <div id="cancelModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex justify-between items-center border-b border-gray-200 pb-3 mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Bạn có chắc muốn hủy đơn hàng này?</h3>
                    <button onclick="hideCancelModal()" class="text-gray-500 hover:text-red-500">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form id="cancelOrderForm" onsubmit="submitCancelOrder(event)">
                    <input type="hidden" id="cancel_order_id" name="order_id" value="">
                    <p class="text-gray-600 mb-3 text-sm">Vui lòng chọn lý do hủy đơn (Bắt buộc):</p>
                    <div class="space-y-3 mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="cancel_reason" value="Đặt nhầm sản phẩm" class="text-axeron-red focus:ring-axeron-red" required>
                            <span>Đặt nhầm sản phẩm</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="cancel_reason" value="Muốn thay đổi địa chỉ" class="text-axeron-red focus:ring-axeron-red">
                            <span>Muốn thay đổi địa chỉ</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="cancel_reason" value="Tìm được sản phẩm khác" class="text-axeron-red focus:ring-axeron-red">
                            <span>Tìm được sản phẩm khác</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="cancel_reason" value="other" class="text-axeron-red focus:ring-axeron-red" onchange="toggleOtherReason(this)">
                            <span>Khác</span>
                        </label>
                        <input type="text" id="other_reason_text" class="w-full border border-gray-300 rounded px-3 py-2 mt-2 hidden focus:ring-2 focus:ring-axeron-red outline-none" placeholder="Nhập lý do của bạn..." disabled>
                    </div>
                    
                    <div class="flex gap-3 justify-end">
                        <button type="button" onclick="hideCancelModal()" class="px-5 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">Đóng</button>
                        <button type="submit" class="px-5 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 font-medium flex items-center gap-2" id="btnCancelSubmit">
                            Xác nhận hủy
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function showCancelModal(orderId) {
                document.getElementById('cancel_order_id').value = orderId;
                document.getElementById('cancelModal').classList.remove('hidden');
            }
            function hideCancelModal() {
                document.getElementById('cancelModal').classList.add('hidden');
            }
            function toggleOtherReason(radio) {
                const textInput = document.getElementById('other_reason_text');
                if (radio.checked && radio.value === 'other') {
                    textInput.classList.remove('hidden');
                    textInput.disabled = false;
                    textInput.required = true;
                } else {
                    textInput.classList.add('hidden');
                    textInput.disabled = true;
                    textInput.required = false;
                }
            }
            
            document.querySelectorAll('input[name="cancel_reason"]:not([value="other"])').forEach(radio => {
                radio.addEventListener('change', () => {
                    document.getElementById('other_reason_text').classList.add('hidden');
                    document.getElementById('other_reason_text').disabled = true;
                    document.getElementById('other_reason_text').required = false;
                });
            });

            function showCenterToast(message, type = 'success') {
                let container = document.getElementById('center-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'center-toast-container';
                    container.className = 'fixed inset-0 pointer-events-none z-[9999] flex flex-col items-center justify-center gap-4';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                const bgColor = 'bg-white';
                const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
                const iconName = type === 'success' ? 'check_circle' : 'error';

                toast.className = `${bgColor} border border-gray-100 pointer-events-auto px-8 py-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col items-center gap-3 transform transition-all duration-300 scale-95 opacity-0 min-w-[320px] text-center`;
                toast.innerHTML = `
                    <span class="material-symbols-outlined text-5xl ${iconColor}">${iconName}</span>
                    <span class="text-gray-800 font-medium text-lg">${message}</span>
                `;

                container.appendChild(toast);

                setTimeout(() => {
                    toast.classList.remove('scale-95', 'opacity-0');
                    toast.classList.add('scale-100', 'opacity-100');
                }, 10);

                setTimeout(() => {
                    toast.classList.remove('scale-100', 'opacity-100');
                    toast.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 2000);
            }

            async function submitCancelOrder(e) {
                e.preventDefault();
                const form = e.target;
                let reason = form.cancel_reason.value;
                if (reason === 'other') {
                    reason = document.getElementById('other_reason_text').value.trim();
                }
                
                if (!reason) {
                    showCenterToast('Vui lòng chọn hoặc nhập lý do.', 'error');
                    return;
                }
                
                const btn = document.getElementById('btnCancelSubmit');
                btn.disabled = true;
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin">refresh</span> Đang xử lý...';
                
                try {
                    const formData = new FormData();
                    formData.append('order_id', document.getElementById('cancel_order_id').value);
                    formData.append('reason', reason);
                    
                    const response = await fetch('<?= BASE_URL ?>/api/cancel_order.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        hideCancelModal();
                        showCenterToast(result.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showCenterToast(result.message || 'Lỗi hệ thống', 'error');
                        btn.disabled = false;
                        btn.innerHTML = 'Xác nhận hủy';
                    }
                } catch (error) {
                    showCenterToast('Lỗi kết nối mạng', 'error');
                    console.error(error);
                    btn.disabled = false;
                    btn.innerHTML = 'Xác nhận hủy';
                }
            }
        </script>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
