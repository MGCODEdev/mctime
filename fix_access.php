<?php
// fix_access.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Auto-Fix Login (Database Mode)</h1>";

require_once 'inc/config.php';
require_once 'inc/db.php';

// 1. Fix Super Admin Password in Database (Settings)
echo "<h2>1. Super Admin Password</h2>";

$admin_user = 'admin';
$admin_pass = 'admin';
$admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);

try {
    $pdo = get_db();

    // Admin User
    $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES ('admin_user', ?)");
    $stmt->execute([$admin_user]);

    // Admin Hash
    $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES ('admin_pass_hash', ?)");
    $stmt->execute([$admin_hash]);

    echo "<p style='color:green'>[SUCCESS] Saved admin credentials to Database.</p>";
    echo "User: <strong>$admin_user</strong><br>";
    echo "Pass: <strong>$admin_pass</strong>";

} catch (Exception $e) {
    echo "<p style='color:red'>[ERROR] Database error (Admin): " . $e->getMessage() . "</p>";
}

// 2. Fix Public Access in Database
echo "<h2>2. Settings (Public Access)</h2>";

try {
    // Public Hash
    $public_pass = 'moto';
    $public_hash = password_hash($public_pass, PASSWORD_DEFAULT);

    // Use REPLACE INTO to avoid parameter count confusion with ON DUPLICATE KEY UPDATE
    $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES ('public_pass_hash', ?)");
    $stmt->execute([$public_hash]);

    echo "<p style='color:green'>[SUCCESS] Set public access password to: <strong>'$public_pass'</strong></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>[ERROR] Database error (Public): " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>Next Step: <strong>Login</strong> will now check these values.</p>";
echo "<ul>";
echo "<li><a href='login.php'>Login Page</a></li>";
echo "<li>Admin User: <strong>admin</strong> / Password: <strong>admin</strong></li>";
echo "<li>Public Password: <strong>moto</strong></li>";
echo "</ul>";
