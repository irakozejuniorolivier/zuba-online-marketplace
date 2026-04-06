<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Fetch all vehicles
$query = "
    SELECT v.*, 
    (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image,
    c.name as category_name
    FROM vehicles v
    LEFT JOIN categories c ON v.category_id = c.id
    ORDER BY v.created_at DESC
";
$result = $conn->query($query);
$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Vehicles | Zuba Online Market</title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; color: #1a1a2e; }
    
    .header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 20px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .header-content { max-width: 1400px; margin: 0 auto; display: flex; align-items: center; gap: 16px; }
    .btn-back { width: 40px; height: 40px; background: #f5f5f5; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1a1a2e; text-decoration: none; cursor: pointer; transition: all 0.3s; }
    .btn-back:hover { background: #f97316; color: #fff; }
    .header-title h1 { font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 0; }
    .header-title p { font-size: 13px; color: #6b7280; margin: 4px 0 0; }
    
    .container { max-width: 1400px; margin: 0 auto; padding: 24px 20px; }
    
    .vehicles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    
    .vehicle-card { background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.3s; text-decoration: none; color: inherit; display: block; position: relative; }
    .vehicle-card:hover { border-color: #f97316; box-shadow: 0 8px 16px rgba(0,0,0,0.1); transform: translateY(-4px); }
    
    .vehicle-image { width: 100%; height: 200px; object-fit: cover; background: #f3f4f6; }
    .no-image { width: 100%; height: 200px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 48px; }
    
    .vehicle-content { padding: 16px; }
    .vehicle-badges { display: flex; gap: 6px; margin-bottom: 8px; flex-wrap: wrap; }
    .badge { display: inline-block; padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; border-radius: 4px; }
    .badge-category { background: #f3f4f6; color: #6b7280; }
    .badge-type { background: #dbeafe; color: #1e40af; }
    .badge-transmission { background: #fef3c7; color: #92400e; }
    
    .vehicle-title { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 48px; }
    .vehicle-details { display: flex; gap: 12px; margin-bottom: 12px; font-size: 12px; color: #6b7280; flex-wrap: wrap; }
    .vehicle-detail { display: flex; align-items: center; gap: 4px; }
    .vehicle-price { font-size: 20px; font-weight: 700; color: #f97316; }
    .vehicle-price-period { font-size: 12px; color: #6b7280; font-weight: 400; }
    
    .badge-featured { position: absolute; top: 12px; left: 12px; padding: 8px; background: #f97316; color: #fff; font-size: 16px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(249,115,22,0.4); }
    
    .badge-status { position: absolute; top: 12px; right: 12px; padding: 6px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; border-radius: 6px; }
    .badge-status.available { background: #d1fae5; color: #065f46; }
    .badge-status.rented { background: #fef3c7; color: #92400e; }
    .badge-status.maintenance { background: #fee2e2; color: #991b1b; }
    .badge-status.inactive { background: #f3f4f6; color: #6b7280; }
    
    .empty { text-align: center; padding: 80px 20px; background: #fff; border-radius: 12px; }
    .empty i { font-size: 64px; color: #d1d5db; margin-bottom: 16px; }
    .empty h3 { font-size: 20px; color: #1a1a2e; margin-bottom: 8px; }
    .empty p { font-size: 14px; color: #6b7280; margin-bottom: 20px; }
    .btn-home { padding: 12px 24px; background: #f97316; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s; }
    .btn-home:hover { background: #ea580c; }
    
    @media (max-width: 768px) {
        .header { padding: 16px; }
        .header-title h1 { font-size: 20px; }
        .header-title p { font-size: 12px; }
        .container { padding: 16px; }
        .vehicles-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .vehicle-image, .no-image { height: 140px; }
        .vehicle-content { padding: 12px; }
        .vehicle-title { font-size: 14px; min-height: 40px; }
        .vehicle-price { font-size: 16px; }
        .vehicle-details { gap: 8px; font-size: 11px; }
        .badge-featured { top: 8px; left: 8px; padding: 6px; font-size: 14px; width: 32px; height: 32px; }
        .badge-status { top: 8px; right: 8px; padding: 4px 8px; font-size: 10px; }
    }
    
    @media (max-width: 480px) {
        .vehicles-grid { gap: 10px; }
        .vehicle-image, .no-image { height: 130px; }
        .vehicle-content { padding: 10px; }
        .vehicle-title { font-size: 13px; -webkit-line-clamp: 2; }
        .vehicle-price { font-size: 15px; }
        .badge { font-size: 10px; padding: 3px 6px; }
        .vehicle-details { font-size: 10px; gap: 6px; }
    }
</style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">
            <h1>All Vehicles</h1>
            <p><?= count($vehicles) ?> vehicle(s) available</p>
        </div>
    </div>
</header>

<div class="container">
    <?php if (count($vehicles) > 0): ?>
        <div class="vehicles-grid">
            <?php foreach ($vehicles as $vehicle): ?>
                <a href="<?= SITE_URL ?>/vehicle-detail.php?slug=<?= e($vehicle['slug']) ?>" class="vehicle-card">
                    <?php if ($vehicle['featured']): ?>
                        <span class="badge-featured">
                            <i class="fas fa-star"></i>
                        </span>
                    <?php endif; ?>
                    
                    <span class="badge-status <?= $vehicle['status'] ?>">
                        <?= strtoupper($vehicle['status']) ?>
                    </span>
                    
                    <?php if ($vehicle['primary_image']): ?>
                        <img src="<?= SITE_URL . '/' . e($vehicle['primary_image']) ?>" alt="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>" class="vehicle-image">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-car"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="vehicle-content">
                        <div class="vehicle-badges">
                            <?php if ($vehicle['category_name']): ?>
                                <span class="badge badge-category"><?= e($vehicle['category_name']) ?></span>
                            <?php endif; ?>
                            <span class="badge badge-type"><?= ucfirst(e($vehicle['vehicle_type'])) ?></span>
                            <span class="badge badge-transmission"><?= ucfirst(e($vehicle['transmission'])) ?></span>
                        </div>
                        
                        <div class="vehicle-title"><?= e($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']) ?></div>
                        
                        <div class="vehicle-details">
                            <div class="vehicle-detail">
                                <i class="fas fa-users"></i> <?= $vehicle['seats'] ?> Seats
                            </div>
                            <div class="vehicle-detail">
                                <i class="fas fa-gas-pump"></i> <?= ucfirst($vehicle['fuel_type']) ?>
                            </div>
                            <?php if ($vehicle['color']): ?>
                                <div class="vehicle-detail">
                                    <i class="fas fa-palette"></i> <?= ucfirst($vehicle['color']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="vehicle-price">
                            <?= formatCurrency($vehicle['daily_rate']) ?>
                            <span class="vehicle-price-period">/ Day</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <i class="fas fa-car"></i>
            <h3>No Vehicles Available</h3>
            <p>Check back later for new vehicles</p>
            <a href="<?= SITE_URL ?>/index.php" class="btn-home">Go to Home</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
