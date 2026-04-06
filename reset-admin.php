<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'zuba_market';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die('<p style="color:red">DB Connection FAILED: ' . $conn->connect_error . '</p>');
}

echo "<p style='color:green'>✅ DB Connected OK</p>";

// Show ALL admins with full details
$result = $conn->query("SELECT id, name, email, password, status FROM admins");
echo "<h3>All Admins:</h3><table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password (first 20 chars)</th><th>Pass Length</th><th>Status</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . substr($row['password'], 0, 20) . "...</td>";
    echo "<td>" . strlen($row['password']) . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Reset password for ALL admins
if (isset($_POST['reset'])) {
    $new_pass = trim($_POST['new_pass']);
    $email    = trim($_POST['email']);
    $hash     = password_hash($new_pass, PASSWORD_DEFAULT);

    if ($email) {
        $stmt = $conn->prepare("UPDATE admins SET password = ?, status = 'active' WHERE email = ?");
        $stmt->bind_param('ss', $hash, $email);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
    } else {
        $conn->query("UPDATE admins SET password = '" . $conn->real_escape_string($hash) . "', status = 'active'");
        $affected = $conn->affected_rows;
    }

    if ($affected > 0) {
        echo "<p style='color:green;font-size:18px'>✅ Password updated! Email: <b>" . ($email ?: 'ALL admins') . "</b> | Password: <b>$new_pass</b></p>";
        echo "<p><a href='admin/login.php' style='font-size:16px'>→ Go to Admin Login</a></p>";
    } else {
        echo "<p style='color:orange'>⚠️ No rows updated. Check the email matches exactly.</p>";
    }

    // Verify the hash works
    $verify = $conn->query("SELECT password FROM admins WHERE email = '" . $conn->real_escape_string($email) . "' LIMIT 1");
    if ($verify && $row = $verify->fetch_assoc()) {
        $ok = password_verify($new_pass, $row['password']);
        echo "<p>Hash verification test: " . ($ok ? "<span style='color:green'>✅ PASS</span>" : "<span style='color:red'>❌ FAIL</span>") . "</p>";
    }
}
?>

<hr>
<h3>Reset Admin Password:</h3>
<form method="POST">
    <label>Admin Email (copy exactly from table above):</label><br>
    <input type="text" name="email" style="width:300px;padding:8px;font-size:15px" placeholder="admin@zubamarket.com"><br><br>
    <label>New Password:</label><br>
    <input type="text" name="new_pass" value="admin123" style="width:300px;padding:8px;font-size:15px"><br><br>
    <button type="submit" name="reset" style="padding:10px 24px;background:#f97316;color:white;border:none;border-radius:6px;font-size:15px;cursor:pointer">
        Reset Password
    </button>
</form>
