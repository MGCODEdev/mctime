<?php
// Reset Admin Configuration
// Run this to force-reset the inc/config.php file with a known password hash.

$password = 'admin';
$hash = password_hash($password, PASSWORD_DEFAULT);

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
define('SUPER_ADMIN_PASS_HASH', '$hash');

// Timezone
date_default_timezone_set('Europe/Berlin');
";

$file = __DIR__ . '/inc/config.php';

if (file_put_contents($file, $config_content)) {
    echo "<h1>Erfolg</h1>";
    echo "<p>Die Datei <code>inc/config.php</code> wurde neu erstellt.</p>";
    echo "<p><strong>Benutzer:</strong> admin<br>";
    echo "<strong>Passwort:</strong> admin</p>";
    echo "<p>Generierter Hash: <code>$hash</code></p>";
    echo "<p><a href='login.php'>Zum Login</a></p>";
} else {
    echo "<h1>Fehler</h1>";
    echo "<p>Konnte <code>inc/config.php</code> nicht schreiben.</p>";
    echo "<p>Bitte prüfen Sie die Dateiberechtigungen oder kopieren Sie folgenden Inhalt manuell in die Datei:</p>";
    echo "<textarea rows='20' cols='80'>" . htmlspecialchars($config_content) . "</textarea>";
}
?>