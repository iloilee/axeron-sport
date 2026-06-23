<?php
if (!isset($settings)) {
    $_headDb = db();
    $_headRaw = $_headDb->select("SELECT setting_key, setting_value FROM site_settings WHERE is_public = 1");
    $settings = [];
    foreach ($_headRaw as $s) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }
}
$siteFaviconUrl = $settings['site_favicon'] ?? '/assets/images/logo-axeron.jpg';
if (strpos($siteFaviconUrl, 'http') !== 0 && !empty($siteFaviconUrl)) {
    $siteFaviconUrl = (defined('BASE_URL') ? BASE_URL : '') . (strpos($siteFaviconUrl, '/') === 0 ? '' : '/') . $siteFaviconUrl;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="<?= htmlspecialchars(generateCsrfToken()) ?>">
    <?php
        $defaultSiteName = $settings['site_name'] ?? 'Axeron Sport';
        $defaultTagline = $settings['site_tagline'] ?? 'Dụng cụ thể thao chuyên nghiệp';
        if (isset($pageTitle)) {
            $displayTitle = htmlspecialchars($pageTitle) . ' - ' . htmlspecialchars($defaultSiteName);
        } else {
            $displayTitle = htmlspecialchars($defaultSiteName) . ' - ' . htmlspecialchars($defaultTagline);
        }
    ?>
    <title><?= $displayTitle ?></title>
    <link rel="icon" href="<?= htmlspecialchars($siteFaviconUrl) ?>" />
    <!-- Compiled Tailwind CSS -->
    <link rel="stylesheet" href="<?= (defined('BASE_URL') ? BASE_URL : '') ?>/assets/css/output.css?v=<?= filemtime(__DIR__ . '/../assets/css/output.css') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin/>
    <link href="https://unpkg.com" rel="preconnect"/>
    <link href="https://res.cloudinary.com" rel="preconnect"/>
    <link rel="dns-prefetch" href="https://res.cloudinary.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <script>
        // Init Dark Mode early to prevent FOUC
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<?php $bodyClass = $bodyClass ?? 'bg-background text-on-background font-body-md antialiased'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
