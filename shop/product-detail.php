<?php
/**
 * Product Detail - Chi tiết sản phẩm
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$slug = sanitize($_GET['slug'] ?? '');
$productId = (int)($_GET['id'] ?? 0);

// Get product
$where = $productId > 0 ? "p.product_id = ?" : "p.slug = ?";
$param = $productId > 0 ? [$productId] : [$slug];

$product = $db->selectOne("
    SELECT p.*, c.category_name, c.slug as category_slug, b.brand_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    WHERE $where AND p.is_visible = 1 AND p.is_deleted = 0
", $param);

if (!$product) {
    header("Location: " . BASE_URL . "/shop/product-catalog.php");
    exit;
}

// Get images
$images = $db->select("
    SELECT image_id, image_url, alt_text, is_primary, color
    FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC
", [$product['product_id']]);

// Get variants grouped by color
$variants = $db->select("
    SELECT variant_id, sku, color, size, extra_price, stock_quantity, is_active
    FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0 ORDER BY color, size
", [$product['product_id']]);

// Group by color
$colorGroups = [];
foreach ($variants as $v) {
    $color = $v['color'] ?? 'default';
    if (!isset($colorGroups[$color])) {
        $colorGroups[$color] = ['color' => $color, 'sizes' => [], 'images' => []];
    }
    $colorGroups[$color]['sizes'][] = $v;
}

// Get review count
$reviewCount = $db->selectOne("
    SELECT COUNT(*) as total FROM reviews WHERE product_id = ? AND status = 'approved' AND is_deleted = 0
", [$product['product_id']]);

// Get related products
$relatedProducts = $db->select("
    SELECT p.product_id, p.product_name, p.slug, p.base_price, p.is_featured, p.category_id, pi.image_url
    FROM products p
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE p.category_id = ? AND p.product_id != ? AND p.is_visible = 1 AND p.is_deleted = 0
    ORDER BY p.is_featured DESC LIMIT 4
", [$product['category_id'], $product['product_id']]);

require_once __DIR__ . '/../config/recommendation.php';

// Log view (chỉ log nếu user còn tồn tại trong database)
if (isLoggedIn()) {
    $userId = getUserId();
    $exists = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
    if ($exists) {
        $db->insert("INSERT INTO product_view_logs (user_id, product_id) VALUES (?, ?)", [$userId, $product['product_id']]);
        getRecommendationEngine()->clearCache();
    }
} else {
    // Khách vãng lai: lưu vào session cho recommendation engine
    trackGuestProductView((int)$product['product_id']);
    getRecommendationEngine()->clearCache();
}

// Tính tổng stock - Ưu tiên stock từ variants, nếu không có thì dùng stock_quantity từ bảng products
$totalStock = array_sum(array_column($variants, 'stock_quantity'));

// Nếu không có variants hoặc totalStock = 0, sử dụng stock_quantity từ bảng products
if ($totalStock <= 0 && $product['stock_quantity'] > 0) {
    $totalStock = $product['stock_quantity'];
}

$hasStock = $totalStock > 0;
$firstColor = array_key_first($colorGroups) ?? 'default';

// Lấy danh sách wishlist của user nếu đã đăng nhập
$userWishlistIds = [];
if (isLoggedIn()) {
    $wl = $db->select("SELECT product_id FROM user_wishlists WHERE user_id = ?", [getUserId()]);
    $userWishlistIds = array_column($wl, 'product_id');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <title><?= htmlspecialchars($product['product_name']) ?> - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&amp;family=Noto+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-background": "#1b1c1c",
                        "inverse-surface": "#303030",
                        "text-dark": "#212121",
                        "background": "#fcf9f8",
                        "axeron-red": "#BE1E2D",
                        "axeron-blue": "#2979FF",
                        "on-surface": "#1b1c1c",
                        "on-surface-variant": "#5b403f",
                        surface: "#fcf9f8",
                        "surface-container": "#f0eded",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-highest": "#e5e2e1",
                        "surface-container-high": "#eae7e7",
                        "surface-container-low": "#f6f3f2",
                        "outline-variant": "#e3bebb",
                        outline: "#8f6f6e",
                        primary: "#98001b",
                        secondary: "#0056c5",
                        white: "#FFFFFF",
                        tertiary: "#005066",
                        "primary-fixed": "#ffdad8",
                        "on-primary": "#ffffff",
                        "secondary-fixed": "#d9e2ff",
                        "on-secondary": "#ffffff"
                    },
                    borderRadius: { DEFAULT: "0.125rem", lg: "0.25rem", xl: "0.5rem", full: "0.75rem" },
                    spacing: { "margin-desktop": "24px", gutter: "16px", "container-max": "1200px", base: "8px", "margin-mobile": "16px" },
                    fontFamily: { "body-lg": ["Noto Sans", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg": ["Montserrat", "sans-serif"] },
                    fontSize: {
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "label-lg": ["14px", {"lineHeight": "20px", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "700"}]
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="max-w-container-max mx-auto px-margin-desktop py-8 md:py-12">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="flex text-on-surface-variant font-label-sm text-label-sm mb-6">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a class="hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>/">Trang chủ</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <span class="material-symbols-outlined text-[16px] mx-1">chevron_right</span>
                        <a class="hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined text-[16px] mx-1">chevron_right</span>
                        <span class="text-on-surface font-semibold"><?= htmlspecialchars($product['product_name']) ?></span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Product Section -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter mb-16">
            <!-- Images Column (Left) -->
            <div class="md:col-span-7 flex flex-col gap-4">
                <!-- Main Image -->
                <div id="img-container" class="w-full bg-surface-container rounded-xl overflow-hidden relative group aspect-square flex-grow min-h-[400px]">
                    <img id="main-image" alt="<?= htmlspecialchars($product['product_name']) ?>"
                         class="w-full h-full object-cover object-center"
                         src="<?= htmlspecialchars(getImageUrl(!empty($images) ? $images[0]['image_url'] : null, 'https://placehold.co/600x600/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 20)))) ?>"/>
                    <?php if ($product['is_featured']): ?>
                    <div class="absolute top-4 left-4 bg-axeron-red text-white px-3 py-1 rounded font-label-sm text-label-sm uppercase font-bold tracking-wider">
                        Nổi bật
                    </div>
                    <?php endif; ?>
                </div>
                <!-- Thumbnails Grid -->
                <?php if (count($images) > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach (array_slice($images, 0, 4) as $idx => $img): ?>
                    <button onclick="changeMainImage('<?= htmlspecialchars($img['image_url']) ?>')"
                            class="rounded-lg overflow-hidden border-2 <?= $idx === 0 ? 'border-axeron-red' : 'border-outline-variant hover:border-axeron-red' ?> transition-colors aspect-square">
                        <img alt="<?= htmlspecialchars($img['alt_text'] ?? '') ?>" class="w-full h-full object-cover" src="<?= htmlspecialchars(getImageUrl($img['image_url'])) ?>"/>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Details Column (Right) -->
            <div class="md:col-span-5 flex flex-col pt-2 md:pl-6">
                <span class="text-on-surface-variant font-label-sm text-label-sm mb-2 uppercase tracking-widest">
                    <?= htmlspecialchars($product['category_name']) ?> / <?= htmlspecialchars($product['brand_name'] ?? '') ?>
                </span>
                <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2 leading-tight">
                    <?= htmlspecialchars($product['product_name']) ?>
                </h1>
                <div class="flex items-center gap-4 mb-4">
                    <span class="text-on-surface-variant font-label-sm text-label-sm">Mã SP: <span class="font-bold text-on-surface"><?= htmlspecialchars($product['slug']) ?></span></span>
                    <?php if ($product['avg_rating']): ?>
                    <div class="flex items-center text-[#FFD700]">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' <?= $i <= round($product['avg_rating']) ? 1 : 0 ?>;">star</span>
                        <?php endfor; ?>
                        <span class="text-on-surface-variant font-label-sm text-label-sm ml-2">(<?= $product['total_reviews'] ?? 0 ?> Đánh giá)</span>
                    </div>
                    <?php endif; ?>
                </div>



                <div class="text-axeron-red font-headline-lg text-headline-lg font-bold mb-6" id="product-price">
                    <?php 
                    $initialPrice = $product['base_price'];
                    if ($initialPrice <= 0 && !empty($variants)) {
                        $initialPrice = min(array_column($variants, 'extra_price'));
                    }
                    
                    // Lấy khuyến mãi cho mức giá khởi điểm
                    $initialPromo = getBestPromotionForProduct($product['product_id'], $product['category_id'], $initialPrice);
                    if ($initialPromo['discount_amount'] > 0) {
                        echo '<div class="flex items-center gap-3">';
                        echo formatPrice($initialPromo['discounted_price']);
                        echo '<span class="text-on-surface-variant line-through text-lg font-medium">' . formatPrice($initialPrice) . '</span>';
                        echo '<span class="text-xs bg-axeron-red text-white px-2 py-1 rounded-sm uppercase tracking-widest">' . htmlspecialchars($initialPromo['promotion']['promo_name']) . '</span>';
                        echo '</div>';
                    } else {
                        echo formatPrice($initialPrice);
                    }
                    ?>
                </div>

                <!-- Color Selection -->
                <?php if (count($colorGroups) > 1): ?>
                <div class="mb-6">
                    <span class="font-label-lg text-label-lg text-on-surface block mb-3">Màu sắc</span>
                    <div class="flex flex-wrap gap-2" id="color-selector">
                        <?php $colorIdx = 0; foreach ($colorGroups as $colorName => $group): ?>
                        <button onclick="selectColor('<?= htmlspecialchars($colorName) ?>')"
                                class="px-4 py-2 rounded-full border color-btn <?= $colorIdx === 0 ? 'border-2 border-axeron-red bg-axeron-red/5 text-axeron-red font-bold' : 'border border-outline-variant hover:border-axeron-red' ?>"
                                data-color="<?= htmlspecialchars($colorName) ?>">
                            <?= htmlspecialchars($colorName) ?>
                        </button>
                        <?php $colorIdx++; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Size Selection -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-label-lg text-label-lg text-on-surface">Kích thước (EU)</span>
                        <a class="text-axeron-blue font-label-sm text-label-sm hover:underline flex items-center gap-1" href="<?= BASE_URL ?>/policies/size-guide.html">
                            <span class="material-symbols-outlined text-[16px]">straighten</span> Hướng dẫn chọn size
                        </a>
                    </div>
                    <div class="flex flex-wrap gap-2" id="size-selector">
                        <?php
                        $firstSize = true;
                        $totalColorStock = 0;
                        $hasSizes = !empty($colorGroups[$firstColor]['sizes']);

                        if ($hasSizes): ?>
                            <?php foreach (($colorGroups[$firstColor]['sizes'] ?? []) as $variant):
                                $isOut = $variant['stock_quantity'] <= 0;
                                $totalColorStock += $variant['stock_quantity'];
                            ?>
                            <button onclick="<?= $isOut ? '' : "selectSize({$variant['variant_id']}, '{$variant['size']}', " . ($product['base_price'] + $variant['extra_price']) . ", {$variant['stock_quantity']})" ?>"
                                    class="w-12 h-12 rounded-full border flex items-center justify-center font-label-lg text-label-lg <?= $isOut ? 'opacity-50 cursor-not-allowed bg-surface-container relative overflow-hidden' : 'hover:border-axeron-red transition-colors text-on-surface-variant ' . ($firstSize && !$isOut ? 'border-2 border-axeron-red bg-axeron-red/5 text-axeron-red font-bold' : 'border border-outline-variant') ?>"
                                    <?= $isOut ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($variant['size']) ?>
                                <?php if ($isOut): ?>
                                <div class="absolute w-full h-[1px] bg-outline-variant rotate-45 top-1/2 left-0"></div>
                                <?php endif; ?>
                            </button>
                            <?php $firstSize = false; endforeach; ?>
                        <?php else: ?>
                            <?php // Sản phẩm không có variants - hiển thị một nút duy nhất để mua trực tiếp
                            if ($hasStock): ?>
                            <button onclick="selectSize(0, 'default', <?= $product['base_price'] ?>, <?= $totalStock ?>)"
                                    class="px-4 py-3 rounded-full border-2 border-axeron-red bg-axeron-red/5 text-axeron-red font-bold hover:bg-axeron-red hover:text-white transition-colors">
                                Mua ngay
                            </button>
                            <?php else: ?>
                            <button disabled class="px-4 py-3 rounded-full border border-gray-300 text-gray-400 cursor-not-allowed">
                                Hết hàng
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm <?= $hasStock ? 'text-green-600' : 'text-red-600' ?> mt-2" id="stock-info">
                        <?= $hasSizes ? "Còn $totalColorStock sản phẩm" : ($hasStock ? "Còn $totalStock sản phẩm" : 'Hết hàng') ?>
                    </p>
                </div>

                <!-- Quantity -->
                <div class="mb-8">
                    <span class="font-label-lg text-label-lg text-on-surface block mb-3">Số lượng</span>
                    <div class="flex items-center border border-outline-variant rounded-lg w-max overflow-hidden bg-white">
                        <button type="button" onclick="changeQty(-1)" class="w-10 h-10 flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface-variant">
                            <span class="material-symbols-outlined">remove</span>
                        </button>
                        <input id="qty-input" class="w-12 h-10 border-0 text-center font-label-lg text-label-lg text-on-surface focus:ring-0 p-0 m-0 [-moz-appearance:_textfield] [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none" min="1" max="99" step="1" value="1"/>
                        <button type="button" onclick="changeQty(1)" class="w-10 h-10 flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface-variant">
                            <span class="material-symbols-outlined">add</span>
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 mt-auto">
                    <button onclick="buyNow()" class="flex-1 bg-axeron-red text-white font-label-lg text-label-lg uppercase font-bold py-4 rounded-lg hover:bg-primary transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">bolt</span>
                        Mua Ngay
                    </button>
                    <div class="flex-1 flex gap-2">
                        <button onclick="addToCartFromDetail()" class="flex-1 border-2 border-axeron-blue text-axeron-blue font-label-lg text-label-lg uppercase font-bold py-4 rounded-lg hover:bg-axeron-blue hover:text-white transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                            Thêm Vào Giỏ
                        </button>
                        <?php
                        $isFavMain = isLoggedIn() && in_array($product['product_id'], $userWishlistIds);
                        ?>
                        <button onclick="addToWishlist(<?= $product['product_id'] ?>, this)" class="w-14 border-2 <?= $isFavMain ? 'border-axeron-red text-axeron-red' : 'border-outline-variant text-on-surface-variant hover:border-axeron-red hover:text-axeron-red' ?> rounded-lg transition-colors flex items-center justify-center" aria-label="Yêu thích">
                            <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' <?= $isFavMain ? 1 : 0 ?>;">favorite</span>
                        </button>
                    </div>
                </div>

                <!-- Perks -->
                <div class="mt-8 grid grid-cols-2 gap-4 border-t border-outline-variant pt-6">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-axeron-blue bg-axeron-blue/10 p-2 rounded-full">local_shipping</span>
                        <div>
                            <span class="block font-label-sm text-label-sm font-bold text-on-surface">Miễn phí giao hàng</span>
                            <span class="block text-xs text-on-surface-variant mt-1">Cho đơn hàng trên 500k</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-axeron-blue bg-axeron-blue/10 p-2 rounded-full">published_with_changes</span>
                        <div>
                            <span class="block font-label-sm text-label-sm font-bold text-on-surface">Đổi trả 30 ngày</span>
                            <span class="block text-xs text-on-surface-variant mt-1">Thủ tục nhanh chóng</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div id="reviews-section" class="mb-16">
            <div class="border-b border-outline-variant flex gap-8 mb-8">
                <button onclick="showSection('description')" id="tab-description" class="font-headline-md text-headline-md font-bold text-axeron-red border-b-2 border-axeron-red pb-4 transition-colors">Mô tả chi tiết</button>
                <button onclick="showSection('reviews')" id="tab-reviews" class="font-headline-md text-headline-md font-semibold text-on-surface-variant hover:text-on-surface pb-4 transition-colors">Đánh giá (<span id="review-count"><?= $reviewCount['total'] ?? 0 ?></span>)</button>
            </div>

            <!-- Description Tab -->
            <div id="content-description" class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                <div class="md:col-span-8 font-body-lg text-body-lg text-on-surface-variant space-y-6">
                    <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Sản phẩm chất lượng cao từ Axeron Sport.')) ?></p>
                </div>
                <div class="md:col-span-4 bg-surface-container-lowest p-6 rounded-xl border border-outline-variant">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Thông số kỹ thuật</h3>
                    <div class="space-y-3 font-body-md text-body-md">
                        <div class="flex justify-between border-b border-surface-container-highest pb-2">
                            <span class="text-on-surface-variant">Thương hiệu</span>
                            <span class="font-medium text-on-surface"><?= htmlspecialchars($product['brand_name'] ?? 'Axeron') ?></span>
                        </div>
                        <div class="flex justify-between border-b border-surface-container-highest pb-2">
                            <span class="text-on-surface-variant">Dòng sản phẩm</span>
                            <span class="font-medium text-on-surface"><?= htmlspecialchars($product['category_name']) ?></span>
                        </div>
                        <div class="flex justify-between border-b border-surface-container-highest pb-2">
                            <span class="text-on-surface-variant">Giới tính</span>
                            <span class="font-medium text-on-surface">Unisex</span>
                        </div>
                        <div class="flex justify-between pb-2">
                            <span class="text-on-surface-variant">Tình trạng</span>
                            <span class="font-medium text-on-surface <?= $hasStock ? 'text-green-600' : 'text-red-600' ?>"><?= $hasStock ? 'Còn hàng' : 'Hết hàng' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="content-reviews" class="hidden">
                <!-- Rating Summary -->
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant mb-8">
                    <div class="flex flex-col md:flex-row gap-8 items-center">
                        <div class="text-center">
                            <div class="text-5xl font-bold text-axeron-red" id="avg-rating"><?= number_format($product['avg_rating'] ?? 0, 1) ?></div>
                            <div class="flex justify-center gap-1 my-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' <?= $i <= round($product['avg_rating'] ?? 0) ? 1 : 0 ?>; color: #FFD700;">star</span>
                                <?php endfor; ?>
                            </div>
                            <p class="text-on-surface-variant text-sm"><?= $reviewCount['total'] ?? 0 ?> đánh giá</p>
                        </div>
                        <div class="flex-1">
                            <!-- Rating distribution bars -->
                            <div id="rating-distribution" class="space-y-2">
                                <!-- Loaded via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Write Review Button -->
                <?php if (isLoggedIn()): ?>
                    <?php
                    $hasPurchased = false;
                    $userId = getUserId();
                    $productId = $product['product_id'];
                    $purchaseCheck = $db->selectOne("
                        SELECT o.order_id 
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        JOIN product_variants pv ON oi.variant_id = pv.variant_id
                        WHERE o.user_id = ? 
                          AND pv.product_id = ? 
                          AND o.order_status = 'delivered'
                        LIMIT 1
                    ", [$userId, $productId]);
                    if ($purchaseCheck) {
                        $hasPurchased = true;
                    }
                    ?>
                    <?php if ($hasPurchased): ?>
                        <div class="mb-6">
                            <button onclick="showReviewForm()" id="btn-write-review" class="px-6 py-3 bg-axeron-red text-white rounded-lg hover:bg-primary transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined">edit</span>
                                Viết đánh giá
                            </button>
                        </div>

                        <!-- Review Form -->
                        <div id="review-form-container" class="hidden bg-surface-container-lowest p-6 rounded-xl border border-outline-variant mb-8">
                            <h3 class="font-headline-md text-headline-md text-on-surface mb-4">Đánh giá sản phẩm</h3>
                            <form id="review-form" onsubmit="submitReviewForm(event)">
                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                                <div class="mb-4">
                                    <label class="block text-on-surface font-medium mb-2">Xếp hạng của bạn</label>
                                    <div class="flex gap-2" id="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <button type="button" onclick="setRating(<?= $i ?>)" class="text-4xl text-gray-300 hover:text-yellow-500 transition-colors" data-rating="<?= $i ?>">
                                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">star</span>
                                        </button>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="input-rating" value="0">
                                    <p class="text-sm text-on-surface-variant mt-1" id="rating-label">Chọn số sao</p>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-on-surface font-medium mb-2">Bình luận của bạn</label>
                                    <textarea name="comment" id="review-comment" rows="4" required minlength="10"
                                              class="w-full px-4 py-3 border border-outline-variant rounded-lg focus:ring-2 focus:ring-axeron-red focus:border-transparent outline-none resize-none"
                                              placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm này (ít nhất 10 ký tự)..."></textarea>
                                    <p class="text-xs text-on-surface-variant mt-1"><span id="char-count">0</span>/10 ký tự tối thiểu</p>
                                </div>

                                <div class="flex gap-4">
                                    <button type="submit" class="px-6 py-3 bg-axeron-red text-white rounded-lg hover:bg-primary transition-colors flex items-center gap-2">
                                        <span class="material-symbols-outlined">send</span>
                                        Gửi đánh giá
                                    </button>
                                    <button type="button" onclick="hideReviewForm()" class="px-6 py-3 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
                                        Hủy
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="mb-6">
                            <button disabled class="px-6 py-3 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed flex items-center gap-2 font-medium">
                                <span class="material-symbols-outlined">lock</span>
                                Mua sản phẩm này để mở khóa tính năng đánh giá
                            </button>
                            <p class="text-sm text-red-500 mt-2 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">info</span>
                                Bạn chỉ có thể đánh giá sản phẩm này sau khi đã mua và nhận hàng thành công!
                            </p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant mb-8 text-center">
                    <p class="text-on-surface-variant mb-4">Bạn cần đăng nhập để viết đánh giá</p>
                    <a href="<?= BASE_URL ?>/auth/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="px-6 py-3 bg-axeron-red text-white rounded-lg hover:bg-primary transition-colors inline-flex items-center gap-2">
                        <span class="material-symbols-outlined">login</span>
                        Đăng nhập
                    </a>
                </div>
                <?php endif; ?>

                <!-- Reviews List -->
                <div id="reviews-list" data-loaded="false"></div>

                <!-- Load More -->
                <div id="reviews-load-more" class="text-center mt-8 hidden">
                    <button onclick="loadMoreReviews()" class="px-6 py-3 border border-outline-variant text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">
                        Xem thêm đánh giá
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mb-8">
            <div class="flex justify-between items-end mb-8">
                <h2 class="font-headline-lg text-headline-lg text-on-surface font-bold">Sản Phẩm Liên Quan</h2>
                <a class="text-axeron-blue hover:text-secondary font-label-lg text-label-lg flex items-center gap-1" href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($product['category_slug']) ?>">Xem tất cả <span class="material-symbols-outlined text-[20px]">arrow_forward</span></a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-gutter">
                <?php foreach ($relatedProducts as $rel): ?>
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($rel['slug']) ?>" 
                   data-aos="fade-up"
                   class="group bg-white rounded-xl overflow-hidden border border-outline-variant hover:shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] transition-all duration-300 flex flex-col relative">
                    <div class="relative w-full aspect-square overflow-hidden bg-surface-container flex items-center justify-center">
                        <?php if (!empty($rel['is_featured'])): ?>
                        <span class="absolute top-2 left-2 bg-gradient-to-r from-orange-500 to-red-600 shadow-[0_0_10px_rgba(239,68,68,0.5)] text-white font-label-sm text-label-sm px-3 py-1 rounded-full uppercase tracking-wider z-10">Nổi bật</span>
                        <?php endif; ?>
                        <?php
                        $isFav = isLoggedIn() && in_array($rel['product_id'], $userWishlistIds);
                        $favColor = $isFav ? 'text-axeron-red' : 'text-on-surface-variant hover:text-axeron-red';
                        $favFill = $isFav ? 1 : 0;
                        $favOpacity = $isFav ? 'opacity-100' : 'opacity-0 group-hover:opacity-100';
                        ?>
                        <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors <?= $favOpacity ?> z-10"
                            onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $rel['product_id'] ?>, this)">
                            <span class="material-symbols-outlined text-[20px] <?= $favColor ?>" style="font-variation-settings: 'FILL' <?= $favFill ?>;">favorite</span>
                        </button>
                        <img alt="<?= htmlspecialchars($rel['product_name']) ?>"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                             src="<?= htmlspecialchars(getImageUrl($rel['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($rel['product_name'], 0, 15)))) ?>"/>
                             
                        <!-- Quick Add Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out z-20 flex justify-center">
                            <button class="bg-on-surface/90 backdrop-blur-sm hover:bg-axeron-red text-white font-label-md text-label-md px-6 py-2.5 rounded-full shadow-lg transition-all flex items-center gap-2 hover:scale-105 w-[90%] justify-center" onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $rel['product_id'] ?>, 0, 1)">
                                <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                                Mua Ngay
                            </button>
                        </div>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-headline-md text-[18px] text-on-surface font-semibold mb-2 truncate group-hover:text-axeron-red transition-colors"><?= htmlspecialchars($rel['product_name']) ?></h3>
                        <?php 
                        $promoInfo = getBestPromotionForProduct($rel['product_id'], $rel['category_id'] ?? 0, $rel['base_price']); 
                        ?>
                        <div class="flex items-center justify-between gap-2 mb-2 min-h-[24px]">
                            <div></div>
                            <?php if ($promoInfo['discount_amount'] > 0): ?>
                            <span class="text-[10px] bg-gradient-to-r from-red-600 to-orange-500 shadow-[0_0_8px_rgba(239,68,68,0.4)] text-white px-2 py-0.5 rounded-sm uppercase tracking-widest text-center max-w-[80px] leading-tight flex-shrink-0"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mt-auto pt-2 flex items-center justify-between">
                            <?php if ($promoInfo['discount_amount'] > 0): ?>
                                <div class="flex flex-col">
                                    <span class="font-headline-md text-headline-md text-axeron-red text-xl"><?= formatPrice($promoInfo['discounted_price']) ?></span>
                                    <span class="text-on-surface-variant line-through text-xs font-medium"><?= formatPrice($rel['base_price']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="font-headline-md text-headline-md text-axeron-red text-xl"><?= formatPrice($rel['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="<?= BASE_URL ?>/js/main.js?v=<?= time() ?>"></script>

    <script>
        const productId = <?= $product['product_id'] ?>;
        const basePrice = <?= (float)$product['base_price'] ?>;
        const variants = <?= json_encode($colorGroups) ?>;
        const hasVariants = <?= !empty($colorGroups) ? 'true' : 'false' ?>;
        const productImages = <?= json_encode($images) ?>;
        const productPromo = <?= json_encode($initialPromo['promotion']) ?>;
        let selectedColor = '<?= htmlspecialchars($firstColor) ?>';
        let selectedVariantId = null;
        let selectedVariant = null;
        const totalStock = <?= $totalStock ?>;

        function changeMainImage(url) {
            document.getElementById('main-image').src = window.getImageUrl(url);
            // Xóa viền đỏ ở tất cả các thumbnail
            const thumbs = document.querySelectorAll('.grid.grid-cols-4 button');
            thumbs.forEach(btn => {
                btn.classList.remove('border-axeron-red');
                btn.classList.add('border-outline-variant');
            });
            // Thêm viền đỏ vào thumbnail được click (cần truyền element vào hàm changeMainImage để làm việc này chuẩn, hoặc tìm theo src)
            // Tạm thời bỏ qua vì cần refactor
        }

        function selectColor(colorName) {
            selectedColor = colorName;
            
            // Switch main image to matching color image if exists
            if (productImages && productImages.length > 0) {
                const matchedImage = productImages.find(img => img.color && img.color.toLowerCase() === colorName.toLowerCase());
                if (matchedImage) {
                    changeMainImage(matchedImage.image_url);
                }
            }
            const colorBtns = document.querySelectorAll('.color-btn');
            colorBtns.forEach(function(btn) {
                btn.classList.remove('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
                btn.classList.add('border', 'border-outline-variant');
            });
            const activeBtn = document.querySelector('.color-btn[data-color="' + colorName + '"]');
            if (activeBtn) {
                activeBtn.classList.remove('border', 'border-outline-variant');
                activeBtn.classList.add('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
            }

            var selector = document.getElementById('size-selector');
            if (!selector) return;
            selector.innerHTML = '';
            var totalColorStock = 0;
            var sizes = variants[colorName]?.sizes || [];

            sizes.forEach(function(v, idx) {
                totalColorStock += v.stock_quantity;
                var isOut = v.stock_quantity <= 0;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-12 h-12 rounded-full border flex items-center justify-center font-label-lg text-label-lg ' +
                    (isOut ? 'opacity-50 cursor-not-allowed bg-surface-container relative overflow-hidden' : 'hover:border-axeron-red transition-colors text-on-surface-variant ') +
                    (idx === 0 && !isOut ? 'border-2 border-axeron-red bg-axeron-red/5 text-axeron-red font-bold' : 'border border-outline-variant');
                btn.innerHTML = v.size + (isOut ? '<div class="absolute w-full h-[1px] bg-outline-variant rotate-45 top-1/2 left-0"></div>' : '');
                if (!isOut) {
                    btn.onclick = function() { selectSize(v.variant_id, v.size, basePrice + parseFloat(v.extra_price || 0), v.stock_quantity); };
                } else {
                    btn.disabled = true;
                }
                selector.appendChild(btn);
            });

            var firstAvailableSizeBtn = selector.querySelector('button:not([disabled])');
            if (firstAvailableSizeBtn) {
                firstAvailableSizeBtn.click();
            }

            var stockInfo = document.getElementById('stock-info');
            if (stockInfo) stockInfo.textContent = 'Còn ' + totalColorStock + ' sản phẩm';
        }

        function selectSize(variantId, size, price, stock) {
            selectedVariantId = variantId;
            selectedVariant = { size: size, price: price, stock: stock };

            var buttons = document.querySelectorAll('#size-selector button');
            buttons.forEach(function(b) {
                b.classList.remove('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
                b.classList.add('border', 'border-outline-variant', 'text-on-surface-variant');
            });

            // Handle when no variants exist
            if (hasVariants) {
                var buttonsList = document.querySelectorAll('#size-selector button');
                buttonsList.forEach(function(b) {
                    if (b.textContent.trim() === size) {
                        b.classList.add('border-2', 'border-axeron-red', 'bg-axeron-red/5', 'text-axeron-red', 'font-bold');
                        b.classList.remove('border', 'border-outline-variant', 'text-on-surface-variant');
                    }
                });
            }

            let finalPrice = price;
            let discountHtml = '';
            
            if (productPromo) {
                let discountAmount = 0;
                if (productPromo.discount_type === 'percent') {
                    discountAmount = (price * parseFloat(productPromo.discount_value)) / 100;
                } else {
                    discountAmount = parseFloat(productPromo.discount_value);
                }
                
                if (productPromo.max_discount > 0 && discountAmount > parseFloat(productPromo.max_discount)) {
                    discountAmount = parseFloat(productPromo.max_discount);
                }
                
                if (discountAmount > price) discountAmount = price;
                
                finalPrice = price - discountAmount;
                
                discountHtml = '<div class="flex items-center gap-3">' +
                               new Intl.NumberFormat('vi-VN').format(finalPrice).replace(/,/g, '.') + 'đ' +
                               '<span class="text-on-surface-variant line-through text-lg font-medium">' + new Intl.NumberFormat('vi-VN').format(price).replace(/,/g, '.') + 'đ</span>' +
                               '<span class="text-xs bg-axeron-red text-white px-2 py-1 rounded-sm uppercase tracking-widest">' + productPromo.promo_name + '</span>' +
                               '</div>';
            } else {
                discountHtml = new Intl.NumberFormat('vi-VN').format(price).replace(/,/g, '.') + 'đ';
            }

            document.getElementById('product-price').innerHTML = discountHtml;
        }

        function changeQty(delta) {
            var input = document.getElementById('qty-input');
            var val = parseInt(input.value) || 1;
            var max = parseInt(input.max) || 99;

            // Tính giá trị mới
            val = val + delta;

            // Giới hạn
            if (val < 1) val = 1;
            if (val > max) val = max;

            input.value = val;
        }

        async function addToCartFromDetail() {
            // Nếu sản phẩm không có variants và chưa chọn mua
            if (hasVariants && selectedVariantId === null) {
                showToast('Vui lòng chọn kích thước', 'error');
                return;
            }
            // Nếu sản phẩm không có variants (selectedVariantId = 0)
            if (!hasVariants) {
                // Tạo variant mặc định hoặc thêm trực tiếp sản phẩm vào giỏ
                await addToCart(productId, 0, parseInt(document.getElementById('qty-input').value) || 1);
                return;
            }
            await addToCart(productId, selectedVariantId, parseInt(document.getElementById('qty-input').value) || 1);
        }

        async function buyNow() {
            // Nếu sản phẩm không có variants và chưa chọn mua
            if (hasVariants && selectedVariantId === null) {
                showToast('Vui lòng chọn kích thước', 'error');
                return;
            }
            // Nếu sản phẩm không có variants (selectedVariantId = 0)
            if (!hasVariants) {
                var result = await addToCart(productId, 0, parseInt(document.getElementById('qty-input').value) || 1);
                if (result) window.location.href = BASE_URL + '/shop/checkout.php';
                return;
            }
            var result = await addToCart(productId, selectedVariantId, parseInt(document.getElementById('qty-input').value) || 1);
            if (result) window.location.href = BASE_URL + '/shop/checkout.php';
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (hasVariants) {
                var firstBtn = document.querySelector('#size-selector button:not([disabled])');
                if (firstBtn) firstBtn.click();
            }
        });

        // ==================== REVIEWS FUNCTIONS ====================
        let currentReviewPage = 1;
        let totalReviewPages = 1;
        let hasMoreReviews = false;

        // Section tabs
        function showSection(section) {
            const tabs = document.querySelectorAll('[id^="tab-"]');
            tabs.forEach(tab => {
                if (tab.id === 'tab-' + section) {
                    tab.classList.add('text-axeron-red', 'border-axeron-red', 'font-bold');
                    tab.classList.remove('text-on-surface-variant');
                } else {
                    tab.classList.remove('text-axeron-red', 'border-axeron-red', 'font-bold');
                    tab.classList.add('text-on-surface-variant');
                }
            });

            const contentDesc = document.getElementById('content-description');
            const contentReviews = document.getElementById('content-reviews');

            if (section === 'description') {
                contentDesc?.classList.remove('hidden');
                contentReviews?.classList.add('hidden');
            } else {
                contentDesc?.classList.add('hidden');
                contentReviews?.classList.remove('hidden');
                // Load reviews if not loaded
                const reviewsList = document.getElementById('reviews-list');
                if (reviewsList && reviewsList.dataset.loaded !== 'true') {
                    currentReviewPage = 1; // Reset page
                    loadReviews();
                }
            }
        }

        // Star rating
        function setRating(rating) {
            document.getElementById('input-rating').value = rating;
            const stars = document.querySelectorAll('#rating-stars button');
            const labels = ['Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Tuyệt vời'];
            stars.forEach((btn, idx) => {
                const icon = btn.querySelector('.material-symbols-outlined');
                if (idx < rating) {
                    icon.style.fontVariationSettings = "'FILL' 1";
                    icon.style.color = '#FFD700';
                } else {
                    icon.style.fontVariationSettings = "'FILL' 0";
                    icon.style.color = '#d1d5db';
                }
            });
            document.getElementById('rating-label').textContent = labels[rating - 1] || 'Chọn số sao';
        }

        // Show/hide review form
        function showReviewForm() {
            document.getElementById('review-form-container').classList.remove('hidden');
            document.getElementById('btn-write-review').classList.add('hidden');
        }

        function hideReviewForm() {
            document.getElementById('review-form-container').classList.add('hidden');
            document.getElementById('btn-write-review').classList.remove('hidden');
            // Reset form
            document.getElementById('review-form').reset();
            setRating(0);
            document.getElementById('char-count').textContent = '0';
        }

        // Character counter for comment
        document.getElementById('review-comment')?.addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length;
        });

        // Submit review
        async function submitReviewForm(e) {
            e.preventDefault();
            const rating = parseInt(document.getElementById('input-rating').value);
            const comment = document.getElementById('review-comment').value.trim();

            if (rating === 0) {
                showToast('Vui lòng chọn số sao', 'error');
                return;
            }
            if (comment.length < 10) {
                showToast('Vui lòng nhập bình luận ít nhất 10 ký tự', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'submit_review');
            formData.append('product_id', productId);
            formData.append('rating', rating);
            formData.append('comment', comment);

            try {
                const response = await fetch(BASE_URL + '/api/products.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    hideReviewForm();
                } else {
                    showToast(result.message || 'Có lỗi xảy ra', 'error');
                }
            } catch (err) {
                showToast('Có lỗi xảy ra khi gửi đánh giá', 'error');
            }
        }

        // Load reviews
        async function loadReviews() {
            const reviewsList = document.getElementById('reviews-list');
            const isFirstPage = currentReviewPage === 1;

            // Show loading only on first page
            if (isFirstPage) {
                let skeletonHTML = '';
                for(let i=0; i<3; i++) {
                    skeletonHTML += `
                    <div class="flex gap-4 animate-pulse border-b border-outline-variant pb-6 mb-6 last:border-b-0">
                        <div class="w-12 h-12 bg-surface-container rounded-full flex-shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="h-4 bg-surface-container rounded w-32"></div>
                                <div class="h-3 bg-surface-container rounded w-20 ml-auto"></div>
                            </div>
                            <div class="flex gap-1 mb-3">
                                <div class="w-4 h-4 bg-surface-container rounded"></div><div class="w-4 h-4 bg-surface-container rounded"></div><div class="w-4 h-4 bg-surface-container rounded"></div><div class="w-4 h-4 bg-surface-container rounded"></div><div class="w-4 h-4 bg-surface-container rounded"></div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-4 bg-surface-container rounded w-full"></div>
                                <div class="h-4 bg-surface-container rounded w-5/6"></div>
                            </div>
                        </div>
                    </div>`;
                }
                reviewsList.innerHTML = `<div class="py-4">${skeletonHTML}</div>`;
            } else {
                // Append loading indicator for pagination
                const loader = document.createElement('div');
                loader.id = 'reviews-loading';
                loader.className = 'py-4';
                loader.innerHTML = `
                    <div class="flex gap-4 animate-pulse border-b border-outline-variant pb-6 mb-6">
                        <div class="w-12 h-12 bg-surface-container rounded-full flex-shrink-0"></div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2"><div class="h-4 bg-surface-container rounded w-32"></div><div class="h-3 bg-surface-container rounded w-20 ml-auto"></div></div>
                            <div class="space-y-2"><div class="h-4 bg-surface-container rounded w-full"></div></div>
                        </div>
                    </div>`;
                reviewsList.appendChild(loader);
            }

            try {
                const response = await fetch(BASE_URL + '/api/products.php?action=reviews&product_id=' + productId + '&page=' + currentReviewPage);
                const result = await response.json();

                // Remove loading indicator if exists
                document.getElementById('reviews-loading')?.remove();

                if (result.success) {
                    totalReviewPages = result.data.total_pages;
                    hasMoreReviews = currentReviewPage < totalReviewPages;

                    if (result.data.reviews.length === 0 && isFirstPage) {
                        reviewsList.innerHTML = '<div class="text-center py-8 text-on-surface-variant"><span class="material-symbols-outlined text-5xl text-gray-300">rate_review</span><p class="mt-4">Chưa có đánh giá nào cho sản phẩm này</p><p class="text-sm mt-2">Hãy là người đầu tiên đánh giá!</p></div>';
                    } else if (result.data.reviews.length > 0) {
                        renderReviews(result.data.reviews);
                    }

                    // Mark as loaded
                    reviewsList.dataset.loaded = 'true';

                    // Show/hide load more button
                    document.getElementById('reviews-load-more').classList.toggle('hidden', !hasMoreReviews);
                } else {
                    if (isFirstPage) {
                        reviewsList.innerHTML = '<div class="text-center py-8 text-red-500">Không thể tải đánh giá</div>';
                    }
                }
            } catch (err) {
                document.getElementById('reviews-loading')?.remove();
                if (isFirstPage) {
                    reviewsList.innerHTML = '<div class="text-center py-8 text-red-500">Có lỗi khi tải đánh giá</div>';
                }
            }
        }

        // Render reviews
        function renderReviews(reviews) {
            const reviewsList = document.getElementById('reviews-list');
            const html = reviews.map(review => {
                const date = new Date(review.created_at).toLocaleDateString('vi-VN');
                const avatarInitial = review.full_name ? review.full_name.charAt(0).toUpperCase() : 'U';
                const avatarUrl = review.avatar_url || null;

                return `
                    <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant mb-4">
                        <div class="flex items-start gap-4">
                            ${avatarUrl
                                ? `<img src="${avatarUrl}" alt="${review.full_name}" class="w-12 h-12 rounded-full object-cover">`
                                : `<div class="w-12 h-12 rounded-full bg-axeron-red text-white flex items-center justify-center font-bold text-lg">${avatarInitial}</div>`
                            }
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-on-surface">${review.full_name || 'Người dùng'}</span>
                                    <span class="text-sm text-on-surface-variant">${date}</span>
                                </div>
                                <div class="flex gap-0.5 mb-2">
                                    ${[1,2,3,4,5].map(i => `
                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' ${i <= review.rating ? 1 : 0}; color: #FFD700;">star</span>
                                    `).join('')}
                                </div>
                                <p class="text-on-surface-variant">${review.comment}</p>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            if (currentReviewPage === 1) {
                reviewsList.innerHTML = html;
            } else {
                reviewsList.innerHTML += html;
            }
        }

        // Load more reviews
        function loadMoreReviews() {
            currentReviewPage++;
            loadReviews();
        }

        // Load reviews on page load (if hash is #reviews or #reviews-section)
        if (window.location.hash === '#reviews' || window.location.hash === '#reviews-section') {
            setTimeout(() => {
                showSection('reviews');
                
                // Show review form if the user is a verified buyer (form exists)
                if (document.getElementById('review-form-container')) {
                    showReviewForm();
                }
                
                // Focus on review comment textarea
                const textarea = document.getElementById('review-comment');
                if (textarea) {
                    textarea.focus();
                }
                
                // Smooth scroll to reviews section
                const target = document.getElementById('reviews-section');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }, 200);
        }
    </script>
</body>
</html>
