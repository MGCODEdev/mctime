<?php
// update_schema.php
// Updates the database schema for existing installations.
// Adds: email, admin_name, phone to clubs table.

require_once 'inc/config.php';
require_once 'inc/db.php';

echo "<h1>Update Database Schema</h1>";

try {
    $pdo = get_db();

    // Helper to check if column exists
    function columnExists($pdo, $table, $column)
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column");
        $stmt->execute([':db' => DB_NAME, ':table' => $table, ':column' => $column]);
        return $stmt->fetchColumn() > 0;
    }

    $new_columns = [
        'email' => 'VARCHAR(100)',
        'admin_name' => 'VARCHAR(100)',
        'phone' => 'VARCHAR(50)'
    ];

    foreach ($new_columns as $col => $type) {
        if (!columnExists($pdo, 'clubs', $col)) {
            echo "<p>Adding column <code>$col</code>...</p>";
            $pdo->exec("ALTER TABLE clubs ADD COLUMN $col $type");
            echo "<p style='color:green'>[SUCCESS] Added $col.</p>";
        } else {
            echo "<p style='color:orange'>[INFO] Column <code>$col</code> already exists.</p>";
        }
    }

    echo "<p style='color:green'>[DONE] Schema update complete.</p>";
    echo "<p><a href='admin_clubs.php'>Go to Clubs Admin</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>Error</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>