<?php
$startTime = microtime(true);
/**
 * Product Catalog - Danh mục sản phẩm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();

// Check if AI is disabled via Cookie (Persists across page loads/searches)
$disableAi = isset($_COOKIE['disable_ai']) && $_COOKIE['disable_ai'] == '1';

// Get filter parameters
$categorySlug = sanitize($_REQUEST['category'] ?? '');
$search = sanitize($_REQUEST['search'] ?? '');
$brand = sanitize($_REQUEST['brand'] ?? '');
$minPrice = (int)($_REQUEST['min_price'] ?? 0);
$maxPrice = (int)($_REQUEST['max_price'] ?? 0);
$sortBy = sanitize($_REQUEST['sort'] ?? 'popular');
$page = max(1, (int)($_GET['page'] ?? 1));
$isFeatured = isset($_GET['featured']) && $_GET['featured'] == '1';
$perPage = 12;

$selectedCategories = isset($_GET['cat_id']) && is_array($_GET['cat_id']) ? array_filter($_GET['cat_id'], fn($v) => $v !== '') : [];
$selectedColors = isset($_GET['color']) && is_array($_GET['color']) ? array_filter($_GET['color'], fn($v) => $v !== '') : [];
$selectedSizes = isset($_GET['size']) && is_array($_GET['size']) ? array_filter($_GET['size'], fn($v) => $v !== '') : [];

// Current category info
$currentCategory = null;
if ($categorySlug) {
    $currentCategory = $db->selectOne("
        SELECT category_id, category_name, slug FROM categories WHERE slug = ? AND is_visible = 1
    ", [$categorySlug]);
    
    // Tự động tick vào bộ lọc nếu user đi từ Menu (chưa submit filter form)
    if ($currentCategory && !isset($_GET['cat_id'])) {
        $selectedCategories[] = $currentCategory['category_id'];
    }
}

// Get categories for sidebar (cây danh mục 2 cấp)
$treeCategories = [];
$rootCats = $db->select("SELECT * FROM categories WHERE parent_id IS NULL AND is_visible = 1 ORDER BY sort_order");
foreach ($rootCats as $rc) {
    $children = $db->select("SELECT * FROM categories WHERE parent_id = ? AND is_visible = 1 ORDER BY sort_order", [$rc['category_id']]);
    $rc['children'] = $children;
    // Lấy thêm cấp 3 nếu có để đếm hoặc tiện mở rộng
    foreach ($rc['children'] as &$child) {
        $grandChildren = $db->select("SELECT * FROM categories WHERE parent_id = ? AND is_visible = 1 ORDER BY sort_order", [$child['category_id']]);
        $child['children'] = $grandChildren;
    }
    $treeCategories[] = $rc;
}

// Get brands, colors, sizes for filter
$brands = $db->select("SELECT * FROM brands WHERE is_active = 1 ORDER BY brand_name");
$colorsList = $db->select("SELECT DISTINCT color FROM product_variants WHERE is_active = 1 AND color IS NOT NULL AND color != '' ORDER BY color");
$sizesList = $db->select("SELECT DISTINCT size FROM product_variants WHERE is_active = 1 AND size IS NOT NULL AND size != '' ORDER BY size");

// Build query
$where = ["p.is_visible = 1", "p.is_deleted = 0", "p.category_id IN (" . getVisibleCategoryQuery() . ")"];
$params = [];

if (!empty($selectedCategories)) {
    $cleanCatIds = array_map('intval', $selectedCategories);
    $allSelectedIds = $cleanCatIds;
    foreach ($cleanCatIds as $cid) {
        $children = $db->select("SELECT category_id FROM categories WHERE parent_id = ?", [$cid]);
        foreach ($children as $c) {
            $allSelectedIds[] = $c['category_id'];
            $grandChildren = $db->select("SELECT category_id FROM categories WHERE parent_id = ?", [$c['category_id']]);
            foreach ($grandChildren as $gc) {
                $allSelectedIds[] = $gc['category_id'];
            }
        }
    }
    $allSelectedIds = array_unique($allSelectedIds);
    $placeholders = implode(',', array_fill(0, count($allSelectedIds), '?'));
    $where[] = "p.category_id IN ($placeholders)";
    $params = array_merge($params, $allSelectedIds);
} elseif ($currentCategory) {
    // Get all child categories
    $catIds = [$currentCategory['category_id']];
    $children = $db->select("SELECT category_id FROM categories WHERE parent_id = ?", [$currentCategory['category_id']]);
    foreach ($children as $c) {
        $catIds[] = $c['category_id'];
        $grandChildren = $db->select("SELECT category_id FROM categories WHERE parent_id = ?", [$c['category_id']]);
        foreach ($grandChildren as $gc) {
            $catIds[] = $gc['category_id'];
        }
    }

    $placeholders = implode(',', array_fill(0, count($catIds), '?'));
    $where[] = "p.category_id IN ($placeholders)";
    $params = array_merge($params, $catIds);
}

// Colors and Sizes filter
if (!empty($selectedColors) || !empty($selectedSizes)) {
    $vWhere = ["pv.product_id = p.product_id", "pv.is_active = 1"];
    if (!empty($selectedColors)) {
        $placeholders = implode(',', array_fill(0, count($selectedColors), '?'));
        $vWhere[] = "pv.color IN ($placeholders)";
        $params = array_merge($params, $selectedColors);
    }
    if (!empty($selectedSizes)) {
        $placeholders = implode(',', array_fill(0, count($selectedSizes), '?'));
        $vWhere[] = "pv.size IN ($placeholders)";
        $params = array_merge($params, $selectedSizes);
    }
    $vWhereClause = implode(' AND ', $vWhere);
    $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE $vWhereClause)";
}

$semanticProductIds = [];
$semanticScoreMap = [];
$aiServerStatus = true;

if (!$disableAi) {
    // Ping nhanh port 5000 để kiểm tra server có online không (Timeout siêu thấp: 0.1s)
    $fp = @fsockopen("127.0.0.1", 5000, $errno, $errstr, 0.1);
    if (!$fp) {
        $aiServerStatus = false;
    } else {
        fclose($fp);
    }
}

if (isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0) {
    if (!$disableAi && $aiServerStatus) {
        $cfile = new CURLFile($_FILES['search_image']['tmp_name'], $_FILES['search_image']['type'], $_FILES['search_image']['name']);
        $ch = curl_init('http://127.0.0.1:5000/search_image');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $responseData = json_decode($response, true);
            if (is_array($responseData)) {
                foreach ($responseData as $sp) {
                    if (isset($sp['product_id'])) {
                        $semanticProductIds[] = $sp['product_id'];
                        $semanticScoreMap[$sp['product_id']] = $sp['score'] ?? 0;
                    }
                }
            }
        }
    }
} elseif ($search) {
    if (!$disableAi && $aiServerStatus) {
        // 1. Gọi API sang Server Python lấy ID đã được xếp hạng
        $apiUrl = "http://127.0.0.1:5000/api/search?keyword=" . urlencode($search);
        
        // Sử dụng context để set timeout (1.5 giây để đảm bảo CPU xử lý kịp mô hình)
        $ctx = stream_context_create(['http' => ['timeout' => 1.5]]);
        $response = @file_get_contents($apiUrl, false, $ctx);
        
        if ($response === false) {
            $aiServerStatus = false;
        } else {
            $responseData = json_decode($response, true);
            if (isset($responseData['results']) && is_array($responseData['results'])) {
                foreach ($responseData['results'] as $sp) {
                    $semanticProductIds[] = $sp['id'];
                    $semanticScoreMap[$sp['id']] = $sp['score'];
                }
            }
        }
    }
}

if ($search || !empty($semanticProductIds)) {
    if (!empty($semanticProductIds)) {
        // AI Tìm thấy kết quả -> Lọc theo mảng ID
        $placeholders = implode(',', array_fill(0, count($semanticProductIds), '?'));
        $where[] = "p.product_id IN ($placeholders)";
        $params = array_merge($params, $semanticProductIds);
    } else {
        // Fallback: Tìm kiếm LIKE truyền thống nếu AI lỗi hoặc không có kết quả phù hợp
        $where[] = "(p.product_name LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
}

if ($brand) {
    $where[] = "b.brand_name = ?";
    $params[] = $brand;
}

if ($minPrice > 0) {
    $where[] = "p.base_price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = "p.base_price <= ?";
    $params[] = $maxPrice;
}

if ($isFeatured) {
    $where[] = "p.is_featured = 1";
}

$whereClause = implode(' AND ', $where);

// Count total
$totalResult = $db->selectOne("
    SELECT COUNT(DISTINCT p.product_id) as total
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE $whereClause
", $params);

$totalProducts = (int)$totalResult['total'];
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;

// Ghi log tìm kiếm (nếu đang ở page 1 và có tìm kiếm)
if ($search && $page === 1 && isLoggedIn()) {
    $db->insert("
        INSERT INTO search_logs (user_id, keyword, result_count)
        VALUES (?, ?, ?)
    ", [getUserId(), $search, $totalProducts]);
}

// Sort options
$orderBy = match($sortBy) {
    'price_asc' => 'p.base_price ASC',
    'price_desc' => 'p.base_price DESC',
    'newest' => 'p.created_at DESC',
    'rating' => 'p.avg_rating DESC',
    default => 'p.is_featured DESC, p.total_reviews DESC'
};

// Ghi đè sắp xếp nếu dùng AI Search (Sort by Relevance)
if (($search || !empty($semanticProductIds)) && !empty($semanticProductIds) && $sortBy == 'popular') {
    $orderBy = "FIELD(p.product_id, " . implode(',', $semanticProductIds) . ")";
}

// Get products
$products = $db->select("
    SELECT DISTINCT
        p.product_id,
        p.category_id,
        p.product_name,
        p.slug,
        p.base_price,
        p.avg_rating,
        p.total_reviews,
        p.is_featured,
        c.category_name,
        b.brand_name,
        pi.image_url
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE $whereClause
    ORDER BY $orderBy
    LIMIT ? OFFSET ?
", array_merge($params, [$perPage, $offset]));

// Get promotions for discount display
$promotions = $db->select("SELECT * FROM promotions WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()");

// Lấy danh sách sản phẩm yêu thích nếu đã đăng nhập
$wishlistIds = [];
if (isLoggedIn()) {
    $wl = $db->select("SELECT product_id FROM user_wishlists WHERE user_id = ?", [getUserId()]);
    $wishlistIds = array_column($wl, 'product_id');
}
?>
<?php
$pageTitle = 'Tất cả sản phẩm';
if (!empty($selectedCategories) && count($selectedCategories) === 1) {
    $sc = $db->selectOne("SELECT category_name FROM categories WHERE category_id = ?", [$selectedCategories[0]]);
    if ($sc) $pageTitle = $sc['category_name'];
} elseif ($brand) {
    $pageTitle = 'Thương hiệu: ' . $brand;
} elseif ($isFeatured) {
    $pageTitle = 'Sản Phẩm Nổi Bật';
} elseif ($currentCategory) {
    $pageTitle = $currentCategory['category_name'];
}
$bodyClass = 'bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col';
include __DIR__ . '/../includes/head.php'; 
?>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 flex flex-col md:flex-row gap-gutter">
        <!-- Mobile Filter Button -->
        <button type="button" class="md:hidden w-full flex items-center justify-center gap-2 bg-white border border-outline-variant text-on-surface font-label-lg py-3 rounded-lg mb-4 hover:bg-surface-dim transition-colors shadow-sm" onclick="document.getElementById('mobile-filter-container').classList.toggle('hidden')">
            <span class="material-symbols-outlined">filter_list</span>
            Bộ lọc & Tùy chọn
        </button>

        <!-- Sidebar Filter -->
        <aside id="mobile-filter-container" class="hidden md:block w-full md:w-64 flex-shrink-0 mb-8 md:mb-0">
            <div class="bg-surface-container-lowest dark:bg-gray-800 rounded-lg border border-outline-variant shadow-sm sticky top-24 flex flex-col max-h-[calc(100vh-120px)] overflow-hidden">
                <form id="filter-form" method="GET" class="flex flex-col flex-grow overflow-hidden min-h-0" autocomplete="off">
                    <!-- Sticky Header Row -->
                    <div class="flex items-center justify-between p-5 border-b border-surface-container-high bg-surface-container-lowest dark:bg-gray-800 z-10 flex-shrink-0">
                        <h2 class="font-headline-md text-[18px] font-bold text-on-surface m-0">Bộ lọc</h2>
                        <button type="submit" class="bg-axeron-red text-white px-3 py-1.5 rounded-full hover:bg-primary transition-colors shadow-sm flex items-center justify-center gap-1 mx-2 flex-shrink-0 font-label-sm text-[13px]" title="Áp dụng bộ lọc">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Lọc
                        </button>
                        <?php
                            $clearUrl = BASE_URL . '/shop/product-catalog.php';
                        ?>
                        <a href="<?= htmlspecialchars($clearUrl) ?>" onclick="document.querySelectorAll('#filter-form input[type=checkbox], #filter-form input[type=radio]').forEach(e => e.checked = false); window.location.href=this.href; return false;" class="font-label-sm text-[13px] text-axeron-red hover:underline whitespace-nowrap m-0">Xóa tất cả</a>
                    </div>

                    <!-- Scrollable Content -->
                    <div class="p-6 pt-5 overflow-y-auto custom-scrollbar flex-grow">
                        <?php if ($categorySlug): ?>
                            <input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>">
                        <?php endif; ?>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <?php if ($isFeatured): ?>
                        <input type="hidden" name="featured" value="1">
                    <?php endif; ?>

                    <!-- Category Filter -->
                    <?php if (!empty($treeCategories)): ?>
                    <div class="mb-6 border-b border-outline-variant pb-6">
                        <h3 class="font-label-lg text-label-lg text-on-surface mb-4">Danh mục sản phẩm</h3>
                        <div class="flex flex-col space-y-4">
                            <?php foreach ($treeCategories as $rootCat): ?>
                            <div>
                                <h4 class="font-bold text-on-surface mb-2 text-sm uppercase tracking-wide"><?= htmlspecialchars($rootCat['category_name']) ?></h4>
                                <?php if (!empty($rootCat['children'])): ?>
                                <div class="flex flex-col space-y-2 pl-2 border-l-2 border-surface-container-high ml-1">
                                    <?php foreach ($rootCat['children'] as $childCat): ?>
                                    <div class="flex flex-col space-y-1">
                                        <label class="flex items-center space-x-3 cursor-pointer group">
                                            <input type="checkbox" name="cat_id[]" value="<?= $childCat['category_id'] ?>" class="parent-checkbox form-checkbox h-4 w-4 text-axeron-red rounded" <?= in_array($childCat['category_id'], $selectedCategories) ? 'checked' : '' ?>/>
                                            <span class="font-body-sm text-sm text-on-surface-variant group-hover:text-axeron-red transition-colors font-medium"><?= htmlspecialchars($childCat['category_name']) ?></span>
                                        </label>
                                        
                                        <!-- Level 3 Categories -->
                                        <?php if (!empty($childCat['children'])): ?>
                                        <div class="flex flex-col space-y-1 pl-7">
                                            <?php foreach ($childCat['children'] as $grandChild): ?>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="checkbox" name="cat_id[]" value="<?= $grandChild['category_id'] ?>" class="child-checkbox form-checkbox h-3.5 w-3.5 text-axeron-red rounded opacity-70" <?= in_array($grandChild['category_id'], $selectedCategories) ? 'checked' : '' ?>/>
                                                <span class="font-body-sm text-[13px] text-on-surface-variant group-hover:text-axeron-red transition-colors"><?= htmlspecialchars($grandChild['category_name']) ?></span>
                                            </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Price Filter -->
                    <div class="mb-6 border-b border-outline-variant pb-6">
                        <h3 class="font-label-lg text-label-lg text-on-surface mb-4">Khoảng giá</h3>
                        <div class="flex flex-col space-y-3">
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="" class="form-radio h-4 w-4 text-axeron-red" <?= !$minPrice && !$maxPrice ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">Tất cả</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="0-500000" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 0 && $maxPrice == 500000 ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">Dưới 500.000đ</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="500000-1000000" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 500000 && $maxPrice == 1000000 ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">500.000đ - 1.000.000đ</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="1000000-2000000" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 1000000 && $maxPrice == 2000000 ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">1.000.000đ - 2.000.000đ</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="2000000-5000000" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 2000000 && $maxPrice == 5000000 ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">2.000.000đ - 5.000.000đ</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="5000000-10000000" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 5000000 && $maxPrice == 10000000 ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">5.000.000đ - 10.000.000đ</span>
                            </label>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="price_range" value="10000000-0" class="form-radio h-4 w-4 text-axeron-red" <?= $minPrice == 10000000 && !$maxPrice ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors">Trên 10.000.000đ</span>
                            </label>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="mb-6 border-b border-outline-variant pb-6">
                        <h3 class="font-label-lg text-label-lg text-on-surface mb-4">Nhãn hiệu</h3>
                        <div class="flex flex-col space-y-3">
                            <?php foreach ($brands as $b): ?>
                            <label class="flex items-center space-x-3 cursor-pointer group">
                                <input type="radio" name="brand" value="<?= htmlspecialchars($b['brand_name']) ?>" class="form-radio h-4 w-4 text-axeron-red" <?= $brand == $b['brand_name'] ? 'checked' : '' ?>/>
                                <span class="font-body-md text-body-md text-on-surface-variant group-hover:text-axeron-red transition-colors"><?= htmlspecialchars($b['brand_name']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <?php if (!empty($sizesList)): ?>
                    <div class="mb-6 border-b border-outline-variant pb-6">
                        <h3 class="font-label-lg text-label-lg text-on-surface mb-4">Kích cỡ (Size)</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($sizesList as $s): ?>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="size[]" value="<?= htmlspecialchars($s['size']) ?>" class="peer sr-only" <?= in_array($s['size'], $selectedSizes) ? 'checked' : '' ?>/>
                                <div class="min-w-[2.5rem] h-10 px-2 flex items-center justify-center rounded-md border border-outline-variant text-on-surface-variant peer-checked:bg-axeron-red peer-checked:text-white peer-checked:border-axeron-red hover:border-axeron-red transition-colors font-label-md whitespace-nowrap">
                                    <?= htmlspecialchars($s['size']) ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Color Filter -->
                    <?php if (!empty($colorsList)): ?>
                    <div class="mb-6 border-b border-outline-variant pb-6">
                        <h3 class="font-label-lg text-label-lg text-on-surface mb-4">Màu sắc</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($colorsList as $c): ?>
                            <label class="cursor-pointer">
                                <input type="checkbox" name="color[]" value="<?= htmlspecialchars($c['color']) ?>" class="peer sr-only" <?= in_array($c['color'], $selectedColors) ? 'checked' : '' ?>/>
                                <div class="px-3 py-1.5 flex items-center justify-center rounded-md border border-outline-variant text-on-surface-variant peer-checked:bg-axeron-red peer-checked:text-white peer-checked:border-axeron-red hover:border-axeron-red transition-colors font-body-sm text-sm">
                                    <?= htmlspecialchars($c['color']) ?>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    </div> <!-- End scrollable content -->
                </form>
            </div>
        </aside>

        <!-- Product Grid Area -->
        <div id="product-list-container" class="flex-grow flex flex-col">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b border-outline-variant gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface">
                        <?php if (isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0): ?>
                            Tìm kiếm bằng hình ảnh
                            <?php $searchTimeMs = round((microtime(true) - $startTime) * 1000); ?>
                            <?php if (isset($semanticProductIds) && !empty($semanticProductIds)): ?>
                                <span class="ml-2 inline-flex items-center gap-1 bg-gradient-to-r from-green-500 to-teal-500 text-white text-[12px] px-2.5 py-0.5 rounded-full align-middle whitespace-nowrap shadow-sm shadow-green-200">
                                    <span class="material-symbols-outlined text-[16px]">photo_camera</span>
                                    AI Visual Search (<?= $searchTimeMs ?> ms)
                                </span>
                            <?php endif; ?>
                        <?php elseif ($search): ?>
                            Kết quả tìm kiếm: "<?= htmlspecialchars($search) ?>"
                            <?php $searchTimeMs = round((microtime(true) - $startTime) * 1000); ?>
                            <?php if (isset($semanticProductIds) && !empty($semanticProductIds)): ?>
                                <span class="ml-2 inline-flex items-center gap-1 bg-gradient-to-r from-purple-600 to-blue-500 text-white text-[12px] px-2.5 py-0.5 rounded-full align-middle whitespace-nowrap shadow-sm shadow-purple-200">
                                    <span class="material-symbols-outlined text-[16px]">smart_toy</span>
                                    AI Tìm kiếm ngữ nghĩa (<?= $searchTimeMs ?> ms)
                                </span>
                            <?php else: ?>
                                <span class="ml-2 inline-flex items-center gap-1 bg-surface-variant text-on-surface-variant border border-outline-variant text-[12px] px-2.5 py-0.5 rounded-full align-middle whitespace-nowrap">
                                    <span class="material-symbols-outlined text-[16px]">search</span>
                                    Tìm kiếm từ khóa thường (<?= $searchTimeMs ?> ms)
                                </span>
                            <?php endif; ?>
                        <?php elseif (!empty($selectedCategories)): ?>
                            <?php if (count($selectedCategories) === 1): 
                                $sc = $db->selectOne("SELECT category_name FROM categories WHERE category_id = ?", [$selectedCategories[0]]);
                            ?>
                                <?= htmlspecialchars($sc['category_name'] ?? 'Kết quả lọc sản phẩm') ?>
                            <?php else: ?>
                                Kết quả lọc sản phẩm
                            <?php endif; ?>
                        <?php elseif ($brand): ?>
                            Thương hiệu: <?= htmlspecialchars($brand) ?>
                        <?php elseif ($isFeatured): ?>
                            Sản Phẩm Nổi Bật
                        <?php elseif ($currentCategory): ?>
                            <?= htmlspecialchars($currentCategory['category_name']) ?>
                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                    </h1>
                    <p class="text-on-surface-variant text-sm mt-1">Hiển thị <?= count($products) ?> trong <?= $totalProducts ?> sản phẩm</p>
                </div>

                <!-- Sort & AI Toggle -->
                <div class="flex flex-col items-end gap-3 mt-4 sm:mt-0 w-full sm:w-auto">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 w-full sm:w-auto">
                        <span class="font-body-md text-body-md text-on-surface-variant whitespace-nowrap">Sắp xếp:</span>
                        <select onchange="window.location.href=this.value" class="form-select font-body-md text-body-md border-outline-variant rounded-md bg-surface-container-lowest dark:bg-gray-800 text-on-surface focus:ring-axeron-red focus:border-axeron-red px-3 py-2 w-full sm:w-auto">
                            <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'popular', 'page' => null])) ?>" <?= $sortBy == 'popular' ? 'selected' : '' ?>>Phổ biến nhất</option>
                            <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest', 'page' => null])) ?>" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc', 'page' => null])) ?>" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                            <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc', 'page' => null])) ?>" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                            <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'rating', 'page' => null])) ?>" <?= $sortBy == 'rating' ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                        </select>
                    </div>

                    <!-- Nút Debug bật/tắt AI Semantic Search -->
                    <?php $showAiAsOn = !$disableAi && $aiServerStatus; ?>
                    <div class="flex items-center gap-2 bg-surface-container-lowest dark:bg-gray-800 border <?= $showAiAsOn ? 'border-purple-300' : 'border-outline-variant' ?> px-3 py-2.5 rounded-lg shadow-sm h-full" <?= !$aiServerStatus ? 'title="Server AI hiện đang không hoạt động"' : '' ?>>
                        <span class="font-bold text-[14px] text-on-surface flex items-center gap-1">
                            <span class="material-symbols-outlined text-[18px]" style="color: <?= $showAiAsOn ? '#8b5cf6' : '#9ca3af' ?>;">smart_toy</span>
                            AI Search
                        </span>
                        <label class="relative inline-block w-8 h-4 cursor-pointer ml-1 mb-0">
                            <input type="checkbox" <?= $showAiAsOn ? 'checked' : '' ?> onchange="toggleAiSearch(this.checked)" class="opacity-0 w-0 h-0 absolute">
                            <span class="absolute top-0 left-0 right-0 bottom-0 transition duration-300 rounded-full" style="background-color: <?= $showAiAsOn ? '#8b5cf6' : '#d1d5db' ?>;">
                                <span class="absolute h-3 w-3 bottom-[2px] bg-white transition duration-300 rounded-full shadow-sm" style="left: <?= $showAiAsOn ? '18px' : '2px' ?>;"></span>
                            </span>
                        </label>
                        <span class="text-[11px] font-bold w-6 text-center" style="color: <?= $showAiAsOn ? '#8b5cf6' : '#6b7280' ?>;"><?= $showAiAsOn ? 'ON' : 'OFF' ?></span>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-16 bg-surface-container-lowest dark:bg-gray-800 rounded-xl border border-outline-variant shadow-sm mb-12">
                    <span class="material-symbols-outlined text-7xl text-on-surface-variant opacity-50 mb-6 block">search_off</span>
                    <h3 class="font-headline-md text-2xl text-on-surface mb-3">Không tìm thấy kết quả phù hợp</h3>
                    <p class="text-on-surface-variant text-base max-w-md mx-auto mb-6">
                        Rất tiếc, chúng tôi không thể tìm thấy sản phẩm nào khớp với 
                        <?= $search ? 'từ khóa "<strong>' . htmlspecialchars($search) . '</strong>"' : (isset($_FILES['search_image']) ? 'ảnh bạn vừa tải lên' : 'bộ lọc của bạn') ?>.
                    </p>
                    <div class="bg-surface-container rounded-lg p-6 max-w-md mx-auto text-left">
                        <h4 class="font-bold text-on-surface mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-axeron-red">lightbulb</span>
                            Gợi ý cho bạn:
                        </h4>
                        <ul class="list-disc pl-5 text-on-surface-variant space-y-2 text-sm">
                            <li>Kiểm tra lại lỗi chính tả của từ khóa.</li>
                            <li>Sử dụng các từ khóa ngắn gọn hoặc chung chung hơn.</li>
                            <li>Bỏ bớt các bộ lọc (giá, danh mục, thương hiệu) để xem nhiều kết quả hơn.</li>
                        </ul>
                    </div>
                    <div class="mt-8">
                        <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-flex items-center gap-2 px-6 py-3 bg-axeron-red text-white font-bold rounded-lg hover:bg-primary transition-colors">
                            <span class="material-symbols-outlined">restart_alt</span>
                            Xóa bộ lọc & Tìm lại
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter mb-12">
                    <?php foreach ($products as $product): ?>
                    <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($product['slug']) ?>"
                        data-aos="fade-up"
                        class="group bg-surface-container-lowest dark:bg-gray-800 rounded-lg border border-outline-variant overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col relative">

                        <div class="relative w-full aspect-square overflow-hidden bg-surface-container-low flex items-center justify-center">
                            <?php if ($product['is_featured']): ?>
                            <span class="absolute top-2 left-2 bg-gradient-to-r from-orange-500 to-red-600 shadow-[0_0_10px_rgba(239,68,68,0.5)] text-white font-label-sm text-label-sm px-3 py-1 rounded-full uppercase tracking-wider z-10">Nổi bật</span>
                            <?php endif; ?>
                             <img loading="lazy" alt="<?= htmlspecialchars($product['product_name']) ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 15)))) ?>"/>
                                
                            <!-- Quick Add Overlay -->
                            <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out z-20 flex justify-center">
                                <button class="bg-on-surface/90 backdrop-blur-sm hover:bg-axeron-red text-white font-label-md text-label-md px-6 py-2.5 rounded-full shadow-lg transition-all flex items-center gap-2 hover:scale-105 w-[90%] justify-center" onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $product['product_id'] ?>, 0, 1)">
                                    <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                                    Mua Ngay
                                </button>
                            </div>
                            <?php
                            $isFav = in_array($product['product_id'], $wishlistIds);
                            $favFill = $isFav ? "'FILL' 1" : "'FILL' 0";
                            $favColor = $isFav ? "text-axeron-red" : "text-gray-400 hover:text-axeron-red";
                            $favOpacity = $isFav ? "opacity-100" : "opacity-0 group-hover:opacity-100";
                            ?>
                            <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors <?= $favOpacity ?> z-10"
                                onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $product['product_id'] ?>, this)">
                                <span class="material-symbols-outlined <?= $favColor ?>" style="font-variation-settings: <?= $favFill ?>;">favorite</span>
                            </button>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider"><?= htmlspecialchars($product['brand_name'] ?? '') ?></div>
                            <h3 class="font-headline-md text-headline-md text-on-surface text-lg leading-tight mb-2 line-clamp-2 group-hover:text-axeron-red transition-colors">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </h3>
                            <?php $promoInfo = getBestPromotionForProduct($product['product_id'], $product['category_id'] ?? 0, $product['base_price']); ?>
                            
                            <!-- Hiển thị điểm số AI Match Score (Chỉ hiện khi search bằng AI) -->
                            <?php if (($search || (isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0)) && isset($semanticScoreMap) && isset($semanticScoreMap[$product['product_id']])): ?>
                                <?php 
                                    $rawScore = $semanticScoreMap[$product['product_id']];
                                    $matchPercent = $rawScore > 1 ? round($rawScore, 1) : round($rawScore * 100, 1);
                                    $isImageSearch = isset($_FILES['search_image']) && $_FILES['search_image']['error'] == 0;
                                ?>
                                <div class="mb-2 w-full mt-1" title="Độ tương đồng: <?= $matchPercent ?>%">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[11px] <?= $isImageSearch ? 'text-green-600' : 'text-purple-600' ?> font-bold flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]"><?= $isImageSearch ? 'photo_camera' : 'auto_awesome' ?></span> 
                                            Khớp: <?= $matchPercent ?>%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="<?= $isImageSearch ? 'bg-gradient-to-r from-green-500 to-teal-500' : 'bg-gradient-to-r from-purple-500 to-blue-500' ?> h-1.5 rounded-full" style="width: <?= $matchPercent ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between gap-2 mb-2 min-h-[24px]">
                                <?php if ($product['avg_rating']): ?>
                                <div class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span class="text-sm text-on-surface-variant"><?= number_format($product['avg_rating'], 1) ?> (<?= $product['total_reviews'] ?>)</span>
                                </div>
                                <?php else: ?>
                                <div></div>
                                <?php endif; ?>

                                <?php if ($promoInfo['discount_amount'] > 0): ?>
                                <span class="text-[10px] md:text-[12px] font-bold bg-gradient-to-r from-red-600 to-orange-500 shadow-[0_0_8px_rgba(239,68,68,0.4)] text-white px-2 py-1 rounded-sm uppercase tracking-wider whitespace-nowrap shrink-0 ml-auto"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-auto pt-2 flex items-center justify-between">
                                <?php if ($promoInfo['discount_amount'] > 0): ?>
                                    <div class="flex flex-col">
                                        <span class="font-headline-md text-headline-md text-axeron-red text-xl"><?= formatPrice($promoInfo['discounted_price']) ?></span>
                                        <span class="text-on-surface-variant line-through text-xs font-medium"><?= formatPrice($product['base_price']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="font-headline-md text-headline-md text-axeron-red text-xl"><?= formatPrice($product['base_price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center items-center space-x-2 mt-auto pb-8 border-t border-outline-variant pt-8">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="p-2 border border-outline-variant rounded-md text-on-surface hover:border-axeron-red hover:text-axeron-red transition-colors">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                        class="w-10 h-10 flex items-center justify-center rounded-md <?= $i == $page ? 'bg-axeron-red text-white' : 'border border-outline-variant text-on-surface hover:border-axeron-red hover:text-axeron-red' ?> transition-colors font-label-lg text-label-lg">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="p-2 border border-outline-variant rounded-md text-on-surface hover:border-axeron-red hover:text-axeron-red transition-colors">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
    function toggleAiSearch(isAiOn) {
        if (!isAiOn) {
            document.cookie = "disable_ai=1; path=/; max-age=86400"; // Tồn tại 1 ngày
        } else {
            document.cookie = "disable_ai=0; path=/; max-age=86400";
        }
        // Reload lại trang hiện tại ngay lập tức để áp dụng
        window.location.reload();
    }
    </script>

    <script src="<?= BASE_URL ?>/js/main.min.js?v=<?= filemtime(__DIR__ . '/../js/main.min.js') ?>"></script>
    <script>
        function getSkeletonHtml() {
            let html = '<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter mb-12">';
            for(let i=0; i<8; i++) {
                html += `
                <div class="bg-surface-container-lowest dark:bg-gray-800 rounded-lg border border-outline-variant overflow-hidden shadow-sm flex flex-col relative animate-pulse">
                    <div class="w-full aspect-square bg-gray-200"></div>
                    <div class="p-4 flex flex-col flex-grow space-y-3">
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-5 bg-gray-200 rounded w-full"></div>
                        <div class="h-5 bg-gray-200 rounded w-2/3"></div>
                        <div class="mt-auto pt-2">
                            <div class="h-6 bg-gray-200 rounded w-1/2"></div>
                        </div>
                    </div>
                </div>`;
            }
            html += '</div>';
            return html;
        }

        async function fetchProducts(url) {
            const container = document.getElementById('product-list-container');
            // Show Skeleton Loader
            container.innerHTML = getSkeletonHtml();
            
            try {
                const response = await fetch(url);
                const htmlText = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlText, "text/html");
                const newContainer = doc.getElementById('product-list-container');
                if (newContainer) {
                    container.innerHTML = newContainer.innerHTML;
                    // Re-init AOS
                    if (typeof AOS !== 'undefined') AOS.init({once:true, offset:50, duration:800});
                }
                
                // Cập nhật sidebar bộ lọc từ response AJAX
                const sidebar = document.getElementById('mobile-filter-container');
                const newSidebar = doc.getElementById('mobile-filter-container');
                if (sidebar && newSidebar) {
                    sidebar.innerHTML = newSidebar.innerHTML;
                    // Re-attach các sự kiện cho sidebar mới
                    attachFilterEvents();
                    attachSortEvents();
                }
                
                // Cập nhật URL trình duyệt
                window.history.pushState({path: url}, '', url);
                
                // Lắng nghe lại các sự kiện phân trang
                attachPaginationEvents();
            } catch(e) {
                console.error("Lỗi khi tải dữ liệu", e);
                container.innerHTML = '<div class="text-center py-16">Lỗi tải dữ liệu. Vui lòng thử lại.</div>';
            }
        }

        function attachPaginationEvents() {
            const paginationLinks = document.querySelectorAll('#product-list-container a[href*="page="]');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchProducts(this.href);
                    window.scrollTo({top: 0, behavior: 'smooth'});
                });
            });
        }

        function attachFilterEvents() {
            // Form submit handler
            const filterForm = document.getElementById('filter-form');
            if (!filterForm) return;
            
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var selectedPrice = document.querySelector('input[name="price_range"]:checked');
                var form = this;
                
                ['min_price', 'max_price'].forEach(function(name) {
                    var existing = form.querySelector('input[name="' + name + '"]');
                    if (existing) existing.remove();
                });

                if (selectedPrice && selectedPrice.value) {
                    var parts = selectedPrice.value.split('-');
                    var minPrice = parseInt(parts[0]) || 0;
                    var maxPrice = parseInt(parts[1]) || 0;

                    if (minPrice > 0) {
                        var minInput = document.createElement('input');
                        minInput.type = 'hidden'; minInput.name = 'min_price'; minInput.value = minPrice;
                        form.appendChild(minInput);
                    }
                    if (maxPrice > 0) {
                        var maxInput = document.createElement('input');
                        maxInput.type = 'hidden'; maxInput.name = 'max_price'; maxInput.value = maxPrice;
                        form.appendChild(maxInput);
                    }
                }
                
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const url = window.location.pathname + '?' + params.toString();
                fetchProducts(url);
                
                // Xóa min_price / max_price khỏi form DOM để tránh rác
                ['min_price', 'max_price'].forEach(function(name) {
                    var existing = form.querySelector('input[name="' + name + '"]');
                    if (existing) existing.remove();
                });
                
                // Ẩn bộ lọc chỉ trên mobile (< 768px)
                if (window.innerWidth < 768) {
                    document.getElementById('mobile-filter-container').classList.add('hidden');
                }
            });

            // Tự động submit khi thay đổi checkbox/radio
            document.querySelectorAll('#filter-form input[type="checkbox"], #filter-form input[type="radio"]').forEach(el => {
                el.addEventListener('change', function() {
                    // Cascade check logic
                    if (this.classList.contains('parent-checkbox')) {
                        const container = this.closest('.flex-col').querySelector('.pl-7');
                        if (container) {
                            container.querySelectorAll('.child-checkbox').forEach(child => {
                                child.checked = this.checked;
                            });
                        }
                    } else if (this.classList.contains('child-checkbox')) {
                        const parentContainer = this.closest('.flex-col.space-y-1').parentElement.closest('.flex-col.space-y-1');
                        if (parentContainer) {
                            const parentCheckbox = parentContainer.querySelector('.parent-checkbox');
                            if (parentCheckbox && !this.checked) {
                                parentCheckbox.checked = false;
                            }
                        }
                    }
                    
                    document.getElementById('filter-form').dispatchEvent(new Event('submit'));
                });
            });
        }

        function attachSortEvents() {
            // Xử lý select sort
            const sortSelect = document.querySelector('#product-list-container select[onchange]');
            if (sortSelect) {
                sortSelect.removeAttribute('onchange');
                sortSelect.addEventListener('change', function(e) {
                    const sortVal = new URL(this.value, window.location.origin).searchParams.get('sort');
                    const form = document.getElementById('filter-form');
                    
                    let sortInput = form.querySelector('input[name="sort"]');
                    if (!sortInput) {
                        sortInput = document.createElement('input');
                        sortInput.type = 'hidden';
                        sortInput.name = 'sort';
                        form.appendChild(sortInput);
                    }
                    sortInput.value = sortVal;
                    
                    form.dispatchEvent(new Event('submit'));
                });
            }
        }

        // Handle back/forward navigation
        window.addEventListener('popstate', function(e) {
            if(e.state !== null) {
                fetchProducts(location.href);
            }
        });

        // Khởi tạo lần đầu
        attachFilterEvents();
        attachSortEvents();
        attachPaginationEvents();
    </script>
</body>
</html>
