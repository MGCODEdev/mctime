<?php
require_once 'inc/config.php';
require_once 'inc/db.php';

echo "<h2>Updating Database Schema...</h2>";

try {
    $pdo = get_db();

    // Define columns to add
    $columns = [
        'contact_email' => 'VARCHAR(255)',
        'website' => 'VARCHAR(255)',
        'president' => 'VARCHAR(100)',
        'vice_president' => 'VARCHAR(100)',
        'meeting_place' => 'VARCHAR(255)',
        'meeting_time' => 'VARCHAR(100)',
        'founded_date' => 'DATE'
    ];

    foreach ($columns as $col => $type) {
        try {
            // Try to add the column. If it exists, MySQL usually errors or warns.
            // Using logic to check existence first is cleaner but straight alter is okay for manual run typically.
            // Let's use a safe approach by checking information_schema or just catching exception.
            // catching exception 1060 (Duplicate column name) is easiest.
            $pdo->exec("ALTER TABLE clubs ADD COLUMN $col $type");
            echo "<p style='color: green;'>Added column: <strong>$col</strong></p>";
        } catch (PDOException $e) {
            // Check if error is "Duplicate column name"
            if (strpos($e->getMessage(), "Duplicate column") !== false || $e->getCode() == '42S21') {
                echo "<p style='color: gray;'>Column <em>$col</em> already exists.</p>";
            } else {
                echo "<p style='color: red;'>Error adding $col: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }

    echo "<h3>Schema update completed.</h3>";
    echo "<p><a href='admin_clubs.php'>Back to Admin Clubs</a></p>";

} catch (PDOException $e) {
    die("Fatal Error: " . $e->getMessage());
}
