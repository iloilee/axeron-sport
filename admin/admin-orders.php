<?php
/**
 * Admin Orders Management
 */

// Load orders
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$timeFilter = $_GET['time'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (o.order_id LIKE ? OR o.recipient_name LIKE ? OR o.recipient_phone LIKE ? OR u.email LIKE ? OR o.recipient_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter) {
    $where .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

if ($timeFilter) {
    if ($timeFilter === 'today') {
        $where .= " AND DATE(o.created_at) = CURDATE()";
    } elseif ($timeFilter === '7days') {
        $where .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($timeFilter === 'month') {
        $where .= " AND MONTH(o.created_at) = MONTH(CURDATE()) AND YEAR(o.created_at) = YEAR(CURDATE())";
    } elseif ($timeFilter === 'custom' && $startDate && $endDate) {
        $where .= " AND DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }
}

$orders = $db->select("
    SELECT o.*, u.full_name, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    $where
    ORDER BY o.created_at DESC
    LIMIT 100
", $params);

// Lấy thống kê tổng quan
$thisMonthStart = date('Y-m-01');
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));

// Thống kê tổng số lượng hiện tại (All time)
$statusCounts = $db->selectOne("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
        SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_count,
        SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_count,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN order_status = 'returned' THEN 1 ELSE 0 END) as returned_count
    FROM orders
");

// Thống kê trước đó (Tạo trước tháng này)
$statusCountsPrev = $db->selectOne("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN order_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
        SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_count,
        SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped_count,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN order_status = 'returned' THEN 1 ELSE 0 END) as returned_count
    FROM orders
    WHERE created_at < ?
", [$thisMonthStart]);

function calculateOrderTrend($current, $prev) {
    $current = $current ?? 0;
    $prev = $prev ?? 0;
    if ($prev == 0) return ['trend' => 'up', 'percent' => $current > 0 ? 100 : 0];
    $diff = $current - $prev;
    $percent = ($diff / $prev) * 100;
    return [
        'trend' => $diff >= 0 ? 'up' : 'down',
        'percent' => abs(round($percent, 1))
    ];
}

$orderStats = [
    'total' => ['count' => $statusCounts['total_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['total_count'], $statusCountsPrev['total_count'])],
    'pending' => ['count' => $statusCounts['pending_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['pending_count'], $statusCountsPrev['pending_count'])],
    'confirmed' => ['count' => $statusCounts['confirmed_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['confirmed_count'], $statusCountsPrev['confirmed_count'])],
    'processing' => ['count' => $statusCounts['processing_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['processing_count'], $statusCountsPrev['processing_count'])],
    'shipped' => ['count' => $statusCounts['shipped_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['shipped_count'], $statusCountsPrev['shipped_count'])],
    'delivered' => ['count' => $statusCounts['delivered_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['delivered_count'], $statusCountsPrev['delivered_count'])],
    'cancelled' => ['count' => $statusCounts['cancelled_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['cancelled_count'], $statusCountsPrev['cancelled_count'])],
    'returned' => ['count' => $statusCounts['returned_count'] ?? 0, 'trend' => calculateOrderTrend($statusCounts['returned_count'], $statusCountsPrev['returned_count'])]
];

function renderOrderStatCard($title, $value, $trendData, $icon, $colorClass, $bgColorClass) {
    $trendIcon = $trendData['trend'] === 'up' ? 'trending_up' : 'trending_down';
    $trendColor = $trendData['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600';
    $percent = $trendData['percent'] . '%';
    
    return '
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500">'.$title.'</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">'.number_format($value).'</h3>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg '.$bgColorClass.' '.$colorClass.'">
                <span class="material-symbols-outlined">'.$icon.'</span>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <span class="flex items-center text-xs font-medium '.$trendColor.'">
                <span class="material-symbols-outlined !text-sm mr-1">'.$trendIcon.'</span>
                '.$percent.'
            </span>
            <span class="text-xs text-slate-500">so với tháng trước</span>
        </div>
    </div>';
}

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
?>

<!-- Thống kê nhanh -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <?= renderOrderStatCard('Tổng đơn hàng', $orderStats['total']['count'], $orderStats['total']['trend'], 'receipt_long', 'text-blue-600', 'bg-blue-50') ?>
    <?= renderOrderStatCard('Chờ xử lý', $orderStats['pending']['count'], $orderStats['pending']['trend'], 'pending_actions', 'text-yellow-600', 'bg-yellow-50') ?>
    <?= renderOrderStatCard('Đã xác nhận', $orderStats['confirmed']['count'], $orderStats['confirmed']['trend'], 'done', 'text-teal-600', 'bg-teal-50') ?>
    <?= renderOrderStatCard('Đang xử lý', $orderStats['processing']['count'], $orderStats['processing']['trend'], 'inventory_2', 'text-purple-600', 'bg-purple-50') ?>
    <?= renderOrderStatCard('Đang giao', $orderStats['shipped']['count'], $orderStats['shipped']['trend'], 'local_shipping', 'text-indigo-600', 'bg-indigo-50') ?>
    <?= renderOrderStatCard('Đã giao', $orderStats['delivered']['count'], $orderStats['delivered']['trend'], 'check_circle', 'text-green-600', 'bg-green-50') ?>
    <?= renderOrderStatCard('Đã hủy', $orderStats['cancelled']['count'], $orderStats['cancelled']['trend'], 'cancel', 'text-red-600', 'bg-red-50') ?>
    <?= renderOrderStatCard('Trả hàng', $orderStats['returned']['count'], $orderStats['returned']['trend'], 'keyboard_return', 'text-gray-600', 'bg-gray-50') ?>
</div>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <div class="flex flex-col xl:flex-row gap-3 items-start xl:items-center">
        <form method="GET" class="flex gap-3 flex-wrap items-center" id="filter-form">
        <input type="hidden" name="action" value="orders">
        
        <input type="text" name="search" placeholder="Tìm mã đơn, tên, SĐT, Email..." value="<?= htmlspecialchars($search) ?>"
               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none w-full md:w-auto">
               
        <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Tất cả trạng thái</option>
            <?php foreach ($statuses as $status): ?>
            <option value="<?= $status ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                <?= match($status) {
                    'pending' => 'Chờ xử lý',
                    'confirmed' => 'Đã xác nhận',
                    'processing' => 'Đang xử lý',
                    'shipped' => 'Đang giao',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy',
                    'returned' => 'Trả hàng',
                    default => ucfirst($status)
                } ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="time" onchange="toggleCustomDate(this.value); if(this.value !== 'custom') this.form.submit();" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Toàn thời gian</option>
            <option value="today" <?= $timeFilter === 'today' ? 'selected' : '' ?>>Hôm nay</option>
            <option value="7days" <?= $timeFilter === '7days' ? 'selected' : '' ?>>7 ngày qua</option>
            <option value="month" <?= $timeFilter === 'month' ? 'selected' : '' ?>>Tháng này</option>
            <option value="custom" <?= $timeFilter === 'custom' ? 'selected' : '' ?>>Tùy chỉnh...</option>
        </select>

        <div id="custom-date-inputs" class="flex items-center gap-2 <?= $timeFilter === 'custom' ? '' : 'hidden' ?>">
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <span>-</span>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium">Lọc</button>
        </div>
    </form>
    
    <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap">
        Tổng số: <strong class="text-base"><?= count($orders) ?></strong> đơn hàng
    </div>
</div>
    
    <script>
    function toggleCustomDate(val) {
        const customDiv = document.getElementById('custom-date-inputs');
        if(val === 'custom') {
            customDiv.classList.remove('hidden');
        } else {
            customDiv.classList.add('hidden');
        }
    }
    </script>
    
    <div class="flex gap-2 w-full md:w-auto mt-3 md:mt-0">
        <button onclick="printSelectedPackingSlips()"
       class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">local_shipping</span>
        In Đơn Hàng (<span id="selected-print-count">0</span>)
        </button>
        <button onclick="exportSelectedOrders()"
       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-xl">download</span>
        Xuất Excel (<span id="selected-count">0</span>)
        </button>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">
                        <input type="checkbox" id="select-all-orders" onchange="toggleSelectAllOrders()" class="w-4 h-4 rounded border-gray-300 cursor-pointer">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mã ĐH</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Khách hàng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tổng tiền</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thanh toán</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ngày đặt</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($orders as $order): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="order_ids[]" value="<?= $order['order_id'] ?>" class="order-checkbox w-4 h-4 rounded border-gray-300 cursor-pointer">
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium">#<?= $order['order_id'] ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800"><?= htmlspecialchars($order['full_name'] ?? $order['recipient_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($order['recipient_phone']) ?></p>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-axeron-red"><?= formatPrice($order['total_amount']) ?></td>
                    <td class="px-4 py-3">
                        <?php
                        $paymentClass = match($order['payment_status']) {
                            'paid' => 'bg-green-100 text-green-800',
                            'unpaid' => 'bg-yellow-100 text-yellow-800',
                            'refunded' => 'bg-gray-100 text-gray-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                        $paymentText = match($order['payment_status']) {
                            'paid' => 'Đã thanh toán',
                            'unpaid' => 'Chưa thanh toán',
                            'refunded' => 'Đã hoàn tiền',
                            default => $order['payment_status']
                        };
                        ?>
                        <div class="relative inline-block">
                            <button onclick="togglePaymentDropdown(event, <?= $order['order_id'] ?>)"
                                    data-order-id="<?= $order['order_id'] ?>"
                                    class="payment-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium <?= $paymentClass ?> hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <span class="payment-text"><?= $paymentText ?></span>
                                <span class="material-symbols-outlined text-xs">expand_more</span>
                            </button>
                            <div id="payment-dropdown-<?= $order['order_id'] ?>" class="payment-dropdown absolute right-0 mt-1 bg-white rounded-lg shadow-lg border z-50 hidden min-w-[160px]">
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'unpaid')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'unpaid' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    Chưa thanh toán
                                </button>
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'paid')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'paid' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Đã thanh toán
                                </button>
                                <button onclick="updatePaymentStatus(<?= $order['order_id'] ?>, 'refunded')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['payment_status'] === 'refunded' ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                    Đã hoàn tiền
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <?php
                        $statusClass = match($order['order_status']) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-purple-100 text-purple-800',
                            'shipped' => 'bg-indigo-100 text-indigo-800',
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
                        $statusDot = match($order['order_status']) {
                            'pending' => 'bg-yellow-500',
                            'confirmed' => 'bg-blue-500',
                            'processing' => 'bg-purple-500',
                            'shipped' => 'bg-indigo-500',
                            'delivered' => 'bg-green-500',
                            'cancelled' => 'bg-red-500',
                            'returned' => 'bg-gray-500',
                            default => 'bg-gray-500'
                        };
                        ?>
                        <div class="relative inline-block">
                            <button onclick="toggleStatusDropdown(event, <?= $order['order_id'] ?>)"
                                    data-order-id="<?= $order['order_id'] ?>"
                                    class="status-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusClass ?> hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <span class="status-dot w-2 h-2 rounded-full <?= $statusDot ?>"></span>
                                <span class="status-text"><?= $statusText ?></span>
                                <span class="material-symbols-outlined text-xs">expand_more</span>
                            </button>
                            <div id="status-dropdown-<?= $order['order_id'] ?>" class="status-dropdown absolute left-0 mt-1 bg-white rounded-lg shadow-lg border z-50 hidden min-w-[180px]">
                                <?php
                                // Hiển thị tất cả trạng thái để Admin có toàn quyền thay đổi
                                $allStatusOpts = [
                                    'pending' => ['Chờ xử lý', 'bg-yellow-500'],
                                    'confirmed' => ['Đã xác nhận', 'bg-blue-500'],
                                    'processing' => ['Đang xử lý', 'bg-purple-500'],
                                    'shipped' => ['Đang giao', 'bg-indigo-500'],
                                    'delivered' => ['Đã giao', 'bg-green-500'],
                                    'cancelled' => ['Đã hủy', 'bg-red-500'],
                                    'returned' => ['Trả hàng', 'bg-gray-500']
                                ];
                                foreach ($allStatusOpts as $st => $info):
                                ?>
                                <button onclick="updateOrderStatusFromDropdown(<?= $order['order_id'] ?>, '<?= $st ?>')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2 <?= $order['order_status'] === $st ? 'bg-gray-50 font-medium' : '' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $info[1] ?>"></span>
                                    <?= $info[0] ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td class="px-4 py-3">
                        <a href="javascript:void(0)" onclick="viewOrder(<?= $order['order_id'] ?>)"
                           class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Chi tiết">
                            <span class="material-symbols-outlined text-gray-600">visibility</span>
                        </a>
                        <a href="javascript:void(0)" onclick="printInvoice(<?= $order['order_id'] ?>)"
                           class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="In hóa đơn">
                            <span class="material-symbols-outlined text-gray-600">print</span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Không tìm thấy đơn hàng nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function viewOrder(orderId) {
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_order&id=' + orderId);
        const result = await response.json();

        if (result.success && result.order) {
            const o = result.order;
            let itemsHtml = '';
            if (result.items) {
                result.items.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td class="px-4 py-2">${item.product_name}</td>
                            <td class="px-4 py-2">${item.variant_info || '-'}</td>
                            <td class="px-4 py-2">${item.quantity}</td>
                            <td class="px-4 py-2">${formatCurrency(item.unit_price)}</td>
                            <td class="px-4 py-2 font-medium">${formatCurrency(item.subtotal)}</td>
                        </tr>
                    `;
                });
            }

            let logsHtml = '';
            if (result.logs && result.logs.length > 0) {
                const statusMap = {
                    'pending': 'Chờ xử lý', 'confirmed': 'Đã xác nhận', 'processing': 'Đang đóng gói',
                    'shipped': 'Đang giao', 'delivered': 'Đã giao', 'cancelled': 'Đã hủy', 'returned': 'Trả hàng'
                };
                result.logs.forEach(log => {
                    logsHtml += `
                        <div class="flex gap-4 items-start mb-4 relative">
                            <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-500 z-10"></div>
                            ${result.logs[result.logs.length-1] !== log ? '<div class="absolute left-1 top-3 bottom-[-20px] w-0.5 bg-gray-200"></div>' : ''}
                            <div>
                                <p class="text-sm font-medium text-gray-900">${statusMap[log.new_status] || log.new_status}</p>
                                <p class="text-xs text-gray-500">${new Date(log.changed_at).toLocaleString('vi-VN')} - Bởi: ${log.changed_by_name || 'Hệ thống'}</p>
                            </div>
                        </div>
                    `;
                });
            }

            const paymentMap = {
                'cod': 'Thanh toán khi nhận hàng (COD)',
                'vnpay': 'Thanh toán qua VNPay',
                'momo': 'Thanh toán qua Momo',
                'bank_transfer': 'Chuyển khoản ngân hàng'
            };

            const modalContent = `
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold">Chi Tiết Đơn Hàng #${o.order_id}</h2>
                        <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Khách hàng</p>
                            <p class="font-medium">${o.full_name || o.recipient_name}</p>
                            <p class="text-sm text-gray-600">${o.email || o.recipient_email || ''}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ngày đặt</p>
                            <p class="font-medium">${new Date(o.created_at).toLocaleString('vi-VN')}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Địa chỉ giao hàng</p>
                            <p class="font-medium">${o.recipient_name} - ${o.recipient_phone}</p>
                            <p class="text-sm text-gray-600">${o.shipping_address}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Thanh toán & Ghi chú</p>
                            <p class="text-sm text-gray-800">Phương thức: <span class="font-medium">${paymentMap[o.payment_method] || o.payment_method}</span></p>
                            ${o.note ? `<p class="text-sm text-gray-600 mt-1 bg-yellow-50 p-2 rounded border border-yellow-100">Ghi chú: ${o.note}</p>` : ''}
                        </div>
                    </div>

                    <div class="border rounded-lg overflow-hidden mb-6">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Sản phẩm</th>
                                    <th class="px-4 py-2 text-left">Phân loại</th>
                                    <th class="px-4 py-2 text-left">SL</th>
                                    <th class="px-4 py-2 text-left">Đơn giá</th>
                                    <th class="px-4 py-2 text-left">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                ${itemsHtml}
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Tạm tính:</td>
                                    <td class="px-4 py-2">${formatCurrency(o.subtotal)}</td>
                                </tr>
                                ${parseFloat(o.discount_amount) > 0 ? `
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Giảm giá:</td>
                                    <td class="px-4 py-2 text-green-600">-${formatCurrency(o.discount_amount)}</td>
                                </tr>
                                ` : ''}
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right">Phí ship:</td>
                                    <td class="px-4 py-2">${formatCurrency(o.shipping_fee)}</td>
                                </tr>
                                <tr class="font-bold">
                                    <td colspan="4" class="px-4 py-2 text-right">Tổng cộng:</td>
                                    <td class="px-4 py-2 text-axeron-red">${formatCurrency(o.total_amount)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    ${logsHtml ? `
                    <div class="mb-6">
                        <h3 class="text-base font-bold mb-4">Lịch sử cập nhật trạng thái</h3>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            ${logsHtml}
                        </div>
                    </div>
                    ` : ''}

                    <div class="flex justify-end gap-3">
                        <button onclick="printPackingSlipFromModal()" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                            <span class="material-symbols-outlined">local_shipping</span>
                            In phiếu giao hàng
                        </button>
                        <button onclick="printInvoiceFromModal()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            <span class="material-symbols-outlined">receipt_long</span>
                            In hóa đơn
                        </button>
                        <button onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Đóng</button>
                    </div>
                </div>
            `;

            openModal(modalContent);
            // Store current order ID for printing
            window.currentOrderId = orderId;
        }
    } catch (err) {
        showToast('Không thể tải thông tin đơn hàng!', 'error');
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
}

// Select all checkbox
function toggleSelectAllOrders() {
    const selectAll = document.getElementById('select-all-orders');
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedCount();
}

// Update selected count display
function updateSelectedCount() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    document.getElementById('selected-count').textContent = checked.length;
    if(document.getElementById('selected-print-count')) document.getElementById('selected-print-count').textContent = checked.length;
}

// Listen for checkbox changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.order-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});

// Export selected orders to Excel
function exportSelectedOrders() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    if (checked.length === 0) {
        showToast('Vui lòng chọn ít nhất một đơn hàng để xuất!', 'error');
        return;
    }

    showConfirm(`Bạn có chắc muốn xuất ${checked.length} đơn hàng đã chọn ra file Excel?`, async () => {
        const orderIds = Array.from(checked).map(cb => cb.value);
        showToast(`Đang chuẩn bị xuất ${orderIds.length} đơn hàng...`);
        console.log('Exporting orders:', orderIds);

        try {
            const formData = new FormData();
            formData.append('ajax_action', 'export_orders');
            formData.append('order_ids', JSON.stringify(orderIds));

            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success && result.data) {
                // Use SheetJS to create Excel file
                if (typeof XLSX !== 'undefined') {
                    exportToExcelWithSheetJS(result.data);
                } else {
                    // Fallback to CSV
                    exportToCSV(result.data);
                }
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            console.error('Export error:', err);
            showToast('Có lỗi xảy ra khi xuất dữ liệu!', 'error');
        }
    });
}

// Export using SheetJS library
function exportToExcelWithSheetJS(orders) {
    // Prepare worksheet data
    const wsData = [
        ['Mã ĐH', 'Khách hàng', 'SĐT', 'Địa chỉ giao hàng', 'Tổng tiền', 'Thanh toán', 'Trạng thái', 'Ngày đặt', 'Sản phẩm']
    ];

    orders.forEach(function(order) {
        var products = order.items ? order.items.map(function(i) { return i.product_name; }).join(', ') : '';
        wsData.push([
            '#' + order.order_id,
            order.recipient_name,
            order.recipient_phone,
            order.shipping_address || '',
            order.total_amount,
            order.payment_status_text,
            order.order_status_text,
            order.created_at,
            products
        ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Set column widths
    ws['!cols'] = [
        { wch: 10 }, // Mã ĐH
        { wch: 25 }, // Khách hàng
        { wch: 15 }, // SĐT
        { wch: 40 }, // Địa chỉ giao hàng
        { wch: 15 }, // Tổng tiền
        { wch: 15 }, // Thanh toán
        { wch: 15 }, // Trạng thái
        { wch: 18 }, // Ngày đặt
        { wch: 40 }  // Sản phẩm
    ];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Danh sách đơn hàng');

    // Generate filename with date
    const date = new Date().toISOString().slice(0, 10);
    XLSX.writeFile(wb, 'DonHang_' + date + '.xlsx');

    showToast('Xuất Excel thành công!', 'success');
}

// CSV export fallback
function exportToCSV(orders) {
    let csv = '﻿'; // BOM for UTF-8
    csv += 'Mã ĐH,Khách hàng,SĐT,Địa chỉ giao hàng,Tổng tiền,Thanh toán,Trạng thái,Ngày đặt,Sản phẩm\n';

    orders.forEach(order => {
        const products = order.items ? order.items.map(i => i.product_name).join('; ') : '';
        const address = (order.shipping_address || '').replace(/"/g, '""');
        csv += `"#${order.order_id}","${order.recipient_name}","${order.recipient_phone}","${address}",${order.total_amount},"${order.payment_status_text}","${order.order_status_text}","${order.created_at}","${products}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'DonHang_' + new Date().toISOString().slice(0, 10) + '.csv';
    link.click();

    showToast('Xuất CSV thành công!', 'success');
}

// Print invoice for single order
async function printInvoice(orderId) {
    console.log('printInvoice called with orderId:', orderId);
    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_order&id=' + orderId);
        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Order result:', result);

        if (result.success && result.order) {
            generateAndPrintInvoice(result.order, result.items);
        } else {
            console.log('Order not found, result:', result);
            showToast('Khong tim thay don hang!', 'error');
        }
    } catch (err) {
        console.error('Print error:', err);
        showToast('Co loi xay ra khi tai hoa don!', 'error');
    }
}

// Print selected orders (Packing slips)
function printSelectedPackingSlips() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    if (checked.length === 0) {
        showToast('Vui lòng chọn ít nhất một đơn hàng để in!', 'error');
        return;
    }

    showConfirm(`Bạn có chắc muốn in phiếu giao hàng cho ${checked.length} đơn hàng đã chọn?`, async () => {
        const orderIds = Array.from(checked).map(cb => cb.value);
        showToast(`Đang chuẩn bị in ${orderIds.length} phiếu...`);

        try {
            const formData = new FormData();
            formData.append('ajax_action', 'export_orders');
            formData.append('order_ids', JSON.stringify(orderIds));

            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success && result.data) {
                generateMultiplePackingSlips(result.data);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            console.error('Print error:', err);
            showToast('Có lỗi xảy ra khi tải dữ liệu in!', 'error');
        }
    });
}

function generateMultiplePackingSlips(orders) {
    let allSlipsHtml = '';
    
    orders.forEach((o, index) => {
        let itemsHtml = '';
        if (o.items && o.items.length > 0) {
            for (var i = 0; i < o.items.length; i++) {
                itemsHtml += '<tr><td style="padding:10px;border-bottom:1px solid #ddd;">' + (i + 1) + '</td><td style="padding:10px;border-bottom:1px solid #ddd;"><strong>' + (o.items[i].product_name || 'N/A') + '</strong><br><small>' + (o.items[i].variant_name || '') + '</small></td><td style="padding:10px;border-bottom:1px solid #ddd;text-align:center;"><strong>' + o.items[i].quantity + '</strong></td></tr>';
            }
        }

        var paymentStatusText = o.payment_status === 'paid' ? 'Đã Thanh Toán' : 'Thu Hộ (COD)';
        var totalAmountToCollect = o.payment_status === 'paid' ? '0đ' : new Intl.NumberFormat('vi-VN').format(o.total_amount) + 'đ';
        var noteHtml = o.note ? `<div style="margin-top:20px;padding:15px;background:#f9f9f9;border:2px dashed #666;border-radius:5px;"><p style="margin:0;"><strong>Ghi chú:</strong> ${o.note}</p></div>` : '';
        
        let pageBreak = index < orders.length - 1 ? 'page-break-after: always;' : '';

        allSlipsHtml += `<div style="border:2px solid #000;padding:20px;max-width:600px;margin:0 auto;border-radius:10px; ${pageBreak}"><div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px dashed #ccc;padding-bottom:15px;margin-bottom:15px;"><div><h2 style="margin:0;font-size:24px;">AXERON SPORTS</h2><p style="margin:5px 0 0 0;color:#666;">Phiếu Giao Hàng</p></div><div style="text-align:right;"><img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${o.order_code}" alt="QR" style="display:block;margin-left:auto;"><p style="margin:5px 0 0 0;font-weight:bold;font-size:18px;">#${o.order_id}</p></div></div><div style="display:flex;justify-content:space-between;margin-bottom:20px;"><div style="width:48%;"><h3>NGƯỜI GỬI:</h3><p style="margin:5px 0;"><strong>Axeron Sports</strong></p><p style="margin:5px 0;">123 Đường Thể thao, Quận 1, TP.HCM</p><p style="margin:5px 0;">ĐT: 0901 234 567</p></div><div style="width:48%;"><h3>NGƯỜI NHẬN:</h3><p style="margin:5px 0;font-size:18px;"><strong>${o.recipient_name}</strong></p><p style="margin:5px 0;font-size:16px;">ĐT: <strong>${o.recipient_phone}</strong></p><p style="margin:5px 0;">${o.shipping_address}</p></div></div><h3 style="background:#000;color:#fff;padding:8px 10px;margin-bottom:0;">CHI TIẾT ĐƠN HÀNG</h3><table style="width:100%;border-collapse:collapse;margin-bottom:10px;"><thead><tr style="border-bottom:2px solid #000;"><th style="padding:10px;text-align:left;">STT</th><th style="padding:10px;text-align:left;">Sản phẩm</th><th style="padding:10px;text-align:center;">SL</th></tr></thead><tbody>${itemsHtml}</tbody></table>${noteHtml}<div style="margin-top:20px;border-top:2px solid #000;padding-top:15px;"><div style="display:flex;justify-content:space-between;"><h2 style="margin:0;">TIỀN THU NGƯỜI NHẬN:</h2><h2 style="margin:0;font-size:28px;">${totalAmountToCollect}</h2></div><p style="text-align:center;margin-top:5px;font-size:14px;">(${paymentStatusText})</p></div></div>`;
    });

    var slipHtml = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>In Hàng Loạt Phiếu Giao Hàng</title></head><body style="font-family:Arial,sans-serif;padding:0;margin:0;max-width:100%;"><style>@media print{body{margin:0;padding:10mm;width:100%;} @page{size: A5; margin:0;} .no-print{display:none;} }</style>${allSlipsHtml}<div class="no-print" style="margin-top:30px;text-align:center;padding:20px;"><button onclick="window.print()" style="padding:10px 30px;font-size:16px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;">In toàn bộ phiếu</button> <button onclick="window.close()" style="padding:10px 30px;font-size:16px;background:#6b7280;color:white;border:none;border-radius:5px;cursor:pointer;">Đóng</button></div></body></html>`;

    const w = 700;
    const h = 900;
    const left = (window.screen.width / 2) - (w / 2);
    const top = (window.screen.height / 2) - (h / 2);
    var printWindow = window.open('', '_blank', `width=${w},height=${h},top=${top},left=${left}`);
    if (!printWindow) {
        showToast('Vui lòng cho phép popup để in!', 'error');
        return;
    }
    printWindow.document.write(slipHtml);
    printWindow.document.close();
}

// Print invoice from modal (uses stored order ID)
async function printInvoiceFromModal() {
    if (window.currentOrderId) {
        await printInvoice(window.currentOrderId);
    } else {
        showToast('Khong co thong tin don hang!', 'error');
    }
}

// Print packing slip from modal (uses stored order ID)
async function printPackingSlipFromModal() {
    if (window.currentOrderId) {
        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_order&id=' + window.currentOrderId);
            const result = await response.json();
            if (result.success && result.order) {
                generateAndPrintPackingSlip(result.order, result.items);
            } else {
                showToast('Khong tim thay don hang!', 'error');
            }
        } catch (err) {
            showToast('Co loi xay ra!', 'error');
        }
    } else {
        showToast('Khong co thong tin don hang!', 'error');
    }
}

// Generate and print invoice HTML
function generateAndPrintInvoice(o, items) {
    // Helper function for currency (inline to work in new window)
    function fc(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    }

    let itemsHtml = '';
    if (items && items.length > 0) {
        for (var i = 0; i < items.length; i++) {
            itemsHtml += '<tr><td style="padding:8px;border:1px solid #ddd;">' + (i + 1) + '</td><td style="padding:8px;border:1px solid #ddd;">' + (items[i].product_name || 'N/A') + '</td><td style="padding:8px;border:1px solid #ddd;">' + (items[i].variant_info || '-') + '</td><td style="padding:8px;border:1px solid #ddd;text-align:center;">' + items[i].quantity + '</td><td style="padding:8px;border:1px solid #ddd;text-align:right;">' + fc(items[i].unit_price) + '</td><td style="padding:8px;border:1px solid #ddd;text-align:right;">' + fc(items[i].subtotal) + '</td></tr>';
        }
    } else {
        itemsHtml = '<tr><td colspan="6" style="padding:8px;border:1px solid #ddd;text-align:center;">Không có sản phẩm</td></tr>';
    }

    var discountLine = '';
    if (parseFloat(o.discount_amount) > 0) {
        discountLine = '<p>Giảm giá: <span style="color:#16a34a;">-' + fc(o.discount_amount) + '</span></p>';
    }

    var dateStr = new Date(o.created_at).toLocaleString('vi-VN');

    var invoiceHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Hóa đơn #' + o.order_id + '</title></head><body style="font-family:Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;"><style>@media print{body{margin:0;padding:0;}@page{margin:10mm;}}</style><div style="text-align:center;margin-bottom:30px;border-bottom:2px solid #dc2626;padding-bottom:20px;"><h1 style="color:#dc2626;margin:0;">AXERON SPORTS</h1><p style="margin:5px 0;">Địa chỉ: 123 Đường Thể thao, Quận 1, TP.HCM</p><p style="margin:5px 0;">Hotline: 0901 234 567</p></div><h2 style="text-align:center;margin:20px 0;color:#333;">HÓA ĐƠN BÁN HÀNG</h2><p style="text-align:center;margin-bottom:20px;">Mã đơn: <strong>#' + o.order_id + '</strong> | Ngày: ' + dateStr + '</p><div style="margin-bottom:20px;"><p><strong>Khách hàng:</strong> ' + o.recipient_name + '</p><p><strong>SĐT:</strong> ' + o.recipient_phone + '</p><p><strong>Địa chỉ giao hàng:</strong> ' + o.shipping_address + '</p></div><table style="width:100%;border-collapse:collapse;margin-bottom:20px;"><thead><tr style="background:#f3f4f6;"><th style="padding:8px;border:1px solid #ddd;text-align:left;">STT</th><th style="padding:8px;border:1px solid #ddd;text-align:left;">Sản phẩm</th><th style="padding:8px;border:1px solid #ddd;text-align:left;">Phân loại</th><th style="padding:8px;border:1px solid #ddd;text-align:center;">SL</th><th style="padding:8px;border:1px solid #ddd;text-align:right;">Đơn giá</th><th style="padding:8px;border:1px solid #ddd;text-align:right;">Thành tiền</th></tr></thead><tbody>' + itemsHtml + '</tbody></table><div style="text-align:right;"><p>Tạm tính: <strong>' + fc(o.subtotal) + '</strong></p>' + discountLine + '<p>Phí ship: ' + fc(o.shipping_fee) + '</p><p style="font-size:18px;"><strong>TỔNG CỘNG: ' + fc(o.total_amount) + '</strong></p></div><div style="margin-top:30px;text-align:center;"><button onclick="window.print()" style="padding:10px 30px;font-size:16px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;">In hóa đơn</button> <button onclick="window.close()" style="padding:10px 30px;font-size:16px;background:#6b7280;color:white;border:none;border-radius:5px;cursor:pointer;">Đóng</button></div></body></html>';

    const w = 800;
    const h = 600;
    const left = (window.screen.width / 2) - (w / 2);
    const top = (window.screen.height / 2) - (h / 2);
    var printWindow = window.open('', '_blank', `width=${w},height=${h},top=${top},left=${left}`);
    if (!printWindow) {
        showToast('Vui lòng cho phép popup để in hóa đơn!', 'error');
        return;
    }
    printWindow.document.write(invoiceHtml);
    printWindow.document.close();
}

// Generate and print packing slip HTML
function generateAndPrintPackingSlip(o, items) {
    let itemsHtml = '';
    if (items && items.length > 0) {
        for (var i = 0; i < items.length; i++) {
            itemsHtml += '<tr><td style="padding:10px;border-bottom:1px solid #ddd;">' + (i + 1) + '</td><td style="padding:10px;border-bottom:1px solid #ddd;"><strong>' + (items[i].product_name || 'N/A') + '</strong><br><small>' + (items[i].variant_info || '') + '</small></td><td style="padding:10px;border-bottom:1px solid #ddd;text-align:center;"><strong>' + items[i].quantity + '</strong></td></tr>';
        }
    }

    var paymentStatusText = o.payment_status === 'paid' ? 'Đã Thanh Toán' : 'Thu Hộ (COD)';
    var totalAmountToCollect = o.payment_status === 'paid' ? '0đ' : new Intl.NumberFormat('vi-VN').format(o.total_amount) + 'đ';
    var noteHtml = o.note ? `<div style="margin-top:20px;padding:15px;background:#f9f9f9;border:2px dashed #666;border-radius:5px;"><p style="margin:0;"><strong>Ghi chú:</strong> ${o.note}</p></div>` : '';

    var slipHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Phiếu Giao Hàng #' + o.order_id + '</title></head><body style="font-family:Arial,sans-serif;padding:0;margin:0;max-width:100%;"><style>@media print{body{margin:0;padding:10mm;width:100%;} @page{size: A5; margin:0;} .no-print{display:none;} }</style><div style="border:2px solid #000;padding:20px;max-width:600px;margin:0 auto;border-radius:10px;"><div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px dashed #ccc;padding-bottom:15px;margin-bottom:15px;"><div><h2 style="margin:0;font-size:24px;">AXERON SPORTS</h2><p style="margin:5px 0 0 0;color:#666;">Phiếu Giao Hàng</p></div><div style="text-align:right;"><img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' + o.order_code + '" alt="QR" style="display:block;margin-left:auto;"><p style="margin:5px 0 0 0;font-weight:bold;font-size:18px;">#' + o.order_id + '</p></div></div><div style="display:flex;justify-content:space-between;margin-bottom:20px;"><div style="width:48%;"><h3>NGƯỜI GỬI:</h3><p style="margin:5px 0;"><strong>Axeron Sports</strong></p><p style="margin:5px 0;">123 Đường Thể thao, Quận 1, TP.HCM</p><p style="margin:5px 0;">ĐT: 0901 234 567</p></div><div style="width:48%;"><h3>NGƯỜI NHẬN:</h3><p style="margin:5px 0;font-size:18px;"><strong>' + o.recipient_name + '</strong></p><p style="margin:5px 0;font-size:16px;">ĐT: <strong>' + o.recipient_phone + '</strong></p><p style="margin:5px 0;">' + o.shipping_address + '</p></div></div><h3 style="background:#000;color:#fff;padding:8px 10px;margin-bottom:0;">CHI TIẾT ĐƠN HÀNG</h3><table style="width:100%;border-collapse:collapse;margin-bottom:10px;"><thead><tr style="border-bottom:2px solid #000;"><th style="padding:10px;text-align:left;">STT</th><th style="padding:10px;text-align:left;">Sản phẩm</th><th style="padding:10px;text-align:center;">SL</th></tr></thead><tbody>' + itemsHtml + '</tbody></table>' + noteHtml + '<div style="margin-top:20px;border-top:2px solid #000;padding-top:15px;"><div style="display:flex;justify-content:space-between;"><h2 style="margin:0;">TIỀN THU NGƯỜI NHẬN:</h2><h2 style="margin:0;font-size:28px;">' + totalAmountToCollect + '</h2></div><p style="text-align:center;margin-top:5px;font-size:14px;">(' + paymentStatusText + ')</p></div></div><div class="no-print" style="margin-top:30px;text-align:center;"><button onclick="window.print()" style="padding:10px 30px;font-size:16px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;">In phiếu giao hàng</button> <button onclick="window.close()" style="padding:10px 30px;font-size:16px;background:#6b7280;color:white;border:none;border-radius:5px;cursor:pointer;">Đóng</button></div></body></html>';

    const w = 700;
    const h = 900;
    const left = (window.screen.width / 2) - (w / 2);
    const top = (window.screen.height / 2) - (h / 2);
    var printWindow = window.open('', '_blank', `width=${w},height=${h},top=${top},left=${left}`);
    if (!printWindow) {
        showToast('Vui lòng cho phép popup để in!', 'error');
        return;
    }
    printWindow.document.write(slipHtml);
    printWindow.document.close();
}

// Backward compatibility wrapper - uses dropdown version with instant update
async function updateOrderStatus(orderId, newStatus) {
    await updateOrderStatusFromDropdown(orderId, newStatus);
}

// Order status dropdown functions
function toggleStatusDropdown(e, orderId) {
    if (e) e.stopPropagation();

    // Close all other dropdowns first (both payment and status)
    document.querySelectorAll('.status-dropdown, .payment-dropdown').forEach(d => {
        if (d.id !== 'status-dropdown-' + orderId && d.id !== 'payment-dropdown-' + orderId) {
            d.classList.add('hidden');
        }
    });

    const dropdown = document.getElementById('status-dropdown-' + orderId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close status dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.status-dropdown-btn') && !e.target.closest('.status-dropdown')) {
        document.querySelectorAll('.status-dropdown').forEach(d => {
            d.classList.add('hidden');
        });
    }
});

function updateOrderStatusFromDropdown(orderId, newStatus) {
    const statusLabels = {
        'pending': 'Chờ xử lý',
        'confirmed': 'Đã xác nhận',
        'processing': 'Đang xử lý',
        'shipped': 'Đang giao',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy',
        'returned': 'Trả hàng'
    };

    // Close dropdown first
    document.getElementById('status-dropdown-' + orderId)?.classList.add('hidden');

    showConfirm(`Cập nhật đơn hàng #${orderId} sang trạng thái "${statusLabels[newStatus]}"?`, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'update_order_status');
        formData.append('order_id', orderId);
        formData.append('new_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');

            if (result.success) {
                const btn = document.querySelector(`.status-dropdown-btn[data-order-id="${orderId}"]`);
                const dropdown = document.getElementById('status-dropdown-' + orderId);

                if (btn && dropdown) {
                    const statusClasses = {
                        'pending': 'bg-yellow-100 text-yellow-800',
                        'confirmed': 'bg-blue-100 text-blue-800',
                        'processing': 'bg-purple-100 text-purple-800',
                        'shipped': 'bg-indigo-100 text-indigo-800',
                        'delivered': 'bg-green-100 text-green-800',
                        'cancelled': 'bg-red-100 text-red-800',
                        'returned': 'bg-gray-100 text-gray-800'
                    };

                    const statusDots = {
                        'pending': 'bg-yellow-500',
                        'confirmed': 'bg-blue-500',
                        'processing': 'bg-purple-500',
                        'shipped': 'bg-indigo-500',
                        'delivered': 'bg-green-500',
                        'cancelled': 'bg-red-500',
                        'returned': 'bg-gray-500'
                    };

                    btn.className = `status-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses[newStatus]} hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap`;

                    const dot = btn.querySelector('.status-dot');
                    if (dot) dot.className = `status-dot w-2 h-2 rounded-full ${statusDots[newStatus]}`;

                    const textSpan = btn.querySelector('.status-text');
                    if (textSpan) textSpan.textContent = statusLabels[newStatus];

                    dropdown.querySelectorAll('button').forEach(b => {
                        b.classList.remove('bg-gray-50', 'font-medium');
                    });
                    
                    const statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
                    const currentIndex = statusOrder.indexOf(newStatus);
                    const buttons = dropdown.querySelectorAll('button');
                    if (buttons[currentIndex]) {
                        buttons[currentIndex].classList.add('bg-gray-50', 'font-medium');
                    }
                }
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

// Payment status dropdown functions
function togglePaymentDropdown(e, orderId) {
    if (e) e.stopPropagation();

    // Close all other dropdowns first
    document.querySelectorAll('.payment-dropdown').forEach(d => {
        if (d.id !== 'payment-dropdown-' + orderId) {
            d.classList.add('hidden');
        }
    });

    const dropdown = document.getElementById('payment-dropdown-' + orderId);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Close all dropdowns on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.payment-dropdown-btn') && !e.target.closest('.payment-dropdown')) {
        document.querySelectorAll('.payment-dropdown').forEach(d => {
            d.classList.add('hidden');
        });
    }
});

function updatePaymentStatus(orderId, newStatus) {
    const statusLabels = {
        'paid': 'Đã thanh toán',
        'unpaid': 'Chưa thanh toán',
        'refunded': 'Đã hoàn tiền'
    };

    // Close dropdown first
    document.getElementById('payment-dropdown-' + orderId)?.classList.add('hidden');

    showConfirm(`Cập nhật trạng thái thanh toán đơn hàng #${orderId} sang "${statusLabels[newStatus]}"?`, async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'update_payment_status');
        formData.append('order_id', orderId);
        formData.append('new_payment_status', newStatus);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            showToast(result.message, result.success ? 'success' : 'error');

            if (result.success && result.new_status) {
                const btn = document.querySelector(`.payment-dropdown-btn[data-order-id="${orderId}"]`);
                const dropdown = document.getElementById('payment-dropdown-' + orderId);

                if (btn && dropdown) {
                    const statusClasses = {
                        'paid': 'bg-green-100 text-green-800',
                        'unpaid': 'bg-yellow-100 text-yellow-800',
                        'refunded': 'bg-gray-100 text-gray-800'
                    };

                    btn.className = `payment-dropdown-btn px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses[newStatus]} hover:opacity-80 transition-opacity flex items-center gap-1 cursor-pointer whitespace-nowrap`;

                    const textSpan = btn.querySelector('.payment-text');
                    if (textSpan) textSpan.textContent = statusLabels[newStatus];

                    dropdown.querySelectorAll('button').forEach(b => {
                        b.classList.remove('bg-gray-50', 'font-medium');
                    });
                    
                    const buttons = dropdown.querySelectorAll('button');
                    const statusIndex = { 'unpaid': 0, 'paid': 1, 'refunded': 2 };
                    if (buttons[statusIndex[newStatus]]) {
                        buttons[statusIndex[newStatus]].classList.add('bg-gray-50', 'font-medium');
                    }
                }
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
</script>
