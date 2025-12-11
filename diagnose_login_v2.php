<?php
require_once 'inc/config.php';
require_once 'inc/data.php';

echo "<h1>Login Diagnosis</h1>";

// 1. Check DB Connection
try {
    $pdo = get_db();
    echo "<p style='color:green'>[OK] Database connected.</p>";
} catch (Exception $e) {
    die("<p style='color:red'>[FAIL] Database connection error: " . $e->getMessage() . "</p>");
}

// 2. Check Settings
$settings = get_settings();
echo "<h2>Settings Check</h2>";
if (isset($settings['public_pass_hash'])) {
    $hash = $settings['public_pass_hash'];
    echo "<p>Hash found in DB: " . htmlspecialchars(substr($hash, 0, 10)) . "... (Length: " . strlen($hash) . ")</p>";

    // 3. Verify 'moto2025'
    $password = 'moto2025';
    if (password_verify($password, $hash)) {
        echo "<p style='color:green'>[PASS] Password 'moto2025' matches the hash in DB.</p>";
    } else {
        echo "<p style='color:red'>[FAIL] Password 'moto2025' does NOT match the hash in DB.</p>";
        echo "<p>Trying to generate new hash and compare...</p>";
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<p>New Hash would be: $new_hash</p>";
    }
} else {
    echo "<p style='color:red'>[FAIL] 'public_pass_hash' NOT found in settings table.</p>";
}

// 4. Check Config (Super Admin)
echo "<h2>Config Check (Super Admin)</h2>";
if (defined('SUPER_ADMIN_PASS_HASH')) {
    $admin_hash = SUPER_ADMIN_PASS_HASH;
    echo "<p>Admin Hash in Config: " . htmlspecialchars(substr($admin_hash, 0, 10)) . "...</p>";

    if (password_verify('admin', $admin_hash)) {
        echo "<p style='color:green'>[PASS] Password 'admin' matches Config hash.</p>";
    } else {
        echo "<p style='color:red'>[FAIL] Password 'admin' does NOT match Config hash.</p>";
    }
} else {
    echo "<p style='color:red'>[FAIL] SUPER_ADMIN_PASS_HASH constant not defined.</p>";
}

echo "<h2>Environment</h2>";
echo "PHP Version: " . phpversion() . "<br>";
?>