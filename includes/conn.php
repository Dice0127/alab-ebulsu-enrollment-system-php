<?php
// ============================================================
//  conn.php — Database Connection
//  IT 211 — Student Enrollment Management System
// ============================================================
require_once __DIR__ . '/env.php';

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'websys_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    // Log the real error for developers, but never expose DB details to the client
    error_log('DB connection failed: ' . mysqli_connect_error());
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']));
}
?>
