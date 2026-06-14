<?php
require 'c:/xampp/htdocs/axeron-sport-website-master/config/database.php';
$res = db()->select("SELECT setting_key FROM site_settings WHERE group_name='social'");
print_r($res);
