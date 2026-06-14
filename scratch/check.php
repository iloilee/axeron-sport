<?php
require 'c:/xampp/htdocs/axeron-sport-website-master/config/database.php';
$res = db()->select("SELECT setting_key, setting_value, is_public FROM site_settings WHERE group_name='footer'");
print_r($res);
