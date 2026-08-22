<?php
session_start();
require_once '../includes/csrf.php';
require_once '../includes/rate_limit.php';
require_once '../includes/audit.php';
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $rlKey = rl_key('admin_login', $username);
        if (!rl_is_allowed($rlKey)) {
            $wait = max(1, (int)ceil(rl_seconds_remaining($rlKey) / 60));
            $error = "Too many failed attempts. Please try again in about $wait minute(s).";
        } else {
            require_once '../includes/conn.php';
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $admin = $res->num_rows > 0 ? $res->fetch_assoc() : null;

            if ($admin && password_verify($password, $admin['password'])) {
                rl_reset($rlKey);
                // Regenerate the session ID on privilege change to prevent session fixation.
                session_regenerate_id(true);
                $_SESSION['admin_id']   = $admin['admin_id'];
                $_SESSION['admin_user'] = $admin['username'];
                log_action_as($conn, $admin['admin_id'], $admin['username'], 'login_success');
                header('Location: dashboard.php');
                exit;
            }

            rl_register_failure($rlKey);
            log_action_as($conn, null, $username, 'login_failed');
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="login-page">
    <div class="login-box">
        <div style="text-align:center; margin-bottom:22px;">
            <div style="width:54px;height:54px;background:linear-gradient(135deg,#00cec9,#00b894);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;margin-bottom:14px;">A</div>
            <div class="login-title">Admin Portal</div>
            <div class="login-sub">Student Enrollment Management System<br>BulSU — IT 211</div>
        </div>

        <?php if ($error): ?>
        <div class="login-error" style="display:block;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="admin" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-solid-success" style="margin-top:8px;">
                Login to Dashboard
            </button>
        </form>

        <div style="text-align:center; margin-top:12px;">
            <a href="../index.php" style="font-size:12px; color:rgba(255,255,255,0.45); text-decoration:none;">Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
