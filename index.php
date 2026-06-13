<?php
/**
 * Trang chủ - Axeron Sports Shop
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/recommendation.php';

$db = db();

// Load featured products
$featuredProducts = $db->select("
    SELECT
        p.product_id,
        p.category_id,
        p.product_name,
        p.slug,
        p.base_price,
        p.avg_rating,
        p.total_reviews,
        c.category_name,
        b.brand_name,
        pi.image_url,
        p.is_featured
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE p.is_visible = 1 AND p.is_deleted = 0 AND p.is_featured = 1
    ORDER BY p.featured_sort_order ASC, p.updated_at DESC, p.created_at DESC
    LIMIT 10
");

// Load shoes for new section
$shoesProducts = $db->select("
    SELECT
        p.product_id,
        p.category_id,
        p.product_name,
        p.slug,
        p.base_price,
        c.slug as category_slug,
        pi.image_url,
        p.is_featured
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE p.is_visible = 1 AND p.is_deleted = 0 AND c.slug IN ('giay-pickleball', 'giay-da-bong', 'giay-cau-long', 'giay-chay-bo')
    ORDER BY p.updated_at DESC
");

// Load banners dynamically
$banners = $db->select("
    SELECT banner_id, title, subtitle, image_url, image_url_mobile, link_url, link_type, button_text
    FROM banners
    WHERE is_active = 1
    AND (start_date IS NULL OR start_date <= NOW())
    AND (end_date IS NULL OR end_date >= NOW())
    ORDER BY position ASC
    LIMIT 6
");

// Load articles/news dynamically
$articles = $db->select("
    SELECT article_id, title, slug, excerpt, featured_image, category, published_at
    FROM articles
    WHERE is_published = 1
    ORDER BY is_featured DESC, published_at DESC
    LIMIT 10
");

// Load site settings for dynamic content
$siteSettings = $db->select("SELECT setting_key, setting_value FROM site_settings WHERE is_public = 1");
$settings = [];
foreach ($siteSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

// Load categories for banner
$categories = $db->select("
    SELECT category_id, category_name, slug, image_url
    FROM categories
    WHERE parent_id IS NULL AND is_visible = 1
    ORDER BY sort_order
    LIMIT 4
");

// Load personalized recommendations
$recoEngine = getRecommendationEngine();
$recommendedProducts = $recoEngine->getRecommendations(10);
$recoSourceType = $recoEngine->getSourceType();
$recoTitle = $recoSourceType === 'personalized' 
    ? (isLoggedIn() ? 'Dành Riêng Cho Bạn' : 'Có Thể Bạn Quan Tâm')
    : 'Có Thể Bạn Quan Tâm';

// Lấy danh sách wishlist của user nếu đã đăng nhập
$userWishlistIds = [];
if (isLoggedIn()) {
    $wl = $db->select("SELECT product_id FROM user_wishlists WHERE user_id = ?", [getUserId()]);
    $userWishlistIds = array_column($wl, 'product_id');
}
?>
<?php $pageTitle = 'Axeron - Dụng cụ thể thao chuyên nghiệp'; require_once __DIR__ . '/includes/head.php'; ?>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- Banner Hero Slider -->
        <section class="relative w-full overflow-hidden group" id="hero-slider" style="aspect-ratio: 1920/700; min-height: 300px;">
            <!-- Slide 1 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100" id="slide-1" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php'" style="cursor: pointer;">
                <img alt="Siêu sự kiện ưu đãi hè" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_1.jpg"/>
            </div>
            <!-- Slide 2 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0" id="slide-2" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php?category=vot-pickleball'" style="cursor: pointer;">
                <img alt="Vợt Pickleball Axeron Summit & Apex Pro" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_2.jpg"/>
            </div>
            <!-- Slide 3 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0" id="slide-3" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php?category=giay-chay-bo'" style="cursor: pointer;">
                <img alt="Axeron Run New Arrival" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_3.jpg"/>
            </div>
            <!-- Slide 4 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0" id="slide-4" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php?category=ao-polo-nam'" style="cursor: pointer;">
                <img alt="Summer Collection 2026" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_4.jpg"/>
            </div>
            <!-- Slide 5 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0" id="slide-5" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php?category=vot-pickleball'" style="cursor: pointer;">
                <img alt="Pickleball Championship Line" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_5.jpg"/>
            </div>
            <!-- Slide 6 -->
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-0" id="slide-6" onclick="window.location.href='<?= BASE_URL ?>/shop/product-catalog.php'" style="cursor: pointer;">
                <img alt="Axeron Hot Summer Performance Sale" class="w-full h-full object-cover" src="<?= BASE_URL ?>/assets/slide-banner/home_slide_banner_6.jpg"/>
            </div>
        </section>

        <!-- Promo Banner -->
        <div class="w-full bg-[#333333] text-white py-3 text-label-sm font-label-sm tracking-wide marquee-container">
            <div class="marquee-content">
                Freeship với hóa đơn trên 500k |
                <a class="underline hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>/auth/register.php">ĐĂNG KÝ NGAY</a>
            </div>
        </div>

        <!-- Product Categories Marquee (Right to Left Loop) -->
        <style>
            @keyframes image-marquee {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            .img-marquee-wrapper {
                display: flex;
                width: 300%; /* Desktop: 2 items visible (6 items total) */
                animation: image-marquee 15s linear infinite;
            }
            .img-marquee-wrapper:hover {
                animation-play-state: paused;
            }
            .img-marquee-item {
                width: 16.666667%;
                flex-shrink: 0;
                padding: 0 12px;
            }
            @media (max-width: 767px) {
                .img-marquee-wrapper {
                    width: 600%; /* Mobile: 1 item visible */
                }
            }
        </style>
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16 overflow-hidden">
            <div class="-mx-[12px]"> <!-- Removed flex to prevent shrinking -->
                <div class="img-marquee-wrapper">
                    <!-- Original 3 -->
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=ao-polo-nam">
                            <img alt="Axeron Nike Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_1.jpg"/>
                        </a>
                    </div>
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=giay-pickleball">
                            <img alt="Axeron Pickleball Shoes Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_2.jpg"/>
                        </a>
                    </div>
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=vot-cau-long">
                            <img alt="Axeron Badminton Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_3.jpg"/>
                        </a>
                    </div>
                    <!-- Duplicated 3 for seamless loop -->
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=ao-polo-nam">
                            <img alt="Axeron Nike Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_1.jpg"/>
                        </a>
                    </div>
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=giay-pickleball">
                            <img alt="Axeron Pickleball Shoes Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_2.jpg"/>
                        </a>
                    </div>
                    <div class="img-marquee-item">
                        <a class="relative block w-full h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=vot-cau-long">
                            <img alt="Axeron Badminton Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                src="<?= BASE_URL ?>/assets/images/home_banner_image_3.jpg"/>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Sản phẩm nổi bật -->
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-surface-dim pb-4">
                <h2 class="font-headline-lg text-headline-lg text-on-background uppercase relative">
                    Sản Phẩm Nổi Bật
                    <span class="absolute -bottom-4 left-0 w-1/2 h-1 bg-axeron-red"></span>
                </h2>
                <a class="hidden md:flex items-center gap-1 text-label-lg font-label-lg text-on-surface-variant hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>/shop/product-catalog.php">
                    Xem tất cả <span class="material-symbols-outlined text-lg">chevron_right</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-gutter" id="featured-products">
                <?php foreach ($featuredProducts as $product): ?>
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($product['slug']) ?>"
                    class="group border border-outline-variant rounded-xl overflow-hidden bg-white hover:shadow-lg transition-all duration-300 flex flex-col relative">
                    <div class="aspect-square bg-surface-container-low relative overflow-hidden flex items-center justify-center">
                        <?php if ($product['is_featured'] ?? false): ?>
                        <span class="absolute top-2 left-2 bg-axeron-red text-white font-label-sm text-label-sm px-2 py-1 rounded-full uppercase z-10">Nổi bật</span>
                        <?php endif; ?>
                        <?php
                        $isFav = isLoggedIn() && in_array($product['product_id'], $userWishlistIds);
                        $favColor = $isFav ? 'text-axeron-red' : 'text-on-surface-variant hover:text-axeron-red';
                        $favFill = $isFav ? 1 : 0;
                        $favOpacity = $isFav ? 'opacity-100' : 'opacity-0 group-hover:opacity-100';
                        ?>
                        <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors <?= $favOpacity ?> z-10"
                            onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $product['product_id'] ?>, this)">
                            <span class="material-symbols-outlined text-[20px] <?= $favColor ?>" style="font-variation-settings: 'FILL' <?= $favFill ?>;">favorite</span>
                        </button>
                        <img alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 20)))) ?>"/>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-label-lg text-label-lg text-on-background mb-2 text-truncate-2 group-hover:text-axeron-red transition-colors">
                            <?= htmlspecialchars($product['product_name']) ?>
                        </h3>
                        <div class="mt-auto flex items-center justify-between">
                            <?php 
                            $promoInfo = getBestPromotionForProduct($product['product_id'], $product['category_id'] ?? 0, $product['base_price']);
                            if ($promoInfo['discount_amount'] > 0): ?>
                                <div class="flex flex-col">
                                    <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($promoInfo['discounted_price']) ?></span>
                                    <span class="text-on-surface-variant line-through text-xs font-medium"><?= formatPrice($product['base_price']) ?></span>
                                </div>
                                <span class="text-[10px] bg-axeron-red text-white px-1.5 py-0.5 rounded-sm uppercase tracking-widest ml-2"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
                            <?php else: ?>
                                <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($product['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section: Giày Thể Thao -->
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 bg-surface-container-low md:bg-transparent rounded-2xl md:rounded-none">
            <div class="flex flex-row justify-between items-center mb-6 border-b border-surface-dim pb-4">
                <h2 class="font-headline-lg text-headline-lg text-on-background uppercase relative shrink-0">
                    Giày Thể Thao
                    <span class="absolute -bottom-4 left-0 w-1/2 h-1 bg-axeron-red"></span>
                </h2>
                
                <a class="flex items-center gap-1 text-label-lg font-label-lg text-on-surface-variant hover:text-axeron-red transition-colors shrink-0 whitespace-nowrap" href="<?= BASE_URL ?>/shop/product-catalog.php?category=giay-the-thao" id="shoes-view-all">
                    Xem tất cả <span class="material-symbols-outlined text-lg">chevron_right</span>
                </a>
            </div>
            
            <!-- Category Tabs -->
            <div class="flex gap-4 md:gap-6 mb-8 overflow-x-auto w-full no-scrollbar">
                <button class="shoe-tab-btn active text-axeron-red font-bold border-b-2 border-axeron-red pb-1 whitespace-nowrap" data-target="giay-pickleball">Giày Pickleball</button>
                <button class="shoe-tab-btn text-on-surface-variant hover:text-axeron-red font-medium whitespace-nowrap" data-target="giay-da-bong">Giày đá bóng</button>
                <button class="shoe-tab-btn text-on-surface-variant hover:text-axeron-red font-medium whitespace-nowrap" data-target="giay-cau-long">Giày cầu lông</button>
                <button class="shoe-tab-btn text-on-surface-variant hover:text-axeron-red font-medium whitespace-nowrap" data-target="giay-chay-bo">Giày chạy bộ</button>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-gutter" id="shoes-container">
                <?php foreach ($shoesProducts as $product): ?>
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($product['slug']) ?>"
                    class="shoe-item <?= htmlspecialchars($product['category_slug']) ?> hidden group border border-outline-variant rounded-xl overflow-hidden bg-white hover:shadow-lg transition-all duration-300 flex-col relative">
                    <div class="aspect-square bg-surface-container-low relative overflow-hidden flex items-center justify-center">
                        <?php if ($product['is_featured'] ?? false): ?>
                        <span class="absolute top-2 left-2 bg-axeron-red text-white font-label-sm text-label-sm px-2 py-1 rounded-full uppercase z-10">Nổi bật</span>
                        <?php endif; ?>
                        <?php
                        $isFav = isLoggedIn() && in_array($product['product_id'], $userWishlistIds);
                        $favColor = $isFav ? 'text-axeron-red' : 'text-on-surface-variant hover:text-axeron-red';
                        $favFill = $isFav ? 1 : 0;
                        $favOpacity = $isFav ? 'opacity-100' : 'opacity-0 group-hover:opacity-100';
                        ?>
                        <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors <?= $favOpacity ?> z-10"
                            onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $product['product_id'] ?>, this)">
                            <span class="material-symbols-outlined text-[20px] <?= $favColor ?>" style="font-variation-settings: 'FILL' <?= $favFill ?>;">favorite</span>
                        </button>
                        <img alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 20)))) ?>"/>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-label-lg text-label-lg text-on-background mb-2 text-truncate-2 group-hover:text-axeron-red transition-colors">
                            <?= htmlspecialchars($product['product_name']) ?>
                        </h3>
                        <div class="mt-auto flex items-center justify-between">
                            <?php 
                            $promoInfo = getBestPromotionForProduct($product['product_id'], $product['category_id'] ?? 0, $product['base_price']);
                            if ($promoInfo['discount_amount'] > 0): ?>
                                <div class="flex flex-col">
                                    <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($promoInfo['discounted_price']) ?></span>
                                    <span class="text-on-surface-variant line-through text-xs font-medium"><?= formatPrice($product['base_price']) ?></span>
                                </div>
                                <span class="text-[10px] bg-axeron-red text-white px-1.5 py-0.5 rounded-sm uppercase tracking-widest ml-2"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
                            <?php else: ?>
                                <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($product['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <div class="col-span-full text-center text-on-surface-variant py-8 hidden" id="empty-shoes-msg">Chưa có sản phẩm nào trong danh mục này.</div>
            </div>
        </section>

        <!-- Section: Gợi ý cá nhân hóa -->
        <?php if (!empty($recommendedProducts)): ?>
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-surface-dim pb-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-axeron-red text-3xl" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                    <h2 class="font-headline-lg text-headline-lg text-on-background uppercase relative">
                        <?= htmlspecialchars($recoTitle) ?>
                        <span class="absolute -bottom-4 left-0 w-1/2 h-1 bg-gradient-to-r from-axeron-red to-axeron-blue"></span>
                    </h2>
                </div>
                <a class="hidden md:flex items-center gap-1 text-label-lg font-label-lg text-on-surface-variant hover:text-axeron-red transition-colors" href="<?= BASE_URL ?>/shop/product-catalog.php">
                    Xem tất cả <span class="material-symbols-outlined text-lg">chevron_right</span>
                </a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-gutter" id="recommended-products">
                <?php foreach ($recommendedProducts as $rProduct): ?>
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($rProduct['slug']) ?>"
                    class="group border border-outline-variant rounded-xl overflow-hidden bg-white hover:shadow-lg transition-all duration-300 flex flex-col relative">
                    <div class="aspect-square bg-surface-container-low relative overflow-hidden flex items-center justify-center">
                        <?php if ($rProduct['is_featured'] ?? false): ?>
                        <span class="absolute top-2 left-2 bg-axeron-red text-white font-label-sm text-label-sm px-2 py-1 rounded-full uppercase z-10">Nổi bật</span>
                        <?php endif; ?>
                        <?php
                        $isFav = isLoggedIn() && in_array($rProduct['product_id'], $userWishlistIds);
                        $favColor = $isFav ? 'text-axeron-red' : 'text-on-surface-variant hover:text-axeron-red';
                        $favFill = $isFav ? 1 : 0;
                        $favOpacity = $isFav ? 'opacity-100' : 'opacity-0 group-hover:opacity-100';
                        ?>
                        <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors <?= $favOpacity ?> z-10"
                            onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $rProduct['product_id'] ?>, this)">
                            <span class="material-symbols-outlined text-[20px] <?= $favColor ?>" style="font-variation-settings: 'FILL' <?= $favFill ?>;">favorite</span>
                        </button>
                        <img alt="<?= htmlspecialchars($rProduct['product_name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            src="<?= htmlspecialchars(getImageUrl($rProduct['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($rProduct['product_name'], 0, 20)))) ?>"/>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-label-lg text-label-lg text-on-background mb-2 text-truncate-2 group-hover:text-axeron-red transition-colors">
                            <?= htmlspecialchars($rProduct['product_name']) ?>
                        </h3>
                        <div class="mt-auto flex items-center justify-between">
                            <?php 
                            $promoInfo = getBestPromotionForProduct($rProduct['product_id'], $rProduct['category_id'] ?? 0, $rProduct['base_price']);
                            if ($promoInfo['discount_amount'] > 0): ?>
                                <div class="flex flex-col">
                                    <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($promoInfo['discounted_price']) ?></span>
                                    <span class="text-on-surface-variant line-through text-xs font-medium"><?= formatPrice($rProduct['base_price']) ?></span>
                                </div>
                                <span class="text-[10px] bg-axeron-red text-white px-1.5 py-0.5 rounded-sm uppercase tracking-widest ml-2"><?= htmlspecialchars($promoInfo['promotion']['promo_name']) ?></span>
                            <?php else: ?>
                                <span class="font-headline-md text-body-lg text-axeron-red font-bold"><?= formatPrice($rProduct['base_price']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Latest News / Articles -->
        <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-16 md:py-24">
            <div class="flex justify-between items-end mb-12">
                <div class="flex flex-col">
                    <span class="text-axeron-red font-label-lg uppercase tracking-widest mb-2">Cập nhật xu hướng</span>
                    <h2 class="font-headline-lg text-headline-lg uppercase">Tin Tức Mới Nhất</h2>
                </div>
                <a class="font-label-lg text-on-surface-variant hover:text-axeron-red flex items-center gap-1" href="<?= BASE_URL ?>/blog/news.php">
                    Xem toàn bộ tin <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
            <div class="relative overflow-hidden w-full group" id="news-slider-wrapper">
                <div class="flex gap-6 overflow-x-auto no-scrollbar snap-x snap-mandatory pb-4 transition-transform duration-300 ease-out" id="news-slider">
                    <?php foreach ($articles as $article): ?>
                    <a href="<?= BASE_URL ?>/blog/news.php?slug=<?= htmlspecialchars($article['slug']) ?>"
                        class="flex-shrink-0 w-[85vw] sm:w-[350px] snap-center group/card border border-outline-variant rounded-xl overflow-hidden bg-white hover:shadow-lg transition-all duration-300 flex flex-col">
                        <div class="aspect-[16/10] bg-surface-container-low overflow-hidden">
                            <img alt="<?= htmlspecialchars($article['title']) ?>"
                                class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                src="<?= htmlspecialchars(getImageUrl($article['featured_image'], 'https://placehold.co/800x500/e5e2e1/5b403f?text=Tin+Tuc')) ?>" />
                        </div>
                        <div class="p-5 flex flex-col flex-grow">
                            <div class="flex items-center gap-2 mb-3">
                                <?php if (!empty($article['category'])): ?>
                                    <span class="bg-surface-container px-2 py-1 rounded text-label-sm font-label-sm text-on-surface-variant uppercase tracking-wider">
                                        <?= htmlspecialchars($article['category']) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-label-sm text-outline">
                                    <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                                </span>
                            </div>
                            <h3 class="font-headline-sm text-headline-sm text-on-background mb-3 text-truncate-2 group-hover/card:text-axeron-red transition-colors">
                                <?= htmlspecialchars($article['title']) ?>
                            </h3>
                            <p class="font-body-md text-body-md text-on-surface-variant text-truncate-3 mt-auto">
                                <?= htmlspecialchars($article['excerpt']) ?>
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Nút điều hướng thủ công -->
                <button class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-axeron-red p-2 rounded-full shadow-md z-10 hidden md:block opacity-0 group-hover:opacity-100 transition-opacity" id="news-prev-btn">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
                <button class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-axeron-red p-2 rounded-full shadow-md z-10 hidden md:block opacity-0 group-hover:opacity-100 transition-opacity" id="news-next-btn">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </section>

        <!-- Info Sections -->
        <section class="relative py-20 md:py-24 bg-center bg-cover" style="background-image: url('https://images.unsplash.com/photo-1508344928928-7137b29de216?q=80&w=1920&auto=format&fit=crop');">
            <!-- Lớp phủ trắng mờ để nổi bật nội dung chữ -->
            <div class="absolute inset-0 bg-[#fcf9f8]/90 backdrop-blur-[2px]"></div>
            
            <div class="relative z-10 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-start">
                    <!-- Giới thiệu -->
                    <div class="text-left">
                        <h2 class="text-2xl md:text-3xl font-bold mb-3 uppercase text-axeron-red">GIỚI THIỆU</h2>
                        <div class="w-16 h-[3px] bg-axeron-red mb-6"></div>
                        <p class="text-[#4a4a4a] leading-[1.8] mb-5 text-[15px] md:text-base">
                            Công ty Cổ phần Axeron tự hào là tập đoàn hàng đầu Việt Nam với hơn 32 năm kinh nghiệm trong lĩnh vực sản xuất, phân phối và xuất nhập khẩu trang phục thể thao.
                        </p>
                        <p class="text-[#4a4a4a] leading-[1.8] mb-8 text-[15px] md:text-base">
                            Axeron hiện có chi nhánh tại Hà Nội, Vinh và TP.HCM với hệ thống phân phối hơn 800 đại lý trên toàn quốc.
                        </p>
                        <a href="<?= BASE_URL ?>/pages/about.php" class="bg-[#222222] hover:bg-axeron-red text-white px-8 py-3 rounded-sm font-semibold transition-colors duration-300 inline-block shadow-sm">
                            Xem thêm
                        </a>
                    </div>
                    <!-- Các dự án -->
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold mb-3 uppercase text-axeron-red">CÁC DỰ ÁN</h2>
                        <div class="w-16 h-[3px] bg-axeron-red mb-6"></div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                            <a href="<?= BASE_URL ?>/blog/stadiums.php" class="relative rounded-sm overflow-hidden group cursor-pointer aspect-[3/2] block shadow-sm">
                                <img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                <div class="absolute inset-0 bg-black/60 group-hover:bg-black/30 transition-colors duration-500"></div>
                                <div class="absolute inset-0 flex items-center justify-center z-10 px-4">
                                    <h3 class="text-[18px] md:text-xl font-extrabold text-white text-center uppercase tracking-wider drop-shadow-md">SÂN VẬN ĐỘNG</h3>
                                </div>
                            </a>
                            <a href="<?= BASE_URL ?>/blog/arenas.php" class="relative rounded-sm overflow-hidden group cursor-pointer aspect-[3/2] block shadow-sm">
                                <img src="https://images.unsplash.com/photo-1547347298-4074fc3086f0?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                <div class="absolute inset-0 bg-black/60 group-hover:bg-black/30 transition-colors duration-500"></div>
                                <div class="absolute inset-0 flex items-center justify-center z-10 px-4">
                                    <h3 class="text-[18px] md:text-xl font-extrabold text-white text-center uppercase tracking-wider drop-shadow-md">NHÀ THI ĐẤU</h3>
                                </div>
                            </a>
                            <a href="<?= BASE_URL ?>/blog/school-uniforms.php" class="relative rounded-sm overflow-hidden group cursor-pointer aspect-[3/2] block shadow-sm">
                                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                <div class="absolute inset-0 bg-black/60 group-hover:bg-black/30 transition-colors duration-500"></div>
                                <div class="absolute inset-0 flex items-center justify-center z-10 px-4">
                                    <h3 class="text-[18px] md:text-xl font-extrabold text-white text-center uppercase tracking-wider drop-shadow-md">ĐỒNG PHỤC HỌC SINH</h3>
                                </div>
                            </a>
                            <a href="<?= BASE_URL ?>/blog/gym-equipment.php" class="relative rounded-sm overflow-hidden group cursor-pointer aspect-[3/2] block shadow-sm">
                                <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                                <div class="absolute inset-0 bg-black/60 group-hover:bg-black/30 transition-colors duration-500"></div>
                                <div class="absolute inset-0 flex items-center justify-center z-10 px-4">
                                    <h3 class="text-[18px] md:text-xl font-extrabold text-white text-center uppercase tracking-wider drop-shadow-md">THIẾT BỊ PHÒNG TẬP</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script src="<?= BASE_URL ?>/js/main.js?v=<?= time() ?>"></script>
    <script>
        console.log("Checking slider...");
        console.log("Total slides found:", document.querySelectorAll('[id^="slide-"]').length);
        setInterval(() => {
            console.log("Is window.nextSlide defined?", typeof window.nextSlide);
            console.log("Current slide opacity classes:", 
                document.getElementById('slide-1')?.className.includes('opacity-100'),
                document.getElementById('slide-2')?.className.includes('opacity-100')
            );
        }, 4000);
    </script>

    <script>


        // Tab logic for Giày thể thao
        document.addEventListener('DOMContentLoaded', function() {
            const shoeTabs = document.querySelectorAll('.shoe-tab-btn');
            const shoeItems = document.querySelectorAll('.shoe-item');
            const emptyMsg = document.getElementById('empty-shoes-msg');

            function showShoes(category) {
                let count = 0;
                shoeItems.forEach(item => {
                    if (item.classList.contains(category) && count < 10) {
                        item.classList.remove('hidden');
                        item.classList.add('flex');
                        count++;
                    } else {
                        item.classList.add('hidden');
                        item.classList.remove('flex');
                    }
                });
                
                if (count === 0 && emptyMsg) {
                    emptyMsg.classList.remove('hidden');
                } else if (emptyMsg) {
                    emptyMsg.classList.add('hidden');
                }
            }

            shoeTabs.forEach(btn => {
                btn.addEventListener('click', function() {
                    shoeTabs.forEach(b => {
                        b.classList.remove('active', 'text-axeron-red', 'font-bold', 'border-b-2', 'border-axeron-red', 'pb-1');
                        b.classList.add('text-on-surface-variant', 'font-medium');
                    });
                    this.classList.remove('text-on-surface-variant', 'font-medium');
                    this.classList.add('active', 'text-axeron-red', 'font-bold', 'border-b-2', 'border-axeron-red', 'pb-1');
                    
                    showShoes(this.dataset.target);
                });
            });

            if(shoeTabs.length > 0) {
                showShoes('giay-pickleball');
            }
        });

        // Auto Slider for News
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('news-slider');
            const prevBtn = document.getElementById('news-prev-btn');
            const nextBtn = document.getElementById('news-next-btn');
            if (!slider) return;

            let scrollAmount = 0;
            const scrollStep = 300; // Cuộn mỗi lần 300px
            const autoScrollInterval = 3000; // 3 giây trượt 1 lần
            let autoScrollTimer;

            function startAutoScroll() {
                autoScrollTimer = setInterval(() => {
                    if (slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 10) {
                        slider.scrollTo({ left: 0, behavior: 'smooth' }); // Reset
                    } else {
                        slider.scrollBy({ left: scrollStep, behavior: 'smooth' });
                    }
                }, autoScrollInterval);
            }

            function stopAutoScroll() {
                clearInterval(autoScrollTimer);
            }

            startAutoScroll();

            // Dừng cuộn khi hover
            slider.addEventListener('mouseenter', stopAutoScroll);
            slider.addEventListener('mouseleave', startAutoScroll);

            if (prevBtn && nextBtn) {
                prevBtn.addEventListener('click', () => {
                    slider.scrollBy({ left: -scrollStep, behavior: 'smooth' });
                });
                nextBtn.addEventListener('click', () => {
                    slider.scrollBy({ left: scrollStep, behavior: 'smooth' });
                });
                prevBtn.addEventListener('mouseenter', stopAutoScroll);
                nextBtn.addEventListener('mouseenter', stopAutoScroll);
                prevBtn.addEventListener('mouseleave', startAutoScroll);
                nextBtn.addEventListener('mouseleave', startAutoScroll);
            }
        });
    </script>
</body>
</html>


