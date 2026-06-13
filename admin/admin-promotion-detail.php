<?php
if (!defined('BASE_URL')) exit;

$promo_id = (int)($_GET['id'] ?? 0);
if ($promo_id <= 0) {
    echo "<div class='p-8 text-center text-gray-500'>ID Khuyến mãi không hợp lệ!</div>";
    exit;
}

// Lấy thông tin promotion
$promo = $db->selectOne("SELECT * FROM promotions WHERE promo_id = ?", [$promo_id]);
if (!$promo) {
    echo "<div class='p-8 text-center text-gray-500'>Không tìm thấy khuyến mãi!</div>";
    exit;
}

// Thống kê dựa trên loại KM
$stats = [
    'orders_count' => 0,
    'total_revenue' => 0,
    'total_discount' => 0
];

if ($promo['type'] === 'voucher') {
    // Với voucher, đếm số đơn hàng có promo_id tương ứng
    $res = $db->selectOne("
        SELECT COUNT(order_id) as c, SUM(total_amount) as rev, SUM(discount_amount) as disc
        FROM orders
        WHERE promo_id = ? AND order_status NOT IN ('cancelled', 'returned')
    ", [$promo_id]);
    $stats['orders_count'] = (int)$res['c'];
    $stats['total_revenue'] = (float)$res['rev'];
    $stats['total_discount'] = (float)$res['disc'];
} else {
    // Với product, category, flashsale: Tính tổng doanh thu của các sản phẩm được áp dụng TRONG thời gian KM
    // Lấy danh sách product ids
    $pIds = [];
    if ($promo['type'] === 'product' || $promo['type'] === 'flashsale') {
        $q = $db->select("SELECT product_id FROM promotion_products WHERE promo_id = ?", [$promo_id]);
        $pIds = array_column($q, 'product_id');
    } elseif ($promo['type'] === 'category') {
        $q = $db->select("SELECT p.product_id FROM products p JOIN promotion_categories pc ON p.category_id = pc.category_id WHERE pc.promo_id = ?", [$promo_id]);
        $pIds = array_column($q, 'product_id');
    }
    
    if (!empty($pIds)) {
        $placeholders = implode(',', array_fill(0, count($pIds), '?'));
        $params = $pIds;
        $params[] = $promo['start_date'];
        $params[] = $promo['end_date'];
        
        $res = $db->selectOne("
            SELECT COUNT(DISTINCT o.order_id) as c, SUM(oi.subtotal) as rev
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id IN ($placeholders) 
            AND o.created_at BETWEEN ? AND ?
            AND o.order_status NOT IN ('cancelled', 'returned')
        ", $params);
        $stats['orders_count'] = (int)$res['c'];
        $stats['total_revenue'] = (float)$res['rev'];
        // Không thể tính discount_amount chính xác ở đây vì đã trừ trực tiếp vào giá
    }
}

// Loại KM text
$typeLabels = [
    'voucher' => ['Voucher', 'bg-purple-100 text-purple-800'],
    'product' => ['Sản phẩm', 'bg-blue-100 text-blue-800'],
    'category' => ['Danh mục', 'bg-teal-100 text-teal-800'],
    'flashsale' => ['Flash Sale', 'bg-orange-100 text-orange-800']
];
$tLabel = $typeLabels[$promo['type']] ?? ['Khác', 'bg-gray-100 text-gray-800'];

// Status
$now = time();
$start = strtotime($promo['start_date']);
$end = strtotime($promo['end_date']);
$isExpired = $now > $end;
$isUpcoming = $now < $start;
$isActive = $promo['is_active'] && !$isExpired && !$isUpcoming;

if ($isExpired) {
    $statusHtml = '<span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Đã kết thúc</span>';
} elseif ($isUpcoming) {
    $statusHtml = '<span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Sắp diễn ra</span>';
} elseif (!$promo['is_active']) {
    $statusHtml = '<span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Tạm dừng</span>';
} else {
    $statusHtml = '<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Đang diễn ra</span>';
}
?>

<div class="mb-6 flex justify-between items-center">
    <div class="flex items-center gap-4">
        <a href="<?= BASE_URL ?>/admin/admin.php?action=promotions" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 hover:text-axeron-red hover:shadow-md transition-all">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($promo['promo_name']) ?></h1>
            <p class="text-sm text-gray-500 mt-1">
                <?= $promo['promo_code'] ? 'Mã: <strong>' . htmlspecialchars($promo['promo_code']) . '</strong> • ' : '' ?>
                Ngày tạo: <?= date('d/m/Y', strtotime($promo['created_at'])) ?>
            </p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <?= $statusHtml ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats KPI -->
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Đơn hàng áp dụng</p>
                        <h3 class="text-2xl font-bold text-gray-800"><?= number_format($stats['orders_count']) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                        <span class="material-symbols-outlined">shopping_bag</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Doanh thu mang lại</p>
                        <h3 class="text-2xl font-bold text-green-600"><?= formatPrice($stats['total_revenue']) ?></h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-500">
                        <span class="material-symbols-outlined">payments</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-500 mb-1"><?= $promo['type'] === 'voucher' ? 'Tổng giá trị giảm' : 'Lượt xem (Dự kiến)' ?></p>
                        <h3 class="text-2xl font-bold text-axeron-red">
                            <?= $promo['type'] === 'voucher' ? formatPrice($stats['total_discount']) : 'N/A' ?>
                        </h3>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-axeron-red">
                        <span class="material-symbols-outlined">local_offer</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($promo['type'] !== 'voucher'): ?>
        <!-- Danh sách Sản phẩm áp dụng (nếu là product, flashsale, category) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Sản phẩm được áp dụng</h2>
            <?php
            $targets = [];
            if ($promo['type'] === 'product' || $promo['type'] === 'flashsale') {
                $targets = $db->select("
                    SELECT p.product_name, p.base_price, p.slug 
                    FROM products p JOIN promotion_products pp ON p.product_id = pp.product_id 
                    WHERE pp.promo_id = ?
                ", [$promo_id]);
            } elseif ($promo['type'] === 'category') {
                $targets = $db->select("
                    SELECT p.product_name, p.base_price, p.slug, c.category_name 
                    FROM products p 
                    JOIN categories c ON p.category_id = c.category_id
                    JOIN promotion_categories pc ON c.category_id = pc.category_id 
                    WHERE pc.promo_id = ?
                ", [$promo_id]);
            }
            ?>
            <?php if (!empty($targets)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-sm text-gray-500 border-b border-gray-100">
                            <th class="pb-3">Sản phẩm</th>
                            <th class="pb-3">Giá gốc</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($targets as $t): ?>
                        <tr>
                            <td class="py-3 text-sm font-medium text-gray-800">
                                <?= htmlspecialchars($t['product_name']) ?>
                                <?php if(isset($t['category_name'])): ?>
                                <span class="text-xs text-gray-500 block"><?= htmlspecialchars($t['category_name']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-sm text-gray-600"><?= formatPrice($t['base_price']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-gray-500 text-sm">Chưa có sản phẩm nào được chọn.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Thông tin cấu hình -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Thông tin cấu hình</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Phân loại</p>
                    <p class="font-medium mt-1"><span class="px-2 py-1 rounded text-xs <?= $tLabel[1] ?>"><?= $tLabel[0] ?></span></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Mức giảm</p>
                    <p class="font-medium mt-1 text-axeron-red">
                        <?= $promo['discount_type'] === 'percent' ? (float)$promo['discount_value'] . '%' : formatPrice($promo['discount_value']) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Thời gian diễn ra</p>
                    <p class="font-medium mt-1 text-gray-800 text-sm">
                        <?= date('H:i d/m/Y', strtotime($promo['start_date'])) ?> <br>
                        <span class="text-gray-400">đến</span> <br>
                        <?= date('H:i d/m/Y', strtotime($promo['end_date'])) ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Điều kiện đơn tối thiểu</p>
                    <p class="font-medium mt-1"><?= formatPrice($promo['min_order_value']) ?></p>
                </div>
                <?php if ($promo['max_discount']): ?>
                <div>
                    <p class="text-sm text-gray-500">Giảm tối đa</p>
                    <p class="font-medium mt-1 text-red-600"><?= formatPrice($promo['max_discount']) ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <p class="text-sm text-gray-500">Giới hạn sử dụng</p>
                    <p class="font-medium mt-1">
                        <?= $promo['usage_limit'] ? number_format($promo['usage_limit']) . ' lần' : 'Không giới hạn' ?>
                        <span class="text-gray-400 font-normal"> (Đã dùng: <?= number_format($promo['used_count']) ?>)</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
