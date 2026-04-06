<?php
session_start();
require_once '../config/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = currentCustomerId();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['item_type']) || !isset($input['item_id']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$item_type = $input['item_type'];
$item_id = intval($input['item_id']);
$action = $input['action'];

// Validate item_type
if (!in_array($item_type, ['product', 'property', 'vehicle'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid item type']);
    exit;
}

try {
    if ($action === 'add') {
        // Check if already in wishlist
        $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $stmt->bind_param('isi', $user_id, $item_type, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
            exit;
        }
        $stmt->close();
        
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, item_type, item_id) VALUES (?, ?, ?)");
        $stmt->bind_param('isi', $user_id, $item_type, $item_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
        }
        $stmt->close();
        
    } elseif ($action === 'remove') {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND item_type = ? AND item_id = ?");
        $stmt->bind_param('isi', $user_id, $item_type, $item_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
        }
        $stmt->close();
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
