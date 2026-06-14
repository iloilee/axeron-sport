<?php
require 'c:/xampp/htdocs/axeron-sport-website-master/config/database.php';
$db = db();
$db->update("UPDATE site_settings SET setting_value = '/policies/purchase-policy.php' WHERE setting_key = 'policy_terms_url'");
echo "Fixed policy terms url\n";
