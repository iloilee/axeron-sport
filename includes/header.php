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
    WHERE parent_id IS NULL AND is_visible = 1
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
</style>

<!-- TopAppBar Component -->
<header class="bg-surface dark:bg-on-background sticky top-0 z-50 border-b border-outline-variant dark:border-outline" id="main-header">
    <div class="flex justify-between items-center w-[80%] max-w-none px-margin-desktop py-5 mx-auto">
        <!-- Mobile Menu Toggle -->
        <button class="md:hidden p-2 -ml-2 hover:bg-surface-container rounded-lg transition-colors" onclick="toggleMobileMenu()" aria-label="Menu">
            <span class="material-symbols-outlined text-2xl text-on-surface">menu</span>
        </button>

        <!-- Brand Logo -->
        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 rounded-lg object-cover">
            <span class="font-display-lg text-axeron-red uppercase tracking-tight text-xl md:text-2xl">Axeron</span>
        </a>

        <!-- Navigation Links (Desktop) - Mega Menu -->
        <nav class="hidden lg:flex items-center gap-6 xl:gap-8 flex-1 justify-center" id="mega-nav">
            <?php foreach ($_navLevel1 as $_l1):
                $_isActive = ($_activeRootId === $_l1['category_id']);
                $_hasLevel2 = isset($_navLevel2Map[$_l1['category_id']]);
            ?>
            <div class="mega-nav-item">
                <a class="mega-nav-link font-label-lg text-label-lg uppercase whitespace-nowrap px-4
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
            <div class="relative hidden lg:block">
                <input
                    class="bg-surface-container rounded-full py-2 pl-4 pr-10 border border-outline-variant focus:border-axeron-blue focus:ring-1 focus:ring-axeron-blue outline-none text-body-md font-body-md w-80 transition-all"
                    placeholder="Tìm kiếm..."
                    type="text"
                    id="search-input"
                    name="search"
                />
                <a href="<?= BASE_URL ?>/shop/product-catalog.php" id="search-btn" class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant cursor-pointer text-xl">
                    search
                </a>
            </div>

            <!-- Mobile Search -->
            <a href="<?= BASE_URL ?>/shop/product-catalog.php" class="lg:hidden p-1 hover:text-axeron-red transition-colors">
                <span class="material-symbols-outlined text-2xl">search</span>
            </a>

            <!-- User Account -->
            <?php if (isLoggedIn()): ?>
                <div class="relative group">
                    <button aria-label="Account" class="hover:text-axeron-red transition-colors duration-200 flex items-center gap-1.5">
                        <?php if (!empty($_SESSION['avatar_url'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($_SESSION['avatar_url']) ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-outline-variant">
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
                            Đơn hàng
                        </a>
                        <?php if (isAdmin()): ?>
                        <hr class="border-outline-variant my-1">
                        <a href="<?= BASE_URL ?>/admin/admin.php" class="block px-4 py-3 text-sm text-on-surface hover:bg-surface-container transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">admin_panel_settings</span>
                            Trang quản trị
                        </a>
                        <?php endif; ?>
                        <hr class="border-outline-variant my-1">
                        <a href="<?= BASE_URL ?>/auth/logout.php" class="block px-4 py-3 text-sm text-error hover:bg-error-container transition-colors rounded-b-lg">
                            <span class="material-symbols-outlined text-lg align-middle mr-2">logout</span>
                            Đăng xuất
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login.php" aria-label="Account" class="hover:text-axeron-red transition-colors duration-200">
                    <span class="material-symbols-outlined text-[28px]">person</span>
                </a>
            <?php endif; ?>

            <!-- Shopping Cart -->
            <a href="<?= BASE_URL ?>/shop/cart.php" aria-label="Shopping Cart" class="hover:text-axeron-red transition-colors relative mt-1">
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

<!-- Promo Banner -->
<div class="w-full bg-black text-white text-center py-2 text-label-sm font-label-sm tracking-wide">
    Freeship với hóa đơn trên 500k |
    <a class="underline hover:text-axeron-red" href="<?= BASE_URL ?>/auth/register.php">ĐĂNG KÝ NGAY</a>
</div>

<!-- Mobile Menu -->
<div class="mobile-menu-backdrop" id="mobile-menu-backdrop" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu-panel" id="mobile-menu-panel">
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <a class="flex items-center gap-2 flex-shrink-0" href="<?= BASE_URL ?>/">
            <img src="<?= BASE_URL ?>/assets/images/logo-axeron.jpg" alt="Logo" class="w-8 h-8 rounded-lg object-cover">
            <span class="font-bold text-xl text-axeron-red uppercase tracking-tight">Axeron</span>
        </a>
        <button onclick="toggleMobileMenu()" class="p-2 hover:bg-gray-100 rounded-lg">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <!-- Mobile Search -->
    <div class="p-4 border-b border-gray-100">
        <form action="<?= BASE_URL ?>/shop/product-catalog.php" method="GET" class="relative">
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..."
                   class="w-full pl-4 pr-10 py-2.5 border border-gray-200 rounded-full text-sm focus:ring-2 focus:ring-axeron-red outline-none">
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2">
                <span class="material-symbols-outlined text-gray-400 text-xl">search</span>
            </button>
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
                <span class="material-symbols-outlined text-lg">receipt_long</span> Đơn hàng
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
</script>
