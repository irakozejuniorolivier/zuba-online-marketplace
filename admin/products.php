<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = 'Manage Products';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $product_ids = $_POST['product_ids'] ?? [];
    
    if (!empty($product_ids)) {
        $ids = implode(',', array_map('intval', $product_ids));
        
        if ($action === 'activate') {
            $conn->query("UPDATE products SET status = 'active' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_activate_products', 'Activated ' . count($product_ids) . ' products');
            setFlash('success', count($product_ids) . ' products activated successfully');
        } elseif ($action === 'deactivate') {
            $conn->query("UPDATE products SET status = 'inactive' WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_deactivate_products', 'Deactivated ' . count($product_ids) . ' products');
            setFlash('success', count($product_ids) . ' products deactivated successfully');
        } elseif ($action === 'delete') {
            $img_result = $conn->query("SELECT image_path FROM product_images WHERE product_id IN ($ids)");
            while ($img = $img_result->fetch_assoc()) {
                @unlink('../uploads/products/' . $img['image_path']);
            }
            $conn->query("DELETE FROM product_images WHERE product_id IN ($ids)");
            $conn->query("DELETE FROM products WHERE id IN ($ids)");
            logActivity('admin', $admin['id'], 'bulk_delete_products', 'Deleted ' . count($product_ids) . ' products');
            setFlash('success', count($product_ids) . ' products deleted successfully');
        }
        redirect('products.php');
    }
}

// Handle single delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $prod = $conn->query("SELECT name FROM products WHERE id = $id")->fetch_assoc();
    $img_result = $conn->query("SELECT image_path FROM product_images WHERE product_id = $id");
    while ($img = $img_result->fetch_assoc()) {
        @unlink('../uploads/products/' . $img['image_path']);
    }
    $conn->query("DELETE FROM product_images WHERE product_id = $id");
    $conn->query("DELETE FROM products WHERE id = $id");
    logActivity('admin', $admin['id'], 'delete_product', 'Deleted product: ' . $prod['name']);
    setFlash('success', 'Product deleted successfully');
    redirect('products.php');
}

// Handle status change
if (isset($_GET['change_status'])) {
    $id = intval($_GET['change_status']);
    $status = $_GET['status'];
    $conn->query("UPDATE products SET status = '$status' WHERE id = $id");
    logActivity('admin', $admin['id'], 'change_product_status', "Changed product #$id status to $status");
    setFlash('success', 'Product status updated');
    redirect('products.php');
}

// Handle featured toggle
if (isset($_GET['toggle_featured'])) {
    $id = intval($_GET['toggle_featured']);
    $conn->query("UPDATE products SET featured = NOT featured WHERE id = $id");
    logActivity('admin', $admin['id'], 'toggle_product_featured', "Toggled featured for product #$id");
    redirect('products.php');
}

// Filters
$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$status = $_GET['status'] ?? '';

$where = ['1=1'];
if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where[] = "(p.name LIKE '%$search_safe%' OR p.sku LIKE '%$search_safe%' OR p.brand LIKE '%$search_safe%')";
}
if ($category_id) {
    $where[] = "p.category_id = " . intval($category_id);
}
if ($status) {
    $where[] = "p.status = '$status'";
}

$where_clause = implode(' AND ', $where);

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_result = $conn->query("SELECT COUNT(*) as total FROM products p WHERE $where_clause");
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

$query = "SELECT p.*, c.name as category_name,
          (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE $where_clause
          ORDER BY p.created_at DESC
          LIMIT $per_page OFFSET $offset";
$products = $conn->query($query);

$categories = $conn->query("SELECT * FROM categories WHERE type = 'ecommerce' AND status = 'active' ORDER BY name");

$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN status = 'out_of_stock' THEN 1 ELSE 0 END) as out_of_stock
    FROM products";
$stats = $conn->query($stats_query)->fetch_assoc();

require_once 'includes/header.php';
?>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-header h1 { margin: 0; font-size: 1.75rem; color: #1a1a2e; }
.page-header p { margin: 0.25rem 0 0 0; color: #666; }
.btn { padding: 0.625rem 1.25rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
.btn-primary { background: #f97316; color: white; }
.btn-primary:hover { background: #ea580c; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
.alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 50px; height: 50px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
.stat-details { flex: 1; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: #1a1a2e; }
.stat-label { color: #666; font-size: 0.875rem; margin-top: 0.25rem; }
.card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 1.5rem; }
.filters-form { padding: 1.5rem; }
.filters-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.filters-grid input, .filters-grid select { padding: 0.625rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; width: 100%; }
.filters-grid input:focus, .filters-grid select:focus { outline: none; border-color: #f97316; }
.filters-actions { display: flex; gap: 0.75rem; }
.table-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.bulk-actions { display: flex; gap: 0.5rem; align-items: center; }
.bulk-actions select { padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; }
.table-info { color: #666; font-size: 0.875rem; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.product-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; position: relative; }
.product-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.12); transform: translateY(-4px); }
.product-card-checkbox { position: absolute; top: 0.75rem; left: 0.75rem; z-index: 10; }
.product-card-checkbox input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; accent-color: #f97316; }
.product-featured-badge { position: absolute; top: 0.75rem; right: 0.75rem; background: #f59e0b; color: white; padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; z-index: 10; display: flex; align-items: center; gap: 0.25rem; }
.product-card-image { position: relative; width: 100%; height: 200px; overflow: hidden; background: #f3f4f6; }
.product-card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.product-card:hover .product-card-image img { transform: scale(1.05); }
.product-card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 4rem; }
.product-card-body { padding: 1.25rem; }
.product-card-category { color: #f97316; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
.product-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.75rem 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.product-card-meta { display: flex; gap: 1rem; margin-bottom: 1rem; font-size: 0.8125rem; color: #6b7280; }
.product-card-meta-item { display: flex; align-items: center; gap: 0.25rem; }
.product-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid #f3f4f6; margin-bottom: 1rem; }
.product-card-price { display: flex; flex-direction: column; gap: 0.25rem; }
.product-card-price-main { font-size: 1.25rem; font-weight: 700; color: #f97316; }
.product-card-price-compare { font-size: 0.875rem; color: #9ca3af; text-decoration: line-through; }
.product-card-stock { font-size: 0.875rem; font-weight: 600; }
.stock-good { color: #10b981; }
.stock-low { color: #f59e0b; }
.stock-out { color: #ef4444; }
.product-card-status { margin-bottom: 1rem; }
.product-card-status select { width: 100%; }
.status-select { padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.status-select:focus { outline: none; border-color: #f97316; }
.status-select.status-active { background: #d1fae5; color: #065f46; border-color: #10b981; }
.status-select.status-inactive { background: #fee2e2; color: #991b1b; border-color: #ef4444; }
.status-select.status-out_of_stock { background: #fef3c7; color: #92400e; border-color: #f59e0b; }
.product-card-actions { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; }
.btn-action { padding: 0.625rem 1rem; border: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem; text-decoration: none; }
.btn-action span { display: inline; }
.btn-action-primary { background: #f97316; color: white; }
.btn-action-primary:hover { background: #ea580c; transform: translateY(-1px); }
.btn-action-warning { background: #f59e0b; color: white; border: 2px solid #f59e0b; }
.btn-action-warning:hover { background: #d97706; border-color: #d97706; }
.btn-action-danger { background: white; color: #ef4444; border: 2px solid #ef4444; }
.btn-action-danger:hover { background: #ef4444; color: white; }
.empty-state { grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; }
.empty-state i { font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1.5rem; color: #1a1a2e; margin: 0 0 0.5rem 0; }
.empty-state p { color: #6b7280; margin: 0 0 1.5rem 0; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 1.5rem; }
.page-link { padding: 0.5rem 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; color: #374151; text-decoration: none; font-size: 0.875rem; transition: all 0.2s; }
.page-link:hover { background: #f9fafb; border-color: #f97316; color: #f97316; }
.page-link.active { background: #f97316; color: white; border-color: #f97316; }
.modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
.modal.show { display: flex; }
.modal-content { background: white; padding: 2rem; border-radius: 8px; max-width: 500px; width: 90%; }
.modal-confirm { text-align: center; max-width: 420px; }
.modal-icon { width: 64px; height: 64px; margin: 0 auto 1.5rem; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #ef4444; }
.modal-confirm h3 { margin: 0 0 1rem 0; color: #1a1a2e; font-size: 1.5rem; }
.modal-confirm p { margin: 0.5rem 0; color: #374151; font-size: 0.9375rem; }
.modal-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; }
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
    .page-header .btn { width: 100%; justify-content: center; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .stat-card { padding: 1rem; }
    .stat-icon { width: 40px; height: 40px; font-size: 1.25rem; }
    .stat-value { font-size: 1.5rem; }
    .stat-label { font-size: 0.75rem; line-height: 1.3; }
    .filters-grid { grid-template-columns: 1fr; }
    .filters-actions { flex-direction: column; }
    .filters-actions .btn { width: 100%; justify-content: center; }
    .table-header { flex-direction: column; gap: 1rem; align-items: stretch; }
    .bulk-actions { flex-direction: column; }
    .bulk-actions select, .bulk-actions .btn { width: 100%; }
    .products-grid { grid-template-columns: 1fr; gap: 1rem; }
    .product-card-image { height: 180px; }
    .product-card-body { padding: 1rem; }
    .product-card-title { font-size: 0.9375rem; }
    .product-card-price-main { font-size: 1.125rem; }
    .product-card-actions { grid-template-columns: 1fr 1fr; }
    .product-card-actions .btn-action:first-child { grid-column: 1 / -1; }
    .btn-action { font-size: 0.8125rem; padding: 0.5rem 0.75rem; }
    .pagination { flex-wrap: wrap; gap: 0.375rem; }
    .page-link { padding: 0.375rem 0.625rem; font-size: 0.8125rem; }
    .modal-content { margin: 1rem; padding: 1.5rem; }
    .modal-actions { flex-direction: column; }
    .modal-actions .btn { width: 100%; }
}
@media (max-width: 480px) {
    .product-card-meta { font-size: 0.75rem; }
    .btn-action { font-size: 0.75rem; padding: 0.5rem; }
}
</style>

<div class="page-header">
    <div>
        <h1>Products</h1>
        <p>Manage your product catalog</p>
    </div>
    <a href="add-product.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
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
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['total']) ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #10b981;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['active']) ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #6b7280;">
            <i class="fas fa-pause-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['inactive']) ?></div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #ef4444;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-value"><?= number_format($stats['out_of_stock']) ?></div>
            <div class="stat-label">Out of Stock</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <form method="GET" class="filters-form">
        <div class="filters-grid">
            <input type="text" name="search" placeholder="Search by name, SKU, brand..." value="<?= e($search) ?>">
            
            <select name="category_id">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            
            <select name="status">
                <option value="">All Status</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
            </select>
        </div>
        
        <div class="filters-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="products.php" class="btn btn-secondary">
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
                Showing <?= $products->num_rows ?> of <?= number_format($total_products) ?> products
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-grid">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($prod = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-card-checkbox">
                        <input type="checkbox" name="product_ids[]" value="<?= $prod['id'] ?>" class="product-checkbox">
                    </div>
                    
                    <?php if ($prod['featured']): ?>
                    <div class="product-featured-badge">
                        <i class="fas fa-star"></i> Featured
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-card-image">
                        <?php if ($prod['primary_image']): ?>
                        <img src="<?= UPLOAD_URL . 'products/' . e($prod['primary_image']) ?>" alt="<?= e($prod['name']) ?>">
                        <?php else: ?>
                        <div class="product-card-placeholder">
                            <i class="fas fa-box"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-card-body">
                        <div class="product-card-category"><?= e($prod['category_name']) ?></div>
                        <h3 class="product-card-title"><?= e($prod['name']) ?></h3>
                        
                        <div class="product-card-meta">
                            <?php if ($prod['sku']): ?>
                            <div class="product-card-meta-item">
                                <i class="fas fa-barcode"></i>
                                <span><?= e($prod['sku']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($prod['brand']): ?>
                            <div class="product-card-meta-item">
                                <i class="fas fa-tag"></i>
                                <span><?= e($prod['brand']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-card-footer">
                            <div class="product-card-price">
                                <div class="product-card-price-main"><?= formatCurrency($prod['price']) ?></div>
                                <?php if ($prod['compare_price'] && $prod['compare_price'] > $prod['price']): ?>
                                <div class="product-card-price-compare"><?= formatCurrency($prod['compare_price']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-stock <?= $prod['stock'] <= 0 ? 'stock-out' : ($prod['stock'] <= 5 ? 'stock-low' : 'stock-good') ?>">
                                <i class="fas fa-box"></i> <?= $prod['stock'] ?>
                            </div>
                        </div>
                        
                        <div class="product-card-status">
                            <select onchange="confirmStatusChange(<?= $prod['id'] ?>, this.value, '<?= e($prod['name']) ?>')" class="status-select status-<?= $prod['status'] ?>">
                                <option value="active" <?= $prod['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $prod['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="out_of_stock" <?= $prod['status'] === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                            </select>
                        </div>
                        
                        <div class="product-card-actions">
                            <a href="edit-product.php?id=<?= $prod['id'] ?>" class="btn-action btn-action-primary">
                                <i class="fas fa-edit"></i> <span>Edit</span>
                            </a>
                            <button type="button" onclick="confirmFeatured(<?= $prod['id'] ?>, <?= $prod['featured'] ? 'false' : 'true' ?>, '<?= e($prod['name']) ?>')" class="btn-action btn-action-warning">
                                <i class="fas fa-star"></i> <span><?= $prod['featured'] ? 'Featured' : 'Feature' ?></span>
                            </button>
                            <button type="button" onclick="confirmDelete(<?= $prod['id'] ?>, '<?= e($prod['name']) ?>')" class="btn-action btn-action-danger">
                                <i class="fas fa-trash"></i> <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-box"></i>
                    <h3>No Products Found</h3>
                    <p>Start by adding your first product</p>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Product
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
            $base_url = 'products.php?' . ($query_string ? $query_string . '&' : '');
            
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
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = this.checked);
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

function confirmDelete(id, name) {
    showConfirmModal(
        'Delete Product',
        `Are you sure you want to delete "<strong>${name}</strong>"?<br><small style="color: #6b7280; margin-top: 0.5rem; display: block;">This action cannot be undone. All product images will also be deleted.</small>`,
        'fas fa-trash',
        'Delete Product',
        'btn-danger',
        () => window.location.href = 'products.php?delete=' + id
    );
}

function confirmStatusChange(id, status, name) {
    const select = event.target;
    const oldValue = select.getAttribute('data-current') || select.value;
    
    showConfirmModal(
        'Change Status',
        `Change status of "<strong>${name}</strong>" to <strong>${status.replace('_', ' ')}</strong>?`,
        'fas fa-exchange-alt',
        'Change Status',
        'btn-primary',
        () => window.location.href = `products.php?change_status=${id}&status=${status}`
    );
    
    const modal = document.getElementById('confirmModal');
    modal.addEventListener('click', function resetSelect(e) {
        if (e.target === modal || e.target.textContent === 'Cancel') {
            select.value = oldValue;
            modal.removeEventListener('click', resetSelect);
        }
    });
}

function confirmFeatured(id, willBeFeatured, name) {
    const action = willBeFeatured ? 'mark as featured' : 'remove from featured';
    showConfirmModal(
        'Toggle Featured',
        `Do you want to ${action} "<strong>${name}</strong>"?`,
        'fas fa-star',
        willBeFeatured ? 'Mark Featured' : 'Remove Featured',
        'btn-warning',
        () => window.location.href = 'products.php?toggle_featured=' + id
    );
}

document.getElementById('confirmModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeConfirmModal();
});

document.getElementById('bulkForm')?.addEventListener('submit', function(e) {
    const action = document.getElementById('bulkAction').value;
    const checked = document.querySelectorAll('.product-checkbox:checked').length;
    
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
            'No Products Selected',
            'Please select at least one product to perform bulk action.',
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
            `Are you sure you want to delete <strong>${checked}</strong> products?<br><small style="color: #6b7280; margin-top: 0.5rem; display: block;">This action cannot be undone.</small>`,
            'fas fa-trash',
            `Delete ${checked} Products`,
            'btn-danger',
            () => this.submit()
        );
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
