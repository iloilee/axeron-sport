<?php
$pages = [
    'about.html' => 'about.php',
    'contact.html' => 'contact.php',
    'store-locator.html' => 'store-locator.php'
];

foreach ($pages as $htmlFile => $phpFile) {
    if (!file_exists($htmlFile)) continue;
    $html = file_get_contents($htmlFile);
    
    $mainContent = '';
    if (preg_match('/<main[^>]*>.*?<\/main>/s', $html, $matches)) {
        $mainContent = $matches[0];
    }
    
    $floatingNav = '';
    if (preg_match('/<!--\s*SideNavBar\s*.*?(<nav\s+class="fixed[^>]*>.*?<\/nav>|<div\s+class="fixed[^>]*>.*?<\/div>\s*<!--\s*Footer\s*-->)/s', $html, $matches)) {
        // Try to be more specific with the floating nav
        if (preg_match('/(<(nav|div)\s+class="fixed right-4 bottom-24[^>]*>.*?<\/\2>)/s', $html, $m)) {
             $floatingNav = $m[0];
        }
    }
    
    // Fallback if the previous regex failed
    if (empty($floatingNav)) {
        if (preg_match('/(<(nav|div)\s+class="fixed right-4 bottom-24[^>]*>.*?<\/\2>)/s', $html, $m)) {
             $floatingNav = $m[0];
        }
    }

    $template = "<?php\nrequire_once __DIR__ . '/config/session.php';\nrequire_once __DIR__ . '/includes/header.php';\n?>\n\n" . $mainContent . "\n\n" . $floatingNav . "\n\n<?php require_once __DIR__ . '/includes/footer.php'; ?>\n";
    
    file_put_contents($phpFile, $template);
    echo "Processed $phpFile\n";
}
?>
