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
    debug_log("Login attempt for user: '$username'");

    // 1. Check Super Admin
    if ($username === SUPER_ADMIN_USER) {
        debug_log("User matches Super Admin. Verifying hash...");
        if (password_verify($password, SUPER_ADMIN_PASS_HASH)) {
            debug_log("Super Admin login successful.");
            $_SESSION['user_role'] = 'super_admin';
            $_SESSION['user_id'] = 0;
            $_SESSION['user_name'] = 'Super Admin';
            session_regenerate_id(true);
            return true;
        } else {
            debug_log("Super Admin password mismatch.");
        }
    }

    // 2. Check Club Admin
    $clubs = get_clubs();
    foreach ($clubs as $club) {
        if ($club['login_name'] === $username && $club['active']) {
            debug_log("Found club user: '{$club['name']}'. Verifying hash...");
            if (password_verify($password, $club['password_hash'])) {
                debug_log("Club login successful.");
                $_SESSION['user_role'] = 'club_admin';
                $_SESSION['user_id'] = $club['id'];
                $_SESSION['user_name'] = $club['name'];
                $_SESSION['club_color'] = $club['color'];
                session_regenerate_id(true);
                return true;
            } else {
                debug_log("Club password mismatch.");
            }
        }
    }

    debug_log("Login failed for user: '$username'");
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
