<?php require_once 'auth.php'; require_once '../includes/conn.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students — Alab E-BulSU Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<div class="bg-blobs">
    <div class="blob blob-1"></div><div class="blob blob-2"></div>
    <div class="blob blob-3"></div><div class="blob blob-4"></div>
</div>


<?php require_once 'nav.php'; ?>
<div class="main-content">

    <div class="page-header">
        <h2> Student Records</h2>
    </div>

    <div class="card">
        <div class="filter-bar">
            <input type="text" id="searchName" placeholder=" Search by name or student no...">
            <select id="filterProgram">
                <option value="">All Programs</option>
                <?php
                $progs = $conn->query("SELECT * FROM program ORDER BY program_code");
                while ($p = $progs->fetch_assoc()):
                ?>
                <option value="<?php echo $p['program_code']; ?>"><?php echo htmlspecialchars($p['program_name']); ?></option>
                <?php endwhile; ?>
            </select>
            <select id="filterStatus">
                <option value="">All Status</option>
                <option>Pending</option><option>Approved</option><option>Rejected</option>
            </select>
            <button class="btn btn-primary" id="btnSearch">Search</button>
            <button class="btn btn-secondary" id="btnReset">Reset</button>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student No.</th><th>Name</th><th>Program</th><th>Section</th>
                        <th>Year</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTable">
                    <tr><td colspan="6" class="loading">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="pagination" id="pager">
            <button id="pagerPrev" class="btn btn-secondary">&laquo; Prev</button>
            <span class="page-info" id="pagerInfo">Page 1</span>
            <button id="pagerNext" class="btn btn-secondary">Next &raquo;</button>
        </div>
    </div>

</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay" id="formModal">
    <div class="modal modal-lg">
        <button class="modal-close" onclick="closeModal('formModal')">&times;</button>
        <h3 id="formModalTitle">Add Student</h3>
        <div id="formModalBody"></div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal modal-sm">
        <button class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
        <h3>Confirm Delete</h3>
        <p style="color:#555;font-size:14px;margin:12px 0 24px;">Are you sure you want to delete this student record? This cannot be undone.</p>
        <div class="modal-actions-center">
            <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<div id="toast"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../includes/esc.js"></script>
<?php require_once __DIR__ . "/../includes/csrf_setup.php"; ?>
<script>
var programs = [];
var pendingDeleteId = null;

function openModal(id)  { $('#' + id).addClass('open'); }
function closeModal(id) { $('#' + id).removeClass('open'); }

function showToast(msg, type) {
    $('#toast').text(msg).removeClass('success error info').addClass(type).fadeIn(200);
    setTimeout(function () { $('#toast').fadeOut(400); }, 3000);
}

// Load programs list
$.get('../ajax/get_programs.php', function (data) { programs = data; }, 'json');

function buildForm(rec) {
    var isEdit = rec !== null;
    var v = function (f) { return isEdit && rec[f] ? esc(rec[f]) : ''; };

    var progOpts = '<option value="">— Select Program —</option>';
    $.each(programs, function (i, p) {
        var sel = (p.program_code === v('program_code')) ? ' selected' : '';
        progOpts += '<option value="' + p.program_code + '"' + sel + '>' + p.program_name + ' (' + p.program_code + ')</option>';
    });

    var semOpts = ['1st Semester','2nd Semester','Summer Class'].map(function(s){
        return '<option value="' + s + '"' + (v('semester')===s?' selected':'') + '>' + s + '</option>';
    }).join('');
    var yrOpts = ['1st Year','2nd Year','3rd Year','4th Year'].map(function(y){
        return '<option value="' + y + '"' + (v('year_level')===y?' selected':'') + '>' + y + '</option>';
    }).join('');
    var statOpts = ['Pending','Approved','Rejected'].map(function(s){
        return '<option value="' + s + '"' + (v('status')===s?' selected':'') + '>' + s + '</option>';
    }).join('');

    return (isEdit ? '<input type="hidden" id="f_enrollment_id" value="' + v('enrollment_id') + '">' : '') +
        '<div class="form-row-3">' +
            fg('First Name *', '<input type="text" id="f_first_name" maxlength="50" value="' + v('first_name') + '" required>') +
            fg('Middle Name', '<input type="text" id="f_middle_name" maxlength="50" value="' + v('middle_name') + '">') +
            fg('Last Name *', '<input type="text" id="f_last_name" maxlength="50" value="' + v('last_name') + '" required>') +
        '</div>' +
        '<div class="form-row">' +
            fg('Student Number *', '<input type="text" id="f_student_number" maxlength="20" placeholder="e.g. 2026-0001" value="' + v('student_number') + '" required>') +
            fg('Gender *', '<select id="f_gender"><option value="Male"' + (v('gender')==='Male'?' selected':'') + '>Male</option><option value="Female"' + (v('gender')==='Female'?' selected':'') + '>Female</option></select>') +
        '</div>' +
        '<div class="form-row">' +
            fg('Birthday *', '<input type="date" id="f_birthday" value="' + v('birthday') + '" required>') +
            fg('Email *', '<input type="email" id="f_email" value="' + v('email') + '" required>') +
        '</div>' +
        fg('Contact Number *', '<input type="text" id="f_contact_number" maxlength="20" value="' + v('contact_number') + '" required>') +
        fg('Address *', '<textarea id="f_address" rows="2">' + v('address') + '</textarea>') +
        '<div class="form-row">' +
            fg('Program *', '<select id="f_program_code">' + progOpts + '</select>') +
            fg('Year Level *', '<select id="f_year_level"><option value="">—</option>' + yrOpts + '</select>') +
        '</div>' +
        '<div class="form-row">' +
            fg('Semester *', '<select id="f_semester"><option value="">—</option>' + semOpts + '</select>') +
            fg('School Year *', '<input type="text" id="f_school_year" maxlength="9" placeholder="2025-2026" value="' + v('school_year') + '" required>') +
        '</div>' +
        fg('Enrollment Status', '<select id="f_status">' + statOpts + '</select>') +
        fg('Remarks', '<textarea id="f_remarks" rows="2">' + v('remarks') + '</textarea>') +
        '<div class="modal-actions">' +
            '<button class="btn btn-secondary" onclick="closeModal(\'formModal\')">Cancel</button>' +
            '<button class="btn btn-success" onclick="submitStudent(' + (isEdit?'true':'false') + ')">' + (isEdit?'Update Student':'Add Student') + '</button>' +
        '</div>';
}

function fg(label, input) {
    return '<div class="form-group"><label>' + label + '</label>' + input + '</div>';
}

function openEditModal(id) {
    $.get('../ajax/get_enrollment_detail.php', { id: id }, function (rec) {
        $('#formModalTitle').text('Update Student');
        $('#formModalBody').html(buildForm(rec));
        openModal('formModal');
    }, 'json');
}

function submitStudent(isEdit) {
    var data = {
        is_edit:        isEdit ? 1 : 0,
        enrollment_id:  isEdit ? $('#f_enrollment_id').val() : '',
        student_number: $('#f_student_number').val().trim(),
        first_name:     $('#f_first_name').val().trim(),
        middle_name:    $('#f_middle_name').val().trim(),
        last_name:      $('#f_last_name').val().trim(),
        gender:         $('#f_gender').val(),
        birthday:       $('#f_birthday').val(),
        email:          $('#f_email').val().trim(),
        contact_number: $('#f_contact_number').val().trim(),
        address:        $('#f_address').val().trim(),
        program_code:   $('#f_program_code').val(),
        year_level:     $('#f_year_level').val(),
        semester:       $('#f_semester').val(),
        school_year:    $('#f_school_year').val().trim(),
        status:         $('#f_status').val(),
        remarks:        $('#f_remarks').val().trim()
    };
    if (!data.first_name || !data.last_name || !data.student_number || !data.program_code) {
        showToast('Please fill in all required fields.', 'error'); return;
    }
    $.post('../ajax/save_student.php', data, function (res) {
        if (res.success) {
            closeModal('formModal');
            showToast(res.message, 'success');
            loadStudents(currentPage);
        } else {
            showToast(res.message, 'error');
        }
    }, 'json');
}

function openDeleteModal(id) {
    pendingDeleteId = id;
    openModal('deleteModal');
}

$('#confirmDeleteBtn').on('click', function () {
    $.post('../ajax/delete_student.php', { id: pendingDeleteId }, function (res) {
        closeModal('deleteModal');
        showToast(res.success ? res.message : res.message, res.success ? 'success' : 'error');
        if (res.success) loadStudents(currentPage);
    }, 'json');
});

var currentPage = 1;

function loadStudents(page) {
    currentPage = page || 1;
    var params = {
        search: $('#searchName').val(), program: $('#filterProgram').val(), status: $('#filterStatus').val(),
        page: currentPage, per_page: 20
    };
    $('#studentTable').html('<tr><td colspan="7" class="loading">Loading...</td></tr>');
    $.get('../ajax/get_enrollments.php', params, function (resp) {
        var data = resp.data || [];
        renderPager(resp);
        if (!data.length) { $('#studentTable').html('<tr><td colspan="7" class="empty">No students found.</td></tr>'); return; }
        var bc = { Pending: 'badge-pending', Approved: 'badge-approved', Rejected: 'badge-rejected' };
        var rows = '';
        $.each(data, function (i, e) {
            var fullName = esc(e.first_name) + (e.middle_name ? ' ' + esc(e.middle_name) : '') + ' ' + esc(e.last_name);
            var sectionDisplay = (e.section_code && e.section_name) ? esc(e.section_code) + ' - ' + esc(e.section_name) : '<span style="color:rgba(255,255,255,0.4);">Not assigned</span>';
            rows += '<tr>' +
                '<td>' + esc(e.student_number) + '</td>' +
                '<td>' + fullName + '</td>' +
                '<td>' + esc(e.program_name) + '</td>' +
                '<td>' + sectionDisplay + '</td>' +
                '<td>' + esc(e.year_level) + '</td>' +
                '<td><span class="badge ' + (bc[e.status]||'') + '">' + esc(e.status) + '</span></td>' +
                '<td>' +
                    '<button class="btn btn-warning" onclick="openEditModal(' + e.enrollment_id + ')">Edit</button>' +
                    '<button class="btn btn-danger"  onclick="openDeleteModal(' + e.enrollment_id + ')">Delete</button>' +
                '</td>' +
            '</tr>';
        });
        $('#studentTable').html(rows);
    }, 'json');
}

function renderPager(resp) {
    var totalPages = resp.total_pages || 1;
    $('#pagerInfo').text('Page ' + resp.page + ' of ' + totalPages + ' (' + resp.total + ' total)');
    $('#pagerPrev').prop('disabled', resp.page <= 1);
    $('#pagerNext').prop('disabled', resp.page >= totalPages);
}

$('#pagerPrev').on('click', function () { if (currentPage > 1) loadStudents(currentPage - 1); });
$('#pagerNext').on('click', function () { loadStudents(currentPage + 1); });

$('#btnSearch').on('click', function () { loadStudents(1); });
$('#btnReset').on('click', function () { $('#searchName, #filterProgram, #filterStatus').val(''); loadStudents(1); });
$('#searchName').on('keypress', function (e) { if (e.which === 13) loadStudents(1); });

$(document).on('click', '.modal-overlay', function (e) {
    if ($(e.target).hasClass('modal-overlay')) closeModal($(this).attr('id'));
});

loadStudents();
</script>
</body>
</html>
