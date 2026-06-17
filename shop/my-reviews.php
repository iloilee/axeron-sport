<?php
/**
 * My Reviews Page
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Require login
requireLogin();

$userId = getUserId();
$db = db();

// Get user reviews
$reviews = $db->select("
    SELECT 
        r.review_id,
        r.rating,
        r.comment,
        r.status,
        r.created_at,
        p.product_id,
        p.product_name,
        p.slug,
        pi.image_url
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE r.user_id = ? AND r.is_deleted = 0
    ORDER BY r.created_at DESC
", [$userId]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đánh Giá Của Tôi - Axeron Sport</title>
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
                        "on-background": "#1b1c1c",
                        "inverse-surface": "#303030",
                        "text-dark": "#212121",
                        "tertiary-container": "#006a85",
                        "background": "#fcf9f8",
                        "on-primary-fixed-variant": "#930019",
                        "inverse-primary": "#ffb3b0",
                        "secondary-fixed-dim": "#b0c6ff",
                        "error-container": "#ffdad6",
                        "outline-variant": "#e3bebb",
                        "tertiary-fixed": "#baeaff",
                        "on-secondary": "#ffffff",
                        "secondary-fixed": "#d9e2ff",
                        "on-primary-fixed": "#410006",
                        "surface-container": "#f0eded",
                        error: "#ba1a1a",
                        "axeron-red": "#BE1E2D",
                        "on-tertiary": "#ffffff",
                        "surface-dim": "#dcd9d9",
                        "on-primary-container": "#ffd3d1",
                        "secondary-container": "#0f6df3",
                        "surface-tint": "#b91a2a",
                        primary: "#98001b",
                        "surface-gray": "#F5F5F5",
                        "surface-bright": "#fcf9f8",
                        "surface-container-highest": "#e5e2e1",
                        "on-surface": "#1b1c1c",
                        white: "#FFFFFF",
                        tertiary: "#005066",
                        "surface-container-high": "#eae7e7",
                        "on-error-container": "#93000a",
                        "primary-container": "#be1e2d",
                        "primary-fixed": "#ffdad8",
                        "surface-container-lowest": "#ffffff",
                        "inverse-on-surface": "#f3f0ef",
                        "on-tertiary-container": "#abe6ff",
                        "surface-variant": "#e5e2e1",
                        "on-secondary-container": "#fefcff",
                        secondary: "#0056c5",
                        outline: "#8f6f6e",
                        "axeron-blue": "#2979FF",
                        "tertiary-fixed-dim": "#85d1ef",
                        surface: "#fcf9f8",
                        "on-secondary-fixed-variant": "#00429b",
                        "on-tertiary-fixed": "#001f29",
                        "on-tertiary-fixed-variant": "#004d62",
                        "surface-container-low": "#f6f3f2",
                        "on-secondary-fixed": "#001945",
                        "primary-fixed-dim": "#ffb3b0",
                        "on-surface-variant": "#5b403f",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff"
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
    </style>
</head>
<body class="bg-surface text-on-surface font-body-md antialiased flex flex-col min-h-screen">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="flex-grow w-full max-w-[1000px] mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <div class="mb-8 border-b border-surface-variant pb-4">
            <h1 class="text-xl md:text-2xl font-bold uppercase text-text-dark m-0">Đánh giá của tôi</h1>
            <p class="text-on-surface-variant mt-2">Quản lý các đánh giá bạn đã chia sẻ về sản phẩm.</p>
        </div>

        <?php if (empty($reviews)): ?>
        <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high p-8 md:p-12 text-center">
            <span class="material-symbols-outlined text-6xl text-on-surface-variant mb-4">rate_review</span>
            <h2 class="font-headline-md text-headline-md text-on-surface mb-4">Bạn chưa có đánh giá nào</h2>
            <p class="text-on-surface-variant mb-8">Hãy trải nghiệm sản phẩm và chia sẻ cảm nhận của bạn nhé.</p>
            <a href="<?= BASE_URL ?>/shop/order-history.php" class="inline-flex items-center gap-2 bg-axeron-red text-white px-6 py-3 rounded-lg font-label-lg hover:bg-primary transition-colors">
                Xem đơn hàng đã mua
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($reviews as $review): ?>
            <div class="bg-surface-container-lowest rounded-xl border border-surface-container-high p-6 flex flex-col md:flex-row gap-6 shadow-sm hover:shadow-md transition-shadow">
                <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($review['slug']) ?>" class="block flex-shrink-0">
                    <img src="<?= htmlspecialchars(strpos($review['image_url'], 'http') === 0 ? $review['image_url'] : BASE_URL . $review['image_url']) ?>" alt="<?= htmlspecialchars($review['product_name']) ?>" class="w-24 h-24 object-cover rounded-lg border border-outline-variant">
                </a>
                
                <div class="flex-grow">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2 mb-2">
                        <div>
                            <a href="<?= BASE_URL ?>/shop/product-detail.php?slug=<?= htmlspecialchars($review['slug']) ?>" class="text-lg font-bold text-on-surface hover:text-axeron-red transition-colors line-clamp-2">
                                <?= htmlspecialchars($review['product_name']) ?>
                            </a>
                            <div class="text-sm text-on-surface-variant mt-1"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></div>
                        </div>
                        
                        <div>
                            <?php
                            $statusClass = match($review['status']) {
                                'approved' => 'bg-green-100 text-green-800',
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'rejected' => 'bg-red-100 text-red-800',
                                'hidden' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                            $statusText = match($review['status']) {
                                'approved' => 'Đã hiển thị',
                                'pending' => 'Chờ duyệt',
                                'rejected' => 'Từ chối',
                                'hidden' => 'Đã ẩn',
                                default => $review['status']
                            };
                            ?>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $statusClass ?> whitespace-nowrap inline-flex items-center gap-1 border border-transparent">
                                <?php if($review['status'] === 'approved'): ?>
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                <?php elseif($review['status'] === 'pending'): ?>
                                    <span class="material-symbols-outlined text-[14px]">pending</span>
                                <?php elseif($review['status'] === 'rejected'): ?>
                                    <span class="material-symbols-outlined text-[14px]">cancel</span>
                                <?php endif; ?>
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 mb-3 text-[#FFC107]">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="material-symbols-outlined text-[20px] <?= $i <= $review['rating'] ? 'fill-current' : 'text-gray-300' ?>" style="<?= $i <= $review['rating'] ? 'font-variation-settings: \'FILL\' 1;' : '' ?>">star</span>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="text-on-surface text-base whitespace-pre-line leading-relaxed"><?= htmlspecialchars($review['comment']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
