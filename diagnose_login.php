<?php
require_once 'inc/config.php';
require_once 'inc/data.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_admin'])) {
        $pass = $_POST['admin_pass'];
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Read config file
        $config_file = __DIR__ . '/inc/config.php';
        $content = file_get_contents($config_file);

        // Replace hash
        $pattern = "/define\('SUPER_ADMIN_PASS_HASH', '.*'\);/";
        $replacement = "define('SUPER_ADMIN_PASS_HASH', '$hash');";
        $new_content = preg_replace($pattern, $replacement, $content);

        if ($new_content && file_put_contents($config_file, $new_content)) {
            $message .= "Super Admin Passwort geändert.<br>";
        } else {
            $message .= "Fehler beim Schreiben von config.php.<br>";
        }
    }

    if (isset($_POST['set_public'])) {
        $pass = $_POST['public_pass'];
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $settings = get_settings();
        $settings['public_pass_hash'] = $hash;

        if (save_settings($settings)) {
            $message .= "Öffentliches Passwort geändert.<br>";
        } else {
            $message .= "Fehler beim Speichern der Settings.<br>";
        }
    }
}

// Read current values
$settings = get_settings();
$public_hash = $settings['public_pass_hash'] ?? 'Nicht gesetzt';
$admin_hash = defined('SUPER_ADMIN_PASS_HASH') ? SUPER_ADMIN_PASS_HASH : 'Nicht definiert';

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Login Diagnose & Fix</title>
    <style>
        body {
            font-family: sans-serif;
            background: #222;
            color: #eee;
            padding: 2rem;
        }

        .card {
            background: #333;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border: 1px solid #444;
        }

        input[type="text"] {
            padding: 0.5rem;
            width: 300px;
        }

        button {
            padding: 0.5rem 1rem;
            cursor: pointer;
            background: #d32f2f;
            color: white;
            border: none;
            border-radius: 4px;
        }

        button:hover {
            background: #b71c1c;
        }

        code {
            background: #111;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: monospace;
            word-break: break-all;
        }

        .success {
            color: #86efac;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <h1>Login Diagnose & Fix</h1>

    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Super Admin</h2>
        <p>Aktueller Hash in Config: <code><?php echo htmlspecialchars($admin_hash); ?></code></p>

        <form method="post">
            <label>Neues Passwort setzen:</label><br>
            <input type="text" name="admin_pass" placeholder="z.B. admin" required>
            <button type="submit" name="set_admin">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Öffentlicher Zugang</h2>
        <p>Aktueller Hash in Settings: <code><?php echo htmlspecialchars($public_hash); ?></code></p>

        <form method="post">
            <label>Neues Passwort setzen:</label><br>
            <input type="text" name="public_pass" placeholder="z.B. moto2025" required>
            <button type="submit" name="set_public">Speichern</button>
        </form>
    </div>

    <p><a href="login.php" style="color: #fff;">Zurück zum Login</a></p>
</body>

</html>