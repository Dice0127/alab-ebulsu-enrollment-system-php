<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
csrf_verify();

$first_name = trim($_POST['first_name'] ?? '');
$middle_name = trim($_POST['middle_name'] ?? '');
$last_name  = trim($_POST['last_name']  ?? '');
$gender = trim($_POST['gender'] ?? '');
$birthday = trim($_POST['birthday'] ?? '');
$email      = trim($_POST['email']      ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$password   = trim($_POST['password'] ?? '');

// Validate required fields
if (!$first_name || !$last_name || !$gender || !$birthday || !$email || !$contact_number || !$address || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

// Validate age (must be at least 15)
$birthDate = new DateTime($birthday);
$today = new DateTime();
$age = $today->diff($birthDate)->y;
if ($age < 15) {
    echo json_encode(['success' => false, 'message' => 'You must be at least 15 years old to register.']);
    exit;
}

// Check duplicate email
$chkStmt = $conn->prepare("SELECT account_id FROM student_account WHERE email = ?");
$chkStmt->bind_param('s', $email);
$chkStmt->execute();
if ($chkStmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
    exit;
}

// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new student account with all personal information
$stmt = $conn->prepare("INSERT INTO student_account (
    email, password, first_name, middle_name, last_name,
    gender, birthday, contact_number, address
) VALUES (?,?,?,?,?,?,?,?,?)");
$stmt->bind_param(
    'sssssssss',
    $email, $hash, $first_name, $middle_name, $last_name,
    $gender, $birthday, $contact_number, $address
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
} else {
    error_log('student_register.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
