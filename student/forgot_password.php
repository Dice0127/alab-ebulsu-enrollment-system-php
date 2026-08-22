<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
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
        <div class="auth-title">Forgot Password</div>
        <div class="auth-sub">Enter your account email to get a reset link.</div>

        <div class="auth-msg error" id="formError"></div>
        <div class="auth-msg success" id="formSuccess"></div>

        <div id="linkBox" style="display:none; margin:14px 0; padding:12px 14px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.12); border-radius:8px; font-size:12px; word-break:break-all;">
            <div style="color:rgba(255,255,255,0.5); margin-bottom:6px;">
                No email service is set up for this project, so here's your reset link directly:
            </div>
            <a id="linkAnchor" href="#" style="color:#74b9ff;"></a>
        </div>

        <form id="forgotForm" novalidate>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@email.com" required autofocus>
            </div>
            <button type="submit" class="btn-solid-primary" id="submitBtn" style="margin-top:8px;">
                Send Reset Link
            </button>
        </form>

        <hr class="auth-divider">
        <div class="auth-footer">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
$(document).ready(function () {
    $('#forgotForm').on('submit', function (e) {
        e.preventDefault();
        $('#formError, #formSuccess').hide();
        $('#linkBox').hide();
        var em = $.trim($('#email').val());
        if (!em) { $('#formError').text('Please enter your email.').show(); return; }

        $('#submitBtn').prop('disabled', true).text('Sending...');
        $.post('../ajax/request_password_reset.php', { email: em }, function (res) {
            $('#submitBtn').prop('disabled', false).text('Send Reset Link');
            if (res.success) {
                $('#formSuccess').text(res.message).show();
                if (res.reset_link) {
                    $('#linkAnchor').attr('href', res.reset_link).text(res.reset_link);
                    $('#linkBox').show();
                }
            } else {
                $('#formError').text(res.message).show();
            }
        }, 'json').fail(function () {
            $('#submitBtn').prop('disabled', false).text('Send Reset Link');
            $('#formError').text('Server error. Please try again.').show();
        });
    });
});
</script>
</body>
</html>
