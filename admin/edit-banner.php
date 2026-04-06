<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();
$page_title = 'Edit Banner';

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('banners.php');

$stmt = $conn->prepare("SELECT * FROM banners WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result->fetch_assoc();

if (!$banner) redirect('banners.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $button_text = trim($_POST['button_text'] ?? '');
    $button_link = trim($_POST['button_link'] ?? '');
    $position = trim($_POST['position'] ?? $banner['position']);
    $page = trim($_POST['page'] ?? $banner['page']);
    $background_color = trim($_POST['background_color'] ?? $banner['background_color']);
    $text_color = trim($_POST['text_color'] ?? $banner['text_color']);
    $overlay_opacity = (float)($_POST['overlay_opacity'] ?? $banner['overlay_opacity']);
    $sort_order = (int)($_POST['sort_order'] ?? $banner['sort_order']);
    $status = trim($_POST['status'] ?? $banner['status']);
    if (empty($status)) $status = 'active';
    $start_date = !empty(trim($_POST['start_date'] ?? '')) ? trim($_POST['start_date']) : ($banner['start_date'] ?? NULL);
    $end_date = !empty(trim($_POST['end_date'] ?? '')) ? trim($_POST['end_date']) : ($banner['end_date'] ?? NULL);

    $errors = [];

    if (empty($title)) $errors[] = 'Title is required';

    if (empty($errors)) {
        $image_filename = $banner['image'];
        
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadFile($_FILES['image'], BANNER_UPLOAD_DIR);
            if (!$upload['success']) {
                $errors[] = $upload['error'];
            } else {
                if (!empty($banner['image']) && file_exists(BANNER_UPLOAD_DIR . $banner['image'])) {
                    unlink(BANNER_UPLOAD_DIR . $banner['image']);
                }
                $image_filename = $upload['filename'];
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE banners SET title = ?, subtitle = ?, description = ?, image = ?, button_text = ?, button_link = ?, position = ?, page = ?, background_color = ?, text_color = ?, overlay_opacity = ?, sort_order = ?, status = ?, start_date = ?, end_date = ? WHERE id = ?");
            
            $stmt->bind_param("ssssssssssdsissi", 
                $title, $subtitle, $description, $image_filename, $button_text, 
                $button_link, $position, $page, $background_color, $text_color, 
                $overlay_opacity, $sort_order, $status, $start_date, $end_date, $id
            );

            if ($stmt->execute()) {
                logActivity('admin', $admin['id'], 'update_banner', "Updated banner ID: $id");
                setFlash('success', 'Banner updated successfully');
                redirect('banners.php');
            } else {
                $errors[] = 'Database error: ' . $conn->error;
            }
        }
    }
}

require_once 'includes/header.php';
?>

<style>
    .form-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .form-card {
        background: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .form-section h3 {
        margin: 0 0 20px 0;
        color: #1a1a2e;
        font-size: 16px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 2px solid #f97316;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        font-size: 14px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #f97316;
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .image-upload {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .image-upload:hover {
        border-color: #f97316;
        background: #fff7ed;
    }

    .image-upload input {
        display: none;
    }

    .image-preview {
        margin-top: 15px;
    }

    .image-preview img {
        max-width: 100%;
        max-height: 300px;
        border-radius: 4px;
    }

    .current-image {
        margin-bottom: 15px;
        padding: 15px;
        background: #f9fafb;
        border-radius: 4px;
    }

    .current-image p {
        margin: 0 0 10px 0;
        font-size: 13px;
        color: #666;
    }

    .current-image img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
    }

    .color-input-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .color-input-group input[type="color"] {
        width: 50px;
        height: 40px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        padding: 10px 24px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #f97316;
        color: white;
    }

    .btn-primary:hover {
        background: #ea580c;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #333;
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .error-message {
        background: #fee2e2;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 20px;
        border-left: 4px solid #ef4444;
    }

    .error-list {
        margin: 0;
        padding-left: 20px;
    }

    .error-list li {
        margin: 5px 0;
    }

    @media (max-width: 768px) {
        .form-container {
            padding: 15px;
        }

        .form-card {
            padding: 20px;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>

<div class="form-container">
    <h1 style="margin: 0 0 30px 0; color: #1a1a2e;">Edit Banner</h1>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
            <!-- Basic Information -->
            <div class="form-section">
                <h3>Basic Information</h3>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Title *</label>
                        <input type="text" name="title" value="<?= e($_POST['title'] ?? $banner['title']) ?>" required>
                    </div>
                    <div class="form-group full">
                        <label>Subtitle</label>
                        <input type="text" name="subtitle" value="<?= e($_POST['subtitle'] ?? $banner['subtitle']) ?>">
                    </div>
                    <div class="form-group full">
                        <label>Description</label>
                        <textarea name="description"><?= e($_POST['description'] ?? $banner['description']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="form-section">
                <h3>Banner Image</h3>
                <?php if (!empty($banner['image']) && file_exists(BANNER_UPLOAD_DIR . $banner['image'])): ?>
                    <div class="current-image">
                        <p>Current Image:</p>
                        <img src="<?= UPLOAD_URL ?>banners/<?= e($banner['image']) ?>" alt="<?= e($banner['title']) ?>">
                    </div>
                <?php endif; ?>
                <div class="form-group full">
                    <label>Replace Image (Optional)</label>
                    <div class="image-upload" onclick="document.getElementById('imageInput').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 40px; height: 40px; margin: 0 auto 10px; color: #f97316;">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                        <p style="margin: 0; color: #666;">Click to upload or drag and drop</p>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: #999;">PNG, JPG, GIF up to 5MB</p>
                    </div>
                    <input type="file" id="imageInput" name="image" accept="image/*" onchange="previewImage(this)">
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Button -->
            <div class="form-section">
                <h3>Call-to-Action Button</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Button Text</label>
                        <input type="text" name="button_text" value="<?= e($_POST['button_text'] ?? $banner['button_text']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Button Link</label>
                        <input type="text" name="button_link" value="<?= e($_POST['button_link'] ?? $banner['button_link']) ?>">
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="form-section">
                <h3>Display Settings</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Position *</label>
                        <select name="position" required>
                            <option value="hero" <?= ($_POST['position'] ?? $banner['position']) === 'hero' ? 'selected' : '' ?>>Hero</option>
                            <option value="top" <?= ($_POST['position'] ?? $banner['position']) === 'top' ? 'selected' : '' ?>>Top</option>
                            <option value="middle" <?= ($_POST['position'] ?? $banner['position']) === 'middle' ? 'selected' : '' ?>>Middle</option>
                            <option value="bottom" <?= ($_POST['position'] ?? $banner['position']) === 'bottom' ? 'selected' : '' ?>>Bottom</option>
                            <option value="sidebar" <?= ($_POST['position'] ?? $banner['position']) === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Page *</label>
                        <select name="page" required>
                            <option value="home" <?= ($_POST['page'] ?? $banner['page']) === 'home' ? 'selected' : '' ?>>Home</option>
                            <option value="products" <?= ($_POST['page'] ?? $banner['page']) === 'products' ? 'selected' : '' ?>>Products</option>
                            <option value="properties" <?= ($_POST['page'] ?? $banner['page']) === 'properties' ? 'selected' : '' ?>>Properties</option>
                            <option value="vehicles" <?= ($_POST['page'] ?? $banner['page']) === 'vehicles' ? 'selected' : '' ?>>Vehicles</option>
                            <option value="all" <?= ($_POST['page'] ?? $banner['page']) === 'all' ? 'selected' : '' ?>>All Pages</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?= e($_POST['sort_order'] ?? $banner['sort_order']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="active" <?= (isset($_POST['status']) ? $_POST['status'] : $banner['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= (isset($_POST['status']) ? $_POST['status'] : $banner['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Styling -->
            <div class="form-section">
                <h3>Styling</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Background Color</label>
                        <div class="color-input-group">
                            <input type="color" name="background_color" value="<?php echo $_POST['background_color'] ?? $banner['background_color']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Text Color</label>
                        <div class="color-input-group">
                            <input type="color" name="text_color" value="<?php echo $_POST['text_color'] ?? $banner['text_color']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Overlay Opacity (0-1)</label>
                        <input type="number" name="overlay_opacity" value="<?= e($_POST['overlay_opacity'] ?? $banner['overlay_opacity']) ?>" min="0" max="1" step="0.1">
                    </div>
                </div>
            </div>

            <!-- Scheduling -->
            <div class="form-section">
                <h3>Scheduling (Optional)</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="datetime-local" name="start_date" value="<?= e($_POST['start_date'] ?? ($banner['start_date'] ? str_replace(' ', 'T', $banner['start_date']) : '')) ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="datetime-local" name="end_date" value="<?= e($_POST['end_date'] ?? ($banner['end_date'] ? str_replace(' ', 'T', $banner['end_date']) : '')) ?>">
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <a href="banners.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Banner</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
