<?php
require_once __DIR__ . '/config/database.php';
$db = db();

$productsCols = $db->select("SHOW COLUMNS FROM products");
$logsCols = $db->select("SHOW COLUMNS FROM search_logs");

echo "Products cols:\n";
foreach ($productsCols as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

echo "\nSearch Logs cols:\n";
foreach ($logsCols as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
