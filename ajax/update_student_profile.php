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
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birthday = trim($_POST['birthday'] ?? '');
$contactNumber = trim($_POST['contact_number'] ?? '');
$enrollmentEmail = trim($_POST['enrollment_email'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validation
if (empty($firstName) || empty($lastName) || empty($gender) || empty($birthday) || 
    empty($contactNumber) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// First, always update the student account record (where registration info is stored)
$stmt = $conn->prepare("UPDATE student_account SET 
    first_name = ?,
    last_name = ?,
    gender = ?,
    birthday = ?,
    contact_number = ?,
    address = ?
    WHERE account_id = ?");
$stmt->bind_param('ssssssi', $firstName, $lastName, $gender, $birthday, $contactNumber, $address, $accountId);

if (!$stmt->execute()) {
    error_log('update_student_profile.php account update error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to update account. Please try again.']);
    exit;
}

// Get current enrollment (if exists)
$enrollStmt = $conn->prepare("SELECT enrollment_id FROM enrollment WHERE account_id = ? ORDER BY enrollment_id DESC LIMIT 1");
$enrollStmt->bind_param('i', $accountId);
$enrollStmt->execute();
$enrollResult = $enrollStmt->get_result();

if ($enrollResult && $enrollResult->num_rows > 0) {
    $enrollment = $enrollResult->fetch_assoc();
    $enrollmentId = intval($enrollment['enrollment_id']);
    
    // Also update enrollment record if it exists
    $updateEnrollStmt = $conn->prepare("UPDATE enrollment SET 
        first_name = ?,
        last_name = ?,
        gender = ?,
        birthday = ?,
        contact_number = ?,
        email = ?,
        address = ?
        WHERE enrollment_id = ?");
    $updateEnrollStmt->bind_param('sssssssi', $firstName, $lastName, $gender, $birthday, $contactNumber, $enrollmentEmail, $address, $enrollmentId);
    if (!$updateEnrollStmt->execute()) {
        error_log('update_student_profile.php enrollment update error: ' . $updateEnrollStmt->error);
    }
}

// Update session name
$_SESSION['student_name'] = "$firstName $lastName";

echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
