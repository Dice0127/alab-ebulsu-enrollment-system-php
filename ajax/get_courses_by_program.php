<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$program_code = isset($_GET['program_code']) ? trim($_GET['program_code']) : '';

$sql = "SELECT course_id, course_code, course_name, units, description, college_code, program_code FROM courses";
if ($program_code !== '') {
    $sql .= " WHERE program_code = ? ORDER BY course_code ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $program_code);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY course_code ASC";
    $result = $conn->query($sql);
}
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>
