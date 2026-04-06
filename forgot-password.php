<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (isCustomerLoggedIn()) redirect(SITE_URL . '/index.php');

$error = '';
$reset_link = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT id, name, status FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Always show success to prevent email enumeration
        $submitted = true;

        if ($user && $user['status'] === 'active') {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->bind_param('ssi', $token, $expires, $user['id']);
            $stmt->execute();
            $stmt->close();

            $reset_link = SITE_URL . '/reset-password.php?token=' . $token;

            logActivity($conn, 'customer', $user['id'], 'PASSWORD_RESET_REQUEST', 'Password reset requested');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Zuba Online Market</title>
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
        .alert-error { background: #fee; color: #c33; border: 1px solid #fcc; }
        .alert-success { background: #eff; color: #0369a1; border: 1px solid #bae6fd; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; font-size: 14px; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #f97316; font-size: 18px; }
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: inherit;
        }
        .form-control:focus { outline: none; border-color: #f97316; box-shadow: 0 0 0 4px rgba(249,115,22,0.1); }
        .form-control::placeholder { color: #aaa; }
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
        .back-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .back-link a { color: #f97316; text-decoration: none; font-weight: 600; }
        .back-link a:hover { color: #ea580c; text-decoration: underline; }

        /* Reset link box */
        .reset-link-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .reset-link-box p { font-size: 13px; color: #166534; margin-bottom: 10px; font-weight: 600; }
        .reset-link-url {
            display: block;
            background: #fff;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 12px;
            color: #1d4ed8;
            word-break: break-all;
            text-decoration: none;
            margin-bottom: 10px;
            font-family: monospace;
        }
        .reset-link-url:hover { background: #f0f9ff; }
        .copy-btn {
            width: 100%;
            padding: 10px;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .copy-btn:hover { background: #15803d; }
        .copy-btn.copied { background: #0369a1; }
        .expires-note { font-size: 12px; color: #6b7280; margin-top: 8px; text-align: center; }

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
        <h1>Forgot Password?</h1>
        <p>Enter your email to reset your password</p>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($submitted): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                If that email is registered, a reset link has been generated below.
            </div>

            <?php if ($reset_link): ?>
                <div class="reset-link-box">
                    <p><i class="fas fa-link"></i> Your password reset link:</p>
                    <a href="<?= htmlspecialchars($reset_link) ?>" class="reset-link-url" id="resetLink"><?= htmlspecialchars($reset_link) ?></a>
                    <button class="copy-btn" id="copyBtn" onclick="copyLink()">
                        <i class="fas fa-copy"></i> Copy Link
                    </button>
                    <p class="expires-note"><i class="fas fa-clock"></i> This link expires in 1 hour</p>
                </div>
                <a href="<?= htmlspecialchars($reset_link) ?>" class="btn" style="display:block; text-align:center; text-decoration:none; margin-top:0;">
                    <i class="fas fa-key"></i> Go to Reset Page
                </a>
            <?php endif; ?>

        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address <span style="color:#e74c3c">*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="Enter your registered email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               maxlength="100" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const link = document.getElementById('resetLink').href;
    const btn = document.getElementById('copyBtn');
    navigator.clipboard.writeText(link).then(() => {
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy Link';
            btn.classList.remove('copied');
        }, 2000);
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = link;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy Link';
            btn.classList.remove('copied');
        }, 2000);
    });
}
</script>
</body>
</html>
