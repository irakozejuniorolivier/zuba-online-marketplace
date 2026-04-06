<?php
/**
 * ZUBA ONLINE MARKET - HOMEPAGE
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Home';
$customer = currentCustomer();
$is_logged_in = isCustomerLoggedIn();

// ===== FETCH FEATURED ITEMS =====
$featured_items = [];

// Products (image stored as filename only)
$result = $conn->query("
    SELECT p.id, p.name, p.price, pi.image_path, 'product' as type
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.featured = 1 AND p.status = 'active'
    ORDER BY p.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_items[] = $row;
    }
}

// Properties (status can be empty, image stored as filename only)
$result = $conn->query("
    SELECT p.id, p.title as name, p.price, p.city, p.listing_type, pi.image_path, 'property' as type
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
    WHERE p.featured = 1
    ORDER BY p.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_items[] = $row;
    }
}

// Vehicles (image stored with full path 'uploads/vehicles/filename')
$result = $conn->query("
    SELECT v.id, CONCAT(v.brand, ' ', v.model) as name, v.daily_rate as price, v.year, v.vehicle_type, vi.image_path, 'vehicle' as type
    FROM vehicles v
    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1
    WHERE v.featured = 1 AND v.status = 'available'
    ORDER BY v.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_items[] = $row;
    }
}

// Shuffle and limit
shuffle($featured_items);
$featured_items = array_slice($featured_items, 0, 12);

// ===== FETCH ALL ITEMS (Featured + Non-Featured) =====
$all_items = [];

// All Products
$result = $conn->query("
    SELECT p.id, p.name, p.price, p.featured, pi.image_path, 'product' as type
    FROM products p
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
    WHERE p.status = 'active'
    ORDER BY p.featured DESC, p.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }
}

// All Properties
$result = $conn->query("
    SELECT p.id, p.title as name, p.price, p.city, p.listing_type, p.featured, pi.image_path, 'property' as type
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
    ORDER BY p.featured DESC, p.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }
}

// All Vehicles
$result = $conn->query("
    SELECT v.id, CONCAT(v.brand, ' ', v.model) as name, v.daily_rate as price, v.year, v.vehicle_type, v.featured, vi.image_path, 'vehicle' as type
    FROM vehicles v
    LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1
    WHERE v.status = 'available'
    ORDER BY v.featured DESC, v.created_at DESC
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_items[] = $row;
    }
}

// Sort all items: featured first, then by newest
usort($all_items, function($a, $b) {
    if ($a['featured'] != $b['featured']) {
        return $b['featured'] - $a['featured'];
    }
    return 0;
});


// ===== FETCH ACTIVE BANNERS =====
$banners = [];
$result = $conn->query("
    SELECT * FROM banners 
    WHERE position = 'hero' 
    AND page IN ('home', 'all') 
    AND status = 'active' 
    AND (start_date IS NULL OR start_date <= NOW()) 
    AND (end_date IS NULL OR end_date >= NOW())
    ORDER BY sort_order ASC
");
if ($result && $result->num_rows > 0) {
    $banners = $result->fetch_all(MYSQLI_ASSOC);
}

// Track banner views
if (!empty($banners)) {
    $banner_ids = array_column($banners, 'id');
    if (!empty($banner_ids)) {
        $ids_str = implode(',', array_map('intval', $banner_ids));
        $conn->query("UPDATE banners SET views = views + 1 WHERE id IN ($ids_str)");
    }
}

require_once 'includes/header.php';
?>

    <!-- ===== HERO BANNER CAROUSEL ===== -->
    <?php if (!empty($banners)): ?>
        <section class="hero-section">
            <div class="hero-wrapper container">
                <div class="hero-main" aria-live="polite">
                    <div class="hero-slides">
                        <?php foreach ($banners as $i => $b):
                            $img = !empty($b['image']) ? UPLOAD_URL . 'banners/' . e($b['image']) : '';
                            $bg = !empty($b['background_color']) ? e($b['background_color']) : 'var(--primary)';
                            $txt = !empty($b['text_color']) ? e($b['text_color']) : '#ffffff';
                            $ov = is_numeric($b['overlay_opacity']) ? (float)$b['overlay_opacity'] : 0.45;
                        ?>
                            <figure class="hero-slide <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" data-id="<?php echo $b['id']; ?>" style="--bg: <?php echo $bg; ?>; --txt: <?php echo $txt; ?>; --ov: <?php echo $ov; ?>; <?php if ($img): ?>background-image: url('<?php echo $img; ?>'); background-size: cover; background-position: center;<?php endif; ?>">
                                <?php if ($img): ?>
                                    <img loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>" src="<?php echo $img; ?>" alt="<?php echo e($b['title']); ?>" aria-hidden="true" />
                                <?php else: ?>
                                    <div class="hero-media-placeholder"></div>
                                <?php endif; ?>
                                <div class="hero-overlay"></div>
                                <figcaption class="hero-caption">
                                    <h2><?php echo e($b['title']); ?></h2>
                                    <?php if (!empty($b['subtitle'])): ?><p class="hero-sub"><?php echo e($b['subtitle']); ?></p><?php endif; ?>
                                    <?php if (!empty($b['button_text']) && !empty($b['button_link'])): ?>
                                        <a class="hero-cta" href="<?php echo SITE_URL . '/' . e($b['button_link']); ?>" data-banner-id="<?php echo $b['id']; ?>"><?php echo e($b['button_text']); ?></a>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                    <div class="hero-nav">
                        <button class="hero-prev" aria-label="Previous slide">‹</button>
                        <div class="hero-dots" role="tablist">
                            <?php foreach ($banners as $i => $b): ?>
                                <button class="hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>" aria-label="Show slide <?php echo $i+1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <button class="hero-next" aria-label="Next slide">›</button>
                    </div>
                </div>
                <aside class="hero-side">
                    <?php if (!empty($banners)): ?>
                        <?php foreach ($banners as $idx => $s): 
                            $simg = !empty($s['image']) ? UPLOAD_URL . 'banners/' . e($s['image']) : '';
                            $sbg = !empty($s['background_color']) ? e($s['background_color']) : '#f97316';
                        ?>
                            <button class="promo-card <?php echo $idx === 0 ? 'active' : ''; ?>" data-id="<?php echo $s['id']; ?>" style="<?php if (!$simg): ?>background: linear-gradient(135deg, <?php echo $sbg; ?> 0%, <?php echo $sbg; ?>dd 100%);<?php endif; ?>">
                                <?php if ($simg): ?>
                                    <img loading="lazy" src="<?php echo $simg; ?>" alt="<?php echo e($s['title']); ?>" />
                                <?php else: ?>
                                    <div class="promo-placeholder" style="background: linear-gradient(135deg, <?php echo $sbg; ?> 0%, <?php echo $sbg; ?>dd 100%);">🎯</div>
                                <?php endif; ?>
                                <div class="promo-text">
                                    <strong><?php echo e($s['title']); ?></strong>
                                    <?php if (!empty($s['subtitle'])): ?>
                                        <small><?php echo e($s['subtitle']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </aside>
            </div>
        </section>
    <?php else: ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 20px; margin: 20px; border-radius: 8px; text-align: center;">
            <p style="margin: 0; color: #856404;"><strong>⚠️ No banners found</strong></p>
        </div>
    <?php endif; ?>

    <!-- ===== FEATURED PICKS ===== -->
    <section class="featured-section">
        <div class="container">
            <div class="section-head">
                <h2>✨ Featured Picks</h2>
                <p>Discover our handpicked selection from all categories</p>
            </div>

            <?php if (!empty($featured_items)): ?>
                <div class="featured-grid">
                    <?php foreach ($featured_items as $item): ?>
                        <?php
                            $detail_url = '';
                            if ($item['type'] === 'product') {
                                $detail_url = SITE_URL . '/product-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'property') {
                                $detail_url = SITE_URL . '/property-detail.php?id=' . $item['id'];
                            } else {
                                $detail_url = SITE_URL . '/vehicle-detail.php?id=' . $item['id'];
                            }
                        ?>
                        <a href="<?= $detail_url ?>" class="featured-card">
                            <div class="featured-star">⭐</div>
                            <div class="featured-img">
                                <?php
                                    $img_src = '';
                                    if (!empty($item['image_path'])) {
                                        if ($item['type'] === 'product') {
                                            $img_src = UPLOAD_URL . 'products/' . $item['image_path'];
                                        } elseif ($item['type'] === 'property') {
                                            $img_src = UPLOAD_URL . 'properties/' . $item['image_path'];
                                        } elseif ($item['type'] === 'vehicle') {
                                            $img_src = SITE_URL . '/' . $item['image_path'];
                                        }
                                    }
                                ?>
                                <?php if ($img_src): ?>
                                    <img src="<?= $img_src ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="featured-img-empty">
                                        <?php if ($item['type'] === 'product'): ?>📦
                                        <?php elseif ($item['type'] === 'property'): ?>🏠
                                        <?php else: ?>🚗<?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <span class="featured-type-tag">
                                    <?php if ($item['type'] === 'product'): ?>📦
                                    <?php elseif ($item['type'] === 'property'): ?>🏠
                                    <?php else: ?>🚗<?php endif; ?>
                                </span>
                            </div>
                            <div class="featured-info">
                                <h3><?= e(substr($item['name'], 0, 45)) ?></h3>
                                <?php if ($item['type'] === 'property' && !empty($item['city'])): ?>
                                    <p class="featured-meta">📍 <?= e($item['city']) ?></p>
                                <?php elseif ($item['type'] === 'vehicle' && !empty($item['year'])): ?>
                                    <p class="featured-meta">📅 <?= e($item['year']) ?></p>
                                <?php endif; ?>
                                <div class="featured-price">
                                    <?= formatCurrency($item['price']) ?>
                                    <?php if ($item['type'] === 'vehicle'): ?><small>/day</small><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-msg">
                    <i class="fas fa-search" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                    <p>No featured items available at the moment</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== ALL ITEMS SECTION ===== -->
    <section class="all-items-section">
        <div class="container">
            <div class="all-items-header">
                <h2>🛍️ All Items</h2>
                <p>Browse everything available in our marketplace</p>
            </div>

            <?php if (!empty($all_items)): ?>
                <div class="all-items-grid">
                    <?php foreach ($all_items as $item): ?>
                        <?php
                            $detail_url = '';
                            if ($item['type'] === 'product') {
                                $detail_url = SITE_URL . '/product-detail.php?id=' . $item['id'];
                            } elseif ($item['type'] === 'property') {
                                $detail_url = SITE_URL . '/property-detail.php?id=' . $item['id'];
                            } else {
                                $detail_url = SITE_URL . '/vehicle-detail.php?id=' . $item['id'];
                            }
                        ?>
                        <a href="<?= $detail_url ?>" class="all-item-card">
                            <?php if (!empty($item['featured']) && $item['featured'] == 1): ?>
                                <div class="featured-star">⭐</div>
                            <?php endif; ?>
                            <div class="all-item-img">
                                <?php
                                    $img_src = '';
                                    if (!empty($item['image_path'])) {
                                        if ($item['type'] === 'product') {
                                            $img_src = UPLOAD_URL . 'products/' . $item['image_path'];
                                        } elseif ($item['type'] === 'property') {
                                            $img_src = UPLOAD_URL . 'properties/' . $item['image_path'];
                                        } elseif ($item['type'] === 'vehicle') {
                                            $img_src = SITE_URL . '/' . $item['image_path'];
                                        }
                                    }
                                ?>
                                <?php if ($img_src): ?>
                                    <img src="<?= $img_src ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="all-img-empty">
                                        <?php if ($item['type'] === 'product'): ?>📦
                                        <?php elseif ($item['type'] === 'property'): ?>🏠
                                        <?php else: ?>🚗<?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <span class="all-type-tag">
                                    <?php if ($item['type'] === 'product'): ?>📦
                                    <?php elseif ($item['type'] === 'property'): ?>🏠
                                    <?php else: ?>🚗<?php endif; ?>
                                </span>
                            </div>
                            <div class="all-item-info">
                                <h3><?= e(substr($item['name'], 0, 45)) ?></h3>
                                <?php if ($item['type'] === 'property' && !empty($item['city'])): ?>
                                    <p class="all-meta">📍 <?= e($item['city']) ?></p>
                                <?php elseif ($item['type'] === 'vehicle' && !empty($item['year'])): ?>
                                    <p class="all-meta">📅 <?= e($item['year']) ?></p>
                                <?php endif; ?>
                                <div class="all-price">
                                    <?= formatCurrency($item['price']) ?>
                                    <?php if ($item['type'] === 'vehicle'): ?><small>/day</small><?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="all-empty">
                    <p>No items available at the moment</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <style>

        /* ===== HERO SECTION ===== */
        .hero-section { padding: 20px 0 30px; margin-bottom: 24px; background: #f9fafb; }
        .hero-wrapper { display: flex; gap: 20px; align-items: stretch; justify-content: space-between; max-width: 1400px; margin: 0 auto; padding: 0 16px; }
        .hero-main { flex: 2.5; position: relative; border-radius: 16px; overflow: hidden; min-height: 420px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); box-shadow: 0 10px 40px rgba(249,115,22,0.15); }
        .hero-slides { position: relative; height: 100%; width: 100%; }
        .hero-slide { position: absolute; inset: 0; opacity: 0; transform: translate3d(30px,0,0) scale(.98); transition: opacity .6s cubic-bezier(.22,.9,.28,1), transform .6s cubic-bezier(.22,.9,.28,1); display: block; }
        .hero-slide.active { opacity: 1; transform: translate3d(0,0,0) scale(1); z-index: 2; }
        .hero-slide img { width: 100%; height: 100%; object-fit: cover; display: block !important; filter: saturate(1.05) contrast(1.03); will-change: transform, opacity; min-height: 200px; object-position: center center; }
        .hero-slide .hero-overlay { position: absolute; inset: 0; z-index: 1; background: rgba(0,0,0,var(--ov,0.45)); transition: background .4s ease; pointer-events: none; }
        .hero-caption { position: absolute; left: 32px; bottom: 32px; z-index: 3; max-width: 65%; color: var(--txt, #ffffff); }
        .hero-caption h2 { font-size: 38px; margin: 0 0 10px; font-weight: 900; text-shadow: 0 4px 20px rgba(0,0,0,0.5); line-height: 1.1; letter-spacing: -0.5px; }
        .hero-sub { margin: 0 0 18px; color: rgba(255,255,255,0.98); font-size: 16px; line-height: 1.5; text-shadow: 0 2px 10px rgba(0,0,0,0.4); }
        .hero-cta { display: inline-block; background: #fff; color: #111827; padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 15px; text-decoration: none; box-shadow: 0 8px 24px rgba(0,0,0,0.25); transition: all .3s ease; }
        .hero-cta:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.3); background: #f97316; color: #fff; }
        .hero-nav { position: absolute; bottom: 20px; right: 20px; z-index: 4; display: flex; align-items: center; gap: 12px; }
        .hero-prev, .hero-next { background: rgba(255,255,255,0.95); border: none; padding: 10px 14px; border-radius: 12px; cursor: pointer; font-size: 22px; color: #111827; box-shadow: 0 4px 16px rgba(0,0,0,0.15); transition: all .2s ease; font-weight: 700; line-height: 1; }
        .hero-prev:hover, .hero-next:hover { transform: scale(1.1); background: #fff; box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
        .hero-prev:active, .hero-next:active { transform: scale(0.95); }
        .hero-dots { display: flex; gap: 8px; align-items: center; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 20px; backdrop-filter: blur(8px); }
        .hero-dot { width: 8px; height: 8px; background: rgba(255,255,255,0.5); border-radius: 999px; border: none; cursor: pointer; transition: all .3s cubic-bezier(.2,.9,.2,1); padding: 0; }
        .hero-dot:hover { background: rgba(255,255,255,0.8); transform: scale(1.2); }
        .hero-dot.active { width: 32px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .hero-side { flex: 1; display: flex; flex-direction: column; gap: 14px; max-height: 420px; overflow-y: auto; overflow-x: hidden; scrollbar-width: thin; scrollbar-color: #f97316 #f3f4f6; }
        .hero-side::-webkit-scrollbar { width: 6px; }
        .hero-side::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 10px; }
        .hero-side::-webkit-scrollbar-thumb { background: #f97316; border-radius: 10px; }
        .hero-side::-webkit-scrollbar-thumb:hover { background: #ea580c; }
        .promo-card { display: flex; gap: 14px; align-items: center; background: #fff; border-radius: 12px; overflow: hidden; padding: 10px; text-decoration: none; color: inherit; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: all .3s ease; border: 2px solid transparent; cursor: pointer; text-align: left; width: 100%; min-height: 95px; }
        .promo-card:hover { box-shadow: 0 8px 24px rgba(249,115,22,0.15); transform: translateX(-4px); border-color: rgba(249,115,22,0.3); }
        .promo-card.active { box-shadow: 0 12px 32px rgba(249,115,22,0.25); transform: translateX(-6px) scale(1.02); border-color: #f97316; background: linear-gradient(135deg, #fff5f0 0%, #ffffff 100%); }
        .promo-card img { width: 100px; height: 75px; object-fit: cover; border-radius: 8px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .promo-text { flex: 1; display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .promo-text strong { font-size: 14px; color: #111827; font-weight: 700; line-height: 1.3; display: block; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .promo-text small { color: #6b7280; font-size: 12px; line-height: 1.4; display: block; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .promo-placeholder { width: 100px; height: 75px; background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 32px; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .hero-media-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, var(--bg, #f97316) 0%, var(--bg, #fb923c) 100%); }

        /* ===== CONTAINER ===== */
        .container { max-width: 1400px; margin: 0 auto; padding: 0 16px; }

        /* ===== FEATURED SECTION ===== */
        .featured-section { padding: 60px 16px; background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%); position: relative; }
        .featured-section::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #f97316, #fb923c, #fdba74); }
        
        .section-head { text-align: center; margin-bottom: 45px; }
        .section-head h2 { font-size: 36px; font-weight: 900; color: #1a1a2e; margin: 0 0 10px 0; letter-spacing: -0.5px; }
        .section-head p { font-size: 17px; color: #6b7280; margin: 0; font-weight: 500; }

        .featured-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 18px; }
        
        .featured-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; transition: all .3s ease; display: flex; flex-direction: column; text-decoration: none; color: inherit; position: relative; box-shadow: 0 2px 8px rgba(249,115,22,0.08); }
        .featured-card:hover { box-shadow: 0 8px 24px rgba(249,115,22,0.2); transform: translateY(-4px); border-color: #f97316; }

        .featured-star { position: absolute; top: 8px; right: 8px; z-index: 10; font-size: 20px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }

        .featured-img { width: 100%; height: 180px; background: #f9fafb; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .featured-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .featured-card:hover .featured-img img { transform: scale(1.06); }
        .featured-img-empty { font-size: 60px; color: #d1d5db; opacity: 0.5; }
        .featured-type-tag { position: absolute; bottom: 8px; right: 8px; background: rgba(255,255,255,0.95); padding: 4px 8px; border-radius: 8px; font-size: 16px; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15); }

        .featured-info { padding: 14px; display: flex; flex-direction: column; gap: 6px; }
        .featured-info h3 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px; }
        .featured-meta { font-size: 12px; color: #6b7280; margin: 0; }
        .featured-price { font-size: 16px; font-weight: 800; color: #f97316; margin-top: 4px; }
        .featured-price small { font-size: 11px; font-weight: 600; color: #9ca3af; }

        .empty-msg { text-align: center; padding: 70px 20px; color: #9ca3af; }
        .empty-msg p { font-size: 17px; margin: 0; font-weight: 600; }

        /* ===== ALL ITEMS SECTION ===== */
        .all-items-section { padding: 60px 16px; background: #fff; }
        .all-items-header { text-align: center; margin-bottom: 40px; }
        .all-items-header h2 { font-size: 34px; font-weight: 900; color: #1a1a2e; margin: 0 0 10px 0; letter-spacing: -0.5px; }
        .all-items-header p { font-size: 16px; color: #6b7280; margin: 0; font-weight: 500; }

        .all-items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 18px; }
        
        .all-item-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; transition: all .3s ease; display: flex; flex-direction: column; text-decoration: none; color: inherit; position: relative; }
        .all-item-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.1); transform: translateY(-4px); border-color: #f97316; }

        .featured-star { position: absolute; top: 8px; right: 8px; z-index: 10; font-size: 20px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3)); }

        .all-item-img { width: 100%; height: 180px; background: #f9fafb; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .all-item-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
        .all-item-card:hover .all-item-img img { transform: scale(1.06); }
        .all-img-empty { font-size: 60px; color: #d1d5db; opacity: 0.5; }
        .all-type-tag { position: absolute; bottom: 8px; right: 8px; background: rgba(255,255,255,0.95); padding: 4px 8px; border-radius: 8px; font-size: 16px; backdrop-filter: blur(4px); box-shadow: 0 2px 6px rgba(0,0,0,0.15); }

        .all-item-info { padding: 14px; display: flex; flex-direction: column; gap: 6px; }
        .all-item-info h3 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 36px; }
        .all-meta { font-size: 12px; color: #6b7280; margin: 0; }
        .all-price { font-size: 16px; font-weight: 800; color: #f97316; margin-top: 4px; }
        .all-price small { font-size: 11px; font-weight: 600; color: #9ca3af; }

        .all-empty { text-align: center; padding: 70px 20px; color: #9ca3af; }
        .all-empty p { font-size: 17px; margin: 0; font-weight: 600; }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 900px) {
            .hero-section { padding: 16px 0 24px; margin-bottom: 20px; }
            .hero-wrapper { flex-direction: column; gap: 16px; padding: 0 12px; }
            .hero-main { order: 1; min-height: 320px; border-radius: 14px; }
            .hero-side { order: 2; flex-direction: row; overflow-x: auto; overflow-y: hidden; max-height: none; padding: 0; gap: 12px; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
            .hero-side::-webkit-scrollbar { display: none; height: 0; }
            .promo-card { min-width: 260px; flex: 0 0 auto; padding: 12px; min-height: 90px; }
            .promo-card:hover { transform: translateY(-2px); }
            .promo-card.active { transform: translateY(-4px) scale(1.02); }
            .promo-card img { width: 95px; height: 72px; }
            .promo-text strong { font-size: 13px; }
            .promo-text small { font-size: 11px; }
            .promo-placeholder { width: 95px; height: 72px; font-size: 30px; }
            .hero-slide .hero-overlay { background: rgba(0,0,0,0.55); }
            .hero-caption { left: 16px; right: 16px; bottom: 60px; max-width: calc(100% - 32px); }
            .hero-caption h2 { font-size: 22px; margin-bottom: 8px; text-shadow: 0 3px 12px rgba(0,0,0,0.6); font-weight: 900; }
            .hero-sub { font-size: 13px; margin-bottom: 14px; line-height: 1.5; text-shadow: 0 2px 8px rgba(0,0,0,0.5); }
            .hero-cta { padding: 11px 22px; font-size: 13px; font-weight: 800; box-shadow: 0 6px 18px rgba(0,0,0,0.3); }
            .hero-nav { bottom: 12px; right: 12px; gap: 8px; }
            .hero-prev, .hero-next { padding: 7px 11px; font-size: 18px; }
            
            .featured-section { padding: 45px 14px; }
            .section-head { margin-bottom: 35px; }
            .section-head h2 { font-size: 30px; }
            .section-head p { font-size: 16px; }
            .featured-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }

            .all-items-section { padding: 50px 14px; }
            .all-items-header h2 { font-size: 28px; }
            .all-items-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }
        }

        @media (max-width: 640px) {
            .hero-section { padding: 12px 0 20px; margin-bottom: 16px; }
            .hero-wrapper { gap: 12px; padding: 0 10px; }
            .hero-main { min-height: 280px; border-radius: 12px; }
            .hero-slide .hero-overlay { background: rgba(0,0,0,0.60); }
            .hero-caption { left: 14px; right: 14px; bottom: 50px; max-width: calc(100% - 28px); }
            .hero-caption h2 { font-size: 19px; margin-bottom: 6px; text-shadow: 0 3px 10px rgba(0,0,0,0.7); line-height: 1.2; font-weight: 900; }
            .hero-sub { font-size: 12px; margin-bottom: 12px; line-height: 1.5; text-shadow: 0 2px 6px rgba(0,0,0,0.6); }
            .hero-cta { padding: 10px 18px; font-size: 12px; font-weight: 800; box-shadow: 0 4px 14px rgba(0,0,0,0.35); border-radius: 10px; }
            .hero-nav { bottom: 10px; right: 10px; gap: 7px; }
            .hero-prev, .hero-next { padding: 6px 9px; font-size: 16px; border-radius: 10px; }
            .hero-dots { padding: 5px 8px; gap: 5px; }
            .hero-dot { width: 6px; height: 6px; }
            .hero-dot.active { width: 22px; }
            .promo-card { min-width: 240px; padding: 10px; min-height: 85px; }
            .promo-card img { width: 85px; height: 64px; }
            .promo-text strong { font-size: 12px; -webkit-line-clamp: 2; }
            .promo-text small { font-size: 10px; -webkit-line-clamp: 1; }
            .promo-placeholder { width: 85px; height: 64px; font-size: 28px; }
            
            .featured-section { padding: 40px 12px; }
            .section-head { margin-bottom: 30px; }
            .section-head h2 { font-size: 26px; }
            .section-head p { font-size: 15px; }
            .featured-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; }
            .featured-img { height: 160px; }
            .featured-info { padding: 12px; }
            .featured-info h3 { font-size: 13px; min-height: 34px; }

            .all-items-section { padding: 40px 12px; }
            .all-items-header { margin-bottom: 30px; }
            .all-items-header h2 { font-size: 24px; }
            .all-items-header p { font-size: 14px; }
            .all-items-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; }
            .all-item-img { height: 160px; }
            .all-item-info { padding: 12px; }
            .all-item-info h3 { font-size: 13px; min-height: 34px; }
        }

        @media (max-width: 480px) {
            .hero-section { padding: 10px 0 16px; margin-bottom: 12px; }
            .hero-wrapper { gap: 10px; padding: 0 8px; }
            .hero-main { min-height: 260px; border-radius: 10px; }
            .hero-slide .hero-overlay { background: rgba(0,0,0,0.65); }
            .hero-caption { left: 12px; right: 12px; bottom: 45px; max-width: calc(100% - 24px); }
            .hero-caption h2 { font-size: 17px; margin-bottom: 5px; text-shadow: 0 3px 10px rgba(0,0,0,0.8); line-height: 1.25; font-weight: 900; letter-spacing: -0.3px; }
            .hero-sub { font-size: 11px; margin-bottom: 10px; line-height: 1.5; text-shadow: 0 2px 6px rgba(0,0,0,0.7); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .hero-cta { padding: 9px 16px; font-size: 11px; border-radius: 8px; font-weight: 800; box-shadow: 0 4px 12px rgba(0,0,0,0.4); }
            .hero-cta:hover { transform: translateY(-2px); }
            .hero-nav { bottom: 8px; right: 8px; gap: 6px; }
            .hero-prev, .hero-next { padding: 5px 8px; font-size: 15px; border-radius: 8px; }
            .hero-dots { padding: 4px 7px; gap: 4px; }
            .hero-dot { width: 5px; height: 5px; }
            .hero-dot.active { width: 18px; }
            .promo-card { min-width: 220px; padding: 8px; border-radius: 10px; min-height: 80px; }
            .promo-card img { width: 75px; height: 56px; border-radius: 6px; }
            .promo-text strong { font-size: 11px; -webkit-line-clamp: 2; }
            .promo-text small { font-size: 9px; -webkit-line-clamp: 1; }
            .promo-placeholder { width: 75px; height: 56px; font-size: 24px; }
            
            .featured-section { padding: 35px 10px; }
            .section-head { margin-bottom: 25px; }
            .section-head h2 { font-size: 24px; margin-bottom: 8px; }
            .section-head p { font-size: 14px; }
            .featured-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .featured-card { border-radius: 10px; }
            .featured-star { font-size: 16px; top: 6px; right: 6px; }
            .featured-img { height: 140px; }
            .featured-type-tag { bottom: 6px; right: 6px; padding: 3px 6px; font-size: 14px; }
            .featured-info { padding: 10px; gap: 4px; }
            .featured-info h3 { font-size: 12px; min-height: 32px; }
            .featured-meta { font-size: 11px; }
            .featured-price { font-size: 14px; margin-top: 2px; }
            .featured-price small { font-size: 10px; }
            .empty-msg { padding: 50px 16px; }
            .empty-msg i { font-size: 36px !important; margin-bottom: 12px !important; }
            .empty-msg p { font-size: 14px; }

            .all-items-section { padding: 35px 10px; }
            .all-items-header { margin-bottom: 25px; }
            .all-items-header h2 { font-size: 22px; margin-bottom: 6px; }
            .all-items-header p { font-size: 13px; }
            .all-items-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .all-item-card { border-radius: 10px; }
            .featured-star { font-size: 16px; top: 6px; right: 6px; }
            .all-item-img { height: 140px; }
            .all-type-tag { bottom: 6px; right: 6px; padding: 3px 6px; font-size: 14px; }
            .all-item-info { padding: 10px; gap: 4px; }
            .all-item-info h3 { font-size: 12px; min-height: 32px; }
            .all-meta { font-size: 11px; }
            .all-price { font-size: 14px; margin-top: 2px; }
            .all-price small { font-size: 10px; }
        }
    </style>

    <script>
        (function(){
            const banners = <?php echo json_encode($banners); ?> || [];
            if (!banners.length) return;

            const slides = Array.from(document.querySelectorAll('.hero-slide'));
            const dots = Array.from(document.querySelectorAll('.hero-dot'));
            const prev = document.querySelector('.hero-prev');
            const nextBtn = document.querySelector('.hero-next');
            const promoCards = Array.from(document.querySelectorAll('.promo-card'));
            let current = 0;
            let timer = null;
            const delay = 5000;

            function goTo(index) {
                if (index === current) return;
                slides[current].classList.remove('active');
                slides[index].classList.add('active');
                dots.forEach(d => d.classList.remove('active'));
                if (dots[index]) dots[index].classList.add('active');
                promoCards.forEach(p => p.classList.remove('active'));
                const pid = slides[index].getAttribute('data-id');
                const activePromo = document.querySelector('.promo-card[data-id="' + pid + '"]');
                if (activePromo) activePromo.classList.add('active');
                current = index;
            }

            function next() { goTo((current + 1) % slides.length); }
            function prevSlide() { goTo((current - 1 + slides.length) % slides.length); }
            function resetTimer() { clearInterval(timer); timer = setInterval(next, delay); }

            dots.forEach((d, i) => d.addEventListener('click', () => { goTo(i); resetTimer(); }));
            if (prev) prev.addEventListener('click', () => { prevSlide(); resetTimer(); });
            if (nextBtn) nextBtn.addEventListener('click', () => { next(); resetTimer(); });

            promoCards.forEach(pc => pc.addEventListener('click', function(){
                const id = this.getAttribute('data-id');
                const idx = slides.findIndex(s => s.getAttribute('data-id') === id);
                if (idx > -1) { goTo(idx); resetTimer(); }
                fetch('<?php echo SITE_URL; ?>/api/track-banner.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ banner_id: id, action: 'click' }) }).catch(()=>{});
            }));

            resetTimer();
        })();
    </script>

<?php
require_once 'includes/footer.php';
?>
