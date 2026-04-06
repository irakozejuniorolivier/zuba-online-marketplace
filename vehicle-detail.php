<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($vehicle_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch vehicle details
$stmt = $conn->prepare("SELECT v.*, c.name as category_name FROM vehicles v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?");
$stmt->bind_param('i', $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    header('Location: index.php');
    exit;
}

// Update views
$conn->query("UPDATE vehicles SET views = views + 1 WHERE id = $vehicle_id");

// Log activity if user is logged in
if (isCustomerLoggedIn()) {
    logActivity($conn, 'customer', currentCustomerId(), 'VIEW_VEHICLE', 'Viewed vehicle: ' . $vehicle['brand'] . ' ' . $vehicle['model'] . ' (ID: ' . $vehicle_id . ')');
}

// Fetch images
$stmt = $conn->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, sort_order ASC");
$stmt->bind_param('i', $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = $row;
}
$stmt->close();

// Check if in wishlist
$in_wishlist = false;
if (isCustomerLoggedIn()) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_type = 'vehicle' AND item_id = ?");
    $stmt->bind_param('ii', $user_id, $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $in_wishlist = $result->num_rows > 0;
    $stmt->close();
}

// Get wishlist count
$wishlist_count = 0;
if (isCustomerLoggedIn()) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $wishlist_count = $row['count'];
    $stmt->close();
}

// Fetch related vehicles
$related = [];
$stmt = $conn->prepare("
    SELECT v.*, 
    COALESCE(
        (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1),
        (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id ORDER BY sort_order ASC LIMIT 1)
    ) as image_path
    FROM vehicles v 
    WHERE v.category_id = ? AND v.id != ? 
    ORDER BY v.featured DESC, v.created_at DESC 
    LIMIT 8
");
$stmt->bind_param('ii', $vehicle['category_id'], $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $related[] = $row;
}
$stmt->close();

$features = $vehicle['features'] ? json_decode($vehicle['features'], true) : [];
$is_logged_in = isCustomerLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?> | Zuba Online Market</title>
    <meta name="description" content="<?= htmlspecialchars(substr($vehicle['description'] ?? '', 0, 160)) ?>">
    
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
        
        /* Vehicle Detail Header */
        .vehicle-header-bar { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .vehicle-header-content { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; gap: 16px; max-width: 1400px; margin: 0 auto; }
        
        .header-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; flex-shrink: 0; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; transform: scale(1.05); }
        
        .vehicle-title-header { flex: 1; min-width: 0; }
        .vehicle-title-header h1 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .vehicle-title-header .vehicle-category { font-size: 12px; color: #6b7280; margin-top: 2px; }
        
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

        .gallery-nav { position: absolute; top: 50%; transform: translateY(-50%); width: 100%; display: flex; justify-content: space-between; padding: 0 16px; pointer-events: none; }
        .gallery-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: rgba(255,255,255,0.9); cursor: pointer; font-size: 18px; color: #333; pointer-events: all; transition: all .3s; }
        .gallery-btn:hover { background: #fff; transform: scale(1.1); }

        .gallery-counter { position: absolute; bottom: 16px; right: 16px; background: rgba(0,0,0,0.7); color: #fff; padding: 6px 12px; border-radius: 20px; font-size: 14px; }

        .thumbnails { display: flex; gap: 8px; padding: 12px 16px; overflow-x: auto; background: #fff; -webkit-overflow-scrolling: touch; }
        .thumbnail { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; cursor: pointer; border: 3px solid transparent; flex-shrink: 0; transition: all 0.2s; }
        .thumbnail.active { border-color: #f97316; transform: scale(1.05); }

        /* Content */
        .content { max-width: 1200px; margin: 0 auto; padding: 16px; padding-bottom: 100px; }
        .card { background: #fff; padding: 20px; border-radius: 12px; margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        
        .vehicle-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(249,115,22,0.3); }
        .vehicle-title { font-size: 24px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; line-height: 1.3; }
        .vehicle-meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .meta-item { display: flex; align-items: center; gap: 6px; background: #f9fafb; padding: 8px 12px; border-radius: 10px; font-size: 13px; color: #6b7280; border: 1px solid #e5e7eb; white-space: nowrap; }
        .meta-item i { color: #f97316; font-size: 14px; }

        .price-card { background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); padding: 20px; border-radius: 14px; margin-bottom: 16px; border: 1px solid rgba(249,115,22,0.2); }
        .price-label { font-size: 14px; color: #6b7280; margin-bottom: 8px; font-weight: 600; }
        .price { font-size: 32px; font-weight: 900; color: #f97316; line-height: 1; }
        .price-period { font-size: 18px; color: #6b7280; font-weight: 600; }

        .section-title { font-size: 18px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #f97316; font-size: 20px; }
        .description { color: #4b5563; line-height: 1.7; font-size: 15px; }

        .specs-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .spec-item { display: flex; flex-direction: column; gap: 6px; background: #f9fafb; padding: 14px; border-radius: 10px; border: 1px solid #e5e7eb; }
        .spec-label { font-size: 12px; color: #6b7280; font-weight: 600; }
        .spec-value { font-size: 15px; font-weight: 700; color: #1a1a2e; }

        .features-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .feature-item { display: flex; align-items: center; gap: 10px; color: #4b5563; font-size: 14px; background: #f9fafb; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .feature-item i { color: #10b981; font-size: 16px; flex-shrink: 0; }

        /* Booking Form */
        .form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px; }
        .form-label { display: block; font-size: 14px; font-weight: 700; color: #1a1a2e; }
        .form-input { width: 100%; padding: 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 15px; font-family: inherit; transition: all .3s; background: #fff; }
        .form-input:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }

        .btn { width: 100%; padding: 16px 20px; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-secondary { background: #fff; color: #f97316; border: 2px solid #f97316; }
        .btn-secondary:hover { background: #fff5f0; }

        .login-required { text-align: center; padding: 40px 20px; }
        .login-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); color: #f97316; display: flex; align-items: center; justify-content: center; font-size: 36px; margin: 0 auto 20px; border: 2px solid rgba(249,115,22,0.2); }
        .login-title { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 12px; }
        .login-message { color: #6b7280; font-size: 15px; line-height: 1.6; margin-bottom: 24px; }
        .login-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .login-buttons .btn { text-decoration: none; min-width: 140px; flex: 1; max-width: 200px; }

        /* Related Vehicles */
        .related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
        .related-card { background: #fff; border-radius: 12px; overflow: hidden; text-decoration: none; color: inherit; transition: all .3s; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; }
        .related-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(249,115,22,0.2); border-color: #f97316; }
        .related-image { width: 100%; height: 140px; object-fit: cover; background: #f9fafb; }
        .related-info { padding: 12px; }
        .related-title { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px; line-height: 1.4; }
        .related-price { font-size: 16px; font-weight: 700; color: #f97316; }

        /* Sticky Footer */
        .sticky-footer { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; padding: 12px 16px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); display: flex; gap: 12px; z-index: 100; border-top: 1px solid #e5e7eb; }
        .sticky-footer .btn { margin: 0; }
        .sticky-footer .btn-primary { flex: 2; }
        .sticky-footer .btn-secondary { flex: 0 0 auto; min-width: 56px; padding: 16px; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .gallery { max-height: 400px; }
            .content { padding: 12px; padding-bottom: 100px; }
            .card, .price-card { padding: 16px; border-radius: 12px; margin-bottom: 12px; }
            .vehicle-title { font-size: 20px; margin-bottom: 10px; }
            .vehicle-meta { gap: 6px; }
            .meta-item { padding: 6px 10px; font-size: 12px; }
            .meta-item i { font-size: 12px; }
            .price { font-size: 28px; }
            .price-period { font-size: 16px; }
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
            .vehicle-header-content { padding: 12px; }
            .vehicle-title-header h1 { font-size: 14px; }
            .vehicle-title-header .vehicle-category { font-size: 11px; }
            .btn-back, .header-icon-btn { width: 36px; height: 36px; font-size: 16px; }
            .gallery { max-height: 300px; }
            .thumbnails { padding: 10px 12px; gap: 6px; }
            .thumbnail { width: 60px; height: 60px; min-width: 60px; }
            .vehicle-title { font-size: 18px; }
            .price { font-size: 24px; }
            .price-period { font-size: 14px; }
        }
    </style>
</head>
<body>

<!-- Vehicle Detail Header -->
<header class="vehicle-header-bar">
    <div class="vehicle-header-content">
        <div class="header-left">
            <a href="<?= SITE_URL ?>/vehicles.php" class="btn-back" title="Go Back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="vehicle-title-header">
                <h1><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?></h1>
                <div class="vehicle-category"><?= htmlspecialchars($vehicle['category_name']) ?></div>
            </div>
        </div>
        
        <div class="header-actions">
            <button class="header-icon-btn" onclick="shareVehicle()" title="Share" type="button">
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
            <img src="<?= SITE_URL . '/' . htmlspecialchars($images[0]['image_path']) ?>" alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>" class="main-image" id="mainImage">
        <?php else: ?>
            <div class="no-image">
                <i class="fas fa-car"></i>
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
        <img src="<?= SITE_URL . '/' . htmlspecialchars($image['image_path']) ?>" alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>" class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="showImage(<?= $index ?>)">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="content">
        <div class="card">
            <span class="vehicle-badge"><?= ucfirst($vehicle['vehicle_type']) ?></span>
            <h1 class="vehicle-title"><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']) ?></h1>
            <div class="vehicle-meta">
                <div class="meta-item">
                    <i class="fas fa-cog"></i>
                    <span><?= ucfirst($vehicle['transmission']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-gas-pump"></i>
                    <span><?= ucfirst($vehicle['fuel_type']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-users"></i>
                    <span><?= $vehicle['seats'] ?> Seats</span>
                </div>
                <?php if ($vehicle['doors']): ?>
                <div class="meta-item">
                    <i class="fas fa-door-open"></i>
                    <span><?= $vehicle['doors'] ?> Doors</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="price-card">
            <div class="price-label">Daily Rate</div>
            <div class="price">
                RWF <?= number_format($vehicle['daily_rate']) ?>
                <span class="price-period">/ day</span>
            </div>
            <?php if ($vehicle['weekly_rate']): ?>
                <div style="margin-top: 8px; font-size: 14px; color: #666;">
                    Weekly: RWF <?= number_format($vehicle['weekly_rate']) ?>
                </div>
            <?php endif; ?>
            <?php if ($vehicle['monthly_rate']): ?>
                <div style="font-size: 14px; color: #666;">
                    Monthly: RWF <?= number_format($vehicle['monthly_rate']) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($vehicle['description']): ?>
        <div class="card">
            <h2 class="section-title">Description</h2>
            <div class="description"><?= nl2br(htmlspecialchars($vehicle['description'])) ?></div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="section-title">Vehicle Details</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-label">Brand</span>
                    <span class="spec-value"><?= htmlspecialchars($vehicle['brand']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Model</span>
                    <span class="spec-value"><?= htmlspecialchars($vehicle['model']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Year</span>
                    <span class="spec-value"><?= $vehicle['year'] ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Type</span>
                    <span class="spec-value"><?= ucfirst($vehicle['vehicle_type']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Transmission</span>
                    <span class="spec-value"><?= ucfirst($vehicle['transmission']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Fuel Type</span>
                    <span class="spec-value"><?= ucfirst($vehicle['fuel_type']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Seats</span>
                    <span class="spec-value"><?= $vehicle['seats'] ?></span>
                </div>
                <?php if ($vehicle['color']): ?>
                <div class="spec-item">
                    <span class="spec-label">Color</span>
                    <span class="spec-value"><?= htmlspecialchars($vehicle['color']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($vehicle['mileage']): ?>
                <div class="spec-item">
                    <span class="spec-label">Mileage</span>
                    <span class="spec-value"><?= number_format($vehicle['mileage']) ?> km</span>
                </div>
                <?php endif; ?>
                <?php if ($vehicle['location']): ?>
                <div class="spec-item">
                    <span class="spec-label">Location</span>
                    <span class="spec-value"><?= htmlspecialchars($vehicle['location']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($features)): ?>
        <div class="card">
            <h2 class="section-title">Features</h2>
            <div class="features-list">
                <?php foreach ($features as $feature): ?>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($feature) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2 class="section-title">Book This Vehicle</h2>
            <?php if (!$is_logged_in): ?>
            <div class="login-required">
                <div class="login-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 class="login-title">Login Required</h3>
                <p class="login-message">You need to login to book this vehicle.</p>
                <div class="login-buttons">
                    <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?= SITE_URL ?>/register.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
            <?php else: ?>
            <form onsubmit="bookVehicle(event)">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-input" id="startDate" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-input" id="endDate" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Pickup Location</label>
                    <input type="text" class="form-input" id="pickupLocation" placeholder="Enter pickup location">
                </div>
                <div class="form-group">
                    <label class="form-label">Additional Notes (Optional)</label>
                    <textarea class="form-input" id="customerNote" rows="3" placeholder="Any special requests..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-calendar-check"></i> Proceed to Booking
                </button>
            </form>
            <?php endif; ?>
        </div>

        <?php if (!empty($related)): ?>
        <div class="card">
            <h2 class="section-title"><i class="fas fa-th-large"></i> You May Also Like</h2>
            <div class="related-grid">
                <?php foreach ($related as $rel): ?>
                <a href="<?= SITE_URL ?>/vehicle-detail.php?id=<?= $rel['id'] ?>" class="related-card">
                    <?php if ($rel['image_path']): ?>
                        <img src="<?= SITE_URL . '/' . htmlspecialchars($rel['image_path']) ?>" alt="<?= htmlspecialchars($rel['brand'] . ' ' . $rel['model']) ?>" class="related-image">
                    <?php else: ?>
                        <div class="related-image" style="background: #f9fafb; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-car" style="font-size: 40px; color: #d1d5db;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="related-info">
                        <div class="related-title"><?= htmlspecialchars($rel['brand'] . ' ' . $rel['model'] . ' ' . $rel['year']) ?></div>
                        <div class="related-price">RWF <?= number_format($rel['daily_rate']) ?>/day</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="sticky-footer">
        <?php if ($is_logged_in): ?>
        <button class="btn btn-primary" style="flex: 2;" onclick="scrollToBooking()">
            <i class="fas fa-calendar-check"></i>
            Book Now
        </button>
        <?php else: ?>
        <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary" style="flex: 2;">
            <i class="fas fa-sign-in-alt"></i>
            Login to Book
        </a>
        <?php endif; ?>
        <button class="btn btn-secondary" style="flex: 1;" onclick="contactOwner()">
            <i class="fas fa-phone"></i>
        </button>
    </div>

    <script>
        const images = <?= json_encode(array_map(function($img) { return SITE_URL . '/' . $img['image_path']; }, $images)) ?>;
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
            toggleWishlist('vehicle', <?= $vehicle_id ?>, btn);
        }

        function shareVehicle() {
            const vehicleTitle = '<?= addslashes($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']) ?>';
            const vehicleUrl = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: vehicleTitle,
                    text: 'Check out this vehicle: ' + vehicleTitle,
                    url: vehicleUrl
                }).then(() => {
                    console.log('Share successful');
                }).catch((error) => {
                    console.log('Share failed:', error);
                    // Fallback to clipboard
                    copyToClipboard(vehicleUrl);
                });
            } else {
                // Fallback to clipboard
                copyToClipboard(vehicleUrl);
            }
        }
        
        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    showPopup({
                        type: 'success',
                        icon: 'fa-check-circle',
                        title: 'Link Copied!',
                        message: 'Vehicle link has been copied to clipboard.',
                        confirmText: 'OK'
                    });
                }).catch(() => {
                    // Fallback for older browsers
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }
        
        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showPopup({
                    type: 'success',
                    icon: 'fa-check-circle',
                    title: 'Link Copied!',
                    message: 'Vehicle link has been copied to clipboard.',
                    confirmText: 'OK'
                });
            } catch (err) {
                showPopup({
                    type: 'error',
                    icon: 'fa-exclamation-circle',
                    title: 'Copy Failed',
                    message: 'Unable to copy link. Please copy manually: ' + text,
                    confirmText: 'OK'
                });
            }
            
            document.body.removeChild(textArea);
        }

        function scrollToBooking() {
            document.querySelector('form').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function contactOwner() {
            showPopup({
                type: 'info',
                icon: 'fa-phone',
                title: 'Contact Information',
                message: 'Please call us at +250788000000 for more information about this vehicle.',
                confirmText: 'OK'
            });
        }

        function bookVehicle(e) {
            e.preventDefault();
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const pickupLocation = document.getElementById('pickupLocation').value;
            const note = document.getElementById('customerNote').value;
            
            // Calculate days
            const start = new Date(startDate);
            const end = new Date(endDate);
            const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            
            if (days < 1) {
                showPopup({
                    type: 'error',
                    icon: 'fa-exclamation-circle',
                    title: 'Invalid Dates',
                    message: 'End date must be after start date.',
                    confirmText: 'OK'
                });
                return;
            }
            
            // Redirect to checkout
            window.location.href = `<?= SITE_URL ?>/vehicle-checkout.php?vehicle_id=<?= $vehicle_id ?>&start=${startDate}&end=${endDate}&days=${days}&pickup=${encodeURIComponent(pickupLocation)}&note=${encodeURIComponent(note)}`;
        }
    </script>
    <script src="<?= SITE_URL ?>/assets/js/popup.js"></script>
</body>
</html>
