<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();
$page_title = 'Banners';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $conn->prepare("SELECT image FROM banners WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $banner = $result->fetch_assoc();
    
    if ($banner && !empty($banner['image'])) {
        $file_path = BANNER_UPLOAD_DIR . $banner['image'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    logActivity('admin', $admin['id'], 'delete_banner', "Deleted banner ID: $id");
    setFlash('success', 'Banner deleted successfully');
    redirect('banners.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int)$_POST['id'];
    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
    
    $stmt = $conn->prepare("UPDATE banners SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    
    logActivity('admin', $admin['id'], 'update_banner_status', "Updated banner ID: $id to $status");
    setFlash('success', 'Banner status updated');
    redirect('banners.php');
}

// Get filters - safely handle GET parameters
$position_filter = isset($_GET['position']) && is_string($_GET['position']) ? $_GET['position'] : '';
$page_filter = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : '';
$status_filter = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) && is_string($_GET['search']) ? $_GET['search'] : '';

// Build query
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($position_filter) {
    $where .= " AND position = ?";
    $params[] = $position_filter;
    $types .= "s";
}

if ($page_filter) {
    $where .= " AND page = ?";
    $params[] = $page_filter;
    $types .= "s";
}

if ($status_filter) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($search) {
    $where .= " AND (title LIKE ? OR subtitle LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN position = 'hero' THEN 1 ELSE 0 END) as hero,
    SUM(CASE WHEN position = 'sidebar' THEN 1 ELSE 0 END) as sidebar
FROM banners";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get banners
$query = "SELECT * FROM banners $where ORDER BY sort_order ASC, created_at DESC";
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$banners = $result->fetch_all(MYSQLI_ASSOC);

// Debug: Verify status values are being fetched
foreach ($banners as &$b) {
    $b['status'] = trim($b['status'] ?? 'active');
}

require_once 'includes/header.php';
?>

<style>
    * {
        box-sizing: border-box;
    }

    .banners-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 15px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .page-header h1 {
        margin: 0;
        color: #1a1a2e;
        font-size: 28px;
        font-weight: 700;
    }

    .btn-add {
        background: #10b981;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-add:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    .stats-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: white;
        padding: 16px;
        border-radius: 8px;
        border-left: 4px solid #f97316;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .stat-card h3 {
        margin: 0;
        font-size: 24px;
        color: #1a1a2e;
        font-weight: 700;
    }

    .stat-card p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 12px;
        font-weight: 500;
    }

    .filters-section {
        background: white;
        padding: 18px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
    }

    .filter-group label {
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        background: white;
        color: #333;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-actions button,
    .filter-actions a {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-filter {
        background: #f97316;
        color: white;
    }

    .btn-filter:hover {
        background: #ea580c;
    }

    .btn-reset {
        background: #e5e7eb;
        color: #333;
    }

    .btn-reset:hover {
        background: #d1d5db;
    }

    .flash-message {
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        border-left: 4px solid;
        font-size: 14px;
        animation: slideIn 0.3s ease;
    }

    .flash-success {
        background: #d1fae5;
        color: #065f46;
        border-left-color: #10b981;
    }

    .flash-error {
        background: #fee2e2;
        color: #991b1b;
        border-left-color: #ef4444;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .banners-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 16px;
    }

    .banner-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .banner-card:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        transform: translateY(-4px);
    }

    .banner-image {
        width: 100%;
        height: 160px;
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #999;
        position: relative;
        overflow: hidden;
    }

    .banner-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .banner-card:hover .banner-image img {
        transform: scale(1.05);
    }

    .banner-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(0,0,0,0.7);
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .banner-badge.active {
        background: #10b981;
    }

    .banner-badge.inactive {
        background: #ef4444;
    }

    .banner-body {
        padding: 14px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .banner-title {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 8px 0;
        color: #1a1a2e;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .banner-meta {
        display: flex;
        gap: 6px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .meta-tag {
        display: inline-block;
        padding: 3px 8px;
        background: #f3f4f6;
        border-radius: 3px;
        font-size: 11px;
        color: #666;
        font-weight: 500;
    }

    .banner-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .stat {
        text-align: center;
    }

    .stat-value {
        font-size: 16px;
        font-weight: 700;
        color: #f97316;
    }

    .stat-label {
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .banner-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: auto;
    }

    .btn-sm {
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-edit {
        background: #3b82f6;
        color: white;
    }

    .btn-edit:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #ef4444;
        color: white;
    }

    .btn-delete:hover {
        background: #dc2626;
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
        background: white;
        border-radius: 8px;
        grid-column: 1 / -1;
    }

    .empty-state svg {
        width: 80px;
        height: 80px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h3 {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 18px;
    }

    .empty-state p {
        margin: 0 0 20px 0;
        color: #999;
        font-size: 14px;
    }

    /* Mobile Responsive */
    @media (max-width: 1024px) {
        .banners-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
    }

    @media (max-width: 768px) {
        .banners-container {
            padding: 12px;
        }

        .page-header {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 20px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .btn-add {
            width: 100%;
        }

        .stats-strip {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 14px;
        }

        .stat-card h3 {
            font-size: 20px;
        }

        .filters-section {
            padding: 14px;
        }

        .filters-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .filter-actions {
            flex-direction: column;
        }

        .filter-actions button,
        .filter-actions a {
            width: 100%;
        }

        .banners-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .banner-image {
            height: 200px;
        }

        .banner-body {
            padding: 12px;
        }

        .banner-title {
            font-size: 14px;
        }

        .banner-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .banners-container {
            padding: 10px;
        }

        .page-header h1 {
            font-size: 20px;
        }

        .stats-strip {
            grid-template-columns: 1fr;
        }

        .stat-card h3 {
            font-size: 18px;
        }

        .banner-image {
            height: 150px;
        }

        .banner-title {
            font-size: 13px;
        }

        .btn-sm {
            font-size: 11px;
            padding: 6px 10px;
        }
    }
</style>

<div class="banners-container">
    <!-- Header -->
    <div class="page-header">
        <h1>Banners Management</h1>
        <a href="add-banner.php" class="btn-add">+ Add Banner</a>
    </div>

    <!-- Stats -->
    <div class="stats-strip">
        <div class="stat-card">
            <h3><?= $stats['total'] ?? 0 ?></h3>
            <p>Total Banners</p>
        </div>
        <div class="stat-card" style="border-left-color: #10b981;">
            <h3><?= $stats['active'] ?? 0 ?></h3>
            <p>Active</p>
        </div>
        <div class="stat-card" style="border-left-color: #ef4444;">
            <h3><?= $stats['inactive'] ?? 0 ?></h3>
            <p>Inactive</p>
        </div>
        <div class="stat-card" style="border-left-color: #3b82f6;">
            <h3><?= $stats['hero'] ?? 0 ?></h3>
            <p>Hero Banners</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" style="display: contents;">
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search banners..." value="<?= e($search) ?>">
                </div>
                <div class="filter-group">
                    <label>Position</label>
                    <select name="position">
                        <option value="">All Positions</option>
                        <option value="hero" <?= $position_filter === 'hero' ? 'selected' : '' ?>>Hero</option>
                        <option value="top" <?= $position_filter === 'top' ? 'selected' : '' ?>>Top</option>
                        <option value="middle" <?= $position_filter === 'middle' ? 'selected' : '' ?>>Middle</option>
                        <option value="bottom" <?= $position_filter === 'bottom' ? 'selected' : '' ?>>Bottom</option>
                        <option value="sidebar" <?= $position_filter === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Page</label>
                    <select name="page">
                        <option value="">All Pages</option>
                        <option value="home" <?= $page_filter === 'home' ? 'selected' : '' ?>>Home</option>
                        <option value="products" <?= $page_filter === 'products' ? 'selected' : '' ?>>Products</option>
                        <option value="properties" <?= $page_filter === 'properties' ? 'selected' : '' ?>>Properties</option>
                        <option value="vehicles" <?= $page_filter === 'vehicles' ? 'selected' : '' ?>>Vehicles</option>
                        <option value="all" <?= $page_filter === 'all' ? 'selected' : '' ?>>All Pages</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-filter">Filter</button>
                <a href="banners.php" class="btn-reset">Reset</a>
            </div>
        </form>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- Banners Grid -->
    <div class="banners-grid">
        <?php if (empty($banners)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
                <h3>No banners found</h3>
                <p>Create your first banner to get started</p>
                <a href="add-banner.php" class="btn-add" style="display: inline-block; margin-top: 15px;">+ Add Banner</a>
            </div>
        <?php else: ?>
            <?php foreach ($banners as $banner): ?>
                <div class="banner-card">
                    <div class="banner-image">
                        <?php if (!empty($banner['image']) && file_exists(BANNER_UPLOAD_DIR . $banner['image'])): ?>
                            <img src="<?= UPLOAD_URL ?>banners/<?= e($banner['image']) ?>" alt="<?= e($banner['title']) ?>">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                        <span class="banner-badge <?= isset($banner['status']) && !empty($banner['status']) ? trim($banner['status']) : 'active' ?>"><?= isset($banner['status']) && !empty($banner['status']) ? ucfirst(trim($banner['status'])) : 'Active' ?></span>
                    </div>

                    <div class="banner-body">
                        <h3 class="banner-title"><?= e($banner['title']) ?></h3>

                        <div class="banner-meta">
                            <span class="meta-tag"><?= ucfirst($banner['position']) ?></span>
                            <span class="meta-tag"><?= ucfirst($banner['page']) ?></span>
                        </div>

                        <div class="banner-stats">
                            <div class="stat">
                                <div class="stat-value"><?= $banner['views'] ?></div>
                                <div class="stat-label">Views</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value"><?= $banner['clicks'] ?></div>
                                <div class="stat-label">Clicks</div>
                            </div>
                        </div>

                        <div class="banner-actions">
                            <a href="edit-banner.php?id=<?= $banner['id'] ?>" class="btn-sm btn-edit">Edit</a>
                            <button type="button" class="btn-sm btn-delete" onclick="showDeleteModal(<?= $banner['id'] ?>, '<?= e($banner['title']) ?>')">Delete</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; padding: 30px; border-radius: 8px; max-width: 400px; width: 100%;">
        <h3 style="margin: 0 0 15px 0; color: #1a1a2e;">Delete Banner</h3>
        <p style="margin: 0 0 20px 0; color: #666;">Are you sure you want to delete "<span id="deleteTitle"></span>"? This action cannot be undone.</p>
        <div style="display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;">
            <button type="button" onclick="closeDeleteModal()" style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer; font-weight: 600;">Cancel</button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <button type="submit" style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function showDeleteModal(id, title) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteTitle').textContent = title;
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
