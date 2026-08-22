<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$program_code = trim($_GET['program_code'] ?? '');
$year_level = trim($_GET['year_level'] ?? '');

$data = [];
$sql = "SELECT s.section_id, s.section_code, s.section_name, s.college_code, s.program_code, s.year_level, s.max_capacity, s.current_enrolled, s.status,
               p.program_name,
               (s.max_capacity - s.current_enrolled) AS available_slots,
               CASE
                   WHEN s.status = 'Closed' THEN 'Closed'
                   WHEN s.current_enrolled >= s.max_capacity THEN 'Full'
                   ELSE 'Open'
               END AS computed_status
        FROM sections s
        LEFT JOIN program p ON s.program_code = p.program_code";

$where = [];
$types = '';
$params = [];
if ($program_code) { $where[] = "s.program_code = ?"; $types .= 's'; $params[] = $program_code; }
if ($year_level)  { $where[] = "s.year_level = ?"; $types .= 's'; $params[] = $year_level; }

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.section_name ASC';

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
