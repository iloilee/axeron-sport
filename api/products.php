<?php
/**
 * Products API - Axeron Sports Shop
 * Xử lý lấy dữ liệu sản phẩm
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'featured':
            getFeaturedProducts($db);
            break;

        case 'by_category':
            getProductsByCategory($db);
            break;

        case 'search':
            searchProducts($db);
            break;

        case 'autocomplete':
            autocompleteProducts($db);
            break;

        case 'detail':
            getProductDetail($db);
            break;

        case 'related':
            getRelatedProducts($db);
            break;

        case 'reviews':
            getProductReviews($db);
            break;

        default:
            jsonResponse(false, 'Invalid action');
    }
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'submit_review':
            submitReview($db);
            break;

        default:
            jsonResponse(false, 'Invalid action');
    }
}

/**
 * Lấy sản phẩm nổi bật
 */
function getFeaturedProducts($db) {
    $limit = (int)($_GET['limit'] ?? 8);

    $products = $db->select("
        SELECT
            p.product_id,
            p.product_name,
            p.slug,
            p.base_price,
            p.avg_rating,
            p.total_reviews,
            p.is_featured,
            c.category_name,
            b.brand_name,
            pi.image_url
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE p.is_visible = 1 AND p.is_deleted = 0
        ORDER BY p.is_featured DESC, p.total_reviews DESC
        LIMIT ?
    ", [$limit]);

    // Định dạng giá
    foreach ($products as &$p) {
        $p['price_formatted'] = formatPrice($p['base_price']);
        $p['discount'] = rand(0, 30); // Mock discount for demo
        if ($p['discount'] > 0) {
            $p['original_price'] = $p['base_price'];
            $p['base_price'] = round($p['base_price'] * (100 - $p['discount']) / 100);
            $p['price_formatted'] = formatPrice($p['base_price']);
            $p['original_formatted'] = formatPrice($p['original_price']);
        }
    }

    jsonResponse(true, 'Success', ['products' => $products]);
}

/**
 * Lấy sản phẩm theo danh mục
 */
function getProductsByCategory($db) {
    $categorySlug = sanitize($_GET['category'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    // Lấy category_id từ slug
    $category = $db->selectOne("
        SELECT category_id, category_name, slug
        FROM categories
        WHERE slug = ? AND is_visible = 1
    ", [$categorySlug]);

    if (!$category) {
        jsonResponse(false, 'Danh mục không tồn tại');
    }

    // Lấy tất cả category_id con
    $categoryIds = [$category['category_id']];
    $children = $db->select("
        SELECT category_id FROM categories WHERE parent_id = ? AND is_visible = 1
    ", [$category['category_id']]);
    foreach ($children as $c) {
        $categoryIds[] = $c['category_id'];
    }

    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

    // Đếm tổng
    $total = $db->selectOne("
        SELECT COUNT(*) as total
        FROM products
        WHERE category_id IN ($placeholders) AND is_visible = 1 AND is_deleted = 0
    ", $categoryIds);

    // Lấy sản phẩm
    $products = $db->select("
        SELECT
            p.product_id,
            p.product_name,
            p.slug,
            p.base_price,
            p.avg_rating,
            p.total_reviews,
            p.is_featured,
            c.category_name,
            b.brand_name,
            pi.image_url,
            pv.stock_quantity,
            MIN(pv.extra_price) as min_extra_price
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        LEFT JOIN product_variants pv ON p.product_id = pv.product_id AND pv.is_active = 1 AND pv.is_deleted = 0
        WHERE p.category_id IN ($placeholders) AND p.is_visible = 1 AND p.is_deleted = 0
        GROUP BY p.product_id
        ORDER BY p.is_featured DESC, p.updated_at DESC
        LIMIT ? OFFSET ?
    ", array_merge($categoryIds, [$perPage, $offset]));

    // Format giá
    foreach ($products as &$p) {
        $p['price_formatted'] = formatPrice($p['base_price'] + ($p['min_extra_price'] ?? 0));
    }

    jsonResponse(true, 'Success', [
        'products' => $products,
        'total' => (int)$total['total'],
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total['total'] / $perPage),
        'category' => $category
    ]);
}

/**
 * Tìm kiếm sản phẩm
 */
function searchProducts($db) {
    $keyword = sanitize($_GET['keyword'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 12;
    $offset = ($page - 1) * $perPage;

    if (strlen($keyword) < 2) {
        jsonResponse(false, 'Từ khóa quá ngắn');
    }

    // Tìm kiếm
    $searchTerm = '%' . $keyword . '%';

    $total = $db->selectOne("
        SELECT COUNT(DISTINCT p.product_id) as total
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.is_visible = 1 AND p.is_deleted = 0
        AND (p.product_name LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)
    ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

    $products = $db->select("
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
        WHERE p.is_visible = 1 AND p.is_deleted = 0
        AND (p.product_name LIKE ? OR p.description LIKE ? OR b.brand_name LIKE ? OR c.category_name LIKE ?)
        GROUP BY p.product_id
        ORDER BY p.total_reviews DESC, p.avg_rating DESC
        LIMIT ? OFFSET ?
    ", array_merge([$searchTerm, $searchTerm, $searchTerm, $searchTerm], [$perPage, $offset]));

    foreach ($products as &$p) {
        $p['price_formatted'] = formatPrice($p['base_price']);
    }

    // Log search
    if (isLoggedIn()) {
        $db->insert("
            INSERT INTO search_logs (user_id, keyword, result_count)
            VALUES (?, ?, ?)
        ", [getUserId(), $keyword, $total['total']]);
    }

    jsonResponse(true, 'Success', [
        'products' => $products,
        'keyword' => $keyword,
        'total' => (int)$total['total'],
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total['total'] / $perPage)
    ]);
}

/**
 * Autocomplete tìm kiếm (gợi ý nhanh)
 */
function autocompleteProducts($db) {
    $keyword = sanitize($_GET['keyword'] ?? '');
    
    if (strlen($keyword) < 2) {
        jsonResponse(true, 'Success', ['products' => [], 'categories' => []]);
    }

    $searchTerm = '%' . $keyword . '%';

    // Tìm danh mục khớp
    $categories = $db->select("
        SELECT category_id, category_name, slug
        FROM categories 
        WHERE is_visible = 1 AND category_name LIKE ?
        LIMIT 3
    ", [$searchTerm]);

    // Tìm sản phẩm khớp
    $products = $db->select("
        SELECT 
            p.product_id, 
            p.product_name, 
            p.slug, 
            p.base_price, 
            c.category_name,
            pi.image_url
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE p.is_visible = 1 AND p.is_deleted = 0
        AND (p.product_name LIKE ? OR c.category_name LIKE ? OR b.brand_name LIKE ? OR p.description LIKE ?)
        GROUP BY p.product_id
        ORDER BY p.is_featured DESC, p.total_reviews DESC, p.avg_rating DESC
        LIMIT 5
    ", [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

    foreach ($products as &$p) {
        $p['price_formatted'] = formatPrice($p['base_price']);
        $p['image_url'] = getImageUrl($p['image_url'], 'https://placehold.co/400x400/f0eded/5b403f?text=' . urlencode(substr($p['product_name'], 0, 15)));
    }

    jsonResponse(true, 'Success', [
        'categories' => $categories,
        'products' => $products
    ]);
}

/**
 * Lấy chi tiết sản phẩm
 */
function getProductDetail($db) {
    $slug = sanitize($_GET['slug'] ?? '');
    $productId = (int)($_GET['id'] ?? 0);

    $where = $productId > 0 ? "p.product_id = ?" : "p.slug = ?";
    $param = $productId > 0 ? [$productId] : [$slug];

    $product = $db->selectOne("
        SELECT
            p.*,
            c.category_name,
            c.slug as category_slug,
            b.brand_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        WHERE $where AND p.is_visible = 1 AND p.is_deleted = 0
    ", $param);

    if (!$product) {
        jsonResponse(false, 'Sản phẩm không tồn tại');
    }

    // Lấy hình ảnh
    $images = $db->select("
        SELECT image_id, image_url, alt_text, is_primary
        FROM product_images
        WHERE product_id = ?
        ORDER BY is_primary DESC, sort_order ASC
    ", [$product['product_id']]);

    // Lấy biến thể (variants)
    $variants = $db->select("
        SELECT variant_id, sku, color, size, extra_price, stock_quantity, is_active
        FROM product_variants
        WHERE product_id = ? AND is_active = 1 AND is_deleted = 0
        ORDER BY color, size
    ", [$product['product_id']]);

    // Nhóm variants theo color
    $colors = [];
    foreach ($variants as $v) {
        $color = $v['color'] ?? 'default';
        if (!isset($colors[$color])) {
            $colors[$color] = [
                'color' => $color,
                'sizes' => []
            ];
        }
        $colors[$color]['sizes'][] = $v;
    }

    $product['images'] = $images;
    $product['colors'] = array_values($colors);
    $product['price_formatted'] = formatPrice($product['base_price']);

    // Log view
    if (isLoggedIn()) {
        $db->insert("
            INSERT INTO product_view_logs (user_id, product_id)
            VALUES (?, ?)
        ", [getUserId(), $product['product_id']]);
    }

    jsonResponse(true, 'Success', ['product' => $product]);
}

/**
 * Lấy sản phẩm liên quan
 */
function getRelatedProducts($db) {
    $productId = (int)($_GET['product_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 4);

    if (!$productId) {
        jsonResponse(false, 'Product ID required');
    }

    // Lấy category của sản phẩm hiện tại
    $product = $db->selectOne("
        SELECT category_id FROM products WHERE product_id = ?
    ", [$productId]);

    if (!$product) {
        jsonResponse(false, 'Product not found');
    }

    $products = $db->select("
        SELECT
            p.product_id,
            p.product_name,
            p.slug,
            p.base_price,
            p.avg_rating,
            p.total_reviews,
            pi.image_url
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
        WHERE p.category_id = ? AND p.product_id != ? AND p.is_visible = 1 AND p.is_deleted = 0
        ORDER BY p.is_featured DESC, p.avg_rating DESC
        LIMIT ?
    ", [$product['category_id'], $productId, $limit]);

    foreach ($products as &$p) {
        $p['price_formatted'] = formatPrice($p['base_price']);
    }

    jsonResponse(true, 'Success', ['products' => $products]);
}

/**
 * Lấy đánh giá sản phẩm
 */
function getProductReviews($db) {
    $productId = (int)($_GET['product_id'] ?? 0);
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    if (!$productId) {
        jsonResponse(false, 'Product ID required');
    }

    $total = $db->selectOne("
        SELECT COUNT(*) as total FROM reviews
        WHERE product_id = ? AND status = 'approved'
    ", [$productId]);

    $reviews = $db->select("
        SELECT
            r.*,
            u.full_name,
            u.avatar_url
        FROM reviews r
        LEFT JOIN users u ON r.user_id = u.user_id
        WHERE r.product_id = ? AND r.status = 'approved'
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ", [$productId, $perPage, $offset]);

    jsonResponse(true, 'Success', [
        'reviews' => $reviews,
        'total' => (int)$total['total'],
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total['total'] / $perPage)
    ]);
}

/**
 * Gửi đánh giá sản phẩm (client-side)
 */
function submitReview($db) {
    // Kiểm tra đăng nhập
    if (!isLoggedIn()) {
        jsonResponse(false, 'Bạn cần đăng nhập để gửi đánh giá');
    }

    $productId = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    // Validation
    if ($productId <= 0) {
        jsonResponse(false, 'ID sản phẩm không hợp lệ');
    }

    if ($rating < 1 || $rating > 5) {
        jsonResponse(false, 'Vui lòng chọn số sao từ 1 đến 5');
    }

    if (empty($comment) || strlen($comment) < 10) {
        jsonResponse(false, 'Vui lòng nhập bình luận ít nhất 10 ký tự');
    }

    // Kiểm tra sản phẩm tồn tại
    $product = $db->selectOne("SELECT product_id FROM products WHERE product_id = ? AND is_visible = 1 AND is_deleted = 0", [$productId]);
    if (!$product) {
        jsonResponse(false, 'Sản phẩm không tồn tại');
    }

    // Kiểm tra đã mua và nhận hàng thành công chưa
    $userId = getUserId();
    $purchaseCheck = $db->selectOne("
        SELECT o.order_id 
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        JOIN product_variants pv ON oi.variant_id = pv.variant_id
        WHERE o.user_id = ? 
          AND pv.product_id = ? 
          AND o.order_status = 'delivered'
        LIMIT 1
    ", [$userId, $productId]);

    if (!$purchaseCheck) {
        jsonResponse(false, 'Bạn chỉ có thể đánh giá sản phẩm này sau khi đã mua và nhận hàng thành công!');
    }

    // Kiểm tra đã đánh giá chưa
    $existingReview = $db->selectOne("SELECT review_id FROM reviews WHERE product_id = ? AND user_id = ?", [$productId, getUserId()]);
    if ($existingReview) {
        jsonResponse(false, 'Bạn đã đánh giá sản phẩm này rồi');
    }

    // Thêm đánh giá với trạng thái pending
    $reviewId = $db->insert("
        INSERT INTO reviews (product_id, user_id, rating, comment, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ", [$productId, getUserId(), $rating, $comment]);

    jsonResponse(true, 'Đánh giá của bạn đã được gửi và đang chờ quản trị viên xét duyệt', [
        'review_id' => $reviewId,
        'pending' => true
    ]);
}
