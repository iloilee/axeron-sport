<?php
/**
 * Cart API - Axeron Sports Shop
 * Xử lý các thao tác với giỏ hàng
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Lấy thông tin user
$userId = getUserId();

switch ($action) {
    case 'add':
        handleAddToCart($input, $userId);
        break;

    case 'update':
        handleUpdateCart($input, $userId);
        break;

    case 'remove':
        handleRemoveFromCart($input, $userId);
        break;

    case 'get':
        handleGetCart($userId);
        break;

    case 'apply_promo':
        handleApplyPromo($input);
        break;

    case 'remove_promo':
        if (isset($_SESSION['checkout_promo'])) {
            unset($_SESSION['checkout_promo']);
        }
        jsonResponse(true, 'Đã xóa mã khuyến mãi');
        break;

    default:
        jsonResponse(false, 'Invalid action');
}

/**
 * Thêm sản phẩm vào giỏ hàng
 */
function handleAddToCart($input, $userId) {
    $productId = (int)($input['product_id'] ?? 0);
    $variantId = (int)($input['variant_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 1);

    if (!$productId || !$variantId || $quantity < 1) {
        jsonResponse(false, 'Thông tin sản phẩm không hợp lệ');
    }

    $db = db();

    // Kiểm tra sản phẩm và variant tồn tại
    $product = $db->selectOne("
        SELECT p.*, pv.stock_quantity, pv.is_active
        FROM products p
        JOIN product_variants pv ON p.product_id = pv.product_id
        WHERE p.product_id = ? AND pv.variant_id = ? AND p.is_visible = 1 AND p.is_deleted = 0 AND pv.is_deleted = 0
    ", [$productId, $variantId]);

    if (!$product) {
        jsonResponse(false, 'Sản phẩm không tồn tại');
    }

    if (!$product['is_active'] || $product['stock_quantity'] < $quantity) {
        jsonResponse(false, 'Sản phẩm đã hết hàng hoặc không đủ số lượng');
    }

    // Kiểm tra user có tồn tại không (tránh lỗi foreign key khi re-import database)
    $userValid = false;
    if (isLoggedIn() && $userId) {
        $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
        $userValid = !empty($userCheck);
    }

    if (isLoggedIn() && $userValid) {
        // Đã đăng nhập - lưu vào database
        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

        if (!$cart) {
            // Tạo cart mới nếu chưa có
            $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);
            $cartId = $db->lastInsertId();
        } else {
            $cartId = $cart['cart_id'];
        }

        // Kiểm tra variant đã có trong giỏ chưa
        $existing = $db->selectOne("
            SELECT cart_item_id, quantity FROM cart_items
            WHERE cart_id = ? AND variant_id = ?
        ", [$cartId, $variantId]);

        if ($existing) {
            // Cập nhật số lượng
            $newQuantity = $existing['quantity'] + $quantity;
            if ($newQuantity > $product['stock_quantity']) {
                jsonResponse(false, 'Số lượng sản phẩm vượt quá tồn kho hiện có.');
            }
            $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$newQuantity, $existing['cart_item_id']]);
        } else {
            // Thêm mới
            $db->insert("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)", [$cartId, $variantId, $quantity]);
        }

        // Đếm tổng số lượng
        $result = $db->selectOne("
            SELECT COALESCE(SUM(ci.quantity), 0) as total
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
        ", [$cartId]);

        $_SESSION['cart_count'] = (int)$result['total'];
        jsonResponse(true, 'Đã thêm vào giỏ hàng', [
            'cart_count' => (int)$result['total']
        ]);

    } else {
        // Chưa đăng nhập - lưu vào session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['variant_id'] == $variantId) {
                $newQty = $item['quantity'] + $quantity;
                if ($newQty > $product['stock_quantity']) {
                    jsonResponse(false, 'Số lượng sản phẩm vượt quá tồn kho hiện có.');
                }
                $item['quantity'] = $newQty;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ];
        }

        // Đếm tổng
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'];
        }

        $_SESSION['cart_count'] = $total;
        jsonResponse(true, 'Đã thêm vào giỏ hàng', [
            'cart_count' => $total
        ]);
    }
}

/**
 * Cập nhật số lượng trong giỏ hàng
 */
function handleUpdateCart($input, $userId) {
    $cartItemId = (int)($input['cart_item_id'] ?? 0);
    $quantity = (int)($input['quantity'] ?? 0);

    if (!$cartItemId || $quantity < 1) {
        jsonResponse(false, 'Thông tin không hợp lệ');
    }

    $db = db();

    // Kiểm tra user có tồn tại không
    $userValid = false;
    if (isLoggedIn() && $userId) {
        $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
        $userValid = !empty($userCheck);
    }

    if (isLoggedIn() && $userValid) {
        // Kiểm tra tồn kho
        $item = $db->selectOne("
            SELECT ci.*, pv.stock_quantity
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            WHERE ci.cart_item_id = ? AND ci.cart_id IN (SELECT cart_id FROM carts WHERE user_id = ?) AND pv.is_deleted = 0 AND p.is_deleted = 0
        ", [$cartItemId, $userId]);

        if (!$item) {
            jsonResponse(false, 'Sản phẩm không có trong giỏ hàng');
        }

        if ($quantity > $item['stock_quantity']) {
            jsonResponse(false, 'Số lượng sản phẩm vượt quá tồn kho hiện có.', ['current_quantity' => $item['quantity']]);
        }

        $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$quantity, $cartItemId]);

        // Tính lại tổng
        $cart = $db->selectOne("
            SELECT c.cart_id FROM carts c WHERE c.user_id = ?
        ", [$userId]);

        $result = $db->selectOne("
            SELECT COALESCE(SUM(ci.quantity), 0) as total
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
        ", [$cart['cart_id']]);

        // Tính subtotal
        $subtotal = $db->selectOne("
            SELECT COALESCE(SUM(ci.quantity * (p.base_price + pv.extra_price)), 0) as subtotal
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            WHERE ci.cart_id = ? AND pv.is_deleted = 0 AND p.is_deleted = 0
        ", [$cart['cart_id']]);

        $_SESSION['cart_count'] = (int)$result['total'];
        jsonResponse(true, 'Đã cập nhật', [
            'cart_count' => (int)$result['total'],
            'subtotal' => (float)$subtotal['subtotal']
        ]);

    } else {
        // Session cart
        if (!isset($_SESSION['cart'])) {
            jsonResponse(false, 'Giỏ hàng trống');
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['variant_id'] == $cartItemId) {
                $variant = $db->selectOne("SELECT stock_quantity FROM product_variants WHERE variant_id = ?", [$cartItemId]);
                if ($variant && $quantity > $variant['stock_quantity']) {
                    jsonResponse(false, 'Số lượng sản phẩm vượt quá tồn kho hiện có.', ['current_quantity' => $item['quantity']]);
                }
                $item['quantity'] = $quantity;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            jsonResponse(false, 'Sản phẩm không có trong giỏ hàng');
        }

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'];
        }

        $_SESSION['cart_count'] = $total;
        jsonResponse(true, 'Đã cập nhật', [
            'cart_count' => $total
        ]);
    }
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 */
function handleRemoveFromCart($input, $userId) {
    $cartItemId = (int)($input['cart_item_id'] ?? 0);

    if (!$cartItemId) {
        jsonResponse(false, 'Thông tin không hợp lệ');
    }

    $db = db();

    // Kiểm tra user có tồn tại không
    $userValid = false;
    if (isLoggedIn() && $userId) {
        $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
        $userValid = !empty($userCheck);
    }

    if (isLoggedIn() && $userValid) {
        $db->delete("
            DELETE ci FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.cart_id
            WHERE ci.cart_item_id = ? AND c.user_id = ?
        ", [$cartItemId, $userId]);

        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);
        $result = $db->selectOne("
            SELECT COALESCE(SUM(ci.quantity), 0) as total
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
        ", [$cart['cart_id']]);

        $_SESSION['cart_count'] = (int)$result['total'];
        jsonResponse(true, 'Đã xóa sản phẩm', [
            'cart_count' => (int)$result['total']
        ]);

    } else {
        if (!isset($_SESSION['cart'])) {
            jsonResponse(false, 'Giỏ hàng trống');
        }

        $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($cartItemId) {
            return $item['variant_id'] != $cartItemId;
        }));

        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['quantity'];
        }

        $_SESSION['cart_count'] = $total;
        jsonResponse(true, 'Đã xóa sản phẩm', [
            'cart_count' => $total
        ]);
    }
}

/**
 * Lấy thông tin giỏ hàng
 */
function handleGetCart($userId) {
    $db = db();

    // Kiểm tra user có tồn tại không
    $userValid = false;
    if (isLoggedIn() && $userId) {
        $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
        $userValid = !empty($userCheck);
    }

    if (isLoggedIn() && $userValid) {
        $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

        if (!$cart) {
            jsonResponse(true, 'Giỏ hàng trống', ['items' => [], 'count' => 0]);
        }

        $items = $db->select("
            SELECT
                ci.cart_item_id,
                ci.quantity,
                pv.variant_id,
                pv.sku,
                pv.color,
                pv.size,
                pv.extra_price,
                p.product_id,
                p.category_id,
                p.product_name,
                p.slug,
                p.base_price,
                p.stock_quantity as product_stock,
                pv.stock_quantity,
                pi.image_url,
                (p.base_price + pv.extra_price) as unit_price
            FROM cart_items ci
            JOIN product_variants pv ON ci.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
            WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
            ORDER BY ci.added_at DESC
        ", [$cart['cart_id']]);

        $count = 0;
        foreach ($items as &$item) {
            $count += $item['quantity'];
            
            // Tính toán discount
            $basePrice = $item['base_price'] + $item['extra_price'];
            $promoInfo = getBestPromotionForProduct($item['product_id'], $item['category_id'], $basePrice);
            
            $item['unit_price'] = $promoInfo['discounted_price'];
            $item['original_price'] = $promoInfo['original_price'];
            if ($promoInfo['promotion']) {
                $item['promotion_applied'] = $promoInfo['promotion']['promo_name'];
            }
        }
        unset($item);

        jsonResponse(true, 'Success', [
            'items' => $items,
            'count' => $count
        ]);

    } else {
        // Session cart
        if (empty($_SESSION['cart'])) {
            jsonResponse(true, 'Giỏ hàng trống', ['items' => [], 'count' => 0]);
        }

        $items = [];
        $count = 0;

        foreach ($_SESSION['cart'] as $cartItem) {
            $item = $db->selectOne("
                SELECT
                    ? as cart_item_id,
                    ? as quantity,
                    pv.variant_id,
                    pv.sku,
                    pv.color,
                    pv.size,
                    pv.extra_price,
                    pv.stock_quantity,
                    p.product_id,
                    p.category_id,
                    p.product_name,
                    p.slug,
                    p.base_price,
                    pi.image_url,
                    (p.base_price + pv.extra_price) as unit_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.product_id
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                WHERE pv.variant_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
            ", [$cartItem['variant_id'], $cartItem['quantity'], $cartItem['variant_id']]);

            if ($item) {
                $item['quantity'] = min($item['quantity'], $item['stock_quantity']);
                
                // Tính toán discount
                $basePrice = $item['base_price'] + $item['extra_price'];
                $promoInfo = getBestPromotionForProduct($item['product_id'], $item['category_id'], $basePrice);
                
                $item['unit_price'] = $promoInfo['discounted_price'];
                $item['original_price'] = $promoInfo['original_price'];
                if ($promoInfo['promotion']) {
                    $item['promotion_applied'] = $promoInfo['promotion']['promo_name'];
                }
                
                $items[] = $item;
                $count += $item['quantity'];
            }
        }

        jsonResponse(true, 'Success', [
            'items' => $items,
            'count' => $count
        ]);
    }
}

/**
 * Áp dụng mã khuyến mãi
 */
function handleApplyPromo($input) {
    $code = sanitize($input['code'] ?? '');

    if (!$code) {
        jsonResponse(false, 'Vui lòng nhập mã khuyến mãi');
    }

    $db = db();

    $promo = $db->selectOne("
        SELECT * FROM promotions
        WHERE promo_code = ? AND is_active = 1 AND type = 'voucher'
        AND start_date <= NOW() AND end_date >= NOW()
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ", [$code]);

    if (!$promo) {
        jsonResponse(false, 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn');
    }

    // Lưu mã khuyến mãi vào session để sử dụng ở trang checkout
    $_SESSION['checkout_promo'] = $promo;

    jsonResponse(true, 'Áp dụng thành công', [
        'promo' => $promo
    ]);
}
