<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
require_once '../includes/section_slots.php';
csrf_verify();

$id      = (int)($_POST['id'] ?? 0);
$status  = trim($_POST['status']  ?? '');
$remarks = trim($_POST['remarks'] ?? '');

if ($id <= 0 || !in_array($status, ['Pending', 'Approved', 'Rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$conn->begin_transaction();

// Lock the enrollment row so we know its current status/section
// before deciding whether a slot needs to be released or reserved.
$curStmt = $conn->prepare("SELECT status, section_id FROM enrollment WHERE enrollment_id = ? FOR UPDATE");
$curStmt->bind_param('i', $id);
$curStmt->execute();
$curRes = $curStmt->get_result();
if (!$curRes || $curRes->num_rows === 0) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Enrollment not found.']);
    exit;
}
$current = $curRes->fetch_assoc();
$oldStatus = $current['status'];
$sectionId = (int)($current['section_id'] ?? 0);

$wasHeld  = enrollment_holds_slot($oldStatus);
$willHold = enrollment_holds_slot($status);

// Moving from a non-slot status (Rejected) back into Pending/Approved:
// re-check capacity before allowing it, same as a fresh submission would.
if (!$wasHeld && $willHold && $sectionId > 0) {
    if (!reserve_section_slot($conn, $sectionId)) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Cannot reinstate this enrollment — the section is now full or closed.']);
        exit;
    }
}

$stmt = $conn->prepare("UPDATE enrollment
        SET status = ?, remarks = ?
        WHERE enrollment_id = ?");
$stmt->bind_param('ssi', $status, $remarks, $id);

if (!$stmt->execute()) {
    $conn->rollback();
    error_log('update_enrollment_status.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    exit;
}

// Moving OUT of a slot-holding status (e.g. rejecting a Pending/Approved
// enrollment) frees up the section seat for someone else.
if ($wasHeld && !$willHold && $sectionId > 0) {
    release_section_slot($conn, $sectionId);
}

$conn->commit();

log_action($conn, 'enrollment_status_changed', "enrollment_id={$id}, status={$status}" . ($remarks ? ", remarks={$remarks}" : ''));
echo json_encode(['success' => true, 'message' => 'Enrollment status updated to ' . $status . '.']);
?>
