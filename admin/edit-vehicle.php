<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('vehicles.php');

$vehicle = $conn->query("SELECT * FROM vehicles WHERE id = $id")->fetch_assoc();
if (!$vehicle) redirect('vehicles.php');

$page_title = 'Edit Vehicle';
$errors = [];

$categories = $conn->query("SELECT * FROM categories WHERE type = 'carrental' AND status = 'active' ORDER BY name");
$images = $conn->query("SELECT * FROM vehicle_images WHERE vehicle_id = $id ORDER BY is_primary DESC, sort_order ASC")->fetch_all(MYSQLI_ASSOC);

// Delete single image
if (isset($_GET['del_img'])) {
    $img_id = (int)$_GET['del_img'];
    $img = $conn->query("SELECT * FROM vehicle_images WHERE id = $img_id AND vehicle_id = $id")->fetch_assoc();
    if ($img) {
        @unlink('../' . $img['image_path']);
        $conn->query("DELETE FROM vehicle_images WHERE id = $img_id");
    }
    redirect("edit-vehicle.php?id=$id");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $daily_rate = (float)($_POST['daily_rate'] ?? 0);
    $plate_number = trim($_POST['plate_number'] ?? '');

    if (!$brand) $errors[] = 'Brand is required.';
    if (!$model) $errors[] = 'Model is required.';
    if ($year < 1900 || $year > date('Y') + 1) $errors[] = 'Valid year is required.';
    if (!$category_id) $errors[] = 'Category is required.';
    if (!$vehicle_type) $errors[] = 'Vehicle type is required.';
    if ($daily_rate <= 0) $errors[] = 'Daily rate must be greater than 0.';
    if (!$plate_number) $errors[] = 'Plate number is required.';

    if (empty($errors)) {
        $description = trim($_POST['description'] ?? '');
        $transmission = $_POST['transmission'] ?? 'manual';
        $fuel_type = $_POST['fuel_type'] ?? 'petrol';
        $seats = (int)($_POST['seats'] ?? 5);
        $doors = (int)($_POST['doors'] ?? 4);
        $color = trim($_POST['color'] ?? '');
        $mileage = (int)($_POST['mileage'] ?? 0);
        $weekly_rate = (float)($_POST['weekly_rate'] ?? 0);
        $monthly_rate = (float)($_POST['monthly_rate'] ?? 0);
        $insurance_included = isset($_POST['insurance_included']) ? 1 : 0;
        $features = trim($_POST['features'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status = $_POST['status'] ?? 'available';
        $featured = isset($_POST['featured']) ? 1 : 0;

        // Unique slug excluding current
        $slug = generateSlug($brand . ' ' . $model . ' ' . $year);
        $original_slug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM vehicles WHERE slug = '$slug' AND id != $id")->num_rows > 0) {
            $slug = $original_slug . '-' . $counter++;
        }

        $stmt = $conn->prepare("UPDATE vehicles SET category_id=?, brand=?, model=?, year=?, slug=?, description=?, vehicle_type=?, transmission=?, fuel_type=?, seats=?, doors=?, color=?, plate_number=?, mileage=?, daily_rate=?, weekly_rate=?, monthly_rate=?, insurance_included=?, features=?, location=?, featured=?, status=? WHERE id=?");
        $stmt->bind_param("ississssiissidddisssisi", $category_id, $brand, $model, $year, $slug, $description, $vehicle_type, $transmission, $fuel_type, $seats, $doors, $color, $plate_number, $mileage, $daily_rate, $weekly_rate, $monthly_rate, $insurance_included, $features, $location, $featured, $status, $id);
        $stmt->execute();
        $stmt->close();

        // Upload new images
        if (!empty($_FILES['images']['name'][0])) {
            $existing_count = count($images);
            $upload_dir = '../uploads/vehicles/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                if ($_FILES['images']['error'][$k] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
                if ($_FILES['images']['size'][$k] > 5242880) continue;
                $new_name = uniqid() . '_' . time() . '.' . $ext;
                $image_path = 'uploads/vehicles/' . $new_name;
                if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
                    $is_primary = ($existing_count === 0 && $k === 0) ? 1 : 0;
                    $sort = $existing_count + $k;
                    $conn->query("INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, sort_order, created_at) VALUES ($id, '$image_path', $is_primary, $sort, NOW())");
                }
            }
        }

        logActivity('admin', $admin['id'], 'edit_vehicle', "Updated vehicle: $brand $model");
        setFlash('success', 'Vehicle updated successfully.');
        redirect('vehicles.php');
    }

    // Merge POST back for re-display
    $vehicle = array_merge($vehicle, $_POST);
}

require_once 'includes/header.php';
?>

<style>
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem; }
.page-header h1 { margin:0; font-size:1.75rem; color:#1a1a2e; }
.page-header p { margin:.25rem 0 0; color:#666; }
.btn { padding:.625rem 1.25rem; border:none; border-radius:6px; cursor:pointer; font-size:.875rem; font-weight:500; text-decoration:none; display:inline-flex; align-items:center; gap:.5rem; transition:all .2s; }
.btn-primary { background:#f97316; color:#fff; }
.btn-primary:hover { background:#ea580c; }
.btn-secondary { background:#6b7280; color:#fff; }
.btn-secondary:hover { background:#4b5563; }
.alert-danger { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; padding:1rem; border-radius:6px; margin-bottom:1.5rem; }
.alert-danger ul { margin:.5rem 0 0 1.5rem; padding:0; }
.form-container { display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; }
.card { background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding:1.5rem; margin-bottom:1.5rem; }
.card h3 { margin:0 0 1.5rem; font-size:1.125rem; color:#1a1a2e; padding-bottom:.75rem; border-bottom:2px solid #f97316; }
.form-group { margin-bottom:1.25rem; }
.form-group label { display:block; margin-bottom:.5rem; font-weight:500; color:#374151; font-size:.875rem; }
.form-group label .req { color:#ef4444; }
.form-group input, .form-group select, .form-group textarea { width:100%; padding:.625rem; border:1px solid #e5e7eb; border-radius:6px; font-size:.875rem; font-family:inherit; box-sizing:border-box; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:#f97316; }
.form-group textarea { resize:vertical; min-height:100px; }
.form-group small { display:block; margin-top:.25rem; color:#6b7280; font-size:.75rem; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; }
.checkbox-group { display:flex; align-items:center; gap:.5rem; padding:.75rem; background:#f9fafb; border-radius:6px; }
.checkbox-group input[type=checkbox] { width:auto; margin:0; accent-color:#f97316; }
.checkbox-group label { margin:0; font-weight:normal; }
.existing-images { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:1rem; }
.existing-img-wrap { position:relative; }
.existing-img-wrap img { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #e5e7eb; display:block; }
.primary-badge { position:absolute; top:4px; left:4px; background:#f97316; color:#fff; font-size:9px; font-weight:700; padding:2px 5px; border-radius:4px; }
.del-img { position:absolute; top:4px; right:4px; background:#ef4444; color:#fff; border:none; border-radius:4px; width:20px; height:20px; font-size:11px; cursor:pointer; display:flex; align-items:center; justify-content:center; text-decoration:none; }
.image-upload-area { border:2px dashed #e5e7eb; border-radius:8px; padding:2rem; text-align:center; cursor:pointer; transition:all .2s; }
.image-upload-area:hover { border-color:#f97316; background:#fff7ed; }
.image-upload-area svg { margin:0 auto .75rem; color:#f97316; }
.image-upload-area p { margin:.5rem 0; color:#6b7280; font-size:.875rem; }
.image-upload-area input { display:none; }
.image-preview-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(80px,1fr)); gap:.75rem; margin-top:1rem; }
.image-preview-grid img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:6px; border:1px solid #e5e7eb; }
.form-actions { display:flex; gap:1rem; justify-content:flex-end; margin-top:1rem; }
@media (max-width:768px) {
    .form-container { grid-template-columns:1fr; }
    .form-row, .form-row-3 { grid-template-columns:1fr; }
    .form-actions { flex-direction:column; }
    .form-actions .btn { width:100%; justify-content:center; }
}
</style>

<div class="page-header">
    <div>
        <h1>Edit Vehicle</h1>
        <p>Update vehicle listing details</p>
    </div>
    <a href="vehicles.php" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Vehicles
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="form-container">

    <!-- Left Column -->
    <div>
        <!-- Basic Information -->
        <div class="card">
            <h3>Basic Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Brand <span class="req">*</span></label>
                    <input type="text" name="brand" value="<?= e($vehicle['brand']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Model <span class="req">*</span></label>
                    <input type="text" name="model" value="<?= e($vehicle['model']) ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Year <span class="req">*</span></label>
                    <input type="number" name="year" value="<?= $vehicle['year'] ?>" min="1900" max="<?= date('Y') + 1 ?>" required>
                </div>
                <div class="form-group">
                    <label>Category <span class="req">*</span></label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $vehicle['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Vehicle Type <span class="req">*</span></label>
                    <select name="vehicle_type" required>
                        <option value="">Select Type</option>
                        <?php foreach (['sedan'=>'Sedan','suv'=>'SUV','truck'=>'Truck','van'=>'Van','coupe'=>'Coupe','convertible'=>'Convertible','hatchback'=>'Hatchback','minivan'=>'Minivan'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $vehicle['vehicle_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Plate Number <span class="req">*</span></label>
                    <input type="text" name="plate_number" value="<?= e($vehicle['plate_number']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="6"><?= e($vehicle['description']) ?></textarea>
            </div>
        </div>

        <!-- Vehicle Specifications -->
        <div class="card">
            <h3>Vehicle Specifications</h3>

            <div class="form-row">
                <div class="form-group">
                    <label>Transmission</label>
                    <select name="transmission">
                        <option value="manual" <?= $vehicle['transmission'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                        <option value="automatic" <?= $vehicle['transmission'] === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fuel Type</label>
                    <select name="fuel_type">
                        <?php foreach (['petrol'=>'Petrol','diesel'=>'Diesel','electric'=>'Electric','hybrid'=>'Hybrid'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $vehicle['fuel_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Seats</label>
                    <input type="number" name="seats" value="<?= $vehicle['seats'] ?>" min="1" max="50">
                </div>
                <div class="form-group">
                    <label>Doors</label>
                    <input type="number" name="doors" value="<?= $vehicle['doors'] ?>" min="2" max="6">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" value="<?= e($vehicle['color']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Mileage (km)</label>
                <input type="number" name="mileage" value="<?= $vehicle['mileage'] ?>" min="0">
            </div>

            <div class="form-group">
                <label>Features & Amenities</label>
                <textarea name="features" rows="4"><?= e($vehicle['features']) ?></textarea>
                <small>e.g., GPS, Air Conditioning, Bluetooth (comma separated)</small>
            </div>
        </div>

        <!-- Rental Rates -->
        <div class="card">
            <h3>Rental Rates</h3>

            <div class="form-group">
                <label>Daily Rate <span class="req">*</span></label>
                <input type="number" name="daily_rate" value="<?= $vehicle['daily_rate'] ?>" step="0.01" min="0" required>
                <small>Price per day in <?= CURRENCY ?></small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Weekly Rate</label>
                    <input type="number" name="weekly_rate" value="<?= $vehicle['weekly_rate'] ?>" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Monthly Rate</label>
                    <input type="number" name="monthly_rate" value="<?= $vehicle['monthly_rate'] ?>" step="0.01" min="0">
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="insurance_included" id="insurance" value="1" <?= $vehicle['insurance_included'] ? 'checked' : '' ?>>
                <label for="insurance">Insurance Included</label>
            </div>
        </div>

        <!-- Location -->
        <div class="card">
            <h3>Location</h3>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= e($vehicle['location']) ?>">
                <small>Where the vehicle is available for pickup</small>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Images -->
        <div class="card">
            <h3>Vehicle Images</h3>

            <?php if (!empty($images)): ?>
            <p style="font-size:.75rem;color:#6b7280;margin-bottom:.75rem;">Existing images — click ✕ to remove</p>
            <div class="existing-images">
                <?php foreach ($images as $img): ?>
                <div class="existing-img-wrap">
                    <img src="<?= SITE_URL . '/' . e($img['image_path']) ?>" alt="">
                    <?php if ($img['is_primary']): ?><span class="primary-badge">Primary</span><?php endif; ?>
                    <a href="edit-vehicle.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>" class="del-img" onclick="return confirm('Remove this image?')">✕</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="image-upload-area" id="uploadArea">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p><strong>Click to upload</strong> or drag and drop</p>
                <p style="font-size:.75rem;">PNG, JPG, GIF up to 5MB</p>
                <input type="file" name="images[]" id="imageInput" multiple accept="image/*">
            </div>
            <div class="image-preview-grid" id="imagePreview"></div>
        </div>

        <!-- Status -->
        <div class="card">
            <h3>Status</h3>

            <div class="form-group">
                <label>Vehicle Status</label>
                <select name="status">
                    <?php foreach (['available'=>'Available','rented'=>'Rented','maintenance'=>'Maintenance','inactive'=>'Inactive'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $vehicle['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="featured" id="featured" value="1" <?= $vehicle['featured'] ? 'checked' : '' ?>>
                <label for="featured">Mark as Featured</label>
            </div>
        </div>

        <div class="form-actions">
            <a href="vehicles.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                Update Vehicle
            </button>
        </div>
    </div>

</div>
</form>

<script>
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
let selectedFiles = [];

uploadArea.addEventListener('click', () => imageInput.click());
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.style.borderColor = '#f97316'; });
uploadArea.addEventListener('dragleave', () => { uploadArea.style.borderColor = '#e5e7eb'; });
uploadArea.addEventListener('drop', e => { e.preventDefault(); uploadArea.style.borderColor = '#e5e7eb'; handleFiles(e.dataTransfer.files); });
imageInput.addEventListener('change', function() { handleFiles(this.files); });

function handleFiles(files) {
    selectedFiles = [...selectedFiles, ...Array.from(files)];
    renderPreview();
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    imageInput.files = dt.files;
}

function renderPreview() {
    imagePreview.innerHTML = '';
    selectedFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
