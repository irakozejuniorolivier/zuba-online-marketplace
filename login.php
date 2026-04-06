<?php
session_start();
require_once 'config/db.php';
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

// Store redirect URL in session if provided
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = $_GET['redirect'];
}

$errors = [];
$success = '';
$email = '';

// Handle flash messages
$flash = getFlash();
if ($flash) {
    if ($flash['type'] === 'success') {
        $success = $flash['message'];
    } else {
        $errors[] = $flash['message'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Get redirect URL from query parameter or session
    $redirect_url = $_GET['redirect'] ?? $_SESSION['redirect_after_login'] ?? SITE_URL . '/index.php';
    
    // Validation
    if (empty($email)) {
        $errors[] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // Authenticate user
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, email, phone, password, profile_image, address, city, country, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $status_message = $user['status'] === 'suspended' ? 'suspended' : 'inactive';
                $errors[] = "Your account is {$status_message}. Please contact support.";
            }
            // Verify password
            elseif (password_verify($password, $user['password'])) {
                // Update last login
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Set session variables
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'profile_image' => $user['profile_image'],
                    'address' => $user['address'],
                    'city' => $user['city'],
                    'country' => $user['country']
                ];
                
                // Log activity
                logActivity($conn, 'customer', $user['id'], 'LOGIN', 'Customer logged in successfully');
                
                // Handle remember me (optional - implement cookie logic if needed)
                if ($remember_me) {
                    // Set a secure cookie for 30 days
                    // setcookie('remember_token', $secure_token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                }
                
                // Redirect to intended page or home
                $redirect_to = $redirect_url;
                unset($_SESSION['redirect_after_login']);
                
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect($redirect_to);
            } else {
                $errors[] = 'Invalid email or password';
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zuba Online Market</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #ea580c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            background: linear-gradient(135deg, #f97316 0%, #fb923c 50%, #ea580c 100%);
            padding: 35px 30px 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .logo-container {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .logo-wrapper {
            width: 90px;
            height: 90px;
            margin: 0 auto;
            background: white;
            border-radius: 50%;
            padding: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            animation: logoAppear 0.6s ease-out;
            position: relative;
        }
        
        @keyframes logoAppear {
            from {
                opacity: 0;
                transform: scale(0.5) rotate(-180deg);
            }
            to {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }
        
        .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1));
            z-index: -1;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .logo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .form-container {
            padding: 40px 30px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label span {
            color: #e74c3c;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #f97316;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus ~ .input-icon {
            color: #ea580c;
            transform: translateY(-50%) scale(1.1);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        
        .form-control::placeholder {
            color: #aaa;
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            user-select: none;
        }
        
        .password-toggle:hover {
            color: #f97316;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #4b5563;
            cursor: pointer;
        }
        
        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #f97316;
        }
        
        .forgot-password {
            font-size: 14px;
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #ea580c;
            text-decoration: underline;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #999;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .register-link a {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #ea580c;
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .container {
                border-radius: 0;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .form-container {
                padding: 30px 20px;
            }
            
            .form-footer {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-container">
                <div class="logo-wrapper">
                    <img src="logo/logo.jpg" alt="Zuba Online Market Logo">
                </div>
            </div>
            <h1>Welcome Back</h1>
            <p>Login to your account</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div>• <?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Enter your email" 
                               value="<?= htmlspecialchars($email) ?>" 
                               maxlength="100" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Enter your password" required>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-footer">
                    <label class="remember-me">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="register-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Add input validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            
            // Email validation feedback
            email.addEventListener('blur', function() {
                if (this.value && !this.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    this.style.borderColor = '#ef4444';
                } else if (this.value) {
                    this.style.borderColor = '#10b981';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });
            
            // Reset border on focus
            email.addEventListener('focus', function() {
                this.style.borderColor = '#f97316';
            });
            
            password.addEventListener('focus', function() {
                this.style.borderColor = '#f97316';
            });
        });
    </script>
</body>
</html>
