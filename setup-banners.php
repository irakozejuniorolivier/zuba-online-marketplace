<?php
session_start();
require_once 'config/db.php';

// Update all banners with NULL or empty status to 'active'
$conn->query("UPDATE banners SET status = 'active' WHERE status IS NULL OR status = ''");

// Verify
$count = $conn->query("SELECT COUNT(*) as cnt FROM banners WHERE status = 'active'")->fetch_assoc()['cnt'];
$hero_count = $conn->query("SELECT COUNT(*) as cnt FROM banners WHERE position = 'hero' AND page IN ('home', 'all') AND status = 'active'")->fetch_assoc()['cnt'];

echo "<h2 style='color: green;'>✓ Setup Complete!</h2>";
echo "<p>Active banners: <strong>$count</strong></p>";
echo "<p>Hero banners for homepage: <strong>$hero_count</strong></p>";
echo "<p><a href='index.php' style='padding: 10px 20px; background: #f97316; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Homepage</a></p>";

// Delete this file after 5 seconds
echo "<script>setTimeout(() => { fetch('setup-banners.php?delete=1'); }, 5000);</script>";

if (isset($_GET['delete'])) {
    @unlink(__FILE__);
}
?>
