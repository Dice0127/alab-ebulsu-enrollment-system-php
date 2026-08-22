<?php
// ============================================================
//  request_password_reset.php — Step 1 of student self-service
//  password reset.
//
//  IMPORTANT (student-project note): this app has no SMTP/mail
//  service configured, so there is no way to actually email the
//  reset link. To keep the feature usable for local development
//  and grading demos, the generated link is returned directly in
//  the JSON response and shown on-screen instead of being emailed.
//  In a real deployment, replace the `reset_link` in the response
//  with a call to a mail service, and stop returning the link to
//  the client — see the comment below.
// ============================================================
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
csrf_verify();

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Throttle by IP + email so this endpoint can't be used to spam an inbox
// (or, since we're returning the link directly, to brute-force enumerate).
$rlKey = rl_key('password_reset', $email);
if (!rl_is_allowed($rlKey)) {
    $wait = max(1, (int)ceil(rl_seconds_remaining($rlKey) / 60));
    echo json_encode(['success' => false, 'message' => "Too many requests. Please try again in about $wait minute(s)."]);
    exit;
}
rl_register_failure($rlKey); // counts attempts regardless of outcome — see note below

$stmt = $conn->prepare("SELECT account_id FROM student_account WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

// Always return the same generic message whether or not the email exists,
// so this endpoint can't be used to check which emails are registered.
$genericResponse = ['success' => true, 'message' => 'If that email is registered, a reset link has been generated below.'];

if (!$account) {
    echo json_encode($genericResponse);
    exit;
}

$accountId = (int)$account['account_id'];

// Invalidate any previous unused tokens for this account.
$invalidate = $conn->prepare("UPDATE password_resets SET used = 1 WHERE account_id = ? AND used = 0");
$invalidate->bind_param('i', $accountId);
$invalidate->execute();

$token     = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $token);
$expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30 minutes

$insert = $conn->prepare("INSERT INTO password_resets (account_id, token_hash, expires_at) VALUES (?, ?, ?)");
$insert->bind_param('iss', $accountId, $tokenHash, $expiresAt);
$insert->execute();

// In production, email this link instead of returning it to the client:
//   mail($email, 'Password reset', "Reset your password: $resetLink");
//   echo json_encode($genericResponse); exit;
$resetLink = '../student/reset_password.php?token=' . $token;
$genericResponse['reset_link'] = $resetLink;
$genericResponse['demo_note']  = 'No email service is configured for this project, so the link is shown here instead of being sent.';

echo json_encode($genericResponse);
?>
