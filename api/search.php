<?php
header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'suggestions' => [], 'trending' => [], 'categories' => []]);
    exit;
}

$search_term = '%' . $conn->real_escape_string($query) . '%';
$suggestions = [
    'products' => [],
    'properties' => [],
    'vehicles' => []
];

// Search products with more details
$result = $conn->query("
    SELECT p.id, p.name, p.slug, p.price, pi.image_path,
           c.name as category_name
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE (p.name LIKE '$search_term' OR p.description LIKE '$search_term') 
    AND p.status = 'active'
    LIMIT 8
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $image = !empty($row['image_path']) ? UPLOAD_URL . 'products/' . $row['image_path'] : '';
        $suggestions['products'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'price' => formatCurrency($row['price']),
            'category' => $row['category_name'] ?? 'Product',
            'image' => $image,
            'type' => 'product',
            'icon' => 'box',
            'url' => SITE_URL . '/product-detail.php?id=' . $row['id']
        ];
    }
}

// Search properties with more details
$result = $conn->query("
    SELECT p.id, p.title as name, p.slug, p.price, p.city, p.listing_type,
           pi.image_path, c.name as category_name
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE (p.title LIKE '$search_term' OR p.description LIKE '$search_term' OR p.city LIKE '$search_term')
    LIMIT 8
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $image = !empty($row['image_path']) ? UPLOAD_URL . 'properties/' . $row['image_path'] : '';
        $suggestions['properties'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'price' => formatCurrency($row['price']),
            'category' => $row['category_name'] ?? 'Property',
            'location' => $row['city'] ?? '',
            'listing_type' => ucfirst($row['listing_type'] ?? ''),
            'image' => $image,
            'type' => 'property',
            'icon' => 'home',
            'url' => SITE_URL . '/property-detail.php?id=' . $row['id']
        ];
    }
}

// Search vehicles with more details
$result = $conn->query("
    SELECT v.id, CONCAT(v.brand, ' ', v.model, ' ', v.year) as name, 
           v.slug, v.daily_rate as price, v.vehicle_type,
           vi.image_path, c.name as category_name
    FROM vehicles v
    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1
    LEFT JOIN categories c ON v.category_id = c.id
    WHERE (v.brand LIKE '$search_term' OR v.model LIKE '$search_term' OR v.description LIKE '$search_term')
    AND v.status = 'available'
    LIMIT 8
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $image = !empty($row['image_path']) ? SITE_URL . '/' . $row['image_path'] : '';
        $suggestions['vehicles'][] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $row['slug'],
            'price' => formatCurrency($row['price']) . '/day',
            'category' => $row['category_name'] ?? 'Vehicle',
            'vehicle_type' => ucfirst(str_replace('_', ' ', $row['vehicle_type'] ?? '')),
            'image' => $image,
            'type' => 'vehicle',
            'icon' => 'car',
            'url' => SITE_URL . '/vehicle-detail.php?id=' . $row['id']
        ];
    }
}

// Get trending searches (most searched terms)
$trending = [];

// Get popular categories
$categories = [];
$result = $conn->query("
    SELECT name, slug, type FROM categories 
    WHERE status = 'active' AND name LIKE '$search_term'
    LIMIT 6
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $url = '';
        if ($row['type'] === 'ecommerce') {
            $url = SITE_URL . '/products.php?category=' . $row['slug'];
        } elseif ($row['type'] === 'realestate') {
            $url = SITE_URL . '/properties.php?category=' . $row['slug'];
        } elseif ($row['type'] === 'carrental') {
            $url = SITE_URL . '/vehicles.php?category=' . $row['slug'];
        }
        $categories[] = [
            'name' => $row['name'],
            'slug' => $row['slug'],
            'type' => $row['type'],
            'url' => $url
        ];
    }
}

echo json_encode([
    'success' => true,
    'suggestions' => $suggestions,
    'trending' => $trending,
    'categories' => $categories
]);
