<?php
/**
 * ============================================
 * ZUBA ONLINE MARKET - CONSTANTS & HELPERS
 * ============================================
 * Helper arrays and lookup tables for the application
 * Extends config.php with additional constants
 */

// ===== HELPER FUNCTION: Get label from constant array =====
if (!function_exists('getLabel')) {
    function getLabel($value, $array) {
        return $array[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }
}

// ===== HELPER FUNCTION: Get all values from constant array =====
if (!function_exists('getValues')) {
    function getValues($array) {
        return array_keys($array);
    }
}

// ===== HELPER FUNCTION: Get all labels from constant array =====
if (!function_exists('getLabels')) {
    function getLabels($array) {
        return array_values($array);
    }
}

// ===== BADGE COLOR MAPPING =====
define('STATUS_BADGE_COLORS', [
    'active' => 'success',
    'available' => 'success',
    'approved' => 'success',
    'completed' => 'success',
    'delivered' => 'success',
    'inactive' => 'secondary',
    'pending_payment' => 'warning',
    'payment_submitted' => 'info',
    'processing' => 'primary',
    'shipped' => 'primary',
    'rented' => 'primary',
    'rejected' => 'danger',
    'cancelled' => 'danger',
    'suspended' => 'danger',
    'out_of_stock' => 'danger',
    'maintenance' => 'warning',
    'sold' => 'secondary',
    'pending' => 'warning'
]);

// ===== MODULE TYPES =====
define('MODULES', [
    'ecommerce' => [
        'name' => 'E-Commerce',
        'icon' => '🛍️',
        'description' => 'Products & Shopping',
        'tables' => ['products', 'product_images', 'cart', 'orders', 'order_items']
    ],
    'realestate' => [
        'name' => 'Real Estate',
        'icon' => '🏠',
        'description' => 'Properties for Sale & Rent',
        'tables' => ['properties', 'property_images', 'property_orders']
    ],
    'carrental' => [
        'name' => 'Car Rental',
        'icon' => '🚗',
        'description' => 'Vehicle Rentals',
        'tables' => ['vehicles', 'vehicle_images', 'bookings']
    ]
]);

// ===== ADMIN PERMISSIONS =====
define('ADMIN_PERMISSIONS', [
    'manage_products' => 'Manage Products',
    'manage_properties' => 'Manage Properties',
    'manage_vehicles' => 'Manage Vehicles',
    'manage_orders' => 'Manage Orders',
    'manage_bookings' => 'Manage Bookings',
    'manage_property_orders' => 'Manage Property Orders',
    'manage_customers' => 'Manage Customers',
    'manage_categories' => 'Manage Categories',
    'manage_payment_methods' => 'Manage Payment Methods',
    'manage_reviews' => 'Manage Reviews',
    'manage_banners' => 'Manage Banners',
    'manage_admins' => 'Manage Admins',
    'view_analytics' => 'View Analytics',
    'manage_settings' => 'Manage Settings'
]);

// ===== SUPER ADMIN DEFAULT PERMISSIONS =====
define('SUPER_ADMIN_PERMISSIONS', array_keys(ADMIN_PERMISSIONS));

// ===== BASIC ADMIN DEFAULT PERMISSIONS =====
define('BASIC_ADMIN_PERMISSIONS', [
    'manage_products',
    'manage_properties',
    'manage_vehicles',
    'manage_orders',
    'manage_bookings',
    'manage_property_orders',
    'manage_reviews'
]);

// ===== VALIDATION RULES =====
define('VALIDATION_RULES', [
    'email' => '/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
    'phone' => '/^[\d\s\-\+\(\)]{7,}$/',
    'url' => '/^https?:\/\/.+/',
    'slug' => '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
    'username' => '/^[a-zA-Z0-9_]{3,20}$/'
]);

// ===== FIELD LENGTH LIMITS =====
define('FIELD_LIMITS', [
    'name' => 100,
    'email' => 100,
    'phone' => 20,
    'title' => 200,
    'description' => 5000,
    'slug' => 200,
    'sku' => 50,
    'brand' => 100,
    'color' => 50,
    'address' => 255,
    'city' => 100,
    'state' => 100,
    'country' => 100,
    'zip_code' => 20,
    'password' => 255
]);

// ===== SEARCH FILTERS =====
define('SEARCH_FILTERS', [
    'products' => ['category', 'brand', 'condition', 'price_min', 'price_max', 'status'],
    'properties' => ['category', 'listing_type', 'property_type', 'price_min', 'price_max', 'bedrooms', 'bathrooms', 'city', 'status'],
    'vehicles' => ['category', 'vehicle_type', 'brand', 'price_min', 'price_max', 'fuel_type', 'transmission', 'status'],
    'orders' => ['status', 'customer', 'date_from', 'date_to', 'amount_min', 'amount_max'],
    'bookings' => ['status', 'customer', 'date_from', 'date_to', 'amount_min', 'amount_max'],
    'customers' => ['status', 'date_from', 'date_to']
]);

// ===== SORT OPTIONS =====
define('SORT_OPTIONS', [
    'newest' => 'Newest First',
    'oldest' => 'Oldest First',
    'price_low' => 'Price: Low to High',
    'price_high' => 'Price: High to Low',
    'name_asc' => 'Name: A to Z',
    'name_desc' => 'Name: Z to A',
    'popular' => 'Most Popular',
    'rating' => 'Highest Rated'
]);

// ===== PRICE RANGES =====
define('PRICE_RANGES', [
    '0-100000' => 'FRw 0 - 100,000',
    '100000-500000' => 'FRw 100,000 - 500,000',
    '500000-1000000' => 'FRw 500,000 - 1,000,000',
    '1000000-5000000' => 'FRw 1,000,000 - 5,000,000',
    '5000000+' => 'FRw 5,000,000+'
]);

// ===== DASHBOARD STATS QUERIES =====
define('DASHBOARD_STATS', [
    'total_products' => "SELECT COUNT(*) FROM products",
    'total_properties' => "SELECT COUNT(*) FROM properties",
    'total_vehicles' => "SELECT COUNT(*) FROM vehicles",
    'total_customers' => "SELECT COUNT(*) FROM users",
    'total_orders' => "SELECT COUNT(*) FROM orders",
    'pending_orders' => "SELECT COUNT(*) FROM orders WHERE status = 'payment_submitted'",
    'total_bookings' => "SELECT COUNT(*) FROM bookings",
    'pending_bookings' => "SELECT COUNT(*) FROM bookings WHERE status = 'payment_submitted'",
    'total_prop_orders' => "SELECT COUNT(*) FROM property_orders",
    'pending_prop_orders' => "SELECT COUNT(*) FROM property_orders WHERE status = 'payment_submitted'",
    'pending_reviews' => "SELECT COUNT(*) FROM reviews WHERE status = 'pending'"
]);

// ===== REVENUE QUERIES =====
define('REVENUE_QUERIES', [
    'ecommerce' => "SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status NOT IN ('pending_payment','rejected','cancelled')",
    'realestate' => "SELECT COALESCE(SUM(amount),0) FROM property_orders WHERE status NOT IN ('pending_payment','rejected','cancelled')",
    'carrental' => "SELECT COALESCE(SUM(total_amount),0) FROM bookings WHERE status NOT IN ('pending_payment','rejected','cancelled')"
]);

// ===== EMAIL TEMPLATES =====
define('EMAIL_TEMPLATES', [
    'order_confirmation' => 'Order Confirmation',
    'order_approved' => 'Order Approved',
    'order_rejected' => 'Order Rejected',
    'booking_confirmation' => 'Booking Confirmation',
    'booking_approved' => 'Booking Approved',
    'booking_rejected' => 'Booking Rejected',
    'property_order_confirmation' => 'Property Order Confirmation',
    'property_order_approved' => 'Property Order Approved',
    'property_order_rejected' => 'Property Order Rejected',
    'welcome' => 'Welcome Email',
    'password_reset' => 'Password Reset',
    'contact_reply' => 'Contact Form Reply'
]);

// ===== NOTIFICATION TYPES =====
define('NOTIFICATION_TYPES', [
    'order_status' => 'Order Status Update',
    'booking_status' => 'Booking Status Update',
    'property_order_status' => 'Property Order Status Update',
    'new_review' => 'New Review',
    'payment_received' => 'Payment Received',
    'system_alert' => 'System Alert'
]);

// ===== EXPORT FORMATS =====
define('EXPORT_FORMATS', [
    'csv' => 'CSV',
    'excel' => 'Excel',
    'pdf' => 'PDF',
    'json' => 'JSON'
]);

// ===== CHART TYPES =====
define('CHART_TYPES', [
    'line' => 'Line Chart',
    'bar' => 'Bar Chart',
    'pie' => 'Pie Chart',
    'doughnut' => 'Doughnut Chart',
    'area' => 'Area Chart'
]);

// ===== TIME PERIODS FOR ANALYTICS =====
define('TIME_PERIODS', [
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'this_week' => 'This Week',
    'last_week' => 'Last Week',
    'this_month' => 'This Month',
    'last_month' => 'Last Month',
    'this_year' => 'This Year',
    'last_year' => 'Last Year',
    'custom' => 'Custom Range'
]);

// ===== CACHE KEYS =====
define('CACHE_KEYS', [
    'featured_products' => 'featured_products',
    'featured_properties' => 'featured_properties',
    'featured_vehicles' => 'featured_vehicles',
    'categories_ecommerce' => 'categories_ecommerce',
    'categories_realestate' => 'categories_realestate',
    'categories_carrental' => 'categories_carrental',
    'payment_methods' => 'payment_methods',
    'site_settings' => 'site_settings',
    'banners' => 'banners'
]);

// ===== CACHE DURATION (in seconds) =====
define('CACHE_DURATION', [
    'featured_products' => 3600, // 1 hour
    'categories' => 86400, // 24 hours
    'payment_methods' => 86400, // 24 hours
    'site_settings' => 86400, // 24 hours
    'banners' => 3600 // 1 hour
]);
