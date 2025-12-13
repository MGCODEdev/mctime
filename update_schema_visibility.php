<?php
// update_schema_visibility.php
require_once 'inc/config.php';
require_once 'inc/db.php';

echo "<h2>Updating Database Schema (Visibility)</h2>";

try {
    $pdo = get_db();

    // Add visibility column
    try {
        $pdo->exec("ALTER TABLE events ADD COLUMN visibility ENUM('public', 'internal') DEFAULT 'public'");
        echo "<p style='color: green;'>Added column: <strong>visibility</strong></p>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column") !== false || $e->getCode() == '42S21') {
            echo "<p style='color: gray;'>Column <em>visibility</em> already exists.</p>";
        } else {
            echo "<p style='color: red;'>Error adding visibility: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    echo "<h3>Schema update completed.</h3>";
    echo "<p><a href='index.php'>Go to Calendar</a> | <a href='admin_events.php'>Go to Admin</a></p>";

} catch (PDOException $e) {
    die("Fatal Error: " . $e->getMessage());
}
