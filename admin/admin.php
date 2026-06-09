<?php
/**
 * Admin Dashboard - Axeron Sports Shop
 * Trang quản trị chính
 */
require_once __DIR__ . /../config/database.php';
require_once __DIR__ . /../config/session.php';

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
    $allActions = ['dashboard', 'products', 'categories', 'brands', 'orders', 'users', 'shipping_price', 'reviews', 'promotions', 'analytics', 'banners', 'articles', 'featured', 'settings'];
    foreach ($allActions as $act) {
        if (hasPermission($act)) {
            header('Location: ' . BASE_URL . '/admin.php?action=' . $act);
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

    $stats = [
        'todayOrders' => $todayOrders['count'] ?? 0,
        'todayRevenue' => $todayOrders['revenue'] ?? 0,
        'pendingOrders' => $pendingOrders['count'] ?? 0,
        'totalProducts' => $totalProducts['count'] ?? 0,
        'totalCustomers' => $totalCustomers['count'] ?? 0,
        'monthlyRevenue' => $monthlyRevenue['revenue'] ?? 0,
        'pendingReviews' => $pendingReviews['count'] ?? 0,
        'recentOrders' => $recentOrders,
        'topProducts' => $topProducts
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - <?= $pageTitle ?> | Axeron Sport</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
        window.getImageUrl = function(url, defaultUrl = '') {
            if (!url) return defaultUrl;
            if (/^https?:\/\//i.test(url)) return url;
            let cleanUrl = url.replace(/^\/+/, '');
            return window.BASE_URL.replace(/\/+$/, '') + '/' + cleanUrl;
        };
    </script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <style>
        body { font-family: 'Noto Sans', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-link.active { background: linear-gradient(90deg, #BE1E2D 0%, #d32f2f 100%); color: white; }
        .sidebar-link:hover:not(.active) { background-color: #2d2d2d; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-dark text-white flex-shrink-0">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-700">
                <a href="<?= BASE_URL ?>/admin.php" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-axeron-red rounded-lg flex items-center justify-center font-bold text-xl">A</div>
                    <div>
                        <div class="font-bold text-lg">Axeron</div>
                        <div class="text-xs text-gray-400">Admin Panel</div>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <?php if (hasPermission('dashboard')): ?>
                <a href="<?= BASE_URL ?>/admin.php" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'dashboard' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Tổng Quan</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('products')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=products" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'products' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>Sản Phẩm</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('categories')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=categories" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'categories' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">category</span>
                    <span>Danh Mục</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('brands')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=brands" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'brands' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">branding_watermark</span>
                    <span>Thương Hiệu</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('orders')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=orders" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'orders' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">receipt_long</span>
                    <span>Đơn Hàng</span>
                    <?php if (($stats['pendingOrders'] ?? 0) > 0): ?>
                    <span class="ml-auto bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['pendingOrders'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('users')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=users" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'users' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">people</span>
                    <span>Người Dùng</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('shipping_price')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=shipping_price" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'shipping_price' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">local_shipping</span>
                    <span>Phí Vận Chuyển</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('reviews')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=reviews" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'reviews' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">reviews</span>
                    <span>Đánh Giá</span>
                    <?php if (($stats['pendingReviews'] ?? 0) > 0): ?>
                    <span class="ml-auto bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $stats['pendingReviews'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('promotions')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=promotions" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'promotions' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">sell</span>
                    <span>Khuyến Mãi</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('analytics')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=analytics" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'analytics' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">analytics</span>
                    <span>Thống Kê</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('banners') || hasPermission('articles') || hasPermission('featured') || hasPermission('settings')): ?>
                <div class="border-t border-gray-700 my-4"></div>
                <!-- CMS Section -->
                <p class="px-4 text-xs text-gray-500 uppercase tracking-wider mb-2">Nội dung CMS</p>

                <?php if (hasPermission('banners')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=banners" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'banners' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">image</span>
                    <span>Banner/Slider</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('articles')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=articles" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'articles' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">article</span>
                    <span>Bài Viết</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('featured')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=featured" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'featured' ? 'active' : '' ?>">
                    <span class="material-symbols-outlined">star</span>
                    <span>SP Nổi Bật</span>
                </a>
                <?php endif; ?>

                <?php if (hasPermission('settings')): ?>
                <a href="<?= BASE_URL ?>/admin.php?action=settings" class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $action === 'settings' ? 'active' : '' ?>">
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
        <main class="flex-1 p-6">
            <!-- Header -->
            <header class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800"><?= $pageTitle ?></h1>
                    <p class="text-gray-500 text-sm">Xin chào, <?= htmlspecialchars($currentUser['full_name']) ?>!</p>
                </div>
                <div class="flex items-center gap-4">
                    <a href="<?= BASE_URL ?>/shop/product-catalog.php" target="_blank" class="text-gray-500 hover:text-axeron-red">
                        <span class="material-symbols-outlined">open_in_new</span>
                    </a>
                    <div class="relative">
                        <div class="w-10 h-10 bg-axeron-red rounded-full flex items-center justify-center text-white font-bold">
                            <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                        </div>
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
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Đơn hàng hôm nay</p>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['todayOrders']) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">shopping_cart</span>
                            </div>
                        </div>
                        <p class="text-green-600 text-sm mt-2">Doanh thu: <?= formatPrice($stats['todayRevenue']) ?></p>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Chờ xử lý</p>
                                <p class="text-3xl font-bold text-yellow-600"><?= number_format($stats['pendingOrders']) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-yellow-600">pending_actions</span>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/admin.php?action=orders" class="text-axeron-red text-sm hover:underline mt-2 inline-block">Xem ngay →</a>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Doanh thu tháng</p>
                                <p class="text-2xl font-bold text-green-600"><?= formatPrice($stats['monthlyRevenue']) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-600">payments</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm">Khách hàng</p>
                                <p class="text-3xl font-bold text-gray-800"><?= number_format($stats['totalCustomers']) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="material-symbols-outlined text-purple-600">people</span>
                            </div>
                        </div>
                        <a href="<?= BASE_URL ?>/admin.php?action=users" class="text-axeron-red text-sm hover:underline mt-2 inline-block">Quản lý →</a>
                    </div>
                </div>

                <!-- Recent Orders & Top Products -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Orders -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="font-bold text-lg">Đơn hàng gần đây</h2>
                            <a href="<?= BASE_URL ?>/admin.php?action=orders" class="text-axeron-red text-sm hover:underline">Xem tất cả</a>
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
                                        <td class="px-4 py-3 text-sm font-medium">#<?= $order['order_id'] ?></td>
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

                    <!-- Top Products -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h2 class="font-bold text-lg">Sản phẩm bán chạy</h2>
                            <a href="<?= BASE_URL ?>/admin.php?action=products" class="text-axeron-red text-sm hover:underline">Xem tất cả</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php foreach ($stats['topProducts'] as $product): ?>
                            <div class="p-4 flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800"><?= htmlspecialchars($product['product_name']) ?></p>
                                    <p class="text-sm text-gray-500">Đã bán: <?= number_format($product['sold']) ?> sản phẩm</p>
                                </div>
                                <p class="font-bold text-axeron-red"><?= formatPrice($product['revenue']) ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php if (empty($stats['topProducts'])): ?>
                            <div class="p-8 text-center text-gray-500">Chưa có dữ liệu</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

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
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');

            const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-axeron-blue';
            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3`;
            toast.innerHTML = `<span>${message}</span>`;

            container.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Modal functions
        function openModal(content) {
            const container = document.getElementById('modal-container');
            container.innerHTML = `
                <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="closeModal()">
                    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
                        ${content}
                    </div>
                </div>
            `;
        }

        function closeModal() {
            document.getElementById('modal-container').innerHTML = '';
        }

        // Confirm delete
        function confirmDelete(url, message = 'Bạn có chắc chắn muốn xóa?') {
            if (confirm(message)) {
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
