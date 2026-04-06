<?php
/**
 * PROPERTY ORDER API
 * Handles property order operations (create, update, cancel)
 */

session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

$user_id = currentCustomerId();

// Handle POST request (Create Order)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : 0;
    $order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '';
    $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    $payment_method_id = isset($_POST['payment_method_id']) ? (int)$_POST['payment_method_id'] : 0;
    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
    $customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
    $customer_city = isset($_POST['customer_city']) ? trim($_POST['customer_city']) : '';
    $customer_address = isset($_POST['customer_address']) ? trim($_POST['customer_address']) : '';
    $customer_note = isset($_POST['customer_note']) ? trim($_POST['customer_note']) : '';
    
    // Rent details (if applicable)
    $rent_duration = isset($_POST['rent_duration']) ? (int)$_POST['rent_duration'] : null;
    $rent_period = isset($_POST['rent_period']) ? $_POST['rent_period'] : null;
    
    // Validate required fields
    if (!$property_id || !$order_type || !$amount || !$payment_method_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    if (empty($customer_name) || empty($customer_phone) || empty($customer_email) || empty($customer_city) || empty($customer_address)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all customer information']);
        exit;
    }
    
    // Validate order type
    if (!in_array($order_type, ['purchase', 'rent'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid order type']);
        exit;
    }
    
    // Fetch property details
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $stmt->close();
    
    if (!$property) {
        echo json_encode(['success' => false, 'message' => 'Property not found']);
        exit;
    }
    
    // Handle payment proof upload
    $payment_proof = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF are allowed']);
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        // Create upload directory if not exists
        $upload_dir = '../uploads/payment_proofs/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . time() . '_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $payment_proof = $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload payment proof']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment proof is required']);
        exit;
    }
    
    // Generate order number
    $order_number = 'PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO property_orders 
            (order_number, user_id, property_id, property_title, order_type, amount, 
            rent_duration, rent_period, payment_method_id, payment_proof, status, customer_note, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'payment_submitted', ?, NOW())
        ");
        
        $stmt->bind_param(
            'siissdisiss',
            $order_number,
            $user_id,
            $property_id,
            $property['title'],
            $order_type,
            $amount,
            $rent_duration,
            $rent_period,
            $payment_method_id,
            $payment_proof,
            $customer_note
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order');
        }
        
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // Update user information
        $stmt = $conn->prepare("UPDATE users SET phone = ?, city = ?, address = ? WHERE id = ?");
        $stmt->bind_param('sssi', $customer_phone, $customer_city, $customer_address, $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Log activity
        logActivity($conn, 'customer', $user_id, 'CREATE_PROPERTY_ORDER', 'Created property order: ' . $order_number);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'order_number' => $order_number
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Delete uploaded file if exists
        if ($payment_proof && file_exists($upload_dir . $payment_proof)) {
            unlink($upload_dir . $payment_proof);
        }
        
        echo json_encode(['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
