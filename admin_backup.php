<?php
require_once 'inc/auth.php';
require_login();
require_super_admin(); // Security: Only super admins can backup

require_once 'inc/config.php';

if (isset($_POST['backup'])) {

    $filename = 'backup_motocalendar_' . date('Y-m-d_H-i-s') . '.sql';

    // Command to dump database
    // Note: This requires mysqldump to be in the path.
    // If password is set, use -p

    $cmd = sprintf(
        "mysqldump --user=%s --password=%s --host=%s %s",
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_NAME)
    );

    // Capture output
    $output = [];
    $return_var = 0;

    // Using exec to capture headers vs passthru for direct stream
    // For large DBs, streaming is better.

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $filename . "\"");

    // Execute command and output directly to browser
    passthru($cmd, $return_var);

    if ($return_var !== 0) {
        // If it failed (and headers already sent), we have a broken file.
        // But we can't easily undo headers.
        error_log("Backup failed with return code $return_var");
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup - MotoCalendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="index.php">MotoCalendar Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin_clubs.php">Clubs</a>
                <a class="nav-link active" href="admin_backup.php">Backup</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card bg-dark text-light border-secondary">
            <div class="card-header border-secondary">
                <h3 class="mb-0">Datenbank Backup</h3>
            </div>
            <div class="card-body">
                <p>Hier können Sie ein vollständiges Backup der Datenbank herunterladen.</p>
                <div class="alert alert-info">
                    <strong>Hinweis:</strong> Das Backup enthält alle Clubs, Termine und Einstellungen.
                </div>

                <form method="post">
                    <button type="submit" name="backup" class="btn btn-primary btn-lg">
                        ⬇ Backup herunterladen (.sql)
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>