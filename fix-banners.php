<?php
/**
 * FIX SCRIPT - Update Banner Status
 */

require_once 'config/db.php';

echo "<h1>Banner Status Fix</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} .success{color:green;font-weight:bold;} .error{color:red;font-weight:bold;}</style>";

// Update all hero/home banners to have status='active'
$query = "UPDATE banners SET status = 'active' WHERE position = 'hero' AND page = 'home' AND (status IS NULL OR status = '')";

if ($conn->query($query)) {
    $affected = $conn->affected_rows;
    echo "<p class='success'>✓ Successfully updated $affected banner(s) to status='active'</p>";
} else {
    echo "<p class='error'>✗ Error updating banners: " . $conn->error . "</p>";
}

// Show updated banners
echo "<h2>Updated Hero/Home Banners</h2>";
$result = $conn->query("SELECT id, title, position, page, status, sort_order FROM banners WHERE position = 'hero' AND page = 'home' ORDER BY sort_order ASC");

if ($result && $result->num_rows > 0) {
    echo "<table style='border-collapse:collapse;width:100%;margin:20px 0;'>";
    echo "<tr style='background:#f97316;color:white;'><th style='border:1px solid #ddd;padding:8px;'>ID</th><th style='border:1px solid #ddd;padding:8px;'>Title</th><th style='border:1px solid #ddd;padding:8px;'>Position</th><th style='border:1px solid #ddd;padding:8px;'>Page</th><th style='border:1px solid #ddd;padding:8px;'>Status</th><th style='border:1px solid #ddd;padding:8px;'>Sort Order</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $statusColor = $row['status'] === 'active' ? 'green' : 'red';
        echo "<tr>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>{$row['id']}</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>{$row['title']}</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>{$row['position']}</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>{$row['page']}</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;color:$statusColor;font-weight:bold;'>{$row['status']}</td>";
        echo "<td style='border:1px solid #ddd;padding:8px;'>{$row['sort_order']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p class='success'>✓ All hero/home banners are now active!</p>";
} else {
    echo "<p class='error'>No hero/home banners found</p>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background:#f97316;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>← Go to Homepage</a></p>";
echo "<p><a href='debug-banners.php' style='background:#3b82f6;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>🔍 Run Debug Again</a></p>";

$conn->close();
?>
