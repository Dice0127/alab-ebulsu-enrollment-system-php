<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
csrf_verify();

// Must be logged in as student
if (!isset($_SESSION['student_account_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit enrollment.']);
    exit;
}

$accountId = (int)$_SESSION['student_account_id'];

// Check if account already has an enrollment (can add more courses)
$existingStmt = $conn->prepare("SELECT enrollment_id, semester, school_year FROM enrollment WHERE account_id = ? ORDER BY enrollment_id DESC LIMIT 1");
$existingStmt->bind_param('i', $accountId);
$existingStmt->execute();
$existingEnrollment = $existingStmt->get_result();
$isReEnroll = $existingEnrollment && $existingEnrollment->num_rows > 0;
$enrollmentId = null;

if ($isReEnroll) {
    $existing = $existingEnrollment->fetch_assoc();
    $enrollmentId = $existing['enrollment_id'];
    // For re-enrollment, use same semester/school year as original
    $sem = $existing['semester'];
    $sy = $existing['school_year'];
}

function val($v) { return trim($v ?? ''); }

$fn  = val($_POST['first_name'] ?? '');
$mn  = val($_POST['middle_name'] ?? '');
$ln  = val($_POST['last_name'] ?? '');
$gen = val($_POST['gender'] ?? '');
$bd  = val($_POST['birthday'] ?? '');
$em  = val($_POST['email'] ?? '');
$cn  = val($_POST['contact_number'] ?? '');
$ad  = val($_POST['address'] ?? '');
$pc  = val($_POST['program_code'] ?? '');
$yl  = val($_POST['year_level'] ?? '');
$sid = (int)($_POST['section_id'] ?? 0);
$st  = val($_POST['student_type'] ?? '');

// Get semester text for conversion
$semText = val($_POST['semester'] ?? '');
$syText  = val($_POST['school_year'] ?? '');

// Convert semester text to number (e.g., "1st Semester" → 1)
$semesterNum = 1;
if (strpos($semText, '2nd') !== false) $semesterNum = 2;
elseif (strpos($semText, '3rd') !== false) $semesterNum = 3;
elseif (strpos($semText, 'Summer') !== false) $semesterNum = 99;

// For re-enrollment, use existing semester/year; for new enrollment, use posted values
if (!$isReEnroll) {
    $sem = $semText;
    $sy = $syText;

    if (!$fn || !$ln || !$gen || !$bd || !$em || !$cn || !$ad || !$pc || !$yl || !$sem || !$sy || !$sid || !$st) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
}

// ── Auto-generate Student Number: YYYY-NNNN ───────────────────
$year = date('Y');
$yearPrefix = $year . '-%';

// Get last student number for this year to increment
$lastStmt = $conn->prepare(
    "SELECT student_number FROM enrollment
     WHERE student_number LIKE ?
     ORDER BY enrollment_id DESC LIMIT 1"
);
$lastStmt->bind_param('s', $yearPrefix);
$lastStmt->execute();
$lastRes = $lastStmt->get_result();

if ($lastRes->num_rows > 0) {
    $last = $lastRes->fetch_assoc()['student_number'];
    $seq  = (int)substr($last, 5) + 1;
} else {
    $seq = 1;
}

$conn->begin_transaction();

// Only validate and update section capacity if NEW enrollment
if (!$isReEnroll) {
    // Validate section availability and update capacity
    $sectionStmt = $conn->prepare("SELECT section_id, section_code, section_name, current_enrolled, max_capacity, status FROM sections WHERE section_id = ? FOR UPDATE");
    $sectionStmt->bind_param('i', $sid);
    $sectionStmt->execute();
    $sectionRes = $sectionStmt->get_result();
    if (!$sectionRes || $sectionRes->num_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Selected section is invalid.']);
        exit;
    }
    $section = $sectionRes->fetch_assoc();
    if ($section['status'] === 'Closed' || (int)$section['current_enrolled'] >= (int)$section['max_capacity']) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Selected section is already full or closed.']);
        exit;
    }

    $updateStmt = $conn->prepare(
        "UPDATE sections SET current_enrolled = current_enrolled + 1,
             status = CASE WHEN current_enrolled + 1 >= max_capacity THEN 'Full' ELSE status END
         WHERE section_id = ? AND current_enrolled < max_capacity"
    );
    $updateStmt->bind_param('i', $sid);
    $updateStmt->execute();
    if ($conn->affected_rows === 0) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Unable to reserve a slot in the selected section.']);
        exit;
    }

    // ── Auto-generate Student Number: YYYY-NNNN ───────────────────
    $studentNumber = $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

    // Insert NEW enrollment record
    $insertStmt = $conn->prepare("INSERT INTO enrollment
            (student_number, account_id, first_name, middle_name, last_name, gender, birthday,
             email, contact_number, address, program_code, year_level, section_id, semester, school_year, student_type, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'Pending')");
    $insertStmt->bind_param(
        'sissssssssssisss',
        $studentNumber, $accountId, $fn, $mn, $ln, $gen, $bd,
        $em, $cn, $ad, $pc, $yl, $sid, $sem, $sy, $st
    );

    if (!$insertStmt->execute()) {
        $conn->rollback();
        error_log('enroll_submit.php insert error: ' . $insertStmt->error);
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
        exit;
    }

    $envid = $conn->insert_id;
} else {
    // RE-ENROLLMENT: Use existing enrollment ID
    $envid = $enrollmentId;
    // Get the student number from existing enrollment
    $sStmt = $conn->prepare("SELECT student_number FROM enrollment WHERE enrollment_id = ?");
    $sStmt->bind_param('i', $envid);
    $sStmt->execute();
    $sRow = $sStmt->get_result()->fetch_assoc();
    $studentNumber = $sRow['student_number'];
}

// Get selected course IDs from form
$selectedCourseIds = isset($_POST['course_ids']) ? array_map('intval', $_POST['course_ids']) : [];
if (empty($selectedCourseIds)) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Please select at least one course to enroll.']);
    exit;
}

// Insert each selected course into enrollment_courses (skip if already enrolled)
$chkCourseStmt    = $conn->prepare("SELECT 1 FROM enrollment_courses WHERE enrollment_id = ? AND course_id = ?");
$insertCourseStmt = $conn->prepare("INSERT INTO enrollment_courses (enrollment_id, course_id) VALUES (?, ?)");

foreach ($selectedCourseIds as $courseId) {
    $courseId = (int)$courseId;

    // Check if already enrolled in this course
    $chkCourseStmt->bind_param('ii', $envid, $courseId);
    $chkCourseStmt->execute();
    if ($chkCourseStmt->get_result()->num_rows > 0) {
        continue; // Skip if already enrolled
    }

    $insertCourseStmt->bind_param('ii', $envid, $courseId);
    if (!$insertCourseStmt->execute()) {
        $conn->rollback();
        error_log('enroll_submit.php course insert error: ' . $insertCourseStmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to save enrolled subjects. Please try again.']);
        exit;
    }
}

$conn->commit();

echo json_encode([
    'success'        => true,
    'message'        => 'Enrollment submitted successfully!',
    'student_number' => $studentNumber
]);
?>
