<?php
session_start();

$host = 'localhost';
$dbuser = 'root';
$dbpass = '';
$database = 'zuba_market';

$conn = new mysqli($host, $dbuser, $dbpass, $database);
if ($conn->connect_error) die('DB Error: ' . $conn->connect_error);

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    echo "<h3>Debug Info:</h3><pre>";
    if ($admin) {
        echo "Admin found: YES\n";
        echo "Name: " . $admin['name'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Status: " . $admin['status'] . "\n";
        echo "Password length: " . strlen($admin['password']) . "\n";
        echo "Password starts with: " . substr($admin['password'], 0, 7) . "\n";
        $verify = password_verify($password, $admin['password']);
        echo "password_verify result: " . ($verify ? "TRUE ✅" : "FALSE ❌") . "\n";

        if ($verify && $admin['status'] === 'active') {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin']    = $admin;
            echo "</pre>";
            echo "<p style='color:green;font-size:20px'>✅ LOGIN SUCCESS! Redirecting...</p>";
            echo "<script>setTimeout(()=>{ window.location='admin/index.php'; }, 2000);</script>";
            exit;
        } elseif (!$verify) {
            // Force reset password to what user typed
            if (isset($_POST['force_reset'])) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("UPDATE admins SET password='$hash', status='active' WHERE email='" . $conn->real_escape_string($email) . "'");
                echo "Password force-reset to: $password\n";
                echo "New hash: $hash\n";
                echo "Verify after reset: " . (password_verify($password, $hash) ? "TRUE ✅" : "FALSE ❌") . "\n";
            }
        } elseif ($admin['status'] !== 'active') {
            echo "PROBLEM: Admin status is not active!\n";
            $conn->query("UPDATE admins SET status='active' WHERE email='" . $conn->real_escape_string($email) . "'");
            echo "Status fixed to active.\n";
        }
    } else {
        echo "Admin found: NO\n";
        echo "No admin with email: $email\n\n";
        echo "All admin emails in DB:\n";
        $r = $conn->query("SELECT email, status FROM admins");
        while ($row = $r->fetch_assoc()) {
            echo "  - " . $row['email'] . " (status: " . $row['status'] . ")\n";
        }
    }
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Debug Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 60px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 8px 0 16px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #f97316; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-bottom: 10px; }
        label { font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
<h2>Admin Debug Login</h2>
<form method="POST">
    <label>Email:</label>
    <input type="text" name="email" value="admin@zubamarket.com">
    <label>Password:</label>
    <input type="text" name="password" value="">
    <button type="submit">Test Login</button>
    <button type="submit" name="force_reset" value="1" style="background:#dc2626">Force Reset Password to Above</button>
</form>
<p style="color:#666;font-size:13px">⚠️ Delete this file after use.</p>
</body>
</html>
