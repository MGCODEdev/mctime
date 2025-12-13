<?php
// fix_access.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Auto-Fix Login</h1>";

$config_file = __DIR__ . '/inc/config.php';
$config_content = file_get_contents($config_file);

// 1. Fix Super Admin Password in Config
echo "<h2>1. Super Admin Password</h2>";
$admin_pass = 'admin';
$admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);

// Regex to find the define line
$pattern = "/define\('SUPER_ADMIN_PASS_HASH',\s*'[^']+'\);/";
$replacement = "define('SUPER_ADMIN_PASS_HASH', '$admin_hash');";

if (preg_match($pattern, $config_content)) {
    $new_content = preg_replace($pattern, $replacement, $config_content);
    if (file_put_contents($config_file, $new_content)) {
        echo "<p style='color:green'>[SUCCESS] Updated inc/config.php with new hash for password: <strong>'$admin_pass'</strong></p>";
    } else {
        echo "<p style='color:red'>[ERROR] Could not write to inc/config.php. Check permissions.</p>";
    }
} else {
    echo "<p style='color:orange'>[WARN] Could not find SUPER_ADMIN_PASS_HASH definition in config.php. Please update manually.</p>";
    echo "Hash for 'admin': <br><code>$admin_hash</code>";
}

// 2. Fix Public Access in Database
echo "<h2>2. Settings (Public Access)</h2>";

require_once 'inc/config.php'; // Load constants (might be old hash if cached, but we need DB consts)
require_once 'inc/db.php';

try {
    $pdo = get_db();

    $public_pass = 'moto';
    $public_hash = password_hash($public_pass, PASSWORD_DEFAULT);

    // Use REPLACE INTO to avoid parameter count confusion with ON DUPLICATE KEY UPDATE
    $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES ('public_pass_hash', ?)");
    $stmt->execute([$public_hash]);

    echo "<p style='color:green'>[SUCCESS] Set public access password to: <strong>'$public_pass'</strong></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>[ERROR] Database error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p>Try to login now:</p>";
echo "<ul>";
echo "<li><a href='login.php'>Login Page</a></li>";
echo "<li>Admin User: <strong>admin</strong> / Password: <strong>admin</strong></li>";
echo "<li>Public Password: <strong>moto</strong></li>";
echo "</ul>";
