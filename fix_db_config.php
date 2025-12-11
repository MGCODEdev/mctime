<?php
// fix_db_config.php
$file = __DIR__ . '/inc/config.php';

if (!file_exists($file)) {
    die("config.php not found!");
}

$content = file_get_contents($file);

if (strpos($content, 'DB_HOST') === false) {
    // Append DB Config
    $db_config = "
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'motocalendar');
define('DB_USER', 'moto_user');
define('DB_PASS', 'geheimes_passwort'); // Bitte ändern!
";

    // Insert after "DATA_PATH" definition or at end
    if (strpos($content, "define('DATA_PATH'") !== false) {
        $content = preg_replace("/(define\('DATA_PATH'.*;)/", "$1\n$db_config", $content);
    } else {
        $content .= $db_config;
    }

    if (file_put_contents($file, $content)) {
        echo "<h1>Fixed!</h1>Database config added to inc/config.php. <a href='index.php'>Go Home</a>";
    } else {
        echo "Failed to write config.php";
    }
} else {
    echo "DB_HOST already exists in config.php. No changes made.";
}
?>