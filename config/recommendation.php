<?php
/**
 * Recommendation Engine - Axeron Sports Shop
 * Gợi ý sản phẩm cá nhân hóa dựa trên hành vi người dùng
 * 
 * Nguồn dữ liệu:
 * - product_view_logs: sản phẩm đã xem
 * - search_logs: từ khóa tìm kiếm
 * - cart_items: sản phẩm trong giỏ hàng
 * - order_items: sản phẩm đã mua
 * - $_SESSION['guest_view_logs']: lịch sử xem của khách vãng lai
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

class RecommendationEngine
{
    private $db;
    private $userId;
    private $isLoggedIn;
    private $cacheKey;
    private $cacheDuration = 900; // 15 phút (giây)

    public function __construct()
    {
        $this->db = db();
        $this->userId = getUserId();
        $this->isLoggedIn = isLoggedIn();
        $this->cacheKey = $this->isLoggedIn
            ? 'reco_cache_user_' . $this->userId
            : 'reco_cache_guest';
    }

    /**
     * Entry point chính — trả về danh sách sản phẩm gợi ý
     * @param int $limit Số lượng SP tối đa trả về
     * @return array Danh sách sản phẩm gợi ý
     */
    public function getRecommendations(int $limit = 10): array
    {
        // Kiểm tra cache
        $cached = $this->getCache();
        if ($cached !== null) {
            return array_slice($cached, 0, $limit);
        }

        // Thu thập dữ liệu hành vi
        $behaviorData = $this->getUserBehaviorData();

        // Phân tích sở thích (tần suất category/brand)
        $preferences = $this->analyzePreferences($behaviorData);

        // Xây dựng và thực thi truy vấn gợi ý
        $recommendations = $this->fetchRecommendations($preferences, $behaviorData, $limit);

        // Nếu không đủ, fallback sang sản phẩm nổi bật / bán chạy
        if (count($recommendations) < $limit) {
            $excludeIds = array_column($recommendations, 'product_id');
            $excludeIds = array_merge($excludeIds, $behaviorData['viewed_ids'], $behaviorData['purchased_ids']);
            $fallback = $this->getFallbackProducts($limit - count($recommendations), $excludeIds);
            $recommendations = array_merge($recommendations, $fallback);
        }

        // Lưu cache
        $this->setCache($recommendations);

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Thu thập dữ liệu hành vi người dùng
     * @return array Dữ liệu hành vi đã chuẩn hóa
     */
    private function getUserBehaviorData(): array
    {
        $data = [
            'viewed_products' => [],   // [{product_id, category_id, brand_id}, ...]
            'viewed_ids' => [],        // [product_id, ...]
            'search_keywords' => [],   // [keyword, ...]
            'cart_product_ids' => [],   // [product_id, ...]
            'purchased_ids' => [],     // [product_id, ...]
        ];

        if ($this->isLoggedIn) {
            // 1. Sản phẩm đã xem (20 gần nhất)
            $viewed = $this->db->select("
                SELECT DISTINCT pvl.product_id, p.category_id, p.brand_id
                FROM product_view_logs pvl
                JOIN products p ON pvl.product_id = p.product_id
                WHERE pvl.user_id = ? AND p.is_visible = 1
                ORDER BY pvl.viewed_at DESC
                LIMIT 20
            ", [$this->userId]);

            $data['viewed_products'] = $viewed;
            $data['viewed_ids'] = array_column($viewed, 'product_id');

            // 2. Từ khóa tìm kiếm (10 gần nhất)
            $searches = $this->db->select("
                SELECT keyword
                FROM search_logs
                WHERE user_id = ?
                ORDER BY searched_at DESC
                LIMIT 10
            ", [$this->userId]);
            $data['search_keywords'] = array_column($searches, 'keyword');

            // 3. Sản phẩm trong giỏ hàng
            $cartProducts = $this->db->select("
                SELECT DISTINCT pv.product_id
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.cart_id
                JOIN product_variants pv ON ci.variant_id = pv.variant_id
                WHERE c.user_id = ?
            ", [$this->userId]);
            $data['cart_product_ids'] = array_column($cartProducts, 'product_id');

            // 4. Sản phẩm đã mua
            $purchased = $this->db->select("
                SELECT DISTINCT pv.product_id
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                JOIN product_variants pv ON oi.variant_id = pv.variant_id
                WHERE o.user_id = ? AND o.order_status NOT IN ('cancelled', 'returned')
            ", [$this->userId]);
            $data['purchased_ids'] = array_column($purchased, 'product_id');

        } else {
            // Khách vãng lai: lấy từ session
            $guestLogs = getGuestViewLogs();
            if (!empty($guestLogs)) {
                $placeholders = implode(',', array_fill(0, count($guestLogs), '?'));
                $viewed = $this->db->select("
                    SELECT p.product_id, p.category_id, p.brand_id
                    FROM products p
                    WHERE p.product_id IN ($placeholders) AND p.is_visible = 1
                ", $guestLogs);

                $data['viewed_products'] = $viewed;
                $data['viewed_ids'] = array_column($viewed, 'product_id');
            }
        }

        return $data;
    }

    /**
     * Phân tích sở thích dựa trên tần suất category/brand
     * @param array $data Dữ liệu hành vi
     * @return array [top_categories => [...], top_brands => [...], search_categories => [...]]
     */
    private function analyzePreferences(array $data): array
    {
        $categoryCounts = [];
        $brandCounts = [];

        // Đếm tần suất từ sản phẩm đã xem
        foreach ($data['viewed_products'] as $p) {
            $catId = $p['category_id'];
            $brandId = $p['brand_id'];

            $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
            if ($brandId) {
                $brandCounts[$brandId] = ($brandCounts[$brandId] ?? 0) + 1;
            }
        }

        // Bổ sung từ sản phẩm trong giỏ (trọng số x2 vì thể hiện ý định mua mạnh hơn)
        if (!empty($data['cart_product_ids'])) {
            $placeholders = implode(',', array_fill(0, count($data['cart_product_ids']), '?'));
            $cartInfo = $this->db->select("
                SELECT category_id, brand_id FROM products 
                WHERE product_id IN ($placeholders) AND is_visible = 1
            ", $data['cart_product_ids']);

            foreach ($cartInfo as $p) {
                $categoryCounts[$p['category_id']] = ($categoryCounts[$p['category_id']] ?? 0) + 2;
                if ($p['brand_id']) {
                    $brandCounts[$p['brand_id']] = ($brandCounts[$p['brand_id']] ?? 0) + 2;
                }
            }
        }

        // Map từ khóa tìm kiếm → category
        $searchCategories = [];
        foreach ($data['search_keywords'] as $keyword) {
            $catId = $this->mapSearchToCategory($keyword);
            if ($catId) {
                $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                $searchCategories[] = $catId;
            }
        }

        // Sắp xếp theo tần suất giảm dần, lấy top
        arsort($categoryCounts);
        arsort($brandCounts);

        return [
            'top_categories' => array_slice(array_keys($categoryCounts), 0, 5, true),
            'top_brands' => array_slice(array_keys($brandCounts), 0, 3, true),
            'search_categories' => array_unique($searchCategories),
            'all_category_scores' => $categoryCounts,
            'all_brand_scores' => $brandCounts,
        ];
    }

    /**
     * Map từ khóa tìm kiếm sang category_id (nếu khớp)
     * @param string $keyword
     * @return int|null
     */
    private function mapSearchToCategory(string $keyword): ?int
    {
        $keyword = mb_strtolower(trim($keyword));

        // Tìm category có tên khớp (LIKE) với từ khóa
        $match = $this->db->selectOne("
            SELECT category_id 
            FROM categories 
            WHERE LOWER(category_name) LIKE ? AND is_visible = 1
            LIMIT 1
        ", ['%' . $keyword . '%']);

        return $match ? (int)$match['category_id'] : null;
    }

    /**
     * Truy vấn sản phẩm gợi ý dựa trên sở thích phân tích được
     * @param array $preferences Kết quả phân tích sở thích
     * @param array $behaviorData Dữ liệu hành vi (để loại trừ SP đã xem/mua)
     * @param int $limit
     * @return array
     */
    private function fetchRecommendations(array $preferences, array $behaviorData, int $limit): array
    {
        $topCategories = $preferences['top_categories'];
        $topBrands = $preferences['top_brands'];

        // Không có dữ liệu gì → trả mảng rỗng, để fallback xử lý
        if (empty($topCategories) && empty($topBrands)) {
            return [];
        }

        // Danh sách SP cần loại trừ (đã xem + đã mua)
        $excludeIds = array_merge($behaviorData['viewed_ids'], $behaviorData['purchased_ids']);
        $excludeIds = array_unique(array_filter($excludeIds));

        // Xây dựng điều kiện WHERE
        $conditions = ["p.is_visible = 1"];
        $params = [];

        // Điều kiện loại trừ
        if (!empty($excludeIds)) {
            $exPlaceholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $conditions[] = "p.product_id NOT IN ($exPlaceholders)";
            $params = array_merge($params, $excludeIds);
        }

        // Điều kiện danh mục / thương hiệu (OR)
        $preferenceConditions = [];
        if (!empty($topCategories)) {
            $catPlaceholders = implode(',', array_fill(0, count($topCategories), '?'));
            $preferenceConditions[] = "p.category_id IN ($catPlaceholders)";
            $params = array_merge($params, $topCategories);
        }
        if (!empty($topBrands)) {
            $brandPlaceholders = implode(',', array_fill(0, count($topBrands), '?'));
            $preferenceConditions[] = "p.brand_id IN ($brandPlaceholders)";
            $params = array_merge($params, $topBrands);
        }

        if (!empty($preferenceConditions)) {
            $conditions[] = '(' . implode(' OR ', $preferenceConditions) . ')';
        }

        $whereClause = implode(' AND ', $conditions);

        // Xây dựng ORDER BY ưu tiên:
        // - Ưu tiên SP cùng danh mục top → FIELD()
        // - Sau đó sắp theo avg_rating DESC, total_reviews DESC
        $orderParts = [];
        if (!empty($topCategories)) {
            // SP thuộc danh mục phổ biến nhất sẽ lên đầu. Vì FIELD() trả về chỉ mục tăng dần, 
            // ta đảo ngược mảng để danh mục hot nhất nằm ở cuối (trả về giá trị lớn nhất khi dùng DESC)
            $reversedCategories = array_reverse($topCategories);
            $catFieldPlaceholders = implode(',', array_fill(0, count($reversedCategories), '?'));
            $orderParts[] = "FIELD(p.category_id, $catFieldPlaceholders) DESC";
            $params = array_merge($params, $reversedCategories);
        }
        $orderParts[] = "COALESCE(p.avg_rating, 0) DESC";
        $orderParts[] = "p.total_reviews DESC";
        $orderParts[] = "p.created_at DESC";

        $orderClause = implode(', ', $orderParts);

        $params[] = $limit;

        $sql = "
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
            WHERE $whereClause
            ORDER BY $orderClause
            LIMIT ?
        ";

        return $this->db->select($sql, $params);
    }

    /**
     * Fallback: lấy sản phẩm nổi bật hoặc được đánh giá cao nhất
     * @param int $limit
     * @param array $excludeIds Những SP đã có trong kết quả
     * @return array
     */
    private function getFallbackProducts(int $limit, array $excludeIds = []): array
    {
        $conditions = ["p.is_visible = 1"];
        $params = [];

        if (!empty($excludeIds)) {
            $exPlaceholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $conditions[] = "p.product_id NOT IN ($exPlaceholders)";
            $params = array_merge($params, $excludeIds);
        }

        $whereClause = implode(' AND ', $conditions);
        $params[] = $limit;

        return $this->db->select("
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
            WHERE $whereClause
            ORDER BY p.is_featured DESC, COALESCE(p.avg_rating, 0) DESC, p.total_reviews DESC
            LIMIT ?
        ", $params);
    }

    /**
     * Lấy cache từ session
     * @return array|null
     */
    private function getCache(): ?array
    {
        if (isset($_SESSION[$this->cacheKey]) && isset($_SESSION[$this->cacheKey . '_time'])) {
            $cacheTime = (int)$_SESSION[$this->cacheKey . '_time'];
            if (time() - $cacheTime < $this->cacheDuration) {
                return $_SESSION[$this->cacheKey];
            }
        }
        return null;
    }

    /**
     * Lưu cache vào session
     * @param array $data
     */
    private function setCache(array $data): void
    {
        $_SESSION[$this->cacheKey] = $data;
        $_SESSION[$this->cacheKey . '_time'] = time();
    }

    /**
     * Xóa cache (gọi khi cần cập nhật gợi ý ngay, ví dụ khi user xem SP mới)
     */
    public function clearCache(): void
    {
        unset($_SESSION[$this->cacheKey]);
        unset($_SESSION[$this->cacheKey . '_time']);
    }

    /**
     * Kiểm tra xem có đủ dữ liệu hành vi để gợi ý cá nhân hóa không
     * @return bool
     */
    public function hasPersonalizedData(): bool
    {
        if ($this->isLoggedIn) {
            $count = $this->db->selectOne("
                SELECT COUNT(*) as cnt FROM product_view_logs WHERE user_id = ?
            ", [$this->userId]);
            return ($count['cnt'] ?? 0) > 0;
        } else {
            $guestLogs = getGuestViewLogs();
            return !empty($guestLogs);
        }
    }

    /**
     * Trả về nguồn gợi ý (để hiển thị title phù hợp)
     * @return string 'personalized' hoặc 'fallback'
     */
    public function getSourceType(): string
    {
        return $this->hasPersonalizedData() ? 'personalized' : 'fallback';
    }
}

/**
 * Helper function tạo instance nhanh
 * @return RecommendationEngine
 */
function getRecommendationEngine(): RecommendationEngine
{
    static $instance = null;
    if ($instance === null) {
        $instance = new RecommendationEngine();
    }
    return $instance;
}
