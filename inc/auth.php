<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/security.php';

// Initialize Secure Session
Security::initSession();
Security::setHeaders();

function debug_log($message)
{
    $log_file = DATA_PATH . 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function check_brute_force($username)
{
    $pdo = get_db();
    $ip = $_SERVER['REMOTE_ADDR'];

    // Count failed attempts in last 15 minutes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = :ip AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
    $stmt->execute([':ip' => $ip]);
    $count = $stmt->fetchColumn();

    if ($count >= 5) {
        debug_log("Brute force blocked for IP: $ip");
        return true; // Blocked
    }
    return false;
}

function record_failed_login($username)
{
    $pdo = get_db();
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, username) VALUES (:ip, :u)");
    $stmt->execute([':ip' => $ip, ':u' => $username]);
}

function login($username, $password)
{
    if (check_brute_force($username)) {
        return 'blocked';
    }

    debug_log("Login attempt for user: '$username'");

    // 1. Check Super Admin (Database Table)
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT * FROM super_admins WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $admin = $stmt->fetch();

    if ($admin) {
        debug_log("User matches Super Admin (DB). Verifying hash...");
        if (password_verify($password, $admin['password_hash'])) {
            debug_log("Super Admin login successful.");
            $_SESSION['user_role'] = 'super_admin';
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['username'];
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
    record_failed_login($username);
    return false;
}

function login_public($password)
{
    debug_log("Public login attempt.");
    $settings = get_settings();

    if (!isset($settings['public_pass_hash'])) {
        debug_log("ERROR: public_pass_hash not set in settings.json");
        return false;
    }

    if (password_verify($password, $settings['public_pass_hash'])) {
        debug_log("Public login successful.");
        $_SESSION['public_access'] = true;
        return true;
    }

    debug_log("Public login failed. Hash mismatch.");
    return false;
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
