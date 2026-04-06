<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = 'Add Property';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate required fields
    $title = trim($_POST['title'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $listing_type = $_POST['listing_type'] ?? '';
    $property_type = $_POST['property_type'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $city = trim($_POST['city'] ?? '');
    
    if (empty($title)) $errors[] = 'Property title is required';
    if ($category_id === 0) $errors[] = 'Category is required';
    if (empty($listing_type)) $errors[] = 'Listing type is required';
    if (empty($property_type)) $errors[] = 'Property type is required';
    if ($price <= 0) $errors[] = 'Price must be greater than 0';
    if (empty($city)) $errors[] = 'City is required';
    
    // Optional fields
    $rent_period = $_POST['rent_period'] ?? null;
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $area = floatval($_POST['area'] ?? 0);
    $area_unit = $_POST['area_unit'] ?? 'sqm';
    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? 'Rwanda');
    $zip_code = trim($_POST['zip_code'] ?? '');
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    $year_built = intval($_POST['year_built'] ?? 0);
    $parking_spaces = intval($_POST['parking_spaces'] ?? 0);
    $features = trim($_POST['features'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($errors)) {
        // Generate slug
        $slug = generateSlug($title);
        
        // Check if slug exists
        $check = $conn->query("SELECT id FROM properties WHERE slug = '$slug'");
        if ($check->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Insert property
        $stmt = $conn->prepare("INSERT INTO properties (category_id, title, slug, description, listing_type, property_type, price, rent_period, bedrooms, bathrooms, area, area_unit, address, city, state, country, zip_code, latitude, longitude, year_built, parking_spaces, features, status, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->bind_param("isssssdsiiissssssddiissi", $category_id, $title, $slug, $description, $listing_type, $property_type, $price, $rent_period, $bedrooms, $bathrooms, $area, $area_unit, $address, $city, $state, $country, $zip_code, $latitude, $longitude, $year_built, $parking_spaces, $features, $status, $featured);
        
        if ($stmt->execute()) {
            $property_id = $conn->insert_id;
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/properties/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $is_primary = 1;
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES['images']['name'][$key];
                        $file_tmp = $_FILES['images']['tmp_name'][$key];
                        $file_size = $_FILES['images']['size'][$key];
                        
                        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        
                        if (in_array($ext, $allowed) && $file_size <= 5242880) {
                            $new_name = uniqid() . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                                $conn->query("INSERT INTO property_images (property_id, image_path, is_primary, sort_order, created_at) VALUES ($property_id, '$new_name', $is_primary, $key, NOW())");
                                $is_primary = 0;
                            }
                        }
                    }
                }
            }
            
            logActivity('admin', $admin['id'], 'add_property', 'Added new property: ' . $title);
            setFlash('success', 'Property added successfully');
            redirect('properties.php');
        } else {
            $errors[] = 'Failed to add property';
        }
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE type = 'realestate' AND status = 'active' ORDER BY name");

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
.btn-secondary {
    background: #6b7280;
    color: white;
}
.btn-secondary:hover {
    background: #4b5563;
}
.btn-primary {
    background: #f97316;
    color: white;
}
.btn-primary:hover {
    background: #ea580c;
}
.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}
.alert-danger {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
.alert ul {
    margin: 0.5rem 0 0 1.5rem;
    padding: 0;
}
.form-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1.5rem;
}
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 1.5rem;
}
.card h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.125rem;
    color: #1a1a2e;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #f97316;
}
.form-group {
    margin-bottom: 1.25rem;
}
.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}
.form-group label .required {
    color: #ef4444;
}
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.625rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    font-family: inherit;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f97316;
}
.form-group textarea {
    resize: vertical;
    min-height: 100px;
}
.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.75rem;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
.form-row-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}
.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
}
.checkbox-group input[type="checkbox"] {
    width: auto;
    margin: 0;
}
.checkbox-group label {
    margin: 0;
    font-weight: normal;
}
.image-upload-area {
    border: 2px dashed #e5e7eb;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}
.image-upload-area:hover {
    border-color: #f97316;
    background: #fff7ed;
}
.image-upload-area.dragover {
    border-color: #f97316;
    background: #fff7ed;
}
.image-upload-area i {
    font-size: 3rem;
    color: #f97316;
    margin-bottom: 1rem;
}
.image-upload-area p {
    margin: 0.5rem 0;
    color: #6b7280;
}
.image-upload-area input[type="file"] {
    display: none;
}
.image-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
}
.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-preview-item .remove-image {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    width: 24px;
    height: 24px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}
.image-preview-item .primary-badge {
    position: absolute;
    bottom: 0.25rem;
    left: 0.25rem;
    background: #f97316;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.625rem;
    font-weight: 600;
}
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}
@media (max-width: 768px) {
    .form-container {
        grid-template-columns: 1fr;
    }
    .form-row,
    .form-row-3 {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="page-header">
    <div>
        <h1>Add New Property</h1>
        <p>Create a new real estate listing</p>
    </div>
    <a href="properties.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Properties
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul>
        <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="form-container">
        <!-- Main Content -->
        <div>
            <!-- Basic Information -->
            <div class="card">
                <h3>Basic Information</h3>
                
                <div class="form-group">
                    <label>Property Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" required>
                    <small>Enter a descriptive title for the property</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Category <span class="required">*</span></label>
                        <select name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Listing Type <span class="required">*</span></label>
                        <select name="listing_type" id="listingType" required>
                            <option value="">Select Type</option>
                            <option value="sale" <?= ($_POST['listing_type'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
                            <option value="rent" <?= ($_POST['listing_type'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Property Type <span class="required">*</span></label>
                        <select name="property_type" required>
                            <option value="">Select Type</option>
                            <option value="apartment" <?= ($_POST['property_type'] ?? '') === 'apartment' ? 'selected' : '' ?>>Apartment</option>
                            <option value="house" <?= ($_POST['property_type'] ?? '') === 'house' ? 'selected' : '' ?>>House</option>
                            <option value="villa" <?= ($_POST['property_type'] ?? '') === 'villa' ? 'selected' : '' ?>>Villa</option>
                            <option value="commercial" <?= ($_POST['property_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                            <option value="land" <?= ($_POST['property_type'] ?? '') === 'land' ? 'selected' : '' ?>>Land</option>
                            <option value="office" <?= ($_POST['property_type'] ?? '') === 'office' ? 'selected' : '' ?>>Office</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Price <span class="required">*</span></label>
                        <input type="number" name="price" value="<?= e($_POST['price'] ?? '') ?>" step="0.01" min="0" required>
                        <small>Enter price in <?= CURRENCY ?></small>
                    </div>
                </div>
                
                <div class="form-group" id="rentPeriodGroup" style="display: none;">
                    <label>Rent Period</label>
                    <select name="rent_period">
                        <option value="">Select Period</option>
                        <option value="day" <?= ($_POST['rent_period'] ?? '') === 'day' ? 'selected' : '' ?>>Per Day</option>
                        <option value="week" <?= ($_POST['rent_period'] ?? '') === 'week' ? 'selected' : '' ?>>Per Week</option>
                        <option value="month" <?= ($_POST['rent_period'] ?? '') === 'month' ? 'selected' : '' ?>>Per Month</option>
                        <option value="year" <?= ($_POST['rent_period'] ?? '') === 'year' ? 'selected' : '' ?>>Per Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
                    <small>Detailed description of the property</small>
                </div>
            </div>
            
            <!-- Property Details -->
            <div class="card">
                <h3>Property Details</h3>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Bedrooms</label>
                        <input type="number" name="bedrooms" value="<?= e($_POST['bedrooms'] ?? '') ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Bathrooms</label>
                        <input type="number" name="bathrooms" value="<?= e($_POST['bathrooms'] ?? '') ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Parking Spaces</label>
                        <input type="number" name="parking_spaces" value="<?= e($_POST['parking_spaces'] ?? '') ?>" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Area</label>
                        <input type="number" name="area" value="<?= e($_POST['area'] ?? '') ?>" step="0.01" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Area Unit</label>
                        <select name="area_unit">
                            <option value="sqm" <?= ($_POST['area_unit'] ?? 'sqm') === 'sqm' ? 'selected' : '' ?>>Square Meters (sqm)</option>
                            <option value="sqft" <?= ($_POST['area_unit'] ?? '') === 'sqft' ? 'selected' : '' ?>>Square Feet (sqft)</option>
                            <option value="acres" <?= ($_POST['area_unit'] ?? '') === 'acres' ? 'selected' : '' ?>>Acres</option>
                            <option value="hectares" <?= ($_POST['area_unit'] ?? '') === 'hectares' ? 'selected' : '' ?>>Hectares</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Year Built</label>
                    <input type="number" name="year_built" value="<?= e($_POST['year_built'] ?? '') ?>" min="1900" max="<?= date('Y') ?>">
                </div>
                
                <div class="form-group">
                    <label>Features & Amenities</label>
                    <textarea name="features" rows="4"><?= e($_POST['features'] ?? '') ?></textarea>
                    <small>e.g., Swimming pool, Garden, Security, Gym, etc. (comma separated)</small>
                </div>
            </div>
            
            <!-- Location -->
            <div class="card">
                <h3>Location</h3>
                
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" value="<?= e($_POST['address'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>City <span class="required">*</span></label>
                        <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>State/Province</label>
                        <input type="text" name="state" value="<?= e($_POST['state'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Country</label>
                        <input type="text" name="country" value="<?= e($_POST['country'] ?? 'Rwanda') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Zip/Postal Code</label>
                        <input type="text" name="zip_code" value="<?= e($_POST['zip_code'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Latitude</label>
                        <input type="number" name="latitude" value="<?= e($_POST['latitude'] ?? '') ?>" step="0.000001">
                        <small>For map integration (optional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Longitude</label>
                        <input type="number" name="longitude" value="<?= e($_POST['longitude'] ?? '') ?>" step="0.000001">
                        <small>For map integration (optional)</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Images -->
            <div class="card">
                <h3>Property Images</h3>
                
                <div class="image-upload-area" id="uploadArea">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <p style="font-size: 0.75rem;">PNG, JPG, GIF up to 5MB</p>
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
                        <option value="active" <?= ($_POST['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="sold" <?= ($_POST['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                        <option value="rented" <?= ($_POST['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                    </select>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="featured" id="featured" value="1" <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                    <label for="featured">Mark as Featured</label>
                </div>
            </div>
        </div>
    </div>
    
    <div class="form-actions">
        <a href="properties.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Add Property
        </button>
    </div>
</form>

<script>
// Show/hide rent period based on listing type
document.getElementById('listingType').addEventListener('change', function() {
    const rentPeriodGroup = document.getElementById('rentPeriodGroup');
    if (this.value === 'rent') {
        rentPeriodGroup.style.display = 'block';
    } else {
        rentPeriodGroup.style.display = 'none';
    }
});

// Trigger on page load
if (document.getElementById('listingType').value === 'rent') {
    document.getElementById('rentPeriodGroup').style.display = 'block';
}

// Image upload handling
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
let selectedFiles = [];

uploadArea.addEventListener('click', () => imageInput.click());

imageInput.addEventListener('change', function() {
    handleFiles(this.files);
});

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

function handleFiles(files) {
    const newFiles = Array.from(files);
    selectedFiles = [...selectedFiles, ...newFiles];
    updateImagePreview();
    updateFileInput();
}

function updateImagePreview() {
    imagePreview.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button type="button" class="remove-image" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
                ${index === 0 ? '<span class="primary-badge">Primary</span>' : ''}
            `;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedFiles.splice(index, 1);
    updateImagePreview();
    updateFileInput();
}

function updateFileInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    imageInput.files = dt.files;
}
</script>

<?php require_once 'includes/footer.php'; ?>
