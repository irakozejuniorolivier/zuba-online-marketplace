<?php
/**
 * DEBUG SCRIPT - Check Banner Data
 */

require_once 'config/db.php';

echo "<h1>Banner Debug Information</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f97316;color:white;} tr:nth-child(even){background:#f9f9f9;}</style>";

// Check all banners
echo "<h2>All Banners in Database</h2>";
$result = $conn->query("SELECT id, title, position, page, status, sort_order, start_date, end_date FROM banners ORDER BY sort_order ASC");
if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Position</th><th>Page</th><th>Status</th><th>Sort Order</th><th>Start Date</th><th>End Date</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['position']}</td>";
        echo "<td>{$row['page']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['sort_order']}</td>";
        echo "<td>{$row['start_date']}</td>";
        echo "<td>{$row['end_date']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;'>No banners found in database!</p>";
}

// Check hero/home banners specifically
echo "<h2>Hero Banners for Home Page (Query Used in index.php)</h2>";
$query = "
    SELECT id, title, position, page, status, sort_order, start_date, end_date 
    FROM banners 
    WHERE position = 'hero' 
    AND page IN ('home', 'all') 
    AND status = 'active' 
    AND (start_date IS NULL OR start_date <= NOW()) 
    AND (end_date IS NULL OR end_date >= NOW())
    ORDER BY sort_order ASC
";
echo "<pre style='background:#f0f0f0;padding:10px;border:1px solid #ccc;'>$query</pre>";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo "<p style='color:green;font-weight:bold;'>Found {$result->num_rows} banner(s)</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Position</th><th>Page</th><th>Status</th><th>Sort Order</th><th>Start Date</th><th>End Date</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['position']}</td>";
        echo "<td>{$row['page']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['sort_order']}</td>";
        echo "<td>{$row['start_date']}</td>";
        echo "<td>{$row['end_date']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color:red;font-weight:bold;'>No hero/home banners found matching the query!</p>";
}

// Check current date/time
echo "<h2>Server Date/Time</h2>";
echo "<p><strong>Current Server Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check for date issues
echo "<h2>Potential Issues</h2>";
$issues = [];

$result = $conn->query("SELECT COUNT(*) as count FROM banners WHERE position = 'hero'");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $issues[] = "No banners with position='hero' found";
}

$result = $conn->query("SELECT COUNT(*) as count FROM banners WHERE page IN ('home', 'all')");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $issues[] = "No banners with page='home' or page='all' found";
}

$result = $conn->query("SELECT COUNT(*) as count FROM banners WHERE status = 'active'");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $issues[] = "No banners with status='active' found";
}

$result = $conn->query("SELECT COUNT(*) as count FROM banners WHERE end_date IS NOT NULL AND end_date < NOW()");
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    $issues[] = "{$row['count']} banner(s) have expired (end_date < NOW())";
}

$result = $conn->query("SELECT COUNT(*) as count FROM banners WHERE start_date IS NOT NULL AND start_date > NOW()");
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    $issues[] = "{$row['count']} banner(s) haven't started yet (start_date > NOW())";
}

if (empty($issues)) {
    echo "<p style='color:green;'>✓ No issues detected</p>";
} else {
    echo "<ul style='color:orange;'>";
    foreach ($issues as $issue) {
        echo "<li>⚠ $issue</li>";
    }
    echo "</ul>";
}

$conn->close();
?>
