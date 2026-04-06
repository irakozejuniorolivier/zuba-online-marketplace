<?php
// $page_title must be set before including this file
$page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — <?= e(SITE_NAME) ?> Admin</title>
    <style>
        /* =============================================
           ZUBA ADMIN — GLOBAL STYLES
           Theme: White + Orange (#f97316 / #ea580c)
        ============================================= */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --orange:      #f97316;
            --orange-dark: #ea580c;
            --orange-light:#fff7ed;
            --sidebar-w:   250px;
            --topbar-h:    64px;
            --dark:        #1a1a2e;
            --dark2:       #16213e;
            --text:        #111827;
            --text-muted:  #6b7280;
            --border:      #e5e7eb;
            --bg:          #f8f9fb;
            --white:       #ffffff;
            --radius:      10px;
            --shadow:      0 1px 4px rgba(0,0,0,.08);
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--dark);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 200;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform .28s ease;
            scrollbar-width: thin;
            scrollbar-color: #374151 transparent;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            min-height: var(--topbar-h);
            flex-shrink: 0;
        }
        .sidebar-brand img {
            height: 38px;
            width: auto;
            object-fit: contain;
            background: #fff;
            border-radius: 8px;
            padding: 4px 8px;
        }
        .sidebar-close {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #9ca3af;
        }
        .sidebar-close svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; }
        .sidebar-close:hover { color: #fff; }

        .sidebar-nav { flex: 1; padding: 10px 0 16px; }

        .nav-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #4b5563;
            padding: 14px 18px 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 18px;
            color: #9ca3af;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all .18s;
            position: relative;
        }
        .sidebar-nav a:hover {
            background: rgba(255,255,255,.05);
            color: #e5e7eb;
            border-left-color: rgba(249,115,22,.5);
        }
        .sidebar-nav a.active {
            background: rgba(249,115,22,.12);
            color: #fff;
            border-left-color: var(--orange);
        }
        .sidebar-nav a.active .nav-icon svg { stroke: var(--orange); }

        .nav-icon {
            width: 20px; height: 20px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .nav-icon svg {
            width: 16px; height: 16px;
            stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--orange);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
        }
        .sidebar-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--orange);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-info strong {
            display: block;
            font-size: 13px;
            color: #e5e7eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-info span { font-size: 11px; color: #6b7280; }
        .sidebar-logout {
            color: #6b7280;
            transition: color .2s;
            flex-shrink: 0;
        }
        .sidebar-logout:hover { color: #f87171; }
        .sidebar-logout svg { width: 17px; height: 17px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 199;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.show { display: block; }

        /* ===== MAIN WRAPPER ===== */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            height: var(--topbar-h);
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            color: var(--text);
            transition: background .2s;
        }
        .topbar-hamburger:hover { background: var(--orange-light); color: var(--orange); }
        .topbar-hamburger svg { width: 22px; height: 22px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; display: block; }

        .topbar-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .topbar-date {
            font-size: 12.5px;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .topbar-admin {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--orange-light);
            border: 1px solid #fed7aa;
            border-radius: 20px;
            padding: 5px 12px 5px 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--orange-dark);
            white-space: nowrap;
        }
        .topbar-admin-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--orange);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* ===== PAGE CONTENT ===== */
        .page-content { padding: 24px; flex: 1; }

        /* ===== CARDS ===== */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .card-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header h4 { font-size: 14px; font-weight: 700; color: var(--text); }
        .card-header a  { font-size: 12.5px; color: var(--orange); text-decoration: none; font-weight: 600; }
        .card-header a:hover { color: var(--orange-dark); }

        /* ===== TABLES ===== */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 400px; }
        th {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .6px;
            color: var(--text-muted);
            padding: 10px 16px;
            text-align: left;
            background: #fafafa;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        td {
            font-size: 13px;
            padding: 11px 16px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #fffbf7; }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-success   { background: #dcfce7; color: #15803d; }
        .badge-warning   { background: #fef9c3; color: #a16207; }
        .badge-info      { background: #e0f2fe; color: #0369a1; }
        .badge-primary   { background: #fff7ed; color: var(--orange-dark); }
        .badge-danger    { background: #fee2e2; color: #dc2626; }
        .badge-secondary { background: #f3f4f6; color: #6b7280; }

        /* ===== ALERTS ===== */
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-error   { background: #fff1f1; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }

        /* ===== MOBILE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,.3); }
            .sidebar-close { display: flex; }
            .main-wrap { margin-left: 0; }
            .topbar { padding: 0 14px; gap: 10px; height: 56px; }
            .topbar-hamburger { display: flex; }
            .topbar-date { display: none; }
            .page-content { padding: 14px; }
        }
        @media (max-width: 480px) {
            .topbar-title { font-size: 15px; }
            .topbar-admin { padding: 4px 10px 4px 5px; font-size: 12px; gap: 6px; }
            .topbar-admin-avatar { width: 24px; height: 24px; font-size: 10px; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<div class="main-wrap">

    <!-- Topbar -->
    <header class="topbar">
        <button class="topbar-hamburger" onclick="openSidebar()" aria-label="Open menu">
            <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title"><?= e($page_title) ?></div>
        <div class="topbar-right">
            <span class="topbar-date"><?= date('D, d M Y') ?></span>
            <div class="topbar-admin">
                <div class="topbar-admin-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
                <?= e(explode(' ', $admin['name'])[0]) ?>
            </div>
        </div>
    </header>

    <!-- Page content starts here -->
    <div class="page-content">
