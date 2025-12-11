<?php
// init_users.php
// Initializes the Users table and creates the default Super Admin.

require_once 'inc/config.php';
require_once 'inc/db.php';

echo "<h1>Initialize Users</h1>";

try {
    $pdo = get_db();

    // Ensure table exists (init_db in inc/db.php has the CREATE logic, but we can call it explicitly or run it here)
    // init_db($pdo) checks IF NOT EXISTS, so safe to call.
    init_db($pdo);
    echo "<p>[OK] Table `users` checked/created.</p>";

    // Create Default Admin
    $username = 'admin';
    $password = 'admin'; // Default password
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'super_admin';

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<p>[INFO] User '$username' already exists. Updating password...</p>";
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash, role = :role WHERE username = :username");
        $stmt->execute([':hash' => $hash, ':role' => $role, ':username' => $username]);
        echo "<p style='color:green'>[SUCCESS] Password updated to default ('admin').</p>";
    } else {
        echo "<p>[INFO] Creating user '$username'...</p>";
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :hash, :role)");
        $stmt->execute([':username' => $username, ':hash' => $hash, ':role' => $role]);
        echo "<p style='color:green'>[SUCCESS] User created.</p>";
    }

    echo "<p>Checking DB content:</p>";
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>" . print_r($rows, true) . "</pre>";

    echo "<p><a href='login.php'>Go to Login</a></p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>[FATAL] Database Error: " . $e->getMessage() . "</p>";
}
?>