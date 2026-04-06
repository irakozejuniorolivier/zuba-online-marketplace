<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $method = $conn->query("SELECT * FROM payment_methods WHERE id = $id")->fetch_assoc();
    
    if ($method) {
        header('Content-Type: application/json');
        echo json_encode($method);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Payment method not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID required']);
}
?>
