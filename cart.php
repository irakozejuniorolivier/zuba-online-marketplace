<?php
/**
 * SHOPPING CART PAGE
 * Displays all cart items with selection, quantity update, and checkout options
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

// Get product ID from Buy Now (if redirected from product detail)
$buy_now_product = isset($_GET['buy_now']) ? (int)$_GET['buy_now'] : 0;

// Fetch cart items with product details
$cart_items = [];
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
    $row['item_total'] = $row['price'] * $row['quantity'];
    $row['available'] = ($row['status'] === 'active' && $row['stock'] >= $row['quantity']);
    $cart_items[] = $row;
}
$stmt->close();

$page_title = 'Shopping Cart';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Zuba Online Market</title>
    
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
        .cart-header { background: #fff; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .header-content { max-width: 1400px; margin: 0 auto; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .btn-back { background: #f9fafb; border: 1px solid #e5e7eb; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all .3s; color: #1a1a2e; font-size: 18px; text-decoration: none; }
        .btn-back:hover { background: #f97316; color: #fff; border-color: #f97316; transform: scale(1.05); }
        .header-title h1 { font-size: 24px; font-weight: 900; color: #1a1a2e; margin: 0; }
        .header-title p { font-size: 13px; color: #6b7280; margin: 4px 0 0; }
        
        /* Main Content */
        .cart-container { max-width: 1400px; margin: 0 auto; padding: 32px 20px; }
        .cart-grid { display: grid; grid-template-columns: 1fr 400px; gap: 24px; align-items: start; }
        
        /* Empty Cart */
        .empty-cart { background: #fff; border-radius: 16px; padding: 60px 40px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .empty-cart i { font-size: 80px; color: #d1d5db; margin-bottom: 20px; }
        .empty-cart h2 { font-size: 24px; font-weight: 900; color: #1a1a2e; margin: 0 0 12px; }
        .empty-cart p { font-size: 15px; color: #6b7280; margin: 0 0 24px; }
        .btn-shop { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 14px 32px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all .3s; }
        .btn-shop:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        
        /* Cart Items */
        .cart-items { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .cart-header-row { display: flex; align-items: center; padding: 16px; background: #f9fafb; border-radius: 10px; margin-bottom: 16px; font-weight: 700; font-size: 13px; color: #6b7280; }
        .cart-header-row .col-select { width: 50px; }
        .cart-header-row .col-product { flex: 1; }
        .cart-header-row .col-price { width: 120px; text-align: center; }
        .cart-header-row .col-quantity { width: 140px; text-align: center; }
        .cart-header-row .col-total { width: 120px; text-align: center; }
        .cart-header-row .col-action { width: 60px; text-align: center; }
        
        .cart-item { display: flex; align-items: center; padding: 20px 16px; border-bottom: 1px solid #f3f4f6; transition: all .3s; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item.unavailable { opacity: 0.5; background: #fef2f2; }
        .cart-item .col-select { width: 50px; }
        .cart-item .col-product { flex: 1; display: flex; gap: 16px; align-items: center; }
        .cart-item .col-price { width: 120px; text-align: center; }
        .cart-item .col-quantity { width: 140px; text-align: center; }
        .cart-item .col-total { width: 120px; text-align: center; }
        .cart-item .col-action { width: 60px; text-align: center; }
        
        .item-checkbox { width: 20px; height: 20px; accent-color: #f97316; cursor: pointer; }
        .item-checkbox:disabled { cursor: not-allowed; }
        
        .item-image { width: 80px; height: 80px; border-radius: 10px; overflow: hidden; background: #f9fafb; flex-shrink: 0; }
        .item-image img { width: 100%; height: 100%; object-fit: cover; }
        .item-image .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 32px; }
        
        .item-details { flex: 1; min-width: 0; }
        .item-name { font-size: 15px; font-weight: 700; color: #1a1a2e; margin: 0 0 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .item-stock { font-size: 12px; color: #6b7280; margin: 0; }
        .item-stock.in-stock { color: #059669; }
        .item-stock.out-stock { color: #dc2626; }
        .item-unavailable { font-size: 12px; color: #dc2626; font-weight: 600; margin: 4px 0 0; }
        
        .item-price { font-size: 16px; font-weight: 800; color: #1a1a2e; }
        
        .quantity-control { display: flex; align-items: center; gap: 0; background: #f9fafb; border-radius: 8px; border: 2px solid #e5e7eb; overflow: hidden; width: fit-content; margin: 0 auto; }
        .qty-btn { background: transparent; border: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #1a1a2e; transition: all .3s; }
        .qty-btn:hover:not(:disabled) { background: #f97316; color: #fff; }
        .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .qty-input { width: 50px; text-align: center; border: none; background: transparent; font-size: 14px; font-weight: 700; color: #1a1a2e; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; height: 36px; }
        
        .item-total { font-size: 18px; font-weight: 900; color: #f97316; }
        
        .btn-remove { background: #fee2e2; color: #dc2626; border: none; width: 36px; height: 36px; border-radius: 8px; cursor: pointer; transition: all .3s; }
        .btn-remove:hover { background: #fecaca; transform: scale(1.1); }
        
        .cart-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f3f4f6; }
        .select-all-wrapper { display: flex; align-items: center; gap: 8px; }
        .select-all-wrapper input { width: 20px; height: 20px; accent-color: #f97316; cursor: pointer; }
        .select-all-wrapper label { font-size: 14px; font-weight: 600; color: #1a1a2e; cursor: pointer; }
        .btn-clear-cart { background: #fee2e2; color: #dc2626; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all .3s; }
        .btn-clear-cart:hover { background: #fecaca; }
        
        /* Order Summary */
        .order-summary { background: #fff; border-radius: 16px; padding: 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); position: sticky; top: 100px; }
        .summary-header { font-size: 20px; font-weight: 900; color: #1a1a2e; margin: 0 0 20px; padding-bottom: 16px; border-bottom: 2px solid #f3f4f6; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; font-size: 14px; }
        .summary-row .label { color: #6b7280; font-weight: 600; }
        .summary-row .value { color: #1a1a2e; font-weight: 700; }
        .summary-row.total { border-top: 2px solid #f3f4f6; margin-top: 12px; padding-top: 16px; font-size: 20px; font-weight: 900; }
        .summary-row.total .value { color: #f97316; font-size: 24px; }
        .summary-info { background: #fff5f0; border: 1px solid rgba(249,115,22,0.2); border-radius: 10px; padding: 12px; margin: 16px 0; font-size: 13px; color: #6b7280; text-align: center; }
        .btn-checkout { width: 100%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; border: none; padding: 16px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; transition: all .3s; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
        .btn-checkout:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(249,115,22,0.4); }
        .btn-checkout:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .cart-grid { grid-template-columns: 1fr; }
            .order-summary { position: static; }
        }
        
        @media (max-width: 768px) {
            .cart-container { padding: 20px 12px; }
            .cart-items { padding: 16px; }
            .cart-header-row { display: none; }
            
            .cart-item { 
                flex-direction: column; 
                padding: 16px; 
                gap: 16px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                margin-bottom: 12px;
                background: #fff;
            }
            
            .cart-item .col-select { 
                width: 100%; 
                display: flex;
                justify-content: flex-end;
            }
            
            .cart-item .col-product { 
                width: 100%;
                flex-direction: row;
                gap: 12px;
            }
            
            .item-image { 
                width: 100px; 
                height: 100px;
            }
            
            .cart-item .col-price,
            .cart-item .col-quantity,
            .cart-item .col-total,
            .cart-item .col-action { 
                width: 100%; 
            }
            
            .cart-item .col-price,
            .cart-item .col-total {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .cart-item .col-price::before {
                content: 'Price:';
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
            }
            
            .cart-item .col-total::before {
                content: 'Total:';
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
            }
            
            .cart-item .col-quantity {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .cart-item .col-quantity::before {
                content: 'Quantity:';
                font-size: 13px;
                font-weight: 600;
                color: #6b7280;
            }
            
            .quantity-control { margin: 0; }
            
            .cart-item .col-action {
                display: flex;
                justify-content: center;
                padding-top: 12px;
                border-top: 1px solid #f3f4f6;
            }
            
            .btn-remove {
                width: 100%;
                height: 44px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                font-size: 14px;
                font-weight: 600;
            }
            
            .btn-remove::after {
                content: 'Remove Item';
            }
            
            .cart-actions { 
                flex-direction: column; 
                gap: 12px; 
                align-items: stretch; 
            }
            
            .btn-clear-cart {
                width: 100%;
                padding: 14px;
                font-size: 15px;
            }
            
            .order-summary {
                margin-top: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .header-content { padding: 12px 12px; }
            .header-title h1 { font-size: 20px; }
            .header-title p { font-size: 12px; }
            .btn-back { width: 40px; height: 40px; }
            
            .cart-items { padding: 12px; }
            .cart-item { padding: 12px; }
            
            .item-image { width: 80px; height: 80px; }
            .item-name { font-size: 14px; }
            
            .order-summary { padding: 20px; }
            .summary-header { font-size: 18px; }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="cart-header">
    <div class="header-content">
        <div class="header-left">
            <a href="<?= SITE_URL ?>/index.php" class="btn-back" title="Continue Shopping">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="header-title">
                <h1>Shopping Cart</h1>
                <p><?= count($cart_items) ?> item(s) in your cart</p>
            </div>
        </div>
    </div>
</header>

<!-- Main Content -->
<div class="cart-container">
    <?php if (empty($cart_items)): ?>
        <!-- Empty Cart -->
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= SITE_URL ?>/index.php" class="btn-shop">
                <i class="fas fa-shopping-bag"></i> Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="cart-grid">
            <!-- Cart Items -->
            <div class="cart-items">
                <div class="cart-header-row">
                    <div class="col-select">SELECT</div>
                    <div class="col-product">PRODUCT</div>
                    <div class="col-price">PRICE</div>
                    <div class="col-quantity">QUANTITY</div>
                    <div class="col-total">TOTAL</div>
                    <div class="col-action">ACTION</div>
                </div>
                
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item <?= !$item['available'] ? 'unavailable' : '' ?>" data-product-id="<?= $item['product_id'] ?>">
                        <div class="col-select">
                            <input type="checkbox" 
                                   class="item-checkbox" 
                                   data-product-id="<?= $item['product_id'] ?>"
                                   data-price="<?= $item['price'] ?>"
                                   data-quantity="<?= $item['quantity'] ?>"
                                   <?= $item['available'] ? '' : 'disabled' ?>
                                   <?= ($buy_now_product == $item['product_id'] && $item['available']) ? 'checked' : '' ?>>
                        </div>
                        
                        <div class="col-product">
                            <div class="item-image">
                                <?php if ($item['image_path']): ?>
                                    <img src="<?= UPLOAD_URL . 'products/' . $item['image_path'] ?>" alt="<?= e($item['name']) ?>">
                                <?php else: ?>
                                    <div class="no-img"><i class="fas fa-box-open"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="item-details">
                                <h3 class="item-name"><?= e($item['name']) ?></h3>
                                <?php if ($item['available']): ?>
                                    <p class="item-stock in-stock">
                                        <i class="fas fa-check-circle"></i> <?= $item['stock'] ?> in stock
                                    </p>
                                <?php else: ?>
                                    <p class="item-stock out-stock">
                                        <i class="fas fa-times-circle"></i> Out of stock
                                    </p>
                                    <p class="item-unavailable">This item is currently unavailable</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-price">
                            <div class="item-price"><?= formatCurrency($item['price']) ?></div>
                        </div>
                        
                        <div class="col-quantity">
                            <div class="quantity-control">
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['product_id'] ?>, -1)" <?= !$item['available'] ? 'disabled' : '' ?>>
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" 
                                       class="qty-input" 
                                       value="<?= $item['quantity'] ?>" 
                                       min="1" 
                                       max="<?= $item['stock'] ?>"
                                       data-product-id="<?= $item['product_id'] ?>"
                                       readonly>
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['product_id'] ?>, 1)" <?= !$item['available'] ? 'disabled' : '' ?>>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-total">
                            <div class="item-total" data-product-id="<?= $item['product_id'] ?>">
                                <?= formatCurrency($item['item_total']) ?>
                            </div>
                        </div>
                        
                        <div class="col-action">
                            <button class="btn-remove" onclick="removeItem(<?= $item['product_id'] ?>)" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-actions">
                    <div class="select-all-wrapper">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <label for="selectAll">Select All Available Items</label>
                    </div>
                    <button class="btn-clear-cart" onclick="clearCart()">
                        <i class="fas fa-trash-alt"></i> Clear Cart
                    </button>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary">
                <h3 class="summary-header">Order Summary</h3>
                
                <div class="summary-row">
                    <span class="label">Selected Items:</span>
                    <span class="value" id="selectedCount">0</span>
                </div>
                
                <div class="summary-row total">
                    <span class="label">Total:</span>
                    <span class="value" id="totalAmount"><?= formatCurrency(0) ?></span>
                </div>
                
                <div class="summary-info">
                    <i class="fas fa-info-circle"></i> Select items to see total amount
                </div>
                
                <button class="btn-checkout" id="checkoutBtn" onclick="proceedToCheckout()" disabled>
                    <i class="fas fa-lock"></i> Proceed to Checkout
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?= SITE_URL ?>/assets/js/popup.js"></script>
<script>
// Calculate and update totals
function updateTotals() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked:not(:disabled)');
    let total = 0;
    let count = 0;
    
    checkboxes.forEach(checkbox => {
        const productId = checkbox.dataset.productId;
        const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
        const quantity = parseInt(qtyInput.value);
        const price = parseFloat(checkbox.dataset.price);
        total += price * quantity;
        count++;
    });
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('totalAmount').textContent = formatCurrency(total);
    document.getElementById('checkoutBtn').disabled = count === 0;
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-RW', {
        style: 'currency',
        currency: 'RWF',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount).replace('RWF', 'FRw');
}

// Update quantity
function updateQuantity(productId, change) {
    const qtyInput = document.querySelector(`.qty-input[data-product-id="${productId}"]`);
    const currentQty = parseInt(qtyInput.value);
    const maxQty = parseInt(qtyInput.max);
    const newQty = currentQty + change;
    
    if (newQty < 1 || newQty > maxQty) return;
    
    // Update via API
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', productId);
    formData.append('quantity', newQty);
    
    fetch('<?= SITE_URL ?>/api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            qtyInput.value = newQty;
            
            // Update checkbox data
            const checkbox = document.querySelector(`.item-checkbox[data-product-id="${productId}"]`);
            checkbox.dataset.quantity = newQty;
            
            // Update item total
            const price = parseFloat(checkbox.dataset.price);
            const itemTotal = price * newQty;
            document.querySelector(`.item-total[data-product-id="${productId}"]`).textContent = formatCurrency(itemTotal);
            
            // Update totals
            updateTotals();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update quantity', 'error');
    });
}

// Remove item
function removeItem(productId) {
    showPopup({
        type: 'warning',
        icon: 'fa-exclamation-triangle',
        title: 'Remove Item',
        message: 'Are you sure you want to remove this item from your cart?',
        confirmText: 'Yes, Remove',
        cancelText: 'Cancel',
        showCancel: true,
        onConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('product_id', productId);
            
            fetch('<?= SITE_URL ?>/api/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const item = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                    item.remove();
                    
                    // Check if cart is empty
                    const remainingItems = document.querySelectorAll('.cart-item');
                    if (remainingItems.length === 0) {
                        location.reload();
                    }
                    
                    updateTotals();
                    showToast('Item removed from cart', 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to remove item', 'error');
            });
        }
    });
}

// Clear cart
function clearCart() {
    showPopup({
        type: 'warning',
        icon: 'fa-exclamation-triangle',
        title: 'Clear Cart',
        message: 'Are you sure you want to remove all items from your cart?',
        confirmText: 'Yes, Clear All',
        cancelText: 'Cancel',
        showCancel: true,
        onConfirm: () => {
            const formData = new FormData();
            formData.append('action', 'clear');
            
            fetch('<?= SITE_URL ?>/api/cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to clear cart', 'error');
            });
        }
    });
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.item-checkbox:not(:disabled)');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateTotals();
}

// Proceed to checkout
function proceedToCheckout() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked:not(:disabled)');
    if (checkboxes.length === 0) {
        showToast('Please select at least one item', 'warning');
        return;
    }
    
    // Get selected product IDs
    const selectedIds = Array.from(checkboxes).map(cb => cb.dataset.productId);
    
    // Redirect to checkout with selected items
    window.location.href = '<?= SITE_URL ?>/checkout.php?items=' + selectedIds.join(',');
}

// Listen to checkbox changes
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('item-checkbox')) {
        updateTotals();
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTotals();
});
</script>

</body>
</html>
