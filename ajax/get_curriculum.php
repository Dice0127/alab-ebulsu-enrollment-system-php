<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$program_code = trim($_GET['program_code'] ?? '');
$year_level = trim($_GET['year_level'] ?? '');

$where = [];
$types = '';
$params = [];
if ($program_code) { $where[] = "cu.program_code = ?"; $types .= 's'; $params[] = $program_code; }
if ($year_level)  { $where[] = "cu.year_level = ?"; $types .= 's'; $params[] = $year_level; }
$sql = "SELECT cu.curriculum_id, cu.program_code, cu.year_level, cu.semester, cu.is_required, cu.course_id,
               p.program_name, c.course_code, c.course_name, c.units
        FROM curriculum cu
        JOIN program p ON cu.program_code = p.program_code
        JOIN courses c ON cu.course_id = c.course_id";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY cu.program_code ASC, cu.year_level ASC, cu.semester ASC, c.course_code ASC';

$data = [];
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
