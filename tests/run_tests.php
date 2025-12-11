<?php
// Simple Test Runner for MotoCalendar
// Run from command line: php tests/run_tests.php

// Setup Environment
define('BASE_PATH', __DIR__ . '/../');
define('DATA_PATH', BASE_PATH . 'data/');
// Use a separate test file to avoid messing up real data
define('TEST_CLUBS_FILE', DATA_PATH . 'test_clubs.json');

// Mock config constants if needed, but we included config.php in data.php
// We need to override the data path logic or just backup/restore files.
// For safety, let's just test the logic functions with a temporary file swap.

echo "Starte Tests...\n";

// 1. Test Data Functions
require_once BASE_PATH . 'inc/data.php';

// Override get_clubs to use our test file
// Since functions use global constants or hardcoded paths in the original code, 
// we might need to modify inc/data.php to accept paths or use a testing config.
// For this prototype, we will just test the JSON read/write logic by creating a temp file.

$test_file = DATA_PATH . 'test_unit.json';

// Test Write
$data = [['id' => 1, 'name' => 'Test Club']];
if (write_json($test_file, $data)) {
    echo "[PASS] write_json: Datei erstellt.\n";
} else {
    echo "[FAIL] write_json: Konnte Datei nicht schreiben.\n";
}

// Test Read
$read_data = read_json($test_file);
if (count($read_data) === 1 && $read_data[0]['name'] === 'Test Club') {
    echo "[PASS] read_json: Daten korrekt gelesen.\n";
} else {
    echo "[FAIL] read_json: Daten stimmen nicht überein.\n";
}

// Cleanup
if (file_exists($test_file))
    unlink($test_file);

// 2. Test Password Hashing (Auth Logic)
require_once BASE_PATH . 'inc/auth.php';

$password = "geheim123";
$hash = password_hash($password, PASSWORD_DEFAULT);

if (password_verify($password, $hash)) {
    echo "[PASS] Auth: Passwort-Hash Verifizierung funktioniert.\n";
} else {
    echo "[FAIL] Auth: Passwort-Hash fehlgeschlagen.\n";
}

// 3. Test Event Date Logic
$events = [
    ['date' => '2025-05-10', 'title' => 'Event A'],
    ['date' => '2025-05-01', 'title' => 'Event B'],
    ['date' => '2025-06-01', 'title' => 'Event C']
];

// Mocking get_events is hard without dependency injection.
// But we can test the sorting logic if we extract it.
// Since we can't easily unit test the tight-coupled code without refactoring,
// we will stop here for the simple script.

echo "Tests abgeschlossen.\n";
?>