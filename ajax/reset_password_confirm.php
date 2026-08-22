<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
csrf_verify();

$token       = trim($_POST['token'] ?? '');
$newPassword = (string)($_POST['new_password'] ?? '');

if ($token === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing reset token.']);
    exit;
}
if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$tokenHash = hash('sha256', $token);

$stmt = $conn->prepare(
    "SELECT reset_id, account_id, expires_at, used FROM password_resets WHERE token_hash = ?"
);
$stmt->bind_param('s', $tokenHash);
$stmt->execute();
$reset = $stmt->get_result()->fetch_assoc();

if (!$reset || (int)$reset['used'] === 1 || strtotime($reset['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'This reset link is invalid or has expired. Please request a new one.']);
    exit;
}

$accountId = (int)$reset['account_id'];
$hashed    = password_hash($newPassword, PASSWORD_BCRYPT);

$update = $conn->prepare("UPDATE student_account SET password = ? WHERE account_id = ?");
$update->bind_param('si', $hashed, $accountId);

if (!$update->execute()) {
    error_log('reset_password_confirm.php update error: ' . $update->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    exit;
}

// Mark this token used and invalidate any other outstanding tokens for the account.
$markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE account_id = ?");
$markUsed->bind_param('i', $accountId);
$markUsed->execute();

echo json_encode(['success' => true, 'message' => 'Password reset successfully! You can now log in with your new password.']);
?>
