<?php
// reset_password_web.php
// Open this file in your browser to reset the Admin password.

require_once 'inc/config.php';

$new_password = 'admin';
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

echo "<h1>Admin Password Reset</h1>";
echo "<p>Generating hash for password: <strong>$new_password</strong></p>";
echo "<p>Hash: <code>$new_hash</code></p>";

// Verify immediately
if (password_verify($new_password, $new_hash)) {
    echo "<p style='color:green'>[OK] Generated hash is valid.</p>";
} else {
    die("<p style='color:red'>[FAIL] Generated hash verification failed. PHP issue?</p>");
}

// Update config.php
$config_file = __DIR__ . '/inc/config.php';

if (file_exists($config_file)) {
    $content = file_get_contents($config_file);

    // Regex to find the constant
    $pattern = "/define\('SUPER_ADMIN_PASS_HASH', '.*'\);/";
    $replacement = "define('SUPER_ADMIN_PASS_HASH', '$new_hash');";

    if (preg_match($pattern, $content)) {
        $new_content = preg_replace($pattern, $replacement, $content);
        if (file_put_contents($config_file, $new_content)) {
            echo "<p style='color:green'>[SUCCESS] Updated <code>inc/config.php</code> with new hash.</p>";
            echo "<p><a href='login.php'>Go to Login</a></p>";
        } else {
            echo "<p style='color:red'>[FAIL] Could not write to <code>inc/config.php</code>. Permission denied?</p>";
            echo "<p>Please manually update `inc/config.php` with the hash above.</p>";
        }
    } else {
        echo "<p style='color:red'>[FAIL] Could not find SUPER_ADMIN_PASS_HASH definition in config file.</p>";
    }
} else {
    echo "<p style='color:red'>[FAIL] config.php not found.</p>";
}
?>