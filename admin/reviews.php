<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Reviews Management";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $review_id = (int)$_POST['review_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $review_id);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'update_review_status', "Updated review #$review_id status to $new_status");
        setFlash('success', 'Review status updated successfully');
    } else {
        setFlash('error', 'Failed to update review status');
    }
    
    redirect('reviews.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $review_id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'delete_review', "Deleted review #$review_id");
        setFlash('success', 'Review deleted successfully');
    } else {
        setFlash('error', 'Failed to delete review');
    }
    
    redirect('reviews.php');
}

// Get filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(u.name LIKE ? OR r.comment LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if ($status_filter) {
    $where[] = "r.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($type_filter) {
    $where[] = "r.item_type = ?";
    $params[] = $type_filter;
    $types .= 's';
}

if ($rating_filter > 0) {
    $where[] = "r.rating = ?";
    $params[] = $rating_filter;
    $types .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get stats
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    ROUND(AVG(rating), 1) as avg_rating
FROM reviews";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    $where_sql";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    if ($types) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_reviews = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_reviews = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_reviews / $per_page);

// Get reviews
$query = "SELECT r.*, u.name as user_name, u.email as user_email,
    CASE 
        WHEN r.item_type = 'product' THEN (SELECT name FROM products WHERE id = r.item_id)
        WHEN r.item_type = 'property' THEN (SELECT title FROM properties WHERE id = r.item_id)
        WHEN r.item_type = 'vehicle' THEN (SELECT CONCAT(brand, ' ', model) FROM vehicles WHERE id = r.item_id)
    END as item_name
FROM reviews r
LEFT JOIN users u ON r.user_id = u.id
$where_sql
ORDER BY r.created_at DESC
LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reviews = $stmt->get_result();

require_once 'includes/header.php';
?>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
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
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
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

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.review-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    transition: all 0.3s;
}

.review-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.review-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
}

.user-info h4 {
    margin: 0 0 3px 0;
    font-size: 15px;
    color: #1a1a2e;
}

.user-info p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.review-meta {
    text-align: right;
}

.rating-stars {
    color: #fbbf24;
    font-size: 18px;
    margin-bottom: 5px;
}

.review-date {
    font-size: 12px;
    color: #999;
}

.review-body {
    margin-bottom: 15px;
}

.review-item {
    display: inline-block;
    padding: 4px 10px;
    background: #f3f4f6;
    border-radius: 12px;
    font-size: 12px;
    color: #666;
    margin-bottom: 10px;
}

.review-item-type {
    font-weight: 600;
    color: #f97316;
}

.review-comment {
    font-size: 14px;
    color: #374151;
    line-height: 1.6;
}

.review-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.review-status {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-rejected {
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

.review-actions {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
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
    font-size: 18px;
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

@media (max-width: 1024px) {
    .filters-form {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .filters-form {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        width: 100%;
    }
    
    .filter-actions .btn {
        flex: 1;
    }
    
    .review-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .review-meta {
        text-align: left;
    }
    
    .review-footer {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .review-status {
        flex-direction: column;
        align-items: stretch;
    }
    
    .status-select {
        width: 100%;
    }
    
    .review-actions {
        width: 100%;
    }
    
    .review-actions .btn-sm {
        flex: 1;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="content-header">
    <h1><?php echo $page_title; ?></h1>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Reviews</h3>
        <div class="number"><?php echo number_format($stats['total']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Pending</h3>
        <div class="number" style="color: #f59e0b;"><?php echo number_format($stats['pending']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Approved</h3>
        <div class="number" style="color: #10b981;"><?php echo number_format($stats['approved']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Rejected</h3>
        <div class="number" style="color: #ef4444;"><?php echo number_format($stats['rejected']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Avg Rating</h3>
        <div class="number" style="color: #f97316;"><?php echo $stats['avg_rating'] ?? '0.0'; ?> ⭐</div>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="User name or comment..." value="<?php echo e($search); ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select name="type">
                <option value="">All Types</option>
                <option value="product" <?php echo $type_filter === 'product' ? 'selected' : ''; ?>>Product</option>
                <option value="property" <?php echo $type_filter === 'property' ? 'selected' : ''; ?>>Property</option>
                <option value="vehicle" <?php echo $type_filter === 'vehicle' ? 'selected' : ''; ?>>Vehicle</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Rating</label>
            <select name="rating">
                <option value="0">All Ratings</option>
                <option value="5" <?php echo $rating_filter === 5 ? 'selected' : ''; ?>>5 Stars</option>
                <option value="4" <?php echo $rating_filter === 4 ? 'selected' : ''; ?>>4 Stars</option>
                <option value="3" <?php echo $rating_filter === 3 ? 'selected' : ''; ?>>3 Stars</option>
                <option value="2" <?php echo $rating_filter === 2 ? 'selected' : ''; ?>>2 Stars</option>
                <option value="1" <?php echo $rating_filter === 1 ? 'selected' : ''; ?>>1 Star</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="reviews.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="reviews-list">
    <?php if ($reviews->num_rows > 0): ?>
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header">
                    <div class="review-user">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($review['user_name'], 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <h4><?php echo e($review['user_name']); ?></h4>
                            <p><?php echo e($review['user_email']); ?></p>
                        </div>
                    </div>
                    <div class="review-meta">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $review['rating'] ? '⭐' : '☆'; ?>
                            <?php endfor; ?>
                        </div>
                        <div class="review-date"><?php echo timeAgo($review['created_at']); ?></div>
                    </div>
                </div>
                <div class="review-body">
                    <div class="review-item">
                        <span class="review-item-type"><?php echo ucfirst($review['item_type']); ?>:</span>
                        <?php echo e($review['item_name'] ?? 'Item not found'); ?>
                    </div>
                    <div class="review-comment"><?php echo nl2br(e($review['comment'])); ?></div>
                </div>
                <div class="review-footer">
                    <div class="review-status">
                        <span class="status-badge status-<?php echo $review['status']; ?>">
                            <?php echo ucfirst($review['status']); ?>
                        </span>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="pending" <?php echo $review['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="approved" <?php echo $review['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $review['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </form>
                    </div>
                    <div class="review-actions">
                        <button onclick="confirmDelete(<?php echo $review['id']; ?>)" class="btn btn-sm btn-danger">Delete</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999; background: white; border-radius: 10px;">
            No reviews found
        </div>
    <?php endif; ?>
</div>

<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&type=<?php echo urlencode($type_filter); ?>&rating=<?php echo $rating_filter; ?>">Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&type=<?php echo urlencode($type_filter); ?>&rating=<?php echo $rating_filter; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&type=<?php echo urlencode($type_filter); ?>&rating=<?php echo $rating_filter; ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-icon">🗑️</div>
        <h3>Delete Review</h3>
        <p>Are you sure you want to delete this review? This action cannot be undone.</p>
        <div class="modal-actions">
            <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeDelete()" class="btn btn-danger">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteReviewId = null;

function confirmDelete(id) {
    deleteReviewId = id;
    document.getElementById('deleteModal').classList.add('active');
}

function executeDelete() {
    if (deleteReviewId) {
        window.location.href = 'reviews.php?delete=' + deleteReviewId;
    }
}

function closeModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteReviewId = null;
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
