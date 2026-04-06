<?php
require_once 'config/db.php';
require_once 'config/config.php';

echo "<h2>PRODUCTS</h2>";
$result = $conn->query("SELECT id, name, featured, status FROM products LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>PRODUCT IMAGES</h2>";
$result = $conn->query("SELECT * FROM product_images LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>FEATURED PRODUCTS WITH IMAGES</h2>";
$result = $conn->query("
    SELECT p.id, p.name, p.featured, p.status, pi.image_path
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.featured = 1 AND p.status = 'active'
");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>PROPERTIES</h2>";
$result = $conn->query("SELECT id, title, featured, status FROM properties LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>PROPERTY IMAGES</h2>";
$result = $conn->query("SELECT * FROM property_images LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>FEATURED PROPERTIES WITH IMAGES</h2>";
$result = $conn->query("
    SELECT p.id, p.title, p.featured, p.status, pi.image_path
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
    WHERE p.featured = 1 AND p.status = 'available'
");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>VEHICLES</h2>";
$result = $conn->query("SELECT id, brand, model, featured, status FROM vehicles LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>VEHICLE IMAGES</h2>";
$result = $conn->query("SELECT * FROM vehicle_images LIMIT 5");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}

echo "<h2>FEATURED VEHICLES WITH IMAGES</h2>";
$result = $conn->query("
    SELECT v.id, v.brand, v.model, v.featured, v.status, vi.image_path
    FROM vehicles v
    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1
    WHERE v.featured = 1 AND v.status = 'available'
");
echo "Total: " . $result->num_rows . "<br>";
while($row = $result->fetch_assoc()) {
    echo json_encode($row) . "<br>";
}
?>
