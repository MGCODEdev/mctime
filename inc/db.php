<?php
require_once __DIR__ . '/config.php';

function get_db()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Initialize schema if needed (or assume it's done via migration)
            // Ideally schema creation should be a separate script or check
            // For now, we can keep init_db but adapt it for MySQL
            // init_db($pdo); // Disabled for production stability, run migration once.

        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

function init_db($pdo)
{
    // Clubs Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS clubs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        shortname VARCHAR(50),
        login_name VARCHAR(100) UNIQUE,
        password_hash VARCHAR(255),
        color VARCHAR(20),
        active TINYINT DEFAULT 1,
        logo VARCHAR(255),
        email VARCHAR(100),
        admin_name VARCHAR(100),
        phone VARCHAR(50)
    ) ENGINE=InnoDB");

    // Events Table
    // Using VARCHAR for id to support existing uniqid() strings temporarily if needed,
    // otherwise cleaner to migrate to INT. 
    // Plan: Use VARCHAR(36) for existing IDs match.
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id VARCHAR(50) PRIMARY KEY, 
        club_id INT,
        title VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        time_from VARCHAR(10),
        time_to VARCHAR(10),
        location VARCHAR(255),
        description TEXT,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    // Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        value TEXT
    ) ENGINE=InnoDB");

    // Users Table (New Authentication)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
}
