<?php
/**
 * Analytics API - Axeron Sports Shop
 * Xử lý dữ liệu thống kê và báo cáo
 */

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/analytics_errors.log');

// Clean any output buffer
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/session.php';

    // Kiểm tra đăng nhập
    if (!isLoggedIn()) {
        jsonResponse(false, 'Vui lòng đăng nhập');
    }

    if (!hasPermission('analytics')) {
        header('HTTP/1.1 403 Forbidden');
        jsonResponse(false, 'Bạn không có quyền truy cập');
    }

    $db = db();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'summary':
                getSummary($db);
                break;
            case 'customers':
                getCustomerStats($db);
                break;
            case 'products':
                getProductStats($db);
                break;
            case 'product_detail':
                getProductDetail($db);
                break;
            case 'revenue':
                getRevenueStats($db);
                break;
            default:
                jsonResponse(false, 'Invalid action');
        }
    }
} catch (Throwable $e) {
    error_log('Analytics API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    jsonResponse(false, 'Lỗi server: ' . $e->getMessage());
}

/**
 * Lấy tổng hợp summary
 */
function getSummary($db) {
    $period = $_GET['period'] ?? 'month';
    $dateRange = getDateRange($period, $_GET);

    // Tổng doanh thu
    $revenue = $db->selectOne("
        SELECT COALESCE(SUM(total_amount), 0) as total
        FROM orders
        WHERE order_status NOT IN ('cancelled', 'returned')
        AND payment_status = 'paid'
        AND created_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);

    // Tổng đơn hàng
    $orders = $db->selectOne("
        SELECT COUNT(*) as total
        FROM orders
        WHERE order_status NOT IN ('cancelled', 'returned')
        AND created_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);

    // Số khách hàng có mua
    $customers = $db->selectOne("
        SELECT COUNT(DISTINCT user_id) as total
        FROM orders
        WHERE order_status NOT IN ('cancelled', 'returned')
        AND user_id IS NOT NULL
        AND created_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);

    // Lượt xem sản phẩm để tính Tỷ lệ chuyển đổi
    $views = $db->selectOne("
        SELECT COUNT(*) as total
        FROM product_view_logs
        WHERE viewed_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);
    $totalViews = (int)$views['total'];
    
    // Conversion rate (Tỷ lệ chuyển đổi)
    $conversionRate = $totalViews > 0 ? ($orders['total'] / $totalViews) * 100 : 0;

    // AOV (Average Order Value)
    $aov = $orders['total'] > 0 ? $revenue['total'] / $orders['total'] : 0;

    jsonResponse(true, 'Success', [
        'revenue' => (float)$revenue['total'],
        'revenue_formatted' => formatPrice($revenue['total']),
        'orders' => (int)$orders['total'],
        'customers' => (int)$customers['total'],
        'aov' => (float)$aov,
        'aov_formatted' => formatPrice($aov),
        'conversion_rate' => round($conversionRate, 2),
        'period' => $period,
        'date_range' => $dateRange
    ]);
}

/**
 * Thống kê theo khách hàng (xếp hạng thân thiết)
 */
function getCustomerStats($db) {
    $period = $_GET['period'] ?? 'month';
    $search = sanitize($_GET['search'] ?? '');
    $sort = $_GET['sort'] ?? 'total_spent';
    $order = $_GET['order'] ?? 'desc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $rankFilter = sanitize($_GET['rank'] ?? '');
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Build Cache Key (include all filter params)
    $year = $_GET['year'] ?? date('Y');
    $month = $_GET['month'] ?? date('n');
    $quarter = $_GET['quarter'] ?? '';
    $cacheKey = "customer_report_" . md5(json_encode([$period, $year, $month, $quarter, $search, $sort, $order, $page, $rankFilter]));
    if (isset($_SESSION[$cacheKey]) && isset($_SESSION[$cacheKey . '_time'])) {
        if (time() - $_SESSION[$cacheKey . '_time'] < 900) { // 15 phút
            jsonResponse(true, 'Success (Cached)', $_SESSION[$cacheKey]);
        }
    }

    $dateRange = getDateRange($period, $_GET);

    // Validate sort column
    $allowedSorts = ['total_spent', 'total_orders', 'avg_order_value', 'full_name', 'last_order_date'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'total_spent';
    }
    $order = $order === 'asc' ? 'ASC' : 'DESC';

    // Build WHERE clause for users
    $userWhere = "u.role_id = 3";
    $searchParams = [];
    if ($search) {
        $userWhere .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $searchParams = ["%$search%", "%$search%", "%$search%"];
    }

    $rankSql = "
        CASE
            WHEN COALESCE(SUM(o.total_amount), 0) > 5000000 OR COUNT(o.order_id) > 5 THEN 'VIP'
            WHEN COALESCE(SUM(o.total_amount), 0) >= 2000000 THEN 'Tiềm năng'
            WHEN COUNT(o.order_id) = 1 THEN 'Mới'
            ELSE 'Bình thường'
        END
    ";

    $havingClause = "HAVING total_orders > 0";
    $havingParams = [];
    if ($rankFilter) {
        $havingClause .= " AND customer_rank = ?";
        $havingParams[] = $rankFilter;
    }

    // Count total with having (Need subquery)
    $countSql = "
        SELECT COUNT(*) as total FROM (
            SELECT u.user_id, $rankSql as customer_rank, COUNT(o.order_id) as total_orders
            FROM users u
            LEFT JOIN orders o ON u.user_id = o.user_id
                AND o.order_status NOT IN ('cancelled', 'returned')
                AND o.payment_status = 'paid'
                AND o.created_at BETWEEN ? AND ?
            WHERE $userWhere
            GROUP BY u.user_id
            $havingClause
        ) as t
    ";
    $countParams = array_merge([$dateRange['start'], $dateRange['end']], $searchParams, $havingParams);
    $totalResult = $db->selectOne($countSql, $countParams);
    $totalItems = (int)($totalResult['total'] ?? 0);

    // Get data
    $dataSql = "
        SELECT
            u.user_id,
            u.full_name,
            u.email,
            u.phone,
            u.avatar_url,
            COUNT(o.order_id) AS total_orders,
            COALESCE(SUM(o.total_amount), 0) AS total_spent,
            COALESCE(AVG(o.total_amount), 0) AS avg_order_value,
            MAX(o.created_at) AS last_order_date,
            $rankSql AS customer_rank
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
            AND o.order_status NOT IN ('cancelled', 'returned')
            AND o.payment_status = 'paid'
            AND o.created_at BETWEEN ? AND ?
        WHERE $userWhere
        GROUP BY u.user_id, u.full_name, u.email, u.phone, u.avatar_url
        $havingClause
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ";
    $dataParams = array_merge([$dateRange['start'], $dateRange['end']], $searchParams, $havingParams, [$perPage, $offset]);
    $customers = $db->select($dataSql, $dataParams);

    // Format data
    foreach ($customers as &$c) {
        $c['total_spent_formatted'] = formatPrice($c['total_spent']);
        $c['avg_order_value_formatted'] = formatPrice($c['avg_order_value']);
        $c['rank'] = $sort === 'total_spent' && $order === 'DESC'
            ? array_search($c['user_id'], array_column($customers, 'user_id')) + 1 + $offset
            : null;
    }

    // -- Calculate Customer Specific Summary --
    $allCustomersSql = "
        SELECT u.user_id, $rankSql as customer_rank, COUNT(o.order_id) as total_orders
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
            AND o.order_status NOT IN ('cancelled', 'returned')
            AND o.payment_status = 'paid'
            AND o.created_at BETWEEN ? AND ?
        WHERE $userWhere
        GROUP BY u.user_id
        HAVING total_orders > 0
    ";
    $allParams = array_merge([$dateRange['start'], $dateRange['end']], $searchParams);
    $allCustomers = $db->select($allCustomersSql, $allParams);

    $totalCus = count($allCustomers);
    $vipCount = 0;
    $newCount = 0;
    $returningCount = 0;
    $segmentationData = [
        'VIP' => 0, 'Tiềm năng' => 0, 'Mới' => 0, 'Bình thường' => 0
    ];

    foreach ($allCustomers as $ac) {
        $r = $ac['customer_rank'];
        if (isset($segmentationData[$r])) $segmentationData[$r]++;
        
        if ($r === 'VIP') $vipCount++;
        if ($r === 'Mới') $newCount++;
        if ($ac['total_orders'] > 1) $returningCount++;
    }

    $payload = [
        'customers' => $customers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $perPage),
            'total_items' => $totalItems,
            'per_page' => $perPage
        ],
        'customer_summary' => [
            'total_customers' => $totalCus,
            'new_customers' => $newCount,
            'vip_customers' => $vipCount,
            'returning_customers' => $returningCount
        ],
        'charts' => [
            'segmentation' => $segmentationData
        ],
        'filters' => [
            'period' => $period,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'rank' => $rankFilter,
            'date_range' => $dateRange
        ]
    ];

    // Save to Cache
    $_SESSION[$cacheKey] = $payload;
    $_SESSION[$cacheKey . '_time'] = time();

    jsonResponse(true, 'Success', $payload);
}

/**
 * Thống kê theo sản phẩm (nâng cấp: views, rating, stock, conversion, classification)
 */
function getProductStats($db) {
    $period = $_GET['period'] ?? 'month';
    $dateRange = getDateRange($period, $_GET);

    $search = sanitize($_GET['search'] ?? '');
    $categoryId = (int)($_GET['category_id'] ?? 0);
    $brandId = (int)($_GET['brand_id'] ?? 0);
    $stockStatus = sanitize($_GET['stock_status'] ?? '');
    $performance = sanitize($_GET['performance'] ?? '');
    $sort = $_GET['sort'] ?? 'total_sold';
    $order = $_GET['order'] ?? 'desc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Validate sort column
    $allowedSorts = ['total_sold', 'total_revenue', 'avg_price', 'product_name', 'view_count', 'avg_rating', 'stock_quantity'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'total_sold';
    }
    $order = $order === 'asc' ? 'ASC' : 'DESC';

    // Build WHERE clause for orders
    $orderWhere = "o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid' AND o.created_at BETWEEN ? AND ?";
    $baseParams = [$dateRange['start'], $dateRange['end']];

    // Product filters
    $productWhere = "1=1";
    $productParams = [];
    if ($search) {
        $productWhere .= " AND p.product_name LIKE ?";
        $productParams[] = "%$search%";
    }
    if ($categoryId > 0) {
        $productWhere .= " AND p.category_id = ?";
        $productParams[] = $categoryId;
    }
    if ($brandId > 0) {
        $productWhere .= " AND p.brand_id = ?";
        $productParams[] = $brandId;
    }

    // Main query: sales + views + ratings + stock
    $dataSql = "
        SELECT
            p.product_id,
            p.product_name,
            p.slug,
            p.base_price,
            p.stock_quantity,
            c.category_name,
            b.brand_name,
            COALESCE(sales.total_sold, 0) AS total_sold,
            COALESCE(sales.total_revenue, 0) AS total_revenue,
            COALESCE(sales.avg_price, 0) AS avg_price,
            COALESCE(sales.order_count, 0) AS order_count,
            COALESCE(views.view_count, 0) AS view_count,
            COALESCE(p.avg_rating, 0) AS avg_rating,
            COALESCE(p.total_reviews, 0) AS review_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN (
            SELECT pv2.product_id,
                   SUM(oi.quantity) AS total_sold,
                   SUM(oi.subtotal) AS total_revenue,
                   AVG(oi.unit_price) AS avg_price,
                   COUNT(DISTINCT o.order_id) AS order_count
            FROM order_items oi
            JOIN product_variants pv2 ON oi.variant_id = pv2.variant_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE $orderWhere
            GROUP BY pv2.product_id
        ) sales ON p.product_id = sales.product_id
        LEFT JOIN (
            SELECT product_id, COUNT(*) AS view_count
            FROM product_view_logs
            WHERE viewed_at BETWEEN ? AND ?
            GROUP BY product_id
        ) views ON p.product_id = views.product_id
        WHERE $productWhere AND p.is_deleted = 0
    ";
    $queryParams = array_merge($baseParams, $baseParams, $productParams);

    // Stock status filter
    if ($stockStatus === 'out') {
        $dataSql .= " AND p.stock_quantity = 0";
    } elseif ($stockStatus === 'low') {
        $dataSql .= " AND p.stock_quantity > 0 AND p.stock_quantity <= 10";
    } elseif ($stockStatus === 'available') {
        $dataSql .= " AND p.stock_quantity > 10";
    }

    // Performance filter (applied after HAVING)
    $havingClause = "";
    if ($performance === 'hot') {
        $havingClause = " HAVING total_sold > 50";
    } elseif ($performance === 'cold') {
        $havingClause = " HAVING total_sold < 5";
    }

    // Wrap for count
    $countSql = "SELECT COUNT(*) as total FROM ($dataSql $havingClause) as sub";
    $totalResult = $db->selectOne($countSql, $queryParams);
    $totalItems = (int)($totalResult['total'] ?? 0);

    // Final query with sort + pagination
    $finalSql = $dataSql . $havingClause . " ORDER BY $sort $order LIMIT ? OFFSET ?";
    $queryParams[] = $perPage;
    $queryParams[] = $offset;
    $products = $db->select($finalSql, $queryParams);

    // Calculate previous period for trending detection
    $prevRange = getPreviousDateRange($period, $_GET);
    $prevSoldMap = [];
    if ($prevRange) {
        $prevSales = $db->select("
            SELECT pv2.product_id, SUM(oi.quantity) AS prev_sold
            FROM order_items oi
            JOIN product_variants pv2 ON oi.variant_id = pv2.variant_id
            JOIN orders o ON oi.order_id = o.order_id
            WHERE o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid'
              AND o.created_at BETWEEN ? AND ?
            GROUP BY pv2.product_id
        ", [$prevRange['start'], $prevRange['end']]);
        foreach ($prevSales as $ps) {
            $prevSoldMap[$ps['product_id']] = (int)$ps['prev_sold'];
        }
    }

    // Classify products + format
    $totalSoldAll = 0;
    $totalRevenueAll = 0;
    $totalViewsAll = 0;
    $bestSellerName = 'N/A';
    $bestSellerSold = 0;
    $slowCount = 0;

    foreach ($products as &$p) {
        $totalSoldAll += (int)$p['total_sold'];
        $totalRevenueAll += (float)$p['total_revenue'];
        $totalViewsAll += (int)$p['view_count'];

        if ((int)$p['total_sold'] > $bestSellerSold) {
            $bestSellerSold = (int)$p['total_sold'];
            $bestSellerName = $p['product_name'];
        }

        // Conversion rate
        $p['conversion_rate'] = $p['view_count'] > 0 ? round(($p['order_count'] / $p['view_count']) * 100, 1) : 0;

        // Classification
        $prevSold = $prevSoldMap[$p['product_id']] ?? 0;
        $currentSold = (int)$p['total_sold'];

        if ($currentSold > 50) {
            $p['status'] = 'hot';
            $p['status_label'] = '🔥 Best Seller';
        } elseif ($prevSold > 0 && $currentSold > 0 && (($currentSold - $prevSold) / $prevSold) > 0.3) {
            $p['status'] = 'trending';
            $p['status_label'] = '🚀 Trending';
        } elseif ($currentSold < 5) {
            $p['status'] = 'cold';
            $p['status_label'] = '📉 Chậm';
            $slowCount++;
        } else {
            $p['status'] = 'normal';
            $p['status_label'] = '📊 Bình thường';
        }

        $p['total_revenue_formatted'] = formatPrice($p['total_revenue']);
        $p['avg_price_formatted'] = formatPrice($p['avg_price']);
        $p['base_price_formatted'] = formatPrice($p['base_price']);
        $p['sold_percentage'] = $totalSoldAll > 0 ? round(($p['total_sold'] / max($totalSoldAll, 1)) * 100, 1) : 0;
    }
    unset($p);

    // Filter by trending performance (post-processing since it needs prev data)
    if ($performance === 'trending') {
        $products = array_values(array_filter($products, fn($p) => $p['status'] === 'trending'));
        $totalItems = count($products);
    }

    // Charts data (top 10 for each)
    $allProductsParams = array_merge($baseParams, $baseParams, $productParams);
    $allProductsSql = $dataSql . " ORDER BY total_sold DESC";
    $allProducts = $db->select($allProductsSql, $allProductsParams);

    $topRevenue = array_slice(
        array_map(fn($p) => ['name' => $p['product_name'], 'value' => (float)$p['total_revenue']],
            (function($arr) { usort($arr, fn($a, $b) => $b['total_revenue'] <=> $a['total_revenue']); return $arr; })($allProducts)),
        0, 10
    );
    $topSellers = array_slice(
        array_map(fn($p) => ['name' => $p['product_name'], 'value' => (int)$p['total_sold']],
            $allProducts),
        0, 10
    );
    // Top viewed
    $allViewProducts = (function($arr) { usort($arr, fn($a, $b) => $b['view_count'] <=> $a['view_count']); return $arr; })($allProducts);
    $topViewed = array_slice(
        array_map(fn($p) => ['name' => $p['product_name'], 'value' => (int)$p['view_count']], $allViewProducts),
        0, 10
    );

    // Get categories + brands for filter
    $categories = $db->select("SELECT category_id, category_name FROM categories WHERE is_visible = 1 ORDER BY category_name");
    $brands = $db->select("SELECT brand_id, brand_name FROM brands ORDER BY brand_name");

    jsonResponse(true, 'Success', [
        'products' => $products,
        'categories' => $categories,
        'brands' => $brands,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => max(1, ceil($totalItems / $perPage)),
            'total_items' => $totalItems,
            'per_page' => $perPage
        ],
        'product_summary' => [
            'total_sold' => $totalSoldAll,
            'total_revenue' => $totalRevenueAll,
            'total_revenue_formatted' => formatPrice($totalRevenueAll),
            'best_seller_name' => $bestSellerName,
            'slow_mover_count' => $slowCount,
            'total_views' => $totalViewsAll
        ],
        'charts' => [
            'top_revenue' => $topRevenue,
            'top_sellers' => $topSellers,
            'top_viewed' => $topViewed
        ],
        'filters' => [
            'period' => $period,
            'search' => $search,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'sort' => $sort,
            'order' => $order,
            'date_range' => $dateRange
        ]
    ]);
}

/**
 * Chi tiết insight cho 1 sản phẩm
 */
function getProductDetail($db) {
    $productId = (int)($_GET['id'] ?? 0);
    if ($productId <= 0) {
        jsonResponse(false, 'Thiếu product_id');
    }

    // Thông tin sản phẩm
    $product = $db->selectOne("
        SELECT p.*, c.category_name, b.brand_name,
               (SELECT GROUP_CONCAT(pi.image_url SEPARATOR '||') FROM product_images pi WHERE pi.product_id = p.product_id AND pi.is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.product_id = ?
    ", [$productId]);

    if (!$product) {
        jsonResponse(false, 'Không tìm thấy sản phẩm');
    }

    // Hiệu suất tổng
    $performance = $db->selectOne("
        SELECT
            COALESCE(SUM(oi.quantity), 0) AS total_sold,
            COALESCE(SUM(oi.subtotal), 0) AS total_revenue,
            COUNT(DISTINCT o.order_id) AS order_count
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE pv.product_id = ? AND o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid'
    ", [$productId]);

    // Lượt xem
    $views = $db->selectOne("SELECT COUNT(*) as total FROM product_view_logs WHERE product_id = ?", [$productId]);
    $viewCount = (int)($views['total'] ?? 0);
    $conversionRate = $viewCount > 0 ? round(($performance['order_count'] / $viewCount) * 100, 1) : 0;

    // Doanh thu theo tháng (năm nay)
    $monthlyRevenue = $db->select("
        SELECT MONTH(o.created_at) as month, SUM(oi.subtotal) as revenue
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE pv.product_id = ? AND o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid'
          AND YEAR(o.created_at) = YEAR(CURDATE())
        GROUP BY MONTH(o.created_at)
    ", [$productId]);
    $chartMap = [];
    foreach ($monthlyRevenue as $r) { $chartMap[$r['month']] = (float)$r['revenue']; }
    $chartValues = [];
    for ($i = 1; $i <= 12; $i++) { $chartValues[] = $chartMap[$i] ?? 0; }

    // Top 5 khách xem nhiều nhất
    $topViewers = $db->select("
        SELECT u.user_id, u.full_name, u.email, COUNT(*) as view_count
        FROM product_view_logs pv
        JOIN users u ON pv.user_id = u.user_id
        WHERE pv.product_id = ?
        GROUP BY u.user_id, u.full_name, u.email
        ORDER BY view_count DESC LIMIT 5
    ", [$productId]);

    // Từ khóa tìm kiếm liên quan
    $relatedKeywords = $db->select("
        SELECT keyword, COUNT(*) as search_count
        FROM search_logs
        WHERE keyword LIKE ? OR keyword LIKE ?
        GROUP BY keyword
        ORDER BY search_count DESC LIMIT 10
    ", ['%' . substr($product['product_name'], 0, 10) . '%', '%' . ($product['category_name'] ?? '') . '%']);

    // Sản phẩm thường mua kèm
    $boughtTogether = $db->select("
        SELECT p2.product_id, p2.product_name, p2.base_price, COUNT(*) as together_count
        FROM order_items oi1
        JOIN order_items oi2 ON oi1.order_id = oi2.order_id AND oi1.order_item_id != oi2.order_item_id
        JOIN product_variants pv1 ON oi1.variant_id = pv1.variant_id
        JOIN product_variants pv2 ON oi2.variant_id = pv2.variant_id
        JOIN products p2 ON pv2.product_id = p2.product_id
        WHERE pv1.product_id = ? AND p2.product_id != ?
        GROUP BY p2.product_id, p2.product_name, p2.base_price
        ORDER BY together_count DESC LIMIT 5
    ", [$productId, $productId]);

    // 5 Review gần nhất
    $reviews = $db->select("
        SELECT r.rating, r.comment, r.created_at, u.full_name
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.product_id = ? AND r.status = 'approved' AND r.is_deleted = 0
        ORDER BY r.created_at DESC LIMIT 5
    ", [$productId]);

    jsonResponse(true, 'Success', [
        'product' => $product,
        'performance' => [
            'total_sold' => (int)$performance['total_sold'],
            'total_revenue' => (float)$performance['total_revenue'],
            'total_revenue_formatted' => formatPrice($performance['total_revenue']),
            'order_count' => (int)$performance['order_count'],
            'view_count' => $viewCount,
            'conversion_rate' => $conversionRate,
            'avg_rating' => (float)$product['avg_rating'],
            'review_count' => (int)$product['total_reviews']
        ],
        'chart_data' => $chartValues,
        'top_viewers' => $topViewers,
        'related_keywords' => $relatedKeywords,
        'bought_together' => $boughtTogether,
        'reviews' => $reviews
    ]);
}

/**
 * Tính khoảng thời gian trước đó (để so sánh trending)
 */
function getPreviousDateRange($period, $params) {
    $year = (int)($params['year'] ?? date('Y'));
    $month = (int)($params['month'] ?? date('n'));
    $quarter = (int)($params['quarter'] ?? ceil($month / 3));

    switch ($period) {
        case 'month':
            $prevMonth = $month - 1;
            $prevYear = $year;
            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
            $days = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
            return [
                'start' => sprintf("%04d-%02d-01 00:00:00", $prevYear, $prevMonth),
                'end' => sprintf("%04d-%02d-%02d 23:59:59", $prevYear, $prevMonth, $days)
            ];
        case 'quarter':
            $prevQ = $quarter - 1;
            $prevY = $year;
            if ($prevQ < 1) { $prevQ = 4; $prevY--; }
            $ms = ($prevQ - 1) * 3 + 1;
            $me = $ms + 2;
            return [
                'start' => sprintf("%04d-%02d-01 00:00:00", $prevY, $ms),
                'end' => sprintf("%04d-%02d-%02d 23:59:59", $prevY, $me, cal_days_in_month(CAL_GREGORIAN, $me, $prevY))
            ];
        case 'year':
            return [
                'start' => ($year - 1) . "-01-01 00:00:00",
                'end' => ($year - 1) . "-12-31 23:59:59"
            ];
        default:
            return null;
    }
}

/**
 * Thống kê doanh thu theo thời gian
 */
function getRevenueStats($db) {
    $period = $_GET['period'] ?? 'month';
    $year = (int)($_GET['year'] ?? date('Y'));
    $quarter = (int)($_GET['quarter'] ?? 0);

    $data = [];

    if ($period === 'year') {
        // Monthly breakdown for the year
        $data = $db->select("
            SELECT
                MONTH(created_at) AS month,
                COUNT(order_id) AS total_orders,
                SUM(total_amount) AS revenue,
                AVG(total_amount) AS avg_order_value
            FROM orders
            WHERE YEAR(created_at) = ?
            AND order_status NOT IN ('cancelled', 'returned')
            AND payment_status = 'paid'
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ", [$year]);

    } elseif ($period === 'quarter') {
        // Monthly breakdown for the quarter
        $monthStart = ($quarter - 1) * 3 + 1;
        $monthEnd = $monthStart + 2;

        $data = $db->select("
            SELECT
                MONTH(created_at) AS month,
                COUNT(order_id) AS total_orders,
                SUM(total_amount) AS revenue,
                AVG(total_amount) AS avg_order_value
            FROM orders
            WHERE YEAR(created_at) = ?
            AND MONTH(created_at) BETWEEN ? AND ?
            AND order_status NOT IN ('cancelled', 'returned')
            AND payment_status = 'paid'
            GROUP BY MONTH(created_at)
            ORDER BY month ASC
        ", [$year, $monthStart, $monthEnd]);

    } elseif ($period === 'all') {
        // Breakdown by year and month for all time
        $data = $db->select("
            SELECT
                YEAR(created_at) AS year,
                MONTH(created_at) AS month,
                COUNT(order_id) AS total_orders,
                SUM(total_amount) AS revenue,
                AVG(total_amount) AS avg_order_value
            FROM orders
            WHERE order_status NOT IN ('cancelled', 'returned')
            AND payment_status = 'paid'
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY year ASC, month ASC
        ");

    } else {
        // Default: current month with daily breakdown (period = month)
        $month = (int)($_GET['month'] ?? date('n'));
        $data = $db->select("
            SELECT
                DAY(created_at) AS day,
                COUNT(order_id) AS total_orders,
                SUM(total_amount) AS revenue,
                AVG(total_amount) AS avg_order_value
            FROM orders
            WHERE YEAR(created_at) = ?
            AND MONTH(created_at) = ?
            AND order_status NOT IN ('cancelled', 'returned')
            AND payment_status = 'paid'
            GROUP BY DAY(created_at)
            ORDER BY day ASC
        ", [$year, $month]);
    }

    $totalRevenue = 0;
    $totalOrders = 0;

    foreach ($data as &$d) {
        $d['revenue_formatted'] = formatPrice($d['revenue'] ?? 0);
        $d['avg_order_value_formatted'] = formatPrice($d['avg_order_value'] ?? 0);
        $totalRevenue += $d['revenue'] ?? 0;
        $totalOrders += $d['total_orders'];
    }

    // Get date range for the current period
    $dateRange = getDateRange($period, $_GET);
    
    // Count total unique customers who bought
    $customersData = $db->selectOne("
        SELECT COUNT(DISTINCT user_id) as total
        FROM orders
        WHERE order_status NOT IN ('cancelled', 'returned')
        AND created_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);
    $totalCustomers = (int)($customersData['total'] ?? 0);

    // Count total product views
    $viewsData = $db->selectOne("
        SELECT COUNT(*) as total
        FROM product_view_logs
        WHERE viewed_at BETWEEN ? AND ?
    ", [$dateRange['start'], $dateRange['end']]);
    $totalViews = (int)($viewsData['total'] ?? 0);

    $conversionRate = $totalViews > 0 ? ($totalOrders / $totalViews) * 100 : 0;

    jsonResponse(true, 'Success', [
        'period_type' => $period,
        'year' => $year,
        'month' => $month ?? date('n'),
        'quarter' => $quarter,
        'data' => $data,
        'summary' => [
            'total_revenue' => (float)$totalRevenue,
            'total_revenue_formatted' => formatPrice($totalRevenue),
            'total_orders' => (int)$totalOrders,
            'customers' => $totalCustomers,
            'conversion_rate' => round($conversionRate, 2),
            'avg_monthly_revenue' => $period === 'year' && count($data) > 0 ? $totalRevenue / count($data) : 0,
            'aov' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0
        ]
    ]);
}

/**
 * Tính toán date range dựa trên period
 */
function getDateRange($period, $params) {
    $now = new DateTime();
    $year = (int)($params['year'] ?? $now->format('Y'));
    $month = (int)($params['month'] ?? $now->format('n'));
    $quarter = (int)($params['quarter'] ?? ceil($month / 3));

    switch ($period) {
        case 'all':
            return [
                'start' => '2000-01-01 00:00:00',
                'end' => $now->format('Y-m-d 23:59:59'),
                'label' => 'Tất cả thời gian'
            ];

        case 'year':
            return [
                'start' => "$year-01-01 00:00:00",
                'end' => "$year-12-31 23:59:59",
                'label' => "Năm $year"
            ];

        case 'quarter':
            $monthStart = ($quarter - 1) * 3 + 1;
            $monthEnd = $monthStart + 2;
            return [
                'start' => sprintf("%04d-%02d-01 00:00:00", $year, $monthStart),
                'end' => sprintf("%04d-%02d-%02d 23:59:59", $year, $monthEnd, cal_days_in_month(CAL_GREGORIAN, $monthEnd, $year)),
                'label' => "Quý $quarter năm $year"
            ];

        case 'month':
        default:
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            return [
                'start' => sprintf("%04d-%02d-01 00:00:00", $year, $month),
                'end' => sprintf("%04d-%02d-%02d 23:59:59", $year, $month, $daysInMonth),
                'label' => date('F', mktime(0, 0, 0, $month, 1)) . " $year"
            ];
    }
}