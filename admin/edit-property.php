<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('properties.php');

$property = $conn->query("SELECT * FROM properties WHERE id = $id")->fetch_assoc();
if (!$property) redirect('properties.php');

$page_title = 'Edit Property';
$errors = [];

$categories = $conn->query("SELECT * FROM categories WHERE type = 'realestate' AND status = 'active' ORDER BY name");
$images = $conn->query("SELECT * FROM property_images WHERE property_id = $id ORDER BY is_primary DESC, sort_order ASC")->fetch_all(MYSQLI_ASSOC);

// Delete single image
if (isset($_GET['del_img'])) {
    $img_id = (int)$_GET['del_img'];
    $img = $conn->query("SELECT * FROM property_images WHERE id = $img_id AND property_id = $id")->fetch_assoc();
    if ($img) {
        @unlink('../uploads/properties/' . $img['image_path']);
        $conn->query("DELETE FROM property_images WHERE id = $img_id");
    }
    redirect("edit-property.php?id=$id");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title          = trim($_POST['title'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $listing_type   = $_POST['listing_type'] ?? '';
    $property_type  = $_POST['property_type'] ?? '';
    $price          = (float)($_POST['price'] ?? 0);
    $description    = trim($_POST['description'] ?? '');
    $city           = trim($_POST['city'] ?? '');

    if (!$title)        $errors[] = 'Property title is required.';
    if (!$category_id)  $errors[] = 'Category is required.';
    if (!$listing_type) $errors[] = 'Listing type is required.';
    if (!$property_type)$errors[] = 'Property type is required.';
    if ($price <= 0)    $errors[] = 'Price must be greater than 0.';
    if (!$city)         $errors[] = 'City is required.';

    if (empty($errors)) {
        $rent_period    = $_POST['rent_period'] ?? null;
        $bedrooms       = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms      = (int)($_POST['bathrooms'] ?? 0);
        $area           = (float)($_POST['area'] ?? 0);
        $area_unit      = $_POST['area_unit'] ?? 'sqm';
        $address        = trim($_POST['address'] ?? '');
        $state          = trim($_POST['state'] ?? '');
        $country        = trim($_POST['country'] ?? 'Rwanda');
        $zip_code       = trim($_POST['zip_code'] ?? '');
        $latitude       = (float)($_POST['latitude'] ?? 0);
        $longitude      = (float)($_POST['longitude'] ?? 0);
        $year_built     = (int)($_POST['year_built'] ?? 0);
        $parking_spaces = (int)($_POST['parking_spaces'] ?? 0);
        $features       = trim($_POST['features'] ?? '');
        $status         = $_POST['status'] ?? 'active';
        $featured       = isset($_POST['featured']) ? 1 : 0;

        // Unique slug excluding current
        $slug = generateSlug($title);
        $original_slug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM properties WHERE slug = '$slug' AND id != $id")->num_rows > 0) {
            $slug = $original_slug . '-' . $counter++;
        }

        $stmt = $conn->prepare("UPDATE properties SET category_id=?, title=?, slug=?, description=?, listing_type=?, property_type=?, price=?, rent_period=?, bedrooms=?, bathrooms=?, area=?, area_unit=?, address=?, city=?, state=?, country=?, zip_code=?, latitude=?, longitude=?, year_built=?, parking_spaces=?, features=?, status=?, featured=? WHERE id=?");
        $stmt->bind_param("isssssdsiiissssssddiissii", $category_id, $title, $slug, $description, $listing_type, $property_type, $price, $rent_period, $bedrooms, $bathrooms, $area, $area_unit, $address, $city, $state, $country, $zip_code, $latitude, $longitude, $year_built, $parking_spaces, $features, $status, $featured, $id);
        $stmt->execute();
        $stmt->close();

        // Upload new images
        if (!empty($_FILES['images']['name'][0])) {
            $existing_count = count($images);
            $upload_dir = '../uploads/properties/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            foreach ($_FILES['images']['tmp_name'] as $k => $tmp) {
                if ($_FILES['images']['error'][$k] !== UPLOAD_ERR_OK) continue;
                $ext = strtolower(pathinfo($_FILES['images']['name'][$k], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) continue;
                if ($_FILES['images']['size'][$k] > 5242880) continue;
                $new_name = uniqid() . '_' . time() . '.' . $ext;
                if (move_uploaded_file($tmp, $upload_dir . $new_name)) {
                    $is_primary = ($existing_count === 0 && $k === 0) ? 1 : 0;
                    $sort = $existing_count + $k;
                    $conn->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order, created_at) VALUES ($id, '$new_name', $is_primary, $sort, NOW())");
                }
            }
        }

        logActivity('admin', $admin['id'], 'edit_property', "Updated property: $title");
        setFlash('success', 'Property updated successfully.');
        redirect('properties.php');
    }

    // Merge POST back for re-display
    $property = array_merge($property, $_POST);
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
.image-upload-area i { font-size:2.5rem; color:#f97316; margin-bottom:.75rem; }
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
        <h1>Edit Property</h1>
        <p>Update real estate listing details</p>
    </div>
    <a href="properties.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Properties
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

            <div class="form-group">
                <label>Property Title <span class="req">*</span></label>
                <input type="text" name="title" value="<?= e($property['title']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Category <span class="req">*</span></label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['id'] ?>" <?= $property['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Listing Type <span class="req">*</span></label>
                    <select name="listing_type" id="listingType" required>
                        <option value="">Select Type</option>
                        <option value="sale" <?= $property['listing_type'] === 'sale' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= $property['listing_type'] === 'rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Property Type <span class="req">*</span></label>
                    <select name="property_type" required>
                        <option value="">Select Type</option>
                        <?php foreach (['apartment'=>'Apartment','house'=>'House','villa'=>'Villa','commercial'=>'Commercial','land'=>'Land','office'=>'Office'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $property['property_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price <span class="req">*</span></label>
                    <input type="number" name="price" value="<?= $property['price'] ?>" step="0.01" min="0" required>
                    <small>In <?= CURRENCY ?></small>
                </div>
            </div>

            <div class="form-group" id="rentPeriodGroup" style="display:<?= $property['listing_type'] === 'rent' ? 'block' : 'none' ?>;">
                <label>Rent Period</label>
                <select name="rent_period">
                    <option value="">Select Period</option>
                    <?php foreach (['day'=>'Per Day','week'=>'Per Week','month'=>'Per Month','year'=>'Per Year'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($property['rent_period'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="6"><?= e($property['description']) ?></textarea>
            </div>
        </div>

        <!-- Property Details -->
        <div class="card">
            <h3>Property Details</h3>

            <div class="form-row-3">
                <div class="form-group">
                    <label>Bedrooms</label>
                    <input type="number" name="bedrooms" value="<?= $property['bedrooms'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Bathrooms</label>
                    <input type="number" name="bathrooms" value="<?= $property['bathrooms'] ?>" min="0">
                </div>
                <div class="form-group">
                    <label>Parking Spaces</label>
                    <input type="number" name="parking_spaces" value="<?= $property['parking_spaces'] ?>" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Area</label>
                    <input type="number" name="area" value="<?= $property['area'] ?>" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Area Unit</label>
                    <select name="area_unit">
                        <?php foreach (['sqm'=>'Square Meters (sqm)','sqft'=>'Square Feet (sqft)','acres'=>'Acres','hectares'=>'Hectares'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($property['area_unit'] ?? 'sqm') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Year Built</label>
                <input type="number" name="year_built" value="<?= $property['year_built'] ?>" min="1900" max="<?= date('Y') ?>">
            </div>

            <div class="form-group">
                <label>Features & Amenities</label>
                <textarea name="features" rows="3"><?= e($property['features']) ?></textarea>
                <small>e.g., Swimming pool, Garden, Security, Gym (comma separated)</small>
            </div>
        </div>

        <!-- Location -->
        <div class="card">
            <h3>Location</h3>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?= e($property['address']) ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City <span class="req">*</span></label>
                    <input type="text" name="city" value="<?= e($property['city']) ?>" required>
                </div>
                <div class="form-group">
                    <label>State/Province</label>
                    <input type="text" name="state" value="<?= e($property['state']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Country</label>
                    <input type="text" name="country" value="<?= e($property['country'] ?: 'Rwanda') ?>">
                </div>
                <div class="form-group">
                    <label>Zip/Postal Code</label>
                    <input type="text" name="zip_code" value="<?= e($property['zip_code']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" name="latitude" value="<?= $property['latitude'] ?>" step="0.000001">
                    <small>For map integration (optional)</small>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" name="longitude" value="<?= $property['longitude'] ?>" step="0.000001">
                    <small>For map integration (optional)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <!-- Images -->
        <div class="card">
            <h3>Property Images</h3>

            <?php if (!empty($images)): ?>
            <p style="font-size:.75rem;color:#6b7280;margin-bottom:.75rem;">Existing images — click ✕ to remove</p>
            <div class="existing-images">
                <?php foreach ($images as $img): ?>
                <div class="existing-img-wrap">
                    <img src="<?= UPLOAD_URL . 'properties/' . e($img['image_path']) ?>" alt="">
                    <?php if ($img['is_primary']): ?><span class="primary-badge">Primary</span><?php endif; ?>
                    <a href="edit-property.php?id=<?= $id ?>&del_img=<?= $img['id'] ?>" class="del-img" onclick="return confirm('Remove this image?')">✕</a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="image-upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
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
                <label>Property Status</label>
                <select name="status">
                    <?php foreach (['active'=>'Active','inactive'=>'Inactive','sold'=>'Sold','rented'=>'Rented'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $property['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="featured" id="featured" value="1" <?= $property['featured'] ? 'checked' : '' ?>>
                <label for="featured">Mark as Featured</label>
            </div>
        </div>

        <div class="form-actions">
            <a href="properties.php" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Property
            </button>
        </div>
    </div>

</div>
</form>

<script>
document.getElementById('listingType').addEventListener('change', function() {
    document.getElementById('rentPeriodGroup').style.display = this.value === 'rent' ? 'block' : 'none';
});

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
