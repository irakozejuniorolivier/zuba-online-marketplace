<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = 'Add Product';
$errors = [];

$categories = $conn->query("SELECT id, name FROM categories WHERE type='ecommerce' AND status='active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $compare_price = (float)($_POST['compare_price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $sku         = trim($_POST['sku'] ?? '');
    $brand       = trim($_POST['brand'] ?? '');
    $condition   = $_POST['condition'] ?? 'new';
    $description = trim($_POST['description'] ?? '');
    $featured    = isset($_POST['featured']) ? 1 : 0;
    $status      = $_POST['status'] ?? 'active';

    if (!$name)        $errors[] = 'Product name is required.';
    if (!$category_id) $errors[] = 'Category is required.';
    if ($price <= 0)   $errors[] = 'Valid price is required.';

    if (empty($errors)) {
        // Generate unique slug
        $slug = generateSlug($name);
        $original_slug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM products WHERE slug = '$slug'")->num_rows > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, compare_price, stock, sku, brand, `condition`, featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssddisssis", $category_id, $name, $slug, $description, $price, $compare_price, $stock, $sku, $brand, $condition, $featured, $status);
        $stmt->execute();
        $product_id = $stmt->insert_id;
        $stmt->close();

        if (!empty($_FILES['images']['name'][0])) {
            $is_primary = 1;
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] !== 0) continue;
                $file = [
                    'tmp_name' => $tmp,
                    'name'     => $_FILES['images']['name'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                ];
                $upload = uploadFile($file, PRODUCT_UPLOAD_DIR);
                if ($upload['success']) {
                    $img = $upload['filename'];
                    $stmt2 = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?,?,?,?)");
                    $stmt2->bind_param("isii", $product_id, $img, $is_primary, $i);
                    $stmt2->execute();
                    $stmt2->close();
                    $is_primary = 0;
                }
            }
        }

        logActivity('admin', $admin['id'], 'ADD_PRODUCT', "Added product: $name");
        redirect(ADMIN_URL . '/products.php?msg=Product added successfully');
    }
}

require_once 'includes/header.php';
?>

<style>
    .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-header h2 { font-size:22px; font-weight:800; color:#1a1a1a; }
    .btn-back { padding:9px 18px; background:#f3f4f6; color:#666; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; }

    .form-grid { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
    .card { background:white; border:1px solid #e5e5e5; border-radius:14px; overflow:hidden; margin-bottom:20px; }
    .card-header { padding:16px 22px; border-bottom:1px solid #f0f0f0; font-size:15px; font-weight:700; color:#1a1a1a; }
    .card-body { padding:22px; }

    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.4px; }
    .form-group input, .form-group select, .form-group textarea {
        width:100%; padding:10px 14px; border:1px solid #e5e5e5; border-radius:8px;
        font-size:13px; color:#1a1a1a; outline:none; transition:border 0.2s; background:white;
        font-family:inherit;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color:#f97316; }
    .form-group textarea { resize:vertical; min-height:120px; }

    .checkbox-group { display:flex; align-items:center; gap:8px; }
    .checkbox-group input[type=checkbox] { width:16px; height:16px; accent-color:#f97316; }
    .checkbox-group label { font-size:13px; font-weight:500; color:#333; text-transform:none; letter-spacing:0; margin:0; }

    .image-upload-area { border:2px dashed #e5e5e5; border-radius:10px; padding:30px; text-align:center; cursor:pointer; transition:all 0.2s; }
    .image-upload-area:hover { border-color:#f97316; background:#fff5f0; }
    .image-upload-area input { display:none; }
    .image-upload-area p { font-size:13px; color:#aaa; margin-top:8px; }
    .image-preview { display:flex; flex-wrap:wrap; gap:8px; margin-top:12px; }
    .image-preview img { width:70px; height:70px; object-fit:cover; border-radius:8px; border:1px solid #e5e5e5; }

    .btn-submit { width:100%; padding:12px; background:linear-gradient(135deg,#f97316,#fb923c); color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer; transition:all 0.2s; }
    .btn-submit:hover { opacity:0.9; }

    .alert-error { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:13px; }
    .alert-error ul { margin:6px 0 0 16px; }

    @media (max-width:900px) { .form-grid { grid-template-columns:1fr; } }
    @media (max-width:480px) { .form-row { grid-template-columns:1fr; } }
</style>

<div class="page-header">
    <h2>📦 Add Product</h2>
    <a href="products.php" class="btn-back">← Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert-error"><strong>Please fix the following:</strong><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="form-grid">

    <div>
        <div class="card">
            <div class="card-header">Basic Information</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Product Name *</label>
                    <input type="text" name="name" value="<?php echo e($_POST['name'] ?? ''); ?>" placeholder="Enter product name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter product description..."><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo e($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand</label>
                        <input type="text" name="brand" value="<?php echo e($_POST['brand'] ?? ''); ?>" placeholder="Brand name">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Pricing & Stock</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (FRw) *</label>
                        <input type="number" name="price" value="<?php echo e($_POST['price'] ?? ''); ?>" placeholder="0" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Compare Price (FRw)</label>
                        <input type="number" name="compare_price" value="<?php echo e($_POST['compare_price'] ?? ''); ?>" placeholder="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" value="<?php echo e($_POST['stock'] ?? '0'); ?>" placeholder="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>SKU</label>
                        <input type="text" name="sku" value="<?php echo e($_POST['sku'] ?? ''); ?>" placeholder="e.g. PROD-001">
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Product Images</div>
            <div class="card-body">
                <div class="image-upload-area" onclick="document.getElementById('images').click()">
                    <div style="font-size:32px;">📷</div>
                    <p>Click to upload images (first image = primary)</p>
                    <p style="font-size:11px;">JPG, PNG, WEBP up to 5MB each</p>
                    <input type="file" id="images" name="images[]" multiple accept="image/*" onchange="previewImages(this)">
                </div>
                <div class="image-preview" id="imagePreview"></div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">Settings</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition">
                        <?php foreach (PRODUCT_CONDITIONS as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo ($_POST['condition'] ?? 'new') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active"   <?php echo ($_POST['status'] ?? 'active') === 'active'   ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="featured" id="featured" <?php echo isset($_POST['featured']) ? 'checked' : ''; ?>>
                        <label for="featured">Mark as Featured</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn-submit">✅ Save Product</button>
            </div>
        </div>
    </div>

</div>
</form>

<script>
function previewImages(input) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
