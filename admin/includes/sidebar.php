<?php
// requires $conn, $admin, $stats to be set before including
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <img src="../logo/logo.jpg" alt="<?= e(SITE_NAME) ?>">
        <button class="sidebar-close" onclick="closeSidebar()">
            <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <nav class="sidebar-nav">

        <div class="nav-label">Main</div>
        <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </span>
            Dashboard
        </a>

        <div class="nav-label">E-Commerce</div>
        <a href="products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </span>
            Products
        </a>
        <a href="orders.php" class="<?= $current_page === 'orders.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </span>
            Orders
            <?php if (!empty($stats['pending_orders']) && $stats['pending_orders'] > 0): ?>
                <span class="nav-badge"><?= $stats['pending_orders'] ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Real Estate</div>
        <a href="properties.php" class="<?= $current_page === 'properties.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            Properties
        </a>
        <a href="property-orders.php" class="<?= $current_page === 'property-orders.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </span>
            Property Orders
            <?php if (!empty($stats['pending_prop_orders']) && $stats['pending_prop_orders'] > 0): ?>
                <span class="nav-badge"><?= $stats['pending_prop_orders'] ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Car Rental</div>
        <a href="vehicles.php" class="<?= $current_page === 'vehicles.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </span>
            Vehicles
        </a>
        <a href="bookings.php" class="<?= $current_page === 'bookings.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            Bookings
            <?php if (!empty($stats['pending_bookings']) && $stats['pending_bookings'] > 0): ?>
                <span class="nav-badge"><?= $stats['pending_bookings'] ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-label">Management</div>
        <a href="customers.php" class="<?= $current_page === 'customers.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            Customers
        </a>
        <a href="categories.php" class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            </span>
            Categories
        </a>
        <a href="payment-methods.php" class="<?= $current_page === 'payment-methods.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </span>
            Payment Methods
        </a>
        <a href="banners.php" class="<?= $current_page === 'banners.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </span>
            Banners
        </a>
        <a href="reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </span>
            Reviews
            <?php if (!empty($stats['pending_reviews']) && $stats['pending_reviews'] > 0): ?>
                <span class="nav-badge"><?= $stats['pending_reviews'] ?></span>
            <?php endif; ?>
        </a>
        <a href="admins.php" class="<?= $current_page === 'admins.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
            Admins
        </a>
        <a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </span>
            Settings
        </a>

    </nav>

    <!-- Admin info at bottom -->
    <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
        <div class="sidebar-user-info">
            <strong><?= e($admin['name']) ?></strong>
            <span>Administrator</span>
        </div>
        <a href="logout.php" class="sidebar-logout" title="Sign Out">
            <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>

</aside>
