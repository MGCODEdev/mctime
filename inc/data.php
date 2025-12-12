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

    // Check if update or insert
    $is_update = false;
    if (isset($club_data['id']) && $club_data['id']) {
        // Check if exists
        if (get_club($club_data['id'])) {
            $is_update = true;
        }
    }

    if ($is_update) {
        $sql = "UPDATE clubs SET 
                name = :name, 
                shortname = :shortname, 
                login_name = :login_name, 
                password_hash = :password_hash, 
                color = :color, 
                active = :active, 
                logo = :logo,
                contact_email = :contact_email,
                website = :website,
                president = :president,
                vice_president = :vice_president,
                meeting_place = :meeting_place,
                meeting_time = :meeting_time,
                founded_date = :founded_date
                WHERE id = :id";
    } else {
        $sql = "INSERT INTO clubs (name, shortname, login_name, password_hash, color, active, logo, 
                contact_email, website, president, vice_president, meeting_place, meeting_time, founded_date) 
                VALUES (:name, :shortname, :login_name, :password_hash, :color, :active, :logo, 
                :contact_email, :website, :president, :vice_president, :meeting_place, :meeting_time, :founded_date)";

        if (isset($club_data['id']) && !empty($club_data['id'])) {
            $sql = "INSERT INTO clubs (id, name, shortname, login_name, password_hash, color, active, logo, 
                contact_email, website, president, vice_president, meeting_place, meeting_time, founded_date) 
                VALUES (:id, :name, :shortname, :login_name, :password_hash, :color, :active, :logo, 
                :contact_email, :website, :president, :vice_president, :meeting_place, :meeting_time, :founded_date)";
        }
    }

    $stmt = $pdo->prepare($sql);

    $params = [
        ':name' => $club_data['name'],
        ':shortname' => $club_data['shortname'] ?? '',
        ':login_name' => $club_data['login_name'] ?? null,
        ':password_hash' => $club_data['password_hash'] ?? null,
        ':color' => $club_data['color'] ?? '#000000',
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

    if ($is_update || (isset($club_data['id']) && !empty($club_data['id']))) {
        $params[':id'] = $club_data['id'];
    }

    return $stmt->execute($params);
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
                description = :description 
                WHERE id = :id";
    } else {
        $sql = "INSERT INTO events (id, club_id, title, date, time_from, time_to, location, description) 
                VALUES (:id, :club_id, :title, :date, :time_from, :time_to, :location, :description)";
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
        ':description' => $event_data['description'] ?? ''
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
