<?php
require_once 'inc/auth.php';
require_login();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

$current_year = date('Y');
$selected_year = $_GET['year'] ?? $current_year;

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_event') {
        $id = $_POST['id'] ?: uniqid();
        $title = trim($_POST['title']);
        $date = $_POST['date'];
        $time_from = $_POST['time_from'];
        $time_to = $_POST['time_to'];
        $location = trim($_POST['location']);

        $description = trim($_POST['description']);
        $visibility = $_POST['visibility'] ?? 'public';

        // Club ID: if super admin, get from POST; if club admin, use session
        $club_id = null;
        if (is_super_admin()) {
            $club_id = $_POST['club_id'];
        } else {
            $club_id = get_current_club_id();
        }

        if ($title && $date && $club_id) {
            $event_data = [
                'id' => $id,
                'club_id' => $club_id,
                'title' => $title,
                'date' => $date,
                'time_from' => $time_from,
                'time_to' => $time_to,
                'location' => $location,
                'description' => $description,
                'visibility' => $visibility
            ];

            if (save_event($event_data)) {
                $year = substr($date, 0, 4);
                // Redirect to the year of the event to show it immediately
                header("Location: admin_events.php?year=$year&success=saved");
                exit;
            } else {
                $error = "Fehler beim Speichern.";
            }
        } else {
            $error = "Titel, Datum und Club sind Pflichtfelder.";
        }
    } elseif ($_POST['action'] === 'delete_event') {
        $event_id = $_POST['id'];
        $event_year = substr($_POST['date'], 0, 4);

        if (delete_event($event_id, $event_year)) {
            header("Location: admin_events.php?year=$event_year&success=deleted");
            exit;
        }
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'saved')
        $success = "Termin gespeichert.";
    if ($_GET['success'] === 'deleted')
        $success = "Termin gelöscht.";
}

$events = get_events($selected_year);
$clubs = get_clubs();
$clubs_map = [];
foreach ($clubs as $c)
    $clubs_map[$c['id']] = $c;

// Filter events for Club Admin
if (is_club_admin()) {
    $my_club_id = get_current_club_id();
    $events = array_filter($events, function ($e) use ($my_club_id) {
        return $e['club_id'] == $my_club_id;
    });
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Termine verwalten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=4" rel="stylesheet">
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Termine verwalten (<?php echo $selected_year; ?>)</h2>
            <div>
                <select class="form-select d-inline-block w-auto me-2"
                    onchange="window.location.href='?year='+this.value">
                    <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $selected_year ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <button class="btn btn-primary" onclick="newEvent()">Neuer Termin</button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success custom-alert"
                style="color: #86efac; background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2);">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Datum</th>
                            <th>Zeit</th>
                            <th>Kalender</th>
                            <th>Titel</th>
                            <?php if (is_super_admin()): ?>
                                <th>Club</th><?php endif; ?>
                            <th>Ort</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['date']); ?></td>
                                <td><?php echo htmlspecialchars($event['time_from'] . ' - ' . $event['time_to']); ?></td>
                                <td>
                                    <?php
                                    $vis = $event['visibility'] ?? 'public';
                                    $badgeClass = $vis === 'internal' ? 'bg-warning text-dark' : 'bg-info text-dark';
                                    $visLabel = $vis === 'internal' ? 'Iron Circle' : 'Open Road';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $visLabel; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <?php if (is_super_admin()): ?>
                                    <td>
                                        <?php
                                        $c = $clubs_map[$event['club_id']] ?? null;
                                        echo $c ? htmlspecialchars($c['shortname']) : '?';
                                        ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary"
                                        onclick='editEvent(<?php echo json_encode($event); ?>)'>Bearbeiten</button>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Wirklich löschen?');">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                        <input type="hidden" name="date" value="<?php echo $event['date']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-light">
                <form method="post">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Termin bearbeiten</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_event">
                        <input type="hidden" name="id" id="event_id">

                        <?php if (is_super_admin()): ?>
                            <div class="mb-3">
                                <label class="form-label">Club</label>
                                <select class="form-select" name="club_id" id="event_club_id" required>
                                    <?php foreach ($clubs as $club): ?>
                                        <option value="<?php echo $club['id']; ?>">
                                            <?php echo htmlspecialchars($club['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Titel</label>
                            <input type="text" class="form-control" name="title" id="event_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sichtbarkeit</label>
                            <select class="form-select" name="visibility" id="event_visibility">
                                <option value="public">Öffentlich (Open Road)</option>
                                <option value="internal">Intern (Iron Circle)</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Datum</label>
                                <input type="date" class="form-control" name="date" id="event_date" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Von</label>
                                <input type="time" class="form-control" name="time_from" id="event_time_from">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bis</label>
                                <input type="time" class="form-control" name="time_to" id="event_time_to">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ort</label>
                            <input type="text" class="form-control" name="location" id="event_location">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Beschreibung</label>
                            <textarea class="form-control" name="description" id="event_description"
                                rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Speichern</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('editEventModal'));

        function newEvent() {
            document.getElementById('event_id').value = '';
            document.getElementById('event_title').value = '';
            document.getElementById('event_date').value = new Date().toISOString().split('T')[0];
            document.getElementById('event_time_from').value = '';
            document.getElementById('event_time_to').value = '';
            document.getElementById('event_location').value = '';
            document.getElementById('event_location').value = '';
            document.getElementById('event_description').value = '';
            document.getElementById('event_visibility').value = 'public';
            <?php if (is_super_admin()): ?>
                document.getElementById('event_club_id').value = '<?php echo $clubs[0]['id'] ?? ''; ?>';
            <?php endif; ?>
            modal.show();
        }

        function editEvent(event) {
            document.getElementById('event_id').value = event.id;
            document.getElementById('event_title').value = event.title;
            document.getElementById('event_date').value = event.date;
            document.getElementById('event_time_from').value = event.time_from;
            document.getElementById('event_time_to').value = event.time_to;
            document.getElementById('event_location').value = event.location;
            document.getElementById('event_location').value = event.location;
            document.getElementById('event_description').value = event.description;
            document.getElementById('event_visibility').value = event.visibility || 'public';
            <?php if (is_super_admin()): ?>
                document.getElementById('event_club_id').value = event.club_id;
            <?php endif; ?>
            modal.show();
        }
    </script>
</body>

</html>