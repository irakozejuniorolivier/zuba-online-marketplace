<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isCustomerLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to continue']);
    exit;
}

$user_id = currentCustomerId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $vehicle_id = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $rental_days = isset($_POST['rental_days']) ? (int)$_POST['rental_days'] : 0;
    $rate_type = isset($_POST['rate_type']) ? $_POST['rate_type'] : 'daily';
    $rate_amount = isset($_POST['rate_amount']) ? (float)$_POST['rate_amount'] : 0;
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    $pickup_location = isset($_POST['pickup_location']) ? trim($_POST['pickup_location']) : '';
    $payment_method_id = isset($_POST['payment_method_id']) ? (int)$_POST['payment_method_id'] : 0;
    $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
    $customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
    $customer_note = isset($_POST['customer_note']) ? trim($_POST['customer_note']) : '';
    
    if (!$vehicle_id || !$start_date || !$end_date || !$rental_days || !$payment_method_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    
    if (empty($customer_name) || empty($customer_phone) || empty($customer_email)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all customer information']);
        exit;
    }
    
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    if ($start === false || $end === false || $end < $start) {
        echo json_encode(['success' => false, 'message' => 'Invalid dates']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param('i', $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicle = $result->fetch_assoc();
    $stmt->close();
    
    if (!$vehicle) {
        echo json_encode(['success' => false, 'message' => 'Vehicle not found']);
        exit;
    }
    
    if ($vehicle['status'] !== 'available') {
        echo json_encode(['success' => false, 'message' => 'Vehicle is not available']);
        exit;
    }
    
    $payment_proof = null;
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['payment_proof'];
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $max_size = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowed_types)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and PDF are allowed']);
            exit;
        }
        
        if ($file['size'] > $max_size) {
            echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit']);
            exit;
        }
        
        $upload_dir = '../uploads/payment_proofs/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'payment_' . time() . '_' . uniqid() . '.' . $extension;
        $upload_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            $payment_proof = 'uploads/payment_proofs/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload payment proof']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Payment proof is required']);
        exit;
    }
    
    $booking_number = 'VB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $vehicle_name = $vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year'];
    $insurance_fee = 0;
    $dropoff_location = $pickup_location;
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO bookings (booking_number, user_id, vehicle_id, vehicle_name, start_date, end_date, rental_days, rate_type, rate_amount, subtotal, insurance_fee, total_amount, pickup_location, dropoff_location, payment_method_id, payment_proof, status, customer_note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'payment_submitted', ?, NOW())");
        
        $stmt->bind_param('siisssisddddssiss', $booking_number, $user_id, $vehicle_id, $vehicle_name, $start_date, $end_date, $rental_days, $rate_type, $rate_amount, $subtotal, $insurance_fee, $total_amount, $pickup_location, $dropoff_location, $payment_method_id, $payment_proof, $customer_note);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create booking: ' . $stmt->error);
        }
        
        $booking_id = $conn->insert_id;
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
        $stmt->bind_param('si', $customer_phone, $user_id);
        $stmt->execute();
        $stmt->close();
        
        logActivity($conn, 'customer', $user_id, 'CREATE_VEHICLE_BOOKING', 'Created vehicle booking: ' . $booking_number);
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Booking created successfully', 'booking_id' => $booking_id, 'booking_number' => $booking_number]);
        
    } catch (Exception $e) {
        $conn->rollback();
        if ($payment_proof && file_exists('../' . $payment_proof)) {
            unlink('../' . $payment_proof);
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
