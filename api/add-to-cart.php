<?php
/**
 * Add to Cart API - Axeron Sports Shop
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$db = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Chi chap nhan POST request']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!$input) {
    $input = $_POST;
}

$productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$variantId = isset($input['variant_id']) ? (int)$input['variant_id'] : 0;
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Thong tin san pham khong hop le']);
    exit;
}

// Nếu variantId = 0, kiểm tra sản phẩm không cần variant
if ($variantId === 0) {
    $product = $db->selectOne("
        SELECT p.product_id, p.product_name, p.stock_quantity, p.is_visible
        FROM products p
        WHERE p.product_id = ? AND p.is_visible = 1 AND p.is_deleted = 0
    ", [$productId]);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'San pham khong ton tai hoac da bi an']);
        exit;
    }

    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'San pham het hang hoac khong du so luong']);
        exit;
    }

    // Kiểm tra sản phẩm đã có variant chưa
    $existingVariant = $db->selectOne("
        SELECT variant_id FROM product_variants WHERE product_id = ? AND is_deleted = 0 LIMIT 1
    ", [$productId]);

    if ($existingVariant) {
        // Sản phẩm đã có variant, sử dụng variant đầu tiên
        $variantId = $existingVariant['variant_id'];
    } else {
        // Tạo variant mặc định cho sản phẩm không có variants
        $db->insert("INSERT INTO product_variants (product_id, sku, color, size, extra_price, stock_quantity, is_active)
                      VALUES (?, ?, 'default', 'default', 0, ?, 1)",
            [$productId, 'DEFAULT-' . $productId . '-' . time(), $product['stock_quantity']]);
        $variantId = $db->lastInsertId();
    }
} else {
    $product = $db->selectOne("
        SELECT p.product_id, p.product_name, pv.stock_quantity, pv.is_active
        FROM products p
        JOIN product_variants pv ON p.product_id = pv.product_id
        WHERE p.product_id = ? AND pv.variant_id = ? AND p.is_visible = 1 AND p.is_deleted = 0 AND pv.is_deleted = 0
    ", [$productId, $variantId]);

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'San pham khong ton tai hoac da bi an']);
        exit;
    }

    if (!$product['is_active'] || $product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'San pham het hang hoac khong du so luong']);
        exit;
    }
}

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'So luong phai lon hon 0']);
    exit;
}

$userId = getUserId();

// Kiểm tra user có tồn tại không (tránh lỗi foreign key khi re-import database)
$validUser = false;
if ($userId) {
    $userCheck = $db->selectOne("SELECT user_id FROM users WHERE user_id = ?", [$userId]);
    $validUser = !empty($userCheck);
}

if ($userId && $validUser) {
    $cart = $db->selectOne("SELECT cart_id FROM carts WHERE user_id = ?", [$userId]);

    if (!$cart) {
        $db->insert("INSERT INTO carts (user_id) VALUES (?)", [$userId]);
        $cartId = $db->lastInsertId();
    } else {
        $cartId = $cart['cart_id'];
    }

    $existing = $db->selectOne("
        SELECT cart_item_id, quantity FROM cart_items
        WHERE cart_id = ? AND variant_id = ?
    ", [$cartId, $variantId]);

    if ($existing) {
        $newQuantity = $existing['quantity'] + $quantity;
        if ($newQuantity > $product['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm vượt quá tồn kho hiện có.']);
            exit;
        }
        $db->update("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?", [$newQuantity, $existing['cart_item_id']]);
    } else {
        $db->insert("INSERT INTO cart_items (cart_id, variant_id, quantity) VALUES (?, ?, ?)", [$cartId, $variantId, $quantity]);
    }

    $result = $db->selectOne("
        SELECT COALESCE(SUM(ci.quantity), 0) as total 
        FROM cart_items ci
        JOIN product_variants pv ON ci.variant_id = pv.variant_id
        JOIN products p ON pv.product_id = p.product_id
        WHERE ci.cart_id = ? AND pv.is_active = 1 AND pv.is_deleted = 0 AND p.is_deleted = 0
    ", [$cartId]);
    $cartCount = (int)$result['total'];

    $_SESSION['cart_count'] = $cartCount;

    echo json_encode([
        'success' => true,
        'message' => 'Da them vao gio hang',
        'cart_count' => $cartCount
    ]);

} else {
    // Session-based cart cho khách chưa đăng nhập
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['variant_id'] == $variantId) {
            $newQty = $item['quantity'] + $quantity;
            if ($newQty > $product['stock_quantity']) {
                echo json_encode(['success' => false, 'message' => 'Số lượng sản phẩm vượt quá tồn kho hiện có.']);
                exit;
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

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['quantity'];
    }
    $_SESSION['cart_count'] = $total;

    echo json_encode([
        'success' => true,
        'message' => 'Da them vao gio hang',
        'cart_count' => $total
    ]);
}
