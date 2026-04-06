-- ============================================
-- ZUBA ONLINE MARKET - DATABASE SCHEMA
-- Multi-vertical Marketplace (E-commerce, Real Estate, Car Rental)
-- Version: 1.0
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `zuba_market` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `zuba_market`;

-- ============================================
-- CORE TABLES
-- ============================================

-- Admins Table (Separate table for admin management)
CREATE TABLE `admins` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `permissions` TEXT DEFAULT NULL COMMENT 'JSON format for granular permissions',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_by` INT(11) UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `created_by` (`created_by`),
  FOREIGN KEY (`created_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table (Customers Only)
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `profile_image` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories Table (Multi-purpose for all modules)
CREATE TABLE `categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `type` ENUM('ecommerce', 'realestate', 'carrental') NOT NULL,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_type` (`slug`, `type`),
  KEY `type` (`type`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Methods Table (Admin can add/remove)
CREATE TABLE `payment_methods` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('mobile_money', 'bank', 'other') NOT NULL,
  `account_name` VARCHAR(100) DEFAULT NULL,
  `account_number` VARCHAR(100) DEFAULT NULL,
  `instructions` TEXT DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- E-COMMERCE MODULE
-- ============================================

-- Products Table
CREATE TABLE `products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `compare_price` DECIMAL(10, 2) DEFAULT NULL,
  `stock` INT(11) NOT NULL DEFAULT 0,
  `sku` VARCHAR(100) DEFAULT NULL,
  `brand` VARCHAR(100) DEFAULT NULL,
  `condition` ENUM('new', 'used', 'refurbished') DEFAULT 'new',
  `weight` DECIMAL(8, 2) DEFAULT NULL,
  `dimensions` VARCHAR(100) DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'active',
  `views` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `price` (`price`),
  FULLTEXT KEY `search` (`name`, `description`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Images Table
CREATE TABLE `product_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `is_primary` (`is_primary`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shopping Cart Table
CREATE TABLE `cart` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE `orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `shipping_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `tax` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method_id` INT(11) UNSIGNED DEFAULT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending_payment', 'payment_submitted', 'approved', 'processing', 'shipped', 'delivered', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending_payment',
  `shipping_name` VARCHAR(100) NOT NULL,
  `shipping_phone` VARCHAR(20) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_city` VARCHAR(100) NOT NULL,
  `shipping_country` VARCHAR(100) NOT NULL,
  `customer_note` TEXT DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `approved_by` (`approved_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items Table
CREATE TABLE `order_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- REAL ESTATE MODULE
-- ============================================

-- Properties Table
CREATE TABLE `properties` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `listing_type` ENUM('sale', 'rent') NOT NULL,
  `property_type` ENUM('apartment', 'house', 'villa', 'commercial', 'land', 'office', 'other') NOT NULL,
  `price` DECIMAL(12, 2) NOT NULL,
  `rent_period` ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT NULL,
  `bedrooms` INT(11) DEFAULT NULL,
  `bathrooms` INT(11) DEFAULT NULL,
  `area` DECIMAL(10, 2) DEFAULT NULL,
  `area_unit` ENUM('sqm', 'sqft', 'acres', 'hectares') DEFAULT 'sqm',
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) NOT NULL,
  `zip_code` VARCHAR(20) DEFAULT NULL,
  `latitude` DECIMAL(10, 8) DEFAULT NULL,
  `longitude` DECIMAL(11, 8) DEFAULT NULL,
  `year_built` INT(4) DEFAULT NULL,
  `parking_spaces` INT(11) DEFAULT NULL,
  `features` TEXT DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('available', 'sold', 'rented', 'inactive') NOT NULL DEFAULT 'available',
  `views` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `listing_type` (`listing_type`),
  KEY `property_type` (`property_type`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `price` (`price`),
  KEY `city` (`city`),
  FULLTEXT KEY `search` (`title`, `description`, `address`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Images Table
CREATE TABLE `property_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `is_primary` (`is_primary`),
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property Orders Table
CREATE TABLE `property_orders` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `property_id` INT(11) UNSIGNED NOT NULL,
  `property_title` VARCHAR(255) NOT NULL,
  `order_type` ENUM('purchase', 'rent') NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `rent_duration` INT(11) DEFAULT NULL,
  `rent_period` ENUM('daily', 'weekly', 'monthly', 'yearly') DEFAULT NULL,
  `payment_method_id` INT(11) UNSIGNED DEFAULT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending_payment', 'payment_submitted', 'approved', 'rejected', 'cancelled', 'completed') NOT NULL DEFAULT 'pending_payment',
  `customer_note` TEXT DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `property_id` (`property_id`),
  KEY `status` (`status`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `approved_by` (`approved_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CAR RENTAL MODULE
-- ============================================

-- Vehicles Table
CREATE TABLE `vehicles` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `brand` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `year` INT(4) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `vehicle_type` ENUM('sedan', 'suv', 'truck', 'van', 'coupe', 'convertible', 'motorcycle', 'other') NOT NULL,
  `transmission` ENUM('automatic', 'manual') NOT NULL,
  `fuel_type` ENUM('petrol', 'diesel', 'electric', 'hybrid') NOT NULL,
  `seats` INT(11) NOT NULL,
  `doors` INT(11) DEFAULT NULL,
  `color` VARCHAR(50) DEFAULT NULL,
  `plate_number` VARCHAR(50) NOT NULL,
  `mileage` INT(11) DEFAULT NULL,
  `daily_rate` DECIMAL(10, 2) NOT NULL,
  `weekly_rate` DECIMAL(10, 2) DEFAULT NULL,
  `monthly_rate` DECIMAL(10, 2) DEFAULT NULL,
  `insurance_included` TINYINT(1) NOT NULL DEFAULT 0,
  `features` TEXT DEFAULT NULL,
  `location` VARCHAR(255) DEFAULT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('available', 'rented', 'maintenance', 'inactive') NOT NULL DEFAULT 'available',
  `views` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `plate_number` (`plate_number`),
  KEY `category_id` (`category_id`),
  KEY `vehicle_type` (`vehicle_type`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `daily_rate` (`daily_rate`),
  FULLTEXT KEY `search` (`brand`, `model`, `description`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicle Images Table
CREATE TABLE `vehicle_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `is_primary` (`is_primary`),
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Table
CREATE TABLE `bookings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `vehicle_name` VARCHAR(255) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `rental_days` INT(11) NOT NULL,
  `rate_type` ENUM('daily', 'weekly', 'monthly') NOT NULL,
  `rate_amount` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  `insurance_fee` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `pickup_location` VARCHAR(255) DEFAULT NULL,
  `dropoff_location` VARCHAR(255) DEFAULT NULL,
  `payment_method_id` INT(11) UNSIGNED DEFAULT NULL,
  `payment_proof` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending_payment', 'payment_submitted', 'approved', 'active', 'completed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending_payment',
  `customer_note` TEXT DEFAULT NULL,
  `admin_note` TEXT DEFAULT NULL,
  `approved_by` INT(11) UNSIGNED DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `user_id` (`user_id`),
  KEY `vehicle_id` (`vehicle_id`),
  KEY `status` (`status`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `approved_by` (`approved_by`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`approved_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADDITIONAL TABLES
-- ============================================

-- Reviews Table (For Products, Properties, Vehicles)
CREATE TABLE `reviews` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `item_type` ENUM('product', 'property', 'vehicle') NOT NULL,
  `item_id` INT(11) UNSIGNED NOT NULL,
  `rating` TINYINT(1) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `comment` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `item_type_id` (`item_type`, `item_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlist Table
CREATE TABLE `wishlist` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `item_type` ENUM('product', 'property', 'vehicle') NOT NULL,
  `item_id` INT(11) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_item` (`user_id`, `item_type`, `item_id`),
  KEY `user_id` (`user_id`),
  KEY `item_type_id` (`item_type`, `item_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table
CREATE TABLE `site_settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('text', 'number', 'boolean', 'json') NOT NULL DEFAULT 'text',
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Activity Logs Table
CREATE TABLE `activity_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('admin', 'customer') NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_type_id` (`user_type`, `user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert Default Super Admin (password: admin123)
INSERT INTO `admins` (`name`, `email`, `phone`, `password`, `status`) VALUES
('Super Admin', 'admin@zubamarket.com', '+250788000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'active');

-- Insert Default Payment Methods
INSERT INTO `payment_methods` (`name`, `type`, `account_name`, `account_number`, `instructions`, `status`) VALUES
('Airtel Money', 'mobile_money', 'Zuba Market', '+250788123456', 'Send payment to the number above and upload screenshot', 'active'),
('MoMo Pay Code', 'mobile_money', 'Zuba Market', '+250788654321', 'Use MoMo Pay Code and upload payment confirmation', 'active'),
('Bank Transfer', 'bank', 'Zuba Online Market Ltd', '1234567890', 'Transfer to our bank account and upload receipt', 'active');

-- Insert Default Categories
INSERT INTO `categories` (`name`, `slug`, `type`, `description`, `status`) VALUES
-- E-commerce Categories
('Electronics', 'electronics', 'ecommerce', 'Electronic devices and gadgets', 'active'),
('Fashion', 'fashion', 'ecommerce', 'Clothing and accessories', 'active'),
('Home & Garden', 'home-garden', 'ecommerce', 'Home improvement and garden supplies', 'active'),
('Sports', 'sports', 'ecommerce', 'Sports equipment and accessories', 'active'),

-- Real Estate Categories
('Residential', 'residential', 'realestate', 'Houses and apartments', 'active'),
('Commercial', 'commercial', 'realestate', 'Commercial properties', 'active'),
('Land', 'land', 'realestate', 'Land for sale or lease', 'active'),

-- Car Rental Categories
('Economy Cars', 'economy-cars', 'carrental', 'Affordable and fuel-efficient vehicles', 'active'),
('Luxury Cars', 'luxury-cars', 'carrental', 'Premium and luxury vehicles', 'active'),
('SUVs', 'suvs', 'carrental', 'Sport utility vehicles', 'active'),
('Vans', 'vans', 'carrental', 'Passenger and cargo vans', 'active');

-- Insert Default Site Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Zuba Online Market', 'text'),
('site_tagline', 'Your One-Stop Marketplace', 'text'),
('site_email', 'info@zubamarket.com', 'text'),
('site_phone', '+250788000000', 'text'),
('site_address', 'Kigali, Rwanda', 'text'),
('site_logo', '', 'text'),
('site_favicon', '', 'text'),
('currency', 'RWF', 'text'),
('currency_symbol', 'FRw', 'text'),
('tax_rate', '0', 'number'),
('shipping_fee', '0', 'number'),
('items_per_page', '12', 'number'),
('enable_reviews', '1', 'boolean'),
('enable_wishlist', '1', 'boolean'),
('enable_products', '1', 'boolean'),
('enable_properties', '1', 'boolean'),
('enable_vehicles', '1', 'boolean'),
('maintenance_mode', '0', 'boolean'),
('primary_color', '#f97316', 'text'),
('secondary_color', '#1a1a2e', 'text'),
('header_background', '#ffffff', 'text'),
('support_email', 'support@zubamarket.com', 'text'),
('support_phone', '+250788000000', 'text'),
('facebook_url', '', 'text'),
('twitter_url', '', 'text'),
('instagram_url', '', 'text'),
('linkedin_url', '', 'text'),
('youtube_url', '', 'text'),
('whatsapp_number', '', 'text'),
('header_sticky', '1', 'boolean'),
('show_search_bar', '1', 'boolean'),
('show_cart_icon', '1', 'boolean'),
('show_wishlist_icon', '1', 'boolean'),
('show_user_menu', '1', 'boolean'),
('show_categories_menu', '1', 'boolean'),
('header_text_color', '#1a1a2e', 'text'),
('header_border_color', '#e5e7eb', 'text'),
('logo_max_width', '150', 'number'),
('logo_max_height', '50', 'number'),
('search_placeholder', 'Search products, properties, vehicles...', 'text'),
('enable_notifications', '1', 'boolean'),
('show_top_bar', '1', 'boolean'),
('top_bar_text', 'Free shipping on orders over 50,000 FRw', 'text'),
('top_bar_background', '#f97316', 'text'),
('top_bar_text_color', '#ffffff', 'text');

-- ============================================
-- END OF DATABASE SCHEMA
-- ============================================
