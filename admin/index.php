<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

// --- Stats ---
$stats = [];
$stat_queries = [
    'total_products'      => "SELECT COUNT(*) FROM products",
    'total_properties'    => "SELECT COUNT(*) FROM properties",
    'total_vehicles'      => "SELECT COUNT(*) FROM vehicles",
    'total_customers'     => "SELECT COUNT(*) FROM users",
    'total_orders'        => "SELECT COUNT(*) FROM orders",
    'pending_orders'      => "SELECT COUNT(*) FROM orders WHERE status = 'payment_submitted'",
    'total_bookings'      => "SELECT COUNT(*) FROM bookings",
    'pending_bookings'    => "SELECT COUNT(*) FROM bookings WHERE status = 'payment_submitted'",
    'total_prop_orders'   => "SELECT COUNT(*) FROM property_orders",
    'pending_prop_orders' => "SELECT COUNT(*) FROM property_orders WHERE status = 'payment_submitted'",
    'pending_reviews'     => "SELECT COUNT(*) FROM reviews WHERE status = 'pending'",
];
foreach ($stat_queries as $key => $sql) {
    $res = $conn->query($sql);
    $stats[$key] = $res ? (int)$res->fetch_row()[0] : 0;
}

// --- Revenue ---
function qVal($conn, $sql) {
    $r = $conn->query($sql);
    if (!$r) return 0;
    $row = $r->fetch_row();
    return $row ? (float)$row[0] : 0;
}
$rev_ecom  = qVal($conn, "SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('pending_payment','rejected','cancelled')");
$rev_prop  = qVal($conn, "SELECT COALESCE(SUM(amount),0) FROM property_orders WHERE status NOT IN ('pending_payment','rejected','cancelled')");
$rev_car   = qVal($conn, "SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status NOT IN ('pending_payment','rejected','cancelled')");
$rev_total = $rev_ecom + $rev_prop + $rev_car;

// --- Recent Orders ---
$recent_orders = $conn->query("
    SELECT o.order_number, o.total_amount, o.status, o.created_at, u.name AS customer
    FROM orders o JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// --- Recent Bookings ---
$recent_bookings = $conn->query("
    SELECT b.booking_number, b.total_amount, b.status, b.created_at, u.name AS customer, b.vehicle_name
    FROM bookings b JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// --- Recent Activity ---
$recent_logs = $conn->query("
    SELECT l.action, l.description, l.created_at, l.user_type,
           COALESCE(a.name, u.name) AS actor
    FROM activity_logs l
    LEFT JOIN admins a ON l.user_type = 'admin'    AND l.user_id = a.id
    LEFT JOIN users  u ON l.user_type = 'customer' AND l.user_id = u.id
    ORDER BY l.created_at DESC LIMIT 8
")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Dashboard';
require_once 'includes/header.php';
?>

<style>
    /* ===== REVENUE BANNER ===== */
    .rev-banner {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 60%, #c2410c 100%);
        border-radius: var(--radius);
        padding: 24px 28px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }
    .rev-banner::before {
        content: '';
        position: absolute;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,.07);
        top: -80px; right: -60px;
    }
    .rev-banner::after {
        content: '';
        position: absolute;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
        bottom: -50px; right: 120px;
    }
    .rev-left { position: relative; z-index: 1; }
    .rev-label { font-size: 12px; color: rgba(255,255,255,.75); font-weight: 600; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 6px; }
    .rev-amount { font-size: 32px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 10px; }
    .rev-breakdown {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 20px;
    }
    .rev-item {
        font-size: 12px;
        color: rgba(255,255,255,.8);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .rev-item::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,.6); flex-shrink: 0; }
    .rev-icon { font-size: 52px; opacity: .15; position: relative; z-index: 1; line-height: 1; }

    /* ===== STAT CARDS ===== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--white);
        border-radius: var(--radius);
        padding: 18px 20px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: box-shadow .2s, transform .2s;
    }
    .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); transform: translateY(-1px); }
    .stat-icon {
        width: 46px; height: 46px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg { width: 22px; height: 22px; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
    .si-orange { background: #fff7ed; }
    .si-orange svg { stroke: #f97316; }
    .si-blue   { background: #eff6ff; }
    .si-blue   svg { stroke: #3b82f6; }
    .si-green  { background: #f0fdf4; }
    .si-green  svg { stroke: #22c55e; }
    .si-purple { background: #faf5ff; }
    .si-purple svg { stroke: #a855f7; }
    .si-red    { background: #fff1f1; }
    .si-red    svg { stroke: #ef4444; }
    .si-teal   { background: #f0fdfa; }
    .si-teal   svg { stroke: #14b8a6; }

    .stat-info { min-width: 0; }
    .stat-info p    { font-size: 12px; color: var(--text-muted); margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .stat-info h3   { font-size: 24px; font-weight: 800; color: var(--text); line-height: 1; }
    .stat-info small { font-size: 11px; color: #f97316; display: block; margin-top: 3px; }
    .stat-info small.ok { color: #22c55e; }

    /* ===== GRID 2 ===== */
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }

    /* ===== ACTIVITY ===== */
    .activity-list { padding: 4px 0; }
    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 18px;
        border-bottom: 1px solid #f3f4f6;
        align-items: flex-start;
    }
    .activity-item:last-child { border-bottom: none; }
    .act-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--orange);
        margin-top: 5px;
        flex-shrink: 0;
    }
    .activity-item p    { font-size: 13px; color: #374151; line-height: 1.5; }
    .activity-item small { font-size: 11px; color: #9ca3af; }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 32px 16px;
        color: #9ca3af;
        font-size: 13px;
    }
    .empty-state svg { width: 36px; height: 36px; stroke: #d1d5db; fill: none; stroke-width: 1.5; margin-bottom: 8px; display: block; margin-inline: auto; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1100px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 820px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .grid-2     { grid-template-columns: 1fr; gap: 16px; }
        .rev-banner { padding: 20px; }
        .rev-amount { font-size: 26px; }
        .rev-icon   { display: none; }
    }
    @media (max-width: 480px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .stat-card  { padding: 14px; gap: 10px; }
        .stat-icon  { width: 38px; height: 38px; }
        .stat-icon svg { width: 18px; height: 18px; }
        .stat-info h3 { font-size: 20px; }
        .rev-breakdown { gap: 6px 14px; }
    }
</style>

<!-- Revenue Banner -->
<div class="rev-banner">
    <div class="rev-left">
        <div class="rev-label">Total Platform Revenue</div>
        <div class="rev-amount"><?= formatCurrency($rev_total) ?></div>
        <div class="rev-breakdown">
            <span class="rev-item">E-Commerce: <?= formatCurrency($rev_ecom) ?></span>
            <span class="rev-item">Real Estate: <?= formatCurrency($rev_prop) ?></span>
            <span class="rev-item">Car Rental: <?= formatCurrency($rev_car) ?></span>
        </div>
    </div>
    <div class="rev-icon">💰</div>
</div>

<!-- Stat Cards -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon si-orange">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div class="stat-info">
            <p>Products</p>
            <h3><?= $stats['total_products'] ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-blue">
            <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="stat-info">
            <p>Properties</p>
            <h3><?= $stats['total_properties'] ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-teal">
            <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div class="stat-info">
            <p>Vehicles</p>
            <h3><?= $stats['total_vehicles'] ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-purple">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <p>Customers</p>
            <h3><?= $stats['total_customers'] ?></h3>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-orange">
            <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div class="stat-info">
            <p>Orders</p>
            <h3><?= $stats['total_orders'] ?></h3>
            <?php if ($stats['pending_orders'] > 0): ?>
                <small><?= $stats['pending_orders'] ?> awaiting review</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-blue">
            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="stat-info">
            <p>Bookings</p>
            <h3><?= $stats['total_bookings'] ?></h3>
            <?php if ($stats['pending_bookings'] > 0): ?>
                <small><?= $stats['pending_bookings'] ?> awaiting review</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-green">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-info">
            <p>Property Orders</p>
            <h3><?= $stats['total_prop_orders'] ?></h3>
            <?php if ($stats['pending_prop_orders'] > 0): ?>
                <small><?= $stats['pending_prop_orders'] ?> awaiting review</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon si-red">
            <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div class="stat-info">
            <p>Pending Reviews</p>
            <h3><?= $stats['pending_reviews'] ?></h3>
            <?php if ($stats['pending_reviews'] == 0): ?>
                <small class="ok">All clear</small>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Recent Orders & Bookings -->
<div class="grid-2">

    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header">
            <h4>Recent Orders</h4>
            <a href="orders.php">View all →</a>
        </div>
        <div class="table-wrap">
            <?php if (empty($recent_orders)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                    No orders yet
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $o): ?>
                    <tr>
                        <td><strong><?= e($o['order_number']) ?></strong></td>
                        <td><?= e($o['customer']) ?></td>
                        <td><?= formatCurrency($o['total_amount']) ?></td>
                        <td><?= statusBadge($o['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header">
            <h4>Recent Bookings</h4>
            <a href="bookings.php">View all →</a>
        </div>
        <div class="table-wrap">
            <?php if (empty($recent_bookings)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    No bookings yet
                </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_bookings as $b): ?>
                    <tr>
                        <td><strong><?= e($b['booking_number']) ?></strong></td>
                        <td><?= e($b['vehicle_name']) ?></td>
                        <td><?= formatCurrency($b['total_amount']) ?></td>
                        <td><?= statusBadge($b['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h4>Recent Activity</h4>
    </div>
    <div class="activity-list">
        <?php if (empty($recent_logs)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                No activity yet
            </div>
        <?php else: ?>
            <?php foreach ($recent_logs as $log): ?>
            <div class="activity-item">
                <div class="act-dot"></div>
                <div>
                    <p>
                        <strong><?= e($log['actor'] ?? ucfirst($log['user_type'])) ?></strong>
                        — <?= e($log['description'] ?: $log['action']) ?>
                    </p>
                    <small><?= formatDateTime($log['created_at']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
