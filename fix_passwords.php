<?php
// Fix passwords for MotoCalendar
// Run this script once to set the correct password hashes.

$admin_pass = 'admin';
$public_pass = 'moto2025';

echo "Generating hashes...\n";
$admin_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
$public_hash = password_hash($public_pass, PASSWORD_DEFAULT);

echo "Admin Hash: " . $admin_hash . "<br>\n";
echo "Public Hash: " . $public_hash . "<br>\n";

// Update data/settings.json
$settings_file = __DIR__ . '/data/settings.json';
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true) ?: [];
    $settings['public_pass_hash'] = $public_hash;
    if (file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT))) {
        echo "Updated data/settings.json<br>\n";
    } else {
        echo "Error writing to data/settings.json<br>\n";
    }
} else {
    echo "data/settings.json not found.<br>\n";
}

// Update inc/config.php
$config_file = __DIR__ . '/inc/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    // Regex to replace the hash
    $pattern = "/define\('SUPER_ADMIN_PASS_HASH', '.*'\);/";
    $replacement = "define('SUPER_ADMIN_PASS_HASH', '$admin_hash');";
    $new_config = preg_replace($pattern, $replacement, $config_content);

    if ($new_config && $new_config !== $config_content) {
        if (file_put_contents($config_file, $new_config)) {
            echo "Updated inc/config.php<br>\n";
        } else {
            echo "Error writing to inc/config.php<br>\n";
        }
    } else {
        echo "Could not update inc/config.php automatically. Please replace the line manually.<br>\n";
    }
} else {
    echo "inc/config.php not found.<br>\n";
}

echo "Done. Please delete this file after use.<br>\n";
?>