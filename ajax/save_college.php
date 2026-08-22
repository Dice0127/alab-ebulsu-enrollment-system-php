<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$is_edit      = (int)($_POST['is_edit'] ?? 0);
$college_code = val($_POST['college_code']);
$college_name = val($_POST['college_name']);
$original_key = val($_POST['original_key'] ?? '');

if ($is_edit) {
    $stmt = $conn->prepare("UPDATE college
            SET college_code = ?, college_name = ?
            WHERE college_code = ?");
    $stmt->bind_param('sss', $college_code, $college_name, $original_key);
    $msg = 'College updated successfully!';
} else {
    $chkStmt = $conn->prepare("SELECT college_code FROM college WHERE college_code = ?");
    $chkStmt->bind_param('s', $college_code);
    $chkStmt->execute();
    if ($chkStmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'College code already exists.']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO college (college_code, college_name) VALUES (?, ?)");
    $stmt->bind_param('ss', $college_code, $college_name);
    $msg = 'College added successfully!';
}

if ($stmt->execute()) {
    log_action($conn, $is_edit ? 'college_updated' : 'college_created', "college_code={$college_code}, name={$college_name}");
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    error_log('save_college.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
