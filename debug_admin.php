<?php
// debug_admin.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Login Debugger</h1>";

// 1. Check File Existence
$config_path = __DIR__ . '/inc/config.php';
echo "Checking config file at: <code>$config_path</code>... ";
if (file_exists($config_path)) {
    echo "<span style='color:green'>FOUND</span><br>";
} else {
    echo "<span style='color:red'>NOT FOUND</span><br>";
    die("Config file missing.");
}

// 2. Include Config
require_once $config_path;

// 3. Dump Constants
echo "<h2>Configuration Values</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Constant</th><th>Value</th></tr>";

if (defined('SUPER_ADMIN_USER')) {
    echo "<tr><td>SUPER_ADMIN_USER</td><td>" . htmlspecialchars(SUPER_ADMIN_USER) . "</td></tr>";
} else {
    echo "<tr><td>SUPER_ADMIN_USER</td><td><span style='color:red'>NOT DEFINED</span></td></tr>";
}

if (defined('SUPER_ADMIN_PASS_HASH')) {
    echo "<tr><td>SUPER_ADMIN_PASS_HASH</td><td>" . htmlspecialchars(SUPER_ADMIN_PASS_HASH) . "</td></tr>";
} else {
    echo "<tr><td>SUPER_ADMIN_PASS_HASH</td><td><span style='color:red'>NOT DEFINED</span></td></tr>";
}
echo "</table>";

// 4. Test Verification
echo "<h2>Test Password</h2>";
$test_pass = $_POST['test_pass'] ?? '';
$result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (defined('SUPER_ADMIN_PASS_HASH')) {
        $verify = password_verify($test_pass, SUPER_ADMIN_PASS_HASH);
        if ($verify) {
            $result = "<h3 style='color:green'>SUCCESS: Password matches hash!</h3>";
        } else {
            $result = "<h3 style='color:red'>FAILURE: Password does NOT match hash.</h3>";
            $result .= "Hash info: " . print_r(password_get_info(SUPER_ADMIN_PASS_HASH), true);
        }
    } else {
        $result = "Cannot test: Hash not defined.";
    }
}
?>

<form method="post">
    <input type="text" name="test_pass" value="<?php echo htmlspecialchars($test_pass); ?>"
        placeholder="Enter password to test">
    <button type="submit">Verify</button>
</form>

<?php echo $result; ?>

<h2>File Content (First 20 lines)</h2>
<pre style="background:#eee; padding:10px;">
<?php
echo htmlspecialchars(file_get_contents($config_path, false, null, 0, 1000));
?>
</pre>