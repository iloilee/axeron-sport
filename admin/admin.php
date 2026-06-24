<?php
/**
 * Admin Dashboard - Axeron Sports Shop
 * Trang quản trị chính
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Kiểm tra đăng nhập và quyền admin
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if (!isAdmin()) {
    setFlash('error', 'Bạn không có quyền truy cập trang quản trị!');
    header('Location: ' . BASE_URL . '/');
    exit;
}

// Lấy thông tin user hiện tại
$currentUser = getUserData();

// Xử lý AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    require_once __DIR__ . '/admin-api.php';
    exit;
}

// Lấy action từ URL
$action = $_GET['action'] ?? 'dashboard';

// Kiểm tra quyền hạn của action được yêu cầu
if (!hasPermission($action)) {
    $allActions = ['dashboard', 'products', 'categories', 'brands', 'orders', 'users', 'shipping_price', 'reviews', 'promotions', 'analytics', 'banners', 'articles', 'featured', 'settings', 'customer_detail'];
    foreach ($allActions as $act) {
        if (hasPermission($act)) {
            header('Location: ' . BASE_URL . '/admin/admin.php?action=' . $act);
            exit;
        }
    }
    // Nếu hoàn toàn không có quyền
    setFlash('error', 'Bạn không có quyền truy cập trang quản trị!');
    header('Location: ' . BASE_URL . '/');
    exit;
}

// Load dữ liệu dựa trên action
$db = db();
$stats = [];

// Cập nhật thông tin avatar mới nhất từ DB
$adminUser = $db->selectOne("SELECT avatar_url FROM users WHERE user_id = ?", [$currentUser['user_id']]);
if ($adminUser) {
    $currentUser['avatar_url'] = $adminUser['avatar_url'];
}

$avatarSrc = '';
if (!empty($currentUser['avatar_url'])) {
    if (strpos($currentUser['avatar_url'], 'http') === 0) {
        $avatarSrc = htmlspecialchars($currentUser['avatar_url']);
    } else {
        $path = $currentUser['avatar_url'];
        if (strpos($path, '/') !== 0) $path = '/' . $path;
        $avatarSrc = BASE_URL . htmlspecialchars($path);
    }
}

$pageTitle = match($action) {
    'dashboard' => 'Tổng Quan',
    'products' => 'Sản Phẩm',
    'categories' => 'Danh Mục',
    'brands' => 'Thương Hiệu',
    'orders' => 'Đơn Hàng',
    'users' => 'Người Dùng',
    'shipping_price' => 'Phí Vận Chuyển',
    'reviews' => 'Đánh Giá',
    'promotions' => 'Khuyến Mãi',
    'banners' => 'Banner/Slider',
    'articles' => 'Bài Viết',
    'settings' => 'Cài Đặt',
    'analytics' => 'Thống Kê',
    'featured' => 'Sản Phẩm Nổi Bật',
    'customer_detail' => 'Chi Tiết Khách Hàng',
    'product_detail' => 'Chi Tiết Sản Phẩm',
    default => 'Dashboard'
};

// Dashboard Statistics
if ($action === 'dashboard') {

    // Đơn hàng hôm nay
    $todayOrders = $db->selectOne("
        SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE DATE(created_at) = CURDATE() AND order_status NOT IN ('cancelled', 'returned')
    ");

    // Đơn hàng chờ xử lý
    $pendingOrders = $db->selectOne("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");

    // Tổng sản phẩm
    $totalProducts = $db->selectOne("SELECT COUNT(*) as count FROM products WHERE is_visible = 1");

    // Tổng khách hàng
    $totalCustomers = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE role_id = 3");

    // Doanh thu tháng này
    $monthlyRevenue = $db->selectOne("
        SELECT COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE MONTH(created_at) = MONTH(CURDATE())
        AND YEAR(created_at) = YEAR(CURDATE())
        AND order_status NOT IN ('cancelled', 'returned')
        AND payment_status = 'paid'
    ");

    // Doanh thu tháng trước
    $prevMonthlyRevenue = $db->selectOne("
        SELECT COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE MONTH(created_at) = MONTH(CURDATE() - INTERVAL 1 MONTH)
        AND YEAR(created_at) = YEAR(CURDATE() - INTERVAL 1 MONTH)
        AND order_status NOT IN ('cancelled', 'returned')
        AND payment_status = 'paid'
    ");

    // Đơn hàng gần đây
    $recentOrders = $db->select("
        SELECT o.*, u.full_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.created_at DESC LIMIT 10
    ");

    // Sản phẩm bán chạy
    $topProducts = $db->select("
        SELECT p.product_name, p.product_id, SUM(oi.quantity) as sold, SUM(oi.subtotal) as revenue
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        JOIN products p ON pv.product_id = p.product_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.order_status NOT IN ('cancelled', 'returned')
        GROUP BY p.product_id, p.product_name
        ORDER BY sold DESC LIMIT 5
    ");

    // Review chờ duyệt
    $pendingReviews = $db->selectOne("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'");

    // --- NÂNG CẤP DASHBOARD (5 TÍNH NĂNG MỚI) ---
    
    // 1. Biểu đồ Doanh thu (30 ngày)
    $chartDataRaw = $db->select("
        SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue
        FROM orders
        WHERE created_at >= DATE(NOW()) - INTERVAL 29 DAY
        AND order_status NOT IN ('cancelled', 'returned')
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $chartDates = [];
    $chartRevenues = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $chartDates[] = date('d/m', strtotime($d));
        $found = false;
        foreach ($chartDataRaw as $row) {
            if ($row['date'] === $d) {
                $chartRevenues[] = (float)$row['revenue'];
                $found = true; break;
            }
        }
        if (!$found) $chartRevenues[] = 0;
    }

    // Biểu đồ Trạng thái Đơn hàng (Pie)
    $orderStatusRaw = $db->select("SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status");
    $statusCounts = ['pending' => 0, 'processing' => 0, 'shipped' => 0, 'completed' => 0, 'cancelled' => 0, 'returned' => 0];
    foreach ($orderStatusRaw as $row) {
        if (isset($statusCounts[$row['order_status']])) {
            $statusCounts[$row['order_status']] = (int)$row['count'];
        }
    }

    // 2. Cảnh báo rủi ro (Low Stock & Dead Stock)
    $lowStockProducts = $db->select("
        SELECT p.product_name, pv.sku, pv.stock_quantity, p.product_id
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.product_id
        WHERE pv.stock_quantity > 0 AND pv.stock_quantity <= 5
        ORDER BY pv.stock_quantity ASC
        LIMIT 5
    ");
    $deadStockProducts = $db->select("
        SELECT p.product_name, pv.sku, pv.stock_quantity, p.product_id
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.product_id
        WHERE pv.stock_quantity > 0
        AND pv.variant_id NOT IN (
            SELECT DISTINCT variant_id 
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.created_at >= DATE(NOW()) - INTERVAL 30 DAY
        )
        LIMIT 5
    ");

    // 3. Marketing & Customer Insights
    $vipCustomers = $db->select("
        SELECT u.full_name, u.email, COALESCE(SUM(o.total_amount), 0) as total_spent, COUNT(o.order_id) as total_orders
        FROM users u
        JOIN orders o ON u.user_id = o.user_id
        WHERE o.order_status NOT IN ('cancelled', 'returned')
        GROUP BY u.user_id
        ORDER BY total_spent DESC
        LIMIT 5
    ");
    $promoPerformance = $db->select("
        SELECT promo_code, promo_name, used_count
        FROM promotions
        WHERE is_active = 1
        ORDER BY used_count DESC
        LIMIT 5
    ");

    // 4. Activity Log (Timeline tổng hợp)
    $activities = [];
    $recentOrderLogs = $db->select("SELECT order_id, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    foreach($recentOrderLogs as $o) {
        $activities[] = [
            'time' => $o['created_at'], 'type' => 'order', 'icon' => 'shopping_bag', 'color' => 'text-blue-500', 'bg' => 'bg-blue-100',
            'message' => "Đơn hàng mới <b>#" . htmlspecialchars($o['order_id']) . "</b> vừa được tạo."
        ];
    }
    $recentReviewLogs = $db->select("SELECT review_id, created_at, rating FROM reviews ORDER BY created_at DESC LIMIT 5");
    foreach($recentReviewLogs as $r) {
        $activities[] = [
            'time' => $r['created_at'], 'type' => 'review', 'icon' => 'star', 'color' => 'text-yellow-500', 'bg' => 'bg-yellow-100',
            'message' => "Khách hàng vừa để lại đánh giá <b>" . htmlspecialchars($r['rating']) . " sao</b>."
        ];
    }
    $recentUserLogs = $db->select("SELECT user_id, full_name, created_at FROM users WHERE role_id = 3 ORDER BY created_at DESC LIMIT 5");
    foreach($recentUserLogs as $u) {
        $activities[] = [
            'time' => $u['created_at'], 'type' => 'user', 'icon' => 'person_add', 'color' => 'text-green-500', 'bg' => 'bg-green-100',
            'message' => "Khách hàng mới <b>" . htmlspecialchars($u['full_name']) . "</b> vừa đăng ký."
        ];
    }
    usort($activities, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
    $activities = array_slice($activities, 0, 8);

    $stats = [
        'todayOrders' => $todayOrders['count'] ?? 0,
        'todayRevenue' => $todayOrders['revenue'] ?? 0,
        'pendingOrders' => $pendingOrders['count'] ?? 0,
        'totalProducts' => $totalProducts['count'] ?? 0,
        'totalCustomers' => $totalCustomers['count'] ?? 0,
        'monthlyRevenue' => $monthlyRevenue['revenue'] ?? 0,
        'prevMonthlyRevenue' => $prevMonthlyRevenue['revenue'] ?? 0,
        'pendingReviews' => $pendingReviews['count'] ?? 0,
        'recentOrders' => $recentOrders,
        'topProducts' => $topProducts,
        'chartDates' => $chartDates,
        'chartRevenues' => $chartRevenues,
        'statusCounts' => $statusCounts,
        'lowStockProducts' => $lowStockProducts,
        'deadStockProducts' => $deadStockProducts,
        'vipCustomers' => $vipCustomers,
        'promoPerformance' => $promoPerformance,
        'activities' => $activities
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php
    $_adminSettingsRaw = $db->select("SELECT setting_key, setting_value FROM site_settings");
    $adminSettings = [];
    foreach ($_adminSettingsRaw as $s) {
        $adminSettings[$s['setting_key']] = $s['setting_value'];
    }
    $adminSiteLogo = $adminSettings['site_logo'] ?? '/assets/images/logo-axeron.jpg';
    $adminSiteFavicon = $adminSettings['site_favicon'] ?? '/assets/images/logo-axeron.jpg';
    
    if (strpos($adminSiteLogo, 'http') !== 0 && !empty($adminSiteLogo)) {
        $adminSiteLogo = (defined('BASE_URL') ? BASE_URL : '') . (strpos($adminSiteLogo, '/') === 0 ? '' : '/') . $adminSiteLogo;
    }
    if (strpos($adminSiteFavicon, 'http') !== 0 && !empty($adminSiteFavicon)) {
        $adminSiteFavicon = (defined('BASE_URL') ? BASE_URL : '') . (strpos($adminSiteFavicon, '/') === 0 ? '' : '/') . $adminSiteFavicon;
    }
    ?>
    <title>Admin - <?= $pageTitle ?> | <?= htmlspecialchars($adminSettings['site_name'] ?? 'Axeron Sport') ?></title>
    <link rel="icon" type="image/jpeg" href="<?= htmlspecialchars($adminSiteFavicon) ?>" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'axeron-red': '#BE1E2D',
                        'axeron-blue': '#2979FF',
                        'dark': '#1a1a1a',
                        'dark-light': '#2d2d2d'
                    }
                }
            }
        }
    </script>
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.CSRF_TOKEN = '<?= htmlspecialchars(generateCsrfToken()) ?>';
        window.getImageUrl = function(url, defaultUrl = '') {
            if (!url) return defaultUrl;
            if (/^https?:\/\//i.test(url)) return url;
            let cleanUrl = url.replace(/^\/+/, '');
            return window.BASE_URL.replace(/\/+$/, '') + '/' + cleanUrl;
        };

        // CSRF Fetch Interceptor
        const originalFetch = window.fetch;
        window.fetch = async function() {
            let [resource, config] = arguments;
            if (config && config.method && config.method.toUpperCase() === 'POST') {
                if (config.body instanceof FormData) {
                    if (!config.body.has('csrf_token')) {
                        config.body.append('csrf_token', window.CSRF_TOKEN);
                    }
                } else if (typeof config.body === 'string' && config.headers && config.headers['Content-Type'] === 'application/x-www-form-urlencoded') {
                    if (!config.body.includes('csrf_token=')) {
                        config.body += '&csrf_token=' + encodeURIComponent(window.CSRF_TOKEN);
                    }
                } else if (typeof config.body === 'string' && config.body.startsWith('{')) {
                    try {
                        let bodyObj = JSON.parse(config.body);
                        if (!bodyObj.csrf_token) {
                            bodyObj.csrf_token = window.CSRF_TOKEN;
                            config.body = JSON.stringify(bodyObj);
                        }
                    } catch (e) {}
                }
            }
            return originalFetch(resource, config);
        };
    </script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <style>
        body { font-family: 'Noto Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-link.active { background: linear-gradient(90deg, #BE1E2D 0%, #d32f2f 100%); color: white; }
        .sidebar-link:hover:not(.active) { background-color: #2d2d2d; }
        /* Hide scrollbar for sidebar */
        #sidebar::-webkit-scrollbar { display: none; }
        #sidebar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Desktop Sidebar Collapse */
        @media (min-width: 1024px) {
            #sidebar {
                transition: width 0.3s ease;
                overflow-x: hidden;
                white-space: nowrap;
            }
            #sidebar.collapsed {
                width: 5rem !important; /* w-20 */
            }
            #sidebar.collapsed:not(:hover) .sidebar-link > span:not(.material-symbols-outlined),
            #sidebar.collapsed:not(:hover) .logo-text,
            #sidebar.collapsed:not(:hover) .cms-text {
                display: none !important;
            }
            #sidebar.collapsed:not(:hover) .sidebar-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }
            #sidebar.collapsed:hover {
                width: 16rem !important;
                position: absolute;
                height: 100vh;
                z-index: 100;
                box-shadow: 4px 0 15px rgba(0,0,0,0.3);
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-dark text-white flex-shrink-0 fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-transform duration-300 lg:relative lg:translate-x-0 h-screen lg:h-auto overflow-y-auto lg:overflow-visible">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-700">
                <a href="<?= BASE_URL ?>/admin/admin.php" class="flex items-center gap-3">
                    <img src="<?= htmlspecialchars($adminSiteLogo) ?>" alt="Logo" class="w-10 h-10 rounded-lg object-cover bg-white">
                    <div class="logo-text whitespace-nowrap overflow-hidden">
                        <div class="font-bold text-lg leading-tight uppercase"><?= htmlspecialchars($adminSettings['site_name'] ?? 'Axeron Sports') ?></div>
                        <div class="text-xs text-gray-400">Admin Panel</div>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <?php if (hasPermission('dashboard')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'dashboard' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Tổng Quan</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('products')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=products" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'products' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>Sản Phẩm</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('categories')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=categories" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'categories' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">category</span>
                    <span>Danh Mục</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('brands')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=brands" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'brands' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">branding_watermark</span>
                    <span>Thương Hiệu</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('orders')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=orders" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'orders' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span>Đơn Hàng</span>
                    <?php if (($stats['pendingOrders'] ?? 0) > 0): ?>
                    <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['pendingOrders'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('users')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=users" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'users' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">people</span>
                    <span>Người Dùng</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('shipping_price')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=shipping_price" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'shipping_price' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span>Phí Vận Chuyển</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('reviews')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=reviews" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'reviews' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">reviews</span>
                    <span>Đánh Giá</span>
                    <?php if (($stats['pendingReviews'] ?? 0) > 0): ?>
                    <span class="ml-auto bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['pendingReviews'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('promotions')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=promotions" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'promotions' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">sell</span>
                    <span>Khuyến Mãi</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('analytics')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=analytics" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'analytics' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Thống Kê</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('banners') || hasPermission('articles') || hasPermission('featured') || hasPermission('settings')): ?>
                <div class="border-t border-gray-700 my-4"></div>
                <!-- CMS Section -->
                <p class="cms-text px-4 text-xs text-gray-500 uppercase tracking-wider mb-2 whitespace-nowrap overflow-hidden">Nội dung CMS</p>

                <?php if (hasPermission('banners')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=banners" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'banners' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">image</span>
                    <span>Banner/Slider</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('articles')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=articles" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'articles' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">article</span>
                    <span>Bài Viết</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('featured')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=featured" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'featured' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">star</span>
                    <span>SP Nổi Bật</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('settings')): ?>
                <a href="<?= BASE_URL ?>/admin/admin.php?action=settings" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'settings' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">settings</span>
                    <span>Cài Đặt</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>

                <div class="border-t border-gray-700 my-4"></div>
                <a href="<?= BASE_URL ?>/" target="_blank" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all">
                    <span class="material-symbols-outlined">open_in_new</span>
                    <span>Xem Website</span>
                </a>
                <a href="<?= BASE_URL ?>/auth/logout.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all hover:bg-red-900">
                    <span class="material-symbols-outlined">logout</span>
                    <span>Đăng Xuất</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 sm:p-6 w-full lg:w-auto overflow-hidden">
            <!-- Header -->
            <header class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden p-2 -ml-2 text-gray-600 hover:bg-gray-100 rounded-lg" onclick="toggleSidebar()">
                        <span class="material-symbols-outlined text-2xl">menu</span>
                    </button>
                    <button class="hidden lg:block p-2 -ml-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" onclick="toggleSidebarDesktop()">
                        <span class="material-symbols-outlined text-2xl">menu</span>
                    </button>
                    <div>
                        <?php if ($action === 'analytics'): ?>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Báo Cáo & Thống Kê</h1>
                        <?php else: ?>
                        <?php 
                        $displayTitle = $pageTitle;
                        if (!in_array($action, ['dashboard', 'settings'])) {
                            $displayTitle = 'Quản lý ' . $pageTitle;
                        }
                        ?>
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-800"><?= $displayTitle ?></h1>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>/" target="_blank" class="text-gray-500 hover:text-axeron-red">
                        <span class="material-symbols-outlined">open_in_new</span>
                    </a>
                    <div class="flex items-center gap-2">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($currentUser['full_name']) ?></p>
                            <p class="text-xs text-gray-500 text-right">Admin</p>
                        </div>
                        <?php if (!empty($avatarSrc)): ?>
                            <img src="<?= $avatarSrc ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border-2 border-axeron-red">
                        <?php else: ?>
                            <div class="w-10 h-10 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php if ($flash = getFlash()): ?>
            <div class="mb-6 p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- Content Area -->
            <?php if ($action === 'dashboard'): ?>
                <!-- Dashboard Content -->

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Đơn hàng hôm nay</p>
                                <h3 class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['todayOrders']) ?></h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <span class="material-symbols-outlined">shopping_cart</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-xs text-slate-500">Doanh thu hôm nay: <span class="font-medium text-green-600"><?= formatPrice($stats['todayRevenue']) ?></span></span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Chờ xử lý</p>
                                <h3 class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['pendingOrders']) ?></h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-50 text-yellow-600">
                                <span class="material-symbols-outlined">pending_actions</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/admin/admin.php?action=orders" class="text-xs font-medium text-axeron-red hover:underline">Xem ngay →</a>
                        </div>
                    </div>

                    <?php
                    $curRev = $stats['monthlyRevenue'] ?? 0;
                    $prevRev = $stats['prevMonthlyRevenue'] ?? 0;
                    if ($prevRev == 0) {
                        $trend = 'up';
                        $percent = $curRev > 0 ? 100 : 0;
                    } else {
                        $diff = $curRev - $prevRev;
                        $trend = $diff >= 0 ? 'up' : 'down';
                        $percent = abs(round(($diff / $prevRev) * 100, 1));
                    }
                    $trendIcon = $trend === 'up' ? 'trending_up' : 'trending_down';
                    $trendColor = $trend === 'up' ? 'text-emerald-600' : 'text-red-600';
                    ?>
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm overflow-hidden">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-500 truncate">Doanh thu tháng</p>
                                <h3 class="mt-1 text-lg md:text-xl font-bold text-slate-900 truncate" title="<?= formatPrice($stats['monthlyRevenue']) ?>"><?= formatPrice($stats['monthlyRevenue']) ?></h3>
                            </div>
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-600">
                                <span class="material-symbols-outlined">payments</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 flex-wrap">
                            <span class="flex items-center text-xs font-medium <?= $trendColor ?> whitespace-nowrap">
                                <span class="material-symbols-outlined !text-[14px] mr-0.5"><?= $trendIcon ?></span>
                                <?= $percent ?>%
                            </span>
                            <span class="text-xs text-slate-500 whitespace-nowrap">so với tháng trước</span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Khách hàng</p>
                                <h3 class="mt-1 text-2xl font-bold text-slate-900"><?= number_format($stats['totalCustomers']) ?></h3>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                                <span class="material-symbols-outlined">people</span>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/admin/admin.php?action=users" class="text-xs font-medium text-axeron-red hover:underline">Quản lý →</a>
                        </div>
                    </div>
                </div>

                <!-- Charts & Timelines (New Feature) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="lg:col-span-2 bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <h2 class="font-bold text-lg mb-4">Biểu đồ Doanh thu (30 ngày qua)</h2>
                        <div class="w-full" style="height: 350px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <h2 class="font-bold text-lg mb-4">Trạng thái đơn hàng</h2>
                        <canvas id="orderStatusChart" height="200"></canvas>
                    </div>
                </div>

                <!-- Low Stock & Marketing -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-l-4 border-l-red-500">
                        <h2 class="font-bold text-lg text-red-600 mb-4 flex items-center gap-2"><span class="material-symbols-outlined">warning</span> Sắp hết hàng</h2>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach($stats['lowStockProducts'] as $p): ?>
                            <li class="py-2 flex justify-between items-center">
                                <span class="text-sm truncate pr-2" title="<?= htmlspecialchars($p['product_name']) ?>"><?= htmlspecialchars($p['product_name']) ?> (<?= htmlspecialchars($p['sku']) ?>)</span>
                                <span class="text-[10px] font-bold bg-red-100 text-red-600 px-2 py-1 rounded whitespace-nowrap">Còn <?= $p['stock_quantity'] ?></span>
                            </li>
                            <?php endforeach; if(empty($stats['lowStockProducts'])): ?>
                            <li class="py-2 text-sm text-gray-500">Tồn kho ổn định.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-l-4 border-l-orange-500">
                        <h2 class="font-bold text-lg text-orange-600 mb-4 flex items-center gap-2"><span class="material-symbols-outlined">inventory_2</span> Tồn kho lâu (30 ngày)</h2>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach($stats['deadStockProducts'] as $p): ?>
                            <li class="py-2 flex justify-between items-center">
                                <span class="text-sm truncate pr-2" title="<?= htmlspecialchars($p['product_name']) ?>"><?= htmlspecialchars($p['product_name']) ?></span>
                                <span class="text-[10px] font-bold bg-orange-100 text-orange-600 px-2 py-1 rounded whitespace-nowrap">Tồn <?= $p['stock_quantity'] ?></span>
                            </li>
                            <?php endforeach; if(empty($stats['deadStockProducts'])): ?>
                            <li class="py-2 text-sm text-gray-500">Không có hàng tồn.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
                        <h2 class="font-bold text-lg text-purple-600 mb-4 flex items-center gap-2"><span class="material-symbols-outlined">workspace_premium</span> Top Khách VIP</h2>
                        <ul class="divide-y divide-gray-100">
                            <?php foreach($stats['vipCustomers'] as $c): ?>
                            <li class="py-2 flex justify-between items-center">
                                <span class="text-sm truncate pr-2 font-medium"><?= htmlspecialchars($c['full_name']) ?></span>
                                <span class="text-sm text-purple-600 font-bold whitespace-nowrap"><?= formatPrice($c['total_spent']) ?></span>
                            </li>
                            <?php endforeach; if(empty($stats['vipCustomers'])): ?>
                            <li class="py-2 text-sm text-gray-500">Chưa có khách hàng.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- AI Forecast Chart -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 mb-6 border-l-4 border-indigo-500">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-bold text-lg text-indigo-700 flex items-center gap-2"><span class="material-symbols-outlined">auto_graph</span> Biểu Đồ Dự Báo Doanh Thu 90 Ngày Tới (AI Prophet)</h2>
                        <button id="btn-load-forecast" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">magic_button</span> Chạy AI Dự Báo
                        </button>
                    </div>
                    <canvas id="revenueForecastChart" height="80"></canvas>
                </div>

                <!-- Recent Orders & Activity Log -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Orders -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="font-bold text-lg">Đơn hàng gần đây</h2>
                            <a href="<?= BASE_URL ?>/admin/admin.php?action=orders" class="text-axeron-red text-sm hover:underline">Xem tất cả</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Mã ĐH</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Khách hàng</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tổng tiền</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($stats['recentOrders'] as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium">
                                            <?= htmlspecialchars($order['order_code']) ?>
                                            <?php if (strpos($order['note'] ?? '', '[Yêu cầu hủy từ khách]') !== false && in_array($order['order_status'], ['pending', 'confirmed'])): ?>
                                                <br><span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-800 text-[10px] font-bold rounded animate-pulse" title="Khách hàng yêu cầu hủy đơn này">YÊU CẦU HỦY</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($order['full_name'] ?? 'N/A') ?></td>
                                        <td class="px-4 py-3 text-sm font-medium text-axeron-red"><?= formatPrice($order['total_amount']) ?></td>
                                        <td class="px-4 py-3">
                                            <?php
                                            $statusClass = match($order['order_status']) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'confirmed' => 'bg-blue-100 text-blue-800',
                                                'processing' => 'bg-purple-100 text-purple-800',
                                                'shipped' => 'bg-indigo-100 text-indigo-800',
                                                'delivered' => 'bg-green-100 text-green-800',
                                                'cancelled' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            $statusText = match($order['order_status']) {
                                                'pending' => 'Chờ xử lý',
                                                'confirmed' => 'Đã xác nhận',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đang giao',
                                                'delivered' => 'Đã giao',
                                                'cancelled' => 'Đã hủy',
                                                default => $order['order_status']
                                            };
                                            ?>
                                            <span class="px-2 py-1 rounded-full text-xs <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($stats['recentOrders'])): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Chưa có đơn hàng nào</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="font-bold text-lg">Hoạt động mới nhất</h2>
                        </div>
                        <div class="p-4">
                            <div class="relative border-l border-gray-200 ml-3 space-y-4">
                                <?php foreach($stats['activities'] as $act): ?>
                                <div class="mb-4 ml-6">
                                    <span class="absolute -left-3.5 flex items-center justify-center w-7 h-7 <?= $act['bg'] ?> rounded-full ring-4 ring-white">
                                        <span class="material-symbols-outlined text-[14px] <?= $act['color'] ?>"><?= $act['icon'] ?></span>
                                    </span>
                                    <p class="text-sm text-gray-600 leading-tight"><?= $act['message'] ?></p>
                                    <time class="block mb-2 text-[11px] font-normal leading-none text-gray-400 mt-1"><?= date('d/m/Y H:i', strtotime($act['time'])) ?></time>
                                </div>
                                <?php endforeach; if(empty($stats['activities'])): ?>
                                <p class="ml-4 text-sm text-gray-500">Chưa có hoạt động nào.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const chartDates = <?= json_encode($stats['chartDates']) ?>;
                        const chartRevenues = <?= json_encode($stats['chartRevenues']) ?>;
                        const statusCounts = <?= json_encode($stats['statusCounts']) ?>;

                        // Revenue Chart
                        const ctxRev = document.getElementById('revenueChart');
                        if (ctxRev) {
                            new Chart(ctxRev, {
                                type: 'line',
                                data: {
                                    labels: chartDates,
                                    datasets: [{
                                        label: 'Doanh thu (VNĐ)',
                                        data: chartRevenues,
                                        borderColor: '#BE1E2D',
                                        backgroundColor: 'rgba(190, 30, 45, 0.1)',
                                        borderWidth: 2,
                                        fill: true,
                                        tension: 0.4
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: { 
                                        y: { beginAtZero: true },
                                        x: { 
                                            ticks: { 
                                                autoSkip: true, 
                                                maxTicksLimit: 15 
                                            } 
                                        }
                                    }
                                }
                            });
                        }

                        // Order Status Chart
                        const ctxStatus = document.getElementById('orderStatusChart');
                        if (ctxStatus) {
                            new Chart(ctxStatus, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Chờ xử lý', 'Đang xử lý', 'Đang giao', 'Đã giao', 'Đã hủy', 'Trả hàng'],
                                    datasets: [{
                                        data: [
                                            statusCounts.pending || 0, 
                                            statusCounts.processing || 0, 
                                            statusCounts.shipped || 0, 
                                            statusCounts.completed || 0, 
                                            statusCounts.cancelled || 0, 
                                            statusCounts.returned || 0
                                        ],
                                        backgroundColor: ['#fef08a', '#e9d5ff', '#c7d2fe', '#bbf7d0', '#fecaca', '#f5f5f4']
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { position: 'bottom' } }
                                }
                            });
                        }

                        // AI Forecast Chart Logic
                        document.getElementById('btn-load-forecast').addEventListener('click', function() {
                            let btn = this;
                            btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">refresh</span> Đang phân tích...';
                            btn.disabled = true;

                            fetch('api_get_forecast.php')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'error') {
                                        throw new Error(data.error);
                                    }
                                    
                                    let all_labels = [...new Set([...data.labels_history, ...data.labels_future])];

                                    let history_data_padded = data.values_history.slice();
                                    for(let i = 0; i < data.labels_future.length - 1; i++) {
                                        history_data_padded.push(null);
                                    }

                                    let future_data_padded = [];
                                    for(let i = 0; i < data.labels_history.length - 1; i++) {
                                        future_data_padded.push(null);
                                    }
                                    future_data_padded = future_data_padded.concat(data.values_future);

                                    var ctx = document.getElementById('revenueForecastChart').getContext('2d');
                                    
                                    if(window.myForecastChart) {
                                        window.myForecastChart.destroy();
                                    }

                                    window.myForecastChart = new Chart(ctx, {
                                        type: 'line',
                                        data: {
                                            labels: all_labels,
                                            datasets: [
                                                {
                                                    label: 'Doanh thu thực tế',
                                                    data: history_data_padded,
                                                    borderColor: '#2979FF',
                                                    backgroundColor: 'rgba(41, 121, 255, 0.1)',
                                                    borderWidth: 2,
                                                    pointRadius: 0,
                                                    pointHoverRadius: 4,
                                                    fill: true,
                                                    tension: 0.3
                                                },
                                                {
                                                    label: 'AI Dự báo (90 ngày tới)',
                                                    data: future_data_padded,
                                                    borderColor: '#BE1E2D',
                                                    borderDash: [5, 5],
                                                    backgroundColor: 'transparent',
                                                    borderWidth: 2,
                                                    pointRadius: 0,
                                                    pointHoverRadius: 4,
                                                    tension: 0.3
                                                }
                                            ]
                                        },
                                        options: {
                                            responsive: true,
                                            interaction: { mode: 'index', intersect: false },
                                            scales: {
                                                x: {
                                                    ticks: {
                                                        autoSkip: true,
                                                        maxTicksLimit: 15,
                                                        maxRotation: 45,
                                                        minRotation: 45
                                                    }
                                                }
                                            },
                                            plugins: {
                                                tooltip: {
                                                    callbacks: {
                                                        label: function(context) {
                                                            let label = context.dataset.label || '';
                                                            if (label) { label += ': '; }
                                                            if (context.parsed.y !== null) {
                                                                label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                                                            }
                                                            return label;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });

                                    btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">check_circle</span> Cập nhật thành công!';
                                    setTimeout(() => { 
                                        btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">magic_button</span> Chạy AI Dự Báo'; 
                                        btn.disabled = false; 
                                    }, 3000);
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert("Có lỗi xảy ra: " + error.message);
                                    btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">magic_button</span> Chạy AI Dự Báo'; 
                                    btn.disabled = false;
                                });
                        });
                    });
                </script>

            <?php elseif ($action === 'products'): ?>
                <!-- Products Management -->
                <?php include __DIR__ . '/admin-products.php'; ?>

            <?php elseif ($action === 'categories'): ?>
                <!-- Categories Management -->
                <?php include __DIR__ . '/admin-categories.php'; ?>

            <?php elseif ($action === 'brands'): ?>
                <!-- Brands Management -->
                <?php include __DIR__ . '/admin-brands.php'; ?>

            <?php elseif ($action === 'orders'): ?>
                <!-- Orders Management -->
                <?php include __DIR__ . '/admin-orders.php'; ?>

            <?php elseif ($action === 'users'): ?>
                <!-- Users Management -->
                <?php include __DIR__ . '/admin-users.php'; ?>

            <?php elseif ($action === 'shipping_price'): ?>
                <!-- Shipping Price Management -->
                <?php include __DIR__ . '/admin-shipping-price.php'; ?>

            <?php elseif ($action === 'reviews'): ?>
                <!-- Reviews Management -->
                <?php include __DIR__ . '/admin-reviews.php'; ?>

            <?php elseif ($action === 'promotions'): ?>
                <!-- Promotions Management -->
                <?php include __DIR__ . '/admin-promotions.php'; ?>

            <?php elseif ($action === 'banners'): ?>
                <!-- Banners Management -->
                <?php include __DIR__ . '/admin-banners.php'; ?>

            <?php elseif ($action === 'articles'): ?>
                <!-- Articles Management -->
                <?php include __DIR__ . '/admin-articles.php'; ?>

            <?php elseif ($action === 'featured'): ?>
                <!-- Featured Products Management -->
                <?php include __DIR__ . '/admin-featured.php'; ?>

            <?php elseif ($action === 'settings'): ?>
                <!-- Site Settings Management -->
                <?php include __DIR__ . '/admin-settings.php'; ?>

            <?php elseif ($action === 'analytics'): ?>
                <!-- Analytics & Reports -->
                <?php include __DIR__ . '/admin-analytics.php'; ?>

            <?php elseif ($action === 'customer_detail'): ?>
                <!-- Customer Detail -->
                <?php include __DIR__ . '/admin-customer-detail.php'; ?>

            <?php elseif ($action === 'product_detail'): ?>
                <!-- Product Detail Insight -->
                <?php include __DIR__ . '/admin-product-detail.php'; ?>

            <?php elseif ($action === 'promotion_detail'): ?>
                <!-- Promotion Detail -->
                <?php include __DIR__ . '/admin-promotion-detail.php'; ?>

            <?php else: ?>
                <div class="bg-white rounded-xl p-12 text-center">
                    <span class="material-symbols-outlined text-6xl text-gray-300">construction</span>
                    <h2 class="text-xl font-bold text-gray-600 mt-4">Tính năng đang được phát triển</h2>
                    <p class="text-gray-500 mt-2">Chức năng này sẽ sớm được cập nhật.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Container -->
    <div id="modal-container"></div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed inset-0 pointer-events-none z-[9999] flex flex-col items-center justify-center gap-4"></div>

    <script>
        // Toggle Sidebar Mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Toggle Sidebar Desktop
        function toggleSidebarDesktop() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            
            // Lưu trạng thái vào localStorage để ghi nhớ
            if (sidebar.classList.contains('collapsed')) {
                localStorage.setItem('admin_sidebar_collapsed', 'true');
            } else {
                localStorage.setItem('admin_sidebar_collapsed', 'false');
            }
        }
        
        // Khôi phục trạng thái sidebar khi load trang
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('admin_sidebar_collapsed') === 'true' && window.innerWidth >= 1024) {
                document.getElementById('sidebar').classList.add('collapsed');
            }
        });

        // Toast notification (Centered Modal Style)
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            
            // Xóa các thông báo cũ để không bị lặp hiển thị nhiều lần
            container.innerHTML = '';

            const toast = document.createElement('div');

            const icon = type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info';
            const iconColor = type === 'success' ? 'text-green-500' : type === 'error' ? 'text-red-500' : 'text-blue-500';
            const bgColor = 'bg-white';

            toast.className = `${bgColor} border border-gray-100 pointer-events-auto px-8 py-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] flex flex-col items-center gap-3 transform transition-all duration-300 scale-95 opacity-0 min-w-[320px] text-center`;
            toast.innerHTML = `
                <span class="material-symbols-outlined text-[48px] ${iconColor}">${icon}</span>
                <span class="text-gray-800 font-semibold text-lg leading-tight">${message}</span>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('scale-95', 'opacity-0');
                toast.classList.add('scale-100', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('scale-100', 'opacity-100');
                toast.classList.add('scale-95', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Modal functions
        function openModal(content) {
            const container = document.getElementById('modal-container');
            container.innerHTML = `
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9000] flex items-center justify-center p-4 transition-opacity duration-300" onclick="closeModal()">
                    <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-transform duration-300 scale-95" onclick="event.stopPropagation()" id="modal-content-wrapper">
                        ${content}
                    </div>
                </div>
            `;
            requestAnimationFrame(() => {
                const wrapper = document.getElementById('modal-content-wrapper');
                if (wrapper) {
                    wrapper.classList.remove('scale-95');
                    wrapper.classList.add('scale-100');
                }
            });
        }

        function closeModal() {
            document.getElementById('modal-container').innerHTML = '';
        }

        // Global Confirm Modal
        function showConfirm(message, onConfirm) {
            const content = `
                <div class="p-8 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-yellow-50 text-yellow-500 mb-5 border border-yellow-100 shadow-sm">
                        <span class="material-symbols-outlined text-[40px]">warning</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3" style="font-family: 'Montserrat', sans-serif;">Xác nhận thao tác</h3>
                    <p class="text-gray-500 mb-8 text-base px-4">${message}</p>
                    <div class="flex justify-center gap-4">
                        <button onclick="closeModal()" class="px-6 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-semibold transition-colors w-32">
                            Hủy bỏ
                        </button>
                        <button id="confirm-ok-btn" class="px-6 py-3 bg-axeron-red text-white rounded-xl hover:bg-red-700 font-semibold transition-colors shadow-md w-32">
                            Đồng ý
                        </button>
                    </div>
                </div>
            `;
            openModal(content);
            document.getElementById('confirm-ok-btn').addEventListener('click', () => {
                closeModal();
                if (typeof onConfirm === 'function') onConfirm();
            });
        }

        // Confirm delete
        function confirmDelete(url, message = 'Bạn có chắc chắn muốn xóa?') {
            showConfirm(message, () => {
                window.location.href = url;
            });
        }
    </script>
</body>
</html>
