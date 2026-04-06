<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();
$page_title = "Manage Vehicles";

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $vehicle_ids = $_POST['vehicle_ids'] ?? [];
    
    if (!empty($vehicle_ids)) {
        $ids = implode(',', array_map('intval', $vehicle_ids));
        
        if ($action === 'activate') {
            $conn->query("UPDATE vehicles SET status = 'active' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_activate_vehicles', count($vehicle_ids) . ' vehicles activated');
            setFlash('success', count($vehicle_ids) . ' vehicles activated successfully');
        } elseif ($action === 'deactivate') {
            $conn->query("UPDATE vehicles SET status = 'inactive' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_deactivate_vehicles', count($vehicle_ids) . ' vehicles deactivated');
            setFlash('success', count($vehicle_ids) . ' vehicles deactivated successfully');
        } elseif ($action === 'delete') {
            $result = $conn->query("SELECT image_path FROM vehicle_images WHERE vehicle_id IN ($ids)");
            while ($row = $result->fetch_assoc()) {
                if (file_exists('../' . $row['image_path'])) {
                    unlink('../' . $row['image_path']);
                }
            }
            $conn->query("DELETE FROM vehicle_images WHERE vehicle_id IN ($ids)");
            $conn->query("DELETE FROM vehicles WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_delete_vehicles', count($vehicle_ids) . ' vehicles deleted');
            setFlash('success', count($vehicle_ids) . ' vehicles deleted successfully');
        }
        redirect('vehicles.php');
    }
}

// Handle single vehicle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("SELECT image_path FROM vehicle_images WHERE vehicle_id = $id");
    while ($row = $result->fetch_assoc()) {
        if (file_exists('../' . $row['image_path'])) {
            unlink('../' . $row['image_path']);
        }
    }
    $conn->query("DELETE FROM vehicle_images WHERE vehicle_id = $id");
    $conn->query("DELETE FROM vehicles WHERE id = $id");
    logActivity('admin', $admin['id'], 'delete_vehicle', "Vehicle ID: $id");
    setFlash('success', 'Vehicle deleted successfully');
    redirect('vehicles.php');
}

// Handle status change
if (isset($_POST['change_status'])) {
    $id = intval($_POST['vehicle_id']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    logActivity('admin', $admin['id'], 'change_vehicle_status', "Vehicle ID: $id, Status: $status");
    setFlash('success', 'Vehicle status updated successfully');
    redirect('vehicles.php');
}

// Handle featured toggle
if (isset($_POST['toggle_featured'])) {
    $id = intval($_POST['vehicle_id']);
    $featured = intval($_POST['featured']);
    $conn->query("UPDATE vehicles SET featured = $featured WHERE id = $id");
    logActivity('admin', $admin['id'], 'toggle_vehicle_featured', "Vehicle ID: $id, Featured: $featured");
    setFlash('success', 'Vehicle featured status updated');
    redirect('vehicles.php');
}

// Filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';

// Build query
$where = ["1=1"];
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where[] = "(v.brand LIKE '%$search_safe%' OR v.model LIKE '%$search_safe%' OR v.plate_number LIKE '%$search_safe%')";
}
if ($status_filter) {
    $where[] = "v.status = '" . $conn->real_escape_string($status_filter) . "'";
}
if ($category_filter) {
    $where[] = "v.category_id = " . intval($category_filter);
}

$where_sql = implode(' AND ', $where);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM vehicles v WHERE $where_sql");
$total_vehicles = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_vehicles / $per_page);

// Get vehicles
$query = "SELECT v.*, c.name as category_name,
          (SELECT image_path FROM vehicle_images WHERE vehicle_id = v.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM vehicles v
          LEFT JOIN categories c ON v.category_id = c.id
          WHERE $where_sql
          ORDER BY v.created_at DESC
          LIMIT $per_page OFFSET $offset";
$vehicles = $conn->query($query);

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE type = 'carrental' AND status = 'active' ORDER BY name");

// Stats
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'],
    'active' => $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'available'")->fetch_assoc()['count'],
    'rented' => $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'rented'")->fetch_assoc()['count'],
    'maintenance' => $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE status = 'maintenance'")->fetch_assoc()['count']
];

require_once 'includes/header.php';
?>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-header h1 { margin: 0; font-size: 1.75rem; color: #1a1a2e; }
.btn { padding: 0.625rem 1.25rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
.btn-primary { background: #f97316; color: white; }
.btn-primary:hover { background: #ea580c; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
.alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 50px; height: 50px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-details { flex: 1; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; }
.stat-label { color: #666; font-size: 0.875rem; margin-top: 0.25rem; }
.card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.card-header h3 { margin: 0; font-size: 1.125rem; color: #1a1a2e; }
.card-body { padding: 1.5rem; }
.filter-form { display: flex; gap: 1rem; flex-wrap: wrap; }
.form-row { display: flex; gap: 1rem; flex-wrap: wrap; flex: 1; }
.form-group { flex: 1; min-width: 200px; }
.form-control { width: 100%; padding: 0.625rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; }
.form-control:focus { outline: none; border-color: #f97316; }
.bulk-actions { display: flex; gap: 0.5rem; align-items: center; }
.vehicles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
.vehicle-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; position: relative; }
.vehicle-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.12); transform: translateY(-4px); }
.vehicle-checkbox { position: absolute; top: 12px; left: 12px; width: 20px; height: 20px; z-index: 10; cursor: pointer; accent-color: #f97316; }
.featured-badge { position: absolute; top: 12px; right: 12px; background: #f59e0b; color: white; padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; z-index: 10; }
.vehicle-image { width: 100%; height: 200px; overflow: hidden; background: #f3f4f6; }
.vehicle-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.vehicle-card:hover .vehicle-image img { transform: scale(1.05); }
.vehicle-body { padding: 1.25rem; }
.vehicle-category { color: #f97316; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
.vehicle-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.75rem 0; line-height: 1.4; }
.vehicle-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; font-size: 0.8125rem; color: #6b7280; }
.vehicle-meta span { display: block; }
.vehicle-footer { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin-bottom: 12px; }
.vehicle-price { display: flex; flex-direction: column; }
.price-main { font-size: 1.125rem; font-weight: 700; color: #f97316; }
.price-alt { font-size: 0.75rem; color: #6b7280; margin-top: 2px; }
.vehicle-views { display: flex; align-items: center; gap: 4px; font-size: 0.8125rem; color: #6b7280; }
.status-form { margin-bottom: 12px; }
.status-select { width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.status-select:focus { outline: none; border-color: #f97316; }
.status-available { background: #dcfce7; color: #16a34a; border-color: #16a34a; }
.status-rented { background: #dbeafe; color: #2563eb; border-color: #2563eb; }
.status-maintenance { background: #fef3c7; color: #d97706; border-color: #d97706; }
.status-inactive { background: #f3f4f6; color: #6b7280; border-color: #6b7280; }
.vehicle-actions { display: grid; grid-template-columns: 1fr auto auto; gap: 0.5rem; }
.vehicle-actions .btn { padding: 0.5rem 1rem; font-size: 0.875rem; text-align: center; }
.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state svg { margin: 0 auto 1.5rem; color: #e5e7eb; }
.empty-state h3 { font-size: 1.5rem; color: #1a1a2e; margin: 0 0 0.5rem 0; }
.empty-state p { color: #6b7280; margin: 0 0 1.5rem 0; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.75rem; padding: 1.5rem; }
.page-info { color: #6b7280; font-size: 0.875rem; }
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; max-width: 480px; width: 90%; text-align: center; }
.modal-icon { width: 64px; height: 64px; margin: 0 auto 1.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; }
.modal-icon.icon-danger { background: #fee2e2; color: #ef4444; }
.modal-icon.icon-warning { background: #fef3c7; color: #f59e0b; }
.modal-icon.icon-info { background: #dbeafe; color: #2563eb; }
.modal-title { margin: 0 0 1rem 0; color: #1a1a2e; font-size: 1.5rem; }
.modal-message { margin: 0 0 1.5rem 0; color: #6b7280; font-size: 0.9375rem; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .page-header .btn { width: 100%; justify-content: center; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .stat-card { padding: 1rem; }
    .stat-icon { width: 40px; height: 40px; }
    .stat-value { font-size: 1.5rem; }
    .filter-form, .form-row { flex-direction: column; }
    .form-group { min-width: 100%; }
    .card-header { flex-direction: column; gap: 1rem; align-items: stretch; }
    .bulk-actions { flex-direction: column; width: 100%; }
    .bulk-actions select, .bulk-actions button { width: 100%; }
    .vehicles-grid { grid-template-columns: 1fr; }
    .vehicle-actions { grid-template-columns: 1fr; }
    .vehicle-actions .btn:first-child { grid-column: 1; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn { width: 100%; }
}
</style>

<div class="page-header">
    <h1><?php echo $page_title; ?></h1>
    <a href="add-vehicle.php" class="btn btn-primary">+ Add Vehicle</a>
</div>

<?php if (getFlash('success')): ?>
    <div class="alert alert-success"><?php echo getFlash('success'); ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #fff7ed; color: #f97316;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Vehicles</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?php echo $stats['active']; ?></div>
            <div class="stat-label">Available</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?php echo $stats['rented']; ?></div>
            <div class="stat-label">Currently Rented</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?php echo $stats['maintenance']; ?></div>
            <div class="stat-label">In Maintenance</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Filter Vehicles</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search by brand, model, plate..." value="<?php echo e($search); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="rented" <?php echo $status_filter === 'rented' ? 'selected' : ''; ?>>Rented</option>
                        <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="vehicles.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Vehicles (<?php echo $total_vehicles; ?>)</h3>
        <form method="POST" id="bulkForm" class="bulk-actions">
            <select name="bulk_action" class="form-control" required>
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button type="button" onclick="submitBulkAction()" class="btn btn-secondary">Apply</button>
        </form>
    </div>
    <div class="card-body">
        <?php if ($vehicles->num_rows > 0): ?>
            <div class="vehicles-grid">
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <div class="vehicle-card">
                        <input type="checkbox" name="vehicle_ids[]" value="<?php echo $vehicle['id']; ?>" form="bulkForm" class="vehicle-checkbox">
                        
                        <?php if ($vehicle['featured']): ?>
                            <span class="featured-badge">Featured</span>
                        <?php endif; ?>
                        
                        <div class="vehicle-image">
                            <?php if ($vehicle['primary_image']): ?>
                                <img src="<?= SITE_URL . '/' . e($vehicle['primary_image']) ?>" alt="<?= e($vehicle['brand'] . ' ' . $vehicle['model']) ?>">
                            <?php else: ?>
                                <img src="../assets/images/placeholder-vehicle.jpg" alt="No image">
                            <?php endif; ?>
                        </div>
                        
                        <div class="vehicle-body">
                            <div class="vehicle-category"><?php echo e($vehicle['category_name'] ?? 'Uncategorized'); ?></div>
                            <h4 class="vehicle-title"><?php echo e($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']); ?></h4>
                            
                            <div class="vehicle-meta">
                                <span><strong>Type:</strong> <?php echo e($vehicle['vehicle_type']); ?></span>
                                <span><strong>Plate:</strong> <?php echo e($vehicle['plate_number']); ?></span>
                                <span><strong>Seats:</strong> <?php echo $vehicle['seats']; ?></span>
                                <span><strong>Transmission:</strong> <?php echo e($vehicle['transmission']); ?></span>
                            </div>
                            
                            <div class="vehicle-footer">
                                <div class="vehicle-price">
                                    <div class="price-main"><?php echo formatCurrency($vehicle['daily_rate']); ?>/day</div>
                                    <?php if ($vehicle['weekly_rate']): ?>
                                        <div class="price-alt"><?php echo formatCurrency($vehicle['weekly_rate']); ?>/week</div>
                                    <?php endif; ?>
                                </div>
                                <div class="vehicle-views">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <?php echo $vehicle['views']; ?>
                                </div>
                            </div>
                            
                            <form method="POST" class="status-form">
                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                <select name="status" class="status-select status-<?php echo $vehicle['status']; ?>" onchange="confirmStatusChange(this)">
                                    <option value="available" <?php echo $vehicle['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="rented" <?php echo $vehicle['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                    <option value="maintenance" <?php echo $vehicle['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="inactive" <?php echo $vehicle['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </form>
                            
                            <div class="vehicle-actions">
                                <a href="edit-vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                    <input type="hidden" name="featured" value="<?php echo $vehicle['featured'] ? 0 : 1; ?>">
                                    <button type="button" onclick="confirmFeatured(this, <?php echo $vehicle['featured'] ? 0 : 1; ?>)" class="btn btn-sm btn-secondary">
                                        <?php echo $vehicle['featured'] ? 'Unfeature' : 'Feature'; ?>
                                    </button>
                                </form>
                                <button type="button" onclick="confirmDelete(<?php echo $vehicle['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&category=<?php echo urlencode($category_filter); ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                </svg>
                <h3>No vehicles found</h3>
                <p>Start by adding your first vehicle to the system.</p>
                <a href="add-vehicle.php" class="btn btn-primary">Add Vehicle</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon"></div>
        <h3 class="modal-title"></h3>
        <p class="modal-message"></p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn modal-confirm-btn"></button>
        </div>
    </div>
</div>

<script>
function showConfirmModal(title, message, iconClass, btnText, btnClass, callback) {
    const modal = document.getElementById('confirmModal');
    modal.querySelector('.modal-icon').className = 'modal-icon ' + iconClass;
    modal.querySelector('.modal-title').textContent = title;
    modal.querySelector('.modal-message').textContent = message;
    const confirmBtn = modal.querySelector('.modal-confirm-btn');
    confirmBtn.textContent = btnText;
    confirmBtn.className = 'btn ' + btnClass;
    confirmBtn.onclick = callback;
    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function confirmDelete(id) {
    showConfirmModal(
        'Delete Vehicle',
        'Are you sure you want to delete this vehicle? This action cannot be undone.',
        'icon-danger',
        'Delete',
        'btn-danger',
        () => { window.location.href = 'vehicles.php?delete=' + id; }
    );
}

function confirmStatusChange(select) {
    const form = select.closest('form');
    const originalValue = select.getAttribute('data-original') || select.value;
    select.setAttribute('data-original', originalValue);
    
    showConfirmModal(
        'Change Status',
        'Are you sure you want to change this vehicle status?',
        'icon-warning',
        'Change Status',
        'btn-primary',
        () => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'change_status';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    );
}

function confirmFeatured(btn, featured) {
    const form = btn.closest('form');
    showConfirmModal(
        featured ? 'Feature Vehicle' : 'Unfeature Vehicle',
        featured ? 'Mark this vehicle as featured?' : 'Remove featured status from this vehicle?',
        'icon-info',
        'Confirm',
        'btn-primary',
        () => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'toggle_featured';
            input.value = '1';
            form.appendChild(input);
            form.submit();
        }
    );
}

function submitBulkAction() {
    const form = document.getElementById('bulkForm');
    const action = form.querySelector('select[name="bulk_action"]').value;
    const checked = form.querySelectorAll('input[name="vehicle_ids[]"]:checked');
    
    if (!action) {
        alert('Please select an action');
        return;
    }
    
    if (checked.length === 0) {
        alert('Please select at least one vehicle');
        return;
    }
    
    let title, message, btnText, btnClass;
    
    if (action === 'delete') {
        title = 'Delete Vehicles';
        message = `Are you sure you want to delete ${checked.length} vehicle(s)? This action cannot be undone.`;
        btnText = 'Delete';
        btnClass = 'btn-danger';
    } else if (action === 'activate') {
        title = 'Activate Vehicles';
        message = `Activate ${checked.length} vehicle(s)?`;
        btnText = 'Activate';
        btnClass = 'btn-primary';
    } else {
        title = 'Deactivate Vehicles';
        message = `Deactivate ${checked.length} vehicle(s)?`;
        btnText = 'Deactivate';
        btnClass = 'btn-primary';
    }
    
    showConfirmModal(title, message, 'icon-warning', btnText, btnClass, () => { form.submit(); });
}

window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<style>
.vehicles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    margin-top: 20px;
}

.vehicle-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    position: relative;
}

.vehicle-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    transform: translateY(-4px);
}

.vehicle-checkbox {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 20px;
    height: 20px;
    z-index: 10;
    cursor: pointer;
}

.vehicle-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f3f4f6;
}

.vehicle-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.vehicle-card:hover .vehicle-image img {
    transform: scale(1.05);
}

.vehicle-body {
    padding: 16px;
}

.vehicle-category {
    font-size: 12px;
    color: #f97316;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.vehicle-title {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 0 0 12px 0;
}

.vehicle-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-bottom: 12px;
    font-size: 13px;
    color: #6b7280;
}

.vehicle-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 12px;
}

.vehicle-price {
    display: flex;
    flex-direction: column;
}

.price-main {
    font-size: 18px;
    font-weight: 700;
    color: #f97316;
}

.price-alt {
    font-size: 12px;
    color: #6b7280;
}

.vehicle-views {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #6b7280;
}

.status-form {
    margin-bottom: 12px;
}

.status-select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}

.status-active { background: #dcfce7; color: #16a34a; border-color: #16a34a; }
.status-rented { background: #dbeafe; color: #2563eb; border-color: #2563eb; }
.status-maintenance { background: #fef3c7; color: #d97706; border-color: #d97706; }
.status-inactive { background: #f3f4f6; color: #6b7280; border-color: #6b7280; }

.vehicle-actions {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 8px;
}

@media (max-width: 768px) {
    .vehicles-grid {
        grid-template-columns: 1fr;
    }
    
    .vehicle-actions {
        grid-template-columns: 1fr;
    }
    
    .vehicle-actions .btn:first-child {
        grid-column: 1 / -1;
    }
    
    .vehicle-actions .btn:not(:first-child) {
        grid-column: auto;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
