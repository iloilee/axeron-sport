<?php
require 'c:/xampp/htdocs/axeron-sport-website-master/config/database.php';
$db = db();
print_r($db->select('SHOW CREATE TABLE reviews'));
