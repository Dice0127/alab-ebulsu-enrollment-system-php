<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';

$college_code = isset($_GET['college_code']) ? trim($_GET['college_code']) : '';

$sql = "SELECT p.*, c.college_name
        FROM program p
        JOIN college c ON p.college_code = c.college_code";
if ($college_code !== '') {
    $sql .= " WHERE p.college_code = ?";
    $sql .= " ORDER BY p.program_code ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $college_code);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY p.program_code ASC";
    $result = $conn->query($sql);
}
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>
