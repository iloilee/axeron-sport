<?php
$pages = [
    'about.html' => ['about.php', 'Về Axeron'],
    'contact.html' => ['contact.php', 'Liên hệ'],
    'store-locator.html' => ['store-locator.php', 'Hệ thống cửa hàng']
];

foreach ($pages as $htmlFile => $info) {
    list($phpFile, $title) = $info;
    if (!file_exists($htmlFile)) continue;
    
    $html = file_get_contents($htmlFile);
    
    $mainContent = '';
    if (preg_match('/<main[^>]*>.*?<\/main>/s', $html, $matches)) {
        $mainContent = $matches[0];
    }
    
    $floatingNav = '';
    if (preg_match('/<!--\s*SideNavBar\s*.*?(<nav\s+class="fixed[^>]*>.*?<\/nav>|<div\s+class="fixed[^>]*>.*?<\/div>\s*<!--\s*Footer\s*-->)/s', $html, $matches)) {
        if (preg_match('/(<(nav|div)\s+class="fixed right-4 bottom-24[^>]*>.*?<\/\2>)/s', $html, $m)) {
             $floatingNav = $m[0];
        }
    }
    
    if (empty($floatingNav)) {
        if (preg_match('/(<(nav|div)\s+class="fixed right-4 bottom-24[^>]*>.*?<\/\2>)/s', $html, $m)) {
             $floatingNav = $m[0];
        }
    }

    $template = "<?php\nrequire_once __DIR__ . '/config/session.php';\n\$pageTitle = '" . addslashes($title) . " - Axeron Sport';\nrequire_once __DIR__ . '/includes/head.php';\nrequire_once __DIR__ . '/includes/header.php';\n?>\n\n" . $mainContent . "\n\n" . $floatingNav . "\n\n<?php require_once __DIR__ . '/includes/footer.php'; ?>\n</body>\n</html>";
    
    file_put_contents($phpFile, $template);
    echo "Processed $phpFile\n";
}
?>
