<?php
// Mobile responsive navigation - Used with header.php
// This file handles mobile menu, mobile search, and mobile user interactions
?>

<style>
    /* ===== MOBILE NAVIGATION ===== */
    .mobile-menu {
        display: none;
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--white);
        z-index: 99;
        overflow-y: auto;
        animation: slideDown .3s ease;
    }

    .mobile-menu.active {
        display: block;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Mobile Search */
    .mobile-search {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
    }

    .mobile-search-form {
        display: flex;
        align-items: center;
        background: var(--bg);
        border-radius: var(--radius);
        padding: 8px 12px;
        gap: 8px;
    }

    .mobile-search-input {
        flex: 1;
        background: none;
        border: none;
        outline: none;
        font-size: 14px;
        color: var(--text);
    }

    .mobile-search-input::placeholder {
        color: var(--text-muted);
    }

    .mobile-search-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mobile-search-btn svg {
        width: 18px;
        height: 18px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
    }

    /* Mobile Menu Items */
    .mobile-nav {
        padding: 12px 0;
    }

    .mobile-nav-item {
        display: block;
        padding: 14px 16px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        border-bottom: 1px solid #f3f4f6;
        transition: all .2s;
    }

    .mobile-nav-item:hover {
        background: var(--bg);
        color: var(--primary);
        padding-left: 20px;
    }

    /* Mobile User Section */
    .mobile-user-section {
        padding: 16px;
        border-top: 1px solid var(--border);
    }

    .mobile-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .mobile-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .mobile-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .mobile-user-details {
        flex: 1;
        min-width: 0;
    }

    .mobile-user-name {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mobile-user-email {
        font-size: 12px;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Mobile Menu Links */
    .mobile-menu-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mobile-menu-link {
        display: block;
        padding: 12px 16px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
        border-radius: var(--radius);
        transition: all .2s;
    }

    .mobile-menu-link:hover {
        background: var(--bg);
        color: var(--primary);
    }

    .mobile-menu-link.logout {
        color: #ef4444;
    }

    .mobile-menu-link.logout:hover {
        background: #fee2e2;
    }

    /* Mobile Auth Buttons */
    .mobile-auth-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mobile-btn {
        padding: 10px 16px;
        border-radius: var(--radius);
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all .2s;
        text-align: center;
    }

    .mobile-btn-outline {
        background: var(--white);
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .mobile-btn-outline:hover {
        background: #fff7ed;
    }

    .mobile-btn-primary {
        background: var(--primary);
        color: var(--white);
    }

    .mobile-btn-primary:hover {
        background: var(--primary-dark);
    }

    /* Mobile Overlay */
    .mobile-overlay {
        display: none;
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,.3);
        z-index: 98;
    }

    .mobile-overlay.active {
        display: block;
    }

    @media (min-width: 769px) {
        .mobile-menu,
        .mobile-overlay {
            display: none !important;
        }
    }
</style>

<!-- Mobile Menu -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="mobile-menu" id="mobileMenu">
    
    <!-- Mobile Search -->
    <div class="mobile-search">
        <form class="mobile-search-form" action="<?= SITE_URL ?>/search.php" method="GET">
            <input type="text" name="q" class="mobile-search-input" placeholder="Search..." required>
            <button type="submit" class="mobile-search-btn" aria-label="Search">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </form>
    </div>

    <!-- Mobile Navigation -->
    <nav class="mobile-nav">
        <a href="<?= SITE_URL ?>/products.php" class="mobile-nav-item">🛍️ Products</a>
        <a href="<?= SITE_URL ?>/properties.php" class="mobile-nav-item">🏠 Properties</a>
        <a href="<?= SITE_URL ?>/vehicles.php" class="mobile-nav-item">🚗 Vehicles</a>
        <a href="<?= SITE_URL ?>/about.php" class="mobile-nav-item">About</a>
        <a href="<?= SITE_URL ?>/contact.php" class="mobile-nav-item">Contact</a>
    </nav>

    <!-- Mobile User Section -->
    <div class="mobile-user-section">
        <?php if ($is_logged_in): ?>
            <!-- Logged In User -->
            <div class="mobile-user-info">
                <div class="mobile-user-avatar">
                    <?php if (!empty($customer['profile_image'])): ?>
                        <img src="<?= UPLOAD_URL ?>profiles/<?= e($customer['profile_image']) ?>" alt="<?= e($customer['name']) ?>">
                    <?php else: ?>
                        <span><?= strtoupper(substr($customer['name'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div class="mobile-user-details">
                    <div class="mobile-user-name"><?= e($customer['name']) ?></div>
                    <div class="mobile-user-email"><?= e($customer['email']) ?></div>
                </div>
            </div>

            <div class="mobile-menu-links">
                <a href="<?= SITE_URL ?>/profile.php" class="mobile-menu-link">👤 My Profile</a>
                <a href="<?= SITE_URL ?>/my-orders.php" class="mobile-menu-link">📦 My Orders</a>
                <a href="<?= SITE_URL ?>/my-bookings.php" class="mobile-menu-link">🚗 My Bookings</a>
                <a href="<?= SITE_URL ?>/wishlist.php" class="mobile-menu-link">❤️ Wishlist</a>
                <a href="<?= SITE_URL ?>/logout.php" class="mobile-menu-link logout">🚪 Logout</a>
            </div>
        <?php else: ?>
            <!-- Not Logged In -->
            <div class="mobile-auth-buttons">
                <a href="<?= SITE_URL ?>/login.php" class="mobile-btn mobile-btn-outline">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="mobile-btn mobile-btn-primary">Register</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
    // Mobile menu toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
        });
    }

    // Close menu when clicking overlay
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
        });
    }

    // Close menu when clicking a link
    document.querySelectorAll('.mobile-nav-item, .mobile-menu-link').forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
        });
    });

    // Update cart count
    function updateCartCount() {
        fetch('<?= SITE_URL ?>/api/cart-count.php')
            .then(r => r.json())
            .then(d => {
                const cartBadge = document.getElementById('cartCount');
                if (cartBadge) {
                    cartBadge.textContent = d.count || 0;
                }
            })
            .catch(() => {});
    }

    updateCartCount();
</script>
