<?php
/**
 * PROPERTY CHECKOUT PAGE
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isCustomerLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$customer = currentCustomer();
$user_id = currentCustomerId();

// Get property ID from URL
$property_id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

if (!$property_id) {
    header('Location: ' . SITE_URL . '/properties.php');
    exit;
}

// Fetch property details
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM properties p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    header('Location: ' . SITE_URL . '/properties.php');
    exit;
}

// Get rent details from URL if rent order
$rent_duration = isset($_GET['rent_duration']) ? (int)$_GET['rent_duration'] : 1;
$rent_period = isset($_GET['rent_period']) ? $_GET['rent_period'] : 'monthly';
$customer_note = isset($_GET['note']) ? $_GET['note'] : '';

// Calculate total amount
$order_type = $property['listing_type'] === 'rent' ? 'rent' : 'purchase';
$amount = $property['price'];

if ($order_type === 'rent') {
    // Calculate based on rent period
    $amount = $property['price'] * $rent_duration;
}

// Fetch property image
$image_path = null;
$stmt = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $image_path = $row['image_path'];
}
$stmt->close();

// Fetch payment methods
$payment_methods = [];
$result = $conn->query("SELECT * FROM payment_methods WHERE status = 'active' ORDER BY id ASC");
while ($row = $result->fetch_assoc()) {
    $payment_methods[] = $row;
}

$page_title = 'Property Checkout';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Zuba Online Market</title>
    
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
        .header-content { display: flex; align-items: center; gap: 16px; padding: 14px 16px; max-width: 1200px; margin: 0 auto; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; }
        .header-title { flex: 1; font-size: 18px; font-weight: 700; color: #1a1a2e; }
        
        /* Container */
        .container { max-width: 800px; margin: 0 auto; padding: 20px 16px 40px; }
        
        /* Property Card */
        .property-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .property-content { display: flex; gap: 16px; align-items: start; }
        .property-image { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; background: #f9fafb; flex-shrink: 0; border: 1px solid #e5e7eb; }
        .property-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 36px; }
        .property-info { flex: 1; min-width: 0; }
        .property-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .property-badge.rent { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .property-name { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; line-height: 1.3; }
        .property-location { display: flex; align-items: center; gap: 6px; color: #6b7280; font-size: 13px; margin-bottom: 10px; }
        .property-location i { color: #f97316; font-size: 12px; flex-shrink: 0; }
        .property-price { font-size: 20px; font-weight: 900; color: #f97316; }
        
        /* Section */
        .section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .section-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; }
        .section-title i { color: #f97316; font-size: 18px; }
        
        /* Form */
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-label { font-size: 14px; font-weight: 700; color: #1a1a2e; }
        .form-label .required { color: #ef4444; margin-left: 2px; }
        .form-input { padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; transition: all .3s; background: #fff; }
        .form-input:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .form-input:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }
        
        /* File Upload */
        .file-upload-wrapper { position: relative; }
        .file-upload-label { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 40px 20px; border: 2px dashed #e5e7eb; border-radius: 10px; cursor: pointer; transition: all .3s; background: #f9fafb; }
        .file-upload-label:hover { border-color: #f97316; background: #fff5f0; }
        .file-upload-label i { font-size: 32px; color: #f97316; }
        .file-upload-text { text-align: center; }
        .file-upload-text strong { display: block; font-size: 14px; color: #1a1a2e; margin-bottom: 4px; }
        .file-upload-text span { font-size: 12px; color: #6b7280; }
        .file-upload-input { position: absolute; opacity: 0; pointer-events: none; }
        .file-preview { margin-top: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; display: none; }
        .file-preview.show { display: flex; align-items: center; gap: 10px; }
        .file-preview i { color: #10b981; font-size: 20px; }
        .file-preview-info { flex: 1; }
        .file-preview-name { font-size: 13px; font-weight: 600; color: #1a1a2e; }
        .file-preview-size { font-size: 12px; color: #6b7280; }
        .file-remove { background: #fee2e2; color: #ef4444; border: none; width: 28px; height: 28px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .3s; }
        .file-remove:hover { background: #fecaca; }
        
        /* Order Summary */
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
        .summary-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        .summary-row:last-child { padding-top: 16px; margin-top: 8px; border-top: 2px solid #e5e7eb; }
        .summary-label { font-size: 14px; color: #6b7280; font-weight: 600; }
        .summary-value { font-size: 14px; color: #1a1a2e; font-weight: 700; }
        .summary-row:last-child .summary-label { font-size: 16px; color: #1a1a2e; font-weight: 700; }
        .summary-row:last-child .summary-value { font-size: 24px; color: #f97316; font-weight: 900; }
        
        /* Payment Methods */
        .payment-methods { display: grid; gap: 12px; margin-bottom: 20px; }
        .payment-method { display: flex; align-items: center; gap: 12px; padding: 14px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; transition: all .3s; background: #fff; }
        .payment-method:hover { border-color: #f97316; background: #fff5f0; }
        .payment-method.selected { border-color: #f97316; background: #fff5f0; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .payment-radio { width: 20px; height: 20px; border: 2px solid #d1d5db; border-radius: 50%; position: relative; flex-shrink: 0; transition: all .3s; }
        .payment-method.selected .payment-radio { border-color: #f97316; }
        .payment-method.selected .payment-radio::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 10px; height: 10px; background: #f97316; border-radius: 50%; }
        .payment-info { flex: 1; min-width: 0; }
        .payment-name { font-size: 14px; font-weight: 700; color: #1a1a2e; margin-bottom: 2px; }
        .payment-details { font-size: 12px; color: #6b7280; }
        
        .payment-instructions { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px; margin-top: 12px; }
        .payment-instructions-title { font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .payment-instructions-title i { font-size: 14px; }
        .payment-instructions-text { font-size: 12px; color: #1e40af; line-height: 1.5; }
        
        /* Buttons */
        .btn-submit { width: 100%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all .3s; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-submit:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container { padding: 16px 12px 40px; }
            .property-content { gap: 12px; }
            .property-image { width: 80px; height: 80px; }
            .property-name { font-size: 15px; }
            .property-price { font-size: 18px; }
            .section { padding: 16px; margin-bottom: 16px; }
            .property-card { padding: 16px; margin-bottom: 16px; }
            .section-title { font-size: 15px; margin-bottom: 14px; padding-bottom: 10px; }
            .file-upload-label { padding: 30px 16px; }
            .file-upload-label i { font-size: 28px; }
        }
        
        @media (max-width: 640px) {
            .header-content { padding: 12px; }
            .header-title { font-size: 16px; }
            .btn-back { width: 36px; height: 36px; font-size: 16px; }
            .btn-submit { padding: 14px; font-size: 15px; }
            .summary-row:last-child .summary-value { font-size: 22px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="checkout-header">
    <div class="header-content">
        <a href="javascript:history.back()" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">Property Checkout</div>
    </div>
</header>

<div class="container">
    <!-- Property Card -->
    <div class="property-card">
        <div class="property-content">
            <?php if ($image_path): ?>
                <img src="<?= UPLOAD_URL . 'properties/' . $image_path ?>" alt="<?= e($property['title']) ?>" class="property-image">
            <?php else: ?>
                <div class="property-image no-image">
                    <i class="fas fa-building"></i>
                </div>
            <?php endif; ?>
            
            <div class="property-info">
                <span class="property-badge <?= $order_type === 'rent' ? 'rent' : '' ?>">
                    <i class="fas fa-<?= $order_type === 'rent' ? 'key' : 'tag' ?>"></i>
                    For <?= ucfirst($property['listing_type']) ?>
                </span>
                <h2 class="property-name"><?= e($property['title']) ?></h2>
                <div class="property-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?= e($property['city'] . ', ' . $property['country']) ?></span>
                </div>
                <div class="property-price">
                    <?= formatCurrency($property['price']) ?>
                    <?php if ($order_type === 'rent'): ?>
                        <span style="font-size: 14px; color: #6b7280; font-weight: 600;">/ <?= ucfirst($rent_period) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <form id="checkoutForm" method="POST">
        <!-- Customer Information -->
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Customer Information
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name<span class="required">*</span></label>
                    <input type="text" name="customer_name" class="form-input" value="<?= e($customer['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number<span class="required">*</span></label>
                    <input type="tel" name="customer_phone" class="form-input" value="<?= e($customer['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address<span class="required">*</span></label>
                    <input type="email" name="customer_email" class="form-input" value="<?= e($customer['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">City<span class="required">*</span></label>
                    <input type="text" name="customer_city" class="form-input" value="<?= e($customer['city'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address<span class="required">*</span></label>
                    <input type="text" name="customer_address" class="form-input" value="<?= e($customer['address'] ?? '') ?>" required>
                </div>
            </div>
        </div>
        
        <!-- Order Details -->
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-file-alt"></i>
                Order Details
            </h3>
            <?php if ($order_type === 'rent'): ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Rent Duration</label>
                        <input type="number" class="form-input" value="<?= $rent_duration ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rent Period</label>
                        <input type="text" class="form-input" value="<?= ucfirst($rent_period) ?>" disabled>
                    </div>
                </div>
            <?php endif; ?>
            <div class="form-group" style="margin-top: <?= $order_type === 'rent' ? '16px' : '0' ?>;">
                <label class="form-label">Customer Note (Optional)</label>
                <textarea name="customer_note" class="form-input" rows="3" placeholder="Any special requests or questions..."><?= e($customer_note) ?></textarea>
            </div>
        </div>
        
        <!-- Payment Method -->
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-credit-card"></i>
                Payment Method
            </h3>
            <div class="payment-methods">
                <?php foreach ($payment_methods as $index => $method): ?>
                    <label class="payment-method <?= $index === 0 ? 'selected' : '' ?>" data-method-id="<?= $method['id'] ?>">
                        <div class="payment-radio"></div>
                        <div class="payment-info">
                            <div class="payment-name"><?= e($method['name']) ?></div>
                            <div class="payment-details"><?= e($method['account_number']) ?></div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="payment_method_id" id="paymentMethodId" value="<?= $payment_methods[0]['id'] ?? '' ?>" required>
            
            <?php if (!empty($payment_methods)): ?>
            <div class="payment-instructions" id="paymentInstructions">
                <div class="payment-instructions-title">
                    <i class="fas fa-info-circle"></i>
                    Payment Instructions
                </div>
                <div class="payment-instructions-text" id="instructionsText">
                    <?= nl2br(e($payment_methods[0]['instructions'] ?? 'Please make payment and upload proof below.')) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Payment Proof -->
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-receipt"></i>
                Payment Proof<span class="required">*</span>
            </h3>
            <div class="file-upload-wrapper">
                <label for="paymentProof" class="file-upload-label">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div class="file-upload-text">
                        <strong>Upload Payment Proof</strong>
                        <span>Click to select image (JPG, PNG, PDF - Max 5MB)</span>
                    </div>
                </label>
                <input type="file" id="paymentProof" name="payment_proof" class="file-upload-input" accept="image/*,.pdf" required>
                <div class="file-preview" id="filePreview">
                    <i class="fas fa-file-image"></i>
                    <div class="file-preview-info">
                        <div class="file-preview-name" id="fileName"></div>
                        <div class="file-preview-size" id="fileSize"></div>
                    </div>
                    <button type="button" class="file-remove" onclick="removeFile()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-receipt"></i>
                Order Summary
            </h3>
            <div class="summary-row">
                <span class="summary-label">Property Price</span>
                <span class="summary-value"><?= formatCurrency($property['price']) ?></span>
            </div>
            <?php if ($order_type === 'rent'): ?>
                <div class="summary-row">
                    <span class="summary-label">Duration</span>
                    <span class="summary-value"><?= $rent_duration ?> <?= ucfirst($rent_period) ?>(s)</span>
                </div>
            <?php endif; ?>
            <div class="summary-row">
                <span class="summary-label">Total Amount</span>
                <span class="summary-value"><?= formatCurrency($amount) ?></span>
            </div>
        </div>
        
        <!-- Hidden Fields -->
        <input type="hidden" name="property_id" value="<?= $property_id ?>">
        <input type="hidden" name="order_type" value="<?= $order_type ?>">
        <input type="hidden" name="amount" value="<?= $amount ?>">
        <?php if ($order_type === 'rent'): ?>
            <input type="hidden" name="rent_duration" value="<?= $rent_duration ?>">
            <input type="hidden" name="rent_period" value="<?= $rent_period ?>">
        <?php endif; ?>
        
        <!-- Submit Button -->
        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-check-circle"></i>
            <span>Place Order</span>
        </button>
    </form>
</div>

<script>
    // Store payment methods data
    const paymentMethodsData = <?= json_encode($payment_methods) ?>;
    
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            const methodId = this.dataset.methodId;
            document.getElementById('paymentMethodId').value = methodId;
            
            // Update instructions
            const selectedMethod = paymentMethodsData.find(m => m.id == methodId);
            if (selectedMethod && selectedMethod.instructions) {
                document.getElementById('instructionsText').innerHTML = selectedMethod.instructions.replace(/\n/g, '<br>');
            }
        });
    });
    
    // File upload handling
    document.getElementById('paymentProof').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                showPopup({
                    type: 'error',
                    icon: 'fa-exclamation-circle',
                    title: 'File Too Large',
                    message: 'Please select a file smaller than 5MB.',
                    confirmText: 'OK'
                });
                e.target.value = '';
                return;
            }
            
            // Show preview
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            document.getElementById('filePreview').classList.add('show');
        }
    });
    
    function removeFile() {
        document.getElementById('paymentProof').value = '';
        document.getElementById('filePreview').classList.remove('show');
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Form submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate payment proof
        const paymentProof = document.getElementById('paymentProof').files[0];
        if (!paymentProof) {
            showPopup({
                type: 'error',
                icon: 'fa-exclamation-circle',
                title: 'Payment Proof Required',
                message: 'Please upload your payment proof to continue.',
                confirmText: 'OK'
            });
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        const originalHTML = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        
        // Prepare form data
        const formData = new FormData(this);
        
        // Send to API
        fetch('<?= SITE_URL ?>/api/property-order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to confirmation page
                window.location.href = '<?= SITE_URL ?>/property-order-confirmation.php?order_id=' + data.order_id;
            } else {
                // Show error
                showPopup({
                    type: 'error',
                    icon: 'fa-exclamation-circle',
                    title: 'Error',
                    message: data.message || 'Failed to create order. Please try again.',
                    confirmText: 'OK'
                });
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showPopup({
                type: 'error',
                icon: 'fa-exclamation-circle',
                title: 'Error',
                message: 'An error occurred. Please try again.',
                confirmText: 'OK'
            });
            
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        });
    });
</script>

<script src="<?= SITE_URL ?>/assets/js/popup.js"></script>

</body>
</html>
