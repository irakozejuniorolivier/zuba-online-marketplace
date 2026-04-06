<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = 'Manage Properties';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $property_ids = $_POST['property_ids'] ?? [];
    
    if (!empty($property_ids)) {
        $ids = implode(',', array_map('intval', $property_ids));
        
        if ($action === 'activate') {
            $conn->query("UPDATE properties SET status = 'active' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_activate_properties', 'Activated ' . count($property_ids) . ' properties');
            setFlash('success', count($property_ids) . ' properties activated successfully');
        } elseif ($action === 'deactivate') {
            $conn->query("UPDATE properties SET status = 'inactive' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_deactivate_properties', 'Deactivated ' . count($property_ids) . ' properties');
            setFlash('success', count($property_ids) . ' properties deactivated successfully');
        } elseif ($action === 'delete') {
            // Delete property images first
            $img_result = $conn->query("SELECT image_path FROM property_images WHERE property_id IN ($ids)");
            while ($img = $img_result->fetch_assoc()) {
                @unlink('../uploads/properties/' . $img['image_path']);
            }
            $conn->query("DELETE FROM property_images WHERE property_id IN ($ids)");
            $conn->query("DELETE FROM properties WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_delete_properties', 'Deleted ' . count($property_ids) . ' properties');
            setFlash('success', count($property_ids) . ' properties deleted successfully');
        }
        redirect('properties.php');
    }
}

// Handle single delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get property title
    $prop = $conn->query("SELECT title FROM properties WHERE id = $id")->fetch_assoc();
    
    // Delete images
    $img_result = $conn->query("SELECT image_path FROM property_images WHERE property_id = $id");
    while ($img = $img_result->fetch_assoc()) {
        @unlink('../uploads/properties/' . $img['image_path']);
    }
    
    $conn->query("DELETE FROM property_images WHERE property_id = $id");
    $conn->query("DELETE FROM properties WHERE id = $id");
    
    logActivity('admin', $admin['id'], 'delete_property', 'Deleted property: ' . $prop['title']);
    setFlash('success', 'Property deleted successfully');
    redirect('properties.php');
}

// Handle status change
if (isset($_GET['change_status'])) {
    $id = intval($_GET['change_status']);
    $status = $_GET['status'];
    
    $conn->query("UPDATE properties SET status = '$status' WHERE id = $id");
    logActivity('admin', $admin['id'], 'change_property_status', "Changed property #$id status to $status");
    setFlash('success', 'Property status updated');
    redirect('properties.php');
}

// Handle featured toggle
if (isset($_GET['toggle_featured'])) {
    $id = intval($_GET['toggle_featured']);
    $conn->query("UPDATE properties SET featured = NOT featured WHERE id = $id");
    logActivity('admin', $admin['id'], 'toggle_property_featured', "Toggled featured for property #$id");
    redirect('properties.php');
}

// Filters
$search = $_GET['search'] ?? '';
$listing_type = $_GET['listing_type'] ?? '';
$status = $_GET['status'] ?? '';
$category_id = $_GET['category_id'] ?? '';

// Build query
$where = ['1=1'];
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where[] = "(p.title LIKE '%$search_safe%' OR p.city LIKE '%$search_safe%' OR p.address LIKE '%$search_safe%')";
}
if ($listing_type) {
    $where[] = "p.listing_type = '$listing_type'";
}
if ($status) {
    $where[] = "p.status = '$status'";
}
if ($category_id) {
    $where[] = "p.category_id = " . intval($category_id);
}

$where_clause = implode(' AND ', $where);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM properties p WHERE $where_clause");
$total_properties = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_properties / $per_page);

// Get properties
$query = "SELECT p.*, c.name as category_name,
          (SELECT image_path FROM property_images WHERE property_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM properties p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $where_clause
          ORDER BY p.created_at DESC
          LIMIT $per_page OFFSET $offset";
$properties = $conn->query($query);

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE type = 'realestate' AND status = 'active' ORDER BY name");

// Stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN listing_type = 'sale' THEN 1 ELSE 0 END) as for_sale,
    SUM(CASE WHEN listing_type = 'rent' THEN 1 ELSE 0 END) as for_rent,
    SUM(CASE WHEN status = 'sold' OR status = 'rented' THEN 1 ELSE 0 END) as sold_rented
    FROM properties";
$stats = $conn->query($stats_query)->fetch_assoc();

require_once 'includes/header.php';
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}
.page-header h1 {
    margin: 0;
    font-size: 1.75rem;
    color: #1a1a2e;
}
.page-header p {
    margin: 0.25rem 0 0 0;
    color: #666;
}
.btn {
    padding: 0.625rem 1.25rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
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
.btn-danger {
    background: #ef4444;
    color: white;
}
.btn-danger:hover {
    background: #dc2626;
}
.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8125rem;
}
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}
.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}
.stat-details {
    flex: 1;
}
.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1a1a2e;
}
.stat-label {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}
.filters-form {
    padding: 1.5rem;
}
.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}
.filters-grid input,
.filters-grid select {
    padding: 0.625rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    width: 100%;
}
.filters-grid input:focus,
.filters-grid select:focus {
    outline: none;
    border-color: #f97316;
}
.filters-actions {
    display: flex;
    gap: 0.75rem;
}
.table-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.bulk-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.bulk-actions select {
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
}
.table-info {
    color: #666;
    font-size: 0.875rem;
}
.table-responsive {
    overflow-x: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
}
.table thead {
    background: #f9fafb;
}
.table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
}
.table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    font-size: 0.875rem;
}
.table tbody tr:hover {
    background: #f9fafb;
}
.table-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}
.table-img-placeholder {
    width: 60px;
    height: 60px;
    background: #f3f4f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.5rem;
}
.property-info strong {
    display: block;
    color: #1a1a2e;
    margin-bottom: 0.25rem;
}
.text-muted {
    color: #6b7280;
    font-size: 0.8125rem;
}
.badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}
.badge-success {
    background: #d1fae5;
    color: #065f46;
}
.badge-info {
    background: #dbeafe;
    color: #1e40af;
}
.badge-warning {
    background: #fef3c7;
    color: #92400e;
}
.status-select {
    padding: 0.5rem 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.status-select:focus {
    outline: none;
    border-color: #f97316;
}
.status-select.status-active {
    background: #d1fae5;
    color: #065f46;
    border-color: #10b981;
}
.status-select.status-inactive {
    background: #fee2e2;
    color: #991b1b;
    border-color: #ef4444;
}
.status-select.status-sold {
    background: #e0e7ff;
    color: #3730a3;
    border-color: #6366f1;
}
.status-select.status-rented {
    background: #dbeafe;
    color: #1e40af;
    border-color: #3b82f6;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
}
.btn-icon {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #f3f4f6;
    border-radius: 4px;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-icon:hover {
    background: #e5e7eb;
    color: #1a1a2e;
}
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
}
.page-link {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    color: #374151;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.page-link:hover {
    background: #f9fafb;
    border-color: #f97316;
    color: #f97316;
}
.page-link.active {
    background: #f97316;
    color: white;
    border-color: #f97316;
}
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.modal.show {
    display: flex;
}
.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}
.modal-content h3 {
    margin: 0 0 1rem 0;
    color: #1a1a2e;
}
.modal-content p {
    margin: 0.5rem 0;
    color: #374151;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    margin-top: 1.5rem;
}
.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.property-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    position: relative;
}
.property-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    transform: translateY(-4px);
}
.property-card-checkbox {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    z-index: 10;
}
.property-card-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #f97316;
}
.property-featured-badge {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #f59e0b;
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.property-card-image {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;
    background: #f3f4f6;
}
.property-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.property-card:hover .property-card-image img {
    transform: scale(1.05);
}
.property-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 4rem;
}
.property-card-badges {
    position: absolute;
    bottom: 0.75rem;
    left: 0.75rem;
}
.property-card-body {
    padding: 1.25rem;
}
.property-card-category {
    color: #f97316;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}
.property-card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.property-card-location {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}
.property-card-location i {
    color: #f97316;
}
.property-card-details {
    display: flex;
    gap: 1rem;
    padding: 1rem 0;
    border-top: 1px solid #f3f4f6;
    border-bottom: 1px solid #f3f4f6;
    margin-bottom: 1rem;
}
.property-detail-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: #374151;
}
.property-detail-item i {
    color: #9ca3af;
    font-size: 0.875rem;
}
.property-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}
.property-card-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f97316;
}
.property-card-price span {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 400;
}
.property-card-views {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #6b7280;
    font-size: 0.875rem;
}
.property-card-status {
    margin-bottom: 1rem;
}
.property-card-status select {
    width: 100%;
}
.property-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.5rem;
}
.btn-action span {
    display: inline;
}
.btn-action {
    padding: 0.625rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    text-decoration: none;
}
.btn-action-primary {
    background: #f97316;
    color: white;
}
.btn-action-primary:hover {
    background: #ea580c;
    transform: translateY(-1px);
}
.btn-action-warning {
    background: #f59e0b;
    color: white;
    border: 2px solid #f59e0b;
}
.btn-action-warning:hover {
    background: #d97706;
    border-color: #d97706;
}
.btn-action-danger {
    background: white;
    color: #ef4444;
    border: 2px solid #ef4444;
}
.btn-action-danger:hover {
    background: #ef4444;
    color: white;
}
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
}
.empty-state i {
    font-size: 4rem;
    color: #e5e7eb;
    margin-bottom: 1rem;
}
.empty-state h3 {
    font-size: 1.5rem;
    color: #1a1a2e;
    margin: 0 0 0.5rem 0;
}
.empty-state p {
    color: #6b7280;
    margin: 0 0 1.5rem 0;
}
.modal-confirm {
    text-align: center;
    max-width: 420px;
}
.modal-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1.5rem;
    background: #fee2e2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #ef4444;
}
.modal-confirm h3 {
    font-size: 1.5rem;
}
.modal-confirm p {
    font-size: 0.9375rem;
}
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .page-header .btn {
        width: 100%;
        justify-content: center;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .stat-card {
        padding: 1rem;
    }
    .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }
    .stat-value {
        font-size: 1.5rem;
    }
    .stat-label {
        font-size: 0.75rem;
        line-height: 1.3;
    }
    .filters-grid {
        grid-template-columns: 1fr;
    }
    .filters-actions {
        flex-direction: column;
    }
    .filters-actions .btn {
        width: 100%;
        justify-content: center;
    }
    .table-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    .bulk-actions {
        flex-direction: column;
    }
    .bulk-actions select,
    .bulk-actions .btn {
        width: 100%;
    }
    .properties-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .property-card-image {
        height: 200px;
    }
    .property-card-body {
        padding: 1rem;
    }
    .property-card-title {
        font-size: 1rem;
    }
    .property-card-price {
        font-size: 1.25rem;
    }
    .property-card-details {
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .property-card-actions {
        grid-template-columns: 1fr 1fr;
    }
    .property-card-actions .btn-action:first-child {
        grid-column: 1 / -1;
    }
    .btn-action {
        font-size: 0.8125rem;
        padding: 0.5rem 0.75rem;
    }
    .btn-action span {
        display: inline;
    }
    .pagination {
        flex-wrap: wrap;
        gap: 0.375rem;
    }
    .page-link {
        padding: 0.375rem 0.625rem;
        font-size: 0.8125rem;
    }
    .modal-content {
        margin: 1rem;
        padding: 1.5rem;
    }
    .modal-actions {
        flex-direction: column;
    }
    .modal-actions .btn {
        width: 100%;
    }
}
@media (max-width: 480px) {
    .property-card-details {
        font-size: 0.8125rem;
    }
    .property-detail-item {
        font-size: 0.8125rem;
    }
    .btn-action {
        font-size: 0.75rem;
        padding: 0.5rem;
    }
}
</style>

<div class="page-header">
    <div>
        <h1>Properties</h1>
        <p>Manage real estate listings</p>
    </div>
    <a href="add-property.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Property
    </a>
</div>

<?php if ($flash = getFlash()): ?>
<div class="alert alert-<?= $flash['type'] ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: #f97316;">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['total']) ?></div>
            <div class="stat-label">Total Properties</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">
            <i class="fas fa-tag"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['for_sale']) ?></div>
            <div class="stat-label">For Sale</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #3b82f6;">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['for_rent']) ?></div>
            <div class="stat-label">For Rent</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #8b5cf6;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['sold_rented']) ?></div>
            <div class="stat-label">Sold/Rented</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" class="filters-form">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="Search by title, city, address..." value="<?= e($search) ?>">
            
            <select name="listing_type">
                <option value="">All Listing Types</option>
                <option value="sale" <?= $listing_type === 'sale' ? 'selected' : '' ?>>For Sale</option>
                <option value="rent" <?= $listing_type === 'rent' ? 'selected' : '' ?>>For Rent</option>
            </select>
            
            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="sold" <?= $status === 'sold' ? 'selected' : '' ?>>Sold</option>
                <option value="rented" <?= $status === 'rented' ? 'selected' : '' ?>>Rented</option>
            </select>
            
            <select name="category_id">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="filters-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="properties.php" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </div>
    </form>
</div>

<!-- Bulk Actions -->
<form method="POST" id="bulkForm">
    <div class="card">
        <div class="table-header">
            <div class="bulk-actions">
                <select name="bulk_action" id="bulkAction">
                    <option value="">Bulk Actions</option>
                    <option value="activate">Activate</option>
                    <option value="deactivate">Deactivate</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Apply</button>
            </div>
            <div class="table-info">
                Showing <?= $properties->num_rows ?> of <?= number_format($total_properties) ?> properties
            </div>
        </div>

        <!-- Properties Grid -->
        <div class="properties-grid">
            <?php if ($properties->num_rows > 0): ?>
                <?php while ($prop = $properties->fetch_assoc()): ?>
                <div class="property-card">
                    <div class="property-card-checkbox">
                        <input type="checkbox" name="property_ids[]" value="<?= $prop['id'] ?>" class="property-checkbox">
                    </div>
                    
                    <?php if ($prop['featured']): ?>
                    <div class="property-featured-badge">
                        <i class="fas fa-star"></i> Featured
                    </div>
                    <?php endif; ?>
                    
                    <div class="property-card-image">
                        <?php if ($prop['primary_image']): ?>
                        <img src="<?= UPLOAD_URL . 'properties/' . e($prop['primary_image']) ?>" alt="<?= e($prop['title']) ?>">
                        <?php else: ?>
                        <div class="property-card-placeholder">
                            <i class="fas fa-building"></i>
                        </div>
                        <?php endif; ?>
                        <div class="property-card-badges">
                            <span class="badge badge-<?= $prop['listing_type'] === 'sale' ? 'success' : 'info' ?>">
                                <?= $prop['listing_type'] === 'sale' ? 'For Sale' : 'For Rent' ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="property-card-body">
                        <div class="property-card-category"><?= e($prop['category_name']) ?></div>
                        <h3 class="property-card-title"><?= e($prop['title']) ?></h3>
                        
                        <div class="property-card-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= e($prop['city']) ?><?= $prop['state'] ? ', ' . e($prop['state']) : '' ?>
                        </div>
                        
                        <div class="property-card-details">
                            <?php if ($prop['bedrooms']): ?>
                            <div class="property-detail-item">
                                <i class="fas fa-bed"></i>
                                <span><?= $prop['bedrooms'] ?> Beds</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($prop['bathrooms']): ?>
                            <div class="property-detail-item">
                                <i class="fas fa-bath"></i>
                                <span><?= $prop['bathrooms'] ?> Baths</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($prop['area']): ?>
                            <div class="property-detail-item">
                                <i class="fas fa-ruler-combined"></i>
                                <span><?= number_format($prop['area']) ?> <?= e($prop['area_unit']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-card-footer">
                            <div class="property-card-price">
                                <?= formatCurrency($prop['price']) ?>
                                <?php if ($prop['listing_type'] === 'rent' && $prop['rent_period']): ?>
                                <span>/<?= e($prop['rent_period']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="property-card-views">
                                <i class="fas fa-eye"></i> <?= number_format($prop['views']) ?>
                            </div>
                        </div>
                        
                        <div class="property-card-status">
                            <select onchange="confirmStatusChange(<?= $prop['id'] ?>, this.value, '<?= e($prop['title']) ?>')" class="status-select status-<?= $prop['status'] ?>">
                                <option value="active" <?= $prop['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $prop['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="sold" <?= $prop['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
                                <option value="rented" <?= $prop['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
                            </select>
                        </div>
                        
                        <div class="property-card-actions">
                            <a href="edit-property.php?id=<?= $prop['id'] ?>" class="btn-action btn-action-primary" title="Edit">
                                <i class="fas fa-edit"></i> <span>Edit</span>
                            </a>
                            <button type="button" onclick="confirmFeatured(<?= $prop['id'] ?>, <?= $prop['featured'] ? 'false' : 'true' ?>, '<?= e($prop['title']) ?>')" class="btn-action btn-action-warning" title="Toggle Featured">
                                <i class="fas fa-star"></i> <span><?= $prop['featured'] ? 'Featured' : 'Feature' ?></span>
                            </button>
                            <button type="button" onclick="confirmDelete(<?= $prop['id'] ?>, '<?= e($prop['title']) ?>')" class="btn-action btn-action-danger" title="Delete">
                                <i class="fas fa-trash"></i> <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-building"></i>
                    <h3>No Properties Found</h3>
                    <p>Start by adding your first property listing</p>
                    <a href="add-property.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Property
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $query_params = $_GET;
            unset($query_params['page']);
            $query_string = http_build_query($query_params);
            $base_url = 'properties.php?' . ($query_string ? $query_string . '&' : '');
            
            if ($page > 1): ?>
                <a href="<?= $base_url ?>page=<?= $page - 1 ?>" class="page-link">&laquo; Previous</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
                    <a href="<?= $base_url ?>page=<?= $i ?>" class="page-link <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php elseif (abs($i - $page) == 3): ?>
                    <span class="page-link">...</span>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="<?= $base_url ?>page=<?= $page + 1 ?>" class="page-link">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</form>

<!-- Modern Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content modal-confirm">
        <div class="modal-icon" id="modalIcon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 id="modalTitle">Confirm Action</h3>
        <p id="modalMessage">Are you sure you want to proceed?</p>
        <div class="modal-actions">
            <button type="button" onclick="closeConfirmModal()" class="btn btn-secondary">Cancel</button>
            <button type="button" id="confirmBtn" class="btn btn-danger">Confirm</button>
        </div>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.property-checkbox').forEach(cb => cb.checked = this.checked);
});

// Modern Confirmation Modal
let confirmCallback = null;

function showConfirmModal(title, message, iconClass, btnText, btnClass, callback) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').innerHTML = message;
    document.getElementById('modalIcon').innerHTML = `<i class="${iconClass}"></i>`;
    
    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.textContent = btnText;
    confirmBtn.className = 'btn ' + btnClass;
    
    confirmCallback = callback;
    document.getElementById('confirmModal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('show');
    confirmCallback = null;
}

document.getElementById('confirmBtn')?.addEventListener('click', function() {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

// Delete confirmation
function confirmDelete(id, name) {
    showConfirmModal(
        'Delete Property',
        `Are you sure you want to delete "<strong>${name}</strong>"?<br><small style="color: #6b7280; margin-top: 0.5rem; display: block;">This action cannot be undone. All property images will also be deleted.</small>`,
        'fas fa-trash',
        'Delete Property',
        'btn-danger',
        () => window.location.href = 'properties.php?delete=' + id
    );
}

// Status change confirmation
function confirmStatusChange(id, status, name) {
    const select = event.target;
    const oldValue = select.getAttribute('data-current') || select.value;
    
    showConfirmModal(
        'Change Status',
        `Change status of "<strong>${name}</strong>" to <strong>${status}</strong>?`,
        'fas fa-exchange-alt',
        'Change Status',
        'btn-primary',
        () => window.location.href = `properties.php?change_status=${id}&status=${status}`
    );
    
    // Reset select if cancelled
    const modal = document.getElementById('confirmModal');
    modal.addEventListener('click', function resetSelect(e) {
        if (e.target === modal || e.target.textContent === 'Cancel') {
            select.value = oldValue;
            modal.removeEventListener('click', resetSelect);
        }
    });
}

// Featured toggle confirmation
function confirmFeatured(id, willBeFeatured, name) {
    const action = willBeFeatured ? 'mark as featured' : 'remove from featured';
    showConfirmModal(
        'Toggle Featured',
        `Do you want to ${action} "<strong>${name}</strong>"?`,
        'fas fa-star',
        willBeFeatured ? 'Mark Featured' : 'Remove Featured',
        'btn-warning',
        () => window.location.href = 'properties.php?toggle_featured=' + id
    );
}

// Close modal on backdrop click
document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

// Bulk action validation
document.getElementById('bulkForm')?.addEventListener('submit', function(e) {
    const action = document.getElementById('bulkAction').value;
    const checked = document.querySelectorAll('.property-checkbox:checked').length;
    
    if (!action) {
        e.preventDefault();
        showConfirmModal(
            'No Action Selected',
            'Please select a bulk action from the dropdown.',
            'fas fa-exclamation-circle',
            'OK',
            'btn-primary',
            () => {}
        );
        return;
    }
    
    if (checked === 0) {
        e.preventDefault();
        showConfirmModal(
            'No Properties Selected',
            'Please select at least one property to perform bulk action.',
            'fas fa-exclamation-circle',
            'OK',
            'btn-primary',
            () => {}
        );
        return;
    }
    
    if (action === 'delete') {
        e.preventDefault();
        showConfirmModal(
            'Bulk Delete',
            `Are you sure you want to delete <strong>${checked}</strong> properties?<br><small style="color: #6b7280; margin-top: 0.5rem; display: block;">This action cannot be undone.</small>`,
            'fas fa-trash',
            `Delete ${checked} Properties`,
            'btn-danger',
            () => this.submit()
        );
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
