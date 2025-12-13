<?php
require_once 'inc/auth.php';
require_once 'inc/logging.php';
require_super_admin();

// Auto-cleanup old logs (30 days)
cleanup_system_logs(30);

$limit = $_GET['limit'] ?? 100;
$logs = get_system_logs($limit);

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - System Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>System Logs</h2>
            <div>
                <a href="admin_logs.php" class="btn btn-secondary btn-sm"
                    onclick="return confirm('Logs älter als 30 Tage werden beim Laden dieser Seite automatisch gelöscht.');">Aktualisieren</a>
            </div>
        </div>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Zeitpunkt</th>
                            <th>Aktion</th>
                            <th>Benutzer (Rolle)</th>
                            <th>IP</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 0.9rem;">
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $class = '';
                            if (strpos($log['action'], 'DELETE') !== false || strpos($log['action'], 'BLOCKED') !== false || strpos($log['action'], 'DENIED') !== false) {
                                $class = 'text-danger';
                            } elseif (strpos($log['action'], 'CREATE') !== false) {
                                $class = 'text-success';
                            }
                            ?>
                            <tr>
                                <td><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?></td>
                                <td class="<?php echo $class; ?> fw-bold"><?php echo htmlspecialchars($log['action']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($log['username']); ?>
                                    <span class="text-muted small">
                                        (<?php echo htmlspecialchars($log['user_role'] ?? '-'); ?>)
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($log['details']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>