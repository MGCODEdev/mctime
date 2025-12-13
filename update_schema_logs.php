<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

try {
    $pdo = get_db();

    echo "Creating system_logs table... ";
    $sql = "CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50),
        username VARCHAR(100),
        user_role VARCHAR(50),
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "OK.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>