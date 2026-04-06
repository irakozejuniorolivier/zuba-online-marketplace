<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$user_id = currentCustomerId();

// Fetch all wishlist items for the user
$query = "SELECT w.*, 
    CASE 
        WHEN w.item_type = 'product' THEN p.name
        WHEN w.item_type = 'property' THEN pr.title
        WHEN w.item_type = 'vehicle' THEN CONCAT(v.brand, ' ', v.model, ' ', v.year)
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
    END as item_image,
    CASE 
        WHEN w.item_type = 'product' THEN p.slug
        WHEN w.item_type = 'property' THEN pr.slug
        WHEN w.item_type = 'vehicle' THEN v.slug
    END as item_slug,
    CASE 
        WHEN w.item_type = 'product' THEN p.status
        WHEN w.item_type = 'property' THEN pr.status
        WHEN w.item_type = 'vehicle' THEN v.status
    END as item_status
    FROM wishlist w
    LEFT JOIN products p ON w.item_type = 'product' AND w.item_id = p.id
    LEFT JOIN properties pr ON w.item_type = 'property' AND w.item_id = pr.id
    LEFT JOIN vehicles v ON w.item_type = 'vehicle' AND w.item_id = v.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$wishlist_items = [];
while ($row = $result->fetch_assoc()) {
    $wishlist_items[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | Zuba Online Market</title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; color: #1a1a2e; }
    
    .header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 20px; }
    .header-content { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 16px; }
    .btn-back { width: 40px; height: 40px; background: #f5f5f5; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #1a1a2e; text-decoration: none; cursor: pointer; }
    .btn-back:hover { background: #e5e7eb; }
    .header-title h1 { font-size: 24px; font-weight: 700; color: #1a1a2e; margin: 0; }
    
    .container { max-width: 1200px; margin: 0 auto; padding: 24px 20px; }
    
    .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    
    .wishlist-card { background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; position: relative; transition: all 0.3s; text-decoration: none; color: inherit; display: block; }
    .wishlist-card:hover { border-color: #f97316; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-2px); }
    
    .item-image { width: 100%; height: 200px; object-fit: cover; background: #f3f4f6; }
    .no-image { width: 100%; height: 200px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 48px; }
    
    .item-content { padding: 16px; }
    .item-type { display: inline-block; padding: 4px 8px; background: #f3f4f6; color: #6b7280; font-size: 11px; font-weight: 600; text-transform: uppercase; border-radius: 4px; margin-bottom: 8px; }
    .item-name { font-size: 16px; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .item-price { font-size: 18px; font-weight: 700; color: #f97316; margin-bottom: 12px; }
    .item-date { font-size: 12px; color: #9ca3af; margin-bottom: 12px; }
    
    .item-actions { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-remove { width: 36px; height: 36px; background: #fee2e2; color: #dc2626; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
    .btn-remove:hover { background: #fecaca; transform: scale(1.1); }
    
    .badge-status { position: absolute; top: 12px; right: 12px; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    .badge-status.active, .badge-status.available { background: #d1fae5; color: #065f46; }
    .badge-status.inactive, .badge-status.sold, .badge-status.rented { background: #fee2e2; color: #991b1b; }
    .badge-status.out_of_stock { background: #fef3c7; color: #92400e; }
    
    .empty { text-align: center; padding: 80px 20px; background: #fff; border-radius: 8px; }
    .empty i { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }
    .empty h3 { font-size: 18px; color: #1a1a2e; margin-bottom: 8px; }
    .empty p { font-size: 14px; color: #6b7280; margin-bottom: 20px; }
    .btn-shop { padding: 10px 20px; background: #f97316; color: #fff; border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-block; }
    .btn-shop:hover { background: #ea580c; }
    
    /* Modal */
    .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
    .modal.show { display: flex; }
    .modal-content { background: #fff; border-radius: 12px; padding: 24px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalSlideIn 0.3s ease; }
    @keyframes modalSlideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .modal-icon { width: 48px; height: 48px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .modal-title { font-size: 20px; font-weight: 700; color: #1a1a2e; }
    .modal-body { color: #6b7280; font-size: 14px; margin-bottom: 24px; line-height: 1.6; }
    .modal-actions { display: flex; gap: 12px; }
    .btn-cancel { flex: 1; padding: 10px 20px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-cancel:hover { background: #e5e7eb; }
    .btn-confirm { flex: 1; padding: 10px 20px; background: #dc2626; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
    .btn-confirm:hover { background: #b91c1c; }
    
    @media (max-width: 640px) {
        .header { padding: 16px; }
        .header-title h1 { font-size: 20px; }
        .container { padding: 16px; }
        .wishlist-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .item-image, .no-image { height: 140px; }
        .item-content { padding: 12px; }
        .item-name { font-size: 14px; }
        .item-price { font-size: 16px; }
        .item-actions { flex-direction: row; justify-content: flex-end; }
        .btn-remove { width: 100%; height: 32px; max-width: 120px; }
    }
</style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/profile.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">
            <h1>My Wishlist</h1>
        </div>
    </div>
</header>

<div class="container">
    <?php if (count($wishlist_items) > 0): ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlist_items as $item): ?>
                <?php
                $detail_page = '';
                if ($item['item_type'] == 'product') {
                    $detail_page = 'product-detail.php?slug=' . $item['item_slug'];
                } elseif ($item['item_type'] == 'property') {
                    $detail_page = 'property-detail.php?slug=' . $item['item_slug'];
                } elseif ($item['item_type'] == 'vehicle') {
                    $detail_page = 'vehicle-detail.php?slug=' . $item['item_slug'];
                }
                ?>
                <a href="<?= SITE_URL ?>/<?= $detail_page ?>" class="wishlist-card">
                    <?php if ($item['item_status']): ?>
                        <span class="badge-status <?= $item['item_status'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $item['item_status'])) ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($item['item_image']): ?>
                        <img src="<?php 
                            if ($item['item_type'] === 'product') {
                                echo UPLOAD_URL . 'products/' . e($item['item_image']);
                            } elseif ($item['item_type'] === 'property') {
                                echo UPLOAD_URL . 'properties/' . e($item['item_image']);
                            } elseif ($item['item_type'] === 'vehicle') {
                                echo UPLOAD_URL . 'vehicles/' . e($item['item_image']);
                            }
                        ?>" alt="<?= e($item['item_name']) ?>" class="item-image">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="item-content">
                        <span class="item-type"><?= e($item['item_type']) ?></span>
                        <div class="item-name"><?= e($item['item_name']) ?></div>
                        <div class="item-price"><?= formatCurrency($item['item_price']) ?></div>
                        <div class="item-date">Added <?= date('M d, Y', strtotime($item['created_at'])) ?></div>
                        
                        <div class="item-actions">
                            <button class="btn-remove" onclick="event.preventDefault(); event.stopPropagation(); showRemoveModal(<?= $item['id'] ?>, '<?= $item['item_type'] ?>', <?= $item['item_id'] ?>);">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <i class="fas fa-heart"></i>
            <h3>Your Wishlist is Empty</h3>
            <p>Start adding items you love to your wishlist</p>
            <a href="<?= SITE_URL ?>/index.php" class="btn-shop">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div id="removeModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="modal-title">Remove from Wishlist?</div>
        </div>
        <div class="modal-body">
            Are you sure you want to remove this item from your wishlist? This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="hideRemoveModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmRemove()">Remove</button>
        </div>
    </div>
</div>

<script>
let itemToRemove = null;

function showRemoveModal(wishlistId, itemType, itemId) {
    itemToRemove = { wishlistId, itemType, itemId };
    document.getElementById('removeModal').classList.add('show');
}

function hideRemoveModal() {
    document.getElementById('removeModal').classList.remove('show');
    itemToRemove = null;
}

function confirmRemove() {
    if (!itemToRemove) return;
    
    fetch('<?= SITE_URL ?>/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            action: 'remove', 
            item_type: itemToRemove.itemType, 
            item_id: itemToRemove.itemId 
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            hideRemoveModal();
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(err => {
        console.error(err);
        hideRemoveModal();
        alert('An error occurred');
    });
}

// Close modal when clicking outside
document.getElementById('removeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRemoveModal();
    }
});
</script>

</body>
</html>
