<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';

// Fetch all active products
$query = "
    SELECT p.*, 
    (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
    c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.created_at DESC
";
$result = $conn->query($query);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products | Zuba Online Market</title>
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
    
    .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    
    .product-card { background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; transition: all 0.3s; text-decoration: none; color: inherit; display: block; position: relative; }
    .product-card:hover { border-color: #f97316; box-shadow: 0 8px 16px rgba(0,0,0,0.1); transform: translateY(-4px); }
    
    .product-image { width: 100%; height: 220px; object-fit: cover; background: #f3f4f6; }
    .no-image { width: 100%; height: 220px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 48px; }
    
    .product-content { padding: 16px; }
    .product-category { display: inline-block; padding: 4px 8px; background: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; border-radius: 4px; margin-bottom: 8px; }
    .product-name { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 48px; }
    .product-price { font-size: 20px; font-weight: 700; color: #f97316; margin-bottom: 8px; }
    .product-stock { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px; }
    .product-stock.in-stock { color: #059669; }
    .product-stock.out-stock { color: #dc2626; }
    
    .badge-featured { position: absolute; top: 12px; left: 12px; padding: 8px; background: #f97316; color: #fff; font-size: 16px; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(249,115,22,0.4); }
    
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
        .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .product-image, .no-image { height: 160px; }
        .product-content { padding: 12px; }
        .product-name { font-size: 14px; min-height: 40px; }
        .product-price { font-size: 16px; }
        .badge-featured { top: 8px; left: 8px; padding: 6px; font-size: 14px; width: 32px; height: 32px; }
    }
    
    @media (max-width: 480px) {
        .products-grid { gap: 10px; }
        .product-image, .no-image { height: 140px; }
        .product-content { padding: 10px; }
        .product-name { font-size: 13px; -webkit-line-clamp: 2; }
        .product-price { font-size: 15px; }
        .product-category { font-size: 10px; padding: 3px 6px; }
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
            <h1>All Products</h1>
            <p><?= count($products) ?> product(s) available</p>
        </div>
    </div>
</header>

<div class="container">
    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <a href="<?= SITE_URL ?>/product-detail.php?slug=<?= e($product['slug']) ?>" class="product-card">
                    <?php if ($product['featured']): ?>
                        <span class="badge-featured">
                            <i class="fas fa-star"></i>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($product['primary_image']): ?>
                        <img src="<?= UPLOAD_URL . 'products/' . e($product['primary_image']) ?>" alt="<?= e($product['name']) ?>" class="product-image">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-box-open"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-content">
                        <?php if ($product['category_name']): ?>
                            <span class="product-category"><?= e($product['category_name']) ?></span>
                        <?php endif; ?>
                        <div class="product-name"><?= e($product['name']) ?></div>
                        <div class="product-price"><?= formatCurrency($product['price']) ?></div>
                        <?php if ($product['stock'] > 0): ?>
                            <div class="product-stock in-stock">
                                <i class="fas fa-check-circle"></i> <?= $product['stock'] ?> in stock
                            </div>
                        <?php else: ?>
                            <div class="product-stock out-stock">
                                <i class="fas fa-times-circle"></i> Out of stock
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <i class="fas fa-box-open"></i>
            <h3>No Products Available</h3>
            <p>Check back later for new products</p>
            <a href="<?= SITE_URL ?>/index.php" class="btn-home">Go to Home</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
