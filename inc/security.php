<?php

class Security
{
    /**
     * Initialize secure session settings.
     * Call this before session_start().
     */
    public static function initSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            // Secure Session Cookies
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);

            // Only set secure if using HTTPS (detect or force)
            // For local dev without HTTPS, this might break login if enforced strictly.
            // Check if HTTPS is on.
            $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            if ($is_https) {
                ini_set('session.cookie_secure', 1);
            }

            ini_set('session.cookie_samesite', 'Strict');

            session_start();
        }
    }

    /**
     * Generate a CSFR token and store it in session.
     * @return string The token to include in forms.
     */
    public static function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Get the CSRF input field HTML.
     */
    public static function csrfField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Verify the CSRF token from POST request.
     * Dies or returns false if invalid.
     */
    public static function verifyCsrfToken()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                // Log this event?
                die('Security Error: Invalid CSRF Token. Please refresh the page.');
            }
        }
        return true;
    }

    /**
     * Set standard security headers.
     */
    public static function setHeaders()
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');
        // XSS Protection (legacy but good to have)
        header('X-XSS-Protection: 1; mode=block');
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }

    /**
     * Sanitize a filename to prevent path traversal.
     * Only allows alphanumeric, underscores, dashes, and dots.
     */
    public static function sanitizeFilename($filename)
    {
        // Remove anything that isn't a-z, A-Z, 0-9, dot, dash, underscore
        $clean = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $filename);
        // Prevent double dots just in case regex missed something
        $clean = str_replace('..', '', $clean);
        return $clean;
    }
}
?>