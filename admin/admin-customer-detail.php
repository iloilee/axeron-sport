<?php
if (!hasPermission('customer_detail')) {
    setFlash('error', 'Bạn không có quyền truy cập trang này!');
    header('Location: ' . BASE_URL . '/admin/admin.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Không tìm thấy khách hàng!');
    header('Location: ' . BASE_URL . '/admin/admin.php?action=analytics');
    exit;
}

// Lấy thông tin khách hàng
$customer = $db->selectOne("
    SELECT 
        u.*, 
        COUNT(o.order_id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent,
        MAX(o.created_at) as last_order_date
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id 
        AND o.order_status NOT IN ('cancelled', 'returned') 
        AND o.payment_status = 'paid'
    WHERE u.user_id = ? AND u.role_id = 3
    GROUP BY u.user_id
", [$id]);

if (!$customer) {
    setFlash('error', 'Không tìm thấy khách hàng!');
    header('Location: ' . BASE_URL . '/admin/admin.php?action=analytics');
    exit;
}

// Tính hạng
$daysSinceLastOrder = $customer['last_order_date'] ? (time() - strtotime($customer['last_order_date'])) / (60 * 60 * 24) : 999;
$rank = 'Bình thường';
if ($customer['total_spent'] > 5000000 || $customer['total_orders'] > 5) {
    $rank = 'VIP';
} elseif ($customer['total_spent'] >= 2000000) {
    $rank = 'Tiềm năng';
} elseif ($customer['total_orders'] == 1) {
    $rank = 'Mới';
} elseif ($customer['total_orders'] == 0) {
    $rank = 'Chưa mua hàng';
}

$rankClass = match($rank) {
    'VIP' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'Tiềm năng' => 'bg-blue-100 text-blue-800 border-blue-200',
    'Mới' => 'bg-green-100 text-green-800 border-green-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

// Lấy lịch sử đơn hàng
$orders = $db->select("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
", [$id]);

// Lấy hành vi xem sản phẩm
$viewLogs = $db->select("
    SELECT p.product_name, p.product_id, v.viewed_at 
    FROM product_view_logs v
    JOIN products p ON v.product_id = p.product_id
    WHERE v.user_id = ?
    ORDER BY v.viewed_at DESC LIMIT 10
", [$id]);

// Lấy lịch sử tìm kiếm
$searchLogs = $db->select("
    SELECT keyword, result_count, searched_at 
    FROM search_logs 
    WHERE user_id = ?
    ORDER BY searched_at DESC LIMIT 10
", [$id]);

// Lấy dữ liệu cho biểu đồ chi tiêu (theo tháng trong năm nay)
$chartDataSql = "
    SELECT MONTH(created_at) as month, SUM(total_amount) as revenue
    FROM orders
    WHERE user_id = ? AND order_status NOT IN ('cancelled', 'returned') AND payment_status = 'paid'
      AND YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
";
$chartDataRaw = $db->select($chartDataSql, [$id]);
$chartDataMap = [];
foreach ($chartDataRaw as $row) {
    $chartDataMap[$row['month']] = (float)$row['revenue'];
}
$months = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'];
$chartValues = [];
for ($i = 1; $i <= 12; $i++) {
    $chartValues[] = $chartDataMap[$i] ?? 0;
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <a href="admin.php?action=analytics" class="inline-flex items-center text-gray-600 hover:text-axeron-red">
            <span class="material-symbols-outlined mr-1">arrow_back</span>
            Quay lại Thống kê
        </a>
    </div>

    <!-- Thông tin chung & Biểu đồ -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Thông tin cá nhân -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center gap-4 border-b pb-4 mb-4">
                <div class="w-16 h-16 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold text-2xl">
                    <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold"><?= htmlspecialchars($customer['full_name']) ?></h2>
                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs border <?= $rankClass ?>"><?= $rank ?></span>
                </div>
            </div>
            <div class="space-y-3 text-sm">
                <p><span class="text-gray-500 w-24 inline-block">Email:</span> <span class="font-medium"><?= htmlspecialchars($customer['email']) ?></span></p>
                <p><span class="text-gray-500 w-24 inline-block">Điện thoại:</span> <span class="font-medium"><?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></span></p>
                <p><span class="text-gray-500 w-24 inline-block">Ngày tạo:</span> <span class="font-medium"><?= date('d/m/Y', strtotime($customer['created_at'])) ?></span></p>
                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4 text-center">
                    <div>
                        <p class="text-gray-500 mb-1">Số Đơn</p>
                        <p class="text-xl font-bold text-blue-600"><?= $customer['total_orders'] ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Tổng Chi Tiêu</p>
                        <p class="text-xl font-bold text-green-600"><?= formatPrice($customer['total_spent']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ chi tiêu -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <h3 class="font-bold text-lg mb-4">Biểu đồ Chi tiêu (Năm nay)</h3>
            <div class="h-64">
                <canvas id="spendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Hành vi & Lịch sử -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Cột trái: Đơn hàng -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl">
                <h3 class="font-bold text-lg">Lịch sử đơn hàng</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 uppercase">
                            <th class="px-4 py-3">Mã ĐH</th>
                            <th class="px-4 py-3">Ngày Đặt</th>
                            <th class="px-4 py-3">Trạng Thái</th>
                            <th class="px-4 py-3 text-right">Tổng Tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Chưa có đơn hàng nào.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): 
                                $statusClass = match($o['order_status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-purple-100 text-purple-800',
                                    'shipped' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                $statusText = match($o['order_status']) {
                                    'pending' => 'Chờ xử lý',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipped' => 'Đang giao',
                                    'delivered' => 'Đã giao',
                                    'cancelled' => 'Đã hủy',
                                    default => $o['order_status']
                                };
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-axeron-red">#<?= $o['order_code'] ?></td>
                                <td class="px-4 py-3 text-gray-500"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-[10px] <?= $statusClass ?>"><?= $statusText ?></span></td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800"><?= formatPrice($o['total_amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cột phải: Hành vi -->
        <div class="space-y-6">
            <!-- Sản phẩm xem gần đây -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="font-bold text-sm text-gray-700">Sản phẩm xem gần đây</h3>
                </div>
                <div class="p-4">
                    <?php if (empty($viewLogs)): ?>
                        <p class="text-gray-500 text-sm text-center py-4">Chưa xem sản phẩm nào.</p>
                    <?php else: ?>
                        <ul class="space-y-3">
                            <?php foreach ($viewLogs as $v): ?>
                            <li class="flex justify-between items-start text-sm">
                                <a href="<?= BASE_URL ?>/shop/product-detail.php?id=<?= $v['product_id'] ?>" target="_blank" class="text-blue-600 hover:underline line-clamp-1 flex-1 pr-2"><?= htmlspecialchars($v['product_name']) ?></a>
                                <span class="text-gray-400 text-[11px] whitespace-nowrap"><?= date('d/m H:i', strtotime($v['viewed_at'])) ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lịch sử tìm kiếm -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="font-bold text-sm text-gray-700">Lịch sử tìm kiếm</h3>
                </div>
                <div class="p-4">
                    <?php if (empty($searchLogs)): ?>
                        <p class="text-gray-500 text-sm text-center py-4">Chưa có lịch sử tìm kiếm.</p>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($searchLogs as $s): ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200" title="<?= date('d/m H:i', strtotime($s['searched_at'])) ?>">
                                <span class="material-symbols-outlined text-[14px] mr-1 text-gray-500">search</span>
                                <?= htmlspecialchars($s['keyword']) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('spendChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Chi tiêu',
                    data: <?= json_encode($chartValues) ?>,
                    backgroundColor: '#10b981', // green-500
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + ' Tr';
                                return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
