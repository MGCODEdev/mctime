<?php
require_once 'inc/auth.php';
$is_super_admin = is_super_admin();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Release Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">MotoCalendar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="index.php">Kalender</a>
                    <?php if (is_logged_in()): ?>
                        <?php if (is_super_admin()): ?>
                            <a class="nav-link" href="admin_clubs.php">Admin</a>
                        <?php else: ?>
                            <a class="nav-link" href="admin_events.php">Verwaltung</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a class="nav-link active" href="info.php">Info</a>
                    <?php if (is_logged_in()): ?>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="glass-card mb-4 p-4">
            <h1 class="text-uppercase fw-bold mb-4">Release Notes</h1>

            <div class="mb-5">
                <h3 class="text-primary border-bottom border-secondary pb-2 mb-3">v1.1.0 - Multi-Calendar Update</h3>
                <ul class="list-unstyled text-light">
                    <li class="mb-2">✨ <strong>Zwei Kalender-System:</strong> Trennung in "Open Road" (Öffentlich) und
                        "Iron Circle" (Intern).</li>
                    <li class="mb-2">🌐 <strong>Kombinierte Ansicht:</strong> Neuer Tab "Alle Termine" für die Übersicht
                        aller Events.</li>
                    <li class="mb-2">🔒 <strong>Verbesserte Zugriffssteuerung:</strong> Interne Termine nur im "Iron
                        Circle" oder "Alle" Modus sichtbar.</li>
                    <li class="mb-2">📋 <strong>Admin-Übersicht:</strong> Neue Spalte "Kalender" in der Terminverwaltung
                        zeigt Sichtbarkeit an.</li>
                    <li class="mb-2">🛠️ <strong>Datenbank-Upgrade:</strong> Automatische Schema-Erweiterung für die
                        Sichtbarkeits-Option.</li>
                </ul>
            </div>

            <div class="mb-5">
                <h3 class="text-secondary border-bottom border-secondary pb-2 mb-3">v1.0.0 - Initial Release</h3>
                <ul class="list-unstyled text-light">
                    <li class="mb-2">📅 <strong>Basis-Kalender:</strong> Monatsansicht mit Terminen.</li>
                    <li class="mb-2">🏍️ <strong>Club-Verwaltung:</strong> Anlegen und Bearbeiten von Clubs mit Logos
                        und Farben.</li>
                    <li class="mb-2">📝 <strong>Termin-Verwaltung:</strong> CRUD-Funktionen für Events.</li>
                    <li class="mb-2">🔐 <strong>Login-System:</strong> Abgesicherter Admin-Bereich und öffentlicher
                        Zugangsschutz.</li>
                </ul>
            </div>

            <div class="alert alert-info mt-4">
                <strong>System Info:</strong> PHP <?php echo phpversion(); ?> | Server:
                <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>