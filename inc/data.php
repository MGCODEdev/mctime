<?php
require_once __DIR__ . '/db.php';

// Helper to get DB connection
function db()
{
    return get_db();
}

// --- Clubs ---

function get_clubs()
{
    $stmt = db()->query("SELECT * FROM clubs WHERE active = 1 ORDER BY name ASC");
    return $stmt->fetchAll();
}

function get_all_clubs_admin()
{
    $stmt = db()->query("SELECT * FROM clubs ORDER BY name ASC");
    return $stmt->fetchAll();
}

function get_club($id)
{
    $stmt = db()->prepare("SELECT * FROM clubs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function get_club_by_login($login_name)
{
    $stmt = db()->prepare("SELECT * FROM clubs WHERE login_name = :login_name");
    $stmt->execute([':login_name' => $login_name]);
    return $stmt->fetch();
}

function save_club($club_data)
{
    $pdo = db();
    if (isset($club_data['id']) && $club_data['id']) {
        // Update
        $sql = "UPDATE clubs SET name=:name, shortname=:shortname, color=:color, active=:active, logo=:logo WHERE id=:id";
        $params = [
            ':id' => $club_data['id'],
            ':name' => $club_data['name'],
            ':shortname' => $club_data['shortname'],
            ':color' => $club_data['color'],
            ':active' => isset($club_data['active']) ? $club_data['active'] : 1,
            ':logo' => $club_data['logo'] ?? null
        ];

        // Only update password if provided
        if (!empty($club_data['password_hash'])) {
            $sql = "UPDATE clubs SET name=:name, shortname=:shortname, login_name=:login_name, password_hash=:password_hash, color=:color, active=:active, logo=:logo WHERE id=:id";
            $params[':login_name'] = $club_data['login_name'];
            $params[':password_hash'] = $club_data['password_hash'];
        } elseif (isset($club_data['login_name'])) {
            $sql = "UPDATE clubs SET name=:name, shortname=:shortname, login_name=:login_name, color=:color, active=:active, logo=:logo WHERE id=:id";
            $params[':login_name'] = $club_data['login_name'];
        }

        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);

    } else {
        // Insert
        $sql = "INSERT INTO clubs (name, shortname, login_name, password_hash, color, active, logo) VALUES (:name, :shortname, :login_name, :password_hash, :color, :active, :logo)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $club_data['name'],
            ':shortname' => $club_data['shortname'],
            ':login_name' => $club_data['login_name'],
            ':password_hash' => $club_data['password_hash'],
            ':color' => $club_data['color'],
            ':active' => isset($club_data['active']) ? $club_data['active'] : 1,
            ':logo' => $club_data['logo'] ?? null
        ]);
    }
}

function delete_club($id)
{
    $stmt = db()->prepare("DELETE FROM clubs WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// --- Events ---

function get_events($year)
{
    $start = "$year-01-01";
    $end = "$year-12-31";
    return get_all_events_range($start, $end);
}

function get_all_events_range($start_date, $end_date)
{
    $sql = "SELECT * FROM events WHERE date BETWEEN :start AND :end ORDER BY date ASC, time_from ASC";
    $stmt = db()->prepare($sql);
    $stmt->execute([':start' => $start_date, ':end' => $end_date]);
    return $stmt->fetchAll();
}

function get_events_by_club($club_id, $year = null)
{
    $sql = "SELECT * FROM events WHERE club_id = :club_id";
    $params = [':club_id' => $club_id];

    if ($year) {
        $sql .= " AND date LIKE :year";
        $params[':year'] = "$year%";
    }

    $sql .= " ORDER BY date DESC";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_event($id)
{
    $stmt = db()->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}


function save_event($event_data)
{
    $pdo = db();

    // Check if ID exists (Update vs Insert)
    // For new events, we might generate ID or let DB handle it?
    // Current setup uses VARCHAR ID. Let's stick to generating UUIDs or using existing IDs.

    if (empty($event_data['id'])) {
        $event_data['id'] = uniqid(); // Generate ID if missing
    }

    // Check if exists
    $existing = get_event($event_data['id']);

    if ($existing) {
        $sql = "UPDATE events SET club_id=:club_id, title=:title, date=:date, time_from=:time_from, time_to=:time_to, location=:location, description=:description WHERE id=:id";
    } else {
        $sql = "INSERT INTO events (id, club_id, title, date, time_from, time_to, location, description) VALUES (:id, :club_id, :title, :date, :time_from, :time_to, :location, :description)";
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id' => $event_data['id'],
        ':club_id' => $event_data['club_id'],
        ':title' => $event_data['title'],
        ':date' => $event_data['date'],
        ':time_from' => $event_data['time_from'],
        ':time_to' => $event_data['time_to'],
        ':location' => $event_data['location'],
        ':description' => $event_data['description']
    ]);
}

function delete_event($id, $year = null)
{
    $stmt = db()->prepare("DELETE FROM events WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// --- Settings ---

function get_settings()
{
    $stmt = db()->query("SELECT * FROM settings");
    $rows = $stmt->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

function save_settings($settings)
{
    $pdo = db();
    $sql = "INSERT INTO settings (`key`, value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE value = :value";
    $stmt = $pdo->prepare($sql);

    foreach ($settings as $key => $value) {
        $stmt->execute([':key' => $key, ':value' => $value]);
    }
    return true;
}
