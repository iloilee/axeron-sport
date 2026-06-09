<?php
/**
 * Privacy Policy page redirect
 */
require_once __DIR__ . '/../config/session.php';

header('Location: ' . BASE_URL . '/policies/privacy-policy.html');
exit;
