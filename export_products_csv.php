<?php
/**
 * Script export bảng products ra file CSV (chuẩn bị cho Kaggle)
 */
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();

// Lấy thêm brand_name và category_name để làm giàu ngữ nghĩa (như trong plan của bạn)
$products = $db->select("
    SELECT 
        p.product_id, 
        p.product_name, 
        p.description,
        b.brand_name,
        c.category_name
    FROM products p
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN categories c ON p.category_id = c.category_id
");

if (empty($products)) {
    die("Không có sản phẩm nào để export.");
}

$filename = __DIR__ . '/products_active.csv';
$file = fopen($filename, 'w');

// Ghi UTF-8 BOM để Excel đọc tiếng Việt không bị lỗi font (nếu cần)
fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

// Ghi Header
fputcsv($file, ['product_id', 'product_name', 'description', 'brand_name', 'category_name']);

// Ghi Dữ liệu
$count = 0;
foreach ($products as $p) {
    // Làm sạch cơ bản trước khi đưa vào CSV (nếu muốn)
    $desc = $p['description'] ?? '';
    fputcsv($file, [
        $p['product_id'],
        $p['product_name'],
        $desc,
        $p['brand_name'] ?? '',
        $p['category_name'] ?? ''
    ]);
    $count++;
}

fclose($file);
echo "Exported $count products to products_active.csv";
