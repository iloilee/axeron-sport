<?php
/**
 * Wishlist Page - Sản phẩm yêu thích
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin(); // Bắt buộc đăng nhập

$db = db();
$userId = getUserId();

// Get wishlist products
$products = $db->select("
    SELECT 
        p.product_id,
        p.product_name,
        p.slug,
        p.base_price,
        p.is_featured,
        p.category_id,
        p.avg_rating,
        p.total_reviews,
        b.brand_name,
        pi.image_url,
        uw.created_at as favorited_at
    FROM user_wishlists uw
    JOIN products p ON uw.product_id = p.product_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE uw.user_id = ?
    ORDER BY uw.created_at DESC
", [$userId]);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <title>Sản phẩm yêu thích - Axeron</title>
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
                    fontFamily: { "body-lg": ["Noto Sans", "sans-serif"], "headline-lg-mobile": ["Montserrat", "sans-serif"], "label-sm": ["Noto Sans", "sans-serif"], "display-lg": ["Montserrat", "sans-serif"], "body-md": ["Noto Sans", "sans-serif"], "headline-md": ["Montserrat", "sans-serif"], "headline-lg": ["Montserrat", "sans-serif"], "label-lg": ["Noto Sans", "sans-serif"] }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8">
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark text-center">Sản Phẩm Yêu Thích</h1>

        <?php if (empty($products)): ?>
            <div class="text-center py-16 bg-surface-container-lowest rounded-xl border border-surface-variant">
                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4" style="font-variation-settings: 'FILL' 1;">favorite</span>
                <h3 class="font-headline-md text-xl text-on-surface mb-2">Danh sách yêu thích trống</h3>
                <p class="text-on-surface-variant mb-6">Bạn chưa có sản phẩm nào trong danh sách yêu thích.</p>
                <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-block bg-axeron-red text-white font-label-lg px-8 py-3 rounded hover:bg-primary transition-colors uppercase">
                    Khám phá ngay
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-gutter mb-12">
                <?php foreach ($products as $product): ?>
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($product['slug']) ?>"
                    data-aos="fade-up"
                    class="group bg-surface-container-lowest rounded-lg border border-outline-variant overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col relative">
                    <div class="relative w-full aspect-square overflow-hidden bg-surface-container-low flex items-center justify-center">
                        <?php if (!empty($product['is_featured'])): ?>
                        <span class="absolute top-2 left-2 bg-gradient-to-r from-orange-500 to-red-600 shadow-[0_0_10px_rgba(239,68,68,0.5)] text-white font-label-sm text-label-sm px-3 py-1 rounded-full uppercase tracking-wider z-10">Nổi bật</span>
                        <?php endif; ?>
                         <img alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                            src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 15)))) ?>"/>
                        <button class="absolute top-2 right-2 p-2 bg-white/80 rounded-full hover:text-axeron-red hover:bg-white transition-colors opacity-100 z-10"
                            onclick="event.preventDefault(); event.stopPropagation(); addToWishlist(<?= $product['product_id'] ?>, this)">
                            <span class="material-symbols-outlined text-axeron-red" style="font-variation-settings: 'FILL' 1;">favorite</span>
                        </button>
                        
                        <!-- Quick Add Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out z-20 flex justify-center">
                            <button class="bg-on-surface/90 backdrop-blur-sm hover:bg-axeron-red text-white font-label-md text-label-md px-6 py-2.5 rounded-full shadow-lg transition-all flex items-center gap-2 hover:scale-105 w-[90%] justify-center" onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $product['product_id'] ?>, 0, 1)">
                                <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                                Mua Ngay
                            </button>
                        </div>
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
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script src="<?= BASE_URL ?>/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
