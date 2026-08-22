<?php
session_start();
$token = trim($_GET['token'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Alab E-BulSU</title>
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
        <div class="auth-title">Reset Password</div>
        <div class="auth-sub">Choose a new password for your account.</div>

        <?php if ($token === ''): ?>
            <div class="auth-msg error" style="display:block;">
                Missing reset token. Use the link from the forgot-password page.
            </div>
        <?php else: ?>
            <div class="auth-msg error" id="formError"></div>
            <div class="auth-msg success" id="formSuccess"></div>

            <form id="resetForm" novalidate>
                <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="newPassword" placeholder="At least 6 characters" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirmPassword" placeholder="Repeat password" required minlength="6">
                </div>
                <button type="submit" class="btn-solid-primary" id="submitBtn" style="margin-top:8px;">
                    Reset Password
                </button>
            </form>
        <?php endif; ?>

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
    $('#resetForm').on('submit', function (e) {
        e.preventDefault();
        $('#formError, #formSuccess').hide();
        var pw  = $('#newPassword').val();
        var cpw = $('#confirmPassword').val();

        if (pw.length < 6) { $('#formError').text('Password must be at least 6 characters.').show(); return; }
        if (pw !== cpw) { $('#formError').text('Passwords do not match.').show(); return; }

        $('#submitBtn').prop('disabled', true).text('Resetting...');
        $.post('../ajax/reset_password_confirm.php', { token: $('#token').val(), new_password: pw }, function (res) {
            if (res.success) {
                $('#formSuccess').text(res.message).show();
                $('#resetForm').hide();
                setTimeout(function () { window.location.href = 'login.php'; }, 1800);
            } else {
                $('#submitBtn').prop('disabled', false).text('Reset Password');
                $('#formError').text(res.message).show();
            }
        }, 'json').fail(function () {
            $('#submitBtn').prop('disabled', false).text('Reset Password');
            $('#formError').text('Server error. Please try again.').show();
        });
    });
});
</script>
</body>
</html>
