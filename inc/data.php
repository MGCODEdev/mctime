<?php
require_once __DIR__ . '/db.php';

// --- Clubs ---

function get_clubs()
{
    $pdo = get_db();
    $stmt = $pdo->query("SELECT * FROM clubs ORDER BY name ASC");
    return $stmt->fetchAll();
}

function get_club($id)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM clubs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function save_club($club_data)
{
    $pdo = get_db();

    // Check if ID is present and numeric (Update)
    // If ID is empty/null, it's an Insert logic for Auto-Increment
    if (!empty($club_data['id']) && is_numeric($club_data['id'])) {
        $sql = "UPDATE clubs SET 
            name = :name, shortname = :shortname, login_name = :login_name, 
            color = :color, color2 = :color2, active = :active, logo = :logo,
            contact_email = :contact_email, website = :website, 
            president = :president, vice_president = :vice_president, 
            meeting_place = :meeting_place, meeting_time = :meeting_time, 
            founded_date = :founded_date
            WHERE id = :id";

        // Add password update only if hash is provided
        if (!empty($club_data['password_hash'])) {
            $sql = "UPDATE clubs SET 
                name = :name, shortname = :shortname, login_name = :login_name, 
                password_hash = :password_hash,
                color = :color, color2 = :color2, active = :active, logo = :logo,
                contact_email = :contact_email, website = :website, 
                president = :president, vice_president = :vice_president, 
                meeting_place = :meeting_place, meeting_time = :meeting_time, 
                founded_date = :founded_date
                WHERE id = :id";
        }
    } else {
        // Insert
        // If ID column is auto_increment, we do not specify it.
        $sql = "INSERT INTO clubs (
            name, shortname, login_name, password_hash, color, color2, active, logo,
            contact_email, website, president, vice_president, meeting_place, meeting_time, founded_date
        ) VALUES (
            :name, :shortname, :login_name, :password_hash, :color, :color2, :active, :logo,
            :contact_email, :website, :president, :vice_president, :meeting_place, :meeting_time, :founded_date
        )";
    }

    $stmt = $pdo->prepare($sql);

    $params = [
        ':name' => $club_data['name'],
        ':shortname' => $club_data['shortname'],
        ':login_name' => $club_data['login_name'],
        ':password_hash' => $club_data['password_hash'] ?? '', // Required for Insert
        ':color' => $club_data['color'] ?? '#000000',
        ':color2' => $club_data['color2'] ?? null,
        ':active' => isset($club_data['active']) && $club_data['active'] ? 1 : 0,
        ':logo' => $club_data['logo'] ?? null,
        ':contact_email' => $club_data['contact_email'] ?? null,
        ':website' => $club_data['website'] ?? null,
        ':president' => $club_data['president'] ?? null,
        ':vice_president' => $club_data['vice_president'] ?? null,
        ':meeting_place' => $club_data['meeting_place'] ?? null,
        ':meeting_time' => $club_data['meeting_time'] ?? null,
        ':founded_date' => $club_data['founded_date'] ?? null
    ];

    if (!empty($club_data['id']) && is_numeric($club_data['id'])) {
        $params[':id'] = $club_data['id'];

        // If updating but no password provided, remove it from params if query doesn't use it
        // The logic above switches query based on password_hash. 
        // If password_hash is empty in update, do not bind it?
        // Wait, the UPDATE query logic is split.
        // Let's use strict logic:

        if (empty($club_data['password_hash'])) {
            unset($params[':password_hash']);
        }
    }

    if ($stmt->execute($params)) {
        if (empty($club_data['id']) || !is_numeric($club_data['id'])) {
            return $pdo->lastInsertId();
        }
        return true;
    }
    return false;
}

function delete_club($id)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// --- Events ---

function get_events($year)
{
    // Return events for a specific year
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE YEAR(date) = :year ORDER BY date ASC, time_from ASC");
    $stmt->execute([':year' => $year]);
    return $stmt->fetchAll();
}

function get_all_events_range($start_date, $end_date)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM events WHERE date BETWEEN :start AND :end ORDER BY date ASC, time_from ASC");
    $stmt->execute([':start' => $start_date, ':end' => $end_date]);
    return $stmt->fetchAll();
}

function save_event($event_data)
{
    $pdo = get_db();
    $is_update = false;

    if (isset($event_data['id']) && !empty($event_data['id'])) {
        // Check existence
        $stmt = $pdo->prepare("SELECT id FROM events WHERE id = :id");
        $stmt->execute([':id' => $event_data['id']]);
        if ($stmt->fetch()) {
            $is_update = true;
        }
    } else {
        // Generate a new ID if not provided (keeping string IDs for now as per schema)
        $event_data['id'] = uniqid();
    }

    if ($is_update) {
        $sql = "UPDATE events SET 
                club_id = :club_id, 
                title = :title, 
                date = :date, 
                time_from = :time_from, 
                time_to = :time_to, 
                location = :location, 
                description = :description,
                visibility = :visibility
                WHERE id = :id";
    } else {
        $sql = "INSERT INTO events (id, club_id, title, date, time_from, time_to, location, description, visibility) 
                VALUES (:id, :club_id, :title, :date, :time_from, :time_to, :location, :description, :visibility)";
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':id' => $event_data['id'],
        ':club_id' => $event_data['club_id'],
        ':title' => $event_data['title'],
        ':date' => $event_data['date'],
        ':time_from' => $event_data['time_from'] ?? '',
        ':time_to' => $event_data['time_to'] ?? '',
        ':location' => $event_data['location'] ?? '',
        ':description' => $event_data['description'] ?? '',
        ':visibility' => $event_data['visibility'] ?? 'public'
    ]);
}

function delete_event($id, $year = null)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

// --- Settings ---

function get_settings()
{
    $pdo = get_db();
    $stmt = $pdo->query("SELECT `key`, value FROM settings");
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $results ?: [];
}

function save_settings($settings)
{
    $pdo = get_db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE value = :value");
        foreach ($settings as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value]);
        }
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// --- Super Admins ---

function get_super_admins()
{
    $pdo = get_db();
    $stmt = $pdo->query("SELECT * FROM super_admins ORDER BY username ASC");
    return $stmt->fetchAll();
}

function get_super_admin($id)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

function save_super_admin($data)
{
    $pdo = get_db();

    if (isset($data['password']) && !empty($data['password'])) {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
        $hash = null;
    }

    if (isset($data['id']) && !empty($data['id'])) {
        // Update
        if ($hash) {
            $stmt = $pdo->prepare("UPDATE super_admins SET username = :u, password_hash = :p WHERE id = :id");
            return $stmt->execute([':u' => $data['username'], ':p' => $hash, ':id' => $data['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE super_admins SET username = :u WHERE id = :id");
            return $stmt->execute([':u' => $data['username'], ':id' => $data['id']]);
        }
    } else {
        // Insert
        if (!$hash)
            return false; // Password required for new
        $stmt = $pdo->prepare("INSERT INTO super_admins (username, password_hash) VALUES (:u, :p)");
        return $stmt->execute([':u' => $data['username'], ':p' => $hash]);
    }
}

function delete_super_admin($id)
{
    // Prevent deleting the last admin? Not strictly required but good practice.
    // For now, simple delete.
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM super_admins WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
