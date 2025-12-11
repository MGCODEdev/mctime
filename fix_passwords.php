<?php
require_once 'inc/config.php';
// We use the new data layer which uses DB
require_once 'inc/data.php';

$admin_pass = 'admin';
$public_pass = 'moto2025';

echo "Generating hashes...\n<br>";
$admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
$public_hash = password_hash($public_pass, PASSWORD_DEFAULT);

echo "Admin Hash: " . $admin_hash . "<br>\n";
echo "Public Hash: " . $public_hash . "<br>\n";

// 1. Update Public Password in DB (Settings)
// Use save_settings from inc/data.php which now writes to DB
$settings = get_settings(); // Fetch current
$settings['public_pass_hash'] = $public_hash;
if (save_settings($settings)) {
    echo "Updated public password in database.<br>\n";
} else {
    echo "Error updating database settings.<br>\n";
}

// 2. Update Super Admin in inc/config.php
// We must be careful NOT to destroy DB constants added recently.
$config_file = __DIR__ . '/inc/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);

    // Regex to replace the hash
    // Look for define('SUPER_ADMIN_PASS_HASH', '...');
    $pattern = "/define\('SUPER_ADMIN_PASS_HASH', '.*'\);/";
    $replacement = "define('SUPER_ADMIN_PASS_HASH', '$admin_hash');";

    // Check if constant exists, if not appened it (though it should exist if valid config)
    if (preg_match($pattern, $config_content)) {
        $new_config = preg_replace($pattern, $replacement, $config_content);
    } else {
        // Append if missing (or we could just fail)
        // Let's retry with a broader search or just append if completely missing, 
        // but user might have a messy config.
        echo "SUPER_ADMIN_PASS_HASH constant not found in config.php. Please add it manually.<br>\n";
        $new_config = $config_content;
    }

    if ($new_config !== $config_content) {
        if (file_put_contents($config_file, $new_config)) {
            echo "Updated inc/config.php with new Admin hash.<br>\n";
        } else {
            echo "Error writing to inc/config.php<br>\n";
        }
    }
} else {
    echo "inc/config.php not found.<br>\n";
}

echo "Done.<br>\n";
echo "Super Admin: admin / $admin_pass<br>\n";
echo "Public Pass: $public_pass<br>\n";
?>