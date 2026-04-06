<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$customer_id = currentCustomerId();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get wishlist items
$wishlist_items = [];
$wishlist_query = "SELECT w.*, 
    CASE 
        WHEN w.item_type = 'product' THEN p.name
        WHEN w.item_type = 'property' THEN pr.title
        WHEN w.item_type = 'vehicle' THEN CONCAT(v.brand, ' ', v.model)
    END as item_name,
    CASE 
        WHEN w.item_type = 'product' THEN p.price
        WHEN w.item_type = 'property' THEN pr.price
        WHEN w.item_type = 'vehicle' THEN v.daily_rate
    END as item_price,
    CASE 
        WHEN w.item_type = 'product' THEN COALESCE(
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1),
            (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1)
        )
        WHEN w.item_type = 'property' THEN COALESCE(
            (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id AND pri.is_primary = 1 LIMIT 1),
            (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id ORDER BY pri.id ASC LIMIT 1)
        )
        WHEN w.item_type = 'vehicle' THEN COALESCE(
            (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id AND vi.is_primary = 1 LIMIT 1),
            (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY vi.id ASC LIMIT 1)
        )
    END as item_image
    FROM wishlist w
    LEFT JOIN products p ON w.item_type = 'product' AND w.item_id = p.id
    LEFT JOIN properties pr ON w.item_type = 'property' AND w.item_id = pr.id
    LEFT JOIN vehicles v ON w.item_type = 'vehicle' AND w.item_id = v.id
    WHERE w.user_id = {$customer['id']} 
    ORDER BY w.created_at DESC LIMIT 8";
$stmt = $conn->query($wishlist_query);
if ($stmt && $stmt->num_rows > 0) {
    while ($row = $stmt->fetch_assoc()) {
        $wishlist_items[] = $row;
    }
}

// Get recently viewed items
$recent_items = [];
$seen_items = []; // Track unique items

// Get recently viewed products
$recent_products_query = "SELECT DISTINCT 'product' as type, p.id, p.name as title, p.price, p.slug,
    COALESCE(
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1),
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1)
    ) as image,
    MAX(al.created_at) as viewed_at
    FROM activity_logs al
    INNER JOIN products p ON p.id = CAST(SUBSTRING_INDEX(al.description, 'ID: ', -1) AS UNSIGNED)
    WHERE al.user_id = {$customer['id']} 
    AND al.action = 'VIEW_PRODUCT'
    AND p.status = 'active'
    GROUP BY p.id
    ORDER BY viewed_at DESC 
    LIMIT 10";
$result = $conn->query($recent_products_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $key = 'product_' . $row['id'];
        if (!isset($seen_items[$key])) {
            $recent_items[] = $row;
            $seen_items[$key] = true;
        }
    }
}

// Get recently viewed properties
$recent_properties_query = "SELECT DISTINCT 'property' as type, pr.id, pr.title, pr.price, pr.slug,
    COALESCE(
        (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id AND pri.is_primary = 1 LIMIT 1),
        (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id ORDER BY pri.id ASC LIMIT 1)
    ) as image,
    MAX(al.created_at) as viewed_at
    FROM activity_logs al
    INNER JOIN properties pr ON pr.id = CAST(SUBSTRING_INDEX(al.description, 'ID: ', -1) AS UNSIGNED)
    WHERE al.user_id = {$customer['id']} 
    AND al.action = 'VIEW_PROPERTY'
    AND pr.status = 'available'
    GROUP BY pr.id
    ORDER BY viewed_at DESC 
    LIMIT 10";
$result = $conn->query($recent_properties_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $key = 'property_' . $row['id'];
        if (!isset($seen_items[$key])) {
            $recent_items[] = $row;
            $seen_items[$key] = true;
        }
    }
}

// Get recently viewed vehicles
$recent_vehicles_query = "SELECT DISTINCT 'vehicle' as type, v.id, CONCAT(v.brand, ' ', v.model, ' ', v.year) as title, 
    v.daily_rate as price, v.slug,
    COALESCE(
        (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id AND vi.is_primary = 1 LIMIT 1),
        (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY vi.id ASC LIMIT 1)
    ) as image,
    MAX(al.created_at) as viewed_at
    FROM activity_logs al
    INNER JOIN vehicles v ON v.id = CAST(SUBSTRING_INDEX(al.description, 'ID: ', -1) AS UNSIGNED)
    WHERE al.user_id = {$customer['id']} 
    AND al.action = 'VIEW_VEHICLE'
    AND v.status = 'available'
    GROUP BY v.id
    ORDER BY viewed_at DESC 
    LIMIT 10";
$result = $conn->query($recent_vehicles_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $key = 'vehicle_' . $row['id'];
        if (!isset($seen_items[$key])) {
            $recent_items[] = $row;
            $seen_items[$key] = true;
        }
    }
}

// Sort by viewed_at (most recent first)
usort($recent_items, function($a, $b) {
    return strtotime($b['viewed_at']) - strtotime($a['viewed_at']);
});
$recent_items = array_slice($recent_items, 0, 8);

// Get recommended items (Zuba For You)
$recommended_items = [];

// Get products with images
$products_query = "SELECT 'product' as type, p.id, p.name as title, p.price, p.slug,
    COALESCE(
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1),
        (SELECT pi.image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1)
    ) as image
    FROM products p 
    WHERE p.status = 'active' 
    ORDER BY RAND() 
    LIMIT 4";
$products = $conn->query($products_query);
if ($products && $products->num_rows > 0) {
    while ($row = $products->fetch_assoc()) {
        $recommended_items[] = $row;
    }
}

// Get properties with images
$properties_query = "SELECT 'property' as type, pr.id, pr.title, pr.price, pr.slug,
    COALESCE(
        (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id AND pri.is_primary = 1 LIMIT 1),
        (SELECT pri.image_path FROM property_images pri WHERE pri.property_id = pr.id ORDER BY pri.id ASC LIMIT 1)
    ) as image
    FROM properties pr 
    WHERE pr.status = 'available' 
    ORDER BY RAND() 
    LIMIT 4";
$properties = $conn->query($properties_query);
if ($properties && $properties->num_rows > 0) {
    while ($row = $properties->fetch_assoc()) {
        $recommended_items[] = $row;
    }
}

// Get vehicles with images
$vehicles_query = "SELECT 'vehicle' as type, v.id, CONCAT(v.brand, ' ', v.model, ' ', v.year) as title, 
    v.daily_rate as price, v.slug,
    COALESCE(
        (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id AND vi.is_primary = 1 LIMIT 1),
        (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = v.id ORDER BY vi.id ASC LIMIT 1)
    ) as image
    FROM vehicles v 
    WHERE v.status = 'available' 
    ORDER BY RAND() 
    LIMIT 4";
$vehicles = $conn->query($vehicles_query);
if ($vehicles && $vehicles->num_rows > 0) {
    while ($row = $vehicles->fetch_assoc()) {
        $recommended_items[] = $row;
    }
}

// Shuffle recommendations
shuffle($recommended_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        /* Clean Header like Amazon/Jumia */
        .header {
            background: white;
            border-bottom: 1px solid #e5e5e5;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #f5f5f5;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: #e5e5e5;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f97316;
        }
        
        .user-details h2 {
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }
        
        .user-details p {
            font-size: 13px;
            color: #666;
        }
        
        .header-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .settings-btn {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border: none;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .settings-btn:hover {
            background: #f97316;
            color: white;
        }
        
        .logout-btn {
            padding: 8px 20px;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }
        
        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Profile Banner */
        .profile-banner {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .banner-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .banner-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }
        
        .banner-info h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .banner-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            opacity: 0.95;
        }
        
        .banner-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .banner-stats {
            display: flex;
            gap: 30px;
        }
        
        .stat-box {
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-box:hover {
            transform: scale(1.05);
        }
        
        .order-selector-box {
            text-align: center;
            background: rgba(255,255,255,0.15);
            padding: 15px;
            border-radius: 12px;
            min-width: 150px;
            transition: all 0.3s;
        }
        
        .order-selector-box:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .order-type-select {
            width: 100%;
            padding: 8px 10px;
            background: white;
            color: #1a1a2e;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .order-type-select:hover {
            background: #f9fafb;
        }
        
        .order-type-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: 800;
            display: block;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* Navigation Tabs */
        .nav-tabs {
            background: white;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .nav-tab {
            padding: 12px 24px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
        }
        
        .nav-tab:hover {
            background: #f5f5f5;
            color: #333;
        }
        
        .nav-tab.active {
            background: #f97316;
            color: white;
        }
        
        .nav-tab i {
            font-size: 16px;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
        }
        
        .card-title i {
            color: #f97316;
        }
        
        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            transform: translateY(-4px);
        }
        
        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background: #f5f5f5;
        }
        
        .product-info {
            padding: 16px;
        }
        
        .product-name {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #f97316;
            margin-bottom: 8px;
        }
        
        .product-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #f97316;
            color: white;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e5e5;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #f97316;
        }
        
        .btn-primary {
            padding: 12px 32px;
            background: #f97316;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #ea580c;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state i {
            font-size: 80px;
            color: #e5e5e5;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .empty-state p {
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                padding: 12px 15px;
            }
            
            .header-left {
                flex-direction: row;
                gap: 12px;
            }
            
            .back-btn {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .back-btn span {
                display: none;
            }
            
            .user-avatar {
                width: 38px;
                height: 38px;
            }
            
            .user-details h2 {
                font-size: 14px;
            }
            
            .user-details p {
                font-size: 12px;
            }
            
            .logout-btn {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .container {
                padding: 15px;
            }
            
            .profile-banner {
                flex-direction: column;
                padding: 20px;
                gap: 20px;
            }
            
            .banner-left {
                flex-direction: column;
                text-align: center;
                width: 100%;
            }
            
            .banner-avatar {
                width: 70px;
                height: 70px;
            }
            
            .banner-info h1 {
                font-size: 22px;
            }
            
            .banner-meta {
                flex-direction: column;
                gap: 8px;
                font-size: 13px;
                align-items: center;
            }
            
            .banner-stats {
                width: 100%;
                justify-content: center;
                gap: 12px;
                flex-wrap: wrap;
            }
            
            .stat-box {
                flex: 1;
                min-width: 80px;
            }
            
            .order-selector-box {
                flex: 1 1 100%;
                min-width: 100%;
                padding: 12px;
                margin-top: 10px;
            }
            
            .stat-number {
                font-size: 20px;
            }
            
            .stat-label {
                font-size: 11px;
            }
            
            .order-type-select {
                font-size: 11px;
                padding: 6px 8px;
                margin-top: 8px;
            }
            
            .nav-tabs {
                padding: 8px;
                gap: 6px;
                margin-bottom: 15px;
            }
            
            .nav-tab {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .card {
                padding: 16px;
                margin-bottom: 15px;
            }
            
            .card-title {
                font-size: 18px;
                margin-bottom: 16px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            
            .product-image {
                height: 160px;
            }
            
            .product-info {
                padding: 12px;
            }
            
            .product-name {
                font-size: 13px;
            }
            
            .product-price {
                font-size: 16px;
            }
            
            .product-badge {
                font-size: 10px;
                padding: 3px 8px;
            }
            
            .empty-state {
                padding: 50px 20px;
            }
            
            .empty-state i {
                font-size: 60px;
            }
            
            .empty-state h3 {
                font-size: 18px;
            }
            
            .empty-state p {
                font-size: 14px;
            }
            
            .btn-primary {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .product-image {
                height: 140px;
            }
            
            .product-info {
                padding: 10px;
            }
            
            .product-name {
                font-size: 12px;
                -webkit-line-clamp: 2;
            }
            
            .product-price {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<!-- Clean Header -->
<div class="header">
    <div class="header-content">
        <div class="header-left">
            <a href="<?= SITE_URL ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Shop</span>
            </a>
            <div class="user-info">
                <img src="<?= !empty($customer['profile_image']) ? SITE_URL . '/' . htmlspecialchars($customer['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($customer['name']) . '&size=45&background=f97316&color=fff&bold=true' ?>" 
                     alt="User" class="user-avatar">
                <div class="user-details">
                    <h2><?= htmlspecialchars($customer['name']) ?></h2>
                    <p><?= htmlspecialchars($customer['email']) ?></p>
                </div>
            </div>
        </div>
        <div class="header-right">
            <a href="<?= SITE_URL ?>/edit-profile.php" class="settings-btn" title="Edit Profile">
                <i class="fas fa-cog"></i>
            </a>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Main Container -->
<div class="container">
    <!-- Profile Banner -->
    <div class="profile-banner">
        <div class="banner-left">
            <img src="<?= !empty($customer['profile_image']) ? SITE_URL . '/' . htmlspecialchars($customer['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($customer['name']) . '&size=80&background=fff&color=f97316&bold=true' ?>" 
                 alt="Profile" class="banner-avatar">
            <div class="banner-info">
                <h1><?= htmlspecialchars($customer['name']) ?></h1>
                <div class="banner-meta">
                    <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?></span>
                    <span><i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?></span>
                    <?php if (!empty($customer['city'])): ?>
                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($customer['city']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="banner-stats">
            <div class="order-selector-box">
                <span class="stat-number"><?php
                    $product_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = " . $customer['id']);
                    $property_orders = $conn->query("SELECT COUNT(*) as count FROM property_orders WHERE user_id = " . $customer['id']);
                    $total_orders = ($product_orders ? $product_orders->fetch_assoc()['count'] : 0) + ($property_orders ? $property_orders->fetch_assoc()['count'] : 0);
                    echo $total_orders;
                ?></span>
                <span class="stat-label">Total Orders</span>
                <select id="orderTypeDropdown" class="order-type-select" onchange="navigateToOrders()">
                    <option value="">View Orders</option>
                    <option value="product">Product Orders</option>
                    <option value="property">Property Orders</option>
                </select>
            </div>
            <a href="wishlist.php" style="text-decoration: none; color: inherit;">
                <div class="stat-box">
                    <span class="stat-number"><?= count($wishlist_items) ?></span>
                    <span class="stat-label">Wishlist</span>
                </div>
            </a>
            <a href="my-reviews.php" style="text-decoration: none; color: inherit;">
                <div class="stat-box">
                    <span class="stat-number"><?php
                        $result = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE user_id = " . $customer['id']);
                        echo $result ? $result->fetch_assoc()['count'] : 0;
                    ?></span>
                    <span class="stat-label">Reviews</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-tabs">
        <button class="nav-tab active" onclick="showTab('for-you')">
            <i class="fas fa-star"></i>
            <span>Zuba For You</span>
        </button>
        <button class="nav-tab" onclick="showTab('wishlist')">
            <i class="fas fa-heart"></i>
            <span>Wishlist</span>
        </button>
        <button class="nav-tab" onclick="showTab('recent')">
            <i class="fas fa-clock"></i>
            <span>Recently Viewed</span>
        </button>
    </div>

    <!-- Zuba For You Tab -->
    <div id="for-you" class="tab-content active">
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-star"></i>
                Zuba For You
            </h2>
            <?php if (count($recommended_items) > 0): ?>
                <div class="grid">
                    <?php foreach ($recommended_items as $item): ?>
                        <a href="<?php 
                            if ($item['type'] === 'product') {
                                echo SITE_URL . '/product-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'property') {
                                echo SITE_URL . '/property-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'vehicle') {
                                echo SITE_URL . '/vehicle-detail.php?id=' . $item['id'];
                            }
                        ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-card">
                                <img src="<?php 
                                    if (!empty($item['image'])) {
                                        if ($item['type'] === 'product') {
                                            echo UPLOAD_URL . 'products/' . htmlspecialchars($item['image']);
                                        } elseif ($item['type'] === 'property') {
                                            echo UPLOAD_URL . 'properties/' . htmlspecialchars($item['image']);
                                        } elseif ($item['type'] === 'vehicle') {
                                            echo SITE_URL . '/' . htmlspecialchars($item['image']);
                                        }
                                    } else {
                                        echo 'https://via.placeholder.com/240x220?text=No+Image';
                                    }
                                ?>" 
                                     alt="<?= htmlspecialchars($item['title']) ?>" class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($item['title']) ?></div>
                                    <div class="product-price">RWF <?= number_format($item['price']) ?></div>
                                    <span class="product-badge"><?= htmlspecialchars($item['type']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>No recommendations yet</h3>
                    <p>Start browsing to get personalized recommendations</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Wishlist Tab -->
    <div id="wishlist" class="tab-content">
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-heart"></i>
                My Wishlist
            </h2>
            <?php if (count($wishlist_items) > 0): ?>
                <div class="grid">
                    <?php foreach ($wishlist_items as $item): ?>
                        <a href="<?php 
                            if ($item['item_type'] === 'product') {
                                echo SITE_URL . '/product-detail.php?id=' . $item['item_id'];
                            } elseif ($item['item_type'] === 'property') {
                                echo SITE_URL . '/property-detail.php?id=' . $item['item_id'];
                            } elseif ($item['item_type'] === 'vehicle') {
                                echo SITE_URL . '/vehicle-detail.php?id=' . $item['item_id'];
                            }
                        ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-card">
                                <img src="<?php 
                                    if (!empty($item['item_image'])) {
                                        if ($item['item_type'] === 'product') {
                                            echo UPLOAD_URL . 'products/' . htmlspecialchars($item['item_image']);
                                        } elseif ($item['item_type'] === 'property') {
                                            echo UPLOAD_URL . 'properties/' . htmlspecialchars($item['item_image']);
                                        } elseif ($item['item_type'] === 'vehicle') {
                                            echo SITE_URL . '/' . htmlspecialchars($item['item_image']);
                                        }
                                    } else {
                                        echo 'https://via.placeholder.com/240x220?text=No+Image';
                                    }
                                ?>" 
                                     alt="<?= htmlspecialchars($item['item_name']) ?>" class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($item['item_name']) ?></div>
                                    <div class="product-price">RWF <?= number_format($item['item_price']) ?></div>
                                    <span class="product-badge"><?= htmlspecialchars($item['item_type']) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-heart-broken"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save items you love to buy them later</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recently Viewed Tab -->
    <div id="recent" class="tab-content">
        <div class="card">
            <h2 class="card-title">
                <i class="fas fa-clock"></i>
                Recently Viewed
            </h2>
            <?php if (count($recent_items) > 0): ?>
                <div class="grid">
                    <?php foreach ($recent_items as $item): ?>
                        <a href="<?php 
                            if ($item['type'] === 'product') {
                                echo SITE_URL . '/product-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'property') {
                                echo SITE_URL . '/property-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'vehicle') {
                                echo SITE_URL . '/vehicle-detail.php?id=' . $item['id'];
                            }
                        ?>" style="text-decoration: none; color: inherit;">
                            <div class="product-card">
                                <img src="<?php 
                                    if (!empty($item['image'])) {
                                        if ($item['type'] === 'product') {
                                            echo UPLOAD_URL . 'products/' . htmlspecialchars($item['image']);
                                        } elseif ($item['type'] === 'property') {
                                            echo UPLOAD_URL . 'properties/' . htmlspecialchars($item['image']);
                                        } elseif ($item['type'] === 'vehicle') {
                                            echo SITE_URL . '/' . htmlspecialchars($item['image']);
                                        }
                                    } else {
                                        echo 'https://via.placeholder.com/240x220?text=No+Image';
                                    }
                                ?>" 
                                     alt="<?= htmlspecialchars($item['title']) ?>" class="product-image">
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($item['title']) ?></div>
                                    <div class="product-price">RWF <?= number_format($item['price']) ?></div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span class="product-badge"><?= htmlspecialchars($item['type']) ?></span>
                                        <small style="color: #999; font-size: 11px;">
                                            <i class="fas fa-clock"></i> <?= date('M j', strtotime($item['viewed_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-eye-slash"></i>
                    <h3>No recently viewed items</h3>
                    <p>Items you view will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.nav-tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

function navigateToOrders() {
    const orderType = document.getElementById('orderTypeDropdown').value;
    
    if (orderType === 'product') {
        window.location.href = '<?= SITE_URL ?>/my-orders.php';
    } else if (orderType === 'property') {
        window.location.href = '<?= SITE_URL ?>/property-orders.php';
    }
    
    // Reset dropdown after navigation starts
    setTimeout(() => {
        document.getElementById('orderTypeDropdown').value = '';
    }, 100);
}
</script>

</body>
</html>
