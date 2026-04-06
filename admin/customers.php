<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Customers Management";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $customer_id = (int)$_POST['customer_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $customer_id);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'update_customer_status', "Updated customer #$customer_id status to $new_status");
        setFlash('success', 'Customer status updated successfully');
    } else {
        setFlash('error', 'Failed to update customer status');
    }
    
    redirect('customers.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $customer_id = (int)$_GET['delete'];
    
    // Check if customer has orders/bookings
    $check = $conn->query("SELECT 
        (SELECT COUNT(*) FROM orders WHERE user_id = $customer_id) as order_count,
        (SELECT COUNT(*) FROM property_orders WHERE user_id = $customer_id) as property_order_count,
        (SELECT COUNT(*) FROM bookings WHERE user_id = $customer_id) as booking_count
    ");
    $counts = $check->fetch_assoc();
    
    if ($counts['order_count'] > 0 || $counts['property_order_count'] > 0 || $counts['booking_count'] > 0) {
        setFlash('error', 'Cannot delete customer with existing orders or bookings');
    } else {
        // Delete related data first
        $conn->query("DELETE FROM cart WHERE user_id = $customer_id");
        $conn->query("DELETE FROM wishlist WHERE user_id = $customer_id");
        $conn->query("DELETE FROM reviews WHERE user_id = $customer_id");
        
        // Delete customer
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $customer_id);
        
        if ($stmt->execute()) {
            logActivity('admin', $admin['id'], 'delete_customer', "Deleted customer #$customer_id");
            setFlash('success', 'Customer deleted successfully');
        } else {
            setFlash('error', 'Failed to delete customer');
        }
    }
    
    redirect('customers.php');
}

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
FROM users";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users $where_sql";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    if ($types) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_customers = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_customers = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_customers / $per_page);

// Get customers
$query = "SELECT u.*,
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
    (SELECT COUNT(*) FROM property_orders WHERE user_id = u.id) as property_order_count,
    (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count
FROM users u
$where_sql
ORDER BY u.created_at DESC
LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result();

require_once 'includes/header.php';
?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-card h3 {
    font-size: 14px;
    color: #666;
    margin: 0 0 10px 0;
    font-weight: 500;
}

.stat-card .number {
    font-size: 32px;
    font-weight: bold;
    color: #1a1a2e;
}

.filters-bar {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #666;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary {
    background: #f97316;
    color: white;
}

.btn-primary:hover {
    background: #ea580c;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.customers-table {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.customers-table table {
    width: 100%;
    border-collapse: collapse;
}

.customers-table th {
    background: #f9fafb;
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.customers-table td {
    padding: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.customer-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.customer-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f97316;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.customer-details h4 {
    margin: 0 0 3px 0;
    font-size: 14px;
    color: #1a1a2e;
}

.customer-details p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.status-select {
    padding: 6px 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 13px;
    cursor: pointer;
}

.status-select.active {
    border-color: #10b981;
    background: #d1fae5;
    color: #065f46;
}

.status-select.inactive {
    border-color: #ef4444;
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-info {
    background: #3b82f6;
    color: white;
}

.btn-info:hover {
    background: #2563eb;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-decoration: none;
    color: #374151;
}

.pagination a:hover {
    background: #f9fafb;
}

.pagination .active {
    background: #f97316;
    color: white;
    border-color: #f97316;
}

.customers-mobile {
    display: none;
}

.customer-card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.customer-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.customer-card-body {
    margin-bottom: 15px;
}

.customer-card-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
}

.customer-card-row strong {
    color: #666;
}

.customer-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.customer-card-actions .btn-sm {
    width: 100%;
    text-align: center;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 30px;
    border-radius: 10px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.modal-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.modal-content h3 {
    margin: 0 0 10px 0;
    color: #1a1a2e;
}

.modal-content p {
    margin: 0 0 20px 0;
    color: #666;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-form {
        flex-direction: column;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
    
    .customers-table {
        display: none;
    }
    
    .customers-mobile {
        display: block;
    }
}
</style>

<div class="content-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<?php if (getFlash('success')): ?>
    <div class="alert alert-success"><?php echo getFlash('success'); ?></div>
<?php endif; ?>

<?php if (getFlash('error')): ?>
    <div class="alert alert-error"><?php echo getFlash('error'); ?></div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Customers</h3>
        <div class="number"><?php echo number_format($stats['total']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Active</h3>
        <div class="number" style="color: #10b981;"><?php echo number_format($stats['active']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Inactive</h3>
        <div class="number" style="color: #ef4444;"><?php echo number_format($stats['inactive']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Joined Today</h3>
        <div class="number" style="color: #f97316;"><?php echo number_format($stats['today']); ?></div>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="Name, email, or phone..." value="<?php echo e($search); ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="customers.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Desktop Table -->
<div class="customers-table">
    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Contact</th>
                <th>Location</th>
                <th>Orders</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($customers->num_rows > 0): ?>
                <?php while ($customer = $customers->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="customer-info">
                                <?php if ($customer['profile_image']): ?>
                                    <img src="<?= UPLOAD_URL . 'profiles/' . e($customer['profile_image']) ?>" alt="" class="customer-avatar">
                                <?php else: ?>
                                    <div class="customer-avatar-placeholder">
                                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="customer-details">
                                    <h4><?php echo e($customer['name']); ?></h4>
                                    <p>ID: #<?php echo $customer['id']; ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><?php echo e($customer['email']); ?></div>
                            <div style="color: #666; font-size: 13px;"><?php echo e($customer['phone']); ?></div>
                        </td>
                        <td>
                            <?php if ($customer['city'] || $customer['country']): ?>
                                <div><?php echo e($customer['city']); ?></div>
                                <div style="color: #666; font-size: 13px;"><?php echo e($customer['country']); ?></div>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div>Products: <?php echo $customer['order_count']; ?></div>
                            <div style="font-size: 13px; color: #666;">
                                Properties: <?php echo $customer['property_order_count']; ?> | 
                                Bookings: <?php echo $customer['booking_count']; ?>
                            </div>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirmStatusChange(event, '<?php echo e($customer['name']); ?>', '<?php echo $customer['status']; ?>')">
                                <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" class="status-select <?php echo $customer['status']; ?>" onchange="this.form.submit()">
                                    <option value="active" <?php echo $customer['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $customer['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </form>
                        </td>
                        <td><?php echo formatDate($customer['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="customer-details.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">View</a>
                                <button onclick="confirmDelete(<?php echo $customer['id']; ?>, '<?php echo e($customer['name']); ?>')" class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                        No customers found
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="customers-mobile">
    <?php 
    $customers->data_seek(0);
    if ($customers->num_rows > 0): 
    ?>
        <?php while ($customer = $customers->fetch_assoc()): ?>
            <div class="customer-card">
                <div class="customer-card-header">
                    <?php if ($customer['profile_image']): ?>
                        <img src="<?= UPLOAD_URL . 'profiles/' . e($customer['profile_image']) ?>" alt="" class="customer-avatar">
                    <?php else: ?>
                        <div class="customer-avatar-placeholder">
                            <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 3px 0;"><?php echo e($customer['name']); ?></h4>
                        <p style="margin: 0; font-size: 13px; color: #666;">ID: #<?php echo $customer['id']; ?></p>
                    </div>
                    <span class="status-badge status-<?php echo $customer['status']; ?>">
                        <?php echo ucfirst($customer['status']); ?>
                    </span>
                </div>
                <div class="customer-card-body">
                    <div class="customer-card-row">
                        <strong>Email:</strong>
                        <span><?php echo e($customer['email']); ?></span>
                    </div>
                    <div class="customer-card-row">
                        <strong>Phone:</strong>
                        <span><?php echo e($customer['phone']); ?></span>
                    </div>
                    <div class="customer-card-row">
                        <strong>Location:</strong>
                        <span><?php echo $customer['city'] ? e($customer['city']) . ', ' . e($customer['country']) : '-'; ?></span>
                    </div>
                    <div class="customer-card-row">
                        <strong>Orders:</strong>
                        <span><?php echo $customer['order_count'] + $customer['property_order_count'] + $customer['booking_count']; ?></span>
                    </div>
                    <div class="customer-card-row">
                        <strong>Joined:</strong>
                        <span><?php echo formatDate($customer['created_at']); ?></span>
                    </div>
                </div>
                <div class="customer-card-actions">
                    <a href="customer-details.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">View Details</a>
                    <button onclick="confirmDelete(<?php echo $customer['id']; ?>, '<?php echo e($customer['name']); ?>')" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            No customers found
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon">🗑️</div>
        <h3>Delete Customer</h3>
        <p id="deleteMessage"></p>
        <div class="modal-actions">
            <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteCustomerId = null;

function confirmDelete(id, name) {
    deleteCustomerId = id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"? This action cannot be undone.`;
    document.getElementById('deleteModal').classList.add('active');
}

function executeDelete() {
    if (deleteCustomerId) {
        window.location.href = 'customers.php?delete=' + deleteCustomerId;
    }
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteCustomerId = null;
}

function confirmStatusChange(event, name, currentStatus) {
    const newStatus = event.target.status.value;
    if (newStatus === currentStatus) {
        return false;
    }
    return confirm(`Change status of "${name}" to ${newStatus}?`);
}

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
