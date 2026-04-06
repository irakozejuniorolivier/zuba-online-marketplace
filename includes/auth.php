<?php
/**
 * ============================================
 * CUSTOMER AUTHENTICATION FUNCTIONS
 * ============================================
 * Public website authentication and authorization
 */

// ===== CUSTOMER LOGIN CHECK =====
function requireCustomerLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['customer_id'])) {
        setFlash('warning', 'Please login to continue');
        redirect(SITE_URL . '/login.php');
    }
}

// ===== GUEST CUSTOMER CHECK =====
function requireGuestCustomer() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['customer_id'])) {
        redirect(SITE_URL . '/profile.php');
    }
}

// ===== GET CURRENT CUSTOMER =====
function currentCustomer() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['customer'] ?? null;
}

// ===== GET CURRENT CUSTOMER ID =====
function currentCustomerId() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['customer_id'] ?? null;
}

// ===== CHECK IF CUSTOMER IS LOGGED IN =====
function isCustomerLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['customer_id']) && isset($_SESSION['customer']);
}

// ===== LOG CUSTOMER ACTIVITY =====
function logActivity($conn, $user_type, $user_id, $action, $description = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sissss', $user_type, $user_id, $action, $description, $ip, $ua);
    $stmt->execute();
    $stmt->close();
}

// ===== CUSTOMER LOGOUT =====
function logoutCustomer() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    session_destroy();
    redirect(SITE_URL . '/');
}
