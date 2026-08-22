<?php
// ============================================================
//  conn.php — Database Connection
//  IT 211 — Student Enrollment Management System
// ============================================================
require_once __DIR__ . '/env.php';

// getenv() can be unreliable on some shared hosts where putenv() is
// disabled for security reasons (open_basedir/suexec setups). load_env()
// also populates $_ENV and $_SERVER directly, so fall back to those.
function env_get(string $key, string $default = ''): string {
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (!empty($_ENV[$key])) return $_ENV[$key];
    if (!empty($_SERVER[$key])) return $_SERVER[$key];
    return $default;
}

$db_host = env_get('DB_HOST', 'localhost');
$db_user = env_get('DB_USER', 'root');
$db_pass = env_get('DB_PASS', '');
$db_name = env_get('DB_NAME', 'websys_db');

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // Log the real error for developers, but never expose DB details to the client
    error_log('DB connection failed: ' . mysqli_connect_error());
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']));
}
?>