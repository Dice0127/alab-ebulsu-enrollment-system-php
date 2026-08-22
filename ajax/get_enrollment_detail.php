<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';

// If no ID provided, get the current student's latest enrollment
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0 && isset($_SESSION['student_account_id'])) {
    $accountId = (int)$_SESSION['student_account_id'];
    $enStmt = $conn->prepare("SELECT enrollment_id FROM enrollment WHERE account_id = ? ORDER BY enrollment_id DESC LIMIT 1");
    $enStmt->bind_param('i', $accountId);
    $enStmt->execute();
    $enRes = $enStmt->get_result();
    if ($enRes->num_rows > 0) {
        $en = $enRes->fetch_assoc();
        $id = $en['enrollment_id'];
    }
}

if ($id <= 0) { 
    echo json_encode(null); 
    exit; 
}

// Access control: an admin may view any enrollment. A logged-in student
// may only view their own. Anyone else is rejected outright — without
// this check, any visitor could view any student's full personal
// details (address, contact number, birthday, email) just by guessing
// an enrollment_id.
$isAdmin = isset($_SESSION['admin_id']);
if (!$isAdmin) {
    if (!isset($_SESSION['student_account_id'])) {
        http_response_code(401);
        echo json_encode(null);
        exit;
    }
    $accountId = (int)$_SESSION['student_account_id'];
    $ownStmt = $conn->prepare("SELECT 1 FROM enrollment WHERE enrollment_id = ? AND account_id = ?");
    $ownStmt->bind_param('ii', $id, $accountId);
    $ownStmt->execute();
    if ($ownStmt->get_result()->num_rows === 0) {
        http_response_code(403);
        echo json_encode(null);
        exit;
    }
}

$stmt = $conn->prepare(
    "SELECT e.*, p.program_name, c.college_name, c.college_code
     FROM enrollment e
     JOIN program p ON e.program_code = p.program_code
     JOIN college c ON p.college_code = c.college_code
     WHERE e.enrollment_id = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

// Get enrolled courses
$coursesStmt = $conn->prepare(
    "SELECT ec.course_id, co.course_code, co.course_name, co.units
     FROM enrollment_courses ec
     JOIN courses co ON ec.course_id = co.course_id
     WHERE ec.enrollment_id = ?
     ORDER BY co.course_code ASC"
);
$coursesStmt->bind_param('i', $id);
$coursesStmt->execute();
$coursesResult = $coursesStmt->get_result();

$courses = [];
if ($coursesResult) {
    while ($course = $coursesResult->fetch_assoc()) {
        $courses[] = $course;
    }
}

if ($row) {
    $row['courses'] = $courses;
}

echo json_encode($row ?: null);
?>
