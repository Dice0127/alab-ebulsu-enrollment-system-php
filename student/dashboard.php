<?php
require_once 'auth_check.php';
require_once '../includes/conn.php';

$accountId = (int)$_SESSION['student_account_id'];

// Get student's enrollment data
$stmt = $conn->prepare(
    "SELECT e.*, p.program_name, c.college_name, s.section_code, s.section_name
     FROM enrollment e
     JOIN program p ON e.program_code = p.program_code
     JOIN college c ON p.college_code  = c.college_code
     LEFT JOIN sections s ON e.section_id = s.section_id
     WHERE e.account_id = ?
     ORDER BY e.enrollment_id DESC LIMIT 1"
);
$stmt->bind_param('i', $accountId);
$stmt->execute();
$enrollResult = $stmt->get_result();
$enrollment = $enrollResult->num_rows > 0 ? $enrollResult->fetch_assoc() : null;

// Get enrollment courses
$enrollmentCourses = [];
if ($enrollment) {
    $enrollmentId = (int)$enrollment['enrollment_id'];
    $coursesStmt = $conn->prepare(
        "SELECT c.course_id, c.course_code, c.course_name, c.units
         FROM enrollment_courses ec
         JOIN courses c ON ec.course_id = c.course_id
         WHERE ec.enrollment_id = ?
         ORDER BY c.course_code ASC"
    );
    $coursesStmt->bind_param('i', $enrollmentId);
    $coursesStmt->execute();
    $coursesResult = $coursesStmt->get_result();
    while ($row = $coursesResult->fetch_assoc()) {
        $enrollmentCourses[] = $row;
    }
}

// Calculate totals for stat cards
$totalCourses = count($enrollmentCourses);
$totalUnits = 0;
foreach ($enrollmentCourses as $course) {
    $totalUnits += (int)$course['units'];
}
$enrollmentStatus = $enrollment ? $enrollment['status'] : 'No Enrollment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard \u2014 Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .stu-dash { max-width:1200px; margin:0 auto; }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="topbar">
    <div class="sys-title"><img src="../Bulsu_Logo.png" alt="BulSU Logo" style="height:32px; width:32px; margin-right:10px; vertical-align:middle;"> Alab E-BulSU — Student Portal</div>
    <div class="topbar-right">
        <span><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
        <a href="profile.php">My Profile</a>
        <a href="enroll.php">Enroll</a>
        <a href="status.php">My Status</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="main-content-full">
<div class="stu-dash">

    <!-- Page Header -->
    <div class="page-header">
        <h2> My Dashboard</h2>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-val"><?php echo $totalCourses; ?></div>
            <div class="stat-label">Total Courses</div>
        </div>
        <div class="stat-card green">
            <div class="stat-val"><?php echo $totalUnits; ?></div>
            <div class="stat-label">Total Units</div>
        </div>
        <div class="stat-card <?php echo $enrollment ? ($enrollment['status'] === 'Approved' ? 'green' : ($enrollment['status'] === 'Pending' ? 'orange' : 'red')) : 'grey'; ?>">
            <div class="stat-val"><?php echo $enrollmentStatus; ?></div>
            <div class="stat-label">Enrollment Status</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-val"><?php echo $enrollment ? htmlspecialchars($enrollment['program_code']) : '—'; ?></div>
            <div class="stat-label">Program</div>
        </div>
    </div>
    <?php if ($enrollment): ?>
    <!-- Enrollment Details Card -->
    <div class="card">
        <h3> Your Enrollment Details</h3>

        <!-- Student Info Header -->
        <div style="margin-bottom:20px;">
            <div style="font-size:18px; font-weight:bold; color:#fff; margin-bottom:4px;">
                <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
            </div>
            <div style="font-size:13px; color:rgba(255,255,255,0.6);">
                Student No: <strong style="color:#a29bfe; font-size:15px; letter-spacing:1px;"><?php echo htmlspecialchars($enrollment['student_number']); ?></strong>
            </div>
        </div>

        <!-- Personal Information -->
        <div style="margin-bottom:22px;">
            <h4 style="font-size:14px; margin-bottom:12px; color:#a29bfe;"> Personal Information</h4>
            <div class="info-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px;">
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Gender</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['gender']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Birthday</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['birthday']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Email</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['email']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Contact</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['contact_number']); ?></div>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div style="margin-bottom:22px;">
            <h4 style="font-size:14px; margin-bottom:12px; color:#a29bfe;"> Academic Information</h4>
            <div class="info-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px;">
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Program</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['program_name']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">College</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['college_name']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Year Level</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['year_level']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Section</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['section_code'] ? $enrollment['section_code'] . ' — ' . $enrollment['section_name'] : '—'); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">Semester</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['semester']); ?></div>
                </div>
                <div style="background:rgba(10,15,50,0.6); padding:12px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                    <div style="font-size:11px; text-transform:uppercase; font-weight:bold; color:rgba(255,255,255,0.5); margin-bottom:4px;">School Year</div>
                    <div style="font-size:14px; color:#fff;"><?php echo htmlspecialchars($enrollment['school_year']); ?></div>
                </div>
            </div>
        </div>

        <!-- Enrolled Courses -->
        <?php if (!empty($enrollmentCourses)): ?>
        <div style="margin-bottom:22px;">
            <h4 style="font-size:14px; margin-bottom:12px; color:#a29bfe;"> Enrolled Courses (<?php echo count($enrollmentCourses); ?>)</h4>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Course Code</th><th>Course Name</th><th>Units</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($enrollmentCourses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo $course['units']; ?> units</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status Info -->
        <div style="background:rgba(162,155,254,0.12); border-left:4px solid rgba(162,155,254,0.3); border-radius:4px; padding:14px 16px;">
            <div style="font-size:12px; color:rgba(162,155,254,0.9); margin-bottom:8px;"><strong>Status Information</strong></div>
            <div style="font-size:13px; color:#a29bfe; margin-bottom:4px;">Status: <strong><?php echo htmlspecialchars($enrollment['status']); ?></strong></div>
            <div style="font-size:13px; color:rgba(255,255,255,0.6);">Submitted: <?php echo date('M d, Y h:i A', strtotime($enrollment['created_at'])); ?></div>
            <?php if ($enrollment['remarks']): ?>
            <div style="font-size:13px; color:#ff7675; margin-top:8px;"><strong>Remarks:</strong> <?php echo htmlspecialchars($enrollment['remarks']); ?></div>
            <?php endif; ?>
        </div>
    </div>


    <?php endif; ?>

</div>
</div>
</body>
</html>
