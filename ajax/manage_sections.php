<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$action       = val($_POST['action'] ?? 'save');
$section_id   = (int)($_POST['section_id'] ?? 0);
$section_code = val($_POST['section_code'] ?? '');
$section_name = val($_POST['section_name'] ?? '');
$college_code = val($_POST['college_code'] ?? '');
$program_code = val($_POST['program_code'] ?? '');
$year_level   = val($_POST['year_level'] ?? '');
$max_capacity = (int)($_POST['max_capacity'] ?? 0);
$status       = val($_POST['status'] ?? 'Open');

if ($action === 'delete') {
    if ($section_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid section specified.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM sections WHERE section_id = ?");
    $stmt->bind_param('i', $section_id);
    if ($stmt->execute()) {
        log_action($conn, 'section_deleted', "section_id={$section_id}");
        echo json_encode(['success' => true, 'message' => 'Section deleted successfully.']);
    } else if ($conn->errno === 1451) {
        // FK constraint violation (fk_enrollment_section, ON DELETE RESTRICT):
        // this section still has enrollment records pointing to it.
        echo json_encode(['success' => false, 'message' => 'This section still has enrolled students and cannot be deleted. Move or remove those enrollments first.']);
    } else {
        error_log('manage_sections.php delete error: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
    exit;
}

if (!$section_code || !$section_name || !$college_code || !$program_code || !$year_level || $max_capacity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Section code, name, college, program, year level, and capacity are required.']);
    exit;
}

if ($action === 'edit') {
    if ($section_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid section specified.']);
        exit;
    }
    $chkStmt = $conn->prepare("SELECT section_id FROM sections WHERE section_code = ? AND section_id != ?");
    $chkStmt->bind_param('si', $section_code, $section_id);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Another section with this code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE sections SET section_code = ?, section_name = ?, college_code = ?, program_code = ?, year_level = ?, max_capacity = ?, status = ? WHERE section_id = ?");
    $stmt->bind_param('sssssisi', $section_code, $section_name, $college_code, $program_code, $year_level, $max_capacity, $status, $section_id);
    $message = 'Section updated successfully.';
} else {
    $chkStmt = $conn->prepare("SELECT section_id FROM sections WHERE section_code = ?");
    $chkStmt->bind_param('s', $section_code);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Section code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO sections (section_code, section_name, college_code, program_code, year_level, max_capacity, current_enrolled, status) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->bind_param('sssssis', $section_code, $section_name, $college_code, $program_code, $year_level, $max_capacity, $status);
    $message = 'Section added successfully.';
}

if ($stmt->execute()) {
    log_action($conn, $action === 'edit' ? 'section_updated' : 'section_created', "section_code={$section_code}, name={$section_name}");
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    error_log('manage_sections.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
