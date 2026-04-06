<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';

function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
function formatCurrency($amount) { return 'RWF ' . number_format($amount, 0); }

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$results = [];
$total = 0;

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    
    // Search Products
    if ($category === 'all' || $category === 'products') {
        $stmt = $conn->prepare("SELECT p.id, p.name, p.price, p.stock, pi.image_path, 'product' as type 
            FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1 
            WHERE p.status = 'active' AND (p.name LIKE ? OR p.description LIKE ?)");
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $results[] = $row;
    }
    
    // Search Properties
    if ($category === 'all' || $category === 'properties') {
        $stmt = $conn->prepare("SELECT p.id, p.title as name, p.price, p.city, pi.image_path, 'property' as type 
            FROM properties p LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1 
            WHERE p.title LIKE ? OR p.description LIKE ?");
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $results[] = $row;
    }
    
    // Search Vehicles
    if ($category === 'all' || $category === 'vehicles') {
        $stmt = $conn->prepare("SELECT v.id, CONCAT(v.brand, ' ', v.model) as name, v.daily_rate as price, v.year, vi.image_path, 'vehicle' as type 
            FROM vehicles v LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id AND vi.is_primary = 1 
            WHERE v.status = 'available' AND (v.brand LIKE ? OR v.model LIKE ?)");
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $results[] = $row;
    }
    
    $total = count($results);
    $results = array_slice($results, $offset, $per_page);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Zuba Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; background: #f5f5f5; }
        
        /* Header */
        .header { background: #fff; padding: 12px 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .header-content { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
        .back-btn { background: none; border: none; font-size: 20px; color: #333; cursor: pointer; padding: 8px; }
        .search-box { flex: 1; position: relative; }
        .search-box input { width: 100%; padding: 10px 40px 10px 40px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; }
        .search-box input:focus { outline: none; border-color: #f97316; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }
        .clear-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #999; cursor: pointer; font-size: 18px; }
        .filter-btn { background: #f97316; color: #fff; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-size: 14px; }
        
        /* Filter Panel */
        .filter-panel { position: fixed; right: -300px; top: 0; bottom: 0; width: 300px; background: #fff; box-shadow: -2px 0 8px rgba(0,0,0,0.1); transition: right 0.3s; z-index: 200; overflow-y: auto; }
        .filter-panel.active { right: 0; }
        .filter-header { padding: 16px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
        .filter-header h3 { font-size: 18px; }
        .close-filter { background: none; border: none; font-size: 24px; cursor: pointer; }
        .filter-group { padding: 16px; border-bottom: 1px solid #e0e0e0; }
        .filter-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .filter-option { display: block; padding: 8px; margin: 4px 0; cursor: pointer; }
        .filter-option input { margin-right: 8px; }
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 150; }
        .overlay.active { display: block; }
        
        /* Results */
        .container { max-width: 1200px; margin: 0 auto; padding: 16px; }
        .results-info { padding: 12px 0; color: #666; font-size: 14px; }
        .results-grid { display: flex; flex-direction: column; gap: 12px; }
        .result-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.2s; }
        .result-card:hover { transform: translateX(4px); box-shadow: 0 4px 16px rgba(249,115,22,0.2); }
        .result-card a { text-decoration: none; color: inherit; display: flex; width: 100%; min-height: 120px; }
        .result-image { width: 120px; height: 120px; min-width: 120px; background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .result-image img { width: 100%; height: 100%; object-fit: cover; }
        .no-image { font-size: 50px; color: #d1d5db; }
        .result-info { padding: 14px 16px; flex: 1; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .result-info h3 { font-size: 16px; margin-bottom: 6px; font-weight: 600; color: #1a1a2e; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .result-meta { font-size: 13px; color: #6b7280; margin-bottom: 8px; display: flex; align-items: center; gap: 4px; }
        .result-meta i { color: #f97316; font-size: 12px; }
        .result-price { font-size: 18px; font-weight: 700; color: #f97316; margin-top: auto; }
        .result-price small { font-size: 13px; font-weight: 500; color: #9ca3af; }
        
        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 80px; color: #ccc; margin-bottom: 20px; }
        .empty-state h2 { font-size: 24px; margin-bottom: 12px; }
        .empty-state p { color: #666; }
        
        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 8px; padding: 24px 0; }
        .pagination a, .pagination span { padding: 8px 12px; background: #fff; border: 1px solid #e0e0e0; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination .active { background: #f97316; color: #fff; border-color: #f97316; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .result-card a { min-height: 110px; }
            .result-image { width: 110px; height: 110px; min-width: 110px; }
            .result-info { padding: 12px 14px; }
            .result-info h3 { font-size: 15px; }
            .result-price { font-size: 17px; }
        }
        @media (max-width: 480px) {
            .header-content { gap: 8px; }
            .back-btn { padding: 6px; font-size: 18px; }
            .filter-btn { padding: 8px 12px; font-size: 13px; }
            .result-card a { min-height: 100px; }
            .result-image { width: 100px; height: 100px; min-width: 100px; }
            .result-info { padding: 10px 12px; }
            .result-info h3 { font-size: 14px; -webkit-line-clamp: 2; }
            .result-meta { font-size: 12px; }
            .result-price { font-size: 16px; }
            .no-image { font-size: 40px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-content">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" value="<?= e($search_query) ?>" placeholder="Search products, properties, vehicles...">
            <?php if (!empty($search_query)): ?>
                <button class="clear-btn" onclick="clearSearch()">
                    <i class="fas fa-times"></i>
                </button>
            <?php endif; ?>
        </div>
        <button class="filter-btn" onclick="toggleFilter()">
            <i class="fas fa-filter"></i>
        </button>
    </div>
</div>

<!-- Filter Panel -->
<div class="filter-panel" id="filterPanel">
    <div class="filter-header">
        <h3>Filters</h3>
        <button class="close-filter" onclick="toggleFilter()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="filter-group">
        <label>Category</label>
        <label class="filter-option">
            <input type="radio" name="category" value="all" <?= $category === 'all' ? 'checked' : '' ?> onchange="applyFilter()">
            All Categories
        </label>
        <label class="filter-option">
            <input type="radio" name="category" value="products" <?= $category === 'products' ? 'checked' : '' ?> onchange="applyFilter()">
            📦 Products
        </label>
        <label class="filter-option">
            <input type="radio" name="category" value="properties" <?= $category === 'properties' ? 'checked' : '' ?> onchange="applyFilter()">
            🏠 Properties
        </label>
        <label class="filter-option">
            <input type="radio" name="category" value="vehicles" <?= $category === 'vehicles' ? 'checked' : '' ?> onchange="applyFilter()">
            🚗 Vehicles
        </label>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleFilter()"></div>

<!-- Results -->
<div class="container">
    <?php if (!empty($search_query)): ?>
        <div class="results-info">
            Found <?= $total ?> result<?= $total != 1 ? 's' : '' ?> for "<?= e($search_query) ?>"
        </div>
    <?php endif; ?>
    
    <?php if (!empty($results)): ?>
        <div class="results-grid">
            <?php foreach ($results as $item): 
                $url = $item['type'] === 'product' ? 'product-detail.php?id=' . $item['id'] : 
                       ($item['type'] === 'property' ? 'property-detail.php?id=' . $item['id'] : 'vehicle-detail.php?id=' . $item['id']);
                $img = !empty($item['image_path']) ? $item['image_path'] : '';
            ?>
                <div class="result-card">
                    <a href="<?= $url ?>">
                        <div class="result-image">
                            <?php if ($img): ?>
                                <img src="<?= $img ?>" alt="<?= e($item['name']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <?= $item['type'] === 'product' ? '📦' : ($item['type'] === 'property' ? '🏠' : '🚗') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="result-info">
                            <h3><?= e($item['name']) ?></h3>
                            <?php if ($item['type'] === 'property' && !empty($item['city'])): ?>
                                <div class="result-meta">📍 <?= e($item['city']) ?></div>
                            <?php elseif ($item['type'] === 'vehicle' && !empty($item['year'])): ?>
                                <div class="result-meta">📅 <?= $item['year'] ?></div>
                            <?php elseif ($item['type'] === 'product' && isset($item['stock'])): ?>
                                <div class="result-meta"><?= $item['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock' ?></div>
                            <?php endif; ?>
                            <div class="result-price">
                                <?= formatCurrency($item['price']) ?><?= $item['type'] === 'vehicle' ? '/day' : '' ?>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($total > $per_page): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?q=<?= urlencode($search_query) ?>&category=<?= $category ?>&page=<?= $page - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min(ceil($total / $per_page), $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?q=<?= urlencode($search_query) ?>&category=<?= $category ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < ceil($total / $per_page)): ?>
                    <a href="?q=<?= urlencode($search_query) ?>&category=<?= $category ?>&page=<?= $page + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php elseif (!empty($search_query)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h2>No results found</h2>
            <p>Try different keywords or filters</p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h2>Start Searching</h2>
            <p>Find products, properties, and vehicles</p>
        </div>
    <?php endif; ?>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const filterPanel = document.getElementById('filterPanel');
const overlay = document.getElementById('overlay');

searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const query = this.value.trim();
        if (query) {
            window.location.href = 'search.php?q=' + encodeURIComponent(query);
        }
    }
});

function clearSearch() {
    window.location.href = 'search.php';
}

function toggleFilter() {
    filterPanel.classList.toggle('active');
    overlay.classList.toggle('active');
}

function applyFilter() {
    const category = document.querySelector('input[name="category"]:checked').value;
    const query = '<?= e($search_query) ?>';
    window.location.href = 'search.php?q=' + encodeURIComponent(query) + '&category=' + category;
}
</script>

</body>
</html>
