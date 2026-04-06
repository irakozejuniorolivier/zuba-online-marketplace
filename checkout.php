<?php
/**
 * CHECKOUT PAGE
 * Displays cart items, shipping form, payment methods, and processes orders
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Require login
requireCustomerLogin();

$user_id = currentCustomerId();
$customer = currentCustomer();

// Get selected product IDs from cart page
$selected_ids = [];
if (isset($_GET['items']) && !empty($_GET['items'])) {
    $selected_ids = array_map('intval', explode(',', $_GET['items']));
}

// Fetch cart items with product details
$cart_items = [];
$subtotal = 0;

if (!empty($selected_ids)) {
    $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
    $query = "
        SELECT c.*, p.name, p.price, p.stock, p.status, pi.image_path
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE c.user_id = ? AND c.product_id IN ($placeholders)
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($selected_ids) + 1);
    $params = array_merge([$user_id], $selected_ids);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Check if product is still available
        if ($row['status'] !== 'active' || $row['stock'] < 1) {
            continue; // Skip unavailable products
        }
        
        // Adjust quantity if exceeds stock
        if ($row['quantity'] > $row['stock']) {
            $row['quantity'] = $row['stock'];
            // Update cart
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update_stmt->bind_param('iii', $row['stock'], $user_id, $row['product_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        $row['item_total'] = $row['price'] * $row['quantity'];
        $subtotal += $row['item_total'];
        $cart_items[] = $row;
    }
    $stmt->close();
} else {
    // If no items selected, fetch all cart items
    $query = "
        SELECT c.*, p.name, p.price, p.stock, p.status, pi.image_path
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Check if product is still available
        if ($row['status'] !== 'active' || $row['stock'] < 1) {
            continue; // Skip unavailable products
        }
        
        // Adjust quantity if exceeds stock
        if ($row['quantity'] > $row['stock']) {
            $row['quantity'] = $row['stock'];
            // Update cart
            $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $update_stmt->bind_param('iii', $row['stock'], $user_id, $row['product_id']);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        $row['item_total'] = $row['price'] * $row['quantity'];
        $subtotal += $row['item_total'];
        $cart_items[] = $row;
    }
    $stmt->close();
}

// Redirect if cart is empty
if (empty($cart_items)) {
    $_SESSION['error_message'] = 'Your cart is empty';
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

// Get site settings
$shipping_fee = 0;
$tax_rate = 0;

$result = $conn->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('shipping_fee', 'tax_rate')");
while ($row = $result->fetch_assoc()) {
    if ($row['setting_key'] === 'shipping_fee') {
        $shipping_fee = (float)$row['setting_value'];
    } elseif ($row['setting_key'] === 'tax_rate') {
        $tax_rate = (float)$row['setting_value'];
    }
}

// Calculate totals
$tax = ($subtotal * $tax_rate) / 100;
$total = $subtotal + $shipping_fee + $tax;

// Fetch active payment methods
$payment_methods = [];
$result = $conn->query("SELECT * FROM payment_methods WHERE status = 'active' ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $payment_methods[] = $row;
}

$page_title = 'Checkout';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Zuba Online Market</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Popup Styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/popup.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
        
        /* Header */
        .checkout-header { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; transform: scale(1.05); }
        .header-title h1 { font-size: 24px; font-weight: 900; color: #1a1a2e; margin: 0; }
        .header-title p { font-size: 13px; color: #6b7280; margin: 4px 0 0; }
        .secure-badge { display: flex; align-items: center; gap: 8px; background: #d1fae5; color: #065f46; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; }
        .secure-badge i { font-size: 16px; }
        
        /* Main Content */
        .checkout-container { max-width: 1200px; margin: 0 auto; padding: 32px 20px; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 24px; }
        
        /* Left Column - Forms */
        .checkout-section { background: #fff; border-radius: 16px; padding: 28px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6; }
        .section-number { width: 36px; height: 36px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16px; }
        .section-header h2 { font-size: 20px; font-weight: 900; color: #1a1a2e; margin: 0; }
        
        /* Form Styles */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 20px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { display: block; font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
        .form-group label .required { color: #ef4444; margin-left: 4px; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; transition: all .3s; background: #fff; }
        .form-control:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .form-control:disabled { background: #f9fafb; cursor: not-allowed; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        
        /* Payment Methods */
        .payment-methods { display: grid; gap: 12px; }
        .payment-method { border: 2px solid #e5e7eb; border-radius: 12px; padding: 16px; cursor: pointer; transition: all .3s; display: flex; align-items: center; gap: 16px; background: #fff; }
        .payment-method:hover { border-color: #f97316; background: #fff5f0; }
        .payment-method.selected { border-color: #f97316; background: #fff5f0; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .payment-method input[type="radio"] { width: 20px; height: 20px; accent-color: #f97316; cursor: pointer; }
        .payment-logo { width: 50px; height: 50px; background: #f9fafb; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #f97316; }
        .payment-info { flex: 1; }
        .payment-info h4 { font-size: 15px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; }
        .payment-info p { font-size: 12px; color: #6b7280; margin: 0; }
        
        .payment-details { margin-top: 16px; padding: 16px; background: #fff5f0; border-radius: 10px; border: 1px solid rgba(249,115,22,0.2); display: none; }
        .payment-details.active { display: block; }
        .payment-details h5 { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0 0 12px; }
        .payment-detail-item { margin-bottom: 8px; font-size: 13px; }
        .payment-detail-item strong { color: #1a1a2e; font-weight: 700; }
        .payment-detail-item span { color: #6b7280; }
        
        /* File Upload */
        .file-upload-wrapper { position: relative; }
        .file-upload-label { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; border: 2px dashed #e5e7eb; border-radius: 10px; cursor: pointer; transition: all .3s; background: #f9fafb; }
        .file-upload-label:hover { border-color: #f97316; background: #fff5f0; }
        .file-upload-label i { font-size: 20px; color: #f97316; }
        .file-upload-label span { font-size: 14px; font-weight: 600; color: #6b7280; }
        .file-upload-input { display: none; }
        .file-preview { margin-top: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; display: none; align-items: center; gap: 12px; }
        .file-preview.active { display: flex; }
        .file-preview i { font-size: 24px; color: #10b981; }
        .file-preview span { flex: 1; font-size: 13px; color: #1a1a2e; font-weight: 600; }
        .file-preview button { background: #fee2e2; color: #991b1b; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: all .3s; }
        .file-preview button:hover { background: #fecaca; }
        
        /* Right Column - Order Summary */
        .order-summary { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); position: sticky; top: 100px; height: fit-content; }
        .summary-header { font-size: 20px; font-weight: 900; color: #1a1a2e; margin: 0 0 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6; }
        
        .cart-items { margin-bottom: 20px; max-height: 300px; overflow-y: auto; }
        .cart-item { display: flex; gap: 12px; padding: 12px; background: #f9fafb; border-radius: 10px; margin-bottom: 12px; }
        .cart-item-img { width: 60px; height: 60px; border-radius: 8px; overflow: hidden; background: #fff; flex-shrink: 0; }
        .cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
        .cart-item-img .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 24px; }
        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-name { font-size: 13px; font-weight: 700; color: #1a1a2e; margin: 0 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cart-item-qty { font-size: 12px; color: #6b7280; margin: 0 0 4px; }
        .cart-item-price { font-size: 14px; font-weight: 800; color: #f97316; }
        
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; font-size: 14px; }
        .summary-row.total { border-top: 2px solid #f3f4f6; margin-top: 12px; padding-top: 16px; font-size: 18px; font-weight: 900; color: #1a1a2e; }
        .summary-row .label { color: #6b7280; font-weight: 600; }
        .summary-row .value { color: #1a1a2e; font-weight: 700; }
        .summary-row.total .value { color: #f97316; }
        
        .btn-place-order { width: 100%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; transition: all .3s; margin-top: 20px; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-place-order:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-place-order:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
        
        /* Responsive */
        @media (max-width: 900px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; order: -1; }
            .form-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 640px) {
            .checkout-container { padding: 20px 16px; }
            .checkout-section { padding: 20px; }
            .header-content { padding: 12px 16px; }
            .header-title h1 { font-size: 20px; }
            .secure-badge { display: none; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="checkout-header">
    <div class="header-content">
        <div class="header-left">
            <a href="<?= SITE_URL ?>/cart.php" class="btn-back" title="Back to Cart">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">
                <h1>Checkout</h1>
                <p><?= count($cart_items) ?> item(s) in your cart</p>
            </div>
        </div>
        <div class="secure-badge">
            <i class="fas fa-lock"></i>
            <span>Secure Checkout</span>
        </div>
    </div>
</header>

<!-- Main Content -->
<div class="checkout-container">
    <form id="checkoutForm" method="POST" enctype="multipart/form-data">
        <div class="checkout-grid">
            <!-- Left Column -->
            <div>
                <!-- Shipping Information -->
                <div class="checkout-section">
                    <div class="section-header">
                        <div class="section-number">1</div>
                        <h2>Shipping Information</h2>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name <span class="required">*</span></label>
                            <input type="text" name="shipping_name" class="form-control" value="<?= e($customer['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number <span class="required">*</span></label>
                            <input type="tel" name="shipping_phone" class="form-control" value="<?= e($customer['phone']) ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Address <span class="required">*</span></label>
                            <input type="text" name="shipping_address" class="form-control" value="<?= e($customer['address'] ?? '') ?>" placeholder="Street address, P.O. Box, etc." required>
                        </div>
                        
                        <div class="form-group">
                            <label>City <span class="required">*</span></label>
                            <input type="text" name="shipping_city" class="form-control" value="<?= e($customer['city'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Country <span class="required">*</span></label>
                            <input type="text" name="shipping_country" class="form-control" value="<?= e($customer['country'] ?? 'Rwanda') ?>" required>
                        </div>
                        
                        <div class="form-group full-width">
                            <label>Order Notes (Optional)</label>
                            <textarea name="customer_note" class="form-control" placeholder="Any special instructions for your order..."></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="checkout-section">
                    <div class="section-header">
                        <div class="section-number">2</div>
                        <h2>Payment Method</h2>
                    </div>
                    
                    <div class="payment-methods">
                        <?php foreach ($payment_methods as $index => $method): ?>
                            <label class="payment-method" data-method-id="<?= $method['id'] ?>">
                                <input type="radio" name="payment_method_id" value="<?= $method['id'] ?>" <?= $index === 0 ? 'checked' : '' ?> required>
                                <div class="payment-logo">
                                    <i class="fas fa-<?= $method['type'] === 'mobile_money' ? 'mobile-alt' : ($method['type'] === 'bank' ? 'university' : 'credit-card') ?>"></i>
                                </div>
                                <div class="payment-info">
                                    <h4><?= e($method['name']) ?></h4>
                                    <p><?= e(ucfirst(str_replace('_', ' ', $method['type']))) ?></p>
                                </div>
                            </label>
                            
                            <div class="payment-details <?= $index === 0 ? 'active' : '' ?>" id="payment-details-<?= $method['id'] ?>">
                                <h5>Payment Instructions:</h5>
                                <?php if ($method['account_name']): ?>
                                    <div class="payment-detail-item">
                                        <strong>Account Name:</strong> <span><?= e($method['account_name']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($method['account_number']): ?>
                                    <div class="payment-detail-item">
                                        <strong>Account Number:</strong> <span><?= e($method['account_number']) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($method['instructions']): ?>
                                    <div class="payment-detail-item">
                                        <strong>Instructions:</strong> <span><?= e($method['instructions']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-group" style="margin-top: 24px;">
                        <label>Upload Payment Proof <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <label for="payment_proof" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Click to upload screenshot or receipt</span>
                            </label>
                            <input type="file" id="payment_proof" name="payment_proof" class="file-upload-input" accept="image/*" required>
                            <div class="file-preview" id="filePreview">
                                <i class="fas fa-check-circle"></i>
                                <span id="fileName"></span>
                                <button type="button" onclick="removeFile()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Order Summary -->
            <div>
                <div class="order-summary">
                    <h3 class="summary-header">Order Summary</h3>
                    
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="cart-item-img">
                                    <?php if ($item['image_path']): ?>
                                        <img src="<?= UPLOAD_URL . 'products/' . $item['image_path'] ?>" alt="<?= e($item['name']) ?>">
                                    <?php else: ?>
                                        <div class="no-img"><i class="fas fa-box-open"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="cart-item-info">
                                    <p class="cart-item-name"><?= e($item['name']) ?></p>
                                    <p class="cart-item-qty">Qty: <?= $item['quantity'] ?></p>
                                    <p class="cart-item-price"><?= formatCurrency($item['item_total']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-row">
                        <span class="label">Subtotal:</span>
                        <span class="value"><?= formatCurrency($subtotal) ?></span>
                    </div>
                    
                    <?php if ($shipping_fee > 0): ?>
                        <div class="summary-row">
                            <span class="label">Shipping Fee:</span>
                            <span class="value"><?= formatCurrency($shipping_fee) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($tax > 0): ?>
                        <div class="summary-row">
                            <span class="label">Tax (<?= $tax_rate ?>%):</span>
                            <span class="value"><?= formatCurrency($tax) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-row total">
                        <span class="label">Total:</span>
                        <span class="value"><?= formatCurrency($total) ?></span>
                    </div>
                    
                    <button type="submit" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="<?= SITE_URL ?>/assets/js/popup.js"></script>
<script>
// Payment method selection
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        // Remove selected class from all
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
        document.querySelectorAll('.payment-details').forEach(d => d.classList.remove('active'));
        
        // Add selected class
        this.classList.add('selected');
        
        // Show payment details
        const methodId = this.dataset.methodId;
        document.getElementById('payment-details-' + methodId).classList.add('active');
        
        // Check radio
        this.querySelector('input[type="radio"]').checked = true;
    });
});

// File upload preview
document.getElementById('payment_proof').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('filePreview').classList.add('active');
    }
});

function removeFile() {
    document.getElementById('payment_proof').value = '';
    document.getElementById('filePreview').classList.remove('active');
}

// Form submission
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('.btn-place-order');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    
    const formData = new FormData(this);
    
    // Add selected items if from cart page
    const urlParams = new URLSearchParams(window.location.search);
    const selectedItems = urlParams.get('items');
    if (selectedItems) {
        formData.append('selected_items', selectedItems);
    }
    
    fetch('<?= SITE_URL ?>/api/place-order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showPopup({
                type: 'success',
                icon: 'fa-check-circle',
                title: 'Order Placed Successfully!',
                message: `Your order #${data.order_number} has been placed. We will review your payment and process your order shortly.`,
                confirmText: 'View Order',
                onConfirm: () => {
                    window.location.href = '<?= SITE_URL ?>/order-details.php?order=' + data.order_number;
                }
            });
        } else {
            submitBtn.innerHTML = originalHTML;
            submitBtn.disabled = false;
            
            // Show detailed error message
            let errorMsg = data.message || 'Failed to place order. Please try again.';
            if (data.debug) {
                console.error('Debug info:', data.debug);
            }
            
            showPopup({
                type: 'error',
                icon: 'fa-exclamation-circle',
                title: 'Order Failed',
                message: errorMsg,
                confirmText: 'OK'
            });
        }
    })
    .catch(error => {
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
        
        console.error('Fetch error:', error);
        
        showPopup({
            type: 'error',
            icon: 'fa-exclamation-circle',
            title: 'Error',
            message: 'An error occurred. Please check your internet connection and try again.',
            confirmText: 'OK'
        });
    });
});
</script>

</body>
</html>
