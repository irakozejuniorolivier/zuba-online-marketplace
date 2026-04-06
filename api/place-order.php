<?php
/**
 * API: Place Order
 * Processes checkout and creates order with order items
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_log('=== Place Order API Called ===');

session_start();

try {
    require_once '../config/db.php';
    require_once '../config/config.php';
    require_once '../includes/auth.php';
    require_once '../includes/functions.php';
} catch (Exception $e) {
    error_log('Failed to include files: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error',
        'debug' => $e->getMessage()
    ]);
    exit;
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to place an order'
    ]);
    exit;
}

$user_id = currentCustomerId();

// Validate required fields
$required_fields = ['shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_country', 'payment_method_id'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in all required fields'
        ]);
        exit;
    }
}

// Validate payment proof upload
if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'message' => 'Please upload payment proof'
    ]);
    exit;
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get selected product IDs if provided
    $selected_ids = [];
    if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
        $selected_ids = array_map('intval', explode(',', $_POST['selected_items']));
    }
    
    // Fetch cart items
    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $cart_query = "
            SELECT c.product_id, c.quantity, p.name, p.price, p.stock, p.status
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND c.product_id IN ($placeholders)
        ";
        $stmt = $conn->prepare($cart_query);
        $types = str_repeat('i', count($selected_ids) + 1);
        $params = array_merge([$user_id], $selected_ids);
        $stmt->bind_param($types, ...$params);
    } else {
        // If no selection, use all cart items
        $cart_query = "
            SELECT c.product_id, c.quantity, p.name, p.price, p.stock, p.status
            FROM cart c
            INNER JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ";
        $stmt = $conn->prepare($cart_query);
        $stmt->bind_param('i', $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    if (empty($cart_items)) {
        throw new Exception('Your cart is empty');
    }
    
    // Validate cart items
    $subtotal = 0;
    foreach ($cart_items as $item) {
        if ($item['status'] !== 'active') {
            throw new Exception("Product '{$item['name']}' is no longer available");
        }
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Insufficient stock for '{$item['name']}'. Only {$item['stock']} available");
        }
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Get site settings
    $shipping_fee = 0;
    $tax_rate = 0;
    $result = $conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('shipping_fee', 'tax_rate')");
    while ($row = $result->fetch_assoc()) {
        if ($row['setting_key'] === 'shipping_fee') {
            $shipping_fee = (float)$row['setting_value'];
        } elseif ($row['setting_key'] === 'tax_rate') {
            $tax_rate = (float)$row['setting_value'];
        }
    }
    
    $tax = ($subtotal * $tax_rate) / 100;
    $total_amount = $subtotal + $shipping_fee + $tax;
    
    // Generate unique order number
    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Handle payment proof upload
    $payment_proof = null;
    $upload_dir = '../uploads/payment_proofs/';
    
    // Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file = $_FILES['payment_proof'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    
    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and PDF are allowed');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new Exception('File size too large. Maximum 5MB allowed');
    }
    
    $payment_proof = 'payment_' . time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $payment_proof;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to upload payment proof');
    }
    
    // Insert order
    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_number, user_id, subtotal, shipping_fee, tax, total_amount,
            payment_method_id, payment_proof, status,
            shipping_name, shipping_phone, shipping_address, shipping_city, shipping_country,
            customer_note
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'payment_submitted', ?, ?, ?, ?, ?, ?)
    ");
    
    $shipping_name = $_POST['shipping_name'];
    $shipping_phone = $_POST['shipping_phone'];
    $shipping_address = $_POST['shipping_address'];
    $shipping_city = $_POST['shipping_city'];
    $shipping_country = $_POST['shipping_country'];
    $payment_method_id = (int)$_POST['payment_method_id'];
    $customer_note = $_POST['customer_note'] ?? null;
    
    $stmt->bind_param(
        'sidddiisssssss',
        $order_number,
        $user_id,
        $subtotal,
        $shipping_fee,
        $tax,
        $total_amount,
        $payment_method_id,
        $payment_proof,
        $shipping_name,
        $shipping_phone,
        $shipping_address,
        $shipping_city,
        $shipping_country,
        $customer_note
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order');
    }
    
    $order_id = $stmt->insert_id;
    $stmt->close();
    
    // Insert order items
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['quantity'];
        $stmt->bind_param(
            'iisidd',
            $order_id,
            $item['product_id'],
            $item['name'],
            $item['quantity'],
            $item['price'],
            $item_subtotal
        );
        $stmt->execute();
    }
    $stmt->close();
    
    // Clear only ordered items from cart
    if (!empty($selected_ids)) {
        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
        $types = str_repeat('i', count($selected_ids) + 1);
        $params = array_merge([$user_id], $selected_ids);
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $stmt->close();
    
    // Log activity
    $total_formatted = number_format($total_amount, 0) . ' FRw';
    logActivity($conn, 'customer', $user_id, 'PLACE_ORDER', "Placed order: {$order_number} (Total: {$total_formatted})");
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_number' => $order_number,
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    if ($conn) {
        $conn->rollback();
    }
    
    // Delete uploaded file if exists
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    // Log error for debugging
    error_log('Place Order Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $e->getMessage() // Remove this in production
    ]);
} catch (Error $e) {
    // Catch PHP errors
    if ($conn) {
        $conn->rollback();
    }
    
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }
    
    error_log('Place Order Fatal Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your order. Please try again.',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}
