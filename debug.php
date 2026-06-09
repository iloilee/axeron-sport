<?php
require_once __DIR__ . '/config/session.php';

echo "BASE_URL: " . BASE_URL;
echo "\n";
echo "host: " . ($_SERVER['HTTP_HOST'] ?? 'N/A');
echo "\n";
echo "script: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A');
echo "\n";
echo "dirname: " . dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
