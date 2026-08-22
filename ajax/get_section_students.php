<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';

$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;

if (!$section_id) {
    echo json_encode(['error' => 'Section ID required']);
    exit;
}

$sql = "SELECT e.enrollment_id, e.student_number, e.first_name, e.middle_name, e.last_name,
               e.year_level, e.semester, e.school_year, e.status, p.program_name
        FROM enrollment e
        JOIN program p ON e.program_code = p.program_code
        WHERE e.section_id = ?
        ORDER BY e.last_name ASC, e.first_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $section_id);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
