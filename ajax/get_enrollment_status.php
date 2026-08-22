<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/conn.php';

// Check if student is logged in or if checking by student number
$mode = $_GET['mode'] ?? 'current'; // 'current' (logged in) or 'check' (by student number)

if ($mode === 'check') {
    // Public check by student number
    $studentNumber = trim($_GET['student_number'] ?? '');
    
    if (!$studentNumber) {
        echo json_encode(['success' => false, 'error' => 'Student number required']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT e.enrollment_id, e.student_number, e.status, e.semester, e.school_year, 
                     e.year_level, e.first_name, e.last_name, p.program_name, c.college_name
              FROM enrollment e
              JOIN program p ON e.program_code = p.program_code
              JOIN college c ON p.college_code = c.college_code
              WHERE e.student_number = ?
              ORDER BY e.enrollment_id DESC
              LIMIT 1");
    $stmt->bind_param('s', $studentNumber);
} else {
    // Logged in student - get current status
    if (!isset($_SESSION['student_account_id'])) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    
    $accountId = (int)$_SESSION['student_account_id'];
    
    $stmt = $conn->prepare("SELECT e.enrollment_id, e.student_number, e.status, e.semester, e.school_year,
                     e.year_level, e.first_name, e.last_name, e.created_at, p.program_name, c.college_name,
                     s.section_name
              FROM enrollment e
              JOIN program p ON e.program_code = p.program_code
              JOIN college c ON p.college_code = c.college_code
              LEFT JOIN sections s ON e.section_id = s.section_id
              WHERE e.account_id = ?
              ORDER BY e.enrollment_id DESC
              LIMIT 1");
    $stmt->bind_param('i', $accountId);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'No enrollment found',
        'status' => 'Not Enrolled'
    ]);
    exit;
}

$enrollment = $result->fetch_assoc();

// Prepare response
$response = [
    'success' => true,
    'status' => $enrollment['status'],
    'studentNumber' => $enrollment['student_number'],
    'studentName' => htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']),
    'program' => htmlspecialchars($enrollment['program_name']),
    'college' => htmlspecialchars($enrollment['college_name']),
    'yearLevel' => htmlspecialchars($enrollment['year_level'] ?? ''),
    'semester' => htmlspecialchars($enrollment['semester']),
    'schoolYear' => htmlspecialchars($enrollment['school_year']),
    'fullData' => $enrollment
];

// Add section info if available
if (!empty($enrollment['section_name'])) {
    $response['section'] = htmlspecialchars($enrollment['section_name']);
}

// Add enrollment date
if (!empty($enrollment['created_at'])) {
    $response['enrollmentDate'] = date('M d, Y', strtotime($enrollment['created_at']));
}

// Determine status message
$statusMessages = [
    'Pending' => 'Your enrollment is awaiting approval from the admin.',
    'Approved' => 'Your enrollment has been approved!',
    'Rejected' => 'Your enrollment was rejected. Please contact the admin for details.'
];

$response['statusMessage'] = $statusMessages[$enrollment['status']] ?? 'Unknown status';

echo json_encode($response);
?>
