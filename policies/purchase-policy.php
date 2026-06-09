<?php
/**
 * Purchase Policy / Terms of Service page redirect
 */
require_once __DIR__ . '/../config/session.php';

header('Location: ' . BASE_URL . '/policies/purchase-policy.html');
exit;
