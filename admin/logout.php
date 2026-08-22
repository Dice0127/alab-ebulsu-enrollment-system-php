<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    require_once '../includes/conn.php';
    require_once '../includes/audit.php';
    log_action($conn, 'logout');
}
session_destroy();
header('Location: login.php');
exit;
?>
