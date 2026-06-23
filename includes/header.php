<?php
/**
 * Header Template - Axeron Sports Shop
 * Mega Menu Navigation with 3-level category hierarchy
 */

// Lấy cart count từ session
$cartCount = getCartCount();

// Xác định trang hiện tại để active nav
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentCategorySlug = $_GET['category'] ?? '';

// Load danh mục 3 cấp từ database (is_visible = 1)
$_headerDb = db();

// Cấp 1: Danh mục gốc
$_navLevel1 = $_headerDb->select("
    SELECT category_id, category_name, slug
    FROM categories
    WHERE parent_id IS NULL AND is_visible = 1 AND category_name != 'TEST'
    ORDER BY sort_order, category_id
    LIMIT 8
");

// Cấp 2 & 3: Load tất cả danh mục con (is_visible = 1)
$_navLevel2Map = []; // parent_id => [children]
$_navLevel3Map = []; // parent_id => [children]
$_allSlugs = []; // Để check active state

foreach ($_navLevel1 as $_l1) {
    // Load cấp 2 (con trực tiếp của cấp 1)
    $_level2 = $_headerDb->select("
        SELECT category_id, category_name, slug
        FROM categories
        WHERE parent_id = ? AND is_visible = 1
        ORDER BY sort_order, category_id
    ", [$_l1['category_id']]);

    if (!empty($_level2)) {
        $_navLevel2Map[$_l1['category_id']] = $_level2;

        // Load cấp 3 (con của cấp 2)
        foreach ($_level2 as $_l2) {
            $_level3 = $_headerDb->select("
                SELECT category_id, category_name, slug
                FROM categories
                WHERE parent_id = ? AND is_visible = 1
                ORDER BY sort_order, category_id
            ", [$_l2['category_id']]);

            if (!empty($_level3)) {
                $_navLevel3Map[$_l2['category_id']] = $_level3;
            }

            // Collect all slugs for active detection
            $_allSlugs[$_l2['slug']] = $_l1['category_id'];
            foreach ($_level3 as $_l3) {
                $_allSlugs[$_l3['slug']] = $_l1['category_id'];
            }
        }
    }

    $_allSlugs[$_l1['slug']] = $_l1['category_id'];
}

// Detect which root category is active
$_activeRootId = $_allSlugs[$currentCategorySlug] ?? null;

// Settings for Logo and Name
if (!isset($settings)) {
    $_headerDb = db();
    $_headerRaw = $_headerDb->select("SELECT setting_key, setting_value FROM site_settings WHERE is_public = 1");
    $settings = [];
    foreach ($_headerRaw as $s) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }
}
$siteLogoUrl = $settings['site_logo'] ?? '/assets/images/logo-axeron.jpg';
if (strpos($siteLogoUrl, 'http') !== 0 && !empty($siteLogoUrl)) {
    $siteLogoUrl = (defined('BASE_URL') ? BASE_URL : '') . (strpos($siteLogoUrl, '/') === 0 ? '' : '/') . $siteLogoUrl;
}
$siteNameDisplay = $settings['site_name'] ?? 'Axeron';
?>

<!-- Mega Menu Styles -->
<style>
    /* Mega Menu Core */
    .mega-nav-item {
        position: static;
    }
    .mega-nav-link {
        position: relative;
        padding: 8px 0;
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    .mega-nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: #BE1E2D;
        transition: width 0.3s ease;
    }
    .mega-nav-link:hover::after,
    .mega-nav-link.active::after {
        width: 100%;
    }
    .mega-nav-link .arrow-icon {
        transition: transform 0.2s ease;
        font-size: 16px;
    }
    .mega-nav-item.mega-open .mega-nav-link .arrow-icon {
        transform: rotate(180deg);
    }
    .mega-nav-item.mega-open .mega-nav-link::after {
        width: 100%;
    }

    /* Mega Panel */
    .mega-panel {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        width: 100%;
        background: #fff;
        border-top: 2px solid #BE1E2D;
        box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 4px 20px rgba(0,0,0,0.06);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease;
        z-index: 100;
        pointer-events: none;
    }
    /* Invisible bridge above the panel to maintain hover continuity */
    .mega-panel::before {
        content: '';
        position: absolute;
        top: -30px;
        left: 0;
        right: 0;
        height: 30px;
        background: transparent;
    }
    /* JS-controlled open state instead of CSS :hover */
    .mega-nav-item.mega-open .mega-panel {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
    }

    /* Mega Panel Columns */
    .mega-columns {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0;
        max-width: 1200px;
        margin: 0 auto;
        padding: 28px 24px 32px;
    }

    /* Column */
    .mega-column {
        padding: 0 16px;
        border-right: 1px solid #f0eded;
    }
    .mega-column:last-child { border-right: none; }

    /* Column Header (Level 2) */
    .mega-col-header {
        display: block;
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #1b1c1c;
        padding-bottom: 10px;
        margin-bottom: 8px;
        border-bottom: 2px solid #BE1E2D;
        transition: color 0.2s;
    }
    .mega-col-header:hover { color: #BE1E2D; }

    /* Column Link (Level 3) */
    .mega-col-link {
        display: block;
        font-family: 'Noto Sans', sans-serif;
        font-size: 13.5px;
        color: #5b403f;
        padding: 5px 0;
        transition: color 0.15s, padding-left 0.2s;
        line-height: 1.5;
    }
    .mega-col-link:hover {
        color: #BE1E2D;
        padding-left: 6px;
    }
    .mega-col-link.is-active {
        color: #BE1E2D;
        font-weight: 600;
    }

    /* View All link */
    .mega-view-all {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 13px;
        font-weight: 600;
        color: #BE1E2D;
        margin-top: 12px;
        padding: 6px 0;
        transition: gap 0.2s;
    }
    .mega-view-all:hover { gap: 8px; }
    .mega-view-all .material-symbols-outlined { font-size: 16px; }

    /* Overlay behind mega panel */
    .mega-overlay {
        position: fixed;
        inset: 0;
        top: 0;
        background: rgba(0,0,0,0.15);
        z-index: 40;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        pointer-events: none;
    }
    .mega-nav-item:hover ~ .mega-overlay,
    .mega-nav-item:hover .mega-overlay-trigger {
        opacity: 1;
        visibility: visible;
    }

    /* Mobile menu */
    .mobile-menu-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 60;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    .mobile-menu-backdrop.active {
        opacity: 1;
        visibility: visible;
    }
    .mobile-menu-panel {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 300px;
        max-width: 85vw;
        background: #fff;
        z-index: 70;
        transform: translateX(-100%);
        transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        overflow-y: auto;
    }
    .mobile-menu-panel.active {
        transform: translateX(0);
    }
    .mobile-submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    .mobile-submenu.open {
        max-height: 1000px;
    }

    /* Marquee Banner */
    .marquee-container {
        overflow: hidden;
        white-space: nowrap;
        width: 100%;
        position: relative;
    }
    .marquee-content {
        display: inline-block;
        padding-left: 100%;
        animation: marquee 20s linear infinite;
    }
    /* Pause animation on hover */
    .marquee-container:hover .marquee-content {
        animation-play-state: paused;
    }
    @keyframes marquee {
        0%   { transform: translate(0, 0); }
        100% { transform: translate(-100%, 0); }
    }

</style>
<?php include __DIR__ . '/dark-mode.php'; ?>

<!-- Page Progress Bar -->
<div id="page-progress-bar" class="fixed top-0 left-0 h-[2px] bg-axeron-red z-[100] transition-all duration-300 ease-out" style="width: 0%"></div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const progressBar = document.getElementById('page-progress-bar');
        if (progressBar) {
            progressBar.style.width = '100%';
            setTimeout(() => {
                progressBar.style.opacity = '0';
            }, 300);
        }
    });
    window.addEventListener('beforeunload', () => {
        const progressBar = document.getElementById('page-progress-bar');
        if (progressBar) {
            progressBar.style.opacity = '1';
            progressBar.style.width = '30%';
            setTimeout(() => {
                progressBar.style.width = '80%';
            }, 100);
        }
    });
</script>

<!-- TopAppBar Component -->
<header class="bg-surface dark:bg-on-background sticky top-0 z-50 border-b border-outline-variant dark:border-outline" id="main-header">
    <div class="flex justify-between items-center w-full max-w-[1400px] px-4 lg:px-8 py-5 mx-auto">
        <!-- Mobile Menu Toggle -->
        <button class="md:hidden p-2 -ml-2 hover:bg-surface-container rounded-lg transition-colors" onclick="toggleMobileMenu()" aria-label="Menu">
            <span class="material-symbols-outlined text-2xl text-on-surface">menu</span>
        </button>

        <!-- Brand Logo -->
        <a class="flex items-center gap-2 flex-shrink-0 mr-4 lg:mr-8 xl:mr-12" href="<?= BASE_URL ?>/">
            <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
            <span class="font-display-lg text-axeron-red uppercase tracking-tight text-xl md:text-2xl"><?= htmlspecialchars($siteNameDisplay) ?></span>
        </a>

        <!-- Navigation Links (Desktop) - Mega Menu -->
        <nav class="hidden lg:flex items-center gap-2 xl:gap-6 flex-1 justify-center" id="mega-nav">
            <?php foreach ($_navLevel1 as $_l1):
                $_isActive = ($_activeRootId === $_l1['category_id']);
                $_hasLevel2 = isset($_navLevel2Map[$_l1['category_id']]);
            ?>
            <div class="mega-nav-item">
                <a class="mega-nav-link font-label-lg text-label-lg uppercase whitespace-nowrap px-2
                          <?= $_isActive ? 'active text-axeron-red font-bold' : 'text-on-surface hover:text-axeron-red' ?> transition-colors duration-200"
                   href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l1['slug']) ?>"
                   title="<?= htmlspecialchars($_l1['category_name']) ?>">
                    <?= htmlspecialchars($_l1['category_name']) ?>
                    <?php if ($_hasLevel2): ?>
                    <span class="material-symbols-outlined arrow-icon text-gray-400">expand_more</span>
                    <?php endif; ?>
                </a>

                <?php if ($_hasLevel2): ?>
                <!-- Mega Panel -->
                <div class="mega-panel">
                    <div class="mega-columns">
                        <?php foreach ($_navLevel2Map[$_l1['category_id']] as $_l2):
                            $_hasLevel3 = isset($_navLevel3Map[$_l2['category_id']]);
                        ?>
                        <div class="mega-column">
                            <!-- Level 2: Column Header -->
                            <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l2['slug']) ?>"
                               class="mega-col-header">
                                <?= htmlspecialchars($_l2['category_name']) ?>
                            </a>

                            <?php if ($_hasLevel3): ?>
                            <!-- Level 3: Sub-links -->
                            <?php foreach ($_navLevel3Map[$_l2['category_id']] as $_l3): ?>
                            <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l3['slug']) ?>"
                               class="mega-col-link <?= $currentCategorySlug === $_l3['slug'] ? 'is-active' : '' ?>">
                                <?= htmlspecialchars($_l3['category_name']) ?>
                            </a>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- View all for this L2 -->
                            <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l2['slug']) ?>"
                               class="mega-view-all">
                                Xem tất cả
                                <span class="material-symbols-outlined">arrow_forward</span>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </nav>

        <!-- Trailing Actions -->
        <div class="flex items-center gap-3 text-axeron-red dark:text-primary-fixed flex-shrink-0">
            <!-- Search -->
            <form action="<?= BASE_URL ?>/shop/product-catalog.php" method="POST" enctype="multipart/form-data" class="relative hidden lg:block" id="desktop-search-form" autocomplete="off">
                <input
                    class="bg-surface-container rounded-full py-2 pl-4 pr-16 border border-outline-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue outline-none text-body-md text-on-surface font-body-md w-80 transition-all"
                    placeholder="Tìm kiếm..."
                    type="text"
                    id="search-input"
                    name="search"
                />
                
                <label class="absolute right-10 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-axeron-red cursor-pointer mb-0">
                    <span class="material-symbols-outlined text-xl">photo_camera</span>
                    <input type="file" name="search_image" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>

                <button type="submit" id="search-btn" class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-axeron-red cursor-pointer text-xl bg-transparent border-none p-0">
                    search
                </button>
                <!-- Autocomplete Dropdown -->
                <div id="desktop-search-dropdown" class="absolute left-0 right-0 top-full mt-2 bg-white border border-outline-variant rounded-xl shadow-lg z-50 hidden max-h-[400px] overflow-y-auto custom-scrollbar"></div>
            </form>

            <!-- Mobile Search -->
            <a href="javascript:void(0)" onclick="toggleMobileMenu(); setTimeout(() => document.querySelector('.mobile-menu-panel input[name=\'search\']').focus(), 300);" class="lg:hidden p-1 hover:text-axeron-red transition-colors">
                <span class="material-symbols-outlined text-2xl">search</span>
            </a>


            <!-- User Account -->
            <?php if (isLoggedIn()): ?>
                <div class="relative group flex items-center">
                    <button aria-label="Account" class="hover:text-axeron-red transition-colors duration-200 flex items-center gap-1.5">
                        <?php if (!empty($_SESSION['avatar_url'])): ?>
                            <img src="<?= (strpos($_SESSION['avatar_url'], 'http') === 0) ? htmlspecialchars($_SESSION['avatar_url']) : BASE_URL . htmlspecialchars($_SESSION['avatar_url']) ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-outline-variant" referrerpolicy="no-referrer">
                        <?php else: ?>
                            <span class="material-symbols-outlined text-[28px]">account_circle</span>
                        <?php endif; ?>
                        <span class="hidden xl:inline text-base font-medium max-w-[120px] truncate"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Tài khoản') ?></span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-outline-variant opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="<?= BASE_URL ?>/auth/account.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors rounded-t-lg">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">person</span>
                            Tài khoản
                        </a>
                        <a href="<?= BASE_URL ?>/shop/order-history.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">receipt_long</span>
                            Đơn hàng của tôi
                        </a>


                        <a href="<?= BASE_URL ?>/shop/wishlist.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">favorite</span>
                            Yêu thích
                        </a>
                        <a href="<?= BASE_URL ?>/shop/recently-viewed.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">history</span>
                            Sản phẩm đã xem
                        </a>
                        <?php if (isAdmin()): ?>
                        <hr class="border-outline-variant my-1">
                        <a href="<?= BASE_URL ?>/admin/admin.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">admin_panel_settings</span>
                            Trang quản trị
                        </a>
                        <?php endif; ?>
                        <hr class="border-outline-variant my-1">
                        
                        <!-- Dark Mode Toggle -->
                        <div class="px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors flex items-center justify-between cursor-pointer" onclick="toggleDarkMode()">
                            <div>
                                <span class="material-symbols-outlined text-lg align-middle mr-2" id="dark-mode-icon">dark_mode</span>
                                <span id="dark-mode-text">Giao diện Tối</span>
                            </div>
                            <div class="relative inline-block w-8 h-4 rounded-full bg-gray-300 transition-colors" id="dark-mode-track">
                                <div class="absolute left-0.5 top-0.5 w-3 h-3 rounded-full bg-white transition-transform duration-200" id="dark-mode-knob"></div>
                            </div>
                        </div>
                        
                        <hr class="border-outline-variant my-1">
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="block px-4 py-3 text-sm text-error hover:bg-error-container transition-colors rounded-b-lg">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">logout</span>
                            Đăng xuất
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" aria-label="Account" class="hover:text-axeron-red transition-colors duration-200 flex items-center">
                    <span class="material-symbols-outlined text-[28px]">person</span>
                </a>
            <?php endif; ?>

            <!-- Wishlist -->
            <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/shop/wishlist.php" aria-label="Wishlist" class="hover:text-axeron-red transition-colors relative flex items-center">
                <span class="material-symbols-outlined text-[28px]" data-icon="favorite">favorite</span>
            </a>
            <?php endif; ?>

            <!-- Dark Mode Toggle (Global Icon) -->
            <button onclick="toggleDarkMode()" aria-label="Toggle Dark Mode" class="hidden md:flex hover:text-axeron-red transition-colors duration-200 focus:outline-none items-center justify-center">
                <span class="material-symbols-outlined text-[28px]" id="dark-mode-icon-header">dark_mode</span>
            </button>

            <!-- Shopping Cart -->
            <a href="<?= BASE_URL ?>/shop/cart.php" aria-label="Shopping Cart" class="hover:text-axeron-red transition-colors relative flex items-center">
                <span class="material-symbols-outlined text-[28px]" data-icon="shopping_cart">shopping_cart</span>
                <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-1.5 -right-1.5 bg-axeron-red text-white text-xs font-bold h-5 w-5 rounded-full flex items-center justify-center cart-badge">
                        <?= $cartCount > 99 ? '99+' : $cartCount ?>
                    </span>
                <?php else: ?>
                    <span class="absolute -top-1.5 -right-1.5 bg-axeron-red text-white text-xs font-bold h-5 w-5 rounded-full flex items-center justify-center cart-badge" style="display: none;">
                        0
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu-backdrop" id="mobile-menu-backdrop" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu-panel" id="mobile-menu-panel">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="Logo" class="w-8 h-8 rounded-lg object-cover">
            <span class="font-bold text-xl text-axeron-red uppercase tracking-tight"><?= htmlspecialchars($siteNameDisplay) ?></span>
        </a>
        <button onclick="toggleMobileMenu()" class="p-2 hover:bg-gray-100 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <!-- Mobile Search -->
    <div class="p-4 border-b border-gray-100">
        <form action="<?= BASE_URL ?>/shop/product-catalog.php" method="POST" enctype="multipart/form-data" class="relative" autocomplete="off">
            <input type="text" name="search" id="mobile-search-input" placeholder="Tìm kiếm sản phẩm..."
                   class="w-full pl-4 pr-16 py-2.5 border border-gray-200 rounded-full text-sm text-on-surface focus:ring-2 focus:ring-axeron-red outline-none">
            
            <label class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-axeron-red cursor-pointer mb-0 p-1">
                <span class="material-symbols-outlined text-xl">photo_camera</span>
                <input type="file" name="search_image" accept="image/*" class="hidden" onchange="this.form.submit()">
            </label>
                   
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 bg-transparent border-none p-0">
                <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
            </button>
            <!-- Mobile Autocomplete Dropdown -->
            <div id="mobile-search-dropdown" class="absolute left-0 right-0 top-full mt-1 bg-white border border-outline-variant rounded-xl shadow-lg z-50 hidden max-h-[300px] overflow-y-auto custom-scrollbar"></div>
        </form>
    </div>

    <nav class="py-2">
        <?php foreach ($_navLevel1 as $_l1):
            $_hasLevel2 = isset($_navLevel2Map[$_l1['category_id']]);
            $_mobileId = 'mobile-cat-' . $_l1['category_id'];
        ?>
        <div class="border-b border-gray-50">
            <div class="flex items-center">
                <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l1['slug']) ?>"
                   class="flex-1 px-5 py-3 font-semibold text-sm uppercase tracking-wide text-gray-800 hover:text-axeron-red transition-colors">
                    <?= htmlspecialchars($_l1['category_name']) ?>
                </a>
                <?php if ($_hasLevel2): ?>
                <button onclick="toggleMobileSubmenu('<?= $_mobileId ?>')" class="px-4 py-3 text-gray-400 hover:text-axeron-red transition-colors">
                    <span class="material-symbols-outlined text-xl mobile-arrow" id="<?= $_mobileId ?>-arrow">expand_more</span>
                </button>
                <?php endif; ?>
            </div>

            <?php if ($_hasLevel2): ?>
            <div class="mobile-submenu" id="<?= $_mobileId ?>">
                <?php foreach ($_navLevel2Map[$_l1['category_id']] as $_l2):
                    $_hasLevel3 = isset($_navLevel3Map[$_l2['category_id']]);
                    $_mobileSubId = 'mobile-sub-' . $_l2['category_id'];
                ?>
                <div class="bg-gray-50/50">
                    <div class="flex items-center">
                        <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l2['slug']) ?>"
                           class="flex-1 pl-8 pr-4 py-2.5 text-sm font-medium text-gray-700 hover:text-axeron-red transition-colors">
                            <?= htmlspecialchars($_l2['category_name']) ?>
                        </a>
                        <?php if ($_hasLevel3): ?>
                        <button onclick="toggleMobileSubmenu('<?= $_mobileSubId ?>')" class="px-4 py-2.5 text-gray-400 hover:text-axeron-red">
                            <span class="material-symbols-outlined text-lg mobile-arrow" id="<?= $_mobileSubId ?>-arrow">expand_more</span>
                        </button>
                        <?php endif; ?>
                    </div>

                    <?php if ($_hasLevel3): ?>
                    <div class="mobile-submenu" id="<?= $_mobileSubId ?>">
                        <?php foreach ($_navLevel3Map[$_l2['category_id']] as $_l3): ?>
                        <a href="<?= BASE_URL ?>/shop/product-catalog.php?category=<?= htmlspecialchars($_l3['slug']) ?>"
                           class="block pl-12 pr-4 py-2 text-sm text-gray-500 hover:text-axeron-red transition-colors <?= $currentCategorySlug === $_l3['slug'] ? 'text-axeron-red font-medium' : '' ?>">
                            <?= htmlspecialchars($_l3['category_name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- Mobile extra links -->
        <div class="mt-4 px-5 py-3 border-t border-gray-200">
            <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/auth/account.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">person</span> Tài khoản
            </a>
            <a href="<?= BASE_URL ?>/shop/order-history.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">receipt_long</span> Đơn hàng của tôi
            </a>


            <a href="<?= BASE_URL ?>/shop/wishlist.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">favorite</span> Yêu thích
            </a>
            <a href="<?= BASE_URL ?>/shop/recently-viewed.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">history</span> Sản phẩm đã xem
            </a>
            <?php if (isAdmin()): ?>
            <a href="<?= BASE_URL ?>/admin/admin.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">admin_panel_settings</span> Trang quản trị
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="flex items-center gap-3 py-2.5 text-sm text-red-500 hover:text-red-700">
                <span class="material-symbols-outlined text-lg">logout</span> Đăng xuất
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/auth/login.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">login</span> Đăng nhập
            </a>
            <a href="<?= BASE_URL ?>/auth/register.php" class="flex items-center gap-3 py-2.5 text-sm text-gray-700 hover:text-axeron-red">
                <span class="material-symbols-outlined text-lg">person_add</span> Đăng ký
            </a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
    window.getImageUrl = function(url, defaultUrl = '') {
        if (!url) return defaultUrl;
        if (/^https?:\/\//i.test(url)) return url;
        let cleanUrl = url.replace(/^\/+/, '');
        return window.BASE_URL.replace(/\/+$/, '') + '/' + cleanUrl;
    };

    // Search functionality
    document.getElementById('search-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = this.value.trim();
            if (query) {
                window.location.href = '<?= BASE_URL ?>/shop/product-catalog.php?search=' + encodeURIComponent(query);
            }
        }
    });

    document.getElementById('search-input')?.addEventListener('input', function() {
        const searchBtn = document.getElementById('search-btn');
        if (searchBtn && this.value.trim()) {
            searchBtn.href = '<?= BASE_URL ?>/shop/product-catalog.php?search=' + encodeURIComponent(this.value.trim());
        }
    });

    // ========== Mega Menu Hover Intent ==========
    (function() {
        let currentOpen = null;
        let closeTimer = null;
        const nav = document.getElementById('mega-nav');
        if (!nav) return;

        function cancelClose() {
            clearTimeout(closeTimer);
            closeTimer = null;
        }

        function closeAll() {
            cancelClose();
            if (currentOpen) {
                currentOpen.classList.remove('mega-open');
                currentOpen = null;
            }
        }

        function openMenu(item) {
            if (currentOpen === item) return; // Already open, do nothing
            cancelClose();
            if (currentOpen) currentOpen.classList.remove('mega-open');

            // Only open if this item has a mega panel
            if (item.querySelector('.mega-panel')) {
                item.classList.add('mega-open');
                currentOpen = item;
            } else {
                currentOpen = null;
            }
        }

        // Single mouseover on entire nav — fires on every mouse move within nav + children.
        // Since mega panels are DOM children of nav items (inside nav),
        // hovering panels also triggers this — keeping the menu alive.
        nav.addEventListener('mouseover', (e) => {
            cancelClose(); // Mouse is inside nav, never close
            const item = e.target.closest('.mega-nav-item');
            if (item) openMenu(item);
        });

        // Only fires when mouse truly leaves nav AND all its descendants
        // (including the absolutely-positioned mega panels + their ::before bridge)
        nav.addEventListener('mouseleave', () => {
            closeTimer = setTimeout(closeAll, 200);
        });

        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#mega-nav')) closeAll();
        });
    })();

    // ========== Mobile Menu ==========
    function toggleMobileMenu() {
        document.getElementById('mobile-menu-backdrop').classList.toggle('active');
        document.getElementById('mobile-menu-panel').classList.toggle('active');
        document.body.style.overflow = document.getElementById('mobile-menu-panel').classList.contains('active') ? 'hidden' : '';
    }

    function toggleMobileSubmenu(id) {
        const el = document.getElementById(id);
        const arrow = document.getElementById(id + '-arrow');
        if (el) {
            el.classList.toggle('open');
            if (arrow) {
                arrow.style.transform = el.classList.contains('open') ? 'rotate(180deg)' : '';
            }
        }
    }

    // ========== Autocomplete Search ==========
    function setupAutocomplete(inputId, dropdownId) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        let timeout = null;

        if (!input || !dropdown) return;

        input.addEventListener('input', function() {
            clearTimeout(timeout);
            const keyword = this.value.trim();
            
            if (keyword.length < 2) {
                dropdown.classList.add('hidden');
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const res = await fetch(`<?= BASE_URL ?>/api/products.php?action=autocomplete&keyword=${encodeURIComponent(keyword)}`);
                    const data = await res.json();
                    
                    if (data.success && (data.data.categories.length > 0 || data.data.products.length > 0)) {
                        let html = '';
                        
                        // Categories
                        if (data.data.categories.length > 0) {
                            html += `<div class="px-4 py-2 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider rounded-t-xl">Danh mục</div>`;
                            data.data.categories.forEach(c => {
                                html += `<a href="<?= BASE_URL ?>/shop/product-catalog.php?category=${c.slug}" class="block px-4 py-2 hover:bg-red-50 hover:text-axeron-red transition-colors text-sm text-gray-700">
                                            <span class="material-symbols-outlined text-[16px] align-middle mr-2">category</span>
                                            ${c.category_name}
                                         </a>`;
                            });
                        }
                        
                        // Products
                        if (data.data.products.length > 0) {
                            html += `<div class="px-4 py-2 bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider border-t">Sản phẩm gợi ý</div>`;
                            data.data.products.forEach(p => {
                                html += `<a href="<?= BASE_URL ?>/shop/product-detail.php?slug=${p.slug}" class="flex items-center gap-3 px-4 py-2 hover:bg-red-50 transition-colors">
                                            <img src="${p.image_url}" class="w-10 h-10 rounded object-cover border border-gray-100 flex-shrink-0">
                                            <div class="overflow-hidden">
                                                <div class="text-sm font-medium text-gray-800 truncate">${p.product_name}</div>
                                                <div class="text-xs text-axeron-red font-bold">${p.price_formatted}</div>
                                            </div>
                                         </a>`;
                            });
                        }
                        
                        dropdown.innerHTML = html;
                        dropdown.classList.remove('hidden');
                    } else {
                        dropdown.innerHTML = `<div class="px-4 py-3 text-sm text-gray-500 text-center">Không tìm thấy "${keyword}"</div>`;
                        dropdown.classList.remove('hidden');
                    }
                } catch (e) {
                    console.error('Autocomplete Error:', e);
                }
            }, 300);
        });

        // Hide on click outside
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Show again on focus
        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2 && !dropdown.classList.contains('hidden') === false && dropdown.innerHTML.trim() !== '') {
                dropdown.classList.remove('hidden');
            }
        });
    }

    // Dark Mode Toggle Logic
    function toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateDarkModeUI(isDark);
    }
    
    function updateDarkModeUI(isDark) {
        const icon = document.getElementById('dark-mode-icon');
        const text = document.getElementById('dark-mode-text');
        const track = document.getElementById('dark-mode-track');
        const knob = document.getElementById('dark-mode-knob');
        
        if (icon && text && track && knob) {
            if (isDark) {
                icon.textContent = 'light_mode';
                text.textContent = 'Giao diện Sáng';
                track.classList.replace('bg-gray-300', 'bg-axeron-red');
                knob.style.transform = 'translateX(16px)';
            } else {
                icon.textContent = 'dark_mode';
                text.textContent = 'Giao diện Tối';
                track.classList.replace('bg-axeron-red', 'bg-gray-300');
                knob.style.transform = 'translateX(0)';
            }
        }
        
        const headerIcon = document.getElementById('dark-mode-icon-header');
        if (headerIcon) {
            headerIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
        }
    }

    // Initialize for both Desktop and Mobile
    document.addEventListener('DOMContentLoaded', () => {
        setupAutocomplete('search-input', 'desktop-search-dropdown');
        setupAutocomplete('mobile-search-input', 'mobile-search-dropdown');
        
        // Sync Dark Mode UI on load
        if (document.documentElement.classList.contains('dark')) {
            updateDarkModeUI(true);
        }
    });
</script>
