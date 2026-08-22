<?php
header('Content-Type: application/json');
require_once '../includes/conn.php';
$result = $conn->query("SELECT * FROM college ORDER BY college_code ASC");
$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;
echo json_encode($data);
?>
