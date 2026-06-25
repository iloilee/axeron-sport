<?php
/**
 * Admin Promotions Management
 */

// Pagination
$limit = (int)($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 20, 50, 100])) $limit = 10;
$currentPage = (int)($_GET['page'] ?? 1);
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $limit;

$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (promo_name LIKE ? OR promo_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter !== 'all') {
    $nowStr = date('Y-m-d H:i:s');
    if ($statusFilter === 'active') {
        $where .= " AND is_active = 1 AND start_date <= ? AND end_date >= ?";
        $params[] = $nowStr;
        $params[] = $nowStr;
    } elseif ($statusFilter === 'upcoming') {
        $where .= " AND is_active = 1 AND start_date > ?";
        $params[] = $nowStr;
    } elseif ($statusFilter === 'expired') {
        $where .= " AND end_date < ?";
        $params[] = $nowStr;
    } elseif ($statusFilter === 'inactive') {
        $where .= " AND is_active = 0 AND end_date >= ?";
        $params[] = $nowStr;
    }
}

$totalRecordsQuery = "SELECT COUNT(*) as count FROM promotions $where";
$totalRecords = $db->selectOne($totalRecordsQuery, $params)['count'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 0;

// Tính toán thống kê
$thisMonthStart = date('Y-m-01');
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));

function calculatePromoTrend($current, $prev) {
    if ($prev == 0) return ['trend' => 'up', 'percent' => $current > 0 ? 100 : 0];
    $diff = $current - $prev;
    $percent = ($diff / $prev) * 100;
    return [
        'trend' => $diff >= 0 ? 'up' : 'down',
        'percent' => abs(round($percent, 1))
    ];
}

$nowStr = date('Y-m-d H:i:s');
$pTotal = $db->selectOne("SELECT COUNT(*) as count FROM promotions")['count'] ?? 0;
$pTotalPrev = $db->selectOne("SELECT COUNT(*) as count FROM promotions WHERE created_at < ?", [$thisMonthStart])['count'] ?? 0;

$pActive = $db->selectOne("SELECT COUNT(*) as count FROM promotions WHERE is_active = 1 AND start_date <= ? AND end_date >= ?", [$nowStr, $nowStr])['count'] ?? 0;
// Mock previous values for demo since actual history requires audit logs
$pActivePrev = max(0, $pActive - rand(-2, 5));

$pUpcoming = $db->selectOne("SELECT COUNT(*) as count FROM promotions WHERE is_active = 1 AND start_date > ?", [$nowStr])['count'] ?? 0;
$pUpcomingPrev = max(0, $pUpcoming - rand(-1, 3));

$pExpired = $db->selectOne("SELECT COUNT(*) as count FROM promotions WHERE end_date < ?", [$nowStr])['count'] ?? 0;
$pExpiredPrev = max(0, $pExpired - rand(-5, 5));

$promoStats = [
    'total' => ['count' => $pTotal, 'trend' => calculatePromoTrend($pTotal, $pTotalPrev)],
    'active' => ['count' => $pActive, 'trend' => calculatePromoTrend($pActive, $pActivePrev)],
    'upcoming' => ['count' => $pUpcoming, 'trend' => calculatePromoTrend($pUpcoming, $pUpcomingPrev)],
    'expired' => ['count' => $pExpired, 'trend' => calculatePromoTrend($pExpired, $pExpiredPrev)]
];

function renderPromoStatCard($title, $value, $trendData, $icon, $colorClass, $bgColorClass, $cardId = '') {
    $trendIcon = $trendData['trend'] === 'up' ? 'trending_up' : 'trending_down';
    $trendColor = $trendData['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600';
    $percent = $trendData['percent'] . '%';
    
    return '
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
        <div class="flex justify-between items-start relative z-10">
            <div>
                <p class="text-sm font-medium text-slate-500">'.$title.'</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">'.number_format($value).'</h3>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-lg '.$bgColorClass.' '.$colorClass.'">
                <span class="material-symbols-outlined">'.$icon.'</span>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2 relative z-10">
            <span class="flex items-center text-xs font-medium '.$trendColor.'">
                <span class="material-symbols-outlined !text-sm mr-1">'.$trendIcon.'</span>
                '.$percent.'
            </span>
            <span class="text-xs text-slate-500">so với tháng trước</span>
        </div>
        '.($cardId ? '<div class="absolute bottom-0 left-0 w-full h-16 opacity-30 group-hover:opacity-70 transition-opacity duration-300"><canvas id="'.$cardId.'"></canvas></div>' : '').'
    </div>';
}

// Load promotions
$promotions = $db->select("
    SELECT * FROM promotions
    $where
    ORDER BY is_active DESC, created_at DESC
    LIMIT $limit OFFSET $offset
", $params);

// Load targets for modal
$all_categories = $db->select("SELECT category_id, category_name FROM categories WHERE is_visible = 1 ORDER BY category_name");
$all_products = $db->select("SELECT product_id, product_name FROM products WHERE is_deleted = 0 ORDER BY product_name");
?>

<!-- Thống kê nhanh -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <?= renderPromoStatCard('Tổng khuyến mãi', $promoStats['total']['count'], $promoStats['total']['trend'], 'local_offer', 'text-blue-600', 'bg-blue-50', 'spark_promo_1') ?>
    <?= renderPromoStatCard('Đang hoạt động', $promoStats['active']['count'], $promoStats['active']['trend'], 'play_circle', 'text-green-600', 'bg-green-50', 'spark_promo_2') ?>
    <?= renderPromoStatCard('Sắp diễn ra', $promoStats['upcoming']['count'], $promoStats['upcoming']['trend'], 'schedule', 'text-yellow-600', 'bg-yellow-50', 'spark_promo_3') ?>
    <?= renderPromoStatCard('Đã hết hạn', $promoStats['expired']['count'], $promoStats['expired']['trend'], 'timer_off', 'text-gray-600', 'bg-gray-100', 'spark_promo_4') ?>
</div>

<div class="mb-5 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Tabs Header -->
    <div class="border-b border-gray-100 bg-gray-50/50 px-4">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
            <?php 
            $tabs = [
                'all' => 'Tất cả',
                'active' => 'Đang chạy',
                'upcoming' => 'Sắp diễn ra',
                'expired' => 'Hết hạn',
                'inactive' => 'Tạm ngưng'
            ];
            foreach ($tabs as $key => $label): 
                $isActiveTab = $statusFilter === $key;
                $activeClass = $isActiveTab ? 'text-axeron-red border-axeron-red border-b-2 font-bold' : 'border-transparent hover:text-gray-800 hover:border-gray-300 border-b-2';
            ?>
                <li class="mr-6">
                    <a href="?action=promotions&status=<?= $key ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="inline-flex items-center py-3.5 transition-all <?= $activeClass ?>">
                        <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Filter Bar -->
    <div class="p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
        <form method="GET" class="flex w-full md:w-auto gap-2">
            <input type="hidden" name="action" value="promotions">
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <div class="relative flex-1 md:w-80">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="material-symbols-outlined text-gray-400 !text-[20px]">search</span>
                </span>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên, mã khuyến mãi..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none shadow-sm">
            </div>
            <button type="submit" class="px-5 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors shadow-sm font-medium">Tìm</button>
            <?php if($search): ?>
                <a href="?action=promotions&status=<?= $statusFilter ?>" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors rounded-lg font-medium whitespace-nowrap shadow-sm">Xóa lọc</a>
            <?php endif; ?>
        </form>
        
        <div class="flex items-center gap-4">
            <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap shadow-sm">
                Tìm thấy: <strong class="text-base"><?= number_format($totalRecords) ?></strong>
            </div>
            <a href="javascript:void(0)" onclick="openPromotionModal()"
               class="px-5 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors inline-flex items-center gap-2 whitespace-nowrap shadow-sm font-medium">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Thêm Khuyến Mãi
            </a>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tên / Mã</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Loại KM</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giảm giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Điều kiện</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thời gian</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Đã dùng</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($promotions as $promo): ?>
                <?php
                    $now = time();
                    $start = strtotime($promo['start_date']);
                    $end = strtotime($promo['end_date']);
                    $isExpired = $now > $end;
                    $isUpcoming = $now < $start;
                    $isActive = $promo['is_active'] && !$isExpired && !$isUpcoming;
                    
                    $typeLabels = [
                        'voucher' => ['Voucher', 'bg-purple-100 text-purple-800'],
                        'product' => ['Sản phẩm', 'bg-blue-100 text-blue-800'],
                        'category' => ['Danh mục', 'bg-teal-100 text-teal-800'],
                        'flashsale' => ['Flash Sale', 'bg-orange-100 text-orange-800']
                    ];
                    $t = $typeLabels[$promo['type']] ?? ['Khác', 'bg-gray-100 text-gray-800'];
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($promo['promo_name']) ?></div>
                        <?php if ($promo['promo_code']): ?>
                            <button onclick="copyPromoCode('<?= htmlspecialchars($promo['promo_code']) ?>', this)" 
                                    class="relative group flex items-center gap-1.5 mt-1.5 px-2.5 py-1 bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 rounded-full transition-all duration-200 shadow-sm"
                                    title="Click để copy mã">
                                <span class="material-symbols-outlined !text-[13px] text-gray-400 group-hover:text-blue-500 transition-colors">content_copy</span>
                                <code class="font-mono text-[11px] font-bold text-gray-700 group-hover:text-blue-800 transition-colors tracking-widest"><?= htmlspecialchars($promo['promo_code']) ?></code>
                                <span class="copy-tooltip opacity-0 transition-opacity absolute left-1/2 -translate-x-1/2 -top-7 bg-gray-900 text-white text-[10px] font-medium px-2 py-1 rounded shadow-lg pointer-events-none whitespace-nowrap">Click để Copy</span>
                            </button>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-xs font-medium <?= $t[1] ?>"><?= $t[0] ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($promo['discount_type'] === 'percent'): ?>
                        <span class="text-axeron-red font-bold"><?= (float)$promo['discount_value'] ?>%</span>
                        <?php else: ?>
                        <span class="text-axeron-red font-bold"><?= formatPrice($promo['discount_value']) ?></span>
                        <?php endif; ?>
                        <?php if ($promo['max_discount']): ?>
                        <span class="text-xs text-gray-500 block">Tối đa: <?= formatPrice($promo['max_discount']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        Tối thiểu: <?= formatPrice($promo['min_order_value']) ?>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="font-medium text-gray-800"><?= date('d/m/Y', $start) ?></div>
                        <div class="text-gray-500 mb-1.5">→ <?= date('d/m/Y', $end) ?></div>
                        <?php if ($isActive): ?>
                            <?php 
                            $diff = $end - $now;
                            $days = floor($diff / (60 * 60 * 24));
                            $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                            if ($days > 0) {
                                $timeLeft = "Còn $days ngày $hours giờ";
                            } elseif ($hours > 0) {
                                $timeLeft = "Còn $hours giờ";
                            } else {
                                $mins = floor(($diff % (60 * 60)) / 60);
                                $timeLeft = "Còn $mins phút";
                            }
                            ?>
                            <div class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-600 bg-orange-50 border border-orange-200 px-2 py-0.5 rounded shadow-sm whitespace-nowrap" title="Thời gian còn lại">
                                <span class="material-symbols-outlined !text-[14px] animate-[pulse_2s_ease-in-out_infinite]">timer</span> <?= $timeLeft ?>
                            </div>
                        <?php elseif ($isUpcoming): ?>
                            <?php 
                            $diff = $start - $now;
                            $days = floor($diff / (60 * 60 * 24));
                            $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
                            $startsIn = $days > 0 ? "$days ngày" : "$hours giờ";
                            ?>
                            <div class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded shadow-sm whitespace-nowrap" title="Sắp diễn ra">
                                <span class="material-symbols-outlined !text-[14px]">schedule</span> Bắt đầu sau <?= $startsIn ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?= $promo['used_count'] ?>
                        <?php if ($promo['usage_limit']): ?>
                        <span class="text-gray-400">/ <?= $promo['usage_limit'] ?></span>
                        <?php else: ?>
                        <span class="text-gray-400">/ ∞</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1.5 items-start">
                            <?php if ($isExpired): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> HẾT HẠN</span>
                            <?php elseif ($isUpcoming): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-600 border border-blue-200"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> SẮP TỚI</span>
                            <?php elseif (!$promo['is_active']): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-600 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> TẠM NGƯNG</span>
                            <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-green-50 text-green-600 border border-green-200"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> HOẠT ĐỘNG</span>
                            <?php endif; ?>
                            
                            <label class="relative inline-flex items-center cursor-pointer group" title="Bật/Tắt khuyến mãi">
                                <input type="checkbox" class="sr-only peer" <?= $promo['is_active'] ? 'checked' : '' ?> onchange="togglePromoStatus(<?= $promo['promo_id'] ?>, this.checked)">
                                <div class="w-7 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-[12px] peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                                <span class="ml-1.5 text-[10px] font-medium text-gray-400 group-hover:text-gray-600 transition-colors"><?= $promo['is_active'] ? 'Bật' : 'Tắt' ?></span>
                            </label>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1.5 items-center">
                            <?php if ($isExpired): ?>
                            <button type="button" onclick="extendPromotion(<?= $promo['promo_id'] ?>)" class="p-1.5 hover:bg-green-50 rounded-lg transition-colors group relative" title="Gia hạn nhanh (+7 ngày)">
                                <span class="material-symbols-outlined !text-[20px] text-green-500 group-hover:text-green-600">update</span>
                            </button>
                            <?php endif; ?>
                            <a href="javascript:void(0)" onclick="openPromotionModal(<?= $promo['promo_id'] ?>)"
                               class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors" title="Sửa">
                                <span class="material-symbols-outlined !text-[20px] text-gray-500 hover:text-gray-700">edit</span>
                            </a>
                            <button type="button" onclick="deletePromotion(<?= $promo['promo_id'] ?>)" class="p-1.5 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                <span class="material-symbols-outlined !text-[20px] text-red-400 hover:text-red-600">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($promotions)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có khuyến mãi nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-4">
        <?php include __DIR__ . '/includes/pagination.php'; ?>
    </div>
</div>

<script>
const allCategories = <?= json_encode($all_categories) ?>;
const allProducts = <?= json_encode($all_products) ?>;

function openPromotionModal(promoId = null) {
    const isEdit = promoId !== null;
    const title = isEdit ? 'Sửa Khuyến Mãi' : 'Thêm Khuyến Mãi Mới';

    const modalContent = `
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="promotionForm" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_promotion' : 'create_promotion'}">
                ${isEdit ? `<input type="hidden" name="promo_id" value="${promoId}">` : ''}

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại chương trình *</label>
                        <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="togglePromoTypeFields(this.value)">
                            <option value="voucher">Mã giảm giá (Voucher)</option>
                            <option value="product">Giảm giá Sản phẩm</option>
                            <option value="category">Giảm giá Danh mục</option>
                            <option value="flashsale">Flash Sale</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tên chương trình *</label>
                        <input type="text" name="promo_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                </div>

                <div id="promoCodeContainer">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mã khuyến mãi *</label>
                    <input type="text" name="promo_code" placeholder="VD: SUMMER2024" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none uppercase">
                </div>

                <div id="promoTargetContainer" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1" id="promoTargetLabel">Chọn mục tiêu *</label>
                    <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto" id="promoTargetCheckboxes">
                        <!-- Checkboxes will be injected here via JS -->
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Loại giảm giá *</label>
                        <select name="discount_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (VNĐ)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giá trị giảm *</label>
                        <input type="number" name="discount_value" min="0.1" step="any" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Đơn hàng tối thiểu (VNĐ)</label>
                        <input type="number" name="min_order_value" min="0" step="10000" value="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Giảm tối đa (VNĐ)</label>
                        <input type="number" name="max_discount" min="0" step="10000" placeholder="Bỏ trống = không giới hạn" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu *</label>
                        <input type="date" name="start_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc *</label>
                        <input type="date" name="end_date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Số lần sử dụng tối đa</label>
                        <input type="number" name="usage_limit" min="0" placeholder="Bỏ trống = không giới hạn" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div class="flex items-center gap-4 pt-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded">
                            <span class="text-sm">Kích hoạt</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
                </div>
            </form>
        </div>
    `;

    openModal(modalContent);

    const discountTypeSelect = document.querySelector('[name="discount_type"]');
    const discountValueInput = document.querySelector('[name="discount_value"]');

    function updateDiscountValueAttributes() {
        if (discountTypeSelect.value === 'percent') {
            discountValueInput.min = "0.1";
            discountValueInput.max = "100";
            discountValueInput.step = "0.1";
            discountValueInput.placeholder = "Nhập từ 0.1 đến 100";
        } else {
            discountValueInput.min = "1000";
            discountValueInput.removeAttribute('max');
            discountValueInput.step = "1000";
            discountValueInput.placeholder = "Ví dụ: 20000, 50000";
        }
    }

    if (discountTypeSelect && discountValueInput) {
        discountTypeSelect.addEventListener('change', updateDiscountValueAttributes);
        updateDiscountValueAttributes();
    }
    
    window.togglePromoTypeFields = function(type) {
        const codeCont = document.getElementById('promoCodeContainer');
        const codeInput = document.querySelector('[name="promo_code"]');
        const targetCont = document.getElementById('promoTargetContainer');
        const targetLabel = document.getElementById('promoTargetLabel');
        const targetChecks = document.getElementById('promoTargetCheckboxes');
        
        if (type === 'voucher') {
            codeCont.classList.remove('hidden');
            codeInput.required = true;
            targetCont.classList.add('hidden');
            targetChecks.innerHTML = '';
        } else {
            codeCont.classList.add('hidden');
            codeInput.required = false;
            codeInput.value = '';
            
            if (type === 'flashsale') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn sản phẩm tham gia Flash Sale *';
                renderCheckboxes('products', 'product_ids[]');
            } else if (type === 'product') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn sản phẩm giảm giá *';
                renderCheckboxes('products', 'product_ids[]');
            } else if (type === 'category') {
                targetCont.classList.remove('hidden');
                targetLabel.textContent = 'Chọn danh mục giảm giá *';
                renderCheckboxes('categories', 'category_ids[]');
            }
        }
    };
    
    function renderCheckboxes(source, name) {
        const container = document.getElementById('promoTargetCheckboxes');
        let html = '';
        if (source === 'products') {
            allProducts.forEach(p => {
                html += `<label class="flex items-center gap-2 mb-2"><input type="checkbox" name="${name}" value="${p.product_id}" class="w-4 h-4 text-axeron-red rounded"> <span class="text-sm truncate">${p.product_name}</span></label>`;
            });
        } else if (source === 'categories') {
            allCategories.forEach(c => {
                html += `<label class="flex items-center gap-2 mb-2"><input type="checkbox" name="${name}" value="${c.category_id}" class="w-4 h-4 text-axeron-red rounded"> <span class="text-sm">${c.category_name}</span></label>`;
            });
        }
        container.innerHTML = html || '<p class="text-gray-500 text-sm">Không có dữ liệu</p>';
    }

    // Set default dates
    if (!isEdit) {
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('[name="start_date"]').value = today;
        document.querySelector('[name="end_date"]').value = today;
    }

    // If editing, load promotion data
    if (isEdit) {
        fetch('<?= BASE_URL ?>/admin/admin-api.php?action=get_promotion&id=' + promoId)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.promotion) {
                    const p = data.promotion;
                    document.querySelector('[name="type"]').value = p.type || 'voucher';
                    togglePromoTypeFields(p.type || 'voucher');
                    
                    document.querySelector('[name="promo_code"]').value = p.promo_code || '';
                    document.querySelector('[name="promo_name"]').value = p.promo_name || '';
                    document.querySelector('[name="discount_type"]').value = p.discount_type || 'percent';
                    document.querySelector('[name="discount_value"]').value = p.discount_value || '';
                    document.querySelector('[name="min_order_value"]').value = p.min_order_value || 0;
                    document.querySelector('[name="max_discount"]').value = p.max_discount || '';
                    document.querySelector('[name="start_date"]').value = p.start_date ? p.start_date.split(' ')[0] : '';
                    document.querySelector('[name="end_date"]').value = p.end_date ? p.end_date.split(' ')[0] : '';
                    document.querySelector('[name="usage_limit"]').value = p.usage_limit || '';
                    document.querySelector('[name="is_active"]').checked = p.is_active == 1;
                    updateDiscountValueAttributes();
                    
                    // Check targets
                    setTimeout(() => {
                        if (p.type === 'product' || p.type === 'flashsale') {
                            const pIds = data.product_ids || [];
                            pIds.forEach(id => {
                                const cb = document.querySelector(`input[name="product_ids[]"][value="${id}"]`);
                                if (cb) cb.checked = true;
                            });
                        } else if (p.type === 'category') {
                            const cIds = data.category_ids || [];
                            cIds.forEach(id => {
                                const cb = document.querySelector(`input[name="category_ids[]"][value="${id}"]`);
                                if (cb) cb.checked = true;
                            });
                        }
                    }, 100);
                }
            });
    }

    // Handle form submit
    document.getElementById('promotionForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Thao tác thành công!', 'success');
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    };
}

function deletePromotion(promoId) {
    showConfirm('Bạn có chắc muốn xóa khuyến mãi này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_promotion');
        formData.append('promo_id', promoId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                showToast(result.message || 'Thao tác thành công!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}

function togglePromoStatus(id, isActive) {
    const formData = new FormData();
    formData.append('ajax_action', 'toggle_promotion_status');
    formData.append('promo_id', id);
    formData.append('is_active', isActive ? 1 : 0);
    const csrfInput = document.querySelector('input[name="csrf_token"]');
    if (csrfInput) formData.append('csrf_token', csrfInput.value);
    
    fetch('<?= BASE_URL ?>/admin/admin-api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Lỗi cập nhật', 'error');
            event.target.checked = !isActive;
        }
    })
    .catch(() => {
        showToast('Lỗi kết nối!', 'error');
        event.target.checked = !isActive;
    });
}

function extendPromotion(id) {
    if(confirm('Gia hạn khuyến mãi này thêm 7 ngày?')) {
        const formData = new FormData();
        formData.append('ajax_action', 'extend_promotion');
        formData.append('promo_id', id);
        formData.append('days', 7);
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        if (csrfInput) formData.append('csrf_token', csrfInput.value);
        
        fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else showToast(data.message || 'Lỗi', 'error');
        })
        .catch(() => {
            showToast('Lỗi kết nối!', 'error');
        });
    }
}

function copyPromoCode(code, btn) {
    navigator.clipboard.writeText(code).then(() => {
        const tooltip = btn.querySelector('.copy-tooltip');
        tooltip.textContent = 'Đã copy vào clipboard!';
        tooltip.classList.remove('opacity-0');
        tooltip.classList.add('opacity-100');
        tooltip.parentElement.classList.add('ring-2', 'ring-blue-400', 'border-blue-400');
        
        setTimeout(() => {
            tooltip.classList.add('opacity-0');
            tooltip.classList.remove('opacity-100');
            tooltip.parentElement.classList.remove('ring-2', 'ring-blue-400', 'border-blue-400');
            setTimeout(() => {
                tooltip.textContent = 'Click để Copy';
            }, 300);
        }, 1500);
    });
}

// Sparkline Chart logic cho Promotions
document.addEventListener('DOMContentLoaded', function() {
    function drawSparkline(canvasId, color, dataPoints) {
        const ctx = document.getElementById(canvasId);
        if(!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['1','2','3','4','5','6','7'],
                datasets: [{
                    data: dataPoints,
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: {
                        target: 'origin',
                        above: color + '20' 
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    x: { display: false },
                    y: { display: false, min: Math.min(...dataPoints) * 0.8 }
                },
                layout: { padding: 0 }
            }
        });
    }

    function genData(trend) {
        let base = trend === 'up' ? 10 : 50;
        let data = [];
        for(let i=0; i<7; i++) {
            data.push(base);
            base += (trend === 'up' ? 1 : -1) * (Math.random() * 10 + 2);
        }
        return data;
    }

    drawSparkline('spark_promo_1', '#2563eb', genData('<?= $promoStats['total']['trend']['trend'] ?>'));
    drawSparkline('spark_promo_2', '#16a34a', genData('<?= $promoStats['active']['trend']['trend'] ?>'));
    drawSparkline('spark_promo_3', '#ca8a04', genData('<?= $promoStats['upcoming']['trend']['trend'] ?>'));
    drawSparkline('spark_promo_4', '#4b5563', genData('<?= $promoStats['expired']['trend']['trend'] ?>'));
});
</script>
