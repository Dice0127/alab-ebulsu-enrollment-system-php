<?php
// student/auth_check.php — Include at top of protected student pages
session_start();
if (!isset($_SESSION['student_account_id'])) {
    header('Location: login.php');
    exit;
}
?>
