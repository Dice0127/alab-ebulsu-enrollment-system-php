<?php require_once 'auth_check.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="topbar">
    <div class="sys-title"><img src="../Bulsu_Logo.png" alt="BulSU Logo" style="height:32px; width:32px; margin-right:10px; vertical-align:middle;"> Alab E-BulSU — My Profile</div>
    <div class="topbar-right">
        <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong></span>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="page-header">
        <h2>Personal Information</h2>
    </div>

    <div class="profile-container">
        <!-- Personal Details -->
        <div class="profile-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3>Personal Details</h3>
                <button class="edit-btn-primary" id="editPersonalBtn" onclick="toggleEditForm('personal')">✎ Edit Information</button>
            </div>
            
            <div id="noDataMessage" style="display:none; background: rgba(255,193,7,0.1); border: 1px solid rgba(255,193,7,0.3); padding: 16px; border-radius: 8px; margin-bottom: 16px; color: rgba(255,255,255,0.8); font-size: 13px;">
                <strong>Note:</strong> You haven't started your enrollment yet. Complete your enrollment process first to add your personal information here.
                <br><a href="enroll.php" style="color: #a29bfe; text-decoration: underline; margin-top: 8px; display: inline-block;">Go to Enrollment →</a>
            </div>
            
            <div id="personalView" class="info-display">
                <div class="info-grid">
                    <div class="info-item">
                        <label>First Name</label>
                        <p id="firstName">-</p>
                    </div>
                    <div class="info-item">
                        <label>Last Name</label>
                        <p id="lastName">-</p>
                    </div>
                    <div class="info-item">
                        <label>Gender</label>
                        <p id="gender">-</p>
                    </div>
                    <div class="info-item">
                        <label>Date of Birth</label>
                        <p id="birthday">-</p>
                    </div>
                    <div class="info-item">
                        <label>Contact Number</label>
                        <p id="contactNumber">-</p>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <p id="enrollEmail">-</p>
                    </div>
                </div>
                <div class="info-item full-width" style="margin-top: 16px;">
                    <label>Address</label>
                    <p id="address">-</p>
                </div>
            </div>

            <div id="personalForm" style="display:none;">
                <div class="form-wrapper">
                    <div class="form-edit-layout">
                        <!-- Edit Form -->
                        <div class="edit-form-panel">
                            <h4 style="color: rgba(255,255,255,0.7); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 16px 0; font-weight: 600;">New Information</h4>
                            <div class="form-grid-2col">
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input type="text" id="editFirstName" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input type="text" id="editLastName" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender *</label>
                                    <select id="editGender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth *</label>
                                    <input type="date" id="editBirthday" required>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number *</label>
                                    <input type="text" id="editContactNumber" required placeholder="e.g., 09123456789">
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" id="editEnrollEmail" required>
                                </div>
                            </div>
                            <div class="form-group full-width" style="margin-top: 16px;">
                                <label>Address *</label>
                                <textarea id="editAddress" required rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-save" onclick="savePersonalInfo()">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="toggleEditForm('personal')">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="profile-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3>Account Security</h3>
                <button class="edit-btn-primary" id="changePasswordBtn" onclick="togglePasswordForm()">🔒 Change Password</button>
            </div>
            
            <div id="passwordForm" style="display:none;">
                <div class="form-wrapper">
                    <div class="form-grid-2col">
                        <div class="form-group full-width">
                            <label>Current Password *</label>
                            <input type="password" id="currentPassword" required placeholder="Enter your current password">
                        </div>
                        <div class="form-group full-width">
                            <label>New Password *</label>
                            <input type="password" id="newPassword" required placeholder="Enter new password (min 6 characters)">
                        </div>
                        <div class="form-group full-width">
                            <label>Confirm New Password *</label>
                            <input type="password" id="confirmPassword" required placeholder="Confirm new password">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-save" onclick="savePassword()">Update Password</button>
                        <button type="button" class="btn-cancel" onclick="togglePasswordForm()">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-container { max-width:800px; margin:0 auto; padding:24px; }
    
    .profile-section {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    
    .profile-section h3 {
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 16px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-item.full-width {
        grid-column: 1 / -1;
    }
    
    .info-item label {
        color: rgba(255,255,255,0.6);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .info-item p {
        color: #fff;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }
    
    .form-wrapper {
        background: rgba(255,255,255,0.05);
        padding: 20px;
        border-radius: 12px;
        margin-top: 16px;
    }
    
    .form-edit-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 20px;
    }
    
    .edit-form-panel {
        display: flex;
        flex-direction: column;
    }
    
    .form-grid-2col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-group label {
        color: rgba(255,255,255,0.8);
        font-size: 13px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 12px 14px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        color: #fff;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        transition: all 0.2s;
    }
    
    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255,255,255,0.4);
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: rgba(162,155,254,0.6);
        background: rgba(162,155,254,0.15);
        box-shadow: 0 0 0 3px rgba(162,155,254,0.1);
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }
    
    .edit-btn-primary,
    .btn-save,
    .btn-cancel {
        padding: 10px 18px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .edit-btn-primary {
        background: linear-gradient(135deg,#6c5ce7,#a29bfe);
        color: #fff;
    }
    
    .edit-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(108,92,231,0.3);
    }
    
    .btn-save {
        background: linear-gradient(135deg,#6c5ce7,#a29bfe);
        color: #fff;
        flex: 1;
    }
    
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(108,92,231,0.3);
    }
    
    .btn-cancel {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
        flex: 1;
    }
    
    .btn-cancel:hover {
        background: rgba(255,255,255,0.15);
    }
    
    .info-display {
        margin-bottom: 16px;
    }
    
    @media (max-width: 768px) {
        .form-grid-2col {
            grid-template-columns: 1fr;
        }
        
        .form-edit-layout {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .profile-container {
            padding: 16px;
        }
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
$(document).ready(function() {
    loadProfileData();
});

function loadProfileData() {
    $.ajax({
        url: '../ajax/get_student_profile.php',
        type: 'GET',
        dataType: 'json',
        success: function(profile) {
            console.log('Profile data received:', profile);
            
            if (!profile || !profile.success) {
                console.error('Profile data error:', profile);
                alert('Unable to load profile data. Please refresh the page.');
                return;
            }
        
        // Check if student has enrollment data
        const hasEnrollmentData = profile.first_name || profile.last_name || profile.gender || profile.birthday;
        
        if (!hasEnrollmentData) {
            console.warn('Student has no enrollment data yet');
            $('#noDataMessage').show();
        } else {
            $('#noDataMessage').hide();
        }
        
        // Personal info - display view
        $('#firstName').text(profile.first_name || '-');
        $('#lastName').text(profile.last_name || '-');
        $('#gender').text(profile.gender || '-');
        $('#birthday').text(profile.birthday ? formatDate(profile.birthday) : '-');
        $('#contactNumber').text(profile.contact_number || '-');
        $('#enrollEmail').text(profile.enrollment_email || '-');
        $('#address').text(profile.address || '-');
        
        // Populate edit form
        $('#editFirstName').val(profile.first_name || '');
        $('#editLastName').val(profile.last_name || '');
        $('#editGender').val(profile.gender || '');
        $('#editBirthday').val(profile.birthday || '');
        $('#editContactNumber').val(profile.contact_number || '');
        $('#editEnrollEmail').val(profile.enrollment_email || '');
        $('#editAddress').val(profile.address || '');
        
        console.log('Edit form populated', {
            firstName: $('#editFirstName').val(),
            lastName: $('#editLastName').val(),
            gender: $('#editGender').val()
        });
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error loading profile data:', error);
            console.error('Response:', xhr.responseText);
            alert('Error loading profile data. Please refresh the page or contact support.');
        }
    });
}

function toggleEditForm(section) {
    if (section === 'personal') {
        $('#personalView').toggle();
        $('#personalForm').toggle();
        
        // Scroll into view if showing the form
        if ($('#personalForm').is(':visible')) {
            setTimeout(function() {
                $('#personalForm')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    }
}

function savePersonalInfo() {
    let data = {
        first_name: $('#editFirstName').val(),
        last_name: $('#editLastName').val(),
        gender: $('#editGender').val(),
        birthday: $('#editBirthday').val(),
        contact_number: $('#editContactNumber').val(),
        enrollment_email: $('#editEnrollEmail').val(),
        address: $('#editAddress').val()
    };
    
    $.ajax({
        url: '../ajax/update_student_profile.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                toggleEditForm('personal');
                loadProfileData();
                alert('Profile updated successfully!');
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating profile:', error);
            alert('Error updating profile');
        }
    });
}

function togglePasswordForm() {
    $('#passwordForm').toggle();
    if ($('#passwordForm').is(':visible')) {
        $('#currentPassword').focus();
    }
}

function savePassword() {
    let currentPassword = $('#currentPassword').val();
    let newPassword = $('#newPassword').val();
    let confirmPassword = $('#confirmPassword').val();
    
    // Validation
    if (!currentPassword) {
        alert('Please enter your current password');
        return;
    }
    
    if (!newPassword) {
        alert('Please enter a new password');
        return;
    }
    
    if (newPassword.length < 6) {
        alert('New password must be at least 6 characters');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    $.ajax({
        url: '../ajax/update_student_password.php',
        type: 'POST',
        data: {
            current_password: currentPassword,
            password: newPassword
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                togglePasswordForm();
                $('#currentPassword').val('');
                $('#newPassword').val('');
                $('#confirmPassword').val('');
                alert('Password updated successfully!');
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating password:', error);
            alert('Error updating password');
        }
    });
}

function formatDate(date) {
    if (!date) return '-';
    let d = new Date(date);
    return d.toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'});
}
</script>
</body>
</html>
