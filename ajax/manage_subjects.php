<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$action      = val($_POST['action'] ?? 'save');
$course_id   = (int)($_POST['course_id'] ?? 0);
$course_code = val($_POST['course_code'] ?? '');
$course_name = val($_POST['course_name'] ?? '');
$description = val($_POST['description'] ?? '');
$units       = (int)($_POST['units'] ?? 0);
$college_code = val($_POST['college_code'] ?? '');
$program_code = val($_POST['program_code'] ?? '');

if ($action === 'delete') {
    if ($course_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid course specified.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param('i', $course_id);
    if ($stmt->execute()) {
        log_action($conn, 'course_deleted', "course_id={$course_id}");
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully.']);
    } else {
        error_log('manage_subjects.php delete error: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
    exit;
}

if (!$course_code || !$course_name || $units <= 0) {
    echo json_encode(['success' => false, 'message' => 'Course code, name, and units are required.']);
    exit;
}

// Use null (not empty string) when college/program code isn't provided
$college_val = $college_code !== '' ? $college_code : null;
$program_val = $program_code !== '' ? $program_code : null;

if ($action === 'edit') {
    if ($course_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid course specified.']);
        exit;
    }
    $chkStmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ? AND course_id != ?");
    $chkStmt->bind_param('si', $course_code, $course_id);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Another course with this code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, units = ?, description = ?, college_code = ?, program_code = ? WHERE course_id = ?");
    $stmt->bind_param('ssisssi', $course_code, $course_name, $units, $description, $college_val, $program_val, $course_id);
    $message = 'Course updated successfully.';
} else {
    $chkStmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ?");
    $chkStmt->bind_param('s', $course_code);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Course code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, units, description, college_code, program_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssisss', $course_code, $course_name, $units, $description, $college_val, $program_val);
    $message = 'Course added successfully.';
}

if ($stmt->execute()) {
    log_action($conn, $action === 'edit' ? 'course_updated' : 'course_created', "course_code={$course_code}, name={$course_name}");
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    error_log('manage_subjects.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
