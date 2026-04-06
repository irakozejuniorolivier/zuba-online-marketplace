<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(ADMIN_URL . '/orders.php');

// Fetch order with customer and payment method info
$order = $conn->query("SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       pm.name AS payment_method_name, pm.account_name, pm.account_number,
                       a.name AS approved_by_name
                       FROM orders o
                       LEFT JOIN users u ON o.user_id = u.id
                       LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
                       LEFT JOIN admins a ON o.approved_by = a.id
                       WHERE o.id = $id")->fetch_assoc();

if (!$order) redirect(ADMIN_URL . '/orders.php');

// Fetch order items
$items = $conn->query("SELECT oi.*, p.slug, p.stock,
                       (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS product_image
                       FROM order_items oi
                       LEFT JOIN products p ON oi.product_id = p.id
                       WHERE oi.order_id = $id")->fetch_all(MYSQLI_ASSOC);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $admin_note = trim($_POST['admin_note'] ?? '');
        $allowed = ['payment_submitted', 'approved', 'processing', 'shipped', 'delivered', 'cancelled', 'rejected'];
        
        if (in_array($new_status, $allowed)) {
            $stmt = $conn->prepare("UPDATE orders SET status = ?, admin_note = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_status, $admin_note, $id);
            $stmt->execute();
            $stmt->close();
            
            if ($new_status === 'approved' && $order['status'] !== 'approved') {
                $conn->query("UPDATE orders SET approved_by = {$admin['id']}, approved_at = NOW() WHERE id = $id");
            }
            
            logActivity('admin', $admin['id'], 'UPDATE_ORDER', "Updated order #{$order['order_number']} status to $new_status");
            setFlash('success', 'Order updated successfully.');
            redirect("order-details.php?id=$id");
        }
    }
}

// sidebar stats
$stats = [];
foreach ([
    'pending_orders' => "SELECT COUNT(*) FROM orders WHERE status='payment_submitted'",
    'pending_bookings' => "SELECT COUNT(*) FROM bookings WHERE status='payment_submitted'",
    'pending_prop_orders' => "SELECT COUNT(*) FROM property_orders WHERE status='payment_submitted'",
    'pending_reviews' => "SELECT COUNT(*) FROM reviews WHERE status='pending'",
] as $k => $q) {
    $stats[$k] = (int)$conn->query($q)->fetch_row()[0];
}

$page_title = 'Order Details';
require_once 'includes/header.php';
$flash = getFlash();
?>

<style>
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:12px; flex-wrap:wrap; }
    .page-header h2 { font-size:20px; font-weight:800; color:var(--text); }
    .btn-back { padding:9px 18px; background:#f3f4f6; color:#666; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }

    .order-grid { display:grid; grid-template-columns:2fr 1fr; gap:20px; align-items:start; }
    
    .card { background:var(--white); border:1px solid var(--border); border-radius:var(--radius); margin-bottom:20px; overflow:hidden; }
    .card-header { padding:16px 20px; border-bottom:1px solid var(--border); font-size:15px; font-weight:700; color:var(--text); display:flex; align-items:center; justify-content:space-between; }
    .card-body { padding:20px; }

    .order-status-badge { display:inline-block; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
    
    .info-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f3f4f6; }
    .info-row:last-child { border-bottom:none; }
    .info-label { font-size:13px; color:var(--text-muted); font-weight:600; }
    .info-value { font-size:13px; color:var(--text); font-weight:600; text-align:right; }

    .items-table { width:100%; border-collapse:collapse; }
    .items-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:var(--text-muted); padding:10px; text-align:left; background:#fafafa; border-bottom:1px solid var(--border); }
    .items-table td { font-size:13px; padding:12px 10px; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
    .items-table tr:last-child td { border-bottom:none; }

    .item-thumb { width:50px; height:50px; border-radius:8px; object-fit:cover; border:1px solid var(--border); background:#f3f4f6; }
    .item-thumb-placeholder { width:50px; height:50px; border-radius:8px; background:#f3f4f6; border:1px solid var(--border); display:flex; align-items:center; justify-content:center; }
    .item-name { font-weight:600; color:var(--text); }

    .payment-proof-img { max-width:100%; border-radius:8px; border:1px solid var(--border); cursor:pointer; transition:all .2s; }
    .payment-proof-img:hover { opacity:.9; }

    .form-group { margin-bottom:16px; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
    .form-group select, .form-group textarea { width:100%; padding:10px 14px; border:1px solid var(--border); border-radius:8px; font-size:13px; color:var(--text); outline:none; transition:border .18s; background:#fff; font-family:inherit; }
    .form-group select:focus, .form-group textarea:focus { border-color:var(--orange); }
    .form-group textarea { resize:vertical; min-height:100px; }

    .btn-submit { width:100%; padding:12px; background:linear-gradient(135deg,#f97316,#fb923c); color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; transition:all .2s; }
    .btn-submit:hover { opacity:.9; }

    .summary-row { display:flex; justify-content:space-between; padding:8px 0; font-size:13px; }
    .summary-row.total { font-size:16px; font-weight:700; color:var(--text); padding-top:12px; border-top:2px solid var(--border); margin-top:8px; }

    .timeline { position:relative; padding-left:30px; }
    .timeline-item { position:relative; padding-bottom:20px; }
    .timeline-item:last-child { padding-bottom:0; }
    .timeline-item::before { content:''; position:absolute; left:-30px; top:0; width:16px; height:16px; border-radius:50%; background:var(--orange); border:3px solid #fff; box-shadow:0 0 0 2px var(--orange); }
    .timeline-item::after { content:''; position:absolute; left:-23px; top:16px; width:2px; height:calc(100% - 16px); background:#e5e7eb; }
    .timeline-item:last-child::after { display:none; }
    .timeline-date { font-size:11px; color:var(--text-muted); font-weight:600; margin-bottom:4px; }
    .timeline-content { font-size:13px; color:var(--text); }

    /* Modal for image preview */
    .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; padding:20px; }
    .modal.show { display:flex; }
    .modal img { max-width:90%; max-height:90vh; border-radius:8px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.3); }
    .modal-close { position:absolute; top:20px; right:20px; width:40px; height:40px; border-radius:50%; background:#fff; color:#000; border:none; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; }

    @media (max-width:900px) {
        .order-grid { grid-template-columns:1fr; }
    }
    @media (max-width:640px) {
        .page-header { flex-direction:column; align-items:flex-start; }
        .page-header .btn-back { width:100%; text-align:center; }
        .card-body { padding:14px; }
        .items-table { font-size:12px; }
        .items-table th, .items-table td { padding:8px 6px; }
        .item-thumb, .item-thumb-placeholder { width:40px; height:40px; }
    }
</style>

<div class="page-header">
    <h2>📦 Order #<?= e($order['order_number']) ?></h2>
    <a href="orders.php" class="btn-back">← Back to Orders</a>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<div class="order-grid">
    <!-- LEFT COLUMN -->
    <div>
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">Order Items</div>
            <div class="card-body" style="padding:0;">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <?php if ($item['product_image']): ?>
                                        <img src="<?= UPLOAD_URL ?>products/<?= e($item['product_image']) ?>" class="item-thumb" alt="">
                                    <?php else: ?>
                                        <div class="item-thumb-placeholder">
                                            <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:#d1d5db;fill:none;stroke-width:1.5;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </div>
                                    <?php endif; ?>
                                    <span class="item-name"><?= e($item['product_name']) ?></span>
                                </div>
                            </td>
                            <td><?= formatCurrency($item['price']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><strong><?= formatCurrency($item['subtotal']) ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="card">
            <div class="card-header">Order Summary</div>
            <div class="card-body">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?= formatCurrency($order['subtotal']) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee:</span>
                    <span><?= formatCurrency($order['shipping_fee']) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span><?= formatCurrency($order['tax']) ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?= formatCurrency($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card">
            <div class="card-header">Customer Information</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value"><?= e($order['customer_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= e($order['customer_email']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value"><?= e($order['customer_phone'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <!-- Shipping Information -->
        <div class="card">
            <div class="card-header">Shipping Information</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value"><?= e($order['shipping_address'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">City:</span>
                    <span class="info-value"><?= e($order['shipping_city'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">State:</span>
                    <span class="info-value"><?= e($order['shipping_state'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Zip Code:</span>
                    <span class="info-value"><?= e($order['shipping_zip'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Note -->
        <?php if ($order['customer_note']): ?>
        <div class="card">
            <div class="card-header">Customer Note</div>
            <div class="card-body">
                <p style="font-size:13px;color:var(--text);line-height:1.6;margin:0;"><?= nl2br(e($order['customer_note'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN -->
    <div>
        <!-- Order Status -->
        <div class="card">
            <div class="card-header">Order Status</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="payment_submitted" <?= $order['status']==='payment_submitted'?'selected':'' ?>>Payment Submitted</option>
                            <option value="approved" <?= $order['status']==='approved'?'selected':'' ?>>Approved</option>
                            <option value="processing" <?= $order['status']==='processing'?'selected':'' ?>>Processing</option>
                            <option value="shipped" <?= $order['status']==='shipped'?'selected':'' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status']==='delivered'?'selected':'' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
                            <option value="rejected" <?= $order['status']==='rejected'?'selected':'' ?>>Rejected</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Admin Note</label>
                        <textarea name="admin_note" placeholder="Add internal notes..."><?= e($order['admin_note']) ?></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn-submit">Update Order</button>
                </form>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="card">
            <div class="card-header">Payment Information</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Method:</span>
                    <span class="info-value"><?= e($order['payment_method_name'] ?? '—') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account:</span>
                    <span class="info-value"><?= e($order['account_number'] ?? '—') ?></span>
                </div>
                <?php if ($order['payment_proof']): ?>
                <div style="margin-top:16px;">
                    <label style="font-size:12px;font-weight:600;color:#555;margin-bottom:8px;display:block;">Payment Proof:</label>
                    <img src="<?= UPLOAD_URL ?>payment_proofs/<?= e($order['payment_proof']) ?>" 
                         class="payment-proof-img" 
                         alt="Payment Proof"
                         onclick="showImageModal(this.src)">
                </div>
                <?php else: ?>
                <div style="margin-top:16px;padding:12px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;">
                    ⚠️ No payment proof uploaded yet
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header">Order Timeline</div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date"><?= formatDateTime($order['created_at']) ?></div>
                        <div class="timeline-content">Order placed</div>
                    </div>
                    <?php if ($order['approved_at']): ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?= formatDateTime($order['approved_at']) ?></div>
                        <div class="timeline-content">Approved by <?= e($order['approved_by_name'] ?? 'Admin') ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="timeline-item">
                        <div class="timeline-date"><?= formatDateTime($order['updated_at']) ?></div>
                        <div class="timeline-content">Last updated</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="card">
            <div class="card-header">Order Details</div>
            <div class="card-body">
                <div class="info-row">
                    <span class="info-label">Order ID:</span>
                    <span class="info-value">#<?= $order['id'] ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Number:</span>
                    <span class="info-value"><?= e($order['order_number']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Created:</span>
                    <span class="info-value"><?= formatDateTime($order['created_at']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Updated:</span>
                    <span class="info-value"><?= formatDateTime($order['updated_at']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal" id="imageModal" onclick="closeImageModal()">
    <button class="modal-close" onclick="closeImageModal()">×</button>
    <img id="modalImage" src="" alt="Payment Proof">
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.add('show');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeImageModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
