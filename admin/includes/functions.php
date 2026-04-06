<?php
function e($str) {
    if (is_array($str) || is_object($str)) {
        return '';
    }
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
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

function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uploadFile($file, $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if ($file['error'] !== 0) return ['success' => false, 'error' => 'Upload error'];
    if ($file['size'] > MAX_UPLOAD_SIZE) return ['success' => false, 'error' => 'File too large'];
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return ['success' => false, 'error' => 'Invalid file type'];
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'error' => 'Failed to save file'];
}

