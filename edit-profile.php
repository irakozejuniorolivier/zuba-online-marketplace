<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireCustomerLogin();

$customer_id = currentCustomerId();
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile_image'])) {
        // Update profile image only
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0 && !empty($_FILES['profile_image']['name'])) {
            $file = $_FILES['profile_image'];
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error_message = 'Only JPG, JPEG, and PNG images are allowed.';
            } elseif ($file['size'] > 10485760) {
                $error_message = 'Image size must be less than 10MB.';;
            } else {
                $filename = uniqid() . '_' . time() . '.' . $ext;
                $destination = 'uploads/profiles/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    if (!empty($customer['profile_image']) && file_exists($customer['profile_image'])) {
                        @unlink($customer['profile_image']);
                    }
                    
                    $update_stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $update_stmt->bind_param('si', $destination, $customer_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = 'Profile image updated successfully!';
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->bind_param("i", $customer_id);
                        $stmt->execute();
                        $customer = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    } else {
                        $error_message = 'Failed to update profile image.';
                    }
                    $update_stmt->close();
                } else {
                    $error_message = 'Failed to upload image.';
                }
            }
        } else {
            $error_message = 'Please select an image to upload.';
        }
    } elseif (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $city = trim($_POST['city']);
        $address = trim($_POST['address']);
        $country = trim($_POST['country']);
        if (empty($name) || empty($email) || empty($phone)) {
            $error_message = 'Name, email, and phone are required.';
        } else {
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_stmt->bind_param('si', $email, $customer_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = 'Email already exists.';
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, city = ?, address = ?, country = ? WHERE id = ?");
                $update_stmt->bind_param('ssssssi', $name, $email, $phone, $city, $address, $country, $customer_id);
                    
                if ($update_stmt->execute()) {
                    $success_message = 'Profile updated successfully!';
                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->bind_param("i", $customer_id);
                    $stmt->execute();
                    $customer = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                } else {
                    $error_message = 'Failed to update profile.';
                }
                $update_stmt->close();
            }
            $check_stmt->close();
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'All password fields are required.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error_message = 'Password must be at least 6 characters.';
        } else {
            // Verify current password
            if (password_verify($current_password, $customer['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param('si', $hashed_password, $customer_id);
                
                if ($update_stmt->execute()) {
                    $success_message = 'Password changed successfully!';
                } else {
                    $error_message = 'Failed to change password.';
                }
                $update_stmt->close();
            } else {
                $error_message = 'Current password is incorrect.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Zuba Online Market</title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
        
        /* Header */
        .header { background: white; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
        .header-content { max-width: 900px; margin: 0 auto; padding: 16px 20px; display: flex; align-items: center; gap: 16px; }
        .back-btn { width: 40px; height: 40px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #1a1a2e; text-decoration: none; transition: all 0.3s; flex-shrink: 0; }
        .back-btn:hover { background: #f97316; color: #fff; border-color: #f97316; }
        .header-title { flex: 1; }
        .header-title h1 { font-size: 22px; font-weight: 900; color: #1a1a2e; }
        .header-title p { font-size: 13px; color: #6b7280; margin-top: 2px; }
        
        /* Container */
        .container { max-width: 900px; margin: 0 auto; padding: 24px 20px 60px; }
        
        /* Profile Header Card */
        .profile-header-card { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 16px; padding: 30px; margin-bottom: 24px; color: white; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .profile-header-content { display: flex; align-items: center; gap: 24px; }
        .profile-image-wrapper { position: relative; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .change-photo-btn { position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; background: white; color: #f97316; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid #f97316; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s; font-size: 14px; }
        .change-photo-btn:hover { transform: scale(1.1); background: #f97316; color: white; }
        .profile-info h2 { font-size: 24px; font-weight: 900; margin-bottom: 6px; }
        .profile-info p { font-size: 14px; opacity: 0.95; display: flex; align-items: center; gap: 6px; }
        .btn-upload-image { margin-top: 12px; padding: 10px 24px; background: white; color: #f97316; border: 2px solid white; border-radius: 8px; font-weight: 700; cursor: pointer; display: none; align-items: center; gap: 8px; transition: all 0.3s; font-size: 14px; border: none; }
        .btn-upload-image:hover { background: rgba(255,255,255,0.9); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        
        /* Popup Overlay */
        .popup-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.3s ease; }
        .popup-overlay.show { display: flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* Popup Box */
        .popup-box { background: white; border-radius: 16px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: slideUp 0.3s ease; text-align: center; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        .popup-icon { width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 32px; }
        .popup-icon.success { background: #d1fae5; color: #10b981; }
        .popup-icon.error { background: #fee2e2; color: #ef4444; }
        
        .popup-title { font-size: 22px; font-weight: 900; margin-bottom: 12px; color: #1a1a2e; }
        .popup-message { font-size: 15px; color: #6b7280; margin-bottom: 24px; line-height: 1.6; }
        
        .popup-btn { padding: 12px 32px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 15px; transition: all 0.3s; }
        .popup-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        
        /* Crop Modal */
        .crop-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: none; align-items: center; justify-content: center; z-index: 10000; animation: fadeIn 0.3s ease; }
        .crop-modal.show { display: flex; }
        .crop-container { background: white; border-radius: 16px; padding: 24px; max-width: 600px; width: 90%; max-height: 90vh; overflow: auto; }
        .crop-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .crop-header h3 { font-size: 20px; font-weight: 900; color: #1a1a2e; }
        .crop-close { width: 36px; height: 36px; background: #f3f4f6; border: none; border-radius: 8px; cursor: pointer; color: #6b7280; font-size: 18px; transition: all 0.3s; }
        .crop-close:hover { background: #e5e7eb; color: #1a1a2e; }
        .crop-image-container { max-height: 400px; margin-bottom: 20px; background: #f9fafb; border-radius: 12px; overflow: hidden; }
        .crop-controls { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; justify-content: center; }
        .crop-btn { padding: 10px 16px; background: #f3f4f6; border: none; border-radius: 8px; cursor: pointer; color: #1a1a2e; font-weight: 600; font-size: 14px; transition: all 0.3s; display: flex; align-items: center; gap: 6px; }
        .crop-btn:hover { background: #e5e7eb; }
        .crop-btn i { font-size: 16px; }
        .crop-actions { display: flex; gap: 12px; }
        .crop-actions button { flex: 1; padding: 12px 24px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 15px; transition: all 0.3s; }
        .btn-cancel { background: #f3f4f6; color: #6b7280; }
        .btn-cancel:hover { background: #e5e7eb; color: #1a1a2e; }
        .btn-crop { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; }
        .btn-crop:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        
        /* Alert */
        .alert { display: none; }
        .alert-success { display: none; }
        .alert-error { display: none; }
        
        /* Card */
        .card { background: white; border-radius: 12px; padding: 28px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
        .card-title { font-size: 18px; font-weight: 800; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; color: #1a1a2e; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6; }
        .card-title i { color: #f97316; font-size: 20px; }
        
        /* Form */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #1a1a2e; font-size: 14px; }
        .form-group label .required { color: #ef4444; margin-left: 2px; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; transition: all 0.3s; font-family: inherit; color: #1a1a2e; background: #fff; }
        .form-control:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .form-control:disabled { background: #f9fafb; cursor: not-allowed; }
        
        .btn-primary { padding: 14px 32px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s; font-size: 15px; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-primary:active { transform: translateY(0); }
        
        #profileImageInput { display: none; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content { padding: 12px 15px; }
            .header-title h1 { font-size: 18px; }
            .header-title p { font-size: 12px; }
            .back-btn { width: 36px; height: 36px; }
            .container { padding: 16px 12px 40px; }
            .profile-header-card { padding: 20px; }
            .profile-header-content { flex-direction: column; text-align: center; }
            .profile-avatar { width: 80px; height: 80px; }
            .change-photo-btn { width: 32px; height: 32px; }
            .profile-info h2 { font-size: 20px; }
            .profile-info p { font-size: 13px; justify-content: center; }
            .btn-upload-image { width: 100%; justify-content: center; padding: 10px 20px; font-size: 13px; }
            .crop-container { padding: 16px; }
            .crop-header h3 { font-size: 18px; }
            .crop-image-container { max-height: 300px; }
            .crop-controls { gap: 8px; }
            .crop-btn { padding: 8px 12px; font-size: 13px; }
            .crop-actions { flex-direction: column; }
            .crop-actions button { padding: 10px 20px; font-size: 14px; }
            .card { padding: 20px 16px; }
            .card-title { font-size: 16px; margin-bottom: 20px; padding-bottom: 12px; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .btn-primary { width: 100%; justify-content: center; padding: 12px 24px; font-size: 14px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/profile.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">
            <h1>Edit Profile</h1>
            <p>Update your account information and settings</p>
        </div>
    </div>
</header>

<!-- Main Container -->
<div class="container">
    <!-- Crop Modal -->
    <div class="crop-modal" id="cropModal">
        <div class="crop-container">
            <div class="crop-header">
                <h3><i class="fas fa-crop"></i> Crop Profile Image</h3>
                <button class="crop-close" onclick="closeCropModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="crop-image-container">
                <img id="cropImage" style="max-width: 100%;">
            </div>
            <div class="crop-controls">
                <button class="crop-btn" onclick="cropper.zoom(0.1)">
                    <i class="fas fa-search-plus"></i> Zoom In
                </button>
                <button class="crop-btn" onclick="cropper.zoom(-0.1)">
                    <i class="fas fa-search-minus"></i> Zoom Out
                </button>
                <button class="crop-btn" onclick="cropper.rotate(90)">
                    <i class="fas fa-redo"></i> Rotate
                </button>
                <button class="crop-btn" onclick="cropper.reset()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
            <div class="crop-actions">
                <button class="btn-cancel" onclick="closeCropModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-crop" onclick="applyCrop()">
                    <i class="fas fa-check"></i> Apply Crop
                </button>
            </div>
        </div>
    </div>
    
    <!-- Popup Overlay -->
    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-box">
            <div class="popup-icon" id="popupIcon">
                <i class="fas" id="popupIconElement"></i>
            </div>
            <h3 class="popup-title" id="popupTitle"></h3>
            <p class="popup-message" id="popupMessage"></p>
            <button class="popup-btn" onclick="closePopup()">OK</button>
        </div>
    </div>
    
    <!-- Profile Header Card -->
    <div class="profile-header-card">
        <form method="POST" enctype="multipart/form-data" id="imageForm">
            <div class="profile-header-content">
                <div class="profile-image-wrapper">
                    <img src="<?= !empty($customer['profile_image']) ? SITE_URL . '/' . htmlspecialchars($customer['profile_image']) . '?v=' . time() : 'https://ui-avatars.com/api/?name=' . urlencode($customer['name']) . '&size=100&background=f97316&color=fff&bold=true' ?>" 
                         alt="Profile" class="profile-avatar" id="profilePreview">
                    <label for="profileImageInput" class="change-photo-btn" title="Change Photo">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="profileImageInput" name="profile_image" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(event)">
                </div>
                <div class="profile-info">
                    <h2><?= htmlspecialchars($customer['name']) ?></h2>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($customer['email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($customer['phone']) ?></p>
                    <button type="submit" name="update_profile_image" class="btn-upload-image" id="uploadBtn">
                        <i class="fas fa-upload"></i>
                        Upload Photo
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- Success/Error Messages (Hidden) -->
    <div style="display:none;">
        <?php if ($success_message): ?>
            <div id="successMessage" data-message="<?= htmlspecialchars($success_message) ?>"></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div id="errorMessage" data-message="<?= htmlspecialchars($error_message) ?>"></div>
        <?php endif; ?>
    </div>
    
    <!-- Account Information -->
    <div class="card">
        <h2 class="card-title">
            <i class="fas fa-user"></i>
            Account Information
        </h2>
        <form method="POST" id="profileForm">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Phone <span class="required">*</span></label>
                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($customer['city'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($customer['address'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Country</label>
                <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($customer['country'] ?? '') ?>">
            </div>
            <button type="submit" name="update_profile" class="btn-primary">
                <i class="fas fa-save"></i>
                Save Changes
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="card">
        <h2 class="card-title">
            <i class="fas fa-lock"></i>
            Change Password
        </h2>
        <form method="POST" id="passwordForm">
            <div class="form-group">
                <label>Current Password <span class="required">*</span></label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>New Password <span class="required">*</span></label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="change_password" class="btn-primary">
                <i class="fas fa-key"></i>
                Update Password
            </button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper = null;
let croppedImageFile = null;

function previewImage(event) {
    const file = event.target.files[0];
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            showPopup('error', 'Invalid File Type', 'Only JPG, JPEG, and PNG images are allowed.');
            event.target.value = '';
            uploadBtn.style.display = 'none';
            return;
        }
        
        if (file.size > 10485760) {
            showPopup('error', 'File Too Large', 'Image size must be less than 10MB.');
            event.target.value = '';
            uploadBtn.style.display = 'none';
            return;
        }
        
        // Show crop modal
        const reader = new FileReader();
        reader.onload = function(e) {
            const cropImage = document.getElementById('cropImage');
            cropImage.src = e.target.result;
            document.getElementById('cropModal').classList.add('show');
            
            // Initialize cropper
            if (cropper) {
                cropper.destroy();
            }
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 2,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }
        reader.readAsDataURL(file);
    } else {
        uploadBtn.style.display = 'none';
    }
}

function closeCropModal() {
    document.getElementById('cropModal').classList.remove('show');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    document.getElementById('profileImageInput').value = '';
    document.getElementById('uploadBtn').style.display = 'none';
}

function applyCrop() {
    if (!cropper) return;
    
    cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    }).toBlob(function(blob) {
        // Create file from blob
        const fileName = 'profile_' + Date.now() + '.jpg';
        croppedImageFile = new File([blob], fileName, { type: 'image/jpeg' });
        
        // Update preview
        const url = URL.createObjectURL(blob);
        document.getElementById('profilePreview').src = url;
        
        // Update file input with cropped image
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(croppedImageFile);
        document.getElementById('profileImageInput').files = dataTransfer.files;
        
        // Close modal first
        document.getElementById('cropModal').classList.remove('show');
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        
        // Show upload button after a small delay
        setTimeout(function() {
            document.getElementById('uploadBtn').style.display = 'inline-flex';
        }, 100);
    }, 'image/jpeg', 0.9);
}
function showPopup(type, title, message) {
    const overlay = document.getElementById('popupOverlay');
    const icon = document.getElementById('popupIcon');
    const iconElement = document.getElementById('popupIconElement');
    const titleElement = document.getElementById('popupTitle');
    const messageElement = document.getElementById('popupMessage');
    
    if (type === 'success') {
        icon.className = 'popup-icon success';
        iconElement.className = 'fas fa-check-circle';
    } else {
        icon.className = 'popup-icon error';
        iconElement.className = 'fas fa-exclamation-circle';
    }
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    overlay.classList.add('show');
}

function closePopup() {
    document.getElementById('popupOverlay').classList.remove('show');
}

// Show popup on page load if there's a message
window.addEventListener('DOMContentLoaded', function() {
    const successMsg = document.getElementById('successMessage');
    const errorMsg = document.getElementById('errorMessage');
    
    if (successMsg) {
        showPopup('success', 'Success!', successMsg.getAttribute('data-message'));
    } else if (errorMsg) {
        showPopup('error', 'Error!', errorMsg.getAttribute('data-message'));
    }
});

// Close popup when clicking outside
document.getElementById('popupOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        closePopup();
    }
});
</script>

</body>
</html>
