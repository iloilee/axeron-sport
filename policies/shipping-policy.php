<?php
/**
 * Shipping Policy page redirect
 */
require_once __DIR__ . '/../config/session.php';

header('Location: ' . BASE_URL . '/policies/shipping-policy.html');
exit;
