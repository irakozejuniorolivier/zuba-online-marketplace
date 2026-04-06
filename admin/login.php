<?php
session_start();
require_once '../config/config.php';
require_once '../config/db.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

requireGuestAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin']    = $admin;
            $conn->query("UPDATE admins SET last_login = NOW() WHERE id = {$admin['id']}");
            logActivity('admin', $admin['id'], 'login', 'Admin logged in');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= SITE_NAME ?></title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f8f8f8;
        }

        /* ===== LEFT PANEL ===== */
        .panel-left {
            width: 52%;
            background: linear-gradient(160deg, #ff8c00 0%, #ea580c 60%, #c2410c 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            position: relative;
            overflow: hidden;
        }

        /* decorative circles */
        .panel-left::before,
        .panel-left::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
        }
        .panel-left::before { width: 500px; height: 500px; top: -180px; left: -160px; }
        .panel-left::after  { width: 360px; height: 360px; bottom: -120px; right: -100px; }

        .panel-left .inner { position: relative; z-index: 2; width: 100%; max-width: 360px; text-align: center; }

        /* logo box */
        .logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 20px;
            padding: 14px 28px;
            margin-bottom: 36px;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
        }
        .logo-box img {
            height: 56px;
            width: auto;
            display: block;
            object-fit: contain;
        }

        .panel-left h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            line-height: 1.35;
            margin-bottom: 12px;
        }
        .panel-left .tagline {
            font-size: 14.5px;
            color: rgba(255,255,255,.82);
            line-height: 1.65;
            margin-bottom: 44px;
        }

        /* feature list */
        .features { display: flex; flex-direction: column; gap: 12px; text-align: left; }
        .feat {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255,255,255,.14);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 12px;
            padding: 13px 18px;
            color: #fff;
            font-size: 13.5px;
            font-weight: 500;
        }
        .feat-icon {
            width: 36px; height: 36px;
            background: rgba(255,255,255,.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        /* ===== RIGHT PANEL ===== */
        .panel-right {
            width: 48%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 48px;
            background: #fff;
        }

        .form-box {
            width: 100%;
            max-width: 400px;
        }

        /* heading */
        .form-box .greet {
            font-size: 13px;
            font-weight: 600;
            color: #f97316;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 8px;
        }
        .form-box h2 {
            font-size: 30px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }
        .form-box .sub {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 36px;
        }

        /* alerts */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .alert-error   { background: #fff1f1; border: 1px solid #fecaca; color: #b91c1c; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
        .alert-icon { font-size: 16px; margin-top: 1px; flex-shrink: 0; }

        /* form fields */
        .field { margin-bottom: 22px; }

        .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: .7px;
            margin-bottom: 8px;
        }

        .field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-wrap .f-icon {
            position: absolute;
            left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            pointer-events: none;
        }
        /* SVG icons */
        .field-wrap .f-icon svg { width: 18px; height: 18px; stroke: #f97316; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        .field-wrap input {
            width: 100%;
            height: 52px;
            padding: 0 50px 0 46px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14.5px;
            color: #111827;
            background: #fafafa;
            outline: none;
            transition: border-color .22s, box-shadow .22s, background .22s;
        }
        .field-wrap input::placeholder { color: #d1d5db; }
        .field-wrap input:focus {
            border-color: #f97316;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(249,115,22,.12);
        }

        /* password toggle */
        .toggle-btn {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            color: #9ca3af;
            transition: color .2s;
            border-radius: 6px;
        }
        .toggle-btn:hover { color: #f97316; background: #fff7ed; }
        .toggle-btn svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }

        /* submit */
        .btn-submit {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15.5px;
            font-weight: 700;
            letter-spacing: .4px;
            cursor: pointer;
            margin-top: 4px;
            box-shadow: 0 4px 18px rgba(234,88,12,.35);
            transition: box-shadow .22s, transform .12s, opacity .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover  { box-shadow: 0 6px 24px rgba(234,88,12,.5); opacity: .95; }
        .btn-submit:active { transform: scale(.98); }
        .btn-submit svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }

        /* divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 28px 0 20px;
            color: #e5e7eb;
            font-size: 12px;
            color: #d1d5db;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

        /* back link */
        .back-link {
            text-align: center;
            font-size: 13.5px;
            color: #9ca3af;
        }
        .back-link a {
            color: #f97316;
            font-weight: 700;
            text-decoration: none;
            transition: color .2s;
        }
        .back-link a:hover { color: #ea580c; text-decoration: underline; }

        /* responsive */
        @media (max-width: 820px) {
            body { flex-direction: column; }
            .panel-left  { width: 100%; padding: 40px 28px 36px; }
            .panel-left .features { display: none; }
            .panel-right { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>

<!-- ===== LEFT PANEL ===== -->
<div class="panel-left">
    <div class="inner">

        <div class="logo-box">
            <img src="<?= SITE_URL ?>/logo/logo.jpg" alt="<?= e(SITE_NAME) ?>">
        </div>

        <h1>Welcome Back,<br>Admin 👋</h1>
        <p class="tagline">Sign in to manage your marketplace —<br>products, properties, vehicles & more.</p>

        <div class="features">
            <div class="feat">
                <div class="feat-icon">📦</div>
                <span>Manage Products, Properties & Vehicles</span>
            </div>
            <div class="feat">
                <div class="feat-icon">💳</div>
                <span>Review & Approve Payments</span>
            </div>
            <div class="feat">
                <div class="feat-icon">📊</div>
                <span>Analytics & Sales Reports</span>
            </div>
            <div class="feat">
                <div class="feat-icon">👥</div>
                <span>Customer & Order Management</span>
            </div>
        </div>

    </div>
</div>

<!-- ===== RIGHT PANEL ===== -->
<div class="panel-right">
    <div class="form-box">

        <p class="greet">Admin Portal</p>
        <h2>Sign In</h2>
        <p class="sub">Enter your credentials to continue</p>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <span class="alert-icon">
                <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:#b91c1c;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </span>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>

            <!-- Email -->
            <div class="field">
                <label for="email">Email Address</label>
                <div class="field-wrap">
                    <span class="f-icon">
                        <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
                    </span>
                    <input type="email" id="email" name="email"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           placeholder="admin@zubamarket.com"
                           autocomplete="email" required>
                </div>
            </div>

            <!-- Password -->
            <div class="field">
                <label for="password">Password</label>
                <div class="field-wrap">
                    <span class="f-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-btn" id="toggleBtn" onclick="togglePass()" title="Show / Hide password">
                        <!-- eye open -->
                        <svg id="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <!-- eye off (hidden by default) -->
                        <svg id="icon-eye-off" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Sign In
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>

        </form>

        <div class="divider">or</div>

        <p class="back-link">
            ← <a href="<?= SITE_URL ?>">Back to Website</a>
        </p>

    </div>
</div>

<script>
    function togglePass() {
        const input  = document.getElementById('password');
        const eyeOn  = document.getElementById('icon-eye');
        const eyeOff = document.getElementById('icon-eye-off');
        const isHidden = input.type === 'password';
        input.type       = isHidden ? 'text' : 'password';
        eyeOn.style.display  = isHidden ? 'none'  : '';
        eyeOff.style.display = isHidden ? ''      : 'none';
    }
</script>

</body>
</html>
