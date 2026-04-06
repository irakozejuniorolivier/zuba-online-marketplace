<?php
/**
 * API: Get Cart and Wishlist Counts
 * Returns the count of items in cart and wishlist for logged-in user
 */

session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    echo json_encode([
        'success' => false,
        'cart_count' => 0,
        'wishlist_count' => 0
    ]);
    exit;
}

$user_id = currentCustomerId();

// Get cart count
$cart_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$cart_count = $row['count'];
$stmt->close();

// Get wishlist count
$wishlist_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$wishlist_count = $row['count'];
$stmt->close();

echo json_encode([
    'success' => true,
    'cart_count' => (int)$cart_count,
    'wishlist_count' => (int)$wishlist_count
]);
