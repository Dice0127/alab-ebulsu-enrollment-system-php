<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$program_code = trim($_GET['program_code'] ?? '');
$year_level = trim($_GET['year_level'] ?? '');
$semester   = trim($_GET['semester'] ?? '');

$data = [];
if ($program_code && $year_level && $semester) {
    $stmt = $conn->prepare("SELECT c.course_id, c.course_code, c.course_name, c.units, cu.semester, cu.is_required
            FROM curriculum cu
            JOIN courses c ON cu.course_id = c.course_id
            WHERE cu.program_code = ?
              AND cu.year_level = ?
              AND cu.semester = ?
            ORDER BY c.course_name ASC");
    $stmt->bind_param('sss', $program_code, $year_level, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

echo json_encode($data);
