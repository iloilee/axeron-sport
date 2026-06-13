<?php
/**
 * Admin Products Management
 */

// Load products
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$brandFilter = $_GET['brand'] ?? '';

// Pagination
$limit = (int)($_GET['limit'] ?? 10);
if (!in_array($limit, [10, 20, 50, 100])) $limit = 10;
$currentPage = (int)($_GET['page'] ?? 1);
if ($currentPage < 1) $currentPage = 1;
$offset = ($currentPage - 1) * $limit;

$where = "WHERE p.is_deleted = 0";
$params = [];

if ($search) {
    $where .= " AND (p.product_name LIKE ? OR p.slug LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($brandFilter) {
    $where .= " AND p.brand_id = ?";
    $params[] = $brandFilter;
}

$totalRecordsQuery = "
    SELECT COUNT(*) as count 
    FROM products p 
    $where
";
$totalRecords = $db->selectOne($totalRecordsQuery, $params)['count'] ?? 0;
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $limit) : 0;

$products = $db->select("
    SELECT p.*, c.category_name, b.brand_name,
           (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as image_url
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    $where
    ORDER BY p.updated_at DESC
    LIMIT $limit OFFSET $offset
", $params);

// Categories for filter
$categories = $db->select("SELECT category_id, category_name FROM categories ORDER BY category_name");

// Brands for filter
$all_brands = $db->select("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_action'])) {
    require_once __DIR__ . '/admin-api.php';
    exit;
}
// Lấy thống kê tổng quan
$thisMonthStart = date('Y-m-01');
$lastMonthStart = date('Y-m-01', strtotime('-1 month'));

$productStatsRaw = $db->selectOne("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_count,
        SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
        SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_count,
        SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN is_visible = 0 THEN 1 ELSE 0 END) as inactive_count,
        SUM(base_price * stock_quantity) as total_inventory_value
    FROM products
    WHERE is_deleted = 0
");

$bestSellersStats = $db->selectOne("
    SELECT COUNT(*) as count 
    FROM (
        SELECT p.product_id 
        FROM products p 
        JOIN product_variants pv ON p.product_id = pv.product_id
        JOIN order_items oi ON pv.variant_id = oi.variant_id 
        WHERE p.is_deleted = 0 
        GROUP BY p.product_id 
        HAVING SUM(oi.quantity) >= 10
    ) t
");
$productStatsRaw['best_sellers_count'] = $bestSellersStats['count'] ?? 0;

$productStatsPrev = $db->selectOne("
    SELECT 
        COUNT(*) as total_count,
        SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_count,
        SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
        SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_count,
        SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN is_visible = 0 THEN 1 ELSE 0 END) as inactive_count,
        SUM(base_price * stock_quantity) as total_inventory_value
    FROM products
    WHERE is_deleted = 0 AND created_at < ?
", [$thisMonthStart]);

$bestSellersStatsPrev = $db->selectOne("
    SELECT COUNT(*) as count 
    FROM (
        SELECT p.product_id 
        FROM products p 
        JOIN product_variants pv ON p.product_id = pv.product_id
        JOIN order_items oi ON pv.variant_id = oi.variant_id 
        WHERE p.is_deleted = 0 AND p.created_at < ?
        GROUP BY p.product_id 
        HAVING SUM(oi.quantity) >= 10
    ) t
", [$thisMonthStart]);
$productStatsPrev['best_sellers_count'] = $bestSellersStatsPrev['count'] ?? 0;

function calculateProductTrend($current, $prev) {
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

$pStats = [
    'total' => ['count' => $productStatsRaw['total_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['total_count'], $productStatsPrev['total_count'])],
    'featured' => ['count' => $productStatsRaw['featured_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['featured_count'], $productStatsPrev['featured_count'])],
    'bestsellers' => ['count' => $productStatsRaw['best_sellers_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['best_sellers_count'], $productStatsPrev['best_sellers_count'])],
    'outofstock' => ['count' => $productStatsRaw['out_of_stock_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['out_of_stock_count'], $productStatsPrev['out_of_stock_count'])],
    'lowstock' => ['count' => $productStatsRaw['low_stock_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['low_stock_count'], $productStatsPrev['low_stock_count'])],
    'active' => ['count' => $productStatsRaw['active_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['active_count'], $productStatsPrev['active_count'])],
    'inactive' => ['count' => $productStatsRaw['inactive_count'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['inactive_count'], $productStatsPrev['inactive_count'])],
    'inventory_value' => ['count' => $productStatsRaw['total_inventory_value'] ?? 0, 'trend' => calculateProductTrend($productStatsRaw['total_inventory_value'], $productStatsPrev['total_inventory_value'])]
];

function renderProductStatCard($title, $value, $trendData, $icon, $colorClass, $bgColorClass, $isCurrency = false) {
    $trendIcon = $trendData['trend'] === 'up' ? 'trending_up' : 'trending_down';
    $trendColor = $trendData['trend'] === 'up' ? 'text-emerald-600' : 'text-red-600';
    $percent = $trendData['percent'] . '%';
    
    // Rút gọn hiển thị tiền cho đẹp
    $displayValue = $value;
    $unit = '';
    if ($isCurrency) {
        if ($value >= 1000000000) {
            $displayValue = round($value / 1000000000, 1) . 'T';
        } elseif ($value >= 1000000) {
            $displayValue = round($value / 1000000, 1) . 'M';
        } elseif ($value >= 1000) {
            $displayValue = round($value / 1000, 1) . 'K';
        } else {
            $displayValue = number_format($value);
        }
        $unit = ' <span class="text-sm font-normal text-slate-500">vnđ</span>';
    } else {
        $displayValue = number_format($value);
    }
    
    return '
    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500 truncate" title="'.$title.'">'.$title.'</p>
                <h3 class="mt-1 text-2xl font-bold text-slate-900">'.$displayValue.$unit.'</h3>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg '.$bgColorClass.' '.$colorClass.'">
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
?>

<!-- Thống kê nhanh -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-4 mb-6">
    <?= renderProductStatCard('Tổng sản phẩm', $pStats['total']['count'], $pStats['total']['trend'], 'inventory', 'text-blue-600', 'bg-blue-50') ?>
    <?= renderProductStatCard('Sản phẩm nổi bật', $pStats['featured']['count'], $pStats['featured']['trend'], 'star', 'text-yellow-600', 'bg-yellow-50') ?>
    <?= renderProductStatCard('Bán chạy (>10sp)', $pStats['bestsellers']['count'], $pStats['bestsellers']['trend'], 'local_fire_department', 'text-orange-600', 'bg-orange-50') ?>
    <?= renderProductStatCard('Hết hàng', $pStats['outofstock']['count'], $pStats['outofstock']['trend'], 'remove_shopping_cart', 'text-red-600', 'bg-red-50') ?>
    <?= renderProductStatCard('Sắp hết hàng', $pStats['lowstock']['count'], $pStats['lowstock']['trend'], 'warning', 'text-amber-600', 'bg-amber-50') ?>
    <?= renderProductStatCard('Đang hoạt động', $pStats['active']['count'], $pStats['active']['trend'], 'check_circle', 'text-green-600', 'bg-green-50') ?>
    <?= renderProductStatCard('Ngừng bán', $pStats['inactive']['count'], $pStats['inactive']['trend'], 'do_not_disturb_on', 'text-gray-600', 'bg-gray-100') ?>
    <?= renderProductStatCard('Tổng giá trị tồn kho', $pStats['inventory_value']['count'], $pStats['inventory_value']['trend'], 'payments', 'text-emerald-600', 'bg-emerald-50', true) ?>
</div>

<div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
    <div class="flex flex-col xl:flex-row gap-3 items-start xl:items-center">
        <form method="GET" class="flex gap-3 flex-wrap">
            <input type="hidden" name="action" value="products">
            <input type="text" name="search" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($search) ?>"
                   class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none">
            <select name="category" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $categoryFilter == $cat['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="brand" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tất cả thương hiệu</option>
                <?php foreach ($all_brands as $brand): ?>
                <option value="<?= $brand['brand_id'] ?>" <?= $brandFilter == $brand['brand_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($brand['brand_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="px-4 py-2 bg-red-50 border border-red-100 rounded-lg text-sm font-medium text-axeron-red whitespace-nowrap">
            Tổng số: <strong class="text-base"><?= number_format($totalRecords) ?></strong> sản phẩm
        </div>
    </div>
    <a href="javascript:void(0)" onclick="openProductModal()"
       class="px-4 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 whitespace-nowrap">
        <span class="material-symbols-outlined text-xl">add</span>
        Thêm Sản Phẩm
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sản phẩm</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Danh mục</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thương hiệu</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Giá</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tồn kho</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nổi bật</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($products as $product): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="<?= getImageUrl($product['image_url'], 'https://placehold.co/60x60') ?>"
                                 alt="" class="w-12 h-12 object-cover rounded-lg bg-gray-100">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($product['product_name']) ?></p>
                                <p class="text-xs text-gray-500">ID: <?= $product['product_id'] ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($product['brand_name'] ?? 'N/A') ?></td>
                    <td class="px-4 py-3 text-sm font-medium text-axeron-red"><?= formatPrice($product['base_price']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= number_format($product['stock_quantity']) ?></td>
                    <td class="px-4 py-3">
                        <label class="relative inline-flex items-center cursor-pointer" title="Nhấn để bật/tắt nổi bật">
                            <input type="checkbox" class="sr-only peer" onchange="toggleProductFeatured(<?= $product['product_id'] ?>, this)" <?= $product['is_featured'] ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-yellow-500"></div>
                            <span class="ml-2 text-xs font-medium text-gray-700 featured-label"><?= $product['is_featured'] ? 'Nổi bật' : '-' ?></span>
                        </label>
                    </td>
                    <td class="px-4 py-3">
                        <label class="relative inline-flex items-center cursor-pointer" title="Nhấn để ẩn/hiện sản phẩm">
                            <input type="checkbox" class="sr-only peer" onchange="toggleProductVisibility(<?= $product['product_id'] ?>, this)" <?= $product['is_visible'] ? 'checked' : '' ?>>
                            <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                            <span class="ml-2 text-xs font-medium text-gray-700 visibility-label"><?= $product['is_visible'] ? 'Hiển thị' : 'Đang ẩn' ?></span>
                        </label>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="javascript:void(0)" onclick="openProductModal(<?= $product['product_id'] ?>)"
                               class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Sửa">
                                <span class="material-symbols-outlined text-gray-600">edit</span>
                            </a>
                            <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= $product['slug'] ?>"
                               target="_blank" class="p-2 hover:bg-gray-100 rounded-lg transition-colors" title="Xem">
                                <span class="material-symbols-outlined text-gray-600">visibility</span>
                            </a>
                            <button onclick="deleteProduct(<?= $product['product_id'] ?>)"
                                    class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">Không tìm thấy sản phẩm nào</td>
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
// Store categories and brands globally for modal
const categories = <?= json_encode($categories) ?>;
const brands = <?= json_encode($db->select("SELECT brand_id, brand_name FROM brands WHERE is_active = 1")) ?>;
const allSystemColors = <?= json_encode(array_column($db->select("SELECT DISTINCT color FROM product_variants WHERE color IS NOT NULL AND color != '' AND color != 'default' AND is_deleted = 0 ORDER BY color"), 'color')) ?>;

// Current product images (for editing)
let currentProductImages = [];
let currentProductId = null;
let currentProductColors = [];
// Store pending images for create mode
let pendingImages = [];

function openProductModal(productId = null) {
    const isEdit = productId !== null;
    currentProductId = productId;
    currentProductImages = [];
    currentProductColors = [];
    pendingImages = []; // Reset pending images

    const title = isEdit ? 'Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới';

    const modalContent = `
        <div class="p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">${title}</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="productForm" method="POST" class="space-y-4">
                <input type="hidden" name="ajax_action" value="${isEdit ? 'update_product' : 'create_product'}">
                ${isEdit ? `<input type="hidden" name="product_id" value="${productId}">` : ''}

                <!-- Product Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên sản phẩm *</label>
                    <input type="text" name="product_name" id="product_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-axeron-red outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Danh mục *</label>
                        <select name="category_id" id="category_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            ${categories.map(c => `<option value="${c.category_id}">${c.category_name}</option>`).join('')}
                        </select>
                    </div>
                    <!-- Brand -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Thương hiệu</label>
                        <select name="brand_id" id="brand_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Chọn thương hiệu --</option>
                            ${brands.map(b => `<option value="${b.brand_id}">${b.brand_name}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <!-- Hidden inputs for legacy fields, base price & stock managed via variants now -->
                <input type="hidden" name="base_price" id="base_price" value="0">
                <input type="hidden" name="stock_quantity" id="stock_quantity" value="0">

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                </div>

                <!-- Image Upload Section (show for both add and edit modes) -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-gray-500">image</span>
                        Hình Ảnh Sản Phẩm
                    </h3>

                    <!-- Image Upload Area -->
                    <div class="mb-4">
                        <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-axeron-red transition-colors cursor-pointer bg-gray-50">
                            <input type="file" id="imageInput" accept="image/*" multiple class="hidden">
                            <div class="flex flex-col items-center">
                                <span class="material-symbols-outlined text-4xl text-gray-400 mb-2">cloud_upload</span>
                                <p class="text-sm text-gray-600 mb-1">Kéo thả ảnh vào đây hoặc</p>
                                <button type="button" onclick="document.getElementById('imageInput').click()" class="text-axeron-red hover:text-red-700 font-medium text-sm">
                                    chọn từ máy tính
                                </button>
                                <p class="text-xs text-gray-400 mt-2">PNG, JPG, GIF, WebP (tối đa 10MB)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="hidden mb-4">
                        <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                            <div class="animate-spin">
                                <span class="material-symbols-outlined text-blue-500">progress_activity</span>
                            </div>
                            <span class="text-sm text-blue-700">Đang upload ảnh...</span>
                        </div>
                    </div>

                    <!-- Image Preview Grid -->
                    <div id="imagePreviewGrid" class="grid grid-cols-3 gap-3">
                        <!-- Images will be loaded here -->
                    </div>

                    <!-- Hidden container for files to upload during create -->
                    <div id="pendingImagesContainer" class="hidden"></div>
                    <datalist id="color-options"></datalist>
                </div>

                <!-- Product Variants Section -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center gap-2">
                            <span class="material-symbols-outlined text-gray-500">grid_on</span>
                            Quản Lý Biến Thể Sản Phẩm
                        </h3>
                        <button type="button" onclick="addVariantRow()" class="px-3 py-1 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded text-xs font-semibold flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-sm">add</span> Thêm biến thể
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto border border-gray-200 rounded-lg max-h-[300px]">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50 text-[11px] font-semibold text-gray-500 uppercase border-b border-gray-200 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">SKU *</th>
                                    <th class="px-3 py-2">Màu sắc</th>
                                    <th class="px-3 py-2">Kích thước</th>
                                    <th class="px-3 py-2">Giá bán *</th>
                                    <th class="px-3 py-2">Tồn kho *</th>
                                    <th class="px-3 py-2 text-center">Bật</th>
                                    <th class="px-3 py-2 text-center">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="variantsTableBody" class="divide-y divide-gray-100 text-xs">
                                <!-- Variant rows will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" class="w-4 h-4 rounded text-axeron-red">
                        <span class="text-sm">Sản phẩm nổi bật</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_visible" id="is_visible" value="1" checked class="w-4 h-4 rounded text-axeron-red">
                        <span class="text-sm">Hiển thị</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeModal()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="px-6 py-2 bg-axeron-red text-white rounded-lg hover:bg-red-700">Lưu</button>
                </div>
            </form>
        </div>
    `;

    openModal(modalContent);
    
    if (!isEdit) {
        populateColorDatalist();
        renderNoVariantsRow();
    }

    // If editing, load product data and images
    if (isEdit) {
        // Wait for modal to fully render, then fetch data
        setTimeout(function() {
            const apiUrl = '<?= BASE_URL ?>/admin/admin-api.php?action=get_product&id=' + productId;

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.product) {
                        const p = data.product;

                        // Helper function to safely set value
                        const setValue = (id, value) => {
                            const el = document.getElementById(id);
                            if (el) el.value = value !== null && value !== undefined ? value : '';
                        };

                        setValue('product_name', p.product_name);
                        setValue('category_id', p.category_id);
                        setValue('brand_id', p.brand_id);
                        setValue('base_price', p.base_price);
                        setValue('stock_quantity', p.stock_quantity);
                        setValue('description', p.description);

                        // Checkboxes
                        const featured = document.getElementById('is_featured');
                        if (featured) featured.checked = p.is_featured == 1;
                        const visible = document.getElementById('is_visible');
                        if (visible) visible.checked = p.is_visible == 1;

                        // Load images
                        currentProductColors = p.product_colors || [];
                        populateColorDatalist();
                        if (p.images && p.images.length > 0) {
                            currentProductImages = p.images;
                            renderProductImages();
                        }

                        // Load variants
                        const tbody = document.getElementById('variantsTableBody');
                        if (tbody) {
                            tbody.innerHTML = '';
                            if (p.variants && p.variants.length > 0) {
                                p.variants.forEach(v => addVariantRow(v));
                            } else {
                                renderNoVariantsRow();
                            }
                        }
                    } else {
                        showToast(data.message || 'Không thể tải dữ liệu sản phẩm', 'error');
                    }
                })
                .catch(err => {
                    console.error('Error loading product:', err);
                    showToast('Lỗi khi tải dữ liệu sản phẩm', 'error');
                });
        }, 200); // Longer delay to ensure modal is rendered
    }

    // Setup drag and drop for both add and edit modes
    setupDragDrop();

    // Handle form submit
    document.getElementById('productForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            // Collect variants data
            const variants = [];
            const rows = document.querySelectorAll('#variantsTableBody tr.variant-row');
            let hasEmptySku = false;
            
            rows.forEach(row => {
                const variantId = row.querySelector('.variant-id').value;
                const sku = row.querySelector('.variant-sku').value.trim();
                const color = row.querySelector('.variant-color').value.trim();
                const size = row.querySelector('.variant-size').value.trim();
                const extraPrice = parseFloat(row.querySelector('.variant-extra-price').value) || 0;
                const stockQuantity = parseInt(row.querySelector('.variant-stock').value) || 0;
                const isActive = row.querySelector('.variant-active').checked ? 1 : 0;
                
                if (!sku) {
                    hasEmptySku = true;
                    row.querySelector('.variant-sku').classList.add('border-red-500');
                } else {
                    row.querySelector('.variant-sku').classList.remove('border-red-500');
                    variants.push({
                        variant_id: variantId ? parseInt(variantId) : null,
                        sku: sku,
                        color: color,
                        size: size,
                        extra_price: extraPrice,
                        stock_quantity: stockQuantity,
                        is_active: isActive
                    });
                }
            });

            if (hasEmptySku) {
                showToast('Vui lòng nhập đầy đủ SKU cho các biến thể!', 'error');
                return;
            }

            if (variants.length === 0) {
                showToast('Vui lòng thêm ít nhất một biến thể cho sản phẩm!', 'error');
                return;
            }

            formData.append('variants_json', JSON.stringify(variants));

            // For create mode: include pending images and their colors
            if (!isEdit && pendingImages.length > 0) {
                for (let i = 0; i < pendingImages.length; i++) {
                    formData.append('pending_images[]', pendingImages[i].file);
                    formData.append('pending_images_colors[]', pendingImages[i].color || '');
                }
            }

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

// Setup drag and drop for image upload
function setupDragDrop() {
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');

    if (!dropZone || !imageInput) return;

    // Click to upload
    dropZone.addEventListener('click', function() {
        imageInput.click();
    });

    // Drag over
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-axeron-red', 'bg-blue-50');
    });

    // Drag leave
    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-axeron-red', 'bg-blue-50');
    });

    // Drop
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-axeron-red', 'bg-blue-50');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleImageFiles(files);
        }
    });

    // File input change
    imageInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleImageFiles(this.files);
        }
    });
}

// Handle image upload
async function handleImageFiles(files) {
    const validFiles = Array.from(files).filter(file => {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        return allowedTypes.includes(file.type) && file.size <= 10 * 1024 * 1024;
    });

    if (validFiles.length === 0) {
        showToast('Vui lòng chọn file ảnh hợp lệ (PNG, JPG, GIF, WebP, tối đa 10MB)!', 'error');
        return;
    }

    // Show progress
    const progressDiv = document.getElementById('uploadProgress');
    progressDiv.classList.remove('hidden');

    if (!currentProductId) {
        // Create mode: store files temporarily for preview and later upload
        for (const file of validFiles) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewData = {
                    file: file,
                    previewUrl: e.target.result,
                    name: file.name,
                    is_primary: pendingImages.length === 0
                };
                pendingImages.push(previewData);
                renderPendingImages();
            };
            reader.readAsDataURL(file);
        }
        progressDiv.classList.add('hidden');
        showToast(`Đã thêm ${validFiles.length} ảnh vào danh sách chờ upload!`);
    } else {
        // Edit mode: upload directly
        for (const file of validFiles) {
            await uploadSingleImage(file);
        }
        progressDiv.classList.add('hidden');
    }
}

// Render pending images (create mode)
function renderPendingImages() {
    const grid = document.getElementById('imagePreviewGrid');
    if (!grid) return;

    if (pendingImages.length === 0) {
        grid.innerHTML = `
            <div class="col-span-3 text-center py-8 text-gray-400">
                <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                <p class="text-sm mt-2">Chưa có ảnh nào</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = pendingImages.map((img, index) => `
        <div class="relative group rounded-lg overflow-hidden border ${img.is_primary ? 'border-axeron-red ring-2 ring-axeron-red' : 'border-gray-200'} bg-gray-50 flex flex-col">
            <div class="relative aspect-square">
                <img src="${img.previewUrl}" alt="${img.name}"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    ${img.is_primary ? `
                        <span class="px-2 py-1 bg-axeron-red text-white text-xs rounded">Ảnh chính</span>
                    ` : `
                        <button type="button" onclick="setPendingImagePrimary(${index})" class="p-2 bg-white rounded-full hover:bg-gray-100" title="Đặt làm ảnh chính">
                            <span class="material-symbols-outlined text-gray-700 text-sm">star</span>
                        </button>
                    `}
                    <button type="button" onclick="removePendingImage(${index})" class="p-2 bg-white rounded-full hover:bg-red-50" title="Xóa ảnh">
                        <span class="material-symbols-outlined text-red-500 text-sm">delete</span>
                    </button>
                </div>
            </div>
            <div class="p-2 border-t border-gray-100">
                <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-semibold mb-1">Màu sắc</label>
                <input type="text" 
                       list="color-options" 
                       value="${img.color || ''}" 
                       onchange="updatePendingImageColor(${index}, this.value)" 
                       class="w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" 
                       placeholder="Không chọn">
            </div>
        </div>
    `).join('');
}

// Set pending image as primary
function setPendingImagePrimary(index) {
    pendingImages = pendingImages.map((img, i) => ({
        ...img,
        is_primary: i === index
    }));
    renderPendingImages();
}

// Remove pending image
function removePendingImage(index) {
    pendingImages.splice(index, 1);
    // If removed was primary, set first one as primary
    if (pendingImages.length > 0 && !pendingImages.some(img => img.is_primary)) {
        pendingImages[0].is_primary = true;
    }
    renderPendingImages();
}

// Upload single image
async function uploadSingleImage(file) {
    const formData = new FormData();
    formData.append('ajax_action', 'upload_product_image');
    formData.append('product_id', currentProductId);
    formData.append('image', file);
    formData.append('alt_text', file.name);
    formData.append('is_primary', currentProductImages.length === 0 ? '1' : '0');

    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success && result.image) {
            currentProductImages.push(result.image);
            renderProductImages();
            showToast('Ảnh đã được upload thành công!');
        } else {
            showToast(result.message || 'Lỗi upload ảnh!', 'error');
        }
    } catch (err) {
        showToast('Lỗi upload ảnh!', 'error');
    }
}

// Render product images in modal
function renderProductImages() {
    const grid = document.getElementById('imagePreviewGrid');
    if (!grid) return;

    if (currentProductImages.length === 0) {
        grid.innerHTML = `
            <div class="col-span-3 text-center py-8 text-gray-400">
                <span class="material-symbols-outlined text-4xl">image_not_supported</span>
                <p class="text-sm mt-2">Chưa có ảnh nào</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = currentProductImages.map((img, index) => `
        <div class="relative group rounded-lg overflow-hidden border ${img.is_primary ? 'border-axeron-red ring-2 ring-axeron-red' : 'border-gray-200'} bg-gray-50 flex flex-col">
            <div class="relative aspect-square">
                <img src="${getImageUrl(img.image_url || img.secure_url)}" alt="${img.alt_text || ''}"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    ${img.is_primary ? `
                        <span class="px-2 py-1 bg-axeron-red text-white text-xs rounded">Ảnh chính</span>
                    ` : `
                        <button type="button" onclick="setPrimaryImage(${img.image_id})" class="p-2 bg-white rounded-full hover:bg-gray-100" title="Đặt làm ảnh chính">
                            <span class="material-symbols-outlined text-gray-700 text-sm">star</span>
                        </button>
                    `}
                    <button type="button" onclick="deleteImage(${img.image_id})" class="p-2 bg-white rounded-full hover:bg-red-50" title="Xóa ảnh">
                        <span class="material-symbols-outlined text-red-500 text-sm">delete</span>
                    </button>
                </div>
            </div>
            <div class="p-2 border-t border-gray-100">
                <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-semibold mb-1">Màu sắc</label>
                <input type="text" 
                       list="color-options" 
                       value="${img.color || ''}" 
                       onchange="updateImageColor(${img.image_id}, this.value)" 
                       class="w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" 
                       placeholder="Không chọn">
            </div>
        </div>
    `).join('');
}

// Set primary image
async function setPrimaryImage(imageId) {
    const formData = new FormData();
    formData.append('ajax_action', 'set_primary_image');
    formData.append('image_id', imageId);

    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            currentProductImages = currentProductImages.map(img => ({
                ...img,
                is_primary: img.image_id === imageId ? 1 : 0
            }));
            renderProductImages();
            showToast('Đã đặt làm ảnh chính!');
        } else {
            showToast(result.message || 'Lỗi!', 'error');
        }
    } catch (err) {
        showToast('Lỗi!', 'error');
    }
}

// Delete image
function deleteImage(imageId) {
    showConfirm('Bạn có chắc muốn xóa ảnh này?', async () => {
        const formData = new FormData();
        formData.append('ajax_action', 'delete_product_image');
        formData.append('image_id', imageId);

        try {
            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                currentProductImages = currentProductImages.filter(img => img.image_id !== imageId);
                renderProductImages();
                showToast('Ảnh đã được xóa!');
            } else {
                showToast(result.message || 'Lỗi!', 'error');
            }
        } catch (err) {
            showToast('Lỗi!', 'error');
        }
    });
}

// Delete product with AJAX (no page reload, shows toast notification)
function deleteProduct(productId) {
    showConfirm('Bạn có chắc muốn xóa sản phẩm này?', async () => {
        try {
            const formData = new FormData();
            formData.append('ajax_action', 'delete_product');
            formData.append('product_id', productId);

            const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showToast(result.message || 'Sản phẩm đã được xóa!', 'success');
                // Reload after short delay to show toast
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Có lỗi xảy ra!', 'error');
            }
        } catch (err) {
            showToast('Có lỗi xảy ra!', 'error');
        }
    });
}
// Toggle product visibility
async function toggleProductVisibility(productId, checkbox) {
    const isVisible = checkbox.checked ? 1 : 0;
    const label = checkbox.parentNode.querySelector('.visibility-label');
    
    // Update label text temporarily
    if (label) {
        label.textContent = isVisible ? 'Hiển thị' : 'Đang ẩn';
    }

    try {
        const formData = new FormData();
        formData.append('ajax_action', 'toggle_product_visibility');
        formData.append('product_id', productId);
        formData.append('is_visible', isVisible);

        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast('Đã cập nhật trạng thái hiển thị!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
            // Revert
            checkbox.checked = !isVisible;
            if (label) label.textContent = !isVisible ? 'Hiển thị' : 'Đang ẩn';
        }
    } catch (err) {
        showToast('Có lỗi xảy ra!', 'error');
        // Revert
        checkbox.checked = !isVisible;
        if (label) label.textContent = !isVisible ? 'Hiển thị' : 'Đang ẩn';
    }
}

// Toggle product featured
async function toggleProductFeatured(productId, checkbox) {
    const isFeatured = checkbox.checked ? 1 : 0;
    const label = checkbox.parentNode.querySelector('.featured-label');
    
    // Update label text temporarily
    if (label) {
        label.textContent = isFeatured ? 'Nổi bật' : '-';
    }

    try {
        const formData = new FormData();
        formData.append('ajax_action', 'toggle_product_featured');
        formData.append('product_id', productId);
        formData.append('is_featured', isFeatured);

        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast('Đã cập nhật trạng thái nổi bật!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
            // Revert
            checkbox.checked = !isFeatured;
            if (label) label.textContent = !isFeatured ? 'Nổi bật' : '-';
        }
    } catch (err) {
        showToast('Có lỗi xảy ra!', 'error');
        // Revert
        checkbox.checked = !isFeatured;
        if (label) label.textContent = !isFeatured ? 'Nổi bật' : '-';
    }
}

// Populate datalist with color suggestions
function populateColorDatalist() {
    const datalist = document.getElementById('color-options');
    if (!datalist) return;
    const colorsSet = new Set([...currentProductColors, ...allSystemColors]);
    datalist.innerHTML = Array.from(colorsSet).map(color => `<option value="${color}">`).join('');
}

// Update color for a pending image (Create mode)
function updatePendingImageColor(index, color) {
    if (pendingImages[index]) {
        pendingImages[index].color = color;
    }
}

// Update color for an uploaded image in database (Edit mode)
async function updateImageColor(imageId, color) {
    const formData = new FormData();
    formData.append('ajax_action', 'update_image_color');
    formData.append('image_id', imageId);
    formData.append('color', color);

    try {
        const response = await fetch('<?= BASE_URL ?>/admin/admin-api.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            currentProductImages = currentProductImages.map(img => {
                if (img.image_id === imageId) {
                    return { ...img, color: color };
                }
                return img;
            });
            showToast('Đã cập nhật màu sắc ảnh!', 'success');
        } else {
            showToast(result.message || 'Lỗi cập nhật màu sắc!', 'error');
        }
    } catch (err) {
        showToast('Lỗi cập nhật màu sắc!', 'error');
    }
}

// Render "No variants" row
function renderNoVariantsRow() {
    const tbody = document.getElementById('variantsTableBody');
    if (!tbody) return;
    tbody.innerHTML = `
        <tr class="no-variants-row">
            <td colspan="7" class="px-3 py-6 text-center text-gray-400">
                Sản phẩm chưa có biến thể nào. Nhấn nút "Thêm biến thể" để tạo mới.
            </td>
        </tr>
    `;
}

// Add new variant row to table
function addVariantRow(variant = null) {
    const tbody = document.getElementById('variantsTableBody');
    if (!tbody) return;
    
    // Remove "no variants" row if it exists
    const noRow = tbody.querySelector('.no-variants-row');
    if (noRow) noRow.remove();

    const id = variant ? variant.variant_id : '';
    const sku = variant ? variant.sku : '';
    const color = variant ? variant.color : '';
    const size = variant ? variant.size : '';
    const extraPrice = variant ? variant.extra_price : 0;
    const stock = variant ? variant.stock_quantity : 0;
    const isActive = variant ? (variant.is_active == 1) : true;

    const row = document.createElement('tr');
    row.className = 'variant-row hover:bg-gray-50 border-b border-gray-100';
    row.innerHTML = `
        <td class="px-2 py-1.5">
            <input type="hidden" class="variant-id" value="${id}">
            <input type="text" class="variant-sku w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" value="${sku}" required placeholder="SKU...">
        </td>
        <td class="px-2 py-1.5">
            <input type="text" list="color-options" class="variant-color w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" value="${color}" placeholder="Màu...">
        </td>
        <td class="px-2 py-1.5">
            <input type="text" class="variant-size w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" value="${size}" placeholder="Size...">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" class="variant-extra-price w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" value="${extraPrice}" min="0" step="1000">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" class="variant-stock w-full text-xs px-2 py-1 border border-gray-300 rounded focus:ring-1 focus:ring-axeron-red outline-none bg-white" value="${stock}" min="0" required>
        </td>
        <td class="px-2 py-1.5 text-center">
            <input type="checkbox" class="variant-active w-4 h-4 rounded text-axeron-red" ${isActive ? 'checked' : ''}>
        </td>
        <td class="px-2 py-1.5 text-center">
            <button type="button" onclick="removeVariantRow(this)" class="p-1 hover:bg-red-50 rounded text-red-500 transition-colors" title="Xóa">
                <span class="material-symbols-outlined text-sm">delete</span>
            </button>
        </td>
    `;
    tbody.appendChild(row);
}

// Remove variant row
function removeVariantRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    
    const tbody = document.getElementById('variantsTableBody');
    if (tbody && tbody.querySelectorAll('.variant-row').length === 0) {
        renderNoVariantsRow();
    }
}
</script>
