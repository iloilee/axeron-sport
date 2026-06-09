<?php
$p = empty($_SERVER['HTTPS']) ? 'http' : 'https';
$h = $_SERVER['HTTP_HOST'];
$s = dirname($_SERVER['SCRIPT_NAME']);
$s = rtrim($s, '/');
$base = $p . '://' . $h . $s;
echo "BASE_URL: <b>$base</b><br>";
echo '<a href="' . $base . '/auth/login.php">Login</a>';
