<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="topbar">
    <div class="sys-title">Alab E-BulSU — Admin Dashboard</div>
    <div class="topbar-right">
        <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_user']); ?></strong></span>
        <a href="../student/index.php" target="_blank">Student Portal</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="sidebar">
    <div class="sidebar-logo"><img src="../Bulsu_Logo.png" alt="BulSU" style="height:40px; width:40px; margin-right:8px; vertical-align:middle;"> <span>Alab E-BulSU</span></div>
    <div class="sidebar-section">Main</div>
    <ul>
        <li><a href="dashboard.php" <?php echo ($currentPage==='dashboard.php')?'class="active"':''; ?>>Dashboard</a></li>
        <li><a href="enrollments.php" <?php echo ($currentPage==='enrollments.php')?'class="active"':''; ?>>Enrollments</a></li>
    </ul>
    <div class="sidebar-section">Records</div>
    <ul>
        <li><a href="students.php" <?php echo ($currentPage==='students.php')?'class="active"':''; ?>>Students</a></li>
        <li><a href="colleges.php" <?php echo ($currentPage==='colleges.php')?'class="active"':''; ?>>Colleges</a></li>
        <li><a href="programs.php" <?php echo ($currentPage==='programs.php')?'class="active"':''; ?>>Programs</a></li>
    </ul>
    <div class="sidebar-section">Curriculum</div>
    <ul>
        <li><a href="subjects.php" <?php echo ($currentPage==='subjects.php')?'class="active"':''; ?>>Courses</a></li>
        <li><a href="curriculum.php" <?php echo ($currentPage==='curriculum.php')?'class="active"':''; ?>>Curriculum</a></li>
        <li><a href="sections.php" <?php echo ($currentPage==='sections.php')?'class="active"':''; ?>>Sections</a></li>
    </ul>
    <div class="sidebar-section">Reports</div>
    <ul>
        <li><a href="charts.php" <?php echo ($currentPage==='charts.php')?'class="active"':''; ?>>Charts &amp; Analytics</a></li>
        <li><a href="audit_log.php" <?php echo ($currentPage==='audit_log.php')?'class="active"':''; ?>>Audit Log</a></li>
    </ul>
</div>
