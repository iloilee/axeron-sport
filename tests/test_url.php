<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
$path = dirname(dirname($scriptPath);
$path = rtrim($path, '/');
$baseUrl = $protocol . $host . $path;
?>
<h2>BASE_URL Debug</h2>
<p>protocol: <?php echo $protocol; ?></p>
<p>host: <?php echo $host; ?></p>
<p>scriptPath: <?php echo $scriptPath; ?></p>
<p>basePath: <?php echo $basePath; ?></p>
<p><strong>BASE_URL: <?php echo $baseUrl; ?></strong></p>
<p><a href="<?php echo $baseUrl; ?>">Test Home</a></p>
<p><a href="<?php echo $baseUrl; ?>/auth/login.php">Test Login</a></p>
