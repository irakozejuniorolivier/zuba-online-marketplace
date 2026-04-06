<?php
/**
 * ============================================
 * ZUBA ONLINE MARKET - CONFIGURATION
 * ============================================
 * Central configuration file for the entire application
 * Used by both admin panel and public website
 */

// ===== SITE INFORMATION =====
define('SITE_NAME', 'Zuba Online Market');
define('SITE_DESCRIPTION', 'A comprehensive multi-vertical marketplace platform');
define('SITE_VERSION', '1.0.0');

// ===== AUTO-DETECT HOST =====
// Works on localhost AND any device on the network
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', 'http://' . $_host . '/zuba-online-market');
define('ADMIN_URL', SITE_URL . '/admin');

// ===== FILE PATHS =====
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/zuba-online-market/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Upload directories for each module
define('PRODUCT_UPLOAD_DIR', UPLOAD_PATH . 'products/');
define('PROPERTY_UPLOAD_DIR', UPLOAD_PATH . 'properties/');
define('VEHICLE_UPLOAD_DIR', UPLOAD_PATH . 'vehicles/');
define('PAYMENT_PROOF_DIR', UPLOAD_PATH . 'payment_proofs/');
define('PROFILE_UPLOAD_DIR', UPLOAD_PATH . 'profiles/');
define('BANNER_UPLOAD_DIR', UPLOAD_PATH . 'banners/');
define('CATEGORY_UPLOAD_DIR', UPLOAD_PATH . 'categories/');
define('PAYMENT_METHOD_UPLOAD_DIR', UPLOAD_PATH . 'payment_methods/');

// ===== CURRENCY & LOCALIZATION =====
define('CURRENCY', 'RWF');
define('CURRENCY_SYMBOL', 'FRw');
define('TIMEZONE', 'Africa/Kigali');
define('DATE_FORMAT', 'd M Y');
define('DATETIME_FORMAT', 'd M Y, H:i');

// ===== PAGINATION & DISPLAY =====
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);
define('SEARCH_RESULTS_PER_PAGE', 12);

// ===== FILE UPLOAD SETTINGS =====
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// ===== PRODUCT SETTINGS =====
define('PRODUCT_CONDITIONS', [
    'new' => 'New',
    'like_new' => 'Like New',
    'used' => 'Used',
    'refurbished' => 'Refurbished'
]);
define('PRODUCT_STATUSES', ['active', 'inactive']);

// ===== PROPERTY SETTINGS =====
define('PROPERTY_TYPES', [
    'apartment' => 'Apartment',
    'house' => 'House',
    'villa' => 'Villa',
    'commercial' => 'Commercial',
    'land' => 'Land',
    'office' => 'Office'
]);
define('LISTING_TYPES', ['sale' => 'For Sale', 'rent' => 'For Rent']);
define('RENT_PERIODS', [
    'day' => 'Per Day',
    'week' => 'Per Week',
    'month' => 'Per Month',
    'year' => 'Per Year'
]);
define('AREA_UNITS', [
    'sqm' => 'Square Meters (sqm)',
    'sqft' => 'Square Feet (sqft)',
    'acres' => 'Acres',
    'hectares' => 'Hectares'
]);
define('PROPERTY_STATUSES', ['active', 'inactive', 'sold', 'rented']);

// ===== VEHICLE SETTINGS =====
define('VEHICLE_TYPES', [
    'sedan' => 'Sedan',
    'suv' => 'SUV',
    'truck' => 'Truck',
    'van' => 'Van',
    'coupe' => 'Coupe',
    'convertible' => 'Convertible',
    'hatchback' => 'Hatchback',
    'minivan' => 'Minivan'
]);
define('TRANSMISSION_TYPES', ['manual' => 'Manual', 'automatic' => 'Automatic']);
define('FUEL_TYPES', [
    'petrol' => 'Petrol',
    'diesel' => 'Diesel',
    'electric' => 'Electric',
    'hybrid' => 'Hybrid'
]);
define('VEHICLE_STATUSES', ['available', 'rented', 'maintenance', 'inactive']);

// ===== ORDER & BOOKING STATUSES =====
define('ORDER_STATUSES', [
    'pending_payment' => 'Pending Payment',
    'payment_submitted' => 'Payment Submitted',
    'approved' => 'Approved',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'completed' => 'Completed',
    'rejected' => 'Rejected',
    'cancelled' => 'Cancelled'
]);

define('BOOKING_STATUSES', [
    'pending_payment' => 'Pending Payment',
    'payment_submitted' => 'Payment Submitted',
    'approved' => 'Approved',
    'processing' => 'Processing',
    'rented' => 'Rented',
    'completed' => 'Completed',
    'rejected' => 'Rejected',
    'cancelled' => 'Cancelled'
]);

define('PROPERTY_ORDER_STATUSES', [
    'pending_payment' => 'Pending Payment',
    'payment_submitted' => 'Payment Submitted',
    'approved' => 'Approved',
    'processing' => 'Processing',
    'completed' => 'Completed',
    'rejected' => 'Rejected',
    'cancelled' => 'Cancelled'
]);

// ===== PAYMENT SETTINGS =====
define('PAYMENT_METHOD_TYPES', ['mobile_money', 'bank', 'other']);
define('PAYMENT_STATUSES', ['active', 'inactive']);

// ===== REVIEW SETTINGS =====
define('REVIEW_STATUSES', ['pending', 'approved', 'rejected']);
define('REVIEW_RATINGS', [1, 2, 3, 4, 5]);

// ===== ADMIN SETTINGS =====
define('ADMIN_STATUSES', ['active', 'inactive', 'suspended']);
define('ADMIN_ROLES', ['super_admin', 'admin']);

// ===== CUSTOMER SETTINGS =====
define('CUSTOMER_STATUSES', ['active', 'inactive', 'suspended']);

// ===== BANNER SETTINGS =====
define('BANNER_POSITIONS', [
    'homepage_hero' => 'Homepage Hero',
    'homepage_top' => 'Homepage Top',
    'homepage_middle' => 'Homepage Middle',
    'homepage_bottom' => 'Homepage Bottom',
    'products_top' => 'Products Page Top',
    'properties_top' => 'Properties Page Top',
    'vehicles_top' => 'Vehicles Page Top'
]);
define('BANNER_PAGES', [
    'homepage' => 'Homepage',
    'products' => 'Products',
    'properties' => 'Properties',
    'vehicles' => 'Vehicles',
    'all' => 'All Pages'
]);
define('BANNER_STATUSES', ['active', 'inactive']);

// ===== CATEGORY SETTINGS =====
define('CATEGORY_TYPES', [
    'ecommerce' => 'E-Commerce',
    'realestate' => 'Real Estate',
    'carrental' => 'Car Rental'
]);
define('CATEGORY_STATUSES', ['active', 'inactive']);

// ===== ACTIVITY LOG SETTINGS =====
define('ACTIVITY_USER_TYPES', ['admin', 'customer']);
define('ACTIVITY_ACTIONS', [
    'LOGIN' => 'Login',
    'LOGOUT' => 'Logout',
    'ADD_PRODUCT' => 'Add Product',
    'EDIT_PRODUCT' => 'Edit Product',
    'DELETE_PRODUCT' => 'Delete Product',
    'ADD_PROPERTY' => 'Add Property',
    'EDIT_PROPERTY' => 'Edit Property',
    'DELETE_PROPERTY' => 'Delete Property',
    'ADD_VEHICLE' => 'Add Vehicle',
    'EDIT_VEHICLE' => 'Edit Vehicle',
    'DELETE_VEHICLE' => 'Delete Vehicle',
    'APPROVE_ORDER' => 'Approve Order',
    'REJECT_ORDER' => 'Reject Order',
    'APPROVE_BOOKING' => 'Approve Booking',
    'REJECT_BOOKING' => 'Reject Booking',
    'APPROVE_PROPERTY_ORDER' => 'Approve Property Order',
    'REJECT_PROPERTY_ORDER' => 'Reject Property Order',
    'ADD_ADMIN' => 'Add Admin',
    'EDIT_ADMIN' => 'Edit Admin',
    'DELETE_ADMIN' => 'Delete Admin',
    'MANAGE_CATEGORIES' => 'Manage Categories',
    'MANAGE_PAYMENT_METHODS' => 'Manage Payment Methods',
    'UPDATE_SETTINGS' => 'Update Settings'
]);

// ===== SITE SETTINGS KEYS =====
// These are used in the site_settings table
define('SITE_SETTINGS_KEYS', [
    'site_name' => 'Site Name',
    'site_description' => 'Site Description',
    'site_logo' => 'Site Logo',
    'site_favicon' => 'Site Favicon',
    'currency' => 'Currency',
    'currency_symbol' => 'Currency Symbol',
    'tax_rate' => 'Tax Rate (%)',
    'shipping_fee' => 'Shipping Fee',
    'items_per_page' => 'Items Per Page',
    'contact_email' => 'Contact Email',
    'contact_phone' => 'Contact Phone',
    'contact_address' => 'Contact Address',
    'social_facebook' => 'Facebook URL',
    'social_twitter' => 'Twitter URL',
    'social_instagram' => 'Instagram URL',
    'social_linkedin' => 'LinkedIn URL',
    'maintenance_mode' => 'Maintenance Mode',
    'allow_registration' => 'Allow Customer Registration',
    'require_email_verification' => 'Require Email Verification',
    'enable_reviews' => 'Enable Reviews',
    'enable_wishlist' => 'Enable Wishlist'
]);
