<?php
require_once __DIR__ . '/db.php';

function system_log($action, $details = '')
{
    try {
        if (session_status() == PHP_SESSION_NONE) {
            // Cannot rely on session if not started, but usually it is.
            // If called from a cli script, session might be missing.
        }

        $pdo = get_db();

        $user_id = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['user_name'] ?? 'System/Guest';
        $user_role = $_SESSION['user_role'] ?? 'public';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        $stmt = $pdo->prepare("INSERT INTO system_logs (user_id, username, user_role, action, details, ip_address) VALUES (:uid, :uname, :role, :action, :details, :ip)");
        $stmt->execute([
            ':uid' => $user_id,
            ':uname' => $username,
            ':role' => $user_role,
            ':action' => $action,
            ':details' => $details,
            ':ip' => $ip_address
        ]);

    } catch (Exception $e) {
        // Logging should not break the app
        error_log("System Log Error: " . $e->getMessage());
    }
}

function get_system_logs($limit = 100)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Deletes logs older than $days.
 */
function cleanup_system_logs($days = 30)
{
    $pdo = get_db();
    $stmt = $pdo->prepare("DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
    $stmt->bindValue(':days', (int) $days, PDO::PARAM_INT);
    $stmt->execute();
}
?>