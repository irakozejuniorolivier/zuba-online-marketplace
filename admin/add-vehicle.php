<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = 'Add Vehicle';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate required fields
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $vehicle_type = $_POST['vehicle_type'] ?? '';
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $plate_number = trim($_POST['plate_number'] ?? '');
    
    if (empty($brand)) $errors[] = 'Brand is required';
    if (empty($model)) $errors[] = 'Model is required';
    if ($year < 1900 || $year > date('Y') + 1) $errors[] = 'Valid year is required';
    if ($category_id === 0) $errors[] = 'Category is required';
    if (empty($vehicle_type)) $errors[] = 'Vehicle type is required';
    if ($daily_rate <= 0) $errors[] = 'Daily rate must be greater than 0';
    if (empty($plate_number)) $errors[] = 'Plate number is required';
    
    // Optional fields
    $description = trim($_POST['description'] ?? '');
    $transmission = $_POST['transmission'] ?? 'manual';
    $fuel_type = $_POST['fuel_type'] ?? 'petrol';
    $seats = intval($_POST['seats'] ?? 5);
    $doors = intval($_POST['doors'] ?? 4);
    $color = trim($_POST['color'] ?? '');
    $mileage = intval($_POST['mileage'] ?? 0);
    $weekly_rate = floatval($_POST['weekly_rate'] ?? 0);
    $monthly_rate = floatval($_POST['monthly_rate'] ?? 0);
    $insurance_included = isset($_POST['insurance_included']) ? 1 : 0;
    $features = trim($_POST['features'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($errors)) {
        // Generate slug
        $slug = generateSlug($brand . ' ' . $model . ' ' . $year);
        
        // Check if slug exists
        $check = $conn->query("SELECT id FROM vehicles WHERE slug = '$slug'");
        if ($check->num_rows > 0) {
            $counter = 1;
            $original_slug = $slug;
            while ($check->num_rows > 0) {
                $slug = $original_slug . '-' . $counter;
                $check = $conn->query("SELECT id FROM vehicles WHERE slug = '$slug'");
                $counter++;
            }
        }
        
        // Insert vehicle
        $stmt = $conn->prepare("INSERT INTO vehicles (category_id, brand, model, year, slug, description, vehicle_type, transmission, fuel_type, seats, doors, color, plate_number, mileage, daily_rate, weekly_rate, monthly_rate, insurance_included, features, location, featured, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        // i=int, s=string, d=double
        // category_id(i), brand(s), model(s), year(i), slug(s), description(s), vehicle_type(s), transmission(s), fuel_type(s), seats(i), doors(i), color(s), plate_number(s), mileage(i), daily_rate(d), weekly_rate(d), monthly_rate(d), insurance_included(i), features(s), location(s), featured(i), status(s)
        $stmt->bind_param("issississiisidddisssis",
            $category_id, $brand, $model, $year, $slug, $description,
            $vehicle_type, $transmission, $fuel_type, $seats, $doors,
            $color, $plate_number, $mileage, $daily_rate, $weekly_rate,
            $monthly_rate, $insurance_included, $features, $location,
            $featured, $status
        );
        
        if ($stmt->execute()) {
            $vehicle_id = $conn->insert_id;
            
            // Handle image uploads
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = '../uploads/vehicles/';
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
                            $image_path = 'uploads/vehicles/' . $new_name;
                            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                                $conn->query("INSERT INTO vehicle_images (vehicle_id, image_path, is_primary, sort_order, created_at) VALUES ($vehicle_id, '$image_path', $is_primary, $key, NOW())");
                                $is_primary = 0;
                            }
                        }
                    }
                }
            }
            
            logActivity('admin', $admin['id'], 'add_vehicle', 'Added new vehicle: ' . $brand . ' ' . $model);
            setFlash('success', 'Vehicle added successfully');
            redirect('vehicles.php');
        } else {
            $errors[] = 'Failed to add vehicle: ' . $stmt->error;
        }
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE type = 'carrental' AND status = 'active' ORDER BY name");

require_once 'includes/header.php';
?>

<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.page-header h1 { margin: 0; font-size: 1.75rem; color: #1a1a2e; }
.page-header p { margin: 0.25rem 0 0 0; color: #666; }
.btn { padding: 0.625rem 1.25rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.875rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }
.btn-primary { background: #f97316; color: white; }
.btn-primary:hover { background: #ea580c; }
.alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
.alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.alert ul { margin: 0.5rem 0 0 1.5rem; padding: 0; }
.form-container { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; }
.card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem; }
.card h3 { margin: 0 0 1.5rem 0; font-size: 1.125rem; color: #1a1a2e; padding-bottom: 0.75rem; border-bottom: 2px solid #f97316; }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #374151; font-size: 0.875rem; }
.form-group label .required { color: #ef4444; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.625rem; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; font-family: inherit; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #f97316; }
.form-group textarea { resize: vertical; min-height: 100px; }
.form-group small { display: block; margin-top: 0.25rem; color: #6b7280; font-size: 0.75rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
.checkbox-group { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem; background: #f9fafb; border-radius: 6px; }
.checkbox-group input[type="checkbox"] { width: auto; margin: 0; }
.checkbox-group label { margin: 0; font-weight: normal; }
.image-upload-area { border: 2px dashed #e5e7eb; border-radius: 8px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; }
.image-upload-area:hover { border-color: #f97316; background: #fff7ed; }
.image-upload-area.dragover { border-color: #f97316; background: #fff7ed; }
.image-upload-area svg { margin: 0 auto 1rem; color: #f97316; }
.image-upload-area p { margin: 0.5rem 0; color: #6b7280; }
.image-upload-area input[type="file"] { display: none; }
.image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 1rem; margin-top: 1rem; }
.image-preview-item { position: relative; aspect-ratio: 1; border-radius: 6px; overflow: hidden; border: 2px solid #e5e7eb; }
.image-preview-item img { width: 100%; height: 100%; object-fit: cover; }
.image-preview-item .remove-image { position: absolute; top: 0.25rem; right: 0.25rem; width: 24px; height: 24px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
.image-preview-item .primary-badge { position: absolute; bottom: 0.25rem; left: 0.25rem; background: #f97316; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.625rem; font-weight: 600; }
.form-actions { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem; }
@media (max-width: 768px) {
    .form-container { grid-template-columns: 1fr; }
    .form-row, .form-row-3 { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <div>
        <h1>Add New Vehicle</h1>
        <p>Create a new vehicle listing for rental</p>
    </div>
    <a href="vehicles.php" class="btn btn-secondary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Vehicles
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
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Brand <span class="required">*</span></label>
                        <input type="text" name="brand" value="<?= e($_POST['brand'] ?? '') ?>" required>
                        <small>e.g., Toyota, Honda, Mercedes</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Model <span class="required">*</span></label>
                        <input type="text" name="model" value="<?= e($_POST['model'] ?? '') ?>" required>
                        <small>e.g., Camry, Civic, C-Class</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Year <span class="required">*</span></label>
                        <input type="number" name="year" value="<?= e($_POST['year'] ?? date('Y')) ?>" min="1900" max="<?= date('Y') + 1 ?>" required>
                    </div>
                    
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
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Vehicle Type <span class="required">*</span></label>
                        <select name="vehicle_type" required>
                            <option value="">Select Type</option>
                            <option value="sedan" <?= ($_POST['vehicle_type'] ?? '') === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                            <option value="suv" <?= ($_POST['vehicle_type'] ?? '') === 'suv' ? 'selected' : '' ?>>SUV</option>
                            <option value="truck" <?= ($_POST['vehicle_type'] ?? '') === 'truck' ? 'selected' : '' ?>>Truck</option>
                            <option value="van" <?= ($_POST['vehicle_type'] ?? '') === 'van' ? 'selected' : '' ?>>Van</option>
                            <option value="coupe" <?= ($_POST['vehicle_type'] ?? '') === 'coupe' ? 'selected' : '' ?>>Coupe</option>
                            <option value="convertible" <?= ($_POST['vehicle_type'] ?? '') === 'convertible' ? 'selected' : '' ?>>Convertible</option>
                            <option value="hatchback" <?= ($_POST['vehicle_type'] ?? '') === 'hatchback' ? 'selected' : '' ?>>Hatchback</option>
                            <option value="minivan" <?= ($_POST['vehicle_type'] ?? '') === 'minivan' ? 'selected' : '' ?>>Minivan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Plate Number <span class="required">*</span></label>
                        <input type="text" name="plate_number" value="<?= e($_POST['plate_number'] ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="6"><?= e($_POST['description'] ?? '') ?></textarea>
                    <small>Detailed description of the vehicle</small>
                </div>
            </div>
            
            <!-- Vehicle Specifications -->
            <div class="card">
                <h3>Vehicle Specifications</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Transmission</label>
                        <select name="transmission">
                            <option value="manual" <?= ($_POST['transmission'] ?? 'manual') === 'manual' ? 'selected' : '' ?>>Manual</option>
                            <option value="automatic" <?= ($_POST['transmission'] ?? '') === 'automatic' ? 'selected' : '' ?>>Automatic</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fuel Type</label>
                        <select name="fuel_type">
                            <option value="petrol" <?= ($_POST['fuel_type'] ?? 'petrol') === 'petrol' ? 'selected' : '' ?>>Petrol</option>
                            <option value="diesel" <?= ($_POST['fuel_type'] ?? '') === 'diesel' ? 'selected' : '' ?>>Diesel</option>
                            <option value="electric" <?= ($_POST['fuel_type'] ?? '') === 'electric' ? 'selected' : '' ?>>Electric</option>
                            <option value="hybrid" <?= ($_POST['fuel_type'] ?? '') === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label>Seats</label>
                        <input type="number" name="seats" value="<?= e($_POST['seats'] ?? '5') ?>" min="1" max="50">
                    </div>
                    
                    <div class="form-group">
                        <label>Doors</label>
                        <input type="number" name="doors" value="<?= e($_POST['doors'] ?? '4') ?>" min="2" max="6">
                    </div>
                    
                    <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" value="<?= e($_POST['color'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Mileage (km)</label>
                    <input type="number" name="mileage" value="<?= e($_POST['mileage'] ?? '0') ?>" min="0">
                    <small>Current mileage of the vehicle</small>
                </div>
                
                <div class="form-group">
                    <label>Features & Amenities</label>
                    <textarea name="features" rows="4"><?= e($_POST['features'] ?? '') ?></textarea>
                    <small>e.g., GPS, Air Conditioning, Bluetooth, Backup Camera (comma separated)</small>
                </div>
            </div>
            
            <!-- Rental Rates -->
            <div class="card">
                <h3>Rental Rates</h3>
                
                <div class="form-group">
                    <label>Daily Rate <span class="required">*</span></label>
                    <input type="number" name="daily_rate" value="<?= e($_POST['daily_rate'] ?? '') ?>" step="0.01" min="0" required>
                    <small>Price per day in <?= CURRENCY ?></small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Weekly Rate</label>
                        <input type="number" name="weekly_rate" value="<?= e($_POST['weekly_rate'] ?? '') ?>" step="0.01" min="0">
                        <small>Optional weekly rate</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Monthly Rate</label>
                        <input type="number" name="monthly_rate" value="<?= e($_POST['monthly_rate'] ?? '') ?>" step="0.01" min="0">
                        <small>Optional monthly rate</small>
                    </div>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="insurance_included" id="insurance" value="1" <?= isset($_POST['insurance_included']) ? 'checked' : '' ?>>
                    <label for="insurance">Insurance Included</label>
                </div>
            </div>
            
            <!-- Location -->
            <div class="card">
                <h3>Location</h3>
                
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?= e($_POST['location'] ?? '') ?>">
                    <small>Where the vehicle is available for pickup</small>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div>
            <!-- Images -->
            <div class="card">
                <h3>Vehicle Images</h3>
                
                <div class="image-upload-area" id="uploadArea">
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
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
                    <label>Vehicle Status</label>
                    <select name="status">
                        <option value="available" <?= ($_POST['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="rented" <?= ($_POST['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                        <option value="maintenance" <?= ($_POST['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="inactive" <?= ($_POST['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
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
        <a href="vehicles.php" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Add Vehicle
        </button>
    </div>
</form>

<script>
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
                <button type="button" class="remove-image" onclick="removeImage(${index})">×</button>
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
