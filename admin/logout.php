<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if (isset($_SESSION['admin_id'])) {
    logActivity($conn, 'admin', $_SESSION['admin_id'], 'logout', 'Admin logged out');
}

session_unset();
session_destroy();

setcookie(session_name(), '', time() - 3600, '/');

redirect(ADMIN_URL . '/login.php');
