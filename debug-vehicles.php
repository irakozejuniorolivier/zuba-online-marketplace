<?php
require_once 'config/db.php';
require_once 'config/config.php';

$result = $conn->query("SELECT vi.id, vi.vehicle_id, vi.image_path, vi.is_primary FROM vehicle_images vi LIMIT 20");

echo "<h3>Vehicle Images in DB:</h3><table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Vehicle ID</th><th>image_path (raw)</th><th>Generated URL</th><th>File Exists?</th></tr>";

while ($row = $result->fetch_assoc()) {
    $raw = $row['image_path'];
    
    // Current logic using basename
    $url_basename = UPLOAD_URL . 'vehicles/' . basename($raw);
    
    // Check if file exists on disk
    $file_path = $_SERVER['DOCUMENT_ROOT'] . '/zuba-online-market/uploads/vehicles/' . basename($raw);
    $exists = file_exists($file_path) ? '✅ YES' : '❌ NO';
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['vehicle_id']}</td>";
    echo "<td><code>$raw</code></td>";
    echo "<td><a href='$url_basename' target='_blank'>$url_basename</a></td>";
    echo "<td>$exists</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><strong>UPLOAD_URL = " . UPLOAD_URL . "</strong><br>";
echo "<strong>UPLOAD_PATH = " . UPLOAD_PATH . "</strong><br>";

// List actual files on disk
echo "<h3>Files on disk in uploads/vehicles/:</h3><ul>";
$dir = $_SERVER['DOCUMENT_ROOT'] . '/zuba-online-market/uploads/vehicles/';
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f !== '.' && $f !== '..') echo "<li>$f</li>";
    }
} else {
    echo "<li style='color:red'>Directory not found: $dir</li>";
}
echo "</ul>";
?>
