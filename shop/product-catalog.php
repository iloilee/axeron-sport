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
$perPage = 12;

// Current category info
$currentCategory = null;
if ($categorySlug) {
    $currentCategory = $db->selectOne("
        SELECT category_id, category_name, slug FROM categories WHERE slug = ? AND is_visible = 1
    ", [$categorySlug]);
}

// Get all categories for sidebar
$categories = $db->select("
    SELECT * FROM categories WHERE parent_id IS NULL AND is_visible = 1 ORDER BY sort_order
");

// Get brands for filter
$brands = $db->select("SELECT * FROM brands WHERE is_active = 1 ORDER BY brand_name");

// Build query
$where = ["p.is_visible = 1"];
$params = [];

if ($currentCategory) {
    // Get all child categories
    $catIds = [$currentCategory['category_id']];
    $children = $db->select("SELECT category_id FROM categories WHERE parent_id = ?", [$currentCategory['category_id']]);
    foreach ($children as $c) $catIds[] = $c['category_id'];

    $placeholders = implode(',', array_fill(0, count($catIds), '?'));
    $where[] = "p.category_id IN ($placeholders)";
    $params = array_merge($params, $catIds);
}

if ($search) {
    $where[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $searchTerm = '%' . $search . '%';
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

$whereClause = implode(' AND ', $where);

// Count total
$totalResult = $db->selectOne("
    SELECT COUNT(DISTINCT p.product_id) as total
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE $whereClause
", $params);

$totalProducts = (int)$totalResult['total'];
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;

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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= $currentCategory ? $currentCategory['category_name'] . ' - ' : '' ?>Danh mục sản phẩm - Axeron</title>
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
        <!-- Sidebar Filter -->
        <aside class="w-full md:w-64 flex-shrink-0 mb-8 md:mb-0">
            <div class="bg-surface-container-lowest p-6 rounded-lg border border-outline-variant shadow-sm sticky top-24 custom-scrollbar max-h-[calc(100vh-120px)] overflow-y-auto">
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-outline-variant">
                    <h2 class="font-headline-md text-headline-md text-on-surface">Bộ lọc</h2>
                    <a href="<?= BASE_URL ?>/shop/product-catalog.php<?= $categorySlug ? '?category=' . $categorySlug : '' ?>" class="font-label-sm text-label-sm text-axeron-red hover:underline">Xóa tất cả</a>
                </div>

                <form id="filter-form" method="GET">
                    <?php if ($categorySlug): ?>
                        <input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>">
                    <?php endif; ?>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
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

                    <button type="submit" class="w-full bg-axeron-red text-white font-label-lg py-2 rounded-lg hover:bg-primary transition-colors">
                        Áp dụng
                    </button>
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
                        <?php elseif ($currentCategory): ?>
                            <?= htmlspecialchars($currentCategory['category_name']) ?>
                        <?php else: ?>
                            Tất cả sản phẩm
                        <?php endif; ?>
                    </h1>
                    <p class="text-on-surface-variant text-sm mt-1">Hiển thị <?= count($products) ?> trong <?= $totalProducts ?> sản phẩm</p>
                </div>

                <!-- Sort -->
                <div class="flex items-center space-x-4">
                    <span class="font-body-md text-body-md text-on-surface-variant">Sắp xếp:</span>
                    <select onchange="window.location.href=this.value" class="form-select font-body-md text-body-md border-outline-variant rounded-md bg-surface-container-lowest text-on-surface focus:ring-axeron-red focus:border-axeron-red px-3 py-2">
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
                <div class="text-center py-16">
                    <span class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">search_off</span>
                    <h3 class="font-headline-md text-xl text-on-surface mb-2">Không tìm thấy sản phẩm</h3>
                    <p class="text-on-surface-variant">Hãy thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter mb-12">
                    <?php foreach ($products as $product): ?>
                    <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($product['slug']) ?>"
                        class="group bg-surface-container-lowest rounded-lg border border-outline-variant overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col">
                        <div class="relative w-full aspect-square overflow-hidden bg-surface-container-low flex items-center justify-center">
                            <?php if ($product['is_featured']): ?>
                            <span class="absolute top-2 left-2 bg-axeron-red text-white font-label-sm text-label-sm px-2 py-1 rounded-full uppercase z-10">Nổi bật</span>
                            <?php endif; ?>
                             <img alt="<?= htmlspecialchars($product['product_name']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 15)))) ?>"/>
                            <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full text-on-surface-variant hover:text-axeron-red hover:bg-white transition-colors opacity-0 group-hover:opacity-100 z-10"
                                onclick="event.preventDefault(); addToWishlist(<?= $product['product_id'] ?>)">
                                <span class="material-symbols-outlined">favorite</span>
                            </button>
                        </div>
                        <div class="p-4 flex flex-col flex-grow">
                            <div class="font-label-sm text-label-sm text-on-surface-variant mb-1 uppercase tracking-wider"><?= htmlspecialchars($product['brand_name'] ?? '') ?></div>
                            <h3 class="font-headline-md text-headline-md text-on-surface text-lg leading-tight mb-2 line-clamp-2 group-hover:text-axeron-red transition-colors">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </h3>
                            <?php if ($product['avg_rating']): ?>
                            <div class="flex items-center gap-1 mb-2">
                                <span class="material-symbols-outlined text-sm text-yellow-400" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="text-sm text-on-surface-variant"><?= number_format($product['avg_rating'], 1) ?> (<?= $product['total_reviews'] ?>)</span>
                            </div>
                            <?php endif; ?>
                            <div class="mt-auto pt-2 flex items-center justify-between">
                                <span class="font-headline-md text-headline-md text-axeron-red text-xl"><?= formatPrice($product['base_price']) ?></span>
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

    <script src="<?= BASE_URL ?>/js/main.js"></script>
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
