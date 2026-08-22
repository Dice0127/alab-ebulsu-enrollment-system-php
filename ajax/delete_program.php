<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

$program_code = trim($_POST['program_code'] ?? '');
if (!$program_code) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM program WHERE program_code = ?");
$stmt->bind_param('s', $program_code);
if ($stmt->execute()) {
    log_action($conn, 'program_deleted', "program_code={$program_code}");
    echo json_encode(['success' => true, 'message' => 'Program deleted successfully!']);
} else if ($conn->errno === 1451) {
    // FK constraint violation (fk_curriculum_program / fk_sections_program /
    // fk_enrollment_program, all ON DELETE RESTRICT): this program still has
    // curriculum entries, sections, or enrollment records tied to it.
    echo json_encode(['success' => false, 'message' => 'This program still has curriculum entries, sections, or enrolled students and cannot be deleted. Remove those first.']);
} else {
    error_log('delete_program.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
