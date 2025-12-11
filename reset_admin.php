<?php
// reset_admin.php
// Safely resets the Admin password to 'admin' without destroying other settings.

require_once 'inc/config.php';

$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Reset Admin Script</h1>";
echo "<p>Target Password: <strong>$password</strong></p>";
echo "<p>New Hash: <code>$hash</code></p>";

$file = __DIR__ . '/inc/config.php';

if (file_exists($file)) {
    $content = file_get_contents($file);

    // 1. Check if DB config exists (just a safety check to warn user)
    if (strpos($content, 'DB_HOST') === false) {
        echo "<p style='color:orange'>[WARNING] It seems DB_HOST is missing in config.php. You might need to run fix_db_config.php first.</p>";
    }

    // 2. Regex Replace the Hash
    // We look for define('SUPER_ADMIN_PASS_HASH', '...');
    $pattern = "/define\('SUPER_ADMIN_PASS_HASH', '.*'\);/";
    $replacement = "define('SUPER_ADMIN_PASS_HASH', '$hash');";

    if (preg_match($pattern, $content)) {
        $new_content = preg_replace($pattern, $replacement, $content);

        if ($new_content !== $content) {
            if (file_put_contents($file, $new_content)) {
                echo "<p style='color:green'>[SUCCESS] Admin password reset to 'admin'.</p>";
                echo "<p>Configuration preserved.</p>";
                echo "<p><a href='login.php'>Go to Login</a></p>";
            } else {
                echo "<p style='color:red'>[FAIL] Could not write to inc/config.php. Check permissions.</p>";
            }
        } else {
            echo "<p>[INFO] Password hash was already set to this value.</p>";
        }
    } else {
        // Constant not found, append it?
        // Better to warn, as specific placement might be desired.
        echo "<p style='color:red'>[FAIL] Constant SUPER_ADMIN_PASS_HASH not found in config.php.</p>";

        // Append Attempt
        $new_content = $content . "\n\n// Admin Password Reset by Script\ndefine('SUPER_ADMIN_PASS_HASH', '$hash');\n";
        if (file_put_contents($file, $new_content)) {
            echo "<p style='color:green'>[SUCCESS] Appended new hash constant to config.php.</p>";
        }
    }

} else {
    echo "<p style='color:red'>[FAIL] inc/config.php not found!</p>";
}
?>