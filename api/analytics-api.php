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

    // AOV (Average Order Value)
    $aov = $orders['total'] > 0 ? $revenue['total'] / $orders['total'] : 0;

    jsonResponse(true, 'Success', [
        'revenue' => (float)$revenue['total'],
        'revenue_formatted' => formatPrice($revenue['total']),
        'orders' => (int)$orders['total'],
        'customers' => (int)$customers['total'],
        'aov' => (float)$aov,
        'aov_formatted' => formatPrice($aov),
        'period' => $period,
        'date_range' => $dateRange
    ]);
}

/**
 * Thống kê theo khách hàng (xếp hạng thân thiết)
 */
function getCustomerStats($db) {
    $period = $_GET['period'] ?? 'month';
    $dateRange = getDateRange($period, $_GET);

    $search = sanitize($_GET['search'] ?? '');
    $sort = $_GET['sort'] ?? 'total_spent';
    $order = $_GET['order'] ?? 'desc';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Validate sort column
    $allowedSorts = ['total_spent', 'total_orders', 'avg_order_value', 'full_name', 'last_order_date'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'total_spent';
    }
    $order = $order === 'asc' ? 'ASC' : 'DESC';

    // Build WHERE clause
    $where = "u.role_id = 3 AND o.order_status NOT IN ('cancelled', 'returned') AND o.payment_status = 'paid'";
    $params = [$dateRange['start'], $dateRange['end']];

    if ($search) {
        $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // Count total
    $countSql = "
        SELECT COUNT(DISTINCT u.user_id) as total
        FROM users u
        JOIN orders o ON u.user_id = o.user_id
        WHERE $where
        AND o.created_at BETWEEN ? AND ?
    ";
    $countParams = $params;
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
            MAX(o.created_at) AS last_order_date
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
            AND o.order_status NOT IN ('cancelled', 'returned')
            AND o.payment_status = 'paid'
            AND o.created_at BETWEEN ? AND ?
        WHERE u.role_id = 3
        GROUP BY u.user_id, u.full_name, u.email, u.phone, u.avatar_url
        HAVING total_orders > 0
        ORDER BY $sort $order
        LIMIT ? OFFSET ?
    ";
    $params[] = $perPage;
    $params[] = $offset;

    $customers = $db->select($dataSql, $params);

    // Format data
    foreach ($customers as &$c) {
        $c['total_spent_formatted'] = formatPrice($c['total_spent']);
        $c['avg_order_value_formatted'] = formatPrice($c['avg_order_value']);
        $c['rank'] = $sort === 'total_spent' && $order === 'DESC'
            ? array_search($c['user_id'], array_column($customers, 'user_id')) + 1 + $offset
            : null;
    }

    jsonResponse(true, 'Success', [
        'customers' => $customers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $perPage),
            'total_items' => $totalItems,
            'per_page' => $perPage
        ],
        'filters' => [
            'period' => $period,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
            'date_range' => $dateRange
        ]
    ]);
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

    } else {
        // Default: current month with daily breakdown
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

    // Format data
    $totalRevenue = 0;
    $totalOrders = 0;

    foreach ($data as &$d) {
        $d['revenue_formatted'] = formatPrice($d['revenue'] ?? 0);
        $d['avg_order_value_formatted'] = formatPrice($d['avg_order_value'] ?? 0);
        $totalRevenue += $d['revenue'] ?? 0;
        $totalOrders += $d['total_orders'];
    }

    jsonResponse(true, 'Success', [
        'period_type' => $period,
        'year' => $year,
        'quarter' => $quarter,
        'data' => $data,
        'summary' => [
            'total_revenue' => (float)$totalRevenue,
            'total_revenue_formatted' => formatPrice($totalRevenue),
            'total_orders' => (int)$totalOrders,
            'avg_monthly_revenue' => $period === 'year' && count($data) > 0 ? $totalRevenue / count($data) : 0
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