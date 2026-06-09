<?php
/**
 * Logout Handler
 */
require_once __DIR__ . '/../config/session.php';

logoutUser();
setFlash('success', 'Đăng xuất thành công!');
header('Location: ' . BASE_URL . '/');
exit;
