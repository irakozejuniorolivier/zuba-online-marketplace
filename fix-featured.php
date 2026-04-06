<?php
// Quick fix for featured properties query
// Replace lines 33-45 in index.php with this:

// ===== FETCH FEATURED PROPERTIES =====
$featured_properties = [];
$result = $conn->query("
    SELECT p.*, pi.image_path 
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
    WHERE p.featured = 1 AND p.status = 'available'
    ORDER BY p.created_at DESC
    LIMIT 6
");
if ($result) {
    $featured_properties = $result->fetch_all(MYSQLI_ASSOC);
}
?>
