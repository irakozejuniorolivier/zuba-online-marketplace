<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$user_id = currentCustomerId();
$order_id = $_GET['order_id'] ?? 0;

if (empty($order_id)) {
    $_SESSION['error_message'] = 'Order not found';
    header('Location: ' . SITE_URL . '/property-orders.php');
    exit;
}

// Fetch order details
$query = "
    SELECT po.*, pm.name as payment_method_name, pm.account_number, pm.instructions,
    p.city, p.country, p.address as property_address,
    (SELECT image_path FROM property_images WHERE property_id = po.property_id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as image_path
    FROM property_orders po
    LEFT JOIN payment_methods pm ON po.payment_method_id = pm.id
    LEFT JOIN properties p ON po.property_id = p.id
    WHERE po.id = ? AND po.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error_message'] = 'Order not found';
    header('Location: ' . SITE_URL . '/property-orders.php');
    exit;
}

// Order status steps for property orders
$status_steps = [
    'payment_submitted' => ['label' => 'Payment Submitted', 'icon' => 'fa-file-invoice-dollar', 'color' => '#3b82f6'],
    'approved' => ['label' => 'Approved', 'icon' => 'fa-check-circle', 'color' => '#10b981'],
    'completed' => ['label' => 'Completed', 'icon' => 'fa-check-double', 'color' => '#059669']
];

$rejected_cancelled = ['rejected', 'cancelled'];
$current_status = $order['status'];
$is_rejected_cancelled = in_array($current_status, $rejected_cancelled);

$page_title = 'Property Order Details';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= e($order['order_number']) ?> | Zuba Online Market</title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
    
    .simple-header { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 16px 20px; }
    .simple-header-content { max-width: 900px; margin: 0 auto; display: flex; align-items: center; gap: 16px; }
    .btn-back { width: 40px; height: 40px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #1a1a2e; text-decoration: none; transition: all .3s; flex-shrink: 0; }
    .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; }
    .header-info { flex: 1; min-width: 0; }
    .header-info h1 { font-size: 20px; font-weight: 800; color: #1a1a2e; margin: 0 0 4px; }
    .header-info p { font-size: 13px; color: #6b7280; margin: 0; }
    .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 16px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .status-badge.payment_submitted { background: #dbeafe; color: #1e40af; }
    .status-badge.approved { background: #d1fae5; color: #065f46; }
    .status-badge.completed { background: #fef3c7; color: #92400e; }
    .status-badge.rejected { background: #fee2e2; color: #991b1b; }
    .status-badge.cancelled { background: #f3f4f6; color: #374151; }
    
    .container { max-width: 900px; margin: 0 auto; padding: 24px 20px; }
        
    .order-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
    
    .card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 16px; }
    .card-header { margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #e5e7eb; }
    .card-title { font-size: 16px; font-weight: 800; color: #1a1a2e; margin: 0; display: flex; align-items: center; gap: 8px; }
    .card-title i { color: #f97316; font-size: 18px; }
    
    .tracking-container { position: relative; padding: 10px 0; }
    .tracking-step { display: flex; gap: 16px; position: relative; padding-bottom: 32px; }
    .tracking-step:last-child { padding-bottom: 0; }
    .tracking-step:not(:last-child)::before { content: ''; position: absolute; left: 19px; top: 44px; bottom: 0; width: 3px; background: #e5e7eb; }
    .tracking-step.completed:not(:last-child)::before { background: linear-gradient(180deg, #10b981 0%, #e5e7eb 100%); }
    
    .step-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; border: 3px solid #e5e7eb; background: #fff; position: relative; z-index: 1; transition: all .3s; }
    .tracking-step.completed .step-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border-color: #10b981; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
    .tracking-step.active .step-icon { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border-color: #f97316; box-shadow: 0 4px 12px rgba(249,115,22,0.3); animation: pulse 2s infinite; }
    .tracking-step.rejected .step-icon { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; border-color: #ef4444; box-shadow: 0 4px 12px rgba(239,68,68,0.3); }
    
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    
    .step-content { flex: 1; min-width: 0; }
    .step-label { font-size: 15px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
    .step-time { font-size: 12px; color: #6b7280; margin: 0 0 6px; display: flex; align-items: center; gap: 4px; }
    .step-time i { font-size: 11px; }
    .step-desc { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5; }
    .tracking-step.active .step-label { color: #f97316; }
    .tracking-step.rejected .step-label { color: #ef4444; }
    
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .info-item { background: #f9fafb; padding: 12px 16px; border-radius: 10px; border: 1px solid #e5e7eb; }
    .info-label { font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { font-size: 14px; color: #1a1a2e; font-weight: 700; word-break: break-word; }
    
    .property-card { display: flex; gap: 16px; padding: 16px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; }
    .property-img { width: 120px; height: 120px; border-radius: 10px; overflow: hidden; background: #fff; flex-shrink: 0; border: 1px solid #e5e7eb; }
    .property-img img { width: 100%; height: 100%; object-fit: cover; }
    .property-img .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 48px; }
    .property-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .property-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; width: fit-content; }
    .property-badge.rent { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .property-name { font-size: 16px; font-weight: 700; color: #1a1a2e; margin: 0 0 8px; line-height: 1.3; }
    .property-location { display: flex; align-items: center; gap: 6px; color: #6b7280; font-size: 13px; margin-bottom: 8px; }
    .property-location i { color: #f97316; font-size: 12px; }
    .property-price { font-size: 18px; font-weight: 800; color: #f97316; }
    
    .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; }
    .summary-row.total { border-top: 2px solid #f3f4f6; margin-top: 8px; padding-top: 12px; font-size: 17px; font-weight: 900; background: #fff5f0; margin-left: -16px; margin-right: -16px; padding-left: 16px; padding-right: 16px; border-radius: 0 0 10px 10px; }
    .summary-row .label { color: #6b7280; font-weight: 600; }
    .summary-row .value { color: #1a1a2e; font-weight: 700; }
    .summary-row.total .value { color: #f97316; }
    
    .payment-proof-container { margin-top: 16px; }
    .payment-proof-label { display: block; font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .payment-proof { width: 100%; max-width: 100%; border-radius: 10px; border: 2px solid #e5e7eb; cursor: pointer; transition: all .3s; }
    .payment-proof:hover { transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    
    .note-box { background: #fff5f0; border: 1px solid rgba(249,115,22,0.2); border-radius: 10px; padding: 12px; margin-top: 12px; }
    .note-box strong { display: block; font-size: 12px; color: #1a1a2e; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    .note-box p { font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5; }
    
    @media (max-width: 640px) {
        .simple-header { padding: 12px; }
        .simple-header-content { gap: 12px; }
        .btn-back { width: 36px; height: 36px; }
        .header-info h1 { font-size: 16px; }
        .header-info p { font-size: 11px; }
        .status-badge { font-size: 10px; padding: 4px 10px; }
        .container { padding: 16px 12px; }
        
        .card { padding: 16px; margin-bottom: 16px; border-radius: 12px; }
        .card-header { margin-bottom: 16px; padding-bottom: 12px; }
        .card-title { font-size: 16px; }
        .card-title i { font-size: 18px; }
        
        .tracking-step { gap: 12px; padding-bottom: 26px; }
        .step-icon { width: 36px; height: 36px; font-size: 14px; border-width: 2px; }
        .tracking-step:not(:last-child)::before { left: 17px; top: 40px; width: 2px; }
        .step-label { font-size: 14px; }
        .step-time { font-size: 11px; margin-bottom: 4px; }
        .step-desc { font-size: 12px; }
        
        .property-card { flex-direction: column; padding: 12px; }
        .property-img { width: 100%; height: 200px; }
        .property-img .no-img { font-size: 64px; }
        .property-name { font-size: 15px; }
        .property-location { font-size: 12px; }
        .property-price { font-size: 16px; }
        
        .info-grid { grid-template-columns: 1fr; gap: 10px; }
        .info-item { padding: 10px 12px; }
        .info-label { font-size: 11px; }
        .info-value { font-size: 13px; }
        
        .summary-row { padding: 8px 0; font-size: 13px; }
        .summary-row.total { font-size: 15px; padding-top: 10px; margin-left: -16px; margin-right: -16px; padding-left: 16px; padding-right: 16px; }
        
        .note-box { padding: 10px; margin-top: 10px; }
        .note-box strong { font-size: 11px; margin-bottom: 4px; }
        .note-box p { font-size: 12px; }
        
        .payment-proof-label { font-size: 11px; margin-bottom: 6px; }
    }
</style>
</head>
<body>

<header class="simple-header">
    <div class="simple-header-content">
        <a href="<?= SITE_URL ?>/property-orders.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-info">
            <h1>Order #<?= e($order['order_number']) ?></h1>
            <p><?= date('M d, Y \\a\\t h:i A', strtotime($order['created_at'])) ?></p>
        </div>
        <span class="status-badge <?= $current_status ?>">
            <i class="fas <?= $is_rejected_cancelled ? 'fa-times-circle' : ($status_steps[$current_status]['icon'] ?? 'fa-clock') ?>"></i>
            <?= $is_rejected_cancelled ? ucfirst($current_status) : ($status_steps[$current_status]['label'] ?? ucfirst(str_replace('_', ' ', $current_status))) ?>
        </span>
    </div>
</header>

<div class="container">
    <div class="order-grid">
        <!-- Order Tracking -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-route"></i>
                    Order Tracking
                </h2>
            </div>
                
            <div class="tracking-container">
                <?php if ($is_rejected_cancelled): ?>
                    <!-- Show rejection/cancellation -->
                    <div class="tracking-step rejected">
                        <div class="step-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="step-content">
                            <h3 class="step-label">Order <?= ucfirst($current_status) ?></h3>
                            <p class="step-time"><?= date('M d, Y \\a\\t h:i A', strtotime($order['updated_at'])) ?></p>
                            <p class="step-desc">
                                <?= $current_status === 'rejected' ? 'Your order has been rejected. Please contact support for more information.' : 'This order has been cancelled.' ?>
                            </p>
                            <?php if ($order['admin_note']): ?>
                                <div class="note-box">
                                    <strong>Admin Note:</strong>
                                    <p><?= nl2br(e($order['admin_note'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Normal flow -->
                    <?php
                    $steps_order = ['payment_submitted', 'approved', 'completed'];
                    $current_index = array_search($current_status, $steps_order);
                    
                    foreach ($steps_order as $index => $step):
                        $is_completed = $index < $current_index;
                        $is_active = $step === $current_status;
                        $step_info = $status_steps[$step];
                    ?>
                        <div class="tracking-step <?= $is_completed ? 'completed' : '' ?> <?= $is_active ? 'active' : '' ?>">
                            <div class="step-icon">
                                <i class="fas <?= $step_info['icon'] ?>"></i>
                            </div>
                            <div class="step-content">
                                <h3 class="step-label"><?= $step_info['label'] ?></h3>
                                <?php if ($is_completed || $is_active): ?>
                                    <p class="step-time">
                                        <i class="fas fa-clock"></i>
                                        <?php
                                        if ($step === 'approved' && $order['approved_at']) {
                                            echo date('M d, Y \\a\\t h:i A', strtotime($order['approved_at']));
                                        } elseif ($is_active) {
                                            echo date('M d, Y \\a\\t h:i A', strtotime($order['updated_at']));
                                        } else {
                                            echo date('M d, Y \\a\\t h:i A', strtotime($order['created_at']));
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <p class="step-desc">
                                    <?php
                                    $descriptions = [
                                        'payment_submitted' => 'Payment proof has been submitted and is under review.',
                                        'approved' => 'Payment verified and order approved. Property is ready for you.',
                                        'completed' => 'Order successfully completed.'
                                    ];
                                    echo $descriptions[$step] ?? '';
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Property Details -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-building"></i>
                    Property Details
                </h2>
            </div>
            
            <div class="property-card">
                <div class="property-img">
                    <?php if ($order['image_path']): ?>
                        <img src="<?= UPLOAD_URL . 'properties/' . $order['image_path'] ?>" alt="<?= e($order['property_title']) ?>">
                    <?php else: ?>
                        <div class="no-img"><i class="fas fa-building"></i></div>
                    <?php endif; ?>
                </div>
                <div class="property-info">
                    <span class="property-badge <?= $order['order_type'] === 'rent' ? 'rent' : '' ?>">
                        <i class="fas fa-<?= $order['order_type'] === 'rent' ? 'key' : 'tag' ?>"></i>
                        For <?= ucfirst($order['order_type']) ?>
                    </span>
                    <h3 class="property-name"><?= e($order['property_title']) ?></h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= e($order['city'] . ', ' . $order['country']) ?></span>
                    </div>
                    <div class="property-price"><?= formatCurrency($order['amount']) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Order Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-file-alt"></i>
                    Order Information
                </h2>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Order Number</div>
                    <div class="info-value"><?= e($order['order_number']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Order Date</div>
                    <div class="info-value"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Order Type</div>
                    <div class="info-value"><?= ucfirst($order['order_type']) ?></div>
                </div>
                <?php if ($order['order_type'] === 'rent'): ?>
                    <div class="info-item">
                        <div class="info-label">Rent Duration</div>
                        <div class="info-value"><?= $order['rent_duration'] ?> <?= ucfirst($order['rent_period']) ?>(s)</div>
                    </div>
                <?php endif; ?>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-badge <?= $order['status'] ?>">
                            <i class="fas fa-circle" style="font-size: 6px;"></i>
                            <?= ucwords(str_replace('_', ' ', $order['status'])) ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['customer_note']): ?>
                <div class="note-box">
                    <strong>Your Note:</strong>
                    <p><?= nl2br(e($order['customer_note'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Summary -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Order Summary
                </h2>
            </div>
            
            <?php if ($order['order_type'] === 'rent'): ?>
                <div class="summary-row">
                    <span class="label">Price per <?= ucfirst($order['rent_period']) ?>:</span>
                    <span class="value"><?= formatCurrency($order['amount'] / $order['rent_duration']) ?></span>
                </div>
                <div class="summary-row">
                    <span class="label">Duration:</span>
                    <span class="value"><?= $order['rent_duration'] ?> <?= ucfirst($order['rent_period']) ?>(s)</span>
                </div>
            <?php endif; ?>
            
            <div class="summary-row total">
                <span class="label">Total Amount:</span>
                <span class="value"><?= formatCurrency($order['amount']) ?></span>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-credit-card"></i>
                    Payment Information
                </h2>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= e($order['payment_method_name'] ?? 'N/A') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Number</div>
                    <div class="info-value"><?= e($order['account_number'] ?? 'N/A') ?></div>
                </div>
            </div>
            
            <?php if ($order['payment_proof']): ?>
                <div class="payment-proof-container">
                    <span class="payment-proof-label">Payment Proof</span>
                    <img src="<?= UPLOAD_URL . 'payment_proofs/' . $order['payment_proof'] ?>" alt="Payment Proof" class="payment-proof" onclick="window.open(this.src, '_blank')">
                </div>
            <?php endif; ?>
            
            <?php if ($order['admin_note'] && !$is_rejected_cancelled): ?>
                <div class="note-box">
                    <strong>Admin Note:</strong>
                    <p><?= nl2br(e($order['admin_note'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
