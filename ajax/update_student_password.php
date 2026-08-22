<?php
require_once '../includes/conn.php';
session_start();
require_once '../includes/csrf.php';
csrf_verify();

if (!isset($_SESSION['student_account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$accountId = intval($_SESSION['student_account_id']);
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['password'] ?? '';

// Validation
if (empty($currentPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current password is required']);
    exit;
}

if (empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'New password cannot be empty']);
    exit;
}

if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Verify current password
$stmt = $conn->prepare("SELECT password FROM student_account WHERE account_id = ?");
$stmt->bind_param('i', $accountId);
$stmt->execute();
$result = $stmt->get_result();
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update password
$updateStmt = $conn->prepare("UPDATE student_account SET password = ? WHERE account_id = ?");
$updateStmt->bind_param('si', $hashedPassword, $accountId);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    error_log('update_student_password.php DB error: ' . $updateStmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
