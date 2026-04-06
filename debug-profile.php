<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/auth.php';

if (!isCustomerLoggedIn()) {
    die('Not logged in');
}

$customer_id = currentCustomerId();
$stmt = $conn->prepare("SELECT id, name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo "<h2>Debug Profile Image</h2>";
echo "<p><strong>User ID:</strong> " . $customer['id'] . "</p>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($customer['name']) . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($customer['email']) . "</p>";
echo "<p><strong>Profile Image Path (from DB):</strong> " . htmlspecialchars($customer['profile_image'] ?? 'NULL') . "</p>";
echo "<p><strong>Is Empty:</strong> " . (empty($customer['profile_image']) ? 'YES' : 'NO') . "</p>";

if (!empty($customer['profile_image'])) {
    $full_path = __DIR__ . '/' . $customer['profile_image'];
    echo "<p><strong>Full Server Path:</strong> " . htmlspecialchars($full_path) . "</p>";
    echo "<p><strong>File Exists:</strong> " . (file_exists($full_path) ? 'YES' : 'NO') . "</p>";
    
    $image_url = SITE_URL . '/' . $customer['profile_image'];
    echo "<p><strong>Image URL:</strong> " . htmlspecialchars($image_url) . "</p>";
    echo "<p><strong>Image Display:</strong></p>";
    echo "<img src='" . htmlspecialchars($image_url) . "' style='width: 100px; height: 100px; border-radius: 50%; border: 2px solid #f97316;' onerror=\"this.style.border='2px solid red'; this.alt='Failed to load';\">";
}
?>
