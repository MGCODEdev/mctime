<?php
require_once 'inc/db.php';

echo "<h2>Updating Schema: Colors</h2>";

$pdo = get_db();

try {
    // Add color2 column
    $pdo->exec("ALTER TABLE clubs ADD COLUMN color2 VARCHAR(20) DEFAULT NULL AFTER color");
    echo "Added column 'color2' to 'clubs' table.<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Column 'color2' already exists.<br>";
    } else {
        echo "Error adding 'color2': " . $e->getMessage() . "<br>";
    }
}

echo "<br>Done.";
?>