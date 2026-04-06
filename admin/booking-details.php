<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('bookings.php');

// Fetch booking details
$sql = "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
        v.brand, v.model, v.year, v.plate_number,
        pm.name AS payment_method_name,
        a.name AS approved_by_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
        LEFT JOIN admins a ON b.approved_by = a.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) redirect('bookings.php');

// Get vehicle primary image
$vehicle_img = $conn->query("SELECT image_path FROM vehicle_images WHERE vehicle_id = {$booking['vehicle_id']} AND is_primary = 1 LIMIT 1")->fetch_assoc();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $admin_note = trim($_POST['admin_note'] ?? '');
    
    $stmt = $conn->prepare("UPDATE bookings SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $admin_note, $id);
    $stmt->execute();
    
    if ($new_status === 'approved') {
        $conn->query("UPDATE bookings SET approved_by = {$admin['id']}, approved_at = NOW() WHERE id = $id");
    }
    
    logActivity('admin', $admin['id'], 'UPDATE_BOOKING_STATUS', "Updated booking #{$booking['booking_number']} status to $new_status");
    setFlash('success', 'Booking status updated successfully.');
    redirect("booking-details.php?id=$id");
}

$page_title = 'Booking Details';
require_once 'includes/header.php';
$flash = getFlash();
?>

<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:12px; flex-wrap:wrap; }
.page-header h2 { font-size:20px; font-weight:800; color:var(--text); }
.btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all .18s; white-space:nowrap; }
.btn-secondary { background:#6b7280; color:#fff; }
.btn-secondary:hover { background:#4b5563; }
.details-grid { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:20px; }
.card { background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:20px; margin-bottom:20px; }
.card-header { font-size:16px; font-weight:700; color:var(--text); margin-bottom:16px; padding-bottom:12px; border-bottom:2px solid #f97316; }
.info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; }
.info-row:last-child { border-bottom:none; }
.info-label { font-size:13px; color:var(--text-muted); font-weight:600; }
.info-value { font-size:13px; color:var(--text); font-weight:600; }
.vehicle-preview { display:flex; gap:16px; align-items:flex-start; margin-bottom:16px; }
.vehicle-img { width:120px; height:90px; border-radius:8px; object-fit:cover; background:#f3f4f6; }
.vehicle-info { flex:1; }
.vehicle-name { font-size:16px; font-weight:700; color:var(--text); margin-bottom:4px; }
.vehicle-meta { font-size:12px; color:var(--text-muted); }
.status-badge { display:inline-block; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; }
.status-payment_submitted { background:#fef3c7; color:#92400e; }
.status-approved { background:#d1fae5; color:#065f46; }
.status-active { background:#dbeafe; color:#1e40af; }
.status-completed { background:#dcfce7; color:#15803d; }
.status-cancelled { background:#fee2e2; color:#991b1b; }
.status-rejected { background:#fecaca; color:#dc2626; }
.form-group { margin-bottom:16px; }
.form-group label { display:block; font-size:13px; font-weight:600; color:var(--text); margin-bottom:6px; }
.form-group select, .form-group textarea { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:6px; font-size:13px; outline:none; font-family:inherit; }
.form-group select:focus, .form-group textarea:focus { border-color:#f97316; }
.form-group textarea { resize:vertical; min-height:80px; }
.btn-primary { background:#f97316; color:#fff; }
.btn-primary:hover { background:#ea580c; }
.timeline { position:relative; padding-left:30px; }
.timeline-item { position:relative; padding-bottom:20px; }
.timeline-item:last-child { padding-bottom:0; }
.timeline-item::before { content:''; position:absolute; left:-30px; top:0; width:12px; height:12px; border-radius:50%; background:#f97316; border:3px solid #fff; box-shadow:0 0 0 2px #f97316; }
.timeline-item::after { content:''; position:absolute; left:-24px; top:12px; width:2px; height:calc(100% - 12px); background:#e5e7eb; }
.timeline-item:last-child::after { display:none; }
.timeline-date { font-size:11px; color:var(--text-muted); margin-bottom:4px; }
.timeline-content { font-size:13px; color:var(--text); font-weight:600; }
.payment-proof { max-width:100%; border-radius:8px; cursor:pointer; transition:transform .2s; }
.payment-proof:hover { transform:scale(1.02); }
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; }
.modal.show { display:flex; }
.modal img { max-width:90%; max-height:90vh; border-radius:8px; }
.modal-close { position:absolute; top:20px; right:20px; width:40px; height:40px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:24px; color:#374151; }
.date-range-box { background:#f9fafb; padding:12px; border-radius:6px; margin-bottom:12px; }
.date-range-row { display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; }
.date-range-row:last-child { margin-bottom:0; }
.date-range-label { color:var(--text-muted); font-weight:600; }
.date-range-value { color:var(--text); font-weight:700; }
@media (max-width:768px) {
    .details-grid { grid-template-columns:1fr; }
    .vehicle-preview { flex-direction:column; }
    .vehicle-img { width:100%; height:200px; }
}
</style>

<div class="page-header">
    <h2>🚗 Booking Details</h2>
    <a href="bookings.php" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Bookings
    </a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<div class="details-grid">
    <!-- Left Column -->
    <div>
        <!-- Vehicle Preview -->
        <div class="card">
            <div class="card-header">Vehicle Information</div>
            <div class="vehicle-preview">
                <?php if ($vehicle_img): ?>
                    <img src="<?= SITE_URL . '/' . e($vehicle_img['image_path']) ?>" alt="Vehicle" class="vehicle-img">
                <?php else: ?>
                    <div class="vehicle-img" style="display:flex;align-items:center;justify-content:center;color:#9ca3af;">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                    </div>
                <?php endif; ?>
                <div class="vehicle-info">
                    <div class="vehicle-name"><?= e($booking['vehicle_name']) ?></div>
                    <div class="vehicle-meta">
                        <?= e($booking['brand']) ?> <?= e($booking['model']) ?> <?= $booking['year'] ?><br>
                        Plate: <?= e($booking['plate_number']) ?>
                    </div>
                </div>
            </div>
            
            <div class="date-range-box">
                <div class="date-range-row">
                    <span class="date-range-label">Start Date:</span>
                    <span class="date-range-value"><?= formatDate($booking['start_date']) ?></span>
                </div>
                <div class="date-range-row">
                    <span class="date-range-label">End Date:</span>
                    <span class="date-range-value"><?= formatDate($booking['end_date']) ?></span>
                </div>
                <div class="date-range-row">
                    <span class="date-range-label">Rental Days:</span>
                    <span class="date-range-value"><?= $booking['rental_days'] ?> days</span>
                </div>
                <div class="date-range-row">
                    <span class="date-range-label">Rate Type:</span>
                    <span class="date-range-value"><?= ucfirst($booking['rate_type']) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card">
            <div class="card-header">Customer Information</div>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value"><?= e($booking['customer_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= e($booking['customer_email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?= e($booking['customer_phone']) ?></span>
            </div>
        </div>

        <!-- Pickup & Dropoff -->
        <?php if ($booking['pickup_location'] || $booking['dropoff_location']): ?>
        <div class="card">
            <div class="card-header">Pickup & Dropoff</div>
            <?php if ($booking['pickup_location']): ?>
            <div class="info-row">
                <span class="info-label">Pickup Location:</span>
                <span class="info-value"><?= e($booking['pickup_location']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($booking['dropoff_location']): ?>
            <div class="info-row">
                <span class="info-label">Dropoff Location:</span>
                <span class="info-value"><?= e($booking['dropoff_location']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Customer Note -->
        <?php if ($booking['customer_note']): ?>
        <div class="card">
            <div class="card-header">Customer Note</div>
            <p style="font-size:13px;color:var(--text);line-height:1.6;margin:0;"><?= nl2br(e($booking['customer_note'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Status Update Form -->
        <div class="card">
            <div class="card-header">Update Status</div>
            <form method="POST">
                <div class="form-group">
                    <label>Booking Status</label>
                    <select name="status" required>
                        <option value="payment_submitted" <?= $booking['status']==='payment_submitted'?'selected':'' ?>>Payment Submitted</option>
                        <option value="approved" <?= $booking['status']==='approved'?'selected':'' ?>>Approved</option>
                        <option value="active" <?= $booking['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="completed" <?= $booking['status']==='completed'?'selected':'' ?>>Completed</option>
                        <option value="cancelled" <?= $booking['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                        <option value="rejected" <?= $booking['status']==='rejected'?'selected':'' ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Admin Note</label>
                    <textarea name="admin_note" placeholder="Add internal notes..."><?= e($booking['admin_note']) ?></textarea>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Update Status
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Booking Details -->
        <div class="card">
            <div class="card-header">Booking Details</div>
            <div class="info-row">
                <span class="info-label">Booking #:</span>
                <span class="info-value"><?= e($booking['booking_number']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="status-badge status-<?= $booking['status'] ?>"><?= ucwords(str_replace('_', ' ', $booking['status'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Rate Amount:</span>
                <span class="info-value"><?= formatCurrency($booking['rate_amount']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Subtotal:</span>
                <span class="info-value"><?= formatCurrency($booking['subtotal']) ?></span>
            </div>
            <?php if ($booking['insurance_fee'] > 0): ?>
            <div class="info-row">
                <span class="info-label">Insurance Fee:</span>
                <span class="info-value"><?= formatCurrency($booking['insurance_fee']) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Total Amount:</span>
                <span class="info-value" style="font-size:16px;color:#f97316;"><?= formatCurrency($booking['total_amount']) ?></span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card">
            <div class="card-header">Payment Information</div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?= e($booking['payment_method_name'] ?? 'Not specified') ?></span>
            </div>
            <?php if ($booking['payment_proof']): ?>
            <div style="margin-top:12px;">
                <div class="info-label" style="margin-bottom:8px;">Payment Proof:</div>
                <img src="<?= UPLOAD_URL . 'payment_proofs/' . e($booking['payment_proof']) ?>" alt="Payment Proof" class="payment-proof" onclick="showImageModal(this.src)">
            </div>
            <?php else: ?>
            <div style="margin-top:12px;padding:12px;background:#fef3c7;border-radius:6px;font-size:12px;color:#92400e;">
                No payment proof uploaded yet
            </div>
            <?php endif; ?>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">Booking Timeline</div>
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDateTime($booking['created_at']) ?></div>
                    <div class="timeline-content">Booking Created</div>
                </div>
                <?php if ($booking['approved_at']): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDateTime($booking['approved_at']) ?></div>
                    <div class="timeline-content">Approved by <?= e($booking['approved_by_name']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($booking['updated_at'] !== $booking['created_at']): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?= formatDateTime($booking['updated_at']) ?></div>
                    <div class="timeline-content">Last Updated</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal" id="imageModal" onclick="closeImageModal()">
    <div class="modal-close">×</div>
    <img src="" alt="Payment Proof" id="modalImage">
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.add('show');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
}
</script>

<?php require_once 'includes/footer.php'; ?>
