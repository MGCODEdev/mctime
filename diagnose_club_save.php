<?php
// diagnose_club_save.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'inc/config.php';
require_once 'inc/data.php';

echo "<h1>Debug Club Creation</h1>";

// Test Data
$test_club = [
    'name' => 'Test Club ' . time(),
    'shortname' => 'TEST',
    'login_name' => 'test_login_' . time(),
    'password_hash' => password_hash('test', PASSWORD_DEFAULT),
    'color' => '#000000',
    'active' => 1,
    'email' => 'test@example.com',
    'admin_name' => 'Test Admin',
    'phone' => '123456'
];

try {
    echo "<p>Attempting to save test club...</p>";
    if (save_club($test_club)) {
        echo "<p style='color:green'>[SUCCESS] Test club created successfully.</p>";
    } else {
        echo "<p style='color:red'>[FAIL] save_club returned false.</p>";
    }
} catch (PDOException $e) {
    echo "<h2 style='color:red'>SQL Error</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "<p style='font-weight:bold'>Cause: Database schema is missing columns.</p>";
        echo "<p>Please run <a href='update_schema.php'>update_schema.php</a> to fix this.</p>";
    }
} catch (Throwable $t) {
    echo "<h2 style='color:red'>Fatal Error</h2>";
    echo "<pre>" . htmlspecialchars($t->getMessage()) . "</pre>";
}
?>