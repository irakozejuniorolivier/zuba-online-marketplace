<?php
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 0);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y, H:i', strtotime($datetime));
}

function generateOrderNumber($prefix = 'ORD') {
    return $prefix . '-' . strtoupper(uniqid());
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    if ($time < 60) return $time . 's ago';
    if ($time < 3600) return floor($time / 60) . 'm ago';
    if ($time < 86400) return floor($time / 3600) . 'h ago';
    return floor($time / 86400) . 'd ago';
}

function statusBadge($status) {
    $map = [
        'active'            => 'success',
        'available'         => 'success',
        'approved'          => 'success',
        'completed'         => 'success',
        'delivered'         => 'success',
        'inactive'          => 'secondary',
        'pending_payment'   => 'warning',
        'payment_submitted' => 'info',
        'processing'        => 'primary',
        'shipped'           => 'primary',
        'rented'            => 'primary',
        'active'            => 'primary',
        'rejected'          => 'danger',
        'cancelled'         => 'danger',
        'suspended'         => 'danger',
        'out_of_stock'      => 'danger',
        'maintenance'       => 'warning',
        'sold'              => 'secondary',
        'pending'           => 'warning',
    ];
    $color = $map[$status] ?? 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span class=\"badge badge-{$color}\">{$label}</span>";
}

function getSetting($key, $default = '') {
    global $conn;
    static $settings_cache = [];
    
    if (isset($settings_cache[$key])) {
        return $settings_cache[$key];
    }
    
    $key_escaped = $conn->real_escape_string($key);
    $result = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = '$key_escaped' LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $settings_cache[$key] = $row['setting_value'];
        return $row['setting_value'];
    }
    
    $settings_cache[$key] = $default;
    return $default;
}

function getLogoUrl() {
    $logo = getSetting('site_logo');
    if (!empty($logo)) {
        return UPLOAD_URL . 'logos/' . $logo;
    }
    return SITE_URL . '/assets/images/default-logo.png';
}

function getFaviconUrl() {
    $favicon = getSetting('site_favicon');
    if (!empty($favicon)) {
        return UPLOAD_URL . 'logos/' . $favicon;
    }
    return SITE_URL . '/assets/images/favicon.ico';
}
