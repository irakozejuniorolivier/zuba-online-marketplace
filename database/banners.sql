-- ============================================
-- HERO/BANNER ADVERTISING SYSTEM
-- Zuba Online Market
-- ============================================

-- Create banners table
CREATE TABLE IF NOT EXISTS `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `position` enum('hero','top','middle','bottom','sidebar') DEFAULT 'hero',
  `page` enum('home','products','properties','vehicles','all') DEFAULT 'home',
  `background_color` varchar(50) DEFAULT NULL,
  `text_color` varchar(50) DEFAULT '#ffffff',
  `overlay_opacity` decimal(3,2) DEFAULT 0.50,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `position` (`position`),
  KEY `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT PROFESSIONAL HERO BANNERS
-- ============================================

-- Hero Banner 1: Main Homepage Hero
INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`) VALUES
('Welcome to Zuba Online Market', 
 'Your One-Stop Marketplace for Everything', 
 'Discover amazing products, dream properties, and reliable vehicles all in one place. Shop with confidence and convenience.',
 'hero-main.jpg',
 'Start Shopping',
 'products.php',
 'hero',
 'home',
 '#f97316',
 '#ffffff',
 0.40,
 1,
 'active',
 NOW(),
 DATE_ADD(NOW(), INTERVAL 6 MONTH));

-- Hero Banner 2: E-Commerce Focus
INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`) VALUES
('Shop the Latest Electronics', 
 'Up to 50% Off on Selected Items', 
 'Upgrade your tech with our exclusive deals on smartphones, laptops, and accessories. Limited time offer!',
 'hero-electronics.jpg',
 'Shop Electronics',
 'products.php?category=electronics',
 'hero',
 'home',
 '#1a1a2e',
 '#ffffff',
 0.50,
 2,
 'active',
 NOW(),
 DATE_ADD(NOW(), INTERVAL 3 MONTH));

-- Hero Banner 3: Real Estate Focus
INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`) VALUES
('Find Your Dream Home', 
 'Premium Properties in Prime Locations', 
 'Browse through our exclusive collection of residential and commercial properties. Your perfect space awaits!',
 'hero-realestate.jpg',
 'Browse Properties',
 'properties.php',
 'hero',
 'home',
 '#10b981',
 '#ffffff',
 0.45,
 3,
 'active',
 NOW(),
 DATE_ADD(NOW(), INTERVAL 6 MONTH));

-- Hero Banner 4: Car Rental Focus
INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`) VALUES
('Rent Your Perfect Ride', 
 'Affordable Daily, Weekly & Monthly Rates', 
 'Choose from our wide selection of well-maintained vehicles. Book now and hit the road with confidence!',
 'hero-vehicles.jpg',
 'View Vehicles',
 'vehicles.php',
 'hero',
 'home',
 '#3b82f6',
 '#ffffff',
 0.40,
 4,
 'active',
 NOW(),
 DATE_ADD(NOW(), INTERVAL 6 MONTH));

-- Hero Banner 5: Seasonal Sale
INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`, `start_date`, `end_date`) VALUES
('Summer Sale Extravaganza', 
 'Massive Discounts Across All Categories', 
 'Save big on products, properties, and vehicle rentals. Don\'t miss out on these incredible deals!',
 'hero-sale.jpg',
 'Shop Sale',
 'products.php?sale=1',
 'hero',
 'home',
 '#ef4444',
 '#ffffff',
 0.35,
 5,
 'active',
 NOW(),
 DATE_ADD(NOW(), INTERVAL 1 MONTH));

-- ============================================
-- PRODUCTS PAGE BANNERS
-- ============================================

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Fashion Week Special', 
 'New Arrivals - Trending Styles', 
 'Discover the latest fashion trends and elevate your wardrobe with our exclusive collection.',
 'banner-fashion.jpg',
 'Shop Fashion',
 'products.php?category=fashion',
 'top',
 'products',
 '#ec4899',
 '#ffffff',
 0.40,
 1,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Home & Garden Essentials', 
 'Transform Your Living Space', 
 'Quality furniture, decor, and garden supplies at unbeatable prices.',
 'banner-home.jpg',
 'Shop Now',
 'products.php?category=home-garden',
 'middle',
 'products',
 '#059669',
 '#ffffff',
 0.45,
 2,
 'active');

-- ============================================
-- PROPERTIES PAGE BANNERS
-- ============================================

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Luxury Apartments Available', 
 'Modern Living in the Heart of Kigali', 
 'Explore our premium apartment listings with world-class amenities and stunning views.',
 'banner-apartments.jpg',
 'View Apartments',
 'properties.php?type=apartment',
 'top',
 'properties',
 '#8b5cf6',
 '#ffffff',
 0.40,
 1,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Commercial Spaces for Rent', 
 'Prime Locations for Your Business', 
 'Find the perfect office or retail space to grow your business.',
 'banner-commercial.jpg',
 'Browse Commercial',
 'properties.php?type=commercial',
 'middle',
 'properties',
 '#0891b2',
 '#ffffff',
 0.45,
 2,
 'active');

-- ============================================
-- VEHICLES PAGE BANNERS
-- ============================================

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Luxury Car Collection', 
 'Experience Premium Driving', 
 'Rent high-end vehicles for special occasions, business trips, or leisure travel.',
 'banner-luxury-cars.jpg',
 'View Luxury Cars',
 'vehicles.php?type=luxury',
 'top',
 'vehicles',
 '#1e293b',
 '#ffffff',
 0.50,
 1,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Family SUVs & Vans', 
 'Spacious & Comfortable Rides', 
 'Perfect for family trips and group travel. Book your adventure today!',
 'banner-family-vehicles.jpg',
 'Browse SUVs',
 'vehicles.php?type=suv',
 'middle',
 'vehicles',
 '#7c3aed',
 '#ffffff',
 0.40,
 2,
 'active');

-- ============================================
-- PROMOTIONAL BANNERS (Sidebar/Bottom)
-- ============================================

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Free Shipping', 
 'On Orders Over 50,000 FRw', 
 NULL,
 'promo-shipping.jpg',
 'Learn More',
 'about.php#shipping',
 'sidebar',
 'all',
 '#f59e0b',
 '#ffffff',
 0.30,
 1,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('24/7 Customer Support', 
 'We\'re Here to Help', 
 NULL,
 'promo-support.jpg',
 'Contact Us',
 'contact.php',
 'sidebar',
 'all',
 '#10b981',
 '#ffffff',
 0.30,
 2,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Secure Payment', 
 'Multiple Payment Options', 
 NULL,
 'promo-payment.jpg',
 'View Methods',
 'about.php#payment',
 'sidebar',
 'all',
 '#3b82f6',
 '#ffffff',
 0.30,
 3,
 'active');

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Download Our App', 
 'Shop on the Go - Coming Soon', 
 NULL,
 'promo-app.jpg',
 'Get Notified',
 '#',
 'bottom',
 'all',
 '#6366f1',
 '#ffffff',
 0.35,
 1,
 'active');

-- ============================================
-- NEWSLETTER BANNER
-- ============================================

INSERT INTO `banners` (`title`, `subtitle`, `description`, `image`, `button_text`, `button_link`, `position`, `page`, `background_color`, `text_color`, `overlay_opacity`, `sort_order`, `status`) VALUES
('Join Our Newsletter', 
 'Get 10% Off Your First Order', 
 'Subscribe now and receive exclusive deals, new arrivals, and special offers directly to your inbox.',
 'banner-newsletter.jpg',
 'Subscribe Now',
 '#newsletter',
 'bottom',
 'home',
 '#f97316',
 '#ffffff',
 0.40,
 1,
 'active');

-- ============================================
-- SAMPLE QUERIES FOR BANNER MANAGEMENT
-- ============================================

-- Get active hero banners for homepage
-- SELECT * FROM banners WHERE position = 'hero' AND page IN ('home', 'all') AND status = 'active' AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW()) ORDER BY sort_order ASC;

-- Get all active banners for products page
-- SELECT * FROM banners WHERE page IN ('products', 'all') AND status = 'active' AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW()) ORDER BY position, sort_order ASC;

-- Increment banner views
-- UPDATE banners SET views = views + 1 WHERE id = ?;

-- Increment banner clicks
-- UPDATE banners SET clicks = clicks + 1 WHERE id = ?;

-- Get banner performance stats
-- SELECT id, title, position, page, views, clicks, ROUND((clicks / views * 100), 2) as ctr FROM banners WHERE views > 0 ORDER BY ctr DESC;
