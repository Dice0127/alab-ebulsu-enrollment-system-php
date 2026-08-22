<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$data = [];
$result = $conn->query("SELECT course_id, course_code, course_name, units, description, college_code, program_code FROM courses ORDER BY course_code ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>