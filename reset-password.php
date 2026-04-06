<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isCustomerLoggedIn()) redirect(SITE_URL . '/index.php');

$token = trim($_GET['token'] ?? '');
$error = '';
$success = '';
$valid_token = false;
$user = null;

// Validate token
if (empty($token) || strlen($token) !== 64 || !ctype_xdigit($token)) {
    $error = 'Invalid or missing reset token.';
} else {
    $stmt = $conn->prepare("SELECT id, name, email, status FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $error = 'This reset link is invalid or has expired. Please request a new one.';
    } elseif ($user['status'] !== 'active') {
        $error = 'Your account is not active. Please contact support.';
    } else {
        $valid_token = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->bind_param('si', $hashed, $user['id']);
        $stmt->execute();
        $stmt->close();

        logActivity($conn, 'customer', $user['id'], 'PASSWORD_RESET', 'Password reset successfully');

        $success = 'Your password has been reset successfully. You can now log in.';
        $valid_token = false; // hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Zuba Online Market</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #ea580c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .header {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #ea580c 100%);
            padding: 35px 30px 40px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .logo-wrapper {
            width: 90px; height: 90px;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 50%;
            padding: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            position: relative; z-index: 1;
        }
        .logo-wrapper img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        .header h1 { font-size: 26px; font-weight: 700; margin-bottom: 8px; position: relative; z-index: 1; }
        .header p { font-size: 14px; opacity: 0.95; position: relative; z-index: 1; }
        .form-container { padding: 40px 30px; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error   { background: #fee; color: #c33; border: 1px solid #fcc; }
        .alert-success { background: #eff; color: #0369a1; border: 1px solid #bae6fd; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #f97316; font-size: 18px; }
        .form-control {
            width: 100%;
            padding: 14px 44px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }
        .form-control:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249,115,22,0.1); }
        .form-control::placeholder { color: #aaa; }
        .toggle-pw {
            position: absolute;
            right: 16px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            user-select: none;
        }
        .toggle-pw:hover { color: #f97316; }
        .password-hint { display: block; margin-top: 6px; font-size: 12px; color: #6b7280; }
        .strength-bar { height: 4px; border-radius: 4px; margin-top: 8px; background: #e5e7eb; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 4px; transition: all 0.3s; width: 0; }
        .strength-label { font-size: 11px; margin-top: 4px; font-weight: 600; }
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(249,115,22,0.4); }
        .btn-login {
            display: block;
            text-align: center;
            text-decoration: none;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }
        .btn-login:hover { box-shadow: 0 10px 25px rgba(16,185,129,0.4); }
        .back-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .back-link a { color: #f97316; text-decoration: none; font-weight: 600; }
        .back-link a:hover { color: #ea580c; text-decoration: underline; }
        .user-greeting { background: #fff5f0; border: 1px solid rgba(249,115,22,0.2); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 14px; color: #1a1a2e; }
        .user-greeting strong { color: #f97316; }
        @media (max-width: 480px) {
            .container { border-radius: 0; }
            .header { padding: 30px 20px; }
            .form-container { padding: 30px 20px; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo-wrapper">
            <img src="logo/logo.jpg" alt="Zuba Online Market">
        </div>
        <h1>Reset Password</h1>
        <p>Create a new secure password</p>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <div class="back-link" style="margin-top:0; margin-bottom:20px;">
                <a href="forgot-password.php"><i class="fas fa-redo"></i> Request a new reset link</a>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        <?php endif; ?>

        <?php if ($valid_token): ?>
            <div class="user-greeting">
                <i class="fas fa-user-circle"></i> Resetting password for <strong><?= htmlspecialchars($user['name']) ?></strong>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password <span style="color:#e74c3c">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Minimum 6 characters" required oninput="checkStrength(this.value)">
                        <span class="toggle-pw" onclick="togglePw('password', 'icon1')">
                            <i class="fas fa-eye" id="icon1"></i>
                        </span>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <span class="strength-label" id="strengthLabel"></span>
                    <small class="password-hint">Use at least 6 characters with letters and numbers</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password <span style="color:#e74c3c">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                               placeholder="Re-enter your new password" required>
                        <span class="toggle-pw" onclick="togglePw('confirm_password', 'icon2')">
                            <i class="fas fa-eye" id="icon2"></i>
                        </span>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-top:-10px;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <button type="submit" class="btn">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        <?php endif; ?>

        <?php if (!$success): ?>
            <div class="back-link">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePw(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    if (f.type === 'password') {
        f.type = 'text';
        i.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        f.type = 'password';
        i.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    if (!fill) return;
    let score = 0;
    if (val.length >= 6)  score++;
    if (val.length >= 10) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { w: '20%', color: '#ef4444', text: 'Very Weak' },
        { w: '40%', color: '#f97316', text: 'Weak' },
        { w: '60%', color: '#f59e0b', text: 'Fair' },
        { w: '80%', color: '#10b981', text: 'Strong' },
        { w: '100%', color: '#059669', text: 'Very Strong' },
    ];
    const lvl = levels[Math.max(0, score - 1)] || levels[0];
    fill.style.width = val.length ? lvl.w : '0';
    fill.style.background = lvl.color;
    label.textContent = val.length ? lvl.text : '';
    label.style.color = lvl.color;
}

// Real-time confirm match
document.addEventListener('DOMContentLoaded', () => {
    const pw  = document.getElementById('password');
    const cpw = document.getElementById('confirm_password');
    if (!cpw) return;
    cpw.addEventListener('input', function() {
        if (this.value && pw.value !== this.value) {
            this.style.borderColor = '#ef4444';
        } else if (this.value) {
            this.style.borderColor = '#10b981';
        } else {
            this.style.borderColor = '#e0e0e0';
        }
    });
});
</script>
</body>
</html>
