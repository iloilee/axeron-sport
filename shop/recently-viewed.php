<?php
/**
 * Recently Viewed Page - Sản phẩm đã xem
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireLogin(); // Bắt buộc đăng nhập

$db = db();
$userId = getUserId();

// Get recently viewed products (max 24 items)
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
        urv.viewed_at
    FROM user_recently_viewed urv
    JOIN products p ON urv.product_id = p.product_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE urv.user_id = ?
    ORDER BY urv.viewed_at DESC
    LIMIT 24
", [$userId]);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <title>Sản phẩm đã xem - Axeron</title>
    <link rel="icon" type="image/jpeg" href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/assets/images/logo-axeron.jpg" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased min-h-screen flex flex-col">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8">
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg font-bold mb-8 uppercase text-text-dark text-center">Sản Phẩm Đã Xem</h1>

        <?php if (empty($products)): ?>
            <div class="text-center py-16 bg-surface-container-lowest rounded-xl border border-surface-variant">
                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">history</span>
                <h3 class="font-headline-md text-xl text-on-surface mb-2">Lịch sử trống</h3>
                <p class="text-on-surface-variant mb-6">Bạn chưa xem sản phẩm nào gần đây.</p>
                <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="inline-block bg-axeron-red text-white font-label-lg px-8 py-3 rounded hover:bg-primary transition-colors uppercase">
                    Khám phá ngay
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-gutter mb-12">
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
    <script src="<?= BASE_URL ?>/js/main.min.js?v=<?= filemtime(__DIR__ . '/../js/main.min.js') ?>"></script>
</body>
</html>
