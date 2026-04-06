<?php
require_once 'config/db.php';

$property_id = 3; // Test with property ID 3

echo "<h2>Property Images for ID: $property_id</h2>";
$stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);

echo "Total images found: " . count($images) . "<br><br>";
echo "<pre>";
print_r($images);
echo "</pre>";

if (empty($images)) {
    echo "<h3>Checking all property images:</h3>";
    $result = $conn->query("SELECT * FROM property_images LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
}
?>
