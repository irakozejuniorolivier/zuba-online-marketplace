<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$user_id = currentCustomerId();

// Fetch all bookings
$stmt = $conn->prepare("
    SELECT b.*, v.brand, v.model, v.year, v.transmission, v.fuel_type, v.seats,
    pm.name as payment_method_name,
    (SELECT vi.image_path FROM vehicle_images vi WHERE vi.vehicle_id = b.vehicle_id ORDER BY vi.is_primary DESC, vi.sort_order ASC LIMIT 1) as vehicle_image
    FROM bookings b
    LEFT JOIN vehicles v ON b.vehicle_id = v.id
    LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

$page_title = 'My Bookings';
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
        .header-content { display: flex; align-items: center; gap: 16px; padding: 14px 16px; max-width: 1200px; margin: 0 auto; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; }
        .header-title { flex: 1; font-size: 18px; font-weight: 700; color: #1a1a2e; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px 16px 40px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .stat-card { background: linear-gradient(135deg, #fff 0%, #fafafa 100%); border-radius: 16px; padding: 20px 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; position: relative; overflow: hidden; transition: all .3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .stat-card::before { content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: radial-gradient(circle, rgba(249,115,22,0.1) 0%, transparent 70%); }
        .stat-label { font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 32px; font-weight: 900; color: #1a1a2e; line-height: 1; margin-bottom: 8px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 12px; }
        .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; }
        .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; }
        .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); color: #9a3412; }
        .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; }
        
        .bookings-list { display: grid; gap: 16px; }
        .booking-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; transition: all .3s; overflow: hidden; }
        .booking-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.12); transform: translateY(-2px); }
        
        .booking-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px dashed #e5e7eb; }
        .booking-number { font-size: 15px; font-weight: 800; color: #1a1a2e; margin-bottom: 6px; letter-spacing: 0.3px; }
        .booking-date { font-size: 12px; color: #9ca3af; display: flex; align-items: center; gap: 6px; }
        .booking-date i { color: #f97316; }
        
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 24px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
        .status-badge i { font-size: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .status-badge.payment_submitted { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); color: #1e40af; border: 1px solid #93c5fd; }
        .status-badge.approved { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border: 1px solid #6ee7b7; }
        .status-badge.active { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e; border: 1px solid #fcd34d; }
        .status-badge.completed { background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); color: #3730a3; border: 1px solid #a5b4fc; }
        .status-badge.rejected { background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #991b1b; border: 1px solid #fca5a5; }
        .status-badge.cancelled { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #6b7280; border: 1px solid #d1d5db; }
        
        .booking-content { display: flex; gap: 16px; }
        .vehicle-image { width: 140px; height: 140px; border-radius: 14px; object-fit: cover; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); flex-shrink: 0; border: 2px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .vehicle-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 48px; }
        
        .booking-details { flex: 1; min-width: 0; }
        .vehicle-name { font-size: 17px; font-weight: 800; color: #1a1a2e; margin-bottom: 10px; line-height: 1.3; }
        .vehicle-meta { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .vehicle-meta span { display: flex; align-items: center; gap: 5px; padding: 4px 10px; background: #f9fafb; border-radius: 8px; font-weight: 600; }
        .vehicle-meta i { color: #f97316; font-size: 11px; }
        
        .booking-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
        .info-item { background: #f9fafb; padding: 10px 12px; border-radius: 10px; border: 1px solid #f3f4f6; }
        .info-label { font-size: 11px; color: #9ca3af; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
        .info-value { font-size: 13px; font-weight: 800; color: #1a1a2e; line-height: 1.3; }
        
        .booking-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 2px dashed #e5e7eb; gap: 12px; }
        .booking-total-wrapper { }
        .booking-total-label { font-size: 11px; color: #9ca3af; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
        .booking-total { font-size: 22px; font-weight: 900; color: #f97316; line-height: 1; }
        .btn-track { padding: 12px 24px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; border-radius: 12px; font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all .3s; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 12px rgba(249,115,22,0.3); white-space: nowrap; }
        .btn-track:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(249,115,22,0.5); background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); }
        .btn-track i { font-size: 14px; }
        
        .empty-state { background: #fff; border-radius: 12px; padding: 80px 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .empty-state i { font-size: 80px; color: #e5e7eb; margin-bottom: 20px; }
        .empty-state h3 { font-size: 20px; color: #6b7280; margin-bottom: 8px; }
        .empty-state p { color: #9ca3af; margin-bottom: 20px; }
        .btn-browse { padding: 12px 24px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        
        @media (max-width: 768px) {
            .container { padding: 16px 12px 40px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 16px 12px; }
            .stat-value { font-size: 26px; }
            .stat-icon { width: 38px; height: 38px; font-size: 16px; margin-bottom: 10px; }
            .stat-label { font-size: 10px; }
            .booking-card { padding: 16px; border-radius: 14px; }
            .booking-header { flex-direction: column; gap: 10px; align-items: flex-start; }
            .booking-content { flex-direction: column; }
            .vehicle-image { width: 100%; height: 220px; border-radius: 12px; }
            .vehicle-name { font-size: 16px; }
            .vehicle-meta { gap: 8px; }
            .vehicle-meta span { padding: 4px 8px; font-size: 11px; }
            .booking-info-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .info-item { padding: 8px 10px; }
            .booking-footer { flex-direction: column-reverse; gap: 12px; align-items: stretch; }
            .booking-total { font-size: 24px; text-align: center; }
            .btn-track { width: 100%; justify-content: center; padding: 14px 24px; font-size: 14px; }
        }
        
        @media (max-width: 480px) {
            .header-content { padding: 10px 12px; }
            .header-title { font-size: 15px; }
            .btn-back { width: 36px; height: 36px; font-size: 16px; border-radius: 8px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .stat-card { padding: 14px 10px; }
            .stat-value { font-size: 24px; }
            .stat-icon { width: 36px; height: 36px; font-size: 15px; }
            .stat-label { font-size: 10px; }
            .booking-card { padding: 14px; }
            .booking-number { font-size: 14px; }
            .vehicle-image { height: 200px; }
            .vehicle-name { font-size: 15px; }
            .booking-info-grid { grid-template-columns: 1fr; }
            .booking-total { font-size: 22px; }
            .btn-track { font-size: 13px; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/profile.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">My Vehicle Bookings</div>
    </div>
</header>

<div class="container">
    <?php
    $total_bookings = count($bookings);
    $pending_count = count(array_filter($bookings, fn($b) => $b['status'] === 'payment_submitted'));
    $approved_count = count(array_filter($bookings, fn($b) => $b['status'] === 'approved'));
    $completed_count = count(array_filter($bookings, fn($b) => $b['status'] === 'completed'));
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-receipt"></i></div>
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= $total_bookings ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-label">Approved</div>
            <div class="stat-value"><?= $approved_count ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?= $pending_count ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div class="stat-label">Completed</div>
            <div class="stat-value"><?= $completed_count ?></div>
        </div>
    </div>
    
    <?php if (count($bookings) > 0): ?>
        <div class="bookings-list">
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <div class="booking-number"><?= htmlspecialchars($booking['booking_number']) ?></div>
                            <div class="booking-date">
                                <i class="fas fa-calendar"></i> <?= date('M d, Y', strtotime($booking['created_at'])) ?>
                            </div>
                        </div>
                        <span class="status-badge <?= $booking['status'] ?>">
                            <i class="fas fa-circle"></i>
                            <?= ucwords(str_replace('_', ' ', $booking['status'])) ?>
                        </span>
                    </div>
                    
                    <div class="booking-content">
                        <?php if ($booking['vehicle_image']): ?>
                            <img src="<?= SITE_URL . '/' . htmlspecialchars($booking['vehicle_image']) ?>" alt="<?= htmlspecialchars($booking['vehicle_name']) ?>" class="vehicle-image">
                        <?php else: ?>
                            <div class="vehicle-image no-image">
                                <i class="fas fa-car"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-details">
                            <div class="vehicle-name"><?= htmlspecialchars($booking['vehicle_name']) ?></div>
                            <div class="vehicle-meta">
                                <span><i class="fas fa-cog"></i> <?= ucfirst($booking['transmission']) ?></span>
                                <span><i class="fas fa-gas-pump"></i> <?= ucfirst($booking['fuel_type']) ?></span>
                                <span><i class="fas fa-users"></i> <?= $booking['seats'] ?> Seats</span>
                            </div>
                            
                            <div class="booking-info-grid">
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
                                <?php if ($booking['pickup_location']): ?>
                                <div class="info-item">
                                    <div class="info-label">Pickup Location</div>
                                    <div class="info-value"><?= htmlspecialchars($booking['pickup_location']) ?></div>
                                </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <div class="info-label">Payment Method</div>
                                    <div class="info-value"><?= htmlspecialchars($booking['payment_method_name']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-footer">
                        <div class="booking-total-wrapper">
                            <div class="booking-total-label">Total Amount</div>
                            <div class="booking-total">RWF <?= number_format($booking['total_amount']) ?></div>
                        </div>
                        <a href="<?= SITE_URL ?>/booking-details.php?booking_id=<?= $booking['id'] ?>" class="btn-track">
                            <i class="fas fa-location-arrow"></i>
                            Track Booking
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <h3>No Bookings Yet</h3>
            <p>You haven't made any vehicle bookings yet</p>
            <a href="<?= SITE_URL ?>/vehicles.php" class="btn-browse">
                <i class="fas fa-car"></i>
                Browse Vehicles
            </a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
