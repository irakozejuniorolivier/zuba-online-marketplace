<?php
// Get settings from database
$site_name = getSetting('site_name', 'Zuba Online Market');
$primary_color = getSetting('primary_color', '#f97316');
$header_background = getSetting('header_background', '#ffffff');
$header_text_color = getSetting('header_text_color', '#1a1a2e');
$header_border_color = getSetting('header_border_color', '#e5e7eb');
$show_search_bar = getSetting('show_search_bar', '1');
$show_cart_icon = getSetting('show_cart_icon', '1');
$show_wishlist_icon = getSetting('show_wishlist_icon', '1');
$enable_products = getSetting('enable_products', '1');
$enable_properties = getSetting('enable_properties', '1');
$enable_vehicles = getSetting('enable_vehicles', '1');

// Get cart count
$cart_count = 0;
if (isCustomerLoggedIn()) {
    $result = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = " . currentCustomerId());
    if ($result) {
        $row = $result->fetch_assoc();
        $cart_count = $row['count'];
    }
}

// Get wishlist count
$wishlist_count = 0;
if (isCustomerLoggedIn()) {
    $result = $conn->query("SELECT COUNT(*) as count FROM wishlist WHERE user_id = " . currentCustomerId());
    if ($result) {
        $row = $result->fetch_assoc();
        $wishlist_count = $row['count'];
    }
}

// Get categories for menu
$categories_ecommerce = [];
$categories_properties = [];
$categories_vehicles = [];

if ($enable_products) {
    $result = $conn->query("SELECT id, name, slug FROM categories WHERE type = 'ecommerce' AND status = 'active' LIMIT 15");
    if ($result) {
        $categories_ecommerce = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if ($enable_properties) {
    $result = $conn->query("SELECT id, name, slug FROM categories WHERE type = 'realestate' AND status = 'active' LIMIT 15");
    if ($result) {
        $categories_properties = $result->fetch_all(MYSQLI_ASSOC);
    }
}

if ($enable_vehicles) {
    $result = $conn->query("SELECT id, name, slug FROM categories WHERE type = 'carrental' AND status = 'active' LIMIT 15");
    if ($result) {
        $categories_vehicles = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$is_logged_in = isCustomerLoggedIn();
$customer = $is_logged_in ? currentCustomer() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Home') . ' - ' . e($site_name); ?></title>
    <link rel="icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: <?php echo $primary_color; ?>;
            --header-bg: <?php echo $header_background; ?>;
            --header-text: <?php echo $header_text_color; ?>;
            --header-border: <?php echo $header_border_color; ?>;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1f2937;
        }

        /* ===== HEADER ===== */
        header {
            background: var(--header-bg);
            border-bottom: 2px solid var(--header-border);
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        /* HEADER TOP TIER - Logo & Brand Name */
        .header-top {
            max-width: 1400px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 16px;
            min-height: 70px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--header-text);
            flex-shrink: 0;
        }

        .logo-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: #ffffff;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            flex-shrink: 0;
            transition: all 0.3s;
            border: 3px solid transparent;
            background-image: 
                linear-gradient(white, white),
                linear-gradient(135deg, var(--primary) 0%, #fb923c 50%, #ea580c 100%);
            background-origin: border-box;
            background-clip: padding-box, border-box;
            position: relative;
        }

        .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, var(--primary), #fb923c, #ea580c);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .logo-wrapper:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.25);
        }

        .logo-wrapper:hover::before {
            opacity: 1;
            animation: rotateBorder 2s linear infinite;
        }

        @keyframes rotateBorder {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            padding: 8px;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-tagline {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* HEADER BOTTOM TIER - Search, Navbar & Actions */
        .header-bottom {
            max-width: 1400px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 68px;
            background: #ffffff;
            border-top: 1px solid var(--header-border);
        }

        .search-wrapper {
            display: flex;
            flex: 1;
            max-width: 600px;
            position: relative;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            min-width: 0;
        }

        .search-input {
            width: 100%;
            padding: 13px 50px 13px 48px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            background: #f9fafb;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .search-input:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.08);
        }

        .search-input::placeholder {
            color: #9ca3af;
            font-weight: 500;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            color: #6b7280;
            font-size: 18px;
            pointer-events: none;
            transition: color 0.3s;
        }

        .search-input:focus ~ .search-icon {
            color: var(--primary);
        }

        .search-btn {
            position: absolute;
            right: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            border: none;
            padding: 9px 20px;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .search-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .search-btn:active {
            transform: translateY(0);
        }

        /* SEARCH SUGGESTIONS */
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 10px 10px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .search-suggestions.active {
            display: block;
        }

        /* KIKUU STYLE FULL SCREEN SEARCH OVERLAY - ENHANCED */
        .search-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #ea580c 100%);
            z-index: 9998;
            display: none;
            opacity: 0;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .search-overlay.active {
            display: block;
            opacity: 1;
            animation: overlayFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes overlayFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .search-overlay::before {
            content: '🔍';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 25vw;
            opacity: 0.03;
            pointer-events: none;
            animation: floatIcon 6s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-50%, -48%) rotate(5deg); }
        }

        /* Animated background particles */
        .search-overlay::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,0.06) 0%, transparent 50%);
            animation: particleFloat 15s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes particleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .search-overlay-content {
            height: 100%;
            overflow-y: auto;
            padding: 20px;
            position: relative;
            z-index: 1;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.3) transparent;
        }

        .search-overlay-content::-webkit-scrollbar {
            width: 8px;
        }

        .search-overlay-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .search-overlay-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .search-overlay-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }

        .search-overlay-header {
            max-width: 900px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            gap: 16px;
            animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .search-overlay-close {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            backdrop-filter: blur(10px);
        }

        .search-overlay-close:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.08) rotate(90deg);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }

        .search-overlay-close:active {
            transform: scale(0.95) rotate(90deg);
        }

        .search-overlay-input-wrapper {
            flex: 1;
            position: relative;
        }

        .search-overlay-input {
            width: 100%;
            padding: 18px 60px 18px 60px;
            border: 3px solid rgba(255, 255, 255, 0.25);
            border-radius: 18px;
            font-size: 18px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.98);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1a1a2e;
            backdrop-filter: blur(20px);
        }

        .search-overlay-input:focus {
            outline: none;
            background: white;
            border-color: white;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2), 0 0 0 4px rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }

        .search-overlay-input::placeholder {
            color: #9ca3af;
        }

        .search-overlay-icon {
            position: absolute;
            left: 22px;
            top: 50%;
            transform: translateY(-50%);
            color: #f97316;
            font-size: 24px;
            animation: searchPulse 2s ease-in-out infinite;
        }

        @keyframes searchPulse {
            0%, 100% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-50%) scale(1.1); }
        }

        .search-overlay-input:focus ~ .search-overlay-icon {
            animation: none;
            transform: translateY(-50%) scale(1.15);
        }

        .search-overlay-clear {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            background: #e5e7eb;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-overlay-clear.active {
            display: flex;
            animation: popIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes popIn {
            0% { transform: translateY(-50%) scale(0); }
            50% { transform: translateY(-50%) scale(1.2); }
            100% { transform: translateY(-50%) scale(1); }
        }

        .search-overlay-clear:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .search-overlay-clear:active {
            transform: translateY(-50%) scale(0.9);
        }

        .search-overlay-body {
            max-width: 900px;
            margin: 0 auto;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .search-section {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 32px 36px;
            margin-bottom: 24px;
            backdrop-filter: blur(20px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .search-section:hover {
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.18);
            transform: translateY(-2px);
        }

        .search-section-title {
            font-size: 14px;
            font-weight: 800;
            color: #6b7280;
            margin: 0 0 24px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            border-radius: 10px;
        }

        /* Recent Searches - Enhanced Pills */
        .recent-searches {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .recent-search-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border: 2px solid rgba(249, 115, 22, 0.15);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            line-height: 1.4;
            position: relative;
            overflow: hidden;
        }

        .recent-search-item::before {
            content: '🔍';
            font-size: 14px;
            opacity: 0.6;
        }

        .recent-search-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .recent-search-item:hover {
            background: #ffffff;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.25);
        }

        .recent-search-item:hover::after {
            opacity: 1;
        }

        .recent-search-item:active {
            transform: translateY(-1px);
        }

        .clear-recent-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            color: #dc2626;
            border: 2px solid #fecaca;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 20px;
        }

        .clear-recent-btn:hover {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #dc2626;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.2);
        }

        .clear-recent-btn:active {
            transform: translateY(-1px);
        }

        .clear-recent-btn i {
            font-size: 14px;
        }

        /* Enhanced Suggestion List with Images */
        .suggestion-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }

        .suggestion-list-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            color: #1a1a2e;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border: 2px solid #e5e7eb;
            text-align: left;
            position: relative;
            overflow: hidden;
        }

        .suggestion-list-item::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.05) 0%, rgba(234, 88, 12, 0.02) 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .suggestion-list-item:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.2);
        }

        .suggestion-list-item:hover::before {
            opacity: 1;
        }

        .suggestion-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }

        .suggestion-list-item:hover .suggestion-image {
            transform: scale(1.08);
            border-color: var(--primary);
        }

        .suggestion-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #fff5f0 0%, #ffe8dc 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 26px;
            flex-shrink: 0;
            transition: all 0.3s;
            border: 2px solid rgba(249, 115, 22, 0.2);
        }

        .suggestion-list-item:hover .suggestion-icon {
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            color: white;
            transform: scale(1.08) rotate(5deg);
            box-shadow: 0 4px 16px rgba(249, 115, 22, 0.3);
        }

        .suggestion-text {
            flex: 1;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        .suggestion-text strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.3;
        }

        .suggestion-text small {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 600;
            display: block;
        }

        .suggestion-price {
            display: block;
            font-size: 13px;
            font-weight: 800;
            color: var(--primary);
            margin-top: 4px;
        }

        .suggestion-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
            font-size: 11px;
            color: #6b7280;
        }

        .suggestion-meta i {
            font-size: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 20px;
            display: block;
            animation: emptyStatePulse 2s ease-in-out infinite;
        }

        @keyframes emptyStatePulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }

        .empty-state p {
            font-size: 16px;
            margin: 0;
            font-weight: 600;
        }

        /* Loading State */
        .search-loading {
            text-align: center;
            padding: 40px 20px;
        }

        .search-loading-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(249, 115, 22, 0.2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .search-loading-text {
            font-size: 14px;
            color: #6b7280;
            font-weight: 600;
        }

        @media (max-width: 1200px) {
            .suggestion-list {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .suggestion-list {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
            }

            .search-overlay::before {
                font-size: 40vw;
            }

            .search-section {
                padding: 24px 20px;
                border-radius: 20px;
            }

            .search-overlay-header {
                gap: 12px;
            }

            .search-overlay-close {
                width: 48px;
                height: 48px;
            }

            .search-overlay-input {
                padding: 16px 50px 16px 50px;
                font-size: 16px;
            }

            .suggestion-list-item {
                padding: 12px;
            }

            .suggestion-image,
            .suggestion-icon {
                width: 50px;
                height: 50px;
            }
        }

        @media (max-width: 480px) {
            .suggestion-list {
                grid-template-columns: 1fr;
            }

            .search-overlay-content {
                padding: 16px;
            }

            .search-section {
                padding: 20px 16px;
            }

            .recent-search-item {
                padding: 8px 14px;
                font-size: 12px;
            }
        }

        /* CATEGORY NAVBAR */
        .category-nav-inline {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .category-link-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--header-text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s;
            position: relative;
            padding: 10px 16px;
            border-radius: 10px;
            background: #f9fafb;
            border: 1px solid transparent;
        }

        .category-link-inline i {
            font-size: 17px;
            color: var(--primary);
            transition: all 0.3s;
        }

        .category-link-inline:hover {
            color: var(--primary);
            background: rgba(249, 115, 22, 0.08);
            border-color: rgba(249, 115, 22, 0.2);
            transform: translateY(-2px);
        }

        .category-link-inline:hover i {
            transform: scale(1.15);
        }

        /* HEADER ACTIONS */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .action-btn {
            position: relative;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            color: var(--header-text);
            font-size: 20px;
            padding: 12px 14px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            min-width: 48px;
            height: 48px;
        }

        .action-btn:hover {
            background: #ffffff;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 6px;
            border-radius: 12px;
            min-width: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            border: 2px solid white;
            display: none;
        }

        .badge.active {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: badgePop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes badgePop {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        .action-btn.has-items {
            background: rgba(249, 115, 22, 0.1);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* AUTH BUTTONS */
        .auth-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        /* User Avatar Dropdown */
        .user-dropdown {
            position: relative;
            flex-shrink: 0;
        }
        
        .user-avatar-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px 6px 6px;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: var(--header-text);
        }
        
        .user-avatar-btn:hover {
            background: #ffffff;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
        }
        
        .user-avatar-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1.2;
        }
        
        .user-email {
            font-size: 11px;
            color: #9ca3af;
            font-weight: 500;
        }
        
        .dropdown-icon {
            font-size: 12px;
            color: #9ca3af;
            transition: transform 0.3s;
        }
        
        .user-dropdown.active .dropdown-icon {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .user-dropdown.active .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #1a1a2e;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: rgba(249, 115, 22, 0.08);
            color: var(--primary);
        }
        
        .dropdown-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        
        .dropdown-item.logout {
            color: #dc2626;
        }
        
        .dropdown-item.logout:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-login, .btn-register {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-login {
            color: var(--primary);
            background: rgba(249, 115, 22, 0.08);
            border: 2px solid var(--primary);
        }

        .btn-login:hover {
            background: rgba(249, 115, 22, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }

        .btn-register {
            color: white;
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            border: 2px solid var(--primary);
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.3);
        }

        .btn-login:active, .btn-register:active {
            transform: translateY(0);
        }

        /* MOBILE BOTTOM NAV */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--header-bg);
            border-top: 1px solid var(--header-border);
            z-index: 99;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            height: 72px;
            width: 100vw;
            overflow: hidden;
        }

        .mobile-bottom-nav.active {
            display: flex;
        }

        .nav-items {
            display: flex;
            justify-content: space-around;
            align-items: stretch;
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            gap: 0;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            flex: 1;
            min-width: 0;
            height: 100%;
            text-decoration: none;
            color: #9ca3af;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.25s ease;
            position: relative;
            border-radius: 0;
            padding: 8px 4px;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-item:hover {
            background: transparent;
            color: var(--primary);
        }

        .nav-item:hover .nav-item-icon {
            transform: translateY(-3px);
        }

        .nav-item.active {
            color: var(--primary);
            background: transparent;
            border-top: none;
        }

        .nav-item.active span {
            color: var(--primary);
            font-weight: 700;
            font-size: 11px;
        }

        .nav-item-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 32px;
            width: 32px;
            flex-shrink: 0;
            border-radius: 10px;
            transition: all 0.25s ease;
            position: relative;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.08) 0%, rgba(234, 88, 12, 0.04) 100%);
        }

        .nav-item:hover .nav-item-icon {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(234, 88, 12, 0.1) 100%);
        }

        .nav-item.active .nav-item-icon {
            background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
            transform: scale(1.08);
        }

        .nav-item-icon i {
            font-size: 18px;
            color: #9ca3af;
            transition: all 0.25s ease;
        }

        .nav-item:hover .nav-item-icon i {
            color: var(--primary);
        }

        .nav-item.active .nav-item-icon i {
            color: white;
            font-size: 19px;
        }

        .nav-item span {
            font-size: 10px;
            line-height: 1.1;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.25s ease;
            color: #9ca3af;
        }

        .nav-item.active span {
            color: var(--primary);
        }

        .nav-item .badge {
            position: absolute;
            top: 2px;
            right: 4px;
            font-size: 8px;
            padding: 1px 4px;
            min-width: 16px;
        }

        /* ===== RESPONSIVE ===== */
        @media (min-width: 768px) {
            .mobile-bottom-nav {
                display: none !important;
            }

            body {
                padding-bottom: 0;
            }

            .category-nav-inline {
                display: flex;
            }

            .auth-buttons {
                display: flex;
            }
            
            .user-dropdown {
                display: block;
            }
        }

            @media (max-width: 767px) {
            .header-top {
                min-height: 60px;
                padding: 10px 12px;
                gap: 10px;
            }

            .logo-wrapper {
                width: 48px;
                height: 48px;
                border-width: 2.5px;
            }

            .logo-wrapper img {
                padding: 6px;
            }

            .brand-name {
                font-size: 17px;
            }

            .brand-tagline {
                display: none;
            }

            .header-bottom {
                min-height: 58px;
                padding: 10px 12px;
                gap: 10px;
                background: #ffffff;
                border-top: 1px solid var(--header-border);
            }

            .search-wrapper {
                flex: 1;
                max-width: none;
            }

            .search-input {
                padding: 11px 12px 11px 40px;
                font-size: 14px;
                border-radius: 10px;
            }

            .search-icon {
                left: 13px;
                font-size: 16px;
            }

            .search-btn {
                display: none;
            }

            .action-btn {
                font-size: 19px;
                padding: 10px 11px;
                min-width: 44px;
                height: 44px;
            }

            .header-actions {
                gap: 8px;
            }

            .category-nav-inline {
                display: none;
            }

            .auth-buttons {
                display: none;
            }
            
            .user-dropdown {
                display: none;
            }

            body {
                padding-bottom: 72px;
            }

            .mobile-bottom-nav.active {
                display: flex;
            }
        }
    </style>
</head>
<body>

<!-- MODERN PRELOADER -->
<div id="sitePreloader" aria-hidden="false">
    <div class="preloader-content">
        <div class="preloader-logo-wrapper">
            <div class="preloader-logo-ring"></div>
            <div class="preloader-logo-inner">
                <img src="<?php echo SITE_URL;?>/logo/logo.jpg" alt="<?php echo e($site_name); ?>">
            </div>
        </div>
        <div class="preloader-text">
            <h2 class="preloader-brand"><?php echo e($site_name); ?></h2>
            <p class="preloader-tagline">Your One-Stop Marketplace</p>
        </div>
        <div class="preloader-progress">
            <div class="preloader-progress-bar"></div>
        </div>
        <div class="preloader-dots">
            <span></span><span></span><span></span>
        </div>
    </div>
</div>

<style>
/* Modern Preloader - Kikuu/Amazon Style */
#sitePreloader {
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    z-index: 9999;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

#sitePreloader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.preloader-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    animation: preloaderFadeIn 0.6s ease-out;
}

@keyframes preloaderFadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Logo with Rotating Ring */
.preloader-logo-wrapper {
    position: relative;
    width: 160px;
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preloader-logo-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 5px solid transparent;
    border-top-color: var(--primary);
    border-right-color: var(--primary);
    animation: preloaderSpin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.preloader-logo-ring::before {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 5px solid transparent;
    border-bottom-color: #fb923c;
    border-left-color: #fb923c;
    animation: preloaderSpin 1.8s cubic-bezier(0.5, 0, 0.5, 1) infinite reverse;
}

@keyframes preloaderSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.preloader-logo-inner {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(249, 115, 22, 0.35);
    animation: preloaderPulse 2s ease-in-out infinite;
    overflow: hidden;
    padding: 18px;
}

@keyframes preloaderPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(249, 115, 22, 0.35); }
    50% { transform: scale(1.05); box-shadow: 0 14px 40px rgba(249, 115, 22, 0.45); }
}

.preloader-logo-inner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Text */
.preloader-text {
    text-align: center;
    animation: preloaderTextFade 1s ease-out 0.3s both;
}

@keyframes preloaderTextFade {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.preloader-brand {
    font-size: 28px;
    font-weight: 900;
    background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
}

.preloader-tagline {
    font-size: 14px;
    color: #6b7280;
    font-weight: 600;
    margin: 0;
    letter-spacing: 0.3px;
}

/* Progress Bar */
.preloader-progress {
    width: 200px;
    height: 4px;
    background: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    animation: preloaderProgressFade 1s ease-out 0.5s both;
}

@keyframes preloaderProgressFade {
    from { opacity: 0; width: 0; }
    to { opacity: 1; width: 200px; }
}

.preloader-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary) 0%, #fb923c 50%, var(--primary) 100%);
    background-size: 200% 100%;
    border-radius: 10px;
    animation: preloaderProgress 1.5s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(249, 115, 22, 0.5);
}

@keyframes preloaderProgress {
    0% { width: 0%; background-position: 0% 50%; }
    50% { width: 70%; background-position: 100% 50%; }
    100% { width: 100%; background-position: 0% 50%; }
}

/* Loading Dots */
.preloader-dots {
    display: flex;
    gap: 8px;
    animation: preloaderDotsFade 1s ease-out 0.7s both;
}

@keyframes preloaderDotsFade {
    from { opacity: 0; }
    to { opacity: 1; }
}

.preloader-dots span {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    animation: preloaderDotBounce 1.4s ease-in-out infinite;
}

.preloader-dots span:nth-child(1) { animation-delay: 0s; }
.preloader-dots span:nth-child(2) { animation-delay: 0.2s; }
.preloader-dots span:nth-child(3) { animation-delay: 0.4s; }

@keyframes preloaderDotBounce {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1.2); opacity: 1; }
}

/* Mobile Responsive */
@media (max-width: 640px) {
    .preloader-logo-wrapper { width: 130px; height: 130px; }
    .preloader-logo-inner { width: 100px; height: 100px; border-radius: 50%; padding: 15px; }
    .preloader-brand { font-size: 24px; }
    .preloader-tagline { font-size: 13px; }
    .preloader-progress { width: 160px; }
}
</style>

<script>
(function() {
    const preloader = document.getElementById('sitePreloader');
    if (!preloader) return;
    
    const minDisplayTime = 1500; // Minimum 1.5s display
    const maxDisplayTime = 3000; // Maximum 3s display
    const startTime = Date.now();
    
    function hidePreloader() {
        const elapsed = Date.now() - startTime;
        const remainingTime = Math.max(0, minDisplayTime - elapsed);
        
        setTimeout(() => {
            preloader.classList.add('hidden');
            preloader.setAttribute('aria-hidden', 'true');
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }, remainingTime);
    }
    
    // Hide when page is fully loaded
    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        window.addEventListener('load', hidePreloader);
    }
    
    // Safety fallback - force hide after max time
    setTimeout(() => {
        if (!preloader.classList.contains('hidden')) {
            hidePreloader();
        }
    }, maxDisplayTime);
})();
</script>

<!-- HEADER -->
<header>
    <!-- TOP TIER: Logo & Brand Name -->
    <div class="header-top">
        <a href="<?php echo SITE_URL; ?>" class="brand">
            <div class="logo-wrapper">
                <img src="<?php echo SITE_URL; ?>/logo/logo.jpg" alt="<?php echo e($site_name); ?>">
            </div>
            <div class="brand-info">
                <div class="brand-name"><?php echo e($site_name); ?></div>
                <div class="brand-tagline">Online Marketplace</div>
            </div>
        </a>
    </div>

    <!-- BOTTOM TIER: Search Bar, Navbar & Action Icons -->
    <div class="header-bottom">
        <?php if ($show_search_bar): ?>
            <div class="search-wrapper">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Search products, properties, vehicles..." id="headerSearch" autocomplete="off" readonly onclick="openSearchOverlay()">
                    <button class="search-btn" onclick="openSearchOverlay()">
                        <i class="fas fa-search"></i>
                        <span>Search</span>
                    </button>
                    <div class="search-suggestions" id="searchSuggestions"></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- INLINE CATEGORY NAVBAR - DESKTOP ONLY -->
        <div class="category-nav-inline">
            <?php if ($enable_products): ?>
                <a href="<?php echo SITE_URL; ?>/products.php" class="category-link-inline">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Products</span>
                </a>
            <?php endif; ?>
            <?php if ($enable_properties): ?>
                <a href="<?php echo SITE_URL; ?>/properties.php" class="category-link-inline">
                    <i class="fas fa-home"></i>
                    <span>Properties</span>
                </a>
            <?php endif; ?>
            <?php if ($enable_vehicles): ?>
                <a href="<?php echo SITE_URL; ?>/vehicles.php" class="category-link-inline">
                    <i class="fas fa-car"></i>
                    <span>Vehicles</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="header-actions">
            <?php if ($show_wishlist_icon): ?>
                <a href="<?php echo SITE_URL; ?>/wishlist.php" class="action-btn wishlist <?php echo $wishlist_count > 0 ? 'has-items' : ''; ?>" title="Wishlist">
                    <i class="fas fa-heart"></i>
                    <span class="badge <?php echo $wishlist_count > 0 ? 'active' : ''; ?>" id="wishlistBadge"><?php echo $wishlist_count; ?></span>
                </a>
            <?php endif; ?>

            <?php if ($show_cart_icon): ?>
                <a href="<?php echo SITE_URL; ?>/cart.php" class="action-btn cart <?php echo $cart_count > 0 ? 'has-items' : ''; ?>" title="Shopping Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="badge <?php echo $cart_count > 0 ? 'active' : ''; ?>" id="cartBadge"><?php echo $cart_count; ?></span>
                </a>
            <?php endif; ?>
        </div>

        <!-- AUTH BUTTONS OR USER DROPDOWN - DESKTOP ONLY -->
        <?php if (!$is_logged_in): ?>
            <div class="auth-buttons">
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn-login">Login</a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn-register">Register</a>
            </div>
        <?php else: ?>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-avatar-btn" onclick="toggleUserDropdown()">
                    <?php if (!empty($customer['profile_image'])): ?>
                        <img src="<?= SITE_URL . '/' . htmlspecialchars($customer['profile_image']) . '?v=' . time() ?>" 
                             alt="<?= htmlspecialchars($customer['name']) ?>" class="user-avatar-img">
                    <?php else: ?>
                        <div class="user-avatar-img" style="background: linear-gradient(135deg, var(--primary) 0%, #ea580c 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 16px;">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($customer['name']) ?></span>
                        <span class="user-email"><?= htmlspecialchars($customer['email']) ?></span>
                    </div>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/my-orders.php" class="dropdown-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My Orders</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/property-orders.php" class="dropdown-item">
                        <i class="fas fa-home"></i>
                        <span>Property Orders</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/my-bookings.php" class="dropdown-item">
                        <i class="fas fa-car"></i>
                        <span>My Bookings</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/edit-profile.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>


</header>

<!-- KIKUU STYLE SEARCH OVERLAY -->
<div class="search-overlay" id="searchOverlay">
    <div class="search-overlay-content">
        <div class="search-overlay-header">
            <button class="search-overlay-close" onclick="closeSearchOverlay()">
                <i class="fas fa-times"></i>
            </button>
            <div class="search-overlay-input-wrapper">
                <i class="fas fa-search search-overlay-icon"></i>
                <input type="text" class="search-overlay-input" id="overlaySearchInput" placeholder="Search for products, properties, vehicles..." autocomplete="off">
                <button class="search-overlay-clear" id="overlayClearBtn" onclick="clearOverlaySearch()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <div class="search-overlay-body">
            <!-- Recent Searches -->
            <div class="search-section" id="recentSearchSection">
                <div class="search-section-title">Recent Searches</div>
                <div class="recent-searches" id="recentSearchesList">
                    <!-- Recent searches will be populated here -->
                </div>
                <button class="clear-recent-btn" onclick="clearRecentSearches()" id="clearRecentBtn" style="display: none;">
                    <i class="fas fa-trash-alt"></i>
                    Clear All
                </button>
            </div>

            <!-- Search Suggestions -->
            <div class="search-section" id="suggestionsSection" style="display: none;">
                <div class="search-section-title">Suggestions</div>
                <div class="suggestion-list" id="suggestionsList">
                    <!-- Suggestions will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MOBILE BOTTOM NAVIGATION -->
<nav class="mobile-bottom-nav active" id="mobileBottomNav">
    <div class="nav-items">
        <a href="<?php echo SITE_URL; ?>" class="nav-item" data-page="home">
            <div class="nav-item-icon"><i class="fas fa-home"></i></div>
            <span>Home</span>
        </a>

        <?php if ($enable_products): ?>
            <a href="<?php echo SITE_URL; ?>/products.php" class="nav-item" data-page="products">
                <div class="nav-item-icon"><i class="fas fa-shopping-bag"></i></div>
                <span>Products</span>
            </a>
        <?php endif; ?>

        <?php if ($enable_properties): ?>
            <a href="<?php echo SITE_URL; ?>/properties.php" class="nav-item" data-page="properties">
                <div class="nav-item-icon"><i class="fas fa-home"></i></div>
                <span>Properties</span>
            </a>
        <?php endif; ?>

        <?php if ($enable_vehicles): ?>
            <a href="<?php echo SITE_URL; ?>/vehicles.php" class="nav-item" data-page="vehicles">
                <div class="nav-item-icon"><i class="fas fa-car"></i></div>
                <span>Vehicles</span>
            </a>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>
            <a href="<?php echo SITE_URL; ?>/profile.php" class="nav-item" data-page="profile">
                <div class="nav-item-icon">
                    <?php if (!empty($customer['profile_image'])): ?>
                        <img src="<?= SITE_URL . '/' . htmlspecialchars($customer['profile_image']) . '?v=' . time() ?>" 
                             alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/login.php" class="nav-item" data-page="login">
                <div class="nav-item-icon"><i class="fas fa-sign-in-alt"></i></div>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<main>

<script>
    // Set active nav item on mobile
    const currentPage = document.body.getAttribute('data-page') || 'home';
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        const page = item.getAttribute('data-page');
        if (page === currentPage || (currentPage === 'home' && page === 'home')) {
            item.classList.add('active');
        }
    });
    
    // User Dropdown Toggle
    function toggleUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('active');
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Modern Search with Autocomplete
    const headerSearch = document.getElementById('headerSearch');
    const searchSuggestions = document.getElementById('searchSuggestions');
    let searchTimeout;

    // Perform search function
    function performSearch() {
        const query = headerSearch.value.trim();
        if (query) {
            window.location.href = '<?php echo SITE_URL; ?>/search.php?q=' + encodeURIComponent(query);
        }
    }

    // Update Cart and Wishlist Badges
    function updateBadge(type, count) {
        const badge = document.getElementById(type + 'Badge');
        const actionBtn = badge ? badge.closest('.action-btn') : null;
        
        if (badge && actionBtn) {
            badge.textContent = count;
            
            if (count > 0) {
                badge.classList.add('active');
                actionBtn.classList.add('has-items');
            } else {
                badge.classList.remove('active');
                actionBtn.classList.remove('has-items');
            }
        }
    }

    // Function to fetch and update counts
    function updateHeaderCounts() {
        <?php if (isCustomerLoggedIn()): ?>
        fetch('<?php echo SITE_URL; ?>/api/get-counts.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateBadge('cart', data.cart_count || 0);
                    updateBadge('wishlist', data.wishlist_count || 0);
                }
            })
            .catch(error => console.error('Error updating counts:', error));
        <?php endif; ?>
    }

    // Update counts on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateHeaderCounts();
    });

    // Make updateBadge available globally for other pages to use
    window.updateHeaderBadge = updateBadge;
    window.updateHeaderCounts = updateHeaderCounts;

    if (headerSearch) {
        // Show suggestions on focus
        headerSearch.addEventListener('focus', function() {
            if (this.value.length >= 2) {
                fetchSuggestions(this.value);
            }
        });

        // Autocomplete on input
        headerSearch.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchSuggestions.classList.remove('active');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        });

        // Search on Enter
        headerSearch.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    window.location.href = '<?php echo SITE_URL; ?>/search.php?q=' + encodeURIComponent(query);
                }
            }
        });

        // Close suggestions on blur
        headerSearch.addEventListener('blur', function() {
            setTimeout(() => {
                searchSuggestions.classList.remove('active');
            }, 200);
        });
    }

    // Fetch suggestions from API
    function fetchSuggestions(query) {
        fetch('<?php echo SITE_URL; ?>/api/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                renderSuggestions(data.suggestions);
            })
            .catch(error => console.error('Search error:', error));
    }

    // Render suggestions
    function renderSuggestions(suggestions) {
        let html = '';

        // Products
        if (suggestions.products && suggestions.products.length > 0) {
            html += '<div class="suggestion-category">';
            html += '<div class="category-title"><i class="fas fa-shopping-bag"></i> Products</div>';
            suggestions.products.forEach(item => {
                html += `<a href="${item.url}" class="suggestion-item">`;
                html += `<div class="suggestion-item-icon"><i class="fas fa-${item.icon}"></i></div>`;
                html += `<div class="suggestion-item-text">${item.name}</div>`;
                html += `<div class="suggestion-item-type">Product</div>`;
                html += '</a>';
            });
            html += '</div>';
        }

        // Properties
        if (suggestions.properties && suggestions.properties.length > 0) {
            html += '<div class="suggestion-category">';
            html += '<div class="category-title"><i class="fas fa-home"></i> Properties</div>';
            suggestions.properties.forEach(item => {
                html += `<a href="${item.url}" class="suggestion-item">`;
                html += `<div class="suggestion-item-icon"><i class="fas fa-${item.icon}"></i></div>`;
                html += `<div class="suggestion-item-text">${item.name}</div>`;
                html += `<div class="suggestion-item-type">Property</div>`;
                html += '</a>';
            });
            html += '</div>';
        }

        // Vehicles
        if (suggestions.vehicles && suggestions.vehicles.length > 0) {
            html += '<div class="suggestion-category">';
            html += '<div class="category-title"><i class="fas fa-car"></i> Vehicles</div>';
            suggestions.vehicles.forEach(item => {
                html += `<a href="${item.url}" class="suggestion-item">`;
                html += `<div class="suggestion-item-icon"><i class="fas fa-${item.icon}"></i></div>`;
                html += `<div class="suggestion-item-text">${item.name}</div>`;
                html += `<div class="suggestion-item-type">Vehicle</div>`;
                html += '</a>';
            });
            html += '</div>';
        }

        if (html) {
            searchSuggestions.innerHTML = html;
            searchSuggestions.classList.add('active');
        } else {
            searchSuggestions.classList.remove('active');
        }
    }
    </script>

    <script>
    // ===== KIKUU STYLE SEARCH OVERLAY - ENHANCED =====
    const searchOverlay = document.getElementById('searchOverlay');
    const overlaySearchInput = document.getElementById('overlaySearchInput');
    const overlayClearBtn = document.getElementById('overlayClearBtn');
    const suggestionsSection = document.getElementById('suggestionsSection');
    const suggestionsList = document.getElementById('suggestionsList');
    const recentSearchSection = document.getElementById('recentSearchSection');
    const recentSearchesList = document.getElementById('recentSearchesList');
    const clearRecentBtn = document.getElementById('clearRecentBtn');
    let overlaySearchTimeout;
    let currentSearchQuery = '';

    // Open search overlay
    function openSearchOverlay() {
        searchOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            overlaySearchInput.focus();
        }, 150);
        loadRecentSearches();
    }

    // Close search overlay
    function closeSearchOverlay() {
        searchOverlay.classList.remove('active');
        document.body.style.overflow = '';
        overlaySearchInput.value = '';
        overlayClearBtn.classList.remove('active');
        suggestionsSection.style.display = 'none';
        currentSearchQuery = '';
    }

    // Clear overlay search input
    function clearOverlaySearch() {
        overlaySearchInput.value = '';
        overlayClearBtn.classList.remove('active');
        suggestionsSection.style.display = 'none';
        overlaySearchInput.focus();
        currentSearchQuery = '';
    }

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
            closeSearchOverlay();
        }
    });

    // Close on overlay click
    searchOverlay.addEventListener('click', function(e) {
        if (e.target === searchOverlay) {
            closeSearchOverlay();
        }
    });

    // Handle overlay search input
    if (overlaySearchInput) {
        overlaySearchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length > 0) {
                overlayClearBtn.classList.add('active');
            } else {
                overlayClearBtn.classList.remove('active');
                suggestionsSection.style.display = 'none';
                recentSearchSection.style.display = 'block';
                currentSearchQuery = '';
                return;
            }

            if (query.length >= 1) {
                recentSearchSection.style.display = 'none';
                clearTimeout(overlaySearchTimeout);
                overlaySearchTimeout = setTimeout(() => {
                    fetchOverlaySuggestions(query);
                }, 200);
            }
        });

        // Search on Enter
        overlaySearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    saveRecentSearch(query);
                    window.location.href = '<?php echo SITE_URL; ?>/search.php?q=' + encodeURIComponent(query);
                }
            }
        });
    }

    // Fetch suggestions for overlay
    function fetchOverlaySuggestions(query) {
        if (currentSearchQuery === query) return;
        currentSearchQuery = query;

        // Show loading state
        suggestionsList.innerHTML = `
            <div class="search-loading">
                <div class="search-loading-spinner"></div>
                <div class="search-loading-text">Searching...</div>
            </div>
        `;
        suggestionsSection.style.display = 'block';

        fetch('<?php echo SITE_URL; ?>/api/search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderOverlaySuggestions(data.suggestions, data.categories);
                } else {
                    showEmptyState();
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                showEmptyState();
            });
    }

    // Render overlay suggestions with images
    function renderOverlaySuggestions(suggestions, categories) {
        let html = '';
        let hasResults = false;

        // Products
        if (suggestions.products && suggestions.products.length > 0) {
            hasResults = true;
            suggestions.products.forEach(item => {
                const imageHtml = item.image 
                    ? `<img src="${item.image}" alt="${escapeHtml(item.name)}" class="suggestion-image" loading="lazy">` 
                    : `<div class="suggestion-icon"><i class="fas fa-box"></i></div>`;
                
                html += `
                    <a href="${item.url}" class="suggestion-list-item" onclick="saveRecentSearch('${escapeHtml(item.name).replace(/'/g, "\\'")}')">  
                        ${imageHtml}
                        <div class="suggestion-text">
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>${item.category || 'Product'}</small>
                            <span class="suggestion-price">${item.price}</span>
                        </div>
                    </a>
                `;
            });
        }

        // Properties
        if (suggestions.properties && suggestions.properties.length > 0) {
            hasResults = true;
            suggestions.properties.forEach(item => {
                const imageHtml = item.image 
                    ? `<img src="${item.image}" alt="${escapeHtml(item.name)}" class="suggestion-image" loading="lazy">` 
                    : `<div class="suggestion-icon"><i class="fas fa-home"></i></div>`;
                
                html += `
                    <a href="${item.url}" class="suggestion-list-item" onclick="saveRecentSearch('${escapeHtml(item.name).replace(/'/g, "\\'")}')">  
                        ${imageHtml}
                        <div class="suggestion-text">
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>${item.category || 'Property'}</small>
                            <span class="suggestion-price">${item.price}</span>
                            ${item.location ? `<div class="suggestion-meta"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(item.location)}</div>` : ''}
                        </div>
                    </a>
                `;
            });
        }

        // Vehicles
        if (suggestions.vehicles && suggestions.vehicles.length > 0) {
            hasResults = true;
            suggestions.vehicles.forEach(item => {
                const imageHtml = item.image 
                    ? `<img src="${item.image}" alt="${escapeHtml(item.name)}" class="suggestion-image" loading="lazy">` 
                    : `<div class="suggestion-icon"><i class="fas fa-car"></i></div>`;
                
                html += `
                    <a href="${item.url}" class="suggestion-list-item" onclick="saveRecentSearch('${escapeHtml(item.name).replace(/'/g, "\\'")}')">  
                        ${imageHtml}
                        <div class="suggestion-text">
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>${item.category || 'Vehicle'}</small>
                            <span class="suggestion-price">${item.price}</span>
                            ${item.vehicle_type ? `<div class="suggestion-meta"><i class="fas fa-car"></i> ${escapeHtml(item.vehicle_type)}</div>` : ''}
                        </div>
                    </a>
                `;
            });
        }

        if (hasResults) {
            suggestionsList.innerHTML = html;
            suggestionsSection.style.display = 'block';
        } else {
            showEmptyState();
        }
    }

    // Show empty state
    function showEmptyState() {
        suggestionsList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No results found. Try different keywords.</p>
            </div>
        `;
        suggestionsSection.style.display = 'block';
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Recent Searches Management
    function loadRecentSearches() {
        const recent = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        if (recent.length > 0) {
            let html = '';
            recent.forEach((search) => {
                html += `
                    <a href="<?php echo SITE_URL; ?>/search.php?q=${encodeURIComponent(search)}" class="recent-search-item">
                        ${escapeHtml(search)}
                    </a>
                `;
            });
            recentSearchesList.innerHTML = html;
            clearRecentBtn.style.display = 'inline-flex';
            recentSearchSection.style.display = 'block';
        } else {
            recentSearchesList.innerHTML = '<div class="empty-state"><i class="fas fa-clock"></i><p>No recent searches</p></div>';
            clearRecentBtn.style.display = 'none';
        }
    }

    function saveRecentSearch(query) {
        if (!query || query.trim() === '') return;
        
        let recent = JSON.parse(localStorage.getItem('recentSearches') || '[]');
        // Remove if already exists
        recent = recent.filter(item => item.toLowerCase() !== query.toLowerCase());
        // Add to beginning
        recent.unshift(query);
        // Keep only last 15
        recent = recent.slice(0, 15);
        localStorage.setItem('recentSearches', JSON.stringify(recent));
    }

    function clearRecentSearches() {
        if (confirm('Clear all recent searches?')) {
            localStorage.removeItem('recentSearches');
            loadRecentSearches();
        }
    }
    </script>
