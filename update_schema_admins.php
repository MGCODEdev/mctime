<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

try {
    $pdo = get_db();

    // 1. Create super_admins table
    echo "Creating super_admins table... ";
    $sql = "CREATE TABLE IF NOT EXISTS super_admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "OK.\n";

    // 2. Check if admin exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM super_admins");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        echo "Seeding default admin user... ";

        // Retrieve existing settings or defaults
        $stmt_settings = $pdo->query("SELECT * FROM settings WHERE `key` IN ('admin_user', 'admin_pass_hash')");
        $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);

        $user = $settings['admin_user'] ?? (defined('SUPER_ADMIN_USER') ? SUPER_ADMIN_USER : 'admin');

        // If hash exists in DB use it, otherwise use default config hash, otherwise fallback to 'default' (should verify what valid default hash is)
        // Ideally we assume valid hash is available. If not, we set a known default "admin"
        $hash = $settings['admin_pass_hash'] ?? (defined('SUPER_ADMIN_PASS_HASH') ? SUPER_ADMIN_PASS_HASH : password_hash('admin', PASSWORD_DEFAULT));

        $stmt_insert = $pdo->prepare("INSERT INTO super_admins (username, password_hash) VALUES (:u, :p)");
        $stmt_insert->execute([':u' => $user, ':p' => $hash]);

        echo "OK (User: $user).\n";
    } else {
        echo "Admin users already exist. Skipping seed.\n";
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>