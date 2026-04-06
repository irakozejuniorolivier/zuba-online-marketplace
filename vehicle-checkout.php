<?php
/**
 * VEHICLE CHECKOUT PAGE
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

// Get vehicle ID from URL
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;

if (!$vehicle_id) {
    header('Location: ' . SITE_URL . '/vehicles.php');
    exit;
}

// Fetch vehicle details
$stmt = $conn->prepare("SELECT v.*, c.name as category_name FROM vehicles v LEFT JOIN categories c ON v.category_id = c.id WHERE v.id = ?");
$stmt->bind_param('i', $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();
$stmt->close();

if (!$vehicle) {
    header('Location: ' . SITE_URL . '/vehicles.php');
    exit;
}

// Get booking details from URL
$start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+1 day'));
$days = isset($_GET['days']) ? (int)$_GET['days'] : 1;
$pickup_location = isset($_GET['pickup']) ? $_GET['pickup'] : '';
$customer_note = isset($_GET['note']) ? $_GET['note'] : '';

// Calculate total amount
$rate_type = 'daily';
$rate_amount = $vehicle['daily_rate'];
$subtotal = $rate_amount * $days;
$insurance_fee = 0;
$total_amount = $subtotal + $insurance_fee;

// Fetch vehicle image
$image_path = null;
$stmt = $conn->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1");
$stmt->bind_param('i', $vehicle_id);
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

$page_title = 'Vehicle Checkout';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Zuba Online Market</title>
    
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/popup.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f5; color: #1a1a2e; }
        
        .checkout-header { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header-content { display: flex; align-items: center; gap: 16px; padding: 14px 16px; max-width: 1200px; margin: 0 auto; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; }
        .header-title { flex: 1; font-size: 18px; font-weight: 700; color: #1a1a2e; }
        
        .container { max-width: 800px; margin: 0 auto; padding: 20px 16px 40px; }
        
        .vehicle-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .vehicle-content { display: flex; gap: 16px; align-items: start; }
        .vehicle-image { width: 100px; height: 100px; border-radius: 10px; object-fit: cover; background: #f9fafb; flex-shrink: 0; border: 1px solid #e5e7eb; }
        .vehicle-image.no-image { display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 36px; }
        .vehicle-info { flex: 1; min-width: 0; }
        .vehicle-badge { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f97316 0%, #fb923c 100%); color: #fff; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; }
        .vehicle-name { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; line-height: 1.3; }
        .vehicle-meta { display: flex; align-items: center; gap: 12px; color: #6b7280; font-size: 13px; margin-bottom: 10px; flex-wrap: wrap; }
        .vehicle-meta span { display: flex; align-items: center; gap: 4px; }
        .vehicle-meta i { color: #f97316; font-size: 12px; }
        .vehicle-price { font-size: 20px; font-weight: 900; color: #f97316; }
        
        .section { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid #e5e7eb; }
        .section-title { font-size: 16px; font-weight: 700; color: #1a1a2e; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 2px solid #f3f4f6; }
        .section-title i { color: #f97316; font-size: 18px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-label { font-size: 14px; font-weight: 700; color: #1a1a2e; }
        .form-label .required { color: #ef4444; margin-left: 2px; }
        .form-input { padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; transition: all .3s; background: #fff; }
        .form-input:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .form-input:disabled { background: #f9fafb; color: #9ca3af; cursor: not-allowed; }
        
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
        
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
        .summary-row:not(:last-child) { border-bottom: 1px solid #f3f4f6; }
        .summary-row:last-child { padding-top: 16px; margin-top: 8px; border-top: 2px solid #e5e7eb; }
        .summary-label { font-size: 14px; color: #6b7280; font-weight: 600; }
        .summary-value { font-size: 14px; color: #1a1a2e; font-weight: 700; }
        .summary-row:last-child .summary-label { font-size: 16px; color: #1a1a2e; font-weight: 700; }
        .summary-row:last-child .summary-value { font-size: 24px; color: #f97316; font-weight: 900; }
        
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
        
        .btn-submit { width: 100%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all .3s; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-submit:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
        
        @media (max-width: 768px) {
            .container { padding: 16px 12px 40px; }
            .vehicle-content { gap: 12px; }
            .vehicle-image { width: 80px; height: 80px; }
            .vehicle-name { font-size: 15px; }
            .vehicle-price { font-size: 18px; }
            .section { padding: 16px; margin-bottom: 16px; }
            .vehicle-card { padding: 16px; margin-bottom: 16px; }
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

<header class="checkout-header">
    <div class="header-content">
        <a href="<?= SITE_URL ?>/vehicle-detail.php?id=<?= $vehicle_id ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="header-title">Vehicle Checkout</div>
    </div>
</header>

<div class="container">
    <div class="vehicle-card">
        <div class="vehicle-content">
            <?php if ($image_path): ?>
                <img src="<?= SITE_URL . '/' . htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']) ?>" class="vehicle-image">
            <?php else: ?>
                <div class="vehicle-image no-image">
                    <i class="fas fa-car"></i>
                </div>
            <?php endif; ?>
            
            <div class="vehicle-info">
                <span class="vehicle-badge">
                    <i class="fas fa-car"></i>
                    Car Rental
                </span>
                <h2 class="vehicle-name"><?= htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model'] . ' ' . $vehicle['year']) ?></h2>
                <div class="vehicle-meta">
                    <span><i class="fas fa-cog"></i> <?= ucfirst($vehicle['transmission']) ?></span>
                    <span><i class="fas fa-gas-pump"></i> <?= ucfirst($vehicle['fuel_type']) ?></span>
                    <span><i class="fas fa-users"></i> <?= $vehicle['seats'] ?> Seats</span>
                </div>
                <div class="vehicle-price">
                    RWF <?= number_format($vehicle['daily_rate']) ?>/day
                </div>
            </div>
        </div>
    </div>
    
    <form id="checkoutForm" method="POST">
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Customer Information
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name<span class="required">*</span></label>
                    <input type="text" name="customer_name" class="form-input" value="<?= htmlspecialchars($customer['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number<span class="required">*</span></label>
                    <input type="tel" name="customer_phone" class="form-input" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address<span class="required">*</span></label>
                    <input type="email" name="customer_email" class="form-input" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Booking Details
            </h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-input" value="<?= htmlspecialchars($start_date) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-input" value="<?= htmlspecialchars($end_date) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Rental Days</label>
                    <input type="text" class="form-input" value="<?= $days ?> Day(s)" disabled>
                </div>
                <?php if ($pickup_location): ?>
                <div class="form-group">
                    <label class="form-label">Pickup Location</label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($pickup_location) ?>" disabled>
                </div>
                <?php endif; ?>
            </div>
            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">Customer Note (Optional)</label>
                <textarea name="customer_note" class="form-input" rows="3" placeholder="Any special requests..."><?= htmlspecialchars($customer_note) ?></textarea>
            </div>
        </div>
        
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
                            <div class="payment-name"><?= htmlspecialchars($method['name']) ?></div>
                            <div class="payment-details"><?= htmlspecialchars($method['account_number']) ?></div>
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
                    <?= nl2br(htmlspecialchars($payment_methods[0]['instructions'] ?? 'Please make payment and upload proof below.')) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
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
        
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-receipt"></i>
                Booking Summary
            </h3>
            <div class="summary-row">
                <span class="summary-label">Daily Rate</span>
                <span class="summary-value">RWF <?= number_format($rate_amount) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Rental Days</span>
                <span class="summary-value"><?= $days ?> Day(s)</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Subtotal</span>
                <span class="summary-value">RWF <?= number_format($subtotal) ?></span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Total Amount</span>
                <span class="summary-value">RWF <?= number_format($total_amount) ?></span>
            </div>
        </div>
        
        <input type="hidden" name="vehicle_id" value="<?= $vehicle_id ?>">
        <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
        <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
        <input type="hidden" name="rental_days" value="<?= $days ?>">
        <input type="hidden" name="rate_type" value="<?= $rate_type ?>">
        <input type="hidden" name="rate_amount" value="<?= $rate_amount ?>">
        <input type="hidden" name="subtotal" value="<?= $subtotal ?>">
        <input type="hidden" name="total_amount" value="<?= $total_amount ?>">
        <input type="hidden" name="pickup_location" value="<?= htmlspecialchars($pickup_location) ?>">
        
        <button type="submit" class="btn-submit" id="submitBtn">
            <i class="fas fa-check-circle"></i>
            <span>Complete Booking</span>
        </button>
    </form>
</div>

<script>
    const paymentMethodsData = <?= json_encode($payment_methods) ?>;
    
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            const methodId = this.dataset.methodId;
            document.getElementById('paymentMethodId').value = methodId;
            
            const selectedMethod = paymentMethodsData.find(m => m.id == methodId);
            if (selectedMethod && selectedMethod.instructions) {
                document.getElementById('instructionsText').innerHTML = selectedMethod.instructions.replace(/\n/g, '<br>');
            }
        });
    });
    
    document.getElementById('paymentProof').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
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
    
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
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
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Processing...</span>';
        
        const formData = new FormData(this);
        
        fetch('<?= SITE_URL ?>/api/vehicle-booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(text => {
            console.log('Response:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.location.href = '<?= SITE_URL ?>/booking-confirmation.php?booking_id=' + data.booking_id;
                } else {
                    showPopup({
                        type: 'error',
                        icon: 'fa-exclamation-circle',
                        title: 'Error',
                        message: data.message || 'Failed to create booking. Please try again.',
                        confirmText: 'OK'
                    });
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                }
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response Text:', text);
                showPopup({
                    type: 'error',
                    icon: 'fa-exclamation-circle',
                    title: 'Error',
                    message: 'Server returned invalid response. Check console for details.',
                    confirmText: 'OK'
                });
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
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
<script>
    console.log('Vehicle Checkout Page Loaded');
    console.log('API URL:', '<?= SITE_URL ?>/api/vehicle-booking.php');
</script>

</body>
</html>
