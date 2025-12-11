<?php
require_once 'inc/config.php';
// Define DB access locally to avoid dependency loops if inc/db.php assumes config is already perfect
// But we updated inc/db.php to use config constants, so it should be fine.

// IMPORTANT: We need the OLD JSON reading functions.
// COPYING them here temporarily since we haven't updated inc/data.php yet, 
// OR inc/data.php is about to be overwritten.
// Let's assume inc/data.php currently HAS the JSON functions.
// We will require it to read data, but we need to be careful if we already updated it.
// Strategy: I haven't updated inc/data.php yet. So I can require it.

require_once 'inc/data.php';
require_once 'inc/db.php';

echo "Starting migration to MariaDB...\n";
echo "--------------------------------\n";

try {
    $pdo = get_db();

    // Ensure tables exist
    init_db($pdo);

    // 1. Migrate Settings
    echo "Migrating Settings...\n";
    if (function_exists('get_settings')) {
        $settings = get_settings();
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE value = :value");
        foreach ($settings as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value]);
            echo "  - Saved setting: $key\n";
        }
    } else {
        echo "  Skipping settings (function not found).\n";
    }

    // 2. Migrate Clubs
    echo "\nMigrating Clubs...\n";
    if (function_exists('get_clubs')) {
        $clubs = get_clubs();
        $stmt = $pdo->prepare("INSERT INTO clubs (id, name, shortname, login_name, password_hash, color, active, logo) 
                               VALUES (:id, :name, :shortname, :login_name, :password_hash, :color, :active, :logo)
                               ON DUPLICATE KEY UPDATE name=:name, shortname=:shortname, color=:color");

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
    }

    // 3. Migrate Events
    echo "\nMigrating Events...\n";
    // Find all event files manually since get_events might rely on arguments
    $files = glob(DATA_PATH . 'events_*.json');
    $stmt = $pdo->prepare("INSERT INTO events (id, club_id, title, date, time_from, time_to, location, description) 
                           VALUES (:id, :club_id, :title, :date, :time_from, :time_to, :location, :description)
                           ON DUPLICATE KEY UPDATE title=:title, date=:date");

    $count = 0;
    foreach ($files as $file) {
        $year_events = json_decode(file_get_contents($file), true);
        if (!is_array($year_events))
            continue;

        foreach ($year_events as $event) {
            // Check if club exists to satisfy foreign key
            // Ideally we migrate clubs first. If club_id is missing, we might have an issue.
            // For now, assuming data is consistent.

            try {
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
            } catch (PDOException $e) {
                echo "  ! Error migrating event {$event['id']}: " . $e->getMessage() . "\n";
            }
        }
    }
    echo "  - Migrated $count events.\n";

    echo "\n--------------------------------\n";
    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
