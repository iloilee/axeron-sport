<?php
require 'c:/xampp/htdocs/axeron-sport-website-master/config/database.php';
$db = db();

$social_links = [
    'social_facebook' => 'https://facebook.com',
    'social_instagram' => 'https://instagram.com',
    'social_youtube' => 'https://youtube.com',
    'social_tiktok' => 'https://www.tiktok.com/',
    'social_zalo' => 'https://zalo.me/vi/'
];

foreach ($social_links as $key => $url) {
    $db->update("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?", [$url, $key]);
}

echo "Social links updated\n";
