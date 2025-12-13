<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Debug Migration</h1>";

require_once 'inc/config.php';
echo "<p>Config loaded.</p>";

// define constants usually in config if missing
if (!defined('DATA_PATH'))
    define('DATA_PATH', __DIR__ . '/data/');

require_once 'inc/db.php';
echo "<p>DB File loaded.</p>";

try {
    $pdo = get_db();
    echo "<p style='color:green'>DB Connection successful.</p>";
} catch (Exception $e) {
    die("<p style='color:red'>DB Connection failed: " . $e->getMessage() . "</p>");
}

require_once 'inc/data.php';
echo "<p>Data File loaded.</p>";

echo "<h2>Settings</h2>";
try {
    $settings = get_settings();
    echo "<pre>" . print_r($settings, true) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red'>get_settings failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Clubs</h2>";
try {
    $clubs = get_clubs();
    echo "<p>Found " . count($clubs) . " clubs.</p>";
    if (count($clubs) > 0) {
        echo "<pre>" . print_r($clubs[0], true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>get_clubs failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Auth Check</h2>";
if (defined('SUPER_ADMIN_USER')) {
    echo "<p>SUPER_ADMIN_USER is defined: " . SUPER_ADMIN_USER . "</p>";
} else {
    echo "<p style='color:red'>SUPER_ADMIN_USER is NOT defined!</p>";
}
