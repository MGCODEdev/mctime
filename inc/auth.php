<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function debug_log($message)
{
    $log_file = DATA_PATH . 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function login($username, $password)
{
    $log = __DIR__ . '/../debug_login.txt';
    $time = date('H:i:s');
    file_put_contents($log, "[$time] Login attempt (DB) for: '$username'\n", FILE_APPEND);

    $pdo = get_db();

    // 1. Check Users Table (Super Admin & others)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        file_put_contents($log, "[$time] User found in 'users' table. Role: {$user['role']}\n", FILE_APPEND);
        if (password_verify($password, $user['password_hash'])) {
            file_put_contents($log, "[$time] Password verified.\n", FILE_APPEND);
            $_SESSION['user_role'] = $user['role']; // e.g., 'super_admin'
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            session_regenerate_id(true);
            return true;
        } else {
            file_put_contents($log, "[$time] Password mismatch for DB user.\n", FILE_APPEND);
        }
    } else {
        file_put_contents($log, "[$time] User not found in 'users' table. Checking Clubs...\n", FILE_APPEND);
    }

    // 2. Check Club Admin (Legacy/Secondary table)
    // Ideally we would migrate clubs to users table too, but for now keep separate as per plan.
    $clubs = get_clubs();

    foreach ($clubs as $club) {
        if ($club['login_name'] === $username) {
            if (!$club['active']) {
                file_put_contents($log, "[$time] Club found but inactive: {$club['name']}\n", FILE_APPEND);
                continue;
            }

            file_put_contents($log, "[$time] Found club user: '{$club['name']}'. Verifying hash...\n", FILE_APPEND);
            if (password_verify($password, $club['password_hash'])) {
                file_put_contents($log, "[$time] Club login successful.\n", FILE_APPEND);
                $_SESSION['user_role'] = 'club_admin';
                $_SESSION['user_id'] = $club['id'];
                $_SESSION['user_name'] = $club['name'];
                $_SESSION['club_color'] = $club['color'];
                session_regenerate_id(true);
                return true;
            } else {
                file_put_contents($log, "[$time] Club password mismatch.\n", FILE_APPEND);
            }
        }
    }

    file_put_contents($log, "[$time] Login failed for user: '$username'\n", FILE_APPEND);
    return false;
}

function login_public($password)
{
    // Log to file for visibility
    $log = __DIR__ . '/../debug_login.txt';
    $time = date('H:i:s');
    file_put_contents($log, "[$time] Public login attempt...\n", FILE_APPEND);

    $settings = get_settings();

    if (!isset($settings['public_pass_hash'])) {
        file_put_contents($log, "[$time] ERROR: public_pass_hash missing from settings.\n", FILE_APPEND);

        // Fallback
        $fallback_pass = 'moto2025';
        if ($password === $fallback_pass) {
            file_put_contents($log, "[$time] Fallback allowed.\n", FILE_APPEND);
            $_SESSION['public_access'] = true;
            return true;
        }
        return false;
    }

    $hash = $settings['public_pass_hash'];
    file_put_contents($log, "[$time] Hash found: " . substr($hash, 0, 10) . "...\n", FILE_APPEND);

    if (password_verify($password, $hash)) {
        file_put_contents($log, "[$time] SUCCESS: Password verified.\n", FILE_APPEND);
        $_SESSION['public_access'] = true;
        return true;
    } else {
        file_put_contents($log, "[$time] FAIL: Hash verify failed.\n", FILE_APPEND);
        file_put_contents($log, "[$time] Provided Pass Length: " . strlen($password) . "\n", FILE_APPEND);
        return false;
    }
}

function logout()
{
    session_destroy();
    session_start();
}

function is_logged_in()
{
    return isset($_SESSION['user_role']);
}

function is_super_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'super_admin';
}

function is_club_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'club_admin';
}

function has_public_access()
{
    return isset($_SESSION['public_access']) && $_SESSION['public_access'] === true;
}

function get_current_club_id()
{
    return $_SESSION['user_id'] ?? null;
}

function require_public_auth()
{
    if (!has_public_access() && !is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_super_admin()
{
    if (!is_super_admin()) {
        die("Access Denied: Super Admin only.");
    }
}
