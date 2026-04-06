<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_status'])) {
        $booking_id = (int)$_POST['booking_id'];
        $new_status = $_POST['status'];
        $allowed = ['payment_submitted', 'approved', 'active', 'completed', 'cancelled', 'rejected'];
        if (in_array($new_status, $allowed)) {
            $conn->query("UPDATE bookings SET status = '$new_status' WHERE id = $booking_id");
            if ($new_status === 'approved') {
                $conn->query("UPDATE bookings SET approved_by = {$admin['id']}, approved_at = NOW() WHERE id = $booking_id");
            }
            logActivity('admin', $admin['id'], 'CHANGE_BOOKING_STATUS', "Changed booking #$booking_id status to $new_status");
            setFlash('success', 'Booking status updated successfully.');
        }
        redirect('bookings.php');
    }
    
    if (isset($_POST['delete_id'])) {
        $del_id = (int)$_POST['delete_id'];
        $conn->query("DELETE FROM bookings WHERE id = $del_id");
        logActivity('admin', $admin['id'], 'DELETE_BOOKING', "Deleted booking ID $del_id");
        setFlash('success', 'Booking deleted successfully.');
        redirect('bookings.php');
    }
}

// Filters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

$where = ['1=1'];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "(b.booking_number LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR b.vehicle_name LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s]);
    $types .= 'ssss';
}
if ($status !== '') {
    $where[] = "b.status = ?";
    $params[] = $status;
    $types .= 's';
}

$where_sql = implode(' AND ', $where);

// Total count
$count_sql = "SELECT COUNT(*) FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE $where_sql";
if ($params) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
} else {
    $total = (int)$conn->query($count_sql)->fetch_row()[0];
}

$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

// Fetch bookings
$sql = "SELECT b.*, u.name AS customer_name, u.email AS customer_email, pm.name AS payment_method_name
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN payment_methods pm ON b.payment_method_id = pm.id
        WHERE $where_sql
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?";

$fetch_params = array_merge($params, [$per_page, $offset]);
$fetch_types = $types . 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($fetch_types, ...$fetch_params);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Stats
$stats_bar = [];
foreach (['payment_submitted', 'approved', 'active', 'completed', 'cancelled', 'rejected'] as $s) {
    $r = $conn->query("SELECT COUNT(*) FROM bookings WHERE status='$s'");
    $stats_bar[$s] = (int)$r->fetch_row()[0];
}
$stats_bar['total'] = (int)$conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];

$page_title = 'Vehicle Bookings';
require_once 'includes/header.php';
$flash = getFlash();
?>

<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:12px; flex-wrap:wrap; }
.page-header h2 { font-size:20px; font-weight:800; color:var(--text); }
.stats-strip { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.strip-card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; }
.strip-card .sc-label { font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:4px; }
.strip-card .sc-val { font-size:22px; font-weight:800; color:var(--text); }
.filters-bar { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); padding:14px 18px; margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
.filter-group { display:flex; flex-direction:column; gap:4px; }
.filter-group label { font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; }
.filter-group input, .filter-group select { padding:8px 12px; border:1px solid var(--border); border-radius:7px; font-size:13px; color:var(--text); background:#fff; outline:none; transition:border .18s; }
.filter-group input:focus, .filter-group select:focus { border-color:var(--orange); }
.filter-group input { min-width:220px; }
.filter-group select { min-width:150px; }
.filter-actions { display:flex; gap:8px; align-items:flex-end; margin-left:auto; }
.btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:all .18s; white-space:nowrap; }
.btn-primary { background:var(--orange); color:#fff; }
.btn-primary:hover { background:var(--orange-dark); }
.bookings-table { width:100%; border-collapse:collapse; }
.bookings-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:var(--text-muted); padding:10px 14px; text-align:left; background:#fafafa; border-bottom:1px solid var(--border); white-space:nowrap; }
.bookings-table td { font-size:13px; padding:11px 14px; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
.bookings-table tr:last-child td { border-bottom:none; }
.bookings-table tbody tr:hover td { background:#fffbf7; }
.booking-number { font-weight:700; color:var(--text); }
.customer-info { display:flex; flex-direction:column; gap:2px; }
.customer-name { font-weight:600; color:var(--text); font-size:13px; }
.customer-email { font-size:11px; color:var(--text-muted); }
.vehicle-name { font-weight:600; color:var(--text); }
.date-range { display:flex; flex-direction:column; gap:2px; font-size:12px; }
.date-range-label { color:var(--text-muted); font-size:10px; }
.status-dropdown { padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; outline:none; }
.status-dropdown:focus { border-color:var(--orange); }
.action-btns { display:flex; gap:6px; }
.action-btn { width:30px; height:30px; border-radius:6px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all .18s; text-decoration:none; }
.action-btn svg { width:14px; height:14px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
.ab-view { background:#eff6ff; color:#3b82f6; }
.ab-view:hover { background:#dbeafe; }
.ab-delete { background:#fee2e2; color:#dc2626; }
.ab-delete:hover { background:#fecaca; }
.pagination { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid var(--border); flex-wrap:wrap; gap:10px; }
.pagination-info { font-size:12.5px; color:var(--text-muted); }
.pagination-links { display:flex; gap:4px; }
.pg-btn { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; text-decoration:none; color:var(--text); border:1px solid var(--border); background:#fff; transition:all .18s; }
.pg-btn:hover { border-color:var(--orange); color:var(--orange); }
.pg-btn.active { background:var(--orange); color:#fff; border-color:var(--orange); }
.pg-btn.disabled { opacity:.4; pointer-events:none; }
.empty-state { text-align:center; padding:48px 16px; color:#9ca3af; }
.empty-state svg { width:48px; height:48px; stroke:#d1d5db; fill:none; stroke-width:1.2; margin-bottom:12px; display:block; margin-inline:auto; }
.empty-state h4 { font-size:15px; color:#6b7280; margin-bottom:6px; }
.empty-state p { font-size:13px; }
.mob-card { display:none; }
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; }
.modal.show { display:flex; }
.modal-content { background:#fff; border-radius:12px; max-width:400px; width:90%; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1); }
.modal-header { font-size:18px; font-weight:700; color:var(--text); margin-bottom:12px; }
.modal-body { font-size:14px; color:#6b7280; margin-bottom:20px; }
.modal-footer { display:flex; gap:10px; justify-content:flex-end; }
.modal-btn { padding:10px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; transition:all .18s; }
.modal-btn-cancel { background:#f3f4f6; color:#374151; }
.modal-btn-cancel:hover { background:#e5e7eb; }
.modal-btn-confirm { background:#ef4444; color:#fff; }
.modal-btn-confirm:hover { background:#dc2626; }
@media (max-width:900px) { .stats-strip { grid-template-columns:repeat(2,1fr); } }
@media (max-width:640px) {
    .page-header { flex-direction:column; align-items:flex-start; }
    .stats-strip { grid-template-columns:1fr; gap:8px; }
    .strip-card { padding:12px 14px; }
    .strip-card .sc-val { font-size:18px; }
    .filters-bar { flex-direction:column; padding:12px; }
    .filter-group { width:100%; }
    .filter-group input, .filter-group select { min-width:0; width:100%; box-sizing:border-box; }
    .filter-actions { margin-left:0; width:100%; }
    .filter-actions .btn { flex:1; justify-content:center; }
    .desk-table { display:none; }
    .mob-card { display:block; }
    .pagination { flex-direction:column; align-items:center; gap:8px; }
}
</style>

<div class="page-header">
    <h2>🚗 Vehicle Bookings</h2>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Stats Strip -->
<div class="stats-strip">
    <div class="strip-card">
        <div class="sc-label">Total Bookings</div>
        <div class="sc-val"><?= $stats_bar['total'] ?></div>
    </div>
    <div class="strip-card">
        <div class="sc-label">Pending Payment</div>
        <div class="sc-val" style="color:#f59e0b;"><?= $stats_bar['payment_submitted'] ?></div>
    </div>
    <div class="strip-card">
        <div class="sc-label">Active Rentals</div>
        <div class="sc-val" style="color:#3b82f6;"><?= $stats_bar['active'] ?></div>
    </div>
    <div class="strip-card">
        <div class="sc-label">Completed</div>
        <div class="sc-val" style="color:#15803d;"><?= $stats_bar['completed'] ?></div>
    </div>
</div>

<!-- Filters -->
<form method="GET" action="bookings.php">
<div class="filters-bar">
    <div class="filter-group">
        <label>Search</label>
        <input type="text" name="search" placeholder="Booking #, customer, vehicle…" value="<?= e($search) ?>">
    </div>
    <div class="filter-group">
        <label>Status</label>
        <select name="status">
            <option value="">All Status</option>
            <option value="payment_submitted" <?= $status==='payment_submitted' ? 'selected':'' ?>>Payment Submitted</option>
            <option value="approved" <?= $status==='approved' ? 'selected':'' ?>>Approved</option>
            <option value="active" <?= $status==='active' ? 'selected':'' ?>>Active</option>
            <option value="completed" <?= $status==='completed' ? 'selected':'' ?>>Completed</option>
            <option value="cancelled" <?= $status==='cancelled' ? 'selected':'' ?>>Cancelled</option>
            <option value="rejected" <?= $status==='rejected' ? 'selected':'' ?>>Rejected</option>
        </select>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="bookings.php" class="btn" style="background:#f3f4f6;color:var(--text);">Reset</a>
    </div>
</div>
</form>

<!-- Bookings Table -->
<div class="card">
    <?php if (empty($bookings)): ?>
    <div class="empty-state">
        <svg viewBox="0 0 24 24"><path d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
        <h4>No bookings found</h4>
        <p><?= $search || $status ? 'Try adjusting your filters.' : 'No vehicle bookings yet.' ?></p>
    </div>
    <?php else: ?>

    <!-- DESKTOP TABLE -->
    <div class="table-wrap desk-table">
        <table class="bookings-table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Rental Period</th>
                    <th>Days</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><span class="booking-number"><?= e($b['booking_number']) ?></span></td>
                    <td>
                        <div class="customer-info">
                            <span class="customer-name"><?= e($b['customer_name']) ?></span>
                            <span class="customer-email"><?= e($b['customer_email']) ?></span>
                        </div>
                    </td>
                    <td><span class="vehicle-name"><?= e($b['vehicle_name']) ?></span></td>
                    <td>
                        <div class="date-range">
                            <span class="date-range-label">From:</span>
                            <span><?= formatDate($b['start_date']) ?></span>
                            <span class="date-range-label">To:</span>
                            <span><?= formatDate($b['end_date']) ?></span>
                        </div>
                    </td>
                    <td><strong><?= $b['rental_days'] ?></strong></td>
                    <td><strong><?= formatCurrency($b['total_amount']) ?></strong></td>
                    <td><?= e($b['payment_method_name'] ?? '—') ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <input type="hidden" name="change_status" value="1">
                            <select name="status" class="status-dropdown" onchange="this.form.submit()">
                                <option value="payment_submitted" <?= $b['status']==='payment_submitted'?'selected':'' ?>>Payment Submitted</option>
                                <option value="approved" <?= $b['status']==='approved'?'selected':'' ?>>Approved</option>
                                <option value="active" <?= $b['status']==='active'?'selected':'' ?>>Active</option>
                                <option value="completed" <?= $b['status']==='completed'?'selected':'' ?>>Completed</option>
                                <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                                <option value="rejected" <?= $b['status']==='rejected'?'selected':'' ?>>Rejected</option>
                            </select>
                        </form>
                    </td>
                    <td><?= formatDate($b['created_at']) ?></td>
                    <td>
                        <div class="action-btns">
                            <a href="booking-details.php?id=<?= $b['id'] ?>" class="action-btn ab-view" title="View Details">
                                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <button type="button" class="action-btn ab-delete" title="Delete" onclick="confirmDelete(<?= $b['id'] ?>, '<?= e($b['booking_number']) ?>')">
                                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MOBILE CARDS -->
    <div class="mob-card">
        <style>
            .mob-list { padding:8px 12px; display:flex; flex-direction:column; gap:10px; }
            .mob-booking { background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px; }
            .mob-booking-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
            .mob-booking-number { font-size:14px; font-weight:700; color:var(--text); }
            .mob-booking-body { display:flex; flex-direction:column; gap:8px; margin-bottom:10px; }
            .mob-row { display:flex; justify-content:space-between; font-size:12px; }
            .mob-row-label { color:var(--text-muted); }
            .mob-row-value { color:var(--text); font-weight:600; }
            .mob-booking-footer { display:flex; gap:8px; }
            .mob-booking-footer select { flex:1; }
        </style>
        <div class="mob-list">
        <?php foreach ($bookings as $b): ?>
        <div class="mob-booking">
            <div class="mob-booking-header">
                <span class="mob-booking-number"><?= e($b['booking_number']) ?></span>
            </div>
            <div class="mob-booking-body">
                <div class="mob-row">
                    <span class="mob-row-label">Customer:</span>
                    <span class="mob-row-value"><?= e($b['customer_name']) ?></span>
                </div>
                <div class="mob-row">
                    <span class="mob-row-label">Vehicle:</span>
                    <span class="mob-row-value"><?= e($b['vehicle_name']) ?></span>
                </div>
                <div class="mob-row">
                    <span class="mob-row-label">Period:</span>
                    <span class="mob-row-value"><?= formatDate($b['start_date']) ?> - <?= formatDate($b['end_date']) ?></span>
                </div>
                <div class="mob-row">
                    <span class="mob-row-label">Days:</span>
                    <span class="mob-row-value"><?= $b['rental_days'] ?></span>
                </div>
                <div class="mob-row">
                    <span class="mob-row-label">Amount:</span>
                    <span class="mob-row-value"><?= formatCurrency($b['total_amount']) ?></span>
                </div>
                <div class="mob-row">
                    <span class="mob-row-label">Date:</span>
                    <span class="mob-row-value"><?= formatDate($b['created_at']) ?></span>
                </div>
            </div>
            <div class="mob-booking-footer">
                <form method="POST" style="flex:1;">
                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="change_status" value="1">
                    <select name="status" class="status-dropdown" style="width:100%;" onchange="this.form.submit()">
                        <option value="payment_submitted" <?= $b['status']==='payment_submitted'?'selected':'' ?>>Payment Submitted</option>
                        <option value="approved" <?= $b['status']==='approved'?'selected':'' ?>>Approved</option>
                        <option value="active" <?= $b['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="completed" <?= $b['status']==='completed'?'selected':'' ?>>Completed</option>
                        <option value="cancelled" <?= $b['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                        <option value="rejected" <?= $b['status']==='rejected'?'selected':'' ?>>Rejected</option>
                    </select>
                </form>
                <a href="booking-details.php?id=<?= $b['id'] ?>" class="action-btn ab-view">
                    <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <button type="button" class="action-btn ab-delete" onclick="confirmDelete(<?= $b['id'] ?>, '<?= e($b['booking_number']) ?>')">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <?php endif; ?>

    <?php if ($total_pages > 1 || $total > 0): ?>
    <div class="pagination">
        <div class="pagination-info">
            Showing <?= number_format(($page-1)*$per_page+1) ?>–<?= number_format(min($page*$per_page,$total)) ?> of <?= number_format($total) ?> bookings
        </div>
        <div class="pagination-links">
            <?php
            $q = http_build_query(array_filter(['search'=>$search,'status'=>$status]));
            $base = 'bookings.php?' . ($q ? $q.'&' : '');
            ?>
            <a href="<?= $base ?>page=<?= max(1,$page-1) ?>" class="pg-btn <?= $page<=1?'disabled':'' ?>">‹</a>
            <?php
            $start = max(1, $page-2);
            $end = min($total_pages, $page+2);
            if ($start > 1) echo "<a href='{$base}page=1' class='pg-btn'>1</a>" . ($start>2?"<span style='padding:0 4px;color:#9ca3af'>…</span>":'');
            for ($i=$start;$i<=$end;$i++) echo "<a href='{$base}page=$i' class='pg-btn ".($i==$page?'active':'')."'>$i</a>";
            if ($end < $total_pages) echo ($end<$total_pages-1?"<span style='padding:0 4px;color:#9ca3af'>…</span>":'')."<a href='{$base}page=$total_pages' class='pg-btn'>$total_pages</a>";
            ?>
            <a href="<?= $base ?>page=<?= min($total_pages,$page+1) ?>" class="pg-btn <?= $page>=$total_pages?'disabled':'' ?>">›</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">Delete Booking</div>
        <div class="modal-body">Are you sure you want to delete booking <strong id="deleteBookingNumber"></strong>? This action cannot be undone.</div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="delete_id" id="deleteBookingId">
                <button type="submit" class="modal-btn modal-btn-confirm">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, bookingNumber) {
    document.getElementById('deleteBookingId').value = id;
    document.getElementById('deleteBookingNumber').textContent = bookingNumber;
    document.getElementById('deleteModal').classList.add('show');
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
