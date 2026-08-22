<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';

$search  = isset($_GET['search'])  ? trim($_GET['search'])  : '';
$program = isset($_GET['program']) ? trim($_GET['program']) : '';
$year    = isset($_GET['year'])    ? trim($_GET['year'])    : '';
$status  = isset($_GET['status'])  ? trim($_GET['status'])  : '';

// Pagination — capped so a caller can't request an absurd page size.
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? 20);
$perPage = max(1, min($perPage, 100));
$offset  = ($page - 1) * $perPage;

$where = " WHERE 1=1";
$types = '';
$params = [];

if ($search !== '') {
    $where .= " AND (e.first_name LIKE ?
               OR e.last_name  LIKE ?
               OR e.student_number LIKE ?
               OR CONCAT(e.first_name,' ',e.last_name) LIKE ?)";
    $likeSearch = '%' . $search . '%';
    $types .= 'ssss';
    array_push($params, $likeSearch, $likeSearch, $likeSearch, $likeSearch);
}
if ($program !== '') { $where .= " AND e.program_code = ?"; $types .= 's'; $params[] = $program; }
if ($year    !== '') { $where .= " AND e.year_level   = ?"; $types .= 's'; $params[] = $year; }
if ($status  !== '') { $where .= " AND e.status       = ?"; $types .= 's'; $params[] = $status; }

// Total count for the pager, using the same filters.
$countSql = "SELECT COUNT(*) AS total
             FROM enrollment e
             JOIN program p ON e.program_code = p.program_code
             JOIN college c ON p.college_code = c.college_code
             LEFT JOIN sections s ON e.section_id = s.section_id" . $where;
if (!empty($params)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = (int)$conn->query($countSql)->fetch_assoc()['total'];
}

$sql = "SELECT e.*, p.program_name, c.college_name, s.section_code, s.section_name
        FROM enrollment e
        JOIN program p ON e.program_code = p.program_code
        JOIN college c ON p.college_code = c.college_code
        LEFT JOIN sections s ON e.section_id = s.section_id"
        . $where .
        " ORDER BY e.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$limitTypes = $types . 'ii';
$limitParams = array_merge($params, [$perPage, $offset]);
$stmt->bind_param($limitTypes, ...$limitParams);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) $data[] = $row;

echo json_encode([
    'data'        => $data,
    'total'       => $total,
    'page'        => $page,
    'per_page'    => $perPage,
    'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1,
]);
?>
