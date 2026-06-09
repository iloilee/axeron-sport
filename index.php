<?php
/**
 * Trang chủ - Axeron Sports Shop
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';

$db = db();

// Load featured products
$featuredProducts = $db->select("
    SELECT
        p.product_id,
        p.product_name,
        p.slug,
        p.base_price,
        p.avg_rating,
        p.total_reviews,
        c.category_name,
        b.brand_name,
        pi.image_url
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
    WHERE p.is_visible = 1 AND p.is_featured = 1
    ORDER BY p.featured_sort_order ASC, p.updated_at DESC, p.created_at DESC
    LIMIT 10
");

// Load banners dynamically
$banners = $db->select("
    SELECT banner_id, title, subtitle, image_url, image_url_mobile, link_url, link_type, button_text
    FROM banners
    WHERE is_active = 1
    AND (start_date IS NULL OR start_date <= NOW())
    AND (end_date IS NULL OR end_date >= NOW())
    ORDER BY position ASC
    LIMIT 5
");

// Load articles/news dynamically
$articles = $db->select("
    SELECT article_id, title, slug, excerpt, featured_image, category, published_at
    FROM articles
    WHERE is_published = 1
    ORDER BY is_featured DESC, published_at DESC
    LIMIT 3
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Axeron - Dụng cụ thể thao chuyên nghiệp</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
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
                        background: "#fcf9f8",
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
                    fontSize: {
                        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                        "headline-lg-mobile": ["24px", { lineHeight: "32px", fontWeight: "700" }],
                        "label-sm": ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "800" }],
                        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                        "headline-md": ["24px", { lineHeight: "32px", fontWeight: "600" }],
                        "headline-lg": ["32px", { lineHeight: "40px", fontWeight: "700" }],
                        "label-lg": ["14px", { lineHeight: "20px", fontWeight: "700" }]
                    }
                }
            }
        };
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: "FILL" 0, "wght" 400, "GRAD" 0, "opsz" 24; }
        .text-truncate-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased">
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- Banner Hero Slider -->
        <section class="relative w-full h-[600px] md:h-[700px] overflow-hidden group" id="hero-slider">
            <?php if (empty($banners)): ?>
            <!-- Fallback banner if no banners configured -->
            <div class="absolute inset-0">
                <img alt="Axeron Vortex Collection" class="w-full h-full object-cover"
                    src="https://placehold.co/1920x800/BE1E2D/ffffff?text=AXERON+SPORT"/>
            </div>
            <?php else: ?>
            <?php foreach ($banners as $index => $banner): ?>
            <div class="absolute inset-0 transition-opacity duration-1000 ease-in-out <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>"
                 id="slide-<?= $index + 1 ?>" <?php if ($banner['link_url']): ?>onclick="window.location.href='<?= htmlspecialchars($banner['link_url']) ?>'" style="cursor: pointer;"<?php endif; ?>>
                <img alt="<?= htmlspecialchars($banner['title']) ?>" class="w-full h-full object-cover"
                    src="<?= htmlspecialchars(getImageUrl($banner['image_url'])) ?>"/>
                <?php if ($banner['title'] || $banner['subtitle']): ?>
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="absolute inset-0 flex items-center">
                    <div class="max-w-container-max mx-auto px-margin-desktop w-full">
                        <?php if ($banner['title']): ?>
                        <h1 class="text-white text-4xl md:text-6xl font-bold mb-4"><?= htmlspecialchars($banner['title']) ?></h1>
                        <?php endif; ?>
                        <?php if ($banner['subtitle']): ?>
                        <p class="text-white text-xl md:text-2xl mb-6"><?= htmlspecialchars($banner['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($banner['button_text'] && $banner['link_url']): ?>
                        <a href="<?= htmlspecialchars($banner['link_url']) ?>"
                           class="inline-block bg-white text-axeron-red px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition-colors">
                            <?= htmlspecialchars($banner['button_text']) ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php if (count($banners) > 1): ?>
            <button class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity z-10" onclick="prevSlide()">
                <span class="material-symbols-outlined text-3xl">chevron_left</span>
            </button>
            <button class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/20 hover:bg-white/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity z-10" onclick="nextSlide()">
                <span class="material-symbols-outlined text-3xl">chevron_right</span>
            </button>
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                <?php foreach ($banners as $index => $banner): ?>
                <button class="slider-dot w-8 h-3 rounded-full transition-all <?= $index === 0 ? 'bg-white' : 'bg-white/50' ?>"
                        id="dot-<?= $index + 1 ?>" onclick="goToSlide(<?= $index + 1 ?>)"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>

        <!-- Product Categories Bento Grid -->
        <section class="max-w-container-max mx-auto px-margin-desktop py-12 md:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-gutter">
                <a class="relative h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=ao-polo">
                    <img alt="Axeron Polo Shirt Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        src="https://lh3.googleusercontent.com/aida/ADBb0ujuRrW5g32r9sw8f5z2wAVDWrCiPpNsJt5pvZivUVOvnVnS-9zBhh9CHA_1JNdJ7j8wmH8hGFPQdgdZ4yKBr6xRW3x7RbZuevOmNa7peEz1Jd7RiMX44nC0oRWYSplrreUKzzI4X2NKzrUH3emUg_qU3eiVLxXYvgDOW_g-kRtXfN4951IB2cqWrEYCmvtSnjHIspHgsRONHfgziMUY39rYQOd7GgTX_wInD-8LK0fNnovBRMyYcIlrIEo4"/>
                </a>
                <a class="relative h-[250px] md:h-[300px] rounded-xl overflow-hidden group" href="<?= BASE_URL ?>/shop/product-catalog.php?category=giay-pickleball">
                    <img alt="Axeron Pickleball Shoes Collection" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        src="https://lh3.googleusercontent.com/aida/ADBb0ui6G5OTndCGIG-FMQxRAvW-YT4W_YJhsNr8QoYtC5rKSuRBdndhDqM9KAgbCXA6h32GMwVqBBfhhlqE1HWNkktZG585n1YDv45hkgFzqwt7dpDlAqMRw-UI5grIIUEY6OtPEToaMWeaIygVEKOCrPEwCR5womaFhyND6eSgCU9EtfoOMPwq4tKo_amzf7IUwXQwF30pI0mfwbI1Amp7PyNImqV5lGYDzfWAxh3FF5grwWaZwD84OMhh0kSM"/>
                </a>
            </div>
        </section>

        <!-- Section: Sản phẩm nổi bật -->
        <section class="max-w-container-max mx-auto px-margin-desktop py-12">
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
                    <div class="aspect-square bg-surface-container-low p-4 relative overflow-hidden flex items-center justify-center">
                        <img alt="<?= htmlspecialchars($product['product_name']) ?>"
                            class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-500"
                            src="<?= htmlspecialchars(getImageUrl($product['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($product['product_name'], 0, 20)))) ?>"/>
                    </div>
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="font-label-lg text-label-lg text-on-background mb-2 text-truncate-2 group-hover:text-axeron-red transition-colors">
                            <?= htmlspecialchars($product['product_name']) ?>
                        </h3>
                        <div class="mt-auto">
                            <p class="font-headline-md text-body-lg text-axeron-red font-bold">
                                <?= formatPrice($product['base_price']) ?>
                            </p>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Latest News / Articles -->
        <section class="max-w-container-max mx-auto px-margin-desktop py-16 md:py-24">
            <div class="flex justify-between items-end mb-12">
                <div class="flex flex-col">
                    <span class="text-axeron-red font-label-lg uppercase tracking-widest mb-2">Cập nhật xu hướng</span>
                    <h2 class="font-headline-lg text-headline-lg uppercase">Tin Tức Mới Nhất</h2>
                </div>
                <a class="font-label-lg text-on-surface-variant hover:text-axeron-red flex items-center gap-1" href="<?= BASE_URL ?>/blog/news.php">
                    Xem toàn bộ tin <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($articles as $article): ?>
                <article class="group cursor-pointer" onclick="window.location.href='<?= BASE_URL ?>/blog/news.php?slug=<?= htmlspecialchars($article['slug']) ?>'">
                    <div class="aspect-[16/10] overflow-hidden rounded-xl mb-6 bg-surface-container">
                        <img alt="<?= htmlspecialchars($article['title']) ?>"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                             src="<?= htmlspecialchars(getImageUrl($article['featured_image'], 'https://placehold.co/800x500/e5e2e1/5b403f?text=Tin+Tuc')) ?>"/>
                    </div>
                    <div class="flex items-center gap-3 mb-3 text-label-sm text-on-surface-variant uppercase tracking-wider">
                        <span><?= htmlspecialchars(ucfirst($article['category'])) ?></span>
                        <span class="w-1 h-1 bg-surface-dim rounded-full"></span>
                        <span><?= $article['published_at'] ? date('d THÁNG m, Y', strtotime($article['published_at'])) : '' ?></span>
                    </div>
                    <h3 class="font-headline-md text-xl mb-4 group-hover:text-axeron-red transition-colors">
                        <?= htmlspecialchars($article['title']) ?>
                    </h3>
                    <p class="text-on-surface-variant line-clamp-3 text-sm"><?= htmlspecialchars($article['excerpt'] ?: '') ?></p>
                </article>
                <?php endforeach; ?>
                <?php if (empty($articles)): ?>
                <!-- Fallback if no articles -->
                <article class="group">
                    <div class="aspect-[16/10] overflow-hidden rounded-xl mb-6 bg-surface-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-5xl text-gray-400">article</span>
                    </div>
                    <div class="text-label-sm text-on-surface-variant uppercase tracking-wider mb-3">Blog</div>
                    <h3 class="font-headline-md text-xl mb-4 text-gray-400">Chưa có bài viết nào</h3>
                    <p class="text-on-surface-variant text-sm">Hãy thêm bài viết từ Admin Panel</p>
                </article>
                <?php endif; ?>
            </div>
        </section>

        <!-- Info Sections -->
        <section class="bg-white py-16 md:py-20">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                    <div>
                        <span class="text-axeron-red uppercase tracking-[4px] text-label-sm font-bold">About Axeron</span>
                        <h2 class="text-headline-lg md:text-display-lg font-headline-lg mt-3 mb-6 uppercase text-on-background">GIỚI THIỆU</h2>
                        <p class="text-on-surface-variant leading-relaxed mb-5 text-body-lg">
                            Công ty Cổ phần Axeron tự hào là tập đoàn hàng đầu Việt Nam với hơn 32 năm kinh nghiệm trong lĩnh vực sản xuất, phân phối và xuất nhập khẩu trang phục thể thao.
                        </p>
                        <p class="text-on-surface-variant leading-relaxed mb-5 text-body-lg">
                            Axeron hiện có chi nhánh tại Hà Nội, Vinh và TP.HCM với hệ thống phân phối hơn 800 đại lý trên toàn quốc.
                        </p>
                        <a href="<?= BASE_URL ?>/about.php" class="bg-axeron-red hover:bg-red-700 text-white px-6 py-3 rounded-lg font-bold transition-colors inline-block">
                            XEM THÊM
                        </a>
                    </div>
                    <div>
                        <span class="text-axeron-blue uppercase tracking-[4px] text-label-sm font-bold">Projects</span>
                        <h2 class="text-headline-lg md:text-display-lg font-headline-lg mt-3 mb-6 uppercase text-on-background">CÁC DỰ ÁN</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="relative rounded-2xl overflow-hidden group cursor-pointer aspect-video">
                                <img src="https://images.unsplash.com/photo-1517466787929-bc90951d0974?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"/>
                                <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition-colors"></div>
                                <div class="absolute bottom-4 left-4 z-10"><h3 class="text-lg font-bold text-white">SÂN VẬN ĐỘNG</h3></div>
                            </div>
                            <div class="relative rounded-2xl overflow-hidden group cursor-pointer aspect-video">
                                <img src="https://images.unsplash.com/photo-1547347298-4074fc3086f0?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"/>
                                <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition-colors"></div>
                                <div class="absolute bottom-4 left-4 z-10"><h3 class="text-lg font-bold text-white">NHÀ THI ĐẤU</h3></div>
                            </div>
                            <div class="relative rounded-2xl overflow-hidden group cursor-pointer aspect-video">
                                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"/>
                                <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition-colors"></div>
                                <div class="absolute bottom-4 left-4 z-10"><h3 class="text-lg font-bold text-white">ĐỒNG PHỤC HỌC SINH</h3></div>
                            </div>
                            <div class="relative rounded-2xl overflow-hidden group cursor-pointer aspect-video">
                                <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"/>
                                <div class="absolute inset-0 bg-black/50 group-hover:bg-black/30 transition-colors"></div>
                                <div class="absolute bottom-4 left-4 z-10"><h3 class="text-lg font-bold text-white">THIẾT BỊ PHÒNG TẬP</h3></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <script>
        // Dynamic slider logic
        const totalSlides = <?= count($banners) ?: 1 ?>;
        let currentSlide = 1;

        function updateSlider() {
            for (let i = 1; i <= totalSlides; i++) {
                const slide = document.getElementById('slide-' + i);
                const dot = document.getElementById('dot-' + i);
                if (slide) {
                    slide.classList.toggle('opacity-0', currentSlide !== i);
                    slide.classList.toggle('opacity-100', currentSlide === i);
                }
                if (dot) {
                    dot.className = 'slider-dot ' + (currentSlide === i
                        ? 'w-8 h-3 rounded-full bg-white transition-all'
                        : 'w-3 h-3 rounded-full bg-white/50 transition-all hover:bg-white/80');
                }
            }
        }

        function nextSlide() {
            if (totalSlides > 1) {
                currentSlide = currentSlide >= totalSlides ? 1 : currentSlide + 1;
                updateSlider();
            }
        }
        function prevSlide() {
            if (totalSlides > 1) {
                currentSlide = currentSlide <= 1 ? totalSlides : currentSlide - 1;
                updateSlider();
            }
        }
        function goToSlide(idx) {
            if (totalSlides > 1) {
                currentSlide = idx;
                updateSlider();
            }
        }

        // Auto-slide if multiple banners
        <?php if (count($banners) > 1): ?>
        setInterval(nextSlide, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
