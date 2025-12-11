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
// Hash generated with password_hash('admin', PASSWORD_DEFAULT)
define('SUPER_ADMIN_USER', 'admin');
define('SUPER_ADMIN_PASS_HASH', '$2y$10$8/z.z.y.x.w.v.u.t.s.r.q.p.o.n.m.l.k.j.i.h.g.f.e.d.c.b.a'); // Placeholder hash, please change!

// Timezone
date_default_timezone_set('Europe/Berlin');
