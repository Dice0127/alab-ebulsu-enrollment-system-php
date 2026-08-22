<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';

$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$user   = isset($_GET['user'])   ? trim($_GET['user'])   : '';

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? 25);
$perPage = max(1, min($perPage, 100));
$offset  = ($page - 1) * $perPage;

$where = " WHERE 1=1";
$types = '';
$params = [];

if ($action !== '') { $where .= " AND action = ?"; $types .= 's'; $params[] = $action; }
if ($user   !== '') { $where .= " AND admin_username LIKE ?"; $types .= 's'; $params[] = '%' . $user . '%'; }

$countSql = "SELECT COUNT(*) AS total FROM audit_log" . $where;
if (!empty($params)) {
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int)$countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = (int)$conn->query($countSql)->fetch_assoc()['total'];
}

$sql = "SELECT log_id, admin_id, admin_username, action, details, ip_address, created_at
        FROM audit_log" . $where . "
        ORDER BY created_at DESC
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
