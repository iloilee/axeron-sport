<?php
/**
 * About page redirect
 */
require_once __DIR__ . '/config/session.php';

// Redirect to static HTML page
header('Location: ' . BASE_URL . '/about.html');
exit;
