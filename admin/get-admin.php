<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $admin = $conn->query("SELECT id, name, email, phone, profile_image, status FROM admins WHERE id = $id")->fetch_assoc();
    
    if ($admin) {
        header('Content-Type: application/json');
        echo json_encode($admin);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Admin not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID required']);
}
?>
