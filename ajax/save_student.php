<?php
header('Content-Type: application/json');
require_once '../includes/admin_guard.php';
require_once '../includes/conn.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';
csrf_verify();

function val($v) { return trim($v ?? ''); }

$is_edit       = (int)($_POST['is_edit']       ?? 0);
$enrollment_id = (int)($_POST['enrollment_id'] ?? 0);
$student_number = val($_POST['student_number']);
$first_name     = val($_POST['first_name']);
$middle_name    = val($_POST['middle_name']);
$last_name      = val($_POST['last_name']);
$gender         = val($_POST['gender']);
$birthday       = val($_POST['birthday']);
$email          = val($_POST['email']);
$contact_number = val($_POST['contact_number']);
$address        = val($_POST['address']);
$program_code   = val($_POST['program_code']);
$year_level     = val($_POST['year_level']);
$semester       = val($_POST['semester']);
$school_year    = val($_POST['school_year']);
$status         = val($_POST['status']  ?? 'Pending');
$remarks        = val($_POST['remarks']);

if ($is_edit) {
    // Check duplicate student_number only if it changed
    $chk = $conn->prepare("SELECT enrollment_id FROM enrollment WHERE student_number = ? AND enrollment_id != ?");
    $chk->bind_param('si', $student_number, $enrollment_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Student number already exists.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE enrollment SET
                student_number  = ?,
                first_name      = ?,
                middle_name     = ?,
                last_name       = ?,
                gender          = ?,
                birthday        = ?,
                email           = ?,
                contact_number  = ?,
                address         = ?,
                program_code    = ?,
                year_level      = ?,
                semester        = ?,
                school_year     = ?,
                status          = ?,
                remarks         = ?
            WHERE enrollment_id = ?");
    $stmt->bind_param(
        'sssssssssssssssi',
        $student_number, $first_name, $middle_name, $last_name, $gender,
        $birthday, $email, $contact_number, $address, $program_code,
        $year_level, $semester, $school_year, $status, $remarks,
        $enrollment_id
    );
    $msg = 'Student record updated successfully!';
} else {
    // Check duplicate
    $chk = $conn->prepare("SELECT enrollment_id FROM enrollment WHERE student_number = ?");
    $chk->bind_param('s', $student_number);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Student number already exists.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO enrollment
                (student_number, first_name, middle_name, last_name, gender, birthday,
                 email, contact_number, address, program_code, year_level, semester,
                 school_year, status, remarks)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        'sssssssssssssss',
        $student_number, $first_name, $middle_name, $last_name, $gender,
        $birthday, $email, $contact_number, $address, $program_code,
        $year_level, $semester, $school_year, $status, $remarks
    );
    $msg = 'Student added successfully!';
}

if ($stmt->execute()) {
    log_action($conn, $is_edit ? 'student_updated' : 'student_created',
        "student_number={$student_number}, name={$first_name} {$last_name}");
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    error_log('save_student.php DB error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
?>
