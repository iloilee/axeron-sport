<?php
/**
 * API Recommendations - Gợi ý sản phẩm cá nhân hóa
 * 
 * GET /api/recommendations.php?limit=10
 * 
 * Response:
 * {
 *   "success": true,
 *   "data": [...],
 *   "source": "personalized|fallback",
 *   "count": 10
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/recommendation.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: private, max-age=900'); // 15 phút

$limit = min(max((int)($_GET['limit'] ?? 10), 1), 20); // Giới hạn 1-20

try {
    $engine = getRecommendationEngine();
    $products = $engine->getRecommendations($limit);
    $sourceType = $engine->getSourceType();

    // Format dữ liệu trả về
    $formattedProducts = array_map(function ($p) {
        return [
            'product_id' => (int)$p['product_id'],
            'product_name' => $p['product_name'],
            'slug' => $p['slug'],
            'base_price' => (float)$p['base_price'],
            'formatted_price' => formatPrice($p['base_price']),
            'avg_rating' => $p['avg_rating'] ? (float)$p['avg_rating'] : null,
            'total_reviews' => (int)($p['total_reviews'] ?? 0),
            'category_name' => $p['category_name'] ?? null,
            'brand_name' => $p['brand_name'] ?? null,
            'image_url' => getImageUrl($p['image_url'] ?? null, 'https://placehold.co/400x400/f0eded/5b403f?text=SP'),
            'detail_url' => (defined('BASE_URL') ? BASE_URL : '') . '/shop/product-detail.php?slug=' . urlencode($p['slug']),
        ];
    }, $products);

    echo json_encode([
        'success' => true,
        'data' => $formattedProducts,
        'source' => $sourceType,
        'count' => count($formattedProducts),
        'is_logged_in' => isLoggedIn(),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi khi tải gợi ý sản phẩm.',
        'data' => [],
    ], JSON_UNESCAPED_UNICODE);
}
