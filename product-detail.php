<?php
/**
 * PRODUCT DETAIL PAGE
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Fetch product details
$query = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 'active'
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Check if user is logged in
$is_logged_in = isCustomerLoggedIn();
$customer = currentCustomer();

// Update views
$conn->query("UPDATE products SET views = views + 1 WHERE id = $product_id");

// Log activity if user is logged in
if ($is_logged_in) {
    logActivity($conn, 'customer', currentCustomerId(), 'VIEW_PRODUCT', 'Viewed product: ' . $product['name'] . ' (ID: ' . $product_id . ')');
}

// Fetch product images
$images = [];
$result = $conn->query("SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC, sort_order ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}

// Fetch related products (same category)
$related = [];
$result = $conn->query("
    SELECT p.id, p.name, p.price, pi.image_path
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.category_id = {$product['category_id']} 
    AND p.id != $product_id 
    AND p.status = 'active'
    ORDER BY RAND()
    LIMIT 8
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $related[] = $row;
    }
}

// Check if product is in wishlist
$in_wishlist = false;
if ($is_logged_in) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND item_type = 'product' AND item_id = ?");
    $stmt->bind_param('ii', $user_id, $product_id);
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

// Get cart count
$cart_count = 0;
if ($is_logged_in) {
    $user_id = currentCustomerId();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cart_count = $row['count'];
    $stmt->close();
}

$page_title = $product['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> | Zuba Online Market</title>
    <meta name="description" content="<?= e(substr($product['description'] ?? '', 0, 160)) ?>">
    
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
        
        /* Product Detail Header */
        .product-header-bar { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .product-header-content { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; gap: 16px; max-width: 1400px; margin: 0 auto; }
        
        .header-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; flex-shrink: 0; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; transform: scale(1.05); }
        
        .product-title-header { flex: 1; min-width: 0; }
        .product-title-header h1 { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-title-header .product-category { font-size: 12px; color: #6b7280; margin-top: 2px; }
        
        .header-actions { display: flex; align-items: center; gap: 8px; }
        .header-icon-btn { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; position: relative; text-decoration: none; }
        .header-icon-btn:hover { background: #fff5f0; border-color: #f97316; color: #f97316; transform: scale(1.05); }
        .header-icon-btn.active { background: #f97316; color: #fff; border-color: #f97316; }
        
        .cart-badge, .wishlist-badge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #fff; }
        
        /* Mobile Responsive */
        @media (max-width: 640px) {
            .product-header-content { padding: 12px 12px; }
            .product-title-header h1 { font-size: 14px; }
            .product-title-header .product-category { font-size: 11px; }
            .btn-back, .header-icon-btn { width: 36px; height: 36px; font-size: 16px; }
        }
    </style>
</head>
<body>

<!-- Product Detail Header -->
<header class="product-header-bar">
    <div class="product-header-content">
        <div class="header-left">
            <a href="javascript:history.back()" class="btn-back" title="Go Back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="product-title-header">
                <h1><?= e($product['name']) ?></h1>
                <div class="product-category"><?= e($product['category_name']) ?></div>
            </div>
        </div>
        
        <div class="header-actions">
            <a href="<?= SITE_URL ?>/wishlist.php" class="header-icon-btn" id="wishlistBtn" title="Wishlist">
                <i class="far fa-heart"></i>
                <?php if ($wishlist_count > 0): ?>
                    <span class="wishlist-badge" id="wishlistCount"><?= $wishlist_count ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/cart.php" class="header-icon-btn" title="Shopping Cart">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge" id="cartCount"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</header>

<div class="product-detail">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i></a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= SITE_URL ?>/products.php?category=<?= $product['category_slug'] ?>"><?= e($product['category_name']) ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?= e(substr($product['name'], 0, 30)) ?>...</span>
        </div>
    </div>

    <!-- Product Section -->
    <div class="product-section">
        <div class="container">
            <div class="product-wrapper">
                
                <!-- Image Gallery -->
                <div class="product-gallery">
                    <div class="main-image-wrapper">
                        <div class="main-image">
                            <?php if (!empty($images)): ?>
                                <img id="mainImg" src="<?= UPLOAD_URL . 'products/' . $images[0]['image_path'] ?>" alt="<?= e($product['name']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-box-open"></i>
                                    <p>No Image</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                            <div class="discount-badge">
                                <?php 
                                $discount = round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100);
                                echo "-{$discount}%";
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <div class="thumbnails">
                            <?php foreach ($images as $idx => $img): ?>
                                <div class="thumb-item <?= $idx === 0 ? 'active' : '' ?>" onclick="changeImage('<?= UPLOAD_URL . 'products/' . $img['image_path'] ?>', this)">
                                    <img src="<?= UPLOAD_URL . 'products/' . $img['image_path'] ?>" alt="<?= e($product['name']) ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-header">
                        <div>
                            <div class="category-badge">
                                <i class="fas fa-tag"></i> <?= e($product['category_name']) ?>
                            </div>
                            <h1><?= e($product['name']) ?></h1>
                        </div>
                        <button class="btn-wishlist-top <?= $in_wishlist ? 'active' : '' ?>" onclick="addToWishlist(<?= $product_id ?>)" title="<?= $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                            <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="product-meta-row">
                        <?php if ($product['brand']): ?>
                            <div class="meta-item">
                                <i class="fas fa-copyright"></i>
                                <span><?= e($product['brand']) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-barcode"></i>
                            <span><?= e($product['sku'] ?? 'N/A') ?></span>
                        </div>
                        <?php if ($product['condition']): ?>
                            <div class="meta-item">
                                <i class="fas fa-box"></i>
                                <span><?= ucfirst($product['condition']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="price-card">
                        <div class="price-main">
                            <div class="current-price"><?= formatCurrency($product['price']) ?></div>
                            <?php if ($product['compare_price'] && $product['compare_price'] > $product['price']): ?>
                                <div class="old-price"><?= formatCurrency($product['compare_price']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="stock-badge <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
                            <?php if ($product['stock'] > 0): ?>
                                <i class="fas fa-check-circle"></i> <?= $product['stock'] ?> In Stock
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> Out of Stock
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-card">
                            <label>Quantity</label>
                            <div class="quantity-selector">
                                <button class="qty-btn" onclick="decreaseQty()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                                <button class="qty-btn" onclick="increaseQty()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <button class="btn-add-cart" onclick="addToCart(<?= $product_id ?>)">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Add to Cart</span>
                            </button>
                            <button class="btn-buy-now" onclick="buyNow(<?= $product_id ?>)">
                                <i class="fas fa-bolt"></i>
                                <span>Buy Now</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="out-of-stock-notice">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>This product is currently out of stock</span>
                        </div>
                    <?php endif; ?>

                    <div class="features-grid">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="feature-text">
                                <strong>Free Shipping</strong>
                                <span>On orders over 50,000 FRw</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-text">
                                <strong>Secure Payment</strong>
                                <span>100% secure transactions</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="feature-text">
                                <strong>Easy Returns</strong>
                                <span>7 days return policy</span>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="feature-text">
                                <strong>24/7 Support</strong>
                                <span>Dedicated customer service</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if ($product['description']): ?>
                <div class="info-section">
                    <div class="section-header">
                        <i class="fas fa-info-circle"></i>
                        <h2>Product Description</h2>
                    </div>
                    <div class="section-content">
                        <?= nl2br(e($product['description'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Specifications -->
            <div class="info-section">
                <div class="section-header">
                    <i class="fas fa-list-ul"></i>
                    <h2>Specifications</h2>
                </div>
                <div class="specs-grid">
                    <div class="spec-item">
                        <span class="spec-label">Category</span>
                        <span class="spec-value"><?= e($product['category_name']) ?></span>
                    </div>
                    <?php if ($product['brand']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Brand</span>
                            <span class="spec-value"><?= e($product['brand']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="spec-item">
                        <span class="spec-label">SKU</span>
                        <span class="spec-value"><?= e($product['sku'] ?? 'N/A') ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Condition</span>
                        <span class="spec-value"><?= ucfirst($product['condition'] ?? 'New') ?></span>
                    </div>
                    <?php if ($product['weight']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Weight</span>
                            <span class="spec-value"><?= e($product['weight']) ?> kg</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($product['dimensions']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Dimensions</span>
                            <span class="spec-value"><?= e($product['dimensions']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related)): ?>
                <div class="related-section">
                    <div class="section-header">
                        <i class="fas fa-th-large"></i>
                        <h2>You May Also Like</h2>
                    </div>
                    <div class="related-grid">
                        <?php foreach ($related as $item): ?>
                            <a href="<?= SITE_URL ?>/product-detail.php?id=<?= $item['id'] ?>" class="related-card">
                                <div class="related-img">
                                    <?php if ($item['image_path']): ?>
                                        <img src="<?= UPLOAD_URL . 'products/' . $item['image_path'] ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fas fa-box-open"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="related-info">
                                    <h3><?= e(substr($item['name'], 0, 40)) ?></h3>
                                    <div class="related-price"><?= formatCurrency($item['price']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Sticky Footer -->
    <?php if ($product['stock'] > 0): ?>
        <div class="mobile-sticky-footer">
            <div class="mobile-price"><?= formatCurrency($product['price']) ?></div>
            <button class="mobile-cart-btn" onclick="addToCart(<?= $product_id ?>)">
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>
        </div>
    <?php endif; ?>
</div>

<style>
/* Global */
* { box-sizing: border-box; }
body { background: #f5f5f5; }

/* Breadcrumb */
.breadcrumb { background: #fff; padding: 14px 0; border-bottom: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.breadcrumb .container { display: flex; align-items: center; gap: 10px; font-size: 13px; flex-wrap: wrap; }
.breadcrumb a { color: #6b7280; text-decoration: none; transition: color .3s; display: flex; align-items: center; }
.breadcrumb a:hover { color: #f97316; }
.breadcrumb i.fa-chevron-right { color: #d1d5db; font-size: 10px; }
.breadcrumb span { color: #1a1a2e; font-weight: 600; }

/* Product Section */
.product-section { padding: 24px 0 80px; }
.product-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px; }

/* Image Gallery */
.product-gallery { position: sticky; top: 80px; height: fit-content; }
.main-image-wrapper { position: relative; margin-bottom: 16px; }
.main-image { width: 100%; aspect-ratio: 1; background: #fff; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.main-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
.main-image:hover img { transform: scale(1.05); }
.no-image { display: flex; flex-direction: column; align-items: center; gap: 12px; color: #d1d5db; }
.no-image i { font-size: 80px; }
.no-image p { font-size: 16px; font-weight: 600; margin: 0; }
.discount-badge { position: absolute; top: 16px; right: 16px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 800; box-shadow: 0 4px 12px rgba(239,68,68,0.4); z-index: 10; }

.thumbnails { display: flex; gap: 12px; overflow-x: auto; padding: 4px; scrollbar-width: thin; scrollbar-color: #f97316 #f3f4f6; }
.thumbnails::-webkit-scrollbar { height: 6px; }
.thumbnails::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 10px; }
.thumbnails::-webkit-scrollbar-thumb { background: #f97316; border-radius: 10px; }
.thumb-item { min-width: 80px; width: 80px; height: 80px; border-radius: 12px; overflow: hidden; cursor: pointer; border: 2px solid #e5e7eb; transition: all .3s; background: #fff; }
.thumb-item:hover { border-color: #f97316; transform: scale(1.05); }
.thumb-item.active { border-color: #f97316; box-shadow: 0 0 0 2px rgba(249,115,22,0.2); }
.thumb-item img { width: 100%; height: 100%; object-fit: cover; }

/* Product Info */
.product-info { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.product-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
.category-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); color: #f97316; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 12px; border: 1px solid rgba(249,115,22,0.2); }
.product-info h1 { font-size: 28px; font-weight: 900; color: #1a1a2e; margin: 0; line-height: 1.3; }
.btn-wishlist-top { background: #fff; border: 2px solid #e5e7eb; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; font-size: 20px; color: #6b7280; flex-shrink: 0; }
.btn-wishlist-top:hover { border-color: #f97316; color: #f97316; background: #fff5f0; transform: scale(1.05); }
.btn-wishlist-top.active { background: #f97316; color: #fff; border-color: #f97316; }

.product-meta-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
.meta-item { display: flex; align-items: center; gap: 6px; background: #f9fafb; padding: 8px 12px; border-radius: 10px; font-size: 13px; color: #6b7280; border: 1px solid #e5e7eb; white-space: nowrap; }
.meta-item i { color: #f97316; font-size: 14px; }

.price-card { background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); border-radius: 14px; padding: 20px; margin-bottom: 24px; border: 1px solid rgba(249,115,22,0.2); }
.price-main { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.current-price { font-size: 36px; font-weight: 900; color: #f97316; line-height: 1; }
.old-price { font-size: 20px; color: #9ca3af; text-decoration: line-through; font-weight: 600; }
.stock-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; }
.stock-badge.in-stock { background: #d1fae5; color: #065f46; }
.stock-badge.out-stock { background: #fee2e2; color: #991b1b; }

.quantity-card { margin-bottom: 24px; }
.quantity-card label { display: block; font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
.quantity-selector { display: flex; align-items: center; gap: 0; width: fit-content; background: #f9fafb; border-radius: 12px; border: 2px solid #e5e7eb; overflow: hidden; }
.qty-btn { background: transparent; border: none; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #1a1a2e; font-size: 16px; transition: all .3s; }
.qty-btn:hover { background: #f97316; color: #fff; }
.qty-btn:active { transform: scale(0.95); }
.quantity-selector input { width: 60px; text-align: center; border: none; background: transparent; font-size: 16px; font-weight: 700; color: #1a1a2e; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; height: 44px; }

.action-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
.btn-add-cart, .btn-buy-now { border: none; padding: 16px 20px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; }
.btn-add-cart { background: #f97316; color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
.btn-add-cart:hover { background: #ea580c; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
.btn-buy-now { background: #1a1a2e; color: #fff; box-shadow: 0 4px 12px rgba(26,26,46,0.3); }
.btn-buy-now:hover { background: #0f0f1e; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(26,26,46,0.4); }

.out-of-stock-notice { background: #fee2e2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 12px; color: #991b1b; font-weight: 600; margin-bottom: 24px; }
.out-of-stock-notice i { font-size: 20px; }

.features-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.feature-item { display: flex; gap: 12px; padding: 14px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; transition: all .3s; }
.feature-item:hover { background: #fff5f0; border-color: rgba(249,115,22,0.3); }
.feature-icon { width: 40px; height: 40px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 18px; flex-shrink: 0; }
.feature-text { display: flex; flex-direction: column; gap: 2px; }
.feature-text strong { font-size: 13px; color: #1a1a2e; font-weight: 700; }
.feature-text span { font-size: 12px; color: #6b7280; }

/* Info Sections */
.info-section { background: #fff; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6; }
.section-header i { color: #f97316; font-size: 22px; }
.section-header h2 { font-size: 22px; font-weight: 900; color: #1a1a2e; margin: 0; }
.section-content { font-size: 15px; line-height: 1.8; color: #4b5563; }

.specs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.spec-item { display: flex; justify-content: space-between; align-items: center; padding: 14px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; }
.spec-label { font-size: 13px; color: #6b7280; font-weight: 600; }
.spec-value { font-size: 14px; color: #1a1a2e; font-weight: 700; }

/* Related Products */
.related-section { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
.related-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; text-decoration: none; color: inherit; transition: all .3s; display: block; }
.related-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.12); transform: translateY(-4px); border-color: #f97316; }
.related-img { width: 100%; height: 180px; background: #f9fafb; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.related-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.related-card:hover .related-img img { transform: scale(1.08); }
.no-img { font-size: 50px; color: #d1d5db; }
.related-info { padding: 14px; }
.related-info h3 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px; }
.related-price { font-size: 16px; font-weight: 800; color: #f97316; }

/* Mobile Sticky Footer */
.mobile-sticky-footer { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; padding: 12px 16px; box-shadow: 0 -4px 12px rgba(0,0,0,0.1); z-index: 1000; border-top: 1px solid #e5e7eb; align-items: center; gap: 12px; }
.mobile-price { font-size: 20px; font-weight: 900; color: #f97316; white-space: nowrap; }
.mobile-cart-btn { flex: 1; background: #f97316; color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; }
.mobile-cart-btn:active { transform: scale(0.98); background: #ea580c; }

/* Responsive */
@media (max-width: 900px) {
    .product-wrapper { grid-template-columns: 1fr; gap: 24px; }
    .product-gallery { position: static; }
    .product-info h1 { font-size: 24px; }
    .current-price { font-size: 30px; }
    .features-grid { grid-template-columns: 1fr; }
    .action-buttons { grid-template-columns: 1fr; }
    .mobile-sticky-footer { display: flex; }
    .product-section { padding-bottom: 100px; }
}

@media (max-width: 640px) {
    .product-section { padding: 16px 0 100px; }
    .product-info { padding: 20px; border-radius: 12px; }
    .product-info h1 { font-size: 20px; }
    .product-meta-row { gap: 6px; margin-bottom: 20px; }
    .meta-item { padding: 6px 10px; font-size: 12px; }
    .meta-item i { font-size: 12px; }
    .price-card { padding: 16px; border-radius: 12px; margin-bottom: 20px; }
    .current-price { font-size: 26px; }
    .old-price { font-size: 16px; }
    .quantity-card { margin-bottom: 20px; }
    .action-buttons { gap: 10px; margin-bottom: 20px; }
    .features-grid { grid-template-columns: 1fr; gap: 12px; }
    .feature-item { padding: 12px; }
    .feature-icon { width: 36px; height: 36px; font-size: 16px; }
    .feature-text strong { font-size: 12px; }
    .feature-text span { font-size: 11px; }
    .info-section, .related-section { padding: 20px; border-radius: 12px; margin-bottom: 16px; }
    .section-header { margin-bottom: 16px; padding-bottom: 12px; }
    .section-header h2 { font-size: 18px; }
    .section-content { font-size: 14px; }
    .related-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .related-img { height: 140px; }
    .related-info { padding: 12px; }
    .related-info h3 { font-size: 13px; min-height: 32px; }
    .related-price { font-size: 15px; }
    .specs-grid { grid-template-columns: 1fr; gap: 12px; }
    .spec-item { padding: 12px; }
    .spec-label { font-size: 12px; }
    .spec-value { font-size: 13px; }
    .thumbnails { gap: 8px; padding: 10px 16px; }
    .thumb-item { min-width: 60px; width: 60px; height: 60px; }
    .mobile-sticky-footer { padding: 10px 16px; }
    .mobile-price { font-size: 18px; }
    .mobile-cart-btn { padding: 12px 16px; font-size: 14px; }
}

/* Modern Popup Notification System */
.popup-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; opacity: 0; animation: fadeIn 0.3s forwards; }
@keyframes fadeIn { to { opacity: 1; } }

.popup-container { background: #fff; border-radius: 20px; max-width: 420px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); transform: scale(0.9) translateY(20px); animation: popupSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; overflow: hidden; }
@keyframes popupSlideIn { to { transform: scale(1) translateY(0); } }

.popup-header { padding: 28px 24px 20px; text-align: center; position: relative; }
.popup-icon { width: 80px; height: 80px; margin: 0 auto 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; animation: iconBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes iconBounce { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }

.popup-success .popup-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; box-shadow: 0 8px 24px rgba(16,185,129,0.4); }
.popup-error .popup-icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; box-shadow: 0 8px 24px rgba(239,68,68,0.4); }
.popup-warning .popup-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #fff; box-shadow: 0 8px 24px rgba(245,158,11,0.4); }
.popup-info .popup-icon { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; box-shadow: 0 8px 24px rgba(59,130,246,0.4); }

.popup-title { font-size: 24px; font-weight: 900; color: #1a1a2e; margin: 0 0 8px; }
.popup-message { font-size: 15px; color: #6b7280; line-height: 1.6; margin: 0; }

.popup-body { padding: 0 24px 24px; }
.popup-actions { display: flex; gap: 12px; }
.popup-btn { flex: 1; border: none; padding: 14px 20px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; transition: all .3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
.popup-btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
.popup-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
.popup-btn-secondary { background: #f3f4f6; color: #4b5563; }
.popup-btn-secondary:hover { background: #e5e7eb; }
.popup-btn-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
.popup-btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(16,185,129,0.4); }
.popup-btn-danger { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
.popup-btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(239,68,68,0.4); }

.popup-close { position: absolute; top: 16px; right: 16px; width: 32px; height: 32px; border-radius: 8px; background: #f3f4f6; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #6b7280; font-size: 18px; transition: all .3s; }
.popup-close:hover { background: #e5e7eb; color: #1a1a2e; transform: rotate(90deg); }

@media (max-width: 480px) {
    .popup-container { max-width: 100%; margin: 0 16px; }
    .popup-icon { width: 70px; height: 70px; font-size: 32px; }
    .popup-title { font-size: 20px; }
    .popup-message { font-size: 14px; }
    .popup-actions { flex-direction: column; }
}
</style>

<script>
// Modern Popup Notification System
function showPopup(options) {
    const { type = 'info', icon, title, message, confirmText = 'OK', cancelText = 'Cancel', showCancel = false, onConfirm, onCancel } = options;
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'popup-overlay';
    
    // Icon mapping
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
        question: 'fa-question-circle',
        cart: 'fa-shopping-cart',
        heart: 'fa-heart',
        login: 'fa-user-lock'
    };
    
    const iconClass = icon || icons[type] || icons.info;
    
    // Create popup HTML
    overlay.innerHTML = `
        <div class="popup-container popup-${type}">
            <div class="popup-header">
                <button class="popup-close" onclick="closePopup(this)"><i class="fas fa-times"></i></button>
                <div class="popup-icon">
                    <i class="fas ${iconClass}"></i>
                </div>
                <h3 class="popup-title">${title}</h3>
                <p class="popup-message">${message}</p>
            </div>
            <div class="popup-body">
                <div class="popup-actions">
                    ${showCancel ? `<button class="popup-btn popup-btn-secondary" onclick="closePopup(this, 'cancel')">${cancelText}</button>` : ''}
                    <button class="popup-btn popup-btn-${type === 'error' || type === 'warning' ? 'danger' : type === 'success' ? 'success' : 'primary'}" onclick="closePopup(this, 'confirm')">${confirmText}</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Store callbacks
    overlay.dataset.onConfirm = onConfirm ? 'true' : 'false';
    overlay.dataset.onCancel = onCancel ? 'true' : 'false';
    if (onConfirm) overlay._onConfirm = onConfirm;
    if (onCancel) overlay._onCancel = onCancel;
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closePopup(overlay, 'cancel');
    });
    
    return overlay;
}

function closePopup(element, action = 'close') {
    const overlay = element.closest ? element.closest('.popup-overlay') : element;
    
    // Execute callbacks
    if (action === 'confirm' && overlay._onConfirm) {
        overlay._onConfirm();
    } else if (action === 'cancel' && overlay._onCancel) {
        overlay._onCancel();
    }
    
    // Animate out
    overlay.style.animation = 'fadeOut 0.3s forwards';
    setTimeout(() => overlay.remove(), 300);
}

// Add fadeOut animation
const style = document.createElement('style');
style.textContent = '@keyframes fadeOut { to { opacity: 0; } }';
document.head.appendChild(style);

function changeImage(src, element) {
    document.getElementById('mainImg').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumb-item').forEach(thumb => {
        thumb.classList.remove('active');
    });
    element.classList.add('active');
}

function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value);
    if (current > 1) {
        input.value = current - 1;
    }
}

function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    <?php if (!$is_logged_in): ?>
        showPopup({
            type: 'warning',
            icon: 'fa-user-lock',
            title: 'Login Required',
            message: 'Please login to add items to your cart. Would you like to go to the login page?',
            confirmText: 'Go to Login',
            cancelText: 'Cancel',
            showCancel: true,
            onConfirm: () => {
                window.location.href = '<?= SITE_URL ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
            }
        });
        return;
    <?php endif; ?>
    
    // Show loading state
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.disabled = true;
    
    // Send request to cart API
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('<?= SITE_URL ?>/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (data.success) {
            // Show success popup
            showPopup({
                type: 'success',
                icon: 'fa-shopping-cart',
                title: data.action === 'updated' ? 'Cart Updated!' : 'Added to Cart!',
                message: data.message,
                confirmText: 'Continue Shopping',
                cancelText: 'View Cart',
                showCancel: true,
                onCancel: () => {
                    window.location.href = '<?= SITE_URL ?>/cart.php';
                }
            });
            
            // Update cart count
            updateCartCount();
        } else {
            // Show error popup
            showPopup({
                type: 'error',
                icon: 'fa-exclamation-circle',
                title: 'Error',
                message: data.message,
                confirmText: 'OK'
            });
        }
    })
    .catch(error => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        showPopup({
            type: 'error',
            icon: 'fa-exclamation-circle',
            title: 'Error',
            message: 'Failed to add item to cart. Please try again.',
            confirmText: 'OK'
        });
        console.error('Error:', error);
    });
}

function buyNow(productId) {
    <?php if (!$is_logged_in): ?>
        showPopup({
            type: 'warning',
            icon: 'fa-user-lock',
            title: 'Login Required',
            message: 'Please login to continue with your purchase. Would you like to go to the login page?',
            confirmText: 'Go to Login',
            cancelText: 'Cancel',
            showCancel: true,
            onConfirm: () => {
                window.location.href = '<?= SITE_URL ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
            }
        });
        return;
    <?php endif; ?>
    
    const quantity = document.getElementById('quantity').value;
    
    // Show loading state
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;
    
    // Add to cart first
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('<?= SITE_URL ?>/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to cart with this product pre-selected
            window.location.href = '<?= SITE_URL ?>/cart.php?buy_now=' + productId;
        } else {
            btn.innerHTML = originalHTML;
            btn.disabled = false;
            
            showPopup({
                type: 'error',
                icon: 'fa-exclamation-circle',
                title: 'Error',
                message: data.message,
                confirmText: 'OK'
            });
        }
    })
    .catch(error => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        showPopup({
            type: 'error',
            icon: 'fa-exclamation-circle',
            title: 'Error',
            message: 'Failed to process your request. Please try again.',
            confirmText: 'OK'
        });
        console.error('Error:', error);
    });
}

function addToWishlist(productId) {
    <?php if (!$is_logged_in): ?>
        showPopup({
            type: 'warning',
            icon: 'fa-lock',
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
    
    const btn = event.target.closest('button');
    toggleWishlist('product', productId, btn);
}

// Smooth scroll to sections
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Lazy load images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});
</script>

<?php
// Include footer if you have one, or close body/html tags
?>

<script>
// Update cart count dynamically
function updateCartCount() {
    fetch('<?= SITE_URL ?>/api/get-counts.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.getElementById('cartCount');
            if (data.cart_count > 0) {
                cartBadge.textContent = data.cart_count;
                cartBadge.style.display = 'flex';
            } else {
                cartBadge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching cart count:', error));
}

// Update wishlist count dynamically
function updateWishlistCount() {
    fetch('<?= SITE_URL ?>/api/get-counts.php')
        .then(response => response.json())
        .then(data => {
            const wishlistBadge = document.getElementById('wishlistCount');
            if (data.wishlist_count > 0) {
                wishlistBadge.textContent = data.wishlist_count;
                wishlistBadge.style.display = 'flex';
            } else {
                wishlistBadge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching wishlist count:', error));
}
</script>
<script src="<?= SITE_URL ?>/assets/js/popup.js"></script>

</body>
</html>
