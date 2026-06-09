<?php
/**
 * Contact page redirect
 */
require_once __DIR__ . '/config/session.php';

header('Location: ' . BASE_URL . '/contact.html');
exit;
