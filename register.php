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
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $country = trim($_POST['country'] ?? 'Rwanda');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    
    // Store form data for repopulation
    $form_data = compact('name', 'email', 'phone', 'address', 'city', 'country');
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Full name is required';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Name must be at least 3 characters';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name must not exceed 100 characters';
    }
    
    if (empty($email)) {
        $errors[] = 'Email address is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif (strlen($email) > 100) {
        $errors[] = 'Email must not exceed 100 characters';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]{7,}$/', $phone)) {
        $errors[] = 'Invalid phone number format';
    } elseif (strlen($phone) > 20) {
        $errors[] = 'Phone number must not exceed 20 characters';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    } elseif (strlen($password) > 255) {
        $errors[] = 'Password is too long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!$agree_terms) {
        $errors[] = 'You must agree to the Terms and Conditions';
    }
    
    // Optional field validation
    if (!empty($city) && strlen($city) > 100) {
        $errors[] = 'City name must not exceed 100 characters';
    }
    
    if (!empty($country) && strlen($country) > 100) {
        $errors[] = 'Country name must not exceed 100 characters';
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This email is already registered. Please <a href="login.php' . (isset($_SESSION['redirect_after_login']) ? '?redirect=' . urlencode($_SESSION['redirect_after_login']) : '') . '" style="color: #ea580c; font-weight: 600;">login</a> instead.';
        }
        $stmt->close();
    }
    
    // Check if phone already exists (optional check)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This phone number is already registered';
        }
        $stmt->close();
    }
    
    // Insert user with all fields from users table
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active'; // Default status
        
        // Prepare SQL with all user table fields
        $sql = "INSERT INTO users (name, email, phone, password, address, city, country, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $name, $email, $phone, $hashed_password, $address, $city, $country, $status);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Log the registration activity
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $log_sql = "INSERT INTO activity_logs (user_type, user_id, action, description, ip_address, user_agent, created_at) 
                        VALUES ('customer', ?, 'REGISTER', 'New customer registration', ?, ?, NOW())";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("iss", $user_id, $ip_address, $user_agent);
            $log_stmt->execute();
            $log_stmt->close();
            
            $success = 'Registration successful! Redirecting to login...';
            
            // Clear form data on success
            $form_data = [];
            
            // Redirect to login with redirect parameter if exists
            $redirect_param = isset($_SESSION['redirect_after_login']) ? '?redirect=' . urlencode($_SESSION['redirect_after_login']) : '';
            header("refresh:2;url=login.php{$redirect_param}");
        } else {
            $errors[] = 'Registration failed. Please try again later.';
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
    <title>Create Account - Zuba Online Market</title>
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        
        .password-hint {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #6b7280;
        }
        
        .checkbox-group {
            margin-top: 20px;
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            font-weight: 400;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #f97316;
        }
        
        .checkbox-text {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
        }
        
        .checkbox-text .link {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
        }
        
        .checkbox-text .link:hover {
            color: #ea580c;
            text-decoration: underline;
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
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .login-link a {
            color: #f97316;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #ea580c;
            text-decoration: underline;
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
            <h1>Create Account</h1>
            <p>Join Zuba Online Market today</p>
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
                    <label for="name">Full Name <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="name" name="name" class="form-control" 
                               placeholder="Enter your full name" 
                               value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" 
                               maxlength="100" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="example@email.com" 
                               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" 
                               maxlength="100" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="+250788000000" 
                               value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>" 
                               maxlength="20" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address (Optional)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" id="address" name="address" class="form-control" 
                               placeholder="Street address" 
                               value="<?= htmlspecialchars($form_data['address'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City (Optional)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-city input-icon"></i>
                            <input type="text" id="city" name="city" class="form-control" 
                                   placeholder="Kigali" 
                                   value="<?= htmlspecialchars($form_data['city'] ?? '') ?>" 
                                   maxlength="100">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country (Optional)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-globe input-icon"></i>
                            <input type="text" id="country" name="country" class="form-control" 
                                   placeholder="Rwanda" 
                                   value="<?= htmlspecialchars($form_data['country'] ?? 'Rwanda') ?>" 
                                   maxlength="100">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Minimum 6 characters" required>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="password-icon"></i>
                        </span>
                    </div>
                    <small class="password-hint">Use at least 6 characters with a mix of letters and numbers</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               placeholder="Re-enter your password" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye" id="confirm_password-icon"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" id="agree_terms" required>
                        <span class="checkbox-text">I agree to the <a href="#" class="link">Terms and Conditions</a> and <a href="#" class="link">Privacy Policy</a></span>
                    </label>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
            
            <div class="divider">
                <span>OR</span>
            </div>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign In</a>
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
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Real-time password match validation
            confirmPassword.addEventListener('input', function() {
                if (this.value && password.value !== this.value) {
                    this.style.borderColor = '#ef4444';
                } else if (this.value && password.value === this.value) {
                    this.style.borderColor = '#10b981';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });
            
            // Password strength indicator
            password.addEventListener('input', function() {
                if (this.value.length >= 6) {
                    this.style.borderColor = '#10b981';
                } else if (this.value.length > 0) {
                    this.style.borderColor = '#f59e0b';
                } else {
                    this.style.borderColor = '#e0e0e0';
                }
            });
        });
    </script>
</body>
</html>
