<?php
require_once 'inc/auth.php';
require_login();
require_super_admin();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save_admin') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $id = $_POST['id'] ?? '';

            if (!$username) {
                $error = "Benutzername darf nicht leer sein.";
            } elseif (!$id && !$password) {
                $error = "Passwort ist für neue Benutzer erforderlich.";
            } else {
                $data = ['username' => $username];
                if ($id)
                    $data['id'] = $id;
                if ($password)
                    $data['password'] = $password;

                // Check for duplicate username on new
                // Simplification for now: Database unique constraint will handle it, but catch exception?
                // data.php save_super_admin doesn't catch duplicates yet. 
                // Let's rely on PDO exception for now or check.

                try {
                    if (save_super_admin($data)) {
                        header("Location: admin_users.php?success=saved");
                        exit;
                    } else {
                        $error = "Fehler beim Speichern. Benutzername bereits vergeben?";
                    }
                } catch (Exception $e) {
                    $error = "Fehler: " . $e->getMessage();
                }
            }
        } elseif ($_POST['action'] === 'delete_admin') {
            $id = $_POST['id'];
            // Prevent self-deletion if it's the current user?
            if ($id == $_SESSION['user_id']) {
                $error = "Sie können sich nicht selbst löschen.";
            } else {
                if (delete_super_admin($id)) {
                    header("Location: admin_users.php?success=deleted");
                    exit;
                } else {
                    $error = "Fehler beim Löschen.";
                }
            }
        }
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'saved')
        $success = "Benutzer gespeichert.";
    if ($_GET['success'] === 'deleted')
        $success = "Benutzer gelöscht.";
}

$users = get_super_admins();
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Administratoren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=6" rel="stylesheet">
</head>

<body>
    <?php include 'inc/navbar.php'; ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Administratoren verwalten</h2>
            <button class="btn btn-primary" onclick="newUser()">Neuer Administrator</button>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success custom-alert"
                style="color: #86efac; background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2);">
                <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Benutzername</th>
                            <th>Erstellt am</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['id']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-secondary"
                                        onclick='editUser(<?php echo json_encode($u); ?>)'>Bearbeiten</button>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="post" class="d-inline"
                                            onsubmit="return confirm('Benutzer wirklich löschen?');">
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-light">
                <form method="post">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="modalTitle">Neuer Administrator</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_admin">
                        <input type="hidden" name="id" id="user_id">

                        <div class="mb-3">
                            <label class="form-label">Benutzername</label>
                            <input type="text" class="form-control" name="username" id="user_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Passwort</label>
                            <input type="password" class="form-control" name="password" id="user_password"
                                placeholder="Leer lassen um nicht zu ändern">
                            <small class="text-muted d-block mt-1">Nur ausfüllen zum Ändern oder bei
                                Neuerstellung.</small>
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
        const modal = new bootstrap.Modal(document.getElementById('editUserModal'));

        function newUser() {
            document.getElementById('modalTitle').innerText = 'Neuer Administrator';
            document.getElementById('user_id').value = '';
            document.getElementById('user_username').value = '';
            document.getElementById('user_password').value = '';
            document.getElementById('user_password').required = true;
            modal.show();
        }

        function editUser(user) {
            document.getElementById('modalTitle').innerText = 'Administrator bearbeiten';
            document.getElementById('user_id').value = user.id;
            document.getElementById('user_username').value = user.username;
            document.getElementById('user_password').value = '';
            document.getElementById('user_password').required = false;
            modal.show();
        }
    </script>
</body>

</html>