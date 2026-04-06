<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup System Test - Zuba Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/popup.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 32px; font-weight: 900; color: #1a1a2e; margin-bottom: 12px; }
        p { color: #666; margin-bottom: 32px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .btn { padding: 14px 20px; border: none; border-radius: 12px; font-size: 15px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s; }
        .btn-success { background: #10b981; color: #fff; }
        .btn-error { background: #ef4444; color: #fff; }
        .btn-warning { background: #f59e0b; color: #fff; }
        .btn-info { background: #3b82f6; color: #fff; }
        .btn-question { background: #8b5cf6; color: #fff; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .section { background: #fff; padding: 24px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .section h2 { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Popup Notification System</h1>
        <p>Test the reusable popup and toast notification system</p>

        <div class="section">
            <h2>Popup Dialogs</h2>
            <div class="grid">
                <button class="btn btn-success" onclick="testSuccess()">
                    <i class="fas fa-check-circle"></i> Success
                </button>
                <button class="btn btn-error" onclick="testError()">
                    <i class="fas fa-times-circle"></i> Error
                </button>
                <button class="btn btn-warning" onclick="testWarning()">
                    <i class="fas fa-exclamation-triangle"></i> Warning
                </button>
                <button class="btn btn-info" onclick="testInfo()">
                    <i class="fas fa-info-circle"></i> Info
                </button>
                <button class="btn btn-question" onclick="testQuestion()">
                    <i class="fas fa-question-circle"></i> Question
                </button>
            </div>
        </div>

        <div class="section">
            <h2>Toast Notifications</h2>
            <div class="grid">
                <button class="btn btn-success" onclick="showToast('Operation successful!', 'success')">
                    <i class="fas fa-check"></i> Success Toast
                </button>
                <button class="btn btn-error" onclick="showToast('Something went wrong!', 'error')">
                    <i class="fas fa-times"></i> Error Toast
                </button>
                <button class="btn btn-warning" onclick="showToast('Please be careful!', 'warning')">
                    <i class="fas fa-exclamation"></i> Warning Toast
                </button>
                <button class="btn btn-info" onclick="showToast('Here is some information', 'info')">
                    <i class="fas fa-info"></i> Info Toast
                </button>
            </div>
        </div>

        <div class="section">
            <h2>Wishlist Example</h2>
            <button class="btn btn-error" onclick="testWishlist()" style="width: 100%;">
                <i class="far fa-heart"></i> Add to Wishlist
            </button>
        </div>
    </div>

    <script src="assets/js/popup.js"></script>
    <script>
        function testSuccess() {
            showPopup({
                type: 'success',
                title: 'Success!',
                message: 'Your operation completed successfully.',
                confirmText: 'Great!'
            });
        }

        function testError() {
            showPopup({
                type: 'error',
                title: 'Error Occurred',
                message: 'Something went wrong. Please try again.',
                confirmText: 'OK'
            });
        }

        function testWarning() {
            showPopup({
                type: 'warning',
                title: 'Warning',
                message: 'This action requires your attention.',
                confirmText: 'I Understand'
            });
        }

        function testInfo() {
            showPopup({
                type: 'info',
                title: 'Information',
                message: 'Here is some important information for you.',
                confirmText: 'Got It'
            });
        }

        function testQuestion() {
            showPopup({
                type: 'question',
                title: 'Are you sure?',
                message: 'Do you want to proceed with this action?',
                confirmText: 'Yes, Proceed',
                cancelText: 'Cancel',
                showCancel: true,
                onConfirm: () => {
                    showToast('Action confirmed!', 'success');
                },
                onCancel: () => {
                    showToast('Action cancelled', 'info');
                }
            });
        }

        function testWishlist() {
            showPopup({
                type: 'success',
                icon: 'fa-heart',
                title: 'Added to Wishlist!',
                message: 'This item has been added to your wishlist successfully.',
                confirmText: 'Continue Shopping',
                cancelText: 'View Wishlist',
                showCancel: true,
                onCancel: () => {
                    showToast('Redirecting to wishlist...', 'info');
                }
            });
        }
    </script>
</body>
</html>
