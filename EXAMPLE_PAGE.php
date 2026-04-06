<?php
/**
 * EXAMPLE PAGE TEMPLATE
 * Shows how to use header.php and navbar.php together
 * Copy this structure for all public website pages
 */

session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Set page title (used in header)
$page_title = 'Products';

// Get customer info if logged in
$customer = currentCustomer();
$is_logged_in = isCustomerLoggedIn();

// Include header (desktop navigation + opening tags)
require_once 'includes/header.php';
?>

    <!-- Page content goes here -->
    <div style="max-width: 1400px; margin: 0 auto; padding: 40px 16px;">
        <h1>Welcome to <?= e(SITE_NAME) ?></h1>
        <p>This is an example page showing how to use header.php and navbar.php</p>
    </div>

<?php
// Include navbar (mobile menu + closing tags)
require_once 'includes/navbar.php';
?>

    </main><!-- /main -->

    <footer style="background: #1a1a2e; color: #e5e7eb; padding: 40px 16px; text-align: center; margin-top: 60px;">
        <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
    </footer>

    <script>
        // User dropdown menu
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');

        if (userMenuBtn) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (userDropdown && !userDropdown.contains(e.target) && !userMenuBtn?.contains(e.target)) {
                userDropdown?.classList.remove('active');
            }
        });
    </script>

</body>
</html>
