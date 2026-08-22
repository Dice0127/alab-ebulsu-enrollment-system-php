<?php
require_once 'auth_check.php';
require_once '../includes/conn.php';

$accountId = (int)$_SESSION['student_account_id'];
$stmt = $conn->prepare("SELECT enrollment_id, student_number, status, semester, school_year, program_code, year_level FROM enrollment WHERE account_id = ? ORDER BY enrollment_id DESC LIMIT 1");
$stmt->bind_param('i', $accountId);
$stmt->execute();
$existing = $stmt->get_result();
$hasExisting = $existing->num_rows > 0 ? $existing->fetch_assoc() : null;

// Fetch saved profile info (from registration/profile page) to prefill the form
$profileStmt = $conn->prepare("SELECT first_name, middle_name, last_name, gender, birthday, email, contact_number, address FROM student_account WHERE account_id = ?");
$profileStmt->bind_param('i', $accountId);
$profileStmt->execute();
$profile = $profileStmt->get_result()->fetch_assoc() ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll — Alab E-BulSU</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
    <style>
        .enroll-wrap { max-width:860px; margin:0 auto; }
        
        /* Step Indicator */
        .step-indicator { display: flex; align-items: center; justify-content: center; margin-bottom: 28px; gap: 20px; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .step-number { width: 48px; height: 48px; border-radius: 50%; background: rgba(162, 155, 254, 0.2); border: 2px solid rgba(162, 155, 254, 0.4); display: flex; align-items: center; justify-content: center; font-weight: bold; color: rgba(162, 155, 254, 0.8); font-size: 18px; transition: all 0.3s; }
        .step.active .step-number { background: rgba(162, 155, 254, 0.5); border-color: rgba(162, 155, 254, 0.8); color: #fff; box-shadow: 0 0 0 4px rgba(162, 155, 254, 0.1); }
        .step.completed .step-number { background: rgba(85, 239, 196, 0.3); border-color: rgba(85, 239, 196, 0.6); color: #55efc4; }
        .step-label { font-size: 13px; color: rgba(255, 255, 255, 0.6); text-align: center; max-width: 80px; }
        .step.active .step-label { color: rgba(162, 155, 254, 0.9); }
        .step.completed .step-label { color: rgba(85, 239, 196, 0.8); }
        .step-connector { flex: 1; height: 2px; background: rgba(162, 155, 294, 0.2); max-width: 60px; }
        .step.completed ~ .step-connector { background: rgba(85, 239, 196, 0.4); }
        
        .step-content { display: none; }
        .step-content.active { display: block; }
        
        .step-actions { display: flex; gap: 12px; justify-content: space-between; margin-top: 24px; }
        .step-actions .btn { flex: 1; }
        .step-actions .btn-prev { flex: 0.5; }
        
        .confirm-box { background: rgba(10,15,50,0.9); color:#55efc4; border:2px solid rgba(85,239,196,0.3); border-radius:10px; padding:28px 32px; text-align:center; display:none; }
        .confirm-box .sn-display { font-size:32px; font-weight:bold; color:#55efc4; background: rgba(85,239,196,0.15); border:2px solid rgba(85,239,196,0.4); border-radius:8px; padding:12px 24px; display:inline-block; margin:14px 0; letter-spacing:2px; }
        .section-label { font-weight:bold; font-size:13px; color:rgba(255,255,255,0.8); margin-bottom:12px; padding-bottom:6px; border-bottom:1px solid rgba(255,255,255,0.1); }
        .course-list { background: rgba(10,15,50,0.6); border-radius:8px; padding:14px; margin-bottom:14px; border:1px solid rgba(255,255,255,0.1); }
        .course-item { display:flex; justify-content:space-between; align-items:center; padding:10px; border-bottom:1px solid rgba(255,255,255,0.1); }
        .course-item:last-child { border-bottom:none; }
        .course-info { flex:1; }
        .course-code { font-weight:bold; color:#a29bfe; font-size:13px; }
        .course-name { font-size:14px; color:#fff; margin:2px 0; }
        .course-units { font-size:12px; color:rgba(255,255,255,0.5); }
        .section-card { background: rgba(10,15,50,0.6); border-radius:8px; padding:12px 14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center; cursor:pointer; transition:all 0.2s; border:2px solid rgba(255,255,255,0.12); }
        .section-card:hover { border-color:rgba(162,155,254,0.5); background: rgba(162,155,254,0.08); }
        .section-card.selected { border-color:rgba(162,155,254,0.6); background: rgba(162,155,254,0.12); box-shadow:0 0 0 2px rgba(162,155,254,0.2); }
        .section-card.full { opacity:0.5; cursor:not-allowed; }
        .section-card.full:hover { border-color: rgba(255,255,255,0.12); background: rgba(10,15,50,0.6); }
        .section-info { flex:1; }
        .section-code { font-weight:bold; color:#a29bfe; font-size:13px; }
        .section-slots { font-size:12px; color:rgba(255,255,255,0.5); margin-top:2px; }
        .section-status { font-size:12px; font-weight:bold; padding:4px 10px; border-radius:4px; }
        .section-status.open { color:#27ae60; background:rgba(39,174,96,0.1); }
        .section-status.full { color:#e74c3c; background:rgba(231,76,60,0.1); }
        .radio-hidden { display:none; }
    </style>
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>

<div class="topbar">
    <div class="sys-title"><img src="../Bulsu_Logo.png" alt="BulSU Logo" style="height:32px; width:32px; margin-right:10px; vertical-align:middle;"> Alab E-BulSU — Enrollment Form</div>
    <div class="topbar-right">
        <span><?php echo htmlspecialchars($_SESSION['student_name']); ?></span>
        <a href="profile.php">My Profile</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>
<div class="main-content-full">
<div class="enroll-wrap">

<?php if ($hasExisting && $hasExisting['status'] === 'Pending'): ?>
<div class="card" style="text-align:center; padding:32px; background:rgba(255,255,255,0.08); border-radius:16px; border:1px solid rgba(255,255,255,0.15);">
    <div style="font-size:46px; margin-bottom:12px;">⏳</div>
    <h3 style="font-size:18px; margin-bottom:8px;">Your Enrollment is Pending Approval</h3>
    <p style="font-size:13px; color:rgba(255,255,255,0.5); margin-bottom:20px;">
        Student Number: <strong style="font-size:18px; color:#a29bfe;"><?php echo htmlspecialchars($hasExisting['student_number']); ?></strong>
        <br><small>Please wait for the admin to approve your enrollment before adding more courses.</small>
    </p>
    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    &nbsp;<a href="status.php" class="btn btn-secondary">Check Status</a>
</div>

<?php else: ?>

<div class="confirm-box" id="confirmBox">
    <div style="font-size:48px; margin-bottom:10px;"></div>
    <h3 style="font-size:20px; margin-bottom:6px;">Courses Added Successfully!</h3>
    <p style="font-size:13px; margin-bottom:12px;">Your additional courses have been added to your enrollment.</p>
    <div style="margin-top:18px;">
        <a href="dashboard.php" class="btn btn-success">Go to Dashboard</a>
        &nbsp;<a href="status.php" class="btn btn-secondary">Check My Status</a>
    </div>
</div>

<div class="card" id="enrollCard">
    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active" id="step-1-indicator">
            <div class="step-number">1</div>
            <div class="step-label">Academic Info</div>
        </div>
        <div class="step-connector"></div>
        <div class="step" id="step-2-indicator">
            <div class="step-number">2</div>
            <div class="step-label">Select Courses</div>
        </div>
    </div>

    <h3 id="stepTitle"> Academic Information</h3>
    <p style="font-size:12px; color:rgba(255,255,255,0.5); margin-top:-10px; margin-bottom:18px;">
        <?php if ($hasExisting): ?>
            <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong> — Add more courses to your enrollment
        <?php else: ?>
            Enrolling as: <strong><?php echo htmlspecialchars($_SESSION['student_name']); ?></strong>
            &nbsp;|&nbsp; Student number will be auto-generated upon submission.
        <?php endif; ?>
    </p>
    <form id="enrollForm" novalidate>
        <!-- STEP 1: PERSONAL & ACADEMIC INFORMATION -->
        <div class="step-content active" id="step-1-content">
            <!-- PERSONAL INFORMATION -->
            <?php if (!$hasExisting): ?>
            <div class="section-label"> Personal Information</div>
            <div class="form-row-3">
                <div class="form-group"><label>First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" id="first_name" maxlength="50" value="<?php echo htmlspecialchars($profile['first_name'] ?? $_SESSION['student_first_name'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name" maxlength="50" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>"></div>
                <div class="form-group"><label>Last Name <span class="req">*</span></label>
                    <input type="text" name="last_name" id="last_name" maxlength="50" value="<?php echo htmlspecialchars($profile['last_name'] ?? $_SESSION['student_last_name'] ?? ''); ?>" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Gender <span class="req">*</span></label>
                    <select name="gender" id="gender" required>
                        <option value="">— Select —</option>
                        <?php $g = $profile['gender'] ?? ''; ?>
                        <option <?php echo $g === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option <?php echo $g === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option <?php echo $g === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                    </select></div>
                <div class="form-group"><label>Birthday <span class="req">*</span></label>
                    <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($profile['email'] ?? $_SESSION['student_email'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Contact Number <span class="req">*</span></label>
                    <input type="text" name="contact_number" id="contact_number" maxlength="20" placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>" required></div>
            </div>
            <div class="form-group"><label>Address <span class="req">*</span></label>
                <textarea name="address" id="address" rows="2" required><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea></div>
            <?php else: ?>
            <!-- Hidden fields for re-enrollment — use existing student data -->
            <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>">
            <input type="hidden" name="middle_name" value="<?php echo htmlspecialchars($profile['middle_name'] ?? ''); ?>">
            <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>">
            <input type="hidden" name="gender" value="<?php echo htmlspecialchars($profile['gender'] ?? ''); ?>">
            <input type="hidden" name="birthday" value="<?php echo htmlspecialchars($profile['birthday'] ?? ''); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>">
            <input type="hidden" name="contact_number" value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>">
            <input type="hidden" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>">
            <?php endif; ?>

            <!-- ACADEMIC INFORMATION -->
            <div class="section-label" style="margin-top:22px;"> Academic Information</div>
            <div class="form-row">
                <div class="form-group"><label>College <span class="req">*</span></label>
                    <select name="college_code" id="college_code" required onchange="onCollegeChanged()" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? 'disabled' : ''; ?>><option value="">Loading...</option></select>
                    <?php if ($hasExisting && $hasExisting['status'] === 'Approved'): ?><small style="color:rgba(85,239,196,0.7); margin-top:4px; display:block;">Fixed from your approved enrollment</small><?php endif; ?>
                </div>
                <div class="form-group"><label>Program <span class="req">*</span></label>
                    <select name="program_code" id="program_code" required onchange="onProgramChanged()" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? 'disabled' : ''; ?>><option value="">— Select a college first —</option></select>
                    <?php if ($hasExisting && $hasExisting['status'] === 'Approved'): ?><small style="color:rgba(85,239,196,0.7); margin-top:4px; display:block;">Fixed from your approved enrollment</small><?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Year Level <span class="req">*</span></label>
                    <select name="year_level" id="year_level" required onchange="onYearLevelChanged()" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? 'disabled' : ''; ?>>
                        <option value="">— Select —</option>
                        <option value="1st Year" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && $hasExisting['year_level'] === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && $hasExisting['year_level'] === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && $hasExisting['year_level'] === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && $hasExisting['year_level'] === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                    <?php if ($hasExisting && $hasExisting['status'] === 'Approved'): ?><small style="color:rgba(85,239,196,0.7); margin-top:4px; display:block;">Fixed from your approved enrollment</small><?php endif; ?>
                </div>
                <div class="form-group"><label>Semester <span class="req">*</span></label>
                    <select name="semester" id="semester" required onchange="refreshEnrollmentDetails()" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? 'disabled' : ''; ?>>
                        <option value="">— Select —</option>
                        <option value="1st Semester" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && strpos($hasExisting['semester'], '1st') !== false) ? 'selected' : ''; ?>>1st Semester</option>
                        <option value="2nd Semester" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && strpos($hasExisting['semester'], '2nd') !== false) ? 'selected' : ''; ?>>2nd Semester</option>
                        <option value="Summer Class" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved' && strpos($hasExisting['semester'], 'Summer') !== false) ? 'selected' : ''; ?>>Summer Class</option>
                    </select>
                    <?php if ($hasExisting && $hasExisting['status'] === 'Approved'): ?><small style="color:rgba(85,239,196,0.7); margin-top:4px; display:block;">Fixed from your approved enrollment</small><?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>School Year <span class="req">*</span></label>
                    <input type="text" name="school_year" id="school_year" maxlength="9" placeholder="e.g. 2025-2026" required value="<?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? htmlspecialchars($hasExisting['school_year']) : ''; ?>" <?php echo ($hasExisting && $hasExisting['status'] === 'Approved') ? 'readonly' : ''; ?>>
                    <span class="hint">Format: YYYY-YYYY</span>
                    <?php if ($hasExisting && $hasExisting['status'] === 'Approved'): ?><small style="color:rgba(85,239,196,0.7); margin-top:4px; display:block;">Fixed from your approved enrollment</small><?php endif; ?>
                </div>
                <div class="form-group"><label>Student Type <span class="req">*</span></label>
                    <select name="student_type" id="student_type" required>
                        <option value="">— Select —</option>
                        <option value="Regular">Regular</option>
                        <option value="Irregular">Irregular</option>
                        <option value="Transferee">Transferee</option>
                    </select></div>
            </div>

            <div id="formError" style="color:#ff7675; background:rgba(255,118,117,0.1); padding:10px 14px; border-radius:10px; font-size:13px; margin-bottom:14px; display:none;"></div>

            <div class="step-actions">
                <button type="button" class="btn btn-success" id="nextBtn" onclick="goToStep(2)">Continue to Courses  →</button>
            </div>
        </div>

        <!-- STEP 2: COURSE & SECTION SELECTION -->
        <div class="step-content" id="step-2-content">
            <div class="section-label"> Select Your Section</div>
            <div style="margin-bottom:18px; padding:12px 16px; background:rgba(162,155,254,0.1); border-left:4px solid rgba(162,155,254,0.4); border-radius:4px; font-size:13px; color:#a29bfe;">
                <strong>Available Sections:</strong>
                <div id="sectionsList" style="margin-top:8px;">
                    <p style="text-align:center; color:rgba(162,155,254,0.7);">Sections will appear based on your selection.</p>
                </div>
            </div>
            <input type="hidden" name="section_id" id="section_id">
            <div class="form-group"><label>Selected Section <span class="req">*</span></label>
                <div id="selectedSectionDisplay" style="min-height:42px; padding:10px 12px; border:1px solid rgba(255,255,255,0.2); border-radius:8px; background:rgba(10,15,50,0.6); color:rgba(255,255,255,0.7); display:flex; align-items:center;">None selected</div>
            </div>

            <div class="section-label" style="margin-top:22px;"> Subjects/Courses to Enroll</div>
            <div class="course-list" id="coursesList">
                <p style="text-align:center; color:rgba(255,255,255,0.5);">Courses will appear after section selection.</p>
            </div>
            <div style="font-size:14px; color:rgba(255,255,255,0.8); margin-top:8px; text-align:right; font-weight:bold;">Total Units: <span id="totalUnits">0</span></div>

            <div id="confirmationSummary" style="display:none; margin-top:18px; background:rgba(162,155,254,0.1); border-radius:10px; padding:16px; color:rgba(255,255,255,0.9); border:1px solid rgba(162,155,254,0.3);">
                <h4 style="margin-top:0; margin-bottom:10px; color:rgba(162,155,254,0.9);">Enrollment Summary</h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; font-size:13px; color:rgba(255,255,255,0.8);">
                    <div><strong>Program:</strong> <span id="summaryProgram">-</span></div>
                    <div><strong>Year Level:</strong> <span id="summaryYear">-</span></div>
                    <div><strong>Semester:</strong> <span id="summarySemester">-</span></div>
                    <div><strong>School Year:</strong> <span id="summarySchoolYear">-</span></div>
                    <div><strong>Section:</strong> <span id="summarySection">-</span></div>
                    <div><strong>Student Type:</strong> <span id="summaryType">-</span></div>
                    <div style="grid-column:1 / -1;"><strong>Total Units:</strong> <span id="summaryUnits">0</span></div>
                </div>
            </div>

            <div style="background:rgba(162,155,254,0.1); border-left:4px solid rgba(162,155,254,0.4); padding:12px 16px; border-radius:4px; font-size:13px; color:#a29bfe; margin-top:16px; margin-bottom:16px;">
                 <strong>Note:</strong> Your Student Number will be automatically generated and displayed after submission. Select your section carefully — enrollment in full sections is not allowed.
            </div>

            <div class="step-actions">
                <button type="button" class="btn btn-secondary btn-prev" onclick="goToStep(1)">← Back</button>
                <button type="submit" class="btn btn-success" id="submitBtn"> Submit Enrollment</button>
            </div>
        </div>
        </div>
    </form>
</div>
<?php endif; ?>

</div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
var allCourses = {};
var allSections = {};
var currentStep = 1;

function goToStep(step) {
    // Validate current step before moving forward
    if (step > currentStep) {
        if (!validateStep(currentStep)) {
            return;
        }
    }
    
    currentStep = step;
    
    // Update step indicators
    $('#step-1-indicator').toggleClass('active', step === 1).toggleClass('completed', step > 1);
    $('#step-2-indicator').toggleClass('active', step === 2);
    
    // Update step title
    if (step === 1) {
        $('#stepTitle').text(' Academic Information');
    } else {
        $('#stepTitle').text(' Select Courses & Sections');
    }
    
    // Toggle step content
    $('.step-content').removeClass('active');
    $('#step-' + step + '-content').addClass('active');
    
    // Scroll to top
    $('html, body').animate({ scrollTop: 0 }, 300);
}

function validateStep(step) {
    if (step === 1) {
        // Validate Academic Information
        var college = $('#college_code').val();
        var prog = $('#program_code').val();
        var year = $('#year_level').val();
        var sem = $('#semester').val();
        var schYear = $('#school_year').val();
        var stuType = $('#student_type').val();
        
        var isReEnroll = <?php echo $hasExisting ? 'true' : 'false'; ?>;
        
        // For re-enrollment, semester and school_year are pre-filled from approved enrollment
        // so disabled fields won't have values - we need to check differently
        if (isReEnroll) {
            // For re-enrollment, just validate program, year level, and student type
            if (!college || !prog || !year || !stuType) {
                $('#formError').text('Please select College, Program, Year Level, and Student Type.').show();
                return false;
            }
        } else {
            if (!college || !prog || !year || !sem || !schYear || !stuType) {
                $('#formError').text('Please complete all required fields: College, Program, Year Level, Semester, School Year, and Student Type.').show();
                return false;
            }
        }
        
        $('#formError').hide();
        return true;
    }
    return true;
}

$(document).ready(function () {
    var isReEnroll = <?php echo $hasExisting ? 'true' : 'false'; ?>;
    var existingProgram = '<?php echo $hasExisting && $hasExisting['status'] === 'Approved' ? htmlspecialchars($hasExisting['program_code']) : ''; ?>';
    var existingYear = '<?php echo $hasExisting && $hasExisting['status'] === 'Approved' ? htmlspecialchars($hasExisting['year_level']) : ''; ?>';
    var existingSemester = '<?php echo $hasExisting && $hasExisting['status'] === 'Approved' ? htmlspecialchars($hasExisting['semester']) : ''; ?>';
    var existingSchoolYear = '<?php echo $hasExisting && $hasExisting['status'] === 'Approved' ? htmlspecialchars($hasExisting['school_year']) : ''; ?>';

    // Load colleges, then all programs (each program already carries its
    // college_code/college_name from get_programs.php) so the Program
    // dropdown can be filtered client-side as soon as a College is picked —
    // no extra round trip per selection.
    $.get('../ajax/get_colleges.php', function (colleges) {
        var opts = '<option value="">— Select College —</option>';
        $.each(colleges, function (i, c) {
            opts += '<option value="' + c.college_code + '">' + c.college_code + ' — ' + c.college_name + '</option>';
        });
        $('#college_code').html(opts);

        $.get('../ajax/get_programs.php', function (programs) {
            allPrograms = programs || [];

            if (isReEnroll && existingProgram) {
                var match = allPrograms.find(function (p) { return p.program_code === existingProgram; });
                if (match) {
                    $('#college_code').val(match.college_code);
                    onCollegeChanged();
                    setTimeout(function () {
                        $('#program_code').val(existingProgram);
                        $('#year_level').val(existingYear);
                        refreshEnrollmentDetails();
                    }, 100);
                }
            }
        }, 'json');
    }, 'json');

    $('#school_year, #student_type').on('change input', function () {
        $('#summarySchoolYear').text($('#school_year').val() || '-');
        $('#summaryType').text($('#student_type').val() || '-');
    });
});

var allPrograms = [];

function onCollegeChanged(){
    var college = $('#college_code').val();
    var opts = '<option value="">— Select Program —</option>';
    var matches = allPrograms.filter(function (p) { return p.college_code === college; });

    if (!college) {
        opts = '<option value="">— Select a college first —</option>';
    } else if (!matches.length) {
        opts = '<option value="">No programs under this college yet</option>';
    } else {
        $.each(matches, function (i, p) {
            opts += '<option value="' + p.program_code + '">' + p.program_name + ' (' + p.program_code + ')</option>';
        });
    }
    $('#program_code').html(opts);
    refreshEnrollmentDetails();
}

function onProgramChanged(){
    refreshEnrollmentDetails();
}

function onYearLevelChanged(){
    refreshEnrollmentDetails();
}

function refreshEnrollmentDetails(){
    var prog = $('#program_code').val();
    var year = $('#year_level').val();
    var semText = $('#semester').val();
    
    // Convert semester text to number (1st Semester → 1, etc)
    var sem = 1;
    if (semText.indexOf('2nd') !== -1) sem = 2;
    else if (semText.indexOf('3rd') !== -1) sem = 3;
    else if (semText.indexOf('Summer') !== -1) sem = 99;

    if (!prog || !year || !semText) {
        $('#sectionsList').html('<p style="text-align:center; color:rgba(162,155,254,0.7);">Select program, year level, and semester to see sections.</p>');
        $('#coursesList').html('<p style="text-align:center; color:rgba(255,255,255,0.5);">Courses will appear after section selection.</p>');
        $('#totalUnits').text('0');
        $('#summaryProgram').text('-');
        $('#summaryYear').text('-');
        $('#summarySemester').text('-');
        $('#summarySection').text('-');
        $('#summaryUnits').text('0');
        $('#selectedSectionDisplay').text('None selected');
        return;
    }

    $('#summaryProgram').text($('#program_code option:selected').text());
    $('#summaryYear').text(year);
    $('#summarySemester').text(semText);

    // Load sections for the selected semester
    $.get('../ajax/get_sections.php', { program_code: prog, year_level: year }, function (sections) {
        allSections = sections;
        var html = '';
        if (sections.length === 0) {
            html = '<p style="text-align:center; color:rgba(162,155,254,0.7);">No sections available for this program and year level.</p>';
        } else {
            $.each(sections, function (i, s) {
                var isFull = s.current_enrolled >= s.max_capacity;
                var statusClass = isFull ? 'full' : 'open';
                html += '<div class="section-card ' + statusClass + '" onclick="selectSection(' + s.section_id + ', \'' + s.section_code.replace(/'/g, "\\'") + '\', ' + (isFull ? 'true' : 'false') + ')" style="' + (isFull ? 'opacity:0.5;cursor:not-allowed;' : '') + '">'+
                            '<div class="section-info">'+
                                '<div class="section-code">' + s.section_code + '</div>'+
                                '<div class="section-slots">' + s.current_enrolled + ' / ' + s.max_capacity + ' enrolled</div>'+
                            '</div>'+
                            '<div class="section-status ' + statusClass + '">' + (isFull ? 'FULL' : 'OPEN') + '</div>'+
                        '</div>';
            });
        }
        $('#sectionsList').html(html);
    }, 'json');
}

function selectSection(sectionId, sectionCode, isFull) {
    if (isFull) return;
    
    $('#section_id').val(sectionId);
    $('#selectedSectionDisplay').text(sectionCode);
    $('#summarySection').text(sectionCode);
    
    // Remove selected class from all cards
    $('.section-card').removeClass('selected');
    // Add selected class to clicked card
    event.target.closest('.section-card').classList.add('selected');
    
    // Load courses for selected section
    var prog = $('#program_code').val();
    var year = $('#year_level').val();
    var sem = $('#semester').val();
    
    // Convert semester text to number
    var semNum = 1;
    if (sem.indexOf('2nd') !== -1) semNum = 2;
    else if (sem.indexOf('Summer') !== -1) semNum = 99;
    
    // Check if this is a re-enrollment to get already-enrolled courses
    var isReEnroll = <?php echo $hasExisting ? 'true' : 'false'; ?>;
    var enrolledCourseIds = [];
    
    if (isReEnroll) {
        $.ajax({
            url: '../ajax/get_enrollment_detail.php',
            type: 'GET',
            async: false,
            dataType: 'json',
            success: function(data) {
                if (data && data.courses) {
                    enrolledCourseIds = data.courses.map(function(c) { return c.course_id; });
                }
            }
        });
    }
    
    $.get('../ajax/get_program_subjects.php', { program_code: prog, year_level: year, semester: semNum }, function (courses) {
        allCourses = courses;
        var html = '';
        if (courses.length === 0) {
            html = '<p style="text-align:center; color:rgba(255,255,255,0.5);">No courses available.</p>';
        } else {
            $.each(courses, function (i, c) {
                var isEnrolled = enrolledCourseIds.indexOf(c.course_id) !== -1;
                var disabledAttr = isEnrolled ? 'disabled' : '';
                var opacityStyle = isEnrolled ? 'opacity:0.5;' : '';
                var enrolledLabel = isEnrolled ? ' <span style="font-size:11px; color:rgba(85,239,196,0.7); margin-left:auto;">(Already Enrolled)</span>' : '';
                
                html += '<label style="display:flex; align-items:center; gap:10px; padding:12px; margin-bottom:8px; cursor:pointer; border-radius:6px; transition:all 0.2s; background:rgba(162,155,254,0.05); border:1px solid transparent;' + opacityStyle + '" onmouseover="this.style.background=\'rgba(162,155,254,0.08)\'" onmouseout="this.style.background=\'rgba(162,155,254,0.05)\'">' +
                    '<input type="checkbox" class="course-checkbox" value="' + c.course_id + '" data-units="' + c.units + '" onchange="updateTotalUnits()" style="width:18px; height:18px; cursor:pointer; flex-shrink:0;" ' + disabledAttr + '>' +
                    '<div style="flex:1;">' +
                        '<div class="course-code">' + c.course_code + '</div>' +
                        '<div class="course-name">' + c.course_name + '</div>' +
                        '<div class="course-units">' + c.units + ' units' + (c.is_required == 1 ? ' (Required)' : '') + '</div>' +
                    '</div>' +
                    enrolledLabel +
                    '</label>';
            });
        }
        $('#coursesList').html(html);
        $('#confirmationSummary').show();
    }, 'json');
}

function updateTotalUnits(){
    var totalUnits = 0;
    $('.course-checkbox:checked').each(function(){
        totalUnits += parseInt($(this).attr('data-units')) || 0;
    });
    $('#totalUnits').text(totalUnits);
    $('#summaryUnits').text(totalUnits);
}

$('#enrollForm').on('submit', function (e) {
    e.preventDefault();
    $('#formError').hide();
    
    // Validate step 2 requirements
    var sectionId = $('#section_id').val();
    var selectedCourses = $('.course-checkbox:checked');
    
    if (!sectionId) {
        $('#formError').text('Please select a section.').show();
        goToStep(2);
        return;
    }
    
    if (selectedCourses.length === 0) {
        $('#formError').text('Please select at least one course.').show();
        goToStep(2);
        return;
    }
    
    var isReEnroll = <?php echo $hasExisting ? 'true' : 'false'; ?>;
    var required = isReEnroll 
        ? ['program_code','year_level','semester','school_year','student_type']
        : ['first_name','last_name','gender','birthday','email','contact_number','address','program_code','year_level','semester','school_year','student_type'];
    
    let valid = true;
    $.each(required, function (i, f) { 
        let fieldVal = $('select[name="' + f + '"]').val() || $('#' + f).val();
        if (!fieldVal || !$.trim(fieldVal)) { valid = false; return false; } 
    });
    
    if (!valid) { 
        $('#formError').text('Please fill in all required fields.').show(); 
        goToStep(1);
        return; 
    }

    // Validate school year format only for new enrollment (not readonly)
    if (!$('#school_year').prop('readonly')) {
        if (!/^\d{4}-\d{4}$/.test($('#school_year').val())) { 
            $('#formError').text('School year must be YYYY-YYYY format.').show(); 
            goToStep(1);
            return; 
        }
    }

    $('#submitBtn').prop('disabled', true).text(isReEnroll ? 'Adding Courses...' : 'Submitting...');
    
    // Collect form data and selected courses
    var formData = $('#enrollForm').serializeArray();
    
    // Add disabled field values for re-enrollment
    if (isReEnroll) {
        formData.push({ name: 'program_code', value: $('#program_code').val() });
        formData.push({ name: 'year_level', value: $('#year_level').val() });
        formData.push({ name: 'semester', value: $('#semester').val() });
        formData.push({ name: 'school_year', value: $('#school_year').val() });
    }
    
    selectedCourses.each(function(){
        formData.push({ name: 'course_ids[]', value: $(this).val() });
    });
    
    $.post('../ajax/enroll_submit.php', formData, function (res) {
        if (res.success) {
            $('#enrollCard').hide();
            $('#confirmBox').fadeIn();
            if (res.student_number) {
                $('<div class="sn-display">' + res.student_number + '</div>').insertAfter($('.confirm-box > div:first')).before('<p>Your Student Number:</p>');
            }
            window.scrollTo(0, 0);
        } else {
            $('#formError').text(res.message || 'An error occurred. Please try again.').show();
            $('#submitBtn').prop('disabled', false).text(isReEnroll ? 'Add Courses' : 'Submit Enrollment');
        }
    }, 'json').fail(function () {
        $('#formError').text('Server error. Please try again.').show();
        $('#submitBtn').prop('disabled', false).text(isReEnroll ? 'Add Courses' : 'Submit Enrollment');
    });
});
</script>
</body>
</html>
