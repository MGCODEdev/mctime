<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

try {
    $pdo = get_db();

    echo "Creating login_attempts table... ";
    $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        username VARCHAR(100),
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "OK.\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>