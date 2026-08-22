<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';

if (!isset($_SESSION['student_account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$accountId = (int)$_SESSION['student_account_id'];

// Get account info which now includes personal fields from registration
$accountStmt = $conn->prepare("SELECT account_id, email, first_name, middle_name, last_name, gender, 
                       birthday, contact_number, address, created_at
                FROM student_account
                WHERE account_id = ?");
$accountStmt->bind_param('i', $accountId);
$accountStmt->execute();
$accountResult = $accountStmt->get_result();
if (!$accountResult || $accountResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Account not found']);
    exit;
}

$account = $accountResult->fetch_assoc();

// Get latest enrollment data if it exists
$enrollStmt = $conn->prepare(
    "SELECT e.*, p.program_name, c.college_name
     FROM enrollment e
     LEFT JOIN program p ON e.program_code = p.program_code
     LEFT JOIN college c ON p.college_code = c.college_code
     WHERE e.account_id = ?
     ORDER BY e.enrollment_id DESC
     LIMIT 1"
);
$enrollStmt->bind_param('i', $accountId);
$enrollStmt->execute();
$enrollResult = $enrollStmt->get_result();

$enrollment = null;
if ($enrollResult && $enrollResult->num_rows > 0) {
    $enrollment = $enrollResult->fetch_assoc();
}

// Prepare profile response - use enrollment data if available, otherwise account data
$profile = [
    'success' => true,
    'account_id' => $account['account_id'],
    'email' => htmlspecialchars($account['email'] ?? ''),
    'first_name' => htmlspecialchars($enrollment['first_name'] ?? $account['first_name'] ?? ''),
    'middle_name' => htmlspecialchars($enrollment['middle_name'] ?? $account['middle_name'] ?? ''),
    'last_name' => htmlspecialchars($enrollment['last_name'] ?? $account['last_name'] ?? ''),
    'gender' => htmlspecialchars($enrollment['gender'] ?? $account['gender'] ?? ''),
    'birthday' => $enrollment['birthday'] ?? $account['birthday'] ?? '',
    'contact_number' => htmlspecialchars($enrollment['contact_number'] ?? $account['contact_number'] ?? ''),
    'enrollment_email' => htmlspecialchars($enrollment['email'] ?? $account['email'] ?? ''),
    'address' => htmlspecialchars($enrollment['address'] ?? $account['address'] ?? ''),
    'program_name' => htmlspecialchars($enrollment['program_name'] ?? ''),
    'college_name' => htmlspecialchars($enrollment['college_name'] ?? ''),
    'year_level' => htmlspecialchars($enrollment['year_level'] ?? ''),
    'student_number' => htmlspecialchars($enrollment['student_number'] ?? ''),
    'enrollment_status' => htmlspecialchars($enrollment['status'] ?? ''),
    'has_enrollment' => !is_null($enrollment),
    'created_at' => $account['created_at'] ?? ''
];

echo json_encode($profile);
?>
