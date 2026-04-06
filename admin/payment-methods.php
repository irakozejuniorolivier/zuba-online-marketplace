<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireAdminLogin();
$admin = currentAdmin();

$page_title = "Payment Methods";

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_method'])) {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $account_name = trim($_POST['account_name']);
    $account_number = trim($_POST['account_number']);
    $instructions = trim($_POST['instructions']);
    $status = $_POST['status'];
    
    $logo = null;
    if (!empty($_FILES['logo']['name'])) {
        $upload = uploadFile($_FILES['logo'], '../uploads/payment_methods/');
        if ($upload['success']) {
            $logo = $upload['filename'];
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO payment_methods (name, type, account_name, account_number, instructions, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $type, $account_name, $account_number, $instructions, $logo, $status);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'add_payment_method', "Added payment method: $name");
        setFlash('success', 'Payment method added successfully');
    } else {
        setFlash('error', 'Failed to add payment method');
    }
    
    redirect('payment-methods.php');
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_method'])) {
    $id = (int)$_POST['method_id'];
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $account_name = trim($_POST['account_name']);
    $account_number = trim($_POST['account_number']);
    $instructions = trim($_POST['instructions']);
    $status = $_POST['status'];
    
    $current = $conn->query("SELECT logo FROM payment_methods WHERE id = $id")->fetch_assoc();
    $logo = $current['logo'];
    
    if (!empty($_FILES['logo']['name'])) {
        $upload = uploadFile($_FILES['logo'], '../uploads/payment_methods/');
        if ($upload['success']) {
            $logo = $upload['filename'];
        }
    }
    
    $stmt = $conn->prepare("UPDATE payment_methods SET name = ?, type = ?, account_name = ?, account_number = ?, instructions = ?, logo = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $name, $type, $account_name, $account_number, $instructions, $logo, $status, $id);
    
    if ($stmt->execute()) {
        logActivity('admin', $admin['id'], 'edit_payment_method', "Updated payment method: $name");
        setFlash('success', 'Payment method updated successfully');
    } else {
        setFlash('error', 'Failed to update payment method');
    }
    
    redirect('payment-methods.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $check = $conn->query("SELECT 
        (SELECT COUNT(*) FROM orders WHERE payment_method_id = $id) as order_count,
        (SELECT COUNT(*) FROM property_orders WHERE payment_method_id = $id) as property_count,
        (SELECT COUNT(*) FROM bookings WHERE payment_method_id = $id) as booking_count
    ");
    $counts = $check->fetch_assoc();
    
    if ($counts['order_count'] > 0 || $counts['property_count'] > 0 || $counts['booking_count'] > 0) {
        setFlash('error', 'Cannot delete payment method with existing transactions');
    } else {
        $stmt = $conn->prepare("DELETE FROM payment_methods WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity('admin', $admin['id'], 'delete_payment_method', "Deleted payment method #$id");
            setFlash('success', 'Payment method deleted successfully');
        } else {
            setFlash('error', 'Failed to delete payment method');
        }
    }
    
    redirect('payment-methods.php');
}

// Get stats
$stats = $conn->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN type = 'mobile_money' THEN 1 ELSE 0 END) as mobile_money,
    SUM(CASE WHEN type = 'bank' THEN 1 ELSE 0 END) as bank
FROM payment_methods")->fetch_assoc();

// Get payment methods
$methods = $conn->query("SELECT * FROM payment_methods ORDER BY name ASC");

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

.methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.method-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    transition: all 0.3s;
}

.method-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    transform: translateY(-4px);
}

.method-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.method-logo {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
    background: #f9fafb;
}

.method-logo-placeholder {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.method-info h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
    color: #1a1a2e;
}

.method-type {
    font-size: 12px;
    color: #666;
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 12px;
    display: inline-block;
}

.method-details {
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

.method-instructions {
    font-size: 13px;
    color: #666;
    margin-bottom: 15px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.method-status {
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

.method-actions {
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
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
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
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .methods-grid {
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
    <button onclick="openAddModal()" class="btn-add">+ Add Payment Method</button>
</div>

<?php if ($flash = getFlash()): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>"><?php echo $flash['message']; ?></div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Methods</h3>
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
        <h3>Mobile Money</h3>
        <div class="number" style="color: #f97316;"><?php echo $stats['mobile_money']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Bank Transfer</h3>
        <div class="number" style="color: #3b82f6;"><?php echo $stats['bank']; ?></div>
    </div>
</div>

<div class="methods-grid">
    <?php if ($methods->num_rows > 0): ?>
        <?php while ($method = $methods->fetch_assoc()): ?>
            <div class="method-card">
                <div class="method-header">
                    <?php if ($method['logo']): ?>
                        <img src="<?= UPLOAD_URL . 'payment_methods/' . e($method['logo']) ?>" alt="" class="method-logo">
                    <?php else: ?>
                        <div class="method-logo-placeholder">💳</div>
                    <?php endif; ?>
                    <div class="method-info">
                        <h3><?php echo e($method['name']); ?></h3>
                        <span class="method-type"><?php echo ucwords(str_replace('_', ' ', $method['type'])); ?></span>
                    </div>
                </div>
                <div class="method-details">
                    <div class="detail-row">
                        <strong>Account Name:</strong>
                        <span><?php echo e($method['account_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <strong>Account Number:</strong>
                        <span><?php echo e($method['account_number']); ?></span>
                    </div>
                </div>
                <?php if ($method['instructions']): ?>
                    <div class="method-instructions"><?php echo e($method['instructions']); ?></div>
                <?php endif; ?>
                <span class="method-status status-<?php echo $method['status']; ?>">
                    <?php echo ucfirst($method['status']); ?>
                </span>
                <div class="method-actions">
                    <button onclick="openEditModal(<?php echo $method['id']; ?>)" class="btn btn-edit">Edit</button>
                    <button onclick="confirmDelete(<?php echo $method['id']; ?>, '<?php echo e($method['name']); ?>')" class="btn btn-delete">Delete</button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
            No payment methods found
        </div>
    <?php endif; ?>
</div>

<div id="methodModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Payment Method</h3>
            <button onclick="closeModal()" class="close-btn">&times;</button>
        </div>
        <form id="methodForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="methodId" name="method_id">
            <input type="hidden" id="formAction" name="add_method" value="1">
            
            <div class="form-group">
                <label>Method Name *</label>
                <input type="text" id="methodName" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    <option value="mobile_money">Mobile Money</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Account Name *</label>
                <input type="text" name="account_name" required>
            </div>
            
            <div class="form-group">
                <label>Account Number *</label>
                <input type="text" name="account_number" required>
            </div>
            
            <div class="form-group">
                <label>Instructions</label>
                <textarea name="instructions"></textarea>
            </div>
            
            <div class="form-group">
                <label>Logo</label>
                <input type="file" name="logo" accept="image/*" onchange="previewImage(event)">
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
                <button type="submit" class="btn btn-primary">Save Method</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="modal">
    <div class="modal-content delete-modal-content">
        <div class="modal-icon">🗑️</div>
        <h3>Delete Payment Method</h3>
        <p id="deleteMessage"></p>
        <div class="form-actions">
            <button onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="executeDelete()" class="btn btn-delete">Delete</button>
        </div>
    </div>
</div>

<script>
let deleteMethodId = null;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Payment Method';
    document.getElementById('methodForm').reset();
    document.getElementById('methodId').value = '';
    document.getElementById('formAction').name = 'add_method';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('methodModal').classList.add('active');
}

function openEditModal(id) {
    fetch('get-payment-method.php?id=' + id)
        .then(r => r.json())
        .then(data => {
            document.getElementById('modalTitle').textContent = 'Edit Payment Method';
            document.getElementById('methodId').value = data.id;
            document.getElementById('methodName').value = data.name;
            document.querySelector('select[name="type"]').value = data.type;
            document.querySelector('input[name="account_name"]').value = data.account_name;
            document.querySelector('input[name="account_number"]').value = data.account_number;
            document.querySelector('textarea[name="instructions"]').value = data.instructions || '';
            document.querySelector('select[name="status"]').value = data.status;
            document.getElementById('formAction').name = 'edit_method';
            
            if (data.logo) {
                document.getElementById('imagePreview').innerHTML = '<img src="<?= UPLOAD_URL ?>payment_methods/' + data.logo + '" alt="">';
            } else {
                document.getElementById('imagePreview').innerHTML = '';
            }
            
            document.getElementById('methodModal').classList.add('active');
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
    document.getElementById('methodModal').classList.remove('active');
}

function confirmDelete(id, name) {
    deleteMethodId = id;
    document.getElementById('deleteMessage').textContent = `Are you sure you want to delete "${name}"?`;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteMethodId = null;
}

function executeDelete() {
    if (deleteMethodId) {
        window.location.href = 'payment-methods.php?delete=' + deleteMethodId;
    }
}

document.getElementById('methodModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
