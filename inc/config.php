<?php
// Global Configuration

// Paths
define('BASE_PATH', __DIR__ . '/../');
define('DATA_PATH', BASE_PATH . 'data/');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'motocalendar');
define('DB_USER', 'moto_user');
define('DB_PASS', 'geheimes_passwort'); // Bitte ändern!

// Default Settings
define('DEFAULT_CALENDAR_VIEW', 'month');

// Super Admin Credentials (Initial Setup)
// Password: 'admin'
define('SUPER_ADMIN_USER', 'admin');
// Hash for 'admin'
define('SUPER_ADMIN_PASS_HASH', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa');

// Timezone
date_default_timezone_set('Europe/Berlin');
