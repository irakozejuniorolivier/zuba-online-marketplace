<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (!isCustomerLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$user_id = currentCustomerId();
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if (!$booking_id) {
    header('Location: ' . SITE_URL . '/vehicles.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT b.*, pm.name as payment_method_name, pm.account_number, pm.instructions,
    v.brand, v.model, v.year, v.transmission, v.fuel_type, v.seats
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
    header('Location: ' . SITE_URL . '/vehicles.php');
    exit;
}

$image_path = null;
$stmt = $conn->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1");
$stmt->bind_param('i', $booking['vehicle_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $image_path = $row['image_path'];
}
$stmt->close();

$page_title = 'Booking Confirmation';
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
        
        .container { max-width: 800px; margin: 0 auto; padding: 20px 16px 40px; }
        
        .success-card { background: #fff; border-radius: 16px; padding: 40px 30px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center; border: 1px solid #e5e7eb; }
        .success-icon { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; box-shadow: 0 8px 24px rgba(16,185,129,0.4); }
        .success-title { font-size: 24px; font-weight: 900; color: #1a1a2e; margin-bottom: 10px; }
        .success-message { font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 20px; }
        .booking-number { display: inline-flex; align-items: center; gap: 8px; background: #f9fafb; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 700; color: #1a1a2e; border: 1px solid #e5e7eb; }
        .booking-number i { color: #f97316; }
        
        .section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .section-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; }
        .section-title i { color: #f97316; font-size: 18px; }
        
        .vehicle-info { display: flex; gap: 16px; align-items: start; }
        .vehicle-image { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; background: #f9fafb; flex-shrink: 0; border: 1px solid #e5e7eb; }
        .vehicle-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 36px; }
        .vehicle-details { flex: 1; min-width: 0; }
        .vehicle-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .vehicle-name { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; line-height: 1.3; }
        .vehicle-meta { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 13px; flex-wrap: wrap; }
        .vehicle-meta span { display: flex; align-items: center; gap: 4px; }
        .vehicle-meta i { color: #f97316; font-size: 12px; }
        
        .info-grid { display: grid; gap: 12px; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
        .info-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        .info-label { font-size: 14px; color: #6b7280; font-weight: 600; }
        .info-value { font-size: 14px; color: #1a1a2e; font-weight: 700; text-align: right; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status-badge.payment_submitted { background: #dbeafe; color: #1e40af; }
        .status-badge.approved { background: #d1fae5; color: #065f46; }
        .status-badge.rejected { background: #fee2e2; color: #991b1b; }
        
        .payment-proof-img { width: 100%; max-width: 400px; border-radius: 10px; border: 1px solid #e5e7eb; margin-top: 12px; }
        
        .alert-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; margin-bottom: 20px; }
        .alert-box-title { font-size: 14px; font-weight: 700; color: #1e40af; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .alert-box-title i { font-size: 16px; }
        .alert-box-text { font-size: 13px; color: #1e40af; line-height: 1.6; }
        
        .btn-group { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; }
        .btn { padding: 14px 20px; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all .3s; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-secondary { background: #fff; color: #1a1a2e; border: 2px solid #e5e7eb; }
        .btn-secondary:hover { background: #f9fafb; border-color: #d1d5db; }
        
        @media (max-width: 768px) {
            .container { padding: 16px 12px 40px; }
            .success-card { padding: 30px 20px; }
            .success-icon { width: 70px; height: 70px; font-size: 36px; }
            .success-title { font-size: 22px; }
            .section { padding: 16px; }
            .vehicle-info { gap: 12px; }
            .vehicle-image { width: 80px; height: 80px; }
            .btn-group { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 640px) {
            .success-title { font-size: 20px; }
            .success-message { font-size: 14px; }
            .booking-number { font-size: 13px; padding: 8px 16px; }
            .section-title { font-size: 15px; }
            .vehicle-name { font-size: 15px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1 class="success-title">Booking Confirmed Successfully!</h1>
        <p class="success-message">
            Your vehicle booking has been submitted. We will review your payment and get back to you shortly.
        </p>
        <div class="booking-number">
            <i class="fas fa-receipt"></i>
            <span>Booking #<?= htmlspecialchars($booking['booking_number']) ?></span>
        </div>
    </div>
    
    <div class="alert-box">
        <div class="alert-box-title">
            <i class="fas fa-info-circle"></i>
            What's Next?
        </div>
        <div class="alert-box-text">
            Our team will verify your payment within 24-48 hours. You will receive a notification once your booking is approved. You can track your booking status in the "My Bookings" section.
        </div>
    </div>
    
    <div class="section">
        <h3 class="section-title">
            <i class="fas fa-car"></i>
            Vehicle Details
        </h3>
        <div class="vehicle-info">
            <?php if ($image_path): ?>
                <img src="<?= SITE_URL . '/' . htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($booking['vehicle_name']) ?>" class="vehicle-image">
            <?php else: ?>
                <div class="vehicle-image no-image">
                    <i class="fas fa-car"></i>
                </div>
            <?php endif; ?>
            
            <div class="vehicle-details">
                <span class="vehicle-badge">
                    <i class="fas fa-car"></i>
                    Car Rental
                </span>
                <h2 class="vehicle-name"><?= htmlspecialchars($booking['vehicle_name']) ?></h2>
                <div class="vehicle-meta">
                    <span><i class="fas fa-cog"></i> <?= ucfirst($booking['transmission']) ?></span>
                    <span><i class="fas fa-gas-pump"></i> <?= ucfirst($booking['fuel_type']) ?></span>
                    <span><i class="fas fa-users"></i> <?= $booking['seats'] ?> Seats</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h3 class="section-title">
            <i class="fas fa-file-alt"></i>
            Booking Information
        </h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Booking Number</span>
                <span class="info-value"><?= htmlspecialchars($booking['booking_number']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Booking Date</span>
                <span class="info-value"><?= date('M d, Y', strtotime($booking['created_at'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Start Date</span>
                <span class="info-value"><?= date('M d, Y', strtotime($booking['start_date'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">End Date</span>
                <span class="info-value"><?= date('M d, Y', strtotime($booking['end_date'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Rental Days</span>
                <span class="info-value"><?= $booking['rental_days'] ?> Day(s)</span>
            </div>
            <?php if ($booking['pickup_location']): ?>
            <div class="info-row">
                <span class="info-label">Pickup Location</span>
                <span class="info-value"><?= htmlspecialchars($booking['pickup_location']) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <span class="status-badge <?= $booking['status'] ?>">
                        <i class="fas fa-clock"></i>
                        <?= ucwords(str_replace('_', ' ', $booking['status'])) ?>
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Amount</span>
                <span class="info-value" style="font-size: 18px; color: #f97316;">RWF <?= number_format($booking['total_amount']) ?></span>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h3 class="section-title">
            <i class="fas fa-credit-card"></i>
            Payment Information
        </h3>
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Payment Method</span>
                <span class="info-value"><?= htmlspecialchars($booking['payment_method_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Account Number</span>
                <span class="info-value"><?= htmlspecialchars($booking['account_number']) ?></span>
            </div>
        </div>
        <?php if ($booking['payment_proof']): ?>
            <div style="margin-top: 16px;">
                <div class="info-label" style="margin-bottom: 8px;">Payment Proof:</div>
                <img src="<?= SITE_URL . '/' . htmlspecialchars($booking['payment_proof']) ?>" alt="Payment Proof" class="payment-proof-img">
            </div>
        <?php endif; ?>
    </div>
    
    <div class="btn-group">
        <a href="<?= SITE_URL ?>/my-bookings.php" class="btn btn-primary">
            <i class="fas fa-list"></i>
            View My Bookings
        </a>
        <a href="<?= SITE_URL ?>/vehicles.php" class="btn btn-secondary">
            <i class="fas fa-car"></i>
            Browse Vehicles
        </a>
    </div>
</div>

</body>
</html>
