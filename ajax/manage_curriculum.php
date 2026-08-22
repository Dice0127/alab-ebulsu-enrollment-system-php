<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$action        = val($_POST['action'] ?? 'save');
$curriculum_id = (int)($_POST['curriculum_id'] ?? 0);
$program_code  = val($_POST['program_code'] ?? '');
$year_level    = val($_POST['year_level'] ?? '');
$course_id     = (int)($_POST['course_id'] ?? 0);
$semester      = (int)($_POST['semester'] ?? 1);
$is_required   = isset($_POST['is_required']) ? 1 : 0;
$course_ids    = isset($_POST['course_ids']) ? json_decode($_POST['course_ids'], true) : [];
if (!is_array($course_ids)) {
    $course_ids = [];
}

if ($action === 'delete') {
    if ($curriculum_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid curriculum entry specified.']);
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM curriculum WHERE curriculum_id = ?");
    $stmt->bind_param('i', $curriculum_id);
    if ($stmt->execute()) {
        log_action($conn, 'curriculum_deleted', "curriculum_id={$curriculum_id}");
        echo json_encode(['success' => true, 'message' => 'Curriculum entry deleted successfully.']);
    } else {
        error_log('manage_curriculum.php delete error: ' . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    }
    exit;
}

if ($action === 'save_multi') {
    if (!$program_code || !$year_level || $semester <= 0 || empty($course_ids)) {
        echo json_encode(['success' => false, 'message' => 'Program, year level, semester, and courses are required.']);
        exit;
    }

    $chkStmt = $conn->prepare("SELECT curriculum_id FROM curriculum WHERE program_code = ? AND year_level = ? AND semester = ? AND course_id = ?");
    $insStmt = $conn->prepare("INSERT INTO curriculum (program_code, year_level, course_id, semester, is_required) VALUES (?, ?, ?, ?, 1)");

    $inserted = 0;
    foreach ($course_ids as $cid) {
        $cid = (int)$cid;
        if ($cid <= 0) {
            continue;
        }
        $chkStmt->bind_param('ssii', $program_code, $year_level, $semester, $cid);
        $chkStmt->execute();
        $chkRes = $chkStmt->get_result();
        if ($chkRes && $chkRes->num_rows > 0) {
            continue;
        }
        $insStmt->bind_param('ssii', $program_code, $year_level, $cid, $semester);
        if ($insStmt->execute()) {
            $inserted++;
        }
    }

    if ($inserted > 0) {
        log_action($conn, 'curriculum_bulk_added', "program_code={$program_code}, year_level={$year_level}, semester={$semester}, count={$inserted}");
        echo json_encode(['success' => true, 'message' => $inserted . ' course' . ($inserted === 1 ? '' : 's') . ' added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No courses were added. They may already be assigned.']);
    }
    exit;
}

if (!$program_code || !$year_level || $course_id <= 0 || $semester <= 0) {
    echo json_encode(['success' => false, 'message' => 'Program, year level, semester, and course are required.']);
    exit;
}

if ($action === 'edit') {
    if ($curriculum_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid curriculum entry specified.']);
        exit;
    }
    $chkStmt = $conn->prepare("SELECT curriculum_id FROM curriculum WHERE program_code = ? AND year_level = ? AND semester = ? AND course_id = ? AND curriculum_id != ?");
    $chkStmt->bind_param('ssiii', $program_code, $year_level, $semester, $course_id, $curriculum_id);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This course is already assigned to the same program/year/semester.']);
        exit;
    }
    $stmt = $conn->prepare("UPDATE curriculum SET program_code = ?, year_level = ?, course_id = ?, semester = ?, is_required = ? WHERE curriculum_id = ?");
    $stmt->bind_param('ssiiii', $program_code, $year_level, $course_id, $semester, $is_required, $curriculum_id);
    $message = 'Curriculum entry updated successfully.';
} else {
    $chkStmt = $conn->prepare("SELECT curriculum_id FROM curriculum WHERE program_code = ? AND year_level = ? AND semester = ? AND course_id = ?");
    $chkStmt->bind_param('ssii', $program_code, $year_level, $semester, $course_id);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This course is already assigned to the same program/year/semester.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO curriculum (program_code, year_level, course_id, semester, is_required) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiii', $program_code, $year_level, $course_id, $semester, $is_required);
    $message = 'Curriculum entry added successfully.';
}

if ($stmt->execute()) {
    log_action($conn, $action === 'edit' ? 'curriculum_updated' : 'curriculum_created', "program_code={$program_code}, year_level={$year_level}, semester={$semester}, course_id={$course_id}");
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    error_log('manage_curriculum.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
