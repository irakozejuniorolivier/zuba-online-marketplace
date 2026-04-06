<?php
require_once 'config/db.php';

echo "<h2>Database Connection Test</h2>";
echo "Connected: " . ($conn->ping() ? "YES" : "NO") . "<br><br>";

echo "<h2>Properties Table Check</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM properties");
$row = $result->fetch_assoc();
echo "Total properties: " . $row['count'] . "<br><br>";

echo "<h2>Sample Properties</h2>";
$result = $conn->query("SELECT id, title, status, price FROM properties LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Status: {$row['status']} | Price: {$row['price']}<br>";
}

echo "<br><h2>Property Images Check</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM property_images");
$row = $result->fetch_assoc();
echo "Total images: " . $row['count'] . "<br><br>";

echo "<h2>Test Property ID 1</h2>";
$property_id = 1;
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM properties p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
echo "<pre>";
print_r($property);
echo "</pre>";
?>
