<?php
/**
 * PROPERTY DETAIL PAGE
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id <= 0) {
    header('Location: ' . SITE_URL . '/properties.php');
    exit;
}

// Check if user is logged in
$is_logged_in = isCustomerLoggedIn();
$customer = currentCustomer();

// Fetch property details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM properties p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    header('Location: ' . SITE_URL . '/properties.php');
    exit;
}

// Update views
$stmt = $conn->prepare("UPDATE properties SET views = views + 1 WHERE id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$stmt->close();

// Log activity if user is logged in
if ($is_logged_in) {
    logActivity($conn, 'customer', currentCustomerId(), 'VIEW_PROPERTY', 'Viewed property: ' . $property['title'] . ' (ID: ' . $property_id . ')');
}

// Fetch images
$images = [];
$stmt = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

// Fetch related properties
$related = [];
$stmt = $conn->prepare("
    SELECT p.*, 
    COALESCE(
        (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1),
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY sort_order ASC LIMIT 1)
    ) as image_path
    FROM properties p 
    WHERE p.category_id = ? AND p.id != ? 
    ORDER BY p.featured DESC, p.created_at DESC 
    LIMIT 8
");
$stmt->bind_param('ii', $property['category_id'], $property_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $related[] = $row;
}
$stmt->close();

// Check if property is in user's wishlist
$in_wishlist = false;
if ($is_logged_in) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_type = 'property' AND item_id = ?");
    $stmt->bind_param('ii', $user_id, $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $in_wishlist = $result->num_rows > 0;
    $stmt->close();
}

// Get wishlist count
$wishlist_count = 0;
if ($is_logged_in) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $wishlist_count = $row['count'];
    $stmt->close();
}

$features = $property['features'] ? json_decode($property['features'], true) : [];
$page_title = $property['title'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($property['title']) ?> | Zuba Online Market</title>
    <meta name="description" content="<?= e(substr($property['description'] ?? '', 0, 160)) ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Popup Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/popup.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
        .container { max-width: 1400px; margin: 0 auto; padding: 0 16px; }
        
        /* Property Detail Header */
        .property-header-bar { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .property-header-content { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; gap: 16px; max-width: 1400px; margin: 0 auto; }
        
        .header-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; flex-shrink: 0; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; transform: scale(1.05); }
        
        .property-title-header { flex: 1; min-width: 0; }
        .property-title-header h1 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .property-title-header .property-category { font-size: 12px; color: #6b7280; margin-top: 2px; }
        
        .header-actions { display: flex; align-items: center; gap: 8px; }
        .header-icon-btn { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; position: relative; text-decoration: none; }
        .header-icon-btn:hover { background: #fff5f0; border-color: #f97316; color: #f97316; transform: scale(1.05); }
        .header-icon-btn.active { background: #f97316; color: #fff; border-color: #f97316; }
        
        .wishlist-badge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #fff; }

        /* Gallery */
        .gallery { position: relative; background: #000; height: 100vh; max-height: 600px; }
        .main-image { width: 100%; height: 100%; object-fit: contain; }
        .no-image { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #6b7280; background: #f9fafb; }
        .no-image i { font-size: 80px; }
        .no-image p { font-size: 16px; font-weight: 600; }

        .wishlist-btn { position: absolute; top: 16px; right: 16px; width: 48px; height: 48px; border-radius: 12px; border: none; background: rgba(255,255,255,0.95); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #6b7280; box-shadow: 0 2px 8px rgba(0,0,0,0.15); transition: all .3s; z-index: 10; }
        .wishlist-btn:hover { transform: scale(1.05); background: #fff; }
        .wishlist-btn.active { background: #f97316; color: #fff; }

        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 16px;
            pointer-events: none;
        }

        .gallery-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.9);
            cursor: pointer;
            font-size: 18px;
            color: #333;
            pointer-events: all;
        }

        .gallery-counter {
            position: absolute;
            bottom: 16px;
            right: 16px;
            background: rgba(0,0,0,0.7);
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        .thumbnails {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            overflow-x: auto;
            background: #fff;
            -webkit-overflow-scrolling: touch;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            border: 3px solid transparent;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .thumbnail.active {
            border-color: #ff6b35;
            transform: scale(1.05);
        }

        .content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 16px;
            padding-bottom: 100px;
        }

        .property-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .property-type { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(249,115,22,0.3); }
        .property-type.rent { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 2px 8px rgba(16,185,129,0.3); }

        .property-title {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .property-location {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .property-location i {
            color: #f97316;
            flex-shrink: 0;
        }

        .property-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .meta-item { display: flex; align-items: center; gap: 6px; background: #f9fafb; padding: 8px 12px; border-radius: 10px; font-size: 13px; color: #6b7280; border: 1px solid #e5e7eb; white-space: nowrap; }
        .meta-item i { color: #f97316; font-size: 14px; }

        .price-card { background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); padding: 20px; border-radius: 14px; margin-bottom: 16px; border: 1px solid rgba(249,115,22,0.2); }
        .price-label { font-size: 14px; color: #6b7280; margin-bottom: 8px; font-weight: 600; }
        .price-main { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .price { font-size: 32px; font-weight: 900; color: #f97316; line-height: 1; }
        .rent-period { font-size: 18px; color: #6b7280; font-weight: 600; }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        .btn { flex: 1; padding: 16px 20px; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-secondary { background: #fff; color: #f97316; border: 2px solid #f97316; }
        .btn-secondary:hover { background: #fff5f0; }

        .section {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #f97316;
            font-size: 20px;
        }

        .description {
            color: #4b5563;
            line-height: 1.7;
            font-size: 15px;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .spec-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            background: #f9fafb;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .spec-label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }

        .spec-value {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #4b5563;
            font-size: 14px;
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .feature-item i {
            color: #10b981;
            font-size: 16px;
            flex-shrink: 0;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
        }

        .form-input {
            padding: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all .3s;
            background: #fff;
        }

        .form-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249,115,22,0.1);
        }

        .price-summary {
            background: #f9fafb;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            border: 1px solid #e5e7eb;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            color: #1a1a2e;
            font-weight: 600;
        }

        .summary-amount {
            font-size: 24px;
            font-weight: 900;
            color: #f97316;
        }

        .login-required {
            text-align: center;
            padding: 40px 20px;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%);
            color: #f97316;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
            border: 2px solid rgba(249,115,22,0.2);
        }

        .login-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 12px;
        }

        .login-message {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .login-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .login-buttons .btn {
            text-decoration: none;
            min-width: 140px;
            flex: 1;
            max-width: 200px;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .related-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: all .3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(249,115,22,0.2);
            border-color: #f97316;
        }

        .related-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            background: #f9fafb;
        }

        .related-info {
            padding: 12px;
        }

        .related-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
            line-height: 1.4;
        }

        .related-price {
            font-size: 16px;
            font-weight: 700;
            color: #f97316;
        }

        .sticky-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 12px 16px;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
            display: flex;
            gap: 12px;
            z-index: 100;
            border-top: 1px solid #e5e7eb;
        }

        .sticky-footer .btn {
            margin: 0;
        }

        .sticky-footer .btn-primary {
            flex: 2;
        }

        .sticky-footer .btn-secondary {
            flex: 0 0 auto;
            min-width: 56px;
            padding: 16px;
        }

        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s;
            max-width: 90%;
            width: 400px;
        }

        .popup.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
            pointer-events: all;
        }

        .popup-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #4CAF50;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px;
        }

        .popup-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 8px;
        }

        .popup-message {
            color: #666;
            text-align: center;
            margin-bottom: 20px;
        }

        .popup-btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #ff6b35;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .overlay.show {
            opacity: 1;
            pointer-events: all;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .gallery { max-height: 400px; }
            .content { padding: 12px; padding-bottom: 100px; }
            .property-header, .price-card, .section { padding: 16px; border-radius: 12px; margin-bottom: 12px; }
            .property-title { font-size: 20px; margin-bottom: 10px; }
            .property-location { font-size: 13px; margin-bottom: 14px; }
            .property-meta { gap: 6px; }
            .meta-item { padding: 6px 10px; font-size: 12px; }
            .meta-item i { font-size: 12px; }
            .price { font-size: 28px; }
            .rent-period { font-size: 16px; }
            .section-title { font-size: 16px; margin-bottom: 14px; }
            .section-title i { font-size: 18px; }
            .description { font-size: 14px; line-height: 1.6; }
            .specs-grid { grid-template-columns: 1fr; gap: 10px; }
            .spec-item { padding: 12px; }
            .spec-label { font-size: 11px; }
            .spec-value { font-size: 14px; }
            .features-list { grid-template-columns: 1fr; gap: 10px; }
            .feature-item { padding: 10px; font-size: 13px; }
            .feature-item i { font-size: 14px; }
            .form-group { gap: 6px; }
            .form-input { padding: 12px; font-size: 14px; }
            .price-summary { padding: 14px; margin-bottom: 14px; }
            .summary-row { font-size: 15px; }
            .summary-amount { font-size: 22px; }
            .login-icon { width: 70px; height: 70px; font-size: 32px; margin-bottom: 16px; }
            .login-title { font-size: 20px; }
            .login-message { font-size: 14px; }
            .login-buttons { flex-direction: column; }
            .login-buttons .btn { max-width: 100%; }
            .related-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .related-image { height: 120px; }
            .related-info { padding: 10px; }
            .related-title { font-size: 13px; min-height: 36px; }
            .related-price { font-size: 15px; }
            .sticky-footer { padding: 10px 16px; }
            .sticky-footer .btn { font-size: 14px; padding: 14px 16px; }
            .sticky-footer .btn-secondary { min-width: 52px; padding: 14px; }
        }
        
        @media (max-width: 640px) {
            .property-header-content { padding: 12px; }
            .property-title-header h1 { font-size: 14px; }
            .property-title-header .property-category { font-size: 11px; }
            .btn-back, .header-icon-btn { width: 36px; height: 36px; font-size: 16px; }
            .gallery { max-height: 300px; }
            .thumbnails { padding: 10px 12px; gap: 6px; }
            .thumbnail { width: 60px; height: 60px; min-width: 60px; }
            .property-title { font-size: 18px; }
            .price { font-size: 24px; }
            .rent-period { font-size: 14px; }
            .action-buttons { gap: 10px; }
        }
    </style>
</head>
<body>

<!-- Property Detail Header -->
<header class="property-header-bar">
    <div class="property-header-content">
        <div class="header-left">
            <a href="javascript:history.back()" class="btn-back" title="Go Back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="property-title-header">
                <h1><?= e($property['title']) ?></h1>
                <div class="property-category"><?= e($property['category_name']) ?></div>
            </div>
        </div>
        
        <div class="header-actions">
            <button class="header-icon-btn" onclick="shareProperty()" title="Share">
                <i class="fas fa-share-alt"></i>
            </button>
            <a href="<?= SITE_URL ?>/wishlist.php" class="header-icon-btn" title="Wishlist">
                <i class="far fa-heart"></i>
                <?php if ($wishlist_count > 0): ?>
                    <span class="wishlist-badge"><?= $wishlist_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

    <div class="gallery">
        <?php if (!empty($images)): ?>
            <img src="<?= UPLOAD_URL . 'properties/' . $images[0]['image_path'] ?>" alt="<?= e($property['title']) ?>" class="main-image" id="mainImage">
        <?php else: ?>
            <div class="no-image">
                <i class="fas fa-building"></i>
                <p>No Image Available</p>
            </div>
        <?php endif; ?>
        
        <button class="wishlist-btn <?= $in_wishlist ? 'active' : '' ?>" onclick="toggleWishlistBtn()">
            <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
        </button>
        
        <?php if (count($images) > 1): ?>
        <div class="gallery-nav">
            <button class="gallery-btn" onclick="prevImage()" id="prevBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="gallery-btn" onclick="nextImage()" id="nextBtn">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="gallery-counter">
            <span id="currentImage">1</span> / <span id="totalImages"><?= count($images) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if (count($images) > 1): ?>
    <div class="thumbnails">
        <?php foreach ($images as $index => $image): ?>
        <img src="<?= UPLOAD_URL . 'properties/' . $image['image_path'] ?>" alt="<?= e($property['title']) ?>" class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="showImage(<?= $index ?>)">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="content">
        <div class="property-header">
            <span class="property-type <?= $property['listing_type'] === 'rent' ? 'rent' : '' ?>">
                <i class="fas fa-<?= $property['listing_type'] === 'rent' ? 'key' : 'tag' ?>"></i>
                For <?= ucfirst($property['listing_type']) ?>
            </span>
            <h1 class="property-title"><?= e($property['title']) ?></h1>
            <div class="property-location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= e($property['address'] . ', ' . $property['city'] . ', ' . $property['country']) ?></span>
            </div>
            <div class="property-meta">
                <?php if ($property['bedrooms']): ?>
                <div class="meta-item">
                    <i class="fas fa-bed"></i>
                    <span><?= $property['bedrooms'] ?> Beds</span>
                </div>
                <?php endif; ?>
                <?php if ($property['bathrooms']): ?>
                <div class="meta-item">
                    <i class="fas fa-bath"></i>
                    <span><?= $property['bathrooms'] ?> Baths</span>
                </div>
                <?php endif; ?>
                <?php if ($property['area']): ?>
                <div class="meta-item">
                    <i class="fas fa-ruler-combined"></i>
                    <span><?= number_format($property['area']) ?> <?= strtoupper($property['area_unit'] ?? 'sqm') ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property['parking_spaces']): ?>
                <div class="meta-item">
                    <i class="fas fa-car"></i>
                    <span><?= $property['parking_spaces'] ?> Parking</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="price-card">
            <div class="price-label">Price</div>
            <div class="price-main">
                <div class="price"><?= formatCurrency($property['price']) ?></div>
                <?php if ($property['listing_type'] === 'rent' && $property['rent_period']): ?>
                <span class="rent-period">/ <?= ucfirst($property['rent_period']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-info-circle"></i> Description</h2>
            <div class="description"><?= nl2br(e($property['description'])) ?></div>
        </div>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-list-ul"></i> Property Details</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-label">Property Type</span>
                    <span class="spec-value"><?= ucfirst($property['property_type']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Listing Type</span>
                    <span class="spec-value">For <?= ucfirst($property['listing_type']) ?></span>
                </div>
                <?php if ($property['bedrooms']): ?>
                <div class="spec-item">
                    <span class="spec-label">Bedrooms</span>
                    <span class="spec-value"><?= $property['bedrooms'] ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property['bathrooms']): ?>
                <div class="spec-item">
                    <span class="spec-label">Bathrooms</span>
                    <span class="spec-value"><?= $property['bathrooms'] ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property['area']): ?>
                <div class="spec-item">
                    <span class="spec-label">Area</span>
                    <span class="spec-value"><?= number_format($property['area']) ?> <?= strtoupper($property['area_unit'] ?? 'sqm') ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property['year_built']): ?>
                <div class="spec-item">
                    <span class="spec-label">Year Built</span>
                    <span class="spec-value"><?= $property['year_built'] ?></span>
                </div>
                <?php endif; ?>
                <?php if ($property['parking_spaces']): ?>
                <div class="spec-item">
                    <span class="spec-label">Parking Spaces</span>
                    <span class="spec-value"><?= $property['parking_spaces'] ?></span>
                </div>
                <?php endif; ?>
                <div class="spec-item">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><?= ucfirst($property['status']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Views</span>
                    <span class="spec-value"><?= number_format($property['views']) ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($features)): ?>
        <div class="section">
            <h2 class="section-title"><i class="fas fa-star"></i> Features & Amenities</h2>
            <div class="features-list">
                <?php foreach ($features as $feature): ?>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span><?= e($feature) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Place Order</h2>
            <?php if (!$is_logged_in): ?>
            <div class="login-required">
                <div class="login-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="login-title">Login Required</h3>
                <p class="login-message">You need to login or register to place an order for this property.</p>
                <div class="login-buttons">
                    <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="<?= SITE_URL ?>/register.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                </div>
            </div>
            <?php else: ?>
            <form class="contact-form" onsubmit="placeOrder(event)">
                <?php if ($property['listing_type'] === 'rent'): ?>
                <div class="form-group">
                    <label class="form-label">Rent Duration</label>
                    <input type="number" class="form-input" id="rentDuration" placeholder="Enter duration" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Rent Period</label>
                    <select class="form-input" id="rentPeriod" required>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected>Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="form-label">Customer Note (Optional)</label>
                    <textarea class="form-input" id="customerNote" rows="4" placeholder="Any special requests or questions..."></textarea>
                </div>
                <div class="price-summary">
                    <div class="summary-row">
                        <span>Amount:</span>
                        <span class="summary-amount" id="orderAmount"><?= formatCurrency($property['price']) ?></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                    Proceed to Payment
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($related)): ?>
        <div class="section">
            <h2 class="section-title"><i class="fas fa-th-large"></i> You May Also Like</h2>
            <div class="related-grid">
                <?php foreach ($related as $rel): ?>
                <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $rel['id'] ?>" class="related-card">
                    <?php if ($rel['image_path']): ?>
                        <img src="<?= UPLOAD_URL . 'properties/' . $rel['image_path'] ?>" alt="<?= e($rel['title']) ?>" class="related-image">
                    <?php else: ?>
                        <div class="related-image" style="background: #f9fafb; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building" style="font-size: 40px; color: #d1d5db;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="related-info">
                        <div class="related-title"><?= e($rel['title']) ?></div>
                        <div class="related-price"><?= formatCurrency($rel['price']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="sticky-footer">
        <?php if ($is_logged_in): ?>
        <button class="btn btn-primary" style="flex: 2;" onclick="scrollToOrder()">
            <i class="fas fa-shopping-cart"></i>
            Place Order
        </button>
        <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary" style="flex: 2;">
            <i class="fas fa-sign-in-alt"></i>
            Login to Order
        </a>
        <?php endif; ?>
        <button class="btn btn-secondary" style="flex: 1;" onclick="contactOwner()">
            <i class="fas fa-phone"></i>
        </button>
    </div>

    <div class="overlay" id="overlay" onclick="closePopup()"></div>
    <div class="popup" id="popup">
        <div class="popup-icon">
            <i class="fas fa-check"></i>
        </div>
        <h3 class="popup-title">Order Created!</h3>
        <p class="popup-message">Your order has been created. Redirecting to checkout...</p>
        <button class="popup-btn" onclick="closePopup()">OK</button>
    </div>

    <script>
        const images = <?= !empty($images) ? json_encode(array_map(function($img) { return UPLOAD_URL . 'properties/' . $img['image_path']; }, $images)) : '[]' ?>;
        let currentIndex = 0;

        function showImage(index) {
            if (images.length === 0) return;
            currentIndex = index;
            document.getElementById('mainImage').src = images[index];
            document.getElementById('currentImage').textContent = index + 1;
            
            document.querySelectorAll('.thumbnail').forEach((thumb, i) => {
                thumb.classList.toggle('active', i === index);
            });
        }

        function nextImage() {
            if (images.length === 0) return;
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        }

        function prevImage() {
            if (images.length === 0) return;
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        }

        function toggleWishlistBtn() {
            <?php if (!$is_logged_in): ?>
                showPopup({
                    type: 'warning',
                    icon: 'fa-user-lock',
                    title: 'Login Required',
                    message: 'Please login to add items to your wishlist.',
                    confirmText: 'Go to Login',
                    cancelText: 'Cancel',
                    showCancel: true,
                    onConfirm: () => {
                        window.location.href = '<?= SITE_URL ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
                    }
                });
                return;
            <?php endif; ?>
            
            const btn = document.querySelector('.wishlist-btn');
            toggleWishlist('property', <?= $property_id ?>, btn);
        }

        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= addslashes($property['title']) ?>',
                    url: window.location.href
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showPopup({
                        type: 'success',
                        icon: 'fa-check-circle',
                        title: 'Link Copied!',
                        message: 'Property link has been copied to clipboard.',
                        confirmText: 'OK'
                    });
                });
            }
        }

        function contactOwner() {
            showPopup({
                type: 'info',
                icon: 'fa-phone',
                title: 'Contact Information',
                message: 'Please call us at +250788000000 for more information about this property.',
                confirmText: 'OK'
            });
        }

        function scrollToOrder() {
            document.querySelector('.contact-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function placeOrder(e) {
            e.preventDefault();
            
            const formData = {
                property_id: <?= $property_id ?>,
                order_type: '<?= $property['listing_type'] === 'rent' ? 'rent' : 'purchase' ?>',
                amount: <?= $property['price'] ?>,
                customer_note: document.getElementById('customerNote').value
            };
            
            <?php if ($property['listing_type'] === 'rent'): ?>
                formData.rent_duration = document.getElementById('rentDuration').value;
                formData.rent_period = document.getElementById('rentPeriod').value;
            <?php endif; ?>
            
            // Redirect to checkout with query params
            let url = '<?= SITE_URL ?>/property-checkout.php?property_id=<?= $property_id ?>';
            <?php if ($property['listing_type'] === 'rent'): ?>
                url += '&rent_duration=' + document.getElementById('rentDuration').value;
                url += '&rent_period=' + document.getElementById('rentPeriod').value;
            <?php endif; ?>
            url += '&note=' + encodeURIComponent(document.getElementById('customerNote').value);
            window.location.href = url;
        }
    </script>
    <script src="<?= SITE_URL ?>/assets/js/popup.js"></script>

</body>
</html>
