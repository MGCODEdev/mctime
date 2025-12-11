<?php
require_once 'inc/auth.php';

$error = '';
$success = '';

// Handle Public Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['public_login'])) {
    $pass = $_POST['public_password'] ?? '';
    if (login_public($pass)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Ungültige Zugangsdaten (Öffentlich).';
    }
}

// Handle Admin Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        if (is_super_admin()) {
            header('Location: admin_clubs.php');
        } else {
            header('Location: admin_events.php');
        }
        exit;
    } else {
        $error = 'Ungültige Zugangsdaten (Admin).';
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoCalendar - Login</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>

<body class="login-page">
    <div class="login-wrapper">
        <div class="login-header">
            <h1 class="brand-title">MotoCalendar</h1>
            <p class="brand-subtitle">Terminverwaltung für Motorradclubs</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger custom-alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="glass-card mb-4">
            <div class="card-header-custom">Öffentlicher Zugang</div>
            <div class="card-body-custom">
                <form method="post">
                    <div class="mb-3">
                        <label for="public_password" class="form-label-custom">Passwort</label>
                        <input type="password" class="form-control form-control-custom" id="public_password"
                            name="public_password" required placeholder="••••••••">
                    </div>
                    <button type="submit" name="public_login" class="btn btn-custom w-100">Kalender ansehen</button>
                </form>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header-custom">Club / Admin Login</div>
            <div class="card-body-custom">
                <form method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label-custom">Benutzername</label>
                        <input type="text" class="form-control form-control-custom" id="username" name="username"
                            required placeholder="Benutzername">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label-custom">Passwort</label>
                        <input type="password" class="form-control form-control-custom" id="password" name="password"
                            required placeholder="••••••••">
                    </div>
                    <button type="submit" name="admin_login" class="btn btn-custom-primary w-100">Anmelden</button>
                </form>
            </div>
        </div>

        <div class="debug-hint mt-4 text-center text-muted">
            <small>Debug Log: data/debug.log</small>
        </div>
    </div>
</body>

</html>