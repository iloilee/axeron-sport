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

    // Build Cache Key
    $cacheKey = "customer_report_" . md5(json_encode([$period, $search, $sort, $order, $page, $rankFilter]));
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
            WHEN DATEDIFF(CURRENT_DATE, MAX(o.created_at)) > 60 THEN 'Rời bỏ'
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
    $churnCount = 0;
    $returningCount = 0;
    $segmentationData = [
        'VIP' => 0, 'Tiềm năng' => 0, 'Mới' => 0, 'Bình thường' => 0, 'Rời bỏ' => 0
    ];

    foreach ($allCustomers as $ac) {
        $r = $ac['customer_rank'];
        if (isset($segmentationData[$r])) $segmentationData[$r]++;
        
        if ($r === 'VIP') $vipCount++;
        if ($r === 'Mới') $newCount++;
        if ($r === 'Rời bỏ') $churnCount++;
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
            'returning_customers' => $returningCount,
            'churn_customers' => $churnCount
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
 * Thống kê theo sản phẩm (hot/cold items)
 */
function getProductStats($db) {
    $period = $_GET['period'] ?? 'month';
    $dateRange = getDateRange($period, $_GET);

    $search = sanitize($_GET['search'] ?? '');
    $categoryId = (int)($_GET['category_id'] ?? 0);
    $sort = $_GET['sort'] ?? 'total_sold';
    $order = $_GET['order'] ?? 'desc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Validate sort column
    $allowedSorts = ['total_sold', 'total_revenue', 'avg_price', 'product_name'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'total_sold';
    }
    $order = $order === 'asc' ? 'ASC' : 'DESC';

    // Build WHERE clause
    $where = "o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid' AND o.created_at BETWEEN ? AND ?";
    $params = [$dateRange['start'], $dateRange['end']];

    if ($search) {
        $where .= " AND p.product_name LIKE ?";
        $params[] = "%$search%";
    }

    if ($categoryId > 0) {
        $where .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }

    // Count total
    $countSql = "
        SELECT COUNT(DISTINCT p.product_id) as total
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        JOIN products p ON pv.product_id = p.product_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE $where
    ";
    $countParams = $params;
    $totalResult = $db->selectOne($countSql, $countParams);
    $totalItems = (int)$totalResult['total'];

    // Get data
    $dataSql = "
        SELECT
            p.product_id,
            p.product_name,
            p.slug,
            c.category_name,
            b.brand_name,
            SUM(oi.quantity) AS total_sold,
            SUM(oi.subtotal) AS total_revenue,
            AVG(oi.unit_price) AS avg_price,
            COUNT(DISTINCT o.order_id) AS order_count
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        JOIN products p ON pv.product_id = p.product_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE $where
        GROUP BY p.product_id, p.product_name, p.slug, c.category_name, b.brand_name
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ";
    $params[] = $perPage;
    $params[] = $offset;

    $products = $db->select($dataSql, $params);

    // Calculate percentage and identify hot/cold
    $totalSoldAll = array_sum(array_column($products, 'total_sold'));
    $totalRevenueAll = array_sum(array_column($products, 'total_revenue'));

    foreach ($products as &$p) {
        $p['total_revenue_formatted'] = formatPrice($p['total_revenue']);
        $p['avg_price_formatted'] = formatPrice($p['avg_price']);
        $p['sold_percentage'] = $totalSoldAll > 0 ? round(($p['total_sold'] / $totalSoldAll) * 100, 1) : 0;
        $p['revenue_percentage'] = $totalRevenueAll > 0 ? round(($p['total_revenue'] / $totalRevenueAll) * 100, 1) : 0;
        $p['status'] = $p['total_sold'] > 50 ? 'hot' : ($p['total_sold'] < 10 ? 'cold' : 'normal');
    }

    // Get categories for filter
    $categories = $db->select("SELECT category_id, category_name FROM categories WHERE is_visible = 1 ORDER BY category_name");

    jsonResponse(true, 'Success', [
        'products' => $products,
        'categories' => $categories,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $perPage),
            'total_items' => $totalItems,
            'per_page' => $perPage
        ],
        'totals' => [
            'sold' => (int)$totalSoldAll,
            'revenue' => (float)$totalRevenueAll,
            'revenue_formatted' => formatPrice($totalRevenueAll)
        ],
        'filters' => [
            'period' => $period,
            'search' => $search,
            'category_id' => $categoryId,
            'sort' => $sort,
            'order' => $order,
            'date_range' => $dateRange
        ]
    ]);
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