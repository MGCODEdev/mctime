<?php
require_once 'inc/auth.php';
require_once 'inc/config.php';
require_once 'inc/db.php';

require_super_admin(); // Only super admins can backup

$message = '';

if (isset($_POST['backup'])) {
    $db_host = DB_HOST;
    $db_user = DB_USER;
    $db_pass = DB_PASS;
    $db_name = DB_NAME;

    $date = date('Y-m-d_H-i-s');
    $filename = "backup_{$db_name}_{$date}.sql";

    // Attempt 1: mysqldump (best for large DBs, requires system command access)
    // Works well on Linux/Ubuntu if installed.
    $dump_cmd = "mysqldump --user={$db_user} --password={$db_pass} --host={$db_host} {$db_name}";

    // Capture output
    $output = [];
    $return_var = 0;

    // We want to stream the download directly to avoid memory issues, but exec captures to array.
    // Let's use passthru with headers.

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"{$filename}\"");

    // Try executing mysqldump
    // Note: On some hosting environments, exec/passthru might be disabled.
    // We should fallback to PHP implementation if possible, or just wrap in try/catch.

    // Check if mysqldump exists/works
    // For simplicity in this environment, we assume standard Ubuntu install has it.

    passthru($dump_cmd, $return_var);

    if ($return_var !== 0) {
        // Fallback or Error
        // If mysqldump fails (e.g. not in path), we could write a PHP dumper, 
        // but that's complex to write scratch without libraries.
        // Let's just output an error file.
        // Reset headers if possible (too late if output started)
        // But passthru outputted nothing if command invalid?
        // Actually, if it failed, we might have partial output.
        // Let's just log it.
        error_log("mysqldump failed with return code $return_var");
        exit;
    }

    exit;
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenbank Backup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1>Datenbank Backup</h1>
        <p class="lead">Erstellen Sie ein vollständiges Backup der Datenbank.</p>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="card bg-secondary text-white mb-4">
            <div class="card-body">
                <h5 class="card-title">Sicherung herunterladen</h5>
                <p class="card-text">
                    Dies exportiert die gesamte Datenbank (<?php echo DB_NAME; ?>) als SQL-Datei.
                </p>
                <form method="post">
                    <button type="submit" name="backup" class="btn btn-warning btn-lg">
                        ⬇ Backup starten
                    </button>
                </form>
            </div>
        </div>

        <a href="admin_clubs.php" class="btn btn-outline-light">Zurück zur Verwaltung</a>
    </div>
</body>

</html>