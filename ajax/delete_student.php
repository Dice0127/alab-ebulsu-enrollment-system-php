<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
require_once '../includes/section_slots.php';
csrf_verify();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
    exit;
}

$conn->begin_transaction();

// Capture identifying info and slot-holding state before the row is
// gone, so the audit trail still means something and so we know
// whether this enrollment was still occupying a section seat.
$label = "enrollment_id={$id}";
$lookup = $conn->prepare("SELECT student_number, first_name, last_name, status, section_id FROM enrollment WHERE enrollment_id = ? FOR UPDATE");
$lookup->bind_param('i', $id);
$lookup->execute();
$row = $lookup->get_result()->fetch_assoc();
if ($row) {
    $label = "student_number={$row['student_number']}, name={$row['first_name']} {$row['last_name']}";
}

$stmt = $conn->prepare("DELETE FROM enrollment WHERE enrollment_id = ?");
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    $conn->rollback();
    error_log('delete_student.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    exit;
}

// If this enrollment was still Pending/Approved, it was occupying a
// section seat — free it up now that the record is gone.
if ($row && enrollment_holds_slot($row['status']) && (int)$row['section_id'] > 0) {
    release_section_slot($conn, (int)$row['section_id']);
}

$conn->commit();

log_action($conn, 'student_deleted', $label);
echo json_encode(['success' => true, 'message' => 'Student deleted successfully!']);
?>
