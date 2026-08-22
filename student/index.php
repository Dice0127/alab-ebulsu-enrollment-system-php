<?php session_start(); $loggedIn = isset($_SESSION['student_account_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alab E-BulSU Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="topbar">
    <div class="sys-title"><img src="../Bulsu_Logo.png" alt="BulSU Logo" style="height:32px; width:32px; margin-right:10px; vertical-align:middle;"> Alab E-BulSU — Student Portal</div>
    <div class="topbar-right">
        <?php if ($loggedIn): ?>
            <span>Hello, <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong></span>
            <a href="dashboard.php">My Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Log In</a>
            <a href="register.php">Sign Up</a>
        <?php endif; ?>
    </div>
</div>

<div class="main-content-full">
    <div class="student-hero">
        <h1>Welcome to Alab E-BulSU</h1>
        <p>Bulacan State University — Student Enrollment Management System</p>
        <p style="font-size:12px; color:rgba(255,255,255,0.35); margin-top:-6px; margin-bottom:32px;">IT 211 Web Systems and Technologies &nbsp;|&nbsp; Dr. Aaron Paul M. Dela Rosa</p>

        <?php if ($loggedIn): ?>
        <div class="student-options">
            <a href="dashboard.php" class="option-card">
                <h3>My Dashboard</h3>
                <p>View your enrollment record and student number</p>
            </a>
            <a href="enroll.php" class="option-card">
                <h3>Enroll Now</h3>
                <p>Submit your enrollment for this semester</p>
            </a>
            <a href="status.php" class="option-card">
                <h3>Check Status</h3>
                <p>View your current enrollment status</p>
            </a>
        </div>
        <?php else: ?>
        <div class="student-options">
            <a href="register.php" class="option-card">
                <h3>Sign Up</h3>
                <p>Create a student account to begin your enrollment</p>
            </a>
            <a href="login.php" class="option-card">
                <h3>Log In</h3>
                <p>Already have an account? Log in to check your status</p>
            </a>
        </div>
        <p style="margin-top:24px; font-size:13px; color:rgba(255,255,255,0.35);">
            New student? Sign up first, then submit your enrollment. A student number will be auto-assigned.
        </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
