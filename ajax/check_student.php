<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';

$sn   = trim($_GET['student_number'] ?? '');
$full = isset($_GET['full']) && $_GET['full'] == 1;

$stmt = $conn->prepare(
    "SELECT e.*, p.program_name FROM enrollment e
     JOIN program p ON e.program_code = p.program_code
     WHERE e.student_number = ?"
);
$stmt->bind_param('s', $sn);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['exists' => false]);
    exit;
}

$row = $res->fetch_assoc();

if ($full) {
    echo json_encode(['exists' => true, 'enrollment' => $row]);
} else {
    echo json_encode(['exists' => true]);
}
?>
