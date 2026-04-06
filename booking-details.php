<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$user_id = currentCustomerId();
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id) {
    header('Location: ' . SITE_URL . '/my-bookings.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT b.*, pm.name as payment_method_name, pm.account_number,
    v.brand, v.model, v.year, v.transmission, v.fuel_type, v.seats,
    (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = b.vehicle_id ORDER BY vi.is_primary DESC, vi.sort_order ASC LIMIT 1) as vehicle_image
    FROM bookings b
    LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param('ii', $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    header('Location: ' . SITE_URL . '/my-bookings.php');
    exit;
}

// Determine tracking steps based on status
$steps = [
    ['key' => 'payment_submitted', 'label' => 'Payment Submitted', 'icon' => 'fa-receipt'],
    ['key' => 'approved', 'label' => 'Booking Approved', 'icon' => 'fa-check-circle'],
    ['key' => 'active', 'label' => 'Vehicle Picked Up', 'icon' => 'fa-car'],
    ['key' => 'completed', 'label' => 'Booking Completed', 'icon' => 'fa-flag-checkered']
];

$current_step = 0;
foreach ($steps as $index => $step) {
    if ($booking['status'] === $step['key']) {
        $current_step = $index;
        break;
    }
}

$page_title = 'Booking Details';
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
        
        .header { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header-content { display: flex; align-items: center; gap: 16px; padding: 14px 16px; max-width: 900px; margin: 0 auto; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; }
        .header-title { flex: 1; font-size: 18px; font-weight: 700; color: #1a1a2e; }
        
        .container { max-width: 900px; margin: 0 auto; padding: 20px 16px 40px; }
        
        .card { background: #fff; border-radius: 16px; padding: 24px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
        
        .booking-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px dashed #e5e7eb; }
        .booking-number { font-size: 16px; font-weight: 800; color: #1a1a2e; margin-bottom: 6px; }
        .booking-date { font-size: 13px; color: #9ca3af; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 24px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-badge.payment_submitted { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; border: 1px solid #93c5fd; }
        .status-badge.approved { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border: 1px solid #6ee7b7; }
        .status-badge.active { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; border: 1px solid #fcd34d; }
        .status-badge.completed { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #3730a3; border: 1px solid #a5b4fc; }
        .status-badge.rejected { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border: 1px solid #fca5a5; }
        .status-badge.cancelled { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #6b7280; border: 1px solid #d1d5db; }
        
        .tracking-timeline { position: relative; padding: 20px 0; }
        .timeline-step { display: flex; gap: 16px; position: relative; padding-bottom: 32px; }
        .timeline-step:last-child { padding-bottom: 0; }
        .timeline-step:not(:last-child)::before { content: ''; position: absolute; left: 19px; top: 48px; width: 2px; height: calc(100% - 32px); background: #e5e7eb; }
        .timeline-step.active:not(:last-child)::before { background: linear-gradient(180deg, #f97316 0%, #e5e7eb 100%); }
        .timeline-step.completed:not(:last-child)::before { background: #10b981; }
        
        .timeline-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; border: 3px solid #e5e7eb; background: #fff; position: relative; z-index: 1; }
        .timeline-step.active .timeline-icon { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border-color: #f97316; box-shadow: 0 4px 12px rgba(249,115,22,0.4); animation: pulse 2s infinite; }
        .timeline-step.completed .timeline-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; border-color: #10b981; }
        
        @keyframes pulse { 0%, 100% { box-shadow: 0 4px 12px rgba(249,115,22,0.4); } 50% { box-shadow: 0 4px 20px rgba(249,115,22,0.6); } }
        
        .timeline-content { flex: 1; }
        .timeline-label { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
        .timeline-step.active .timeline-label { color: #f97316; }
        .timeline-step.completed .timeline-label { color: #10b981; }
        .timeline-time { font-size: 12px; color: #9ca3af; }
        .timeline-desc { font-size: 13px; color: #6b7280; margin-top: 6px; line-height: 1.5; }
        
        .vehicle-card { display: flex; gap: 16px; }
        .vehicle-image { width: 120px; height: 120px; border-radius: 12px; object-fit: cover; background: #f9fafb; flex-shrink: 0; border: 2px solid #e5e7eb; }
        .vehicle-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 40px; }
        .vehicle-info { flex: 1; }
        .vehicle-name { font-size: 17px; font-weight: 800; color: #1a1a2e; margin-bottom: 10px; }
        .vehicle-meta { display: flex; gap: 12px; flex-wrap: wrap; }
        .vehicle-meta span { display: flex; align-items: center; gap: 5px; padding: 6px 12px; background: #f9fafb; border-radius: 8px; font-size: 12px; font-weight: 600; color: #6b7280; }
        .vehicle-meta i { color: #f97316; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .info-item { }
        .info-label { font-size: 11px; color: #9ca3af; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
        .info-value { font-size: 14px; font-weight: 800; color: #1a1a2e; }
        
        .total-amount { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #fcd34d; border-radius: 12px; padding: 16px; text-align: center; margin-top: 20px; }
        .total-label { font-size: 12px; color: #92400e; font-weight: 600; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .total-value { font-size: 28px; font-weight: 900; color: #92400e; }
        
        .payment-proof { margin-top: 16px; }
        .payment-proof img { width: 100%; max-width: 400px; border-radius: 12px; border: 2px solid #e5e7eb; }
        
        @media (max-width: 768px) {
            .container { padding: 16px 12px 40px; }
            .card { padding: 20px 16px; }
            .booking-header { flex-direction: column; gap: 12px; }
            .vehicle-card { flex-direction: column; }
            .vehicle-image { width: 100%; height: 200px; }
            .info-grid { grid-template-columns: 1fr; gap: 12px; }
            .timeline-step { gap: 12px; }
            .timeline-icon { width: 38px; height: 38px; font-size: 14px; }
            .timeline-step:not(:last-child)::before { left: 18px; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/my-bookings.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">Booking Details</div>
    </div>
</header>

<div class="container">
    <div class="card">
        <div class="booking-header">
            <div>
                <div class="booking-number"><?= htmlspecialchars($booking['booking_number']) ?></div>
                <div class="booking-date">Booked on <?= date('M d, Y', strtotime($booking['created_at'])) ?></div>
            </div>
            <span class="status-badge <?= $booking['status'] ?>">
                <i class="fas fa-circle"></i>
                <?= ucwords(str_replace('_', ' ', $booking['status'])) ?>
            </span>
        </div>
        
        <div class="tracking-timeline">
            <?php foreach ($steps as $index => $step): ?>
                <?php 
                $is_completed = $index < $current_step;
                $is_active = $index === $current_step;
                $class = $is_completed ? 'completed' : ($is_active ? 'active' : '');
                ?>
                <div class="timeline-step <?= $class ?>">
                    <div class="timeline-icon">
                        <i class="fas <?= $step['icon'] ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-label"><?= $step['label'] ?></div>
                        <?php if ($is_completed || $is_active): ?>
                            <div class="timeline-time">
                                <?php if ($step['key'] === 'payment_submitted'): ?>
                                    <?= date('M d, Y - h:i A', strtotime($booking['created_at'])) ?>
                                <?php elseif ($step['key'] === 'approved' && $booking['approved_at']): ?>
                                    <?= date('M d, Y - h:i A', strtotime($booking['approved_at'])) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($is_active): ?>
                            <div class="timeline-desc">
                                <?php if ($step['key'] === 'payment_submitted'): ?>
                                    Your payment is being verified by our team. This usually takes 24-48 hours.
                                <?php elseif ($step['key'] === 'approved'): ?>
                                    Your booking has been approved! Please proceed to pickup the vehicle.
                                <?php elseif ($step['key'] === 'active'): ?>
                                    Enjoy your ride! Please return the vehicle on time.
                                <?php elseif ($step['key'] === 'completed'): ?>
                                    Thank you for choosing us! We hope you had a great experience.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="vehicle-card">
            <?php if ($booking['vehicle_image']): ?>
                <img src="<?= SITE_URL . '/' . htmlspecialchars($booking['vehicle_image']) ?>" alt="<?= htmlspecialchars($booking['vehicle_name']) ?>" class="vehicle-image">
            <?php else: ?>
                <div class="vehicle-image no-image">
                    <i class="fas fa-car"></i>
                </div>
            <?php endif; ?>
            
            <div class="vehicle-info">
                <div class="vehicle-name"><?= htmlspecialchars($booking['vehicle_name']) ?></div>
                <div class="vehicle-meta">
                    <span><i class="fas fa-cog"></i> <?= ucfirst($booking['transmission']) ?></span>
                    <span><i class="fas fa-gas-pump"></i> <?= ucfirst($booking['fuel_type']) ?></span>
                    <span><i class="fas fa-users"></i> <?= $booking['seats'] ?> Seats</span>
                </div>
            </div>
        </div>
        
        <div class="info-grid" style="margin-top: 20px;">
            <div class="info-item">
                <div class="info-label">Start Date</div>
                <div class="info-value"><?= date('M d, Y', strtotime($booking['start_date'])) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">End Date</div>
                <div class="info-value"><?= date('M d, Y', strtotime($booking['end_date'])) ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Rental Days</div>
                <div class="info-value"><?= $booking['rental_days'] ?> Day(s)</div>
            </div>
            <div class="info-item">
                <div class="info-label">Rate Type</div>
                <div class="info-value"><?= ucfirst($booking['rate_type']) ?></div>
            </div>
            <?php if ($booking['pickup_location']): ?>
            <div class="info-item">
                <div class="info-label">Pickup Location</div>
                <div class="info-value"><?= htmlspecialchars($booking['pickup_location']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($booking['dropoff_location']): ?>
            <div class="info-item">
                <div class="info-label">Dropoff Location</div>
                <div class="info-value"><?= htmlspecialchars($booking['dropoff_location']) ?></div>
            </div>
            <?php endif; ?>
            <div class="info-item">
                <div class="info-label">Payment Method</div>
                <div class="info-value"><?= htmlspecialchars($booking['payment_method_name']) ?></div>
            </div>
            <?php if ($booking['account_number']): ?>
            <div class="info-item">
                <div class="info-label">Account Number</div>
                <div class="info-value"><?= htmlspecialchars($booking['account_number']) ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($booking['payment_proof']): ?>
        <div class="payment-proof">
            <div class="info-label" style="margin-bottom: 10px;">Payment Proof</div>
            <img src="<?= UPLOAD_URL . 'payment_proofs/' . htmlspecialchars(basename($booking['payment_proof'])) ?>" alt="Payment Proof">
        </div>
        <?php endif; ?>
        
        <div class="total-amount">
            <div class="total-label">Total Amount</div>
            <div class="total-value">RWF <?= number_format($booking['total_amount']) ?></div>
        </div>
    </div>
</div>

</body>
</html>
