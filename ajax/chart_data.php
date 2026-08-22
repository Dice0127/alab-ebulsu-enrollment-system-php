<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';

$type = trim($_GET['type'] ?? '');
$college = isset($_GET['college']) && $_GET['college'] !== 'all' ? trim($_GET['college']) : null;
$program = isset($_GET['program']) && $_GET['program'] !== 'all' ? trim($_GET['program']) : null;

switch ($type) {

    // ── Status distribution ─────────────────────────────
    case 'by_status':
        $labels = ['Pending', 'Approved', 'Rejected'];
        $values = [];
        foreach ($labels as $s) {
            $query = "SELECT COUNT(*) AS c FROM enrollment e
                     LEFT JOIN program p ON e.program_code = p.program_code
                     LEFT JOIN college c ON p.college_code = c.college_code
                     WHERE e.status = ?";
            $types = 's';
            $params = [$s];
            if ($college) { $query .= " AND c.college_code = ?"; $types .= 's'; $params[] = $college; }
            if ($program) { $query .= " AND p.program_code = ?"; $types .= 's'; $params[] = $program; }

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $r = $stmt->get_result();
            $values[] = (int)$r->fetch_assoc()['c'];
        }
        echo json_encode(['labels' => $labels, 'values' => $values]);
        break;

    // ── By Program ──────────────────────────────────────
    case 'by_program':
        $query = "SELECT p.program_code, p.program_name, COUNT(e.enrollment_id) AS cnt
                 FROM program p
                 LEFT JOIN enrollment e ON p.program_code = e.program_code
                 LEFT JOIN college c ON p.college_code = c.college_code";

        $types = '';
        $params = [];
        if ($college) { $query .= " WHERE c.college_code = ?"; $types .= 's'; $params[] = $college; }

        $query .= " GROUP BY p.program_code, p.program_name ORDER BY cnt DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        $labels = []; $values = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['program_code'];
            $values[] = (int)$row['cnt'];
        }
        echo json_encode(['labels' => $labels, 'values' => $values]);
        break;

    // ── By Year Level ───────────────────────────────────
    case 'by_year':
        $years = ['1st Year','2nd Year','3rd Year','4th Year'];
        $values = [];
        foreach ($years as $y) {
            $query = "SELECT COUNT(*) AS c FROM enrollment e
                     LEFT JOIN program p ON e.program_code = p.program_code
                     LEFT JOIN college c ON p.college_code = c.college_code
                     WHERE e.year_level = ?";
            $types = 's';
            $params = [$y];
            if ($college) { $query .= " AND c.college_code = ?"; $types .= 's'; $params[] = $college; }
            if ($program) { $query .= " AND p.program_code = ?"; $types .= 's'; $params[] = $program; }

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $r = $stmt->get_result();
            $values[] = (int)$r->fetch_assoc()['c'];
        }
        echo json_encode(['labels' => $years, 'values' => $values]);
        break;

    // ── By Gender ───────────────────────────────────────
    case 'by_gender':
        $query = "SELECT gender, COUNT(*) AS cnt FROM enrollment e
                 LEFT JOIN program p ON e.program_code = p.program_code
                 LEFT JOIN college c ON p.college_code = c.college_code
                 WHERE 1";
        $types = '';
        $params = [];
        if ($college) { $query .= " AND c.college_code = ?"; $types .= 's'; $params[] = $college; }
        if ($program) { $query .= " AND p.program_code = ?"; $types .= 's'; $params[] = $program; }
        $query .= " GROUP BY gender ORDER BY cnt DESC";

        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        $labels = []; $values = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['gender'];
            $values[] = (int)$row['cnt'];
        }
        echo json_encode(['labels' => $labels, 'values' => $values]);
        break;

    // ── Semestral/Enrollment Trend ──────────────────────
    case 'by_trend':
        $query = "SELECT semester, COUNT(*) AS cnt FROM enrollment e
                 LEFT JOIN program p ON e.program_code = p.program_code
                 LEFT JOIN college c ON p.college_code = c.college_code
                 WHERE 1";
        $types = '';
        $params = [];
        if ($college) { $query .= " AND c.college_code = ?"; $types .= 's'; $params[] = $college; }
        if ($program) { $query .= " AND p.program_code = ?"; $types .= 's'; $params[] = $program; }
        $query .= " GROUP BY semester ORDER BY semester ASC";

        if (!empty($params)) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($query);
        }
        $labels = []; $values = [];
        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['semester'];
            $values[] = (int)$row['cnt'];
        }
        echo json_encode(['labels' => $labels, 'values' => $values]);
        break;

    // ── Summary table (no filters, no user input) ────────
    case 'summary':
        $query = "SELECT p.program_code, p.program_name, c.college_name,
                        COUNT(e.enrollment_id)                                   AS total,
                        SUM(e.status = 'Pending')                                AS pending,
                        SUM(e.status = 'Approved')                               AS approved,
                        SUM(e.status = 'Rejected')                               AS rejected
                 FROM program p
                 JOIN college c ON p.college_code = c.college_code
                 LEFT JOIN enrollment e ON p.program_code = e.program_code
                 GROUP BY p.program_code, p.program_name, c.college_name
                 ORDER BY total DESC";

        $result = $conn->query($query);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'program_code' => $row['program_code'],
                'program_name' => $row['program_name'],
                'college_name' => $row['college_name'],
                'total'        => (int)$row['total'],
                'pending'      => (int)$row['pending'],
                'approved'     => (int)$row['approved'],
                'rejected'     => (int)$row['rejected'],
            ];
        }
        echo json_encode($data);
        break;

    default:
        echo json_encode([]);
}
?>
