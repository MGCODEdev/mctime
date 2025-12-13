<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'inc/config.php';

echo "<h1>Debug Login</h1>";

echo "<h2>1. Super Admin Config</h2>";
if (defined('SUPER_ADMIN_USER')) {
    echo "User Constant: <code>" . SUPER_ADMIN_USER . "</code><br>";
} else {
    echo "<strong style='color:red'>SUPER_ADMIN_USER not defined!</strong><br>";
}

if (defined('SUPER_ADMIN_PASS_HASH')) {
    echo "Hash Constant Length: " . strlen(SUPER_ADMIN_PASS_HASH) . "<br>";
    echo "Hash Preview: " . substr(SUPER_ADMIN_PASS_HASH, 0, 10) . "...<br>";
} else {
    echo "<strong style='color:red'>SUPER_ADMIN_PASS_HASH not defined!</strong><br>";
}

echo "<h2>2. Verification Test</h2>";
$test_pass = 'admin';
$hash_to_test = defined('SUPER_ADMIN_PASS_HASH') ? SUPER_ADMIN_PASS_HASH : '';

echo "Testing password: <code>'$test_pass'</code> against Hash...<br>";

if (password_verify($test_pass, $hash_to_test)) {
    echo "<h3 style='color:green'>[PASS] Verification Successful!</h3>";
    echo "The password 'admin' matches the hash in the config.";
} else {
    echo "<h3 style='color:red'>[FAIL] Verification Failed!</h3>";
    echo "The password 'admin' does NOT match the hash in the config.<br>";
    echo "It seems the config was not updated correctly or holds a wrong hash.";
}

echo "<h2>3. Config File Content (First 30 lines)</h2>";
$content = file_get_contents('inc/config.php');
echo "<pre>" . htmlspecialchars(substr($content, 0, 1000)) . "</pre>";
