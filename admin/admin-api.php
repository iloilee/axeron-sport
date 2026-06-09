<?php
/**
 * Admin API Handler
 * Xử lý các yêu cầu AJAX từ admin panel
 */

// Set JSON header đầu tiên
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Enable error logging for debugging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api_errors.log');

// Clean any output buffer
while (ob_get_level()) {
    ob_end_clean();
}

// Custom error handler để trả về JSON khi có lỗi
function apiErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_msg = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error_msg);
    echo json_encode(['success' => false, 'message' => 'Lỗi server: ' . $errstr, 'debug' => $errfile . ':' . $errline]);
    exit;
}
set_error_handler('apiErrorHandler');

// Custom exception handler
function apiExceptionHandler($e) {
    error_log("Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    exit;
}
set_exception_handler('apiExceptionHandler');

// Bắt đầu output buffer
ob_start();

require_once __DIR__ . /../config/database.php';
require_once __DIR__ . /../config/session.php';

// Load upload configurations (Cloudinary hoặc Local) - Ép buộc dùng local upload
require_once __DIR__ . /../config/upload_config.php';
define('USE_CLOUDINARY', false);

// Lấy output buffer và discard (loại bỏ whitespace BOM)
ob_end_clean();

// Kiểm tra quyền đăng nhập và quyền quản trị
if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
    exit;
}

// Xác định action và kiểm tra quyền hạn chi tiết
$requestAction = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $requestAction = $_GET['action'] ?? '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestAction = $_POST['ajax_action'] ?? '';
}

if (!empty($requestAction)) {
    $actionPermissions = [
        // users
        'create_user' => 'users',
        'update_user' => 'users',
        'toggle_user_status' => 'users',
        'delete_user' => 'users',
        'get_user' => 'users',
        
        // reviews
        'update_review_status' => 'reviews',
        'delete_review' => 'reviews',
        
        // products
        'get_product' => 'products',
        'create_product' => 'products',
        'update_product' => 'products',
        'delete_product' => 'products',
        'upload_product_image' => 'products',
        'delete_product_image' => 'products',
        'set_primary_image' => 'products',
        'get_product_images' => 'products',
<<<<<<< HEAD
=======
        'update_image_color' => 'products',
>>>>>>> 4528578 (Push code)
        
        // categories
        'get_category' => 'categories',
        'create_category' => 'categories',
        'update_category' => 'categories',
        'delete_category' => 'categories',
        
        // brands
        'get_brand' => 'brands',
        'create_brand' => 'brands',
        'update_brand' => 'brands',
        'toggle_brand_status' => 'brands',
        'delete_brand' => 'brands',
        'upload_brand_logo' => 'brands',
        
        // promotions
        'get_promotion' => 'promotions',
        'create_promotion' => 'promotions',
        'update_promotion' => 'promotions',
        'delete_promotion' => 'promotions',
        
        // orders
        'get_order' => 'orders',
        'export_orders' => 'orders',
        'update_payment_status' => 'orders',
        'update_order_status' => 'orders',
        
        // shipping_price
        'get_shipping_price' => 'shipping_price',
        'add_shipping_price' => 'shipping_price',
        'update_shipping_price' => 'shipping_price',
        'delete_shipping_price' => 'shipping_price',
        
        // featured
        'search_products_to_feature' => 'featured',
        'add_featured_product' => 'featured',
        'remove_featured_product' => 'featured',
        'save_featured_order' => 'featured',
    ];
    
    $requiredSection = $actionPermissions[$requestAction] ?? '';
    if (!empty($requiredSection) && !hasPermission($requiredSection)) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
        exit;
    }
}

try {
    $db = db();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database: ' . $e->getMessage()]);
    exit;
}

$response = ['success' => false, 'message' => 'Thao tác không hợp lệ!'];

// GET requests - Lấy dữ liệu
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    switch ($action) {
        case 'get_product':
            if ($id > 0) {
                $product = $db->selectOne("SELECT * FROM products WHERE product_id = ?", [$id]);
                if ($product) {
                    // Lấy danh sách ảnh (chỉ các cột cơ bản)
                    $images = $db->select("SELECT image_id, image_url, alt_text, is_primary, sort_order, color
                                           FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC", [$id]);
                    $product['images'] = $images;
                    
                    // Lấy các màu sắc của sản phẩm hiện tại
                    $prod_colors = $db->select("SELECT DISTINCT color FROM product_variants WHERE product_id = ? AND color IS NOT NULL AND color != '' AND color != 'default' AND is_deleted = 0", [$id]);
                    $product['product_colors'] = array_column($prod_colors, 'color');

                    // Lấy tất cả màu sắc trong hệ thống làm gợi ý
                    $all_colors = $db->select("SELECT DISTINCT color FROM product_variants WHERE color IS NOT NULL AND color != '' AND color != 'default' AND is_deleted = 0");
                    $product['all_colors'] = array_column($all_colors, 'color');

                    // Lấy danh sách biến thể (variants)
                    $variants = $db->select("SELECT variant_id, sku, color, size, extra_price, stock_quantity, is_active
                                             FROM product_variants WHERE product_id = ? AND is_deleted = 0 ORDER BY color, size", [$id]);
                    
                    // Reconstruct actual price: actual_price = base_price + extra_price
                    $base_price = (float)($product['base_price'] ?? 0);
                    foreach ($variants as &$v) {
                        $v['extra_price'] = (float)$v['extra_price'] + $base_price;
                    }
                    unset($v); // clear reference
                    
                    $product['variants'] = $variants;

                    $response = ['success' => true, 'product' => $product];
                } else {
                    $response = ['success' => false, 'message' => 'Không tìm thấy sản phẩm!'];
                }
            }
            break;

        case 'get_user':
            if ($id > 0) {
                $user = $db->selectOne("SELECT user_id, full_name, email, phone, role_id, is_active FROM users WHERE user_id = ?", [$id]);
                if ($user) {
                    $response = ['success' => true, 'user' => $user];
                } else {
                    $response = ['success' => false, 'message' => 'Không tìm thấy người dùng!'];
                }
            }
            break;

        case 'get_category':
            if ($id > 0) {
                $category = $db->selectOne("SELECT * FROM categories WHERE category_id = ?", [$id]);
                if ($category) {
                    $response = ['success' => true, 'category' => $category];
                } else {
                    $response = ['success' => false, 'message' => 'Không tìm thấy danh mục!'];
                }
            }
            break;

        case 'get_brand':
            if ($id > 0) {
                $brand = $db->selectOne("SELECT * FROM brands WHERE brand_id = ?", [$id]);
                if ($brand) {
                    $response = ['success' => true, 'brand' => $brand];
                } else {
                    $response = ['success' => false, 'message' => 'Không tìm thấy thương hiệu!'];
                }
            }
            break;

        case 'get_promotion':
            if ($id > 0) {
                $promo = $db->selectOne("SELECT * FROM promotions WHERE promo_id = ?", [$id]);
                if ($promo) {
                    $response = ['success' => true, 'promotion' => $promo];
                } else {
                    $response = ['success' => false, 'message' => 'Không tìm thấy khuyến mãi!'];
                }
            }
            break;

        case 'get_order':
            if ($id > 0) {
                $order = $db->selectOne("SELECT * FROM orders WHERE order_id = ?", [$id]);
                if ($order) {
                    $items = $db->select("SELECT * FROM order_items WHERE order_id = ?", [$id]);
                    $response = ['success' => true, 'order' => $order, 'items' => $items];
                } else {
                    $response = ['success' => false, 'message' => 'Khong tim thay don hang!'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID khong hop le!'];
            }
            break;

        case 'search_products_to_feature':
            $q = trim($_GET['q'] ?? '');
            if (empty($q)) {
                $response = ['success' => true, 'products' => []];
                break;
            }
            $products = $db->select("
                SELECT p.product_id, p.product_name, p.slug, p.base_price, c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE p.is_visible = 1 AND p.is_featured = 0 AND (p.product_name LIKE ? OR p.slug LIKE ?)
                ORDER BY p.product_name ASC
                LIMIT 10
            ", ["%$q%", "%$q%"]);
            $response = ['success' => true, 'products' => $products];
            break;
    }

    echo json_encode($response);
    exit;
}

// POST requests - Thêm/Sửa/Xóa dữ liệu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra quyền admin cho POST requests
    if (!isAdmin()) {
        echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này!']);
        exit;
    }

    $action = $_POST['ajax_action'] ?? '';

    switch ($action) {
        // ==================== EXPORT ====================
        case 'export_orders':
            $order_ids = json_decode($_POST['order_ids'] ?? '[]', true);
            if (empty($order_ids)) {
                $response = ['success' => false, 'message' => 'Khong co don hang nao duoc chon!'];
                break;
            }

            $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';
            $orders = $db->select("SELECT * FROM orders WHERE order_id IN ($placeholders)", $order_ids);

            $data = [];
            foreach ($orders as $order) {
                $items = $db->select("SELECT * FROM order_items WHERE order_id = ?", [$order['order_id']]);

                $paymentStatusText = match($order['payment_status']) {
                    'paid' => 'Da thanh toan',
                    'unpaid' => 'Chua thanh toan',
                    'refunded' => 'Da hoan tien',
                    default => $order['payment_status']
                };
                $orderStatusText = match($order['order_status']) {
                    'pending' => 'Cho xu ly',
                    'confirmed' => 'Da xac nhan',
                    'processing' => 'Dang xu ly',
                    'shipped' => 'Dang giao',
                    'delivered' => 'Da giao',
                    'cancelled' => 'Da huy',
                    'returned' => 'Tra hang',
                    default => $order['order_status']
                };

                $data[] = [
                    'order_id' => $order['order_id'],
                    'recipient_name' => $order['recipient_name'],
                    'recipient_phone' => $order['recipient_phone'],
                    'shipping_address' => $order['shipping_address'],
                    'total_amount' => $order['total_amount'],
                    'subtotal' => $order['subtotal'],
                    'shipping_fee' => $order['shipping_fee'],
                    'discount_amount' => $order['discount_amount'],
                    'payment_status' => $order['payment_status'],
                    'payment_status_text' => $paymentStatusText,
                    'order_status' => $order['order_status'],
                    'order_status_text' => $orderStatusText,
                    'created_at' => date('d/m/Y H:i', strtotime($order['created_at'])),
                    'items' => array_map(function($item) {
                        return [
                            'product_name' => $item['product_name'] ?? 'N/A',
                            'variant_name' => $item['variant_info'] ?? '-',
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'subtotal' => $item['subtotal']
                        ];
                    }, $items)
                ];
            }

            $response = ['success' => true, 'data' => $data, 'count' => count($data)];
            break;

        // ==================== PRODUCTS ====================
        case 'create_product':
            $product_name = trim($_POST['product_name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $brand_id = (int)($_POST['brand_id'] ?? 0) ?: null;
            $base_price = (float)($_POST['base_price'] ?? 0);
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            if (empty($product_name) || $category_id <= 0 || $base_price <= 0) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc!'];
                break;
            }

            // Tạo slug
            $slug = createSlug($product_name);
            // Kiểm tra slug trùng
            $exists = $db->selectOne("SELECT product_id FROM products WHERE slug = ?", [$slug]);
            if ($exists) {
                $slug .= '-' . time();
            }

            try {
                $db->insert("INSERT INTO products (category_id, brand_id, product_name, slug, description, base_price, stock_quantity, is_featured, is_visible)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$category_id, $brand_id, $product_name, $slug, $description, $base_price, $stock_quantity, $is_featured, $is_visible]);

                $product_id = $db->lastInsertId();

                // Xử lý upload ảnh từ form
                $hasUploadedImages = false;
                $debug_info = [];
                if (isset($_FILES['pending_images'])) {
                    $files = $_FILES['pending_images'];
                    $debug_info['files_structure'] = is_array($files['name']) ? 'multiple' : 'single';
                    $debug_info['files_data'] = $files;

                    $fileCount = is_array($files['name']) ? count($files['name']) : 1;
                    $debug_info['fileCount'] = $fileCount;

                    // Xử lý cả 2 cấu trúc: nhiều file và 1 file
                    if ($fileCount > 0 && is_array($files['name']) && !empty($files['name'][0])) {
                        for ($i = 0; $i < $fileCount; $i++) {
                            $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];

                            $debug_info['file_'.$i] = ['error' => $error, 'tmp' => $tmpName, 'name' => $fileName, 'exists' => file_exists($tmpName)];

                            if ($error === UPLOAD_ERR_OK && !empty($tmpName) && file_exists($tmpName)) {
                                $isPrimary = ($i === 0) ? 1 : 0;

                                // Upload ảnh
                                $uploadResult = null;
                                if (USE_CLOUDINARY) {
                                    $uploadResult = uploadToCloudinary($tmpName, [
                                        'alt_text' => $product_name,
                                        'public_id' => 'product_' . $product_id . '_' . time() . '_' . $i
                                    ]);
                                } else {
                                    $uploadResult = uploadToLocal($tmpName, 'products/' . $slug);
                                }

                                $debug_info['upload_'.$i] = $uploadResult;

                                if ($uploadResult && $uploadResult['success']) {
                                    $color = isset($_POST['pending_images_colors'][$i]) ? trim($_POST['pending_images_colors'][$i]) : null;
                                    if ($color === '') $color = null;

                                    if (USE_CLOUDINARY) {
                                        $db->insert("INSERT INTO product_images (product_id, image_url, secure_url, public_id, alt_text, is_primary, color)
                                                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                                            [$product_id, $uploadResult['url'], $uploadResult['secure_url'], $uploadResult['public_id'], $product_name, $isPrimary, $color]);
                                    } else {
                                        $db->insert("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, color)
                                                     VALUES (?, ?, ?, ?, ?)",
                                            [$product_id, $uploadResult['url'], $product_name, $isPrimary, $color]);
                                    }
                                    $hasUploadedImages = true;
                                }
                            }
                        }
                    }
                }

                // Nếu không có ảnh nào được upload, tạo ảnh mặc định
                if (!$hasUploadedImages) {
                    $db->insert("INSERT INTO product_images (product_id, image_url, alt_text, is_primary) VALUES (?, ?, ?, 1)",
                        [$product_id, "https://placehold.co/600x600/111827/ffffff?text=" . urlencode(substr($product_name, 0, 20)), $product_name]);
                }

                // Xử lý các biến thể sản phẩm (product_variants)
                $variants_json = $_POST['variants_json'] ?? '';
                $min_price = null;
                $variants = [];
                if (!empty($variants_json)) {
                    $variants = json_decode($variants_json, true) ?: [];
                    foreach ($variants as $v) {
                        $v_sku = trim($v['sku'] ?? '');
                        if (empty($v_sku)) continue;
                        $v_active = (int)($v['is_active'] ?? 1);
                        if ($v_active) {
                            $price = (float)($v['extra_price'] ?? 0);
                            if ($min_price === null || $price < $min_price) {
                                $min_price = $price;
                            }
                        }
                    }
                }
                if ($min_price === null) $min_price = 0.0;

                // Cập nhật giá cơ bản của sản phẩm chính bằng giá biến thể nhỏ nhất
                $db->update("UPDATE products SET base_price = ? WHERE product_id = ?", [$min_price, $product_id]);

                // Lưu các biến thể với extra_price = giá thực tế - giá cơ bản
                foreach ($variants as $v) {
                    $v_sku = trim($v['sku'] ?? '');
                    if (empty($v_sku)) continue;
                    $v_color = trim($v['color'] ?? '') ?: null;
                    $v_size = trim($v['size'] ?? '') ?: null;
                    $v_actual_price = (float)($v['extra_price'] ?? 0);
                    $v_extra_price = $v_actual_price - $min_price;
                    $v_stock = (int)($v['stock_quantity'] ?? 0);
                    $v_active = (int)($v['is_active'] ?? 1);

                    $db->insert("INSERT INTO product_variants (product_id, sku, color, size, extra_price, stock_quantity, is_active, is_deleted)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, 0)",
                        [$product_id, $v_sku, $v_color, $v_size, $v_extra_price, $v_stock, $v_active]);
                }

                // Cập nhật tổng tồn kho trong bảng products nếu có biến thể hoạt động
                $totalStock = $db->selectOne("SELECT SUM(stock_quantity) as total FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0", [$product_id]);
                $stock_sum = (int)($totalStock['total'] ?? 0);
                $has_active_variants = $db->selectOne("SELECT COUNT(*) as count FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0", [$product_id]);
                if (($has_active_variants['count'] ?? 0) > 0) {
                    $db->update("UPDATE products SET stock_quantity = ? WHERE product_id = ?", [$stock_sum, $product_id]);
                }

                $response = ['success' => true, 'message' => 'Sản phẩm đã được tạo thành công!', 'debug' => $debug_info];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_product':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $product_name = trim($_POST['product_name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $brand_id = (int)($_POST['brand_id'] ?? 0) ?: null;
            $base_price = (float)($_POST['base_price'] ?? 0);
            $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            if ($product_id <= 0 || empty($product_name) || $category_id <= 0) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            try {
                // Xử lý tính toán giá cơ bản (base_price) dựa trên giá biến thể nhỏ nhất
                $variants_json = $_POST['variants_json'] ?? '';
                $min_price = null;
                $variants = [];
                if (!empty($variants_json)) {
                    $variants = json_decode($variants_json, true) ?: [];
                    foreach ($variants as $v) {
                        $v_sku = trim($v['sku'] ?? '');
                        if (empty($v_sku)) continue;
                        $v_active = (int)($v['is_active'] ?? 1);
                        if ($v_active) {
                            $price = (float)($v['extra_price'] ?? 0);
                            if ($min_price === null || $price < $min_price) {
                                $min_price = $price;
                            }
                        }
                    }
                }
                if ($min_price === null) {
                    $min_price = $base_price; // Fallback to current if no active variants
                }
                $base_price = $min_price;

                $db->update("UPDATE products SET category_id = ?, brand_id = ?, product_name = ?, description = ?,
                             base_price = ?, stock_quantity = ?, is_featured = ?, is_visible = ?, updated_at = NOW()
                             WHERE product_id = ?",
                    [$category_id, $brand_id, $product_name, $description, $base_price, $stock_quantity, $is_featured, $is_visible, $product_id]);

                // Xử lý các biến thể sản phẩm (product_variants)
                $existing_variants = $db->select("SELECT variant_id FROM product_variants WHERE product_id = ?", [$product_id]);
                $existing_ids = array_column($existing_variants, 'variant_id');
                
                $submitted_ids = [];
                
                foreach ($variants as $v) {
                    $v_id = isset($v['variant_id']) ? (int)$v['variant_id'] : null;
                    $v_sku = trim($v['sku'] ?? '');
                    if (empty($v_sku)) continue;
                    $v_color = trim($v['color'] ?? '') ?: null;
                    $v_size = trim($v['size'] ?? '') ?: null;
                    $v_actual_price = (float)($v['extra_price'] ?? 0);
                    $v_extra_price = $v_actual_price - $min_price;
                    $v_stock = (int)($v['stock_quantity'] ?? 0);
                    $v_active = (int)($v['is_active'] ?? 1);

                    if ($v_id && in_array($v_id, $existing_ids)) {
                        // Cập nhật biến thể hiện có
                        $db->update("UPDATE product_variants 
                                     SET sku = ?, color = ?, size = ?, extra_price = ?, stock_quantity = ?, is_active = ?, is_deleted = 0
                                     WHERE variant_id = ?",
                            [$v_sku, $v_color, $v_size, $v_extra_price, $v_stock, $v_active, $v_id]);
                        $submitted_ids[] = $v_id;
                    } else {
                        // Tạo biến thể mới
                        $db->insert("INSERT INTO product_variants (product_id, sku, color, size, extra_price, stock_quantity, is_active, is_deleted)
                                     VALUES (?, ?, ?, ?, ?, ?, ?, 0)",
                            [$product_id, $v_sku, $v_color, $v_size, $v_extra_price, $v_stock, $v_active]);
                    }
                }

                // Xóa (hoặc vô hiệu hóa/soft-delete) các biến thể bị gỡ bỏ
                $deleted_ids = array_diff($existing_ids, $submitted_ids);
                foreach ($deleted_ids as $old_id) {
                    try {
                        $db->delete("DELETE FROM product_variants WHERE variant_id = ?", [$old_id]);
                    } catch (mysqli_sql_exception $e) {
                        if ($e->getCode() == 1451) {
                            // Deactivate and soft-delete if referenced by order items to preserve database integrity
                            $db->update("UPDATE product_variants SET is_active = 0, is_deleted = 1 WHERE variant_id = ?", [$old_id]);
                        } else {
                            throw $e;
                        }
                    }
                }

                // Cập nhật tổng tồn kho trong bảng products nếu có biến thể hoạt động
                $totalStock = $db->selectOne("SELECT SUM(stock_quantity) as total FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0", [$product_id]);
                $stock_sum = (int)($totalStock['total'] ?? 0);
                $has_active_variants = $db->selectOne("SELECT COUNT(*) as count FROM product_variants WHERE product_id = ? AND is_active = 1 AND is_deleted = 0", [$product_id]);
                if (($has_active_variants['count'] ?? 0) > 0) {
                    $db->update("UPDATE products SET stock_quantity = ? WHERE product_id = ?", [$stock_sum, $product_id]);
                }

                $response = ['success' => true, 'message' => 'Sản phẩm đã được cập nhật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_product':
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ!'];
                break;
            }

            // Kiểm tra xem sản phẩm có trong đơn hàng nào không
            $orders = $db->select("
                SELECT oi.order_id 
                FROM order_items oi
                JOIN product_variants pv ON oi.variant_id = pv.variant_id
                WHERE pv.product_id = ?
            ", [$product_id]);

            if (!empty($orders)) {
                $response = ['success' => false, 'message' => 'Không thể xóa sản phẩm đã có trong đơn hàng! Bạn hãy chọn ẩn sản phẩm này thay vì xóa.'];
                break;
            }

            try {
                $db->beginTransaction();

                // 1. Xóa các sản phẩm trong giỏ hàng (cart_items)
                $db->delete("
                    DELETE FROM cart_items 
                    WHERE variant_id IN (SELECT variant_id FROM product_variants WHERE product_id = ?)
                ", [$product_id]);

                // 2. Xóa các hình ảnh (product_images)
                $db->delete("DELETE FROM product_images WHERE product_id = ?", [$product_id]);

                // 3. Xóa lịch sử xem sản phẩm (product_view_logs)
                $db->delete("DELETE FROM product_view_logs WHERE product_id = ?", [$product_id]);

                // 4. Xóa đánh giá (reviews)
                $db->delete("DELETE FROM reviews WHERE product_id = ?", [$product_id]);

                // 5. Xóa các biến thể sản phẩm (product_variants)
                $db->delete("DELETE FROM product_variants WHERE product_id = ?", [$product_id]);

                // 6. Cuối cùng, xóa sản phẩm khỏi bảng products
                $db->delete("DELETE FROM products WHERE product_id = ?", [$product_id]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Sản phẩm đã được xóa thành công!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage()];
            }
            break;

        // ==================== USERS ====================
        case 'create_user':
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $role_id = (int)($_POST['role_id'] ?? 3);
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($full_name) || empty($email) || empty($password)) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
                break;
            }

            // Kiểm tra email tồn tại
            $exists = $db->selectOne("SELECT user_id FROM users WHERE email = ?", [$email]);
            if ($exists) {
                $response = ['success' => false, 'message' => 'Email đã được sử dụng!'];
                break;
            }

            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $db->insert("INSERT INTO users (role_id, full_name, email, phone, password_hash, is_active, email_verified)
                             VALUES (?, ?, ?, ?, ?, ?, 1)",
                    [$role_id, $full_name, $email, $phone, $password_hash, $is_active]);

                $response = ['success' => true, 'message' => 'Người dùng đã được tạo thành công!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $role_id = (int)($_POST['role_id'] ?? 3);
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if ($user_id <= 0 || empty($full_name) || empty($email)) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            // Không cho phép thay đổi role của chính mình thành non-admin
            if ($user_id == getUserId() && $role_id != 1) {
                $response = ['success' => false, 'message' => 'Bạn không thể thay đổi vai trò của chính mình!'];
                break;
            }

            // Validate password change
            if (!empty($new_password)) {
                if ($new_password !== $confirm_password) {
                    $response = ['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!'];
                    break;
                }
                if (strlen($new_password) < 6) {
                    $response = ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự!'];
                    break;
                }
            }

            try {
                if (!empty($new_password)) {
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->update("UPDATE users SET full_name = ?, email = ?, phone = ?, role_id = ?, is_active = ?, password_hash = ? WHERE user_id = ?",
                        [$full_name, $email, $phone, $role_id, $is_active, $password_hash, $user_id]);
                    $response = ['success' => true, 'message' => 'Thông tin và mật khẩu đã được cập nhật!'];
                } else {
                    $db->update("UPDATE users SET full_name = ?, email = ?, phone = ?, role_id = ?, is_active = ? WHERE user_id = ?",
                        [$full_name, $email, $phone, $role_id, $is_active, $user_id]);
                    $response = ['success' => true, 'message' => 'Thông tin người dùng đã được cập nhật!'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'toggle_user_status':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $new_status = (int)($_POST['new_status'] ?? 0);

            if ($user_id <= 0) {
                $response = ['success' => false, 'message' => 'ID người dùng không hợp lệ!'];
                break;
            }

            // Không cho khóa chính mình
            if ($user_id == getUserId()) {
                $response = ['success' => false, 'message' => 'Bạn không thể khóa tài khoản của chính mình!'];
                break;
            }

            try {
                $db->update("UPDATE users SET is_active = ? WHERE user_id = ?", [$new_status, $user_id]);
                $response = ['success' => true, 'message' => $new_status ? 'Tài khoản đã được mở khóa!' : 'Tài khoản đã bị khóa!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id <= 0) {
                $response = ['success' => false, 'message' => 'ID người dùng không hợp lệ!'];
                break;
            }

            if ($user_id == getUserId()) {
                $response = ['success' => false, 'message' => 'Bạn không thể xóa tài khoản của chính mình!'];
                break;
            }

            // Kiểm tra xem người dùng có đơn hàng chưa hoàn thành (chờ xử lý, đã xác nhận, đang xử lý, đang giao)
            $active_orders = $db->select("
                SELECT order_id FROM orders 
                WHERE user_id = ? 
                AND order_status NOT IN ('delivered', 'cancelled', 'returned')
            ", [$user_id]);

            if (!empty($active_orders)) {
                $response = ['success' => false, 'message' => 'Không thể xóa người dùng đang có đơn hàng chưa hoàn thành!'];
                break;
            }

            try {
                $db->beginTransaction();

                // 1. Xóa các sản phẩm trong giỏ hàng (cart_items)
                $db->delete("
                    DELETE FROM cart_items 
                    WHERE cart_id IN (SELECT cart_id FROM carts WHERE user_id = ?)
                ", [$user_id]);

                // 2. Xóa giỏ hàng (carts)
                $db->delete("DELETE FROM carts WHERE user_id = ?", [$user_id]);

                // 3. Xóa tin nhắn chat (chat_messages)
                $db->delete("
                    DELETE FROM chat_messages 
                    WHERE session_id IN (SELECT session_id FROM chat_sessions WHERE user_id = ?)
                ", [$user_id]);

                // 4. Xóa phiên chat (chat_sessions)
                $db->delete("DELETE FROM chat_sessions WHERE user_id = ?", [$user_id]);

                // 5. Xóa chi tiết đơn hàng (order_items)
                $db->delete("
                    DELETE FROM order_items 
                    WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = ?)
                ", [$user_id]);

                // 6. Xóa lịch sử trạng thái đơn hàng (order_status_logs)
                $db->delete("
                    DELETE FROM order_status_logs 
                    WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = ?)
                ", [$user_id]);

                // 7. Xóa giao dịch thanh toán (payment_transactions)
                $db->delete("
                    DELETE FROM payment_transactions 
                    WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = ?)
                ", [$user_id]);

                // 8. Xóa các đơn hàng đã hoàn thành/hủy/trả lại (orders)
                $db->delete("DELETE FROM orders WHERE user_id = ?", [$user_id]);

                // 9. Xóa yêu cầu khôi phục mật khẩu (password_resets)
                $db->delete("DELETE FROM password_resets WHERE user_id = ?", [$user_id]);

                // 10. Xóa lịch sử xem sản phẩm (product_view_logs)
                $db->delete("DELETE FROM product_view_logs WHERE user_id = ?", [$user_id]);

                // 11. Xóa các đánh giá (reviews)
                $db->delete("DELETE FROM reviews WHERE user_id = ?", [$user_id]);

                // 12. Xóa lịch sử tìm kiếm (search_logs)
                $db->delete("DELETE FROM search_logs WHERE user_id = ?", [$user_id]);

                // 13. Xóa địa chỉ nhận hàng (user_addresses)
                $db->delete("DELETE FROM user_addresses WHERE user_id = ?", [$user_id]);

                // 14. Cuối cùng, xóa thông tin người dùng
                $db->delete("DELETE FROM users WHERE user_id = ?", [$user_id]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Người dùng đã được xóa thành công!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi khi xóa người dùng: ' . $e->getMessage()];
            }
            break;

        // ==================== CATEGORIES ====================
        case 'create_category':
            $category_name = trim($_POST['category_name'] ?? '');
            $parent_id = $_POST['parent_id'] ?? null;
            $parent_id = $parent_id ? (int)$parent_id : null;
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            if (empty($category_name)) {
                $response = ['success' => false, 'message' => 'Vui lòng nhập tên danh mục!'];
                break;
            }

            if (empty($slug)) {
                $slug = createSlug($category_name);
            }

            try {
                $db->insert("INSERT INTO categories (parent_id, category_name, slug, description, sort_order, is_visible)
                             VALUES (?, ?, ?, ?, ?, ?)",
                    [$parent_id, $category_name, $slug, $description, $sort_order, $is_visible]);

                $response = ['success' => true, 'message' => 'Danh mục đã được tạo!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_category':
            $category_id = (int)($_POST['category_id'] ?? 0);
            $category_name = trim($_POST['category_name'] ?? '');
            $parent_id = $_POST['parent_id'] ?? null;
            $parent_id = $parent_id ? (int)$parent_id : null;
            $slug = trim($_POST['slug'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_visible = isset($_POST['is_visible']) ? 1 : 0;

            if ($category_id <= 0 || empty($category_name)) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            // Không cho danh mục cha thuộc chính nó
            if ($parent_id == $category_id) {
                $response = ['success' => false, 'message' => 'Danh mục cha không thể là chính nó!'];
                break;
            }

            if (empty($slug)) {
                $slug = createSlug($category_name);
            }

            try {
                $db->update("UPDATE categories SET parent_id = ?, category_name = ?, slug = ?, description = ?, sort_order = ?, is_visible = ? WHERE category_id = ?",
                    [$parent_id, $category_name, $slug, $description, $sort_order, $is_visible, $category_id]);

                $response = ['success' => true, 'message' => 'Danh mục đã được cập nhật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_category':
            $category_id = (int)($_POST['category_id'] ?? 0);
            if ($category_id <= 0) {
                $response = ['success' => false, 'message' => 'ID danh mục không hợp lệ!'];
                break;
            }

            try {
                // Kiểm tra có danh mục con không
                $children = $db->selectOne("SELECT category_id FROM categories WHERE parent_id = ? LIMIT 1", [$category_id]);
                if ($children) {
                    $response = ['success' => false, 'message' => 'Vui lòng xóa các danh mục con trước!'];
                    break;
                }

                // Kiểm tra có sản phẩm thuộc danh mục này không
                $productCount = $db->selectOne("SELECT COUNT(*) as count FROM products WHERE category_id = ?", [$category_id]);
                if ($productCount && $productCount['count'] > 0) {
                    $response = ['success' => false, 'message' => 'Không thể xóa danh mục này vì đang có ' . $productCount['count'] . ' sản phẩm thuộc danh mục! Vui lòng chuyển hoặc xóa các sản phẩm trước.'];
                    break;
                }

                $db->update("DELETE FROM categories WHERE category_id = ?", [$category_id]);
                $response = ['success' => true, 'message' => 'Danh mục đã được xóa!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        // ==================== BRANDS ====================
        case 'create_brand':
            $brand_name = trim($_POST['brand_name'] ?? '');
            $logo_url = trim($_POST['logo_url'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($brand_name)) {
                $response = ['success' => false, 'message' => 'Vui lòng nhập tên thương hiệu!'];
                break;
            }

            try {
                $db->insert("INSERT INTO brands (brand_name, logo_url, description, is_active) VALUES (?, ?, ?, ?)",
                    [$brand_name, $logo_url, $description, $is_active]);

                $response = ['success' => true, 'message' => 'Thương hiệu đã được tạo!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_brand':
            $brand_id = (int)($_POST['brand_id'] ?? 0);
            $brand_name = trim($_POST['brand_name'] ?? '');
            $logo_url = trim($_POST['logo_url'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($brand_id <= 0 || empty($brand_name)) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            try {
                $db->update("UPDATE brands SET brand_name = ?, logo_url = ?, description = ?, is_active = ? WHERE brand_id = ?",
                    [$brand_name, $logo_url, $description, $is_active, $brand_id]);

                $response = ['success' => true, 'message' => 'Thương hiệu đã được cập nhật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'toggle_brand_status':
            $brand_id = (int)($_POST['brand_id'] ?? 0);
            $new_status = (int)($_POST['new_status'] ?? 0);

            if ($brand_id <= 0) {
                $response = ['success' => false, 'message' => 'ID thương hiệu không hợp lệ!'];
                break;
            }

            try {
                $db->update("UPDATE brands SET is_active = ? WHERE brand_id = ?", [$new_status, $brand_id]);
                $response = ['success' => true, 'message' => $new_status ? 'Thương hiệu đã được kích hoạt!' : 'Thương hiệu đã bị tạm ngưng!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_brand':
            $brand_id = (int)($_POST['brand_id'] ?? 0);
            if ($brand_id <= 0) {
                $response = ['success' => false, 'message' => 'ID thương hiệu không hợp lệ!'];
                break;
            }

            try {
                $db->update("UPDATE brands SET is_active = 0 WHERE brand_id = ?", [$brand_id]);
                $response = ['success' => true, 'message' => 'Thương hiệu đã được xóa!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        // ==================== PROMOTIONS ====================
        case 'create_promotion':
            $promo_code = strtoupper(trim($_POST['promo_code'] ?? ''));
            $promo_name = trim($_POST['promo_name'] ?? '');
            $discount_type = $_POST['discount_type'] ?? 'percent';
            $discount_value = (float)($_POST['discount_value'] ?? 0);
            $min_order_value = (float)($_POST['min_order_value'] ?? 0);
            $max_discount = $_POST['max_discount'] ?? null;
            $max_discount = $max_discount ? (float)$max_discount : null;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $usage_limit = $_POST['usage_limit'] ?? null;
            $usage_limit = $usage_limit ? (int)$usage_limit : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (empty($promo_code) || empty($promo_name) || $discount_value <= 0 || empty($start_date) || empty($end_date)) {
                $response = ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!'];
                break;
            }

            // Kiểm tra mã trùng
            $exists = $db->selectOne("SELECT promo_id FROM promotions WHERE promo_code = ?", [$promo_code]);
            if ($exists) {
                $response = ['success' => false, 'message' => 'Mã khuyến mãi đã tồn tại!'];
                break;
            }

            try {
                $db->insert("INSERT INTO promotions (promo_code, promo_name, discount_type, discount_value, min_order_value, max_discount, start_date, end_date, usage_limit, is_active)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$promo_code, $promo_name, $discount_type, $discount_value, $min_order_value, $max_discount, $start_date, $end_date, $usage_limit, $is_active]);

                $response = ['success' => true, 'message' => 'Khuyến mãi đã được tạo!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_promotion':
            $promo_id = (int)($_POST['promo_id'] ?? 0);
            $promo_code = strtoupper(trim($_POST['promo_code'] ?? ''));
            $promo_name = trim($_POST['promo_name'] ?? '');
            $discount_type = $_POST['discount_type'] ?? 'percent';
            $discount_value = (float)($_POST['discount_value'] ?? 0);
            $min_order_value = (float)($_POST['min_order_value'] ?? 0);
            $max_discount = $_POST['max_discount'] ?? null;
            $max_discount = $max_discount ? (float)$max_discount : null;
            $start_date = $_POST['start_date'] ?? '';
            $end_date = $_POST['end_date'] ?? '';
            $usage_limit = $_POST['usage_limit'] ?? null;
            $usage_limit = $usage_limit ? (int)$usage_limit : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($promo_id <= 0 || empty($promo_code) || empty($promo_name) || $discount_value <= 0) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            try {
                $db->update("UPDATE promotions SET promo_code = ?, promo_name = ?, discount_type = ?, discount_value = ?,
                             min_order_value = ?, max_discount = ?, start_date = ?, end_date = ?, usage_limit = ?, is_active = ?
                             WHERE promo_id = ?",
                    [$promo_code, $promo_name, $discount_type, $discount_value, $min_order_value, $max_discount,
                     $start_date, $end_date, $usage_limit, $is_active, $promo_id]);

                $response = ['success' => true, 'message' => 'Khuyến mãi đã được cập nhật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_promotion':
            $promo_id = (int)($_POST['promo_id'] ?? 0);
            if ($promo_id <= 0) {
                $response = ['success' => false, 'message' => 'ID khuyến mãi không hợp lệ!'];
                break;
            }

            try {
                $db->update("DELETE FROM promotions WHERE promo_id = ?", [$promo_id]);
                $response = ['success' => true, 'message' => 'Khuyến mãi đã được xóa!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        // ==================== ORDERS ====================
        case 'update_payment_status':
            $order_id = (int)($_POST['order_id'] ?? 0);
            $new_payment_status = $_POST['new_payment_status'] ?? '';

            $validPaymentStatuses = ['unpaid', 'paid', 'refunded'];
            if ($order_id <= 0 || !in_array($new_payment_status, $validPaymentStatuses)) {
                $response = ['success' => false, 'message' => 'Dữ liệu không hợp lệ!'];
                break;
            }

            try {
                // Lấy trạng thái cũ
                $order = $db->selectOne("SELECT payment_status FROM orders WHERE order_id = ?", [$order_id]);
                if (!$order) {
                    $response = ['success' => false, 'message' => 'Không tìm thấy đơn hàng!'];
                    break;
                }

                $old_status = $order['payment_status'];

                // Cập nhật trạng thái thanh toán
                $db->update("UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE order_id = ?", [$new_payment_status, $order_id]);

                // Ghi log
                $db->insert("INSERT INTO order_status_logs (order_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)",
                    [$order_id, getUserId(), $old_status, $new_payment_status, 'Cập nhật thanh toán bởi admin']);

                $statusText = match($new_payment_status) {
                    'paid' => 'Đã thanh toán',
                    'unpaid' => 'Chưa thanh toán',
                    'refunded' => 'Đã hoàn tiền',
                    default => $new_payment_status
                };

                $response = [
                    'success' => true,
                    'message' => "Đơn hàng #$order_id đã cập nhật: $statusText",
                    'new_status' => $new_payment_status
                ];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_order_status':
            $order_id = (int)($_POST['order_id'] ?? 0);
            $new_status = $_POST['new_status'] ?? '';

            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
            if ($order_id <= 0 || !in_array($new_status, $validStatuses)) {
                $response = ['success' => false, 'message' => 'Du lieu khong hop le!'];
                break;
            }

            try {
                // Lay trang thai cu
                $order = $db->selectOne("SELECT order_status FROM orders WHERE order_id = ?", [$order_id]);
                if (!$order) {
                    $response = ['success' => false, 'message' => 'Khong tim thay don hang!'];
                    break;
                }

                $old_status = $order['order_status'];

                // Cap nhat trang thai
                $db->update("UPDATE orders SET order_status = ?, updated_at = NOW() WHERE order_id = ?", [$new_status, $order_id]);

                // Ghi log
                $db->insert("INSERT INTO order_status_logs (order_id, changed_by, old_status, new_status) VALUES (?, ?, ?, ?)",
                    [$order_id, getUserId(), $old_status, $new_status]);

                $statusText = match($new_status) {
                    'pending' => 'Cho xu ly',
                    'confirmed' => 'Da xac nhan',
                    'processing' => 'Dang xu ly',
                    'shipped' => 'Dang giao',
                    'delivered' => 'Da giao',
                    'cancelled' => 'Da huy',
                    'returned' => 'Tra hang',
                    default => $new_status
                };

                $response = ['success' => true, 'message' => "Don hang #$order_id da cap nhat sang trang thai: $statusText"];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Loi: ' . $e->getMessage()];
            }
            break;

        // ==================== REVIEWS ====================
        case 'update_review_status':
            $review_id = (int)($_POST['review_id'] ?? 0);
            $new_status = $_POST['new_status'] ?? '';

            $validStatuses = ['pending', 'approved', 'rejected', 'hidden'];
            if ($review_id <= 0 || !in_array($new_status, $validStatuses)) {
                $response = ['success' => false, 'message' => 'Du lieu khong hop le!'];
                break;
            }

            try {
                $db->update("UPDATE reviews SET status = ?, updated_at = NOW() WHERE review_id = ?", [$new_status, $review_id]);
                $statusText = match($new_status) {
                    'pending' => 'Cho duyet',
                    'approved' => 'Da duyet',
                    'rejected' => 'Tu choi',
                    'hidden' => 'An',
                    default => $new_status
                };
                $response = ['success' => true, 'message' => "Danh gia da duoc cap nhat: $statusText"];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Loi: ' . $e->getMessage()];
            }
            break;

        case 'delete_review':
            $review_id = (int)($_POST['review_id'] ?? 0);
            if ($review_id <= 0) {
                $response = ['success' => false, 'message' => 'ID đánh giá không hợp lệ!'];
                break;
            }

            try {
                $db->beginTransaction();

                // Cleanly delete the target row from reviews table
                $db->delete("DELETE FROM reviews WHERE review_id = ?", [$review_id]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Đã xóa đánh giá thành công!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        // ==================== PRODUCT IMAGES ====================
        case 'upload_product_image':
            $product_id = (int)($_POST['product_id'] ?? 0);

            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ!'];
                break;
            }

            // Kiểm tra sản phẩm tồn tại
            $product = $db->selectOne("SELECT product_id FROM products WHERE product_id = ?", [$product_id]);
            if (!$product) {
                $response = ['success' => false, 'message' => 'Sản phẩm không tồn tại!'];
                break;
            }

            // Kiểm tra file upload
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File vượt quá giới hạn server',
                    UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn form',
                    UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
                    UPLOAD_ERR_NO_FILE => 'Không có file nào được chọn',
                    UPLOAD_ERR_NO_TMP_DIR => 'Thư mục tạm không tồn tại',
                    UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
                    UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension',
                ];
                $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errorMsg = $uploadErrors[$errorCode] ?? 'Lỗi không xác định';
                $response = ['success' => false, 'message' => $errorMsg];
                break;
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedTypes)) {
                $response = ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)!'];
                break;
            }

            // Validate file size (10MB max)
            if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
                $response = ['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 10MB!'];
                break;
            }

            $alt_text = trim($_POST['alt_text'] ?? '');
            $is_primary = isset($_POST['is_primary']) ? 1 : 0;

            // Lấy product slug để tạo thư mục
            $product = $db->selectOne("SELECT slug FROM products WHERE product_id = ?", [$product_id]);
            $productSlug = $product['slug'] ?? 'product-' . $product_id;

            // Upload file
            $uploadResult = null;
            if (USE_CLOUDINARY) {
                // Upload lên Cloudinary
                $uploadResult = uploadToCloudinary($_FILES['image']['tmp_name'], [
                    'alt_text' => $alt_text,
                    'public_id' => 'product_' . $product_id . '_' . time()
                ]);
            } else {
                // Upload lên local server
                $uploadResult = uploadToLocal($_FILES['image']['tmp_name'], 'products/' . $productSlug);
            }

            if (!$uploadResult || !($uploadResult['success'] ?? false)) {
                $errorMsg = $uploadResult['error'] ?? 'Lỗi upload ảnh';
                $response = ['success' => false, 'message' => 'Lỗi upload ảnh: ' . $errorMsg];
                break;
            }

            // Nếu là ảnh chính, bỏ đánh dấu ảnh chính cũ
            if ($is_primary) {
                $db->update("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$product_id]);
            }

            // Lấy sort_order tiếp theo
            $maxOrder = $db->selectOne("SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = ?", [$product_id]);
            $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

            // Lưu vào database
            try {
                $color = isset($_POST['color']) ? trim($_POST['color']) : null;
                if ($color === '') $color = null;

                if (USE_CLOUDINARY) {
                    $db->insert("INSERT INTO product_images (product_id, image_url, secure_url, public_id, alt_text, is_primary, sort_order, color)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$product_id, $uploadResult['url'], $uploadResult['secure_url'], $uploadResult['public_id'], $alt_text, $is_primary, $nextOrder, $color]);
                    $imageUrl = $uploadResult['secure_url'] ?? $uploadResult['url'];
                } else {
                    $db->insert("INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order, color)
                                 VALUES (?, ?, ?, ?, ?, ?)",
                        [$product_id, $uploadResult['url'], $alt_text, $is_primary, $nextOrder, $color]);
                    $imageUrl = $uploadResult['url'];
                }

                $image_id = $db->lastInsertId();

                $response = [
                    'success' => true,
                    'message' => 'Ảnh đã được upload thành công!',
                    'image' => [
                        'image_id' => $image_id,
                        'image_url' => $imageUrl,
                        'alt_text' => $alt_text,
                        'is_primary' => $is_primary,
                        'sort_order' => $nextOrder,
                        'color' => $color
                    ]
                ];
            } catch (Exception $e) {
                // Xóa ảnh đã upload nếu lưu DB thất bại
                if (USE_CLOUDINARY && !empty($uploadResult['public_id'])) {
                    deleteFromCloudinary($uploadResult['public_id']);
                } elseif (!USE_CLOUDINARY && !empty($uploadResult['path'])) {
                    deleteLocalImage($uploadResult['path']);
                }
                $response = ['success' => false, 'message' => 'Lỗi lưu database: ' . $e->getMessage()];
            }
            break;

        case 'delete_product_image':
            $image_id = (int)($_POST['image_id'] ?? 0);

            if ($image_id <= 0) {
                $response = ['success' => false, 'message' => 'ID ảnh không hợp lệ!'];
                break;
            }

            // Lấy thông tin ảnh
            $image = $db->selectOne("SELECT * FROM product_images WHERE image_id = ?", [$image_id]);
            if (!$image) {
                $response = ['success' => false, 'message' => 'Ảnh không tồn tại!'];
                break;
            }

            // Kiểm tra nếu là ảnh duy nhất
            $imageCount = $db->selectOne("SELECT COUNT(*) as total FROM product_images WHERE product_id = ?", [$image['product_id']]);
            if ($imageCount['total'] <= 1) {
                $response = ['success' => false, 'message' => 'Sản phẩm phải có ít nhất 1 ảnh!'];
                break;
            }

            try {
                // Xóa file ảnh
                if (USE_CLOUDINARY && !empty($image['public_id'])) {
                    deleteFromCloudinary($image['public_id']);
                } elseif (!USE_CLOUDINARY && !empty($image['image_url'])) {
                    // Trích xuất path từ URL local
                    $pathParts = parse_url($image['image_url']);
                    $path = $pathParts['path'] ?? '';
                    // Loại bỏ leading slash và trích xuất đường dẫn tương đối từ sau /uploads/ hoặc uploads/
                    $cleanPath = ltrim($path, '/');
                    if (strpos($cleanPath, 'uploads/') === 0) {
                        $localPath = substr($cleanPath, strlen('uploads/'));
                        deleteLocalImage($localPath);
                    } elseif (strpos($cleanPath, 'assets/images/products/') === 0) {
                        // Tương thích ngược với ảnh cũ
                        $oldFullPath = __DIR__ . '/' . $cleanPath;
                        if (file_exists($oldFullPath)) {
                            unlink($oldFullPath);
                        }
                    }
                }

                // Xóa khỏi database
                $db->update("DELETE FROM product_images WHERE image_id = ?", [$image_id]);

                // Nếu là ảnh chính bị xóa, đặt ảnh đầu tiên còn lại làm chính
                if ($image['is_primary']) {
                    $firstImage = $db->selectOne("SELECT image_id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1", [$image['product_id']]);
                    if ($firstImage) {
                        $db->update("UPDATE product_images SET is_primary = 1 WHERE image_id = ?", [$firstImage['image_id']]);
                    }
                }

                $response = ['success' => true, 'message' => 'Ảnh đã được xóa!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'set_primary_image':
            $image_id = (int)($_POST['image_id'] ?? 0);

            if ($image_id <= 0) {
                $response = ['success' => false, 'message' => 'ID ảnh không hợp lệ!'];
                break;
            }

            // Lấy thông tin ảnh
            $image = $db->selectOne("SELECT product_id FROM product_images WHERE image_id = ?", [$image_id]);
            if (!$image) {
                $response = ['success' => false, 'message' => 'Ảnh không tồn tại!'];
                break;
            }

            try {
                // Bỏ đánh dấu tất cả ảnh chính của sản phẩm
                $db->update("UPDATE product_images SET is_primary = 0 WHERE product_id = ?", [$image['product_id']]);

                // Đặt ảnh mới làm chính
                $db->update("UPDATE product_images SET is_primary = 1 WHERE image_id = ?", [$image_id]);

                $response = ['success' => true, 'message' => 'Đã đặt làm ảnh chính!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_image_color':
            $image_id = (int)($_POST['image_id'] ?? 0);
            $color = isset($_POST['color']) ? trim($_POST['color']) : null;
            if ($color === '') $color = null;

            if ($image_id <= 0) {
                $response = ['success' => false, 'message' => 'ID ảnh không hợp lệ!'];
                break;
            }

            // Kiểm tra ảnh tồn tại
            $image = $db->selectOne("SELECT image_id FROM product_images WHERE image_id = ?", [$image_id]);
            if (!$image) {
                $response = ['success' => false, 'message' => 'Ảnh không tồn tại!'];
                break;
            }

            try {
                $db->update("UPDATE product_images SET color = ? WHERE image_id = ?", [$color, $image_id]);
                $response = ['success' => true, 'message' => 'Cập nhật màu sắc ảnh thành công!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'get_product_images':
            $product_id = (int)($_GET['product_id'] ?? 0);

            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'ID sản phẩm không hợp lệ!'];
                break;
            }

            $images = $db->select("SELECT image_id, image_url, secure_url, alt_text, is_primary, sort_order, color
                                   FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC", [$product_id]);

            $response = ['success' => true, 'images' => $images];
            break;

        case 'upload_brand_logo':
            // Kiểm tra file upload
            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'File vượt quá giới hạn server',
                    UPLOAD_ERR_FORM_SIZE => 'File vượt quá giới hạn form',
                    UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
                    UPLOAD_ERR_NO_FILE => 'Không có file nào được chọn',
                ];
                $errorCode = $_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE;
                $errorMsg = $uploadErrors[$errorCode] ?? 'Lỗi không xác định';
                $response = ['success' => false, 'message' => $errorMsg];
                break;
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg', 'image/svg+xml'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['logo']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowedTypes)) {
                $response = ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP, SVG)!'];
                break;
            }

            // Validate file size (5MB max for logos)
            if ($_FILES['logo']['size'] > 5 * 1024 * 1024) {
                $response = ['success' => false, 'message' => 'Kích thước logo không được vượt quá 5MB!'];
                break;
            }

            // Upload lên Cloudinary
            $uploadResult = uploadToCloudinary($_FILES['logo']['tmp_name'], [
                'public_id' => 'brand_logo_' . time()
            ]);

            if (!$uploadResult || !$uploadResult['success']) {
                $response = ['success' => false, 'message' => 'Lỗi upload logo: ' . ($uploadResult['error'] ?? 'Không xác định')];
                break;
            }

            $response = [
                'success' => true,
                'message' => 'Logo đã được upload thành công!',
                'logo_url' => $uploadResult['secure_url'] ?? $uploadResult['url'],
                'public_id' => $uploadResult['public_id']
            ];
            break;

        // ==================== SHIPPING PRICES ====================
        case 'get_shipping_price':
            $shipping_id = (int)($_GET['id'] ?? 0);
            if ($shipping_id <= 0) {
                $response = ['success' => false, 'message' => 'ID phí vận chuyển không hợp lệ!'];
                break;
            }

            $shipping = $db->selectOne("SELECT * FROM shipping_prices WHERE shipping_id = ?", [$shipping_id]);
            if ($shipping) {
                $response = ['success' => true, 'shipping' => $shipping];
            } else {
                $response = ['success' => false, 'message' => 'Không tìm thấy cấu hình phí vận chuyển!'];
            }
            break;

        case 'add_shipping_price':
            $province_city = trim($_POST['province_city'] ?? '');
            $base_price = (float)($_POST['base_price'] ?? 0);
            $estimated_days = (int)($_POST['estimated_days'] ?? 3);

            if (empty($province_city) || $base_price < 0 || $estimated_days <= 0) {
                $response = ['success' => false, 'message' => 'Thông tin nhập vào không hợp lệ!'];
                break;
            }

            try {
                $db->beginTransaction();

                // Check duplicate
                $exists = $db->selectOne("SELECT shipping_id FROM shipping_prices WHERE province_city = ?", [$province_city]);
                if ($exists) {
                    $response = ['success' => false, 'message' => 'Cấu hình phí vận chuyển cho tỉnh/thành phố này đã tồn tại!'];
                    $db->rollback();
                    break;
                }

                $db->insert("
                    INSERT INTO shipping_prices (province_city, base_price, estimated_days) 
                    VALUES (?, ?, ?)
                ", [$province_city, $base_price, $estimated_days]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Đã thêm cấu hình phí vận chuyển mới!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'update_shipping_price':
            $shipping_id = (int)($_POST['shipping_id'] ?? 0);
            $province_city = trim($_POST['province_city'] ?? '');
            $base_price = (float)($_POST['base_price'] ?? 0);
            $estimated_days = (int)($_POST['estimated_days'] ?? 3);

            if ($shipping_id <= 0 || empty($province_city) || $base_price < 0 || $estimated_days <= 0) {
                $response = ['success' => false, 'message' => 'Thông tin nhập vào không hợp lệ!'];
                break;
            }

            try {
                $db->beginTransaction();

                // Check duplicate excluding self
                $exists = $db->selectOne("SELECT shipping_id FROM shipping_prices WHERE province_city = ? AND shipping_id != ?", [$province_city, $shipping_id]);
                if ($exists) {
                    $response = ['success' => false, 'message' => 'Cấu hình phí vận chuyển cho tỉnh/thành phố này đã tồn tại!'];
                    $db->rollback();
                    break;
                }

                $db->update("
                    UPDATE shipping_prices 
                    SET province_city = ?, base_price = ?, estimated_days = ? 
                    WHERE shipping_id = ?
                ", [$province_city, $base_price, $estimated_days, $shipping_id]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Đã cập nhật cấu hình phí vận chuyển!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'delete_shipping_price':
            $shipping_id = (int)($_POST['shipping_id'] ?? 0);
            if ($shipping_id <= 0) {
                $response = ['success' => false, 'message' => 'ID không hợp lệ!'];
                break;
            }

            if ($shipping_id == 1) {
                $response = ['success' => false, 'message' => 'Không thể xóa cấu hình phí vận chuyển mặc định (TP. Hồ Chí Minh)!'];
                break;
            }

            // Check if any active/unfinished orders are linked to this shipping_id
            $active_orders = $db->select("
                SELECT order_id FROM orders 
                WHERE shipping_id = ? 
                AND order_status NOT IN ('delivered', 'cancelled', 'returned')
            ", [$shipping_id]);

            if (!empty($active_orders)) {
                $response = ['success' => false, 'message' => 'Không thể xóa cấu hình phí vận chuyển này vì đang có đơn hàng chưa hoàn thành áp dụng mức phí này!'];
                break;
            }

            try {
                $db->beginTransaction();

                // Update completed/cancelled orders to fallback to default shipping_id = 1
                $db->update("UPDATE orders SET shipping_id = 1 WHERE shipping_id = ?", [$shipping_id]);

                // Delete from shipping_prices
                $db->delete("DELETE FROM shipping_prices WHERE shipping_id = ?", [$shipping_id]);

                $db->commit();
                $response = ['success' => true, 'message' => 'Đã xóa cấu hình phí vận chuyển thành công!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'add_featured_product':
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Sản phẩm không hợp lệ!'];
                break;
            }
            try {
                $db->update("UPDATE products SET is_featured = 1, featured_sort_order = 999 WHERE product_id = ?", [$product_id]);
                $response = ['success' => true, 'message' => 'Đã thêm sản phẩm vào danh sách nổi bật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'remove_featured_product':
            $product_id = (int)($_POST['product_id'] ?? 0);
            if ($product_id <= 0) {
                $response = ['success' => false, 'message' => 'Sản phẩm không hợp lệ!'];
                break;
            }
            try {
                $db->update("UPDATE products SET is_featured = 0, featured_sort_order = 999 WHERE product_id = ?", [$product_id]);
                $response = ['success' => true, 'message' => 'Đã xóa sản phẩm khỏi danh sách nổi bật!'];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
            }
            break;

        case 'save_featured_order':
            $product_ids = $_POST['product_ids'] ?? [];
            $sort_orders = $_POST['sort_orders'] ?? [];
            if (empty($product_ids)) {
                $response = ['success' => false, 'message' => 'Không có sản phẩm nào được cập nhật!'];
                break;
            }
            try {
                $db->beginTransaction();
                foreach ($product_ids as $index => $pid) {
                    $pid = (int)$pid;
                    $order_val = !empty($sort_orders) ? (int)($sort_orders[$index] ?? 999) : ($index + 1);
                    $db->update("UPDATE products SET featured_sort_order = ? WHERE product_id = ?", [$order_val, $pid]);
                }
                $db->commit();
                $response = ['success' => true, 'message' => 'Đã lưu thứ tự sắp xếp thành công!'];
            } catch (Exception $e) {
                $db->rollback();
                $response = ['success' => false, 'message' => 'Lỗi khi cập nhật: ' . $e->getMessage()];
            }
            break;
    }

    echo json_encode($response);
    exit;
}

echo json_encode($response);
