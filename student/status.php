<?php
require_once 'auth_check.php';
require_once '../includes/conn.php';

$accountId = (int)$_SESSION['student_account_id'];

// Get all enrollments for this student account
$stmt = $conn->prepare(
    "SELECT e.*, p.program_name, c.college_name
     FROM enrollment e
     JOIN program p ON e.program_code = p.program_code
     JOIN college c ON p.college_code  = c.college_code
     WHERE e.account_id = ?
     ORDER BY e.enrollment_id DESC"
);
$stmt->bind_param('i', $accountId);
$stmt->execute();
$result = $stmt->get_result();
$enrollments = [];
while ($row = $result->fetch_assoc()) $enrollments[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Status — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .status-wrap { max-width:760px; margin:0 auto; }
        .enroll-record { background: rgba(10,15,50,0.8); border-radius:10px; padding:24px 28px; box-shadow:0 8px 32px rgba(0,0,0,0.3); margin-bottom:18px; border-left:5px solid #ccc; border: 1px solid rgba(255,255,255,0.12); }
        .enroll-record.Pending  { border-color: #f39c12; }
        .enroll-record.Approved { border-color: #27ae60; }
        .enroll-record.Rejected { border-color: #c0392b; }
        .record-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
        .record-sn { font-size:20px; font-weight:bold; color:#a29bfe; }
        .record-grid { display:grid; grid-template-columns:150px 1fr; gap:6px 10px; font-size:13px; }
        .record-grid .rl { font-weight:bold; color: rgba(255,255,255,0.55); font-size:12px; text-transform:uppercase; }
        .sn-box { background: rgba(0,206,201,0.15); border:2px solid rgba(0,206,201,0.4); border-radius:8px; padding:12px 18px; margin-bottom:14px; display:flex; align-items:center; gap:12px; }
        .sn-box .sn-label { font-size:12px; color:rgba(255,255,255,0.45); }
        .sn-box .sn-val   { font-size:22px; font-weight:bold; color:#a29bfe; letter-spacing:1px; }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>


<div class="topbar">
    <div class="sys-title"><img src="../Bulsu_Logo.png" alt="BulSU Logo" style="height:32px; width:32px; margin-right:10px; vertical-align:middle;"> Alab E-BulSU — My Enrollment Status</div>
    <div class="topbar-right">
        <span><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
        <a href="profile.php">My Profile</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="main-content-full">
<div class="status-wrap">

    <div style="margin-bottom:20px;">
        <h2 style="font-size:20px; color:#fff;"> My Enrollment Records</h2>
        <p style="font-size:13px; color:rgba(255,255,255,0.5); margin-top:4px;">
            Logged in as: <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong>
            &nbsp;|&nbsp; <?php echo htmlspecialchars($_SESSION['student_email']); ?>
        </p>
    </div>

    <?php if (empty($enrollments)): ?>

    <div class="card" style="text-align:center; padding:40px; background:rgba(255,255,255,0.06); border-radius:16px; border:1px solid rgba(255,255,255,0.12);">
        <div style="font-size:48px; margin-bottom:14px;"></div>
        <p style="font-size:15px; font-weight:bold; margin-bottom:8px;">No Enrollment Found</p>
        <p style="font-size:13px; color:rgba(255,255,255,0.5); margin-bottom:20px;">You have not submitted any enrollment yet.</p>
        <a href="enroll.php" class="btn btn-success btn-lg">Enroll Now</a>
    </div>

    <?php else: ?>

    <?php foreach ($enrollments as $e):
        $icons = ['Pending'=>'','Approved'=>'','Rejected'=>''];
        $bc    = ['Pending'=>'badge-pending','Approved'=>'badge-approved','Rejected'=>'badge-rejected'];
        $icon  = $icons[$e['status']] ?? '';
        $badge = $bc[$e['status']] ?? '';
    ?>

    <div class="enroll-record <?php echo htmlspecialchars($e['status']); ?>">
        <div class="record-header">
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="font-size:28px;"><?php echo $icon; ?></span>
                <div>
                    <div class="record-sn"><?php echo htmlspecialchars($e['student_number']); ?></div>
                    <div style="font-size:12px; color:rgba(255,255,255,0.5);">Student Number</div>
                </div>
            </div>
            <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($e['status']); ?></span>
        </div>

        <!-- Student Number Highlight Box -->
        <div class="sn-box">
            <div></div>
            <div>
                <div class="sn-label">Your Assigned Student Number</div>
                <div class="sn-val"><?php echo htmlspecialchars($e['student_number']); ?></div>
            </div>
        </div>

        <div class="record-grid">
            <span class="rl">Full Name</span>
            <span><?php echo htmlspecialchars($e['first_name'] . ($e['middle_name'] ? ' '.$e['middle_name'] : '') . ' ' . $e['last_name']); ?></span>

            <span class="rl">Program</span>
            <span><?php echo htmlspecialchars($e['program_name'] . ' (' . $e['program_code'] . ')'); ?></span>

            <span class="rl">College</span>
            <span><?php echo htmlspecialchars($e['college_name']); ?></span>

            <span class="rl">Year Level</span>
            <span><?php echo htmlspecialchars($e['year_level']); ?></span>

            <span class="rl">Semester</span>
            <span><?php echo htmlspecialchars($e['semester']); ?></span>

            <span class="rl">School Year</span>
            <span><?php echo htmlspecialchars($e['school_year']); ?></span>

            <span class="rl">Email</span>
            <span><?php echo htmlspecialchars($e['email']); ?></span>

            <span class="rl">Contact</span>
            <span><?php echo htmlspecialchars($e['contact_number']); ?></span>

            <span class="rl">Submitted</span>
            <span><?php echo htmlspecialchars($e['created_at']); ?></span>

            <?php if ($e['remarks']): ?>
            <span class="rl">Remarks</span>
            <span style="color:#ff7675;"><?php echo htmlspecialchars($e['remarks']); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php endforeach; ?>

    <div style="text-align:center; margin-top:10px;">
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>

    <?php endif; ?>

</div>
</div>
</body>
</html>
