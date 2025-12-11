<?php
// fix_final.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Final Login Fix</h1>";

$password = 'admin';
echo "Target Password: <strong>$password</strong><br>";

// 1. Generate Hash
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated Hash: <code>$hash</code><br>";

// 2. Verify in Memory
if (password_verify($password, $hash)) {
    echo "<span style='color:green'>In-memory verification SUCCESS.</span><br>";
} else {
    die("<span style='color:red'>In-memory verification FAILED. PHP password functions are broken on this server.</span>");
}

// 3. Construct Config Content safely
// We use single quotes for the string to prevent any variable interpolation
$config_content = "<?php
// Global Configuration

// Paths
define('BASE_PATH', __DIR__ . '/../');
define('DATA_PATH', BASE_PATH . 'data/');

// Default Settings
define('DEFAULT_CALENDAR_VIEW', 'month');

// Super Admin Credentials
// User: admin
// Pass: admin
define('SUPER_ADMIN_USER', 'admin');
define('SUPER_ADMIN_PASS_HASH', '" . $hash . "');

// Timezone
date_default_timezone_set('Europe/Berlin');
";

// 4. Write File
$file = __DIR__ . '/inc/config.php';
if (file_put_contents($file, $config_content)) {
    echo "Wrote config file to: <code>$file</code><br>";
} else {
    die("<span style='color:red'>Failed to write config file. Check permissions.</span>");
}

// 5. Verify File Content
// We reload the file by reading it (cannot include again easily if constants are defined)
$file_content = file_get_contents($file);
if (strpos($file_content, $hash) !== false) {
    echo "<span style='color:green'>File content verification SUCCESS. Hash found in file.</span><br>";
} else {
    echo "<span style='color:red'>File content verification FAILED. Hash not found in file.</span><br>";
    echo "<pre>" . htmlspecialchars($file_content) . "</pre>";
}

echo "<h2>Next Steps</h2>";
echo "<p>1. Delete this file (fix_final.php).</p>";
echo "<p>2. <a href='login.php' target='_blank'>Go to Login</a> and use User: <strong>admin</strong>, Pass: <strong>admin</strong></p>";
?>