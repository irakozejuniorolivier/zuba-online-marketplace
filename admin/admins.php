<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Admin Management";

// Handle add admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = $_POST['status'];
    $created_by = $admin['id'];
    
    $profile_image = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $upload = uploadFile($_FILES['profile_image'], '../uploads/profiles/');
        if ($upload['success']) {
            $profile_image = $upload['filename'];
        }
    }
    
    // Check email uniqueness
    $check = $conn->query("SELECT id FROM admins WHERE email = '" . $conn->real_escape_string($email) . "'");
    if ($check->num_rows > 0) {
        setFlash('error', 'Email already exists');
        redirect('admins.php');
    }
    
    $stmt = $conn->prepare("INSERT INTO admins (name, email, phone, password, profile_image, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $password, $profile_image, $status, $created_by);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'add_admin', "Added admin: $name");
        setFlash('success', 'Admin added successfully');
    } else {
        setFlash('error', 'Failed to add admin');
    }
    
    redirect('admins.php');
}

// Handle edit admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin'])) {
    $admin_id = (int)$_POST['admin_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $status = $_POST['status'];
    
    // Check email uniqueness
    $check = $conn->query("SELECT id FROM admins WHERE email = '" . $conn->real_escape_string($email) . "' AND id != $admin_id");
    if ($check->num_rows > 0) {
        setFlash('error', 'Email already exists');
        redirect('admins.php');
    }
    
    $current = $conn->query("SELECT profile_image FROM admins WHERE id = $admin_id")->fetch_assoc();
    $profile_image = $current['profile_image'];
    
    if (!empty($_FILES['profile_image']['name'])) {
        $upload = uploadFile($_FILES['profile_image'], '../uploads/profiles/');
        if ($upload['success']) {
            $profile_image = $upload['filename'];
        }
    }
    
    // Update password if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, password = ?, profile_image = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $email, $phone, $password, $profile_image, $status, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET name = ?, email = ?, phone = ?, profile_image = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $profile_image, $status, $admin_id);
    }
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'edit_admin', "Updated admin: $name");
        setFlash('success', 'Admin updated successfully');
    } else {
        setFlash('error', 'Failed to update admin');
    }
    
    redirect('admins.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $admin_id = (int)$_GET['delete'];
    
    // Prevent self-deletion
    if ($admin_id == $admin['id']) {
        setFlash('error', 'Cannot delete your own account');
        redirect('admins.php');
    }
    
    // Check if admin has approved orders/bookings
    $check = $conn->query("SELECT 
        (SELECT COUNT(*) FROM orders WHERE approved_by = $admin_id) as order_count,
        (SELECT COUNT(*) FROM property_orders WHERE approved_by = $admin_id) as property_count,
        (SELECT COUNT(*) FROM bookings WHERE approved_by = $admin_id) as booking_count
    ");
    $counts = $check->fetch_assoc();
    
    if ($counts['order_count'] > 0 || $counts['property_count'] > 0 || $counts['booking_count'] > 0) {
        setFlash('error', 'Cannot delete admin with approved transactions');
    } else {
        $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        
        if ($stmt->execute()) {
            logActivity('admin', $admin['id'], 'delete_admin', "Deleted admin #$admin_id");
            setFlash('success', 'Admin deleted successfully');
        } else {
            setFlash('error', 'Failed to delete admin');
        }
    }
    
    redirect('admins.php');
}

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
FROM admins")->fetch_assoc();

// Get admins
$admins = $conn->query("SELECT a.*, 
    (SELECT name FROM admins WHERE id = a.created_by) as creator_name
FROM admins a
ORDER BY a.created_at DESC");

require_once 'includes/header.php';
?>

<style>
.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.btn-add {
    background: #f97316;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
}

.btn-add:hover {
    background: #ea580c;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
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

.admins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    transition: all 0.3s;
}

.admin-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.admin-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.admin-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
}

.admin-avatar-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: bold;
}

.admin-info h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #1a1a2e;
}

.admin-email {
    font-size: 13px;
    color: #666;
}

.admin-details {
    margin-bottom: 15px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
}

.detail-row strong {
    color: #666;
}

.admin-status {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-bottom: 15px;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.admin-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.btn {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
    text-align: center;
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

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

.current-admin-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    margin-left: 8px;
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
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.close-btn:hover {
    color: #1a1a2e;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
}

.form-note {
    font-size: 12px;
    color: #666;
    margin-top: 3px;
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 100%;
    max-height: 100px;
    border-radius: 5px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.delete-modal-content {
    text-align: center;
}

.delete-modal-content .modal-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.delete-modal-content h3 {
    font-size: 18px;
    margin: 0 0 10px 0;
}

.delete-modal-content p {
    color: #666;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .content-header {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .btn-add {
        width: 100%;
        text-align: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .admins-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .modal-content {
        max-width: 95%;
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions .btn {
        width: 100%;
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
    <button onclick="openAddModal()" class="btn-add">+ Add Admin</button>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Admins</h3>
        <div class="number"><?php echo $stats['total']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Active</h3>
        <div class="number" style="color: #10b981;"><?php echo $stats['active']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Inactive</h3>
        <div class="number" style="color: #ef4444;"><?php echo $stats['inactive']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Added Today</h3>
        <div class="number" style="color: #f97316;"><?php echo $stats['today']; ?></div>
    </div>
</div>

<div class="admins-grid">
    <?php if ($admins->num_rows > 0): ?>
        <?php while ($adm = $admins->fetch_assoc()): ?>
            <div class="admin-card">
                <div class="admin-header">
                    <?php if ($adm['profile_image']): ?>
                        <img src="<?= UPLOAD_URL . 'profiles/' . e($adm['profile_image']) ?>" alt="" class="admin-avatar">
                    <?php else: ?>
                        <div class="admin-avatar-placeholder">
                            <?php echo strtoupper(substr($adm['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div class="admin-info">
                        <h3>
                            <?php echo e($adm['name']); ?>
                            <?php if ($adm['id'] == $admin['id']): ?>
                                <span class="current-admin-badge">You</span>
                            <?php endif; ?>
                        </h3>
                        <div class="admin-email"><?php echo e($adm['email']); ?></div>
                    </div>
                </div>
                <div class="admin-details">
                    <div class="detail-row">
                        <strong>Phone:</strong>
                        <span><?php echo e($adm['phone']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Created By:</strong>
                        <span><?php echo $adm['creator_name'] ? e($adm['creator_name']) : 'System'; ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Last Login:</strong>
                        <span><?php echo $adm['last_login'] ? timeAgo($adm['last_login']) : 'Never'; ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Joined:</strong>
                        <span><?php echo formatDate($adm['created_at']); ?></span>
                    </div>
                </div>
                <span class="admin-status status-<?php echo $adm['status']; ?>">
                    <?php echo ucfirst($adm['status']); ?>
                </span>
                <div class="admin-actions">
                    <button onclick="openEditModal(<?php echo $adm['id']; ?>)" class="btn btn-edit">Edit</button>
                    <?php if ($adm['id'] != $admin['id']): ?>
                        <button onclick="confirmDelete(<?php echo $adm['id']; ?>, '<?php echo e($adm['name']); ?>')" class="btn btn-delete">Delete</button>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
            No admins found
        </div>
    <?php endif; ?>
</div>

<div id="adminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Admin</h3>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <form id="adminForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="adminId" name="admin_id">
            <input type="hidden" id="formAction" name="add_admin" value="1">
            
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Phone *</label>
                <input type="text" name="phone" required>
            </div>
            
            <div class="form-group">
                <label id="passwordLabel">Password *</label>
                <input type="password" id="passwordInput" name="password">
                <div class="form-note" id="passwordNote"></div>
            </div>
            
            <div class="form-group">
                <label>Profile Image</label>
                <input type="file" name="profile_image" accept="image/*" onchange="previewImage(event)">
                <div id="imagePreview" class="image-preview"></div>
            </div>
            
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Admin</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content delete-modal-content">
        <div class="modal-icon">🗑️</div>
        <h3>Delete Admin</h3>
        <p id="deleteMessage"></p>
        <div class="form-actions">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeDelete()" class="btn btn-delete">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteAdminId = null;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Admin';
    document.getElementById('adminForm').reset();
    document.getElementById('adminId').value = '';
    document.getElementById('formAction').name = 'add_admin';
    document.getElementById('passwordLabel').textContent = 'Password *';
    document.getElementById('passwordInput').required = true;
    document.getElementById('passwordNote').textContent = '';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('adminModal').classList.add('active');
}

function openEditModal(id) {
    fetch('get-admin.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Admin';
            document.getElementById('adminId').value = data.id;
            document.querySelector('input[name="name"]').value = data.name;
            document.querySelector('input[name="email"]').value = data.email;
            document.querySelector('input[name="phone"]').value = data.phone;
            document.querySelector('select[name="status"]').value = data.status;
            document.getElementById('formAction').name = 'edit_admin';
            document.getElementById('passwordLabel').textContent = 'Password';
            document.getElementById('passwordInput').required = false;
            document.getElementById('passwordInput').value = '';
            document.getElementById('passwordNote').textContent = 'Leave blank to keep current password';
            
            if (data.profile_image) {
                document.getElementById('imagePreview').innerHTML = '<img src="<?= UPLOAD_URL ?>profiles/' + data.profile_image + '" alt="">';
            } else {
                document.getElementById('imagePreview').innerHTML = '';
            }
            
            document.getElementById('adminModal').classList.add('active');
        });
}

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').innerHTML = '<img src="' + e.target.result + '" alt="">';
        };
        reader.readAsDataURL(file);
    }
}

function closeModal() {
    document.getElementById('adminModal').classList.remove('active');
}

function confirmDelete(id, name) {
    deleteAdminId = id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"?`;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteAdminId = null;
}

function executeDelete() {
    if (deleteAdminId) {
        window.location.href = 'admins.php?delete=' + deleteAdminId;
    }
}

document.getElementById('adminModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
