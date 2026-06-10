<?php
$projectDir = 'c:/xampp/htdocs/axeron-sport-website-master';
if (!is_dir($projectDir . '/admin')) {
    mkdir($projectDir . '/admin');
}

// 1. Move all admin*.php files
$adminFiles = glob($projectDir . '/admin*.php');
foreach ($adminFiles as $file) {
    $basename = basename($file);
    $newPath = $projectDir . '/admin/' . $basename;
    rename($file, $newPath);
}

// 2. Remove HTML files in admin/
$htmlFiles = glob($projectDir . '/admin/*.html');
foreach ($htmlFiles as $htmlFile) {
    unlink($htmlFile);
}

// 3. Update paths in the newly moved admin/*.php files
$movedFiles = glob($projectDir . '/admin/admin*.php');
foreach ($movedFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace __DIR__ . '/config/' with __DIR__ . '/../config/'
    $content = preg_replace('/__DIR__\s*\.\s*([\'""])\/config\//', '__DIR__ . /../config/', $content);
    $content = preg_replace('/__DIR__\s*\.\s*([\'""])\/includes\//', '__DIR__ . /../includes/', $content);
    $content = preg_replace('/__DIR__\s*\.\s*([\'""])\/database\//', '__DIR__ . /../database/', $content);
    
    // Also if there's any simple 'config/' require not using __DIR__
    $content = preg_replace('/require_once\s+([\'""])config\//', 'require_once ../config/', $content);
    $content = preg_replace('/require_once\s+([\'""])includes\//', 'require_once ../includes/', $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
    }
}

// 4. Update admin links in root files
$rootFiles = glob($projectDir . '/*.php');
foreach ($rootFiles as $file) {
    $content = file_get_contents($file);
    $original = $content;
    
    // Replace admin-products.php with admin/admin-products.php
    $content = preg_replace('/([\'""])admin-([a-zA-Z0-9_-]+)\.php([\'""])/', 'admin/admin-.php', $content);
    // Replace admin.php with admin/admin.php (careful to not replace admin/admin.php)
    $content = preg_replace('/([\'""])\/?admin\.php([\'""])/', 'admin/admin.php', $content);
    // Fix if it became admin/admin.php
    $content = str_replace('admin/admin', 'admin/admin', $content);
    
    if ($content !== $original) {
        file_put_contents($file, $content);
    }
}

// 5. Update admin links in subfolders
$subdirs = ['includes', 'auth', 'shop', 'blog', 'api'];
foreach ($subdirs as $dir) {
    if (!is_dir($projectDir . '/' . $dir)) continue;
    
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectDir . '/' . $dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $original = $content;
            
            // Note: BASE_URL . '/admin-products.php'
            $content = preg_replace('/([\'""])\/?admin-([a-zA-Z0-9_-]+)\.php([\'""])/', '/admin/admin-.php', $content);
            $content = preg_replace('/([\'""])\/?admin\.php([\'""])/', '/admin/admin.php', $content);
            // Fix double admin
            $content = str_replace('/admin/admin', '/admin/admin', $content);
            
            if ($content !== $original) {
                file_put_contents($file->getPathname(), $content);
            }
        }
    }
}

echo "Migration done.";
?>
