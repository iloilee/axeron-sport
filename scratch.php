<?php
require 'config/database.php';
$db = db();
$logs = $db->select("DESCRIBE order_status_logs");
print_r($logs);
