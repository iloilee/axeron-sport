<?php
$pages = [
    'pages/about.php' => 'V? Axeron',
    'pages/contact.php' => 'Liên h?',
    'pages/store-locator.php' => 'H? th?ng c?a hàng'
];

foreach ($pages as $file => $title) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    
    // Check if it already has the head
    if (strpos($content, 'includes/head.php') === false) {
        // Find where includes/header.php is
        $insertStr = "\n$pageTitle = '$title - Axeron Sport';\nrequire_once __DIR__ . '/includes/head.php';\n";
        
        $content = str_replace("require_once __DIR__ . '/includes/header.php';", $insertStr . "require_once __DIR__ . '/includes/header.php';", $content);
        
        // At the end, after footer, add </body></html>
        $content = str_replace("<?php require_once __DIR__ . '/includes/footer.php'; ?>", "<?php require_once __DIR__ . '/includes/footer.php'; ?>\n</body>\n</html>", $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>
