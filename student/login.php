<?php
session_start();
if (isset($_SESSION['student_account_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .password-input-wrapper input {
            width: 100%;
            padding-right: 40px;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 8px;
            color: rgba(255, 255, 255, 0.6);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: rgba(162, 155, 254, 0.8);
        }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="auth-page">
    <div class="auth-box">
        <div class="auth-logo">
            <div class="auth-logo-img">S</div>
        </div>
        <div class="auth-title">Student Login</div>
        <div class="auth-sub">BulSU Student Enrollment Management System</div>

        <?php if (isset($_GET['registered'])): ?>
        <div class="auth-msg success" style="display:block;">Account created! You can now log in.</div>
        <?php endif; ?>
        <?php if (isset($_GET['logout'])): ?>
        <div class="auth-msg success" style="display:block;">You have been logged out successfully.</div>
        <?php endif; ?>

        <div class="auth-msg error" id="loginError"></div>

        <form id="loginForm" novalidate>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@email.com" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn-solid-primary" id="loginBtn" style="margin-top:8px;">
                Log In
            </button>
        </form>

        <div style="text-align:center; margin-top:10px;">
            <a href="forgot_password.php" style="font-size:12px; color:rgba(255,255,255,0.5);">Forgot your password?</a>
        </div>

        <hr class="auth-divider">
        <div class="auth-footer">
            Don't have an account? <a href="register.php">Sign Up</a>
            <br><br>
            <a href="../index.php" style="color:rgba(255,255,255,0.35);">Back to Home</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
}

$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault();
        $('#loginError').hide();
        var em = $.trim($('#email').val());
        var pw = $('#password').val();
        if (!em || !pw) { $('#loginError').text('Please fill in all fields.').show(); return; }
        $('#loginBtn').prop('disabled', true).text('Logging in...');
        $.post('../ajax/student_login.php', { email: em, password: pw }, function (res) {
            if (res.success) {
                window.location.href = 'dashboard.php';
            } else {
                $('#loginError').text(res.message).show();
                $('#loginBtn').prop('disabled', false).text('Log In');
            }
        }, 'json').fail(function () {
            $('#loginError').text('Server error. Please try again.').show();
            $('#loginBtn').prop('disabled', false).text('Log In');
        });
    });
});
</script>
</body>
</html>
