<?php
require_once 'inc/db.php';
require_once 'inc/data.php'; // To read existing JSONs using old functions (before we overwrite them)

// Note: This script assumes inc/data.php still has the JSON implementation!
// Run this BEFORE overwriting inc/data.php

echo "Starting migration to SQLite...\n";
echo "--------------------------------\n";

$pdo = get_db();

// 1. Migrate Settings
echo "Migrating Settings...\n";
$settings = get_settings();
$stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (:key, :value)");
foreach ($settings as $key => $value) {
    $stmt->execute([':key' => $key, ':value' => $value]);
    echo "  - Saved setting: $key\n";
}

// 2. Migrate Clubs
echo "\nMigrating Clubs...\n";
$clubs = get_clubs();
$stmt = $pdo->prepare("INSERT OR IGNORE INTO clubs (id, name, shortname, login_name, password_hash, color, active, logo) VALUES (:id, :name, :shortname, :login_name, :password_hash, :color, :active, :logo)");

foreach ($clubs as $club) {
    $stmt->execute([
        ':id' => $club['id'],
        ':name' => $club['name'],
        ':shortname' => $club['shortname'] ?? '',
        ':login_name' => $club['login_name'] ?? null,
        ':password_hash' => $club['password_hash'] ?? null,
        ':color' => $club['color'] ?? '#000000',
        ':active' => $club['active'] ? 1 : 0,
        ':logo' => $club['logo'] ?? null
    ]);
    echo "  - Migrated club: {$club['name']} (ID: {$club['id']})\n";
}

// 3. Migrate Events
echo "\nMigrating Events...\n";
// Find all event files
$files = glob(DATA_PATH . 'events_*.json');
$stmt = $pdo->prepare("INSERT OR IGNORE INTO events (id, club_id, title, date, time_from, time_to, location, description) VALUES (:id, :club_id, :title, :date, :time_from, :time_to, :location, :description)");

$count = 0;
foreach ($files as $file) {
    $year_events = json_decode(file_get_contents($file), true);
    if (!is_array($year_events))
        continue;

    foreach ($year_events as $event) {
        $stmt->execute([
            ':id' => $event['id'],
            ':club_id' => $event['club_id'],
            ':title' => $event['title'],
            ':date' => $event['date'],
            ':time_from' => $event['time_from'] ?? '',
            ':time_to' => $event['time_to'] ?? '',
            ':location' => $event['location'] ?? '',
            ':description' => $event['description'] ?? ''
        ]);
        $count++;
    }
}
echo "  - Migrated $count events.\n";

echo "\n--------------------------------\n";
echo "Migration completed successfully!\n";
echo "Database file created at: " . DATA_PATH . "database.sqlite\n";
