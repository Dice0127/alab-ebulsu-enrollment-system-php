<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
csrf_verify();

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please enter your email and password.']);
    exit;
}

$rlKey = rl_key('student_login', $email);
if (!rl_is_allowed($rlKey)) {
    $wait = max(1, ceil(rl_seconds_remaining($rlKey) / 60));
    echo json_encode(['success' => false, 'message' => "Too many failed attempts. Please try again in about $wait minute(s)."]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM student_account WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    rl_register_failure($rlKey);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

$account = $res->fetch_assoc();

if (!password_verify($password, $account['password'])) {
    rl_register_failure($rlKey);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

rl_reset($rlKey);

// Regenerate the session ID on privilege change to prevent session fixation.
session_regenerate_id(true);

// Set session
$_SESSION['student_account_id'] = $account['account_id'];
$_SESSION['student_email']      = $account['email'];
$_SESSION['student_first_name'] = $account['first_name'];
$_SESSION['student_last_name']  = $account['last_name'];
$_SESSION['student_name']       = $account['first_name'] . ' ' . $account['last_name'];

echo json_encode(['success' => true, 'message' => 'Login successful!']);
?>
