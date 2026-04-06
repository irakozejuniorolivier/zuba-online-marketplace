<?php
session_start();
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../includes/functions.php';

// Default settings
$default_settings = [
    'site_name' => 'Zuba Online Market',
    'site_tagline' => 'Your One-Stop Marketplace',
    'site_email' => 'info@zubamarket.com',
    'site_phone' => '+250788000000',
    'site_address' => 'Kigali, Rwanda',
    'site_logo' => '',
    'site_favicon' => '',
    'currency' => 'RWF',
    'currency_symbol' => 'FRw',
    'tax_rate' => '0',
    'shipping_fee' => '0',
    'items_per_page' => '12',
    'enable_reviews' => '1',
    'enable_wishlist' => '1',
    'enable_products' => '1',
    'enable_properties' => '1',
    'enable_vehicles' => '1',
    'maintenance_mode' => '0',
    'primary_color' => '#f97316',
    'secondary_color' => '#1a1a2e',
    'header_background' => '#ffffff',
    'support_email' => 'support@zubamarket.com',
    'support_phone' => '+250788000000',
    'facebook_url' => '',
    'twitter_url' => '',
    'instagram_url' => '',
    'linkedin_url' => '',
    'youtube_url' => '',
    'whatsapp_number' => '',
    'header_sticky' => '1',
    'show_search_bar' => '1',
    'show_cart_icon' => '1',
    'show_wishlist_icon' => '1',
    'show_user_menu' => '1',
    'show_categories_menu' => '1',
    'header_text_color' => '#1a1a2e',
    'header_border_color' => '#e5e7eb',
    'logo_max_width' => '150',
    'logo_max_height' => '50',
    'search_placeholder' => 'Search products, properties, vehicles...',
    'enable_notifications' => '1',
    'show_top_bar' => '1',
    'top_bar_text' => 'Free shipping on orders over 50,000 FRw',
    'top_bar_background' => '#f97316',
    'top_bar_text_color' => '#ffffff',
];

$inserted = 0;
$updated = 0;
$errors = [];

foreach ($default_settings as $key => $value) {
    $key_escaped = $conn->real_escape_string($key);
    $value_escaped = $conn->real_escape_string($value);
    
    // Determine setting type
    $type = 'text';
    if (in_array($key, ['tax_rate', 'shipping_fee', 'items_per_page', 'logo_max_width', 'logo_max_height'])) {
        $type = 'number';
    } elseif (in_array($key, ['enable_reviews', 'enable_wishlist', 'enable_products', 'enable_properties', 'enable_vehicles', 'maintenance_mode', 'header_sticky', 'show_search_bar', 'show_cart_icon', 'show_wishlist_icon', 'show_user_menu', 'show_categories_menu', 'enable_notifications', 'show_top_bar'])) {
        $type = 'boolean';
    }
    
    // Check if exists
    $check = $conn->query("SELECT id FROM site_settings WHERE setting_key = '$key_escaped'");
    
    if ($check && $check->num_rows > 0) {
        // Update existing
        $result = $conn->query("UPDATE site_settings SET setting_value = '$value_escaped', setting_type = '$type', updated_at = NOW() WHERE setting_key = '$key_escaped'");
        if ($result) {
            $updated++;
        } else {
            $errors[] = "Error updating $key: " . $conn->error;
        }
    } else {
        // Insert new
        $result = $conn->query("INSERT INTO site_settings (setting_key, setting_value, setting_type, updated_at) VALUES ('$key_escaped', '$value_escaped', '$type', NOW())");
        if ($result) {
            $inserted++;
        } else {
            $errors[] = "Error inserting $key: " . $conn->error;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Initialize Settings - Zuba Market</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f9fafb;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #1a1a2e;
            margin: 0 0 10px 0;
        }
        p {
            color: #666;
            margin: 0 0 20px 0;
        }
        .stats {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .stats div {
            margin: 5px 0;
            font-size: 14px;
        }
        .errors {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .errors div {
            margin: 5px 0;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #f97316;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #ea580c;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (empty($errors)): ?>
            <div class="success-icon">✅</div>
            <h1>Settings Initialized Successfully!</h1>
            <p>Your site settings have been initialized with default values.</p>
            
            <div class="stats">
                <div><strong>New Settings Inserted:</strong> <?php echo $inserted; ?></div>
                <div><strong>Existing Settings Updated:</strong> <?php echo $updated; ?></div>
                <div><strong>Total Settings:</strong> <?php echo $inserted + $updated; ?></div>
            </div>
            
            <a href="../index.php" class="btn">Go to Dashboard</a>
        <?php else: ?>
            <div class="error-icon">⚠️</div>
            <h1>Settings Initialization Completed with Errors</h1>
            <p>Some settings were processed, but errors occurred:</p>
            
            <div class="stats">
                <div><strong>New Settings Inserted:</strong> <?php echo $inserted; ?></div>
                <div><strong>Existing Settings Updated:</strong> <?php echo $updated; ?></div>
                <div><strong>Total Settings:</strong> <?php echo $inserted + $updated; ?></div>
            </div>
            
            <div class="errors">
                <strong>Errors:</strong>
                <?php foreach ($errors as $error): ?>
                    <div>• <?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
            
            <a href="../index.php" class="btn">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
