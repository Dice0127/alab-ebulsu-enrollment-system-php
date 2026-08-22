<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$is_edit      = (int)($_POST['is_edit'] ?? 0);
$program_code = val($_POST['program_code']);
$program_name = val($_POST['program_name']);
$college_code = val($_POST['college_code']);
$original_key = val($_POST['original_key'] ?? '');

if ($is_edit) {
    $stmt = $conn->prepare("UPDATE program
            SET program_code = ?,
                program_name = ?,
                college_code = ?
            WHERE program_code = ?");
    $stmt->bind_param('ssss', $program_code, $program_name, $college_code, $original_key);
    $msg = 'Program updated successfully!';
} else {
    $chkStmt = $conn->prepare("SELECT program_code FROM program WHERE program_code = ?");
    $chkStmt->bind_param('s', $program_code);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Program code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO program (program_code, program_name, college_code)
            VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $program_code, $program_name, $college_code);
    $msg = 'Program added successfully!';
}

if ($stmt->execute()) {
    log_action($conn, $is_edit ? 'program_updated' : 'program_created', "program_code={$program_code}, name={$program_name}");
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    error_log('save_program.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
