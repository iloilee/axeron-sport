<?php
/**
 * Product Catalog - Danh mục sản phẩm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();

// Get filter parameters
$categorySlug = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$brand = sanitize($_GET['brand'] ?? '');
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 0);
$sortBy = sanitize($_GET['sort'] ?? 'popular');
$page = max(1, (int)($_GET['page'] ?? 1));
$isFeatured = isset($_GET['featured']) && $_GET['featured'] == '1';
$perPage = 12;

$selectedCategories = isset($_GET['cat_id']) && is_array($_GET['cat_id']) ? $_GET['cat_id'] : [];
$selectedColors = isset($_GET['color']) && is_array($_GET['color']) ? $_GET['color'] : [];
$selectedSizes = isset($_GET['size']) && is_array($_GET['size']) ? $_GET['size'] : [];

// Current category info
$currentCategory = null;
if ($categorySlug) {
    $currentCategory = $db->selectOne("
        SELECT category_id, category_name, slug FROM categories WHERE slug = ? AND is_visible = 1
    ", [$categorySlug]);
    
    // Nếu truy cập từ URL (Menu/Banner) chưa chọn filter, tự động check danh mục hiện tại
    if ($currentCategory && empty($selectedCategories)) {
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
$where = ["p.is_visible = 1", "p.is_deleted = 0"];
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

if ($search) {
    $where[] = "(p.product_name LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <title><?= htmlspecialchars($currentCategory['category_name'] ?? 'Danh mục sản phẩm') ?> - Axeron</title>
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
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 flex flex-col md:flex-row gap-gutter">
        <!-- Mobile Filter Button -->
        <button type="button" class="md:hidden w-full flex items-center justify-center gap-2 bg-white border border-outline-variant text-on-surface font-label-lg py-3 rounded-lg mb-4 hover:bg-surface-dim transition-colors shadow-sm" onclick="document.getElementById('mobile-filter-container').classList.toggle('hidden')">
            <span class="material-symbols-outlined">filter_list</span>
            Bộ lọc & Tùy chọn
        </button>

        <!-- Sidebar Filter -->
        <aside id="mobile-filter-container" class="hidden md:block w-full md:w-64 flex-shrink-0 mb-8 md:mb-0">
            <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-sm sticky top-24 flex flex-col max-h-[calc(100vh-120px)] overflow-hidden">
                <form id="filter-form" method="GET" class="flex flex-col flex-grow overflow-hidden min-h-0">
                    <!-- Sticky Header Row -->
                    <div class="flex items-center justify-between p-5 border-b border-surface-container-high bg-surface-container-lowest z-10 flex-shrink-0">
                        <h2 class="font-headline-md text-[18px] font-bold text-on-surface m-0">Bộ lọc</h2>
                        <button type="submit" class="bg-axeron-red text-white px-3 py-1.5 rounded-full hover:bg-primary transition-colors shadow-sm flex items-center justify-center gap-1 mx-2 flex-shrink-0 font-label-sm text-[13px]" title="Áp dụng bộ lọc">
                            <span class="material-symbols-outlined text-[18px]">filter_alt</span>
                            Lọc
                        </button>
                        <?php
                            $clearParams = [];
                            if ($search) $clearParams['search'] = $search;
                            if ($isFeatured) $clearParams['featured'] = 1;
                            $clearUrl = BASE_URL . '/shop/product-catalog.php' . (!empty($clearParams) ? '?' . http_build_query($clearParams) : '');
                        ?>
                        <a href="<?= htmlspecialchars($clearUrl) ?>" class="font-label-sm text-[13px] text-axeron-red hover:underline whitespace-nowrap m-0">Xóa tất cả</a>
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
                                            <input type="checkbox" name="cat_id[]" value="<?= $childCat['category_id'] ?>" class="form-checkbox h-4 w-4 text-axeron-red rounded" <?= in_array($childCat['category_id'], $selectedCategories) ? 'checked' : '' ?>/>
                                            <span class="font-body-sm text-sm text-on-surface-variant group-hover:text-axeron-red transition-colors font-medium"><?= htmlspecialchars($childCat['category_name']) ?></span>
                                        </label>
                                        
                                        <!-- Level 3 Categories -->
                                        <?php if (!empty($childCat['children'])): ?>
                                        <div class="flex flex-col space-y-1 pl-7">
                                            <?php foreach ($childCat['children'] as $grandChild): ?>
                                            <label class="flex items-center space-x-3 cursor-pointer group">
                                                <input type="checkbox" name="cat_id[]" value="<?= $grandChild['category_id'] ?>" class="form-checkbox h-3.5 w-3.5 text-axeron-red rounded opacity-70" <?= in_array($grandChild['category_id'], $selectedCategories) ? 'checked' : '' ?>/>
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
        <div class="flex-grow flex flex-col">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 pb-4 border-b border-outline-variant gap-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface">
                        <?php if ($search): ?>
                            Kết quả tìm kiếm: "<?= htmlspecialchars($search) ?>"
                        <?php elseif (!empty($selectedCategories)): ?>
                            <?php if (count($selectedCategories) === 1): 
                                $sc = $db->selectOne("SELECT category_name FROM categories WHERE category_id = ?", [$selectedCategories[0]]);
                            ?>
                                <?= htmlspecialchars($sc['category_name'] ?? 'Kết quả lọc sản phẩm') ?>
                            <?php else: ?>
                                Kết quả lọc sản phẩm
                            <?php endif; ?>
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

                <!-- Sort -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center w-full sm:w-auto gap-2 sm:gap-4 mt-4 sm:mt-0">
                    <span class="font-body-md text-body-md text-on-surface-variant whitespace-nowrap">Sắp xếp:</span>
                    <select onchange="window.location.href=this.value" class="form-select font-body-md text-body-md border-outline-variant rounded-md bg-surface-container-lowest text-on-surface focus:ring-axeron-red focus:border-axeron-red px-3 py-2 w-full sm:w-auto">
                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'popular', 'page' => null])) ?>" <?= $sortBy == 'popular' ? 'selected' : '' ?>>Phổ biến nhất</option>
                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest', 'page' => null])) ?>" <?= $sortBy == 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_asc', 'page' => null])) ?>" <?= $sortBy == 'price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_desc', 'page' => null])) ?>" <?= $sortBy == 'price_desc' ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'rating', 'page' => null])) ?>" <?= $sortBy == 'rating' ? 'selected' : '' ?>>Đánh giá cao nhất</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-16 bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm mb-12">
                    <span class="material-symbols-outlined text-7xl text-on-surface-variant opacity-50 mb-6 block">search_off</span>
                    <h3 class="font-headline-md text-2xl text-on-surface mb-3">Không tìm thấy kết quả phù hợp</h3>
                    <p class="text-on-surface-variant text-base max-w-md mx-auto mb-6">
                        Rất tiếc, chúng tôi không thể tìm thấy sản phẩm nào khớp với 
                        <?= $search ? 'từ khóa "<strong>' . htmlspecialchars($search) . '</strong>"' : 'bộ lọc của bạn' ?>.
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
                        class="group bg-surface-container-lowest rounded-lg border border-outline-variant overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col relative">
                        <div class="relative w-full aspect-square overflow-hidden bg-surface-container-low flex items-center justify-center">
                            <?php if ($product['is_featured']): ?>
                            <span class="absolute top-2 left-2 bg-gradient-to-r from-orange-500 to-red-600 shadow-[0_0_10px_rgba(239,68,68,0.5)] text-white font-label-sm text-label-sm px-3 py-1 rounded-full uppercase tracking-wider z-10">Nổi bật</span>
                            <?php endif; ?>
                             <img alt="<?= htmlspecialchars($product['product_name']) ?>"
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
                            $favColor = $isFav ? "text-axeron-red" : "text-on-surface-variant";
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
                                <span class="text-[10px] bg-gradient-to-r from-red-600 to-orange-500 shadow-[0_0_8px_rgba(239,68,68,0.4)] text-white px-2 py-0.5 rounded-sm uppercase tracking-widest text-center max-w-[80px] leading-tight flex-shrink-0"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
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

    <script src="<?= BASE_URL ?>/js/main.js?v=<?= time() ?>"></script>
    <script>
        // Chuyển đổi price_range thành min_price và max_price trước khi submit form
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            var selectedPrice = document.querySelector('input[name="price_range"]:checked');
            if (selectedPrice && selectedPrice.value) {
                var parts = selectedPrice.value.split('-');
                var minPrice = parseInt(parts[0]) || 0;
                var maxPrice = parseInt(parts[1]) || 0;

                // Xóa các hidden inputs cũ nếu có
                var form = this;
                ['min_price', 'max_price'].forEach(function(name) {
                    var existing = form.querySelector('input[name="' + name + '"]');
                    if (existing) existing.remove();
                });

                // Thêm hidden inputs mới
                if (minPrice > 0) {
                    var minInput = document.createElement('input');
                    minInput.type = 'hidden';
                    minInput.name = 'min_price';
                    minInput.value = minPrice;
                    form.appendChild(minInput);
                }

                if (maxPrice > 0) {
                    var maxInput = document.createElement('input');
                    maxInput.type = 'hidden';
                    maxInput.name = 'max_price';
                    maxInput.value = maxPrice;
                    form.appendChild(maxInput);
                }
            }
        });
    </script>
</body>
</html>
