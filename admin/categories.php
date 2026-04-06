<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Categories Management";

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $slug = generateSlug($name);
    $status = $_POST['status'];
    
    // Check slug uniqueness
    $check = $conn->query("SELECT id FROM categories WHERE slug = '$slug'");
    if ($check->num_rows > 0) {
        $slug .= '-' . time();
    }
    
    // Handle image upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $upload_result = uploadFile($_FILES['image'], '../uploads/categories/');
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO categories (name, slug, type, description, parent_id, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $slug, $type, $description, $parent_id, $image, $status);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'add_category', "Added category: $name");
        setFlash('success', 'Category added successfully');
    } else {
        setFlash('error', 'Failed to add category');
    }
    
    redirect('categories.php?type=' . $type);
}

// Handle edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $description = trim($_POST['description']);
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $status = $_POST['status'];
    
    // Get current category
    $current = $conn->query("SELECT * FROM categories WHERE id = $category_id")->fetch_assoc();
    $image = $current['image'];
    
    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $upload_result = uploadFile($_FILES['image'], '../uploads/categories/');
        if ($upload_result['success']) {
            $image = $upload_result['filename'];
        }
    }
    
    $stmt = $conn->prepare("UPDATE categories SET name = ?, type = ?, description = ?, parent_id = ?, image = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $type, $description, $parent_id, $image, $status, $category_id);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'edit_category', "Updated category: $name");
        setFlash('success', 'Category updated successfully');
    } else {
        setFlash('error', 'Failed to update category');
    }
    
    redirect('categories.php?type=' . $type);
}

// Handle delete
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    $type = $_GET['type'] ?? 'ecommerce';
    
    // Check if category has items
    $table = $type === 'ecommerce' ? 'products' : ($type === 'realestate' ? 'properties' : 'vehicles');
    $check = $conn->query("SELECT COUNT(*) as count FROM $table WHERE category_id = $category_id");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        setFlash('error', "Cannot delete category with $count items");
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        
        if ($stmt->execute()) {
            logActivity('admin', $admin['id'], 'delete_category', "Deleted category #$category_id");
            setFlash('success', 'Category deleted successfully');
        } else {
            setFlash('error', 'Failed to delete category');
        }
    }
    
    redirect('categories.php?type=' . $type);
}

// Get filters
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'ecommerce';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get stats
$stats = [];
foreach (['ecommerce', 'realestate', 'carrental'] as $t) {
    $result = $conn->query("SELECT COUNT(*) as count FROM categories WHERE type = '$t'");
    $stats[$t] = $result->fetch_assoc()['count'];
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$where = "type = '$type_filter'";
if ($search) {
    $where .= " AND name LIKE '%$search%'";
}

$total = $conn->query("SELECT COUNT(*) as count FROM categories WHERE $where")->fetch_assoc()['count'];
$total_pages = ceil($total / $per_page);

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE $where ORDER BY name ASC LIMIT $per_page OFFSET $offset");

// Get parent categories for dropdown
$parent_categories = $conn->query("SELECT id, name FROM categories WHERE type = '$type_filter' AND parent_id IS NULL ORDER BY name");

require_once 'includes/header.php';
?>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.content-header h1 {
    margin: 0;
}

.btn-add {
    background: #f97316;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-add:hover {
    background: #ea580c;
}

.type-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.type-tab {
    padding: 12px 20px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.type-tab:hover {
    color: #1a1a2e;
}

.type-tab.active {
    color: #f97316;
    border-bottom-color: #f97316;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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

.filter-group input {
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.category-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s;
    display: flex;
    flex-direction: column;
}

.category-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.category-image {
    width: 100%;
    height: 150px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}

.category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.category-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 48px;
}

.category-body {
    padding: 15px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.category-name {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 8px 0;
    word-break: break-word;
}

.category-meta {
    font-size: 13px;
    color: #666;
    margin: 0 0 10px 0;
}

.category-description {
    font-size: 13px;
    color: #666;
    margin: 0 0 12px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.category-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 12px;
    align-self: flex-start;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.category-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
    margin-top: auto;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 13px;
    text-align: center;
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
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
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    color: #1a1a2e;
    font-size: 18px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.close-btn:hover {
    color: #1a1a2e;
    transform: rotate(90deg);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 100%;
    max-height: 150px;
    border-radius: 5px;
    display: block;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.delete-modal-content {
    text-align: center;
}

.delete-modal-content .modal-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.delete-modal-content h3 {
    font-size: 18px;
    margin: 0 0 10px 0;
}

.delete-modal-content p {
    color: #666;
    margin-bottom: 20px;
    font-size: 14px;
}

@media (max-width: 1024px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    }
}

@media (max-width: 768px) {
    .content-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .btn-add {
        width: 100%;
        text-align: center;
    }
    
    .type-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 15px;
    }
    
    .type-tab {
        white-space: nowrap;
        padding: 10px 16px;
        font-size: 13px;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .stat-card {
        padding: 15px;
    }
    
    .stat-card .number {
        font-size: 24px;
    }
    
    .filters-bar {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .filters-form {
        flex-direction: column;
        gap: 12px;
    }
    
    .filter-group {
        flex: 1;
    }
    
    .filter-group label {
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .filter-group input {
        padding: 9px;
        font-size: 13px;
    }
    
    .filter-actions {
        width: 100%;
        flex-direction: column;
        gap: 8px;
    }
    
    .filter-actions .btn {
        flex: 1;
        padding: 9px 15px;
        font-size: 13px;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .category-card {
        display: flex;
        flex-direction: row;
        overflow: visible;
    }
    
    .category-image {
        width: 100px;
        height: 100px;
        min-width: 100px;
        border-radius: 8px 0 0 8px;
    }
    
    .category-body {
        flex: 1;
        padding: 12px;
        display: flex;
        flex-direction: column;
    }
    
    .category-name {
        font-size: 14px;
        margin: 0 0 4px 0;
    }
    
    .category-meta {
        font-size: 12px;
        margin: 0 0 4px 0;
    }
    
    .category-description {
        font-size: 12px;
        margin: 0 0 8px 0;
        -webkit-line-clamp: 1;
    }
    
    .category-status {
        font-size: 11px;
        padding: 3px 8px;
        margin-bottom: 8px;
        align-self: flex-start;
    }
    
    .category-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        padding-top: 8px;
        border-top: 1px solid #e5e7eb;
    }
    
    .btn-sm {
        padding: 7px 10px;
        font-size: 12px;
    }
    
    .pagination {
        padding: 15px;
        gap: 5px;
    }
    
    .pagination a,
    .pagination span {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    .modal-content {
        max-width: 95%;
        padding: 20px;
        border-radius: 8px;
    }
    
    .modal-header {
        margin-bottom: 15px;
        padding-bottom: 12px;
    }
    
    .modal-header h3 {
        font-size: 16px;
    }
    
    .form-group {
        margin-bottom: 12px;
    }
    
    .form-group label {
        font-size: 13px;
        margin-bottom: 4px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 9px;
        font-size: 13px;
    }
    
    .form-group textarea {
        min-height: 70px;
    }
    
    .form-actions {
        flex-direction: column-reverse;
        gap: 8px;
        margin-top: 15px;
    }
    
    .form-actions .btn {
        width: 100%;
        padding: 10px;
    }
    
    .delete-modal-content .modal-icon {
        font-size: 36px;
        margin-bottom: 12px;
    }
    
    .delete-modal-content h3 {
        font-size: 16px;
    }
    
    .delete-modal-content p {
        font-size: 13px;
        margin-bottom: 15px;
    }
}

@media (max-width: 480px) {
    .content-header h1 {
        font-size: 20px;
    }
    
    .type-tabs {
        margin-bottom: 12px;
    }
    
    .type-tab {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .stat-card {
        padding: 12px;
    }
    
    .stat-card h3 {
        font-size: 12px;
    }
    
    .stat-card .number {
        font-size: 20px;
    }
    
    .filters-bar {
        padding: 12px;
    }
    
    .filter-group label {
        font-size: 12px;
    }
    
    .filter-group input {
        padding: 8px;
        font-size: 12px;
    }
    
    .categories-grid {
        gap: 12px;
    }
    
    .category-card {
        flex-direction: column;
    }
    
    .category-image {
        width: 100%;
        height: 120px;
        border-radius: 8px 8px 0 0;
    }
    
    .category-body {
        padding: 10px;
    }
    
    .category-name {
        font-size: 13px;
    }
    
    .category-meta {
        font-size: 11px;
    }
    
    .category-description {
        font-size: 11px;
        -webkit-line-clamp: 1;
    }
    
    .category-actions {
        grid-template-columns: 1fr 1fr;
        gap: 5px;
    }
    
    .btn-sm {
        padding: 6px 8px;
        font-size: 11px;
    }
    
    .modal-content {
        max-width: 100%;
        padding: 15px;
        border-radius: 8px;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 8px;
        font-size: 12px;
    }
}
</style>

<div class="content-header">
    <h1><?php echo $page_title; ?></h1>
    <button onclick="openAddModal()" class="btn-add">+ Add Category</button>
</div>

<?php if (getFlash('success')): ?>
    <div class="alert alert-success"><?php echo getFlash('success'); ?></div>
<?php endif; ?>

<?php if (getFlash('error')): ?>
    <div class="alert alert-error"><?php echo getFlash('error'); ?></div>
<?php endif; ?>

<!-- Type Tabs -->
<div class="type-tabs">
    <a href="?type=ecommerce" class="type-tab <?php echo $type_filter === 'ecommerce' ? 'active' : ''; ?>">E-Commerce</a>
    <a href="?type=realestate" class="type-tab <?php echo $type_filter === 'realestate' ? 'active' : ''; ?>">Real Estate</a>
    <a href="?type=carrental" class="type-tab <?php echo $type_filter === 'carrental' ? 'active' : ''; ?>">Car Rental</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>E-Commerce</h3>
        <div class="number"><?php echo $stats['ecommerce']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Real Estate</h3>
        <div class="number"><?php echo $stats['realestate']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Car Rental</h3>
        <div class="number"><?php echo $stats['carrental']; ?></div>
    </div>
</div>

<!-- Filters -->
<div class="filters-bar">
    <form method="GET" class="filters-form">
        <input type="hidden" name="type" value="<?php echo e($type_filter); ?>">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="Category name..." value="<?php echo e($search); ?>">
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="?type=<?php echo $type_filter; ?>" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!-- Categories Grid -->
<div class="categories-grid">
    <?php if ($categories->num_rows > 0): ?>
        <?php while ($category = $categories->fetch_assoc()): ?>
            <div class="category-card">
                <div class="category-image">
                    <?php if ($category['image']): ?>
                        <img src="<?= UPLOAD_URL . 'categories/' . e($category['image']) ?>" alt="">
                    <?php else: ?>
                        <div class="category-image-placeholder">📁</div>
                    <?php endif; ?>
                </div>
                <div class="category-body">
                    <h4 class="category-name"><?php echo e($category['name']); ?></h4>
                    <p class="category-meta">
                        <?php 
                        $type_labels = ['ecommerce' => 'E-Commerce', 'realestate' => 'Real Estate', 'carrental' => 'Car Rental'];
                        echo $type_labels[$category['type']] ?? $category['type'];
                        ?>
                    </p>
                    <?php if ($category['description']): ?>
                        <p class="category-description"><?php echo e($category['description']); ?></p>
                    <?php endif; ?>
                    <span class="category-status status-<?php echo $category['status']; ?>">
                        <?php echo ucfirst($category['status']); ?>
                    </span>
                    <div class="category-actions">
                        <button onclick="openEditModal(<?php echo $category['id']; ?>)" class="btn btn-sm btn-edit">Edit</button>
                        <button onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo e($category['name']); ?>')" class="btn btn-sm btn-delete">Delete</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
            No categories found
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&type=<?php echo $type_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Category</h3>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <form id="categoryForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="categoryId" name="category_id" value="">
            <input type="hidden" id="formAction" name="add_category" value="1">
            
            <div class="form-group">
                <label>Category Name *</label>
                <input type="text" id="categoryName" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Type *</label>
                <select id="categoryType" name="type" required onchange="updateParentCategories()">
                    <option value="ecommerce">E-Commerce</option>
                    <option value="realestate">Real Estate</option>
                    <option value="carrental">Car Rental</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Parent Category</label>
                <select id="parentCategory" name="parent_id">
                    <option value="">None</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="categoryDescription"></textarea>
            </div>
            
            <div class="form-group">
                <label>Image</label>
                <input type="file" name="image" accept="image/*" onchange="previewImage(event)">
                <div id="imagePreview" class="image-preview"></div>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content delete-modal-content">
        <div class="modal-icon">🗑️</div>
        <h3>Delete Category</h3>
        <p id="deleteMessage"></p>
        <div class="form-actions">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteCategoryId = null;
let deleteType = null;
let parentCategoriesData = {};

// Load parent categories for each type
<?php
$types = ['ecommerce', 'realestate', 'carrental'];
foreach ($types as $t) {
    $parents = $conn->query("SELECT id, name FROM categories WHERE type = '$t' AND parent_id IS NULL ORDER BY name");
    echo "parentCategoriesData['$t'] = [";
    while ($p = $parents->fetch_assoc()) {
        echo "{id: " . $p['id'] . ", name: '" . addslashes($p['name']) . "'},";
    }
    echo "];\n";
}
?>

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('formAction').name = 'add_category';
    document.getElementById('imagePreview').innerHTML = '';
    updateParentCategories();
    document.getElementById('categoryModal').classList.add('active');
}

function openEditModal(id) {
    // Fetch category data via AJAX
    fetch('get-category.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = data.id;
            document.getElementById('categoryName').value = data.name;
            document.getElementById('categoryType').value = data.type;
            document.getElementById('categoryDescription').value = data.description || '';
            document.getElementById('formAction').name = 'edit_category';
            
            updateParentCategories();
            document.getElementById('parentCategory').value = data.parent_id || '';
            
            if (data.image) {
                document.getElementById('imagePreview').innerHTML = '<img src="<?= UPLOAD_URL ?>categories/' + data.image + '" alt="">';
            } else {
                document.getElementById('imagePreview').innerHTML = '';
            }
            
            document.querySelector('select[name="status"]').value = data.status;
            document.getElementById('categoryModal').classList.add('active');
        });
}

function updateParentCategories() {
    const type = document.getElementById('categoryType').value;
    const select = document.getElementById('parentCategory');
    select.innerHTML = '<option value="">None</option>';
    
    if (parentCategoriesData[type]) {
        parentCategoriesData[type].forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            select.appendChild(option);
        });
    }
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" alt="">';
        };
        reader.readAsDataURL(file);
    }
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('active');
}

function confirmDelete(id, name) {
    deleteCategoryId = id;
    deleteType = '<?php echo $type_filter; ?>';
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"?`;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteCategoryId = null;
}

function executeDelete() {
    if (deleteCategoryId) {
        window.location.href = 'categories.php?delete=' + deleteCategoryId + '&type=' + deleteType;
    }
}

// Close modals on outside click
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
