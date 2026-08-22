<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Register — Alab E-BulSU</title>
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
        
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: all 0.3s ease;
            background: linear-gradient(90deg, #e74c3c, #f39c12);
        }
        
        .strength-fill.weak {
            width: 25%;
            background: linear-gradient(90deg, #e74c3c, #e74c3c);
        }
        
        .strength-fill.fair {
            width: 50%;
            background: linear-gradient(90deg, #f39c12, #f39c12);
        }
        
        .strength-fill.good {
            width: 75%;
            background: linear-gradient(90deg, #f39c12, #27ae60);
        }
        
        .strength-fill.strong {
            width: 100%;
            background: linear-gradient(90deg, #27ae60, #27ae60);
        }
        
        .strength-text {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .password-match {
            font-size: 11px;
            margin-top: 6px;
            padding: 6px 8px;
            border-radius: 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .password-match.match {
            background: rgba(39, 174, 96, 0.15);
            color: #27ae60;
        }
        
        .password-match.not-match {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="auth-page">
    <div class="auth-box" style="width:720px;">
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
            <div class="auth-logo">
                <div class="auth-logo-img">S</div>
            </div>
            <div>
                <div class="auth-title">Create Account</div>
                <div class="auth-sub">BulSU Student Enrollment Management System</div>
            </div>
        </div>

        <div class="auth-msg" id="authMsg"></div>

        <form id="registerForm" novalidate style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">
            <!-- Full Name Row -->
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="first_name" name="first_name" maxlength="50" placeholder="Juan" required>
            </div>
            <div class="form-group">
                <label>Middle Name (Optional)</label>
                <input type="text" id="middle_name" name="middle_name" maxlength="50" placeholder="Santos">
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="last_name" name="last_name" maxlength="50" placeholder="dela Cruz" required>
            </div>

            <!-- Personal Details Row -->
            <div class="form-group">
                <label>Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">— Select Gender —</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" id="birthday" name="birthday" required>
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" maxlength="20" placeholder="09XXXXXXXXX" required>
            </div>

            <!-- Email Row (full width) -->
            <div class="form-group" style="grid-column:1/-1;">
                <label>Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@email.com" required>
            </div>

            <!-- Address Row (full width) -->
            <div class="form-group" style="grid-column:1/-1;">
                <label>Address</label>
                <textarea id="address" name="address" rows="2" placeholder="Street, Barangay, Town/City, Province" required></textarea>
            </div>

            <!-- Password Section (full width, 2 equal columns) -->
            <div style="grid-column:1/-1; display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">👁️</button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar"><div id="strengthMeter" class="strength-fill"></div></div>
                        <div id="strengthText" class="strength-text">Strength: Weak</div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">👁️</button>
                    </div>
                    <div id="passwordMatch" class="password-match" style="display:none;"></div>
                </div>
            </div>

            <!-- Button (full width) -->
            <button type="submit" class="btn-solid-primary" id="registerBtn" style="grid-column:1/-1; margin-top:8px;">
                Create Account
            </button>
        </form>

        <hr class="auth-divider">
        <div class="auth-footer">
            Already have an account? <a href="login.php">Log In</a>
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

// Calculate password strength
function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength += 1;
    if (password.length >= 10) strength += 1;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
    if (/\d/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    return strength;
}

// Get password strength label
function getStrengthLabel(strength) {
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    return labels[strength] || 'Weak';
}

// Update password strength indicator
function updatePasswordStrength() {
    const password = $('#password').val();
    const $meter = $('#strengthMeter');
    const $text = $('#strengthText');
    
    if (!password) {
        $meter.removeClass('weak fair good strong');
        $text.text('Strength: Enter a password');
        return;
    }
    
    const strength = calculatePasswordStrength(password);
    const label = getStrengthLabel(strength);
    
    $meter.removeClass('weak fair good strong');
    
    if (strength <= 1) {
        $meter.addClass('weak');
        $text.text('Strength: Weak');
    } else if (strength === 2) {
        $meter.addClass('fair');
        $text.text('Strength: Fair');
    } else if (strength === 3) {
        $meter.addClass('good');
        $text.text('Strength: Good');
    } else {
        $meter.addClass('strong');
        $text.text('Strength: Strong');
    }
}

// Check if passwords match
function checkPasswordMatch() {
    const password = $('#password').val();
    const confirmPassword = $('#confirm_password').val();
    const $matchDiv = $('#passwordMatch');
    
    if (!confirmPassword) {
        $matchDiv.hide();
        return;
    }
    
    if (password === confirmPassword) {
        $matchDiv.show().removeClass('not-match').addClass('match').text('✓ Passwords match');
    } else {
        $matchDiv.show().removeClass('match').addClass('not-match').text('✗ Passwords do not match');
    }
}

$(document).ready(function () {
    // Listen to password input
    $('#password').on('input', function() {
        updatePasswordStrength();
        checkPasswordMatch();
    });
    
    // Listen to confirm password input
    $('#confirm_password').on('input', function() {
        checkPasswordMatch();
    });
    
    function showMsg(msg, type) {
        $('#authMsg').text(msg).removeClass('success error').addClass(type).show();
    }
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();
        var fn  = $.trim($('#first_name').val());
        var mn  = $.trim($('#middle_name').val());
        var ln  = $.trim($('#last_name').val());
        var gen = $.trim($('#gender').val());
        var bd  = $.trim($('#birthday').val());
        var em  = $.trim($('#email').val());
        var cn  = $.trim($('#contact_number').val());
        var ad  = $.trim($('#address').val());
        var pw  = $('#password').val();
        var cpw = $('#confirm_password').val();

        // Validate required fields
        if (!fn || !ln || !gen || !bd || !em || !cn || !ad || !pw || !cpw) {
            showMsg('Please fill in all required fields.', 'error');
            return;
        }

        // Validate email format
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(em)) {
            showMsg('Please enter a valid email address.', 'error');
            return;
        }

        // Validate contact number (basic validation)
        if (!/^\d{10,}$/.test(cn.replace(/\D/g, ''))) {
            showMsg('Contact number must have at least 10 digits.', 'error');
            return;
        }

        // Validate age - must be at least 15 years old
        var birthDate = new Date(bd);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age < 15) {
            showMsg('You must be at least 15 years old to register.', 'error');
            return;
        }

        if (pw.length < 6) {
            showMsg('Password must be at least 6 characters.', 'error');
            return;
        }
        if (pw !== cpw) {
            showMsg('Passwords do not match.', 'error');
            return;
        }

        $('#registerBtn').prop('disabled', true).text('Creating account...');
        $.post('../ajax/student_register.php', {
            first_name: fn,
            middle_name: mn,
            last_name: ln,
            gender: gen,
            birthday: bd,
            email: em,
            contact_number: cn,
            address: ad,
            password: pw
        }, function (res) {
            if (res.success) {
                showMsg('Account created! Redirecting to login...', 'success');
                setTimeout(function () { window.location.href = 'login.php?registered=1'; }, 1500);
            } else {
                showMsg(res.message, 'error');
                $('#registerBtn').prop('disabled', false).text('Create Account');
            }
        }, 'json').fail(function () {
            showMsg('Server error. Please try again.', 'error');
            $('#registerBtn').prop('disabled', false).text('Create Account');
        });
    });
});
</script>
</body>
</html>
