<?php
function requireAdminLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function requireGuestAdmin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['admin_id'])) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
}

function currentAdmin() {
    return $_SESSION['admin'] ?? null;
}

function logActivity($user_type, $user_id, $action, $description = '') {
    global $conn;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissss', $user_type, $user_id, $action, $description, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}
