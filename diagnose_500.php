<?php
// diagnose_500.php
// Run this to see PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug 500 Error</h1>";

try {
    echo "<li>Loading config...</li>";
    require_once 'inc/config.php';
    echo "<li>Loading db...</li>";
    require_once 'inc/db.php';
    echo "<li>Loading data...</li>";
    require_once 'inc/data.php';

    echo "<li>Connecting to DB...</li>";
    $pdo = get_db();

    echo "<li>Fetching Clubs (Test)...</li>";
    $clubs = get_clubs();
    echo "<li>Found " . count($clubs) . " active clubs.</li>";

    echo "<li>Fetching All Clubs (Admin)...</li>";
    $all = get_all_clubs_admin();
    echo "<li>Found " . count($all) . " total clubs.</li>";

    echo "<p style='color:green'>[Success] Database layer works.</p>";

} catch (Throwable $e) {
    echo "<h2 style='color:red'>FATAL ERROR</h2>";
    echo "<pre>" . $e . "</pre>";
}
?>