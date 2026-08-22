<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

$college_code = trim($_POST['college_code'] ?? '');
if (!$college_code) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM college WHERE college_code = ?");
$stmt->bind_param('s', $college_code);
if ($stmt->execute()) {
    log_action($conn, 'college_deleted', "college_code={$college_code}");
    echo json_encode(['success' => true, 'message' => 'College deleted successfully!']);
} else if ($conn->errno === 1451) {
    // FK constraint violation: this college still has programs (which, thanks
    // to fk_curriculum_program / fk_sections_program / fk_enrollment_program
    // being RESTRICT, can only exist here if they still have their own
    // curriculum/sections/enrollment data). Deleting the college would
    // otherwise cascade into deleting those programs.
    echo json_encode(['success' => false, 'message' => 'This college still has programs (with curriculum, sections, or enrolled students) and cannot be deleted. Remove those first.']);
} else {
    error_log('delete_college.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
