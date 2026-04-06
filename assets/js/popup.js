/**
 * Zuba Online Market - Popup Notification System
 * Reusable popup for confirmations, alerts, and notifications
 */

// Show popup notification
function showPopup(options) {
    const {
        type = 'info',
        icon,
        title,
        message,
        confirmText = 'OK',
        cancelText = 'Cancel',
        showCancel = false,
        onConfirm,
        onCancel
    } = options;

    // Remove existing popup if any
    const existingPopup = document.querySelector('.popup-overlay');
    if (existingPopup) existingPopup.remove();

    // Icon mapping
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
        question: 'fa-question-circle',
        heart: 'fa-heart'
    };

    const iconClass = icon || icons[type] || icons.info;

    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'popup-overlay';
    overlay.innerHTML = `
        <div class="popup-box popup-${type}">
            <button class="popup-close" onclick="closePopup(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="popup-icon">
                <i class="fas ${iconClass}"></i>
            </div>
            <h3 class="popup-title">${title}</h3>
            <p class="popup-message">${message}</p>
            <div class="popup-actions">
                ${showCancel ? `<button class="popup-btn popup-btn-cancel" onclick="closePopup(this, 'cancel')">${cancelText}</button>` : ''}
                <button class="popup-btn popup-btn-confirm" onclick="closePopup(this, 'confirm')">${confirmText}</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Store callbacks
    if (onConfirm) overlay._onConfirm = onConfirm;
    if (onCancel) overlay._onCancel = onCancel;

    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closePopup(overlay, 'cancel');
    });

    // Animate in
    setTimeout(() => overlay.classList.add('show'), 10);

    return overlay;
}

// Close popup
function closePopup(element, action = 'close') {
    const overlay = element.closest ? element.closest('.popup-overlay') : element;

    // Execute callbacks
    if (action === 'confirm' && overlay._onConfirm) {
        overlay._onConfirm();
    } else if (action === 'cancel' && overlay._onCancel) {
        overlay._onCancel();
    }

    // Animate out
    overlay.classList.remove('show');
    setTimeout(() => overlay.remove(), 300);
}

// Show toast notification (quick message)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-times-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add wishlist functionality
function toggleWishlist(itemType, itemId, buttonElement) {
    const icon = buttonElement.querySelector('i');
    const isAdding = icon.classList.contains('far');

    fetch('/zuba-online-market/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            item_type: itemType,
            item_id: itemId,
            action: isAdding ? 'add' : 'remove'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Toggle icon
            icon.classList.toggle('far');
            icon.classList.toggle('fas');
            buttonElement.classList.toggle('active');
            
            // Show notification
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(() => {
        showToast('An error occurred', 'error');
    });
}
