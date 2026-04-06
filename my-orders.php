<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$user_id = currentCustomerId();

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$query = "SELECT o.*, pm.name as payment_method_name,
    (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count,
    (SELECT pi.image_path FROM order_items oi 
     LEFT JOIN product_images pi ON oi.product_id = pi.product_id AND pi.is_primary = 1 
     WHERE oi.order_id = o.id LIMIT 1) as first_product_image
    FROM orders o
    LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
    WHERE o.user_id = ?";

$params = [$user_id];
$types = 'i';

if ($status_filter !== 'all') {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'My Product Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Zuba Online Market</title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
    
    /* Container */
    .container { max-width: 1200px; margin: 0 auto; padding: 20px 16px 40px; }
    
    /* Header */
    .page-header { background: #fff; border-radius: 16px; padding: 24px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .page-title { font-size: 24px; font-weight: 900; color: #1a1a2e; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; }
    .page-title i { color: #f97316; }
    .page-subtitle { font-size: 14px; color: #6b7280; }
    
    /* Filters */
    .filters { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
    .filter-group { display: flex; flex-direction: column; gap: 6px; }
    .filter-label { font-size: 13px; font-weight: 600; color: #6b7280; }
    .filter-select { padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-weight: 600; color: #1a1a2e; background: #fff; cursor: pointer; transition: all .3s; }
    .filter-select:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
    
    /* Orders Grid */
    .orders-grid { display: grid; gap: 16px; }
    .order-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; transition: all .3s; }
    .order-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); transform: translateY(-2px); }
    
    /* Order Header */
    .order-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; }
    .order-number { font-size: 15px; font-weight: 700; color: #1a1a2e; display: flex; align-items: center; gap: 8px; }
    .order-number i { color: #f97316; font-size: 14px; }
    .order-date { font-size: 13px; color: #6b7280; margin-top: 4px; }
    
    /* Status Badge */
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .status-badge.pending_payment { background: #fef3c7; color: #92400e; }
    .status-badge.payment_submitted { background: #dbeafe; color: #1e40af; }
    .status-badge.approved { background: #d1fae5; color: #065f46; }
    .status-badge.processing { background: #ede9fe; color: #5b21b6; }
    .status-badge.shipped { background: #cffafe; color: #155e75; }
    .status-badge.delivered { background: #d1fae5; color: #065f46; }
    .status-badge.rejected { background: #fee2e2; color: #991b1b; }
    .status-badge.cancelled { background: #f3f4f6; color: #6b7280; }
    
    /* Order Body */
    .order-body { display: flex; gap: 16px; margin-bottom: 16px; }
    .order-image { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; background: #f9fafb; flex-shrink: 0; border: 1px solid #e5e7eb; }
    .order-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 36px; }
    .order-details { flex: 1; min-width: 0; }
    
    /* Order Info */
    .order-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
    .info-item { display: flex; flex-direction: column; gap: 4px; }
    .info-label { font-size: 12px; color: #6b7280; font-weight: 600; }
    .info-value { font-size: 14px; color: #1a1a2e; font-weight: 700; }
    .info-value.amount { color: #f97316; font-size: 16px; }
    
    /* Order Footer */
    .order-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #f3f4f6; }
    .shipping-info { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280; }
    .shipping-info i { color: #f97316; }
    .btn { padding: 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .3s; text-decoration: none; }
    .btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 2px 8px rgba(249,115,22,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(249,115,22,0.4); }
    
    /* Empty State */
    .empty-state { background: #fff; border-radius: 12px; padding: 60px 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
    .empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f9fafb; color: #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
    .empty-title { font-size: 20px; font-weight: 700; color: #1a1a2e; margin-bottom: 10px; }
    .empty-text { font-size: 14px; color: #6b7280; margin-bottom: 24px; }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .container { padding: 16px 12px 40px; }
        .page-header { padding: 20px 16px; }
        .page-title { font-size: 20px; }
        .filters { padding: 16px; }
        .filter-grid { grid-template-columns: 1fr; }
        .order-card { padding: 16px; }
        .order-header { flex-direction: column; gap: 10px; }
        .order-body { flex-direction: column; }
        .order-image { width: 100%; height: 200px; }
        .order-info { grid-template-columns: 1fr 1fr; }
        .order-footer { flex-direction: column; gap: 12px; align-items: stretch; }
        .btn { width: 100%; justify-content: center; }
    }
    
    @media (max-width: 640px) {
        .page-title { font-size: 18px; }
        .order-info { grid-template-columns: 1fr; }
    }
</style>
</head>
<body>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-shopping-bag"></i>
            My Product Orders
        </h1>
        <p class="page-subtitle">View and track all your product orders</p>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <form method="GET" action="" id="filterForm">
            <div class="filter-grid">
                <div class="filter-group">
                    <label class="filter-label">Order Status</label>
                    <select name="status" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="pending_payment" <?= $status_filter === 'pending_payment' ? 'selected' : '' ?>>Pending Payment</option>
                        <option value="payment_submitted" <?= $status_filter === 'payment_submitted' ? 'selected' : '' ?>>Payment Submitted</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $status_filter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Orders Grid -->
    <?php if (count($orders) > 0): ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div>
                            <div class="order-number">
                                <i class="fas fa-receipt"></i>
                                <?= e($order['order_number']) ?>
                            </div>
                            <div class="order-date">
                                <i class="far fa-calendar"></i>
                                <?= date('M d, Y - h:i A', strtotime($order['created_at'])) ?>
                            </div>
                        </div>
                        <span class="status-badge <?= $order['status'] ?>">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            <?= ucwords(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </div>
                    
                    <!-- Order Body -->
                    <div class="order-body">
                        <?php if ($order['first_product_image']): ?>
                            <img src="<?= UPLOAD_URL . 'products/' . $order['first_product_image'] ?>" alt="Order" class="order-image">
                        <?php else: ?>
                            <div class="order-image no-image">
                                <i class="fas fa-box-open"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="order-details">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">Items</span>
                                    <span class="info-value"><?= $order['items_count'] ?> Item(s)</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Payment Method</span>
                                    <span class="info-value"><?= e($order['payment_method_name'] ?? 'N/A') ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Amount</span>
                                    <span class="info-value amount"><?= formatCurrency($order['total_amount']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Footer -->
                    <div class="order-footer">
                        <div class="shipping-info">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= e($order['shipping_city']) ?>, <?= e($order['shipping_country']) ?></span>
                        </div>
                        <a href="<?= SITE_URL ?>/order-details.php?order=<?= e($order['order_number']) ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i>
                            Track Order Journey
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h2 class="empty-title">No Orders Found</h2>
            <p class="empty-text">You haven't placed any product orders yet. Start shopping to make your first order!</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i>
                Browse Products
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
